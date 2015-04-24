
<style type="text/css">
#cabinet{width: 100%; float: left; background-color: #fff}
#cabinet_top_menu1,#cabinet_top_menu{height: 28px;width: 100%; float: left;  background-color: #92b73e}
#cabinet_left_coll_menu{ height: 100%; border-right:1px solid #8b8a89;  width: 10%; position: absolute;}
#cabinet_central_panel{ padding-left: 10%; width: 90%; float: left;}
ul#cabinet_left_menu{list-style: none; padding: 0; margin: 0;}
ul#cabinet_left_menu li{ padding: 0; margin: 0; list-style: none; font-size: 14px;}
ul#cabinet_left_menu li a{  line-height: 28px;
	text-decoration: none;color: #000; width: 90%; height: 100%; padding: 0 5%; margin-top: 5px; float: left;}
ul#cabinet_left_menu li a:hover{background-color: #92b73e}
ul#cabinet_left_menu li.selected{ background-color: #92b73e}
</style>
<div id="cabinet">	
	<div id="cabinet_left_coll_menu">
		<div id="cabinet_top_menu1"></div>
		<ul id="cabinet_left_menu">
			<li><a href="">Важно</a></li>
			<li><a href="">Запросы</a></li>
			<li><a href="">В оформлении</a></li>
			<li><a href="">Заказы</a></li>
			<li><a href="">Закрытие</a></li>
			<li><a href="">Оформление</a></li>
			<li><a href="">Планировщик</a></li>
		</ul>
	</div>
	<div id="cabinet_central_panel">
		<div id="cabinet_top_menu">
			<ul id="left_menu">
			<li><a href="">Не обработанные</a></li>
			<li><a href="">В работе</a></li>
			<li><a href="">Отправлены в СНАБ</a></li>
			<li><a href="">Рассчитанные в СНАБ</a></li>
			<li><a href="">Выставлено КП</a></li>
			<li><a href="">Отказанные</a></li>
			<li><a href="">Все</a></li>
		</ul>
		</div>
		<?php	
    echo '<div style="margin-top:300px;text-align:center;">такого подраздела пока не существует 99</div>';
?>
	</div>
	
</div>