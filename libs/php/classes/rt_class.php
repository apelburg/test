<?php
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
		static function expel_value_from_calculation($id,$val){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `expel` = '".$val."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function change_svetofor($id,$val){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `row_status` = '".$val."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
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
				$manager_data = $result->fetch_assoc();
				$manager_id_arr[] = $manager_data['id'];
			}
			//print_r($manager_id_arr);
			
			//
			$date = date('Y-m-d H:i:s');
			
			// содержимое корзины
			$basket_arr = $_SESSION['basket'];
			print_r($basket_arr);
			
			
			
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
			
			
			foreach($basket_arr as $data){
				// выбираем из базы каталога данные об артикуле
				$query = "SELECT*FROM `".BASE_TBL."` WHERE id = '".$data['article']."'"; 								
				$result = $mysqli->query($query) or die($mysqli->error);
				$art_data = $result->fetch_assoc();
			
			
				// вносим основные данные о позиции в RT_MAIN_ROWS
				$query = "INSERT INTO `".RT_MAIN_ROWS."` SET 
												`query_num` = '$query_num',
												`type` = 'cat',
												`art` = '".$art_data['art']."',
												`name` = '".$art_data['name']."'"; 
												
				$result = $mysqli->query($query) or die($mysqli->error);
				$row_id = $mysqli->insert_id;
				
				// вносим основные данные о количестве RT_DOP_DATA
				$query = "INSERT INTO `".RT_DOP_DATA."` SET 
												`row_id` = '$row_id',
												`quantity` = '".$data['quantity']."',
												`price_out` = '".$data['price']."'"; 
												
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
		static function make_order($json){
			// СОЗДАНИЕ ЗАКАЗА
			global $mysqli;

		    $data_obj = json_decode($json,true);
		    $user_id = $_SESSION['access']['user_id'];
		    $query_num = $data_obj['query_num'];
		    $client_id = $data_obj['client_id'];
		    // СОЗДАЁМ СТРОКУ ЗАКАЗА
		    $query = "INSERT INTO `".CAB_ORDER_ROWS."`  (`manager_id`, `client_id` )
				SELECT `manager_id`, `client_id`
				FROM `".RT_LIST."` 
				WHERE  `query_num` = '".$query_num."';
				";
			$result = $mysqli->query($query) or die($mysqli->error);
        	$order_id = $mysqli->insert_id; // id нового заказа... он же номер

			// перебираем отправленные данные по позициям
			foreach ($data_obj['ids'] as $key => $value) {
				// ЗАВОДИМ ПОЗИЦИИ К НОВОМУ ЗАКАЗУ
				$query = "INSERT INTO `".CAB_ORDER_MAIN."`  (`master_btn`, `order_num`,`type`,`art`,`art_id`,`name` )
					SELECT `master_btn`,`query_num`,`type`,`art`,`art_id`,`name`
					FROM `".RT_MAIN_ROWS."` 
					WHERE  `query_num` = '".$query_num."' 
					AND `id` = '".$key."';
				";

				$result = $mysqli->query($query) or die($mysqli->error);
        		$main_row_id = $mysqli->insert_id; // id нового заказа... он же новый номер
        		// echo $query;

        		// выбираем id строки расчёта
				$key_dop_data_arr = array_keys($value);
				$key_dop_data = $key_dop_data_arr[0]; // id строки расчёта

				// КОПИРУЕМ СТРОКУ РАСЧЁТА (В ЗАКАЗЕ ОНА У НАС ДЛЯ КАЖДОГО ЗАКАЗА ТОЛЬКО 1)
				$query = "INSERT INTO `" . CAB_ORDER_DOP_DATA . "`  (
					`row_id`,`expel`,`quantity`,`price_in`,`price_out`,`discount`,`tirage_json`,
					`print_z`,`standart`,`shipping_time`,`shipping_date`
					)
					SELECT `row_id`,`expel`,`quantity`,`price_in`,`price_out`,`discount`,`tirage_json`,
					`print_z`,`standart`,`shipping_time`,`shipping_date`
					FROM `".RT_DOP_DATA."` 
					WHERE  `id` = '".$key_dop_data."'
				";
				$result = $mysqli->query($query) or die($mysqli->error);
        		$dop_data_row_id = $mysqli->insert_id; // id нового расчёта... он же номер


				
				// правим row_id на полученный из созданной строки позиции
				$query = "UPDATE  `".CAB_ORDER_DOP_DATA."` 
						SET  `row_id` =  '".$main_row_id."' 
						WHERE  `id` ='".$dop_data_row_id."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				// правим order_num на полученный id при создании заказа
				$query = "UPDATE  `".CAB_ORDER_MAIN."` 
						SET  `order_num` =  '".$order_id."' 
						WHERE  `id` ='".$main_row_id."';";
				$result = $mysqli->query($query) or die($mysqli->error);

				// КОПИРУЕМ ДОП УСЛУГИ И УСЛУГИ ПЕЧАТИ
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
					WHERE  `id` = '".$key_dop_data."'
				";
				$arr_dop_uslugi = array();
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$arr_dop_uslugi[] = $row;
					}
				}
				
				$query1 = '';
				foreach ($arr_dop_uslugi as $k12 => $v12) {
					$query1 .= "INSERT INTO `".CAB_DOP_USLUGI."` SET
						`dop_row_id` =  '".$dop_data_row_id."', 
						`uslugi_id` = '".$v12['uslugi_id']."',
						`glob_type` = '".$v12['glob_type']."',
						`type` = '".$v12['type']."',
						`quantity` = '".$v12['quantity']."',
						`price_in` = '".$v12['price_in']."',
						`price_out` = '".$v12['price_out']."',
						`for_how` = '".$v12['for_how']."';";
				}	
				if($query1!=''){// в случае наличия доп услуг
					$result = $mysqli->multi_query($query1) or die($mysqli->error);	
				}
			}
			return 1;
		}  
    }

?>