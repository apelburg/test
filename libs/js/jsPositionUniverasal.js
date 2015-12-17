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

// клик по исходящей цене (цена из каталога)
$(document).on('click', '.row_price_out_one.price_out', function(event) {
  if($(this).find('input').attr('disabled') == 'disabled'){
    var message = "Чтобы редактировать цену, воспользуйтесь инструментом наценки";
    echo_message_js(message,'error_message',800);
  }
});

// кнопка переключатель цены в таблице расчета
$(document).on('click', '.js--button-out_ptice_for_tirage', function(event) {
  event.preventDefault();
  if($(this).hasClass('for_out')){
    $(this).removeClass('for_out').addClass('for_in').find('div').html('входящая<br>(сумма)');
    $('.calkulate_table:visible td:nth-of-type(6)').removeClass('for_out').addClass('for_in');
  }else{
    $(this).addClass('for_out').removeClass('for_in').find('div').html('исходящая<br>(сумма)');
    $('.calkulate_table:visible td:nth-of-type(6)').removeClass('for_in').addClass('for_out');
  }
  
});



// jQuery дополнение
// операции над строкой URL
$.extend({
	getUrlVars: function(){
	    var vars = [], hash;
	    var pos = window.location.href.indexOf('?');
	    console.log(pos);
		if(pos > 0) {
			var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
		    for(var i = 0; i < hashes.length; i++)
		    {
		      	hash = hashes[i].split('=');
		     	vars.push(hash[0]);
		    	vars[hash[0]] = hash[1];
		    }
		}    
	    return vars;
	},
	urlVar: function(name, val){
		if(val === undefined)
  		{
  			return $.getUrlVars()[name];	
  		}else{
  			return $.setUrlVal(name, val);	
  		}
  	},
  	setUrlVal: function (name, val){
  		var hashes = $.getUrlVars();

  		if(hashes[name] === undefined){
  			hashes.push(name);	
  		}
  		
	    hashes[name] = val;
  		
	    var urlString = '';
  		hashes.forEach(function(element, index){
  			console.log(element);
  			urlString += ((index==0)?'?':'&') + element + '=' + hashes[element];
  		});	
  		window.history.pushState("object or string", "Портфолио", "/os/" + urlString);
  	},
  	delUrlVal: function (name){
  		var hashes = $.getUrlVars();

  		if(hashes[name] !== undefined){
  			hashes.splice(hashes.indexOf(name), 1);
  			var urlString = '';
	  		hashes.forEach(function(element, index){
	  			console.log(element);
	  			urlString += ((index==0)?'?':'&') + element + '=' + hashes[element];
	  		});	
	  		window.history.pushState("object or string", "Портфолио", "/os/" + urlString);
  		} 		
	    
  	}

});