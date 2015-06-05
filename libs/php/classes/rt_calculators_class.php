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
			
			print_r($out_put);
			//echo json_encode($out_put);
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
			    while($row = $result->fetch_assoc()){
					$places[0] = $row['print'];	
					$print_types[$row['print']] = array();
				}
			}
			else $places[0] = false;	
			
			return array('places'=>$places,'print_types'=>$print_types);
		}
		static function get_related_print_places($out_put,$art_id){
		    global $mysqli;  
			 
			// получаем данные о местах нанесений соответсвующих данному артикулу
			$query="SELECT*FROM `".BASE__ART_PRINT_PLACES_REL_TBL."` WHERE `art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
				
				    // получаем данные о типах нанесений соответсвующих данному месту
					$query_2="SELECT*FROM `".BASE__PRINT_PLACES_PRINT_TYPES_REL_TBL."` WHERE `place_id` = '".$row['place_id']."'";
					$result_2 = $mysqli->query($query_2)or die($mysqli->error);/**/
					if($result_2->num_rows>0){
					    $print_types = array();
						while($row_2 = $result_2->fetch_assoc()){
						    if(!isset($out_put['print_types'][$row_2['print_id']])) $out_put['print_types'][$row_2['print_id']] = array();	
							
						    $print_types[] = $row_2['print_id'];
						}
						// добавляем результат в итоговый массив ключем устанавливаем id места нанесения
						$out_put['places'][$row['place_id']] = $print_types;		
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
					    $out_put['print_types'][$print_id][$row['param_type']][$row['value']] = array('percentage'=>$row['percentage']);   
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
					    unset($row['id'],$row['place_id']);
						// добавляем результат в итоговый массив ключем устанавливаем id типа нанесения и id места нанесения
					    $out_put['print_types'][$print_id]['sizes'][$place_id][] = $row;   
				    }
				}
				
				// НУЖНА ЕЩЕ ИНФОРМАЦИЯ О СТОИМОСТИ ПОДГОТОВИТЕЛЬНЫХ РАБОТ
			}
			
			return $out_put;
		}
		
		
    }

?>