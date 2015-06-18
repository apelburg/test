<link href="./skins/css/order_art_edit.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>

<!--//пригодится тут в будущем <script type="text/javascript" src="libs/js/forms__js.js"></script> -->
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





</script>
<div id="order_art_edit">
	<div id="info_string_on_query">
		<ul>
			<li id="back_to_string_of_claim"><a href="?page=client_folder&query_num=<?php echo  $order_num; ?>"></a></li>
			<li id="claim_number" data-order="<?php echo  $order_num_id; ?>">Запрос № <?php echo  $order_num; ?></li>
			<li id="claim_date"><span>от <?php echo $order_num_date; ?></span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	
			<li id="art_name_topic"><span>Тема:</span> <?php echo $art_name; ?></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul>
			<li title="порядковый номер позиции в заказе">Позиция № 1 (Это нужно????)</li>
			<li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span><?php echo $FORM->arr_type_product[$type_product]['name']; ?></li>
			<li><span>доп инфо: </span>Тендер</li>
			<li><span>снабженец: </span><?php echo Manager::get_snab_name_for_query_String($snab_id); ?></li>
			<li><span>статус позиции: </span>Расчитано</li>
			<?php
				echo $POSITION_NO_CAT->get_top_funcional_byttun_for_user_Html();
			?>
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
											<?php 
												echo $POSITION_NO_CAT->dop_info_no_cat_Html($dop_info_no_cat,$FORM,$type_product);
											?>
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

					
					<?php

					echo $POSITION_NO_CAT->get_all_on_calculation_Html();

					?>
					
				</div>
			</div>
		</div>
	</div>
	<?php echo $forum; ?>
</div>

