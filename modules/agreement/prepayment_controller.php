<?php
    // print_r($_GET); exit;
	// При дальнейшем создании нового договора (когда не указан id существующего), проверяем существуюет ли уже договор для данных участников
	$dateDataObj = json_decode($_GET['dateDataObj']);
	
    if($dateDataObj->doc_type=='spec' && isset($_GET['agreement_type']) && $_GET['agreement_type'] == 'long_term' && !$agreement_id ) check_agreements_existence($client_id,$_GET['agreement_type'],$_GET['date'],$_GET['our_firm_id'],$_GET['requisit_id']);
	
    include './skins/tpl/agreement/prepayment_setting.tpl';
   
?>