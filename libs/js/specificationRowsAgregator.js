// JavaScript Document
    var specificationRowsAgregator = {
		bg:['#65db56','#FFCC33','#996600','#6633FF','#FF0066','#003366','#FF0000','#990066'],//['#65db56','#017f47']
		counter:0,//this.bg.length
		all_checkboxes:null,
		data_buffer:[],
		define_checkboxes:function(){
			var tbl = document.getElementById('specification_tbl');
			var inputs = tbl.getElementsByTagName('input');
			//alert(inputs.length);
			for( var i =0 , all_checkboxes = [] ; i < inputs.length; i++) if(inputs[i].getAttribute('agregated')) all_checkboxes.push(inputs[i]);
			this.all_checkboxes = all_checkboxes;
			//alert(this.all_checkboxes.length);
		}
		,
		set:function(){
			if(!this.all_checkboxes) this.define_checkboxes();
			var all_checkboxes = this.all_checkboxes;
			
			for( var i =0 , avail_checkboxes = [] ; i < all_checkboxes.length; i++){
				if(all_checkboxes[i].getAttribute('agregated') && all_checkboxes[i].getAttribute('agregated') == 'no') avail_checkboxes.push(all_checkboxes[i]);
			}
			
			var data_buffer_length = this.data_buffer.length;
			for( var i =0; i < avail_checkboxes.length; i++){
				if(avail_checkboxes[i].checked == true){
					avail_checkboxes[i].setAttribute('agregated','yes');
					if(!this.data_buffer[data_buffer_length])this.data_buffer[data_buffer_length] =[];
					this.data_buffer[data_buffer_length].push(avail_checkboxes[i].getAttribute('row_id'));
					avail_checkboxes[i].parentNode.style.backgroundColor = this.bg[this.counter%this.bg.length];
				}
			}
			//this.bg.reverse();
			this.counter++;
			
			this.drop_checkboxes(all_checkboxes);
			
			//console.log(this.data_buffer);
		}
		,
		drop_checkboxes: function(checkboxes){
			for( var i =0; i < checkboxes.length; i++) checkboxes[i].checked = false;
		}
		,
		reset_all: function(){
			if(!this.all_checkboxes) this.define_checkboxes();
			
			for( var i =0; i < this.all_checkboxes.length; i++){
				this.all_checkboxes[i].setAttribute('agregated','no');
				this.all_checkboxes[i].checked = false;
				this.data_buffer = [];
				this.all_checkboxes[i].parentNode.style.backgroundColor = '#FFF';
			}
		}
		,
		send_changes:function(doc_type){
			if(!this.data_buffer.length){
				alert('Выберите строки для объединения')
				return;
			}
			var data_buffer = this.data_buffer;
			
			var arr = [];
			for( var i =0, s =0; i < data_buffer.length; i++){
				for( var j =0; j < data_buffer[i].length; j++) arr[s++]= 'data['+ i + ']['+ j + ']=' + data_buffer[i][j];
			}
			if(!confirm('Выбранные строки будут объеденены, отмена будет не возможна')) return;
			// alert('&agregate_doc_rows=' + doc_type + '&' + arr.join('&'));
			location = location + '&agregate_doc_rows=' + doc_type + '&' + arr.join('&');
		}
	}