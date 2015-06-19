<!-- begin skins/tpl/admin/price_manager/show.tpl --> 
<script>
function addRowsToTbl(type,key){
   //alert('tbl'+type+key);
   var tbl = document.getElementById('tbl'+type+key);
   var rowsNum = parseInt(document.getElementById('rowsNum'+type+key).value);
   rowsNum = (rowsNum>15)?15:rowsNum ;
   addSomeRowsToTbl(tbl,rowsNum);
  // alert(lastRow);
}

function addSomeRowsToTbl(tbl,num){
   var rows = tbl.getElementsByTagName('TR');
   var preLastRow = rows[rows.length-2];
   var lastRow = rows[rows.length-1];

   for(var i=0;i<num;i++){
       var row = preLastRow.cloneNode(true);
	   preLastRow.parentNode.insertBefore(row,lastRow);
   }
}

function addColsToTbl(type,key){
   //alert('tbl'+type+key);
   var tbl = document.getElementById('tbl'+type+key);
   var colsNum = parseInt(document.getElementById('colsNum'+type+key).value);
   colsNum = (colsNum>15)?15:colsNum ;
   addSomeColsToTbl(tbl,colsNum);
  // alert(lastRow);
}

function addSomeColsToTbl(tbl,num){
   //alert('tbl'+type+key);
   var rows = tbl.getElementsByTagName('TR');
  
   
   for(var i=0;i<rows.length;i++){
       var cels = rows[i].getElementsByTagName('TD');
	   var preLastCel = cels[cels.length-2];
	   var lastCel = cels[cels.length-1];
	   
       for(var j=0;j<num;j++){
	       var cel = preLastCel.cloneNode(true);
           preLastCel.parentNode.insertBefore(cel,lastCel);
		   
	   }
   }
}

function priseManagerSendDataToBase(form,data_obj){
   //alert(data_obj.type);
   if(data_obj.type=='price'){
       var tbl = document.getElementById(data_obj.tblId);
	   var rows = tbl.getElementsByTagName('TR');
	   var dataForBuffer={};
	   dataForBuffer.print_type_id = data_obj.print_type_id;
	   dataForBuffer.price_type = data_obj.price_type;
	   dataForBuffer.count = data_obj.count;
	   dataForBuffer.tbl_data = [];
	   
       for(var i=0;i<rows.length-1;i++){
	       var cels = rows[i].getElementsByTagName('TD');
		   var celsData=[];
		   for(var j=0;j<cels.length-1;j++){
			   celsData[j] = cels[j].innerHTML;
		   }

		 
		   dataForBuffer.tbl_data[i] = celsData;
	   }
	  

	   //console.log(dataForBuffer);
   }

   document.getElementById(data_obj.bufferId).value =  JSON.stringify(dataForBuffer);
   form.submit();
   
}
function deleteRowFromTable(cell){
   //alert('tbl'+type+key);
   var tr = cell.parentNode.parentNode;
   var tbl = tr.parentNode;
   tbl.removeChild(tr);
}
function deleteColFromTable(cell){
   var cell = cell.parentNode;
   var tr = cell.parentNode;
   var tbl = tr.parentNode;
   var rows = tbl.getElementsByTagName('TR');
  
   var cels = tr.getElementsByTagName('TD');
   for(var i=0;i<cels.length;i++){
   ///alert(cels[i]);
       if(cels[i]===cell) var cell_num = i;
   }
   //alert(cell_num);
   if(cell_num){
	   for(var i=0;i<rows.length;i++){
		   var cels = rows[i].getElementsByTagName('TD');
		   cels[cell_num].parentNode.removeChild(cels[cell_num]); 
	   }
   }
}
deleteColFromTable

</script>
<table class="mainWinTbl" border="1">
  <tr>
    <td width="150" >
       <?php echo implode('',$menu_arr); ?>
    </td>
    <td>
       <?php echo $razdel_content; ?>
    </td>
  </tr>
</table> 
<!-- end skins/tpl/admin/price_manager/show.tpl -->
 
