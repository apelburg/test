<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td><strong><?php echo trim($client['company']); ?></strong></td>
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
                    	<td><?php echo $client['addres']; ?></td>
                    </tr>
                	<tr>
                    	<td>Адрес доставки</td>
                    	<td><?php echo $client['delivery_address']; ?></td>
                    </tr>
                	<tr>
                    	<td>Дополнительная информация</td>
                    	<td><?php echo !empty($client['dop_info'])?$client['dop_info']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?></td>
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