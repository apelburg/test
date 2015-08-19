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

// показать / скрыть каталожные позиции 
$(document).on('click', '.click_me_and_show_catalog', function(event) {
	$(this).parent().parent().find('tr.cat_8').toggle('fast');
});

//календарь
$(document).ready(function() {
	$('.payment_date').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.parent().parent().attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_payment_date',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				console.log(data);
			});
		},
	 	format:'d.m.Y',
	 	
	});


	// дата сдачи заказа
	$('.date_of_delivery_of_the_order').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.parent().parent().attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_date_of_delivery_of_the_order',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);

			},'json');
		},
	 	format:'d.m.Y',	 	
	});

	// ожидаемая дата прихода продукции на склад

	$('.waits_products_div input').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.parent().attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_waits_products_div_input',
				row_id: row_id, // cab_main_rows_id
				date: date // введённое значение
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
		},
	 	format:'d.m.Y',	 	
	});
	

	// дата утверждения макета
	$('.approval_date').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.attr('data-id');
			var dop_data_id = $input.parent().parent().attr('data-cab_dop_data_id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_approval_date',
				row_id: row_id,
				date: date,
				dop_data_id:dop_data_id
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);

			},'json');
		},
	 	format:'d.m.Y',	 	
	});
});


// Изменение глобального статуса ЗАКАЗА / ПРЕДЗАКАЗА
$(document).on('change', '.choose_statuslist_order_and_paperwork', function(event) {
	var row_id = $(this).parent().parent().attr('data-id'); //main_rows_id
	var value = $(this).val();
	var obj = $(this).parent().parent();
	// отправляем запрос
	$.post('', {
		AJAX:'choose_statuslist_order_and_paperwork',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		replace_query_row_obj(obj);
	},'json');
});

