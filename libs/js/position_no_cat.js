$(document).on('click', '#all_variants_menu_pol .variant_name', function(event) {
	$('.variant_name').removeClass('checked');
	$(this).addClass('checked');
	var table_id = $(this).attr('data-cont_id');
	$('#variant_of_snab .variant_content_table').css({'display':'none'});
	$('#'+table_id).css({'display':'block'});
});

// клик по первому варианту при загрузке страницы
$(document).ready(function() {
	var obj = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id') + ' .show_table tr:nth-of-type(2) td:nth-of-type(2)');

	obj.click();
	$('#inform_for_variant_number').html(obj.html());
});

// клик по варианту расчёта
$(document).on('click', '#variant_of_snab table.show_table tr td', function(event) {
	// скрываем все блоки с расширенной информацие о варианте
	$('.variant_info').css({'display':'none'});
	


	// выделяем выбранный вариант
	$(this).parent().parent().find('.checked').removeClass('checked');
	$(this).parent().parent().find('td').css({'background':'none'});
	$(this).parent().find('td').css({'background':'#c7c8ca'});

	// получаем id строки варианта
	var id_row = $(this).parent().addClass('checked').attr('data-id');

	// показываем расширенную информацию по выбранному варианту
	$('#variant_info_'+id_row).css({'display':'block'});

	// трасляция подробной информации в правом верхнем углу экрана
	$('#inform_for_variant').html($('#variant_info_'+id_row+ ' .table.inform_for_variant').parent().html());

	$('#inform_for_variant_number').html($(this).parent().find('td').eq(1).html());
});


$(document).on('click', '.add_usl', function(event) {
	$.post('', 
		{
			AJAX:"get_uslugi_list_Database_Html"
		}, function(data, textStatus, xhr) {
		show_dialog_and_send_POST_window(data,'Выберите услугу');
	});
	
});

//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').css({'background':'none'});
	$(this).css({'background':'grey'});


	var id = $(this).attr('data-id');
	var dop_row_id = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked').attr('data-id');
	// получим тираж
	var quantity = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html();
	console.log(quantity);
	$('#dialog_gen_window_form form input[name="quantity"]').val(quantity);
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	$('#dialog_gen_window_form form input[name="dop_row_id"]').val(dop_row_id);
});



// проверка на ввод букв
function proverka(input) { 
    var value = input.value; 
    var rep = /[-\.;":'a-zA-Zа-яА-Я]/; 
    if (rep.test(value)) { 
        value = value.replace(rep, ''); 
        input.value = value; 
    } 
} 


// округление
function round_s(int_r){
	return Math.ceil((int_r)*100)/100;
}


// показать окно
function show_dialog_and_send_POST_window(html,title){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
				if(data['response']=='show_new_window'){
					title = data['title'];
					show_dialog_and_send_POST_window(data['html']);
				}else{
					$('#dialog_gen_window_form').dialog( "destroy" );
					console.log(data['response']);
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
          height: 'auto',
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });

}

// КАЛЬКУЛЯЦИЯ

// пересчет ИТОГО таблицы с ценами по варианту
function recalculate_table_price_Itogo(){
	// находим таблицу расчёта текущего (активного) варианта
	var itogo_price_in = 0;
	var itogo_price_out = 0;
	var itogo_pribl = 0;
	var itogo_percent = 0;
	var n = 0;
	$('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('tr.calculate').each(function(index, el) {
			itogo_price_in += Number($(this).find('td:nth-of-type(2) span').html());// входящая цена		
			itogo_percent += Number($(this).find('td:nth-of-type(3) span').html());// процент		
			itogo_price_out += Number($(this).find('td:nth-of-type(4) span').html());	
		n++;	
	});

	itogo_price_out = round_s(itogo_price_out);
	itogo_price_in = round_s(itogo_price_in);
	itogo_percent = round_s(itogo_percent/n);
	itogo_pribl = round_s(itogo_price_out-itogo_price_in);

	// пишем в итого

	$('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('tr.variant_calc_itogo').attr('id','adsfadsf65456asfd');
	
	$('#adsfadsf65456asfd').find('td:nth-of-type(2) span').html(itogo_price_in);
	$('#adsfadsf65456asfd').find('td:nth-of-type(3) span').html(itogo_percent);
	$('#adsfadsf65456asfd').find('td:nth-of-type(4) span').html(itogo_price_out);
	$('#adsfadsf65456asfd').find('td:nth-of-type(5) span').html(itogo_pribl);

	$('#adsfadsf65456asfd').removeAttr('id');

	// меняем значения цен в строке списка
	// цена входящая
	$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked td:nth-of-type(4) span').html(itogo_price_in);
	// цена минимальная исходящая
	$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked td:nth-of-type(4) span').html(itogo_price_in);


}

