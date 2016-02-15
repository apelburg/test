<?php
class Client extends aplStdAJAXMethod{	
	#################################
	###         СВОЙСТВА          ###
	#################################
	// содержит название и пути к изображениям для каждого типа контактных данных
	static $array_img = array('email'=>'<img src="skins/images/img_design/social_icon1.png" >','skype' => '<img src="skins/images/img_design/social_icon2.png" >','isq' => '<img src="skins/images/img_design/social_icon3.png" >','twitter' => '<img src="skins/images/img_design/social_icon4.png" >','fb' => '<img src="skins/images/img_design/social_icon5.png" >',
'vk' => '<img src="skins/images/img_design/social_icon6.png" >','other' => '<img src="skins/images/img_design/social_icon7.png" >');

	#################################
	###          МЕТОДЫ           ###
	#################################
	/*
	__construct($id) 
	(id клиента , массив)
	// получает основные данные о клиенте 
	
	public function create($array_in)
	(массив с ключами в виде названий колонок основной таблицы клиентов)
	// создаёт клиента


	static function delete($id)
	(id клиента)
	// удалить клиента и всё, что с ним связано
	// возвращает строку сообщене об успешном удалении
	

	get_addres($id)  
	(id клиента , массив)
	//получает адрес доставки и фактический адрес клиента
	
	get_requisites($client_id) 
	(id клиента)
	// получает массив реквизитов 

	get_contact_info_arr($tbl,$type,$parent_id)
	(имя константы с названием таблицы, фильтр по типу (если не указан выводит все), 
	id строки родителя из той таблицы по которой выполняется поиск)
	// получаем контактные данные только по клиенту
	
	get_contats_info_all($type, $client_id)
	(фильтр по типу (если не указан выводит все), 
	id строки родителя из той таблицы по которой выполняется поиск)
	// получаем контактные данные по клиенту и его контактным лицам
	
	requisites($id)
	(id клиента) int
	// получаем массив реквизитов
	
	cont_faces($id)
	(id клиента) int
	//получаем данные о контактных дицах компании

	get_relate_managers($id)
	(id клиента)
	// получаем кураторов клиента в массиве
	
	private function get_manager_info_by_id($manager_id)
	//Получение данных о Менеджере по id
	//возвращает одномерный массив

	function cor_data_for_SQL($data)
	//защита от sql инъекций
	
	######################################################
	###   НЕ УНИФИЦИРОВАННЫЕ (УЗКОНАПРАВЛЕННЫЕ) МЕТОДЫ ###
	######################################################

	get_contact_row($contact_company, $type,$array_dop_contacts_img) 
	(массив контактов,строка тип, массив с изображениями)
	// вывод доп контактов в табличном виде,
	
	get__clients_persons_for_requisites($type)
	(id уже выбранной строки, по умолчанию необходимо посылать 0)
	// отдает список <option> с названиями занесенных в базу должностей сотрудников для реквизитов
	
	edit_requsits_show_person($requisites_id)
	(id реквизита)
	// лица (контрагенты) имеющие право подписи  для реквизитов в массиве

	edit_requsits_show_person_all($arr,$client_id)
	(массив персон имеющих право подписи, id клиента)
	// выводит html форму редактирования персон имеющих право подписи

	get_relate_managers($client_id)
	(id клиента)
	// получаем кураторов клиента 
	
	get_ralate_manager_names($client_id)
	(id клиента)
	// получаем кураторов клиента через запятую

	get_reiting($id,$rate)
	(id клиента, число рейтинга от 0-5)
	// вывод html рейтинга
	
	get_contact_info($tbl,$parent_id)
	(константа таблицы, id строки родителя')
	// выовд контактных данных в html

	*/

	private $user_access = 0;
	private $user_id = 0;
	
	
	public function __construct($id = 0 ) {
		// подключение к базе
		$this->db();

		$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;
		$this->user_access = $this->get_user_access_Database_Int($this->user_id);


		## данные POST
		if(isset($_POST['AJAX'])){
			// получаем данные пользователя
			$User = $this->getUserDatabase($this->user_id);
			
			$this->user_last_name = $User['last_name'];
			$this->user_name = $User['name'];

			$this->_AJAX_($_POST['AJAX']);
		}

		## данные POST
		if(isset($_GET['AJAX'])){
			// получаем данные пользователя
			$User = $this->getUserDatabase($this->user_id);
			
			$this->user_last_name = $User['last_name'];
			$this->user_name = $User['name'];

			$this->_AJAX_($_GET['AJAX']);
		}


		if($id > 0){
			$this->get_object($id);
		}


	}

	// стандартный AJAX обработчик
	// protected function _AJAX_($name){
	// 	$method_AJAX = $name.'_AJAX';
	// 	// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
	// 	if(method_exists($this, $method_AJAX)){
	// 		$this->$method_AJAX();
	// 		exit;
	// 	}					
	// }

	//////////////////////////
	//	AJAX
	//////////////////////////
		/*
			ВНИМАНИЕ !!!!
			если в конце _AJAX метода стоит exit, то метод не работает со стандартным ответчиком
			и для того чтобы туда передать какие-либо доп. функции JS нужно переписывать JS приёмник
		*/

		/**
		 *	апдейт должностей в реквизитах
		 *
		 *	@author  	Алексей Капитонов
		 *	@version 	12:23 28.01.2016
		 */
		/*
		protected function update_requisits_tbl_AJAX(){
			// скрипт отработан и более не нужен
			exit;

			global $mysqli;
			$query = "SELECT * FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."`";
			$requesit = array();
	        
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
	        if ($result->num_rows > 0) {
	            while ($row = $result->fetch_assoc()) {
	                $requesit[$row['id']] = $row;
	            }
	        }

	        $get__clients_persons_arr = $this->get__clients_persons();

	        $query = ''; $i = 0;
	        foreach ($requesit as $key => $value) {
	        	if($i >= 20){
	        		$result = $mysqli->multi_query($query) or die($mysqli->error);	
	        		
	        		echo "Скопировано $i строк<br>";
	        		$query = ''; $i = 0;
	        	}

	        	if(isset($get__clients_persons_arr[$value['post_id']])){
		        	$query = "UPDATE `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  ";
		        	$query.= "`position` =  '" . $get__clients_persons_arr[$value['post_id']]['position'] . "',";	
					$query.= "`position_in_padeg` =  '" . $get__clients_persons_arr[$value['post_id']]['position_in_padeg'] . "'";
					$query.= " WHERE id = '".$key."'; ";
					//$i++;
					$result = $mysqli->query($query) or die($query.'<br>'.$mysqli->error);	
				}
	        }
	        echo "<strong>Скрипт завершён!!!</strong>";
		}
		*/

		// окно просмотра реквизитов
		protected function show_requesit_AJAX() {
	        $query = "SELECT * FROM `" . CLIENT_REQUISITES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
	        $requesit = array();
	        
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
	        if ($result->num_rows > 0) {
	            while ($row = $result->fetch_assoc()) {
	                $requesit = $row;
	            }
	        }
	        ob_start();
		    include ('./skins/tpl/clients/client_folder/client_card/show_requsits.tpl');
		    $html = ob_get_contents();
		    ob_get_clean();

	        $options['width'] = 1200;
			$this->responseClass->addSimpleWindow($html,$_POST['title'],$options);	        
	    }

	    /**
	     *	получить окно со списком реквизитов
	     *
	     *	@author  	Alexey Kapitonov
	     *	@version 	10:21 09.02.2016
	     */
	    protected function get_requisites_AJAX(){
	    	if(!isset($_GET['client_id'])){
	    		$html = 'Не указан ID клиента.';
				$this->responseClass->addMessage($html,'error_message');
				return;
	    	}
	    	// получаем реквизиты
	    	$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` "; 
	    	// $query .= " INNER JOIN `".CLIENTS_TBL."` ON `".CLIENTS_TBL."`.`id` = `".CLIENT_REQUISITES_TBL."`.`client_id` ";
	    	$query .= " WHERE client_id = '".$_GET['client_id']."'";
	        $requisites = array();
	        
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
	        if ($result->num_rows > 0) {
	            while ($row = $result->fetch_assoc()) {
	                $requisites[] = $row;
	            }
	        }

	        $html = '';
	        $html .= '<style type="text/css">#requesites_form{}#requesites_form table tr td:nth-of-type(2){min-width: 50px;text-align: right;}#requesites_form table tr td:nth-of-type(2) img{ margin: 0 0 0 10px; border: 1px solid #fff; padding: 1px 7px}#requesites_form table tr td:nth-of-type(2) img:hover{ border:1px solid #DBDBDB;}</style>';
	        $html .= '<div id="requesites_form">';
			$html .= '<form>';
			$html .= '<table>';


			$edit_buttons = ($this->user_access == 1 || $this->user_access == 5)?'':' style="display:none;"';
			$edit_buttons_all = (!isset($_POST['no_edit']))?'':' style="display:none;"';

				if (count($requisites)>0) {
					foreach ($requisites as $key => $value) {
						$html .= "<tr>
								<td>
								".++$key.". <a class=\"show_requesit\" href=\"#\" data-id=\"".$value['id']."\" title=\"".$value['company']."\">".$value['company']."</a>
								</td>
								<td ".$edit_buttons_all.">
									<img title=\"Редактировать реквизиты\" class=\"edit_this_req\" data-id=\"".$value['id']."\" src=\"skins/images/img_design/edit.png\" >
									<img title=\"Удалить реквизиты\" class=\"delete_this_req\" data-id=\"".$value['id']."\" src=\"skins/images/img_design/delete.png\" ".$edit_buttons.">
								</td>
							</tr>";
					}
				}
			$html .= '</table>';
			$html .= '</form>';
			$html .= '</div>';
			if(!isset($_POST['no_edit'])){
				// выводим новое окно с кнопкой добавить
				$option['html'] = $html;
				$option['title'] = 'Реквизиты';
				$this->responseClass->addResponseFunction('get_requisites_window',$option);	
			}else{
				// стандартное окно с кнопкой "Закрыть"
				$this->responseClass->addSimpleWindow($html,'Реквизиты');
			}
			
		}

	    // изменения рейтинга клиента
	    protected function update_reiting_cont_face_AJAX() {
	    	$query = "UPDATE  `" . CLIENTS_TBL . "` SET  `rate` =  '" . $_POST['rate'] . "' WHERE  `id` = '" . $_POST['id'] . "';";
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
	        // сообщение
	        $html = 'Рейтинг успешно обнавлён. Спасибо.';
			$this->responseClass->addMessage($html,'successful_message');	        
	    }
	    protected function check_the_main_contact_face_AJAX(){
	    	$query  = "UPDATE  `" . RELATE_CLIENT_MANAGER_TBL . "` ";
	    	$query .= "SET  `cont_faces_relation_id` =  '" . $_POST['contact_face_id'] . "' ";
	    	$query .= "WHERE  `id` = '" . $_POST['relate_id'] . "';";

	        $result = $this->mysqli->query($query);// or die($this->mysqli->error);
	        if(!$result){
	        	$html = 'Ошибка записи';
				$this->responseClass->addMessage($html,'error_message');		
	        }else{
	        	$html = 'Контактное лицо по умолчанию сохранено.';
				$this->responseClass->addMessage($html,'successful_message');		
	        }
	    	
		}

	    // окно заведения новых реквизитов
	    protected function create_requesit_AJAX() {        
	        include ('./skins/tpl/clients/client_folder/client_card/new_requsits.tpl');	        
	        exit;
	    }

	   	protected function get_manager_lis_for_curator_AJAX() { 
	    	$query = "SELECT * FROM ".MANAGERS_TBL." ORDER BY  `name` ASC ";
	    	$requesit = array();
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
	        if ($result->num_rows > 0) {
	            while ($row = $result->fetch_assoc()) {
	                $managers[] = $row;
	            }
	        }
	        $num_rows = floor(count($managers)/3);
	        $client_id = $_GET['client_id'];
	        //получаем список менеджеров прикреплённых к клиенту
	        $curators_arr = Client::get_relate_managers($client_id);
	        
	        $num = 0;
	        $html = '';
	        foreach ($managers as $key => $value) {
	        	if(trim($value['name'])!="" || trim($value['last_name'])!=""){
	        	// перебираем всех менеджеров
	        	// если менеджер прикреплён добавляем ему класс enabled
	        	$enable = '';
	        	foreach($curators_arr as $k => $v){
	        		if($v['id']==$value['id']){
	        			$enable = 'enabled';
	        		}
	        	}
	        	
	        	$str = '<span data-id="'.$value['id'].'" class="chose_curators '.$enable.'">'.$value['name'].' '.$value['last_name'].'</span>';
		        	
		        if($num==0){
		        	$str = '<div class="column_chose_window">'.$str;
		        }else if($num==$num_rows){
		        	$str = $str.'</div>';
		        	$num=-1;
		        }
		          $html .= $str;
	        	
	        	$num++;
	        	}
	        }
	        echo $html;
	    	exit;
	    }

	    // окно редактирования реквизитов
	    protected function edit_requesit_AJAX() {
	        $query = "SELECT * FROM `" . CLIENT_REQUISITES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
	        $requesit = array();
	        
	        // echo $query;exit;
	        $result = $this->mysqli->query($query) or die($this->mysqli->error);
	        if ($result->num_rows > 0) {
	            while ($row = $result->fetch_assoc()) {
	                $requesit = $row;
	            }
	        }
	        
	        // получаем список должностей для персональных данных контактных лиц из реквизитов
	        //$get__clients_persons_for_requisites = Client::get__clients_persons_for_requisites($client_id);
	        // получаем контактные лица для реквизитов
	        $client_id = $_GET['client_id'];
	        include ('./skins/tpl/clients/client_folder/client_card/edit_requsits.tpl');
	        exit;
	    }

