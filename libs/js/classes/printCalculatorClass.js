var printCalculator = {
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
			printCalculator.send_ajax(url,callback);
			function callback(response){ 
			    // alert(response);
				// этап 1
				if(typeof data_AboutPrintsArr !== 'undefined') delete data_AboutPrintsArr;
				var data_AboutPrintsArr = JSON.parse(response);
				printCalculator.dataObj_toEvokeCalculator = {"art_id":art_id,"dop_data_row_id":dop_data_row_id,"quantity":quantity,"cell":cell};
				
				if(data_AboutPrintsArr.length == 0){
					
					// запускаем калькулятор
					printCalculator.evoke_calculator();
				}
				else{
					// запускаем панель
					printCalculator.launch_dop_uslugi_panel(data_AboutPrintsArr);
				}
			}
			/**/
		//alert(quantity);
		
		}
		else if(calculator_type == 'extra'){
		    alert('для добавления дополнительной услуги, перейдите в карточку позиции');	
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
		printCalculator.uslugi_panel_print_details = [];
		
		printCalculator.currentCalculationData = [];
		for(var i = 0; i < data_AboutPrintsArr.length; i++){
			var tr =  tr.cloneNode(false);
			var td = document.createElement('TD');
			td.style.border = '#CCC solid 1px';
			td.style.padding = '2px 4px';

            // сохраняем данные в общей переменной
			// предварительно трансофорировав сериализованную строку из свойства "print_details" в объект
			var print_details = JSON.parse(data_AboutPrintsArr[i].print_details);
			data_AboutPrintsArr[i].print_details = print_details;
			
			printCalculator.currentCalculationData[i] = data_AboutPrintsArr[i];
			printCalculator.currentCalculationData[i].dop_uslugi_id = data_AboutPrintsArr[i].id;

			if(typeof printCalculator.currentCalculationData[i].id !== 'undefined') delete printCalculator.currentCalculationData[i].id;
			if(typeof printCalculator.currentCalculationData[i].type !== 'undefined') delete printCalculator.currentCalculationData[i].type;

			
			td.innerHTML = i+1;
			
			//// console.log(printCalculator.currentCalculationData[i]);
			tr.appendChild(td);
			
			// тип нанесения
			var td =  td.cloneNode(false);
			td.innerHTML = printCalculator.currentCalculationData[i].print_details.print_type;
			td.setAttribute('index',i);
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			tr.appendChild(td);
			td.onclick = function(){ 
				// запускаем калькулятор для конкретного нанесения
				$("#calculatorDopUslugiBox").remove();
				// добавляем в передаваемые данные данные индекс массива printCalculator.currentCalculationData содержащего
				// данные конкретного(этого)нанесения
				printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id = this.getAttribute('index');
				// запускаем
				printCalculator.evoke_calculator(printCalculator.dataObj_toEvokeCalculator);
			}
			
			// место нанесения
			var td =  td.cloneNode(false);
			td.style.textDecoration = 'none';
			td.innerHTML = printCalculator.currentCalculationData[i].print_details.place_type;
			tr.appendChild(td);
			
			// сумма
			var td =  td.cloneNode(false);
			td.innerHTML = (Math.round(printCalculator.currentCalculationData[i].price_out*printCalculator.currentCalculationData[i].quantity * 100) / 100 ).toFixed(2) +' р.';
			tr.appendChild(td);
			
			var td =  td.cloneNode(false);
			td.innerHTML = 'Удалить нанесение';
			td.style.textDecoration = 'underline';
			td.style.cursor = 'pointer';
			td.setAttribute('usluga_id',data_AboutPrintsArr[i].dop_uslugi_id);
			td.onclick = function(){ 
			
				// отправляем запрос на удаление для текущего нанесения
				var url = OS_HOST+'?' + addOrReplaceGetOnURL('delete_prints_for_row='+printCalculator.dataObj_toEvokeCalculator.dop_data_row_id+'&usluga_id='+this.getAttribute('usluga_id'));
				printCalculator.send_ajax(url,callback);
			
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
		    printCalculator.evoke_calculator(printCalculator.dataObj_toEvokeCalculator);
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
			var url = OS_HOST+'?' + addOrReplaceGetOnURL('delete_prints_for_row='+printCalculator.dataObj_toEvokeCalculator.dop_data_row_id+'&all=true');
		    printCalculator.send_ajax(url,callback);
		
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
	    var url = OS_HOST+'?' + addOrReplaceGetOnURL('grab_calculator_data={"art_id":"'+printCalculator.dataObj_toEvokeCalculator.art_id+'","type":"'+printCalculator.dataObj_toEvokeCalculator.cell.parentNode.getAttribute('calc_btn')+'"}');
		printCalculator.send_ajax(url,callback);
		//alert(last_val);
		function callback(response_calculatorParamsData){
			// alert(response_calculatorParamsData);
			// return;
			if(typeof printCalculator.calculatorParamsObj !== 'undefined') delete printCalculator.calculatorParamsObj;
			
            printCalculator.calculatorParamsObj = JSON.parse(response_calculatorParamsData);
			// строим калькулятор
			printCalculator.build_print_calculator();
			// открываем окно с калькулятором
			
				
			$("#calculatorDialogBox").dialog({autoOpen: false, position:{ at: "top+25%", of: window } ,title: "Расчет нанесения логотипа",modal:true,width: 680,close: function() {this.remove();$("#calculatorDialogBox").remove();}});
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
		if(typeof printCalculator.currentCalculationData.print_details.place_id === 'undefined'){
			alert('Вы не выбрали место нанесения');
			return;
		}
		if(typeof printCalculator.currentCalculationData.print_details.print_id === 'undefined' || printCalculator.currentCalculationData.print_details.print_id == 0){
			alert('Вы не выбрали тип нанесения');
			return;
		}
		
		// предварительно удаляем предыдущие данные printCalculator.distributionData в если они есть 
		if(typeof printCalculator.distributionData !== 'undefined') delete printCalculator.distributionData;
		if(typeof printCalculator.distributionData === 'undefined') printCalculator.distributionData = {};
		if(typeof printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id] === 'undefined') printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id] = {};
		// если еще нет данных по данным типам мест и нанесения строим массив с данными
		if(typeof printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id] === 'undefined'){
			printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id] = {};
			
			// создаем таблицу с позициями к которым возможно применить данное нанесение
			var table = printCalculator.createDistributionDataTbl();
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
							details.calculationData = printCalculator.currentCalculationData;
							
							if(typeof details.calculationData.print_details.place_type === 'undefined') details.calculationData.print_details.place_type = printCalculator.currentCalculationData.print_details.place_type =  printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id].name;
							
							if(typeof details.calculationData.print_details.print_type === 'undefined') details.calculationData.print_details.print_type = printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id].prints[printCalculator.currentCalculationData.print_details.print_id];
							
							
							
							var url = OS_HOST+'?' + addOrReplaceGetOnURL('distribute_print=1&details='+JSON.stringify(details));
							printCalculator.send_ajax(url,callback);
						 
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
										str += 'строка '+printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].dop_data[response_obj.errors[i].id].glob_counter+', артикул. '+printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].dop_data[response_obj.errors[i].id].article+"\r";
										
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
								location.reload();
							}
					}  
					else{
						alert('Вы не выбрали позиции к которым надо применить нанесение');
					}
				}
				calculatorDataBox.appendChild(saveBtn);
				printCalculator.commonContainer.appendChild(calculatorDataBox);
				
			}
		}
	}
	,
    createDistributionDataTbl:function(){
	    //if(typeof printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id] === 'undefined') printCalculator.createDistributionDataTbl();
		
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
				artTd.width = '230';

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
						   
						   //alert(typeof dop_details_obj.allowed_prints[printCalculator.currentCalculationData.print_details.place_id]);
						   
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
							   if(typeof dop_details_obj.allowed_prints[printCalculator.currentCalculationData.print_details.place_id] ==='undefined') continue outerloop;
							   if(typeof dop_details_obj.allowed_prints[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id] ==='undefined') continue outerloop;
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
				//printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].push({"art_id":art_id,"tr":newTR});
				if(typeof printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].trs === 'undefined'){
					printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].trs = [];
				}
				if(typeof printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].dop_data === 'undefined'){
					printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].dop_data = {};
				}
				
				printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].trs.push({"art_id":art_id,"tr":newTR});
				printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].dop_data[pos_id]= {"glob_counter":glob_counter,"article":article};
			}
	    }
		
		//alert(printCalculator.currentCalculationData.print_details.place_id+' '+printCalculator.currentCalculationData.print_details.print_id);
		if(printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].trs && printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].trs.length > 0){
			var dataArr = printCalculator.distributionData[printCalculator.currentCalculationData.print_details.place_id][printCalculator.currentCalculationData.print_details.print_id].trs;
			 
			var table = document.createElement('TABLE');
			table.className="calculatorDistributionTbl";
			
		    for(var i =0; i < dataArr.length; i++){
				//console.log(dataArr[i]);
				table.appendChild(dataArr[i].tr);
			}
			return table;
		}
		//console.log('printCalculator.distributionData');
		//console.log(printCalculator.distributionData);
	}
	,
    build_print_calculator:function(){
		
		// если калькулятор был вызван для существующего нанесения пересохраняем данные для конкретного нанесения 
		// иначе готовим структуру для сохранения данных при создании калькулятора 
	    if(printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id){
			printCalculator.currentCalculationData =  printCalculator.currentCalculationData[printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id];
			printCalculator.currentCalculationData.dop_data_row_id = printCalculator.dataObj_toEvokeCalculator.dop_data_row_id;
			
			if(typeof printCalculator.currentCalculationData.dop_row_id !== 'undefined') delete printCalculator.currentCalculationData.dop_row_id;
			if(typeof printCalculator.currentCalculationData.price_in !== 'undefined') delete printCalculator.currentCalculationData.price_in;
			if(typeof printCalculator.currentCalculationData.price_out !== 'undefined') delete printCalculator.currentCalculationData.price_out;
		}
		else{
		    printCalculator.currentCalculationData =  {};	
			printCalculator.currentCalculationData.quantity = printCalculator.dataObj_toEvokeCalculator.quantity;
		    printCalculator.currentCalculationData.dop_data_row_id = printCalculator.dataObj_toEvokeCalculator.dop_data_row_id;
			printCalculator.currentCalculationData.print_details = {};
			printCalculator.currentCalculationData.print_details.dop_params = {};
		}
		
	    console.log('>>> build_print_calculator');
		console.log(' printCalculator.currentCalculationData',printCalculator.currentCalculationData);
		console.log('<<< build_print_calculator');
        console.log('>>>  printCalculator.calculatorParamsObj', printCalculator.calculatorParamsObj,'<<<  printCalculator.calculatorParamsObj');
		// строим интерфейс калькулятора
		
		//return;
		// console.log('>>> calculatorParamsObj',printCalculator.calculatorParamsObj,'<<< calculatorParamsObj');
		
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
		btn2.onclick = printCalculator.distributePrint;
		var infoField = document.createElement('DIV');
		infoField.className = "calculatorMenuCell";
		infoField.innerHTML = "Тираж "+printCalculator.currentCalculationData.quantity+' шт.';
		
		
		menuContainer.appendChild(btn1);
		menuContainer.appendChild(btn2);
		menuContainer.appendChild(infoField);
		dialogBox.appendChild(menuContainer);
		
		var clear_div = document.createElement('DIV');
		clear_div.className = "clear_div";
		dialogBox.appendChild(clear_div.cloneNode(true));
		
        // общий контейнер для всех закладок(разделов) калькулятора
		printCalculator.commonContainer = document.createElement('DIV');


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
		
		//// console.log(printCalculator.calculatorParamsObj.places);
		for(var id in printCalculator.calculatorParamsObj.places){
			// если это заново запускаемый калькулятор сохраняем id первого места нанесения 
			if(typeof printCalculator.currentCalculationData.print_details.place_id === 'undefined') printCalculator.currentCalculationData.print_details.place_id = id;
           
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(printCalculator.calculatorParamsObj.places[id].name));
            printPlaceSelect.appendChild(option);
			
			if(printCalculator.currentCalculationData.print_details.place_id==id) option.setAttribute("selected",true);
			//// console.log(i + printCalculator.dataObj_toEvokeCalculator.places[i].name);
		}
		//currPlace_id = 1;
		printPlaceSelect.onchange = function(){
			if(document.getElementById("printCalculatorBlockA")){
			    document.getElementById("printCalculatorBlockA").parentNode.removeChild(document.getElementById("printCalculatorBlockA"));
			}
			if(document.getElementById("printCalculatorItogDisplay")) document.getElementById("printCalculatorItogDisplay").innerHTML = '';
			// alert('printPlaceSelect');
			//
			printCalculator.currentCalculationData.print_details = {};
			printCalculator.currentCalculationData.print_details.dop_params = {};
			// определяем id места нанесения
			printCalculator.currentCalculationData.print_details.place_id = parseInt(this.options[this.selectedIndex].value);
			//alert(place_id);
			// создаем новый block_A
			 var block_A = printCalculator.buildBlockA();
			
			document.getElementById("calculatorBodyBox").appendChild(block_A);
			if(printCalculator.makeProcessingFlag) printCalculator.makeProcessing();
		}
		
		var elementsBox = document.createElement('DIV');
		elementsBox.className = "calculatorElementsBox";

		elementsBox.appendChild(printPlaceSelect);
		printPlaceSelectDiv.appendChild(elementsBox);
		printPlaceSelectDiv.appendChild(clear_div.cloneNode(true));
		mainCalculatorBox.appendChild(printPlaceSelectDiv);
		
		
		// создаем блок block_A который будет содеражать в себе select выбора типа нанесения
		// и блок block_B содержащий в себе все остальные элементы интерфейса
		if(printCalculator.currentCalculationData.print_details.place_id){
			// если остался print_id от предыдущего запуска калькулятора удаляем его
	        var block_A = printCalculator.buildBlockA();
		    box.appendChild(block_A);
		}
		else{
			box.appendChild(document.createTextNode("Ошибка: не определен ID места нанесения "));
			
		}
		
		mainCalculatorBox.appendChild(box);
	
		printCalculator.commonContainer.appendChild(mainCalculatorBox);
		dialogBox.appendChild(printCalculator.commonContainer);
		document.body.appendChild(dialogBox);
		
		
		if(printCalculator.makeProcessingFlag) printCalculator.makeProcessing();
		
		// help button
		// box.appendChild(help.btn('kp.sendLetter.window'));
		
	}
	,
	buildBlockA:function(){
		
		// метод строит блок block_A и взависимости от ситуации
		// или ( строит и вставляет в него block_B ) или ( не делает этого )
		// block_A содержит селект с выбором типа нанесения
		
		var block_A = document.createElement('DIV');
		block_A.id = 'printCalculatorBlockA';
		block_A.className = 'printCalculatorBlockA';

		// вызваем метод строящий  select для типов нанеснения
		// передаем ему id первого места нанесения из printPlaceSelect
		// он возвращает select и id типа нанесения первого в списке select
		var printTypesSelect = printCalculator.build_print_types_select();
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
		
		
		// alert(printCalculator.currentCalculationData.print_details.print_id);
		// если мы имеем конкретное типа нанесения (тоесть оно не равно 0) тогда строим калькулятор дальше
		// вызываем метод строящий блок В калькулятора и вставляем его в тело калькулятора
		if(printCalculator.currentCalculationData.print_details.print_id != 0){	
		    var block_B = printCalculator.buildBlockB();
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
		for(var id in printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id].prints){
			// если это заново запускаемый калькулятор сохраняем id первого  нанесения 
			if(typeof printCalculator.currentCalculationData.print_details.print_id === 'undefined') printCalculator.currentCalculationData.print_details.print_id = id;
			counter++;
			var option = document.createElement('OPTION');
            option.setAttribute("value",id);
            option.appendChild(document.createTextNode(printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id].prints[id]));
            printTypesSelect.appendChild(option);
			//// console.log(i + data_obj.places[i].name);
			if(typeof printCalculator.currentCalculationData.print_details.print_id !== 'undefined'){
			    if(printCalculator.currentCalculationData.print_details.print_id==id) option.setAttribute("selected",true);
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
			if(typeof printCalculator.dataObj_toEvokeCalculator.currentCalculationData_id === 'undefined'){
				option.setAttribute("selected",true);
				printCalculator.currentCalculationData.print_details.print_id = 0;
			}
		}

		
		// обработчик события onchange
		printTypesSelect.onchange = function(){
			
			if(document.getElementById("printCalculatorBlockB"))document.getElementById("printCalculatorBlockB").parentNode.removeChild(document.getElementById("printCalculatorBlockB"));
			if(document.getElementById("printCalculatorItogDisplay")) document.getElementById("printCalculatorItogDisplay").innerHTML = '';
			// alert('printTypesSelect');
			var place_id  = printCalculator.currentCalculationData.print_details.place_id;
			printCalculator.currentCalculationData.print_details = {};
			printCalculator.currentCalculationData.print_details.dop_params = {};
			printCalculator.currentCalculationData.print_details.place_id = place_id;
			printCalculator.currentCalculationData.print_details.print_id = this.options[this.selectedIndex].value;
			
			var block_B = printCalculator.buildBlockB();
			
			document.getElementById("printCalculatorBlockA").appendChild(block_B);
			// метод осуществляющий итоговый расчет 
		    // и помещающий итоговые данные в сторку ИТОГО
		    printCalculator.makeProcessing();
			
		}
		//alert(printCalculator.currentCalculationData.print_details.print_id);
		if(typeof printCalculator.currentCalculationData.print_details.print_id === 'undefined'){
		    var printTypesSelect = document.createElement('DIV');
			printTypesSelect.appendChild(document.createTextNode("Ошибка: не определен ID типа нанесения "));
			
		}
		
		return printTypesSelect;
	}
	,
	buildBlockB:function(){
		
		var blockB = document.createElement('DIV');
		blockB.id = 'printCalculatorBlockB';
		blockB.className = 'printCalculatorBlockB';
		var br = document.createElement('BR');
		// выбираем данные выбранного нанесения и выводим их в калькулятор
		var currRrintParams = printCalculator.setCurrPrintParams();

		blockB.appendChild(br.cloneNode(true));
		blockB.appendChild(currRrintParams);
	
		
		// если был построен blockB то по окончании вывода калькулятора в поток можно запускать 
		// printCalculator.makeProcessing() и подгружать Итоговые суммы
		printCalculator.makeProcessingFlag = true; 
		
		return blockB;
	}
	,
	setCurrPrintParams:function(){
		
        // console.log('>>> setCurrPrintParams');
		// console.log(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id]);
		// console.log('<<< setCurrPrintParams');
		
		var clear_div = document.createElement('DIV');
	    clear_div.className = "clear_div";
		
		var printParamsBox = document.createElement('DIV');
		
		// определяем переменную содержащую массив данных относящихся к текущему типу нанесения
		var CurrPrintTypeData = printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id];
		
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
			YPriceParamCMYK.onblur =  printCalculator.onblurCMYK;
			
			var YPriceParamSelect = document.createElement('SELECT');
			var YPriceParamSelectWrap =  document.createElement('DIV');
			// метод onchangeYPriceParamSelect пикрепляется к Селекту здесь и пикрепляется к добавляемым селектам ниже в специальном цикле 
			YPriceParamSelect.onchange = function(){ printCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
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
				if(YPriceParamDiv.getElementsByTagName('SELECT').length < printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1){
					var YPriceParamSelectClone = YPriceParamSelect.cloneNode(true);
	
					// навешиваем обработчик события селекту, потому что при YPriceParamSelect.cloneNode(true); он слетает
					YPriceParamSelectClone.onchange = function(){ printCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
					
					var YPriceParamSelectWrapClone = YPriceParamSelectWrap.cloneNode();
					YPriceParamSelectWrapClone.appendChild(YPriceParamSelectClone);
					YPriceParamDiv.appendChild(YPriceParamSelectWrapClone);
					
					var YPriceParamCMYKсlone = YPriceParamCMYK.cloneNode(true);
					YPriceParamCMYKсlone.innerHTML = '';
					YPriceParamCMYKсlone.className += ' hidden';	
					YPriceParamCMYKсlone.onblur = printCalculator.onblurCMYK;
					YPriceParamCMYKdiv.appendChild(YPriceParamCMYKсlone);		
				}
				if(YPriceParamDiv.getElementsByTagName('SELECT').length >= printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1){
					// скрываем ссылку добавления если она есть
				   this.className += ' hidden';	
				}
			}
			
			// добавляем один или несколько селектов в калькулятор в зависимости от того был он вызван 
			// для уже существующего расчета или для нового расчета 
			if(typeof printCalculator.currentCalculationData.print_details.dop_params.YPriceParam !== 'undefined'){
				for(var i = 0;i < printCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length; i++){ 
				     // Select
				     var YPriceParamSelectClone = YPriceParamSelect.cloneNode(true);
					 var YPriceParamSelectWrapClone = YPriceParamSelectWrap.cloneNode(true);
					 var optionsArr = YPriceParamSelectClone.getElementsByTagName("OPTION");
					 //var optionsArr = YPriceParamSelectClone.options;
					 for(var j in optionsArr){
						if(optionsArr[j] && optionsArr[j].nodeType ==1 && optionsArr[j].nodeName == 'OPTION'){ 
							if(optionsArr[j].getAttribute("item_id") && optionsArr[j].getAttribute("item_id") == printCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].id) optionsArr[j].setAttribute("selected",true);
						}
					 }
					 
					 //YPriceParamSelectClone.options[parseInt(printCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].id)].setAttribute("selected",true);
					 // Select
				     YPriceParamSelectWrapClone.appendChild(YPriceParamSelectClone);
					 YPriceParamDiv.appendChild(YPriceParamSelectWrapClone);
					 // CMYK
					 var YPriceParamCMYKсlone = YPriceParamCMYK.cloneNode(true);
					 if(printCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].cmyk) YPriceParamCMYKсlone.innerHTML = Base64.decode(printCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].cmyk);
					 YPriceParamCMYKсlone.onblur = printCalculator.onblurCMYK;
				     YPriceParamCMYKdiv.appendChild(YPriceParamCMYKсlone);
				}
				var selectsArr = YPriceParamDiv.getElementsByTagName("SELECT");
				// навешиваем обработчики события каждому селекту, потому что при YPriceParamSelect.cloneNode(true); они слетают
				for(var i in selectsArr){
					selectsArr[i].onchange = function(){ printCalculator.onchangeYPriceParamSelect(YPriceParamDiv,YPriceParamCMYKdiv); }
				}
			}
			else{
				// Select
				YPriceParamSelectWrap.appendChild(YPriceParamSelect);
				YPriceParamDiv.appendChild(YPriceParamSelectWrap);
				// CMYK
				YPriceParamCMYKdiv.appendChild(YPriceParamCMYK);
				
				printCalculator.currentCalculationData.print_details.dop_params.YPriceParam = [];
				printCalculator.currentCalculationData.print_details.dop_params.YPriceParam.push({'id':0,'coeff':1});
			}
			
			var title = document.createElement('DIV');
			title.className = "calculatorTitle";
			title.innerHTML = 'Цвет: ';
			
			
			var elementsBox = document.createElement('DIV');
		    elementsBox.className = "calculatorElementsBox";
		 
			
			elementsBox.appendChild(YPriceParamDiv);
			// если количество селектов меньше рядов в таблице прайса то добавляем сслыку добавления новых селектов
			if(YPriceParamDiv.getElementsByTagName('SELECT').length < printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1) elementsBox.appendChild(addYPriceParamLink);
			
			YPriceParamDivContainer.appendChild(title);
			YPriceParamDivContainer.appendChild(elementsBox);
			YPriceParamDivContainer.appendChild(YPriceParamCMYKdiv);
			YPriceParamDivContainer.appendChild(clear_div.cloneNode(true));
			printParamsBox.appendChild(YPriceParamDivContainer);
			
		}
		
		if(CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id]){
			// собираем данные для расчета
			// площади нанесения
			// if(typeof printCalculator.dataForProcessing['coefficients'] === 'undefined') printCalculator.dataForProcessing['coefficients']={};
			// if(typeof printCalculator.dataForProcessing['dop_params'] === 'undefined') printCalculator.dataForProcessing['dop_params']={};
			// printCalculator.dataForProcessing['coefficients']['sizes'] = [];
			// printCalculator.dataForProcessing['dop_params']['sizes'] = [];
			
			var printSizesSelect = document.createElement('SELECT');
			var printSizesSelectDiv  = document.createElement('DIV');
			printSizesSelectDiv.className = "printSizesSelectDiv";
			
			printSizesSelect.onchange = function(){
				printCalculator.currentCalculationData.print_details.dop_params.sizes[0] = {'id':this.options[this.selectedIndex].getAttribute('item_id'),'coeff':this.options[this.selectedIndex].value}
				
				printCalculator.makeProcessing();
			}
			for(var id in CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id]){
				if(typeof printCalculator.currentCalculationData.print_details.dop_params.sizes === 'undefined'){
					printCalculator.currentCalculationData.print_details.dop_params.sizes = [];
					printCalculator.currentCalculationData.print_details.dop_params.sizes[0] = {'id':CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id][id]['item_id'],'coeff':CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id][id]['percentage']};
				}
				
				
				var option = document.createElement('OPTION');


				option.setAttribute("value",CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id][id]['percentage']);
				// id значения (размер нанесения) который будет сохранен в базу данных и по нему будет отстроен калькулятор 
				// в случае вызова по кокретному нанесению
				option.setAttribute("item_id",CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id][id]['item_id']);
				option.appendChild(document.createTextNode(CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id][id]['size']));
				printSizesSelect.appendChild(option);
				
				if(printCalculator.currentCalculationData.print_details.dop_params.sizes && printCalculator.currentCalculationData.print_details.dop_params.sizes[0].id == CurrPrintTypeData['sizes'][printCalculator.currentCalculationData.print_details.place_id][id]['item_id']){
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
			printCalculator.price_tblIn = printCalculator.build_priceTbl(CurrPrintTypeData['priceIn_tbl'],'in');
		    // printParamsBox.appendChild(printCalculator.price_tblIn);
		}
		else alert('отсутствует прайс входящих цен');
		// исходящяя цена 
		if(CurrPrintTypeData['priceOut_tbl']){
			printCalculator.price_tblOut = printCalculator.build_priceTbl(CurrPrintTypeData['priceOut_tbl'],'out');
		    // printParamsBox.appendChild(printCalculator.price_tblOut);
		}
		else alert('отсутствует прайс исходящих цен');
		
		console.log('>>> после определения Xindex');
		console.log(printCalculator.currentCalculationData);
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
						// printParamsBox.appendChild(printCalculator.makeCommonSelect('coeffs',target,type,data));
						dopParamsArr.push(printCalculator.makeCommonSelect('coeffs',target,type,data));
					}
				    else{// если применяется по умолчанию
					    if(typeof printCalculator.currentCalculationData.print_details.dop_params.coeffs === 'undefined')
						          printCalculator.currentCalculationData.print_details.dop_params.coeffs = {};
					    if(typeof printCalculator.currentCalculationData.print_details.dop_params.coeffs[target] === 'undefined')
								printCalculator.currentCalculationData.print_details.dop_params.coeffs[target] = {};
						if(typeof printCalculator.currentCalculationData.print_details.dop_params.coeffs[target][type] === 'undefined'){
								printCalculator.currentCalculationData.print_details.dop_params.coeffs[target][type] = [];
								for(var index in data.data){
									printCalculator.currentCalculationData.print_details.dop_params.coeffs[target][type].push({"value": parseFloat(data.data[index].coeff),"id": data.data[index].item_id});
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
						//printParamsBox.appendChild(printCalculator.makeCommonSelect('additions',target,type,data));
						dopParamsArr.push(printCalculator.makeCommonSelect('additions',target,type,data));
					}
					else{// если применяется по умолчанию
					    if(typeof printCalculator.currentCalculationData.print_details.dop_params.additions === 'undefined')
						          printCalculator.currentCalculationData.print_details.dop_params.additions = {};
					    if(typeof printCalculator.currentCalculationData.print_details.dop_params.additions[target] === 'undefined')
								printCalculator.currentCalculationData.print_details.dop_params.additions[target] = {};
						if(typeof printCalculator.currentCalculationData.print_details.dop_params.additions[target][type] === 'undefined')
								printCalculator.currentCalculationData.print_details.dop_params.additions[target][type] = [];
						
						// console.log(printCalculator.currentCalculationData.print_details.dop_params.additions);
						for(var index in data.data){
							printCalculator.currentCalculationData.print_details.dop_params.additions[target][type].push({"value": parseFloat(data.data[index].value),"id": data.data[index].item_id});
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
		if(printCalculator.currentCalculationData.tz){
			var comment = Base64.decode(printCalculator.currentCalculationData.tz);
			textarea.value= comment.replace(/<br \/>/g,"\r");
		}
		textarea.onchange = function(){  printCalculator.currentCalculationData.print_details.comment = this.value; }
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
				if(typeof printCalculator.currentCalculationData.print_details.dop_params[glob_type] === 'undefined')
					  printCalculator.currentCalculationData.print_details.dop_params[glob_type] = {};
			    if(typeof printCalculator.currentCalculationData.print_details.dop_params[glob_type][target] === 'undefined')
					printCalculator.currentCalculationData.print_details.dop_params[glob_type][target] = {};
			    if(typeof printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type] === 'undefined')
					printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type] = [];
				
				var obj = {"value": parseFloat(this.options[this.selectedIndex].value),"id": this.options[this.selectedIndex].getAttribute('item_id')};
                if(this.options[this.selectedIndex].getAttribute('multi')){
					var nextTr = printCalculator.nextTag(this.parentNode);
					obj.multi = parseInt(nextTr.getElementsByTagName('INPUT')[0].value);
				}
				printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type].push(obj);
				
				printCalculator.makeProcessing();
				
			}
			else{
				if(printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type]){

					delete printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type];
					printCalculator.makeProcessing();
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
			
			if(printCalculator.currentCalculationData.print_details.dop_params[glob_type] && printCalculator.currentCalculationData.print_details.dop_params[glob_type][target] && printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type]){
			     if(printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].id==data.data[index].item_id) option.setAttribute("selected",true);
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
			 if(printCalculator.currentCalculationData.print_details.dop_params[glob_type] && printCalculator.currentCalculationData.print_details.dop_params[glob_type][target] && printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type]){
			     if(printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi) input_field.value = printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi;
			}
			 
			 
			 input_field.onkeyup = function(){
				 if(typeof printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi !== 'undefined'){
					printCalculator.currentCalculationData.print_details.dop_params[glob_type][target][type][0].multi = this.value;
				    printCalculator.makeProcessing();
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
		
		if(typeof printCalculator.makeProcessingFlag !== 'undefined') delete printCalculator.makeProcessingFlag;
		
		// обращаемся к ряду таблицы цен, 
		// по значению параметра printCalculator.currentCalculationData.print_details.priceOut_tblYindex
		// и выбираем нужную ячейку 
		// по значению параметра printCalculator.currentCalculationData.print_details.priceOut_tblXindex
		// console.log('>>> printCalculator.currentCalculationData <<<');
		// console.log( printCalculator.currentCalculationData);
		// console.log('>>><<<');
		//alert(printCalculator.currentCalculationData.print_details.priceIn_tblXindex+' ++ '+printCalculator.currentCalculationData.print_details.priceOut_tblXindex);
		// снимаем значение price с таблицы прайса
		
		console.log('>>> priceOut_tbl <<<');
		console.log( printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0] );
		//alert(printCalculator.currentCalculationData.print_details.priceOut_tblXindex);
		var priceOut_tblYindex = (typeof printCalculator.currentCalculationData.print_details.dop_params.YPriceParam !== 'undefined')? printCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length:1;
		var priceIn_tblYindex = (typeof printCalculator.currentCalculationData.print_details.dop_params.YPriceParam !== 'undefined')? printCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length:1;
		
		var price_out =printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0][priceOut_tblYindex][printCalculator.currentCalculationData.print_details.priceOut_tblXindex];
		var price_in =printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceIn_tbl[0][priceIn_tblYindex][printCalculator.currentCalculationData.print_details.priceIn_tblXindex];
		// alert('out '+price_out+' - in '+price_in);
		// если полученная цена оказалась равна 0 то значит стоимость не  указана
	    if(parseFloat(price_out) == 0 || parseFloat(price_in) == 0){
			
			var sourse_tbls = ['priceIn_tbl','priceOut_tbl'];
			for(index in sourse_tbls){
			    var sourse_tblXindex = sourse_tbls[index]+'Xindex';
				// alert(printCalculator.currentCalculationData.print_details.print_id); 
				// alert(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id][sourse_tbls[index]][0][0]['maxXIndex']);
				// alert(printCalculator.currentCalculationData.print_details[sourse_tblXindex]);
				// если это последние ряды прайс значит это лимит
				if(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id][sourse_tbls[index]][0][0]['maxXIndex'] == printCalculator.currentCalculationData.print_details[sourse_tblXindex]){
	
					var limimt =parseInt(printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id][sourse_tbls[index]][0][0][printCalculator.currentCalculationData.print_details[sourse_tblXindex]]);
					
					printCalculator.cancelSaveReslut = true;
					var caution = 'Цена не может быть расчитана, достигнут лимит тиража в '+limimt+' шт.\rтребуется индивидуальный расчет';
					break;
				}
				else{//иначе это индивидуальный расчет cancelCalculator
				    //if(typeof printCalculator.cancelSaveReslut === 'undefined')
					printCalculator.cancelSaveReslut = true;
					var caution = 'Рассчет с данными параметрами не может быть произведен \rтребуется индивидуальный расчет';
				}
			}
		}
					
		if(printCalculator.currentCalculationData.print_details.lackOfQuantOutPrice){
			price_out = price_out*printCalculator.currentCalculationData.print_details.minQuantOutPrice/printCalculator.currentCalculationData.quantity;
		}
		if(printCalculator.currentCalculationData.print_details.lackOfQuantOutPrice){
			price_in = price_in*printCalculator.currentCalculationData.print_details.minQuantInPrice/printCalculator.currentCalculationData.quantity;
		}
			
		//console.log('>>> YPriceParam.length  --   priceIn_tblXindex  priceOut_tblXindex  --  price_in  price_out <<<');
		//console.log( printCalculator.currentCalculationData.print_details.dop_params.YPriceParam.length + ' -- '+printCalculator.currentCalculationData.print_details.priceIn_tblXindex + ' '+ printCalculator.currentCalculationData.print_details.priceOut_tblXindex+' -- '+ price_in + ' '+ price_out );
		
		// КОЭФФИЦИЕНТЫ НА ПРАЙС
		// КОЭФФИЦИЕНТЫ НА ИТОГОВУЮ СУММУ
		// перебираем printCalculator.currentCalculationData.print_details.
		// в нем содержатся коэффициенты по Y параметру таблицы прайса и по размеру нанесения
		var price_coeff = summ_coeff = 1;
		var price_coeff_list  = summ_coefficient_list = '';
		for(glob_type in printCalculator.currentCalculationData.print_details.dop_params){
			if(glob_type=='YPriceParam' || glob_type=='sizes'){
				var set = printCalculator.currentCalculationData.print_details.dop_params[glob_type];
				for(var i = 0;i < set.length;i++){ 
				    // подстраховка
				    if(set[i].coeff == 0) set[i].coeff = 1;
					price_coeff *= set[i].coeff;
					price_coeff_list += glob_type +' - '+ set[i].coeff+', ';
				}
			}
			if(glob_type=='coeffs'){
				var data = printCalculator.currentCalculationData.print_details.dop_params[glob_type];
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
		
		
		// перебираем printCalculator.currentCalculationData.print_details.
	
		
	
	
	    // НАДБАВКИ НА ИТОГОВУЮ СУММУ
		// перебираем printCalculator.currentCalculationData.print_details.
		var price_addition = summ_addition = 0;
		var price_additions_list = summ_additions_list = '';
		for(glob_type in printCalculator.currentCalculationData.print_details.dop_params){
			if(glob_type=='additions'){
				var data = printCalculator.currentCalculationData.print_details.dop_params[glob_type];
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
		console.log(printCalculator.currentCalculationData);

		
		
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!  
		// CXEMA  - total_price = ((((price*price_coeff)+price_addition)*quantity)*sum_coeff)+sum_addition
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 
		
		var total_price_out = ((((price_out*price_coeff)+price_addition)*printCalculator.currentCalculationData.quantity)*summ_coeff)+summ_addition;
		var total_price_in  = ((((price_in*price_coeff)+price_addition)*printCalculator.currentCalculationData.quantity)*summ_coeff)+summ_addition;
		
		total_price_out = Math.round(total_price_out * 100) / 100 ;
		total_price_in = Math.round(total_price_in * 100) / 100 ;
		
	    printCalculator.currentCalculationData.price_out = Math.round(total_price_out/printCalculator.currentCalculationData.quantity * 100) / 100;
		printCalculator.currentCalculationData.price_in  = Math.round(total_price_in/printCalculator.currentCalculationData.quantity * 100) / 100;
		
		total_price_out = Math.round((printCalculator.currentCalculationData.price_out*printCalculator.currentCalculationData.quantity) * 100) / 100 ;
		total_price_in = Math.round((printCalculator.currentCalculationData.price_in*printCalculator.currentCalculationData.quantity) * 100) / 100 ;
		
		var total_str  = '';
		var total_details  = '';
	    if(typeof printCalculator.cancelSaveReslut === 'undefined'){
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
			tdClone.innerHTML = ((printCalculator.currentCalculationData.price_in).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
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
			tdClone.innerHTML = ((printCalculator.currentCalculationData.price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			tdClone = td.cloneNode(true);
			tdClone.innerHTML = ((total_price_out).toFixed(2)).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1 ")+'р';
			TRclone.appendChild(tdClone);
			total_tbl.appendChild(TRclone);
	
		}
		else{
			alert(caution);
	
			delete printCalculator.cancelSaveReslut;
			if(typeof document.getElementById("calculatorsaveResultBtn") !== 'undefined')  document.getElementById("calculatorsaveResultBtn").style.display = 'none';
			
		}

		 //console.log('>>> total_str <<<');
		// console.log('in  - '+(printCalculator.currentCalculationData.price_in).toFixed(2)+' '+(total_price_in).toFixed(2)+' out - '+(printCalculator.currentCalculationData.price_out).toFixed(2)+' '+(total_price_out).toFixed(2));   
		printCalculator.total_details = document.createElement('div');	
		printCalculator.total_details.className ="calculatorTotalDetails";

		var total_details = '<span style="color:#FF6633;">коэффициэнты прайса:</span> '+price_coeff_list+'<br>';
		total_details += '<span style="color:#FF6633;">надбавки прайса:</span> '+price_additions_list+'<br>';
		total_details += '<span style="color:#FF6633;">коэффициэнты суммы:</span> '+summ_coefficient_list+'<br>';
		total_details += '<span style="color:#FF6633;">надбавки суммы:</span> '+summ_additions_list+'<br>';
        printCalculator.total_details.innerHTML = total_details;
		if(document.getElementById("showProcessingDetailsBoxTotalDetails")){
			document.getElementById("showProcessingDetailsBoxTotalDetails").innerHTML = '';
			document.getElementById("showProcessingDetailsBoxTotalDetails").appendChild(printCalculator.total_details);
		}
			
		if(total_tbl){
			
			// дисплей итоговых подсчетов
			if(document.getElementById("printCalculatorItogDisplay")){
				printCalculatorItogDisplay = document.getElementById("printCalculatorItogDisplay");
				printCalculatorItogDisplay.innerHTML = '';
			}
			else{
				var printCalculatorItogDisplay = document.createElement('DIV');
			    printCalculatorItogDisplay.id = 'printCalculatorItogDisplay';
			}
			var dopParametrsTitle = document.createElement('DIV');
			dopParametrsTitle.className = "dopParametrsTitle";
			dopParametrsTitle.innerHTML = 'Цена';
			printCalculatorItogDisplay.appendChild(dopParametrsTitle);
		
			printCalculatorItogDisplay.appendChild(total_tbl);
			
			var BtnsDiv = document.createElement('DIV');
			BtnsDiv.className = 'BtnsDiv';
			
			var showProcDetBtn = document.createElement('DIV');
			showProcDetBtn.className = 'showProcessingDetailsBtn';
			showProcDetBtn.innerHTML = 'Включить вкладку прайс';
			showProcDetBtn.onclick =  printCalculator.showProcessingDetails;
			
			BtnsDiv.appendChild(showProcDetBtn);
			
			var saveBtn = document.createElement('DIV');
			saveBtn.className = 'saveBtn';
			saveBtn.innerHTML = 'Сохранить расчет';
			saveBtn.onclick =  printCalculator.saveCalculatorResult;
			
			BtnsDiv.appendChild(saveBtn);
			printCalculatorItogDisplay.appendChild(BtnsDiv);
	
			
			document.getElementById("mainCalculatorBox").appendChild(printCalculatorItogDisplay);
		}
		
		
		
		
	}
	,
	showProcessingDetails:function(){

		var box = document.createElement('DIV');
		box.id = "showProcessingDetailsBox";
		//box.style.width = '300px';
		box.style.display = "none";
		box.appendChild(printCalculator.price_tblIn);
		box.appendChild(printCalculator.price_tblOut);
		var total_details = document.createElement('DIV');
		total_details.id = "showProcessingDetailsBoxTotalDetails";
		
		total_details.appendChild(printCalculator.total_details);
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
		// console.log(printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id]); 
		// корректируем объект с информацией удаляем не нужные для сохранение данные, добавляем нужные
		printCalculator.currentCalculationData.print_details.place_type =  printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id].name;
		printCalculator.currentCalculationData.print_details.print_type =  printCalculator.calculatorParamsObj.places[printCalculator.currentCalculationData.print_details.place_id].prints[printCalculator.currentCalculationData.print_details.print_id];
		
		
		if(typeof printCalculator.currentCalculationData.glob_type !== 'undefined') delete printCalculator.currentCalculationData.glob_type;
		if(typeof printCalculator.currentCalculationData.dop_row_id !== 'undefined') delete printCalculator.currentCalculationData.dop_row_id;

		if(typeof printCalculator.currentCalculationData.print_details.priceOut_tblXindex !== 'undefined') delete printCalculator.currentCalculationData.print_details.priceOut_tblXindex;
		if(typeof printCalculator.currentCalculationData.print_details.priceIn_tblXindex !== 'undefined') delete printCalculator.currentCalculationData.print_details.priceIn_tblXindex;
		if(typeof printCalculator.currentCalculationData.print_details.priceOut_tbl !== 'undefined') delete printCalculator.currentCalculationData.print_details.priceOut_tbl;
		if(typeof printCalculator.currentCalculationData.print_details.priceIn_tbl !== 'undefined') delete printCalculator.currentCalculationData.print_details.priceIn_tbl;
		if(typeof printCalculator.price_tblIn !== 'undefined') delete printCalculator.price_tblIn;
		if(typeof printCalculator.price_tblOut !== 'undefined') delete printCalculator.price_tblOut;
		if(typeof printCalculator.total_details !== 'undefined') delete printCalculator.total_details;
		
		
		if(typeof printCalculator.dataObj_toEvokeCalculator !== 'undefined') delete printCalculator.dataObj_toEvokeCalculator;
		
		// console.log('>>> saveCalculatorResult --');
		// console.log(printCalculator.currentCalculationData);
        // console.log('<<< saveCalculatorResult --');
		
		//return;
		// формируем url для AJAX запроса
		var url = OS_HOST+'?' + addOrReplaceGetOnURL('save_calculator_result=1&details='+JSON.stringify(printCalculator.currentCalculationData));
		printCalculator.send_ajax(url,callback);
		//alert(url);//
		$("#calculatorsaveResultBtn").remove();
		
		
		function callback(response){ 
		    //  alert(response);
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
		printCalculator.currentCalculationData.print_details.dop_params.YPriceParam[i].cmyk = cur_cell.innerHTML;
	}
	,
	onchangeYPriceParamSelect:function(YPriceParamDiv,YPriceParamCMYKdiv){
		// здесь нам надо пройти по всем селектам в YPriceParamDiv и собрать данные о выбранных полях
		// чтобы сохранить их в dataForProcessing а затем запустить printCalculator.makeProcessing();
		
		// затираем данные по цветам которые были до этого
		if(printCalculator.currentCalculationData.print_details.dop_params.YPriceParam) printCalculator.currentCalculationData.print_details.dop_params.YPriceParam = [];
		
		var selectsArr = YPriceParamDiv.getElementsByTagName("SELECT");
		var CMYKsArr = YPriceParamCMYKdiv.getElementsByTagName("DIV");
		
		//alert(selectsArr.length);
		for( var i = 0; i < selectsArr.length; i++){
			var value = selectsArr[i].options[selectsArr[i].selectedIndex].value;
			var item_id = selectsArr[i].options[selectsArr[i].selectedIndex].getAttribute('item_id');
			// если value != 0(0 равно вспомогательное значение "Выбрать"), значит выбор в селекте сделан 
			// добавляем его в dataForProcessing
			if(value != 0){
				printCalculator.currentCalculationData.print_details.dop_params.YPriceParam.push({'id':item_id,'coeff':value}); 
				CMYKsArr[i].className = 'YPriceParamCMYK';
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
		if(YPriceParamDiv.getElementsByTagName('SELECT').length <printCalculator.calculatorParamsObj.print_types[printCalculator.currentCalculationData.print_details.print_id].priceOut_tbl[0].length-1){
		   document.getElementById('calculatoraddYPriceParamLink').className = '';
		}
		
		// alert(printCalculator.currentCalculationData.print_details.priceOut_tblYindex);
		printCalculator.makeProcessing();
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
							if(printCalculator.currentCalculationData.quantity < parseFloat(tbl[row][1])){
								var priceOut_tblXindex = 1;
								printCalculator.currentCalculationData.print_details.lackOfQuantOutPrice = true;
								printCalculator.currentCalculationData.print_details.minQuantOutPrice = parseInt(tbl[row][1]);
							}
						    else if(printCalculator.currentCalculationData.quantity >= parseFloat(tbl[row][counter]) && parseFloat(tbl[row][counter])>0){
								// alert(tbl[row][counter]+' '+counter);
								var priceOut_tblXindex = counter;
								if(typeof printCalculator.currentCalculationData.print_details.lackOfQuantOutPrice !== 'undefined') delete printCalculator.currentCalculationData.print_details.lackOfQuantOutPrice;
								if(typeof printCalculator.currentCalculationData.print_details.minQuantOutPrice !== 'undefined') delete printCalculator.currentCalculationData.print_details.minQuantOutPrice;
							}
						}
						if(type=='in'){
							if(printCalculator.currentCalculationData.quantity < parseFloat(tbl[row][1])){
								var priceIn_tblXindex = 1;
								printCalculator.currentCalculationData.print_details.lackOfQuantInPrice = true;
								printCalculator.currentCalculationData.print_details.minQuantInPrice = parseInt(tbl[row][1]);
							}
						    else if(printCalculator.currentCalculationData.quantity >= parseFloat(tbl[row][counter]) && parseFloat(tbl[row][counter])>0){
								// alert(tbl[row][counter]+' '+counter);
								var priceIn_tblXindex = counter;
								if(typeof printCalculator.currentCalculationData.print_details.lackOfQuantInPrice !== 'undefined') delete printCalculator.currentCalculationData.print_details.lackOfQuantInPrice;
								if(typeof printCalculator.currentCalculationData.print_details.minQuantInPrice !== 'undefined') delete printCalculator.currentCalculationData.print_details.minQuantInPrice;
							}
						}
						
						//// console.log(parseInt(tbl[row][counter])+' '+printCalculator.currentCalculationData.quantity);
					}
					
					
				}
				
				tbl_html.appendChild(tr);
				
			}
			// собираем данные для расчета
			// для определения текущей цены
			if(type=='out'){
				if(typeof printCalculator.currentCalculationData.print_details.priceOut_tblXindex=== 'undefined'){ 
				    // alert('out>'+priceOut_tblXindex);
					printCalculator.currentCalculationData.print_details.priceOut_tblXindex = priceOut_tblXindex;
					// alert(printCalculator.currentCalculationData.print_details.priceOut_tblXindex);
				}
			}
			else if(type=='in'){
			    if(typeof printCalculator.currentCalculationData.print_details.priceIn_tblXindex=== 'undefined'){ 
				     // alert('in>'+priceIn_tblXindex);
					printCalculator.currentCalculationData.print_details.priceIn_tblXindex = priceIn_tblXindex;
					// alert(printCalculator.currentCalculationData.print_details.priceIn_tblXindex);
				}
			}
				
			//// console.log(printCalculator.dataForProcessing['price']);
			if(printCalculator.currentCalculationData.print_details.lackOfQuantOutPrice){
				alert("Минимальный тираж для данного типа нанесения - "+printCalculator.currentCalculationData.print_details.minQuantOutPrice+"шт \rстоимость будет расчитана как для минимального тиража");	
			}
			return tbl_html;
			
	}
	,
	nextTag:function(node){ 
	   var node = node.nextSibling; 
	   return (node && node.nodeType!=1) ? this.nextTag(node) : node; 
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