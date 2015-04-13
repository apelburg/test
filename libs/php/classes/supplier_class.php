<?php
// echo "hellow world<br>";
class Supplier{
	#################################
	###         СВОЙСТВА          ###
	#################################
	// содержит название и пути к изображениям для каждого типа контактных данных
		static $array_img = array('email'=>'<img src="skins/images/img_design/social_icon1.png" >','skype' => '<img src="skins/images/img_design/social_icon2.png" >','isq' => '<img src="skins/images/img_design/social_icon3.png" >','twitter' => '<img src="skins/images/img_design/social_icon4.png" >','fb' => '<img src="skins/images/img_design/social_icon5.png" >',
	'vk' => '<img src="skins/images/img_design/social_icon6.png" >','other' => '<img src="skins/images/img_design/social_icon7.png" >');

	#################################
	###          МЕТОДЫ           ###
	#################################

	######################################################
	###   НЕ УНИФИЦИРОВАННЫЕ (УЗКОНАПРАВЛЕННЫЕ) МЕТОДЫ ###
	######################################################
	public function __construct($id) {
		global $mysqli;		
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".SUPPLIERS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				$this->name = $row['nickName'];
			}
		}
		//получаем телефоны, email, vk и т.д.	
		$arr = $this->get_contact_info("SUPPLIERS_TBL",$id);
		$this->cont_company_phone = (isset($arr['phone']))?$arr['phone']:''; 
		$this->cont_company_other = (isset($arr['other']))?$arr['other']:'';
	}

	public function get_contact_info($tbl,$parent_id){
		global $mysqli;
		$query = "SELECT * FROM `".CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."' AND `parent_id` = '".$parent_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$contact = array('phone'=>'','other'=>'');//инициализируем массив
		$contacts = array();		
		$contact['phone'] = '<table class="table_phone_contact_information"></table>';
		$contact['other'] = '<table class="table_other_contact_information"></table>';
		
		//  
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$contacts[] = $row;
			}
			$contact['phone'] = self::get_contact_row($contacts, 'phone',self::$array_img);
			$contact['other'] = self::get_contact_row($contacts, 'other',self::$array_img);
		}
		return $contact;
	}

	static function get_activities($client_id){
		global $mysqli;
		$query = "
			SELECT  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` . * ,  `".SUPPLIERS_ACTIVITIES_TBL."`.`name` AS  `name` 
			FROM  `".SUPPLIERS_ACTIVITIES_TBL."` 
			INNER JOIN  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."` ON  `".SUPPLIERS_ACTIVITIES_TBL."`.`id` =  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."`.`activity_id` 
			WHERE  `".RELATE_SUPPLIERS_ACTIVITIES_TBL."`.`supplier_id` =  '".$client_id."'
		";
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
		
	}

	static function get_addres($id){
		global $mysqli;
		$query = "SELECT * FROM  `".CLIENT_ADRES_TBL."` WHERE `parent_id` = '".(int)$id."'";
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}
	static function get_contact_row($contact_company, $type,$array_dop_contacts_img){
		
		if(isset($type) && $type == "phone"){
			$i=1;
			$str = '<table class="table_phone_contact_information">';
			if(empty($contact_company)){return;}

			foreach($contact_company as $k=>$v){				
				if($v['type'] == $type){
					$str .= "<tr><td class='td_phone'>".$v['telephone_type']." ".$i."</td><td><div  class='del_text' data-adress-id=".$v['id'].">".$v['contact'].((trim($v['dop_phone'])!=0)?" доп.".$v['dop_phone']:'')."</div></td></tr>";	
					$i++;
				}
			}
			$str .= "</table>";	
			// echo $str;
			return $str;
		}else{

			$str = '<table class="table_other_contact_information">';
			foreach($contact_company as $k=>$v){
			if(isset($array_dop_contacts_img[trim($v['type'])])){
				$icon = $array_dop_contacts_img[trim($v['type'])];
			}else{
				@$icon = $array_dop_contacts_img['other'];
			}
				if($v['type'] != 'phone'){
					$str .= "<tr><td class='td_icons'>".$icon."</td><td><div   class='del_text' data-adress-id=".$v['id'].">".$v['contact']."<div></td></tr>";	
				}
			}
			$str .= "</table>";	
			return $str;		
		}			
	}

	static function get_reiting($id,$rate){
		$arr[0] = array('5','0');
		$arr[1] = array('5','5');
		$arr[2] = array('5','10');
		$arr[3] = array('5','15');
		$arr[4] = array('5','20');
		$arr[5] = array('5','25');

		$r = '<div id="rate_1" data-id="'.$id.'">
			<input type="hidden" name="review_count" value="'.$arr[$rate]['0'].'" />
			<input type="hidden" name="review_rate" value="'.$arr[$rate]['1'].'" />
		</div>';
		return $r;
	}

	static function cont_faces($id){
		global $mysqli;
		$query = "SELECT * FROM `".SUPPLIERS_CONT_FACES_TBL."` WHERE `supplier_id` = '".(int)$id."'";
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

}
