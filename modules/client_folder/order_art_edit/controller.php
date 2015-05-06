<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['suppliers']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	$images_data = fetch_images_for_article2('32285','1');
	// echo $img_galery['main_img_src'];
	// echo $img_galery['previews_block'];


	$art = '375190.60';

	function fetch_images_for_article2($art,$id){
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
	function color_variants_to_html2($color_variants){
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
	function get_art_color_variants($art){
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


	// вычисление вариантов данного артикула с другими цветами
		if($color_variants = get_art_color_variants($art)) $color_variants_block = color_variants_to_html2($color_variants);
		else $color_variants_block = '';
?>