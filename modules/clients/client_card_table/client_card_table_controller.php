<?php 
$clientClass = new Client($client_id);

$cont_company_phone = $clientClass->cont_company_phone;
$cont_company_other = $clientClass->cont_company_other;

$client = $clientClass->info;


$contact_faces_contacts = Client::cont_faces($client_id);


$edit_show = (isset($_GET['client_edit']))?'admin_':'';

ob_start();
include('./skins/tpl/clients/client_folder/client_card_table/'.$edit_show.'client_table.tpl');
$client_content = ob_get_contents();
ob_get_clean();

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


include('./skins/tpl/clients/client_folder/client_card_table/show.tpl'); 
?>
