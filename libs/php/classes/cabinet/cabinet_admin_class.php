<?php
	
	class Cabinet_admin_class extends Cabinet{
		// разрешить показ сообщений
		// private $allow_messages = false;


		// расшифровка меню СНАБ
		public $menu_name_arr = array(
			'important' => 'Важно',
			'in_processed'=>'обрабатывается',
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


		
		############################################
		###				AJAX END                 ###
		############################################




		#############################################################
		##                          START                          ##
		##      методы для работы с поддиректориями subsection     ##
		#############################################################

		
		//////////////////////////
		//	Section - Важно
		//////////////////////////
		private function important_Template(){
			echo 'Раздел в разработке =)';
		}		
		

		//////////////////////////
		//	Section - Запросы
		//////////////////////////
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

			$query .= ' ORDER BY `id` DESC';
			$result = $mysqli->query($query) or die($mysqli->error);
			$zapros_arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$zapros_arr[] = $row;
				}
			}
			// echo '<pre>';
			// print_r($zapros);
			// echo '</pre>';
				
			$general_tbl_row = '';
			// собираем html строк-запросов 
			$html = '';
			foreach ($zapros_arr as $zapros) {
				// получаем позиции по запросу
				$positions_arr = $this->get_position_arr_Database($zapros['query_num']);
				
				/*
					в эту переменную запишется 0 если при переборе вариантов 
					не встретится ни одного некаталожного товара
					потом проверим и если все товары в запросе каталожные вывод данного запроса отменяем
				*/
				$enabled_echo_this_query = 0;

				
				// наименование продукта
				$name_product = ''; 
				// порядковый номер варианта расчёта одного и того же продукта
				$name_count = 1;
				
				// Html строки вариантов 
				$variant_row = '';

				// счетчик кнопок показа каталожных позиций
				// необходим для ограничения до одной кнопки
				$count_button_show_catalog_variants=0;

				// перебор вариантов
				foreach ($positions_arr as $position) {
					////////////////////////////////////
					//	Расчёт стоимости позиций START  
					////////////////////////////////////
					/*
						!!!!!!!!    ОПИСАНИЕ    !!!!!!!!!

						стоимость товара
						$this->Price_for_the_goods;
						стоимость услуг печати
						$this->Price_of_printing;
						стоимость услуг не относящихся к печати
						$this->Price_of_no_printing;
						общаяя цена позиции включает в себя стоимость услуг и товара
						$this->Price_for_the_position;
					*/
					$this->GET_PRICE_for_position($position);				
					
					////////////////////////////////////
					//	Расчёт стоимости позиций END
					////////////////////////////////////
					
					
					//////////////////////////
					//	собираем строки вариантов по каждой позиции
					//////////////////////////
					// 
					if($name_product != $position['name']){$name_product = $position['name']; $name_count = 1;}
					$variant_row .= '<tr data-id_dop_data="'.$position['id_dop_data'].'" class="'.$position['type'].'_1">
						<td>'.$position['art'].'</td>
						<td><a class="go_to_position_card_link" href="./?page=client_folder&section=rt_position&id='.$position['id'].'">'.$position['name'].'</a> <span class="variant_comments_dop">( Вариант '.$name_count++.' )</span></td>
						<td>'.$position['quantity'].'</td>
						<td></td>
						<td>'.$this->Price_for_the_goods.'</td>
						<td>'.$this->Price_of_printing.'</td>
						<td>'.$this->Price_of_no_printing.'</td>
						<td>'.$this->Price_for_the_position.'</td>
						<td></td>
						<td data-type="'.$position['type'].'" data-status="'.$position['status_snab'].'" class="'.$position['status_snab'].'_'.$this->user_access.' '.$zapros['status'].'_status_snab_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($position['status_snab']).'</td>
					</tr>';
				}

				//////////////////////////
				//	собираем строку с номером запроса (шапку заказа)
				//////////////////////////
				switch ($zapros['status']) {
					case 'new_query':
						$status_or_button = '<div class="give_to_all">отдать свободному</div>';
						break;
					default:
						####
						# $this->name_cirillic_status  -  содержится в родительском классе
						###
						$status_or_button = (isset($this->name_cirillic_status[$zapros['status']])?$this->name_cirillic_status[$zapros['status']]:'статус не предусмотрен!!!!'.$zapros['status']);
						break;
				}

				// выделяем красным текстом если менеджер не взял запрос в обработку в течение 5 часов
				$overdue = (($zapros['time_attach_manager_sec']*(-1)>18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
				// если в массиве $_POST содержится значение, значит мы запрашиваем только одну строку и подставляем значение из массива
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				// собираем строку запроса
				$general_tbl_row_body ='<td class="show_hide" rowspan="'.$rowspan.'"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&client_id='.$zapros['client_id'].'&query_num='.$zapros['query_num'].'">'.$zapros['query_num'].'</a> </td>
							<td><span data-sec="'.$zapros['time_attach_manager_sec']*(-1).'" '.$overdue.'>'.$zapros['time_attach_manager'].'</span>'.$this->get_manager_name_Database_Html($zapros['manager_id']).'</td>
							<td>'.$zapros['create_time'].'</td>
							<td><span data-rt_list_query_num="'.$zapros['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($zapros['query_num']).'"></span></td>
							<td>'.$this->get_client_name_Database($zapros['client_id']).'</td>
							<td>'.RT::calcualte_query_summ($zapros['query_num']).'</td>
							<td class="'.$zapros['status'].'_'.$this->user_access.'">'.$status_or_button.'</td>';
				
				// если запрос по строке, возвращаем строку
				if($id_row!=0){return $general_tbl_row_body;}

				$general_tbl_row .= '<tr data-id="'.$zapros['id'].'" id="rt_list_id_'.$zapros['id'].'">
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
		// получаем позиции по запросу
		private function get_position_arr_Database($id){
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

		
		//////////////////////////
		//	Section - Предзаказ
		//////////////////////////
		protected function paperwork_Template($id_row=0){

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
			$Order = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$Order[] = $row;
				}
			}

			
			
			// собираем html строк-предзаказов
			$html1 = '';
			if(count($Order)==0){return 1;}

			foreach ($Order as $predzakaz) {
				//if(!isset($predzakaz2)){continue;} // !!!!!!!!!!!!!!!!!
				$order_num_1 = Cabinet::show_order_num($predzakaz['order_num']);
				$invoice_num = $predzakaz['invoice_num'];


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
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$predzakaz['id']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";

				$result = $mysqli->query($query) or die($mysqli->error);
				$position_arr = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$position_arr[] = $row;
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


				$this->Price_of_position = 0; // общая стоимость заказа
				foreach ($position_arr as $position) {
					
					
					////////////////////////////////////
					//	Расчёт стоимости позиций START  
					////////////////////////////////////
					/*
						!!!!!!!!    ОПИСАНИЕ    !!!!!!!!!

						стоимость товара
						$this->Price_for_the_goods;
						стоимость услуг печати
						$this->Price_of_printing;
						стоимость услуг не относящихся к печати
						$this->Price_of_no_printing;
						общаяя цена позиции включает в себя стоимость услуг и товара
						$this->Price_for_the_position;
					*/
					$this->GET_PRICE_for_position($position);				
					
					////////////////////////////////////
					//	Расчёт стоимости позиций END
					////////////////////////////////////

					$html .= '<tr  data-id="'.$predzakaz['id'].'">
					<td> '.$position['id_dop_data'].'<!--'.$position['id_dop_data'].'|-->  '.$position['art'].'</td>
					<td>'.$position['name'].'</td>
					<td>'.($position['quantity']+$position['zapas']).'</td>
					<td></td>
					<td><span>'.$this->Price_for_the_goods.'</span> р.</td>
					<td><span>'.$this->Price_of_printing.'</span> р.</td>
					<td><span>'.$this->Price_of_no_printing.'</span> р.</td>
					<td><span>'.$this->Price_for_the_position.'</span> р.</td>
					<td></td>
					<td></td>
							</tr>';
					$this->Price_of_position +=$this->Price_for_the_position; // прибавим к общей стоимости
				}

				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = ($this->Price_of_position!=0)?round($predzakaz['payment_status']*100/$this->Price_of_position,2):'0.00';		
				// собираем строку заказа
				
				$html2 = '<tr data-id="'.$predzakaz['id'].'" >';
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				//'.$this->get_manager_name_Database_Html($predzakaz['manager_id']).'
				$html2_body = '<td class="show_hide" rowspan="'.$rowspan.'"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$predzakaz['id'].'&client_id='.$predzakaz['client_id'].'">'.$order_num_1.'</a></td>
							<td>'.$predzakaz['create_time'].'<br>'.$this->get_manager_name_Database_Html($predzakaz['manager_id'],1).'</td>
							<td>'.$this->get_client_name_Database($predzakaz['client_id'],1).'</td>
							<td class="invoice_num" contenteditable="true">'.$predzakaz['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" predzakaz="'.$predzakaz['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$predzakaz['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span edit_span"  contenteditable="true">'.$predzakaz['payment_status'].'</span>р</td>
							<td><span>'.$this->Price_of_position.'</span> р.</td>
							<td class="buch_status_select">'.$this->decoder_statuslist_buch($predzakaz['buch_status']).'</td>
							<td class="select_global_status">'.$this->decoder_statuslist_order_and_paperwork($predzakaz['global_status']).'</td>';
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

		
		//////////////////////////
		//	Section - Заказы
		//////////////////////////
		protected function orders_Template($id_row=0){
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

			// // отфильтровываем по статусам ПРЕДЗАКАЗЫ от заказов, выводим только заказы
			// $query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` = '".implode(",", array_keys($this->order_status))."'";
			
			$query .= ' ORDER BY `id` DESC';
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$this->Order_arr = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->Order_arr[] = $row;
				}
			}

			$table_order_row = '';		
			// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
			// error_reporting(E_ALL);
			//include '../os_form_class.php';
			// создаем экземпляр класса форм
			$this->FORM = new Forms();

			// ПЕРЕБОР ЗАКАЗОВ
			foreach ($this->Order_arr as $this->Order) {
				// цена заказа
				$this->price_order = 0;

				// запоминаем обрабатываемые номера заказа и запроса
				// номер запроса
				$this->query_num = $this->Order['query_num'];
				// номер заказа
				$this->order_num = $this->Order['order_num'];

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

				// запрашиваем информацию по позициям
				$table_order_positions_rows = $this->table_order_positions_rows_Html();
				
				// формируем строку с информацией о заказе
				$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
				
				$table_order_row2_body = '<td class="show_hide" rowspan="'.$this->position_item.'"><span class="cabinett_row_hide_orders"></span></td>
						<td colspan="4" class="orders_info">
							<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>
							'.$this->get_client_name_link_Database($this->Order['client_id']).'
							<span class="greyText">счёт№:'.$this->Order['number_pyament_list'].'</span>
						</td>
						<td>
							<!--// comments -->
							<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>	
						</td>
						<td><span class="show_the_full_information">'.$this->price_order.'</span> р.</td>
						<td colspan="2">
							<span class="greyText">оплачен: </span>'.$this->Order['payment_date'].'
							<span class="greyText">в размере: </span> '.$this->Order['payment_status'].' р.
						</td>
						<td contenteditable="true" class="deadline">'.$this->Order['deadline'].'</td>
						<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>
						<td><span class="greyText">заказа: </span></td>
						<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
				$table_order_row2 = '</tr>';
				// включаем вывод позиций 
				$table_order_row .= $table_order_row2_body.$table_order_row2.$table_order_positions_rows;

				// запрос по одной строке без подробностей
				if($id_row){return $table_order_row2_body;}
			}

			

			$html = $table_head_html.$table_order_row.'</table>';
			echo $html;
		}

		
		// возвращает html строки позиций
		private function table_order_positions_rows_Html(){			
			// получаем массив позиций заказа
			$positions_rows = $this->positions_rows_Database($this->Order['id']);
			$html = '';	

			$this->position_item = 1;// порядковый номер позиции
			// формируем строки позиций	(перебор позиций)		
			foreach ($positions_rows as $key => $position) {
				$this->Position_status_list = array(); // в переменную заложим все статусы

				$this->id_dop_data = $position['id_dop_data'];
				////////////////////////////////////
				//	Расчёт стоимости позиций START  
				////////////////////////////////////
					/*
						!!!!!!!!    ОПИСАНИЕ    !!!!!!!!!

						стоимость товара
						$this->Price_for_the_goods;	// $price_out
						стоимость услуг печати
						$this->Price_of_printing;
						стоимость услуг не относящихся к печати
						$this->Price_of_no_printing;
						общаяя цена позиции включает в себя стоимость услуг и товара
						$this->Price_for_the_position;
					*/
				
				$this->GET_PRICE_for_position($position);				
					
				////////////////////////////////////
				//	Расчёт стоимости позиций END
				////////////////////////////////////			
				
				$html .= '<tr class="positions_rows row__'.$this->position_item.'" data-id="'.$position['id'].'">';
				// порядковый номер позиции в заказе
				$html .= '<td><span class="orders_info_punct">'.$this->position_item.'п</span></td>';
				// описание позиции
				$html .= '<td>';
				// комментарии
				// наименование товара
				$html .= '<span class="art_and_name">'.$position['art'].'  '.$position['name'].'</span>';
					
				// добавляем доп описание
				// для каталога и НЕкаталога способы хранения и получения данной информации различны
				if(trim($position['type'])!='cat' && trim($position['type'])!=''){
					// доп инфо по некаталогу берём из json 
					$html .= $this->decode_json_no_cat_to_html($position);
				}else if(trim($position['type'])!=''){
					// доп инфо по каталогу из услуг..... НУЖНО РЕАЛИЗОВЫВАТЬ
					$html .= '';
				}


				$html .= '</td>';
				// тираж, запас, печатать/непечатать запас
				$html .= '<td>';
				$html .= '<div class="quantity">'.$position['quantity'].'</div>';
				$html .= '<div class="zapas">'.(($position['zapas']!=0 && trim($position['zapas'])!='')?'+'.$position['zapas']:'').'</div>';
				$html .= '<div class="print_z">'.(($position['zapas']!=0 && trim($position['zapas'])!='')?(($position['print_z']==0)?'НПЗ':'ПЗ'):'').'</div>';
				$html .= '</td>';
				
				// поставщик товара и номер резерва для каталожной продукции 
				$html .= '<td>
						<div class="supplier">'.$this->get_supplier_name($position['art']).'</div>
						<div class="number_rezerv">'.$position['number_rezerv'].'</div>
						</td>';
				// подрядчк печати 
				$html .= '<td class="change_supplier"  data-id="'.$position['suppliers_id'].'" data-id_dop_data="'.$position['id_dop_data'].'">'.$position['suppliers_name'].'</td>';
				// сумма за позицию включая стоимость услуг 

				$html .= '<td data-order_id="'.$this->Order['id'].'" data-id="'.$position['id'].'" data-order_num_user="'.$this->order_num_for_User.'" data-order_num="'.$this->Order['order_num'].'" data-cab_dop_data_id="'.$position['id_dop_data'].'" class="price_for_the_position">'.$this->Price_for_the_position.'</td>';
				// всплывающее окно тех и доп инфо
				// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
				// думаю есть смысл хранения в json 
				// обязательные поля:
				// {"comments":" ","technical_info":" ","maket":" "}
				$html .= $this->grt_dop_teh_info($position);
				
				// дата утверждения макета
				$this->Position_approval_date = $position['approval_date'];
				$html .= '<td><input type="text" class="approval_date" value="'.$this->Position_approval_date.'"></td>';

				$html .= '<td><!--// срок по ДС по позиции --></td>';

				// дата сдачи
				// тут м.б. должна быть дата сдачи позиции ... но вроде как мы все позиции по умолчанию сдаём в срок по заказу, а если нет, то отгружаем частично по факту готовности, а следовательно нам нет необходимости вставлять для позиций редактируемое поле с датой сдачи
				$html .= '<td><!--// дата сдачи по позиции --></td>';


				// получаем статусы участников заказа в две колонки: отдел - статус
				$html .= $this->position_status_list_Html($position);
				$html .= '</tr>';	

				// добавляем стоимость позиции к стоимости заказа
				$this->price_order += $this->Price_for_the_position;
				$this->position_item++;
			}				
			return $html;
		}		


		
		

		// // вывод описания по позиции каталог
		// private function get_dop_information_text_cat_Html($position){
		// 	// echo '<pre>';
		// 	// print_r($position);
		// 	// echo '</pre>';
		// }

		
		


		//////////////////////////
		//	Section - На отгрузку
		//////////////////////////
		private function for_shipping_Template(){
			echo 'Раздел в разработке =)';
		}	


		//////////////////////////
		//	Section - Закрытые
		//////////////////////////
		private function closed_Template(){
			echo 'Раздел в разработке =)';
		}	


		//////////////////////////
		//	Section - Образцы
		//////////////////////////
		private function simples_Template(){
			echo 'Раздел в разработке =)';
		}	


		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################

		
		
		function __destruct(){}
}


?>