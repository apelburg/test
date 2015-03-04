<?php
    //$session_path = $_SERVER['DOCUMENT_ROOT'].'/modules/tmp/';
	// если под Windows
	//$session_path = strtr($session_path,"/","\\");  

    // names base tables
	define('BASE_TBL','base');
	define('IMAGES_TBL','new__base_images');
	define('BASE_DOP_PARAMS_TBL','new__base__dop_params');
	define('BASE_PRINT_MODE_TBL','new__base__print_mode');
	define('BASE_COLORS_TBL','new__base_colors');
	define('BASE_MATERIALS_TBL','new__base_material');
    define("CLIENTS_TBL","order_manager__client_list"); // таблица клиентов
	define("CLIENT_REQUISITES_TBL","order_manager__clients_requisites"); // таблица реквизитов клиентов
	define("CLIENT_REQUISITES_MANAGMENT_FACES_TBL","order_manager__clients_requisites_management");// таблица лиц (контрагентов) имеющих право подписи 
	define("CLIENT_CONT_FACES_TBL","order_manager__relate_client_cont_faces"); // таблица контактных лиц клиентов  
	define("CLIENT_CONT_FACES_CONTACT_INFO_TBL", 'order_manager__clients_contact_information'); // таблица контактной информации для контактных лиц клиентов и их компаний
	define("CLIENT_ADRES_TBL", 'order_manager__client_addres_tbl');//таблица адресов

	define("SUPPLIERS_TBL","order_manager__supplier_list"); // таблица поставщиков
	define("SUPPLIERS_ACTIVITIES_TBL","order_manager__suppliers_activities"); // таблица видов деятельности поставщиков	
	define("SUPPLIERS_CONT_FACES_TBL","order_manager__relate_supplier_cont_faces"); // таблица контактных лиц поставщиков
	define("SUPPLIERS_RATINGS_TBL","order_manager__suppliers_rating"); // таблица контактных лиц поставщиков	
	define("RELATE_SUPPLIERS_ACTIVITIES_TBL","order_manager__relate_supplier_and_activity"); // таблица соотношения клиентов и видов деятельности
	define("MANAGERS_TBL","order_manager__manager_list"); // таблица менеджеров
	define("MANAGERS_DOP_INFO_TBL","order_manager__manager_dop_info"); // таблица менеджеров c дополнительной информацией
	define("PERSONAL_MANAGERS_GROUPS_TBL","order_manager__personal_managers_groups"); // группы для страницы на сайте - "персональный менеджер"
	
	define("DEPARTMENTS_TBL","order_manager__departments_tbl"); // таблица отделов
	define("RELATE_CLIENT_MANAGER_TBL","order_manager__relate_client_manager_tbl"); // таблица соотношения клиентов и менеджеров
	define("RELATE_ORDER_MANAGER_CLIENT_TBL","order_manager__relate_order_manager_client_tbl"); // таблица соотношения заказов менеджеров и клиентов 
	//define("RELATE_MANAGERS_BY_DEPARTMENTS_TBL","order_manager__relate_managers_by_departments_tbl"); // таблица отношения менеджеров к отделам
	define("CLIENT_ORDERS_TBL","orders"); // таблица заказов
	define("CLIENT_ORDERS_TABLE_PART_TBL","orders_table_part"); // табличная часть заказов
	define("CALCULATE_TBL","order_manager__orders_calculate_table"); // расчетная таблица
	define("CALCULATE_TBL_PROTOCOL","order_manager__orders_calculate_insert_delete_protocol"); // протокол добавления, удаления строк из РТ 
	define("COM_PRED_LIST","order_manager__com_pred_list"); // КП
	define("COM_PRED_LIST_OLD","order_manager__com_pred_list_old"); // КП
	define("COM_PRED_ROWS","order_manager__com_pred_rows"); // ряды КП
	define("LAST_COM_PRED_NUM","order_manager__last_com_pred_num"); // последний номер КП
	define("INVOICES_TBL","order_manager__invoices_for_pay"); // таблица счетов
	define("INVOICES_TBL2","order_manager__invoices_for_pay2"); // таблица счетов
	
	define("OUR_FIRMS_TBL","order_manager__our_firms"); //
	define("OUR_AGREEMENTS_TBL","order_manager__agreements"); // таблица типов договоров
	define("GENERATED_AGREEMENTS_TBL","order_manager__generated_agreements"); // таблица созданных договоров
	define("GENERATED_SPECIFICATIONS_TBL","order_manager__generated_specifications"); // таблица созданных договоров
	define("PLANNER","order_manager__planner"); // 
	


	
	$client_id = (isset($_GET['client_id']))? $_GET['client_id'] : false ;
	
	$suppliers_data_by_prefix = array( 15 => array('name'=>'интерпрезент','link'=>'http://www.happygifts.ru/catalog_new/search/?q='),
	                                   26 => array('name'=>'оазис','link'=>'http://krug-office.ru/artinfo.php?art='),
							           37 => array('name'=>'проект','link'=>'http://www.gifts.ru/search?text='),
									   59 => array('name'=>'макрос','link'=>'http://cabinet.makroseuro.ru/catalogue/search/?keyword='),
									  'e_'=> array('name'=>'ебазар','link'=>'http://ebazaar.ru/search/index.php?q=')
									   );
	//
	$print_mode_names = array( 'tampoo' => 'тампопечать',
	                           'textil_shelk' => 'шелкография',
							   'tisnenie' => 'тиснение',
							   'other' => 'другое',
							   'tampoo_ra1' => 'тампопечать РА 1',
							   'textil_shelk_ra1' => 'шелкография РА 1',
							   'tisnenie_ra1' => 'тиснение РА 1');
							   
	
	$month_day_name_arr = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
	
	$num_word_transfer_arr = array(
	              array('','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
	              array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
	   	          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
	              array('','одна','две','три','четыре','пять','шесть','семь','восемь','девять'),
	              array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
	   	          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
				  array('','один','два','три','четыре','пять','шесть','семь','восемь','девять'),
	              array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
	   	          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот'),
	              array('','одна','две','три','четыре','пять','шесть','семь','восемь','девять'),
				  array('','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят','восемьдесят','девяносто'),
		          array('','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот','девятьсот')
				  );
	$num_word_transfer_razriad_arr = array('','','','тысяч','','','миллионов','','','миллиардов');
    	
	$desjatichn_word_transfer_arr = array('десять  один'=>'одиннадцать',
	                                       'десять  два'=>'двенадцать',
										   'десять  три'=>'тринадцать',
										   'десять  четыре'=>'четырнадцать',
										   'десять  пять'=>'пятнадцать',
										   'десять  шесть'=>'шестнадцать',
										   'десять  семь'=>'семьнадцать',
										   'десять  восемь'=>'восемьнадцать',
										   'десять  девять'=>'девятьнадцать');
    $change_word_ending_arr_I = array('один  рублей'=>'один  рубль',
	                                       'два  рублей'=>'два  рубля',
										   'три  рублей'=>'три  рубля',
										   'четыре  рублей'=>'четыре  рубля');
    $change_word_ending_arr_II = array('один  тысяч'=>'одна  тысяча',
	                                       'два  тысяч'=>'две  тысячи',
										   'три  тысяч'=>'три  тысячи',
										   'четыре  тысяч'=>'четыре  тысячи');
	
	$change_word_ending_arr_III = array('один миллионов'=>'один  миллион',
	                                       'два миллионов'=>'два  миллиона',
										   'три миллионов'=>'три  миллиона',
										   'четыре миллионов'=>'четыре  миллиона');
   $change_word_ending_arr_IV = array('один миллиардов'=>'один  миллиард',
	                                       'два миллиардов'=>'два  миллиарда',
										   'три миллиардов'=>'три  миллиарда',
										   'четыре миллиардов'=>'четыре  миллиарда');
									
	//print_r($m_arr);
	include_once('mysql.php');
	if(isset($_GET['page']) && $_GET['page']=="samples"){
	$img_catalog = "../img/";
	$query = "SELECT * FROM `base_images`";
        $result = mysql_query($query,$db);
        if(!$result)exit(mysql_error());
        if(mysql_num_rows($result) > 0){
        		while($item = mysql_fetch_assoc($result)){
        			$img_arr[$item['art']][$item['size']] = $item['name'];  
                }
        }
}