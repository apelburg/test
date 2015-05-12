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

	foreach ($variants as $key => $value) {
		// формируем размерную таблицу
		if($draft_enable){
			$display_this_block = ($value['draft']=='0')?' style="display:block"':' style="display:none"';
		}else{
			$display_this_block = ($key==0)?' style="display:block"':' style="display:none"';
		}
		
		$get_size_table = $ARTICUL->get_size_table($art_dop_params,$value);
		// echo $value;
		$rr = json_decode($value['tirage_json'], true);
		$sum_tir = $sum_dop = 0;
		// echo '<pre>';
		// print_r($value);
		// echo '</pre>';
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

		


		// echo '<pre>';
		// print_r($rr);
		// echo '</pre>';


		include 'skins/tpl/client_folder/order_art_edit/variants_template.tpl';

	}
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	

	// шаблон страницы
	include 'skins/tpl/client_folder/order_art_edit/show.tpl';

	
	