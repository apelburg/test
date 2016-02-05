/**
 *  JavaScript Document
 *
 *	RT drag and drop
 *
 *	@author  	Alexey Kapitonov
 *	@version 	11:23 05.02.2016
 */
function get_dop_data_rows(row){
	// if(window.ui_add_class == 0){
		if(!row.hasClass('pos_edge')){
			row.addClass('checked-row');
			if(row.next().length){
				get_dop_data_rows(row.next());
			}
		}
	// }
}
	
	
		
	/**
 *  JavaScript Document
 *
 *	RT drag and drop
 *
 *	@author  	Alexey Kapitonov
 *	@version 	11:23 05.02.2016
 */
// JavaScript Document
$(function() {
	$('.borderBottomRow').each(function(index, element) {
        if($(this).find('.table').length==1){
			$(this).find('.table').height($(this).find('.table').parent().height());
		}
    });
	
	
	/*  НАЧАЛО СКРИПТА ПЕРЕМЕЩЕНИЯ СТРОК  */
	var fixHelper = function(e, ui) {
		$('#rt_tbl_body .ui-sortable-handle').removeClass('ui-sortable-handle');
		$('#rt_tbl_body .checked-row').removeClass('checked-row');
		if(!ui.hasClass('checked-row')){
			ui.addClass('checked-row');
		}
		if(ui.next().length){
			get_dop_data_rows(ui.next());	
		}
		

		var element = ui;
		window.bCheckSort = 0;
		$('.rt_tbl_body .checked-row').addClass('my-ui');
		$(document).on('mouseup','#basket_del_absolute',function(e){
		///alert("удалим то- что перетаскивали");
			$('.placeh,.ui-sortable-helper').remove();	
		});

		
		if(ui.hasClass('checked-row')){
			//var selected = $('.checked-row'); 
			var container = $('<div/>').attr({'id': 'draggingContainer','class':'rt_tbl_body'}); 
			//container.append($('.checked-row').clone());
			window.bCheckSort = 1;
			//var ui = $('<div/>').attr('id', 'draggingContainer'); 
			$('.rt_tbl_body .checked-row').each(function(index, elem) {
                container.append($(elem).clone().css('background-color','#f1f1f1'));
            });
			element.children('.td').each(function(index,el){
				container.children().children('.td:nth-of-type('+(1+index)+')').css({'padding':$(this).css('padding'),'height':$(this).height(),'width':$(this).css('width')})
			});			
			//return ; 	
			$('.rt_tbl_body .checked-row').fadeOut('fast');
			return container;
		}else{
			console.log(element.attr('class'))
			element.children('.td').each(function(index,el){
				ui.children('.td:nth-of-type('+(1+index)+')').css({'padding':$(this).css('padding'),'height':$(this).height(),'width':$(this).css('width')})
			});				
			return ui;
		}
	
	};
	
	var fixStart = function(e, ui) {
		var height_div = 0 ;// $('#draggingContainer').innerHeight() ;
		$('#draggingContainer .my-ui').each(function(index, el) {
			height_div += $(this).height();
		});
		$('#rt_tbl_body tr.orderBottomRow.placeh.ui-state-highlight td').height(height_div);
		console.log($('#draggingContainer').height());


		if($('#basket_del_absolute').length==0){$('body').append('<div id ="basket_del_absolute">ПЕРЕТАЩИТЕ СЮДА ВЫБРАННЫЕ ЭЛЕМЕНТЫ, ЧТОБЫ ИХ УДАЛИТЬ</div>');
		//отработка наведения на полосу удаления при перетаскивании элементов таблицы
		$('#basket_del_absolute').hover(
		function(){
			$(this).css('background','#BBBBBB').stop(true).animate({opacity:0.9},'fast')}
		,function(){
			$(this).stop(true).animate({opacity:0.3},'fast',function(){$(this).css('background','#BBBBBB');});
		});
		}
		return ui;
	}
	var befor = function(e, ui){

		// console.log(window.bCheckSort);		
		if(window.bCheckSort==1){
			//alert(ui.attr(class))
		var clone = $('#draggingContainer .my-ui').clone();

		$('.rt_tbl_body .my-ui').remove();
		$('.ui-state-highlight').replaceWith(clone);
		
		$('.rt_tbl_body .my-ui')
		.removeClass('my-ui')
		.removeAttr('style')
		.removeClass('checked-row').find('.checked').removeClass('checked');
		$('#draggingContainer').fadeOut().remove();
		$('.rt_tbl_body .checked-row').fadeIn('fast');
		//ui не возвращаем
		
			if($('#basket_del_absolute')){$('#basket_del_absolute').remove();}
			//пересчет html таблицы
			// reCalculate();	
			window.bCheckSort =0;	
			$('#rt_tbl_body .checked-row').removeClass('checked-row');
		}else{
			if($('#basket_del_absolute')){$('#basket_del_absolute').remove();}
			//пересчет html таблицы
			// reCalculate();	
			window.bCheckSort =0;	
			$('#rt_tbl_body .checked-row').removeClass('checked-row');
			return ui;
			
		}
	}
	
	var fixStop = function(e, ui){	
		// return ui;

		$('.ui-state-highlight').height($('#draggingContainer').height());
		if(window.bCheckSort==1){
			var cont = '';
			console.log(cont);
			$('.rt_tbl_body .checked-row').each(function(index, element) {
				if($(this).css('display')=='none'){
					cont+=$(this).clone().html();	
					$(this).remove();				
				}    
            });
			$('ui-dop-class').before(cont);//alert(cont);
			$('.checked-row').css({'display':'table-row'});			
			$('.rt_tbl_body .checked-row').css('display','block');
			return ui;			
		}	
		$('.rt_tbl_body .checked-row').css('display','block');		
		if($('#basket_del_absolute')){
			$('#basket_del_absolute').remove();
		}
		//пересчет html таблицы
		reCalculate();	
		window.bCheckSort =0;	
		return ui;
	}

	$('body').append('<div id="dragdiv"></div>');
	$("#rt_tbl_body").sortable({
		delay: 100,
		appendTo:'#dragdiv',
		distance:5,
		beforeStop: befor,
		stop:fixStop,
		items: ".pos_edge",
		revert:true,
		connectToSortable: ".rt_tbl_body",
		placeholder:"orderBottomRow placeh ui-state-highlight",
		helper: fixHelper,
		start: fixStart,
		scope:'tasks'	
	}).disableSelection();	
	/*  КОНЕЦ СКРИПТА ПЕРЕМЕЩЕНИЯ СТРОК  */
	//пересчет html таблицы
	// reCalculate();
	
	
	
	
	
	$(document).on('click touchstart touch','.rt_tbl_body .basket-check', function(index){
		if($(this).hasClass('checked')){
			$(this).removeClass('checked');
			$(this).parent().parent().removeClass('checked-row');
		}else{
			$(this).addClass('checked');
			$(this).parent().parent().addClass('checked-row');
		}
	});	      
});
