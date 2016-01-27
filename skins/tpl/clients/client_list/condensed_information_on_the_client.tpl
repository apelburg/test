<?php
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
    
?>
<div class="client_details_field new_tbl ">
<table id="details_tbl" class="details_tbl">
<tr>
        <td class="company">
            <?php 
                if($_GET['page'] == 'cabinet'){
                    echo $back_without_client;
                }
            ?>
            <div class="container">
                <a href="?page=clients&amp;section=client_folder&amp;subsection=client_card_table&amp;client_id=<?php echo $_GET['client_id']; ?>">  
                        <?php echo $company_name; ?></a>
            </div>
        </td>
       <!--  <td class="cap" style="width:70px;">
            Контакт:
        </td>
        <td class="name">
            <div class="container"> Вася пупкин</div>
        </td> -->
        <td class="empty">&nbsp;
             
        </td>
        <td class="cap">
            Тел.:
        </td>
        <td class="phone">
            <div class="container">
            <?php
                echo $phone;
            ?>
            </div>
        </td>
        <td class="cap">
                    E-mail :
        </td>
        <td class="email">
            <div class="container">
            <?php
                echo $email;
            ?>
            </div>
        </td>
    </tr>
</table>
</div>
<div class="cabinet_top_menu first_line">
                <ul class="central_menu" style="padding-left: 19px;height: 27px;">
                    <li <?php 
                    if(!isset($_GET['section']) || isset($_GET['section']) && ($_GET['section'] == 'requests' || $_GET['section'] =='rt_position')){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php echo HOST; ?>/?page=cabinet<?php
                            if(isset($_GET['client_id'])){
                                echo '&client_id='.$_GET['client_id'];
                            }
                         ?>">
                            <div class="border">Запросы</div>
                        </a>
                    </li>
                    <li <?php 
                    if(isset($_GET['section']) && $_GET['section'] == 'business_offers'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php  echo HOST; ?>/?page=client_folder&section=business_offers&query_num=&client_id=<?php  echo $_GET['client_id']; ?>" style="color:#FFFFFF;">
                            <div class="border">Коммерческие предложения</div>
                        </a>
                    </li>
                    <li <?php 
                    if(isset($_GET['section']) && $_GET['section'] == 'agreements' 
                        && isset($_GET['doc_type']) && $_GET['doc_type'] == 'agreement'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&doc_type=agreement&client_id=<?php  echo $_GET['client_id']; ?>" style="color:#FFFFFF;">
                            <div class="border">Договоры</div>
                        </a>
                    </li>
                    <li <?php 
                    if(isset($_GET['section']) && $_GET['section'] == 'agreements' 
                        && isset($_GET['doc_type']) && $_GET['doc_type'] == 'oferta'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&doc_type=oferta&client_id=<?php  echo $_GET['client_id']; ?>" style="color:#FFFFFF;">
                            <div class="border">Оферты</div>
                        </a>
                    </li>
                </ul>
            </div>