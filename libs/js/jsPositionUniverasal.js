/**
 *	Скрипт универсальной карточки товара
 *
 *	@author  Алексей Капитонов
 *	@version 16:13 14.12.2015
 */

 function togleImageGallery(button){
 	// если процесс уже выпоняется, просто выходим из функции 
 	if($('#articulusImages').hasClass('used_by_another_process')){return false;}
 	// сохраняем положение
 	
 	var hidden = 1;
 	// лочим на время выполнения процесса, чтобы избежать ошибки анимации
 	$('#articulusImages').addClass('used_by_another_process');
 	if ($('#articulusImages').hasClass('hidden')) {
 		$(button).removeClass('hidden');
 		// раскрываем
 		$('#articulusImages').removeClass('hidden').css({"display":"block"}).animate({width:$('#articulusImages').attr('data-width'),opacity:1},800).parent().animate({width:'277px',opacity:1},800, function(){
 			// снимаем блокировку
 			$('#articulusImages').removeClass('used_by_another_process');
 		});
 	}else{
 		
 		// скрываем
 		$('#articulusImages').addClass('hidden').attr('data-width', $('#articulusImages').innerWidth()).animate({width:'0'},800).parent().animate({width:'0px',opacity:0},800, function(){
 			$(button).addClass('hidden');
 			// снимаем блокировку
 			$('#articulusImages').css({"display":"none"}).removeClass('used_by_another_process');
 		});
 		hidden = 0;

 	}
 	$.post('', {
 		AJAX: 'save_image_open_close',
 		id_row: $('#ja--image-gallety-togle').attr('data-id'),
 		val:hidden
 	}, function(data, textStatus, xhr) {
 		standard_response_handler(data);
 	},'json');
 }