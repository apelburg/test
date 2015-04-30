<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	
	ob_start();	
	 
	 switch($section){
	 
	   case 'rt':
	   include 'rt_controller.php';
	   break;

	   default: 
	   include 'rt_controller.php';
	   break;
	
	}
	
	$content = ob_get_contents();
	ob_get_clean();

	include'./skins/tpl/clients/show.tpl';
	
	unset($content);
?>