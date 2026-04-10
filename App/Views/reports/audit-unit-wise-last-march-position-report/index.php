<?php

if(!empty($data['data']['db_year_data'])) {

    echo '<div class="no-print mb-3">';
        require_once('form.php');
    echo '</div>';

    if(isset($data['data']['executive_summary_data']) && !empty($data['data']['executive_summary_data']))
    {   
        $fy = $data['data']['db_year_data'][$data['data']['executive_summary_data'][0] -> year_id];

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
                        <th style="width:30%">Type</th>
                        <th style="width:15%">March Position</th>
                    </tr>
                </thead>

                <tbody>
                    <?php        
                        foreach($data['data']['executive_summary_data'] as $cKey => $cLastMarchData)
                        {  
                            $depositsKeys = array_keys(BRANCH_FINANCIAL_POSITION['deposits']);
                            $advancesKeys = array_keys(BRANCH_FINANCIAL_POSITION['advances']);
                            $npaKeys = array_keys(BRANCH_FINANCIAL_POSITION['npa']);

                            if($cLastMarchData -> gl_type_id <= $depositsKeys[sizeof($depositsKeys) - 1])
                                $type = 'deposits';
                            elseif($cLastMarchData -> gl_type_id > $depositsKeys[sizeof($depositsKeys) - 1] && $cLastMarchData -> gl_type_id <= $advancesKeys[sizeof($advancesKeys) - 1] )
                                $type = 'advances';
                            elseif($cLastMarchData -> gl_type_id > end($advancesKeys) && $cLastMarchData -> gl_type_id <= end($npaKeys))
                                $type = 'npa';

                            echo
                            '<tr>
                                <td style="width:15%">'. $data['data']['db_year_data'][$cLastMarchData -> year_id] . '</td>
                                <td style="width:10%" class="text-center">' . $cLastMarchData -> audit_unit_id .'</td>

                                <td style="width:30%">'. ( isset($data['data']['db_audit_unit_data'][ $cLastMarchData -> audit_unit_id ]) ? $data['data']['db_audit_unit_data'][ $cLastMarchData -> audit_unit_id ] -> name : ERROR_VARS['notFound'] ) .'</td>

                                <td style="width:30%">'. BRANCH_FINANCIAL_POSITION[ $type ][ $cLastMarchData -> gl_type_id ].'</td>
                                <td style="width:15%">'. get_decimal($cLastMarchData -> march_position, 2) .' Lakhs</td>
                            </tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>

<?php
        echo '</div>' . "\n";
    }
    elseif(isset($data['data']['executive_summary_data'])  && empty($data['data']['executive_summary_data']))
    {
        echo '<div class="mt-2">' . 
            $data['noti']::getCustomAlertNoti('noDataFound') . '
        </div>';
    }
}
else
    echo $data['noti']::getCustomAlertNoti('noDataFound');

?>