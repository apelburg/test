<?php
    // print_r($_GET); exit;
    if(!$agreement_id && $_GET['agreement_type'] == 'long_term') check_agreements_existence($client_id,$_GET['agreement_type'],$_GET['date'],$_GET['our_firm_id'],$_GET['requisit_id']);
	
    include './skins/tpl/agreement/prepayment_setting.tpl';
   
?>