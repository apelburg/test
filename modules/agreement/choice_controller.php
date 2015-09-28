<?php

     //if($dateDataObj->doc_type=='oferta') 
	 $dateDataObj = json_decode($_GET['dateDataObj']);
	 if($dateDataObj->doc_type=='spec'){
		 // ЕСЛИ СОЗДАЕТСЯ СПЕЦИФИКАЦИЯ
		 // - получаем данные о созданных договорах и выводим списком реквизиты между которыми созданны договора
		 // также выводим кнопку "переход" для создания договоров, если договоров еще не было выводим только её
		 $long_term_agreements = fetch_client_agreements_by_type('long_term',$client_id);
		 
		 
		 if($long_term_agreements['results_num'] > 0)
		 {
			 $specification_section ='<div class="subsection">спецификацию для:</div>';
			 while($row =mysql_fetch_assoc($long_term_agreements['result']))
			 {
				 //echo '<pre>'; print_r($row); echo '</pre>';
				 
				 $basic = ($row['basic'])? 'border-left:3px solid #14AC40;' :'';
				 $checked = ($row['basic'])? 'checked' :'';
				 $specification_section .= '<div class="row" style="float:left;'.$basic.'"><label class="areement_set_label"><input type="radio" name="agreement_id" value="'.$row['id'].'" onclick="drop_radio_buttons(this);" '.$checked.'>Договора № '.$row['agreement_num'].' от '.$row['date'].'('.$row['our_comp_full_name'].' - '.$row['client_comp_full_name'].')</label></div><!--<div class="row" style="float:right;margin-top:15px;"><a href="?page=agreement&section=agreement_editor&client_id='.$client_id.'&agreement_id='.$row['id'].'&agreement_type=long_term&open=all">>></a></div>--><div class="clear_div"></div>';
	
			}
			 
			 
		 }
		 else if($long_term_agreements['results_num'] == 0)
		 {
			 $specification_section ='';
		 }
		 else
		 {
			 echo $long_term_agreements['result'];
		 }
     }
	 else if($dateDataObj->doc_type=='oferta'){
	     // ЕСЛИ СОЗДАЕТСЯ ОФЕРТА
		 // выводим списком реквизиты между которыми может быть создана офферта

	 }
	 
	 
	 
     include('./skins/tpl/agreement/choice.tpl');
?>
