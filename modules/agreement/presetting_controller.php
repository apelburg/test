<?php

   $_SESSION['data_for_specification'] = $_GET['ids'];
   

    print_r($_GET);
	exit;
    header('Location:?'.addOrReplaceGetOnURL('section=choice','data&ids'));

?>