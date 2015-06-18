<?php
   
   $place = ' > <a href="?page=admin&section=price_manager">УПРАВЛЕНИЕ ПРАЙСАМИ</a>';
   
   // выбираем услуги по которым будем работать 

	$query="SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '6'";
    $result = $mysqli->query($query)or die($mysqli->error);
    if($result->num_rows>0){
	    while($row = $result->fetch_assoc()){
		    $menu_arr[] = '<div><a href="?page=admin&section=price_manager&usluga='.$row['id'].'">'.$row['name'].'</a></div>';
		}
	}
    // если был выбран какой либо пункт в меню с услугами подключаем соответсвующий контроллер
	if(!empty($_GET['usluga'])) include 'uslugi/controller.php';
	
	include ROOT.'/skins/tpl/admin/price_manager/show.tpl';
?>