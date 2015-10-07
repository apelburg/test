<?php
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
										<div class="number_rezerv">'.$this->position['number_rezerv'].'</div>
									</td>';

							// подрядчик печати
							$html .= '<td>
										<div>'.$this->position['suppliers_name'].'</div>
									</td>';

							// дата отгрузки
							$html .= '<td>';
								// $html .= '<div>'.$this->Order['date_of_delivery_of_the_order'].'</div>';
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