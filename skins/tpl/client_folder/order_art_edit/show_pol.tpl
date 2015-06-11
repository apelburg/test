<link href="./skins/css/order_art_edit.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="libs/js/forms__js.js"></script>
<!-- 
<script type="text/javascript" src="libs/js/order_art_edit.js"></script>
<script type="text/javascript" src="../libs/js/jquery.uploadify.min.js"></script>

<script type="text/javascript" src="../libs/js/jsArticulus.js"></script> -->

<script type="text/javascript">



$(document).on('click', '#all_variants_menu_pol .variant_name', function(event) {
	$('.variant_name').removeClass('checked');
	$(this).addClass('checked');
	var table_id = $(this).attr('data-cont_id');
	$('#variant_of_snab table').removeClass('show_table');
	$('#'+table_id).addClass('show_table');
});


// $(document).on('click', '#variant_of_snab table tr td', function(event) {
// 	$('#variant_of_snab').find('.checked_row').removeClass('checked_row');
// 	$(this).parent().find('td').addClass('checked_row');
// });



//отправка запроса на получение формы
$(document).keydown(function(e) {	
	if(e.keyCode == 192){//ESC	
		$.post('', {
			AJAX:'get_form'
		}, function(data, textStatus, xhr) {
			show_dialog(data);
			//alert(data);
		});
	}
});

