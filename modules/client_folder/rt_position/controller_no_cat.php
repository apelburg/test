<?php	
	if(!isset($type_product)){echo "Тип товара не определён,<br>или строка с id=".$_GET['id']." в таблице `".RT_DOP_DATA."` не существует ";exit;}
	
	
	// инициализация класса работы с позициями
	// ВНИМАНИЕ!!!
	// AJAX ОБРАБАТЫВАЕТСЯ ВНУТРИ КЛАССОВ
	$POSITION_GEN = new Position_general_Class((isset($_GET)?$_GET:array()),(isset($_POST)?$_POST:array()),$_SESSION);

	ob_start();		

	//echo 'controller_pol.php отвечает за обработку информации по товару полиграфии листовой';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>