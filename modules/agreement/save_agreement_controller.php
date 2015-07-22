<?php
    
    // print_r($_POST);
    /* echo '<pre>';
	print_r($_GET);
	echo '</pre>';
    echo '<br>';
    print_r($form_data);
	echo 1;
	exit;
	 */

	if(!$agreement_id)
	{
	    $our_firm = fetch_our_certain_firm_data($_GET['our_firm_id']);
		$our_firm_acting_manegement_face = our_firm_acting_manegement_face($_GET['our_firm_id']);
		
		$client_firm = get_client_requisites($_GET['requisit_id']);
        $client_firm_acting_manegement_face = get_client_requisites_acting_manegement_face($_GET['requisit_id']);
		
		$short_description = isset($_GET['short_description'])? $_GET['short_description']:'';
		
		$standart = true;
		$existent = false;
		$agreement_num = false;
		$date_arr = explode('.',$_GET['date']);
	    $date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
		$expire_date = date('Y-12-31');
		
		if(isset($_GET['agreement_exists']) && $_GET['agreement_exists'] == 'on'){
		    if(isset($_GET['existent_agreement_client_agreement']) && $_GET['existent_agreement_client_agreement'] == 'on'){
				$standart = false;
				$existent = true;
				$agreement_num =  'CL'.$_GET['existent_client_agreement_num'];
	
				$date_arr = explode('.',$_GET['existent_agreement_date']);
				$date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
				$date_arr = explode('.',$_GET['existent_agreement_expire_date']);
				$expire_date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
			
				
			}
			else{
			    $standart = true;
				$existent = true;
	            $agreement_num = $_GET['existent_agreement_num'];
				
				$date_arr = explode('.',$_GET['existent_agreement_date']);
				$date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];
				$date_arr = explode('.',$_GET['existent_agreement_expire_date']);
				$expire_date = $date_arr[2].'-'.$date_arr[1].'-'.$date_arr[0];

			
			}
		}
		
	    $agreement_id = add_new_agreement($client_id,$agreement_num,$_GET['agreement_type'],$existent,$standart,$_GET['our_firm_id'],$_GET['requisit_id'],$our_firm['comp_full_name'],$our_firm_acting_manegement_face,$client_firm['comp_full_name'],$client_firm_acting_manegement_face,$date,$expire_date,$short_description);
	}

	header('Location:?'.addOrReplaceGetOnURL('section=agreement_editor','agreement_id&existent_agreement_num&existent_agreement_date&existent_agreement_expire_date&existent_agreement_client_agreement').'&agreement_id='.$agreement_id);	

?>