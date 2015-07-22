<div class="client_requisites_tbl" style="margin-top:80px;">
    <div style="position:relative;width:1000px;padding:30px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
      <div class="close_btn"><a href="?<?php echo addOrReplaceGetOnURL('section=long_term_agr_setting'); ?>">&#215;</a></div>
<form id="form" action="?<?php echo addOrReplaceGetOnURL('section=save_client_requisites'); ?>" name="form" method="POST">
<table class="client_form_table" style="margin-top:30px;">
  <tr>
    <td width="180" align="right">Компания</td>
    <td width="220">
       <input id="form_data_company" type="text" name="form_data[company]" value="Новые реквизиты">
       <input id="form_data_company" type="hidden" name="form_data[client_id]" value="<?php echo $_GET['client_id']; ?>">
    </td>
    <td width="40"align="right">Тел.</td>
    <td width="220">
       <input type="text" name="form_data[phone1]" value="<?php echo htmlspecialchars($form_data['phone1'],ENT_QUOTES); ?>">
    </td>
    <td width="40" align="right">Тел.</td>
    <td width="220">
       <input type="text" name="form_data[phone2]" value="<?php echo htmlspecialchars($form_data['phone2'],ENT_QUOTES); ?>"  >
    </td>
  </tr>
  <tr>
    <td align="right">Юридическое лицо</td>
    <td>
       <input type="text" name="form_data[comp_full_name]"  value="<?php echo htmlspecialchars($form_data['comp_full_name'],ENT_QUOTES); ?>">
    </td>
    <td align="right">Юр.адрес</td>
    <td>
       <input type="text" name="form_data[legal_address]"  value="<?php echo htmlspecialchars($form_data['legal_address'],ENT_QUOTES); ?>">
    </td>
    <td align="right">Почт.адрес</td>
    <td>
       <input type="text" name="form_data[postal_address]"  value="<?php echo htmlspecialchars($form_data['postal_address'],ENT_QUOTES); ?>">
    </td>
  </tr>
</table>
<div class="div_between_form_rows"></div>
<div style="text-align:center;font-weight:bold;">Руководитель</div>
<!--//////////////////////////////////////////////////////////////-->
<div id="chief_fields_div">
  <?php echo $chief_fields; ?>
</div>
<div class="cont_faces_delete_btn"><a href="#" onclick="return add_new_management_element('chief_fields_div');">+ добавить</a></div>
<div class="div_between_form_rows"></div>
<div style="text-align:center;font-weight:bold;">Бухгалтер</div>
<!--//////////////////////////////////////////////////////////////-->
<div id="accountant_fields_div">
  <?php echo $accountant_fields; ?>
</div>
<div class="cont_faces_delete_btn"><a href="#" onclick="return add_new_management_element('accountant_fields_div');">+ добавить</a></div>
<div class="div_between_form_rows"></div>
<div style="text-align:center;font-weight:bold;">Банковские реквизиты</div>
<div class="div_between_form_rows"></div>
<!--//////////////////////////////////////////////////////////////-->
<table class="client_form_table">
  <tr>
    <td align="right">ИНН</td>
    <td>
        <input type="text" name="form_data[inn]" value="<?php echo htmlspecialchars($form_data['inn'],ENT_QUOTES); ?>" >
    </td>
    <td align="right">КПП</td>
    <td>
        <input type="text" name="form_data[kpp]" value="<?php echo htmlspecialchars($form_data['kpp'],ENT_QUOTES); ?>" >
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td width="180" align="right">Банк</td>
    <td width="220">
        <input type="text" name="form_data[bank]"  value="<?php echo htmlspecialchars($form_data['bank'],ENT_QUOTES); ?>">
    </td>
    <td width="40" align="right">Адрес Банка</td>
    <td width="220">
        <input type="text" name="form_data[bank_address]" value="<?php echo htmlspecialchars($form_data['bank_address'],ENT_QUOTES); ?>" >
    </td>
    <td align="right">ОГРН</td>
    <td>
       <input type="text" name="form_data[ogrn]" value="<?php echo htmlspecialchars($form_data['ogrn'],ENT_QUOTES); ?>">
    </td>
  </tr>
  <tr>
    <td align="right">Расечтн.счет</td>
    <td>
        <input type="text" name="form_data[r_account]" value="<?php echo htmlspecialchars($form_data['r_account'],ENT_QUOTES); ?>" >
    </td>
    <td align="right">Кор.счет</td>
    <td >
        <input type="text" name="form_data[cor_account]" value="<?php echo htmlspecialchars($form_data['cor_account'],ENT_QUOTES); ?>" >
    </td>
    <td align="right">БИК</td>
    <td width="220">
        <input type="text" name="form_data[bik]" value="<?php echo htmlspecialchars($form_data['bik'],ENT_QUOTES); ?>" >
    </td>
  </tr>
</table>
<div class="div_between_form_rows"></div>
<!--//////////////////////////////////////////////////////////////-->
<table class="client_form_table" width="1000px">
  <tr>
    <td width="180" align="right">Дополнительная информация</td>
    <td>
         <textarea type="text" style="width:200px;" name="form_data[dop_info]" ><?php echo htmlspecialchars($form_data['dop_info'],ENT_QUOTES); ?></textarea>
    </td>
  </tr>
</table>
<div class="div_between_form_rows"></div>
<table class="client_form_table" style="margin:20px 0px 0px 260px;">
  <tr>
    <input type="hidden" name="form_data[id]" value="<?php echo $form_data['id']; ?>" >
    <td width="200" height="30" align="left">&nbsp;</td>
    <td width="200" align="center" height="30">
    <input type="hidden" name="requisit_id" value="<?php echo $requisit_id; ?>" >
    <input type="hidden" name="section" value="save_client_requisites" /><!--<a href="#" onclick="document.getElementById('form').submit();return false;">сохранить изменения</a>--></td>
    <td width="200" align="left" height="30"><!--<?php  //if(!isset($_GET['new'])){ echo '<a href="?'.addOrReplaceGetOnURL('section=agreement_editor').'">продолжить</a>'; } ?> --><button type="submit">продолжить</button></td>
  </tr>
</table>
</form>
</div>
</div>