<?php
	
	class Cabinet_admin_class extends Cabinet{

		// расшифровка меню СНАБ
		public $menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked_snab' => 'Не обработанные СНАБ',		
		'no_worcked_men' => 'Не обработанные МЕН',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'ТЗ не корректно',
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'tz_no_correct' => 'ТЗ не корректно',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'В производстве',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'на паузе',
		'history' => 'история',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'issue'=>'Вопрос',
		'not accepted' => 'Не принято',
		'for_shipping' => 'На отгрузку',
		'my_orders_diz' => 'Мои заказы дизайн',
		'all_orders_diz' => 'Все заказы дизайн',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные'													
		); 


		// название подраздела кабинета
		private $sub_subsection;

		// содержит экземпляр класса кабинета вер. 1.0
		private $CABINET;

		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		public $POSITION_NO_CATALOG;

		function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =)) 

			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $user_access;

			//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; left:0">this->Cabinet_snab_class </div>';
			
			// экземпляр класса продукции НЕ каталог
			$this->POSITION_NO_CATALOG = new Position_no_catalog();


			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}

			// экземпляр класса кабинета вер. 1.0
			$this->CABINET = new Cabinet;

			//$this->FORM = new Forms;
		}


		private function _AJAX_($name){
			$method_AJAX = $name.'_AJAX';
			// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
			if(method_exists($this, $method_AJAX)){
				$this->$method_AJAX();
				exit;
			}					
		}

		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();
				
			}else{
				echo 'метод '.$method_template.' не предусмотрен';
			}

		}



		############################################
		###				AJAX START               ###
		############################################

		private function replace_query_row_AJAX(){
			$method = $_GET['section'].'_Template';
			// echo $method;
			// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
			if(method_exists($this, $method)){
				echo '{"response":"OK","html":"'.base64_encode($this->$method($_POST['os__rt_list_id'])).'"}';
				exit;
			}							
		}

		private function get_dop_tex_info_AJAX(){
			// подгружаем комментарии для позиции 
			global $PositionComments;
			$html = $PositionComments -> get_comment_for_position_without_Out();

			// Вывод
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}

		
		############################################
		###				AJAX END                 ###
		############################################







		#############################################################
		##                          START                          ##
		##      методы для работы с поддиректориями subsection     ##
		#############################################################

		##########################################
		################ Важно
		private function important_Template(){
			echo 'Раздел в разработке =)';
		}		
		################ Важно_END
		##########################################


		##########################################
		################ Запросы
		private function requests_Template($id_row = 0){
		 	// для обсчёта суммы за тираж			
			
			include_once ('./libs/php/classes/rt_class.php');

			include_once ('./libs/php/classes/comments_class.php');

			$array_request = array();
			global $mysqli;
	
			$query = "SELECT 
				`".RT_LIST."`.*, 
				(UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)-UNIX_TIMESTAMP())*(-1) AS `time_attach_manager_sec`,
				SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)) AS `time_attach_manager`,
				
				DATE_FORMAT(`".RT_LIST."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".RT_LIST."`";
			
			if($id_row==1){
				$query .=" WHERE `".RT_LIST."`.`id` = '".$id_row."'";
			}else{				
				/////////////////////////
				// фильтрация по статусам запросов
				/////////////////////////
				// 
				// статусы могут быть трёх (3) типов:
				// not_process - не обработанные:
				// 		те, что приходят от клиентов через корзину, и прикрепляются к тому или иному менеджеру
				// in_work - в работе
				// 		те, что менеджер завёл сам или взял из необработанных, которые в свою очередь ему отдал админ 
				// history - история
				//  	сюда попадают все запросы после того как из запроса создана спецификация и сгенерирован предзаказ
				//
				//////////////////////////
				//	в последствии:
				// 1 - необходимо запретить рт для запросов попавших в историю
				// 2 - необходимо сделать возможность копирования исторического запроса из истории в работу, при этом цены на услуги вероятно есть смысл пересчитать по новой
				//////////////////////////
				// делаем фильтрацию в зависимости от того по какому фильтру мы собираемся выбирать выдачу
				
				switch ($_GET['subsection']) {
					case 'history':
						$query .= " WHERE `".RT_LIST."`.`status` = 'history'";
						break;
					case 'no_worcked_men':
						$query .= " WHERE `".RT_LIST."`.`status` = 'not_process' OR `".RT_LIST."`.`status` = 'new_query'";
						break;

					case 'in_work':
						$query .= " WHERE `".RT_LIST."`.`status` = 'in_work' ";
						break;
					default:
						break;
				}
			}

			

			// массви с переводом статусов запроса
			$name_cirillic_status['new_query'] = 'новый запрос'; 
			$name_cirillic_status['not_process'] = 'не обработан менеджером'; 
			$name_cirillic_status['taken_into_operation'] = 'взят в обработку';
			$name_cirillic_status['in_work'] = 'в работе';
			$name_cirillic_status['history'] = 'история';

			$query .= ' ORDER BY `id` DESC';
			$result = $mysqli->query($query) or die($mysqli->error);
			$zapros = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$zapros[] = $row;
				}
			}
			// echo '<pre>';
			// print_r($zapros);
			// echo '</pre>';
				
			$general_tbl_row = '';
			// собираем html строк-запросов 
			$html = '';
			foreach ($zapros as $key => $value) {
				// получаем позиции по запросу
				$main_rows = $this->requests_Template_recuestas_main_rows_Database($value['query_num']);
				// echo '<pre>';
				// print_r($main_rows);
				// echo '</pre>';
					
				if(empty($main_rows)){ continue;}


				// в эту переменную запишется 0 если при переборе вариантов 
				// не встретится ни одного некаталожного товара
				// потом проверим и если все товары в запросе каталожные вывод данного запроса отменяем
				$enabled_echo_this_query = 0;

				
				// наименование продукта
				$name_product = ''; $name_count = 1;
				
				$variant_row = '';

				// счетчик кнопок показа каталожных позиций
				// необходим для ограничения до одной кнопки
				$count_button_show_catalog_variants=0;

				// перебор вариантов
				foreach ($main_rows as $key1 => $val1) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $this-> get_query_dop_uslugi($val1['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $this->get_dop_uslugi_no_print_type($dop_usl);

					
					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $this-> calc_summ_dop_uslug($dop_usl_print,$val1['quantity']);
					
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $this-> calc_summ_dop_uslug($dop_usl_no_print,$val1['quantity']);
					
					// стоимость товара для варианта
					$price_out = $val1['price_out'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;
					
					//////////////////////////
					//	собираем строки вариантов по каждой позиции
					//////////////////////////
					if($name_product != $val1['name']){$name_product = $val1['name']; $name_count = 1;}
					$variant_row .= '<tr data-id_dop_data="'.$val1['id_dop_data'].'" class="'.$val1['type'].'_1">
						<td>'.$val1['art'].'</td>
						<td><a class="go_to_position_card_link" href="./?page=client_folder&section=rt_position&id='.$val1['id'].'">'.$val1['name'].'</a> <span class="variant_comments_dop">( Вариант '.$name_count++.' )</span></td>
						<td>'.$val1['quantity'].'</td>
						<td></td>
						<td>'.$price_out.'</td>
						<td>'.$calc_summ_dop_uslug.'</td>
						<td>'.$calc_summ_dop_uslug2.'</td>
						<td>'.$in_out.'</td>
						<td></td>
						<td data-type="'.$val1['type'].'" data-status="'.$val1['status_snab'].'" class="'.$val1['status_snab'].'_'.$this->user_access.' '.$value['status'].'_status_snab_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($val1['status_snab']).'</td>
					</tr>';
					
				}

				//////////////////////////
				//	собираем строку с номером заказа (шапку заказа)
				//////////////////////////
				switch ($value['status']) {
					case 'new_query':
						$status_or_button = '<div class="give_to_all">отдать свободному</div>';
						break;
					default:
						$status_or_button = $name_cirillic_status[$value['status']];
						break;
				}
				$overdue = (($value['time_attach_manager_sec']*(-1)>18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				$general_tbl_row_body ='<td class="show_hide" rowspan="'.$rowspan.'"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&client_id='.$value['client_id'].'&query_num='.$value['query_num'].'">'.$value['query_num'].'</a> </td>
							<td><span data-sec="'.$value['time_attach_manager_sec']*(-1).'" '.$overdue.'>'.$value['time_attach_manager'].'</span>'.$this->get_manager_name_Database_Html($value['manager_id']).'</td>
							<td>'.$value['create_time'].'</td>
							<td><span data-rt_list_query_num="'.$value['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($value['query_num']).'"></span></td>
							<td>'.$this->get_client_name_Database($value['client_id']).'</td>
							<td>'.RT::calcualte_query_summ($value['query_num']).'</td>
							<td class="'.$value['status'].'_'.$this->user_access.'">'.$status_or_button.'</td>';
				
				// если запрос по строке, возвращаем строку
				if($id_row!=0){return $general_tbl_row_body;}

				$general_tbl_row .= '<tr data-id="'.$value['id'].'" id="rt_list_id_'.$value['id'].'">
									'.$general_tbl_row_body.'
									</tr>';
				
				$general_tbl_row .= '<tr class="query_detail">';
					//$general_tbl_row .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
					$general_tbl_row .= '<td colspan="7" class="each_art">';

					// шапка таблицы вариантов запроса
					$variant_top = '<table class="cab_position_div">
						<tr>
							<th>артикул</th>
							<th>номенклатура</th>
							<th>тираж</th>
							<th>цены:</th>
							<th>товар</th>
							<th>печать</th>
							<th>доп. услуги</th>
							<th>в общем</th>
							<th></th>
							<th></th>
						</tr>';


					// прикручиваем найденные варианты
					$general_tbl_row .=	$variant_top.$variant_row;
					// закрываем теги
					$general_tbl_row .= '</table>';
					$general_tbl_row .= '</td>';
				$general_tbl_row .= '</tr>';
			}
			
			//////////////////////////
			//	собираем шапку главной таблицы в окне
			//////////////////////////
			$general_tbl_top = '
			<table class="cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Номер</th>
								<th>отдан менеджеру</th>
								<th>запрос от клиента</th>
								<th>Коммент</th>
								<th>Компания</th>
								<th>Сумма</th>
								<th>Статус</th>
							</tr>';
			// Закрывающий тег главной таблицы
			$general_tbl_bottm = '</table>';

			// собраем воедино контент с главной таблицей
			$html = $general_tbl_top.$general_tbl_row.$general_tbl_bottm;

			// выводим
			echo $html;
		}
		## Запросы __ запросы к базе
		// получаем позиции по запросу
		private function requests_Template_recuestas_main_rows_Database($id){
			// ФИЛЬТРАЦИЯ ПО ВЕРХНЕМУ МЕНЮ 
			switch ($_GET['subsection']) {
				case 'all':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' ";
					break;
				case 'no_worcked_snab':
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab')";
					break;
				case 'history':
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` LIKE '%Расчёт от' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
					// $where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` LIKE '%Расчёт от%')";
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."'";
					break;
				case 'in_work':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` = 'on_calculation'";
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
					`".RT_MAIN_ROWS."`.*,
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".RT_LIST."`.`id` AS `request_id` 
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					LEFT JOIN `".RT_LIST."` ON `".RT_LIST."`.`id` = `".RT_MAIN_ROWS."`.`query_num`
					".$where."
					ORDER BY `".RT_MAIN_ROWS."`.`type` DESC";

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
		################ Запросы __ END
		##########################################


		##########################################
		## Предзаказ
		private function paperwork_Template($id_row=0){

			global $mysqli;
			
			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".CAB_ORDER_ROWS."`";
			
			if($id_row){
				$query .=" WHERE `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
			}else{
				$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'being_prepared' OR `".CAB_ORDER_ROWS."`.`global_status` = 'requeried_expense'";
			}
			

			$query .= ' ORDER BY `id` DESC';
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			
			
			// собираем html строк-запросов
			$html1 = '';
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				//if(!isset($value2)){continue;} // !!!!!!!!!!!!!!!!!
				$order_num_1 = Cabinet::show_order_num($value['order_num']);
				$invoice_num = $value['invoice_num'];


				$query = "
				SELECT 
					`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
					`".CAB_ORDER_DOP_DATA."`.`quantity`,	
					`".CAB_ORDER_DOP_DATA."`.`price_out`,	
					`".CAB_ORDER_DOP_DATA."`.`print_z`,	
					`".CAB_ORDER_DOP_DATA."`.`zapas`,	
					DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".CAB_ORDER_MAIN."`.*,
					`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
					`".CAB_ORDER_ROWS."`.`global_status`,
					`".CAB_ORDER_ROWS."`.`payment_status`,
					`".CAB_ORDER_ROWS."`.`number_pyament_list`
					FROM `".CAB_ORDER_MAIN."` 
					INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
					LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$value['id']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";

				$main_rows = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				$main_rows_id = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$main_rows[] = $row;
					}
				}

				// СОБИРАЕМ ТАБЛИЦУ
				###############################
				// строка с артикулами START
				###############################
				$html = '<tr class="query_detail">';
				//$html .= '<td class="show_hide"><span class="this->cabinett_row_hide"></span></td>';
				$html .= '<td colspan="11" class="each_art">';
				
				
				// ВЫВОД позиций
				$html .= '<table class="cab_position_div">';
				
				// шапка таблицы позиций заказа
				$html .= '<tr>
						<th>артикул</th>
						<th>номенклатура</th>
						<th>тираж</th>
						<th>цены:</th>
						<th>товар</th>
						<th>печать</th>
						<th>доп. услуги</th>
					<th>в общем</th>
					<th></th>
					<th></th>
						</tr>';


				$in_out_summ = 0; // общая стоимость заказа
				foreach ($main_rows as $key1 => $val1) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $this-> get_order_dop_uslugi($val1['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $this-> get_dop_uslugi_no_print_type($dop_usl);

					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $this-> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $this-> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость товара для варианта
					$price_out = $val1['price_out'] * $val1['quantity'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

					$html .= '<tr  data-id="'.$value['id'].'">
					<td> '.$val1['id_dop_data'].'<!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
					<td>'.$val1['name'].'</td>
					<td>'.($val1['quantity']+$val1['zapas']).'</td>
					<td></td>
					<td><span>'.$price_out.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug.'</span> р.</td>
					<td><span>'.$calc_summ_dop_uslug2.'</span> р.</td>
					<td><span>'.$in_out.'</span> р.</td>
					<td></td>
					<td></td>
							</tr>';
					$in_out_summ +=$in_out; // прибавим к общей стоимости
				}

				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = ($in_out_summ!=0)?round($value['payment_status']*100/$in_out_summ,2):'0.00';		
				// собираем строку заказа
				
				$html2 = '<tr data-id="'.$value['id'].'" >';
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				//'.$this->get_manager_name_Database_Html($value['manager_id']).'
				$html2_body = '<td class="show_hide" rowspan="'.$rowspan.'"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
							<td>'.$value['create_time'].'<br>'.$this->get_manager_name_Database_Html($value['manager_id'],1).'</td>
							<td>'.$this->get_client_name_Database($value['client_id'],1).'</td>
							<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span edit_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
							<td><span>'.$in_out_summ.'</span> р.</td>
							<td class="buch_status_select">'.$this->select_status($value['buch_status'],$this->buch_status).'</td>
							<td class="select_global_status">'.$this->select_status($value['global_status'],$this->order_status).'</td>';
				$html3 = '</tr>';

				$html1 .= $html2 .$html2_body.$html3. $html;
				// запрос по одной строке без подробностей
				if($id_row){return $html2_body;}
			}

			


			echo '
			<table class="cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Номер</th>
								<th>Дата/время заведения</th>
								<th>Компания</th>						
								<th class="invoice_num">Счёт</th>
								<th>Дата опл-ты</th>
								<th>№ платёжки</th>
								<th>% оплаты</th>
								<th>Оплачено</th>
								<th>стоимость заказа</th>
								<th>стутус БУХ</th>
								<th>Статус заказа.</th>
							</tr>';
			echo $html1;
			echo '</table>';
		}
		################ Предзаказ __ END



		##########################################
		## Заказы
		Private Function orders_Template($id_row=0){
			$where = 0;
			$html = '';
			$table_head_html = '
				<table id="general_panel_orders_tbl">
				<tr>
					<th colspan="3">Артикул/номенклатура/печать</th>
					<th>тираж<br>запас</th>
					<th>поставщик товара и резерв</th>
					<th>подрядчик печати</th>
					<th>сумма</th>
					<th>тех + доп инфо</th>
					<th>дата утв. макета</th>
					<th>срок ДС</th>
					<th>дата сдачи</th>
					<th></th>
					<th>статус</th>
				</tr>
			';

			global $mysqli;

			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".CAB_ORDER_ROWS."`";
			
			if($id_row){
				$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
				$where = 1;
			}else{
				// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = ''";
			}

			if(isset($_GET['client_id'])){
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
				$where = 1;
			}
			
			$query .= ' ORDER BY `id` DESC';
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			$table_order_row = '';		
			// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
			// error_reporting(E_ALL);
			//include '../os_form_class.php';
			// создаем экземпляр класса форм
			$this->FORM = new Forms();
			foreach ($main_rows_id as $key => $value) {
				// запоминаем обрабатываемые номеразаказа и запроса
				// номер запроса
				$this->query_num = $value['query_num'];
				// номер заказа
				$this->order_num = $value['order_num'];
				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($value['order_num']);

				// запрашиваем информацию по позициям
				$positions_arr = $this->table_order_positions_rows_Html($value);
				$table_order_positions_rows = $positions_arr['html'];
				
				// формируем строку с информацией о заказе
				$table_order_row .= '
					<tr class="order_head_row">
						<td class="show_hide" rowspan="'.$positions_arr['rowspan'].'"><span class="cabinett_row_hide_orders"></span></td>
						<td colspan="4" class="orders_info">
							<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$value['client_id'].'&query_num='.$value['query_num'].'" target="_blank" class="greyText">'.$value['query_num'].'</a>)</span>
							'.$this->get_client_name_link_Database($value['client_id']).'
							<span class="greyText">счёт№:'.$value['number_pyament_list'].'</span>
						</td>
						<td>
							<!--// комментарии -->
							<span data-cab_list_order_num="'.$value['order_num'].'" data-cab_list_query_num="'.$value['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($value['order_num']).'"></span>	
						</td>
						<td><span class="show_the_full_information">'.$value['payment_status'].'</span> р.</td>
						<td colspan="2">
							<span class="greyText">оплачен: </span>'.$value['payment_date'].'
							<span class="greyText">в размере: </span> '.$value['payment_status'].'
						</td>
						<td>???</td>
						<td>???</td>
						<td><span class="greyText">заказа: </span></td>
						<td>'.$this->order_status[$value['global_status']].'</td>
					</tr>';
				// включаем вывод позиций 
				$table_order_row .= $table_order_positions_rows;
			}

			

			$html = $table_head_html.$table_order_row.'</table>';
			echo $html;
		}
		


		// возвращает html строки позиций
		private function table_order_positions_rows_Html($order_arr){			
			$positions_rows = $this->positions_rows_Database($order_arr['id']);
			$html = '';
			// echo '<pre>';
			// print_r($positions_rows);
			// echo '</pre>';
			
			$n = 1;
			// формируем строки позиций			
			foreach ($positions_rows as $key => $value) {
				$this->position_item = $n;
				$html .= '<tr class="positions_rows row__'.$n.'" data-id="'.$value['id'].'">';
				// порядковый номер позиции в заказе
				$html .= '<td><span class="orders_info_punct">'.$n++.'п</span></td>';
				// описание позиции
				$html .= '<td>';
				// комментарии
				//$html .= '<span data-cab_list_order_num="'.$order_arr['order_num'].'" data-cab_list_query_num="'.$order_arr['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($value['order_num']).'"></span>';	
				// наименование товара
				$html .= '<span class="art_and_name">'.$value['art'].'  '.$value['name'].'</span>';
					
				// добавляем доп описание
				// для каталога и НЕкаталога способы хранения и получения данной информации различны
				if(trim($value['type'])!='cat' && trim($value['type'])!=''){
					// доп инфо по некаталогу берём из json 
					$html .= $this->decode_json_no_cat_to_html($value);
				}else if(trim($value['type'])!=''){
					// доп инфо по каталогу из услуг..... НУЖНО РЕАЛИЗОВЫВАТЬ
					$html .= '';
				}


				$html .= '</td>';
				// тираж, запас, печатать/непечатать запас
				$html .= '<td>';
				$html .= '<div class="quantity">'.$value['quantity'].'</div>';
				$html .= '<div class="zapas">'.(($value['zapas']!=0 && trim($value['zapas'])!='')?'+'.$value['zapas']:'').'</div>';
				$html .= '<div class="print_z">'.(($value['zapas']!=0 && trim($value['zapas'])!='')?(($value['print_z']==0)?'НПЗ':'ПЗ'):'').'</div>';
				$html .= '</td>';
				
				// поставщик товара и номер резерва для каталожной продукции 
				$html .= '<td>
						<div class="supplier">'.$this->get_supplier_name($value['art']).'</div>
						<div class="number_rezerv">'.$value['number_rezerv'].'</div>
						</td>';
				// подрядчк печати 
				// что если их несколько????? где мы их указываем ???? 
				$html .= '<td>что если их несколько????? где мы их указываем ???? </td>';
				// сумма за позицию включая стоимость услуг ???!!!
				$html .= '<td></td>';
				// всплывающее окно тех и доп инфо
				// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
				// думаю есть смысл хранения в json 
				// обязательные поля:
				// {"comments":" ","technical_info":" ","maket":" "}
				$html .= $this->grt_dop_teh_info($value);
				
				// дата утверждения макета
				// где, когда и кто её проставляет, и кто и когда это может исправить???? 
				$html .= '<td></td>';
				// срок ДС --- что тут должно быть????
				$html .= '<td>что тут должно быть????</td>';
				// дата сдачи
				// где, когда и кто её проставляет, и кто и когда это может исправить???? 
				// или откуда она вычисляется.... ведь её не может не быть
				$html .= '<td>08.09.2015</td>';

				// получаем статусы участников заказа в две колонки: отдел - статус
				$html .= $this->position_status_list_Html($value);
				$html .= '</tr>';			
			}		

			$arr['html'] = $html;
			$arr['rowspan'] = $n;	
			return $arr;
		}

		// всплывающее окно тех и доп инфо
		private function grt_dop_teh_info($value){
			// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
			// думаю есть смысл хранения в json 
			// обязательные поля:
			// {"comments":" ","technical_info":" ","maket":" "}

			// если есть информация
			$no_empty_class = (trim($value['dop_teh_info'])!='')?' no_empty':'';

			$html = '<td>
					<div class="dop_teh_info '.$no_empty_class.'" data-id="'.$value['id'].'" data-query_num="'.$this->query_num.'" data-position_item="'.$this->position_item.'" data-order_num="'.$this->order_num.'" data-order_num_User="'.$this->order_num_for_User.'"  >доп/тех инфо</div>
					<div class="dop_teh_info_window_content"></div>
				</td>';

			return $html;
		}
		
		// декодируем поле json для некаталога в читабельный вид
		private function decode_json_no_cat_to_html($arr){
			// список разрешённых для вывода в письмо полей
			$send_info_enabled= array('format'=>1,'material'=>1,'plotnost'=>1,'type_print'=>1,'change_list'=>1,'laminat'=>1);


			
			// получаем json с описанием продукта
			$dop_info_no_cat = ($arr['no_cat_json']!='')?json_decode($arr['no_cat_json']):array();
			
			
			$html = '';
			// если у нас есть описание заявленного типа товара
			if(isset($this->FORM->form_type[$arr['type']])){
				$names = $this->FORM->form_type[$arr['type']]; // массив описания хранится в классе форм
				$html .= '<div class="get_top_funcional_byttun_for_user_Html table">';
				foreach ($dop_info_no_cat as $key => $value) {
					if(!isset($send_info_enabled[$key])){continue;}
					$html .= '
						<div class="row">
							<div class="cell" >'.$names[$key]['name'].'</div>
							<div class="cell">'.$value.'</div>
						</div>
					';
				}
				$html .= '</div>';
				// echo '<pre>';
				// print_r($arr);
				// echo '</pre>';
				return $html;
			}else{// в случае исключения выводим массив, дабы было видно куда копать
				echo '<pre>';
				print_r($arr);
				echo '</pre>';
			}
		} 


		// вывод описания по позиции НЕ_каталог
		private function get_dop_information_text_cat_Html($position){
			// echo '<pre>';
			// print_r($position);
			// echo '</pre>';
				
		}
		// статусы позиций
		private function position_status_list_Html($cab_order_main_row){
			$status_list = array();
			// снабжение
			if(trim($cab_order_main_row['status_snab'])!=''){
				$status_list['снабжение'] = $cab_order_main_row['status_snab'];	
			}
			// склад
			if(trim($cab_order_main_row['status_sklad'])!=''){
				$status_list['склад'] = $cab_order_main_row['status_sklad'];	
			}

			$html1 = '<td>';
			$html2 = '<td>';
			foreach ($status_list as $key => $value) {
				$html1 .= '<div class="otdel_name">'.$key.'</div>';
				$html2 .= '<div class="otdel_status">'.$value.'</div>';
			}
			$html1 .= '</td>';
			$html2 .= '</td>';	

			return $html1.$html2;
		}
		// запрос строк позиций из базы
		private function positions_rows_Database($order_id){
			$arr = array();
			global $mysqli;
			$query = "SELECT *, `".CAB_ORDER_DOP_DATA."`.`id` AS `dop_data_id` 
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


		## Заказы __ END
		##########################################



		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################




		#################################################
		##                   START                     ##
		##      методы для работы с базой данных       ##
		#################################################

		function get_all_orders_Database_Array(){
			global $mysqli;
			$arr = array();
			$query = '';

		}

		#################################################
		##      методы для работы с базой данных       ##
		##                    END                      ##
		#################################################
		
		//////////////////////////
		//	service method
		//////////////////////////
		private function show_cirilic_name_status_snab($status_snab){
			if(substr_count($status_snab, '_pause')){
				$status_snab = 'На паузе';
			}
			// echo '<pre>';
			// print_r($this->POSITION_NO_CATALOG->status_snab);
			// echo '</pre>';
						
			if(isset($this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'])){
				$status_snab = $this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'];
			}else{
				$status_snab;
			}
			return $status_snab;
		}

		

		//////////////////////////
		//	оборачивает в оболочку warning_message
		//////////////////////////
		private function wrap_text_in_warning_message($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}



		//////////////////////////
		//	комментарии к запросу
		//////////////////////////
		private function get_comment_for_query_Database(){
			global $mysqli;
			$query = "";
		}





		function __destruct(){}
	}


?>