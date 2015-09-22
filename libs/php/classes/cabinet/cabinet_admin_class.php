<?php
	
	class Cabinet_admin_class extends Cabinet{
		// разрешить показ сообщений
		// private $allow_messages = false;


		// словарь
		public $menu_name_arr = array(
			'important' => 'Важно',
			'in_processed'=>'обрабатывается',
			'no_worcked_snab' => 'Не обработанные СНАБ',		
			'no_worcked_men' => 'Не обработанные МЕН',
			'send_to_snab' => 'Отправлены в СНАБ',
			'calk_snab' => 'Рассчитанные',
			'ready_KP' => 'Выставлено КП',
			'denied' => 'ТЗ не корректно',
			'orders' => 'Заказы',
			'requests' =>'Запросы',
			'create_spec' => 'Спецификация создана',
			'signed' => 'Спецификация подписана',
			'expense' => 'Счёт выставлен',
			'requested_the_bill' => 'Счёт запрошен',
			'paperwork' => 'Предзаказ',
			'order_start' => 'Запуск в работу (заказ)',
			'tz_no_correct' => 'ТЗ не корректно',
			'purchase' => 'Закупка',
			'design' => 'Дизайн',
			
			'ready_for_shipment' => 'Готов к отгрузке',
			'paused' => 'на паузе',
			'history' => 'История',
			'simples' => 'Образцы',
			'closed'=>'Закрытые',
			'all' => 'Все',
			'issue'=>'Вопрос',
			'not accepted' => 'Не принято',
			'for_shipping' => 'На отгрузку',
			'my_orders_diz' => 'Мои заказы дизайн',
			'all_orders_diz' => 'Все заказы дизайн',
			'order_of_documents' => 'Заказ документов',
			'arrange_delivery' => 'Оформить доставку',
			'delivery' => 'Доставка',
			'pclosing_documents' => 'Закрывающие документы',
			'otgrugen' => 'Отгруженные',
			'already_shipped' => 'Отгруженные',
			'partially_shipped' => 'Частично',
			'fully_shipped' => 'Полностью',
			'partially_shipped' => 'Частично отгружен',
			'the_order_is_create' => 'Заказ сформирован',
			'payment_the_bill' => 'Счёт оплачен',	
			'refund_in_a_row' => 'возврат средств по счёту',
			'cancelled' => 'Счёт аннулирован',
			'all_the_bill' => 'Все счёта',
			// заказы
			'order_all' => 'Все заказы',
			'order_start' => 'Запуск в работу (заказ)',
			'order_in_work' => 'Заказы в работе',
			'design_all' => 'Дизайн ВСЕ',
			'design_for_one_men' => 'Дизайн МОЁ',
			'production' => 'Производство'									
		); 

		// protected $user_id;
		// protected $user_access;

		// название подраздела кабинета
		// private $sub_subsection;

		protected $user_id;
		protected $user_access;


		// содержит экземпляр класса кабинета вер. 1.0
		// private $CABINET;

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
			// $this->CABINET = new Cabinet;

			//$this->FORM = new Forms;
			

		}


		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			// $method_template = $_GET['section'].'_Template';
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();				
			}else{
				// обработка ответа о неправильном адресе
				$this->response_to_the_wrong_address($method_template);	
			}
		}


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
			protected function requests_Template($id_row = 0){
				$where = 0;
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
					$query .= " ".(($where)?'AND':'WHERE')." WHERE `".RT_LIST."`.`id` = '".$id_row."'";
					$where = 1;
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
							$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`status` = 'history'";
							$where = 1;
							break;
						case 'no_worcked_men':
							$query .= " ".(($where)?'AND':'WHERE')." (`".RT_LIST."`.`status` = 'not_process' OR `".RT_LIST."`.`status` = 'new_query')";
							$where = 1;
							break;

						case 'in_work':
							$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`status` = 'in_work' ";
							$where = 1;
							break;
						default:
							break;
					}

					// если знаем id клиента - выводим только заказы по клиенту
					if(isset($_GET['client_id'])){
						$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`client_id` = '".$_GET['client_id']."'";
						$where = 1;
					}
				}

				// последний запрос всегда ввеорху
				$query .= ' ORDER BY `id` DESC'; 
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$this->Zapros_arr = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Zapros_arr[] = $row;
					}
				}

				$general_tbl_row = '';
				// собираем html строк-запросов 
				$html = '';
				foreach ($this->Zapros_arr as $this->Zapros) {
					// получаем позиции по запросу
					$positions_arr = $this->get_position_arr_Database($this->Zapros['query_num']);
					// получаем открыт/закрыто
					$this->open__close = $this->get_open_close_for_this_user($this->Zapros['open_close']);
						
					

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
							<td><a class="go_to_position_card_link" target="_blank" href="./?page=client_folder&client_id='.$this->Zapros['client_id'].'&section=rt_position&id='.$position['id'].'">'.$position['name'].'</a> <span class="variant_comments_dop">( Вариант '.$name_count++.' )</span></td>
							<td>'.$position['quantity'].'</td>
							<td></td>
							<td>'.$this->Price_for_the_goods.'</td>
							<td>'.$this->Price_of_printing.'</td>
							<td>'.$this->Price_of_no_printing.'</td>
							<td>'.$this->Price_for_the_position.'</td>
							<td></td>
							<td data-type="'.$position['type'].'" data-status="'.$position['status_snab'].'" class="'.$position['status_snab'].'_'.$this->user_access.' '.$this->Zapros['status'].'_status_snab_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($position['status_snab']).'</td>
						</tr>';
					}

					//////////////////////////
					//	собираем строку с номером запроса (шапку заказа)
					//////////////////////////
					switch ($this->Zapros['status']) {
						/*
							на дальнейшую реализацию
						*/
						// case 'new_query':
						// 	$status_or_button = '<div class="give_to_all">отдать свободному</div>';
						// 	break;
						default:
							####
							# $this->name_cirillic_status  -  содержится в родительском классе
							###
							$status_or_button = (isset($this->name_cirillic_status[$this->Zapros['status']])?$this->name_cirillic_status[$this->Zapros['status']]:'статус не предусмотрен!!!!'.$this->Zapros['status']);
							break;
					}

					// выделяем красным текстом если менеджер не взял запрос в обработку в течение 5 часов
					$overdue = (($this->Zapros['time_attach_manager_sec']*(-1)>18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
					// если в массиве $_POST содержится значение, значит мы запрашиваем только одну строку и подставляем значение из массива
					$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
						//////////////////////////
						//	собираем строку запроса
						//////////////////////////
							$general_tbl_row_body ='<td class="show_hide" '.$this->open_close_rowspan.'="'.$rowspan.'"><span class="cabinett_row_hide'.$this->open_close_class.'"></span></td>';
							$general_tbl_row_body .='<td><a href="./?page=client_folder&client_id='.$this->Zapros['client_id'].'&query_num='.$this->Zapros['query_num'].'">'.$this->Zapros['query_num'].'</a> </td>';
							$general_tbl_row_body .='<td><span data-sec="'.$this->Zapros['time_attach_manager_sec']*(-1).'" '.$overdue.'>'.$this->Zapros['time_attach_manager'].'</span>'.$this->get_manager_name_Database_Html($this->Zapros['manager_id']).'</td>';
							$general_tbl_row_body .='<td>'.$this->Zapros['create_time'].'</td>';
							$general_tbl_row_body .='<td><span data-rt_list_query_num="'.$this->Zapros['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($this->Zapros['query_num']).'"></span></td>';
							$general_tbl_row_body .='<td>'.$this->get_client_name_Database($this->Zapros['client_id']).'</td>';
							$general_tbl_row_body .='<td>'.RT::calcualte_query_summ($this->Zapros['query_num']).'</td>';
							$general_tbl_row_body .='<td class="'.$this->Zapros['status'].'_'.$this->user_access.'">'.$status_or_button.'</td>';
					
					// если запрос по строке, возвращаем строку
					if($id_row!=0){return $general_tbl_row_body;}

					$general_tbl_row .= '<tr data-id="'.$this->Zapros['id'].'" id="rt_list_id_'.$this->Zapros['id'].'">
										'.$general_tbl_row_body.'
										</tr>';
					
					$general_tbl_row .= '<tr class="query_detail" '.$this->open_close_tr_style.'>';
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
		//	Section - Заказы
		//////////////////////////
			// // роутер по предзаказу
			// 	protected function orders_Template($id_row=0){
			// 		$method_template = $_GET['section'].'_'.$_GET['subsection'].'_Template';
			// 		// $method_template = $_GET['section'].'_Template';
			// 		echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

			// 		// если в этом классе существует такой метод - выполняем его
			// 		if(method_exists($this, $method_template)){
			// 			$this->$method_template($id_row);				
			// 		}else{
			// 			// обработка ответа о неправильном адресе
			// 			echo 'фильтр не найден';
			// 		}
			// 	}
			// 	// шаблон заказ создан
			// 	protected function orders_all_Template($id_row=0){

			// 		$where = 0;
			// 		$html = '';
			// 		$table_head_html = '
			// 			<table id="general_panel_orders_tbl">
			// 			<tr>
			// 				<th colspan="3">Артикул/номенклатура/печать</th>
			// 				<th>тираж<br>запас</th>
			// 				<th>поставщик товара и резерв</th>
			// 				<th>подрядчик печати</th>
			// 				<th>сумма</th>
			// 				<th>тех + доп инфо</th>
			// 				<th>дата утв. макета</th>
			// 				<th>срок ДС</th>
			// 				<th>дата сдачи</th>
			// 				<th></th>
			// 				<th>статус</th>
			// 			</tr>
			// 		';

			// 		$this->collspan = 12;

			// 		global $mysqli;

			// 		$query = "SELECT 
			// 			`".CAB_ORDER_ROWS."`.*, 
			// 			DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
			// 			FROM `".CAB_ORDER_ROWS."`";
					
			// 		// вывод только строки заказа
			// 		if($id_row){
			// 			$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
			// 			$where = 1;
			// 		}else{
			// 			// если знаем id клиента - выводим только заказы по клиенту
			// 			if(isset($_GET['client_id'])){
			// 				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
			// 				$where = 1;
			// 			}

			// 			// если это МЕН - выводим только его заказы
			// 			if($this->user_access ==5){
			// 				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
			// 				$where = 1;
			// 			}

			// 			/*
			// 				// // получаем статусы заказа
			// 				// $order_status_string = '';
			// 				// foreach (array_keys($this->order_status) as $key => $status) {
			// 				// 	$order_status_string .= (($key>0)?",":"")."'".$status."'";
			// 				// }	
			// 			*/		
			// 			// выбираем из базы только заказы being_prepared (в оформлении)
			// 			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE 'being_prepared'";
			// 			$where = 1;
			// 		}
			// 		//////////////////////////
			// 		//	sorting
			// 		//////////////////////////
			// 		$query .= ' ORDER BY `id` DESC';
					
			// 		//////////////////////////
			// 		//	check the query
			// 		//////////////////////////
			// 		// echo '*** $query = '.$query.'<br>';


			// 		//////////////////////////
			// 		//	query for get data
			// 		//////////////////////////
			// 		$result = $mysqli->query($query) or die($mysqli->error);

			// 		$this->Order_arr = array();
					
			// 		if($result->num_rows > 0){
			// 			while($row = $result->fetch_assoc()){
			// 				$this->Order_arr[] = $row;
			// 			}
			// 		}


			// 		$table_order_row = '';		

			// 		// создаем экземпляр класса форм
			// 		$this->FORM = new Forms();


			// 		// тут будут храниться операторы
			// 		$this->Order['operators_listiong'] = '';


			// 		// ПЕРЕБОР ЗАКАЗОВ
			// 		foreach ($this->Order_arr as $this->Order) {						
			// 			$this->price_order = 0;// стоимость заказа 

			// 			//////////////////////////
			// 			//	open_close   -- start
			// 			//////////////////////////
			// 				// получаем флаг открыт/закрыто
			// 				$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
			// 			//////////////////////////
			// 			//	open_close   -- end
			// 			//////////////////////////

			// 			// запоминаем обрабатываемые номера заказа и запроса
			// 			// номер запроса
			// 			$this->query_num = $this->Order['query_num'];
			// 			// номер заказа
			// 			$this->order_num = $this->Order['order_num'];

			// 			// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
			// 			$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

			// 			// запрашиваем информацию по позициям
			// 			$this->order_deadline = ''; // дата отгрузки заказа (из спецификации)
			// 			$this->order_date_of_delivery = ''; // количество рабочих дней на работу над заказом (из спецификации)
			// 			$this->position_item = 1; // порядковый номер позиции
			// 			$table_order_positions_rows = $this->table_specificate_for_order_Html();
			// 			// $table_order_positions_rows = '';
						
			// 			// формируем строку с информацией о заказе
			// 			$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'" data-order_num="'.$this->Order['order_num'].'">';
						

			// 			//////////////////////////
			// 			//	тело строки заказа -- start ---
			// 			//////////////////////////
			// 				$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.($this->rows_num+1).'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>';
			// 				$table_order_row2_body .= '<td colspan="3" class="orders_info">';
			// 					$table_order_row2_body .= '<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText">';
			// 						// добавляем ссылку на клиента
			// 						$table_order_row2_body .= $this->get_client_name_link_Database($this->Order['client_id']);
									
			// 						// исполнители заказа
			// 						$table_order_row2_body .= '<br>';
			// 						$table_order_row2_body .= '<table class="curator_on_request">';
			// 							$table_order_row2_body .= '<tr>';
			// 								$table_order_row2_body .= '<td>';
			// 									$table_order_row2_body .= '<span class="greyText">мен: '.$this->get_name_employee_Database_Html($this->Order['manager_id']).'</span>';
			// 								$table_order_row2_body .= '</td>';
			// 								$table_order_row2_body .= '<td>';
			// 									$table_order_row2_body .= '<span class="greyText">дизайнер: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
			// 								$table_order_row2_body .= '</td>';
			// 							$table_order_row2_body .= '</tr>';	
			// 							$table_order_row2_body .= '<tr>';
			// 								$table_order_row2_body .= '<td>';
			// 									$table_order_row2_body .= '<span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span>';
			// 								$table_order_row2_body .= '</td>';
			// 								$table_order_row2_body .= '<td>';
			// 									$table_order_row2_body .= '<span class="greyText">оператор: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
			// 								$table_order_row2_body .= '</td>';
			// 							$table_order_row2_body .= '</tr>';	
			// 						$table_order_row2_body .= '</table>';								

			// 				$table_order_row2_body .= '</td>';
			// 				// комментарии
			// 				$table_order_row2_body .= '<td>';								
			// 					$table_order_row2_body .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
			// 				$table_order_row2_body .= '</td>';
							
			// 				$table_order_row2_body .= '<td></td>';
							
			// 				// стоимость заказа
			// 				$table_order_row2_body .= '<td><span class="show_the_full_information">'.$this->price_order.'</span> р.</td>';
							
			// 				// бух учет
			// 				$table_order_row2_body .= '<td class="buh_uchet_for_order" data-id="'.$this->Order['order_num'].'"></td>';
							
			// 				// платёжная информация
			// 				$this->Order_payment_percent = $this->calculation_percent_of_payment($this->price_order, $this->Order['payment_status']);

			// 				$table_order_row2_body .= '<td>';
			// 					// // если был оплачен.... и % оплаты больше нуля
			// 					// if ((int)$this->Order_payment_percent > 0) {
			// 					// 	// когда оплачен
			// 					// 	$table_order_row2_body .= '<span class="greyText">оплачен: </span>'.$this->Order['payment_date'].'<br>';
			// 					// 	// сколько оплатили в %
			// 					// 	$table_order_row2_body .= '<span class="greyText">в размере: </span> '. $this->Order_payment_percent .' %';
			// 					// }else{
			// 					// 	$table_order_row2_body .= '<span class="redText">НЕ ОПЛАЧЕН</span>';
			// 					// }
			// 				$table_order_row2_body .= '</td>';
			// 					/*
			// 							$this->order_deadline = ''; // дата отгрузки заказа (из спецификации)
			// 			$this->order_date_of_delivery = ''; // количество рабочих дней на работу над заказом (из спецификации)
			// 					*/
			// 				$table_order_row2_body .= '<td></td>';
			// 				$table_order_row2_body .= '<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>';
			// 				$table_order_row2_body .= '<td><span class="greyText">заказа: </span></td>';
			// 				$table_order_row2_body .= '<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
						
			// 			/////////////////////////////////////
			// 			//	тело строки заказа -- end ---
			// 			/////////////////////////////////////

			// 			$table_order_row2 = '</tr>';
			// 			// включаем вывод позиций 
			// 			$table_order_row .= $table_order_row2_body.$table_order_row2.$table_order_positions_rows;

			// 			// запрос по одной строке без подробностей
			// 			if($id_row != 0){return $table_order_row2_body;}						
			// 		}

					
					

			// 		$html = $table_head_html.$table_order_row.'</table>';
			// 		echo $html;
			// 	}
		// protected function orders_Template($id_row=0){

		// 	$where = 0;
		// 	$html = '';
		// 	$table_head_html = '
		// 		<table id="general_panel_orders_tbl">
		// 		<tr>
		// 			<th colspan="3">Артикул/номенклатура/печать</th>
		// 			<th>тираж<br>запас</th>
		// 			<th>поставщик товара и резерв</th>
		// 			<th>подрядчик печати</th>
		// 			<th>сумма</th>
		// 			<th>тех + доп инфо</th>
		// 			<th>дата утв. макета</th>
		// 			<th>срок ДС</th>
		// 			<th>дата сдачи</th>
		// 			<th></th>
		// 			<th>статус</th>
		// 		</tr>
		// 	';

		// 	global $mysqli;

		// 	$query = "SELECT 
		// 		`".CAB_ORDER_ROWS."`.*, 
		// 		DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		// 		FROM `".CAB_ORDER_ROWS."`";
			
		// 	// вывод только строки заказа
		// 	if($id_row){
		// 		$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
		// 		$where = 1;
		// 	}else{
		// 		// если знаем id клиента - выводим только заказы по клиенту
		// 		if(isset($_GET['client_id'])){
		// 			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
		// 			$where = 1;
		// 		}

		// 		// если это МЕН - выводим только его заказы
		// 		if($this->user_access ==5){
		// 			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
		// 			$where = 1;
		// 		}

		// 		// получаем статусы заказа
		// 		$order_status_string = '';
		// 		foreach (array_keys($this->order_status) as $key => $status) {
		// 			$order_status_string .= (($key>0)?",":"")."'".$status."'";
		// 		}			
		// 		// выбираем из базы только заказы (предзаказы не показываем)
		// 		$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$order_status_string.")";
		// 		$where = 1;
		// 	}






		// 	//////////////////////////
		// 	//	sorting
		// 	//////////////////////////
		// 	$query .= ' ORDER BY `id` DESC';
			
		// 	//////////////////////////
		// 	//	check the query
		// 	//////////////////////////
		// 	// echo '*** $query = '.$query.'<br>';


		// 	//////////////////////////
		// 	//	query for get data
		// 	//////////////////////////
		// 	$result = $mysqli->query($query) or die($mysqli->error);

		// 	$this->Order_arr = array();
			
		// 	if($result->num_rows > 0){
		// 		while($row = $result->fetch_assoc()){
		// 			$this->Order_arr[] = $row;
		// 		}
		// 	}


		// 	$table_order_row = '';		

		// 	// создаем экземпляр класса форм
		// 	$this->FORM = new Forms();


		// 	// тут будут храниться операторы
		// 	$this->Order['operators_listiong'] = '';

		// 	// ПЕРЕБОР ЗАКАЗОВ
		// 	foreach ($this->Order_arr as $this->Order) {
		// 		// цена заказа
		// 		$this->price_order = 0;

		// 		//////////////////////////
		// 		//	open_close   -- start
		// 		//////////////////////////
		// 			// получаем флаг открыт/закрыто
		// 			$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
					
		// 			// выполнение метода get_open_close_for_this_user - вернёт 3 переменные в object
		// 			// class для кнопки показать / скрыть
		// 			#$this->open_close_class = "";
		// 			// rowspan / data-rowspan
		// 			#$this->open_close_rowspan = "rowspan";
		// 			// стили для строк которые скрываем или показываем
		// 			#$this->open_close_tr_style = ' style="display: table-row;"';

		// 		//////////////////////////
		// 		//	open_close   -- end
		// 		//////////////////////////

		// 		// запоминаем обрабатываемые номера заказа и запроса
		// 		// номер запроса
		// 		$this->query_num = $this->Order['query_num'];
		// 		// номер заказа
		// 		$this->order_num = $this->Order['order_num'];

		// 		// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
		// 		$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

		// 		// запрашиваем информацию по позициям
		// 		$table_order_positions_rows = $this->table_order_positions_rows_Html();
				
		// 		// формируем строку с информацией о заказе
		// 		$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'" data-order_num="'.$this->Order['order_num'].'">';
				

		// 		//////////////////////////
		// 		//	тело строки заказа -- start ---
		// 		//////////////////////////
		// 			$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>';
		// 			$table_order_row2_body .= '<td colspan="4" class="orders_info">';
		// 				$table_order_row2_body .= '<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>';
		// 					// добавляем ссылку на клиента
		// 					$table_order_row2_body .= $this->get_client_name_link_Database($this->Order['client_id']);
		// 				// номер счёта
		// 				$table_order_row2_body .= '&nbsp;<span class="greyText">счёт№:'.$this->Order['number_pyament_list'].'</span>';
		// 				// имя менеджера
		// 				$table_order_row2_body .= '&nbsp;<span class="greyText">менеджер: '.$this->get_name_employee_Database_Html($this->Order['manager_id']).'</span>';
		// 				// снабжение 
		// 				$table_order_row2_body .= '&nbsp;<span class="greyText">снабжение: '.$this->get_name_employee_Database_Html($this->Order['snab_id']).'</span>';

		// 			$table_order_row2_body .= '</td>';
					
		// 			// комментарии
		// 			$table_order_row2_body .= '<td><!--// comments -->';
		// 				$table_order_row2_body .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
		// 			$table_order_row2_body .= '</td>';
					
		// 			// стоимость заказа
		// 			$table_order_row2_body .= '<td><span class="show_the_full_information">'.$this->price_order.'</span> р.</td>';
					
		// 			// платёжная информация
		// 			$this->Order_payment_percent = $this->calculation_percent_of_payment($this->price_order, $this->Order['payment_status']);

		// 			$table_order_row2_body .= '<td colspan="2">';
		// 				// если был оплачен.... и % оплаты больше нуля
		// 				if ((int)$this->Order_payment_percent > 0) {
		// 					// когда оплачен
		// 					$table_order_row2_body .= '<span class="greyText">оплачен: </span>'.$this->Order['payment_date'].'<br>';
		// 					// сколько оплатили в %
		// 					$table_order_row2_body .= '<span class="greyText">в размере: </span> '. $this->Order_payment_percent .' %';
		// 				}else{
		// 					$table_order_row2_body .= '<span class="redText">НЕ ОПЛАЧЕН</span>';
		// 				}
		// 			$table_order_row2_body .= '</td>';
						
		// 			$table_order_row2_body .= '<td contenteditable="true" class="deadline">'.$this->Order['deadline'].'</td>';
		// 			$table_order_row2_body .= '<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>';
		// 			$table_order_row2_body .= '<td><span class="greyText">заказа: </span></td>';
		// 			$table_order_row2_body .= '<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
				
		// 		/////////////////////////////////////
		// 		//	тело строки заказа -- end ---
		// 		/////////////////////////////////////

		// 		$table_order_row2 = '</tr>';
		// 		// включаем вывод позиций 
		// 		$table_order_row .= $table_order_row2_body.$table_order_row2.$table_order_positions_rows;

		// 		// запрос по одной строке без подробностей
		// 		if($id_row != 0){return $table_order_row2_body;}
				
		// 	}

			
			

		// 	$html = $table_head_html.$table_order_row.'</table>';
		// 	echo $html;
		// }

		/*
		// возвращает html строки позиций
		private function table_order_positions_rows_Html(){	

			/////////////////////////////
			//	фильтр позиций  -- start
			/////////////////////////////
				// готов к отгрузке
				if(isset($_GET['subsection']) && $_GET['subsection']=='ready_for_shipment'){
					$this->filter_position = " AND `".CAB_ORDER_MAIN."`.`status_sklad` = 'ready_for_shipment'";
				}
				// готов к отгрузке
				if (isset($_GET['subsection']) && $_GET['subsection']=='partially_shipped') {
					$this->filter_position = " AND `".CAB_ORDER_MAIN."`.`status_sklad` = 'goods_shipped_for_client'";	
				}	
			/////////////////////////////
			//	фильтр позиций  -- end
			/////////////////////////////

			// получаем массив позиций заказа
			$positions_rows = $this->positions_rows_Database($this->Order['order_num']);
			$html = '';	

			$this->position_item = 1;// порядковый номер позиции
			// формируем строки позиций	(перебор позиций)		
			foreach ($positions_rows as $key => $position) {
				$this->Position_status_list = array(); // в переменную заложим все статусы

				$this->id_dop_data = $position['id_dop_data'];
				////////////////////////////////////
				//	Расчёт стоимости позиций START  
				////////////////////////////////////
				
				
				$this->GET_PRICE_for_position($position);				
					
				////////////////////////////////////
				//	Расчёт стоимости позиций END
				////////////////////////////////////			
				
				$html .= '<tr class="positions_rows row__'.$this->position_item.'" data-cab_dop_data_id="'.$this->id_dop_data.'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>';
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
				$html .= '<td>';
					$html .= $this->get_Position_approval_date( $this->Position_approval_date = $position['approval_date'], $position['id'] );
				$html .= '</td>';

				$html .= '<td><!--// срок по ДС по позиции --></td>';

				// дата сдачи
				// тут м.б. должна быть дата сдачи позиции ... но вроде как мы все позиции по умолчанию сдаём в срок по заказу, а если нет, то отгружаем частично по факту готовности, а следовательно нам нет необходимости вставлять для позиций редактируемое поле с датой сдачи
				$html .= '<td><!--// дата сдачи по позиции --></td>';


				// получаем статусы участников заказа в две колонки: отдел - статус
				// ob_start();
			 // 	echo '<pre>';
			 // 	print_r($position);
			 // 	echo '</pre>';
			    	
			 // 	$content = ob_get_contents();
			 // 	ob_get_clean();
			 	// $html =$content;
				$html .= $this->position_status_list_Html($position);
				$html .= '</tr>';	

				// добавляем стоимость позиции к стоимости заказа
				$this->price_order += $this->Price_for_the_position;
				$this->position_item++;
			}				
			return $html;
		}		

		*/
		
		//////////////////////////
		//	Section - На отгрузку
		//////////////////////////
		protected function for_shipping_Template($id_row=0){
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

				//////////////////////////
				//	open_close   -- start
				//////////////////////////
					// получаем флаг открыт/закрыто
					$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
					
					// выполнение метода get_open_close_for_this_user - вернёт 3 переменные в object
					// class для кнопки показать / скрыть
					#$this->open_close_class = "";
					// rowspan / data-rowspan
					#$this->open_close_rowspan = "rowspan";
					// стили для строк которые скрываем или показываем
					#$this->open_close_tr_style = ' style="display: table-row;"';

				//////////////////////////
				//	open_close   -- end
				//////////////////////////

				// запоминаем обрабатываемые номера заказа и запроса
				// номер запроса
				$this->query_num = $this->Order['query_num'];
				// номер заказа
				$this->order_num = $this->Order['order_num'];

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

				// запрашиваем информацию по позициям
				$table_order_positions_rows = $this->table_order_positions_rows_Html();
				
				// если позиций не найдено - html по заказу не отдаём
				if($table_order_positions_rows == ""){continue;}

				// формируем строку с информацией о заказе
				$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
				
				$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>
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

		//////////////////////////
		//	Section - Отгруженные
		//////////////////////////
		protected function already_shipped_Template($id_row=0){
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

				//////////////////////////
				//	open_close   -- start
				//////////////////////////
					// получаем флаг открыт/закрыто
					$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
					
					// выполнение метода get_open_close_for_this_user - вернёт 3 переменные в object
					// class для кнопки показать / скрыть
					#$this->open_close_class = "";
					// rowspan / data-rowspan
					#$this->open_close_rowspan = "rowspan";
					// стили для строк которые скрываем или показываем
					#$this->open_close_tr_style = ' style="display: table-row;"';

				//////////////////////////
				//	open_close   -- end
				//////////////////////////

				// запоминаем обрабатываемые номера заказа и запроса
				// номер запроса
				$this->query_num = $this->Order['query_num'];
				// номер заказа
				$this->order_num = $this->Order['order_num'];

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

				// запрашиваем информацию по позициям
				$table_order_positions_rows = $this->table_order_positions_rows_Html();
				
				// если позиций не найдено - html по заказу не отдаём
				if($table_order_positions_rows == ""){continue;}

				// формируем строку с информацией о заказе
				$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
				
				$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>
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


		//////////////////////////
		//	Section - Закрытые
		//////////////////////////
		protected function closed_Template($id_row=0){
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
			
			// вывод только строки заказа
			if($id_row){
				$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
				$where = 1;
			}else{
				// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = ''";
			}

			// если знаем id клиента - выводим только заказы по клиенту
			if(isset($_GET['client_id'])){
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
				$where = 1;
			}

			// если это МЕН - выводим только его заказы
			if($this->user_access ==5){
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
				$where = 1;
			}


			// получаем статусы заказа
			$order_status_string = '';
			foreach (array_keys($this->order_status) as $key => $status) {
				$order_status_string .= (($key>0)?",":"")."'".$status."'";
			}
			// выбираем из базы только заказы (предзаказы не показываем)
			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` = 'shipped'";
			$where = 1;


			// последний заказ вверху		
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

				//////////////////////////
				//	open_close   -- start
				//////////////////////////
					// получаем флаг открыт/закрыто
					$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
					
					// выполнение метода get_open_close_for_this_user - вернёт 3 переменные в object
					// class для кнопки показать / скрыть
					#$this->open_close_class = "";
					// rowspan / data-rowspan
					#$this->open_close_rowspan = "rowspan";
					// стили для строк которые скрываем или показываем
					#$this->open_close_tr_style = ' style="display: table-row;"';

				//////////////////////////
				//	open_close   -- end
				//////////////////////////

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
				
				//////////////////////////
				//	тело строки заказа -- start ---
				//////////////////////////
					$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>';
					$table_order_row2_body .= '<td colspan="4" class="orders_info">';
						$table_order_row2_body .= '<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>';
							// добавляем ссылку на клиента
							$table_order_row2_body .= $this->get_client_name_link_Database($this->Order['client_id']);
						// номер счёта
						$table_order_row2_body .= '&nbsp;<span class="greyText">счёт№:'.$this->Order['number_pyament_list'].'</span>';
						// имя менеджера
						$table_order_row2_body .= '&nbsp;<span class="greyText">менеджер: '.$this->get_name_employee_Database_Html($this->Order['manager_id']).'</span>';
						// снабжение 
						$table_order_row2_body .= '&nbsp;<span class="greyText">снабжение: '.$this->get_name_employee_Database_Html($this->Order['snab_id']).'</span>';

					$table_order_row2_body .= '</td>';
					
					// комментарии
					$table_order_row2_body .= '<td><!--// comments -->';
						$table_order_row2_body .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
					$table_order_row2_body .= '</td>';
					
					// стоимость заказа
					$table_order_row2_body .= '<td><span class="show_the_full_information">'.$this->price_order.'</span> р.</td>';
					
					// платёжная информация
					$this->Order_payment_percent = $this->calculation_percent_of_payment($this->price_order, $this->Order['payment_status']);

					$table_order_row2_body .= '<td colspan="2">';
						// если был оплачен.... и % оплаты больше нуля
						if ((int)$this->Order_payment_percent > 0) {
							// когда оплачен
							$table_order_row2_body .= '<span class="greyText">оплачен: </span>'.$this->Order['payment_date'].'<br>';
							// сколько оплатили в %
							$table_order_row2_body .= '<span class="greyText">в размере: </span> '. $this->Order_payment_percent .' %';
						}else{
							$table_order_row2_body .= '<span class="redText">НЕ ОПЛАЧЕН</span>';
						}
					$table_order_row2_body .= '</td>';
						
					$table_order_row2_body .= '<td contenteditable="true" class="deadline">'.$this->Order['deadline'].'</td>';
					$table_order_row2_body .= '<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>';
					$table_order_row2_body .= '<td><span class="greyText">заказа: </span></td>';
					$table_order_row2_body .= '<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
				//////////////////////////
				//	тело строки заказа -- end ---
				//////////////////////////

				$table_order_row2 = '</tr>';
				// включаем вывод позиций 
				$table_order_row .= $table_order_row2_body.$table_order_row2.$table_order_positions_rows;

				// запрос по одной строке без подробностей
				if($id_row){return $table_order_row2_body;}
			}

			

			$html = $table_head_html.$table_order_row.'</table>';
			echo $html;
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