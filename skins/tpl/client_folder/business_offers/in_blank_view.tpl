<!-- begin skins/tpl/client_folder/business_offers/in_blank_view.tpl --> 
<script type="application/javascript" language="javascript">
  $(document).ready(function(){ kpDisplayManager.initObj(); });
  var kpDisplayManager = {
     initObj:function(){ 

		 if(!document.getElementById('kpDisplaySettings') && !this.dataObj){
			 alert('Сохраненные ранее настройки не доступны');
			 this.dataObj = {};
		 }
		 else{
		     var inputNodes = document.getElementById('kpDisplayManagerBarTbl').getElementsByTagName('INPUT');
		     var ln = inputNodes.length;
		     if(document.getElementById('kpDisplaySettings').value !=''){
				 try {  
				     this.dataObj = JSON.parse(document.getElementById('kpDisplaySettings').value); 
				     // ставим галочки у НЕвыбранных позиций
					 outerLoop:
					 for(var i=0; i < ln; i++){ 
						 if(inputNodes[i].type=='checkbox'){ 
						     for(var prop in  this.dataObj){ 
							     if(inputNodes[i].name==prop) continue outerLoop;
							 } 
							//alert(inputNodes[i].checked);
							 inputNodes[i].checked=true; 
						 }
					 }	 
				 }
				 catch (e) { 
					alert('Сохраненные ранее настройки имеют некорректную структуру');
					this.dataObj = {};
				 }
				    
			 }
			 else{
			    // ставим галочки у ВСЕХ позиций
			    for(var i=0; i < ln; i++){ 
				   if(inputNodes[i].type=='checkbox')inputNodes[i].checked=true; 
			    }	 
			    this.dataObj = {};
			 }
		 }
		 // console.log('--');
		 // console.log(this.dataObj);
	  },
	  initKpConteiner:function(){ 

		 if(!document.getElementById('kpBlankConteiner')){
			 alert('не найден контейнер бланка КП');
			 return;
		 }
		 this.kpConteiner = document.getElementById('kpBlankConteiner');

	  },
	  setDisplayState:function(name,state){ 
	     var spansNodes = this.kpConteiner.getElementsByTagName('managedDisplay'); 
		 var ln = spansNodes.length;
		 for(var i=0;i< ln;i++){ //in spansArr
		     if(spansNodes[i].hasAttribute('name') && spansNodes[i].getAttribute('name')==name) spansNodes[i].style.display = state;
		 }
	  },
      saveChanges:function(checkBox){ 
	     if(!this.dataObj) this.initObj();
		 if(!this.kpConteiner) this.initKpConteiner();
	     if(checkBox.checked == true){
		    if(this.dataObj[checkBox.name]) delete this.dataObj[checkBox.name];
			this.setDisplayState(checkBox.name,'inline-block');//display, inline-block ,none | visibility,visible,collapse
		 }
		 if(checkBox.checked == false){
		    if(!this.dataObj[checkBox.name]) this.dataObj[checkBox.name] = 'hide';
		 	this.setDisplayState(checkBox.name,'none');
		 }
		 kpDisplayManager.saveChangesInBase();
	     // alert(this.dataObj);
		 // console.log(this.dataObj);
	  }
	  ,
      saveChangesInBase:function(){ 
	      if(!this.dataObj) this.initObj();
	      if(!document.getElementById('kpDisplaySettings_kpId')){
		     alert('Не удалось определить id КП');
			 return;
		  }
          show_processing_timer();
		  
	      $.ajax({
			type: 'POST',
			url: '',
			dataType: 'html',
			data: 'saveChangesInBase=1&dataJSON='+JSON.stringify(this.dataObj)+'&kp_id='+document.getElementById('kpDisplaySettings_kpId').value
		  })
		  .done(function(response) {
			 // console.log(response);
			 close_processing_timer();
		  })
		  .fail(kpDisplayManager.error);
		  return false;
	   },
	   error: function(jqXHR, textStatus) {
			console.log('Request failed: ' + textStatus);
	   }
    
  };
  

</script>

<table width="100%" border="0">
  <tr>
    <td><?php echo $in_blank_view; ?></td>
    <td valign="top">
    
          <div style="float:right;margin:20px 30px 0 0 ;"><a href="?page=client_folder&section=business_offers&query_num=<?php echo $query_num; ?>&client_id=<?php echo $client_id; ?>" class="someABtn">выйти в общий список КП</a></div>
        <table width="230" id="kpDisplayManagerBarTbl" style="margin-top:200px;" border="0">
          <tr>
            <td><label><input type="checkbox" name="art" onclick="kpDisplayManager.saveChanges(this);" />номер артикула</label></td>
          </tr>
         <!-- <tr>
            <td><label><input type="checkbox" name="characters" onclick="kpDisplayManager.saveChanges(this);" />характеристики (цвет, материал)</label></td>
          </tr>
          <tr>
            <td><label><input type="checkbox" name="itogo" onclick="kpDisplayManager.saveChanges(this);" />сумма позиции (итого)</label></td>
          </tr>-->
          <tr>
            <td><label><input type="checkbox" name="full_summ" onclick="kpDisplayManager.saveChanges(this);" />итоговая стоимость кп</label></td>
          </tr>
          <tr>
            <td><label><input type="checkbox" name="header" onclick="kpDisplayManager.saveChanges(this);" />шапка кп</label></td>
          </tr>
          <!-- <tr>
            <td><label><input type="checkbox" name="dop_uslugi" onclick="kpDisplayManager.saveChanges(this);" />дополнительные услуги</label></td>
          </tr> -->
        </table>
        <div style="margin:20px 0 0 20px;"><a href="#" onclick="return kpDisplayManager.saveChangesInBase();" class="someABtn">Сохранить</a></div>
        
    </td>
  </tr>
</table>
<!-- end skins/tpl/client_folder/business_offers/in_blank_view.tpl -->