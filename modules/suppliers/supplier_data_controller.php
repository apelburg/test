<?php  
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	$supplier_id = (isset($_GET['suppliers_id']) && $_GET['suppliers_id']!=0)?$_GET['suppliers_id']:0;


	$url_string = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
	if(isset($_GET['supplier_edit'])){
		$url_string = str_replace("&supplier_edit", "", $url_string);
	}else{
		$url_string .= "&supplier_edit";
	}

	$quick_button = '<div class="quick_button_div"><a href="'.$url_string.'" id="" class="button ">'.((isset($_GET['supplier_edit']))?'Сохранить':'Редактировать').'</a></div>';


	$view_button = '<div class="quick_view_button_div"><a href="#11" class="button">&nbsp;</a></div>';

	/////////////////////////////////// AJAX //////////////////////////////////////
	if(isset($_POST['ajax_standart_window'])){
		echo '{
		       "response":"1",
		       "text":"Данные сохранены"
		      }';
		      exit;
	}
	
	 
	 /////////////////////////////////// AJAX //////////////////////////////////////	

	$supplierClass = new Supplier($_GET['suppliers_id']);

	$supplier = $supplierClass->info;

	$cont_company_phone = $supplierClass->cont_company_phone;
	$cont_company_other = $supplierClass->cont_company_other;

	// получаем адрес
	$supplier_address = Supplier::get_addres($_GET['suppliers_id']);

	$contact_faces_contacts = Supplier::cont_faces($_GET['suppliers_id']);

	$edit_show = (isset($_GET['supplier_edit']))?'admin_':'';

	$supplier = $supplierClass->info;
	################################

	if($supplier==0){
		//такого клиента не существует
		$quick_button = '<div class="quick_button_div"><a href="http://'.$_SERVER['SERVER_NAME'].'/os/?page=suppliers&section=suppliers_list" id="" class="button ">Показать всех</a></div>';
		include('./skins/tpl/suppliers/supplier_data/default.tpl'); 	
	}else{
		// получаем рейтинг компании
		$supplierRating = Supplier::get_reiting($supplier_id,$supplier['rate']);

		//получаем текущий адрес клиента
		ob_start();
		foreach ($supplier_address as $adress_number => $adress) {
			include('./skins/tpl/suppliers/supplier_data/supplier_adress_row.tpl');
		}
		$supplier_address_s .= ob_get_contents();
		ob_get_clean();

		//получаем информацию по клиенту
		ob_start();
		include('./skins/tpl/suppliers/supplier_data/'.$edit_show.'supplier_table.tpl');
		$supplier_content = ob_get_contents();
		ob_get_clean();

		//получаем информацию по контактным лицам данного клиента
		ob_start();
		$supplier_content_contact_faces = "";
		$contact_face_d_arr = array();
		foreach($contact_faces_contacts as $k=>$this_contact_face){
			//print_r($this_contact_face);
			$contact_face_d_arr = $supplierClass->get_contact_info("SUPPLIERS_CONT_FACES_TBL",$this_contact_face['id']);
			// echo "<pre>";
			// print_r($contact_face_d_arr);
			// echo "</pre>";
			$cont_company_phone = (isset($contact_face_d_arr['phone']))?$contact_face_d_arr['phone']:''; 
			$cont_company_other = (isset($contact_face_d_arr['other']))?$contact_face_d_arr['other']:'';
			
			//echo $clientClass->$this->get_contact_info("CLIENTS_TBL",$id)($contact_face_d_arr, 'phone',Client::$array_img);
			include('./skins/tpl/suppliers/supplier_data/'.$edit_show.'supplier_cotact_face_table.tpl');
		}

		$supplier_content_contact_faces .= ob_get_contents();
		ob_get_clean();


		//получаем адрес папки и примечания
		ob_start();
		include('./skins/tpl/suppliers/supplier_data/'.$edit_show.'supplier_dop_info.tpl');
		$supplier_content_dop_info = ob_get_contents();
		ob_get_clean();

		// получаем подготовленный контент для модальных окон
		ob_start();
		include('./skins/tpl/suppliers/supplier_data/dialog_windows.tpl');
		$dialog_windows = ob_get_contents();
		ob_get_clean();

		//выводим общий шаблон
		include('./skins/tpl/suppliers/supplier_data/show.tpl'); 
		// echo "<pre>";
		// print_r($supplierClass);
		// echo "</pre>";

	}




?>
