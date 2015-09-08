<?php 

    $menu_id = (!empty($_GET['menu_id']))? $_GET['menu_id'] : FALSE ;
	
	
	
	
	
	if(!empty($_POST['tblDataBuffer'])){
	    
	    $data = json_decode($_POST['tblDataBuffer']);
		
        // удаляем последний ряд содержащий кнопки удаления колонок
		unset($data->tbl_data[0]);
	    echo '<pre>'; print_r($data);echo '</pre>';
		
		
		
		if(!empty($_POST['dataBufferForDeleting'])){
		    $toDeleteArr = explode('|',trim($_POST['dataBufferForDeleting'],'|'));
		    echo '<pre>'; print_r($toDeleteArr);echo '</pre>';////
			foreach($toDeleteArr as $place_id ){
			    // раздел меню может быть не конечным а родительским поэтому проверяем нет ли дочерних элементов
		        $menuIdsArr =  get_child_menu_items($menu_id);
			
				// выбираем артикулы которые надо будет удалить
				$query ="SELECT tbl2.art_id  art_id FROM `".BASE_ARTS_CATS_RELATION."` tbl1 LEFT JOIN 
										 `".BASE__ART_PRINT_PLACES_REL_TBL."` tbl2 
										 ON tbl1.article_id = tbl2.art_id 
										 WHERE tbl1.category_id IN ('".implode("','",$menuIdsArr)."') AND  tbl2.place_id = '".$place_id."' GROUP BY tbl2.art_id";
				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows>0)
				{
					while($item = $result->fetch_assoc())
					{ 
						$idsArtsToDelete[] = $item['art_id'];
					}	
				    echo '<pre>$idsArtsToDelete'; print_r($idsArtsToDelete);echo '</pre>';////
					if(isset($idsArtsToDelete) && count($idsArtsToDelete)>0){
					      $query ="DELETE FROM `".BASE__ART_PRINT_PLACES_REL_TBL."` WHERE art_id IN ('".implode("','",$idsArtsToDelete)."') AND place_id = '".$place_id."'";
						  echo  $query;
						  $mysqli->query($query)or die($mysqli->error);
				    }
					unset($idsArtsToDelete);
				}
			}
		}
		// exit; exit;
		foreach($data->tbl_data as $val){
		    // раздел меню может быть не конечным а родительским поэтому проверяем нет ли дочерних элементов
			$menuIdsArr =  get_child_menu_items($data->menu_id);
			// echo '<pre>$menuIdsArr'; print_r($menuIdsArr);echo '</pre>';
		    // exit;
			
		    // сначала выбираем артикулы которым уже присвоено данное место нанесения
			// и сохраняем его в массив, затем мы добавим в таблицу артикулы которые не буду в ходить в этот массив
		    $query ="SELECT tbl1.article_id article_id FROM `".BASE_ARTS_CATS_RELATION."` tbl1 LEFT JOIN 
			                     `".BASE__ART_PRINT_PLACES_REL_TBL."` tbl2 
								 ON tbl1.article_id = tbl2.art_id
			                     WHERE tbl1.category_id IN ('".implode("','",$menuIdsArr)."') AND  tbl2.place_id = '".$val[0]."'  GROUP BY tbl2.art_id";
			echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
			    while($row = $result->fetch_assoc()){ 
				    $existsArtsIdsArr[]=$row['article_id'];
				}
				echo '<pre>$existsArtsIdsArr'; print_r($existsArtsIdsArr);echo '</pre>';////
			}
			
			// выбираем артикулы которые относятся к данному разделу, разделам
			$query ="SELECT*FROM `".BASE_ARTS_CATS_RELATION."`
							 WHERE category_id IN ('".implode("','",$menuIdsArr)."') GROUP BY article_id";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0){
				while($row = $result->fetch_assoc()){ 
					$allArtsIdsArr[]=$row['article_id'];
				}
			}
			echo '<pre>$allArtsIdsArr'; print_r($allArtsIdsArr);echo '</pre>';//
				 
			
			
			if(isset($existsArtsIdsArr)) $allArtsIdsArr = array_diff($allArtsIdsArr,$existsArtsIdsArr);
			echo '<pre>$allArtsIdsArr'; print_r($allArtsIdsArr);echo '</pre>';//
			
			if(isset($allArtsIdsArr) && count($allArtsIdsArr)>0){
			    echo "bb";
				foreach($allArtsIdsArr as $art_id){
					$query2 ="INSERT INTO `".BASE__ART_PRINT_PLACES_REL_TBL."`
									 SET art_id = '".$art_id."', place_id = '".$val[0]."'";
					$mysqli->query($query2)or die($mysqli->error);
				}
			}
			unset($existsArtsIdsArr);
			unset($allArtsIdsArr);
		}
		header('location:'.$_SERVER['HTTP_REFERER']);
		exit;
	}
	
	
	function recur_menu($id){	
		global $mysqli;
		
		$query = "SELECT*FROM `".GIFTS_MENU_TBL."` WHERE parent_id = '".$id."' ORDER BY id";
		$result = $mysqli->query($query)or die($mysqli->error);
		if($result->num_rows>0)
		{
			while($item = $result->fetch_assoc())
			{ 
				$idsArr[] = $item['id'];
				$resultArr = recur_menu($item['id']);
				if($resultArr) $idsArr = array_merge($idsArr,$resultArr);
			}
			return $idsArr;	
		}
		return false;	
	}
	
	function get_child_menu_items($id)
	{
		$idsArr = recur_menu($id);
		$idsArr = ($idsArr)? array_merge($idsArr,array($id)):array($id) ;
		return $idsArr;
	}
	
    function rendering($id,$level){	
			global $mysqli;
			global $menu_id;
			 
			$query = "SELECT*FROM `".GIFTS_MENU_TBL."` WHERE parent_id = '".$id."' ORDER BY id";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0)
			{
				 ++$level;
				$td = '';
				
				while($item = $result->fetch_assoc())
				{ 
					
					$class = ($item['id']== $menu_id)?'active':'';
					$td .= "<tr section_id=".$item['id']." parent_id=".$item['parent_id'].">
					        <td width='60'>
							    <span style='color:#AEC7EC;'>(ID ".$item['id'].")</span>
							</td>
							<td  width='270' style='padding:1px 1px 1px ".(40*($level-1))."px;'>
							   <a href='?page=admin&section=places_editor&menu_id=".$item['id']."' class=".$class.">".$item['name']."</a>
							</td>
							</tr>"
							.rendering($item['id'],$level);
				}
				return $td;
			}
			return '';
			
	    }
    function rendering_menu($id =0 ,$level =0 )
	{
	    global $mysqli;
	    	
		$trs ='';
		if($id !=0)
		{	
		    $level++;
		    $query = "SELECT*FROM `".GIFTS_MENU_TBL."` WHERE id = '".$id."' ORDER BY id";
			$result = $mysqli->query($query)or die($mysqli->error);
			$item = $result->fetch_assoc();
			$trs .= "<tr section_id=".$item['id']." parent_id=".$item['parent_id'].">
						<td width='60'>
							<span style='color:#AEC7EC;'>(ID ".$item['id'].")</span>
						</td>
						<td  width='270' style='padding:1px 1px 1px ".(40*($level-1))."px;'>
						   <a href='?page=admin&section=places_editor&menu_id=".$item['id']."' class='active'>".$item['name']."</a>
						</td>
					</tr>";
		}
		$trs .= rendering($id,$level);
		
		return '<table class="catalogMenu">'.$trs.'</table>';
	}

			
			
	
	function buildPlacesSelectInterface()
	{
		
		function buildPlacesSelect(){	
			global $mysqli;
			
			$query ="SELECT*FROM `".BASE__PRINT_PLACES_TYPES_TBL."` ORDER BY id";
			$result = $mysqli->query($query)or die($mysqli->error);
			if($result->num_rows>0)
			{
				while($item = $result->fetch_assoc())
				{ 
					$options[]= "<option value=".$item['id'].">".$item['name'].'   &nbsp;&nbsp;['.$item['comment']."]</option>";
				}	
			
			}
			$select = "<select onchange='return addInputField(this);'><option value='0'></option>".implode('',$options)."</select>";
		    return $select;
	    }
		
		return  buildPlacesSelect();	
		//return '<table class="catalogMenu">'.$td.'</table>';
	
	}
	
	function buildExistsPlacesRows($menu_id)
	{
	    global $mysqli;
		
		// раздел меню может быть не конечным а родительским поэтому проверяем нет ли дочерних элементов
		$menuIdsArr =  get_child_menu_items($menu_id);
		
		$output = array();
	    $query ="SELECT tbl2.place_id place_id, tbl3.name name, tbl3.comment comment, COUNT(tbl1.article_id) count FROM `".BASE_ARTS_CATS_RELATION."` tbl1 LEFT JOIN 
			                     `".BASE__ART_PRINT_PLACES_REL_TBL."` tbl2 
								 ON tbl1.article_id = tbl2.art_id INNER JOIN 
			                     `".BASE__PRINT_PLACES_TYPES_TBL."` tbl3 
								 ON tbl3.id = tbl2.place_id
			                     WHERE tbl1.category_id IN ('".implode("','",$menuIdsArr)."') GROUP BY tbl2.place_id";
		$result = $mysqli->query($query)or die($mysqli->error);
	    if($result->num_rows>0)
		{
			while($item = $result->fetch_assoc())
			{ 
				$output[] ="<tr><td>".$item['place_id']."</td><td>".$item['name'].'  &nbsp;&nbsp;['.$item['comment'].']</td><td>'.$item['count'].' </td><td  class="pointer" onclick="deleteRowFromTable();">&#215;</td></tr>';
			}	
		
		}
		return $output;
	
	}
	
	$menuHTML = rendering_menu();
	
	if($menu_id){
	   
	    $placesSelectInterface = '<div class="affected_subparts">Затрагиваемые разделы:'.rendering_menu($menu_id).'</div>';
	    $placesSelectInterface .= '<div>Добавьте место нанесения выбрав из списка:</div>';
	    $placesSelectInterface .= buildPlacesSelectInterface();
		$placesSelectInterface .= '<form method="POST">';
		
		$existsPlacesRows = buildExistsPlacesRows($menu_id);
		
		$placesSelectInterface .= '<table id="containsDataTbl" class="containsDataTbl" style="display:'.((count($existsPlacesRows)>0)?'block':'none').'">
									   <tr>
										   <td></td>
										   <td width="330">место нанесения / коммент</td>
										   <td>кол-во артикулов</td>
										   <td></td>
									   </tr>'
									   .implode('',$existsPlacesRows).
								   '</table>';
	
		 
        $placesSelectInterface .= '<input type="hidden" name="dataBufferForDeleting" id="dataBufferForDeleting" value="">';
		$placesSelectInterface .= '<input type="hidden" name="tblDataBuffer" id="tblDataBuffer" value="">';
		$placesSelectInterface .= '<input type="button"  class="pointer" onclick="placesEditorSendDataToBase(this.form,{\'menu_id\':\''.$menu_id.'\',\'tblId\':\'containsDataTbl\',\'bufferId\':\'tblDataBuffer\'});" value="сохранить">';
		$placesSelectInterface .= '</form>';
	}
	else  $placesSelectInterface = 'выберите раздел меню';
	
	//this.form,{\'type\':\'price\',\'bufferId\':\'tblDataBuffer'.$type.$count.'\',\'tblId\':\'tbl'.$type.$count.'\',\'price_type\':\''.$type.'\',\'print_type_id\':\''.$usluga_id.'\',\'count\':\''.$count.'\'}
    
?>