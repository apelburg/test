<style> .main_menu_tbl{ display:none; } </style>
<div class="agreement_setting_window" style="margin-top:150px;">
    <div style="width:650px;padding:30px 30px 70px 30px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
       <!-- <form method="GET">

        <div style="margin:-15px 0px 20px 10px;">  </div>-->
        
            <div class="cap">Условия предоплаты:</div>
       
        <hr />
        <div style="margin:20px 0px 0px 0px;">
            <div class="prepayment_row"><a href='?<?php echo addOrReplaceGetOnURL("section=delivery")."&prepayment=100"; ?>'>предоплата 100%</a></div>
            <div class="prepayment_row"><a href='?<?php echo addOrReplaceGetOnURL("section=delivery")."&prepayment=70"; ?>'>предоплата 70% - 30%</a></div>
            <div class="prepayment_row"><a href='?<?php echo addOrReplaceGetOnURL("section=delivery")."&prepayment=50"; ?>'>предоплата 50% - 50%</a></div>
            <div class="prepayment_row"><a href='?<?php echo addOrReplaceGetOnURL("section=delivery")."&prepayment=30"; ?>'>предоплата 30% - 70%</a></div>
           <!--  <?php 
               if($dateDataObj->doc_type=='spec'){ ?>
             <div class="prepayment_row"><a href='?<?php echo addOrReplaceGetOnURL("section=delivery")."&prepayment=0"; ?>'>постоплата 0 - 100%</a></div>
             <?php } ?>-->
        </div>
        <!-- save_agreement

        </form> -->
    </div>
</div>