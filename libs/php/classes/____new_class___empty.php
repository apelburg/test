<?php
	class Class_Name{
		// глобальные массивы
		private $POST;
		private $GET;
		private $SESSION;

		// допуски пользователя
		private $user_access;


		// права на редактирование поля определяются внутри 
		// некоторых функций
		// переменные содержат строки типа:   contenteditable="true" class="edit_span"
		private $edit_admin;
		private $edit_men;
		private $edit_snab;

		// экземпляры классав по типу пользователей
		public $other_Class1;
		public $other_Class1;

		function __construct($get,$post,$session){
			// выделяем память под глобальные массивы внутри класса
			$this->GET = $get;
			$this->POST = $post;
			$this->SESSION = $session;

			// определяем id авторизированного пользователя
			$this->user_id = $session['access']['user_id'];

			//  			
			$this->user_access = $this->get_user_access_Database_Int($this->user_id);


			$this->id_position = isset($this->GET['id'])?$this->GET['id']:0;
			
			// экземпляр класса 
			$other_Class1 = new other_Class();

			// обработчик AJAX через ключ AJAX
			if(isset($this->POST['AJAX'])){
				$this->_AJAX_();
			}
		}
	}
?>