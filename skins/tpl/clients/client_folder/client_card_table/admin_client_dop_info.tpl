<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table  id="client_dop_information">
                	<tr>
                    	<td>Дополнительная информация</td>
                    	<td><?php echo (!empty($client['dop_info']))?$client['dop_info']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?>
                            </td>
                    </tr>
                	<tr>
                    	<td>Папка</td>
                    	<td><?php echo (!empty($client['ftp_folder']))?'Z:/'.$client['ftp_folder']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>' ?></td>
                    </tr>
                </table>
        	<td>
            	
            </td>
            <td>
            	
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>