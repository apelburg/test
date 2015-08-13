<!-- begin skins/tpl/clients/client_details_field_general.tpl -->     
<style type="text/css">
#order_art_edit{font-family: Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 12px}


#info_string_on_query{ background-color: #5a5c61; color: #fff;}

#info_string_on_query ul{ padding: 0; list-style: none; margin: 0}
#info_string_on_query ul li{ display: inline-block;padding: 5px 10px;}
#back_to_string_of_claim a{width: 32px;
  height: 24px;
  cursor: pointer;
  background: url('../../skins/images/img_design/back_art.png') no-repeat;
  /* float: left; */
  position: absolute;
  z-index: 3;
  /* top: 61px; */
  margin-top: -5px;
  background-position-x: 3px;}
</style>

     
<div id="order_art_edit">
<div id="info_string_on_query">
		<ul>
			<li id="back_to_string_of_claim"></li>
			<li id="claim_number">Запрос №<?php  echo $query_num; ?></li>
			<li id="claim_date"><span>от <?php echo $create_time; ?></span></li>
			<!--<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	-->
			<li id="art_name_topic"><span>Тема:</span> <?php echo $theme_block; ?></li>
            <li style="float:right"><span data-rt_list_query_num="<?php  echo $query_num; ?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($query_num); ?> "></span></li>
            <li style="float:right"><?php  echo $cont_face; ?></li>
		</ul>
	</div>
	<div id="options_bar" style="background-color:#92b73e;">
		<ul>
			<!--<li>Позиции № 1</li>
			<li>В работе select</li>
			<li>Каталожные</li>
            <li>Не принятые</li>
            <li>2 п</li>-->
            <li><a href="<?php  echo HOST; ?>/?page=client_folder&section=business_offers&query_num=<?php  echo $query_num; ?>&client_id=<?php  echo $client_id; ?>" style="color:#FFFFFF;">Коммерческие предложения</a></li>
            <li><a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&client_id=<?php  echo $client_id; ?>" style="color:#FFFFFF;">Договоры</a></li>
		</ul>
	</div>
 </div>    
<!-- end skins/tpl/clients/client_details_field_general.tpl -->
 