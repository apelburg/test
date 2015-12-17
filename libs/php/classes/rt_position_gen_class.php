<?php

class Position_general_Class{
	// тип продукта
	private $type_product;

	// id юзера
	private $user_id;

	// допуски пользователя
	private $user_access;

	// права на редактирование поля определяются внутри 
	// некоторых функций 
	private $edit_admin;
	private $edit_men;
	private $edit_snab;

	// id позиции
	private $id_position;

	// экземпляр класса форм
	public $FORM;
	// экземпляр класса продукции каталог
	public $POSITION_CATALOG;
	// экземпляр класса продукции НЕ каталог
	public $POSITION_NO_CATALOG;

	
	function __construct(){
		$this->user_id = $_SESSION['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		$this->id_position = isset($_GET['id'])?$_GET['id']:0;
		
		// экземпляр класса продукции каталог
		$this->POSITION_CATALOG = new Position_catalog($this->user_access);

		// экземпляр класса продукции НЕ каталог
		$this->POSITION_NO_CATALOG = new Position_no_catalog($this->user_access);

		// экземпляр класса форм
		$this->FORM = new Forms();

		// обработчик AJAX через ключ AJAX
		if(isset($_POST['AJAX'])){
			$this->_AJAX_();
		}
	}


	# В данном классе расположены обработчики AJAX ОБЩИЕ для всей продукции !!!
	/////////////////  AJAX START ///////////////// 
	private function _AJAX_(){
		$method_AJAX = $_POST['AJAX'].'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}		
		
	}
	/////////////////  AJAX METHODs  ///////////////// 

	protected function save_tz_text_AJAX(){
		global $mysqli;
		$query = "UPDATE `".RT_DOP_USLUGI."` SET `tz`='".base64_encode($_POST['tz'])."' WHERE `id`='".$_POST['rt_dop_uslugi_id']."';
";
		$result = $mysqli->query($query) or die($mysqli->error);

		// echo '{"response":"OK" , "name":"save_tz_text_AJAX","increment_id":"'.$_POST['increment_id'].'"}';
		
		if(trim($_POST['tz'])==''){
			echo '{"response":"OK" , "name":"save_empty_tz_text_AJAX"'.(isset($_POST['increment_id'])?',"increment_id":"'.$_POST['increment_id'].'"':'').'}';
		}else{
			echo '{"response":"OK" , "name":"save_tz_text_AJAX"'.(isset($_POST['increment_id'])?',"increment_id":"'.$_POST['increment_id'].'"':'').'}';	
		}		
	}

	// редактирование темы в запросе
	protected function save_query_theme_AJAX(){
		global $mysqli;
		$query = "UPDATE `".RT_LIST."` SET";
		$query .= " `theme` = '".$_POST['value']."'";
		$query .= " WHERE id='".(int)$_POST['row_id']."'";
		$result = $mysqli->query($query) or die($mysqli->error);

		echo '{"response":"OK"}';
		// echo '{"response":"show_new_window","html":"'.base64_encode($query).'"}';	
	}


	// добавить доп услугу для варианта
	public function add_new_usluga_AJAX(){
		$id_uslugi = $_POST['id_uslugi'];
		$dop_row_id = $_POST['dop_row_id'];
		$quantity = $_POST['quantity'];
		$for_all = $_POST['for_all'];

		global $mysqli;
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` 
		WHERE `id` = '".$id_uslugi."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$usluga = array();
		if($result->num_rows > 0){		
			while($row = $result->fetch_assoc()){
				$usluga = $row;
			}		
		}

		// если массив услуг пуст
		if(empty($usluga)){return 'такой услуги не существует';}


		if(!isset($this->POSITION_NO_CATALOG)){
			$this->POSITION_NO_CATALOG = new Position_no_catalog($this->user_access);
		}


		// получаем флаг если этой услуги ещё нет и придётся формировать имя группы услуг
		$flag = $this->POSITION_NO_CATALOG->check_parent_exists_Database_Int($dop_row_id,$usluga['parent_id']);


		// вставляем новую услугу в базу
		$query ="INSERT INTO `".RT_DOP_USLUGI."` SET
		             `dop_row_id` = '".$dop_row_id."',
		             `uslugi_id` = '".$id_uslugi."',
					 `glob_type` = 'extra',
					 `price_in` = '".$usluga['price_in']."',
					 `price_out` = '".$usluga['price_out']."',					 
					 `performer` = '".$usluga['performer']."',
					 `price_out_snab` = '".$usluga['price_out']."',
					 `for_how` = '".$usluga['for_how']."',
					 `creator_id` = '". $this->user_id."',
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
		$NEW_usl[0]['performer'] = $usluga['performer'];
		$NEW_usl[0]['quantity'] = $quantity;
		$NEW_usl[0]['creator_id'] = $this->user_id;
		// $NEW_usl[0]['print_details'] = $usluga['print_details'];
		$NEW_usl[0]['tz'] = '';
		
		// генерим html выдачу для ajax
		global $type_product;
		// echo $type_product;
		switch ($type_product) {
			case 'cat':
				$dop = '_cat';
				$html = $this->POSITION_CATALOG->uslugi_template_cat_Html($NEW_usl, $flag);
				break;			
			default:

				$dop = '_no_cat';
				$html = $this->POSITION_NO_CATALOG->uslugi_template_Html($NEW_usl, $flag);
				break;
		}
		echo '{"response":"close_window","function":"window_reload","name":"add_uslugu'.$dop.'","parent_id":"'.$usluga['parent_id'].'","html":"'.base64_encode($html).'"}';
	}

	// сохранение информации по резерву
	protected function reserv_save_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_MAIN_ROWS."` 
					SET 
					`number_rezerv` =  '".base64_encode($_POST['value'])."'
					WHERE  `id` ='".$_POST['row_id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);

		echo '{"response":"OK"}';
	}

