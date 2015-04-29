<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['cabinet']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
    /////////////////////////////////// AJAX //////////////////////////////////////
	
	 
	/////////////////////////////////// AJAX //////////////////////////////////////
	$menu_name_arr = array(
		'important' => 'Важно',
		'no_worcked' => 'Не обработанные',
		'in_work' => 'В работе',
		'send_to_snab' => 'Отправлены в СНАБ',
		'calk_snab' => 'Рассчитанные СНАБ',
		'ready_KP' => 'Выставлено КП',
		'denied' => 'Отказанные',
		'all' => 'Все',
		'orders' => 'Заказы',
		'requests' =>'Запросы',
		'create_spec' => 'Спецификация создана',
		'signed' => 'Спецификация подписана',
		'expense' => 'Счёт выставлен',
		'paperwork' => 'В оформлении',
		'start' => 'Запуск',
		'purchase' => 'Закупка',
		'design' => 'Дизайн',
		'production' => 'Производство',
		'ready_for_shipment' => 'Готов к отгрузке',
		'paused' => 'Приостановлен',
		'simples' => 'Образцы',
		'closed'=>'Закрытые',
		'for_shipping' => 'На отгрузку',
		'order_of_documents' => 'Заказ документов',
		'arrange_delivery' => 'Оформить доставку',
		'delivery' => 'Доставка',
		'pclosing_documents' => 'Закрывающие документы'
													
		); 


	$STATUS_LIST = array(
		'1'=> array(), // админ
		'2'=> array(
			'счёт выставлен',
			'ждём оплаты',
			'оплачен',//дата в таблицу
			'частично оплачен',//дата в таблицу			
			'приходник на залог',
			'аннулирован',			
			'приходник на залог',
			'возврат залога клиенту',
			'возврат денег по счёту',
			'огрузочные приняты (подписанные)'
			), // бух
		'4'=> array(
			'распаковка',
			'распаковано',
			'трансферы опечатаны',
			'в печати',
			'напечатано __%',
			'упаковка',
			'готово производство'
		), // пр-во
		'5'=> array(
			'work' => array(
				'дизайн утвержден',
				'согласование МАКЕТА с клиентом',
				'согласование ДИЗАЙНА с клиентом'
				),
			'pause' => array(
				),
			'service' => array(
				)
			
			), //мен
		'6'=> array(), // водитель
		'7'=> array(
			'work' => array(
				'принят на склад',
				'принят на пр-во',
				'отгружено'	
				),
			'pause' => array(
				),
			'service' => array(
				)
			), // склад
		'8'=> array(
			'work' => array(	// выкуп продукции есть следствие
				'ожидает счет от поставщика',
				'Продукция выкуплена',
				'Ожидаем отправку постащика',
				'Продукция ожидается :  00,00,00'
				),
			'pause' => array(
				),
			'service' => array(
				)
			), // снаб
		'9'=> array(
			'work' => array(			
				'дизайн готов',// НУЖНО СОГЛАСОВАНИЕ
				'макет готов', // НУЖНО СОГЛАСОВАНИЕ
				'верстка готова', 
				'ожидает обработки',
				'задача принята ожидает',
				'Пленки отправлены',
				'в работе',
				'Клише заказано 00,00,00',
				'задача принята ожидает',
				'ожидает соглосования',
				),
			'pause' => array(
				'задача не принята',
				),
			'service' => array(
				'очередь № __',
				'новая правка на дизайн',
				)
			
			// подразумевается, что очередь выставляется автоматичеки 
			//(очередь 1, очередь 2, очередь 3 .. и т.д.)
			) // диз
	);

	include'./skins/tpl/common/quick_bar.tpl';

	// обрабатываем массив разрешённых разделов
	$menu_left = "";
	foreach ($ACCESS['cabinet']['section'] as $key => $value) {
		if($value['access']){
			$menu_left .= '<li '.((isset($_GET["section"]) && $_GET["section"]==$key)?'class="selected"':'').'><a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet&section='.$key.'&subsection='.key($value['subsection']).'">'.$menu_name_arr[$key].'</a><li>';
		}
	}
	
	// определяем controller для подраздела
	$section = isset($_GET["section"])?$_GET["section"]:'default';

	// инициируем центральное меню
	$menu_central = "";
	$menu_central_arr = (array_key_exists($section, $ACCESS['cabinet']['section']))?$ACCESS['cabinet']['section'][$section]['subsection']:array();


	ob_start();
	echo "<pre>";
	print_r($menu_central_arr);
	echo "</pre>";
	foreach ($menu_central_arr as $key2 => $value2) {
		// $menu_central .= "$key2 -";
		$menu_central .= '<li '.((isset($_GET["subsection"]) && $_GET["subsection"]==$key2)?'class="selected"':'').'><a href="http://'.$_SERVER['HTTP_HOST'].'/os/?page=cabinet'.((isset($_GET["section"]))?'&section='.$_GET["section"]:'').'&subsection='.$key2.'">'.$menu_name_arr[$key2].'</a><li>';
	}
	// подгружаем контроллер подраздела
	switch ($section) {
		case 'important':
			include 'important_controller.php';
			break;

		case 'requests':
			include 'requests_controller.php';
			break;

		case 'paperwork':
			include 'paperwork_controller.php';
			break;

		case 'orders':
			include 'orders_controller.php';
			break;

		case 'for_shipping':
			include 'for_shipping_controller.php';
			break;

		case 'closed':
			include 'closed_controller.php';
			break;

		case 'simples':
			include 'simples_controller.php';
			break;
		
		default:
			include 'default_controller.php';
			break;
	}
		

	$content = ob_get_contents();

	ob_get_clean();

	include'./skins/tpl/cabinet/show.tpl';
	
	unset($content);
?>