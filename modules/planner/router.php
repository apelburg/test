<?php
	
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['planner']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	if(isset($_GET['show_client_list_by_manager'])){
	    $itog_arr = get_clients_list_for_user($_GET['manager_id'],array('company',''));
		$str1 = $str2 = '';
		foreach($itog_arr as $val) $str1 .=$val['id'].'{@}';
		foreach($itog_arr as $val) $str2 .=$val['company'].'{@}';
		echo trim($str1,'{@}').'{@#@#@}'.trim($str2,'{@}');
		exit;
	}
	
	
	include 'controller.php';
	//echo $content;
	//
    include ROOT.'/skins/tpl/common/quick_bar.tpl';
	include ROOT.'/skins/tpl/client_folder/rt/client_details_bar.tpl';
	// шаблон страницы
	include ROOT.'/skins/tpl/planner/show.tpl';

?>
	
	