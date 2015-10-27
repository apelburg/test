<?php
	
	class Requests extends Cabinet{
		// фильтрация по запросу
		protected $filtres_query = '';
		// сортировка по запросу
		protected $filtres_query_sort = "  ORDER BY `id` DESC";
		// фильтрация по позиции
		protected $filtres_position = '';
		// сортировка по позиции
		protected $filtres_position_sort = "";
		// фильтрация по вариантам позиций
		protected $filtres_position_variant = '';
		// сортировка по вариантам позиций
		protected $filtres_position_variant_sort = '';




		// экземпляр класса продукции НЕ каталог (там нас интересуют кириллические названия статусов)
		protected $POSITION_NO_CATALOG;

		function __construct($id_row = 0,$user_access,$user_id){	
			include_once ('./libs/php/classes/rt_class.php');

			include_once ('./libs/php/classes/comments_class.php');

			// экземпляр класса продукции НЕ каталог
			$this->POSITION_NO_CATALOG = new Position_no_catalog();




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
				echo 'фильтр '.$method_template.'() не найден';
			}

			
    	}

    	
		// все
    	protected function requests_all_Template($id_row){
    		$this->filtres_query = " `".RT_LIST."`.`status` <> 'history'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";
    		$this->request_Template($id_row);
    	}
    	// не обработанные менеджер
    	protected function requests_no_worcked_men_Template($id_row){    		
    		$this->filtres_query = "  (`".RT_LIST."`.`status` = 'not_process' OR `".RT_LIST."`.`status` = 'new_query')";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";
    		$this->request_Template($id_row);
    	}

    	// ТЗ не корректно снаб
    	protected function requests_denied_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`row_status` IN ('tz_is_not_correct_on_recalculation','tz_is_not_correct')";
    		$this->request_Template($id_row);
    	}

    	// пауза МЕН
    	protected function requests_pause_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`row_status` LIKE '%pause%'";
    		$this->request_Template($id_row);
    	}

    	// рассчитанные СНАБ
    	protected function requests_calk_snab_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` = 'calculate_is_ready'";
    		$this->request_Template($id_row);
    	}

    	// Отказанные варианты (красный)
    	protected function requests_denided_query_Template($id_row){    		
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` = 'red'";
    		$this->request_Template($id_row);
    	}


    	// в работе
    	protected function requests_in_work_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red'";
    		$this->request_Template($id_row);
    	}

    	// отправлено в снаб
    	protected function requests_send_to_snab_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` IN ('on_calculation_snab','on_recalculation_snab')";
    		$this->request_Template($id_row);
    	}

    	// в работе снаб 
    	protected function requests_in_work_snab_Template($id_row){
    		$this->filtres_query = "  `".RT_LIST."`.`status` = 'in_work'";
    		$this->filtres_position = " `".RT_DOP_DATA."`.`row_status` <> 'red' AND `".RT_DOP_DATA."`.`status_snab` IN ('in_calculation')";
    		$this->request_Template($id_row);
    	}

    	// история
    	protected function requests_history_Template($id_row){
    		$this->filtres_query = " `".RT_LIST."`.`status` = 'history'";
    		$this->request_Template($id_row);
    	}

    	// ЗАПРОС из базы строк запросов
    	protected function get_queries_Database_Array($id_row){
    		$where = 0;
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
				// фильтрация по клиенту
				if(isset($_GET['client_id'])){
					$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`client_id` = '".$_GET['client_id']."'";
					$where = 1;
				}
				// для менеджера 
				if($this->user_access == 5){
					$query .= " ".(($where)?'AND':'WHERE')." `".RT_LIST."`.`manager_id` = '".$this->user_id."'";
					$where = 1;
				}
			}

			// фильтрация по запросу
			if($this->filtres_query != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_query;
				$where = 1;
			}
			// сортировка по запросу
			if($this->filtres_query_sort != ''){
				$query .= " ".$this->filtres_query_sort;
			}
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);

			$this->Query_arr = array();

			$n = 0;
			$this->Query_id_str = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$this->Query_arr[] = $row;
					$this->Query_id_str .= (($n>0)?',':'')."'".$row['query_num']."'";
					$n++; 
				}
			}
    	}



    	// шаблон html вывода запроса
    	protected function request_Template($id_row){
    		// вывод шапки главной таблицы
    		echo $this->get_header_general_tbl();

			// запрос массив запросов
			$this->get_queries_Database_Array($id_row);

			
			
			// получаем позиции
			$this->positions_arr = $this->get_positions_Database_Array();

			$this->query_num = '';

			foreach ($this->Query_arr as $this->Query) {
					$general_tbl_row = '';
					
					// получаем открыт/закрыт
					$this->open__close = $this->get_open_close_for_this_user($this->Query['open_close']);
						
					

										
					// наименование продукта
					$name_product = ''; 
					// порядковый номер варианта расчёта одного и того же продукта
					$this->name_count = 1;
					
					// Html строки вариантов 
					$html = '';

					// счетчик кнопок показа каталожных позиций
					// необходим для ограничения до одной кнопки
					$count_button_show_catalog_variants=0;


					// если позиций нет - переходим к следующей интерации цикла
					if(empty($this->positions_arr[$this->Query['query_num']])){continue;}

					// если это первая строка нового запроса, выводим строку названий колонок вариантов
					if($this->query_num != $this->Query['query_num']){
						$html .= $this->get_header_start_position_list_tr();
					}

					
					
					$this->position_count = count($this->positions_arr[$this->Query['query_num']]);
					
					$this->rowspan = $this->position_count +3;
					// перебор вариантов
					foreach ($this->positions_arr[$this->Query['query_num']] as $this->position) {
						////////////////////////////////////
						//	Расчёт стоимости позиций START  
						////////////////////////////////////
						$this->GET_PRICE_for_position($this->position);				
						
						//////////////////////////
						//	собираем строки вариантов по каждой позиции
						//////////////////////////
						// 
						if($name_product != $this->position['name']){$name_product = $this->position['name']; $this->name_count = 1;}
						
						$html .= $this->position_dop_data_Temp($this->Query, $this->position);

					}

					
					$this->status_or_button = (isset($this->name_cirillic_status[$this->Query['status']])?$this->name_cirillic_status[$this->Query['status']]:'статус не предусмотрен!!!!'.$this->Query['status']);
					

					// выделяем красным текстом если менеджер не взял запрос в обработку в течение 5 часов
					$this->overdue = (($this->Query['time_attach_manager_sec']*(-1)>18000)?'style="color:red"':''); // если мен не принял заказ более 5ти часов
					// если в массиве $_POST содержится значение, значит мы запрашиваем только одну строку и подставляем значение из массива
					
						//////////////////////////
						//	собираем строку запроса
						//////////////////////////
					$general_tbl_row_body = $this->get_row_query_Html_temp();
					
					// если запрос по строке, возвращаем строку
					if($id_row != 0){return $general_tbl_row_body;}

					$general_tbl_row .= '<tr data-id="'.$this->Query['id'].'" id="rt_list_id_'.$this->Query['id'].'"  class="order_head_row" '.$this->open_close_tr_style.'>
										'.$general_tbl_row_body.'
										</tr>';
					
					$general_tbl_row .= '<tr class="query_detail" '.$this->open_close_tr_style.'>';
						//$general_tbl_row .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
						// $general_tbl_row .= '<td colspan="7" class="each_art">';

						// шапка таблицы вариантов запроса
						// $variant_top = '<table class="cab_position_div">
						// 	<tr>
						// 		<th>артикул</th>
						// 		<th>номенклатура</th>
						// 		<th>тираж</th>
						// 		<th>цены:</th>
						// 		<th>товар</th>
						// 		<th>печать</th>
						// 		<th>доп. услуги</th>
						// 		<th>в общем</th>
						// 		<th></th>
						// 		<th></th>
						// 	</tr>';


						// прикручиваем найденные варианты
						$general_tbl_row .=	$html;
						// закрываем теги
						// $general_tbl_row .= '</table>';
						// $general_tbl_row .= '</td>';
					
					$general_tbl_row .= '</tr>';
					echo $general_tbl_row;
					unset($general_tbl_row);	
					$this->query_num = $this->Query['query_num'];		
			}			
			echo $this->get_footer_tbl();
		}

		// строка запроса
		protected function get_row_query_Html_temp(){
			$html  = '<td class="show_hide" '.$this->open_close_rowspan.'="'.$this->rowspan.'"><span class="cabinett_row_hide '.$this->open_close_class.'"></span></td>';
			$html .= '<td><a href="./?page=client_folder&client_id='.$this->Query['client_id'].'&query_num='.$this->Query['query_num'].'">'.$this->Query['query_num'].'</a> </td>';
			$html .= '<td><span data-sec="'.$this->Query['time_attach_manager_sec']*(-1).'" '.$this->overdue.'>'.$this->Query['time_attach_manager'].'</span>'.$this->get_manager_name_Database_Html($this->Query['manager_id']).'</td>';
			$html .= '<td>'.$this->get_client_name_Database($this->Query['client_id']).'</td>';
			$html .= '<td>'.$this->Query['create_time'].'</td>';
			$html .= '<td></td>';
			$html .= '<td><span data-rt_list_query_num="'.$this->Query['query_num'].'" class="icon_comment_show white '.Comments_for_query_class::check_the_empty_query_coment_Database($this->Query['query_num']).'"></span></td>';
			
			$html .='<td>'.RT::calcualte_query_summ($this->Query['query_num']).'</td>';
			$html .='<td class="'.$this->Query['status'].'_'.$this->user_access.'">'.$this->status_or_button.'</td>';
			return $html;
		}

		// возвращает строку с названиями ячеек по позициям
		protected function get_header_start_position_list_tr(){
			$html = '<tr class="query_detail cab_position_div" '.$this->open_close_tr_style.'>';
				$html .= '<th>артикул</th>';
				$html .= '<th>номенклатура</th>';
				$html .= '<th>тираж</th>';
				$html .= '<th>товар</th>';
				$html .= '<th>печать</th>';
				$html .= '<th>доп. услуги</th>';
				$html .= '<th>в общем</th>';
				$html .= '<th></th>';
			$html .= '</tr>';
			return $html; 
		}

		// шаблон строки варианта позиции
		protected function position_dop_data_Temp($Query, $position){
			$html = '<tr data-id_dop_data="'.$position['id_dop_data'].'" class="'.$position['type'].'_1 query_detail" '.$this->open_close_tr_style.'">';
				$html .= '<td>'.$position['art'].'</td>';
				$html .= '<td><a class="go_to_position_card_link" target="_blank" href="./?page=client_folder&client_id='.$this->Query['client_id'].'&section=rt_position&id='.$position['id'].'">'.$position['name'].'</a> <span class="variant_comments_dop">( Вариант '.$this->name_count++.' )</span></td>';
				$html .= '<td>'.$position['quantity'].'</td>';
				$html .= '<td>'.$this->Price_for_the_goods.'</td>';
				$html .= '<td>'.$this->Price_of_printing.'</td>';
				$html .= '<td>'.$this->Price_of_no_printing.'</td>';
				$html .= '<td>'.$this->Price_for_the_position.'</td>';
				$html .= '<td data-type="'.$position['type'].'" data-status="'.$position['status_snab'].'" class="'.$position['status_snab'].'_'.$this->user_access.' '.$Query['status'].'_status_snab_'.$this->user_access.'">'.$this->show_cirilic_name_status_snab($position['status_snab']).'</td>';
			$html .= '</tr>';
			return $html;
		}

		// возвращает шапку главной таблицы
		protected function get_header_general_tbl(){
			$html = '<table class="cabinet_general_content_row">';
				$html .= '<tr>';
					$html .= '<th id="show_allArt"></th>';
					$html .= '<th>Номер</th>';
					$html .= '<th>Куратор компании</th>';					
					$html .= '<th>Компания</th>';
					$html .= '<th>Дата запроса</th>';
					$html .= '<th></th>';
					$html .= '<th>Коммент</th>';
					$html .= '<th>Сумма</th>';
					$html .= '<th>Статус</th>';
				$html .= '</tr>';

			return $html;
		}

		// возвращает закрывающий тег главной таблицы
		protected function get_footer_tbl(){
			return '</table>';
		}




		
		// ЗАПРОС позиции по запросу
		protected function get_positions_Database_Array(){
			$where = 0;

			global $mysqli;
			$query = "
				SELECT 
					`".RT_DOP_DATA."`.`id` AS `id_dop_data`,
					`".RT_DOP_DATA."`.`quantity`,	
					`".RT_DOP_DATA."`.`price_out`,		
					`".RT_DOP_DATA."`.`print_z`,	
					`".RT_DOP_DATA."`.`zapas`,	
					`".RT_DOP_DATA."`.`status_snab`,	
					`".RT_MAIN_ROWS."`.*,
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`
					
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					
					WHERE `".RT_MAIN_ROWS."`.`query_num` IN (".$this->Query_id_str.")";

			$where = 1;

			// фильтрация по запросу
			if($this->filtres_position != ''){
				$query .= " ".(($where)?'AND':'WHERE')." ".$this->filtres_position;
				$where = 1;
			}
			// сортировка по запросу
			if($this->filtres_position_sort != ''){
				$query .= " ".$this->filtres_position_sort;
			}else{
				$query .= " ORDER BY `".RT_MAIN_ROWS."`.`type` DESC";
			}


			// echo $query.'<br>';
			$main_rows = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			$main_rows_id = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$main_rows[$row['query_num']][] = $row;
				}
			}
			// if($main_rows){ echo $query;}
			return $main_rows;
		}


   
	
}



