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

		// сохраняем контент по услуге
		if($this->POST['AJAX'] == 'save_edit_usluga'){
			echo $this->save_change_usluga();
			exit;
		}

	}

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
				//if($row['id']!=6){// исключаем нанесение apelburg
				// запрос на детей
				$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<div data-id="'.$row['id'].'" class="lili'.(($child=='')?' '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px">'.$row['name'].'<span class="button  usl_add">+</span><span class="button usl_del">X</span></div>'.$child;
				//}
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
		// тип услуги
		$html .= '<div class="name_input">Тип</div>';
		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['type'].'" name="type"></div>';
		// Цена входящая
		$html .= '<div class="name_input">Цена входащя</div>';
		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['price_in'].'" name="price_in"> руб.</div>';
		// Цена исходящая
		$html .= '<div class="name_input">Цена исходащая</div>';
		$html .= '<div class="edit_info"><input type="text" value="'.$usluga['price_out'].'" name="price_out"> руб.</div>';

		// Цена исходящая
		$html .= '<div class="name_input">Как считаем</div>';
		$html .= '<div class="edit_info"><input type="radio" id="for_how1" name="for_how" value="" '.(($usluga['for_how']=="")?'checked':'').'><label for="for_how1"><span class="icon_style folder">папка</span></label></div>';
		$html .= '<div class="edit_info"><input type="radio" id="for_how2" name="for_how" value="for_one" '.(($usluga['for_how']=="for_one")?'checked':'').'><label for="for_how2"><span class="icon_style for_one">на единицу товара</span></label></div>';
		$html .= '<div class="edit_info"><input type="radio" id="for_how3" name="for_how" value="for_all" '.(($usluga['for_how']=="for_all")?'checked':'').'><label for="for_how3"><span class="icon_style for_all">на тираж</span></label></div>';
		
		// Цена исходящая
		$html .= '<div class="name_input">Цена исходащая</div>';
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
	public function get_status_uslugi_Html($id,$uslugi_all_list=array()){
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
				$html.= '<div><input class="status_name" value="'.$row['name'].'"> <span class="button status_del"  data-id="'.$row['id'].'">X</span></div>';
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