	    // окно редактирования имени компании
	    protected function getWindowChengeNameCompany_AJAX(){
	    	$html = '';
	    	$html .= '<form>';
	    		$html .= '<input type="text" name="company" onkeyup="$(\'#chenge_name_company\').html($(this).val());" value="'.$_POST['company'].'">';
		    	$html .= '<input type="hidden" name="AJAX" value="chenge_name_company">';
		    	$html .= '<input type="hidden" name="id" value="'.$_POST['id'].'">';
		    	$html .= '<input type="hidden" name="tbl" value="'.$_POST['tbl'].'">';
	    	$html .= '</form>';
	    	
	    	// добавляем окно
			$this->responseClass->addPostWindow($html,'Редактировать название',array('width' => '1000'));
	    }

	    // сохранение данных их формы редактирования имени компании
	    protected function chenge_name_company_AJAX() {
			$tbl = $_POST['tbl'];
			$client_id = $_GET['client_id'];
			$company = $_POST['company'];
			$id_row = $_POST['id'];
			$tbl = "CLIENTS_TBL";
			//-- START -- //  логирование
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента



			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' изменил название клиента ';
			Client::history_edit_type($client_id, $this->user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
			//-- END -- //  


			//тут обновляем название компании
			
			$query = "UPDATE  `" . constant($tbl) . "` SET  `company` =  '" . $company . "' WHERE  `id` ='" . $id_row . "'; ";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			$html = 'Имя клиента изменено на « '.$company.' »';
			$this->responseClass->addMessage($html,'successful_message');
		}
				    
		protected function get_adres_AJAX() {
		    $id_row = $_POST['id_row'];
		    $tbl = "CLIENT_ADRES_TBL";
		    $query = "SELECT * FROM " . constant($tbl) . " WHERE `id` = '" . $id_row . "'";
		    $result = $this->mysqli->query($query) or die($this->mysqli->error);
		    if ($result->num_rows > 0) {
		        while ($row = $result->fetch_assoc()) {
		            $arr_adres = $row;
		        }
		    }
		    extract($arr_adres, EXTR_PREFIX_SAME, "wddx");			        
		    //получаем контент для окна
		    ob_start();
		    include ('./skins/tpl/clients/client_folder/client_card/edit_adres.tpl');
		    $content = ob_get_contents();
		    ob_get_clean();
		    echo $content;
		    exit;
		}
				    
		protected function edit_adress_row_AJAX() {
		    $id_row = $_POST['id'];
		    $tbl = "CLIENT_ADRES_TBL";
		    //-- START -- //  логирование
		    $client_id = $_GET['client_id'];
		    $client_name_i = Client::get_client_name($client_id); // получаем название клиента
		    $user_n = $this->user_name.' '.$this->user_last_name;
		    $text_history = $user_n.' отредактировал адрес клиента '.$client_name_i.' ';
		    Client::history_edit_type($client_id, $this->user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
		    //-- END -- //  
		    //-- START --// сохранение данных
		    
		    $query = "UPDATE  `" . constant($_POST['tbl']) . "` SET  
			`city` =  '" . $_POST['city'] . "',
			`street` =  '" . $_POST['street'] . "',
			`house_number` =  '" . $_POST['house_number'] . "', 
			`korpus` =  '" . $_POST['korpus'] . "',
			`office` =  '" . $_POST['office'] . "',
			`liter` =  '" . $_POST['liter'] . "', 
			`bilding` =  '" . $_POST['bilding'] . "',
			`postal_code` =  '" . $_POST['postal_code'] . "',
			`note` =  '" . $_POST['note'] . "' WHERE  `id` ='" . $_POST['id'] . "';";
				    $result = $this->mysqli->query($query) or die($this->mysqli->error);
			echo '{
			       "response":"1",
			       "text":"Данные сохранены"
			}';
		    //-- END --// сохранение данных
		    exit;
		}
		protected function delete_adress_row_AJAX() {
		    $id_row = $_POST['id_row'];
		    $tbl = "CLIENT_ADRES_TBL";
		    $client_id = $_GET['client_id'];
		    //-- START -- //  логирование
		    $client_name_i = Client::get_client_name($client_id); // получаем название клиента
		    $user_n = $this->user_name.' '.$this->user_last_name;
		    $text_history = $user_n.' удалил(а) адрес клиента '.$client_name_i.' ';
		    Client::history_delete_type($client_id,$this->user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
		    //-- END -- //  

		    $query = "DELETE FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
		    $result = $this->mysqli->query($query) or die($this->mysqli->error);
		    
			$html = 'Данные успешно удалены';
			$this->responseClass->addMessage($html,'successful_message');
		    // exit;
		}
		protected function add_new_adress_row_AJAX() {
		    //-- START -- //  логирование
		    $client_id = $_GET['client_id'];
		    $client_name_i = Client::get_client_name($client_id); // получаем название клиента
		    $user_n = $this->user_name.' '.$this->user_last_name;
		    $text_history = $user_n.' создал новый адрес для клиента '.$client_name_i.' ';
		    Client::history($this->user_id, $text_history ,'add_new_adress_row',$_GET['client_id']);
		    //-- END -- //  логирование
		    $tbl = 'CLIENT_ADRES_TBL';
		    $query = "";
		    $adres_type = (isset($_POST['adress_type']) && $_POST['adress_type'] != "") ? $_POST['adress_type'] : 'office';
		    $query = "INSERT INTO `" . constant($tbl) . "` SET 
				`parent_id` = '" . addslashes($_POST['parent_id']) . "',
				`table_name` = '" . addslashes($_POST['tbl']) . "',
				`adress_type` = '" . addslashes($adres_type) . "',
				`city` = '" . addslashes($_POST['city']) . "',
				`street` = '" . addslashes($_POST['street']) . "',
				`house_number` = '" . addslashes($_POST['house_number']) . "',
				`korpus` = '" . addslashes($_POST['korpus']) . "',
				`office` = '" . addslashes($_POST['office']) . "',
				`liter` = '" . addslashes($_POST['liter']) . "',
				`bilding` = '" . addslashes($_POST['bilding']) . "',
				`postal_code` = '" . addslashes($_POST['postal_code']) . "',
				`note` = '" . addslashes($_POST['note']) . "'
				;";
				        
		    $result = $this->mysqli->query($query) or die($this->mysqli->error);
		    // echo $this->mysqli->insert_id;

		    $html = 'Данные добавлены';
			$this->responseClass->addMessage($html,'successful_message');
			// добавляем функцию JS
			$this->responseClass->addResponseFunction('edit_general_info');
		    // exit;
		}
		
		// окно добавления адреса	    
		protected function new_adress_row_AJAX() {
		    ob_start();
		    include ('./skins/tpl/clients/client_folder/client_card/new_adres.tpl');
		    $html = ob_get_contents();
		    foreach ($_POST as $key => $value) {
		    	$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		    }
		    $html .= '<input type="hidden" name="AJAX" value="add_new_adress_row">';
		    ob_get_clean();
		    // echo $content;
		    // добавляем окно
			$this->responseClass->addPostWindow($html,'Заведение нового адреса',array('width' => '1000'));
		}



				    
		protected function add_new_phone_row_AJAX() {
				        
			$query = "INSERT INTO `" . CONT_FACES_CONTACT_INFO_TBL . "` SET 
			`parent_id` ='" . $_POST['client_id'] . "', 
			`table` = '" . $_POST['parent_tbl'] . "', 
			`type` = 'phone', 
			`telephone_type` = '" . $_POST['type_phone'] . "', 
			`contact` = '" . $_POST['telephone'] . "',
			`dop_phone` = '" . ((trim($_POST['dop_phone']) != "" && is_numeric(trim($_POST['dop_phone']))) ? trim($_POST['dop_phone']) : '') . "';";
				        
				        
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$id_i = $this->mysqli->insert_id;

			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' завел новый контактный телефон для клиента '.$client_name_i.'(id = '.$id_i.') ';
			Client::history($this->user_id, $text_history ,'add_new_phone',$_POST['client_id']);
			//-- END -- //  логирование

			echo $id_i;
			exit;
		}
				    
		// добавление телефона или любой другой небольшой информации по клиенту
		protected function add_new_other_row_AJAX() {
			$query = "INSERT INTO `" . CONT_FACES_CONTACT_INFO_TBL . "` SET 			
							
				`parent_id` ='" . $_POST['client_id'] . "', 
				`table` = '" . $_POST['parent_tbl'] . "', 
				`type` = '" . $_POST['type'] . "', 
				`telephone_type` = '', 
				`contact` = '" . $_POST['input_text'] . "',
				`dop_phone` = '';";

			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$insert_id = $this->mysqli->insert_id;
			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;        
			$text_history = $user_n.' завел новую запись '.$_POST['type'].' для клиента '.$client_name_i.'(id = '.$insert_id.') ';
			Client::history($this->user_id, $text_history ,'add_new_other',$_POST['client_id']);
			//-- END -- //  логирование

			echo $insert_id;
			exit;
		}
				    
		// удаление телефона или любой другой небольшой информации по клиенту
		protected function delete_dop_cont_row_AJAX() {
			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$tbl = "CONT_FACES_CONTACT_INFO_TBL";
			$id_row = $_POST['id'];
			Client::history_delete_type($client_id, $this->user_id, 'Удалена строка из контактной информации (телефон/Emeil/Fb/VK)' ,'delete_dop_cont_row',$tbl,$_POST,$id_row);
			//-- END -- //  логирование

			$query = "DELETE FROM `" . CONT_FACES_CONTACT_INFO_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			// echo "OK";
			// exit;
			$html = 'Строка удалена.';
			$this->responseClass->addMessage($html,'successful_message');	
		}
		
		protected function show_cont_face_in_json_AJAX() {
			$query = "SELECT * FROM `" . CLIENT_CONT_FACES_TBL . "` WHERE `id` = '" . $_POST['id'] . "'";
			$arr = array();
			        
			// echo $query;exit;
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if ($result->num_rows > 0) {
			    while ($row = $result->fetch_assoc()) {
			        $arr[] = $row;
			    }
			}
			        
			$my_json = json_encode($arr);
			print $my_json;
			exit;
		}
			    
		protected function contact_face_edit_form_AJAX() {
		    $id_row = $_POST['id'];
		    $tbl = "CLIENT_CONT_FACES_TBL";

			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' отредактировал данные из контактного лица '.$client_name_i.' ';

			Client::history_edit_type($client_id, $this->user_id, $text_history ,'edit_contact_face',$tbl,$_POST,$id_row);
			//-- END -- //  логирование

			$query = "UPDATE  `" . constant($tbl) . "` SET  
						`surname` =  '" . $_POST['surname'] . "',
						`last_name` =  '" . $_POST['last_name'] . "',
						`name` =  '" . $_POST['name'] . "', 
						`position` =  '" . $_POST['position'] . "',
						`department` =  '" . $_POST['department'] . "',
						`note` =  '" . $_POST['note'] . "' WHERE  `id` ='" . $id_row . "';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			echo '{
					"response":"1",
					"text":"Данные успешно обновлены"
				}';
			exit;
		}
			    
		protected function contact_face_new_form_AJAX() {
			$query = "INSERT INTO  `" . CLIENT_CONT_FACES_TBL . "` SET  
						`client_id` =  '" . $_POST['parent_id'] . "',
						`surname` =  '" . $_POST['surname'] . "',
						`last_name` =  '" . $_POST['last_name'] . "',
						`name` =  '" . $_POST['name'] . "', 
						`position` =  '" . $_POST['position'] . "',
						`department` =  '" . $_POST['department'] . "',
						`note` =  '" . $_POST['note'] . "' ";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);

			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' создал новый контакт для клиента '.$client_name_i.' ';
			Client::history($this->user_id, $text_history ,'add_new_contact_row',$_GET['client_id']);
			//-- END -- //  логирование
			echo '{
					       "response":"1",
					       "id":"' . $this->mysqli->insert_id . '",
					       "text":"Данные успешно добавлены"
					      }';
			exit;
		}
			    
