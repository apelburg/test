<?php
	
	class Cabinet_snab_class{

		// подраздел раздела
		private $sub_subsection;


		function __construct(){
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; left:0">Cabinet_snab_class </div>';
			## данные POST
			if(isset($_POST['AJAX'])){
				$this->_AJAX_($_POST['AJAX']);
			}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
			if(isset($_GET['AJAX'])){
				$this->_AJAX_($_GET['AJAX']);
			}
		}


		private function _AJAX_($name){
			$method_AJAX = $name.'_AJAX';

			// если в этом классе существует такой метод - выполняем его и выходим
			if(method_exists($this, $method_AJAX)){
				$this->$method_AJAX();
				exit;
			}		
			
		}



		// стадратный метод для вывода шаблона
		public function __subsection_router__(){
			$method_template = $_GET['section'].'_Template';
			echo '<div id="fixed_div" style="position:fixed; background-color:#fff;padding:5px; bottom:0; right:0">метод '.$method_template.' </div>';
			// если в этом классе существует такой метод - выполняем его
			if(method_exists($this, $method_template)){
				$this->$method_template();
			}else{
				echo 'метод '.$method_template.' не предусмотрен';
			}
		}

		// МЕТОДЫ ДЛЯ ШАБЛОНОВ
		## Важно
		Private Function important_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}
		## Запросы
		Private Function requests_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}
		## Предзаказ
		Private Function paperwork_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}
		## Заказы
		Private Function orders_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}
		## На отгрузку
		Private Function for_shipping_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}
		## Закрытые
		Private Function closed_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}
		## Образцы
		Private Function simples_Template(){
			$message = 'important_Template';
			$html = '';
			/*other content template*/

			$html .= $message;
			return $html;
		}

		// function show_



		function __destruct(){}
	}


?>