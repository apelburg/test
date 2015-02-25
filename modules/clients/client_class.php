<?php
class Client {	
	static $array_img = array('email'=>'<img src="skins/images/img_design/social_icon1.png" >','skype' => '<img src="skins/images/img_design/social_icon2.png" >','isq' => '<img src="skins/images/img_design/social_icon3.png" >','twitter' => '<img src="skins/images/img_design/social_icon4.png" >','fb' => '<img src="skins/images/img_design/social_icon5.png" >',
'vk' => '<img src="skins/images/img_design/social_icon6.png" >','other' => '<img src="skins/images/img_design/social_icon7.png" >');
	
	public function __construct($id) {
		global $mysqli;
		
		//$this->id = $id;	
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				$this->name = $row['company'];
			}
		}

		//получаем телефоны, email, vk и т.д.	
		$arr = $this->get_contact_info("CLIENTS_TBL",$id);
		$this->cont_company_phone = (isset($arr['phone']))?$arr['phone']:''; 
		$this->cont_company_other = (isset($arr['other']))?$arr['other']:'';
		
	}

	//вывод доп контактов в табличном виде
	static function get_contact_row($contact_company, $type,$array_dop_contacts_img){
		
		$contact_arr;
		// echo "<pre>";
		// print_r($contact_company);
		// echo "</pre>";

		$str = '<table>';
		if(isset($type) && $type == "phone"){
			$i=0;
			if(empty($contact_company)){return;}

			foreach($contact_company as $k=>$v){				
				if($v['type'] == $type){
					$str .= "<tr><td class='td_phone'>".$v['telephone_type']." ".$i."</td><td>".$v['contact']." ".$v['dop_phone']."</td></tr>";	
					$i++;
				}
			}
			$str .= "</table>";	
			// echo $str;
			return $str;
		}else{
			
			foreach($contact_company as $k=>$v){
			if(isset($array_dop_contacts_img[trim($v['type'])])){
				$icon = $array_dop_contacts_img[trim($v['type'])];
			}else{
				@$icon = $array_dop_contacts_img['other'];
			}
				if($v['type'] != 'phone'){
					$str .= "<tr><td class='td_icons'>".$icon."</td><td>".$v['contact']."</td></tr>";	
				}
			}
			$str .= "</table>";	
			return $str;
		
		}			
	}
	public function get_contact_info($tbl,$parent_id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."' AND `parent_id` = '".$parent_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$contact = array('phone'=>'','other'=>'');//инициализируем массив
		$contacts = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$contacts[] = $row;
			}
			$contact['phone'] = self::get_contact_row($contacts, 'phone',self::$array_img);
			$contact['other'] = self::get_contact_row($contacts, 'other',self::$array_img);
		}
		return $contact;

	}
	//получаем реквизиты компании
	static function requisites($id) {
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			return $array;
		}
		return $array;
	}
	//получаем данные о контактных дицах компании
	static function cont_faces($id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			//получаем телефоны, email, vk и т.д.	
			/*$arr = self::get_contact_info("CLIENT_CONT_FACES_TBL",$id);
			
			$array['phone'] = (isset($arr['phone']))?$arr['phone']:''; 
			$array['other'] = (isset($arr['other']))?$arr['other']:'';*/
			return $array;
		}
		
		return $array;
	}
	//получаем данные о прикреплённых менеджерах
	static function relate_managers($id){
		global $mysqli;
		$query = "SELECT * FROM `".MANAGERS_TBL."` INNER JOIN `".RELATE_CLIENT_MANAGER_TBL."` ON `".RELATE_CLIENT_MANAGER_TBL."`.`manager_id` = `".MANAGERS_TBL."`.`id` WHERE `".RELATE_CLIENT_MANAGER_TBL."`.`client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			return $array;
		}
		return $array;
	}	
	//удалить клиента и всё, что с ним связано
	static function delete($id){
		global $mysqli;
		//выполняем все запросы ипишем ОК
				
		//лица имеющих право подписи
		$query = "DELETE FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` WHERE `requisites_id` IN (SELECT id FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."');\n";	
		//контактные лица компании
		$query .= "DELETE FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".(int)$id."';\n";	
		//прикреплённые менеджеры		
		$query .= "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//реквизиты относящиеся к данному клиенту
		$query .= "DELETE FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//договора
		$query .= "DELETE FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//спецификации
		$query .= "DELETE FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//таблицы РТ
		$query .= "DELETE FROM `".CALCULATE_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//таблица заказов
		$query .= "DELETE FROM `".CLIENT_ORDERS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//КП
		$query .= "DELETE FROM `".COM_PRED_LIST."` WHERE `client_id` = '".(int)$id."';\n";
		//протокол добавления
		$query .= "DELETE FROM `".CALCULATE_TBL_PROTOCOL."` WHERE `client` = '".(int)$id."';\n";			
		//планы
		$query .= "DELETE FROM `".PLANNER."` WHERE `id` = '".(int)$id."';\n";
		//удаляем клинта из основной таблицы		
		$query .= "DELETE FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."';\n";	
		
		//прикреплённые менеджеры		
		$query .= "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$id."';";
		//return $query;
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		return "Клиент id ".$id." успешно удален.";		
	}
	
	//заводим клиента
	public function create($array_in){
		if(empty($array_in))return "не достаточно данных";
		global $mysqli;
		global $user_id;
		//return $user_id;	
		extract($array_in);
		$rate =(empty($rate))? 1 : $rate ;		
		$query ="INSERT INTO `".CLIENTS_TBL."` SET
					 `set_client_date` = CURRENT_DATE(),
		             `company` = '".$this->cor_data_for_SQL($company)."', `delivery_address` = '".$this->cor_data_for_SQL($delivery_address)."',
					 `email` = '".$this->cor_data_for_SQL($email)."', `phone` = '".$this->cor_data_for_SQL($phone)."',
					 `addres` = '".$this->cor_data_for_SQL($addres)."', `web_site` = '".$this->cor_data_for_SQL($web_site)."',
					 `dop_info` = '".$this->cor_data_for_SQL($dop_info)."', `rate` = '".$rate."'";
		 
	    $result = $mysqli->query($query) or die($mysqli->error);
		$client_id = $mysqli->insert_id;
		
		///////////////////////////////////////////////////
		// add new data in CLIENT_CONT_FACES_TBL
		///////////////////////////////////////////////////

		foreach($_POST['cont_faces_data'] as $cont_face){
			   $query = "INSERT INTO `".CLIENT_CONT_FACES_TBL."` 
		              SET 
					 `client_id` = '".$client_id."',
					 `set_main` = '".@$this->cor_data_for_SQL($cont_face['set_main'])."', 
					 `name` = '".$this->cor_data_for_SQL($cont_face['name'])."',
					 `position` = '".$this->cor_data_for_SQL($cont_face['position'])."',
					 `department` = '".$this->cor_data_for_SQL($cont_face['department'])."',
					 `email` = '".$this->cor_data_for_SQL($cont_face['email'])."',
					 `phone` = '".$this->cor_data_for_SQL($cont_face['phone'])."',
					 `isq_skype` = '".$this->cor_data_for_SQL($cont_face['isq_skype'])."'";
					 
		   $result = $mysqli->query($query) or die($mysqli->error);		   
		}
	
	    $query = "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` VALUES('','$client_id','$user_id')";
        $result = $mysqli->query($query) or die($mysqli->error);
		global $mail;
		//$headers = 'Cc : andrey@apelburg.ru';
		//$headers = 'Cc : '.implode(',',array('runman@mail.ru','slava@apelburg.ru'));
		$headers = ''; 
		$manager_info = $this->get_manager_info_by_id($user_id);		
		$message = 'В базу добавлен новый клиент:<br><b>'.$company.'</b><br>
				   поставщика добавил пользователь: '.$manager_info['nickname'].' / '.$manager_info['name'].' '.$manager_info['last_name'].'<br><br>
				   <a href="http://apelburg.ru/admin/order_manager/?page=clients&client_id='.$client_id.'&razdel=show_client_data">ссылка на карточку клиента</a>';
		/**/
		//$mail->sendMail(2,'apelburg.m7@gmail.com','Новый клиент',$message,$headers);		
		$mail->sendMail(2,'kapitonoval2012@gmail.com','Новый клиент',$message,$headers);		
		return $client_id;
	}
	
	//защита от sql инъекций
	private function cor_data_for_SQL($data){
	    if(is_int($data) || is_double($data)) return($data);
	    //return strtr($data,"1","2");
		$data = strip_tags($data,'<b><br><a>');
		return mysql_real_escape_string($data);
	}
	//Получение данных о Менеджере по id
	//возвращает одномерный массив
	private function get_manager_info_by_id($manager_id){
		global $mysqli;
		$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE `id` = '".$manager_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				return $row;
			}
			//return $array;
		}
		return $array;
	}
	
}