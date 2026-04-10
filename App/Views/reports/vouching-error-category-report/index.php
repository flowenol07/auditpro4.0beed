<?php
if(!empty($data['data']['annex_col_data'])):
    
echo '<div class="no-print mb-3">';
    generate_report_buttons(['print', 'excel']); 
echo '</div>';

echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

?>
    <div class="table-responsive">
        <table class="table table-bordered v-table mt-2 exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:20%" class="text-center">Vouching Error Code</th>
                    <th style="width:80%">Vouching Error Description</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 1;
                    foreach(json_decode($data['data']['annex_col_data'] -> column_options) as $cKey => $cKeyVoucColData)
                    {   
                        echo'
                        <tr>
                            <td style="width:20%" class="text-center">' . $i . '</td>
                            <td style="width:80%">' . $cKeyVoucColData -> column_option .'</td>
                        </tr>';

                        $i++;
                    }
                ?>
            </tbody>
        </table>
    </div>
<?php

echo '</div>' . "\n";

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');;
endif;
?>