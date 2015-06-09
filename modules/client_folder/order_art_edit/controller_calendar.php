<?php
	
	/*******************************   AJAX   ***********************************/
	if(isset($_POST['global_change'])){

	}
	/*******************************  END AJAX  *********************************/


	ob_start();		
	
	echo 'controller_pol.php отвечает за обработку информации по календарям';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>