// создание диалогового окна с формой заведения вариантов расчёта для снаба
function show_dialog(html){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	$.post('', $('#general_form_for_create_product form').serialize(), function(data, textStatus, xhr) {
				alert(data);
			});
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

</script>


<div id="order_art_edit">
	<div id="info_string_on_query">
		<ul>
			<li id="back_to_string_of_claim"></li>
			<li id="claim_number" data-order="<?php echo  $order_num_id; ?>">Запрос № <?php echo  $order_num; ?></li>
			<li id="claim_date"><span>от <?php echo $order_num_date; ?></span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	
			<li id="art_name_topic"><span>Тема:</span> <?php echo $art_name; ?></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul>
			<li title="порядковый номер позиции в заказе">Позиция № 1</li>
			<li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span>Полиграфия</li>
			<li><span>доп инфо: </span>Тендер</li>
			<li><span>снабженец: </span>Ольга Подгурская</li>
			<li><span>статус позиции: </span>Расчитано</li>
			<li class="status_art_right_class grey_border"><div><span>?</span></div></li>
			<li class="status_art_right_class"><div><span>Запросить пересчёт</span></div></li>
			<li class="status_art_right_class green"><div><span>принять расчёт</span></div></li>
		</ul>
	</div>
	<div class="table" id="order_art_edit_content_table" >
		<div class="row">			
			<div class="cell" id="order_art_edit_centr">
				<!--общая информация по позиции и комментарии к этой инфе -->
				<div id="edit_option_content">
					<div class="table" id="characteristics_and_delivery">
						<div class="row">
							<div class="cell  b_r" >
								<strong>Характеристики изделия:</strong>
								<div class="table" id="characteristics_art">
									<div class="row">
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell">Номенклатура</div>
													<div class="cell">Листовка</div>
												</div>
												<div class="row">
													<div class="cell">Название</div>
													<div class="cell">№1</div>
												</div>
												<div class="row">
													<div class="cell">Формат</div>
													<div class="cell">
														не менее 200*70мм, не более 210*75 мм
														<span class="snab_comment">
															<span class="slaches">/</span>
															считали 200*70мм
														</span>
													</div>
												</div>
												<div class="row">
													<div class="cell">Материал</div>
													<div class="cell">
														матовый двухсторонний мелованый картон плотность не менее 285г/кв.м.б не более 300
														<span class="snab_comment">
															<span class="slaches">/</span>картон мелованный 280 г/кв.м.
														</span>
													</div>
												</div>
												<div class="row">
													<div class="cell">Вид печати</div>
													<div class="cell">
															Офсет, 4+4
													</div>
												</div>
												<div class="row">
													<div class="cell">Изменение листа 1</div>
													<div class="cell">
															вырубка
													</div>
												</div>
												<div class="row">
													<div class="cell">Ламинат</div>
													<div class="cell">
														глянцевый 1+0/ нет /1+1
													</div>
												</div>
												<div class="row">
													<div class="cell">Дополнительно</div>
													<div class="cell">
														Печать с переменными данными (имя, подпись, текст поздравления)
													</div>
												</div>
												<div class="row">
													<div class="cell">Тираж</div>
													<div class="cell">
														300шт, 500шт
													</div>
												</div>
											</div>
										</div>
										
									</div>
								</div>								
							</div>
							<div class="cell left_bord">								
											<table>
												<tr><td colspan="2"><strong>Даты сдачи:</strong></td></tr>
												<tr>
													<td><span style="color: grey;">Расчёт:</span></td>
													<td>
														<span id="btn_date_std" class="btn_var_std">стандартно</span>
														<input type="text" name="date" id="datepicker1">
														<input type="text" name="time" id="timepicker1">
													</td>
												</tr>
												<tr>
													<td><span style="color: grey;">Отгрузка:</span></td>
													<td>
														<span id="btn_date_std" class="btn_var_std">стандартно</span>
														<input type="text" name="date" id="datepicker1">
														<input type="text" name="time" id="timepicker1">
													</td>
												</tr>
												<tr>
													<td><span style="color: grey;">Макет:</span></td>
													<td>
														<span id="btn_date_std" class="btn_var_std">стандартно</span>
														<input type="text" name="date" id="datepicker1">
														<input type="text" name="time" id="timepicker1">
													</td>
												</tr>											
												<tr>
													<td>Бюджет:</td>
													<td>
														стоимость для клиента не выше 140 рублей, короткая строка, ограничить кол-во символов
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<span class="snab_comment">
															<span class="slaches">/</span>
															140р для заказчика не реальный бюджет АКСар
														</span>
													</td>
												</tr>
											</table>

										
								
								
								
								
							</div>
						</div>
					</div>
				</div>
				<!--варианты расчётов от снабжения по позиции -->
				<div id="edit_variants_content">
					<div id="variants_name">
									<ul id="all_variants_menu_pol">
										<li data-cont_id="variant_content_table_0" class="variant_name checked">Расчёт снабжения от 07.02.2015</li>
										<li data-cont_id="variant_content_table_1"  class="variant_name ">Расчёт снабжения от 23.02.2015</li>
										<li data-cont_id="variant_content_table_2"  class="variant_name ">Итоговый расчёт для клиента</li>
										<li id="choose_end_variant">
											<span class="chenged_text">
												Выбрать окончательный
											</span>										
										</li>
									</ul>
						
					</div>
					<!-- вставка таблицы вариантов расчётов от снабжения -->
					<div id="variant_of_snab">
						<table id="variant_content_table_0" class="show_table">
							<tr>
								<th></th>
								<th>варианты</th>
								<th>тираж</th>
								<th>$ входящая</th>
								<th>%</th>
								<th>$ МИН исходящая</th>
								<th>подрядчик</th>
								<th>срок р/д</th>
								<th>комментарий снабжения</th>
							</tr>
							<tr>
								<td><span>X</span></td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td><span>6450.00</span>р</td>
								<td><span>10</span>%</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td>5</td>
								<td><input type="text" value="не стоит без ламината"></td>
							</tr>

						</table>
						<table id="variant_content_table_1" class="">
							<tr>
								<th></th>
								<th>варианты ламината</th>
								<th>тираж</th>
								<th>срок р/д</th>
								<th>возможная дата сдачи</th>
								<th>$ вход.</th>
								<th>$ МИН исход.</th>
								<th>подрядчик</th>
								<th></th>
								<th>комментарии и уточнения снабжения</th>
							</tr>
							<tr>
								<td>Вариант 1</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>10.03.15</td>
								<td><span>6450.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td></td>
								<td>не стоит без ламината</td>
							</tr>
							<tr>
								<td>Вариант 2</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>10.03.15</td>
								<td><span>6450.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td></td>
								<td>-</td>
							</tr>
							<tr>
								<td>Вариант 3</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>10.03.15</td>
								<td><span>6450.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td></td>
								<td>без ламината цена та же, что и с ламинатом</td>
							</tr>
							<tr>
								<td>Вариант 3</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>10.03.15</td>
								<td><span>6450.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Проект</td>
								<td></td>
								<td></td>
							</tr>
						</table>

						<table id="variant_content_table_2" class="">
							<tr>
								<th></th>
								<th>варианты ламината</th>
								<th>тираж</th>
								<th>срок р/д</th>
								<th>возможная дата сдачи</th>
								<th>$ вход.</th>
								<th>$ МИН исход.</th>
								<th>подрядчик</th>
								<th></th>
								<th>комментарии и уточнения снабжения</th>
							</tr>
							<tr>
								<td>Вариант 1</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>15.03.15</td>
								<td><span>645440.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td></td>
								<td>не стоит без ламината</td>
							</tr>
							<tr>
								<td>Вариант 2</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>10.05.15</td>
								<td><span>450.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td></td>
								<td>-</td>
							</tr>
							<tr>
								<td>Вариант 3</td>
								<td>глянцевый 1+0</td>
								<td>350шт</td>
								<td>5</td>
								<td>09.03.15</td>
								<td><span>6450.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Антан</td>
								<td></td>
								<td>без ламината цена та же, что и с ламинатом</td>
							</tr>
							<tr>
								<td>Вариант 3</td>
								<td>глянцевый 1+0</td>
								<td>300шт</td>
								<td>5</td>
								<td>14.09.15</td>
								<td><span>640.00</span>р</td>
								<td><span>8450.00</span>р</td>
								<td>Проект</td>
								<td></td>
								<td></td>
							</tr>
						</table>
					</div>
					<?php //echo $variants_content; ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $forum; ?>
</div>

