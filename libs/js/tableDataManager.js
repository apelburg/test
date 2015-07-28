// JavaScript Document


    var tableDataManager = {
		url: null,
		element: null,
		container: null,
		edit_field: null,
		processing_timer_div: null,
		max_length: null,
	    install: function(){ 
			 
			 var all_tables = document.getElementsByTagName('table');
			 for(var i = 0; i < all_tables.length; i++)
			 {
				  if(all_tables[i].getAttribute('tbl') && all_tables[i].getAttribute('tbl') == 'managed'){
					  var table = all_tables[i];
				      break;
				  }
			 }
			 if(!table) return;
			 //alert(table);
			 //table.style.border = '#FF0000 solid 2px';
			 
			 var all_tds = table.getElementsByTagName('td');
			 for(var i = 0; i < all_tds.length; i++)
			 {
				 if(all_tds[i].getAttribute('managed') && all_tds[i].getAttribute('managed') == 'text'){
					 if(window.addEventListener) all_tds[i].addEventListener('click',tableDataManager.text_type_cell_handler,false);
					 else if(window.attachEvent) all_tds[i].attachEvent('onclick',tableDataManager.text_type_cell_handler);
					 else all_tds[i].onclick = tableDataManager.text_type_cell_handler;
					 //all_tds[i].style.border = '#FF0000 solid 2px';
					 all_tds[i].style.cursor = 'pointer';
					 all_tds[i].style.position = 'relative';
				 }
				 //if(all_tds[key].getAttribute('managed') && all_tds[key].getAttribute('managed') == 'num')  all_tds[key].onclick = this.nun_cell_handler;
			 }
			/* */
			document.body.addEventListener('click',function(){/*alert(1);*/tableDataManager.close_edit_field();},false);
		}
		,
		text_type_cell_handler: function(e){
			var e = e || window.event;
			
			if(e.target) var element = e.target;
			if(e.srcElement) var element = e.srcElement;
			tableDataManager.element = element;
			e.stopPropagation();
			//element.style.border = '#FF0000 solid 2px';
			if(tableDataManager.container){
				//alert(2);
				tableDataManager.close_edit_field();
			}
			
			tableDataManager.build_edit_field();
	
		}
		,
		build_edit_field: function(element){
			
			var element = tableDataManager.element;
			
			var container = document.createElement('div');
			var pos = tableDataManager.define_item_positon(element);
			container.style.position = 'absolute';
			container.style.top = '-2px';
			container.style.left = '-2px';
			container.style.zIndex = '1';
			
			var edit_field = document.createElement('div');
			edit_field.style.border = '#499BEF solid 2px';
			edit_field.style.padding = '4px';
			edit_field.style.backgroundColor = '#FFFFFF';
			edit_field.style.width = '100%';
		    edit_field.style.minHeight = '30px';
			edit_field.contentEditable = "true";
			edit_field.style.cursor = 'default';
			edit_field.style.fontSize = 'inherit';
			edit_field.style.fontFamily = 'inherit';
	        edit_field.innerHTML = element.innerHTML;
			edit_field.addEventListener('click',function(e){e.stopPropagation();},false);
			
			// close button
			var close_btn = document.createElement('div');
			close_btn.style.border = '#000000 solid 1px';
			close_btn.style.backgroundColor = '#E2C759';
			close_btn.style.color = '#743813';
			close_btn.style.fontWeight = 'bold';
			close_btn.style.border = '#BA7113 solid 2px';
			close_btn.style.padding = '6px';
			close_btn.style.width = '74px';
			close_btn.innerHTML = 'сохранить';
			close_btn.style.cursor = 'pointer';
			close_btn.addEventListener('click',function(e){ e.stopPropagation(); tableDataManager.save_data(); },false);
			
			container.appendChild(edit_field);
			container.appendChild(close_btn);
			element.appendChild(container);
			
			tableDataManager.edit_field = edit_field;
			tableDataManager.container = container;
			
			
		}
		,
		save_data: function(){
			tableDataManager.processing_timer();
			
			var edit_field = tableDataManager.edit_field;
			var element = tableDataManager.element;
			edit_field.style.border = '#00FF00 solid 1px';
			var new_data = edit_field.innerHTML;
			//alert(new_data);
            
			element.innerHTML = new_data;
			//tableDataManager.element = null;
			tableDataManager.container = null;
			tableDataManager.edit_field = null;
			
			var bd_row_id = element.getAttribute('bd_row_id');
			var bd_field = element.getAttribute('bd_field');
			//alert(edit_field.innerHTML);
			
			//////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////    AJAX  ///////////////////////////////////////////		
			
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
	        var pair = "&id=" + bd_row_id + "&field_name=" + bd_field + "&field_val=" + encodeURIComponent(new_data).replace(regexp,"+");
	       //alert(itog_pairs); 
	        var request = HTTP.newRequest();
	  
			var url = this.url;
		    // alert(pair);
			// производим запрос
			request.open("POST", url); 
			request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			request.send(pair);
			
			
		   
			request.onreadystatechange = function(){ // создаем обработчик события
			   if(request.readyState == 4){ // проверяем состояние запроса если запрос == 4 значит ответ получен полностью
				   if(request.status == 200){ // проверяем состояние ответа (код состояния HTTP) если все впорядке продолжаем 
					   ///////////////////////////////////////////
					   // обрабатываем ответ сервера
						
						var request_response = request.responseText;
						// alert(request_response);
						tableDataManager.stop_processing_timer();
						// выводим замечание об ощибке если есть
						//if(request_response != '') 
				
					   //alert("AJAX запрос выполнен");
					 
					}
					else{
				      tableDataManager.stop_processing_timer();
					  alert("ошибка AJAX");
					}
				 }
			 }
			
			//////////////////////////////////////////////////////////////////////////////////////////
		}
		,
		close_edit_field: function(e){
           // alert(tableDataManager.container.parentNode);
			if(tableDataManager.container){
			    tableDataManager.container.parentNode.removeChild(tableDataManager.container);
				tableDataManager.element = null;
			    tableDataManager.container = null;
			    tableDataManager.edit_field = null;
			}
		}
		,
		define_item_positon: function(element){
			var top = 0;
			var left = 0;
			while(element){
				top  += element.offsetTop;
				left += element.offsetLeft;
				element = element.offsetParent;
			}
			var pos = [];
			pos[0] = top;
			pos[1] = left;
			return pos;

		}
		,
		processing_timer: function(){
			function show_timer(){
				var timer_container = document.createElement('div');
				timer_container.style.position = 'absolute';
				timer_container.style.top = '0px';
				timer_container.style.left = '0px';
				timer_container.style.height = '40px';
				timer_container.style.width = '40px';
			
				
				var img = new Image();
				img.src = '../../admin/order_manager/libs/js/img/loading.gif';
				
				timer_container.appendChild(img);
				tableDataManager.element.appendChild(timer_container);
				tableDataManager.processing_timer_div = timer_container;	
			}
			setTimeout(show_timer,5);
			

		}
		,
		stop_processing_timer: function(){
		    if(this.processing_timer_div) this.processing_timer_div.parentNode.removeChild(this.processing_timer_div);	

		}
	}
	
	// инициализация
	if(window.addEventListener){
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
	