<?php
	
	class Cabinet_buch_class extends Cabinet{

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
		'requested_the_bill' => 'Счёт запрошен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'tz_no_correct' => 'ТЗ не корректно',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'на паузе',
		'history' => 'История',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		// 'otgrugen' => 'Отгруженные',
		'partially_shipped' => 'Частично отгружен',
		'already_shipped' => 'Отгруженные',
		'partially_shipped' => 'Частично',
		'fully_shipped' => 'Полностью',
		'requested_the_bill' => 'Счёт запрошен',
		'the_order_is_create' => 'Заказ сформирован',
		'payment_the_bill' => 'Счёт оплачен',	
		'refund_in_a_row' => 'возврат средств по счёту',
		'cancelled' => 'Счёт аннулирован',
		'all_the_bill' => 'Все счёта'										
		); 

		// название подраздела кабинета
		private $sub_subsection;

		

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
		//	Section - Предзаказ
		//////////////////////////
		// protected function paperwork_create_spec_Template($id_row=0){
		// 	$where = 0;
		// 	global $mysqli;
			
		// 	// простой запрос
		// 	$array_request = array();

			
		// 	$query = "SELECT 
		// 		`".CAB_ORDER_ROWS."`.*, 
		// 		DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		// 		FROM `".CAB_ORDER_ROWS."`";
			
		// 	if($id_row){
		// 		$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
		// 		$where = 1;
		// 	}else{
		// 		// получаем статусы предзаказа
		// 		$paperwork_status_string = '';
		// 		foreach (array_keys($this->paperwork_status) as $key => $status) {
		// 			$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
		// 		}
		// 		// выбираем из базы только предзаказы (заказы не показываем)
		// 		$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$paperwork_status_string.")";
		// 		$where = 1;
		// 	}


			
		// 	//////////////////////////
		// 	//	sorting
		// 	//////////////////////////
		// 	$query .= ' ORDER BY `id` DESC';
		// 	// echo $query;
		// 	$result = $mysqli->query($query) or die($mysqli->error);
		// 	$this->Order_arr = array();
			
		// 	if($result->num_rows > 0){
		// 		while($row = $result->fetch_assoc()){
		// 			$this->Order_arr[] = $row;
		// 		}
		// 	}


			
			
		// 	// собираем html строк-запросов
		// 	$html1 = '';
		// 	if(count($this->Order_arr)==0){return 1;}

		// 	foreach ($this->Order_arr as $key => $this->Order) {
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
		// 		// $table_order_positions_rows = $this->table_order_positions_rows_Html();

		// 		// // если нет позиций заказа, не
		// 		// if($table_order_positions_rows == ""){continue;}

				
		// 		$this->invoice_num = $this->Order['invoice_num'];


		// 		$query = "
		// 		SELECT 
		// 			`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
		// 			`".CAB_ORDER_DOP_DATA."`.`quantity`,	
		// 			`".CAB_ORDER_DOP_DATA."`.`price_out`,	
		// 			`".CAB_ORDER_DOP_DATA."`.`print_z`,	
		// 			`".CAB_ORDER_DOP_DATA."`.`zapas`,	
		// 			DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
		// 			`".CAB_ORDER_MAIN."`.*,
		// 			`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
		// 			`".CAB_ORDER_ROWS."`.`global_status`,
		// 			`".CAB_ORDER_ROWS."`.`payment_status`,
		// 			`".CAB_ORDER_ROWS."`.`number_pyament_list`
		// 			FROM `".CAB_ORDER_MAIN."` 
		// 			INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
		// 			LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
		// 			WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$this->Order['id']."'
		// 			ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
		// 		";

		// 		$position_arr = array();
		// 		$result = $mysqli->query($query) or die($mysqli->error);
		// 		$position_arr_id = array();
		// 		if($result->num_rows > 0){
		// 			while($row = $result->fetch_assoc()){
		// 				$position_arr[] = $row;
		// 			}
		// 		}

		// 		// СОБИРАЕМ ТАБЛИЦУ
		// 		###############################
		// 		// строка с артикулами START
		// 		###############################
		// 		$html = '<tr class="query_detail" '.$this->open_close_tr_style.'>';
		// 		//$html .= '<td class="show_hide"><span class="this->cabinett_row_hide"></span></td>';
		// 		$html .= '<td colspan="12" class="each_art">';
				
				
		// 		// ВЫВОД позиций
		// 		$html .= '<table class="cab_position_div">';
				
		// 		// шапка таблицы позиций заказа
		// 		$html .= '<tr>
		// 				<th>артикул</th>
		// 				<th>номенклатура</th>
		// 				<th>тираж</th>
		// 				<th>цены:</th>
		// 				<th>товар</th>
		// 				<th>печать</th>
		// 				<th>доп. услуги</th>
		// 			<th>в общем</th>
		// 			<th></th>
		// 			<th></th>
		// 				</tr>';


		// 		$this->Price_of_position = 0; // общая стоимость заказа
		// 		foreach ($position_arr as $key1 => $this->position) {
		// 			////////////////////////////////////
		// 			//	Расчёт стоимости позиций START  
		// 			////////////////////////////////////
		// 			/*
		// 				!!!!!!!!    ОПИСАНИЕ    !!!!!!!!!

		// 				стоимость товара
		// 				$this->Price_for_the_goods;
		// 				стоимость услуг печати
		// 				$this->Price_of_printing;
		// 				стоимость услуг не относящихся к печати
		// 				$this->Price_of_no_printing;
		// 				общаяя цена позиции включает в себя стоимость услуг и товара
		// 				$this->Price_for_the_position;
		// 			*/
		// 			$this->GET_PRICE_for_position($this->position);				
					
		// 			////////////////////////////////////
		// 			//	Расчёт стоимости позиций END
		// 			////////////////////////////////////
					
					
		// 			//////////////////////////
		// 			//	собираем строки вариантов по каждой позиции
		// 			//////////////////////////

		// 			$html .= '<tr  data-id="'.$this->Order['id'].'">
		// 			<td><!--'.$this->position['id_dop_data'].'|-->  '.$this->position['art'].'</td>
		// 			<td>'.$this->position['name'].'</td>
		// 			<td>'.($this->position['quantity']+$this->position['zapas']).'</td>
		// 			<td></td>
		// 			<td><span>'.$this->Price_for_the_goods.'</span> р.</td>
		// 			<td><span>'.$this->Price_of_printing.'</span> р.</td>
		// 			<td><span>'.$this->Price_of_no_printing.'</span> р.</td>
		// 			<td><span>'.$this->Price_for_the_position.'</span> р.</td>
		// 			<td></td>
		// 			<td></td>
		// 					</tr>';
		// 			$this->Price_of_position += $this->Price_for_the_position; // прибавим к общей стоимости
		// 		}

		// 		$html .= '</table>';
		// 		$html .= '</td>';
		// 		$html .= '</tr>';
		// 		###############################
		// 		// строка с артикулами END
		// 		###############################

		// 		// получаем % оплаты
		// 		$percent_payment = $this->calculation_percent_of_payment($this->Price_of_position,$this->Order['payment_status']);
		// 		// $percent_payment = ($this->Price_of_position!=0)?round($this->Order['payment_status']*100/$this->Price_of_position,2):'0.00';		
		// 		// собираем строку заказа
				
		// 		$html2 = '<tr data-id="'.$this->Order['id'].'" >';
		// 		$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
		// 		//'.$this->get_manager_name_Database_Html($this->Order['manager_id']).'
		// 		$html2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$rowspan.'"><span class="cabinett_row_hide'.$this->open_close_class.'"></span></td>
		// 					<td><a href="./?page=client_folder&section=order_tbl&order_num='.$this->order_num_for_User.'&order_id='.$this->Order['id'].'&client_id='.$this->Order['client_id'].'">'.$this->order_num_for_User.'</a></td>
		// 					<td>'.$this->Order['create_time'].'<br>'.$this->get_manager_name_Database_Html($this->Order['manager_id'],1).'</td>
		// 					<td>'.$this->get_client_name_Database($this->Order['client_id'],1).'</td>
		// 					<td class="buh_uchet"></td>
		// 					<td class="invoice_num" ></td>
		// 					<td><input type="text" class="payment_date" readonly="readonly" value="'.$this->Order['payment_date'].'"></td>
		// 					<td class="number_payment_list" contenteditable="true">'.$this->Order['number_pyament_list'].'</td>
		// 					<td><span>'.$percent_payment.'</span> %</td>
		// 					<td><span class="payment_status_span edit_span"  contenteditable="true">'.$this->Order['payment_status'].'</span>р</td>
		// 					<td><span>'.$this->Price_of_position.'</span> р.</td>
		// 					<td class="buch_status_select">'.$this->decoder_statuslist_buch($this->Order['buch_status']).'</td>
		// 					<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
		// 		$html3 = '</tr>';

		// 		$html1 .= $html2 .$html2_body.$html3. $html;
		// 		// запрос по одной строке без подробностей
		// 		if($id_row){return $html2_body;}
		// 	}

			


		// 	echo '
		// 	<table class="cabinet_general_content_row">
		// 					<tr>
		// 						<th id="show_allArt"></th>
		// 						<th>Номер</th>
		// 						<th>Дата/время заведения</th>
		// 						<th>Компания</th>						
		// 						<th class="buh_uchet">Бух. Уч.</th>
		// 						<th>Счёт</th>
		// 						<th>Дата опл-ты</th>
		// 						<th>№ платёжки</th>
		// 						<th>% оплаты</th>
		// 						<th>Оплачено</th>
		// 						<th>стоимость заказа</th>
		// 						<th>статус БУХ</th>
		// 						<th>Статус заказа.</th>
		// 					</tr>';
		// 	echo $html1;
		// 	echo '</table>';
		// }
		// protected function paperwork_Template($id_row=0){
		// 	/*
		// 		т.к. фильты по предзаказу для БУХ подразумевают различные выгрузки - осущевствляем 
		// 		роутинг по subsection
		// 	*/
		// 	$method_template = $_GET['section'].'_'.$_GET['subsection'].'_Template';
		// 	// $method_template = $_GET['section'].'_Template';
		// 	echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

		// 	// если в этом классе существует такой метод - выполняем его
		// 	if(method_exists($this, $method_template)){
		// 		$this->$method_template();				
		// 	}else{
		// 		// обработка ответа о неправильном адресе
		// 		echo 'Фильтр не сущевствует';
		// 	}
		// }

		


		//////////////////////////
		//	Section - Заказы
		//////////////////////////
		protected function orders_Template($id_row=0){
			echo $this->wrap_text_in_warning_message('Привет мир !');
		}

		//////////////////////////
		//	Section - На отгрузку
		//////////////////////////
		protected function for_shipping_Template($id_row=0){
			echo $this->wrap_text_in_warning_message('Привет мир !');
		}

		//////////////////////////
		//	Section - Отгруженные
		//////////////////////////
		protected function already_shipped_Template($id_row=0){
			echo $this->wrap_text_in_warning_message('Привет мир !');
		}

		//////////////////////////
		//	Section - Закрытые
		//////////////////////////
		protected function closed_Template($id_row=0){
			echo $this->wrap_text_in_warning_message('Привет мир !');
		}

		//////////////////////////
		//	Section - История
		//////////////////////////
		protected function history_Template($id_row=0){
			echo $this->wrap_text_in_warning_message('Привет мир !');
		}

		#############################################################
		##      методы для работы с поддиректориями subsection     ##
		##                           END                           ##
		#############################################################

		function __destruct(){}
}


?>