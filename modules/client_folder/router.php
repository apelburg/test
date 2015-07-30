<?php
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	

	 $planner_display = '<div class="quick_button_circle">
							<div class="quick_button_circle__circle" style="background-image: url(./skins/images/img_design/button_circle_2.png); border-color:red">
								<div class="quick_button_circle__alert"></div>
							</div>
							<div class="quick_button_circle__text"><a href="?page=client_folder&section=planner&client_id='.$client_id.'">Планировщик</a></div>
						</div>'; 
	
	
	ob_start();	
	 
	switch($section){
	 
		case 'rt':
		include 'rt/router.php';
		break;

		case 'order_tbl':
		include 'order_tbl/router.php';
		break;

		case 'rt_position':
		include 'rt_position/router.php';
		break;
		
		case 'business_offers':
		include 'business_offers/router.php';
		break;
		
		case 'agreements':
		include 'agreements/router.php';
		break;
		
		case 'planner':
		include 'planner/router.php';
		break;

		default: 
		include 'rt/router.php';
		break;
	
	}
	
	$content = ob_get_contents();
	ob_get_clean();


   

	include'./skins/tpl/client_folder/show.tpl';
	
	unset($content);
?>