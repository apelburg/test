<?php

	/*******************************   AJAX   ***********************************/
	if(isset($_POST['AJAX'])){

	}
	/*******************************  END AJAX  *********************************/


	ob_start();		
	
	echo 'controller_pol.php отвечает за обработку информации по товару полиграфии многолистовой';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>