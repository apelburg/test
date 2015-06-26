<?php


/*
Класс работы с некаталожной продукцией

в конце названий методов указан формат в котором выдаётся информация по окончании работы метода
Html, Array, String, Int
Либо:
Database - если метод предназначен только для работы с базой

PS было бы неплохо взять взять это за правило 



*/
class Position_no_catalog{
	// глобальные массивы
	private $POST;
	private $GET;
	private $SESSION;

	// тип продукта
	private $type_product;

	// id юзера
	private $user_id;

	// id позиции
	private $id_position;

	// класс форм
	private $FORM;

	// кнопки для различных групп пользователей
	private $buttons_top_command = array(
		'confirm_calculation' => array(
			'name' => 'Принять расчёт',
			'access' => '1,5'
			),
		'queryes_calculation' => array( // 
			'name' => 'Запросить расчёт',
			'access' => '1,5'
			),
		'queryes_recalculation' => array( // меняет статус у всех отмеченных и переводит варианты "на расчёт"
			'name' => 'Запросить пересчёт',
			'access' => '1,5'
			),
		'get_in_work' => array(// статус по каждому варианту позиции
			'name' => 'Принять в работу',
			'access' => '1,8'
			),
		'tz_is_not_correct' => array( // статус снабжения по позиции
			'name' => 'ТЗ не корректно',
			'access' => '1,8'
			),
		'to_set_pause' => array(// статус позиции или даже запроса
			'name' => 'Поставить на паузу',
			'access' => '1,5'
			)
		);

	private $status_snab = array(
		'on_calculation' => array(
			'name' => 'На расчёт'
			),
		'query_on_calculation' => array(
			'name' => 'Запроc на расчёт'
			),
		'in_calculation' => array(
			'name' => 'В расчёте'
			),
		'calculation_of_snab' => array(
			'name' => 'Расчёт от снабжения'
			)
		);

	function __construct($get,$post,$session){
		$this->GET = $get;
		$this->POST = $post;
		$this->SESSION = $session;
		$this->user_id = $session['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		$this->id_position = isset($this->GET['id'])?$this->GET['id']:0;
	}

	public function get_user_access_Database_Int($id){
		global $mysqli;
		$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$int = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$int = (int)$row['access'];
			}
		}
		//echo $query;
		return $int;
	}

	public function get_top_funcional_byttun_for_user_Html(){
		$html = '';
		// перебираем все возможные кнопки кнопки
		foreach ($this->buttons_top_command as $key => $value) {
			// получаем массив разрешёний данной кнопки
			$access = 0;
			foreach (explode(',', $value['access']) as $key2 => $value2) {
				if(trim($value2)==$this->user_access){
					$access = 1;
				}
			}

			if($access==1){
				$html .= '<li class="status_art_right_class"><div><span>'.$value['name'].'</span></div></li>';
			}
		}
		return $html;
	}

	

	public function edit_work_days_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `work_days` = '".$this->POST['work_days']."'
		             WHERE `id` =  '".$this->POST['id_dop_data']."';
		             ";
// echo $query.'    ';
		$result = $mysqli->query($query) or die($mysqli->error);
		
		echo '{"response":"OK","name":"edit_work_days"}';

	}

	public function edit_snab_comment_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `snab_comment` = '".$this->POST['note']."'
		             WHERE `id` =  '".$this->POST['id_dop_data']."';
		             ";
