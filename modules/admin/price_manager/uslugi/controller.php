<?php 
    
    $razdel = (!empty($_GET['razdel']))? $_GET['razdel']: FALSE;
	 
	ob_start();	
    switch ($razdel) {
		case 'prices':
		include 'prices_controller.php';
		break;
			
		default:
		include 'prices_controller.php';
		break;
	}
	
	$razdel_content = ob_get_contents();
	ob_get_clean();

?>