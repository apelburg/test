



<style type="text/css">
table#tbl_edit_usl{
	border-collapse: collapse;
}
#tbl_edit_usl tr th{
	border:1px solid #d4d4d4;
	padding: 5px 15px 5px 15px;
	text-align: left
}
#tbl_edit_usl tr td{
	border:1px solid #d4d4d4;
	padding: 5px
}
#tbl_edit_usl tr td#tbl_edit_usl_content{
	min-width: 750px
}
#tbl_edit_usl .lili{
	padding: 6px 5px 2px 5px;
	cursor: default;
}
#tbl_edit_usl .lili:hover{
	background-color: #f0f0f0;
}
#tbl_edit_usl .lili.checked{
	background-color: #BABABA;
}
#tbl_edit_usl .lili.checked:hover{
	background-color: #BABABA;
}
#tbl_edit_usl .lili.f_open{
	background-image: url('./skins/images/img_design/open-closed-folder.png');
	background-repeat: no-repeat;
	background-position-y: -21px;
}

#tbl_edit_usl .lili.for_one{
	background-image: url('./skins/images/img_design/usluga_icon_for_all_one.png');
	background-repeat: no-repeat;
	background-position-y: -21px;
}

#tbl_edit_usl .lili.for_all{
	background-image: url('./skins/images/img_design/usluga_icon_for_all_one.png');
	background-repeat: no-repeat;
	background-position-y: 3px;
}
#tbl_edit_usl .lili.f_close{
	background-image: url('./skins/images/img_design/open-closed-folder.png');
	background-repeat: no-repeat;
	background-position-y: 3px;
}

#tbl_edit_usl .lili span.button{
	padding: 2px 5px 0 5px;
	margin-left: 5px;
	margin-top: -4px;
	float: right;
	background-color: #fff;
	border: 1px solid #ddd;
	color: grey;
}
#tbl_edit_usl tr td#tbl_edit_usl_content {
	padding-left: 30px;
}

#tbl_edit_usl_content.loading{
	background-image: url(./skins/images/img_design/preloader.gif);
	background-repeat: no-repeat;
	background-position: 50%;
}
#tbl_edit_usl_content div{
	padding-top: 15px;
}
#tbl_edit_usl_content div input{
	/*float: left;*/
}
#tbl_edit_usl_content .gname{
	padding-top: 18px;
}
#tbl_edit_usl_content .status_del{
	padding: 2px 5px 0 5px;
	margin-left: 5px;
	background-color: #fff;
	border: 1px solid #ddd;
	color: grey;
	position: relative;
	padding-top: 0px;
	cursor: default;
}
.icon_style{
	padding-left: 27px;
	height: 15px;
}
.icon_style.folder{
	background-image: url('./skins/images/img_design/open-closed-folder.png');
	background-repeat: no-repeat;
	background-position-y: -26px;
}
.icon_style.for_one{
	background-image: url('./skins/images/img_design/usluga_icon_for_all_one.png');
	background-repeat: no-repeat;
	background-position-y: -26px;
}
.icon_style.for_all{
	background-image: url('./skins/images/img_design/usluga_icon_for_all_one.png');
	background-repeat: no-repeat;
	background-position-y: -1px;
}
.name_input{
	font-weight: bold;
}
#hidden_button,#response_message{ 
	display: none;	
}
#response_message.green{background-color: #78C05A;}
#response_message.red{background-color: #DF9F9F;}
</style>

<script type="text/javascript">
	
$(document).on('click', '#tbl_edit_usl .lili', function(event) {
	$('.lili').removeClass('checked');
	$(this).addClass('checked');
	$('#tbl_edit_usl tr td#tbl_edit_usl_content').html('').addClass('loading');

	$.post('', {
		AJAX:'get_edit_content_for_usluga',
		id:$(this).attr('data-id')
	}, function(data, textStatus, xhr) {
		$('#tbl_edit_usl_content').html(data).removeClass('loading');
	});
});




// ОТРАБОТКА КНОПОК
// удаление услуги
$(document).on('click', '.button.usl_del', function(event) {
	if(confirm('Вы уверены, что хотите удалить эту услугу')){
		alert('Удаляем');
		$(this).parent().remove();
	}
});

// добавление услуги
$(document).on('click', '.button.usl_add', function(event) {
	if(confirm('Вы уверены, что хотите добавить сюда новую услугу')){
		alert('Добавляем');
	}
});

// добавление статуса к услуге
$(document).on('click', '#add_new_status', function(event) {
	alert('добавить статус');

});

// удслить статус
$(document).on('click', '.button.status_del', function(event) {
	if(confirm('Вы уверены, что хотите удалить данный статус')){
		alert('Удаляем');
		$(this).parent().remove();
	}
});

// меняем название статуса
$(document).on('keyup', '.status_name', function(event) {
	console.log($(this).val());
});


// РЕДАКТИРУЕМ УСЛУГИ

// показываем кнопку сохраниеть при внесении изменений
$(document).on('keyup', '#edit_block_usluga input,#edit_block_usluga textarea', function(event) {
	$('#hidden_button').show('fast');
});
$(document).on('change', '#edit_block_usluga input,#edit_block_usluga textarea', function(event) {
	$('#hidden_button').show('fast');
});

// сохранение изменённой информации по варианту
$(document).on('click', '#save_usluga', function(event) {
	var form = $('#edit_block_usluga form').serialize();
	$.post('',form, function(data, textStatus, xhr) {
		if(data['response']=="OK"){
			$("#response_message")
				.html(data['message'])
				.fadeIn('fast')
				.delay(3000)
				.fadeOut('slow');
		}else{
			$("#response_message")
				.html(data)
				.fadeIn('fast')
				.delay(3000)
				.fadeOut('slow');
		}
	}, 'json');
});





</script>

<table id="tbl_edit_usl">
	<tr>
		<th>Дерево услуг</th>
		<th>Настройки</th>
	</tr>
	<tr>
		<td><?php echo $tree; ?></td>
		<td id="tbl_edit_usl_content">
			<div></div>
		</td>
	</tr>
</table>