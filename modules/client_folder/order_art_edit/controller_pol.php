<?php	
	// инициализация класса формы
	$FORM = new Forms;
	/*******************************   AJAX   ***********************************/
	if(isset($_POST['AJAX'])){
		

		if($_POST['AJAX']=='get_form'){
			// пока что тестип только тип pol_list, в дальнейшем будем брать данные из меременной $type_poduct
			$FORM->get_product_form($type_product);			
			exit;
		}

		if($_POST['AJAX'] == 'general_form_for_create_product'){
			echo '<pre>';
			print_r($_POST);
			echo '</pre>';
			exit;
		}
	}
	/*******************************  END AJAX  *********************************/


	ob_start();		
	
	echo 'controller_pol.php отвечает за обработку информации по товару полиграфии листовой';
	
	$variants_content = ob_get_contents();
	ob_get_clean();

	
	//echo $variants_content;

	
?>