<?php
    class Cabinet{
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

    	function __consturct(){
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
		public function calc_summ_dop_uslug($arr,$tir){
			$summ = 0;

			foreach ($arr as $key => $value) {
				//echo $value['dop_row_id'].'  |  '.$value['glob_type'].'  |  '.$value['type'].'  |  '.$value['for_how'].' - ';
				if($value['for_how']=="for_one"){
					//echo ''.$value['price_out'].' * '.$tir.' + '.$summ.' = '.($summ+$value['price_out']*$tir).'<br>';
					$summ += ($value['price_out']*$tir);					
				}else{
					//echo ''.$value['price_out'].' + '.$summ.'= '.($summ+$value['price_out']).'<br>';
					$summ += $value['price_out'];					
				}
				
			}
			// echo $summ.'<br>';
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


   	}