<script type="text/javascript">
$(document).on('dblclick', '#chenge_name_company', function(event) {
    var name_window = $(this).parent().prev().html();
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '';
    var type = $(this).attr('data-editType');
    var name = $(this).attr('name');
    if(type != "textarea"){
        html = '<input type="'+ type +'" name="'+name+'" onkeyup="$(\'#chenge_name_company\').html($(this).val());" value="'+$(this).html()+'">';
    }else{
        html = '<textarea type="'+ type +'" name="'+name+'"> '+$(this).text()+'</textarea>';
    }    
    var id_row = ($(this).attr('data-idRow') != "")?$(this).attr('data-idRow'):'none';
    var tbl = ($(this).attr('data-tableName') != "")?$(this).attr('data-tableName'):'none';
    var buttons = $(this).attr('data-button-name-window');
    buttons = (buttons!="")?buttons:'';
    new_html_modal_window(html,name_window,buttons,'chenge_name_company', id_row, tbl);
    $('.html_modal_window_body input:nth-of-type(1)').focus();
});


// создание нового адреса
$(document).on('click', '.button_add_new_row.adres_row', function(event) {
    var prepend_s = $(this).parent().parent();
    
    var html = '<img src="http://www.os1.ru/os/skins/images/img_design/preloader.gif" >'; 
    var tbl = 'CLIENT_ADRES_TBL';
    new_html_modal_window(html,'Добавить адрес','greate','add_new_adress_row', '', tbl);
    $.post('', {ajax_standart_window: 'new_adress_row' }, function(data, textStatus, xhr) {
        $('.html_modal_window_body').html(data);
    });
    //добавляем поле для передачи parent_id
    $(".html_modal_window form").append('<input type="hidden" name="parent_id" value="'+  $('#chenge_name_company').attr('data-idrow') +'" >');

    $('.ok_bw, .send_bw, .greate_bw, .save_bw').click( function(event) {
    //отправляем, если это создание новой строки адреса
    if($('.html_modal_window form input[name="ajax_standart_window"]').val()=="add_new_adress_row"){
        var str = $('.html_modal_window form').serialize();
        $.post('', str, function(data, textStatus, xhr) {
            var re = /^[0-9]*$/;
            if (!isNaN(data)){//если вернулось число
                //скорее всего вернулся id
                //копируем соседнее поле
                var html2 = '<tr><td>Адрес</td><td><div class="edit_row edit_adress_row del_text" data-tableName="CLIENT_ADRES_TBL" data-editType="input" data-adress-id="'+data+'" data-button-name-window="save">'+get_adress_info_form()+'</div></td></tr>';
                prepend_s.before(html2);
                $('#bg_modal_window,.html_modal_window').remove();
            }else{
                //сообщаем, что что-то пошло не так
                new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
            }
        });   

    }

    
});

});

// редактирование адреса
$(document).on('dblclick', '.edit_adress_row', function(event) {
    var name_window = $(this).parent().prev().html();
    var element = $(this);
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '<img src="http://www.os1.ru/os/skins/images/img_design/preloader.gif" >';  
    var id_row = ($(this).attr('data-adress-id') != "")?$(this).attr('data-adress-id'):'none';
    var tbl = 'CLIENT_ADRES_TBL';
    var buttons = $(this).attr('data-button-name-window');
    //вызываем окно редактирования адреса
    new_html_modal_window(html,name_window,buttons,'edit_adress_row', id_row, tbl);
    //получаем контент для редактирования адреса
    $.post('', {ajax_standart_window: 'get_adres', id_row: id_row }, function(data, textStatus, xhr) {
        $('.html_modal_window_body').html(data);        
    });  
    //при клике на сохранить...возвращаем всё, что нередактировали на страницу
    $('.green_bw.save_bw').click(function() {
        element.html(get_adress_info_form());
    });
    
});
$(document).on('click','.type_adress',function(event) {    
    $('.html_modal_window form input[name="adress_type"]').val($(this).attr('data-type'));
    $('.type_adress').removeClass('checked');
    $(this).addClass('checked');
});
$(document).on('click','.city_fast',function(event) {
    $(this).parent().next().find('input').val($(this).attr('data-city'));
});

//выбираем информацию об адресе из полей формы и выдаем её в строке 
function get_adress_info_form(){
    var cont_str = "";
        var ind = $('.html_modal_window_body input[name="postal_code"]').val();     
        cont_str+= ind;

        var city = $('.html_modal_window_body input[name="city"]').val();
        if(city!=""){cont_str+=", ";}
        cont_str+= city;

        var street = $('.html_modal_window_body input[name="street"]').val();
        if(street!=""){cont_str+=", ";}
        cont_str+= street;

        var house_number = $('.html_modal_window_body input[name="house_number"]').val();
        house_number = (house_number!="")?'дом № '+house_number:'';
        if(house_number!=""){cont_str+=", ";}
        cont_str+= house_number;

        var korpus = $('.html_modal_window_body input[name="korpus"]').val();
        korpus = (korpus!="")?'кор. '+korpus:'';
        if(korpus!=""){cont_str+=", ";}
        cont_str+= korpus;

        var office = $('.html_modal_window_body input[name="office"]').val();  
        office = (office!="")?'офис '+office:'';
        if(office!=""){cont_str+=", ";} 
        cont_str+= office;

        var liter = $('.html_modal_window_body input[name="liter"]').val();  
        liter = (liter!="")?'литера '+liter:'';
        if(liter!=""){cont_str+=", ";} 
        cont_str+=liter;


        var bilding = $('.html_modal_window_body input[name="bilding"]').val();
        bilding = (bilding!="")?'строение '+bilding:'';
        cont_str+=bilding; 

        cont_str += '<span class="adress_note">' + $('.html_modal_window_body textarea').val() + '</span>';
        return cont_str;
}

