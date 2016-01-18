<?php
	

	
	//$page = !empty($_GET['page'])? ((strpos($_GET['page'],'/'))? substr($_GET['page'],0,strpos($_GET['page'],'/')):$_GET['page']) : 'main';
	$page = !empty($_GET['page'])? $_GET['page'] : FALSE ;
	$section = !empty($_GET['section'])? $_GET['section'] : FALSE ;
	$subsection = !empty($_GET['subsection'])? $_GET['subsection'] : FALSE ;
	
	$client_id = !empty($_GET['client_id'])? (int)$_GET['client_id'] : FALSE ;
	$num_page = !empty($_GET['num_page'])? (int)$_GET['num_page'] : 1 ;
	$query_num = !empty($_GET['query_num'])? $_GET['query_num'] : FALSE ;
	$quick_bar_tbl =  $quick_button = $view_button = '';
	
	$form_data = !empty($_POST['form_data'])? $_POST['form_data'] : NULL; // массив для передачи данных через форму
	
	$quick_button_back = '<a href="javascript:history.go(-1)"></a>';
	$searchPlaceholder = 'не работает';
	$planner_display = '<div class="quick_button_circle">
							<div class="quick_button_circle__circle" style="background-image: url(./skins/images/img_design/button_circle_2.png); border-color:red">
								<!--<div class="quick_button_circle__alert">12</div>-->
							</div>';
		if(isset($_GET['client_id'])){
			$planner_display .= '<div class="quick_button_circle__text"><a href="?page=client_folder&section=planner&client_id='.$_GET['client_id'].'">Планировщик</a></div>';
		}else{
			$planner_display .= '<div class="quick_button_circle__text"><a href="?page=planner">Планировщик</a></div>';
		}
	$planner_display .= '</div>'; 

?>