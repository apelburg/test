<?php 
/*
в конце названий методов указан формат в котором выдаётся информация по окончании работы метода
Html, Array, String, Int
Если метод работает с базой сначала указывается обравиатура Database 
и уже потом Тип возвращаемых данных






PS было бы неплохо взять взять это за правило 

*/
    class Forms{
    	private $GET;
		private $POST;
		private $SESSION;
		// id пользователя
    	private $user_id;

    	// тип продукта с которым работает форма
    	private $type_product;
		
		// сюда будем сохранять id html элементов формы, чтобы иметь понятие какие id мы использовать уже не можем
     	// id в основной своей массе используются для label
     	private $id_closed =array(); 
     	
     	// html код отвечающий за удаление записи, 
     	// которую добаил менеджер для личного пользования
     	private $span_del = '<span class="delete_user_val">X</span>';

     	# ОПИСАНИЕ ТИПОВ ТОВАРОВ 
			//cat  - каталог
			//pol - полиграфия листовая
			//pol_many - полиграфия многолистовая
			// calendar - икалендарь
			//packing - упаковка картон
			//packing_other - упаковка другая
			//ext - сувениры под заказ
			//ext_cl - сувениры клиента

     	# true/false разрешаем или запрещаем работу класса с ними
		public $arr_type_product = array(
			'cat' => array(
				'name' => 'Продукция с сайта',
				'readonly' => true,
				'access' => false
				),
			'pol' => array(
				'name' => 'Полиграфия / листовая',
				'readonly' => false,
				'access' => true
				),
			'pol_many' => array(
				'name' => 'Полиграфия / многолистовая',
				'readonly' => true,
				'access' => true
				),
			'calendar' => array(
				'name' => 'Полиграфия / календари',
				'readonly' => true,
				'access' => true
				),
			'packing' => array(
				'name' => 'Упаковка картонная',
				'readonly' => true,
				'access' => true
				),
			'packing_other' => array(
				'name' => 'Упаковка разная',
				'readonly' => true,
				'access' => true
				),
			'ext' => array(
				'name' => 'Сувениры под заказ',
				'readonly' => true,
				'access' => true
				),
			'ext_cl' => array(
				'name' => 'Сувениры клиента',
				'readonly' => true,
				'access' => true
				),
			);

     	// перечисление разрешённых разделов полей 
     	// а так же некоторая необходимая для их обработки информация
     	// имя на кириллице
     	// доп описание по заполняемой форме
     	// наличие кнопки клонирования раздела формы
     	// наличие кнопки добавления своего варианта // копирует самый нижний imput
     	public $form_type = array(
     		'pol' => array( // поисание формы для полиграфической продукции
     			'name_product'=>array(
     				'name'=>'Наименование',
     				'moderate'=>true,
     				'note'=>'укажите название изделия',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true,
     				'cancel_selection' =>false // кнопка отмены всех выбранных
     				),
     			'product_dop_text'=>array(
     				'name'=>'Доп. наименование',
     				'moderate'=>false,
     				'note'=>'текст который будет виден в РТ сразу же за Намименованием. К примеру: Открытка № 1, где "№ 1" - это доп. наименование',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'quantity'=>array(
     				'name'=>'Тираж',
     				'moderate'=>true,
     				'note'=>'укажите тираж изделий',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true,
     				'cancel_selection' =>false
     				),
     			'format'=>array(
     				'name'=>'Формат',
     				'moderate'=>true,
     				'note'=>'укажите формат (мм)',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true,
     				'cancel_selection' =>false
     				),
     			'material' =>array(
     				'name'=>'Материал',
     				'moderate'=>true,
     				'note'=>'укажите материал (картон не мелованный, дизайнерский, бумага мелованная и т.д.), название материала (Splendorgel-Сплендоргель)',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'plotnost' =>array(
     				'name'=>'Плотность материала',
     				'note'=>'плотность (130гр, 170гр,300гр и т.д.)',
     				'moderate'=>true,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'type_print' =>array(
     				'name'=>'Вид печати',
     				'moderate'=>false,
     				'note'=>'укажите вид печати и кол-во цветов (4+0 и т.д.) + "другое" , выбрать Pantone если есть дополнительная печать пятым цветом',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'change_list' => array(
     				'name'=>'Изменение листа',
     				'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>true,
     				'cancel_selection' => true
     				),
     			'laminat' => array(
     				'name'=>'Ламинат',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>false,
     				'cancel_selection' =>true
     				),
     			'lak' => array(
     				'name'=>'Лак',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>false,
     				'cancel_selection' =>true
     				),
     			'date_print' => array(
     				'name'=>'Дата сдачи',
     				'note'=>'если необходима конкретная дата поставки',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'how_mach' => array(
     				'name'=>'Бюджет',
     				'note'=>'',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),
     			'dop_info' => array(
     				'name'=>'Пояснения',
     				'note'=>'укажите дополнительную информацию, если таковая имеется',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				),     			
     			'images' => array(
     				'name'=>'Путь',
     				'note'=>'если есть картинка или фото',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false,
     				'cancel_selection' =>false
     				)
    			)
     		);


		function __construct($get,$post,$session){
			$this->GET = $get;
			$this->POST = $post;
			$this->SESSION = $session;	

			$this->usser_id = $session['access']['user_id'];
		}

		// возвращает форму выбора заведения новой позиции в запрос
		// осущевствляется выбор типа товара
		# на вход подается номер запроса
		public function to_chose_the_type_product_form_Html(){
			# ОПИСАНИЕ ТИПОВ ТОВАРОВ
			//cat  - каталог
			//pol - полиграфия листовая
			//pol_many - полиграфия многолистовая
			// calendar - икалендарь
			//packing - упаковка картон
			//packing_other - упаковка другая
			//ext - сувениры под заказ
			//ext_cl - сувениры клиента
			$array_product_type = $this->arr_type_product;

			$html = '';

			$html .= '<form>';
			$i=0;
			foreach ($array_product_type as $key => $value) {
				if($value['access']){

					$readonly = ($value['readonly'])?'disabled':'';
					$readonly_style = ($value['readonly'])?'style="color:grey"':'';
					$html .= '<input type="radio" name="type_product" id="type_product_'.$i.'" value="'.$key.'" '.$readonly.'><label '.$readonly_style.' for="type_product_'.$i.'">'.$value['name'].'</label><br>';
					$i++;
				}				
			}
			$html .= '<input type="hidden" name="AJAX" value="get_form_Html">';
			$html .= '</form>';
			return $html;
		}


		// возвращает html формы для заведения запроса на расчёт в отделе снабжения
		public function get_product_form_Html($type_product){
			
			// если поля для запрошенного типа продукции описаны в классе
			if(isset($this->form_type[$type_product]) && count($this->form_type[$type_product])!=0){
				// получаем форму
				$this->type_product = $type_product;

				$form = self::get_form_Html($this->form_type[$this->type_product] , $type_product);
				return $form;
			}else{
				// впротивном случае выводи ошибку
				$error = "Такого типа продукции не предусмотрено. Обратитесь к администрации";
				return $error;
			}
				
		}

		// заносит новые варианты в базу, на вход принимает массив POST
		public function insert_new_options_in_the_Database(){
			// $id_i = (isset($this->POST['id']))?$this->POST['id']:(isset($this->GET['id'])?$this->GET['id']:0);
			$id_i = (isset($this->GET['id'])?$this->GET['id']:0);

			// $query_num_i = (isset($this->POST['query_num']))?$_POST['query_num']:(isset($_GET['query_num'])?$_GET['query_num']:0);
			$query_num_i =isset($this->GET['query_num'])?$this->GET['query_num']:0;

			//type_product
			$type_product = isset($this->POST['type_product'])?$this->POST['type_product']:0;

			// проверяем наличие вариантов, если все впорядке идём дальше

			if(!isset($this->POST['json_variants']) || count($this->POST['json_variants'])==0){return 'Не было создано ни одного варианта.';}
			

			// echo '<pre>';
			// print_r($this->POST['json_general']);
			// echo '</pre>';
			

			if($query_num_i!=0){
				// если нам известен $query_num, то работа ведётся из РТ
				
				#/ получаем наименование и доп название позиции из Json
				$arr = json_decode($this->POST['json_general'],true);

				
				#/ заводим новую строку позиции и получаем её id
				$new_position_id = $this->insert_new_main_row_Database($query_num_i,$arr,$type_product);
				
				#/ для каждой строки варианта заводим новую строку варианта с ценой равной нулю
				
				#/ Json
				foreach ($this->POST['json_variants'] as $key => $json_for_variant) {
					// $str = json_decode(,true);
					$this->insert_new_dop_data_row_Database($new_position_id,$json_for_variant);
				}

				// echo ;
				echo 'OK';

				return;

			}else if($id_i){

			// В ВЕРСИИ 1.0 ДЕИСТВИЯ С РЕДАКТИРОВАНИЕМ ВАРИАНТОВ ВНУТРИ ПОЗИЦИИ НЕ ПРЕДУСМОТРЕНЫ
			return;

				// если нам известен $id, то работа ведётся из позиции
				#/ 1 выбираем json позиции и считываем его в массив1
				#/ 2 считываем в массив2 новый json
				#/ 3 свиреряем
				#/ ? для каждой строки варианта заводим новую строку варианта с ценой равной нулю
			}			
			return 'неожиданный конец программы #0001';			
		}

		private function insert_new_dop_data_row_Database($new_position_id,$json_for_variant){
			global $mysqli;	
			$arr = json_decode($json_for_variant,true);
			$quantity = $arr['quantity'];
			unset($arr['quantity']);$json_for_variant = json_encode($arr);


			// status_snab - присваиватся(по умолчанию) первый статус - on_calculation (на расчёт)
			$query ="INSERT INTO `".RT_DOP_DATA."` SET
				`row_id` = '".$new_position_id."',
				`quantity` = '".$quantity."',
				`price_in` = '0',
				`price_out` = '0',
				`create_date` = CURRENT_DATE(),
				no_cat_json = '".$json_for_variant."'";		 
		    
		    $result = $mysqli->query($query) or die($mysqli->error);
			
			return $mysqli->insert_id;
		}

		private function insert_new_main_row_Database($query_num_i, $arr, $type_product){	
			
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			global $mysqli;	

			$query ="INSERT INTO `".RT_MAIN_ROWS."` SET
				`query_num` = '".$query_num_i."',
				`name` = '".$arr['name_product'][0]." ".$arr['product_dop_text'][0]."',
				`date_create` = CURRENT_DATE(),
				`type` = '".$type_product."',
			    `dop_info_no_cat` = '".addslashes($this->POST['json_general'])."'";				 
		    
		    $result = $mysqli->query($query) or die($mysqli->error);
			
			return $mysqli->insert_id;	
		}

		// обработка данных из формы
		public function restructuring_of_the_entry_form($array_in,$type_product,$child = 0){
			$html = '';
			
			// получаем массив описаний
			$product_options = $this->form_type[$type_product];

			//массив второстепенных описаний
			$arr = $this->get_cirilic_names_keys_Database();
			foreach ($arr as $key => $value) {
				$all_name[$value['parent_name']] = array('name'=>$value['name_cirilic']); 
			}		
			// сливаем массив описаний из базы с основным массивом 
			$product_options = array_merge($product_options,$all_name);
			
			// считаем количество возможных вариаций вариантов расчёта
			
			// объявляем массив
			$array_for_table = array();
			// перебираем входящие данные и пишем в массив

			foreach ($array_in as $key => $value) {// перебор по полям
				
				// $value - всегда массивы, в противном случае это будет сервисная информация
				if(!is_array($value)){continue;}

				// собираем данные	
				// название поля в кириллице
				
				foreach ($value as $k => $v) {// перебор по вариантам

					$array_for_table[$key][]= implode('; ',$this->gg_Array($v,1,$product_options));
					
				}
			}



			$return = $this->greate_table_variants_Html($array_for_table,$product_options,$type_product);
			

			return $return;

		}

		// выдаёт форму по типу продукции
		public function get_form_Html($arr,$type_product){
			global $mysqli;
			$html = '';
			$html .= '<div id="general_form_for_create_product"><form>';
			$html .= '<input type="hidden" name="AJAX" value="general_form_for_create_product">';
			$html .= '<input type="hidden" name="type_product" value="'.$type_product.'">';
			// перебираем массив разрешенных для данного типа товара полей
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			foreach ($arr as $key => $value) {
				$html .= '<div class="one_row_for_this_type '.$key.'" data-type="'.$key.'" data-moderate="'.$value['moderate'].'">';
				
				$moderate = ($value['moderate'])?'<span style="color:red; font-size:14px">*</span>':'';
				// определяем имя поля
				$html .= '<strong>'.$value['name'].' '.$moderate.'</strong><br>';

				// доп описание по полю
				$html .= ($value['note']!='')?'<div style="font-size:10px">'.$value['note'].'</div>':'';
				
				//для каждого поля запрашиваем форму
				$html .= $this->generate_form_Html($this->get_form_Html_listing_Database_Array($type_product,$key),'',$type_product);
								
				// добавляем кнопки				
				$html .= '</div>';	
				$html .= '<div class="buttons_form">';
				$html .= ($value['btn_add_var'])?'<span class="btn_add_var">+ вариант</span>':'';
				$html .= ($value['btn_add_val'])?'<span class="btn_add_val">+ значение</span>':'';
				$html .= ($value['cancel_selection'])?'<span class="cancel_selection">отменить</span>':'';
				$html .= '</div>';
			}	

			$html .= '</form></div>';
			echo $html;
		}

		// возвращает таблицу всех возможных вариантов из множества, которое натыкал юзер
		private function greate_table_variants_Html($arr,$product_options,$type_product){
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			// return 1;

			// поучаем массив вариантов
			$array = $this->greate_array_variants_Array($arr);

			
			// массив для сохранения предыдущего варианта при выводе строк вариантов
			// нужен для выделения различий между каждым следующим вариантом
			$prev_variant = array();

			// перерабатываем его в таблицу
			$html = '';
			$html .= '<form>';
			$html .= '<input type="hidden" name="AJAX" value="save_no_cat_variant">';
			$html .= "<input type='hidden' name='json_general' value='".json_encode($arr)."'>";
			$html .= "<input type='hidden' name='type_product' value='".$type_product."'>";
			// $html .= '<div id="json_general" style="display:none">'.json_encode($arr).'</div>';
			$html .= '<table class="answer_table">';
			$html .= '<tr>';
			$html .= '<th>№ варианта</th>';
			$html .= '<th>Описание</th>';
			$html .= '<th>удалить</th>';
			$html .= '</tr>';

			foreach ($array as $key => $variant) {

				$html .= "<tr>";
				$html .= '<td>'.($key+1);
				// $html .= '<div class="json_hidden" style="display:none">'.json_encode($variant).'</div>';
				$html .= "<input type='hidden' name='json_variants[]' value='".json_encode($variant)."'>";
				$html .= '</td>';
				$html .= '<td>';
				foreach ($variant as $key1 => $value1) {
					$bold = (isset($prev_variant[$key1]) && $prev_variant[$key1]!=$value1)?'bold':'normaol';
					$html .= '<span style="font-weight:'.$bold.'">'.$product_options[$key1]['name'].'</span>: '.$value1.'<br>';
				}
				$html .= '</td>';
				$html .= '<td><span class="delete_user_val">X</span></td>';
						
				$html .= '</tr>';

				$prev_variant = $variant;
			}
			
			$html .= '</table>';
			$html .= '</form>';
			return $html;

		}

		// возвращает переработанный массив вариантов
		private function greate_array_variants_Array($arr){
			// подсчёт количества вариаций 
			$count = 1;
			foreach ($arr as $key => $value) {
				$count = $count*count($value);
			}		

			// создаем массив вариантов 
			$n = 0;
			foreach ($arr as $key2 => $value2) {
				
				if ($n==0) {
					$f=0;
					foreach ($value2 as $key3 => $value3) {
						for ($k=0; $k < $count/count($value2); $k++) { 
							$variants[$f][$key2] = $value3;
						$f++;		
						}	
					}
					$n++;	
				}else{
					$f=0;
					for ($k=0; $k < $count/count($value2); $k++) { 
						foreach ($value2 as $key3 => $value3) {						
							$variants[$f][$key2] = $value3;
						$f++;		
						}	//$f++;
					}
					$n++;
				}

				

			}
			

			return $variants;
		}

		// всомагательная функция обработки результатов выбора 
		private function gg_Array($arr,$n=0,$product_options){
			$html = array();
			$i=0;$k=0;
			foreach ($arr as $key1 => $val1) {// снимаем значения
				if(is_numeric($key1)){
					# если $key1 - число, то $val1 - то, что было выбрано или набрано
					$html1 = $val1;

					// прибавляем ключ
					$html[(++$i)] = ($html1!='')?$html1.' ':' ';

					$k=$i; //запоминаем ключ для сравнения
				}else{
					# если строка, то у предыдущего поля были дети и $val1 - массив
					# кирилическое название детей хрнаится в базе
					if(isset($product_options[$key1]['name']) && $product_options[$key1]['name']!=''){
						$html[$i] .= $product_options[$key1]['name'].': '.implode(', ',$this->gg_Array($val1,0,$product_options));
					}else{
						//определяем нужен ли тут знак припинания и какой
						$zn ='';
						if($k!=$i){ //  это значит, что родитель всё ещё предыдйщий и нам нужна запятая
							$zn = (($n>=0)?', ':'');
						}else{
							switch ($n) {// знаки присваивания для разных уровней вложенности
								case 1: // уровень первый
									$zn = ': ';
									break;

								case 0: // уровень второй
									$zn = '-> ';
									break;
								
								default: // третий и выше
									$zn = '-> ';
									break;
							}
							// $zn .= ' --$n='.$n.'--';
							//$zn = (($n>0)?': ':'');
						}
						
						$html[$i] .= $zn.implode(', ',$this->gg_Array($val1,(($n>0)?0:(-1)),$product_options));	
						//$html[$i] .= $zn.implode(', ',$this->gg_Array($val1,0,$product_options));	
						
						$k++;
						
					}					
				}
				
			}
			// сначала метод работал с Html, потом стал работать с Array, название переменной осталось
			return $html;
		}
		
		// получает массив описаний всех полей (кроме списков)
		private function get_cirilic_names_keys_Database(){
			$query = "SELECT `parent_name`,`name_cirilic` FROM `form_rows_for_lists` WHERE type NOT LIKE('select') AND type NOT LIKE('checkbox');";
			global $mysqli;			
			$arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}
		

		// генератор id
		private function generate_id_Strintg($name){
			//$id = $val['parent_name'].'_'.($id_i++);
			$this->id_closed[$name][] = true;

			$id = $name.'_'.count($this->id_closed[$name]);
			return $id;
		}

		// генерит html
		private function generate_form_Html($arr,$parent='',$type_product){	
			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';
			$html = '';
			$select = 0;

			foreach ($arr as $k => $val){
				// $p_name = '';
				if($parent==''){
					// если это группа checkbox, то 
					// echo $this->form_type[$type_product][$val['parent_name']]['btn_add_var'];
					// if($val['type']=='checkbox' && isset($this->form_type[$type_product][$val['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$val['parent_name']]['btn_add_var']){
					if($val['type']=='checkbox' && isset($this->form_type[$type_product][$val['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$val['parent_name']]['btn_add_var']){
						$p_name = $val['parent_name'].'[][]';
					}else{
						$p_name = $val['parent_name'].'[0][]';
					}
				}else{
					$parent = (substr($parent, -2, 2)=='[]')?substr($parent,0,strlen($parent)-2):$parent;
					
					 if(!strstr($parent, "[0]")){
					 	$parent = $parent.'[0]';
					 }
					$p_name = $parent.'['.$val['parent_name'].']'.'[]';
				}
				
				$id = $this->generate_id_Strintg($val['parent_name']);


				$html .= ($val['note']!='')?'<span style="font-size:10px">'.$val['note'].'</span><br>':'';
				// $html .= $val['type'];
				switch ($val['type']) {
					case 'textarea':// если тип поля textarea
						if($select > 0){$html .= '</select><br>';$select =0;}
						switch ($val['manager_id']) {
							case '0': // если запись соответствует 0, т.е. обязательна для вывода
								// выводим как есть
								$html .= '<textarea data-id="'.$val['id'].'" id="'.$id.'" name="'.$p_name.'">'.$val['val'].'</textarea><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<textarea data-id="'.$val['id'].'" id="'.$id.'" name="'.$p_name.'">'.$val['val'].'</textarea>'.$this->span_del.'<br>';
								break;
							
							default:
								# code...
								break;
						}	
						break;
					case 'text':// если тип поля text
						if($select > 0){$html .= '</select><br>';$select =0;}
						switch ($val['manager_id']) {
							case '0': // если запись соответствует 0, т.е. обязательна для вывода
								// выводим как есть
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'"><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'">'.$this->span_del.'<br>';
								break;
							
							default:
								# code...
								break;
						}	
						break;
					case 'select':// если тип поля select
						if($select == 0){$html .= '<select name="'.$p_name.'">';$select =1;}
						switch ($val['manager_id']) {
							case '0': // если запись соответствует 0, т.е. обязательна для вывода
								// выводим как есть
								$html .= '<option data-id="'.$val['id'].'" id="'.$id.'" value="'.$val['val'].'">'.$val['val'].'</option><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<option data-id="'.$val['id'].'" id="'.$id.'" value="'.$val['val'].'">'.$val['val'].' '.$this->span_del.'</option><br>';
								break;
							
							default:
								# code...
								break;
						}	
						break;
					
					default:
						if($select > 0){$html .= '</select><br>';$select =0;}
						switch ($val['manager_id']) {
							case '0': // если запись соответствует 0, т.е. обязательна для вывода
								// выводим как есть
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].'</label><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].' '.$this->span_del.'</label><br>';
								break;
							
							default:
								# code...
								break;
						}	
						break;
				}
					
				if($val['child']!=''){
					$arr_child = $this->get_child_listing_Database_Array($val['child']);
					$html .= '<div class="pad">'.$this->generate_form_Html($arr_child,$p_name,$type_product).'</div>';
				}

									
			}
			if($select > 0){$html .= '</select><br>';$select =0;}
			return $html;
		}


		// запрашивает из базы список вариантов для полей формы по отдельности
		private function get_form_Html_listing_Database_Array($type_product,$input_name){
			global $mysqli;			
			$query = "SELECT * FROM `form_rows_for_lists` WHERE `type_product` = '".$type_product."' AND `parent_name` = '".$input_name."'";
			$arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		// запрашивает из базы список CHILD для полей формы
		private function get_child_listing_Database_Array($child){
			global $mysqli;			
			$query = "SELECT * FROM `form_rows_for_lists` WHERE `id` IN (".$child.")";
			$arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

	}


?>