<?php	
	// инициализация класса формы
	$post = isset($_POST)?$_POST:array();
	$get = isset($_GET)?$_GET:array();
	$FORM = new Forms($get,$post,$_SESSION);

	// инициализация класса работы с некаталожными позициями
	$POSITION_NO_CAT = new Position_no_catalog($get,$post,$_SESSION);

	/*******************************   AJAX   ***********************************/
	if(isset($_POST['AJAX'])){		

		if($_POST['AJAX']=='to_chose_the_type_product_form'){
			// форма выбора типа продукта
			echo $FORM->to_chose_the_type_product_form_Html();
			exit;
		}

		if($_POST['AJAX']=='get_form_Html'){
			// запрашиваем из POST массива данные о типе продукта
			$t_p = (isset($_POST['type_product']) && $_POST['type_product']!="")?$_POST['type_product']:'none';
			// если тип уже известен, то мы уже не можем его менять, а значит можем выдать форму только для него
			if(isset($type_product)){
				$t_p = $type_product;
			}

			// запрос формы html
			$FORM->get_product_form_Html($t_p);
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
		if($_POST['AJAX'] == 'save_no_cat_variant'){
			unset($_POST['AJAX']); // уничтожаем переменную, дабы она не попала в массив обработки
			

			$FORM->insert_new_options_in_the_Database();
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