 <?php



 class Our_uslugi{
 	// глобальные массивы
	private $POST;
	private $GET;
	private $SESSION;

	// id юзера
	private $user_id;

	// допуски пользователя
	private $user_access;
	
	function __construct($get,$post,$session){
		$this->GET = $get;
		$this->POST = $post;
		$this->SESSION = $session;

		$this->user_id = $session['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		// обработчик AJAX
		if(isset($_POST['AJAX'])){
			$this->_AJAX_();
		}
	}


	/*
		обработка AJAX внутри класса работает быстреее!!!!
		скорее всего за счёт экономии памяти на ссылках на переменные
	*/

	// функции AJAX
	private function _AJAX_(){
		
		// получаем контент по услуге и статусам
		if($this->POST['AJAX'] == 'get_edit_content_for_usluga'){
			// блок редактирования цен, имени и типа услуги
			echo $this->get_chenge_form_uslugi_Html();
			// блок редактирования статусов
			echo $this->get_status_uslugi_Html($_POST['id']);
			exit;
		}

		// создаем новый статус в услуге
		if($this->POST['AJAX'] == 'add_new_status'){
			echo $this->add_new_status_Html();
			exit;
		}

		// сохраняем контент по услуге
		if($this->POST['AJAX'] == 'save_edit_usluga'){
			echo $this->save_change_usluga();
			exit;
		}

		// удаляем статус
		if($this->POST['AJAX'] == 'delete_status_uslugi'){
			echo $this->delete_status_uslugi_Database();
			exit;
		} 

		// редактируем статусы
		if($this->POST['AJAX'] == 'edit_name_status'){
			echo $this->edit_name_status_Database();
			exit;
		}

		// удаление услуги
		if($this->POST['AJAX'] == 'del_uslugu'){
			echo $this->del_uslugu_Database();
			exit;
		}

		// добавляем новую услугу
		if($this->POST['AJAX'] == 'add_new_usluga'){
			echo  $this->add_new_usluga_Database();
			exit;
		}

	}

	private function add_new_usluga_Database(){
		
		global $mysqli;

		// 1 - приваращаем родительскую услугу в папку
		// цены пока что оставляем
		$query = "UPDATE `".OUR_USLUGI_LIST."` SET 
			`for_how` = ''
		 WHERE `id`='".$this->POST['parent_id']."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);

		// если этота услуга принадлежит к разделу нанесения 
		if($this->POST['parent_id']==6){
			// - пишем пустую строку
			$for_how = '';
			// - выводим её с иконкой калькулятора
			$class = 'calc_icon';
			$buttons='';
		}else{
			$buttons = '<span class="button  usl_add">+</span>
			<span class="button usl_del">X</span>';
			$for_how = $class = 'for_all';
		}
		
		



		// 2 - добавляем услугу 
		$query ="INSERT INTO `".OUR_USLUGI_LIST."` SET
		             `parent_id` = '".$this->POST['parent_id']."',
		             `name` = 'Новая услуга',
		             `price_in` = '0.00',
		             `price_out` = '0.00',
		             `for_how` = '".$for_how."',
		             `type` = 'ЗАПОЛНИТЕ ПОЛЕ!!!' ";

		$result = $mysqli->multi_query($query) or die($mysqli->error);


		

		$html = '
		<div data-id="'.$mysqli->insert_id.'" data-parent_id="'.$this->POST['parent_id'].'" class="lili '.$class.'" style="padding-left:'.($this->POST['padding_left']+30).'px;background-position-x:'.($this->POST['bg_x']+30).'px" data-bg_x="'.($this->POST['bg_x']+30).'">
			<span class="name_text">Новая услуга</span>
			'.$buttons.'
		</div>';
		return $html;
	}

	private function del_uslugu_Database(){
		global $mysqli;
		$query = "DELETE FROM `".OUR_USLUGI_LIST."` WHERE `id`='".$this->POST['id']."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		$return_json = '{"response":"OK"}'; 
		return $return_json; 
	}

	private function edit_name_status_Database(){
		global $mysqli;

		$query = "UPDATE `".USLUGI_STATUS_LIST."` SET 
			`name` = '".$this->POST['name']."'
		 WHERE `id`='".$this->POST['id']."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		$return_json = '{"response":"OK"}'; 
		return $return_json; 
	}

	private function delete_status_uslugi_Database(){
		global $mysqli;

		$query = "DELETE FROM `".USLUGI_STATUS_LIST."` WHERE `id`='".$this->POST['id']."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		$return_json = '{"response":"OK"}'; 
		return $return_json; 
	}

	// добавление статуса к услуге
	private function add_new_status_Html(){
		global $mysqli;
		$query ="INSERT INTO `".USLUGI_STATUS_LIST."` SET
		             `parent_id` = '".$this->POST['id']."',
		             `name` = 'Новый статус для услуги'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);


		$html = '<input class="status_name" type="text" value="Новый статус для услуги"> <span class="button status_del" data-id="'.$mysqli->insert_id.'">X</span>';
		return $html;
	}

	// сохранение изменённых данных в услуге
	private function save_change_usluga(){
		global $mysqli;
		$id = $this->POST['id']; 
		unset($this->POST['id'],$this->POST['AJAX']);

		$query = "UPDATE `".OUR_USLUGI_LIST."` SET ";		
		$n=0;
		foreach ($this->POST as $key => $value) {
			$query .= ($n>0)?', ':'';
			$query .= "`".$key."` = '".$value."'";
			$n++;
		}

		$query .=" WHERE `id`='".$id."';";
		$result = $mysqli->query($query) or die($mysqli->error);
		// echo $query;
		// echo '<pre>';
		// print_r($this->POST);
		// echo '</pre>';
		echo '{"response":"OK","message":"Изменения успешно сохранены"}';


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

	// получаем список услуг в HTML для редактирования
	public function get_uslugi_list_Database_Html($id=0,$pad=30){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
					# Это услуги НЕ из КАЛЬКУЛЯТОРА
					// запрос на детей
					$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
					// присваиваем конечным услугам класс may_bee_checked
					$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span><span class="button  usl_add">+</span><span class="button usl_del">X</span></div>'.$child;
				}else{
					# Это услуги из КАЛЬКУЛЯТОРА
					// кнопки добавления калькуляторов
					$button_add = ($row['id']==6)?'<span class="button  usl_add">+</span>':'';
					// кнопки удаления калькуляторов
					$button_del = ($row['parent_id']==6)?'<span class="button usl_del calc_type">X</span>':'';

					$buttons = $button_del.$button_add;

					// запрос на детей
					$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
					// присваиваем конечным услугам класс may_bee_checked
					$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$buttons.'</div>'.$child;
				
				}
			}
		}
		return $html;
	}



	// получаем список услуг 
	public function get_ALL_uslugi_list_Database_Array(){	
		global $mysqli;
				
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."`";
		$result = $mysqli->query($query) or die($mysqli->error);
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[$row['id']] = $row;
			}
		}
		return $arr;
	}



	public function get_chenge_form_uslugi_Html(){
		$html = '<div id="edit_block_usluga">';
		$html .= '<form>';
		//массив редактируемых данных по услуге
		$usluga['parent_id'] = 'Родительский id';
		$usluga['name'] = 'Наименование';
		$usluga['price_in'] = 'Цена входящая';
		$usluga['price_out'] = 'Цена исходящая';
		$usluga['for_how'] = 'Kaк считать';
		$usluga['type'] = 'Тип (англ. без пробелов)';

		$radio_for_how[''] = 'none';
		$radio_for_how['for_all'] = 'Для всех';
		$radio_for_how['for_one'] = 'Для каждой ед. товара';


		// получаем полную информацию из базы по одной услуге
		$usluga = $this->get_usluga_Database_Array($this->POST['id']);




		// $html .= '<div></div>';
		// наименовнаие услуги
		$html .= '<div class="name_input">Наименование</div>';
		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['name'].'" name="name"></div>';
		if($this->POST['id'] != 6 && $this->POST['parent_id'] != 6){
			// тип услуги
			$html .= '<div class="name_input">Тип</div>';
			$html .= '<div class="edit_info"><input type="text" value="'.$usluga['type'].'" name="type"></div>';
			// Цена входящая
			$html .= '<div class="name_input">Цена входащя</div>';
			$html .= '<div class="edit_info"><input type="text" value="'.$usluga['price_in'].'" data-real="'.$usluga['price_in'].'" name="price_in"> руб.</div>';
			// Цена исходящая
			$html .= '<div class="name_input">Цена исходащая</div>';
			$html .= '<div class="edit_info"><input type="text" value="'.$usluga['price_out'].'" data-real="'.$usluga['price_out'].'" name="price_out"> руб.</div>';

			// Разрешить редактировать входящую цену
			$html .= '<div class="name_input">Разрешить редактировать входящую цену</div>';
			$html .= '<div class="edit_info"><input type="radio" id="edit_pr_in1" name="edit_pr_in" value="0" '.(($usluga['edit_pr_in']=="0")?'checked':'').'><label for="edit_pr_in1"><span>Запретить</span></label></div>';
			$html .= '<div class="edit_info"><input type="radio" id="edit_pr_in2" name="edit_pr_in" value="1" '.(($usluga['edit_pr_in']=="1")?'checked':'').'><label for="edit_pr_in2"><span>Разрешить</span></label></div>';
			

			// Как считаем
			$html .= '<div class="name_input">Как считаем</div>';
			$html .= '<div class="edit_info"><input type="radio" id="for_how1" name="for_how" value="" '.(($usluga['for_how']=="")?'checked':'').'><label for="for_how1"><span class="icon_style folder">папка</span></label></div>';
			$html .= '<div class="edit_info"><input type="radio" id="for_how2" name="for_how" value="for_one" '.(($usluga['for_how']=="for_one")?'checked':'').'><label for="for_how2"><span class="icon_style for_one">на единицу товара</span></label></div>';
			$html .= '<div class="edit_info"><input type="radio" id="for_how3" name="for_how" value="for_all" '.(($usluga['for_how']=="for_all")?'checked':'').'><label for="for_how3"><span class="icon_style for_all">на тираж</span></label></div>';
			
			// Цена исходящая
		$html .= '<div class="name_input">Шаблон ТЗ для менеджера</div>';
		$html .= '<div class="edit_info"><textarea name="tz">'.$usluga['tz'].'</textarea></div>';
		}
		// Цена исходящая
		$html .= '<div class="name_input">Описание услуги</div>';
		$html .= '<div class="edit_info"><textarea name="note">'.$usluga['note'].'</textarea></div>';
		// скрытое поле ID

		$html .= '<div class="edit_info"><input type="hidden" name="AJAX" value="save_edit_usluga"></div>';
		$html .= '<div class="edit_info"><input type="hidden" name="id" value="'.$usluga['id'].'"></div>';

		$html .= '</form>';
		$html .= '<div id="response_message"></div>';
		$html .= '<div id="hidden_button"><input type="button" id="save_usluga" value="Сохранить"></div>';
		$html .= '</div>';
		return $html;
	}


	// получаем выпадающий список статусов для услуги
	public function get_status_uslugi_Html($id,$uslugi_all_list = array()){
		//получаем полный список услуг
		if(empty($uslugi_all_list)){
			$uslugi_all_list = $this->get_ALL_uslugi_list_Database_Array();
		}
		// получаем id по которым будем выбирать статусы для услуги
		$id_s = implode(",",$this->get_id_parent_Database_Array($id,array()));
		global $mysqli;
		$html = '<div id="status_list">';
		$html .= '<strong>Список статусов по разделам:</strong><br>';
		$query = "SELECT * FROM `".USLUGI_STATUS_LIST."` WHERE `parent_id` IN (".$id_s.") ORDER BY `parent_id` ASC";
		// echo $query.'<br>';
		$result = $mysqli->query($query) or die($mysqli->error);
		$gname = '';
		if($result->num_rows > 0){		
			while($row = $result->fetch_assoc()){
				if($gname != $uslugi_all_list[$row['parent_id']]['name']){
					$gname = $uslugi_all_list[$row['parent_id']]['name'];
					// $html .= '<strong>'.$gname.'</strong>';
					$html .= '<div class="gname">'.$gname.'</div>';
				}
				
				// $is_checked = ($real_val==$row['name'])?'selected="selected"':'';
				// $html.= '<option value="'.$row['name'].'" '.$is_checked.'><!--'.$row['id'].' '.$row['parent_id'].'--> '.$row['name'].'</option>';
				$html.= '<div><input class="status_name" type="text" value="'.$row['name'].'"> <span class="button status_del"  data-id="'.$row['id'].'">X</span></div>';
			}
		
		}
		$html.= '</div>';
		$html.= '<div><input type="button" id="add_new_status" value="Добавить +"></div>';
		

		return $html;
	}

	// получаем полную информацию по услуге
	private function get_usluga_Database_Array($id){
		global $mysqli;

		$arr = array();
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){	
			while($row = $result->fetch_assoc()){				
				$arr = $row;				
			}	
		}
		return  $arr;
	}



	// получаем id родительских услуг 
	// private function get_id_parent_Database_Array($id,$arr){
	private function get_id_parent_Database_Array($id,$arr){
		global $mysqli;
		$arr[] = $id;
		$id = implode(",",$arr);

		$arr2 = array();
		$query = "SELECT `id`,`parent_id` FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id.")";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){	
			while($row = $result->fetch_assoc()){
				$arr2[] = $row['parent_id'];
				if($row['parent_id']!='0'){
					$arr2 = array_merge($arr2, $this->get_id_parent_Database_Array($row['parent_id'],$arr2));
				}
				}	
		}
		return  array_unique(array_merge ($arr, $arr2));
	}

}


?>