// Изменение глобального статуса ЗАКАЗА / ПРЕДЗАКАЗА
$(document).on('change', '.choose_statuslist_sklad', function(event) {
	var row_id = $(this).attr('data-id'); //main_rows_id
	var value = $(this).val();
	// отправляем запрос
	$.post('', {
		AJAX:'choose_statuslist_sklad',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});



// запуск в работу заказа
$(document).on('click', '.in_operation', function(event) {
	var row_id = $(this).parent().parent().attr('data-id'); //main_rows_id
	var obj = $(this).parent().parent();
	// отправляем запрос
	$.post('', {
		AJAX:'choose_statuslist_order_and_paperwork',
		row_id:row_id,
		value:'in_work'
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		replace_query_row_obj(obj);
	},'json');
});



// меняем статус снабжения к позиции
/*
	СНАБЖЕНИЕ РАБОТАЕТ ПО ПОЗИЦИЯМ
*/
$(document).on('change', '.choose_statuslist_snab', function(event) {
	var row_id = $(this).attr('data-id'); //main_rows_id
	var value = $(this).val();
	
	// пишем исключение показа и скрытия ожидаемой даты поставки в зависимости от выбранного статуса
	if( value == "waits_products"){
		$(this).parent().find('.waits_products_div').show('fast');
	}else{
		$(this).parent().find('.waits_products_div').hide('fast');
		$(this).parent().parent().find('.waits_products_div input').val('');
	}

	// отправляем запрос
	$.post('', {
		AJAX:'change_status_snab',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// стандартный обработчик ответа AJAX
function standard_response_handler(data){
	if(data['function'] !== undefined){ // вызов функции... если требуется
		window[data['function']](data);
	}
	if(data['response'] != "OK"){ // вывод при ошибке
		console.log(data);
	}
	if(data['error']  !== undefined){ // на случай предусмотренной ошибки из PHP
		alert(data['error']);
	}
}

// редактирование срока по ДС
$(document).on('keyup', '.deadline', function(event) {
	var value = $(this).html();
	var row_id = $(this).parent().attr('data-id');
	$.post('', {
		AJAX:'change_deadline_value',
		row_id:row_id,
		value:value

	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
	});
	check_loading_ajax();
});
// сохраняем поле ОПЛАЧЕНО
$(document).on('change','.buch_status_select select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().attr('data-id');
		var value = $(this).val();
		var obj = $(this).parent().parent();
		window_preload_add();
		$.post('', {
			AJAX:'buch_status_select',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
			replace_query_row_obj(obj);
		});
	});

	// схраняем статус заказа
	$(document).on('change','.select_global_status select',function(){
		// записываем id строки услуги
		var row_id = $(this).parent().parent().attr('data-id');
		var value = $(this).val();
		var obj = $(this).parent().parent();
		window_preload_add();
		$.post('', {
			AJAX:'select_global_status',
			row_id:row_id,
			value:value
		}, function(data, textStatus, xhr) {
			console.log(data);
			replace_query_row_obj(obj);
		});
	});



// свернуть/развернуть строку ЗАПРОСА
$(document).on('click','#cabinet_general_content .cabinett_row_hide',function() {	
	if($(this).hasClass('show')){
		$(this).parent().attr('rowspan','2').parent().next().show();
		$(this).removeClass('show');
	}else{
		$(this).parent().attr('rowspan','1').parent().next().hide();
		$(this).addClass('show');
	}	
});

// свернуть/развернуть строку ЗАКАЗА
$(document).on('click','#cabinet_general_content .cabinett_row_hide_orders',function() {	
	if($(this).hasClass('show')){
		// запоминаем значение rowspan
		var rowspan = Number($(this).parent().attr('data-rowspan'));

		$(this).parent().attr('rowspan',rowspan);

		// скрываем все строки
		obj = $(this).parent().parent().next('tr');
		for (var i = 0; i < rowspan-1; i++) {
			obj.show();
			obj = obj.next('tr');
		};

		$(this).removeClass('show');
	}else{
		// запоминаем значение rowspan
		var rowspan = Number($(this).parent().attr('rowspan'));
		// ставим rowspan 1, сохраняем заначение в тег
		$(this).parent().attr('rowspan','1').attr('data-rowspan',rowspan);
		// скрываем все строки
		obj = $(this).parent().parent().next('tr');
		for (var i = 0; i < rowspan-1; i++) {
			obj.hide();
			obj = obj.next('tr');
			console.log(obj.next('tr').html());
		};

		$(this).addClass('show');
	}	
});







//////////////////////////
//	БУХГАЛТЕРИЯ START
//////////////////////////
$(document).on('keyup','.invoice_num:focus',function(){
	// записываем id строки позиции
	var row_id = $(this).parent().attr('data-id');
	var value = $(this).html();
	
	$.post('', {
		AJAX:'change_invoce_num',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})


$(document).on('keyup','.payment_status_span:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().parent().attr('data-id');
	var value = $(this).html();

	// подсчитываем процент оплаты
	var all_summ = Number($(this).parent().next().find('span').html());
	var percent = Number($(this).html())*100/all_summ;	
	$(this).parent().prev().find('span').html(percent.toFixed(2));

	$.post('', {
		AJAX:'change_payment_status',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})

$(document).on('keyup','.number_payment_list:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().attr('data-id');
	var value = $(this).html();

	$.post('', {
		AJAX:'number_payment_list',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})

// номер TTH
$(document).on('keyup','.change_ttn_number:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().attr('data-id');
	var value = $(this).html();

	$.post('', {
		AJAX:'change_ttn_number',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})
// отгружено
$(document).on('keyup','.change_delivery_tir:focus',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().parent().attr('data-id');
	var value = Number($(this).html());
	var max_tir = Number($(this).parent().next().html());
	if(max_tir<value){
		$(this).html(max_tir);
		value = max_tir;
	}

	$.post('', {
		AJAX:'change_delivery_tir',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
})
//////////////////////////
//	БУХГАЛТЕРИЯ END
//////////////////////////

//////////////////////////
//	СНАБ START
//////////////////////////
// схраняем статус снаб
$(document).on('change','.status_snab select',function(){
	// записываем id строки услуги
	var row_id = $(this).parent().parent().attr('data-id');
	var value = $(this).val();
	
	$.post('', {
		AJAX:'change_status_snab',
		row_id:row_id,
		value:value
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
});
//////////////////////////
//	СНАБ END
//////////////////////////
	

//////////////////////////
//	АДМИН start
//////////////////////////

// запрос - прикрепить / сменить менеджера
$(document).on('click', '.attach_the_manager', function(event) {
	var client_id = Number($(this).parent().parent().find('.attach_the_client').attr('data-id'));
	var manager_id = Number($(this).attr('data-id'));
	var rt_list_id = Number($(this).parent().parent().attr('data-id'));
	$.post('', {
		AJAX:'get_a_list_of_managers_to_be_attached_to_the_request',
		client_id:client_id,
		manager_id:manager_id,
		rt_list_id:rt_list_id
	}, function(data, textStatus, xhr) {
		show_dialog_and_send_POST_window(data,'Выбрать менеджера');
	});
});


// запрос - прикрепить / сменить клиента
// вывод спика для выбора клиента, который будет прикреплён к запросу
$(document).on('click', '.attach_the_client', function(event) {
	var manager_id = Number($(this).parent().parent().find('.attach_the_manager').attr('data-id'));
	var client_id = Number($(this).attr('data-id'));
	var rt_list_id = Number($(this).parent().parent().attr('data-id'));
	var obj = $(this).parent().parent();
	$.post('', {
		AJAX:'get_a_list_of_clients_to_be_attached_to_the_request',
		client_id:client_id,
		manager_id:manager_id,
		rt_list_id:rt_list_id
	}, function(data, textStatus, xhr) {
		show_dialog_and_send_POST_window(data,'Выбрать клиента',750);
		// replace_query_row_obj(obj);
		location.reload();
	});
});


// отработка клика по таблице выбора менеджера для прикрепления к запросу
$(document).on('click', '#chose_manager_tbl table tr td', function(event) {
	if($(this).html()!=''){
		$('#chose_manager_tbl tr td').removeClass('checked');
		$(this).addClass('checked');
		// rt_list_id_
		var rt_list_id = $(this).parent().parent().parent().parent().find('input[name$="rt_list_id"]').val();
		$('#rt_list_id_'+rt_list_id + ' .attach_the_manager').html($(this).html()).attr('data-id',$(this).attr('data-id'));
		var manager_id = $(this).attr('data-id');//alert($(this).parent().parent().parent().parent().html());
		$(this).parent().parent().parent().parent().find('input[name$="manager_id"]').val(manager_id);
	}
});

// отработка клика по таблице выбора менеджера для прикрепления к запросу
$(document).on('click', '#chose_client_tbl table tr td', function(event) {
	if($(this).html()!=''){
		$('#chose_client_tbl tr td').removeClass('checked');
		$(this).addClass('checked');
		// rt_list_id_
		var rt_list_id = $(this).parent().parent().parent().parent().find('input[name$="rt_list_id"]').val();
		$('#rt_list_id_'+rt_list_id + ' .attach_the_client').html($(this).html()).attr('data-id',$(this).attr('data-id'));
		var manager_id = $(this).attr('data-id');//alert($(this).parent().parent().parent().parent().html());
		$(this).parent().parent().parent().parent().find('input[name$="client_id"]').val(manager_id);
	}
});

//////////////////////////
//	АДМИН end
//////////////////////////

function change_attache_manager(data){
	var id_row = '#rt_list_id_'+data['rt_list_id'];
	$(id_row).find('.attach_the_manager').attr('data-id',data['manager_id']).html(data['manager_name']);
	if ($('#dialog_gen_window_form').length) {
		$('#dialog_gen_window_form').remove();
	};
}

//////////////////////////////////////////////////////
//	General function for generate dialog windo START
//////////////////////////////////////////////////////
// показать окно № 1
function show_dialog_and_send_POST_window(html,title,height,width){
	height_window = height || 'auto';
	width = width || '1000';
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
          width: width,
          height: height_window,
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });

}


// простое диалоговое окно с кнопкой закрыть
function show_simple_dialog_window(html,title,height,width){
	height_window = height || 'auto';
	width = width || '1000';
	title = title || '*** Название окна ***';
	var buttons = new Array();
	buttons.push({
	    text: 'Закрыть',
	    click: function() {
			// подчищаем за собой
			$('#dialog_gen_window_form').html('');
			$('#dialog_gen_window_form').dialog( "destroy" );
	    }
	});

	if($('#dialog_gen_window_form').length==0){
		$('body').append('<div id="dialog_gen_window_form"></div>');
	}

	$('#dialog_gen_window_form').html(html);
	$('#dialog_gen_window_form').dialog({
          width: width,
          height: height_window,
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });
}

// показать окно № 2  
// используется в случае, когда нужно 2 одновременно открытых окна
function show_dialog_and_send_POST_window_2(html,title,height,width){
	height_window = height || 'auto';
	width = width || '1000';
	title = title || '*** Название окна ***';
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form2 form').serialize();
	    	
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
		    	// если из PHP было передано название какой либо функции
		    	// выполняем её
		    	if(data['function'] !== undefined){
		    		window[data['function']](data);
		    	}

				if(data['response']=='show_new_window'){
					title = data['title'];// для генерации окна всегда должен передаваться title
					show_dialog_and_send_POST_window_2(Base64.decode(data['html']),title);
				}else{
					// подчищаем за собой
					$('#dialog_gen_window_form2').html('');
					$('#dialog_gen_window_form2').dialog( "destroy" );
					// тут можно расположить какие либо действия в зависимости от ответа
					// с сервера					
				}
			},'json');				    	
	    }
	});

	if($('#dialog_gen_window_form2').length==0){
		$('body').append('<div id="dialog_gen_window_form2"></div>');
	}
	$('#dialog_gen_window_form2').html(html);
	$('#dialog_gen_window_form2').dialog({
          width: width,
          height: height_window,
          modal: true,
          title : title,
          autoOpen : true,
          buttons: buttons          
        });

}


//////////////////////////////////////////////////////
//	General function for generate dialog windo END
//////////////////////////////////////////////////////



//////////////////////////
//	МЕНЕДЖЕР start
//////////////////////////

// принять запрос в обработку
$(document).on('click', '.take_in_operation', function(event) {
	var obj = $(this);
	var obj_row = $(this).parent().parent();
	var rt_list_id = $(this).parent().parent().attr('data-id');
	$.post('', {
		AJAX: 'take_in_operation',
			rt_list_id:rt_list_id
		}, function(data, textStatus, xhr) {
			if(data['response'] != 'OK'){
				alert(data);
			}else{
				replace_query_row_obj(obj_row);
			}
	},'json');
});


// взять в работу запрос
$(document).on('click', '.get_in_work', function(event) {
	var obj = $(this);
	if(Number($(this).parent().parent().find('.attach_the_client').attr('data-id')) == 0){
		alert('Сначала укажите клиента.');
	}else{
		var rt_list_id = $(this).parent().parent().attr('data-id');
		$.post('', {
			AJAX: 'get_in_work',
			rt_list_id:rt_list_id
		}, function(data, textStatus, xhr) {
			if(data['response'] != 'OK'){
				alert(data);
			}else{
				// показываем что сменили статус и удаляем строку из дом модели
				obj.html('в работе').delay(3000).parent().parent().addClass('remove_this_row').next().addClass('remove_this_row').parent().parent().find('.remove_this_row').remove();
				
			}
		},'json');
	}
});




//////////////////////////
//	МЕНЕДЖЕР end
//////////////////////////
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

// запрос на обновление строки
// с отредактированными данными.... 
// сделано ВРЕМЕННО в целях экономии времени на проверку и смену всех данных в строке пооочерёдно
function replace_query_row_obj(obj){
	window_preload_add();
	var os__rt_list_id = obj.attr('data-id');
	// запоминаем rowspan
	// console.log('65654546');

	var rowspan = obj.find('.show_hide').attr('rowspan');
	// console.log(obj.find('.show_hide').attr('rowspan'));
	// console.log(obj);
	// console.log(rowspan);
	$.post('', {
		AJAX: 'replace_query_row',
		os__rt_list_id: os__rt_list_id,
		rowspan:rowspan
	}, function(data, textStatus, xhr) {
		if(data['response'] == 'OK'){
			
			// console.log(Base64.decode(data['html'])+' ++++++++++++++++++' + obj.html());
			obj.html(Base64.decode(data['html']));
			window_preload_del();
		}else{
			alert('что-то пошло не так');
			window_preload_del();
		}
	},'json');
}



function del_id_chose_supplier_id(data){
	$('#chose_supplier_id').removeAttr('id');
}
//////////////////////////
//	НАЗНАЧЕНИЕ ПОСТАВЩИКА 	
//////////////////////////
$(document).on('click', '.change_supplier', function(event) {
	$(this).attr('id', 'chose_supplier_id');
	chose_supplier($(this));
});

function chose_supplier(obj){
	$.post('', {
		AJAX:'chose_supplier',
		id_dop_data: $('#chose_supplier_id').attr('data-id_dop_data'),
		already_chosen: $('#chose_supplier_id').attr('data-id'),
		suppliers_name:$('#chose_supplier_id').html()
	}, function(data, textStatus, xhr) {
		show_dialog_and_send_POST_window(data,'Выбирите поставщика',$(window).height()/100*90);
	});
}

$(document).on('click', '#chose_supplier_tbl tr td', function(event) {
	if($(this).hasClass('checked')){
		$(this).removeClass('checked');
	}else{
		$(this).addClass('checked');
	}

	var arr_id = new Array();
	var arr_name = new Array();
	$('#chose_supplier_tbl tr td.checked').each(function(index, el) {
		arr_id.push($(this).attr('data-id'));
		arr_name.push($(this).html());
	});

	var str_id = arr_id.join(',');
	var str_name = arr_name.join(', ');
	console.log(str_id);

	$('#chose_supplier_tbl').parent().find('input[name="dop_data_id"]').val($('#chose_supplier_id').parent().attr('data-id'));
	$('#chose_supplier_tbl').parent().find('input[name="suppliers_id"]').val(str_id);
	$('#chose_supplier_tbl').parent().find('input[name="suppliers_name"]').val(str_name);

	$('#chose_supplier_id').html(str_name);
	$('#chose_supplier_id').attr('data-id',str_id);

});

//////////////////////////
//	ДОП/ТЕХ ИНФО
//////////////////////////
$(document).on('click', '.dop_teh_info', function(event) {
	var query_num = Number($(this).attr('data-query_num'));
	var order_num = Number($(this).attr('data-order_num'));
	var order_num_user = $(this).attr('data-order_num_user');
	var position_id = Number($(this).attr('data-id'));
	var position_item = Number($(this).attr('data-position_item'));
	var id_dop_data = $(this).attr('data-id_dop_data');
	var title = 'Заказ ' + order_num_user
				+' / позиция ' + position_item +' / '
				+ $(this).parent().parent().find('.art_and_name').html()
				+' - техническая дополнительная информация';

	$.post('', {
		AJAX: 'get_dop_tex_info',
		query_num:query_num,
		order_num:order_num,
		position_id:position_id,
		id_dop_data:id_dop_data
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){			
			show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
		}else{
			alert('Что-то пошло не так');	
		}
	},'json');
});

// редактирование статуса пленки / клише
$(document).on('change', '.statuslist_film_photos', function(event) {
	var row_id = $(this).attr('data-id');
	var value = $(this).val();
	$.post('', {
		AJAX:'choose_statuslist_film_photos',
		value:value,
		row_id:row_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	/* Act on the event */
});


// редактирование/ЗАПОЛНЕНИЕ dop_inputs
$(document).on('click', '#services_listing_each .lili', function(event) {
	if(!$(this).hasClass('no_active')){
		// console.log($(this).attr('data-uslugi_id'));
		// id услуги
		var uslugi_id = $(this).attr('data-uslugi_id');
		// id строки 
		var dop_usluga_id = $(this).attr('data-dop_usluga_id');
		

		$('#services_listing_each .lili').removeClass('checked');
		$(this).addClass('checked');
		window_preload_add();
		
		$.post('', {
			AJAX:'get_dop_inputs_for_services',
			uslugi_id: uslugi_id,
			dop_usluga_id: dop_usluga_id
		}, function(data, textStatus, xhr) {
			window_preload_del();
			if(data['response']=="OK"){
				console.log(Base64.decode(data['html']));
				$('#content_dop_inputs_and_tz').html(Base64.decode(data['html']));
			}else{
				alert('Что-то плошло не так...');
			}
		},'json');
	}else{
		console.log($(this).attr('data-uslugi_id'));
		var uslugi_id = $(this).attr('data-uslugi_id');
		var dop_usluga_id = $(this).attr('data-dop_usluga_id');
		$('#services_listing_each .lili').removeClass('checked');
		$(this).addClass('checked');

		$('#content_dop_inputs_and_tz').html('Услуга была отключена из работы<br> для её редактирования необходимо снова включить её в финансовой детализации');
	}
});

// редактирование поля резерв в доп тех инфо
$(document).on('keyup','#dialog_gen_window_form .rezerv_info_input', function(event) {
	
	var cab_dop_data_id = $(this).attr('data-cab_dop_data_id');

	$.post('', {
		AJAX:'save_rezerv_info',
		cab_dop_data_id: cab_dop_data_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование поля ТЗ по услуге к позиции заказа
$(document).on('keyup','#dialog_gen_window_form .save_tz', function(event) {
	
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');

	$.post('', {
		AJAX:'save_tz_info',
		cab_dop_usluga_id: cab_dop_usluga_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование поля ТЗ по услуге к позиции заказа
$(document).on('keyup','#dialog_gen_window_form .save_logotip', function(event) {
	
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');

	$.post('', {
		AJAX:'save_logotip_info',
		cab_dop_usluga_id: cab_dop_usluga_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});
// сохранения поля путь к макету
$(document).on('keyup','#dialog_gen_window_form .save_the_url_for_layout', function(event) {
	
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');

	$.post('', {
		AJAX:'save_the_url_for_layout',
		cab_dop_usluga_id: cab_dop_usluga_id,
		text : $(this).val()
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});

// редактирование dop_inputs
$(document).on('keyup','#dialog_gen_window_form .dop_inputs', function(event) {
	var name_en = $(this).attr('name');
	var val = $(this).val();
	
	var Json = $('#dop_input_json').html();
	var json_object = JSON.parse(Json);

	json_object[name_en] = val;
	if(val.trim()==""){
		delete json_object[name_en];
	}

	Json = JSON.stringify(json_object);

	$('#dop_input_json').html(Json)
	var cab_dop_usluga_id = $('#services_listing_each .lili.checked').attr('data-dop_usluga_id');
	console.log(cab_dop_usluga_id);
	$.post('', {
		AJAX:'save_dop_inputs',
		cab_dop_usluga_id: cab_dop_usluga_id,
		Json : Json
	}, function(data, textStatus, xhr) {
		if(data['response']!="OK"){
			alert('Что-то пошло не так');
		}
	},'json');
	check_loading_ajax();
});



///////////////////////////////////////////////
//	статус сохранения отредактированного поля
///////////////////////////////////////////////
function check_loading_ajax(){
		window.l++;
		console.log(jQuery.active);
		if(jQuery.active>0){
			if($('#alert_saving_status').length==0){
				$('body').append('<div style="'
					+'position:fixed;'
					+'float:left;'
					+'font-family: arial,sans-serif;'
					+'left:50%; '
					+'z-index:110; '
					+'top:100px; '
					+'margin-left:-100px; '
					+'background-color:#F9EDBE;'
					+'border:1px solid #F0C36D; '
					+'padding:7px 15px; '
					+'font-size:12px" id="alert_saving_status"><div id="ll">Данные сохраняются...</div><div id="lll" style="text-align:center"></div><div id="lll1"><div id="lll2" style="width:0%;background: #F0C36D; height:5px; border:0"></div></div></div>');	
				$('#alert_saving_status').stop(true, true).fadeIn('fast');
			}else{
				$('#alert_saving_status').fadeIn('fast');			
			}
			var p = jQuery.active;
			var q = window.l / 100;
			var per = Math.ceil((100-p/q));
			$('#lll').html(per +' %');
			$('#lll2').width(per+'%');
			setTimeout(check_loading_ajax, 300);
			return false;
		}else{
			
			$('#ll').html('Данные успешно сохранены.')
			$('#lll').html('100 %');
			$('#lll2').width('100%');		
			$('#alert_saving_status').delay(1000).animate({opacity:0},700,function(){$(this).remove()});
			
			//setTimeout($('#alert_saving_status').fadeOut('fast').remove(), 3000)	
			window.l = 0;
			return true;	
		}
	};
	$(document).ready(function(){
	window.l = 0;
	window.onbeforeunload = function () {return ((check_loading_ajax()==false) ? "Измененные данные не сохранены. Закрыть страницу?" : null);}
	});


////////////////////////////////
//	детализация по списку услуг
////////////////////////////////

$(document).on('click', '#general_panel_orders_tbl tr td.price_for_the_position', function(event) {
	var dop_data_id = $(this).attr('data-cab_dop_data_id');
	var id = $(this).attr('data-id');
	var order_num_user = $(this).attr('data-order_num_user');
	var order_num = $(this).attr('data-order_num');
	var order_id = $(this).attr('data-order_id');


	$.post('', {
		AJAX: 'get_a_detailed_article_on_the_price_of_positions',
		dop_data_id: dop_data_id,
		id:id,
		order_num:order_num,
		order_id:order_id
	}, function(data, textStatus, xhr) {
		if(data['function'] !== undefined){ // на всякий
			window[data['function']](data);
		}

		if(data['response'] == "OK"){
			title = 'Заказ № '+order_num_user+' - финансовые расчёты';
			show_dialog_and_send_POST_window_2(Base64.decode(data['html']),title,$(window).height(),$(window).width());
		}else{
			alert('Что-то пошло не так');
		}
	},'json');
});

// включение/отключение услуг
$(document).on('click', '#a_detailed_article_on_the_price_of_positions .on_of', function(event) {
	var id = $(this).attr('data-id');
	var obj = $(this);
	var val = 0;

	if ($(this).hasClass('minus')) {
		val = 1;
		$(this).removeClass('minus').html('+');
		$(this).parent().parent().removeClass('no_calc');
	}else{
		val = 0;
		$(this).addClass('minus').html('-');
		$(this).parent().parent().addClass('no_calc');
	}

	recalculate_a_detailed_article_on_the_price_of_positions(); // пересчитываем таблицу
	$.post('', {
		AJAX: 'change_service_on_of',
		id: id,
		val: val
	}, function(data, textStatus, xhr) {
		if(data['function'] !== undefined){ // на всякий
			window[data['function']](data);
		}
		if(data['response'] == "OK"){
			
		}else{
			alert('Что-то пошло не так');
		}
	},'json');
});

///////////////////////////////////////////
//	пересчёт окна финансовые расчёты
//////////////////////////////////////////
function recalculate_a_detailed_article_on_the_price_of_positions(){
	if ($('#a_detailed_article_on_the_price_of_positions tr').length) {
		///////////////////////////////////////////////////////
		//	объявляем переменные по стоимости заказа
		///////////////////////////////////////////////////////
		var Order_price_in = 0; // стоимость входящая
		var Order_price_out = 0; // стоимость исходящая
		var Order_price_pribl = 0; // прибыль 
		var Order_price_in_postfactum = 0; // стоимость входащаяя постфактум

		///////////////////////////////////////////////////////
		//	объявляем переменные по стоимости позиции
		///////////////////////////////////////////////////////
		var Position_price_in = 0; // стоимость входящая
		var Position_price_out = 0; // стоимость исходящая
		var Position_price_pribl = 0; // прибыль 
		var Position_price_in_postfactum = 0; // стоимость входащаяя постфактум

		///////////////////////////////////////////////////////
		//	объявляем переменные по стоимости товаров и услуг
		///////////////////////////////////////////////////////
		var Service_price_in = 0; // стоимость входящая
		var Service_price_out = 0; // стоимость исходящая
		var Service_price_pribl = 0; // прибыль 
		var Service_price_in_postfactum = 0; // стоимость входащаяя постфактум

		//////////////////////////////////////////////////////////
		//	флаги подсветки непредусмотренных потерь по стоимости
		///////////////////////////////////////////////////////////
		var order_not_provided = 0; 
		var position_not_provided = 0;
		var service_not_provided = 0;

		$('#a_detailed_article_on_the_price_of_positions tr').each(function(index, el) {
			// перебираем все строки, которые относятся к товарам и услугам, а так же к ИТОГО по позициям
			if(!$(this).hasClass('no_calc')){
				if (!$(this).hasClass('itogo_for_position')){


					service_not_provided = 0;
					///////////////////////////////////////
					//	перебираем строки товаров и услуг
					////////////////////////////////////////
					
					Service_price_in = Number($(this).find('.service_price_in').html()); // стоимость входящая
					Service_price_out = Number($(this).find('.service_price_out').html()); // стоимость исходящая
					Service_price_pribl = Number($(this).find('.service_price_pribl').html()); // прибыль 
					Service_price_in_postfactum = Number($(this).find('.service_price_in_postfactum').html()); // стоимость входащаяя постфактум
					// console.log(index);
					// console.log(Service_price_in);
					// console.log(Service_price_out);
					// console.log(Service_price_pribl);
					// console.log(Service_price_in_postfactum);
					// console.log('-------------------');
					////////////////////////////////////////////////////////////////
					//	суммируем стоимость услуги или товара к стоимости позиции
					////////////////////////////////////////////////////////////////
					Position_price_in += Service_price_in; // стоимость входящая
					Position_price_out += Service_price_out; // стоимость исходящая
					Position_price_pribl += Service_price_pribl; // прибыль 
					Position_price_in_postfactum += Service_price_in_postfactum; // стоимость входащаяя постфактум


					// НЕ предусмотренная услуга
					if ($(this).hasClass('not_provided')) {
						// устанавливаем флаги
						order_not_provided = 1;
						position_not_provided = 1;
						service_not_provided = 1;
					}
				}
				// console.log(order_not_provided);
				// если достигли итоговой стоимости по позиции - добавляем стоимость позиции к стоимости заказа и 
				//обнуляем переменные содержащие стоимость позиции для общёта следующих позиции
				if ($(this).hasClass('itogo_for_position')){
					///////////////////////////////////////////
					//	суммируем стоимость позиции к заказу
					///////////////////////////////////////////
					Order_price_in += Position_price_in; // стоимость входящая
					Order_price_out += Position_price_out; // стоимость исходящая
					Order_price_pribl += Position_price_pribl; // прибыль 
					Order_price_in_postfactum += Position_price_in_postfactum; // стоимость входащаяя постфактум
					// console.log(Position_price_in);
					// console.log(Position_price_out);
					// console.log(Position_price_pribl);
					// console.log(Position_price_in_postfactum);
					// console.log($(this).find('.position_price_in_postfaktum').length);
					// console.log('-------------------');

					//////////////////////////
					// правим стоимость позиции	
					//////////////////////////
					$(this).find('.position_price_in').html(Position_price_in);// стоимость входящая
					$(this).find('.position_price_out').html(Position_price_out);// стоимость исходящая
					$(this).find('.position_price_pribl').html(Position_price_pribl);// прибыль 
					$(this).find('.position_price_in_postfaktum').html(Position_price_in_postfactum);// стоимость входащаяя постфактум

					//////////////////////////
					//	обнуляем переменные со стоимостью позиции
					//////////////////////////
					Position_price_in = 0; // стоимость входящая
					Position_price_out = 0; // стоимость исходящая
					Position_price_pribl = 0; // прибыль 
					Position_price_in_postfactum = 0; // стоимость входащаяя постфактум

					// при наличии в расчёте непредусмотренных услуг подсвечиваем Итого позиции
					// в противном случае убираем подсветку
					if(position_not_provided){
						$(this).find('.td_shine').each(function(index, el) {
							if(!$(this).hasClass('added_postfactum_class')){
								$(this).addClass('added_postfactum_class');
							}				
						});
					}else{
						$(this).find('.added_postfactum_class').removeClass('added_postfactum_class');
					}
					// обнуляем флаг позиции 
					position_not_provided = 0;



				}


			}
		});

		// правим стоимость заказа
		$('#itogo_order .order_price_in').html(Order_price_in);// стоимость входящая
		$('#itogo_order .order_price_out').html(Order_price_out);// стоимость исходящая
		$('#itogo_order .order_price_pribl').html(Order_price_pribl + (Order_price_in - Order_price_in_postfactum));// прибыль
		$('#itogo_order .order_price_in_postfactum').html(Order_price_in_postfactum);// стоимость входащаяя постфактум
		$('#itogo_order .added_postfactum_class .minus span').html(Order_price_in - Order_price_in_postfactum); // разница постфактум

		// при наличии в расчёте непредусмотренных услуг подсвечиваем Итого заказа
		// в противном случае убираем подсветку
		if(order_not_provided){
			$('#itogo_order .td_shine').each(function(index, el) {
				if(!$(this).hasClass('added_postfactum_class')){
					$(this).addClass('added_postfactum_class');
				}				
			});
		}else{
			$('#itogo_order .added_postfactum_class').removeClass('added_postfactum_class');
		}
		//////////////////////////
		//	определяем разницу постфактум
		//////////////////////////
		if(Order_price_in_postfactum != Order_price_in){
			$('#itogo_order .minus').html('<span>'+(Order_price_in - Order_price_in_postfactum)+'</span>р');
		}else{
			$('#itogo_order .minus').html('');
		}

	}
}

// добавление услуги в финансовые расчёты
$(document).on('click', '.add_service', function(event) {
	var id_dop_data = $(this).attr('data-id_dop_data');
	$(this).attr('id','liuhjadbwefbkelwqfeqwfqw');
	$.post('', 
		{
			AJAX:"get_uslugi_list_Database_Html_steep_1",
			id_dop_data:id_dop_data			
		}, function(data, textStatus, xhr) {

		show_dialog_and_send_POST_window(data,'Шаг 1: Выберите услугу', 800);
		
	});
});

function add_new_usluga_end(data){
	var id_row = $('#liuhjadbwefbkelwqfeqwfqw').attr('data-rowspan_id');
	$('#'+id_row+' td').eq(0).attr('rowspan',(Number($('#'+id_row+' td').eq(1).attr('rowspan'))+1));
	$('#'+id_row+' td').eq(1).attr('rowspan',(Number($('#'+id_row+' td').eq(1).attr('rowspan'))+1))
	$('#liuhjadbwefbkelwqfeqwfqw').parent().parent().before(Base64.decode(data['html']));
	$('#liuhjadbwefbkelwqfeqwfqw').removeAttr('id');
	recalculate_a_detailed_article_on_the_price_of_positions();
}


//отработка выбора услуги в диалоговом окне
$(document).on('click', '#dialog_gen_window_form form .may_bee_checked', function(event) {
	// выделяем выбранную услугу
	$('#dialog_gen_window_form form .may_bee_checked').removeClass('checked');
	$(this).addClass('checked');
	var id = $(this).attr('data-id');
	var service_name = $(this).find('.name_text').html();

	// группа исполнителей допущенная к изменению статусов
	var performer = $(this).attr('data-performer');
	
	// console.log(quantity);
	// $('#dialog_gen_window_form form input[name="quantity"]').val(quantity);
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	$('#dialog_gen_window_form form input[name="performer"]').val(performer);

	$('#dialog_gen_window_form form input[name="service_name"]').val(service_name);
	// $('#dialog_gen_window_form form input[name="dop_row_id"]').val(dop_row_id);
});

// обработчик изменения статуса услуги
$(document).on('change', '.get_statuslist_uslugi', function(event) {
	var id_row = $(this).attr('data-id');
	var value = $(this).val();
	$.post('', {
		AJAX: 'choose_service_status',
		id_row: id_row,
		value:value 
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
});

////////////////////////////////////////////////
//	функции вызываемые из PHP  --- start ---  //
////////////////////////////////////////////////

// вывод сообщения из PHP
function php_message(data){
	alert(data.text);
}
// перезагрузка окна
function window_reload(data) {
	location.reload();
}


//////////////////////////
//	ПР-ВО
//////////////////////////
$(document).on('click', '.show_dialog_tz_for_production', function(event) {
	$.post('', {
		AJAX: 'get_dialog_tz_for_production',
		row_id:$(this).attr('data-id')
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){
			title = data['title'];// для генерации окна всегда должен передаваться title
			show_simple_dialog_window(Base64.decode(data['html']),title);
		}else{
			alert('Упс. Что-то пошло не так...');
		}
	},'json');
});


//////////////////////////
//	запуск услуги в работу 
//////////////////////////
$(document).on('click', '.start_statuslist_uslugi', function(event) {
	var id = $(this).attr('data-id');
	$.post('', {
		AJAX: 'start_services_in_processed',
		id:id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
});


// выставление даты работы над услугой
// дата сдачи заказа
jQuery(document).ready(function($) {
	$('.show_backlight .calendar_date_work').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		closeOnDateSelect:true,
		onChangeDateTime: function(dp,$input){// событие выбора даты
			// получение данных для отправки на сервер
			var row_id = $input.attr('data-id');
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				AJAX: 'change_date_work_of_service',
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
			check_loading_ajax();
		},
	 	format:'d.m.Y',	 	
	});
});

// взять в работу услугу
$(document).on('click', '.get_in_work_service', function(event) {
	// сохраняем ID строки
	var row_id = $(this).attr('data-service_id');
	// id пользователя, взяшего услугу в работу
	var user_id = $(this).attr('data_user_ID');

	// меняем html
	$(this).replaceWith('<span data-id="'+user_id+'">'+$(this).attr('data-user_name')+'</span>');
	

	$.post('', {
		AJAX: 'get_in_work_service',
		row_id:row_id,
		user_id:user_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
});

// назначить исполнителя услуги
$(document).on('change', '.production_userlist', function(event) {
	var row_id = $(this).attr('data-row_id');
	var user_id = $(this).val();
	$.post('', {
		AJAX: 'get_in_work_service',
		row_id:row_id,
		user_id:user_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
	},'json');
	check_loading_ajax();
});

//////////////////////////////////////////
//	меняем % готовности услуги --- start
//////////////////////////////////////////
$(document).on('keyup', '.percentage_of_readiness', function(event) {
	 timing_save_input('change_percentage_of_readiness',$(this))
});
function change_percentage_of_readiness(obj){// на вход принимает object input
    var row_id = obj.attr('data-service_id');
    $.post('', {
        AJAX:'change_percentage_of_readiness',
        row_id:row_id,
        value:obj.html()
    }, function(data, textStatus, xhr) {
    	standard_response_handler(data);
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

//////////////////////////////////////////
//	меняем % готовности услуги --- end
//////////////////////////////////////////

// нажатие кнопки макет утверждён
$(document).on('click', '.set_approval_date', function(event) {
	// получаем текущую дату
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1;
	var curr_year = d.getFullYear();

	var date  = curr_date + "." + curr_month + "." + curr_year;
	// alert(date);

	// меняем html
	$(this).replaceWith('<span class="greyText">'+date+'</span>');

	// получаем id позиции
	var row_id = $(this).attr('data-id');
	// получаем dop_data_id
	var dop_data_id = $(this).parent().parent().attr('data-cab_dop_data_id');

	// закрываем экран от дальнейшего редактирования
	window_preload_add();
	// отправляем запрос
	$.post('', {
		AJAX: 'change_approval_date',
		row_id: row_id,
		date: date,
		dop_data_id: dop_data_id
	}, function(data, textStatus, xhr) {
		standard_response_handler(data);
		// при удачном ответе перезагружаем окно
		if (data['response']=="OK"){
			location.reload();
		}else{
			alert('Что-то пошло не так.');
			window_preload_del();
		}
	},'json');
});