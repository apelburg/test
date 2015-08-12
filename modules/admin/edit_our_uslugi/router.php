<?php

	// класс редактирования услуг
	include ROOT.'/libs/php/classes/edit_our_uslugi_class.php';

	// создаём экземпляр класса
	$EDIT_USLUG = new Our_uslugi();	

	include 'controller.php';

	include ROOT.'/skins/tpl/admin/our_uslugi/show.tpl';



?>