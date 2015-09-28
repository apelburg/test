//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');
	$(this).addClass('checked');

	var id = $(this).attr('data-id');
		
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	console.log('654');
});
