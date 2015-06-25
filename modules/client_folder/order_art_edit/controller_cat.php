<?php
	/*******************************   AJAX   ***********************************/
	if(isset($_POST['global_change'])){
		if(isset($_POST['change_name']) && $_POST['change_name']=='size_in_var'){

			$query = "SELECT `tirage_json`,`print_z` FROM ".RT_DOP_DATA." WHERE `id` = '".$_POST['id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$json = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$json = $row['tirage_json'];
					$print_z = $row['print_z'];
				}
			}
			$arr_json = json_decode($json,true);

			$arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];

			/*
				ОБСУДИТЬ С АНДРЕЕМ РАСПРЕДЕЛЕНИЕ ТИРАЖА 
				ВВЕДЁННОГО В ОБЩЕЕ поле
			*/
			// $quantity = 0;
			// foreach ($arr_json as $key => $value) {
			// 	$quantity += $arr_json[$key]['tir'];
			// 	if($print_z){$quantity += $arr_json[$key]['dop'];}
			// }


			$query = "UPDATE `".RT_DOP_DATA."` SET `tirage_json` = '".json_encode($arr_json)."', `quantity` = '".$quantity."' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='size_in_var_all'){
			// echo "<pre>";
			// print_r($_POST);
			// echo "</pre>";

			$tir = $_POST['val']; // array / тиражи
			$key2 = $_POST['key']; // array / id _ row size
			$dop = $_POST['dop']; // array / запас
			$id = $_POST['id']; // array / id 

			//print_r($_POST['id']);exit;

			$query = "SELECT `tirage_json` FROM ".RT_DOP_DATA." WHERE `id` = '".$id[0]."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$json = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$json = $row['tirage_json'];
					//echo $row['tirage_json'];
				}
			}
			//echo $json;
			//$r = $json;
			$arr_json = json_decode($json,true);
			$sum_tir = 0;
			$sum_zap = 0;
			foreach ($key2 as $key => $value) {
				//echo $value;
				$arr_json[$value]['dop'] = $dop[$key];
				$arr_json[$value]['tir'] = $tir[$key];

				$sum_zap += $dop[$key];
				$sum_tir += $tir[$key];
			}

			// $arr_json[$_POST['key']][$_POST['dop']] = $_POST['val'];
			//echo $r .'   -   ';
			//echo json_encode($arr_json);
			$query = "UPDATE `".RT_DOP_DATA."` SET `quantity` = '".$sum_tir."',`zapas` = '".$sum_zap."',`tirage_json` = '".json_encode($arr_json)."' WHERE  `id` ='".$id[0]."'";	
			// // echo $query;			
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}
		if(isset($_POST['change_name']) && $_POST['change_name']=='change_status_row'){
			$color = $_POST['color'];
			$id_in = $_POST['id_in'];
			$query  = "UPDATE `".RT_DOP_DATA."` SET `row_status` = '".$color."' WHERE  `id` IN (".$id_in.");";
			echo $query;
			// echo '<pre>';
			// print_r($_POST);
			// echo '</pre>';
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"1","text":"test"}';
			exit;
		}
		// if(isset($_POST['change_name']) && $_POST['change_name']=='change_draft'){
		// 	$query  = "UPDATE `".RT_DOP_DATA."` SET `archiv` = '1' WHERE  `row_id` ='".$_POST['row_id']."' AND `id` NOT LIKE  '".$_POST['id']."';";
		// 	$query .= "UPDATE `".RT_DOP_DATA."` SET `draft` = '0' WHERE  `id` ='".$_POST['id']."';";
		// 	$result = $mysqli->multi_query($query) or die($mysqli->error);
		// 	echo '{"response":"1","text":"test"}';
		// 	exit;
		// }

		// извлекает текущую запись из архива
		if(isset($_POST['change_name']) && $_POST['change_name']=='change_archiv'){
			$query = "UPDATE `".RT_DOP_DATA."` SET `row_status` = 'green' WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->multi_query($query) or die($mysqli->error);
			// $result = $mysqli->query($query) or die($mysqli->error);
			echo '{"response":"1","text":"test"}';
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_tirage_pz'){
			$query = "UPDATE `".RT_DOP_DATA."` SET `print_z` = '".$_POST['pz']."' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_variante_shipping_time'){
			$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_time` = '".$_POST['time']."', `standart` = '' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='change_variante_shipping_date'){
			$date = $_POST['date'];
			$date = strtotime($date);
			$date = date("Y-m-d", $date);
			// exit;

			$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_date` = '".$date."' , `standart` = '' WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='save_standart_day'){
			$query = "UPDATE `".RT_DOP_DATA."` 
					SET `shipping_time` = '00:00:00',
					`shipping_date` = '0000-00-00' ,
					`standart` =  '".$_POST['standart']."'
					WHERE  `id` ='".$_POST['id']."'";	
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			exit;
		}

		if(isset($_POST['change_name']) && $_POST['change_name']=='new_variant'){
			// собираем запрос, копируем строку в БД
			$query = "INSERT INTO `".RT_DOP_DATA."` (row_id, row_status,quantity,price_in, price_out,discount,tirage_json) (SELECT row_id, row_status,quantity,price_in, price_out,discount,tirage_json FROM `".RT_DOP_DATA."` WHERE id = '".$_POST['id']."')";
			$result = $mysqli->query($query) or die($mysqli->error);
			// запоминаем новый id
			$insert_id = $mysqli->insert_id;
			// узнаем количество строк
			$query = "SELECT COUNT( * ) AS `num`
					FROM  `os__rt_dop_data` 
					WHERE  `row_id` ='1'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$num_rows = $row['num'];
				}
			}
			echo '{ "response":"1",
					"text":"test",
					"new_id":"'.$insert_id.'",
					"num_row":"'.($num_rows-1).'",
					"num_row_for_name":"Вариант '.$num_rows.'"
					}';
			exit;
		}
	}
	/*******************************  END AJAX  *********************************/

	// получаем все варианты просчёта по данному артикулу
	//$query = "SELECT `".RT_DOP_DATA."`.*,`".RT_ART_SIZE."`.`tirage_json`,`".RT_ART_SIZE."`.`id` AS `id_2` FROM `".RT_DOP_DATA."` INNER JOIN `".RT_ART_SIZE."` ON `".RT_ART_SIZE."`.`variant_id` = `".RT_DOP_DATA."`.`id` WHERE `".RT_DOP_DATA."`.`row_id` = '".$id."'";
	$query = "SELECT `".RT_DOP_DATA."`.*, DATE_FORMAT(shipping_date,'%d.%m.%Y') AS `shipping_date` FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$id."'";
	
	// echo $query;
	$result = $mysqli->query($query) or die($mysqli->error);
	// $this->info = 0;
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$variants[] = $row;
		}
	}

	if(!isset($type_product)){echo "Тип товара не определён,<br>или строка с id=".$_GET['id']." в таблице `".RT_DOP_DATA."` не существует ";exit;}


	

	$ARTICUL = new Articul();

	// получаем необходимые данные в переменные класса
	$ARTICUL->get_all_info($art_id);

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
	// print_r($images_data);
	// echo '</pre>';

	// вычисление вариантов данного артикула с другими цветами
	if($color_variants = $ARTICUL->get_art_color_variants($art)){
		$color_variants_block = $ARTICUL->color_variants_to_html2($color_variants);	
	}else{ $color_variants_block = '';}



	// варианты просчётов
	ob_start();		
	$dop_enable = array(0,0);
	foreach ($variants as $k => $v) {
		//if($v['draft']=='0' && $v['row_status']!='red'/*исключение на возможную ошибку в базе*/){$dop_enable[0] = 1;}			
		if($v['row_status']=="green"){$dop_enable[0] = 1;}		
		if($v['row_status']=="grey"){$dop_enable[1] = 1;}	
	}	
	$isset_green = $dop_enable[0];
	$isset_grey = $dop_enable[1];
	// echo '<pre>';
	// print_r($variants);
	// echo '</pre>';
	
	// print_r($dop_enable);

	$ch = 0;
	foreach ($variants as $key => $value) {
		// по умолчанию блоки скрыты
		$display_this_block = ' style="display:none"';
		// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
		if((!isset($_GET['show_archive']) && ($isset_green || $isset_grey)) && $value['row_status']=='red'){ continue;}

		// если это зона записи red, добавляем класс запрещающий редактирование данного блока
		if($value['row_status']=='red'){$show_archive_class = " archiv_opacity";}else{$show_archive_class ='';}

		///////// ВАРИАНТЫ СВЕТОФОР  ///////
		$var = $value['row_status'];
		switch ($var) {
			case 'green':// не история - рабочий вариант расчёта
				// может входить в КП
				if($ch < 1){$display_this_block=' style="display:block"';$ch++;}				
			break;
			
			case 'grey':// не история - вариант расчёта не учитывается в РТ
				// серый вариант расчёта не входит в КП	
				if (!$isset_green && $ch== 0){$display_this_block=' style="display:block"';$ch++;}
			break;						
			
			default: // вариант расчёта red (архив), остальное не важно
				if (!$isset_green && !$isset_grey && $ch== 0){$display_this_block=' style="display:block"';$ch++;}
			break;
		}
		
		$get_size_table = $ARTICUL->get_size_table($art_dop_params,$value);
		/* 
		старый вариант
		тут подсчитывается тираж и запас варианта расчета каталожной продукции 
		исходя из размерной сетки,
		позднее мы договорились, что при изменении тиража из РТ таблицы размеров обнуляются
		так что тираж берем из quantity и zapas
		$rr = json_decode($value['tirage_json'], true);
		$sum_tir = $sum_dop = 0;
		foreach ($rr as $k => $v) {
			$sum_tir += (isset($v['tir']))?(int)$v['tir']:0;
			$sum_dop += (isset($v['dop']))?(int)$v['dop']:0;
		}
		*/
		// тираж
		$sum_tir = $value['quantity'];
		$sum_dop = $value['zapas'];
		// сумма за тираж для нас
		$sum_of_tirage_in = round($value['price_in']*($sum_tir+$sum_dop),2);
		// сумма за тираж для клиента
		$sum_of_tirage_out = round($value['price_out']*($sum_tir+$sum_dop),2);
		// сумма прибыль
		$sum_prib_of_tirage = $sum_of_tirage_out-$sum_of_tirage_in;
		// печатаем / не печатаем запас
		$print_z = ($value['print_z']=='1')?'checked':'';
		$print_z_no = ($value['print_z']=='0')?'checked':'';

		$dop_uslugi = $ARTICUL->get_dop_uslugi_html($value['id'],($sum_tir+$sum_dop));
		// стандартное время изготовления
		// $std_pr = ($value['standart']=='10' && $type_tovar=='cat')?1:0;
		$std_time_print = ($value['standart']=='10' && $type_tovar=='cat')?'checked':'';

		
		include 'skins/tpl/client_folder/order_art_edit/variants_template.tpl';

	}
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	// шаблон страницы
	//include 'skins/tpl/client_folder/order_art_edit/show_cat.tpl';
	

	
?>