<?php
   
   $place = ' > <a href="?page=admin&section=price_manager">УПРАВЛЕНИЕ ПРАЙСАМИ</a>';
   
   // строим меню услуг по которым будем работать 
	$query="SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '6' ORDER BY id";
    $result = $mysqli->query($query)or die($mysqli->error);
    if($result->num_rows>0){
	    while($row = $result->fetch_assoc()){
		    $menu_arr[] = '<div style="padding:4px 0;border-bottom:#DDD 1px solid;"><a href="?page=admin&section=price_manager&subsection='.$subsection.'&usluga='.$row['id'].'" class="'.((!empty($_GET['usluga']) && $_GET['usluga']==$row['id'])?'currMenuItem':'').' menuItem">'.$row['name'].'</a></div>';
		}
	}

?>