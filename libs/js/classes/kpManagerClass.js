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
				kpManager.contfaceSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.client_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.client_mails[i].mail+'">'+kpManager.details.client_mails[i].person+' '+kpManager.details.client_mails[i].mail+'</option>';
				}
				kpManager.contfaceSelect.innerHTML = options_arr.join("\r\n");
				document.getElementById('mailSelectTo').appendChild(kpManager.contfaceSelect);
				
				
				kpManager.managerMailsSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.manager_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.manager_mails[i].mail+'">'+kpManager.details.manager_mails[i]+'</option>';
				}
				kpManager.managerMailsSelect.innerHTML = options_arr.join("\r\n");
				document.getElementById('mailSelectFrom').appendChild(kpManager.managerMailsSelect);
				
				// поле темы письма
				kpManager.mailSubject = document.createElement('div');
				kpManager.mailSubject.style.height = '20px';
				kpManager.mailSubject.style.border = '2px solid blue';
				kpManager.mailSubject.contentEditable = "true";
				document.getElementById('mailSubject').appendChild(kpManager.mailSubject);
				
				// поле для ввода текста письма
				kpManager.textarea = document.createElement('div');
				kpManager.textarea.style.height = '20px';
				kpManager.textarea.style.border = '2px solid green';
				kpManager.textarea.contentEditable = "true";
				document.getElementById('mailMessage').appendChild(kpManager.textarea);
				
				
				$("#mailSendDialog").dialog({autoOpen: false,title: "отправка КП на email клиента",modal:true,width: 900});
				$("#mailSendDialog").dialog("open");/**/
		}
		,
		sendKpByMail:function (id,client_id,manager_id){
		
			//alert(id+" "+client_id+" "+manager_id);
			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search+'&send_kp_by_mail=['+id+','+client_id+','+manager_id+']';
			
			make_ajax_request(url,call_back);
			function call_back(response){
				
				//alert(response);
			    var response_obj = JSON.parse(response);
				kpManager.details = response_obj;
				kpManager.manager_id = manager_id;
				kpManager.client_id = client_id;

				
				//for (var prop in response_obj) alert ( prop+' '+response_obj[prop]);
				kpManager.createLetterWIndow();
				
				
				
			}
	
		},
		sendKpByMailFinalStep:function (){
			// var filename
			// message
			// manager_id
			// client_id
			// template

			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search;
			
			var regexp = /%20/g; // Регулярное выражение соответствующее закодированному пробелу
	        
			var pairs = 'send_kp_by_mail_final_step=';
		    pairs += '{';
			pairs += '"message":"'+encodeURIComponent(kpManager.textarea.innerHTML).replace(regexp,"+")+'",';
			pairs += '"filename":"'+kpManager.details.filename+'",';
			pairs += '"to":"'+kpManager.contfaceSelect.options[kpManager.contfaceSelect.selectedIndex].value+'",';
			pairs += '"from":"andrey@apelburg.ru",';
			pairs += '"subject":"'+kpManager.mailSubject.innerHTML+'"';
			pairs += '}';
			
			
            alert(pairs);
			
			make_ajax_post_request(url,pairs,call_back);
			function call_back(response){
				 alert(response);
				 response = JSON.parse(response);
				 var div = document.createElement('div');
				 div.id = "mailResponseDialog";
				 div.style.textAlign = "center";
				 div.style.display = "none";
				 div.innerHTML = response[1];
				 document.body.appendChild(div);
				 
				 if(response[0]) $("#mailSendDialog").dialog("close");
				 
				 $("#mailResponseDialog").dialog({autoOpen:false ,title:"Результат отправки письма",close: function() {$("#mailResponseDialog").remove();}});
				 $("#mailResponseDialog").dialog("open");
		    }
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