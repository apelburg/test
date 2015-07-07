<?php
class Position_catalog{
	// экземпляр класса mysqli
	private $mysqli;

	// глобальные массивы
	private $POST;
	private $GET;
	private $SESSION;

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
	
	function __construct($get,$post,$session,$user_access){
		$this->GET = $get;
		$this->POST = $post;
		$this->SESSION = $session;

		$this->user_id = $session['access']['user_id'];

		$this->user_access = $user_access;

		// обработчик AJAX // в первой редакции.... РАБОЧИЙ !!!!
		if(isset($this->POST['global_change']) && isset($this->POST['change_name'])){
			$this->_AJAX_first();
		}

		// обработчик AJAX // в конечной редакции... теперь все обработчики будут 
		// передававться через ключ AJAX
		if(isset($this->POST['AJAX'])){
			$this->_AJAX_();
		}

	}

	/////////////////  AJAX START ///////////////// 
	////////////      AJAX STATIC    /////////////////
	// вариант 2
	// для организации возможности вызова методов AJAX из других скриптов 
	// без создания экземпляра класса пришлось сделать их статичными
	private function _AJAX_(){
		$method_AJAX = $this->POST['AJAX'].'_AJAX';

		// если в этом классе существует такой метод - выполняем его и выходим
		if(method_exists($this, $method_AJAX)){
			self::$method_AJAX();
			exit;
		}		
		
	}
	/////////////////  AJAX METHODs  ///////////////// 

	static function add_new_usluga_AJAX(){
		// инициализация класса формы
		$post = isset($_POST)?$_POST:array();
		$get = isset($_GET)?$_GET:array();
		$FORM = new Forms($get,$post,$_SESSION);

		// инициализация класса работы с некаталожными позициями
		$POSITION_NO_CAT = new Position_no_catalog($get,$post,$_SESSION);

		$POSITION_NO_CAT->add_uslug_Database_Html($_POST['id_uslugi'], $_POST['dop_row_id'], $_POST['quantity']);

		inset($POSITION_NO_CAT);
	}
	

	static function get_uslugi_list_Database_Html_AJAX(){
		// получение формы выбора услуги
		if($_POST['AJAX']=="get_uslugi_list_Database_Html"){
			$html = '<form>';
			$html.= '<div class="lili lili_head"><span class="name_text">Название услуги</span><div class="echo_price_uslug"><span>$ вход.</span><span>$ исх.</span><span>за сколько</span></div></div>';
			$html .= self::get_uslugi_list_Database_Html_();
			$html .= '<input type="hidden" name="id_uslugi" value="">';
			$html .= '<input type="hidden" name="dop_row_id" value="">';
			$html .= '<input type="hidden" name="quantity" value="">';
			$html .= '<input type="hidden" name="AJAX" value="add_new_usluga">';
			$html .= '</form>';
			echo $html;
		}
	}

