<style> .main_menu_tbl{ display:none; } </style>
<div class="agreement_setting_window" style="margin-top:150px;">
    <div style="width:650px;padding:30px 30px 70px 30px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
        <form method="GET">
        <!-- hidden -->
        <input type="hidden" name="query_num" value="<?php echo $_GET['query_num']; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
        <input type="hidden" name="address" value="<?php echo $_GET['address']; ?>">
        <input type="hidden" name="prepayment" value="<?php echo $_GET['prepayment']; ?>">
        <input type="hidden" name="section" value="<?php echo $section; ?>">
        <input type="hidden" name="dateDataObj" value="<?php echo htmlspecialchars($_GET['dateDataObj']); ?>">
        <?php if(isset($_GET['agreement_type'])) echo '<input type="hidden" name="agreement_type" value="'.$_GET['agreement_type'].'">'; ?>
        <?php if(isset($_GET['date'])) echo '<input type="hidden" name="date" value="'.$_GET['date'].'">'; ?>
        <?php if(isset($_GET['requisit_id'])) echo '<input type="hidden" name="requisit_id" value="'.$_GET['requisit_id'].'">'; ?>
        <?php if(isset($_GET['our_firm_id'])) echo '<input type="hidden" name="our_firm_id" value="'.$_GET['our_firm_id'].'">'; ?>
        <?php if(isset($_GET['agreement_id'])) echo '<input type="hidden" name="agreement_id" value="'.$_GET['agreement_id'].'">'; ?>
        <?php if(isset($_GET['agreement_exists'])) echo '<input type="hidden" name="agreement_exists" value="'.$_GET['agreement_exists'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_num'])) echo '<input type="hidden" name="existent_agreement_num" value="'.$_GET['existent_agreement_num'].'">'; ?>
        <?php if(isset($_GET['existent_client_agreement_num'])) echo '<input type="hidden" name="existent_client_agreement_num" value="'.$_GET['existent_client_agreement_num'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_date'])) echo '<input type="hidden" name="existent_agreement_date" value="'.$_GET['existent_agreement_date'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_expire_date'])) echo '<input type="hidden" name="existent_agreement_expire_date" value="'.$_GET['existent_agreement_expire_date'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_client_agreement'])) echo '<input type="hidden" name="existent_agreement_client_agreement" value="'.$_GET['existent_agreement_client_agreement'].'">'; ?>
        <?php if(isset($_GET['existent_agreement_spec_num'])) echo '<input type="hidden" name="existent_agreement_spec_num" value="'.$_GET['existent_agreement_spec_num'].'">'; ?>
        
        <!-- <input type="hidden" name="our_firm_id" value="<?php //echo $_GET['our_firm_id']; ?>">
        <input type="hidden" name="requisit_id" value="<?php //echo $_GET['requisit_id']; ?>">
        
         -->
        <div class="cap">описание для спецификации (не более 30 символов вместе с пробелами)</div>
        <hr />
        <!-- -->
        
        <div class="prepayment_row" style="margin-top:20px;"><a href="#" onclick="document.getElementById('short_description').value = this.innerHTML; return false;">Сборные сувениры</a></div>
        <div class="prepayment_row"><a href="#" onclick="document.getElementById('short_description').value = this.innerHTML; return false;">Ежедневники</a></div>
        <div class="prepayment_row"><a href="#" onclick="document.getElementById('short_description').value = this.innerHTML; return false;">Ручки</a></div>
        <div class="prepayment_row"><a href="#" onclick="document.getElementById('short_description').value = this.innerHTML; return false;">Календари</a></div>
        <div class="prepayment_row"><a href="#" onclick="document.getElementById('short_description').value = this.innerHTML; return false;">Текстиль</a></div>
        <input type="text" id="short_description" name="short_description" value="" style="width:630px; padding:4px 10px; margin-top:20px; font-size:20px;" maxlength="30">
        <!-- -->
        <div style="text-align:right;margin-top:10px;"><input type="submit" class="button" value="Далее"></div>
        </form>
    </div>
</div>