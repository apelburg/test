<script type="text/javascript" src="libs/js/tableDataManager.js"></script>
<script type="text/javascript" src="libs/js/specificationRowsAgregator.js"></script>
<script type="text/javascript" src="libs/js/geometry.js"></script>
<script type="text/javascript" src="libs/js/common.js"></script>
<script type="text/javascript">
   <?php if($dateDataObj->doc_type=='spec'){ ?> tableDataManager.url = '?page=agreement&update_specification_ajax=1'; <?php } ?> 
   <?php if($dateDataObj->doc_type=='oferta'){ ?> dddd <?php } ?> 
</script>
<style> .main_menu_tbl{ display:none; } </style>
<div class="specification">
    <br />
    <br />
    <br />
    <br />
    
    <table class="spec_edit_plank" align="center">
        <tr>
            <td width="500">
                <input type="button" onclick="specificationRowsAgregator.set();" value="выделить строки" />&nbsp;&nbsp;&nbsp;&nbsp; 
                <input type="button" onclick="specificationRowsAgregator.reset_all();" value="сбросить всё"/>&nbsp;&nbsp;&nbsp;&nbsp;
                <input type="button" onclick="specificationRowsAgregator.send_changes('<?php echo $dateDataObj->doc_type; ?>');" value="объединить"/>
            </td>
            <td style="text-align:right"><input type="button" onclick=" location = '<?php echo $_SESSION['back_url']; ?>' ;" value="закрыть"/></td>
        </tr>
    </table>
    <table class="spec_tbl" id="specification_tbl" tbl="managed" border="1" align="center">
        <tr class="bold_font">
            <td width="20">&nbsp;</td>
            <td width="20">№</td>
            <td width="500">Наименование и<br>описание продукции</td>
            <!--<td  width="250">Порядок изготовления продукции</td>-->
            <td>Количество продукции</td>
            <td colspan="2">стоимость за штуку</td>
            <td colspan="2">Общая стоимость</td>
        </tr>
        <?php echo $rows; ?>
        <tr class="bold_font">
            <td width="20">&nbsp;</td>
            <td colspan="5">Итоговая сумма по данной спецификации (договору)</td>
            <td class="price"><?php  echo number_format($itogo,"2",".",''); ?></td>
            <td class="currensy">p.</td>
        </tr>
        <tr class="bold_font">
            <td width="20">&nbsp;</td>
            <td colspan="5">В т.ч. НДС 18%</td>
            <td class="price"><?php  echo number_format(($itogo/118*18),"2",".",''); ?></td>
            <td class="currensy">p.</td>
        </tr>
    </table>
    <!--<a href="#" onclick="alert(onerror_report);return false;">ошибки</a>-->   
</div>
 <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br /> <br />
    <br />
    <br />
    <br />
    <br />
    <br />