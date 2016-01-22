				<script type="text/javascript">
					$(document).keydown(function(event) {
					if(event.keyCode == 13) {
						if( $("#js--window_client_sherch_form input").is(":focus") ){
							event.preventDefault();
					        search_and_show_client_list();
					        return false;
						}

						// если открыто окно поиска клиентов
						if($('#chose_client_tbl .checked').length>0){
							
							$('#chose_client_tbl').parent().parent().find('.ui-dialog-buttonpane .ui-dialog-buttonset button').click();
						}
					}
				});
				</script>
				<div class="search_div">
				<form>
					<?php

						foreach ($_GET as $key => $value) {
							if($key == 'page'){
								$value = 'clients';
							}
							if($key == 'section'){
								$value = 'clients_list';
							}
							echo '<input type="hidden" value="'.$value.'" name="'.$key.'">';
						}
						if(!isset($_GET['section'])){
							echo '<input type="hidden" value="clients_list" name="section">';
						}

					?>
                    <div class="search_cap">Поиск:</div>
                    <div class="search_field">                    
                        <input id="search_query" placeholder="по клиентам" type="text" onclick="delete_alert_win();" name="search" value="<?php echo (isset($_GET['search']))? $_GET['search'] : ''; ?>"><div class="undo_btn"><a href="#"  onclick="return  clear_search_input();">&#215;</a></div></div>
                    <input type="button" onclick="submit.form()" style="border: 0;
    width: 53px;
    height: 27px;" value="" class="search_button" >
                    <div class="clear_div"></div>
                    </form>
                </div>