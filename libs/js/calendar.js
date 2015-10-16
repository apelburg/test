// JavaScript Document
// styles
//
// !!!!!!!! Высокосный год !!!!!!!!!!!!!!!!!

    var calendar = {
		today:null,
		context_date:null,
		calendarLaunchBtnContainer:null,
		mainContainer:null,
		container:null,
		calendar_tbl:null,
		targetField:["setDateField","hiddenSetDateField"/*string || array*/],
		month_names:['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'],
		month_value:[31,28,31,30,31,30,31,31,30,31,30,31],
		week_days:['Пн','Вт','Ср','Чт','Пт','Сб','Вс'],
		show:function(arg){
			
			var date = (typeof arg == 'string')? arg : null;
			var element = (typeof arg == 'object')? arg : null;
			
			//alert(date+'>>');
			this.set_context_date(date);
			if(!this.calendarLaunchBtnContainer){
				this.calendarLaunchBtnContainer = element.parentNode;
				this.calendarLaunchBtnContainer.className = 'calendarLaunchButtonContainer';
			}
			if(!this.mainContainer) this.build_main_container();
			if(!this.container) this.build_container();
			if(!this.calendar_tbl) this.build_calendar();

			this.container.appendChild(this.calendar_tbl);
			this.mainContainer.appendChild(this.container);
			this.calendarLaunchBtnContainer.insertBefore(this.mainContainer,element);
			
		}
		,
		set_context_date:function(date_str){
			date = new Date();
			if(date_str){
				var date_arr = date_str.split('.');//alert(date_arr[2]+'='+(date_arr[1]-1)+'='+date_arr[0]);
				date.setFullYear(date_arr[2],date_arr[1]-1,date_arr[0]/*year,month,day*/);
			}
			this.context_date = date;//alert(this.context_date+'===');
		},
		build_main_container:function(){
			var div = document.createElement('div');
			div.className = 'calendarMainContainer';
			
			document.body.appendChild(div);
			this.mainContainer = div;
			//alert(div);
		}
		,
		build_container:function(){
			var div = document.createElement('div');
			div.className = 'calendarContainer';
			
			document.body.appendChild(div);
			this.container = div;
			//alert(div);
		}
		, 
		transferDate:function(){

			var targetField = (typeof calendar.targetField == 'string')? [calendar.targetField] :calendar.targetField ;
			
			for(var i=0 ; i < targetField.length ; i++){
			    var target = document.getElementById(targetField[i]);
				if(target.value) target.value = this.getAttribute('date');
				else target.innerHTML =  this.getAttribute('date');
			}
			 
			calendar.mainContainer.parentNode.removeChild(calendar.mainContainer);
			calendar.calendarLaunchBtnContainer = null;
			calendar.mainContainer = null;
			calendar.container = null;
			calendar.calendar_tbl = null;
			
			return false;
		}
		,
		changeMonth:function(){
			//alert(this.getAttribute('date'));
			//alert(calendar.calendar_tbl);
			calendar.calendar_tbl.parentNode.removeChild(calendar.calendar_tbl);
			//alert(calendar.calendar_tbl);
			calendar.calendar_tbl = null;
			calendar.show(this.getAttribute('date'));

			return false;
		}
		,
		build_calendar:function(){
		
			var year = this.context_date.getFullYear();
			var month = this.context_date.getMonth();
			var day = this.context_date.getDate();// день месяца
			// если высокосный год
			this.month_value[1] = (year%4 == 0)? 29 : 28 ;
			///
			var firstMonthDay = new Date();
			firstMonthDay.setFullYear(year,month,1);
			var NumDayInWeek = firstMonthDay.getDay(); // от 0(Sunday) до 6(Saturday)
			var firstMonthDayNumDayInWeek = (NumDayInWeek == 0)? 6: NumDayInWeek -1;// трансформируем номер дня недели 
			                                                                        // в формат от 0(Пн) до 6(Вс)
			//alert(firstMonthDayNumDayInWeek);
			
			var stop_flag =false;
			var day_counter = null;
			var table = document.createElement('table');
			table.className = 'calendarTable';
			
			// панель навигации
			var tr = document.createElement('tr');
			var td = document.createElement('td');
			td.setAttribute('colspan', '7');
			var nav_table = document.createElement('table');
			nav_table.className = 'NavTable';
			var nav_table_tr = document.createElement('tr');
			for(var i=0 ; i < 3 ; i++){
			    var nav_table_td = document.createElement('td');
				if(i==0 || i==2){
					nav_table_td.className = 'arrow';
					var text_str = (i==0)? '<<':'>>';
					var new_month = (i==0)? month : month+2;
					var text = document.createTextNode(text_str);
					var a = document.createElement('a');
					a.href = '#';
					a.setAttribute('date',1+'.'+new_month+'.'+year);//)+'.'+year
					a.onclick = this.changeMonth;
					a.appendChild(text);
					nav_table_td.appendChild(a);
				}
				if(i==1){
					nav_table_td.className = 'date';
					var text = document.createTextNode(this.month_names[month]+' '+year);
					nav_table_td.appendChild(text);
				}
				nav_table_tr.appendChild(nav_table_td);
			}
			nav_table.appendChild(nav_table_tr);
			td.appendChild(nav_table);
			tr.appendChild(td);
			table.appendChild(tr);
			
			// оглавление
			var tr = document.createElement('tr');
			for(var j=0 ; j < 7 ; j++){
			    var td = document.createElement('td');
                td.className = 'calendarCellCup';
				var text = document.createTextNode(this.week_days[j]);
				td.appendChild(text);
				tr.appendChild(td);
			}
			table.appendChild(tr);
			// сетка
			for(var i=0 ; !stop_flag; i++){
				
				var tr = document.createElement('tr');
                for(var j=0 ; j < 7 ; j++){
					if(!day_counter && !stop_flag &&  firstMonthDayNumDayInWeek == j) day_counter = 1;
					var td = document.createElement('td');
                    td.className =  (j>=5)? 'calendarCell weekEnd' :'calendarCell';
        
					if(day_counter){
							
						var a = document.createElement('a');
						a.setAttribute('date',this.toStr(day_counter)+'.'+this.toStr(month+1)+'.'+year);
						a.href = '#';
						a.onclick = this.transferDate;
						
						var text = document.createTextNode(day_counter);
						a.appendChild(text);
						td.appendChild(a);
						
						if(day_counter >= this.month_value[month]){
							stop_flag = true;
							day_counter = null;
						}
						else day_counter++;
					}
					else{
						td.className += ' notActive';
					}
					tr.appendChild(td);	
				}
				table.appendChild(tr);
			}
			this.calendar_tbl = table;
			//alert(this.calendar);
			//document.body.appendChild(table);
		}
		,
		toStr:function(val){
			var str = val.toString();
			if(str.length == 2) return str; 
            return '0'+str;
		}	
	}