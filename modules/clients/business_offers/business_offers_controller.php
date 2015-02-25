<?php

    
	// Собираем ряды для таблицы коммерческих предложений
	$rows = Com_pred::create_list($client_id);
	// Подключаем шаблон таблицы списка коммерческих предложений
	include ('skins/tpl/clients/client_folder/business_offers/table.tpl');
		
	
?>

