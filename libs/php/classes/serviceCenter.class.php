<?php
	class ServiceCenter  extends aplStdAJAXMethod{

		function __construct(){
			
			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);
		


		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);		
		}
	}

		/**
		 *	возвращает окно
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:10 12.02.2016
		 */
		protected function get_service_center_AJAX(){
			
			$html = '<div id="js-service-center">';
				ob_start();
				// ROOT.'/libs/php/classes/rt_KpGallery.class.php';
				include_once ROOT.'/skins/tpl/client_folder/service_center/show.tpl';
				$html .= ob_get_contents();
				ob_get_clean();
			$html .= '</div>';

			$options['width'] = '100%';
			$options['height'] = '100%';
			$options['title'] = 'Центр услуг';
			$options['html'] = $html;
			$this->responseClass->addResponseFunction('show_SC',$options);	  
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

?>