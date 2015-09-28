<?php

   $_SESSION['data_for_specification'] = $_GET['ids'];

    // print_r($_GET);
	$dateDataObj = json_decode($_GET['dateDataObj']);
    if($dateDataObj->doc_type=='spec') header('Location:?'.addOrReplaceGetOnURL('section=choice','data&ids'));
	if($dateDataObj->doc_type=='oferta') header('Location:?'.addOrReplaceGetOnURL('section=long_term_agr_setting','data&ids'));


?>