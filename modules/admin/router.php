<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['admin']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

    ob_start();	
	
    switch ($section) {
		case 'price_manager':
		include 'price_manager/router.php';
		break;

		case 'edit_our_uslugi':
		include 'edit_our_uslugi/router.php';
		break;
		
		case 'places_editor':
		include 'places_editor/router.php';
		break;
		
		case 'prints_manager':
		include 'prints_manager/router.php';
		break;
		
		case 'form_edit':
		include 'form_edit/router.php';
		break;
			
		default:
		include 'controller.php';
		break;
	}
	
	$content = ob_get_contents();
	ob_get_clean();

	include ROOT.'/skins/tpl/admin/show.tpl';
   
?>