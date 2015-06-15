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
	start_calculator:function(e){
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		// метод срабатывающий первым ( изначально ) при клике по значку обозначаещему нанесения в РТ
		
		//if(cell.parentNode.getAttribute('calc_btn') == 'print') alert('калькулятор нанесения логотипа');
		//if(cell.parentNode.getAttribute('calc_btn') == 'extra') alert('калькулятор доп. услуг');
		// определяем из какой ячейки сделан вызов калькулятора ( могут быть - нанесение или доп услуги)
		var calculator_type = cell.parentNode.getAttribute('calc_btn');
		
        // родительский тэг tr
		var trTag = cell.parentNode.parentNode;
		// id - артикула
		var art_id = trTag.getAttribute('art_id');
		// id - родительского ряда (ряда рассчета) (ряда в таблице os__rt_dop_data)
		var dop_data_row_id = trTag.getAttribute('row_id');
		
		// определяем количество товара (берем данные из ячейки quantity данного ряда)
		var tdsArr = trTag.getElementsByTagName('TD');
		//alert(tdsArr);
		for(var i =0;i < tdsArr.length;i++){
			if(tdsArr[i].getAttribute('type') && tdsArr[i].getAttribute('type')=='quantity'){
				var quantity = parseInt(tdsArr[i].innerHTML);
			} 
		}
		if(typeof quantity === 'undefined') alert('Не удается получить данные о количестве товара!!!');
		
		
		if(calculator_type == 'print'){
			// ДВА ЭТАПА
			// 1. отправляем запрос проверяющий есть ли расчеты дополнительных услуг для этого расчета
			//    если есть получаем в ответ массив с данными если нет получаем пустой массив
			// 2. если полученный массив имел данные выводим предварительное окно с указанием имеющихся
			//    расчетов дополнительных услуг, если массив был пустой вызываем метод запускающий калькулятор
			
			// этап 1
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('fetch_dop_uslugi_for_row='+dop_data_row_id);
			rtCalculator.send_ajax(url,callback);
			function callback(response){ 
				//console.log(response);
				// этап 1
				var response_data_arr = JSON.parse(response);
				
				if(response_data_arr.length == 0){
					
					// запускаем калькулятор
					rtCalculator.evoke_calculator({"art_id":art_id,"dop_data_row_id":dop_data_row_id,"quantity":quantity,"cell":cell});
				}
				else{
					// запускаем панель
					rtCalculator.launch_dop_uslugi_panel(response_data_arr,{"art_id":art_id,"dop_data_row_id":dop_data_row_id,"quantity":quantity,"cell":cell});
				}
			}
			/**/
		//alert(quantity);
		
		}
		else if(calculator_type == 'extra'){
		    alert('калькулятор доп. услуг');	
		}
		
	}
	,
	launch_dop_uslugi_panel:function(response_data_arr,data_obj_for_evoke_calculator){
		console.log('>>> response_data_arr start');
		console.log(response_data_arr);
		console.log('<<< response_data_arr end');
		
		var box = document.createElement('DIV');
		box.id = "calculatorDopUslugiBox";
		//box.style.width = '300px';
		box.style.display = "none";
		
		var tbl = document.createElement('TABLE');
		tbl.style.borderCollapse = 'Collapse';
		var tr = document.createElement('TR');
		rtCalculator.uslugi_panel_print_details = [];
		
		for(var i = 0; i < response_data_arr.length; i++){
			var tr =  tr.cloneNode(false);
			var td = document.createElement('TD');
			td.style.border = '#CCC solid 1px';
			td.style.padding = '2px 4px';

            // сохраняем данные в общей переменной
			// предварительно трансофорировав сериализованную строку из свойства "print_details" в объект
			var print_details = JSON.parse(response_data_arr[i].print_details);
			response_data_arr[i].print_details = print_details;
			rtCalculator.current_calculate_data = [];
			rtCalculator.current_calculate_data[i] = response_data_arr[i];
			rtCalculator.current_calculate_data[i].dop_uslugi_id = response_data_arr[i].id;
			
			
			td.innerHTML = i+1;
			
			//console.log(rtCalculator.current_calculate_data[i]);
			tr.appendChild(td);
			
			// тип нанесения
			var td =  td.cloneNode(false);
			td.innerHTML = rtCalculator.current_calculate_data[i].print_details.print_type;
			td.setAttribute('index',[i]);
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			tr.appendChild(td);
			td.onclick = function(){ 
				// запускаем калькулятор для конкретного нанесения
				$("#calculatorDopUslugiBox").remove();
				// добавляем в передаваемые данные данные индекс массива rtCalculator.current_calculate_data содержащего
				// данные конкретного(этого)нанесения
				data_obj_for_evoke_calculator.current_calculate_data_id = this.getAttribute('index');
				// запускаем
				rtCalculator.evoke_calculator(data_obj_for_evoke_calculator);
			}
			
			// место нанесения
			var td =  td.cloneNode(false);
			td.style.textDecoration = 'none';
			td.innerHTML = rtCalculator.current_calculate_data[i].print_details.place_type;
			tr.appendChild(td);
			
			// сумма
			var td =  td.cloneNode(false);
			td.innerHTML = rtCalculator.current_calculate_data[i].price_out*rtCalculator.current_calculate_data[i].quantity+' р.';
			tr.appendChild(td);
			
			var td =  td.cloneNode(false);
			td.innerHTML = 'Удалить нанесение';
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			td.setAttribute('usluga_id',response_data_arr[i].id);
			td.onclick = function(){ 
			
				// отправляем запрос на удаление для текущего нанесения
				var url = OS_HOST+'?' + addOrReplaceGetOnURL('delete_prints_for_row='+data_obj_for_evoke_calculator.dop_data_row_id+'&usluga_id='+this.getAttribute('usluga_id'));
				rtCalculator.send_ajax(url,callback);
			
				function callback(response){ 
				    console.log(response);
					$("#calculatorDopUslugiBox").remove();
					location.reload();
				}
			}
			
			tr.appendChild(td);
			tbl.appendChild(tr);
			
		}
		box.appendChild(tbl);
		
	    // кнопка добавления нового нанесения
		var addNewPrintBtn = document.createElement('DIV');
		addNewPrintBtn.id = 'calculatorAddNewPrintBtn';
		addNewPrintBtn.style.border = '#CCC solid 1px';
		addNewPrintBtn.style.margin = '20px 1px 1px 50px';
		addNewPrintBtn.style.padding = '10px 20px';
		addNewPrintBtn.style.width = '200px';
		addNewPrintBtn.style.cursor = 'pointer';
		addNewPrintBtn.innerHTML = 'Добавить еще место';
		addNewPrintBtn.onclick =  function(){ 
		    $("#calculatorDopUslugiBox").remove();
		    rtCalculator.evoke_calculator(data_obj_for_evoke_calculator);
	    };
		box.appendChild(addNewPrintBtn);
		
		// кнопка удаления всех нанесений
		var deleteAllPrinstBtn = document.createElement('DIV');
		deleteAllPrinstBtn.id = 'calculatorDeleteAllPrinstBtn';
		deleteAllPrinstBtn.style.border = '#CCC solid 1px';
		deleteAllPrinstBtn.style.margin = '20px 1px 1px 50px';
		deleteAllPrinstBtn.style.padding = '10px 20px';
		deleteAllPrinstBtn.style.width = '200px';
		deleteAllPrinstBtn.style.cursor = 'pointer';
		deleteAllPrinstBtn.innerHTML = 'Удалить все места печати';
		deleteAllPrinstBtn.onclick =  function(){ 
		    $("#calculatorDeleteAllPrinstBtn").remove();
			
			// отправляем запрос на удаление всех нанесений для текущего расчета
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('delete_prints_for_row='+data_obj_for_evoke_calculator.dop_data_row_id+'&all=true');
		    rtCalculator.send_ajax(url,callback);
		
			function callback(response){ 
				$("#calculatorDopUslugiBox").remove();
				location.reload();
			}
	    };
		box.appendChild(deleteAllPrinstBtn);
		
		
		
		document.body.appendChild(box);
		// открываем панель
		$("#calculatorDopUslugiBox").dialog({autoOpen: false, position:{ at: "top+35%", of: window } ,title: "Печать для этой позиции",modal:true,width: 600,close: function() {this.remove();$("#calculatorDopUslugiBox").remove();}});
		$("#calculatorDopUslugiBox").dialog("open");
	}
	,
	evoke_calculator:function(data_obj){
		
		/* УДАЛИТЬ 
		// если калькулятор был вызван для конкретного существующего расчета нанесения
		// сохраняем детали этого нанесения в переменную 
		// иначе устанавливаем её в false concrete_print_data_obj
		if(data_obj.concrete_print_data) var concrete_print_data_obj = data_obj.concrete_print_data;
		else  var concrete_print_data_obj = false;
		var data = 'default';*/
		
		
		// отправляем запрос чтобы получить описание параметров возможного калькулятора для данного ариткула
	    var url = OS_HOST+'?' + addOrReplaceGetOnURL('grab_calculator_data={"art_id":"'+data_obj.art_id+'","type":"'+data_obj.cell.parentNode.getAttribute('calc_btn')+'"}');
		rtCalculator.send_ajax(url,callback);
		//alert(last_val);
		function callback(response){ 
			console.log(response);
			
			// строим калькулятор
			rtCalculator.build_print_calculator(data_obj,response);
		    
			// открываем окно с калькулятором
			$("#calculatorBox").dialog({autoOpen: false, position:{ at: "top+25%", of: window } ,title: "Расчет с нанесением логотипа",modal:true,width: 600,close: function() {this.remove();$("#calculatorBox").remove();}});
			$("#calculatorBox").dialog("open");
		}
	}
	,
    build_print_calculator:function(data_obj,response_data){

		
		// если калькулятор был вызван для существующего нанесения пересохраняем данные для конкретного нанесения 
		// иначе готовим структуру для сохранения данных при создании калькулятора 
	    if(data_obj.current_calculate_data_id){
			rtCalculator.current_calculate_data =  rtCalculator.current_calculate_data[data_obj.current_calculate_data_id];
			rtCalculator.current_calculate_data.dop_data_row_id = data_obj.dop_data_row_id;
		}
		else{
		    rtCalculator.current_calculate_data =  {};	
			rtCalculator.current_calculate_data.quantity = data_obj.quantity;
		    rtCalculator.current_calculate_data.dop_data_row_id = data_obj.dop_data_row_id;
			rtCalculator.current_calculate_data.print_details = {};
		}
		
		console.log('>>> build_print_calculator');
		console.log(rtCalculator.current_calculate_data);
		console.log('<<< build_print_calculator');
	  
		// строим интерфейс калькулятора
		rtCalculator.calculate_params_obj = JSON.parse(response_data);
		// структура элемента data.places
		// [places] => Array([0] => ключ соответсвует id места нанесения (если id = 0 - "Стандартное" место )неограниченное количество элементов
		//					Array (
		//						  [name] => "Стандартно" или "грудь (00х00)" - строка описывающая место нанесения
		//						  [data] => Array([0] => 13,[1] => 23) массив значения - id видов нанесения 
		//					       )
        //                   )
        var br = document.createElement('BR');
		
		var box = document.createElement('DIV');
		box.id = "calculatorBox";
		//box.style.width = '300px';
		box.style.display = "none";
		
		box.appendChild(document.createTextNode("Тираж "+rtCalculator.current_calculate_data.quantity+' шт.'));
		box.appendChild(br.cloneNode(true));
		box.appendChild(br.cloneNode(true));
		
		// select выбора мест нанесений
		var printPlaceSelect = document.createElement('SELECT');
		printPlaceSelect.onchange = function(){
			rtCalculator.current_calculate_data.dop_params.place_id = parseInt(this.options[this.selectedIndex].value);
			//alert(place_id);
			var block_A = rtCalculator.buildBlockA(rtCalculator.current_calculate_data.dop_params.place_id);
			if(document.getElementById("rtCalculatorBlockA"))document.getElementById("rtCalculatorBlockA").parentNode.removeChild(document.getElementById("rtCalculatorBlockA"));
			this.parentNode.appendChild(block_A);
			
		}
		//console.log(rtCalculator.calculate_params_obj.places);
		for(var id in rtCalculator.calculate_params_obj.places){
			// если это заново запускаемый калькулятор сохраняем id первого места нанесения 
			if(typeof rtCalculator.current_calculate_data.print_details.place_id === 'undefined') rtCalculator.current_calculate_data.print_details.place_id = id;
           
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(rtCalculator.calculate_params_obj.places[id].name));
            printPlaceSelect.appendChild(option);
			
			if(rtCalculator.current_calculate_data.print_details.place_id==id) option.setAttribute("selected",true);
			//console.log(i + data_obj.places[i].name);
		}
		//currPlace_id = 1;
		box.appendChild(printPlaceSelect);
		
		// создаем блок block_A который будет содеражать в себе select выбора типа нанесения
		// и блок block_B содержащий в себе все остальные элементы интерфейса
	    var block_A = rtCalculator.buildBlockA();
		
		
		box.appendChild(block_A);
		document.body.appendChild(box);
		
		//if(concrete_print_data_obj)rtCalculator.makeProcessing();
		
		// help button
		// box.appendChild(help.btn('kp.sendLetter.window'));
		
	}
	,
	buildBlockA:function(){
		// метод строит блок block_A и взависимости от ситуации
		// или ( строит и вставляет в него block_B ) или ( не делает этого )
		
		var block_A = document.createElement('DIV');
		block_A.id = 'rtCalculatorBlockA';
		var br = document.createElement('BR');
		// вызваем метод строящий  select для типов нанеснения
		// передаем ему id первого места нанесения из printPlaceSelect
		// он возвращает select и id типа нанесения первого в списке select
		var printTypesSelect = rtCalculator.build_print_types_select();
		
		block_A.appendChild(br.cloneNode(true));
		block_A.appendChild(br.cloneNode(true));
		block_A.appendChild(printTypesSelect);
		
		//alert(rtCalculator.current_calculate_data.print_details.print_id);
		// если мы имеем конкретное типа нанесения (тоесть оно не равно 0) тогда строим калькулятор дальше
		// вызываем метод строящий блок В калькулятора и вставляем его в тело калькулятора
		if(rtCalculator.current_calculate_data.print_details.print_id != 0){	
		    var block_B = rtCalculator.buildBlockB();
		    block_A.appendChild(block_B);
		}

		return block_A;
	}
	,
	buildBlockB:function(){
		
		var blockB = document.createElement('DIV');
		blockB.id = 'rtCalculatorBlockB';
		var br = document.createElement('BR');
		// выбираем данные выбранного нанесения и выводим их в калькулятор
		var currRrintParams = rtCalculator.getCurrPrintParams();

		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(currRrintParams);
	
		// дисплей итоговых подсчетов
		var itogDisplay = document.createElement('DIV');
		itogDisplay.id = 'rtCalculatorItogDisplay';
		
		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(itogDisplay);
		
		// кнопка сохранения данных в таблицу на сервер
		var saveBtn = document.createElement('DIV');
		saveBtn.id = 'calculatorsaveResultBtn';
		saveBtn.style.border = '#CCC solid 1px';
		saveBtn.style.margin = '20px 1px 1px 50px';
		saveBtn.style.padding = '10px 20px';
		saveBtn.style.width = '200px';
		saveBtn.style.cursor = 'pointer';
		saveBtn.innerHTML = 'Сохранить расчет';
		saveBtn.onclick =  rtCalculator.saveCalculatorResult;
		blockB.appendChild(saveBtn);
		
		return blockB;
	}
	,
	makeProcessing:function(){
		console.log(rtCalculator.dataForProcessing);
		
		// определяем цену по dataForProcessing['priceTbl']
		// обращаемся к ряду таблицы цен, по значению параметра dataForProcessing['priceTblYindex']
		// и выбираем нужную ячейку по значению параметра dataForProcessing['priceTblXindex']
		var price = rtCalculator.dataForProcessing['priceTbl'][rtCalculator.dataForProcessing['priceTblYindex']][rtCalculator.dataForProcessing['priceTblXindex']];
		console.log('>>><<<');
		console.log(rtCalculator.dataForProcessing['priceTblYindex']+' '+rtCalculator.dataForProcessing['priceTblXindex']+' '+price);
		console.log(rtCalculator.dataForProcessing['coefficients']);
		// коэффициэнты
		// перебираем 
		var total_coefficient = 1;
		for(type in rtCalculator.dataForProcessing['coefficients']){
			var set = rtCalculator.dataForProcessing['coefficients'][type];
			for(var i = 0;i < set.length;i++){ 
			    total_coefficient *= set[i];
			}
		}
		  console.log(total_coefficient);
		rtCalculator.dataForProcessing['price'] = price*total_coefficient;
		  console.log(price+' итог калькулятора');
		var total_price = rtCalculator.dataForProcessing['price']*rtCalculator.dataForProcessing['quantity'];
		var total_str  = 'ИТОГО - цена за штуку: '+(rtCalculator.dataForProcessing['price']).toFixed(2)+',   за Тираж: '+(total_price).toFixed(2)+'';
		
		document.getElementById("rtCalculatorItogDisplay").innerHTML = total_str;
	}
	,
	saveCalculatorResult:function(){
		// в этом методе две задачи 
		// 1. отправить данные на сервер
		// 2. закрыть калькулятор
		
		// формируем объект с необходимой информацией
		var data = {};
		//dop_uslugi_id
		data['dop_uslugi_id'] = (rtCalculator.dataForProcessing['dop_uslugi_id'])? rtCalculator.dataForProcessing['dop_uslugi_id']:0;
		data['quantity'] = rtCalculator.dataForProcessing['quantity'];
		data['price'] = rtCalculator.dataForProcessing['price'];
		data['dop_data_row_id'] = rtCalculator.dataForProcessing['dop_data_row_id'];
		data['print_details']= {};
		data['print_details']['place_id'] = rtCalculator.dataForProcessing['place_id'];
		data['print_details']['print_id'] = rtCalculator.dataForProcessing['print_id'];
		data['print_details']['place_type'] = rtCalculator.data_obj.places[data['print_details']['place_id']].name;
		data['print_details']['print_type'] = rtCalculator.data_obj.places[data['print_details']['place_id']].data[data['print_details']['print_id']];
		data['print_details']['dop_params'] = rtCalculator.dataForProcessing['dop_params'];
		data['print_details']['priceTblYindex'] = rtCalculator.dataForProcessing['priceTblYindex'];
		data['print_details']['priceTblXindex'] = rtCalculator.dataForProcessing['priceTblXindex'];
		console.log('>>> saveCalculatorResult data');
		console.log(data);
        console.log('<<< saveCalculatorResult data end');
		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_calculator_result=1&details='+JSON.stringify(data));
		rtCalculator.send_ajax(url,callback);
		//alert(url);//
		$("#calculatorsaveResultBtn").remove();
		
		
		function callback(response){ 
		    
			console.log(response);
			//location.reload();
		}
		
	}
	,
    build_print_types_select:function(){
		// place_id = 1;
		// строит и возвращает select для типов нанеснения
		//console.log(place_id);
		//console.log(rtCalculator.data_obj.places[place_id].data);
		//console.log(rtCalculator.data_obj.places[place_id].data.length);
		//if(rtCalculator.data_obj.places[place_id].data.length==1)
		var printTypesSelect = document.createElement('SELECT');
		printTypesSelect.onchange = function(){
			rtCalculator.current_calculate_data.print_details.print_id = this.options[this.selectedIndex].value;
			var block_B = rtCalculator.buildBlockB();
			if(document.getElementById("rtCalculatorBlockB"))document.getElementById("rtCalculatorBlockB").parentNode.removeChild(document.getElementById("rtCalculatorBlockB"));
			this.parentNode.appendChild(block_B);
			// метод осуществляющий итоговый расчет 
		    // и помещающий итоговые данные в сторку ИТОГО
		    rtCalculator.makeProcessing();
			
		}
		//alert(rtCalculator.current_calculate_data.print_details.print_id);
		var counter = 0;
		for(var id in rtCalculator.calculate_params_obj.places[rtCalculator.current_calculate_data.print_details.place_id].data){
			// если это заново запускаемый калькулятор сохраняем id первого  нанесения 
			if(typeof rtCalculator.current_calculate_data.print_details.print_id === 'undefined') rtCalculator.current_calculate_data.print_details.print_id = id;
			counter++;
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(rtCalculator.calculate_params_obj.places[rtCalculator.current_calculate_data.print_details.place_id].data[id]));
            printTypesSelect.appendChild(option);
			//console.log(i + data_obj.places[i].name);
			if(typeof rtCalculator.current_calculate_data.print_details.print_id !== 'undefined'){
			    if(rtCalculator.current_calculate_data.print_details.print_id==id) option.setAttribute("selected",true);
			}
		}
		// если типов нанесения было больше чем одно вставляем в начало Selectа option ' -- выберите вариант -- '
		// и устанавливаем значение описывающиее id типа нанесения в 0
		// id типа нанесения передается в вызывающий метод для дальнейшего построения калькулятора 
		if(counter>1){
			var option = document.createElement('OPTION');
            option.setAttribute("value",0);
            option.appendChild(document.createTextNode(' -- выберите вариант -- '));
			printTypesSelect.insertBefore(option, printTypesSelect.firstChild); 
            
			if(typeof rtCalculator.current_calculate_data.print_details.print_id === 'undefined') rtCalculator.current_calculate_data.print_details.print_id = 0;
		}
	    else rtCalculator.current_calculate_data.print_details.print_id==id;
		
		return printTypesSelect;
	}
	,
	onchangeColorsSelect:function(colorsDiv){
		// здесь нам надо пройти по всем селектам в colorsDiv и собрать данные о выбранных полях
		// чтобы сохранить их в dataForProcessing а затем запустить rtCalculator.makeProcessing();
		
		// затираем данные по цветам которые были до этого
		if(rtCalculator.dataForProcessing['coefficients']['colors']) rtCalculator.dataForProcessing['coefficients']['colors'] = [];
		if(rtCalculator.dataForProcessing['dop_params']['colors']) rtCalculator.dataForProcessing['dop_params']['colors'] = [];
		//alert(colorsDiv);
		var selectsArr = colorsDiv.getElementsByTagName("SELECT");
		//alert(selectsArr.length);
		for( var i = 0; i < selectsArr.length; i++){
			var value = selectsArr[i].options[selectsArr[i].selectedIndex].value;
			var item_id = selectsArr[i].options[selectsArr[i].selectedIndex].getAttribute('item_id');
			// если value != 0(0 равно вспомогательное значение "Выбрать"), значит выбор в селекте сделан 
			// добавляем его в dataForProcessing
			if(value != 0){
				rtCalculator.dataForProcessing['coefficients']['colors'].push(value);
				rtCalculator.dataForProcessing['dop_params']['colors'].push({'id':item_id,'coeff':value});
			}
			// если value == 0(0 равно вспомогательное значение "Выбрать"), значит выбор в селекте не сделан
			// удаляем этот селект
			if(value == 0) selectsArr[i].parentNode.removeChild(selectsArr[i]);
		}
		rtCalculator.dataForProcessing['priceTblYindex'] =  rtCalculator.dataForProcessing['coefficients']['colors'].length;
		rtCalculator.makeProcessing();
	}
	,
    getCurrPrintParams:function(){
		
        console.log('>>> getCurrPrintParams');
		console.log(rtCalculator.calculate_params_obj.print_types[rtCalculator.current_calculate_data.print_details.print_id]);
		console.log('<<< getCurrPrintParams');
		
		var br = document.createElement('BR');
		var printParamsBox = document.createElement('DIV');

		
		// определяем переменную содержащую массив данных относящихся к текущему типу нанесения
		var CurrPrintTypeData = rtCalculator.calculate_params_obj.print_types[rtCalculator.current_calculate_data.print_details.print_id];
		
		// select для возможных цветов, в принипе этот селект даже должен быть не для цветов а для любого параметра 
		// который определяется по вертикали в таблице прайса 
        //                  [цвет] => Array   
        //                        ([белый] => Array([percentage] => 1.00 )
		//						   [серебро] => Array([percentage] => 1.20 ))

        if(CurrPrintTypeData['цвет']){
			var colorsDiv = document.createElement('DIV');
            colorsDiv.id = 'rtCalculatorColorsDiv';
			
			var colorsSelect = document.createElement('SELECT');
			// метод onchangeColorsSelect пикрепляется к Селекту здесь и пикрепляется к добавляемым селектам ниже в специальном цикле 
			colorsSelect.onchange = function(){ rtCalculator.onchangeColorsSelect(colorsDiv); }
			for(var color in CurrPrintTypeData['цвет']){
				
				var option = document.createElement('OPTION');
				option.setAttribute("value",CurrPrintTypeData['цвет'][color]['percentage']);
				option.setAttribute("item_id",CurrPrintTypeData['цвет'][color]['item_id']);
				option.appendChild(document.createTextNode(color));
				colorsSelect.appendChild(option);
			}
			
			// добавляем поле Выбрать в начало селекта
			var option = document.createElement('OPTION');
            option.setAttribute("value",0);
            option.appendChild(document.createTextNode(' -- выбрать -- '));
			colorsSelect.insertBefore(option, colorsSelect.firstChild); 
			
			
			var addColorLink = document.createElement('A');
			addColorLink.href = '#';
			addColorLink.innerHTML = 'добавить цвет';
			addColorLink.onclick =  function(){
				//alert(1);
				colorsDiv.appendChild(colorsSelect.cloneNode(true));
				var selectsArr = colorsDiv.getElementsByTagName("SELECT");
				for(var i in selectsArr){
					selectsArr[i].onchange = function(){ rtCalculator.onchangeColorsSelect(colorsDiv); }
				}
				
				
			}
			
			/*// собираем данные для расчета
			// цвет (по умолчанию ставим значение 1)
			rtCalculator.current_calculate_data.print_details.dop_params.colors coeff: "1.00"id: 
			rtCalculator.dataForProcessing['coefficients']={};
			rtCalculator.dataForProcessing['coefficients']['colors'] = [];
			rtCalculator.dataForProcessing['dop_params']={}
			rtCalculator.dataForProcessing['dop_params']['colors'] = [];
			*/
			// добавляем один или несколько селектов в калькулятор в зависимости от того был он вызван 
			// для уже существующего расчета или для нового расчета 
			if(typeof rtCalculator.current_calculate_data.print_details.dop_params.colors !== 'udefined'){
				for(var i = 0;i < rtCalculator.current_calculate_data.print_details.dop_params.colors.length; i++){ 
				     var colorsSelectClone = colorsSelect.cloneNode(true);
					 colorsSelectClone.options[parseInt(rtCalculator.current_calculate_data.print_details.dop_params.colors[i].id)].setAttribute("selected",true);
				     colorsDiv.appendChild(colorsSelectClone);
				}
			}
			else{
				colorsDiv.appendChild(colorsSelect);
			    rtCalculator.dataForProcessing['priceTblYindex'] =  1;
				rtCalculator.current_calculate_data.print_details.dop_params.colors.push({'id':0,'coeff':1});
			}
			
			printParamsBox.appendChild(colorsDiv);
			printParamsBox.appendChild(addColorLink);
			
		}
		
		
		if(CurrPrintTypeData['price_tbl']){
			// строим таблицу с ценами price_tbl
			var tbl = CurrPrintTypeData['price_tbl'][0];
			var tbl_html = document.createElement('TABLE');
			//alert(tbl.length);
			for(var row in tbl){
				var tr = document.createElement('TR');
				var td = document.createElement('TD');
				
				// создаем 1-ую колонку каждого ряда
				// если это первый ряд (указывающий на количество) первую колонку оставляем пустой
				// иначе вносим комбинацию данных значение параметра и его тип например (1 цвет)
				td.innerHTML = (row == 0)? '':tbl[row]['param_val']+' '+tbl[row]['param_type'];
				tr.appendChild(td);
				
				
				// перебераем ячейки таблицы озаглавленные 1,2,3,4 и т.д.
				for(var counter = 1 ;tbl[row][counter] != undefined ; counter++){
					var td = document.createElement('TD');  
					
					// если это первый ряд (указывающий на количество) преобразуем значения до Integer и добавляем тип параметра ( например 100 шт.)
					// иначе ввносим в ячейку оригинальное Float значение обозначающее стоимость 
					if(row == 0) var val = parseInt(tbl[row][counter])+' '+tbl[row]['param_type'];
					else var val = tbl[row][counter];
	
					
					td.innerHTML = val;
					tr.appendChild(td);
					
					
			        // собираем данные для расчета
			        // для определения текущей цены
					// этап 1 - определяем в какой диапазон входит количество товара, 
					// исходя из этого получаем индекс соответсвующей колонки таблицы цен
					if(row == 0){
						// если значение ячейки меньше значение параметра quantity, значит мы еще не вышли из диапазона, значение сохраняем
						if(parseInt(tbl[row][counter]) < rtCalculator.current_calculate_data.quantity) var priceTblXindex = counter;
						console.log(parseInt(tbl[row][counter])+' '+rtCalculator.current_calculate_data.quantity);
					}
					
					
				}
				
				tbl_html.appendChild(tr);
				
				// собираем данные для расчета
			    // сохраняем таблицу цен
				if(typeof rtCalculator.current_calculate_data.price_Tbl === 'undefined') rtCalculator.current_calculate_data.price_Tbl = [];
				rtCalculator.current_calculate_data.price_Tbl[parseInt(tbl[row]['param_val'])]=tbl[row];
				
				
			}
			// добавляем html таблицы в html контейнер
			printParamsBox.appendChild(tbl_html);
			
			// собираем данные для расчета
			// для определения текущей цены
			// этап 2 - 
			// устанавливаем dataForProcessing['priceTblXindex'] = 1;
			// устанавливаем dataForProcessing['priceTblXindex'] = ранее полученный priceTblXindex;
			if(typeof priceTblXindex === 'undefined') alert('количество тиража ниже допустимого');
			if(typeof rtCalculator.current_calculate_data.print_details.dop_params.priceTblXindex=== 'undefined'){ 
			    rtCalculator.current_calculate_data.print_details.dop_params = priceTblXindex;
			}
			//console.log(rtCalculator.dataForProcessing['price']);
			
		}
		return printParamsBox;
		// select для возможных площадей нанесения
		// [sizes] => Array
        //                ( [1] => Array   - ключ id места нанесения 
        //                        ([0] => Array ([print_id] => 13[size] => до 630 см2 (А4)[percentage] => 1.00)
		//						   [1] => Array ([print_id] => 13[size] => до 1260 см2 (А3)[percentage] => 1.50))
		//				  )
		if(CurrPrintTypeData['sizes']){
			// собираем данные для расчета
			// площади нанесения
			if(typeof rtCalculator.dataForProcessing['coefficients'] === 'undefined') rtCalculator.dataForProcessing['coefficients']={};
			if(typeof rtCalculator.dataForProcessing['dop_params'] === 'undefined') rtCalculator.dataForProcessing['dop_params']={};
			rtCalculator.dataForProcessing['coefficients']['sizes'] = [];
			rtCalculator.dataForProcessing['dop_params']['sizes'] = [];
			
			var printSizesSelect = document.createElement('SELECT');
			printSizesSelect.onchange = function(){
				rtCalculator.dataForProcessing['coefficients']['sizes'][0] = this.options[this.selectedIndex].value;
				rtCalculator.dataForProcessing['dop_params']['sizes'][0] = {'id':this.options[this.selectedIndex].getAttribute('item_id'),'coeff':this.options[this.selectedIndex].value};
				
			    //alert(rtCalculator.dataForProcessing['coefficients']['sizes'][0]);
				rtCalculator.makeProcessing();
			}
			for(var id in CurrPrintTypeData['sizes'][place_id]){
				if(typeof firstSizeVal === 'undefined') var firstSizeVal = CurrPrintTypeData['sizes'][place_id][id]['percentage'];
				if(typeof firstSizeItemId === 'undefined') var firstSizeItemId = CurrPrintTypeData['sizes'][place_id][id]['item_id'];
				
				var option = document.createElement('OPTION');


				option.setAttribute("value",CurrPrintTypeData['sizes'][place_id][id]['percentage']);
				// id значения (размер нанесения) который будет сохранен в базу данных и по нему будет отстроен калькулятор 
				// в случае вызова по кокретному нанесению
				option.setAttribute("item_id",CurrPrintTypeData['sizes'][place_id][id]['item_id']);
				option.appendChild(document.createTextNode(CurrPrintTypeData['sizes'][place_id][id]['size']));
				printSizesSelect.appendChild(option);
				
				if(concrete_print_data_obj && concrete_print_data_obj.dop_params.sizes[0] == CurrPrintTypeData['sizes'][place_id][id]['item_id']){
					option.setAttribute("selected",true);
					rtCalculator.dataForProcessing['coefficients']['sizes'][0] = CurrPrintTypeData['sizes'][place_id][id]['percentage'];
				    rtCalculator.dataForProcessing['dop_params']['sizes'][0] = {'id':CurrPrintTypeData['sizes'][place_id][id]['item_id'],'coeff':CurrPrintTypeData['sizes'][place_id][id]['percentage']};
					
				}
				
			}
			if(!concrete_print_data_obj){
				rtCalculator.dataForProcessing['coefficients']['sizes'][0]= parseFloat(firstSizeVal);
				rtCalculator.dataForProcessing['dop_params']['sizes'][0]= {'id':firstSizeItemId,'coeff':1};
			}
			
			printParamsBox.appendChild(br.cloneNode(true));
			printParamsBox.appendChild(br.cloneNode(true));
			printParamsBox.appendChild(br.cloneNode(true));
			printParamsBox.appendChild(br.cloneNode(true));
			printParamsBox.appendChild(printSizesSelect);
		}
		
		return printParamsBox;
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
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
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
				if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
					var type = tds_arr[j].getAttribute('type');
					this.tbl_model[row_id][type] = parseFloat(tds_arr[j].innerHTML);
					
	
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
				//console.log(trs_arr[i]);
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
								tds_arr[j].onkeyup = function(){
								   rtCalculator.check();
								   // запускаем таймер по истечению которого вызываем функцию rtCalculator.complite_input
								   // отправляющую данные на сервер
								   if(!rtCalculator.complite_timer) rtCalculator.complite_timer = setTimeout(rtCalculator.complite_input,2000);
								}
								tds_arr[j].onblur = this.complite_input;
								tds_arr[j].setAttribute("contenteditable",true);
								tds_arr[j].style.outline="none";
							}
							if(tds_arr[j].getAttribute('expel')){
								tds_arr[j].onclick = this.expel_value_from_calculation;
							}
							if(tds_arr[j].getAttribute('svetofor')){
								//console.log(j+' svetofor');
								if(tds_arr[j].getElementsByTagName('img')[0]) $(tds_arr[j].getElementsByTagName('img')[0]).mouseenter(this.show_svetofor);
								
							}
							if(tds_arr[j].getAttribute('calc_btn')){
								//console.log(j+' svetofor');
								if(tds_arr[j].getElementsByTagName('span')[0]) tds_arr[j].getElementsByTagName('span')[0].onclick = this.start_calculator;
								
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
		 console.log('№'+(++rtCalculator.complite_count));
		 console.log(1);
		// получаем значение ячейки
		var last_val = rtCalculator.cur_cell.innerHTML;
		
		// сравниваем текущее значение с первоначальным, если они равны значит окончательные изменения не были произведены
		// в таком случае ничего не меняем в базе - прерываем дальнейшее выполнение
		if(rtCalculator.primary_val == last_val){
			rtCalculator.changes_in_process = false;
			return;
		}
		console.log(rtCalculator.primary_val+' '+last_val);

		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_rt_changes={"id":"'+rtCalculator.cur_cell.parentNode.getAttribute('row_id')+'","prop":"'+rtCalculator.cur_cell.getAttribute('type')+'","val":"'+last_val+'"}');
		rtCalculator.send_ajax(url,callback);
		//alert(last_val);
		function callback(){ 
		    rtCalculator.changes_in_process = false;
		    /*cell.className = cell.className.slice(0,cell.className.indexOf("active")-1);*/
			console.log(2);
		}
	}
	,
	check:function(){// корректировка значений вводимых пользователем

		var cell = rtCalculator.cur_cell;
		
		//alert(floatLengthToFixed (cell.innerHTML));
		if(cell.getAttribute('type') == 'quantity')  var result = correctToInt(cell.innerHTML);
		else  var result = correctToFloat(cell.innerHTML);
		
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
    make_calculations:function(cell){
	    // Когда в ячейке(поле ввода) в результате каких то действий происходит изменение содержимого нужно вызывать этот метод
		// метод производит калькуляцию текущих данных, и вычисляет разность текущих данных с теми которые были до изменения 
		//e = e || window.event;
		//var cell = e.target || e.srcElement;
		// получаем id ряда
		var cur_tr = cell.parentNode;
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
		rtCalculator.change_html(cur_tr,row_id);

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
	    if(!row['dop_data']['expel']['main']){
			rtCalculator.tbl_model['total_row']['price_in_summ'] += row['price_in_summ'] - rtCalculator.previos_data['price_in_summ'];
			rtCalculator.tbl_model['total_row']['price_out_summ'] += row['price_out_summ'] - rtCalculator.previos_data['price_out_summ'];
			rtCalculator.tbl_model['total_row']['in_summ'] += row['in_summ'] - rtCalculator.previos_data['in_summ'];
			rtCalculator.tbl_model['total_row']['out_summ'] += row['out_summ'] - rtCalculator.previos_data['out_summ'];
			rtCalculator.tbl_model['total_row']['delta'] +=  row['delta'] - rtCalculator.previos_data['delta'];
			rtCalculator.tbl_model['total_row']['margin'] +=  row['margin'] - rtCalculator.previos_data['margin'];
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
				var connected_vals = tds_arr[j].getAttribute('connected_vals');
				
				if(type=='quantity') tds_arr[j].innerHTML = rtCalculator.tbl_model[row_id][type];
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
					//tds_arr[j].innerHTML = rtCalculator.tbl_model['total_row'][tds_arr[j].getAttribute('type')];
					tds_arr[j].innerHTML = (type=='quantity')? rtCalculator.tbl_model['total_row'][type]:(rtCalculator.tbl_model['total_row'][type]).toFixed(2); 
				}
			}
		//}
		
	}
	,
	expel_value_from_calculation:function(e){
		// метод исключающий или включающий значения из подсчетов
		// либо в текущих рядах, либо в окончательных суммах по всей таблице (итоговый ряд)
		
	    if(rtCalculator.expel_value_from_calculation.in_process) return; 
		rtCalculator.expel_value_from_calculation.in_process = true;
		
	    e = e || window.event;
		var cell = e.target || e.srcElement;
		
		if(cell.getAttribute('expel') == undefined){ alert('attribute expel dont exists'); return;}
		
	    // получаем текущий статус ячейки и меняем его на противоположный
		var status = !(!!parseInt(cell.getAttribute('expel')));
		//alert(status+' '+cell.getAttribute('expel')+' '+cell.getAttribute('type'));
        
		var row_id = cell.parentNode.getAttribute('row_id');
		if(row_id == undefined) { alert('attribute row_id dont exists'); return;}
		if(row_id == 0) {  rtCalculator.expel_value_from_calculation.in_process = false; return;}  // прерываем выполнение - вспомогательный ряд
		//console.log(row_id);
		
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
				if(rtCalculator.tbl_model[id]['dop_data']['expel']['main']) continue; 
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
			rtCalculator.change_html(cell.parentNode,row_id);
	
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
			//console.log('plank');
			var sourse_src = OS_HOST + '/skins/images/img_design/';
			
			var arr = ['red','green','grey'];
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
		//console.log();
		var td = img_btn.parentNode.parentNode;
		var row_id = td.parentNode.getAttribute("row_id");
		var status = img_btn.getAttribute("status");
		
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_svetofor='+ status +'&id='+row_id);
		rtCalculator.send_ajax(url,callback);
		function callback(response){ /*alert(response);*/
		   td.getElementsByTagName('img')[0].src = OS_HOST + '/skins/images/img_design/rt_svetofor_'+status+'.png';
		   td.setAttribute("svetofor",status);
		}
		
		rtCalculator.hide_svetofor();
		rtCalculator.show_svetofor.pause = true;
		setTimeout( pause, 300 );
		function pause(){ rtCalculator.show_svetofor.pause = false; }
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
						//console.log(value+" "+tds_arr[j].getAttribute('connected_vals'));
						var stat = parseInt(tds_arr[j].getAttribute("c_stat"));
						var new_stat = (stat+1)%2;
						tds_arr[j].setAttribute("c_stat",new_stat);
						//console.log(stat+' '+new_stat);
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
				//console.log(pos_id+' '+trsArr[i].getAttribute('row_id'));
				// работаем с рядом - ищем светофор 
				var tdsArr = trsArr[i].getElementsByTagName('td');   
				for( var j= 0 ; j < tdsArr.length; j++){
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='green'){
						idsObj[pos_id][trsArr[i].getAttribute('row_id')]=true;
						nothing = false;
					}
				}
				
			}
		}
		return (nothing)? false : idsObj;
	}
	,
	copy_rows:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		// определяем какие ряды были выделены (какие Мастер Кнопки были нажаты и установлен ли зеленый маркер в светофоре)
		if(!(idsObj = rtCalculator.get_active_rows())){
			alert('не возможно скопировать ряды, вы не выбрали ни одной позиции');
			return;
		} 
		var control_num = 1;
		show_processing_timer();
		//alert(idsObj);
		
		// Сохраняем полученные данные в cессию(SESSION) чтобы потом при выполнении действия (вставить скопированное) получить данные из SESSION
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_copied_rows_to_buffer='+JSON.stringify(idsObj)+'&control_num='+control_num);
		rtCalculator.send_ajax(url,callback);
		function callback(response){  /* console.log(response); */ close_processing_timer(); closeAllMenuWindows(); }
	}
	,
	insert_copied_rows:function(e){ 
	   
	    e = e|| window.event;
		var cell = e.target || e.srcElement;
		
		var control_num = 1;
		show_processing_timer();
		//  
		// 1. Обращаемся к серверу, получаем данные из буфера(SESSIONS)
		// 2. Вставляем данные из буфера в базу данных на стороне сервера
		// 3. Получаем ответ об успешном действии
		// 4. Вносим изменения в HTML

		var url = OS_HOST+'?' + addOrReplaceGetOnURL('insert_copied_rows=1&control_num='+control_num);
		rtCalculator.send_ajax(url,callback);
		function callback(response){ 
		    console.log(response);  /* */ 
			var data = JSON.parse(response);
			if(!data[0]) return;
			close_processing_timer(); closeAllMenuWindows(); location.reload();
		}
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
}