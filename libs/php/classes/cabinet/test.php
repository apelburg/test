<?php

//////////////////////////
		//	Section - Запросы
		//////////////////////////
		protected function requests_Template($id_row = 0){
			$where = 0;
		 	// для обсчёта суммы за тираж			
			
			include_once ('./libs/php/classes/rt_class.php');

			include_once ('./libs/php/classes/comments_class.php');

			$array_request = array();
			global $mysqli;
	
			$query = "SELECT 
				`".RT_LIST."`.*, 
				(UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)-UNIX_TIMESTAMP())*(-1) AS `time_attach_manager_sec`,
				SEC_TO_TIME(UNIX_TIMESTAMP()-UNIX_TIMESTAMP(`os__rt_list`.`time_attach_manager`)) AS `time_attach_manager`,
				
				DATE_FORMAT(`".RT_LIST."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
				FROM `".RT_LIST."`";
			
			if($id_row==1){
				$query .= " ".(($where)?'AND':'WHERE')." WHERE `".RT_LIST."`.`id` = '".$id_row."'";
				$where = 1;
			}else{				
				/////////////////////////
				// фильтрация по статусам запросов
				/////////////////////////
				// 
				// статусы могут быть трёх (3) типов:
				// not_process - не обработанные:
				// 		те, что приходят от клиентов через корзину, и прикрепляются к тому или иному менеджеру
				// in_work - в работе
				// 		те, что менеджер завёл сам или взял из необработанных, которые в свою очередь ему отдал админ 
				// history - история
				//  	сюда попадают все запросы после того как из запроса создана спецификация и сгенерирован предзаказ
				//
				//////////////////////////
				//	в последствии:
				// 1 - необходимо запретить рт для запросов попавших в историю
				// 2 - необходимо сделать возможность копирования исторического запроса из истории в работу, при этом цены на услуги вероятно есть смысл пересчитать по новой
				//////////////////////////
				// делаем фильтрацию в зависимости от того по какому фильтру мы собираемся выбирать выдачу
				
				switch ($_GET['subsection']) {
					case 'history':
						$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`status` = 'history'";
						$where = 1;
						break;
					case 'no_worcked_men':
						$query .= " ".(($where)?'AND':'WHERE')." (`".RT_LIST."`.`status` = 'not_process' OR `".RT_LIST."`.`status` = 'new_query')";
						$where = 1;
						break;

					case 'in_work':
						$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`status` = 'in_work' ";
						$where = 1;
						break;
					default:
						break;
				}

				// если знаем id клиента - выводим только заказы по клиенту
				if(isset($_GET['client_id'])){
					$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`client_id` = '".$_GET['client_id']."'";
					$where = 1;
				}
			}

			// последний запрос всегда ввеорху
			$query .= ' ORDER BY `id` DESC'; 
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$this->Requests_arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->Requests_arr[] = $row;
				}
			}

			$general_tbl_row = '';
			// собираем html строк-запросов 
			$html = '';
			foreach ($this->Requests_arr as $this->Request) {
				// получаем позиции по запросу
				$this->positions_arr = $this->request_Template_recuestas_main_rows_Database($this->Request['query_num']);
				

				// если позиции отсутствуют - выходим из цикла (не показываем запрос)
				if(empty($this->positions_arr)){ continue;}
				//////////////////////////
				//	open_close   -- start
				//////////////////////////
					// получаем флаг открыт/закрыто
					$this->open__close = $this->get_open_close_for_this_user($this->Request['open_close']);
					
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

				/*
					в эту переменную запишется 0 если при переборе вариантов 
					не встретится ни одного некаталожного товара
					потом проверим и если все товары в запросе каталожные вывод данного запроса отменяем
				*/
				$enabled_echo_this_query = 0;

				
				// наименование продукта
				$name_product = ''; 
				// порядковый номер варианта расчёта одного и того же продукта
				$name_count = 1;
				
				// Html строки вариантов 
				$variant_row = '';

				// счетчик кнопок показа каталожных позиций
				// необходим для ограничения до одной кнопки
				$count_button_show_catalog_variants=0;

				// перебор вариантов
				foreach ($this->positions_arr as $position) {
					////////////////////////////////////
					//	Расчёт стоимости позиций START  
					////////////////////////////////////
					/*
						!!!!!!!!    ОПИСАНИЕ    !!!!!!!!!

						стоимость товара
						$this->Price_for_the_goods;
						стоимость услуг печати
						$this->Price_of_printing;
						стоимость услуг не относящихся к печати
						$this->Price_of_no_printing;
						общаяя цена позиции включает в себя стоимость услуг и товара
						$this->Price_for_the_position;
					*/
					$this->GET_PRICE_for_position($position);				
					
					////////////////////////////////////
					//	Расчёт стоимости позиций END
					////////////////////////////////////
					
					
					//////////////////////////
					//	собираем строки вариантов по каждой позиции
					//////////////////////////
					// 
					if($name_product != $position['name']){$name_product = $position['name']; $name_count = 1;}
					$variant_row .= '<tr data-id_dop_data="'.$position['id_dop_data'].'" class="'.$position['type'].'_1">
						<td>'.$position['art'].'</td>
						<td><a class="go_to_position_card_link" href="./?page=client_folder&section=rt_position&id='.$position['id'].'">'.$position['name'].'</a> <span class="variant_comments_dop">( Вариант '.$name_count++.' )</span></td>
						<td>'.$position['quantity'].'</td>
						<td></td>
						<td>'.$this->Price_for_the_goods.'</td>
						<td>'.$this->Price_of_printing.'</td>
						<td>'.$this->Price_of_no_printing.'</td>
						<td>'.$this->Price_for_the_position.'</td>
						<td></td>
						<td data-type="'.$position['type'].'" data-status="'.$position['status_snab'].'" class="'.$position['status_snab'].'_'.$this->user_access.' '.$this->Request['status'].'_status_snab_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($position['status_snab']).'</td>
					</tr>';
				}

				//////////////////////////
				//	собираем строку с номером запроса (шапку заказа)
				//////////////////////////
				switch ($this->Request['status']) {
					/*
						на дальнейшую реализацию
					*/
					// case 'new_query':
					// 	$status_or_button = '<div class="give_to_all">отдать свободному</div>';
					// 	break;
					default:
						####
						# $this->name_cirillic_status  -  содержится в родительском классе
						###
						$status_or_button = (isset($this->name_cirillic_status[$this->Request['status']])?$this->name_cirillic_status[$this->Request['status']]:'статус не предусмотрен!!!!'.$this->Request['status']);
						break;
				}

				// выделяем красным текстом если менеджер не взял запрос в обработку в течение 5 часов
				$overdue = (($this->Request['time_attach_manager_sec']*(-1)>18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
				// если в массиве $_POST содержится значение, значит мы запрашиваем только одну строку и подставляем значение из массива
				$rowspan = (isset($_POST['rowspan'])?$_POST['rowspan']:2);
					//////////////////////////////////////
					//	собираем строку запроса  -- start
					//////////////////////////////////////
						// раскрыть / свернуть
						$general_tbl_row_body ='<td class="show_hide" '.$this->open_close_rowspan.'="'.$rowspan.'"><span class="cabinett_row_hide'.$this->open_close_class.'"></span></td>';
						
						// номер запроса
						$general_tbl_row_body .='<td><a href="./?page=client_folder&client_id='.$this->Request['client_id'].'&query_num='.$this->Request['query_num'].'">'.$this->Request['query_num'].'</a> </td>';
						
						// имя прикреплённого менеджера
						$general_tbl_row_body .='<td>'.$this->get_manager_name_Database_Html($this->Request['manager_id'],1).'</td>';
						
						// дата заведения запроса (запрос от клиента)
						$general_tbl_row_body .='<td>'.$this->Request['create_time'].'</td>';
						
						// комменты по запросу
						$general_tbl_row_body .='<td><span data-rt_list_query_num="'.$this->Request['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($this->Request['query_num']).'"></span></td>';
						
						// компания
						$general_tbl_row_body .='<td>'.$this->get_client_name_Database($this->Request['client_id'],1).'</td>';
						
						// сумма запроса
						$general_tbl_row_body .='<td>'.RT::calcualte_query_summ($this->Request['query_num']).'</td>';
						
						// статус запроса
						$general_tbl_row_body .='<td class="'.$this->Request['status'].'_'.$this->user_access.'">'.$status_or_button.'</td>';
				
						// если запрос по строке, возвращаем строку
						if($id_row!=0){return $general_tbl_row_body;}

					//////////////////////////////////////
					//	собираем строку запроса  -- end
					//////////////////////////////////////

				$general_tbl_row .= '<tr data-id="'.$this->Request['id'].'" id="rt_list_id_'.$this->Request['id'].'">';
					$general_tbl_row .= $general_tbl_row_body;
				$general_tbl_row .= '</tr>';
				
				$general_tbl_row .= '<tr class="query_detail" '.$this->open_close_tr_style.'>';
					//$general_tbl_row .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
					$general_tbl_row .= '<td colspan="7" class="each_art">';

					// шапка таблицы вариантов запроса
					$variant_top = '<table class="cab_position_div">
						<tr>
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


					// прикручиваем найденные варианты
					$general_tbl_row .=	$variant_top.$variant_row;
					// закрываем теги
					$general_tbl_row .= '</table>';
					$general_tbl_row .= '</td>';
				$general_tbl_row .= '</tr>';
			}
			
			//////////////////////////
			//	собираем шапку главной таблицы в окне
			//////////////////////////
			$general_tbl_top = '
			<table class="cabinet_general_content_row">
							<tr>
								<th id="show_allArt"></th>
								<th>Номер</th>
								<th>отдан менеджеру</th>
								<th>запрос от клиента</th>
								<th>Коммент</th>
								<th>Компания</th>
								<th>Сумма</th>
								<th>Статус</th>
							</tr>';
			// Закрывающий тег главной таблицы
			$general_tbl_bottm = '</table>';

			// собраем воедино контент с главной таблицей
			$html = $general_tbl_top.$general_tbl_row.$general_tbl_bottm;

			// выводим
			echo $html;
		}