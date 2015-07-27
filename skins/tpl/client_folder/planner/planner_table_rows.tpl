<tr class="<?php echo $row_class_name; ?>">
   <td>&nbsp;</td>
   <td onclick="<?php echo $onclick; ?>">
      <div class="date_in_format"><?php echo $write_date_in_format; ?></div>
      <div class="time_in_format"><?php echo $write_time_in_format; ?></div>
   </td>
   <td onclick="<?php echo $onclick; ?>">
      <div class="date_in_format"><?php echo get_manager_nickname_by_id($pl_manager_id); ?></div>
   </td>
   <td onclick="<?php echo $onclick; ?>">
      <div class="date_in_format"><?php echo $exec_date_in_format; ?></div>
      <div class="time_in_format"><?php echo $exec_time_in_format; ?></div>
      <input id="exec_date_input_<?php echo $pl_id; ?>" type="hidden" value="<?php echo $pl_exec_datetime; ?>">
   </td>
   <td onclick="<?php echo $onclick; ?>" id="type_<?php echo $pl_id; ?>"><?php echo $pl_type; ?></td>
   <td onclick="<?php echo $onclick; ?>" id="cont_face_<?php echo $pl_id; ?>"><?php echo $pl_cont_face; ?></td>
   <td colspan="3" onclick="<?php echo $onclick; ?>" id="plan_<?php echo $pl_id; ?>"><?php echo $pl_plan; ?></td>
   <td><?php echo @$done_button; ?></td>
</tr>

<!--?<?php echo addOrReplaceGetOnURL('plan_id='.$pl_id.'&set_plan_status=done') ?>"-->