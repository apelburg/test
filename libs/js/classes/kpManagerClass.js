// JavaScript Document
    var kpManager = {
		createLetterWIndow:function (){
			
			    var box = document.createElement('div');
				box.id = "mailSendDialog";
				box.style.width = '900px';
				box.style.display = "none";
				
				box.innerHTML = Base64.decode(kpManager.details.main_window_tpl);
				// help button
				box.appendChild(help.btn('kp.sendLetter.window'));
		        document.body.appendChild(box);
				
				
				//инициализируем и навешиваем  поля с данными которые будут отправлены на сервер
				
				
				// поле Кому
				kpManager.mailSelectTo = document.createElement('div');
				kpManager.mailSelectTo.className = 'mailSubject';
				kpManager.mailSelectTo.onclick = function(){ kpManager.bildSelect(this,'client_mails');}
				kpManager.mailSelectTo.innerHTML = '';
				kpManager.mailSelectTo.contentEditable = "true";
				document.getElementById('mailSelectTo').appendChild(kpManager.mailSelectTo);
				
				// поле От кого
				kpManager.mailSelectFrom = document.createElement('div');
				kpManager.mailSelectFrom.className = 'mailSubject';
				kpManager.mailSelectFrom.onclick = function(){ kpManager.bildSelect(this,'manager_mails');}
				kpManager.mailSelectFrom.innerHTML = '';
				kpManager.mailSelectFrom.contentEditable = "true";
				document.getElementById('mailSelectFrom').appendChild(kpManager.mailSelectFrom);
				
				/*
				kpManager.managerMailsSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.manager_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.manager_mails[i].mail+'">'+kpManager.details.manager_mails[i]+'</option>';
				}
				kpManager.managerMailsSelect.innerHTML = options_arr.join("\r\n");
				document.getElementById('mailSelectFrom').appendChild(kpManager.managerMailsSelect);
				*/
				
				// поле темы письма
				kpManager.mailSubject = document.createElement('div');
				kpManager.mailSubject.innerHTML = "Тема письма";
				kpManager.mailSubject.className = 'mailSubject';
				kpManager.mailSubject.contentEditable = "true";
				document.getElementById('mailSubject').appendChild(kpManager.mailSubject);
				
				// поле для ввода текста письма
				kpManager.textarea = document.createElement('div');
				kpManager.textarea.className = 'mailTextarea';
				kpManager.textarea.contentEditable = "true";
				document.getElementById('mailMessage').appendChild(kpManager.textarea);
				
				// поле отображения прикрепленного файла
                document.getElementById('attachedKpFileInput').value = kpManager.details.kp_filename;
				document.getElementById('attachedKpFile').innerHTML = kpManager.details.kp_filename.slice(kpManager.details.kp_filename.lastIndexOf("/")+1);
				
				
				
				$("#mailSendDialog").dialog({autoOpen: false,title: "Отправить коммерческое предложение",modal:true,width: 900,close: function() {this.remove();$("#mailResponseDialog").remove();kpManager.current_message_tpl =false;}});
				$("#mailSendDialog").dialog("open");/**/
		}
		,
		bildSelect:function (element,sourse){
			if(kpManager.bildSelectInProcess) return;
			kpManager.bildSelectInProcess = true;
			// alert(1);
			
			kpManager.bildSelect.container = document.createElement('div');
			kpManager.bildSelect.container.contentEditable = "false";
			kpManager.bildSelect.container.style.position = 'absolute';
			kpManager.bildSelect.container.style.backgroundColor='#FFFFFF';
			kpManager.bildSelect.container.style.zIndex='100';
			kpManager.bildSelect.container.style.top = '20px';
			kpManager.bildSelect.container.style.left = '-1px';
			
			var arr = kpManager.details[sourse];
			for( var i = 0 ; i < arr.length ; i++ ){//.length
				 var div = document.createElement('div');
				 div.className = 'selectRow';
				 div.onclick = function(){ kpManager.addValueToSelect(sourse,element,this); }
				 if(sourse=='client_mails') div.innerHTML = '<div style="float:left; width:270px; border:#FF0000 solid 0px;"><span>' + arr[i].email+'</span></div><div style="float:left; width:200px;">'+ arr[i].position+'</div><div style="float:left; width:250px;">'+ arr[i].name+' '+ arr[i].last_name+ ' '+ arr[i].surname+'</div>';
				 if(sourse=='manager_mails') div.innerHTML = '<div style="float:left; width:300px; border:#FF0000 solid 0px;"><span>' + arr[i]+'</span></div>';
				 kpManager.bildSelect.container.appendChild(div);
			}
			
			if(sourse=='client_mails'){
				 var div = document.createElement('div');
				 div.className = 'linkRow';
				 div.innerHTML = '<div style="float:left; width:500px; border:#FF0000 solid 0px;"><a href="?' + addOrReplaceGetOnURL('page=clients&section=client_folder&subsection=client_card_table','query_num')+'" target="_blank">добавить контакты в карточку клиента</a></div><div class="closeBtn" onclick="kpManager.closeSelect();">&#215;</div>'; //"
				 kpManager.bildSelect.container.appendChild(div);
			}
			element.style.position = 'relative';
			element.appendChild(kpManager.bildSelect.container);
		}
		,
		addValueToSelect:function (sourse,target,row){
            window.event.stopPropagation();
			var value = row.getElementsByTagName("SPAN")[0].innerHTML;
			kpManager.bildSelect.container.parentNode.removeChild(kpManager.bildSelect.container);
			
			// здесь надо будет делать проверку адресов на валидность
			if(sourse=='manager_mails'){
				// поле "От" может быть только один адрес
				target.innerHTML = value;
			}
			else{
				if(target.innerHTML.replace(/^\s\s*/, '').replace(/\s\s*$/, '')=='') target.innerHTML = value;
				else target.innerHTML = target.innerHTML+', '+value;
			}
	
			kpManager.bildSelectInProcess = false;
		}
		,
		closeSelect:function (sourse,target,row){
            window.event.stopPropagation();
			
			kpManager.bildSelect.container.parentNode.removeChild(kpManager.bildSelect.container);
			kpManager.bildSelectInProcess = false;
		}
		,
		sendKpByMail:function (id){
		    show_processing_timer();
			kpManager.kp_id = id;
			//alert(id+" "+client_id+" "+manager_id);
			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search+'&send_kp_by_mail='+kpManager.kp_id;
			
			make_ajax_request(url,call_back);
			function call_back(response){
			    //alert (response);
				close_processing_timer();
				try { 
				   var response_obj = JSON.parse(response);
				}
                catch (e) { 
				    alert('kpManager.sendKpByMail() ошибка JSON.parse(response)');
				}

				kpManager.details = response_obj;
                if(kpManager.details.message_tpls) for(var prop in kpManager.details.message_tpls)kpManager.details.message_tpls[prop] = Base64.decode(kpManager.details.message_tpls[prop]);
				
				//for (var prop in response_obj) alert ( prop+' '+response_obj[prop]);
				kpManager.createLetterWIndow();
				
				
				
			}
	
		},
		sendKpByMailFinalStep:function (){
			// show_processing_timer(); открывается ниже чем само окрно отправки КП
	        // подготавливаем к отправке текст сообщения
		    var  message = kpManager.textarea.innerHTML;
			message = encodeURIComponent(message);
			message = Base64.encode(message);
			
			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search;
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
	        
			// снимаем данные о прикрепленных файлах
			var inputs = document.getElementsByTagName("input");
			var attached_files_arr = [];
			for( var i = 0 ; i < inputs.length ; i++ ){
				if(inputs[i].type == 'checkbox'){
					if(inputs[i].name == 'attachedFile'){
						if(inputs[i].checked == true) attached_files_arr.push(inputs[i].value);//alert(inputs[i].value);
				    }
				}
			}
		
			if(kpManager.mailSelectTo.innerHTML.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
			    alert('не заполнено поле Кому');
				return;
			}
			if(kpManager.mailSelectFrom.innerHTML.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
			    alert('не заполнено поле От');
				return;
			}
			if(kpManager.mailSubject.innerHTML.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
			    alert('не заполнено поле Тема');
				return;
			}
			if(message.replace(/^\s\s*/, '').replace(/\s\s*$/, '')==''){
			    alert('Вы не написали сообщение');
				return;
			}
			

			var pairs = 'send_kp_by_mail_final_step=';
		    pairs += '{';
			
			pairs += '"kp_id":"'+kpManager.kp_id+'",';
			pairs += '"to":"'+kpManager.mailSelectTo.innerHTML+'",';
			pairs += '"from":"'+kpManager.mailSelectFrom.innerHTML+'",';
			pairs += '"subject":"'+kpManager.mailSubject.innerHTML+'",';
			pairs += '"message":"'+message+'"';
			if(attached_files_arr.length) pairs += ',"attached_files":["'+attached_files_arr.join('","')+'"]';
			pairs += '}';
		    //alert(pairs);
			
			make_ajax_post_request(url,pairs,call_back);
			function call_back(response){
				 //alert(response);
				 //return;
				 //close_processing_timer(); открывается ниже чем само окрно отправки КП
	 			 try { 
				     var response = JSON.parse(response);
				 }
                 catch (e) { 
					 var response =  [0,'Oшибка JS - kpManager.sendKpByMailFinalStep(), JSON.parse(response)'];
				 }
				 
				 var div = document.createElement('div');
				 div.id = "mailResponseDialog";
				 div.style.textAlign = "center";
				 div.style.display = "none";
				 div.innerHTML = response[1];
				 document.body.appendChild(div);
				 
				 
				 // если отправка прошла удачно удаляем окно редактирования сообщения и обнуляем значение kpManager.current_message_tpl
				 if(response[0]){
					 $("#mailSendDialog").remove();
					 kpManager.current_message_tpl =false;
					 
					 // вписываем дату отправки 
					 var tdsArr = document.getElementById('kp_list_tbl').getElementsByTagName('TD');
					 
					 for(var i = 0; i < tdsArr.length;i++){
						 if(tdsArr[i].hasAttribute('send_time_type') && tdsArr[i].getAttribute('send_time_type') == kpManager.kp_id){
							 var date = new Date();
							 var day = date.getDate().toString();
							 if(day.length ==1 ) day = '0'+day;
							 var month = (date.getMonth()+1).toString();
							 if(month.length ==1 ) month = '0'+month;
							 tdsArr[i].innerHTML = day + '.' + month + '.' + date.getFullYear(); 
						 }
					 }
				 }
				 
				 $("#mailResponseDialog").dialog({autoOpen:false ,title:"Результат отправки письма",width: 1200,close: function() {this.remove();}});
				 $("#mailResponseDialog").dialog("open");
		    }
		}
		,
		setMessageTpl:function (name){
			// если есть текущий установленный шаблон то копируем его состояние в массив хранящий шаблоны
			if(kpManager.current_message_tpl) kpManager.details.message_tpls[kpManager.current_message_tpl] = kpManager.textarea.innerHTML;
			// записываем имя текущего шаблона
			kpManager.current_message_tpl = name;
			kpManager.textarea.innerHTML = kpManager.details.message_tpls[name];
		}
		,
		kpToPrint:function (version,param){
			//php script lies in client_folder_controller father dont work
			if(version == 'old') var url = location.protocol +'//'+ location.hostname+location.pathname+location.search+'&kp_to_print=old{@}'+param;
			
			//alert(url);
	
			make_ajax_request(url,call_back);
			function call_back(response){
				//alert(response);
				var div = document.createElement('div');
				div.style.position = 'absolute';
				div.style.top = '0px';
				div.style.right = '0px';
				div.style.left = '0px';
				div.style.height = '2000px';
				div.style.backgroundColor = '#FF0000';
				var inner_div = document.createElement('div');
				inner_div.style.margin = 'auto';
				inner_div.style.border = '3px solid #CCC';
				inner_div.style.width = '600px';
				inner_div.innerHTML = response;
				div.appendChild(inner_div);
				document.body.appendChild(div);
				window.open();
				//window.print();
				
			}
		}
	}