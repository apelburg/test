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
   alert(1);
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
						else if(type=='print_out_summ'){
							this.tbl_model[row_id].dop_data.expel.print=expel;
						}
						else if(type=='dop_uslugi_out_summ'){
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
				        if(i == 0 && tds_arr[j].getAttribute('connected_vals')){// взаимно переключаемые ряды (ед/тираж, вход/выход)
						   tds_arr[j].onclick = this.relay_connected_cols;
					    }
					}
			    }	
		    }
		}
		
		var trs_arr = this.body_tbl.getElementsByTagName('tr');
		for(var i in trs_arr){
			if(trs_arr[i].getAttribute){
			    if(trs_arr[i].getAttribute("row_id")!='0'){
					var tds_arr = trs_arr[i].getElementsByTagName('td');
					for(var j in tds_arr){
						if(tds_arr[j].getAttribute){
							if(tds_arr[j].getAttribute('editable')){
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
									   rtCalculator.checkQuantity();
								   }
								   else{
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
							if(tds_arr[j].getAttribute('expel')){
								tds_arr[j].onclick = this.expel_value_from_calculation;
							}
							if(tds_arr[j].getAttribute('svetofor')){
								//// console.log(j+' svetofor');
								if(tds_arr[j].getElementsByTagName('img')[0]) $(tds_arr[j].getElementsByTagName('img')[0]).mouseenter(this.show_svetofor);
								
							}
							if(tds_arr[j].getAttribute('calc_btn')){
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
		// используем этот момент для отправки измененных данных в базу данных на сервер для синхронизации изменений ) 
		// либо при срабатывании таймера запускающегося при onkeyup в ячейке что позволяет отправлять данные из ячейки с некоторыим интервалом
		
		if(rtCalculator.complite_timer){
			 clearTimeout(rtCalculator.complite_timer);
			 rtCalculator.complite_timer = null;
		}
		 // console.log('№'+(++rtCalculator.complite_count));
		 // console.log(1);
		// получаем значение ячейки
		var last_val = rtCalculator.cur_cell.innerHTML;
		
		// сравниваем текущее значение с первоначальным, если они равны значит окончательные изменения не были произведены
		// в таком случае ничего не меняем в базе - прерываем дальнейшее выполнение
		if(rtCalculator.primary_val == last_val){
			rtCalculator.changes_in_process = false;
			return;
		}
		// console.log(rtCalculator.primary_val+' '+last_val);

		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_rt_changes={"id":"'+rtCalculator.cur_cell.parentNode.getAttribute('row_id')+'","prop":"'+rtCalculator.cur_cell.getAttribute('type')+'","val":"'+last_val+'"}');
		rtCalculator.send_ajax(url,callback);
		//alert(last_val);
		function callback(){ 
		    rtCalculator.changes_in_process = false;
		    /*cell.className = cell.className.slice(0,cell.className.indexOf("active")-1);*/
			// console.log(2);
		}
	}
	,
	checkQuantity:function(){// корректировка значений вводимых пользователем

		var cell = rtCalculator.cur_cell;
		//alert(cell.innerHTML);
		//alert(floatLengthToFixed (cell.innerHTML));
		var result = correctToInt(cell.innerHTML);

		if(result != 0) setCaretToPos2(cell,result);
		rtCalculator.makeQuantityCalculations(cell);
		
		
	
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
		rtCalculator.previos_data['price_in_summ'] = rtCalculator.tbl_model[row_id]['price_in_summ'];
		rtCalculator.previos_data['price_out_summ'] = rtCalculator.tbl_model[row_id]['price_out_summ'];
		rtCalculator.previos_data['in_summ'] = rtCalculator.tbl_model[row_id]['in_summ'];
		rtCalculator.previos_data['out_summ'] = rtCalculator.tbl_model[row_id]['out_summ'];
		rtCalculator.previos_data['delta'] = rtCalculator.tbl_model[row_id]['delta'];
		rtCalculator.previos_data['margin'] = rtCalculator.tbl_model[row_id]['margin'];
		
		
		// вносим изменённое значение в соответствующую ячейку this.tbl_model
		var type = cell.getAttribute('type');
		rtCalculator.tbl_model[row_id][type] = (type=='quantity')? parseInt(cell.innerHTML):parseFloat(cell.innerHTML);
		
		// производим пересчет ряда
		rtCalculator.calculate_row(row_id);
		
		//**print_r(rtCalculator.tbl_model[row_id]);
		
		// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
		rtCalculator.change_html(row_id);

	}
	,
	makeQuantityCalculations:function(cell){
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
		rtCalculator.previos_data['delta'] = rtCalculator.tbl_model[row_id]['delta'];
		rtCalculator.previos_data['margin'] = rtCalculator.tbl_model[row_id]['margin'];
		
	    
		// проверяем есть ли в ячейке расчеты нанесения
		var printsExitst = false;
		var extraExitst = false;
		var tds_arr = cur_tr.getElementsByTagName('td');
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type') && tds_arr[j].getAttribute('type') == 'print_exists_flag'){
				// отправляем запрос на сервер
				if(tds_arr[j].innerHTML == 'yes'){
					printsExitst = true;
				}
			}
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('calc_btn') && tds_arr[j].getAttribute('calc_btn') == 'extra' && tds_arr[j].getAttribute('extra_exists_flag')){
					extraExitst = true;
			}
			
		}
		
		if(printsExitst || extraExitst){// если нанесение есть то нужно отправлять запрос на сервер для обсчета нанесений в соответсвии с новым тиражом
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_quantity_and_calculators=1&quantity='+cell.innerHTML+'&id='+row_id+'&print='+printsExitst+'&extra='+extraExitst);
			//alert(url);
		    rtCalculator.send_ajax(url,callbackPrintsExitst);
		}
		else{// отправляем запрос на изменение только лишь значения тиража в базе данных 
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_quantity=1&quantity='+cell.innerHTML+'&id='+row_id);
		    rtCalculator.send_ajax(url,callbackOnlyQuantity);
		}
						
		function callbackPrintsExitst(response){
		    //alert(response);
			var response_obj = JSON.parse(response);
							
			if(response_obj.print.lackOfQuantity){
				 var str =''; 
				 for(var index in response_obj.print.lackOfQuantity){
					 str += (parseInt(index)+1)+'). '+response_obj.print.lackOfQuantity[index].print_type+', мин тираж - '+response_obj.print.lackOfQuantity[index].minQuantity+"\r";  
				 }
				 alert("Тираж  меньше минимального тиража для нанесения(ний):\r"+str+"стоимость будет пересчитана как для минимального тиража");
			}
			if(response_obj.print.outOfLimit){
				 var str ='';  
				 for(var index in response_obj.print.outOfLimit){
					 str += (parseInt(index)+1)+'). '+response_obj.print.outOfLimit[index].print_type+', лимит тиража - '+response_obj.print.outOfLimit[index].limitValue+"\r";  
				 }
				 alert("Все перерасчеты отклонены!!!\rПотому что имеются нанесения для которых не возможно расчитать цену - достигнут лимит тиража :\r"+str+"для этих нанесений требуется индивидуальный расчет");
			}
			if(response_obj.print.needIndividCalculation){ 
				 var str ='';  
				 for(var index in response_obj.print.needIndividCalculation){
					 str += (parseInt(index)+1)+'). '+response_obj.print.needIndividCalculation[index].print_type+"\r";  
				 }
				 alert("Все перерасчеты отклонены!!!\rПотому что имеются нанесения для которых не возможно расчитать цену - для этих нанесений требуется индивидуальный расчет :\r"+str+"");
				
			}
			
			// console.log(response_obj);
			// если ответ был ok значит все нормально изменения сделаны 
			// теперь нужно внести изменения в hmlt
			if(response_obj.print.result == 'ok' && response_obj.extra.result == 'ok'){
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
				rtCalculator.calculate_row(response_obj.row_id);
				
				//**print_r(rtCalculator.tbl_model[row_id]);
				
				// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
				rtCalculator.change_html(response_obj.row_id);
			}
			else{
				// самый лучщий вариант иначе могут быть разные ошибки
				location.reload();
			}
			rtCalculator.changes_in_process = false;
		}
		function callbackOnlyQuantity(response){
			//alert('callbackOnlyQuantity');
			// вносим изменённое значение в соответствующую ячейку this.tbl_model
			rtCalculator.tbl_model[row_id]['quantity'] =  parseInt(cell.innerHTML) ;
			// производим пересчет ряда
			rtCalculator.calculate_row(row_id);
			
			// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
			rtCalculator.change_html(row_id);
			
			rtCalculator.changes_in_process = false;
		}
	}
	,
	calculate_row:function(row_id){
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
		
		row['delta'] = row['margin'] = row['out_summ']-row['in_summ'];

		// если ряд не исключен из рассчетов расчитываем разницу появивщуюся в результате изменений и помещаем данные 
	    if(!row['dop_data']['expel']['main'] && (row['dop_data']['svetofor']=='green' || row['dop_data']['svetofor']=='sgreen')){
			rtCalculator.tbl_model['total_row']['price_in_summ'] += row['price_in_summ'] - rtCalculator.previos_data['price_in_summ'];
			rtCalculator.tbl_model['total_row']['price_out_summ'] += row['price_out_summ'] - rtCalculator.previos_data['price_out_summ'];
			rtCalculator.tbl_model['total_row']['in_summ'] += row['in_summ'] - rtCalculator.previos_data['in_summ'];
			rtCalculator.tbl_model['total_row']['out_summ'] += row['out_summ'] - rtCalculator.previos_data['out_summ'];
			rtCalculator.tbl_model['total_row']['delta'] +=  row['delta'] - rtCalculator.previos_data['delta'];
			rtCalculator.tbl_model['total_row']['margin'] +=  row['margin'] - rtCalculator.previos_data['margin'];
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
		if(type=='print_out_summ' || type=='dop_uslugi_out_summ'){
		    // получаем значения входящей и исходящей суммы по данному типу ячейки
		    var cur_out_summ = parseFloat(cell.innerHTML);
			// соседняя ячейка
			var sibling_cell = cell.previousSibling;
			while(sibling_cell != null){
				if(sibling_cell.nodeName == 'TD'){
					 var cur_in_summ  = parseFloat(sibling_cell.innerHTML);
					 break;
				}
				sibling_cell = sibling_cell.previousSibling;
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
			rtCalculator.tbl_model[row_id]['delta'] = rtCalculator.tbl_model[row_id]['margin'] = rtCalculator.tbl_model[row_id]['out_summ']-rtCalculator.tbl_model[row_id]['in_summ'];

		}
		
		// изменяем значение status в JS модели таблицы - rtCalculator.tbl_model
		if(type =='out_summ') rtCalculator.tbl_model[row_id]['dop_data']['expel']['main'] = status;
		else if(type =='print_out_summ') rtCalculator.tbl_model[row_id]['dop_data']['expel']['print'] = status;
		else if(type =='dop_uslugi_out_summ') rtCalculator.tbl_model[row_id]['dop_data']['expel']['dop'] = status;
		
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
			        rtCalculator.tbl_model['total_row']['margin'] = rtCalculator.tbl_model['total_row']['out_summ'] - rtCalculator.tbl_model['total_row']['in_summ'];
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
			        rtCalculator.tbl_model['total_row']['margin'] = rtCalculator.tbl_model['total_row']['out_summ'] - rtCalculator.tbl_model['total_row']['in_summ'];
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
	relay_connected_cols:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		if(cell.nodeName=='SPAN') cell = cell.parentNode;
		
		var value =  cell.getAttribute("connected_vals");

		var tds_arr = rtCalculator.head_tbl.getElementsByTagName('td');
		relay(tds_arr,value);
		var tds_arr = rtCalculator.body_tbl.getElementsByTagName('td');
		relay(tds_arr,value);
		function relay(tds_arr,value){
			for(var j in tds_arr){
				if(tds_arr[j].getAttribute){
					if(tds_arr[j].getAttribute('connected_vals') && tds_arr[j].getAttribute('connected_vals')==value){
						//// console.log(value+" "+tds_arr[j].getAttribute('connected_vals'));
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
	get_active_rows:function(){ 
	    
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
			var flag ;
			// если это ряд позиции проверяем не нажата ли Мастер Кнопка
			if(trsArr[i].getAttribute('pos_id')){
				pos_id = trsArr[i].getAttribute('pos_id');
				
				// работаем с рядом - ищем мастер кнопку 
				var inputs = trsArr[i].getElementsByTagName('input');
				for( var j= 0 ; j < inputs.length; j++){
					if(inputs[j].type == 'checkbox' && inputs[j].name == 'masterBtn' && inputs[j].checked == true){
						  // if(inputs[j].getAttribute('rowIdNum') && inputs[j].getAttribute('rowIdNum') !=''){inputs[j].getAttribute('rowIdNum')
								 idsObj[pos_id] = {}; 
				    }
					else pos_id = false;
				}
			}
			// если в ряду позиции была нажата Мастер Кнопка проверяем этот и последующие до нового ряда позици на нажатие зеленой кнопки
			// светофора (позиции для отправки в КП)
			if(pos_id!==false){
				//// console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td');   
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && (tdsArr[j].getAttribute('svetofor')=='green' || tdsArr[j].getAttribute('svetofor')=='sgreen')){
						idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						nothing = false;
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
			alert('не возможно скопировать ряды, вы не выбрали ни одной позиции');
			return;
		} 
		var control_num = 1;
		show_processing_timer();
		/*console.log(idsObj);return; */
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj)+'&control_num='+control_num);
		rtCalculator.send_ajax(url,callback);
		function callback(response){  /*console.log(response); //  */ close_processing_timer(); closeAllMenuWindows(); }
	}
	,
	copy_row:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		var pos_id = cell.getAttribute("pos_id");
		var control_num = 1;
		show_processing_timer();
		// собираем данные о расчетах присвоенных данному ряду и о том которые из них "зеленые"
		var idsObj = rtCalculator.get_active_rows_for_one_position(pos_id);
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj)+'&control_num='+control_num);
		rtCalculator.send_ajax(url,callback);
		function callback(response){  /* // console.log(response);*/   close_processing_timer(); closeAllMenuWindows();  if(openCloseContextMenuNew.lastElement) openCloseContextMenuNew.lastElement.style.backgroundColor = '#FFFFFF'; }
	}
	,
	get_active_rows_for_one_position:function(pos_id){ 
	    
		// обходим РТ 
		// собираем данные о расчетах присвоенных данному ряду и о том которые из них "зеленые"
		var idsObj = {};
		var goAhead = false;
		var trsArr = this.body_tbl.getElementsByTagName('tr');
		for(var i = 0;i < trsArr.length;i++){
		    // если ряд не имеет атрибута row_id пропускаем его
		    if(!trsArr[i].getAttribute('row_id')) continue;
			
			
			if(trsArr[i].getAttribute('pos_id')){
				if(goAhead && trsArr[i].getAttribute('pos_id') != pos_id){
					goAhead=false;
				}
				
				// если встречается ряд позиции из которого было вызвано событие , создаем объект в который будем добавлять возможные ряды расчетов
				if(trsArr[i].getAttribute('pos_id') == pos_id){
					idsObj[pos_id] = {};
					var goAhead = true;
				}
			}
			if(goAhead && idsObj[pos_id]){
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td'); 
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='green'){
						idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
					}
				}
			}
		}
		return idsObj;
	}
	,
	insert_copied_rows:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		var control_num = 1;
		if(cell.getAttribute('pos_id')) var place_id = cell.getAttribute('pos_id');
		if(rtCalculator.body_tbl.getAttribute('query_num')) query_num =  rtCalculator.body_tbl.getAttribute('query_num');
		else{
			alert('Не удалось определить номер заявки');
			return;
		}
		
		show_processing_timer();
		//  
		// 1. Обращаемся к серверу, получаем данные из буфера(SESSIONS)
		// 2. Вставляем данные из буфера в базу данных на стороне сервера
		// 3. Получаем ответ об успешном действии
		// 4. Вносим изменения в HTML

		var url = OS_HOST+'?' + addOrReplaceGetOnURL('insert_copied_rows=1&control_num='+control_num+'&query_num='+query_num+((typeof place_id != 'undefined')?'&place_id='+place_id:''));
		rtCalculator.send_ajax(url,callback);
		function callback(response){ 
		    /*alert(response);
		    console.log(response); //  
			 alert(response); */

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
				alert('не возможно удалить '+target+', вы не выбрали ни одной позиции');
				return;
			} 
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
		   alert('не удалось определить клиента');
		   return;
		}
		if(query_num==''){
		   alert('не удалось номер заявки');
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
	makeSpecAndPreorder2:function(e){

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
		var idsArr = [];
		var dopInfObj = {};
		var indexCounter = 0;
		
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
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='sgreen'){
						// idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						idsObj[pos_id].push(trsArr[i].getAttribute('row_id'));
						//idsArr[indexCounter].push({'"'+pos_id+'":'row_id'));
						//var val = {};
						//val[pos_id] = trsArr[i].getAttribute('row_id');
						//idsArr[indexCounter].push({pos_id:trsArr[i].getAttribute('row_id')});
						idsArr[indexCounter] = {pos_id:pos_id,row_id:trsArr[i].getAttribute('row_id')};
						indexCounter++;
						nothing = false;
					}
				}
			}
			
		}
		//console.log('--');
		//console.log(idsArr);
		
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
		   alert('не удалось определить клиента');
		   return;
		}
		if(query_num==''){
		   alert('не удалось номер заявки');
		   return;
		}
		
		location = "?page=agreement&section=presetting&client_id=" + client_id + "&ids=" +JSON.stringify(idsArr)+'&query_num='+query_num;
		
		
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
			alert('не возможно применить ярлык,\rвы не выбрали ни одной позиции');
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