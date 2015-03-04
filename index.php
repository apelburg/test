<?php 

    header('Content-type: text/html; charset=utf-8');
	error_reporting(E_ALL);
	set_time_limit(0);

    include('libs/mysql.php');
	include('libs/mysqli.php');
    include('libs/config.php');
	include('libs/lock.php');
	include('libs/php/common.php');
    include('libs/autorization.php');
	include('libs/variables.php');
	

	if(!($user_status == 1 || (isset($_SESSION['access']['come_back_in_own_profile']) && mysql_result(select_manager_data($_SESSION['access']['come_back_in_own_profile']),0,'access') == 1))) exit;

	
	ob_start();	
	//print_r($_SESSION);
    switch($page){
	
	  
	   
	   case 'clients':
	   include 'modules/clients/router.php';
	   break;
	   
	   case 'suppliers':
	   include 'modules/suppliers/router.php';
	   break;
	   
	   case 'samples':
	   include 'modules/samples/router.php';
	   break;
	   /*
	   case 'orders':
	   include 'modules/orders/router.php';
	   break;
	   
	   case 'in_work':
	   include 'modules/in_work/router.php';
	   break;
	   
	   case 'planner':
	   include 'modules/planner/router.php';
	   break;
	      
	   case 'managers':
	   include 'modules/managers/router.php';
	   break;
	   
	   case 'search':
	   include 'modules/search/router.php';
	   break;
	   
	   case 'invoiceforpay':
	   include 'modules/invoiceforpay/router.php';
	   break;
	   
	   case 'our_firms':
	   include 'modules/our_firms/router.php';
	   break;
	   
	   case 'reports':
	   include 'modules/reports/router.php';
	   break;
	   
	   case 'agreement':
	   include 'modules/agreement/router.php';
	   break;
	   
	   case 'common':
	   include 'modules/common/router.php';
	   break;
	   */
	   default: 
	   include 'modules/default/router.php';
	   break;
	
	}
	$content = ob_get_contents();
	ob_get_clean();

	include'./skins/tpl/index.tpl';

?>
