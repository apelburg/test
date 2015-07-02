<?php
    class rtCalculators{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function grab_data($data){
		    // начальный общий метод для всех услуг (получение информации по калькуляторам)
			
			// ветвим
			// если расчитываем нанесение
            if($data->type=='print') self::grab_data_for_print($data);
	
		}
		static function fetch_dop_uslugi_for_row($dop_data_row_id){
		    global $mysqli; 
			
			$out_put = array();
			  
            // получаем данные о расчетах дополнительных услуг для данного ряда расчета
			$query="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `glob_type` = 'print' AND `dop_row_id` = '".$dop_data_row_id."'";
			//echo $query;
			
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
					$out_put[] = $row;	
				}
			}
            //print_r($out_put);
			echo json_encode($out_put);
			
		}
		static function grab_data_for_print($data){
		    // global $mysqli;   
			// print_r($data);
			
			
			// в результате действия метода будет получен массив всех данных для артикула необходимых для совершения
			// расчетов в калькуляторе
			// массив 
			// 
            //  Array  (
            //            [places] => масссив мест нанесений (каждое из которых содержит массив типов нанесений)
            //            [print_types] => масссив типов нанесений, которые могут быть использованны применительно к данному артикулу,
			//                             вынесен в отдельный массив, чтобы избежать дублирования так как разные места нанесений могут
			//                             содержать один и тот же тип нанесения.
			//                             каждый отдельный элемент массива соответсвует отдельному нанесению и содержит информацию о 
			//                             цветах применяемых при печате, размерах печати и таблицы - прайсы с расценками нанесения.
			//          )
           
			
			// ищем типы нанесения присвоенные данному артикулу на прямую 
			// возврашаемое значение: массив содержащий один элемент обозначающий (имитирующий)
			// стандартное (дефолтное) место нанесения с вложенными в него типами нанесения 
			$out_put = self::get_related_art_and_print_types($data->art_id);
			
			// получаем (если установленны) данные о конкретных местах нанесения для данного артикула
			// если были найдены места добавляем их в масив $out_put
			// и заполняем их данным о присвоенных местам типам нанесиния 
            $out_put = self::get_related_print_places($out_put,$data->art_id);

		    // получаем дополнительные данные соответсвующие нанесениям ( возможные размеры, цвета, таблицы прайсов )
            $out_put = self::get_print_types_related_data($out_put);
			
			//print_r($out_put);
			echo json_encode($out_put);
			//return print_r($out_put,true);
			return $out_put;
		
		}
		static function get_related_art_and_print_types($art_id){
		    global $mysqli;  
			$places = array();
			$print_types = array();
			//UPDATE `new__base__print_mode` SET `print_id`=13 WHERE `print` = 'шелкография'
			// получаем данные о типах нанесений соответсвующих данному артикулу на прямую
			$query="SELECT*FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    $places[0]['name'] = 'Стандартно';	
			    while($row = $result->fetch_assoc()){
					$places[0]['print'][$row['print_id']] = $row['print'];	
					$print_types[$row['print_id']]['sizes'][0][] = array("item_id" => "0", "percentage"=>"1.00","size"=> "Стандартно"); 
					//$print_types[$row['print_id']]['sizes'][0][] = array("item_id" => "0", "percentage"=>"1.00","print_id"=> '"'.$print_id.'"',"size"=> "Стандартно");   
					//$print_types[$row['print_id']]['sizes'][0][] = array("item_id" => "0", "percentage"=>"1.00", "print_id"=>$print_id,"size"=> "Стандартно");   
				}/**/
				
			}
			else $places[0] = false;	
			
			return array('places'=>$places,'print_types'=>$print_types);
		}
		static function get_related_print_places($out_put,$art_id){
		    global $mysqli;  
			 
			// получаем данные о местах нанесений соответсвующих данному артикулу
			$query="SELECT tbl1.`place_id` place_id, tbl2.`name` name  FROM `".BASE__ART_PRINT_PLACES_REL_TBL."` tbl1 
			        INNER JOIN  `".BASE__PRINT_PLACES_TYPES_TBL."` tbl2 
					ON tbl1.`place_id`  = tbl2.`id`
                    WHERE tbl1.`art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
				    // получаем данные о типах нанесений соответсвующих данному месту
					$query_2="SELECT  tbl1.print_id print_id,tbl2.name name FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` tbl1
					          INNER JOIN  `".OUR_USLUGI_LIST."` tbl2 
					          ON tbl1.`print_id`  = tbl2.`id` 
							  WHERE tbl1.`place_id` = '".$row['place_id']."'";
					
					$result_2 = $mysqli->query($query_2)or die($mysqli->error);/**/
					if($result_2->num_rows>0){
					    $print_types = array();
						while($row_2 = $result_2->fetch_assoc()){
						    if(!isset($out_put['print_types'][$row_2['print_id']])) $out_put['print_types'][$row_2['print_id']] = array();	
							
						    $print_types[$row_2['print_id']] = $row_2['name'];
						}
						// добавляем результат в итоговый массив ключем устанавливаем id места нанесения
						$out_put['places'][$row['place_id']]['name'] = $row['name'];
						$out_put['places'][$row['place_id']]['print'] = $print_types;		
					}			
				}
			}
			
			return $out_put;
		}
		static function get_print_types_related_data($out_put){
		    global $mysqli;  
			
			
			 
			//$print_types = $out_put['print_types'];
			
			// 
			foreach($out_put['print_types'] as $print_id => $val){
			
			    // выбираем дополнительные параметры  определяющие вертикальную позицию в прайсе (такие как например цвета)
				$query="SELECT*FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE `print_type_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    
				    while($row = $result->fetch_assoc()){
					    $out_put['print_types'][$print_id]['y_price_param'][$row['value']] = array('percentage'=>$row['percentage'],'item_id'=>$row['id']);   
				    }
				}
				
				
				// выбираем таблицу с расценками 
				$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_id."' ORDER by id, param_val";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $count = $row['count'];
					    unset($row['id'],$row['print_type_id'],$row['count']);
						
						if(!isset($end[$row['price_type']]))$end[$row['price_type']] = false;
						
						if($row['param_val']==0){
						      $end_counter = 0;
							  foreach($row as $key => $val){
								  if($key!='param_val' && $key!='param_type'){
									  //echo $val;
									  if($val==0 && !$end[$row['price_type']]) $end[$row['price_type']] = $end_counter;
								  } 
								  $end_counter++;
							  }
						}
						
						if($end[$row['price_type']]){
							 $counter = 0;
							 foreach($row as $key => $val){
								  if($counter>=$end[$row['price_type']]){
									  unset($row[$key]);
								  } 
								  $counter++;
							  }
						  }	
						
					    if($row['price_type']=='out') $out_put['print_types'][$print_id]['priceOut_tbl'][$count][] = $row; 
						else if($row['price_type']=='in')  $out_put['print_types'][$print_id]['priceIn_tbl'][$count][] = $row;  
				    }
				}
				
				
				// выбираем данные по размерам нанесения в соответствии с типом и местом нанесения
				$query="SELECT*FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE `print_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $place_id = $row['place_id'];
						$row['item_id'] = $row['id'];
					    unset($row['id'],$row['place_id']);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения и id места нанесения
					    $out_put['print_types'][$print_id]['sizes'][$place_id][] = $row;   
				    }
				}
				
				
				// выбираем данные по коэффициэнтам влияющим на цену товара
				$query="SELECT*FROM `".BASE__CALCULATORS_COEFFS."` WHERE `print_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				   $data = $coeff_data= array();
				    while($row = $result->fetch_assoc()){
					    $coeff_data[$row['target']][$row['type']][] = array('item_id'=>$row['id'],'title'=>$row['title'],'coeff'=>$row['percentage']);
						$data[$row['target']][$row['type']] =  array('optional'=>$row['optional'],'multi'=>$row['multi'],'data'=>$coeff_data[$row['target']][$row['type']]);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения
					    $out_put['print_types'][$row['print_id']]['coeffs'][$row['target']] = $data[$row['target']];   
				    }
				}
				
				// выбираем данные по надбавкам влияющим на цену товара
				$query="SELECT*FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE `print_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    $data = $additions_data= array();
				    while($row = $result->fetch_assoc()){
					    $additions_data[$row['target']][$row['type']][] = array('item_id'=>$row['id'],'title'=>$row['title'],'value'=>$row['value']);
						$data[$row['target']][$row['type']] =  array('optional'=>$row['optional'],'multi'=>$row['multi'],'data'=>$additions_data[$row['target']][$row['type']]);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения
					    $out_put['print_types'][$row['print_id']]['additions'][$row['target']] = $data[$row['target']];   
				    }
				}
				// НУЖНА ЕЩЕ ИНФОРМАЦИЯ О СТОИМОСТИ ПОДГОТОВИТЕЛЬНЫХ РАБОТ
			}
			
			return $out_put;
		}
		
		static function json_fix_cyr($json_str) { 
			$cyr_chars = array ( 
			'\u0430' => 'а', '\u0410' => 'А', 
			'\u0431' => 'б', '\u0411' => 'Б', 
			'\u0432' => 'в', '\u0412' => 'В', 
			'\u0433' => 'г', '\u0413' => 'Г', 
			'\u0434' => 'д', '\u0414' => 'Д', 
			'\u0435' => 'е', '\u0415' => 'Е', 
			'\u0451' => 'ё', '\u0401' => 'Ё', 
			'\u0436' => 'ж', '\u0416' => 'Ж', 
			'\u0437' => 'з', '\u0417' => 'З', 
			'\u0438' => 'и', '\u0418' => 'И', 
			'\u0439' => 'й', '\u0419' => 'Й', 
			'\u043a' => 'к', '\u041a' => 'К', 
			'\u043b' => 'л', '\u041b' => 'Л', 
			'\u043c' => 'м', '\u041c' => 'М', 
			'\u043d' => 'н', '\u041d' => 'Н', 
			'\u043e' => 'о', '\u041e' => 'О', 
			'\u043f' => 'п', '\u041f' => 'П', 
			'\u0440' => 'р', '\u0420' => 'Р', 
			'\u0441' => 'с', '\u0421' => 'С', 
			'\u0442' => 'т', '\u0422' => 'Т', 
			'\u0443' => 'у', '\u0423' => 'У', 
			'\u0444' => 'ф', '\u0424' => 'Ф', 
			'\u0445' => 'х', '\u0425' => 'Х', 
			'\u0446' => 'ц', '\u0426' => 'Ц', 
			'\u0447' => 'ч', '\u0427' => 'Ч', 
			'\u0448' => 'ш', '\u0428' => 'Ш', 
			'\u0449' => 'щ', '\u0429' => 'Щ', 
			'\u044a' => 'ъ', '\u042a' => 'Ъ', 
			'\u044b' => 'ы', '\u042b' => 'Ы', 
			'\u044c' => 'ь', '\u042c' => 'Ь', 
			'\u044d' => 'э', '\u042d' => 'Э', 
			'\u044e' => 'ю', '\u042e' => 'Ю', 
			'\u044f' => 'я', '\u042f' => 'Я', 
			
			'\r' => '', 
			'\n' => '<br />', 
			'\t' => '' 
			); 

			foreach ($cyr_chars as $cyr_char_key => $cyr_char) { 
			    $json_str = str_replace($cyr_char_key, $cyr_char, $json_str); 
			} 
			return $json_str; 
         } 
		static function save_calculatoins_result($details_obj){
		    global $mysqli;  
			
			print_r($details_obj);
			 
           
            // если PHP 5.4 то достаточно этого
               /* $print_details = json_encode($details_obj->print_details,JSON_UNESCAPED_UNICODE);*/
			// но пришлось использовать это
			$print_details = self::json_fix_cyr(json_encode($details_obj->print_details)); 

			// если нет dop_uslugi_id или он равен ноль, добавляем новый расчет доп услуг для ряда 
			// иначе перезаписываем данные в строке где `id` = $details_obj->dop_uslugi_id
			if(!isset($details_obj->dop_uslugi_id) || $details_obj->dop_uslugi_id ==0){
			    $query="INSERT INTO `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `glob_type` ='print',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `print_details` ='".$print_details."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
				 //echo 1;
			}
			else if(isset($details_obj->dop_uslugi_id) && $details_obj->dop_uslugi_id !=0){
			   $query="UPDATE `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `glob_type` ='print',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = '".$details_obj->price_in."',
									   `price_out` ='".$details_obj->price_out."',
									   `print_details` ='".$print_details."'
									    WHERE `id` ='".$details_obj->dop_uslugi_id."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
		}
		static function delete_prints_for_row($dop_row_id,$usluga_id,$all){
		    global $mysqli;  
			
			// если надо удалить все расчеты нанесения
			if($all && !$usluga_id){
			    $query="DELETE FROM `".RT_DOP_USLUGI."` WHERE
									   `dop_row_id` ='".$dop_row_id."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			
			}
			else if($usluga_id && !$all){
			     $query="DELETE FROM `".RT_DOP_USLUGI."` WHERE
									   `id` ='".$usluga_id."' AND `dop_row_id` ='".$dop_row_id."' "; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
			}
		}
		static function change_quantity_and_calculators($quantity,$dop_data_id){
		    global $mysqli;  
			$itog_sums = array("summ_in"=>0,"summ_out"=>0);
			// делаем запрос чтобы получить данные о всех расчетах нанесений привязанных к данному ряду
		    $query="SELECT uslugi.print_details print_details, uslugi.id uslugi_row_id FROM `".RT_DOP_USLUGI."` uslugi INNER JOIN
			                    `".RT_DOP_DATA."` dop_data
								  ON dop_data.`id` =  uslugi.`dop_row_id`
			                      WHERE uslugi.glob_type ='print' AND dop_data.`id` = '".$dop_data_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				while($row = $result->fetch_assoc()){
				    // детали расчета нанесения
					$print_details_obj = json_decode($row['print_details']);
					//print_r($print_details_obj->dop_params);echo "\r\n";//
					$YPriceParam = (isset($print_details_obj->dop_params->YPriceParam))? count($print_details_obj->dop_params->YPriceParam):1;
					// получаем новые исходящюю и входящюю цену исходя из нового таража
					$new_price_arr = self::change_quantity_and_calculators_price_query($quantity,$print_details_obj->print_id,$YPriceParam);
					//print_r($new_price_arr);echo "\r\n";//
					
					// рассчитываем окончательную стоимость с учетом коэффициентов и надбавок
					$new_data = self::make_calculations($quantity,$new_price_arr,$print_details_obj->dop_params);
					
					// перезаписываем новые значения прайсов и X индекса обратно в базу данных
					$query2="UPDATE `".RT_DOP_USLUGI."` 
					              SET 
								  quantity = '".$quantity."',
								  price_in = '".$new_data["new_price_arr"]["price_in"]."',
								  price_out = '".$new_data["new_price_arr"]["price_out"]."'
			                      WHERE id = '".$row['uslugi_row_id']."'";
					$mysqli->query($query2)or die($mysqli->error);
					
					$itog_sums["summ_in"] += $new_data["new_summs"]["summ_in"];
					$itog_sums["summ_out"] += $new_data["new_summs"]["summ_out"];
					//print_r($new_summs);
					//echo "\r\n";
				}
				
				// если дошли до этого места значит все нормально
				// отправляем новые данные обратно клиенту
				// print_r($itog_sums);
				echo '{"result":"ok","row_id":'.$dop_data_id.',"new_sums":'.json_encode($itog_sums).''.(isset($new_price_arr['lackOfQuantity'])?',"lackOfQuantity":"1","minQuantInPrice":"'.$new_price_arr['minQuantInPrice'].'"':'').''.(isset($new_price_arr['outOfLimit'])?',"outOfLimit":"1","limit":"'.$new_price_arr['limit'].'"':'').''.(isset($new_price_arr['needIndividCalculation'])?',"needIndividCalculation":"1"':'').'}';
			}
		}
		static function change_quantity_and_calculators_price_query($quantity,$print_id,$YPriceParam){
		    global $mysqli;  
			
			$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_id."' ORDER by id, param_val";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				   $priceIn_tblXindex = 0;
				    while($row = $result->fetch_assoc()){
					    //print_r($row);
						if($row['param_val']==0){
						    
						    // здесь мы определяем в какой диапазон входит новое количество
							for($i=1;isset($row[$i]);$i++){
							    // если оно меньше минимального тиража
							    
								//if($row[$i] > $quantity) break;
								if($row['price_type']=='in'){
									if($quantity < $row[1]){
										$newIn_Xindex = 1;
										$lackOfQuantInPrice = true;
										$minQuantInPrice  = $row[1];
									}
								    else if($row[$i] >0 && $quantity >= $row[$i]){
										$newIn_Xindex = $i;
									}
								    if($row[$i] >0){
									    $in_limit = $row[$i];
									    $in_limitIndex = $i;
									}
								}
								if($row['price_type']=='out'){
									if($quantity < $row[1]){
										$newOut_Xindex = 1;
										$lackOfQuantOutPrice = true;
										$minQuantOutPrice  = $row[1];
									}
								    else if($quantity >= $row[$i]  &&  $row[$i] >0){
										$newOut_Xindex = $i;
									}
									if($row[$i] >0){
										$out_limit = $row[$i];
										$out_limitIndex = $i;
									}
								}
							}
						}
						// определяем новые входящие и исходящие цены
						if($row['price_type']=='in' && $row['param_val']==$YPriceParam) $new_priceIn = $row[$newIn_Xindex];
						if($row['price_type']=='out' && $row['param_val']==$YPriceParam) $new_priceOut = $row[$newOut_Xindex];   
				    }
					$out = array("price_in"=> $new_priceIn,"price_out"=> $new_priceOut);
					
					// если тираж был меньше минимального значения в прайсе пересчитываем цены
					if(isset($lackOfQuantIntPrice) && $lackOfQuantInPrice==true){
					    $out['price_in'] = $new_priceIn*$minQuantInPrice/$quantity;
						$out['lackOfQuantity'] = true;
						$out['minQuantInPrice'] = (int)$minQuantInPrice;
					}
					if(isset($lackOfQuantOutPrice) && $lackOfQuantOutPrice==true){
					    $out['price_out'] = $new_priceOut*$minQuantOutPrice/$quantity;
						$out['lackOfQuantity'] = true;
						$out['minQuantInPrice'] = (int)$minQuantInPrice;
					}
					
					//echo $newIn_Xindex .' - '. $in_limitIndex;  echo "\r" ;
                    //echo $newOut_Xindex .' - '. $out_limitIndex;
					
					// если полученная цена оказалась равна 0 то значит стоимость не указана
					if((float)($out['price_in']) == 0 ||(float)($out['price_out']) == 0){

						// если это последние ряды прайс значит это лимит
						if($newIn_Xindex == $in_limitIndex){
							$out['outOfLimit'] = true;
							$out['limit'] = (int)$out_limit;
						}
						elseif($newOut_Xindex == $out_limitIndex){
							$out['outOfLimit'] = true;
							$out['limit'] = (int)$out_limit;
						}
						else{//иначе это индивидуальный расчет cancelCalculator
							if(!isset($out['outOfLimit']))
							$out['needIndividCalculation'] = true;
						}
					}
					// echo "\r \$YPriceParam - ".$YPriceParam."\r In".$newIn_Xindex.' '.$new_priceIn; echo "\r Out".$newOut_Xindex.' '.$new_priceOut."\r";
					return $out;
				}
		}
		static function make_calculations($quantity,$new_price_arr,$print_dop_params){
			
			$price_coeff = $summ_coeff = 1;
			$price_addition = $summ_addition = 0;
			$new_summs = array();
			//print_r($print_dop_params);
			
		    // КОЭФФИЦИЕНТЫ НА ПРАЙС
			// КОЭФФИЦИЕНТЫ НА ИТОГОВУЮ СУММУ
			// НАДБАВКИ НА ПРАЙС
			// НАДБАВКИ НА ИТОГОВУЮ СУММУ
			foreach($print_dop_params as $glob_type => $set){
				
				if($glob_type=='YPriceParam' || $glob_type=='sizes'){
					foreach($set as $data){
					    // подстраховка
						if($data->coeff == 0) $data->coeff = 1;
						
						//echo "coeff ".$data->coeff."\r\n";
						$price_coeff *= (float)$data->coeff;
					}
				}
				if($glob_type=='coeffs'){
					foreach($set as $target => $data){
					    foreach($data as $type => $details){
							for($i = 0;$i < count($details);$i++){ 
							    // подстраховка
							    if((isset($details[$i]->multi)) && $details[$i]->multi == 0) $details[$i]->multi = 1;
								if($details[$i]->value == 0) $details[$i]->value = 1;
								
								if($target=='price'){
								     // echo 'coeffs price';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
								     $price_coeff *= (isset($details[$i]->multi))?  $details[$i]->value*$details[$i]->multi : $details[$i]->value;
								}
								if($target=='summ'){
									 // echo 'coeffs summ';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
									 $summ_coeff *= (isset($details[$i]->multi))?  $details[$i]->value*$details[$i]->multi : $details[$i]->value;
								}
							}								
						}
					}
				}
				if($glob_type=='additions'){
					foreach($set as $target => $data){
					    foreach($data as $type => $details){
							for($i = 0;$i < count($details);$i++){ 
							    // подстраховка
							    if((isset($details[$i]->multi)) && $details[$i]->multi == 0) $details[$i]->multi = 1;
								
								if($target=='price'){
								    // echo 'additions price';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
								     $price_addition += (isset($details[$i]->multi))?  $details[$i]->value*$details[$i]->multi : $details[$i]->value;
								}
								if($target=='summ'){
								     // echo 'additions summ';echo "\r\n"; echo $details[$i]->value;echo "\r\n";print_r($details[$i]);
									 $summ_addition += (isset($details[$i]->multi))?  $details[$i]->value*$details[$i]->multi : $details[$i]->value;
								}
							}								
						}
					}
				}
			} 
			
			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!  
			// CXEMA  - new_summ = ((((price*price_coeff)+price_addition)*quantity)*sum_coeff)+sum_addition
			//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! 
			
			
			$new_summs["summ_in"] = round((((($new_price_arr['price_in']*$price_coeff)+$price_addition)*$quantity)*$summ_coeff)+$summ_addition,2);
			$new_summs["summ_out"] = round((((($new_price_arr['price_out']*$price_coeff)+$price_addition)*$quantity)*$summ_coeff)+$summ_addition,2);
		
			
			//echo "all_coeffs ".$all_coeffs."\r\n";
			$new_price_arr["price_in"] =  round($new_summs["summ_in"]/$quantity,2);
			$new_price_arr["price_out"] = round($new_summs["summ_out"]/$quantity,2);
			$new_summs["summ_in"] = round($new_price_arr["price_in"]*$quantity,2);
			$new_summs["summ_out"] = round($new_price_arr["price_out"]*$quantity,2);
			
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_in"]." \r";
			//echo "all_coeffs ".$all_coeffs." ".$new_summs["summ_out"]."\r\n";
			
			return array("new_summs"=>$new_summs,"new_price_arr"=>$new_price_arr);
		}
		
    }

?>