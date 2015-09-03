<?php

// генерит html
     		private function generate_form_Html($inputs_arr,$parent='',$type_product){	
     			// echo '<pre>';
     			// print_r($arr);
     			// echo '</pre>';
     			$html = '';
     			$select = 0;
                    
     			foreach ($inputs_arr as $input){
                         
     				// $p_name = '';
     				if($parent==''){
     					// если это группа checkbox, то 
     					// echo $this->form_type[$type_product][$input['parent_name']]['btn_add_var'];
     					// if($input['type']=='checkbox' && isset($this->form_type[$type_product][$input['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$input['parent_name']]['btn_add_var']){
     					if($input['type']=='checkbox' && isset($this->form_type[$type_product][$input['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$input['parent_name']]['btn_add_var']){
     						$p_name = $input['parent_name'].'[][]';
     					}else{
     						$p_name = $input['parent_name'].'[0][]';
     					}
     				}else{
     					$parent = (substr($parent, -2, 2)=='[]')?substr($parent,0,strlen($parent)-2):$parent;
     					
     					 if(!strstr($parent, "[0]")){
     					 	$parent = $parent.'[0]';
     					 }
     					$p_name = $parent.'['.$input['parent_name'].']'.'[]';
     				}
     				
     				$id = $this->generate_id_Strintg($input['parent_name']);


     				$html .= ($input['note']!='')?'<span style="font-size:10px">'.$input['note'].'</span><br>':'';
     				// $html .= $input['type'];
     				switch ($input['type']) {
     					case 'textarea':// если тип поля textarea
     						if($select > 0){$html .= '</select><br>';$select =0;}
     						switch ($input['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<textarea data-id="'.$input['id'].'" id="'.$id.'" name="'.$p_name.'">'.$input['val'].'</textarea><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<textarea data-id="'.$input['id'].'" id="'.$id.'" name="'.$p_name.'">'.$input['val'].'</textarea>'.$this->span_del.'<br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     					case 'text':// если тип поля text
     						if($select > 0){$html .= '</select><br>';$select =0;}
     						switch ($input['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'"><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'">'.$this->span_del.'<br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     					case 'select':// если тип поля select
     						if($select == 0){$html .= '<select name="'.$p_name.'">';$select =1;}
     						switch ($input['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<option data-id="'.$input['id'].'" id="'.$id.'" value="'.$input['val'].'">'.$input['val'].'</option><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<option data-id="'.$input['id'].'" id="'.$id.'" value="'.$input['val'].'">'.$input['val'].' '.$this->span_del.'</option><br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     					
     					default:
     						if($select > 0){$html .= '</select><br>';$select =0;}
     						switch ($input['manager_id']) {
     							case '0': // если запись соответствует 0, т.е. обязательна для вывода
     								// выводим как есть
     								$html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'"><label for="'.$id.'">'.$input['val'].'</label><br>';
     								break;
     							case $this->user_id: // если запись соответствует id менеджера
     								// позволяем менеджеру удалить своё поле
     								$html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'"><label for="'.$id.'">'.$input['val'].' '.$this->span_del.'</label><br>';
     								break;
     							
     							default:
     								# code...
     								break;
     						}	
     						break;
     				}
     					
     				if($input['child']!=''){
     					$arr_child = $this->get_child_listing_Database_Array($input['child']);
     					$html .= '<div class="pad">'.$this->generate_form_Html($arr_child,$p_name,$type_product).'</div>';
     				}

     									
     			}
     			if($select > 0){$html .= '</select><br>';$select =0;}

     			return $html;
     		}