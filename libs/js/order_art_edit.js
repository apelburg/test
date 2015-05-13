
$(document).ready(function() {
	/* отработка верхней части */
	//календарь для даты отгрузки
	// $('#datepicker1,#datepicker2').datetimepicker({
	// 	minDate:new Date(),
	// 	// disabledDates:['07.05.2015'],
	// 	timepicker:false,
	//  	dayOfWeekStart: 1,
	//  	onGenerate:function( ct ){
	// 		$(this).find('.xdsoft_date.xdsoft_weekend')
	// 			.addClass('xdsoft_disabled');
	// 		$(this).find('.xdsoft_date');
	// 	},
	// 	onSelectDate: function(ct){
	// 		//$('#datepicker1').removeAttr('readonly').removeClass('input_disabled');
	// 		$('#btn_date_var').click();
	// 	},
	//  	format:'d.m.Y',
	 	
	// });
	// // время для даты отгрузки
	// $('#timepicker1,#timepicker2').datetimepicker({
	//  datepicker:false,
	//  format:'H:i',
	//  // minTime:'9:00',
	//  // maxTime:'21:00'
	//  allowTimes:[
	// 	  '09:00', '10:00', '11:00', '12:00','13:00', '14:00','15:00', 
	// 	  '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'
	//  ]
	// });	

	/* отработака индивидуальной части для каждого варианта*/
	//************************************************/
	//                ВРЕМЯ И ДАТА ОТГРУЗКИ
	//************************************************/
	// ДАТА
	create_datepicker_for_variant_cont();

	// ВРЕМЯ
	create_timepicker_for_variant_cont();
});

function destroy_datetimepicker_for_variant_cont(){
	$('#edit_variants_content .datepicker2').datetimepicker('destroy');
	$('#edit_variants_content .timepicker2').datetimepicker('destroy');
}

function create_timepicker_for_variant_cont(){
	$('#edit_variants_content .timepicker2').datetimepicker({
	 datepicker:false,
	 format:'H:i',
	 closeOnDateSelect:true,
	 onChangeDateTime: function(dp,$input){// событие выбора даты
			// меняем html
			var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
			$('#'+id_variant+' .fddtime_rd2').val('');
			$('#'+id_variant+' .btn_var_std[name="std"]').removeClass('checked');

			// получение данных для отправки на сервер
			var id = $('#variants_name .variant_name.checked ').attr('data-id');
			var row_id = $('#claim_number').attr('data-order');	
			var time = $input.val()+':00';

			// alert($input.attr('class'));
			$.post('', {
				global_change: 'AJAX',
				change_name: 'change_variante_shipping_time',
				id: id,
				row_id: row_id,
				time: time
			}, function(data, textStatus, xhr) {
				/*optional stuff to do after success */
			},"json");
		},
	 // minTime:'9:00',
	 // maxTime:'21:00'
	 allowTimes:[
		  '00:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00',
		  '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'
	 ]
	});	
}


function create_datepicker_for_variant_cont(){
	$('#edit_variants_content .datepicker2').datetimepicker({
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
			// меняем html
			var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
			$('#'+id_variant+' .timepicker2').show().val(''); // показать поле время
			$('#'+id_variant+' .fddtime_rd2').val('');
			$('#'+id_variant+' .btn_var_std[name="std"]').removeClass('checked');		


			// получение данных для отправки на сервер
			var id = $('#variants_name .variant_name.checked ').attr('data-id');
			var row_id = $('#claim_number').attr('data-order');	
			var date = $input.val();

			//alert($input.attr('class'));
			$.post('', {
				global_change: 'AJAX',
				change_name: 'change_variante_shipping_date',
				id: id,
				row_id: row_id,
				date: date
			}, function(data, textStatus, xhr) {
				/*optional stuff to do after success */
			},"json");
		},
	 	format:'d.m.Y',
	 	
	});
}



