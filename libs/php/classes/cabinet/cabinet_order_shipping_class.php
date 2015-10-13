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

	class Order_shipping extends Cabinet{
		
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
							//if(isset($_GET['subsection']) && $_GET['subsection'] != 'production'){
								$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
								$where = 1;
							//}								
						}

						// фильтрация по заказу
						if($this->filtres_order != ''){
							$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_order;
							$where = 1;
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
						$query .= $this->filtres_order_sort;
					
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
			//////////////////////////
			//	заказ создан
			//////////////////////////
				// html шаблон заказа (МЕН/СНАБ/АДМИН)
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
							<th  colspan="2">статус</th>
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
						// переменные для вычисления даты сдачи заказа
						// обнуляются при начале обсчётак каждого заказа
						$this->order_shipping_date = '';
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
						
						if($table_order_positions_rows == ''){continue;}


						// формируем строку с информацией о заказе
						$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'" data-order_num="'.$this->Order['order_num'].'">';
						
						$this->meneger_name_for_order = $this->get_name_employee_Database_Html($this->Order['manager_id']);
						//////////////////////////
						//	тело строки заказа -- start ---
						//////////////////////////
							$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.($this->rows_num+1).'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>';
							$table_order_row2_body .= '<td colspan="5" class="orders_info">';
								// $table_order_row2_body .= '<span class="greyText">№: </span><a href="'.$this->link_enter_to_filters('order_num',$this->order_num_for_User).'">'.$this->order_num_for_User.'</a> <span class="greyText">';
									
									// исполнители заказа
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
							
							
							// стоимость заказа
							$table_order_row2_body .= '<td><span class="show_the_full_information">'.$this->price_order.'</span> р.</td>';
							
							// бух учет
							$table_order_row2_body .= '<td class="buh_uchet_for_order" data-id="'.$this->Order['order_num'].'"></td>';
							
							// платёжная информация
							$this->Order_payment_percent = $this->calculation_percent_of_payment($this->price_order, $this->Order['payment_status']);

							// комментарии
							$table_order_row2_body .= '<td>';								
								$table_order_row2_body .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
							$table_order_row2_body .= '</td>';
								
							// срок по ДС
							$table_order_row2_body .= '<td></td>';
							// $table_order_row2_body .= '<td><input type="text" name="date_of_delivery_of_the_order" class="date_of_delivery_of_the_order" value="'.$this->Order['date_of_delivery_of_the_order'].'"></td>';
							// дата сдачи / отгрузки
							$table_order_row2_body .= '<td>';
								$table_order_row2_body .= $this->order_shipping_date;
							$table_order_row2_body .= '</td>';

							$table_order_row2_body .= '<td style="width:78px"><span class="greyText black">Заказа: </span></td>';
							$table_order_row2_body .= '<td class="'.(($this->user_access == 5 || $this->user_access == 1)?'order_status_chenge':'').'">'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
						
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

				// перебор документов (МЕН/СНАБ/АДМИН)
				private function table_specificate_for_order_Html(){
					// получаем массив документов к заказу
					$this->spec_arr = $this->table_specificate_for_order_Database($this->Order['id']);

					$html = '';
					$this->rows_num = 0;// порядковый номер строки
					$this->position_num = 1;// порядковый номер позиции
					$this->specificate_item = 0;// порядковый номер спецификации
					
					$this->position_num_in_order = 0; // сколько позиций в заказе
					
					// обход массива спецификаций
					foreach ($this->spec_arr as $key => $this->specificate) {
						/**
						 * для работы check_type_the_document_and_payment_date()						
						*/
						$this->approval_date = 0;// timestamp старшей даты утверждения макета 
						$this->one_position_is_not_approval = 0; // флаг оповещает о неутвержденной позиции

						// стоимость по спецификации (НАЧАЛЬНАЯ)
						$this->price_specificate = 0; 

						// подсчет номер спецификаций
						$this->specificate_item++;

						// вывод html строк позиций по спецификации 
						// запрашивается раньше спец-ии, чтобы подсчитать её стоимость
						$positions_rows = $this->table_order_positions_rows_Html();

						if($positions_rows == ''){continue;}

						// проверяем не просрочена ли дата оплаты
						$this->check_type_the_document_and_payment_date();

						// проверка даты сдачи заказа
						$this->get_shipping_bigest_date_for_order();

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

				// html шаблон строки документа (МЕН/СНАБ/АДМИН)
				// get_order_specificate_Html_Template()
				// ПЕРЕНЕСЕНО в cabinet_class.php

				// перебор позиций по документам (МЕН/СНАБ/АДМИН)
				private function table_order_positions_rows_Html(){    
					// получаем массив позиций 
					// метод positions_rows_Database() находится в cabinet_class.php
					$positions_rows = $this->positions_rows_Database($this->specificate['id']);
					$this->number_of_positions = count($positions_rows);
					$this->position_num_in_order += $this->number_of_positions;
					$html = '';    

					$this->position_item = 1;// порядковый номер позиции
					foreach ($positions_rows as $key => $this->position) {
						// вычисляем крайнюю дату утверждения макета по всем позициям к по одному документу
						$this->get_position_approval_bigest_date();

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

				// html шаблон вывода позиций  (МЕН/СНАБ/АДМИН)
				// get_order_specificate_position_Html_Template()
				// ПЕРЕНЕСЕНО в cabinet_class.php
				
				//////////////////////////
				//	Выгрузка в виде заказов
				//////////////////////////
					/*
						фильтрация встроена непосредственно в запросе
					*/
					// Готовые к запуску (Менеджер)
					private function for_shipping_ready_for_shipment_Template($id_row=0){
						if(!isset($_GET['order_num'])){
							$this->filtres_position = " `status_sklad` = 'ready_for_shipment'";
						}
						$this->order_standart_rows_Template($id_row=0);
					}
					
}

