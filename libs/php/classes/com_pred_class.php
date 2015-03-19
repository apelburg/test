<?php 

 class Com_pred{
        static function save_to_tbl($id_arr,$conrtol_num){
	       global $mysqli;
		   // !!! $conrtol_num
		   // выбираем из базы строки выбранные для создания КП
		   $query="SELECT*FROM `".CALCULATE_TBL."` WHERE id IN('".implode("','",$id_arr)."') ORDER BY id DESC";//echo $query;
		   
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		         // получаем необходимые данные для помещения в таблицу
		         $row=$result->fetch_assoc();
		         // записываем данные о КП в таблицу COM_PRED_LIST
                 $query2="INSERT INTO `".COM_PRED_LIST."` 
					              SET 
								  `client_id` = '".$row['client_id']."',
								  `manager_id` = '".$row['manager_id']."',
								  `order_num` = '".$row['order_num']."'
								  ";
				 $result2 = $mysqli->query($query2)or die($mysqli->error);
				 if(!$result2) return 2;
				 $kp_id = $mysqli->insert_id;
				 
				// Записываем содержимое КП в таблицу COM_PRED_ROWS
				$result->data_seek(0);
		        while($row=$result->fetch_assoc()){ //
				    //print_r($row).'<br>';
					// сохраняем выбранные строки в таблицу КП
				    $query3="INSERT INTO `".COM_PRED_ROWS."` 
					              SET 
								  `kp_id` = '".$kp_id."',
								  `type` = '".$row['type']."',
								  `article` = '".$row['article']."',
								  `name` = '".$row['name']."',
								  `quantity` = '".$row['quantity']."',
								  `coming_price` = '".$row['coming_price']."',
								  `price` = '".$row['price']."',
								  `discount` = '".$row['discount']."',
								  `percent` = '".$row['percent']."',
								  `hide_article_marker` = '".$row['hide_article_marker']."',
								  `supplier` = '".$row['supplier']."'
								  ";
				    $result3 = $mysqli->query($query3)or die($mysqli->error);
					if(!$result3) return 2;
				}/**/
				return 1;
		   }
		   else return '0: в таблице CALCULATE_TBL не найдены строки соответсвующие переданным id';

	   }
	   /*function get_last_kp_num(){
	       global $mysqli;
	       $query="SELECT kp_num FROM `".COM_PRED_LIST."` GROUP BY kp_num ORDER BY id DESC";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		       $kp_num = $result->fetch_assoc();
		   }
		   return (!empty($kp_num['kp_num']))? ++$kp_num['kp_num']: 100000;
	   }*/
	   function delete($kp_id){
	       global $mysqli;
		   $arr=array();
		   // !!! $conrtol_num
		   // выбираем из базы строки выбранные для создания КП
		   $query="DELETE FROM `".COM_PRED_ROWS."` WHERE kp_id = '".(int)$kp_id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
		   
		   $query="DELETE FROM `".COM_PRED_LIST."` WHERE id = '".(int)$kp_id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
	   }
	   function delete_old_version($file,$client_id,$id){
		
		  function delete_file_comment($file_name){
		      global $client_id;
		
		      $dir_name = 'data/com_offers/'.$client_id;
		      $file_path = $dir_name.'/comments.txt';
		      if(file_exists($file_path)){
			      $string_arr = file($file_path);

			      foreach($string_arr as $string){
			           $string = trim($string,"\r\n");
				       list($old_file_name,$old_comment)= explode(';',$string);
				       if($old_file_name != $file_name){
					        $file_content_arr[] = $old_file_name.';'.$old_comment;	
				       }
			      }
			      if(isset($file_content_arr)) $file_content = implode("\r\n",$file_content_arr);
			      else $file_content = '';
			 
			      $fd = fopen($file_path,'w');
	              $write_result = fwrite($fd,$file_content); //\r\n
	              fclose($fd);
		      }
		      else return;
	      }

	      $prefix = '../admin/order_manager/';
		  $file = urldecode($file);
	      unlink($prefix.'data/com_offers/'.trim($client_id).'/'.trim(iconv("UTF-8","windows-1251", $file)));
		  delete_file_comment($file);

	   }
	   function change_comment($id,$comment){
	       global $mysqli;
		   
		   $query="UPDATE `".COM_PRED_LIST."` SET comment ='".$comment."'  WHERE id = '".(int)$id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if(!$result) return;
	   }
	   function change_comment_old_version($file_name,$file_comment){
			global $client_id;
			
			//echo $file_name.$file_comment;
			$prefix = '../admin/order_manager/';
			
			
			
			$file_comment = strip_tags($file_comment,'<b><br><a>');
			$file_comment = trim($file_comment," ");
			$file_comment = trim($file_comment,"\r");
			$file_comment = trim($file_comment,"\n");
			$dir_name = $prefix.'data/com_offers/'.$client_id;
			$file_path = $dir_name.'/comments.txt';
			if(file_exists($file_path)){
			
				$string_arr = file($file_path);
				$flag = 0;
				foreach($string_arr as $string){
					 $string = trim($string,"\r\n");
					 list($old_file_name,$old_comment)= explode(';',$string);
					 if($old_file_name == $file_name){
						  $file_content_arr[] = $old_file_name.';'.$file_comment;
						  $flag = 1;
					 }
					 else  $file_content_arr[] = $old_file_name.';'.$old_comment;
				}
				if($flag == 0) $file_content_arr[] = $file_name.';'.$file_comment;
				$file_content = implode("\r\n",$file_content_arr);  
				
				
			}
			else $file_content = $file_name.';'.$file_comment;
			
			$fd = fopen($file_path,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
	   }
	   function fetch_kp_rows($kp_id){
	       global $mysqli;
		   $arr=array();
		   // !!! $conrtol_num
		   // выбираем из базы строки выбранные для создания КП
		   $query="SELECT*FROM `".COM_PRED_ROWS."` WHERE kp_id = '".(int)$kp_id."' ORDER BY id";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		        while($row=$result->fetch_assoc()) $arr[]=$row;
		   }
		   return $arr;
	   }
	   function open_old_kp($show_old_kp){
	        $prefix = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/';
            $file_name = $prefix.'data/com_offers/'.$show_old_kp;
			
			//echo $file_name;
			$fd = fopen($file_name,"rb");
			$fcontent = fread($fd,filesize($file_name));
			$fcontent = str_replace('src="../..','src="',$fcontent);
			
			return $fcontent;
	   }
	   function prepare_send_mail($kp_id,$client_id,$user_id){
	        // проверяем есть папка данного клента, если её нет то создаем её
	        $document_root = $_SERVER['DOCUMENT_ROOT'];
			$dirname = '/os/data/com_offers/'.strval(intval($_GET['client_id']));
			if(!file_exists($document_root.$dirname)){
				if(!mkdir($document_root.$dirname, 0777)){
					echo 'ошибка создания папки клиента (kp#1)'.$document_root.$dirname;
					exit;
				}
			}
			$filename = '/Пробный_ПДФ_в_кириллицe_'.$client_id.'_'.date('Y_i_s').'.pdf';
			//$filename = '/probe_file_in_latin_'.$client_id.'_'.date('Y_i_s').'.pdf';
			$filename_utf = iconv("UTF-8","windows-1251", $filename);
			$save_to = $document_root.$dirname.$filename_utf;
			
            Com_pred::save_in_pdf_on_server($kp_id,$client_id,$user_id,$save_to);
			return $dirname.$filename;
            exit;
	   }
	   function save_in_pdf_on_server($kp_id,$client_id,$user_id,$filename){
	   
            $html = Com_pred::open_in_blank($kp_id,$client_id,$user_id);
			
			include($_SERVER['DOCUMENT_ROOT']."/os/libs/php/mpdf60/mpdf.php");
			$mpdf=new mPDF();
			$mpdf->WriteHTML($html,2);
			$mpdf->Output($filename,'F');
	   }
	   function save_in_pdf($kp_id,$client_id,$user_id,$filename = '1.pdf'){
	   
            $html = Com_pred::open_in_blank($kp_id,$client_id,$user_id);
		
			include($_SERVER['DOCUMENT_ROOT']."/os/libs/php/mpdf60/mpdf.php");
            //$stylesheet = file_get_contents('style.css');
				
			$mpdf=new mPDF();
			//$mpdf->WriteHTML($stylesheet,1);
			$mpdf->WriteHTML($html,2);
			$mpdf->Output($filename,'D');
			//$mpdf->Output();
            exit;
	   }
	   function open_in_tbl($kp_id){
	       $arr=Com_pred::fetch_kp_rows($kp_id);

		   //!!! разобраться с отображением marker_summ_print hide_article_marker
		   
		   // print_r($arr); echo '<br>';
		   // шаблон ряда таблицы списка КП
		   $tpl_name = 'skins/tpl/clients/client_folder/business_offers/kp_table_rows.tpl';
		   $fd = fopen($tpl_name,'r');
		   $rows_template = fread($fd,filesize($tpl_name));
		   fclose($fd); 
			
		   ob_start();	   
			   foreach($arr as $row){
				  eval(' ?>'.$rows_template.'<?php ');
			   }
		   $rows .= ob_get_contents();
		   ob_get_clean();	
		   ob_start();	   
		      include ('skins/tpl/clients/client_folder/business_offers/kp_table.tpl');
		   $output .= ob_get_contents();
		   ob_get_clean();	

		   return $output;			
	   }
	   function open_in_blank($kp_id,$client_id,$user_id,$save_on_disk = false){
	        global $mysqli;
			$stock = false;
			$com_offer_descriptions = array();
		    $com_offer_description_length = 80;
			$string = $article_string = $ordinary_string = $print_string = $itog_string = $previos_marker_summ_print = '';
		    // Здесь делаем то что в старой версии делали при сохранении КП в файл
		   
		    $cont_face_data_arr = get_client_cont_face_by_id($client_id,$user_id,true);
		    $client_data_arr = select_all_client_data($client_id);
			
			//print_r($cont_face_data_arr);
			//exit;
			$rows_data=Com_pred::fetch_kp_rows($kp_id);
			$rows_data = array_reverse($rows_data);
			
			// собираем контент коммерческого предложения
			//if($save_on_disk)//?'.$_SERVER['QUERY_STRING'].'&show_kp_in_blank='.$kp_id.'
			$kp_content = '<div style="width:625px;background-color:#FFFFFF;"><div style="text-align:right;font-family:verdana;font-size:12px;font-weight:bold;line-height:16px;"><br>В компанию: '.$client_data_arr['comp_full_name'].'<br>Кому: '.$cont_face_data_arr['name'].'<br>Контакты: '.$cont_face_data_arr['phone'].'<br>'.$cont_face_data_arr['email'].'<br><br></div>
			<div style="font-family:verdana;font-size:18px;padding:10px;color:#10B050;text-align:center">Коммерческое предложение</div>';
			$kp_content .=  '<table width="625"  style="border:#CCCCCC solid 1px; border-collapse:collapse;background-color:#FFFFFF;font-family:Verdana, Arial, Helvetica, sans-serif; font-size:12px;" valign="top">';
			$tr_td = '<tr><td style="border:#CCCCCC solid 1px;" width="300" valign="middle" align="center">';
			$td_tr = '</td></tr>';
			$td_td = '</td><td style="border:#CCCCCC solid 1px;padding:6px;" width="325" valign="top">';
			
			
			
			// этап создания контента меню
			// принцип следующий сортируем id в порядке возрастания, и считываем ряды из таблицы в порядке возрастания, при считывании
			// рядов первоначально считывается персонализация относящаяся к артикулу, записываем её данные в массив, когда доходим до            // ряда "article" или ряда "ordinary" проверям были ли созданна переменная содержащая информацию о нанесении если да
			// записываем данные по нанесению которые были считанны до этого в общую строку, предварительно развернув массив
			// с этими данными, затем переменную с данными о нанесении удаляем, если нет добавляем пустую запись
			// собранную сроку записываем в итоговый массив который перед записью в файл разворачиваем
			// если в конце всей обработки были считанны данные по нанесению но строка артикул в итоге не последовала
			// тогда эти данные добавляются в итоговый массив без данных об артикуле, на последнем шаге
			// перед разворотом массива и записью данных в файл
			foreach($rows_data as $item){
			  
		    
		     if($item['type'] == 'article'){
	
	            $query="SELECT*FROM `".BASE_TBL."` WHERE art = '".$item['article']."'";
			    $result = $mysqli->query($query)or die($mysqli->error);
				$row=$result->fetch_assoc();
			    $id = $row['id'];
	
	
				$article_string = $tr_td;
				$art_img = new  Art_Img($item['article']);
				// проверяем наличие изображения
	    	    //$img_path = '../../img/'.$art_img->big;
				$img_path = 'http://www.apelburg.ru/img/'.$art_img->big;
		        $img_src = checkImgExists($img_path);
				//$img_path = '';
				//$img_src = '../../skins/images/img_design/icon_index_2.jpg';
			                        
		        // меняем размер изображения
			     $size_arr = transform_img_size($img_src,230,300);
				//$size_arr = array(230,300);
				//$size_arr = array(100,100);
			
				
				// вставляем изображение
				$article_string .= '<img src="'.$img_src.'" height="'.$size_arr[0].'" width="'.$img_src[1].'">'.$td_td;
				
				// количество
				$quantity = $item['quantity'];
				// стоимость
				$price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$summ = $quantity*$price;
				
				
				$article = ($item['hide_article_marker'] == 'on')? '' :'арт.: <a href="/index.php?page=description&id='.$id.'" target="_blank">'.$item['article'].'</a>';
				
				// наименование сувенира
				$str_len = 40;
				$article_name = $item['name'];
				$article_name = nl2br($article_name);
				$article_name = iconv("UTF-8","windows-1251//TRANSLIT", $article_name);
				
				if(strpos($article_name,'<br>') == true) $article_name = str_replace('<br>','<br />',$article_name);
				$article_name_arr = explode('<br />',$article_name);
				$new_line = '<br />&nbsp;&nbsp;&nbsp;';
				foreach($article_name_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $article_name_arr[$key] = $piece;
					}
					else $article_name_arr[$key] = trim($piece);
				}
				
				$article_name = implode($new_line,$article_name_arr);
				$article_name = iconv("windows-1251","UTF-8//TRANSLIT", $article_name);
				//iconv_strlen($article_name,'UTF-8')
	
				
			    $article_string .= '<b>Сувенир:</b><br />
				&nbsp;&nbsp;&nbsp;'.$article_name.'<br />
				&nbsp;&nbsp;&nbsp;'.$article.'<br />
				&nbsp;&nbsp;&nbsp;Тираж: '.$item['quantity'].' шт.<br />
				&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($summ,2,'.',' ').'руб.</nobr><br />';
				$description_str = strip_tags($article_name);
				
				$description_str = str_replace('<br>',' ',$description_str);
				$description_str = str_replace('<br/>',' ',$description_str);
				$description_str = str_replace('<br />',' ',$description_str);
				$description_str = str_replace('&nbsp;',' ',$description_str);
				$description_str = preg_replace('|[\s]+|s',' ',$description_str);
				$description_str = trim($description_str,' ');
				$description_str = str_replace(' ',',',$description_str);
				
				$com_offer_description = substr($description_str,0,strpos($description_str,','));
				$com_offer_description = (strlen($com_offer_description) > $com_offer_description_length)? substr($com_offer_description,0,$com_offer_description_length):$com_offer_description;
				if(trim($com_offer_description) != '') $com_offer_descriptions[] = $com_offer_description;
				

				if($stock){
				$ostatok_update_time = substr($item_dop['ostatok_update_time'],11,5);
				$ostatok_update_date = substr($item_dop['ostatok_update_time'],8,2).substr($item_dop['ostatok_update_time'],4,4).substr($item_dop['ostatok_update_time'],0,4);
				$ostatok = $item_dop['ostatok'];
				$ostatok_block = 
				  '<div style="font-size:10px;color:#669900;">
					 &nbsp;&nbsp;&nbsp;<span style="font-size:13px;font-family:Arial;">остаток - '.$ostatok.'</span> шт. на&nbsp; 
					 <span style="font-size:11px;font-family:Arial;">'.$ostatok_update_time.' &nbsp;'.$ostatok_update_date.'</span>
				  </div><br />';
			    }
			    else $ostatok_block ='<br />';
				
				
				$short_space_str = '&nbsp;&nbsp;&nbsp;';
				$long_space_str = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$quantity_for_division = ($quantity == 0)? 1: $quantity;
				
				if(isset($print_rows)){
				    $print_rows = array_reverse($print_rows);
				    $print_string = '<b>Лого:</b><br />';
					$itog_string = '<br /><b>Стоимость сувенира + лого:</b><br />';
					
				    for( $i = 0 ; $i < count($print_rows); $i++ ){
						if(count($print_rows)==1){
						    $print_string .= $short_space_str.str_replace('{##}',$short_space_str,$print_rows[$i][1]);
							$itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
						else {
						    $print_string .= '<br />'.$short_space_str.($i + 1).'. '.str_replace('{##}',$long_space_str,$print_rows[$i][1]);
						    $itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">'.($i + 1).'. 1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
				    }
				} 
				$tbl_rows[] = $article_string.$ostatok_block.$print_string.$itog_string.$td_tr;
				
				unset($print_rows);
				$article_string = $print_string = $itog_string = '';
		    }
			elseif($item['type'] == 'ordinary'){
	            // пустая ячейка
				$ordinary_string = $tr_td.'&nbsp;'.$td_td;				
				
				// количество
				$quantity = $item['quantity'];
				// стоимость
				$price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$summ = $quantity*$price;
				
				 // наименование сувенира
				$str_len = 40;
				$article_name = $item['name'];
				$article_name = nl2br($article_name);
				$article_name = iconv("UTF-8","windows-1251//TRANSLIT", $article_name);
				
				if(strpos($article_name,'<br>') == true) $article_name = str_replace('<br>','<br />',$article_name);
				$article_name_arr = explode('<br />',$article_name);
				$new_line = '<br />&nbsp;&nbsp;&nbsp;';
				foreach($article_name_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $article_name_arr[$key] = $piece;
					}
					else $article_name_arr[$key] = trim($piece);
				}
				
				$article_name = implode($new_line,$article_name_arr);
				$article_name = iconv("windows-1251","UTF-8//TRANSLIT", $article_name);
				//iconv_strlen($article_name,'UTF-8')
	
				
			    $ordinary_string .= '
				&nbsp;&nbsp;&nbsp;'.$article_name.'<br />
				&nbsp;&nbsp;&nbsp;Тираж: '.$item['quantity'].' шт.<br />
				&nbsp;&nbsp;&nbsp;1шт.: '.number_format($price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($summ,2,'.',' ').'руб.</nobr><br /><br />';
				
				
				
				
				$short_space_str = '&nbsp;&nbsp;&nbsp;';
				$long_space_str = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				$quantity_for_division = ($quantity == 0)? 1: $quantity;
				
				if(isset($print_rows)){
				    $print_rows = array_reverse($print_rows);
				    $print_string = '<b>Лого:</b><br />';
					$itog_string = '<br /><b>Стоимость сувенира + лого:</b><br />';
					
				    for( $i = 0 ; $i < count($print_rows); $i++ ){
						if(count($print_rows)==1){
						    $print_string .= $short_space_str.str_replace('{##}',$short_space_str,$print_rows[$i][1]);
							$itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
						else {
						    $print_string .= '<br />'.$short_space_str.($i + 1).'. '.str_replace('{##}',$long_space_str,$print_rows[$i][1]);
						    $itog_string .= $short_space_str.'<span style="color:#00B050;font-weight:bold;">'.($i + 1).'. 1шт. : '.number_format(($summ+$print_rows[$i][0])/$quantity_for_division,2,'.',' ').' руб. / тираж: <nobr>'.number_format(($summ+$print_rows[$i][0]),2,'.',' ').'руб.</nobr></span><br />';
						}
				    }
				} 
				
				$tbl_rows[] = $ordinary_string.$print_string.$itog_string.$td_tr;
				
				unset($print_rows);
				$ordinary_string = $print_string = $itog_string = '';
		    }
			elseif($item['type'] == 'print'){
				// количество
				$print_quantity = $item['quantity'];
				// стоимость
				$print_price = ($item['discount'] == 0)? $item['price'] : $item['price'] + $item['price']/100*$item['discount'];
				$print_summ = $item['quantity']*$print_price;
				
				// наименование нанесения
				$str_len = 38;
				$print_description = $item['name'];
				$print_description = nl2br($print_description);
				$print_description = iconv("UTF-8","windows-1251//TRANSLIT", $print_description);
				
				if(strpos($print_description,'<br>') == true) $print_description = str_replace('<br>','<br />',$print_description);
				$print_description_arr = explode('<br />',$print_description);
				$new_line = '<br />{##}';
				foreach($print_description_arr as $key => $piece){
				    if(strlen($piece) > $str_len){  
					    $piece = wordwrap($piece,$str_len,$new_line);
				        $print_description_arr[$key] = $piece;
					}
					else $print_description_arr[$key] = trim($piece);
				}
				
				$print_description = implode($new_line,$print_description_arr);
				$print_description = iconv("windows-1251","UTF-8//TRANSLIT", $print_description);
				//iconv_strlen($print_description,'UTF-8')
				
                $string .= $print_description.'<br />
				{##}Тираж: '.$item['quantity'].' шт.<br />
				{##}1шт.: '.number_format($print_price,2,'.',' ').'руб. / тираж: <nobr>'.number_format($print_summ,2,'.',' ').'руб.</nobr><br />';
				
                // если предыдущий ряд был отмечен маркером marker_summ_print (объединить расчет нанесения) то объеденяем данные 
				// внося их в созданный ранее эелемент массива, если нет добавляем новый элемент.
				if($previos_marker_summ_print == 'on') $print_rows[(count($print_rows) - 1)] = array($print_rows[(count($print_rows) - 1)][0] + $print_summ, $string.'<br />{##}'.$print_rows[(count($print_rows) - 1)][1]);  
				else $print_rows[] = array($print_summ,$string);  
               
				$string = '';
				$previos_marker_summ_print = $item['marker_summ_print'];
			}
			/**/
			
			
			}
			
			//$tbl_rows[] = $tr_td.'proba'.$td_tr;
			
			// записываем все данные в строку предварительно развернув массив
		   $kp_content .= implode('',array_reverse($tbl_rows)).'</td></tr></table>
		   <div style="text-align:right;font-family:verdana;font-size:12px;line-height:20px;"><br>'.convert_bb_tags(mysql_result(select_manager_data($user_id),0,'mail_signature')).'<br><br><br></div></div>';
		   
		   return $kp_content;
	   }
	   function create_list($client_id,$certain_kp = FALSE){
	       
		    $rows = '';
			if(!$certain_kp){// если не указан конкретный КП создаем полный список
				$rows .= Com_pred::create_list_new_version($client_id);
				$rows .= Com_pred::create_list_old_version($client_id);
            }
			else{
			    if($certain_kp['type'] == 'new') $rows .= Com_pred::create_list_new_version($client_id,$certain_kp['kp']);
				if($certain_kp['type'] == 'old')  $rows .= Com_pred::create_list_old_version($client_id,substr($certain_kp['kp'],strpos($certain_kp['kp'],"/")+1));
			}
			return (!empty($rows))?$rows:"<tr><td colspan='4'>для данного клиента пока небыло создано коммерческих предложений</td></tr>";
		}
		function create_list_new_version($client_id,$certain_kp_id = FALSE){
		   global $mysqli;
		   global $user_id;
		   // шаблон ряда таблицы списка КП
		   $tpl_name = 'skins/tpl/clients/client_folder/business_offers/list_table_rows.tpl';
		   $fd = fopen($tpl_name,'r');
		   $rows_template = fread($fd,filesize($tpl_name));
		   fclose($fd);
		   
		   $rows = '';
		   
		   $query="SELECT*FROM `".COM_PRED_LIST."` WHERE `client_id` = '".$client_id."'";
		   if($certain_kp_id)$query.= " AND id = '".$certain_kp_id."'";
		   $query.= " ORDER BY id DESC";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		        ob_start();
		        while($row=$result->fetch_assoc()){ //
				
					 $date_arr = explode("-",substr($row['create_time'],0,10));
					 $date = implode(".",array_reverse($date_arr));
					 $order_num = $row['order_num'] ;
					 $comment = 'добавьте свой комментарий' ;
					 $comment = (!empty($row['comment']))? $row['comment'] : 'добавьте свой комментарий' ;
					 $comment_style = (trim($comment) == 'добавьте свой комментарий')? 'italic grey' : '' ;
					 eval(' ?>'.$rows_template.'<?php ');
				}
				$rows .= ob_get_contents();
		        ob_get_clean();	
				
				
	        }
			return $rows;
		}
		function create_list_old_version($client_id,$certain_kp_filename = FALSE){
		   
            $rows = '';
		    $prefix = '../admin/order_manager/';
            $dir_name = $prefix.'data/com_offers/'.$client_id;
			
			if(file_exists($dir_name)){
				// считываем комментарии
				$comments_file_path = $dir_name.'/comments.txt';
				if(file_exists($comments_file_path)){
					$srting_arr = file($comments_file_path);
					foreach($srting_arr as $comment_srting){
						list($file_name,$comment) = explode(';',$comment_srting);
						//$file_name = trim(iconv("windows-1251","UTF-8", $file_name));
						//$file_name = trim(strtr($file_name,' ',''));
						$comments_arr[md5($file_name)] = $comment ;
					}	 
				}
				
				// считываем файлы КП из директории
				$dir = opendir($dir_name);
				while($file = readdir($dir)){// считываем файлы и создаем массив с датой создания в качестве ключа
					if($file != "." && $file != ".." && $file != "comments.txt"){ 
						 //echo $dir_name.'/'.$file.'<br>';
						 if(!$certain_kp_filename){ 
						     $data_mod = date("ymdHis", filemtime($dir_name.'/'.$file));
						     $file_arr[$data_mod] = array(date("d.m.Y", filemtime($dir_name.'/'.$file)),basename($dir_name.'/'.$file));
						 }
						 else if($certain_kp_filename && $file == $certain_kp_filename) {
						     $data_mod = date("ymdHis", filemtime($dir_name.'/'.$file));
						     $file_arr[$data_mod] = array(date("d.m.Y", filemtime($dir_name.'/'.$file)),basename($dir_name.'/'.$file));
						 }
					}
				}
				closedir($dir);
				
				// альтернатива предыдушей комбинации
				//foreach(glob($dir_name.'/*.doc') as $file){
					// echo $file.'<br>'; 
					// функция stat не подходит под unix //$stat = stat($file);
					//if($user_nickname == 'andrey') echo $file.' '.$stat[10].' '.filemtime($file).' ';
					//$data_mod = date("ymdHis", filemtime($file));
					// if($user_nickname == 'andrey') echo $data_mod.' <br>';
					// $file_arr[$data_mod] = array(date("d.m.Y", filemtime($file)),basename($file));
				 //}
				 //clearstatcache();
				 krsort($file_arr);
				 // if($user_nickname == 'andrey') 
				 /*
				 echo '<pre>';
				 print_r($file_arr);
				 echo '</pre>';
				*/
				 // шаблон ряда таблицы
				 $tpl_name = 'skins/tpl/clients/client_folder/business_offers/list_table_rows_old_version.tpl';
				 $fd = fopen($tpl_name,'r');
				 $rows_template = fread($fd,filesize($tpl_name));
				 fclose($fd);
				 
				 ob_start();
				
				 
				 foreach($file_arr as $key => $file_data){
					 $new_com_pred_format = (strrpos($file_data[1],'_')==33)? true: false;
					 $file_name_arr = explode('_',substr($file_data[1],0,strrpos($file_data[1],'.')));
					 $order_num = ($new_com_pred_format)? $file_name_arr[count($file_name_arr)-2]:'';
					 $com_pred_num = ($new_com_pred_format)? $file_name_arr[count($file_name_arr)-1]:'';
					 $com_pred_data = get_com_offer_data($new_com_pred_format,$com_pred_num);
				 
					 
					 list($date,$file) = $file_data;
					 $file = trim(iconv("windows-1251", "UTF-8", $file));
					 $comment = (isset($comments_arr[md5($file)]))? $comments_arr[md5($file)] : 'добавьте свой комментарий' ;
					 $comment_style = (trim($comment) == 'добавьте свой комментарий')? 'italic grey' : '' ;
					 //$file = trim($file);
					 eval(' ?>'.$rows_template.'<?php ');
				}	
				$rows .= ob_get_contents();
				ob_get_clean();	
			}
			return $rows;
		}
   }












?>