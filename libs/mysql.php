<?php

	// $db = mysql_connect('localhost','php','1234');
	$db= mysql_connect ("localhost","php_3477686","3477686");
	if(!$db) exit(mysql_error());
	//mysql_select_db('apelburg',$db);
	mysql_select_db ("apelburg_base",$db);
	//echo $db;
	mysql_query('SET NAMES utf8');          
    mysql_query('SET CHARACTER SET utf8');  
    mysql_query('SET COLLATION_CONNECTION="utf8_general_ci"');

?>
