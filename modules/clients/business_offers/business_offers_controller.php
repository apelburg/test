<?php
    
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/art_img_class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/com_pred_class.php");
	
	///////////////////////////////////////// AJAX ////////////////////////////////////////////////////
	
	if(isset($_GET['send_kp_by_mail'])){
	    //echo  $_GET['send_kp_by_mail'];
	    list($kp_id,$client_id,$manager_id) = json_decode($_GET['send_kp_by_mail']);
		//$kp_filename = Com_pred::prepare_send_mail($kp_id,$client_id,$manager_id);
		//$kp_filename = $_SERVER['DOCUMENT_ROOT'].$kp_filename;
        $kp_filename = ROOT.'/data/com_offers/1894apelburg_1894_2015_56_01.pdf';
		
		$tpl_name = ROOT.'/skins/tpl/clients/client_folder/business_offers/send_mail_window.tpl';
		$fd = fopen($tpl_name,'r');
		$main_window_tpl = fread($fd,filesize($tpl_name));
	    fclose($fd);

        // кодируем данные в формате HTML перед передачей в формате JSON
		$main_window_tpl = base64_encode($main_window_tpl); 
		echo '{
		       "kp_filename":"'.$kp_filename.'",
		       "client_mails":[{"person":"менеджер - Наталья","mail":"premier22@yandex.ru"},{"person":"директор - Елена","mail":"premier_22@yandex.ru"}],
			   "manager_mails":["andrey@apelburg.ru","andrey2@apelburg.ru"],
			   "main_window_tpl":"'.$main_window_tpl.'",
			   "message_tpls":{"":"","":""}
			  }';//
	    exit;
	}
	
	if(isset($_POST['send_kp_by_mail_final_step'])){
	    //var_dump(json_decode($_POST['send_kp_by_mail_final_step']));
		$mail_details =json_decode($_POST['send_kp_by_mail_final_step']);

        // вызываем класс выполняющий отправку сообщения
		include(ROOT."/libs/php/classes/mail_class.php");
		$mail = new Mail();
		$mail->add_bcc('box1@yandex.ru');
		$mail->add_cc('box2@yandex.ru');
		$mail->attach_file($mail_details->filename);
		echo $mail->send($mail_details->to,$mail_details->from,$mail_details->subject,$mail_details->message);
	    exit;
	}
	
	
	
	///////////////////////////////////////  END AJAX  /////////////////////////////////////////////////
	
	
	
	/////////////////////////////////////// Временно /////////////////////////////////////// 
	if(isset($_GET['save_in_pdf'])){
	     list($kp_id,$client_id,$manager_id)=(explode("|",$_GET['save_in_pdf']));
	     Com_pred::save_in_pdf($kp_id,$client_id,$manager_id);
	}
	if(isset($_GET['show_kp'])){
		 $kp_id = (int)$_GET['show_kp'];
		 $rows = Com_pred::create_list($client_id,$kp_id);
		 $detailed_view = Com_pred::open_in_tbl($_GET['show_kp']); 
		 //$detailed_view .= '<a href="?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'">open_in_blank</a>';
		 $detailed_view .= '<br><a href="?'.$_SERVER['QUERY_STRING'].'&save_in_pdf='.$kp_id.'|'.$client_id.'|'.$manager_id.'">сохранить на диск</a>';
		 $dont_show_rows = TRUE;
	}
	if(isset($_GET['show_old_kp'])){
	     
		 $rows = Com_pred::create_list($client_id,$_GET['show_old_kp']);
		 $dont_show_rows = TRUE;
		 $detailed_view = Com_pred::open_old_kp($_GET['show_old_kp']);
		 
	}
	if(isset($_GET['show_kp_in_blank'])){
	     $kp_id = (int)$_GET['show_kp_in_blank'];
		 $rows = Com_pred::create_list($client_id,$kp_id);
		 $dont_show_rows = TRUE;
		 $detailed_view = Com_pred::open_in_blank($kp_id,$client_id,$manager_id,true);
		 //$detailed_view .= '<a href="?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'">open_in_blank</a>';
		 $detailed_view .= '<br><a href="?'.$_SERVER['QUERY_STRING'].'&save_in_pdf='.$kp_id.'|'.$client_id.'|'.$manager_id.'">сохранить на диск</a>';
	}
	/////////////////////////////////////// end Временно /////////////////////////////////////// 
	
	
	 if(isset($_GET['delete_com_offer'])){
	     if($_GET['old_version']) Com_pred::delete_old_version($_GET['delete_com_offer'],intval($_GET['client_id']),$_GET['id']/* must be string*/);
		 else Com_pred::delete($_GET['delete_com_offer']);
		 
		 header('Location:?'.addOrReplaceGetOnURL('client_id='.$client_id,'delete_com_offer&old_version&id'));
	 }
	
	
	// Собираем ряды для таблицы коммерческих предложений
	if(empty($dont_show_rows)) $rows = Com_pred::create_list($client_id);
	// Подключаем шаблон таблицы списка коммерческих предложений
	include ('skins/tpl/clients/client_folder/business_offers/list_table.tpl');
	if(!empty($detailed_view))	echo $detailed_view;
	
?>

