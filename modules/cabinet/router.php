<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['cabinet']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

		
    /////////////////////////////////// AJAX //////////////////////////////////////
	if(isset($_POST['AJAX'])){
		// ИЗМЕНЕНИЯ РАЗРЕШЁННЫЕ ДЛЯ БУЧА
		if($_POST['AJAX'] == 'change_payment_date'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_date` =  '".$_POST['date']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_payment_status'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `payment_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'number_payment_list'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `number_pyament_list` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'select_global_status'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `global_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'buch_status_select'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `buch_status` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_ttn_number'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `ttn_number` =  '".$_POST['value']."', ttn_get = NOW() WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_delivery_tir'){
			// print_r($_POST);
			$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  `delivery_tir` =  '".$_POST['value']."' WHERE  `id` ='".$_POST['row_id']."';";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if($_POST['AJAX'] == 'change_status_snab'){
		// print_r($_POST);
			$query = "UPDATE `".CAB_ORDER_MAIN."` SET  `status_snab` =  '".$_POST['value']."' WHERE  `".CAB_ORDER_MAIN."`.`id` =".$_POST['row_id'].";";
		// $query = "UPDATE  SET `status_snab` = ' ' WHERE `id` = ".$_POST['row_id'].";";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}	
	}
	 
	/////////////////////////////////// AJAX //////////////////////////////////////
		
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

	// }
?>