//закрытие стандартного окна при ответе об успешном выполнении запроса 
$(document).on('click', '.ok_bw, .send_bw, .greate_bw, .save_bw', function(event) {
    var str = $('.html_modal_window form').serialize();
    $.post('', str, function(data, textStatus, xhr) {
        if(data=="OK"){
            $('#bg_modal_window,.html_modal_window').remove();
        }
    });
});