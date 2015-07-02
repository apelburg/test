<?php 

    if(!empty($_POST['dataBufferForSavingToBase'])){
	    
	    $data = json_decode($_POST['dataBufferForSavingToBase']);
		
        // удаляем последний ряд содержащий кнопки удаления колонок
		unset($data->tbl_data[count($data->tbl_data)-1]);
	    echo '<pre>'; print_r($data);echo '</pre>';
		
		
		
		if(!empty($_POST['dataBufferForDeleting'])){
		    $toDeleteArr = explode('|',trim($_POST['dataBufferForDeleting'],'|'));
		    //echo '<pre>'; print_r($toDeleteArr);echo '</pre>';////
			if(count($toDeleteArr)>0){
			    for($i=0;$i<count($toDeleteArr);$i++){
				     //echo '-'.(int)$toDeleteArr[$i].'-<br>';
					 if((int)$toDeleteArr[$i]!=0){
					      $query ="DELETE FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE id = '".$toDeleteArr[$i]."'";
						  $mysqli->query($query)or die($mysqli->error);
					 }
				}
				
			}
		}
		//exit; //exit;
		foreach($data->tbl_data as $val){
		    $query ="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE id = '".$val[0]."'";
			
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
		       $query2 ="UPDATE `".BASE__CALCULATORS_PRICE_TABLES_TBL."` SET `print_type_id` = '".$data->print_type_id."', `price_type` = '".$data->price_type."' , `count` = '".$data->count."', `param_val`='".$val[1]."', `param_type`='".$val[2]."'";
			   
			   for($i=1,$j=3;$i<=20;$i++,$j++){
				   if(isset($val[$j])) $query2.= ", `".$i."`='".$val[$j]."'";
				   else $query2.= ", `".$i."`='0'";
				}
			   
			   $query2 .=" WHERE id = '".$val[0]."'";
			   //echo $query2.'<br>';
			   $mysqli->query($query2)or die($mysqli->error);
			}
			else{
			  
			    $query2 ="INSERT INTO `".BASE__CALCULATORS_PRICE_TABLES_TBL."` VALUES('',";
				$query2.= "'".$data->print_type_id."','".$data->price_type."','".$data->count."'";
				
				
				for($i=1;$i<=22;$i++){
				   if(isset($val[$i])) $query2.= ",'".$val[$i]."'";
				   else $query2.= ",''";
				}
				$query2.= ")";
			    //echo $query2;
			    $mysqli->query($query2)or die($mysqli->error);//
			}
		}
		header('location:'.$_SERVER['HTTP_REFERER']);
		exit;
	}

    $usluga_id = $_GET['usluga'];
	
	$td1  = '<td contenteditable="true">'; 
	$td1_hidden  = '<td style="display:none;">'; 
	$td2  = '</td>';
	$td_td  = $td2.$td1;
	$tr1  = '<tr>'; 
	$tr2  = '</tr>';
	$tr_tr  = $tr2.$tr1;
	$tbl_types = array();
	
	// выбираем данные из таблицы содержащей прайсы
	
	$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$usluga_id."' ORDER by price_type, id, param_val";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     
		     $price_type=$row['price_type'];
			 $count=$row['count'];
		     unset($row['print_type_id'],$row['count'],$row['price_type']);
			 
			 if(!isset($end[$price_type]))$end[$price_type] = false;
			 // создаем массив содержащий вариации существующих в таблице прайсов
		     if(!in_array($price_type,$tbl_types)) $tbl_types[$price_type]['count'] = $count;
			 
			 
			 
			 //
			 if($row['param_val']==0){
			      array_walk($row, function(&$val, $key){ if($key!='param_type')$val = (int)$val;});
				  
				 
				  $counter = 0;
				  foreach($row as $key => $val){
					  if($key!='param_val' && $key!='param_type'){
					      //echo $val;
						  if($val==0 && !$end[$price_type]) $end[$price_type] = $counter;
					  } 
					  $counter++;
				  }
			 }
			 //echo $end;
			 if($end[$price_type]){
			     $counter = 0;
				 foreach($row as $key => $val){
				      
					  if($counter>=$end[$price_type]){
						  unset($row[$key]);
					  } 
					  $counter++;
				  }
			  }	  /**/
			 if($row['param_val']==0) array_push($row,'');
			 else array_push($row,'<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$price_type.$count.'\');">&#215;</span>');
			 //
		     $tbl_row[$price_type][$count][] = $tr1.$td1_hidden.implode($td_td,$row).$td2.$tr2;
			 $tbl_types[$price_type][$count]['cols_num'] = $end[$price_type];
			 //print_r($row);
		}
	}
	
	function make_table_tpl($type){
	    global $td1;
		global $td1_hidden;
		global $td2;
		global $td_td;
		global $tr1;
		global $tr2;
		global $tr_tr;
	
	    $row = array( '' , 0,  'шт.' , 100,  '200', '');
	    $tbl_row[0][] = $tr1.$td1_hidden.implode($td_td,$row).$td2.$tr2;
		
		$row = array( '' ,'param_val' => 1, 'param_type' => 'цвет' , '1' => '1.00', '2' => '2.00', '3' => '<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$type.'0\');">&#215;</span>');
	    $tbl_row[0][] = $tr1.$td1_hidden.implode($td_td,$row).$td2.$tr2;
		return $tbl_row;
	}
	
	if(!isset($tbl_row)){
	    
	 
		$tbl_types = array(
		                   'in'=>array('count'=>0,array('cols_num'=>5)),
						   'out'=>array('count'=>0,array('cols_num'=>5))
						   );
						   
         $tbl_row['in'] = make_table_tpl('in');
		 $tbl_row['out'] = make_table_tpl('out');
	}
	if(isset($tbl_row) && count($tbl_types)==1){
	 
		 $type = (isset($tbl_types['out']))? 'in':'out';
		
		 $tbl_types[$type]= array('count'=>0,array('cols_num'=>5));	
		 ksort($tbl_types);			   
         $tbl_row[$type] = make_table_tpl($type);
	}

	//echo '<pre>'; print_r($tbl_types);echo '</pre>';
	//echo '<pre>'; print_r($tbl_row);echo '</pre>';
	foreach($tbl_types as $type => $data){
           $count = 0;
	       $dop_row_data = array();
		   for($i=0;$i<=$tbl_types[$type][$count]['cols_num'];$i++){
			  if($i<3 || $i==$tbl_types[$type][$count]['cols_num']) $dop_row_data[$i]='<span></span>';
			  else $dop_row_data[$i]='<span onclick="deleteColFromTable(this);" class="deleteElementBtn">&#215;</span>';
		   }
		   // print_r($dop_row_data);
		   $dop_row = $tr1.$td1_hidden.implode($td_td,$dop_row_data).$td2.$tr2;
		   echo 'тип прайса: '.$type;
		   echo '<div>
				  <span class="pointer" onclick="addRowsToTbl(\''.$type.$count.'\',{\'preLast\':true,\'clearCell\':1});">добавить</span><input size="1" id="rowsNum'.$type.$count.'" value="1">рядов
				  &nbsp;&nbsp;
				  <span class="pointer" onclick="addColsToTbl(\''.$type.'\','.$count.');">добавить</span><input size="1" id="colsNum'.$type.$count.'" value="1">колонок
				 </div>';
		   echo '<form method="POST">';
		   echo '<table id="tbl'.$type.$count.'">'.implode('',$tbl_row[$type][$data['count']]).$dop_row.'</table>';
		   	
		   echo '<input type="hidden" name="dataBufferForDeleting" id="dataBufferForDeleting'.$type.$count.'" value="">';
		   echo '<input type="hidden" name="dataBufferForSavingToBase" id="tblDataBuffer'.$type.$count.'" value="">';
		   echo '<input type="button"  class="pointer" onclick="priseManagerSendDataToBase(this.form,{\'type\':\'price\',\'bufferId\':\'tblDataBuffer'.$type.$count.'\',\'tblId\':\'tbl'.$type.$count.'\',\'price_type\':\''.$type.'\',\'print_type_id\':\''.$usluga_id.'\',\'count\':\''.$count.'\'});" value="сохранить">';
		   echo '</form>';
		   echo '<br><br><br>';
	}
	

?>