$(document).on('click','#new_variant',function(){
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	$.post('',{
		global_change: 'AJAX',
		change_name: 'new_variant',
		id:id,
		row_id:row_id
		
	}, function(data, textStatus, xhr) {
		if(data['response']=='1'){
			// клонируем html вкладки текущего расчета
			var menu_li = $('#variants_name .variant_name.checked ').clone();
			// ставим название и на всякий подчищаем архивный класс, если он есть
			menu_li.html(data['num_row_for_name']).removeClass('show_archive');		
			// получаем id текущего пблока расчёта
			var id_div = menu_li.attr('data-cont_id');
			// меняем id для для работы вкладки с новым блоком 
			menu_li.attr('data-cont_id','variant_content_block_'+data['num_row']);
			// убираем класс "выбрано" со всех вкладок
			$('#variants_name .variant_name').removeClass('checked');
			// вставляем html
			$('#variants_name .variant_name:last-of-type').after(menu_li);
			// post запрос для названия вкладки и получения id для склонированного контента

			// клонируем html текущего расчёта со всеми данными
			var div_html = $('#'+id_div).clone();
			// id на новый
			div_html.attr('id','variant_content_block_'+data['num_row']);
			// подчищаем архивный класс, если есть
			div_html.removeClass('archiv_opacity');
			// скрываем все видимые блоки расчета
			$('#edit_variants_content .variant_content_block').css({'display':'none'})
			// вставляем html
			$('#edit_variants_content .variant_content_block:last-of-type').after(div_html);

			// убиваем календари 
			destroy_datetimepicker_for_variant_cont()
			// создаем календари для всех по новой
			create_datepicker_for_variant_cont();// ДАТА
			create_timepicker_for_variant_cont();// ВРЕМЯ
			
		}
	},"json");
});


// обработка кнопок ПЗ, НПЗ
$(document).on('click', '#edit_variants_content .tirage_buttons .btn_var_std', function(event) {
	// отработка html
	$('#edit_variants_content .tirage_buttons .btn_var_std').removeClass('checked');
	$(this).addClass('checked');
	// получение данных для отправки на сервер
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	var pz = ($(this).html() == 'ПЗ')?1:0; // печатать \ не печатать запас
	// отправка данных на сервер
	$.post('', {
		global_change: 'AJAX',
		change_name: 'change_tirage_pz',
		id:id,
		row_id:row_id,
		pz: pz
	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
	},'json');
});


// отработка клика по быстрым кнопкам
$(document).on('click','#btn_make_std',function(){
	$(this).addClass('checked');
	$('#btn_make_var').removeClass('checked');
	$(this).parent().find('input').attr('readonly','true').addClass('input_disabled').val(10);
});
$(document).on('click','#btn_make_var',function(){
	$(this).addClass('checked');
	$('#btn_make_std').removeClass('checked');
	$(this).parent().find('input').removeAttr('readonly').removeClass('input_disabled');
});

$(document).on('click','#btn_date_std',function(){
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1;
	var curr_year = d.getFullYear();
	// указать дату отгрузки
	// решить как будет производиться обсчет выходных дней и праздников
	// исключить выходные из подсчёта рабочих дней
	cmm = (curr_date+5) + "." + curr_month + "." + curr_year;	
	$(this).addClass('checked');
	$('#btn_date_var').removeClass('checked');
	$('#datepicker1').val(cmm).attr('readonly','true').addClass('input_disabled');
});
$(document).on('click','#btn_date_var',function(){	
	$(this).addClass('checked');
	$('#btn_date_std').removeClass('checked');
	$(this).parent().find('input').removeAttr('readonly').removeClass('input_disabled');
});


$(document).ready(function() {
	chenge_draft_name();		
});

$(document).on('click', '#variants_name .variant_name', function(){
	// отработка показа / скрытия вариантов расчёта
	// при клике по кнопкам вариантов
	$('.variant_name').removeClass('checked');
	$(this).addClass('checked');	
	var id = $(this).attr('data-cont_id');
	$('.variant_content_block').css({'display':'none'});
	$('#'+id).css({'display':'block'});

	test_chenge_archive_list();
});

function test_chenge_archive_list(){
	if($('#all_variants_menu .variant_name.checked').hasClass('show_archive')){
		$('#choose_end_variant').html('Извлечь расчёт из архива').attr('id','extract_from_archive');
	}else{
		$('#extract_from_archive').html('Выбрать основной').attr('id','choose_end_variant');
	}
}

