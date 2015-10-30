// JavaScript Document

   function ConvertSpecOfferClass (){
	   this.page_h = 970;//980
	   this.img_h = 65;// 45 + margin 60
	   this.container_h = this.page_h - 5; // запас чтобы не было в притык
	   this.level = 1;
	   
	   //alert(this.container_h );
	
   }
   
   ConvertSpecOfferClass.prototype.start = function (){

	   this.tbl_container_div =  document.createElement('div');
	   tbl = document.getElementsByTagName('table')[0];
	   
	   var img  = new Image();
	   img.src = 'http://' + location.host +'/skins/images/img_design/spec_offer_top_plank_2.jpg';
	   img.style.margin = '0px 0px 20px 0px';
	
	   tbl.parentNode.insertBefore(this.tbl_container_div, tbl);
	    //tbl.parentNode.insertBefore(img, this.tbl_container_div);
	   this.tbl_container_div.appendChild(img);
       this.tbl_container_div.appendChild(tbl);
	   
	   this.analyser(tbl);

	   /*
	   для измерения высоты страницы
	   spec_div =  document.createElement('div');
	   spec_div.style.border = '#FF0000 solid 1px';
	   spec_div.style.position = 'absolute'; 
	   spec_div.style.top = '0px'; 
	   spec_div.style.height = '978px';
	   //spec_div.style.fontSize = '12px';
       document.body.appendChild(spec_div);
	   */
	   
	   
   }
   ConvertSpecOfferClass.prototype.analyser = function (tbl){
	   
	   var tr_nodes = tbl.getElementsByTagName('tr');

	   
      
	   var max_pos = this.page_h*(this.level-1)+this.container_h;
	   //var max_pos = this.page_h*this.level;
	   var img  = new Image();
	   img.src =  'http://' + location.host + '/skins/images/img_design/spec_offer_top_plank_2.jpg';
	   
	   //img.style.marginTop="-75px";
	   var new_table = document.createElement('table');
	   new_table.style.border = '#CCCCCC solid 0px';
	   new_table.style.width = tbl.offsetWidth;
	   new_table.style.borderCollapse = 'collapse'; 
	   new_table.style.backgroundColor = '#FFFFFF'; 
	   new_table.style.fontFamily = 'Verdana, Arial, Helvetica, sans-serif';
	   new_table.style.fontSize = '12px';
	   
	   
	   

	   for(var i=0; i< tr_nodes.length; i++)
	   {	   
            var row_top_pos  = this.define_item_positon(tr_nodes[i]);
		    var row_bottom_pos  = row_top_pos + tr_nodes[i].offsetHeight; 
			
			 if(row_bottom_pos > max_pos)
		     {
				  this.level++;
				  var bottom_point = row_top_pos;
	
				  
				  var kids = [];
                  for(var x = tr_nodes[i]; x != null; x = x.nextSibling) if (x.nodeType == 1 /* Node.ELEMENT_NODE */) kids.push(x);

                  for(var i = 0; i < kids.length; i++) new_table.appendChild(kids[i]);
				  break;
			 }
			 
			 var bottom_point = row_bottom_pos;
			
	   }
	   
	   //alert(max_pos+' '+row_top_pos+' '+rest);
	   if(new_table.childNodes.length > 0){
		   var rest = max_pos - bottom_point;	  
	       tbl.style.margin = '0px 0px '+(rest+2)+'px 0px'; // + 'px';
	   
	       this.tbl_container_div.appendChild(img);
	       img.style.margin = '0px 0px 20px 0px';
	       this.tbl_container_div.appendChild(new_table);
		   
		   this.analyser(new_table);
	   }

	   
   }
   ConvertSpecOfferClass.prototype.define_item_positon = function(element){// определение расположения элемента на странице
	    var top = 0;
		while(element){
			top  += element.offsetTop;
			element = element.offsetParent;
		}
		return top;
   }
   var conv_spec_offer = new ConvertSpecOfferClass();
   //conv_spec_offer.start();