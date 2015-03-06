<div class="client_table">
    <?php
    // echo "<pre>";
    // print_r($this_contact_face);
    // echo "</pre>";
    ?>
	<table class="client_table_gen" >
    	<tr>
        	<td>
            	<table>
                	<tr>
                    	<td>ФИО</td>
                    	<td><strong><?php echo $this_contact_face['name']; ?></strong></td>
                    </tr>
                	<tr>
                    	<td>Должность</td>
                    	<td><span  style="color:red"><?php echo $this_contact_face['position']; ?></span></td>
                    </tr>
                	<tr>
                    	<td>Отдел</td>
                    	<td><span  style="color:red"><?php echo $this_contact_face['department']; ?></span></td>
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