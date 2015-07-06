<?php
/*
ОПИСАНИЕ:






*/

/*
Класс работы с некаталожной продукцией

в конце названий методов указан формат в котором выдаётся информация по окончании работы метода
Html, Array, String, Int
Либо:
Database - если метод предназначен только для работы с базой

PS было бы неплохо взять взять это за правило 



*/
class Position_no_catalog{
	// глобальные массивы
	private $POST;
	private $GET;
	private $SESSION;

	// тип продукта
	private $type_product;

	// 

	// id юзера
	private $user_id;

	// допуски пользователя
	private $user_access;

	// права на редактирование поля определяются внутри 
	// некоторых функций 
	private $edit_admin;
	private $edit_men;
	private $edit_snab;

	// id позиции
	private $id_position;

	// класс форм
	private $FORM;

	// статусы кнопки для различных групп пользователей 
	private $status_snab = array(

		'on_calculation' => array( //на расчёт мен
			'name' => 'На расчёт',
			'buttons' =>  array( // кнопки для данного статуса
				'on_calculation_snab' => array(// статус позиции или даже запроса
					'name' => 'Запросить расчёт',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '5'
					)
				)
			),


		'on_calculation_snab' => array( 
			'name' => 'Запрошен расчёт', // в снабжение
			'buttons' =>  array( // кнопки для данного статуса
				'in_calculation' => array(		
					'name' => 'Принять в работу',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					),
				'tz_is_not_correct' => array( // статус снабжения по позиции
					'name' => 'ТЗ не корректно',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					)
				)
			),

		'on_recalculation_snab' => array(
			'name' => 'На перерасчёт',
			'buttons' =>  array( // кнопки для данного статуса
				'in_calculation' => array(		
					'name' => 'Принять в работу',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					),
				'tz_is_not_correct' => array( // статус снабжения по позиции
					'name' => 'ТЗ не корректно',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					)
				)
			),

		'tz_is_not_correct' => array( // статус снабжения по позиции
			'name' => 'ТЗ не корректно',
			'buttons' =>  array( // кнопки для данного статуса
				'on_recalculation_snab' => array( // 
					'name' => 'Запросить расчёт',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '5'
					)
				)
			
			),
		'in_calculation' => array(
			'name' => 'В расчёте снабжение',
			'buttons' =>  array( // кнопки для данного статуса
				'calculate_is_ready' => array(
					'name' => 'Расчёт готов',
					'class' => 'status_art_right_class',// класс кнопки для смены статуса
					'access' => '8'
					),
				'create_text_mail_for_supplier' => array(
					'name' => 'Письмо поставщику',
					'class' => 'create_text_mail_for_supplier',
					'access' => '8' 
					)
				)
			),

		'calculate_is_ready' => array(
			'name' => 'Расчёт от снабжения',
			'buttons' =>  array( // кнопки для данного статуса
				'on_recalculation_snab' => array( // 
					'name' => 'Запросить перерасчёт',
					'access' => '5'
					)
				)
			),
		
		);

	function __construct($get,$post,$session){
		$this->GET = $get;
		$this->POST = $post;
		$this->SESSION = $session;
		$this->user_id = $session['access']['user_id'];

		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		$this->id_position = isset($this->GET['id'])?$this->GET['id']:0;
	}

