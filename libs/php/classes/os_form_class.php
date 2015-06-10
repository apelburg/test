<?php 
     class Forms{

     	private $user_id;
		
		// сюда будем сохранять id html элементов формы, чтобы иметь понятие какие id мы использовать уже не можем
     	// id в основной своей массе используются для label
     	private $id_closed =array(); 
     	
     	// html код отвечающий за удаление записи, 
     	// которую добаил менеджер для личного пользования
     	private $span_del = '<span class="delete_user_val">X</span>';

     	// перечисление разрешённых полей для редактрования в формах 
     	private $form_type = array(
     		'pol' => array( // поисание формы для полиграфической продукции
     			'quantity'=>array(
     				'name'=>'Тираж',
     				'note'=>'укажите тираж изделий',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true
     				),
     			'name_product'=>array(
     				'name'=>'Наименование',
     				'note'=>'укажите название изделия',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true
     				),
     			'format'=>array(
     				'name'=>'Формат',
     				'note'=>'укажите формат (мм)',
     				'btn_add_var'=>false,
     				'btn_add_val'=>true
     				),
     			'material' =>array(
     				'name'=>'Материал',
     				'note'=>'укажите материал (картон не мелованный, дизайнерский, бумага мелованная и т.д.), плотность (130гр, 170гр,300гр и т.д.), название материала (Splendorgel-Сплендоргель)',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),
     			'type_print' =>array(
     				'name'=>'Вид печати',
     				'note'=>'укажите вид печати и кол-во цветов (4+0 и т.д.) + "другое" , выбрать Pantone если есть дополнительная печать пятым цветом',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'change_list' => array(
     				'name'=>'Изменение листа',
     				'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'laminat' => array(
     				'name'=>'Ламинат',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'lak' => array(
     				'name'=>'Лак',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'date_print' => array(
     				'name'=>'Дата сдачи',
     				'note'=>'если необходима конкретная дата поставки',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'how_mach' => array(
     				'name'=>'Бюджет',
     				'note'=>'',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),
     			'dop_info' => array(
     				'name'=>'Пояснения',
     				'note'=>'укажите дополнительную информацию, если таковая имеется',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),     			
     			'images' => array(
     				'name'=>'Путь',
     				'note'=>'если есть картинка или фото',
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

		// выдаёт форму по типу продукции
		public function get_form($arr,$type_product){
			global $mysqli;
			$html = '';
			$html .= '<div id="general_form_for_create_product"><form>';
			$html .= '<input type="hidden" name="AJAX" value="general_form_for_create_product">';
			// перебираем массив разрешенных для данного типа товара полей
			foreach ($arr as $key => $value) {
				$html .= '<div class="one_row_for_this_type '.$key.'" data-type="'.$key.'">';
				
				// определяем имя поля
				$html .= '<strong>'.$value['name'].'</strong><br>';

				// доп описание по полю
				$html .= ($value['note']!='')?'<div style="font-size:10px">'.$value['note'].'</div>':'';
				
				//для каждого поля запрашиваем форму
				$html .= $this->generate_html_form($this->get_form_listing_arr($type_product,$key));
								
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
		private function generate_html_form($arr){	
			$html = '';
			$select = 0;

			//счетчик id
			$id_i=0;
			foreach ($arr as $k => $val){
				
				
				$id = $this->generate_id($val['parent_name']);


				$html .= ($val['note']!='')?'<span style="font-size:10px">'.$val['note'].'</span><br>':'';
				switch ($val['type']) {
					case 'textarea':// если тип поля textarea
						if($select > 0){$html .= '</select><br>';$select =0;}
						switch ($val['manager_id']) {
							case '0': // если запись соответствует 0, т.е. обязательна для вывода
								// выводим как есть
								$html .= '<textarea data-id="'.$val['id'].'" id="'.$id.'" name="'.$val['parent_name'].'">'.$val['val'].'</textarea><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<textarea data-id="'.$val['id'].'" id="'.$id.'" name="'.$val['parent_name'].'">'.$val['val'].'</textarea>'.$this->span_del.'<br>';
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
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$val['parent_name'].'" value="'.$val['val'].'"><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$val['parent_name'].'" value="'.$val['val'].'">'.$this->span_del.'<br>';
								break;
							
							default:
								# code...
								break;
						}	
						break;
					case 'select':// если тип поля select
						if($select == 0){$html .= '<select name="'.$val['parent_name'].'[]">';$select =1;}
						switch ($val['manager_id']) {
							case '0': // если запись соответствует 0, т.е. обязательна для вывода
								// выводим как есть
								$html .= '<option data-id="'.$val['id'].'" id="'.$id.'" name="'.$val['parent_name'].'" value="'.$val['val'].'">'.$val['val'].'</option><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<option data-id="'.$val['id'].'" id="'.$id.'" name="'.$val['parent_name'].'" value="'.$val['val'].'">'.$val['val'].' '.$this->span_del.'</option><br>';
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
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$val['parent_name'].'[]" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].'</label><br>';
								break;
							case $this->usser_id: // если запись соответствует id менеджера
								// позволяем менеджеру удалить своё поле
								$html .= '<input data-id="'.$val['id'].'" type="'.$val['type'].'" id="'.$id.'" name="'.$val['parent_name'].'[]" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].' '.$this->span_del.'</label><br>';
								break;
							
							default:
								# code...
								break;
						}	
						break;
				}
					
				if($val['child']!=''){
					$arr_child = $this->get_child_listing_arr($val['child']);
					$html .= '<div class="pad">'.$this->generate_html_form($arr_child).'</div>';
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