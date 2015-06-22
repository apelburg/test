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
		$html = '';
		$extended_info = '';// расширенная информация по каждому варианту
		$html .= "<table class='show_table'>";
			$html .= "<tr>
									<th></th>
									<th>варианты</th>
									<th>тираж</th>
									<th>$ входящая</th>
									<th>$ МИН исходящая</th>
									<th>подрядчик</th>
									<th>макет к</th>
									<th>срок р/д</th>
									<th>комментарий снабжения</th>
								</tr>";

			### выбираем все строки по каждуму статусу снабжения
			$n = 1;
			foreach ($variants_array as $key2 => $value2) {
				if ($status_snab==$value2['status_snab']) {
					$html .= "<tr data-id='".$value2['id']."'>
							<td><span>X</span></td>
							<td>".$n."</td>
							<td><span>".$value2['quantity']."</span>шт</td>
							<td><span>".$value2['price_in']."</span>р</td>
							<td><span>".$value2['price_out']."</span>р</td>
							<td class='change_supplier'>Антан</td>
							<td class='chenge_maket_date'></td>
							<td class='change_srok'>5</td>";
				
				//$html .= ($this->user_access == 1 || $this->user_access == 8 || $value2['extended_rights_for_manager']==1)?"	<td><input type='text' value='".$value2['snab_comment']."'></td>
				$html .= ($this->user_access == 1 || $this->user_access == 8 || $value2['extended_rights_for_manager']==1)?"	<td><div contenteditable='true' class='edit_snab_comment'> ".$value2['snab_comment']."</div></td>
						":"<td>".$value2['snab_comment']."</td>";
				$html .= "</tr>";
				$extended_info .= $this->get_extended_info_for_variant_Html($value2,$value2['id']);
				$n++;		
				}
			}
			
			$html .= "</table>";
			$html = $html.$extended_info;// прикрепляем расшириную инфу
			
		return $html;			
	}

	


	// возвращает расшириную информацию по варианту
	private function get_extended_info_for_variant_Html($arr,$id){
		// получаем услуги для данного варианта
		$uslugi = $this->get_uslugi_Database_Array($id);

		// расчёт стоимостей услуг
		$uslugi_arr = $this->calclate_summ_uslug_arr($uslugi);
		

		$html = '';
		$this->FORM = new Forms($this->GET,$this->POST,$this->SESSION);


		$dop_info_no_cat = ($arr['no_cat_json']!='')?json_decode($arr['no_cat_json']):array();

		// проценты наценки по варианту
		$per = ($arr['price_in']!= 0)?$arr['price_in']:0.09;
		$percent = $this->get_percent_Int($arr['price_in'],$arr['price_out']);


		// формируем html c расширенной информацией по варианту
		$html .= '<div id="variant_info_'.$id.'" class="variant_info" style="display:none">';
		$html .= '<table><tr><td  style="vertical-align: baseline;">';
		$html .= '<table class="calkulate_table">
									<tbody><tr>
										<th>Стоимость товара</th>
										<th>$ вход.</th>
										<th>%</th>
										<th>$ исход.</th>
										<th>прибыль</th>
										<th class="edit_cell">ред.</th>
										<th class="del_cell">del</th>
									</tr>
									<tr class="tirage_and_price_for_one">
										<td>1 шт.</td>
										<td class="row_tirage_in_one price_in"><span>'.round(($arr['price_in']/$arr['quantity']),2).'</span> р.</td>
										<td rowspan="2" class="percent_nacenki">
											<span contenteditable="true" class="edit_span">'.$percent.'</span>%

										</td>
										<td class="row_price_out_one price_out"><span>'.round(($arr['price_out']/$arr['quantity']),2).'</span> р.</td>
										<td class="row_pribl_out_one pribl"><span>'.round((($arr['price_out']/$arr['quantity'])-($arr['price_in']/$arr['quantity'])),2).'</span> р.</td>
										<td rowspan="2">
											<!-- <span class="edit_row_variants"></span> -->
										</td>
										<td rowspan="2"></td>
									</tr>
									<tr class="tirage_and_price_for_all for_all">
										<td>тираж</td>
										<td class="row_tirage_in_gen price_in"><span  contenteditable="true" class="edit_span">'.$arr['price_in'].'</span> р.</td>
										<td class="row_price_out_gen price_out"><span  contenteditable="true" class="edit_span">'.$arr['price_out'].'</span> р.</td>
										<td class="row_pribl_out_gen pribl"><span>'.round(($arr['price_out']-$arr['price_in']),2).'</span> р.</td>
									</tr>
									
									
									'.$this->uslugi_template_Html($uslugi).'
							
									<tr>
										<th colspan="7" class="type_row_calc_tbl"><div class="add_usl">Добавить ещё услуги</div></th>
									</tr>
									<tr>
										<td colspan="7" class="table_spacer"> </td>
									</tr>
									<tr class="variant_calc_itogo">
										<td>ИТОГО:</td>
										<td><span>'.($uslugi_arr['summ_price_in']+$arr['price_in']).'</span> р.</td>
										<td><span>'.round((($percent+$uslugi_arr['summ_percent'])/$uslugi_arr['count_usl']),2).'</span> %</td>
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

	// вывод услуг
	private function uslugi_template_Html($arr){

		$html ='';
		if(!count($arr)){return $html;}
		// сохраняем id услуг
		$id_s = array();
		foreach ($arr as $key => $value) {
			$id_s[] = $value['uslugi_id'];
		}
		$id_s = implode(', ', $id_s);

		// делаем запрос по услугам  
		global $mysqli;
		$query = "SELECT `".OUR_USLUGI_LIST."`.`parent_id`,`".OUR_USLUGI_LIST."`.`for_how`,`".OUR_USLUGI_LIST."`.`id`,`".OUR_USLUGI_LIST."`.`name`,`".OUR_USLUGI_LIST."_par`.`name` AS 'parent_name' FROM ".OUR_USLUGI_LIST."
inner join `".OUR_USLUGI_LIST."` AS `".OUR_USLUGI_LIST."_par` ON `".OUR_USLUGI_LIST."`.`parent_id`=`".OUR_USLUGI_LIST."_par`.`id` WHERE `".OUR_USLUGI_LIST."`.`id` IN (".$id_s.")";
		// $query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id_s.")";
		
		$result = $mysqli->query($query) or die($mysqli->error);				
		$name_uslugi = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$name_uslugi[$row['id']]['name'] = $row['name'];
				$name_uslugi[$row['id']]['parent_name'] = $row['parent_name'];
			}
		}
		// echo '<pre>';
		// print_r($arr);
		// echo '</pre>';

		foreach ($arr as $key => $value) {
			$price_in = (($value['for_how']=="for_all")?$value['price_in']:($value['price_in']*$value['quantity']));
			$price_out = ($value['for_how']=="for_all")?$value['price_out']:$value['price_out']*$value['quantity'];
			$pribl = ($value['for_how']=="for_all")?($value['price_out']-$value['price_in']):($value['price_out']*$value['quantity']-$value['price_in']*$value['quantity']);

			$html .= '<tr>
						<th colspan="7">'.$name_uslugi[$value['uslugi_id']]['parent_name'].'</th>
					</tr>';
			$html .= '<tr class="tirage_and_price_for_all for_all">
										<td>'.$name_uslugi[$value['uslugi_id']]['name'].'</td>
										<td class="row_tirage_in_gen price_in"><span contenteditable="true" class="edit_span">'.$price_in.'</span> р.</td>
										<td class="row_tirage_in_gen price_in"><span contenteditable="true" class="edit_span">'.$this->get_percent_Int($value['price_in'],$value['price_out']).'</span> %.</td>
										<td class="row_price_out_gen price_out"><span contenteditable="true" class="edit_span">'.$price_out.'</span> р.</td>
										<td class="row_pribl_out_gen pribl"><span>'.$pribl.'</span> р.</td>
									</tr>';
		}
		return $html;
	}

	// подсчёт стоимотсти услуг для варианта
	private function calclate_summ_uslug_arr($uslugi){
		$uslugi_arr['summ_price_in'] = 0;
		$uslugi_arr['summ_price_out'] = 0;
		$uslugi_arr['summ_pribl'] = 0;
		$uslugi_arr['summ_percent'] = 0;
		$uslugi_arr['count_usl'] = 0.09;

		foreach ($uslugi as $key => $value) {
			if(trim($value['for_how'])!=''){
				$uslugi_arr['summ_price_in'] += ($value['for_how']=="for_all")?$value['price_in']:$value['price_in']*$value['quantity'];
				$uslugi_arr['summ_price_out'] += ($value['for_how']=="for_all")?$value['price_out']:$value['price_out']*$value['quantity'];
				$uslugi_arr['summ_pribl'] += ($value['for_how']=="for_all")?($value['price_out']-$value['price_in']):($value['price_out']*$value['quantity']-$value['price_in']*$value['quantity']);
				$uslugi_arr['summ_percent'] += $this->get_percent_Int($value['price_in'],$value['price_out']);
				$uslugi_arr['count_usl']++;
			}
		}
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
				if($row['id']!=6)// исключаем нанесение apelburg
				// запрос на детей
				$child = self::get_uslugi_list_Database_Html($row['id']);
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<li data-id="'.$row['id'].'" '.(($child=='')?'class="may_bee_checked"':'').'>'.$row['name'].' '.$child.'</li>';
			}
			$html.= '</ul>';
		}
		return $html;
	}

	// добавить доп услугу для варианта
	static function add_uslug_Database($id_uslugi,$dop_row_id,$quantity){
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

		
		$query ="INSERT INTO `".RT_DOP_USLUGI."` SET
		             `dop_row_id` = '".$dop_row_id."',
		             `uslugi_id` = '".$id_uslugi."',
					 `glob_type` = 'extra',
					 `price_in` = '".$usluga['price_in']."',
					 `price_out` = '".$usluga['price_out']."',
					 `for_how` = '".$usluga['for_how']."',
					 `quantity` = '".$quantity."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);	
		return 1;
	}


}