<?php

	// класс редактирования услуг
	include ROOT.'/libs/php/classes/edit_forms_class.php';

		
 	ob_start();	
		
	include 'controller.php';
		
	$content = ob_get_contents();
	ob_get_clean();
	

	include ROOT.'/skins/tpl/admin/edit_form/show.tpl';



?>