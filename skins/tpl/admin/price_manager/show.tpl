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
   var lastRow = rows[rows.length-1];
   //alert('tbl'+type+key);
   for(var i=0;i<num;i++){
       row = lastRow.cloneNode(true);
       tbl.appendChild(row);
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
	   var lastCel = cels[cels.length-1];
	   
       for(var j=0;j<num;j++){
	       cel = lastCel.cloneNode(true);
           rows[i].appendChild(cel);
	   }
   }
}

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
 
