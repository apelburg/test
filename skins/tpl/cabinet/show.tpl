<!-- begin skins/tpl/cabinet/show.tpl -->  
<link href="./skins/css/cabinet.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
/*
// Cвернуть строку запроса
$(document).on('click','#cabinet_general_content .show_hide',function() {
	$(this).parent().hide();
	$(this).parent().prev().find('td:nth-of-type(1)').addClass('show');
});

// Развернуть строку запроса
$(document).on('click','#cabinet_general_content .cabinett_row_show',function() {
	$('.query_detail').css('display','none');
	//$(this).removeClass('show');
	$(this).parent().next().show('fast');
});

*/


</script>
<div class="table" id="cabinet">
	<div class="row">
		<div class="cell" id="cabinet_left_coll_menu">
			<div id="cabinet_top_menu1"></div>
			<ul id="cabinet_left_menu">
				<?php echo $menu_left; ?>
			</ul>
		</div>
		<div class="cell" id="cabinet_central_panel">
			<div id="cabinet_top_menu">
				<ul id="central_menu">
					<?php echo $menu_central; ?>
				</ul>
			</div>
			<div id="cabinet_general_content">
				<table class="cabinet_general_content_row">
					<tr>
						<th id="show_allArt"></th>
						<th>Номер</th>
						<th>Дата/время</th>
						<th>Компания</th>
						<!-- <th>Клиент</th> -->
						<th>Сумма</th>
						<th>Статус мен.</th>
						<th>Статус снаб.</th>
					</tr>
				<?php echo $content; ?> 				
				</table>
			</div>
		</div>
	</div>
</div>
<!-- end skins/tpl/cabinet/show.tpl -->
 
