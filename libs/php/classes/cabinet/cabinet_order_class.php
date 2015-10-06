<?php

	/**
	 * Класс предназначен для обработки части кабинета относящейся к section = "Заказы"
	 * все AJAX методы лежат в унаследованном классе Cabinet
	 *
	 * @version     	1.001, 2015-09-21
	 * @author      	Alexey Kapitonov
	 * @since       	1.0
	 *
	*/

	class Order extends Cabinet{
		/** 
	     * Class constructor.
	     */
		function __construct($id_row = 0,$user_access,$user_id){
		// echo 'Hellow World =)';	
			$this->user_id = $user_id;
			$this->user_access = $user_access;	
			// echo 'привет мир';
			$method_template = $_GET['section'].'_'.$_GET['subsection'].'_Template';
			// $method_template = $_GET['section'].'_Template';
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				// echo $this->$method_template;
				$this->$method_template($id_row);				
			}else{
				// обработка ответа о неправильном адресе
				echo 'фильтр не найден';
			}
    	}


    	//////////////////////////
		//	Section - Заказы  -- start
		//////////////////////////			
    		////////////////////////////////////////////////////////////
			//	методы запроса данных из базы для заказа и предзаказа
			///////////////////////////////////////////////////////////

				// запрос спецификаций для заказа
				private function get_the_specificate_order_Database($id_row=0){
					$where = 0;
					global $mysqli;


					$query = "SELECT 
						`".CAB_BILL_AND_SPEC_TBL."`.*, 
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y ')  AS `create_time`,
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`payment_date`,'%d.%m.%Y ')  AS `payment_date`,
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_order_the_bill`,'%d.%m.%Y')  AS `date_order_the_bill`
						FROM `".CAB_BILL_AND_SPEC_TBL."`";
					
					if($id_row){
						//////////////////////////
						//	выборка одной строки 	
						//////////////////////////	
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`id` = '".$id_row."'";
							$where = 1;
					}else{
						/////////////////////////////////////////////////////////////////
						// выбираем из базы только предзаказы (заказы не показываем)
						/////////////////////////////////////////////////////////////////
							// получаем статусы предзаказа
							$paperwork_status_string = '';
							foreach (array_keys($this->order_status) as $key => $status) {
								$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
							}
							foreach (array_keys($this->order_service_status) as $key => $status) {
								$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
							}
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`global_status` IN (".$paperwork_status_string.")";
							$where = 1;
						//////////////////////////
						//	выборка по клиенту
						//////////////////////////
						if(isset($_GET['client_id'])){
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`client_id` = '".$_GET['client_id']."'";
							$where = 1;
						}


						// выборка по статусу
						if(isset($_GET['subsection'])){
							switch ($_GET['subsection']) {
								case 'create_spec': // спецификация создана
									$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'is_pending'";
									break;
								case 'requested_the_bill': // запрошен счёт
									$paperwork_status_string = '';
									foreach (array_keys($this->buch_status_service) as $key => $status) {
										$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
									}
									$query .= " ".(($where)?'AND':'WHERE')." (`".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'request_expense' OR `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'reget_the_bill')";
									break;
								case 'expense': // счёт выставлен
									$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'score_exhibited'";
									break;
								case 'payment_the_bill': // счёт оплачен
									$query .= " ".(($where)?'AND':'WHERE')." (`".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'payment' OR `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'collateral_received' OR `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'partially_paid') ";
									break;
								case 'cancelled': // счёт аннулирован
									$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'cancelled' ";
									break;

								case 'refund_in_a_row': // возврат денег по счёту
									$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'refund_in_a_row'";
									break;
								
								
								default:
									# code...
									break;

									$where = 1;
							}

							// фильтрация спецификаций(счётов) по менеджеру
							if(isset($_GET['manager_id'])){
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`manager_id` = '".(int)$_GET['manager_id']."'";
								$where = 1;
							}
							// фильтрация по менеджеру
							if($this->user_access == 5){
								if(isset($_GET['subsection']) && $_GET['subsection'] != 'production'){
									$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`manager_id` = '".$this->user_id."'";
									$where = 1;
								}								
							}
							
							
						}
					}

					
					
					
					//////////////////////////
					//	sorting
					//////////////////////////
					$query .= ' ORDER BY `id` DESC';
					// echo $query;
					$result = $mysqli->query($query) or die($mysqli->error);
					$this->Specificate_arr = array();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->Specificate_arr[] = $row;
						}
					}
				}

				// // получаем позиции к спецификациям
				// private function get_the_position_with_specificate_Database($id){
				// 	global $mysqli;
				// 	$query = "
				// 		SELECT 
				// 			`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
				// 			`".CAB_ORDER_DOP_DATA."`.`quantity`,	
				// 			`".CAB_ORDER_DOP_DATA."`.`price_out`,	
				// 			`".CAB_ORDER_DOP_DATA."`.`print_z`,	
				// 			`".CAB_ORDER_DOP_DATA."`.`zapas`,	
				// 			DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
				// 			`".CAB_ORDER_MAIN."`.*
				// 			FROM `".CAB_ORDER_MAIN."` 
				// 			INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
				// 			WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`the_bill_id` = '".$id."'";
						
				// 	$query .= " ORDER BY `".CAB_ORDER_MAIN."`.`id` DESC";
				// 		// echo $query;

				// 	$result = $mysqli->query($query) or die($mysqli->error);
				// 	$position_arr = array();
				// 	if($result->num_rows > 0){
				// 		while($row = $result->fetch_assoc()){
				// 			$position_arr[] = $row;
				// 		}
				// 	}
				// 	return $position_arr;
				// }
			
			//////////////////////////
			//	заказ создан
			//////////////////////////

				// получаем HTML спецификации
				private function table_specificate_for_order_Html(){
					$this->spec_arr = $this->table_specificate_for_order_Database($this->Order['id']);

					$html = '';
					$this->rows_num = 0;// порядковый номер строки
					$this->position_num_in_order = 0; // сколько позиций в заказе
					$this->position_num = 1;// порядковый номер позиции
					$this->specificate_item = 0;// порядковый номер спецификации

					// обход массива спецификаций
					foreach ($this->spec_arr as $key => $this->specificate) {
						// стоимость по спецификации (НАЧАЛЬНАЯ)
						$this->price_specificate = 0; 

						// подсчет номер спецификаций
						$this->specificate_item++;

						// вывод html строк позиций по спецификации 
						// запрашивается раньше спец-ии, чтобы подсчитать её стоимость
						$positions_rows = $this->table_order_positions_rows_Html();

						// получаем html строку со спецификацией
						$html .= $this->get_order_specificate_Html_Template();

						

						// // если хранящаяся в базу стоимость 
						// // не совпадает со стоимостью которая была рассчитана - перезаписываем её на правильную 
						// if ($this->price_specificate != $this->specificate['spec_price']) {
						// 	$this->save_price_specificate_Database($this->specificate['id'],$this->price_specificate);
						// }

						// подсчёт стоимости заказа
						$this->price_order += $this->price_specificate;

						// строки позиций идут под спецификацией
						$html .= $positions_rows;
												
					}
					
					return $html;
				}

				//////////////////////////
				//	save
				//////////////////////////
					/*
					// сохранение стоимости спецификации
					private function save_price_specificate_Database($id,$price_specificate){
						global $mysqli;
						$query ="UPDATE `".CAB_BILL_AND_SPEC_TBL."` SET 
						`spec_price` = '".$price_specificate."'";
						$query .= " WHERE `id` = '".$id."'";

						$result = $mysqli->query($query) or die($mysqli->error);
						return; 
					}

					// сохраняем кол-во позиций в спец-ии
					private function save_number_of_positions_in_specificate_row_Database($id,$number_of_positions){
						global $mysqli;
						$query ="UPDATE `".CAB_ORDER_ROWS."` SET 
						`number_of_positions` = '".$number_of_positions."'";
						$query .= " WHERE `id` = '".$id."'";

						$result = $mysqli->query($query) or die($mysqli->error);
						return;
					}

					// сохранение порядкового номера позиции
					private function save_sequence_number_of_position_Database($id,$sequence_number){
						global $mysqli;
						$query ="UPDATE `".CAB_ORDER_MAIN."` SET 
						`sequence_number` = '".$sequence_number."'";
						$query .= " WHERE `id` = '".$id."'";

						$result = $mysqli->query($query) or die($mysqli->error);
						return;
					}
					*/

				// ШАБЛОН строки спецификации
				private function get_order_specificate_Html_Template(){
					$this->rows_num++;
					$html = '';
					$html .= '<tr  class="specificate_rows" '.$this->open_close_tr_style.' data-id="'.$this->specificate['id'].'">';
						$html .= '<td colspan="4">';
							// спецификация
							$html .= 'Спецификация '.$this->specificate_item;
							// ссылка на спецификацию
							$html .= '&nbsp; '.$this->get_specification_link($this->specificate,$this->specificate['client_id'],$this->specificate['create_time']);
							// номер запроса
							$html .= '&nbsp;<span class="greyText"> (<a href="?page=client_folder&client_id='.$this->specificate['client_id'].'&query_num='.$this->specificate['query_num'].'" target="_blank" class="greyText">Запрос №: '.$this->specificate['query_num'].'</a>)</span>';
							// снабжение
							$html .= '&nbsp; <span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->specificate['snab_id'],8).'</span>';

						$html .='</td>';
						$html .= '<td>';
							$html .= 'сч: '.$this->specificate['number_the_bill'];
						$html .= '</td>';
						$html .= '<td>';
							$html .= '<span>'.$this->price_specificate.'</span>р';
						$html .= '</td>';
						$html .= '<td>';
							// % оплаты
							$html .= '<span class="greyText">оплачено: </span> '.$this->calculation_percent_of_payment($this->price_specificate, $this->specificate['payment_status']).' %';

						$html .= '</td>';
						$html .= '<td>';
						$html .= '</td>';
						$html .= '<td contenteditable="true" class="deadline">'.$this->specificate['deadline'].'</td>';
						$html .= '<td>';
							$html .= '<input type="text" name="date_of_delivery_of_the_specificate" class="date_of_delivery_of_the_specificate" value="'.$this->specificate['date_of_delivery'].'" data-id="'.$this->specificate['id'].'">';
						$html .= '</td>';
						$html .= '<td>Бух.</td>';
						$html .= '<td class="buch_status_select">'.$this->decoder_statuslist_buch($this->specificate['buch_status']).'</td>';
					$html .= '</tr>';
					return $html;
				}

				// вывод позиций по заказу
				private function table_order_positions_rows_Html(){    
					// получаем массив позиций заказа
					$positions_rows = $this->positions_rows_Database($this->specificate['id']);
					$this->number_of_positions = count($positions_rows);
					$this->position_num_in_order += $this->number_of_positions;
					$html = '';    

					$this->position_item = 1;// порядковый номер позиции
					foreach ($positions_rows as $key => $this->position) {
						$this->rows_num++;// номер строки в таблице

						// // если записываем порядковый номер позиции, если он ещё не присвоен
						// if($this->position['sequence_number'] == 0){
						// 	$this->save_sequence_number_of_position_Database($this->position['id'],$this->position_num);
						// 	$this->position['sequence_number'] = $this->position_num;
						// }

						$this->Position_status_list = array(); // в переменную заложим все статусы

						$this->id_dop_data = $this->position['id_dop_data'];
						////////////////////////////////////
						//   Расчёт стоимости позиций START  
						////////////////////////////////////                             
							  
							$this->GET_PRICE_for_position($this->position);                   
								   
						////////////////////////////////////
						//   Расчёт стоимости позиций END
						////////////////////////////////////              
							  
						$html .= $this->get_order_specificate_position_Html_Template();  

						// добавляем стоимость позиции к стоимости заказа
						$this->price_specificate += $this->Price_for_the_position;
						$this->position_item++;
						$this->position_num++;

					}
					return $html;
				}


				// ШАБЛОН вывода позиций для заказа со спецификацией
				private function get_order_specificate_position_Html_Template(){
					$html = '';
					$html .= '<tr class="position-row position-row-production" id="position_row_'.$this->position['sequence_number'].'" data-cab_dop_data_id="'.$this->id_dop_data.'" data-id="'.$this->position['id'].'" '.$this->open_close_tr_style.'>';
					// порядковый номер позиции в заказе
					
						
					$html .= '<td><span class="orders_info_punct">'.$this->position['sequence_number'].'п<br>('.$this->Order['number_of_positions'].')</span></td>';
					// описание позиции
					$html .= '<td>';
					// комментарии
					// наименование товара
					$html .= '<span class="art_and_name">'.$this->position['art'].'  '.$this->position['name'].'</span>';
								   
					// добавляем доп описание
					// для каталога и НЕкаталога способы хранения и получения данной информации различны
					if(trim($this->position['type'])!='cat' && trim($this->position['type'])!=''){
						// доп инфо по некаталогу берём из json 
						$html .= $this->decode_json_no_cat_to_html($this->position);
					}else if(trim($this->position['type'])!=''){
						// доп инфо по каталогу из услуг..... НУЖНО РЕАЛИЗОВЫВАТЬ
						$html .= '';
					}


					$html .= '</td>';
					// тираж, запас, печатать/непечатать запас
					$html .= '<td>';
					$html .= '<div class="quantity">'.$this->position['quantity'].'</div>';
					$html .= '<div class="zapas">'.(($this->position['zapas']!=0 && trim($this->position['zapas'])!='')?'+'.$this->position['zapas']:'').'</div>';
					$html .= '<div class="print_z">'.(($this->position['zapas']!=0 && trim($this->position['zapas'])!='')?(($this->position['print_z']==0)?'НПЗ':'ПЗ'):'').'</div>';
					$html .= '</td>';
							
					// поставщик товара и номер резерва для каталожной продукции 
					$html .= '<td>
							<div class="supplier">'.$this->get_supplier_name($this->position['art']).'</div>
							<div class="number_rezerv">'.base64_decode($this->position['number_rezerv']).'</div>
							</td>';
					// подрядчк печати 
					$html .= '<td class="change_supplier"  data-id="'.$this->position['suppliers_id'].'" data-id_dop_data="'.$this->position['id_dop_data'].'">'.$this->position['suppliers_name'].'</td>';
					// сумма за позицию включая стоимость услуг 

					$html .= '<td data-order_id="'.$this->Order['id'].'" data-id="'.$this->position['id'].'" data-order_num_user="'.$this->order_num_for_User.'" data-order_num="'.$this->Order['order_num'].'" data-specificate_id="'.$this->specificate['id'].'" data-cab_dop_data_id="'.$this->position['id_dop_data'].'" class="price_for_the_position">'.$this->Price_for_the_position.'</td>';
					// всплывающее окно тех и доп инфо
					// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
					// думаю есть смысл хранения в json 
					// обязательные поля:
					// {"comments":" ","technical_info":" ","maket":" "}
					$html .= $this->grt_dop_teh_info($this->position);
							  
					// дата утверждения макета
					$html .= '<td>';
						$html .= $this->get_Position_approval_date( $this->Position_approval_date = $this->position['approval_date'], $this->position['id'] );
					$html .= '</td>';

					$html .= '<td><!--// срок по ДС по позиции --></td>';

					// дата сдачи
						 // тут м.б. должна быть дата сдачи позиции ... но вроде как мы все позиции по умолчанию сдаём в срок по заказу, а если нет, то отгружаем частично по факту готовности, а следовательно нам нет необходимости вставлять для позиций редактируемое поле с датой сдачи
					$html .= '<td><!--// дата сдачи по позиции --></td>';


					// получаем статусы участников заказа в две колонки: отдел - статус
					$html .= $this->position_status_list_Html($this->position);
					$html .= '</tr>'; 
					return $html;
				}	

				// получаем спецификации к заказу
				private function table_specificate_for_order_Database($id){
					global $mysqli;
					$query = "SELECT *,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_of_delivery`,'%d.%m.%Y %H:%i:%s')  AS `date_of_delivery` FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_id` = '".$id."'";
					// $where = 1;
					$result = $mysqli->query($query) or die($mysqli->error);
					$spec_arr = array();
					// if(isset($_GET['order_num'])){
					// 	$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`order_num` = '".(int)$_GET['order_num']."'";
					// 	$where = 1;
					// }
					// if(isset($_GET['manager_id'])){
					// 	$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`manager_id` = '".(int)$_GET['manager_id']."'";
					// 	$where = 1;
					// }
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$spec_arr[] = $row;
						}
					}
					return $spec_arr;
				}				

				// запрос строк заказа
				private function get_the_orders_Database($id_row = 0){
					$where = 0;
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
						// если знаем id клиента - выводим только заказы по клиенту
						if(isset($_GET['client_id'])){
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`client_id` = '".$_GET['client_id']."'";
							$where = 1;
						}

						if(isset($_GET['order_num'])){
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`order_num` = '".(int)$_GET['order_num']."'";
							$where = 1;
						}

						if(isset($_GET['manager_id'])){
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$_GET['manager_id']."'";
							$where = 1;
						}

						// если это МЕН - выводим только его заказы
						// фильтрация по менеджеру
						if($this->user_access == 5){
							if(isset($_GET['subsection']) && $_GET['subsection'] != 'production'){
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
								$where = 1;
							}								
						}
						switch ($_GET['subsection']) {
							case 'order_all':
								// получаем статусы заказа
								$order_status_string = '';
								$n = 0;
								foreach (array_keys($this->order_status) as $key => $status) {
									$order_status_string .= (($key>0)?",":"")."'".$status."'";
									$n++;
								}
								// получаем сервисные статусы заказа
								foreach (array_keys($this->order_service_status) as $key => $status) {
									if($status == 'maket_without_payment'){
										// заказы со статусом макет быз оплаты видны: админам, менам, дизам и бухам
										if($this->user_access == 1 || $this->user_access == 5 || $this->user_access == 9 || $this->user_access == 2){
											$order_status_string .= (($n>0)?",":"")."'".$status."'";
											$n++;
										}
									}else{
										$order_status_string .= (($n>0)?",":"")."'".$status."'";
										$n++;
									}
									
								}

								// выбираем из базы только заказы 
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$order_status_string.")";
								$where = 1;
								break;
							case 'order_start':
								// заказ в стадии запуск в рабуту
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN ('in_operation')";
								$where = 1;
								break;
							case 'order_in_work':
								// заказ в стадии в работе
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN ('in_work')";
								$where = 1;
								break;
							case 'design_for_one_men':
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
								$where = 1;
								break;
							case 'production':
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN ('in_work')";
								$where = 1;
								break;							
							default:
								# code...
								break;
						}						
					}
					//////////////////////////
					//	sorting
					//////////////////////////
						$query .= ' ORDER BY `id` DESC';
					
					//////////////////////////
					//	check the query
					//////////////////////////
					// echo '*** $query = '.$query.'<br>';


					//////////////////////////
					//	query for get data
					//////////////////////////
					$result = $mysqli->query($query) or die($mysqli->error);

					$this->Order_arr = array();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->Order_arr[] = $row;
						}
					}
				}

				// ШАБЛОН заказа стандартный
				private function order_standart_rows_Template($id_row=0){

					
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

					$this->collspan = 12;

					// запрос строк заказов
					$this->get_the_orders_Database($id_row);


					$table_order_row = '';		

					// создаем экземпляр класса форм
					$this->FORM = new Forms();


					// тут будут храниться операторы
					$this->Order['operators_listiong'] = '';


					// ПЕРЕБОР ЗАКАЗОВ
					foreach ($this->Order_arr as $this->Order) {						
						$this->price_order = 0;// стоимость заказа 

						//////////////////////////
						//	open_close   -- start
						//////////////////////////
							// получаем флаг открыт/закрыто
							$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
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
						$this->order_deadline = ''; // дата отгрузки заказа (из спецификации)
						$this->order_date_of_delivery = ''; // количество рабочих дней на работу над заказом (из спецификации)
						$this->position_item = 1; // порядковый номер позиции
						$table_order_positions_rows = $this->table_specificate_for_order_Html();
						// $table_order_positions_rows = '';
						
						// формируем строку с информацией о заказе
						$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'" data-order_num="'.$this->Order['order_num'].'">';
						
						$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
						//////////////////////////
						//	тело строки заказа -- start ---
						//////////////////////////
							$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.($this->rows_num+1).'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>';
							$table_order_row2_body .= '<td colspan="3" class="orders_info">';
								$table_order_row2_body .= '<span class="greyText">№: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
									
									// исполнители заказа
									$table_order_row2_body .= '<br>';
									$table_order_row2_body .= '<table class="curator_on_request">';
										$table_order_row2_body .= '<tr>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">мен: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';
											$table_order_row2_body .= '</td>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">дизайнер: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
											$table_order_row2_body .= '</td>';
										$table_order_row2_body .= '</tr>';	
										$table_order_row2_body .= '<tr>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span>';
											$table_order_row2_body .= '</td>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">оператор: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
											$table_order_row2_body .= '</td>';
										$table_order_row2_body .= '</tr>';	
									$table_order_row2_body .= '</table>';								

							$table_order_row2_body .= '</td>';
							// комментарии
							$table_order_row2_body .= '<td>';								
								$table_order_row2_body .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
							$table_order_row2_body .= '</td>';
							
							$table_order_row2_body .= '<td></td>';
							
							// стоимость заказа
							$table_order_row2_body .= '<td><span class="show_the_full_information">'.$this->price_order.'</span> р.</td>';
							
							// бух учет
							$table_order_row2_body .= '<td class="buh_uchet_for_order" data-id="'.$this->Order['order_num'].'"></td>';
							
							// платёжная информация
							$this->Order_payment_percent = $this->calculation_percent_of_payment($this->price_order, $this->Order['payment_status']);

							$table_order_row2_body .= '<td>';
								// // если был оплачен.... и % оплаты больше нуля
								// if ((int)$this->Order_payment_percent > 0) {
								// 	// когда оплачен
								// 	$table_order_row2_body .= '<span class="greyText">оплачен: </span>'.$this->Order['payment_date'].'<br>';
								// 	// сколько оплатили в %
								// 	$table_order_row2_body .= '<span class="greyText">в размере: </span> '. $this->Order_payment_percent .' %';
								// }else{
								// 	$table_order_row2_body .= '<span class="redText">НЕ ОПЛАЧЕН</span>';
								// }
							$table_order_row2_body .= '</td>';
								/*
										$this->order_deadline = ''; // дата отгрузки заказа (из спецификации)
						$this->order_date_of_delivery = ''; // количество рабочих дней на работу над заказом (из спецификации)
								*/
							$table_order_row2_body .= '<td></td>';
							$table_order_row2_body .= '<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>';
							$table_order_row2_body .= '<td><span class="greyText">заказа: </span></td>';
							$table_order_row2_body .= '<td class="order_status_chenge">'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
						
						/////////////////////////////////////
						//	тело строки заказа -- end ---
						/////////////////////////////////////

						$table_order_row2 = '</tr>';
						// включаем вывод позиций 
						$table_order_row .= $table_order_row2_body.$table_order_row2.$table_order_positions_rows;

						// запрос по одной строке без подробностей
						if($id_row != 0){return $table_order_row2_body;}						
					}

					$html = $table_head_html.$table_order_row.'</table>';
					echo $html;
				}

				//////////////////////////
				//	Выгрузка в виде заказов
				//////////////////////////
					/*
						фильтрация встроена непосредственно в запросе
					*/
					// все заказы под сервисным статусом и статусом заказа
					private function orders_order_all_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}
					// заказ в стадии запуск в работу
					private function orders_order_start_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}
					// заказ в стадии в работе
					private function orders_order_in_work_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}

				//////////////////////////
				//	выгрузка позиций по шаблону Дизайн/препресс
				//////////////////////////
					private function orders_design_all_Template($id_row=0){
						$this->group_access = 9;
						// id начальника отдела дизайна
						$this->director_of_operations_ID = 77; 

						$this->design_rows($id_row=0);
					}
					private function orders_design_for_one_men_Template($id_row=0){
						$this->group_access = 9;
						// id начальника отдела дизайна
						$this->director_of_operations_ID = 77; 

						$this->design_rows($id_row=0);
					}

					// получаем HTML спецификации (Дизайн/препресс)
					private function table_specificate_for_order_for_design_Html(){
						$this->spec_arr = $this->table_specificate_for_order_Database($this->Order['id']);
						// echo 'Hellow World =D<br>';
						$html = '';

						$this->rows_num = 0;// порядковый номер строки
						$this->position_num = 1;// порядковый номер позиции
						$this->specificate_item = 0;// порядковый номер спецификации

						// обход массива спецификаций
						foreach ($this->spec_arr as $key => $this->specificate) {
							// стоимость по спецификации (НАЧАЛЬНАЯ)
							$this->price_specificate = 0; 

							// подсчет номер спецификаций
							$this->specificate_item++;

							// вывод html строк позиций по спецификации 
							// запрашивается раньше спец-ии, чтобы подсчитать её стоимость
							$positions_rows = $this->table_order_positions_rows_for_design_Html();
							// echo 'Hellow World =D<br>';
							// echo $this->print_arr($positions_rows);
							// получаем html строку со спецификацией
							// $html .= $this->get_order_specificate_Html_Template();

							// // если количество позиций не известно - сохраняем
							// if($this->specificate['number_of_positions'] == 0){
							// 	$this->save_number_of_positions_in_specificate_row_Database($this->specificate['id'],$this->number_of_positions);
							// }

							// // если хранящаяся в базу стоимость 
							// // не совпадает со стоимостью которая была рассчитана - перезаписываем её на правильную 
							// if ($this->price_specificate != $this->specificate['spec_price']) {
							// 	$this->save_price_specificate_Database($this->specificate['id'],$this->price_specificate);
							// }

							// подсчёт стоимости заказа
							$this->price_order += $this->price_specificate;

							// строки позиций идут под спецификацией
							$html .= $positions_rows;
													
						}
						return $html;
					}

					// получаем позиции по спецификации
					private function table_order_positions_rows_for_design_Html(){	//$this->position_item	
						// получаем массив позиций 
						$positions_rows = $this->positions_rows_Database($this->specificate['id']);
						$this->number_of_positions = count($positions_rows);	
						
						$html = '';	
						$html_row_1 = '';

						
						// формируем строки позиций	(перебор позиций)	
						$n = 0;	
						foreach ($positions_rows as $key => $position) {

							$this->Position_status_list = array(); // в переменную заложим все статусы

							$this->id_dop_data = $position['id_dop_data'];
							
							// ТЗ на изготовление продукцию для НЕКАТАЛОГА
							// для каталога и НЕкаталога способы хранения и получения данной информации различны
							$this->no_cat_TZ = '';
							if(trim($position['type'])!='cat' && trim($position['type'])!=''){
								// доп инфо по некаталогу берём из json 
								$this->no_cat_TZ = $this->decode_json_no_cat_to_html($position);
							}

							// получаем массив услуг по позиции
							$this->position_services_arr = $this->get_order_dop_uslugi( $this->id_dop_data );

							// выборка только массива услуг дизайна
							$this->services_design = $this->get_dop_services_for_production( $this->position_services_arr , 9 );
							// выборка только массива услуг производства
							$this->services_production = $this->get_dop_services_for_production( $this->position_services_arr , 4 );

							$this->services_num  = count($this->services_design);
							
							$n++;				
							// если услуг для производства в данной позиции нет - переходм к следующей
							if($this->services_num == 0){continue;}
								
								// // порядковый номер позиции в заказе
								$html_row_1 = '<td rowspan="'.($this->services_num).'"><span class="orders_info_punct">'.$position['sequence_number'].'п<br>('.$this->Order['number_of_positions'].')</span></td>';
								
								// // описание позиции
								$html_row_1 .= '<td  rowspan="'.($this->services_num).'" >';

									// вставляем номер заказа
									$html_row_1 .= '№ '.$this->order_num_for_User.'<br>';
									// наименование товара
									$html_row_1 .= '<span class="art_and_name">'.$position['art'].'  '.$position['name'].'</span>';
									// описание некаталожной продукции
									$html_row_1 .= $this->no_cat_TZ;
									// места нанесения
									$html_row_1 .= $this->get_service_printing_list();

									// // массив по позиции
									// $html_row_1 .= 'массив позиции<br>';
									// $html_row_1 .= $this->print_arr($position);

									// // массив всeх услуг
									// $html_row_1 .= 'массив всех услуг<br>';
									// $html_row_1 .= $this->print_arr($this->position_services_arr);

									// // массив услуг печати
									// $html_row_1 .= 'массив услуг печати<br>';
									// $html_row_1 .= $this->print_arr($this->services_production);
									// добавляем тираж
									$html_row_1 .= 'Тираж: '.($position['quantity']) .' шт.';	


									$html_row_1 .= '<div class="linked_div">'.identify_supplier_by_prefix($position['art']).'</div>';
								$html_row_1 .= '</td>';

								// $html_row_2 = '<td rowspan="'.$this->services_num.'">1</td>';
								$html_row_2 = '<td rowspan="'.$this->services_num.'" >
											<div>'.$this->decoder_statuslist_snab($position['status_snab'],$position['date_delivery_product'],0,$position['id']).'</div>
										</td>';

							// $html_row_2 .= '</tr>';	


							$html .= $this->get_service_content_for_designer_operations($position,$this->services_design,$html_row_1,$html_row_2);
							
							// $this->position_item++;
							// $this->position_item = count($positions_rows) * $this->services_num+1;
							
						}				
						return $html;
					}

					// выводит строки услуг для дизайнеров и операторов
					private function get_service_content_for_designer_operations($position, $services_arr, $html_row_1, $html_row_2){
						if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
							$this->Services_list_arr = $this->get_all_services_Database();
						}

						$gen_html = '';
						$n = 0;

						$service_count = count($services_arr);
						
						// перебираем услуги по позиции
						foreach ($services_arr as $key => $service) {

							// получаем  json
							$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
							// декодируем json  в массив
							$this->print_details_dop = json_decode($this->print_details_dop_Json, true);



							// получаем наименование услуги
							$this->Service_name = (isset($this->Services_list_arr[ $service['uslugi_id'] ]['name'])?$this->Services_list_arr[ $service['uslugi_id'] ]['name']:'данная услуга в базе не найдена');

							$html = '';
							// $html .= ($n>0)?'<tr class="position-row position-row-production row__'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>':'';
								// место
								

								// операция
								$html .= '<td class="show-backlight js-modal--tz-prodaction" data-id="'.$service['id'].'">';
									$html .= $this->Service_name;


									// перебираем производственные услуги к которым дизайнер/оператор будет готовить макет или дизайн
									foreach ($this->services_production as $key_production_service => $production_service) {
										$html .= '<div class="seat_number_logo">';
											$html .= 'место'.($key_production_service+1).' ('.$this->Services_list_arr[ $production_service['uslugi_id'] ]['name'].'): ';
											$html .= $production_service['logotip'];
										$html .='</div>';	
									}

									// выводим ТЗ
									//$html .= '<br>'.$service['tz'];

								$html .= '</td>';

								
								

								if($n==0){// это дополнительные колонки в уже сформированную строку
									// оборачиваем колонки в html переданный в качестве параметра
									$html .= '<td class="show-backlight" rowspan="'.count($services_arr).'">';
										// подрядчик печати
										$html .= $position['suppliers_name'];
										// пленки / клише
										$html .= $this->get_film_and_cliches();
									$html .= '</td>';

									//$html .= '<tr class="position-row position-row-production" id="position_row_'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>'.$html_row_3 .'</tr>';
								}
								// // плёнки / клише
								// $html .= '<td class="show-backlight">';
								// 	$html .= $this->get_statuslist_film_photos($service['film_photos_status'],$service['id']);
								// $html .= '</td>';
								

								// дата сдачи
								$html .= '<td class="show-backlight">';
									$html .= '<span class="greyText">'.$this->Order['date_of_delivery_of_the_order'].'</span>';
								$html .= '</td>';
								
								// дата работы
								$html .= '<td class="show-backlight">';
									//$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000')?'нет':$service['date_work']).'" data-id="'.$service['id'].'" class="calendar_date_work">';
								$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000')?'нет':$service['date_work']).'" data-id="'.$service['id'].'" disabled style="width:70px;text-align:center">';
								$html .= '</td>';

								// исполнитель услуги
								$html .= '<td class="show-backlight">';
									$html .= $this->get_production_userlist_Html($service['performer_id'],$service['id']);
								$html .= '</td>';

								// статус готовности
								$html .= '<td class="show-backlight">';
									$html .= $this->get_statuslist_uslugi_Dtabase_Html($service['uslugi_id'],$service['performer_status'],$service['id'], $service['performer']);
								$html .= '</td>';

								// // % готовности
								// $html .= '<td class="show-backlight percentage_of_readiness" contenteditable="true" data-service_id="'.$service['id'].'">';
								// 	$html .= $service['percentage_of_readiness'];
								// $html .= '</td>';
							// $html .= ($n>0)?'</tr>':'';

							if($n==0){// это дополнительные колонки в уже сформированную строку
								// оборачиваем колонки в html переданный в качестве параметра
								$gen_html .= '<tr class="position-row position-row-production" id="position_row_'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>'.$html_row_1 . $html . $html_row_2 .'</tr>';
							}else{
								$gen_html .= '<tr class="position-row position-row-production" id="position_row_'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>'.$html.'</tr>';
							}

							// $gen_html = (trim($gen_html)!='')?'<tr data-ddd=\''.md5($gen_html).'\'>'.$gen_html.'</tr>':'';
							$this->position_item++;
							$n++;
						}
						return $gen_html ;
					}

					// ШАБЛОН заказа Дизайн/препресс
					private function design_rows($id_row=0){

						// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
						// создаем экземпляр класса форм
						$this->FORM = new Forms();

						$where = 0;
						// скрываем левое меню
						$html = '';
						$table_head_html = '';
						
						// формируем шапку таблицы вывода
						// $table_head_html .= $this->print_arr($_SESSION);
						$table_head_html .= '<table id="general_panel_orders_tbl">';
							$table_head_html .= '<tr>';
								$table_head_html .= '<th colspan="3" rowspan="2">Артикул/номенклатура/печать</th>';
								$table_head_html .= '<th  rowspan="2">Техническое задание</th>';
								$table_head_html .= '<th>Подрядчик печати</th>';
								$table_head_html .= '<th rowspan="2">Дата сдачи<br>макета</th>';
								$table_head_html .= '<th rowspan="2">Дата утв.<br>макета</th>';
								$table_head_html .= '<th rowspan="2">исполнитель</th>';
								$table_head_html .= '<th rowspan="2">статус дизайна</th>';
								$table_head_html .= '<th rowspan="2">статус снабжение</th>';
							$table_head_html .= '</tr>';
							$table_head_html .= '<tr>';
							$table_head_html .= '<th><span style="float:left; height:100%; padding: 0 5px 0 0; border-right:1px solid grey">М</span><span style="folat:right; padding:0 5px;">пленки / клише</span></th>';
							$table_head_html .= '</tr>';

						
						// запрос заказов
						$this->get_the_orders_Database($id_row);
						
						$table_order_row = '';		
						// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
						// создаем экземпляр класса форм
						// $this->FORM = new Forms();

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
							$this->order_deadline = ''; // дата отгрузки заказа (из спецификации)
							$this->order_date_of_delivery = ''; // количество рабочих дней на работу над заказом (из спецификации)
							$this->position_item = 1; // порядковый номер позиции
							$table_order_positions_rows = $this->table_specificate_for_order_for_design_Html();
							
							
							// усли позиций по данному заказу нет - переходим к следующеё итерации цикла
							if($table_order_positions_rows==''){continue;}

							// формируем строку с информацией о заказе
							$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
								$table_order_row .= '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'">
														<span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span>
													</td>';
								$table_order_row .= '<td colspan="3" class="orders_info">';
									$table_order_row .= '<span class="greyText">№: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
									
									$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
									// исполнители заказа
									$table_order_row .= '<br>';
									$table_order_row .= '<table class="curator_on_request">';
										$table_order_row .= '<tr>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">мен: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';
											$table_order_row .= '</td>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">дизайнер: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
											$table_order_row .= '</td>';
										$table_order_row .= '</tr>';	
										$table_order_row .= '<tr>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span>';
											$table_order_row .= '</td>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">оператор: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
											$table_order_row .= '</td>';
										$table_order_row .= '</tr>';	
									$table_order_row .= '</table>';	
								$table_order_row .= '</td>';

								
								// $table_order_row .= '<td colspan="3" class="orders_info">
								// 					<span class="greyText">№: </span><a href="#">'.$this->order_num_for_User.'</a> <span class="greyText"> &larr; (<a href="?page=client_folder&client_id='.$this->Order['client_id'].'&query_num='.$this->Order['query_num'].'" target="_blank" class="greyText">'.$this->Order['query_num'].'</a>)</span>
								// 					'.$this->get_client_name_link_Database($this->Order['client_id']).'
								// 					<span class="greyText">,&nbsp;&nbsp;&nbsp;   менеджер: '.$this->get_manager_name_Database_Html($this->Order['manager_id'],1).'</span>
								// 					<span class="greyText">,&nbsp;&nbsp;&nbsp;   снабжение: '.$this->get_name_employee_Database_Html($this->Order['snab_id']).'</span>
								// 					<span class="greyText">,&nbsp;&nbsp;&nbsp;   оператор : в разработке</span>
												// </td>';
								
								// дата сдачи
								$table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
								
								$table_order_row .= '<td colspan="5"></td>';
								
							$table_order_row .= '</tr>';
							// включаем вывод позиций 
							$table_order_row .= $table_order_positions_rows;
						}		

						$html = $table_head_html.$table_order_row.'</table>';
						echo $html;
					}

					// фильтр услуг
					private function get_dop_services_for_production($services_arr, $user_access, $service_id = 0){
						if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
							$this->Services_list_arr = $this->get_all_services_Database();
						}
						// объявляем массив, который будем возвращать 
						$new_arr = array();
						
						// $new_arr[] = 'Hellow World';
						foreach ($services_arr as $key => $service) {
							// фильтрация
							if($service_id > 0 && $service['uslugi_id'] != $service_id){ continue; }

							// если такая услуга существует в базе
						 	if(isset( $this->Services_list_arr[$service['uslugi_id']]) ){
						 		/**
						 		 * если доступ позволяет её обрабатывать
								 *	Т.к. в данном случае дизайнер работает не со всеми услугами производства, отфильтровываем все услуги по флагу maket_true
								 */
						 		if($this->Services_list_arr[ $service['uslugi_id'] ]['performer'] == $user_access && $this->Services_list_arr[ $service['uslugi_id'] ]['maket_true'] == "on"){
						 			
						 			// добавляем услугу в новый массив 
						 			$new_arr[] = $service;
						 		}

						 	}
						}
						// возвращаем отфильтрованный список услуг
						return $new_arr;
					}

					// докодируем доп поля по услугам в читабельный вид
					private function decode_dop_inputs_information_for_servece($service){
						global $mysqli;
						$html = '';
						//////////////////////////
						//	ДОП ПОЛЯ
						//////////////////////////
						if(!isset($this->dop_inputs_listing)){
							// получаем список всех полей
							$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
							$result = $mysqli->query($query) or die($mysqli->error);
							$this->dop_inputs_listing = array();
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									$this->dop_inputs_listing[$row['name_en']] = $row;
								}
							}

						}
						
						

						// получаем  json
						$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
						// декодируем json  в массив
						$this->print_details_dop = json_decode($this->print_details_dop_Json, true);
						
						if(!isset($this->print_details_dop)){
							$html .= "<div>произошла ошибка json</div>";
						}
							
						$n=0;
						// раскодируем jsondop_inputs	
						if(isset($this->print_details_dop) && !empty($this->print_details_dop)){
							//echo  $service['print_details_dop'];
							$n=0;
							foreach ($this->print_details_dop as $key => $text) {
								$html .= (($n>0)?', ':'').$this->dop_inputs_listing[$key]['name_ru'].': '.base64_decode($text);
								$n++;
							}
						}

						return $html;
					}


					

					// информация о плёнках и клише
					private function get_film_and_cliches(){
						// если услуг печати нет - выходим
						if(empty($this->services_production)){return '';}
						
						$html = '';
						//return $this->print_arr($this->services_production);
						// перебираем услуги печати
						$n = 1;
						foreach ($this->services_production as $key => $production_service) {
							$html .= '<div class="seat_number_film"><span class="seat_number">'.$n++.'</span>'.$this->get_statuslist_film_photos($production_service['film_photos_status'],$production_service['id']).'</div>';
						}
						return $html;
					}

					// отдаёт имя пользователя, список пользователей или 
					private function get_production_userlist_Html($performer_id, $service_id){

						// получаем список пользователей производства
						$this->get_production_userlist_Database();

						$html = '';
						// регулируем вывод в зависимости от уровня доступа
						switch ($this->user_access) {
							case '1': // для админа список
								$html .= '<select class="production_userlist">';
								foreach ($this->userlist as $key => $user) {
									$checked = ($performer_id == $user['id'])?' selected="selected"':'';
									$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
								}
								$html .= '</select>';
								return $html;
								break;
							case '4': 
								if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
									$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
									$html .= '<option value=""></option>';
									foreach ($this->userlist as $key => $user) {
										$checked = ($performer_id == $user['id'])?' selected="selected"':'';
										$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
									}
									$html .= '</select>';
									return $html;
								}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
									if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
										$user = $this->userlist[$performer_id];
										return $user['name'].' '.$user['last_name'];
									}else{
										$user = $this->userlist[$this->user_id];
										return '<input type="button" value="Взать в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['name'].' '.$user['last_name'].'" class="get_in_work_service">';
									};
								}
								break;
							case '9': 
								if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
									$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
									$html .= '<option value=""></option>';
									foreach ($this->userlist as $key => $user) {
										$checked = ($performer_id == $user['id'])?' selected="selected"':'';
										$html .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['name'].' '.$user['last_name'].'</option>';
									}
									$html .= '</select>';
									return $html;
								}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
									if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
										$user = $this->userlist[$performer_id];
										return $user['name'].' '.$user['last_name'];
									}else{
										$user = $this->userlist[$this->user_id];
										return '<input type="button" value="Взать в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['name'].' '.$user['last_name'].'" class="get_in_work_service">';
									};
								}
								break;
							
							default: // для остальных просто то, что хранится в ячейке
								if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
									$user = $this->userlist[$performer_id];
									return $user['name'].' '.$user['last_name'];
								}else{
									return 'исполнитель не назначен';
								};
								break;
						}			
					}


					// места нанесения
					private function get_service_printing_list(){
						//если нет прикрепленных мест печати - выходим
						if(empty($this->services_production)){return '';}


						if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
							$this->Services_list_arr = $this->get_all_services_Database();
						}


						$html = '';
						$n = 1;
						$service_name = '';
						
						// перебираем услуги нанесения по позиции
						foreach ($this->services_production as $key => $service) {
							if($service_name != $this->Services_list_arr[$service['uslugi_id']]['name']){
								if($service_name == ''){$html .= '<br>';}
								$service_name = $this->Services_list_arr[$service['uslugi_id']]['name'];
								$html .= $service_name.'<br>';
								$n = 1;
							}
							$html .= 'место '.$n++.': ';
							
							// декодируем dop_inputs для услуги печати
							$decode_dop_inputs_information_for_servece = $this->decode_dop_inputs_information_for_servece($service);
							$html .= (($decode_dop_inputs_information_for_servece != "")?$decode_dop_inputs_information_for_servece:'<span style="color:red">информация отсутствует</span>').'<br>';
						}
						return $html;	

					}	



				/**
				 *	выгрузка позиций по шаблону Производство
				*/
					/**
					 * Возвращает фильрацию по вкладке производства "ВСЁ"
					 *
					 * @param string $id_row 	id row from the base 
					 * @return 					html code
					 * @see 					html
					*/

					private function orders_production_Template($id_row=0){
						$this->group_access = 4;
						// id начальника отдела дизайна
						$this->director_of_operations_ID = 42; 

						echo $this->production_rows($id_row=0);
					}

					// взять в работу
					private function orders_production_get_in_work_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}

					/* Возвращает фильрацию по вкладке производства "трафарет"
					 *
					 * @param string $id_row 	id row from the base 
					 * @return 					html code
					 * @see 					html
					*/

					private function orders_production_stencil_shelk_and_transfer_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Шелкография
					private function orders_production_shelk_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Термотрансфер
					private function orders_production_transfer_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Тампопечать
					private function orders_production_tampoo_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Тиснение
					private function orders_production_tisnenie_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Доп. услуги
					private function orders_production_dop_uslugi_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Проверка плёнок/клише
					private function orders_production_plenki_and_klishe_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}

					/**
					 * фильтрация по услугам для subsection для производства
					 *
					 * @param  		array()
					 * @return 		array()
					 */	
					protected function filter_of_subsection_for_production($services_print){
						if($this->user_access == 4){
							$services_print_NEW = array();
							foreach ($services_print as $key => $value) {
								switch ($_GET['subsection']) {
									// фильтр по статусу "ожидает обработки"
									case 'production_get_in_work':
										if($value['performer_status'] != 'Ожидает обработки'){continue;}
										$services_print_NEW[] = $value;
										break;
									// фильтр по всему трафаретному участку
									case 'production_stencil_shelk_and_transfer':
										// перечислим разрешённые ключи
									    $keys = array(28, 13, 14, 15, 30, 31, 32, 33, 34, 35);
									    // проверяем 
										if( !in_array($value['uslugi_id'], $keys)){continue;}
										$services_print_NEW[] = $value;
										break;
									// фильтр по шелкухе
									case 'production_shelk':
										// перечислим разрешённые ключи
									    $keys = array(13, 14, 15, 30, 31, 32, 33, 34, 35);
									    // проверяем 
										if( !in_array($value['uslugi_id'], $keys)){continue;}
										$services_print_NEW[] = $value;
										break;

									// фильтр по трансферу
									case 'production_transfer':
										// перечислим разрешённые ключи
									    $keys = array(28);
									    // проверяем 
										if( !in_array($value['uslugi_id'], $keys)){continue;}
										$services_print_NEW[] = $value;
										break;

									// фильтр по тампухе
									case 'production_tampoo':
										// перечислим разрешённые ключи
									    $keys = array(18);
									    // проверяем 
										if( !in_array($value['uslugi_id'], $keys)){continue;}
										$services_print_NEW[] = $value;
										break;

									// фильтр по тиснение
									case 'production_tisnenie':
										// перечислим разрешённые ключи
									    $keys = array(17, 19);
									    // проверяем 
										if( !in_array($value['uslugi_id'], $keys)){continue;}
										$services_print_NEW[] = $value;
										break;

									// фильтр по тиснение
									case 'production_dop_uslugi':
										// перечислим разрешённые ключи
									    $keys = array(19, 18, 17, 37,16,15,14,13,28,30,31,32,33,34,35,36,38,46);
									    // проверяем 
										if( in_array($value['uslugi_id'], $keys)){continue;}
										$services_print_NEW[] = $value;
										break;

									// фильтр "проверить плёнки"
									case 'production_plenki_and_klishe':
										if($value['film_photos_status'] != 'проверить наличие'){ continue; }
										$services_print_NEW[] = $value;
										break;

									
									default:
										$services_print_NEW[] = $value;
										break;
								}
							}
							return $services_print_NEW;
						}else{
							return $services_print;
						}
					}



					// HTML заказа (Производство)
					private function production_rows($id_row=0){
						$where = 0;
						// скрываем левое меню
						$html = '';
						$table_head_html = '<style type="text/css" media="screen">
							#cabinet_left_coll_menu{display:none;}
						</style>';
						
						// формируем шапку таблицы вывода
						$table_head_html .= '
							<table id="general_panel_orders_tbl">
							<tr>
								<th colspan="3" rowspan="2">Артикул/номенклатура/печать</th>
								<th rowspan="2">М</th>
								<th rowspan="2">операции</th>
								<th rowspan="2">тираж</th>
								<th rowspan="2">запас</th>
								<th rowspan="2">цвета</th>
								<th rowspan="2">логотип нанесения</th>
								<th rowspan="2">пплёнки/клише</th>
								<th rowspan="2">статус склад</th>
								<th rowspan="2">статус позиции</th>
								<th rowspan="2">дата сдачи</th>
								<th colspan="2">дата работы</th>
								<th rowspan="2">станок</th>
								<th rowspan="2">мастер</th>
								<th rowspan="2">статус операции</th>
								<th rowspan="2">% гот-ти</th>
							</tr>
							<tr>
								<th>от</th>
								<th>до</th>
							</tr>
						';

						// запрос заказов
						$this->get_the_orders_Database($id_row);


						$table_order_row = '';		
						// подключаем класс форм (понадобится в методе: decode_json_no_cat_to_html)
						// создаем экземпляр класса форм
						// $this->FORM = new Forms();

						// ПЕРЕБОР ЗАКАЗОВ
						foreach ($this->Order_arr as $this->Order) {
							// цена заказа

							$this->price_order = 0;

							// получаем флаг открыт/закрыто
							$this->open__close = $this->get_open_close_for_this_user($this->Order['open_close']);
							
							// запоминаем обрабатываемые номера заказа и запроса
							// номер запроса
							$this->query_num = $this->Order['query_num'];
							// номер заказа
							$this->order_num = $this->Order['order_num'];

							// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
							$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

							// запрашиваем информацию по спецификациям
							$table_order_positions_rows = $this->table_specificate_for_order_for_production_Html();
							
							if($table_order_positions_rows == ''){continue;}

							
							// формируем строку с информацией о заказе
							$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
								$table_order_row .= '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'">
														<span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span>
													</td>';
								$table_order_row .= '<td colspan="12" class="orders_info">';
									$table_order_row .= '<span class="greyText">№: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
									
									$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
									// исполнители заказа
									$table_order_row .= '<br>';
									$table_order_row .= '<table class="curator_on_request">';
										$table_order_row .= '<tr>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">мен: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';
											$table_order_row .= '</td>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">дизайнер: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
											$table_order_row .= '</td>';
										$table_order_row .= '</tr>';	
										$table_order_row .= '<tr>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span>';
											$table_order_row .= '</td>';
											$table_order_row .= '<td>';
												$table_order_row .= '<span class="greyText">оператор: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
											$table_order_row .= '</td>';
										$table_order_row .= '</tr>';	
									$table_order_row .= '</table>';	
								$table_order_row .= '</td>';
								
								// дата сдачи
								$table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
								
								$table_order_row .= '<td colspan="5"></td>';
								
							$table_order_row .= '</tr>';
							// включаем вывод позиций 
							$table_order_row .= $table_order_positions_rows;
						}		

						$html = $table_head_html.$table_order_row.'</table>';
						return $html;
					}

					// HTML спецификации (Производство)
					private function table_specificate_for_order_for_production_Html(){
						$this->spec_arr = $this->table_specificate_for_order_Database($this->Order['id']);
						// echo 'Hellow World =D<br>';
						$html = '';
						$this->rows_num = 0;// порядковый номер строки
						$this->position_num = 1;// порядковый номер позиции
						$this->position_item = 1;
						$this->specificate_item = 0;// порядковый номер спецификации

						// обход массива спецификаций
						foreach ($this->spec_arr as $key => $this->specificate) {
							// стоимость по спецификации (НАЧАЛЬНАЯ)
							$this->price_specificate = 0; 

							// подсчет номер спецификаций
							$this->specificate_item++;

							// вывод html строк позиций по спецификации 
							// запрашивается раньше спец-ии, чтобы подсчитать её стоимость
							$positions_rows = $this->table_order_positions_rows_for_production_Html();
							
							// подсчёт стоимости заказа
							$this->price_order += $this->price_specificate;

							// строки позиций идут под спецификацией
							$html .= $positions_rows;
													
						}
						return $html;
					}


					// HTML позиции (Производство)
					private function table_order_positions_rows_for_production_Html(){			
						// получаем массив позиций заказа
						$positions_rows = $this->positions_rows_Database($this->specificate['id']);
						$html = '';	

						// $this->position_item = 1;// порядковый номер позиции
						// формируем строки позиций	(перебор позиций)		
						foreach ($positions_rows as $key => $position) {
							$this->Position_status_list = array(); // в переменную заложим все статусы

							$this->id_dop_data = $position['id_dop_data'];

							
							// выборка только массива печати
							$this->services_print = $this->get_dop_services_for_production( $this->get_order_dop_uslugi( $this->id_dop_data ), 4 ,((isset($_GET['service_id']) && (int)$_GET['service_id']>0)?$_GET['service_id']:0));

							/**
						 	 * фильтрация для subsection для производства
						 	 */	
						 	$this->services_print = $this->filter_of_subsection_for_production($this->services_print);


							$this->services_num  = count($this->services_print);
											
							// если услуг для производства в данной позиции нет - переходм к следующей
							if($this->services_num == 0){continue;}

							
							
								// // порядковый номер позиции в заказе
								$html_row_1 = '<td rowspan="'.$this->services_num.'"><span class="orders_info_punct">'.$position['sequence_number'].'п<br>('.$this->Order['number_of_positions'].')</span></td>';
								
								// // описание позиции
								$html_row_1 .= '<td  rowspan="'.$this->services_num.'" >';
									// наименование товара
									$html_row_1 .= '<span class="art_and_name">'.$position['art'].'  '.$position['name'].'</span>';
								$html_row_1 .= '</td>';

								// склад, снабжение
								// $html .= 
								$html_row_2 = '<td rowspan="'.$this->services_num.'" >';
									$html_row_2 .= $this->decoder_statuslist_sklad($position['status_sklad'], $position['id']);
								$html_row_2 .= '</td>';
								$html_row_2 .= '<td rowspan="'.$this->services_num.'" >';
									$html_row_2 .= '<div>'.$this->decoder_statuslist_snab($position['status_snab'],$position['date_delivery_product'],0,$position['id']).'</div>';
								$html_row_2 .= '</td>';

							// $html_row_2 .= '</tr>';	


							$html .= $this->get_service_content_for_production($position,$this->services_print,$html_row_1,$html_row_2);

							$this->position_item += $this->services_num;
						}				
						return $html;
					}



					// HTML строки услуг (Производство)
					private function get_service_content_for_production($position, $services_arr, $html_row_1, $html_row_2){
						if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
							$this->Services_list_arr = $this->get_all_services_Database();
						}

						$gen_html = '';
						$n = 0;
						
						// перебираем услуги по позиции
						foreach ($services_arr as $key => $service) {
							// получаем  json
							$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
							// декодируем json  в массив
							$this->print_details_dop = json_decode($this->print_details_dop_Json, true);



							// получаем наименование услуги
							$this->Service_name = (isset($this->Services_list_arr[ $service['uslugi_id'] ]['name'])?$this->Services_list_arr[ $service['uslugi_id'] ]['name']:'данная услуга в базе не найдена');

							$html = '';
							if($n>0){
								$html .= ($n>0)?'<tr class="position-row-production row__'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>':'';
							}else{
								$html .= '<tr class="position-row position-row-production" id="position_row_'.$position['sequence_number'].'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>';
							}
								if($n==0){// это дополнительные колонки в уже сформированную строку
									// оборачиваем колонки в html переданный в качестве параметра
									$html .= $html_row_1;
								}
								// место
								$html .= '<td class="show-backlight js-modal--tz-prodaction" data-id="'.$service['id'].'">';
									$html .= ($key+1);
								$html .= '</td>';

								// операция
								// $html .= '<td class="show-backlight js-filter--type-print position-row-production__service-name" data-href="'.$this->link_enter_to_filters('service_id',$service['uslugi_id']).'">';
								$html .= '<td class="show-backlight js-filter--type-print position-row-production__service-name">';
									$html .= '<a href="'.$this->link_enter_to_filters('service_id',$service['uslugi_id']).'">';
										$html .= $this->Service_name;
									$html .= '</a>';
								$html .= '</td>';

								// тираж
								$html .= '<td class="show-backlight">';
									$html .= $position['quantity'];
								$html .= '</td>';

								// запас
								$html .= '<td class="show-backlight">';
									$html .= (($position['zapas']!=0 && trim($position['zapas'])!='')?(($position['print_z']==0)?'+'.$position['zapas'].'<br>НПЗ':'+'.$position['zapas'].'<br>ПЗ'):'');
								$html .= '</td>';

								// цвета, логотип и другие персонализированные данные мы оставляем в окне ТЕХ. ЗАДАНИЕ
								$html .= '<td class="show-backlight">';
									// комментарии для решения возможных проблем

									$html .= '<!--// ключ к полю возможно будет отличаться не в локальной версии... при изменении названия поля Пантоны... выгрузка информации сюда изменится -->';
									$html .= (isset($this->print_details_dop['pantony'])?$this->print_details_dop['pantony']:'');
								$html .= '</td>';

								
								$html .= '<td class="show-backlight" data-id="'.$service['id'].'">';
									$html .= $service['logotip'];
								$html .= '</td>';

								// плёнки / клише
								$html .= '<td class="show-backlight">';
									if($this->user_access == 1 || $this->user_access == 4 || $this->user_id == $this->Order['manager_id']){
										$html .= $this->get_statuslist_film_photos($service['film_photos_status'],$service['id']);	
									}else{
										$html .= $service['film_photos_status'];
									}
									
								$html .= '</td>';
								// статус склада
								// $html .= '<td class="show-backlight">';
								// 	$html .= $this->decoder_statuslist_sklad($position['status_sklad'], $position['id']);
								// $html .= '</td>';
								if($n==0){// это дополнительные колонки в уже сформированную строку
									// оборачиваем колонки в html переданный в качестве параметра
									$html .= $html_row_2;
								}
								// дата сдачи
								$html .= '<td class="show-backlight">';
									$html .= '<span class="greyText">'.$this->Order['date_of_delivery_of_the_order'].'</span>';
								$html .= '</td>';
								// дата работы start
								$html .= '<td class="show-backlight">';
									if($this->user_access == 4 || $this->user_access == 1){
										$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000 00:00')?'  -  ':$service['date_work']).'" data-id="'.$service['id'].'" class="calendar_date_work">';
									}else{
										$html .= (($service['date_work']=='00.00.0000 00:00')?'':''.$service['date_work']);
									}
								$html .= '</td>';
								$html .= '<td class="show-backlight">';
									if($this->user_access == 4 || $this->user_access == 1){
										$html .= '<input type="text" name="calendar_date_ready"  value="'.(($service['date_work']=='00.00.0000 00:00')?'  -  ':$service['date_ready']).'" data-id="'.$service['id'].'" class="calendar_date_ready">';
									}else{
										$html .= (($service['date_ready']=='00.00.0000 00:00')?'':''.$service['date_ready']);
									}
								$html .= '</td>';
								// станок
								$html .= '<td class="show-backlight">';
									$html .= $this->get_machine_list($service['machine'],$service['id']);
									// if($this->user_access == 4 || $this->user_access == 1){
									// 	$html .= '<input type="text" name="calendar_date_work"  value="'.$service['machine'].'" data-id="'.$service['id'].'" class="machine_type">';
									// }else{
									// 	$html .= $service['machine'];
									// }
								$html .= '</td>';
								// мастер
								$html .= '<td class="show-backlight">';
									$html .= $this->get_production_userlist_Html($service['performer_id'],$service['id']);
								$html .= '</td>';
								// статус готовности
								$html .= '<td class="show-backlight">';
									$html .= $this->get_statuslist_uslugi_Dtabase_Html($service['uslugi_id'],$service['performer_status'],$service['id'], $service['performer']);
								$html .= '</td>';
								// % готовности
								$html .= '<td class="show-backlight percentage_of_readiness" contenteditable="true" data-service_id="'.$service['id'].'">';
									$html .= $service['percentage_of_readiness'];
								$html .= '</td>';
							$html .= ($n>0)?'</tr>':'';

							
							$gen_html .= $html;
							
							$n++;
						}
						return $gen_html ;
					}






				

			
			// // все счета
			// private function orders_all_the_bill_Template($id_row=0){
			// 	$this->get_paperwork_specificate_rows_Template();
			// }

			// // шаблон выгрузки счетов (спецификаций)
			// private function get_paperwork_specificate_rows_Template(){
			// 	// запрос по спецификациям
			// 	$this->get_the_specificate_order_Database($id_row=0);
				
			// 	// собираем html строк-Заказыов
			// 	$html1 = '';
			// 	if(count($this->Specificate_arr)==0){return 1;}

			// 	$table_head_html = '
			// 		<table class="cabinet_general_content_row" id="cabinet_general_content_row">
			// 					<tr>
			// 						<th id="show_allArt"></th>
			// 						<th class="check_show_me"></th>
			// 						<th>Дата/время заведения</th>
			// 						<th>Заказ</th>
			// 						<th>Компания</th>	
			// 						<th>Спецификация:</th>
			// 						<th class="buh_uchet">Бух. учет</th>					
			// 						<th class="invoice_num">Счёт</th>
			// 						<th>Дата опл-ты</th>
			// 						<th>% оплаты</th>
			// 						<th>Оплачено</th>
			// 						<th>стоимость в спец.</th>
			// 						<th>статус БУХ</th>
			// 					</tr>';

			// 	foreach ($this->Specificate_arr as $Specificate) {



			// 		$invoice_num = $Specificate['number_the_bill']; // номер счёта

			// 			// получаем флаг открыт/закрыто
			// 			$this->open__close = $this->get_open_close_for_this_user($Specificate['open_close']);
						
			// 		//////////////////////////
			// 		//	open_close   -- end
			// 		//////////////////////////

			// 		// получаем массив позиций к спецификации
			// 		$position_arr = $this->get_the_position_with_specificate_Database($Specificate['id']);

			// 		// СОБИРАЕМ ТАБЛИЦУ
			// 		###############################
			// 		// строка с артикулами START
			// 		###############################
			// 		$html = '<tr class="query_detail" '.$this->open_close_tr_style.'>';
			// 		//$html .= '<td class="show_hide"><span class="this->cabinett_row_hide"></span></td>';
			// 		$html .= '<td colspan="14" class="each_art" >';
					
					
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
			// 		foreach ($position_arr as $position) {
						
						
			// 			////////////////////////////////////
			// 			//	Расчёт стоимости позиций START  
			// 			////////////////////////////////////
						
			// 				$this->GET_PRICE_for_position($position);				
						
			// 			////////////////////////////////////
			// 			//	Расчёт стоимости позиций END
			// 			////////////////////////////////////

			// 			$html .= '<tr  data-id="'.$Specificate['id'].'">
			// 			<td> '.$position['art'].'</td>
			// 			<td>'.$position['name'].'</td>
			// 			<td>'.($position['quantity']+$position['zapas']).'</td>
			// 			<td></td>
			// 			<td><span>'.$this->Price_for_the_goods.'</span> р.</td>
			// 			<td><span>'.$this->Price_of_printing.'</span> р.</td>
			// 			<td><span>'.$this->Price_of_no_printing.'</span> р.</td>
			// 			<td><span>'.$this->Price_for_the_position.'</span> р.</td>
			// 			<td></td>
			// 			<td></td>
			// 					</tr>';
			// 			$this->Price_of_position +=$this->Price_for_the_position; // прибавим к общей стоимости
			// 		}

			// 		$html .= '</table>';
			// 		$html .= '</td>';
			// 		$html .= '</tr>';
			// 		###############################
			// 		// строка с артикулами END
			// 		###############################

			// 		// получаем % оплаты
			// 		$percent_payment = ($this->Price_of_position!=0)?round($Specificate['payment_status']*100/$this->Price_of_position,2):'0.00';		
			// 		// собираем строку заказа
					
			// 		$html2 = '<tr data-id="'.$Specificate['id'].'" >';
			// 		$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
			// 		//'.$this->get_manager_name_Database_Html($Specificate['manager_id']).'
					
			// 		$html2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$rowspan.'"><span class="cabinett_row_hide'.$this->open_close_class.'"></span></td>';
					
			// 		$enable_check_for_order = '';
			// 		if($this->user_access == 1 || ($Specificate['order_num'] == 0  and $this->user_access == 5)){
			// 			$enable_check_for_order = '<div class="masterBtnContainer" data-manager_id="'.$Specificate['manager_id'].'" data-id="'.$Specificate['id'].'">';
			// 				$enable_check_for_order .= '<input type="checkbox" name="masterBtn" id="masterBtn'.$Specificate['id'].'"><label for="masterBtn'.$Specificate['id'].'"></label>';
			// 			$enable_check_for_order .= '</div>';	
			// 		}
					
			// 		/////////////////////////
			// 		// если хранящаяся в базу стоимость 
			// 		// не совпадает со стоимостью которая была выщетана - перезаписываем её на правильную 
			// 		// необходимо для записи там, где пусто
			// 		/////////////////////////////////
			// 		if ($this->Price_of_position != $Specificate['spec_price']) {
			// 			$this->save_price_specificate_Database($Specificate['id'],$this->Price_of_position);
			// 		}

			// 		// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
			// 		$this->order_num_for_User = Cabinet::show_order_num($Specificate['order_num']);

			// 		$html2_body .= '<td  class="check_show_me">'.$enable_check_for_order.'</td>
			// 					<td>'.$Specificate['create_time'].'<br>'.$this->get_manager_name_Database_Html($Specificate['manager_id'],1).'</td>
			// 					<td>'.$this->order_num_for_User.'</td>
			// 					<td>'.$this->get_client_name_Database($Specificate['client_id'],1).'</td>
			// 					<td>'.$this->get_specification_link($Specificate,$Specificate['client_id'],$Specificate['create_time']).'</td>
			// 					<td class="buh_uchet_for_spec" data-id="'.$Specificate['id'].'"></td>
			// 					<td class="invoice_num">'.$Specificate['number_the_bill'].'</td>
			// 					<td><input type="text" class="payment_date" readonly="readonly" value="'.(((int)$Specificate['payment_date']!=0)?$Specificate['payment_date']:'').'"></td>
								
			// 					<td><span>'.$percent_payment.'</span> %</td>
			// 					<td><span class="payment_status_span edit_span">'.$Specificate['payment_status'].'</span>р</td>
			// 					<td><span>'.$this->Price_of_position.'</span> р.</td>
			// 					<td class="buch_status_select">'.$this->decoder_statuslist_buch($Specificate['buch_status']).'</td>';
			// 		$html3 = '</tr>';


			// 		$html1 .= $html2 .$html2_body.$html3. $html;
			// 		// запрос по одной строке без подробностей
			// 		if($id_row){return $html2_body;}
			// 	}

			// 	// добавляем скрытую кнопку для объединения выбранных счётов/спецификаций в заказ
			// 	$html1 .= '<div id="export_in_order_div">';
			// 		$html1 .= '<ul>';
			// 			$html1 .= '<li id="create_in_order_button">Создать заказ</li>';
			// 			// для админа добавляем возможность приркрепления спецификации уже к существующему заказу
			// 			if($this->user_access == 1){
			// 				$html1 .= '<li id="add_for_other_order">Добавть к существующему заказу</li>';
			// 			}
			// 		$html1 .= '</ul>';
			// 	$html1 .= '</div>';


			// 	echo $table_head_html;
			// 	echo $html1;

			// 	echo '</table>';
			// }

		//////////////////////////
		//	Section - Заказы  -- end
		//////////////////////////
	

}
?>
