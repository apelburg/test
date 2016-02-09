//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');
	$(this).addClass('checked');


	if($('#js-add-comment').length){
		$('#js-add-comment').remove();
	}
	console.log()
	// добавляем поле комментариев
	$(this).after('<div id="js-add-comment"><textarea name="comment" placeholder="ТЗ/Комментарии к услуге"></textarea></div>')
	$('#js-add-comment').css({'paddingLeft':$(this).css('paddingLeft'),'paddingRight':"42px"}).find('textarea').css({'minHeight':'auto','height':'52px'}).focus();

	var id = $(this).attr('data-id');
		
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	console.log('654');
});
