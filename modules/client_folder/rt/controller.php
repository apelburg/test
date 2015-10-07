<?php

	// ПРИМЕЧАНИЕ - после заверщения скрипта убрать из common.js функции с окончанием Old
	
	// Данные расчетной таблицы хронятся в 3-х таблицах базы данных
	// 1-я таблица является родительской для 2-ой , 2-ая родительской для 3-ей
	// os__rt_main_rows  (RT_MAIN_ROWS)  1-я таблица основные ряды таблицы, содержащие описательные данные о товарной позиции (наименование, тип )
	// os__rt_dop_data   (RT_DOP_DATA)  2-я таблица содрежит данные о количестве и цене для каждого варианта расчета товарной позиции (к каждой строке из 1-ой таблицы может соответсвовать любое количество стро из 2-ой таблицы)
	// os__rt_dop_uslugi (RT_DOP_USLUGI) 3-я таблица содержит данные о расчетах о дополнительных услугах к ним относятся - нанесение, доставка, упаковка. Причем данные в таблице разделены на группы нанесение входит в группу print, все остальное в группу extra (к каждой строке из 2-ой таблицы может соответсвовать любое количество строк из 3-ей таблицы)
	// os__rt_print_data (RT_PRINT_DATA) 3-я таблица содержит данные о расчетах нанесения (к каждой строке из 2-ой таблицы может соответсвовать любое количество стро из 3-ей таблицы)
	
	
	// cat catalogs - товары из каталогов 
	// pol polygraphy - полиграфия 
	// ext externals (внешние) - товары не из каталогов 
	
	// поля таблицы 
	// row_status - ствтус ряда (светофор)
	// glob_status - статус ряда в глобальном масштабе (в работе, на расчете)
	//
	//
	//
	//
    
	function fetch_rows_from_rt($query_num){
	     global $mysqli;
		 
		 $rows = array();
		 
		 $query = "SELECT main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art_id AS art_id ,main_tbl.art AS art ,main_tbl.name AS item_name ,main_tbl.master_btn AS master_btn , main_tbl.svetofor_display AS svetofor_display ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_in AS dop_t_price_in , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.row_status AS row_status, dop_data_tbl.glob_status AS glob_status, dop_data_tbl.expel AS expel, dop_data_tbl.shipping_date AS shipping_date,dop_data_tbl.shipping_type AS shipping_type, dop_data_tbl.shipping_time AS shipping_time, dop_data_tbl.status_snab AS status_snab,
						  
						  dop_uslugi_tbl.id AS uslgi_t_id , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_in AS uslugi_t_price_in , dop_uslugi_tbl.price_out AS uslugi_t_price_out, dop_uslugi_tbl.for_how AS uslugi_t_for_how
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE main_tbl.query_num ='".$query_num."' ORDER BY main_tbl.sort,dop_data_tbl.id";
		
			// echo $query;
		 $result = $mysqli->query($query) or die($mysqli->error);
		 $multi_dim_arr = array();
	     while($row = $result->fetch_assoc()){
		     if(!isset($multi_dim_arr[$row['main_id']])){
			     $multi_dim_arr[$row['main_id']]['row_type'] = $row['main_row_type'];
				 $multi_dim_arr[$row['main_id']]['master_btn'] = $row['master_btn'];
				 $multi_dim_arr[$row['main_id']]['art_id'] = $row['art_id'];
				 $multi_dim_arr[$row['main_id']]['art'] = $row['art'];
				 $multi_dim_arr[$row['main_id']]['name'] = $row['item_name'];
				 $multi_dim_arr[$row['main_id']]['svetofor_display'] = $row['svetofor_display'];
				 
				 if($row['main_row_type']=='cat'){
				     $data = RT::getArtRelatedPrintInfo($row['art_id']);
				     $multi_dim_arr[$row['main_id']]['dop_details'] = $data;
				 }
			 }
			 //$multi_dim_arr[$row['main_id']]['uslgi_t_id'][] = $row['uslgi_t_id'];
			 if(isset($multi_dim_arr[$row['main_id']]) && !isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) &&!empty($row['dop_data_id'])){
			     $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']] = array(
																	'expel' => $row['expel'],
																	'shipping_date' => $row['shipping_date'],
																	'shipping_type' => $row['shipping_type'],
																	'shipping_time' => $row['shipping_time'],
																	'row_status' => $row['row_status'],
																	'glob_status' => $row['glob_status'],
																	'status_snab' => $row['status_snab'],
																	'quantity' => $row['dop_t_quantity'],
																	'discount' => $row['dop_t_discount'],
																	'price_in' => $row['dop_t_price_in'],
																	'price_out' => $row['dop_t_price_out']);
		    }
			if(isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) && !empty($row['uslgi_t_id'])){
			    $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['dop_uslugi'][$row['uslugi_t_glob_type']][$row['uslgi_t_id']] = array(
																									'type' => $row['uslugi_t_type'],
																									'id' => $row['uslgi_t_id'],
																									'quantity' => $row['uslugi_t_quantity'],
																									'price_in' => $row['uslugi_t_price_in'],
																									'price_out' => $row['uslugi_t_price_out'],
																									'for_how' => $row['uslugi_t_for_how']	
																									);
			}
			
			//print_r( $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['print_data']); echo "<br>";
			
			$rows[]= '<tr><td>'.implode('</td><td>',$row).'</td></tr>';
		   
		 }
	     return array($multi_dim_arr,$rows);
	 }
	
	 $rows = fetch_rows_from_rt($query_num);
	 
	 

	 $test_data = FALSE; // TRUE
	 // Построение таблицы РТ
	 // основой для рядов таблицы (тегов <tr>) является второй уровень массива $rows[0], тоесть это те данные которые лежат в $row['dop_data']
	 // каждый элемент этого массива выводится как отдельный ряд (в ходе обработки вывода могут быть добавлены дополнительные вспомагательные 
	 // элементы или изменен их порядок) 
	 // Тоесть таблица, количество (тегов <tr>), формируется на основе уровня $row['dop_data'], остальные уровни только влияют на результирующий
	 // вид таблицы
	 // элементы первого уровня выводятся один раз на весь блок элементов $row['dop_data']
	 // элементы третьего уровня $dop_row['dop_uslugi'] выводятся в виде ссылки внутри существующего ряда, если $dop_row['dop_uslugi'] существует
	 // draft - РЕАЛИЗАЦИЯ ФУНКЦИОНАЛА "ДРАФТ" оказалась не востребованной поле draft можно удалить из таблицы в базе данных
	 // если что реализация сохранена, закомментирована внизу скрипта 
	  
	 // echo '<pre>'; print_r($rows[0]); echo '</pre>';
	 
	 $service_row[0] = array('quantity'=>'','price_in'=>'','price_out'=>'','row_status'=>'','glob_status'=>'');
	 $glob_counter = 0;
	 $mst_btn_summ = 0;
	 $svetofor_display_relay_status_all = 'on';
	 foreach($rows[0] as $key => $row){
	     $glob_counter++;
         // Проходим по первому уровню и определям некоторые моменты отображения таблицы, которые будут применены при проходе по второму
		 // уровню массива, ряды таблицы будут создаваться там
		 
		 
		 // считаем сколько мастер кнопок нажаты
		 $mst_btn_summ += $row['master_btn'];
		 
		 
		 // если товарная позиция имеет больше одного варианта расчета вставляем пустой ряд вверх
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
		 if(isset($row['dop_data']) && count($row['dop_data'])>1){
			  $row['dop_data']= $service_row + $row['dop_data']; 
		 }
		 // 
		 if(!isset($row['dop_data'])){
		      $row['name'] .= '<span style="color:red"> ОШИБКА - ДАННЫЕ ПО РАСЧЕТУ НЕ ПОЛУЧЕНЫ (УДАЛИТЕ ПОЗИЦИЮ)</span>';
			  $row['dop_data']= $service_row; 
		 }
		 
		 //array_unshift($row['dop_data'],array('quantity'=>0,'price_in'=>0,'price_out'=>0,'row_status'=>0,'glob_status'=>0));
		 
		 // здесь мы определяем значение для атрибута rowspan тегов td которые будут выводится единой ячейкой для всей расчетов товарной позиции
		 $svetofor_display_relay_status = 'on';
		 $row_span = count($row['dop_data']);
		 if($row['svetofor_display']==1){
		      foreach($row['dop_data'] as $dop_row){
			      if($dop_row['row_status']=='red'){
				      $row_span--;
					  $svetofor_display_relay_status_all = 'off';
					  $svetofor_display_relay_status = 'off';
				  }
			  }
		 }
		 $counter=0;
		
		  
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>---';
		 // Проходим в цикле по второму уровню массива($row['dop_data']) на основе которого строится основной шаблон таблицы
	     foreach($row['dop_data'] as $dop_key => $dop_row){
		    // если $dop_key==0 это значит что это вспомогательный ряд (отображается пустым без функционала)
			// если ряд $dop_key!=0 не вспомогательный работаем с ним в обычном порядке
		    if($dop_key!=0){
				 // определяем какие расчеты будут учитываться в конечных суммах а какие нет и их отображение в таблице
				 // json_decode($row['details']);
				 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
				 if(@$dop_row['expel']!=''){
					$obj = @json_decode($dop_row['expel']);
					foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
				 }
				 //echo '<br>'; print_r($expel);
				
				 // работаем с информацией о дополнительных услугах определяя что будет выводиться и где
				 // 1. определяем данные описывающие варианты нанесения логотипа, они хранятся в $dop_row['dop_uslugi']['print']
				 if(isset($dop_row['dop_uslugi']['print'])){ // если $dop_row['dop_uslugi']['print'] есть выводим данные о нанесениях 
					 $summ_in = $summ_out = array();
					 foreach($dop_row['dop_uslugi']['print'] as $extra_data){
					     // если количество в расчете нанесения не равно количеству в колонке тираж товара 
						 // необходимо присвоить нанесениям такое же количество и пересчитать их
						//$extra_data['quantity'] = 250;
					    if($extra_data['quantity']!=$dop_row['quantity']){
						     $reload['flag'] = true;
						     //echo $dop_row['quantity'];
						     include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		                     $json_out =  rtCalculators::change_quantity_and_calculators($dop_row['quantity'],$dop_key,'true','false');
							 $json_out_obj =  json_decode($json_out);
							 
							 // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
							 // сбрасываем количество тиража и нанесения до 1шт.
							 if(isset($json_out_obj->print->outOfLimit) || isset($json_out_obj->print->needIndividCalculation)){
							     rtCalculators::change_quantity_and_calculators(1,$dop_key,'true','false');
								 
								 $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$dop_key."'";
			                     $result = $mysqli->query($query)or die($mysqli->error);
							 }
							 
	
						 } /**/
						 $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
						 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
					 }
					 $print_btn = '<span>'.count($dop_row['dop_uslugi']['print']).'</span>'; 
					 $print_exists_flag = 'yes';
					 $print_in_summ = array_sum($summ_in);
					 $print_out_summ = array_sum($summ_out);
				 }
				 else{// если данных по печати нет то проверяем выводим кнопку добавление нанесения
					 $print_btn = '<span>+</span>';
					 $print_exists_flag = 'no';
					 $print_in_summ = 0;
					 $print_out_summ = 0;
				 }
				 // 2. определяем данные описывающие варианты дополнительных услуг, они хранятся в $dop_row['dop_uslugi']['extra']
				 if(isset($dop_row['dop_uslugi']['extra'])){// если $dop_row['dop_uslugi']['extra'] есть выводим данные о дополнительных услугах 
					 $summ_in = $summ_out = array();
					 foreach($dop_row['dop_uslugi']['extra'] as $extra_data){
					     // если количество в расчете доп услуг не равно количеству в колонке тираж товара 
						 // необходимо присвоить доп услугам такое же количество
						
					     if($extra_data['quantity']!=$dop_row['quantity']){
						     
						     $query="UPDATE `".RT_DOP_USLUGI."` SET  `quantity` = '".$dop_row['quantity']."'  WHERE `id` = '".$extra_data['id']."'";
						     $mysqli->query($query)or die($mysqli->error);
							 $extra_data['quantity']=$dop_row['quantity'];
					     }
						 $summ_in[] = ($extra_data['for_how']=='for_all')? $extra_data['price_in']:$extra_data['quantity']*$extra_data['price_in'];
						 $summ_out[] = ($extra_data['for_how']=='for_all')? $extra_data['price_out']:$extra_data['quantity']*$extra_data['price_out'];
					 }
					 $dop_uslugi_btn =  '<span>'.count($dop_row['dop_uslugi']['extra']).'</span>';
					 $extra_exists_flag = 'extra_exists_flag="1"';
					 $dop_uslugi_in_summ = array_sum($summ_in);
					 $dop_uslugi_out_summ = array_sum($summ_out);
					 if($test_data) $extra_open_data =  print_r($dop_row['dop_uslugi']['extra'],TRUE);
				 }
				 else{// если данных по дополнительным услугам  нет выводим кнопку добавление дополнительных услуг
				     $extra_exists_flag = '';
					 $dop_uslugi_in_summ = 0;
					 $dop_uslugi_out_summ = 0;
					 $dop_uslugi_btn = '<span>+</span>';
					 if($test_data) $extra_open_data =($counter==0)? 0:'0';
				 }
				 
				 // подсчет сумм в ряду
				 $price_out = ($dop_row['discount'] != 0 )? (($dop_row['price_out']/100)*(100 + $dop_row['discount'])) : $dop_row['price_out'] ;
				 // 1. подсчитываем входящую сумму
				 $price_in_summ = $dop_row['quantity']*$dop_row['price_in'];
				 $in_summ = $price_in_summ;
				 if(!(!!$expel["print"]))$in_summ += $print_in_summ;
				 if(!(!!$expel["dop"]))$in_summ += $dop_uslugi_in_summ;
				 // 2. подсчитываем исходящую сумму 
				 $price_out_summ =  $dop_row['quantity']*$price_out;
				 $out_summ =  $price_out_summ;
				 if(!(!!$expel["print"]))$out_summ += $print_out_summ;
				 if(!(!!$expel["dop"]))$out_summ += $dop_uslugi_out_summ;
				 
				 $delta = $out_summ-$in_summ; 
				 $margin = ($in_summ>0 && $out_summ>0)?(($out_summ-$in_summ)/$out_summ)*100:0;
				 
				 $price_in_summ_format = number_format($price_in_summ,'2','.','');
				 $price_out_summ_format = number_format($price_out_summ,'2','.','');
				 $print_in_summ_format = number_format($print_in_summ,'2','.','');
				 $print_out_summ_format = number_format($print_out_summ,'2','.','');
				 $dop_uslugi_in_summ_format = number_format($dop_uslugi_in_summ,'2','.','');
				 $dop_uslugi_out_summ_format = number_format($dop_uslugi_out_summ,'2','.','');
				 $in_summ_format = number_format($in_summ,'2','.','');
				 $out_summ_format = number_format($out_summ,'2','.','');
				 $delta_format = number_format($delta,'2','.','');
				 $margin_format = number_format($margin,'2','.','');
		         $margin_currency = '%';
				 
				 $svetofor_stat = ($dop_row['row_status']=='')?'green':$dop_row['row_status'];
				 // если ряд не исключен из расчетов добавляем значения в итоговый ряд
				 if(!(!!$expel["main"]) && ($svetofor_stat=='sgreen' || $svetofor_stat=='green')){// && ( || $dop_row['row_status']=='')
					 @$total['price_in_summ'] += $price_in_summ;
					 @$total['price_out_summ'] += $price_out_summ;
					 if(!(!!$expel["print"])) @$total['print_in_summ'] += $print_in_summ;
					 if(!(!!$expel["print"])) @$total['print_out_summ'] += $print_out_summ;
					 if(!(!!$expel["dop"])) @$total['dop_uslugi_in_summ'] += $dop_uslugi_in_summ;
					 if(!(!!$expel["dop"])) @$total['dop_uslugi_out_summ'] += $dop_uslugi_out_summ;
					 @$total['in_summ'] += $in_summ;
					 @$total['out_summ'] += $out_summ;
				 }
				 $img_design_path = HOST.'/skins/images/img_design/';
				 
				 $svetofor_src = $img_design_path.'rt_svetofor_'.$svetofor_stat.'.png';
				 $svetofor = '<img src="'.$svetofor_src.'" />';
				 $svetofor_td_attrs = 'svetofor="'.$svetofor_stat.'" class="svetofor pointer center"';
				 $svetofor_tr_display = ($row['svetofor_display']==1 && $dop_row['row_status']=='red')?'hidden':'';
				 $currency = 'р';
				 //$quantity_dim = 'шт';<td width="20" class=" left quantity_dim">'.$quantity_dim.'</td>
				 $discount = $dop_row['discount'].'%';
				 //$srock_sdachi = implode('.',array_reverse(explode('-',$dop_row['shipping_date'])));
				  $srock_sdachi = ($dop_row['shipping_type']=='date')? implode('.',array_reverse(explode('-',$dop_row['shipping_date']))):'';
				 if($srock_sdachi=='00.00.0000') $srock_sdachi='';
				 
				 $expel_class_main = ($expel['main']=='1')?' red_cell':'';
				 $expel_class_print = ($expel['print']=='1')?' red_cell':'';
				 $expel_class_dop = ($expel['dop']=='1')?' red_cell':'';
				 
				  // дополнительная скрытая инфа 
		         
		     }
			 else{
			     $expel = array ("main"=>0,"print"=>0,"dop"=>0);
				 $svetofor = '<img src="'.HOST.'/skins/images/img_design/rt_svetofor_top_btn_'.$svetofor_display_relay_status.'.png" onclick="rtCalculator.svetofor_display_relay(this,true);" class="svetofor_btn">';
			     $svetofor_td_attrs = 'svetofor_btn';
				 $currency = $print_btn = $dop_uslugi_btn = '';
				 $price_out = $price_in_summ_format = $price_out_summ_format = $print_in_summ_format = $print_out_summ_format = '';
				 $dop_uslugi_in_summ_format = $dop_uslugi_out_summ_format = $in_summ_format = $out_summ_format = '';
				 $delta_format = $margin_format = $expel_class_main = $expel_class_print = $expel_class_dop = $quantity_dim = $discount = $srock_sdachi = $print_exists_flag = $extra_exists_flag = $margin_currency = '';
				 
				  
			 }
			 //$art_id = get_base_art_id($row['art']);
			  
			 $dop_details = '';
			  //echo $row['row_type'].' = ';
			 if($row['row_type'] == 'cat'){ 
				 $extra_panel = '<div class="pos_plank cat">
								   <a href="?page=client_folder&section=rt_position&id='.$key.'&client_id='.$client_id.'">'.$row['art'].'</a>
								   <div class="pos_link_plank">
									  <div class="catalog">
										  <a id="" href="/?page=description&id='.$row['art_id'].'" target="_blank" onmouseover="change_href(this);return false;"><img src="./skins/images/img_design/basic_site_link.png" border="0" /></a>
									  </div>
									  <div class="supplier">
										   '.identify_supplier_by_prefix($row['art']).'
									  </div>
								   </div>
								 </div>
								 <div>'.$row['name'].'</div>';
				 // дополнительная скрытая инфа 
		        if($counter==0 &&  count($row['dop_details'])>0)  $dop_details['allowed_prints'] = $row['dop_details'];
			 }
			 else if($row['row_type'] == 'ext'){
				 $extra_panel = '<div class="pos_plank ext">
								   <a href="?page=client_folder&client_id='.$_GET['client_id'].'&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 </div>';
			 }
			 else if($row['row_type'] == 'pol'){
				 $extra_panel = '<div class="pos_plank pol">
								   <a href="?page=client_folder&client_id='.$_GET['client_id'].'&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 </div>';
			 }else{
			 	$extra_panel = '<div class="pos_plank pol">
								   <a href="?page=client_folder&client_id='.$_GET['client_id'].'&section=rt_position&id='.$key.'">'.$row['name'].'</a>
								 </div>';
			 }

			 $block = (isset($dop_row['status_snab']) && ($dop_row['status_snab']=='on_calculation_snab' || $dop_row['status_snab']=='on_recalculation_snab' || $dop_row['status_snab']=='in_calculation'))?1:0;
			 
		     $cur_row  =  '';
		     $cur_row .=  '<tr '.(($counter==0)?'pos_id="'.$key.'" type="'.$row['row_type'].'"':'').' row_id="'.$dop_key.'" art_id="'.$row['art_id'].'" class="'.(($key>1 && $counter==0)?'pos_edge ':'').(((count($row['dop_data'])-1)==$counter)?'lowest_row_in_pos ':'').(($counter!=0)?$svetofor_tr_display:'').(($row_span==0)?'hidden':'').(($block==1)?' block_snab':'').'"  block="'.$block.'">';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="glob_counter" class="top glob_counter" width="30" oncontextmenu="openCloseMenu(event,\'contextmenuNew\',{\'pos_id\':\''.$key.'\',\'control_num\':\''.'4'.'\'});">'.$glob_counter.'</td>':'';
			 
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="master_btn" class="top master_btn noselect" width="35">   
											<div class="masterBtnContainer" id="">
											   <input type="checkbox" id="masterBtn'.$key.'" rowIdNum="'.$key.'" name="masterBtn"   onclick="return onClickMasterBtn(this,\'rt_tbl_body\','.$key.');" '.(($row['master_btn'] == 1)? 'checked':'').'/><label for="masterBtn'.$key.'"></label>
											</div>
			                              </td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$dop_key.'</td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$row['row_type'].'</td>':'';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" type="name" class="art_name top">'.$extra_panel.'</td>':'';
										  
										  //extra_panel $dop_row['status_snab'].$block.
			 $cur_row .=  '<td class="hidden"></td>
			               <td type="dop_details" class="hidden">'.json_encode($dop_details).'</td>
			               <td width="40" type="svetofor" '.$svetofor_td_attrs.'>'.$svetofor.'</td>
			               <td width="60" type="quantity" class="quantity right r_border"  editable="true">'.$dop_row['quantity'].'</td>
						   <td width="90" type="price_in" editable="true" connected_vals="art_price" c_stat="1" class="in right">'.$dop_row['price_in'].'</td>
						   <td width="15" connected_vals="art_price" c_stat="1" class="currency left">'.$currency.'</td>
						   <td width="90" type="price_in_summ" connected_vals="art_price" c_stat="0" class="in right hidden">'.$price_in_summ_format.'</td>
						  
						   <td width="15" connected_vals="art_price" c_stat="0" class="currency left hidden">'.$currency.'</td>
						   <td width="45" class="center" type="discount" discount_fieid="1">'.$discount.'</td>
						   <td width="90" type="price_out" editable="true" connected_vals="art_price" c_stat="1" class="out right">'.$price_out.'</td>
						   <td width="15" class="currency left r_border" connected_vals="art_price" c_stat="1" >'.$currency.'</td>
						   <td width="90" type="price_out_summ"  connected_vals="art_price" c_stat="0" class="out right hidden">'.$price_out_summ_format.'</td>
						   <td width="15" connected_vals="art_price" c_stat="0" class="currency left r_border hidden">'.$currency.'</td>
						   <td width="25" class="calc_btn" calc_btn="print">'.$print_btn.'</td>
                           <td type="print_exists_flag" class="hidden">'.$print_exists_flag.'</td>
			               <td width="80" type="print_in_summ"  connected_vals="print" c_stat="0" class="test_data in hidden '.$expel_class_print.'">'.$print_in_summ_format.$currency.'</td> 
			               <td width="80" type="print_out_summ"  connected_vals="print" c_stat="1" class="out '.$expel_class_print.'" expel="'.$expel['print'].'">'.$print_out_summ_format.$currency.'</td>
			               <td width="25" class="calc_btn" calc_btn="extra" '.$extra_exists_flag.' data-id="'.$key.' ">'.$dop_uslugi_btn.'</td>';
			     if($test_data)	 $cur_row .=  '<td class="test_data">'.$extra_open_data.'</td>';
			 $cur_row .=  '<td width="80" type="dop_uslugi_in_summ" connected_vals="uslugi" c_stat="0" class="test_data r_border in hidden '.$expel_class_dop.'">'.$dop_uslugi_in_summ_format.$currency.'</td>';
			 $cur_row .=  '<td width="80" type="dop_uslugi_out_summ" connected_vals="uslugi" c_stat="1"  class="out r_border '.$expel_class_dop.'" expel="'.$expel['dop'].'">'.$dop_uslugi_out_summ_format.$currency.'</td>
						   <td type="in_summ" connected_vals="total_summ" c_stat="0" class="total in right hidden '.$expel_class_main.'">'.$in_summ_format.'</td>
						   <td width="15" connected_vals="total_summ" c_stat="0" class="currency hidden r_border '.$expel_class_main.'">'.$currency.'</td>
						   <td type="out_summ" connected_vals="total_summ" c_stat="1" class="total out right '.$expel_class_main.'" expel="'.$expel['main'].'">'.$out_summ_format.'</td>
						   <td width="15" connected_vals="total_summ" c_stat="1" class="currency r_border left '.$expel_class_main.'">'.$currency.'</td>
						   <td width="70" class="grey r_border center">'.$srock_sdachi.'</td>
						   <td type="delta" class="delta right">'.$delta_format.'</td>
						   <td width="10" class="left">'.$currency.'</td>
						   <td type="margin" class="margin right">'.$margin_format.'</td>
						   <td width="10" class="left">'.$margin_currency.'</td>
						   <td stretch_column>&nbsp;</td>';

						   global $Position_no_catalog;
			 $cur_row .=  '<td class="overflow"><div style="">'.((isset($dop_row['status_snab']))?$Position_no_catalog->get_name_group($dop_row['status_snab']):'').'<div></td>';  

			 $cur_row .= '</tr>';
			 
			 // загружаем сформированный ряд в итоговый массив
		     $tbl_rows[]= $cur_row;
		     $counter++;
		 }
	 }
	 if(isset($reload['flag']) && $reload['flag'] == true){
	     header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
	     exit;
	 }/**/
	 
	 $rt = '<table class="rt_tbl_head" id="rt_tbl_head" scrolled="head" style="width: 100%;" border="0">
	          <tr class="w_border cap">
			      <td width="30"></td>
			       <td width="35" class="top">
				      <div class="master_button_container">
						  <div class="master_button noselect">
							<a href="#" onclick="openCloseMenu(event,\'rtMenu\'); return false;">&nbsp;</a>
							<div id="reset_master_button" class="reset_button'.((count($rows[0])==$mst_btn_summ)?' on':'').'" onclick="resetMasterBtn(this,\'rt_tbl_body\');">&nbsp;</div>
						  </div>
					  </div>
				  </td>
	              <td class="hidden"></td>
				  <td class="hidden">тип</td>
				  <td class="art_name right">
				      <a href="#" onclick="console.log(rtCalculator.tbl_model);/*print_r(rtCalculator.tbl_model);*/">_</a>
				  </td>
				  <td class="hidden">dop_details</td>
				  <td class="hidden">draft</td>
				  <td width="40" class="center"><img src="'.HOST.'/skins/images/img_design/rt_svetofor_top_btn_'.$svetofor_display_relay_status_all.'.png" onclick="rtCalculator.svetofor_display_relay(this);"></td>
				  <td width="60" type="quantity" class="quantity right r_border">тираж</td>
				  <td width="90" connected_vals="art_price" c_stat="1" class="grey w_border  right pointer">$ товара<br><span class="small">входящая штука</span></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="grey w_border"></td>
				  <td width="90" connected_vals="art_price" c_stat="0" class="grey w_border right hidden pointer">$ товара<br><span class="small">входящая тираж</span></td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="grey w_border hidden"></td>
				  <td width="45" class="grey w_border">наценка</td>
				  <td width="90" connected_vals="art_price" c_stat="1" class="grey w_border right pointer">$ товара<br><span class="small">исходящая штука</span></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="grey w_border r_border"></td>
				  <td width="90" connected_vals="art_price" c_stat="0" class="grey w_border right pointer hidden">$ товара<br><span class="small">исходящая тираж</span></td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="grey w_border r_border hidden"></td>
				  <td width="25"></td>
	              <td class="hidden">нанес подробн</td>
	              <td width="80" connected_vals="print" c_stat="0" class="pointer hidden">$ печать<br><span class="small">входящая тираж</span></td> 	  
			      <td width="80" connected_vals="print" c_stat="1" class="pointer">$ печать<br><span class="small">исходящая тираж</span></td>
			      <td width="25"></td>';
    if($test_data)	 $rt.= '<td class="test_data_cap">доп.усл подробн</td>';
           $rt.= '<td width="80"  connected_vals="uslugi" c_stat="0" class="pointer r_border hidden">$ доп. услуги<br><span class="small">входящая тираж</span></td> 
			      <td width="80"  connected_vals="uslugi" c_stat="1" class="out pointer r_border">$ доп. услуги<br><span class="small">исходящая тираж</span></td>
				  <td connected_vals="total_summ" c_stat="0" class="total pointer hidden center">итого<br><span class="small">входящая</span></td>
				  <td width="15" connected_vals="total_summ" c_stat="0" class="hidden r_border"></td>
				  <td connected_vals="total_summ" c_stat="1" class="total pointer center">итого<br><span class="small">исходящая</span></td>
				  <td width="15" connected_vals="total_summ" c_stat="1" class="r_border"></td>
				  <td width="70" class="center grey r_border">срок сдачи</td>
				  <td class="delta center">delta</td>
				  <td width="10"></td>
				  <td class="margin center">маржина-<br>льность</td>
				  <td width="10"></td>
				  <td stretch_column>&nbsp;</td>
                  <td width="70">статус</td>';              
	    $rt.= '</tr>
	           <tr row_id="total_row" class="grey bottom_border">
			      <td width="30" height="18"></td>
			      <td width="35"></td>
	              <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td class="right"></td>
				  <td class="hidden">dop_details</td>
				  <td></td>
				  <td class="quantity r_border"></td>
				  <td connected_vals="art_price" c_stat="1"></td>
				  <td width="15" connected_vals="art_price" c_stat="1"></td>
				  <td type="price_in_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format(@$total['price_in_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="hidden">р</td>
				  <td width="45" class=""></td>
				  <td connected_vals="art_price" c_stat="1"></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="r_border"></td>
				  <td type="price_out_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format(@$total['price_out_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="r_border hidden">р</td>
				  <td></td>
	              <td class="hidden">нанес подробн</td>
	              <td type="print_in_summ" connected_vals="print" c_stat="0" class="hidden">'.number_format(@$total['print_in_summ'],'2','.','').'р</td> 		  
			      <td type="print_out_summ" connected_vals="print" c_stat="1">'.number_format(@$total['print_out_summ'],'2','.','').'р</td>
			      <td></td>';
    if($test_data)	$rt.= '<td class="test_data_cap"></td>';
           $rt.= '<td width="80" type="dop_uslugi_in_summ" connected_vals="uslugi" c_stat="0"  class="r_border hidden">'.number_format(@$total['dop_uslugi_in_summ'],'2','.','').'р</td> 
			      <td width="80" type="dop_uslugi_out_summ" connected_vals="uslugi" c_stat="1" class="out r_border">'.number_format(@$total['dop_uslugi_out_summ'],'2','.','').'р</td>
			      <td type="in_summ" connected_vals="total_summ" c_stat="0" class="right hidden">'.number_format(@$total['in_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="total_summ" c_stat="0" class="left hidden r_border">р</td>
				  <td  type="out_summ" connected_vals="total_summ" c_stat="1" class="right">'.number_format(@$total['out_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="total_summ" c_stat="1" class="left r_border">р</td>
				  <td width="70" class="grey r_border"></td>
				  <td type="delta" class="right">'.number_format((@$total['out_summ']-@$total['in_summ']),'2','.','').'</td>
				  <td width="10" class="left">р</td>
				  <td type="margin" class="right">'.number_format((@$total['out_summ']-@$total['in_summ']),'2','.','').'</td>
				  <td width="10" class="left">р</td>
				  <td stretch_column>&nbsp;</td>
                  <td></td>';              
	   $rt.= '</tr>
	          </table>
			  <div id="scrolled_part_container" class="scrolled_tbl_movable_part">
	          <table class="rt_tbl_body" id="rt_tbl_body" scrolled="body" client_id="'.$client_id.'" query_num="'.$query_num.'" border="0">'.implode('',$tbl_rows).'</table>
			  </div>';
			  
			  
			  
			  
			  
			  
			  
	/*	
	     РЕАЛИЗАЦИЯ ФУНКЦИОНАЛА "ДРАФТ" оказалась не востребованной
		 
		 ПРИМ. поле draft можно удалить из таблицы в базе данных
	
		 // РЯДЫ ДЛЯ РАСЧЕТА ВАРИАНТОВ!!! $dop_row['draft'] - для реализации функционала при котором позиция может иметь несколько расчетов, 
		 // при этом может быть что ни один из низ не выбран как окончательный, или один выбран а остальные остаются вариантами, введено понятие
		 // draft - (черновик,проект,эскиз) для маркирования таких рядов в базе данных - объявляется следующее правило - если позиция имеет один  
		 // расчетный ряд то он не может быть draft (он окончательный и участвует в дальнейших расчетах), если несколько они могут все быть draft,
		 // (и в дальшейших расчетах не участвуют) если один из низ не draft значит он окончательный (он окончательный и участвует в дальнейших
		 // расчетах), если позиция имеет несколько расчетных рядов, то не draft из них может быть только один
		 // ПРИМ. если этот вариант останется рабочим удалить из таблицы поле final
		 //
		 // был еще вариант обратный маркировать окончательные финальные ряды - пока от него ушли
		 // ФИНАЛЬНЫЙ РЯД!!! $dop_row['final'] - для реализации функционала при котором позиция может иметь несколько расчетов, при этом может быть
		 // что ни один из низ не является финальным, или один может быть установлен как финальный и тогда он участвует в дальнейшем расчете заказа
		 
	 
	
	
		  
		 // если товарная позиция имеет больше одного варианта расчета и один из этих вариатов расчета не является draft
		 // то мы должны переместить его вверх вывода а остальные вариатны вывести ниже
		 // при этом мы указываем $all_draft = FALSE; что не все ряды являются draft
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
		 
		 $all_draft = FALSE;
		 if(isset($row['dop_data']) && count($row['dop_data'])>1){
		     $all_draft = TRUE;
			 foreach($row['dop_data'] as $dop_key => $dop_row){
				 if($dop_row['draft']!=1){
				      $all_draft = FALSE;
					  $row_to_lift_up[$dop_key] = $dop_row;
					  unset($row['dop_data'][$dop_key]);
				  }
			 }
			 // использование в данном случае не возможно потому что этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
			 if(isset($row_to_lift_up)){
			      $row['dop_data']= $row_to_lift_up + $row['dop_data']; 
				  unset($row_to_lift_up);
			 }
		 }
		 else{
		     // !!! НАДО КАК_ТО ЭТО ОТРАБОТАТЬ
		 }
		 
		 // здесь определяем выводить ли дополнительный вспомогательный пустой ряд сверху
		 // когда все остальные являются draft то выводим  т.е. (если не все draft то невыводим)
		 // использование в данном случае не возможно потомучто этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
		 if($all_draft) $row['dop_data']= $service_row + $row['dop_data']; 
			  */
			  
			  
			  
			  

?>