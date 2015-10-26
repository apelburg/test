// стандартный обработчик ответа AJAX
function standard_response_handler(data){
    if(data['function'] !== undefined){ // вызов функции... если требуется
        window[data['function']](data);
    }
    if(data['response'] != "OK"){ // вывод при ошибке
        console.log(data);
    }
}
// выбор исполнителя услуги // select_performer 
$(document).on('change', '.select_performer', function(event) {
    var val = $(this).val();
    $.post('', {
        AJAX : 'select_performer',
        val:val,
        usl_id:$('#tbl_edit_usl .lili.checked').attr('data-id')
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');
});

$(document).on('click', '.tz_incorrect', function(event) {
    // event.preventDefault();
    var value = '';
    var id_row = $(this).attr('data-id');
    if ($(this).prop('checked')) {
        value = 'on';
    }
    $.post('', {
        AJAX: 'edit_status_pause_tz_incorect',
        id_row:id_row,
        value:value
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');
});
$(document).on('click', '.get_performer_comment', function(event) {
    // event.preventDefault();
    var value = '';
    var id_row = $(this).attr('data-id');
    if ($(this).prop('checked')) {
        value = 'on';
    }
    $.post('', {
        AJAX: 'edit_status_get_performer_comment',
        id_row:id_row,
        value:value
    }, function(data, textStatus, xhr) {
        standard_response_handler(data);
    },'json');
});



//////////////////////////
//  вызов кокна добавления доп поля
//////////////////////////
$(document).on('click', '#add_new_dop_input', function(event) {
    $.post('', {
        AJAX: 'get_add_new_dop_input_form',
        usl_id:$('#tbl_edit_usl .lili.checked').attr('data-id')
    }, function(data, textStatus, xhr) {
        if(data['response']=="OK"){
            show_dialog_and_send_POST_window(Base64.decode(data['html']),'title');
        }else{
            alert('что-то пошло не так');
        }
    },'json');
});
// добавление dop_inputs
function add_new_dop_inputs(data){
    // если такой элемент уже есть в списке доп полей - выводим предупреждение и выходим
    var $alredy_exists = 0;
    $('#dop_inputs_listing .dop_inputs').each(function(index, el) {
        //console.log($(this).attr('data-id')+'   '+Number(data['dop_inputs_id']));
        
        if(Number($(this).attr('data-id'))==Number(data['dop_inputs_id'])){
            $alredy_exists = 1;
            return 1;
        }
    });
    if($alredy_exists == 0){
        $('#dop_inputs_listing').append('<div class="dop_inputs"  data-id="'+data['dop_inputs_id']+'"><span>'+data['name_ru']+'</span><span class="button_del_dop_inputs status_del" data-id="'+data['dop_inputs_id']+'">X</span></div>');
    }else{
        alert('Данное поле уже содержится в списке по этой услуге');
    }
}
function alerting(data){
    alert(data['html']);
}
// удаление dop_inputs из услуги
$(document).on('click', '.button_del_dop_inputs.status_del', function(event) {
    var id_dop_imput = $(this).attr('data-id');
    $(this).parent().remove();
    $.post('', {
        AJAX:'delete_dop_input_from_services',
        id_dop_imput:id_dop_imput,
        usl_id: $('#tbl_edit_usl .lili.checked').attr('data-id')
    }, function(data, textStatus, xhr) {
        // управление js из php
        if(data['function'] !== undefined){
            window[data['function']](data);
        }

        if(data['response']!="OK"){
            alert('Что-то пошло не так');
        }
    },'json');
    /* Act on the event */
});

//////////////////////////
//  выбор услуги
//////////////////////////
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
        if($(this).parent().hasClass('calc_icon')){
            var text  = 'Удаление раздела удалит из калькуляторов опцию расчета данного нанесения! Производите удаление только если вы уверенны в правильности данного шага!';
            if(confirm(text)){
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
        }else{
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
    // первым параметром перелаём название функции отвечающей за отправку запроса AJAX
    // вторым параметром передаём объект к которому добавляется класс saved (класс подсветки)
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
            // php возвращает json в виде {"response":"OK"}
            // если ответ OK - снимаем класс saved
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
        // меняем название
        $('#tbl_edit_usl .lili.checked span.name_text').html($('#edit_block_usluga form .edit_info input[name="name"]').val());
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


//////////////////////////////////////////////////////
//  General function for generate dialog windo START
//////////////////////////////////////////////////////
// показать окно
function show_dialog_and_send_POST_window(html,title,height){
    height_window = height || 'auto';
    title = title || '*** Название окна ***';
    var buttons = new Array();
    buttons.push({
        text: 'OK',
        click: function() {
            var serialize = $('#dialog_gen_window_form form').serialize();
            
            $('#general_form_for_create_product .pad:hidden').remove();
            $.post('', serialize, function(data, textStatus, xhr) {
                // если из PHP было передано название какой либо функции
                // выполняем её
                if(data['function'] !== undefined){
                    window[data['function']](data);
                }

                if(data['response']=='show_new_window'){
                    title = data['title'];// для генерации окна всегда должен передаваться title
                    show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
                }else{
                    // подчищаем за собой
                    $('#dialog_gen_window_form').html('');
                    $('#dialog_gen_window_form').dialog( "destroy" );
                    // тут можно расположить какие либо действия в зависимости от ответа
                    // с сервера                    
                }
            },'json');                      
        }
    });

    if($('#dialog_gen_window_form').length==0){
        $('body').append('<div id="dialog_gen_window_form"></div>');
    }
    $('#dialog_gen_window_form').html(html);
    $('#dialog_gen_window_form').dialog({
          width: '1000',
          height: height_window,
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });

}


//////////////////////////////////////////////////////
//  General function for generate dialog windo END
//////////////////////////////////////////////////////