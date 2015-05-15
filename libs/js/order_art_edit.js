
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
	
});

// ИЗМЕНЕНИЕ тиража ИЗ ОБЩЕГО input варианта
$(document).on('keyup','#edit_variants_content .tirage_var',function(){
	chenge_the_general_input();
});

// ИЗМЕНЕНИЕ запаса ИЗ ОБЩЕГО input варианта
$(document).on('keyup','#edit_variants_content .dop_tirage_var',function(){
	chenge_the_general_input();
});

// функция сохранения размерной сетки
function save_all_table_size(){
	var id_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	var id_size = new Array();
	var tirage = new Array();
	var var_id = new Array();
	var dop = new Array();
	$(id_variant+' .size_card .val_tirage').each(function(index, el) {
		id_size[index] = $(this).attr('data-id_size');
		tirage[index] = $(this).val();
		var_id[index] = $(this).attr('data-var_id');
		dop[index] = $(this).parent().parent().find('.val_tirage_dop').val();
	});

	$.post('', {
		global_change: 'AJAX',
		change_name: 'size_in_var_all',
		val:tirage,
		key:id_size,
		dop:dop,
		id: var_id
	}, function(data, textStatus, xhr) {
		console.log(data);
	});

}

// расчитывает размер с наибольшим свободным местом
// возвращает объект (массив) с информацией о строке размера
function get_info_for_ost(){
	var id = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	var max = 0; // максимальное значение остатка, исключая полностью заполненные менеджером
	var ostatok = 0; // доступный остаток у поставщика по данному размеру
	var tir = 0; // введённый тираж
	var prigon = 0;
	var ind = 0;
	var tir_out = 0;
	var prigon_out = 0;

	$(id+' .size_card .ostatok_free').each(function(index, el) {
		ostatok = Number($(this).html());
		prigon = Number($(this).parent().find('.val_tirage_dop').val());
		tir = Number($(this).parent().find('.val_tirage').val());		

		if(ostatok>max && ostatok>(tir+prigon)){
			ind = index+1;
			tir_out = Number($(this).parent().find('.val_tirage').val());
			prigon_out = Number($(this).parent().find('.val_tirage_dop').val());
			max = Number($(this).html()); // свободно у поставщика максимально
		}
	});
	var arr = [max, tir_out, ostatok, prigon_out, ind];
	return arr;
}

