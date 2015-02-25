<?php
    

	 /////////////////////////////////// AJAX //////////////////////////////////////
	 
	
	 
	 /////////////////////////////////// AJAX //////////////////////////////////////
	 
	 ob_start();	
	 
	 switch($section){
	 
	   case 'suppliers_list':
	   include 'suppliers_list_controller.php';
	   break;
	   
	   case 'supplier_data':
	   include 'supplier_data_controller.php';
	   break;
	   
	   case 'profiles_list':
	   include 'profiles_list_controller.php';
	   break;
	   
	   case 'profile_data':
	   include 'profile_data_controller.php';
	   break;

	   default: 
	   include 'default_controller.php';
	   break;
	
	}
	
	$content = ob_get_contents();
	ob_get_clean();

	include'./skins/tpl/clients/show.tpl';
	
?>