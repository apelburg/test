/**
 *	Service center scripts	
 *
 *	@author  	Alexey Kapitonov
 *	@version 	12:23 12.02.2016
 */
jQuery(document).ready(function($) {
	$.SC_createButton();
});


/**
 *	модуль окна 
 *
 *	@param 		
 *	@return  	
 *	@see 		
 *	@author  	Alexey Kapitonov
 *	@version 	
 */
$.extend({
	SC_createButton : function(){
		if($('#js-win-sv').length) return true;
		var obj = $('<div/>',{
			"id" : "js-win-sv"	
		}).click(function(event) {
			$.post('', {
				AJAX: 'get_service_center'
			}, function(data, textStatus, xhr) {
				standard_response_handler(data);
			},'json');
		});

		$('body').append( obj );
	}
});


function show_SC(data){
	var html = (data['html'] !== undefined)?Base64.decode(data['html']):'нет информации';
	var title = (data['title'] !== undefined)?data['title']:'Название окна';
	var height = (data['height'] !== undefined)?data['height']:'auto';
	if(height == '100%'){
		height = $(window).height()-2;
	}
	var width = (data['width'] !== undefined)?data['width']:'auto';	
	if(width == '100%'){
		width = $(window).width()- 2;
	}
	
	var buttons = new Array();
		
	buttons.push({
	    text: 'Калькулятор',
	    click: function() {
			// подчищаем за собой
			$.notify("Вызов калькулятора",'info');
	    }
	});		

	buttons.push({
	    text: 'Закрыть',
	    click: function() {
			// подчищаем за собой
			$(this).dialog("destroy");
	    }
	});		

	$('body').append('<div id="SC_window"></div>');
	$('#SC_window').html(html);
	$('#SC_window').dialog({
	    width: width,
	    height: height,
	    modal: true,
	    title : title,
	    autoOpen : true,
	    buttons: buttons          
	});
}



