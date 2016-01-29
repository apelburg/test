$(document).on('click', '.row_tbl label, .row_tbl input', function(event) {
	// event.preventDefault();
	if($(".main_tbl_ordinary .row_tbl input[type='checkbox']:checked").length > 0){
		$('.show_choosen').addClass('make-it-slow');
	}else{
		$('.show_choosen').removeClass('make-it-slow');
	}	
});

$(window).scroll(function(){
		
	if ($(this).scrollTop() >= 85 ) {
		if($('.options_bar.noselect.js--supplier-top-menu').length == 0){
			var top_menu = $('.options_bar.noselect').clone();
			top_menu.addClass('js--supplier-top-menu');
			$('body').append(top_menu);
		}		
	}else{
		if($('.options_bar.noselect.js--supplier-top-menu').length > 0){
			$('.js--supplier-top-menu').remove();
		}
	}
});