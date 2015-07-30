<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['planner']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	
		// client_details
	
	$client_id = (isset($_GET['client_id']) && $_GET['client_id']!='0')? $_GET['client_id'] :((isset($_POST['client_id']) && $_GET['client_id']!='0')? $_POST['client_id']: FALSE) ;

	///////////////////////////////////////////    информация о клиенте   ////////////////////////////////////////////////
    $client_data_arr = select_all_client_data($client_id);
	//echo '<pre>'; print_r($client_data_arr); echo '</pre>';
	$client_name = $client_data_arr['company'];
	$client_reg_date_arr = explode('-',$client_data_arr['set_client_date']);
	@$client_reg_date = $client_reg_date_arr[2].'.'.$client_reg_date_arr[1].'.'.substr($client_reg_date_arr[0],2);
	// кураторы //////////////
	$manager_nickname = '';
	 
	//print_r($_POST);
	$manager_id_arr = detect_manager_for_client($client_id);
	$forbidd_flag = true;	
	foreach($manager_id_arr as $mngr_id){
	    if($user_id == $mngr_id)$forbidd_flag = false;
	    $manager_nickname .= get_manager_nickname_by_id($mngr_id).', ';	 
	}
	$manager_nickname = trim($manager_nickname,', ');

	 // end кураторы /////////
	$main_cont_face_data = get_main_client_cont_face($client_id);
	///////////////////////////////////////////  end  информация о клиенте   ////////////////////////////////////////////////
	
	
	include 'planner_controller.php';
	//echo $content;
	//
    include ROOT.'/skins/tpl/common/quick_bar.tpl';
	include ROOT.'/skins/tpl/client_folder/rt/client_details_bar.tpl';
	// шаблон страницы
	include ROOT.'/skins/tpl/client_folder/planner/show.tpl';

?>
	
	