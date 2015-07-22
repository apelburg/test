<?php 

	//echo $client_id.' '.$agreement_id;
	delete_agreement($agreement_id);
	
	header('Location:'.$_SERVER['HTTP_REFERER'] );

?>