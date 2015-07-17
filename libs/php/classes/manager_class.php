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
			foreach($result->fetch_assoc() as $key => $val){
			   $this->{$key} = $val;
			}
	    }
	    else{
	        return false;
	    }
	}

	static 	function get_snab_name_for_query_String($id){
	    global $mysqli;
	   	$arr = array();
	    $query="SELECT*FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
	    $result = $mysqli->query($query)or die($mysqli->error);
	    if($result->num_rows>0){
			foreach($result->fetch_assoc() as $key => $val){
			   $arr[$key] = $val;
			}
	    }
	    $String = "Не назначен";
	    if(count($arr)){
	    	$String = $arr['name'].' '.$arr['last_name'];
	    }
	    return $String;
	}

	static 	function get_apl_users(){
	    global $mysqli;
	   	$arr = array();
	    $query="SELECT `id`,`nickname`,`name`,`last_name`,`access` FROM `".MANAGERS_TBL."`  WHERE `access` IN (1,2,3,4,5,6,7,8,9,10)";
	    $result = $mysqli->query($query)or die($mysqli->error);
	    if($result->num_rows>0){
			foreach($result->fetch_assoc() as $key => $val){
			   $arr[] = $val;
			}
	    }
	    return $arr;
	}
}

?>