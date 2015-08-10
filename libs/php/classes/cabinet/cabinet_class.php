<?php
    class Cabinet{
    	// допуски пользователя
    	protected $user_access = 0;

		// глобальные статусы заказа
    	// могут меняться кириллические формулировки в зависимости от уровня доступа
    	// содержится в базе в `os__cab_orders_list` в global_status
    	protected $order_status = array(
			'being_prepared'=>'В оформлении',
			'request_expense'=>'Запрошен счёт',
			'requeried_expense'=>'Перевыставить счёт',
			'waiting_for_payment' => 'ждём оплаты', // сервисный
			'in_work'=>'В работе',
			'ready_for_shipment'=>'Готов к отгрузке',
			'shipped'=>'Отгружен',
			'paused'=>'Приостановлен',		
			'cancelled'=>'Аннулирован'
			);

    	protected $buch_status = array(
    		'score_exhibited' => 'счёт выставлен',
			'payment' => 'оплачен',//дата в таблицу
			'partially_paid' => 'частично оплачен',//дата в таблицу			
			'prihodnik_on_bail' => 'приходник на залог',
			'cancelled'=>'Аннулирован',		
			'returns_client_collateral' => 'возврат залога клиенту',
			'refund_in_a_row' => 'возврат денег по счёту',
			'ogruzochnye_accepted' => 'огрузочные приняты (подписанные)'
    	);

    	// массви с переводом статусов запроса
		protected $name_cirillic_status = array(
			'new_query' => 'новый запрос',
			'not_process' => 'не обработан менеджером',
			'taken_into_operation' => 'взят в обработку',
			'in_work' => 'в работе',
			'history' => 'история'
		);
			


    	function __consturct(){
		}



		// получение формы выбора услуги для заказа
		protected function get_uslugi_list_Database_Html_steep_1_AJAX(){ // шаг 0: выбираем услугу
			$html = '<form>';
			$html.= '<div class="lili lili_head"><span class="name_text">Название услуги</span><div class="echo_price_uslug"><span>$ вход.</span><span>$ исх.</span><span>за сколько</span></div></div>';
			$html .= $this->get_uslugi_list_Database_Html();
			$html .= '<input type="hidden" name="id_uslugi" value="">';
			$html .= '<input type="hidden" name="service_name" value="">';
			$html .= '<input type="hidden" name="id_dop_data" value="'.$_POST['id_dop_data'].'">';
			// $html .= '<input type="hidden" name="quantity" value="">';
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_form_steep_2">';
			$html .= '</form>';
			echo $html;
		}

		// вывод формы дополнительных фопросов по добавляемой услуге
		// шаг 1: заполняем тираж, и доп поля (если назначены)
		protected function add_new_usluga_form_steep_2_AJAX(){ 
			$html = 'Услуга <strong>'.$_POST['service_name'].'</strong>';
			
			$html .= '<form>';
			// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
			unset($_POST['AJAX']);
			// перебираем остальные значения для передачи их далее
			foreach ($_POST as $key => $value) {
				$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
			
			// добавляем новые поля по необходимости
			$html .= '<div>';
			$html .= 'Введите тираж:<br>';
			$html .= '<input type="text" name="quantity" value="">';
			$html .= '</div>';

			//$html .= $this->get_dop_inputs_for_services($_POST['id_uslugi'], $_POST['id_dop_data'])

			$html .= $this->get_empty_dop_inputs_form($_POST['id_uslugi']);

			$html .= '<div>';
			$html .= 'Введите ТЗ / комментарии к услуге:<br>';
			$html .= '<textarea name="tz"></textarea>';
			$html .= '</div>';


			// передаём название метода, который будет обрабатывать данную форму
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_form_steep_3">';
			// чтобы узнать имя пользователя подключам класс Managers
			include_once './libs/php/classes/manager_class.php';
			$html .= '<input type="hidden" name="author_name_added_services" value="'.Manager::get_snab_name_for_query_String($_SESSION['access']['user_id']).'">';
			$html .= '<input type="hidden" name="author_id_added_services" value="'.$_SESSION['access']['user_id'].'">';
			$html .= '</form>';

			echo '{"response":"show_new_window","title":"Шаг 2: Заполните необходимые поля", "html":"'.base64_encode($html).'"}';
		}

		// получаем пустую форму с dop_inputs для прикрепляемой услуги
		protected function get_empty_dop_inputs_form($id){
			global $mysqli;
			// получаем id полей для этой услуги
			$query = "SELECT `name`,`uslugi_dop_inputs_id`,`price_in`,`price_out`,`for_how` FROM ".OUR_USLUGI_LIST." WHERE `id` = '".$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$this->iputs_id_Str = '0';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->iputs_id_Str = $row['uslugi_dop_inputs_id'];
					$this->AddedServiceName = $row['name'];
					$this->Service = $row;
				}
			}

			// получаем массив dop_inputs
			$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `id` IN (".$this->iputs_id_Str.")";
			$this->iputs_arr = array();
			if(trim($this->iputs_id_Str)!=''){
				$result = $mysqli->query($query) or die($mysqli->error);				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->iputs_arr[] = $row;
					}
				}
			}
			$html = '';


			// добавляем поля dop_inputs
			foreach ($this->iputs_arr as $key => $input) {
				//echo $input['name_ru'];
				$html .= $input['name_ru'].'<br>';
				if($input['type']=="text"){
					$html .= '<div><input type="'.$input['type'].'" name="dop_inputs['.$input['name_en'].']" placeholder="" value=""></div>';
				}
			}
			// добавляем скрытые поля 
			$html .= '<input type="hidden" value="'.$this->Service['price_in'].'"  name="price_in">';
			$html .= '<input type="hidden" value="'.$this->Service['price_out'].'"  name="price_out">'; // для услуг добавленных в заказ исходащая цена = 0, т.е. их сибистоимость вычитается из маржинальности
			$html .= '<input type="hidden" value="'.$this->Service['for_how'].'"  name="for_how">';


			return $html;
		}

		protected function add_new_usluga_form_steep_3_AJAX(){ 
			$this->TZ = $_POST['tz'];
			unset($_POST['tz']);
			unset($_POST['AJAX']);// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 

			global $mysqli;
			$html = '';
			// СОБИРАЕМ СКРЫТУЮ ФОРМУ
			$html .= '<form>';

			// перебираем POST массив для передачи их далее
			$f = 0;
			foreach ($_POST as $key => $value) {
				if($key!='dop_inputs'){
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}else if(!$f){
					foreach ($_POST['dop_inputs'] as $key1 => $value) {
						$html .= '<input type="hidden" name="dop_inputs['.$key1.']" value="'.$value.'">';
						// $html .= '<tr><td>'.$iputs_all_arr[$key]['name_ru'].':</td><td>'.$value.'</td></tr>';
					}
					$f++;	
				}else{

				}
			}

			
			// ВЫВОДИМ ВВЕДЁННУЮ ИНФОРМАЦИЮ ДЛЯ ПРОВЕРКИ
			$html .= '<table id="check_input_iformation">';
			$html .= '<tr><td>Услуга:</td><td>'.$_POST['service_name'].'</td></tr>';
			$html .= '<tr><td>Тираж:</td><td>'.$_POST['quantity'].'</td></tr>';
			$this->Service_price_in = ($_POST['for_how'] == "for_one")?$_POST['quantity']*$_POST['price_in']:$_POST['price_in'];
			// $this->Service_price_out = ($_POST['for_how'] == "for_one")?$_POST['quantity']*$_POST['price_out']:$_POST['price_in'];
			$this->Service_price_out = 0; // для услуг добавленных в заказ показываем исходащую цену = 0, т.е. их сибистоимость вычитается из маржинальности
			
			
			$html .= '<tr><td>$ входящая:</td><td><span>'.(($this->user_access==1 || $this->user_access==8)?'<input type="text" name="price_in" value="'.$this->Service_price_in.'">':$this->Service_price_in).'</span>р.</td></tr>';
			

			$html .= '<tr><td>$ исходащая:</td><td><span>'.$this->Service_price_out.'</span>р</td></tr>';
			if(isset($_POST['dop_inputs']) && count($_POST['dop_inputs'])){// если есть доп поля
				// для представления пользователю информациии по доп полям в читабельном виде
				// получаем список всех полей dop_inputs
				$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
				$result = $mysqli->query($query) or die($mysqli->error);
				$iputs_all_arr = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$iputs_all_arr[$row['name_en']] = $row;
					}
				}

				$html .= '<tr><td colspan="2" style="text-align:center">Дополнительные поля</td></tr>';
				foreach ($_POST['dop_inputs'] as $key => $value) {
					$html .= '<tr><td>'.$iputs_all_arr[$key]['name_ru'].':</td><td>'.$value.'</td></tr>';
				}				
			}

			$html .= '<table>';
			$html .= '<div>';
			$html .= 'Введите ТЗ / комментарии к услуге:<br>';
			$html .= '<textarea name="tz">'.$this->TZ.'</textarea>';
			$html .= '</div>';

			// передаём название метода, который будет обрабатывать данную форму
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_end">';

			$html .= '</form>';

			echo '{"response":"show_new_window","title":"Шаг 3: Проверка введённых данных","html":"'.base64_encode($html).'"}';
			
		}

		protected function add_new_usluga_end_AJAX(){
			



			global $mysqli;
			// ob_start();
			// echo '<pre>';
			// print_r($_POST['dop_inputs']);
			// echo '</pre>';
			    	
			// $content = ob_get_contents();
			// ob_get_clean();
			// $html =$content;
			// echo $html;
			$query ="INSERT INTO `".CAB_DOP_USLUGI."` SET ";
			$query .= "`dop_row_id` = '".$_POST['id_dop_data']."',";
			$query .= "`uslugi_id` = '".$_POST['id_uslugi']."',";
			$query .= "`glob_type` = 'extra',";
			$query .= "`quantity` = '".$_POST['quantity']."',";
			$query .= "`price_in` = '".$_POST['price_in']."',";
			$query .= "`price_out` = '".$_POST['price_out']."',";
			$query .= "`for_how` = '".$_POST['for_how']."',";
			$query .= "`tz` = '".$_POST['tz']."',";
			// собираем JSON по доп полям
			if(isset($_POST['dop_inputs']) && count($_POST['dop_inputs'])){
				$query .= "`print_details_dop` = '".json_encode($_POST['dop_inputs'])."',";
			}

			$query .= "`author_name_added_services` = '".$_POST['author_name_added_services']."',";
			$query .= "`author_id_added_services` = '".$_POST['author_id_added_services']."',";			
			$query .= "`change_log` = 'Добавил ".$_POST['author_name_added_services']." ".date("d.m.Y H:m:s")."'";



			$result = $mysqli->query($query) or die($mysqli->error);
			// запоминаем новый id
			$insert_id = $mysqli->insert_id;

			// собираем HTML
			$html = '<tr class="not_provided" data-id="'.$insert_id.'">
					<td></td>
					<td><span class="postfaktum_non_calculate">0</span></td>
					<td><span class="postfaktum_non_calculate service_price_in">0</span></td>
					<td><span class="postfaktum_non_calculate">0</span></td>
					<td><span class="postfaktum_non_calculate service_price_out">0</span></td>
					<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>
					<td class="postfaktum"></td><td class="postfaktum added_postfactum">'.$_POST['service_name'].'</td>
					<td class="postfaktum added_postfactum"><span>'.(($_POST['for_how'] == 'for_one')?$_POST['quantity']:'  -  ').'</span></td>
					<td class="postfaktum added_postfactum"><span class="service_price_in_postfactum">'.(($_POST['for_how'] == 'for_one')?$_POST['quantity']*$_POST['price_in']:$_POST['price_in']).'</span>р</td>
					<td class="postfaktum"><span data-id="'.$insert_id.'" class="on_of">+</span></td><td></td></tr>';
			
			echo '{"response":"OK","function":"add_new_usluga_end","html":"'.base64_encode($html).'"}';
		}


		protected function get_uslugi_list_Database_Html($id=0,$pad=30){	
			global $mysqli;
			$html = '';
			
			$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$price = '<div class="echo_price_uslug"><span></span><span></span></div>';
					if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
						# Это услуги НЕ из КАЛЬКУЛЯТОРА
						// запрос на детей
						$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
						
						$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
						
						// присваиваем конечным услугам класс may_bee_checked
						$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
					}else{
						# Это услуги из КАЛЬКУЛЯТОРА
						// запрос на детей
						$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));

						$price = ($child =='')?'<div class="echo_price_uslug"><span>&nbsp;</span><span>&nbsp;</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
						// присваиваем конечным услугам класс may_bee_checked
						$html.= '<div data-id="'.$row['id'].'" data-type="'.$row['type'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
					}
				}
			}
			return $html;
		}


		// // форма добавления услуги 
		// protected function get_uslugi_list_Database_Html_AJAX(){
		// 	include_once './libs/php/classes/rt_position_catalog_class.php';
		// 	include_once './libs/php/classes/rt_position_gen_class.php';
		// 	new Position_general_Class();			
		// }

		// включение отключение услуги
		protected function change_service_on_of_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
				`on_of` =  '".(int)$_POST['val']."' 
				WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"OK"}';
		}

		// редактирование поля ТЗ к услуге
		protected function save_tz_info_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
				`tz` =  '".$_POST['text']."' 
				WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"OK"}';
		}

		// сохранение dop_inputs, поля хранятся в json 
		protected function save_dop_inputs_AJAX(){
			global $mysqli;
			$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
				`print_details_dop` =  '".$_POST['Json']."' 
				WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"OK"}';
		}


		// сохранение поля резерв
		protected function save_rezerv_info_AJAX(){
			global $mysqli;

			$query = "UPDATE  `".CAB_ORDER_DOP_DATA."`  SET  
				`number_rezerv` =  '".$_POST['text']."' 
				WHERE  `id` ='".$_POST['cab_dop_data_id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"OK"}';

		}


		protected function get_dop_inputs_for_services_AJAX(){
			// для вызова AJAX
			if(isset($_POST['uslugi_id'])){
				$html = $this->get_dop_inputs_for_services($_POST['uslugi_id'],$_POST['dop_usluga_id']);
			}else{
				return 'Укажите id услуги';
			}
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}		

		// ролучаем dop_inputs
		protected function get_dop_inputs_for_services($id, $dop_usluga_id){
			global $mysqli;
			
			// запрашиваем информацию по ТЗ и , если нужно
			if(!isset($this->Service)){ // если нам ничего не известно по строке из CAB_DOP_USLUGI
				$query = "SELECT * FROM ".CAB_DOP_USLUGI." WHERE `id` = '".$dop_usluga_id."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				$this->Service = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Service = $row;
					}
				}
			}
			
			// если у нас есть информация в поле $this->Service['print_details'] - декодируем её в читабельный вид
			if(trim($this->Service['print_details'])!=''){
				include_once './libs/php/classes/agreement_class.php';
				$this->Service['print_details_read'] = '<div><span>Данные из калькулятора:</span><br><div class="calculator_info">'.Agreement::convert_print($this->Service['print_details']) .'</div></div>';
			}else{
				$this->Service['print_details_read'] = '';
			}

			// получаем id полей для этой услуги
			$query = "SELECT `uslugi_dop_inputs_id` FROM ".OUR_USLUGI_LIST." WHERE `id` = '".$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$this->iputs_id_Str = '0';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->iputs_id_Str = $row['uslugi_dop_inputs_id'];
				}
			}



			//////////////////////////
			//	СЛЕДУЮЩИЕ 2 запроса В БЛИЖАЙШЕМ БУДУЩЕМ нужно сократить до одного !!!!!!!!!!!!
			//////////////////////////
			// запрашиваем список полей предназначенных для этой услуги
			$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `id` IN (".$this->iputs_id_Str.")";
			$this->iputs_arr = array();
			if(trim($this->iputs_id_Str)!=''){
				$result = $mysqli->query($query) or die($mysqli->error);				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->iputs_arr[] = $row;
					}
				}
			}

			// получаем список всех полей
			$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
			$result = $mysqli->query($query) or die($mysqli->error);
			$iputs_all_arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$iputs_all_arr[] = $row;
				}
			}

			// получаем  json
			$this->print_details_dop_Json = (trim($this->Service['print_details_dop'])=="")?'{}':$this->Service['print_details_dop'];
			// декодируем json  в массив
			$this->print_details_dop = json_decode($this->print_details_dop_Json, true);


			// перебор полей указанных в услуге
			$html = '';
			// добавляем скрытую json строку для обработке в JS
			$html = '<div id="dop_input_json">'.$this->print_details_dop_Json.'</div>';
					// 	ob_start();
			 	// echo '<pre>';
			 	// print_r($this->print_details_dop);
			 	// echo '</pre>';
			    	
			 	// $content = ob_get_contents();
			 	// ob_get_clean();
			 	// $html .=$content;
			 	// 		ob_start();
			 	// echo '<pre>';
			 	// print_r($iputs_all_arr);
			 	// echo '</pre>';
			    	
			 	// $content = ob_get_contents();
			 	// ob_get_clean();
			 	// $html .=$content;
			// добавляем информацию из калькулятора.... если есть
			$html .= $this->Service['print_details_read'];
			foreach ($this->iputs_arr as $key => $input) {
				//echo $input['name_ru'];
				$html .= $input['name_ru'].'<br>';
				if($input['type']=="text"){
						if(isset($this->print_details_dop[$input['name_en']])){
							$text = $this->print_details_dop[$input['name_en']];
						}else{
							$text = '';
						}
						
						// определяем допуски на редактирование доп полей
						if($this->user_access == 9 || $this->user_access == 8 || $this->user_access == 11){
								$html .= $text;
								$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'"></div>';
							}else{
								$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'" '.((trim($text)=='')?'':'disabled').'></div>';
							}
						
				}else{
						$html .= 'данный тип поля пока что не предусмотрен';
				}
				// удаляем $this->print_details_dop[$input['name_en']]
				unset($this->print_details_dop[$input['name_en']]);				
			}

			
			//перебираем оставшиеся значения из json .... они могут остаться, 
			// если админы что-то наменяли и открепили доп поля от услуги 
			foreach ($iputs_all_arr as $key => $input) {
				if(isset($this->print_details_dop[$input['name_en']])){
					$html .= $input['name_ru'].' * <span class="delete_dop_input_for_admin">(было удалено Админом из списка обязательных полей для услуги)</span><br>';
					if($input['type']=="text"){
							$text = isset($this->print_details_dop[$input['name_en']])?$this->print_details_dop[$input['name_en']]:'';
							
							// определяем допуски на редактирование доп полей
							if($this->user_access == 9 || $this->user_access == 8 || $this->user_access == 11){
								$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'"></div>';
							}else{
								$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'" '.(($text=='')?'':'disabled').'></div>';
							}
							
					}else{
							$html .= 'данный тип поля пока что не предусмотрен';
					}	
				}
			}
			$html .='ТЗ <span class="greyText"> / (комментарий к услуге)</span><br><textarea class="save_tz" name="tz">'.$this->Service['tz'].'</textarea>';

			return $html;
		}



		// определяем поставщика
		protected function get_supplier_name($article){
			$html = '';
			global $suppliers_names_by_prefix_for_get_name;		   
			$prefix = substr($article,0,2);
			if(isset($suppliers_names_by_prefix_for_get_name[$prefix])){	
				$html = $suppliers_names_by_prefix_for_get_name[$prefix];
			}else{
				$html = '<span class="greyText">не требуется</span>';
			}
			return $html;
		}

		// подсчет суммы доп услуг или печати
		// на вход подаётся результат работы get_dop_uslugi_print_type() 
		// или get_dop_uslugi_no_print_type
		public function calc_summ_dop_uslug($arr,$tir=0){ // 
			$summ = 0;
			foreach ($arr as $key => $value) {
				if($value['for_how']=="for_one"){
					$summ += ($value['price_out']*$value['quantity']);					
				}else{
					$summ += $value['price_out'];					
				}
				
			}
			return $summ;
		}

		// выбираем данные о стоимости печати 
		//на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		public function get_dop_uslugi_print_type($arr){
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']=='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}
		public function select_global_status($real_val,$status_arr){
			
			$html = '<select>';
			foreach ($status_arr as $key => $value) {
				$is_checked = ($key==$real_val)?'selected="selected"':'';
				$html .= ' <option '.$is_checked.' value="'.$key.'">'.$value.'</option>';
			}	
			$html .= '</select>';
			return $html;
		}

		
		public function select_status($real_val,$status_arr){
			
			$html = '<select><option value="">...</option>';
			foreach ($status_arr as $key => $value) {
				$is_checked = ($real_val==$key)?'selected="selected"':'';
				$html .= ' <option '.$is_checked.' value="'.$key.'">'.$value.'</option>';
			}	
			$html .= '</select>';
			return $html;
		}

		
		public function get_gen_status($variable,$type){
			$start_status = $variable[0]['status_'.$type];

			foreach ($variable as $key => $value) {
				if($start_status!=$value['status_'.$type] ){
					$start_status = '';
				}
			}
			return $start_status;
		}

		// выбираем данные о стоимости доп услуг не относящихся к печати
		// на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		public function get_dop_uslugi_no_print_type($arr){
			
			
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']!='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}


		// выбираем данные о доп услугах для варианта расчёта
		public function get_query_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data`
			global $mysqli;
			$query = "SELECT `".RT_DOP_USLUGI."`.*,`os__our_uslugi`.`name` FROM `".RT_DOP_USLUGI."` 
			LEFT JOIN  `os__our_uslugi` ON  `os__our_uslugi`.`id` = `".RT_DOP_USLUGI."`.`uslugi_id` 
			WHERE `".RT_DOP_USLUGI."`.`dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}



		// выбираем данные о доп услугах для заказа
		public function get_order_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data` 
			global $mysqli;

			$query = "SELECT `".CAB_DOP_USLUGI."`.*,`os__our_uslugi`.`name`
			FROM `".CAB_DOP_USLUGI."` 
			LEFT JOIN  `os__our_uslugi` ON  `os__our_uslugi`.`id` = `".CAB_DOP_USLUGI."`.`uslugi_id` 
			WHERE `".CAB_DOP_USLUGI."`.`dop_row_id` = '".$dop_row_id."'";

			//$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			// $arr[] = $query;
			//echo $query;
			return $arr;
		}

		static function show_order_num($key){
			$i = 6 - strlen($key);
			// echo $i.'    */';
			$str = '';
			for ($t=0; $t < $i ; $t++) { 
				$str .='0';		}
			return $str.$key;
		}

		// выводит имя клиента для запроса в форме редактирования
		protected function get_client_name_Database($id,$no_edit=0){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = '<div'.(($no_edit==0)?' class="attach_the_client"':' class="dop__info"').' data-id="'.$row['id'].'">'.$row['company'].'</div>';
				}
			}else{
				$name = '<div'.(($no_edit==0)?' class="attach_the_client add"':' class="dop__info"').' data-id="0">Прикрепить клиента</div>';
			}
			return $name;
		}

		// выводит имя клиента в заказе, по ссылке в url добавляется id клиента
		protected function get_client_name_link_Database($id){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = 'Клиент не прикреплён';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = '<a data-id="'.$row['id'].'" '.((!isset($_GET['client_id']) || (isset($_GET['client_id']) && $_GET['client_id']!=$row['id']))?'href="'.$this->change_one_get_URL('client_id').$row['id'].'"':'').'>'.$this->str_reduce($row['company'],50).'</a>';
				}
			}
			return $name;
		}

		// обрезаем строку по количеству символов ...
		private function str_reduce($string,$num){
			// $num - ограничение по количеству символов
			if(mb_strlen($string, 'utf-8')>$num){
				// убираем html 
				// $string = strip_tags($str);
				// обрезаем
				$string = mb_substr($string, 0, $num);
				// убедимся, что текст не заканчивается восклицательным знаком, запятой, точкой или тире
				$string = rtrim($string, "!,.-");
				// находим последний пробел, устраняем его и ставим троеточие
				$string = substr($string, 0, strrpos($string, ' '));
				$string = $string.'...';
			}
			return $string;
		}

		// производим подмену одного из значений GET в URL
		private function change_one_get_URL($name){
			$f = 0;
			$str = '';

			foreach ($_GET as $key => $value) {
				if($key!=$name){
					if($f == 0){$str .= '?';}else{$str .= '&';}
					$str .= $key.'='.$value; 
					$f++;
				}
			}
			if($f == 0){$str .= '?';}else{$str .= '&';}
			$str .= $name.'='; 
			return $str;
		}

		

		// получаем имя менеджера
		protected 	function get_manager_name_Database_Html($id,$no_edit=0){
		    global $mysqli;
		    $String = '<span'.(($no_edit==0)?' class="attach_the_manager add"':' class="dop_grey_small_info"').' data-id="0">Прикрепить менеджера</span>';
		   	$arr = array();
		    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $arr[$key] = $val;
				}
		    }		    
		    if(count($arr)){
		    	$String = '<span'.(($no_edit==0)?' class="attach_the_manager"':' class="dop_grey_small_info"').' data-id="'.$arr['id'].'">'.$arr['name'].' '.$arr['last_name'].'</span>';
		    }
		    return $String;
		}


		## Запросы __ запросы к базе
		// фильтрация позиций ЗАПРОСОВ по горизонтальному меню
		private function requests_Template_recuestas_main_rows_Database($id){
						
			// ФИЛЬТРАЦИЯ ПО ВЕРХНЕМУ МЕНЮ 
			switch ($_GET['subsection']) {
				case 'no_worcked_men': // не обработанные
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' ";
					break;
					

				case 'in_work': // в работе у менеджера
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' ";
					break;				

				case 'history':
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` LIKE '%Расчёт от' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
				$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` LIKE '%Расчёт от%')";
					break;
				case 'denied':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` = 'tz_is_not_correct'";
					break;

				case 'paused':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` LIKE '%pause%'";
					break;

				case 'calk_snab':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` LIKE 'calculate_is_ready'";
					break;

				default:
					$where = "WHERE `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".RT_MAIN_ROWS."`.`query_num` = '".$id."' ";
					break;
			}


			global $mysqli;
			$query = "
				SELECT 
					`".RT_DOP_DATA."`.`id` AS `id_dop_data`,
					`".RT_DOP_DATA."`.`quantity`,	
					`".RT_DOP_DATA."`.`price_out`,		
					`".RT_DOP_DATA."`.`print_z`,	
					`".RT_DOP_DATA."`.`zapas`,	
					`".RT_DOP_DATA."`.`status_snab`,	
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".RT_MAIN_ROWS."`.*,
					`".RT_LIST."`.`id` AS `request_id`,
					`".RT_LIST."`.`manager_id`,
					`".RT_LIST."`.`manager_id`,
					`".RT_LIST."`.`client_id`
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					LEFT JOIN `".RT_LIST."` ON `".RT_LIST."`.`id` = `".RT_MAIN_ROWS."`.`query_num`
					".$where."
					ORDER BY `".RT_MAIN_ROWS."`.`type` DESC";
				// echo  $query.'<br><br>';
			$main_rows = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows[] = $row;
				}
			}
			// if($main_rows){ echo $query;}
			return $main_rows;
		}


		// получаем информацию из cab_dop_data
		protected function get_cab_dop_data_position_Database($id){
			global $mysqli;
			$arr = array();
		    $query="SELECT `number_rezerv` FROM `".CAB_ORDER_DOP_DATA."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    $str = '';
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $str = $val;
				}
		    }
		    return $str;
		}

		// детализация позиции по прикреплённым услугам
		protected function get_a_detailed_article_on_the_price_of_positions_AJAX(){
			$html = '';
			 	
			// собираем Object по заказу
			$this->Positions_arr = $this->positions_rows_Database($_POST['order_id']);
			foreach ($this->Positions_arr as $key => $value) {
				$this->Positions_arr[$key]['SERVICES'] = $this->get_order_dop_uslugi($value['id_dop_data']);	 								
			}

			// собираем HTML
			$html .= $this->get_a_detailed_article_on_the_price_of_positions_Html();

			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}

		protected function get_all_services_names_Database(){
			global $mysqli;
			$arr = array();
			$query = "SELECT `id`, `parent_id`, `name`, `type` FROM `".OUR_USLUGI_LIST."`;";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;
				}
			}
			return $arr;
		}

		protected function get_a_detailed_article_on_the_price_of_positions_Html(){
			// получаем название всех услуг
			$this->Services_list = $this->get_all_services_names_Database();


			$html = '';



			// собираем шапку таблицы
			$html .= '<table id="a_detailed_article_on_the_price_of_positions">';
			$html .= '<tr class="no_calc">';
			$html .= '<td></td><td colspan="7">Рассчитанная стоимость заказа</td><td class="postfaktum"></td><td colspan="4" class="postfaktum">Фактическая входящая стоимость</td><td></td>';
			$html .= '</tr>';

			$html .= '<tr class="no_calc">';
				// рассчитано ранее
				$html .= '<th>п</th>';
				$html .= '<th>Артикул/номенклатура</th>';
				$html .= '<th>перечень товаров и услуг</th>';
				$html .= '<th>тираж</th>';
				$html .= '<th>$ входящая</th>';
				$html .= '<th>%</th>';
				$html .= '<th>$ исходящая</th>';
				$html .= '<th>прибыль</th>';
				// то, что получилось по факту
				$html .= '<th class="postfaktum"></th>';
				$html .= '<th class="postfaktum">перечень товаров и услуг</th>';
				$html .= '<th class="postfaktum">тираж</th>';
				$html .= '<th class="postfaktum">$ входащая</th>';
				$html .= '<th class="postfaktum"></th>';
				$html .= '<th>комментарии СНАБ</th>';
			$html .= '</tr>';


			//////////////////////////////////////////////////////////
			//	объявляем переменные для подсчёта итого по заказу   //
			//////////////////////////////////////////////////////////
			$this->GlobItogo_price_in = 0;	// входящая за заказ
			$this->GlobItogo_price_out = 0; // исходящая за заказ
			$this->GlobItogo_price_pribl = 0; // прибыль за заказ
			$this->GlobItogo_price_in_postfaktum = 0;
			$this->GlobAdded_postfactum_class = '';
			//////////////////////////////////
			//	перебор заказа по позициям  //
			//////////////////////////////////
			$this->GlobAdded_postfactum_class  = 'td_shine'; // класс подсветки цен при появлении услуг добавленных в заказ
			foreach ($this->Positions_arr as $key => $position) {
				// считаем тираж для товара по позиции
				$this->PosGenTirage = $position['quantity']+$position['zapas'];


				//////////////////////////////////////////////////////////
				//	объявляем переменные для подсчёта итого по позиции  //
				//////////////////////////////////////////////////////////
				// сразу же записываем в них цены за тираж по товару
				$this->PositionItogo_price_in = $this->PosGenTirage*$position['price_in'];	// входящая  по позиции то, что было рассчитано клиенту
				$this->PositionItogo_price_in_postfaktum = $this->PosGenTirage*$position['price_in'];	// входящая  по позиции по факту то, что получилось
				$this->PositionItogo_price_out = $this->PosGenTirage*$position['price_out']; // исходящая по позиции
				$this->PositionItogo_price_pribl = $this->PositionItogo_price_out - $this->PositionItogo_price_in; // прибыль по позиции
				$this->PositionItogo_price_percent = $this->get_percent_Int($this->PositionItogo_price_in,$this->PositionItogo_price_out);

				// строка стоимости и описания товара
				$html .= '<tr class="tovar_provided" id="tovar_provided_'.($key+1).'">';
					// рассчитано ранее
					$rowspan = count($position['SERVICES'])+1;
					$html .= '<td rowspan="'.$rowspan.'">'.($key+1).'</td>';
					$html .= '<td rowspan="'.$rowspan.'">'.$position['name'].'</td>';
					$html .= '<td>товар</td>';
					$html .= '<td><span>'.$this->PosGenTirage.'</span>шт</td>';
					$html .= '<td><span class="service_price_in">'.$this->PositionItogo_price_in.'</span>р</td>';
					$html .= '<td><span>'.$this->PositionItogo_price_percent.'</span>%</td>';
					$html .= '<td><span class="service_price_out">'.$this->PositionItogo_price_out.'</span>р</td>';
					$html .= '<td><span class="service_price_pribl">'.$this->PositionItogo_price_pribl.'</span>р</td>';
					// то, что получилось по факту
					$html .= '<td class="postfaktum"></td>';
					$html .= '<td class="postfaktum">'.$position['name'].'</td>';
					$html .= '<td class="postfaktum"><span>'.$this->PosGenTirage.'</span>шт</td>';
					$html .= '<td class="postfaktum"><span class="service_price_in_postfactum">'.$this->PositionItogo_price_in.'</span>р</td>';
					$html .= '<td class="postfaktum"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';



				$html_added = ''; // услуги добавленные в заказ
				$added_postfactum_class = 'td_shine'; // класс подсветки цен при появлении услуг добавленных в заказ
				// перебираем прикреплённые услуги
				foreach ($position['SERVICES'] as $count => $service) {
					//////////////////////////////////////////////////////////
					//	объявляем переменные для подсчёта стоимости услуги  //
					//////////////////////////////////////////////////////////
					$this->Service_price_in = ($service['for_how']=='for_one')?$service['price_in']*$service['quantity']:$service['price_in'];// входящая  по услуге то, что было рассчитано клиенту
					$this->Service_price_out = $this->calc_summ_dop_uslug(array($service)); // исходящая по услуге
					$this->Service_price_pribl = $this->Service_price_out - $this->Service_price_in; // прибыль по услуге
					$this->Service_tir = ($service['for_how']=='for_one')?'<span>'.$service['quantity'].'</span>шт':'<span>  -  </span>'; // тираж по услуге
					$this->Service_Name = $this->Services_list[$service['uslugi_id']]['name']; // название услуги
					$this->Service_percent = $this->get_percent_Int($this->Service_price_in,$this->Service_price_out);

					$this->Service_swhitch_On_Of = ((int)$service['on_of'] == 1)?'<span  data-id="'.$service['id'].'" class="on_of">+</span>':'<span  data-id="'.$service['id'].'" class="on_of minus">-</span>';


					switch ((int)$service['author_id_added_services']) {
							case 0: // для услуг добавленных из запроса
								$html .= '<tr class="provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html .= '<td>'.$this->Service_Name.'</td>';
									$html .= '<td>'.$this->Service_tir.'</td>';
									$html .= '<td><span class="service_price_in">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td><span>'.$this->Service_percent.'</span>%</td>';
									$html .= '<td><span class="service_price_out">'.$this->Service_price_out.'</span>р</td>';
									$html .= '<td><span class="service_price_pribl">'.$this->Service_price_pribl.'</span>р</td>';
									// то, что получилось по факту
									$html .= '<td class="postfaktum"></td>';
									$html .= '<td class="postfaktum">'.$this->Service_Name.'</td>';
									$html .= '<td class="postfaktum">'.$this->Service_tir.'</td>';
									$html .= '<td class="postfaktum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html .= '<td></td>';
								$html .= '</tr>';

								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in += $this->Service_price_in;	// входящая  по позиции то, что было рассчитано клиенту
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								break;
							
							default:// если указан id того, кто добавил услугу, то услуга была добавлена в заказ
								$html_added .= '<tr class="not_provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html_added .= '<td></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_in">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_out">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>';
									// то, что получилось по факту
									$html_added .= '<td class="postfaktum"></td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_Name.'</td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_tir.'</td>';
									$html_added .= '<td class="postfaktum added_postfactum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									$html_added .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html_added .= '<td></td>';
								$html_added .= '</tr>';
								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								// добавляем класс подсветки цены
								$this->GlobAdded_postfactum_class = 'added_postfactum_class td_shine';
								$added_postfactum_class = 'added_postfactum_class td_shine';
								
								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								
								break;
						}						
				}
				// добавляем услуги добавленные в заказ
				$html .= $html_added;
				// строка с кнопкой добавления услуги
				// добавляем строку пробел
				$html .= '<tr class="itogo_for_position_probel no_calc">';
					$html .= '<td colspan="8"></td>';
					$html .= '<td colspan="5" class="postfaktum"><input type="button" data-rowspan_id="tovar_provided_'.($key+1).'" data-id_dop_data="'.$position['id_dop_data'].'" class="add_service" name="add_service" value="Добавить"></td>';			
					$html .= '<td colspan="2"></td>';		
				$html .= '</tr>';

				// итого по позиции
				$html .= '<tr class="itogo_for_position">';
					$html .= '<td></td>';
					$html .= '<td>Итого по позиции</td>';
					$html .= '<td></td>';
					$html .= '<td></td>';
					// $ входащая итого
					$html .= '<td class="'.$added_postfactum_class.'"><span class="position_price_in">'.$this->PositionItogo_price_in.'</span>р</td>';

					$html .= '<td></td>';
					// исходящая итого
					$html .= '<td><span  class="position_price_out">'.$this->PositionItogo_price_out.'</span>р</td>';
					// прибыль итого
					$html .= '<td class="'.$added_postfactum_class.'"><span class="position_price_pribl">'.$this->PositionItogo_price_pribl.'</span>р</td>';
					$html .= '<td colspan="3"  style="background-color:#C7C7C7;text-align:right;
"></td>';
					// заплатили по факту //// фходащая по факту
					$html .= '<td style="background-color:#C7C7C7;
"><span class="'.$added_postfactum_class.'"><span  class="position_price_in_postfaktum">'.$this->PositionItogo_price_in_postfaktum.'</span>р</span></td>';
					$html .= '<td style="background-color:#C7C7C7;text-align:right;
"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';

				//////////////////////////
				//	обсчитываем ИТОГО за заказ
				//////////////////////////
				$this->GlobItogo_price_in += $this->PositionItogo_price_in;	// входящая за заказ
				$this->GlobItogo_price_out += $this->PositionItogo_price_out; // исходящая за заказ
				$this->GlobItogo_price_pribl += $this->PositionItogo_price_pribl; // прибыль за заказ
				$this->GlobItogo_price_in_postfaktum += $this->PositionItogo_price_in_postfaktum; // входящая по факту
			}

			// если имеем разницу в постфактум выводим её
			$this->GlobItogo_price_in_difference = $this->GlobItogo_price_in-$this->GlobItogo_price_in_postfaktum;
			// собираем html разницы фактической и предусмотренной в расчёте стоимости
			$this->GlobItogo_price_in_difference_Html = ($this->GlobItogo_price_in_difference!=0)?'<span>'.$this->GlobItogo_price_in_difference.'</span>р':'';

			// добавляем строку пробел
			$html .= '<tr class="itogo_for_position_probel no_calc">';
				$html .= '<td colspan="15"></td>';					
			$html .= '</tr>';
			// добавляем ИТОГО по заказу
			$html .= '<tr class="itogo_for_position no_calc" id="itogo_order">';
					$html .= '<td></td>';
					$html .= '<td>Итого по заказу</td>';
					$html .= '<td></td>';
					$html .= '<td></td>';
					// $ входащая итого
					$html .= '<td class="'.$this->GlobAdded_postfactum_class.'"><span class="order_price_in">'.$this->GlobItogo_price_in.'</span>р</td>';

					$html .= '<td></td>';
					// исходящая итого
					$html .= '<td><span  class="order_price_out">'.$this->GlobItogo_price_out.'</span>р</td>';
					// прибыль итого
					$html .= '<td class="'.$this->GlobAdded_postfactum_class.'"><span  class="order_price_pribl">'.$this->GlobItogo_price_pribl.'</span>р<div class="minus">'.$this->GlobItogo_price_in_difference_Html.'</div></td>';
					$html .= '<td colspan="3"  style="background-color:#B1C370;text-align:right;
"></td>';
					// заплатили по факту //// фходащая по факту
					$html .= '<td style="background-color:#B1C370;
"><span class="'.$this->GlobAdded_postfactum_class.'"><span  class="order_price_in_postfactum">'.$this->GlobItogo_price_in_postfaktum.'</span>р</span></td>';
					$html .= '<td style="background-color:#B1C370;text-align:right;
"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';

			$html .= '<table>';

			// ob_start();
			// echo '<pre>';
			// print_r($this->Positions_arr);
			// echo '</pre>';
			    	
			// $content = ob_get_contents();
			// ob_get_clean();
			// $html .=$content;
			return $html;
		}

		// подсчёт процентов наценки
		protected function get_percent_Int($price_in,$price_out){
			$per = ($price_in!= 0)?$price_in:0.09;
			$percent = round((($price_out-$price_in)*100/$per),2);
			return $percent;
		}

		// запрос строк позиций из базы
		protected function positions_rows_Database($order_id){
			$arr = array();
			global $mysqli;
			$query = "SELECT *, `".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data` 
			FROM `".CAB_ORDER_DOP_DATA."` 
			INNER JOIN ".CAB_ORDER_MAIN." ON `".CAB_ORDER_MAIN."`.`id` = `".CAB_ORDER_DOP_DATA."`.`row_id` 
			WHERE `".CAB_ORDER_MAIN."`.`order_num` = '".$order_id."'";
			// $query = "SELECT * FROM ".CAB_ORDER_MAIN." WHERE `order_num` = '".$order_id."'";
			//echo $query.'<br>';
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}






   	}