// обсчет и трансляция общего тиража и запаса в размерную таблицу
function chenge_the_general_input(){
	// получаем id активного блока
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	
	// считаем тираж до изменения	
	var old_tirage = 0;
	$(id_active_variant +' .val_tirage').each(function(index, el) {
		old_tirage += Number($(this).val());
	});
	
	// получаем общий старый запас
	var old_zapas = 0;
	$(id_active_variant +' .val_tirage_dop').each(function(index, el) {
		old_zapas += Number($(this).val());
	});
	
	// получаем максимальный остаток
	var max_ostatok = 0;
	$(id_active_variant +' .ostatok_free').each(function(index, el) {
		max_ostatok += Number($(this).html());
	});

	// определяем значение общего поля тиража
	var general_tirage = Number($(id_active_variant+ ' .tirage_var').val());
	// определяем значение общего поля запаса
	var general_zapas = Number($(id_active_variant+ ' .dop_tirage_var').val());
	
	// определяем ограничиваем ли тираж и запас по остаткам
	var reserv =($(id_active_variant+' .size_card .sevrice_button_size_table span[name="reserve"]').hasClass('checked'))?1:0;
	

	//определяем что именно было изменено и в какую сторону +/-
	var raznost = 0;
	if(general_tirage != old_tirage){
		/*******************************/
		/*           TIRAGE            */
		/*******************************/		
		if(reserv){// ограничения по резерву
			// уменьшаем до максимально допустимого значения
			if(max_ostatok<(general_tirage+general_zapas)){
				general_tirage = max_ostatok - general_zapas;
				$(id_active_variant+ ' .tirage_var').val(general_tirage);
			}	
			// изменения коснулись тиража, кнопка резерв активирована
			if(general_tirage > old_tirage){//тираж увеличен
				raznost = general_tirage - old_tirage;
				/*******************************/
				/*             CALC            */
				/*******************************/
				var service_val = 0;

				while(raznost>0){
					var obj = get_info_for_ost(); //arr = [max, tir_out, ostatok, prigon_out, ind];
					// размер не занятых единиц тиража в строке размера
					var sv = obj[0] - obj[1] - obj[3]; 
					// индекс строки размерной сетки
					var ind = obj[4], 

					// обсчитываем тираж в размерной сетке по новой			
					old_tirage = 0;	
					$(id_active_variant + ' .val_tirage').each(function(index, el) {
						old_tirage += Number($(this).val());
					});
					// считаем разностть
					raznost = general_tirage - old_tirage;
					// выходим из цикла , если разность равна 0
					if(raznost==0) break;
					
					// запоминаем значение тиража в размерной сетке данного размера
					var object = $(id_active_variant+' .size_card table tr:nth-of-type('+(ind+1)+') td .val_tirage');
					service_val = Number(object.val());

					if(sv>=raznost){// если свободные единицы тиража больше или равны разности		
						object.val(service_val+raznost);
						console.log(obj[4] + ' - '+object.val());
					}else{				
						object.val(service_val+sv);
						console.log(obj[4] + ' - '+object.val());
					}
				}
				
				/*******************************/
				/*         END CALC            */
				/*******************************/

			}else{// тираж уменьшен
				raznost = old_tirage - general_tirage;				
				if(raznost==0){return false;}
				/*******************************/
				/*             CALC            */
				/*******************************/
				/*
				raznost - это разность между новым тиражом и тиражом из размерной сетки
				- то есть это число на которое нам необходимо понизить наш тираж
				*/ 
				
				// заведём переменные тиража в строке 
				var size_tir = 0;
				// переберём в цикле все размеры
				$(id_active_variant +' .val_tirage').each(function(index, el) {
					// запоминаем тираж в данном размере
					size_tir = Number($(this).val());
					// если тираж по данному размеру <1
					if(size_tir<1){return true;}
					// вычитаем из него наше число
					if(size_tir >= raznost){
						$(this).val(size_tir-raznost);
						raznost = 0;
					}else{
						raznost = (raznost - size_tir);
						$(this).val(0);
					}
					return ( raznost !== 0 );
				});
			}				
		}else{// кнопка резерв не активна
			export_gen_input_in_size_tbl();
		}
		
	}else{
		/*******************************/
		/*            ZAPAS            */
		/*******************************/
		if(reserv){// ограничения по резерву
			// уменьшаем до максимально допустимого значения
			if(max_ostatok<(general_tirage+general_zapas)){
				general_zapas = max_ostatok - general_tirage;
				$(id_active_variant+ ' .dop_tirage_var').val(general_zapas);
			}	
			// изменения коснулись запаса, кнопка резерв активирована
			if(general_zapas > old_zapas){//запас увеличен
				raznost = general_zapas - old_zapas;
				/*******************************/
				/*             CALC            */
				/*******************************/
				var service_val = 0;

				while(raznost>0){
					var obj = get_info_for_ost(); //arr = [max, tir_out, ostatok, prigon_out, ind];
					// размер не занятых единиц тиража в строке размера
					var sv = obj[0] - obj[1] - obj[3]; 
					// индекс строки размерной сетки
					var ind = obj[4], 

					// обсчитываем запас в размерной сетке по новой			
					old_zapas = 0;	
					$(id_active_variant + ' .val_tirage_dop').each(function(index, el) {
						old_zapas += Number($(this).val());
					});
					// считаем разностть
					raznost = general_zapas - old_zapas;
					// выходим из цикла , если разность равна 0
					if(raznost==0) break;
					
					// запоминаем значение запаса в размерной сетке данного размера
					var object = $(id_active_variant+' .size_card table tr:nth-of-type('+(ind+1)+') td .val_tirage_dop');
					service_val = Number(object.val());

					if(sv>=raznost){// если свободные единицы тиража больше или равны разности		
						object.val(service_val+raznost);
						console.log(obj[4] + ' - '+object.val());
					}else{				
						object.val(service_val+sv);
						console.log(obj[4] + ' - '+object.val());
					}
				}
				
				/*******************************/
				/*         END CALC            */
				/*******************************/

			}else{// запас уменьшен
				raznost = old_zapas - general_zapas;
				if(raznost==0){return false;}	
				/*******************************/
				/*             CALC            */
				/*******************************/
				/*
				raznost - это разность между новым запасом и запасом из размерной сетки
				- то есть это число на которое нам необходимо понизить наш запасом
				*/ 
				
				// заведём переменные запаса в строке 
				var size_zap = 0;
				// переберём в цикле все размеры
				$(id_active_variant +' .val_tirage_dop').each(function(index, el) {
					// запоминаем тираж в данном размере
					size_zap = Number($(this).val());
					// если запасом по данному размеру <1 переходим к следующей интерации
					if(size_zap<1){return true;}
					console.log(size_zap);
					// вычитаем из него наше число
					if(size_zap >= raznost){
						$(this).val(size_zap-raznost);
						raznost = 0;
					}else{
						raznost = (raznost - size_zap);
						$(this).val(0);
					}
					return ( raznost !== 0 );
				});
			}				
		}else{// кнопка резерв не активна
			export_gen_input_in_size_tbl();
		}

	}
	// сохраняем размерную таблицу
	save_all_table_size();
}

