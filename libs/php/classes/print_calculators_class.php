<?php
    class printCalculator{
	    function __consturct(){
		}
		static function convert_print_details($print_details){
		
		    global $mysqli;
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			// echo '<pre>'; print_r($print_details); echo '</pre>';//
			$out_put = array();
			$out_put[] = $print_details->print_type;
			$out_put[] = 'место нанесения: '.$print_details->place_type;
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
					    $type = $row['param_type'];
					    $prefix = $type.': ';
						$result->data_seek(0);
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   //echo '<pre>'; print_r($row); echo '</pre>';
						   $tail = ($row['percentage']>1)?' - увелич. на '.$row['percentage'].'%':'';
						   $out_put[] = $prefix.$row['value'].$tail;
						   $prefix='';
						}
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){

						$prefix = 'Размер нанесния: ';
						while($row = $result->fetch_assoc()) {
						   // echo '<pre>'; print_r($row); echo '</pre>';
						   $tail = ($row['percentage']>1)?' - увелич. на '.$row['percentage'].'%':'';
						   $out_put[] = $prefix.$row['size'].$tail;
						   $prefix='';
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
							if($details->id!=0){
								$idsArr[] = $details->id;
								$dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						$prefix='Коэффициэнты: ';
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   $multi = ($row['multi']>1)? ' '.$row['multi'].' раза ':'';
						   $out_put[] = $prefix.$row['title'].' увелич. на '.$row['percentage'].'%'.$dop_details[$row['id']]['multi'];
						   $prefix='';
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
				            if($details->id!=0){
							    $idsArr[] = $details->id;
							    $dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза по ':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						$prefix='Надбавки:';
						while($row = $result->fetch_assoc()) {
						   // echo '<pre>'; print_r($row); echo '</pre>';
						   $out_put[] = $prefix.$row['title'].' +'.$dop_details[$row['id']]['multi'].''.$row['value'].'руб.';
						   $prefix='';
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
	
			//echo '<pre>'; print_r($out_put); echo '</pre>';//
			//echo implode(', ',$out_put);
			//exit; 
		    return implode(', ',$out_put);
		}
		static function convert_print_details_to_dop_tech_info($print_details){
		
		    global $mysqli;
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			// echo '<pre>'; print_r($print_details); echo '</pre>';//
			$out_put = array();
			
			// если пустые значения не создаем элемент
			if(isset($print_details->place_type) && trim($print_details->place_type) !=''){
			    if(trim($print_details->place_type) !='Стандартно' && trim($print_details->place_type) !='стандартно')  $out_put['mesto_pechati'] = base64_encode($print_details->place_type);
		    }
			
			
			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0){
					    $idsArr[] = $details->id;
						$CMYKArr[$details->id] = (isset($details->cmyk))?$details->cmyk:'';
					}	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
					    //$type = $row['param_type'];
						$result->data_seek(0);
						while($row = $result->fetch_assoc()) {
                            $arr[] = $row['value'].' '.base64_decode($CMYKArr[$row[id]]);
						}
                        // если пустые значения не создаем элемент
						if(isset($arr)){
						    $out_put['Pantone'] =  base64_encode(implode(', ',$arr));
						    $out_put['kolvo_cvetov'] =  base64_encode(count($arr));
						    unset($arr);
						} 
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   $out_put['razmer_pechati'] = base64_encode($row['size']);
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
							if($details->id!=0){
								$idsArr[] = $details->id;
								$dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' ('.$details->multi.' раз)':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   $arr[] = $row['title'].' '.$dop_details[$row['id']]['multi'];
						}
						// если пустые значения не создаем элемент
						if(isset($arr)){
						    $out_put['dop_opcii'] =  base64_encode(implode(', ',$arr));
						    unset($arr);
						} 
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
				            if($details->id!=0){
							    $idsArr[] = $details->id;
							    $dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' ('.$details->multi.' раз)':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()) {
						   // echo '<pre>'; print_r($row); echo '</pre>';
						   $arr[] = $row['title'].''.$dop_details[$row['id']]['multi'];
						   $prefix='';
						}
						// если пустые значения не создаем элемент
						if(isset($arr)){
						    if(isset($out_put['dop_opcii'])) $out_put['dop_opcii'] .=  ', '.base64_encode(implode(', ',$arr));
							else $out_put['dop_opcii'] = base64_encode(implode(', ',$arr));
						    unset($arr);
						} 
					    
						unset($arr);
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			// echo '<pre>'; print_r($out_put); echo '</pre>';//
			return json_encode($out_put);
		}
		static function convert_print_details_to_arr($print_details){
		
		    global $mysqli;     
			
		    // если данные были переданы ввиде json преобразуем их в объект
		    $print_details = (!is_object($print_details))? json_decode($print_details):$print_details;
			//echo '<pre>'; print_r($print_details); echo '</pre>';
			$out_put = array();
			$out_put['print_type'] = $print_details->print_type;
			$out_put['place'] = $print_details->place_type;
			if(isset($print_details->lackOfQuantInPrice)){
			    $out_put['lackOfQuantityInPrice']['minVal'] = $print_details->minQuantInPrice;
			}
			if(isset($print_details->lackOfQuantOutPrice)){
			    $out_put['lackOfQuantityOutPrice']['minVal'] = $print_details->minQuantOutPrice;
			}
			
			if(isset($print_details->dop_params->sizes)){
			    foreach($print_details->dop_params->sizes as $index => $details){
				         // echo '<pre>22'; print_r($details); echo '</pre>';
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE id IN('".implode("','",$idsArr)."')";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
                        $counter = 0;
						while($row = $result->fetch_assoc()) {
						   $out_put['place_size']['params'][$counter]['name'] = $row['size'];
						   $out_put['place_size']['params'][$counter]['target'] = 'price';
						   $out_put['place_size']['params'][$counter]['target_ru'] = 'к цене';
						   $out_put['place_size']['params'][$counter]['value'] = $row['percentage'];
						   $counter++;
						}
					}
					unset($idsArr);
					unset($details); 
					unset($dop_details); 
				}
			}

			if(isset($print_details->dop_params->YPriceParam)){
			    foreach($print_details->dop_params->YPriceParam as $index => $details){
				    if($details->id!=0) $idsArr[] = $details->id;	
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id IN('".implode("','",$idsArr)."') ORDER BY percentage";
					// echo $query;
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
					    $row = $result->fetch_assoc();
						$out_put['YPriceParam']['name'] = $row['param_type'];
						$result->data_seek(0);
						$counter = 0;
						while($row = $result->fetch_assoc()) {
						   //$tail = ($target==$row['price'])?'%':'руб.';
						   //echo '<pre>'; print_r($row); echo '</pre>';
						   //$tail = ($row['percentage']>1)?' - увелич. на '.$row['percentage'].'%':'';
						   $out_put['YPriceParam']['params'][$counter]['name'] = $row['value'];
						   $out_put['YPriceParam']['params'][$counter]['target'] = 'price';
						   $out_put['YPriceParam']['params'][$counter]['target_ru'] = 'к цене';
						   $out_put['YPriceParam']['params'][$counter]['value'] = $row['percentage'];
						   $counter++;
						}
					}
					unset($idsArr); 
					unset($details);
					unset($dop_details); 
				}
			}
			
		    if(isset($print_details->dop_params->coeffs)){
			    foreach($print_details->dop_params->coeffs as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
							if($details->id!=0){
								$idsArr[] = $details->id;
								$dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_COEFFS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){

						$out_put['coeffs']['name'] = 'Коэффициэнт';
						$counter = 0;
						while($row = $result->fetch_assoc()) {
						   $out_put['coeffs']['params'][$counter]['name'] = $row['title'];
						   $out_put['coeffs']['params'][$counter]['target'] = $row['target'];
						   $out_put['coeffs']['params'][$counter]['target_ru'] = ($row['target']=='price')? 'к цене':'к сумме';
						   $out_put['coeffs']['params'][$counter]['value'] = $row['percentage'];
						   $out_put['coeffs']['params'][$counter]['multi'] = $dop_details[$row['id']]['multi'];
						   $counter++;
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
			if(isset($print_details->dop_params->additions)){
			    foreach($print_details->dop_params->additions as $target => $data){
					foreach($data as $type => $val){
					    foreach($val as $index => $details){
					        //    echo '<pre>'; print_r($details); echo '</pre>';
				            if($details->id!=0){
							    $idsArr[] = $details->id;
							    $dop_details[$details->id]['multi'] = (isset($details->multi) && $details->multi>1)?' '.$details->multi.' раза по ':'';
							}
						}
					}
				}
				if(isset($idsArr)){
					$query = "SELECT * FROM `".BASE__CALCULATORS_ADDITIONS."` WHERE id IN('".implode("','",$idsArr)."')";
					$result = $mysqli->query($query)or die($mysqli->error);
					if($result->num_rows > 0){
						$out_put['additions']['name'] = 'Надбавки';
						$counter = 0;
						while($row = $result->fetch_assoc()) {
						   $out_put['additions']['params'][$counter]['name'] = $row['title'];
						   $out_put['additions']['params'][$counter]['target'] = $row['target'];
						   $out_put['additions']['params'][$counter]['target_ru'] = ($row['target']=='price')? 'к цене':'к сумме';
						   $out_put['additions']['params'][$counter]['value'] = $row['value'];
						   $out_put['additions']['params'][$counter]['multi'] = $dop_details[$row['id']]['multi'];
						   $counter++;
						}
					}
					unset($idsArr); 
					unset($details); 
					unset($dop_details); 
				}
			}
		    return $out_put;
		}
		
    }

?>