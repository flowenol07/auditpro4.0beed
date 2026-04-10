<?php

if(!empty($data['data']['annex_master_data'])):

echo '<div class="no-print mb-3">';
   require_once('form.php');
echo '</div>';

echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data']);
?>
    <div class="table-responsive">
        <table class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:10%" class="text-center">Annexure Id</th>
                    <th style="width:25%">Annexure Name</th>
                    <th style="width:10%">Associated Risk Category</th>
                    <th style="width:10%">Associated Business Risk</th>
                    <th style="width:10%">Associated Control Risk</th>
                    <th style="width:25%">Column Names</th>
                    <th style="width:10%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['annex_master_data'] as $cKey => $cAnnexMastData)
                    {   echo
                        '<tr>
                            <td style="width:10%" class="text-center">' . $cAnnexMastData -> id . '</td>
                            <td style="width:25%">'. $cAnnexMastData -> name .'</td>
                            <td style="width:10%">'. ((array_key_exists($cAnnexMastData -> risk_category_id, $data['data']['risk_category_select_data'])) ? $data['data']['risk_category_select_data'][$cAnnexMastData -> risk_category_id] : ERROR_VARS['notFound']) .'</td>  
                            <td style="width:10%">'. (isset(RISK_PARAMETERS_ARRAY[$cAnnexMastData -> business_risk]['title']) ? RISK_PARAMETERS_ARRAY[$cAnnexMastData -> business_risk]['title'] : '-') .'</td>
                            <td style="width:10%">'. (isset(RISK_PARAMETERS_ARRAY[$cAnnexMastData -> control_risk]['title']) ? RISK_PARAMETERS_ARRAY[$cAnnexMastData -> control_risk]['title'] : '-').'</td>

                            <td style="width:25%">';                    
                            if(isset($cAnnexMastData -> column_data) && is_array($cAnnexMastData -> column_data))
                            {
                                $col_names = '';
                                foreach($cAnnexMastData -> column_data as $dColId => $dColData)
                                {
                                    $col_names .= '<span class="d-inline-block"> { ' . $cAnnexMastData -> column_data[$dColId] -> name . ' }</span><br>';
                                }
                                echo substr(trim_str($col_names), 0, -1);
                            }
                            else    
                                echo '-';
                            
                            echo '</td> 
                            <td style="width:10%" class="text-center">'. check_active_status($cAnnexMastData -> is_active) .'</td>
                        </tr>';
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