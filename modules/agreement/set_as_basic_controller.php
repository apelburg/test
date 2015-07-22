<?php 

	//echo $client_id.' '.$agreement_id;
	set_agreement_as_basic($client_id,$agreement_id);
	
	header('Location:'.$_SERVER['HTTP_REFERER'] );

?>