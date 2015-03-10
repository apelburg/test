<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


////////////////////////////// AJAX ///////////////////////////////////////
//обрабатываем ajax запросы из стандартного окна ОС
	 if(isset($_POST['ajax_standart_window'])){

		if($_POST['ajax_standart_window']=="chenge_name_company"){
			$id = $_POST['id'];
			$tbl = $_POST['tbl'];
			$company = $_POST['company'];
			//тут обновляем название компании
			global $mysqli;
			$query = "UPDATE  `".constant($tbl)."` SET  `company` =  '".$company."' WHERE  `id` ='".$id."'; ";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные сохранены"
		      }';
			exit;
		}

		if($_POST['ajax_standart_window']=="get_adres"){
			$id_row = $_POST['id_row'];
			$tbl = "CLIENT_ADRES_TBL";
			$query = "SELECT * FROM ".constant($tbl)." WHERE `id` = '".$id_row."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr_adres = $row;
				}
			}
			extract($arr_adres, EXTR_PREFIX_SAME, "wddx");
			//получаем контент для окна 
			ob_start();
			include('./skins/tpl/clients/client_folder/client_card_table/edit_adres.tpl');
			$content = ob_get_contents();
			ob_get_clean();
			echo $content;
			exit;
		}
		if($_POST['ajax_standart_window']=="edit_adress_row"){
			global $mysqli;
			$query = "UPDATE  `".constant($_POST['tbl'])."` SET  
			`city` =  '".$_POST['city']."',
			`street` =  '".$_POST['street']."',
			`house_number` =  '".$_POST['house_number']."', 
			`korpus` =  '".$_POST['korpus']."',
			`office` =  '".$_POST['office']."',
			`liter` =  '".$_POST['liter']."', 
			`bilding` =  '".$_POST['bilding']."',
			`postal_code` =  '".$_POST['postal_code']."',
			`note` =  '".$_POST['note']."' WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные сохранены"
		      }';
			exit;
		}
		if($_POST['ajax_standart_window']=="delete_adress_row"){
			
			$id_row = $_POST['id_row'];
			$tbl = $_POST['tbl'];
			$query = "DELETE FROM ".constant($tbl)." WHERE `id`= '".$id_row."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      }';
			exit;
		}
		if($_POST['ajax_standart_window']=="add_new_adress_row"){
			$tbl = $_POST['tbl'];
			$query = "";
			$adres_type = (isset($_POST['adress_type']) && $_POST['adress_type']!="")?$_POST['adress_type']:'office';
			$query = "INSERT INTO `".constant($tbl)."` SET 
			`parent_id` = '".addslashes($_POST['parent_id'])."',
			`table_name` = '".addslashes($_POST['tbl'])."',
			`adress_type` = '".addslashes($adres_type)."',
			`city` = '".addslashes($_POST['city'])."',
			`street` = '".addslashes($_POST['street'])."',
			`house_number` = '".addslashes($_POST['house_number'])."',
			`korpus` = '".addslashes($_POST['korpus'])."',
			`office` = '".addslashes($_POST['office'])."',
			`liter` = '".addslashes($_POST['liter'])."',
			`bilding` = '".addslashes($_POST['bilding'])."',
			`postal_code` = '".addslashes($_POST['postal_code'])."',
			`note` = '".addslashes($_POST['note'])."'
			;";
			//echo "$query";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo $mysqli->insert_id;
			exit;
		}
		if($_POST['ajax_standart_window']=="new_adress_row"){
			ob_start();
			include('./skins/tpl/clients/client_folder/client_card_table/new_adres.tpl');
			$content = ob_get_contents();
			ob_get_clean();
			echo $content;
			exit;
		}
		if($_POST['ajax_standart_window']=="add_new_phone_row"){
			$query = "INSERT INTO `".CLIENT_CONT_FACES_CONTACT_INFO_TBL."` SET 
			`parent_id` ='".$_POST['client_id']."', 
			`table` = '".$_POST['parent_tbl']."', 
			`type` = 'phone', 
			`telephone_type` = '".$_POST['type_phone']."', 
			`contact` = '".$_POST['telephone']."',
			`dop_phone` = '".((trim($_POST['dop_phone'])!="" && is_numeric(trim($_POST['dop_phone'])))?trim($_POST['dop_phone']):'')."';";

			// echo "$query";exit;
			$result = $mysqli->query($query) or die($mysqli->error);
			echo $mysqli->insert_id;
			exit;
		}
		if($_POST['ajax_standart_window']=="add_new_other_row"){
			$query= "INSERT INTO `".CLIENT_CONT_FACES_CONTACT_INFO_TBL."` SET 			
			
			`parent_id` ='".$_POST['client_id']."', 
			`table` = '".$_POST['parent_tbl']."', 
			`type` = '".$_POST['type']."', 
			`telephone_type` = '', 
			`contact` = '".$_POST['input_text']."',
			`dop_phone` = '';";
			 // echo "$query";exit;
			$result = $mysqli->query($query) or die($mysqli->error);
			echo $mysqli->insert_id;			
			exit;
		}
		if($_POST['ajax_standart_window']=="delete_dop_cont_row"){
			$query = "DELETE FROM `".CLIENT_CONT_FACES_CONTACT_INFO_TBL."` WHERE `id` = '".$_POST['id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo "OK";
			exit;
		}
		if($_POST['ajax_standart_window']=="show_cont_face_in_json"){
			$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."` WHERE `id` = '".$_POST['id']."'";
			$arr = array();
			// echo $query;exit;
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}

			$my_json = json_encode($arr); 
			print $my_json; 
			exit;
		}

		if($_POST['ajax_standart_window']=="contact_face_edit_form"){
			global $mysqli;
			$query = "UPDATE  `".CLIENT_CONT_FACES_TBL."` SET  
			`surname` =  '".$_POST['surname']."',
			`last_name` =  '".$_POST['last_name']."',
			`name` =  '".$_POST['name']."', 
			`position` =  '".$_POST['position']."',
			`department` =  '".$_POST['department']."',
			`note` =  '".$_POST['note']."' WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные успешно обновлены"
		      }';
			exit;
		}

		if($_POST['ajax_standart_window']=="contact_face_new_form"){
			global $mysqli;
			$query = "INSERT INTO  `".CLIENT_CONT_FACES_TBL."` SET  
			`client_id` =  '".$_POST['parent_id']."',
			`surname` =  '".$_POST['surname']."',
			`last_name` =  '".$_POST['last_name']."',
			`name` =  '".$_POST['name']."', 
			`position` =  '".$_POST['position']."',
			`department` =  '".$_POST['department']."',
			`note` =  '".$_POST['note']."' ";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "id":"'.$mysqli->insert_id.'",
		       "text":"Данные успешно обновлены"
		      }';
			exit;
		}

		if($_POST['ajax_standart_window']=="edit_client_dop_information"){
			global $mysqli;
			$query = "UPDATE  `".CLIENTS_TBL."` SET  
			`dop_info` =  '".$_POST['dop_info']."',
			`ftp_folder` =  '".$_POST['ftp_folder']."' WHERE  `id` ='".$_POST['id']."';";
			$result = $mysqli->query($query) or die($mysqli->error);
			echo '{
		       "response":"1",
		       "text":"Данные успешно обновлены"
		      }';
			exit;
		}

		if($_POST['ajax_standart_window']=="delete_cont_face_row"){
			
			$id_row = $_POST['id'];
			$tbl = "CLIENT_CONT_FACES_TBL";
			$query = "DELETE FROM ".constant($tbl)." WHERE `id`= '".$id_row."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			// echo $query;
			echo '{
		       "response":"1",
		       "text":"Данные успешно удалены"
		      }';
			exit;
		}

		
		// CLIENT_CONT_FACES_CONTACT_INFO_TBL
		
	}
	/////////////////////////////////////  AJAX END /////////////////////////////////


