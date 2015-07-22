<?php
    //print_r($_POST);
	//echo '<br>';
	//echo $requisit_id;
    //print_r($form_data);
	//exit;
	$requisit_id = add_client_requisites($form_data);
	//$section = (isset($_GET['go_on']))? 'agreement_editor':'editing_client_requisites';

	header('Location:?'.addOrReplaceGetOnURL('section='.$_GET['agreement_type'].'_agr_setting&requisit_id='.$requisit_id));

?>
