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
					<strong>Дата отгрузки:</strong>
					<!-- <span class="btn_var_std">Стандартно</span> -->
					<input type="text" class="datepicker2" name="datepicker2" value="<?php echo ($value['shipping_date']!="00.00.0000")?$value['shipping_date']:''; ?>"> 
					<?php
					if($value['shipping_date']!="00.00.0000"){
						if($value['shipping_time']!="00:00:00"){
							echo '<input type="text" class="timepicker2" name="timepicker2" value="'.$value['shipping_time'].'">';
						}else{
							echo '<input type="text" class="timepicker2" name="timepicker2" value="">';
						}
					}else{
						echo '<input type="text" class="timepicker2" name="timepicker2" value="" style="display:none">';
					}

					?>
					
				</td>
				<td>
					<strong>Изготовление р/д:</strong>
					<span class="btn_var_std <?php echo $std_time_print;?>" name="std">Стандартно</span> 
					<input type="text" class="fddtime_rd2" name="fddtime_rd2" value="<?php echo $value['standart']; ?>"> р/д</td>
			<tr>
		</table>
	</div>
					<div id="variant_info_<?php echo $key; ?>" class="variant_info table">
						<div class="row">
							<div class="cell">
								<table class="calkulate_table">
									<tr>
										<th>Стоимость товара</th>
										<th>$ вход.</th>
										<th>%</th>
										<th>$ исход.</th>
										<th>прибыль</th>
										<th class="edit_cell">ред.</th>
										<th class="del_cell">del</th>
									</tr>
									<tr>
										<td>1 шт.</td>
										<td contenteditable="true"><?php echo $value['price_in']; ?></td>
										<td rowspan="2">
											<?php 
											echo round((($value['price_out']-$value['price_in'])*100/$value['price_in']),2).'%';
											?>
										</td>
										<td><?php echo $value['price_out']; ?></td>
										<td>12,00</td>
										<td rowspan="2">
											<!-- <span class="edit_row_variants"></span> -->
										</td>
										<td rowspan="2"></td>
									</tr>
									<tr>
										<td>тираж</td>
										<td><?php echo $sum_of_tirage_in;   ?></td>
										<td><?php echo $sum_of_tirage_out;  ?></td>
										<td><?php echo $sum_prib_of_tirage; ?></td>
									</tr>
									<tr>
										<th colspan="7"><span class="add_row">+</span>печать</th>
									</tr>
									<tr>
										<td>Термотрансфер, 1цв</td>
										<td> 133,00р</td>
										<td rowspan="2">20%</td>
										<td>195,00р</td>
										<td>12,00</td>
										<td rowspan="2">
											<span class="edit_row_variants"></span>
										</td>
										<td rowspan="2">
											<span class="del_row_variants"></span>
										</td>
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
							<div class="cell size_card">
								<?php echo $get_size_table; ?>
							</div>
						</div>
					</div>
</div>