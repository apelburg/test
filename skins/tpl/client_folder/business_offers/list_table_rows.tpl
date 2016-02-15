<tr>
     <td class="flank_cell">&nbsp;</td>
     <td class="center without_padding"><?php  echo $date; ?></td>
     <td class="center without_padding"><a href="http://<?= HOST;?>/?page=client_folder&amp;client_id=<?=$_GET['client_id'];?>&amp;query_num=<?=$query_num;?>" style="text-decoration: underline;"><?php  echo $query_num; ?></a></td>
     <td class="left "><?php  echo $row['theme']; ?></td>
     <!--<td class="left"><?php  //echo $com_pred_data['description']; ?></td>-->
     <td class="left">
         <?php  echo $recipient; ?>
     </td>
     <td class="without_padding_all">
         <table class="options_tbl">
             <tr>
                 <td><a href="?<?php  echo addOrReplaceGetOnURL('','show_kp_in_blank&show_kp').'&'.(isset($_GET['show_kp_in_blank'])? 'show_kp':'show_kp_in_blank').'='.$row['id']; ?>">посмотреть</a></td>
                 <td><a href='<?php  echo HOST; ?>/modules/client_folder/business_offers/to_print.php?data=<?php  echo 'new{@}'.$row['id'].'-'.$client_id.'-'.$user_id; ?>' target="_blank">напечатать</a></td>
                 <td><a href='#' onclick="kpManager.sendKpByMail(<?php  echo $row['id']; ?>);">письмо</a></td>
             </tr>
         </table>
  
       <!--<a href='?page=clients&razdel=show_client_folder&sub_razdel=com_offer&client_id=<?php //echo $client_id; ?>&preview=<?php //echo urlencode($file); ?>'>открыть</a> /-->
       <!--<a href='<?php  //echo $dir_name."/".$file; ?>'>скачать</a> /-->
        <!--<a href='#' onclick='rename_file("<?php  //echo $file; ?>");return false;'>переименовать</a> / /-->
   </td>
   <td class="<?php  echo $comment_style; ?>" managed="text" action="change_comment" bd_row_id="<?php echo $row['id']; ?>" bd_field="comment" when_done="clear_class" bg_text="добавьте свой комментарий"><?php  echo $comment; ?></td>
   <td class="left" send_time_type="<?php echo $row['id']; ?>">
      <?php  echo $send_time; ?>
   </td>
   <td class="center italic grey">
     <a href="?<?php  echo addOrReplaceGetOnURL('delete_com_offer='.urlencode($row['id'])); ?>" onclick='if(confirm(" Внимание! КП будет удалено!\r\n Вы действительно хотите удалить КП?")){ return true;} else{ return false;}'>DEL</a>
   </td>
   <td class="flank_cell">&nbsp;</td>
</tr>