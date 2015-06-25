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
	// console.log(quantity);
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
					title = data['title'];// для генерации окна всегда должен передаваться title
					show_dialog_and_send_POST_window(data['html'],title);
				}else{
					$('#dialog_gen_window_form').dialog( "destroy" );


					if(data['name'] == 'add_uslugu'){ // если нужно добавить услугу
						
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





//###################################################################
//##  РАБОТА ТАБЛИЦЫ РАСЧЕТОВ В НЕКАТАЛОЖНОЙ ПРОДУКИИ ##   START   ##
//###################################################################

// КАЛЬКУЛЯЦИЯ

// пересчет ИТОГО таблицы с ценами по варианту
function recalculate_table_price_Itogo(){
	// находим таблицу расчёта текущего (активного) варианта
	var itogo_price_in = 0;
	var itogo_price_out_snab = 0;
	var itogo_price_out_men = 0;
	var itogo_pribl = 0;
	var itogo_percent = 0;
	var n = 0;

	// ПОДСЧЁТ СТОИМОСТЕЙ ВСЕХ УСЛУГ
	$('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('tr.calculate').each(function(index, el) {
			itogo_price_in += Number($(this).find('td:nth-of-type(2) span').html());// входящая цена		
			itogo_percent += Number($(this).find('td:nth-of-type(3) span').html());// процент		
			itogo_price_out_snab += Number($(this).find('td:nth-of-type(4) span').html());// исходящая цена cнаб	
			itogo_price_out_men += Number($(this).find('td:nth-of-type(5) span').html());// исходящая цена мен	
		n++;	
	});
	// console.log(itogo_price_out_snab);

	// ПРИБАВЛЯЕМ СТОИМОСТЬ ТОВАРА

	// стоимость входящая
	itogo_price_in += Number($('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('.row_tirage_in_gen.price_in span').html());
	// исходящая цена cнаб
	itogo_price_out_snab += Number($('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('.row_price_out_gen.price_out_snab span').html());
	// исходящая цена мен
	itogo_price_out_men += Number($('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('.row_price_out_gen.price_out_men span').html());
	// %  с тиража
	itogo_percent += Number($('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('.percent_nacenki span').html()); 


	// ОКРУГЛЯЕМ
	// исходящая цена cнаб
	itogo_price_out_snab = round_s(itogo_price_out_snab);
	// исходящая цена мен
	itogo_price_out_men = round_s(itogo_price_out_men);
	// входящая цена
	itogo_price_in = round_s(itogo_price_in);
	// процент		
	itogo_percent = round_s((itogo_percent)/(n+1));

	// // консоль СТАРТ
	// console.log(itogo_price_out_snab );
	// // исходящая цена мен
	// console.log(itogo_price_out_men);
	// // входящая цена
	// console.log(itogo_price_in );
	// // процент		
	// console.log(itogo_percent);

	// итоговая прибыль
	itogo_pribl = round_s(itogo_price_out_men-itogo_price_in);

	// ЗАПОЛНЯЕМ ИТОГО
	// помечаем строку итого
	$('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('tr.variant_calc_itogo').attr('id','adsfadsf65456asfd');
	
	// вносим изменения
	$('#adsfadsf65456asfd').find('td:nth-of-type(2) span').html(itogo_price_in);
	$('#adsfadsf65456asfd').find('td:nth-of-type(3) span').html(itogo_percent);
	$('#adsfadsf65456asfd').find('td:nth-of-type(4) span').html(itogo_price_out_snab);
	$('#adsfadsf65456asfd').find('td:nth-of-type(5) span').html(itogo_price_out_men);
	$('#adsfadsf65456asfd').find('td:nth-of-type(6) span').html(itogo_pribl);
	// удаляем метку id
	$('#adsfadsf65456asfd').removeAttr('id');

	// МЕНЯЕМ ЗНАЧЕНИЯ ЦЕН В ОБЩЕМ СПИСКЕ ВАРИАНТОВ
	// цена входящая
	$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked td:nth-of-type(4) span').html(itogo_price_in);
	// цена минимальная исходящая от снаба
	$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked td:nth-of-type(5) span').html(itogo_price_out_snab);
	// цена минимальная исходящая от мена
	$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked td:nth-of-type(6) span').html(itogo_price_out_men);
}


/*--1--START--*/
// редактирование мин исходящей цены 1ед товара 
$(document).on('keyup keypress', '.row_tirage_in_one.price_in span', function(event) {
	calculate_price_out_tovars_Edit_start_price_for_one( $(this).parent().parent().parent());
	// ПЕРЕСЧИТЫВАЕМ ИТОГО
	recalculate_table_price_Itogo();
});
// пересчёт исходящей стоимости при изменении начальной стоимости товара за 1
// на вход принимает объект активной calkulate_table
function calculate_price_out_tovars_Edit_start_price_for_one(object){
	// ОБЪЯВЛЯЕМ ПЕРЕМЕННЫЕ
	// ПОЛУЧАЕМ ЗНАЧЕНИЯ
	object = object.find('.row_tirage_in_one.price_in span');
	// цена за единицу товара
	var price_for_one = Number(object.html());
	
	// тираж
	var quantity = Number($('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html());
	
	// цена за единицу товара снаб (мин цена с мин наценкой)
	var price_out_for_one_snab = Number(object.parent().parent().find('.row_price_out_one.price_out_snab span').html()); 

	// цена за тираж снаб (мин цена с мин наценкой)
	var price_out_for_all_snab = Number(object.parent().parent().next().find('.row_price_out_gen.price_out_snab span').html()); 
	
	// вход. цена за тираж
	var price_in_for_all = Number(object.parent().parent().next().find('.row_tirage_in_gen.price_in span').html());
	
	// процент наценки
	var percent = Number(object.parent().parent().find('.percent_nacenki span').html());
	

	// если введённые данные больше, цена с наценкой
	if(price_for_one>=price_out_for_one_snab){
		// ставим % = 0
		object.parent().parent().find('.percent_nacenki span').html(0);
		// приравниваем цену с наценкой за ед к введённым данным
		object.parent().parent().find('.row_price_out_one.price_out_snab span').html(price_for_one)
		// пересчитываем цену с наценкой за тираж
		object.parent().parent().next().find('.row_price_out_gen.price_out_snab span').html(round_s(price_for_one*quantity));

		//исходящая для менеджера
		// приравниваем цену с наценкой за ед к введённым данным
		object.parent().parent().find('.row_price_out_one.price_out_men span').html(price_for_one)
		// пересчитываем цену с наценкой за тираж
		object.parent().parent().next().find('.row_price_out_gen.price_out_men span').html(round_s(price_for_one*quantity));
		// пересчитываем входящую цену за тираж
		object.parent().parent().next().find('.row_tirage_in_gen.price_in span').html(round_s(price_for_one*quantity));
		//пересчёт прибыли
		object.parent().parent().find('.row_pribl_out_one.pribl span').html('0.00')
		// пересчитываем цену с наценкой за тираж
		object.parent().parent().next().find('.row_pribl_out_gen.pribl span').html('0.00');

	}else{
		// пересчитываем входящую цену за тираж
		object.parent().parent().next().find('.row_tirage_in_gen.price_in span').html(round_s(price_for_one*quantity));
		// ставим % 
		object.parent().parent().find('.percent_nacenki span').html(percent_calc(price_out_for_one_snab,price_for_one));
		// пересчёт прибыли
		object.parent().parent().find('.row_pribl_out_one.pribl span').html(round_s(price_out_for_one_snab-price_for_one));
		// пересчёт прибыли за тираж
		console.log();
		object.parent().parent().next().find('.row_pribl_out_gen.pribl span').html(round_s(price_out_for_all_snab-price_for_one*quantity));
	}
}
/*--1--END--*/


/*--2--START--*/
// редактирование вход. цены за тираж
$(document).on('keyup', '.row_tirage_in_gen.price_in span', function(event) {
	Edit_start_price_for_all($(this));
	// ПЕРЕСЧИТЫВАЕМ ИТОГО
	recalculate_table_price_Itogo();
});
// пересчёт исходящей стоимости относительно начальной стоимости товара за тираж
function Edit_start_price_for_all(object){
	// цена за единицу товара
	var price_in_for_all = Number(object.html());
	
	// тираж
	var quantity = Number($('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html());
	console.log(quantity);
				
	// пересчитываем входящую цену за ед
	object.parent().parent().prev().find('.row_tirage_in_one.price_in span').html(round_s(price_in_for_all/quantity));
	// пересчитываем отностительно вход. цены за ед
	calculate_price_out_tovars_Edit_start_price_for_one(object.parent().parent().parent());
}
/*--2--END--*/


/*--3--START--*/
// редактирование % наценки за продукцию
$(document).on('keyup', '.percent_nacenki span', function(event) {
	// var inter_percent = 0; // введённый процент
	
	//  = Number($(this).html()):
	var inter_percent = Number($(this).html());
	if(Number($(this).html())<0){
		var inter_percent = 0;
		$(this).html(0);
	}

	var price_in_for_one = Number($(this).parent().parent().find('.row_tirage_in_one.price_in span').html());
	var price_in_for_all = Number($(this).parent().parent().next().find('.row_tirage_in_gen.price_in span').html());

	if(price_in_for_one==0 || price_in_for_all==0){alert('Пожалуйста, укажите входящую стоимость продукта');return;}

	/* 
	если работает снаб или админ, то меняя процент наценки 
	он меняет и исх. цену снабжения и исх. цену менеджера одновременно
	*/

	/* 
	если работает менеджер, то меняя процент наценки 
	он меняет тольк исх. цену менеджера 
	*/


	// цена исходящая за ед снаб и мен
	var price_out_for_one_snab = 0;
	var price_out_for_one_men = 0;

	// цена исходящая за тираж снаб и мен
	var price_out_for_all_snab = 0;
	var price_out_for_all_men = 0; 
	// работает снаб или админ
	//console.log($(this).parent().parent().find('.row_tirage_in_one.price_in span').attr('contenteditable')=="true");
	if($(this).parent().parent().find('.row_tirage_in_one.price_in span').attr('contenteditable')){

		price_out_for_one_snab = price_out_for_one_men = round_s((100+inter_percent)*price_in_for_one/100);
		price_out_for_all_snab = price_out_for_all_men = round_s((100+inter_percent)*price_in_for_all/100);

		console.log('price_out_for_one_snab =' + price_out_for_one_snab);
		console.log('price_out_for_all_snab =' + price_out_for_all_snab);
		console.log('price_in_for_all =' + price_in_for_all);
		console.log('price_in_for_one =' + price_in_for_one);
		console.log('inter_percent =' + inter_percent);

		// устанавливаем исходящие цены
		$(this).parent().next().find('span').html(price_out_for_one_snab).parent().next().find('span').html(price_out_for_one_snab);
		$(this).parent().parent().next().find('.row_price_out_gen.price_out_snab span').html(price_out_for_all_snab).parent().parent().find('.row_price_out_gen.price_out_men span').html(price_out_for_all_snab);

		// подсчитываем прибыль
		// $(this).parent().next().next().next().find('span').html(round_s(price_out_for_one_snab-price_in_for_one));
		// $(this).parent().parent().next().find('.row_pribl_out_gen.pribl span').html(round_s(price_out_for_all_snab-price_in_for_all));
		calc_percent();

	}else{// работает менеджер

		price_out_for_one_snab = Number($(this).parent().next().find('span').html())

		price_out_for_one_men = round_s((100+inter_percent)*price_in_for_one/100);
		price_out_for_all_men = round_s((100+inter_percent)*price_in_for_all/100);

		console.log(price_out_for_one_snab);
		console.log(price_out_for_one_men);

		if(price_out_for_one_snab>price_out_for_one_men){
			console.log('Вы не можете указавать цены ниже чем снабжение');
		}else{
			// устанавливаем исходящие цены
			$(this).parent().next().next().find('span').html(price_out_for_one_men);
			$(this).parent().parent().next().find('.row_price_out_gen.price_out_men span').html(price_out_for_all_men);

			// подсчитываем прибыль
			// $(this).parent().next().next().next().find('span').html(round_s(price_out_for_one_men-price_in_for_one));
			// $(this).parent().parent().next().find('.row_pribl_out_gen.pribl span').html(round_s(price_out_for_all_men-price_in_for_all));
			calc_percent();
		}
	}
	
	recalculate_table_price_Itogo()
});



/*--4--START--*/
// редактирование мин исходящей цены 1ед товара из тиража
$(document).on('keyup', '.row_price_out_one.price_out_snab span', function(event) {
	edit_price_out_one_snab($(this));
	recalculate_table_price_Itogo();
});
function edit_price_out_one_snab(obj){
	// получим тираж
	var quantity = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html();
	// console.log(quantity);
	var price_out_one_snab = Number(obj.html());
	var price_in_one = Number(obj.parent().prev().prev().find('span').html()); 
	// console.log(price_out_one_snab);
	// console.log(price_in_one);
	// если цена меньше, приравниваем её к минимальной
	if(price_in_one>price_out_one_snab){
		obj.html(price_in_one).parent().next().find('span').html(price_in_one);
		price_out_one_snab=price_in_one;

		var price_out_all_snab = Number(obj.parent().parent().next().find('.row_tirage_in_gen.price_in span').html());
		console.log(price_out_all_snab);
		obj.parent().parent().next().find('.row_price_out_gen.price_out_snab span').html(price_out_all_snab).parent().next().find('span').html(price_out_all_snab);
		
		// расчёт процентов товара
		calc_percent();
	}else{
		
		price_out_all_snab = round_s(price_out_one_snab*quantity);
		obj.parent().parent().next().find('.row_price_out_gen.price_out_snab span').html(price_out_all_snab).parent().next().find('span').html(price_out_all_snab);
		obj.parent().next().find('span').html(price_out_one_snab);	
		
		// расчёт процентов товара	
		calc_percent();
	}
}
/*--4--END--*/


/*--5--START--*/
// редактирование мин исходящей цены за тираж
$(document).on('keyup', '.row_price_out_gen.price_out_snab span', function(event) {
	edit_price_out_all_snab($(this));
	recalculate_table_price_Itogo();
});
function edit_price_out_all_snab(obj){
	// получим тираж
	var quantity = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html();
	// получим то, что мы наменяли
	var price_out_all_snab = Number(obj.html());
	// получим объект таблицы расётов
	var table_obj = obj.parent().parent().parent();
	// входящая цена за тираж
	var price_in_for_all = Number(table_obj.find('.row_tirage_in_gen.price_in span').html());

	if(price_in_for_all>price_out_all_snab){
		obj.html(price_in_for_all);price_out_all_snab = price_in_for_all;
	}
	// правим цену тиража для мена
	obj.parent().next().find('span').html(price_out_all_snab);

	// правим цену ед-цы тиража для снаба
	table_obj.find('.row_price_out_one.price_out_snab span').html(round_s(price_out_all_snab/quantity));
	// правим цену ед-цы тиража для мена
	table_obj.find('.row_price_out_one.price_out_men span').html(round_s(price_out_all_snab/quantity));


	calc_pribl_tir();
}
/*--5--END--*/


/*--6--START--*/
// редактирование исходящей цены за ед из тиража MEN
$(document).on('keyup', '.row_price_out_one.price_out_men span', function(event) {
	edit_price_out_one_men($(this));
	recalculate_table_price_Itogo();
});
function edit_price_out_one_men(obj){
	// получим тираж
	var quantity = Number($('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html());
	var price_out_one_men = Number(obj.html());
	var price_out_one_snab = Number(obj.parent().prev().find('span').html());

	// если стоимость меньше установленной снабом
	if(price_out_one_snab>price_out_one_men){
		price_out_one_men = price_out_one_snab;
		obj.html(price_out_one_snab);
	}
	var price_out_all_men = round_s(price_out_one_men*quantity);	
	obj.parent().parent().parent().find('.row_price_out_gen.price_out_men span').html(price_out_all_men);
	
	// подсчёт процентов
	calc_percent();
	// посчёт прибыли
	calc_pribl_tir();

}
/*--6--END--*/



/*--7--START--*/
// редактирование исходящей цены за тираж MEN
$(document).on('keyup', '.row_price_out_gen.price_out_men span', function(event) {
	// получим тираж
	var quantity = Number($('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html());
	var price_out_all_men = Number($(this).html());
	var price_out_all_snab = Number($(this).parent().prev().find('span').html());
	console.log(price_out_all_snab);
	// если стоимость меньше установленной снабом
	if(price_out_all_snab>price_out_all_men){
		price_out_all_men = price_out_all_snab;
		$(this).html(price_out_all_snab);
	}

	var price_out_one_men = round_s(price_out_all_men/quantity);
	console.log(price_out_one_men);
	
	$(this).parent().parent().parent().find('.row_price_out_one.price_out_men span').html(price_out_one_men);
	
	console.log('65464654');
	// подсчёт процентов
	calc_percent();
	// посчёт прибыли
	calc_pribl_tir();
	// подсчёт ИТОГО
	recalculate_table_price_Itogo();
});

/*--7--END--*/


function percent_calc(price_out,price_in){
	return Math.ceil(((price_out-price_in)*100/price_in)*100)/100;
}

// подсчёт процентов товара для активной таблицы
function calc_percent(){
	// получаем активную таблицу
	obj = $('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table');
	var price_out = obj.find('.row_price_out_one.price_out_men span').html()
	var price_in  = obj.find('.row_tirage_in_one.price_in span').html()
	obj.find('.percent_nacenki span').html(percent_calc(price_out,price_in));
}
// расчёт прибыли в тираже
function calc_pribl_tir(){
	obj = $('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table');

	var price_out_for_one_men = Number(obj.find('.row_price_out_one.price_out_men span').html());
	var price_in_for_one = Number(obj.find('.row_tirage_in_one.price_in span').html());


	 
	var price_in_for_all = Number(obj.find('.row_tirage_in_gen.price_in span').html());
	var price_out_for_all_men= Number(obj.find('.row_price_out_gen.price_out_men span').html());

	
	obj.find('.row_pribl_out_one.pribl span')
	.html(round_s(price_out_for_one_men-price_in_for_one))
	.parent().parent().next().find('.row_pribl_out_gen.pribl span')
	.html(round_s(price_out_for_all_men-price_in_for_all));
	console.log(price_out_for_all_men);
	console.log(price_in_for_all);
	console.log(price_out_for_all_men-price_in_for_all);
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

	$.post('', 
		{
			AJAX: 'delete_usl_of_variant',
			uslugi_id: dop_uslugi_id
		}, function(data, textStatus, xhr) {
		console.log(data);
	});
	
});


// ДОП УСЛУГИ
// %
$(document).on('keyup', '.row_tirage_in_gen.uslugi_class.percent_usl span', function(event) {
	// получаем реальные значения цены данной услуги, цены взяты из прайса
	// если эти значения равны, то услуга применяется к тиражу, если нет то к единице товара
	var min_price_real_for_one = Number($(this).parent().next().attr('data-real_min_price_for_one'));
	var min_price_real_for_all = Number($(this).parent().next().attr('data-real_min_price_for_all'));
	
	// цена от снаба (устанавливает снаб или админ)
	var min_price_snab = Number($(this).parent().next().find('span').html());
	
	// ввёденное значение процентов
	var enter_percent = Number($(this).html());

	// входящая цена
	var price_in = Number($(this).parent().prev().find('span').html());

	// если % меньше 0, то  % = 0
	if(enter_percent<0){
		enter_percent =0;
		$(this).html(enter_percent);
	}

	// если рабтает не мен, то замена исх. цены за услугу идёт и в поле менеджера и в поле снаба
	if($(this).parent().next().find('span').attr('contenteditable')=="true"){
		price_out_snab = price_out_men = round_s((100+enter_percent)*price_in/100);
		if(price_out_snab<min_price_real_for_all){
			enter_percent = percent_calc(min_price_real_for_all,price_in);
			$(this).html(enter_percent);
			price_out_snab = price_out_men = min_price_real_for_all;
		}
		$(this).parent().next().find('span').html(price_out_snab).parent().next().find('span').html(price_out_men);


	}else{ // работает мен

	}
	


});

// цена снаб
$(document).on('keyup', '.row_price_out_gen.uslugi_class.price_out_snab span', function(event) {
	event.preventDefault();
	/* Act on the event */
});

// цена мен
$(document).on('keyup', '.row_price_out_gen.uslugi_class.price_out_men span', function(event) {
	event.preventDefault();
	/* Act on the event */
});



//#################################################################
//##  РАБОТА ТАБЛИЦЫ РАСЧЕТОВ В НЕКАТАЛОЖНОЙ ПРОДУКИИ ##   END   ##
//#################################################################