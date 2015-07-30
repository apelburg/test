<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['cabinet']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

	include './libs/php/classes/comments_class.php';
	new Comments_for_query_class;
	new Comments_for_order_class;
	$PositionComments = new Comments_for_order_dop_data_class;

	include './libs/php/classes/os_form_class.php';

	include './libs/php/classes/rt_position_no_catalog_class.php';
	include './libs/php/classes/cabinet/cabinet_class.php';		
	include './libs/php/classes/cabinet/cabinet_general_class.php';		


	ob_start();	
	$CABINET = new Cabinet_general();
	$content = ob_get_contents();
	ob_get_clean();

	// обновляем доступы в соответствии с проверенными по базе допусками
	// на случай вхождения админом в чужой аккаунт 
	$ACCESS = $ACCESS_SHABLON[$CABINET->user_access];

	$menu_name_arr = $CABINET->menu_name_arr;
	
	// ЛЕВОЕ МЕНЮ РАЗДЕЛОВ
	## обрабатываем массив разрешённых разделов
	$menu_left = "";
	foreach ($ACCESS['cabinet']['section'] as $key => $value) {
		if($value['access']){
			$menu_left .= '<li '.((isset($_GET["section"]) && $_GET["section"]==$key)?'class="selected"':'').'><a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section='.$key.'&subsection='.key($value['subsection']).'">'.$menu_name_arr[$key].'</a><li>';
		}
	}
		
	// ЦЕНТРАЛЬНОЕ МЕНЮ СВЕРХУ
	$menu_central = "";
	$menu_central_arr = (array_key_exists($section, $ACCESS['cabinet']['section']))?$ACCESS['cabinet']['section'][$section]['subsection']:array();
	foreach ($menu_central_arr as $key2 => $value2) {
		// $menu_central .= "$key2 -";
		$menu_central .= '<li '.((isset($_GET["subsection"]) && $_GET["subsection"]==$key2)?'class="selected"':'').'><a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet'.((isset($_GET["section"]))?'&section='.$_GET["section"]:'').'&subsection='.$key2.'">'.$menu_name_arr[$key2].'</a><li>';
	}

	include'./skins/tpl/common/quick_bar.tpl';

	include'./skins/tpl/cabinet/show.tpl';
		
	unset($content);
?>