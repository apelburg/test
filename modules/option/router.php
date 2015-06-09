<?php
/*

РАЗДЕЛ БЫЛ СОЗДАН ДЛЯ ПОМЕЩЕНИЯ ДЛЯ НЕГО ФУНКЦИОНАЛА НАСТРОЕК ДЛЯ ОС


*/
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['option']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	$section = (isset($_GET['section']))?$_GET['section']:'none';
 
	 ob_start();	
	 
	 switch($section){
	 
	   case 'uslugi':
	   include 'uslugi_list_controller.php';
	   break;
	   
	   default: 
	   include 'default_controller.php';
	   break;
	
	}
	$content = ob_get_contents();
	ob_get_clean();

	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';
	
	echo $content;
    unset($content);
	
?>