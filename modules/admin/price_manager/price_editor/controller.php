<?php 
     $usluga_id = $_GET['usluga'];
	 
     if(isset($_POST['save_comment'])){
	     // echo $_POST['comment'];
		
		 $query ="SELECT id FROM `".BASE__PRICE_COMMENTS_TBL."` WHERE `print_id` = '".$usluga_id."'";
		 $result = $mysqli->query($query)or die($mysqli->error);
		 if($result->num_rows>0){
		      $query ="UPDATE `".BASE__PRICE_COMMENTS_TBL."` SET `comment` = '".cor_data_for_SQL($_POST['comment'])."' WHERE  `print_id` = '".$usluga_id."'";
			  $mysqli->query($query)or die($mysqli->error);
		 }
		 else{
		      $query ="INSERT `".BASE__PRICE_COMMENTS_TBL."` SET `comment` = '".cor_data_for_SQL($_POST['comment'])."', `print_id` = '".$usluga_id."'";
			  $mysqli->query($query)or die($mysqli->error);
		 }		   
		 // echo $query.'<br>';
		 header('location:'.$_SERVER['HTTP_REFERER']);
		 exit;
	 }

    if(!empty($_POST['dataBufferForSavingToBase'])){
	    
	    $data = json_decode($_POST['dataBufferForSavingToBase']);
		
        // удаляем последний ряд содержащий кнопки удаления колонок
		unset($data->tbl_data[count($data->tbl_data)-1]);
	    echo '<pre>'; print_r($data);echo '</pre>';
		
		//exit;
		
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
			if($result->num_rows>0/*false*/){
		       $query2 ="UPDATE `".BASE__CALCULATORS_PRICE_TABLES_TBL."` SET `print_type_id` = '".$data->print_type_id."', `price_type` = '".$data->price_type."' , `level` = '".$data->level."' , `count` = '".$data->count."', `param_val`='".(int)$val[1]."', `param_type`='".cor_data_for_SQL($val[2])."'";
			   
			   for($i=1,$j=3;$i<=20;$i++,$j++){
				   if(isset($val[$j])) $query2.= ", `".$i."`='".(float)$val[$j]."'";
				   else $query2.= ", `".$i."`='0'";
				}
			   
			   $query2 .=" WHERE id = '".$val[0]."'";
			    //echo $query2.'<br>';//
			   $mysqli->query($query2)or die($mysqli->error);
			}
			else{
			  
			    $query2 ="INSERT INTO `".BASE__CALCULATORS_PRICE_TABLES_TBL."` VALUES('',";
				$query2.= "'".$data->print_type_id."','".$data->price_type."','".$data->level."','".$data->count."'";
				
				
				for($i=1;$i<=22;$i++){
				   if(isset($val[$i])){
					   if($i==1) $query2.= ",'".(int)$val[$i]."'";
					   if($i==2) $query2.= ",'".cor_data_for_SQL($val[$i])."'";
				       if($i>=3) $query2.= ",'".(float)$val[$i]."'";
				   }
				   
				   else $query2.= ",''";
				}
				$query2.= ")";
			    // echo $query2;
			    $mysqli->query($query2)or die($mysqli->error);//
			}
		}

		header('location:'.$_SERVER['HTTP_REFERER']);
		exit;
	}
	
	$td1  = '<td contenteditable="true">'; 
	$td1_hidden  = '<td style="display:none;">'; 
	$td2  = '</td>';
	$td_td  = $td2.$td1;
	$tr1  = '<tr>'; 
	$tr2  = '</tr>';
	$tr_tr  = $tr2.$tr1;
	$tbl_types = array();
	
	// выбираем данные из таблицы содержащей прайсы
	
	$query="SELECT*FROM `".BASE__CALCULATORS_PRICE_TABLES_TBL."` WHERE `print_type_id` = '".$usluga_id."' ORDER BY level, price_type, id, param_val";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     
			 //echo '<pre>'; print_r($row);echo '</pre>';
			 
		     $price_type=$row['price_type'];
			 $count=$row['count'];
			 $level=$row['level'];
		     unset($row['level'],$row['print_type_id'],$row['count'],$row['price_type']);
			 
			 if(!isset($end[$level][$price_type])) $end = array($level=>array($price_type=>false));//[$price_type] = false;
			 // создаем массив содержащий вариации существующих в таблице прайсов
			 if(!isset($tbl_types[$level]))$tbl_types[$level] = array();
		     if(!in_array($price_type,$tbl_types[$level])) $tbl_types[$level][$price_type]['count'] = $count;
			 
			 
			 
			 //
			 if($row['param_val']==0){
			      array_walk($row, function(&$val, $key){ if($key!='param_type')$val = (int)$val;});
				  
				 
				  $counter = 0;
				  foreach($row as $key => $val){
					  if($key!='param_val' && $key!='param_type'){
					      //echo $val;
						  if($val==0 && !$end[$level][$price_type]) $end = array($level=>array($price_type=>$counter));
					  } 
					  $counter++;
				  }
			 }
			 //echo $end;
			 if($end[$level][$price_type]){
			     $counter = 0;
				 foreach($row as $key => $val){
				      
					  if($counter>=$end[$level][$price_type]){
						  unset($row[$key]);
					  } 
					  $counter++;
				  }
			  }	  /**/
			 if($row['param_val']==0) array_push($row,'');
			 else array_push($row,'<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$level.$price_type.$count.'\');">&#215;</span>');
			 //
		     $tbl_row[$level][$price_type][$count][] = $tr1.$td1_hidden.implode($td_td,$row).$td2.$tr2;
			 $tbl_types[$level][$price_type][$count]['cols_num'] = $end[$level][$price_type];
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
	// echo '<pre>'; print_r($tbl_types);echo '</pre>';
	if(!isset($tbl_row)){
	    
	 
		$tbl_types = array(
		                   'in'=>array('count'=>0,array('cols_num'=>5)),
						   'out'=>array('count'=>0,array('cols_num'=>5))
						   );
		$tbl_types = array(
		                   'full'=>array(
										   'in'=>array('count'=>0,array('cols_num'=>5)),
										   'out'=>array('count'=>0,array('cols_num'=>5))
										   ),
						   'ra'=>array(
										   'in'=>array('count'=>0,array('cols_num'=>5)),
										   'out'=>array('count'=>0,array('cols_num'=>5))
										   ),
						   );
						   
         $tbl_row['full']['in'] = make_table_tpl('in');
		 $tbl_row['full']['out'] = make_table_tpl('out');
		 $tbl_row['ra']['in'] = make_table_tpl('in');
		 $tbl_row['ra']['out'] = make_table_tpl('out');
	}
	if(isset($tbl_row)){
	     
		/* if(in_array('full',$tbl_row)){
		    if(!in_array('in',$tbl_row['full'])){
			   // $tbl_row['full']['in'] = make_table_tpl('in');
			}
			//if(!in_array('out',$tbl_row['full'])) $tbl_row['full']['out'] = make_table_tpl('out');
		 }*/
		 foreach(array('full','ra') as $level){
			 if(array_key_exists($level,$tbl_row)){
				if(!array_key_exists('in',$tbl_row[$level])){
					 $tbl_row[$level]['in'] = make_table_tpl('in');
					 $tbl_types[$level]['in'] = array('count'=>0,array('cols_num'=>5));
				}
				if(!array_key_exists('out',$tbl_row[$level])){
					 $tbl_row[$level]['out'] = make_table_tpl('out');
					 $tbl_types[$level]['out'] = array('count'=>0,array('cols_num'=>5));
				 }
			 }
			 else{
				 $tbl_row[$level] = array();
				 $tbl_row[$level]['in'] = make_table_tpl('in');
				 $tbl_types[$level]['in'] = array('count'=>0,array('cols_num'=>5));
				
				 $tbl_row[$level]['out'] = make_table_tpl('out');
				 $tbl_types[$level]['out'] = array('count'=>0,array('cols_num'=>5));
			 }
		 }
	}
	// сортируем массив чтобы всегда, сначала шел прайс 'in' а потом 'out', если будут еще уровни ПРАЙСОВ добавить инструкции по ним
	ksort($tbl_types['full']);
	ksort($tbl_types['ra']);
	// сортируем массив чтобы всегда, сначала шел уровень 'full' а потом 'ra', если будет больше уровней нужна будет другая сортировка
    ksort($tbl_types);

	//echo '<pre>'; print_r($tbl_types);echo '</pre>';
	//exit;
	//echo '<pre>'; print_r($tbl_row);echo '</pre>';
	//exit;
	foreach($tbl_types as $level => $levels){
	    echo '<div>уровень прайса - '.$level.'</div>';
		echo '<hr>';
	    foreach($levels as $type => $data){
            $count = 0;
	        $dop_row_data = array();
		    for($i=0;$i<=$levels[$type][$count]['cols_num'];$i++){
			  if($i<3 || $i==$levels[$type][$count]['cols_num']) $dop_row_data[$i]='<span></span>';
			  else $dop_row_data[$i]='<span onclick="deleteColFromTable(this);" class="deleteElementBtn">&#215;</span>';
		    }
		    // print_r($dop_row_data);
		    $dop_row = $tr1.$td1_hidden.implode($td_td,$dop_row_data).$td2.$tr2;
		    echo 'тип прайса: '.$type;
		    echo '<div>
				  <span class="pointer" onclick="addRowsToTbl(\''.$level.$type.$count.'\',{\'preLast\':true,\'clearCell\':1});">добавить</span><input size="1" id="rowsNum'.$level.$type.$count.'" value="1">рядов
				  &nbsp;&nbsp;
				  <span class="pointer" onclick="addColsToTbl(\''.$level.$type.$count.'\');">добавить</span><input size="1" id="colsNum'.$level.$type.$count.'" value="1">колонок
				 </div>';
		    echo '<form method="POST">';
		    echo '<table id="tbl'.$level.$type.$count.'">'.implode('',$tbl_row[$level][$type][$data['count']]).$dop_row.'</table>';
		   	
		    echo '<input type="text" name="dataBufferForDeleting" id="dataBufferForDeleting'.$level.$type.$count.'" value="">';
		    echo '<input type="text" name="dataBufferForSavingToBase" id="tblDataBuffer'.$level.$type.$count.'" value="">';
		    echo '<input type="button"  class="pointer" onclick="priceManagerSendDataToBase(this.form,{\'type\':\'price\',\'bufferId\':\'tblDataBuffer'.$level.$type.$count.'\',\'tblId\':\'tbl'.$level.$type.$count.'\',\'level\':\''.$level.'\',\'price_type\':\''.$type.'\',\'print_type_id\':\''.$usluga_id.'\',\'count\':\''.$count.'\'});" value="сохранить">';
		    echo '</form>';
		    echo '<br><br><br>';
		}
	}
	
	$query ="SELECT comment FROM `".BASE__PRICE_COMMENTS_TBL."` WHERE `print_id` = '".$usluga_id."'";
	$result = $mysqli->query($query)or die($mysqli->error);
	if($result->num_rows>0){
		  $cell = $result->fetch_assoc();
		  $comment = $cell['comment'];
	}
	else $comment='';
	 
	$price_comment ='<td width="230" class="subContentTd">';
	$price_comment .='Комментарий:';
	$price_comment .='<form method="POST" style="margin-top:8px;">';
    $price_comment .='<div style="margin-bottom:8px;"><textarea name="comment">'.$comment.'</textarea></div>';
	$price_comment .='<input type="submit" name="save_comment" value="сохранить">';
	$price_comment .='</form>';
	$price_comment .='</td>';
?>