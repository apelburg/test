<?php
	$forum = '';
	include './libs/php/classes/articul_class.php';
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['order_art_edit']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	include 'controller.php';


	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';

	// шаблон forum
	// ob_start();	
	
	// include 'skins/tpl/client_folder/order_art_edit/forum.tpl';
	
	// $forum = ob_get_contents();
	// ob_get_clean();

	// варианты
	ob_start();		
		// echo '<pre>';
		// print_r($variants);
		// echo '</pre>';
	$draft_enable = 0;
	foreach ($variants as $k => $v) {
		if($v['draft']=='0'){$draft_enable = 1;}
	}	

	$display_this_block = ' style="display:block"';
	$di = 0;
	foreach ($variants as $key => $value) {
		if(!isset($_GET['show_archive']) && $value['archiv']=='1'){ continue;}

		if((int)$value['archiv']){$show_archive_class = " archiv_opacity";}else{$show_archive_class ='';}


		$var = $value['draft'].$value['archiv'];
		switch ($var) {
			case '00':// не история - главный вариант расчёта
				if($di>0){$display_this_block = ' style="display:none"';}else{
					$display_this_block = ' style="display:block"';$di++;}
			break;
			
			case '10':// не история - обычный вариант расчёта
				if($draft_enable || $di>0){$display_this_block=' style="display:none"';
				}else{//$display_this_block = ' style="display:block"';
					$di++;}
			break;
						
			default: // архив, остальное не важно				
					$display_this_block = ' style="display:none"';
			break;
		}
		
		$get_size_table = $ARTICUL->get_size_table($art_dop_params,$value);

		$rr = json_decode($value['tirage_json'], true);
		$sum_tir = $sum_dop = 0;
		foreach ($rr as $k => $v) {
			$sum_tir += (isset($v['tir']))?(int)$v['tir']:0;
			$sum_dop += (isset($v['dop']))?(int)$v['dop']:0;
		}
		// сумма за тираж для нас
		$sum_of_tirage_in = round($value['price_in']*($sum_tir+$sum_dop),2);
		// сумма за тираж для клиента
		$sum_of_tirage_out = round($value['price_out']*($sum_tir+$sum_dop),2);
		// сумма прибыль
		$sum_prib_of_tirage = $sum_of_tirage_out-$sum_of_tirage_in;
		// печатаем / не печатаем запас
		$print_z = ($value['print_z']=='1')?'checked':'';
		$print_z_no = ($value['print_z']=='0')?'checked':'';

		// стандартное время изготовления
		// $std_pr = ($value['standart']=='10' && $type_tovar=='cat')?1:0;
		$std_time_print = ($value['standart']=='10' && $type_tovar=='cat')?'checked':'';


		include 'skins/tpl/client_folder/order_art_edit/variants_template.tpl';

	}
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	

	// шаблон страницы
	include 'skins/tpl/client_folder/order_art_edit/show.tpl';

	
	