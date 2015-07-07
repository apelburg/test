<?php
 
 	include 'controller.php';
 
 
    if(!empty($_GET['usluga'])){
		
	    ob_start();	
		
	    switch ($subsection) {
			case 'price_editor':
			include 'price_editor/controller.php';
			break;
			
			case 'paramY_editor':
			include 'paramY_editor/controller.php';
			break;
			
			case 'sizes_editor':
			include 'sizes_editor/controller.php';
			break;
 
			case 'places_editor':
			include 'places_editor/controller.php';
			break;
			
			case 'coeffs_editor':
			include 'coeffs_editor/controller.php';
			break;
			
			case 'additions_editor':
			include 'additions_editor/controller.php';
			break;
				
			default:
			include 'price_editor/controller.php';
			break;
	    }
		
	    $subsection_content = ob_get_contents();
	    ob_get_clean();
	   
	}	
	else $subsection_content = 'выберите услугу в меню слева';
	
    include ROOT.'/skins/tpl/admin/price_manager/show.tpl';
   
?>