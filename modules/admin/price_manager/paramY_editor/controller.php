<?php 
    $usluga_id = $_GET['usluga'];

    if(!empty($_POST['dataBufferForSavingToBase'])){
	    
	    $data = json_decode($_POST['dataBufferForSavingToBase']);
		
		// удаляем первый ряд содержащий вспомогательную информацию
		unset($data->tbl_data[0]);
		
	    //echo '<pre>'; print_r($data);echo '</pre>';
		
		if(!empty($_POST['dataBufferForDeleting'])){
		    $toDeleteArr = explode('|',trim($_POST['dataBufferForDeleting'],'|'));
		    //echo '<pre>'; print_r($toDeleteArr);echo '</pre>';////
			if(count($toDeleteArr)>0){
			    for($i=0;$i<count($toDeleteArr);$i++){
				     //echo '-'.(int)$toDeleteArr[$i].'-<br>';
					 if((int)$toDeleteArr[$i]!=0){
					      $query ="DELETE FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id = '".$toDeleteArr[$i]."'";
						  //echo $query;
						  $mysqli->query($query)or die($mysqli->error);
					 }
				}
				
			}
		}
		//
		
		foreach($data->tbl_data as $val){
		    $query ="SELECT*FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE id = '".$val[0]."'";
			echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
		       $query2 ="UPDATE `".BASE__CALCULATORS_Y_PRICE_PARAMS."` SET  print_type_id='".$usluga_id."' , param_type='".$val[1]."', value='".$val[2]."', percentage='".$val[3]."'  WHERE id = '".$val[0]."'";
			   $mysqli->query($query2)or die($mysqli->error);
			}
			else{
			  
			   $query2 ="INSERT INTO `".BASE__CALCULATORS_Y_PRICE_PARAMS."` VALUES('','".$usluga_id."','".$val[1]."','".$val[2]."','".$val[3]."')";
			   //echo $query2;
			   $mysqli->query($query2)or die($mysqli->error);
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
	
	// выбираем данные из таблицы содержащей дополнительные параметры для расчета нанесения
	$query="SELECT*FROM `".BASE__CALCULATORS_Y_PRICE_PARAMS."` WHERE `print_type_id` = '".$usluga_id."' ORDER by  id";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     
			 //print_r($row);
			
			 $param_type = substr(md5($row['param_type']),0,4);
		     unset($row['print_type_id']);
			 
			 array_push($row,'<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$param_type.'\');">&#215;</span>');
			 //
		     $tbl_data[$param_type][] = $tr1.$td1_hidden.implode($td_td,$row).$td2.$tr2;
			 
			 //print_r($tbl_rows);
		}
	}
	else{
	    $param_type = 'start';
	}
	
	function make_table_tpl($type){
	    global $td1;
		global $td1_hidden;
		global $td2;
		global $td_td;
		global $tr1;
		global $tr2;
		global $tr_tr;
	
	    $row_tpl = array('','тип', 'value' => 'наименование', 'percentage' => 'коэффициент','');
	    $row[] = $tr1.$td1_hidden.implode($td_td,$row_tpl).$td2.$tr2;
		
		$row_tpl = array('','','value' => '', 'percentage' => '1.00','<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$type.'\');">&#215;</span>');
	    $row[] = $tr1.$td1_hidden.implode($td_td,$row_tpl).$td2.$tr2;
		return $row;
	}
	
	if(isset($tbl_data)){
	    foreach($tbl_data as $param_type => $val){
			$row =array('','тип','value' => 'наименование', 'percentage' => 'коэффициент','');	
			array_unshift($tbl_data[$param_type],$tr1.$td1_hidden.implode($td_td,$row).$td2.$tr2);	 
		}  
         
		 
	}
	else{
	    $tbl_data[$param_type] = make_table_tpl($param_type);
	}
	
	foreach($tbl_data as $type => $rows){
       
		    //echo 'тип прайса: '.$type;
		    echo '<div>
				  <span class="pointer" onclick="addRowsToTbl(\''.$type.'0\',{\'clearCell\':1});">добавить</span><input size="1" id="rowsNum'.$type.'0" value="1">рядов
				 </div>';
		   echo '<form method="POST">';
		   echo '<table id="tbl'.$type.'0">'.implode('',$rows).'</table>';	
		   echo '<input type="hidden" name="dataBufferForDeleting" id="dataBufferForDeleting'.$type.'" value="">';
		   echo '<input type="hidden" name="dataBufferForSavingToBase" id="tblDataBuffer'.$type.'0" value="">';
		   echo '<input type="button"  class="pointer" onclick="priceManagerSendDataToBase(this.form,{\'type\':\'dop_data\',\'param_type\':\'цвет\',\'bufferId\':\'tblDataBuffer'.$type.'0\',\'tblId\':\'tbl'.$type.'0\'});" value="сохранить">';/**/
		   echo '</form>';
		   echo '<br><br><br>';
		
	}
	
	

?>