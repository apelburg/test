<!-- begin skins/tpl/client_folder/rt/show.tpl --> 
<script type="text/javascript">// возвращает размер
$(document).on('click', '.getSizesBtn', function(event) {
	event.preventDefault();
	var pos_id = $(this).attr('pos_id');
    var obj = $(this);
	$.ajax({
        type: 'POST',
        url: '',
        dataType: 'html',
        data: 'getSizesForArticle=' + 1 +'&pos_id=' + pos_id
      })
      .done(function(response) {
	    // alert(response);
		var json_obj = JSON.parse(response);
		if(json_obj.error){
		   if(json_obj.error == 'multi') obj.replaceWith('<br>в позиции несколько вариантов расчетов - зайдите в артикул');
		   if(json_obj.error == 'nothingFind') obj.replaceWith('<br>ошибка получения информации');
		}
		else obj.replaceWith('<br>'+json_obj.sizes.join('<br>'));
      })
});
</script>
<link href="<?php  echo HOST; ?>/skins/css/rt_position.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/statusTooltip.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/standard_response_handler.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.liTranslit.js"></script><!-- транслитерация-->
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/forms__js.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/attach_dop_serv.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/calendar_consturctor.js"></script>


<link href="<?php  echo HOST; ?>/skins/css/__rt_vremenno.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/skins/css/checkboxes.css" rel="stylesheet" type="text/css">


<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/scrolledTableSizeFixing.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/rtCalculatorClass.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/printCalculatorClass.js"></script>
<script type="text/javascript" src="./libs/js/up_window_consructor.js"></script>
<div class="scrolled_tbl_container"> 
<?php echo $rt; ?>
</div>
<!-- end skins/tpl/client_folder/rt/show.tpl -->