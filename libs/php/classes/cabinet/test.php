<?php
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

							$this->services_num  = count($this->services_design);
							
							$n++;				
							// если услуг для производства в данной позиции нет - переходм к следующей
							if($this->services_num == 0){continue;}
								
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
									$html_row_1 .= 'Тираж: '.($this->position['quantity']) .' шт.';	


									$html_row_1 .= '<div class="linked_div">'.identify_supplier_by_prefix($this->position['art']).'</div>';
								$html_row_1 .= '</td>';

								// $html_row_2 = '<td rowspan="'.$this->services_num.'">1</td>';
								$html_row_2 = '<td rowspan="'.$this->services_num.'" >
											<div>'.$this->decoder_statuslist_snab($this->position['status_snab'],$this->position['date_delivery_product'],0,$this->position['id']).'</div>
										</td>';

							// $html_row_2 .= '</tr>';	


							$html .= $this->get_service_content_for_designer_operations($this->position,$this->services_design,$html_row_1,$html_row_2);
							
							// $this->position_item++;
							// $this->position_item = count($positions_rows) * $this->services_num+1;
							
						}		