$clientClass = new Client($client_id);

$cont_company_phone = $clientClass->cont_company_phone;
$cont_company_other = $clientClass->cont_company_other;

$client = $clientClass->info;


$contact_faces_contacts = Client::cont_faces($client_id);

$client_address = Client::get_addres($client_id);

$edit_show = (isset($_GET['client_edit']))?'admin_':'';

$adress_name_arr = array('office' => 'офиса', 'delivery' => 'доставки' );

//получаем текущий адрес клиента
ob_start();
foreach ($client_address as $adress_number => $adress) {
	include('./skins/tpl/clients/client_folder/client_card_table/client_adress_row.tpl');
}
$client_address_s .= ob_get_contents();
ob_get_clean();

//получаем информацию по клиенту
ob_start();
include('./skins/tpl/clients/client_folder/client_card_table/'.$edit_show.'client_table.tpl');
$client_content = ob_get_contents();
ob_get_clean();

// получаем кнопку
if(isset($_GET['client_edit'])){
	$quick_button = '';
}

//получаем информацию по контактным лицам данного клиента
ob_start();
$client_content_contact_faces = "";
$contact_face_d_arr = array();
foreach($contact_faces_contacts as $k=>$this_contact_face){
	//print_r($this_contact_face);
	$contact_face_d_arr = $clientClass->get_contact_info("CLIENT_CONT_FACES_TBL",$this_contact_face['id']);
	$cont_company_phone = (isset($contact_face_d_arr['phone']))?$contact_face_d_arr['phone']:''; 
	$cont_company_other = (isset($contact_face_d_arr['other']))?$contact_face_d_arr['other']:'';
	
	//echo $clientClass->$this->get_contact_info("CLIENTS_TBL",$id)($contact_face_d_arr, 'phone',Client::$array_img);
	include('./skins/tpl/clients/client_folder/client_card_table/'.$edit_show.'client_cotact_face_table.tpl');
}

$client_content_contact_faces .= ob_get_contents();
ob_get_clean();

// AJAX
// на случай выдачи контента только с контактными лицами
if(isset($_POST['ajax_standart_window']) && $_POST['ajax_standart_window']=="get_empty_cont_face"){
	echo $client_content_contact_faces;
	exit;
}
// ALAX END


//получаем адрес папки и примечания
ob_start();
include('./skins/tpl/clients/client_folder/client_card_table/'.$edit_show.'client_dop_info.tpl');
$client_content_dop_info = ob_get_contents();
ob_get_clean();

// получаем подготовленный контент для модальных окон
ob_start();
include('./skins/tpl/clients/client_folder/client_card_table/dialog_windows.tpl');
$dialog_windows = ob_get_contents();
ob_get_clean();

//выводим общий шаблон
include('./skins/tpl/clients/client_folder/client_card_table/show.tpl'); 
?>