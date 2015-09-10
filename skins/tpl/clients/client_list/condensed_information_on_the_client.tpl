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
                <a href="?page=clients&amp;section=client_folder&amp;subsection=client_card_table&amp;client_id=<?php echo $_GET['client_id']; ?>" target="_blank">  
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