	static function get_uslugi_list_Database_Html_($id=0,$pad=30){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$price = '<div class="echo_price_uslug"><span></span><span></span></div>';
				if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
					# Это услуги НЕ из КАЛЬКУЛЯТОРА
					// запрос на детей
					$child = self::get_uslugi_list_Database_Html_($row['id'],($pad+30));
					
					$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					

					// присваиваем конечным услугам класс may_bee_checked
					$html.= '<div data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}else{
					# Это услуги из КАЛЬКУЛЯТОРА
					// запрос на детей
					$child = self::get_uslugi_list_Database_Html_($row['id'],($pad+30));

					$price = ($child =='')?'<div class="echo_price_uslug"><span>&nbsp;</span><span>&nbsp;</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
					// присваиваем конечным услугам класс may_bee_checked
					$html.= '<div data-id="'.$row['id'].'" data-type="'.$row['type'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
				}
			}
		}
		return $html;
	}




	////////////      AJAX PRIVATE    /////////////////
	// вариант 1, 
	// все новые вызовы пишется по варианту 2 !!!!!
	private function _AJAX_first(){ // router
		$method_AJAX = $this->POST['change_name'].'_AJAX';
		$this->$method_AJAX();
	}

	private function get_uslugi_list_Database_Html_7777($id=0){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			$html .= '<ul>';
			while($row = $result->fetch_assoc()){
				if($row['id']!=6){// исключаем нанесение apelburg
				// запрос на детей
				$child = $this->get_uslugi_list_Database_Html_($row['id']);
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<li data-id="'.$row['id'].'" '.(($child=='')?'class="may_bee_checked"':'').'>'.$row['name'].' '.$child.'</li>';
				}
			}
			$html.= '</ul>';
		}
		return $html;
	}

	private function size_in_var_AJAX(){
		global $mysqli;

		$query = "SELECT `tirage_json`,`print_z` FROM ".RT_DOP_DATA." WHERE `id` = '".$this->POST['id']."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$json = '';
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$json = $row['tirage_json'];
				$print_z = $row['print_z'];
			}
		}
		$arr_json = json_decode($json,true);

		$arr_json[$this->POST['key']][$this->POST['dop']] = $this->POST['val'];
		/*
			ОБСУДИТЬ С АНДРЕЕМ РАСПРЕДЕЛЕНИЕ ТИРАЖА 
			ВВЕДЁННОГО В ОБЩЕЕ поле
		*/
		// $quantity = 0;
		// foreach ($arr_json as $key => $value) {
		// 	$quantity += $arr_json[$key]['tir'];
		// 	if($print_z){$quantity += $arr_json[$key]['dop'];}
		// }

		$query = "UPDATE `".RT_DOP_DATA."` SET `tirage_json` = '".json_encode($arr_json)."', `quantity` = '".$quantity."' WHERE  `id` ='".$this->POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	private function size_in_var_all_AJAX(){
		global $mysqli;
		// echo "<pre>";
		// print_r($this->POST);
		// echo "</pre>";
		$tir = $this->POST['val']; // array / тиражи
		$key2 = $this->POST['key']; // array / id _ row size
		$dop = $this->POST['dop']; // array / запас
		$id = $this->POST['id']; // array / id 

			//print_r($this->POST['id']);exit;
		$query = "SELECT `tirage_json` FROM ".RT_DOP_DATA." WHERE `id` = '".$id[0]."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$json = '';
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$json = $row['tirage_json'];
				//echo $row['tirage_json'];
			}
		}
		//echo $json;
		//$r = $json;
		$arr_json = json_decode($json,true);
		$sum_tir = 0;
		$sum_zap = 0;
		foreach ($key2 as $key => $value) {
			//echo $value;
			$arr_json[$value]['dop'] = $dop[$key];
			$arr_json[$value]['tir'] = $tir[$key];

			$sum_zap += $dop[$key];
			$sum_tir += $tir[$key];
		}

		// $arr_json[$this->POST['key']][$this->POST['dop']] = $this->POST['val'];
		//echo $r .'   -   ';
		//echo json_encode($arr_json);
		$query = "UPDATE `".RT_DOP_DATA."` SET `quantity` = '".$sum_tir."',`zapas` = '".$sum_zap."',`tirage_json` = '".json_encode($arr_json)."' WHERE  `id` ='".$id[0]."'";	
		// // echo $query;			
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	private function change_status_row_AJAX(){
		global $mysqli;

		$color = $this->POST['color'];
		$id_in = $this->POST['id_in'];
		$query  = "UPDATE `".RT_DOP_DATA."` SET `row_status` = '".$color."' WHERE  `id` IN (".$id_in.");";
		echo $query;
		// echo '<pre>';
		// print_r($this->POST);
		// echo '</pre>';
		$result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"1","text":"test"}';
		exit;
	}
	private function change_archiv_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` SET `row_status` = 'green' WHERE  `id` ='".$this->POST['id']."';";
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		// $result = $mysqli->query($query) or die($mysqli->error);
		echo '{"response":"1","text":"test"}';
		exit;
	}
	private function change_tirage_pz_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` SET `print_z` = '".$this->POST['pz']."' WHERE  `id` ='".$this->POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;

	}
	private function change_variante_shipping_time_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_time` = '".$this->POST['time']."', `standart` = '' WHERE  `id` ='".$this->POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	private function new_variant_AJAX(){
		global $mysqli;

		// собираем запрос, копируем строку в БД
		$query = "INSERT INTO `".RT_DOP_DATA."` (row_id, row_status,quantity,price_in, price_out,discount,tirage_json) (SELECT row_id, row_status,quantity,price_in, price_out,discount,tirage_json FROM `".RT_DOP_DATA."` WHERE id = '".$this->POST['id']."')";
		$result = $mysqli->query($query) or die($mysqli->error);
		// запоминаем новый id
		$insert_id = $mysqli->insert_id;
		// узнаем количество строк
		$query = "SELECT COUNT( * ) AS `num`
				FROM  `os__rt_dop_data` 
				WHERE  `row_id` ='1'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$num_rows = $row['num'];
			}
		}
		echo '{ "response":"1",
				"text":"test",
				"new_id":"'.$insert_id.'",
				"num_row":"'.($num_rows-1).'",
				"num_row_for_name":"Вариант '.$num_rows.'"
				}';
		exit;
	}
	private function save_standart_day_AJAX(){
		global $mysqli;

		$query = "UPDATE `".RT_DOP_DATA."` 
					SET `shipping_time` = '00:00:00',
					`shipping_date` = '0000-00-00' ,
					`standart` =  '".$this->POST['standart']."'
					WHERE  `id` ='".$this->POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	private function change_variante_shipping_date_AJAX(){
		global $mysqli;

		$date = $this->POST['date'];
		$date = strtotime($date);
		$date = date("Y-m-d", $date);
		$query = "UPDATE `".RT_DOP_DATA."` SET `shipping_date` = '".$date."' , `standart` = '' WHERE  `id` ='".$this->POST['id']."'";	
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		exit;
	}
	///////////    AJAX END   /////////////////

	public function get_all_info($art_id){
		$this->color = $this->get_color($art_id);		
		$this->material = $this->get_material($art_id);
		$this->get_print_mode = $this->get_print_mode($art_id);

		global $mysqli;
		$query = "SELECT * FROM `".BASE_TBL."` WHERE `id` = '".(int)$art_id."'";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				
			}
		}
	}

	private function get_color($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_COLORS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['color'];
			}
		}
		return $arr;
	}

	private function get_material($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_MATERIALS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['material'];
			}
		}
		return $arr;
	}


	private function get_print_mode($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['print'];
			}
		}
		return $arr;
	}

	public function get_dop_uslugi($id_dop_data){
		global $mysqli;
		//$query = "SELECT * FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".(int)$id_dop_data."'";
		$query = "SELECT  `".RT_DOP_USLUGI."`. * ,  `rt_uslugi_gen`.`name` AS  `group_name` ,  `".OUR_USLUGI_LIST."`.`name` AS  `name` ,  `rt_uslugi_gen`.`type` AS  `group_type` ,  `".OUR_USLUGI_LIST."`.`type` AS  `type` 
			FROM  `".RT_DOP_USLUGI."` 
			INNER JOIN  `".OUR_USLUGI_LIST."` ON  `".OUR_USLUGI_LIST."`.`id` =  `".RT_DOP_USLUGI."`.`uslugi_id` 
			INNER JOIN  `".OUR_USLUGI_LIST."` AS  `rt_uslugi_gen` ON  `".OUR_USLUGI_LIST."`.`parent_id` =  `rt_uslugi_gen`.`id` 
			WHERE  `".RT_DOP_USLUGI."`.`dop_row_id` =  '".$id_dop_data."'";
		/*
			// запрос без переменных
			SELECT  `os__rt_dop_uslugi`. * ,  `rt_uslugi_gen`.`name` AS  `group_name` ,  `os__rt_uslugi`.`name` AS  `name` ,  `rt_uslugi_gen`.`type` AS  `group_type` ,  `os__rt_uslugi`.`type` AS  `type` 
			FROM  `os__rt_dop_uslugi` 
			INNER JOIN  `os__rt_uslugi` ON  `os__rt_uslugi`.`id` =  `os__rt_dop_uslugi`.`uslugi_id` 
			INNER JOIN  `os__rt_uslugi` AS  `rt_uslugi_gen` ON  `os__rt_uslugi`.`parent_id` =  `rt_uslugi_gen`.`id` 
			WHERE  `os__rt_dop_uslugi`.`dop_row_id` =  '1'
		*/


		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}

	public function get_dop_uslugi_html($id){
		$arr = $this->get_dop_uslugi($id);
		// ob_start();	
		// echo "<pre>";
		// print_r($arr);
		// echo "</pre>";
		// $html = ob_get_contents();
		// ob_get_clean();
		// return $html;
		$html = '';
		$general_name = '';
		$percent_nacenki = 0;

		foreach ($arr as $k => $val) {

			$percent_nacenki = round((($val['price_out']-$val['price_in'])*100/$val['price_in']),2);

			if( $general_name != trim($val['group_name']) ){
				
				$general_name = trim($val['group_name']);
					$html .='
							<tr>
								<th colspan="7">'.$general_name.'</th>
							</tr>
					';
			}			

			$html .='<tr class="'.$val['for_how'].'">';
			$html .='<td>'.$val['name'].'</td>';
			$html .='<td class="price_in"><span>'.$val['price_in'].'</span> р.</td>';
			$html .='<td class="percent_nacenki"><span>'.$percent_nacenki.'</span>%</td>';
			$html .='<td  class="price_out"><span>'.$val['price_out'].'</span> р.</td>';
			$html .='<td  class="pribl"><span>'.($val['price_out'] - $val['price_in']).'</span> р.</td>';
			$html .='<td><span class="edit_row_variants"></span></td>';
			$html .='<td><span class="del_row_variants"></span></td>';
			$html .='</tr>';	
		}
		//return $html;


		$uslugi = $this->uslugi_template_Html();

		
			
		
		return $html.$uslugi;
	}

	public function uslugi_template_Html($arr=array()){
		$html = '';
		$html .='							<tr>
										<th colspan="7"><span class="add_row">+</span>печать</th>
									</tr>
									<tr >
										<td>Термотрансфер, 1цв</td>
										<td> 133,00р</td>
										<td rowspan="2" class="percent_nacenki"><span>20</span>%</td>
										<td>195,00р</td>
										<td>12,00</td>
										<td rowspan="2"><span class="edit_row_variants"></span></td>
										<td rowspan="2"><span class="del_row_variants"></span></td>
									</tr>';
		return $html;
	}

	public function get_dop_params($art_id){
		// выгружает данные запроса в массив
		global $mysqli;
		$query = "SELECT * FROM `".BASE_DOP_PARAMS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;

		/*$query = "SELECT `".BASE_DOP_PARAMS_TBL."`.* , `".RT_ART_SIZE."`.`variant_id`,`".RT_ART_SIZE."`.`tirage`,`".RT_ART_SIZE."`.`id` AS id2, `".RT_ART_SIZE."`.`tirage_dop` 
		FROM `".BASE_DOP_PARAMS_TBL."` 
		left JOIN `".RT_ART_SIZE."` ON `".RT_ART_SIZE."`.`size_id` = `".BASE_DOP_PARAMS_TBL."`.`id`  
		WHERE `".BASE_DOP_PARAMS_TBL."`.`art_id` = '".$art_id."'";
*/
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}
	public function generate_variants_menu_OLD($variants,$draft_enable){
		$html = '';
		for ($i=0; $i < count($variants); $i++) { 
			// если есть $draft_enable=1, т.е. мы знаем, что в списке есть основной
			// вариант, то грузим его выбранным по умолчанию
			$checked = '';
			if($draft_enable){
				$draft = ($variants[$i]['draft']=='0')?'osnovnoy checked':'';
			}else{
			// если же все варианты являются черновиками, то выбираем первый по списку
			// для одного черновика схема отработает соответственно
				$checked = ($i==0)?'checked':'';				
			}
			$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$draft.' '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants['row_status'].'"></span></li>';
		}
		return $html;
	}


	public function generate_variants_menu($variants,$dop_enable){		
		$html = ''; // контент функции
		
		$ch = 0; // счетчик количества выбранных элементов, может не больше одного
		
		// проверяем наличие green расчётов
		$isset_green = $dop_enable[0];
		// проверяем наличие grey расчётов
		$isset_grey = $dop_enable[1];
		
		for ($i=0; $i < count($variants); $i++) { 
			$checked = ''; // имя класса для выбранного элемента

			$var = $variants[$i]['row_status'];

			// если это зона записи red, а архив нам не нужно показывать переходим к следующей интерации цикла
			if((!isset($_GET['show_archive']) && ($isset_green || $isset_grey)) && $var=='red'){ continue;}

			switch ($var) {
				case 'green':// не история - рабочий вариант расчёта
					// может входить в КП
					if($ch < 1){$checked='checked';$ch++;}					
					$html .='<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
				break;
				
				case 'grey':// не история - вариант расчёта не учитывается в РТ
					// вариант расчёта не входит в КП	
				  	if (!$isset_green && $ch == 0){$checked='checked';$ch++;}
					$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
				break;			
				
				default: // вариант расчёта red (архив), остальное не важно
					if (!$isset_green && !$isset_grey && $ch== 0){$checked='checked';$ch++;}
					$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name show_archive">Вариант '.($i+1).'<span class="variant_status_sv '.$variants[$i]['row_status'].'"></span></li>';
				break;
			}
		}
		return $html;
	}

	public function get_size_table($dop_params_arr, $val){
		// преобразует массив дополнительных параметров в таблицу размеров

		// выборка данных о введённых ранее размерах из строки JSON 
		$tirage_json = json_decode($val['tirage_json'], true);

		$html = "";
		if(count($dop_params_arr)==0){
			$html = "Дополнительная информация отсутствует. Обратитесь к администратору.";
			return $html;
		};

		// собираем таблицу с доп размерами
		$html = '
			<table>
				<tr>
					<th>Размеры</th>
					<th>на складе</th>
					<th>свободно</th>
					<th>тираж</th>
					<th>пригон</th>
				</tr>
		';
		
		
		// подсчитываем сумму заказа и общий остаток для их сравнения
		$summ_zakaz = 0;
		$summ_ostatok = 0;
		
		// флаг под заказ
		$pod_zakaz = 0;

		foreach ($dop_params_arr as $k => $v) {
			$value = (isset($tirage_json[$v['id']]['tir']))?$tirage_json[$v['id']]['tir']:0;
			$value_dop = (isset($tirage_json[$v['id']]['dop']))?$tirage_json[$v['id']]['dop']:0;
			$summ_ostatok += $v['ostatok_free'];
			$summ_zakaz += $value + $value_dop;
			if($v['ostatok_free']<($value + $value_dop)){$pod_zakaz = 1;}
		}
		// перебираем строки размерной таблицы
		foreach ($dop_params_arr as $k => $v) {
			$value = (isset($tirage_json[$v['id']]['tir']))?$tirage_json[$v['id']]['tir']:0;
			$value_dop = (isset($tirage_json[$v['id']]['dop']))?$tirage_json[$v['id']]['dop']:0;
			$no_edit_class = (($v['ostatok_free']=='0' && $summ_ostatok>=$summ_zakaz && $pod_zakaz!=1)?' input_disabled':'');
			$rearonly = (($v['ostatok_free']=='0' && $summ_ostatok>=$summ_zakaz  && $pod_zakaz!=1)?'readonly="readonly"':'');
			$html .= '
					<tr class="size_row_tbl">
						<td>'.$v['size'].'</td>
						<td>'.$v['ostatok'].'<br><span>(в пути) '.$v['on_way_free'].'</span></td>
						<td class="ostatok_free">'.$v['ostatok_free'].'</td>
						<td><input type="text" data-dop="tir" data-var_id="'.$val['id'].'" class="val_tirage'.$no_edit_class.'" data-id_size="'.$v['id'].'"  value="'.$value.'" '.$rearonly.'></td>
						<td><input type="text" data-dop="dop" data-var_id="'.$val['id'].'" class="val_tirage_dop'.$no_edit_class.'" data-id_size="'.$v['id'].'"  value="'.$value_dop.'" '.$rearonly.'></td>
					</tr>
			';
		}
		$html .= '</table>';

		$html .= '
			<div class="sevrice_button_size_table">
				<span onclick="chenge_hidden_input_status(\'0\',this);" class="btn_var_std '.(($pod_zakaz==1)?'checked':'').'" name="order">под заказ</span>
				<span onclick="chenge_hidden_input_status(\'1\',this);" class="btn_var_std '.(($pod_zakaz==0)?'checked':'').'" name="reserve">под резерв</span>
			</div>
			';

		return $html;

	}


	// далее старые функции 
	public function fetch_images_for_article2($art){
		if(!$art || $art=='0'){return array();}
		global $db;
		// основная картинка
		$i=0;
		$query = "SELECT*FROM `".IMAGES_TBL."` WHERE art_id ='".$art."' AND size='big' ORDER BY  id ASC";
		// echo $query;
		$result =mysql_query($query,$db) or die(mysql_error());
		//$counter = 0;
		if(mysql_num_rows($result)>0){
			while($item = mysql_fetch_assoc($result)){
			   $big_images_id[] = $item['id'];
			   if(!isset($main_img_src)) $main_img_src = checkImgExists( APELBURG_HOST.'/img/'.$item['name']);

			   if(mysql_num_rows($result)>1) $big_images[] = $item['name'];
			   
			}
		}
		else $main_img_src = checkImgExists(APELBURG_HOST.'/img/no_image.jpg');
	   	
		// вычисляем превьющки
		$query = "SELECT*FROM `".IMAGES_TBL."` WHERE art_id ='".$art."' AND size='small' ORDER BY  id ASC";
		$result =mysql_query($query,$db) or die(mysql_error());
		$counter = 0;
		$counter2 = 0;
		$counter3 = 0;
		$alt = (isset($name))?altAndTitle($name):'';
		// если артикул имеет больше одной картинки строим панель с превьюшками
		if(mysql_num_rows($result)>1){
		
			while($item = mysql_fetch_assoc($result)){
			
				//$deleting_img = (isset($_SESSION['access']['access']) &&  ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3))?'<div class="catalog_delete_img_link"><a href="#" title="удалить изображение из базы" data-del="'.APELBURG_HOST.'/admin/order_manager/?page=common&delete_img_from_base_by_id='.$item['id'].'|'.$big_images_id[$counter2++].'|'.$big_images[$counter3++].'|'.$item['name'].'"  onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a></div>':'3';
				$deleting_img = (isset($_SESSION['access']['access']) &&  ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3))?'<div class="catalog_delete_img_link"><a href="#" title="удалить изображение из базы" data-del="'.APELBURG_HOST.'/admin/order_manager/?page=common&delete_img_from_base_by_id='.$big_images[$counter3++].'|'.$item['name'].'"  onclick="if(confirm(\' изображение будет удалено из базы!\')){$.get( $(this).attr(\'data-del\'),function( data ) {});remover_image(this); return false; } else{ return false;}">&#215</a></div>':'';
				
				$previews_block[] = '<div  class="carousel-block"><img class="articulusImagesMiniImg imagePr" alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$item['name']).'" data-src_IMG_link="'.APELBURG_HOST.'/img/'.$big_images[$counter++].'">'.$deleting_img.'</div>';
				
			   //echo $item['size'].' '.$item['name'].'<br>';
			   $i++;
			}
		}
		if(isset($_SESSION['access']['access']) && ($_SESSION['access']['access']==1 || $_SESSION['access']['access']==3)){
			$previews_block[] = '<div  class="carousel-block" id="image_add"><img class="articulusImagesMiniImg imagePr" alt="" src="'.APELBURG_HOST.'/skins/images/general/add_image_d.png" data-src_IMG_link="'.APELBURG_HOST.'/skins/images/general/add_image_d.png"></div>';	
			$i++;	
		}
		if(isset($i) && $i>0){
			$string	= implode('',$previews_block);
			$html = '<div class="carousel shadow" style="">'.PHP_EOL;
			$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-left" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s2.png)"></a>'.PHP_EOL:'';
			$html .= '<div class="carousel-wrapper">'.PHP_EOL;
			$html .= '<div class="carousel-items">'.PHP_EOL;	
			$html .= $string;
			$html .= '</div>'.PHP_EOL;
			$html .= '</div>'.PHP_EOL;
			$html .= count($previews_block)>=3?'<a href="" class="articulusImagesArrow2 carousel-button-right" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s22.png); float:right; margin-top:-70px"></a>'.PHP_EOL:'';
			$html .= '</div>'.PHP_EOL;
			$previews_block = $html;
		}else{
			$previews_block = '<div>нет дополнительных картинок</div>';
		}
		return array('main_img_src' => $main_img_src,'previews_block' => $previews_block);
	}

	
	//функция вывода вариантов цветов, при нали, при кол-ве цветов более 6 - выводим стрелки прокрутки
	public function color_variants_to_html2($color_variants){
		//print_r($color_variants);//		
		foreach($color_variants as $item){ $block[] = '<div class="carousel-block"><a target="_blank" href="'.APELBURG_HOST.'/description/'.$item['id'].'/" border="0"><img class="carousel-block"  alt="" src="'.checkImgExists(APELBURG_HOST.'/img/'.$item['img']).'" ></a></div>'.PHP_EOL;}
		$string = implode('',$block);
		$html = '<div id="articulusImagesMiniImg" class="carousel shadow">'.PHP_EOL;
		$html .= count($block)>6?'<a href="" class="articulusImagesArrow1 carousel-button-left" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s1.png); margin-right:5px"></a>'.PHP_EOL:'';
		$html .= '<div class="carousel-wrapper">'.PHP_EOL;
		$html .= '<div class="carousel-items">';
		$html .=$string;
		$html .='</div>'.PHP_EOL;
		$html .='</div>'.PHP_EOL;
		$html .=count($block)>6?'<a href="" class="articulusImagesArrow1 carousel-button-right" style="background-image:url('.APELBURG_HOST.'/skins/images/general/artkart/s11.png); margin-left:5px; background-position: 3px"></a>'.PHP_EOL:'';
		$html .='</div>';
		return $html;
	}
	public function get_art_color_variants($art){
		function find_matches($art,$pattern){
			global $db;
			//выбираем id артикулов соответсвующих патерну
			$query = "SELECT id FROM `".BASE_TBL."` WHERE art != '".$art."' AND SUBSTRING(art,1,".strlen($pattern).")='".$pattern."'";
			$result = mysql_query($query,$db) or die(mysql_error());
			while($item = mysql_fetch_assoc($result)) $itog_ids_arr[] = $item['id'];		
			//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
				
			
			//отсекаем те артикулы которые у которых нулевые остатки и цена
			$arr15 = (isset($itog_ids_arr))?implode("','",$itog_ids_arr):'';
			$query = "SELECT art_id FROM `".BASE_DOP_PARAMS_TBL."` WHERE ( ostatok + on_way ) >= '0' AND price > '0' AND art_id IN('".$arr15."') GROUP BY art_id";
			
			$result = mysql_query($query,$db) or die(mysql_error());
			$itog_ids_arr = array();
			while($item = mysql_fetch_assoc($result)) $itog_ids_arr[] = $item['art_id'];
			//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
			
			//отсекаем те артикулы которые лежат в скрытых категориях
			$arr15 = implode("','",$itog_ids_arr);
			$query = "SELECT rel.article_id article_id, rel.category_id category_id
			           FROM `".BASE_ARTS_CATS_RELATION."` rel 
					   INNER JOIN `".GIFTS_MENU_TBL."` menu
					   ON  rel.category_id = menu.id
			           WHERE menu.hide != '1' AND rel.article_id IN('".$arr15."') ORDER BY rel.category_id ASC LIMIT 0,15";
			$result = mysql_query($query,$db) or die(mysql_error());
			$itog_ids_arr = array();
			while($item = mysql_fetch_assoc($result)){
				 // отказался от такого подхода  $hiden_cat_begining = get_menu_item_id(BEGINING_HIDEN_MENU_CATS);
				 // отказался от такого подхода  if((int)$item['category_id'] >= (int)$hiden_cat_begining['id']) break;
			     $itog_ids_arr[] = $item['article_id'];
			}
			//echo '<pre>';print_r($itog_ids_arr);echo '</pre>';
			
			
			//получаем изображения артикулов
			$arr15 = implode("','",$itog_ids_arr);
			$query = "SELECT base.id id,  base.art art, images.name name FROM `".BASE_TBL."` base
			          INNER JOIN `".IMAGES_TBL."` images 
					  ON  base.art = images.art  WHERE size = 'small' AND base.id IN('".$arr15."') GROUP BY  base.id ORDER BY images.id ASC";
			$result = mysql_query($query,$db) or die(mysql_error());
			while($item = mysql_fetch_assoc($result)){
				 $output[] = array('id'=>$item['id'],'art'=>$item['art'],'img'=>$item['name']);
			}
			//echo '<pre>';print_r($output);echo '</pre>';
			return  (isset($output))?$output:'';
			
		}
		
	    $prefix = substr($art,0,2);
		switch($prefix){
		   case '15':
		      // для Интерпрезента(15) следующее правило две цифры или латинские буквы заглавные с точкой  или слешем ними перед ними в конце номера артикула обозанчают цвет
		      if(!preg_match('/^(.*[^\.])(\.[\dA-Z]{2})$/',$art,$matches) && !preg_match('/^(.*[^\.])(\/[\dA-Z]{2})$/',$art,$matches)) return FALSE;
		      
		      break;
		   case '26':
		      // для Оазиса(26) следующее правило две цифры (с точкой  или без перед ними) в конце номера артикула обозанчают цвет
		      if(!preg_match('/^([\d]{7})(\.[\d]{2})$/',$art,$matches) && !preg_match('/^([\d]{7})([\d]{1})$/',$art,$matches)) return FALSE;
		      break;
		  case '37':
		  	  // для Проекта(37) следующее правило две цифры с точкой перед ними в конце номера артикула обозанчают цвет		
		      if(!preg_match('/^(.*[^\.])(\.[\d]{2})$/',$art,$matches)) return FALSE;
		      break;
		  case '59':
		      // для Макроса(59) следующее правило от 1 до 2 цифр с тире перед ними в конце номера артикула обозанчают цвет
		      if(!preg_match('/^(.*[^\.])(-[^-.]{1,2})$/',$art,$matches)) return FALSE;
		      break;
		  case 'e_':
		       // для Ебазара(e_) следующее правило от 3 до 6 СИМВОЛОВ с тире перед ними в конце номера артикула обозанчают цвет
		      if(!preg_match('/^(.*[^\.])(-[^-.]{3,6})$/',$art,$matches)) return FALSE;
		      break;
		  default:
		      return FALSE;
			  break;
		
		}
			return find_matches($art,$matches[1]);	
		//exit;
	}

	// получаем все варианты просчёта по данному артикулу
	public function get_all_variants_info_Database_Array($id){
		global $mysqli;

		//$query = "SELECT `".RT_DOP_DATA."`.*,`".RT_ART_SIZE."`.`tirage_json`,`".RT_ART_SIZE."`.`id` AS `id_2` FROM `".RT_DOP_DATA."` INNER JOIN `".RT_ART_SIZE."` ON `".RT_ART_SIZE."`.`variant_id` = `".RT_DOP_DATA."`.`id` WHERE `".RT_DOP_DATA."`.`row_id` = '".$id."'";
		$query = "SELECT `".RT_DOP_DATA."`.*, DATE_FORMAT(shipping_date,'%d.%m.%Y') AS `shipping_date` FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$id."'";
		
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		// $this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$variants[] = $row;
			}
		}	
		return $variants;
	}


	function __destruct(){}

}
