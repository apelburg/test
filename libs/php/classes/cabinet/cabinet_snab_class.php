<?php
	
	class Cabinet_snab_class extends Cabinet{

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
		'otgrugen' => 'Отгруженные',
		// кнопки фильтрации заказа для СНАБ
		'get_in_work' => 'Принять',
		'my_orders' => 'Мои заказы',
		'only_get_in' => 'Только принятые',
		'expected_a_union' => 'Ожидает объединения'										
		); 

		//////////////////////////
		//	фильтры по разделам для кнопок подраздела
		//////////////////////////
		protected $filtres = array();

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
					// case 'history':
					// 	$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`status` = 'history'";
					// 	$where = 1;
					// 	break;
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
			$this->Requests_arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->Requests_arr[] = $row;
				}
			}

			$general_tbl_row = '';
			// собираем html строк-запросов 
			$html = '';
			foreach ($this->Requests_arr as $this->Request) {
				// получаем позиции по запросу
				$this->positions_arr = $this->requests_Template_recuestas_main_rows_Database($this->Request['query_num']);
				

				// если позиции отсутствуют - выходим из цикла (не показываем запрос)
				if(empty($this->positions_arr)){ continue;}
				//////////////////////////
				//	open_close   -- start
				//////////////////////////
					// получаем флаг открыт/закрыто
					$this->open__close = $this->get_open_close_for_this_user($this->Request['open_close']);
					
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
				foreach ($this->positions_arr as $this->position) {
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
					$this->GET_PRICE_for_position($this->position);				
					
					////////////////////////////////////
					//	Расчёт стоимости позиций END
					////////////////////////////////////
					
					
					//////////////////////////
					//	собираем строки вариантов по каждой позиции
					//////////////////////////
					// 
					if($name_product != $this->position['name']){$name_product = $this->position['name']; $name_count = 1;}
					$variant_row .= '<tr data-id_dop_data="'.$this->position['id_dop_data'].'" class="'.$this->position['type'].'_1">
						<td>'.$this->position['art'].'</td>
						<td><a class="go_to_position_card_link" href="./?page=client_folder&section=rt_position&client_id='.$this->Request['client_id'].'&id='.$this->position['id'].'">'.$this->position['name'].'</a> <span class="variant_comments_dop">( Вариант '.$name_count++.' )</span></td>
						<td>'.$this->position['quantity'].'</td>
						<td></td>
						<td>'.$this->Price_for_the_goods.'</td>
						<td>'.$this->Price_of_printing.'</td>
						<td>'.$this->Price_of_no_printing.'</td>
						<td>'.$this->Price_for_the_position.'</td>
						<td></td>
						<td data-type="'.$this->position['type'].'" data-status="'.$this->position['status_snab'].'" class="'.$this->position['status_snab'].'_'.$this->user_access.' '.$this->Request['status'].'_status_snab_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($this->position['status_snab']).'</td>
					</tr>';
				}

				
				//////////////////////////////////////
				//	собираем строку запроса  -- start
				//////////////////////////////////////
					$status_or_button = (isset($this->name_cirillic_status[$this->Request['status']])?$this->name_cirillic_status[$this->Request['status']]:'статус не предусмотрен!!!!'.$this->Request['status']);

					// если в массиве $_POST содержится значение, значит мы запрашиваем только одну строку и подставляем значение из массива
					$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				
					// раскрыть / свернуть
					$general_tbl_row_body ='<td class="show_hide" '.$this->open_close_rowspan.'="'.$rowspan.'"><span class="cabinett_row_hide'.$this->open_close_class.'"></span></td>';
						
					// номер запроса
					$general_tbl_row_body .='<td><a href="./?page=client_folder&client_id='.$this->Request['client_id'].'&query_num='.$this->Request['query_num'].'">'.$this->Request['query_num'].'</a> </td>';
						
					// имя прикреплённого менеджера
					$general_tbl_row_body .='<td>'.$this->get_manager_name_Database_Html($this->Request['manager_id'],1).'</td>';
						
					// дата заведения запроса (запрос от клиента)
					$general_tbl_row_body .='<td>'.$this->Request['create_time'].'</td>';
						
					// комменты по запросу
					$general_tbl_row_body .='<td><span data-rt_list_query_num="'.$this->Request['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($this->Request['query_num']).'"></span></td>';
						
					// компания
					$general_tbl_row_body .='<td>'.$this->get_client_name_Database($this->Request['client_id'],1).'</td>';
						
					// сумма запроса
					$general_tbl_row_body .='<td>'.RT::calcualte_query_summ($this->Request['query_num']).'</td>';
						
					// статус запроса
					$general_tbl_row_body .='<td class="'.$this->Request['status'].'_'.$this->user_access.'">'.$status_or_button.'</td>';
				
					// если запрос по строке, возвращаем строку
					if($id_row!=0){return $general_tbl_row_body;}

				//////////////////////////////////////
				//	собираем строку запроса  -- end
				//////////////////////////////////////

				$general_tbl_row .= '<tr data-id="'.$this->Request['id'].'" id="rt_list_id_'.$this->Request['id'].'">';
					$general_tbl_row .= $general_tbl_row_body;
				$general_tbl_row .= '</tr>';
				
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
		protected function requests_Template_recuestas_main_rows_Database($id){
			// ФИЛЬТРАЦИЯ ПО ВЕРХНЕМУ МЕНЮ 

			$dop_data_tbl = RT_DOP_DATA;
			switch ($_GET['subsection']) {
				case 'all':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' ";
					break;
				case 'no_worcked_snab':
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".$dop_data_tbl."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab')";
					break;
				case 'history':
					$dop_data_tbl = DOP_DATA_HIST;
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".$dop_data_tbl."`.`status_snab` LIKE '%Расчёт от%')";
					break;
				case 'in_work':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".$dop_data_tbl."`.`status_snab` = 'in_calculation'";
					break;
				case 'denied':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".$dop_data_tbl."`.`status_snab` = 'tz_is_not_correct'";
					break;

				case 'paused':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".$dop_data_tbl."`.`status_snab` LIKE '%pause%'";
					break;

				case 'calk_snab':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".$dop_data_tbl."`.`status_snab` LIKE 'calculate_is_ready'";
					break;

				default:
					$where = "WHERE `".$dop_data_tbl."`.`row_status` NOT LIKE 'red' AND `".RT_MAIN_ROWS."`.`query_num` = '".$id."' ";
					break;
			}

			global $mysqli;
			$query = "
				SELECT 
					`".$dop_data_tbl."`.`id` AS `id_dop_data`,
					`".$dop_data_tbl."`.`quantity`,	
					`".$dop_data_tbl."`.`price_out`,		
					`".$dop_data_tbl."`.`print_z`,	
					`".$dop_data_tbl."`.`zapas`,	
					`".$dop_data_tbl."`.`status_snab`,	
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".RT_MAIN_ROWS."`.*,
					`".RT_LIST."`.`id` AS `request_id` 
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".$dop_data_tbl."` ON `".$dop_data_tbl."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					LEFT JOIN `".RT_LIST."` ON `".RT_LIST."`.`id` = `".RT_MAIN_ROWS."`.`query_num`
					".$where."
					ORDER BY `".RT_MAIN_ROWS."`.`type` DESC";
				// echo  $query.'<br><br>';
			$positions_arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$positions_arr[] = $row;
				}
			}
			return $positions_arr;
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

		
		// возвращает html строки позиций
		private function table_order_positions_rows_Html(){			
			/////////////////////////////
			//	фильтр позиций  -- start
			/////////////////////////////
				// готов к отгрузке
				$this->filter_position = (isset($_GET['subsection']) && $_GET['subsection']=='ready_for_shipment')?" AND `".CAB_ORDER_MAIN."`.`status_snab` = 'ready_for_shipment'":"";
				// готов к отгрузке
				$this->filter_position = (isset($_GET['subsection']) && $_GET['subsection']=='otgrugen')?" AND `".CAB_ORDER_MAIN."`.`status_sklad` = 'goods_shipped_for_client'":"";
			/////////////////////////////
			//	фильтр позиций  -- end
			/////////////////////////////
			// получаем позиции по заказу
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
				
				$html .= '<tr class="positions_rows row__'.$this->position_item.'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>';
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
				
				$table_order_row2_body = '<td class="show_hide" data-rowspan="'.$this->position_item.'"><span class="cabinett_row_hide_orders show"></span></td>
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
		private function for_shipping_Template_OLD/*!!!!!*/(){
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
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'for_shipping'";
					break;
					
				case 'otgrugen':
					# code...Приостановлен
					$query .=" WHERE `".CAB_ORDER_ROWS."`.`global_status` = 'Отгружен'";
					break;
					

				default:
					# code...
					break;
			}
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
				foreach ($main_rows as $key1 => $val1) {
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
		private function closed_Template(){
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
					foreach ($main_rows as $key1 => $val1) {
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

		
		

		function __destruct(){}
	}


?>