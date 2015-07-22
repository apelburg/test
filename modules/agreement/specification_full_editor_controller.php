<?php 

    
	
    $agreement = fetch_agreement_content($agreement_id);
   
    $date_arr = explode('-',$agreement['date']);
	$agreement_year_folder = $date_arr[0];
	
	$our_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'our_requisit_id','coll'=>'id','val'=>$agreement_id));
	$client_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'client_requisit_id','coll'=>'id','val'=>$agreement_id));
	
	$path = 'data/agreements/'.$client_id.'/'.$agreement_year_folder.'/'.$_GET['agreement_type'].'/'.$our_requisit_id.'_'.$client_requisit_id.'/specifications/'.$_GET['specification_num'].'.tpl';
	
    if(isset($_POST['submit_ok']))
	{
		put_content($path,$_POST['page_content']);
	}
	
	
  
 
    $page_content = get_content($path); 

?>

<?php include ('../../skins/tpl/admin/order_manager/agreement/tinymse_js_block.html'); ?>
<div style="margin:auto;width:1200px;">
    <div style="margin:10px 20px;">
        <button type="button" onclick="location = '/admin/order_manager/?<?php echo addOrReplaceGetOnURL('section=agreement_editor'); ?>';" style="cursor:pointer;">назад</button>
    </div>
    <div style="margin:0px 20px;">
    <form action="" method="post">
    <textarea id='elm1' name='page_content' style="width:1150px; height:600px;"><?php echo $page_content; ?></textarea>
    <input type='reset' value='сброс'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit_ok' value='изменить'>
    </form>
    </div>
</div>
