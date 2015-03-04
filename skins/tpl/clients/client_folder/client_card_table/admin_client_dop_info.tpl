<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table>
                	<tr>
                    	<td>Дополнительная информация</td>
                    	<td><textarea placeholder="информация отсутствует" name=""><?php if(!empty($client['dop_info'])){echo $client['dop_info'];} ?></textarea>
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