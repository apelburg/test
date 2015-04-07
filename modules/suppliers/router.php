<?php
    
	include_once('./libs/php/classes/supplier_class.php');
	 /////////////////////////////////// AJAX //////////////////////////////////////
	 
	
	 
	 /////////////////////////////////// AJAX //////////////////////////////////////
	 
	 ob_start();	
	 
	 switch($section){
	 
	   case 'suppliers_list':
	   include 'suppliers_list_controller.php';
	   break;
	   
	   case 'suppliers_data':
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

	
	
	echo $content;
    unset($content);
	
?>