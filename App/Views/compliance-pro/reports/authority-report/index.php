<?php

if(!empty($data['data']['authority_data']['data'])):

    echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

    echo '<div class="hide-this mb-3">' . "\n";
        $btnArray = array('print');
        generate_report_buttons($btnArray);
    echo '</div>' . "\n";

?>
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:10%" class="text-center">Sr. No.</th>
                    <th style="width:70%">Authority</th>
                    <th style="width:20%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    $srNo = 1;

                    foreach($data['data']['authority_data']['data'] as $cAuthData)
                    {   
                        echo '<tr>
                            <td style="width:10%" class="text-center">' . $srNo . '</td>
                            <td style="width:70%">'. $cAuthData -> name .'</td>
                            <td style="width:20%" class="text-center">' . check_active_status($cAuthData -> is_active) .'</td>
                        </tr>';

                        $srNo++;
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