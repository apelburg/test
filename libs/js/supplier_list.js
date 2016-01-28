$(document).on('click', '.row_tbl label, .row_tbl input', function(event) {
	// event.preventDefault();
	setTimeout(function(){
		$(".main_tbl_ordinary .row_tbl input[type='checkbox']").each(function(index, el) {
			if ($(this).prop("checked")) {
				console.log($(this))
				console.log($(this).prop("checked"));
			}
		});
	}, 1000);
	
});