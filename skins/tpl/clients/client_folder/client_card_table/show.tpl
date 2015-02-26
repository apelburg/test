<style>
#content_general_header{ border-bottom:1px solid #595243;font-size: 12px;color:#595652; padding: 0 20px}
#content_general{ padding:15px 10px; background-color:#d6d6d6}
.border_in_table{border-bottom:1px solid #979694; width:100%; margin-left:0}
#content_general_header table td{ padding:0 5px; font-size:12px; }
#content_general table td{ padding:5px; font-size:12px; }
#content_general_header table,#content_general table{ width:100%;}

#content_general_header table tr td:nth-of-type(1),#content_general table tr td:nth-of-type(1){ width:50%;}
#content_general_header table tr td:nth-of-type(2),#content_general table tr td:nth-of-type(2){ width:25%;}
#content_general table tr td:nth-of-type(1) table tr td:nth-of-type(1){ width: 7%;min-width: 150px;}
#content_general table tr td:nth-of-type(2) table tr td:nth-of-type(1){ width: 7%;min-width: 50px;}
#content_general table tr td:nth-of-type(3) table tr td:nth-of-type(1){ width: 3%;min-width: 20px;}
.client_table{ background:#fff; padding: 5px 10px}
.client_table .client_table_gen table tr td:nth-of-type(1){color:#7f7e7c}
#content_general table tr td.td_phone{width: 20%}
#content_general table tr td.td_icons{width: 20%}
.white_bg{ background: #fff; padding-bottom: 50px}
#content_general table tr td:nth-of-type(2),#content_general table tr td:nth-of-type(3){vertical-align: baseline;}
#content_general table tr td:nth-of-type(2) table tr td,#content_general table tr td:nth-of-type(3) table tr td{vertical-align: middle;}

input[type="text"],textarea{ font-family: Arial, Helvetica, sans-serif; font-size: 12px; width: 100%; float: left; }

textarea{ min-height: 70px}
</style>

<div id="content_general_header">
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>Компания/контактное лицо</td>
                    </tr>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td>Телефоны:</td>
                    </tr>
                </table>
            </td>
            <td>Сайт/почта/интернет</td>
        </td>
    </td>
    </tr>
    </table>
</div>
<div id="content_general">
    <div class="white_bg">
        <?php echo $client_content; ?>
        <?php echo $client_content_contact_faces; ?>
    </div>
</div>