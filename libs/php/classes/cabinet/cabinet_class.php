<?php
    class Cabinet{
    	// услуга с которой работает система в данный момент времени, содержит массив строки из 
    	protected $Service; // array();

    	// содержит массив всех прикреплённых к позиции услуг
    	protected $Services_for_position_arr; // array();

    	// содержит массив всех прикреплённых к позиции услуг отсортирована по подразделениям
    	protected $Services_for_position_arr_sort_by_performer; // array();


    	// содержит массив всех существующих услуг содержащихся в OUR_USLUGI_LIST (все предоставляемые услуги)
    	protected $Services_list_arr; // array();

    	// исполнитель услуг по правам
    	protected $performer = array(
    		'0' => 'noname', // ответственный не указан
			'1' => 'Админ',
			'2' => 'Бухгалтерия',
			'4' => 'Производство',
			'5' => 'Менеджер',
			'6' => 'Доставка',
			'7' => 'Склад',
			'8' => 'Снабжение',
			'9' => 'Дизайнер' 
		);


		protected $filter_order = ' '; // status_global и status_buch
		protected $filter_position = ' '; // status_sklad и status_snab
		protected $filter_uslugi = ' ';// uslugi_id и performer_status и performer


    	////////////////////////////////////////////////////////////////////////////////////
    	// -- START -- СТАТУСЫ ПОДРАЗДЕЛЕНИЙ ПО ПОЗИЦИЯМ, ЗАКАЗУ И ПРЕДЗАКАЗУ -- START -- //
    	////////////////////////////////////////////////////////////////////////////////////
	    	// допуски пользователя
	    	protected $user_access = 0;

	    	// статусы запроса
			protected $name_cirillic_status = array(
				'new_query' => 'новый запрос',
				'not_process' => 'не обработан менеджером',
				'taken_into_operation' => 'взят в обработку',
				'in_work' => 'в работе',
				'history' => 'история'
			);

			// глобальные статусы ПРЕДЗАКАЗА(заказа)
	    	// содержится в базе в `os__cab_orders_list` в global_status
	    	protected $paperwork_status = array(
	    		// ПРЕДЗАКАЗ
				'being_prepared'=>'В оформлении',
				// 'request_expense'=>'Запрошен счёт',
				// 'requeried_expense'=>'Перевыставить счёт',
				'waiting_for_payment' => 'ждём оплаты', // сервисный
				'paused_paperwork'=>'Предзаказ приостановлен',		
				'cancelled_paperwork'=>'Предзаказ аннулирован'
				);

	    	// глобальные статусы ЗАКАЗА
	    	// содержится в базе в `os__cab_orders_list` в global_status
	    	protected $order_status = array(
				// ЗАКАЗ
				'in_operation'=>'Запуск в работу', // нельзя выбрать // вытекает из статусов BUCH
				'in_work'=>'В работе', // вытекает из кнопки Запуск в работу
				// 'ready_for_shipment'=>'Готов к отгрузке',
				'shipped'=>'Отгружен', // вытекает из статусов позиций
				
				);
	    	protected $order_service_status = array(
	    		'maket_without_payment' =>'Макет без оплаты',
	    		'paused'=>'Заказ приостановлен',	
				'cancelled'=>'Заказ аннулирован'
	    		);

			// статусы БУХ - вывод в select
	    	protected $buch_status = array(
	    		'is_pending' => 'предзаказ ожидает обработки', //-> статус предзаказа = being_prepared (в обработке)
	    		'score_exhibited' => 'счёт выставлен ', //-> статус предзаказа = waiting_for_payment (ожидает оплаты)
				'payment' => 'оплачен',//дата в таблицу // -> перевод в заказ
				'partially_paid' => 'частично оплачен',//дата в таблицу	// -> перевод в заказ	
				'collateral_received' => 'залог принят', //  -> перевод в заказ = in_operation
				'bail_refunded' => 'залог возвращен', 
				'prihodnik_on_bail_ofr_samples' => 'залог за образцы ???', 
				'letter_of_guarantee' => 'гарантийное письмо', // -> перевод в заказ = in_operation
				// 'cancelled'=>'Аннулирован',	//-> статус предзаказа =  cancelled_paperwork
				'returns_client_collateral' => 'возврат залога клиенту', 
				'refund_in_a_row' => 'возврат денег по счёту', 
				'ogruzochnye_accepted' => 'огрузочные приняты (подписанные) ВСЕ', // -> статус предзаказа =  'shipped'
				'return_order_in_paperorder' => 'вернуть заказ в предзаказ' 	    		
	    	);
			// статусы БУХ - сервисные (если уже не выставлены) 
	    	protected $buch_status_service = array(
	    		'request_expense'=>'Запрошен счёт',
	    		'reget_the_bill' => 'перевыставить счёт', 
				'get_the_dop_bill' => 'запрошен доп. счёт'
	    	);
			
			// комманды менеджера  (при клике на статус буха меню)
			protected $commands_men_for_buch = array(
				'reget_the_bill' => 'перевыставить счёт', 
				'get_the_dop_bill' => 'запросить доп. счёт'
			);

			

	    	// типы счетов которые мы можем запросить
	    	protected $type_the_bill =array(
	    		'the_bill' => array(
	    			'счёт',// кто, что
	    			'счёта' // кого, чего
	    			),
	    		'the_bill_offer' => array(
	    			'счёт - оферта',// кто, что
	    			'счёта - оферты' // кого, чего
	    			),
	    		'the_bill_for_simples' => array(
	    			'счёт на образцы',
	    			'счёта на образцы'
	    			),
	    		'prihodnik' => array(
	    			'приходник',
	    			'приходника
	    			'),
	    		);


	    	
			// статусы склад
			protected $statuslist_sklad = array(
				'no_goods' => 'нет в наличии', 
				// 'waiting' => 'ожидаем',
				'goods_in_stock' => 'принято на склад', // ->
				'sended_on_outsource' => 'отправлено на аутсорсинг',
				'ready_for_shipment'  => 'готов к отгрузке',
				'goods_shipped_for_client' => 'отгружен клиенту'
			);
				
			// статусы снабжение
			protected $statuslist_snab = array(
				'in_operation' => 'Ожидает запуска', // не выводим в общий выбор --- статус на уровне кнопки
				'adopted' => 'Принят',
				'maquette_adopted' => 'Макет принят',
				'not_adopted' => 'Не принят',
				'maquette_maket' => 'Ожидает макет',
				'waits_union' => 'Ожидает объединения',
				// 'products_capitalized_warehouse' => 'Продукция оприходована складом',// сервисный статус, вытекает из статуса склада - принято на склад
				'waits_union' => 'Ожидает счет от поставщика',
				// 'on_outsource' => 'уехало на аутсорсинг',
				'waits_the_bill_of_supplier' => 'Ожидаем отправку постащика',
				'products_bought' => 'выкуплено',
				'waits_products' => 'Продукция ожидается:',
				'in_production' => 'В Производстве', // -> запуск всех услуг кроме доставки и дизайна, при этом услуга Диза ставится на "услуга выполнена"
				// 'ready_for_shipment' => 'Готов к отгрузке',	
				// 'goods_shipped_for_client' => 'отгружен клиенту',			
				'question' => 'Вопрос'
			);

			// статусы плёнок для услуги
			protected $status_film_photos = array(
				// админ
				1 => array(
					'проверить наличие', // диз не видит
					'нужно делать',
					'в наличии',
					'не требуются', // диз не видит
					'готовы к отправке',
					'отправлены',
					'получены', // диз не видит
					'клише клиента'
					),
				// снабжениец
				// 8 => array(),
				// менеджер
				5 => array(
					'проверить наличие', // диз не видит
					'в наличии',
					'клише клиента',
					'нужно делать',
					'не требуются' // диз не видит
					),
				// дизайн
				9 => array(
					'готовы к отправке',
					'отправлены на фотовывод',
					'клише заказано'
					),
				// производство 
				4 => array(
					'перевывод',
					'в наличии', // диз не видит
					'получены' // // диз не видит
					)
			); 



			////////////////////////////////////////////////////////
			//  --- START ---  СЛЕДСТВИЯ СТАТУСОВ  --- START ---  //
			////////////////////////////////////////////////////////
				/*
					следствия нужны для переключения статусов других подразделений при изменении какого-либо статуса			
				*/

				// статус БУХ -> статус Заказа/Предзаказа
		    	protected $CONSEQUENCES_of_status_buch = array(
		    		'return_order_in_paperorder' => 'being_prepared', // вернуть заказ в предзаказ -> предзаказ ожидает обработки
		    		'score_exhibited' => 'waiting_for_payment', // счёт выставлен -> ждём оплаты
		    		'payment' => 'in_operation', // оплачен -> кнопка "Запуск в работу"
		    		'partially_paid' => 'in_operation', // чатично оплачен -> кнопка "Запуск в работу"
		    		'collateral_received' => 'in_operation', // принят залог -> кнопка "Запуск в работу"
		    		'letter_of_guarantee' => 'in_operation', // гарантийное -> кнопка "Запуск в работу"
		    		
		    		'ogruzochnye_accepted' => 'shipped'// отгрузочные приняты ???
		    	);

		    	// // статус склад -> статус снаб
		    	// protected $CONSEQUENCES_of_status_sklad = array(
		    	// 	'goods_in_stock' => 'goods_in_stock',// принято на склад -> продукция оприходована складом
		    	// 	'sended_on_outsource' => 'goods_in_stock',// отправлено на оутсорс -> отправлено на оутсорсинг
		    	// 	'checked_and_packed' => 'goods_in_stock', // проверено и упаковано -> готово к отгрузке
		    	// 	'goods_shipped_for_client' => 'goods_shipped_for_client'// отгружен клиенту -> отгружен клиенту
		    	// );

	    	////////////////////////////////////////////////////////
			//   --- END ---   СЛЕДСТВИЯ СТАТУСОВ   --- END ---   //
			////////////////////////////////////////////////////////

	    ////////////////////////////////////////////////////////////////////////////////
    	// -- END -- СТАТУСЫ ПОДРАЗДЕЛЕНИЙ ПО ПОЗИЦИЯМ, ЗАКАЗУ И ПРЕДЗАКАЗУ -- END -- //
    	////////////////////////////////////////////////////////////////////////////////

		########   КОНСТРУКТОР   ########
    	function __consturct(){
		}


		
		/////////////////////////////////////////////////////////////////////////////////////
		//	-----  START  ----- 	ДЕКОДЕРЫ СТАТУСОВ ПОДРАЗДЕЛЕНИЙ 	-----  START  -----
		/////////////////////////////////////////////////////////////////////////////////////
			// вывод статусов плёнок
			protected function get_statuslist_film_photos($real_val,$cab_uslugi_id){
				$html = '';
				// флаг соответствия
				$conformity_true = 0;
				// если плёнки или клише не требуются то для всех кроме менеджеров и админов выводим неизменяемый статус
				if($real_val == 'не требуются' && ($this->user_access != 5 && $this->user_access != 1)){ return '<span class="greyText">'.$real_val.'</span>';}
				

				// проверяем предусмотрена ли для пользователя возможность выставлять статусы
				if(isset($this->status_film_photos[$this->user_access])){
					$html .= '<select class="statuslist_film_photos" data-id="'.$cab_uslugi_id.'">';
						$select_html = '';
						foreach ($this->status_film_photos[$this->user_access] as $key => $value) {
							if ($value == $real_val) {
								$is_checked = 'selected="selected"';
								$conformity_true = 1;
							}else{
								$is_checked = '';
							}
							
							$select_html .= '<option value=\''.$value.'\' '.$is_checked.'>'.$value.'</option>';
						}
					// если соответствий в разрешённых для пользователя статусах так и не было найдено, добавляем в список возможность выбора того, что стояло до того
					if(!$conformity_true){
						$html .= '<option value=\''.$real_val.'\' selected="selected">'.$real_val.'</option>';
					}
					$html .= $select_html;
					$html .= '</select>';
				}else{
					// узер без прав, просто транслируем тот статус который содержится в базе
					$html .= $real_val;
				}

				// если $html всё ещё пуст, значит прав на выставление статуса у юзера нет и поле пустое
				if(trim($html) == ""){$html="нет информации";}
				return $html;
			}

			// вывод статусов склада с возможностью их редактирования (опционально по флагу $enable_selection)
			protected function decoder_statuslist_sklad($real_val, $main_rows_id, $enable_selection = 0){
				/*
					$real_val - реальное значение поля в базе
					 
					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено


					в случае со складом статус по умолчанию имеется в массиве рабочих статусов и исключения для него писать не нужно
				*/

				$html = '';
				
				// проверяем на разрешение смены статуса снабжения
				if($this->user_access == 7 || $this->user_access == 1){ // на будущеее, пока работаем по параметру
				// if($enable_selection){
					$html .= '<select class="choose_statuslist_sklad" data-id="'.$main_rows_id.'">';
						foreach ($this->statuslist_sklad as $name_en => $name_ru) {
							$is_checked = ($name_en == $real_val)?'selected="selected"':'';
							$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';
						}
					$html .= '</select>';
				}else{
					$html .='<span class="greyText">'.(isset($this->statuslist_sklad[$real_val])?$this->statuslist_sklad[$real_val]:$real_val).'</span>';
					
				}
				// возвращаем
				return $html;
			}

			// вывод статусов снабжения с возможностью выбора статуса
			protected function decoder_statuslist_snab($real_val, $date_delivery_product = '', $enable_selection = 0,$main_rows_id){
				/*
					$real_val - реальное значение поля в базе

					$date_delivery_product - дата ожидаемой поставки продукции на склад
					заводится при выборе статуса продукция ожидается

					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено
				*/

				// если стоит статус "Вопрос" красная подсветка
				$red_bg_color = (trim($real_val) == "question")?' style="background-color:rgba(255, 0, 0, 0.4);"':'';

				$html = '';
				// проверяем на разрешение смены статуса снабжения
				if($this->user_access == 8 || $this->user_access == 1 || $enable_selection){ // на будущеее, пока работаем по параметру
					$html .= '<select '.$red_bg_color.' data-id="'.$main_rows_id.'" class="choose_statuslist_snab">';
						
						if($real_val == 'in_processed'){$html .= '<option value="in_processed" selected="selected">в обработке</option>';}
						// перебираем статусы склада (т.к. статусы склада транслируются в статусы снабжения)
						foreach ($this->statuslist_sklad as $name_en => $name_ru) {
							if ($name_en == $real_val) {
								$is_checked = 'selected="selected"';
								$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';	
							}
						}
						// перебираем статусы снабжения
						foreach ($this->statuslist_snab as $name_en => $name_ru) {
							$is_checked = ($name_en == $real_val)?'selected="selected"':'';
							$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';
						}


					$html .= '</select>';
					// добавляем div с iput для редактирования ожидаемой даты поставки
					$html .= '<div data-id="'.$main_rows_id.'" class="waits_products_div '.(($date_delivery_product!='' && $real_val == "waits_products")?'show':'').'"><input typwe="text" value="'.$date_delivery_product.'"></div>';
				
				}else{
					if($real_val == 'in_processed'){// если статус in_processed, то его не декодировать в кириллицу с помощью стандартного массива, поэтому пишем исключение
						$html .='<span class="greyText">в обработке</span>';
					}else{
						$decoder_status_name = $real_val;
						// если существует соответствие в статусах склад
						if(isset($this->statuslist_sklad[$real_val])){
							$decoder_status_name = $this->statuslist_sklad[$real_val];
						}
						// если существует соответствие в статусах снаб
						if(isset($this->statuslist_snab[$real_val])){
							$decoder_status_name = $this->statuslist_snab[$real_val];
						}
						// выводим трансляцию статуса снабжения
						$html .='<span '.$red_bg_color.' class="greyText">'.$decoder_status_name.'</span>';
					}
					// добавляем div c ожидаемой датой поставки
					$html .= '<div  data-id="'.$main_rows_id.'" class="waits_products_div '.(($date_delivery_product!='' && $real_val == "waits_products")?'show':'').'">'.$date_delivery_product.'</div>';
				}
				

				// возвращаем
				return $html;
			}

			// вывод статусов бухгалтерии с возможностью выбора статуса
			protected function decoder_statuslist_buch($real_val, $enable_selection = 0){
				/*
					$real_val - реальное значение поля в базе

					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено
				*/

				$html = '';
				// если стоит is_pending - ставим кнопку
				if($real_val == "is_pending" && $this->user_access!=2 && $this->user_access!=8){
					return '<input type="button" name="query_the_bill" class="query_the_bill" value="Запросить счёт">';
				}

				
				// проверяем на разрешение смены статуса снабжения
				if($this->user_access == 2 || $this->user_access == 1 || $enable_selection){ // на будущеее, пока работаем по параметру
				// if($enable_selection){
					$html .= '<select class="choose_statuslist_buch">';
						// перебираем статусы склада (т.к. статусы склада транслируются в статусы снабжения)
						foreach ($this->buch_status_service as $name_en => $name_ru) {
							if ($name_en == $real_val) {
								$is_checked = 'selected="selected"';
								$html .= '<option value=\''.$name_en.'\' '.$is_checked.'>'.$name_ru.'</option>';	
							}
						}
						foreach ($this->buch_status as $name_en => $name_ru) {
							$is_checked = ($name_en == $real_val)?'selected="selected"':'';
							$html .= '<option value="'.$name_en.'" '.$is_checked.'>'.$name_ru.'</option>';
						}
					$html .= '</select>';
				}else{
					if(isset($this->buch_status[$real_val])){
						$html .='<span class="greyText get_requeried_expense_menu">'.$this->buch_status[$real_val].'</span>';
					}else if(isset($this->buch_status_service[$real_val])){
						$html .='<span class="greyText get_requeried_expense_menu">'.$this->buch_status_service[$real_val].'</span>';
					}else{
						$html .='<span class="greyText get_requeried_expense_menu">'.$real_val.'</span>';
					}
					
					
				}
				// возвращаем
				return $html;
			}

			// вывод статусов заказа/предзаказа с возможностью выбора статуса
			protected function decoder_statuslist_order_and_paperwork($real_val, $enable_selection = 0){
				/*
					$real_val - реальное значение поля в базе

					$enable_selection - разрешение на вывод редактируемого списка, по умолчанию запрещено
				*/
				$html = '';
				// определяем рабочий массив статусов для работы (ЗАКАЗ или ПРЕДЗАКАЗ)
				if (array_key_exists($real_val, $this->paperwork_status)) { // ищем ключ
					$status_arr = $this->paperwork_status;
				}else if (array_key_exists($real_val, $this->order_status)){
					$status_arr = $this->order_status;
				}else{
					return $real_val.' (статус не известен)';// статус не известен
				}

				// проверяем на разрешение смены статуса
				//if($this->user_access == 2 || $this->user_access == 1 || $enable_selection || ($this->user_access == 5 && isset($this->paperwork_status[$real_val]) )){ 
				if($this->user_access == 1 || $enable_selection){ 
					if($real_val == 'in_operation' && $this->user_access == 1){
						$html = '<input type="button" name="'.$real_val.'" class="'.$real_val.'" value="'.$status_arr[$real_val].'">';
					}else{
						$html .= '<select class="choose_statuslist_order_and_paperwork">';
							foreach ($status_arr as $name_en => $name_ru) {
								$is_checked = ($name_en == $real_val)?'selected="selected"':'';
								$html .= '<option value="'.$name_en.'" '.$is_checked.'>'.$name_ru.'</option>';
							}
						$html .= '</select>';
					}
				}else{
					$html .='<span class="greyText">'.(isset($status_arr[$real_val])?$status_arr[$real_val]:$real_val).'</span>';
				}
				// возвращаем
				return $html;
			}

			// ВЫВОД ВСЕХ СТАТУСОВ ПО ПОЗИЦИЯМ
			protected function position_status_list_Html($cab_order_main_row){	
				
				// собираем массив для поиска
				$search_glob_status_arr = $this->paperwork_status; // закидываем туда статусы предзаказа
				$search_glob_status_arr['in_operation'] = 'Запуск в работу';// добавляем статус запуск в работу
				
				if(array_key_exists($this->Order['global_status'], $search_glob_status_arr)){
					 // если в созданном нами массиве попался текущий статус заказа - возвращаем html
					return '<td style="width: 78px;"><span class="greyText">Отделы<!-- // раньше было Подразделения, слишком длинно пришлось поменять --></span></td><td><span>Ожидают запуска заказа</span></td>';
				}

				// убиваем массив поиска
				unset($search_glob_status_arr);



				// собираем вывод
				$html = '<td colspan="2"  class="orders_status_td_tbl">';
				$html .= '<table>';


				// выодим статус снабжения
				$html .= '<tr>';
					$html .= '<td style="width: 78px;">';
					$html .= '<div class="otdel_name">Снабжение</div>';
					$html .= '</td>';
					$html .= '<td>';				
						$html .= '<div class="otdel_status">';
							// привеодим статус снабжения к необходимому виду		
							$html .= '<div class="performer_status">'.$this->decoder_statuslist_snab($cab_order_main_row['status_snab'],$cab_order_main_row['date_delivery_product'],0,$cab_order_main_row['id']).'</div>';				
						$html .= '</div>';									
					$html .= '</td>';
				$html .= '</tr>';

				//выводис статус склад
				$html .= '<tr>';
					$html .= '<td>';
					$html .= '<div class="otdel_name">Cклад</div>';
					$html .= '</td>';
					$html .= '<td>';				
						$html .= '<div class="otdel_status">';	
							// привеодим статус склада к необходимому виду			
							$html .= '<div class="performer_status">'.$this->decoder_statuslist_sklad($cab_order_main_row['status_sklad'], $cab_order_main_row['id']).'</div>';				
						$html .= '</div>';									
					$html .= '</td>';
				$html .= '</tr>';

				//$html .= '<tr><td colspan="2">'.$this->print_arr($this->Position_status_list).'</td></tr>';

				// выводим статусы услуг
				foreach ($this->Position_status_list as $performer => $performer_status_arr) {
					$html .= '<tr>';
					$html .= '<td>';

					$html .= '<div class="otdel_name">'.$performer.'</div>';
					$html .= '</td>';
					$html .= '<td>';

					foreach ($performer_status_arr as $key => $value) {
						$html .= '<div class="otdel_status" data-id="'.$value['id'].'">';
							$html .= '<div class="service_name">'.$value['service_name'].'</div>';
							$html .= '<div class="performer_status">'.$this->get_statuslist_uslugi_Dtabase_Html($value['id'],$value['performer_status'],$value['id_dop_uslugi_row'],$value['performer']).'</div>';
						$html .= '</div>';
										
					}

					$html .= '</td>';
					$html .= '</tr>';
				}						
				$html .= '</table>';
				$html .= '</td>';	
				// echo '<pre>';
				// print_r($this->Position_status_list);
				// echo '</pre>';
				return $html;
			}

			// выпадающий список статусов услуги
			protected function get_statuslist_uslugi_Dtabase_Html($id,$real_val,$cab_dop_usl_id, $performer){
				// $performer - подразделение (права доступа)

				// если стоит статус "Вопрос" красная подсветка
				$red_bg_color = (trim($real_val) == "Вопрос")?' style="background-color:rgba(255, 0, 0, 0.4);"':'';


				if(trim($real_val)!="" || $real_val == "in_processed"){// если есть статус - значит услуга запущена
					// проверяем права доступа на редактирование статуса
					if($this->user_access == $performer || $this->user_access==1){
						// получаем id по которым будем выбирать статусы для услуги
						$id_s = $this->get_id_parent_Database($id);
						global $mysqli;
						$html = '';
						$html .= '<select '.$red_bg_color.' class="get_statuslist_uslugi" data-id="'.$cab_dop_usl_id.'"><option value=""></option>';
						$query = "SELECT * FROM `".USLUGI_STATUS_LIST."` WHERE `parent_id` IN (".$id_s.")";
						//echo $query.'<br>';
						$result = $mysqli->query($query) or die($mysqli->error);
						if($result->num_rows > 0){			
							while($row = $result->fetch_assoc()){
								$is_checked = ($real_val==$row['name'])?'selected="selected"':'';
								$html.= '<option value="'.$row['name'].'" '.$is_checked.'><!--'.$row['id'].' '.$row['parent_id'].'--> '.$row['name'].'</option>';
							}
						
						}
						$html.= '</select>';	
					}else{
						$html = '<span  '.$red_bg_color.' class="greyText"  data-id="'.$cab_dop_usl_id.'">'.(($real_val=="in_processed")?'обрабатывается':$real_val).'</span>';
					}					
					return $html;
				
				}else{// если статус отсутствует - услуга ещё не запущена
					if($real_val=="in_processed"){
						if($this->user_access == $performer){
							$html = '<input type="button" value="Взять в работу" class="start_statuslist_uslugi" data-id="'.$cab_dop_usl_id.'">';	
						}else{
							$html = 'ожидает обработки';
						}
						
					}else{
						// все услуги могут запускать только АДМИНЫ и СНАБЫ
						if($this->user_access == 1 || $this->user_access== 8){
							$html = '<input type="button" value="Запуск" class="start_statuslist_uslugi" data-id="'.$cab_dop_usl_id.'">';	
						}else{

							$html = 'ожидает запуска';
						}
					}
					
					
					return $html;
				}
			}

			
			//////////////////////////////////////////////////////////////////////////////////////////
			//  -- START --  получаем id по которым будем выбирать статусы для услуги  -- START --  //
			//////////////////////////////////////////////////////////////////////////////////////////
				protected function get_id_parent_Database($id){
					global $mysqli;
					if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
						$this->Services_list_arr = $this->get_all_services_Database();
					}
					return $this->merge_arr_id_s($id);			 
				}
				// возвращает строку с id услуги и её родителей по id услуги
				protected function merge_arr_id_s($id){
					$arr = array();
					$arr[] = $id;
					$parent_id = $id;
					while ($parent_id!=0) {
						if(isset($this->Services_list_arr[$parent_id]['parent_id'])){
							$parent_id = $this->Services_list_arr[$parent_id]['parent_id'];						
						}else{
							$parent_id = 0;
						}
						
						if($parent_id!=0){
							$arr[] = $parent_id;
						}
					}
					$str = implode(',', $arr);
					return $str;
				}
				// запрос на все услуги
				protected function get_all_services_Database(){
					global $mysqli;
					$arr = array();
					$query = "SELECT *
					 FROM `".OUR_USLUGI_LIST."`";
					 // echo $query;	
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){	
						while($row = $result->fetch_assoc()){
							$arr[$row['id']] = $row;
						}
					}	
					return $arr;
				}

			//////////////////////////////////////////////////////////////////////////////////////
			//  -- END --  получаем id по которым будем выбирать статусы для услуги  -- END --  //
			//////////////////////////////////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////////
		//   -----  END  -----     ДЕКОДЕРЫ СТАТУСОВ ПОДРАЗДЕЛЕНИЙ 	    -----  END  -----
		///////////////////////////////////////////////////////////////////////////////////

		/////////////////////////////////////////////////////////////
		//	-----  START  -----  МЕТОДЫ AJAX  -----  START  -----  //
		/////////////////////////////////////////////////////////////
			########   вызов AJAX   ########
			protected function _AJAX_($name){
				$method_AJAX = $name.'_AJAX';
				// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
				if(method_exists($this, $method_AJAX)){
					$this->$method_AJAX();
					exit;
				}					
			}


			// создать строку пустого счёта
			protected function create_a_new_bill(){
				global $mysqli;
				$time = time();
				$date_for_base = date("Y-m-d",$time);
				$date_for_html = date("d.m.Y",$time);


				$query ="INSERT INTO `".CAB_BILL_TBL."` SET 
					`order_id` = '".$_POST['order_id']."',
					`type_the_bill` = '".$_POST['type_the_bill']."',
					`date_create` = '".$date_for_base."',
					`comments` = '".$_POST['comment_text']."'
				";

				$result = $mysqli->query($query) or die($mysqli->error);
				// запоминаем новый id


				$insert_id = $mysqli->insert_id;
				$html = '<tr class="container_from_the_bill">';
					$html .= '<td>';
						$html .= '<span class="change_order_type" data-id="'.$insert_id.'">№ '.$this->type_the_bill[$_POST['type_the_bill']][1].'</span>';
					$html .= '</td>';
					$html .= '<td>';		
						// поле редактирования номера
						$html .= '&nbsp;<input type="text" data-id="'.$insert_id.'" name="number" class="number_the_bill" value="">';
							
						$html .= '<span>';
						$html .= 'Дата: ';
						// дата создания счёта
							$html .= '<input type="text" data-id="'.$insert_id.'" name="date_create" class="date_create_the_bill" value="'.$date_for_html.'">';
						$html .= '</span>';
						$html .= '<span>';
							$html .= 'На сумму: ';
							// дата создания счёта
							$html .= '<input type="text" data-id="'.$insert_id.'" name="for_price" class="for_price_the_bill" value=""> р';
						$html .= '</span>';
					$html .= '</td>';
					$html .= '<td>';
						$html .= '<span class="buch_comments"  data-id="'.$insert_id.'"></span>';
					$html .= '</td>';
					$html .= '<td>';
						$html .= '<span class="button usl_del" data-id="'.$insert_id.'">X</span>';
					$html .= '</td>';
				$html .= '</tr>';
				return $html;
			}

			// получаем комментарии к счёту
			protected function get_the_comment_width_the_bill_AJAX(){
				global $mysqli;
				// типы счетов которые мы можем запросить
		    	/*
		    		$type_the_bill =array(
		    		'the_bill' => 'счёт',
		    		'the_bill_offer' => 'счёт - оферта',
		    		'the_bill_for_simples' => 'счёт на образцы',
		    		'prihodnik' => 'приходник',
		    		);
		    	*/

				$query = "SELECT *
				 FROM `".CAB_BILL_TBL."` WHERE `id` = '".$_POST['row_id']."'";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$the_bill = array();				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$the_bill = $row;
					}
				}				

				$html = '';
					$html .= '<form>';
					//////////////////////////////
					//	форма комментария для БУХ
					//////////////////////////////
					$html .= '<input type="hidden" value="save_the_comment_for_the_bill" name="AJAX">';
					$html .= '<input type="hidden" value="'.(int)$_POST['row_id'].'" name="row_id">';
					$html .= '<div class="comment table">';
						$html .= '<div class="row">';
							$html .= '<div class="cell comment_text">';
								// исключение для only read
								if(isset($_POST['onlyread']) && $_POST['onlyread'] ==  1){
									$html .= '<strong>Комментарий к счёту:</strong><br><br>';
									$html .= '<div class="onlyread">';
									$html .= $the_bill['comments'];
									$html .= '</div>';
								}else{
									$html .= '<textarea name="comment_text">'.$the_bill['comments'].'</textarea>';
								}									
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';
					$html .= '</form>';
					// исключение для only read
					if(isset($_POST['onlyread']) && $_POST['onlyread'] ==  1){
						echo '{"response":"show_new_window_simple", "html":"'.base64_encode($html).'","title":"Комментарии для Бухгалтерии:","width":"600"}';
					}else{
						echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Комментарии для Бухгалтерии:","width":"600"}';
					}
					

				//comments
			}



			// сохраняем комментарии для счёта 
			protected function save_the_comment_for_the_bill_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_TBL."`  SET  
					`comments` =  '".addslashes($_POST['comment_text'])."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// удаляем счёт (делаем не активным) 
			protected function delete_the_bill_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_TBL."`  SET  
					`deleted` =  '1' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// создаем пустой счёт
			protected function create_the_new_bill_AJAX(){
				// если мы имеем дело не с обычным счётом выводим окно с комментариями по заказанному документу(счёту)
				if(isset($_POST['type_the_bill']) && $_POST['type_the_bill'] != "the_bill" && !isset($_POST['comment_text'])){
					$html = '';
					$html .= '<form>';
					// перебираем остальные значения для передачи их далее
					foreach ($_POST as $key => $value) {
						$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}

					//////////////////////////////
					//	форма комментария для БУХ
					//////////////////////////////

					$html .= '<div class="comment table">';
						$html .= '<div class="row">';
							$html .= '<div class="cell comment_text">';
									$html .= '<textarea name="comment_text"></textarea>';
									$html .= '<div class="div_for_button">';
										$html .= '<button class="add_nah">Нах</button>';
										$html .= '<button class="add_nah">Нах?</button>';
										$html .= '<button class="add_nah">Без комментария</button>';
										// $html .= '<button id="add_new_comment_button">Отправить</button>';
									$html .= '</div>';
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';

					$html .= '</form>';
					echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Комментарии для Бухгалтерии:","width":"600"}';
				}else{

					if(isset($_POST['get_html_row_the_bill'])){ // если нам нужно вернуть  только строку
						echo '{"response":"OK","function":"add_new_bill_in_window","html":"'.base64_encode($this->create_a_new_bill()).'"}';	
						return;
					}
					// меняем статус бух и заказа
					$this->buch_status_select($_POST['status_buch'],$_POST['order_id']);
					// заводим счёт
					$this->create_a_new_bill();						
					// вывод окна бух учёта
					$this->get_window_buh_uchet_AJAX();
					// echo '{"response":"show_new_window_simple", "html":"'.base64_encode($this->get_window_buh_uchet()).'","title":"Бухгалтерский учёт:","width":"1100"}';				
				}
			}

			// смена даты подписи спецификации
			protected function change_date_specification_signed_AJAX(){
				// $html = '';
				// $html .= $this->print_arr($_POST);

				// $html .= date("Y-m-d",strtotime($_POST['date']));
				global $mysqli;

				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  
					`date_specification_signed` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['order_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';

				//echo '{"response":"show_new_window_simple", "html":"'.base64_encode($html).'","title":"Разработчику!!!"}';
			}

			// смена даты выставления счёта
			protected function change_date_create_the_bill_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_BILL_TBL."`  SET  
					`date_create` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['id_row']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// смена даты возврата подписанной спецификации
			protected function change_date_return_width_specification_signed_AJAX(){
				// $html = '';
				// $html .= $this->print_arr($_POST);

				// $html .= date("Y-m-d",strtotime($_POST['date']));
				global $mysqli;

				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  
					`date_specification_signed` =  '".date("Y-m-d",strtotime($_POST['date']))."' 
					WHERE  `id` ='".(int)$_POST['order_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
				//echo '{"response":"show_new_window_simple", "html":"'.base64_encode("Запрос нужно делать").'","title":"Разработчику!!!"}';
			}

			// сохранение номера счёта
			protected function change_number_the_bill_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_TBL."`  SET  
					`number` =  '".addslashes($_POST['value'])."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// сохранение суммы счёта
			protected function change_for_price_the_bill_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_BILL_TBL."`  SET  
					`for_price` =  '".addslashes($_POST['value'])."' 
					WHERE  `id` ='".(int)$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}


			//получить все счета по заказу в массиве
			protected function get_the_bill_for_order_Database($order_id){
				global $mysqli;
				// типы счетов которые мы можем запросить
		    	/*
		    		$type_the_bill =array(
		    		'the_bill' => 'счёт',
		    		'the_bill_offer' => 'счёт - оферта',
		    		'the_bill_for_simples' => 'счёт на образцы',
		    		'prihodnik' => 'приходник',
		    		);
		    	*/

				$query = "SELECT *,
				DATE_FORMAT(`".CAB_BILL_TBL."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `date_create`
				 FROM `".CAB_BILL_TBL."` WHERE `order_id` = '".$order_id."'";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$the_bill_arr = array();				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$the_bill_arr[] = $row;
					}
				}
				return $the_bill_arr;
			}

			//получить все счета по заказу в Html для бух учёта
			protected function get_the_bill_for_order_Html($order){
				global $mysqli;
				// типы счетов которые мы можем запросить
		    	// $this->type_the_bill 

				// запрашиваем
				$the_bill_arr = $this->get_the_bill_for_order_Database($order['id']);
				if(empty($the_bill_arr)){return '';}

				$html = '';

				$html .='<table>';
				foreach ($the_bill_arr as $key => $the_bill) {
					switch ($the_bill['deleted']) { // проверка не удален ли
						case '0': // не удалён
							// номер чего
							$html .= '<tr class="container_from_the_bill">';
								$html .='<td>';
										$html .= '<span class="change_order_type" data-id="'.$the_bill['id'].'">№ '.$this->type_the_bill[$the_bill['type_the_bill']][1].'</span>';
								$html .='</td>';
								$html .='<td>';
									// поле редактирования номера
									$html .= '&nbsp;<input type="text" data-id="'.$the_bill['id'].'" name="number" class="number_the_bill" value="'.$the_bill['number'].'">';
									$html .= '<span>';
										$html .= 'Дата: ';
										// дата создания счёта
										$html .= '<input type="text" data-id="'.$the_bill['id'].'" name="date_create" class="date_create_the_bill" value="'.$the_bill['date_create'].'">';
									$html .= '</span>';
									$html .= '<span>';
										$html .= 'На сумму: ';
										// дата создания счёта
										$html .= '<input type="text" data-id="'.$the_bill['id'].'" name="for_price" class="for_price_the_bill" value="'.$the_bill['for_price'].'"> р';
									$html .= '</span>';
								$html .= '</td>';
								$html .= '<td>';
									$html .= '<span class="'.(($the_bill['comments']=="")?'tz_text_new':'buch_comments').'"  data-id="'.$the_bill['id'].'"></span>';
								$html .= '</td>';
								$html .= '<td>';
									$html .= '<span class="button usl_del" data-id="'.$the_bill['id'].'">X</span>';
								$html .='</td>';
							$html .= '</tr>';
							break;
						
						default: // удалён и запрещён к редактированию
							// номер чего
							$html .= '<tr class="container_from_the_bill deleted">';
								$html .='<td>';
									$html .= '<span class="change_order_type only_read" data-id="'.$the_bill['id'].'">№ '.$this->type_the_bill[$the_bill['type_the_bill']][1].'</span>';										
								$html .='</td>';
								$html .='<td>';
									$html .= '<div class="deleted_note">'.$the_bill['deleted_note'].'</div>';
									// поле номера
									$html .= '&nbsp;'.$the_bill['number'];
									$html .= '<span>';
										$html .= 'Дата: ';
										// дата создания счёта
										$html .= $the_bill['date_create'];
									$html .= '</span>';
									$html .= '<span>';
										$html .= 'На сумму: ';
										// дата создания счёта
										$html .= $the_bill['for_price']. ' р';
									$html .= '</span>';
									$html .= '<span class="why_this_doc_deleted">';
										$html .= 'Удален';
									$html .= '</span>';
								$html .= '</td>';
								$html .= '<td>';
									$html .= '<span class="buch_comments only_read"  data-id="'.$the_bill['id'].'"></span>';
								$html .= '</td>';
								$html .= '<td>';
								$html .='</td>';
							$html .= '</tr>';

							break;
					}
					
				}
				$html .= '</table>';
				return $html;


			}


			// вывод окна бух учёта AJAX
			protected function get_window_buh_uchet_AJAX(){
				echo '{"response":"show_new_window_simple", "html":"'.base64_encode($this->get_window_buh_uchet()).'","title":"Бухгалтерский учёт:","width":"1100"}';
			}

			// окно бух учёта
			protected function get_window_buh_uchet(){
				$html = '';
				// строка заказ, менеджер, Компания
				$this->Order = $this->get_one_order_row_Database((int)$_POST['order_id']);

				// преобразовываем вид номера заказа для пользователя (подставляем впереди 0000)
				$this->order_num_for_User = Cabinet::show_order_num($this->Order['order_num']);

				// номер заказа, менеджер, компания
				$html .= '<div class="buh_window general_info_for_order">';
					$html .= '<span><strong>Заказ: </strong>'.$this->order_num_for_User.'</span>';
					include_once './libs/php/classes/manager_class.php';
					
					$html .= '&nbsp;<span>Менеджер: '.Manager::get_snab_name_for_query_String($this->Order['manager_id']).'</span>';
					$html .= '&nbsp;<span>Компания: '.$this->get_client_name_simple_Database($this->Order['client_id'],1).'</span>';
				$html .= '</div>';


				// Спецификация и счёт
				$html .= '<div class="buh_window">';
					$html .= '<span><strong>Спецификация и счёт </strong></span><br>';
					$html .= '<span>Спецификация: '.$this->get_specification_link($this->Order).'</span>';
					$html .= '&nbsp;<span>подписана: <input data-order_id="'.$this->Order['id'].'" type="text" value="'.$this->Order['date_specification_signed'].'" class="date_specification_signed"></span>';
					$html .= '&nbsp;<span>возвращена с подписью: <input data-order_id="'.$this->Order['id'].'" type="text" value="'.$this->Order['date_return_width_specification_signed'].'" class="date_return_width_specification_signed"></span>';
					$html .= '&nbsp;<span>Для договора: '.$this->get_agreement_link($this->Order).'</span>';


					// счета
					$html .= '<div id="container_from_the_bill">';
						$html .= $this->get_the_bill_for_order_Html($this->Order);
						$html .= '<div id="add_the_bill_link" data-id="'.$this->Order['id'].'">';
								$html.= '<span>добавить строчку для доп счёта</span>';
						$html .= '</div>';	

					$html .= '</div>';
				
				$html .= '</div>';


				// $html .= $this->print_arr($this->Order);				
				// $html .= $this->print_arr($_POST);

				//////////////////////////
				//	выводим скрытый POST для обновления окна
				//////////////////////////
					$html .= '<form>';
					// перебираем остальные значения для передачи их далее
						foreach ($_POST as $key => $value) {
							$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
						}
					$html .= '</form>';
				//////////////////////////
				//	выводим скрытый POST для обновления окна
				//////////////////////////
				return $html;
			}





			// запрос информации по заказу
			protected function get_one_order_row_Database($order_id){
				global $mysqli;
				$query = "SELECT 
				`".CAB_ORDER_ROWS."`.*, 
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`create_time`,'%d.%m.%Y %H:%i:%s')  AS `create_time`,
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`date_specification_signed`,'%d.%m.%Y %H:%i:%s')  AS `date_specification_signed`,
				DATE_FORMAT(`".CAB_ORDER_ROWS."`.`date_return_width_specification_signed`,'%d.%m.%Y %H:%i:%s')  AS `date_return_width_specification_signed`
				FROM `".CAB_ORDER_ROWS."`";
				$query .= " WHERE `".CAB_ORDER_ROWS."`.`id` = '".$order_id."'";

				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				$Order_arr = array();
				
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$Order_arr = $row;
					}
				}
				return $Order_arr;
			}



			// запрос из кнопки выставить счёт
			protected function get_listing_type_the_bill_AJAX(){
				$html = '';
				$html .= '<form>';
				$html .= '<ul id="get_listing_type_the_bill" class="check_one_li_tag">';
				$n = 0;
				$first_val = '';
				foreach ($this->type_the_bill as $name_en => $name_ru) {
					$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru[0].'</li>';
					if($n==0){$first_val = $name_en;}
					$n++;
				}
				$html .= '<input type="hidden" name="type_the_bill" value="'.$first_val.'">';	
				$html .= '<input type="hidden" name="AJAX" value="create_the_new_bill">';	
				$html .= '</ul>';
				// если информации о статусе бух не пришло
				if(!isset($_POST['status_buch'])){$html .= '<input type="hidden" name="status_buch" value="get_the_bill">';}
				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
				$html .= '</form>';

				echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите тип счёта:","width":"230"}';
			}

			// вывод меню выбора запроса счёта 
			protected function get_commands_men_for_buch_AJAX(){
				$html = '';
				$n = 0;
				$html .= '<ul id="get_commands_men_for_buch" class="check_one_li_tag">';
				$first_val = '';
				foreach ($this->commands_men_for_buch as $name_en => $name_ru) {
					$html .= '<li data-name_en="'.$name_en.'" '.(($n==0)?'class="checked"':'').'>'.$name_ru.'</li>';
					if($n==0){$first_val = $name_en;}
					$n++;

				}
				$html .= '</ul>';


				$html .= '<form>';

				$html .= '<input type="hidden" name="status_buch" value="'.$first_val.'">';	
				$html .= '<input type="hidden" name="AJAX" value="get_listing_type_the_bill">';	

				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}

				$html .= '</form>';

				echo '{"response":"show_new_window", "html":"'.base64_encode($html).'","title":"Выберите действие:","width":"230"}';
				// echo '{"response":"OK","html":"'.base64_encode($html).'"}';
				// echo 'base';
			}	

			// правим дату сдачи заказа
			protected function change_date_of_delivery_of_the_order_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  
					`date_of_delivery_of_the_order` =  '".$_POST['date']."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// правим дату утверждения макета
			protected function change_approval_date_AJAX(){
				
				// вносим правки в позицию
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`approval_date` =  '".$_POST['date']."' 
					WHERE  `id` ='".$_POST['row_id']."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				// запускаем все прикреплённые услуги


				////////////////////////////////////////////////
				//  ищем 'being_prepared' меняем на in_processed	
				////////////////////////////////////////////////
					// запрашиваем id прикреплённых услуг, которые необходимо стартануть
					$str = '';
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."`
					WHERE  `dop_row_id` ='".$_POST['dop_data_id']."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					// echo $query;
					$n = 0;
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							if($row['performer_status'] == 'being_prepared' || trim($row['performer_status']) == '')
							$str .= (($n>0)?",":"")."'".$row['id']."'";
							$n++;
						}
					}

					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`performer_status` =  'in_processed' 
					WHERE  `id` IN (".$str.")";
					// echo $query;
					if($str!=''){
						$result = $mysqli->query($query) or die($mysqli->error);	
					}
					
					// меняем на in_processed
					
				////////////////////////////////////////////////
				//  ищем 'being_prepared' меняем на in_processed	
				////////////////////////////////////////////////
				
				echo '{"response":"OK"}';
				//echo 'необходимо доделать функцию. ищем \'being_prepared\' меняем на in_processed';
			}

			// правим срок по дс
			protected function change_deadline_value_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  
					`deadline` =  '".$_POST['value']."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}
			// детализация позиции по прикреплённым услугам
			protected function get_a_detailed_article_on_the_price_of_positions_AJAX(){
				$html = '';
				 	
				// собираем Object по заказу
				$this->Positions_arr = $this->positions_rows_Database($_POST['order_num']);
				foreach ($this->Positions_arr as $key => $value) {
					$this->Positions_arr[$key]['SERVICES'] = $this->get_order_dop_uslugi($value['id_dop_data']);	 								
				}

				// собираем HTML
				$html .= $this->get_a_detailed_article_on_the_price_of_positions_Html();

				echo '{"response":"OK","html":"'.base64_encode($html).'"}';
			}

			// присваиваем значение поля логотип (в окне доп. тех. инфо) ко всем услугам по текущей позиции
			protected function save_logotip_for_all_position_AJAX(){
				global $mysqli;

				// если массив услуг пуст - заполняем его
				if(empty($this->Services_list_arr)){
					$this->Services_list_arr = $this->get_all_services_Database();
				}

				// завпрашиваем услуги прикрепленные к данной позиции
				$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".(int)$_POST['id_dop_data']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$service_union_arr[] = $row;
					}
				}
				
				$id_s = array();

				// перебираем прикрепленные услуги
				foreach ($service_union_arr as $key => $union_service) {
					// проеряем существует ли описание такой услуги и если существует то включено ли поле логотип
					if(isset($this->Services_list_arr[$union_service['uslugi_id']]) && $this->Services_list_arr[$union_service['uslugi_id']]['logotip_on'] == 'on'){
						// запоминаем id прикреплённой услуги, который мы намереваемся изменить
						$id_s[] = $union_service['id'];
					}else{
						// если такой услуги у нас в списках почему-то нет, но она у нас прикреплена
						// т.е. мы не можем проверить включено ли поле логотип в данной прикрепленнной услуге и заполняем ету ячейку в базе без проверки 
						$id_s[] = $union_service['id'];
					}
				}

				// перебор выписанных нами id услуг, к которым мы будем прикреплять логотип
				$id_s_str = '';
				foreach ($id_s as $key => $value) {
					$id_s_str .= (($key>0)?',':'')."'".$value."'";
				}



				if($id_s_str != ''){
					//////////////////////////////////////////////////////////////////////////////
					//	запрос на прикрепление логотипа к услугам прикреплённым к позиции
					//////////////////////////////////////////////////////////////////////////////
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
						`logotip` =  '".trim($_POST['logotip'])."' 
						WHERE  `id` IN (".$id_s_str.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					// формируем ответ
					$Message = 'Значение проля логотип успешно прикреплено ко всем услугам по текущей позиции.';
				}else{
					// формируем ответ
					$Message = 'К данной позиции не прикреплено ни одной услуги<br> в которой можно было бы заполнить поле логотип.';

				}

				echo '{"response":"OK","message":"'.base64_encode($Message).'", "function":"php_message_alert", "title":"Сообщение из ОС"}';
			}

			// присваиваем значение поля логотип (в окне доп. тех. инфо) ко всем услугам по текущему заказу
			protected function save_logotip_for_all_order_AJAX(){
				global $mysqli;

				// если массив услуг пуст - заполняем его
				if(empty($this->Services_list_arr)){
					$this->Services_list_arr = $this->get_all_services_Database();
				}


				// запрашиваем позиции прикреплённые к зауазу
				$query = "SELECT *, `".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data` 
				FROM `".CAB_ORDER_DOP_DATA."` 
				INNER JOIN ".CAB_ORDER_MAIN." ON `".CAB_ORDER_MAIN."`.`id` = `".CAB_ORDER_DOP_DATA."`.`row_id` 
				WHERE `".CAB_ORDER_MAIN."`.`order_num` = '".(int)$_POST['order_num']."'";
				$dop_row_id_str = '';
				$result = $mysqli->query($query) or die($mysqli->error);
				$n = 0;
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$dop_row_id_str .= (($n>0)?',':'')."'".$row['id_dop_data']."'";
						$n++;
					}
				}
				// echo $query.'  <br>  '.$dop_row_id_str; 

				$service_union_arr = array();
				// если у нас есть список dop_row_id позиций
				if($dop_row_id_str != ''){
					// завпрашиваем услуги прикрепленные к позициям заказа
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` IN (".$dop_row_id_str.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$service_union_arr[] = $row;
						}
					}
				}

				// echo '<br>'.$query.'<br>';
				
				$id_s = array();

				// перебираем прикрепленные услуги
				foreach ($service_union_arr as $key => $union_service) {
					// проеряем существует ли описание такой услуги и если существует то включено ли поле логотип
					if(isset($this->Services_list_arr[$union_service['uslugi_id']]) && $this->Services_list_arr[$union_service['uslugi_id']]['logotip_on'] == 'on'){
						// запоминаем id прикреплённой услуги, который мы намереваемся изменить
						$id_s[] = $union_service['id'];
					}else{
						// если такой услуги у нас в списках почему-то нет, но она у нас прикреплена
						// т.е. мы не можем проверить включено ли поле логотип в данной прикрепленнной услуге и заполняем ету ячейку в базе без проверки 
						$id_s[] = $union_service['id'];
					}
				}

				// перебор выписанных нами id услуг, к которым мы будем прикреплять логотип
				$id_s_str = '';
				foreach ($id_s as $key => $value) {
					$id_s_str .= (($key>0)?',':'')."'".$value."'";
				}

				// echo $id_s_str,'  <br>';

				if($id_s_str != ''){
					//////////////////////////////////////////////////////////////////////////////
					//	запрос на прикрепление логотипа к услугам прикреплённым к позиции
					//////////////////////////////////////////////////////////////////////////////
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
						`logotip` =  '".trim($_POST['logotip'])."' 
						WHERE  `id` IN (".$id_s_str.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					// формируем ответ
					$Message = 'Значение проля логотип успешно прикреплено ко всем услугам заказа № '.$_POST['order_num'].'.';
					
					// echo $query;
				}else{
					// формируем ответ
					$Message = 'К данному заказу не прикреплено ни одной услуги<br> в которой можно было бы заполнить поле логотип.';

				}

				echo '{"response":"OK","message":"'.base64_encode($Message).'", "function":"php_message_alert","title":"Сообщение из ОС"}';
			}

			// сохранение пути к макету
			protected function save_the_url_for_layout_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`the_url_for_layout` =  '".base64_encode($_POST['text'])."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				// echo $query;
				echo '{"response":"OK"}';
			}

			// сохранение % готовности (функция с таймингом в JS)
			protected function change_percentage_of_readiness_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `percentage_of_readiness` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}'; 	
			}

			// присваиваем пользователя исполнителя услуги к услуге (взять услугу в работу)
			protected function get_in_work_service_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `performer_id` =  '".$_POST['user_id']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}'; 	
			}

			// редактирование даты работы над услугой
			protected function change_date_work_of_service_AJAX(){
				
				// проверка принятых значений даты
				if (($timestamp = strtotime($_POST['date'])) === false) {
				    return '{"error":"Строка ('.$_POST['date'].') недопустима"}';
				}

				global $mysqli;
				//записываем дату в базу
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `date_work` =  '".date("Y-m-d",$timestamp)."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				
				// echo $query;
				echo '{"response":"OK"}'; 	
			}			

			// запуск услуг в работу
			protected function start_services_in_processed_AJAX(){
				global $mysqli;

				
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `performer_status` =  'in_processed' ";
					$query .= "WHERE  `id` ='".$_POST['id']."';";
					$result = $mysqli->query($query) or die($mysqli->error);
					

					
					// получаем информацию по услуге в которой меняем статус
					$this->Service = $this->get_service((int)$_POST['id']);
					// получаем список прикреплённных услуг отсортированных по Performer
					$this->Services_for_position_arr_sort_by_performer = $this->position_service_sort_of_perforfer($this->Service['dop_row_id']);

					// проверяем все ли услуги Дизайна выполнены
					$design_already_made = true; $n = 0; $design_service_ids = '';
					if(isset($this->Services_for_position_arr_sort_by_performer['9'])){
						foreach ($this->Services_for_position_arr_sort_by_performer['9'] as $key => $design_service) {
							if(trim($design_service['performer_status']) == 'услуга выполнена' || trim($design_service['performer_status']) == 'Макет отправлен в СНАБ'){
								// запоминаем id ДИЗ услуг, которые будем менять на услуга выполнена
								$design_service_ids .=  ((0 != $n++)?',':'')."'".$design_service['id']."'";
							}else{
								// меняем флаг
								$design_already_made = false;									
							}
						}
					}

					// если услуги дизайна выполнены - 
					if ($design_already_made == true) { 
						// переводим статусы услуг дизайно в окончательный статус
						$this->update_performer_status_for_services('услуга выполнена', $design_service_ids);

						// правим статус указанной услуги
						$this->update_performer_status_for_services('in_processed', "'".(int)$_POST['id']."'");
						// отвечаем, что всё ОК и перезагружаем страницу
						echo '{"response":"OK","function":"window_reload"}'; 
					}




					// для не админов и всех кто так или иначе получил доступ к кнопке или для всех поголовно 
					// --- нужно обсудить с Серёгой
					//!!!!!! доделать !!!!!
					/*
					1). сдалеть запрос в базу и узнать dop_data_id в данной услуге
					2). опросить базу на услуги с куратором ДИЗ по данному dop_data_id
					3). если НЕ Нашли - просто запускаем в работу
						если НАШЛИ - выводим окно с перечислением услуг диза, их текущего статуса и предлагаем 
						3 варианта:
						1). Запустить и изменить статус услуг диза на "услуга выполнена"
						2). Запустить и не менять статусы
						3). Отменить запуск услуги в работу


						или вообще не давать запустить если ты не админ.... ведь запуск без дизайна 
						обычно не возможен.... хотя это ведь может быть распаковка и её нужно сделать заранее
						что тогда???? выходит придётся дать такую превилегию снабженцам???? 

					*/

			}

			/////////////////////////////////////////////////////////////////////////////////////
			//	-----  START  -----  service->  -----  START  -----
			/////////////////////////////////////////////////////////////////////////////////////
				// обновление статусов у группы услуг
				protected function update_performer_status_for_services($status, $IDs){ // нужно ли???? !!!! 
					global $mysqli;
					$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `performer_status` =  '".$status."' ";
					$query .= "WHERE  `id` IN (".$IDs.");";
					$result = $mysqli->query($query) or die($mysqli->error);
					return;
				}

				// получаем прикреплённую услугу по id
				protected function get_service($service_id){
					global $mysqli;
					$service = 0;
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".$service_id."'";
					
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$service = $row;
						}
					}
					return $service;
				}

				// получаем список услуг к позиции
				protected function get_all_services_for_position($dop_row_id){
					global $mysqli;
					$position_service_arr = array();
					$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$position_service_arr = $row;
						}
					}
					return $position_service_arr;
				}

				// сортируем массив прикреплённных услуг по Performer (куратору, подразделению) 
				protected function position_service_sort_of_perforfer($dop_row_id = 0){
					if(!isset($this->Services_for_position_arr)){
						if($dop_row_id != 0){
							$this->Services_for_position_arr = $this->get_all_services_for_position($dop_row_id);
						}else{
							echo 'dop_row_id не указан!!! ';exit;
						}
					}

					$service_arr_filtering_for_performer = array();
					foreach ($this->Services_for_position_arr as $service) {
						$service_arr_filtering_for_performer[$service['performer']][] = $service;
					}

					return $service_arr_filtering_for_performer;
				}



				
			//////////////////////////////////////////////////////////////////////////////////
			//   -----  END  -----  service->  -----  END  -----
			///////////////////////////////////////////////////////////////////////////////////

			// выяисляем свёрнут или развёрнут заказ для данного пользователя
			protected function get_open_close_for_this_user($open_close){
				$open_close_arr = ($open_close!="")?json_decode($open_close,true):array();
				//return true;
				if(isset($open_close_arr[$this->user_id])){
					// class для кнопки показать / скрыть
					$this->open_close_class = "";
					// rowspan / data-rowspan
					$this->open_close_rowspan = "rowspan";
					// стили для строк которые скрываем или показываем
					$this->open_close_tr_style = ' style="display: table-row;"';
					return true;
				}else{
					// class для кнопки показать / скрыть
					$this->open_close_class = " show";
					// rowspan / data-rowspan
					$this->open_close_rowspan = "data-rowspan";
					// стили для строк которые скрываем или показываем
					$this->open_close_tr_style = '"';
					return false;
				}
			}

			// раскрыть скрыть заказ
			protected function open_close_order_AJAX(){

				/*
					т.к. один и тот же заказ может просматривать очень много пользователей одновременно 
					хранить информацию о том закрыт данный заказ или открыть у данного пользователя будем в Json
					массив имеет вид
					array( $user_id => 1,$user_id => 1 )
					примем за правило:
						- если записи нет - то заказ свёрнут
						- если запись есть - заказ развёрнут

				*/
				$tbl = (isset($_GET['section']) && $_GET['section'] == 'requests')?RT_LIST:CAB_ORDER_ROWS;


				// делаем запрос на хранящуюся у нас в базе информацию по данной строке заказа
				global $mysqli;
				$open_close = "";
				$query = "SELECT `id`, `open_close` FROM `".$tbl."`";
				$query .= " WHERE  `id` ='".$_POST['order_id']."';";

				// делаем выборку open_close
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$open_close = $row['open_close'];
					}
				}

				// echo $query;

				// декодируем json (если там что-то есть)
				//  !!!! если будут вылезать ошибке о некорректном json - будем решать по мере их поступления
				$open_close_arr = array();
				
				if(trim($open_close)!=""){
					$open_close_arr = json_decode($open_close,true);
				}

				// если заказ нужно открыть
				if($_POST['open_close'] == 1){
					// вставляем в наш массив информацию, что для данного пользователя заказ раскрыт
					$open_close_arr[(string)$this->user_id] = '1';	
				}else{
					// удаляем из массива информацию от том, что заказ 
					if(isset($open_close_arr[$this->user_id])){
						unset($open_close_arr[$this->user_id]);
					}
				}

				// echo '<pre>';
				// print_r($open_close_arr);
				// echo '</pre>';

				// переписываем информацию в строке заказа
				$query = "UPDATE  `".$tbl."`  SET  `open_close` =  '".json_encode($open_close_arr)."' ";
				$query .= " WHERE  `id` ='".$_POST['order_id']."';";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
				
				echo '{"response":"OK"}';
			}

			// ТЗ для производства
			protected function get_dialog_tz_for_production_AJAX(){
				$html = '';
				
				global $mysqli;

				$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `id` = '".(int)$_POST['row_id']."'";
				$result = $mysqli->query($query) or die($mysqli->error);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$service = $row;
					}
				}

				// ограничиваем выгрузку стоимости по допуску юзера
				if($this->user_access == 1 || $this->user_access == 2 || $this->user_access == 5 || $this->user_access == 8 || $this->user_access == 9){
					// стоимость услуги
					$html .= '<div class="separation_container">';
						$html .= '<strong>Входащая стоимость услуги</strong>:<br>';
						$html .= '<div class="data_info">'.$service['price_in'].' р.</div>';
						$html .= '<strong>Исходащая стоимость услуги</strong>:<br>';
						$html .= '<div class="data_info">'.$service['price_out'].' р.</div>';			
					$html .= '</div>';
				}else if($this->user_id == $this->director_of_operations_ID){
					// стоимость услуги
					$html .= '<div class="separation_container">';
						$html .= '<strong>Входащая стоимость услуги</strong>:<br>';
						$html .= '<div class="data_info">'.$service['price_in'].' р.</div>';
					$html .= '</div>';
				}

				// путь к макету
				// если указан
				if (trim($service['the_url_for_layout'])!='') {
					$html .= '<div class="separation_container">';
					$html .= '<strong>Путь к макету</strong>:<br>';
					$html .= '<div class="data_info">'.base64_decode($service['the_url_for_layout']).'</div>';			
				$html .= '</div>';
				}



				//////////////////////////
				//	ДОП ПОЛЯ
				//////////////////////////
				if(!isset($this->dop_inputs_listing)){
					// получаем список всех полей
					$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
					$result = $mysqli->query($query) or die($mysqli->error);
					$this->dop_inputs_listing = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->dop_inputs_listing[$row['name_en']] = $row;
						}
					}

				}


				// получаем  json
				$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
				// декодируем json  в массив
				$this->print_details_dop = json_decode($this->print_details_dop_Json, true);
				
				if(!isset($this->print_details_dop)){
					$html .= "<div>произошла ошибка json</div>";
				}

				if(isset($this->print_details_dop) && !empty($this->print_details_dop)){
					//echo  $service['print_details_dop'];
					foreach ($this->print_details_dop as $key => $text) {
						$html .= '<div class="separation_container">';
							$html .= '<strong>'.$this->dop_inputs_listing[$key]['name_ru'].'</strong>:<br>';
							$html .= '<div class="data_info">'.$text.'</div>';		
						$html .= '</div>';			
					}
				}
				//////////////////////////
				//	текст TЗ 
				//////////////////////////
				$html .= '<div class="separation_container">';
					$html .= '<strong>Техническое задание, пояснения:</strong><br>';
					$html .= '<div class="data_info">'.base64_decode($service['tz']).'</div>';
				$html .= '</div>';
				echo '{"response":"OK","html":"'.base64_encode($html).'","title":"ТЗ"}';
			}

			// смена статуса плёнок по услуге
			protected function choose_statuslist_film_photos_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `film_photos_status` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// смена статуса услуги
			protected function choose_service_status_AJAX(){
				
				global $mysqli;
				//////////////////////////
				//	смена статуса услуги
				//////////////////////////
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  `performer_status` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['id_row']."';";
				$result = $mysqli->query($query) or die($mysqli->error);

				

				////////////////////////////////////////////////
				//	следствие окончания всех услуг на позицию  START
				////////////////////////////////////////////////
					// каждый раз при смене статуса по услуге 
					// (если этот статус меняется из под профиля пр-во )  !!!!!!!
					// система должна проверить статусы по всем услугам прикрепленным 
					// к текущей позиции и при условии, что все статусы будут иметь значение 
					// "услуга выполнена" система выставляет статусы склада и снабжения 
					// по данной позиции на готов к отгрузке

					if($this->user_access == 4 && isset($_POST['value']) && $_POST['value'] == 'услуга выполнена'){
						// проверяем все ли услуги выполнены
						$query = "SELECT `performer_status`,`dop_row_id` FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".$this->get_dop_data_id_from_service($_POST['id_row'])."'";
						// echo $query;
						$result = $mysqli->query($query) or die($mysqli->error);
						$union_services_arr = array();

						$UPDATE_FLAG = true;
						$dop_row_id = 0;
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								if (trim($row['performer_status']) != 'услуга выполнена') {
									$UPDATE_FLAG = false;									
								}
								$dop_row_id = $row['dop_row_id'];
							}
						}

						// если $UPDATE_FLAG всё ещё true , т.е. все услуги готовы
						if($UPDATE_FLAG == true && $dop_row_id!=0){
							// делаем запрос по $dop_row_id и узнаем id строки из CAB_ORDER_MAIN
							$query = "SELECT * FROM `".CAB_ORDER_DOP_DATA."` WHERE `id` = '".$dop_row_id."'";
							$result = $mysqli->query($query) or die($mysqli->error);
							$row_id = 0;
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									$row_id = $row['row_id'];
								}
							}

							if($row_id != 0){
								// обновляем статусы снабжения и склада
								$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
								`status_snab` =  'ready_for_shipment',
								`status_sklad` =  'checked_and_packed' 
								WHERE  `id` ='".$row_id."';";
								$result = $mysqli->query($query) or die($mysqli->error);
							}						
						}
					}
				////////////////////////////////////////////////
				//	следствие окончания всех услуг на позицию  END
				////////////////////////////////////////////////

				// echo '{"response":"OK", "function":"php_message","text":"Статус услуги успешно изменён на ` '.$_POST['value'].' `"}';
				

				echo '{"response":"OK"}';
				
					
			}

			// смена глобального статуса ЗАКАЗА
			protected function choose_statuslist_order_and_paperwork_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `global_status` =  '".$_POST['value']."' ";
				$query .= "WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				// echo '{"response":"OK", "function":"window_reload"}';
				echo '{"response":"OK"}';
			}

			//////////////////////////
			// смена статуса бухгалтерии
			//////////////////////////
				protected function buch_status_select($value,$row_id){
					global $mysqli;
					$query = "UPDATE  `".CAB_ORDER_ROWS."`  SET  `buch_status` =  '".$value."' ";
					// если есть следствия влияющие на статус заказа
					if(isset($this->CONSEQUENCES_of_status_buch[trim($value)])){
						// меняем статус ПРЕДЗАКАЗА / ЗАКАЗА
						$query .= " , `global_status` =  '".$this->CONSEQUENCES_of_status_buch[trim($value)]."'";
					} 

					$query .= "WHERE  `id` ='".$row_id."';";
					$result = $mysqli->query($query) or die($mysqli->error);
				}
				protected function buch_status_select_AJAX(){
					$this->buch_status_select($_POST['value'],$_POST['row_id']);
					echo '{"response":"OK"}';
				}



			// смена статуса склада
			protected function choose_statuslist_sklad_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`status_sklad` =  '".$_POST['value']."' ";
				// все статусы склада транслируются в статусы снабжения
				$query .= " , `status_snab` =  '".$_POST['value']."'";				

				$query .= " WHERE  `id` ='".$_POST['row_id']."';";

				$result = $mysqli->query($query) or die($mysqli->error);
				// echo $query;
				echo '{"response":"OK"}';
			}

			// редактирование ожидаемой даты поставки товара на склад
			protected function change_waits_products_div_input_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`date_delivery_product` =  '".$_POST['date']."' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// смена статуса снабжения
			protected function change_status_snab_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_ORDER_MAIN."`  SET  
					`status_snab` =  '".$_POST['val']."',
					`approval_date` =  '' 
					WHERE  `id` ='".$_POST['row_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}


			protected function replace_query_row_AJAX(){
				$method = $_GET['section'].'_Template';
				// echo $method;
				// если в этом классе существует искомый метод для AJAX - выполняем его и выходим
				if(method_exists($this, $method)){
					$html = $this->$method($_POST['os__rt_list_id']);
					
					echo '{"response":"OK","html":"'.base64_encode($html).'"}';
					
					exit;
				}							
			}
			// получение формы выбора услуги для заказа
			protected function get_uslugi_list_Database_Html_steep_1_AJAX(){ // шаг 0: выбираем услугу
				$html = '<form>';
				$html.= '<div class="lili lili_head"><span class="name_text">Название услуги</span><div class="echo_price_uslug"><span>$ вход.</span><span>$ исх.</span><span>за сколько</span></div></div>';
				// список услуг
				$html .= $this->get_uslugi_list_Database_Html();
				$html .= '<input type="hidden" name="id_uslugi" value="">';
				$html .= '<input type="hidden" name="performer" value="">';
				$html .= '<input type="hidden" name="service_name" value="">';
				$html .= '<input type="hidden" name="id_dop_data" value="'.$_POST['id_dop_data'].'">';
				// $html .= '<input type="hidden" name="quantity" value="">';
				$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_form_steep_2">';
				$html .= '</form>';
				echo $html;
			}
			// вывод формы дополнительных фопросов по добавляемой услуге
			// ШАГ 2: заполняем тираж, и доп поля (если назначены)
			protected function add_new_usluga_form_steep_2_AJAX(){ 
				$html = 'Услуга <strong>'.$_POST['service_name'].'</strong>';
				
				$html .= '<form>';
				// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 
				unset($_POST['AJAX']);
				// перебираем остальные значения для передачи их далее
				foreach ($_POST as $key => $value) {
					$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
				
				// добавляем новые поля по необходимости
				$html .= '<div>';
				$html .= 'Введите тираж:<br>';
				$html .= '<input type="text" name="quantity" value="">';
				$html .= '</div>';

				//$html .= $this->get_dop_inputs_for_services($_POST['id_uslugi'], $_POST['id_dop_data'])

				$html .= $this->get_empty_dop_inputs_form($_POST['id_uslugi']);

				$html .= '<div>';
				$html .= 'Введите ТЗ / комментарии к услуге:<br>';
				$html .= '<textarea name="tz"></textarea>';
				$html .= '</div>';


				// передаём название метода, который будет обрабатывать данную форму
				$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_form_steep_3">';
				// чтобы узнать имя пользователя подключам класс Managers
				include_once './libs/php/classes/manager_class.php';
				$html .= '<input type="hidden" name="author_name_added_services" value="'.Manager::get_snab_name_for_query_String($_SESSION['access']['user_id']).'">';
				$html .= '<input type="hidden" name="author_id_added_services" value="'.$_SESSION['access']['user_id'].'">';
				$html .= '</form>';
				echo '{"response":"show_new_window","title":"Шаг 2: Заполните необходимые поля", "html":"'.base64_encode($html).'"}';
			}

			// ШАГ 3: проверка данных, коррекция цены
			protected function add_new_usluga_form_steep_3_AJAX(){ 
				$this->TZ = $_POST['tz'];
				unset($_POST['tz']);
				unset($_POST['AJAX']);// удаляем пеерменную AJAX - она содержит название метода AJAX, оно изменится 

				global $mysqli;
				$html = '';
				// СОБИРАЕМ СКРЫТУЮ ФОРМУ
				$html .= '<form>';

				// перебираем POST массив для передачи их далее
				$f = 0;
				foreach ($_POST as $key => $value) {
					if($key!='dop_inputs'){
						$html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
					}else if(!$f){
						foreach ($_POST['dop_inputs'] as $key1 => $value) {
							$html .= '<input type="hidden" name="dop_inputs['.$key1.']" value=\''.$value.'\'>';
							// $html .= '<tr><td>'.$iputs_all_arr[$key]['name_ru'].':</td><td>'.$value.'</td></tr>';
						}
						$f++;	
					}else{

					}
				}

				
				// ВЫВОДИМ ВВЕДЁННУЮ ИНФОРМАЦИЮ ДЛЯ ПРОВЕРКИ
				$html .= '<table id="check_input_iformation">';
				$html .= '<tr><td>Услуга:</td><td>'.$_POST['service_name'].'</td></tr>';
				$html .= '<tr><td>Тираж:</td><td>'.$_POST['quantity'].'</td></tr>';
				$this->Service_price_in = ($_POST['for_how'] == "for_one")?$_POST['quantity']*$_POST['price_in']:$_POST['price_in'];
				// $this->Service_price_out = ($_POST['for_how'] == "for_one")?$_POST['quantity']*$_POST['price_out']:$_POST['price_in'];
				$this->Service_price_out = 0; // для услуг добавленных в заказ показываем исходащую цену = 0, т.е. их сибистоимость вычитается из маржинальности
				
				
				$html .= '<tr><td>$ входящая:</td><td><span>'.(($this->user_access==1 || $this->user_access==8)?'<input type="text" name="price_in" value="'.$this->Service_price_in.'">':$this->Service_price_in).'</span>р.</td></tr>';
				

				$html .= '<tr><td>$ исходащая:</td><td><span>'.$this->Service_price_out.'</span>р</td></tr>';
				if(isset($_POST['dop_inputs']) && count($_POST['dop_inputs'])){// если есть доп поля
					// для представления пользователю информациии по доп полям в читабельном виде
					// получаем список всех полей dop_inputs
					$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
					$result = $mysqli->query($query) or die($mysqli->error);
					$iputs_all_arr = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$iputs_all_arr[$row['name_en']] = $row;
						}
					}

					$html .= '<tr><td colspan="2" style="text-align:center">Дополнительные поля</td></tr>';
					foreach ($_POST['dop_inputs'] as $key => $value) {
						$html .= '<tr><td>'.$iputs_all_arr[$key]['name_ru'].':</td><td>'.$value.'</td></tr>';
					}				
				}

				$html .= '<table>';
				$html .= '<div>';
				$html .= 'Введите ТЗ / комментарии к услуге:<br>';
				$html .= '<textarea name="tz">'.$this->TZ.'</textarea>';
				$html .= '</div>';

				// передаём название метода, который будет обрабатывать данную форму
				$html .= '<input type="hidden" name="AJAX" value="add_new_usluga_end">';

				$html .= '</form>';

				echo '{"response":"show_new_window","title":"Шаг 3: Проверка введённых данных","html":"'.base64_encode($html).'"}';
				
			}
			// ОБРАБОТКА ДАННЫХ ИЗ ШАГ 3
			protected function add_new_usluga_end_AJAX(){
				
				global $mysqli;
				// ob_start();
				// echo '<pre>';
				// print_r($_POST['dop_inputs']);
				// echo '</pre>';
				    	
				// $content = ob_get_contents();
				// ob_get_clean();
				// $html =$content;
				// echo $html;
				$query ="INSERT INTO `".CAB_DOP_USLUGI."` SET ";
				$query .= "`dop_row_id` = '".$_POST['id_dop_data']."',";
				$query .= "`uslugi_id` = '".$_POST['id_uslugi']."',";
				$query .= "`glob_type` = 'extra',";
				$query .= "`quantity` = '".$_POST['quantity']."',";
				$query .= "`price_in` = '".$_POST['price_in']."',";
				$query .= "`price_out` = '".$_POST['price_out']."',";
				$query .= "`performer` = '".$_POST['performer']."',";

				$query .= "`for_how` = '".$_POST['for_how']."',";
				$query .= "`tz` = '".$_POST['tz']."',";
				// собираем JSON по доп полям
				if(isset($_POST['dop_inputs']) && count($_POST['dop_inputs'])){
					$query .= "`print_details_dop` = '".json_encode($_POST['dop_inputs'])."',";
				}

				$query .= "`author_name_added_services` = '".$_POST['author_name_added_services']."',";
				$query .= "`author_id_added_services` = '".$_POST['author_id_added_services']."',";			
				$query .= "`change_log` = 'Добавил ".$_POST['author_name_added_services']." ".date("d.m.Y H:m:s")."'";



				$result = $mysqli->query($query) or die($mysqli->error);
				// запоминаем новый id
				$insert_id = $mysqli->insert_id;

				// собираем HTML
				$html = '<tr class="not_provided" data-id="'.$insert_id.'">
						<td></td>
						<td><span class="postfaktum_non_calculate">0</span></td>
						<td><span class="postfaktum_non_calculate service_price_in">0</span></td>
						<td><span class="postfaktum_non_calculate">0</span></td>
						<td><span class="postfaktum_non_calculate service_price_out">0</span></td>
						<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>
						<td class="postfaktum"></td><td class="postfaktum added_postfactum">'.$_POST['service_name'].'</td>
						<td class="postfaktum added_postfactum"><span>'.(($_POST['for_how'] == 'for_one')?$_POST['quantity']:'  -  ').'</span></td>
						<td class="postfaktum added_postfactum"><span class="service_price_in_postfactum">'.(($_POST['for_how'] == 'for_one')?$_POST['quantity']*$_POST['price_in']:$_POST['price_in']).'</span>р</td>
						<td class="postfaktum"><span data-id="'.$insert_id.'" class="on_of">+</span></td><td></td></tr>';
				
				echo '{"response":"OK","function":"add_new_usluga_end","html":"'.base64_encode($html).'"}';
			}

			// контент для окна доп/тех инфо
			protected function get_dop_tex_info_AJAX(){
				$html = '';
				// подгружаем форму по резерву
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Информация для снабжения</div>';
				$html .= 'Резерв<br>';
				$html .= '<input type="text" class="rezerv_info_input" name="rezerv_info" data-cab_dop_data_id="'.$_POST['id_dop_data'].'" value="'.$this->get_cab_dop_data_position_Database($_POST['id_dop_data']).'">';
				$html .= '</div>';

				// подгружаем форму по заполнению поля логотип для всех услуг
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Логотип (использовать поле при условии, что логотип клиента одинаковый для всех услуг)</div>';
					$html .= '<table id="save_logotip_for_all_services_tbl">';
					$html .= '<tr>';
						$html .= '<td>Название</td>';
						$html .= '<td colspan="2">Применить название для:</td>';
					$html .= '</tr>';
					$html .= '<tr>';

						// собираем строку для передачи данных из POST массива в теги input
						$data_str = '';
						if(isset($_POST)){
							unset($_POST['AJAX']);
							foreach ($_POST as $key => $value) {
								$data_str .= ' data-'.$key.'="'.$value.'"';
							}
						}

						// добавляем кнопки
						$html .= '<td><input type="text" class="save_logotip_for_all_services" name="logotip" data-cab_dop_data_id="'.$_POST['id_dop_data'].'" value=""></td>';
						$html .= '<td><input type="button" name="" '.$data_str.' id="save_logotip_for_all_position" value="Всех услуг в списке этой позиции"></td>';
						$html .= '<td><input type="button" name="" '.$data_str.' id="save_logotip_for_all_order" value="Всех услуг в этом заказе"></td>';
						
					$html .= '</tr></table>';
				$html .= '</div>';

				#######################################

				// подгружаем таблицу услуг
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Услуги</div>';		
						
				$this->uslugi = $this->get_order_dop_uslugi($_POST['id_dop_data']); 
				
				if(count($this->uslugi)){ // если услуги прикреплены
					/*
					// делаем запрос по услугам в базу
					// if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
					// 	$this->Services_list_arr = $this->get_all_services_Database();
					// }
					*/

					// собираем html форму
					$html .= '<table id="services_listing"><tr>';
					$html .= '<tr><th>Название услуги</th><th>Информация для заполнения</th></tr>';
					$html .= '<td id="services_listing_each"><ul>';
					
					// перебираем услуги и вы
					$first_right_content = '';// контент по первой услуге
					$n = 0; // порядковый номер
					foreach ($this->uslugi as $usluga) {
						$no_active = ($usluga['on_of']=='0')?' no_active':'';	// услуга отключена / включена из выполнения в окне финансовой детализации
						$this->Service = $usluga; // по сути строка из CAB_DOP_USLUGI			
						$html .= '<li  data-cab_dop_data_id="'.$_POST['id_dop_data'].'" data-uslugi_id="'.$usluga['uslugi_id'].'"  data-dop_usluga_id="'.$usluga['id'].'" data-id_tz="tz_id_'.$n.'" class="lili '.$usluga['for_how'].' '.(($n==0)?'checked':'').''.$no_active.'" data-id_dop_inputs="'.addslashes($usluga['print_details_dop']).'">'.$usluga['name'].'</li>';
						if($n == 0){
							// запоминаем тз по первой услуге
							$first_right_content .= $this->get_dop_inputs_for_services($usluga['uslugi_id'],$usluga['id']);						
						}
						$n++;
					}
					$html .= '</ul></td>';
					// про

					// $html .= '<td id="content_dop_inputs_and_tz"><span class="title_dop_inputs_info">Выберите услугу</span></td>';
					$html .= '<td id="content_dop_inputs_and_tz">'.$first_right_content.'</td>';

					$html .= '</table>';
				}else{
					$html .= 'услуги не прикреплены.... и это оооочень странно. Обратитесь к Админу.';
				}

				$html .= '</div>';

				################################################

				// подгружаем комментарии для позиции 
				global $PositionComments;
				$html .= '<div class="container_form">';
				$html .= '<div class="green_inform_block">Переписка</div>';
				$html .= '</div>';
				$html .= '<div class="container_form">';
				
				$html .= $PositionComments -> get_comment_for_position_without_Out();
				$html .= '</div>';
				

				// Вывод
				echo '{"response":"OK","html":"'.base64_encode($html).'"}';
			}	

			// включение отключение услуги
			protected function change_service_on_of_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`on_of` =  '".(int)$_POST['val']."' 
					WHERE  `id` ='".$_POST['id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// редактирование поля ТЗ к услуге
			protected function save_tz_info_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`tz` =  '".$_POST['text']."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// редактирование поля логотип к услуге
			protected function save_logotip_info_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`logotip` =  '".$_POST['text']."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// сохранение dop_inputs, поля хранятся в json 
			protected function save_dop_inputs_AJAX(){
				global $mysqli;
				$query = "UPDATE  `".CAB_DOP_USLUGI."`  SET  
					`print_details_dop` =  '".$_POST['Json']."' 
					WHERE  `id` ='".$_POST['cab_dop_usluga_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			// сохранение поля резерв
			protected function save_rezerv_info_AJAX(){
				global $mysqli;

				$query = "UPDATE  `".CAB_ORDER_DOP_DATA."`  SET  
					`number_rezerv` =  '".$_POST['text']."' 
					WHERE  `id` ='".$_POST['cab_dop_data_id']."';";
				$result = $mysqli->query($query) or die($mysqli->error);
				echo '{"response":"OK"}';
			}

			protected function get_dop_inputs_for_services_AJAX(){
				// для вызова AJAX
				if(isset($_POST['uslugi_id'])){
					$html = $this->get_dop_inputs_for_services($_POST['uslugi_id'],$_POST['dop_usluga_id']);
				}else{
					return 'Укажите id услуги';
				}
				echo '{"response":"OK","html":"'.base64_encode($html).'"}';
			}

			// ролучаем dop_inputs
			protected function get_dop_inputs_for_services($id, $dop_usluga_id){
				global $mysqli;
				include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/print_calculators_class.php");
				// запрашиваем информацию по ТЗ и , если нужно
				if(!isset($this->Service)){ // если нам ничего не известно по строке из CAB_DOP_USLUGI
					$query = "SELECT * FROM ".CAB_DOP_USLUGI." WHERE `id` = '".$dop_usluga_id."'";
					$result = $mysqli->query($query) or die($mysqli->error);
					$this->Service = array();
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->Service = $row;
						}
					}
				}
				
				// если у нас есть информация в поле $this->Service['print_details'] - декодируем её в читабельный вид
				if(trim($this->Service['print_details'])!=''){
					include_once './libs/php/classes/agreement_class.php';
					$this->Service['print_details_read'] = '<div><span>Данные из калькулятора:</span><br><div class="calculator_info">'.printCalculator::convert_print_details($this->Service['print_details']) .'</div></div>';
				}else{
					$this->Service['print_details_read'] = '';
				}

				// получаем инфу и настройки по данной услуге
				if(!isset($this->All_Services_arr)){
					$this->All_Services_arr = $this->get_all_services_names_Database();
				}


				$this->iputs_id_Str = isset($this->All_Services_arr[$id]['uslugi_dop_inputs_id'])?$this->All_Services_arr[$id]['uslugi_dop_inputs_id']:'';
				$this->Service_logotip_on = isset($this->All_Services_arr[$id]['logotip_on'])?$this->All_Services_arr[$id]['logotip_on']:'';
				$this->Service_show_status_film_photos = isset($this->All_Services_arr[$id]['show_status_film_photos'])?$this->All_Services_arr[$id]['show_status_film_photos']:'';						
				$this->maket_true_for_Service = isset($this->All_Services_arr[$id]['maket_true'])?$this->All_Services_arr[$id]['maket_true']:'';
						// echo $row['logotip_on'];




				//////////////////////////
				//	СЛЕДУЮЩИЕ 2 запроса В БЛИЖАЙШЕМ БУДУЩЕМ нужно сократить до одного !!!!!!!!!!!!
				//////////////////////////
				// запрашиваем список полей предназначенных для этой услуги
				$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `id` IN (".$this->iputs_id_Str.")";
				$this->iputs_arr = array();
				if(trim($this->iputs_id_Str)!=''){
					$result = $mysqli->query($query) or die($mysqli->error);				
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							$this->iputs_arr[] = $row;
						}
					}
				}

				// получаем список всех полей
				$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."`";
				$result = $mysqli->query($query) or die($mysqli->error);
				$iputs_all_arr = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$iputs_all_arr[] = $row;
					}
				}

				// получаем  json
				$this->print_details_dop_Json = (trim($this->Service['print_details_dop'])=="")?'{}':$this->Service['print_details_dop'];
				// декодируем json  в массив
				$this->print_details_dop = json_decode($this->print_details_dop_Json, true);


				// перебор полей указанных в услуге
				$html = '';
				// добавляем скрытую json строку для обработке в JS
				$html = '<div id="dop_input_json">'.$this->print_details_dop_Json.'</div>';
				
				//$html .= $this->print_arr($this->print_details_dop);

				// добавляем информацию из калькулятора.... если есть
				$html .= $this->Service['print_details_read'];
				foreach ($this->iputs_arr as $key => $input) {
					//echo $input['name_ru'];
					$html .= $input['name_ru'].':<br>';
					//if($input['name_en'] == "tip_pechati"){$html .= '<div>'.$this->print_details_dop[$input['name_en']].'</div>';}
					if($input['type']=="text"){
							if(isset($this->print_details_dop[$input['name_en']])){
								$text = $this->print_details_dop[$input['name_en']];
								$text = htmlspecialchars(base64_decode($text),ENT_QUOTES);
							}else{
								// $html .= '!= '.$input['name_en'];
								$text = '';
							}
							
							// определяем допуски на редактирование доп полей
							if($this->user_access == 9 || $this->user_access == 8 || $this->user_access == 1){
									// $html .= $text;
									$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value=\''.$text.'\'></div>';
								}else{
									$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value=\''.$text.'\' '.((trim($text)=='')?'':'disabled').'></div>';
								}
							
					}else{
							$html .= 'данный тип поля пока что не предусмотрен';
					}
					// удаляем $this->print_details_dop[$input['name_en']]
					unset($this->print_details_dop[$input['name_en']]);				
				}

				
				//перебираем оставшиеся значения из json .... они могут остаться, 
				// если админы что-то наменяли и открепили доп поля от услуги 
				foreach ($iputs_all_arr as $key => $input) {
					if(isset($this->print_details_dop[$input['name_en']])){
						$html .= $input['name_ru'].' * <span class="delete_dop_input_for_admin">(было удалено Админом из списка обязательных полей для услуги)</span><br>';
						if($input['type']=="text"){
								$text = isset($this->print_details_dop[$input['name_en']])?$this->print_details_dop[$input['name_en']]:'';
								$text = htmlspecialchars(base64_decode($text),ENT_QUOTES);
								// определяем допуски на редактирование доп полей
								if($this->user_access == 9 || $this->user_access == 8 || $this->user_access == 1){
									$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'"></div>';
								}else{
									$html .= '<div><input class="dop_inputs" data-dop_usluga_id="'.$dop_usluga_id.'" type="'.$input['type'].'" name="'.$input['name_en'].'" placeholder="" value="'.$text.'" '.(($text=='')?'':'disabled').'></div>';
								}
								
						}else{
								$html .= 'данный тип поля пока что не предусмотрен';
						}	
					}
				}
					// ob_start();
				 // 	echo '<pre>';
				 // 	print_r($this->Service);
				 // 	echo '</pre>';
				    	
				 // 	$content = ob_get_contents();
				 // 	ob_get_clean();
				 // 	$html .=$content;
				
				// подключаем поле логотип, если оно включено в админке или уже что-то содержит
				if($this->Service['logotip']!='' || trim($this->Service_logotip_on)=="on"){
					$html .='<div>Логотип<br><textarea class="save_logotip" name="logotip">'.$this->Service['logotip'].'</textarea></div>';
				}

				// подключаем поле плёнки, если оно включено в админке 
				if(trim($this->Service_show_status_film_photos)=="on"){
					$html .='<div>Плёнки/Клише<br>';
					$html .= $this->get_statuslist_film_photos($this->Service['film_photos_status'],$this->Service['id']);
					// $html .='<textarea class="save_logotip" name="logotip">'.$this->Service['logotip'].'</textarea>';
					$html .='</div>';
				}

				// подключаем поле путь к макету
				if (trim($this->maket_true_for_Service)=="on") {
					$html .='<div>Путь к макету (к старому):<br>';
					$html .= '<div><input type="text" name="the_url_for_layout" placeholder="заполнить при необходимости" class="save_the_url_for_layout" value="'.base64_decode($this->Service['the_url_for_layout']).'"></div>';
					// $html .='<textarea class="save_logotip" name="logotip">'.$this->Service['logotip'].'</textarea>';
					$html .='</div>';
				}

				$html .='<div>Комментарии для исполнителя '.(isset($this->performer[$this->Service['performer']])?'"'.$this->performer[$this->Service['performer']].'"':'').'<br><textarea class="save_tz" name="tz">'.base64_decode($this->Service['tz']).'</textarea></div>';

				return $html;
			}		

				/////////////////////////////////////////
				//	методы для работы AJAX -- START -- //
				/////////////////////////////////////////
					// получаем пустую форму с dop_inputs для прикрепляемой услуги
					protected function get_empty_dop_inputs_form($id){
						global $mysqli;
						// получаем id полей для этой услуги
						$query = "SELECT `name`,`uslugi_dop_inputs_id`,`price_in`,`price_out`,`for_how` FROM ".OUR_USLUGI_LIST." WHERE `id` = '".$id."'";
						$result = $mysqli->query($query) or die($mysqli->error);
						$this->iputs_id_Str = '0';
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								$this->iputs_id_Str = $row['uslugi_dop_inputs_id'];
								$this->AddedServiceName = $row['name'];
								$this->Service = $row;
							}
						}

						// получаем массив dop_inputs
						$query = "SELECT * FROM `".CAB_DOP_USLUGI_DOP_INPUTS."` WHERE `id` IN (".$this->iputs_id_Str.")";
						$this->iputs_arr = array();
						if(trim($this->iputs_id_Str)!=''){
							$result = $mysqli->query($query) or die($mysqli->error);				
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									$this->iputs_arr[] = $row;
								}
							}
						}
						$html = '';


						// добавляем поля dop_inputs
						foreach ($this->iputs_arr as $key => $input) {
							//echo $input['name_ru'];
							$html .= $input['name_ru'].'<br>';
							if($input['type']=="text"){
								$html .= '<div><input type="'.$input['type'].'" name="dop_inputs['.$input['name_en'].']" placeholder="" value=""></div>';
							}
						}
						// добавляем скрытые поля 
						$html .= '<input type="hidden" value="'.$this->Service['price_in'].'"  name="price_in">';
						$html .= '<input type="hidden" value="'.$this->Service['price_out'].'"  name="price_out">'; // для услуг добавленных в заказ исходащая цена = 0, т.е. их сибистоимость вычитается из маржинальности
						$html .= '<input type="hidden" value="'.$this->Service['for_how'].'"  name="for_how">';


						return $html;
					}
					// возвращает список услуг для выбора
					protected function get_uslugi_list_Database_Html($id=0,$pad=30){	
						global $mysqli;
						$html = '';
						
						$query = "SELECT * FROM `".OUR_USLUGI_LIST."` WHERE `parent_id` = '".$id."' AND `deleted` = '0'";
						$result = $mysqli->query($query) or die($mysqli->error);
						

						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								$price = '<div class="echo_price_uslug"><span></span><span></span></div>';
								if($row['id']!=6 && $row['parent_id']!=6){// исключаем нанесение apelburg
									# Это услуги НЕ из КАЛЬКУЛЯТОРА
									// запрос на детей
									$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));
									
									$price = ($child =='')?'<div class="echo_price_uslug"><span>'.$row['price_in'].'</span><span>'.$row['price_out'].'</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
									
									// присваиваем конечным услугам класс may_bee_checked
									$html.= '<div data-performer="'.$row['performer'].'" data-id="'.$row['id'].'" data-parent_id="'.$row['parent_id'].'" class="lili'.(($child=='')?' may_bee_checked '.$row['for_how']:' f_open').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
								}else{
									# Это услуги из КАЛЬКУЛЯТОРА
									// запрос на детей
									$child = $this->get_uslugi_list_Database_Html($row['id'],($pad+30));

									$price = ($child =='')?'<div class="echo_price_uslug"><span>&nbsp;</span><span>&nbsp;</span><span>'.(($row['for_how']=="for_one")?'за ед.':'за тираж').'</span></div>':'';
									// присваиваем конечным услугам класс may_bee_checked
									$html.= '<div data-id="'.$row['id'].'" data-type="'.$row['type'].'" data-parent_id="'.$row['parent_id'].'" class="lili calc_icon'.(($child=='')?' calc_icon_chose':'').'" style="padding-left:'.$pad.'px;background-position-x:'.($pad-27).'px" data-bg_x="'.($pad-27).'"><span class="name_text">'.$row['name'].'</span>'.$price.'</div>'.$child;
								}
							}
						}
						return $html;
					}
				///////////////////////////////////////
				//	методы для работы AJAX -- END -- //
				///////////////////////////////////////
		
		//////////////////////////////////////////////////////////
		//   -----  END  -----  МЕТОДЫ AJAX  -----  END  -----  //
		//////////////////////////////////////////////////////////

		///////////////////////////////////////////////////////////////////////////////////////
		//	-----  START  -----  General Template (общие для всех шаблоны)  -----  START  -----
		///////////////////////////////////////////////////////////////////////////////////////
			protected function history_Template(){
				echo $this->wrap_text_in_warning_message('Сюда попадают заказы, которые были закрыты и по которым была произведена выплата менеджеру.');
			}
			
		////////////////////////////////////////////////////////////////////////////////////
		//   -----  END  -----  General Template (общие для всех шаблоны) -----  END  -----
		////////////////////////////////////////////////////////////////////////////////////
		

		/*
			декодируем поле json для некаталога в читабельный вид
			получаем из json описания некаталожного товара всю содержащуюся там информацию
		*/		
		protected function decode_json_no_cat_to_html($arr){// из cabinet_admin_class.php
			// список разрешённых для вывода в письмо полей
			$send_info_enabled= array('format'=>1,'material'=>1,'plotnost'=>1,'type_print'=>1,'change_list'=>1,'laminat'=>1);


			
			// получаем json с описанием продукта
			$dop_info_no_cat = ($arr['no_cat_json']!='')?json_decode($arr['no_cat_json']):array();
			
			
			$html = '';
			// если у нас есть описание заявленного типа товара
			if(isset($this->FORM->form_type[$arr['type']])){
				$names = $this->FORM->form_type[$arr['type']]; // массив описания хранится в классе форм
				$html .= '<div class="get_top_funcional_byttun_for_user_Html table">';
				foreach ($dop_info_no_cat as $key => $value) {
					if(!isset($send_info_enabled[$key])){continue;}
					$html .= '
						<div class="row">
							<div class="cell" >'.$names[$key]['name'].'</div>
							<div class="cell">'.$value.'</div>
						</div>
					';
				}

				$html .= '</div>';

				return $html;
			}else{// в случае исключения выводим массив, дабы было видно куда копать
				return $this->print_arr($arr);
			}
		}

		// вывод кнопки доп/тех инфо, с подсветкой в случае наличия в ней сообщиений в переписке по позиции
		protected function grt_dop_teh_info($value){
			// т.к. услуги для каждой позиции один хрен перебирать, думаю можно сразу выгрузить контент для окна
			// думаю есть смысл хранения в json 
			// обязательные поля:
			// {"comments":" ","technical_info":" ","maket":" "} ???? 

			// если есть информация
			$no_empty_class = (Comments_for_order_dop_data_class::check_the_empty_position_coment_Database($value['id']))?' no_empty':'';

			$html = '<td>
					<div class="dop_teh_info '.$no_empty_class.'" data-id_dop_data="'.$this->id_dop_data.'" data-id="'.$value['id'].'" data-query_num="'.$this->query_num.'" data-position_item="'.$this->position_item.'" data-order_num="'.$this->order_num.'" data-order_num_User="'.$this->order_num_for_User.'"  >доп/тех инфо</div>
					<div class="dop_teh_info_window_content"></div>
				</td>';

			return $html;
		}	

		// получаем контент для поля логотип
		protected function get_content_logotip($id_dop_data){
			// для полуяения поля логотип запрашиваем услуги
			$this->Services = $this-> get_order_dop_uslugi($id_dop_data);
			// перебираем поля и собираем контент по полю логотип
			$html = '';$n = 0;
			foreach ($this->Services as $num => $service) {
				if($service['logotip'] != ''){
					if($n){$html .= ', ';}
					$html .= $service['logotip'];
					$n++;
				}
			}
			if($html == ''){$html = '  -  ';}
			return $html;
		}

		// общет стоимости позиции
		protected function GET_PRICE_for_position($position){
			////////////////////////////////////
			//	Расчёт стоимости позиций START  
			////////////////////////////////////

				//ОБСЧЁТ ВАРИАНТОВ
				// получаем массив стоимости нанесения и доп услуг для данного варианта 
				$dop_usl = $this-> get_order_dop_uslugi($position['id_dop_data']);

				// выборка только массива стоимости печати
				$dop_usl_print = $this->get_dop_uslugi_print_type($dop_usl);
				// выборка только массива стоимости доп услуг
				$dop_usl_no_print = $this-> get_dop_uslugi_no_print_type($dop_usl);


				// стоимость товара
				$this->Price_for_the_goods = $position['price_out'] * ($position['quantity']+$position['zapas']);
				// стоимость услуг печати
				$this->Price_of_printing = $this -> calc_summ_dop_uslug($dop_usl_print,(($position['print_z']==1)?$position['quantity']+$position['zapas']:$position['quantity']));
				// стоимость услуг не относящихся к печати
				$this->Price_of_no_printing = $this-> calc_summ_dop_uslug($dop_usl_no_print,(($position['print_z']==1)?$position['quantity']+$position['zapas']:$position['quantity']));
				// общаяя цена позиции включает в себя стоимость услуг и товара
				$this->Price_for_the_position = $this->Price_for_the_goods + $this->Price_of_printing + $this->Price_of_no_printing;
					

			////////////////////////////////////
			//	Расчёт стоимости позиций END
			////////////////////////////////////
		}

		// преобразует статус снабжения в читабельный вид
		protected function show_cirilic_name_status_snab($status_snab){
			if(substr_count($status_snab, '_pause')){
				$status_snab = 'На паузе';
			}

			// echo '<pre>';
			// print_r($this->POSITION_NO_CATALOG->status_snab);
			// echo '</pre>';
						
			if(isset($this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'])){
				$status_snab = $this->POSITION_NO_CATALOG->status_snab[$status_snab]['name'];
			}else{
				$status_snab;
			}
			return $status_snab;
		}

		//	оборачивает в div warning_message
		protected function wrap_text_in_warning_message($text){
			$html = '<div class="warning_message"><div>';	
			$html .= $text;
			$html .= '</div></div>';

			return $html;
		}
		
		// определяем поставщика каталожной продукции по номеру артикула
		protected function get_supplier_name($article){
			$html = '';
			global $suppliers_names_by_prefix_for_get_name;		   
			$prefix = substr($article,0,2);
			if(isset($suppliers_names_by_prefix_for_get_name[$prefix])){	
				$html = $suppliers_names_by_prefix_for_get_name[$prefix];
			}else{
				$html = '<span class="greyText">не требуется</span>';
			}
			return $html;
		}

		// подсчет суммы доп услуг или печати
		// на вход подаётся результат работы get_dop_uslugi_print_type() 
		// или get_dop_uslugi_no_print_type
		public function calc_summ_dop_uslug($arr,$tir=0){ // 
			$summ = 0;
						
			// перебираем массив услуг
			foreach ($arr as $key => $value) {
				// если услуга не была добавлена кем либо в заказ, при добавлении добавляется id автора
				if(!isset($value['author_id_added_services']) || $value['author_id_added_services'] <= 0){
					// суммируем её к общей сумме позиции
					if($value['for_how']=="for_one"){
						$summ += ($value['price_out']*$value['quantity']);					
					}else{
						$summ += $value['price_out'];					
					}
				}
			}
			return $summ;
		}

		// выбираем данные о стоимости печати 
		//на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		protected function get_dop_uslugi_print_type($arr){
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']=='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}
		
		// выбираем данные о стоимости доп услуг не относящихся к печати
		// на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		public function get_dop_uslugi_no_print_type($arr){			
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']!='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}

		// выбираем данные о доп услугах для варианта расчёта
		public function get_query_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data`
			global $mysqli;
			$query = "SELECT `".RT_DOP_USLUGI."`.*,`os__our_uslugi`.`name` FROM `".RT_DOP_USLUGI."` 
			LEFT JOIN  `os__our_uslugi` ON  `os__our_uslugi`.`id` = `".RT_DOP_USLUGI."`.`uslugi_id` 
			WHERE `".RT_DOP_USLUGI."`.`dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		// выбираем данные о доп услугах для заказа
		public function get_order_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data` 
			// ВНИМАНИЕ !!!!!!!!!!
			// данный метод используется (специально заточен) для просчёта стоимости заказа и запроса
			// нежелательно его модифицировать для других нужд !!!!!
			global $mysqli;

			// определяем таблицу прикреплённых услуг
			$tbl = (isset($_GET['section']) && $_GET['section'] == 'requests')?RT_DOP_USLUGI:CAB_DOP_USLUGI;

			// при условии что:
			//	- это запросы 
			//  - работает снабжение
			//  - subsection = history
			//  -> выгружаем данные из таблиц для СНАБ HISTORY
			$tbl = ($this->user_access == 8 && isset($_GET['section']) && $_GET['section'] == 'requests' && isset($_GET['subsection']) && $_GET['subsection'] == 'history')?DOP_USLUGI_HIST:$tbl;
			/*
			  при изменении в админке по услуге ответственного за услугу, это изменение коснется только 
			  тех услуг, которые были созданы после этого изменения
			  чтобы изменения брались сразу из услуг нужно дополнить запрос выборкой по полю ,`os__our_uslugi`.`performer`
			*/

			$query = "SELECT 
			`".$tbl."`.*,
			`".OUR_USLUGI_LIST."`.`name`";
			$query .= (isset($_GET['section']) && $_GET['section'] == 'requests')?"":", DATE_FORMAT(`".$tbl."`.`date_ready`,'%d.%m.%Y')  AS `date_ready`";
			$query .= (isset($_GET['section']) && $_GET['section'] == 'requests')?"":",DATE_FORMAT(`".$tbl."`.`date_work`,'%d.%m.%Y')  AS `date_work`";
			$query .= " FROM `".$tbl."` 
			LEFT JOIN  `".OUR_USLUGI_LIST."` ON  `".OUR_USLUGI_LIST."`.`id` = `".$tbl."`.`uslugi_id` 
			WHERE `".$tbl."`.`dop_row_id` = '".$dop_row_id."'";
			$query .= " ORDER BY `".OUR_USLUGI_LIST."`.`id` ASC";

			//$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();

			// echo $query;

			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;


					// для зказа на понадобится дополнительная информация по статусам 
					// если мы не в запросе
					if(isset($_GET['section']) && $_GET['section'] != 'requests'){
						// сортируем услугу в массив $this->Position_status_list по подразделениям
						$er = $this->performer[ $row['performer']];
						
						$new_arr['performer_status'] = $row['performer_status']; 
						$new_arr['service_name'] = $row['name'];
						$new_arr['id'] = $row['uslugi_id'];
						$new_arr['performer'] = $row['performer'];
						$new_arr['id_dop_uslugi_row'] = $row['id'];
						
						$this->Position_status_list[  $er  ][] = $new_arr;
					}
				}
			}
			return $arr;
		}

		static function show_order_num($key){
			$i = 6 - strlen($key);
			// echo $i.'    */';
			$str = '';
			for ($t=0; $t < $i ; $t++) { 
				$str .='0';		}
			return $str.$key;
		}

		// выводит имя клиента для запроса в форме редактирования
		protected function get_client_name_Database($id,$no_edit=0){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = '<div'.(($no_edit==0)?' class="attach_the_client"':' class="dop__info"').' data-id="'.$row['id'].'">'.$row['company'].'</div>';
				}
			}else{
				$name = '<div'.(($no_edit==0)?' class="attach_the_client add"':' class="dop__info"').' data-id="0">Прикрепить клиента</div>';
			}
			return $name;
		}

		// выводит имя клиента для запроса в форме редактирования
		protected function get_client_name_simple_Database($id,$no_edit=0){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = '';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = ''.$row['company'];
				}
			}else{
				$name = 'Клиент не определён';
			}
			return $name;
		}

		// выводит имя клиента в заказе, по ссылке в url добавляется id клиента
		protected function get_client_name_link_Database($id){
			global $mysqli;		
			//получаем название клиента
			$query = "SELECT `company`,`id` FROM `".CLIENTS_TBL."` WHERE `id` = '".(int)$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$name = 'Клиент не прикреплён';
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$name = '<a data-id="'.$row['id'].'" '.((!isset($_GET['client_id']) || (isset($_GET['client_id']) && $_GET['client_id']!=$row['id']))?'href="'.$this->change_one_get_URL('client_id').$row['id'].'"':'').'>'.$this->str_reduce($row['company'],50).'</a>';
				}
			}
			return $name;
		}

		// обрезаем строку по количеству символов ...
		private function str_reduce($string,$num){
			// $num - ограничение по количеству символов
			if(mb_strlen($string, 'utf-8')>$num){
				// убираем html 
				// $string = strip_tags($str);
				// обрезаем
				$string = mb_substr($string, 0, $num);
				// убедимся, что текст не заканчивается восклицательным знаком, запятой, точкой или тире
				$string = rtrim($string, "!,.-");
				// находим последний пробел, устраняем его и ставим троеточие
				$string = substr($string, 0, strrpos($string, ' '));
				$string = $string.'...';
			}
			return $string;
		}

		// производим подмену одного из значений GET в URL
		private function change_one_get_URL($name){
			$f = 0;
			$str = '';

			foreach ($_GET as $key => $value) {
				if($key!=$name){
					if($f == 0){$str .= '?';}else{$str .= '&';}
					$str .= $key.'='.$value; 
					$f++;
				}
			}
			if($f == 0){$str .= '?';}else{$str .= '&';}
			$str .= $name.'='; 
			return $str;
		}		

		// получаем имя менеджера
		protected 	function get_manager_name_Database_Html($id,$no_edit=0){
		    global $mysqli;
		    $String = '<span'.(($no_edit==0)?' class="attach_the_manager add"':' class="dop_grey_small_info"').' data-id="0">Прикрепить менеджера</span>';
		   	$arr = array();
		    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $arr[$key] = $val;
				}
		    }		    
		    if(count($arr)){
		    	$String = '<span'.(($no_edit==0)?' class="attach_the_manager"':' class="dop_grey_small_info"').' data-id="'.$arr['id'].'">'.$arr['name'].' '.$arr['last_name'].'</span>';
		    }
		    return $String;
		}

		// получаем форму присвоения даты утверждения макета
		// в зависимости от уровня допуска для некоторых это календарь, а для менеджеров это кнопка
		protected function get_Position_approval_date($approval_date,$position_id){
			$html = '';
			if($this->user_access == 5){
				if(trim($approval_date)==""){
					$html .= '<input type="button" class="set_approval_date" data-id="'.$position_id.'" value="Макет утверждён">';
				}else{
					$html .= '<span class="greyText">'.$approval_date.'</span>';	
				}
				
			}else{
				$html .= '<input type="text" class="approval_date" value="'.$approval_date.'" data-id="'.$position_id.'">';
			}
			


			return $html;
		}

		// получаем имя сотрудника по id, если он указан
		protected 	function get_name_employee_Database_Html($id,$no_edit=0){
			if(isset($id) && trim($id)!=''){
			    global $mysqli;
			    $String = '<span'.(($no_edit==0)?' class="attach_the_manager add"':' class="dop_grey_small_info"').' data-id="0">Прикрепить менеджера</span>';
			   	$arr = array();
			    $query="SELECT * FROM `".MANAGERS_TBL."`  WHERE `id` = '".(int)$id."'";
			    $result = $mysqli->query($query)or die($mysqli->error);
			    if($result->num_rows>0){
					foreach($result->fetch_assoc() as $key => $val){
					   $arr[$key] = $val;
					}
			    }		    
			    if(count($arr)){
			    	$String = '<span data-id="'.$arr['id'].'">'.$arr['name'].' '.$arr['last_name'].'</span>';
			    }
			    return $String;
			}else{
				return 'не указан';
			}
		}
		
		// фильтрация позиций ЗАПРОСОВ по горизонтальному меню
		protected function requests_Template_recuestas_main_rows_Database($id){
						
			// ФИЛЬТРАЦИЯ ПО ВЕРХНЕМУ МЕНЮ 
			switch ($_GET['subsection']) {
				case 'no_worcked_men': // не обработанные
					//$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` = 'on_calculation_snab' OR `".RT_DOP_DATA."`.`status_snab` ='on_recalculation_snab' OR `".RT_DOP_DATA."`.`status_snab` = 'on_calculation')";
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
					

				case 'in_work': // в работе у менеджера
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;				

				case 'history':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND (`".RT_DOP_DATA."`.`status_snab` LIKE '%Расчёт от%') AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
				case 'denied':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` = 'tz_is_not_correct' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;

				case 'paused':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` LIKE '%pause%' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;

				case 'calk_snab':
					$where = "WHERE `".RT_MAIN_ROWS."`.`query_num` = '".$id."' AND `".RT_DOP_DATA."`.`status_snab` LIKE 'calculate_is_ready' AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;

				default:
					$where = "WHERE `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red' AND `".RT_MAIN_ROWS."`.`query_num` = '".$id."'  AND `".RT_DOP_DATA."`.`row_status` NOT LIKE 'red'";
					break;
			}


			global $mysqli;
			$query = "
				SELECT 
					`".RT_DOP_DATA."`.`id` AS `id_dop_data`,
					`".RT_DOP_DATA."`.`quantity`,	
					`".RT_DOP_DATA."`.`price_out`,		
					`".RT_DOP_DATA."`.`print_z`,	
					`".RT_DOP_DATA."`.`zapas`,	
					`".RT_DOP_DATA."`.`status_snab`,	
					DATE_FORMAT(`".RT_MAIN_ROWS."`.`date_create`,'%d.%m.%Y %H:%i:%s')  AS `gen_create_date`,
					`".RT_MAIN_ROWS."`.*,
					`".RT_LIST."`.`id` AS `request_id`,
					`".RT_LIST."`.`manager_id`,
					`".RT_LIST."`.`manager_id`,
					`".RT_LIST."`.`client_id`
					FROM `".RT_MAIN_ROWS."` 
					INNER JOIN `".RT_DOP_DATA."` ON `".RT_DOP_DATA."`.`row_id` = `".RT_MAIN_ROWS."`.`id`
					LEFT JOIN `".RT_LIST."` ON `".RT_LIST."`.`id` = `".RT_MAIN_ROWS."`.`query_num`
					".$where."
					ORDER BY `".RT_MAIN_ROWS."`.`type` DESC";
				// echo  $query.'<br><br>';
			
			$postion_arr = array();
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$postion_arr[] = $row;
				}
			}
			return $postion_arr;
		}

		// получаем информацию из cab_dop_data
		protected function get_cab_dop_data_position_Database($id){
			global $mysqli;
			$arr = array();
		    $query="SELECT `number_rezerv` FROM `".CAB_ORDER_DOP_DATA."`  WHERE `id` = '".(int)$id."'";
		    $result = $mysqli->query($query)or die($mysqli->error);
		    $str = '';
		    if($result->num_rows>0){
				foreach($result->fetch_assoc() as $key => $val){
				   $str = $val;
				}
		    }
		    return $str;
		}

		

		protected function get_all_services_names_Database(){
			global $mysqli;
			$arr = array();
			$query = "SELECT *
			 FROM `".OUR_USLUGI_LIST."`;";
			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;
				}
			}
			return $arr;
		}

		// отработка кнопок фильтров горизонтального меню
		protected function get_filter_list_Order($where){
			if (isset($_GET['subsection'])) {
				switch ($this->user_access) {
					case '1': // админы
						break;

					case '2': // бух
						break;

					case '4': // пр-во
						// фильтрация по заказу отсутствует
						break;

					case '5': // мен
						
						break;

					case '6': // доставка
						# code...
						break;

					case '7': // склад
						# code...
						break;

					case '8': // снабжение
						# code...
						break;

					case '9': // диз
						# code...
						break;
					
					default:
						# code...
						break;
				}
			}
		}

		protected function get_a_detailed_article_on_the_price_of_positions_Html(){
			// получаем название всех услуг
			$this->Services_list = $this->get_all_services_names_Database();


			$html = '';


			// собираем шапку таблицы
			$html .= '<table id="a_detailed_article_on_the_price_of_positions">';
			$html .= '<tr class="no_calc">';
			$html .= '<td></td><td colspan="7">Рассчитанная стоимость заказа</td><td class="postfaktum"></td><td colspan="4" class="postfaktum">Фактическая входящая стоимость</td><td></td>';
			$html .= '</tr>';

			$html .= '<tr class="no_calc">';
				// рассчитано ранее
				$html .= '<th>п</th>';
				$html .= '<th>Артикул/номенклатура</th>';
				$html .= '<th>перечень товаров и услуг</th>';
				$html .= '<th>тираж</th>';
				$html .= '<th>$ входящая</th>';
				$html .= '<th>%</th>';
				$html .= '<th>$ исходящая</th>';
				$html .= '<th>прибыль</th>';
				// то, что получилось по факту
				$html .= '<th class="postfaktum"></th>';
				$html .= '<th class="postfaktum">перечень товаров и услуг</th>';
				$html .= '<th class="postfaktum">тираж</th>';
				$html .= '<th class="postfaktum">$ входащая</th>';
				$html .= '<th class="postfaktum"></th>';
				$html .= '<th>комментарии СНАБ</th>';
			$html .= '</tr>';


			//////////////////////////////////////////////////////////
			//	объявляем переменные для подсчёта итого по заказу   //
			//////////////////////////////////////////////////////////
			$this->GlobItogo_price_in = 0;	// входящая за заказ
			$this->GlobItogo_price_out = 0; // исходящая за заказ
			$this->GlobItogo_price_pribl = 0; // прибыль за заказ
			$this->GlobItogo_price_in_postfaktum = 0;
			$this->GlobAdded_postfactum_class = '';
			//////////////////////////////////
			//	перебор заказа по позициям  //
			//////////////////////////////////
			$this->GlobAdded_postfactum_class  = 'td_shine'; // класс подсветки цен при появлении услуг добавленных в заказ
			foreach ($this->Positions_arr as $key => $position) {
				// считаем тираж для товара по позиции
				$this->PosGenTirage = $position['quantity']+$position['zapas'];


				//////////////////////////////////////////////////////////
				//	объявляем переменные для подсчёта итого по позиции  //
				//////////////////////////////////////////////////////////
				// сразу же записываем в них цены за тираж по товару
				$this->PositionItogo_price_in = $this->PosGenTirage*$position['price_in'];	// входящая  по позиции то, что было рассчитано клиенту
				$this->PositionItogo_price_in_postfaktum = $this->PosGenTirage*$position['price_in'];	// входящая  по позиции по факту то, что получилось
				$this->PositionItogo_price_out = $this->PosGenTirage*$position['price_out']; // исходящая по позиции
				$this->PositionItogo_price_pribl = $this->PositionItogo_price_out - $this->PositionItogo_price_in; // прибыль по позиции
				$this->PositionItogo_price_percent = $this->get_percent_Int($this->PositionItogo_price_in,$this->PositionItogo_price_out);

				// строка стоимости и описания товара
				$html .= '<tr class="tovar_provided" id="tovar_provided_'.($key+1).'">';
					// рассчитано ранее
					$rowspan = count($position['SERVICES'])+1;
					$html .= '<td rowspan="'.$rowspan.'">'.($key+1).'</td>';
					$html .= '<td rowspan="'.$rowspan.'">'.$position['name'].'</td>';
					$html .= '<td>товар</td>';
					$html .= '<td><span>'.$this->PosGenTirage.'</span>шт</td>';
					$html .= '<td><span class="service_price_in">'.$this->PositionItogo_price_in.'</span>р</td>';
					$html .= '<td><span>'.$this->PositionItogo_price_percent.'</span>%</td>';
					$html .= '<td><span class="service_price_out">'.$this->PositionItogo_price_out.'</span>р</td>';
					$html .= '<td><span class="service_price_pribl">'.$this->PositionItogo_price_pribl.'</span>р</td>';
					// то, что получилось по факту
					$html .= '<td class="postfaktum"></td>';
					$html .= '<td class="postfaktum">'.$position['name'].'</td>';
					$html .= '<td class="postfaktum"><span>'.$this->PosGenTirage.'</span>шт</td>';
					$html .= '<td class="postfaktum"><span class="service_price_in_postfactum">'.$this->PositionItogo_price_in.'</span>р</td>';
					$html .= '<td class="postfaktum"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';



				$html_added = ''; // услуги добавленные в заказ
				$added_postfactum_class = 'td_shine'; // класс подсветки цен при появлении услуг добавленных в заказ
				// перебираем прикреплённые услуги
				foreach ($position['SERVICES'] as $count => $service) {
					//////////////////////////////////////////////////////////
					//	объявляем переменные для подсчёта стоимости услуги  //
					//////////////////////////////////////////////////////////
					$this->Service_price_in = ($service['for_how']=='for_one')?$service['price_in']*$service['quantity']:$service['price_in'];// входящая  по услуге то, что было рассчитано клиенту
					$this->Service_price_out = $this->calc_summ_dop_uslug(array($service)); // исходящая по услуге
					$this->Service_price_pribl = $this->Service_price_out - $this->Service_price_in; // прибыль по услуге
					$this->Service_tir = ($service['for_how']=='for_one')?'<span>'.$service['quantity'].'</span>шт':'<span>  -  </span>'; // тираж по услуге
					$this->Service_Name = (isset($service['uslugi_id']))?$this->Services_list[$service['uslugi_id']]['name']:$service['uslugi_id']; // название услуги
					$this->Service_percent = $this->get_percent_Int($this->Service_price_in,$this->Service_price_out);

					/*	
						выводим кнопку отключения услуги только СНАБАМ, АДМИНАМ И АВТОРУ ДОБАВЛЕННОЙ УСЛУГИ, 
						а так же показываем кнопку у тех услуг которые не имеют автора - это услуги добавленные ещё в запросе
					*/
					if($service['author_id_added_services'] == 0 || $service['author_id_added_services'] == $this->user_id || $this->user_access == 1 || $this->user_access == 8){
						$this->Service_swhitch_On_Of = ((int)$service['on_of'] == 1)?'<span  data-id="'.$service['id'].'" class="on_of">+</span>':'<span  data-id="'.$service['id'].'" class="on_of minus">-</span>';
					}else{
						$this->Service_swhitch_On_Of = '';
					}
					


					switch ((int)$service['author_id_added_services']) {
							case 0: // для услуг добавленных из запроса
								$html .= '<tr class="provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html .= '<td>'.$this->Service_Name.'</td>';
									$html .= '<td>'.$this->Service_tir.'</td>';
									$html .= '<td><span class="service_price_in">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td><span>'.$this->Service_percent.'</span>%</td>';
									$html .= '<td><span class="service_price_out">'.$this->Service_price_out.'</span>р</td>';
									$html .= '<td><span class="service_price_pribl">'.$this->Service_price_pribl.'</span>р</td>';
									// то, что получилось по факту
									$html .= '<td class="postfaktum"></td>';
									$html .= '<td class="postfaktum">'.$this->Service_Name.'</td>';
									$html .= '<td class="postfaktum">'.$this->Service_tir.'</td>';
									$html .= '<td class="postfaktum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									$html .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html .= '<td></td>';
								$html .= '</tr>';

								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in += $this->Service_price_in;	// входящая  по позиции то, что было рассчитано клиенту
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								break;
							
							default:// если указан id того, кто добавил услугу, то услуга была добавлена в заказ
								$html_added .= '<tr class="not_provided '.(($service['on_of'] == 0)?'no_calc':'').'" data-id="'.$service['id'].'">';
									// рассчитано ранее
									$html_added .= '<td></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_in">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_out">0</span></td>';
									$html_added .= '<td><span class="postfaktum_non_calculate service_price_pribl">0</span></td>';
									// то, что получилось по факту
									$html_added .= '<td class="postfaktum"></td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_Name.'</td>';
									$html_added .= '<td class="postfaktum added_postfactum">'.$this->Service_tir.'</td>';
									$html_added .= '<td class="postfaktum added_postfactum"><span  class="service_price_in_postfactum">'.$this->Service_price_in.'</span>р</td>';
									$html_added .= '<td class="postfaktum">'.$this->Service_swhitch_On_Of.'</td>';
									$html_added .= '<td></td>';
								$html_added .= '</tr>';
								// если в просчёте не учавствует - continue
								if($service['on_of'] == 0){continue;}

								// добавляем класс подсветки цены
								$this->GlobAdded_postfactum_class = 'added_postfactum_class td_shine';
								$added_postfactum_class = 'added_postfactum_class td_shine';
								
								//////////////////////////////////////////////////
								//	добавляем стоимость услуги к цене за позицию
								//////////////////////////////////////////////////
								$this->PositionItogo_price_in_postfaktum += $this->Service_price_in;	// входящая  по позиции по факту то, что получилось
								$this->PositionItogo_price_out += $this->Service_price_out; // исходящая по позиции
								$this->PositionItogo_price_pribl += $this->Service_price_pribl; // прибыль по позиции
								
								break;
						}						
				}
				// добавляем услуги добавленные в заказ
				$html .= $html_added;
				// строка с кнопкой добавления услуги
				// добавляем строку пробел
				$html .= '<tr class="itogo_for_position_probel no_calc">';
					$html .= '<td colspan="8"></td>';
					$html .= '<td colspan="5" class="postfaktum"><input type="button" data-rowspan_id="tovar_provided_'.($key+1).'" data-id_dop_data="'.$position['id_dop_data'].'" class="add_service" name="add_service" value="Добавить"></td>';			
					$html .= '<td colspan="2"></td>';		
				$html .= '</tr>';

				// итого по позиции
				$html .= '<tr class="itogo_for_position">';
					$html .= '<td></td>';
					$html .= '<td>Итого по позиции</td>';
					$html .= '<td></td>';
					$html .= '<td></td>';
					// $ входащая итого
					$html .= '<td class="'.$added_postfactum_class.'"><span class="position_price_in">'.$this->PositionItogo_price_in.'</span>р</td>';

					$html .= '<td></td>';
					// исходящая итого
					$html .= '<td><span  class="position_price_out">'.$this->PositionItogo_price_out.'</span>р</td>';
					// прибыль итого
					$html .= '<td class="'.$added_postfactum_class.'"><span class="position_price_pribl">'.$this->PositionItogo_price_pribl.'</span>р</td>';
					$html .= '<td colspan="3"  style="background-color:#C7C7C7;text-align:right;"></td>';
					// заплатили по факту //// фходащая по факту
					$html .= '<td style="background-color:#C7C7C7;"><span class="'.$added_postfactum_class.'"><span  class="position_price_in_postfaktum">'.$this->PositionItogo_price_in_postfaktum.'</span>р</span></td>';
					$html .= '<td style="background-color:#C7C7C7;text-align:right;"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';

				//////////////////////////
				//	обсчитываем ИТОГО за заказ
				//////////////////////////
				$this->GlobItogo_price_in += $this->PositionItogo_price_in;	// входящая за заказ
				$this->GlobItogo_price_out += $this->PositionItogo_price_out; // исходящая за заказ
				$this->GlobItogo_price_pribl += $this->PositionItogo_price_pribl; // прибыль за заказ
				$this->GlobItogo_price_in_postfaktum += $this->PositionItogo_price_in_postfaktum; // входящая по факту
			}

			// если имеем разницу в постфактум выводим её
			$this->GlobItogo_price_in_difference = $this->GlobItogo_price_in-$this->GlobItogo_price_in_postfaktum;
			// собираем html разницы фактической и предусмотренной в расчёте стоимости
			$this->GlobItogo_price_in_difference_Html = ($this->GlobItogo_price_in_difference!=0)?'<span>'.$this->GlobItogo_price_in_difference.'</span>р':'';

			// добавляем строку пробел
			$html .= '<tr class="itogo_for_position_probel no_calc">';
				$html .= '<td colspan="15"></td>';					
			$html .= '</tr>';
			// добавляем ИТОГО по заказу
			$html .= '<tr class="itogo_for_position no_calc" id="itogo_order">';
					$html .= '<td></td>';
					$html .= '<td>Итого по заказу</td>';
					$html .= '<td></td>';
					$html .= '<td></td>';
					// $ входащая итого
					$html .= '<td class="'.$this->GlobAdded_postfactum_class.'"><span class="order_price_in">'.$this->GlobItogo_price_in.'</span>р</td>';

					$html .= '<td></td>';
					// исходящая итого
					$html .= '<td><span  class="order_price_out">'.$this->GlobItogo_price_out.'</span>р</td>';
					// прибыль итого
					$html .= '<td class="'.$this->GlobAdded_postfactum_class.'"><span  class="order_price_pribl">'.$this->GlobItogo_price_pribl.'</span>р<div class="minus">'.$this->GlobItogo_price_in_difference_Html.'</div></td>';
					$html .= '<td colspan="3"  style="background-color:#B1C370;text-align:right;
"></td>';
					// заплатили по факту //// фходащая по факту
					$html .= '<td style="background-color:#B1C370;
"><span class="'.$this->GlobAdded_postfactum_class.'"><span  class="order_price_in_postfactum">'.$this->GlobItogo_price_in_postfaktum.'</span>р</span></td>';
					$html .= '<td style="background-color:#B1C370;text-align:right;
"></td>';
					$html .= '<td></td>';
				$html .= '</tr>';

			$html .= '<table>';

			return $html;
		}

		// подсчёт процентов наценки
		protected function get_percent_Int($price_in,$price_out){
			$per = ($price_in!= 0)?$price_in:0.09;
			$percent = round((($price_out-$price_in)*100/$per),2);
			return $percent;
		}

		

		// получаем список пользователей по номеру подразделения
		protected function get_production_userlist_Database(){
			if(empty($this->userlist)){
				global $mysqli;
				$query = "SELECT * FROM `".MANAGERS_TBL."` WHERE  `access` = '".$this->group_access."'";
				$result = $mysqli->query($query) or die($mysqli->error);

				$this->userlist = array();
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$this->userlist[$row['id']] = $row;
					}
				}
			}
			return $this->userlist;
		}

		
		// запрос строк позиций по заказу
		protected function positions_rows_Database($order_num, $filters = 0){
			$arr = array();
			global $mysqli;
			$query = "SELECT *, `".CAB_ORDER_DOP_DATA."`.`id` AS `id_dop_data` 
			FROM `".CAB_ORDER_DOP_DATA."` 
			INNER JOIN ".CAB_ORDER_MAIN." ON `".CAB_ORDER_MAIN."`.`id` = `".CAB_ORDER_DOP_DATA."`.`row_id` 
			WHERE `".CAB_ORDER_MAIN."`.`order_num` = '".$order_num."'";
			// $query = "SELECT * FROM ".CAB_ORDER_MAIN." WHERE `order_num` = '".$order_id."'";
			

			$query .= $this->filter_position; 

			$result = $mysqli->query($query) or die($mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		// считает % оплаты
		protected function calculation_percent_of_payment($price, $payment){
			// $price - стоимость
			// $payment - уже заплаченная сумма
			return ($price!=0)?round($payment*100/$price,2):'0.00';
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

		// ответ о неверном адресе
		protected function response_to_the_wrong_address($method_template){
			// отправляем сообщение об ошибке
			$this->error_message_for_incorrect_URL();
			// собиравем сообщение для пользоватедля
			$message = 'Вы не должны были попасть на данную страницу, но что-то пошло не так и Вы всё таки здесь!!!<br>';
			$message .= 'Через 12 секунд Вы будете переадресованы на стартовую страницу кабинета <br>в соответствии с Вашим уровнем доступа.<br>';
			$message .= 'Сообщение о данном происшествии уже отправлено разработчикам. Спасибо.';
			// при выгрузке данного дива на страницу JS переадресует пользователя через 5 секунд по указанной в div ссылке
			$message .= '<div id="js_location" data-time="12000"><a href="http://'.$_SERVER['HTTP_HOST'].'/'.get_worked_link_href_for_cabinet().'">Перейти по ссылке</a></div>';
			// выводим сообщение
			echo $this->wrap_text_in_warning_message($message);
		}

		// оповещение об ошибке Разработчиков
		protected function error_message_for_incorrect_URL(){
			// получаем имя пользователя
			include_once './libs/php/classes/manager_class.php';
			$user_name = Manager::get_snab_name_for_query_String($this->user_access);
			
			$message = '';
			$message .= date('d-m-Y в H:i:s').'<br>';

			if(isset($_SESSION['come_back_in_own_profile'])){
				$user_name_real = Manager::get_snab_name_for_query_String($_SESSION['come_back_in_own_profile']);	
				$message .= 'Пользователь '.$user_name_real.', находясь под учётной записью: "'.$user_name.'" (ID: '.$this->user_id.', Access: '.$this->user_access.') перешёл по адресу http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' и наткнулся на ошибку.';
				$message .= 'Реальный уровень допуска пользователя: '.$this->get_user_access_Database_Int($_SESSION['come_back_in_own_profile']).'<br>';
				$message .= 'Реальный ID пользователя: '.$_SESSION['come_back_in_own_profile'].'<br>';
				$message .= isset($_GET)?'ID пользователя: '.$this->user_id.'<br>':'';	

			}else{
				$message .= 'Пользователь '.$user_name.' перешёл по адресу http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].' и наткнулся на ошибку.';
				$message .= 'Уровень допуска пользователя: '.$this->user_access.'<br>';
				$message .= 'ID пользователя: '.$this->user_id.'<br>';
				$message .= isset($_GET)?'ID пользователя: '.$this->user_id.'<br>':'';	
			}
			
			// отправка сообщения
			$this->error_message($message);
			
		}

		
		// отправка сообщения об ошибке
		protected function error_message($message,$subject = 'Error message' ,$from_email = 'os@apelburg.ru'){
			include_once './libs/php/classes/mail_class.php';
			$mailClass = new Mail();
			$mailClass->send('kapitonoval2012@gmail.com',$from_email,$subject,$message);	
		}



		// запрашивает из базы допуски пользователя
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

		//////////////////////////
		//	получение информации по спецификациям и договорам
		//////////////////////////
			// возвращает ссылку на спецификацию по строке заказа
			protected function get_specification_link($order_row){
				// если нет информации по спецификации 
				if($order_row['specification_num'] ==0 ){return 'не указано';}

				// получаем информацию по договору
				$agrement_arr = $this->get_info_for_agreement_Database($order_row['agreement_id']);
				if(empty($agrement_arr)){return 'не найдена информация по договору';}
					
				$html = '<a href="?page=agreement&section=agreement_editor&client_id='.$order_row['client_id'].'&agreement_id='.$order_row['agreement_id'].'&agreement_type='.$agrement_arr['agreement_type'].'&open=specification&specification_num='.$order_row['specification_num'].'">№ '.$order_row['specification_num'].' от '.$order_row['create_time'].'</a>';
				return $html;
			}

			// возвращает ссылку на договор по строке заказа
			protected function get_agreement_link($order_row){
				// если нет информации по договору
				if($order_row['agreement_id'] ==0 ){return 'не указан';}

				// получаем информацию по договору
				$agrement_arr = $this->get_info_for_agreement_Database($order_row['agreement_id']);
				if(empty($agrement_arr)){return 'не найден';}

				$html = '<a href="?page=agreement&section=agreement_editor&client_id='.$order_row['client_id'].'&agreement_id='.$order_row['agreement_id'].'&agreement_type='.$agrement_arr['agreement_type'].'&open=empty">'.$agrement_arr['agreement_num'].'</a>';
				return $html;
			}
	
			// запрос информации по договору
			protected function get_info_for_agreement_Database($agreement_id){
				global $mysqli;
				$agreement_info_arr = array();
				$query = "SELECT * FROM `".GENERATED_AGREEMENTS_TBL."` WHERE id = '".$agreement_id."'";
				// echo $query;
				$result = $mysqli->query($query) or die($mysqli->error);
					
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						$agreement_info_arr = $row;
					}
				}

				return $agreement_info_arr;
			}

		
   	}