<?php
	/*
	Все методы которые заканчиваются на AJAX относятся к обработчикам событий AJAX
	Причем название метода состоит из строки отправленной в переменной $_POST['AJAX']
	и приставки _AJAX
	*/


    class Cabinet_general{
		// id юзера
		private $user_id;

		// меню
		public $menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked' => 'Не обработанные',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные СНАБ',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'Отказанные',
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'Предзаказ',
		'start' => 'Запуск',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'Приостановлен',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы',
		'otgrugen' => 'Отгруженные'													
		); 

		// допуски пользователя
		private $user_access;

		// права на редактирование поля определяются внутри 
		// некоторых функций 
		private $edit_admin =  ' contenteditable="true" class="edit_span"';
		private $edit_men = ' contenteditable="true" class="edit_span"';
		private $edit_snab = ' contenteditable="true" class="edit_span"';

				
		function __construct(){
			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);

			//$this->id_position = isset($_GET['id'])?$_GET['id']:0;
			
			
			// обработчик AJAX через ключ AJAX
			# если существует метод с названием из запроса AJAX - обращаемся к нему

			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}

			## роутер классов по уровню допуска
			## т.е. на каждый допуск свои шаблоны и классы,
			## при желании можно инклудить несколько классов и обращаться к их методам
			$this->__ROUTER_CLASS__();
		}

		private Function __ROUTER_CLASS__(){
			switch ($this->user_access) {
				case '1':
					break;

				case '2':
					break;

				case '3':
					break;

				case '4':
					break;

				case '5':
					break;

				case '6':
					break;

				case '8':
					include_once 'cabinet_snab_class.php';
					// создаём экземпляр класса
					$this->CALASS = new Cabinet_snab_class();
					// запускаем роутер шаблонов
					$this->CALASS->__subsection_router__();
					break;

				case '9':
					break;

				default:
					# code...
					break;
			}
		}

		/////////////////  AJAX START ///////////////// 
		private function _AJAX_($name){
			$method_AJAX = $name.'_AJAX';

			// если в этом классе существует такой метод - выполняем его и выходим
			if(method_exists($this, $method_AJAX)){
				$this->$method_AJAX();
				exit;
			}		
			
		}
		/////////////////  AJAX METHODs  ///////////////// 

		//пример обработки AJAX запроса
		# выводит информацию из глобальных массивов и объекта текущего класса
		private function show_globals_arrays_AJAX(){
			echo '<strong>POST:</strong>';
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';

			echo '<strong>GET:</strong>';
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';

			echo '<strong>SESSION:</strong>';
			echo '<pre>';
			print_r($_SESSION);
			echo '</pre>';

			echo '<strong>Object Class:</strong>';
			echo '<pre>';
			print_r($this);
			echo '</pre>';
		}




		// запрашивает из базы допуски пользователя
		// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
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


   	}