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
	  saveChangesRadio:function(radio){ 
	  
	      for(var i =0 ;i < 3; i++){
		     var staus = (i == radio.value)?'inline-block':'none';
			 var staus2 = (radio.value != 2)?'table-row':'none';
		     document.getElementById('itogo_display_setting_1_'+i).style.display=staus;
			 document.getElementById('itogo_display_setting_2_'+i).style.display=staus;
			 
			
			 for(var j =0 ;j < 100; j++){	 
				    if(document.getElementById('metod_display_setting_'+j+'1_'+i)) document.getElementById('metod_display_setting_'+j+'1_'+i).style.display=staus;
					if(document.getElementById('metod_display_setting_'+j+'2_'+i)) document.getElementById('metod_display_setting_'+j+'2_'+i).style.display=staus;
					if(document.getElementById('metod_display_setting_'+j+'3')) document.getElementById('metod_display_setting_'+j+'3').style.display=staus2;
					 for(var s =0 ;s < 6; s++){	
			            if(document.getElementById('metod_display_setting_'+j+s+'4')) document.getElementById('metod_display_setting_'+j+s+'4').style.display=staus2;
					}
					
			 }/**/
		  }
		 
		 
		 
		 // document.getElementById('itogo_display_setting_3_'+radio.value).style.display='inline-block';
		 // document.getElementById('itogo_display_setting_4_'+radio.value).style.display='inline-block';
		  
	      show_processing_timer();
	      $.ajax({
			type: 'POST',
			url: '',
			dataType: 'html',
			data: 'saveChangesRadioInBase=1&val='+radio.value+'&kp_id='+document.getElementById('kpDisplaySettings_kpId').value
		  })
		  .done(function(response) {
			 // console.log(response);
			 close_processing_timer();
		  })
		  .fail(kpDisplayManager.error);
		  return false;
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

$(document).on('keyup', '.saveKpPosDescription', function(event) {
    // первым параметром перелаём название функции отвечающей за отправку запроса AJAX
    // вторым параметром передаём объект к которому добавляется класс saved (класс подсветки)
    timing_save_input2('save_pos_status',$(this));
});

function save_pos_status(obj){// на вход принимает object input
    var id = obj.attr('pos_id');
	//alert(obj);
	console.log(obj.context.outerText);
	 $.ajax({
			type: 'POST',
			url: '',
			dataType: 'html',
			data: 'saveKpPosData=posDescription&val='+Base64.encode(obj.context.outerText)+'&id='+id
		    })
		    .done(function(response) {
			  console.log(response);
			  obj.removeClass('saved');
		    });
}


// функция тайминга
function timing_save_input2(fancName,obj){

    //если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
    if(!obj.hasClass('saved')){
        window[fancName](obj);
        obj.addClass('saved');                  
    }else{// стоит запрет, проверяем очередь по сейву данной функции        
        if(obj.hasClass(fancName)){ //стоит в очереди на сохранение
            // стоит очередь, значит мимо... всё и так сохранится
        }else{
            // не стоит в очереди, значит ставим
            obj.addClass(fancName);
            // вызываем эту же функцию через n времени всех очередей
            var time = 20000;
            $('.'+fancName).each(function(index, el) {
                console.log($(this).html());
                
                setTimeout(function(){timing_save_input(fancName,$('.'+fancName).eq(index));// обнуляем очередь
        	if(obj.hasClass(fancName)){obj.removeClass(fancName);}}, time); 
            });         
        }       
    }
}
</script>

<table width="100%" border="0">
  <tr>
    <td width="750"><?php echo $in_blank_view; ?></td>
    <td valign="top">
    
         <div style="float:right;margin:20px 30px 0 0 ;"><a href="?page=client_folder&section=business_offers&query_num=<?php echo $query_num; ?>&client_id=<?php echo $client_id; ?>" class="someABtn">выйти в общий список КП</a></div>
        <div style=" position:fixed;top:230px; border:#0000CC 0px solid;">
        <table width="550" id="kpDisplayManagerBarTbl" style="margin-top:0px;" border="0">
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
         <table width="550" id="kpDisplayManagerBarTbl" style="margin-top:50px;font-size:11px;" border="0">
           <tr>
            <td><label><input type="radio" name="deatils_tpl" onclick="kpDisplayManager.saveChangesRadio(this);" value="0" <?php echo ($display_setting_2==0)?'checked':'';?> /><ul><li>Cумма нанесений точно по прайсу,</li><li>Общая стоимость нанесения: - вся стоимость нанесения с коэффициэнтами и надбавками</li><li>Общая стоимость доп услуг - только доп услуги</li></ul></label></td>
          </tr>
           <tr>
            <td><label><input type="radio" name="deatils_tpl" onclick="kpDisplayManager.saveChangesRadio(this);" value="1" <?php echo ($display_setting_2==1)?'checked':'';?> /><ul><li>Cумма нанесений точно по прайсу</li><li>Общая стоимость нанесения: - вся стоимость нанесения без с коэффициэнтов и надбавок</li><li>Общая стоимость доп услуг - коэффициэнты и надбавки печати  и доп услуги</li></ul></label></td>
          </tr>
           <tr>
            <td><label><input type="radio" name="deatils_tpl" onclick="kpDisplayManager.saveChangesRadio(this);" value="2" <?php echo ($display_setting_2==2)?'checked':'';?> /><ul><li>Cумма нанесений прайс плюс коэффициэнт печати и коэффициэнт цвета</li><li>Общая стоимость нанесения: - включает в себя блок который относится к печати логотипа(вся стоимость плюс коэффициэнт печати и коэффициэнт цвета)</li><li>Общая стоимость доп услуг - включает в себя все допуслуги из 3 блок</li></ul></label></td>
          </tr>
        </table>
        </div>
        
    </td>
  </tr>
</table>
<!-- end skins/tpl/client_folder/business_offers/in_blank_view.tpl -->