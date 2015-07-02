<?php

	$place = ' > <a href="?page=admin&section=price_manager">УПРАВЛЕНИЕ ДОП. УСЛУГАМИ</a>';
   


	function get_uslugi_list_Database_Html($id=0){	
		global $mysqli;
		$html = '';
		
		$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			$html .= '<ul>';
			while($row = $result->fetch_assoc()){
				if($row['id']!=6){// исключаем нанесение apelburg
				// запрос на детей
				$child = get_uslugi_list_Database_Html($row['id']);
				// присваиваем конечным услугам класс may_bee_checked
				$html.= '<li data-id="'.$row['id'].'" '.(($child=='')?'class="may_bee_checked"':'').'>'.$row['name'].' '.$child.'</li>';
				}
			}
			$html.= '</ul>';
		}
		return $html;
	}

	echo '<table class="tbl_edit_usl"><tr><td>';
	echo  get_uslugi_list_Database_Html();
	echo '</td>';
	echo '<td>';
	echo 654654;

	echo '</td></tr>';
	echo '</table>';
	// echo '<>';
	// echo '</table>';



?>
