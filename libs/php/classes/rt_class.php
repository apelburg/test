<?php
// удалить insert_copied_rows_old
    class RT{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function save_rt_changes($data){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `".$data->prop."` = '".$data->val."'  WHERE `id` = '".$data->id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function change_quantity($quantity,$id){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '".$quantity."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function expel_value_from_calculation($id,$val){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `expel` = '".$val."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function change_all_svetofors($idsArr,$val){
		    global $mysqli;   //print_r($data); 
			echo '<pre>'; print_r($idsArr); echo '</pre>';
	      	$query="UPDATE `".RT_DOP_DATA."` SET  `row_status` = '".$val."'  WHERE `row_id` IN('".implode("','",$idsArr)."')";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function change_svetofor($idsArr,$val,$idsArr2){
		    global $mysqli;   //print_r($data); 
	        
			$query="UPDATE `".RT_DOP_DATA."` SET  `row_status` = '".$val."'  WHERE `id` IN('".implode("','",$idsArr)."')";
			//echo $query;
			$mysqli->query($query)or die($mysqli->error);
			
			if($val=='sgreen'){
			    $query="UPDATE `".RT_DOP_DATA."` SET  `row_status` = 'red'  WHERE `row_status` <> 'grey' AND `id` IN('".implode("','",$idsArr2)."')";
			    //echo $query;
			    $mysqli->query($query)or die($mysqli->error);			
			}
		}
		static function svetofor_display_relay($status,$ids){
		    global $mysqli; 
			
			$status = ( $status == 'on')?0:1;
		    $query = "UPDATE `".RT_MAIN_ROWS."` SET `svetofor_display` = '".$status."'  WHERE `id` IN ('".$ids."')";
		    echo $query."\r\n";
			$mysqli->query($query)or die($mysqli->error);
			return 1;
		}
		static function set_order_deadline($ids,$date,$time){
		    global $mysqli; 
			$time = str_replace('.',':',$time).':00';
		    $query = "UPDATE `".RT_DOP_DATA."` SET `shipping_date` = '".$date."',`shipping_time` = '".$time."'  WHERE `row_id` IN('".implode("','",json_decode($ids))."')";
		     //echo $query."\r\n";exit;
			$mysqli->query($query)or die($mysqli->error);
		}
		static function fetch_query_create_time($query_num){
		    global $mysqli; 
			$query = "SELECT create_time FROM `".RT_LIST."` WHERE query_num ='".$query_num."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$row = $result->fetch_assoc();
			$create_time_arr = explode(' ',$row['create_time']);
			$create_time_arr[1] = substr($create_time_arr[1],0,5);
			$create_time_arr2 = explode('-',$create_time_arr[0]);
			return $create_time_arr2[2].'.'.$create_time_arr2[1].'.'.$create_time_arr2[0].' '.$create_time_arr[1];
		}
		static function fetch_theme($query_num){
		    global $mysqli; 
			
			$query = "SELECT theme FROM `".RT_LIST."` WHERE query_num ='".$query_num."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$row = $result->fetch_assoc();
			return $row['theme'];
		}
		static function save_theme($query_num,$theme){
		    global $mysqli; 
			
			$query = "UPDATE`".RT_LIST."`SET theme = '".$mysqli->real_escape_string($theme)."' WHERE query_num ='".$query_num."'";
			$mysqli->query($query) or die($mysqli->error);
		}
		static function fetch_query_client_face($query_num){
		    global $mysqli; 
			
			$query = "SELECT client_face_id FROM `".RT_LIST."` WHERE query_num ='".$query_num."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$row = $result->fetch_assoc();
			$data['id'] = $row['client_face_id'];
			
			if($data['id']!=0){
			    include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/client_class.php");
			    $data['details'] = Client::get_cont_face_details($data['id']);
			}
			
			return $data;
		}
		static function set_cont_face($cont_face_id,$query_num){
		    global $mysqli;
			   
			$query = "UPDATE `".RT_LIST."` SET `client_face_id` = '".$cont_face_id."' WHERE `query_num` = '".$query_num."'"; 
            $mysqli->query($query) or die($mysqli->error);
		}
		static function save_copied_rows_to_buffer($data,$control_num){
		    global $mysqli;   
			RT::save_to_buffer($data,'copied_rows');
		}
		static function save_to_buffer($data,$type){
		    if(!isset($_SESSION['rt']['buffer'])) $_SESSION['rt']['buffer'] = array();
			$_SESSION['rt']['buffer'][$type] = $data;
			//echo '<pre>'; print_r($_SESSION); echo '</pre>';
			return 1;
		}
		static function shift_rows_down($place_id,$mainCopiedRowId,$shift_counter /* $place_id - куда вставляем, $pos_id - что будем вставлять */){
		    global $mysqli;
			
			// опускаем ряды в таблице чтобы освободить место
		    $query_enlarge = "UPDATE `".RT_MAIN_ROWS."` SET `id` = `id` + 1  WHERE `id` >= '".$place_id."' ORDER BY `id` DESC";
			// echo $query_enlarge."\r\n";
			$result_enlarge = $mysqli->query($query_enlarge)or die($mysqli->error);
			
			// теперь в таблице RT_DOP_DATA нужно изменть parent_id всех рядов у которых parent_id больше или равен $place_id
			$query_enlarge = "UPDATE `".RT_DOP_DATA."` SET `row_id` = `row_id` + 1  WHERE `row_id` >= '".$place_id."' ORDER BY `row_id` DESC";
			//echo $query_enlarge."\r\n";
			$result_enlarge = $mysqli->query($query_enlarge)or die($mysqli->error);
			
			// если id ($pos_id) скопированного ряда который мы хотим вставить больше или равен id ($place_id) ряда в который мы вставляем 
			// - то увеличиваем его на 1 тоже, и меняем parent_id рядов в таблице RT_DOP_DATA
			if($mainCopiedRowId >= $place_id) $shift_counter++;
			return $shift_counter;
	      
		}
		static function insert_copied_rows_insert_part_TO_HOLE_TBL($place_id,$mainCopiedRowId,$query_num,$dop_data /* $place_id - куда вставляем, $pos_id - что будем вставлять */){
		    global $mysqli;
			
			// echo 'insert_copied_rows_insert_part'."\r\n";
			// копируем и вставляем ряд из таблицы RT_MAIN_ROWS
			$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$mainCopiedRowId."'";
			// echo $query."\r\n";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				$copied_row = $result->fetch_assoc();
				$copied_row['query_num']= $query_num;
				$copied_row['master_btn']= 0;
				$copied_row['date_create']= date('Y-m-d H:i:s');
				foreach($copied_row as $param => $val){
				    // если мы знаем id места ($place_id) куда мы хотим вставить ряды 
					// указываем его id, иначе указываем пустое значение чтобы добавить их в конец
				    if($place_id && $param == 'id') $val=$place_id;
					else if($param == 'id') $val='';
					
					$substrArr[] = $param."='".$val."'";
				}
				$query2="INSERT INTO `".RT_MAIN_ROWS."`
				       SET ".implode(",",$substrArr); 
			    // echo $query2."\r\n";
				$mysqli->query($query2)or die($mysqli->error);
				$row_id = $mysqli->insert_id;
				
				// вставляем ряды в таблицу RT_DOP_DATA и в таблицу RT_DOP_USLUGI
				foreach ($dop_data as $dop_id => $dop_value){
					//echo  $dop_key;
					// выбираем данные из таблицы RT_DOP_DATA
					$query3="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_id."'";
					$result3 = $mysqli->query($query3)or die($mysqli->error);
					if($result3->num_rows>0){
						// сохраняем полученный вывод в массив и производим корректировку данных:
						// меняем row_id обозначивающий внешний ключ на id вставленного в RT_MAIN_ROWS ряда и присваиваем id пустое значение 
						$copied_dop_row = $result3->fetch_assoc();
						$dop_row_id = $copied_dop_row['id'];
						$copied_dop_row['id']='';
						// id родительского ряда равно последнего вставленного ряда
						$copied_dop_row['row_id']= $row_id;
						
						$query4="INSERT INTO `".RT_DOP_DATA."` VALUES ('".implode("','",$copied_dop_row)."')"; 
						// echo $query4."\r\n";
						$mysqli->query($query4)or die($mysqli->error);
						$new_dop_row_id = $mysqli->insert_id;
						
						// выбираем данные из таблицы RT_DOP_USLUGI
						$query5="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
						$result5 = $mysqli->query($query5)or die($mysqli->error);
						if($result5->num_rows>0){
							while($copied_data = $result5->fetch_assoc()){
								// сохраняем полученный вывод в массив и производим корректировку данных:
								// меняем dop_row_id обозначивающий внешний ключ на id вставленного в RT_DOP_DATA ряда и
								$copied_data['id']='';
								$copied_data['dop_row_id']= $new_dop_row_id;
								$query6="INSERT INTO `".RT_DOP_USLUGI."` VALUES ('".implode("','",$copied_data)."')"; 
								//echo $query."\r\n";
								$mysqli->query($query6)or die($mysqli->error);
							}
						}
					}
				}	 
			}
			
			
	      
		}
		static function insert_copied_rows_insert_part($sort_id,$mainCopiedRowId,$query_num,$dop_data /* $sort_id - значение которое мы присвоим полю sort нового ряда, $mainCopiedRowId - что будем вставлять */){
		    global $mysqli;
			
			// echo 'insert_copied_rows_insert_part'."\r\n";
			// копируем и вставляем ряд из таблицы RT_MAIN_ROWS
			$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$mainCopiedRowId."'";
			//  echo $query."\r\n";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				$copied_row = $result->fetch_assoc();
				$copied_row['id']='';
				$copied_row['sort']= $sort_id;
				$copied_row['query_num']= $query_num;
				$copied_row['master_btn']= 0;
				$copied_row['date_create']= date('Y-m-d H:i:s');
				$query2="INSERT INTO `".RT_MAIN_ROWS."` VALUES ('".implode("','",$copied_row)."')";
			    //  echo $query2."\r\n";
				$mysqli->query($query2)or die($mysqli->error);
				$row_id = $mysqli->insert_id;
				
				// вставляем ряды в таблицу RT_DOP_DATA и в таблицу RT_DOP_USLUGI
				foreach ($dop_data as $dop_id => $dop_value){
					//echo  $dop_key;
					// выбираем данные из таблицы RT_DOP_DATA
					$query3="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_id."'";
					$result3 = $mysqli->query($query3)or die($mysqli->error);
					if($result3->num_rows>0){
						// сохраняем полученный вывод в массив и производим корректировку данных:
						// меняем row_id обозначивающий внешний ключ на id вставленного в RT_MAIN_ROWS ряда и присваиваем id пустое значение 
						$copied_dop_row = $result3->fetch_assoc();
						$dop_row_id = $copied_dop_row['id'];
						$copied_dop_row['id']='';
						// id родительского ряда равно последнего вставленного ряда
						$copied_dop_row['row_id']= $row_id;
						
						$query4="INSERT INTO `".RT_DOP_DATA."` VALUES ('".implode("','",$copied_dop_row)."')"; 
						// echo $query4."\r\n";
						$mysqli->query($query4)or die($mysqli->error);
						$new_dop_row_id = $mysqli->insert_id;
						
						// выбираем данные из таблицы RT_DOP_USLUGI
						$query5="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
						$result5 = $mysqli->query($query5)or die($mysqli->error);
						if($result5->num_rows>0){
							while($copied_data = $result5->fetch_assoc()){
								// сохраняем полученный вывод в массив и производим корректировку данных:
								// меняем dop_row_id обозначивающий внешний ключ на id вставленного в RT_DOP_DATA ряда и
								$copied_data['id']='';
								$copied_data['dop_row_id']= $new_dop_row_id;
								$query6="INSERT INTO `".RT_DOP_USLUGI."` VALUES ('".implode("','",$copied_data)."')"; 
								//echo $query."\r\n";
								$mysqli->query($query6)or die($mysqli->error);
							}
						}
					}
				}	 
			}
		}
		static function insert_copied_rows($query_num,$control_num,$place_id){
			
			if(empty($_SESSION['rt']['buffer']['copied_rows'])) return '[0,"нет сохраненной информации для вставки"]';
			
			if(($data = json_decode($_SESSION['rt']['buffer']['copied_rows']))==NULL) return '[0,"нет сохраненной информации для вставки"]';
			$data = (array)$data;
			// print_r($data); 
			// exit;
			$shift_counter = 0;
		    foreach ($data as $mainCopiedRowId => $dop_data) {
			    // если у нас есть место в которое мы хотим вставить скопированные ряды
			    if($place_id){
				    // echo 'mainCopiedRowId-'.$mainCopiedRowId."\r\n";
					// первым шагом "опускаем" ряды, которые находятся на том и ниже местах куда хотим вставить скопированные ряды
					// тоесть увеличиваем sort на 1 (sort+1) в рамках данной заявки там где id равно или больше $place_id
					// функция возвращает новый sort_id который был у ряда в который мы хотим вставить, чтобы затем присвоить его
					// новому ряду
					$sort_id = RT::change_rows_sort((int)$place_id,$query_num);
				}
				else{
				    // нам нужно получить максимальное значение поля sort для текущей заявки
					// затем мы присвоим новому вставляемому ряду это значение увеличенное на еденицу
				    $sort_id = RT::get_maximum_sort_id($query_num);
				}
				
				// теперь мы вставляем ряд в конец таблицы RT_MAIN_ROWS копируя его из RT_MAIN_ROWS по $mainCopiedRowId
				// затем вставляем в RT_DOP_DATA новые ряды скопированные из RT_DOP_DATA  
				// затем вставляем в RT_DOP_USLUGI новые ряды скопированные из RT_DOP_USLUGI  
				RT::insert_copied_rows_insert_part((int)$sort_id,(int)$mainCopiedRowId,$query_num,$dop_data);
			}
			return '[1]';
		}
		static function change_rows_sort($place_id,$query_num){
		    global $mysqli;
			
			// получаем значение sort существующее на данный момент у ряда на место которого мы хотим вставить новый ряд
			$query="SELECT sort FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$place_id."'";
			// echo $query."\r\n";
			$result = $mysqli->query($query)or die($mysqli->error);
			$row = $result->fetch_assoc();
			$sort_id = $row['sort'];
			// echo $sort;
			// увеличиваем sort на 1 (sort+1) в рамках данной заявки там где id равно или больше $place_id
			$query_enlarge = "UPDATE `".RT_MAIN_ROWS."` SET `sort` = `sort` + 1  WHERE `query_num` = '".$query_num."' AND `sort` >= '".$sort_id."'";
			// echo $query_enlarge."\r\n";
			$result_enlarge = $mysqli->query($query_enlarge)or die($mysqli->error);
			
			return $sort_id;
			
		}
		static function get_maximum_sort_id($query_num){
		    global $mysqli;
			
			// получаем значение sort существующее на данный момент у ряда на место которого мы хотим вставить новый ряд
			$query="SELECT MAX(sort) max_sort FROM `".RT_MAIN_ROWS."` WHERE `query_num` = '".$query_num."'";
			// echo $query."\r\n";
			$result = $mysqli->query($query)or die($mysqli->error);
			$row = $result->fetch_assoc();
			$sort_id = $row['max_sort'];
			
			return $sort_id++;
			
		}
		static function insert_copied_rows_TO_HOLE_TBL($query_num,$control_num,$place_id){
			
			if(empty($_SESSION['rt']['buffer']['copied_rows'])) return '[0,"нет сохраненной информации для вставки"]';
			
			if(($data = json_decode($_SESSION['rt']['buffer']['copied_rows']))==NULL) return '[0,"нет сохраненной информации для вставки"]';
			$data = (array)$data;
			// если вставляем в конкретное место разворачиваем массив, если нет оставляем в существующем виде
			if($place_id) $data = array_reverse($data);
			// print_r($data); 
			// exit;
			$shift_counter = 0;
		    foreach ($data as $mainCopiedRowId => $dop_data) {
			    // если у нас есть место в которое мы хотим вставить скопированные ряды
			    if($place_id){
				    // echo 'mainCopiedRowId-'.$mainCopiedRowId."\r\n";
					// первым шагом "опускаем" ряд куда хотим вставить и все ряды в таблице находящиеся ниже 1 (id+1)
					// тем самым освобождая место для вставки ряда в RT_MAIN_ROWS
					// затем меням parent_id рядов в таблице RT_DOP_DATA у которых parent_id больше или равен id ряда куда хотим вставить
					// если id ($mainCopiedRowId) скопированного ряда который мы хотим вставить больше или равен id ряда в который мы вставляем 
					// - то увеличиваем его на 1 тоже, 
					// функция возвращает новый $mainCopiedRowId который мог быть изменен
					$shift_counter = RT::shift_rows_down((int)$place_id,(int)$mainCopiedRowId,$shift_counter);
					$mainCopiedRowId +=$shift_counter;
				}
				
				// теперь мы вставляем ряд в таблицу RT_MAIN_ROWS копируя его из RT_MAIN_ROWS по $pos_id в $place_id
				// затем просто вставляем в RT_DOP_DATA новые ряды скопированные из RT_DOP_DATA по старым id 
				// потому что в RT_DOP_DATA они не менялись и устанавливаем им parent_id равный $pos_id
				RT::insert_copied_rows_insert_part((int)$place_id,(int)$mainCopiedRowId,$query_num,$dop_data);
			}
			return '[1]';
		}
		static function delete_rows($data){
			
			global $mysqli;
	
		    // print_r($data); 
		
		    foreach ($data as $deletedRowId) {
			    //удаляем ряд и все связанные с ним ряды в других таблицах
				
				// удаляем ряд в RT_MAIN_ROWS
			    $query="DELETE FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$deletedRowId."'";
			    $result = $mysqli->query($query)or die($mysqli->error);
				// echo $query."\r\n";
				// получаем id рядов из таблицы RT_DOP_DATA перед удалением потому что они являются родительскими для
				// рядов из таблицы RT_DOP_USLUGI в которой нам тоже надо будет удалить ряды
			    $query="SELECT id FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$deletedRowId."'";
				// echo $query."\r\n";
			    $result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
					while($row = $result->fetch_assoc()){
					   $dopRowIdsArr[] = $row['id'];
				    }
				}
				// print_r($dopRowIdsArr);
				
				// удаляем ряды из таблицы RT_DOP_DATA
				$query="DELETE FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$deletedRowId."'";
				// echo $query."\r\n";
			    $result = $mysqli->query($query)or die($mysqli->error);
				
				// удаляем ряды из таблицы RT_DOP_USLUGI 
				if(isset($dopRowIdsArr)){
					$query="DELETE FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` IN('".implode("','",$dopRowIdsArr)."')";
					// echo $query."\r\n";
					$result = $mysqli->query($query)or die($mysqli->error);
				}
			}
			return '[1]';
		}
		static function deletePrintsAndUslugi($data,$type){
			
			global $mysqli;
	
		    // print_r($data); 
		
		    foreach ($data as $deletedRowId) {
				// получаем id рядов из таблицы RT_DOP_DATA потому что они являются родительскими для
				// рядов из таблицы RT_DOP_USLUGI
			    $query="SELECT id FROM `".RT_DOP_DATA."` WHERE `row_id` = '".$deletedRowId."'";
				// echo $query."\r\n";
			    $result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
					while($row = $result->fetch_assoc()){
					   $dopRowIdsArr[] = $row['id'];
				    }
				}
				// print_r($dopRowIdsArr);
				// удаляем ряды из таблицы RT_DOP_USLUGI 
				if(isset($dopRowIdsArr)){
					$query="DELETE FROM `".RT_DOP_USLUGI."` 
					        WHERE `dop_row_id` IN('".implode("','",$dopRowIdsArr)."')";
							if($type == 'prints') $query.=" AND glob_type = 'print'";
							if($type == 'uslugi') $query.=" AND glob_type = 'extra'";
							if($type == 'printsAndUslugi') $query.=" AND (glob_type = 'print' OR glob_type = 'extra')";
					// echo $query."\r\n";
					$result = $mysqli->query($query)or die($mysqli->error);
				}
			}
			return '[1]';
		}
		
		static function insert_copied_rows_old($query_num,$control_num,$pos_id){
		    global $mysqli;   //print_r($data); 
         
            if(empty($_SESSION['rt']['buffer']['copied_rows'])) return "[0]";
			
			if(($data = json_decode($_SESSION['rt']['buffer']['copied_rows']))==NULL) return "[0]";
			
			print_r($data); 
			exit;
			
			// копируем выбранные ряды в таблицы
			foreach ($data as $key => $dop_data) {
			    //echo $key;
				// выбираем данные из таблицы RT_MAIN_ROWS
				$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$key."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
				    // сохраняем полученный вывод в массив и производим корректировку данных:
					// меняем номер запроса на текущий и присваиваем id пустое значение 
				    $copied_data = $result->fetch_assoc();
					$copied_data['query_num']=$query_num;
				    $copied_data['id']='';
				    $query="INSERT INTO `".RT_MAIN_ROWS."` VALUES ('".implode("','",$copied_data)."')"; 
				    //echo $query;
				    $mysqli->query($query)or die($mysqli->error);
				    $row_id = $mysqli->insert_id;
					
				    foreach ($dop_data as $dop_key => $dop_value){
					    //echo  $dop_key;
						// выбираем данные из таблицы RT_DOP_DATA
						$query="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_key."'";
				        $result = $mysqli->query($query)or die($mysqli->error);
						if($result->num_rows>0){
						    // сохраняем полученный вывод в массив и производим корректировку данных:
					        // меняем row_id обозначивающий внешний ключ на id вставленного в RT_MAIN_ROWS ряда и присваиваем id пустое значение 
				            $copied_data = $result->fetch_assoc();
							$dop_row_id = $copied_data['id'];
							$copied_data['id']='';
							$copied_data['row_id']= $row_id;
							$query="INSERT INTO `".RT_DOP_DATA."` VALUES ('".implode("','",$copied_data)."')"; 
							//echo $query;
							$mysqli->query($query)or die($mysqli->error);
							$new_dop_row_id = $mysqli->insert_id;
							
							// выбираем данные из таблицы RT_DOP_USLUGI
							$query="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
							$result = $mysqli->query($query)or die($mysqli->error);
							if($result->num_rows>0){
							    while($copied_data = $result->fetch_assoc()){
									// сохраняем полученный вывод в массив и производим корректировку данных:
									// меняем dop_row_id обозначивающий внешний ключ на id вставленного в RT_DOP_DATA ряда и
									$copied_data['id']='';
									$copied_data['dop_row_id']= $new_dop_row_id;
									$query="INSERT INTO `".RT_DOP_USLUGI."` VALUES ('".implode("','",$copied_data)."')"; 
									//echo $query;
									$mysqli->query($query)or die($mysqli->error);
								}
								
							}
							
						}
					}
				}
				
				
				
			}
			//$query="UPDATE `".RT_MAIN_ROWS."` SET  `master_btn` = '".$data_obj->status."'  WHERE `id` IN('".str_replace(";","','",$data_obj->ids)."')";
			//echo $query;
			//$result = $mysqli->query($query)or die($mysqli->error);
			
			return "[1,".$_SESSION['rt']['buffer']['copied_rows']."]";
			
		}
		static function set_masterBtn_status($data_obj){
		    global $mysqli;   //print_r($data); 
			$query="UPDATE `".RT_MAIN_ROWS."` SET  `master_btn` = '".$data_obj->status."'  WHERE `id` IN('".str_replace(";","','",$data_obj->ids)."')";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		
		static function add_data_from_basket($client,$manager_login){
			global $mysqli;
			//global $print_mode_names;
			$user_id = $_SESSION['access']['user_id'];
			
			
			// узнаем id клиента
			$query = "SELECT*FROM `".CLIENTS_TBL."` WHERE `company` = '".$client."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$client_data = $result->fetch_assoc();
			$client_id = $client_data['id'];
			//echo $client_id;
			
			// узнаем id менеджера
			$manager_login_arr = explode('&',$manager_login);
			foreach($manager_login_arr as $manager_login){
				$query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `nickname` = '".$manager_login."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows>0){
				    $manager_data = $result->fetch_assoc();
				    $manager_id_arr[] = $manager_data['id'];
				}
				else $manager_id_arr[] = 0;
			}
			//print_r($manager_id_arr);
			
			//
			$date = date('Y-m-d H:i:s');
			
			// содержимое корзины
			$basket_arr = $_SESSION['basket'];
			// print_r($basket_arr);
			// exit;
			
			
			
			// определяем номер запроса
			$query = "SELECT MAX(query_num) max FROM `".RT_LIST."`"; 								
			$result = $mysqli->query($query) or die($mysqli->error);
			$query_num_data = $result->fetch_assoc();
			$query_num = ($query_num_data['max']==0)? 10000:$query_num_data['max']+1;
			//echo $query_num;
			
			// вносим строку с данными заказа в RT_LIST
			$query = "INSERT INTO `".RT_LIST."` SET 
												`create_time` = NOW(),
												`client_id` = '$client_id',
												`manager_id` = '$manager_id_arr[0]',
												`query_num` = '".$query_num."'"; 
												
			$result = $mysqli->query($query) or die($mysqli->error);
			
			$sort_id = 0;
			foreach($basket_arr as $data){
				// выбираем из базы каталога данные об артикуле
				$query = "SELECT*FROM `".BASE_TBL."` WHERE id = '".$data['article']."'"; 								
				$result = $mysqli->query($query) or die($mysqli->error);
				$art_data = $result->fetch_assoc();
			
			
				// вносим основные данные о позиции в RT_MAIN_ROWS
				// ПРИМЕЧАНИЕ id артикула на сегодняшний день из корзины  поступает в виде $data['article']
				$query = "INSERT INTO `".RT_MAIN_ROWS."` SET 
				                                `sort` = '".++$sort_id."',
												`query_num` = '$query_num',
												`type` = 'cat',
												`art_id` = '".$data['article']."',
												`art` = '".$art_data['art']."',
												`name` = '".$art_data['name']."'"; 
												
				$result = $mysqli->query($query) or die($mysqli->error);
				$row_id = $mysqli->insert_id;
				
				// вносим основные данные о количестве RT_DOP_DATA
				$query = "INSERT INTO `".RT_DOP_DATA."` SET 
												`row_id` = '$row_id',
												`quantity` = '".$data['quantity']."',
												`price_out` = '".$data['price']."'"; 
				if(!empty($data['size_id']) && $data['size_id']!='undefined'){
				    $tirage_json[$data['size_id']] = array("dop"=>"0","tir"=>$data['quantity']);
				    $query .= ",`tirage_json` = '".json_encode($tirage_json)."'";	
					unset($tirage_json);
				}							
												
				$result = $mysqli->query($query) or die($mysqli->error);
				$dop_row_id = $mysqli->insert_id;
				
				if(isset($data['param1'])){ //если есть данные о нанесении
					// вносим основные данные о количестве RT_DOP_USLUGI
					// в корзине - $data['param2'](цена всего тиража) $data['param3'](цена штуки)
					$query = "INSERT INTO `".RT_DOP_USLUGI."` SET 
													`dop_row_id` = '$dop_row_id',
													`glob_type` = 'print',
													`type` = '".$data['param1']."',
													`quantity` = '".$data['param3']/$data['param2']."',
													`price_out` = '".$data['param2']."'"; 
													
					$result = $mysqli->query($query) or die($mysqli->error);
				}
	
			}
			$out_put = array(0  , $client_id);
			return json_encode($out_put);
			
		}
		static function calcualte_query_summ($query_num){
		    global $mysqli;   //print_r($data); 
		    $query = "SELECT dop_data_tbl.id AS dop_data_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.row_status AS row_status, dop_data_tbl.expel AS expel,
						  
						  dop_uslugi_tbl.id AS uslugi_id , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_out AS uslugi_t_price_out
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE main_tbl.query_num ='".$query_num."' ORDER BY main_tbl.id";
			 $result = $mysqli->query($query) or die($mysqli->error);
			 $arr = array();
			 while($row = $result->fetch_assoc()){
			     $arr[$row['dop_data_id']]['quantity'] = $row['dop_t_quantity'];
				 $arr[$row['dop_data_id']]['price_out'] = $row['dop_t_price_out'];
				 $arr[$row['dop_data_id']]['expel'] = $row['expel'];
				 if(!empty($row['uslugi_id'])){
				       $uslugi['glob_type'] = $row['uslugi_t_glob_type'];
				       $uslugi['quantity'] = $row['uslugi_t_quantity'];
					   $uslugi['price_out'] = $row['uslugi_t_price_out'];
				       $arr[$row['dop_data_id']]['uslugi'][] = $uslugi;
				 }
			 }
			 //echo '<pre>'; print_r($arr); echo '</pre>';
			 $summ = 0;
			 foreach($arr as $data){
			     if($data['expel']!='') $obj = json_decode($data['expel']);
				 if(isset($obj->main)&&$obj->main==1) continue;
				 $summ += $data['quantity']*$data['price_out'];
				 
				 if(!isset($data['uslugi'])) continue;
				 foreach($data['uslugi'] as $uslugi){
				     if(isset($obj->print) && $obj->print==1 && $uslugi['glob_type']=='print') continue;
					 if(isset($obj->dop) && $obj->dop==1 && $uslugi['glob_type']=='extra') continue;
					 $summ += $uslugi['quantity']*$uslugi['price_out'];
				 }
			 }
			 return  number_format($summ,'2','.','');
		}
		static function getArtRelatedPrintInfo($art_id){
			$out_put = array();
			// ищем типы нанесения присвоенные данному артикулу на прямую 
			// возврашаемое значение: массив содержащий один элемент обозначающий (имитирующий)
			// стандартное (дефолтное) место нанесения с вложенными в него типами нанесения 
			$out_put = self::get_related_art_and_print_types($out_put, $art_id);
			
			// получаем (если установленны) данные о конкретных местах нанесения для данного артикула
			// если были найдены места добавляем их в масив $out_put
			// и заполняем их данным о присвоенных местам типам нанесиния 
			$out_put = self::get_related_print_places($out_put,$art_id);
				
			return $out_put;	
		}
		static function get_related_art_and_print_types($out_put,$art_id){
		    global $mysqli;  
		
			//UPDATE `new__base__print_mode` SET `print_id`=13 WHERE `print` = 'шелкография'
			// получаем данные о типах нанесений соответсвующих данному артикулу на прямую
			$query="SELECT*FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
				    if($row['print_id']!=0){
				       $out_put[0][$row['print_id']] = '';		
					}
				}
			}
			return $out_put;
		}
		static function get_related_print_places($out_put,$art_id){
		    global $mysqli;  
			 
			// получаем данные о местах нанесений соответсвующих данному артикулу
			$query="SELECT  place_id FROM `".BASE__ART_PRINT_PLACES_REL_TBL."`
                                WHERE art_id = '".$art_id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);/**/
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){
				    // получаем данные о типах нанесений соответсвующих данному месту
					$query2="SELECT  print_id FROM `".BASE__CALCULATORS_PRINT_TYPES_SIZES_PLACES_REL_TBL."` 
							  WHERE place_id = '".$row['place_id']."' GROUP BY print_id";
					
					$result2 = $mysqli->query($query2)or die($mysqli->error);/**/
					if($result2->num_rows>0){
					    while($row2 = $result2->fetch_assoc()){
						    $out_put[$row['place_id']][$row2['print_id']] ='' ;
							//$out_put[2][] = 1;
						}	
					}			
				}
			}
			
			return $out_put; 
		}
		// создание заказа из запроса
        static function make_order($rows_data,$client_id,$query_num,$specification_num,$agreement_id){
            // подключаем класс для информации из калькулятора
        	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/print_calculators_class.php");


            global $mysqli;


            // echo $rows_data.' - '.$client_id.' - '.$query_num.' - '.$specification_num.' - '.$agreement_id;
            // = json_decode($rows_data,true);
            $user_id = $_SESSION['access']['user_id'];

            // убиваем пустые позиции
            foreach (json_decode($rows_data,true) as $key => $value) {
            	if(!empty($value)){
            		$positions_arr[] = $value;
            	}
            }
            // echo $rows_data;
            // echo '<pre>';
            // print_r($positions_arr);
            // echo '</pre>';
            	
            /////////////////////////////
            //  СОЗДАНИЕ ЗАКАЗА -- START
            /////////////////////////////       

                // определяем номер заказа
                $query = "SELECT MAX(order_num) max FROM `".CAB_ORDER_ROWS."`";                                 
                $result = $mysqli->query($query) or die($mysqli->error);
                $order_num_data = $result->fetch_assoc();
                $order_num = ($order_num_data['max']==0)? 00001:$order_num_data['max']+1;
                //echo $query_num;

                // КОПИРУЕМ СТРОКУ ЗАКАЗА из таблицы запросов
                $query = "INSERT INTO `".CAB_ORDER_ROWS."`  (`manager_id`, `client_id`, `snab_id`, `query_num` )
                    SELECT `manager_id`, `client_id`, `snab_id`, `query_num`
                    FROM `".RT_LIST."` 
                    WHERE  `query_num` = '".$query_num."';
                    ";
                // выполняем запрос
                $result = $mysqli->query($query) or die($mysqli->error);
                // получаем id нового заказа... он же номер
                $order_id = $mysqli->insert_id; 
                // пишем номер заказа в созданную строку
                $query = "UPDATE  `".CAB_ORDER_ROWS."` 
                            SET  `order_num` =  '".(int)$order_num."' 
                            WHERE  `id` ='".$order_id."';";
                // выполняем запрос
                $result = $mysqli->query($query) or die($mysqli->error);

                /*
                    на выходе имеем order_id - id строки заказа
                    и $order_num - номер заказа
                */
            /////////////////////////////
            //  СОЗДАНИЕ ЗАКАЗА -- START
            /////////////////////////////

            // echo '<br>'.$order_num.'<br>';
            // перебираем принятые данные по позициям

            $query1 = '';//запрос копирования услуг
            foreach ($positions_arr as $position) {
            	// if(empty($position)){echo 'пусто<br>';continue(2);}else{echo '<pre>';
            	// print_r($position);
            	// echo '</pre>';
            	// 	}
                // ЗАВОДИМ ПОЗИЦИИ К НОВОМУ ЗАКАЗУ

                //`dop_info_no_cat` не копируем т.к. это общая информация обовсех вариантах
                $query = "INSERT INTO `".CAB_ORDER_MAIN."`  (`master_btn`, `order_num`,`type`,`art`,`art_id`,`name`)
                    SELECT `master_btn`,`query_num`,`type`,`art`,`art_id`,`name`
                    FROM `".RT_MAIN_ROWS."` 
                    WHERE  `query_num` = '".$query_num."' 
                    AND `id` = '".$position['pos_id']."';
                ";

                // выполняем запрос
                $result = $mysqli->query($query) or die($mysqli->error);
                // id новой позиции
                $main_row_id = $mysqli->insert_id;

                // echo $query.'<br>';

                // выбираем id строки расчёта
                // КОПИРУЕМ СТРОКУ РАСЧЁТА (В ЗАКАЗЕ ОНА У НАС ДЛЯ КАЖДОГО ЗАКАЗА ТОЛЬКО 1)
                $query = "INSERT INTO `" . CAB_ORDER_DOP_DATA . "`  (
                    `row_id`,`expel`,`quantity`,`zapas`,`price_in`,`price_out`,`discount`,`tirage_json`,
                    `print_z`,`standart`,`shipping_time`,`shipping_date`,`no_cat_json`,`suppliers_name`,`suppliers_id`
                    )
                    SELECT `row_id`,`expel`,`quantity`,`zapas`,`price_in`,`price_out`,`discount`,`tirage_json`,
                    `print_z`,`standart`,`shipping_time`,`shipping_date`,`no_cat_json`,`suppliers_name`,`suppliers_id`
                    FROM `".RT_DOP_DATA."` 
                    WHERE  `id` = '".$position['row_id']."'
                ";
                $result = $mysqli->query($query) or die($mysqli->error);
                
                $dop_data_row_id = $mysqli->insert_id; // id нового расчёта... он же номер

                // echo $dop_data_row_id.'<br>';

                // echo $query.'<br>';

                // echo '$dop_data_row_id = '.$dop_data_row_id;


                // правим row_id на полученный из созданной строки позиции
                $query = "UPDATE  `".CAB_ORDER_DOP_DATA."` 
                        SET  `row_id` =  '".$main_row_id."' 
                        WHERE  `id` ='".$dop_data_row_id."';";
                $result = $mysqli->query($query) or die($mysqli->error);
                
                // правим order_num на новый номер заказа
                $query = "UPDATE  `".CAB_ORDER_MAIN."` 
                        SET  `order_num` =  '".$order_num ."' 
                        WHERE  `id` ='".$main_row_id."';";
                $result = $mysqli->query($query) or die($mysqli->error);


                //////////////////////////////////////////////////////
                //    КОПИРУЕМ ДОП УСЛУГИ И УСЛУГИ ПЕЧАТИ -- start  //
                //////////////////////////////////////////////////////
                    // думаю в данном случае копировать не стоит,
                    // лучше сначала выбрать , преобразовать в PHP и вставить
                    // в противном случае при одновременном обращении нескольких менеджеров к данному скрипту
                    // данные о доп услугах для заказа могут быть потеряны
                    /*
                     данный вопрос решается в любом случае двумя запросами:
                     Вар. 1) копируем данные, замораживаем таблицу доп услуг и апдейтим родительский id
                     Вар. 2) выгружаем данные о доп услугах в PHP, и записывае в новую таблицу
                    */

                    
                    $query = "SELECT * FROM `".RT_DOP_USLUGI."` 
                        WHERE  `dop_row_id` = '".$position['row_id']."'";

                        // echo $position['row_id'].'<br><br><br><br>';
                    $arr_dop_uslugi = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    
                    if($result->num_rows > 0){

                    	// echo $row.'<br><br><br>';
                        while($row = $result->fetch_assoc()){
                			 $query2 = "INSERT INTO `".CAB_DOP_USLUGI."` SET
						`dop_row_id` =  '".$dop_data_row_id."',
						`date_ready` = '0000-00-00',
						`date_send_out` = '0000-00-00',
						`uslugi_id` = '".$row['uslugi_id']."',
						`glob_type` = '".$row['glob_type']."',
						`type` = '".$row['type']."',
						`quantity` = '".$row['quantity']."',
						`price_in` = '".$row['price_in']."',
						`price_out` = '".$row['price_out']."',
						`for_how` = '".$row['for_how']."',
						`print_details_dop` = '".printCalculator::convert_print_details_to_dop_tech_info($row['print_details'])."',
						`tz` = '".$row['tz']."',				
						`performer` = '".$row['performer']."',
						`print_details` = '".$row['print_details']."';";
						// echo $query2.'<br><br>';exit;
						$mysqli->query($query2) or die($mysqli->error);	
                        }
                    }


                    

                //////////////////////////////////////////////////////
                //    КОПИРУЕМ ДОП УСЛУГИ И УСЛУГИ ПЕЧАТИ -- end  //
                //////////////////////////////////////////////////////
                /*      
                        пока не знаю нужно ли копировать id создателя услуги из запроса в заказ.
                        пока что не копируем
                        `author_id_added_services` = '".$row['creator_id']."',      
                */

                /*if($result->num_rows > 0){
                    while($row = $result->fetch_assoc()){
                        $arr_dop_uslugi[] = $row;
                    }
                
                    $query1 =''; 
                    foreach ($arr_dop_uslugi as $usluga) {
                        $query1 .= "INSERT INTO `".CAB_DOP_USLUGI."` SET
                        `dop_row_id` =  '".$dop_data_row_id."',
                        `uslugi_id` = '".$usluga['uslugi_id']."',
                        `glob_type` = '".$usluga['glob_type']."',
                        `type` = '".$usluga['type']."',
                        `quantity` = '".$usluga['quantity']."',
                        `price_in` = '".$usluga['price_in']."',
                        `price_out` = '".$usluga['price_out']."',
                        `for_how` = '".$usluga['for_how']."',
                        `tz` = '".$usluga['tz']."',
                        `print_details` = '".$usluga['print_details']."';";
                    }
                }*/

            }
/*
            if($query1!=''){// в случае наличия доп услуг
                    $result = $mysqli->multi_query($query1) or die($mysqli->error); 
                }
            return 1;*/
        }  
    }
?>