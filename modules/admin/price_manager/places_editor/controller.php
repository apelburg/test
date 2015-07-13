<?php 
    $usluga_id = $_GET['usluga'];
    $type = 'places';
    if(!empty($_POST['dataBufferForSavingToBase'])){
	    
	    $data = json_decode($_POST['dataBufferForSavingToBase']);
		
		// удаляем первый ряд содержащий вспомогательную информацию
		unset($data->tbl_data[0]);
		
	    //echo '<pre>'; print_r($data);echo '</pre>';//
	
		if(!empty($_POST['dataBufferForDeleting'])){
		    $toDeleteArr = explode('|',trim($_POST['dataBufferForDeleting'],'|'));
		    //echo '<pre>'; print_r($toDeleteArr);echo '</pre>';////
			if(count($toDeleteArr)>0){
			    for($i=0;$i<count($toDeleteArr);$i++){
				     //echo '-'.(int)$toDeleteArr[$i].'-<br>';
					 if((int)$toDeleteArr[$i]!=0){
					      $query ="DELETE FROM `".BASE__PRINT_PLACES_TYPES_TBL."` WHERE id = '".$toDeleteArr[$i]."'";
						  //echo $query;
						  $mysqli->query($query)or die($mysqli->error);
					 }
				}
				
			}
		}
		//
		foreach($data->tbl_data as $val){
		    $query ="SELECT*FROM `".BASE__PRINT_PLACES_TYPES_TBL."` WHERE id = '".$val[0]."'";
			
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
		       $query2 ="UPDATE `".BASE__PRINT_PLACES_TYPES_TBL."` SET name='".$val[1]."', comment='".$val[2]."' WHERE id = '".$val[0]."'";
			   $mysqli->query($query2)or die($mysqli->error);
			}
			else{
			  
			   $query2 ="INSERT INTO `".BASE__PRINT_PLACES_TYPES_TBL."` VALUES('','".$val[1]."','".$val[2]."')";
			   //echo $query2;
			   $mysqli->query($query2)or die($mysqli->error);
			}
		}
       // 
		header('location:'.$_SERVER['HTTP_REFERER']);
		exit;
	}

   
	
	$td1  = '<td contenteditable="true">'; 
	$td1_hidden  = '<td style="display:none;">'; 
	$td1_grey  = '<td style="color:#AEC7EC;">'; 
	$td1_unedit  = '<td>'; 
	$td2  = '</td>';
	$td_td  = $td2.$td1;
	$td_td_unedit  = $td2.$td1_unedit;
	$tr1  = '<tr>'; 
	$tr2  = '</tr>';
	$tr_tr  = $tr2.$tr1;
	$tbl_types = array();
	
	// выбираем данные из таблицы содержащей данные о местах нанесения
	$query="SELECT*FROM `".BASE__PRINT_PLACES_TYPES_TBL."` ORDER by  id";
	//echo $query;
	$result = $mysqli->query($query)or die($mysqli->error);
	
	if($result->num_rows>0){
		while($row = $result->fetch_assoc()){ 
		     
			 //print_r($row);
			 
			 array_push($row,'<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$type.'\');">&#215;</span>');
			 //
		     $tbl_rows[] = $tr1.$td1_grey.implode($td_td,$row).$td2.$tr2;
			 
			 //print_r($tbl_rows);
		}
	}
	function make_table_tpl(){
	    global $td1;
		global $td1_hidden;
		global $$td1_grey;
		global $td2;
		global $td_td;
		global $tr1;
		global $tr2;
		global $tr_tr;
	
	    $row_tpl =array('','место нанесения', 'комментарий','');	
	    $row[] = $tr1.$td1_grey.implode($td_td,$row_tpl).$td2.$tr2;
		
		$row_tpl = array('','грудь (00х00)(пример)', 'футболка, поло(пример)','<span class="deleteElementBtn" onclick="deleteRowFromTable(this,\''.$type.'\');">&#215;</span>');
	    $row[] = $tr1.$td1_grey.implode($td_td,$row_tpl).$td2.$tr2;
		return $row;
	}
	
	if(isset($tbl_rows)){
	   
		$row =array('','место нанесения', 'комментарий','');	
		array_unshift($tbl_rows,$td1_grey.implode($td_td_unedit,$row).$td2.$tr2);	
	
         
		 
	}
	else{
	    unset($tbl_rows);
	    $tbl_rows = make_table_tpl();
	}
	/**/

   
	//echo 'тип прайса: '.$type;
	echo '<div>
		  <span class="pointer" onclick="addRowsToTbl(\''.$type.'\',{\'clearCell\':1});">добавить</span><input size="1" id="rowsNum'.$type.'" value="1">рядов
		 </div>';
   echo '<form method="POST">';
   echo '<table id="tbl'.$type.'">'.implode('',$tbl_rows).'</table>';	
   echo '<input type="hidden" name="dataBufferForDeleting" id="dataBufferForDeleting'.$type.'" value="">';
   echo '<input type="hidden" name="dataBufferForSavingToBase" id="tblDataBuffer'.$type.'" value="">';
   echo '<input type="button"  class="pointer" onclick="priseManagerSendDataToBase(this.form,{\'type\':\'places\',\'bufferId\':\'tblDataBuffer'.$type.'\',\'tblId\':\'tbl'.$type.'\'});" value="сохранить">';/**/
   echo '</form>';
   echo '<br><br><br>';
		
	
	

?>