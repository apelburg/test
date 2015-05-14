<?php
	
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


	function fetch_rows_from_rt($order_num){
	     global $mysqli;
		 
		 $rows = array();
		 
		 $query = "SELECT main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art AS art ,main_tbl.name AS item_name ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_in AS dop_t_price_in , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.row_status AS row_status, dop_data_tbl.glob_status AS glob_status, dop_data_tbl.draft AS draft, dop_data_tbl.expel AS expel,
						  
						  dop_uslugi_tbl.id AS uslugi_id , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_in AS uslugi_t_price_in , dop_uslugi_tbl.price_out AS uslugi_t_price_out
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE main_tbl.order_num ='".$order_num."' ORDER BY main_tbl.id";
				  
		 $result = $mysqli->query($query) or die($mysqli->error);
		 $multi_dim_arr = array();
	     while($row = $result->fetch_assoc()){
		     if(!isset($multi_dim_arr[$row['main_id']])){
			     $multi_dim_arr[$row['main_id']]['row_type'] = $row['main_row_type'];
				 $multi_dim_arr[$row['main_id']]['art'] = $row['art'];
				 $multi_dim_arr[$row['main_id']]['name'] = $row['item_name'];
			 }
			 //$multi_dim_arr[$row['main_id']]['uslugi_id'][] = $row['uslugi_id'];
			 if(isset($multi_dim_arr[$row['main_id']]) && !isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) &&!empty($row['dop_data_id'])){
			     $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']] = array(
																	'draft' => $row['draft'],
																	'expel' => $row['expel'],
																	'row_status' => $row['row_status'],
																	'glob_status' => $row['glob_status'],
																	'quantity' => $row['dop_t_quantity'],
																	'price_in' => $row['dop_t_price_in'],
																	'price_out' => $row['dop_t_price_out']);
		    }
			if(isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) && !empty($row['uslugi_id'])){
			    $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['dop_uslugi'][$row['uslugi_t_glob_type']][$row['uslugi_id']] = array(
																									'type' => $row['uslugi_t_type'],
																									'quantity' => $row['uslugi_t_quantity'],
																									'price_in' => $row['uslugi_t_price_in'],
																									'price_out' => $row['uslugi_t_price_out'],
																									'uslugi_id' => $row['uslugi_id']
																									);
			}
			
			//print_r( $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['print_data']); echo "<br>";
			
			$rows[]= '<tr><td>'.implode('</td><td>',$row).'</td></tr>';
		   
		 }
	     return array($multi_dim_arr,$rows);
	 }
	 
	 $rows = fetch_rows_from_rt(10147);
	 
	 

	 $test_data = FALSE; // TRUE
	 // Построение таблицы РТ
	 // основой для рядов таблицы (тегов <tr>) является второй уровень массива $rows[0], тоесть это те данные которые лежат в $row['dop_data']
	 // каждый элемент этого массива выводится как отдельный ряд (в ходе обработки вывода могут быть добавлены дополнительные вспомагательные 
	 // элементы или изменен их порядок) 
	 // Тоесть таблица, количество (тегов <tr>), формируется на основе уровня $row['dop_data'], остальные уровни только влияют на результирующий
	 // вид таблицы
	 // элементы первого уровня выводятся один раз на весь блок элементов $row['dop_data']
	 // элементы третьего уровня $dop_row['dop_uslugi'] выводятся в виде ссылки внутри существующего ряда, если $dop_row['dop_uslugi'] существует
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
	 //
	 //echo '<pre>'; print_r($rows[0]); echo '</pre>';
	 
	 $service_row[0] = array('quantity'=>0,'price_in'=>0,'price_out'=>0,'row_status'=>0,'glob_status'=>0);
	 foreach($rows[0] as $key => $row){
         // Проходим по первому уровню и определям некоторые моменты отображения таблицы, которые будут применены при проходе по второму
		 // уровню массива, ряды таблицы будут создаваться там
		 
		 // если товарная позиция имеет больше одного варианта расчета и один из этих вариатов расчета не является draft
		 // то мы должны переместить его вверх вывода а остальные вариатны вывести ниже
		 // при этом мы указываем $all_draft = FALSE; что не все ряды являются draft
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
		 $all_draft = FALSE;
		 if(count($row['dop_data'])>1){
		     $all_draft = TRUE;
			 foreach($row['dop_data'] as $dop_key => $dop_row){
				 if($dop_row['draft']!=1){
				      $all_draft = FALSE;
					  $row_to_lift_up[$dop_key] = $dop_row;
					  unset($row['dop_data'][$dop_key]);
				  }
			 }
			 // использование в данном случае не возможно потомучто этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
			 if(isset($row_to_lift_up)){
			      $row['dop_data']= $row_to_lift_up + $row['dop_data']; 
				  unset($row_to_lift_up);
			 }
		 }
		
		 
		 // здесь определяем выводить ли дополнительный вспомогательный пустой ряд сверху
		 // когда все остальные являются draft то выводим  т.е. (если не все draft то невыводим)
		 // использование в данном случае не возможно потомучто этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
		 if($all_draft) $row['dop_data']= $service_row + $row['dop_data']; 
		 
		 //array_unshift($row['dop_data'],array('quantity'=>0,'price_in'=>0,'price_out'=>0,'row_status'=>0,'glob_status'=>0));
		 
		 
		 // здесь мы определяем значение для атрибута rowspan тегов td которые будут выводится единой ячейкой для всей товарной позиции
		 $row_span = count($row['dop_data']);
		 $counter=0;
		  
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>---';
		 // Проходим в цикле по второму уровню массива($row['dop_data']) на основе которого стороится основной шаблон таблицы
	     foreach($row['dop_data'] as $dop_key => $dop_row){
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
				     $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
					 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
				 }
				 $print_btn = count($dop_row['dop_uslugi']['print']); 
				 $print_in_summ = array_sum($summ_in);
			     $print_out_summ = array_sum($summ_out);
			     if($test_data) $print_open_data = print_r($dop_row['dop_uslugi']['print'],TRUE);
			 }
			 else{// если данных по печати нет то проверяем - не являются ли все ряды draft а данный ряд первым, если да то
			      // выводим пустое значение для пустого верхнего ряда, если нет выводим кнопку добавление нанесения
			     $print_btn = ($all_draft && $counter==0)? '' : '+';
				 $print_in_summ = 0;
			     $print_out_summ = 0;
				 if($test_data) $print_open_data =($all_draft && $counter==0)? 0:'0';
			 }
			 // 2. определяем данные описывающие варианты дополнительных услуг, они хранятся в $dop_row['dop_uslugi']['extra']
			 if(isset($dop_row['dop_uslugi']['extra'])){// если $dop_row['dop_uslugi']['extra'] есть выводим данные о дополнительных услугах 
			     $summ_in = $summ_out = array();
				 foreach($dop_row['dop_uslugi']['extra'] as $extra_data){
				     $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
					 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
				 }
				 $dop_uslugi_in_summ = array_sum($summ_in);
			     $dop_uslugi_out_summ = array_sum($summ_out);
				 $dop_uslugi_btn = count($dop_row['dop_uslugi']['extra']);
                 if($test_data) $extra_open_data =  print_r($dop_row['dop_uslugi']['extra'],TRUE);
			 }
			 else{// если данных по дополнительным услугам нет то проверяем - не являются ли все ряды draft а данный ряд первым, если да 
			      // выводим пустое значение для пустого верхнего ряда, если нет выводим кнопку добавление дополнительных услуг
			     $dop_uslugi_in_summ = 0;
				 $dop_uslugi_out_summ = 0;
				 $dop_uslugi_btn = ($all_draft && $counter==0)? '' : '+';
			     if($test_data) $extra_open_data =($all_draft && $counter==0)? 0:'0';
			 }
			 
			 // подсчет сумм ряду
			 // 1. подсчитываем входящую сумму
			 $price_in_summ = $dop_row['quantity']*$dop_row['price_in'];
			 $in_summ = $price_in_summ;
			 if(!(!!$expel["print"]))$in_summ += $print_in_summ;
			 if(!(!!$expel["dop"]))$in_summ += $dop_uslugi_in_summ;
			 // 2. подсчитываем исходящую сумму 
			 $price_out_summ =  $dop_row['quantity']*$dop_row['price_out'];
			 $out_summ =  $price_out_summ;
			 if(!(!!$expel["print"]))$out_summ += $print_out_summ;
			 if(!(!!$expel["dop"]))$out_summ += $dop_uslugi_out_summ;
			 
			 $delta = $out_summ-$in_summ; 
			 $margin = $out_summ-$in_summ;
			 
			 // если ряд не исключен из расчетов добавляем значения в итоговый ряд
			 if(!(!!$expel["main"])){
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
			 $svetofor_src = ($dop_row['row_status']=='')? $img_design_path.'rt_svetofor_green.png':$img_design_path.'rt_svetofor_'.$dop_row['row_status'].'.png';
			 $svetofor = ($dop_key!=0)? '<img src="'.$svetofor_src.'">':'';
		 
		     $cur_row  =  '';
		     $cur_row .=  '<tr row_id="'.$dop_key.'"  class="'.(($counter==0)?'pos_edge':'').'">';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="top" width="30">'.$key.'</td>':'';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="top" width="80">MK</td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$dop_key.'</td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="hidden">'.$row['row_type'].'</td>':'';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" width="300" class="top"><a href="?page=client_folder&section=order_art_edit&id='.$dop_key.'">'.$row['art'].''.$row['name'].'</a></td>':'';
			/* $cur_row .= '<td rowspan="" class="hidden">'.$dop_key.'</td>';
		     $cur_row .= '<td rowspan="" class="hidden">'.$row['row_type'].'</td>';
			 $cur_row .=  '<td rowspan="" width="300" class="top"><a href="?page=client_folder&section=order_art_edit&id='.$dop_key.'">'.$row['art'].''.$row['name'].'</a></td>';*/
			 $cur_row .=  '<td class="hidden">'.@$dop_row['draft'].'</td>
			               <td width="50" svetofor="1" class="svetofor pointer">'.$svetofor.'</td>
			               <td width="50" type="quantity" class="r_border"  editable="true">'.$dop_row['quantity'].'</td>
						   <td width="90" type="price_in" editable="true" connected_vals="art_price" c_stat="1" class="in right">'.$dop_row['price_in'].'</td>
						   <td width="15" connected_vals="art_price" c_stat="1" class="currency left">р</td>
						   <td width="90" type="price_in_summ" connected_vals="art_price" c_stat="0" class="in right hidden">'.number_format($price_in_summ,'2','.','').'</td>
						   <td width="15" connected_vals="art_price" c_stat="0" class="currency left hidden">р</td>
						   <td width="90" type="price_out" editable="true" connected_vals="art_price" c_stat="1" class="out right">'.$dop_row['price_out'].'</td>
						   <td width="15" class="currency left r_border" connected_vals="art_price" c_stat="1" >р</td>
						   <td width="90" type="price_out_summ"  connected_vals="art_price" c_stat="0" class="out right hidden">'.number_format($price_out_summ,'2','.','').'</td>
						   <td width="15" connected_vals="art_price" c_stat="0" class="currency left r_border hidden">р</td>
						   <td width="20">'.$print_btn.'</td>';
                 if($test_data)	 $cur_row .=  '<td class="test_data">'.$print_open_data.'</td>';
			 $cur_row .=  '<td width="90" type="print_in_summ" class="test_data in hidden">'.$print_in_summ.'</td> 
			               <td width="70" type="print_out_summ" class="out '.(($expel['print']=='1')?' red_cell':'').'" expel="'.$expel['print'].'">'.number_format($print_out_summ,'2','.','').'</td>
			               <td width="20">'.$dop_uslugi_btn.'</td>';
			     if($test_data)	 $cur_row .=  '<td class="test_data">'.$extra_open_data.'</td>';
			 $cur_row .=  '<td type="dop_uslugi_in_summ" class="test_data in hidden">'.$dop_uslugi_in_summ.'</td>';
			 $cur_row .=  '<td width="90" type="dop_uslugi_out_summ" class="out r_border'.(($expel['dop']=='1')?' red_cell':'').'" expel="'.$expel['dop'].'">'.number_format($dop_uslugi_out_summ,'2','.','').'</td>
						   <td type="in_summ" connected_vals="total_summ" c_stat="0" class="in hidden" width="100">'.number_format($in_summ,'2','.','').'</td>
						   <td type="out_summ" connected_vals="total_summ" c_stat="1" class="out '.(($expel['main']=='1')?' red_cell':'').'" expel="'.$expel['main'].'" width="100">'.number_format($out_summ,'2','.','').'</td>
						   <td type="delta" >'.number_format($delta,'2','.','').'</td>
						   <td type="margin" >'.number_format($margin,'2','.','').'</td>';
			 $cur_row .=  '<td>'.$dop_row['glob_status'].'</td>';  
			 $cur_row .= '</tr>';
			 
			 // загружаем сформированный ряд в итоговый массив
		     $tbl_rows[]= $cur_row;
		     $counter++;
		 }
	 }
	 $rt = '<table class="rt_tbl_head" id="rt_tbl_head" scrolled="head" style="width: 100%;" border="0">
	          <tr class="cap">
			      <td width="30"></td>
			       <td width="80">
					  <div class="master_button noselect">
						<a href="#" onclick="openCloseMenu(event,\'tableMenu\'); return false;">&nbsp;</a>
						<div id="reset_master_button" class="reset_button" onclick="resetMasterBtn(this);">&nbsp;</div>
					  </div>
				  </td>
	              <td class="hidden"></td>
				  <td class="hidden">тип</td>
				  <td width="300" class="right">
				      &nbsp;<a href="#" onclick="print_r(rtCalculator.tbl_model);">_</a>
					  прибыль ???? р подробно?
				  </td>
				  <td class="hidden">draft</td>
				  <td width="50"><img src="'.HOST.'/skins/images/img_design/rt_svetofor_top_btn.png"></td>
				  <td width="50" class="r_border">тираж</td>
				  <td width="90" connected_vals="art_price" c_stat="1" class="right pointer">$ товара<br><span class="small">входящая штука</span></td>
				  <td width="15" connected_vals="art_price" c_stat="1"</td>
				  <td width="90" connected_vals="art_price" c_stat="0" class="right hidden pointer">$ товара<br><span class="small">входящая тираж</span></td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="hidden"></td>
				  <td width="90" connected_vals="art_price" c_stat="1" class="right pointer">$ товара<br><span class="small">исходящая штука</span></td>
				  <td width="15" connected_vals="art_price" c_stat="1"class="r_border"></td>
				  <td width="90" connected_vals="art_price" c_stat="0" class="right pointer hidden">$ товара<br><span class="small">исходящая тираж</span></td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="r_border hidden"></td>
				  <td width="20"></td>';
	if($test_data)	 $rt.= '<td class="test_data_cap">нанес подробн</td>';
	       $rt.= '<td width="70" class="test_data_cap hidden">нанес вход</td> 	  
			      <td width="90">нанесение выход</td>
			      <td width="20"></td>';
    if($test_data)	 $rt.= '<td class="test_data_cap">доп.усл подробн</td>';
           $rt.= '<td class="test_data_cap hidden">доп.усл вход</td> 
			      <td width="90" class="out r_border">доп.усл выход</td>
				  <td width="100" connected_vals="total_summ" c_stat="0" class="hidden">сумма вход</td>
				  <td width="100" connected_vals="total_summ" c_stat="1">сумма выход</td>
				  <td>дельта</td>
				  <td>маржина-<br>льность</td>
                  <td width="70">статус</td>';              
	    $rt.= '</tr>
	           <tr row_id="total_row">
			      <td width="30"></td>
			      <td width="80"></td>
	              <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td class="hidden">44</td>
				  <td class="right">Счет №45384? оплата 70%?</td>
				  <td></td>
				  <td class="r_border"></td>  
				  <td connected_vals="art_price" c_stat="1"></td>
				  <td width="15" connected_vals="art_price" c_stat="1"></td>
				  <td type="price_in_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format($total['price_in_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="hidden">р</td>
				  <td connected_vals="art_price" c_stat="1"></td>
				  <td width="15" connected_vals="art_price" c_stat="1" class="r_border"></td>
				  <td type="price_out_summ" connected_vals="art_price" c_stat="0" class="right hidden">'.number_format($total['price_out_summ'],'2','.','').'</td>
				  <td width="15" connected_vals="art_price" c_stat="0" class="r_border hidden">р</td>
				  <td></td>';
	if($test_data)	$rt.= '<td class="test_data_cap"></td>';
	       $rt.= '<td type="print_in_summ" class="test_data_cap hidden">'.number_format($total['print_in_summ'],'2','.','').'</td> 		  
			      <td type="print_out_summ">'.number_format($total['print_out_summ'],'2','.','').'</td>
			      <td></td>';
    if($test_data)	$rt.= '<td class="test_data_cap"></td>';
           $rt.= '<td type="dop_uslugi_in_summ" class="test_data_cap hidden">'.number_format($total['dop_uslugi_in_summ'],'2','.','').'</td> 
			      <td type="dop_uslugi_out_summ" class="out r_border">'.number_format($total['dop_uslugi_out_summ'],'2','.','').'</td>
			      <td type="in_summ" connected_vals="total_summ" c_stat="0" class="hidden" width="100">'.number_format($total['in_summ'],'2','.','').'</td>
				  <td type="out_summ" connected_vals="total_summ" c_stat="1"  width="100">'.number_format($total['out_summ'],'2','.','').'</td>
				  <td type="delta">'.number_format(($total['out_summ']-$total['in_summ']),'2','.','').'</td>
				  <td type="margin">'.number_format(($total['out_summ']-$total['in_summ']),'2','.','').'</td>
                  <td></td>';              
	   $rt.= '</tr>
	          </table>
			  <div id="scrolled_part_container" class="scrolled_tbl_movable_part">
	          <table class="rt_tbl_body" id="rt_tbl_body" scrolled="body"  border="0">'.implode('',$tbl_rows).'</table>
			  </div>';
			  
