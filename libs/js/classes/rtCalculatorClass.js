// JavaScript Document
window.onload = function(){
   rtCalculator.init_tbl('rt_tbl_head','rt_tbl_body');
}

// инициализация
/*if(window.addEventListener){
	window.addEventListener('load',tableDataManager.install,false);
}
else if(window.attachEvent){
	window.attachEvent('onload',tableDataManager.install);
}
else{
	var old_handler = window.onload;
	window.onload = function (){
		if(typeof old_handler == 'function') old_handler();
		tableDataManager.install();
	}
}
if(window.addEventListener) window.addEventListener('load',tableDataManager.install,false);
	else if(window.attachEvent) window.attachEvent('onload',tableDataManager.install);
	else window.onload = tableDataManager.install;
*/

$(window).on('beforeunload', function() {
    if(rtCalculator.changes_in_process) return 'У Вас есть не сохраненные данные, Вы можете их потерять';							  
});
	
window.onunload = function(){// пока с этим не ясно
   //alert(1);
}
print_r.count = 0;
function print_r(val/* array or object */){
	var str = scan(val);
	var win = window.open(null,'print_r'+(print_r.count++),'width=300,height=800',true);
	win.document.write(str);
	win.document.close();
	
	function scan(val){
		var str = '';
		for(var i in val){
			if(typeof val[i] != 'object') str += '[' + i + '] = [' + val[i] + ']<br>';
			if(typeof val[i] == 'object') str += '[' + i + '] => (' + scan(val[i]) + ')<br>';
		}
		return str;
	}
}

