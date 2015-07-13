<?php 

   function rendering_menu()
	{
		global $db;
		
		function rendering($id,$level){	
			global $db;
			
			$query = "SELECT*FROM `".GIFTS_MENU_TBL."` WHERE parent_id = '".$id."' ORDER BY id";
			$result = mysql_query($query,$db) or die(mysql_error());
			if(mysql_num_rows($result)>0)
			{
				 ++$level;
				$td = '';
				
				while($item=mysql_fetch_assoc($result))
				{ 
					
					
					$td .= "<tr section_id=".$item['id']." parent_id=".$item['parent_id'].">
					        <td width='60'>
							    <span style='color:#AEC7EC;'>(ID ".$item['id'].")</span>
							</td>
							<!--<td class='numeration'>
							   ".$item['parent_id']."
							</td>-->
							<td  width='330' style='padding:0px 1px 0px ".(40*($level-1))."px;'>
							   <div id='name".$item['id']."' type='name'>".$item['name']."</div>
							</td>
							<!--<td class='item category' style='padding:0px;'>
							   <div id='category".$item['id']."' type='category'>".$item['category']."</div>
							</td>-->
							</tr>"
							.rendering($item['id'],$level);
				}
				return $td;
			}
			return '';
			
	    }
		
		$td = rendering(0,0);	
		return '<table class="menu_prototype">'.$td.'</table>';
	
	}
	
	$html = rendering_menu();

?>