<?php
foreach ($position['SERVICES'] as $count => $service) {
					//////////////////////////////////////////////////////////
					//	объявляем переменные для подсчёта стоимости услуги  //
					//////////////////////////////////////////////////////////
					$this->Service_price_in = ($service['for_how']=='for_one')?$service['price_in']*$service['quantity']:$service['price_in'];// входящая  по услуге то, что было рассчитано клиенту
					$this->Service_price_out = $this->calc_summ_dop_uslug(array($service)); // исходящая по услуге
					$this->Service_price_pribl = $this->Service_price_out - $this->Service_price_in; // прибыль по услуге
					$this->Service_tir = ($service['for_how']=='for_one')?'<span>'.$service['quantity'].'</span>шт':'<span>  -  </span>'; // тираж по услуге
					$this->Service_Name = (isset($service['uslugi_id']))?$this->Services_list[$service['uslugi_id']]['name']:$service['uslugi_id']; // название услуги
					$this->Service_percent = $this->get_percent_Int($this->Service_price_in,$this->Service_price_out);

					/*	
						выводим кнопку отключения услуги только СНАБАМ, АДМИНАМ И АВТОРУ ДОБАВЛЕННОЙ УСЛУГИ, 
						а так же показываем кнопку у тех услуг которые не имеют автора - это услуги добавленные ещё в запросе
					*/
					if($service['author_id_added_services'] == 0 || $service['author_id_added_services'] == $this->user_id || $this->user_access == 1 || $this->user_access == 8){
						$this->Service_swhitch_On_Of = ((int)$service['on_of'] == 1)?'<span  data-id="'.$service['id'].'" class="on_of">+</span>':'<span  data-id="'.$service['id'].'" class="on_of minus">-</span>';
					}else{
						$this->Service_swhitch_On_Of = '';
					}
					


					switch ((int)$service['author_id_added_services']) {
							case 0: // для услуг добавленных из запроса
								$html .= '<tr class="provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html .= '<td>'.$this->Service_Name.'</td>';
									$html .= '<td>'.$this->Service_tir.'</td>';
									$html .= '<td><span class="service_price_in">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td><span>'.$this->Service_percent.'</span>%</td>';
									$html .= '<td><span class="service_price_out">'.$this->Service_price_out.'</span>р</td>';
									$html .= '<td><span class="service_price_pribl">'.$this->Service_price_pribl.'</span>р</td>';
									// то, что получилось по факту
									$html .= '<td class="postfaktum"></td>';
									$html .= '<td class="postfaktum">'.$this->Service_Name.'</td>';
									$html .= '<td class="postfaktum">'.$this->Service_tir.'</td>';
									$html .= '<td class="postfaktum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html .= '<td></td>';
								$html .= '</tr>';

								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in += $this->Service_price_in;	// входящая  по позиции то, что было рассчитано клиенту
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								break;
							
							default:// если указан id того, кто добавил услугу, то услуга была добавлена в заказ
								$html_added .= '<tr class="not_provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html_added .= '<td></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_in">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_out">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>';
									// то, что получилось по факту
									$html_added .= '<td class="postfaktum"></td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_Name.'</td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_tir.'</td>';
									if($this->user_access == 1 || $this->user_access == 8){
										$html_added .= '<td class="postfaktum added_postfactum"><input type="text" value="'.$this->Service_price_in.'" class="change_price_in_for_postfactum_added_service">р</td>';	
									}else{
										$html_added .= '<td class="postfaktum added_postfactum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									}
									
									$html_added .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html_added .= '<td></td>';
								$html_added .= '</tr>';
								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								// добавляем класс подсветки цены
								$this->GlobAdded_postfactum_class = 'added_postfactum_class td_shine';
								$added_postfactum_class = 'added_postfactum_class td_shine';
								
								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								
								break;
						}						
				}