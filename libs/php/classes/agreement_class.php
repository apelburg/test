<?php
    // удалить insert_copied_rows_old

    class Agreement{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function add_items_for_specification($specification_num,$rows_data,$client_id,$agreement_id,$agreement_date, $our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$date,$short_description,$address,$prepayment/**/){
		
			global $mysqli;
			

			if(!$specification_num){
				$query = "SELECT MAX(specification_num) specification_num FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows > 0){
				    $row = $result->fetch_assoc();
					$specification_num = $row['specification_num'] + 1 ;
				}
				else $specification_num = 1 ;
			}
			$date_arr = explode('.',$date);
			$date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
			
			
			$rows_data_arr = json_decode($rows_data);
			// echo $specification_num.'<pre>'; print_r($rows_data_arr); echo '</pre>';//
			
			foreach($rows_data_arr as $main_id => $dop_id){
			    $summ_out = 0;
                $uslugi_summ_out = 0;
				 
				$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$main_id."'";
				// echo $query."\r\n";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
				    // 1). main_data
				    $main_data = $result->fetch_assoc();
					
					$query2="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_id[0]."'";
					// echo $query."\r\n";
					$result2 = $mysqli->query($query2)or die($mysqli->error);
					if($result2->num_rows>0){
					     // 2). dop_data
					     $dop_data = $result2->fetch_assoc();
						 $expel = array ("main"=>0,"print"=>0,"dop"=>0);
						 if(@$dop_data['expel']!=''){
							 $obj = @json_decode($dop_data['expel']);
							 foreach($obj as $expel_key => $expel_val) $expel[$expel_key] = $expel_val;
						 }
					
						 $summ_out = $dop_data['quantity']*$dop_data['price_out'];
						 
						 $query3="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id[0]."'";
						 // echo $query."\r\n";
						 $result3 = $mysqli->query($query3)or die($mysqli->error);
						 if($result3->num_rows>0){
						     
						     while($uslugi_data = $result3->fetch_assoc()){
					            // 3). uslugi_data
								 if($uslugi_data['glob_type'] == 'print' && !(!!$expel["print"])){
								    $uslugi_summ_out += $uslugi_data['quantity']*$uslugi_data['price_out'];
								 }
								 if($uslugi_data['glob_type'] == 'extra' && !(!!$expel["dop"])){
								    $uslugi_summ_out += $uslugi_data['quantity']*$uslugi_data['price_out'];
								 }
							 }
						 }
				    }
				}

				// echo '<pre>'; print_r($expel); echo '</pre>';
				// здесь как-то в учу надо собирать данные о нанесениях и о доп услугах
				// 1. брать суммму всех нанесений и допуслуг
				// складывать вместе с суммой стоимости ариткула и делить на количество артикулов
				// 2. в каком-то формате записывать данные о нанесениях и допуслугах
				
				// echo "<br>".(($summ_out+$uslugi_summ_out)/$dop_data['quantity'])*$dop_data['quantity']." -> $summ_out +$uslugi_summ_out / ".$dop_data['quantity']."<br>";
				$price = ($dop_data['discount'] != 0 )? round(((($summ_out+$uslugi_summ_out)/$dop_data['quantity'])/100)*(100 + $dop_data['discount']),2) :  round(($summ_out+$uslugi_summ_out)/$dop_data['quantity'],2) ;
						
				$query4 = "INSERT INTO `".GENERATED_SPECIFICATIONS_TBL."` SET 
							  client_id='".$client_id."',
							  agreement_id='".$agreement_id."',
							  our_chief='".$our_firm_acting_manegement_face['name']."',
							  our_chief_in_padeg='".$our_firm_acting_manegement_face['name_in_padeg']."',
							  our_chief_position='".$our_firm_acting_manegement_face['position']."',
							  our_chief_position_in_padeg='".$our_firm_acting_manegement_face['position_in_padeg']."',
							  our_basic_doc='".$our_firm_acting_manegement_face['basic_doc']."',
							  client_chief='".$client_firm_acting_manegement_face['name']."',
							  client_chief_in_padeg='".$client_firm_acting_manegement_face['name_in_padeg']."',
							  client_chief_position='".$client_firm_acting_manegement_face['position']."',
							  client_chief_position_in_padeg='".$client_firm_acting_manegement_face['position_in_padeg']."',
							  client_basic_doc='".$client_firm_acting_manegement_face['basic_doc']."',
							  specification_num='".$specification_num."',
							  short_description='".$short_description."',
							  address='".$address."',
							  prepayment='".$prepayment."',
							  date = '$date',
							  name='".(($main_data['art']!='')? 'арт.'.$main_data['art']:'')." ".$main_data['name']."',
							  makets_delivery_term='5 (пяти)',
							  item_production_term='10 (десять)',
							  quantity='".$dop_data['quantity']."',
							  price='".$price."',
							  summ='".$dop_data['quantity']*$price."'
							  ";
							  
				// echo $query4;		  
				  $result4 = $mysqli->query($query4)or die($mysqli->error);
			}	
				
		//exit;
			
			// этап создания отдельного файла Спецификации и сохраниения его на диск
			// проверяем существует ли папка данного клиента если нет создаем её
			// если происходит ошибка выводим отчет
			
			// проверяем есть папка данного клента, если её нет то создаем её
			$client_dir_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.strval($client_id);
			//chmod("data/com_offers/", 0775);
			
			if(!file_exists($client_dir_name)){
				if(!mkdir($client_dir_name, 0775)){
					echo 'ошибка создания папки клиента (4)'.$client_dir_name;
					exit;
				}
			}
			
			// папка обозначающая год (название папки - название года)
			
			$agreement_date = explode('-',$agreement_date);
			$year_dir_name = $client_dir_name.'/'.$agreement_date[0];
			if(!file_exists($year_dir_name)){
				if(!mkdir($year_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$year_dir_name;
					exit;
				}
			}
			
			// папка для типа договора
			$type_dir_name = $year_dir_name.'/long_term';
			if(!file_exists($type_dir_name)){
				if(!mkdir($type_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$type_dir_name;
					exit;
				}
			}
			
			$our_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'our_requisit_id','coll'=>'id','val'=>$agreement_id));
			$client_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'client_requisit_id','coll'=>'id','val'=>$agreement_id));
			
			// папка для выбранных сторон
			$full_dir_name = $type_dir_name.'/'.$our_requisit_id.'_'.$client_requisit_id;
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$full_dir_name;
					exit;
				}
			}
			
			// папка для выбранных спецификаций
			$full_dir_name = $full_dir_name.'/specifications';
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$full_dir_name;
					exit;
				}
			}
			
			// записываем файл
			$file_name = $full_dir_name.'/'.$specification_num.'.tpl';
			//$file_name = $dir_name_full.'/com_pred_1_1.doc';
			if(file_exists($file_name)){
				echo 'файл с таким именем уже существует (2)';
				exit;
			}
	
			$origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/specification.tpl';
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
			
			return $specification_num;

		} 
		static function fetch_specification($client_id,$agreement_id,$specification_num){
		//function fetch_specification
		
			global $mysqli;
			
			$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."' AND specification_num = '".$specification_num."' ORDER BY id";
			$result = $mysqli->query($query)or die($mysqli->error);
			
	
			if($result->num_rows > 0) return $result;
			else return false;
	
	    }
		function fetch_specifications($client_id,$agreement_id,$group_by = FALSE){
			global $mysqli;
			
			$query = "SELECT * FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE agreement_id = '".$agreement_id."' AND client_id = '".$client_id."'";
			
			if($group_by) $query .= "GROUP BY ".$group_by ;
			else $query .= "ORDER BY id ";
			
			$result = $mysqli->query($query)or die($mysqli->error);
			
			if($result->num_rows > 0){
				 return $result;
			}
			else return false;
		
		}
    }

?>