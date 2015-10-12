<?php
	
	class Paperwork extends Cabinet{

		function __construct($id_row = 0,$user_access,$user_id){	
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
		//	Section - Предзаказ  -- start
		//////////////////////////			
    		////////////////////////////////////////////////////////////
			//	методы запроса данных из базы для заказа и предзаказа
			///////////////////////////////////////////////////////////

				// запрос спецификаций для предзаказа
				protected function get_the_specificate_paperworck_Database($id_row=0){
					$where = 0;
					global $mysqli;


					$query = "SELECT 
						`".CAB_BILL_AND_SPEC_TBL."`.*, 
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y ')  AS `create_time`,
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`payment_date`,'%d.%m.%Y ')  AS `payment_date`,
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date_limit`,'%d.%m.%Y ')  AS `shipping_date_limit`,
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
							foreach (array_keys($this->paperwork_status) as $key => $status) {
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
									// $paperwork_status_string = '';
									// foreach (array_keys($this->buch_status_service) as $key => $status) {
									// 	$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
									// }
									$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` IN ('request_expense','get_the_bill_oferta', 'reget_the_bill')";
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
									break;$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`buch_status` = 'refund_in_a_row'";
								// case 'all_the_bill': // все счета

								// 	break;
								
								
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
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`manager_id` = '".$this->user_id."'";
								$where = 1;
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

				// запрос спецификаций для заказа
				protected function get_the_specificate_order_Database($id_row=0){
					$where = 0;
					global $mysqli;
				
					$query = "SELECT 
						`".CAB_BILL_AND_SPEC_TBL."`.*, 
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y ')  AS `create_time`,

						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date`,'%d.%m.%Y %H:%i:%s')  AS `shipping_date`,
						
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date_limit`,'%d.%m.%Y')  AS `shipping_date_limit`,
						
						DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`payment_date`,'%d.%m.%Y')  AS `payment_date`
						
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
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_BILL_AND_SPEC_TBL."`.`manager_id` = '".$this->user_id."'";
								$where = 1;
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

				// получаем позиции к спецификациям
				protected function get_the_position_with_specificate_Database($id){
					global $mysqli;
					$query = "
						SELECT 
							`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
							`".CAB_ORDER_DOP_DATA."`.`quantity`,	
							`".CAB_ORDER_DOP_DATA."`.`price_out`,	
							`".CAB_ORDER_DOP_DATA."`.`print_z`,	
							`".CAB_ORDER_DOP_DATA."`.`zapas`,	
							DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
							`".CAB_ORDER_MAIN."`.*,
							DATE_FORMAT(`".CAB_ORDER_MAIN."`.`approval_date`,'%d.%m.%Y %H:%i:%s')  AS `approval_date`
							
							FROM `".CAB_ORDER_MAIN."` 
							INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
							WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`the_bill_id` = '".$id."'";
						
					$query .= " ORDER BY `".CAB_ORDER_MAIN."`.`id` DESC";
						// echo $query;

					$result = $mysqli->query($query) or die($mysqli->error);
					$position_arr = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$position_arr[] = $row;
						}
					}
					return $position_arr;
				}
			//////////////////////////
			//	заказ создан
			//////////////////////////
				
				
				
				// получаем HTML спецификации
				private function table_specificate_for_order_Html(){
					// получаем массив документов к заказу
					$this->spec_arr = $this->table_specificate_for_order_Database($this->Order['id']);

					$html = '';
					$this->rows_num = 0;// порядковый номер строки
					$this->position_num = 1;// порядковый номер позиции
					$this->specificate_item = 0;// порядковый номер досументу
					$this->number_of_positions = 0; // количество позиций в досументу
					$this->position_num_in_order = 0; // количество позиций в заказе
					
					// обход массива документов
					foreach ($this->spec_arr as $key => $this->specificate) {
						

						/**
						 * для работы check_type_the_document_and_payment_date()						
						*/
						$this->approval_date = 0;// timestamp старшей даты утверждения макета 
						$this->one_position_is_not_approval = 0; // флаг оповещает о неутвержденной позиции
						


						// стоимость по документу (НАЧАЛЬНАЯ)
						$this->price_specificate = 0; 

						// подсчет номер документу
						$this->specificate_item++;


						// вывод html строк позиций по досументу 
						// запрашивается раньше спец-ии, чтобы подсчитать её стоимость
						$positions_rows = $this->table_order_positions_rows_Html();


						// проверяем не просрочена ли дата оплаты
						$this->check_type_the_document_and_payment_date();

						// проверка даты сдачи заказа
						$this->get_shipping_bigest_date_for_order();

						// получаем html строку со досументом
						$html .= $this->get_order_specificate_Html_Template();

						

						// если хранящаяся в базу стоимость 
						// не совпадает со стоимостью которая была рассчитана - перезаписываем её на правильную 
						if ($this->price_specificate != $this->specificate['spec_price']) {
							$this->save_price_specificate_Database($this->specificate['id'],$this->price_specificate);
						}

						// подсчёт стоимости заказа
						$this->price_order += $this->price_specificate;

						// строки позиций идут под спецификацией
						$html .= $positions_rows;
												
					}

					// если количество позиций не известно - сохраняем
					if($this->Order['number_of_positions'] == 0){
						$this->save_number_of_positions_in_specificate_row_Database($this->Order['id'],$this->position_num_in_order);
					}
					return $html;
				}

				//////////////////////////
				//	save
				//////////////////////////
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


				
				// ШАБЛОН строки спецификации
				private function get_order_specificate_Html_Template(){
					


					$this->rows_num++;
					$html = '';
					$html .= '<tr  class="specificate_rows" '.$this->open_close_tr_style.' data-id="'.$this->specificate['id'].'">';
						$html .= '<td colspan="4">';
							// спецификация
							// $html .= 'Спецификация '.$this->specificate_item;
							// ссылка на спецификацию
							$html .= ''.$this->get_document_link($this->specificate,$this->specificate['client_id'],$this->specificate['create_time']);
							
							// номер запроса
							$html .= '&nbsp;<span class="greyText"> (<a href="?page=client_folder&client_id='.$this->specificate['client_id'].'&query_num='.$this->specificate['query_num'].'" target="_blank" class="greyText">Запрос №: '.$this->specificate['query_num'].'</a>)</span>';
							// снабжение
							$html .= '&nbsp; <span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->specificate['snab_id'],8).'</span>';
							// дата лимита + % предоплаты, если работаем по дате
							if($this->specificate['date_type'] == 'date'){
								$html .= '<br> <span class="greyText '.$this->red_flag_date_limit.'" style="padding:5px">оплатить '.$this->specificate['prepayment'].'% и утвердить макет до: '.$this->specificate['shipping_date_limit'].'</span>';
							}else{
								//% предоплаты, если работаем по дате
								$html .= '<br> <span class="greyText '.$this->red_flag_date_limit.'" style="padding:5px">оплатить '.$this->specificate['prepayment'].'%</span>';
							}
							

						$html .='</td>';
						$html .= '<td>';
							$html .= 'сч: '.$this->specificate['number_the_bill'];
						$html .= '</td>';
						$html .= '<td>';
							$html .= '<span>'.$this->price_specificate.'</span>р';
						$html .= '</td>';
						$html .= '<td class="'.$this->red_flag_date_payment.'">';
							// % оплаты
							$html .= '<span class="greyText">оплачено: </span> '.$this->calculation_percent_of_payment($this->price_specificate, $this->specificate['payment_status']).' %'.(($this->specificate['payment_date']!='00.00.0000')?', <span class="greyText">от '.$this->specificate['payment_date'].'</span>':'');

						$html .= '</td>';

						$html .= '<td class="'.$this->red_flag_date_date_approval.'">';
							
							$html .= (($this->approval_date>0)?date('d.m.Y', $this->approval_date):'<!-- none -->').'<br>';
							// $html .= $this->approval_date.'';
						$html .= '</td>';
						// срок по ДС
						$html .= '<td contenteditable="true" class="deadline">'.$this->work_days.'</td>';
						
						//дата сдачи по документам
						$html .= '<td class="'.$this->red_flag_date_shipping_date.'">';
							
							// if($this->user_access == 1){
							// 	$html .= '<input type="text" name="date_of_delivery_of_the_specificate" class="date_of_delivery_of_the_specificate" value="'.$this->specificate_shipping_date.'" data-id="'.$this->specificate['id'].'">';

							// }else{
							$html .= $this->specificate_shipping_date.'<br>';
							// }
						$html .= '</td>';
						$html .= '<td>Бух.</td>';
						$html .= '<td class="buch_status_select">'.$this->decoder_statuslist_buch($this->specificate['buch_status'],0,$this->specificate).'</td>';
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
						// вычисляем крайнюю дату утверждения макета по всем позициям к по одному документу
						$this->get_position_approval_bigest_date();


						$this->rows_num++;// номер строки в таблице

						// если записываем порядковый номер позиции, если он ещё не присвоен
						if($this->position['sequence_number'] == 0){
							$this->save_sequence_number_of_position_Database($this->position['id'],$this->position_num);
							$this->position['sequence_number'] = $this->position_num;
						}

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
					$html .= '<td><span class="orders_info_punct">'.$this->position['sequence_number'].'п</span></td>';
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
							<div class="number_rezerv">'.$this->position['number_rezerv'].'</div>
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
						// $approval_date_timestamp  = strtotime($this->position['approval_date']);
						$html .= $this->get_Position_approval_date( $this->Position_approval_date = $this->position['approval_date'], $this->position['id'] );
						
						// $html .= '<br>'.$this->position['approval_date'];
						// $html .= '<br>'.strtotime($this->position['approval_date']);
					
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
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`create_time`,'%d.%m.%Y ')  AS `create_time`,
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`date_create_the_bill`,'%d.%m.%Y ')  AS `date_create_the_bill`,

					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date`,'%d.%m.%Y %H:%i:%s')  AS `shipping_date`,
					
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`shipping_date_limit`,'%d.%m.%Y')  AS `shipping_date_limit`,
					
					DATE_FORMAT(`".CAB_BILL_AND_SPEC_TBL."`.`payment_date`,'%d.%m.%Y')  AS `payment_date`
					
					FROM `".CAB_BILL_AND_SPEC_TBL."` WHERE `order_id` = '".$id."'";
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

				// ШАБЛОН заказа
				private function paperwork_the_order_is_create_Template($id_row=0){

					$where = 0;
					$html = '';
					$table_head_html = '
						<table id="general_panel_orders_tbl">
						<tr>
							<th colspan="3">Артикул/номенклатура</th>
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
						if($this->user_access ==5){
							$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
							$where = 1;
						}

						/*
							// // получаем статусы заказа
							// $order_status_string = '';
							// foreach (array_keys($this->order_status) as $key => $status) {
							// 	$order_status_string .= (($key>0)?",":"")."'".$status."'";
							// }	
						*/		
						// выбираем из базы только заказы being_prepared (в оформлении)
						$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN ('being_prepared','in_operation','maket_without_payment')";
						$where = 1;
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


					$table_order_row = '';		

					// создаем экземпляр класса форм
					$this->FORM = new Forms();


					// тут будут храниться операторы
					$this->Order['operators_listiong'] = '';


					// ПЕРЕБОР ЗАКАЗОВ
					foreach ($this->Order_arr as $this->Order) {		
						// переменные для вычисления даты сдачи заказа
						 	// обнуляются при начале обсчётак каждого заказа
							$this->order_shipping_date_timestamp = 0;
							$this->one_specificate_is_not_approval = 0; // одна из спецификаций не утверждена


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
							$table_order_row2_body .= '<td colspan="4" class="orders_info">';
								//$table_order_row2_body .= '<span class="greyText">№: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
									
									// исполнители заказа
									//$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
									// исполнители заказа
									// $table_order_row .= '<br>';
									$table_order_row2_body .= '<table class="curator_on_request">';
										$table_order_row2_body .= '<tr>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">Заказ №: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
											$table_order_row2_body .= '</td>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">Клиент: </span>'.$this->get_client_name_link_Database($this->Order['client_id']).'';
												
											$table_order_row2_body .= '</td>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">менеджер: <a href="'.$this->link_enter_to_filters('manager_id', $this->Order['manager_id']).'">'.$this->meneger_name_for_order.'</a></span>';

											$table_order_row2_body .= '</td>';
										$table_order_row2_body .= '</tr>';	
										$table_order_row2_body .= '<tr>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->Order['snab_id'],8).'</span>';
											$table_order_row2_body .= '</td>';
											$table_order_row2_body .= '<td>';
												$table_order_row2_body .= '<span class="greyText">дизайнер: '.$this->get_name_no_men_employee_Database_Html($this->Order['operator_id'],9).'</span>';
												// $table_order_row2_body .= '<span class="greyText">,&nbsp;&nbsp;&nbsp;   Компания: </span>'.$this->get_client_name_link_Database($this->Order['client_id']).'';
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
							// OLD
							//$table_order_row2_body .= '<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>';
							$table_order_row2_body .= '<td>';
								$table_order_row2_body .= $this->order_shipping_date;
							$table_order_row2_body .= '</td>';
							
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

			// счёт оплачен
			private function paperwork_payment_the_bill_Template($id_row=0){
				if(isset($_GET['client_id']) AND ($this->user_access == 5 || $this->user_access == 1)){
					global $quick_button;
					$quick_button = '<div class="quick_button_div"><a href="#" id="create_the_order" class="button add">Создать заказ</a></div>';	
				}else{
					if($this->user_access == 5 || $this->user_access == 1){ // бухгалтерия не создаёт заказы
						global $quick_button;
						$quick_button = '<div class="quick_button_div"><a href="#" id="create_the_order" class="button add disabled">Создать заказ</a></div>';	
					}
				}

				$this->get_paperwork_specificate_rows_Template();
			}

			// счёт выставлен
			private function paperwork_expense_Template($id_row=0){
				if(isset($_GET['client_id']) AND ($this->user_access == 5 || $this->user_access == 1)){
					global $quick_button;
					$quick_button = '<div class="quick_button_div"><a href="#" id="create_the_order" class="button add">Создать заказ</a></div>';	
				}else{
					if($this->user_access == 5 || $this->user_access == 1){ // бухгалтерия не создаёт заказы
						global $quick_button;
						$quick_button = '<div class="quick_button_div"><a href="#" id="create_the_order" class="button add disabled">Создать заказ</a></div>';	
					}
				}

				$this->get_paperwork_specificate_rows_Template();
			}
			
			// счёт заннулирован
			private function paperwork_cancelled_Template($id_row=0){
				$this->get_paperwork_specificate_rows_Template();
			}

			// возврат средств по счёту
			private function paperwork_refund_in_a_row_Template($id_row=0){
				$this->get_paperwork_specificate_rows_Template();
			}

			// все счета
			private function paperwork_all_the_bill_Template($id_row=0){
				$this->get_paperwork_specificate_rows_Template();
			}

			// счёт запрошен
			private function paperwork_requested_the_bill_Template($id_row=0){
				$this->get_paperwork_specificate_rows_Template();
			}

			// спецификация создана
			private function paperwork_create_spec_Template($id_row=0){
				$this->get_paperwork_specificate_rows_Template();
			}

			// шаблон выгрузки счетов (спецификаций)
			private function get_paperwork_specificate_rows_Template(){
				// запрос по спецификациям
				$this->get_the_specificate_paperworck_Database($id_row=0);
				
				// собираем html строк-предзаказов
				$html1 = '';
				if(count($this->Specificate_arr)==0){return 1;}

				$table_head_html = '
					<table class="cabinet_general_content_row" id="cabinet_general_content_row">
								<tr>
									<th id="show_allArt"></th>
									<th class="check_show_me"></th>
									<th>Дата создания документа</th>
									<th>Заказ</th>
									<th>Компания</th>	
									<th>Документ:</th>
									<th class="buh_uchet">Бух. учет</th>					
									<th class="invoice_num">Счёт</th>
									<th>Дата опл-ты</th>
									<th>% оплаты</th>
									<th>Оплачено</th>
									<th>стоимость в спец.</th>
									<th>статус БУХ</th>
								</tr>';

				foreach ($this->Specificate_arr as $this->specificate) {



					$invoice_num = $this->specificate['number_the_bill']; // номер счёта

						// получаем флаг открыт/закрыто
						$this->open__close = $this->get_open_close_for_this_user($this->specificate['open_close']);
						
					//////////////////////////
					//	open_close   -- end
					//////////////////////////

					// получаем массив позиций к спецификации
					$position_arr = $this->get_the_position_with_specificate_Database($this->specificate['id']);

					// СОБИРАЕМ ТАБЛИЦУ
					###############################
					// строка с артикулами START
					###############################
					$html = '<tr class="query_detail" '.$this->open_close_tr_style.'>';
					//$html .= '<td class="show_hide"><span class="this->cabinett_row_hide"></span></td>';
					$html .= '<td colspan="14" class="each_art" >';
					
					
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
						
							$this->GET_PRICE_for_position($position);				
						
						////////////////////////////////////
						//	Расчёт стоимости позиций END
						////////////////////////////////////

						$html .= '<tr  data-id="'.$this->specificate['id'].'">';
							$html .= '<td> '.$position['art'].'</td>';
							$html .= '<td>'.$position['name'].'</td>';
							$html .= '<td>'.($position['quantity']+$position['zapas']).'</td>';
							$html .= '<td></td>';
							$html .= '<td><span>'.$this->Price_for_the_goods.'</span> р.</td>';
							$html .= '<td><span>'.$this->Price_of_printing.'</span> р.</td>';
							$html .= '<td><span>'.$this->Price_of_no_printing.'</span> р.</td>';
							$html .= '<td><span>'.$this->Price_for_the_position.'</span> р.</td>';
							$html .= '<td></td>';
							$html .= '<td></td>';
						$html .= '</tr>';
						$this->Price_of_position +=$this->Price_for_the_position; // прибавим к общей стоимости
					}

					$html .= '</table>';
					$html .= '</td>';
					$html .= '</tr>';
					###############################
					// строка с артикулами END
					###############################

					// получаем % оплаты
					$percent_payment = ($this->Price_of_position!=0)?round($this->specificate['payment_status']*100/$this->Price_of_position,2):'0.00';		
					// собираем строку заказа
					
					$html2 = '<tr data-id="'.$this->specificate['id'].'" >';
					$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
					//'.$this->get_manager_name_Database_Html($this->specificate['manager_id']).'
					
					$html2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$rowspan.'"><span class="cabinett_row_hide'.$this->open_close_class.'"></span></td>';
					
					$enable_check_for_order = '';
					if($this->user_access == 1 || ($this->specificate['order_num'] == 0  and $this->user_access == 5)){
						$enable_check_for_order = '<div class="masterBtnContainer" data-manager_id="'.$this->specificate['manager_id'].'" data-id="'.$this->specificate['id'].'">';
							$enable_check_for_order .= '<input type="checkbox" name="masterBtn" id="masterBtn'.$this->specificate['id'].'"><label for="masterBtn'.$this->specificate['id'].'"></label>';
						$enable_check_for_order .= '</div>';	
					}
					
					/////////////////////////
					// если хранящаяся в базу стоимость 
					// не совпадает со стоимостью которая была выщетана - перезаписываем её на правильную 
					// необходимо для записи там, где пусто
					/////////////////////////////////
					if ($this->Price_of_position != $this->specificate['spec_price']) {
						$this->save_price_specificate_Database($this->specificate['id'],$this->Price_of_position);
					}

					// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
					$this->order_num_for_User = Cabinet::show_order_num($this->specificate['order_num']);

					$html2_body .= '<td  class="check_show_me">'.$enable_check_for_order.'</td>';
						$html2_body .= '<td>'.$this->specificate['create_time'].'<br>'.$this->get_manager_name_Database_Html($this->specificate['manager_id'],1).'</td>';
						$html2_body .= '<td>'.$this->order_num_for_User.'</td>';
						$html2_body .= '<td>'.$this->get_client_name_Database($this->specificate['client_id'],1).'</td>';
						$html2_body .= '<td>';
							$html2_body .= $this->get_document_link($this->specificate,$this->specificate['client_id'],$this->specificate['create_time']);
							// дата лимита, если работаем по дате
							$html2_body .= ($this->specificate['date_type'] == 'date')?'<br> <span class="dop_grey_small_info" style="padding:5px">оплатить '.$this->specificate['prepayment'].'% и утвердить макет до: '.$this->specificate['shipping_date_limit'].'</span>':'';
						$html2_body .='</td>';
						$html2_body .= '<td class="buh_uchet_for_spec" data-id="'.$this->specificate['id'].'"></td>';
						$html2_body .= '<td class="invoice_num">'.$this->specificate['number_the_bill'].'</td>';
						$html2_body .= '<td><input type="text" class="payment_date" readonly="readonly" value="'.(((int)$this->specificate['payment_date']!=0)?$this->specificate['payment_date']:'').'"></td>';
						$html2_body .= '<td><span>'.$percent_payment.'</span> %</td>';
						$html2_body .= '<td><span class="payment_status_span edit_span">'.$this->specificate['payment_status'].'</span>р</td>';
						$html2_body .= '<td><span>'.$this->Price_of_position.'</span> р.</td>';
						$html2_body .= '<td class="buch_status_select">'.$this->decoder_statuslist_buch($this->specificate['buch_status'],0,$this->specificate).'</td>';
					$html3 = '</tr>';


					$html1 .= $html2 .$html2_body.$html3. $html;
					// запрос по одной строке без подробностей
					if($id_row){return $html2_body;}
				}

				// добавляем скрытую кнопку для объединения выбранных счётов/спецификаций в заказ
				$html1 .= '<div id="export_in_order_div">';
					$html1 .= '<ul>';
						$html1 .= '<li id="create_in_order_button">Создать заказ</li>';
						// для админа добавляем возможность приркрепления спецификации уже к существующему заказу
						if($this->user_access == 1){
							$html1 .= '<li id="add_for_other_order">Добавть к существующему заказу</li>';
						}
					$html1 .= '</ul>';
				$html1 .= '</div>';


				echo $table_head_html;
				echo $html1;

				echo '</table>';
			}

		//////////////////////////
		//	Section - Предзаказ  -- end
		//////////////////////////
	

}
?>
