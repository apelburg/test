// JavaScript Document
var calendar_consturctor = {
   id:'',
   cur_date:[],
   cont_date:[],
   month_names:['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'],
   month_value:[31,28,31,30,31,30,31,31,30,31,30,31],
   user_preset:false,
   setContextDay:function(cont_date,user_preset){
      var date = new Date();
	  
      this.cur_date = [date.getFullYear(),date.getMonth(),date.getDate()];
	  if(cont_date && cont_date[1] == -1){
	     cont_date[1] = 11;
		 cont_date[0]--;
	  }
	  if(cont_date && cont_date[1] == 12){
	     cont_date[1] = 0;
		 cont_date[0]++;
	  }
	  this.cont_date = cont_date || [date.getFullYear(),date.getMonth(),date.getDate()];
	  this.user_preset = user_preset || false;
	  //alert(this.user_preset);
   },
   calendarCupBilder:function(id){// верхняя панель календаря с месяцем и годом
   
      var table = document.createElement("table");
	  table.id = id;
	  table.style.borderCollapse = "collapse";
	  table.border ="0";
	  table.style.width = '100%';
	  var tr = document.createElement("tr");
	  
	  var td1 = document.createElement("td");
	  td1.appendChild(document.createTextNode("Дата:"));
	  
	  var td2 = document.createElement("td");
	  var a1 = document.createElement("a");
	  a1.style.cursor = 'pointer';
	  a1.onclick = function(){
	    
		 calendar_consturctor.setContextDay([calendar_consturctor.cont_date[0],calendar_consturctor.cont_date[1]-1,1]);
		 var div = document.getElementById('div_for_' + calendar_consturctor.id);
		 div.removeChild(document.getElementById(calendar_consturctor.id));
		 div.removeChild(document.getElementById('cup_' + calendar_consturctor.id));
	     var calendar = calendar_consturctor.calendarTableBilder(calendar_consturctor.id);	
		 div.appendChild(calendar[1]);
         div.appendChild(calendar[2]);   
	  };
	  a1.innerHTML = '<<';
	  td2.appendChild(a1);
	  td2.style.width = '18px';
	  
	  var td3 = document.createElement("td");
      td3.innerHTML = this.month_names[this.cont_date[1]]+' '+this.cont_date[0];
	  
	  
	  var td4 = document.createElement("td");
	  var a2 = document.createElement("a");
	  a2.style.cursor = 'pointer';
	  a2.onclick = function(){
	     calendar_consturctor.setContextDay([calendar_consturctor.cont_date[0],calendar_consturctor.cont_date[1]+1,1]);
		 var div = document.getElementById('div_for_' + calendar_consturctor.id);
		 div.removeChild(document.getElementById(calendar_consturctor.id));
		 div.removeChild(document.getElementById('cup_' + calendar_consturctor.id));
	     var calendar = calendar_consturctor.calendarTableBilder(calendar_consturctor.id);	
		 div.appendChild(calendar[1]);
         div.appendChild(calendar[2]); 
	  };
	  a2.innerHTML = '>>';
	  td4.appendChild(a2);
	  td4.style.width = '18px';
	  
	  tr.appendChild(td1);
	  tr.appendChild(td2);
	  tr.appendChild(td3);
	  tr.appendChild(td4);
      table.appendChild(tr);
	  return table;
	  
   },
   calendarTableBilder:function(id){
	  //alert(id);
	  this.id = id;
	  
	  // div container for calendar
	  var div = (document.getElementById('div_for_'+id))? document.getElementById('div_for_'+id) : document.createElement("div");
	  div.id = 'div_for_' + id;
	  
	  // input for store context_day
	  var input=(document.getElementById('input_for_'+id))?document.getElementById('input_for_'+id):document.createElement("input");
	  input.id = 'input_for_' + id;
	  input.type = 'hidden';
	  input.name = 'form_data[remind_date]';
	  //var cur_date_1_str = ((this.cur_date[1].toString()).length < 2)? '0'+this.cur_date[1].toString():this.cur_date[1].toString();
	  //var cur_date_2_str = ((this.cur_date[2].toString()).length < 2)? '0'+this.cur_date[2].toString():this.cur_date[2].toString();
	  //input.value = cur_date_2_str+'.'+cur_date_1_str+'.'+this.cur_date[0];
	  var cont_date_1_str=(((this.cont_date[1]+1).toString()).length < 2)?'0'+(this.cont_date[1]+1).toString():(this.cont_date[1]+1).toString();
	  var cont_date_2_str=((this.cont_date[2].toString()).length < 2)?'0'+this.cont_date[2].toString():this.cont_date[2].toString();
	  input.value = this.cur_date[0]+'-'+cont_date_1_str+'-'+cont_date_2_str;
	  
	  
	  
      // день недели, первого дня текущего месяца
      var first_date_week = new Date(this.cont_date[0],this.cont_date[1],1);
      var day_first_date_week = (first_date_week.getDay() == 0)? 7: first_date_week.getDay();
      //alert(day_first_date_week); 

      // настройки
      var month_duration = this.month_value[this.cont_date[1]];
      var week_duration = 7;
      var week_num = 0;
      var weeks =[];
     
      // расчитываем сколько должно быть ячеек в таблице календаря	
	  var num_table_cell = ((month_duration+(day_first_date_week-1))%week_duration == 0)? month_duration+(day_first_date_week-1) : Math.floor((month_duration+(day_first_date_week-1))/week_duration)*week_duration + week_duration ;
	  //alert(num_table_cell);
	  
      // сетка календаря
	  var table = document.createElement("table");
	  table.id = id;
	  table.style.width = '100%';
	  var tr = document.createElement("tr");
		
      var go_calendar = false;
	  var calendar_day_num = 0;
	  for(var i = 1; i <= num_table_cell ;i++ ){ 
	     
	     if(i == day_first_date_week)go_calendar = true;
	     if(calendar_day_num == month_duration)go_calendar = false;
	     if(go_calendar){
		    calendar_day_num++;
		    var td = document.createElement("td");
			td.style.textAlign = 'right';
			td.style.padding = '0px 4px 0px 4px ';
		    var a = document.createElement("a");
		    a.style.cursor = 'pointer';
		   
			//this.cont_date[1]+1
			//alert((calendar_day_num.toString()).length);
			var calendar_day_num_str = ((calendar_day_num.toString()).length < 2)? '0'+calendar_day_num.toString():calendar_day_num.toString();
			var month_num_str =(((this.cont_date[1]+1).toString()).length<2)? '0'+(this.cont_date[1]+1).toString():(this.cont_date[1]+1).toString();
			a.date = this.cont_date[0]+'-'+ month_num_str+'-'+calendar_day_num_str;
		    a.onclick = function(){
			   document.getElementById('input_for_' + id).value = this.date;
			   calendar_consturctor.setBgcolorToTd(document.getElementById('calendar_table'));
			   this.parentNode.style.backgroundColor = '#95B425';
			   
		    };
		    a.innerHTML = calendar_day_num;
		    td.appendChild(a);
		    tr.appendChild(td);
		    if(calendar_day_num == this.cur_date[2] && this.cur_date[1]==this.cont_date[1] && this.cur_date[0]==this.cont_date[0]){
			    td.style.backgroundColor = '#BBBBBB'; 
				td.setAttribute('cur_day',true); 
			}
			if(this.user_preset && calendar_day_num == this.cont_date[2]){
			    td.style.backgroundColor = '#95B425'; 
			}
	     } 
	     else{
		    var td = document.createElement("td");
		    tr.appendChild(td);
	     }
		 if(i%week_duration == 0 || (i+1)%week_duration == 0 ){
			td.style.backgroundColor = '#FFCC66';
			td.setAttribute('weekend',true);
		 }
	     if(i%week_duration == 0){ 
		    weeks[week_num++] = tr;
		    var tr = document.createElement("tr");
	     }
         
      }

      for(var i = 0; i < weeks.length ;i++ ){
   
         table.appendChild(weeks[i]);
      }
	  return new Array(div,this.calendarCupBilder('cup_' + id),table,input);
   
   },
   timeTable:function(id,preset_time){
	  var time_arr = ['07.00','07.30','08.00','08.30','09.00','09.30','10.00','10.30','11.00','11.30','12.00','12.30','13.00','13.30','14.00','14.30','15.00','15.30','16.00','16.30','17.00','18.30','19.00','19.30','20.00','20.30','21.00','21.30','22.00','22.30','23.00','23.30','00.00',];
	  var preset_time = preset_time || time_arr[6]; 
	  // div container for time_table
	  var div = (document.getElementById('div_for_'+id))? document.getElementById('div_for_'+id) : document.createElement("div");
	  div.id = 'div_for_' + id;
	  
	  // input for store context_day
	  var input=(document.getElementById('input_for_'+id))?document.getElementById('input_for_'+id):document.createElement("input");
	  input.id = 'input_for_' + id;
	  input.type = 'hidden';
	  input.name = 'form_data[time_table_date]';
	  input.value = preset_time;
	  
	  // заголовок 
	  var header_div = document.createElement("div");
	  header_div.innerHTML = 'Время';
	  
	  // сетка time_table
	  var table = document.createElement("table");
	  table.id = id;
	  table.style.width = '100%';
	  var tr = document.createElement("tr");
	  
	  var time_table_cell_num = 30;
	  var coll_in_row = 5;
	  var rows = [];
	  var rows_num = 0;
	  for(var i = 1; i <= time_table_cell_num ;i++ ){
		  var td = document.createElement("td");
		  td.style.padding = '0px 1px 0px 1px ';
		  var a = document.createElement("a");
		  a.style.cursor = 'pointer';
		  a.setAttribute('time',time_arr[i]);
		  a.onclick = function(){
			  document.getElementById('input_for_' + id).value = this.getAttribute('time');
			  calendar_consturctor.setBgcolorToTd(document.getElementById('time_table'));
			  this.parentNode.style.backgroundColor = '#95B425';
			   
		  };
		  a.innerHTML = time_arr[i];
		  td.appendChild(a);
		  tr.appendChild(td);
		  if(time_arr[i] == preset_time) td.style.backgroundColor = '#95B425'; 
		  if(i%coll_in_row == 0){
			  rows[rows_num++] = tr;
			  var tr = document.createElement("tr");
		  }   
      }
	  for(var i = 0; i < rows.length ;i++ ) table.appendChild(rows[i]);

	  return new Array(div,table,input,header_div);
     
   },
   setBgcolorToTd:function(table){
	  var td_arr = table.getElementsByTagName('td');
	  for(var i = 0; i < td_arr.length ;i++ ){
		 td_arr[i].style.backgroundColor = '#ECECEC';
         if(td_arr[i].getAttribute('weekend')) td_arr[i].style.backgroundColor = '#FFCC66';
		 if(td_arr[i].getAttribute('cur_day')) td_arr[i].style.backgroundColor = '#BBBBBB';
      }
   }
}
//образец вызова и вставки в страницу
//calendar_consturctor.setContextDay();
//var calendar = calendar_consturctor.calendarTableBilder('calendar_table');
//calendar[0].style.width = '180px';
//document.body.appendChild(calendar[0]);
//calendar[0].appendChild(calendar[1]);
//calendar[0].appendChild(calendar[2]);