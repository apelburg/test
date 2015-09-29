<?php
   
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['rt_position']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
    
    save_way_back(array('section=rt_position','section=planner'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	
	$forum = '';
	
	// комментарии
	include './libs/php/classes/comments_class.php';
	$comments = new Comments_for_query_class;
	
	// класс работы с базой
	include './libs/php/classes/db_class.php';
	// класс работы с формами
	include './libs/php/classes/os_form_class.php';

	// класс работы с поставщиками
	include './libs/php/classes/supplier_class.php';

	// главный класс по позициям
	include './libs/php/classes/rt_position_gen_class.php';
	// класс работы с позициями каталога
	include './libs/php/classes/rt_position_catalog_class.php';
	// класс работы с позициями не каталога
	include './libs/php/classes/rt_position_no_catalog_class.php';
	
	// класс работы с менеджерами
	include './libs/php/classes/manager_class.php';

	
	$id = (isset($_GET['id']))?$_GET['id']:'none';

	
	// чеерез get параметр id мы получаем id 1 из строк запроса
	// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
	$query = "SELECT `".RT_LIST."`.*,`".RT_LIST."`.`id` AS `RT_LIST_ID`, `".RT_MAIN_ROWS."`.*, DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`
	  FROM `".RT_MAIN_ROWS."`
	  INNER JOIN `".RT_LIST."`
	  ON `".RT_LIST."`.`query_num` = `".RT_MAIN_ROWS."`.`query_num`

	   WHERE `".RT_MAIN_ROWS."`.`id` = '".$id."'";
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	$Order = array();
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			// id записи 
			$order_num_id = $row['id']; 
			
			// номер запроса
			$order_num = $row['query_num']; 
			
			// название артикула, возможно отредактированное менеджером
			$art_name = $row['name']; 
			
			// id строки артикула в базе
			$art_id = $row['art_id']; 
			
			// type тип продукции
			$type_product = $row['type']; 

			// дата создания строки
			$order_num_date = $row['date_create']; 
			
			// тип товара : 
			// каталог /cat, 
			// полиграфия /pol, 
			// сувениры под заказ /ext, 
			// нанесение на чужом сувенире /не определено
			$type_tovar = $row['type']; 

			$snab_id = $row['snab_id'];

			// json no_cat
			$dop_info_no_cat = ($row['dop_info_no_cat']!='')?json_decode($row['dop_info_no_cat']):array();
			$Order = $row;
		}
	}


	// если тип продукции не определен
	if(!isset($type_product)){echo 'Тип продукции не определён.';exit;}

	switch ($type_product) {
		case 'cat'://каталог
			include 'controller_cat.php';
			$tpl_style = 'cat';
			break;
		
		default:
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
	}

	// echo 'asdsad';
	//шаблон forum
	if(isset($_GET['forum'])){
		ob_start();	
		
		include 'skins/tpl/client_folder/rt_position/forum.tpl';
		
		$forum = ob_get_contents();
		ob_get_clean();
	}
	
		

	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';
	
	// планка клиента
	include_once './libs/php/classes/client_class.php';
	Client::get_client__information($_GET['client_id']);
	
	// шаблон страницы
	include 'skins/tpl/client_folder/rt_position/show_'.$tpl_style.'.tpl';

	

	


	

	

	

	
	