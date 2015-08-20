<?php	
	if(!isset($type_product)){echo "Тип товара не определён,<br>или строка с id=".$_GET['id']." в таблице `".RT_DOP_DATA."` не существует ";exit;}
	
	if(!isset($_GET['client_id'])){$_GET['client_id'] = 1874;}
	
	// инициализация класса работы с позициями
	// ВНИМАНИЕ!!!
	// AJAX ОБРАБАТЫВАЕТСЯ ВНУТРИ КЛАССОВ
	$POSITION_GEN = new Position_general_Class();

	ob_start();		

	//echo 'controller_pol.php отвечает за обработку информации по товару полиграфии листовой';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>