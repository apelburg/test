
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
		<input type="hidden" name="client_id" value="">
		<input type="hidden" name="parent_tbl" value="">
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
				<!-- <input type="hidden" name="type" id="new_other_row_infoType_input"> -->
				<input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
				<input type="hidden" name="parent_tbl" value="">
			</tr>
		</table>
	</form>
</div>

<!-- contact face edit -->
<style type="text/css">
	#contact_face_edit_form{display: none}
	#contact_face_edit_form table{width: 100%;}
	#contact_face_edit_form table tr td:nth-of-type(1){width: 70px; text-align: right}
	#contact_face_edit_form input{width: 100%}
</style>
<div id="contact_face_edit_form">	
	<form>
		<table>
			<tr>
				<td>Фаммилия</td>
				<td><input type="text" name="last_name" ></td>
			</tr>
			<tr>
				<td>Имя</td>
				<td><input type="text" name="name" ></td>
			</tr>
			<tr>
				<td>Отчество</td>
				<td><input type="text" name="surname" ></td>
			</tr>
			<tr>
				<td>Должность</td>
				<td><input type="text" name="position" ></td>
			</tr>
			<tr>
				<td>Отдел</td>
				<td><input type="text" name="department" ></td>
			</tr>
			<tr>
				<td>Прим.</td>
				<td><input type="text" name="note" ></td>
			</tr>
		</table>
		<input type="hidden" name="ajax_standart_window" value="contact_face_edit_form">
		<input type="hidden" name="id" value="">
	</form>
</div>

