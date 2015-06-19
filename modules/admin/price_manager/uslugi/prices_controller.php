<?php 

    $usluga_id = $_GET['usluga'];
	
	$td1  = '<td>'; 
	$td2  = '</td>';
	$td_td  = $td2.$td1;
	$tr1  = '<tr>'; 
	$tr2  = '</tr>';
	$tr_tr  = $tr2.$tr1;
	$tbl_types = array();
	// выбираем данные из таблицы содержащей прайсы
	$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$usluga_id."' ORDER by id, param_val";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	$end = false;
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     $price_type=$row['price_type'];
		     unset($row['id'],$row['print_type_id'],$row['count'],$row['price_type']);
			 // создаем массив содержащий вариации существующих в таблице прайсов
		     if(!in_array($price_type,$tbl_types)) $tbl_types[] = $price_type;
			 
			 
			 
			 //
			 if($row['param_val']==0){
			      array_walk($row, function(&$val, $key){ if($key!='param_type')$val = (int)$val;});
				  
				 
				  $counter = 0;
				  foreach($row as $key => $val){
					  if($key!='param_val' && $key!='param_type'){
					      //echo $val;
						  if($val==0 && !$end) $end = $counter;
					  } 
					  $counter++;
				  }
			 }
			 //echo $end;
			 if($end){
			     $counter = 0;
				 foreach($row as $key => $val){
				      
					  if($counter>=$end){
						  unset($row[$key]);
					  } 
					  $counter++;
				  }
			  }	  
			 
			 //
		     $tbl_row[$price_type][] = $tr1.$td1.implode($td_td,$row).$td2.$tr2;
		}
	}
	else{
	    echo 'no prices';
		return;
	}
	
	foreach($tbl_types as $key => $type){
	   echo 'тип прайса: '.$type;
	   echo '<table id="tbl'.$type.$key.'">'.implode('',$tbl_row[$type]).'</table>';	
	   echo '<div>
	          <span onclick="addRowsToTbl(\''.$type.'\','.$key.');">добавить</span><input size="1" id="rowsNum'.$type.$key.'" value="1">рядов
			  &nbsp;&nbsp;
			  <span onclick="addColsToTbl(\''.$type.'\','.$key.');">добавить</span><input size="1" id="colsNum'.$type.$key.'" value="1">колонок
	         </div>';
	   echo '<br><br><br>';
	}
	

?>