	protected function get_uslugi_list_Database_Html_AJAX(){
		// получение формы выбора услуги
		if($_POST['AJAX']=="get_uslugi_list_Database_Html"){
			$html = '<form>';
			$html.= '<div class="lili lili_head"><span class="name_text">Название услуги</span><div class="echo_price_uslug"><span>$ вход.</span><span>$ исх.</span><span>за сколько</span></div></div>';
			$html .= $this->get_uslugi_list_Database_Html();
			$html .= '<input type="hidden" name="for_all" value="'.$_POST['for_all'].'">';
			$html .= '<input type="hidden" name="id_uslugi" value="">';
			$html .= '<input type="hidden" name="dop_row_id" value="'.(isset($_POST['dop_row_id'])?$_POST['dop_row_id']:'').'">';
			$html .= '<input type="hidden" name="quantity" value="'.(isset($_POST['quantity'])?$_POST['quantity']:'').'">';
			$html .= '<input type="hidden" name="type_product" value="'.(isset($_POST['type_product'])?$_POST['type_product']:'').'">';
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga">';
			$html .= '</form>';
			
			echo $html;
		}
	}


	private function get_uslugi_list_Database_Html( $id=0, $pad=30){	

		global $mysqli; 
		$html = '';
		$apl_services = '';
		$supplier_services = '';
		$calc_services = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."' AND `deleted` = '0'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$price = '<div class="echo_price_uslug"><span></span><span></span></div>';
				if($row['id']==2){
					/**
					 *	услуги оутсорс 		
					 */
					$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
					
					$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					
					// присваиваем конечным услугам класс may_bee_checked
					$supplier_services.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}else if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
					/**
					 *	услуги АПЛ	
					 */
					$child = '';
					// if($row['parent_id']==0){
					// 	// кнопка калькулятора
					// 	$child .= '<div data-id="'.$row['id'].'" data-client_id="'.$_POST['client_id'].'"  data-client_id="'.$_POST['query_num'].'" data-type="'.$row['type'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">КАЛЬКУЛЯТОР</span></div>';
					// }
					$child .= $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
					
					
					$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					
					// присваиваем конечным услугам класс may_bee_checked
					$apl_services.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}else{

					// Это услуги из КАЛЬКУЛЯТОРА
					// запрос на детей
					// $child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));

					// $price = ($child =='')?'<div class="echo_price_uslug"><span>&nbsp;</span><span>&nbsp;</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					// // присваиваем конечным услугам класс may_bee_checked
					//$apl_services.= '<div data-id="'.$row['id'].'" data-type="'.$row['type'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}
			}
		}
		return $apl_services.$supplier_services;
	}

	// запрашивает из базы допуски пользователя
	// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
	protected function get_user_access_Database_Int($id){
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

	// отдаёт $html распечатанного массива
	public function print_arr($arr){
		ob_start();
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
		$content = ob_get_contents();
		ob_get_clean();
		
		return $content;
	}
	// форматируем денежный формат + округляем
	public function round_money($num){
		return number_format(round($num, 2), 2, '.', '');
	}
	// подсчёт процентов наценки
	public function get_percent_Int($price_in,$price_out){
		$per = ($price_in!= 0)?$price_in:0.09;
		$percent = round((($price_out-$price_in)*100/$per),2);
		return $percent;
	}

	/////////////////   AJAX  END   ///////////////// 

}