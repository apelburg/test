<link href="./skins/css/order_art_edit.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">

<script type="text/javascript" src="libs/js/jquery_ui/jquery.datetimepicker.js"></script>

<script type="text/javascript" src="libs/js/order_art_edit.js"></script>



<div id="order_art_edit">
	<div id="info_string_on_query">
		<ul>
			<li id="back_to_string_of_claim"></li>
			<li id="claim_number">Запрос №2585631</li>
			<li id="claim_date"><span>от 12.11.15 19:38</span></li>
			<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	
			<li id="art_name_topic"><span>Тема:</span> Футболка белая «T-bolka 140»</li>
		</ul>
	</div>
	<div id="number_position_and_type">
		<ul>
			<li title="порядковый номер позиции в заказе">Позиция № 1</li>
			<li title="каталог/полиграфия/товар клиента/сувениры под заказ"><span>Тип: </span>Каталог</li>
			<li id="status_art_z"><div>Статус <span>В работе</span></div></li>
		</ul>
	</div>
	<div class="table" id="order_art_edit_content_table" >
		<div class="row">
			<div class="cell b_r" id="order_art_edit_left" >
				<div id="articulusImages">
		            <?php //echo $color_variants_block; $alt = altAndTitle($name); ?>
		            
		            <div id="articulusImagesBigImg">
		                <div class="showImagegallery"></div>
		                <img id='img_for_item_<?php echo '$id'; ?>' src='<?php echo $images_data['main_img_src']; ?>' itemprop="image"  alt='
		                <?php echo '$alt'; ?>' title="<?php echo '$h1'; ?>" style='max-width: 286px;
		max-height: 300px;'>
		            </div>
		            <div id="articulusImagesPrevBigImg"> 
		                <?php echo $images_data['previews_block']; ?>                  
		            </div>
		        </div>
				<?php
					
				?>
			</div>
			<div class="cell" id="order_art_edit_centr">
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
													<div class="cell">Артикул</div>
													<div class="cell">375190.60</div>
												</div>
												<div class="row">
													<div class="cell">Номенклатура</div>
													<div class="cell">Футболка белая «T-bolka 140»</div>
												</div>
												<div class="row">
													<div class="cell">Бренд</div>
													<div class="cell">T-bolka 140</div>
												</div>
											</div>
										</div>
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell">Цвет</div>
													<div class="cell">белый</div>
												</div>
												<div class="row">
													<div class="cell">Материал</div>
													<div class="cell">хлопок</div>
												</div>
												<div class="row">
													<div class="cell">вид нанесения</div>
													<div class="cell">шелкография</div>
												</div>
											</div>
										</div>
									</div>
								</div>								
							</div>
							<div class="cell">
								<form id="fddtime_form">
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
											<!-- <input type="hidden" name="status_time_delivery" id="status_time_delivery"> -->
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
											<!-- <input type="hidden" name="status_time_make" id="status_time_make" > -->
										</div>
									</div>
								</div>
								</form>
								
								<div id="technical_assignment">Техническое задание</div>
								
							</div>
						</div>
					</div>
				</div>
				<div id="edit_variants_content">
					<div id="variants_name">
						<ul>
							<li id="new_variant">&nbsp;</li>
							<li class="variant_name checked">Вариант 1</li>
							<li class="variant_name">Вариант 2</li>
						</ul>
					</div>
					<div id="variants_dop_info">
						<table>
							<tr>
								<td>
									<strong>Тираж:</strong>
									<input type="text" class="tirage_var" value="300 шт"> + 
									<input type="text" class="dop_tirage_var" value="3">
									<span class="btn_var_std">ПЗ</span>
									<span class="btn_var_std">НПЗ</span>
								</td>
								<td>
									<strong>Дата отгрузки:</strong>
									<span class="btn_var_std">Стандартно</span>
									<input type="text" value="25.05.2015"> 
									<input type="text" value="15:00">
								</td>
								<td>
									<strong>Изготовление р/д:</strong>
									<span class="btn_var_std">Стандартно</span> 
									<input type="text" value="10"> р/д	
								</td>
							</tr>
						</table>
					</div>
					<div id="variant_info" class="table">
						<div class="row">
							<div class="cell">
								<table>
									<tr>
										<th>Стоимость товара</th>
										<th>$ вход.</th>
										<th>%</th>
										<th>$ исход.</th>
										<th>прибыль</th>
										<th>ред.</th>
										<th>del</th>
									</tr>
									<tr>
										<td>1 шт.</td>
										<td> 133,00р</td>
										<td rowspan="2">20%</td>
										<td>195,00р</td>
										<td>12,00</td>
										<td rowspan="2">ред.</td>
										<td rowspan="2"></td>
									</tr>
									<tr>
										<td>тираж</td>
										<td> 39 900,00р</td>
										<td>85 600,00р</td>
										<td>25 452,00</td>
									</tr>



									<tr>
										<th colspan="7"><span class="add_row">+</span>печать</th>
									</tr>
									<tr>
										<td>1 шт.</td>
										<td> 133,00р</td>
										<td rowspan="2">20%</td>
										<td>195,00р</td>
										<td>12,00</td>
										<td rowspan="2">ред.</td>
										<td rowspan="2"></td>
									</tr>
									<tr>
										<td>тираж</td>
										<td> 39 900,00р</td>
										<td>85 600,00р</td>
										<td>25 452,00</td>
									</tr>
									<tr>
										<th colspan="7"> + добавить ещё услуги</th>
									</tr>
									<tr>
										<td colspan="7" class="table_spacer"> </td>
									</tr>
									<tr id="variant_calc_itogo">
										<td>ИТОГО:</td>
										<td> 57 254,00р</td>
										<td>%</td>
										<td>78 864,15р</td>
										<td>35 365,45р</td>
										<td></td>
										<td></td>
									</tr>
								</table>
							</div>
							<div class="cell" id="size_card">
								<table>
									<tr>
										<th>Размеры</th>
										<th>на складе</th>
										<th>свободно</th>
										<th>тираж</th>
										<th>пригон</th>
									</tr>
									<tr>
										<td>S</td>
										<td>24<br><span>(в пути) 3000</span></td>
										<td>6</td>
										<td><input type="text"  value="250"></td>
										<td><input type="text"  value="2"></td>
									</tr>
									<tr>
										<td>M</td>
										<td>20<br><span>(в пути) 2600</span></td>
										<td>3</td>
										<td><input type="text"  value="150"></td>
										<td><input type="text"  value="2"></td>
									</tr>
									<tr>
										<td>XL</td>
										<td>20<br><span>(в пути) 1000</span></td>
										<td>3</td>
										<td><input type="text"  value="20"></td>
										<td><input type="text"  value="0"></td>
									</tr>
									<tr>
										<td>XXL</td>
										<td>20<br><span>(в пути) 1000</span></td>
										<td>3</td>
										<td><input type="text"  value="20"></td>
										<td><input type="text"  value="0"></td>
									</tr>
									<tr>
										<td>XXXL</td>
										<td>20<br><span>(в пути) 1000</span></td>
										<td>3</td>
										<td><input type="text"  value="20"></td>
										<td><input type="text"  value="0"></td>
									</tr>
									
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 


function get_weekends(){
	$n=1;
	$sut = 6;
	$sun = 7;
	//ближайшее воскресенье
	for ($i=1; $i <= 3 ; $i++) { 
		for ($k=1; $k <= 5; $k++) { 
			$sut2 = $sut+ $i*$k*7;
			$sun2 = $sun+ $i*$k*7;
			// субботы
			$d  = mktime(0, 0, 0, date("m"), date("d")+ $sut2 - date("N"), date("Y"));
			if($n>1){echo ', ';}else{$n++;}
			echo "'".date('d.m.Y', $d)."'";
			// воскресенья

			$d  = mktime(0, 0, 0, date("m"), date("d")+ $sun2 - date("N"), date("Y"));
			echo ",'".date('d.m.Y', $d)."'";
		}
	}
}

// echo get_weekends();
?>