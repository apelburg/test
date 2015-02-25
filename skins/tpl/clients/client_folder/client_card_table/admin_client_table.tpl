<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td><input type="text" name="" value="<?php echo trim($client['company']); ?>"></td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td><span  style="color:red">В разработке</span></td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td><span  style="color:red">В разработке</span></td>
                    </tr>
                	<tr>
                    	<td>Адрес офиса</td>
                    	<td><textarea name=""><?php echo $client['addres']; ?></textarea></td>
                    </tr>
                	<tr>
                    	<td>Адрес доставки</td>
                    	<td><textarea name=""><?php echo $client['delivery_address']; ?></textarea></td>
                    </tr>
                	<tr>
                    	<td>Дополнительная информация</td>
                        <?php $dop_info_text_placeholder =(empty($client['dop_info']))?'информация отсутствует':''; $dop_info_text =(!empty($client['']))?$client['dop_info']:'';?>
                    	<td><textarea placeholder="<?php echo $dop_info_text_placeholder; ?> " name=""><?php echo $dop_info_text; ?></textarea>
                            </td>
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