// echo $query.'    ';
		$result = $mysqli->query($query) or die($mysqli->error);
		
		echo '{"response":"OK","name":"edit_snab_comment_Database"}';

	}

	// выводит все варианты по группам, 
	// по сути является главной функцией вывода основного контента
	public function get_all_on_calculation_Html($type_product){
		//сохраняем тип продукта
		$this->type_product = $type_product;

		$variants_array = $this->get_all_variants_Database_Array();
		$variants_array_GROUP_status_snab = $this->get_all_variants_Group_Database_Array();
		
		$variants_group_menu_Html = '<div id="variants_name"><ul id="all_variants_menu_pol">';

		$html = '<div id="variant_of_snab">';

		### перебираем все статусы снабжения
		foreach ($variants_array_GROUP_status_snab as $key => $value) {

			# групируем по статусу в разные вкладки

			// получаем имя вкладки
			if(isset($this->status_snab[$value['status_snab']])){

				$name_group = ($value['status_snab']=='calculation_of_snab')?$this->status_snab[$value['status_snab']]['name'].' от '.$value['snab_end_work']:$this->status_snab[$value['status_snab']]['name'];
			
			}else{
				// на всякий случай на время тестирования выведем
				// вдруг найдутся варианты с неизвестными в классе статусами
				$name_group = "НЕОПОЗНАННЫЕ";
			}	


			// считаем количество вариантов во вкладке
			$number_variants = 0;
			foreach ($variants_array as $key2 => $value2) {
				if ($value['status_snab']==$value2['status_snab']) {
					$number_variants++;
				}
			}

			// добавляем вкладку в список
			$variants_group_menu_Html .= '<li data-cont_id="variant_content_table_'.$key.'" class="variant_name '.(($key==0)?'checked':'').'">'.$name_group.' ('.$number_variants.')</li>';
			
			// шаблон для вкладки "на расчет"
			$html .= '<div id="variant_content_table_'.$key.'" class="variant_content_table" '.(($key==0)?'style="display:block"':'style="display:none"').'>';
			
			// подгружаем таблицу со списком вариантов
			$html .= $this->get_variants_list_Html($variants_array,$value['status_snab']);	
			$html .= "</div>";		
			
			
		}

		$variants_group_menu_Html .= '</ul></div>';
		//echo $variants_group_menu_Html;
		return $variants_group_menu_Html.$html;
		
	}

	// возвращает таблицу со списком вариантов
	private function get_variants_list_Html($variants_array,$status_snab){
		// определяем редакторов для полей (html тегов)
		$edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$edit_admin.$edit_snab.$edit_men.'

		$html = '';
		$extended_info = '';// расширенная информация по каждому варианту
		$html .= "<table class='show_table'>";
			$html .= "<tr>
									<th></th>
									<th>варианты</th>
									<th>тираж</th>
									<th>$ входящая</th>
									<th>$ МИН исходящая</th>
									<th>$ исходящая</th>
									<th>подрядчик</th>
									<th>макет к</th>
									<th>срок р/д</th>
									<th>комментарий снабжения</th>
								</tr>";

			### выбираем все строки по каждуму статусу снабжения
			$n = 1;
			foreach ($variants_array as $key2 => $value2) {
				if ($status_snab==$value2['status_snab']) {
					// получаем услуги для данного варианта
					$uslugi = $this->get_uslugi_Database_Array($value2['id']);
					// расчёт стоимостей услуг
					$uslugi_arr = $this->calclate_summ_uslug_arr($uslugi);
					// получаем всю инфу по варианту
					$extended_info .= $this->get_extended_info_for_variant_Html($value2,$value2['id'],$uslugi,$uslugi_arr);



					$html .= "<tr data-id='".$value2['id']."'>
							<td><span class='traffic_lights_".$value2['row_status']."'><span></span></span></td>
							<td>".$n."</td>
							<td><span>".$value2['quantity']."</span> шт</td>
							<td><span>".($uslugi_arr['summ_price_in']+$value2['price_in'])."</span> р</td>
							<td style='color:red'><span>".($uslugi_arr['summ_price_out']+$value2['price_out_snab'])."</span> р</td>
							<td><span>".($uslugi_arr['summ_price_out']+$value2['price_out'])."</span> р</td>
							<td ".(($edit_snab!='' || $edit_admin!='')?"class='change_supplier'":"")." data-id='".$value2['suppliers_id']."'>".$value2['suppliers_name']."</td>
							<td class='chenge_maket_date'></td>
							<td><div ".(($edit_snab!='' || $edit_admin!='')?"class='change_srok'":"")." ".$edit_snab.$edit_admin.">".$value2['work_days']."</div></td>";
				
				//$html .= ($this->user_access == 1 || $this->user_access == 8 || $value2['extended_rights_for_manager']==1)?"	<td><input type='text' value='".$value2['snab_comment']."'></td>
				$html .= ($this->user_access == 1 || $this->user_access == 8 || $value2['extended_rights_for_manager']==1)?"	<td><div contenteditable='true' class='edit_snab_comment'> ".$value2['snab_comment']."</div></td>
						":"<td>".$value2['snab_comment']."</td>";
				$html .= "</tr>";
				
				$n++;		
				}
			}
			
			$html .= "</table>";
			$html = $html.$extended_info;// прикрепляем расшириную инфу
			
		return $html;			
	}

	// // возвращает список имён поставщиков для каждого варианта
	// private function get_suppliers_Database_String($id_s){
	// 	$suppliers_arr = Supplier::get_suppliers_Database($id_s);

	// 	$suppliers_name_arr = array();
	// 	foreach ($suppliers_arr as $key => $value) {
	// 		$suppliers_name_arr[] = $value['nickName'];
	// 	}
	// 	return implode(', ', $suppliers_name_arr);
	// }

	// редактируем информацию об поставщиках для некаталожного варианта расчёта
	public function change_supliers_info_dop_data_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `suppliers_id` = '".$this->POST['suppliers_id']."',
		             `suppliers_name` = '".$this->POST['suppliers_name']."' 
		             WHERE `id` =  '".$this->POST['dop_data_id']."';
		             ";
