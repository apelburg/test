<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/Base64Class.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/kpManagerClass.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/tableDataManager.js"></script>
<script type="text/javascript">
   tableDataManager.url = '?page=client_folder&section=business_offers&change_comment=1&client_id=<?php echo $client_id; ?>';
</script>
<?php
if (isset($_GET['query_num']) && (int)$_GET['query_num']) {  
    ?>
        <div class="cabinet_top_menu">
          <ul class="central_menu" >
            
            <li <?php if(!isset($_GET['show_all'])){echo 'class="selected"';} ?>>
              <a href="http://<?= HOST;?>/?page=client_folder&section=business_offers&query_num=<?=$_GET['query_num'];?>&client_id=<?=$_GET['client_id'];?>">
                <div class="border">По запросу</div>
              </a>
            </li>
            <li <?php if(isset($_GET['show_all'])){echo 'class="selected"';} ?>>
              <a href="http://<?= HOST;?>/?page=client_folder&section=business_offers&query_num=<?=$_GET['query_num'];?>&show_all=1&client_id=<?=$_GET['client_id'];?>">
                <div class="border">Все</div>
              </a>
            </li>
          </ul>
        </div>
    <?php
    // комментарии
    // include ROOT.'/libs/php/classes/comments_class.php';
    // $comments = new Comments_for_query_class;
    if(isset($COMMENTS)){
        $comments = $COMMENTS;
    }

    // класс работы с базой
    include ROOT.'/libs/php/classes/db_class.php';
    // класс работы с формами
    // include './libs/php/classes/os_form_class.php';

    // класс работы с поставщиками
    // include './libs/php/classes/supplier_class.php';

    // класс карточки товара
    include ROOT.'/libs/php/classes/rt_position_gen_class.php';
    
    // отключить после приведения карточки товара к единому виду
        // класс работы с позициями каталога
        include ROOT.'/libs/php/classes/rt_position_catalog_class.php';
        // класс работы с позициями не каталога
        include ROOT.'/libs/php/classes/rt_position_no_catalog_class.php';
    
    // расширение класса карточки товара
    include_once ROOT.'/libs/php/classes/rtPositionUniversal.class.php';
    
    // класс работы с менеджерами
    include ROOT.'/libs/php/classes/manager_class.php';

    
    $id = (isset($_GET['id']))?$_GET['id']:'none';

    
    $POSITION = new rtPositionUniversal;

    $type_product = $POSITION->position['type'];




    include_once (ROOT.'/libs/php/classes/rt_class.php');
    $query_num = $_GET['query_num'];
    $cont_face_data = RT::fetch_query_client_face($query_num);
    //print_r($cont_face_data);

    $cont_face = '<div class="client_faces_select2" sourse="rt" query_num="'.$query_num.'" client_id="'.$client_id.'" onclick="openCloseMenu(event,\'clientManagerMenu\');">Контактное лицо: '.(($cont_face_data['id']==0)?'не установлено':$cont_face_data['details']['last_name'].' '.$cont_face_data['details']['name'].' '.$cont_face_data['details']['surname']).'</div>';


    $CALCULATOR_LEVELS = array('full'=>"Конечники",'ra'=>"Рекламщики");
    $calculator_level = ($POSITION->position['calculator_level']!='')?$POSITION->position['calculator_level']:'full';
    $calculator_level_ru = $CALCULATOR_LEVELS[ $calculator_level ];
    ?>
    <script type="text/javascript">
        $(document).on('keyup', '.query_theme', function(event) {
            // первым параметром перелаём название функции отвечающей за отправку запроса AJAX
            // вторым параметром передаём объект к которому добавляется класс saved (класс подсветки)
            timing_save_input('save_status_name',$(this));
        });


        function save_status_name(obj){// на вход принимает object input
            var query_num = obj.attr('query_num');
            //alert(query_num);
            $.post('', {
                AJAX:'edit_query_theme',
                query_num:query_num,
                theme:obj.val()
            }, function(data, textStatus, xhr) {
                 console.log(data);
                // обрабатываем положительный ответ из PHP
                if(data['response']=="OK"){
                    // php возвращает json в виде {"response":"OK"}
                    // если ответ OK - снимаем класс saved
                    obj.removeClass('saved');
                }else{
                    console.log('Данные не были сохранены.');
                }
            },'json');
        }


        // функция тайминга
        function timing_save_input(fancName,obj){
            //если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
            if(!obj.hasClass('saved')){
                window[fancName](obj);
                obj.addClass('saved');                  
            }else{// стоит запрет, проверяем очередь по сейву данной функции        
                if(obj.hasClass(fancName)){ //стоит в очереди на сохранение
                    // стоит очередь, значит мимо... всё и так сохранится
                }else{
                    // не стоит в очереди, значит ставим
                    obj.addClass(fancName);
                    // вызываем эту же функцию через n времени всех очередей
                    var time = 2000;
                    $('.'+fancName).each(function(index, el) {
                        console.log($(this).html());
                        
                        setTimeout(function(){timing_save_input(fancName,$('.'+fancName).eq(index));// обнуляем очередь
                    if(obj.hasClass(fancName)){obj.removeClass(fancName);}}, time); 
                    });         
                }       
            }
        }
    </script>  
    <link href="./skins/css/rt_position.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        #info_string_on_query {
        background-color: #c7c8ca;
        color: #fff;
        font-size: 12px;
    }</style>
    <div id="info_string_on_query">
        <ul>
            <li style="opacity:0" id="back_to_string_of_claim"></li>
            <li id="claim_number" data-order="<?=$POSITION->position['id'];?>">
                <a href="?page=client_folder&query_num=<?=$POSITION->position['query_num'];?>&client_id=<?php echo $client_id; ?>">Запрос № <?=$POSITION->position['query_num'];?></a></li>
            <li id="claim_date"><span>от <?=$POSITION->position['date_create'];?></span></li>
            
            <li id="query_theme_block"><span>Тема:</span> <input id="query_theme_input" class="query_theme" data-id="<?=$POSITION->position['RT_LIST_ID'];?>" type="text" query_num="<?=$POSITION->position['query_num'];?>" value="<?=$POSITION->position['theme']?>" onclick="fff(this,'Введите тему');"></li>
            <li style="float:right;height: 100%;width: 40px;"><span data-rt_list_query_num="<?=$POSITION->position['query_num'];?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($POSITION->position['query_num']); ?> "></span></li>
            <li style="float:right"><?php  echo $cont_face; ?></li>
            <li style=""><div class="client_faces_select2" sourse="rt" query_num="'.$query_num.'" client_id="'.$client_id.'" onclick="openCloseMenu(event,'calcLevelSwitcher');">Калькулятор: <?php  echo $calculator_level_ru; ?></div>
          <input type="hidden" id="calcLevelStorage" value="<?php  echo $calculator_level; ?>"></li>
        </ul>
    </div>

    <?php
}
?>



<table id="kp_list_tbl" class="clients_common_output_table"  tbl="managed">
    <tr class="header">
        <td style="width:  7px;border:none;">&nbsp;</td>
        <td style="width:90px;border-left:none;">Дата созадания</td>
        <td style="width:90px;">№ запроса</td>
        <!-- <td style="width:300px;">Краткое описание</td>-->
        <td style="width:300px;">Тема</td>
        <td style="width:350px;">Для контактного лица:</td>
        <td style="width:250px;">Действия</td>
        <td style="width:auto;">Комментарии</td>
        <td style="width:110px;">Дата отправки</td>
        <td style="width:60px;border-right:none;">Удалить</td>
        <td style="width:  7px;border:none;">&nbsp;</td>
    </tr>
    <tr>
        <td  class="interval" colspan="10"></td>
    </tr>
    <?php  echo $rows; ?>
    <tr>
        <td  class="interval" colspan="10"></td>
    </tr>
</table>