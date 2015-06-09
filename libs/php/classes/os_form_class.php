<?php 
     class Forms{
     	private $user_id;
     	
     	private $span_del = '<span class="delete_user_val">X</span>';

     	// перечисление разрешённых полей для редактрования в формах 
     	private $form_type = array(
     		'pol' => array( // поисание формы для полиграфической продукции
     			'quantity'=>array(
     				'type'=>'radio',
     				'name'=>'Тираж',
     				'note'=>'укажите тираж изделий',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'name_product'=>array(
     				'type'=>'radio',
     				'name'=>'Наименование',
     				'note'=>'укажите название изделия',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'format'=>array(
     				'type'=>'radio',
     				'name'=>'Формат',
     				'note'=>'укажите формат (мм)',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'material' =>array(
     				'type'=>'text',
     				'name'=>'Материал',
     				'note'=>'укажите материал (картон не мелованный, дизайнерский, бумага мелованная и т.д.), плотность (130гр, 170гр,300гр и т.д.), название материала (Splendorgel-Сплендоргель)',
     				'btn_add_var'=>true,
     				'btn_add_val'=>false
     				),
     			'type_print' =>array(
     				'type'=>'checkbox',
     				'name'=>'Вид печаити',
     				'note'=>'укажите вид печати и кол-во цветов (4+0 и т.д.) + "другое" , выбрать Pantone если есть дополнительная печать пятым цветом',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'change_list' => array(
     				'type'=>'checkbox',
     				'name'=>'Изменение листа',
     				'note'=>'укажите при необходимости дальнейшего изменения формы листа, при вырубке указать наличие штампа',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'laminat' => array(
     				'type'=>'radio',
     				'name'=>'Ламинат',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'lak' => array(
     				'type'=>'radio',
     				'name'=>'Лак',
     				'note'=>'укажите при необходимости вид обработки поверхности листа',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'date_print' => array(
     				'type'=>'radio',
     				'name'=>'Дата сдачи',
     				'note'=>'если необходима конкретная дата поставки',
     				'btn_add_var'=>true,
     				'btn_add_val'=>true
     				),
     			'how_mach' => array(
     				'type'=>'textarea',
     				'name'=>'Бюджет',
     				'note'=>'',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),
     			'dop_info' => array(
     				'type'=>'textarea',
     				'name'=>'Пояснения',
     				'note'=>'укажите дополнительную информацию, если таковая имеется',
     				'btn_add_var'=>false,
     				'btn_add_val'=>false
     				),     			
     			'images' => array(
     				'type'=>'textarea',
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
					$form = self::get_form($type_product);
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
		public function get_form($type_product){
			global $mysqli;
			$arr = array();
			$array = $arr = $this->form_type[$type_product];
			$html = '';
			// перебираем массив разрешенных для данного типа товара полей
			foreach ($array as $key => $value) {
				$html .= '<div class="one_row_for_this_type">';
				// определяем имя поля
				$html .= '<strong>'.$value['name'].'</strong><br>';


				// доп описание по поля
				$html .= ($value['note']!='')?'<div style="font-size:10px">'.$value['note'].'</div>':'';
				
				// если тип поля не textarea
				if($value['type'] !='textarea'){
					//для каждого поля запрашиваем форму
					$html .= $this->generate_html_form($type_product,$key,$value);	
				}else{
					$html .= '<textarea name="'.$type_product.'"></textarea>';
				}
				// добавляем кнопки				
				$html .= '</div>';	
				$html .= '<div class="buttons_form">';
				$html .= ($value['btn_add_var'])?'<span class="btn_add_var">+ вариант</span>':'';
				$html .= ($value['btn_add_val'])?'<span class="btn_add_val">+ значение</span>':'';
				$html .= '</div>';
			}	
			echo $html;
		}

		private function generate_html_form($type_product,$key,$options){
			// запоминаем тип поля //checkbox, select или radio 
			$type_input = $options['type'];
			$html = '';
			// получаем 
			$arr = $this->get_form_listing_arr($type_product,$key);
			//счетчик id
			$id_i=0;
			foreach ($arr as $k => $val){
				// создаём id для каждого поля формы, чтобы потом можно было обращаться к нему через label
				$id = $val['parent_name'].'_'.($id_i++);
				$html .= ($val['note']!='')?'<span style="font-size:10px">'.$val['note'].'</span><br>':'';
				switch ($val['manager_id']) {
					case '0': // если запись соответствует 0, т.е. обязательна для вывода
						// выводим как есть
						$html .= '<input type="'.$type_input.'" id="'.$id.'" name="'.$val['parent_name'].'" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].'</label><br>';
						break;
					case $this->usser_id: // если запись соответствует id менеджера
						// позволяем менеджеру удалить своё поле
						$html .= '<input type="'.$type_input.'" id="'.$id.'" name="'.$val['parent_name'].'" value="'.$val['val'].'"><label for="'.$id.'">'.$val['val'].' '.$this->span_del.'</label><br>';
						break;
					
					default:
						# code...
						break;
				}	

									
			}
			return $html;
		}


		// запрашивает из базы список вариантов для полей формы, работает ТОЛЬКО ДЛЯ СПИСКОВ
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




		public function get_table_column($table){// 
			global $mysqli;
			// $query = "
			// SELECT COLUMN_NAME
			// FROM information_schema.COLUMNS
			// WHERE TABLE_SCHEMA = DATABASE()
			//   AND TABLE_NAME = '".constant($table)."'
			// ORDER BY ORDINAL_POSITION
			// ";
			$query = "SELECT * FROM ";

			// $arr = array();
			// $result = $mysqli->query($query) or die($mysqli->error);
			// if($result->num_rows > 0){
			// 	while($row = $result->fetch_assoc()){
			// 		$arr[] = $row['COLUMN_NAME'];
			// 	}
			// }


			


			return $arr;
		}



	}


?>