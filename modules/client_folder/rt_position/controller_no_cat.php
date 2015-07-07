<?php	
	// инициализация класса работы с некаталожными позициями
	// ВНИМАНИЕ!!!
	// AJAX ОБРАБАТЫВАЕТСЯ ВНУТРИ КЛАССОВ
	$POSITION_GEN = new Position_general_Class((isset($_GET)?$_GET:array()),(isset($_POST)?$_POST:array()),$_SESSION);

	ob_start();		

	echo 'controller_pol.php отвечает за обработку информации по товару полиграфии листовой';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>