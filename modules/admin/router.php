<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['admin']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

    switch ($section) {
		case 'price_manager':
		include 'price_manager/controller.php';
		break;
			
		default:
		include 'controller.php';
		break;
	}
	
	include ROOT.'/skins/tpl/admin/show.tpl';
   
?>