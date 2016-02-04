<form>
	<!-- <div class="art_name">
		<div class="inform_message">Введите артикул:</div>
	</div> -->
	<div class="art_name_input">
		<div class="search_cap">Поиск:</div>
		<input type="text" value="" placeholder="поиск по артикулу" id="add_new_articul_in_rt">
		<div class="search_button">&nbsp;</div>
	</div>
	
	<div id="information_block_of_articul">
		
	</div>

	
</form>
<style type="text/css">
.art_name_input{
	height: 30px;
}
.art_name_input .search_cap{
	    float: left;
    font-size: 18px;
    font-weight: bold;
    color: #007036;
    padding: 0px 5px 0px 0px;
}
.art_name_input #add_new_articul_in_rt{
	float: left;
    padding: 2px 25px 3px 5px;
    width: 375px;
}

.art_name_input .search_button {
    float: left;
    margin: 0px;
    width: 61px;
    height: 29px;
    cursor: pointer;
    background-image: url(./skins/images/img_design/quick_search_button.png);
    background-repeat: no-repeat;
}
#choose_one_of_several_articles input[type="checkbox"]{
	display: block;
}
#choose_one_of_several_articles .admin_checkbox{
	padding: 0;
}
#choose_one_of_several_articles label{
	width: 100%;
    height: 100%;
    float: left;
    /* background: red; */
    padding: 5px 2px;
    cursor: pointer;
}
</style>