var rtCalculator = {
    // алгоритм действия калькулятора таблицы РТ: 1. При при наступлении события window.onload() считываются данные таблицы и сохраняются 
	// в переменной 2. специальным методом необходимые поля ввода устанавливаются редактируемыми и на необходимые поля навешиваются 
	// обработчики событий (это делается после полного считывания первоначальных данных, потому что для проведения расчета необходимы, данные
	// ДО и после ввода данных в поля ввода) 3. при возникновении событий приводящих к изменению данных в полях ввода происходит перерасчет
	// данных по конкретному ряду - вычисление разницы данных по данному ряду, внесение новых значений в итоговые суммы ряда , внесение новых
	// значений в итоговые суммы таблицы
    tbl:false,
    tbl_model:false,
	tbl_total_row:false,
	previos_data:{},
	complite_count:0,
	dscntDisclaimerProtocol:{},
	sizeExistsDisclaimerProtocol:{},
	primary_val:false
	,
	init_tbl:function(head_tbl_id,body_tbl_id){// метод запускаемый при наступлении события window.onload()
	                          // вызывает методы:
							  // collect_data - для создания модели таблицы
							  // set_interactive_cells - для установки интерактивных полей таблицы ( поля ввода, переключатели, маркеры)
	    this.head_tbl = document.getElementById(head_tbl_id);
	    this.body_tbl = document.getElementById(body_tbl_id);
		//alert(this.tbl);
		this.collect_data();
		this.set_interactive_cells();
	}
	,
	collect_data:function(){
	    // метод считывающий данные таблицы РТ и сохраняющий их в свойство this.tbl_model 
	    this.tbl_model={};
		
		// считываем данные из head_tbl
		var trs_arr = this.head_tbl.getElementsByTagName('tr');
		for(var i = 0;i < trs_arr.length;i++){ 
		
		    if(!trs_arr[i].getAttribute('row_id')) continue;
			
			var row_id = trs_arr[i].getAttribute('row_id');
			
			if(!this.tbl_model[row_id]) this.tbl_model[row_id] = {}; 
			
		    if(row_id=='total_row') this.tbl_total_row = trs_arr[i]; 
			
			
			var tds_arr = trs_arr[i].getElementsByTagName('td');
			//alert(row_id);
			for(var j = 0;j < tds_arr.length;j++){
				//if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
				if(tds_arr[j].hasAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					if(type == 'glob_counter' || type == 'dop_details' || type == 'master_btn' || type == 'name') continue;
				    this.tbl_model[row_id][type] = parseFloat(tds_arr[j].innerHTML);
				}
			}/**/
	    }
		
		//  считываем данные из body_tbl
	    var trs_arr = this.body_tbl.getElementsByTagName('tr');
		for(var i = 0;i < trs_arr.length;i++){
		    // если ряд не имеет атрибута row_id пропускаем его
		    if(!trs_arr[i].getAttribute('row_id')) continue;
			
			var row_id = trs_arr[i].getAttribute('row_id');
			
			//
			var pos_id = (trs_arr[i].getAttribute('pos_id'))? trs_arr[i].getAttribute('pos_id'):false;
			var parent_pos_id = (pos_id)? pos_id:((typeof parent_pos_id !=='undefined')?parent_pos_id:false);
			
			// row_id==0 у вспомогательных рядов их пропускаем
			if(row_id==0) continue; //trs_arr[i].style.backgroundColor = '#FFFF00';
			
			if(!this.tbl_model[row_id]) this.tbl_model[row_id] = {}; 
			
			// заносим информацию об исключении ряда из расчета
			/*if(row_id!='total_row'){
			    var expel = !!parseInt(trs_arr[i].getAttribute('expel'));
			    this.tbl_model[row_id].dop_data={'expel':expel};
			}*/
			
			var tds_arr = trs_arr[i].getElementsByTagName('td');
			for(var j = 0;j < tds_arr.length;j++){
				
				if(parent_pos_id){
					// устанавливаем id родительского ряда id товарной позиции
					if(!this.tbl_model[row_id].dop_data) this.tbl_model[row_id].dop_data = {};
					this.tbl_model[row_id].dop_data.parent_pos_id = parent_pos_id; 
				}
				
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					if(type == 'glob_counter' || type == 'master_btn' || type == 'name') continue;
					
					this.tbl_model[row_id][type] = parseFloat(tds_arr[j].innerHTML);
	
					if(tds_arr[j].getAttribute('expel')){
						if(!this.tbl_model[row_id].dop_data)this.tbl_model[row_id].dop_data = {};
						if(!this.tbl_model[row_id].dop_data.expel)this.tbl_model[row_id].dop_data.expel = {};
						var expel = !!parseInt(tds_arr[j].getAttribute('expel'));
						if(type=='out_summ'){
							this.tbl_model[row_id].dop_data.expel.main=expel;
						}
						else if(type=='print_out_summ' || type=='print_in_summ'){
							this.tbl_model[row_id].dop_data.expel.print=expel;
						}
						else if(type=='dop_uslugi_out_summ' || type=='dop_uslugi_in_summ'){
							this.tbl_model[row_id].dop_data.expel.dop=expel;
						}
						
					}
					if(tds_arr[j].hasAttribute('svetofor')){
						if(!this.tbl_model[row_id].dop_data)this.tbl_model[row_id].dop_data = {};
						this.tbl_model[row_id].dop_data.svetofor = tds_arr[j].getAttribute('svetofor');
					}
					
					/*// если это ряд содержащий абсолютные ссуммы сохраняем постоянные ссылки на его ячейки , чтобы затем вносить в них изменения
					// КАК ТО НЕ ПОЛУЧИЛОСЬ
					if(row_id=='total_row'){
					    if(!this.tbl_model['total_row_links']) this.tbl_model['total_row_links'] = {};
					    this.tbl_model['total_row_links'][tds_arr[j].getAttribute('type')] = tds_arr_1[j];
					}
					*/
				}
				
			}
		
		}
	    //print_r(this.tbl_model);
		return true;  
	},
	set_interactive_cells:function(){
	    // Этот метод устанавливает необходимым ячекам различные интерактивные свойства
		// и навешивает обработчики событий
		var trs_arr = this.head_tbl.getElementsByTagName('tr');
		for(var i in trs_arr){
	        if(trs_arr[i].nodeName=='TR'){
				//// console.log(trs_arr[i]);
				var tds_arr = trs_arr[i].getElementsByTagName('td');
				for(var j in tds_arr){
					if(tds_arr[j].nodeName == 'TD'){
				        if(i == 0 && tds_arr[j].getAttribute('swiched_cols')){// swiched_cols взаимно переключаемые ряды (ед/тираж, вход/выход)
						   tds_arr[j].onclick = this.swich_cols;
					    }
					}
			    }	
		    }
		}
		
		var trs_arr = this.body_tbl.getElementsByTagName('tr');
		for(var i in trs_arr){
			if(trs_arr[i].getAttribute){
			    if(trs_arr[i].getAttribute("row_id")!='0'){
					var block = (trs_arr[i].hasAttribute("block") && trs_arr[i].getAttribute("block")=='1')?true:false;
					var tds_arr = trs_arr[i].getElementsByTagName('td');
					for(var j in tds_arr){
						if(tds_arr[j].getAttribute){
							if(tds_arr[j].getAttribute('editable') && !block){
								//tds_arr[j].onkeyup = this.make_calculations;
								tds_arr[j].onfocus = function(e){ 
								   e = e || window.event;
								   // устанавливаем текущюю ячейку и сохраняем изначальное значение
		                           rtCalculator.cur_cell = e.target || e.srcElement;
								   rtCalculator.primary_val = rtCalculator.cur_cell.innerHTML;
                                   // устанавливаем текущюю ячейку
								   rtCalculator.changes_in_process = true;
								}
								tds_arr[j].onkeyup = function(e){
									
								   //if(!rtCalculator.cur_cell) location.reload();
								   if(rtCalculator.cur_cell  &&  rtCalculator.cur_cell.hasAttribute('type') && rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
									   // alert(1);
									   rtCalculator.checkQuantity();
								   }
								   else{
									   // alert(2);
									   rtCalculator.check();
									   // запускаем таймер по истечению которого вызываем функцию rtCalculator.complite_input
									   // отправляющую данные на сервер
									   //if(rtCalculator.cur_cell.getAttribute('type') && rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
									   if(!rtCalculator.complite_timer) rtCalculator.complite_timer = setTimeout(rtCalculator.complite_input,2000); 
								   }
								   
								}
								tds_arr[j].onblur = function(){
								   if(rtCalculator.cur_cell.getAttribute('type') && rtCalculator.cur_cell.getAttribute('type')!= 'quantity'){
									   rtCalculator.complite_input();
								   } 
								}
								tds_arr[j].setAttribute("contenteditable",true);
								tds_arr[j].style.outline="none";
							}
							if(tds_arr[j].getAttribute('discount_fieid') && !block){
								tds_arr[j].onclick = this.show_discount_window;//(this,'.$dop_key.','.$client_id.')
							}
							if(tds_arr[j].getAttribute('expel')){
								tds_arr[j].onclick = this.expel_value_from_calculation;
							}
							if(tds_arr[j].getAttribute('svetofor')){
								//// console.log(j+' svetofor');
								if(tds_arr[j].getElementsByTagName('img')[0]) $(tds_arr[j].getElementsByTagName('img')[0]).mouseenter(this.show_svetofor);
								
							}
							if(tds_arr[j].getAttribute('raschet_status')){
								$(tds_arr[j]).mouseenter(function(e){ statusTooltip.schedule(this)});
							}
							if(tds_arr[j].getAttribute('calc_btn') && !block){
								//// console.log(j+' svetofor');
								if(tds_arr[j].getElementsByTagName('span')[0]) tds_arr[j].getElementsByTagName('span')[0].onclick = printCalculator.start_calculator;
								
							}
						}
					}
				}
			}
		}
	}	
	,
	complite_input:function(){
		// метод срабатывает либо при событие onblur в ячейках ввода данных для расчета ( тем самым он срабатывает когда ввод данных завершен
		// либо при срабатывании таймера запускающегося при onkeyup в ячейке что позволяет отправлять данные из ячейки с некоторыим интервалом
		// используем этот момент для отправки измененных данных в базу данных на сервер для синхронизации изменений ) 
		
		if(rtCalculator.complite_timer){
			 clearTimeout(rtCalculator.complite_timer);
			 rtCalculator.complite_timer = null;
		}
		 
		 // console.log('№'+(++rtCalculator.complite_count));
		 // console.log(1);
		// получаем значение ячейки
		var prop = rtCalculator.cur_cell.getAttribute('type');
		var last_val = rtCalculator.cur_cell.innerHTML;
		
		
		// сравниваем текущее значение с первоначальным, если они равны значит окончательные изменения не были произведены
		// в таком случае ничего не меняем в базе - прерываем дальнейшее выполнение
		if(rtCalculator.primary_val == last_val){
			rtCalculator.changes_in_process = false;
			return;
		}
		// console.log(rtCalculator.primary_val+' '+last_val);
		
		var row_id = rtCalculator.cur_cell.parentNode.getAttribute('row_id');
		var discount = (rtCalculator.tbl_model[row_id].discount)?rtCalculator.tbl_model[row_id].discount:0;
		//if(prop == 'price_out' && (discount!=0 || (discount==0 && rtCalculator.previos_data['discount']!=0 ))){
		if(prop == 'price_out' && discount!=0){
		    //alert(rtCalculator.previos_data['$discount']);
			prop = 'discount';
			last_val = discount;
			//console.log(rtCalculator.tbl_model[row_id].discount);
			//if(!rtCalculator.dscntDisclaimerProtocol[row_id]) rtCalculator.shDscntDisclaimer(rtCalculator.cur_cell,row_id,discount);
		}
		
		if(prop == 'price_out' && discount==0){
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_rt_changes={"id":"'+row_id+'","discount":"0","prop":"'+prop +'","val":"'+last_val+'"}');
			
			
			//console.log(rtCalculator.tbl_model[row_id].discount);
			//if(!rtCalculator.dscntDisclaimerProtocol[row_id]) rtCalculator.shDscntDisclaimer(rtCalculator.cur_cell,row_id,discount);
		}
		else{
			if(prop == 'price_out' && discount!=0){
				prop = 'discount';
				last_val = discount;
			}
			// формируем url для AJAX запроса
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_rt_changes={"id":"'+row_id+'","prop":"'+prop +'","val":"'+last_val+'"}');
		}
		rtCalculator.send_ajax(url,callback);
		//alert(url);
		function callback(){ 
		    rtCalculator.changes_in_process = false;
		    /*cell.className = cell.className.slice(0,cell.className.indexOf("active")-1);*/
			// console.log(2);
		}
	}
	,
	shDscntDisclaimer:function(cur_cell,row_id,discount){
		rtCalculator.dscntDisclaimerProtocol[row_id] = true;
		
		tooltip = document.createElement("div");  
        tooltip.style.position = "absolute";  
        tooltip.id = "dscntDisclaimer"+row_id; 
        tooltip.className = "rtDiscountTooltip"; 
		tooltip.innerHTML = "на ячейку установлена "+((discount<0)?"скидка":"наценка")+"<br>введеная цена будет не верна1"; 
		var pos = rtCalculator.getPos(cur_cell);
		tooltip.style.top = pos[0] + 2 +"px";
		//tooltip.style.left = pos[1] -200 +"px";
		document.body.appendChild(tooltip);
		tooltip.style.left = (pos[1] -tooltip.offsetWidth -55) +"px";
		
		var closeDscntTimer = setTimeout(closeDscntDisclaimer1,4000); 
		var closeDscntTimer = setTimeout(closeDscntDisclaimer2,8000); 
		function closeDscntDisclaimer1(){
		    if(document.getElementById('dscntDisclaimer'+row_id)) document.getElementById('dscntDisclaimer'+row_id).parentNode.removeChild(document.getElementById('dscntDisclaimer'+row_id));	
			
		}
		function closeDscntDisclaimer2(){
			delete rtCalculator.dscntDisclaimerProtocol[row_id];	
		}
	}
	,
	sizeExistsDisclaimer:function(cur_cell,row_id){
		rtCalculator.sizeExistsDisclaimerProtocol[row_id] = true;
		
		tooltip = document.createElement("div");  
        tooltip.style.position = "absolute";  
		tooltip.style.textAlign = "left";
        tooltip.id = "sizeExistsDisclaimer"+row_id; 
        tooltip.className = "rtDiscountTooltip"; 
		tooltip.innerHTML = "Изделие содержит размерный ряд<br>для изменения тиража<br>пройдите в карточку артикула"; 
		var pos = rtCalculator.getPos(cur_cell);
		tooltip.style.top = pos[0] - 10 +"px";
		//tooltip.style.left = pos[1] -200 +"px";
		document.body.appendChild(tooltip);
		tooltip.style.left = (pos[1] -tooltip.offsetWidth + 5) +"px";
		
		var closeDscntTimer = setTimeout(closeSizeExsDisclaimer1,6000); 
		var closeDscntTimer = setTimeout(closeSizeExsDisclaimer2,10000); 
		function closeSizeExsDisclaimer1(){
		    if(document.getElementById('sizeExistsDisclaimer'+row_id)) document.getElementById('sizeExistsDisclaimer'+row_id).parentNode.removeChild(document.getElementById('sizeExistsDisclaimer'+row_id));	
			
		}
		function closeSizeExsDisclaimer2(){
			delete rtCalculator.sizeExistsDisclaimerProtocol[row_id];	
		}
	}
	,
	getPos:function(element){
		 var y= 0;
		   var х = 0;
		   for(var e = element; e != null; e = e.offsetParent){ // Цикл по offsetParent
			  y += e.offsetTop;
			  х += e.offsetLeft;
		   }
		
		   for(e = element.parentNode; e && e != document.body; e = e.parentNode){
			  if(e.scrollTop) y -= e.scrollTop; 
		   }
		   return [y,х];
	}
	,
	checkQuantity:function(){// корректировка значений вводимых пользователем

		var cell = rtCalculator.cur_cell;
		//alert(cell.innerHTML);
		//alert(floatLengthToFixed (cell.innerHTML));
		var result = correctToInt(cell.innerHTML);

		if(result != 0) setCaretToPos2(cell,result);
		rtCalculator.makeQuantityCalculationsPreparing(cell);
		
		
	
		function correctToInt(str){// корректировка значений вводимых пользователем в поле ввода типа Integer
		    var wrong_input = false;
            var pos = 0;
			
			// если строка пустая правим на 0
			if(str == ''){ wrong_input = true; str = '0'; pos = 1;}
			
			// если строка содержит что-то кроме цифры или точки вырезаем этот символ
			var pattern = /[^\d]+/; 
			var result = pattern.exec(str);
		    if(result !== null){ 
			    wrong_input = true;
				var substr_arr = str.split(result[0]);
				pos =  substr_arr[0].length;
				str =  substr_arr[0] + substr_arr[1];
			    
		    }
			if(str.length>10){ wrong_input = true;  str = '1000000000'; pos = 10;}
			
		    // если был выявлен некорректный ввод исправляем содержимое ячейки 
			if(wrong_input) cell.innerHTML = str ;  
			
			return pos;
		}

		function setCaretToPos2(el, pos) {
		    var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], pos);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}


		return true;//parseInt(str);
    }
	,
	check:function(){// корректировка значений вводимых пользователем

		var cell = rtCalculator.cur_cell;
		
	    var result = correctToFloat(cell.innerHTML);
		
		//placeCaretAtEnd(cell);
		if(result != 0) setCaretToPos2(cell,result);
		rtCalculator.make_calculations(cell);
		
		
		
	    function correctToFloat(str){// корректировка значений вводимых пользователем в поле ввода типа Float
		    var wrong_input = false;
			var pos = 0;
			// если строка пустая правим на 0.00
			if(str == ''){ wrong_input = true; str = '0.00';}
			
			// если строка содержит запятую меняем её на точку
			var pattern = /,/; 
		    if(str.match(pattern)){ wrong_input = true;  pos =  str.indexOf(','); str =  str.replace(',','.');}
			
			// если строка содержит что-то кроме цифры или точки вырезаем этот символ
			var pattern = /[^\d\.]+/; 
			var result = pattern.exec(str);
		    if(result !== null){ 
			    wrong_input = true;
				var substr_arr = str.split(result[0]);
				pos =  substr_arr[0].length;
				str =  substr_arr[0] + substr_arr[1];
				
			    
		    }
			
			// если строка содержит более одной точки вырезаем оставляем только одну точку
			var pattern = /\./g; 
			var counter = 0;
			var result;
			while ((result = pattern.exec(str)) !== null) {
			  if(counter++>0){
				  wrong_input = true;
				  str =  str.replace('.','');
				  pos =  str.indexOf('.');
			  }
			 
			}
			//  если после точки введено менее или более 2 цифр исправляем до 2-х
			// ЗДЕСЬ НУЖНО РЕШИТЬ ВОПРОС УСТАНОВКИ КУРСОРА В НУЖНОЕ МЕСТО ПОКА ПЕРЕНОСИТСЯ В КОНЕЦ
			var pattern = /^\d+\.\d{2}$/; 
		    if(!str.match(pattern)){ wrong_input = true;  str = parseFloat(str).toFixed(2); pos = str.length;}
			
			// если величина числа больше допустимого - обрезаем его
		    if(str.length>12){ wrong_input = true;  str = '100000000.00'; pos = 12;}
		
			// если был выявлен некорректный ввод исправляем содержимое ячейки 
			if(wrong_input) cell.innerHTML = str;
			
			//alert(str);
			return pos; 
		}
		
		function setCaretToPos2(el, pos) {
		    var range = document.createRange();
			var sel = window.getSelection();
			range.setStart(el.childNodes[0], pos);
			range.collapse(true);
			sel.removeAllRanges();
			sel.addRange(range);
		}


		return true;//parseInt(str);
    }
	,
    make_calculations:function(cell){
	    // Когда в ячейке(поле ввода) в результате каких то действий происходит изменение содержимого нужно вызывать этот метод
		// метод производит калькуляцию текущих данных, и вычисляет разность текущих данных с теми которые были до изменения 

		// получаем id ряда
		var row_id = cell.parentNode.getAttribute('row_id');
		
		//**print_r(rtCalculator.tbl_model[row_id]);
		
		// сохраняем итоговые суммы ряда до изменения ячейки
		rtCalculator.previos_data['price_out'] = rtCalculator.tbl_model[row_id]['price_out'];
		rtCalculator.previos_data['price_in_summ'] = rtCalculator.tbl_model[row_id]['price_in_summ'];
		rtCalculator.previos_data['price_out_summ'] = rtCalculator.tbl_model[row_id]['price_out_summ'];
		rtCalculator.previos_data['in_summ'] = rtCalculator.tbl_model[row_id]['in_summ'];
		rtCalculator.previos_data['out_summ'] = rtCalculator.tbl_model[row_id]['out_summ'];
		rtCalculator.previos_data['discount'] = rtCalculator.tbl_model[row_id]['discount'];
		rtCalculator.previos_data['delta'] = rtCalculator.tbl_model[row_id]['delta'];
		rtCalculator.previos_data['margin'] = rtCalculator.tbl_model[row_id]['margin'];
		
		
		// вносим изменённое значение в соответствующую ячейку this.tbl_model
		var type = cell.getAttribute('type');
		rtCalculator.tbl_model[row_id][type] = (type=='quantity')? parseInt(cell.innerHTML):parseFloat(cell.innerHTML);
		
		// производим пересчет ряда
		rtCalculator.calculate_row(row_id,type);
		
		//**print_r(rtCalculator.tbl_model[row_id]);
		
		// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
		rtCalculator.change_html(row_id);

	}
	,
	makeQuantityCalculationsPreparing:function(cell){
	    // Когда в ячейке(поле ввода) в результате каких то действий происходит изменение содержимого нужно вызывать этот метод
		// метод производит калькуляцию текущих данных, и вычисляет разность текущих данных с теми которые были до изменения 

		// получаем id ряда
		var cur_tr = cell.parentNode;
		var row_id = cell.parentNode.getAttribute('row_id');
		
		//**print_r(rtCalculator.tbl_model[row_id]);
		
		// сохраняем итоговые суммы ряда до изменения ячейки
		rtCalculator.previos_data['print_in_summ'] = rtCalculator.tbl_model[row_id]['print_in_summ'];
		rtCalculator.previos_data['print_out_summ'] = rtCalculator.tbl_model[row_id]['print_out_summ'];
		rtCalculator.previos_data['dop_uslugi_in_summ'] = rtCalculator.tbl_model[row_id]['dop_uslugi_in_summ'];
		rtCalculator.previos_data['dop_uslugi_out_summ'] = rtCalculator.tbl_model[row_id]['dop_uslugi_out_summ'];
		rtCalculator.previos_data['price_in_summ'] = rtCalculator.tbl_model[row_id]['price_in_summ'];
		rtCalculator.previos_data['price_out_summ'] = rtCalculator.tbl_model[row_id]['price_out_summ'];
		rtCalculator.previos_data['in_summ'] = rtCalculator.tbl_model[row_id]['in_summ'];
		rtCalculator.previos_data['out_summ'] = rtCalculator.tbl_model[row_id]['out_summ'];
		rtCalculator.previos_data['discount'] = rtCalculator.tbl_model[row_id]['discount'];
		rtCalculator.previos_data['delta'] = rtCalculator.tbl_model[row_id]['delta'];
		rtCalculator.previos_data['margin'] = rtCalculator.tbl_model[row_id]['margin'];
		
	    
		// проверяем есть ли в ячейке расчеты нанесения
		var printsExists = false;
		var extraExists = false;
		var tds_arr = cur_tr.getElementsByTagName('td');
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type') && tds_arr[j].getAttribute('type') == 'print_exists_flag'){
				// отправляем запрос на сервер
				if(tds_arr[j].innerHTML == 'yes'){
					printsExists = true;
				}
			}
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('calc_btn') && tds_arr[j].getAttribute('calc_btn') == 'extra' && tds_arr[j].getAttribute('extra_exists_flag')){
					extraExists = true;
			}
			
		}
        //////////////////////////////////// card rt
		rtCalculator.makeQuantityCalculations('rt',cell.innerHTML,row_id,printsExists,extraExists,cell);
	}	
	,
	makeQuantityCalculations(source,quantity,row_id,printsExists,extraExists,cell){
	    if(printsExists || extraExists){// если есть нанесение или доп услуги то нужно отправлять запрос на сервер для обсчета нанесений в соответсвии с новым тиражом
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&change_quantity_and_calculators=1&quantity='+quantity+'&id='+row_id+'&print='+printsExists+'&extra='+extraExists+'&source='+source,'section');
			//alert(url);
		    rtCalculator.send_ajax(url,callbackprintsExists);
		}
		else{// отправляем запрос на изменение только лишь значения тиража в базе данных 
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('page=client_folder&change_quantity=1&quantity='+quantity+'&id='+row_id+'&source='+source,'section');
		    rtCalculator.send_ajax(url,callbackOnlyQuantity);
		}
						
		function callbackprintsExists(response){
		    // alert(response);
			
			try {  var response_obj = JSON.parse(response); }
			catch (e) {}
			
			if(response_obj){
				if(response_obj.warning || response_obj.warning=='size_exists'){
					// если найдено что позиция имеет какие-либо размеры изменение количества должно быть отменено
					// возвращаем в ячейку прежнее значение
					//alert(response_obj.warning);
					rtCalculator.sizeExistsDisclaimer(cell,row_id);
					cell.innerHTML = rtCalculator.tbl_model[row_id]['quantity'];
					return;
				}
			}
		
		
			if(response_obj.print && response_obj.print.lackOfQuantity){
				 var str =''; 
				 for(var index in response_obj.print.lackOfQuantity){
					 str += (parseInt(index)+1)+'). '+response_obj.print.lackOfQuantity[index].print_type+', мин тираж - '+response_obj.print.lackOfQuantity[index].minQuantity+"<br>";  
				 }
				 var dialog = $('<div>Тираж  меньше минимального тиража для нанесения(ний):<br>'+str+'стоимость будет пересчитана как для минимального тиража</div>');
				 $('body').append(dialog);
				 $(dialog).dialog({modal: true, width: 500,minHeight : 200, buttons: [{text: "Ok",click: function(){$(this).dialog("close"); }}]});
				 $(dialog).dialog('open');
				 
				 //alert("Тираж  меньше минимального тиража для нанесения(ний):\r"+str+"стоимость будет пересчитана как для минимального тиража");
			}
			if(response_obj.print && response_obj.print.outOfLimit){
				 var str ='';  
				 for(var index in response_obj.print.outOfLimit){
					 str += (parseInt(index)+1)+'). '+response_obj.print.outOfLimit[index].print_type+', лимит тиража - '+response_obj.print.outOfLimit[index].limitValue+"<br>";  
				 }
				 var dialog = $('<div>Все перерасчеты отклонены!!!<br>Потому что имеются нанесения для которых не возможно расчитать цену - достигнут лимит тиража :<br>'+str+'для этих нанесений требуется индивидуальный расчет</div>');
				 $('body').append(dialog);
				 $(dialog).dialog({modal: true, width: 500,minHeight : 200 , buttons: [{text: "Ok",click: function(){$(this).dialog("close"); }}], close: function( event, ui ) {location.reload();} });
				 $(dialog).dialog('open');
				 rtCalculator.changes_in_process=false;
				 return;
				 //alert("Все перерасчеты отклонены!!!\rПотому что имеются нанесения для которых не возможно расчитать цену - достигнут лимит тиража :\r"+str+"для этих нанесений требуется индивидуальный расчет");
			}
			if(response_obj.print && response_obj.print.needIndividCalculation){ 
				 var str ='';  
				 for(var index in response_obj.print.needIndividCalculation){
					 str += (parseInt(index)+1)+'). '+response_obj.print.needIndividCalculation[index].print_type+"\r";  
				 }
				 var dialog = $('<div>Все перерасчеты отклонены!!!<br>Потому что имеются нанесения для которых не возможно расчитать цену - для этих нанесений требуется индивидуальный расчет :<br>'+str+'</div>');
				 $('body').append(dialog);
				 $(dialog).dialog({modal: true, width: 500,minHeight : 200 , buttons: [{text: "Ok",click: function(){$(this).dialog("close"); }}] });
				 $(dialog).dialog('open');
				 // alert("Все перерасчеты отклонены!!!\rПотому что имеются нанесения для которых не возможно расчитать цену - для этих нанесений требуется индивидуальный расчет :\r"+str+"");
				
			}
			
			// если ответы ok для print и extra значит все нормально изменения сделаны 
			// вызываем функции производящие изменения в HTML
			if((response_obj.print && response_obj.print.result == 'ok') && (response_obj.extra && response_obj.extra.result == 'ok')){
			    if(source=='rt')rtCalculator.quantityCalculationsResponseFull(cell,row_id,response_obj);
			    if(source=='card')rtCalculator.cardQuantityCalculationsResponseFull(cell,row_id,response_obj);
			}
			else{
				// самый лучщий вариант иначе могут быть разные ошибки
				location.reload();
			}
			
		}
		function callbackOnlyQuantity(response){
			// alert(response);
		
			
			if(source=='rt')rtCalculator.quantityCalculationsResponse(cell,row_id,response);
			if(source=='card')rtCalculator.cardQuantityCalculationsResponse(cell,row_id,response);
			
		}
	}
	,
	cardQuantityCalculationsResponse:function(cell,row_id,response){
		alert(2);
	}
	,
	cardQuantityCalculationsResponseFull:function(cell,row_id,response_obj){
		response_rtCalculator_makeQuantityCalculations(cell,row_id,response_obj);
	}
	,
	quantityCalculationsResponseFull:function(cell,row_id,response_obj){
	        console.log(response_obj);
			
		    // Вносим изменения в hmlt
		
			rtCalculator.tbl_model[row_id]['quantity'] =  parseInt(cell.innerHTML) ;
			//// console.log(response_obj.new_sums);
			if(response_obj.print.new_sums){ 
				rtCalculator.tbl_model[row_id]["print_in_summ"] = parseFloat(response_obj.print.new_sums.summ_in);
				rtCalculator.tbl_model[row_id]["print_out_summ"] = parseFloat(response_obj.print.new_sums.summ_out);
				if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['print'] && (rtCalculator.tbl_model[row_id]['dop_data']['svetofor'] =='green' || rtCalculator.tbl_model[row_id]['dop_data']['svetofor'] =='sgreen')){
					rtCalculator.tbl_model['total_row']["print_in_summ"]  += rtCalculator.tbl_model[row_id]["print_in_summ"]-rtCalculator.previos_data['print_in_summ'];
					rtCalculator.tbl_model['total_row']["print_out_summ"] += rtCalculator.tbl_model[row_id]["print_out_summ"]-rtCalculator.previos_data['print_out_summ'];
				}
				
			}
			rtCalculator.tbl_model[row_id]["print_exists_flag"] = 'yes';
			
			if(response_obj.extra.new_sums){
				rtCalculator.tbl_model[row_id]["dop_uslugi_in_summ"] = parseFloat(response_obj.extra.new_sums.summ_in);
				rtCalculator.tbl_model[row_id]["dop_uslugi_out_summ"] = parseFloat(response_obj.extra.new_sums.summ_out);
				if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['dop'] && (rtCalculator.tbl_model[row_id]['dop_data']['svetofor'] =='green' || rtCalculator.tbl_model[row_id]['dop_data']['svetofor'] =='sgreen')){
					rtCalculator.tbl_model['total_row']["dop_uslugi_in_summ"]  += rtCalculator.tbl_model[row_id]["dop_uslugi_in_summ"]-rtCalculator.previos_data['dop_uslugi_in_summ'];
					rtCalculator.tbl_model['total_row']["dop_uslugi_out_summ"]  += rtCalculator.tbl_model[row_id]["dop_uslugi_out_summ"]-rtCalculator.previos_data['dop_uslugi_out_summ'];
				}
			}

			
			
			// производим пересчет ряда
			rtCalculator.calculate_row(response_obj.row_id,'quantity');
			
			//**print_r(rtCalculator.tbl_model[row_id]);
			
			// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
			rtCalculator.change_html(response_obj.row_id);
			
			rtCalculator.changes_in_process = false;
	}
	,
	quantityCalculationsResponse:function(cell,row_id,response){
		
		try {  var response_obj = JSON.parse(response); }
		catch (e) {}
		if(response_obj){
			if(response_obj.warning || response_obj.warning=='size_exists'){
				// если найдено что позиция имеет какие-либо размеры изменение количества должно быть отменено
				// возвращаем в ячейку прежнее значение
				// alert(response_obj.warning);
				rtCalculator.sizeExistsDisclaimer(cell,row_id);
				cell.innerHTML = rtCalculator.tbl_model[row_id]['quantity'];
				return;
			}
		}
		//alert('callbackOnlyQuantity');
		// вносим изменённое значение в соответствующую ячейку this.tbl_model
		rtCalculator.tbl_model[row_id]['quantity'] =  parseInt(cell.innerHTML) ;
		// производим пересчет ряда
		rtCalculator.calculate_row(row_id,'quantity');
		
		// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
		rtCalculator.change_html(row_id);
		
		rtCalculator.changes_in_process = false;
	}
	,
	calculate_row:function(row_id,type){
	    // метод который рассчитывает итоговые суммы конкретного ряда таблицы и если ряд не исключен из итоговых расчетов
		// делает изменения в ряду содержащем абсолютные суммы total_row
		// методу передается id затронутого ряда таблицы, дальше метод выделят этот ряд в модели таблицы rtCalculator.tbl_model
		// и рассчитывает его
		var row = rtCalculator.tbl_model[row_id];
		
		row['price_in_summ'] = row['quantity']*row['price_in'];
		row['price_out_summ'] = row['quantity']*row['price_out'];
		
		row['in_summ'] = row['price_in_summ'];
		if(!row['dop_data']['expel']['print']) row['in_summ'] +=row['print_in_summ'];
		if(!row['dop_data']['expel']['dop'])   row['in_summ'] +=row['dop_uslugi_in_summ'];
		
		row['out_summ'] = row['price_out_summ'];
		if(!row['dop_data']['expel']['print']) row['out_summ'] +=row['print_out_summ'];
		if(!row['dop_data']['expel']['dop'])   row['out_summ'] +=row['dop_uslugi_out_summ'];
		
		if(type=='price_out') rtCalculator.tbl_model[row_id]['discount'] = (row['discount']!=0)? ((row['price_out']!=0)?(Math.round(((row['price_out']*100/(rtCalculator.previos_data['price_out']*100/(100+row['discount'])))-100)* 100) / 100): 0): 0;
		//alert('('+row['price_out']+'*'+100+'/'+rtCalculator.previos_data['price_out']+')'+'-'+100);
		
		row['delta'] = row['out_summ']-row['in_summ'];
		row['margin'] = (row['out_summ']>0 && row['in_summ']>0)?((row['out_summ']-row['in_summ'])/row['out_summ'])*100:0;

		// если ряд не исключен из рассчетов расчитываем разницу появивщуюся в результате изменений и помещаем данные 
	    if(!row['dop_data']['expel']['main'] && (row['dop_data']['svetofor']=='green' || row['dop_data']['svetofor']=='sgreen')){
			rtCalculator.tbl_model['total_row']['price_in_summ'] += row['price_in_summ'] - rtCalculator.previos_data['price_in_summ'];
			rtCalculator.tbl_model['total_row']['price_out_summ'] += row['price_out_summ'] - rtCalculator.previos_data['price_out_summ'];
			rtCalculator.tbl_model['total_row']['in_summ'] += row['in_summ'] - rtCalculator.previos_data['in_summ'];
			rtCalculator.tbl_model['total_row']['out_summ'] += row['out_summ'] - rtCalculator.previos_data['out_summ'];
			rtCalculator.tbl_model['total_row']['delta'] +=  row['delta'] - rtCalculator.previos_data['delta'];
			//rtCalculator.tbl_model['total_row']['margin'] +=  row['margin'] - rtCalculator.previos_data['margin'];
		}
	}
	,
	change_html:function(row_id){
	
	    // метод который вносит изменения (итоги рассчетов в таблицу HTML)
		// alert(row_id);
		// вычисляем текущий ряд
		var trs_arr = rtCalculator.body_tbl.getElementsByTagName('tr');
		for(var i = 0;i < trs_arr.length;i++){
			if(trs_arr[i].hasAttribute && trs_arr[i].hasAttribute('row_id')){
				if(trs_arr[i].getAttribute('row_id') == row_id) var cur_tr = trs_arr[i];
			}
		}
			
			
		// внесение изменений в затронутый ряд
		var tds_arr = cur_tr.getElementsByTagName('td');
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
			    var type = tds_arr[j].getAttribute('type');
				var connected_vals = tds_arr[j].getAttribute('connected_vals');
				
				
				if(type == 'glob_counter' || type == 'dop_details' || type == 'master_btn' || type == 'name' || type == 'svetofor') continue;
				
				if(type=='quantity') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type];
				else if(type=='print_exists_flag') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type]; 
				else if(connected_vals=='print' || connected_vals=='uslugi') tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2)+'р'; 
				else if(type=='margin') tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2)+'%'; 
				else if(type=='discount') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type]+'%';
				else tds_arr[j].innerHTML = (rtCalculator.tbl_model[row_id][type]).toFixed(2); 
			    /*if(tds_arr[j].getAttribute('type') == 'in_summ') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id]['in_summ'];*/
			}
		}

		// если ряд не исключен из рассчетов внoсим изменения в итоговый ряд
	   // if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['main']){
			var tds_arr =this.tbl_total_row.getElementsByTagName('td');
			for(var j = 0;j < tds_arr.length;j++){
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					var connected_vals = tds_arr[j].getAttribute('connected_vals');
					//tds_arr[j].innerHTML = rtCalculator.tbl_model['total_row'][tds_arr[j].getAttribute('type')];
					//tds_arr[j].innerHTML = (type=='quantity')? rtCalculator.tbl_model['total_row'][type]:(rtCalculator.tbl_model['total_row'][type]).toFixed(2); 
					if(type == 'margin') continue;
					if(type=='quantity') tds_arr[j].innerHTML = rtCalculator.tbl_model['total_row'][type];
				    else if(connected_vals=='print' || connected_vals=='uslugi') tds_arr[j].innerHTML = (rtCalculator.tbl_model['total_row'][type]).toFixed(2)+'р'; 
					else tds_arr[j].innerHTML = (rtCalculator.tbl_model['total_row'][type]).toFixed(2); 
				}
			}
		//}
		
	}
	,
	expel_value_from_calculation:function(e){
		// метод исключающий или включающий значения из подсчетов
		// либо в текущих рядах, либо в окончательных суммах по всей таблице (итоговый ряд)
		
		// связано с состоянием интерфейса светофоров - поэтому слушаем его
		if(rtCalculator.change_svetofor.in_process) return; 

	    if(rtCalculator.expel_value_from_calculation.in_process) return; 
		rtCalculator.expel_value_from_calculation.in_process = true;
		
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		
		if(cell.getAttribute('expel') == undefined){ alert('attribute expel dont exists'); return;}
		
	    // получаем текущий статус ячейки и меняем его на противоположный
		var status = !(!!parseInt(cell.getAttribute('expel')));
		// alert(status+' '+cell.getAttribute('expel')+' '+cell.getAttribute('type'));
        
		var row_id = cell.parentNode.getAttribute('row_id');
		if(row_id == undefined) { alert('attribute row_id dont exists'); return;}
		if(row_id == 0) {  rtCalculator.expel_value_from_calculation.in_process = false; return;}  // прерываем выполнение - вспомогательный ряд
		//// console.log(row_id);
		
		var type = cell.getAttribute('type');
		
		// при отключении или включении ячеек НАНЕСЕНИЯ и ДОП УСЛУГ необходимо произвести перерасчет внутри ряда
		// если откючается весь ряд этого делать не нужно
		if(type=='print_out_summ' || type=='print_in_summ' || type=='dop_uslugi_out_summ' || type=='dop_uslugi_in_summ'){
		    // получаем значения входящей и исходящей суммы по данному типу ячейки
		    if(type=='print_out_summ' || type=='dop_uslugi_out_summ') var cur_out_summ = parseFloat(cell.innerHTML);
			if(type=='print_in_summ'  || type=='dop_uslugi_in_summ') var cur_in_summ = parseFloat(cell.innerHTML);
			// соседняя ячейка
			if(type=='print_out_summ' || type=='dop_uslugi_out_summ') var sibling_cell = cell.previousSibling;
			if(type=='print_in_summ'  || type=='dop_uslugi_in_summ') var sibling_cell = cell.nextSibling;
			
			while(sibling_cell != null){
				if(sibling_cell.nodeName == 'TD'){
					 if(type=='print_out_summ' || type=='dop_uslugi_out_summ')  var cur_in_summ  = parseFloat(sibling_cell.innerHTML);
			         if(type=='print_in_summ'  || type=='dop_uslugi_in_summ')  var cur_out_summ  = parseFloat(sibling_cell.innerHTML);
					 break;
				}
				if(type=='print_out_summ' || type=='dop_uslugi_out_summ') sibling_cell = sibling_cell.previousSibling;
			    if(type=='print_in_summ'  || type=='dop_uslugi_in_summ') sibling_cell = sibling_cell.nextSibling;
				
			}

			// получаем значения входящей и исходящей суммы по данному типу ячейки
			if(status){// исключить из расчетов
			    rtCalculator.tbl_model[row_id]['out_summ']-= cur_out_summ;
				rtCalculator.tbl_model[row_id]['in_summ'] -= cur_in_summ;
			}
			else{// использовать в расчетах
			    rtCalculator.tbl_model[row_id]['out_summ']+= cur_out_summ;
				rtCalculator.tbl_model[row_id]['in_summ'] += cur_in_summ;
			}
			// меняем значения delta и margin текущей ячейки
			rtCalculator.tbl_model[row_id]['delta'] = rtCalculator.tbl_model[row_id]['out_summ']-rtCalculator.tbl_model[row_id]['in_summ'];
			rtCalculator.tbl_model[row_id]['margin'] = (rtCalculator.tbl_model[row_id]['out_summ']>0 && rtCalculator.tbl_model[row_id]['in_summ']>0)?((rtCalculator.tbl_model[row_id]['out_summ']-rtCalculator.tbl_model[row_id]['in_summ'])/rtCalculator.tbl_model[row_id]['out_summ'])*100:0;    
		}
		
		// изменяем значение status в JS модели таблицы - rtCalculator.tbl_model
		if(type =='out_summ' || type=='in_summ') rtCalculator.tbl_model[row_id]['dop_data']['expel']['main'] = status;
		else if(type =='print_out_summ' || type=='print_in_summ') rtCalculator.tbl_model[row_id]['dop_data']['expel']['print'] = status;
		else if(type =='dop_uslugi_out_summ' || type=='dop_uslugi_in_summ') rtCalculator.tbl_model[row_id]['dop_data']['expel']['dop'] = status;
		
		// меняем значение status в HTML
		cell.setAttribute('expel',Number(status));
		
		// меняем значение аттрибута class во всех связанных ячейках
		var connected_vals = cell.getAttribute('connected_vals');
		var tdsArr = cell.parentNode.getElementsByTagName('td');
		for(var i = 0 ;i < tdsArr.length ;i++){
			 if(tdsArr[i].getAttribute('connected_vals') && tdsArr[i].getAttribute('connected_vals') == connected_vals){
			     if(status){  tdsArr[i].className =  tdsArr[i].className+' red_cell'; }
				 else{
					  var classArr = tdsArr[i].className.split(' ');
					  var newClassArr = [];
				      for(var index in classArr){ if(classArr[index] != "red_cell") newClassArr.push(classArr[index]); }
					  tdsArr[i].className =  newClassArr.join(' ');
				 }
			 }
		}
		
		// перебираем ячейки ряда с итоговыми суммами и суммируем значения соответсвующих ячеек в rtCalculator.tbl_model
		var total_row =  rtCalculator.tbl_model['total_row'];
		for(var type in total_row){
		    
			var summ = 0;
			// перебираем rtCalculator.tbl_model
			for(var id in rtCalculator.tbl_model){
			    // итоговый ряд пропускаем
				if(id =='total_row') continue;
				// если в ряд не участвует в расчете конечных сумм пропускаем его
				// также если ряд имеет не зеленый светофор
				if(rtCalculator.tbl_model[id]['dop_data']['expel']['main']) continue; 
				if(!(rtCalculator.tbl_model[id]['dop_data']['svetofor']=='green' || rtCalculator.tbl_model[id]['dop_data']['svetofor']=='sgreen')) continue; 
				//alert(id +' '+row_id+' '+type);
				var row = rtCalculator.tbl_model[id];
				
				for(var prop in row){
					if(prop==type){
						// если ячеки не участвуют в расчете конечных сумм пропускаем их
						if((type=='print_in_summ' || type=='print_out_summ' ) && row['dop_data']['expel']['print']) continue;
						if((type=='dop_uslugi_in_summ' || type=='dop_uslugi_out_summ' ) && row['dop_data']['expel']['dop']) continue;
						
						summ+=row[prop];
					} 
				}
			}
			total_row[type] = summ;
		}
		
	    // формируем url для AJAX запроса
	    var markers = {}
		for(var prop in rtCalculator.tbl_model[row_id]['dop_data']['expel']){
			 if(rtCalculator.tbl_model[row_id]['dop_data']['expel'][prop]) markers[prop] = "1";
		}

		var url = OS_HOST+'?' + addOrReplaceGetOnURL('expel_value_from_calculation='+JSON.stringify(markers)+'&id='+row_id);
		rtCalculator.send_ajax(url,callback);
		
		function callback(response){ /*alert(response);*/
			// вызываем метод производящий замену значений в HTML
			rtCalculator.change_html(row_id);
	
			rtCalculator.expel_value_from_calculation.in_process = false;
		}
	}
	,
	show_svetofor:function(e){ 
	   
	    e = e|| window.event;
		var img = e.target || e.srcElement;
		
		// проверяем не является ли изображение на котором был сделан клик тем же самым что было в последний раз если да проверяем не 
		// установленнна ли пауза, это все делается для того чтобы сделать некоторую паузу после закрытия всплывающего дива и 
		// его открытия по новой - важно при клике на маркер распологающийся над главной кнопкой.
	    if(rtCalculator.show_svetofor.last_img && rtCalculator.show_svetofor.last_img == img && rtCalculator.show_svetofor.pause) return; 
		rtCalculator.show_svetofor.last_img = img;
		//
		if(rtCalculator.show_svetofor.in_process) return; 
		rtCalculator.show_svetofor.in_process = true;
		
		var td = img.parentNode;

		// если еще не создана всплывающая планка с кнопками создаем её
		if(!rtCalculator.show_svetofor.plank){
			//// console.log('plank');
			var sourse_src = OS_HOST + '/skins/images/img_design/';
			
			var arr = ['red','green','grey','sgreen'];
            var plank = document.createElement('div');
			plank.className = 'svetofor_plank';
			$(plank).mouseleave(rtCalculator.hide_svetofor);
			
			for(var i = 0;i < arr.length;i++){ 
			   var img_btn = new Image();
			   img_btn.src = sourse_src + 'rt_svetofor_'+arr[i]+'.png';
			   img_btn.setAttribute("status",arr[i]);
			   img_btn.onclick = rtCalculator.change_svetofor;
			   plank.appendChild(img_btn);
			}
			// помещаем планку в переменную 
			rtCalculator.show_svetofor.plank = plank;
		}
	    $(td).mouseleave(rtCalculator.hide_svetofor);
		td.appendChild(rtCalculator.show_svetofor.plank);
	}
	,
	hide_svetofor:function(){ 
	
		if(rtCalculator.show_svetofor.plank.parentNode){// именно так - если у plank есть parentNode тоесть если он добавлен
		                                                //  куданибудь как Child то тогда его удаляем с помощью removeChild
			rtCalculator.show_svetofor.plank.parentNode.removeChild(rtCalculator.show_svetofor.plank);
		}
		if(rtCalculator.show_svetofor.in_process) rtCalculator.show_svetofor.in_process = false;
		
	}
	,
	change_svetofor:function(e){ 
	   
	    e = e|| window.event;
		var img_btn = e.target || e.srcElement;
		//// console.log(); 
        // связано с состоянием интерфейса исключения рядов - поэтому слущаем его
		if(rtCalculator.expel_value_from_calculation.in_process) return; 
		if(rtCalculator.change_svetofor.in_process) return; 
		rtCalculator.change_svetofor.in_process = true;
		
		var td = img_btn.parentNode.parentNode;
		var row_id = td.parentNode.getAttribute("row_id");
		var cur_status = td.getAttribute("svetofor");
		var new_status = img_btn.getAttribute("status");

        // проверяем не является ли новый статус sgreen если нет то проверяем не равен ли новый статус текущему статусу если да то
		// прекращаем выполнение метода потому что это ничего не меняет и не имеет смысла
		// но если новый статус равен sgreen то сверку не производим и продолжаем выполнение скрипта потому что sgreen воздействует
		// не только на свой ряд но и на другие в которых могли произоти какие то изменения
		if(new_status!= 'sgreen' && new_status == cur_status){
		    rtCalculator.change_svetofor.in_process = false;	
			return;
		}
		// alert(cur_status);
		
		if(new_status=='sgreen'){
			// собираем id остальных рядов относящихся к этой позиции для отправки на сервер чтобы отключить (нужные из них) в красный
			var parent_pos_id = rtCalculator.tbl_model[row_id].dop_data.parent_pos_id;
			// alert(parent_pos_id);
			// cобираем id-шники рядов относяшихся к данной товарной позиции
			var idsObj = {};
			var idsArr = [];
			for(var id in rtCalculator.tbl_model){
				if(rtCalculator.tbl_model[id].dop_data && rtCalculator.tbl_model[id].dop_data.parent_pos_id == parent_pos_id && id!=row_id){
					idsObj[id] = true;
					idsArr.push(id);
				}
			}
		}
		/* ДОДЕЛАТЬ 
		   - пересчет общей суммы для переназначенных
		*/
		
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_svetofor='+ new_status +'&id='+row_id+((idsArr && idsArr.length > 0)?'&idsArr='+JSON.stringify(idsArr):''));
		// alert(url);
		
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /*alert(response);*/
		   td.getElementsByTagName('img')[0].src = OS_HOST + '/skins/images/img_design/rt_svetofor_'+new_status+'.png';
		   td.setAttribute("svetofor",new_status);
		    // alert(new_status+' '+ typeof rtCalculator.tbl_model[row_id]['dop_data']['expel']['main']+' '+rtCalculator.tbl_model[row_id]['out_summ']);
			
			if(new_status=='green'){
				addToItog(row_id,cur_status);
				rtCalculator.tbl_model[row_id].dop_data.svetofor = new_status;
			}
			if(new_status == 'grey' || new_status == 'red'){  
				subtractFromItog(row_id,cur_status);
				rtCalculator.tbl_model[row_id].dop_data.svetofor = new_status;
			}
			if(new_status=='sgreen'){
				
				var tbl = document.getElementById('rt_tbl_body');
		        var trsArr = tbl.getElementsByTagName('tr');
	
		        // обходим ряды таблицы
				// В РЕЗУЛЬТАТЕ ОБХОДА, суммы всех рядов позиции, кроме текущего устанавливаемого в sgreen, 
				// при соответсвии условиям вычитаются из Итого
				forMark:
			    for( var i= 0 ; i < trsArr.length; i++){
					// проверяем входит ли расчет в расчеты данной позиции, причем текущий устанавливаемый не входит в idsObj
				    if(trsArr[i].hasAttribute('row_id') && idsObj[trsArr[i].getAttribute('row_id')]){
						var r_id = trsArr[i].getAttribute('row_id');
						var tdsArr = trsArr[i].getElementsByTagName('td');
						// проверяем какое текущее значение у светофора ряда если grey то не трогаем ряд, пропускаем его
						for( var j= 0 ; j < tdsArr.length; j++){
							 if(tdsArr[j].hasAttribute('svetofor')){
								 if(tdsArr[j].getAttribute('svetofor')=="grey") continue forMark;
								 
								 tdsArr[j].getElementsByTagName('IMG')[0].src = OS_HOST + '/skins/images/img_design/rt_svetofor_red.png';
								 var r_cur_status = tdsArr[j].getAttribute("svetofor");
						         tdsArr[j].setAttribute('svetofor','red');
								 rtCalculator.tbl_model[r_id].dop_data.svetofor = 'red';
					             break;
				             }
						}
						// alert(r_id+ ' '+cur_status+ ' '+r_cur_status);
						subtractFromItog(r_id,r_cur_status);
					}
			    }
				//суммы текущего устанавливаемого в sgreen ряда, при соответвии условиям прибавляются к Итого
				 addToItog(row_id,cur_status);
				 rtCalculator.tbl_model[row_id].dop_data.svetofor = new_status;
			}
			function addToItog(row_id,cur_status){
				// alert(cur_status);
				// РАСШИФРОВКА - если при новом статусе строка должна учитываться в Итого, при этом текущий статус grey || red 
				// (тоесть на данный момент не учитывается в Итого),и строка не исключена из расчета(тоесть должна учитываеться в Итого)
				// добавляем значения строки в Итого при этом надо учесть не исключены ли нанесения и доп услуги если да то их трогать 
				// не надо(потому что они уже не должны учитываются в  Итого
				if((cur_status == 'grey' || cur_status == 'red') && rtCalculator.tbl_model[row_id]['dop_data']['expel']['main'] != true ){
					// alert('add');
					rtCalculator.tbl_model['total_row']['out_summ'] += rtCalculator.tbl_model[row_id]['out_summ'];
					rtCalculator.tbl_model['total_row']['in_summ'] += rtCalculator.tbl_model[row_id]['in_summ']; 
					rtCalculator.tbl_model['total_row']['delta'] = rtCalculator.tbl_model['total_row']['out_summ'] - rtCalculator.tbl_model['total_row']['in_summ'];
			        //rtCalculator.tbl_model['total_row']['margin'] = (rtCalculator.tbl_model['out_summ']>0 && rtCalculator.tbl_model['in_summ']>0)?((rtCalculator.tbl_model['out_summ']-rtCalculator.tbl_model['in_summ'])/rtCalculator.tbl_model['in_summ'])*100:0;

					if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['print']){
						rtCalculator.tbl_model['total_row']["print_in_summ"] += rtCalculator.tbl_model[row_id]["print_in_summ"];
						rtCalculator.tbl_model['total_row']["print_out_summ"] += rtCalculator.tbl_model[row_id]["print_out_summ"];
					}
					if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['dop']){
						rtCalculator.tbl_model['total_row']["dop_uslugi_in_summ"] += rtCalculator.tbl_model[row_id]["dop_uslugi_in_summ"];
						rtCalculator.tbl_model['total_row']["dop_uslugi_out_summ"] += rtCalculator.tbl_model[row_id]["dop_uslugi_out_summ"];
					}
				}
			}
			function subtractFromItog(row_id,cur_status){
				// alert(cur_status);
				// РАСШИФРОВКА - если при новом статусе строка не должна учитываться в Итого, при этом текущий статус  green || sgreen 
				// (тоесть на данный момент учитывается в Итого), и строка не исключена из расчета (тоесть на данный момент учитывается в Итого)
				// вычитаем значения строки из Итого при этом надо учесть не исключены ли нанесения и доп услуги если да то их трогать 
				// не надо(потому что они уже не учитываются в  Итого		 
				if((cur_status == 'green' || cur_status == 'sgreen') && rtCalculator.tbl_model[row_id]['dop_data']['expel']['main'] != true ){
					// alert('subtract');
					rtCalculator.tbl_model['total_row']['out_summ'] -= rtCalculator.tbl_model[row_id]['out_summ'];
					rtCalculator.tbl_model['total_row']['in_summ'] -= rtCalculator.tbl_model[row_id]['in_summ']; 
				    rtCalculator.tbl_model['total_row']['delta'] = rtCalculator.tbl_model['total_row']['out_summ'] - rtCalculator.tbl_model['total_row']['in_summ'];
			        //rtCalculator.tbl_model['total_row']['margin'] = rtCalculator.tbl_model['total_row']['out_summ'] - rtCalculator.tbl_model['total_row']['in_summ'];
					if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['print']){
						rtCalculator.tbl_model['total_row']["print_in_summ"] -= rtCalculator.tbl_model[row_id]["print_in_summ"];
						rtCalculator.tbl_model['total_row']["print_out_summ"] -= rtCalculator.tbl_model[row_id]["print_out_summ"];
					}
					if(!rtCalculator.tbl_model[row_id]['dop_data']['expel']['dop']){
						rtCalculator.tbl_model['total_row']["dop_uslugi_in_summ"] -= rtCalculator.tbl_model[row_id]["dop_uslugi_in_summ"];
						rtCalculator.tbl_model['total_row']["dop_uslugi_out_summ"] -= rtCalculator.tbl_model[row_id]["dop_uslugi_out_summ"];
					}
				}
			}
			rtCalculator.change_html(row_id);/**/
			rtCalculator.change_svetofor.in_process = false;
		}
		
		rtCalculator.hide_svetofor();
		rtCalculator.show_svetofor.pause = true;
		setTimeout( pause, 300 );
		function pause(){ rtCalculator.show_svetofor.pause = false; }
	}
	,
	svetofor_display_relay:function(img_btn,certainRow){ 

		if(rtCalculator.svetofor_display_relay.in_process) return; 
		rtCalculator.svetofor_display_relay.in_process = true;

	    var status = img_btn.src.slice((img_btn.src.lastIndexOf('_')+1),img_btn.src.lastIndexOf('.'));
	    // alert(status);
		if(status =='on'){
		    var new_status = 'off';	
			var action = 'hide';
		}
		else{
			var new_status = 'on';	
			var action = 'show';
		}
		// alert(new_status+' - '+status);
		// определяем стартовый ряд
		if(certainRow) var start = img_btn.parentNode.parentNode;
		else{
			var start = rtCalculator.body_tbl.firstChild;
			if(start.nodeName == 'TBODY') start = start.firstChild;
		}
		//start = start.nextSibling;
		//alert(start.nodeName);
		
		var idsArr = [];
		// проходим по рядам таблицы и меняем отображение рядов
		fff:
		for( var tr = start ; tr != null ; tr = tr.nextSibling){ 
		     var target = false;
			 if(tr.getAttribute("pos_id")){
				 pos_row = tr;//
				 idsArr.push(tr.getAttribute("pos_id"));
			 }
			 // tr.style.backgroundColor = '#FF0000';
			 // if(tr.getAttribute("pos_id")) continue;
			 var tdsArr = tr.getElementsByTagName('td');
			 for(var j in tdsArr){
				 if(tdsArr[j].nodeName == 'TD'){
					// tdsArr[j].style.backgroundColor = '#FFFF00';//
					 if(tdsArr[j].getAttribute("svetofor")){
						 if(tdsArr[j].getAttribute("svetofor") == 'red') target = true;
						 //break;
					 }
				 }
			 }
		     if(target){
				 // обрабатываем текущий ряд
			     var curClassArr = tr.className.split(' ');
				 // alert(cur_display);
				 var newClass = (action == 'show')?'':'hidden';
				 // if(!certainRow && cur_display == new_display) continue;
				 for(var j in curClassArr){
					 if(curClassArr[j] == newClass) continue fff;
				 }
				 
				 tr.className = newClass;
				 
				 // производим изменения атрибута rowspan в ряду позиции (pos_row) иначе таблицу перекорежит
				 // в зависимости от того скрываем или открываем, нужно уменьшить или увеличить row_span в pos_row
				 if(typeof pos_row !=='undefined'){
					 var val = (action == 'show')?1:-1;
					 
					 var tdsArr = pos_row.getElementsByTagName('td');
					 for(var j in tdsArr){
						 if(tdsArr[j].nodeName == 'TD' && tdsArr[j].hasAttribute("rowspan")){
							row_span = parseInt(tdsArr[j].getAttribute("rowspan"))+val;
							tdsArr[j].setAttribute("rowspan",row_span);
							//alert(tdsArr[j].getAttribute("rowspan"));
						 }
						 if(tdsArr[j].nodeName == 'TD' && tdsArr[j].hasAttribute("svetofor_btn")){
							tdsArr[j].getElementsByTagName('IMG')[0].src = tdsArr[j].getElementsByTagName('IMG')[0].src.replace(status,new_status);
							//tdsArr[j].getElementsByTagName('IMG')[0].src.replace(status,new_status);
							//tdsArr[j].getElementsByTagName('IMG')[0].src = 'rt_svetofor_red.png';
						 }
					 }			 
				 }
			 }
			 //tr.style.backgroundColor = '#FF0000';//
			 img_btn.src = img_btn.src.replace(status,new_status);
			 if(certainRow && tr.nextSibling && tr.nextSibling.getAttribute("pos_id")) break;
			 
		}
		
		
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('svetofor_display_relay='+ new_status +'&ids='+idsArr.join("','"));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /*alert(response);*/
		    rtCalculator.svetofor_display_relay.in_process = false;
		}
		
		
		
	}
	,
	change_row_span:function(tr,val){ 
	    
	}
	,
	swich_cols:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		if(cell.nodeName=='SPAN') cell = cell.parentNode;
		
		var value =  cell.getAttribute("swiched_cols");

		var tds_arr = rtCalculator.head_tbl.getElementsByTagName('td');
		relay(tds_arr,value);
		var tds_arr = rtCalculator.body_tbl.getElementsByTagName('td');
		relay(tds_arr,value);
		function relay(tds_arr,value){
			for(var j in tds_arr){
				if(tds_arr[j].getAttribute){
					if(tds_arr[j].getAttribute('swiched_cols') && tds_arr[j].getAttribute('swiched_cols')==value){
						var stat = parseInt(tds_arr[j].getAttribute("c_stat"));
						var new_stat = (stat+1)%2;
						tds_arr[j].setAttribute("c_stat",new_stat);
						//// console.log(stat+' '+new_stat);
						var class_arr = tds_arr[j].className.split(' ');
						if(new_stat==1){
							var class_arr_clone = class_arr; 
							class_arr=[];
							for(var s in class_arr_clone) if(class_arr_clone[s]!='hidden')class_arr.push(class_arr_clone[s]);
						}
						else{
							class_arr.push('hidden');
						}
						tds_arr[j].className = class_arr.join(' ');
					}
				}
			}
		}
	}
	,
	get_active_rows:function(dop_params_obj){ 
	    
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор есть ли зеленые маркеры
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		var nothing = true;
		var pos_id = false;
		var idsObj = {};
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			var flag = false;
			
			// фильтруем по типу позиции (каталог, не каталог и т.п.) если равно указаному значению прерываем выполненин
			if(dop_params_obj && dop_params_obj.filter_glob_type_apart){
				if(trsArr[i].getAttribute('type') && trsArr[i].getAttribute('type')==dop_params_obj.filter_glob_type_apart){
					pos_id = false;
					continue;
				}
			}
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				console.log(pos_id);
				// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn'){
						if(inputs[j].checked != true) pos_id = false;
					}
					/*{
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
								 idsObj[pos_id] = {}; 
				    }
					else  pos_id = false;*/
				}
			}
			console.log(pos_id);
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие до нового ряда позици на нажатие зеленой кнопки
			// светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//// console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td');   
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor')){
						if(tdsArr[j].getAttribute('svetofor')=='green' || tdsArr[j].getAttribute('svetofor')=='sgreen'){
							if(typeof idsObj[pos_id] == 'undefined') idsObj[pos_id] = {};
							idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
							nothing = false;
				        }
						if(dop_params_obj && dop_params_obj.svetofor_dop_val && tdsArr[j].getAttribute('svetofor')==dop_params_obj.svetofor_dop_val){
							if(typeof idsObj[pos_id] == 'undefined') idsObj[pos_id] = {};
							idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
							nothing = false;
				        }
					}
				}
			}
		}
		return (nothing)? false : idsObj;
	}
	,
	get_active_main_rows:function(){ 
	    
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		var pos_id = false;
		var idsArr = [];
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn' && inputs[j].checked == true){
						idsArr.push(pos_id); 
				    }
				}
			}
		}
		return (idsArr.length>0)? idsArr : false ;
	}
	,
	get_positions_num_in_query:function(){ 

		var counter=0;
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			// если это ряд позиции
			if(trsArr[i].getAttribute('pos_id')) counter++;
		}
		return counter;
	}
	,
	copy_rows:function(e){ 
		
		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
		if(!(idsObj = rtCalculator.get_active_rows())){
            echo_message_js("Невозможно скопировать ряды, вы не выбрали ни одной позиции",'system_message',2000);
			return;
		} 
        
		show_processing_timer();
		/*console.log(idsObj); //return; */
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj));
		rtCalculator.send_ajax(url,callback);
		function callback(response){  /*console.log(response);  // */ close_processing_timer(); closeAllMenuWindows(); }
	}
	,
	copy_row:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		var pos_id = cell.getAttribute("pos_id");
		// собираем данные о расчетах присвоенных данному ряду и о том которые из них "зеленые"
		if(!(idsObj = rtCalculator.get_active_rows_for_one_position(pos_id))){
            echo_message_js("не возможно скопировать позицию, она не содержит активных расчетов",'system_message',2000);
			return;
		} 
		
		show_processing_timer();
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /* console.log(response);  // */   close_processing_timer(); closeAllMenuWindows();  if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF'; }
	}
	,
	get_active_rows_for_one_position:function(pos_id){ 
	    
		// обходим РТ 
		// собираем данные о расчетах присвоенных данному ряду и о том которые из них "зеленые"
		var idsObj = {};
		var goAhead = false;
		var nothing = true;
		var trsArr = this.body_tbl.getElementsByTagName('tr');
		for(var i = 0;i < trsArr.length;i++){
		    // если ряд не имеет атрибута row_id пропускаем его
		    if(!trsArr[i].getAttribute('row_id')) continue;
			
			
			if(trsArr[i].getAttribute('pos_id')){
				if(goAhead && trsArr[i].getAttribute('pos_id') != pos_id){
					goAhead=false;
				}
				
				// если встречается ряд позиции из которого было вызвано событие устанавливаем флаг в true
				if(trsArr[i].getAttribute('pos_id') == pos_id){
					goAhead = true;
				}
			}
			if(goAhead){
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td'); 
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && (tdsArr[j].getAttribute('svetofor')=='green' || tdsArr[j].getAttribute('svetofor')=='sgreen')){
						if(typeof idsObj[pos_id] == 'undefined') idsObj[pos_id] = {};
						idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						nothing = false;
					}
				}
			}
		}
		return (nothing)? false : idsObj;
	}
	,
	insert_copied_rows:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		var control_num = 1;
		if(cell.getAttribute('pos_id')) var place_id = cell.getAttribute('pos_id');
		if(rtCalculator.body_tbl.getAttribute('query_num')) query_num =  rtCalculator.body_tbl.getAttribute('query_num');
		else{
			echo_message_js("не удалось определить номер заявки",'system_message',2000);
			return;
		}
		
		show_processing_timer();
		//  
		// 1. Обращаемся к серверу, получаем данные из буфера(SESSIONS)
		// 2. Вставляем данные из буфера в базу данных на стороне сервера
		// 3. Получаем ответ об успешном действии
		// 4. Вносим изменения в HTML

		var url = OS_HOST+'?' + addOrReplaceGetOnURL('insert_copied_rows=1&query_num='+query_num+((typeof place_id != 'undefined')?'&place_id='+place_id:''));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ 
		
            close_processing_timer(); 
			closeAllMenuWindows();
			if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF';
			
			var data = JSON.parse(response);
			// alert(data[0]);
			if(data[0]==0){
				alert(data[1]);
				return;
			}/**/
			location.reload();
		}
	}
	,
	deleting:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
	    
		if(cell.getAttribute('pos_id')) var pos_id = cell.getAttribute('pos_id');
		if(cell.getAttribute('type')) var type = cell.getAttribute('type');
		
		var idsArr =[];
		
		// если есть pos_id то значит функция вызвана из контекстног меню - тоесть удаляем одну позицию
		// обходить ряды таблицы чтобы проверять мастер-кнопки не нужно 
        if(pos_id){
		    idsArr.push(pos_id);
		}
		else{// иначе обходим ряды таблицы
			 // определяем какие ряды были выделены (какие Мастер Кнопки были нажаты)
			if(!(idsArr = rtCalculator.get_active_main_rows())){
				if(type && type == 'prints') var target = 'нанесения';
				else if(type && type == 'uslugi') var target = 'доп услуги';
				else if(type && type == 'printsAndUslugi') var target = 'нанесения и доп услуги';
				else var target = 'ряды';
				echo_message_js('не возможно удалить '+target+', вы не выбрали ни одной позиции','system_message',2000);
				closeAllMenuWindows();
				return;
			} 
		}
		
		if(!confirm('программа удалит '+((pos_id)?'выбранную вами строку':'выбранные вами строки'))){
			closeAllMenuWindows();
			return;
		}
		// alert(idsArr.join(';'));
		show_processing_timer();
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('deleting='+JSON.stringify(idsArr)+((typeof type !== 'undefined')?'&type='+type:''));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ 
		    /* console.log(response); //  
			alert(response); */

            close_processing_timer(); 
			closeAllMenuWindows();
			if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF';
			
			var data = JSON.parse(response);
			// alert(data[0]);
			if(data[0]==0){
				alert(data[1]);
				return;
			}
			location.reload();
		}
	}
	,
	makeSpecAndPreorder:function(e){

		e = e || window.event;
		var element = e.target;
        
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор - должна быть нажата только одна зеленая кнопка (если больше или ни одна прерываемся)
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		var nothing = true;
		var pos_id = false;
		var idsObj = {};
		var dopInfObj = {};
		
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			var flag ;
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				/*// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn' && inputs[j].checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
								 idsObj[pos_id] = {}; 
				    }
					else pos_id = false;
				}*/
				
				var tdsArr = trsArr[i].getElementsByTagName('TD');
				
				for(var j =0; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'master_btn'){ 
						   var input = tdsArr[j].getElementsByTagName('input')[0];
						   if(input.type == 'checkbox' && input.name == 'masterBtn' && input.checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
										 idsObj[pos_id] = []; 
										 
							}
							else pos_id = false;
						}
						if(type == 'name'){ 
						   var article = tdsArr[j].getElementsByTagName('DIV')[0].getElementsByTagName('A')[0].innerHTML;
						   var name = tdsArr[j].getElementsByTagName('DIV')[tdsArr[j].getElementsByTagName('DIV').length-1].innerHTML;
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['name'] = article+' '+name;
						}
						if(type == 'glob_counter'){ 
						   var glob_counter = tdsArr[j].innerHTML;
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['glob_counter'] = glob_counter;
						}/**/
						
					}
				}
				
				
				
				
			}
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие до нового ряда позици на нажатие зеленой кнопки
			// светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td');   
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='green'){
						// idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						idsObj[pos_id].push(trsArr[i].getAttribute('row_id'));
						nothing = false;
					}
				}
			}
		}
		//console.log('--');
		//console.log(idsObj);
		
		// проверяем сколько зеленых кнопок светофора были нажаты и  в итоге были учтены
		var more_then_one = false;
		var less_then_one = false;
		var counter1 = 0;
		for(var index in idsObj){
            var counter2 = 0;
			for(var index2 in idsObj[index]){
				counter1++;
				counter2++;
			}
			
			if(counter1==0) less_then_one = true;
			if(counter2>1) more_then_one = true;
		}
		
		//var conrtol_num = getControlNum();
        //console.log(JSON.stringify(idsObj));
		//console.log(JSON.stringify(dopInfObj));
	    //return;
		
		if(nothing || more_then_one || less_then_one){
			if(nothing) alert('не возможно создать заказ,\rвы не выбрали ни одной позиции');
			else if(more_then_one){
				var alertStrObj ={};
				var alertStrArr =[];
				for(var pos in idsObj){
					if(idsObj[pos].length >1) alertStrObj[dopInfObj[pos]['glob_counter']] = dopInfObj[pos]['glob_counter']+'). '+dopInfObj[pos]['name']+'\r';
				}
				for(var i in alertStrObj){
					alertStrArr.push(alertStrObj[i]);
				}
				alert('не возможно создать заказ,\rвыбрано более одного варианта расчета в рядах:\r\n'+alertStrArr.join(''));
			}
			else if(less_then_one) alert('не возможно создать заказ,\rдля позиции(ий) невыбрано ни одного варианта расчета');
			return;
		}
		
	    show_processing_timer();
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
		if(client_id==''){
		   echo_message_js('не удалось определить клиента','system_message',2000);
		   return;
		}
		if(query_num==''){
		   echo_message_js('не удалось определить номер заявки','system_message',2000);
		   return;
		}
		

		location = "?page=agreement&section=presetting&client_id=" + client_id + "&ids=" +JSON.stringify(idsObj)+'&query_num='+query_num;
		
		
	    // формируем url для AJAX запроса
		/*var url = OS_HOST+'?' + addOrReplaceGetOnURL('makeSpecAndPreorder={"ids":'+JSON.stringify(idsObj)+',"client_id":"'+client_id+'","query_num":"'+query_num+'"}');
		// AJAX запрос
		make_ajax_request(url,callback);
		//alert(last_val);
		function callback(response){ 
		   
		    / *if(response == '1') location = OS_HOST+'?page=client_folder&section=business_offers&query_num='+query_num+'&client_id='+client_id;* /
		    console.log(response); 
			close_processing_timer(); closeAllMenuWindows();
		}	*/  

	}
	,
	sendToSnab:function(e){
		
		e = e || window.event;
		var element = e.target;
		
		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
        if(!(idsObj = rtCalculator.get_active_rows({"filter_glob_type_apart":"cat"/*нам нужны будут товары не относящиеся к каталогу (снаб не отвечает за каталог)*/,"svetofor_dop_val":"grey"}))){
			alert('не возможно отправить в снаб - не выбраны товары или расчеты');
			return;
		} 
		/*console.log(idsObj);return;  */
		
		show_processing_timer();
		
	    // формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('sendToSnab='+JSON.stringify(idsObj));
		// AJAX запрос
		make_ajax_request(url,callback);
		function callback(response){ 
		    //alert(response);
		    if(response == '1') location.reload();
		    /*console.log(response);*/ 
			close_processing_timer(); closeAllMenuWindows();
		}	  
	}
	,
	makeSpecAndPreorder2:function(e){

		e = e || window.event;
		var element = e.target;
        
		
		var tbl = document.getElementById('rt_tbl_body');
		var client_id = tbl.getAttribute('client_id');
		var query_num = tbl.getAttribute('query_num');
		if(client_id==''){ alert('не удалось определить клиента'); return;}
		if(query_num==''){ alert('не удалось номер заявки'); return; }
		
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		// 2. если Мастер Кнопка нажата проверяем светофор - должна быть нажата только одна зеленая кнопка (если больше или ни одна прерываемся)
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		var pos_id = false;
		var idsObj = {};
		var idsArr = [];
		var dopInfObj = {};
		var indexCounter = 0;
		
		
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			var flag ;
			
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				// работаем с рядом - ищем мастер кнопку
				var tdsArr = trsArr[i].getElementsByTagName('TD');
				
				for(var j =0; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'master_btn'){ 
						   var input = tdsArr[j].getElementsByTagName('input')[0];
						   if(input.type == 'checkbox' && input.name == 'masterBtn' && input.checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
							   idsObj[pos_id] = []; 
										 
							}
							else pos_id = false;
						}
						if(type == 'name'){ 
						   // если ячейка содержит более одного елемента значит это каталожный товар и в первом елементе 
						   // размещен артикул, для остальных товаров в этом(единственном и последнем) элементе размешено имя
						   var article = (tdsArr[j].getElementsByTagName('DIV').length>1)? tdsArr[j].getElementsByTagName('DIV')[0].getElementsByTagName('A')[0].innerHTML:'';
						   var name = $(tdsArr[j].getElementsByTagName('DIV')[tdsArr[j].getElementsByTagName('DIV').length-1]).text();
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['name'] = article+' '+name;
						}
						if(type == 'glob_counter'){ 
						   var glob_counter = tdsArr[j].innerHTML;
						   if(typeof dopInfObj[pos_id] ==='undefined') dopInfObj[pos_id]= {};
						   dopInfObj[pos_id]['glob_counter'] = glob_counter;
						}/**/
					}
				}
			}
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие, до нового ряда, 
			// позици на нажатие супер зеленой кнопки светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td');   
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='sgreen'){

						idsObj[pos_id].push(trsArr[i].getAttribute('row_id'));
						idsArr[indexCounter] = {pos_id:pos_id,row_id:trsArr[i].getAttribute('row_id')};
						indexCounter++;
					}
				}
			}
			
		}
		
		//console.log(idsObj);
		
		// проверяем сколько зеленых кнопок светофора были нажаты и в итоге были учтены
		var nothing = true; // если вообще ни однин светофор не был суперзеленым
		var more_then_one = false; // если больше одной в ряду
		var less_then_one = false; // если вообще ни однин светофор не был суперзеленым
		var counter1 = 0;
		for(var index in idsObj){
            var counter2 = 0;
			nothing = false;
			for(var index2 in idsObj[index]){
				counter1++;
				counter2++;
			}
			if(counter2>1) more_then_one = true;
		}
		if(counter1==0) less_then_one = true;
		//var conrtol_num = getControlNum();
        //console.log(JSON.stringify(idsObj));
		//console.log(JSON.stringify(dopInfObj));
	    //return;
		
		if(nothing || more_then_one || less_then_one){
			if(nothing) alert('не возможно создать спецификацию,\rвы не выбрали ни одной товарной позиции\r\rнеобходимо:\r1). отметить нужный товар галочкой в мастер-кнопке\r2). выделить один расчет для данного товара, выставив суперзеную кнопку в светофоре');
			else if(more_then_one){
				var alertStrObj ={};
				var alertStrArr =[];
				for(var pos in idsObj){
					if(idsObj[pos].length >1) alertStrObj[dopInfObj[pos]['glob_counter']] = dopInfObj[pos]['glob_counter']+'). '+dopInfObj[pos]['name']+'\r';
				}
				for(var i in alertStrObj){
					alertStrArr.push(alertStrObj[i]);
				}
				alert('не возможно создать спецификацию,\rвыбрано более одного варианта расчета в рядах:\r\n'+alertStrArr.join(''));
			}
			else if(less_then_one) alert('не возможно создать спецификацию,\rдля выбранных товаров не выбрано ни одного варианта расчета\r\rнеобходимо:\rвыделить один расчет для каждого выбранного товара выставив суперзеную кнопку в светофоре');
			return;
		}
		
		////////////
		// Промежуточный интерфейс настройки типа спецификации, дат и сроков изготовления
		//////////////////////////////////////////////////////////////////////////////////
		
		
		function launch_set_window(id,title,content){
			var box = document.createElement('DIV');
		    box.id = id;
		    box.style.display = "none";
		    box.appendChild(content);
		    document.body.appendChild(box);
		    $("#"+id).dialog({autoOpen: false ,title: title,modal:true,width: 600,close: function() {this.remove();$("#"+id).remove();}});
		    $("#"+id).dialog("open");
		}
		
		// 1. Выбор типа спецификации
		//var content = document.createDocumentFragment();
		var content = document.createElement('DIV');
		var winId ="specificationsPreWin1";
		content.innerHTML = '<div><label><input type="radio" name="radio" value="spec" checked>Спецификация</label><br><label><input type="radio" name="radio" value="oferta">Оферта</label></div>';
		var button = document.createElement('BUTTON');
		button.className="CommonRightBtn";

		var button1 = button.cloneNode();
		button1.onclick=function(){ $("#"+winId).remove(); step2();}
		button1.innerHTML ="Далее";
		var button2 = button.cloneNode();
		button2.onclick= function(){ $("#"+winId).remove(); }
		button2.innerHTML ="Отмена";
		
		content.appendChild(button1);
		content.appendChild(button2);
		
		launch_set_window(winId,"Выбор типа документа",content);
		
		function step2(e){
		    e = e || window.event;
		    var container = e.target.parentNode;
			var doc_type = '';
			$(container).find('input').each(function(key,val){ if(val.checked == true){  doc_type = val.value; }}); //alert(type);
			if(type != ''){
				// alert(doc_type);
			    var url = OS_HOST+'?' + addOrReplaceGetOnURL('getSpecificationsDates={"ids":'+JSON.stringify(idsArr)+'}');
		        make_ajax_request(url,callback);
				function callback(response){ 
					//alert(response);
					try {  var dataObj = JSON.parse(response); }
					catch (e) { 
						alert('неправильный формат данных in calculatorClass.makeSpecAndPreorder2() ошибка JSON.parse(response)');
						return;
					}
					console.log(dataObj);console.log(idsObj);console.log(dopInfObj);/**/
					
                    var content = document.createElement('DIV');
					content.className = "specificationsPreWin";
					var winId ="specificationsPreWin2";
					
					content.innerHTML += '<div class="cap">Укажите срок сдачи, либо срок изготовления Вашего заказ в рабочих днях.<div>'; 
					content.innerHTML += '<div class="info">В запрос были введены следующие даты и р/д:<br>(всего-'+dataObj['all_positions']+', установленно-'+dataObj['defined_positions']+')<div>';
					
                    var tbl = '<table id="preWindataTbl" class="dataTbl"><tr class="cap"><th>позиция</th><th>шаблон</th><th></th><th>кто</th><th></th></tr>';
				    for(var key in dataObj.data){
						 var value = (dataObj.data[key]['shablon_en']=='date')?((dataObj.data[key]['value'].split('-')).reverse()).join('.'):dataObj.data[key]['value'];
						 
						 
						 tbl += '<tr><td class="first">Арт № '+dopInfObj[dataObj.data[key]['row_id']]['glob_counter']+'</td>';
						 tbl += '<td>'+dataObj.data[key]['shablon']+'</td><td>'+value+'</td>';
						 tbl += '<td>'+dataObj.data[key]['who']+'</td>';
						 tbl += '<td>';
						 if(!(dataObj.data[key]['shablon_en']=='date' && dataObj.data[key]['value']<dataObj['min_allowed_date'])) tbl += '<input type="radio" name="radio" data_type="'+dataObj.data[key]['shablon_en']+'" value="'+value+'">';
						 tbl += '</td></tr>';
					}
				
					tbl += '<tr><td class="first"></td>';
					tbl += '<td>';
					tbl +='<select onchange="$(this).parent().parent().find(\'span\').hide(); $(\'#\'+this.options[this.selectedIndex].value).show(); $(\'#alternate_date\')[0].checked=true; " ><option value="" selected="selected"></option><option value="date_block">по дате</option><option value="rd_block">по рд</option></select></td>';					
					tbl +='<td><span id="date_block" style="display:none"><input type="text" class="datepicker" id="datepicker"><!--<input type="text" class="timepicker" id="timepicker">--></span>';
					tbl +='<span id="rd_block" class="rd" style="display:none"><input type="text" onkeyup="$(\'#alternate_date\')[0].checked=true; $(\'#alternate_date\')[0].setAttribute(\'data_type\',\'days\'); $(\'#alternate_date\')[0].value=this.value;" class="time_rd" id="rd" value=""> р/д</span></td>';
					tbl += '<td></td>';
					tbl += '<td><input id="alternate_date" type="radio" name="radio" data_type="" value=""></td></tr>';
					tbl += '</table>';
					content.innerHTML += tbl;

                    content.innerHTML+= '<BR>';
					var button1 = button.cloneNode();
					button1.onclick=function(){
						 var data_type='';
						 var value='';
						 $(content).find('input').each(function(key,val){ if(val.checked == true){ data_type= val.getAttribute("data_type"); value = val.value; }}); 
						 if(value==''){
							 alert('Вы не выбрали значение');
						     return;
					     }
						 $("#"+winId).remove(); 
						 step3(doc_type,data_type,value);/**/
					}
					button1.innerHTML ="ОК";
					var button2 = button.cloneNode();
					button2.onclick= function(){ $("#"+winId).remove(); }
					button2.innerHTML ="Отмена";
					content.appendChild(button1);
					content.appendChild(button2);
					launch_set_window(winId,"Выбор сроков сдачи заказа",content);
					
					$('#datepicker').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart: 1,startTime: new Date(0,0,0,15,0,0),minDate: new Date(dataObj['min_allowed_date']), onChangeDateTime: function(dp,$input){$('#alternate_date')[0].checked=true;$('#alternate_date')[0].setAttribute('data_type','date');$('#alternate_date')[0].value=$input.val();},closeOnDateSelect:true,
					   onGenerate:function( ct ){$(this).find('.xdsoft_date.xdsoft_weekend').addClass('xdsoft_disabled');$(this).find('.xdsoft_date');},allowTimes:['00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00','15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00']});
					/*$('#timepicker').datetimepicker({datepicker:false,format:'H:i',closeOnDateSelect:true,
						  onChangeDateTime:function(dp,$input){$('#alternate_date')[0].checked=true;$('#alternate_date')[0].setAttribute('data_type','date');$('#alternate_date')[0].value=$input.val()+':00';},allowTimes:['00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00','15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00']});*/
			
			    }
			}
			else{ alert('не удалось определить тип документа in calculatorClass.makeSpecAndPreorder2() '); return;}
			
			function step3(doc_type,data_type,datetime){
				
				// alert(doc_type+value+data_type);
				 
				 var content = document.createElement('DIV');
			     content.className = "specificationsPreWin";
				 var winId ="specificationsPreWin3";
				 // от той даты которая была выбрана на предыдушем шаге надо вычесть 3 рабочих дня
				 // они нужны на время для подписания макета и оплаты
				 var date = datetime.slice(0,10);
				 var time = datetime.slice(11,16);
				 time = (time!='')?time:'22:00';
					
				 if(data_type=='date'){
					 content.innerHTML += '<div class="cap"> укажите лимит<br>(до какого числа клиент обязуется оплатить заказ и подписать макет)<div>';
					 //content.innerHTML +='doc_type-'+ doc_type+' date-'+date+' time-'+time+' data_type-'+data_type+'<br>';
					 content.innerHTML += '<div class="limitInput"><input  id="datepicker" type="text"><input id="final_date" style="display:none" type="text"><div>';
					 
				 }
				 if(data_type=='days'){
					step4({'doc_type':doc_type,'data_type':data_type,'datetime':datetime});
					return;
				 }
				 var button1 = button.cloneNode();
				 button1.onclick=function(){
					 if($('#final_date')[0].value==''){echo_message_js('Установите дату','system_message',2000);return;} 
					 step4({'doc_type':doc_type,'data_type':data_type,'datetime':datetime,'final_date':$('#final_date')[0].value,'winId':winId});
				 }
				 button1.innerHTML ="Далее";
				 var button2 = button.cloneNode();
				 button2.onclick= function(){ $("#"+winId).remove(); }
				 button2.innerHTML ="Отмена";
				 content.appendChild(button1);
		         content.appendChild(button2);
		         //alert(datetime);
				 datetime =(((date.split('.')).reverse()).join('-'))+' '+time+':00'
				 //alert(datetime);
                 var pickerMaxDate = ((goOnNumWorkingDays(datetime,3,'-')).slice(0,10)).replace(/\-/g,'/');
				 //alert(pickerMaxDate);
				 launch_set_window(winId,"Установка лимита",content);
				 
				 $('#datepicker').datetimepicker({format:'d.m.Y H:i',dayOfWeekStart: 1,minDate:0,maxDate:pickerMaxDate,maxTime:time,
					   onChangeDateTime: function(dp,$input){$('#final_date')[0].value=$input.val();},closeOnDateSelect:true,
					   onGenerate:function( ct ){$(this).find('.xdsoft_date.xdsoft_weekend').addClass('xdsoft_disabled');$(this).find('.xdsoft_date');},allowTimes:['00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00','15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00']});
			}
			
			function step4(dataObj){
				 if(dataObj.winId) $("#"+dataObj.winId).remove();
				 //console.log(dataObj);
				 location = "?page=agreement&section=presetting&client_id=" + client_id + "&ids=" +JSON.stringify(idsArr)+'&query_num='+query_num+'&dateDataObj='+JSON.stringify(dataObj);
			}
		}
		
		return;
		// Всего 4 ситуации
		// промежуточное диалоговое окно 
		// Выводит информацию по типу создаваемой спецификации, и срокам выполнения заказа указываемым в спецификации
		// 1. Получение дат отгрузки товара или количества рабочих дней необходимых на изготовление исходя из установленных
		// в карточках товара
		// 2. формирование диалога на основании полученных данных
		//   2.1 - если хотя бы в одном товаре не указан срок изготовления в рабочих днях спецификация не может быть создана вообще
		//       2.1.1 если указаны разные значения используется большее значение
		//   2.2 если хотя бы в одном товаре указана точная календарная дата изготовления заказа оповещаем что будет создана 
		//       спецификация тип 2
		//     2.2.1 - если все даты истекли то останавливаем формирование спецификации и оповещаем 
		//     2.2.2 - если указаны разные даты то берем самую максимальную, при этом предлагаем выбрать другие установленные даты 
		//           при этом учитывая смогут ли они удовлетворить диапазону установленных рабочих дней   
		//     2.2.2 проверяем установленную дату и срок изготовления так чтобы они были валидными(чтобы дата минус срок 
		//           изготовления и другие доп дни  в итоге не были раньше текущего числа)
		//   3. В случае наличия точной даты определить и предложить оптимальную дату исходя из максимального значения срока 
		//      в рабочих днях

	}
	,
	show_discount_window:function(e){
	    
		e = e || window.event;
		var element = e.target;
		/*
		var client_id = (element.parentNode.parentNode.nodeName == 'TBODY')? element.parentNode.parentNode.parentNode.getAttribute('client_id'):element.parentNode.parentNode.getAttribute('client_id');
		*/
		var row_id = element.parentNode.getAttribute('row_id');
		
		var its_rt = (element.hasAttribute('its_rt'))? true:false;
		//alert(its_rt);
	    //alert(client_id);alert(row_id);
		
	    if(document.getElementById("BNODYUF0WE38")) document.getElementById("BNODYUF0WE38").parentNode.removeChild(document.getElementById("BNODYUF0WE38"));
	
	   // создаем всплывающее окно
	   up_window_consructor.setWindowDimentions(/*((its_rt)? 310:280)*/310,425)
	   
	   var arr = up_window_consructor.windowBilder('BNODYUF0WE38');
	   
	   /*  
	   var price_td = rtCalculator.certainTd(element,'price_out');
	   if(price_td == false){
		   alert('не удалось определить цену');
		   return;
	   }
	   var cur_price = price_td.innerHTML; */
	    
	   ///////////////////////////////////////////////////////
	   // содержимое сплывающего окна
	   ///////////////////////////////////////////////////////
	 
	   //элемент форма
	   var form = document.createElement("form");
	   form.method = "POST";
	   form.action = location;
	   
	   // div_float_left1
	   div_float_left1 = document.createElement("div");
	   div_float_left1.style.float ='left';
	   div_float_left1.style.margin ='10px 10px 15px 15px';
	   
	   div_float_left1.style.width ='250px';
	   div_float_left1.style.height ='200px';
	   // div_float_left1.style.border ='#BBBBBB solid 1px';
	   div_float_left1.style.borderRight ='#BBBBBB solid 1px';
	   
	   // div1
	   var div1 = document.createElement("div");
	   div1.style.marginRight ='25px';
	   div1.style.padding ='7px 5px 7px 5px';
	   div1.style.borderBottom ='#BBBBBB solid 1px';
	   
	   //поле row_id
	   var input_row_id = document.createElement("input"); 
	   input_row_id.type = 'hidden';
	   input_row_id.name = 'form_data[id]';
	   input_row_id.value = row_id;
	   
	   /*//поле client_id
	   var input_client_id = document.createElement("input"); 
	   input_client_id.type = 'hidden';
	   input_client_id.name = 'form_data[client_id]';
	   input_client_id.value = client_id;*/
	   
	   /*//поле cur_price
	   var input_cur_price = document.createElement("input"); 
	   input_cur_price.type = 'hidden';
	   input_cur_price.name = 'form_data[cur_price]';
	   input_cur_price.value = cur_price;*/
	   
	   //поле ввода цены
	   var price_input = document.createElement("input"); 
	   //price_input.style.border ='#BBBBBB solid 1px';
	   price_input.type = 'text';
	   price_input.name = 'form_data[new_price]';
	   price_input.style.marginLeft ='10px';
	   price_input.style.width = '50px';
	   price_input.style.height = '16px';
	   
	   // объединяем div1
	   div1.appendChild(input_row_id);  
	   //div1.appendChild(input_client_id);
	   //div1.appendChild(input_cur_price);
	   div1.appendChild(document.createTextNode("установить стоимость "));
	   div1.appendChild(price_input);
	   
	   
	   // div2
	   var div2 = document.createElement("div");
	   div2.style.marginRight ='25px';
	   div2.style.padding ='7px 5px 7px 5px';
	   div2.style.borderBottom ='#BBBBBB solid 1px';
	   
	   
	   var dop_div1 = document.createElement("div");
	   dop_div1.style.float ='left';
	   dop_div1.appendChild(document.createTextNode("присвоить"));
	   
	   //переключатели radio buttons (скидка или наценка)
	   var radio_type_action1 = document.createElement("input"); 
	   radio_type_action1.type = 'radio';
	   radio_type_action1.name = 'form_data[type_action]';
	   radio_type_action1.value = 'discount';
	   radio_type_action1.checked = 'true';
	   var type_action_label1 = document.createElement("label"); 
	   type_action_label1.style.cursor ='pointer';
	   type_action_label1.appendChild(radio_type_action1);
	   type_action_label1.appendChild(document.createTextNode("скидку"));
	   type_action_label1.appendChild(document.createElement("br"));
	   
	   var radio_type_action2 = document.createElement("input"); 
	   radio_type_action2.type = 'radio';
	   radio_type_action2.name = 'form_data[type_action]';
	   radio_type_action2.value = 'markup';
	   var type_action_label2 = document.createElement("label"); 
	   type_action_label2.style.cursor ='pointer';
	   type_action_label2.appendChild(radio_type_action2);
	   type_action_label2.appendChild(document.createTextNode("наценку"));
	   type_action_label2.appendChild(document.createElement("br"));
	   
	   var dop_div2 = document.createElement("div");
	   dop_div2.style.float ='left';
	   dop_div2.appendChild(type_action_label1);
	   dop_div2.appendChild(type_action_label2);

      //поле ввода процентов
	   var persent_input = document.createElement("input"); 
	   persent_input.type = 'text';
	   persent_input.name = 'form_data[percent]';
	   persent_input.style.marginLeft ='3px';
	   persent_input.style.width = '50px';
	   persent_input.style.height = '16px';

       var dop_div3 = document.createElement("div");
	   dop_div3.style.float ='left';
	   dop_div3.style.marginLeft ='10px';
	   dop_div3.style.marginTop ='8px';
	   dop_div3.appendChild(persent_input);
	   
	   var dop_div4 = document.createElement("div");
	   dop_div4.style.clear ='both';

	   // объединяем div2
	   div2.appendChild(dop_div1);
	   div2.appendChild(dop_div2);
	   div2.appendChild(dop_div3);
	   div2.appendChild(dop_div4);


	   // div3
	   var div3 = document.createElement("div");
	   div3.style.marginRight ='25px';
	   div3.style.padding ='7px 5px 7px 5px';
	   //div3.style.borderBottom ='#BBBBBB solid 1px';
	   
	   //переключатели radio buttons (для каких рядов произвести действие)
	   var input_radio1 = document.createElement("input"); 
	   input_radio1.type = 'radio';
	   if(!its_rt) input_radio1.style.display = 'none';
	   input_radio1.name = 'form_data[which_rows]';
	   input_radio1.value = 'one_row';
	   input_radio1.checked = 'true';
	   var label1 = document.createElement("label"); 
	   label1.style.cursor ='pointer';
	   if(!its_rt) label1.style.display = 'none';
	   label1.appendChild(input_radio1);
	   label1.appendChild(document.createTextNode("на данный расчет"));
	   label1.appendChild(document.createElement("br"));
	   
	   div3.appendChild(label1);
	   
	   if(its_rt){
		   var input_radio2 = document.createElement("input"); 
		   input_radio2.type = 'radio';
		   input_radio2.name = 'form_data[which_rows]';
		   input_radio2.value = 'all_in_pos';
		   var label2 = document.createElement("label");
		   label2.style.cursor ='pointer';
		   label2.appendChild(input_radio2);
		   label2.appendChild(document.createTextNode("на все расчеты в позиции"));
		   label2.appendChild(document.createElement("br"));
		   
		   
		   var input_radio3 = document.createElement("input"); 
		   input_radio3.type = 'radio';
		   input_radio3.name = 'form_data[which_rows]';
		   input_radio3.value = 'all_in_query';
		   var label3 = document.createElement("label"); 
		   label3.style.cursor ='pointer';
		   label3.appendChild(input_radio3);
		   label3.appendChild(document.createTextNode("на все позиции в заявке"));
		   label3.appendChild(document.createElement("br"));
		   
		   div3.appendChild(label2);
	       div3.appendChild(label3);
	   }

       // объединяем div4
	  
	  
	   //div3.appendChild(label4);
	   
	   // div4
	   var div4 = document.createElement("div");
	   div4.style.marginRight ='25px';
	   div4.style.padding ='7px 5px 7px 5px';
	   div4.style.borderBottom ='#BBBBBB solid 1px';

	   
	   // checkbox
	   var input_checkbox = document.createElement("input"); 
	   input_checkbox.type = 'checkbox';
	   input_checkbox.style.display ='inline';
	   input_checkbox.name = 'form_data[drop_discont]';
	   var checkbox_label = document.createElement("label"); 
	   checkbox_label.style.cursor ='pointer';
	   checkbox_label.appendChild(input_checkbox);
	   checkbox_label.appendChild(document.createTextNode(" сбросить скидку/наценку"));
	   
	   // объединяем div3
	   div4.appendChild(checkbox_label);	   

       div_float_left1.appendChild(div1);
	   div_float_left1.appendChild(div2);
	   div_float_left1.appendChild(div4);
	   div_float_left1.appendChild(div3);
	  
       form.appendChild(div_float_left1);
	 
	   //кнопки ok reset и отменить
	   var div_float_right1 = document.createElement("div"); // плавающий div контейнер
	   div_float_right1.style.float ='right';
	   div_float_right1.style.margin ='10px 0px 0px 0px';
	   div_float_right1.style.width ='98px';
	   div_float_right1.style.border ='#000000 solid 0px';
	 
       //кнопкa ok
	   var button_ok = document.createElement("input"); 
	   button_ok.type = 'submit';
	   button_ok.name = 'set_discount';
	   button_ok.value = 'ok';
	   button_ok.style.width = '90px';
	 
	   var button_ok_div = document.createElement("div");
	   button_ok_div.appendChild(button_ok);
	 
	   div_float_right1.appendChild(button_ok_div);
	 
       //кнопкa reset
	   var button_reset = document.createElement("input"); 
	   button_reset.type = 'reset';
	   button_reset.value = 'очистить';
	   button_reset.style.width = '90px';
	 
	   var button_reset_div = document.createElement("div");
	   button_reset_div.appendChild(button_reset);
	 
	   div_float_right1.appendChild(button_reset_div);
	 
       //кнопкa отменить
	   var button_escape = document.createElement("input"); 
	   button_escape.type = 'button';
	   button_escape.value = 'отменить';
	   button_escape.style.width = '90px';
	   button_escape.onclick = up_window_consructor.closeWindow;
	 
	   var button_escape_div = document.createElement("div");
	   button_escape_div.appendChild(button_escape);
	 
	   div_float_right1.appendChild(button_escape_div);
	 

	   
	   ///////////////////////////////////////////////////////
	   // end содержимое сплывающего окна
	   ///////////////////////////////////////////////////////
	 
	   // добавляем содержимое в таблицу в форму а затем в таблицу окна
	   form.appendChild(div_float_right1);
	   //form.appendChild(div2);
	   arr[2].childNodes[1].childNodes[1].childNodes[0].appendChild(form);
	 
	   // добавляем таблицу в окно
	   arr[1].appendChild(arr[2]);
	   arr[1].className = 'discount_window';
	   document.body.appendChild(arr[0]);
	   document.body.appendChild(arr[1]);
	   
	   return false;
	   
	   
   }
	,
	setSvetoforStatusIn:function(e){

		e = e || window.event;
		var element = e.target;
		
        var status = element.getAttribute('status');
		// обходим РТ чтобы 
		// 1. определить какие Мастер Кнопки были нажаты 
		
		var tbl = document.getElementById('rt_tbl_body');
		var trsArr = tbl.getElementsByTagName('tr');
		var nothing = true;
		var idsArr = [];
		
		// обходим ряды таблицы
		for( var i= 0 ; i < trsArr.length; i++){
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				var pos_id = trsArr[i].getAttribute('pos_id');
				
				var tdsArr = trsArr[i].getElementsByTagName('TD');
				
				for(var j =0; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'master_btn'){ 
						    var input = tdsArr[j].getElementsByTagName('input')[0];
						    if(input.type == 'checkbox' && input.name == 'masterBtn' && input.checked == true){
						 
							   idsArr.push(pos_id); 
							   nothing = false;
		 
							}
						}
					}
				}
			}
		}

		if(nothing){
			echo_message_js('не возможно применить ярлык, не выбрано ни одной позиции','system_message',2000);
			return;
		}

	    show_processing_timer();
		//location = "?page=client_folder&set_svetofor_status=" + status + "&ids=" +JSON.stringify(idsArr);
		location = '?' + addOrReplaceGetOnURL('set_svetofor_status='+status + "&ids=" +JSON.stringify(idsArr));
		//alert('?' + addOrReplaceGetOnURL('set_svetofor_status='+status + "&ids=" +JSON.stringify(idsArr)));
	
	}
	,
	send_ajax:function(url,callback){
		
		
	    //////////////////////////////////////////////////////////////////////////////////////////
		/////////////////////////////////////    AJAX  ///////////////////////////////////////////
		
		var request = HTTP.newRequest();
		
	   
	    // производим запрос
	    request.open("GET", url, true);
	    request.send(null);
	   
		request.onreadystatechange = function(){ // создаем обработчик события
		   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
			   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
				   ///////////////////////////////////////////
				   // обрабатываем ответ сервера

					
					var request_response = request.responseText;
				    //alert(request_response);
                    if(callback) callback(request_response);
				 
			    }
			    else{
				  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
			    }
		     }
	     }
		
		//////////////////////////////////////////////////////////////////////////////////////////	
	}
	,
	certainTd:function(node,type){ 
	   if(node==null)return false;
	   var node = node.nextSibling; 
	   return (node && node.nodeName=='TD' && node.getAttribute('type')  && node.getAttribute('type')==type) ? node : this.certainTd(node,type); 
	}
}