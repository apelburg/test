<div class="agreement_setting_window" style="margin-top:150px;">
    <div style="width:650px;padding:30px 30px 70px 30px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
        <form method="GET">

        <!-- -->
        <div class="cap">Условия доставки продукции:</div>
        <hr />
        <div style="margin:20px 0px 0px 0px;">
            <div class="prepayment_row"><a href="?<?php echo addOrReplaceGetOnURL('section=short_description').'&address=samo_vivoz'; ?>">Самовывоз покупателем со склада: Санкт-Петербург, ул. Чугунная, д. 14, корп.1</a></div>
            <?php echo $addresses; ?>
            <div class="prepayment_row" style="margin:10px 0px 0px 10px;"><a href="#"  style="text-decoration:underline;" onclick="var result = prompt('введите адрес доставки'); var regexp = /%20/g; if(result) location = location.pathname + '?'+addOrReplaceGetOnURL('section=short_description')+'&address='+encodeURIComponent(result).replace(regexp,'+') ; return false;">Добавить адрес доставки</a></div>
           
        </div>
        <!-- save_agreement location = '/?'+addOrReplaceGetOnURL('section=short_description')+'address='+encodeURIComponent(address).replace(regexp,"+")  
        ?<?php echo addOrReplaceGetOnURL('section=short_description').'&address='.urlencode('Самовывоз покупателем со склада: Санкт-Петербург, ул. Чугунная, д. 14, корп.1'); ?>-->

        </form>
    </div>
</div>