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
   // alert(JSON.stringify(dataForBuffer));
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
        <?php echo $printsSelectInterface; ?>
    </td>
  </tr>
</table>  
</div>
<!-- end skins/tpl/admin/places_editor/show.tpl -->