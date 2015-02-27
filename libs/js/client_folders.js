// Отработка клавиш
$(document).keydown(function(e) {	
	if(e.keyCode == 27){//ESC
		$('#bg_modal_window,.html_modal_window').remove();
	}
});
$(document).on('click', '.html_modal_window_head_close,.cancel_bw', function(event) {
	$('#bg_modal_window,.html_modal_window').remove();
});
$(document).ready(function() {
	 // alert('JC');
});

