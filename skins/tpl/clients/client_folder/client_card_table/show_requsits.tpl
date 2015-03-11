<?php
// echo "<pre>";
// print_r($requesit);
// echo "</pre>";

?>
<table>
	<tr>
		<td>Компания</td>
		<td><?php echo (trim($requesit['company'])!='')?$requesit['company']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>Юр.лицо</td>
		<td><?php echo (trim($requesit['comp_full_name'])!='')?$requesit['comp_full_name']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>Телефон</td>
		<td><?php echo (trim($requesit['phone1'])!='' && trim($requesit['phone2'])!='')?$requesit['phone1'].'  '.$requesit['phone2'] :'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>Почт.адрес</td>
		<td><?php echo (trim($requesit['legal_address'])!='')?$requesit['legal_address']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>Почт.адрес</td>
		<td><?php echo (trim($requesit['postal_address'])!='')?$requesit['postal_address']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>Банк</td>
		<td><?php echo (trim($requesit['bank'])!='')?$requesit['bank']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>Адрес Банка</td>
		<td><?php echo (trim($requesit['bank_address'])!='')?$requesit['bank_address']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>ИНН</td>
		<td><?php echo (trim($requesit['r_account'])!='')?$requesit['r_account']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>ИНН</td>
		<td><?php echo (trim($requesit['r_account'])!='')?$requesit['r_account']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>КОР счет</td>
		<td><?php echo (trim($requesit['cor_account'])!='')?$requesit['cor_account']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>БИК</td>
		<td><?php echo (trim($requesit['bik'])!='')?$requesit['bik']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>ОГРН</td>
		<td><?php echo (trim($requesit['ogrn'])!='')?$requesit['ogrn']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>ОКПО</td>
		<td><?php echo (trim($requesit['okpo'])!=0)?$requesit['okpo']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
	<tr>	
		<td>доп. инфо</td>
		<td><?php echo (trim($requesit['okpo'])!=0)?$requesit['okpo']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></td>
	</tr>
</table>