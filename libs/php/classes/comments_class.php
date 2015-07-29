<?php
// класс комментариев к запросу
class Comments_for_query_class{
	// id пользователя
	private $user_id;

	function __construct(){
		$this->user_id = $_SESSION['access']['user_id'];
		//////////////////////////////////////////////////////////////////////////////
		//	обработчик AJAX через ключ _AJAX 										//
		//	если существует метод с названием из запроса AJAX - обращаемся к нему	//
		//////////////////////////////////////////////////////////////////////////////
		
		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}
	}

	static function check_the_empty_query_coment_Database($os__rt_list_query_num){
		global $mysqli;
		$query = "SELECT count(*) AS `count` FROM `".RT_LIST_COMMENTS."` WHERE `query_num` = '".$os__rt_list_query_num."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		$count = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$count = $row['count'];
			}
		}
		if($count>0){
			return 'no_empty';
		}else{
			return '';
		}
	}

	
	##################################################
	####### 	ВЫЗОВ ФУНКЦИЙ AJAX start    	######
	##################################################

	private function _AJAX_($name){
		$method_AJAX = $name.'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}	
	}

	private function add_new_comment_for_query_AJAX(){
			$this->save_query_comment_Database();
			$html = '<div class="comment table">';
					$html .= '<div class="row">';
						$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$_POST['name'].'</div>';
						$html .= '<div class="create_time_message">'.date('d.m.Y H:i:s').'</div>';
						$html .= '</div>';
						$html .= '<div class="cell comment_text">';
						$html .= '<div class="create_time_message">'.$_POST['comment_text'].'</div>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';			
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}
	
	private function save_query_comment_Database(){
		global $mysqli;
		$query ="INSERT INTO `".RT_LIST_COMMENTS."` SET
	             `user_id` = '".(int)$_POST['id']."',
	             `query_num` = '".(int)$_POST['query_num']."',
	             `user_name` = '".$_POST['name']."',
	             `comment_text` = '".$_POST['comment_text']."',
	            `create_time` = NOW()";
			$result = $mysqli->query($query) or die($mysqli->error);	
		return  $mysqli->insert_id;
	}

	private function get_comment_for_query_AJAX(){
		$html = '';
		$html .= $this->get_comment_for_query();
		$html .= $this->get_the_comment_for_query_form();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';
	}

	// получаем все комментарии по запросу
	protected function get_comment_for_query(){
		global $mysqli;
		$html = '';	
		$comments = array();
		$query = "SELECT `".RT_LIST_COMMENTS."`.*, 
		DATE_FORMAT(`".RT_LIST_COMMENTS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		 FROM `".RT_LIST_COMMENTS."`  WHERE `query_num` = '".(int)$_POST['query_num']."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$comments[] = $row;
			}
		}		

		$html .= '<div class="add_new_comment">';
		foreach ($comments as $key => $value) {
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name">'.$value['user_name'].'</div>';
					$html .= '<div class="create_time_message">'.$value['create_time'].'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<div class="create_time_message">'.$value['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
		}
		return $html;
	}

	// собираем форму отправки нового комментария по запросу
	private function get_the_comment_for_query_form(){
		// подключаем класс менеджера
		include_once ('./libs/php/classes/manager_class.php');
		$user_name = Manager::get_apl_users(); 
		$this->user_name = (trim($user_name[$_SESSION['access']['user_id']]['name'])!='')?$user_name[$_SESSION['access']['user_id']]['name']:'';
		$this->user_name .= (trim($user_name[$_SESSION['access']['user_id']]['last_name'])!='')?' '.$user_name[$_SESSION['access']['user_id']]['last_name']:'';
		
		$html = '';
		$html .= '<form>';
		$html .= '<div class="comment table">';
			$html .= '<div class="row">';
				$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name" data-id="'.$_SESSION['access']['user_id'].'">'. $this->user_name .'</div>';
					
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<textarea name="comment_text"></textarea>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
			
			$html .= '<input name="name" type="hidden" value="'.$this->user_name .'"></input>';
			$html .= '<input name="AJAX" type="hidden" value="add_new_comment_for_query"></input>';
			$html .= '<input name="id" type="hidden" value="'.$this->user_id.'"></input>';
			$html .= '<input name="query_num" type="hidden" value="'.$_POST['query_num'].'"></input>';
			$html .= '<button id="add_new_comment_button">Отправить</button>';
		$html .= '</form>';
		$html .= '</div>';
		return $html;
	}

	##################################################
	#######     	ВЫЗОВ ФУНКЦИЙ AJAX end    	######
	##################################################
}

// класс комментариев к заказу
class Comments_for_order_class extends Comments_for_query_class{
	// id пользователя
	private $user_id;

	function __construct(){
		$this->user_id = $_SESSION['access']['user_id'];
		//////////////////////////////////////////////////////////////////////////////
		//	обработчик AJAX через ключ _AJAX 										//
		//	если существует метод с названием из запроса AJAX - обращаемся к нему	//
		//////////////////////////////////////////////////////////////////////////////
		
		## данные POST
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}
	}

	static function check_the_empty_order_coment_Database($os__cab_list_order_num){
		global $mysqli;
		$query = "SELECT count(*) AS `count` FROM `".CAB_LIST_COMMENTS."` WHERE `order_num` = '".$os__cab_list_order_num."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		$count = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$count = $row['count'];
			}
		}
		if($count>0){
			return 'no_empty';
		}else{
			return '';
		}
	}


	##################################################
	####### 	ВЫЗОВ ФУНКЦИЙ AJAX start    	######
	##################################################

	private function _AJAX_($name){
		$method_AJAX = $name.'_AJAX';
		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			$this->$method_AJAX();
			exit;
		}	
	}

	private function add_new_comment_for_order_AJAX(){
			$this->save_order_comment_Database();
			$html = '<div class="comment table">';
					$html .= '<div class="row">';
						$html .= '<div class="cell user_name_comments">';
						$html .= '<div class="user_name">'.$_POST['name'].'</div>';
						$html .= '<div class="create_time_message">'.date('d.m.Y H:i:s').'</div>';
						$html .= '</div>';
						$html .= '<div class="cell comment_text">';
						$html .= '<div class="create_time_message">'.$_POST['comment_text'].'</div>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';			
			echo '{"response":"OK","html":"'.base64_encode($html).'"}';
		}
	
	private function save_order_comment_Database(){
		global $mysqli;
		$query ="INSERT INTO `".CAB_LIST_COMMENTS."` SET
	             `user_id` = '".(int)$_POST['id']."',
	             `order_num` = '".(int)$_POST['order_num']."',
	             `user_name` = '".$_POST['name']."',
	             `comment_text` = '".$_POST['comment_text']."',
	            `create_time` = NOW()";
			$result = $mysqli->query($query) or die($mysqli->error);	
		return  $mysqli->insert_id;
	}


	private function get_comment_for_order_AJAX(){
		$html = $this->get_comment_for_order();
		$html .= $this->get_the_comment_for_order_form();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';		
	}

	private function get_comment_fo_query_without_form_AJAX(){
		$html = $this->get_comment_for_query();
		echo '{"response":"OK","html":"'.base64_encode($html).'"}';
	}

	// получаем все комментарии по заказу
	protected function get_comment_for_order(){
		global $mysqli;
		$html = '';	
		$html .= '<div class="add_new_comment">';
		$html .= '<div id="add_comments_of_query" data-query_num="'.$_POST['query_num'].'">переписка по запросу</div>';
		$html .= '</div>';
		$comments = array();
		$query = "SELECT `".CAB_LIST_COMMENTS."`.*, 
		DATE_FORMAT(`".CAB_LIST_COMMENTS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`
		 FROM `".CAB_LIST_COMMENTS."`  WHERE `order_num` = '".(int)$_POST['order_num']."'";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$comments[] = $row;
			}
		}
		

		$html .= '<div class="add_new_comment">';
		foreach ($comments as $key => $value) {
			$html .= '<div class="comment table">';
				$html .= '<div class="row">';
					$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name">'.$value['user_name'].'</div>';
					$html .= '<div class="create_time_message">'.$value['create_time'].'</div>';
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<div class="create_time_message">'.$value['comment_text'].'</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
		$html .= '</div>';
		}
		return $html;
	}

	// собираем форму отправки нового комментария по заказу
	private function get_the_comment_for_order_form(){
		// подключаем класс менеджера
		include_once ('./libs/php/classes/manager_class.php');
		$user_name = Manager::get_apl_users(); 
		$this->user_name = (trim($user_name[$_SESSION['access']['user_id']]['name'])!='')?$user_name[$_SESSION['access']['user_id']]['name']:'';
		$this->user_name .= (trim($user_name[$_SESSION['access']['user_id']]['last_name'])!='')?' '.$user_name[$_SESSION['access']['user_id']]['last_name']:'';


		$html = '';
		$html .= '<div class="add_new_comment">';
		$html .= '<form>';
		$html .= '<div class="comment table">';
			$html .= '<div class="row">';
				$html .= '<div class="cell user_name_comments">';
					$html .= '<div class="user_name" data-id="'.$_SESSION['access']['user_id'].'">'. $this->user_name .'</div>';
					
					$html .= '</div>';
					$html .= '<div class="cell comment_text">';
					$html .= '<textarea name="comment_text"></textarea>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';
			
			$html .= '<input name="name" type="hidden" value="'.$this->user_name .'"></input>';
			$html .= '<input name="AJAX" type="hidden" value="add_new_comment_for_order"></input>';
			$html .= '<input name="id" type="hidden" value="'.$this->user_id.'"></input>';
			$html .= '<input name="order_num" type="hidden" value="'.$_POST['order_num'].'"></input>';
			$html .= '<button id="add_new_comment_button">Отправить</button>';
		$html .= '</form>';
		$html .= '</div>';
		return $html;
	}


	##################################################
	#######     	ВЫЗОВ ФУНКЦИЙ AJAX end    	######
	##################################################
}



?>