// JavaScript Document
var up_window_consructor = {
	id:'',
	height:340,
	width:540,
    createDiv:function(height,width,position,top,bottom,left,right,padding,border,bgColor,id,textAlign){// 
	    var div = document.createElement('div');
		div.style.height = height + 'px';
		div.style.width =  width + 'px';
		div.style.position = position;
	    div.style.top = top + 'px';
		div.style.bottom = bottom + 'px';
		div.style.left = left + 'px';
		div.style.right = right + 'px';
		div.style.textAlign = textAlign;
		div.style.padding = padding;
		div.style.border = border;
		div.style.backgroundColor = bgColor;
		div.id = id;
		return div;
		
	},
	createElement:function(tagname,atrributes){// 
	    var element = document.createElement(tagname);
		for(var name in atrributes) element.setAttribute(name,atrributes[name]);
		return element;
		
	},
	createTable:function(height,borderSize){// 
	    var table = document.createElement('table');
		table.border = '0';
		table.style.borderCollapse = 'collapse';
		table.cellpadding = '0';
		table.cellspacing = '0';
		table.style.width = '100%';
		table.style.height = height;
		table.style.backgroundColor = 'none';
		var tr1 = document.createElement('tr');
		var tr2 = document.createElement('tr');
		var tr3 = document.createElement('tr');

		var td1_1 = document.createElement('td');
		var td1_2 = document.createElement('td');
		var td1_3 = document.createElement('td');
		var td2_1 = document.createElement('td');
		var td2_2 = document.createElement('td');
		var td2_3 = document.createElement('td');
		var td3_1 = document.createElement('td');
		var td3_2 = document.createElement('td');
		var td3_3 = document.createElement('td');
		
		td1_1.width = borderSize;
		td1_1.height = borderSize;
		td1_3.width = borderSize;
		td3_1.height = borderSize;
		td2_2.style.backgroundColor = '#FFFFFF';
		td2_2.style.verticalAlign = 'top';
		var div = document.createElement('div');
		div.style.overflow = 'auto';
		div.style.height = (height- 2*borderSize) + 'px';
		td2_2.appendChild(div);
		td1_1.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_left_top_edge.gif) no-repeat bottom right';
		td1_2.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_top_edge.gif) repeat-x bottom';
		td1_3.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_right_top_edge.gif) no-repeat bottom left';
		td2_1.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_left_edge.gif) repeat-y right';
		td2_3.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_right_edge.gif) repeat-y left';
		td3_1.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_left_bottom_edge.gif) no-repeat top right';
		td3_2.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_bottom_edge.gif) repeat-x top';
		td3_3.style.background ='url(../../skins/tpl/admin/order_manager/img/up_win_right_bottom_edge.gif) no-repeat top left';
			
			
		tr1.appendChild(td1_1);
		tr1.appendChild(td1_2);
		tr1.appendChild(td1_3);
		tr2.appendChild(td2_1);
		tr2.appendChild(td2_2);
		tr2.appendChild(td2_3);
		tr3.appendChild(td3_1);
		tr3.appendChild(td3_2);
		tr3.appendChild(td3_3);
		table.appendChild(tr1);
		table.appendChild(tr2);
		table.appendChild(tr3);
		return table;
		
	},
	detectCenterPosition:function(elementHeight,elementWidth){// определяет координаты центральной точки экрана
		var dimentions = [];
		// может не работать в некоторых браузерах
	    dimentions[0] = window.innerHeight;
		dimentions[1] = window.innerWidth;
		
		var centerPosition = [];
		centerPosition[0] = Math.round((dimentions[0]-elementHeight)/2) + 10 + window.pageYOffset;
		centerPosition[1] = Math.round((dimentions[1]-elementWidth)/2) - 10 + window.pageXOffset;
		return centerPosition;
		//alert(centerPosition[0] + ' ' + centerPosition[1]);
	},
	detectBodySize:function(){// определяет координаты центральной точки экрана
	    var dimentions = [];
		// может не работать в некоторых браузерах
	    dimentions[0] = document.body.scrollHeight;
		dimentions[1] = document.body.scrollWidth;
		return dimentions;
		//alert(dimentions[0]);
		
	},
	alignByWindow:function(){// выравнивает элемент по центру экрана
		
	},
	setWindowDimentions:function(height,width){// выравнивает элемент по центру экрана
		this.height = height;
		this.width = width;
	},
	closeWindow:function(){
		var id = up_window_consructor.id;
		document.getElementById(id  + 'conteiner').parentNode.removeChild(document.getElementById(id  + 'conteiner'));
		document.getElementById(id).parentNode.removeChild(document.getElementById(id));
		return false;
	},
	windowBilder:function(id){// выводит окно в браузер
	
	    this.id = id;
	    // div_bg
		var bodyDimentions = this.detectBodySize();
		var div_bg = this.createDiv(bodyDimentions[0],bodyDimentions[1],'absolute',0,'',0,'','0px','none','#CCCCCC',id,'');
		div_bg.style.filter = "alpha(opacity=70)";
	    div_bg.style.opacity = "0.70";
	    
		// div_conteiner
		var height = this.height;
		var width = this.width;
		var centerPosition = this.detectCenterPosition(height,width);
		var div_conteiner = this.createDiv(height,width,'absolute',centerPosition[0],'',centerPosition[1],'','0px','none','none',id + 'conteiner','');
		
		// div_close
		var div_close = this.createDiv(12,20,'absolute',6,'','',20,'0px','none','#FF0000','','center');
		var a_close = this.createElement('a',{ href:'#'});
		a_close.innerHTML = '&times;';
		a_close.style.textDecoration = 'none';
		a_close.style.fontFamily = 'arial';
		a_close.style.fontSize = '16px';
		a_close.style.lineHeight = '11px';
		a_close.style.fontWeight = '800';
		a_close.style.padding = '0px';
		a_close.style.color = '#FFFFFF';
		a_close.style.display = 'block';
		//a_close.style.backgroundColor = '#FF0000';
		
		a_close.onclick = this.closeWindow;
		div_close.appendChild(a_close);
		div_conteiner.appendChild(div_close);
		//
		var table = this.createTable(height - 40,16);
		//div_conteiner.appendChild(table);
		//document.body.appendChild(div_bg);
		//document.body.appendChild(div_conteiner);
		var arr = []; //[div_bg,div_conteiner];
		var arr = [div_bg,div_conteiner,table];
		//arr[0] = div_bg;
		//arr[1] = div_conteiner;
		return arr;
		
		
	}
}
