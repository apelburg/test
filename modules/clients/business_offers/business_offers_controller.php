<?php
    //Временно 
	if(isset($_GET['save_in_pdf'])){
	     list($kp_id,$client_id,$manager_id)=(explode("|",$_GET['save_in_pdf']));
	     Com_pred::save_in_pdf($kp_id,$client_id,$manager_id);
	}
	if(isset($_GET['show_kp'])){
	     Com_pred::open_in_tbl($_GET['show_kp']);
	}
	if(isset($_GET['show_old_kp'])){
	     Com_pred::open_old_kp($_GET['show_old_kp']);
	}
	if(isset($_GET['show_kp_in_blank'])){
	     echo Com_pred::open_in_blank($_GET['show_kp_in_blank'],$client_id,$manager_id,true);
	}
	
	///////////////////////////////////////// AJAX ////////////////////////////////////////////////////
	
	if(isset($_GET['send_kp_by_mail'])){
	    //echo  $_GET['send_kp_by_mail'];
	    list($kp_id,$client_id,$manager_id) = json_decode($_GET['send_kp_by_mail']);
		//$filename = Com_pred::prepare_send_mail($kp_id,$client_id,$manager_id);
		 //$filename = $_SERVER['DOCUMENT_ROOT'].$filename;
       $filename = $_SERVER['DOCUMENT_ROOT'].'/os/data/com_offers/1894apelburg_1894_2015_56_01.pdf';
		echo '{
		       "filename":"'.$filename.'",
		       "client_mails":[{"person":"менеджер - Наталья","mail":"premier22@yandex.ru"},{"person":"директор - Елена","mail":"premier_22@yandex.ru"}],
			   "manager_mails":["andrey@apelburg.ru","andrey2@apelburg.ru"],
			   "template":""
			  }';
	    exit;
	}
	
	if(isset($_POST['send_kp_by_mail_final_step'])){
	    //echo($_POST['send_kp_by_mail_final_step']);
	    var_dump(json_decode($_POST['send_kp_by_mail_final_step']));
		
		$mail_details =json_decode($_POST['send_kp_by_mail_final_step']);
		echo gettype($mail_details);
        // вызываем метод выполняющий отправку сообщения
		$mail = new Mail();
		$mail->add_bcc('box@yandex.ru');
		$mail->attach_file($mail_details->filename);
		$mail->send($mail_details->to,$mail_details->from,$mail_details->subject);
	    exit;
	}
	
	
	///////////////////////////////////////  END AJAX  /////////////////////////////////////////////////
	
	 if(isset($_GET['delete_com_offer'])){
	     if($_GET['old_version']) Com_pred::delete_old_version($_GET['delete_com_offer'],intval($_GET['client_id']),$_GET['id']/* must be string*/);
		 else Com_pred::delete($_GET['delete_com_offer']);
		 
		 header('Location:?'.addOrReplaceGetOnURL('client_id='.$client_id,'delete_com_offer&old_version&id'));
	 }
	
	
	// Собираем ряды для таблицы коммерческих предложений
	$rows = Com_pred::create_list($client_id);
	// Подключаем шаблон таблицы списка коммерческих предложений
	include ('skins/tpl/clients/client_folder/business_offers/list_table.tpl');
		
	
?>

