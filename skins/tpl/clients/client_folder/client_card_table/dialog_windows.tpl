
<style type="text/css">
#dop_phone_numver{width:50px;float: right}
#phone_numver{width:200px;}	
</style>

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


