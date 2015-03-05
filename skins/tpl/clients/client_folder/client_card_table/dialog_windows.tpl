
<style type="text/css">
#dop_phone_numver{width:50px;float: right}
#phone_numver{width:200px;}	
</style>
<!-- добавление телефона -->
<div id="add_new_phone" style="display:none;" title="Добавить номер телефона">
	<form>
		<table>
			<tr>
				<td>
					<select name="type_phone" id="type_phone">
						<option value="Рабочий" checked>Рабочий</option>
						<option value="Факс" >Факс</option>
						<option value="Домашний" >Домашний</option>
					</select>
				</td>
				<td><input type="text" name="telephone" id="phone_numver"></td>
				<td><div>доб. <input type="text" name="dop_phone" id="dop_phone_numver"></div></td>
			</tr>
		</table>
		<input type="hidden" name="ajax_standart_window" value="add_new_phone_row">
		<input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
	</form>
</div>

<!-- удаление телефона -->
<style type="text/css" media="screen">
	#delete_one_contact_row{ display: none}
</style>
<div id="delete_one_contact_row">
	Вы уверены, что хотите удалить данное поле?
</div>
<!-- добавление www, VK, E-mail -->
<style type="text/css" media="screen">
	#new_other_row_info{ display: none}
</style>
<div id="new_other_row_info">
	<form>
		<table>
			<tr>
				<td>
					<select name="type" id="new_other_row_infoType">
						<option selected="selected" value="0" disabled>Выберите тип записи</option>
						<option value="email"  >E-mail</option>
						<option value="web_site" >адрес web - сайта</option>
						<option value="vk" >адрес VK</option>
						<option value="fb" >адрес FaceBook</option>
						<option value="twitter" >адрес Twitter</option>
						<option value="skype" >Skype</option>
						<option value="icq" >ICQ</option>
						<option value="other" >другое</option>
					</select>
				</td>
				<td><input type="text" name="input_text" id="input_text"></td>
				<input type="hidden" name="ajax_standart_window" value="add_new_other_row">
				<input type="hidden" name="type" id="new_other_row_infoType_input">
				<input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
			</tr>
		</table>
	</form>
</div>

