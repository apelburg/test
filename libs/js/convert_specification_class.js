// JavaScript Document

   function ConvertSpecificationClass (){
	   this.page_h = 1225;//980
	   this.container_h = this.page_h - 50; // запас чтобы не было в притык
	   
	   //alert(this.container_h );
	
   }
   
   ConvertSpecificationClass.prototype.start = function (){
	   //alert(1);
	   tbl = document.getElementById('spec_tbl');
	   if(!tbl) return;
	   //tbl.style.border = '#00FF00 solid 2px';
	   
	   this.container_div =  document.createElement('div');
	   //this.container_div.style.border = '#FF0000 solid 1px';
	   
	   
	
	   tbl.parentNode.insertBefore(this.container_div, tbl);
       this.container_div.appendChild(tbl);
	   
	   var tbl_pos =  this.define_item_positon(tbl);
	   this.level = Math.floor(tbl_pos/this.page_h)+1;
	   
	   if(location.search.indexOf('open=all')>0) return;
	   
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
   ConvertSpecificationClass.prototype.analyser = function (tbl){
	   
	   
	   //this.level = 1;
	   //
	   var tr_nodes = tbl.getElementsByTagName('tr');
	   
       
	  // alert(tr_nodes.length);
      
	   var max_pos = this.page_h*(this.level-1)+this.container_h;
	   //var max_pos = this.page_h*this.level;
	  
	   
	   //img.style.marginTop="-75px";
	   var new_table = document.createElement('table');
	   new_table.className = 'spec_tbl';
	  
	   
	   
	   

	   for(var i=0; i< tr_nodes.length; i++)
	   {	   
            var row_top_pos  = this.define_item_positon(tr_nodes[i]);
		    var row_bottom_pos  = row_top_pos + tr_nodes[i].offsetHeight; 
			
			 if(row_bottom_pos > max_pos)
		     {    
			      //alert(row_bottom_pos);
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
		   var rest = this.page_h*(this.level-1) - bottom_point;	  
	       tbl.style.margin = '0px 0px '+(rest+2)+'px 0px'; // + 'px';
	  
	       this.container_div.appendChild(new_table);
		   
		   this.analyser(new_table);
	   }

	   
   }
   ConvertSpecificationClass.prototype.define_item_positon = function(element){// определение расположения элемента на странице
	    var top = 0;
		while(element){
			top  += element.offsetTop;
			element = element.offsetParent;
		}
		return top;
   }
   var conv_specification = new ConvertSpecificationClass();
   //conv_spec_offer.start();