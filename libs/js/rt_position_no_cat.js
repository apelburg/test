// выбор группы вариантов по статусам
$(document).on('click', '#all_variants_menu_pol .variant_name', function(event) {
	$('.variant_name').removeClass('checked');
	$(this).addClass('checked');
	var table_id = $(this).attr('data-cont_id');
	$('#variant_of_snab .variant_content_table').css({'display':'none'});
	$('#'+table_id).css({'display':'block'});
	// обновляем верхние кнопки
	opcional_top_buttons();

	// если выбранных строк нет - автоклик по первому варианту
	if($('#'+$(this).attr('data-cont_id')+' .show_table tr.checked').length==0){
		$('#'+$(this).attr('data-cont_id')+' .show_table tr').eq(1).find('td').eq(2).click();
	}else{
		$('#'+$(this).attr('data-cont_id')+' .show_table tr.checked').find('td').eq(2).click();
	}
});

// клик по первому варианту при загрузке страницы
$(document).ready(function() {
	var obj = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id') + ' .show_table tr:nth-of-type(2) td:nth-of-type(2)');
	obj.click();
	// $('#inform_for_variant_number').html(obj.html());
});

// клик по варианту расчёта
$(document).on('click', '#variant_of_snab table.show_table tr td', function(event) {
	// скрываем все блоки с расширенной информацие о варианте
	$('.variant_info').css({'display':'none'});

	// выделяем выбранный вариант
	$(this).parent().parent().find('.checked').removeClass('checked');
	
	// $(this).parent().parent().find('td').css({'background':'none'});
	// $(this).parent().find('td').css({'background':'#c7c8ca'});

	// получаем id строки варианта
	var id_row = $(this).parent().addClass('checked').attr('data-id');

	// показываем расширенную информацию по выбранному варианту
	$('#variant_info_'+id_row).css({'display':'block'});

	// трасляция подробной информации в правом верхнем углу экрана, отмечаем id строки dop_data в хар-ках изделия
	//$('#inform_for_variant').html($('#variant_info_'+id_row+ ' .table.inform_for_variant').parent().html()).attr('data-id',id_row);

	// пишем номер варианта в хар-ках изделия
	//$('#inform_for_variant_number').html($(this).parent().find('td').eq(1).html());

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
	itogo_price_out_snab += Number($('#variant_info_'+$('#' + $('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr.checked').attr('data-id')+' .calkulate_table').find('.row_price_out_gen.price_out_snab.tirage span').html());
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
	var price_out_for_all_snab = Number(object.parent().parent().next().find('.row_price_out_gen.price_out_snab.tirage span').html()); 
	
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
		object.parent().parent().next().find('.row_price_out_gen.price_out_snab.tirage span').html(round_s(price_for_one*quantity));

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
	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
}
/*--1--END--*/


/*--2--START--*/
// редактирование вход. цены за тираж
$(document).on('keyup', '.row_tirage_in_gen.price_in.tir span', function(event) {
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

	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
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
		$(this).parent().parent().next().find('.row_price_out_gen.price_out_snab.tirage span').html(price_out_for_all_snab).parent().parent().find('.row_price_out_gen.price_out_men span').html(price_out_for_all_snab);

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
	calc_pribl_tir();

	recalculate_table_price_Itogo();


	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
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
		obj.parent().parent().next().find('.row_price_out_gen.price_out_snab.tirage span').html(price_out_all_snab).parent().next().find('span').html(price_out_all_snab);
	
	}else{		
		price_out_all_snab = round_s(price_out_one_snab*quantity);
		obj.parent().parent().next().find('.row_price_out_gen.price_out_snab.tirage span').html(price_out_all_snab).parent().next().find('span').html(price_out_all_snab);
		obj.parent().next().find('span').html(price_out_one_snab);	
		
		
	}
	// расчёт процентов товара	
	calc_percent();
	calc_pribl_tir();
	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
}
/*--4--END--*/


/*--5--START--*/
// редактирование мин исходящей цены за тираж SNAB
$(document).on('keyup', '.row_price_out_gen.price_out_snab.tirage span', function(event) {
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

	calc_percent();

	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
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
	obj.parent().parent().next().find('.row_price_out_gen.price_out_men span').html(price_out_all_men);
	
	// подсчёт процентов
	calc_percent();
	// посчёт прибыли
	calc_pribl_tir();

	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
}
/*--6--END--*/



/*--7--START--*/
// редактирование исходящей цены за тираж MEN
$(document).on('keyup', '.row_price_out_gen.price_out_men.tirage span', function(event) {
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

	// сохраняем значения тиража в dop_data
	time_to_save('save_dop_data',$('.calkulate_table:visible'));
});

/*--7--END--*/




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







// цена снаб
$(document).on('keyup', '.row_price_out_gen.uslugi_class.price_out_snab span', function(event) {
	var price_out_snab = Number($(this).html());

	// получаем реальные значения цены данной услуги, цены взяты из прайса
	// если эти значения равны, то услуга применяется к тиражу, если нет то к единице товара
	var min_price_real_for_one = Number($(this).parent().attr('data-real_min_price_for_one'));
	var min_price_real_for_all = Number($(this).parent().attr('data-real_min_price_for_all'));

	// если указанная цена меньше указанной в прайсе
	if(price_out_snab<min_price_real_for_all){
	console.log('65');
		// меняем то, что навводил снаб на минимальное значение
		$(this).html(min_price_real_for_all).parent().next().find('span').html(min_price_real_for_all);
		//price_out_snab = min_price_real_for_all;
	}else{
		$(this).html(price_out_snab).parent().next().find('span').html(price_out_snab);

	}

	// считаем прибыль
	calc_usl_pribl($(this).parent().parent());

	// считаем %
	calc_usl_percent($(this).parent().parent());

	// подсчёт ИТОГО
	recalculate_table_price_Itogo();

	// добавляем маркер к строке которое мы отредактировали
	$(this).parent().parent().addClass('editing');
	// сохраняем значения тиража в dop_uslugi
	time_to_save('save_dop_dop_usluga',$('.calkulate_table:visible'));
});






// сохраняет данные в таблицу dop_data
function save_dop_data(obj){		
	var price_in = Number(obj.find('.row_tirage_in_gen.price_in span').html());
	var price_out_snab = Number(obj.find('.row_price_out_gen.price_out_snab.tirage span').html());
	var price_out = Number(obj.find('.row_price_out_gen.price_out_men.tirage span').html());
	var dop_data_id = obj.find('.tirage_and_price_for_all.for_all').attr('data-dop_data_id');
	$.post('', { 
			AJAX: 'save_new_price_dop_data',
			price_in: price_in,
			price_out_snab: price_out_snab,
			price_out: price_out,
			dop_data_id:dop_data_id
		}
		, function(data, textStatus, xhr) {
		console.log(data);
	});
}




// сохраняет данные в таблицу dop_data
function save_dop_dop_usluga(obj){	
	console.log(obj);
	var data = {};
	obj.find('.calculate.calculate_usl.editing').each(function(index, el) {
		var dop_usl_id = $(this).attr('data-dop_uslugi_id');
		var price_out_snab = $(this).find('.row_price_out_gen.uslugi_class.price_out_snab span').html();
		var price_out = $(this).find('.row_price_out_gen.uslugi_class.price_out_men span').html();
		var price_in = Number($(this).find('.row_tirage_in_gen.uslugi_class.price_in span').html());
		var quantity = Number($('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' table tr.checked td:nth-of-type(3) span').html());
		var for_how = $(this).attr('data-for_how');
		// console.log(for_how);
		// console.log(quantity);
		// console.log(price_in);
		if(for_how == 'for_one'){
			price_out_snab = round_s(price_out_snab/quantity);
			price_out = round_s(price_out/quantity);
			price_in = round_s(price_in/quantity);
		}

		// console.log(price_out_snab);
		// console.log(price_out);

		data[$(this).attr('data-dop_uslugi_id')] = {"price_out":price_out,"price_in":price_in,"price_out_snab":price_out_snab};		
		$(this).removeClass('editing');
	});
	// console.log(data);
	$.post('', { 
			AJAX: 'save_new_price_dop_uslugi',
			data:data
		}
		, function(data, textStatus, xhr) {
		console.log(data);
	});
}

//#################################################################
//##  РАБОТА ТАБЛИЦЫ РАСЧЕТОВ В НЕКАТАЛОЖНОЙ ПРОДУКИИ ##   END   ##
//#################################################################


// НАЗНАЧЕНИЕ ПОСТАВЩИКА
$(document).on('click', '.change_supplier', function(event) {
	$(this).attr('id', 'chose_supplier_id');
	chose_supplier($(this));
});

function chose_supplier(obj){

	$.post('', {AJAX:'chose_supplier',already_chosen:$('#chose_supplier_id').attr('data-id')}, function(data, textStatus, xhr) {
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


// КОММЕНТАРИИ ОТ СНАБЖЕНИЯ
$(document).on('keyup', '.edit_snab_comment', function(event) {
	timing_save_comment('save_comment_snab',$(this));	
});
// функция тайминга отправки запросов AJAX при сохранении данных
function timing_save_comment(fancName,obj){
	//если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
	if(!obj.hasClass('saved')){
		
		
		window[fancName](obj);

		

		// пишем запрет на save
		obj.addClass('saved');
		// снимаем запрет на через n времени
		// var time = 2000;
		
		// setTimeout(function(){obj.removeClass('saved')}, time);				
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
				
				setTimeout(function(){timing_save_comment(fancName,$('.'+fancName).eq(index));// обнуляем очередь
		if(obj.hasClass(fancName)){obj.removeClass(fancName);}}, time);	
			});
			
		}		
	}
}

//сохраняет коменты снаба, на вход подаётся объект поля (не imput)
function save_comment_snab(obj){
	$.post('', {
		AJAX: 'edit_snab_comment',
		note: obj.html(),
		id_dop_data: obj.parent().parent().attr('data-id')
	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
		obj.removeClass('saved');
		
	});
}


$(document).on('keyup', '.change_srok', function(event) {
	timing_save_comment('save_worke_days',$(this))
});
function save_worke_days(obj){
	$.post('', {
		AJAX: 'edit_work_days',
		work_days: obj.html(),
		id_dop_data: obj.parent().parent().attr('data-id')
	}, function(data, textStatus, xhr) {
		/*optional stuff to do after success */
		obj.removeClass('saved');
		
	});	
}


jQuery(document).ready(function($) {
	// дата необходимого наличия макета
	$('.chenge_maket_date input').datetimepicker({
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
			// var id_variant = $('#variants_name .variant_name.checked ').attr('data-cont_id');
			// $('#'+id_variant+' .timepicker2').show().val(''); // показать поле время
			// $('#'+id_variant+' .fddtime_rd2').val('');
			// $('#'+id_variant+' .btn_var_std[name="std"]').removeClass('checked');		


			// получение данных для отправки на сервер
			var id_dop_data = $input.parent().parent().attr('data-id');
			//var row_id = $('#claim_number').attr('data-order');	
			var maket_date = $input.val();

			// alert(id);

			$.post('', {
				AJAX: 'change_maket_date',
				id_dop_data: id_dop_data,
				maket_date: maket_date
			}, function(data, textStatus, xhr) {
				/*optional stuff to do after success */
			},"json");
		},
		format:'d.m.Y',
	});	
});


// редактирование полей описания варианта
$(document).on('keyup', '.inform_for_variant .cell', function(event) {
	// save_no_cat_json($(this).parent().parent().parent())
	timing_save_no_cat_json('save_no_cat_json',$(this).parent().parent().parent())
	
});

// сохраняем хар-ки варианта
function save_no_cat_json(obj){
	var arr = {};
	obj.find('.row').each(function(index, el) {
		// console.log();
		// console.log($(this).find('.cell').eq(1).attr('data-type') +' = '+$(this).find('.cell').eq(1).html());
		arr[$(this).find('.cell').eq(1).attr('data-type')] = $(this).find('.cell').eq(1).html();

	});
	console.log(obj);
	// var type = $(this).attr('data-type');
	var dop_data_id = obj.attr('data-id');
	// копируем отредактированную таблицу в скрытую область html
	//$('#variant_info_'+dop_data_id+' .inform_for_variant').html(obj.find('.inform_for_variant').html())


	$.post('', {
				AJAX: 'change_no_cat_json',
				id_dop_data: dop_data_id,
				// type: type,
				data: arr
			}, function(data, textStatus, xhr) {
				obj.removeClass('saved');
				// обнуляем очередь				
			},"json");
}

// тайминг сохранения хар-к варианта
function timing_save_no_cat_json(fancName,obj){
	//если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
	if(!obj.hasClass('saved')){
		
		
		window[fancName](obj);		

		// пишем запрет на save
		obj.addClass('saved');
		// снимаем запрет на через n времени
		// var time = 2000;
		
		// setTimeout(function(){obj.removeClass('saved')}, time);				
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
				
				setTimeout(function(){
					timing_save_no_cat_json(fancName,$('#inform_for_variant'));// обнуляем очередь
					if(obj.hasClass(fancName)){// убиваем метку очереди
						obj.removeClass(fancName);
					}

				}, time);	
			});
			
		}		
	}
}

//#####################################################
//#################   КНОПКИ ВЕРХ   ###################
//#####################################################

// функция подставновки верхних опциональных кнопок 
function opcional_top_buttons(){
	// удаляем такие кнопки, если они есть
	$('#number_position_and_type ul .buttons_top_1').remove();

	// подставляем кнопки из скрытого дива соответствующего текущему разделу
	var buttons = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .hidden_top_buttons').html();
	$('#number_position_and_type ul').append(buttons);
}

// обновляем верхние кнопки
$(document).ready(function($) {
	// обновляем верхние кнопки
	opcional_top_buttons();
});


// ОТРАБОТКА КНОПОК статусов между меном и снабом
$(document).on('click', '.status_art_right_class', function(event) {
	window_preload_add();
	var new_status = $(this).attr('data-send_status');
	$.post('', {
		AJAX: 'change_status_gl',
		variants_arr: get_activ_tbl_variants_id(),
		new_status:new_status,
		old_status:$('#all_variants_menu_pol li.checked').attr('data-status'),
		query_id:$('#info_string_on_query').attr('data-id'),

	}, function(data, textStatus, xhr) {
		standard_response_handler(data);	
	},'json');
});

// ОТРАБОТКА КНОПКИ ПАУЗЫ
$(document).on('click', '#number_position_and_type ul .status_art_right_class_pause', function(event) {
	window_preload_add();
	$.post('', {
		AJAX: 'change_status_gl_pause',
		variants_arr: get_activ_tbl_variants_id(),
		status:$('#all_variants_menu_pol .variant_name.checked').attr('data-status')
	}, function(data, textStatus, xhr) {
		// В ВЕРСИИ 1.1 будем вносить правки в html в соответствии с ответом от сервера
		// сейчас при получении ответа - просто перегружаем страницу яваскриптом
		if(data['response']=='OK'){
			location.reload();
		}else{
			alert('что-то пошло не так');
		}
	},'json');
});


// получаем id вариантов активной таблицы с вариантами
function get_activ_tbl_variants_id(){
	var arr = new Array();

	$('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .show_table tr').each(function(index, el) {
		if( $(this).attr('data-id')){
			console.log($(this).attr('data-id'));
			arr.push($(this).attr('data-id'));
		}
	});
	return arr;
}


// ***********************************
// ПИСЬМО ПОСТАВЩИКУ ******* START
$(document).on('click', '.create_text_mail_for_supplier', function(event) {
	var title = 'Тут вы можете скопировать описание по всем вариантам активной вкладки';
	var data = $('#'+$('#all_variants_menu_pol .variant_name.checked').attr('data-cont_id')+' .text_for_send_mail').html();
	show_dialog_and_send_mail_text(data,title,$(window).height()/100*90);
});

// показать окно
function show_dialog_and_send_mail_text(html,title,height){
	height_window = height || 'auto';
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	$('#dialog_gen_window_form').dialog( "destroy" );
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
// ПИСЬМО ПОСТАВЩИКУ ******* END
// ***********************************

