<?php
	/*
	Все методы которые заканчиваются на AJAX относятся к обработчикам событий AJAX
	Причем название метода состоит из строки отправленной в переменной $_POST['AJAX']
	и приставки _AJAX
	*/


    class Cabinet_general{
		// id юзера
		private $user_id;

		// дефолтная расшифровка меню
		public $menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked_snab' => 'Не обработанные СНАБ',
		'no_worcked_men' => 'Не обработанные МЕН',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные СНАБ',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'Отказанные',
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'Приостановлен',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные',
		'history' => 'История'													
		); 

		// допуски пользователя
		public $user_access;

		// права на редактирование поля определяются внутри 
		// некоторых функций 
		private $edit_admin =  ' contenteditable="true" class="edit_span"';
		private $edit_men = ' contenteditable="true" class="edit_span"';
		private $edit_snab = ' contenteditable="true" class="edit_span"';

				
		function __construct(){
			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);

			//$this->id_position = isset($_GET['id'])?$_GET['id']:0;
			
			
			// обработчик AJAX через ключ AJAX
			# если существует метод с названием из запроса AJAX - обращаемся к нему

			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}

			## роутер классов по уровню допуска
			## т.е. на каждый допуск свои шаблоны и классы,
			## при желании можно инклудить несколько классов и обращаться к их методам
			$this->__ROUTER_CLASS__();
		}

		//////////////////////////
		//	Для раздичных подразделений компании необходимы совершенно различные права
		//  и отображение информации, причём одни и теже статусы в программе для каждого пользователя 
		//  выгледят по разному, поэтому для каждого отдела предусмотрен отдельный класс
		//  в каждом классе содержится метод с названием '.$_GET['section'].'_Template для каждого пункта левого меню  
		//////////////////////////
		private Function __ROUTER_CLASS__(){
			switch ($this->user_access) {
				case '1':					
					$text = 'администратор <br>';
					echo $this->wrap_text_in_warning_message($text);
					
					include_once 'cabinet_admin_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_admin_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					
					break;

				case '2':				
					$text = 'бухгалтер<br>';
					echo $this->wrap_text_in_warning_message($text);
					include_once 'cabinet_buch_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_buch_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				case '4':					
					$text = 'производство';// УСЛУГИ
					echo $this->wrap_text_in_warning_message($text);
					
					include_once 'cabinet_production_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_production_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;
				case '5':
					$text = 'менеджер';
					echo $this->wrap_text_in_warning_message($text);
					include_once 'cabinet_men_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_men_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				case '6':
					$text = 'водитель';// УСЛУГИ
					echo $this->wrap_text_in_warning_message($text);
					break;

				case '7':
					$text = 'склад';
					echo $this->wrap_text_in_warning_message($text);

					echo '';


					include_once 'cabinet_sklad_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_sklad_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса снабжения формулировки для меню, понятные для снаба
					$this->menu_name_arr = $this->CLASS->menu_name_arr;


					break;

				case '8':
					$text = 'снабжение';
					echo $this->wrap_text_in_warning_message($text);
					include_once 'cabinet_snab_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_snab_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса снабжения формулировки для меню, понятные для снаба
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				case '9':
					$text = 'дизайнер';// УСЛУГИ
					echo $this->wrap_text_in_warning_message($text);

					include_once 'cabinet_designers_class.php';
					// создаём экземпляр класса
					$this->CLASS = new Cabinet_designer_class($this->user_access);
					// запускаем роутер шаблонов
					$this->CLASS->__subsection_router__();
					// получаем из класса формулировки для меню
					$this->menu_name_arr = $this->CLASS->menu_name_arr;
					break;

				default:					
					$text = 'У вас не хватает прав на доступ к данному разделу!!!';
					echo $this->wrap_text_in_warning_message($text);
					break;
			}
		}

		/////////////////  AJAX START ///////////////// 
		private function _AJAX_($name){
			$method_AJAX = $name.'_AJAX';

			// если в этом классе существует такой метод - выполняем его и выходим
			if(method_exists($this, $method_AJAX)){
				$this->$method_AJAX();
				exit;
			}		
			
		}
		/////////////////  AJAX METHODs  ///////////////// 
		############################################
		###				AJAX START               ###
		############################################
		//////////////////////////
		//	paperwork START
		//////////////////////////
		private function change_payment_date_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_date` =  '".$_POST['date']."' WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		private function change_payment_status_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		private function change_invoce_num_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `invoice_num` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		private function number_payment_list_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `number_pyament_list` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		private function select_global_status_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `global_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		
		private function change_ttn_number_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `ttn_number` =  '".$_POST['value']."', ttn_get = NOW() WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}

		private function change_delivery_tir_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `delivery_tir` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		private function change_status_snab_AJAX(){
			global $mysqli;
			$query = "UPDATE `".CAB_ORDER_MAIN."` SET  `status_snab` =  '".$_POST['value']."' WHERE  `".CAB_ORDER_MAIN."`.`id` =".$_POST['row_id'].";";
			$result = $mysqli->query($query) or die($mysqli->error);
		}
		//////////////////////////
		//	paperwork END
		//////////////////////////
		
	// 	// сохраняет TZ по услуге
	// 	private function save_tz_text_AJAX(){
	// 		global $mysqli;
	// 		$query = "UPDATE `".RT_DOP_USLUGI."` SET `tz`='".$_POST['tz']."' WHERE `id`='".$_POST['rt_dop_uslugi_id']."';
	// ";
	// 		$result = $mysqli->query($query) or die($mysqli->error);

	// 		echo '{"response":"OK" , "name":"save_tz_text_AJAX","increment_id":"'.$_POST['increment_id'].'"}';
	// 	}


		// выводит форму с выбором менеджеров 
		public function get_a_list_of_managers_to_be_attached_to_the_request_AJAX(){
			global $mysqli;
			$html = '';				

			if($_POST['client_id']!='0'){// если клиент приклеплён
				$html .= $this->wrap_text_in_warning_message('Для прикреплённого клиента доступны следующие кураторы:');
				# получаем список кураторов
				// подключаем класс клиента
				include_once ('./libs/php/classes/client_class.php');
				$managers_arr = Client::get_relate_managers($_POST['client_id']);
			}else{ // если клиент не прикреплён
				$managers_arr = array();
			    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `access` = '5'";
			    $result = $mysqli->query($query)or die($mysqli->error);
			    if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$managers_arr[] = $row;
					}
				}
			}

			// echo count($managers_arr);
			$html .= '<form  id="chose_manager_tbl">';
			$html .='<table>';

			
			for ($i=0; $i < count($managers_arr); $i++) {
				$html .= '<tr>';
			    for ($j=1; $j<=3; $j++) {
			    	if(isset($managers_arr[$i])){
				    	$checked = ($managers_arr[$i]['id'] == $_POST['manager_id'])?'class="checked"':'';
				    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
				    	$html .= '<td '.$checked.' data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
			    	}else{
			    		$html .= "<td></td>";
			    	}
			    	$i++;
			    }
			    $html .= '</tr>';
			}

			$html .= '</table>';
			$html .= '<input type="hidden" value="attach_manager_to_request" name="AJAX">';
			$html .= '<input type="hidden" value="'.$_POST['manager_id'].'" name="manager_id">';
			$html .= '<input type="hidden" value="'.$_POST['rt_list_id'].'" name="rt_list_id">';
			$html .= '<input type="hidden" value="" name="client_id">';
			$html .= '<form>';
			echo $html;
			// вывод менеджеров				
		}


		private function attach_manager_to_request_AJAX(){
			global $mysqli;
			// прикрепить менеджера к запросу	
			$client_id = (trim($_POST['client_id'])=='')?0:$_POST['client_id'];
			$query ="UPDATE  `".RT_LIST."` SET  
			`manager_id` =  '".(int)$_POST['manager_id']."', 
			`time_attach_manager` = NOW(),
			`status` = 'not_process'
			 WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
			$result = $mysqli->query($query) or die($mysqli->error);	
			echo '{"response":"OK"}';
			return;		
		}


		// выводит форму со списоком клиентов для прикрепления к запросу 
		private function get_a_list_of_clients_to_be_attached_to_the_request_AJAX(){
			global $mysqli;
			$html ='';
			$query = "SELECT * FROM `".CLIENTS_TBL."` ";

			// Запрос с сортировкой почему-то не хочет выводит ь некоторые компании
			// к примеру не выводит компанию *Морской салон ЗАО  - id = 18
			// $query = "SELECT * FROM `".CLIENTS_TBL."`  order by company ASC;";
			$result = $mysqli->query($query) or die($mysqli->error);				
			$clients = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$clients[] = $row;
				}
			}

			$html .= '<form  id="chose_client_tbl">';
			$html .='<table>';

			$html_row = '';
			$first_row = ''; 
			$f_r = 0;

			for ($i=0; $i < count($clients); $i++) {
				$row = '<tr>';
			    for ($j=1; $j<=3; $j++) {
			    	if(isset($clients[$i])){
				    	$checked = ($clients[$i]['id'] == $_POST['client_id'])?'class="checked"':'';
				    	$row .= '<td '.$checked.' data-id="'.$clients[$i]['id'].'">'.$clients[$i]['company']."</td>";
			    		// если присутствует выбранный клиент, ставим флаг
			    		if($checked!=''){$f_r = 1;}	
			    	}else{
			    		$row .= "<td></td>";
			    	}			    	
			    	$i++;
			    }

			    $row .= '</tr>';

			    // если нам попалась строка с выбранным клиентом, запоминаем её и не добавляем в Html...
			    // добавим её в начало таблицы позже
			    if($f_r==1){
			    	$first_row .= $row; $f_r = 0;
			    }else{
			    	$html_row .= $row;
			    }
			}

			// помещаем выбранного клиента в начало таблицы
			$html .= $first_row.$html_row;

			$html .= '</table>';
			$html .= '<input type="hidden" value="attach_client_to_request" name="AJAX">';
			$html .= '<input type="hidden" value="" name="manager_id">';
			$html .= '<input type="hidden" value="'.$_POST['rt_list_id'].'" name="rt_list_id">';
			$html .= '<input type="hidden" value="'.$_POST['client_id'].'" name="client_id">';
			$html .= '<form>';
			echo $html;
		}

		// прикрепляет клиента к запросу
		private function attach_client_to_request_AJAX(){
			// получаем кураторов по выбранному клиенту
			// подключаем класс клиента
			include_once ('./libs/php/classes/client_class.php');
			$managers_arr = Client::get_relate_managers($_POST['client_id']);
						

			switch (count($managers_arr)) {
				case '0':
					// если у нас не прикреплено к данному клиенту ни одного менеджера - выводим сообщение об ошибке
					$html = $this->wrap_text_in_warning_message_post('К данному клиенту не прикреплено ни одного менеджера.');
					echo '{"response":"show_new_window","title":"Ошибка","html":"'.base64_encode($html).'"}';
					break;
				case '1':
					// echo '<pre>';
					// print_r($_POST);
					// echo '</pre>';
						
					// если прикреплен только 1 - никаких проблем. 
					// прикрепляем к запросу клиента и менеджера 
					// Переписываем менеджера, отправляем данные о нем в браузер, вызываем там функцию и меняем имя менеджера на странице
					global $mysqli;
					// прикрепить клиента и менеджера к запросу	
					$query ="UPDATE  `".RT_LIST."` SET  
					`manager_id` =  '".(int)$managers_arr[0]['id']."',
					`client_id` =  '".(int)$_POST['client_id']."', 
					`time_attach_manager` = NOW(),
					`status` = 'not_process' 
					WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
					$result = $mysqli->query($query) or die($mysqli->error);	
					echo '{"response":"OK","function":"change_attache_manager","rt_list_id":"'.$_POST['rt_list_id'].'", "manager_id":"'.$managers_arr[0]['id'].'","manager_name":"'.$managers_arr[0]['name'].' '.$managers_arr[0]['last_name'].'"}';

					break;
				
				default:
					// если к клиенту присоединено несколько кураторов выполняем первый пункт по умолчанию, потом вызываем окно с выбором менеджера
					global $mysqli;
					// прикрепить клиента и менеджера к запросу	
					$query ="UPDATE  `".RT_LIST."` SET  
						`manager_id` =  '".(int)$managers_arr[0]['id']."',
						`client_id` =  '".(int)$_POST['client_id']."', 
						`time_attach_manager` = NOW(),
						`status` = 'not_process'
						WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
					$result = $mysqli->query($query) or die($mysqli->error);	
					
					//////////////////////////
					//	осбираем форму для выбора одного из кураторов
					//  
					//////////////////////////
					$html = '<form  id="chose_manager_tbl">';
					$html .='<table>';					
					for ($i=0; $i < count($managers_arr); $i++) {
						$html .= '<tr>';
					    for ($j=1; $j<=3; $j++) {
					    	if(isset($managers_arr[$i])){
						    	$checked = ($managers_arr[$i]['id'] == $managers_arr[0]['id'])?'class="checked"':'';
						    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
						    	$html .= '<td '.$checked.' data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
					    	}else{
					    		$html .= "<td></td>";
					    	}
					    	$i++;
					    }

					    $html .= '</tr>';
					}
					$html .= '</table>';
					$html .= '<input type="hidden" value="attach_manager_to_request" name="AJAX">';
					$html .= '<input type="hidden" value="'.$_POST['manager_id'].'" name="manager_id">';
					$html .= '<input type="hidden" value="'.$_POST['rt_list_id'].'" name="rt_list_id">';
					$html .= '<input type="hidden" value="" name="client_id">';
					$html .= '<form>';
					
					// записываем на странице пользователя в строку с установленным клиентом имя первого куратора из списка
					// затем даём выбрать из иставшихся
					echo '{"response":"show_new_window","html":"'.base64_encode($html).'","function":"change_attache_manager","rt_list_id":"'.$_POST['rt_list_id'].'", "manager_id":"'.$managers_arr[0]['id'].'","manager_name":"'.$managers_arr[0]['name'].' '.$managers_arr[0]['last_name'].'"}';
					break;
			}
			// echo '<pre>';
			// print_r($managers_arr);
			// echo '</pre>';				
		}



		//пример обработки AJAX запроса
		# выводит информацию из глобальных массивов и объекта текущего класса
		private function show_globals_arrays_AJAX(){
			echo '<strong>POST:</strong>';
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';

			echo '<strong>GET:</strong>';
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';

			echo '<strong>SESSION:</strong>';
			echo '<pre>';
			print_r($_SESSION);
			echo '</pre>';

			echo '<strong>Object Class:</strong>';
			echo '<pre>';
			print_r($this);
			echo '</pre>';
		}

		//////////////////////////
		//	ORDERS start
		//////////////////////////
		// выбор поставщика
		private function chose_supplier_AJAX(){

			// запоминаем id уже выбранных поставщиков
			$already_chosen_arr = explode(',', $_POST['already_chosen']);

			$suppliers_arr = Supplier::get_all_suppliers_Database_Array();
			$html = '<form>';
			$html .='<table id="chose_supplier_tbl">';

			$n=0;
			for ($i=1; $i < count($suppliers_arr); $i++) {
				$html .= '<tr>';
			    for ($j=1; $j<=3; $j++) {
			    	$checked = '';
			    	foreach ($already_chosen_arr as $key => $id) {
			    		if(isset($suppliers_arr[$i]['id'])){
				    		if($suppliers_arr[$i]['id']==trim($id)){
				    			$checked = 'class="checked"';
				    		}
			    		}
			    	}
			    	$html .= (isset($suppliers_arr[$i]['nickName']))?'<td '.$checked.' data-id="'.$suppliers_arr[$i]['id'].'">'.$suppliers_arr[$i]['nickName']."</td>":"<td></td>";
			    	$i++;
			    }

			    $html .= '</tr>';
			}
			$html .= '</table>';
			$html .= '<input type="hidden" name="AJAX" value="change_supliers_info_dop_data">';
			$html .= '<input type="hidden" name="id_dop_data" value="'.$_POST['id_dop_data'].'">';
			$html .= '<input type="hidden" name="suppliers_id" value="'.$_POST['already_chosen'].'">';
			$html .= '<input type="hidden" name="suppliers_name" value="'.$_POST['suppliers_name'].'">';
			$html .= '</form>';
			echo $html;
			exit;	
		}
		private function change_supliers_info_dop_data_AJAX(){
			$this->change_supliers_info_dop_data_Database();
		}
		// редактируем информацию об поставщиках для некаталожного варианта расчёта
		public function change_supliers_info_dop_data_Database(){
			global $mysqli;
			$query ="UPDATE `".CAB_ORDER_DOP_DATA."` SET
			             `suppliers_id` = '".$_POST['suppliers_id']."',
			             `suppliers_name` = '".$_POST['suppliers_name']."' 
			             WHERE `id` =  '".$_POST['id_dop_data']."';
			             ";
			$result = $mysqli->query($query) or die($mysqli->error);
			
			echo '{"response":"OK","name":"chose_supplier_end", "function":"del_id_chose_supplier_id"}';
		}

		############################################
		###				 AJAX END                ###
		############################################


		// запрашивает из базы допуски пользователя
		// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
		private function get_user_access_Database_Int($id){
			global $mysqli;
			$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);				
			$int = 0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$int = (int)$row['access'];
				}
			}
			//echo $query;
			return $int;
		}

		//////////////////////////
		//	оборачивает в оболочку warning_message
		//////////////////////////
		// отключается при post запросе, важно для AJAX
		private function wrap_text_in_warning_message($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			// в случае получения POST запроса предупреждения отключаем
			if(!empty($_POST)){$html='';}
			return $html;
		}
		private function wrap_text_in_warning_message_post($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}

   	}