<?php

private function paperwork_Template($id_row=0){
			$where = 0;
			global $mysqli;
			
			// простой запрос
			$array_request = array();

			
			$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".CAB_ORDER_ROWS."`";
				
			// фильтр по менеджеру
			$query .=" ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`manager_id` = '".$this->user_id."'";
			$where = 1;

			
			// получаем статусы предзаказа
			$paperwork_status_string = '';
			foreach (array_keys($this->paperwork_status) as $key => $status) {
				$paperwork_status_string .= (($key>0)?",":"")."'".$status."'";
			}
			// выбираем из базы только предзаказы (заказы не показываем)
			$query .= " ".(($where)?'AND':'WHERE')." `".CAB_ORDER_ROWS."`.`global_status` IN (".$paperwork_status_string.")";
			$where = 1;


			///////////////////////////////////
			//	execution filtration --- START
			///////////////////////////////////
				if($id_row){// если указан, осущевствляем вывод только одного заказа
					$query .=" AND `".CAB_ORDER_ROWS."`.`id` = '".$id_row."'";
				}else{
					// если указан id клиента, делаем выборку заказов по клиенту
					if(isset($_GET['client_id']) && $_GET['client_id']!=''){
						$query .=" AND `".CAB_ORDER_ROWS."`.`client_id` = '".(int)$_GET['client_id']."'";
					}
					// default
					$query .=" AND `".CAB_ORDER_ROWS."`.`global_status` = 'being_prepared' OR `".CAB_ORDER_ROWS."`.`global_status` = 'requeried_expense'";
				}

				// если указан id клиента, делаем выборку заказов по клиенту
				if(isset($_GET['client_id']) && $_GET['client_id']!=''){
					$query .=" AND `".CAB_ORDER_ROWS."`.`client_id` = '".(int)$_GET['client_id']."'";
				}

			/////////////////////////////////
			//	execution filtration --- END
			/////////////////////////////////
			
			//////////////////////////
			//	sorting
			//////////////////////////
				$query .= " ORDER BY `".CAB_ORDER_ROWS."`.`id` DESC";

			//////////////////////////
			//	check the query
			//////////////////////////
				// echo '*** $query = *** '.$query.'<br>';

			//////////////////////////
			//	query for get data
			//////////////////////////
				$result = $mysqli->query($query) or die($mysqli->error);
				$orders_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$orders_arr[] = $row;
					}
				}

			
			//////////////////////////
			//	collecting the query strings to HTML
			//////////////////////////
			$html1 = '';
			if(count($orders_arr)==0){return 1;}

			foreach ($orders_arr as $this->Order) {
				
				// цена заказа
				$this->price_order = 0;

				// запоминаем обрабатываемые номера заказа и запроса
				// номер запроса
				$this->query_num = $this->Order['query_num'];
				// номер заказа
				$this->order_num = $this->Order['order_num'];

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);


				$query = "
				SELECT 
					`".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data`,
					`".CAB_ORDER_DOP_DATA."`.`quantity`,	
					`".CAB_ORDER_DOP_DATA."`.`price_out`,	
					`".CAB_ORDER_DOP_DATA."`.`print_z`,	
					`".CAB_ORDER_DOP_DATA."`.`zapas`,	
					DATE_FORMAT(`".CAB_ORDER_MAIN."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".CAB_ORDER_MAIN."`.*,
					`".CAB_ORDER_ROWS."`.`id` AS `request_id`,
					`".CAB_ORDER_ROWS."`.`global_status`,
					`".CAB_ORDER_ROWS."`.`payment_status`,
					`".CAB_ORDER_ROWS."`.`number_pyament_list`
					FROM `".CAB_ORDER_MAIN."` 
					INNER JOIN `".CAB_ORDER_DOP_DATA."` ON `".CAB_ORDER_DOP_DATA."`.`row_id` = `".CAB_ORDER_MAIN."`.`id`
					LEFT JOIN `".CAB_ORDER_ROWS."` ON `".CAB_ORDER_ROWS."`.`id` = `".CAB_ORDER_MAIN."`.`order_num`
					WHERE `".CAB_ORDER_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".CAB_ORDER_MAIN."`.`order_num` = '".$this->Order['id']."'
					ORDER BY `".CAB_ORDER_MAIN."`.`id` ASC
			                
				";

				$positions_arr = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$positions_arr[] = $row;
					}
				}

				// СОБИРАЕМ ТАБЛИЦУ
				###############################
				// строка с артикулами START
				###############################
				$html = '<tr class="query_detail">';
				//$html .= '<td class="show_hide"><span class="thist_row_hide"></span></td>';
				$html .= '<td colspan="11" class="each_art">';
				
				
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


				$this->Order['price_out'] = 0; // общая стоимость заказа
				// ПЕРЕБОР ЗАКАЗА / ПРЕДЗАКАЗА
				foreach ($positions_arr as $this->position) {
					////////////////////////////////////
					//	Расчёт стоимости позиций START  
					////////////////////////////////////
						/*
							!!!!!!!!    ОПИСАНИЕ    !!!!!!!!!

							стоимость товара
							$this->Price_for_the_goods;	// $price_out
							стоимость услуг печати
							$this->Price_of_printing;
							стоимость услуг не относящихся к печати
							$this->Price_of_no_printing;
							общаяя цена позиции включает в себя стоимость услуг и товара
							$this->Price_for_the_position;
						*/
					
					$this->GET_PRICE_for_position($position);	


					$html .= '<tr  data-id="'.$this->Order['id'].'">
								<td> '.$this->position['id_dop_data'].'<!--'.$this->position['id_dop_data'].'|-->  '.$this->position['art'].'</td>
								<td>'.$this->position['name'].'</td>
								<td>'.($this->position['quantity']+$this->position['zapas']).'</td>
								<td></td>
								<td><span>'.$this->Price_for_the_goods.'</span> р.</td>
								<td><span>'.$this->Price_of_printing.'</span> р.</td>
								<td><span>'.$this->Price_of_no_printing.'</span> р.</td>
								<td><span>'.$this->Price_for_the_position.'</span> р.</td>
								<td></td>
								<td></td>
							</tr>';

					$this->Order['price_out'] += $this->Price_for_the_position; // прибавим к общей стоимости
				}

				$html .= '</table>';
				$html .= '</td>';
				$html .= '</tr>';
				###############################
				// строка с артикулами END
				###############################

				// получаем % оплаты
				$percent_payment = ($this->Order['price_out']!=0)?round($this->Order['payment_status']*100/$this->Order['price_out'],2):'0.00';		
				// собираем строку заказа
				
				$html2 = '<tr data-id="'.$this->Order['id'].'" >';
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
				//'.$this->get_manager_name_Database_Html($this->Order['manager_id']).'
				$html2_body = '<td class="show_hide" rowspan="'.$rowspan.'"><span class="cabinett_row_hide"></span></td>
							<td><a href="./?page=client_folder&section=order_tbl&order_num='.$this->order_num.'&order_id='.$this->Order['id'].'&client_id='.$this->Order['client_id'].'">'.$this->order_num_for_User.'</a></td>
							<td>'.$this->Order['create_time'].'</td>
							<td>'.$this->get_client_name_Database($this->Order['client_id'],1).'</td>
							<td class="invoice_num" contenteditable="true">'.$this->Order['invoice_num'].'</td>
							<td><input type="text" class="payment_date" readonly="readonly" this->Order="'.$this->Order['payment_date'].'"></td>
							<td class="number_payment_list" contenteditable="true">'.$this->Order['number_pyament_list'].'</td>
							<td><span>'.$percent_payment.'</span> %</td>
							<td><span class="payment_status_span edit_span"  contenteditable="true">'.$this->Order['payment_status'].'</span>р</td>
							<td><span>'.$this->Order['price_out'].'</span> р.</td>
							<td class="buch_status_select">'.$this->buch_status[$this->Order['buch_status']].'</td>
							<td class="select_global_status">'.$this->decoder_statuslist_order_and_paperwork($this->Order['global_status']).'</td>';
				$html3 = '</tr>';

				$html1 .= $html2 .$html2_body.$html3. $html;
				// запрос по одной строке без подробностей
				if($id_row){return $html2_body;}
			}

			


			echo '
			<table class="cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Номер</th>
								<th>Дата/время заведения</th>
								<th>Компания</th>						
								<th class="invoice_num">Счёт</th>
								<th>Дата опл-ты</th>
								<th>№ платёжки</th>
								<th>% оплаты</th>
								<th>Оплачено</th>
								<th>стоимость заказа</th>
								<th>стутус БУХ</th>
								<th>Статус заказа.</th>
							</tr>';
			echo $html1;
			echo '</table>';
		}