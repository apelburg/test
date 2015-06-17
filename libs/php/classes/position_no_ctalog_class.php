<?php
/*
Класс работы с некаталожной продукцией

в конце названий методов указан формат в котором выдаётся информация по окончании работы метода
Html, Array, String, Int
Либо:
Database - если метод предназначен только для работы с базой

PS было бы неплохо взять взять это за правило 



*/
class Position_no_catalog{

	function __construct(){
	}

	public function dop_info_no_cat_Html($arr,$FORM,$type_product){

		$html = '';

		// если у нас есть описание заявленного типа товара
		if(isset($FORM->form_type[$type_product])){
			$names = $FORM->form_type[$type_product]; // массив описания хранится в классе форм
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


}