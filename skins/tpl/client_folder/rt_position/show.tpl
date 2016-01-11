<!-- <?php echo __FILE__; ?> -- START-->

<!-- стили -->
<link href="./skins/css/rt_position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/position.css" rel="stylesheet" type="text/css">
<link href="./skins/css/forum.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<!-- библиотеки -->
<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="./libs/js/classes/Base64Class.js"></script>
<script type="text/javascript" src="../libs/js/jquery.uploadify.min.js"></script>



<!-- старые скрипты -->
<script type="text/javascript" src="http://www.apelburg.ru/os/libs/js/classes/printCalculatorClass.js"></script>
<script type="text/javascript" src="http://www.apelburg.ru/os/libs/js/up_window_consructor.js"></script>
<script type="text/javascript" src="http://www.apelburg.ru/os/libs/js/classes/rtCalculatorClass.js"></script>

<script type="text/javascript" src="libs/js/rt_position.js"></script>
<script type="text/javascript" src="./libs/js/rt_position_gen.js"></script>

<!-- стандартный обработчик -->
<script type="text/javascript" src="../libs/js/standard_response_handler.js"></script>
<!-- скрипт страницы -->
<script type="text/javascript" src="./libs/js/jsPositionUniverasal.js"></script>

<?php
// 	echo '<pre>';
// 	print_r($POSITION->position['status']);
// 	echo '</pre>';
// $POSITION->position['status'];
// echo '<br>';
// echo $POSITION->user_access;
?>


<div id="order_art_edit">
	<div id="info_string_on_query">
		<ul>
			<li style="opacity:0" id="back_to_string_of_claim"><span href="?page=client_folder&query_num=<?=$POSITION->position['query_num'];?>&client_id=<?php echo $client_id; ?>"></span></li>
			<li id="claim_number" data-order="<?=$POSITION->position['id'];?>">Запрос № <?=$POSITION->position['query_num'];?></li>
			<li id="claim_date"><span>от <?=$POSITION->position['date_create'];?></span></li>
			
			<li id="query_theme_block"><span>Тема:</span> <input id="query_theme_input" class="query_theme" data-id="<?=$POSITION->position['RT_LIST_ID'];?>" type="text" value="<?=$POSITION->position['theme']?>" onclick="fff(this,'Введите тему');"></li>
			<li style="float:right"><span data-rt_list_query_num="<?=$POSITION->position['query_num'];?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($POSITION->position['query_num']); ?> "></span></li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul><!-- 
			<li title="порядковый номер позиции в заказе">Позиция № 1</li> -->
			<!-- <li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span>Каталог</li> -->
			<li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span><?=$product_type_RU;?></li>
			<li title="порядковый номер позиции"><span>Позиция № : </span><?=$POSITION->position['sort'];?></li>
			<li id="status_art_z"><div>Статус <span>В работе</span></div></li>
		</ul>
	</div>
	<div class="table" id="order_art_edit_content_table" >
		<div class="row">
			<div class="cell b_r" id="order_art_edit_left" <?php 
				if($POSITION->position['show_img'] == 0){
					echo ' style="width: 0px; opacity: 0;"';
				}
				?>>
				
				<?php
					echo $POSITION->getImage();
				?>
			</div>
			<div class="cell<?php
			// echo $POSITION->user_access.' '.$POSITION->position['status'];
				if($POSITION->position['status'] != 'in_work' && $POSITION->user_access != 1){
					echo ' not_edit';
				}
			 ?>" id="order_art_edit_centr" >
				<div id="ja--image-gallety-togle" data-id="<?php echo $POSITION->position['id']; ?>" <?php 
				if($POSITION->position['show_img'] == 0){
					echo 'class="hidden"';
				}
				?>  onclick="togleImageGallery(this)">
					<span class="triangle"></span>
				</div>
				<div id="edit_option_content"  style="display:block">
					<div class="table" id="characteristics_and_delivery">
						<div class="row">
							<div class="cell  b_r" >
								<?php 
									echo $POSITION->getCharacteristics();					
								?>
							</div>
							<div class="cell">
								<!-- <form id="fddtime_form">
								<div class="table" id="fddtime">
									<div class="row">
										<div class="cell">
											<strong>Дата отгрузки:</strong>
										</div>
										<div class="cell">
											<span id="btn_date_var" class="btn_var_std">варианты</span>
											<span id="btn_date_std" class="btn_var_std">стандартно</span>
											<input type="text" name="date" id="datepicker1">
											<input type="text" name="time" id="timepicker1">
											<input type="hidden" name="status_time_delivery" id="status_time_delivery">
										</div>
									</div>
									<div  class="row">
										<div class="cell">
											<strong>Изготовление р/д:</strong>
										</div>
										<div class="cell">
											<span id="btn_make_var" class="btn_var_std">варианты</span>
											<span id="btn_make_std" class="btn_var_std">стандартно</span>
											<input type="text" name="rd" id="fddtime_rd" value="10"> р/д
											
										</div>
									</div>
								</div>
								</form> -->
								
								<!-- <div id="technical_assignment">Техническое задание</div> -->
								
							</div>
						</div>
					</div>
				</div>
				<div id="edit_variants_content">
					<div id="variants_name">
						<table>
							<tr>
								<!-- <td>
									<ul id="new_variant_UL">
										<li id="new_variant">&nbsp;</li>
									</ul>
								</td> -->
								<td>
									<ul id="all_variants_menu">
										<!-- вставка кнопок вариантов -->
										<?php echo $POSITION->Variants->generate_variants_menu($variants_arr); ?>
									</ul>
								</td>
								<td>
									<ul>
										<li id="show_archive">
											<?php
												if(isset($_GET['show_archive'])){
													echo '<a data-true="1" href="'.str_replace('&show_archive', '', $_SERVER['REQUEST_URI']).'">Скрыть отказанные</a>';
												}else{
													echo '<a data-true="0" href="'.$_SERVER['REQUEST_URI'].'&show_archive">Показать отказанные</a>';

												}
											?>	
										</li>									
									</ul>
								</td>
							</tr>
						</table>
						
					</div>
					<!-- вставка блоков вариантов -->
					<?php echo $variants_content; ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo $forum; ?>
</div>
<!-- <?php echo __FILE__; ?> -- END-->