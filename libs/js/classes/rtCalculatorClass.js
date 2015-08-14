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
		if(typeof quantity === 'undefined'){
			alert('Не удается получить данные о количестве товара!!!');
			return;
		}
		if(quantity === 0){
			alert('Расчет не возможен, тираж 0шт. !!!');
			return;
		}
		
		
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
			    // alert(response);
				// этап 1
				if(typeof data_AboutPrintsArr !== 'undefined') delete data_AboutPrintsArr;
				var data_AboutPrintsArr = JSON.parse(response);
				rtCalculator.dataObj_toEvokeCalculator = {"art_id":art_id,"dop_data_row_id":dop_data_row_id,"quantity":quantity,"cell":cell};
				
				if(data_AboutPrintsArr.length == 0){
					
					// запускаем калькулятор
					rtCalculator.evoke_calculator();
				}
				else{
					// запускаем панель
					rtCalculator.launch_dop_uslugi_panel(data_AboutPrintsArr);
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
	launch_dop_uslugi_panel:function(data_AboutPrintsArr){
		console.log('>>> launch_dop_uslugi_panel start');
		console.log(data_AboutPrintsArr);
		console.log('<<< launch_dop_uslugi_panel end');
		//return;
		var box = document.createElement('DIV');
		box.id = "calculatorDopUslugiBox";
		//box.style.width = '300px';
		box.style.display = "none";
		
		var tbl = document.createElement('TABLE');
		tbl.style.borderCollapse = 'Collapse';
		var tr = document.createElement('TR');
		rtCalculator.uslugi_panel_print_details = [];
		
		rtCalculator.currentCalculationData = [];
		for(var i = 0; i < data_AboutPrintsArr.length; i++){
			var tr =  tr.cloneNode(false);
			var td = document.createElement('TD');
			td.style.border = '#CCC solid 1px';
			td.style.padding = '2px 4px';

            // сохраняем данные в общей переменной
			// предварительно трансофорировав сериализованную строку из свойства "print_details" в объект
			var print_details = JSON.parse(data_AboutPrintsArr[i].print_details);
			data_AboutPrintsArr[i].print_details = print_details;
			
			rtCalculator.currentCalculationData[i] = data_AboutPrintsArr[i];
			rtCalculator.currentCalculationData[i].dop_uslugi_id = data_AboutPrintsArr[i].id;

			if(typeof rtCalculator.currentCalculationData[i].id !== 'undefined') delete rtCalculator.currentCalculationData[i].id;
			if(typeof rtCalculator.currentCalculationData[i].type !== 'undefined') delete rtCalculator.currentCalculationData[i].type;

			
			td.innerHTML = i+1;
			
			//// console.log(rtCalculator.currentCalculationData[i]);
			tr.appendChild(td);
			
			// тип нанесения
			var td =  td.cloneNode(false);
			td.innerHTML = rtCalculator.currentCalculationData[i].print_details.print_type;
			td.setAttribute('index',i);
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			tr.appendChild(td);
			td.onclick = function(){ 
				// запускаем калькулятор для конкретного нанесения
				$("#calculatorDopUslugiBox").remove();
				// добавляем в передаваемые данные данные индекс массива rtCalculator.currentCalculationData содержащего
				// данные конкретного(этого)нанесения
				rtCalculator.dataObj_toEvokeCalculator.currentCalculationData_id = this.getAttribute('index');
				// запускаем
				rtCalculator.evoke_calculator(rtCalculator.dataObj_toEvokeCalculator);
			}
			
			// место нанесения
			var td =  td.cloneNode(false);
			td.style.textDecoration = 'none';
			td.innerHTML = rtCalculator.currentCalculationData[i].print_details.place_type;
			tr.appendChild(td);
			
			// сумма
			var td =  td.cloneNode(false);
			td.innerHTML = (Math.round(rtCalculator.currentCalculationData[i].price_out*rtCalculator.currentCalculationData[i].quantity * 100) / 100 ).toFixed(2) +' р.';
			tr.appendChild(td);
			
			var td =  td.cloneNode(false);
			td.innerHTML = 'Удалить нанесение';
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			td.setAttribute('usluga_id',data_AboutPrintsArr[i].dop_uslugi_id);
			td.onclick = function(){ 
			
				// отправляем запрос на удаление для текущего нанесения
				var url = OS_HOST+'?' + addOrReplaceGetOnURL('delete_prints_for_row='+rtCalculator.dataObj_toEvokeCalculator.dop_data_row_id+'&usluga_id='+this.getAttribute('usluga_id'));
				rtCalculator.send_ajax(url,callback);
			
				function callback(response){ 
				    // alert(response);
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
		    rtCalculator.evoke_calculator(rtCalculator.dataObj_toEvokeCalculator);
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
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('delete_prints_for_row='+rtCalculator.dataObj_toEvokeCalculator.dop_data_row_id+'&all=true');
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
	evoke_calculator:function(){
		
		// отправляем запрос чтобы получить описание параметров возможного калькулятора для данного ариткула
	    var url = OS_HOST+'?' + addOrReplaceGetOnURL('grab_calculator_data={"art_id":"'+rtCalculator.dataObj_toEvokeCalculator.art_id+'","type":"'+rtCalculator.dataObj_toEvokeCalculator.cell.parentNode.getAttribute('calc_btn')+'"}');
		rtCalculator.send_ajax(url,callback);
		//alert(last_val);
		function callback(response_calculatorParamsData){
			// alert(response_calculatorParamsData);
			// return;
			if(typeof rtCalculator.calculatorParamsObj !== 'undefined') delete rtCalculator.calculatorParamsObj;
			
            rtCalculator.calculatorParamsObj = JSON.parse(response_calculatorParamsData);
			// строим калькулятор
			rtCalculator.build_print_calculator();
			// открываем окно с калькулятором
			
				
			$("#calculatorDialogBox").dialog({autoOpen: false, position:{ at: "top+25%", of: window } ,title: "Расчет нанесения логотипа",modal:true,width: 600,close: function() {this.remove();$("#calculatorDialogBox").remove();}});
			$("#calculatorDialogBox").dialog("open");
		}
	}
	,
    distributePrint:function(e){

	    e = e || window.event;
	    // устанавливаем текущюю ячейку и сохраняем изначальное значение
	    var cell = e.target || e.srcElement;
        // меняем отображение меню
		var divArr = cell.parentNode.getElementsByTagName('DIV');
		for(var name in divArr) divArr[name].className = "calculatorMenuCell pointer";
		cell.className += " deepGreyBg";
		
		// скрываем первое окно калькулятора 
		document.getElementById('mainCalculatorBox').style.display = 'none';
		
		
        if(document.getElementById('calculatorDataBox')){
			document.getElementById('calculatorDataBox').parentNode.removeChild(document.getElementById('calculatorDataBox'));
		}
		
		//строим контейнер для данного окна 
		var calculatorDataBox = document.createElement('DIV'); 
		calculatorDataBox.id = 'calculatorDataBox';
		
		//проверяем есть ли данные по месту печати и типу печати если чего-то нет выводим предупреждение
		if(typeof rtCalculator.currentCalculationData.print_details.place_id === 'undefined'){
			alert('Вы не выбрали место нанесения');
			return;
		}
		if(typeof rtCalculator.currentCalculationData.print_details.print_id === 'undefined' || rtCalculator.currentCalculationData.print_details.print_id == 0){
			alert('Вы не выбрали тип нанесения');
			return;
		}
		
		// предварительно удаляем предыдущие данные rtCalculator.distributionData в если они есть 
		if(typeof rtCalculator.distributionData !== 'undefined') delete rtCalculator.distributionData;
		if(typeof rtCalculator.distributionData === 'undefined') rtCalculator.distributionData = {};
		if(typeof rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id] === 'undefined') rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id] = {};
		// если еще нет данных по данным типам мест и нанесения строим массив с данными
		if(typeof rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id] === 'undefined'){
			rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id] = {};
			
			// создаем таблицу с позициями к которым возможно применить данное нанесение
			var table = rtCalculator.createDistributionDataTbl();
			if(table){
				
				calculatorDataBox.appendChild(table);
			    var saveBtn = document.createElement('DIV'); 
				saveBtn.className = 'distributionSaveBtn';
				saveBtn.id = 'distributionSaveResultBtn';
				saveBtn.innerHTML = 'Сохранить';
				saveBtn.onclick = function(){
					alert("Нанесение которое вы собиратесь скопировать, будет скопированно на все варианты расчетов относящихся к позициям которые вы выбрали, нанесение будет скопировано со всеми настройками.");
					var idsArr = [];
					var inputsArr = table.getElementsByTagName('INPUT');
					for(var i = 0;i < inputsArr.length;i++){
						if(inputsArr[i].type == 'checkbox' && inputsArr[i].checked == true){
							idsArr.push(inputsArr[i].value);//alert(inputsArr[i].value);
						}
					}
					if(idsArr.length>0){
						    var details = {};
							details.ids = idsArr;
							details.calculationData = rtCalculator.currentCalculationData;
							
							if(typeof details.calculationData.print_details.place_type === 'undefined') details.calculationData.print_details.place_type = rtCalculator.currentCalculationData.print_details.place_type =  rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id].name;
							
							if(typeof details.calculationData.print_details.print_type === 'undefined') details.calculationData.print_details.print_type = rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id].prints[rtCalculator.currentCalculationData.print_details.print_id];
							
							
							
							var url = OS_HOST+'?' + addOrReplaceGetOnURL('distribute_print=1&details='+JSON.stringify(details));
							rtCalculator.send_ajax(url,callback);
						 
							//$("#distributionSaveResultBtn").remove();
							//$("#calculatorDialogBox").remove();
							
							function callback(response){ 
								// alert(response);
								// return;
								var response_obj =JSON.parse(response);
								if(response_obj.errors){
									var str = 'ВНИМАНИЕ\r\n';
									var space = '        ';
									
									for(var i in response_obj.errors){
										str += 'строка '+rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].dop_data[response_obj.errors[i].id].glob_counter+', артикул. '+rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].dop_data[response_obj.errors[i].id].article+"\r";
										
										for(var j in response_obj.errors[i].errors){
											var errors = [];
											if(response_obj.errors[i].errors[j].needIndividCalculation)  errors.push('    Ошибка! Нанесение не может быть применено, требуется индивидуальный расчет');
											if(response_obj.errors[i].errors[j].outOfLimit)  errors.push('    Ошибка! Нанесение не может быть применено, превышен максимальный лимит');
											if(response_obj.errors[i].errors[j].lackOfQuantity)  errors.push('    Количество меньше минимального тиража, цена была рассчитана по минимально расценке');
											 
											 
										     str += space+"расчет на "+response_obj.errors[i].errors[j].quantity +"шт. "+errors.join(',') + "\r";
									    }
										str += "\r";
									}
									alert(str);
								}
								//location.reload();
							}
					}  
					else{
						alert('Вы не выбрали позиции к которым надо применить нанесение');
					}
				}
				calculatorDataBox.appendChild(saveBtn);
				rtCalculator.commonContainer.appendChild(calculatorDataBox);
				
			}
		}
	}
	,
    createDistributionDataTbl:function(){
	    //if(typeof rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id] === 'undefined') rtCalculator.createDistributionDataTbl();
		
		// проходимся по рядам РТ и собираем те позиции к которым может быть применен данный тип нанесения на данном месте нанесения

		var tbl = document.getElementById('rt_tbl_body');
		var trs_arr = tbl.getElementsByTagName('tr');
		
		outerloop:
		for(var i = 0;i < trs_arr.length;i++){ 
		
		    if(!trs_arr[i].getAttribute('pos_id')) continue;
			if(!trs_arr[i].getAttribute('type')) continue;
			
			var pos_id = trs_arr[i].getAttribute('pos_id');
			var row_id = trs_arr[i].getAttribute('row_id');
			var trType = trs_arr[i].getAttribute('type');
			if(true/*trType=='cat'*/){
				
				var art_id = trs_arr[i].getAttribute('art_id');
			    var tr = trs_arr[i].cloneNode(true);
				
				var newTR = document.createElement('TR'); 
				var tdsArr = tr.getElementsByTagName('TD');
				var artTd = document.createElement('TD'); 
				artTd.width = '60';

				var checkbox = document.createElement('INPUT');
				checkbox.type = 'checkbox';
				checkbox.style.display = 'block';
				var checkboxTd = document.createElement('TD'); 
				checkboxTd.width = '10';
				checkboxTd.appendChild(checkbox);	
				
				for(var j =0; j < tdsArr.length/**/; j++){
					if(tdsArr[j].getAttribute('type')){
						var type = tdsArr[j].getAttribute('type');
						
						if(type == 'dop_details'){ 
						   var dop_details = tdsArr[j].innerHTML;
						   
						   // alert(dop_details);
						   dop_details_obj = JSON.parse(dop_details);
						   
						   //alert(typeof dop_details_obj.allowed_prints[rtCalculator.currentCalculationData.print_details.place_id]);
						   
						   //if(typeof dop_details_obj.allowed_prints ==='undefined') continue outerloop;
						   
						   // здесь вообще полная шизофрения 
						   // если dop_details_obj.allowed_prints не существует в природе - мы продолжаем работать с этим рядом
						   // тоесть к нему можно все применять - так как не указано ничего конкретно
						   // но если dop_details_obj.allowed_prints есть, то мы начинаем проверять на наличие в ней:
						   // сначала вложенности указывающей конкретное place_id 
						   // потом  вложенности указывающей конкретное print_id 
						   // если все сошлось значит такое нанесение на таком месте к этой позиции можно применить 
						   // причем сначала надо проверить первую вложенность а затем только вторую иначе может вылезти ошибка если 
						   // первая вложенность с таким данными будет отсутсвовать
						   if(typeof dop_details_obj.allowed_prints !=='undefined'){
							   if(typeof dop_details_obj.allowed_prints[rtCalculator.currentCalculationData.print_details.place_id] ==='undefined') continue outerloop;
							   if(typeof dop_details_obj.allowed_prints[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id] ==='undefined') continue outerloop;
						   }
						   
						}
						
						
						if(type == 'glob_counter' ||  type == 'master_btn' || type == 'name' || type == 'quantity'){
							// проверяем может ли быть применено текущее нанесение к этой данной позиции
							
							
							if(tdsArr[j].hasAttribute('colspan')) tdsArr[j].removeAttribute('colspan');
							if(tdsArr[j].hasAttribute('rowspan')) tdsArr[j].removeAttribute('rowspan');
							
							if(type == 'master_btn'){ 
							    var checkboxTdClone = checkboxTd.cloneNode(true);
								checkboxTdClone.getElementsByTagName('INPUT')[0].value = pos_id;
							    newTR.appendChild(checkboxTdClone);
								continue;
							}
							if(type == 'name'){ 
							   if(tdsArr[j].hasAttribute('width')) tdsArr[j].removeAttribute('width');
							   var article = tdsArr[j].getElementsByTagName('DIV')[0].getElementsByTagName('A')[0].innerHTML;
							   artTd.innerHTML = article;
							   
							   newTR.appendChild(artTd.cloneNode(true));
							   tdsArr[j].getElementsByTagName('DIV')[0].parentNode.removeChild(tdsArr[j].getElementsByTagName('DIV')[0]);
							   
							}

							if(type == 'quantity'){ 
							   if(tdsArr[j].innerHTML == '') tdsArr[j].innerHTML = 'Варианты';
							}
							if(type == 'glob_counter'){ 
							   var glob_counter = tdsArr[j].innerHTML;
							}
							
							newTR.appendChild(tdsArr[j].cloneNode(true));
						}
					}
				}
				//rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].push({"art_id":art_id,"tr":newTR});
				if(typeof rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].trs === 'undefined'){
					rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].trs = [];
				}
				if(typeof rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].dop_data === 'undefined'){
					rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].dop_data = {};
				}
				
				rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].trs.push({"art_id":art_id,"tr":newTR});
				rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].dop_data[pos_id]= {"glob_counter":glob_counter,"article":article};
			}
	    }
		
		//alert(rtCalculator.currentCalculationData.print_details.place_id+' '+rtCalculator.currentCalculationData.print_details.print_id);
		if(rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].trs && rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].trs.length > 0){
			var dataArr = rtCalculator.distributionData[rtCalculator.currentCalculationData.print_details.place_id][rtCalculator.currentCalculationData.print_details.print_id].trs;
			 
			var table = document.createElement('TABLE');
			table.className="calculatorDistributionTbl";
			
		    for(var i =0; i < dataArr.length; i++){
				//console.log(dataArr[i]);
				table.appendChild(dataArr[i].tr);
			}
			return table;
		}
		//console.log('rtCalculator.distributionData');
		//console.log(rtCalculator.distributionData);
	}
	,
    build_print_calculator:function(){
		
		// если калькулятор был вызван для существующего нанесения пересохраняем данные для конкретного нанесения 
		// иначе готовим структуру для сохранения данных при создании калькулятора 
	    if(rtCalculator.dataObj_toEvokeCalculator.currentCalculationData_id){
			rtCalculator.currentCalculationData =  rtCalculator.currentCalculationData[rtCalculator.dataObj_toEvokeCalculator.currentCalculationData_id];
			rtCalculator.currentCalculationData.dop_data_row_id = rtCalculator.dataObj_toEvokeCalculator.dop_data_row_id;
			
			if(typeof rtCalculator.currentCalculationData.dop_row_id !== 'undefined') delete rtCalculator.currentCalculationData.dop_row_id;
			if(typeof rtCalculator.currentCalculationData.price_in !== 'undefined') delete rtCalculator.currentCalculationData.price_in;
			if(typeof rtCalculator.currentCalculationData.price_out !== 'undefined') delete rtCalculator.currentCalculationData.price_out;
		}
		else{
		    rtCalculator.currentCalculationData =  {};	
			rtCalculator.currentCalculationData.quantity = rtCalculator.dataObj_toEvokeCalculator.quantity;
		    rtCalculator.currentCalculationData.dop_data_row_id = rtCalculator.dataObj_toEvokeCalculator.dop_data_row_id;
			rtCalculator.currentCalculationData.print_details = {};
			rtCalculator.currentCalculationData.print_details.dop_params = {};
		}
		
	    console.log('>>> build_print_calculator');
		console.log(' rtCalculator.currentCalculationData',rtCalculator.currentCalculationData);
		console.log('<<< build_print_calculator');
        console.log('>>>  rtCalculator.calculatorParamsObj', rtCalculator.calculatorParamsObj,'<<<  rtCalculator.calculatorParamsObj');
		// строим интерфейс калькулятора
		
		//return;
		// console.log('>>> calculatorParamsObj',rtCalculator.calculatorParamsObj,'<<< calculatorParamsObj');
		
		// структура элемента data.places
		// [places] => Array([0] => ключ соответсвует id места нанесения (если id = 0 - "Стандартное" место )неограниченное количество элементов
		//					Array (
		//						  [name] => "Стандартно" или "грудь (00х00)" - строка описывающая место нанесения
		//						  [data] => Array([0] => 13,[1] => 23) массив значения - id видов нанесения 
		//					       )
        //                   )
		
		var dialogBox = document.createElement('DIV');
		dialogBox.id = "calculatorDialogBox";
		dialogBox.className = "calculatorDialogBox";
		dialogBox.style.display = "none";
		
		// 
		var menuContainer = document.createElement('DIV');
		menuContainer.className = "menuContainer";
		var btn1 = document.createElement('DIV');
		btn1.onclick = function(e){

			e = e || window.event;
			// устанавливаем текущюю ячейку и сохраняем изначальное значение
			var cell = e.target || e.srcElement;
			
			var divArr = cell.parentNode.getElementsByTagName('DIV');

			for(var name in divArr) divArr[name].className = "calculatorMenuCell pointer";
			cell.className += " deepGreyBg";
			
			document.getElementById('mainCalculatorBox').style.display = 'block';
			if(typeof document.getElementById('calculatorDataBox') !== 'undefined') document.getElementById('calculatorDataBox').style.display = 'none';
		}
		btn1.className = "calculatorMenuCell pointer deepGreyBg";
		btn1.innerHTML = 'Расчет печати';
		var btn2 = document.createElement('DIV');
		btn2.className = "calculatorMenuCell pointer";
		btn2.innerHTML = 'Артикулы';
		btn2.onclick = rtCalculator.distributePrint;
		var infoField = document.createElement('DIV');
		infoField.className = "calculatorMenuCell";
		infoField.innerHTML = "Тираж "+rtCalculator.currentCalculationData.quantity+' шт.';
		
		
		menuContainer.appendChild(btn1);
		menuContainer.appendChild(btn2);
		menuContainer.appendChild(infoField);
		dialogBox.appendChild(menuContainer);
		
		var clear_div = document.createElement('DIV');
		clear_div.className = "clear_div";
		dialogBox.appendChild(clear_div.cloneNode(true));
		
        // общий контейнер для всех закладок(разделов) калькулятора
		rtCalculator.commonContainer = document.createElement('DIV');


        // контейнер для главного окна калькулятора
		var mainCalculatorBox = document.createElement('DIV');
		mainCalculatorBox.className = "mainCalculatorBox";
		mainCalculatorBox.id = "mainCalculatorBox";
		
		//
		var box = document.createElement('DIV');
		box.className = "calculatorBodyBox";
		box.id = "calculatorBodyBox";
		
		var printPlaceSelectDiv = document.createElement('DIV');
		printPlaceSelectDiv.className = "printPlaceSelectDiv";
		
		var title = document.createElement('DIV');
		title.className = "calculatorTitle";
		title.innerHTML = 'Место: ';
		
		printPlaceSelectDiv.appendChild(title);
		// строим select выбора мест нанесений
		var printPlaceSelect = document.createElement('SELECT');
		
		//// console.log(rtCalculator.calculatorParamsObj.places);
		for(var id in rtCalculator.calculatorParamsObj.places){
			// если это заново запускаемый калькулятор сохраняем id первого места нанесения 
			if(typeof rtCalculator.currentCalculationData.print_details.place_id === 'undefined') rtCalculator.currentCalculationData.print_details.place_id = id;
           
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(rtCalculator.calculatorParamsObj.places[id].name));
            printPlaceSelect.appendChild(option);
			
			if(rtCalculator.currentCalculationData.print_details.place_id==id) option.setAttribute("selected",true);
			//// console.log(i + rtCalculator.dataObj_toEvokeCalculator.places[i].name);
		}
		//currPlace_id = 1;
		printPlaceSelect.onchange = function(){
			if(document.getElementById("rtCalculatorBlockA")){
			    document.getElementById("rtCalculatorBlockA").parentNode.removeChild(document.getElementById("rtCalculatorBlockA"));
			}
			if(document.getElementById("rtCalculatorItogDisplay")) document.getElementById("rtCalculatorItogDisplay").innerHTML = '';
			// alert('printPlaceSelect');
			//
			rtCalculator.currentCalculationData.print_details = {};
			rtCalculator.currentCalculationData.print_details.dop_params = {};
			// определяем id места нанесения
			rtCalculator.currentCalculationData.print_details.place_id = parseInt(this.options[this.selectedIndex].value);
			//alert(place_id);
			// создаем новый block_A
			 var block_A = rtCalculator.buildBlockA();
			
			document.getElementById("calculatorBodyBox").appendChild(block_A);
			if(rtCalculator.makeProcessingFlag) rtCalculator.makeProcessing();
		}
		
		var elementsBox = document.createElement('DIV');
		elementsBox.className = "calculatorElementsBox";

		elementsBox.appendChild(printPlaceSelect);
		printPlaceSelectDiv.appendChild(elementsBox);
		printPlaceSelectDiv.appendChild(clear_div.cloneNode(true));
		mainCalculatorBox.appendChild(printPlaceSelectDiv);
		
		
		// создаем блок block_A который будет содеражать в себе select выбора типа нанесения
		// и блок block_B содержащий в себе все остальные элементы интерфейса
		if(rtCalculator.currentCalculationData.print_details.place_id){
			// если остался print_id от предыдущего запуска калькулятора удаляем его
	        var block_A = rtCalculator.buildBlockA();
		    box.appendChild(block_A);
		}
		else{
			box.appendChild(document.createTextNode("Ошибка: не определен ID места нанесения "));
			
		}
		
		mainCalculatorBox.appendChild(box);
	
		rtCalculator.commonContainer.appendChild(mainCalculatorBox);
		dialogBox.appendChild(rtCalculator.commonContainer);
		document.body.appendChild(dialogBox);
		
		
		if(rtCalculator.makeProcessingFlag) rtCalculator.makeProcessing();
		
		// help button
		// box.appendChild(help.btn('kp.sendLetter.window'));
		
	}
	,
	buildBlockA:function(){
		
		// метод строит блок block_A и взависимости от ситуации
		// или ( строит и вставляет в него block_B ) или ( не делает этого )
		// block_A содержит селект с выбором типа нанесения
		
		var block_A = document.createElement('DIV');
		block_A.id = 'rtCalculatorBlockA';
		block_A.className = 'rtCalculatorBlockA';

		// вызваем метод строящий  select для типов нанеснения
		// передаем ему id первого места нанесения из printPlaceSelect
		// он возвращает select и id типа нанесения первого в списке select
		var printTypesSelect = rtCalculator.build_print_types_select();
		var elementsBox = document.createElement('DIV');
		elementsBox.className = "calculatorElementsBox";
		
		
		var printTypesSelectDiv = document.createElement('DIV');
		printTypesSelectDiv.className = "printTypesSelectDiv";
	   



        var title = document.createElement('DIV');
		title.className = "calculatorTitle";
		title.innerHTML = 'Вид: ';
	    var clear_div = document.createElement('DIV');
		clear_div.className = "clear_div";
		
		printTypesSelectDiv.appendChild(title);
        elementsBox.appendChild(printTypesSelect);
		printTypesSelectDiv.appendChild(elementsBox);
		printTypesSelectDiv.appendChild(clear_div);
		block_A.appendChild(printTypesSelectDiv);
		
		
		// alert(rtCalculator.currentCalculationData.print_details.print_id);
		// если мы имеем конкретное типа нанесения (тоесть оно не равно 0) тогда строим калькулятор дальше
		// вызываем метод строящий блок В калькулятора и вставляем его в тело калькулятора
		if(rtCalculator.currentCalculationData.print_details.print_id != 0){	
		    var block_B = rtCalculator.buildBlockB();
		    block_A.appendChild(block_B);
		}

		return block_A;
	}
	,
    build_print_types_select:function(){
		
		// строит и возвращает select для типов нанеснения
		
		var printTypesSelect = document.createElement('SELECT');
		
		var counter = 0;
		// проходим по массиву содержащему id и названия типов нанесения соответствующих данному месту нанесения
		for(var id in rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id].prints){
			// если это заново запускаемый калькулятор сохраняем id первого  нанесения 
			if(typeof rtCalculator.currentCalculationData.print_details.print_id === 'undefined') rtCalculator.currentCalculationData.print_details.print_id = id;
			counter++;
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id].prints[id]));
            printTypesSelect.appendChild(option);
			//// console.log(i + data_obj.places[i].name);
			if(typeof rtCalculator.currentCalculationData.print_details.print_id !== 'undefined'){
			    if(rtCalculator.currentCalculationData.print_details.print_id==id) option.setAttribute("selected",true);
			}
		}
		// если типов нанесения было больше чем одно вставляем в начало Selectа option ' -- выберите вариант -- '
		// и устанавливаем значение описывающиее id типа нанесения в 0
		// id типа нанесения передается в вызывающий метод для дальнейшего построения калькулятора 
		if(counter>1){
			var option = document.createElement('OPTION');
            option.setAttribute("value",0);
            option.appendChild(document.createTextNode(' -выберите вариант- '));
			printTypesSelect.insertBefore(option, printTypesSelect.firstChild); 
            
			// если это не был вызов калькулятора для конкретного существующего расчета 
			// ставим выбранным  option ' -- выберите вариант -- '
			// и устанавливаем print_id = 0
			if(typeof rtCalculator.dataObj_toEvokeCalculator.currentCalculationData_id === 'undefined'){
				option.setAttribute("selected",true);
				rtCalculator.currentCalculationData.print_details.print_id = 0;
			}
		}

		
		// обработчик события onchange
		printTypesSelect.onchange = function(){
			
			if(document.getElementById("rtCalculatorBlockB"))document.getElementById("rtCalculatorBlockB").parentNode.removeChild(document.getElementById("rtCalculatorBlockB"));
			if(document.getElementById("rtCalculatorItogDisplay")) document.getElementById("rtCalculatorItogDisplay").innerHTML = '';
			// alert('printTypesSelect');
			var place_id  = rtCalculator.currentCalculationData.print_details.place_id;
			rtCalculator.currentCalculationData.print_details = {};
			rtCalculator.currentCalculationData.print_details.dop_params = {};
			rtCalculator.currentCalculationData.print_details.place_id = place_id;
			rtCalculator.currentCalculationData.print_details.print_id = this.options[this.selectedIndex].value;
			
			var block_B = rtCalculator.buildBlockB();
			
			document.getElementById("rtCalculatorBlockA").appendChild(block_B);
			// метод осуществляющий итоговый расчет 
		    // и помещающий итоговые данные в сторку ИТОГО
		    rtCalculator.makeProcessing();
			
		}
		//alert(rtCalculator.currentCalculationData.print_details.print_id);
		if(typeof rtCalculator.currentCalculationData.print_details.print_id === 'undefined'){
		    var printTypesSelect = document.createElement('DIV');
			printTypesSelect.appendChild(document.createTextNode("Ошибка: не определен ID типа нанесения "));
			
		}
		
		return printTypesSelect;
	}
	,
	buildBlockB:function(){
		
		var blockB = document.createElement('DIV');
		blockB.id = 'rtCalculatorBlockB';
		blockB.className = 'rtCalculatorBlockB';
		var br = document.createElement('BR');
		// выбираем данные выбранного нанесения и выводим их в калькулятор
		var currRrintParams = rtCalculator.setCurrPrintParams();

		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(currRrintParams);
	
		
		// если был построен blockB то по окончании вывода калькулятора в поток можно запускать 
		// rtCalculator.makeProcessing() и подгружать Итоговые суммы
		rtCalculator.makeProcessingFlag = true; 
		
		return blockB;
	}
	,
	setCurrPrintParams:function(){
		
        // console.log('>>> setCurrPrintParams');
		// console.log(rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id]);
		// console.log('<<< setCurrPrintParams');
		
		var clear_div = document.createElement('DIV');
	    clear_div.className = "clear_div";
		
		var printParamsBox = document.createElement('DIV');
		
		// определяем переменную содержащую массив данных относящихся к текущему типу нанесения
		var CurrPrintTypeData = rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id];
		
		// select для возможных цветов, в принипе этот селект даже должен быть не для цветов а для любого параметра 
		// который определяется по вертикали в таблице прайса 
        //                  [цвет] => Array   
        //                        ([белый] => Array([percentage] => 1.00 )
		//						   [серебро] => Array([percentage] => 1.20 ))

	  
		
        if(CurrPrintTypeData['y_price_param']){
		    // содержит всё название подарздела контейнеры для селектов ЦМИКов и т.д.
			var YPriceParamDivContainer = document.createElement('DIV');
            YPriceParamDivContainer.className = 'YPriceParamDivContainer';
			
			// содержит селекты в обертках
			var YPriceParamDiv = document.createElement('DIV');
            YPriceParamDiv.id = 'YPriceParamDiv';
			YPriceParamDiv.className = 'YPriceParamDiv';
			
			// содержит ЦМИКи
			var YPriceParamCMYKdiv = document.createElement('DIV');
			YPriceParamCMYKdiv.className = 'YPriceParamCMYKdiv';
			
			// содержит поле ввода ЦМИКа
			var YPriceParamCMYK = document.createElement('DIV');
			YPriceParamCMYK.className = 'YPriceParamCMYK';
			YPriceParamCMYK.setAttribute("contenteditable",true);
			YPriceParamCMYK.onblur =  rtCalculator.onblurCMYK;
			
			var YPriceParamSelect = document.createElement('SELECT');
			var YPriceParamSelectWrap =  document.createElement('DIV');
			// метод onchangeYPriceParamSelect пикрепляется к Селекту здесь и пикрепляется к добавляемым селектам ниже в специальном цикле 
			YPriceParamSelect.onchange = function(){ rtCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
			// добавляем теги OPTION
			for(var color in CurrPrintTypeData['y_price_param']){
				
				var option = document.createElement('OPTION');
				option.setAttribute("value",CurrPrintTypeData['y_price_param'][color]['percentage']);
				option.setAttribute("item_id",CurrPrintTypeData['y_price_param'][color]['item_id']);
				option.appendChild(document.createTextNode(color));
				YPriceParamSelect.appendChild(option);
			}
			
			// добавляем поле Выбрать в начало селекта
			var option = document.createElement('OPTION');
            option.setAttribute("value",0);
            option.appendChild(document.createTextNode(' -- выбрать -- '));
			YPriceParamSelect.insertBefore(option, YPriceParamSelect.firstChild); 
			
			// ссылка добавить цвет
			var addYPriceParamLink = document.createElement('A');
			addYPriceParamLink.href = '#';
			addYPriceParamLink.id = 'calculatoraddYPriceParamLink';
			addYPriceParamLink.innerHTML = 'добавить цвет';
			addYPriceParamLink.onclick =  function(){
				// если количество селектов меньше рядов в таблице прайса то можем добавлять новый 
				// если сравнялось или больше добавлять не можем
				if(YPriceParamDiv.getElementsByTagName('SELECT').length < rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1){
					var YPriceParamSelectClone = YPriceParamSelect.cloneNode(true);
	
					// навешиваем обработчик события селекту, потому что при YPriceParamSelect.cloneNode(true); он слетает
					YPriceParamSelectClone.onchange = function(){ rtCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
					
					var YPriceParamSelectWrapClone = YPriceParamSelectWrap.cloneNode();
					YPriceParamSelectWrapClone.appendChild(YPriceParamSelectClone);
					YPriceParamDiv.appendChild(YPriceParamSelectWrapClone);
					
					var YPriceParamCMYKсlone = YPriceParamCMYK.cloneNode(true);
					YPriceParamCMYKсlone.onblur = rtCalculator.onblurCMYK;
					YPriceParamCMYKdiv.appendChild(YPriceParamCMYKсlone);
					
					
						
				}
				if(YPriceParamDiv.getElementsByTagName('SELECT').length >= rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1){
					// скрываем ссылку добавления если она есть
				   this.className += ' hidden';	
				}
			}
			
			// добавляем один или несколько селектов в калькулятор в зависимости от того был он вызван 
			// для уже существующего расчета или для нового расчета 
			if(typeof rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam !== 'undefined'){
				for(var i = 0;i < rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length; i++){ 
				     // Select
				     var YPriceParamSelectClone = YPriceParamSelect.cloneNode(true);
					 var YPriceParamSelectWrapClone = YPriceParamSelectWrap.cloneNode(true);
					 var optionsArr = YPriceParamSelectClone.getElementsByTagName("OPTION");
					 //var optionsArr = YPriceParamSelectClone.options;
					 for(var j in optionsArr){
						if(optionsArr[j] && optionsArr[j].nodeType ==1 && optionsArr[j].nodeName == 'OPTION'){ 
							if(optionsArr[j].getAttribute("item_id") && optionsArr[j].getAttribute("item_id") == rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].id) optionsArr[j].setAttribute("selected",true);
						}
					 }
					 
					 //YPriceParamSelectClone.options[parseInt(rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].id)].setAttribute("selected",true);
					 // Select
				     YPriceParamSelectWrapClone.appendChild(YPriceParamSelectClone);
					 YPriceParamDiv.appendChild(YPriceParamSelectWrapClone);
					 // CMYK
					 var YPriceParamCMYKсlone = YPriceParamCMYK.cloneNode(true);
					 if(rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].cmyk) YPriceParamCMYKсlone.innerHTML = rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].cmyk;
					 YPriceParamCMYKсlone.onblur = rtCalculator.onblurCMYK;
				     YPriceParamCMYKdiv.appendChild(YPriceParamCMYKсlone);
				}
				var selectsArr = YPriceParamDiv.getElementsByTagName("SELECT");
				// навешиваем обработчики события каждому селекту, потому что при YPriceParamSelect.cloneNode(true); они слетают
				for(var i in selectsArr){
					selectsArr[i].onchange = function(){ rtCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
				}
			}
			else{
				// Select
				YPriceParamSelectWrap.appendChild(YPriceParamSelect);
				YPriceParamDiv.appendChild(YPriceParamSelectWrap);
				// CMYK
				YPriceParamCMYKdiv.appendChild(YPriceParamCMYK);
				
				rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam = [];
				rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam.push({'id':0,'coeff':1});
			}
			
			var title = document.createElement('DIV');
			title.className = "calculatorTitle";
			title.innerHTML = 'Цвет: ';
			
			
			var elementsBox = document.createElement('DIV');
		    elementsBox.className = "calculatorElementsBox";
		 
			
			elementsBox.appendChild(YPriceParamDiv);
			// если количество селектов меньше рядов в таблице прайса то добавляем сслыку добавления новых селектов
			if(YPriceParamDiv.getElementsByTagName('SELECT').length < rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1) elementsBox.appendChild(addYPriceParamLink);
			
			YPriceParamDivContainer.appendChild(title);
			YPriceParamDivContainer.appendChild(elementsBox);
			YPriceParamDivContainer.appendChild(YPriceParamCMYKdiv);
			YPriceParamDivContainer.appendChild(clear_div.cloneNode(true));
			printParamsBox.appendChild(YPriceParamDivContainer);
			
		}
		
		if(CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id]){
			// собираем данные для расчета
			// площади нанесения
			// if(typeof rtCalculator.dataForProcessing['coefficients'] === 'undefined') rtCalculator.dataForProcessing['coefficients']={};
			// if(typeof rtCalculator.dataForProcessing['dop_params'] === 'undefined') rtCalculator.dataForProcessing['dop_params']={};
			// rtCalculator.dataForProcessing['coefficients']['sizes'] = [];
			// rtCalculator.dataForProcessing['dop_params']['sizes'] = [];
			
			var printSizesSelect = document.createElement('SELECT');
			var printSizesSelectDiv  = document.createElement('DIV');
			printSizesSelectDiv.className = "printSizesSelectDiv";
			
			printSizesSelect.onchange = function(){
				rtCalculator.currentCalculationData.print_details.dop_params.sizes[0] = {'id':this.options[this.selectedIndex].getAttribute('item_id'),'coeff':this.options[this.selectedIndex].value}
				
				rtCalculator.makeProcessing();
			}
			for(var id in CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id]){
				if(typeof rtCalculator.currentCalculationData.print_details.dop_params.sizes === 'undefined'){
					rtCalculator.currentCalculationData.print_details.dop_params.sizes = [];
					rtCalculator.currentCalculationData.print_details.dop_params.sizes[0] = {'id':CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id][id]['item_id'],'coeff':CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id][id]['percentage']};
				}
				
				
				var option = document.createElement('OPTION');


				option.setAttribute("value",CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id][id]['percentage']);
				// id значения (размер нанесения) который будет сохранен в базу данных и по нему будет отстроен калькулятор 
				// в случае вызова по кокретному нанесению
				option.setAttribute("item_id",CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id][id]['item_id']);
				option.appendChild(document.createTextNode(CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id][id]['size']));
				printSizesSelect.appendChild(option);
				
				if(rtCalculator.currentCalculationData.print_details.dop_params.sizes && rtCalculator.currentCalculationData.print_details.dop_params.sizes[0].id == CurrPrintTypeData['sizes'][rtCalculator.currentCalculationData.print_details.place_id][id]['item_id']){
					option.setAttribute("selected",true);
					
				}
				
			}
			
			
			
			
			var title = document.createElement('DIV');
			title.className = "calculatorTitle";
			title.innerHTML = 'Площадь: ';
			
			
			var elementsBox = document.createElement('DIV');
		    elementsBox.className = "calculatorElementsBox";
		 
			
			elementsBox.appendChild(printSizesSelect);
			
			printSizesSelectDiv.appendChild(title);
			printSizesSelectDiv.appendChild(elementsBox);
			printSizesSelectDiv.appendChild(clear_div.cloneNode(true));
			
			
			printParamsBox.appendChild(printSizesSelectDiv);
			
		}
		
		printParamsBox.appendChild(clear_div.cloneNode(true));
		
		// работаем с таблицами цен 
		
		
		// входящяя цена 
		if(CurrPrintTypeData['priceIn_tbl']){
			rtCalculator.price_tblIn = rtCalculator.build_priceTbl(CurrPrintTypeData['priceIn_tbl'],'in');
		    // printParamsBox.appendChild(rtCalculator.price_tblIn);
		}
		else alert('отсутствует прайс входящих цен');
		// исходящяя цена 
		if(CurrPrintTypeData['priceOut_tbl']){
			rtCalculator.price_tblOut = rtCalculator.build_priceTbl(CurrPrintTypeData['priceOut_tbl'],'out');
		    // printParamsBox.appendChild(rtCalculator.price_tblOut);
		}
		else alert('отсутствует прайс исходящих цен');
		
		console.log('>>> после определения Xindex');
		console.log(rtCalculator.currentCalculationData);
		console.log('<<< после определения Xindex');
		// return;
		
		// select для возможных площадей нанесения
		// [sizes] => Array
        //                ( [1] => Array   - ключ id места нанесения 
        //                        ([0] => Array ([print_id] => 13[size] => до 630 см2 (А4)[percentage] => 1.00)
		//						   [1] => Array ([print_id] => 13[size] => до 1260 см2 (А3)[percentage] => 1.50))
		//				  )
		
		var dopParamsArr = [];

		if(CurrPrintTypeData['coeffs']){
			for(var target in CurrPrintTypeData['coeffs']){
				for(var type in CurrPrintTypeData['coeffs'][target]){
					var data = CurrPrintTypeData['coeffs'][target][type];
					// console.log('..');
					// console.log(type);
					// console.log(data);
					//printParamsBox.appendChild(document.createTextNode(data.data[0].coeff));
					//printParamsBox.appendChild(br.cloneNode(true));
					if(data.optional==1){
						// printParamsBox.appendChild(rtCalculator.makeCommonSelect('coeffs',target,type,data));
						dopParamsArr.push(rtCalculator.makeCommonSelect('coeffs',target,type,data));
					}
				    else{// если применяется по умолчанию
					    if(typeof rtCalculator.currentCalculationData.print_details.dop_params.coeffs === 'undefined')
						          rtCalculator.currentCalculationData.print_details.dop_params.coeffs = {};
					    if(typeof rtCalculator.currentCalculationData.print_details.dop_params.coeffs[target] === 'undefined')
								rtCalculator.currentCalculationData.print_details.dop_params.coeffs[target] = {};
						if(typeof rtCalculator.currentCalculationData.print_details.dop_params.coeffs[target][type] === 'undefined'){
								rtCalculator.currentCalculationData.print_details.dop_params.coeffs[target][type] = [];
								for(var index in data.data){
									rtCalculator.currentCalculationData.print_details.dop_params.coeffs[target][type].push({"value": parseFloat(data.data[index].coeff),"id": data.data[index].item_id});
								}
						}
						/*;*/
						
					}
				}
				
			}
		}

		if(CurrPrintTypeData['additions']){
			for(var target in CurrPrintTypeData['additions']){
				for(var type in CurrPrintTypeData['additions'][target]){
					var data = CurrPrintTypeData['additions'][target][type];
					// console.log('..');
					// console.log(data);
					//printParamsBox.appendChild(document.createTextNode(data.data[0].coeff));
					//printParamsBox.appendChild(br.cloneNode(true));

					if(data.optional==1){
						//printParamsBox.appendChild(rtCalculator.makeCommonSelect('additions',target,type,data));
						dopParamsArr.push(rtCalculator.makeCommonSelect('additions',target,type,data));
					}
					else{// если применяется по умолчанию
					    if(typeof rtCalculator.currentCalculationData.print_details.dop_params.additions === 'undefined')
						          rtCalculator.currentCalculationData.print_details.dop_params.additions = {};
					    if(typeof rtCalculator.currentCalculationData.print_details.dop_params.additions[target] === 'undefined')
								rtCalculator.currentCalculationData.print_details.dop_params.additions[target] = {};
						if(typeof rtCalculator.currentCalculationData.print_details.dop_params.additions[target][type] === 'undefined')
								rtCalculator.currentCalculationData.print_details.dop_params.additions[target][type] = [];
						
						// console.log(rtCalculator.currentCalculationData.print_details.dop_params.additions);
						for(var index in data.data){
							rtCalculator.currentCalculationData.print_details.dop_params.additions[target][type].push({"value": parseFloat(data.data[index].value),"id": data.data[index].item_id});
						}
						/*;*/
						
					}
				}
				
			}
		}
		// alert(dopParamsArr.length);
		
		if(dopParamsArr.length>0){
			
			var dopParametrsTitle = document.createElement('DIV');
		    dopParametrsTitle.className = "dopParametrsTitle";
		    dopParametrsTitle.innerHTML = 'Дополнительно:';
		    printParamsBox.appendChild(dopParametrsTitle);
			
			
			var tbl = document.createElement('TABLE');
		    tbl.className = 'dopParametrsTbl';
		    var tr = document.createElement('TR');
		    var td = document.createElement('TD');
			
			for(var i = 0;i < dopParamsArr.length;i++){ 
			    if(0 || (i)%2 == 0)  var trClone = tr.cloneNode(true);
				trClone.appendChild(dopParamsArr[i]);
				if((i+1)%2 == 0) tbl.appendChild(trClone);
			}
			if(9%2 != 0){
				for(var i = 0;i < 3;i++) trClone.appendChild(td.cloneNode(true));
				tbl.appendChild(trClone);
				
			}
			printParamsBox.appendChild(tbl);
		}
		else{
		   var empytDiv = document.createElement('DIV');
		   empytDiv.style.height = '50px';
		   printParamsBox.appendChild(empytDiv);	
		}
		
		
	    var dopParametrsTitle = document.createElement('DIV');
		dopParametrsTitle.className = "dopParametrsTitle";
		dopParametrsTitle.innerHTML = 'Комментарий по нанесению';
		printParamsBox.appendChild(dopParametrsTitle);

		var textarea = document.createElement('TEXTAREA');
		textarea.className = "commentsTextArea";
		if(rtCalculator.currentCalculationData.print_details.comment) textarea.value=rtCalculator.currentCalculationData.print_details.comment;
		textarea.onchange = function(){  rtCalculator.currentCalculationData.print_details.comment = this.value; }
		printParamsBox.appendChild(textarea);
		
		return printParamsBox;
	} 
	,
	makeCommonSelect:function(glob_type,target,type,data){
		
		// складываем элементы в ячейки будующей таблицы
		var docFragment = document.createDocumentFragment();
		var td = document.createElement('TD');	
		//var div = document.createElement('DIV');
		var tdClone = td.cloneNode();
		tdClone.className = 'title';
		tdClone.appendChild(document.createTextNode(data.data[0].title));
		docFragment.appendChild(tdClone);
		
		var commonSelect = document.createElement('SELECT');
		var option = document.createElement('OPTION');
		option.innerHTML = 'Выбрать';
		option.setAttribute("item_id",0);
		commonSelect.appendChild(option);
		
		commonSelect.onchange = function(){
			var item_id = this.options[this.selectedIndex].getAttribute('item_id');
			if(item_id != 0){
				if(typeof rtCalculator.currentCalculationData.print_details.dop_params[glob_type] === 'undefined')
					  rtCalculator.currentCalculationData.print_details.dop_params[glob_type] = {};
			    if(typeof rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target] === 'undefined')
					rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target] = {};
			    if(typeof rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type] === 'undefined')
					rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type] = [];
				
				var obj = {"value": parseFloat(this.options[this.selectedIndex].value),"id": this.options[this.selectedIndex].getAttribute('item_id')};
                if(this.options[this.selectedIndex].getAttribute('multi')){
					var nextTr = rtCalculator.nextTag(this.parentNode);
					obj.multi = parseInt(nextTr.getElementsByTagName('INPUT')[0].value);
				}
				rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type].push(obj);
				
				rtCalculator.makeProcessing();
				
			}
			else{
				if(rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type]){

					delete rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type];
					rtCalculator.makeProcessing();
				}
			}

		}
		
		for(var index in data.data){
			var option = document.createElement('OPTION');
			option.innerHTML = 'Применить';
			if(data.data[index].coeff) option.value = data.data[index].coeff;
			if(data.data[index].value) option.value = data.data[index].value;
			if(data.data[index].item_id) option.setAttribute("item_id",data.data[index].item_id);
			if(data.multi==1) option.setAttribute("multi",1);
			
			if(rtCalculator.currentCalculationData.print_details.dop_params[glob_type] && rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target] && rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type]){
			     if(rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].id==data.data[index].item_id) option.setAttribute("selected",true);
			}
			
			commonSelect.appendChild(option);
		}
		
		var tdClone = td.cloneNode();
		tdClone.appendChild(commonSelect);
		tdClone.className = 'select';
		docFragment.appendChild(tdClone);
		
		if(data.multi==1){
			 var input_field =  document.createElement('INPUT');
			 input_field.value = 1;
			 if(rtCalculator.currentCalculationData.print_details.dop_params[glob_type] && rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target] && rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type]){
			     if(rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi) input_field.value = rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi;
			}
			 
			 
			 input_field.onkeyup = function(){
				 if(typeof rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi !== 'undefined'){
					rtCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi = this.value;
				    rtCalculator.makeProcessing();
				 }
			 }
		}
		
		var tdClone = td.cloneNode();
		tdClone.className = 'multi';
		if(input_field) tdClone.appendChild(input_field);
		docFragment.appendChild(tdClone);
		
		return docFragment;
	}
	,
	makeProcessing:function(){
		// alert('makeProcessing');
		
		if(typeof rtCalculator.makeProcessingFlag !== 'undefined') delete rtCalculator.makeProcessingFlag;
		
		// обращаемся к ряду таблицы цен, 
		// по значению параметра rtCalculator.currentCalculationData.print_details.priceOut_tblYindex
		// и выбираем нужную ячейку 
		// по значению параметра rtCalculator.currentCalculationData.print_details.priceOut_tblXindex
		// console.log('>>> rtCalculator.currentCalculationData <<<');
		// console.log( rtCalculator.currentCalculationData);
		// console.log('>>><<<');
		//alert(rtCalculator.currentCalculationData.print_details.priceIn_tblXindex+' ++ '+rtCalculator.currentCalculationData.print_details.priceOut_tblXindex);
		// снимаем значение price с таблицы прайса
		
		console.log('>>> priceOut_tbl <<<');
		console.log( rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0] );
		//alert(rtCalculator.currentCalculationData.print_details.priceOut_tblXindex);
		var priceOut_tblYindex = (typeof rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam !== 'undefined')? rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length:1;
		var priceIn_tblYindex = (typeof rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam !== 'undefined')? rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length:1;
		
		var price_out =rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0][priceOut_tblYindex][rtCalculator.currentCalculationData.print_details.priceOut_tblXindex];
		var price_in =rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceIn_tbl[0][priceIn_tblYindex][rtCalculator.currentCalculationData.print_details.priceIn_tblXindex];
		// alert('out '+price_out+' - in '+price_in);
		// если полученная цена оказалась равна 0 то значит стоимость не  указана
	    if(parseFloat(price_out) == 0 || parseFloat(price_in) == 0){
			
			var sourse_tbls = ['priceIn_tbl','priceOut_tbl'];
			for(index in sourse_tbls){
			    var sourse_tblXindex = sourse_tbls[index]+'Xindex';
				// alert(rtCalculator.currentCalculationData.print_details.print_id); 
				// alert(rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id][sourse_tbls[index]][0][0]['maxXIndex']);
				// alert(rtCalculator.currentCalculationData.print_details[sourse_tblXindex]);
				// если это последние ряды прайс значит это лимит
				if(rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id][sourse_tbls[index]][0][0]['maxXIndex'] == rtCalculator.currentCalculationData.print_details[sourse_tblXindex]){
	
					var limimt =parseInt(rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id][sourse_tbls[index]][0][0][rtCalculator.currentCalculationData.print_details[sourse_tblXindex]]);
					
					rtCalculator.cancelSaveReslut = true;
					var caution = 'Цена не может быть расчитана, достигнут лимит тиража в '+limimt+' шт.\rтребуется индивидуальный расчет';
					break;
				}
				else{//иначе это индивидуальный расчет cancelCalculator
				    //if(typeof rtCalculator.cancelSaveReslut === 'undefined')
					rtCalculator.cancelSaveReslut = true;
					var caution = 'Рассчет с данными параметрами не может быть произведен \rтребуется индивидуальный расчет';
				}
			}
		}
					
		if(rtCalculator.currentCalculationData.print_details.lackOfQuantOutPrice){
			price_out = price_out*rtCalculator.currentCalculationData.print_details.minQuantOutPrice/rtCalculator.currentCalculationData.quantity;
		}
		if(rtCalculator.currentCalculationData.print_details.lackOfQuantOutPrice){
			price_in = price_in*rtCalculator.currentCalculationData.print_details.minQuantInPrice/rtCalculator.currentCalculationData.quantity;
		}
			
		//console.log('>>> YPriceParam.length  --   priceIn_tblXindex  priceOut_tblXindex  --  price_in  price_out <<<');
		//console.log( rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length + ' -- '+rtCalculator.currentCalculationData.print_details.priceIn_tblXindex + ' '+ rtCalculator.currentCalculationData.print_details.priceOut_tblXindex+' -- '+ price_in + ' '+ price_out );
		
		// КОЭФФИЦИЕНТЫ НА ПРАЙС
		// КОЭФФИЦИЕНТЫ НА ИТОГОВУЮ СУММУ
		// перебираем rtCalculator.currentCalculationData.print_details.
		// в нем содержатся коэффициенты по Y параметру таблицы прайса и по размеру нанесения
		var price_coeff = summ_coeff = 1;
		var price_coeff_list  = summ_coefficient_list = '';
		for(glob_type in rtCalculator.currentCalculationData.print_details.dop_params){
			if(glob_type=='YPriceParam' || glob_type=='sizes'){
				var set = rtCalculator.currentCalculationData.print_details.dop_params[glob_type];
				for(var i = 0;i < set.length;i++){ 
				    // подстраховка
				    if(set[i].coeff == 0) set[i].coeff = 1;
					price_coeff *= set[i].coeff;
					price_coeff_list += glob_type +' - '+ set[i].coeff+', ';
				}
			}
			if(glob_type=='coeffs'){
				var data = rtCalculator.currentCalculationData.print_details.dop_params[glob_type];
				for(target in data){
					
					for(var type in data[target]){
						var set = data[target][type];
						for(var i = 0;i < set.length;i++){ 
						    // подстраховка
							if(set[i].value == 0) set[i].value = 1;
							if(set[i].multi && set[i].multi == 0) set[i].multi = 1;
							
							if(target=='price'){
								price_coeff *= (set[i].multi)?  set[i].value*set[i].multi : set[i].value;
								price_coeff_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
							if(target=='summ'){
								summ_coeff *= (set[i].multi)?  set[i].value*set[i].multi : set[i].value;
								summ_coefficient_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
						}								
					}
				}
			}
		}
		//// console.log('price coefficient');
		//// console.log(price_coeff);
		//// console.log(price_coeff_list);
		//// console.log('summ coefficient');
		//// console.log(summ_coeff);
		//// console.log(summ_coefficient_list);
		
		
		// перебираем rtCalculator.currentCalculationData.print_details.
	
		
	
	
	    // НАДБАВКИ НА ИТОГОВУЮ СУММУ
		// перебираем rtCalculator.currentCalculationData.print_details.
		var price_addition = summ_addition = 0;
		var price_additions_list = summ_additions_list = '';
		for(glob_type in rtCalculator.currentCalculationData.print_details.dop_params){
			if(glob_type=='additions'){
				var data = rtCalculator.currentCalculationData.print_details.dop_params[glob_type];
				for(target in data){
					
					for(var type in data[target]){
						var set = data[target][type];
						for(var i = 0;i < set.length;i++){ 
						    // подстраховка
							if(set[i].multi && set[i].multi == 0) set[i].multi = 1;
							
							if(target=='price'){
								price_addition += (set[i].multi)?  set[i].value*set[i].multi : set[i].value;
								price_additions_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
							if(target=='summ'){
								summ_addition += (set[i].multi)?  set[i].value*set[i].multi : set[i].value;
								summ_additions_list += type + ' - '+((set[i].multi)? set[i].multi + ' раз по ':'')+ set[i].value+', ';
							}
						}								
					}
				}
			}
		}
		//// console.log('price additions');
		//// console.log(price_addition);
		//// console.log(price_additions_list);
		//// console.log('summ additions');
		//// console.log(summ_addition);
		//// console.log(summ_additions_list);
		
		console.log('price additions');
		console.log(rtCalculator.currentCalculationData);

		
		
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!  
		// CXEMA  - total_price = ((((price*price_coeff)+price_addition)*quantity)*sum_coeff)+sum_addition
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 
		
		var total_price_out = ((((price_out*price_coeff)+price_addition)*rtCalculator.currentCalculationData.quantity)*summ_coeff)+summ_addition;
		var total_price_in  = ((((price_in*price_coeff)+price_addition)*rtCalculator.currentCalculationData.quantity)*summ_coeff)+summ_addition;
		
		total_price_out = Math.round(total_price_out * 100) / 100 ;
		total_price_in = Math.round(total_price_in * 100) / 100 ;
		
	    rtCalculator.currentCalculationData.price_out = Math.round(total_price_out/rtCalculator.currentCalculationData.quantity * 100) / 100;
		rtCalculator.currentCalculationData.price_in  = Math.round(total_price_in/rtCalculator.currentCalculationData.quantity * 100) / 100;
		
		total_price_out = Math.round((rtCalculator.currentCalculationData.price_out*rtCalculator.currentCalculationData.quantity) * 100) / 100 ;
		total_price_in = Math.round((rtCalculator.currentCalculationData.price_in*rtCalculator.currentCalculationData.quantity) * 100) / 100 ;
		
		var total_str  = '';
		var total_details  = '';
	    if(typeof rtCalculator.cancelSaveReslut === 'undefined'){
			var total_tbl = document.createElement('TABLE');
			total_tbl.className = 'itogDisplayTbl';
			var tr = document.createElement('TR');
			var td = document.createElement('TD');
			
			TRclone = tr.cloneNode(true);
			TRclone.className = 'head';
			tdClone = td.cloneNode(true);
			// tdClone.innerHTML = '';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'штука';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = 'тираж';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
			
			
			TRclone = tr.cloneNode(true);
			tdClone = td.cloneNode(true);
			tdClone .className = 'title';
			tdClone.innerHTML = 'Входящая:';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = ((rtCalculator.currentCalculationData.price_in).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = ((total_price_in).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
	//alert("1231231.23".replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 "));		
			
			TRclone = tr.cloneNode(true);
			tdClone = td.cloneNode(true);
			tdClone .className = 'title';
			tdClone.innerHTML = 'Исходящая:';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = ((rtCalculator.currentCalculationData.price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = ((total_price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
	
		}
		else{
			alert(caution);
	
			delete rtCalculator.cancelSaveReslut;
			if(typeof document.getElementById("calculatorsaveResultBtn") !== 'undefined')  document.getElementById("calculatorsaveResultBtn").style.display = 'none';
			
		}

		 //console.log('>>> total_str <<<');
		// console.log('in  - '+(rtCalculator.currentCalculationData.price_in).toFixed(2)+' '+(total_price_in).toFixed(2)+' out - '+(rtCalculator.currentCalculationData.price_out).toFixed(2)+' '+(total_price_out).toFixed(2));   
		rtCalculator.total_details = document.createElement('div');	
		rtCalculator.total_details.className ="calculatorTotalDetails";

		var total_details = '<span style="color:#FF6633;">коэффициэнты прайса:</span> '+price_coeff_list+'<br>';
		total_details += '<span style="color:#FF6633;">надбавки прайса:</span> '+price_additions_list+'<br>';
		total_details += '<span style="color:#FF6633;">коэффициэнты суммы:</span> '+summ_coefficient_list+'<br>';
		total_details += '<span style="color:#FF6633;">надбавки суммы:</span> '+summ_additions_list+'<br>';
        rtCalculator.total_details.innerHTML = total_details;
		if(document.getElementById("showProcessingDetailsBoxTotalDetails")){
			document.getElementById("showProcessingDetailsBoxTotalDetails").innerHTML = '';
			document.getElementById("showProcessingDetailsBoxTotalDetails").appendChild(rtCalculator.total_details);
		}
			
		if(total_tbl){
			
			// дисплей итоговых подсчетов
			if(document.getElementById("rtCalculatorItogDisplay")){
				rtCalculatorItogDisplay = document.getElementById("rtCalculatorItogDisplay");
				rtCalculatorItogDisplay.innerHTML = '';
			}
			else{
				var rtCalculatorItogDisplay = document.createElement('DIV');
			    rtCalculatorItogDisplay.id = 'rtCalculatorItogDisplay';
			}
			var dopParametrsTitle = document.createElement('DIV');
			dopParametrsTitle.className = "dopParametrsTitle";
			dopParametrsTitle.innerHTML = 'Цена';
			rtCalculatorItogDisplay.appendChild(dopParametrsTitle);
		
			rtCalculatorItogDisplay.appendChild(total_tbl);
			
			var BtnsDiv = document.createElement('DIV');
			BtnsDiv.className = 'BtnsDiv';
			
			var showProcDetBtn = document.createElement('DIV');
			showProcDetBtn.className = 'showProcessingDetailsBtn';
			showProcDetBtn.innerHTML = 'Включить вкладку прайс';
			showProcDetBtn.onclick =  rtCalculator.showProcessingDetails;
			
			BtnsDiv.appendChild(showProcDetBtn);
			
			var saveBtn = document.createElement('DIV');
			saveBtn.className = 'saveBtn';
			saveBtn.innerHTML = 'Сохранить расчет';
			saveBtn.onclick =  rtCalculator.saveCalculatorResult;
			
			BtnsDiv.appendChild(saveBtn);
			rtCalculatorItogDisplay.appendChild(BtnsDiv);
	
			
			document.getElementById("mainCalculatorBox").appendChild(rtCalculatorItogDisplay);
		}
		
		
		
		
	}
	,
	showProcessingDetails:function(){

		var box = document.createElement('DIV');
		box.id = "showProcessingDetailsBox";
		//box.style.width = '300px';
		box.style.display = "none";
		box.appendChild(rtCalculator.price_tblIn);
		box.appendChild(rtCalculator.price_tblOut);
		var total_details = document.createElement('DIV');
		total_details.id = "showProcessingDetailsBoxTotalDetails";
		
		total_details.appendChild(rtCalculator.total_details);
		box.appendChild(total_details);
		document.body.appendChild(box);
		
		$("#showProcessingDetailsBox").dialog({autoOpen: false, position:{ at: "top+35%", of: window } ,title: "Детали расчета",width: 400,close: function() {this.remove();$("#showProcessingDetailsBox").remove();}});
		$("#showProcessingDetailsBox").dialog("open");
		 //
        //
	}
	,
	saveCalculatorResult:function(){
		// в этом методе две задачи 
		// 1. отправить данные на сервер
		// 2. закрыть калькулятор
		// console.log(rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id]); 
		// корректируем объект с информацией удаляем не нужные для сохранение данные, добавляем нужные
		rtCalculator.currentCalculationData.print_details.place_type =  rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id].name;
		rtCalculator.currentCalculationData.print_details.print_type =  rtCalculator.calculatorParamsObj.places[rtCalculator.currentCalculationData.print_details.place_id].prints[rtCalculator.currentCalculationData.print_details.print_id];
		
		
		if(typeof rtCalculator.currentCalculationData.glob_type !== 'undefined') delete rtCalculator.currentCalculationData.glob_type;
		if(typeof rtCalculator.currentCalculationData.dop_row_id !== 'undefined') delete rtCalculator.currentCalculationData.dop_row_id;

		if(typeof rtCalculator.currentCalculationData.print_details.priceOut_tblXindex !== 'undefined') delete rtCalculator.currentCalculationData.print_details.priceOut_tblXindex;
		if(typeof rtCalculator.currentCalculationData.print_details.priceIn_tblXindex !== 'undefined') delete rtCalculator.currentCalculationData.print_details.priceIn_tblXindex;
		if(typeof rtCalculator.currentCalculationData.print_details.priceOut_tbl !== 'undefined') delete rtCalculator.currentCalculationData.print_details.priceOut_tbl;
		if(typeof rtCalculator.currentCalculationData.print_details.priceIn_tbl !== 'undefined') delete rtCalculator.currentCalculationData.print_details.priceIn_tbl;
		if(typeof rtCalculator.price_tblIn !== 'undefined') delete rtCalculator.price_tblIn;
		if(typeof rtCalculator.price_tblOut !== 'undefined') delete rtCalculator.price_tblOut;
		if(typeof rtCalculator.total_details !== 'undefined') delete rtCalculator.total_details;
		
		
		if(typeof rtCalculator.dataObj_toEvokeCalculator !== 'undefined') delete rtCalculator.dataObj_toEvokeCalculator;
		
		// console.log('>>> saveCalculatorResult --');
		// console.log(rtCalculator.currentCalculationData);
        // console.log('<<< saveCalculatorResult --');
		
		//return;
		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_calculator_result=1&details='+JSON.stringify(rtCalculator.currentCalculationData));
		rtCalculator.send_ajax(url,callback);
		//alert(url);//
		$("#calculatorsaveResultBtn").remove();
		
		
		function callback(response){ 
		    
			// console.log(response);
			location.reload();
		}
		
	}
	,
	onblurCMYK:function(e){ 
	    e = e || window.event;
	    // устанавливаем текущюю ячейку
	    cur_cell = e.target || e.srcElement;
		var container = cur_cell.parentNode;
		var cellsArr = container.getElementsByTagName("DIV");
		for( var i = 0; i < cellsArr.length; i++){
			if(cellsArr[i] == cur_cell) break;//alert(i);
		}
		rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].cmyk = cur_cell.innerHTML;
	}
	,
	onchangeYPriceParamSelect:function(YPriceParamDiv,YPriceParamCMYKdiv){
		// здесь нам надо пройти по всем селектам в YPriceParamDiv и собрать данные о выбранных полях
		// чтобы сохранить их в dataForProcessing а затем запустить rtCalculator.makeProcessing();
		
		// затираем данные по цветам которые были до этого
		if(rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam) rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam = [];
		
		var selectsArr = YPriceParamDiv.getElementsByTagName("SELECT");
		var CMYKsArr = YPriceParamCMYKdiv.getElementsByTagName("DIV");
		
		//alert(selectsArr.length);
		for( var i = 0; i < selectsArr.length; i++){
			var value = selectsArr[i].options[selectsArr[i].selectedIndex].value;
			var item_id = selectsArr[i].options[selectsArr[i].selectedIndex].getAttribute('item_id');
			// если value != 0(0 равно вспомогательное значение "Выбрать"), значит выбор в селекте сделан 
			// добавляем его в dataForProcessing
			if(value != 0){
				rtCalculator.currentCalculationData.print_details.dop_params.YPriceParam.push({'id':item_id,'coeff':value}); 
			}
			// если value == 0(0 равно вспомогательное значение "Выбрать"), значит выбор в селекте не сделан
			// удаляем этот селект
			// if(value == 0) selectsArr[i].parentNode.parentNode.removeChild(selectsArr[i].parentNode);
			if(value == 0){
				selectsArr[i].parentNode.parentNode.removeChild(selectsArr[i].parentNode);
				CMYKsArr[i].parentNode.removeChild(CMYKsArr[i]);
			}
		}
		
		// alert(YPriceParamDiv.getElementsByTagName('SELECT').length);
		//
		// если количество селектов меньше количества рядов в прайсе открываем ссылку для добавления новых селектов (она могла быть скрыта)
		if(YPriceParamDiv.getElementsByTagName('SELECT').length <rtCalculator.calculatorParamsObj.print_types[rtCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1){
		   document.getElementById('calculatoraddYPriceParamLink').className = '';
		}
		
		// alert(rtCalculator.currentCalculationData.print_details.priceOut_tblYindex);
		rtCalculator.makeProcessing();
	}
	,
	build_priceTbl:function(tbl,type){
	 
			// строим таблицу с ценами
			var tbl = tbl[0];
			var tbl_html = document.createElement('TABLE');
			tbl_html.className = "calculatorPriceTbl";
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
				
				/*if(row == 0){
					var maxColInex = 1;
					for(var counter = 1 ;typeof tbl[row][counter] !== 'undefined' ; counter++){
						if(parseFloat(tbl[row][counter])==0) break;
						maxColInex = counter;
					}
				}*/
				// перебераем ячейки таблицы озаглавленные 1,2,3,4 и т.д.
				for(var counter = 1 ;typeof tbl[row][counter] !== 'undefined'; counter++){
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
						// если значение ячейки меньше или равно значения параметра quantity, значит мы еще не вышли из диапазона, значение сохраняем
						if(type=='out'){
							if(rtCalculator.currentCalculationData.quantity < parseFloat(tbl[row][1])){
								var priceOut_tblXindex = 1;
								rtCalculator.currentCalculationData.print_details.lackOfQuantOutPrice = true;
								rtCalculator.currentCalculationData.print_details.minQuantOutPrice = parseInt(tbl[row][1]);
							}
						    else if(rtCalculator.currentCalculationData.quantity >= parseFloat(tbl[row][counter]) && parseFloat(tbl[row][counter])>0){
								// alert(tbl[row][counter]+' '+counter);
								var priceOut_tblXindex = counter;
								if(typeof rtCalculator.currentCalculationData.print_details.lackOfQuantOutPrice !== 'undefined') delete rtCalculator.currentCalculationData.print_details.lackOfQuantOutPrice;
								if(typeof rtCalculator.currentCalculationData.print_details.minQuantOutPrice !== 'undefined') delete rtCalculator.currentCalculationData.print_details.minQuantOutPrice;
							}
						}
						if(type=='in'){
							if(rtCalculator.currentCalculationData.quantity < parseFloat(tbl[row][1])){
								var priceIn_tblXindex = 1;
								rtCalculator.currentCalculationData.print_details.lackOfQuantInPrice = true;
								rtCalculator.currentCalculationData.print_details.minQuantInPrice = parseInt(tbl[row][1]);
							}
						    else if(rtCalculator.currentCalculationData.quantity >= parseFloat(tbl[row][counter]) && parseFloat(tbl[row][counter])>0){
								// alert(tbl[row][counter]+' '+counter);
								var priceIn_tblXindex = counter;
								if(typeof rtCalculator.currentCalculationData.print_details.lackOfQuantInPrice !== 'undefined') delete rtCalculator.currentCalculationData.print_details.lackOfQuantInPrice;
								if(typeof rtCalculator.currentCalculationData.print_details.minQuantInPrice !== 'undefined') delete rtCalculator.currentCalculationData.print_details.minQuantInPrice;
							}
						}
						
						//// console.log(parseInt(tbl[row][counter])+' '+rtCalculator.currentCalculationData.quantity);
					}
					
					
				}
				
				tbl_html.appendChild(tr);
				
			}
			// собираем данные для расчета
			// для определения текущей цены
			if(type=='out'){
				if(typeof rtCalculator.currentCalculationData.print_details.priceOut_tblXindex=== 'undefined'){ 
				    // alert('out>'+priceOut_tblXindex);
					rtCalculator.currentCalculationData.print_details.priceOut_tblXindex = priceOut_tblXindex;
					// alert(rtCalculator.currentCalculationData.print_details.priceOut_tblXindex);
				}
			}
			else if(type=='in'){
			    if(typeof rtCalculator.currentCalculationData.print_details.priceIn_tblXindex=== 'undefined'){ 
				     // alert('in>'+priceIn_tblXindex);
					rtCalculator.currentCalculationData.print_details.priceIn_tblXindex = priceIn_tblXindex;
					// alert(rtCalculator.currentCalculationData.print_details.priceIn_tblXindex);
				}
			}
				
			//// console.log(rtCalculator.dataForProcessing['price']);
			if(rtCalculator.currentCalculationData.print_details.lackOfQuantOutPrice){
				alert("Минимальный тираж для данного типа нанесения - "+rtCalculator.currentCalculationData.print_details.minQuantOutPrice+"шт \rстоимость будет расчитана как для минимального тиража");	
			}
			return tbl_html;
			
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
					if(type == 'glob_counter' || type == 'master_btn' || type == 'name') continue;
					
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
								tds_arr[j].onkeyup = function(){
								   if(rtCalculator.cur_cell.getAttribute('type') && rtCalculator.cur_cell.getAttribute('type')== 'quantity'){
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
									   this.complite_input();
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
		rtCalculator.previos_data['price_in_summ'] = rtCalculator.tbl_model[row_id]['price_in_summ'];
		rtCalculator.previos_data['price_out_summ'] = rtCalculator.tbl_model[row_id]['price_out_summ'];
		rtCalculator.previos_data['in_summ'] = rtCalculator.tbl_model[row_id]['in_summ'];
		rtCalculator.previos_data['out_summ'] = rtCalculator.tbl_model[row_id]['out_summ'];
		rtCalculator.previos_data['delta'] = rtCalculator.tbl_model[row_id]['delta'];
		rtCalculator.previos_data['margin'] = rtCalculator.tbl_model[row_id]['margin'];
		
	    
		// проверяем есть ли в ячейке расчеты нанесения
		var printsExitst = false;
		var tds_arr = cur_tr.getElementsByTagName('td');
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type') && tds_arr[j].getAttribute('type') == 'print_exists_flag'){
				// отправляем запрос на сервер
				if(tds_arr[j].innerHTML == 'yes'){
					printsExitst = true;
				}
			}
		}
		
		if(printsExitst){// если нанесение есть то нужно отправлять запрос на сервер для обсчета нанесений в соответсвии с новым тиражом
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_quantity_and_calculators=1&quantity='+cell.innerHTML+'&id='+row_id);
		    rtCalculator.send_ajax(url,callbackPrintsExitst);
		}
		else{// отправляем запрос на изменение только лишь значения тиража в базе данных 
		    var url = OS_HOST+'?' + addOrReplaceGetOnURL('change_quantity=1&quantity='+cell.innerHTML+'&id='+row_id);
		    rtCalculator.send_ajax(url,callbackOnlyQuantity);
		}
						
		function callbackPrintsExitst(response){
			// alert(response);
			var response_obj = JSON.parse(response);
							
			if(response_obj.lackOfQuantity){
				 var str =''; 
				 for(var index in response_obj.lackOfQuantity){
					 str += (parseInt(index)+1)+'). '+response_obj.lackOfQuantity[index].print_type+', мин тираж - '+response_obj.lackOfQuantity[index].minQuantity+"\r";  
				 }
				 alert("Тираж  меньше минимального тиража для нанесения(ний):\r"+str+"стоимость будет пересчитана как для минимального тиража");
			}
			if(response_obj.outOfLimit){
				 var str ='';  
				 for(var index in response_obj.outOfLimit){
					 str += (parseInt(index)+1)+'). '+response_obj.outOfLimit[index].print_type+', лимит тиража - '+response_obj.outOfLimit[index].limitValue+"\r";  
				 }
				 alert("Все перерасчеты отклонены!!!\rПотому что имеются нанесения для которых не возможно расчитать цену - достигнут лимит тиража :\r"+str+"для этих нанесений требуется индивидуальный расчет");
			}
			if(response_obj.needIndividCalculation){ 
				 var str ='';  
				 for(var index in response_obj.needIndividCalculation){
					 str += (parseInt(index)+1)+'). '+response_obj.needIndividCalculation[index].print_type+"\r";  
				 }
				 alert("Все перерасчеты отклонены!!!\rПотому что имеются нанесения для которых не возможно расчитать цену - для этих нанесений требуется индивидуальный расчет :\r"+str+"");
				
			}
			
			//// console.log(response_obj);
			// если ответ был ok значит все нормально изменения сделаны 
			// теперь нужно внести изменения в hmlt
			if(response_obj.result == 'ok'){
				rtCalculator.tbl_model[row_id]['quantity'] =  parseInt(cell.innerHTML) ;
				//// console.log(response_obj.new_sums);
				rtCalculator.tbl_model[row_id]["print_in_summ"] = parseFloat(response_obj.new_sums.summ_in);
				rtCalculator.tbl_model[row_id]["print_out_summ"] = parseFloat(response_obj.new_sums.summ_out);
				rtCalculator.tbl_model[row_id]["print_exists_flag"] = 'yes';
				
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
	change_html:function(row_id){
	
	    // метод который вносит изменения (итоги рассчетов в таблицу HTML)
		
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
				
				
				if(type == 'glob_counter' || type == 'dop_details' || type == 'master_btn' || type == 'name') continue;
				
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
		//// console.log();
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
			if(more_then_one){
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
			if(less_then_one) alert('не возможно создать заказ,\rдля позиции(ий) невыбрано ни одного варианта расчета');
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
					if(tdsArr[j].getAttribute('svetofor') && tdsArr[j].getAttribute('svetofor')=='green'){
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
			if(more_then_one){
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
			if(less_then_one) alert('не возможно создать заказ,\rдля позиции(ий) невыбрано ни одного варианта расчета');
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
	nextTag:function(node){ 
	   var node = node.nextSibling; 
	   return (node && node.nodeType!=1) ? this.nextTag(node) : node; 
	}
	,
	certainTd:function(node,type){ 
	   if(node==null)return false;
	   var node = node.nextSibling; 
	   return (node && node.nodeName=='TD' && node.getAttribute('type')  && node.getAttribute('type')==type) ? node : this.certainTd(node,type); 
	}
}