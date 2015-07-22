<?php 

	//echo $client_id.' '.$agreement_id.' '.$specification_num;
	delete_specification($client_id,$agreement_id,$specification_num);
	
	header('Location:'.$_SERVER['HTTP_REFERER'] );

?>