		protected function edit_client_dop_information_AJAX() {
			$id_row = $_POST['id'];
			$tbl = "CLIENTS_TBL";
			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' обновил блок доп. инфо. у клиента '.$client_name_i.' ';
			Client::history_edit_type($client_id,$this->user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
			//-- END -- //  

			$query = "UPDATE  `" . CLIENTS_TBL . "` SET  
				`dop_info` =  '" . $_POST['dop_info'] . "',
				`ftp_folder` =  '" . $_POST['ftp_folder'] . "' WHERE  `id` ='" . $_POST['id'] . "';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			echo '{
			       "response":"1",
			       "text":"Данные успешно обновлены"
			      }';
			exit;
		}
			    
		protected function delete_cont_face_row_AJAX() {
			$id_row = $_POST['id'];
			$tbl = "CLIENT_CONT_FACES_TBL";
			//-- START -- //  логирование
			$client_id = $_GET['client_id'];
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' удалил контактное лицо у клиента '.$client_name_i;
			Client::history_delete_type($client_id,$this->user_id, $text_history ,'delete_cont_face',$tbl,$_POST,$id_row);
			//-- END -- //  

			$query = "DELETE FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			$html = 'Контактное лицо успешно удалено.';
			$this->responseClass->addMessage($html,'successful_message');
		}
			    
