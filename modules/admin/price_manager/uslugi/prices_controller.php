<?php 

    if(!empty($_POST['dataBufferForSavingToBase'])){
	    
	    $data = json_decode($_POST['dataBufferForSavingToBase']);
	    //echo '<pre>'; print_r($data);echo '</pre>';print_r($data);//
		//exit;
		
		$query ="DELETE FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE 
		                            `print_type_id` = '".$data->print_type_id."' AND
									`price_type` = '".$data->price_type."' AND
									`count` = '".$data->count."'";
		echo $query;echo '<br>';
		$mysqli->query($query)or die($mysqli->error);
		
		foreach($data->tbl_data as $val){
		    $query ="INSERT INTO `".BASE__CALCULATORS_PRICE_TABLES_TBL."` VALUES('',";
			$query.= "'".$data->print_type_id."','".$data->price_type."','".$data->count."'";
			
			
			for($i=0;$i<=21;$i++){
			   if(isset($val[$i])) $query.= ",'".$val[$i]."'";
			   else $query.= ",''";
		    }
            $query.= ")";
			
	        //echo $query;echo '<br>';
	        $result = $mysqli->query($query)or die($mysqli->error);
		}
		
		header('location:'.$_SERVER['HTTP_REFERER']);
		
	}

    $usluga_id = $_GET['usluga'];
	
	$td1  = '<td contenteditable="true">'; 
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
		     unset($row['id'],$row['print_type_id'],$row['count'],$row['price_type']);
			 
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
			 else array_push($row,'<span class="deleteElementBtn" onclick="deleteRowFromTable(this);">&#215;</span>');
			 //
		     $tbl_row[$price_type][$count][] = $tr1.$td1.implode($td_td,$row).$td2.$tr2;
			 $tbl_types[$price_type][$count]['cols_num'] = $end[$price_type];
			 $tbl_row_ = $end[$price_type];
			 //print_r($row);
		}
	}
	else{
	    echo 'no prices';
		return;
	}
	//echo '<pre>'; print_r($tbl_types);echo '</pre>';
	//echo '<pre>'; print_r($tbl_row);echo '</pre>';
	foreach($tbl_types as $type => $data){

	       $dop_row_data = array();
		   for($i=0;$i<=$tbl_types[$type][$count]['cols_num'];$i++){
			  if($i<2 || $i==$tbl_types[$type][$count]['cols_num']) $dop_row_data[$i]='<span></span>';
			  else $dop_row_data[$i]='<span onclick="deleteColFromTable(this);" class="deleteElementBtn">&#215;</span>';
		   }
		   // print_r($dop_row_data);
		   $dop_row = $tr1.$td1.implode($td_td,$dop_row_data).$td2.$tr2;
		   echo 'тип прайса: '.$type;
		   echo '<form method="POST">';
		   echo '<table id="tbl'.$type.$key.'">'.implode('',$tbl_row[$type][$data['count']]).$dop_row.'</table>';	
		   echo '<div>
				  <span onclick="addRowsToTbl(\''.$type.'\','.$key.');">добавить</span><input size="1" id="rowsNum'.$type.$key.'" value="1">рядов
				  &nbsp;&nbsp;
				  <span onclick="addColsToTbl(\''.$type.'\','.$key.');">добавить</span><input size="1" id="colsNum'.$type.$key.'" value="1">колонок
				 </div>';
		   echo '<input type="hidden" name="dataBufferForSavingToBase" id="tblDataBuffer'.$type.$key.'" value="">';
		   echo '<input type="button" onclick="priseManagerSendDataToBase(this.form,{\'type\':\'price\',\'bufferId\':\'tblDataBuffer'.$type.$key.'\',\'tblId\':\'tbl'.$type.$key.'\',\'price_type\':\''.$type.'\',\'print_type_id\':\''.$usluga_id.'\',\'count\':\''.$count.'\'});" value="сохранить">';
		   echo '</form>';
		   echo '<br><br><br>';
	}
	

?>