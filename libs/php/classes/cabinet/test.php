<?php
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
							$html2_body .= ($this->specificate['date_type'] == 'date')?'<br> <span class="greyText" style="padding:5px">оплатить '.$this->specificate['prepayment'].'% и утвердить макет до:'.$this->specificate['shipping_date_limit'].'</span>':'';
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