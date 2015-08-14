<?php

   $_SESSION['data_for_specification'] = $_GET['ids'];
   

    //print_r($_GET);
    header('Location:?'.addOrReplaceGetOnURL('section=choice','data&ids'));

?>