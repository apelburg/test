<?php
/**
 *	библиотека универсальных классов	
 * 		
 */
if ( isset($_SESSION['access']['user_id'])  && $_SESSION['access']['user_id'] == 42) {
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
}
	/**
	 *	галлерея изображений для некаталожной продукции
	 *  для КП
	 * 		
	 *	@author  	Алексей Капитонов
	 *	@version 	07.12.2015 13:45
	 */
 	class rtKpGallery extends aplStdAJAXMethod{
 		function __construct(){
			parent::__construct();
			// подключаемся к базе
			// $this->db();
 		}

 		//////////////////////////
		//	KP
		//////////////////////////
			// protected function getImageGalleryContent_AJAX(){
			// 	if(!isset($_POST['id'])){
			// 		$html = 'Отсутствует информация по названию папки хранения изображеницй.';
			// 		$this->addMessage($html,'error_message');
			// 		return;
			// 	}

			// 	return getImageGalleryContent($_POST['id']);
			// }


 			// выбор изображения из загруженных в галлерею
 			protected function chooseImgGallery_AJAX(){
 				// если не получено название папки
 				if(!isset($_POST['id'])){
 					$html = 'ID не указан';	
					$this->responseClass->addMessage($html,'error_message');
 					return;
 				}
 				// если не получено название изоюбражения
 				if(!isset($_POST['img'])){
 					$html = 'Image не указана';	
					$this->responseClass->addMessage($html,'error_message');
 					return;
 				}


 				// запрашиваем сохраненные значения выбранного изображения
 				$row = $this->checkSavedchoosen($_POST['id']);
 					
 				// если сохраненные значения есть
				
				global $mysqli; 
				$query = "UPDATE `".RT_MAIN_ROWS."` SET";
				$query .=" img_folder_choosen_img = '".$_POST['img']."'";
				$query .=" WHERE `id` = ".$row['id'].";";
				$result = $mysqli->query($query) or die($mysqli->error);		

				return;
 			}
 			protected function chooseNoImgGallery_AJAX(){
 				global $mysqli; 
				$query = "UPDATE `".RT_MAIN_ROWS."` SET";
				$query .=" img_folder_choosen_img = ''";
				$query .=" WHERE `id` = ".$_POST['id'].";";
				$result = $mysqli->query($query) or die($mysqli->error);		

				return;
 			}

 			// запрос на существующий выбор 
 			private function checkSavedchoosen($id){
 				global $mysqli;
 				
 				// запрос наличия выбранного изображения для данной строки
 				$query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `id` = '".$id."' ";
 				$row2 = array();
 				$result = $mysqli->query($query) or die($mysqli->error);
 				
 				// echo $query;
 				$result = $mysqli->query($query)or die($mysqli->error);
				if($result->num_rows > 0){
					// echo $result->num_rows;
					while($row = $result->fetch_assoc()){
						$row2 = $row;
					}
				}
					
				return $row2;
 			}


 			// вставляет новую запись о выборанном изображении в базу
 			private function newSelectRow($dir, $img){
 			// 	global $mysqli;
 			// 	$query = "INSERT INTO `".RT_MAIN_ROWS."` SET";
				// $query .=" img_folder = '".$dir."'";
				// $query .=", img_folder_choosen_img = '".$img."'";
				// $result = $mysqli->query($query) or die($mysqli->error);
 			}

 			


 			// проверка наличия изображений для по RT_id
 			// при наличии изображения выбранного в галлерее возвращает его имя
 			// в противном случае false
 			static function checkTheFolder($RT_id, $name = ''){				
 				// echo method_get_name();
 				$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/admin/order_manager/data/images/'.$RT_id.'/';
 				$dir = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$RT_id.'/';
				// если папка не нейдена возвращаем false
				if (!is_dir($dir)) {
					return flase;
				}
				// если папка пуста возвращаем false
				$files = scandir($dir);
				if(count($files) <= 2){
					return flase;	
				}

				global $mysqli;

				$query = "SELECT * FROM `".KP_GALLERY."` WHERE dir = '".$RT_id."' ";

				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$img = '';
 				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$img = $row['img'];
					}
				}
				// если изображение не указано возвращаем false
				if($img == ''){
					return flase;
				}

				// по умолчанию возвращаем название выбранного изображения
				switch ($name) {
					case 'dir':
						$dir = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$RT_id.'/'.$img;
						return $dir;
						break;

					case 'global_dir':
					$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$RT_id.'/'.$img;	
						return $global_dir;
						break;
					
					default:
						return $img;
						break;
				}							
 			}

 			// 	protected function check_changes_to_rt_protocol($control_num,$id){
			// 	global $db;
				
			//     $query = "SELECT*FROM `".CALCULATE_TBL_PROTOCOL."`";	
			// 	$result = mysql_query($query,$db);
			// 	if(!$result)exit(mysql_error());
				
			// 	$num_rows = mysql_num_rows($result);
			// 	if($num_rows == $control_num) return $id;
				
			// 	//
			// 	for($i = $control_num ; $i < $num_rows; $i++){
			// 	    $row_id = mysql_result($result,$i,'row_id');
			// 		$action = mysql_result($result,$i,'action');
					
			// 		if($action == 'insert'){
			// 		    if($row_id <= $id ) ++$id;
			// 		} 
			// 		if($action == 'delete'){
			// 		    if($row_id < $id )--$id;
			// 			if($row_id == $id ) return false;
			// 		}
			// 	}
				
			// 	return $id;
			// }


			// получаем изображения из папки
			protected function getImagesList($folder, $id){
				$saved = $this->checkSavedchoosen($id);
				$dir = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$folder.'/';
				$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder.'/';
				$html = '';
				// $html .= $this->printArr(scandir($dir));
				$html .= '<ul>';
				$all_li = '';$checked = false;
				if(is_dir($dir)){

					$files = scandir($dir);
					for ($i = 0; $i < count($files); $i++) { // Перебираем все файлы
					    if (($files[$i] == ".") || ($files[$i] == "..")) { // Текущий каталог и родительский пропускаем
					    	continue;
					    }
						     
					    $path = $global_dir.$files[$i]; // Получаем путь к картинке
					    $class_li = "";
					    if($files[$i] == $saved['img_folder_choosen_img']){
					    	$class_li = "checked";
					    	// $html .= '<li>'.$saved['img_folder_choosen_img'].'</li>';
					    	$checked = true;
					    }
					    
					    $all_li .= $this->getImgLiHtml($path, $files[$i], $class_li);
					}
				}

				$li_class = (!$checked)?"checked":"";

				$html .= '<li class="rt-gallery-cont no_images_select '. $li_class .'" >';
					//$html .= '<img src="http://'.$_SERVER['HTTP_HOST'].'/img/no_image.jpg"';
					$html .= $this->getArtImg($saved);
				$html .= '</li>';
				
				$html .= $all_li.'</ul>';

				return $html;
			}
			//////////////////////////
			//	из common.php
			//////////////////////////
				protected function transform_img_size($img,$limit_height,$limit_width){
			     	list($img_width, $img_height, $type, $attr) = (file_exists($img))? getimagesize($img): array($limit_width,$limit_height,'',''); 
					$limit_relate = $limit_height/$limit_width;
					$img_relate = $img_height/$img_width;
					if($limit_relate < $img_relate) $limit_width = $limit_height/$img_relate; 
					else $limit_height = $limit_width*$img_relate;
					return array($limit_height,$limit_width); 
				}
				protected function get_big_img_name($art){
			        global $db;
				   
			        $query = "SELECT*FROM `".IMAGES_TBL."` WHERE `size` = 'big' AND art='".$art."' ORDER BY id";
				    $result = mysql_query($query,$db);
				    if($result && mysql_num_rows($result)>0){
					   $row = mysql_fetch_assoc($result);
				       $img = ($row['name'] !='')? $row['name']:'no_image.jpg';
				    }
				    else $img = 'no_image.jpg';
				    return $img;	
			    } 
			    protected function checkImgExists($path,$no_image_name = NULL ){
				    $mime = $this->getExtension($path);
					if(@fopen($path, 'r')){//file_exists
						$img_src = $path;	
					}
					else{
					    $no_image_name =!empty($no_image_name)? $no_image_name :'no_image';
						$img_src= substr($path,0,strrpos($path,'/') + 1).$no_image_name.'.'.$mime;
					} 
					return $img_src;
				}
				protected function getExtension($filename){
			        $path_info = pathinfo($filename);
			        return $path_info['extension'];
			    }


			// получаем изображение для артикула
			protected function getArtImg($item){
				$img_path = '../img/'.$this->get_big_img_name($item['art']);	
			    $img_src = $this->checkImgExists($img_path);
				// меняем размер изображения
			    $size_arr = $this->transform_img_size($img_src,226,300); // установленое здесь значение для высоты является оптимальным 
				                                                   // при выводе КП на печать (не съезжают ячейки таблицы)
				//$size_arr = array(230,300);
				//$size_arr = array(100,100);
			
				
				// вставляем изображение
				return '<img src="'.$img_src.'" height="'.$size_arr[0].'" width='.$img_src[1].'">';
			}

			// получаем html изображения
			protected function getImgLiHtml($path, $file = '',$li_class = ''){
				$html = '<li class="rt-gallery-cont '. $li_class .'" data-file="'.$file.'" >';
				$html .= '<img src="'.$path.'" alt="" />'; // Вывод превью картинки
				$html .= '</li>';
				return $html;
			}

			// получаем контент из галлереи загруженных изображений
			protected function getImageGalleryContent($folder_name, $rt_id){
				$folder = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$folder_name.'/';
				
				// DEBUG
					// $html  = ''.$folder.'<br>'.$rt_id;
				$html = '';
				$html .= '<div id="rt-gallery-images">';
					$html .= $this->getImagesList($folder_name,$rt_id);
				$html .= '</div>';
				return $html;
			}

			// проверка существования папки
			protected function checkFolderExist($folder){
				return is_dir('/var/www/admin/data/www/apelburg.ru/os/data/images/'.$folder.'/');

			}

			// создание новой папки
			protected function greateNewDir($rt_id_row){
				$dirName_1 = md5(time());
				
				$dirName = '/var/www/admin/data/www/apelburg.ru/os/data/images/'.$dirName_1.'/';
				
				if (!is_dir($dirName)) {
					//если папкb $dirName не существует
					mkdir($dirName,0777);
					
					global $mysqli; // пишем её название в базу
					$query = "UPDATE `".RT_MAIN_ROWS."` SET";
					$query .=" img_folder = '".$dirName_1."'";
					$query .=" WHERE `id` = ".$rt_id_row.";";
					$result = $mysqli->query($query) or die($mysqli->error);
				}

				return $dirName_1;
			}

			private function warpDopText($text){
				return '<div class="dop_text">'.$text.'</div>';
			}

			// собираем окно галлереи изображений для позиции
			protected function getStdKpGalleryWindow_AJAX(){
				if(!isset($_POST['id'])){
					$html = 'Отсутствует id.';
					$this->responseClass->addMessage($html,'error_message');
					return;
				}

				// $rt_id = $this->check_changes_to_rt_protocol($_POST['control_num'],$_POST['id']);
				$rt_id = (int)$_POST['id'];
				$rt_row = $this->checkSavedchoosen($rt_id);

				$folder_name = $rt_row['img_folder'];
				
				
				// $html = $this->warpDopText($this->printArr($rt_row));
				// $global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder_name.'/';					
				// $html .= $this->warpDopText($global_dir);
				
				// $this->responseClass->addMessage($html,'system_message',25000);
				
				// проверка на существование папки
				if($folder_name == '' || !$this->checkFolderExist($folder_name)){
					// создаем новую папку

					$folder_name = $this->greateNewDir($_POST['id']);
					$html = 'Создана новая папка';
					$this->responseClass->addMessage($html,'error_message',25000);
				}

				$win_DIV_ID = 'rt-gallery-DIV_'.$folder_name;
				$id = 'file_upload_'.md5(time());
				$html = '';
				// $html .= $this->printArr($rt_row);
				$html .= '<div id='.$win_DIV_ID.'>';
				
				// $html .= '<div style="    padding: 5px;
				// 		    border: 1px solid red;
				// 		    color: red;
				// 		    text-align: center;">';
				// $html .= '<div>В разработке</div>';
				// $html .= '<div style="text-align:right"><span style="color:grey;font-size:10px">Алексей Капитонов</span></div>';
				// $html .= '</div>';

				// вывод изображений по позиции
				$html .= $this->getImageGalleryContent($folder_name,$rt_id);
				
				$timestamp = time();
				$token = md5('unique_salt' . $timestamp);
				$html .= '<h1>Загрузка изображений</h1>
						<form>
							<div id="queue"></div>
							<input id="'.$token.'" data-folder_name="'.$folder_name.'" name="file_upload" type="file" multiple="true">
						</form>
						';

				// $html .= $this->printArr($_POST); // распечатка POST в окно

				$html .= '</div>';

				$options['width'] = 1200;
				// $options['height'] = 500;

				$this->responseClass->addSimpleWindow($html,'Загрузить изображение',$options);
				// запустим функцию JS и передадим ей новый id
				$options = array();
				$options['id'] = $rt_id;
				$options['folder_name'] = $folder_name;
				$options['timestamp'] = $timestamp;
    			$options['token'] = $token;
    			// выз
				$this->responseClass->addResponseFunction('uploadify',$options);

			}

			
			// добавление новых изображений для КП
			protected function add_new_files_in_kp_gallery_AJAX(){
				$firstImg = false;

				$folder_name = $_POST['folder_name'];
				$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$folder_name.'/';

				// echo $uploadDir;
				if (!is_dir($uploadDir)) {
					$folder_name = $this->greateNewDir($_POST['id']);
					$uploadDir = $_SERVER['DOCUMENT_ROOT'].'/os/data/images/'.$folder_name.'/';
					
				}
				// меняем права на папку
				chmod($uploadDir, 0777);
				///var/www/admin/data/www/apelburg.ru/admin/order_manager/data/images/file_upload_97be41adc28fd2a828c8317cfb520029/
				
					

				// исключение на неполные данные
				if( !isset($_POST['id']) || trim($_POST['id']) == ''){
					$html = $this->printArr($_POST);
					$options['width'] = 1200;
					$options['height'] = 500;
					$html .= '<br>'.$uploadDir;
					$this->addSimpleWindow($html,'',$options);


					$html = 'Не указан путь сохранения';
						

					$this->responseClass->addMessage($html,'error_message');

					$this->responseClass->addMessage($uploadDir,'error_message', 15000);

					return;	
				}
				// разрешёные форматы файлов
				$fileTypes = array('jpg', 'jpeg', 'gif', 'png');

				if ($_FILES) {

					$verifyToken = md5('unique_salt' . $_POST['timestamp']);

					if (!empty($_FILES) && $_POST['token'] == $verifyToken) {

						$tempFile   = $_FILES['Filedata']['tmp_name'];
						$uploadDir  = $uploadDir;



						//удаляем старые файлы в папке
						//removeFiles($uploadDir, $verifyToken);

						// проверка типа файла
						$fileParts = pathinfo($_FILES['Filedata']['name']);
						$extension = strtolower($fileParts['extension']);
						if (in_array($extension, $fileTypes)) {

							//устанавливаем имя файла
							$fileName = mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y"));
							//добавляем актуальный токен к файлу
							$fileName .= "_$verifyToken";
							$fileNameExtension = $fileName . ".$extension";
							$targetFile = $uploadDir . $fileNameExtension;


							

							// сохраняем файл
							move_uploaded_file($tempFile, $targetFile);

							//меняем атрибуты
							//chmod($targetFile, 0775);

							//die(json_encode($targetFile));
							$html = 'Изображение загружено';
						

							$this->responseClass->addMessage($html,'system_message');
							// добавляем загруженные изображения
							$global_dir = 'http://'.$_SERVER['HTTP_HOST'].'/os/data/images/'.$folder_name.'/';
							$path = $global_dir.$fileName . ".$extension";
							$this->responseClass->addResponseFunction('rtGallery_add_img',array('id'=>$folder_name,'html'=>$this->getImgLiHtml($path,$fileName . ".$extension",(($firstImg)?'checked':''))));
						} else {

							// загрузка не удалась
							echo 'Invalid file type.';
						}
					}
				}
			}
}

		

	




?>