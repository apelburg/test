<style> .main_menu_tbl{ display:none; } </style>
<div  class="requisites_choice" style="margin-top:200px;">
    <div style="width:500px;padding:0px;margin:auto;border:1px solid #CCCCCC;box-shadow: 0 0 8px -1px #555555;-moz-box-shadow: 0 0 8px -1px #555555;-webkit-box-shadow: 0 0 8px -1px #555555;">
        <form method="GET" action="">
            <table class="choice_display_tbl">
                <tr>
                    <td colspan="2" style="position:relative">
                        <?php echo $caption; ?>
                        <hr>
                    </td>
                </tr>
                <tr>
                    <td>
                    <div class="subsection">Юр лицо клиента</div>
                    <?php echo $client_requisites; ?>
                    </td>
                    <td>
                    <div class="subsection">Юр лицо Апельбурга</div>
                    <?php echo $our_firms; ?>
                    </td>
                </tr>
                <tr>
                    <?php echo $varying_part; ?>
                </tr>
            </table>
        </form>
    </div>
</div>