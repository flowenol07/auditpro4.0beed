<?php

if(!empty($data['data']['db_year_data'])) {

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';
    
if(isset($data['data']['target_data'])  && !empty($data['data']['target_data']))
{   
    $fy = $data['data']['db_year_data'][$data['data']['target_data'][0] -> year_id];

    echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data'], true, $fy);
?>
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:15%">Financial Year</th>
                    <th style="width:10%" class="text-center">Unit Code</th>
                    <th style="width:30%">Audit Unit Name</th>
                    <th style="width:15%">Annual Incremental Deposit Target (In Lakhs)</th>
                    <th style="width:15%">Annual Incremental Advances Target (In Lakhs)</th>
                    <th style="width:15%">Annual Differential NPA Target (In Lakhs)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['target_data'] as $cKey => $cTargetData)
                    {  
                        echo '<tr>
                            <td style="width:15%">' . $data['data']['db_year_data'][ $cTargetData -> year_id ] . '</td>
                            <td style="width:10%" class="text-center">' . $cTargetData -> audit_unit_id .'</td>

                            <td style="width:30%">' . ($data['data']['db_audit_unit_data'][ $cTargetData -> audit_unit_id ] ?? ERROR_VARS['notFound']) .'</td>

                            <td style="width:15%">' . $cTargetData -> deposit_target .'</td>
                            <td style="width:15%">' . $cTargetData -> advances_target .'</td>
                            <td style="width:15%">' . $cTargetData -> npa_target .'</td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
<?php 

    echo '</div>' . "\n";

    }
    elseif(isset($data['data']['target_data'])  && empty($data['data']['target_data']))
        echo '<div class="mt-2">' . 
        $data['noti']::getCustomAlertNoti('noDataFound') . '
        </div>';
}
else {
    echo $data['noti']::getCustomAlertNoti('noDataFound');
}

?>