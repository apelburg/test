<?php
	
	class Cabinet_admin_class extends Cabinet{
		// разрешить показ сообщений
		// private $allow_messages = false;


		// словарь
		public $menu_name_arr = array(
			'important' => 'Важно',
			'in_processed'=>'обрабатывается',
			'no_worcked_snab' => 'Не обработанные СНАБ',		
			'no_worcked_men' => 'Не обработанные МЕН',
			'send_to_snab' => 'Отправлены в СНАБ',
			'calk_snab' => 'Рассчитанные',
			'ready_KP' => 'Выставлено КП',
			'denied' => 'ТЗ не корректно',
			'orders' => 'Заказы',
			'requests' =>'Запросы',
			'create_spec' => 'Документ создан',
			'signed' => 'Спецификация подписана',
			'expense' => 'Счёт выставлен',
			'requested_the_bill' => 'Счёт запрошен',
			'paperwork' => 'Предзаказ',
			'order_start' => 'Готовые к запуску',
			'tz_no_correct' => 'ТЗ не корректно',
			'purchase' => 'Закупка',
			'design' => 'Дизайн',
			
			'ready_for_shipment' => 'Готов к отгрузке',
			'paused' => 'на паузе',
			'history' => 'История',
			'simples' => 'Образцы',
			'closed'=>'Закрытые',
			'all' => 'Все',
			'issue'=>'Вопрос',
			'not accepted' => 'Не принято',
			'for_shipping' => 'На отгрузку',
			'my_orders_diz' => 'Мои заказы дизайн',
			'all_orders_diz' => 'Все заказы дизайн',
			'order_of_documents' => 'Заказ документов',
			'arrange_delivery' => 'Оформить доставку',
			'delivery' => 'Доставка',
			'pclosing_documents' => 'Закрывающие документы',
			'otgrugen' => 'Отгруженные',
			'already_shipped' => 'Отгруженные',
			'partially_shipped' => 'Частично',
			'fully_shipped' => 'Полностью',
			'partially_shipped' => 'Частично отгружен',
			'the_order_is_create' => 'Заказ сформирован',
			'payment_the_bill' => 'Счёт оплачен',	
			'refund_in_a_row' => 'Возврат средств',
			'cancelled' => 'Аннулированные',
			'all_the_bill' => 'Все документы',
			// запросы
			'in_work' => 'В работе',
			// заказы
			'order_all' => 'Все заказы',
			'order_in_work_snab' => 'В работе',
			// 'order_start' => 'Запуск в работу (заказ)',
			// 'order_in_work' => 'Заказы в работе',
			'design_all' => 'Дизайн',
			'order_in_work' => 'В обработке',
			'production' => 'Производство',
			'stock_all' => 'Склад'								
		); 

		// protected $user_id;
		// protected $user_access;

		// название подраздела кабинета
		// private $sub_subsection;

		protected $user_id;
		protected $user_access;


		// содержит экземпляр класса кабинета вер. 1.0
		// private $CABINET;

		

		function __construct($user_access = 0){ // необязательный параметр доступа... не передан - нет доступа =)) 

			$this->user_id = $_SESSION['access']['user_id'];
			$this->user_access = $user_access;

			}


		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			// $method_template = $_GET['section'].'_Template';
			//echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';

			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();				
			}else{
				// обработка ответа о неправильном адресе
				$this->response_to_the_wrong_address($method_template);	
			}
		}


		function __destruct(){}
}


?>