<?php
	if(!isset($type_product)){echo "Тип товара не определён,<br>или строка с id=".$_GET['id']." в таблице `".RT_DOP_DATA."` не существует ";exit;}
	
	// инициализация класса работы с позициями
	// ВНИМАНИЕ!!!
	// AJAX ОБРАБАТЫВАЕТСЯ ВНУТРИ КЛАССОВ
	$POSITION_GEN = new Position_general_Class();


	// получаем все варианты просчёта по данному артикулу
	$variants_arr = $POSITION_GEN->POSITION_CATALOG->get_all_variants_info_Database_Array($id);

	// получаем необходимые данные в переменные класса
	$POSITION_GEN->POSITION_CATALOG->get_all_info($art_id);

	// основная информация по артикулу
	$articul = $POSITION_GEN->POSITION_CATALOG->info;
	// информация по позиции
	$info_main = $POSITION_GEN->POSITION_CATALOG->info_main;

	// акртикул
	$art = $articul['art'];
	//цвет
	$art_colors = implode(", ", $POSITION_GEN->POSITION_CATALOG->color);
	// ссылка на сайт 
	$link_of_the_site = '<a target="_blank" href="http://'.$_SERVER['HTTP_HOST'].'/description/'.$info_main['art_id'].'"><img src="http://'.$_SERVER['HTTP_HOST'].'/os/skins/images/img_design/basic_site_link.png"></a>';
	// материал
	$art_materials = implode(", ", $POSITION_GEN->POSITION_CATALOG->material);
	// вид печати
	$print_names = $POSITION_GEN->POSITION_CATALOG->get_print_names_string();
	// получаем изображения артикула
	$images_data = $POSITION_GEN->POSITION_CATALOG->fetch_images_for_article2($art_id,'1');
	// получаем дополнительные параметры: размер, цену ...
	$art_dop_params = $POSITION_GEN->POSITION_CATALOG->get_dop_params($art_id);
	
	
	// вычисление вариантов данного артикула с другими цветами
	if($color_variants = $POSITION_GEN->POSITION_CATALOG->get_art_color_variants($art)){
		$color_variants_block = $POSITION_GEN->POSITION_CATALOG->color_variants_to_html2($color_variants);	
	}else{$color_variants_block = '';}



	// варианты просчётов
	ob_start();		
	
	$arr_for_type = $POSITION_GEN->POSITION_CATALOG->get_variants_arr_sort_for_type($variants_arr);

	$ch = 0;
	foreach ($variants_arr as $key => $variant) {
		// по умолчанию блоки скрыты
		$display_this_block = ' style="display:none"';
		// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
		if(!isset($_GET['show_archive']) && $variant['row_status']=='red'){ continue;}

		// если это зона записи red, добавляем класс запрещающий редактирование данного блока
		if($variant['row_status']=='red'){
			$show_archive_class = " archiv_opacity";
		}else{
			$show_archive_class ='';
		}

		///////// ВАРИАНТЫ СВЕТОФОР  ///////
		switch ( $variant['row_status'] ) {
			case 'sgreen':// 
				if($ch < 1){
					$display_this_block=' style="display:block"';$ch++;
				}	
				break;	

			case 'green':// не история - рабочий вариант расчёта
				// может входить в КП
				if($ch < 1 && @count($arr_for_type['sgreen']) == 0){$display_this_block=' style="display:block"';$ch++;}				
				break;	
			
			case 'grey':// не история - вариант расчёта не учитывается в РТ
				// серый вариант расчёта не входит в КП	
				if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0){
					$display_this_block=' style="display:block"';$ch++;
				}
				break;						
			
			default: // вариант расчёта red (архив), остальное не важно
				if ($ch == 0 && @count($arr_for_type['green']) == 0 && @count($arr_for_type['sgreen']) == 0 && @count($arr_for_type['grey']) == 0){
					$display_this_block =' style="display:block"';$ch++;
				}
				break;
		}
		
		// размерная таблица
		$get_size_table = $POSITION_GEN->POSITION_CATALOG->get_size_table($art_dop_params,$variant);
	
		// тираж
		$sum_tir = $variant['quantity'];
		$sum_dop = $variant['zapas'];
		// сумма за тираж для нас
		$sum_of_tirage_in = round($variant['price_in'] * ($sum_tir + $sum_dop),2);
		// сумма за тираж для клиента
		$sum_of_tirage_out = round($variant['price_out'] * ($sum_tir + $sum_dop),2);
		// сумма прибыль
		$sum_prib_of_tirage = $sum_of_tirage_out-$sum_of_tirage_in;
		// печатаем / не печатаем запас
		$print_z = ($variant['print_z']=='1')?'checked':'';
		$print_z_no = ($variant['print_z']=='0')?'checked':'';

		
		// стандартное время изготовления
		//$std_time_print = ($variant['standart']=='10' && $type_tovar=='cat')?'checked':'';

		$shipping_type__show_date = ' style="display:none"';
		$shipping_type__show_rd = ' style="display:none"';
		
		switch ($variant['shipping_type']) {
			case 'date':
				$shipping_type__show_date = ' style=""';
				break;
			case 'rd':
				$shipping_type__show_rd = ' style=""';
				break;
			
			default:
				# code...
				break;
		}


		
		include 'skins/tpl/client_folder/rt_position/variants_template.tpl';

	}



	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
?>