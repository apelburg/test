<table class="letterEditWindowTbl" width="100%">
  <tr>
    <td width="70" class='fieldName'>Кому:</td>
    <td><div id='mailSelectTo'></div></td>
  </tr>
  <tr>
    <td class='fieldName'>От:</td>
    <td><div id='mailSelectFrom'></div></td>
  </tr>
  <tr>
    <td class='fieldName'>Тема:</td>
    <td><div id='mailSubject'></div></td>
  </tr>
  <tr>
    <td class='fieldName'>Шаблон:</td>
    <td>
       <div class='mailSendWindow_tplBtn' type="recalculation">Перерасчет КП</div>
       <div class='mailSendWindow_tplBtn' type="new_kp_new_client">Новое КП / новый клиент</div>
       <div class='mailSendWindow_tplBtn' type="new_kp">Новое КП / постоянный клиент</div>
    </td>
  </tr>
  <tr>
    <td class='fieldName'></td>
    <td><div id='mailMessage'></div></td>
  </tr>
  <tr>
    <td class='fieldName'>Док-ты:</td>
    <td><input type="checkbox"/ checked="checked"><div id='attachedKpFile' class='attachedKpFileName'></div></td>
  </tr>
  <tr>
    <td class='fieldName'></td>
    <td><input type="checkbox"/><div id='' class='attachedKpFileName'>Apelburg_порядок_проведения_заказа.pdf</div></td>
  </tr>
  <tr>
    <td class='fieldName'></td>
    <td><input type="checkbox"/><div id='' class='attachedKpFileName'>Apelburg_презентация_компании.pdf</div></td>
  </tr>
  <tr>
    <td><div class=''></div></td>
    <td><button onclick="kpManager.sendKpByMailFinalStep();">send</button></td>
  </tr>
</table>