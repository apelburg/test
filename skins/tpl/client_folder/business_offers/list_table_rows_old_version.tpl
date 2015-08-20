<tr>
     <td class="flank_cell">&nbsp;</td>
     <td class="center without_padding"><?php  echo $date; ?></td>
     <td class="center without_padding"><?php  echo $query_num; ?></td>
     <!--<td class="left"><?php  echo $com_pred_data['description']; ?></td>-->
     <td class="left"></td>
     <td class="without_padding_all">
         <table class="options_tbl">
             <tr>
                 <td><a href="?<?php  echo $_SERVER['QUERY_STRING'].'&show_old_kp='.$client_id.'/'.$file; ?>">посмотреть</a></td>
                 <td><a href='<?php  echo HOST; ?>/modules/clients/client_folder/business_offers/to_print.php?data=<?php  echo 'old{@}'.$client_id.'/'.$file; ?>' target="_blank">напечатать</a></td>
                <!-- onclick="kp_to_print('old','<?php  echo $client_id.'/'.$file; ?>');"-->
                 <td><a href='#' onclick="alert('старая версия КП не имеет опции отправки по почте');">письмо</a></td>
             </tr>
         </table>
   </td>
   <td class="<?php  echo $comment_style; ?>" managed="text" file_name="<?php  echo $file; ?>" when_done="clear_class" bg_text="добавьте свой комментарий"><?php  echo $comment; ?></td>
   <td class="left grey">
      ----
   </td>
   <td class="center italic grey">
       <a href='?<?php  echo addOrReplaceGetOnURL('delete_com_offer='.urlencode($file).'&id='.$com_pred_data["id"].'&old_version=true'); ?>' onclick='if(confirm(" Внимание! КП будет удалено!\r\n Вы действительно хотите удалить КП?")){ return true;} else{ return false;}'>DEL</a>
   </td>
   <td class="flank_cell">&nbsp;</td>
</tr>