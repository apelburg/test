<script>
function addInputField(selectNode){
   //alert(1);
   var id = parseInt(selectNode.options[selectNode.selectedIndex].value);
   var name =selectNode.options[selectNode.selectedIndex].innerHTML;
   
   var tbl = document.getElementById("containsDataTbl");
   var tbody = tbl.getElementsByTagName('TBODY')[0];
   //alert(tbody);
   
   var tr = document.createElement("TR");
   var td1 = document.createElement("TD");
   td1.innerHTML = id;
   
   var td2 = document.createElement("TD");
   td2.innerHTML = name;
   
   var td3 = document.createElement("TD");
   
   var td4 = document.createElement("TD");
   td4.innerHTML = '&#215';
   td4.className = 'pointer';
   td4.onclick = deleteRowFromTable;
   
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
   document.getElementById('dataBufferForDeleting').value+='|'+tr.firstChild.innerHTML;//
   var tbl = tr.parentNode;
   tbl.removeChild(tr);
   if(tbl.getElementsByTagName('TR').length < 2) tbl.style.display = 'none';
   
}

function placesEditorSendDataToBase(form,data_obj){
   //alert(data_obj.type);
   
   var tbl = document.getElementById(data_obj.tblId);
   var rows = tbl.getElementsByTagName('TR');
   var dataForBuffer={};

   dataForBuffer.menu_id = data_obj.menu_id;
   dataForBuffer.tbl_data = [];
   
   for(var i=0;i<rows.length;i++){
	   var cels = rows[i].getElementsByTagName('TD');
	   var celsData=[];
	   for(var j=0;j<cels.length-1;j++){
		   celsData[j] = cels[j].innerHTML;
	   }
	   dataForBuffer.tbl_data[i] = celsData;
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
        <?php echo $printsSelectInterface; ?>
    </td>
  </tr>
</table>  
</div>
<!-- end skins/tpl/admin/places_editor/show.tpl -->