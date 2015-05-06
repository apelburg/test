// JavaScript Document
window.onload = function(){
   rt_calculator.init_tbl('rt_tbl');
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

var rt_calculator = {
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
	init_tbl:function(tbl_id){// метод запускаемый при наступлении события window.onload()
	                          // вызывает методы:
							  // collect_data - для создания модели таблицы
							  // set_editable_cells - для установки полей ввода
	    this.tbl = document.getElementById(tbl_id);
		//alert(this.tbl);
		this.collect_data();
		this.set_editable_cells();
	},
	set_editable_cells:function(){
	    // Этот метод устанавливает необходимым ячекам свойство contenteditable
		// и навешивает обработчик события onkeyup
		var tds_arr = this.tbl.getElementsByTagName('td');
	    for(var i in tds_arr){
		    if(tds_arr[i].getAttribute){
			    if(tds_arr[i].getAttribute('editable')){
					//tds_arr[i].onkeyup = this.make_calculations;
					tds_arr[i].onkeyup = this.check;
					tds_arr[i].setAttribute("contenteditable",true);
			    }
				if(tds_arr[i].getAttribute('expel')){
					tds_arr[i].onclick = this.expel_row_from_total;
				}
		    }
			
	    }
	}
	,
	check:function(e){
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		
		//alert(floatLengthToFixed (cell.innerHTML));
		if(cell.getAttribute('type') == 'quantity')  var result = correctToInt(cell.innerHTML);
		else  var result = correctToFloat(cell.innerHTML);
		
		//placeCaretAtEnd(cell);
		if(result != 0) setCaretToPos2(cell,result);
		rt_calculator.make_calculations(cell);
		
		
		
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
		
			/**/
			// если был выявлен некорректный ввод исправляем содержимое ячейки 
			if(wrong_input) cell.innerHTML = str;
			
			//alert(str);
			return pos; 
		}
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
    make_calculations:function(cell){
	    // Когда в ячейке(поле ввода) в результате каких то действий происходит изменение содержимого нужно вызывать этот метод
		// метод производит калькуляцию текущих данных, и вычисляет разность текущих данных с теми которые были до изменения 
		//e = e || window.event;
		//var cell = e.target || e.srcElement;
		// получаем id ряда
		var cur_tr = cell.parentNode;
		var row_id = cell.parentNode.getAttribute('row_id');
		
		//**print_r(rt_calculator.tbl_model[row_id]);
		
		// сохраняем итоговые суммы ряда до изменения ячейки
		rt_calculator.previos_data['price_in_summ'] = rt_calculator.tbl_model[row_id]['price_in_summ'];
		rt_calculator.previos_data['price_out_summ'] = rt_calculator.tbl_model[row_id]['price_out_summ'];
		rt_calculator.previos_data['in_summ'] = rt_calculator.tbl_model[row_id]['in_summ'];
		rt_calculator.previos_data['out_summ'] = rt_calculator.tbl_model[row_id]['out_summ'];
		rt_calculator.previos_data['delta'] = rt_calculator.tbl_model[row_id]['delta'];
		rt_calculator.previos_data['margin'] = rt_calculator.tbl_model[row_id]['margin'];
		
		
		// вносим изменённое значение в соответствующую ячейку this.tbl_model
		var type = cell.getAttribute('type');
		rt_calculator.tbl_model[row_id][type] = (type=='quantity')? parseInt(cell.innerHTML):parseFloat(cell.innerHTML);
	    
		// производим пересчет ряда
		rt_calculator.calculate_row(row_id);
		
		//**print_r(rt_calculator.tbl_model[row_id]);
		
		// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
		rt_calculator.change_html(cur_tr,row_id);

	}
	,
	calculate_row:function(row_id){
	    // метод который рассчитывает итоговые суммы конкретного ряда таблицы и если ряд не исключен из итоговых расчетов
		// делает изменения в ряду содержащем абсолютные суммы total_row
		// методу передается id затронутого ряда таблицы, дальше метод выделят этот ряд в модели таблицы rt_calculator.tbl_model
		// и рассчитывает его
		var row = rt_calculator.tbl_model[row_id];
		
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
	    if(!row['dop_data']['expel']['main']){
			rt_calculator.tbl_model['total_row']['price_in_summ'] += row['price_in_summ'] - rt_calculator.previos_data['price_in_summ'];
			rt_calculator.tbl_model['total_row']['price_out_summ'] += row['price_out_summ'] - rt_calculator.previos_data['price_out_summ'];
			rt_calculator.tbl_model['total_row']['in_summ'] += row['in_summ'] - rt_calculator.previos_data['in_summ'];
			rt_calculator.tbl_model['total_row']['out_summ'] += row['out_summ'] - rt_calculator.previos_data['out_summ'];
			rt_calculator.tbl_model['total_row']['delta'] +=  row['delta'] - rt_calculator.previos_data['delta'];
			rt_calculator.tbl_model['total_row']['margin'] +=  row['margin'] - rt_calculator.previos_data['margin'];
		}
	}
	,
	change_html:function(cur_tr,row_id){
	    // метод который вносит изменения (итоги рассчетов в таблицу HTML)
		
		// внесение изменений в затронутый ряд
		var tds_arr = cur_tr.getElementsByTagName('td');
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
			    var type = tds_arr[j].getAttribute('type');
			    tds_arr[j].innerHTML = (type=='quantity')? rt_calculator.tbl_model[row_id][type]:(rt_calculator.tbl_model[row_id][type]).toFixed(2); 
			    /*if(tds_arr[j].getAttribute('type') == 'in_summ') tds_arr[j].innerHTML = rt_calculator.tbl_model[row_id]['in_summ'];*/
			}
		}

		// если ряд не исключен из рассчетов внoсим изменения в итоговый ряд
	   // if(!rt_calculator.tbl_model[row_id]['dop_data']['expel']['main']){
			var tds_arr =this.tbl_total_row.getElementsByTagName('td');
			for(var j = 0;j < tds_arr.length;j++){
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					//tds_arr[j].innerHTML = rt_calculator.tbl_model['total_row'][tds_arr[j].getAttribute('type')];
					tds_arr[j].innerHTML = (type=='quantity')? rt_calculator.tbl_model['total_row'][type]:(rt_calculator.tbl_model['total_row'][type]).toFixed(2); 
				}
			}
		//}
		
	}
	,
	expel_row_from_total:function(e){// метод исключающий или включающий ряд в подсчете окончательных сумм по всей таблице (итоговый ряд)
	    if(rt_calculator.expel_row_from_total.in_process) return; 
		rt_calculator.expel_row_from_total.in_process = true;
		
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		
		if(cell.getAttribute('expel') == undefined){ alert('attribute expel dont exists'); return;}
		
	    // получаем текущий статус ячейки и меняем его на противоположный
		var status = !(!!parseInt(cell.getAttribute('expel')));
		//alert(status+' '+cell.getAttribute('expel')+' '+cell.getAttribute('type'));
        
		var row_id = cell.parentNode.getAttribute('row_id');
		if(row_id == undefined) { alert('attribute row_id dont exists'); return;}
		if(row_id == 0) {  rt_calculator.expel_row_from_total.in_process = false; return;}  // прерываем выполнение - вспомогательный ряд
		//alert(row_id);
		
		var type = cell.getAttribute('type');
		
		// при отключении или включении ячеек НАНЕСЕНИЯ и ДОП УСЛУГ необходимо произвести перерасчет внутри ряда
		// если откючается весь ряд этого делать не нужно
		if(type=='print_out_summ' || type=='dop_uslugi_out_summ'){
		    // получаем значения входящей и исходящей суммы по данному типу ячейки
		    var cur_out_summ = parseFloat(cell.innerHTML);
		    var cur_in_summ  = parseFloat(cell.previousSibling.innerHTML); // соседняя ячейка
			
			// получаем значения входящей и исходящей суммы по данному типу ячейки
			if(status){// исключить из расчетов
			    rt_calculator.tbl_model[row_id]['out_summ']-= cur_out_summ;
				rt_calculator.tbl_model[row_id]['in_summ'] -= cur_in_summ;
			}
			else{// использовать в расчетах
			    rt_calculator.tbl_model[row_id]['out_summ']+= cur_out_summ;
				rt_calculator.tbl_model[row_id]['in_summ'] += cur_in_summ;
			}
			// меняем значения delta и margin текущей ячейки
			rt_calculator.tbl_model[row_id]['delta'] = rt_calculator.tbl_model[row_id]['margin'] = rt_calculator.tbl_model[row_id]['out_summ']-rt_calculator.tbl_model[row_id]['in_summ'];

		}
		
		// изменяем значение status в JS модели таблицы - rt_calculator.tbl_model
		if(type =='out_summ') rt_calculator.tbl_model[row_id]['dop_data']['expel']['main'] = status;
		else if(type =='print_out_summ') rt_calculator.tbl_model[row_id]['dop_data']['expel']['print'] = status;
		else if(type =='dop_uslugi_out_summ') rt_calculator.tbl_model[row_id]['dop_data']['expel']['dop'] = status;
		
		// меняем значение status в HTML и меняем значение аттрибута class текущей ячейки
		cell.setAttribute('expel',Number(status));
	    cell.className = (status)? cell.className+' red_cell': cell.className.slice(0,cell.className.indexOf("red_cell")-1);
		
		// перебираем ячейки ряда с итоговыми суммами и суммируем значения соответсвующих ячеек в rt_calculator.tbl_model
		var total_row =  rt_calculator.tbl_model['total_row'];
		for(var type in total_row){
		    
			var summ = 0;
			// перебираем rt_calculator.tbl_model
			for(var id in rt_calculator.tbl_model){
			    // итоговый ряд пропускаем
				if(id =='total_row') continue;
				// если в ряд не участвует в расчете конечных сумм пропускаем его
				if(rt_calculator.tbl_model[id]['dop_data']['expel']['main']) continue; 
				//alert(id +' '+row_id+' '+type);
				var row = rt_calculator.tbl_model[id];
				
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
	    // вызываем метод производящий замену значений в HTML
		rt_calculator.change_html(cell.parentNode,row_id);

		rt_calculator.expel_row_from_total.in_process = false;
	}
	,
    collect_data:function(){
	    // метод считывающий данные таблицы РТ и сохраняющий их в свойство this.tbl_model 
	    this.tbl_model={};
	    var trs_arr = this.tbl.getElementsByTagName('tr');
	
		for(var i = 0;i < trs_arr.length;i++){
		    // если ряд не имеет атрибута row_id пропускаем его
		    if(!trs_arr[i].getAttribute('row_id')) continue;
			
			var row_id = trs_arr[i].getAttribute('row_id');
			
			// row_id==0 у вспомогательных рядов их пропускаем
			if(row_id==0) continue; //trs_arr[i].style.backgroundColor = '#FFFF00';
			
            if(row_id=='total_row') this.tbl_total_row = trs_arr[i];
			
			if(!this.tbl_model[row_id]) this.tbl_model[row_id] = {}; 
			
			// заносим информацию об исключении ряда из расчета
			/*if(row_id!='total_row'){
			    var expel = !!parseInt(trs_arr[i].getAttribute('expel'));
			    this.tbl_model[row_id].dop_data={'expel':expel};
			}*/
			
			var tds_arr = trs_arr[i].getElementsByTagName('td');
			for(var j = 0;j < tds_arr.length;j++){
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					this.tbl_model[row_id][type] = parseFloat(tds_arr[j].innerHTML);
					
					if(row_id!='total_row'){
					    if(tds_arr[j].getAttribute('expel')){
						    if(!this.tbl_model[row_id].dop_data)this.tbl_model[row_id].dop_data = {"expel":{}};
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
    execute:function(){
	    alert(2);
	}
}