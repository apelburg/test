<?php
    // удалить insert_copied_rows_old

    class Agreement{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function fetch_agreement_content($agreement_id){
			global $mysqli;
			$query = "SELECT*FROM `".GENERATED_AGREEMENTS_TBL."` WHERE id='$agreement_id'";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows == 0){
				echo 'не удается получить содержимое договора';
				exit;
			} 
			else return $result->fetch_assoc();
		}
		static function add_new_agreement($client_id,$agreement_num,$type,$existent,$standart,$our_requisit_id,$client_requisit_id,$our_comp_full_name,$our_firm_acting_manegement_face,$client_comp_full_name,$client_firm_acting_manegement_face,$date,$expire_date,$short_description){
			global $mysqli;
			//echo print_r($our_firm_acting_manegement_face).'<br>';
			//echo print_r($client_firm_acting_manegement_face).'<br>';
			//exit;
	
			if($type == 'long_term')
			{
				$date_arr = explode('-',$date);
				$query = "SELECT id FROM `".GENERATED_AGREEMENTS_TBL."` 
						  WHERE client_id='$client_id' AND type='$type' AND 
						  our_requisit_id='$our_requisit_id' AND client_requisit_id='$client_requisit_id' AND LEFT(date,4) = '".$date_arr[0]."'";
						  
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows > 0)
				{   
					echo 'лимит на создание долгосрочных договоров: 1 договор в год,<br>
						  договор на '.$date_arr[0].' год  между компаниями:<br>'.fetch_client_requisites_nikename($client_requisit_id).' и '.fetch_our_requisites_nikename($our_requisit_id).' уже создан';///.mysql_result($result,0,'id');
					exit;
				}
			}
			
			if(!$agreement_num){
				$date_arr = explode('-',$date);
				$query = "SELECT MAX(agreement_num) agreement_num FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `standart` = '1' AND  `existent` = '0' AND  LEFT(date,4) = '".$date_arr[0]."'";
				$result = $mysqli->query($query)or die($mysqli->error);
				$row = $result->fetch_assoc();
				$agreement_num = $row['agreement_num'];
			
				$agreement_num_arr = explode('/',$agreement_num);
				if($agreement_num_arr[0] == 0) $agreement_num_arr[0] = 100;
				$agreement_num = ((int)$agreement_num_arr[0]+1).'/'.$date_arr[1].substr($date_arr[0],2);
				
				//echo $agreement_num;
				//exit;
			}
			
			$query = "INSERT INTO `".GENERATED_AGREEMENTS_TBL."` SET
					  date = '$date', 
					  expire_date = '$expire_date',
					  client_id='$client_id',
					  type='$type',
					  standart='$standart',
					  existent='$existent',
					  agreement_num='$agreement_num',
					  our_comp_full_name='$our_comp_full_name',
					  our_chief='".$our_firm_acting_manegement_face['name']."',
					  our_chief_in_padeg='".$our_firm_acting_manegement_face['name_in_padeg']."',
					  our_chief_position_in_padeg='".$our_firm_acting_manegement_face['position_in_padeg']."',
					  our_chief_position='".$our_firm_acting_manegement_face['position']."',
					  our_basic_doc='".$our_firm_acting_manegement_face['basic_doc']."',
					  client_comp_full_name='$client_comp_full_name',
					  client_chief_position='".$client_firm_acting_manegement_face['position']."',
					  client_chief_position_in_padeg='".$client_firm_acting_manegement_face['position_in_padeg']."',
					  client_chief='".$client_firm_acting_manegement_face['name']."',
					  client_chief_in_padeg='".$client_firm_acting_manegement_face['name_in_padeg']."',
					  client_basic_doc='".$client_firm_acting_manegement_face['basic_doc']."',
					  our_requisit_id='$our_requisit_id',
					  client_requisit_id='$client_requisit_id',
					  short_description='$short_description'
					  ";
			$result = $mysqli->query($query)or die($mysqli->error);
			$last_agreement_id =  $mysqli->insert_id;
			
			
			
			
			// этап создания отдельного файла Договра и сохраниения его на диск
			// проверяем существует ли папка данного клиента если нет создаем её
			// если происходит ошибка выводим отчет
			
			// проверяем есть папка данного клента, если её нет то создаем её
			$client_dir_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.strval(intval($_GET['client_id']));
			//chmod("data/com_offers/", 0775);
			
			if(!file_exists($client_dir_name)){
				if(!mkdir($client_dir_name, 0775)){
					echo 'ошибка создания папки клиента (4)'.$client_dir_name;
					exit;
				}
			}
			
			
			// папка обозначающая год (название папки - название года)
			$year_dir_name = $client_dir_name.'/'.$date_arr[0];
			if(!file_exists($year_dir_name)){
				if(!mkdir($year_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$year_dir_name;
					exit;
				}
			}
			
			// папка для типа договора
			$type_dir_name = $year_dir_name.'/'.$type;
			if(!file_exists($type_dir_name)){
				if(!mkdir($type_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$type_dir_name;
					exit;
				}
			}
			
			
			// папка для выбранных сторон
			$full_dir_name = $type_dir_name.'/'.$our_requisit_id.'_'.$client_requisit_id;
			if(!file_exists($full_dir_name)){
				if(!mkdir($full_dir_name, 0775)){
					echo 'ошибка создания папки с именем года'.$full_dir_name;
					exit;
				}
			}
			
			if((boolean)$existent) return $last_agreement_id;
			
			// записываем файл
			$file_name = $full_dir_name.'/agreement.tpl';
			//$file_name = $dir_name_full.'/com_pred_1_1.doc';
			if(file_exists($file_name)){
				echo 'файл с таким именем уже существует (2)';
				exit;
			}
			

			$origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/long_term.tpl';	
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
		
			return $last_agreement_id;
	
		}
		
		static function add_items_for_specification($dateDataObj,$specification_num,$rows_data,$client_id,$agreement_id,$agreement_date, $our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$date,$short_description,$address,$prepayment/**/){
		
			global $mysqli;
			
             
			 // print_r($dateDataObj);
			// exit;
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
			// exit;
			
			
			$shipping = '0000-00-00 00:00:00';
			foreach($rows_data_arr as $data_arr){
			
				if(count($data_arr)==0) continue;
				
			    $summ_out = 0;
                $uslugi_summ_out = 0;
				
				$main_id = $data_arr->pos_id;
				$dop_id = $data_arr->row_id;
				 
				$query="SELECT*FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$main_id."'";
				// echo $query."\r\n";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0){
				    // 1). main_data
				    $main_data = $result->fetch_assoc();
					
					$query2="SELECT*FROM `".RT_DOP_DATA."` WHERE `id` = '".$dop_id."'";
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
						 $name= (($main_data['art']!='')? 'арт.'.$main_data['art']:'')." ".$main_data['name'];					 
						   
						 // $price = ($dop_data['discount'] != 0 )? round((($summ_out/$dop_data['quantity'])/100)*(100 + $dop_data['discount']),2) :  round($summ_out/$dop_data['quantity'],2) ;
						 $price = ($dop_data['discount'] != 0 )? round(($dop_data['price_out']/100)*(100 + $dop_data['discount']),2) :  $dop_data['price_out'] ;
						 // прежде чем записать ряд в спецификацию сверим совпадает ли количество в расчете и в услугах
						 // для этого делаем дополнительный запрос к таблице RT_DOP_USLUGI, далее после добавления ряда 
						 // будет такойже запрос к таблице RT_DOP_USLUGI но уже чтобы добавить доп услуги в спецификацию
						 $query2_dop="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type";
						 // echo $query."\r\n";
						 $result2_dop = $mysqli->query($query2_dop)or die($mysqli->error);
						 if($result2_dop->num_rows>0){
						     while($uslugi_data = $result2_dop->fetch_assoc()){
							     if($uslugi_data['glob_type']=='print' && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									 $reload['flag'] = true;
									 //echo $dop_data['quantity'];
									 include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
									 $json_out =  rtCalculators::change_quantity_and_calculators($dop_data['quantity'],$dop_data['id'],'true','false');
									 $json_out_obj =  json_decode($json_out);
									 
									 // если расчет не может быть произведен по причине outOfLimit или needIndividCalculation
									 // сбрасываем количество тиража и нанесения до 1шт.
									 if(isset($json_out_obj->print->outOfLimit) || isset($json_out_obj->print->needIndividCalculation)){
										 rtCalculators::change_quantity_and_calculators(1,$dop_data['id'],'true','false');
										 
										 $query="UPDATE `".RT_DOP_DATA."` SET  `quantity` = '1'  WHERE `id` = '".$dop_data['id']."'";
										 $result = $mysqli->query($query)or die($mysqli->error);
									 }
									 
			
								 } /**/
								 if($uslugi_data['glob_type']=='extra' && ($uslugi_data['quantity']!=$dop_data['quantity'])){
									  $query="UPDATE `".RT_DOP_USLUGI."` SET  `quantity` = '".$dop_data['quantity']."'  WHERE `id` = '".$uslugi_data['id']."'";
									  $result = $mysqli->query($query)or die($mysqli->error);
									  $uslugi_data['quantity'] = $dop_data['quantity'];
									 
			
								 }
							 }
						 }
						 if(isset($reload['flag']) && $reload['flag'] == true){
							 header('Location:'.HOST.'/?'.$_SERVER['QUERY_STRING']);
							 exit;
						 }
						 
				         // записываем ряд
						 $specIdsArr[] =  Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$dop_data['quantity'],$price,$date,$dateDataObj);
						 
						 
						 $query3="SELECT*FROM `".RT_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_id."' ORDER BY glob_type DESC";
						 // echo $query."\r\n";
						 $result3 = $mysqli->query($query3)or die($mysqli->error);
						 if($result3->num_rows>0){
						     
						     while($uslugi_data = $result3->fetch_assoc()){
					            // 3). uslugi_data
								 if($uslugi_data['glob_type'] == 'print' && !(!!$expel["print"])){
									  include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/print_calculators_class.php");
								      $name = printCalculator::convert_print_details($uslugi_data['print_details']);
									 // записываем ряд
									 $specIdsArr[] =  Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$uslugi_data['quantity'],$uslugi_data['price_out'],$date,$dateDataObj);
								 }
								 if($uslugi_data['glob_type'] == 'extra' && !(!!$expel["dop"])){
									 $extra_usluga_details = self::get_usluga_details($uslugi_data['uslugi_id']);
									 $name = ($extra_usluga_details)? $extra_usluga_details['name']:'Неопределено'; 
									 
									 // меняем количество на 1(еденицу) если это надбавка на всю стоимость
									 $uslugi_data['quantity'] = ($uslugi_data['for_how']=='for_all')? 1: $uslugi_data['quantity'];
									 // записываем ряд
									 $specIdsArr[] =  Agreement::insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$uslugi_data['quantity'],$uslugi_data['price_out'],$date,$dateDataObj);
								 }/**/
								 
								  
						       
						 
						    }
					    }
				    }
				}
			}	
			
			// exit;	
		
			
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
			//echo $file_name;
			//$file_name = $dir_name_full.'/com_pred_1_1.doc';
			if(file_exists($file_name)){
				echo 'файл с таким именем уже существует (2)';
				exit;
			}
	
			if($dateDataObj->data_type=='days') $origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/specification.tpl';
			if($dateDataObj->data_type=='date') $origin_file_name = $_SERVER['DOCUMENT_ROOT'].'/os/modules/agreement/agreements_templates/specification_type2_by_date.tpl';
			
			$fd_origin = fopen($origin_file_name,'r');
			$file_content = fread($fd_origin,filesize($origin_file_name));
			fclose($fd_origin);
			
			$fd = fopen($file_name,'w');
			$write_result = fwrite($fd,$file_content); //\r\n
			fclose($fd);
			
			// создаем предзаказ 
			include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_class.php");
			//RT::make_order($rows_data,$client_id,$_GET['query_num'],$specification_num,$agreement_id);
			
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
		static function insert_row($client_id,$agreement_id,$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$specification_num,$short_description,$address,$prepayment,$name,$quantity,$price,$date,$dateDataObj){
			global $mysqli;
			
			// настройки в завасимости от типа спецификации
			if($dateDataObj->data_type=='days'){
			    $specification_type = 'days';
			    $shipping_date_time = '';
				$final_date_time = '';
				$item_production_term = $dateDataObj->datetime.'()';
			}
			if($dateDataObj->data_type=='date'){
			    $specification_type = 'date';
				
			    echo $dateDataObj->datetime;echo '<br>';
			    $shipping_date_time_arr = explode(' ',$dateDataObj->datetime);
				$shipping_date_time_arr[0] = implode('-',array_reverse(explode('.',$shipping_date_time_arr[0])));
				if(isset($shipping_date_time_arr[1])){
				     if(strlen($shipping_date_time_arr[1])==0 || strlen($shipping_date_time_arr[1])>8) $shipping_date_time_arr[1] = '00:00:00';
				     if(strlen($shipping_date_time_arr[1])==2) $shipping_date_time_arr[1] = $shipping_date_time_arr[1].':00:00';
				     if(strlen($shipping_date_time_arr[1])==5) $shipping_date_time_arr[1] = $shipping_date_time_arr[1].':00';
				}
				else $shipping_date_time_arr[1] = '00:00:00';
			    $shipping_date_time = implode(' ',$shipping_date_time_arr);
				echo $shipping_date_time;echo '<br>';
				echo $dateDataObj->final_date;echo '<br>';
				$final_date_time_arr = explode(' ',$dateDataObj->final_date);
				$final_date_time_arr[0] = implode('-',array_reverse(explode('.',$final_date_time_arr[0])));
				if(isset($shipping_date_time_arr[1])){
					if(strlen($final_date_time_arr[1])==0 || strlen($final_date_time_arr[1])>8) $final_date_time_arr[1] = '00:00:00';
					if(strlen($final_date_time_arr[1])==2) $final_date_time_arr[1] = $final_date_time_arr[1].':00:00';
					if(strlen($final_date_time_arr[1])==5) $final_date_time_arr[1] = $final_date_time_arr[1].':00';
				}
				else $final_date_time_arr[1] = '00:00:00';
				$final_date_time = implode(' ',$final_date_time_arr);
				echo $final_date_time;
				$item_production_term = '';
			}
			
			$query = "INSERT INTO `".GENERATED_SPECIFICATIONS_TBL."` SET 
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
						  specification_type='".$specification_type."',
						  short_description='".$short_description."',
						  address='".$address."',
						  prepayment='".$prepayment."',
						  date = '".$date."',
						  shipping_date_time='".$shipping_date_time."',
						  final_date_time='".$final_date_time."',
						  name='".$name."',
						  makets_delivery_term='5 (пяти)',
						  item_production_term='10 (десять)',
						  quantity='".$quantity."',
						  price='".$price."',
						  summ='".$quantity*$price."'
						  ";
						  
			  //echo $query;	
			  //exit;	  
			  $result = $mysqli->query($query)or die($mysqli->error);
			  return $mysqli->insert_id;
		
		}
		static function get_usluga_details($usluga_id){
	        global $mysqli;
			
			$query="SELECT name FROM `".OUR_USLUGI_LIST."` WHERE id = '".$usluga_id."'";
			$result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
			   $row=$result->fetch_assoc();
			   return $row;
			}
			return false;
	   }	
	   static function fetch_specifications($client_id,$agreement_id,$group_by = FALSE){
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
		static function getSpecificationsDates($inDataArr){
			global $mysqli;
			
			 //print_r($inDataArr);
			 $inDataArr = (array)$inDataArr;
			 
			 foreach($inDataArr['ids'] as $key => $data){
				//echo $data->row_id.' - <br>';
				$dataArr[$data->row_id] = $data->row_id;
			 }
			 
			 if(isset($dataArr)){
				 $query="SELECT id, row_id, shipping_date, shipping_time, standart, shipping_redactor_access FROM `".RT_DOP_DATA."` WHERE `id` IN('".implode("','",$dataArr)."')";
				 $result = $mysqli->query($query)or die($mysqli->error);
				 if($result->num_rows>0){
				 
					 // не понадобилось
					 // $day_num_count = 0;
					 // $max_day_num = '0';
					 // $defined_date = $expired_date = false;
					 
					 $max_date = '1970-01-01 00:00:01';
					 $cur_date = '2015-10-21 00:00:01';//date("Y-m-d H:i:s");    
					 while($row = $result->fetch_assoc()){
					     // не понадобилось
					     //if($row['standart']!='' && $row['standart']!='0') $day_num_count++;
						 $value = $shablon = $shablon_en = ''; 
						 $who = ($row['shipping_redactor_access']=='0' || $row['shipping_redactor_access']=='5')?'вы':'СНАБ';
						 // если установленна дата выбираем её, иначе количество рабочих дней
						 if($row['shipping_date']>'1970-01-01' || $row['standart']!=''){
						 	 if($row['shipping_date']>'1970-01-01'){
								 $value = $row['shipping_date']; 
								 $shablon = 'дата'; 
								 $shablon_en = 'date'; 
							 }
							 else if($row['standart']!=''){
								 $value = $row['standart']; 
								 $shablon = 'р/д'; 
								 $shablon_en = 'days'; 
							 }
							 $dataArr[$row['id']] = array('row_id'=> $row['row_id'],'value'=> $value,'shablon'=> $shablon,'shablon_en'=> $shablon_en, 'who'=> $who);
						 }
						 else unset($dataArr[$row['id']]);
						 // не понадобилось
						 // определяем максимальное значение установленных рабочих дней в $row['standart']
						 // if($row['standart']>$max_day_num) $max_day_num = $row['standart'];
						
						 // $some_date = $row['shipping_date'].' '.$row['shipping_time'];
						 // если определенна хотябы одна дата 
						 // if($some_date>'1970-01-01') $defined_date = true;
						 // определяем максимальное значение установленных дат в $row['shipping_date'] и $row['shipping_time']
						 // if($some_date>$max_date) $max_date = $some_date;
						 
					 }
					 $outDataArr['data'] = $dataArr;
					 $outDataArr['all_positions'] = $result->num_rows;
					 $outDataArr['defined_positions'] = count($dataArr);
					 // если не во всех расчетах установлен срок изготовления содаем флаг undefined_days_warn
					 // if(count($dataArr)>$day_num_count) $outDataArr['undefined_days_warn'] = 1;
					 // если определенна хотябы одна дата 
					/* if($defined_date){
					     $outDataArr['defined_date'] = 1;
						 
						 // если количество рабочих дней между текущей и максимальной датой меньше 
						 // максимального установленного срока в рабочих днях то устанавливаем флаг 
						 if($cur_date > $max_date) $outDataArr['expired_date'] = 1;
						 else{
						     // определяем количество дней между текущей и максимальной датой
						     $outDataArr['working_days_range'] = getWorkingDays($cur_date,$max_date);
						     if($max_day_num > $outDataArr['working_days_range']) $outDataArr['expired_date'] = 1;
						 }
					 }*/
					 // мексмальное установленное количество рабочих дней 
					 // $outDataArr['max_day_num'] = $max_day_num;
					 // мексмально установленная дата
					 // $outDataArr['max_date'] = substr($max_date,0,10);
					 // $outDataArr['cur_date'] = substr($cur_date,0,10);
					 
				 }
				 
			 }
			 // print_r($outDataArr);
			 return json_encode($outDataArr);
		}
    }

?>