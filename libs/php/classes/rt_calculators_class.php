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
			
			// получаем данные о типах нанесений соответсвующих данному артикулу на прямую
			$query="SELECT*FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    $places[0]['name'] = 'Стандартно';	
			    while($row = $result->fetch_assoc()){
					$places[0]['data'][] = $row['print'];	
					$print_types[$row['print']] = array();
				}
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
					$query_2="SELECT  tbl1.print_id print_id,tbl2.name name FROM `".BASE__PRINT_PLACES_PRINT_TYPES_REL_TBL."` tbl1
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
						$out_put['places'][$row['place_id']]['data'] = $print_types;		
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
			
			    // выбираем дополнительные параметры соответствующие данному нанесению (такие как например цвета)
				$query="SELECT*FROM `".BASE__DOP_PARAMS_FOR_PRINT_TYPES_TBL."` WHERE `print_type_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    
				    while($row = $result->fetch_assoc()){
					    $out_put['print_types'][$print_id][$row['param_type']][$row['value']] = array('percentage'=>$row['percentage'],'item_id'=>$row['id']);   
				    }
				}
				
				
				// выбираем таблицу с расценками 
				$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$print_id."'";
				//echo $query;
				$result = $mysqli->query($query)or die($mysqli->error);/**/
				if($result->num_rows>0){
				    while($row = $result->fetch_assoc()){
					    $count = $row['count'];
					    unset($row['id'],$row['print_type_id'],$row['count']);
					    $out_put['print_types'][$print_id]['price_tbl'][$count][] = $row;   
				    }
				}
				
				
				// выбираем данные по размерам нанесения в соответствии с типом и местом нанесения
				$query="SELECT*FROM `".BASE__PRINT_TYPES_SIZES_PLACES_REL_TBL."` WHERE `print_id` = '".$print_id."'";
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
				
				// НУЖНА ЕЩЕ ИНФОРМАЦИЯ О СТОИМОСТИ ПОДГОТОВИТЕЛЬНЫХ РАБОТ
			}
			
			return $out_put;
		}
		function save_calculatoins_result($details_obj){
		    global $mysqli;  
			
			//print_r($details_obj);
			
			// если нет dop_uslugi_id или он равен ноль, добавляем новый расчет доп услуг для ряда 
			if(!isset($details_obj->dop_uslugi_id) || $details_obj->dop_uslugi_id ==0){
			    $query="INSERT INTO `".RT_DOP_USLUGI."` SET
				                       `dop_row_id` ='".$details_obj->dop_data_row_id."',
									   `glob_type` ='print',
									   `quantity` ='".$details_obj->quantity."',
									   `price_in` = 0,
									   `price_out` ='".$details_obj->price."',
									   `print_details` ='".json_encode($details_obj->print_details)."'"; 
				 //echo $query;
				 $mysqli->query($query)or die($mysqli->error);
				 $new_dop_row_id = $mysqli->insert_id; /**/
				 //echo 1;
			}
		}
    }

?>