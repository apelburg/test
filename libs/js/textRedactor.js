// JavaScript Document


    var textRedactor = {
		url: null,
		container: null,
		processing_timer_div: null,
		max_length: null,
	    install: function(url){
			 
		     document.body.onload = function(){ 
				 textRedactor.url = url; 
				 
				 var all_spans = document.getElementsByTagName('span');
				
				 for(var i = 0; i < all_spans.length; i++)
				 {
					 if(all_spans[i].getAttribute('managed') && all_spans[i].getAttribute('managed') == 'text'){
						 all_spans[i].onclick = textRedactor.text_cell_handler;
						 all_spans[i].style.cursor = 'pointer';
					 }
					 //if(all_tds[key].getAttribute('managed') && all_tds[key].getAttribute('managed') == 'num')  all_tds[key].onclick = this.nun_cell_handler;
				 }
				/* */
			 }
		}
		,
		text_cell_handler: function(){
			var element = this;
			textRedactor.build_edit_field(element);
			
			//alert(this.innerHTML);
		}
		,
		build_edit_field: function(element){
			//alert(element);
			var container = document.createElement('div');
			var pos = textRedactor.define_item_positon(element);
			container.style.position = 'absolute';
			container.style.top = pos[0] + 'px';
			container.style.left = pos[1] + 'px';
			
			var edit_field = document.createElement('div');
			edit_field.style.border = '#499BEF solid 2px';
			edit_field.style.padding = '4px';
			edit_field.style.backgroundColor = '#FFFFFF';
			edit_field.style.width = '100%';
		    edit_field.style.minHeight = '30px';
			edit_field.contentEditable = "true";
	        edit_field.innerHTML = element.innerHTML;
			if(element.getAttribute('max_length')){
				this.max_length = element.getAttribute('max_length');
				edit_field.onkeydown = function(){ 
				    var new_data = edit_field.innerHTML;
					if(new_data.length > textRedactor.max_length){
						alert('максимально допустимое количество символов = ' + textRedactor.max_length + '\r\n Вы ввели ' + new_data.length + ' символ!');
						edit_field.innerHTML = (edit_field.innerHTML).slice(0,30);
					}
			    }
			}
			
			
			
			// close button
			var close_btn = document.createElement('div');
			close_btn.style.border = '#000000 solid 1px';
			close_btn.style.backgroundColor = '#E2C759';
			close_btn.style.color = '#743813';
			close_btn.style.fontWeight = 'bold';
			close_btn.style.border = '#BA7113 solid 2px';
			close_btn.style.padding = '6px';
			close_btn.style.width = '70px';
			close_btn.innerHTML = 'сохранить';
			close_btn.style.cursor = 'pointer';
			close_btn.onclick = function(){ textRedactor.save_data(container,edit_field,element); }
			
			container.appendChild(edit_field);
			container.appendChild(close_btn);
			document.body.appendChild(container);
			
			this.container = container;
		
			//var pos = textRedactor.define_item_positon(element);
			//alert(Geometry.getVerticalScroll());//Geometry.getHorizontalScroll
			 
		}
		,
		save_data: function(container,edit_field,element){
			
			var new_data = edit_field.innerHTML;
            this.processing_timer(textRedactor.container.style.top,textRedactor.container.style.left);
			
			container.parentNode.removeChild(container);
			textRedactor.container = null;
			element.innerHTML = new_data;
			
			var file_link = element.getAttribute('file_link');
			var bd_row_id = element.getAttribute('bd_row_id');
			var bd_field = element.getAttribute('bd_field');
			//alert(edit_field.innerHTML);
			
			
			//////////////////////////////////////////////////////////////////////////////////////////
			/////////////////////////////////////    AJAX  ///////////////////////////////////////////		
			
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
	        var pair = "&id=" + bd_row_id + "&field_name=" + bd_field + "&field_val=" + encodeURIComponent(new_data).replace(regexp,"+");
	       //alert(itog_pairs); 
	        var request = HTTP.newRequest();
	  
			var url = this.url[parseInt(file_link)];
			//alert(pair);
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
						//alert(request_response);
						textRedactor.stop_processing_timer();
						// выводим замечание об ощибке если есть
						//if(request_response != '') 
				
					   //alert("AJAX запрос выполнен");
					 
					}
					else{
					  textRedactor.stop_processing_timer();
					  alert("Частота запросов превысила допустимое значение\rдля данного интернет-соединения, попробуйте\rперезагрузить сайт, для этого нажмите F5");
					}
				 }
			 }
			
			//////////////////////////////////////////////////////////////////////////////////////////
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
		processing_timer: function(top,left){
			function show_timer(){
				var container = document.createElement('div');
				container.style.position = 'absolute';
				container.style.top = top;
				container.style.left = left;
				container.style.height = '40px';
				container.style.width = '40px';
			
				
				var img = new Image();
				img.src = '../../admin/order_manager/libs/js/img/loading.gif';
				
				container.appendChild(img);
				document.body.appendChild(container);
				textRedactor.processing_timer_div = container;	
			}
			setTimeout(show_timer,5);
			

		}
		,
		stop_processing_timer: function(){
		    if(this.processing_timer_div) this.processing_timer_div.parentNode.removeChild(this.processing_timer_div);	

		}
		
		
		
	}
	
	// инициализация
	// textRedactor.install();
	