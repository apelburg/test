<style>
.field_for_fill{
 cursor:pointer;
 background-color: #E6DCC8;
}
.main_menu_tbl{ display:none; }
</style>
<div class="agreement">
     <div class="cap" id="agreement_tools_plank">
        <table width="100%" border="0">
          <tr>
            <td width="400"><!--width="167"-->
                <?php 
                    if(isset($_GET['conrtol_num']))
                    {
                    // это срабатывает когда осуществляется переход из шага выбора договора для создания спецификации, через ссылку напротив договора
                ?>
                    <button type="button" onclick="location = '?page=agreement&section=choice&client_id=<?php echo $client_id; ?>&conrtol_num=<?php echo $_GET['conrtol_num']; ?>';" style="cursor:pointer;">закрыть</button>
                    <!--
                    <button type="button" onclick="location = '/admin/order_manager/?page=clients&razdel=show_client_folder&sub_razdel=agreements&client_id=<?php echo $client_id; ?>';" style="cursor:pointer;">вернуться в Договоры</button>
                    <button type="button" onclick="location = '/admin/order_manager/?page=clients&razdel=show_client_folder&sub_razdel=calculate_table&client_id=<?php echo $client_id; ?>';" style="cursor:pointer;">вернуться в РТ</button>-->
                <?php 
                    }
                    else
                    {

                       if(isset($_GET['query_num']))
                       {
                      echo '<button type="button" onclick="location = \'?page=client_folder&query_num='.$query_num.'&client_id='.$client_id.'\';" style="cursor:pointer;">вернуться в РТ</button>&nbsp;&nbsp;';
                    
                       }
                     echo '<button type="button" onclick="location = \'?page=cabinet&section=requests&subsection=no_worcked_men\';" style="cursor:pointer;">в раздел Кабинет</button>';
                       if($dateDataObj->doc_type=='spec')
                       {
                        echo '&nbsp;&nbsp;<button type="button" onclick="location = \'?page=client_folder&section=agreements&client_id='.$client_id.'\';" style="cursor:pointer;">в раздел Договоры</button>';
                       }
                        if($dateDataObj->doc_type=='oferta')
                       {
                        echo '&nbsp;&nbsp;<button type="button" onclick="location = \'?page=client_folder&section=agreements&doc_type=oferta&client_id='.$client_id.'\';" style="cursor:pointer;">в раздел Оферты</button>';
                       }
            
                    }
                ?>  <!--'.$_GET['query_num'].'=10001&client_id='.$client_id.'-->
            </td>
            <td>
                <?php if($dateDataObj->doc_type=='spec') echo 'Договор №'.$agreement['agreement_num'].' от '.$agreement_date.' ('.fetchOneValFromAgreementTbl(array('retrieve' => 'type_ru','coll' => 'type' ,'val' => $_GET['agreement_type'])).')'; ?>
            </td>
            <td>  
             
            </td>
            <td align="right">
                <?php 
                    if($dateDataObj->doc_type=='oferta')
                    {
                    
                     $href = '?page=agreement&section=specification_editor&client_id='.$client_id.'&oferta_id='.$oferta_id.'&dateDataObj='.htmlspecialchars('{"doc_type":"oferta"}');
                    // echo $href;
                ?>
                    <script type="text/javascript">
                        $(document).on('click', '#qetwertwertwtretwretwe', function(event) {
                            // event.preventDefault();
                            var url = 'http://<?=HOST;?>/?page=clients&section=client_folder&subsection=client_card_table&client_id=<?=$_GET['client_id'];?>';
                            // event.preventDefault();
                                $.post(url, {
                                    AJAX: "show_requesit",
                                    id:"<?=$client_firm['id']?>",
                                    title:"<?=$client_firm['company']?>"
                                }, function(data, textStatus, xhr) {
                                    // $("#show_requesit").html(data);
                                    // $("#show_requesit").dialog('option', 'title', title);
                                    // $("#show_requesit").dialog("open");
                                    standard_response_handler(data);
                                },'json');  
                        });
                    </script>

                    <button type="button" id="qetwertwertwtretwretwe" data-client_id="" style="cursor:pointer;">реквизиты</button>
                    <button type="button" onclick="location = '?<?php echo htmlspecialchars(addOrReplaceGetOnURL('section=oferta_full_editor')); ?>';" style="cursor:pointer;">редактировать текст Оферты</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" onclick="location = location.pathname + '<?php echo $href; ?>';" style="cursor:pointer;">редактировать Оферту</button>
                <?php 
                    }
                    if($dateDataObj->doc_type=='spec' && isset($_GET['open']) && $_GET['open']== 'specification')
                    {
                ?>
                    <script type="text/javascript">
                        $(document).on('click', '#qetwertwertwtretwretwe', function(event) {
                            // event.preventDefault();
                            var url = 'http://<?=HOST;?>/?page=clients&section=client_folder&subsection=client_card_table&client_id=<?=$_GET['client_id'];?>';
                            // event.preventDefault();
                                $.post(url, {
                                    AJAX: "show_requesit",
                                    id:"<?=$client_firm['id']?>",
                                    title:"<?=$client_firm['company']?>"
                                }, function(data, textStatus, xhr) {
                                    // $("#show_requesit").html(data);
                                    // $("#show_requesit").dialog('option', 'title', title);
                                    // $("#show_requesit").dialog("open");
                                    standard_response_handler(data);
                                },'json');  
                        });
                    </script>

                    <button type="button" id="qetwertwertwtretwretwe" data-client_id="" style="cursor:pointer;">реквизиты</button>
                
                    <button type="button" onclick="location = '?<?php echo htmlspecialchars(addOrReplaceGetOnURL('section=specification_full_editor')); ?>';" style="cursor:pointer;">редактировать текст СП</button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <button type="button" onclick=" location = location.pathname + '?page=agreement&section=specification_editor&client_id=<?php echo $client_id; ?>&specification_num=<?php echo $key; ?>&agreement_id=<?php echo $agreement_id.htmlspecialchars('&dateDataObj={"doc_type":"spec"}'); ?>' ;" style="cursor:pointer;">редактировать СП</button>
                 <?php 
                    }
                    else if($dateDataObj->doc_type=='spec'){
            
                        // if($user_status=='1' && ((boolean)$agreement['standart'])){
                       if((boolean)$agreement['standart']){
                  ?>    
                    <script type="text/javascript">
                        $(document).on('click', '#qetwertwertwtretwretwe', function(event) {
                            // event.preventDefault();
                            var url = 'http://<?=HOST;?>/?page=clients&section=client_folder&subsection=client_card_table&client_id=<?=$_GET['client_id'];?>';
                            // event.preventDefault();
                                $.post(url, {
                                    AJAX: "show_requesit",
                                    id:"<?=$client_firm['id']?>",
                                    title:"<?=$client_firm['company']?>"
                                }, function(data, textStatus, xhr) {
                                    // $("#show_requesit").html(data);
                                    // $("#show_requesit").dialog('option', 'title', title);
                                    // $("#show_requesit").dialog("open");
                                    standard_response_handler(data);
                                },'json');  
                        });
                    </script>

                    <button type="button" id="qetwertwertwtretwretwe" data-client_id="" style="cursor:pointer;">реквизиты</button>
                        <button type="button" onclick="location = '?<?php echo addOrReplaceGetOnURL('section=agreement_full_editor'); ?>';" style="cursor:pointer;">редактировать договор</button>
                <?php 
                        }
                    }
                ?>

                <button type="button" onclick="conv_specification.start();print_agreement();" style="cursor:pointer;">распечатать</button>&nbsp;&nbsp;&nbsp;&nbsp;
              
            </td>
          </tr>
        </table>
     </div>
     <div class="agreement_field" id="agreement_blank">
    <!-- <?php //print_r($_GET); ?><br /><br />
     <?php //print_r($our_firm); ?><br /><br />
     <?php //print_r($client_firm); ?><br /><br />
    --> 
     <?php echo $agreement_content; ?>
     <?php echo $specifications; ?>
     </div>
</div>
<script type="text/javascript" src="libs/js/convert_specification_class.js"></script>
<script type="text/javascript" src="libs/js/textRedactor.js"></script>
<script type="text/javascript" src="libs/js/geometry.js"></script>
<script type="text/javascript">
   textRedactor.install(['?page=agreement&update_agreement_finally_sheet_ajax=1','?page=agreement&update_specification_common_fields_ajax=1']);
</script>