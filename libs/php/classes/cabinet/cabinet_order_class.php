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
						
						//////////////////////////
						//	тело строки заказа -- start ---
						//////////////////////////
							$table_order_row2_body = '<td class="show_hide" '.$this->open_close_rowspan.'="'.($this->rows_num+1).'"><span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span></td>';
							$table_order_row2_body .= '<td colspan="5" class="orders_info">';
									
							// исполнители заказа
							$table_order_row2_body .= $this->performer_table_for_order();								

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

							$table_order_row2_body .= '<td style="width:78px"><span class="greyText black">'.(($this->user_access==8)?'':'Заказа:').' </span></td>';
							$table_order_row2_body .= '<td class="'.(($this->user_access == 5 || $this->user_access == 1 || $this->user_access == 9)?'order_status_chenge':'').'">'.(($this->user_access!=8)?$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']):'').'</td>';
						
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
						$this->poused_and_question = 1;
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

				// html шаблон строки документа (МЕН/СНАБ/АДМИН)
				// get_order_specificate_Html_Template()
				// ПЕРЕНЕСЕНО в cabinet_class.php

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
					private function orders_order_start_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}
					// В обработке (Менеджер)
					private function orders_order_in_work_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}

					// Статус макета (Менеджер)
					private function orders_design_for_one_men_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// В работе (Менеджер)
					private function orders_order_in_work_snab_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}

					// пауза/вопрос/ТЗ не корректно (Менеджер)
					private function orders_tpause_and_questions_Template($id_row=0){
						echo $this->wrap_text_in_warning_message('нужно делать фильтрацию');
						$this->order_standart_rows_Template($id_row=0);
					}	

					// все (Менеджер)
					private function orders_order_all_Template($id_row=0){
						$this->order_standart_rows_Template($id_row=0);
					}			


				
				/**
				 * выгрузка по шаблону (Дизайн/препресс)
				*/
					// Дизайн всё (Дизайн/препресс)
					private function orders_design_all_Template($id_row=0){
						$this->group_access = 9;
						// id начальника отдела дизайна
						$this->director_of_operations_ID = 80; 

						$this->design_rows($id_row=0);
					}

					// Ожидают распределения (Дизайн/препресс)
					private function orders_design_waiting_for_distribution_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// Разработать дизайн (Дизайн/препресс)
					private function orders_design_develop_design_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// Сверстать макет (Дизайн/препресс)
					private function orders_design_laid_out_a_layout_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// Правки (Дизайн/препресс)
					private function orders_design_edits_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// На согласовании (Дизайн/препресс)
					private function orders_design_on_agreeing_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// Подготовить в печать (Дизайн/препресс)
					private function orders_design_prepare_to_print_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// Пленки и клише (Дизайн/препресс)
					private function orders_design_films_and_cliches_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// пауза/вопрос/ТЗ не корректно (Дизайн/препресс)
					private function orders_design_pause_question_TK_is_not_correct_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// Готовые макеты (Дизайн/препресс)
					private function orders_design_finished_models_Template($id_row=0){
						$this->orders_design_all_Template($id_row);
					}

					// private function orders_design_all_Template($id_row=0){
					// 	$this->group_access = 9;
					// 	// id начальника отдела дизайна
					// 	$this->director_of_operations_ID = 80; 

					// 	$this->design_rows($id_row=0);
					// }
					

					// ШАБЛОН строки документа (Дизайн/препрес)
					private function get_order_specificate_for_design_Html_Template(){
						$this->rows_num++;
						$html = '';
						$html .= '<tr  class="specificate_rows" '.$this->open_close_tr_style.' data-id="'.$this->specificate['id'].'">';
							$html .= '<td colspan="6">';
								// спецификация
								// $html .= $this->specificate_item;
								// ссылка на спецификацию
								$html .= '&nbsp; '.$this->get_document_link($this->specificate,$this->specificate['client_id'],$this->specificate['create_time']);
								// номер запроса
								$html .= '&nbsp;<span class="greyText"> (<a href="?page=client_folder&client_id='.$this->specificate['client_id'].'&query_num='.$this->specificate['query_num'].'" target="_blank" class="greyText">Запрос №: '.$this->specificate['query_num'].'</a>)</span>';
								// снабжение
								$html .= '&nbsp; <span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->specificate['snab_id'],8).'</span>';

							$html .='</td>';
							$html .= '<td>';
								$html .= 'сч: '.$this->specificate['number_the_bill'];
							$html .= '</td>';
							// $html .= '<td>';
							// 	$html .= '<span>'.$this->price_specificate.'</span>р';
							// $html .= '</td>';
							// $html .= '<td>';
							// 	// % оплаты
							// 	$html .= '<span class="greyText">оплачено: </span> '.$this->calculation_percent_of_payment($this->price_specificate, $this->specificate['payment_status']).' %';

							// $html .= '</td>';
							// $html .= '<td>';
							// $html .= '</td>';
							// $html .= '<td contenteditable="true" class="deadline">'.$this->specificate['deadline'].'</td>';
							// $html .= '<td>';
							// 	$html .= '<input type="text" name="date_of_delivery_of_the_specificate" class="date_of_delivery_of_the_specificate" value="'.$this->specificate['date_of_delivery'].'" data-id="'.$this->specificate['id'].'">';
							// $html .= '</td>';
							$html .= '<td><span class="greyText">Бухгалтерия</span></td>';
							$html .= '<td class="buch_status_select_for_design">'.$this->decoder_statuslist_buch($this->specificate['buch_status']).'</td>';
						$html .= '</tr>';
						return $html;
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
							
							// if($positions_rows != ''){
							// 	$this->position_item++;
							// 	$html .= $this->get_order_specificate_for_design_Html_Template();	
							// }
							

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
							//$this->price_order += $this->price_specificate;

							// строки позиций идут под спецификацией
							$html .= $positions_rows;
													
						}

						return $html;
					}

					// получаем позиции (Дизайн/препрес)
					private function table_order_positions_rows_for_design_Html(){	//$this->position_item	
						// получаем массив позиций 
						$positions_rows = $this->positions_rows_Database($this->specificate['id']);
						$this->number_of_positions = count($positions_rows);	
						
						$html = '';	
						$html_row_1 = '';

						
						// формируем строки позиций	(перебор позиций)	
						$n = 0;	
						foreach ($positions_rows as $key => $this->position) {

							$this->Position_status_list = array(); // в переменную заложим все статусы

							$this->id_dop_data = $this->position['id_dop_data'];
							
							// ТЗ на изготовление продукцию для НЕКАТАЛОГА
							// для каталога и НЕкаталога способы хранения и получения данной информации различны
							$this->no_cat_TZ = '';
							if(trim($this->position['type'])!='cat' && trim($this->position['type'])!=''){
								// доп инфо по некаталогу берём из json 
								$this->no_cat_TZ = $this->decode_json_no_cat_to_html($this->position);
							}

							// получаем массив услуг по позиции
							$this->position_services_arr = $this->get_order_dop_uslugi( $this->id_dop_data );

							// выборка только массива услуг дизайна
							$this->services_design = $this->get_dop_services_for_production( $this->position_services_arr , 9 );
							// выборка только массива услуг производства
							$this->services_production = $this->get_dop_services_for_production( $this->position_services_arr , 4 );
							// выборка услуг оутсорс (которые ведёт снабжение)
							$this->services_production_snab = $this->get_dop_services_for_production( $this->position_services_arr , 8 );

							$this->services_num  = count($this->services_design);
							
							$n++;				
							// если услуг для производства в данной позиции нет - переходм к следующей
							if($this->services_num == 0){continue;}
							if(isset($_GET['subsection']) && $_GET['subsection'] == 'design_films_and_cliches'){
								if($this->check_the_status_films('готовы к отправке')){continue;}
							}

								// плёнки клише
								$html_row_5 = '<td class="show-backlight" rowspan="'.($this->services_num).'">';
									// подрядчик печати
									$html_row_5 .= $this->position['suppliers_name'];
									// пленки / клише
									$film_and_cliches = $this->get_film_and_cliches();

									$html_row_5 .= $film_and_cliches;
								$html_row_5 .= '</td>';
								
								// // порядковый номер позиции в заказе
								$html_row_1 = '<td rowspan="'.($this->services_num).'"><span class="orders_info_punct">'.$this->position['sequence_number'].'п<br>('.$this->Order['number_of_positions'].')</span></td>';
								
								// // описание позиции
								$html_row_1 .= '<td  rowspan="'.($this->services_num).'" >';

									// вставляем номер заказа
									$html_row_1 .= '№ '.$this->order_num_for_User.'<br>';
									// наименование товара
									$html_row_1 .= '<span class="art_and_name">'.$this->position['art'].'  '.$this->position['name'].'</span>';
									// описание некаталожной продукции
									$html_row_1 .= $this->no_cat_TZ;
									// места нанесения
									$html_row_1 .= $this->get_service_printing_list();
									$html_row_1 .= 'Тираж: '.($this->position['quantity']) .' шт.';	
									$html_row_1 .= '<div class="linked_div">'.identify_supplier_by_prefix($this->position['art']).'</div>';
								$html_row_1 .= '</td>';

								// дата сдачи макета
								$html_row_2 = '<td class="show-backlight" rowspan="'.$this->services_num.'">';
									$this->shipping_date_limit = ($this->specificate['shipping_date_limit'] != '00.00.0000')?$this->specificate['shipping_date_limit']:'';
									$html_row_2 .= '<span class="greyText">'.$this->shipping_date_limit.'</span>';
								$html_row_2 .= '</td>';

								// дата утв. макета
								$html_row_3 = '<td class="show-backlight"  rowspan="'.$this->services_num.'" ><span class="greyText">';
									// проверка на отсутствие пустого значения
									if($this->position['approval_date']!='' && $this->position['approval_date']!='00.00.0000 00:00:00'){
										$approval_date_timestamp = strtotime($this->position['approval_date']);
										if($approval_date_timestamp != 0){
											// дата
											$this->approval_date = date('d.m.Y',$approval_date_timestamp);
											
											$html_row_3 .= $this->approval_date;
											// время
											$this->approval_time = date('H:i',$approval_date_timestamp);
											if($this->approval_time != '00:00'){
												$html_row_3 .= '<br>'.$this->approval_time.'';
											}
										}
									}
								$html_row_3 .= '</td>';

								// дата печати
								$html_row_4 = '<td class="show-backlight" rowspan="'.$this->services_num.'" >';
									$html_row_4 .= $this->get_date_printing();
								$html_row_4 .= '</td>';




							$html .= $this->get_service_content_for_designer_operations($this->position,$this->services_design,$html_row_1, $html_row_2, $html_row_3, $html_row_4, $html_row_5);
							
							// $this->position_item++;
							// $this->position_item = count($positions_rows) * $this->services_num+1;
							
						}		
						return $html;
					}

					// строки услуг (Дизайн/препрес)
					private function get_service_content_for_designer_operations($position, $services_arr, $html_row_1, $html_row_2,  $html_row_3, $html_row_4, $html_row_5){
						if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
							$this->Services_list_arr = $this->get_all_services_Database();
						}

						$gen_html = '';
						$n = 0;

						$service_count = count($services_arr);
						
						// перебираем услуги по позиции
						foreach ($services_arr as $key => $this->service) {

							// получаем  json
							$this->print_details_dop_Json = (trim($this->service['print_details_dop'])=="")?'{}':$this->service['print_details_dop'];
							// декодируем json  в массив
							$this->print_details_dop = json_decode($this->print_details_dop_Json, true);



							// получаем наименование услуги
							$this->Service_name = (isset($this->Services_list_arr[ $this->service['uslugi_id'] ]['name'])?$this->Services_list_arr[ $this->service['uslugi_id'] ]['name']:'данная услуга в базе не найдена');

							$html = '';							

								// операция
								$html .= '<td class="show-backlight js-modal--tz-prodaction" data-id="'.$this->service['id'].'">';
									$html .= $this->Service_name;

									// перебираем производственные услуги к которым дизайнер/оператор будет готовить макет или дизайн
									foreach ($this->services_production as $key_production_service => $production_service) {
										$html .= '<div class="seat_number_logo">';
											$html .= 'место'.($key_production_service+1).' ('.$this->Services_list_arr[ $production_service['uslugi_id'] ]['name'].'): ';
											$html .= $production_service['logotip'];
										$html .='</div>';	
									}

									// перебор услуг оутсорса
									foreach ($this->services_production_snab as $key_production_service => $production_service) {
										$html .= '<div class="seat_number_logo">';
											$html .= 'место'.($key_production_service+1).' ('.$this->Services_list_arr[ $production_service['uslugi_id'] ]['name'].' "А"): ';
											$html .= $production_service['logotip'];
										$html .='</div>';	
									}

								$html .= '</td>';

								
								
								// статусы плёнок
								if($n==0){// это дополнительные колонки в уже сформированную строку
									$html .= $html_row_5;
								}

								// дата печати
								if($n==0){
									$html .= $html_row_4;
								}
								// дата сдачи макета
								if($n==0){
									$html .= $html_row_2;
								}								
								
								// дата утв. макета
								if($n==0){
									$html .= $html_row_3;
								}								

								// исполнитель
								$html .= '<td class="show-backlight">';
									$html .= $this->get_production_userlist_Html($this->service['performer_id'],$this->service['id']);
								$html .= '</td>';

								// статус дизайна
								$html .= '<td class="show-backlight">';
									$html .= $this->get_statuslist_uslugi_Dtabase_Html($this->service['uslugi_id'],$this->service['performer_status'],$this->service['id'], $this->service['performer']);
								$html .= '</td>';

							if($n==0){// это дополнительные колонки в уже сформированную строку
								// оборачиваем колонки в html переданный в качестве параметра
								$gen_html .= '<tr class="position-row position-row-production" id="position_row_'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>'.$html_row_1 . $html .'</tr>';
							}else{
								$gen_html .= '<tr class="position-row position-row-production" id="position_row_'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>'.$html.'</tr>';
							}

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
								$table_head_html .= '<th rowspan="2">Дата печати</th>';
								$table_head_html .= '<th rowspan="2">Дата сдачи<br>макета</th>';
								$table_head_html .= '<th rowspan="2">Дата утв.<br>макета</th>';
								$table_head_html .= '<th rowspan="2">исполнитель</th>';
								$table_head_html .= '<th rowspan="2">статус дизайна</th>';
								// $table_head_html .= '<th rowspan="2">статус снабжение</th>';
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
								
								// исполнители заказа
								$table_order_row .= $this->performer_table_for_order();

								$table_order_row .= '</td>';
								
								// дата сдачи
								$table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
								// комментарии по заказу
								$table_order_row .= '<td>';								
									$table_order_row .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
								$table_order_row .= '</td>';

								$table_order_row .= '<td></td>';
								$table_order_row .= '<td></td>';
								$table_order_row .= '<td></td>';
								// $table_order_row .= '<td>статус заказа</td>';
								$table_order_row .= '<td class="'.(($this->user_access == 5 || $this->user_access == 1 || $this->user_access == 9)?'order_status_chenge':'').'">'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
								
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
						 			switch ($user_access) {
						 				case '5':
						 					switch ($_GET['subsection']) {
												case 'question_pause':// пауза/вопрос/ТЗ не корректно
													if($service['performer_status'] == 'ТЗ не корректно' || $service['performer_status'] == 'пауза' || $service['performer_status'] == 'вопрос'){
														$new_arr[] = $service;
													}
													break;
												default:// добавляем услугу в новый массив 
								 					$new_arr[] = $service;
													break;
												}
						 					break;
						 				case '9':// фильтрация услуг дизайна по зелёным вкладкам дизайна
											switch ($_GET['subsection']) {
												case 'design_waiting_for_distribution'://Ожидают распределения
												/*
													"все входящие в заказ имеющие услуги дизайн и пре-пресс

													статус по умолчанию: ""ожидает обработки""

													вспомнил важное - либо МАКЕТ БЕЗ ОПЛАТЫ!!!"
												*/
													if($service['performer_id'] == 0){
														$new_arr[] = $service;
													}
													break;
												case 'design_develop_design'://Разработать дизайн
												/*
													"все позиции с услугой из папки дизайн по которым назначено имя ДИЗа и стоит статус: 
													задача принята, ожидает
													в работе
													"
												*/
													if($this->Services_list_arr[ $service['uslugi_id'] ]['parent_id'] == 53){
														$new_arr[] = $service;
													}
													break;
												case 'design_laid_out_a_layout': //Сверстать макет
												/*
													все позиции с услугой из папки пре-пресс по которым назначено имя ДИЗа и стоит статус: 
													задача принята, ожидает
													дизайн-эскиз утвержден
													в работе

													вспомнил важное - либо МАКЕТ БЕЗ ОПЛАТЫ!!!
												*/
													if($this->Services_list_arr[ $service['uslugi_id'] ]['parent_id'] == 50 && $service['performer_id'] != 0){
														if($service['performer_status'] == 'дизайн-эскиз утвержден' || $service['performer_status'] == 'в работе' || $service['performer_status'] == 'задача принята, ожидает'){
															$new_arr[] = $service;
														}
													}
													break;
												case 'design_edits': // правки
													if($service['performer_status'] == 'исправить макет' || $service['performer_status'] == 'исправить дизайн' || substr($service['performer_status'], 0, 14) == 'очередь'){
														$new_arr[] = $service;
													}
													break;												
												case 'design_on_agreeing': // на согласовании
													if($service['performer_status'] == 'дизайн-эскиз готов' || $service['performer_status'] == 'оригинал-макет готов' || $service['performer_status'] == 'Печатная Pdf на утверждении'){
														$new_arr[] = $service;
													}
													break;
												case 'design_prepare_to_print': // Подготовить в печать
													if($service['performer_status'] == 'подготовить в печать'){
														$new_arr[] = $service;
													}
													break;
												case 'design_pause_question_TK_is_not_correct': // пауза/вопрос/ТЗ не корректно
													if($service['performer_status'] == 'ТЗ не корректно' || $service['performer_status'] == 'стоимость работ не корректна' || $service['performer_status'] == 'пауза' || $service['performer_status'] == 'вопрос'){
														$new_arr[] = $service;
													}
													break;

												case 'design_finished_models': // Готовые макеты
													if($service['performer_status'] == 'услуга выполнена' || $service['performer_status'] == 'макет отправлен в СНАБ'){
														$new_arr[] = $service;
													}
													break;
												
												default:
													// добавляем услугу в новый массив 
								 					$new_arr[] = $service;
													break;
											}
						 					break;
						 				
						 				default:
						 					$new_arr[] = $service;
						 					break;
						 			}
						 			
						 			
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
							foreach ($this->print_details_dop as $key => $text){
								$html .= (($n>0)?', ':'').$this->dop_inputs_listing[$key]['name_ru'].': '.base64_decode($text);
								$n++;
							}
						}

						return $html;
					}	

					// проверка плёнок на статус "готовы к отправке"
					private function check_the_status_films($status){
						// echo '<pre>';
						// print_r($this->services_production);
						// echo '</pre>';
						if (empty($this->services_production)) {
							return 1;
						}
						foreach ($this->services_production as $key => $service) {
							if($service['film_photos_status'] == $status){
								return 0;
							}
						}
						return 1;
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
					// получаем дату печати
					private function get_date_printing(){
						// если услуг печати нет - выходим
						if(empty($this->services_production)){return '';}
						
						$html = '';
						//return $this->print_arr($this->services_production);
						// перебираем услуги печати
						$n = 1;
						foreach ($this->services_production as $key => $production_service) {
							$html .= '<div class="designe_date_utv_maket"><span class="greyText">'.$this->format_the_date($production_service['date_work']).'</span></div>';
						}
						return $html;
					}

					// преобразуем время
					private function format_the_date($date, $excuse = ''){
						if($date == '00.00.0000 00:00'){
							return '<br>отсутствует';
						}
						$timestamp = strtotime($date);
						$date_new = date('d.m.Y',$timestamp);
						
						//$time = date('H:i',$timestamp);
						$excuse = $excuse.' ';
						return $date_new;
					}

					// отдаёт имя пользователя, список пользователей или 
					private function get_production_userlist_Html($performer_id, $service_id){

						// получаем список пользователей производства
						$this->get_production_userlist_Database();
						// echo '<pre>';
						// print_r($this->userlist);
						// echo '</pre>';
							
						$html = '';
						$check = 0;
						$options_tag_empty = '<option value=""></option>';
						// регулируем вывод в зависимости от уровня доступа
						switch ($this->user_access) {
							case '1': // для админа список
								$html .= '<select data-row_id="'.$service_id.'" data-order_id="'.$this->Order['id'].'" class="production_userlist">';
								
								$options_tag = '';
								foreach ($this->userlist as $key => $user) {
									$checked = ($performer_id == $user['id'])?' selected="selected"':'';
									$options_tag .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['last_name'].' '.$user['name'].'</option>';
									if($checked != ''){$check = 1;}
								}

								if($check == 0){
									$options_tag_empty = '<option value="" selected="selected"></option>';
								}

								$html .= $options_tag_empty.$options_tag;
								
								$html .= '</select>';
								return $html;
								break;
							case '4': 
								if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
									$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
									
									$options_tag = '';
									foreach ($this->userlist as $key => $user) {
										$checked = ($performer_id == $user['id'])?' selected="selected"':'';
										$options_tag .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['last_name'].' '.$user['name'].'</option>';
										if($checked != ''){$check = 1;}
									}

									if($check == 0){
										$options_tag_empty = '<option value="" selected="selected"></option>';
									}

									$html .= $options_tag_empty.$options_tag;

									$html .= '</select>';
									return $html;
								}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
									if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
										$user = $this->userlist[$performer_id];
										return $user['last_name'].' '.$user['name'];
									}else{
										// $user = $this->userlist[$this->user_id];
										// return '<input type="button" value="Взать в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['name'].' '.$user['last_name'].'" class="get_in_work_service">';
										if(isset($this->userlist[$this->user_id])){
											$user = $this->userlist[$this->user_id];
											return '<input type="button" value="Взять в работу" data-order_id="'.$this->Order['id'].'" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['last_name'].' '.$user['name'].'" class="get_in_work_service">';
										}else{
											return 'Не назначен';
										}
									};
								}
								break;
							case '9': 
								if($this->user_id == $this->director_of_operations_ID){// исключение для начальника производства - он должен иметь возможность распределять работу между работниками производства
									$html .= '<select class="production_userlist" data-row_id="'.$service_id.'">';
									$options_tag = '';
									foreach ($this->userlist as $key => $user) {
										$checked = ($performer_id == $user['id'])?' selected="selected"':'';
										$options_tag .= '<option value="'.$user['id'].'" '.$checked.'>'.$user['last_name'].' '.$user['name'].'</option>';
										if($checked != ''){$check = 1;}
									}

									if($check == 0){
										$options_tag_empty = '<option value="" selected="selected"></option>';
									}

									$html .= $options_tag_empty.$options_tag;
									$html .= '</select>';
									return $html;
								}else{// для произ-ва выдаём кнопку взять в работу или транслируем имя пользователя, который взялся за заказ или был назначен для него
									if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
										$user = $this->userlist[$performer_id];
										return $user['last_name'].' '.$user['name'];
									}else{
										if(isset($this->userlist[$this->user_id])){
											$user = $this->userlist[$this->user_id];
											return '<input type="button" value="Взять в работу" name="get_in_work" data_user_ID="'.$this->user_id.'" data-service_id="'.$service_id.'" data-user_name="'.$user['last_name'].' '.$user['name'].'" class="get_in_work_service">';
										}else{
											return 'Не назначен';
										}
										
									};
								}
								break;
							
							default: // для остальных просто то, что хранится в ячейке
								if(trim($performer_id)!='' && isset($this->userlist[$performer_id])){
									$user = $this->userlist[$performer_id];
									return $user['last_name'].' '.$user['name'];
								}else{
									return 'исполнитель не назначен';
								};
								break;
						}			
					}


					// места нанесения
					private function get_service_printing_list(){
						//если нет прикрепленных мест печати - выходим
						if(empty($this->services_production) && empty($this->services_production_snab)){return '';}


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
								//$n = 1;
							}
							$html .= 'место '.$n++.': ';
							
							// декодируем dop_inputs для услуги печати
							$decode_dop_inputs_information_for_servece = $this->decode_dop_inputs_information_for_servece($service);
							$html .= (($decode_dop_inputs_information_for_servece != "")?$decode_dop_inputs_information_for_servece:'<span style="color:red">информация отсутствует</span>').'<br>';
						}
						// перебираем услуги нанесения по позиции
						foreach ($this->services_production_snab as $key => $service) {
							if($service_name != $this->Services_list_arr[$service['uslugi_id']]['name']){
								if($service_name == ''){$html .= '<br>';}
								$service_name = $this->Services_list_arr[$service['uslugi_id']]['name'];
								$html .= $service_name.'<br>';
								//$n = 1;
							}
							$html .= 'место '.$n++.': ';
							
							// декодируем dop_inputs для услуги печати
							$decode_dop_inputs_information_for_servece = $this->decode_dop_inputs_information_for_servece($service);
							$html .= (($decode_dop_inputs_information_for_servece != "")?$decode_dop_inputs_information_for_servece:'<span style="color:red">информация отсутствует</span>').'<br>';
						}
						return $html;	

					}	

				/**
				 * выгрузка по шаблону Склад
				*/
					private function orders_stock_Template($id_row=0){
						$this->group_access = 7;
						//////////////////////////
						//	фильтры 
						//////////////////////////
							// фильтр по ожидаемой дате
							if(isset($_GET['snab_id'])  && $_GET['snab_id'] != ''){
								// $pattern = '/([0-2]\d|3[01])\.(0\d|1[012])\.(\d{4})/';
								// if(preg_match($pattern, $_GET['date_delivery_product'])){
									if($this->filtres_order != ''){
										$this->filtres_order .= " AND";
									}

									$this->filtres_order .= " `snab_id` = '".$_GET['snab_id']."'";
									
								// }
							}
							// фильтр по ожидаемой дате
							if(isset($_GET['date_delivery_product'])  && $_GET['date_delivery_product'] != ''){
								// $pattern = '/([0-2]\d|3[01])\.(0\d|1[012])\.(\d{4})/';
								// if(preg_match($pattern, $_GET['date_delivery_product'])){
									if($this->filtres_position != ''){
										$this->filtres_position .= " AND";
									}

									$this->filtres_position .= " `date_delivery_product` = '".$_GET['date_delivery_product']."' AND `status_snab` = 'waits_products'";
									
								// }
							}

							// фильтр по дате отгрузки
							if(isset($_GET['shipping_date'])  && $_GET['shipping_date'] != ''){
								// $pattern = '/([0-2]\d|3[01])\.(0\d|1[012])\.(\d{4})/';
								// if(preg_match($pattern, $_GET['shipping_date'])){
									if($this->filtres_specificate != ''){
										$this->filtres_specificate .= " AND";
									}

									$this->filtres_specificate .= " DATE_FORMAT(shipping_date , '%d.%m.%Y') = '".$_GET['shipping_date']."'";
									
								// }
							}
						// id начальника отдела дизайна
						$this->director_of_operations_ID = 79; 

						echo $this->stock_rows($id_row=0);
					}

					// всё (Склад)
					private function orders_stock_all_Template($id_row=0){
						$this->orders_stock_Template($id_row);
					}

					// продукция ожидается (Склад)
					private function orders_stock_waits_products_Template($id_row=0){
						$this->filtres_position = " `status_snab` = 'waits_products'";
						
						$this->orders_stock_Template($id_row);
					}

					// На складе (Склад)
					private function orders_stock_goods_in_stock_Template($id_row=0){
						$this->filtres_position = " `status_sklad` = 'goods_in_stock'";
						$this->orders_stock_Template($id_row);
					}

					// В производстве у поставщика (Склад) - отправлен на аутсорс
					private function orders_stock_sended_on_outsource_Template($id_row=0){
						$this->filtres_position = " `status_sklad` = 'sended_on_outsource'";
						$this->orders_stock_Template($id_row);
					}

					// заказы на отгрузку (Склад)
					private function orders_stock_checked_and_packed_Template($id_row=0){
						$this->filtres_position = " `status_sklad` = 'ready_for_shipment'";
						$this->orders_stock_Template($id_row);
					}

					// отгруженные (Склад)
					private function orders_stock_goods_shipped_for_client_Template($id_row=0){
						$this->filtres_position = " `status_sklad` = 'goods_shipped_for_client'";
						$this->orders_stock_Template($id_row);
					}

					// HTML заказа (Склад)
					private function stock_rows($id_row=0){
						$where = 0;
						// скрываем левое меню
						$html = '';
						$table_head_html = '';
						if ($this->user_access == $this->group_access) {
							$table_head_html .= '<style type="text/css" media="screen">
								#cabinet_left_coll_menu{display:none;}
							</style>';	
						}
						
						// формируем шапку таблицы вывода
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
							$table_order_positions_rows = $this->table_specificate_for_order_for_stock_Html();
							////////////////////////////////////
							// test retutn $table_order_positions_rows
							////////////////////////////////////
							// echo '<div style="border:1px solid red">'.$table_order_positions_rows.'</div>';
							if($table_order_positions_rows == ''){continue;}

							
							// формируем строку с информацией о заказе
							$table_order_row .= '<tr class="order_head_row" data-id="'.$this->Order['id'].'">';
								$table_order_row .= '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->position_item.'">
														<span class="cabinett_row_hide_orders'.$this->open_close_class.'"></span>
													</td>';
								$table_order_row .= '<td colspan="3" class="orders_info">';
									
								// исполнители заказа
								$table_order_row .= $this->performer_table_for_order();
								
								$table_order_row .= '</td>';
								$table_order_row .= '<td>';													
									$table_order_row .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>	';
								$table_order_row .= '</td>';
								$table_order_row .= '<td>';		
								$table_order_row .= '</td>';
								$table_order_row .= '<td>';		
								$table_order_row .= '</td>';
								$table_order_row .= '<td>';													
								$table_order_row .= '</td>';
								// $table_order_row .= '<td><strong>'.$this->Order['date_of_delivery_of_the_order'].'</strong></td>';
												$table_order_row .= '<td><strong></strong></td>';
								$table_order_row .= '<td><span class="greyText">заказа: </span></td>';
								$table_order_row .= '<td>'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
							$table_order_row .= '</tr>';
							// включаем вывод позиций 
							$table_order_row .= $table_order_positions_rows;
						}		

						$html = $table_head_html.$table_order_row.'</table>';
						return $html;
					}
					// спецификации + позиции (Склад)
					private function table_specificate_for_order_for_stock_Html(){
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
							$positions_rows = $this->table_order_positions_rows_for_stock_Html();
							// echo '<pre>';
							// print_r($this->specificate);
							// echo '</pre>';
							// echo '$positions_rows = '.$positions_rows.'br';
								
							// вывод спецификаций для про-ва
							// if($positions_rows != ''){
							// 	$this->position_item++;
							// 	$html .= $this->get_order_specificate_for_stock_Html_Template();	
							// }

							// подсчёт стоимости заказа
							$this->price_order += $this->price_specificate;

							// строки позиций идут под спецификацией
							$html .= $positions_rows;
													
						}
						// echo $html;
						return $html;
					}
					// спецификации (Склад)
					private function get_order_specificate_for_stock_Html_Template(){
						$this->rows_num++;
						$html = '';
						$html .= '<tr  class="specificate_rows" '.$this->open_close_tr_style.' data-id="'.$this->specificate['id'].'">';
							$html .= '<td colspan="7">';
								// спецификация
								// $html .= $this->specificate_item;
								// ссылка на спецификацию
								$html .= '&nbsp; '.$this->get_document_link($this->specificate,$this->specificate['client_id'],$this->specificate['create_time']);
								// номер запроса
								$html .= '&nbsp;<span class="greyText"> (<a href="?page=client_folder&client_id='.$this->specificate['client_id'].'&query_num='.$this->specificate['query_num'].'" target="_blank" class="greyText">Запрос №: '.$this->specificate['query_num'].'</a>)</span>';
								// снабжение
								$html .= '&nbsp; <span class="greyText">снабжение: '.$this->get_name_no_men_employee_Database_Html($this->specificate['snab_id'],8).'</span>';

							$html .='</td>';
							$html .= '<td>';
								$html .= 'сч: '.$this->specificate['number_the_bill'];
							$html .= '</td>';
							// $html .= '<td>';
							// 	$html .= '<span>'.$this->price_specificate.'</span>р';
							// $html .= '</td>';
							// $html .= '<td>';
							// 	// % оплаты
							// 	$html .= '<span class="greyText">оплачено: </span> '.$this->calculation_percent_of_payment($this->price_specificate, $this->specificate['payment_status']).' %';

							// $html .= '</td>';
							// $html .= '<td>';
							// $html .= '</td>';
							// $html .= '<td contenteditable="true" class="deadline">'.$this->specificate['deadline'].'</td>';
							// $html .= '<td>';
							// 	$html .= '<input type="text" name="date_of_delivery_of_the_specificate" class="date_of_delivery_of_the_specificate" value="'.$this->specificate['date_of_delivery'].'" data-id="'.$this->specificate['id'].'">';
							// $html .= '</td>';
							$html .= '<td>Бух.</td>';
							$html .= '<td class="buch_status_select_for_design">'.$this->decoder_statuslist_buch($this->specificate['buch_status']).'</td>';
						$html .= '</tr>';
						return $html;
					}

					// HTML позиции (Склад)
					private function table_order_positions_rows_for_stock_Html(){			
						// получаем массив позиций заказа
						$positions_rows = $this->positions_rows_Database($this->specificate['id']);
						$html = '';	

						// $this->position_item = 1;// порядковый номер позиции
						// формируем строки позиций	(перебор позиций)		
						foreach ($positions_rows as $key => $this->position) {
							
							$this->id_dop_data = $this->position['id_dop_data'];

							$this->logotip = $this->get_content_logotip($this->id_dop_data);
							
							$html .= '<tr  class="position-row position-row-production" id="position_row_'.($key+2).'" data-id="'.$this->position['id'].'" '.$this->open_close_tr_style.'>';
							// // порядковый номер позиции в заказе
							$html .= '<td><span class="orders_info_punct">'.$this->position['sequence_number'].'п<br>('.$this->Order['number_of_positions'].')</span></td>';
							// // описание позиции
							$html .= '<td>';
							
							// наименование товара
							$html .= '<span class="art_and_name">'.$this->position['art'].'  '.$this->position['name'].'</span>';
						
							$html .= '</td>';
							// тираж
							$html .= '<td>';
								$html .= '<div class="quantity">'.($this->position['quantity']+$this->position['zapas']).'</div>';
							$html .= '</td>';


							// логотип
							$html .= '<td><span class="greyText">'.$this->logotip.'</span></td>';
							
							// поставщик товара  
							$html .= '<td>
										<div class="supplier">'.$this->get_supplier_name($this->position['art']).'</div>
									</td>';
							// № резерва
							$html .= '<td>
										<div class="number_rezerv">'.base64_decode($this->position['number_rezerv']).'</div>
									</td>';

							// подрядчик печати
							$html .= '<td>
										<div>'.$this->position['suppliers_name'].'</div>
									</td>';

							// дата отгрузки
							$html .= '<td>';
								if($this->specificate['shipping_date'] != '' && $this->specificate['shipping_date'] != '00.00.0000 00:00:00'){
									$shipping_date_timestamp = strtotime($this->specificate['shipping_date']);
										$shipping_date_date = date('d.m.Y',$shipping_date_timestamp);
										$shipping_date_time = date('H:i',$shipping_date_timestamp);
										//$html_row_3 .= '<span class="greyText">'.$shipping_date_date.(($shipping_date_time!='00:00')?'<br>к '.$shipping_date_time:'').'</span>';
									$html .= '<a href="'.$this->link_enter_to_filters('shipping_date',date('d.m.Y',strtotime($this->specificate['shipping_date']))).'">'.$shipping_date_date.'</a>'.(($shipping_date_time!='00:00')?'<br>к '.$shipping_date_time:'');
									// $html .= '<div>'.$this->Order['date_of_delivery_of_the_order'].'</div>';
								}
							$html .= '</td>';

							// статус товара
							$html .= '<td>
										<div>'.$this->decoder_statuslist_sklad($this->position['status_sklad'],$this->position['id']).'</div>
									</td>';
							// статус снабжение
							$html .= '<td>
										<div>'.$this->decoder_statuslist_snab($this->position['status_snab'],$this->position['date_delivery_product'],0,$this->position['id']).'</div>
									</td>';

							$html .= '</tr>';
							$this->position_item++;
						}				
						return $html;
					}
				

				/**
				 * выгрузка по шаблону Снабжение
				*/

					// Все (Снабжение)
					private function orders_snab_all_Template($id_row=0){
						$this->filtres_order .= (($this->filtres_order!="")?" AND":"")." `global_status` = 'in_work'";
						$this->group_access = 8;
						// id начальника отдела производства
						$this->director_of_operations_ID = 78; 

						echo $this->order_standart_rows_Template($id_row=0);
					}

					// Запуск в обработку (Снабжение)
					private function orders_snab_starting_in_processing_Template($id_row=0){
						// $this->filtres_order = " `snab_id` = '0'";
						$this->orders_snab_all_Template($id_row);
					}

					// В обработке (Снабжение)
					private function orders_snab_in_Progress_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->filtres_position = " `status_snab` NOT IN ('is_pending','in_operation','in_production','question')";
						$this->orders_snab_all_Template($id_row);
					}
					// Макеты в работу (Снабжение)
					private function orders_snab_mock_ups_of_the_work_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->orders_snab_all_Template($id_row);
					}
					// Ожидают (Снабжение)
					private function orders_snab_waiting_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->orders_snab_all_Template($id_row);
					}
					// Продукция (Снабжение)
					private function orders_snab_products_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->orders_snab_all_Template($id_row);
					}
					// В производстве (Снабжение)
					private function orders_snab_in_the_production_of_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->orders_snab_all_Template($id_row);
					}
					// Наше производство (Снабжение)
					private function orders_snab_our_production_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->orders_production_Template($id_row);

					}
					// пауза/вопрос/ТЗ не корректно (Снабжение)
					private function orders_snab_pause_and_questions_Template($id_row=0){
						$this->filtres_order = " snab_id <> '0'";
						$this->orders_snab_all_Template($id_row);
					}

				/**
				 *	выгрузка позиций по шаблону Производство
				*/
					// Всё (Производство)
					private function orders_production_Template($id_row=0){
						$this->group_access = 4;
						// id начальника отдела производства
						$this->director_of_operations_ID = 87; 

						echo $this->production_rows($id_row=0);
					}

					// Ожидают распределения (Производство)
					private function orders_production_get_in_work_Template($id_row=0){
						$this->filtres_services = " `performer_status` = 'Ожидает обработки'";
						// $this->filtres_services = " `date_work` = '0000-00-00 00:00:00'";
						// $this->filtres_services .= " AND `performer_id` = '0'";

						$this->orders_production_Template($id_row=0);
					}
					// Поставлены в план (Производство)
					private function orders_set_in_the_plan_Template($id_row=0){
						$this->filtres_services = " `date_work` <> '0000-00-00 00:00:00'";
						$this->filtres_services .= " AND `performer_id` <> '0'";
						$this->orders_production_Template($id_row=0);
					}
					// трафарет (Ш+Т) (Производство)
					private function orders_production_stencil_shelk_and_transfer_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Шелкография (Производство)
					private function orders_production_shelk_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Термотрансфер (Производство)
					private function orders_production_transfer_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Тампопечать (Производство)
					private function orders_production_tampoo_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Тиснение (Производство)
					private function orders_production_tisnenie_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Доп. услуги (Производство)
					private function orders_production_dop_uslugi_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Проверка плёнок/клише (Производство)
					private function orders_production_plenki_and_klishe_Template($id_row=0){
						$this->orders_production_Template($id_row=0);
					}
					// Вопрос, пауза (Производство)
					private function orders_question_pause_Template($id_row=0){
						$this->filtres_services = " `performer_status` IN ('Вопрос','пауза')";
						$this->orders_production_Template($id_row=0);
					}

					// Услуга выполнена (Производство)
					private function orders_the_service_is_performed_Template($id_row=0){
						$this->filtres_services = " `performer_status` IN ('услуга выполнена')";
						$this->orders_production_Template($id_row=0);
					}


					/**
					 *  фильтрация услуг по subsection для производства
					 *
					 *  @param  		array()
					 *  @return 		array()
					*/	
						protected function filter_of_subsection_for_production($services_print){
							// фильтрация для производства
							if($this->user_access == 4){
								$services_print_NEW = array();
								foreach ($services_print as $key => $value) {
									switch ($_GET['subsection']) {
										// фильтр по статусу "ожидает обработки"
										case 'production_get_in_work':
										// echo strtotime($value['date_work']).'<br>';
										// echo $value['date_work'].'<br>';

											if($value['date_work'] == '0000-00-00 00:00' || strtotime($value['performer_id']) == 0){
												//echo $value['date_work'] .' -- '.strtotime($value['date_work']) . ' -- '.$value['performer_id'].'<br>';
												// if($value['performer_status'] != 'Ожидает обработки'){continue;}
												$services_print_NEW[] = $value;	
											}else{
												continue;
											
											}
											
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
						$table_head_html = '';
						if ($this->user_access == $this->group_access) {
							$table_head_html .= '<style type="text/css" media="screen">
								#cabinet_left_coll_menu{display:none;}
							</style>';	
						}
						
						
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
								<th rowspan="2">статус снабжение</th>
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
							// переменные для вычисления даты сдачи заказа
						 	// обнуляются при начале обсчётак каждого заказа
							$this->order_shipping_date = '';
							$this->order_shipping_date_timestamp = 0;
							$this->one_specificate_is_not_approval = 0; // одна из спецификаций не утверждена

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
									
									
								// исполнители заказа
								$table_order_row .= $this->performer_table_for_order();
								
								// дата сдачи
								$table_order_row .= '<td>';
									$table_order_row .= $this->order_shipping_date;
								$table_order_row .= '</td>';
								
								// комментарии по заказу
								$table_order_row .= '<td>';								
									$table_order_row .= '<span data-cab_list_order_num="'.$this->order_num.'" data-cab_list_query_num="'.$this->Order['query_num'].'"  class="icon_comment_order_show white '.Comments_for_order_class::check_the_empty_order_coment_Database($this->Order['order_num']).'"></span>';
								$table_order_row .= '</td>';

							
								$table_order_row .= '<td colspan="4"></td>';
								
							$table_order_row .= '</tr>';
							// включаем вывод позиций 
							$table_order_row .= $table_order_positions_rows;
						}		

						$html = $table_head_html.$table_order_row.'</table>';
						return $html;
					}

					// перебор документов (Производство)
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
							$positions_rows = $this->table_order_positions_rows_for_production_Html();
							
							// проверяем не просрочена ли дата оплаты
							$this->check_type_the_document_and_payment_date();

							// проверка даты сдачи заказа
							$this->get_shipping_bigest_date_for_order();
							
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
						foreach ($positions_rows as $key => $this->position) {
							if(($this->position['approval_date'] == '00.00.0000 00:00:00' || trim($this->position['approval_date']) == '') && $_GET['subsection'] != 'production'){ continue;}
							// вычисляем крайнюю дату утверждения макета по всем позициям к по одному документу
							$this->get_position_approval_bigest_date();

							$this->Position_status_list = array(); // в переменную заложим все статусы

							$this->id_dop_data = $this->position['id_dop_data'];

							
							// выборка только массива печати
							$this->services_print = $this->get_dop_services_for_production( $this->get_order_dop_uslugi( $this->id_dop_data ), 4 ,((isset($_GET['service_id']) && (int)$_GET['service_id']>0)?$_GET['service_id']:0));

							/**
						 	 * фильтрация для subsection для производства
						 	 */	
						 	$this->services_print = $this->filter_of_subsection_for_production($this->services_print);

						 	$this->services_production = $this->services_print;

							$this->services_num  = count($this->services_print);
											
							// если услуг для производства в данной позиции нет - переходм к следующей
							if($this->services_num == 0){continue;}

							if(isset($_GET['subsection']) && $_GET['subsection'] == 'production_plenki_and_klishe'){
								if($this->check_the_status_films('проверить наличие')){continue;}
							}
							
								// // порядковый номер позиции в заказе
								$html_row_1 = '<td rowspan="'.$this->services_num.'"><span class="orders_info_punct">'.$this->position['sequence_number'].'п<br>('.$this->Order['number_of_positions'].')</span></td>';
								
								// // описание позиции
								$html_row_1 .= '<td  rowspan="'.$this->services_num.'" >';
									// наименование товара
									$html_row_1 .= '<span class="art_and_name">'.$this->position['art'].'  '.$this->position['name'].'</span>';
								$html_row_1 .= '</td>';

								// склад, снабжение
								// $html .= 
								$html_row_2 = '<td rowspan="'.$this->services_num.'" >';
									$html_row_2 .= $this->decoder_statuslist_sklad($this->position['status_sklad'], $this->position['id']);
								$html_row_2 .= '</td>';
								$html_row_2 .= '<td rowspan="'.$this->services_num.'" >';
									$html_row_2 .= '<div>'.$this->decoder_statuslist_snab($this->position['status_snab'],$this->position['date_delivery_product'],0,$this->position['id']).'</div>';
								$html_row_2 .= '</td>';

								// дата сдачи
								$html_row_3 = '<td  rowspan="'.$this->services_num.'" class="show-backlight ">';
									$shipping_date_timestamp = strtotime($this->specificate['shipping_date']);
									$shipping_date_date = date('d.m.Y',$shipping_date_timestamp);
									$shipping_date_time = date('H:i',$shipping_date_timestamp);
									$html_row_3 .= '<span class="greyText">'.$shipping_date_date.(($shipping_date_time!='00:00')?'<br>к '.$shipping_date_time:'').'</span>';
								$html_row_3 .= '</td>';


							$html .= $this->get_service_content_for_production($this->position,$this->services_print,$html_row_1,$html_row_2,$html_row_3);

							$this->position_item += $this->services_num;
						}				
						return $html;
					}

					// HTML строки услуг (Производство)
					private function get_service_content_for_production($position, $services_arr, $html_row_1, $html_row_2, $html_row_3){
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

								// Цвета
								$html .= '<td class="show-backlight">';
									$html .= '<!--// ключ к полю возможно будет отличаться не в локальной версии... при изменении названия поля Пантоны... выгрузка информации сюда изменится -->';
									$html .= (isset($this->print_details_dop['Pantone'])?base64_decode($this->print_details_dop['Pantone']):'');
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
								if($n==0){// это дополнительные колонки в уже сформированную строку
									// дата сдачи
									$html .= $html_row_3;
								}							

								
								// дата работы start
								$html .= '<td class="show-backlight">';
									if($service['date_work']=='00.00.0000 00:00'){
										$date_work = ' - ';
									}else{
										$date_work = $service['date_work'];
									}
									if($this->user_access == 4 || $this->user_access == 1){
										$html .= '<input type="text" name="calendar_date_work"  value="'.$date_work.'" data-id="'.$service['id'].'" class="calendar_date_work">';
									}else{
										$html .= $date_work;
									}
								$html .= '</td>';
								$html .= '<td class="show-backlight">';
									if($service['date_ready']=='00.00.0000 00:00'){
										$date_ready = ' - ';
									}else{
										$date_ready = $service['date_ready'];
									}
									if($this->user_access == 4 || $this->user_access == 1){
										$html .= '<input type="text" name="calendar_date_ready"  value="'.$date_ready.'" data-id="'.$service['id'].'" class="calendar_date_ready">';
									}else{
										$html .= $date_ready;
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
								$html .= '<td class="show-backlight percentage_of_readiness"'.(($this->user_access == 4 || $this->user_access == 1)?' contenteditable="true"':'').' data-service_id="'.$service['id'].'">';
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
			// 					<td>'.$this->get_document_link($Specificate,$Specificate['client_id'],$Specificate['create_time']).'</td>
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
