<style type="text/css">
    .adress_note{ float: left; padding-top: 10px; color: rgb(176, 175, 175)}
</style>
<script type="text/javascript" src="libs/js/rate_script.js"></script>
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
                    	<td><?php echo $clientRating; ?></td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td><span  style="color:#f1f1f1">В разработке</span></td>
                    </tr>
                	<?php echo $client_address_s; ?>
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