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

