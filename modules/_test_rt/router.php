<style>
.tbl {border-collapse:collapse}
.tbl td{ border:#AAA solid 1px;}
.cap td{font-size:13px;padding-bottom:12px;}
.test_data{font-size:9px;}
.test_data_cap{ background-color:#CCCCCC;}
.in{ background-color: #CCFFFF;}
.out{ background-color: #FFFFCC;}
.grey{ background-color: #DDD;}
.top{ vertical-align:top}
</style>
<script type="text/javascript">
window.onload = function(){
   rt_calculator.init_tbl('rt_tbl');
}
print_r.count = 0;
function print_r(val/* array or object */){
	var str = scan(val);
	var win = window.open(null,'print_r'+(print_r.count++),'width=300,height=800',true);
	win.document.write(str);
	win.document.close();
	
	function scan(val){
		var str = '';
		for(var i in val){
			if(typeof val[i] != 'object') str += '[' + i + '] = [' + val[i] + ']<br>';
			if(typeof val[i] == 'object') str += '[' + i + '] => (' + scan(val[i]) + ')<br>';
		}
		return str;
	}
}

var rt_calculator = {
    // алгоритм действия калькулятора таблицы РТ: 1. При при наступлении события window.onload() считываются данные таблицы и сохраняются 
	// в переменной 2. специальным методом необходимые поля ввода устанавливаются редактируемыми и на необходимые поля навешиваются 
	// обработчики событий (это делается после полного считывания первоначальных данных, потому что для проведения расчета необходимы, данные
	// ДО и после ввода данных в поля ввода) 3. при возникновении событий приводящих к изменению данных в полях ввода происходит перерасчет
	// данных по конкретному ряду - вычисление разницы данных по данному ряду, внесение новых значений в итоговые суммы ряда , внесение новых
	// значений в итоговые суммы таблицы
    tbl:false,
    tbl_model:false,
	init_tbl:function(tbl_id){// метод запускаемый при наступлении события window.onload()
	                          // вызывает методы:
							  // collect_data - для создания модели таблицы
							  // set_editable_cells - для установки полей ввода
	    this.tbl = document.getElementById(tbl_id);
		//alert(this.tbl);
		this.collect_data();
		this.set_editable_cells();
	},
	set_editable_cells:function(){
	    // Этот метод устанавливает необходимым ячекам свойство contenteditable
		// и навешивает обработчик события onkeyup
		var tds_arr = this.tbl.getElementsByTagName('td');
	    for(var i in tds_arr){
		    if(tds_arr[i].getAttribute && tds_arr[i].getAttribute('editable')){
		        tds_arr[i].onkeyup = this.make_calculations;
		        tds_arr[i].setAttribute("contenteditable",true);
		    }
	    }
	}
	,
    make_calculations:function(e){
	    // Когда в ячейке(поле ввода) в результате каких то действий происходит изменение содержимого нужно вызывать этот метод
		// метод производит калькуляцию текущих данных, и вычисляет разность текущих данных с теми которые были до изменения 
		e = e || window.event;
		var cell = e.target || e.srcElement;
		// получаем id ряда
		var cur_tr = cell.parentNode;
		var row_id = cell.parentNode.getAttribute('row_id');
		
		//**print_r(rt_calculator.tbl_model[row_id]);
		
		// сохраняем итоговые суммы ряда до изменения ячейки
		var previos_data = {}
		previos_data['in_summ'] = rt_calculator.tbl_model[row_id]['in_summ'];
		previos_data['out_summ'] = rt_calculator.tbl_model[row_id]['out_summ'];
		
		// вносим изменённое значение в соответствующую ячейку this.tbl_model
		rt_calculator.tbl_model[row_id][cell.getAttribute('type')] = cell.innerHTML;
	    
		// производим пересчет ряда
		rt_calculator.calculate_row(row_id);
		
		//**print_r(rt_calculator.tbl_model[row_id]);
		
		// заменяем итоговые ссуммы в таблице HTML для данного ряда и для всей таблицы
		rt_calculator.change_html(cur_tr,row_id);

	}
	,
	calculate_row:function(row_id){
	    // метод который рассчитывает итоговые суммы конкретного ряда таблицы
		// методу передается id конкретного ряда таблицы, дальше метод выделят этот ряд в модели таблицы rt_calculator.tbl_model
		// и рассчитывает его
		var row = rt_calculator.tbl_model[row_id];
		row['in_summ'] = (row['quantity']*row['price_in'])+row['print_in_summ']+row['dop_uslugi_in_summ'];
		row['out_summ'] = row['quantity']*row['price_out']+row['print_out_summ']+row['dop_uslugi_out_summ'];
        row['delta'] = row['margin'] = row['out_summ']-row['in_summ'];
	}
	,
	change_html:function(cur_tr,row_id){
	    // метод который вносит изменения (итоги рассчетов в таблицу HTML)
		var tds_arr = cur_tr.getElementsByTagName('td');
		for(var j = 0;j < tds_arr.length;j++){
			if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
				if(tds_arr[j].getAttribute('type') == 'in_summ') tds_arr[j].innerHTML = rt_calculator.tbl_model[row_id]['in_summ'];
				if(tds_arr[j].getAttribute('type') == 'out_summ') tds_arr[j].innerHTML = rt_calculator.tbl_model[row_id]['out_summ'];
				if(tds_arr[j].getAttribute('type') == 'delta') tds_arr[j].innerHTML = rt_calculator.tbl_model[row_id]['delta'];
				if(tds_arr[j].getAttribute('type') == 'margin') tds_arr[j].innerHTML = rt_calculator.tbl_model[row_id]['margin'];
			}
		}
	}
	,
    collect_data:function(){
	    // метод считывающий данные таблицы РТ и сохраняющий их в свойство this.tbl_model 
	    this.tbl_model={};
	    var trs_arr = this.tbl.getElementsByTagName('tr');
	
		for(var i = 0;i < trs_arr.length;i++){
		    var row_id = trs_arr[i].getAttribute('row_id');
			if(row_id!=0){// row_id==0 у вспомогательных рядов их пропускаем
				var tds_arr = trs_arr[i].getElementsByTagName('td');
				for(var j = 0;j < tds_arr.length;j++){
					if(tds_arr[j].getAttribute && tds_arr[j].getAttribute('type')){
						if(!this.tbl_model[row_id]) this.tbl_model[row_id] = {};
						this.tbl_model[row_id][tds_arr[j].getAttribute('type')] = parseFloat(tds_arr[j].innerHTML);
					}
				}
			}
		}
		/**/
	    //print_r(this.tbl_model);
		return true;  
	},
    execute:function(){
	    alert(2);
	}
}
</script>
<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['_test_rt']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	
	// Данные расчетной таблицы хронятся в 3-х таблицах базы данных
	// 1-я таблица является родительской для 2-ой , 2-ая родительской для 3-ей
	// os__rt_main_rows  (RT_MAIN_ROWS)  1-я таблица основные ряды таблицы, содержащие описательные данные о товарной позиции (наименование, тип )
	// os__rt_dop_data   (RT_DOP_DATA)  2-я таблица содрежит данные о количестве и цене для каждого варианта расчета товарной позиции (к каждой строке из 1-ой таблицы может соответсвовать любое количество стро из 2-ой таблицы)
	// os__rt_dop_uslugi (RT_DOP_USLUGI) 3-я таблица содержит данные о расчетах о дополнительных услугах к ним относятся - нанесение, доставка, упаковка. Причем данные в таблице разделены на группы нанесение входит в группу print, все остальное в группу extra (к каждой строке из 2-ой таблицы может соответсвовать любое количество строк из 3-ей таблицы)
	// os__rt_print_data (RT_PRINT_DATA) 3-я таблица содержит данные о расчетах нанесения (к каждой строке из 2-ой таблицы может соответсвовать любое количество стро из 3-ей таблицы)
	
	
	// cat catalogs - товары из каталогов 
	// pol polygraphy - полиграфия 
	// ext externals (внешние) - товары не из каталогов 
	
	// поля таблицы 
	// row_status - ствтус ряда (светофор)
	// glob_status - статус ряда в глобальном масштабе (в работе, на расчете)
	//
	//
	//
	//

	function fetch_rows_from_rt($order_num){
	     global $mysqli;
		 
		 $rows = array();
		 
		 $query = "SELECT main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art AS art ,main_tbl.name AS item_name ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.in_price AS dop_t_in_price , dop_data_tbl.out_price AS dop_t_out_price , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.row_status AS row_status, dop_data_tbl.glob_status AS glob_status, dop_data_tbl.draft AS draft,
						  
						  dop_uslugi_tbl.id AS uslugi_id , dop_uslugi_tbl.dop_row_id AS uslugi_t_dop_row_id ,dop_uslugi_tbl.type AS uslugi_t_type ,
		                  dop_uslugi_tbl.glob_type AS uslugi_t_glob_type , dop_uslugi_tbl.quantity AS uslugi_t_quantity , dop_uslugi_tbl.price_in AS uslugi_t_price_in , dop_uslugi_tbl.price_out AS uslugi_t_price_out
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_DOP_USLUGI."` dop_uslugi_tbl ON  dop_data_tbl.id = dop_uslugi_tbl.dop_row_id
		          WHERE main_tbl.order_num ='".$order_num."' ORDER BY main_tbl.id";
				  
		 $result = $mysqli->query($query) or die($mysqli->error);
		 $multi_dim_arr = array();
	     while($row = $result->fetch_assoc()){
		     if(!isset($multi_dim_arr[$row['main_id']])){
			     $multi_dim_arr[$row['main_id']]['row_type'] = $row['main_row_type'];
				 $multi_dim_arr[$row['main_id']]['art'] = $row['art'];
				 $multi_dim_arr[$row['main_id']]['name'] = $row['item_name'];
			 }
			 //$multi_dim_arr[$row['main_id']]['uslugi_id'][] = $row['uslugi_id'];
			 if(isset($multi_dim_arr[$row['main_id']]) && !isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) &&!empty($row['dop_data_id'])){
			     $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']] = array(
																	'draft' => $row['draft'],
																	'row_status' => $row['row_status'],
																	'glob_status' => $row['glob_status'],
																	'quantity' => $row['dop_t_quantity'],
																	'price_in' => $row['dop_t_in_price'],
																	'price_out' => $row['dop_t_out_price']);
		    }
			if(isset($multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]) && !empty($row['uslugi_id'])){
			    $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['dop_uslugi'][$row['uslugi_t_glob_type']][$row['uslugi_id']] = array(
																									'type' => $row['uslugi_t_type'],
																									'quantity' => $row['uslugi_t_quantity'],
																									'price_in' => $row['uslugi_t_price_in'],
																									'price_out' => $row['uslugi_t_price_out'],
																									'uslugi_id' => $row['uslugi_id']
																									);
			}
			
			//print_r( $multi_dim_arr[$row['main_id']]['dop_data'][$row['dop_data_id']]['print_data']); echo "<br>";
			
			$rows[]= '<tr><td>'.implode('</td><td>',$row).'</td></tr>';
		   
		 }
	     return array($multi_dim_arr,$rows);
	 }
	 
	 $rows = fetch_rows_from_rt(10147);
	 
	 
	 $test_data = TRUE; // FALSE
	 // Построение таблицы РТ
	 // основой для рядов таблицы (тегов <tr>) является второй уровень массива $rows[0], тоесть это те данные которые лежат в $row['dop_data']
	 // каждый элемент этого массива выводится как отдельный ряд (в ходе обработки вывода могут быть добавлены дополнительные вспомагательные 
	 // элементы или изменен их порядок) 
	 // Тоесть таблица, количество (тегов <tr>), формируется на основе уровня $row['dop_data'], остальные уровни только влияют на результирующий
	 // вид таблицы
	 // элементы первого уровня выводятся один раз на весь блок элементов $row['dop_data']
	 // элементы третьего уровня $dop_row['dop_uslugi'] выводятся в виде ссылки внутри существующего ряда, если $dop_row['dop_uslugi'] существует
	 // РЯДЫ ДЛЯ РАСЧЕТА ВАРИАНТОВ!!! $dop_row['draft'] - для реализации функционала при котором позиция может иметь несколько расчетов, 
	 // при этом может быть что ни один из низ не выбран как окончательный, или один выбран а остальные остаются вариантами, введено понятие
	 // draft - (черновик,проект,эскиз) для маркирования таких рядов в базе данных - объявляется следующее правило - если позиция имеет один  
	 // расчетный ряд то он не может быть draft (он окончательный и участвует в дальнейших расчетах), если несколько они могут все быть draft,
	 // (и в дальшейших расчетах не участвуют) если один из низ не draft значит он окончательный (он окончательный и участвует в дальнейших
	 // расчетах), если позиция имеет несколько расчетных рядов, то не draft из них может быть только один
	 // если этот вариант останется рабочим удалить из таблицы поле final
	 //
	 // был еще вариант обратный маркировать окончательные финальные ряды - пока от него ушли
	 // ФИНАЛЬНЫЙ РЯД!!! $dop_row['final'] - для реализации функционала при котором позиция может иметь несколько расчетов, при этом может быть
	 // что ни один из низ не является финальным, или один может быть установлен как финальный и тогда он участвует в дальнейшем расчете заказа
	 ////
	  //echo '<pre>'; print_r($rows[0]); echo '</pre>';
	 $service_row[0] = array('quantity'=>0,'price_in'=>0,'price_out'=>0,'row_status'=>0,'glob_status'=>0);
	 foreach($rows[0] as $key => $row){
         // Проходим по первому уровню и определям некоторые моменты отображения таблицы, которые будут применены при проходе по второму
		 // уровню массива, ряды таблицы будут создаваться там
		 
		 // если товарная позиция имеет больше одного варианта расчета и один из этих вариатов расчета не является draft
		 // то мы должны переместить его вверх вывода а остальные вариатны вывести ниже
		 // при этом мы указываем $all_draft = FALSE; что не все ряды являются draft
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>';
		 $all_draft = FALSE;
		 if(count($row['dop_data'])>1){
		     $all_draft = TRUE;
			 foreach($row['dop_data'] as $dop_key => $dop_row){
				 if($dop_row['draft']!=1){
				      $all_draft = FALSE;
					  $row_to_lift_up[$dop_key] = $dop_row;
					  unset($row['dop_data'][$dop_key]);
				  }
			 }
			 // использование в данном случае не возможно потомучто этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
			 if(isset($row_to_lift_up)){
			      $row['dop_data']= $row_to_lift_up + $row['dop_data']; 
				  unset($row_to_lift_up);
			 }
		 }
		
		 
		 // здесь определяем выводить ли дополнительный вспомогательный пустой ряд сверху
		 // когда все остальные являются draft то выводим  т.е. (если не все draft то невыводим)
		 // использование в данном случае не возможно потомучто этот метод приводит к переиндексации ключей а ключи у нас содержат id ряда 
		 if($all_draft) $row['dop_data']= $service_row + $row['dop_data']; 
		 
		 //array_unshift($row['dop_data'],array('quantity'=>0,'price_in'=>0,'price_out'=>0,'row_status'=>0,'glob_status'=>0));
		 
		 // здесь мы определяем значение для атрибута rowspan тегов td которые будут выводится единой ячейкой для всей товарной позиции
		 $row_span = count($row['dop_data']);
		 $counter=0;
		 // echo '<pre>'; print_r($row['dop_data']); echo '</pre>---';
		 // Проходим в цикле по второму уровню массива($row['dop_data']) на основе которого стороится основной шаблон таблицы
	     foreach($row['dop_data'] as $dop_key => $dop_row){
		 
		     // работаем с информацией о дополнительных услугах определяя что будет выводиться и где
			 // 1. определяем данные описывающие варианты нанесения логотипа, они хранятся в $dop_row['dop_uslugi']['print']
			 if(isset($dop_row['dop_uslugi']['print'])){ // если $dop_row['dop_uslugi']['print'] есть выводим данные о нанесениях 
			     $summ_in = $summ_out = array();
			     foreach($dop_row['dop_uslugi']['print'] as $extra_data){
				     $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
					 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
				 }
				 $print_btn = 'печать '.count($dop_row['dop_uslugi']['print']); 
				 $print_in_summ = array_sum($summ_in);
			     $print_out_summ = array_sum($summ_out);
			     if($test_data) $print_open_data = print_r($dop_row['dop_uslugi']['print'],TRUE);
			 }
			 else{// если данных по печати нет то проверяем - не являются ли все ряды draft а данный ряд первым, если да то
			      // выводим пустое значение для пустого верхнего ряда, если нет выводим кнопку добавление нанесения
			     $print_btn = ($all_draft && $counter==0)? '' : 'печать +';
				 $print_in_summ = 0;
			     $print_out_summ = 0;
				 if($test_data) $print_open_data =($all_draft && $counter==0)? 0:'- печать 0-';
			 }
			 // 2. определяем данные описывающие варианты дополнительных услуг, они хранятся в $dop_row['dop_uslugi']['extra']
			 if(isset($dop_row['dop_uslugi']['extra'])){// если $dop_row['dop_uslugi']['extra'] есть выводим данные о дополнительных услугах 
			     $summ_in = $summ_out = array();
				 foreach($dop_row['dop_uslugi']['extra'] as $extra_data){
				     $summ_in[] = $extra_data['quantity']*$extra_data['price_in'];
					 $summ_out[] = $extra_data['quantity']*$extra_data['price_out'];
				 }
				 $dop_uslugi_in_summ = array_sum($summ_in);
			     $dop_uslugi_out_summ = array_sum($summ_out);
				 $dop_uslugi_btn = 'extra '.count($dop_row['dop_uslugi']['extra']);
                 if($test_data) $extra_open_data =  print_r($dop_row['dop_uslugi']['extra'],TRUE);
			 }
			 else{// если данных по дополнительным услугам нет то проверяем - не являются ли все ряды draft а данный ряд первым, если да 
			      // выводим пустое значение для пустого верхнего ряда, если нет выводим кнопку добавление дополнительных услуг
			     $dop_uslugi_in_summ = 0;
				 $dop_uslugi_out_summ = 0;
				 $dop_uslugi_btn = ($all_draft && $counter==0)? '' : 'extra +';
			     if($test_data) $extra_open_data =($all_draft && $counter==0)? 0:'- extra 0-';
			 }
			 
			 // подсчет сумм ряду
			 // 1. подсчитываем входящую сумму
			 $in_summ = $dop_row['quantity']*$dop_row['price_in']+$print_in_summ+$dop_uslugi_in_summ;
			 // 2. подсчитываем исходящую сумму 
			 $out_summ =  $dop_row['quantity']*$dop_row['price_out']+$print_out_summ+$dop_uslugi_out_summ;
			 
			 $delta = $out_summ-$in_summ; 
			 $margin = $out_summ-$in_summ;
			 
		     $cur_row  =  '';
		     $cur_row .=  '<tr row_id="'.$dop_key.'">';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'">'.$dop_key.'</td>':'';
		     $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'">'.$row['row_type'].'</td>':'';
			 $cur_row .=  ($counter==0)? '<td rowspan="'.$row_span.'" class="top">'.$row['art'].''.$row['name'].'</td>':'';
			 $cur_row .=  '<td>'.@$dop_row['draft'].' - '.$dop_key.'</td>
			               <td>'.$dop_row['row_status'].'</td>
			               <td type="quantity" editable="true">'.$dop_row['quantity'].'</td>
						   <td type="price_in" class="in" editable="true">'.$dop_row['price_in'].'</td>
						   <td type="price_out" class="out" editable="true">'.$dop_row['price_out'].'</td>
						   <td class="grey">'.$print_btn.'</td>';
                if($test_data)	 $cur_row .=  '<td class="test_data">'.$print_open_data.'</td>';
			    if($test_data)	 $cur_row .=  '<td type="print_in_summ" class="test_data in">'.$print_in_summ.'</td>';
			 $cur_row .=  '<td type="print_out_summ" class="out">'.$print_out_summ.'</td>
			               <td>'.$dop_uslugi_btn.'</td>';
			    if($test_data)	 $cur_row .=  '<td class="test_data">'.$extra_open_data.'</td>';
			    if($test_data)	 $cur_row .=  '<td type="dop_uslugi_in_summ" class="test_data in">'.$dop_uslugi_in_summ.'</td>';
			 $cur_row .=  '<td type="dop_uslugi_out_summ" class="out">'.$dop_uslugi_out_summ.'</td>
						   <td type="in_summ" class="in">'.$in_summ.'</td>
						   <td type="out_summ" class="out">'.$out_summ.'</td>
						   <td type="delta" >'.$delta.'</td>
						   <td type="margin" >'.$margin.'</td>';
			 $cur_row .=  '<td>'.$dop_row['glob_status'].'</td>';  
			 $cur_row .= '</tr>';
			 
			 // загружаем сформированный ряд в итоговый массив
		     $tbl_rows[]= $cur_row;
		     $counter++;
		 }
	 }
	 
	 echo '<table id="rt_tbl" class="tbl">
	          <tr class="cap">
	              <td>&nbsp;<a href="#" onclick="print_r(rt_calculator.tbl_model);">_</a></td>
				  <td>тип ряда</td>
				  <td width="100">наименование</td>
				  <td width="50">draft</td>
				  <td>статус ряда</td>
				  <td>кол-во</td>
				  <td>вход</td>
				  <td>выход</td>
				  <td>нанесение</td>';
	if($test_data)	 echo '<td class="test_data_cap">нанес подробн</td>';
	if($test_data)	 echo '<td class="test_data_cap">нанес вход</td>';		  
			echo '<td width="70">нанесение выход</td>
			      <td width="70">доп.усл</td>';
    if($test_data)	 echo '<td class="test_data_cap">доп.усл подробн</td>';
    if($test_data)	 echo '<td class="test_data_cap">доп.усл вход</td>';
			echo '<td width="70">доп.усл выход</td>
				  <td>сумма вход</td>
				  <td>сумма выход</td>
				  <td>дельта</td>
				  <td>маржинальность</td>
                  <td>статус</td>';              
	 echo     '</tr>'
	         .implode('',$tbl_rows).'
		   </table>';
	 
	 echo '<br>';
	 echo '<br>';
	 echo '<br>'; 
	 echo '<br>';
	 echo '<br>';
	 echo '<br>';
	
		/* 
		 ВАРИАНТ С ФИНАЛЬНЫМ РЯДОМ
		 
		// если товарная позиция имеет больше одного варианта расчета и один из этих вариатов расчета обозначен как финальный
		 // то мы должны переместить его вверх вывода а остальные вариатны вывести ниже
		 $final_row = FALSE;
		 foreach($row['dop_data'] as $dop_key => $dop_row){
		     if($dop_row['final']==1){
			      $final_row = $dop_row;
				  unset($row['dop_data'][$dop_key]);
		      }
		 }
		 if($final_row) array_unshift($row['dop_data'],$final_row);
		 // если товарная позиция имеет больше одного варианта расчета и ни один из этих вариатов расчета не обозначен как финальный 
		 // добавляем пустой ряд в начало строки
		 if(!$final_row && count($row['dop_data'])>1) array_unshift($row['dop_data'],array('quantity'=>0,'in_price'=>0,'out_price'=>0,'print_data'=>0));
		 $row_span = count($row['dop_data']);
		 $counter=0;
		 
		 
		  ВАРИАНТ С RT_PRINT_DATA
		 
		  $query = "SELECT main_tbl.id AS main_id ,main_tbl.type AS main_row_type  ,main_tbl.art AS art ,main_tbl.name AS item_name ,
		 
		                  dop_data_tbl.id AS dop_data_id , dop_data_tbl.row_id AS dop_t_row_id , dop_data_tbl.quantity AS dop_t_quantity , dop_data_tbl.in_price AS dop_t_in_price , dop_data_tbl.out_price AS dop_t_out_price , dop_data_tbl.discount AS dop_t_discount , dop_data_tbl.final AS final,  dop_data_tbl.draft AS draft,
						  
						  print_data_tbl.id AS print_id , print_data_tbl.dop_row_id AS print_t_dop_row_id ,print_data_tbl.type AS print_t_type ,
		                  print_data_tbl.quantity AS print_t_quantity ,print_data_tbl.price AS print_t_price 
		          FROM 
		          `".RT_MAIN_ROWS."`  main_tbl 
				  LEFT JOIN 
				  `".RT_DOP_DATA."`   dop_data_tbl   ON  main_tbl.id = dop_data_tbl.row_id
				  LEFT JOIN 
				  `".RT_PRINT_DATA."` print_data_tbl ON  dop_data_tbl.id = print_data_tbl.dop_row_id
		          WHERE main_tbl.order_num ='".$order_num."' ORDER BY main_tbl.id";
		 */
?>