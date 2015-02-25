<?php
include_once('./libs/config.php');
include_once('./libs/mysqli.php');

//удаляем таблицу
/*
//$query = "DROP TABLE IF EXISTS order_manager__clients_contact_information";
$query = "DROP TABLE order_manager__clients_contact_information";
$result = $mysqli->query($query) or die($mysqli->error);
echo ($result)?"таблица удалена успешно<br>":'';
//создаём таблицу
$query = "
CREATE TABLE IF NOT EXISTS `order_manager__clients_contact_information` (
  `id` int(11) AUTO_INCREMENT,
  `parent_id` int(11) ,
  `table` varchar(255) ,
  `type` varchar(255) ,
  `telephone_type` varchar(255) ,
  `contact` varchar(255) ,
  `dop_phone` varchar(255) ,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
";
$result = $mysqli->query($query) or die($mysqli->error);
echo ($result)?"таблица создана успешно<br>":'';
*/
function client_contact_list_update_1(){
	global $mysqli;
//получаем данные из основной таблицы
$query = "SELECT * FROM `".CLIENTS_TBL."`";
$result = $mysqli->query($query) or die($mysqli->error);
if($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$arr[] = $row;
	}
}
/*
echo '<pre>';
print_r($arr);
echo '<pre>';
*/
$i = 0;
$r = 0;
$num_str = 0;
$query = $query2=$q = "";
foreach($arr as $k=>$v){
	if(trim($v['email'])!=""){
		if($i==0){
			$q .= ($r==1)?';':'';
			$q .= "INSERT INTO `order_manager__clients_contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','email','','".$v['email']."','')";$i++;$num_str++;
		}else{
			$q .= ",('',".$v['id'].",'CLIENTS_TBL','email','','".$v['email']."','')";	$i++;$num_str++;
			if($i>500){$i=0;$r=1;}
		}
	}
	if(trim($v['phone'])!=""){
		if($i==0){
			$q .= ($r==1)?';':'';
			$q .= "INSERT INTO `order_manager__clients_contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','phone','work','".addslashes($v['phone'])."','')";$i++;$num_str++;
		}else{
			$q .= ",('',".$v['id'].",'CLIENTS_TBL','phone','work','".addslashes($v['phone'])."','')";	$i++;$num_str++;
			if($i>500){$i=0;$r=1;}
		}
	}
	if(trim($v['web_site'])!=""){
		if($i==0){
			$q .= ($r==1)?';':'';
			$q .= "INSERT INTO `order_manager__clients_contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','web_site','','".addslashes($v['web_site'])."','')";$i++;$num_str++;
		}else{
			$q .= ",('',".$v['id'].",'CLIENTS_TBL','web_site','','".addslashes($v['web_site'])."','')";	$i++;$num_str++;
			if($i>500){$i=0;$r=1;}
		}
	}
}

//echo $q."<br>";
$result = $mysqli->multi_query($q) or die($mysqli->error);//сохраняем выборку из 1 таблицы
echo "Из общей таблицы добавлено ".$num_str." строк<br>";
}
function client_contact_list_update_2(){
global $mysqli;
//получаем данные из основной таблицы
$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."`";
$result = $mysqli->query($query) or die($mysqli->error);
if($result->num_rows > 0){
	while($row = $result->fetch_assoc()){
		$arr[] = $row;
	}
}
/*
echo '<pre>';
print_r($arr);
echo '<pre>';
*/
$i = 0;
$r = 0;
$query = $query2=$q = "";
foreach($arr as $k=>$v){
	if(trim($v['email'])!=""){
		if($i==0){
			$q .= ($r==1)?';':'';
			$q .= "INSERT INTO `order_manager__clients_contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','email','','".$v['email']."','')";$i++;$num_str++;
		}else{
			$q .= ",('',".$v['id'].",'CLIENT_CONT_FACES_TBL','email','','".$v['email']."','')";	$i++;$num_str++;
			if($i>500){$i=0;$r=1;}
		}
	}
	if(trim($v['phone'])!=""){
		if($i==0){
			$q .= ($r==1)?';':'';
			$q .= "INSERT INTO `order_manager__clients_contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','phone','work','".addslashes($v['phone'])."','')";$i++;$num_str++;
		}else{
			$q .= ",('',".$v['id'].",'CLIENT_CONT_FACES_TBL','phone','work','".addslashes($v['phone'])."','')";	$i++;$num_str++;
			if($i>500){$i=0;$r=1;}
		}
	}
}

//echo $q."<br>";
$result = $mysqli->multi_query($q) or die($mysqli->error);//сохраняем выборку из 1 таблицы
echo "Из таблицы ".CLIENT_CONT_FACES_TBL." добавлено ".$num_str." строк<br>";
}

//client_contact_list_update_1();
client_contact_list_update_2();