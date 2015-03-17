<?php 

class Manager {
   public function get_mail_signature($id){
		
		   global $mysqli;
		   
		   $query="SELECT*FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		   $result = $mysqli->query($query)or die($mysqli->error);
		   if($result->num_rows>0){
		       $manager_data=$result->fetch_assoc();
		   }
		
		return convert_bb_tags($manager_data['mail_signature']);
	}	
}

?>