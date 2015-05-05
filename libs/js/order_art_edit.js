
$(document).ready(function() {
	//календарь для даты отгрузки
	$('#datepicker1').datetimepicker({
		minDate:new Date(),
		// disabledDates:['07.05.2015'],
		timepicker:false,
	 	dayOfWeekStart: 1,
	 	onGenerate:function( ct ){
			$(this).find('.xdsoft_date.xdsoft_weekend')
				.addClass('xdsoft_disabled');
			$(this).find('.xdsoft_date');
		},
		onSelectDate: function(ct){
			//$('#datepicker1').removeAttr('readonly').removeClass('input_disabled');
			$('#btn_date_var').click();
		},
	 	format:'d.m.Y',
	 	
	});
	// время для даты отгрузки
	$('#timepicker1').datetimepicker({
	 datepicker:false,
	 format:'H:i',
	 // minTime:'9:00',
	 // maxTime:'21:00'
	 allowTimes:[
		  '09:00', '10:00', '11:00', '12:00','13:00', '14:00','15:00', 
		  '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'
	 ]
	});	
});

// отработка клика по быстрым кнопкам
$(document).on('click','#btn_make_std',function(){
	$(this).addClass('checked');
	$('#btn_make_var').removeClass('checked');
	$(this).parent().find('input').attr('readonly','true').addClass('input_disabled').val(10);
});
$(document).on('click','#btn_make_var',function(){
	$(this).addClass('checked');
	$('#btn_make_std').removeClass('checked');
	$(this).parent().find('input').removeAttr('readonly').removeClass('input_disabled');
});

$(document).on('click','#btn_date_std',function(){
	var d = new Date();
	var curr_date = d.getDate();
	var curr_month = d.getMonth() + 1;
	var curr_year = d.getFullYear();
	// указать дату отгрузки
	// решить как будет производиться обсчет выходных дней и праздников
	// исключить выходные из подсчёта рабочих дней
	cmm = (curr_date+5) + "." + curr_month + "." + curr_year;	
	$(this).addClass('checked');
	$('#btn_date_var').removeClass('checked');
	$('#datepicker1').val(cmm).attr('readonly','true').addClass('input_disabled');
});
$(document).on('click','#btn_date_var',function(){
	
	$(this).addClass('checked');
	$('#btn_date_std').removeClass('checked');
	$(this).parent().find('input').removeAttr('readonly').removeClass('input_disabled');
});