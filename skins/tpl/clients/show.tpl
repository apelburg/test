<!--<button id="opener">open the dialog</button>
<div id="dialog" title="Dialog Title">I'm a dialog<div id="dialog" title="Dialog Title">I'm a dialog</div><div id="dialog" title="Dialog Title">I'm a dialog</div></div>

<script>
var div = document.createElement('div');
div.id = "dialog";
div.appendChild(document.createTextNode("I'm a dialog new"));
document.body.appendChild(div);
$( "#dialog" ).dialog({ autoOpen: false });
$( "#opener" ).click(function() {
  $( "#dialog" ).dialog( "open" );
});
</script>-->
<!-- begin skins/tpl/clients/show.tpl -->         
     <?php echo $content; ?> 
<!-- end skins/tpl/clients/show.tpl -->
 
