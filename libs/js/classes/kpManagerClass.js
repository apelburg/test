// JavaScript Document
var kpManager = {
		sendKpByMail:function (id,client_id,manager_id){
		
			//alert(id+" "+client_id+" "+manager_id);
			var url = location.protocol +'//'+ location.hostname+location.pathname+location.search+'&send_kp_by_mail=['+id+','+client_id+','+manager_id+']';
			
			make_ajax_request(url,call_back);
			function call_back(response){
				
				//alert(response);
			    var response_obj = JSON.parse(response);
				//for (var prop in response_obj) alert ( prop+' '+response_obj[prop]);
				kpManager.details = response_obj;
				kpManager.manager_id = manager_id;
				kpManager.client_id = client_id;

				
				
				//
				var box = document.createElement('div');
				box.id = "mailSendDialog";
				box.style.width = '600px';
				box.style.border = '2px solid #CCC';
				box.style.display = "none";
				//
				kpManager.textarea = document.createElement('textarea');
			    ///textarea.id = 'message';
				//textarea.name = 'message';
				
				//
				kpManager.contfaceSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.client_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.client_mails[i].mail+'">'+kpManager.details.client_mails[i].person+' '+kpManager.details.client_mails[i].mail+'</option>';
				}
				kpManager.contfaceSelect.innerHTML = options_arr.join("\r\n");
				
				kpManager.managerMailsSelect = document.createElement('select');
				var options_arr = new Array();
				for( var i = 0 ; i < kpManager.details.manager_mails.length ; i++ ){//.length
					options_arr[i] = '<option value="'+kpManager.details.manager_mails[i].mail+'">'+kpManager.details.manager_mails[i]+'</option>';
				}
				kpManager.managerMailsSelect.innerHTML = options_arr.join("\r\n");
				
				//
				var button = document.createElement('button');
				button.onclick = kpManager.sendKpByMailFinalStep;
				button.innerHTML = 'send';
				
				var br = document.createElement('br')
				
				box.appendChild(document.createTextNode("кому:"));
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode("почтовые ящики сотрудников"));
				box.appendChild(kpManager.contfaceSelect.cloneNode(true));
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode("другие почтовые ящики"));
				input = document.createElement('input');
				input.style.height = "10px";
				input.style.width = "200px";
				box.appendChild(input.cloneNode(true));
				box.appendChild(br.cloneNode());
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode("от:"));
				box.appendChild(kpManager.managerMailsSelect);
				
				box.appendChild(br.cloneNode());
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode("заголовок письма:"));
				box.appendChild(br.cloneNode());
				box.appendChild(input);
				
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode("текст письма:"));
				box.appendChild(br.cloneNode());
				box.appendChild(kpManager.textarea);
				box.appendChild(br.cloneNode());
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode("прикрепленный файл:"));
				box.appendChild(br.cloneNode());
				box.appendChild(document.createTextNode(kpManager.details.filename.slice(kpManager.details.filename.lastIndexOf("/")+1)));
				box.appendChild(br.cloneNode());
				box.appendChild(br.cloneNode());
				box.appendChild(button);
				
				document.body.appendChild(box);
				
				
				$("#mailSendDialog").dialog({autoOpen: false,title: "отправка КП на email клиента",modal:true,width: 600});
				$("#mailSendDialog").dialog("open");
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
			pairs += '"message":"'+encodeURIComponent(kpManager.textarea.value).replace(regexp,"+")+'",';
			pairs += '"filename":"'+kpManager.details.filename+'",';
			pairs += '"to":"'+kpManager.contfaceSelect.options[kpManager.contfaceSelect.selectedIndex].value+'",';
			pairs += '"from":"andrey@apelburg.ru",';
			pairs += '"subject":"Коммерческое предложение"';
			pairs += '}';
			
			

			make_ajax_post_request(url,pairs,call_back);
			function call_back(response){
				 //alert(response);
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