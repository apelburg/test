<!-- begin skins/tpl/cabinet/show.tpl -->  
<link href="./skins/css/cabinet.css" rel="stylesheet" type="text/css">
<link href="./skins/css/checkboxes.css" rel="stylesheet" type="text/css">

<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="./libs/js/classes/Base64Class.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jsCabinet.js"></script>

<!-- комментарии к запросу START -->
<link href="./skins/css/comments.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/comments.js"></script>
 <!-- комментарии к запросу END -->
 

<div class="table" id="cabinet">
	<div class="row">
		<div class="cell" id="cabinet_left_coll_menu">
			<div id="cabinet_top_menu1"></div>
			<ul id="cabinet_left_menu">
				<?php echo $CABINET->menu_left_Html; ?>
			</ul>
		</div>
		<div class="cell" id="cabinet_central_panel">
			<div id="cabinet_top_menu">
				<ul id="central_menu">
					<?php echo $CABINET->menu_top_center_Html; ?>
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
 
