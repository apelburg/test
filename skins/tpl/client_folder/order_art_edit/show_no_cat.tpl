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
	console.log(quantity);
	$('#dialog_gen_window_form form input[name="quantity"]').val(quantity);
	$('#dialog_gen_window_form form input[name="id_uslugi"]').val(id);
	$('#dialog_gen_window_form form input[name="dop_row_id"]').val(dop_row_id);
});


function show_dialog_and_send_POST_window(html,title){
	var buttons = new Array();
	buttons.push({
	    text: 'OK',
	    click: function() {
	    	var serialize = $('#dialog_gen_window_form form').serialize();
	    	
	    	$('#general_form_for_create_product .pad:hidden').remove();
		    $.post('', serialize, function(data, textStatus, xhr) {
				if(data['response']=='show_new_window'){
					title = data['title'];
					show_dialog_and_send_POST_window(data['html']);
				}else{
					$('#dialog_gen_window_form').dialog( "destroy" );
					console.log(data['response']);
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
								<strong>Запрос, полная информация:</strong>
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
								<div id="inform_for_variant_head">Вариант № <span id="inform_for_variant_number">1</span>, характеристика изделия:</div>
								<div id="inform_for_variant"></div>
											
							</div>
						</div>
					</div>
				</div>
				<!--варианты расчётов от снабжения по позиции -->
				<div id="edit_variants_content">

					
					<?php

					echo $POSITION_NO_CAT->get_all_on_calculation_Html($type_product);

					?>
					
				</div>
			</div>
		</div>
	</div>
	<?php echo $forum; ?>
</div>

