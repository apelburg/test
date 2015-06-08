<?php
	
	/*******************************   AJAX   ***********************************/
	if(isset($_POST['global_change'])){

	}
	/*******************************  END AJAX  *********************************/


	ob_start();		
	
	echo 'controller_ext.php отвечает за обработку информации по товару под заказ';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	

	// шаблон страницы
	//include 'skins/tpl/client_folder/order_art_edit/show.tpl';
?>