<!-- begin skins/tpl/cabinet/show.tpl -->  
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
			<div style="float:left">
				<?php echo $content; ?> 
			</div>
		</div>
	</div>
</div>
<!-- end skins/tpl/cabinet/show.tpl -->
 
