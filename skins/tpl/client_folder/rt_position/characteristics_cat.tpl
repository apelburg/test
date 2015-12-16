
								<strong>Характеристики изделия:</strong>
								<div class="table" id="characteristics_art">
									<div class="row">
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell"><a target="_blank" href="<?php echo identify_supplier_href($articul['art']);  ?>">Артикул</a> <?=$link_of_the_site;?></div>
													<div class="cell"><?php echo $this->position['art']; ?></div>
												</div>
												<div class="row">
													<div class="cell">Номенклатура</div>
													<div class="cell"><?php echo $this->position['name']; ?></div>
												</div>
												<div class="row">
													<div class="cell">Бренд</div>
													<div class="cell"><?php
															echo $this->info['brand']; 
														?></div>
												</div>
												<div class="row">
													<div class="cell">Резерв</div>
													<div class="cell">
														<input type="text" id="rezerv_save" data-id="<?php echo $this->position['id']; ?>" value="<?php echo base64_decode($this->position['number_rezerv']); ?>" placeholder="№ резерва">
														</div>
												</div>
											</div>
										</div>
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell">Цвет</div>
													<div class="cell"><?php 
													echo implode(", ", $this->color); ?></div>
												</div>
												<div class="row">
													<div class="cell">Материал</div>
													<div class="cell"><?php echo implode(", ", $this->material); ?></div>
												</div>
												<div class="row">
													<div class="cell">вид нанесения</div>
													<div class="cell"><?php echo $this->get_print_names_string(); ?></div>
												</div>
											</div>
										</div>
									</div>
								</div>								
							