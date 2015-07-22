<?php 

    if(isset($_GET['go_back']))
	{
	    $agreement_back_url = $_SESSION['agreement_back_url'];
		unlink($_SESSION['agreement_back_url']);
	    header('Location:'.$agreement_back_url);
		exit;
	}
	
	if(!isset($_SESSION['agreement_back_url'])) $_SESSION['agreement_back_url'] = $_SERVER['HTTP_REFERER'];

	$path = 'agreement/agreements_templates/long_term.tpl';
	
    if(isset($_POST['submit_ok']))
	{
		put_content($path,$_POST['page_content']);
	}
 
    $page_content = get_content($path); 
	$page_content = strip_tags($page_content,'<p><span><table><tr><td><tbody><div><strong><br /><br>');

     include ('../../skins/tpl/admin/order_manager/agreement/tinymse_js_block.html'); 

?>


<div style="margin:auto;width:1200px;">
    <div style="margin:10px 20px;">
        <button type="button"  onclick="location = '?<?php echo addOrReplaceGetOnURL('go_back=yes'); ?>';" style="cursor:pointer;">назад</button>
    </div>
    <div style="margin:0px 20px;">
    <form action="" method="post">
    <textarea id='elm1' name='page_content' style="width:1150px; height:600px;"><?php echo $page_content; ?></textarea>
    <input type='reset' value='сброс'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit_ok' value='изменить'>
    </form>
    </div>
</div>