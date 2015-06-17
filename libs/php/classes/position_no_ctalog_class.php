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

	// id юзера
	private $user_id;

	// id позиции
	private $id_position;

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
	public function get_all_on_calculation_Html(){
		$variants_array = $this->get_all_variants_Database_Array();
		$variants_array_GROUP_status_snab = $this->get_all_variants_Group_Database_Array();
		
		$variants_group_menu_Html = '<div id="variants_name"><ul id="all_variants_menu_pol">';

		$html = '<div id="variant_of_snab">';

		### перебираем все статусы снабжения
		foreach ($variants_array_GROUP_status_snab as $key => $value) {
			// групируем по статусу в разные вкладки
			if(isset($this->status_snab[$value['status_snab']])){
				$name_group = ($value['status_snab']=='calculation_of_snab')?$this->status_snab[$value['status_snab']]['name'].' от '.$value['snab_end_work']:$this->status_snab[$value['status_snab']]['name'];
			}else{
				// на всякий случай на время тестирования выведем
				// вдруг найдутся варианты с неизвестными в классе статусами
				$name_group = "НЕОПОЗНАННЫЕ";
			}

			$variants_group_menu_Html .= '<li data-cont_id="variant_content_table_'.$key.'" class="variant_name '.(($key==0)?'checked':'').'">'.$name_group.'</li>';
			
			if($value['status_snab']=='on_calculation'){
				$html .= "<table id='variant_content_table_".$key."' ".(($key==0)?"class='show_table'":"").">";
				$html .= "<tr>
									<th></th>
									<th>варианты</th>
									<th>тираж</th>
									<th>$ входящая</th>
									<th>%</th>
									<th>$ МИН исходящая</th>
									<th>подрядчик</th>
									<th>срок р/д</th>
									<th>комментарий снабжения</th>
								</tr>";

				### выбираем все строки по каждуму статусу снабжения
				$n = 1;
				foreach ($variants_array as $key2 => $value2) {
					if ($value['status_snab']==$value2['status_snab']) {
						$html .= "<tr>
								<td><span>X</span></td>
								<td>".$n."</td>
								<td><span>".$value2['quantity']."</span>шт</td>
								<td><span>".$value2['price_in']."</span>р</td>
								<td><span></span>%</td>
								<td><span>".$value2['price_out']."</span>р</td>
								<td>Антан</td>
								<td>5</td>";
					
					$html .= ($this->user_access == 1 || $this->user_access == 8 || $value2['extended_rights_for_manager']==1)?"	<td><input type='text' value='".$value2['snab_comment']."'></td>
							":"<td>".$value2['snab_comment']."</td>";
					$html .= "</tr>";
					$n++;		
					}
				}
				$html .= "</table>";	
				// $html .= "</div>";		
			}else{
				$html .= '<div id="variant_content_table_'.$key.'" class="variant_name" '.(($key==0)?'style="display:block"':'').'>';
				$html .= '<div id="variants_name2">
						<table>
							<tr>
								<td>
									<ul id="all_variants_menu">
										<!-- вставка кнопок вариантов -->
										<li data-cont_id="variant_content_block_0" data-id="5" class="variant_name checked">Вариант 1<span class="variant_status_sv green"></span></li>		
									</ul>
								</td>
								
							</tr>
						</table>
						
					</div>';
				// $html .= '</div>';
			}
			$html .= '</div>';
			
		}
		$variants_group_menu_Html .= '</ul></div>';
		//echo $variants_group_menu_Html;
		return $variants_group_menu_Html.$html;
		
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


}