// перенос содержимого общего тиража и запаса в первое поле размерной сетки, остальное трется
function export_gen_input_in_size_tbl(){
	var id_active_variant = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	// определяем значение общего поля тиража
	var general_tirage = Number($(id_active_variant+ ' .tirage_var').val());
	// определяем значение общего поля запаса
	var general_zapas = Number($(id_active_variant+ ' .dop_tirage_var').val());
	$(id_active_variant+' .size_card .val_tirage,'+id_active_variant+' .size_card .val_tirage_dop').val(0);
	$(id_active_variant+' .size_card .val_tirage:first').val(general_tirage);
	$(id_active_variant+' .size_card .val_tirage_dop:first').val(general_zapas);
}

// колькуляция и сохранение изменённых данных от тираже в таблице размеров
$(document).on('keyup','.val_tirage, .val_tirage_dop', function(){
	// console.log($(this).parent('div').html());
	var id = '#'+$('#variants_name .variant_name.checked ').attr('data-cont_id');
	// $(id+' .size_card').

	// определяем максимальный тираж по данному размеру
	var max_tirage = Number($(this).parent().parent().find('.ostatok_free').html());
	// запоминаем введённые данные
	var save_val = Number($(this).val());
	// если введенные данные меньше нуля
	if(save_val<0){$(this).val(0);}

	if($(this).attr('class') == 'val_tirage'){// редактируется тираж для размера
		// если считаем под резерв
		if($(id+' .size_card .btn_var_std[name="reserve"]').hasClass('checked')){
			// определяем введённый запас
			var zapas = Number($(this).parent().parent().find('.val_tirage_dop').val());
			// определяем общее количество введенного в поле товара: сумму запаса и тиража
			var general_tirage_this_size = save_val + zapas;
			// определяем максимальный тираж по данному размеру сувенира
			max_tirage_size = max_tirage - zapas;
			// проверяем на привышения тиража по заданному размеру, 
			// если надо правим на максимально возможную цифру
			if(general_tirage_this_size > max_tirage){$(this).val(max_tirage_size);}
		}
		// определяем адрес общего поля тиража
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .tirage_var';
	}else{// редактируется запас для размера
		// если считаем под резерв
		if($(id+' .size_card .btn_var_std[name="reserve"]').hasClass('checked')){
			// определяем введённый тираж
			var tirage = Number($(this).parent().parent().find('.val_tirage').val());
			// определяем общее количество введенного в поле товара: сумму запаса и тиража
			var general_tirage_this_size = save_val + tirage;
			// определяем максимальный тираж по данному размеру сувенира
			max_tirage_size = max_tirage - tirage;
			// проверяем на привышения тиража по заданному размеру, 
			// если надо правим на максимально возможную цифру
			if(general_tirage_this_size > max_tirage){$(this).val(max_tirage_size);}
		}
		// определяем адрес общего поля запаса
		var id = '#'+$('.variant_name.checked').attr('data-cont_id')+' .dop_tirage_var';	
	}

	/*
		С этого момента мы считаем, что в полях размерной сетки введены валидные данные, 
		которые не привышают доступное количество на складе
	*/
	
	// подсчитываем сумму всех полей редактируемого типа (тираж или запас)
	var summ = 0;
	$('#'+$('.variant_name.checked').attr('data-cont_id')+' .'+$(this).attr('class')).each(function(index, el) {
		summ += Number($(this).val());
	});
	// выводим сумму в input общего тиража
	$(id).val(summ);

	// отправляем запрос на изменение данных в базе по отредактированному размеру
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


// переключение сервисных кнопок под резерв и под заказ
$(document).on('click','#edit_variants_content .variant_content_block:visible .size_card .sevrice_button_size_table span',function() {
	$('#edit_variants_content .variant_content_block:visible .size_card .sevrice_button_size_table span').removeClass('checked');
	$(this).addClass('checked');
	if($(this).attr('name')=="order"){
		$('#edit_variants_content .variant_content_block:visible .size_card input').removeAttr('readonly').removeClass('input_disabled');
	}else{
		$('#edit_variants_content .variant_content_block:visible .size_card tr').each(function(index, el) {
			if(Number($(this).find('.ostatok_free').html())==0){
				$(this).find('input').attr('readonly','readonly').addClass('input_disabled').val(0);
			}	
		});
	}
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



