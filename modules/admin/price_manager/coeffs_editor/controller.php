<?php 

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
					      $query ="DELETE FROM `".BASE__CALCULATORS_COEFFS."` WHERE id = '".$toDeleteArr[$i]."'";
						  //echo $query;
						  $mysqli->query($query)or die($mysqli->error);
					 }
				}
				
			}
		}
		foreach($data->tbl_data as $val){
		    $query ="SELECT*FROM `".BASE__CALCULATORS_COEFFS."` WHERE id = '".$val[0]."'";
			
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
		       $query2 ="UPDATE `".BASE__CALCULATORS_COEFFS."` SET  print_id='".$data->print_type_id."' , type='".$val[1]."', title='".$val[2]."', percentage='".$val[3]."', optional='".$val[4]."', multi='".$val[5]."', target='".$val[6]."' WHERE id = '".$val[0]."'";
			   $mysqli->query($query2)or die($mysqli->error);
			}
			else{
			  
			   $query2 ="INSERT INTO `".BASE__CALCULATORS_COEFFS."` VALUES('','".$data->print_type_id."','".$val[1]."','".$val[2]."','".$val[3]."','".$val[4]."','".$val[5]."','".$val[6]."')";
			   //echo $query2;
			   $mysqli->query($query2)or die($mysqli->error);
			}
		}
		

		header('location:'.$_SERVER['HTTP_REFERER']);
		exit;
		
	}

    $usluga_id = $_GET['usluga'];
	
	$td1  = '<td contenteditable="true">'; 
	$td1_unedit  = '<td>';
	$td1_hidden  = '<td style="display:none;">'; 
	$td2  = '</td>';
	$td_td  = $td2.$td1;
	$tr1  = '<tr>'; 
	$tr2  = '</tr>';
	$tr_tr  = $tr2.$tr1;
	$tbl_types = array();
	
	// выбираем данные из таблицы содержащей дополнительные параметры для расчета нанесения
	$query="SELECT*FROM `".BASE__CALCULATORS_COEFFS."` WHERE `print_id` = '".$usluga_id."' ORDER by  id";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     
			 //print_r($row);
			 $id = $row['id'];
		     unset($row['id'],$row['print_id']);
			 
			 array_push($row,'<span class="deleteElementBtn" onclick="deleteRowFromTable(this,0);">&#215;</span>');
			 //
		     $tbl_data[0][] = $tr1.$td1_hidden.$id.$td2.$td1.implode($td_td,$row).$td2.$tr2;
			 
			 //print_r($tbl_rows);
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
	
	    $row_tpl =array('','англ','заголовок','коэффициент','выборочно/поумолчанию','мульти/одиноч','применение(price/summ)','');	
	    $row[] = $tr1.$td1_hidden.implode($td_td,$row_tpl).$td2.$tr2;
		
		$row_tpl = array('','','','1.00','1','0','price','<span class="deleteElementBtn" onclick="deleteRowFromTable(this,0);">&#215;</span>');
	    $row[] = $tr1.$td1_hidden.implode($td_td,$row_tpl).$td2.$tr2;
		return $row;
	}

	if(isset($tbl_data)){
	   
		$row =array('англ','заголовок','коэффициент','выборочно/поумолчанию','мульти/одиноч','применение(price/summ)','');	
		array_unshift($tbl_data[0],$td1_hidden.''.$td2.$td1.implode($td_td,$row).$td2.$tr2);	   
         
		 
	}
	else{
	    $tbl_data[0] = make_table_tpl(0);
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
		   echo '<input type="button"  class="pointer" onclick="priceManagerSendDataToBase(this.form,{\'type\':\'dop_data\',\'bufferId\':\'tblDataBuffer'.$type.'0\',\'tblId\':\'tbl'.$type.'0\',\'print_type_id\':\''.$usluga_id.'\'});" value="сохранить">';/**/
		   echo '</form>';
		   echo '<br><br><br>';
		
	}
	
	

?>