/*	 $rt = '<table class="rt_tbl_head" scrolled="head" style="width: 100%;">
	          <tr class="cap">
	              <td class="hidden"></td>
				  <td class="hidden">тип</td>
				  <td width="300">&nbsp;<a href="#" onclick="print_r(rt_calculator.tbl_model);">_</a>наименование</td>
				  <td class="hidden">draft</td>
				  <td width="50">статус</td>
				  <td width="50">кол-во</td>
				  <td width="70">вход</td>
				  <td width="70">вход</td>
				  <td width="70">выход</td>
				  <td width="70">выход</td>
				  <td width="20"></td>';
	if($test_data)	 $rt.= '<td class="test_data_cap">нанес подробн</td>';
	       $rt.= '<td width="70" class="test_data_cap hidden">нанес вход</td> 	  
			      <td width="70">нанесение выход</td>
			      <td width="20"></td>';
    if($test_data)	 $rt.= '<td class="test_data_cap">доп.усл подробн</td>';
           $rt.= '<td class="test_data_cap hidden">доп.усл вход</td> 
			      <td width="70">доп.усл выход</td>
				  <td>сумма вход</td>
				  <td>сумма выход</td>
				  <td>дельта</td>
				  <td>маржинальность</td>
                  <td width="70">статус</td>';              
	    $rt.= '</tr>
	           <tr row_id="total_row">
	              <td class="hidden"></td>
				  <td class="hidden"></td>
				  <td></td>
				  <td class="hidden"></td>
				  <td></td>
				  <td></td>
				  <td></td>
				  <td type="price_in_summ">'.number_format($total['price_in_summ'],'2','.','').'</td>
				  <td></td>
				  <td type="price_out_summ">'.number_format($total['price_out_summ'],'2','.','').'</td>
				  <td></td>';
	if($test_data)	$rt.= '<td class="test_data_cap"></td>';
	       $rt.= '<td  type="print_in_summ" class="test_data_cap hidden">'.number_format($total['print_in_summ'],'2','.','').'</td> 		  
			      <td type="print_out_summ">'.number_format($total['print_out_summ'],'2','.','').'</td>
			      <td></td>';
    if($test_data)	$rt.= '<td class="test_data_cap"></td>';
           $rt.= '<td type="dop_uslugi_in_summ" class="test_data_cap hidden">'.number_format($total['dop_uslugi_in_summ'],'2','.','').'</td> 
			      <td type="dop_uslugi_out_summ">'.number_format($total['dop_uslugi_out_summ'],'2','.','').'</td>
			      <td type="in_summ">'.number_format($total['in_summ'],'2','.','').'</td>
				  <td type="out_summ">'.number_format($total['out_summ'],'2','.','').'</td>
				  <td type="delta">'.number_format(($total['out_summ']-$total['in_summ']),'2','.','').'</td>
				  <td type="margin">'.number_format(($total['out_summ']-$total['in_summ']),'2','.','').'</td>
                  <td></td>';              
	   $rt.= '</tr>
	          </table>
			  <div id="scrolled_part_container" class="scrolled_tbl_movable_part">
	          <table class="rt_tbl_body" id="rt_tbl" scrolled="body">'.implode('',$tbl_rows).'</table>
			  </div>';*/
	 
	
	
		/* 
		 ВАРИАНТ С ФИНАЛЬНЫМ РЯДОМ
		 
		// если товарная позиция имеет больше одного варианта расчета и один из этих вариатов расчета обозначен как финальный
		 // то мы должны переместить его вверх вывода а остальные вариатны вывести ниже
		 $final_row = FALSE;
		 foreach($row['dop_data'] as $dop_key => $dop_row){
		     if($dop_row['final']==1){
			      $final_row = $dop_row;
				  unset($row['dop_data'][$dop_key]);
		      }
		 }
		 if($final_row) array_unshift($row['dop_data'],$final_row);
		 // если товарная позиция имеет больше одного варианта расчета и ни один из этих вариатов расчета не обозначен как финальный 
		 // добавляем пустой ряд в начало строки
		 if(!$final_row && count($row['dop_data'])>1) array_unshift($row['dop_data'],array('quantity'=>0,'price_in'=>0,'price_out'=>0,'print_data'=>0));
		 $row_span = count($row['dop_data']);
		 $counter=0;
		 
		 
		  ВАРИАНТ С RT_PRINT_DATA
		 
		  $query = "SELECT main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art AS art ,main_tbl.name AS item_name ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_in AS dop_t_price_in , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.final AS final,  dop_data_tbl.draft AS draft,
						  
						  print_data_tbl.id AS print_id , print_data_tbl.dop_row_id AS print_t_dop_row_id ,print_data_tbl.type AS print_t_type ,
		                  print_data_tbl.quantity AS print_t_quantity ,print_data_tbl.price AS print_t_price 
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_PRINT_DATA."` print_data_tbl ON  dop_data_tbl.id = print_data_tbl.dop_row_id
		          WHERE main_tbl.order_num ='".$order_num."' ORDER BY main_tbl.id";
		 */
?>