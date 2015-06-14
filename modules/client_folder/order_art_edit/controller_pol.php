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
			unset($_POST['AJAX']); // уничтожаем переменную, дабы она не попала в массив обработки
			$type_product = $_POST['type_product'];
			// echo '<pre>';
			// print_r($_POST);
			// echo '<pre>';
			echo '<div style="border-top:1px solid red">'.$FORM->restructuring_of_the_entry_form($_POST,$type_product).'</div>';
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