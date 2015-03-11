<table width="100%" border="1">
  <tr>
    <td width="100"><div class=''>Кому:</div></td>
    <td><div id='mailSelectTo'></div></td>
  </tr>
  <tr>
    <td><div class=''>От:</div></td>
    <td><div id='mailSelectFrom'></div></td>
  </tr>
  <tr>
    <td><div class=''>Тема:</div></td>
    <td><div id='mailSubject'></div></td>
  </tr>
  <tr>
    <td><div class=''>Шаблон:</div></td>
    <td>
       <div class='mailSendWindow_tplBtn' type="recalculation">Перерасчет КП</div>
       <div class='mailSendWindow_tplBtn' type="new_kp_new_client">Новое КП / новый клиент</div>
       <div class='mailSendWindow_tplBtn' type="new_kp">Новое КП / постоянный клиент</div>
    </td>
  </tr>
  <tr>
    <td><div class=''></div></td>
    <td><div id='mailMessage'></div></td>
  </tr>
  <tr>
    <td><div class=''>Док-ты:</div</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td><div class=''></div</td>
    <td><button onclick="kpManager.sendKpByMailFinalStep();">send</button></td>
  </tr>
</table>