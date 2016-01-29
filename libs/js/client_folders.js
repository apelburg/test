$(document).on('click', '.client_table_gen input[type="radio"]', function(event) {
    // event.preventDefault();
    
    console.log($('.client_table_gen input[type="radio"]:checked').attr('data-id'));
     // запрос окна
    $.post('', {
        AJAX: 'check_the_main_contact_face',
        contact_face_id:$('.client_table_gen input[type="radio"]:checked').attr('data-contact_face_id'),
        relate_id:$('.client_table_gen input[type="radio"]:checked').attr('data-relate_id'),
        // tbl:$(this).attr('data-tablename')
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');

});

