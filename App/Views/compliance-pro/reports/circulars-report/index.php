<?php

if(!empty($data['data']['circular_data'])):
    
echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

?>
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:8%" class="text-center">Sr. No.</th>
                    <th style="width:20%">Authority</th>
                    <th style="width:60%">Circular Details</th>
                    <th style="width:12%" class="text-center">Applicability</th>
                    <th style="width:10%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    $srNo = 1;

                    foreach($data['data']['circular_data'] as $cCircularId => $cCircularData)
                    {   
                        $applicableStr = $cCircularData -> is_applicable == 1 ? 'Applicable' : 'Not Applicable';
                        echo '<tr>
                            <td style="width:8%" class="text-center">'. $srNo .'</td>
                            <td style="width:20%">'. $cCircularData -> auth_name .'</td>
                            <td style="width:60%">';
                            
                            echo '<p class="font-sm mb-0">Ref No. '. $cCircularData -> ref_no .'</p>' . "\n";
                            echo '<p class="mb-0">'. $cCircularData -> name .'</p>' . "\n";
                            
                        echo '</td>
                            <td style="width:12%" class="text-center">' . $applicableStr . '</td>
                            <td style="width:10%" class="text-center">' . check_active_status($cCircularData -> is_active) .'</td>
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
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>