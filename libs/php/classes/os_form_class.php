<?php 
     class Forms{
     	// переменные для работы
     	private $form_type = array(
     		'pol_list' => array(
     			'quantity' => array('tag'=>'input','type'=>'text'),
     			'name_product' => array('tag'=>'input','type'=>'text'),
     			 'format'=> array('tag'=>'input','type'=>'text'),
     			 'tipe_print'=> array('tag'=>'input','type'=>'text'),
     			 'material'=> array('tag'=>'input','type'=>'text'),
     			 'time_print'=> array('tag'=>'input','type'=>'text'),
     			 'lak'=> array('tag'=>'input','type'=>'text'),
     			 'laminat'=> array('tag'=>'input','type'=>'text')
     			)
     		); 

     	/*


    [5] => quantity
    [6] => zapas
    [18] => name_product
    [20] => format
    [21] => material
    [22] => tipe_print
    [23] => change_list
    [24] => lak
     	*/
		function __construct(){

		}
		// возвращает html формы для заведения запроса на расчёт в отделе снабжения
		public function get_prodect_form($type_product){
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
				case 'pol_list':// полиграфия листовая
					// получаем колонки таблицы варантов для заполнения через форму
					$arr = self::get_table_column('RT_VARIANTS_POL_LIST');

					// преобразуем массив колонок из таблицы в форму
					$form = self::get_form($arr);
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

		public function get_form($arr){
			// убиваем сервисные колонки, не входящие в форму
			echo '<pre>';
			print_r($arr);
			echo '</pre>';


		}


		public function get_table_column($table){// 
			global $mysqli;
			$query = "
			SELECT COLUMN_NAME
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = DATABASE()
			  AND TABLE_NAME = '".constant($table)."'
			ORDER BY ORDINAL_POSITION
			";

			$arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row['COLUMN_NAME'];
				}
			}


			


			return $arr;
		}



	}


?>