// показать загрузку траницы
function window_preload_add(){
	if(!$('#preloader_window_block').length){
		var object = $('<div/>').attr('id','preloader_window_block'); object.appendTo('body')
	}	
}
// скрыть загрузку страницы
function window_preload_del(){
	if($('#preloader_window_block').length){
		$('#preloader_window_block').remove();
	}	
}




///////////// КОД ОПЩИЙ ДЛЯ КАРТОЧКИ КАТАЛОГА И НЕ КАТАЛОГА


$(document).on('click', '.add_usl', function(event) {
	$.post('', 
		{
			AJAX:"get_uslugi_list_Database_Html"
		}, function(data, textStatus, xhr) {
		show_dialog_and_send_POST_window(data,'Выберите услугу', 800);
		
	});
	
});

//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');
	$(this).addClass('checked');

	var id,dop_row_id,quantity;
	// для каталожной и некаталожной карточки продукции основные данные ищем по разному
	if($('#dialog_gen_window_form form input[name="type_product"]').val() != 'cat'){
		id = $(this).attr('data-id');
		dop_row_id = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked').attr('data-id');
		// получим тираж
		quantity = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html();
	}else{
		var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
		id = $(this).attr('data-id');
		//console.log($(id_variant).attr('data-id'));
		dop_row_id = $('#variants_name .variant_name.checked ').attr('data-id');
		// получим тираж
		quantity = $(id_variant+' .tirage_var').val();
	}

	// console.log(quantity);
	$('#dialog_gen_window_form form input[name="quantity"]').val(quantity);
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	$('#dialog_gen_window_form form input[name="dop_row_id"]').val(dop_row_id);
});

// округление
function round_s(int_r){
	return Math.ceil((int_r)*100)/100;
}


// показать окно
function show_dialog_and_send_POST_window(html,title,height){
	height_window = height || 'auto';
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
				if(data['response']=='show_new_window'){
					title = data['title'];// для генерации окна всегда должен передаваться title
					show_dialog_and_send_POST_window(data['html'],title);
				}else{
					$('#dialog_gen_window_form').dialog( "destroy" );

					if(data['name'] == 'chose_supplier_end'){
						$('#chose_supplier_id').removeAttr('id');

					}

					//### добавляем услугу в карточке НЕ каталожного товара
					if(data['name'] == 'add_uslugu_no_cat'){ // если нужно добавить услугу
						
						// ADD USLUGA start *** старт ***
						var added=1; // флаг, который сигнализирует, что HTML добавлен
						var add_html = Base64.decode(data['html']); // html новой услуги
						var parent_id_new_usl = Number(data['parent_id']); // parent_id овой услуги
						
						// поищем по всем группам уже существующих услуг
						// если найдется подходящая - добаляем html
						$('.calkulate_table:visible .group_usl_name').each(function(index, el) {
							
							if(Number($(el).attr('data-usl_id'))==parent_id_new_usl){
								$(el).after(add_html);
								added=0;
							}
						}); 

						// если услуга не найдена добаляем в конец
						if(added){
							$('#variant_info_' +$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table .variant_calc_itogo').prev().prev().before(add_html);
						}
						// пересчитываем Итого
						recalculate_table_price_Itogo()
						// ADD USLUGA end *** конец ***
						
					}
					
					//### добавляем услугу в карточке каталожного товара
					if(data['name'] == 'add_uslugu_cat'){
						
						// ADD USLUGA start *** старт ***
						var added=1; // флаг, который сигнализирует, что HTML добавлен
						var add_html = Base64.decode(data['html']); // html новой услуги
						var parent_id_new_usl = Number(data['parent_id']); // parent_id овой услуги
						
						console.log('154');
						// поищем по всем группам уже существующих услуг
						// если найдется подходящая - добаляем html
						$('.calkulate_table:visible .group_usl_name').each(function(index, el) {
							console.log('654');
							if(Number($(el).attr('data-usl_id'))==parent_id_new_usl){
								$(el).after(add_html);
								added=0;
							}
						}); 

						// если услуга не найдена добаляем в конец
						if(added){
							$('.calkulate_table:visible .variant_calc_itogo').prev().prev().before(add_html);
						}
						// пересчитываем Итого
						recalculate_table_price_Itogo()
						// ADD USLUGA end *** конец ***
						
					}
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

// Удаление услуги
$(document).on('click', '.del_row_variants', function(event) {
	/* Act on the event */
	console.log('клик на удаление услуги');
	console.log($(this).parent().parent().prev().find('th').length);
	console.log($(this).parent().parent().next().find('th').length);
	//если это последняя услуга в своём разделе, удаляем имя раздела
	if($(this).parent().parent().next().find('th').length){
		if($(this).parent().parent().prev().find('th').length){
			$(this).parent().parent().prev().remove();
		}
	}

	var dop_uslugi_id = $(this).parent().parent().attr('data-dop_uslugi_id');
	$(this).parent().parent().remove();
	// подсчёт ИТОГО
	$.post('', 
		{
			AJAX: 'delete_usl_of_variant',
			uslugi_id: dop_uslugi_id
		}, function(data, textStatus, xhr) {
		console.log(data);
	});


	recalculate_table_price_Itogo();	
	
});

$(document).on('click', '.calc_icon_chose', function(event) {
	// снимаем выделение с остальных услуг
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');

	alert('хотим калькулятор '+$(this).find('.name_text').html()+', type = '+$(this).attr('data-type')+', id = '+$(this).attr('data-id'));
});