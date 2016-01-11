<!-- <?php echo __FILE__; ?> -- START-->
<script type="text/javascript" src="libs/js/rate_script.js"></script>
<link href="skins/css/supplier.css" rel="stylesheet" type="text/css">
<link href="skins/css/main.css" rel="stylesheet" type="text/css">

<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
<!-- <div id="requisits_button">Реквизиты</div> -->
            	<table>
                  <tr>
                      <td>Сокращенное название</td>
                      <td><strong><?php echo trim($supplier['nickName']); ?></strong></td>
                    </tr>
                  <tr>
                  <tr>
                      <td>Полное название</td>
                      <td><strong><?php echo trim($supplier['fullName']); ?></strong></td>
                    </tr>
                  <tr>
                    	<td>Рейтинг</td>
                    	<td><?php echo $supplierRating; ?></td>
                    </tr>
                	<tr>
                    	<td>Профили</td>
                    	<td><?php echo $get_activities; ?></td>
                    </tr>
                	<?php echo $supplier_address_s; ?>
                	<tr>
                    	<td>Дополнительная информация</td>
                    	<td><?php echo !empty($client['dop_info'])?$supplier['dop_info']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?></td>
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
<!-- <?php echo __FILE__; ?> -- END-->