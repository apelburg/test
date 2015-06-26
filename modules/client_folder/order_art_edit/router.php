<?php


	$forum = '';
	// класс работы с базой
	include './libs/php/classes/db_class.php';
	// класс работы с формами
	include './libs/php/classes/os_form_class.php';

	// класс работы с поставщиками
	include './libs/php/classes/supplier_class.php';

	// класс работы с позициями каталога
	include './libs/php/classes/articul_class.php';
	// класс работы с позициями не каталога
	include './libs/php/classes/position_no_ctalog_class.php';
	
	// класс работы с менеджерами
	include './libs/php/classes/manager_class.php';

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['order_art_edit']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	$id = (isset($_GET['id']))?$_GET['id']:'none';


	
	// чеерез get параметр id мы получаем id 1 из строк запроса
	// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
	$query = "SELECT DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`, `query_num`, `name`,`id`,`dop_info_no_cat`, `art_id`, `type` FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$id."'";
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
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

			// json no_cat
			$dop_info_no_cat = ($row['dop_info_no_cat']!='')?json_decode($row['dop_info_no_cat']):array();
		}
	}

	$query = "SELECT * FROM `".RT_LIST."` WHERE `query_num` = '".$order_num."';";
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	$snab_id = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$snab_id = $row['snab_id'];
		}
	}

	

	




	// если тип продукции не определен
	if(!isset($type_product)){echo 'Тип продукции не определён.';exit;}

	switch ($type_product) {
		case 'cat'://каталог
			include 'controller_cat.php';
			$tpl_style = 'cat';
			break;
		case 'pol':// полиграфия полиграфия листовая
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		case 'pol_many':// полиграфия многолистовая
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		case 'calendar':// календарь
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		case 'packing':// упаковка картон
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		case 'packing_other':// упаковка другая
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		case 'ext'://сувениры под заказ
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		case 'ext_cl'://сувениры клиента
			include 'controller_no_cat.php';
			$tpl_style = 'no_cat';
			break;
		
		default:
			echo 'Не известный тип продукции.';exit;
			break;
	}


	//шаблон forum
	if(isset($_GET['forum'])){
		ob_start();	
		
		include 'skins/tpl/client_folder/order_art_edit/forum.tpl';
		
		$forum = ob_get_contents();
		ob_get_clean();
	}


	// шаблон поиска
	include'./skins/tpl/common/quick_bar.tpl';
	// шаблон страницы
	include 'skins/tpl/client_folder/order_art_edit/show_'.$tpl_style.'.tpl';

	

	


	

	

	

	
	