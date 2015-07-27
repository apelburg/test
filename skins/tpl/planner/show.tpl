<script type="text/javascript" src="./libs/js/up_window_consructor.js"></script>
<script type="text/javascript" src="./libs/js/classes/calendar_consturctor.js"></script>
<style type="text/css">
#menu_for_planner_td_<?php echo $razdel;?>{ background:bottom repeat-x url(./skins/images/img_design/bg_menu_third_level_on.png);}
#calendar_table td{ 
  background-color:#ECECEC; 
  border:0px solid #333333;
}
#time_table td{ 
  background-color:#ECECEC; 
  border:0px solid #333333;
}
.planner_rows{ 
  cursor:pointer;
}
</style>
<table class="planner_menu" style="margin:10px 0px 0px 0px;width:100%;" cellpadding="0" cellspacing="0">
  <tr>
     <td height="26" width="200" class="add_button">
         <a href="#" onclick="show_client_list_by_manager_for_planner(<?php echo $user_id; ?>);return false;">+ Добавить новый план</a>
     </td>
     <td height="26" width="200" id="menu_for_planner_td_history">
         <a href="?page=planner&razdel=history">История</a>
     </td>
     <td width="200" id="menu_for_planner_td_plans">
         <a href="?page=planner&razdel=plans">Планы</a>
     </td>
     <td width="200" id="menu_for_planner_td_common">
         <a href="?page=planner&razdel=common">Все</a>
     </td>
     <td class="menu_for_razdel_2_td">&nbsp;
         
     </td>
  </tr>
</table>
<div style="text-align:center;margin:8px 0px 8px 0px;"><span class="page_nav_block"><?php echo $page_navigation; ?></span></div>
<?php echo $palnner_content; ?>
<div style="text-align:center;margin:8px 0px 8px 0px;"><span class="page_nav_block"><?php echo $page_navigation; ?></span></div>