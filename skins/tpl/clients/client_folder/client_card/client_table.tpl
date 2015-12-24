<style type="text/css">
    body .quick_bar_tbl td .quick_button_div{
      background: none;
    }
    .adress_note{ float: left; padding-top: 10px; color: rgb(176, 175, 175)}
    #requisits_button{display: block;float: right;padding: 3px 10px 2px 10px;background-color: #F3F5F5;border:1px solid #D0D7D8;position: absolute;right: 50%;margin-right: 20px; cursor: default;}
    #requisits_button:hover{ background-color: #E4E8E8;}
</style>
<script type="text/javascript" src="libs/js/client_card_table.js"></script>
<script type="text/javascript" src="libs/js/rate_client.js"></script>



<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
<div id="requisits_button">Реквизиты</div>
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
                        <td>Кураторы</td>
                        <td><?php echo $manager_names; ?></td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td><span  style="color:#f1f1f1">В разработке</span></td>
                    </tr>
                	<?php echo $client_address_s; ?>
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