



<style type="text/css">
table#tbl_edit_usl{
	border-collapse: collapse;
}

.edit_info input[type="text"],#status_list input[type="text"]{
	min-width: 250px;
}
.edit_info textarea{
	min-width: 450px;
	min-height: 150px;
}
#tbl_edit_usl tr th{
	border:1px solid #d4d4d4;
	padding: 5px 15px 5px 15px;
	text-align: left;
	vertical-align: top;
}
#tbl_edit_usl tr td{
	vertical-align: top;
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
#tbl_edit_usl .lili.calc_icon{
	background-image: url('./skins/images/img_design/calc_icon.png');
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
	padding-top: 5px;
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
.icon_style.calc_icon{
	background-image: url('./skins/images/img_design/calc_icon.png');
	background-repeat: no-repeat;
	background-position-y: -1px;
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

#status_list .preload{
	background-image: url(./skins/images/img_design/spiffygif_32x32.gif);
	background-repeat: no-repeat;
	background-position: 0 50%;
	background-position-x: 75px;
	/* background-color: #DD3; */
	min-height: 23px;
}
.status_name.saved,.status_name.save_status_name{
	background-color: #ddd;
}

</style>

<script type="text/javascript">
	
$(document).on('click', '#tbl_edit_usl .lili', function(event) {
	$('.lili').removeClass('checked');
	$(this).addClass('checked');
	$('#tbl_edit_usl tr td#tbl_edit_usl_content').html('').addClass('loading');

	$.post('', {
		AJAX:'get_edit_content_for_usluga',
		id:$(this).attr('data-id'),
		parent_id:$(this).attr('data-parent_id')
	}, function(data, textStatus, xhr) {
		$('#tbl_edit_usl_content').html(data).removeClass('loading');
	});
});



// ОТРАБОТКА КНОПОК
// удаление услуги
$(document).on('click', '.button.usl_del', function(event) {
	var obj = $(this);
	event.stopPropagation();
	if(confirm('Вы уверены, что хотите удалить эту услугу')){
		$.post('', {
			AJAX:'del_uslugu',
			id:obj.parent().attr('data-id')
		}, function(data, textStatus, xhr) {			
			if(data['response']=="OK"){
				obj.parent().remove();
			}else{
				console.log('При удалении услуги произошла ошибка');
			}
		},'json');
	}
});

// добавление услуги
$(document).on('click', '.button.usl_add', function(event) {



	if(confirm('Вы уверены, что хотите добавить сюда новую услугу')){
		var obj = $(this);
		// меняем класс на папку
		obj.parent().attr('class','lili f_open');
		$.post('', {
			AJAX:'add_new_usluga',
			parent_id: obj.parent().attr('data-id'),
			padding_left:obj.parent().css('paddingLeft'),
			bg_x:obj.parent().attr('data-bg_x')
		}, function(data, textStatus, xhr) {
				obj.parent().after(data);
				obj.parent().next().click();
			});	

	}
});

// добавление статуса к услуге
$(document).on('click', '#add_new_status', function(event) {
	// добавляем div с классом анимашки загрузки
	$('#status_list').append('<div class="preload"></div>');
	var id = $('#edit_block_usluga input[name="id"]').val();
	$.post('', {AJAX:'add_new_status',id:id}, function(data, textStatus, xhr) {
		$('#status_list .preload').html(data).removeClass('preload');
	});

});

// удслить статус
$(document).on('click', '.button.status_del', function(event) {
	if(confirm('Вы уверены, что хотите удалить данный статус')){
		var obj = $(this);
		$.post('', {
			AJAX:'delete_status_uslugi',
			id: $(this).attr('data-id')
		}, function(data, textStatus, xhr) {
			if(data["response"]=="OK"){
				obj.parent().html('Удалено.');
			}else{
				console.log('что-т пошло не так');
			}
		},'json');		
	}
});

// РЕДАКТИРУЕМ НАЗВАНИЕ СТАТУСА
$(document).on('keyup', '.status_name', function(event) {
	timing_save_input('save_status_name',$(this));	
});
function save_status_name(obj){// на вход принимает object input
	var id = obj.next().attr('data-id');
	$.post('', {
		AJAX:'edit_name_status',
		id:id,
		name:obj.val()
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){
			obj.removeClass('saved');
		}else{
			console.log('Данные не были сохранены.');
		}
	},'json');
}
function timing_save_input(fancName,obj){
	//если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
	if(!obj.hasClass('saved')){
		window[fancName](obj);
		obj.addClass('saved');					
	}else{// стоит запрет, проверяем очередь по сейву данной функции		
		if(obj.hasClass(fancName)){ //стоит в очереди на сохранение
			// стоит очередь, значит мимо... всё и так сохранится
		}else{
			// не стоит в очереди, значит ставим
			obj.addClass(fancName);
			// вызываем эту же функцию через n времени всех очередей
			var time = 2000;
			$('.'+fancName).each(function(index, el) {
				console.log($(this).html());
				
				setTimeout(function(){timing_save_input(fancName,$('.'+fancName).eq(index));// обнуляем очередь
		if(obj.hasClass(fancName)){obj.removeClass(fancName);}}, time);	
			});			
		}		
	}
}

//сохраняет коменты снаба, на вход подаётся объект поля (не imput)
function save_comment_snab(obj){
	$.post('', {
		AJAX: 'edit_snab_comment',
		note: obj.html(),
		id_dop_data: obj.parent().parent().attr('data-id')
	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
		obj.removeClass('saved');
		
	});
}


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
				.fadeOut( "slow", function() {
				   $('#save_usluga').parent().fadeOut( "fast");
				  });
		}else{
			$("#response_message")
				.html(data)
				.fadeIn('fast')
				.delay(3000)
				.fadeOut( "slow");
		}
	}, 'json');
});

// обнуляем цену в input при выборе в radio папки
$(document).on('change', '#edit_block_usluga input[name="for_how"]', function(event){
	if($(this).val()==""){
		$('#edit_block_usluga input[name="price_in"]').val('0.00').attr('readonly',true);	
		$('#edit_block_usluga input[name="price_out"]').val('0.00').attr('readonly',true);	
	}else{		
		$('#edit_block_usluga input[name="price_in"]').val($('#edit_block_usluga input[name="price_in"]').attr('data-real')).attr('readonly',false);	
		$('#edit_block_usluga input[name="price_out"]').val($('#edit_block_usluga input[name="price_out"]').attr('data-real')).attr('readonly',false);
	}
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