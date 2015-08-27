<?php
	
	class Cabinet_men_class extends Cabinet{

		// расшифровка меню СНАБ
		public $menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked_men' => 'Не обработанные',
		'in_work' => 'В работе',
		'in_work_snab' => 'В работе СНАБ',
		'send_to_snab' => '&&&',
		'calk_snab' => 'Рассчитанные СНАБ',
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
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'на паузе',
		'history' => 'история',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные'													
		); 

		// название подраздела кабинета
		private $sub_subsection;

		// содержит экземпляр класса кабинета вер. 1.0
		// private $CABINET;

		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		public $POSITION_NO_CATALOG;

		function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =)) 
			// echo '<pre>';
			// print_r($_SESSION);
			// echo '</pre>';
				
			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $user_access;

			//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; left:0">this_snab_class </div>';
			
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
			// $this = new Cabinet;

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



		############################################
		###		        AJAX START               ###
		############################################
			protected function get_in_work_AJAX(){
				global $mysqli;
				// прикрепить клиента и менеджера к запросу	
				$query ="UPDATE  `".RT_LIST."` SET `status`='in_work',  `time_taken_into_operation` = NOW(), `manager_id` = '".$this->user_id."' WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
				$result = $mysqli->query($query) or die($mysqli->error);	
				echo '{"response":"OK"}';
			}

			protected function take_in_operation_AJAX(){
				global $mysqli;
				// прикрепить клиента и менеджера к запросу	
				$query ="UPDATE  `".RT_LIST."` SET `status`='taken_into_operation',  `time_taken_into_operation` = NOW(), `manager_id` = '".$this->user_id."' WHERE `id` = '".(int)$_POST['rt_list_id']."';";	
				$result = $mysqli->query($query) or die($mysqli->error);	
				echo '{"response":"OK"}';
			}		

		############################################
		###		         AJAX END                ###
		############################################
		


		#############################################################
		##                          START                          ##
		##      методы для работы с поддиректориями subsection     ##
		#############################################################

		##########################################
		################ Важно
		Private Function important_Template(){
			echo 'Раздел в разработке =)';
		}
		## Важно __ запросы к базе
		private function ___Database1(){
			// запрос 1
		}
		################ Важно_END
		##########################################


		protected function requests_Template($id_row=0){			
			$where = 0;
			include ('./libs/php/classes/rt_class.php');

			global $mysqli;
			///////////////////////////////////////////////
			//	collecting query for the request template
			///////////////////////////////////////////////
				$query = "SELECT 
					`".RT_LIST."`.*, 
					(UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)-UNIX_TIMESTAMP()) AS `time_attach_manager_sec`,
					SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)) AS `time_attach_manager`,
					
					DATE_FORMAT(`".RT_LIST."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
					FROM `".RT_LIST."`
					WHERE (`".RT_LIST."`.`manager_id` = '".$this->user_id."') ";
				
				///////////////////////////////////
				//	execution filtration --- START
				///////////////////////////////////
					if($id_row){// если указан, осущевствляем вывод только одного заказа
						$query .=" AND `".RT_LIST."`.`id` = '".$id_row."'";
						$where = 1;
					}else{
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
								$query .= " AND `".RT_LIST."`.`status` = 'history' ";
								break;
							case 'no_worcked_men':
								$query .= " AND (`".RT_LIST."`.`status` = 'not_process' OR `".RT_LIST."`.`status` = 'taken_into_operation') OR (( `".RT_LIST."`.`manager_id` = '0' OR `".RT_LIST."`.`manager_id` = '') AND (`".RT_LIST."`.`status` = 'not_process')) ";
								break;
							default:
								$query .= " AND `".RT_LIST."`.`status` = 'in_work'";
								break;
						}

						// если знаем id клиента - выводим только заказы по клиенту
						if(isset($_GET['client_id'])){
							$query .= " AND `".RT_LIST."`.`client_id` = '".$_GET['client_id']."'";
						}

					}

				///////////////////////////////////
				//	execution filtration --- END
				///////////////////////////////////

			//////////////////////////
			//	sorting
			//////////////////////////
			$query .= "ORDER BY `id` DESC";

			//////////////////////////
			//	check the query
			//////////////////////////
				// echo '*** $query = *** '.$query.'<br>';

			//////////////////////////
			//	query for get data
			//////////////////////////
				$result = $mysqli->query($query) or die($mysqli->error);			
				$this->Requests_arr = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->Requests_arr[] = $row;
					}
				}


			$general_tbl_row = '';
			// собираем html строк-запросов 
			$html = '';
			foreach ($this->Requests_arr as $key => $this->Requests) {
				// получаем позиции по запросу
				// $this->requests_Template_recuestas_main_rows_Database($value['query_num']);
				$this->Requests_positions = $this->requests_Template_recuestas_main_rows_Database($this->Requests['query_num']);
							

				if(empty($this->Requests_positions)){ continue;}


				// в эту переменную запишется 0 если при переборе вариантов 
				// не встретится ни одного некаталожного товара
				// потом проверим и если все товары в запросе каталожные вывод данного запроса отменяем
				$enabled_echo_this_query = 0;

				
				// наименование продукта
				$name_product = ''; 

				$name_count = 1;
				
				$variant_row = '';

				// счетчик кнопок показа каталожных позиций
				// необходим для ограничения до одной кнопки
				$count_button_show_catalog_variants=0;

				// перебор вариантов
				foreach ($this->Requests_positions as $variant) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $this -> get_query_dop_uslugi($variant['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $this -> get_dop_uslugi_no_print_type($dop_usl);
					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $this -> calc_summ_dop_uslug($dop_usl_print,$variant['quantity']);
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $this -> calc_summ_dop_uslug($dop_usl_no_print,$variant['quantity']);
					// стоимость товара для варианта
					$price_out = $variant['price_out'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;
					

					
									
					//////////////////////////
					//	собираем строки вариантов по каждой позиции
					//////////////////////////
					if($name_product != $variant['name']){$name_product = $variant['name']; $name_count = 1;}
					$variant_row .= '<tr data-id_dop_data="'.$variant['id_dop_data'].'" class="'.$variant['type'].'_5">
						<td>'.$variant['art'].'</td>
						<td><a target="_blank" class="go_to_position_card_link" href="./?page=client_folder&section=rt_position&id='.$variant['id'].'&client_id='.$this->Requests['client_id'].'">'.$variant['name'].'</a> <span class="variant_comments_dop">( Вариант '.$name_count++.' )</span></td>
						<td>'.$variant['quantity'].'</td>
						<td></td>
						<td>'.$price_out.'</td>
						<td>'.$calc_summ_dop_uslug.'</td>
						<td>'.$calc_summ_dop_uslug2.'</td>
						<td>'.$in_out.'</td>
						<td></td>
						<td data-type="'.$variant['type'].'" data-status="'.$variant['status_snab'].'" class="'.$variant['status_snab'].'_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($variant['status_snab']).'</td>
					</tr>';
					
				}

				//////////////////////////
				//	собираем строку с номером заказа (шапку заказа)
				//////////////////////////

				switch ($this->Requests['status']) {
					case 'not_process':
						$status_or_button = '<div class="take_in_operation">Принять в обработку</div>';
						break;
					case 'taken_into_operation':
						$status_or_button = '<div class="get_in_work">Взять в работу</div>';						
						break;					

					default:
						$status_or_button = $this->name_cirillic_status[$this->Requests['status']];
						break;
				}

				$general_tbl_row .= '
						<tr data-id="'.$this->Requests['id'].'" id="rt_list_id_'.$this->Requests['id'].'">							
							<td class="show_hide" data-rowspan="2"><span class="cabinett_row_hide show"></span></td>
							<td><a target="_blank" href="./?page=client_folder&client_id='.$this->Requests['client_id'].'&query_num='.$this->Requests['query_num'].'">'.$this->Requests['query_num'].'</a></td>
							<td>'.$this->get_client_name($this->Requests['client_id'],$this->Requests['status']).'</td>
							<td>'.$this->Requests['create_time'].'</td>
							<td><span data-rt_list_query_num="'.$this->Requests['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($this->Requests['query_num']).'"></span></td>
							<td>'.RT::calcualte_query_summ($this->Requests['query_num']).'</td>
							<td>'.$status_or_button.'</td>
						</tr>';
				
				$general_tbl_row .= '<tr class="query_detail">';
				//$general_tbl_row .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
				$general_tbl_row .= '<td colspan="6" class="each_art">';

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

			///////////////////////////////////////////////////////
			//	collecting the header to the general table
			///////////////////////////////////////////////////////
			$general_tbl_top = '
			<table class="cabinet_general_content_row">
							<tr>
								<th class="show_allArt"></th>
								<th>Запрос №</th>
								<th class="company_name">Компания</th>
								<th>Время обращения</th>
								<th>Коммент</th>
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

		protected function paperwork_Template($id_row=0){
			$where = 0;
			global $mysqli;
			
			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".CAB_ORDER_ROWS."`";
				
			// фильтр по менеджеру
			$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
			$where = 1;

			
			// получаем статусы предзаказа
			$paperwork_status_string = '';
			foreach (array_keys($this->paperwork_status) as $key => $status) {
				$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
			}

			// выбираем из базы только предзаказы (заказы не показываем)
			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$paperwork_status_string.")";
			$where = 1;


			///////////////////////////////////
			//	execution filtration --- START
			///////////////////////////////////
				if($id_row){// если указан, осущевствляем вывод только одного заказа
					$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
					$where = 1;
				}else{
					// если указан id клиента, делаем выборку заказов по клиенту
					if(isset($_GET['client_id']) && $_GET['client_id']!=''){
						$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".(int)$_GET['client_id']."'";
						$where = 1;
					}
					// default
					// $query .=" AND `".CAB_ORDER_ROWS."`.`global_status` = 'being_prepared' OR `".CAB_ORDER_ROWS."`.`global_status` = 'requeried_expense'";
				}

				// если указан id клиента, делаем выборку заказов по клиенту
				if(isset($_GET['client_id']) && $_GET['client_id']!=''){
					$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".(int)$_GET['client_id']."'";
					$where = 1;
				}

			/////////////////////////////////
			//	execution filtration --- END
			/////////////////////////////////
			
			//////////////////////////
			//	sorting
			//////////////////////////
				$query .= " ORDER BY `".CAB_ORDER_ROWS."`.`id` DESC";

			//////////////////////////
			//	check the query
			//////////////////////////
				// echo '*** $query = *** '.$query.'<br>';

			//////////////////////////
			//	query for get data
			//////////////////////////
				$result = $mysqli->query($query) or die($mysqli->error);
				$orders_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$orders_arr[] = $row;
					}
				}

			
			//////////////////////////
			//	collecting the query strings to HTML
			//////////////////////////
			$html1 = '';
			if(count($orders_arr)==0){return 1;}

			foreach ($orders_arr as $this->Order) {
				
				// цена заказа
				$this->price_order = 0;

				// запоминаем обрабатываемые номера заказа и запроса
				// номер запроса
				$this->query_num = $this->Order['query_num'];
				// номер заказа
				$this->order_num = $this->Order['order_num'];

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);


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
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$this->Order['order_num']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";

				$positions_arr = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$positions_arr[] = $row;
					}
				}

				// СОБИРАЕМ ТАБЛИЦУ
				###############################
				// строка с артикулами START
				###############################
				$html = '<tr class="query_detail">';
				//$html .= '<td class="show_hide"><span class="thist_row_hide"></span></td>';
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


				$this->Order['price_out'] = 0; // общая стоимость заказа
				// ПЕРЕБОР ЗАКАЗА / ПРЕДЗАКАЗА
				foreach ($positions_arr as $this->position) {
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


					$html .= '<tr  data-id="'.$this->Order['id'].'">
								<td> '.$this->position['id_dop_data'].'<!--'.$this->position['id_dop_data'].'|-->  '.$this->position['art'].'</td>
								<td>'.$this->position['name'].'</td>
								<td>'.($this->position['quantity']+$this->position['zapas']).'</td>
								<td></td>
								<td><span>'.$this->Price_for_the_goods.'</span> р.</td>
								<td><span>'.$this->Price_of_printing.'</span> р.</td>
								<td><span>'.$this->Price_of_no_printing.'</span> р.</td>
								<td><span>'.$this->Price_for_the_position.'</span> р.</td>
								<td></td>
								<td></td>
							</tr>';

					$this->Order['price_out'] += $this->Price_for_the_position; // прибавим к общей стоимости
				}

				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = ($this->Order['price_out']!=0)?round($this->Order['payment_status']*100/$this->Order['price_out'],2):'0.00';		
				// собираем строку заказа
				
				$html2 = '<tr data-id="'.$this->Order['id'].'" >';
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				//'.$this->get_manager_name_Database_Html($this->Order['manager_id']).'
				$html2_body = '<td class="show_hide" rowspan="'.$rowspan.'"><span class="cabinett_row_hide"></span></td>
							<td>'.$this->order_num_for_User.'<span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span></td>
							<td>'.$this->Order['create_time'].'</td>
							<td>'.$this->get_client_name_Database($this->Order['client_id'],1).'</td>
							<td class="invoice_num" contenteditable="true">'.$this->Order['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" value="'.$this->Order['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$this->Order['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span edit_span"  contenteditable="true">'.$this->Order['payment_status'].'</span>р</td>
							<td><span>'.$this->Order['price_out'].'</span> р.</td>
							<td class="buch_status_select">'.$this->decoder_statuslist_buch($this->Order['buch_status']).'</td>
							<td class="select_global_status">'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
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
		



		////////////////////////////////////////////////////
		//	-----  START  -----  ORDERS  -----  START  -----
		////////////////////////////////////////////////////
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
				$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$order_status_string.")";
				$where = 1;




				// получаем статусы заказа
			$order_status_string = '';
			foreach (array_keys($this->order_status) as $key => $status) {
				$order_status_string .= (($key>0)?",":"")."'".$status."'";
			}
			// выбираем из базы только заказы (предзаказы не показываем)
			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$order_status_string.")";

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
					
					//////////////////////////
					//	тело строки заказа -- start ---
					//////////////////////////
						$table_order_row2_body = '<td class="show_hide" data-rowspan="'.$this->position_item.'"><span class="cabinett_row_hide_orders show"></span></td>';
						$table_order_row2_body .= '<td colspan="4" class="orders_info">';
							$table_order_row2_body .= $this->order_num_for_User.' <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>';
								// добавляем ссылку на клиента
								$table_order_row2_body .= '&nbsp;'.$this->get_client_name_link_Database($this->Order['client_id']);
							// номер счёта
							$table_order_row2_body .= '<span class="greyText">счёт№:'.$this->Order['number_pyament_list'].'</span>';
							// имя менеджера
							//$table_order_row2_body .= '&nbsp;<span class="greyText">менеджер: '.$this->get_name_employee_Database_Html($this->Order['manager_id']).'</span>';
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

			
			// возвращает html строки позиций
			private function table_order_positions_rows_Html(){			
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
					
					$html .= '<tr class="positions_rows row__'.$this->position_item.'" data-cab_dop_data_id="'.$this->id_dop_data.'" data-id="'.$position['id'].'">';
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
		
		//////////////////////////////////////////////////
		//   -----  END  -----  ORDERS  -----  END  -----
		//////////////////////////////////////////////////




		## На отгрузку
		Private Function for_shipping_Template(){
			global $mysqli;
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				`".CLIENTS_TBL."`.`company`,
				`".MANAGERS_TBL."`.`name`,
				`".MANAGERS_TBL."`.`last_name`,
				`".MANAGERS_TBL."`.`email` 
				FROM `".CAB_ORDER_ROWS."`
				INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
				INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
			// $query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Отгружен%' AND `".CAB_ORDER_ROWS."`.`global_status` NOT LIKE '%Аннулирован%'";
			$subsection = (isset($_GET['subsection']))?$_GET['subsection']:'';
			switch ($subsection) {
				case 'ready_for_shipment':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'ready_for_shipment'";
					break;
					
				case 'shipped':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'shipped'";
					break;
					

				default:
					# code...
					break;
			}
			//////////////////////////
			//	sorting
			//////////////////////////
			$query .= " ORDER BY `".CAB_ORDER_ROWS."`.`id` DESC";

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

			// выходим если нет заказов с таким статусом
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				if(!isset($value2)){continue;}
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
				// echo $query.'<br><br><br>';

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
				$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
				$html .= '<td colspan="11" class="each_art">';
				
				
				// ВЫВОД позиций
				$html .= '<table class="cab_position_div">';
				
				// шапка таблицы позиций заказа
				$html .= '<tr>
						<th>артикул</th>
						<th>номенклатура</th>
						<th  class="change_ttn_number">ТТН</th>
						<th>отгружено</th>
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
			// 		echo '<pre>';
			// print_r($main_rows);
			// echo '</pre>';
				foreach ($main_rows as $val1) {
					//ОБСЧЁТ ВАРИАНТОВ
					// получаем массив стоимости нанесения и доп услуг для данного варианта 
					$dop_usl = $CABINET -> get_order_dop_uslugi($val1['id_dop_data']);
					// выборка только массива стоимости печати
					$dop_usl_print = $CABINET->get_dop_uslugi_print_type($dop_usl);
					// выборка только массива стоимости доп услуг
					$dop_usl_no_print = $CABINET -> get_dop_uslugi_no_print_type($dop_usl);

					// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
					// стоимость печати варианта
					$calc_summ_dop_uslug = $CABINET -> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость доп услуг варианта
					$calc_summ_dop_uslug2 = $CABINET -> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
					// стоимость товара для варианта
					$price_out = $val1['price_out'] * $val1['quantity'];
					// стоимость варианта на выходе
					$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

					$html .= '<tr  data-id="'.$val1['id'].'">
					<td> <!--'.$val1['id_dop_data'].'|-->  '.$val1['art'].'</td>
					<td>'.$val1['name'].'</td>
					<td class="change_ttn_number"  contenteditable="true">'.$val1['ttn_number'].'</td>
					<td><span class="change_delivery_tir" contenteditable="true">'.$val1['delivery_tir'].'</span>шт.</td>
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
				$percent_payment = round($value['payment_status']*100/$in_out_summ,2);		
				// собираем строку заказа
				$html2 = '
						<tr data-id="'.$value['id'].'">
							<td class="cabinett_row_show show"><span></span></td>
							<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
							<td>'.$value['create_time'].'</td>
							<td>'.$value['company'].'</td>
							<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
							<td><span>'.$in_out_summ.'</span> р.</td>
							<td class="buch_status_select">'.$CABINET->select_status(2,$value['buch_status']).'</td>
							<td class="select_global_status">'.$CABINET->select_global_status($value['global_status']).'</td>
						</tr>
				';
				$html1 .= $html2 . $html;
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
								<th></th>
								<th>Статус заказа.</th>
							</tr>';
			echo $html1;
			echo '</table>';
		}
		## Закрытые
		Private Function closed_Template(){
			global $mysqli;
			//include ('./libs/php/classes/rt_class.php');

			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				`".CLIENTS_TBL."`.`company`,
				`".MANAGERS_TBL."`.`name`,
				`".MANAGERS_TBL."`.`last_name`,
				`".MANAGERS_TBL."`.`email` 
				FROM `".CAB_ORDER_ROWS."`
				INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`client_id`
				INNER JOIN `".MANAGERS_TBL."` ON `".MANAGERS_TBL."`.`id` = `".CAB_ORDER_ROWS."`.`manager_id`";
			$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Откружен' OR `".CAB_ORDER_ROWS."`.`buch_status` = 'огрузочные приняты (подписанные)'";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows_id[] = $row;
				}
			}

			// echo '<pre>';
			// print_r($zapros);
			// echo '</pre>';

			// собираем html строк-запросов
			$html1 = '';
			if(count($main_rows_id)==0){return 1;}

			foreach ($main_rows_id as $key => $value) {
				if(!isset($value2)){continue;}
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
					$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
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
					foreach ($main_rows as $val1) {
						//ОБСЧЁТ ВАРИАНТОВ
						// получаем массив стоимости нанесения и доп услуг для данного варианта 
						$dop_usl = $CABINET -> get_order_dop_uslugi($val1['id_dop_data']);
						// выборка только массива стоимости печати
						$dop_usl_print = $CABINET->get_dop_uslugi_print_type($dop_usl);
						// выборка только массива стоимости доп услуг
						$dop_usl_no_print = $CABINET -> get_dop_uslugi_no_print_type($dop_usl);

						// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
						// стоимость печати варианта
						$calc_summ_dop_uslug = $CABINET -> calc_summ_dop_uslug($dop_usl_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
						// стоимость доп услуг варианта
						$calc_summ_dop_uslug2 = $CABINET -> calc_summ_dop_uslug($dop_usl_no_print,(($val1['print_z']==1)?$val1['quantity']+$val1['zapas']:$val1['quantity']));
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
					$percent_payment = round($value['payment_status']*100/$in_out_summ,2);		
					// собираем строку заказа
					$html2 = '
							<tr data-id="'.$value['id'].'">
								<td class="cabinett_row_show show"><span></span></td>
								<td><a href="./?page=client_folder&section=order_tbl&order_num='.$order_num_1.'&order_id='.$value['id'].'&client_id='.$value['client_id'].'">'.$order_num_1.'</a></td>
								<td>'.$value['create_time'].'</td>
								<td>'.$value['company'].'</td>
								<td class="invoice_num" contenteditable="true">'.$value['invoice_num'].'</td>
								<td><input type="text" class="payment_date" readonly="readonly" value="'.$value['payment_date'].'"></td>
								<td class="number_payment_list" contenteditable="true">'.$value['number_pyament_list'].'</td>
								<td><span>'.$percent_payment.'</span> %</td>
								<td><span class="payment_status_span"  contenteditable="true">'.$value['payment_status'].'</span>р</td>
								<td><span>'.$in_out_summ.'</span> р.</td>
								<td class="buch_status_select">'.$CABINET->select_status(2,$value['buch_status']).'</td>
								<td class="select_global_status">'.$CABINET->select_global_status($value['global_status']).'</td>
							</tr>
					';
					$html1 .= $html2 . $html;
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
									<th></th>
									<th>Статус заказа.</th>
								</tr>';
				echo $html1;
				echo '</table>';
					// $message = 'important_Template';
					// $html = '';
					// other content template

					// $html .= $message;
					// return $html;this->
		}
		## Образцы
		Private Function simples_Template(){
			// $message = 'important_Template';
			// $html = '';
			// other content template

			// $html .= $message;
			// return $html;
		}

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
		
		

		private function get_client_name($id,$status){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';

			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					switch ($status) {
					case 'not_process':
						$name = '<div class="attach_the_client" data-id="'.$row['id'].'">'.$row['company'].'</div>';	
						break;
					case 'taken_into_operation':
						$name = '<div class="attach_the_client" data-id="'.$row['id'].'">'.$row['company'].'</div>';						
						break;					

					default:
						$name = '<div data-id="'.$row['id'].'">'.$row['company'].'</div>';					
						break;
				}
				}
			}else{
				$name = '<div class="attach_the_client" data-id="0">Прикрепить клиента</div>';
			}
			return $name;
		}



		function __destruct(){}
	}


?>