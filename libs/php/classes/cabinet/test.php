<?php
oreach ($positions_rows as $key => $this->position) {
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


							$this->services_num  = count($this->services_print);
											
							// если услуг для производства в данной позиции нет - переходм к следующей
							if($this->services_num == 0){continue;}

							
							
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

							// $html_row_2 .= '</tr>';	


							$html .= $this->get_service_content_for_production($this->position,$this->services_print,$html_row_1,$html_row_2);

							$this->position_item += $this->services_num;
						}