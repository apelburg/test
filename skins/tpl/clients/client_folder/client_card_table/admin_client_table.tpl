<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td><input type="text" name=""   placeholder="информация отсутствует" value="<?php echo trim($client['company']); ?>"></td>
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
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>