<?php
    class rtCalculators{
	    //public $val = NULL;
	    function __consturct(){
		}
		
		static function change_svetofor($id,$val){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `row_status` = '".$val."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
    }

?>