	public function get_user_access_Database_Int($id){
		global $mysqli;
		$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$int = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$int = (int)$row['access'];
			}
		}
		//echo $query;
		return $int;
	}

	public function edit_work_days_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `work_days` = '".$this->POST['work_days']."'
		             WHERE `id` =  '".$this->POST['id_dop_data']."';
		             ";
		$result = $mysqli->query($query) or die($mysqli->error);		
		echo '{"response":"OK","name":"edit_work_days"}';
	}

	public function edit_snab_comment_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `snab_comment` = '".$this->POST['note']."'
		             WHERE `id` =  '".$this->POST['id_dop_data']."';
		             ";
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"OK","name":"this->edit_snab_comment_Database"}';
	}


	// имя вкладки вариантов
	private function get_name_group($status_snab){
		// получаем имя вкладки
			if(isset($this->status_snab[$status_snab])){				
				$name_group = $this->status_snab[$status_snab]['name'];				
			}else{

				// ТАКЖЕ СУЩЕСТВУЮТ ЕЩЕ ВАРИАНТЫ ПОСТАВЛЕННЫЕ НА ПАУЗУ _pause
				if(substr_count($status_snab, '_pause')){
					return $this->get_name_group(str_replace('_pause','',$status_snab)).' (ПАУЗА)'; 
					// return '(ПАУЗА)';
				}else{
					// варант статуса отсутствует в предусмотренных
					$name_group = $status_snab;
				}
			}	
		return $name_group;
	}


	// выводит все варианты по группам, 
	// по сути является главной функцией вывода основного контента
	public function get_all_on_calculation_Html($type_product){
		//сохраняем тип продукта
		$this->type_product = $type_product;
// !!!!!!!!!!!!!!!!!!!!!!!!!
	// **
		$variants_array = $this->get_all_variants_Database_Array();
		$variants_array_GROUP_status_snab = $this->get_all_variants_Group_Database_Array();
		
		$variants_group_menu_Html = '<div id="variants_name"><ul id="all_variants_menu_pol">';

		$html = '<div id="variant_of_snab">';

		### перебираем все статусы снабжения
		foreach ($variants_array_GROUP_status_snab as $key => $value) {

			# групируем по статусу в разные вкладки
			// ПОЛУЧАЕМ ИМЯ ВКЛАДКИ
			$name_group = $this->get_name_group($value['status_snab']);

			
			// считаем количество вариантов во вкладке
			$number_variants = 0;
			foreach ($variants_array as $key2 => $value2) {
				if ($value['status_snab']==$value2['status_snab']) {
					$number_variants++;
				}
			}

			// добавляем вкладку в список
			$variants_group_menu_Html .= '<li data-cont_id="variant_content_table_'.$key.'" class="variant_name '.(($key==0)?'checked':'').'" data-status="'.$value['status_snab'].'">'.$name_group.' ('.$number_variants.')</li>';
			
			// шаблон для вкладки "на расчет"
			$html .= '<div id="variant_content_table_'.$key.'" class="variant_content_table" '.(($key==0)?'style="display:block"':'style="display:none"').'>';
			
			// подгружаем таблицу со списком вариантов
			$html .= $this->get_variants_list_Html($variants_array,$value['status_snab']);	
			$html .= "</div>";				
		}

		$variants_group_menu_Html .= '</ul></div>';
		//echo $variants_group_menu_Html;
		return $variants_group_menu_Html.$html;
	}



	// возвращает таблицу со списком вариантов
	private function get_variants_list_Html($variants_array,$status_snab){
		// сохраняем статус 
		$status_snab_whith_pause = $status_snab;

		// определяем редакторов для полей (html тегов
		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'

		$html = '';
		$extended_info = '';// расширенная информация по каждому варианту
		$html .= "<table class='show_table'>";
			$html .= "<tr>
									<th></th>
									<th>варианты</th>
									<th>тираж</th>
									<th>$ входящая</th>
									<th>$ МИН исходящая</th>
									<th>$ исходящая</th>
									<th>подрядчик</th>
									<th>макет к</th>
									<th>срок р/д</th>
									<th>комментарий снабжения</th>
								</tr>";

			### выбираем все строки по каждуму статусу снабжения
			$n = 1;

			$status_snab_whith_pause = $status_snab;

			$text_for_send_mail = '';
			$text_for_send_mail_name_product = '';
			foreach ($variants_array as $key2 => $value2) {
				if ($status_snab_whith_pause==$value2['status_snab']) {
					// наличие паузы ограничивает редактирование для снаба и мена, 
					// мен может снять паузу
					$pause = substr_count($value2['status_snab'], '_pause');

					// генеральное разрешение.... проверяет не история ли это
					$edit_true = substr_count($status_snab, 'ё')?false:true;
					
					if($edit_true){// если работаем НЕ с HISTORY			
						// получаем услуги для данного варианта
						$uslugi = $this->get_uslugi_Database_Array($value2['id']);
					}else{// если работаем с HISTORY
						$uslugi = $this->get_uslugi_history_Database_Array($value2['id']);
					}
					// расчёт стоимостей услуг
					$uslugi_arr = $this->calclate_summ_uslug_arr($uslugi);
					
					// получаем реальный статус (буз паузы)
					$status_snab = ($pause)?str_replace('_pause', '', $value2['status_snab']):$value2['status_snab'];


					// получаем всю инфу по варианту
					$this->FORM = new Forms($this->GET,$this->POST,$this->SESSION);//для вызова следующего метода нужна информация из сласса форм
					
					// контент для отправки поставщику
					$no_cat_json = json_decode($value2['no_cat_json'],true);
					$text_for_send_mail_name_product = $no_cat_json['name_product'];
					$text_for_send_mail .= $this->send_mail_for_supplier_Html($value2);
					$extended_info .= $this->get_extended_info_for_variant_Html($value2,$value2['id'],$uslugi,$uslugi_arr,$status_snab);
					


					// если работает снаб, ограничиваем права мена и наоборот
					if($value2['status_snab']=='in_calculation' || $value2['status_snab']=='in_recalculation'){
						$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
						$this->edit_men = '';						
					}else{
						$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
						$this->edit_snab = '';						
					}

					// обнуляем все права при $edit_true == false
					if($edit_true == false){
						$this->edit_men = '';
						$this->edit_snab = '';
						$this->edit_admin = '';
						$pause = 1;
					}


					$html .= "<tr data-id='".$value2['id']."'>
							<td><span class='traffic_lights_".(($value2['row_status']!='')?$value2['row_status']:'green')."'><span></span></span></td>
							<td>".$n."</td>
							<td><span>".$value2['quantity']."</span> шт</td>
							<td><span>".($uslugi_arr['summ_price_in']+$value2['price_in'])."</span> р</td>
							<td style='color:red'><span>".($uslugi_arr['summ_price_out']+$value2['price_out_snab'])."</span> р</td>
							<td><span>".($uslugi_arr['summ_price_out']+$value2['price_out'])."</span> р</td>
							<td ".(( ($this->edit_snab!='' && $pause==0) || $this->edit_admin!='')?"class='change_supplier'":"")." data-id='".$value2['suppliers_id']."'>".$value2['suppliers_name']."</td>
							<td ".((($this->edit_snab!='' && $pause==0) || $this->edit_admin!='')?"class='chenge_maket_date'><input type='text' name='maket_date' value='".$value2['maket_date']."'>":">".$value2['maket_date'])."</td>
							<td ><div ".((($this->edit_snab!='' && $pause==0) || $this->edit_admin!='')?"class='change_srok'":"")." ".$this->edit_snab.$this->edit_admin.">".$value2['work_days']."</div></td>";
				
				//$html .= ($this->user_access == 1 || $this->user_access == 8 || $value2['extended_rights_for_manager']==1)?"	<td><input type='text' value='".$value2['snab_comment']."'></td>
				
				$html .= ($this->user_access == 1 && $edit_true || $this->edit_snab!='' && $pause==0 || ($this->user_access == 5 && $value2['extended_rights_for_manager']==1 && $pause==0))?"	<td><div contenteditable='true' class='edit_snab_comment'> ".$value2['snab_comment']."</div></td>
						":"<td>".$value2['snab_comment']."</td>";
				$html .= "</tr>";
				
				$n++;	
				}
			}
			
			$html .= "</table>";
			// получаем набор кнопок управления данной вкладкой
			$buttons_option = $this->get_top_funcional_byttun_for_user_Html($status_snab_whith_pause,$pause);
			

			// составляем конечный текст письма
			// оборачиваем text_for_send_mail в div
			$text_for_send_mail = '<div class="text_for_send_mail">
			Вариантов: '.$n.'<br>
			Наименование товара: '.$text_for_send_mail_name_product.' 
			'.$text_for_send_mail.'</div>';


			// составляем конечный html
			$html = $html.$buttons_option.$text_for_send_mail.$extended_info;// прикрепляем расшириную инфу
			
		return $html;			
	}


	// кнопки top
	public function get_top_funcional_byttun_for_user_Html($status_snab){
		// пауза 
		$html = '<div class="hidden_top_buttons">';
		$pause_buttons = '';
		if(!substr_count($status_snab, '_pause')){
			if(!substr_count($status_snab, 'Расчёт от снабжения')){
				if(($this->user_access == 1 || $this->user_access == 5) && ($status_snab == 'in_calculation' || $status_snab == 'on_recalculation_snab')){
					$pause_buttons = '<li class="buttons_top_1 status_art_right_class_pause" data-send_status="1"><div><span>Поставить на паузу</span></div></li>';
				}
				// все кнопки выводятся только когда пауза равна 0
				// перебираем все возможные кнопки кнопки
				foreach ($this->status_snab[$status_snab]['buttons'] as $key => $value) {
					// при наличии допуска к кнопке - выводим её
					if($this->user_access == 1 || $this->user_access == $value['access']){

						$html .= '<li class="buttons_top_1 '.(isset($value['class'])?$value['class']:'status_art_right_class').'" data-send_status="'.$key.'"><div><span>'.$value['name'].'</span></div></li>';			
					}
				}
			}
		}else{
			// когда стоит пауза, единственное
			if($this->user_access == 1 || $this->user_access == 5){
				$pause_buttons = '<li class="buttons_top_1 status_art_right_class_pause" data-send_status="0"><div><span>Снять с паузы</span></div></li>';
			}	
		}
				
		
		$html .= $pause_buttons.'</div>';
		// $html .= '</div>';
		return $html;
	}

	// метод смены даты подач макета
	public function change_maket_date_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `maket_date` = '".date('Y-m-j', strtotime($this->POST['maket_date']))."'
		             WHERE `id` =  '".$this->POST['id_dop_data']."';
		             ";
		$result = $mysqli->query($query) or die($mysqli->error);
		
		echo '{"response":"OK","name":"change_maket_date_Database"}';
	}

	// редактируем информацию об поставщиках для некаталожного варианта расчёта
	public function change_supliers_info_dop_data_Database(){
		global $mysqli;
		$query ="UPDATE `".RT_DOP_DATA."` SET
		             `suppliers_id` = '".$this->POST['suppliers_id']."',
		             `suppliers_name` = '".$this->POST['suppliers_name']."' 
		             WHERE `id` =  '".$this->POST['dop_data_id']."';
		             ";
		$result = $mysqli->query($query) or die($mysqli->error);
		
		echo '{"response":"OK","name":"chose_supplier_end"}';
	}

	// форматируем денежный формат + округляем
	private function round_money($num){
		return number_format(round($num, 2), 2, '.', '');
	}

	private function send_mail_for_supplier_Html($arr){
		// список разрешённых для вывода в письмо полей
		$send_info_enabled= array('format'=>1,'material'=>1,'plotnost'=>1,'type_print'=>1,'change_list'=>1,'laminat'=>1);


		
		// получаем json с описанием продукта
		$dop_info_no_cat = ($arr['no_cat_json']!='')?json_decode($arr['no_cat_json']):array();
		
		
		$html = '';
		// если у нас есть описание заявленного типа товара
		if(isset($this->FORM->form_type[$this->type_product])){
			$names = $this->FORM->form_type[$this->type_product]; // массив описания хранится в классе форм
			$html .= '<div class="get_top_funcional_byttun_for_user_Html table">';
			foreach ($dop_info_no_cat as $key => $value) {
				if(!isset($send_info_enabled[$key])){continue;}
				$html .= '
					<div class="row">
						<div class="cell" >'.$names[$key]['name'].'</div>
						<div class="cell">'.$value.'</div>
					</div>
				';
			}
			$html .= '</div>';
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}
	}


	// возвращает расшириную информацию по варианту в Html
	private function get_extended_info_for_variant_Html($arr,$id,$uslugi,$uslugi_arr,$status_snab){
		
		// $arr - содержит всю информацию по данному варианту
		// $id - порядковый номер варианта от 0 (нуля)
		// $uslugi -  список услуг для данного варианта
		// $uslugi_arr -  расчёт стоимостей услуг
		// $status_snab -  статус снабжения


		// если в статусе снабжения присутствует добавленное окончание _pause,
		// все варианты во вкладке поставлены на паузу
		$pause = substr_count($status_snab, '_pause');

		// определяем редакторов для полей (html тегов)

		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'

		// если работает снаб, ограничиваем права мена и наоборот
		if($status_snab=='in_calculation' || $status_snab=='in_recalculation'){
			$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
			$this->edit_men = '';						
		}else{
			$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
			$this->edit_snab = '';						
		}

		// ограничиваем редактирование на паузе
		if($pause){$this->edit_men = '';$this->edit_snab = '';}

		// генеральное разрешение.... проверяет не история ли это
		$edit_true = substr_count($status_snab, 'ё')?false:true;
		// обнуляем все права при $edit_true == false
		if($edit_true == false){
			$this->edit_men = '';
			$this->edit_snab = '';
			$this->edit_admin = '';
			$pause = 1;
		}




		$html = '';	


		$dop_info_no_cat = ($arr['no_cat_json']!='')?json_decode($arr['no_cat_json']):array();

		// проценты наценки по варианту
		$per = ($arr['price_in']!= 0)?$arr['price_in']:0.09;
		$percent = $this->get_percent_Int($arr['price_in'],$arr['price_out']);


		// формируем html c расширенной информацией по варианту
		$html .= '<div id="variant_info_'.$id.'" class="variant_info" style="display:none">';
		$html .= '<table><tr><td  style="vertical-align: baseline;">';
		$html .= '<table class="calkulate_table" data-save_enabled="">
									<tbody><tr>
										<th>Стоимость товара</th>
										<th>$ вход.</th>
										<th>%</th>
										<th>$ МИН исход.</th>
										<th>$ исход.</th>
										<th>прибыль</th>
										<th class="edit_cell">ред.</th>
										<th class="del_cell">del</th>
									</tr>
									<tr class="tirage_and_price_for_one" data-dop_data_id="'.$arr['id'].'">
										<td>1 шт.</td>
										<td class="row_tirage_in_one price_in"><span '.$this->edit_admin.$this->edit_snab.'>'.(($arr['quantity']!=0)?$this->round_money($arr['price_in']/$arr['quantity']):0).'</span> р.</td>
										<td rowspan="2" class="percent_nacenki">
											<span '.$this->edit_admin.$this->edit_snab.$this->edit_men.'>'.$percent.'</span>%

										</td>
										<td class="row_price_out_one price_out_snab" style="color:red"><span '.$this->edit_admin.$this->edit_snab.'>'.(($arr['quantity']!=0)?$this->round_money(($arr['price_out_snab']/$arr['quantity'])):0).'</span> р.</td>
										<td class="row_price_out_one price_out_men"><span '.$this->edit_admin.$this->edit_men.'>'.(($arr['quantity']!=0)?$this->round_money($arr['price_out']/$arr['quantity']):0).'</span> р.</td>
										<td class="row_pribl_out_one pribl"><span>'.(($arr['quantity']!=0)?$this->round_money(($arr['price_out']/$arr['quantity'])-($arr['price_in']/$arr['quantity'])):0).'</span> р.</td>
										<td rowspan="2">
											<!-- <span class="edit_row_variants"></span> -->
										</td>
										<td rowspan="2"></td>
									</tr>
									<tr class="tirage_and_price_for_all for_all" data-dop_data_id="'.$arr['id'].'">
										<td>тираж</td>
										<td class="row_tirage_in_gen price_in"><span '.$this->edit_admin.$this->edit_snab.'>'.$arr['price_in'].'</span> р.</td>
										<td class="row_price_out_gen price_out_snab tirage" style="color:red"><span  '.$this->edit_admin.$this->edit_snab.'>'.$arr['price_out_snab'].'</span> р.</td>
										<td class="row_price_out_gen price_out_men tirage"><span  '.$this->edit_admin.$this->edit_men.'>'.$arr['price_out'].'</span> р.</td>
										<td class="row_pribl_out_gen pribl"><span>'.$this->round_money($arr['price_out']-$arr['price_in']).'</span> р.</td>
										
									</tr>
									
									'.$this->uslugi_template_Html($uslugi,0,$status_snab,$pause,$edit_true).'

									<tr>
										<th colspan="8" class="type_row_calc_tbl">'.(($pause==0 || $this->user_access == 1 && $edit_true)?'<div class="add_usl">Добавить ещё услуги</div>':'').'</th>
									</tr>
									<tr>
										<td colspan="8" class="table_spacer"> </td>
									</tr>
									<tr class="variant_calc_itogo">
										<td>ИТОГО:</td>
										<td><span>'.($uslugi_arr['summ_price_in']+$arr['price_in']).'</span> р.</td>
										<td><span>'.$this->round_money(($percent+$uslugi_arr['summ_percent'])/(1+$uslugi_arr['count_usl'])).'</span> %</td>
										<td><span>'.($uslugi_arr['summ_price_out']+$arr['price_out_snab']).'</span> р.</td>
										<td><span>'.($uslugi_arr['summ_price_out']+$arr['price_out']).'</span> р.</td>
										<td><span>'.($uslugi_arr['summ_price_out']+$arr['price_out']-$uslugi_arr['summ_price_in']-$arr['price_in']).'</span> р.</td>
										<td></td>
										<td></td>
									</tr>
								</tbody></table>
							';
		$html .= '</td><td style="display:none">'.$this->variant_no_cat_json_Html($dop_info_no_cat,$this->type_product,$pause,$edit_true).'</td></tr></table>';
		$html .= '</div>';

		
		return $html;
	}

	// ВЫВОДИТ СПИСОК УСЛУГ ПРИКРЕПЛЁННЫХ ДЛЯ ВАРИАНТА
	// $NO_show_head добавлен как необязательная переменная для отключения вывода 
	// $pause - флаг запрета редактирования
	// названия группы услуги
	private function uslugi_template_Html($arr, $NO_show_head = 0, $status_snab='', $pause=0, $edit_true=true){

		// echo '<pre>';
		// print_r($arr);
		// echo '</pre>';
		// определяем редакторов для полей (html тегов)
		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'

		// если работает снаб, ограничиваем права мена и наоборот
		if($status_snab=='in_calculation' || $status_snab=='in_recalculation'){
			$this->edit_snab = ($this->user_access == 8 && !$pause)?' contenteditable="true" class="edit_span"':'';
			$this->edit_men = '';						
		}else{
			$this->edit_men = ($this->user_access == 5 && !$pause)?' contenteditable="true" class="edit_span"':'';
			$this->edit_snab = '';						
		}

		// обнуляем все права при $edit_true == false
		if($edit_true == false){
			$this->edit_men = '';
			$this->edit_snab = '';
			$this->edit_admin = '';
			$pause = 1;
		}

		$html ='';
		// если массив услуг пуст возвращаем пустое значение 
		if(!count($arr)){return $html;}
		
		// сохраняем id услуг
		$id_s = array();
		foreach ($arr as $key => $value) {
			$id_s[] = $value['uslugi_id'];
		}
		$id_s = implode(', ', $id_s);

		// делаем запрос по услугам  
		global $mysqli;
		$query = "SELECT `".OUR_USLUGI_LIST."`.`parent_id`,`".OUR_USLUGI_LIST."`.`price_out`,`".OUR_USLUGI_LIST."`.`for_how`,`".OUR_USLUGI_LIST."`.`id`,`".OUR_USLUGI_LIST."`.`name`,`".OUR_USLUGI_LIST."_par`.`name` AS 'parent_name' FROM ".OUR_USLUGI_LIST."
inner join `".OUR_USLUGI_LIST."` AS `".OUR_USLUGI_LIST."_par` ON `".OUR_USLUGI_LIST."`.`parent_id`=`".OUR_USLUGI_LIST."_par`.`id` WHERE `".OUR_USLUGI_LIST."`.`id` IN (".$id_s.") ORDER BY  `os__our_uslugi_par`.`name` ASC ";
		// $query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (".$id_s.")";
		//echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);				
		$name_uslugi = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				foreach ($arr as $key => $value) {
						$name_uslugi[$row['id']] = $row;
				}
			}
		}

		$uslname = '';
		foreach ($name_uslugi as $key => $value) {
			// $NO_show_head добавлен как необязательная переменная для отключения вывода 
			// названия группы услуги

			if($uslname!=$value['parent_name'] && !$NO_show_head){
				$html .= '<tr  class="group_usl_name" data-usl_id="'.$value['parent_id'].'">
		 				<th colspan="8">'.$value['parent_name'].'</th>
 				</tr>';
 				$uslname = $value['parent_name'];
			}
			foreach ($arr as $key2 => $value2) {
				if($value2['uslugi_id']==$key){

					$price_in = (($value2['for_how']=="for_all")?$value2['price_in']:($value2['price_in']*$value2['quantity']));
					$price_out_men = ($value2['for_how']=="for_all")?$value2['price_out']:$value2['price_out']*$value2['quantity'];
					
					$pribl = ($value2['for_how']=="for_all")?($value2['price_out']-$value2['price_in']):($value2['price_out']*$value2['quantity']-$value2['price_in']*$value2['quantity']);
					$dop_inf = ($value2['for_how']=="for_one")?'(за тираж '.$value2['quantity'].' шт.)':'';
					
					$price_out_snab = ($value2['for_how']=="for_all")?$value2['price_out_snab']:$value2['price_out_snab']*$value2['quantity'];


					$real_price_out = ($value['for_how']=="for_all")?$value['price_out']:$value['price_out']*$value2['quantity'];

					$html .= '<tr class="calculate calculate_usl" data-dop_uslugi_id="'.$value2['id'].'" data-our_uslugi_id="'.$value['id'].'" data-our_uslugi_parent_id="'.trim($value['parent_id']).'">
										<td>'.$value['name'].' '.$dop_inf.'</td>
										<td class="row_tirage_in_gen uslugi_class price_in"><span>'.$this->round_money($price_in).'</span> р.</td>
										<td class="row_tirage_in_gen uslugi_class percent_usl"><span '.$this->edit_admin.$this->edit_snab.$this->edit_men.'>'.$this->get_percent_Int($value2['price_in'],$value2['price_out']).'</span> %</td>
										<td class="row_price_out_gen uslugi_class price_out_snab" style="color:red" data-real_min_price_for_one="'.$value['price_out'].'" data-real_min_price_for_all="'.$real_price_out.'"><span '.$this->edit_admin.$this->edit_snab.'>'.$this->round_money($price_out_snab).'</span> р.</td>
										<td class="row_price_out_gen uslugi_class price_out_men"><span '.$this->edit_admin.$this->edit_men.'>'.$this->round_money($price_out_men).'</span> р.</td>
										<td class="row_pribl_out_gen uslugi_class pribl"><span>'.$this->round_money($pribl).'</span> р.</td>
										<td class="usl_edit"><!-- <span class="edit_row_variants"></span> --></td>';

					$html .= ($this->user_id == $value2['creator_id'] || $this->user_access == 1 )?'<td class="usl_del"><span class="del_row_variants"></span></td>':'';
					// $html .= $value2['creator_id'];
					$html .='</tr>';

				}
			}

		}

		return $html;
	}

	// подсчёт стоимотсти услуг для варианта
	private function calclate_summ_uslug_arr($uslugi){
		// echo '<pre>';
		// print_r($uslugi);
		// echo '</pre>';



		$uslugi_arr['summ_price_in'] = 0;
		$uslugi_arr['summ_price_out'] = 0;
		$uslugi_arr['summ_pribl'] = 0;
		$uslugi_arr['summ_percent'] = 0;
		$uslugi_arr['count_usl'] = 0;

		foreach ($uslugi as $key => $value) {
			if(trim($value['for_how'])!=''){
				//echo '$value[\'for_how\'] = '.$value['for_how'].'  ,  '.(($value['for_how']=="for_one")?$value['price_out']*$value['quantity']:$value['price_out']).'  - '.$uslugi_arr['summ_price_out'].'<br>';
				
				$uslugi_arr['summ_price_in'] += ($value['for_how']=="for_one")?$value['price_in']*$value['quantity']:$value['price_in'];
				$uslugi_arr['summ_price_out'] += ($value['for_how']=="for_one")?$value['price_out']*$value['quantity']:$value['price_out'];
				$uslugi_arr['summ_pribl'] += ($value['for_how']=="for_one")?($value['price_out']*$value['quantity']-$value['price_in']*$value['quantity']):($value['price_out']-$value['price_in']);
				$uslugi_arr['summ_percent'] += $this->get_percent_Int($value['price_in'],$value['price_out']);
				$uslugi_arr['count_usl']++;
			}
		}
		//echo $uslugi_arr['summ_price_out'].'   *   ';
		return $uslugi_arr;
	}

	// подсчёт процентов наценки
	private function get_percent_Int($price_in,$price_out){
		$per = ($price_in!= 0)?$price_in:0.09;
		$percent = round((($price_out-$price_in)*100/$per),2);
		return $percent;
	}

	// получаем услуги по варианту расчета
	private function get_uslugi_Database_Array($id){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	// получаем услуги по варианту расчета из истории
	private function get_uslugi_history_Database_Array($id){
		global $mysqli;
		$query = "SELECT * FROM `".DOP_USLUGI_HIST."` WHERE dop_row_id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		// echo $query;
		return $arr;
	}

	// получаем услугу по id   МОЖЕТ НЕ ПОНАДОБИТЬСЯ !!!!!!!!!!!!!!!!!!
	private function get_uslugi_of_id_Database_Array($id){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_USLUGI."` WHERE id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	// получаем все варианты
	private function get_all_variants_Database_Array(){
		global $mysqli;
		$query = "SELECT *, DATE_FORMAT(`maket_date`, '%m.%d.%Y') AS `maket_date` FROM `".RT_DOP_DATA."` WHERE row_id = '".$this->id_position."' 
		 UNION SELECT *, DATE_FORMAT(`maket_date`, '%m.%d.%Y') AS `maket_date` FROM `".DOP_DATA_HIST."` WHERE row_id='".$this->id_position."' ORDER BY id ASC;";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		
		return $arr;
	}



	
	//получаем все уникальные по статусу варанты
	private function get_all_variants_Group_Database_Array(){
		global $mysqli;
		$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE row_id = '".$this->id_position."' GROUP BY `status_snab` 
		 UNION SELECT * FROM `".DOP_DATA_HIST."` WHERE row_id='".$this->id_position."' GROUP BY `snab_end_work`";
		// echo $query.'<br>';
		$result = $mysqli->query($query) or die($mysqli->error);				
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		
		return $arr;

	}

	private function copy_variants_to_history_Database($id_dop_data){
		global $mysqli;
		// получаем время получения расчёта от снабжения
		$query = "SELECT DATE_FORMAT(`snab_end_work`, '%m.%d.%Y %H:%i:%s') AS `snab_end_work` FROM  `".RT_DOP_DATA."` WHERE `id` IN (".$id_dop_data.") GROUP BY `status_snab`";
		$result = $mysqli->query($query) or die($mysqli->error);
		$arr = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr = $row;
			}
		}



		// меняем стутус на HISTORY
		$query = "UPDATE `".RT_DOP_DATA."` SET 
		`status_snab`='Расчёт от снабжения ".$arr['snab_end_work']."' WHERE `id` IN (".$id_dop_data.");";
		
		$result = $mysqli->query($query) or die($mysqli->error);


		// переводим id в массив
		$id_arr = explode(",", $id_dop_data);

		//для каждого id выполняем отдельное копирование
		foreach ($id_arr as $key => $id) {
			// копируем в таблицу History
			$query = '
			INSERT INTO `'.DOP_DATA_HIST.'` (
					`row_id`,
					`expel`,
					`row_status`,
					`glob_status`,
					`quantity`,
					`zapas`,
					`price_in`,
					`price_out`,
					`price_out_snab`,
					`discount`,
					`tirage_json`,
					`print_z`,
					`standart`,
					`shipping_time`,
					`shipping_date`,
					`suppliers_id`,
					`suppliers_name`,
					`note`,
					`create_date`,
					`no_cat_json`,
					`status_snab`,
					`snab_end_work`,
					`query_send_by_snab`,
					`snab_comment`,
					`extended_rights_for_manager`,
					`work_days`,
					`maket_date`
					)  SELECT 
					`row_id`,
					`expel`,
					`row_status`,
					`glob_status`,
					`quantity`,
					`zapas`,
					`price_in`,
					`price_out`,
					`price_out_snab`,
					`discount`,
					`tirage_json`,
					`print_z`,
					`standart`,
					`shipping_time`,
					`shipping_date`,
					`suppliers_id`,
					`suppliers_name`,
					`note`,
					`create_date`,
					`no_cat_json`,
					`status_snab`,
					`snab_end_work`,
					`query_send_by_snab`,
					`snab_comment`,
					`extended_rights_for_manager`,
					`work_days`,
					`maket_date` FROM `'.RT_DOP_DATA.'` WHERE `'.RT_DOP_DATA.'`.`id` IN ('.$id.')
			';

			$result = $mysqli->query($query) or die($mysqli->error);
			$new_id = $mysqli->insert_id;
			// запрашиваем услуги по старому id
			// получаем доп инфо
			$query = "SELECT * FROM  `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$uslugi = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$uslugi[] = $row;
				}
			}
			
			//составляем множественный запрос на запись всех услуг в базу истории
			foreach ($uslugi as $key1 => $usl) {
				$query ="INSERT INTO `".DOP_USLUGI_HIST."` SET ";
				$n= 0;
				foreach ($usl as $key2 => $value2) {
					$query .= ($n>0)?', ':'';
					if($key2=='dop_row_id'){
						// устанавливаем родительский id от только что созданной записи dop_data
						$query .="`".$key2."` = '".$new_id."'";
					}else if($key2=='id'){
						// очищаем значение idв базе установлен автоинкремент
						$query .="`".$key2."` = ''";
					// }else if($key2=='creator_id'){ 
					// 	// ставим создателем услуг админу, чтобы никто не удалил
					// 	$query .="`".$key2."` = '42'";
					}else{
						$query .="`".$key2."` = '".$value2."'";
					}		            
					$n++;
				}
				$query .="; ";

				if($query!='')$result = $mysqli->query($query) or die($mysqli->error);
			}
			// print_r($uslugi);
			// echo $query;


		}
	}

	public function change_status_gl_Database(){
		global $mysqli;
		// получаем id dop_data строк в которых будем менять статус
		$id_dop_data = implode(",", $this->POST['variants_arr']); 
		
		// получаем новый статус
		$new_status = $this->POST['new_status'];
		
		// если новый статус - это запрос на перерасчёт:
		if($new_status == 'on_recalculation_snab'){

			//копируем все строки которые были отправлены на перерасчёт в таблицу HISTORY
			$this->copy_variants_to_history_Database($id_dop_data);
		}

		// получаем кирилическое название
		$new_status_rus = $this->status_snab[$new_status]['name'];

		// меняем
		$query = "UPDATE `".RT_DOP_DATA."` SET 
		`status_snab`='".$new_status."'";
		// если это конечный расчёт  от снаба, пишем дату этого расчёта
		if($new_status == 'calculate_is_ready'){
			$query .= ', `snab_end_work` = NOW()';

		}
		// вборка id
		$query .= " WHERE `id` IN (".$id_dop_data.");";

		// получаем новые функцианальные кнопки для нового статуса для данной в кладки
		
		$result = $mysqli->query($query) or die($mysqli->error);
		

		//ФОРМИРУЕМ ОТВЕТ СЕРВЕРА ДЛЯ ИЗМЕНЕНИЯ html НА СТРАНИЦЕ
		// echo '{"new_name":"'.$new_status_rus.'","new_status":"'.$new_status.'",new_buttons":"'.base64_encode($this->get_top_funcional_byttun_for_user_Html($new_status)).'"}';
		// В ВЕРСИИ 1.1 будем вносить правки в html в соответствии с ответом от сервера
		// сейчас при получении ответа - просто перегружаем страницу яваскриптом
		echo '{"response":"OK"}';

	}

	public function change_status_gl_pause_Database(){
		global $mysqli;
		// получаем id dop_data строк в которых будем менять статус
		$id_dop_data = implode(",", $this->POST['variants_arr']); 
		$status =$this->POST['status'];

		// если позиции уже на паузе - снимаеем их с паузы
		if(substr_count($status, '_pause')){			
			// меняем
			$status = str_replace('_pause', '', $status);
			$query = "UPDATE `".RT_DOP_DATA."` SET 
			`status_snab`='".$status."'";

		}else{
			// меняем
			$status .= '_pause';
			$query = "UPDATE `".RT_DOP_DATA."` SET 
			`status_snab`='".$status."'";
		}


		
		// если это конечный расчёт  от снаба, пишем дату этого расчёта
		if($status == 'calculate_is_ready'){
			$query .= ', `snab_end_work` = NOW()';

		}
		// вборка id
		$query .= " WHERE `id` IN (".$id_dop_data.");";

		$result = $mysqli->query($query) or die($mysqli->error);
		
		// В ВЕРСИИ 1.1 будем вносить правки в html в соответствии с ответом от сервера
		// сейчас при получении ответа - просто перегружаем страницу яваскриптом
		echo '{"response":"OK"}';

	}


	// выводит общую информацию по ВАРИАНТУ из json
	public function variant_no_cat_json_Html($arr,$type_product,$pause,$edit_true){

		$FORM = $this->FORM; /*Экземпляр класса форм*/
		// определяем редакторов для полей (html тегов)
		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'
		if($pause){$this->edit_snab=$this->edit_men='';}

		// обнуляем все права при $edit_true == false
		if($edit_true == false){
			$this->edit_men = '';
			$this->edit_snab = '';
			$this->edit_admin = '';
			$pause = 1;
		}
		
		$html = '';

		// если у нас есть описание заявленного типа товара
		if(isset($FORM->form_type[$type_product])){
			$names = $FORM->form_type[$type_product]; // массив описания хранится в классе форм
			$html .= '<div class="table inform_for_variant">';
			foreach ($arr as $key => $value) {
				$html .= '
					<div class="row">
						<div class="cell" >'.$names[$key]['name'].'</div>
						<div class="cell" data-type="'.$key.'" '.$this->edit_admin.$this->edit_snab.'>';
				$html .= $value;
				$html .='</div>
					</div>
				';
			}
			$html .= '</div>';
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}

	}


	public function change_no_cat_json_Database(){
		// echo '<pre>';
		// print_r();
		// echo '</pre>';

		global $mysqli;
		// $query = "SELECT * FROM `".RT_DOP_DATA."` WHERE id = '".$this->POST['id_dop_data']."' GROUP BY `status_snab`";
		// $result = $mysqli->query($query) or die($mysqli->error);				
		// $arr = array();
		// if($result->num_rows > 0){
		// 	while($row = $result->fetch_assoc()){
		// 		$arr = $row;
		// 	}
		// }
		// echo '<pre>';
		// print_r(json_decode($arr['no_cat_json']));
		// echo '</pre>'; 
		
		$query = "UPDATE `".RT_DOP_DATA."` SET 
		`no_cat_json`='".addslashes(json_encode($this->POST['data']))."' 

		WHERE `id`='".$this->POST['id_dop_data']."';
";
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"OK"}';
	}


	// выводит общую информацию по позиции из json, 
	// json был создан через класс форм заведения позициий
	public function dop_info_no_cat_Html($arr,$type_product){
		$html = '';
		
		$this->FORM = new Forms($this->GET,$this->POST,$this->SESSION);//для вызова следующего метода нужна информация из сласса форм
		
		// если у нас есть описание заявленного типа товара
		if(isset($this->FORM->form_type[$type_product])){
			$names = $this->FORM->form_type[$type_product]; // массив описания хранится в классе форм
			$html .= '<div class="table">';
			foreach ($arr as $key => $value) {
				$html .= '
					<div class="row">
						<div class="cell">'.$names[$key]['name'].'</div>
						<div class="cell">';
				$html .= implode(', ', $value);
				$html .='</div>
					</div>
				';
			}
			$html .= '</div>';
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			return $html;
		}else{// в случае исключения выводим массив, дабы было видно куда копать
			echo '<pre>';
			print_r($arr);
			echo '</pre>';
		}

	}


	static function get_uslugi_list_Database_Html($id=0){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			$html .= '<ul>';
			while($row = $result->fetch_assoc()){
				if($row['id']!=6){// исключаем нанесение apelburg
				// запрос на детей
				$child = self::get_uslugi_list_Database_Html($row['id']);
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<li data-id="'.$row['id'].'" '.(($child=='')?'class="may_bee_checked"':'').'>'.$row['name'].' '.$child.'</li>';
				}
			}
			$html.= '</ul>';
		}
		return $html;
	}

	//удаление услуги
	static function del_uslug_Database($uslugi_id){
		global $mysqli;
		$query = "DELETE FROM  `".RT_DOP_USLUGI."` WHERE  `id` = '".$uslugi_id."';
";
		// echo $query; echo  '   ';
		$result = $mysqli->query($query) or die($mysqli->error);
	}

	// добавить доп услугу для варианта
	public function add_uslug_Database_Html($id_uslugi,$dop_row_id,$quantity){
		// определяем редакторов для полей (html тегов)
		$this->edit_admin = ($this->user_access == 1)?' contenteditable="true" class="edit_span"':'';
		$this->edit_men = ($this->user_access == 5)?' contenteditable="true" class="edit_span"':'';
		$this->edit_snab = ($this->user_access == 8)?' contenteditable="true" class="edit_span"':'';
		// '.$this->edit_admin.$this->edit_snab.$this->edit_men.'


		global $mysqli;
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `id` = '".$id_uslugi."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$usluga = array();
		if($result->num_rows > 0){		
			while($row = $result->fetch_assoc()){
				$usluga = $row;
			}		
		}

		// если массив услуг пуст
		if(empty($usluga)){return 'такой услуги не существует';}




		// получаем флаг если этой услуги ещё нет и придётся формировать имя группы услуг
		$flag = $this->check_parent_exists_Database_Int($dop_row_id,$usluga['parent_id']);


		// вставляем новую услугу в базу
		$query ="INSERT INTO `".RT_DOP_USLUGI."` SET
		             `dop_row_id` = '".$dop_row_id."',
		             `uslugi_id` = '".$id_uslugi."',
					 `glob_type` = 'extra',
					 `price_in` = '".$usluga['price_in']."',
					 `price_out` = '".$usluga['price_out']."',
					 `price_out_snab` = '".$usluga['price_out']."',
					 `for_how` = '".$usluga['for_how']."',
					 `creator_id` = '". $this->user_id."',
					 `quantity` = '".$quantity."'";
		$result = $mysqli->multi_query($query) or die($mysqli->error);

		// формируем массив для генерации HTML выдачи для ajax
		// ajax примет html и добавить на страницу
		$NEW_usl[0]['id'] = $mysqli->insert_id;
		$NEW_usl[0]['dop_row_id'] = $dop_row_id;
		$NEW_usl[0]['uslugi_id'] = $id_uslugi;
		$NEW_usl[0]['glob_type'] = 'extra';
		$NEW_usl[0]['price_in'] = $usluga['price_in'];
		$NEW_usl[0]['price_out'] = $usluga['price_out'];
		$NEW_usl[0]['price_out_snab'] = $usluga['price_out'];
		$NEW_usl[0]['for_how'] = $usluga['for_how'];
		$NEW_usl[0]['quantity'] = $quantity;
		$NEW_usl[0]['creator_id'] = $this->user_id;
		
		// генерим html выдачу для ajax
		$html = $this->uslugi_template_Html($NEW_usl, $flag);

		echo '{"response":"close_window","name":"add_uslugu","parent_id":"'.$usluga['parent_id'].'","html":"'.base64_encode($html).'"}';
	}

	public function change_dop_data_Database(){
		// $this->POST['parice_in'];
		// $this->POST['parice_out'];
		// $this->POST['parice_out_snab'];
		// $this->POST['dop_data_id'];

		global $mysqli;
		$query = "UPDATE `".RT_DOP_DATA."` SET 
		`price_in`='".$this->POST['price_in']."', 
		`price_out`='".$this->POST['price_out']."',
		`price_out_snab`='".$this->POST['price_out_snab']."' 

		WHERE `id`='".$this->POST['dop_data_id']."';
";
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"OK"}';

	}

	public function save_edit_price_dop_uslugi_Database(){
		global $mysqli;
		$query = '';
		foreach ($this->POST['data'] as $id => $value) {
			// $id
			// $value['price_out_snab'];
			// $value['price_out'];

			$query .= "UPDATE `".RT_DOP_USLUGI."` SET
			`price_out`='".$value['price_out']."',
			`price_out_snab`='".$value['price_out_snab']."' 
			WHERE `id`='".$id."';";
		}
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		echo '{"response":"OK"}';

	}

	private function check_parent_exists_Database_Int($dop_row_id, $parent_id){
		global $mysqli;
		
		
		$query = "SELECT `parent_id` FROM `".OUR_USLUGI_LIST."` WHERE `id` IN (SELECT `uslugi_id` FROM `".RT_DOP_USLUGI."` WHERE dop_row_id = '".$dop_row_id."'
) AND `parent_id` = '".$parent_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows == 0){
			$ret = '0';
		}else{
			$ret = '1';
		}

		return $ret;		
	}

	public function get_suppliers_Database_Array(){
		global $mysqli;

	}
}