		protected function delete_cont_requisits_row_AJAX() {
			$id_row = $_POST['id'];
			$tbl = $_POST['tbl'];

			$query = "DELETE FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			echo '{
			       "response":"1",
			       "text":"Данные успешно удалены"
			      }';
			exit;
		}

		protected function client_delete_AJAX() {
			$outer = Client::delete_for_manager($_POST['id'], $this->user_id);
			if ($outer == '1') {
				$client_id = $_GET['client_id'];

				$Client = Client::get_client_informationDatabase($client_id);

			    $client_name_i = $Client['company']; // получаем название клиента
			    
			    // сообщение об отказе от клиента
			    $text = '';
			    $clien_href = '<a href="'.HOST.'/?page=clients&section=client_folder&subsection=client_card_table&client_id='.$Client['id'].'">'.$client_name_i.'</a>';
			    if(isset($_POST['text'])){
			    	$text .= 'Куратор '.$this->user_name.' '. $this->user_last_name.' отказался от клиента '.$clien_href.'. ';
			    	$text .= '<br>Причина: '.$_POST['text']; 
			    }else{
			    	$text .= 'Куратор '.$this->user_name.' '. $this->user_last_name.' отказался от клиента '.$clien_href.' не указав причину.';
			    }

			    Client::history($this->user_id, $text ,'rejection_of_the_client',$_GET['client_id']);
			    

			    // сообщение
		        $message = 'Вы успешно откреплены от клиента. Спасибо.<br>';
		        $message .= $outer;
				$this->responseClass->addMessage($message,'successful_message');	

				// echo "test";
				// JS function
				$options['href'] = '?page=clients&section=clients_list';
				$this->responseClass->addResponseFunction('location_href',$options);
			    
			    include_once('mail_class.php');
			    $mailClass = new Mail;
			    // $mailClass->add_bcc('kapitonoval2012@gmail.com');
				// $mailClass->send('kapitonoval2012@gmail.com','os@apelburg.ru','Отказ от клиента',$text);		
				$mailClass->send('ab@apelburg.ru','os@apelburg.ru','Отказ от клиента',$text);		
			} 
			else {
			   	// сообщение
		        $message = 'Нет смысла отказываться от клиента, куратором которого вы не являетесь.';
				$this->responseClass->addMessage($message,'error_message');

			}
			
		}

		// открепление от клиента
		static function delete_for_manager($client_id,$manager_id){
			global $mysqli;
			$query = "SELECT * FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$client_id."'";
			// echo $query;
			$result = $mysqli->query($query) or die($mysqli->error);
			$relate_managers = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$relate_managers[$row['manager_id']] = $row;
				}
			}
				
			// если менеджер не прикреплён
			if(!isset($relate_managers[$manager_id])){
				return 0;
			}

			// если прикреплён ещё кто-то
			if(count($relate_managers) > 1){
				$query = "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `manager_id` = '".(int)$manager_id."';";
			}else{
				// открепить менеджера от клиента в пользу юзера для раздачи	
				$query ="UPDATE  `".RELATE_CLIENT_MANAGER_TBL."` SET  `manager_id` =  '61' WHERE `client_id` = '".(int)$client_id."' AND `manager_id` = '".(int)$manager_id."';";	
			}		
			$result = $mysqli->multi_query($query) or die($mysqli->error);	
			return 1;
		}
			    
		protected function update_requisites_AJAX() {
			$query = "
				UPDATE  `" . CLIENT_REQUISITES_TBL . "` SET
				`client_id`='" . $_POST['client_id'] . "', 
				`company`='" . $_POST['company'] . "', 
				`comp_full_name`='" . $_POST['form_data']['comp_full_name'] . "', 
				`postal_address`='" . $_POST['form_data']['postal_address'] . "', 
				`legal_address`='" . $_POST['form_data']['legal_address'] . "', 
				`inn`='" . $_POST['form_data']['inn'] . "', 
				`kpp`='" . $_POST['form_data']['kpp'] . "', 
				`bank`='" . $_POST['form_data']['bank'] . "', 
				`bank_address`='" . $_POST['form_data']['bank_address'] . "', 
				`r_account`='" . $_POST['form_data']['r_account'] . "', 
				`cor_account`='" . $_POST['form_data']['cor_account'] . "', 
				`ogrn`='" . $_POST['form_data']['ogrn'] . "', 
			    `bik`='" . $_POST['form_data']['bik'] . "', 
				`okpo`='" . $_POST['form_data']['okpo'] . "', 
				`dop_info`='" . $_POST['form_data']['dop_info'] . "' WHERE id = '" . $_POST['requesit_id'] . "';";
			

			$get__clients_persons_arr = $this->get__clients_persons();
			foreach ($_POST['form_data']['managment1'] as $key => $val) {
			    if (trim($val['id']) != "") {
			        $query.= "UPDATE  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  ";
					$query.= "`requisites_id` =  '" . $val['requisites_id'] . "',";
					$query.= "`type` =  '" . $val['type'] . "',";
					$query.= "`post_id` =  '" . $val['post_id'] . "',";
					if(isset($get__clients_persons_arr[$val['post_id']])){
						$query.= "`position` =  '" . $get__clients_persons_arr[$val['post_id']]['position'] . "',";	
						$query.= "`position_in_padeg` =  '" . $get__clients_persons_arr[$val['post_id']]['position_in_padeg'] . "',";
					}
					$query.= "`basic_doc` =  '" . $val['basic_doc'] . "',";
					$query.= "`name` =  '" . $val['name'] . "',";
					$query.= "`name_in_padeg` =  '" . $val['name_in_padeg'] . "',";
					$query.= "`acting` =  '" . $val['acting'] . "'";
					$query.= " WHERE  `id` ='" . $val['id'] . "'; ";
			    }else {
			        
						if($val['acting'] == 1){
							 $query.= "UPDATE  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  ";
							 $query.= "`acting` =  '0'";
							$query.= " WHERE  `requisites_id` ='" . $val['requisites_id'] . "'; ";
						}

						$query.= "INSERT INTO  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  
						`requisites_id` =  '" . $val['requisites_id'] . "',
						`type` =  '" . $val['type'] . "',
						`post_id` =  '" . $val['post_id'] . "',
						`basic_doc` =  '" . $val['basic_doc'] . "',
						`name` =  '" . $val['name'] . "',
						`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
						`acting` =  '" . $val['acting'] . "';";
			    }
			}
			$result = $this->mysqli->multi_query($query) or die($this->mysqli->error);
			echo '{
				    "response":"1",
					"text":"Данные успешно обновлены"
				}';
			
			exit;
		}

		protected function get__clients_persons(){
			global $mysqli;
			$query = "SELECT * FROM `".CLIENT_PERSON_REQ_TBL."`";
			$arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);				
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;
				}
			}
			return $arr;
		} 
			    
		protected function create_new_requisites_AJAX() {
			$query = "
				INSERT INTO `" . CLIENT_REQUISITES_TBL . "` SET id = '" . $_POST['requesit_id'] . "',
				`client_id`='" . $_POST['client_id'] . "', 
				`company`='" . $_POST['company'] . "', 
				`comp_full_name`='" . $_POST['form_data']['comp_full_name'] . "', 
				`postal_address`='" . $_POST['form_data']['postal_address'] . "', 
				`legal_address`='" . $_POST['form_data']['legal_address'] . "', 
				`inn`='" . $_POST['form_data']['inn'] . "', 
				`kpp`='" . $_POST['form_data']['kpp'] . "', 
				`bank`='" . $_POST['form_data']['bank'] . "', 
				`bank_address`='" . $_POST['form_data']['bank_address'] . "', 
				`r_account`='" . $_POST['form_data']['r_account'] . "', 
				`cor_account`='" . $_POST['form_data']['cor_account'] . "', 
				`ogrn`='" . $_POST['form_data']['ogrn'] . "', 
			    `bik`='" . $_POST['form_data']['bik'] . "', 
				`okpo`='" . $_POST['form_data']['okpo'] . "', 
				`dop_info`='" . $_POST['form_data']['dop_info'] . "'
				";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			// запоминаем id созданной записи
			$req_new_id = $this->mysqli->insert_id;
			
			if (isset($_POST['form_data']['managment1'])) {
			    $query = "";
			    foreach ($_POST['form_data']['managment1'] as $key => $val) {
			        $query.= "INSERT INTO  `" . CLIENT_REQUISITES_MANAGMENT_FACES_TBL . "` SET  
						`requisites_id` =  '" . $req_new_id . "',
						`type` =  '" . $val['type'] . "',
						`post_id` =  '" . $val['post_id'] . "',
						`basic_doc` =  '" . $val['basic_doc'] . "',
						`name` =  '" . $val['name'] . "',
						`name_in_padeg` =  '" . $val['name_in_padeg'] . "',
						`acting` =  '" . $val['acting'] . "';";
			    }
			    
			    $result = $this->mysqli->multi_query($query) or die($this->mysqli->error);
			}
			echo '{
				    "response":"1",
					"id_new_req":"' . $req_new_id . '",
					"company":"' . $_POST['company'] . '"
				}';        
			exit;
		}
			    
		protected function delete_requesit_row_AJAX() {
			$id_row = $_POST['id'];
			$query = "DELETE FROM " . CLIENT_REQUISITES_TBL . " WHERE `id`= '" . $id_row . "'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			$html = 'Данные успешно удалены';
			$this->responseClass->addMessage($html,'system_message');
		}			    
			    
		protected function update_curator_list_for_client_AJAX() {
			$client_id = $_GET['client_id'];
			$json = $_POST['managers_id'];
			$manager_id = json_decode($json,true);
			//-- START -- //  логирование
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' обновил список кураторов для клиента '.$client_name_i;
			Client::history($this->user_id, $text_history ,'update_curator_list',$_GET['client_id']);
			//-- END -- //  логирование
			
			$str_id = '';
			$query = "";
			foreach($manager_id as $k => $v){

			    $query .= "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` SET 
			    `client_id` = '".$client_id."', 
			    `manager_id` = '".$v."';";

			    $str_id .= ($str_id=='')?$v:', '.$v;
			}
			// echo $str_id;
			$query1 = "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".$client_id."';";
			// $result = $mysqli->query($query) or die($mysqli->error);
			// ECHO $query;
			$result = $this->mysqli->multi_query($query1.$query) or die($this->mysqli->error);
			

			$html = 'Данные успешно обновлены';
			$this->responseClass->addMessage($html,'system_message');
		}
			    
		protected function remove_curator_AJAX() {
			$client_id = $_GET['client_id'];
			$manager_id = $_POST['id'];
			Client::remove_curator($client_id,$manager_id);
			//-- START -- //  логирование
			$client_name_i = Client::get_client_name($client_id); // получаем название клиента
			$User = $this->getUserDatabase($manager_id);// получаем Фамилию Имя менеджера
			$manager_name_i = $User['name'].' '.$User['last_name'];
			$user_n = $this->user_name.' '.$this->user_last_name;
			$text_history = $user_n.' удалил куратора '.$manager_name_i.' у клиента '.$client_name_i;
			Client::history($this->user_id, $text_history ,'remove_curator',$_GET['client_id']);
			//-- END -- //  логирование
			$html = 'Куратор удален';
			$this->responseClass->addMessage($html,'system_message');
		}
		
		protected function new_person_type_req_AJAX() {
			$query = "INSERT INTO  `" . CLIENT_PERSON_REQ_TBL . "` SET  
				`type` =  '',
				`position` =  '" . $_POST['position'] . "',
				`position_in_padeg` =  '" . $_POST['position_in_padeg'] . "'";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			
			// echo $query;
			$id_row = $this->mysqli->insert_id;
			echo '{
			      "response":"1",
			      "id_new_row":"' . $id_row . '",
			      "text":"Данные успешно обновлены"
			     }';
			exit;		
		}

	// запрашивает из базы допуски пользователя
	// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
	private function get_user_access_Database_Int($id){
		global $mysqli;
		$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);				
		$int = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$int = (int)$row['access'];
			}
		}
		//echo $query;
		return $int;
	}

	
	// прикрепляет клиента к запросу
	protected function attach_client_for_new_query_AJAX(){
		if(isset($_POST['client_id']) && $_POST['client_id'] == 'new_client'){
			// echo 'get_form_the_create_client1';
			// начинаем заводить клиента
			$this->get_form_the_create_client_AJAX('insert_new_client_for_new_qury');
			
		}else{
			// создаём запрос и переадресовываем на старицу запроса
			if(isset($_POST['client_id'])){
				include_once ('./libs/php/classes/rt_class.php');

				$data_arr = array();
				// echo $_SERVER['HTTP_HOST'];
				$qury_num_NEW = RT::create_new_query((int)$_POST['client_id'], $this->user_id, $data_arr,'in_work');
				// $qury_num_NEW = 10;
				$href = '?page=client_folder&client_id='.$_POST['client_id'].'&query_num='.$qury_num_NEW;
				echo '{"response":"OK","function":"location_href","href":"'.$href.'"}';	
			}
			
			exit;
		}
	}

	private function wrap_text_in_warning_message_post($text){
		$html = '<div class="warning_message"><div>';	
		$html .= $text;
		$html .= '</div></div>';
		return $html;
	}

	// новая форма заведения нового клиента
	public function get_form_the_create_client_AJAX($AJAX = 'insert_new_client'){
		// echo 'get_form_the_create_client_AJAX<pre>';
		// print_r($_POST);
		// echo '</pre>';
		$html = '';
		$html .= '<div id="create_client">';
		if(isset($_POST['company']) && trim($_POST['company']) == ''){
			$text = 'Введите название компании';
			$html .= $this->wrap_text_in_warning_message_post($text);
		}
		$html .= '<form>';
			foreach ($_POST as $key => $value) {
				$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
			$html .= '<table>';
				$html .= '<tr>';
					$html .= '<td>Название</td>';
					$html .= '<td>';
					$html .= '<input type="text" name="company" placeholder="Название компани" value="'.((isset($_POST['company'])?$_POST['company']:'НОВЫЙ КЛИЕНТ '.$this->check_number_new_clients())).'">';
					$html .= '<input type="hidden" name="AJAX" value="'.$AJAX.'">';
					$html .= '</td>';
				$html .= '</tr>';
				$html .= '<tr>';
					$html .= '<td colspan="2"></td>';
				$html .= '</tr>	';
				$html .= '<tr>';
					$html .= '<td>Дополнительная информация</td>';
					$html .= '<td><textarea type="text" name="dop_info">'.((isset($_POST['dop_info'])?$_POST['dop_info']:'')).'</textarea></td>';
				$html .= '</tr>	';
			$html .= '</table>';

		// unset($_POST['AJAX']);
		// unset($_POST['company']);
		// unset($_POST['dop_info']);

		

		$html .= '</form>';

		$html .= '</div>';

		// echo '654654654sasasd312';
		$this->responseClass->addPostWindow($html,'Новый клиент',array('width' => '500'));
		// echo '654654654sasasd312';
		echo $this->responseClass->getResponse();					
				// exit;
		// echo json_encode($this->responseClass);
		exit;
			
	}

	// получаем номер нового клиента
	private function check_number_new_clients(){
		global $mysqli;
		$query = "SELECT COUNT(*) AS count FROM `".CLIENTS_TBL."` WHERE `company` LIKE '%НОВЫЙ КЛИЕНТ%'";
		$count = 0;
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$count = $row['count'];
			}
		}
		return ($count+1);
	}


	// кнопка новый запрос из кабинета
	public function button_new_query_wtidth_cabinet(){
		$html = '';
		if($this->user_access == 1){
			include_once ('./libs/php/classes/rt_class.php');

			$data_arr = array();
			// echo $_SERVER['HTTP_HOST'];
			$qury_num_NEW = RT::create_new_query(0, 24, $data_arr);
			// $qury_num_NEW = 10;
			$href = '?page=cabinet&section=requests&subsection=query_wait_the_process';
			echo '{"response":"OK","function":"location_href","href":"'.$href.'"}';
			exit;
		}
		// если мы находимся в клиенте
		if(isset($_GET['client_id']) && $_GET['client_id'] != ''){
			include_once ('./libs/php/classes/rt_class.php');

			$data_arr = array();
			// echo $_SERVER['HTTP_HOST'];
			$qury_num_NEW = RT::create_new_query((int)$_GET['client_id'], $this->user_id, $data_arr,'in_work');
			// $qury_num_NEW = 10;
			$href = '?page=client_folder&client_id='.$_GET['client_id'].'&query_num='.$qury_num_NEW;
			echo '{"response":"OK","function":"location_href","href":"'.$href.'"}';
			// header('Location: '.HOST.'/'.$href);
			exit;
		}else{ // если мы находимся вне клиента
			$html = $this->get_form_attach_the_client('attach_client_for_new_query');
			echo '{"response":"'.(isset($_POST['client_name_search'])?"OK":"show_new_window").'","html":"'.base64_encode($html).'","title":"Выберите клиента",'.(($this->i>30)?'"height":"600",':'').'"width":"1000"}';
			exit;
		}
		
	}

	// вывод пришедших данных в новом окне
	private function chow_post_arr_in_new_window_ajax(){
		$html = $this->print_arr($_POST);
		echo '{"response":"show_new_window_simple", "html":"'.base64_encode($html).'","width":"600"}';
	}

	

	// protected function attach_client_for_new_query_AJAX(){
	// в cabinet_class.php		
	// }

	// возвращает строку поиска по клиентам
	protected function get_client_sherch_form(){
		$html = '';
		// if(isset($_POST['AJAX'])){unset($_POST['AJAX']);}
		$html .= '<form id="js--window_client_sherch_form">';
		// foreach ($_POST as $key => $value) {
		// 	$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		// }
		$html .= '<div class="quick_bar_tbl"><div class="search_div">
                <div class="search_cap">Поиск:</div>
                <div class="search_field">                    
                    <input id="client_name_search" name="client_name_search" placeholder="поиск по клиентам" type="text" onclick="" value=""><div class="undo_btn"><a href="#" onclick="$(this).parent().prev().val(\'\');return false;">×</a></div></div>
                <div class="search_button" onclick="search_and_show_client_list();">&nbsp;</div>
                <div class="clear_div"></div>
            </div></div>';
		// $html .= '<input type="text" name="client_name_search">';
		$html .= '<input type="hidden" name="AJAX" value="create_new_query">';
		// $html .= '<input type="hidden" name="client_id" value="'.$_POST['client_id'].'">';
		$html .= '</form>';
		//echo '{"response":"show_new_window","html":"'.base64_encode($html).'","title":"Поиск клиента","height":"300","width":"1000"}';
		return $html;
	}

	// форма выбора клиента 
	private function get_form_attach_the_client($AJAX = 'test'){
		global $mysqli;
		$html ='';
		$clients = array();
		$clients[0]['id'] = 'new_client';
		$clients[0]['company'] = 'НОВЫЙ КЛИЕНТ';

		if(isset($_POST['client_name_search'])){
			if($this->user_access == 1){
					
				$query = "SELECT * FROM `".CLIENTS_TBL."` ";
				// $query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id`= '".(int)$_POST['client_id']."' ";
				if($_POST['client_name_search'] != 'all' && $_POST['client_name_search'] != 'все'){
					$query .= " AND `company` LIKE '%".trim($_POST['client_name_search'])."%'";	
				}
					
			}else{				
				$query = "SELECT `client_id` FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `manager_id` = '".$this->user_id."'";
					
				$result = $mysqli->query($query) or die($mysqli->error);				
				$id_str = "'0'";
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$id_str .= ",'".$row['client_id']."'";
					}
				}
				// echo $query;					
				
				$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` IN (".$id_str.")";
				if($_POST['client_name_search'] != 'all' && $_POST['client_name_search'] != 'все'){
					$query .= " AND `company` LIKE '%".trim($_POST['client_name_search'])."%'";	
				}
				
				
			}		
			$query .= "  ORDER BY `company`";			
			// $html .= $query;		
			// Запрос с сортировкой почему-то не хочет выводит ь некоторые компании
			// к примеру не выводит компанию *Морской салон ЗАО  - id = 18
			// $query = "SELECT * FROM `".CLIENTS_TBL."`  order by company ASC;";
			$result = $mysqli->query($query) or die($mysqli->error);				
			
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$clients[] = $row;
				}
			}
		}
		$html .= isset($_POST['client_name_search'])?'':$this->get_client_sherch_form();

		$html .= '<form  id="chose_client_tbl">';

		$html .='<table>';
		$column = 3;
		$num_client  = count($clients);
		$num_rows = ceil($num_client / $column);
		$td_count = $num_rows*$column;
		$clients_array = array();
				
		for ($td=0; $td <= $num_rows; $td++) { 
			for ($col=0; $col < $column; $col++) { 
				$html .= ($col == 0)?'<tr>':'';
				// $html .= '<td>';
				$id = ($col>0)?$td + $num_rows*$col+$col:$td + $num_rows*$col;
				if(isset($clients[$id])){
					$checked = '';
			    	// $checked = ($clients[$id]['id'] == $_POST['client_id'])?'class="checked checked_client"':'';
			    	$html .= '<td '.$checked.' data-id="'.$clients[$id]['id'].'" id="client_'.$clients[$id]['id'].'">'.$clients[$id]['company']."</td>";
		    		// если присутствует выбранный клиент, ставим флаг
		    		if($checked!=''){$f_r = 1;}	
		    	}else{
		    		$html .= "<td></td>";
		    	}
				$html .= ($col == $column)?'</tr>':'';
			}
		}
		$this->i = $num_rows;

		$html .= '</table>';
		$html .= '<input type="hidden" value="'.$AJAX.'" name="AJAX">';
		// $html .= '<input type="hidden" value="" name="manager_id">';
		if(isset($_POST)){
			unset($_POST['AJAX']);
			foreach ($_POST as $key => $value) {
				$html .= '<input type="hidden" value="'.$value.'" name="'.$key.'">';	
			}	
		}

		if(!isset($_POST['client_id'])){
			$html .= '<input type="hidden" value="" name="client_id">';
		}
				
		$html .= '<form>';
		return $html;
	}

	// заведение нового клиента при создании запроса
	protected function insert_new_client_for_new_qury_AJAX(){
		if(!isset($_POST['company']) || trim($_POST['company']) == ''){
			$this->get_form_the_create_client_AJAX('insert_new_client_for_new_qury');
			exit;
		}
			switch ($this->user_access) {
				case '1':
					$message = 'заведение запроса из под админа не предусмотрено
					т.к. это слишком трудоемкий и долгий процесс
					до запуска  не актуально
					вывод кнопки будет отключен для админа';
					
					// $message .= $this->print_arr($_POST);
					// $message = 'Для корректного сохранения данных по оплате, сначало заполните поле "дата"!!!';
					$json = '{"response":"OK","show_new_window_simple","html":"'.base64_encode($message).'"}';
					echo $json;
					exit;
					break;
				case '5':
					// создаём клиента
					$this->client_id = $this->create_new_client($_POST['company'], $_POST['dop_info']);

					// куратором нового клиента будет менеджер
					$this->attach_relate_manager($this->client_id,$this->user_id);


					// создаем новый запрос
					include_once ('./libs/php/classes/rt_class.php');
					$data_arr = array();
					$qury_num_NEW = RT::create_new_query($this->client_id, $this->user_id, $data_arr,'in_work');

					$href = '?page=client_folder&client_id='.$this->client_id.'&query_num='.$qury_num_NEW;
					echo '{"response":"OK","function":"location_href","href":"'.$href.'"}';
					exit;
					break;
				default:
					break;
			}

		$message = 'в методе insert_new_client_for_new_qury_AJAX() что-то пошло не так ... client_class.php';
		$json = '{"response":"OK","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
		echo $json;
		exit;	
	}


	// заведение нового клиента при присвоении его к существующему запросу
	protected function insert_new_client_AJAX(){
		if(!isset($_POST['company']) || trim($_POST['company']) == ''){
			// echo '<pre>';
			// 	print_r($_POST);
			// 	echo '</pre>';
			// 	exit;
			$this->get_form_the_create_client_AJAX();
				// $this->insert_new_client_for_new_qury_AJAX();
			exit;
		}else{

			//если клиент был создан из запроса
			if(isset($_POST['row_id']) && (int)$_POST['row_id'] > 0){
				$_POST['rt_list_id'] = $_POST['row_id'];
			}
			// echo '<pre>';
			// print_r($_POST);
			// echo '</pre>';
			if(isset($_POST['rt_list_id']) && (int)$_POST['rt_list_id'] > 0){
				switch ($this->user_access) {
					case '1':
						// запрашиваем окно со списком всех менеджеров 
						// для выбора куратора клиента
						$html = $this->get_choose_curators();
						echo '{"response":"show_new_window","title":"Выберите менеджера","html":"'.base64_encode($html).'"}';
						exit;
						break;
					case '5':
						$this->client_id = $this->create_new_client($_POST['company'], $_POST['dop_info']);

						// куратором нового клиента будет менеджер
						// сразу же прикрепляем его
						$this->attach_relate_manager($this->client_id,$this->user_id);

						// прикрепляем к запросу менеджера(ов)
						$men_arr[$this->user_id] = $this->user_id;
						$this->attach_for_query_many_managers($men_arr,$this->client_id);

						if(isset($_POST['query_status']) && $_POST['query_status'] == 'in_work'){
							global $mysqli;
							// include_once ('./cabinet/cabinet_class.php');
							include_once(ROOT.'/libs/php/classes/cabinet/cabinet_class.php');
							$Cabinet = new Cabinet;
							$this->Query = $Cabinet->get_query((int)$_POST['row_id']);
							
							if($_POST['query_status'] != $this->Query['status']){
								$query = "UPDATE  `".RT_LIST."` SET";
								$query .= " `status` =  'in_work' ";
								$query .= "WHERE  `id` ='".$this->Query['id']."';";
								$result = $mysqli->query($query) or die($mysqli->error);
							}
							// echo '<pre>';
							// print_r($_POST);
							// echo '</pre>';
							$link = '?page=client_folder&client_id='.$_POST['client_id'].'&query_num='.$_POST['query_num'];
							// переадресация на другую вкладку
							$option['href'] = ''.HOST.'/'.$link;
								
							echo '{"response":"OK","function":"location_href","href":"'.$option['href'].'"}';
							exit;
						}


						echo '{"response":"OK","function":"reload_order_tbl"}';
						exit;
						break;
					default:
						break;

				exit;
				}
			}else{
				
				if(isset($_POST['options']) && $_POST['options'] == 'for_me'){
					// создаётся из раздела клиенты
					$client_id = $this->create_new_client($_POST['company'], $_POST['dop_info']);
					global $mysqli;
					$query = "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` SET 
					    `client_id` = '".$client_id."', 
					    `manager_id` = '".$this->user_id."';";
					$result = $mysqli->query($query) or die($mysqli->error);
					$html = 'клиент Добавлен';
					$this->responseClass->addMessage($html,'successful_message');
					$href = "http://".$_SERVER['HTTP_HOST']."/os/?page=clients&section=client_folder&subsection=client_card_table&client_id=".$client_id;
					$this->responseClass->addResponseFunction('location_href',array('href' => $href));
					echo $this->responseClass->getResponse();					
					exit;
				}

				
			}	
				
		}
	}

	private function create_new_client($company, $dop_info){
		global $mysqli;		
		$query ="INSERT INTO `".CLIENTS_TBL."` SET
			`set_client_date` = CURRENT_DATE(),
		    `company` = '".$this->cor_data_for_SQL($company)."',
			`dop_info` = '".$this->cor_data_for_SQL($dop_info)."'";					 
		$result = $mysqli->query($query) or die($mysqli->error);
		return $mysqli->insert_id;
	}

	// получаем форму выбора кураторов для нового клиента
	public function get_choose_curators(){
		// получаем список менеджеров
		$access_arr[] = 5;
		$managers_arr = $this->get_manager_list($access_arr);
		// echo '<pre>';
		// print_r($managers_arr);
		// echo '</pre>';
		$html = '';
		$html .= '<form  id="chose_many_curators_tbl">';
		$html .=' <div id="json_manager_arr">{}</div>';
		$html .=' <input type="hidden" name="Json_meneger_arr" value=\'\' id="json_manager_arr_val">';
				$html .='<table>';

				$count = count($managers_arr);
				for ($i=0; $i <= $count; $i) {
					$html .= '<tr>';
				    for ($j=1; $j<=3; $j++) {
				    	if(isset($managers_arr[$i])){
					    	$checked = (isset($_POST['manager_id']) && $managers_arr[$i]['id'] == $_POST['manager_id'])?'class="checked"':'';
					    	$name = ((trim($managers_arr[$i]['name']) == '' && trim($managers_arr[$i]['last_name']) == '')?$managers_arr[$i]['nickname']:$managers_arr[$i]['name'].' '.$managers_arr[$i]['last_name']);
					    	$html .= '<td '.$checked.' date-lll="'.$i.'" data-id="'.$managers_arr[$i]['id'].'">'.$name."</td>";
				    		$i++;
				    	}else{
				    		$html .= '<td  date-lll="'.$i.'"></td>';
				    		$i++;
				    	}				    	
				    }				    
				    $html .= '</tr>';
				}

				$html .= '</table>';

		$html .= '<input type="hidden" name="AJAX" value="create_new_client_and_insert_curators">';

		unset($_POST['AJAX']);
		foreach ($_POST as $key => $value) {
			$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
		}

		$html .= '</form>';

		return $html;
	}
	// создание новокго клиента и прикрепление кораторов
	protected function create_new_client_and_insert_curators_AJAX(){
		$html = '';
		$html .= $this->print_arr($_POST);

		$managers_arr = json_decode($_POST['Json_meneger_arr'], true);
		
		// если кураторы не были получены
		if(empty($managers_arr)){
			$message = "Необходимо выбрать минимум одного менеджера на обработку запроса.";
			// echo '{"response":"show_new_window","title":"Выбрать куратора","html":"'.base64_encode($this->get_choose_curators()).'"}';	
			echo '{"response":"false","function":"echo_message","message_type":"error_message","message":"'.base64_encode($message).'"}';
			exit;
		}


		// $html .= $this->print_arr(json_decode($_POST['Json_meneger_arr']));
		
		// если клиент не заведен заводим клиента
		if(!isset($_POST['client_id']) || isset($_POST['client_id']) && $_POST['client_id'] == 'new_client'){
			$message = 'Клиент успешно заведён, прикреплённые менеджеры увидят запрос';
			$this->client_id = $this->create_new_client($_POST['company'], $_POST['dop_info']);
		}else{
			// случай для редактирования списка админом
			$message = 'Список прикреплённых менеджеров успешно изменён';
			$this->client_id = (int)$_POST['client_id'];
			if(isset($_POST['row_id']) && !isset($_POST['rt_list_id'])){
				$_POST['rt_list_id'] = $_POST['row_id'];	
			}
			
		}
		

		// удаляем всех кураторов
		//$this->remove_curator_width_client($this->client_id);
		

		// // заводим новых кураторов
		// foreach ($managers_arr as $key => $user_id) {
		// 	$this->attach_relate_manager($this->client_id, $user_id);
		// }
		
		// прикрепляем к запросу менеджера(ов)
		$this->attach_for_query_many_managers($managers_arr,$this->client_id);
		
		// оповещение группы менеджеров о новом запросе
		$mail_message = "";
		include_once(ROOT.'/libs/php/classes/cabinet/cabinet_class.php');
		$Cabinet = new Cabinet;

		$this->Query = $Cabinet->get_query((int)$_POST['rt_list_id']);

		 

		$managers_email_arr = $Cabinet->get_users_email($managers_arr);
		$managers_info_arr = $this->getUserArrDatabase($managers_arr);

		
		if(count($managers_arr)>1){
			$subject = 'Вам доступен новый запрос.';
			$mail_message .= 'Вам и ещё нескольким менеджерам доступен <a href="http://www.apelburg.ru/os/?page=cabinet&section=requests&subsection=no_worcked_men&query_num='.$this->Query['query_num'].'">новый запрос № '.$this->Query['query_num'].'</a>';
			$mail_message .= '<br>';
			$mail_message .= 'P.S. Запрос так же увидят:';
			$mail_message .= '<br>';
			foreach ($managers_info_arr as $key => $manager) {
				$mail_message .= ($manager['name'] != "")?$manager['name']:'';
				$mail_message .= ' ';
				$mail_message .= ($manager['last_name'] != "")?$manager['last_name']:'';
				$mail_message .= '<br>';
			}	
		}else{
			$subject = 'Вам доступен новый запрос.';
			$mail_message .= 'Вам доступен <a href="http://www.apelburg.ru/os/?page=cabinet&section=requests&subsection=no_worcked_men&query_num='.$this->Query['query_num'].'">новый запрос № '.$this->Query['query_num'].'</a>';
			$mail_message .= '<br>';
			$mail_message .= 'P.S. Запрос доступен только Вам!';
		}
		


		$mailClass = new Mail();
							
		foreach ($managers_email_arr as $key => $email) {
			// echo $email.' *** ';
			$mailClass->send($email,'os@apelburg.ru',$subject,$mail_message);
		}
		/*
			тут нужно уведомление на почту для каждого прикреплённого менеджера !!!!!!!!!!
		*/


		echo '{"response":"OK","function":"reload_order_tbl","function2":"echo_message","message_type":"successful_message","message":"'.base64_encode($message).'"}';	
		exit;
		// echo '{"response":"OK","title":"ТЕСТ","html":"'.base64_encode($html).'"}';
	}

	// прикрепление к запросу нескольких менеджеров
	public function attach_for_query_many_managers($managers_arr,$client_id){
		// заводим переменную для хранения id списка менеджеров
		$dop_managers_id = '';
		
		// получаем первого менеджера из массива прикреплённых
		$manager_id = current($managers_arr);

		// если менеджеров более одного 
		if(count($managers_arr) > 1){
			// сохраняем список менеджеров
			$dop_managers_id = implode(',', $managers_arr);
			// запрос пока что ни за кем конкретно не закреплён
			$manager_id = 0;




		}else if( count($managers_arr) == 1 ){
			// если в списке менеджеров выбран 1 менеджер - назначаем его уратором нового клиента
			
			foreach ($managers_arr as $manager_ids => $manager_ids2) {
				$this->attach_relate_manager($client_id, $manager_ids);
			}
			
		}

		global $mysqli;		
		$query = "UPDATE  `".RT_LIST."` SET ";
		$query .= "`manager_id` =  '".$manager_id."'";
		$query .= ",`client_id` =  '".(int)$client_id."' ";
		// если прикреплял админ - запоминаем время назначения менеджера
		if($this->user_access == 1){
			$query .= ",`time_attach_manager` = NOW()";	
			
		}	
		if($this->user_id != $manager_id){
			$query .= ",`status` = 'not_process'";		
		}		
		
		$query .= ",`dop_managers_id` = '".$dop_managers_id."'";

		
		$query .= " WHERE `id` = '".(int)$_POST['rt_list_id']."';";		
				

	// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
	}

	// прикрепление кураторов
	public function attach_relate_manager($client_id, $user_id){
		global $mysqli;		
	
		// проверяем не является данный юзер на данный момент куратором этой компании
		$query =  " SELECT * FROM `".RELATE_CLIENT_MANAGER_TBL."`";
		$query .= " WHERE `client_id` = '".$client_id."'
		AND `manager_id` = '".$user_id."'";
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);		
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}

		// если нет - записываем его в кураторы
		if(count($arr) == 0){
			$query = "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` SET
			`client_id` = '".$client_id."'
			, `manager_id` = '".$user_id."'";
	        $result = $mysqli->query($query) or die($mysqli->error);
		}

		return $mysqli->insert_id;
		
	}

	// удаляем всех кураторов по клиенту
	private function remove_curator_width_client($client_id){
		global $mysqli;
		// открепить менеджера от клиента
		$query ="DELETE FROM  `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$client_id."';";	
		$result = $mysqli->multi_query($query) or die($mysqli->error);	
		return 1;
	}

	// отдаёт $html распечатанного массива
	protected function print_arr($arr){
		ob_start();
		echo '<pre>';
		print_r($arr);
		echo '</pre>';
		$content = ob_get_contents();
		ob_get_clean();
		
		return $content;
	}

	// возвращает объект клиента если в класс передан $id
	private function get_object($id){
		global $mysqli;		
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$this->info = 0;
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$this->info = $row;
				$this->name = $row['company'];
			}
		}
		//получаем телефоны, email, vk и т.д.	
		$arr = $this->get_contact_info("CLIENTS_TBL",$id);
		$this->cont_company_phone = (isset($arr['phone']))?$arr['phone']:''; 
		$this->cont_company_other = (isset($arr['other']))?$arr['other']:'';
	}

	public function search_name($name){
		global $mysqli;
		$query = "SELECT `id` FROM `".CLIENTS_TBL."` WHERE `company` = '".$name."' OR `comp_full_name` = '".$name."'";
		$result = $mysqli->query($query) or die($mysqli->error);

		$row_cnt = $result->num_rows;
		return $row_cnt;
	}
	
	static function cont_face_communications($client_id,$options = FALSE){
		global $mysqli;
	
		$tbls_constant_names = array('CLIENT_CONT_FACES_TBL','CLIENT_REQUISITES_MANAGMENT_FACES_TBL');
		if(!empty($options['tbls'])) {
		   $tbls_constant_names = (is_array($options['tbls']))? $options['tbls'] :array($options['tbls']);
		}
		foreach($tbls_constant_names as $tbl_name){
		    if($tbl_name == 'CLIENT_REQUISITES_MANAGMENT_FACES_TBL'){
			    $query_arr[] = "(SELECT position, name FROM `".constant($tbl_name)."` WHERE requisites_id IN (SELECT id FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".$client_id."')) ";
			}
		    else $query_arr[] = "(SELECT position, name FROM `".constant($tbl_name)."` WHERE `client_id` = '".$client_id."')";
		}
		// print_r($query_arr);
		$query = implode(' UNION ', $query_arr);
		//echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		while($row = $result->fetch_assoc()){
		    print_r($row);
			echo '<br>';
		}
        // 
	}

	/**
	 *	получаем инвормацию по клиенту из CLIENTS_TBL
	 *
	 *	@param 		id
	 *	@author     Алексей Капитонов
	 *	@version    14:41
	 */
	static function get_client_informationDatabase($id){
		global $mysqli;	
		$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$Client_info = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$Client_info = $row;
			}
		}
		return $Client_info;
	}

	// вывод краткой информации о клиенте
	static function get_client__information($id){
		// получаем информацию по клиенту
		global $mysqli;		

		$Client_info = self::get_client_informationDatabase($id);
		
		$company_name = '';
		
		if(!empty($Client_info)){
			$company_name = $Client_info['company'];
		}


		switch ($_SESSION['access']['access']) {
			case '1':
				$query = "SELECT * FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".$id."' 
				AND cont_faces_relation_id <> 0";# code...
				break;
			
			default:
				$query = "SELECT * FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".$id."' 
				AND cont_faces_relation_id <> 0 && `manager_id` = '".$_SESSION['access']['user_id']."'";
				break;
		}
		$relate = array();
		$contact_face['email'] = '';
		$contact_face['phone'] = '';
		$contact_face['name'] = '';

		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$relate = $row;
			}
		}


		if(isset($relate['cont_faces_relation_id'])){
			$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."` WHERE id = '".$relate['cont_faces_relation_id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			
			$contact_face_arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$contact_face_arr = $row;
					$contact_face['name'] = $row['last_name'] .' '.$row['name'].' '.$row['surname'];
				}
			}
			if(count($contact_face_arr) > 0){
				$query = "SELECT * FROM `".CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = 'CLIENT_CONT_FACES_TBL' AND `parent_id` = '".$contact_face_arr['id']."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				$contacts = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$contacts[] = $row;
					}
				}
				foreach ($contacts as $contact) {
					if(isset($contact['type']) && $contact['type'] == 'phone'){
						$contact_face['phone'] = $contact['contact'];
					}
					if(isset($contact['type']) && $contact['type'] == 'email' ){
						$contact_face['email'] = $contact['contact'];
					}
				}
			}
		}
		
		
		$get_str = '';
		$n = 0;
		foreach ($_GET as $key => $value) {
			if($key != 'client_id'){
				$get_str .= ($n==0)?'?':'&';
				$get_str .= $key.'='.$value;
				$n++;
			}
		}
		$back_without_client = '<a id="back_without_client" href="./'.$get_str.'"></a>';
		$back_without_client = '';
		include './skins/tpl/clients/client_list/condensed_information_on_the_client.tpl';
		return;
	}
	
	static function get_addres($id){
		global $mysqli;
		$parent_tbl = 'CLIENTS_TBL';
		$query = "SELECT * FROM  `".CLIENT_ADRES_TBL."` WHERE `parent_id` = '".(int)$id."' AND `table_name` = '".$parent_tbl."'";
		$arr = array();
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}
	static function get_contact_row($contact_company, $type,$array_dop_contacts_img){
		
		if(isset($type) && $type == "phone"){
			$i=1;
			$str = '<table class="table_phone_contact_information">';
			if(empty($contact_company)){return;}

			foreach($contact_company as $k=>$v){				
				if($v['type'] == $type){
					$str .= "<tr><td class='td_phone'>".$v['telephone_type']." ".$i."</td><td><div  class='del_text' data-adress-id=".$v['id'].">".$v['contact'].((trim($v['dop_phone'])!=0)?" доп.".$v['dop_phone']:'')."</div></td></tr>";	
					$i++;
				}
			}
			$str .= "</table>";	
			// echo $str;
			return $str;
		}else{

			$str = '<table class="table_other_contact_information">';
			foreach($contact_company as $k=>$v){
			if(isset($array_dop_contacts_img[trim($v['type'])])){
				$icon = $array_dop_contacts_img[trim($v['type'])];
			}else{
				@$icon = $array_dop_contacts_img['other'];
			}
				if($v['type'] != 'phone'){
					$str .= "<tr><td class='td_icons'>".$icon."</td><td><div   class='del_text' data-adress-id=".$v['id'].">".$v['contact']."<div></td></tr>";	
				}
			}
			$str .= "</table>";	
			return $str;		
		}			
	}
	static function get__clients_persons_for_requisites($type){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_PERSON_REQ_TBL."`";
		$str = "<option value=\"0\">Выберите должность...</option>".PHP_EOL;
		$result = $mysqli->query($query) or die($mysqli->error);				
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$str .= "<option value=\"".$row['id']."\" ".(($type==$row['id'])?'selected':'').">".$row['position']."</option>".PHP_EOL;
			}
		}
		return $str;
	} 
	static function edit_requsits_show_person($requisites_id){
		// array
		// лица (контрагенты) имеющие право подписи  для реквизитов в массиве
		global $mysqli;
		$arr = array();
		$query = "SELECT * FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` WHERE `requisites_id` = '".$requisites_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$arr[] = $row;
			}
		}
		return $arr;
	}
	static function edit_requsits_show_person_all($arr,$client_id){
		// echo '<pre>';
		// print_r($arr);
		// echo '</pre>';
			
		foreach ($arr as $key => $contact) {
			$get__clients_persons_for_requisites = Client::get__clients_persons_for_requisites($contact['post_id']);
			include('./skins/tpl/clients/client_folder/client_card/edit_requsits_show_person.tpl');
		}
	}
	static function get_reiting($id,$rate){
		$arr[0] = array('5','0');
		$arr[1] = array('5','5');
		$arr[2] = array('5','10');
		$arr[3] = array('5','15');
		$arr[4] = array('5','20');
		$arr[5] = array('5','25');

		$r = '<div id="rate_1" data-id="'.$id.'">
			<input type="hidden" name="review_count" value="'.$arr[$rate]['0'].'" />
			<input type="hidden" name="review_rate" value="'.$arr[$rate]['1'].'" />
		</div>';
		return $r;
	}
	public function get_contact_info_arr($tbl,$type,$parent_id){
		global $mysqli;
		$contacts = array();
		$query = "SELECT * FROM `".CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."'".(($type!='')?" AND `type` = '".$type."'":'')." AND `parent_id` = '".$parent_id."'";
		echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$contacts[] = $row;
			}
		}
		return $contacts;
	}
	static function get_contats_info_all($type, $client_id){
		// получаем данные по клиенту
		$contacts = self::get_contact_info_arr('CLIENTS_TBL',$type,$client_id);

		// получаем массив контактных лиц клиента
		$contact_faces_contacts = Client::cont_faces($client_id);
		foreach($contact_faces_contacts as $k => $this_contact_face){
			unset($arr);
			// получаем для каждого контактного лица 
			// необходимые нам контактные данные (email, www, fb, vk ......)
			$arr = self::get_contact_info_arr("CLIENT_CONT_FACES_TBL",$type,$this_contact_face['id']);
			foreach ($arr as $key => $val) {
				$contacts[] = $val;
			}
		}
		return $contacts;
	}	
	public function get_contact_info($tbl,$parent_id){
		global $mysqli;
		$query = "SELECT * FROM `".CONT_FACES_CONTACT_INFO_TBL."` WHERE `table` = '".$tbl."' AND `parent_id` = '".$parent_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$contact = array('phone'=>'','other'=>'');//инициализируем массив
		$contacts = array();		
		$contact['phone'] = '<table class="table_phone_contact_information"></table>';
		$contact['other'] = '<table class="table_other_contact_information"></table>';
		
		//  
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$contacts[] = $row;
			}
			$contact['phone'] = self::get_contact_row($contacts, 'phone',self::$array_img);
			$contact['other'] = self::get_contact_row($contacts, 'other',self::$array_img);
		}
		return $contact;
	}
	static function requisites($id) {
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			return $array;
		}
		return $array;
	}
	static function get_requisites($client_id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$client_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);					
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
		}
		return $array;
	}
	static function fetch_requisites($requisit_id){
	    // Я ИСПОЛЬЗУЮ ПРИ СОЗДАНИИ СПЕЦИФИКАЦИЙ (АНДРЕЙ)
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_REQUISITES_TBL."` WHERE `id` = '".(int)$requisit_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);     
		return $result->fetch_assoc();
	}
	static function fetch_client_requisites_nikename($id){
	    // Я ИСПОЛЬЗУЮ ПРИ ОТБРАЖЕНИИ СПЕЦИФИКАЦИЙ (АНДРЕЙ)
		global $mysqli;
		$query = "SELECT company FROM `".CLIENT_REQUISITES_TBL."` WHERE `id` = '".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error); 
		$row = $result->fetch_assoc();    
		return $row['company'];
	}
	static function get_cont_face_details($id){
		global $mysqli;
		$query = "SELECT position, name, last_name, surname FROM `".CLIENT_CONT_FACES_TBL."` WHERE id ='".$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
		    return $result->fetch_assoc();
		}
		else return false;
	}
	static function cont_faces($id){
		global $mysqli;
		$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			//получаем телефоны, email, vk и т.д.	
			/*$arr = self::get_contact_info("CLIENT_CONT_FACES_TBL",$id);
			
			$array['phone'] = (isset($arr['phone']))?$arr['phone']:''; 
			$array['other'] = (isset($arr['other']))?$arr['other']:'';*/
			return $array;
		}
		
		return $array;
	}
	
	static function cont_faces_list($id){
	    global $mysqli;
		$query = "SELECT*FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".$id."' ORDER BY `id`";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
		   $counter = 0;
		   while($row = $result->fetch_assoc()){
		      if($row['set_main']!='') $items[0] = $row['name'];
			  else $items[++$counter] = $row['name'];
			  
		   }
		   ksort($items);
		   $str = implode('{@}',$items);
		}
		else  $str = 'не контактов{@}';
		return $str;
	}
    static function cont_faces_data_for_mail($id){
		global $mysqli;
		// Я ИСПОЛЬЗУЮ ПРИ ОТПРАВКЕ КП (АНДРЕЙ)
		$array = array();
		
		$query = "SELECT  tbl_2.contact email FROM `".CONT_FACES_CONTACT_INFO_TBL."` tbl_2 
						  WHERE tbl_2.parent_id = '".(int)$id."' AND tbl_2.type='email'";
									  
		$result = $mysqli->query($query) or die($mysqli->error);
		
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
			    $row['position'] =  $row['surname'] =  '';
				$row['name'] =  'Корпоративная';
				$row['last_name'] =  'почта';
				$array[] = $row;
			}
		}
		/**/
		
		$query = "SELECT tbl_1.position position, tbl_1.name name, tbl_1.last_name last_name, tbl_1.surname surname, tbl_2.contact email FROM `".CLIENT_CONT_FACES_TBL."` tbl_1
						  LEFT JOIN `".CONT_FACES_CONTACT_INFO_TBL."` tbl_2 
						  ON  tbl_1.id =  tbl_2.parent_id
						  WHERE tbl_1.client_id = '".(int)$id."' AND tbl_2.type='email'";
									  
		$result = $mysqli->query($query) or die($mysqli->error);

		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
		}
		return $array;
	}
	static  function get_cont_faces_ajax($client_id){
	    global $mysqli;
		
		$cont_faces_arr = array();
		
		$query = "SELECT*FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".$client_id."'";
		//echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0)
		{
		    while($item = $result->fetch_assoc()) $cont_faces_arr[] = $item['id'].'{;}'.$item['last_name'].' '.$item['name'].' '.$item['surname'];				
		}
		
	
		return implode('{@}',$cont_faces_arr);

	}
	static function relate_managers($id){
		global $mysqli;
		$query = "SELECT * FROM `".MANAGERS_TBL."` INNER JOIN `".RELATE_CLIENT_MANAGER_TBL."` ON `".RELATE_CLIENT_MANAGER_TBL."`.`manager_id` = `".MANAGERS_TBL."`.`id` WHERE `".RELATE_CLIENT_MANAGER_TBL."`.`client_id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$array[] = $row;
			}
			return $array;
		}
		return $array;
	}	
	static function get_relate_managers($client_id){
		global $mysqli;
		//$query = "SELECT * FROM  `".MANAGERS_TBL."` WHERE `id` IN (SELECT `manager_id` FROM  `".RELATE_CLIENT_MANAGER_TBL."`  WHERE `client_id` IN (SELECT `id` FROM `".CLIENTS_TBL."` WHERE `id` = ".$client_id." ));";
		$query = "SELECT 
			    `".RELATE_CLIENT_MANAGER_TBL."`.`cont_faces_relation_id`,
			    `".RELATE_CLIENT_MANAGER_TBL."`.`id` AS `relate_id`,
			    `".MANAGERS_TBL."`.*
			FROM
			    `".RELATE_CLIENT_MANAGER_TBL."`
			    INNER JOIN `".MANAGERS_TBL."`
			    ON `".MANAGERS_TBL."`.`id` = `".RELATE_CLIENT_MANAGER_TBL."`.`manager_id`
			    WHERE `".RELATE_CLIENT_MANAGER_TBL."`.`client_id` = '".$client_id."' 
    		";
		$result = $mysqli->query($query) or die($mysqli->error);
		$manager_names = array();			
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$manager_names[$row['id']] = $row;
			}
		}
		return $manager_names;
	}

	static function get_relate_managers_2($client_id){
		global $mysqli;
		//$query = "SELECT * FROM  `".MANAGERS_TBL."` WHERE `id` IN (SELECT `manager_id` FROM  `".RELATE_CLIENT_MANAGER_TBL."`  WHERE `client_id` IN (SELECT `id` FROM `".CLIENTS_TBL."` WHERE `id` = ".$client_id." ));";
		$query = "SELECT 
			    `".RELATE_CLIENT_MANAGER_TBL."`.`cont_faces_relation_id`,
			    `".RELATE_CLIENT_MANAGER_TBL."`.`id` AS `relate_id`,
			    `".MANAGERS_TBL."`.*
			FROM
			    `".RELATE_CLIENT_MANAGER_TBL."`
			    INNER JOIN `".MANAGERS_TBL."`
			    ON `".MANAGERS_TBL."`.`id` = `".RELATE_CLIENT_MANAGER_TBL."`.`manager_id`
			    WHERE `".RELATE_CLIENT_MANAGER_TBL."`.`client_id` = '".$client_id."' 
    		";
    	// echo $query;
		$result = $mysqli->query($query) or die($mysqli->error);
		$manager_names = array();			
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$manager_names[] = $row;
			}
		}
		return $manager_names;
	}


	static function get_ralate_manager_names($client_id){
		$men_names = array();
		$men_arr = self::get_relate_managers($client_id);
		foreach ($men_arr as $key => $value) {
			$men_names[] = $value['name'].' '.$value['last_name'];
		}
		return implode(',', $men_names);
	}

	// получаем массив пользователей с правами 5 и 1
	private function get_manager_list($access_arr = array(0 => 5)){
		$n = 0;
		$access_str = '';
		foreach ($access_arr as $key => $value) {
			$access_str = (($n>0)?',':'')."'".$value."'";
			$n++;
		}

		global $mysqli;
		$query = "SELECT * FROM  `".MANAGERS_TBL."` WHERE `access` IN (".$access_str.") ORDER BY `name` ASC;";
		$result = $mysqli->query($query) or die($mysqli->error);
		$manager_names = array();			
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$manager_names[] = $row; 
			}
		}
		return $manager_names;
	}

	static function get_manager_name($manager_id){
		global $mysqli;
		$query = "SELECT * FROM  `".MANAGERS_TBL."` WHERE `id` IN (".$manager_id.");";
		$result = $mysqli->query($query) or die($mysqli->error);
		$manager_names = "";			
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$manager_names = $row['name'].' '.$row['last_name'];
				if(trim($manager_names)==""){$manager_names=$row['nickname'];}
			}
		}
		return $manager_name;
	}
	static function history($user_id, $notice, $type, $client_id){
		global $mysqli;
		$query ="INSERT INTO `".LOG_CLIENT."` SET
		             `user_id` = '".$user_id."',
		             `client_id` = '".$client_id."',
					 `user_nick` = (SELECT `nickname` FROM `".MANAGERS_TBL."` WHERE `id` = '".$user_id."'),
					 `date` = CURRENT_TIMESTAMP,
					 `type` = '".$type."',
					 `notice` = '".$notice."'";
		// echo $query;
		$result = $mysqli->multi_query($query) or die($mysqli->error);	
		return 1;
	}
	# запись в лог
	static function history_edit_type($client_id, $user_id, $text ,$type,$tbl,$post,$id_row){
		global $mysqli;

		$query = "SELECT * FROM " . constant($tbl) . " WHERE `id` = '" . $_POST['id'] . "'";
        $i=0;
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        // пишем в лог предыдущие данные
        foreach ($arr_adres as $key => $value){
            if(isset($post[$key]) && trim($value)!=trim($post[$key])){
                if($i>0 && count($arr_adres)!=($i-1)){
                    $text.=",";
                }
                $i++;
                $text .= "поле ".$key ." изменено с ". $value." на ".$_POST[$key];
            }
        }
        if($i>0){
            self::history($user_id, $text ,$type,$client_id);
        }
        return 1;

	}
	static function history_delete_type($client_id, $user_id, $text ,$type,$tbl,$post,$id_row){
		global $mysqli;
		$query = "SELECT * FROM " . constant($tbl) . " WHERE `id`= '" . $id_row . "'";
        $i=0;
        $result = $mysqli->query($query) or die($mysqli->error);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arr_adres = $row;
            }
        }
        // пишем в лог предыдущие данные
        foreach ($arr_adres as $key => $value) {
            if($i!=0 || count($arr_adres)!=($i-1)){
                $text.=",";
            }
            $text .= "поле ".$key ." = ". $value;
            
        }
        self::history($user_id, $text ,$type, $client_id);
		return 1;
	}

	static function get_client_name($id){
		global $mysqli;		
		$name = "";
		//получаем данные из основной таблицы
		$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				$name = $row['company'];
			}
		}
		return $name;
	}
	static function get_whatever_client_name($id){
	    // когда нужно имя компании и желательно чтобы оно было полным, но если нет полного тогда хотя бы краткое
		global $mysqli;		

		
		$query = "SELECT * FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();
		    $name = (trim($row['comp_full_name'])!='')? $row['comp_full_name']:$row['company'];
		}
		return $name;
	}

	

	static function remove_curator($client_id,$manager_id){
		global $mysqli;
		// открепить менеджера от клиента
		$query ="DELETE FROM  `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$client_id."' AND `manager_id` = '".(int)$manager_id."';";	
		$result = $mysqli->multi_query($query) or die($mysqli->error);	
		return 1;
	}

	static function delete($id){
		/*
		global $mysqli;
		//выполняем все запросы ипишем ОК
						
		//лица имеющих право подписи
		$query = "DELETE FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."` WHERE `requisites_id` IN (SELECT id FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."');\n";	
		//контактные лица компании
		$query .= "DELETE FROM `".CLIENT_CONT_FACES_TBL."` WHERE `client_id` = '".(int)$id."';\n";	
		//прикреплённые менеджеры		
		$query .= "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//реквизиты относящиеся к данному клиенту
		$query .= "DELETE FROM `".CLIENT_REQUISITES_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//договора
		$query .= "DELETE FROM `".GENERATED_AGREEMENTS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//спецификации
		$query .= "DELETE FROM `".GENERATED_SPECIFICATIONS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//таблицы РТ
		$query .= "DELETE FROM `".CALCULATE_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//таблица заказов
		$query .= "DELETE FROM `".CLIENT_ORDERS_TBL."` WHERE `client_id` = '".(int)$id."';\n";
		//КП
		$query .= "DELETE FROM `".COM_PRED_LIST."` WHERE `client_id` = '".(int)$id."';\n";
		//протокол добавления
		$query .= "DELETE FROM `".CALCULATE_TBL_PROTOCOL."` WHERE `client` = '".(int)$id."';\n";			
		//планы
		$query .= "DELETE FROM `".PLANNER."` WHERE `id` = '".(int)$id."';\n";
		//удаляем клинта из основной таблицы		
		$query .= "DELETE FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."';\n";	
		
		//прикреплённые менеджеры		
		$query .= "DELETE FROM `".RELATE_CLIENT_MANAGER_TBL."` WHERE `client_id` = '".(int)$id."';";
		//return $query;
		$result = $mysqli->multi_query($query) or die($mysqli->error);
		return 1;	
		*/	
		return 1;
	}	



	public function create($array_in){
		if(empty($array_in))return "не достаточно данных";
		global $mysqli;
		global $mailClass;
		global $user_id;
		//return $user_id;	
		extract($array_in);
		$rate =(empty($rate))? 1 : $rate ;		
		// $query ="INSERT INTO `".CLIENTS_TBL."` SET
		// 	`set_client_date` = CURRENT_DATE(),
		//     `company` = '".$this->cor_data_for_SQL($company)."',
		//     `delivery_address` = '".$this->cor_data_for_SQL($delivery_address)."',
		// 	`email` = '".$this->cor_data_for_SQL($email)."', 
		// 	`phone` = '".$this->cor_data_for_SQL($phone)."',
		// 	`addres` = '".$this->cor_data_for_SQL($addres)."', 
		// 	`web_site` = '".$this->cor_data_for_SQL($web_site)."',
		// 	`dop_info` = '".$this->cor_data_for_SQL($dop_info)."', 
		// 	`rate` = '".$rate."'";
		$query ="INSERT INTO `".CLIENTS_TBL."` SET
			`set_client_date` = CURRENT_DATE(),
		    `company` = '".$this->cor_data_for_SQL($company)."',
			`dop_info` = '".$this->cor_data_for_SQL($dop_info)."'";
		 
	    $result = $mysqli->query($query) or die($mysqli->error);
		$client_id = $mysqli->insert_id;
		
		///////////////////////////////////////////////////
		// add new data in CLIENT_CONT_FACES_TBL
		///////////////////////////////////////////////////
		if(isset($_POST['cont_faces_data'])){
			foreach($_POST['cont_faces_data'] as $cont_face){
				   $query = "INSERT INTO `".CLIENT_CONT_FACES_TBL."` 
			              SET 
						 `client_id` = '".$client_id."',
						 `set_main` = '".@$this->cor_data_for_SQL($cont_face['set_main'])."', 
						 `name` = '".$this->cor_data_for_SQL($cont_face['name'])."',
						 `position` = '".$this->cor_data_for_SQL($cont_face['position'])."',
						 `department` = '".$this->cor_data_for_SQL($cont_face['department'])."',
						 `email` = '".$this->cor_data_for_SQL($cont_face['email'])."',
						 `phone` = '".$this->cor_data_for_SQL($cont_face['phone'])."',
						 `isq_skype` = '".$this->cor_data_for_SQL($cont_face['isq_skype'])."'";
						 
			   $result = $mysqli->query($query) or die($mysqli->error);		   
			}
		}
	
	    $query = "INSERT INTO `".RELATE_CLIENT_MANAGER_TBL."` VALUES('','$client_id','$user_id')";
        $result = $mysqli->query($query) or die($mysqli->error);
		//$headers = 'Cc : andrey@apelburg.ru';
		//$headers = 'Cc : '.implode(',',array('runman@mail.ru','slava@apelburg.ru'));
		$headers = ''; 
		$manager_info = $this->get_manager_info_by_id($user_id);		
		$message = 'В базу добавлен новый клиент:<br><b>'.$company.'</b><br>
				   поставщика добавил пользователь: '.$manager_info['nickname'].' / '.$manager_info['name'].' '.$manager_info['last_name'].'<br><br>
				   <a href="http://apelburg.ru/os/?page=clients&section=client_folder&subsection=client_card_table&client_id='.$client_id.'&razdel=show_client_data">ссылка на карточку клиента</a>';
		/**/
		//$mail->sendMail(2,'apelburg.m7@gmail.com','Новый клиент',$message,$headers);		
		$mailClass->send('kapitonoval2012@gmail.com','os@apelburg.ru','Новый клиент',$message);		
		return $client_id;
	}		
	private function cor_data_for_SQL($data){
	    if(is_int($data) || is_double($data)) return($data);
	    //return strtr($data,"1","2");
		$data = strip_tags($data,'<b><br><a>');
		return mysql_real_escape_string($data);
	}
	private function get_manager_info_by_id($manager_id){
		global $mysqli;
		$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE `id` = '".$manager_id."'";
		$result = $mysqli->query($query) or die($mysqli->error);
		$array = array();
		if($result->num_rows > 0){
			while($row = $result->fetch_assoc()){
				return $row;
			}
			//return $array;
		}
		return $array;
	}
	static function requisites_acting_manegement_face_details($requisite_id){
		global $mysqli;

					
		$query ="SELECT * FROM `".CLIENT_REQUISITES_MANAGMENT_FACES_TBL."`
					WHERE requisites_id = '".$requisite_id."' AND acting = '1'";
					

		$result = $mysqli->query($query) or die($mysqli->error);
		if($result->num_rows > 0){
			while($item = $result->fetch_assoc()){
			
		    return array('position' => $item['position'],'position_in_padeg' => $item['position_in_padeg'],'name' => $item['name'],'name_in_padeg' => $item['name_in_padeg'],'basic_doc' => $item['basic_doc']);
		    }
		}
	
	}

	
}

?>