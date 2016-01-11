//////////////////////////
//	КОММЕНТАРИИ К ЗАПРОСАМ
//////////////////////////

$(document).on('click', '.icon_comment_show', function(event) {
	var query_num = $(this).attr('data-rt_list_query_num');
	$.post('', {
		AJAX: 'get_comment_for_query',
		query_num:query_num
	}, function(data, textStatus, xhr) {
		show_dialog_comments(Base64.decode(data['html']),'Комментарии к запросу',800)
	},'json');
});

$(document).on('click', '#add_comments_of_query', function(event) {
	var query_num = $(this).attr('data-query_num');
	$(this).animate({
		height: 68},
		'fast').addClass('loading').html('');
	var obj = $(this);
	$.post('', {
		AJAX: 'get_comment_for_query_without_form',
		query_num:query_num
	}, function(data, textStatus, xhr) {
		obj.replaceWith('<div class="history_query">'+Base64.decode(data['html'])+'</div>');
	},'json');
});

//////////////////////////
//	КОММЕНТАРИИ К ЗАКАЗАМ
//////////////////////////
$(document).on('click', '.icon_comment_order_show', function(event) {
	var query_num = $(this).attr('data-cab_list_query_num');
	var order_num = $(this).attr('data-cab_list_order_num');
	$.post('', {
		AJAX: 'get_comment_for_order',
		query_num:query_num,
		order_num:order_num
	}, function(data, textStatus, xhr) {
		show_dialog_comments(Base64.decode(data['html']),'Комментарии к заказу',800)
	},'json');
});


$(document).on('click', '.get_comments_for_positions', function(event) {
	event.preventDefault();
	var position_id = $(this).attr('data-id');
	$.post('', {
		AJAX:'get_comment_for_position',
		position_id:position_id
	}, function(data, textStatus, xhr) {
		show_dialog_comments(Base64.decode(data['html']),'Комментарии по позиции',800)
	},'json');
});

$(document).on('click', '#add_comments_of_order', function(event) {
	var order_num = $(this).attr('data-order_num');
	$(this).animate({
		height: 68},
		'fast').addClass('loading').html('');
	var obj = $(this);
	$.post('', {
		AJAX: 'get_comment_for_order_without_form',
		order_num:order_num
	}, function(data, textStatus, xhr) {
		obj.replaceWith('<div class="history_order">'+Base64.decode(data['html'])+'</div>');
	},'json');
});

//////////////////////////
//	КОММЕНТАРИИ К ПОЗИЦИЯМ
//////////////////////////

$(document).on('click', '#add_comments_of_position', function(event) {
	var position_id = $(this).attr('data-position_id');
	$(this).animate({
		height: 68},
		'fast').addClass('loading').html('');
	var obj = $(this);
	$.post('', {
		AJAX: 'get_comment_for_position',
		position_id:position_id
	}, function(data, textStatus, xhr) {
		obj.replaceWith(Base64.decode(data['html']));
	},'json');
});

//////////////////////////
//	ОБЩИЕ ФУНКЦИИ
//////////////////////////
$(document).on('click', '.add_nah', function(event) {
	event.preventDefault();
	var add_val = $(this).html();	//alert($(this).val());
	var textarea_val = $(this).parent().parent().parent().find('textarea').val();
	// if(textarea_val!=""){
		// $(this).parent().parent().parent().find('textarea').val(textarea_val+'\n' +add_val);	
	// }else{
	$(this).parent().parent().parent().find('textarea').val(textarea_val+' '+add_val);
	// }
	
});

$(document).on('click', '#add_new_comment_button', function(event) {
	event.preventDefault();
	var obj = $(this);
	var serialize = $('#dialog_gen_window_form .add_new_comment form,.add_new_comment form').serialize();
	$(this).parent().find('.comment_text textarea').val('');
	$.post('', serialize, function(data, textStatus, xhr) {
		if (data['response']!="OK") {
			alert('УПС......Что-то пошло не так');
		}else{
			// вставляем сообщение в HTML
			obj.parent().parent().parent().parent().before(Base64.decode(data['html']));
			// подчищаем textarea
			obj.parent().parent().parent().find('textarea').val('');
		}
	},'json');
});

// показать окно для комментариев
function show_dialog_comments(html,title,height){
	height_window = height || 'auto';
	title = title || '*** Название окна ***';
	var buttons = new Array();
	buttons.push({
	    text: 'Закрыть',
	    click: function() {

	    	$('#dialog_gen_window_form').dialog( "destroy" );			    	
	    	$('#dialog_gen_window_form').remove();
	    }
	});

	if($('#dialog_gen_window_form').length==0){
		$('body').append('<div id="dialog_gen_window_form"></div>');
	}
	$('#dialog_gen_window_form').html(html);
	$('#dialog_gen_window_form').dialog({
          width: '1000',
          // height: height_window,
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });
}


