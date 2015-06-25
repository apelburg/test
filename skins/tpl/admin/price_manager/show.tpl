<!-- begin skins/tpl/admin/price_manager/show.tpl --> 
<script>
function addRowsToTbl(idPart,settings){
   // если нужно вствить копию предпоследнего ряда
   //alert(idPart);
   var tbl = document.getElementById('tbl'+idPart);
   var rowsNum = parseInt(document.getElementById('rowsNum'+idPart).value);
   rowsNum = (rowsNum>15)?15:rowsNum ;
   addSomeRowsToTbl(tbl,rowsNum,settings);
  // alert(lastRow);
}

function addSomeRowsToTbl(tbl,num,settings){
   var rows = tbl.getElementsByTagName('TR');
   var preLastRow = rows[rows.length-2];
   var lastRow = rows[rows.length-1];
   //console.log(settings);
   for(var i=0;i<num;i++){
       if(settings && settings.preLast){
		   var row =  preLastRow.cloneNode(true);
		   preLastRow.parentNode.insertBefore(row,lastRow);
	   }
	   else{
	       var row =  lastRow.cloneNode(true);
		   if(settings && settings.clearCell) row.getElementsByTagName('TD')[settings.clearCell-1].innerHTML = '';
	       lastRow.parentNode.appendChild(row);
	   }
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
	   
       for(var i=0;i<rows.length;i++){
	       var cels = rows[i].getElementsByTagName('TD');
		   var celsData=[];
		   for(var j=0;j<cels.length-1;j++){
			   celsData[j] = cels[j].innerHTML;
		   }

		 
		   dataForBuffer.tbl_data[i] = celsData;
	   }
	   //console.log(dataForBuffer);
   }
   else if(data_obj.type=='dop_data'){
       var tbl = document.getElementById(data_obj.tblId);
	   var rows = tbl.getElementsByTagName('TR');
	   var dataForBuffer={};
	   dataForBuffer.print_type_id = data_obj.print_type_id;
       dataForBuffer.param_type = data_obj.param_type;
	   dataForBuffer.tbl_data = [];
	   
       for(var i=0;i<rows.length;i++){
	       var cels = rows[i].getElementsByTagName('TD');
		   var celsData=[];
		   for(var j=0;j<cels.length-1;j++){
			   celsData[j] = cels[j].innerHTML;
		   }

		 
		   dataForBuffer.tbl_data[i] = celsData;
	   }
	   //console.log(dataForBuffer);
   }
   else if(data_obj.type=='places'){
       var tbl = document.getElementById(data_obj.tblId);
	   var rows = tbl.getElementsByTagName('TR');
	   var dataForBuffer={};
	   dataForBuffer.tbl_data = [];
	   
       for(var i=0;i<rows.length;i++){
	       var cels = rows[i].getElementsByTagName('TD');
		   var celsData=[];
		   for(var j=0;j<cels.length-1;j++){
			   celsData[j] = cels[j].innerHTML;
		   }

		 
		   dataForBuffer.tbl_data[i] = celsData;
	   }
	   //console.log(dataForBuffer);
   }
   else if(data_obj.type=='sizes'){
       var tbl = document.getElementById(data_obj.tblId);
	   var rows = tbl.getElementsByTagName('TR');
	   var dataForBuffer={};
	   dataForBuffer.tbl_data = [];
	   
       for(var i=0;i<rows.length;i++){
	       var cels = rows[i].getElementsByTagName('TD');
		   var celsData=[];
		   // проходим по ячейкам таблицы
		   for(var j=0;j<cels.length;j++){
		        
			   var selectTagArr = cels[j].getElementsByTagName('SELECT');
			   if(selectTagArr && selectTagArr[0]){
			       
			       var selectTag =  selectTagArr[0];
				   //alert(selectTag.selectedIndex);
				   var val = selectTag.options[selectTag.selectedIndex].value;
			   }
		       else var val = cels[j].innerHTML;
		       celsData[j] = val;
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

</script>
<table class="mainWinTbl" border="1">
   <tr>
      <td width="150">
         <a href="?<?php echo addOrReplaceGetOnURL('subsection=price_editor'); ?>" class="<?php echo ((empty($_GET['subsection']) ||$_GET['subsection']=='price_editor')?'commoMenuCurrItem':''); ?> commoMenuItem">Прайсы</a>
      </td>
      <td width="150">
         <a href="?<?php echo addOrReplaceGetOnURL('subsection=colors_editor'); ?>" class="<?php echo ((!empty($_GET['subsection']) && $_GET['subsection']=='colors_editor')?'commoMenuCurrItem':''); ?> commoMenuItem">Цвета</a>
      </td>
      <td width="300">
         <a href="?<?php echo addOrReplaceGetOnURL('subsection=sizes_editor'); ?>" class="<?php echo ((!empty($_GET['subsection']) && $_GET['subsection']=='sizes_editor')?'commoMenuCurrItem':''); ?> commoMenuItem">Места нанесения - Размеры нанесения</a>
      </td>
       <td width="150">
         <a href="?<?php echo addOrReplaceGetOnURL('subsection=places_editor'); ?>" class="<?php echo ((!empty($_GET['subsection']) && $_GET['subsection']=='places_editor')?'commoMenuCurrItem':''); ?> commoMenuItem">Места нанесения</a>
      </td>
      <td width="250">
        <!-- <a href="?<?php echo addOrReplaceGetOnURL('subsection=places_prints_editor'); ?>" class="<?php echo ((!empty($_GET['subsection']) && $_GET['subsection']=='places_prints_editor')?'commoMenuCurrItem':''); ?> commoMenuItem">Места нанесения - Типы нанесения</a>-->
      </td>
      <td width="">
      </td>
   </tr>
</table> 
<table class="mainWinTbl" border="1">
  <tr>
    <td width="150" class="leftMenuTd">
       <?php echo implode('',$menu_arr); ?>
    </td>
    <td class="subContentTd">
       <?php echo $subsection_content; ?>
    </td>
  </tr>
</table> 
<!-- end skins/tpl/admin/price_manager/show.tpl -->