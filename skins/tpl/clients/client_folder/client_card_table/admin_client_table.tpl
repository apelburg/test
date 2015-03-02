<script type="text/javascript">
$(document).on('click', '#chenge_name_company', function(event) {
    var name_window = $(this).parent().prev().html();
    name_window = 'Редактировать '+ name_window.toLowerCase();
    var html = '';
    var type = $(this).attr('data-editType');
    var name = $(this).attr('name');
    if(type != "textarea"){
        html = '<input type="'+ type +'" name="'+name+'" value="'+$(this).html()+'">';
    }else{
        html = '<textarea type="'+ type +'" name="'+name+'"> '+$(this).text()+'</textarea>';
    }  
    var buttons = $(this).attr('data-button-name-window');
    buttons = (buttons!="")?buttons:'';
    new_html_modal_window(html,name_window,buttons,'chenge_name_company');
    $('.html_modal_window_body input:nth-of-type(1)').focus();

});

$(document).on('click', '.ok_bw, .send_bw, .greate_bw, .save_bw', function(event) {
    var str = $('.html_modal_window form').serialize();
    //console.log(str);
    $.post('', str, function(data, textStatus, xhr) {
        //console.log(data);
    });
});



</script>
<style type="text/css">
.edit_row{ padding: 5px; cursor: default;float: left;}
.edit_row:hover{ background: rgb(255, 228, 228); }  
</style>

<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td>
                            <div class="edit_row" id="chenge_name_company" name="company" data-name="company" data-editType="text" data-button-name-window="save"><?php echo trim($client['company']); ?></div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td>
                            <div class="edit_row">В разработке</div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td>
                            <div class="edit_row">В разработке</div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Адрес офиса</td>
                    	<td>
                            <div class="edit_row"><?php echo $client['addres']; ?></div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Адрес доставки</td>
                    	<td>                            
                            <div class="edit_row" data-editType="textarea"><?php echo $client['delivery_address']; ?></div>
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