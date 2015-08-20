// radio button

function create_datepicker_for_variant_cont(){
	$('#date_1').datetimepicker({
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
	 	format:'d.m.Y',
	 	
	});

	$('#date_2').datetimepicker({
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
	 	format:'d.m.Y',
	 	
	});
}




$(document).on('change', '.one_row_for_this_type input[type="radio"]', function(event) {
	$(this).parent().find('div.pad').hide('fast');
	$(this).parent().find('div.pad').find('input').prop('checked','false');
	if($(this).next().next().next().hasClass('pad')){
		$(this).next().next().next().show('fast');
	}
	
});

// checkbox
$(document).on('change', '.one_row_for_this_type input[type="checkbox"]', function(event) {
	if($(this).prop('checked')){
		if($(this).next().next().next().hasClass('pad')){
			// показываем доп меню если есть
			$(this).next().next().next().show('fast');
		}	
	}else{
		if($(this).next().next().next().hasClass('pad')){
			//скрываем доп меню если есть
			$(this).next().next().next().hide('fast');
			// отменяем выбранное
			$(this).next().next().next().find('input').prop('checked',false);
			// скрываем доп внутренние опции
			$(this).next().next().next().find('.pad').hide('fast');
		}	
	}	
});
$(document).on('click', '.answer_table .delete_user_val', function(event) {
	$(this).parent().parent().remove();
});

// кнопка добавления вариантов
$(document).on('click', '.btn_add_var', function(event) {
	var obj = $(this).parent().prev().clone();
	var get_class_clone_obj = obj.attr('data-type');
	var num_var = $('.'+get_class_clone_obj).length;
	var num_var_new = num_var+1;
	// меняем название для опознания варианта
	obj.find('strong').html($('.'+get_class_clone_obj).find('strong').html()+' '+num_var);

	

	obj.find('input[type="checkbox"],input[type="radio"],input[type="text"],select').each(function(index, el) {
		var new_name = $(this).attr('name')+'_'+num_var_new;
		var new_id = $(this).attr('id')+'_'+num_var_new;

		$(this).attr('id',new_id);//.attr('name',new_name);
		var str = $(this).attr('name');
		str = str.replace('['+(num_var-1)+']', '['+num_var+']');
		console.log(str);
		$(this).attr('name',str)
		
		//console.log($(this).next().get(0).tagName);
		if($(this).next().get(0).tagName == 'LABEL'){
			$(this).next().attr('for',new_id);
		}
	});
	$(this).parent().before(obj);
});

// кнопка снятия выделения со всего списка
$(document).on('click', '.cancel_selection', function(event) {
	var obj = $(this).parent().prev();
	obj.find('.pad').css({'display':'none'});
	obj.find('input').each(function(index, el) {
		$(this).prop('checked', false);
	});

});

// кнопка добавить своё значение
$(document).on('click', '.btn_add_val', function(event) {
	var obj = $(this).parent().prev();
	var obj_input = obj.find('input').first();

	var type_new_obj = obj_input.attr('type');
	var name = obj_input.attr('name');

	var input_text = '<input class="change_form_inputs" type="text" value="" id="eded_input"><br>';
	var append = '<input type="'+type_new_obj+'" name="'+name+'">'+input_text;
	// console.log();
	$(this).parent().prev().append(append);
	$('#eded_input').prev().click();
	$('#eded_input').focus().removeAttr('id');
});

// сохранение значения input в value списка
$(document).on('keyup', '.change_form_inputs', function(event) {
	$(this).prev().val($(this).val()); 
	/* Act on the event */
});


//отправка запроса на получение формы
$(document).on('click','#create_new_position',function(e) {	
	// if(e.keyCode == 192){//ESC	
		$.post('', {
			AJAX:'to_chose_the_type_product_form'
		}, function(data, textStatus, xhr) {
			// вызов окна выбора типа продукции
			show_dialog_chosen_type_product(data);			
			//alert(data);
		});
	// }
});

