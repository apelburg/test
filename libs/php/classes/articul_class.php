<?php
class Articul{

	public function __construct($art_id){
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
			$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$draft.' '.$checked.'">Вариант '.($i+1).'</li>';
		}
		return $html;
	}
	public function generate_variants_menu($variants,$draft_enable){		
		$html = ''; // контент функции
		$checked = 'checked'; // имя класса для выбранного элемента
		$ch = 0; // счетчик количества выбранных элементов, может не больше одного
		
		for ($i=0; $i < count($variants); $i++) { 
			// если есть $draft_enable=1, т.е. мы знаем, что в списке есть основной
			// вариант, то грузим только его, остальное является историей
			$var = $variants[$i]['draft'].$variants[$i]['archiv'];
			switch ($var) {
				case '00':// не история - главный вариант расчёта
					// в случае существования такогого он имеет первое приимущество на
					// то, чтобы быть выделенным, т.к. с ним вероятнее всего будет производиться
					// основная работа
					if($ch>0){$checked='';}else{$checked = 'checked';$ch++;}
					$html .='<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'</li>';
				break;
				
				case '10':// не история - обычный вариант расчёта
					// тут у нас обычный вариант расчета, ни чем не примечательный
					
					// если $draft_enableпередан, значит обычный вариант расчёта не сможет 
					// быть выделен при старте, эту роль на себя возьмёт заранее известный 
					// главный вариант - обнуляем имя класса, чтобы оно не попало в вывод 
					// если если класс был использован ранее, тоже обнуляем имя класса
					if($draft_enable || $ch>0){$checked='';}else{$ch++;}
					$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name '.$checked.'">Вариант '.($i+1).'</li>';
				break;
							
				default: // архив, остальное не важно
					// это архивные записи, видны только в случае существования $_GET['show_archive']
					// не могут быть выделенными по умолчанию, кроме этих вкладок всегда найдутся
					// или главный вариант или хотябы один обычный вариант расчёта (который пока один и является главным)
					if(isset($_GET['show_archive'])){
						$html .= '<li data-cont_id="variant_content_block_'.$i.'" data-id="'.$variants[$i]['id'].'" class="variant_name show_archive">Вариант '.($i+1).'</li>';
					}
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
					<tr>
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

}