// echo $query.'    ';
		$result = $mysqli->query($query) or die($mysqli->error);
		
		echo '{"response":"OK","name":"chose_supplier_end"}';
	}

	// форматируем денежный формат + округляем
	private function round_money($num){
		return number_format(round($num, 2), 2, '.', '');
	}


	// возвращает расшириную информацию по варианту в Html
	private function get_extended_info_for_variant_Html($arr,$id,$uslugi,$uslugi_arr){
		// определяем редакторов для полей (html тегов)
		$edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$edit_admin.$edit_snab.$edit_men.'


		$html = '';
		$this->FORM = new Forms($this->GET,$this->POST,$this->SESSION);


		$dop_info_no_cat = ($arr['no_cat_json']!='')?json_decode($arr['no_cat_json']):array();

		// проценты наценки по варианту
		$per = ($arr['price_in']!= 0)?$arr['price_in']:0.09;
		$percent = $this->get_percent_Int($arr['price_in'],$arr['price_out']);


		// формируем html c расширенной информацией по варианту
		$html .= '<div id="variant_info_'.$id.'" class="variant_info" style="display:none">';
		$html .= '<table><tr><td  style="vertical-align: baseline;">';
		$html .= '<table class="calkulate_table" data-save_enabled="">
									<tbody><tr>
										<th>Стоимость товара</th>
										<th>$ вход.</th>
										<th>%</th>
										<th>$ МИН исход.</th>
										<th>$ исход.</th>
										<th>прибыль</th>
										<th class="edit_cell">ред.</th>
										<th class="del_cell">del</th>
									</tr>
									<tr class="tirage_and_price_for_one" data-dop_data_id="'.$arr['id'].'">
										<td>1 шт.</td>
										<td class="row_tirage_in_one price_in"><span '.$edit_admin.$edit_snab.'>'.$this->round_money($arr['price_in']/$arr['quantity']).'</span> р.</td>
										<td rowspan="2" class="percent_nacenki">
											<span '.$edit_admin.$edit_snab.$edit_men.'>'.$percent.'</span>%

										</td>
										<td class="row_price_out_one price_out_snab" style="color:red"><span '.$edit_admin.$edit_snab.'>'.$this->round_money(($arr['price_out_snab']/$arr['quantity'])).'</span> р.</td>
										<td class="row_price_out_one price_out_men"><span '.$edit_admin.$edit_men.'>'.$this->round_money($arr['price_out']/$arr['quantity']).'</span> р.</td>
										<td class="row_pribl_out_one pribl"><span>'.$this->round_money(($arr['price_out']/$arr['quantity'])-($arr['price_in']/$arr['quantity'])).'</span> р.</td>
										<td rowspan="2">
											<!-- <span class="edit_row_variants"></span> -->
										</td>
										<td rowspan="2"></td>
									</tr>
									<tr class="tirage_and_price_for_all for_all" data-dop_data_id="'.$arr['id'].'">
										<td>тираж</td>
										<td class="row_tirage_in_gen price_in"><span '.$edit_admin.$edit_snab.'>'.$arr['price_in'].'</span> р.</td>
										<td class="row_price_out_gen price_out_snab tirage" style="color:red"><span  '.$edit_admin.$edit_snab.'>'.$arr['price_out_snab'].'</span> р.</td>
										<td class="row_price_out_gen price_out_men tirage"><span  '.$edit_admin.$edit_men.'>'.$arr['price_out'].'</span> р.</td>
										<td class="row_pribl_out_gen pribl"><span>'.$this->round_money($arr['price_out']-$arr['price_in']).'</span> р.</td>
										
									</tr>
									
									
									'.$this->uslugi_template_Html($uslugi).'
							
									<tr>
										<th colspan="8" class="type_row_calc_tbl"><div class="add_usl">Добавить ещё услуги</div></th>
									</tr>
									<tr>
										<td colspan="8" class="table_spacer"> </td>
									</tr>
									<tr class="variant_calc_itogo">
										<td>ИТОГО:</td>
										<td><span>'.($uslugi_arr['summ_price_in']+$arr['price_in']).'</span> р.</td>
										<td><span>'.$this->round_money(($percent+$uslugi_arr['summ_percent'])/(1+$uslugi_arr['count_usl'])).'</span> %</td>
										<td><span>'.($uslugi_arr['summ_price_out']+$arr['price_out_snab']).'</span> р.</td>
										<td><span>'.($uslugi_arr['summ_price_out']+$arr['price_out']).'</span> р.</td>
										<td><span>'.($uslugi_arr['summ_price_out']+$arr['price_out']-$uslugi_arr['summ_price_in']-$arr['price_in']).'</span> р.</td>
										<td></td>
										<td></td>
									</tr>
								</tbody></table>
							';
		$html .= '</td><td style="display:none">'.$this->variant_no_cat_json_Html($dop_info_no_cat,$this->FORM/*экземпляр класса форм*/,$this->type_product).'</td></tr></table>';
		$html .= '</div>';

		
		return $html;
	}

	// ВЫВОДИТ СПИСОК УСЛУГ ПРИКРЕПЛЁННЫХ ДЛЯ ВАРИАНТА
	// $NO_show_head добавлен как необязательная переменная для отключения вывода 
	// названия группы услуги
	private function uslugi_template_Html($arr, $NO_show_head = 0){
		// определяем редакторов для полей (html тегов)
		$edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$edit_admin.$edit_snab.$edit_men.'

		$html ='';
		// если массив услуг пуст возвращаем пустое значение 
		if(!count($arr)){return $html;}
		
		// сохраняем id услуг
		$id_s = array();
		foreach ($arr as $key => $value) {
			$id_s[] = $value['uslugi_id'];
		}
		$id_s = implode(', ', $id_s);

		// делаем запрос по услугам  
		global $mysqli;
		$query = "SELECT `".OUR_USLUGI_LIST."`.`parent_id`,`".OUR_USLUGI_LIST."`.`price_out`,`".OUR_USLUGI_LIST."`.`for_how`,`".OUR_USLUGI_LIST."`.`id`,`".OUR_USLUGI_LIST."`.`name`,`".OUR_USLUGI_LIST."_par`.`name` AS 'parent_name' FROM ".OUR_USLUGI_LIST."
inner join `".OUR_USLUGI_LIST."` AS `".OUR_USLUGI_LIST."_par` ON `".OUR_USLUGI_LIST."`.`parent_id`=`".OUR_USLUGI_LIST."_par`.`id` WHERE `".OUR_USLUGI_LIST."`.`id` IN (".$id_s.") ORDER BY  `os__our_uslugi_par`.`name` ASC ";
		// $query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id_s.")";
		//echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);				
		$name_uslugi = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				foreach ($arr as $key => $value) {
						$name_uslugi[$row['id']] = $row;
				}
			}
		}

		$uslname = '';
		foreach ($name_uslugi as $key => $value) {
			// $NO_show_head добавлен как необязательная переменная для отключения вывода 
			// названия группы услуги

			if($uslname!=$value['parent_name'] && !$NO_show_head){
				$html .= '<tr  class="group_usl_name" data-usl_id="'.$value['parent_id'].'">
		 				<th colspan="8">'.$value['parent_name'].'</th>
 				</tr>';
 				$uslname = $value['parent_name'];
			}
			foreach ($arr as $key2 => $value2) {
				if($value2['uslugi_id']==$key){

					$price_in = (($value2['for_how']=="for_all")?$value2['price_in']:($value2['price_in']*$value2['quantity']));
					$price_out_men = ($value2['for_how']=="for_all")?$value2['price_out']:$value2['price_out']*$value2['quantity'];
					
					$pribl = ($value2['for_how']=="for_all")?($value2['price_out']-$value2['price_in']):($value2['price_out']*$value2['quantity']-$value2['price_in']*$value2['quantity']);
					$dop_inf = ($value2['for_how']=="for_one")?'(за тираж '.$value2['quantity'].' шт.)':'';
					
					$price_out_snab = ($value2['for_how']=="for_all")?$value2['price_out_snab']:$value2['price_out_snab']*$value2['quantity'];


					$real_price_out = ($value['for_how']=="for_all")?$value['price_out']:$value['price_out']*$value2['quantity'];

					$html .= '<tr class="calculate calculate_usl" data-dop_uslugi_id="'.$value2['id'].'" data-our_uslugi_id="'.$value['id'].'" data-our_uslugi_parent_id="'.trim($value['parent_id']).'">
										<td>'.$value['name'].' '.$dop_inf.'</td>
										<td class="row_tirage_in_gen uslugi_class price_in"><span>'.$this->round_money($price_in).'</span> р.</td>
										<td class="row_tirage_in_gen uslugi_class percent_usl"><span '.$edit_admin.$edit_snab.$edit_men.'>'.$this->get_percent_Int($value2['price_in'],$value2['price_out']).'</span> %</td>
										<td class="row_price_out_gen uslugi_class price_out_snab" style="color:red" data-real_min_price_for_one="'.$value['price_out'].'" data-real_min_price_for_all="'.$real_price_out.'"><span '.$edit_admin.$edit_snab.'>'.$this->round_money($price_out_snab).'</span> р.</td>
										<td class="row_price_out_gen uslugi_class price_out_men"><span '.$edit_admin.$edit_men.'>'.$this->round_money($price_out_men).'</span> р.</td>
										<td class="row_pribl_out_gen uslugi_class pribl"><span>'.$this->round_money($pribl).'</span> р.</td>
										<td class="usl_edit"><!-- <span class="edit_row_variants"></span> --></td>
										<td class="usl_del"><span class="del_row_variants"></span></td>
									</tr>';

				}
			}

		}

		return $html;
	}

	// подсчёт стоимотсти услуг для варианта
	private function calclate_summ_uslug_arr($uslugi){
		// echo '<pre>';
		// print_r($uslugi);
		// echo '</pre>';



		$uslugi_arr['summ_price_in'] = 0;
		$uslugi_arr['summ_price_out'] = 0;
		$uslugi_arr['summ_pribl'] = 0;
		$uslugi_arr['summ_percent'] = 0;
		$uslugi_arr['count_usl'] = 0;

		foreach ($uslugi as $key => $value) {
			if(trim($value['for_how'])!=''){
				//echo '$value[\'for_how\'] = '.$value['for_how'].'  ,  '.(($value['for_how']=="for_one")?$value['price_out']*$value['quantity']:$value['price_out']).'  - '.$uslugi_arr['summ_price_out'].'<br>';
				
				$uslugi_arr['summ_price_in'] += ($value['for_how']=="for_one")?$value['price_in']*$value['quantity']:$value['price_in'];
				$uslugi_arr['summ_price_out'] += ($value['for_how']=="for_one")?$value['price_out']*$value['quantity']:$value['price_out'];
				$uslugi_arr['summ_pribl'] += ($value['for_how']=="for_one")?($value['price_out']*$value['quantity']-$value['price_in']*$value['quantity']):($value['price_out']-$value['price_in']);
				$uslugi_arr['summ_percent'] += $this->get_percent_Int($value['price_in'],$value['price_out']);
				$uslugi_arr['count_usl']++;
			}
		}
		//echo $uslugi_arr['summ_price_out'].'   *   ';
		return $uslugi_arr;
	}

	// подсчёт процентов наценки
	private function get_percent_Int($price_in,$price_out){
		$per = ($price_in!= 0)?$price_in:0.09;
		$percent = round((($price_out-$price_in)*100/$per),2);
		return $percent;
	}

	// получаем услуги по варианту расчета
	private function get_uslugi_Database_Array($id){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	// получаем услугу по id   МОЖЕТ НЕ ПОНАДОБИТЬСЯ !!!!!!!!!!!!!!!!!!
	private function get_uslugi_of_id_Database_Array($id){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_USLUGI."` WHERE id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	// получаем все варианты
	private function get_all_variants_Database_Array(){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE row_id = '".$this->id_position."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	//получаем все уникальные по статусу варанты
	private function get_all_variants_Group_Database_Array(){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE row_id = '".$this->id_position."' GROUP BY `status_snab`";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;

	}

	// выводит общую информацию по ВАРИАНТУ из json
	public function variant_no_cat_json_Html($arr,$FORM/*экземпляр класса форм*/,$type_product){
		$html = '';

		// если у нас есть описание заявленного типа товара
		if(isset($FORM->form_type[$type_product])){
			$names = $FORM->form_type[$type_product]; // массив описания хранится в классе форм
			$html .= '<div class="table inform_for_variant">';
			foreach ($arr as $key => $value) {
				$html .= '
					<div class="row">
						<div class="cell">'.$names[$key]['name'].'</div>
						<div class="cell">';
				$html .= $value;
				$html .='</div>
					</div>
				';
			}
			$html .= '</div>';
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}

	}



	// выводит общую информацию по позиции из json, 
	// json был создан через класс форм заведения позициий
	public function dop_info_no_cat_Html($arr,$FORM/*экземпляр класса форм*/,$type_product){
		$html = '';

		// если у нас есть описание заявленного типа товара
		if(isset($FORM->form_type[$type_product])){
			$names = $FORM->form_type[$type_product]; // массив описания хранится в классе форм
			$html .= '<div class="table">';
			foreach ($arr as $key => $value) {
				$html .= '
					<div class="row">
						<div class="cell">'.$names[$key]['name'].'</div>
						<div class="cell">';
				$html .= implode(', ', $value);
				$html .='</div>
					</div>
				';
			}
			$html .= '</div>';
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}

	}


	static function get_uslugi_list_Database_Html($id=0){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			$html .= '<ul>';
			while($row = $result->fetch_assoc()){
				if($row['id']!=6){// исключаем нанесение apelburg
				// запрос на детей
				$child = self::get_uslugi_list_Database_Html($row['id']);
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<li data-id="'.$row['id'].'" '.(($child=='')?'class="may_bee_checked"':'').'>'.$row['name'].' '.$child.'</li>';
				}
			}
			$html.= '</ul>';
		}
		return $html;
	}

	//удаление услуги
	static function del_uslug_Database($uslugi_id){
		global $mysqli;
		$query = "DELETE FROM  `".RT_DOP_USLUGI."` WHERE  `id` = '".$uslugi_id."';
";
		echo $query; echo  '   ';
		$result = $mysqli->query($query) or die($mysqli->error);
	}

	// добавить доп услугу для варианта
	public function add_uslug_Database_Html($id_uslugi,$dop_row_id,$quantity){
		// определяем редакторов для полей (html тегов)
		$edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$edit_admin.$edit_snab.$edit_men.'


		global $mysqli;
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` = '".$id_uslugi."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$usluga = array();
		if($result->num_rows > 0){		
			while($row = $result->fetch_assoc()){
				$usluga = $row;
			}		
		}

		if(empty($usluga)){return 'такой услуги не существует';}




		// получаем флаг если этой услуги ещё нет и придётся формировать имя группы услуг
		$flag = $this->check_parent_exists_Database_Int($dop_row_id,$usluga['parent_id']);


		// вставляем новую услугу в базу
		$query ="INSERT INTO `".RT_DOP_USLUGI."` SET
		             `dop_row_id` = '".$dop_row_id."',
		             `uslugi_id` = '".$id_uslugi."',
					 `glob_type` = 'extra',
					 `price_in` = '".$usluga['price_in']."',
					 `price_out` = '".$usluga['price_out']."',
					 `price_out_snab` = '".$usluga['price_out']."',
					 `for_how` = '".$usluga['for_how']."',
					 `quantity` = '".$quantity."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);

		// формируем массив для генерации HTML выдачи для ajax
		// ajax примет html и добавить на страницу
		$NEW_usl[0]['id'] = $mysqli->insert_id;
		$NEW_usl[0]['dop_row_id'] = $dop_row_id;
		$NEW_usl[0]['uslugi_id'] = $id_uslugi;
		$NEW_usl[0]['glob_type'] = 'extra';
		$NEW_usl[0]['price_in'] = $usluga['price_in'];
		$NEW_usl[0]['price_out'] = $usluga['price_out'];
		$NEW_usl[0]['price_out_snab'] = $usluga['price_out'];
		$NEW_usl[0]['for_how'] = $usluga['for_how'];
		$NEW_usl[0]['quantity'] = $quantity;
		
		// генерим html выдачу для ajax
		$html = $this->uslugi_template_Html($NEW_usl, $flag);

		echo '{"response":"close_window","name":"add_uslugu","parent_id":"'.$usluga['parent_id'].'","html":"'.base64_encode($html).'"}';
	}

	public function change_dop_data_Database(){
		// $this->POST['parice_in'];
		// $this->POST['parice_out'];
		// $this->POST['parice_out_snab'];
		// $this->POST['dop_data_id'];

		global $mysqli;
		$query = "UPDATE `".RT_DOP_DATA."` SET 
		`price_in`='".$this->POST['price_in']."', 
		`price_out`='".$this->POST['price_out']."',
		`price_out_snab`='".$this->POST['price_out_snab']."' 

		WHERE `id`='".$this->POST['dop_data_id']."';
";
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"OK"}';

	}

	public function save_edit_price_dop_uslugi_Database(){
		global $mysqli;
		$query = '';
		foreach ($this->POST['data'] as $id => $value) {
			// $id
			// $value['price_out_snab'];
			// $value['price_out'];

			$query .= "UPDATE `".RT_DOP_USLUGI."` SET
			`price_out`='".$value['price_out']."',
			`price_out_snab`='".$value['price_out_snab']."' 
			WHERE `id`='".$id."';";
		}
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		echo '{"response":"OK"}';

	}

	private function check_parent_exists_Database_Int($dop_row_id, $parent_id){
		global $mysqli;
		
		
		$query = "SELECT `parent_id` FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (SELECT `uslugi_id` FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$dop_row_id."'
) AND `parent_id` = '".$parent_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows == 0){
			$ret = '0';
		}else{
			$ret = '1';
		}

		return $ret;		
	}

	public function get_suppliers_Database_Array(){
		global $mysqli;

	}
}