<?php

class Position_general_Class{
	// глобальные массивы
	private $POST;
	private $GET;
	private $SESSION;

	// тип продукта
	private $type_product;

	// 

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

	// статусы кнопки для различных групп пользователей 
	private $status_snab = array(

		'on_calculation' => array( //на расчёт мен
			'name' => 'На расчёт',
			'buttons' =>  array( // кнопки для данного статуса
				'on_calculation_snab' => array(// статус позиции или даже запроса
					'name' => 'Запросить расчёт',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '5'
					)
				)
			),


		'on_calculation_snab' => array( 
			'name' => 'Запрошен расчёт', // в снабжение
			'buttons' =>  array( // кнопки для данного статуса
				'in_calculation' => array(		
					'name' => 'Принять в работу',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					),
				'tz_is_not_correct' => array( // статус снабжения по позиции
					'name' => 'ТЗ не корректно',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					)
				)
			),

		'on_recalculation_snab' => array(
			'name' => 'На перерасчёт',
			'buttons' =>  array( // кнопки для данного статуса
				'in_calculation' => array(		
					'name' => 'Принять в работу',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					),
				'tz_is_not_correct' => array( // статус снабжения по позиции
					'name' => 'ТЗ не корректно',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					)
				)
			),

		'tz_is_not_correct' => array( // статус снабжения по позиции
			'name' => 'ТЗ не корректно',
			'buttons' =>  array( // кнопки для данного статуса
				'on_recalculation_snab' => array( // 
					'name' => 'Запросить расчёт',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '5'
					)
				)
			
			),
		'in_calculation' => array(
			'name' => 'В расчёте снабжение',
			'buttons' =>  array( // кнопки для данного статуса
				'calculate_is_ready' => array(
					'name' => 'Расчёт готов',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					),
				'create_text_mail_for_supplier' => array(
					'name' => 'Письмо поставщику',
					'class' => 'create_text_mail_for_supplier',
					'access' => '8' 
					)
				)
			),

		'calculate_is_ready' => array(
			'name' => 'Расчёт от снабжения',
			'buttons' =>  array( // кнопки для данного статуса
				'on_recalculation_snab' => array( // 
					'name' => 'Запросить перерасчёт',
					'access' => '5'
					)
				)
			),
		
		);


	function __construct($get,$post,$session){
		$this->GET = $get;
		$this->POST = $post;
		$this->SESSION = $session;
		$this->user_id = $session['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		$this->id_position = isset($this->GET['id'])?$this->GET['id']:0;
		
		// экземпляр класса продукции каталог
		$this->POSITION_CATALOG = new Position_catalog($this->GET,$this->POST,$this->SESSION,$this->user_access);

		// экземпляр класса продукции каталог
		$this->POSITION_NO_CATALOG = new Position_no_catalog($this->GET,$this->POST,$this->SESSION,$this->user_access);

		// экземпляр класса форм
		$this->FORM = new Forms($this->GET,$this->POST,$this->SESSION);

		// обработчик AJAX через ключ AJAX
		if(isset($this->POST['AJAX'])){
			$this->_AJAX_();
		}
	}

	private function get_user_access_Database_Int($id){
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

	# В данном классе расположены обработчики AJAX ОБЩИЕ для всей продукции !!!
	/////////////////  AJAX START ///////////////// 
	private function _AJAX_(){
		$method_AJAX = $this->POST['AJAX'].'_AJAX';

		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}		
		
	}
	/////////////////  AJAX METHODs  ///////////////// 


	// private function add_new_usluga_AJAX(){
	// 	// добавляет новую услугу
	// 	global $type_product;
	// 	// echo $type_product;
	// 	switch ($type_product) {
	// 		case 'cat':
	// 			$this->POSITION_CATALOG->add_uslug_Database_Html($_POST['id_uslugi'], $_POST['dop_row_id'], $_POST['quantity']);
	// 			break;			
	// 		default:
	// 			$this->POSITION_NO_CATALOG->add_uslug_Database_Html($_POST['id_uslugi'], $_POST['dop_row_id'], $_POST['quantity']);
	// 			break;
	// 	}
	// }

	private function save_tz_text_AJAX(){
		global $mysqli;
		$query = "UPDATE `".RT_DOP_USLUGI."` SET `tz`='".$this->POST['tz']."' WHERE `id`='".$this->POST['rt_dop_uslugi_id']."';
";
		$result = $mysqli->query($query) or die($mysqli->error);
		return '{"response":"OK"}';
	}
	// добавить доп услугу для варианта
	public function add_new_usluga_AJAX(){
		$id_uslugi = $this->POST['id_uslugi'];
		$dop_row_id = $this->POST['dop_row_id'];
		$quantity = $this->POST['quantity'];

		global $mysqli;
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` = '".$id_uslugi."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$usluga = array();
		if($result->num_rows > 0){		
			while($row = $result->fetch_assoc()){
				$usluga = $row;
			}		
		}

		// если массив услуг пуст
		if(empty($usluga)){return 'такой услуги не существует';}




		// получаем флаг если этой услуги ещё нет и придётся формировать имя группы услуг
		$flag = $this->POSITION_NO_CATALOG->check_parent_exists_Database_Int($dop_row_id,$usluga['parent_id']);


		// вставляем новую услугу в базу
		$query ="INSERT INTO `".RT_DOP_USLUGI."` SET
		             `dop_row_id` = '".$dop_row_id."',
		             `uslugi_id` = '".$id_uslugi."',
					 `glob_type` = 'extra',
					 `price_in` = '".$usluga['price_in']."',
					 `price_out` = '".$usluga['price_out']."',
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
		$NEW_usl[0]['quantity'] = $quantity;
		$NEW_usl[0]['creator_id'] = $this->user_id;
		
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

		echo '{"response":"close_window","name":"add_uslugu'.$dop.'","parent_id":"'.$usluga['parent_id'].'","html":"'.base64_encode($html).'"}';
	}

	private function get_uslugi_list_Database_Html_AJAX(){
		global $type_product;
		// получение формы выбора услуги
		if($_POST['AJAX']=="get_uslugi_list_Database_Html"){
			$html = '<form>';
			$html.= '<div class="lili lili_head"><span class="name_text">Название услуги</span><div class="echo_price_uslug"><span>$ вход.</span><span>$ исх.</span><span>за сколько</span></div></div>';
			$html .= $this->get_uslugi_list_Database_Html();
			$html .= '<input type="hidden" name="id_uslugi" value="">';
			$html .= '<input type="hidden" name="dop_row_id" value="">';
			$html .= '<input type="hidden" name="quantity" value="">';
			$html .= '<input type="hidden" name="type_product" value="'.$type_product.'">';
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga">';
			$html .= '</form>';
			echo $html;
		}
	}

	private function get_uslugi_list_Database_Html($id=0,$pad=30){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$price = '<div class="echo_price_uslug"><span></span><span></span></div>';
				if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
					# Это услуги НЕ из КАЛЬКУЛЯТОРА
					// запрос на детей
					$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
					
					$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					

					// присваиваем конечным услугам класс may_bee_checked
					$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}else{
					# Это услуги из КАЛЬКУЛЯТОРА
					// запрос на детей
					$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));

					$price = ($child =='')?'<div class="echo_price_uslug"><span>&nbsp;</span><span>&nbsp;</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					// присваиваем конечным услугам класс may_bee_checked
					$html.= '<div data-id="'.$row['id'].'" data-type="'.$row['type'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}
			}
		}
		return $html;
	}

	/////////////////   AJAX  END   ///////////////// 

}