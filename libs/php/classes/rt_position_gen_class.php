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

	// НУЖНО ЛИ СЕЙЧАС ????  ЗАКОММЕНТИРОВАНО ДО ВЫЯСНЕНИЯ
	// private function to_chose_the_type_product_form_AJAX(){   
	// 	// форма выбора типа продукта
	// 	$this->FORM->to_chose_the_type_product_form_Html();	
	// }


	
	private function get_uslugi_list_Database_Html_AJAX(){
		// получение формы выбора услуги
		$html = '<form>';
		// $html .= POSITION_GEN->Position_no_catalog::get_uslugi_list_Database_Html();
		$html .= Position_catalog::get_uslugi_list_Database_Html_AJAX();
		$html .= '<input type="hidden" name="id_uslugi" value="">';
		$html .= '<input type="hidden" name="dop_row_id" value="">';
		$html .= '<input type="hidden" name="quantity" value="">';
		$html .= '<input type="hidden" name="AJAX" value="add_new_usluga">';
		$html .= '</form>';
		echo $html;
	}

	/////////////////   AJAX  END   ///////////////// 

}