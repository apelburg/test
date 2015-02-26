<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td>
                            <input type="text" name=""   placeholder="информация отсутствует" value="<?php echo trim($client['company']); ?>"><br><br>
                            <div id="test_before"><?php echo trim($client['company']); ?></div>

                        </td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td><input type="text" name=""   placeholder="информация отсутствует" value="В разработке" disabled></td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td><input type="text" name="" placeholder="информация отсутствует" value="В разработке"  disabled></td>
                    </tr>
                	<tr>
                    	<td>Адрес офиса</td>
                    	<td><textarea name=""  placeholder="информация отсутствует"><?php echo $client['addres']; ?></textarea></td>
                    </tr>
                	<tr>
                    	<td>Адрес доставки</td>
                    	<td><textarea name=""  placeholder="информация отсутствует"><?php echo $client['delivery_address']; ?></textarea></td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row">добавить телефон</div></td>
                    </tr>
                </table>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>