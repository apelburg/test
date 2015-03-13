<style type="text/css">
    .adress_note{ float: left; padding-top: 10px; color: rgb(176, 175, 175)}
    #requisits_button{display: block;float: right;padding: 3px 10px 2px 10px;background-color: #F3F5F5;border:1px solid #D0D7D8;position: absolute;right: 50%;margin-right: 20px; cursor: default;}
    #requisits_button:hover{ background-color: #E4E8E8;}
</style>
<script type="text/javascript" src="libs/js/rate_script.js"></script>
<script type="text/javascript">
$(document).on('click',' #requisits_button', function(){
    $('#requesites_form').dialog("open");
});
$(function() {
    $('#requesites_form').dialog({
        width: 'auto',
        height: 'auto',
        title: 'Реквизиты',
        autoOpen : false,
        buttons: [
            {
                text: 'Добавить',
                click: function() {                    
                    $( this ).dialog( "close" );                    
                }
            },
            {
                text: 'Закрыть',
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});

// 
// РЕКВИЗИТЫ
//

// ПОКАЗТЬ РЕКВИЗИТЫ
$(document).on('click', '#requesites_form a', function(event) {
    var title = $(this).attr('title');
    $.post('', {
        ajax_standart_window: "show_requesit",
        id:$(this).attr('data-id')
    }, function(data, textStatus, xhr) {
        $("#show_requesit").html(data);
        $("#show_requesit").dialog('option', 'title', title);
        $("#show_requesit").dialog("open");
    });    
});

// ИНИЦИАЛИЗАЦИЯ ОКНА ПОКАЗА РЕКВИЗИТОВ
$(function(){
    $("#show_requesit").dialog({
        // width: ($(window).width()-2),
        width: 800,
        // height: ($(window).height()-2),
        // position: [0,0],
        autoOpen : false,
        draggable: false,
        modal:true,

        buttons: [
            {
                text: 'Закрыть',
                click: function() { 
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});
function add_new_management_element(container_name){
      // get container
      var container = document.getElementById(container_name);
      
      var div_arr = get_divs(container);

      var new_element = div_arr[div_arr.length-1].cloneNode(true);
      clean_fiedls(new_element);
      
      if(new_element.getElementsByTagName('delete_btn')[0]) new_element.getElementsByTagName('delete_btn')[0].parentNode.removeChild(new_element.getElementsByTagName('delete_btn')[0]);

      container.appendChild(new_element);
      
      return false; 
       
      // функция копирования строки контактов из старой ОС
      function get_divs(div){
          var nodes_arr = div.childNodes;
          for(var i=0;i<nodes_arr.length;i++){
              if((nodes_arr[i].nodeName).toLowerCase()=='div'){
                  if(!div_arr) var div_arr = [];
                  div_arr.push(nodes_arr[i]);
              }
          }          
          return div_arr;
      }     
      // функция копирования строки контактов из старой ОС 2
      function clean_fiedls(element){
          var input_arr = element.getElementsByTagName("input");
          for(var i=0;i<input_arr.length;i++){ 
              input_arr[i].name = (input_arr[i].name).slice(0,(input_arr[i].name).indexOf('][')+2) + (parseInt((input_arr[i].name).slice((input_arr[i].name).indexOf('][')+2))+1) + (input_arr[i].name).slice((input_arr[i].name).lastIndexOf(']['));
              if(!input_arr[i].getAttribute("field_type")) input_arr[i].value = ''; 
              if(input_arr[i].getAttribute("field_type") && input_arr[i].getAttribute("field_type") == 'id') input_arr[i].value = '';
              if(input_arr[i].getAttribute("field_type") && input_arr[i].getAttribute("field_type") == 'acting'){
                  input_arr[i].value = '';
                  input_arr[i].checked = false;
              }
          }
      }
   }

// УДАЛЕНИЕ КОНТАКТНОГО ЛИЦА ИЗ РЕКВИЗИТОВ
$(document).on('click', '.cont_faces_field_delete_btn', function(){
    var id = $(this).attr('data-id');
    var e =$(this);
    var tbl = $(this).attr('data-tbl');
    // показываем UI confirm
    $( "#dialog-confirm" ).dialog({
            resizable: false,
            height:160,
            modal: true,
            buttons: {
                "Удалить": function() {
                    e.parent().parent().parent().parent().parent().remove();
                    $.post('', {ajax_standart_window:"delete_cont_requisits_row",id: id,tbl:tbl}, function(data, textStatus, xhr) {
                        if(data['response']!=1){
                            new_html_modal_window('Что-то пошло не так, запомните свои действия и опишите их в письме к разработчикам.','Предупреждение об ошибке','','', '', '');
                        }
                    },'json');
                    $( this ).dialog( "close" );
                },
                Отмена: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
    
});

//  РЕДАКТОР РЕКВИЗИТОВ
$(document).on('click', '#requesites_form table tr td:nth-of-type(2) img:nth-of-type(1)', function(event) {
    var title = $(this).attr('title');
    $.post('', {
        ajax_standart_window: "edit_requesit",
        id:$(this).attr('data-id')
    }, function(data, textStatus, xhr) {
        $("#edit_requesit").html(data);
        $("#edit_requesit").dialog('option', 'title', title);
        $("#edit_requesit").dialog("open");
    });    
});
// ИНИЦИАЛИЗАЦИЯ ОКНА РЕДАКТОРА РЕКВИЗИТОВ
$(function(){
    $("#edit_requesit").dialog({
        width: ($(window).width()-2),
        height: ($(window).height()-2),
        position: [0,0],
        autoOpen : false,
        draggable: false,
        modal:true,

        buttons: [
            {
                text: 'Сохранить',
                click: function() {
                    var post = $("#requisits_edit_form").serialize();
                    $.post('', post, function(data, textStatus, xhr) {
                        new_html_modal_window(data,'пришло на сервер:','','', '', '');
                    });
                    $( this ).dialog( "close" );
                }
            },
            {
                text: 'Отменить',
                click: function() { 
                    $( this ).dialog( "close" );
                }
            }
       ]
    });
});




</script>


<div class="client_table">
    <?php
    // echo "<pre>";
    // print_r(Client::get_requisites($client_id));
    // echo "</pre>";
    ?>
	<table class="client_table_gen">
    	<tr>            
        	<td>
<div id="requisits_button">Реквизиты</div>
            	<table>
                	<tr>
                    	<td>Название</td>
                    	<td><strong><?php echo trim($client['company']); ?></strong></td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td><?php echo $clientRating; ?></td>
                    </tr>
                    <tr>
                        <td>Кураторы</td>
                        <td><?php echo $manager_names; ?></td>
                    </tr>
                	<tr>
                    	<td>Деятельность</td>
                    	<td><span  style="color:#f1f1f1">В разработке</span></td>
                    </tr>
                	<?php echo $client_address_s; ?>
                	<tr>
                    	<td>Дополнительная информация</td>
                    	<td><?php echo !empty($client['dop_info'])?$client['dop_info']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?></td>
                    </tr>
                </table>

        	<td>
            	<?php echo $cont_company_phone; ?>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>