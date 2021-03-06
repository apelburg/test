<script>
function addInputField(selectNode){
   //alert(1);
   var id = parseInt(selectNode.options[selectNode.selectedIndex].value);
   var name =selectNode.options[selectNode.selectedIndex].innerHTML;
   
   var tbl = document.getElementById("containsDataTbl");
   var tbody = tbl.getElementsByTagName('TBODY')[0];
   //alert(tbody);
   
   var tr = document.createElement("TR");
   var td0 = document.createElement("TD");
   td0.innerHTML = "<input type='checkbox' checked>";
   
   var td1 = document.createElement("TD");
   td1.innerHTML = id;
   
   var td2 = document.createElement("TD");
   td2.innerHTML = name;
   
   var td3 = document.createElement("TD");
   
   var td4 = document.createElement("TD");
   td4.innerHTML = '&#215';
   td4.className = 'pointer';
   td4.onclick = deleteRowFromTable;
   
   tr.appendChild(td0);
   tr.appendChild(td1);
   tr.appendChild(td2);
   tr.appendChild(td3);
   tr.appendChild(td4);
   if(tbody){
       tbody.appendChild(tr);
	   tbl.appendChild(tbody);
   }
   else tbl.appendChild(tr);
   
   tbl.style.display = 'block';
   if(tbody) tbody.style.display = 'block';
   
   return false;
}

function _deleteRowFromTable(e){
   e = e || window.event;
   var cell = e.target || e.srcElement;
   var tr = cell.parentNode;
   document.getElementById('dataBufferForDeleting').value+='|'+tr.firstChild.nextSibling.innerHTML;//
   var tbl = tr.parentNode;
   tbl.removeChild(tr);
   if(tbl.getElementsByTagName('TR').length < 2) tbl.style.display = 'none';
   
}

function deleteRowFromTable(e){
   e = e || window.event;
   var cell = e.target || e.srcElement;
   var tr = cell.parentNode;
   var table = tr.parentNode;
   var id = tr.firstChild.nextSibling.innerHTML;
   //проверяем таблицу если в таблице осталось еще строчка с данным типом нанесения то не будем её удалять(не добавляем в dataBufferForDeleting)
   var entriesCount = 0; // считаем количество вхождений если будет больше 1 не удаляем
   var rows = table.getElementsByTagName('TR');
   for(var i=1;i<rows.length;i++){
	   var cels = rows[i].getElementsByTagName('TD');
	   for(var j=0;j<cels.length-1;j++){
		   if(j==1){
		       //alert(cels[j].innerHTML+' == '+id);
		       if(cels[j].innerHTML == id) entriesCount++; 
		   }
	   }
   }
	
   if(entriesCount==1) document.getElementById('dataBufferForDeleting').value+='|'+id;//
   table.removeChild(tr);
   if(table.getElementsByTagName('TR').length < 2) table.style.display = 'none';
   
}

function placesEditorSendDataToBase(form,data_obj){
   //alert(data_obj.type);
   
   var tbl = document.getElementById(data_obj.tblId);
   var rows = tbl.getElementsByTagName('TR');
   var dataForBuffer={};

   dataForBuffer.menu_id = data_obj.menu_id;
   dataForBuffer.tbl_data = [];
   
   loop:
   for(var i=1;i<rows.length;i++){
	   var cels = rows[i].getElementsByTagName('TD');
	   var celsData=[];
	   for(var j=0;j<cels.length-1;j++){
		   if(j==0){
		       if(cels[j].getElementsByTagName('INPUT')[0].checked == false) continue loop;
			   continue; 
		   }
		   else celsData.push(cels[j].innerHTML);
	   }
	   if(celsData.length>0)dataForBuffer.tbl_data.push(celsData);
   }
   document.getElementById(data_obj.bufferId).value =  JSON.stringify(dataForBuffer);
   form.submit();
}

