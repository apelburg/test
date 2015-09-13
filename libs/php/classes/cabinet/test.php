<?php
private function table_order_positions_rows_Html(){    
                         // получаем массив позиций заказа
                         $positions_rows = $this->positions_rows_Database($this->specificate['id']);
                         
                         $html = '';    

                         $this->position_item = 1;// порядковый номер позиции
                         foreach ($positions_rows as $key => $this->position) {
                              $this->rows_num++;
                              $this->Position_status_list = array(); // в переменную заложим все статусы

                              $this->id_dop_data = $this->position['id_dop_data'];
                              ////////////////////////////////////
                              //   Расчёт стоимости позиций START  
                              ////////////////////////////////////
                              
                              
                              $this->GET_PRICE_for_position($this->position);                   
                                   
                              ////////////////////////////////////
                              //   Расчёт стоимости позиций END
                              ////////////////////////////////////              
                              
                              $html .= $this->get_order_specificate_Html_Template();  

                              // добавляем стоимость позиции к стоимости заказа
                              $this->price_order += $this->Price_for_the_position;
                              $this->position_item++;
                              $this->position_num++;
                         }



                         return $html;
                    }
                    protected function get_order_spcificate_Htmal_Template(){
                          $html .= '<tr class="positions_rows row__'.$this->position_num.'" data-cab_dop_data_id="'.$this->id_dop_data.'" data-id="'.$this->position['id'].'" '.$this->open_close_tr_style.'>';
                              // порядковый номер позиции в заказе
                              $html .= '<td><span class="orders_info_punct">'.$this->position_num.'п</span></td>';
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
                                   $html .= $this->get_Position_approval_date( $this->Position_approval_date = $this->position['approval_date'], $this->position['id'] );
                              $html .= '</td>';

                              $html .= '<td><!--// срок по ДС по позиции --></td>';

                              // дата сдачи
                              // тут м.б. должна быть дата сдачи позиции ... но вроде как мы все позиции по умолчанию сдаём в срок по заказу, а если нет, то отгружаем частично по факту готовности, а следовательно нам нет необходимости вставлять для позиций редактируемое поле с датой сдачи
                              $html .= '<td><!--// дата сдачи по позиции --></td>';


                              // получаем статусы участников заказа в две колонки: отдел - статус
                              $html .= $this->position_status_list_Html($this->position);
                              $html .= '</tr>'; 
                    }