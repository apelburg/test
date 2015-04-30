<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['order_art_edit']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';


	// шаблон страницы
	include 'skins/tpl/order_art_edit/show.tpl';
	