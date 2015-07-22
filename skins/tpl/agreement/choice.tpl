<script type="text/javascript" src="libs/js/calendar.js"></script>
<style>

.calendarLaunchButtonContainer{
   position:relative;
}

.calendarMainContainer{
   position:absolute;
   top:-180px;
   left:40px;
}
.calendarContainer{
   width:160px;/**/
   padding:5px;
   border: #CCCCCC solid 3px;/**/
   background-color:#FFFFFF;
}

.calendarTable{
   border-collapse:collapse;
   font-family:Arial, Helvetica, sans-serif;
   font-size:12px;
}
.calendarTable td{
   height:20px;
   width:20px;
   text-align:center;
   vertical-align:middle;
   border: #CCCCCC solid 1px;
}
.calendarTable td.calendarCellCup{
   background-color: #CCC;
}
.calendarTable td.weekEnd{
   background-color: #FF9966;
}
.calendarTable td.calendarCell:hover{
   background-color:#CCCCCC;
}
.calendarTable td.weekEnd:hover{
   background-color:CCCCCC;
}

.calendarTable td a{
   text-decoration:none;
   color:#000000;
   display:block;
   line-height:18px;
}
.calendarTable td a:hover{
   color:#FFF;
}
.calendarTable .NavTable{
   margin:0px;
   width:100%;
   border-collapse:collapse;
   font-family:Arial, Helvetica, sans-serif;
   font-size:12px;   
}
.calendarTable .NavTable td{
   height:20px;
   text-align:center;
   vertical-align:middle;
   border:none;
   /*border: #CCCCCC solid 1px;*/
}
.calendarTable .NavTable td.date{
   width:90px;
   border-left: #CCCCCC solid 1px;
   border-right: #CCCCCC solid 1px;
}
.calendarTable .NavTable td.arrow{
}
.calendarTable .NavTable td.arrow:hover{
   background-color:#EEE;
}
.calendarTable .NavTable td.arrow a{
   display:block;
   line-height:16px; 
   color:#000;
}
.calendarTable .NavTable td.arrow a:hover{
   color:#000;
}

#todayButton{
   margin:0px 15px 0px 0px;
   padding:5px 0px 5px 0px;/**/
   border: #CCCCCC solid 1px;
}
#todayButton a{
  padding:5px 20px 5px 20px;
  color:#000000;
  text-decoration:none;
}
#todayButton a:hover{
   background:#78C371;
}
#callCalendarButton{
   border: #CCCCCC solid 1px;
}
#setDateField{
  padding:5px 40px 5px 4px;
  color:#000000;
}
#setDateFieldPrefix{
  padding:5px 0px 5px 40px;
  color:#000000;
}

</style>
<div class="agreement_setting_window" style="margin-top:150px;">
    <div style="width:650px;padding:30px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
        <div class="cap">Cоздать:</div>
        <hr>
        <form method="GET">
        <!-- hidden -->
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="_hidden" name="client_id" value="<?php echo $client_id; ?>">
        <input type="hidden" name="section" value="choice_2">
        <input id="hiddenSetDateField"  type="hidden" name="date" value="<?php echo date('d.m.Y'); ?>">
        <!-- -->
        <?php echo $specification_section; ?>
        <div class="subsection" style="color:#777;">новый договор:</div>
        <!-- <div class="row"><label class="areement_set_label"><input type="radio" name="agreement_type" onclick="drop_radio_buttons(this);" value="short_term">Краткосрочный</label></div> -->
        <div class="row"  style="color:#777;"><label class="areement_set_label"><input type="radio" name="agreement_type" onclick="drop_radio_buttons(this);" value="long_term">Долгосрочный</label></div>
        
        <br />
        <hr>
        <div style="margin:20px 0px 50px 0px;">
            <div class="subsection">дата создания:</div>
            <div style="float:left" id="todayButton"><a href="#" onclick="document.getElementById('setDateField').innerHTML = '<?php echo date('d.m.Y'); ?>'; document.getElementById('hiddenSetDateField').value = '<?php echo date('d.m.Y'); ?>'; return false;">Сегодня</a></div>
            <div style="float:left" id="callCalendarButton"><a href="#" onclick="calendar.show(this); return false;"><img src="../../skins/tpl/admin/order_manager/img/call_calendar_button.png" /></a></div>
            <div style="float:left" id="setDateFieldPrefix">Выбрано:</div>
            <div style="float:left" id="setDateField"><?php echo date('d.m.Y'); ?></div>
            
            <div style="float:right"><input class="button" type="submit" value="Далее"></div>
            
            
            <div style="clear_div"></div>
        </div>
        <br />
        </form>
    </div>
</div>
