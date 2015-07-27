<?php 

    
	if(!isset($_SESSION['back_url'])) $_SESSION['back_url'] = $_SERVER['HTTP_REFERER'];
	
    if(isset($_GET['agregate_specification_rows']))
	{
	    //print_r($_GET['data']
		agregate_specification_rows($_GET['data']);
		header('Location:?page=agreement&section=specification_editor&client_id='.$client_id.'&specification_num='.$specification_num.'&agreement_id='.$agreement_id); 
		exit;
    }
	
	$result = fetch_specification($client_id,$agreement_id,$specification_num);
	
	if($result)
	{
        
		$tpl = './skins/tpl/agreement/specification_edit_row.tpl';
	    $fd = fopen($tpl,'r');
	    $tpl = fread($fd,filesize($tpl));
	    fclose($fd);
		
		ob_start();
		$row_num = 0;
		$itogo=0;
		while($row = mysql_fetch_assoc($result))
		{
            eval('?>'.$tpl.'<?php ');  
			$itogo += (float)$row['summ'];		
		}
		
		$rows = ob_get_contents();
	    ob_get_clean();
		
		include './skins/tpl/agreement/specification_edit_tbl.tpl';
	}
	else $rows = 'ошибка получения данных specification_editor_controller.php';

?>
