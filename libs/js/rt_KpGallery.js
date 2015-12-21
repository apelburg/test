//////////////////////////
//	uploadify
//////////////////////////
	function uploadify(data) {
		if(!$('#'+data['token']).length){
			setTimeout(uploadify(data), 1000);
			return;
		}
     // alert(data['timestamp']);
      $('#'+data['token']).uploadify({
        'formData'     : {
        	'AJAX'			: 'add_new_files_in_kp_gallery',
        	'timestamp' 	: data['timestamp'],
        	'token'     	: data['token'],
        	'gnom'      	: 'sdfdsfdsf',
        	'id'        	: data['id'],
        	'folder_name'	: data['folder_name']
        },
        'buttonText': 'Загрузить',
        'swf'      	: '../../libs/php/uploadify.swf',
        'uploader' 	: '',
        'multi'     : false,
        'onUploadSuccess' : function(file, data) {
            // alert('The file ' + file.name + ' uploaded successfully.');
            // подключаем стандартный обработчик ответа
            standard_response_handler(jQuery.parseJSON(data));
        }
      });
    }



	// запрос окна галлереи 
	$(document).on('click', '.showImgGalleryWindow', function(event) {
		var c = $(this).attr('data-control_num');
		event.preventDefault();
		$.post('', {
			AJAX: 'getStdKpGalleryWindow',
			id: $(this).attr('data-rt_id'),
			control_num: $(this).attr('data-control_num'),
			folder_name: $(this).attr('data-rt_folder_name')

		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	});

	// добавление изображений
	function rtGallery_add_img(data){
		$('#rt-gallery-images ul').append(Base64.decode(data['html']));
		
		rtGallery_scroll_bottom();// прокрутка скролла галлереи до инициализируем
	}

	// прокрутка скролла галлереи до инициализируем
	function rtGallery_scroll_bottom(){
		var block = $('#rt-gallery-images');
		$('#rt-gallery-images').animate({"scrollTop":99999 },"slow");
  		// block.scrollTop = block.scrollHeight;
	}


$(document).on('click', '#rt-gallery-images li', function(event) {
	event.preventDefault();
	$('#rt-gallery-images li').removeClass('checked');
	$(this).addClass('checked');
	var id = $(this).parent().parent().parent().parent().find("input[name*='id']").val();

	// выбираем отсутствие изображения
	if($(this).hasClass('no_images_select')){
		$.post('', {
			AJAX 		:'chooseNoImgGallery',
			id 			:id
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	}else{// выбираем загруженное изображение
		var folder_name = $(this).parent().parent().parent().parent().find("input[name*='folder_name']").val();
		var img = $(this).attr('data-file');
		$.post('', {
			AJAX 		:'chooseImgGallery',
			folder_name :folder_name,
			id 			:id,
			img 		:img
		}, function(data, textStatus, xhr) {
			standard_response_handler(data);
		},'json');
	}


	
	
	
});