// создание диалогового окна с выбором типа продукции
function show_dialog_chosen_type_product(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
				// если ответ от серера положительный
				if(data['response']=="OK"){
					// отфильтровываем каталожный товар
					if(data['type']=="cat"){
						// ОБРАБОТКА КАТАЛОЖНОГО ТОВАРА
						show_dialog_and_send_POST_window(Base64.decode(data['html']),'Введите № артикула',$(window).height());

					}else{
						// ОБРАБОТКА НЕКАТАЛОЖНОГО ТОВАРА
						// вызов формы заведения не каталожного товара и генерации вариантов
						show_dialog(Base64.decode(data['html']));
						// объявляем работу датапикера для полей даты
						create_datepicker_for_variant_cont();
					}
				}else{
					alert('Что-то пошло не так');
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
          title : 'Выберите тип продукции',
          autoOpen : true,
          buttons: buttons          
        });

}


// создание диалогового окна с формой заведения вариантов расчёта для снаба
function show_dialog(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var moderate = 0;
	    	$('#general_form_for_create_product .one_row_for_this_type').each(function(index, el) {
	    		
	    		if($(this).attr('data-moderate')=='1'){
	    			// moderate = 0;
	    			var moder = false;
	    			// проверка  nput[type="checkbox"],input[type="radio"
	    			$(this).find('input[type="checkbox"],input[type="radio"]').each(function(index, el) {		    				
		    			if($(this).prop('checked')){		    					
		    				moder = true;
		    			}
	    			});
	    			// проверка input[type="text"]
	    			$(this).find('input[type="text"]').each(function(index, el) {		    				
		    			if($(this).val()!=''){
		    				moder = true;
		    			}
	    			});

	    			if(moder===true){
	    				$(this).css({'border':'none'});	    				
	    			}else{
	    				$(this).css({'border':'1px solid red'});
	    				moderate = 1;
	    			}
	    			// console.log(moder);
	    			
	    		
	    		}	

	    	});
	    	console.log(moderate);
	    	if(moderate==0){
		    	$('#general_form_for_create_product .pad:hidden').remove();
		    	$.post('', $('#general_form_for_create_product form').serialize(), function(data, textStatus, xhr) {
					// alert(data);
					// $('#dialog_gen_window_form').html(data)
					show_dialog_var(data);
					$('#general_form_for_create_product').remove();
				});
			}else{
				alert('Исправьте ошибки заполнения');
			}
			//general_form_for_create_product();	    	
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
          title : 'Заполните форму',
          autoOpen : true,
          buttons: buttons          
        });

}


// создание диалогового окна с выбором заведённых вариантов
function show_dialog_var(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
				
				//$('#dialog_gen_window_form').append(data);
				if(data=="OK"){
					location.reload();
				}else{
					alert(data);
				}
			});
				    	
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
          title : 'Заполните форму',
          autoOpen : true,
          buttons: buttons          
        });

}

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
	if(data['response']=='show_new_window'){
		title = data['title'];// для генерации окна всегда должен передаваться title
		show_dialog_and_send_POST_window(Base64.decode(data['html']),title);
	}
}


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

$(document).on('change keyup', '#add_new_articul_in_rt', function(event) {
	var art = $(this).val();
	$('#information_block_of_articul').addClass('loader');
	$.post('', {
		AJAX: 'check_exists_articul',
		art: art
	}, function(data, textStatus, xhr) {
		if(data['response']=="OK"){
			$('#information_block_of_articul').removeClass('loader').html(Base64.decode(data['html']));
		}else{
			alert('Что-то пошло не так');
		}
	},'json');
});

$(document).on('click', '#choose_one_of_several_articles tr td', function(event) {
	var art_id = $(this).parent().attr('data-art_id');
	var art = $(this).parent().attr('data-art');
	var art_name = $(this).parent().attr('data-art_name');
	// подставляем выбранные значения
	$('#information_block_of_articul input[name="art"]').val(art);
	$('#information_block_of_articul input[name="art_id"]').val(art_id);
	$('#information_block_of_articul input[name="art_name"]').val(art_name);

	$('#choose_one_of_several_articles tr.checked').removeClass('checked');
	$(this).parent().addClass('checked');

});

$(document).on('click', '#choose_the_size tr td', function(event) {
	var size = $(this).parent().find('td').eq(0).html();
	var price = $(this).parent().find('td').eq(1).html();

	// подставляем выбранные значения

	$('#dialog_gen_window_form input[name="chosen_size"]').val(size);
	$('#dialog_gen_window_form input[name="price_out"]').val(price);

	$('#choose_the_size tr.checked').removeClass('checked');
	$(this).parent().addClass('checked');

});
//	15610440.34