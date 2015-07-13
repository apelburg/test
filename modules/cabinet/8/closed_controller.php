<?php
// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **

///////////////////////////// AJAX ////////////////////////////////
if(isset($_POST['AJAX'])){
	if($_POST['AJAX']=="change_invoce_num"){
		$query = "UPDATE  `apelburg_base`.`os__cab_orders_list` SET  `invoice_num` =  '".$_POST['value']."' WHERE  `os__cab_orders_list`.`id` ='".$_POST['row_id']."';";	
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
}


///////////////////////////// AJAX ////////////////////////////////





?>