//закрытие на ESC
$(document).keydown(function(e) {	
	if(e.keyCode == 27){
		$('#bg_modal_window,.html_modal_window').remove();
	}
});

//закрытие стандартного окна на "крестик" и "отмена"
$(document).on('click', '.html_modal_window_head_close,.cancel_bw', function(event) {
	$('#bg_modal_window,.html_modal_window').remove();
});

//закрытие стандартного окна при ответе об успешном выполнении запроса 
$(document).on('click', '.ok_bw, .send_bw, .greate_bw, .save_bw', function(event) {
    var str = $('.html_modal_window form').serialize();
    $.post('', str, function(data, textStatus, xhr) {
        if(data=="OK"){
            $('#bg_modal_window,.html_modal_window').remove();
        }
    });
});