$(document).on('click','#extract_from_archive',function(){
	// отправляем запрос на смену статуса (на "не архив")
	
	// получение данных для отправки на сервер
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	

	// отправка данных на сервер
	$.post('', 
		{
			global_change: 'AJAX',
			change_name: 'change_archiv',
			id:id,
			row_id:row_id
		}, function(data, textStatus, xhr) {
		if(data['response']!='1'){
			alert('что-то пошло не так.');
		}else{
			// меняем html получив положительный ответ
			var id_div = $('#all_variants_menu .variant_name.checked').removeClass('show_archive').attr('data-cont_id');
			$('#'+id_div).removeClass('archiv_opacity');
			test_chenge_archive_list();			
		}
	},'json');
});

$(document).on('click','#choose_end_variant',function(){
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	// отправляем запрос на смену статуса варианта на Основной
	$.post('', 
		{
			global_change: 'AJAX',
			change_name: 'change_draft',
			id:id,
			row_id:row_id,
		}, function(data, textStatus, xhr) {
		if(data['response']!='1'){
			alert('что-то пошло не так.');
		}else{
			// присваиваем статус варианта Основной
			$('#variants_name .variant_name').each(function(index, el) {
				if(!$(this).hasClass('checked')){
					if($('#show_archive a').attr('data-true')!='1'){
						$(this).remove();
					}else{
						$(this).addClass('show_archive');
						$('#'+$(this).attr('data-cont_id')).addClass('archiv_opacity');

					}
				}
			});
			//$('#choose_end_variant').attr('data-back','1').html('Сделать черновиком');
		}
	},'json');
	
})

// колькуляция и сохранение изменённых данных от тираже в таблице размеров
$(document).on('keyup','.val_tirage, .val_tirage_dop', function(){
	var summ = 0;
	$('#'+$('.variant_name.checked').attr('data-cont_id')+' .'+$(this).attr('class')).each(function(index, el) {
		summ += Number($(this).val());
	});
	console.log('-'+$(this).attr('class')+'- = -val_tirag-');
	if($(this).attr('class') == 'val_tirage'){
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .tirage_var';
	}else{
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .dop_tirage_var';	
	}
	$(id).val(summ);


	$.post('', {
		global_change: 'AJAX',
		change_name: 'size_in_var',
		val:$(this).val(),
		key:$(this).attr('data-id_size'),
		dop:$(this).attr('data-dop'),
		id: $(this).attr('data-var_id')
	}, function(data, textStatus, xhr) {
		console.log(data);
	});
});




// изменеие информации по изготовлению в р/д в текущем варианте
function save_standart_day(){
	// получение данных для отправки на сервер
	var id = $('#variants_name .variant_name.checked ').attr('data-id');
	var row_id = $('#claim_number').attr('data-order');	
	var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
	$('#'+id_variant+' .timepicker2').hide(); // показать поле время
	$('#'+id_variant+' .datepicker2, #'+id_variant+' .timepicker2').val('');
	var standart = $('#'+id_variant+' .fddtime_rd2[name="fddtime_rd2"]').val();
	// var time = $input.val()+':00';
	// alert($input.attr('class'));
	$.post('', {
		global_change: 'AJAX',
		change_name: 'save_standart_day',
		id: id,
		row_id: row_id,
		standart:standart
	}, function(data, textStatus, xhr) {
	/*optional stuff to do after success */
	},"json");
}

$(document).on('click','.btn_var_std[name="std"]',function(){	
	$(this).addClass('checked');
	$(this).parent().find('input').val(10);
	// сохраняемся и меняем html
	save_standart_day();
});



$(document).on('keyup','.fddtime_rd2',function(){
	if($(this).val()!='10'){
		$(this).prev().removeClass('checked');
	}else{
		if(!$(this).prev().hasClass('checked')){
			$(this).prev().addClass('checked');
		}		
	}
	// сохраняемся и меняем html
	save_standart_day();
});


// отслеживание нажатий функциональных клавиш с клавиатуры
$(document).keydown(function(e) {
	if(e.keyCode == 27){//ESC	
	// alert();
	}	
	if(e.keyCode == 38){//вверх		
		// alert()
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .fddtime_rd2';
		if($(id).is( ":focus" )){			
			$(id).val(Number($(id).val())+1);
			$(id).setCursorPosition($(id).val().length);
		}		
	}
	if(e.keyCode == 40){//вниз		
		// alert()
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .fddtime_rd2';
		if($(id).is( ":focus" )){			
			$(id).val(Number($(id).val())-1);
			// $(id).setCursorPosition($(id).val().length);
		}	
	}
	

});


