//standart function OS
function new_html_modal_window(html,head_text){
	if($('#bg_modal_window').length>0){$('#bg_modal_window,.html_modal_window').remove();}
	$('body').append('<div id="bg_modal_window"></div><div class="html_modal_window"><div class="html_modal_window_head">'+ head_text +'<div class="html_modal_window_head_close">x</div></div><div class="html_modal_window_body">'+ html +'</div></div>');
	$('#html_modal_window').draggable();
	var he = ($(window).height()/2);
	var margin = $('.html_modal_window').innerHeight()/2*(-1);
	$('.html_modal_window').css({'top':he,'margin-top':margin,'display':'block'}).draggable();	
	return true;
}


// Отработка клавиш
$(document).keydown(function(e) {	
	if(e.keyCode == 27){//ESC
		$('#bg_modal_window,.html_modal_window').remove();
	}
});
$(document).on('click', '.html_modal_window_head_close', function(event) {
	$('#bg_modal_window,.html_modal_window').remove();
});
$(document).ready(function() {
	 // alert('JC');
});

