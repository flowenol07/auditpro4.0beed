<?php

if(!empty($data['data']['risk_control_key_aspec_data'])):
    
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
                    <th style="width:20%" class="text-center">Sr. No.</th>
                    <th style="width:30%">Control Risk</th>
                    <th style="width:30%">Key Aspect</th>
                    <th style="width:20%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 1;
                    // print_r($data['data']['risk_control_key_aspec_data']);
                    foreach($data['data']['risk_control_key_aspec_data'] as $cKey => $cKeyAspectData)
                    {   
                        echo'
                        <tr>
                            <td style="width:20%" class="text-center">' . $i . '</td>
                            <td style="width:30%">' . ((array_key_exists($cKeyAspectData -> risk_control_id, $data['data']['risk_control_data'])) ? $data['data']['risk_control_data'][$cKeyAspectData -> risk_control_id] : ERROR_VARS['notFound'])  .'</td>
                            <td style="width:30%">' . $cKeyAspectData -> name .'</td>
                            <td style="width:20%" class="text-center">' . check_active_status($cKeyAspectData -> is_active) .'</td>
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