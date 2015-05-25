<?php
    class RT{
	    //public $val = NULL;
	    function __consturct(){
		}
		static function save_rt_changes($data){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `".$data->prop."` = '".$data->val."'  WHERE `id` = '".$data->id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function expel_value_from_calculation($id,$val){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `expel` = '".$val."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function change_svetofor($id,$val){
		    global $mysqli;   //print_r($data); 
	   
			$query="UPDATE `".RT_DOP_DATA."` SET  `row_status` = '".$val."'  WHERE `id` = '".$id."'";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function set_masterBtn_status($data_obj){
		    global $mysqli;   //print_r($data); 

			$query="UPDATE `".RT_MAIN_ROWS."` SET  `master_btn` = '".$data_obj->status."'  WHERE `id` IN('".str_replace(";","','",$data_obj->ids)."')";
			//echo $query;
			$result = $mysqli->query($query)or die($mysqli->error);
		}
		static function calcualte_query_summ($query_num){
		    global $mysqli;   //print_r($data); 

		    $query = "SELECT dop_data_tbl.id AS dop_data_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.price_out AS dop_t_price_out , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.row_status AS row_status, dop_data_tbl.expel AS expel,
						  
						  dop_uslugi_tbl.id AS uslugi_id , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_out AS uslugi_t_price_out
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE main_tbl.query_num ='".$query_num."' ORDER BY main_tbl.id";
			 $result = $mysqli->query($query) or die($mysqli->error);
			 $arr = array();
			 while($row = $result->fetch_assoc()){
			     $arr[$row['dop_data_id']]['quantity'] = $row['dop_t_quantity'];
				 $arr[$row['dop_data_id']]['price_out'] = $row['dop_t_price_out'];
				 $arr[$row['dop_data_id']]['expel'] = $row['expel'];
				 if(!empty($row['uslugi_id'])){
				       $uslugi['glob_type'] = $row['uslugi_t_glob_type'];
				       $uslugi['quantity'] = $row['uslugi_t_quantity'];
					   $uslugi['price_out'] = $row['uslugi_t_price_out'];
				       $arr[$row['dop_data_id']]['uslugi'][] = $uslugi;
				 }
			 }
			 //echo '<pre>'; print_r($arr); echo '</pre>';
			 $summ = 0;
			 foreach($arr as $data){
			     if($data['expel']!='') $obj = json_decode($data['expel']);
				 if(isset($obj->main)&&$obj->main==1) continue;
				 $summ += $data['quantity']*$data['price_out'];
				 
				 if(!isset($data['uslugi'])) continue;
				 foreach($data['uslugi'] as $uslugi){
				     if(isset($obj->print) && $obj->print==1 && $uslugi['glob_type']=='print') continue;
					 if(isset($obj->dop) && $obj->dop==1 && $uslugi['glob_type']=='extra') continue;
					 $summ += $uslugi['quantity']*$uslugi['price_out'];
				 }
			 }
			 return  number_format($summ,'2','.','');
		}
		static function make_order($json){
		    $data_obj = json_decode($json);
            print_r($data_obj);
		}  
    }

?>