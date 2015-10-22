<!-- begin skins/tpl/client_folder/rt/show.tpl --> 
<script type="text/javascript">// возвращает размер
$(document).on('click', '.get_size_table_read', function(event) {
	event.preventDefault();
	alert(1);
	var dop_data_id = $(this).attr('data-id_dop_data');
	var pos_id = $(this).attr('data-position_id');
	//var obj = $(this);
	
	$.ajax({
        type: 'POST',
        url: '',
        dataType: 'html',
        data: 'get_size_table_read=' + 1 +'pos_id=' + pos_id +'dop_data_id=' + dop_data_id
      })
      .done(function(response) {
        // console.log(response);
		alert(response);
        //app.boxin(response, app.initModal());
      })
      .fail(app.error);
	  
	/*$.post('', {
		AJAX:'get_size_table_read',
		id_dop_data: id_dop_data,
		position_id:position_id
	}, function(data, textStatus, xhr) {
	    alert(data);
		if(data['response'] = 'replace_width'){
			// alert(Base64.decode(data['html']));
			obj.replaceWith(Base64.decode(data['html']));
		}
	},'json');*/
});
// возвращает детализацию
$(document).on('click', '.get_a_detailed_specifications', function(event) {
	event.preventDefault();
	var position_id = $(this).attr('data-position_id');
	var obj = $(this);
	$.post('', {
		AJAX:'get_a_detailed_specifications',
		position_id:position_id
	}, function(data, textStatus, xhr) {
		if(data['response'] = 'replace_width'){
			// alert(Base64.decode(data['html']));
			obj.replaceWith(Base64.decode(data['html']));
		}
	},'json');
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