<?php

/*
	
	Предлагается сложить сюда полезные функции и методы на PHP (наработки), 
	которые могут понадобится в дальнейшем. Создаём библиотеку полезных скриптов =)

	файл никуда не подключяется!!!  Расширение PHP только для подсветки синтаксиса!!!

*/


// показать содержимое директории
function show_dir($dir){
	// $dir = "./skins/tpl/clients/client_list/";
	$dh  = opendir($dir);
	while (false !== ($filename = readdir($dh))) {
	    $files[] = $filename;
	}
	echo '<pre>';
	print_r($files);
	echo '</pre>';	
}

// распечатать массив в переменную
function print_arr($arr){
	ob_start();	
		
		echo '<pre>';
		print_r($arr);
		echo '</pre>';		

		$html = ob_get_contents();
	
	ob_get_clean();

	return $html;
}

// генератор таблиц из массива
$items_num = 8;
$cols_num = 2;
$rows_num = ceil($items_num/$cols_num);
//echo $rows_num;
   
echo "<table border='1' width='100'>";
for($i =0 ;$i<$rows_num ; $i++){
	echo "<tr>";
	for($j =0 ;$j<$cols_num ; $j++){
		echo "<td style='border:1px solid #000'>".($i+$rows_num*$j)."</td>";
	}
	echo "</tr>";
}
echo "</table>";