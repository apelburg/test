<?php


// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **


include ('./libs/php/classes/rt_class.php');

// $RT = new RT;



// подсчет стоимости запроса
function get_gen_price_out($variable){
		return;
	}

// простой запрос
	$array_request = array();
	// $query = "
	// SELECT 
	// 	`os__rt_dop_data`.`id` AS `id_dop_data`,
	// 	`os__rt_dop_data`.`quantity`,	
	// 	`os__rt_dop_data`.`price_out`,	
	// 	DATE_FORMAT(`os__rt_main_rows`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
	// 	`os__client_list`.`company`,
	// 	`os__rt_main_rows`.*,
	// 	`os__rt_request_rows`.`id` AS `request_id` 
	// 	FROM `os__rt_main_rows` 
	// 	INNER JOIN `os__rt_dop_data` ON `os__rt_dop_data`.`row_id` = `os__rt_main_rows`.`id`
	// 	LEFT JOIN `os__client_list` ON `os__client_list`.`id` = `os__rt_main_rows`.`client_id`
	// 	LEFT JOIN `os__rt_request_rows` ON `os__rt_request_rows`.`id` = `os__rt_main_rows`.`order_num`
	// 	WHERE `os__rt_dop_data`.`row_status` NOT LIKE 'red'
	// 	ORDER BY `os__rt_main_rows`.`id` ASC
                
	// ";
	
	$query = "SELECT 
		`os__rt_list`.*, 
		DATE_FORMAT(`os__rt_list`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
		`os__client_list`.`company`,
		`os__manager_list`.`name`,
		`os__manager_list`.`last_name`,
		`os__manager_list`.`email` 
		FROM `os__rt_list`
		INNER JOIN `os__client_list` ON `os__client_list`.`id` = `os__rt_list`.`client_id`
		INNER JOIN `os__manager_list` ON `os__manager_list`.`id` = `os__rt_list`.`manager_id`";
	$result = $mysqli->query($query) or die($mysqli->error);
	$main_rows_id = array();
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$zapros[] = $row;
		}
	}

	
	// $main_rows_id = implode(',', $main_rows_id);
	// echo $main_rows_id;
	// echo '<pre>';
	// print_r($zapros);
	// echo '</pre>';

	// собираем html строк-запросов
	$html = '';
	foreach ($zapros as $key => $value) {
		// $company_name = $value[0]['company']; // название компании
		// $id_dop_data = $value[0]['id_dop_data']; // id варианта расчёта
		// $client_id = $value[0]['client_id']; // id клиента
		// $gen_create_date = $value[0]['gen_create_date']; // дата заведения запроса

		$html .= '
				<tr>
					<td class="cabinett_row_show show"><span></span></td>
					<td><a href="./?page=client_folder&query_num='.$value['query_num'].'">'.$value['query_num'].'</a></td>
					<td>'.$value['create_time'].'</td>
					<td>'.$value['company'].'</td>
					<td>'.RT::calcualte_query_summ($value['query_num']).'</td>
					<td></td>
					<td></td>
				</tr>
		';
		$html .= '<tr class="query_detail">';
		$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
		$html .= '<td colspan="6" class="each_art">';



		////////////////
		$html .= '</td>';
		$html .= '</tr>';
	}
	echo $html;
	// $content = $html;


	// // собираем массив запросов
	// foreach ($array_request as $key => $value) {	
	// 		// $zapros[$value['request_id']]['company'] = $value['company'];
	// 		$zapros[$value['request_id']][] = $value;
	// }

	
	// // собираем html строк-запросов
	// $html = '';
	// foreach ($zapros as $key => $value) {
	// 	$company_name = $value[0]['company']; // название компании
	// 	$id_dop_data = $value[0]['id_dop_data']; // id варианта расчёта
	// 	$client_id = $value[0]['client_id']; // id клиента
	// 	$gen_create_date = $value[0]['gen_create_date']; // дата заведения запроса

	// 	$html .= '
	// 			<tr>
	// 				<td class="cabinett_row_show show"><span></span></td>
	// 				<td><a href="./?page=client_folder&client_id='.$client_id.'&section=order_art_edit&id='.$id_dop_data.'">' .$CABINET->show_number_query($key).'</a></td>
	// 				<td>'.$gen_create_date.'</td>
	// 				<td>'.$company_name.'</td>
	// 				<td>'.get_gen_price_out($value).'</td>
	// 				<td>'.$CABINET->get_gen_status($value,'man').'</td>
	// 				<td>'.$CABINET->get_gen_status($value,'snab').'</td>
	// 			</tr>
	// 	';
	// 	$html .= '<tr class="query_detail">';
	// 	$html .= '<td class="show_hide"><span class="cabinett_row_hide"></span></td>';
	// 	$html .= '<td colspan="6" class="each_art">';

	// 	##################
	// 	# START ВАРИАНТЫ #
	// 	##################
	// 	// ВЫВОД ВАРИАНТОВ
	// 	$html .= '<table class="cab_position_div">';
		
	// 	// шапка таблицы вариантов запроса
	// 	$html .= '<tr>
	// 			<th>артикул</th>
	// 			<th>номенклатура</th>
	// 			<th>тираж</th>
	// 			<th>цены:</th>
	// 			<th>товар</th>
	// 			<th>печать</th>
	// 			<th>доп. услуги</th>
	// 		<th>в общем</th>
	// 		<th></th>
	// 		<th></th>
	// 			</tr>';

	// 	foreach ($value as $key1 => $val1) {
	// 		//ОБСЧЁТ ВАРИАНТОВ
	// 		// получаем массив стоимости нанесения и доп услуг для данного варианта 
	// 		$dop_usl = $CABINET -> get_dop_uslugi($val1['id_dop_data']);
	// 		// выборка только массива стоимости печати
	// 		$dop_usl_print = $CABINET->get_dop_uslugi_print_type($dop_usl);
	// 		// выборка только массива стоимости доп услуг
	// 		$dop_usl_no_print = $CABINET -> get_dop_uslugi_no_print_type($dop_usl);

	// 		// ВЫЧИСЛЯЕМ СТОИМОСТЬ ПЕЧАТИ И ДОП УСЛУГ ДЛЯ ВАРИАНТА ПРОСЧЁТА
	// 		// стоимость печати варианта
	// 		$calc_summ_dop_uslug = $CABINET -> calc_summ_dop_uslug($dop_usl_print,$val1['quantity']);
	// 		// стоимость доп услуг варианта
	// 		$calc_summ_dop_uslug2 = $CABINET -> calc_summ_dop_uslug($dop_usl_no_print,$val1['quantity']);
	// 		// стоимость товара для варианта
	// 		$price_out = $val1['price_out'] * $val1['quantity'];
	// 		// стоимость варианта на выходе
	// 		$in_out = $calc_summ_dop_uslug + $calc_summ_dop_uslug2 + $price_out;

	// 		$html .= '<tr>
	// 		<td>'.$val1['id_dop_data'].'|  '.$val1['art'].'</td>
	// 		<td>'.$val1['name'].'</td>
	// 		<td>'.$val1['quantity'].'</td>
	// 		<td></td>
	// 		<td>'.$price_out.'</td>
	// 		<td>'.$calc_summ_dop_uslug.'</td>
	// 		<td>'.$calc_summ_dop_uslug2.'</td>
	// 		<td>'.$in_out.'</td>
	// 		<td>'.$val1['status_man'].'</td>
	// 		<td>'.$val1['status_snab'].'</td>
	// 				</tr>';
	// 	}
	// 	$html .= '</table>';
	// 	################
	// 	# END ВАРИАНТЫ #
	// 	################

	// 	$html .= '</td>';
	// 	$html .= '</tr>';
	// }
	// echo $html;
	// $content = $html;

// echo "<pre>";
// print_r($zapros);
// echo "</pre>";



// echo 'раздел '.$_GET["section"].'<br>';
// echo 'подраздел '.$_GET["subsection"].'<br>';
// echo 'тестим текст в разделе ';