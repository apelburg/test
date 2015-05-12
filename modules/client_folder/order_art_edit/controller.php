<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['suppliers']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	/*******************************   AJAX   ***********************************/
	if(isset($_POST['global_change'])){
		if(isset($_POST['change_name']) && $_POST['change_name']=='size_in_var'){

			$query = "SELECT `tirage_json` FROM ".RT_DOP_DATA." WHERE `id` = '".$_POST['id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$json = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$json = $row['tirage_json'];
				}
			}
			$arr_json = json_decode($json,true);

			$arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];

			$query = "UPDATE `".RT_DOP_DATA."` SET `tirage_json` = '".json_encode($arr_json)."' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_draft'){
			$query = "UPDATE `".RT_DOP_DATA."` SET `draft` = '1' WHERE  `row_id` ='".$_POST['row_id']."';";
			$query .= "UPDATE `".RT_DOP_DATA."` SET `draft` = '0' WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->multi_query($query) or die($mysqli->error);
			echo "{'response':'1'}";
			exit;
		}
	}



	/*******************************  END AJAX  *********************************/


	//10147
	// $art_id =  (isset($_GET['art_id']))?$_GET['art_id']:0;
	// $art_id = 32285;

	$id = (isset($_GET['id']))?$_GET['id']:1;


	
	// чеерез get параметр id мы получаем id 1 из строк запроса
	// получаем основные хар-ки артикула из таблицы артикулов входящих в запрос
	$query = "SELECT DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`, `order_num`, `name`,`id`, `art_id` FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$id."'";
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$order_num_id = $row['id']; // id записи 
			$order_num = $row['order_num']; // номер запроса
			$art_name = $row['name']; // название артикула, возможно отредактированное менеджером
			$art_id = $row['art_id']; // id строки артикула в базе
			$order_num_date = $row['date_create']; // дата создания строки
		}
	}

	// получаем все варианты просчёта по данному артикулу
	//$query = "SELECT `".RT_DOP_DATA."`.*,`".RT_ART_SIZE."`.`tirage_json`,`".RT_ART_SIZE."`.`id` AS `id_2` FROM `".RT_DOP_DATA."` INNER JOIN `".RT_ART_SIZE."` ON `".RT_ART_SIZE."`.`variant_id` = `".RT_DOP_DATA."`.`id` WHERE `".RT_DOP_DATA."`.`row_id` = '".$id."'";
	$query = "SELECT `".RT_DOP_DATA."`.* FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$id."'";
	
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$variants[] = $row;
		}
	}


	$ARTICUL = new Articul($art_id);
	// основная информация по артикулу
	$articul = $ARTICUL->info;
	// акртикул
	$art = $articul['art'];
	//цвет
	$art_colors = implode(",", $ARTICUL->color);
	// материал
	$art_materials = implode(",", $ARTICUL->material);
	// вид печати
	$art_get_print_mode = implode(",", $ARTICUL->get_print_mode);
	// получаем изображения артикула
	$images_data = $ARTICUL->fetch_images_for_article2($art_id,'1');
	// получаем дополнительные параметры: размер, цену ...
	$art_dop_params = $ARTICUL->get_dop_params($art_id);
	
	

	// итак


	// // echo $art_colors;
	// echo '<pre>';
	// print_r($variants);
	// echo '</pre>';

	// вычисление вариантов данного артикула с другими цветами
	if($color_variants = $ARTICUL->get_art_color_variants($art)){
		$color_variants_block = $ARTICUL->color_variants_to_html2($color_variants);	
	}else{ $color_variants_block = '';}
?>