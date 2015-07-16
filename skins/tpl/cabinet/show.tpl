<!-- begin skins/tpl/cabinet/show.tpl -->  
<link href="./skins/css/cabinet.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jsCabinet.js"></script>

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
				
				<?php echo $content; ?> 				
				</table>
			</div>
		</div>
	</div>
</div>
<!-- end skins/tpl/cabinet/show.tpl -->
 
