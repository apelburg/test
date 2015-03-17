<script type="text/javascript" src="libs/js/client_card_table.js"></script>
<link href="./skins/css/client_card_table.css" rel="stylesheet" type="text/css">


<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table class="edit_general_info">
                	<tr>
                    	<td>Название</td>
                    	<td>
                            <div class="edit_row edit" id="chenge_name_company" name="company" data-name="company" data-editType="text" data-button-name-window="save" data-idRow="<?php echo $client_id; ?>" data-tableName='CLIENTS_TBL'><?php echo trim($client['company']); ?></div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td>
                            <div style="padding:5px">В разработке</div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td>
                            <div style="padding:5px">В разработке</div>
                        </td>
                    </tr>
                	<?php echo $client_address_s; ?>                    
                    <tr>
                        <td></td>
                        <td>
                            <div class="button_add_new_row adres_row" data-parent-id="<?php echo $client_id; ?>">Добавить адрес</div>
                        </td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="add_new_row_phone button_add_new_row"  data-parent-id="<?php echo $client_id; ?>" data-parenttable="CLIENTS_TBL">добавить телефон</div></td>
                    </tr>
                </table>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row other_row add_new_row_other"  data-parent-id="<?php echo $client_id; ?>"  data-parenttable="CLIENTS_TBL">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>
