<?php

if(!empty($data['data']['db_year_data']))
{
    echo '<div class="no-print mb-3">';
        require_once('form.php');
    echo '</div>';

    if(isset($data['data']['executive_summary_data'])  && !empty($data['data']['executive_summary_data']))
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
                        <th style="width:10%">Financial Year</th>
                        <th style="width:10%" class="text-center">Unit Code</th>
                        <th style="width:10%">Audit Unit Name</th>
                        <th style="width:10%">Type</th>
                        
                        <?php 
                            for($i = 4; $i <= 12; $i++)
                            {
                                echo '<th style="width:5%" class="text-center">Month '. $i .'</th>';
                                if( $i == 12) $i = 0;
                                if( $i == 3) break;
                            }
                        ?>
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

                            echo '<tr>
                                <td style="width:10%">' . $data['data']['db_year_data'][$cLastMarchData -> year_id] . '</td>
                                <td style="width:10%" class="text-center">' . $cLastMarchData -> audit_unit_id .'</td>

                                <td style="width:10%">'. ( isset($data['data']['db_audit_unit_data'][ $cLastMarchData -> audit_unit_id ]) ? $data['data']['db_audit_unit_data'][ $cLastMarchData -> audit_unit_id ] -> name : ERROR_VARS['notFound'] ) .'</td>

                                <td style="width:10%">'. BRANCH_FINANCIAL_POSITION[ $type ][ $cLastMarchData -> gl_type_id ] .'</td>';

                                for($i = 4; $i <= 12; $i++)
                                {
                                    $month = 'm_' . $i;
                                    echo '<td style="width:5%" class="text-center">'. $cLastMarchData -> $month .'</td>';
                                    if( $i == 12) $i = 0;
                                    if( $i == 3) break;
                                }

                            echo'</tr>';
                        }
                    ?>
                </tbody>

            </table>
        </div>

    <?php 

        echo '</div>' . "\n";

    }
    elseif(isset($data['data']['executive_summary_data']) && empty($data['data']['executive_summary_data']))
        echo '<div class="mt-2">' . 
        $data['noti']::getCustomAlertNoti('noDataFound') . '
        </div>';
}
else
    echo $data['noti']::getCustomAlertNoti('noDataFound');
?>