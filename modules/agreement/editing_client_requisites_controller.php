<?php

	//$form_data = get_client_requisites($requisit_id);
	
	    $tpl_name = '../../skins/tpl/admin/order_manager/clients/manegement_row.tpl';
		$fd = fopen($tpl_name,"rb");
		$tpl = fread($fd,filesize($tpl_name));
		fclose($fd);
		
		
		ob_start();
		
		$manegement_data_result = array();
		$counter = 0;
		$type = 'chief';
		$managment = 'managment1'; 
		$checked = 'checked';
	
	    eval('?>'.$tpl.'<?php ');
	
		
		$chief_fields = ob_get_contents();
	    ob_get_clean();
		
		ob_start();
		$manegement_data_result = array();
        $counter = 0;
		$type = 'accountant';
		$managment = 'managment2'; 
		$checked = '';
		
		eval('?>'.$tpl.'<?php ');
	
		$accountant_fields = ob_get_contents();
	    ob_get_clean();
    
	include('../../skins/tpl/admin/order_manager/agreement/client_requisites_form.tpl');
	
?>