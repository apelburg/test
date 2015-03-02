<script type="text/javascript">
$(document).on('click', '#chenge_name_company', function(event) {
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

$(document).on('click', '.edit_adress_row', function(event) {
    var name_window = $(this).parent().prev().html();
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '<img src="http://www.os1.ru/os/skins/images/img_design/preloader.gif" >';  
    var id_row = ($(this).attr('data-adress-id') != "")?$(this).attr('data-adress-id'):'none';
    var tbl = 'CLIENT_ADRES_TBL';
    var buttons = $(this).attr('data-button-name-window');
    new_html_modal_window(html,name_window,buttons,'edit_adress_row', id_row, tbl);
    $.post('', {ajax_standart_window: 'get_adres', id_row: id_row }, function(data, textStatus, xhr) {
        $('.html_modal_window_body').html(data);
        $('.type_adress').click(function(event) {
            $('.type_adress').removeClass('checked');
            $(this).addClass('checked');
        });
        $('.city_fast').click(function(event) {
            $(this).parent().next().children('input').val($(this).attr('data-city'));
        });
    });  
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

`
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
                            <div class="button_add_new_row">Добавить адрес</div>
                        </td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row">добавить телефон</div></td>
                    </tr>
                </table>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>