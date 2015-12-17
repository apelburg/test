<!--<?php echo __DIR__; ?>/calculate_tbl.tpl -- START-->
<?php 
	// echo date('d.m.Y H:i',time());
	// phpinfo();
?>
<div id="variant_content_block_<?php echo $key;?>" <?php echo $display_this_block; ?> class="variant_content_block<?php echo $show_archive_class;?>" data-id="<?=$variant['id'];?>">
	<!-- таблица с тиражом START -->
	<div id="variants_dop_info_<?php echo $key; ?>" class="variants_dop_info">
		<table>
			<tr class="tirage_option_and_date_print">
				<td class="tirage_buttons">
					<strong>Тираж:</strong>
					<input type="text" class="tirage_var" id="tirage_var_<?php echo $key;?>" value="<?php echo $sum_tir; ?>"> + 
					<input type="text" class="dop_tirage_var" id="dop_tirage_var_<?php echo $key;?>" value="<?php echo $sum_dop; ?>">
					<span class="btn_var_std <?php echo $print_z; ?>" name="pz">ПЗ</span>
					<span class="btn_var_std <?php echo $print_z_no; ?>" name="npz">НПЗ</span>
				</td>
				<td>
					
					<?php echo Position_catalog::get_select_shipping_type($variant) ?>						
					<span class="type_specificate-info date" <?php echo $shipping_type__show_date;?>>
						<!-- <strong>Дата отгрузки:</strong> -->
						<!-- <span class="btn_var_std">Стандартно</span> -->
						<input type="text" class="datepicker2" name="datepicker2" value="<?php echo ($variant['shipping_date']!="00.00.0000")?$variant['shipping_date']:''; ?>"  placeholder="дата"> 
						<?php
						if($variant['shipping_date']!="00.00.0000"){
							if($variant['shipping_time']!="00:00:00"){
								echo '<input type="text" class="timepicker2" placeholder="время" name="timepicker2" value="'.$variant['shipping_time'].'">';
							}else{
								echo '<input type="text" placeholder="время" class="timepicker2" name="timepicker2" value="">';
							}
						}else{
							echo '<input type="text" class="timepicker2" name="timepicker2" value=""  placeholder="время" style="display:none">';
						}

						?>
					</span>

					<span class="type_specificate-info rd" <?php echo $shipping_type__show_rd;?>>
						<!-- <strong>Изготовление р/д:</strong> -->
						<!-- <span class="btn_var_std <?php //echo $std_time_print;?>" name="std">Стандартно</span>  -->
						<input type="text" class="fddtime_rd2" name="fddtime_rd2" value="<?php echo $variant['work_days']; ?>"> р/д
					</span>

					
					
				</td>
				<td>
					
					
				</td>
			<tr>
		</table>
	</div>
	<!-- таблица с тиражом END -->
	<div id="variant_info_<?php echo $key; ?>" class="variant_info table">
		<div class="row">
			<div class="cell">
				<table class="calkulate_table">
					<tr>
						<th  style="width: 316px;"></th>
						<th>тираж</th>
						<th>входящая<br>(штука)</th>
						<th>наценка<br>&nbsp;</th>
						<th>исходящая<br>(штука)</th>
						<th class="js--button-out_ptice_for_tirage for_tir"><div class="">исходящая
							<br>(сумма)</div></th>
						<th>маржа<br>&nbsp;</th>
						<th class="edit_cell">ТЗ<br>&nbsp;</th>
						<th class="del_cell">del<br>&nbsp;</th>
					</tr>
					<tr class="variant_calc_itogo">
						<td>строка итого:</td>
						<td><span></span></td>
						<td><span></span></td>
						<td><span></span></td>
						<td><span></span></td>
						<td><span></span></td>
						<td><span></span></td>
						<td></td>
						<td></td>
					</tr>
					<tr class="tirage_and_price_for_one">
						<td>Товар</td>
						<td><?=$variant['quantity'];?></td>
						<td class="row_tirage_in_one price_in"><input type="text" value="<?=$variant['price_in'];?>"></td>
						
						<td class="percent_nacenki js--calculate_tbl-edit_percent" data-val="<?=$variant['discount'];?>"  data-id="<?=$variant['id'];?>">
							<?php
								// расчет исходящей цены на товар
								$price_out = $POSITION->round_money(($variant['discount'] != 0 )? (($variant['price_out']/100)*(100 + $variant['discount'])) : $variant['price_out'] );
							?>

							<span class=""><?=$variant['discount'];?>%</span>
							<?php
								if($price_out != $variant['price_out']){
									echo '<span class="greyText">'.$variant['price_out'].'</span>';
								}
							?>
						</td>
						<td  class="row_price_out_one price_out">	
							<input type="text" disabled="disabled" value="<?php echo $price_out ?>">							
						</td>

							

						<?php
							// сумма исх / вход
							$price_out_summ_out = $POSITION->round_money($variant['quantity']*$price_out);
							$price_out_summ_in = $POSITION->round_money($variant['quantity']*$variant['price_in']);
						?>
						<td class="price_out_summ for_out" data-for_in="<?=$price_out_summ_in;?>" data-for_out="<?=$price_out_summ_out;?>">
							<span class="for_out">
								<?=$price_out_summ_out;?>
							</span>
							<span class="for_in">
								<?=$price_out_summ_in;?>
							</span>
						</td>
						<?php
							// на всякий случай посчитано для штуки и для тиража,
							// вдруг понадобится задействовать эту колонку в переключении 
							// при клике на сумму

							// по умолчанию стоит цена за тираж
							$pribl_for_one = $POSITION->round_money($price_out - $variant['price_in']);
							$pribl_for_tir = $POSITION->round_money($variant['quantity']*($price_out - $variant['price_in']));
						?>
						<td class="row_pribl_out_one pribl" data-for_tir="<?=$pribl_for_tir;?>" data-for_one="<?=$pribl_for_one;?>">
							<span>
								<?=$pribl_for_tir;?>
							</span>
						</td>
						<td></td>
					</tr>
					<?php 
					
					$uslugi = $POSITION->Variants->getServices($variant['id']);

					echo $POSITION->Variants->Services->htmlTemplate($uslugi,$variant);
					?>
					<tr>
						<th colspan="9" class="type_row_calc_tbl">
							<div class="add_usl">Добавить услуги к этому варианту</div>
							<!-- <div class="add_usl all">Добавить услуги ко всем рабочим вариантам</div></th> -->
					</tr>				
				</table>
			</div>
			<div class="cell size_card">
				<?php echo $get_size_table; ?>								
			</div>
		</div>
	</div>
</div>
<!-- <?php echo __DIR__; ?>/calculate_tbl.tpl -- END