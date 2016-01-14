<!-- <?php echo __FILE__; ?> -- START-->
<div id="variant_content_block_<?php echo $key;?>" <?php echo $display_this_block; ?> class="variant_content_block<?php echo $show_archive_class;?>">
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
	<div id="variant_info_<?php echo $key; ?>" class="variant_info table">
		<div class="row">
			<div class="cell">
				<table class="calkulate_table">
					<tr>
						<th  style="width: 316px;">Стоимость товара (Руб)</th>
						<th>$ вход. (Руб)</th>
						<th>%</th>
						<th>$ исход. (Руб)</th>
						<th>прибыль (Руб)</th>
						<th class="edit_cell">ТЗ</th>
						<th class="del_cell"></th>
					</tr>
					<tr class="tirage_and_price_for_one">
						<td>1 шт.</td>
						<td class="row_tirage_in_one price_in"><span contenteditable="true" class="edit_span" ><?php echo $variant['price_in']; ?></span></td>
						<td rowspan="2"  class="percent_nacenki">
							<span contenteditable="true" class="edit_span"><?php 
							$per = ($variant['price_in']!= 0)?$variant['price_in']:0.09;
							echo round((($variant['price_out']-$variant['price_in'])*100/$per),2);
							?></span>
						</td>
						<td  class="row_price_out_one price_out"><span class="edit_span" contenteditable="true"><?php echo $variant['price_out']; ?></span></td>
						<td class="row_pribl_out_one pribl"><span><?php echo ($variant['price_out']-$variant['price_in']); ?></span></td>
						<td rowspan="2">
							<!-- <span class="edit_row_variants"></span> -->
						</td>
						<td rowspan="2"></td>
					</tr>
					<tr  class="tirage_and_price_for_all for_all">
						<td>тираж</td>
						<td class="row_tirage_in_gen price_in"><span class="price_in_all"><?php echo $sum_of_tirage_in;   ?></span></td>
						<td class="row_price_out_gen price_out"><span><?php echo $sum_of_tirage_out;  ?></span></td>
						<td class="row_pribl_out_gen pribl" ><span><?php echo $sum_prib_of_tirage; ?></span></td>
					</tr>
					<?php 
					
					$uslugi = $POSITION_GEN->POSITION_NO_CATALOG->get_uslugi_Database_Array($variant['id']);

					echo $POSITION_GEN->POSITION_CATALOG->uslugi_template_cat_Html($uslugi);
					?>
					<tr>
						<th colspan="7" class="type_row_calc_tbl">
							<div class="add_usl">Добавить услуги к этому варианту</div>
							<!-- <div class="add_usl all">Добавить услуги ко всем рабочим вариантам</div></th> -->
					</tr>
					<tr>
						<td colspan="7" class="table_spacer"> </td>
					</tr>
					<tr class="variant_calc_itogo">
						<td>ИТОГО:</td>
						<td><span></span></td>
						<td><span></span></td>
						<td><span></span></td>
						<td><span></span></td>
						<td></td>
						<td></td>
					</tr>
				</table>
			</div>
			<div class="cell size_card">
				<?php echo $get_size_table; ?>								
			</div>
		</div>
	</div>
</div>
<!-- <?php echo __FILE__; ?> -- END-->