</script>
<!-- begin skins/tpl/admin/places_editor/show.tpl --> 
<div class="placesEditor">
<table class="mainWinTbl" border="1">
  <tr>
    <td width="430" class="leftMenuTd">
       <?php echo $menuHTML; ?>
    </td>
    <td class="subContentTd">
        <?php echo $placesSelectInterface; ?>
        <div class="disclaimer">!!! Автоматический сервис присваивания существующих нанесений всем артикулам в разделе будет работать исправно до того момента пока какому либо артикулу в разделе не будет присвоено оригинальное место нанесения. В результате такой ситуации автоматическим сервисом будет присвоено это место нанесение все остальным артикулам в разделе. Может быть другая ситуация - когда у какого-нибудь артикула будет удалено определенное место нанесения, но оно снова будет присвоено автоматической системой при очередном её срабатываниию.<br>Получается если даже создать для каждого раздела какой-либо набор "обязательных" мест нанесений, эти места нанесения после срабатывания будут иметь все артикулы данного радела, и те у которых эти места не должны быть. Тоесть добиться чтобы у какого-то определенного артикула не было "обязательного" места нанесения не получится.<br>Можно предположить что есть какие-то разделы в которых лежат товары одинаковые в плане нанесения, к этим разделам наверно можно будет применять автоматическое присваивание.<br>Другой вариант присваивать места нанесения артикулу при переводе его из раздела в раздел, удалять старые места нанесения, и навешивать ему обязательные места нанесения нового раздела. Это позволит обрабатывать как все новые входящие товары так и товары которые будут переносится из раздела в раздел, либо добавляться еще в какие-то кроме единственного раздела.<br>Также нужен интерфейс дающий возможность присваивать оригинальные места нанесения конкретному артикулу, а также группе артикулов.
<br />
<br />
<br />
<br />
ПРИНЦИП ПРИСВАИВАНИЯ ТИПА НАНЕСЕНИЯ ДЛЯ АРТИКУЛА:<br />
1-ый вариант - Нанесение может быть присвоено артикулу напрямую<br />
2-ой вариант - Артикулу присваивается место нанесения (например грудь или рукав) а место нанесения имеет прикрепленные к нему типы нанесений<br />
<br />
<br />
При запуске калькулятора система смотрит есть ли у данного артикула совпадения между нанесениями прикрепленными напрямую и прикрепленными к "местам нанесения".<br />
 - если система находит совпадения, то те которые прикреплены напрямую игнорируются<br />
 - если после проверки остаются какие-либо непроигнорированные нанесения прикрепленные напрямую, создается место нанесения "Стандартно" и они добавляются в него. В Итоге в калькуляторе в добавление к имеющимся у артикула местам нанесения выводится пункт "Стандартно" в котором указываются непроигнорированные "оригинальные" нанесения(из числа прикрепленных напрямую) более нигде не указанные для данного артикула<br />
 - если у артикула нет присвоенных мест нанесения, то создается место нанесения "Стандартно" и все типы нанесений присвоенные напрямую добавляются в него и он единственный выводится в калькуляторе.<br />
 - если у артикула нет ни прикрепленных напрямую нанесений, ни присвоенных "мест нанесений", или если это не артикул из каталога то создается место нанесения "Стандартно" и в нем выводятся все имеющиеся у нас типы нанесений<br />
 <br />
 <br />
 Отображение нанесений в карточке товара<br />
 - 1-ая строка. общая строка всех типов нанесений ассоциированных с данным артикулом (либо присвоенных напрямую, через места нанесения) отображается для всех<br />
 - 2-ая строка. строка присвоенных напрямую типов нанесения<br />
 - 3-ая строка. строка присвоенных мест нанесения и присвоенных к ним типов нанесения<br />
 <br />
 <br />
 НУЖЕН ЕЩЁ СЕРВИС ПРИСВАИВАНИЯ ТИПОВ НАНЕСЕНИЯ НАПРЯМУЮ НА ПОДОБИИ "УПРАВЛЕНИЕ МЕСТАМИ НАНЕСЕНИЯ"
 
 

</div>
    </td>
  </tr>
</table>  
</div>
<!-- end skins/tpl/admin/places_editor/show.tpl -->