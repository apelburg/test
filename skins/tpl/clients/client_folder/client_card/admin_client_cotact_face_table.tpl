<div class="delete_contact_face_table">
    <div class="delete_contact_face_table_button" data-contface="<?php echo $this_contact_face['id']; ?>">X</div>
</div>

<div class="client_table client_contact_face_tables">
	<table class="client_table_gen" >
    	<tr>
        	<td>
            	<table class="contact_face_tbl_edit" data-contface="<?php echo $this_contact_face['id']; ?>">
                	<tr>
                    	<td>ФИО</td>
                    	<td>
                            <span style="float: left;">
                               <?php
                                    global $manager_names_arr;

                                    if(isset($manager_names_arr[$_SESSION['access']['user_id']]['relate_id'])){

                                        $checked = '';
                                        if($this_contact_face['id'] == $manager_names_arr[$_SESSION['access']['user_id']]['cont_faces_relation_id']){
                                            $checked = ' checked = "checked"';
                                        }
                                    ?>
                                        <input type="radio" <?=$checked;?> data-relate_id="<?=$manager_names_arr[$_SESSION['access']['user_id']]['relate_id']?>" style="height: 2em;width: 2em;" data-contact_face_id="<?=$this_contact_face['id'];?>" id="id<?=$this_contact_face['id'];?>" name="main_cont_face">        
                                    <?php
                                    }
                                ?>
                            </span>
                            <label style="float: left; padding: 0.8em;" for="id<?=$this_contact_face['id'];?>">
                                <strong>
                                    <?php echo $this_contact_face['last_name'].' '.$this_contact_face['name'].' '.$this_contact_face['surname']; ?>
                                </strong>
                            </label>
                        </td>
                    </tr>
                	<tr>
                    	<td>Должность</td>
                    	<td><?php echo $this_contact_face['position']; ?></td>
                    </tr>
                	<tr>
                    	<td>Отдел</td>
                    	<td><?php echo $this_contact_face['department']; ?></td>
                    </tr>
                	<tr>
                    	<td>Примечание</td>
                    	<td><?php echo $this_contact_face['note']; ?></td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="add_new_row_phone button_add_new_row" data-parent-id="<?php echo $this_contact_face['id']; ?>" data-parenttable="CLIENT_CONT_FACES_TBL">добавить телефон</div></td>
                    </tr>
                </table>                
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row other_row add_new_row_other" data-parenttable="CLIENT_CONT_FACES_TBL"  data-parent-id="<?php echo $this_contact_face['id']; ?>">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>    
    <div class="border_in_table"></div>
</div>