<?php
    if(isset($_GET['show_calculator'])){
	   include('./skins/tpl/calculators/calculators.tpl');
	   exit;
	}
    echo '<div style="margin-top:300px;text-align:center;">такого раздела не существует</div>';
?>