<!-- begin skins/tpl/client_folder/rt/show.tpl --> 
<link href="<?php  echo HOST; ?>/skins/css/order_art_edit.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/skins/css/__rt_vremenno.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/skins/css/checkboxes.css" rel="stylesheet" type="text/css">
<style type="text/css">
	.cabinet_general_content_row,.dop_usl_tbl{width: 100%; border-collapse: collapse;}
	.cabinet_general_content_row tr td,.cabinet_general_content_row tr th{border:1px solid #f4f4f4; padding: 5px; font-size: 12px}
	.cabinet_general_content_row tr td .dop_usl_tbl tr td{border:1px solid #DFDFDF; padding: 2px; font-size: 14px}
	.itogo{font-weight: bold; font-size: 16px; float: right;}
	.itogo_n{font-weight: bold; font-size: 14px; float: right;}
	.itogo_n_no_bold{float: right;}
	.cabinet_general_content_row tr td select{width: 150px}
	.cabinet_general_content_row tr td span{padding-right: 3px}
</style>
<div class="scrolled_tbl_container">
	<table class="cabinet_general_content_row">
		
		<?php
		// $query = "";

		echo $order_tbl; 

		?>		
	</table> 
</div>
<!-- end skins/tpl/client_folder/rt/show.tpl -->