<?php 

class Manager {
    /*
	
	СВОЙСТВА :
	  data - содержит : даннные из таблицы MANAGERS_TBL ,тип : массив 
	МЕТОДЫ :
	   __construct - действие : помещает даннные из таблицы MANAGERS_TBL в свойство $this->data , принимает : id менеджера , возращает : ничего
	
	*/
    public function __construct($id){
	    global $mysqli;
	   
	    $query="SELECT*FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
	    $result = $mysqli->query($query)or die($mysqli->error);
	    if($result->num_rows>0){
		    $this->data=$result->fetch_assoc();
	    }
	    else{
	        // обработка пустой выборки
	    }
	}	
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