// показать/скрыть кнопку удалить
$(document).on('mouseover', '.del_text', function(event) {
    // наведение
    var af = $(this).offset();
    var wi = $(this).innerWidth()+af.left-1;
    if($('#del_text').length==0){
        $(this).parent().append('<div id="del_text"></div>');
    }else{
        $('#del_text').remove(); 
        $(this).parent().append('<div id="del_text"></div>');   
    }    
    $('#del_text').css({'top':af.top,'left':wi});
});
//удаление строки с информацией
$(document).on('click', '#del_text', function(event) {
    var del_div = $(this).prev();
    var id_row = (del_div.attr('data-adress-id') != "")?del_div.attr('data-adress-id'):'none';
    var tbl = (del_div.attr('data-tablename') != "")?del_div.attr('data-tablename'):'none';
    // console.log(tbl+'');
    new_html_modal_window('Вы уверены, что хотите удалить данную запись? ','Подтвердите действие','ok','','','');
   
    $('.ok_bw').click(function(event) {
        $.post('', {
            ajax_standart_window: 'delete_adress_row',
            id_row : id_row ,
            tbl : tbl
        }, function(data, textStatus, xhr) {
            if(data=='OK'){
                $('#bg_modal_window,.html_modal_window').remove();
                del_div.parent().parent().remove();
            }
        });
    });

});

$(document).ready(function() {
    // if(os_confirm('вы уверены')){alert('уверен')}else{'не уверен'}
});

</script>
<style type="text/css">
.edit_row{ padding: 5px; cursor: default;float: left;}
.edit_row:hover{ background: rgb(255, 228, 228); }  
.adress_note{ float: left; padding-top: 10px; color: rgb(176, 175, 175)}

#edit-client-adres,#edit-client-adres table{ width: 100%}
#edit-client-adres .table_2 input{width: 40px; padding-left: 5px; padding-right: 5px}
#edit-client-adres tr td{padding: 5px }
#edit-client-adres input, #edit-client-adres textarea{ width: 98%;
padding-top: 5px;
padding-bottom: 5px;
padding-left: 1%;
padding-right: 1%;border: 1px solid rgb(213, 213, 213);
}
#edit-client-adres tr td:nth-of-type(1){ width: 60px; padding-right: 5px; text-align: right; color: #AEAEAE; font-size: 14px }
#edit-client-adres tr td table:nth-of-type(1) tr td:nth-of-type(1)
{ width: 55px; padding-right: 5px; text-align: right; color: #AEAEAE; font-size: 14px }
#edit-client-adres tr td table tr td { width: auto;}
#edit-client-adres .type_adress,.city_fast{ cursor:default;padding: 5px 7px;border: 1px solid rgb(213, 213, 213);margin-left: 10px;
color: #C2C2C2;}
#edit-client-adres .type_adress:active,.city_fast:active{ background-color: grey}
#edit-client-adres .type_adress.checked{ padding: 5px 7px; background-color: #9dbe8e; color: #000;border: 1px solid #9dbe8e;}
#del_text{ height:22px; opacity: 0.7; width:36px; float: right; top:5px;right: 20px; position: absolute; background: no-repeat url(http://ssl.gstatic.com/mail/sprites/general_black-16bf964ab5b51c4b7462e4429bfa7fe8.png) 7px -411px; background-color: #BBBBBB;
cursor: default; opacity: 0.4}
.c_r{position:relative; padding-right:48px;float: left;
width: 100%;}
#del_text:hover{ opacity: 1}
</style>


<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td>
                            <div class="edit_row edit" id="chenge_name_company" name="company" data-name="company" data-editType="text" data-button-name-window="save" data-idRow="<?php echo $client_id; ?>" data-tableName='CLIENTS_TBL'><?php echo trim($client['company']); ?></div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td>
                            <div style="padding:5px">В разработке</div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td>
                            <div style="padding:5px">В разработке</div>
                        </td>
                    </tr>
                	<?php echo $client_address_s; ?>                    
                    <tr>
                        <td></td>
                        <td>
                            <div class="button_add_new_row adres_row">Добавить адрес</div>
                        </td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row phone_row">добавить телефон</div></td>
                    </tr>
                </table>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row other_row">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>