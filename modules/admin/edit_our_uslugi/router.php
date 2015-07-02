<?php

	// класс редактирования услуг
	include ROOT.'/libs/php/classes/edit_our_uslugi_class.php';

	// создаём экземпляр класса
	$EDIT_USLUG = new Our_uslugi($_GET,$_POST,$_SESSION);
	
	/***********************************  AJAX **********************************/
	// if(isset($_POST['AJAX'])){
	// 	if($_POST['AJAX'] == 'get_edit_content_for_usluga'){
	// 		// блок редактирования цен, имени и типа услуги
	// 		echo $EDIT_USLUG->get_chenge_form_uslugi_Html();
	// 		// блок редактирования статусов
	// 		echo $EDIT_USLUG->get_status_uslugi_Html($_POST['id']);
	// 		exit;
	// 	}
	// }
	/********************************** AJAX END ********************************/

	include 'controller.php';

	include ROOT.'/skins/tpl/admin/our_uslugi/show.tpl';



?>