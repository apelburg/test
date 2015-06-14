<?php 
     class Forms{

     	private $user_id;
		
		// сюда будем сохранять id html элементов формы, чтобы иметь понятие какие id мы использовать уже не можем
     	// id в основной своей массе используются для label
     	private $id_closed =array(); 
     	
     	// html код отвечающий за удаление записи, 
     	// которую добаил менеджер для личного пользования
     	private $span_del = '<span class="delete_user_val">X</span>';

     	// перечисление разрешённых разделов полей 
     	// а так же некоторая необходимая для их обработки информация
     	// имя на кириллице
     	// доп описание по заполняемой форме
     	// наличие кнопки клонирования раздела формы
     	// наличие кнопки добавления своего варианта // копирует самый нижний imput
     	private $form_type = array(
     		'pol' => array( // поисание формы для полиграфической продукции
     			'quantity'=>array(
     				'name'=>'Тираж',
     				'moderate'=>true,
     				'note'=>'укажите тираж изделий',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true

     				),
     			'name_product'=>array(
     				'name'=>'Наименование',
     				'moderate'=>true,
     				'note'=>'укажите название изделия',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true
     				),
     			'format'=>array(
     				'name'=>'Формат',
     				'moderate'=>true,
     				'note'=>'укажите формат (мм)',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true
     				),
     			'material' =>array(
     				'name'=>'Материал',
     				'moderate'=>true,
     				'note'=>'укажите материал (картон не мелованный, дизайнерский, бумага мелованная и т.д.), название материала (Splendorgel-Сплендоргель)',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),
     			'plotnost' =>array(
     				'name'=>'Плотность материала',
     				'note'=>'плотность (130гр, 170гр,300гр и т.д.)',
     				'moderate'=>true,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),
     			'type_print' =>array(
     				'name'=>'Вид печати',
     				'moderate'=>true,
     				'note'=>'укажите вид печати и кол-во цветов (4+0 и т.д.) + "другое" , выбрать Pantone если есть дополнительная печать пятым цветом',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'change_list' => array(
     				'name'=>'Изменение листа',
     				'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'laminat' => array(
     				'name'=>'Ламинат',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'lak' => array(
     				'name'=>'Лак',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'date_print' => array(
     				'name'=>'Дата сдачи',
     				'note'=>'если необходима конкретная дата поставки',
     				'moderate'=>false,
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'how_mach' => array(
     				'name'=>'Бюджет',
     				'note'=>'',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),
     			'dop_info' => array(
     				'name'=>'Пояснения',
     				'note'=>'укажите дополнительную информацию, если таковая имеется',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),     			
     			'images' => array(
     				'name'=>'Путь',
     				'note'=>'если есть картинка или фото',
     				'moderate'=>false,
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				)
    			)
     		);


		function __construct(){
		}

		// возвращает html формы для заведения запроса на расчёт в отделе снабжения
		public function get_product_form($type_product){
			global $_SESSION;
			$this->usser_id = $_SESSION['access']['user_id'];

			switch ($type_product) {
				case 'ext'://сувениры под заказ / не каталог
					return array('Формы для заведения данного товара не существует');
					break;
				case 'ext_cl'://материал(сувениры) заказчика
					return array('Формы для заведения данного товара не существует');
					break;
				case 'cat'://каталожная продукция / каталог
					return array('Формы для заведения данного товара не существует');
					break;
				case 'pol':// полиграфия листовая					
					// получим форму для полиграфии
					$form = self::get_form($this->form_type[$type_product] , $type_product);
					return $form;
					break;
				case 'pol_many':// полиграфия многолистовая
					return array('Формы для заведения данного товара не существует');
					break;
				case 'calendar':// календари
					return array('Формы для заведения данного товара не существует');
					break;
				
				default:
					# code...
					break;
			}			
		}


		// обработка данных из формы
		public function restructuring_of_the_entry_form($array_in,$type_product,$child = 0){
			$html = '';
			
			// получаем массив описаний
			$product_options = $this->form_type[$type_product];

			//массив второстепенных описаний
			$arr = $this->get_cirilic_names_keys();
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

					$array_for_table[$key][]= implode('; ',$this->gg($v,1,$product_options));
					
				}
			}


			$return = $this->greate_table_variants($array_for_table,$product_options);
			// $return .= $this->greate_array_variants($array_for_table);
			return $return;


			// echo '<pre>';
			// print_r($array_for_table);
			// echo '</pre>';

		}

		// возвращает переработанный массив вариантов
		private function greate_array_variants($arr){
			// подсчёт количества вариаций // можно удалить
			$count = 1;
			foreach ($arr as $key => $value) {
				$count = $count*count($value);
			}
			
			// если вариантов более одного
			// if($count>1){
				// создаем массив вариантов заполненный первыми вариантам
				
				foreach ($arr as $key2 => $value2) {
					$f=0;
					foreach ($value2 as $key3 => $value3) {
						for ($k=0; $k < $count/count($value2); $k++) { 
							$variants[$f][$key2] = $value3;
						$f++;		
						}	
					}
				}
			// }		
			// echo '<pre>';
			// print_r($variants);
			// echo '</pre>';

			// return $count;

			return $variants;
		}

		private function greate_table_variants($arr,$product_options){
			// поучаем массив вариантов
			$array = $this->greate_array_variants($arr);

			// перерабатываем его в таблицу
			$html = '';

			$html .= '<table class="answer_table">';
			$html .= '<tr>';
			$html .= '<th>№ варианта</th>';
			$html .= '<th>Описание</th>';
			$html .= '<th>удалить</th>';
			$html .= '</tr>';
			foreach ($array as $key => $variant) {
				$html .= "<tr>";
				$html .= '<td>'.($key+1).'<div class="json_hidden" style="display:none">'.json_encode($variant).'</div></td>';
				$html .= '<td>';
				foreach ($variant as $key1 => $value1) {
					$html .= ''.$product_options[$key1]['name'].': '.$value1.'<br>';
				}
				$html .= '</td>';
				$html .= '<td><span class="delete_user_val">X</span></td>';
						
				$html .= '</tr>';
			}
			
			$html .= '</table>';
			return $html;

		}

		private function greate_table_variants_OLD_DELETE($arr,$product_options){
			// $product_options содержит названия полей

			// начальный массив вариаций 
			$n_arr = array(0=>'');

			
			// заполним массив вариаций
			foreach ($arr as $key => $value) {
				// кириллическое название данных пришедших из формы
				$name = '<strong>'.$product_options[$key]['name'].': </strong>';
				
				// вспомагательные массивы для выдачи юзеру
				$new_arr1 = array();
				$n_arr2 = array();

				foreach ($n_arr as $k1 => $v1) {
					foreach ($value as $k => $v) {
						$new_arr1[$k] = $v1.' '.$name.' '.$v;
					}
					$n_arr2 = array_merge($n_arr2,$new_arr1);
				}
				// обновляем массив
				$n_arr = $n_arr2;
			}


			// подсчёт количества вариаций // можно удалить
			$count = 1;
			foreach ($arr as $key => $value) {
				$count = $count*count($value);
			}

			// echo '<pre>';
			// print_r($arr);
			// echo '</pre>';


			echo '<pre>';
			print_r($n_arr);
			echo '</pre>';
			return $count;
		}

		// всомагательная функция обработки результатов выбора 
		private function gg($arr,$n=0,$product_options){
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
						$html[$i] .= $product_options[$key1]['name'].': '.implode(', ',$this->gg($val1,0,$product_options));
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
						
						$html[$i] .= $zn.implode(', ',$this->gg($val1,(($n>0)?0:(-1)),$product_options));	
						//$html[$i] .= $zn.implode(', ',$this->gg($val1,0,$product_options));	
						
						$k++;
						
					}					
				}
				
			}
			return $html;
		}
		

		private function get_cirilic_names_keys(){
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
		



		// выдаёт форму по типу продукции
		public function get_form($arr,$type_product){
			global $mysqli;
			$html = '';
			$html .= '<div id="general_form_for_create_product"><form>';
			$html .= '<input type="hidden" name="AJAX" value="general_form_for_create_product">';
			$html .= '<input type="hidden" name="type_product" value="'.$type_product.'">';
			// перебираем массив разрешенных для данного типа товара полей
			foreach ($arr as $key => $value) {
				$html .= '<div class="one_row_for_this_type '.$key.'" data-type="'.$key.'" data-moderate="'.$value['moderate'].'">';
				
				$moderate = ($value['moderate'])?'<span style="color:red; font-size:14px">*</span>':'';
				// определяем имя поля
				$html .= '<strong>'.$value['name'].' '.$moderate.'</strong><br>';

				// доп описание по полю
				$html .= ($value['note']!='')?'<div style="font-size:10px">'.$value['note'].'</div>':'';
				
				//для каждого поля запрашиваем форму
				$html .= $this->generate_html_form($this->get_form_listing_arr($type_product,$key),'',$type_product);
								
				// добавляем кнопки				
				$html .= '</div>';	
				$html .= '<div class="buttons_form">';
				$html .= ($value['btn_add_var'])?'<span class="btn_add_var">+ вариант</span>':'';
				$html .= ($value['btn_add_val'])?'<span class="btn_add_val">+ значение</span>':'';
				$html .= '</div>';
			}	

			$html .= '</form></div>';
			echo $html;
		}

		// генератор id
		private function generate_id($name){
			//$id = $val['parent_name'].'_'.($id_i++);
			$this->id_closed[$name][] = true;

			$id = $name.'_'.count($this->id_closed[$name]);
			return $id;

		}

		// генерит html
		private function generate_html_form($arr,$parent='',$type_product){	
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
				
				$id = $this->generate_id($val['parent_name']);


				$html .= ($val['note']!='')?'<span style="font-size:10px">'.$val['note'].'</span><br>':'';
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
					$arr_child = $this->get_child_listing_arr($val['child']);
					$html .= '<div class="pad">'.$this->generate_html_form($arr_child,$p_name,$type_product).'</div>';
				}

									
			}
			if($select > 0){$html .= '</select><br>';$select =0;}
			return $html;
		}


		// запрашивает из базы список вариантов для полей формы
		private function get_form_listing_arr($type_product,$input_name){
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
		private function get_child_listing_arr($child){
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