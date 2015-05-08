<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['suppliers']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	//10147
	// $art_id =  (isset($_GET['art_id']))?$_GET['art_id']:0;
	// $art_id = 32285;

	$id = (isset($_GET['id']))?$_GET['id']:0;



	$query = "SELECT DATE_FORMAT(date_create,'%d.%m.%Y %H:%i:%s') as `date_create`, `order_num`, `name`, `art_id` FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$id."'";
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$order_num = $row['order_num'];
			$art_name = $row['name'];
			$art_id = $row['art_id'];	
			$order_num_date = $row['date_create'];
		}
	}

	// получаем все варианты просчёта по данному артикулу
	$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$id."'";
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$variants[] = $row;
		}
	}



	// echo $order_num_date;

	// echo '<pre>';
	// print_r($arra);
	// echo '</pre>';



	

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
	
	// // echo $art_colors;
	// echo '<pre>';
	// print_r($ARTICUL->get_size_table($art_id));
	// echo '</pre>';

	// вычисление вариантов данного артикула с другими цветами
	if($color_variants = $ARTICUL->get_art_color_variants($art)){
		$color_variants_block = $ARTICUL->color_variants_to_html2($color_variants);	
	}else{ $color_variants_block = '';}
?>