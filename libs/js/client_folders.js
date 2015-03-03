//закрытие стандартного окна при ответе об успешном выполнении запроса 
$(document).on('click', '.ok_bw, .send_bw, .greate_bw, .save_bw', function(event) {
	//не отправляем, если это создание новой строки адреса
    if($('.html_modal_window form input[name="ajax_standart_window"]').val()=="add_new_adress_row"){return 1;}

    var str = $('.html_modal_window form').serialize();
    $.post('', str, function(data, textStatus, xhr) {
        if(data=="OK" || !(n.match(/^[-\+]?\d+/) === null)){
            $('#bg_modal_window,.html_modal_window').remove();
        }
    });
});

