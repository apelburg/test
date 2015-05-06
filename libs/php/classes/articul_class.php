<?php
class Articul{

	public function __construct($art_id){
		$this->color = $this->get_color($art_id);		
		$this->material = $this->get_material($art_id);
		$this->get_print_mode = $this->get_print_mode($art_id);

		global $mysqli;
		$query = "SELECT * FROM `".BASE_TBL."` WHERE `id` = '".(int)$art_id."'";
		// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				
			}
		}
	}

	private function get_color($art_id){
		global $mysqli;
		$query = "SELECT * FROM `".BASE_COLORS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['color'];
			}
		}
		return $arr;
	}
	private function get_material($art_id){
		global $mysqli;
		$query = "SELECT * FROM `".BASE_MATERIALS_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['material'];
			}
		}
		return $arr;
	}
	private function get_print_mode($art_id){
		global $mysqli;
		$query = "SELECT * FROM `".BASE_PRINT_MODE_TBL."` WHERE `art_id` = '".(int)$art_id."'";
		// echo $query;
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row['print'];
			}
		}
		return $arr;
	}
}