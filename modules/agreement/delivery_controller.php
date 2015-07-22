<?php

   
	if(isset($_GET['agreement_id']))
	{
	    $data = fetch_agreement_content($agreement_id);
		$requisit_id = $data['client_requisit_id'];

	}
	 
    
	//$section = (isset($_GET['agreement_id']))? 'agreement_editor' : 'save_agreement'; .$section
    /*$item = get_client_requisites($requisit_id);
	$address = (($item['postal_address'] != '')? $item['postal_address'] : 'адрес не указан');
	$addresses  = '<div class="prepayment_row" style="margin:10px 0px 0px 10px;"><a href="?'.addOrReplaceGetOnURL('section=short_description').'&address='.urlencode($address).'">'.$address.'</a></div>';
	$address = (($item['legal_address'] != '')? $item['legal_address'] : 'адрес не указан');
	$addresses .= '<div class="prepayment_row" style="margin:10px 0px 0px 10px;"><a href="?'.addOrReplaceGetOnURL('section=short_description').'&address='.urlencode($address).'">'.$address.'</a></div>';
	*/
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/client_class.php");
	$addresses = Client::get_addres($client_id);
	
	$addresses ='';
	foreach($addresses as $address){
	    $addresses .= '<div class="prepayment_row" style="margin:10px 0px 0px 10px;"><a href="?'.addOrReplaceGetOnURL('section=short_description').'&address='.urlencode($address).'">'.$address.'</a></div>';
	}

	
	
	/*if($result_row['addres']!=''){
	  $addresses = '<div class="prepayment_row" style="margin:10px 0px 0px 10px;"><a href="?'.addOrReplaceGetOnURL('section=short_description').'&address='.urlencode($address).'">'.$result_row['addres'].'</a></div>';
	}
	if($result_row['delivery_address']!=''){
	  $addresses = '<div class="prepayment_row" style="margin:10px 0px 0px 10px;"><a href="?'.addOrReplaceGetOnURL('section=short_description').'&address='.urlencode($address).'">'.$result_row['delivery_address'].'</a></div>';
	}*/
	
    include './skins/tpl/agreement/delivery_setting.tpl';
   
?>
