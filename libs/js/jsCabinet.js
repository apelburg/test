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
		replace_query_row_obj(obj);
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

//////////////////////////
//	ДОП/ТЕХ ИНФО
//////////////////////////
$(document).on('click', '.dop_teh_info', function(event) {
	var query_num = Number($(this).attr('data-quewy_num'));
	var order_num = Number($(this).attr('data-order_num'));
	var order_num_user = Number($(this).attr('data-order_num_user'));
	var position_id = Number($(this).attr('data-id'));
	var position_item = Number($(this).attr('data-position_item'));
	var title = 'Заказ ' + order_num_user
				+' / позиция ' + position_item +' / '
				+ $(this).parent().parent().find('.art_and_name').html()
				+' - техническая дополнительная информация';

	$.post('', {
		AJAX: 'get_dop_tex_info',
		query_num:query_num,
		order_num:order_num,
		position_id:position_id
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){			
			show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
		}else{
			alert('Что-то пошло не так');	
		}
	},'json');
});




