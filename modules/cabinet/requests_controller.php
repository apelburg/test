<?php
// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **


// простой запрос
	$query = "SELECT*FROM `".BASE_TBL."` WHERE `size` ='small'  AND `art`='37Z53218' ORDER BY id";
	$result = $mysqli->query($query) or die($mysqli->error);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			print_r($row);
		}
	}






echo 'раздел '.$_GET["section"].'<br>';
echo 'подраздел '.$_GET["subsection"].'<br>';
echo 'тестим текст в разделе ';