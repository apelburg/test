<?php
	
	class Cabinet_sklad_class extends Cabinet{
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

		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		// public $POSITION_NO_CATALOG;

		function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =)) 

			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $user_access;

			//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; left:0">this->Cabinet_snab_class </div>';
			
			// экземпляр класса продукции НЕ каталог
			// $this->POSITION_NO_CATALOG = new Position_no_catalog();


			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}

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



		
		//////////////////////////
		//	Section - Заказы
		//////////////////////////
		private function orders_Template($id_row=0){
			$where = 0;
			// скрываем левое меню
			$html = '';
			$table_head_html = '<style type="text/css" media="screen">
				#cabinet_left_coll_menu{display:none;}
			</style>';
			// $html = '';
			$table_head_html .= '
				<table id="general_panel_orders_tbl">
				<tr>
					<th colspan="3">Артикул/номенклатура/печать</th>
					<th>тираж</th>
					<th>логотип</th>
					<th>поставщик товара</th>
					<th>№ резерва</th>
					<th>подрядчик печати</th>
					<th>дата отгрузки</th>
					<th>статус товара</th>
					<th>статус заказа</th>
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
				/*
					<tr>
						<th colspan="3">Артикул/номенклатура/печать</th>
						<th>тираж</th>
						<th>логотип</th>
						<th>поставщик товара</th>
						<th>№ резерва</th>
						<th>подрядчик печати</th>
						<th>дата отгрузки</th>
						<th>статус товара</th>
						<th>статус заказа</th>
					</tr>
				*/
				$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
					$table_order_row .= '<td class="show_hide" rowspan="'.$this->position_item.'">
											<span class="cabinett_row_hide_orders"></span>
										</td>';
					$table_order_row .= '<td colspan="6" class="orders_info">
										<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>
										'.$this->get_client_name_link_Database($this->Order['client_id']).'
										<span class="greyText">,&nbsp;&nbsp;&nbsp;   Юр.лицо : в разработке</span>
										<span class="greyText">,&nbsp;&nbsp;&nbsp;   менеджер: '.$this->get_manager_name_Database_Html($this->Order['manager_id'],1).'</span>
										<span class="greyText">,&nbsp;&nbsp;&nbsp;   снабжение: '.$this->get_name_employee_Database_Html($this->Order['snab_id']).'</span>
									</td>';
					$table_order_row .= '<td>
										<!--// comments -->
										<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>	
									</td>';
					$table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
					$table_order_row .= '<td><span class="greyText">заказа: </span></td>';
					$table_order_row .= '<td>'.$this->order_status[$this->Order['global_status']].'</td>';
				$table_order_row .= '</tr>';
				// включаем вывод позиций 
				$table_order_row .= $table_order_positions_rows;
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
				// // порядковый номер позиции в заказе
				$html .= '<td><span class="orders_info_punct">'.$this->position_item.'п</span></td>';
				// // описание позиции
				$html .= '<td>';
				
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
				// тираж
				$html .= '<td>';
					$html .= '<div class="quantity">'.($position['quantity']+$position['zapas']).'</div>';
				$html .= '</td>';


				// логотип
				$html .= '<td><span class="greyText">  -  </span></td>';
				
				// поставщик товара  
				$html .= '<td>
							<div class="supplier">'.$this->get_supplier_name($position['art']).'</div>
						</td>';
				// № резерва
				$html .= '<td>
							<div class="number_rezerv">'.$position['number_rezerv'].'</div>
						</td>';

				// подрядчик печати
				$html .= '<td>
							<div>'.$position['suppliers_name'].'</div>
						</td>';

				// дата отгрузки
				$html .= '<td>
							<div>'.$this->Order['date_of_delivery_of_the_order'].'</div>
						</td>';

				// статус товара
				$html .= '<td>
							<div>'.$this->decoder_statuslist_sklad($position['status_sklad']).'</div>
						</td>';
				// статус снабжение
				$html .= '<td>
							<div>'.$this->decoder_statuslist_snab($position['status_snab'],$position['date_delivery_product']).'</div>
						</td>';





				// // подрядчк печати 
				// $html .= '<td class="change_supplier"  data-id="'.$position['suppliers_id'].'" data-id_dop_data="'.$position['id_dop_data'].'">'.$position['suppliers_name'].'</td>';
				// // сумма за позицию включая стоимость услуг 

				// $html .= '<td data-order_id="'.$this->Order['id'].'" data-id="'.$position['id'].'" data-order_num_user="'.$this->order_num_for_User.'" data-order_num="'.$this->Order['order_num'].'" data-cab_dop_data_id="'.$position['id_dop_data'].'" class="price_for_the_position">'.$this->Price_for_the_position.'</td>';
				// // всплывающее окно тех и доп инфо
				// // т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
				// // думаю есть смысл хранения в json 
				// // обязательные поля:
				// // {"comments":" ","technical_info":" ","maket":" "}
				// $html .= $this->grt_dop_teh_info($position);
				
				// // дата утверждения макета
				// $this->Position_approval_date = $position['approval_date'];
				// $html .= '<td><input type="text" class="approval_date" value="'.$this->Position_approval_date.'"></td>';

				// $html .= '<td><!--// срок по ДС по позиции --></td>';

				// // дата сдачи
				// // тут м.б. должна быть дата сдачи позиции ... но вроде как мы все позиции по умолчанию сдаём в срок по заказу, а если нет, то отгружаем частично по факту готовности, а следовательно нам нет необходимости вставлять для позиций редактируемое поле с датой сдачи
				// $html .= '<td><!--// дата сдачи по позиции --></td>';


				// // получаем статусы участников заказа в две колонки: отдел - статус
				// $html .= $this->position_status_list_Html($position);
				$html .= '</tr>';	

				// добавляем стоимость позиции к стоимости заказа
				$this->price_order += $this->Price_for_the_position;
				$this->position_item++;
			}				
			return $html;
		}		


		
		

		

		// статусы позиций
		private function position_status_list_Html($cab_order_main_row){
			
			if($this->Order['global_status'] == 'in_operation'){
				 return '<td><span class="greyText">Подразделения</span></td><td><span>Ожидают запуска заказа</span></td>';
			}else{				
				$buttons_service_start = '<input type="button" class="start_in_work" value="в работу">';
			}
			// получаем статусы по позиции
			// $status_list = array();
			// снабжение
			if(trim($cab_order_main_row['status_snab'])!=''){
				$this->Position_status_list['снабжение'][] = array( 'performer_status'=> $this->menu_name_arr[ $cab_order_main_row['status_snab'] ] , 'service_name' => '  ');	
			}else{
				$this->Position_status_list['снабжение'][] = array( 'performer_status'=>$cab_order_main_row[ 'status_snab' ] , 'service_name' => 'позиция');	
			}
			// склад
			if(trim($cab_order_main_row['status_sklad'])!=''){
				$this->Position_status_list['склад'][] = array('performer_status'=> $this->statuslist_sklad[ $cab_order_main_row['status_sklad'] ], 'service_name' => ' ');	
			}else{
				$this->Position_status_list['склад'][] = array('performer_status'=> 'ожидает товар', 'service_name' => 'позиция');	
			}

			
			// foreach ($this->Position_status_list as $key => $value) {
			// 	# code...
			// }

			// собираем вывод
			$html = '<td colspan="2"  class="orders_status_td_tbl">';
			$html .= '<table>';
			foreach ($this->Position_status_list as $performer => $performer_status_arr) {
				$html .= '<tr>';
				$html .= '<td>';
				$html .= '<div class="otdel_name">'.$performer.'</div>';
				$html .= '</td>';
				$html .= '<td>';

				foreach ($performer_status_arr as $key => $value) {
					$html .= '<div class="otdel_status">
								<div class="service_name">'.$value['service_name'].'</div>
								<div class="performer_status">'.(($value['performer_status']!='')?$value['performer_status']:$buttons_service_start).'</div>
							</div>';
									
				}

				$html .= '</td>';
				$html .= '</tr>';
			}						
			$html .= '</table>';
			$html .= '</td>';	
			// echo '<pre>';
			// print_r($this->Position_status_list);
			// echo '</pre>';
			return $html;
		
			
				

		}
		


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