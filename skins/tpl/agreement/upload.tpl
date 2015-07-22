<!-- форма загрузки файлов -->
    <div align='left' style="float:left;margin-top:5px;padding:15px 18px 0px 10px;height:50px;width:300px; background:url(../../skins/tpl/admin/order_manager/img/download_form_bg_small.gif) no-repeat;position:relative;">
       <div style="border:#333333 solid 0px;">
        <form action="" method="POST" enctype="multipart/form-data">
          <div style="position:relative;">
             <div id="download_form_small_input_file_div">
                <input id="download_form_small_input_file" type="file" name="file" onchange="show_name_download_file(this.value,'small_');" size="15">
             </div>
             <div id="small_emul_input_file">
                <span>файл не выбран</span>
             </div>
             <input type="hidden" name="agreement_num" value="<?php echo $agreement['agreement_num']; ?>">
			 <input type="hidden" name="path" value="<?php echo $path; ?>">
			 <input type="hidden" name="id" value="<?php echo $agreement['id'];?>">
          </div>
          <div align="right">
             <button type="submit" name="upload_file" value="загрузить" style="background:none; padding:0px;margin:0px; border: #000000 solid 0px;cursor:pointer; width:76px; height:36px;">&nbsp;
             </button>
          </div>
        </form>
       </div>
    </div>
    <!-- /форма загрузки файлов -->