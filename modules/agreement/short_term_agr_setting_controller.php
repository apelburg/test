<?php

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // client_requisites
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$data_arr = fetch_all_client_requisites($client_id);

	
	$client_requisites=''; 
	
	if($data_arr['results_num'] > 0)
	{
        $result = $data_arr['result'];	
	    while($requisites = mysql_fetch_assoc($result))
		{
		    $checked1 = (!isset($checked1))? 'checked' :'';
			$client_requisites .= '<div class="row"><label class="areement_set_label"><input type="radio" name="requisit_id" value="'.$requisites['id'].'" '.$checked1.'>'.$requisites['company'].'</label></div>';	
		}
	}
	else{
	
	    $client_requisites = '<div>реквизиты еще не созданы</div>';
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// our_firms
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$our_firms ='';
	
	$data_arr = fetch_our_firms_data();
	//print_r($data_arr);
	if(!$data_arr['results_num'])
	{
	    $our_firms = $data_arr['result'];
	} 
	else if($data_arr['results_num'] > 0)
	{
        $result = $data_arr['result'];
	    while($item = mysql_fetch_assoc($result))
		{
		    $checked2 = (!isset($checked2))? 'checked' :'';
			$our_firms .= '<div class="row"><label class="areement_set_label"><input type="radio" name="our_firm_id" value="'.$item['id'].'" '.$checked2.'>'.$item['company'].'</label></div>';
		}
	}
	else{   $our_firms .= 'реквизиты еще не созданы'; }
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// varying part
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/*
	$varying_part = '<td><div class="add_requisits_link"><a href="?'.addOrReplaceGetOnURL('section=editing_client_requisites').'">добавить реквизиты</a></div>
                    </td>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
					    '.GETtoINPUT($_SERVER['QUERY_STRING'],array(''),array('section')).'<button type="submit" name="section" value="save_agreement">Создать</button>
                    </td>';
	*/
	$varying_part = '<td style="vertical-align:top;padding-top:10px;padding-bottom:25px;">
	                      <div class="add_requisits_link"><a href="?'.addOrReplaceGetOnURL('section=editing_client_requisites').'">добавить реквизиты</a></div>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
					    опция не доступна
                    </td>';
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// caption
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	

	$caption = '<div class="caption" style="margin:0px:text-align:center">Создать документ между следующими юр.лицами:</div>';
	
	include('../../skins/tpl/admin/order_manager/agreement/agr_setting.tpl');
?>
