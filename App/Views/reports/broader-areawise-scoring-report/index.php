<?php require_once('form.php'); 

if( array_key_exists('data_array', $data['data']) && empty($data['data']['data_array']))
{
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

//has data
elseif( array_key_exists('data_array', $data['data']) && 
        is_array($data['data']['data_array']) && 
        sizeof($data['data']['data_array']) > 0 )
{
    echo '<div class="mb-3"></div>' . "\n";

    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);

        echo '<div class="table-responsive height-400">' . "\n";

        $res_markup = '<table class="table table-bordered v-table exportToExcelTable">
            <thead>
            <tr>
                <th class="vrt-header" rowspan="2">Audit Unit Code</th>
                <th class="vrt-header" rowspan="2">Branch</th>
                <th class="vrt-header" rowspan="2">Risk Type</th>
                <th class="vrt-header" rowspan="2">Category</th>
                <th rowspan="2">Broader Area of Audit Non-Compliance</th>
                <th colspan="6">Business Risk-High</th>
                <th colspan="6">Business Risk-Medium</th>
                <th colspan="6">Business Risk-Low</th>
                <th colspan="11">TOTAL SCORE</th>
            </tr>
            <tr>
                <th colspan="3">Qualitative Score</th>
                <th colspan="3">Quantitative Score</th>
                <th colspan="3">Qualitative Score</th>
                <th colspan="3">Quantitative Score</th>
                <th colspan="3">Qualitative Score</th>
                <th colspan="3">Quantitative Score</th>
                <th class="vrt-header" rowspan="2">Qualitative Score</th>
                <th class="vrt-header" rowspan="2">Quantitative Score</th>
                <th class="vrt-header" rowspan="2">Total Score Before Averaging</th>
                <th class="vrt-header" rowspan="2">No. Of Accounts Non-Compliant</th>
                <th class="vrt-header" rowspan="2">No Of Accounts Checked</th>
                <th class="vrt-header" rowspan="2">Averaged Quantitative Score</th>
                <th class="vrt-header" rowspan="2">Total Averaged Score</th>
                <th class="vrt-header" rowspan="2">Number of Audits Conducted</th>
                <th class="vrt-header" rowspan="2">Averaged Total Score Per Audit</th>
                <th class="vrt-header" rowspan="2">Risk Weight</th>
                <th class="vrt-header" rowspan="2">Weighted Score</th>
            </tr>

            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th class="vrt-header">High</th>
                <th class="vrt-header">Medium</th>
                <th class="vrt-header">Low</th>
                <th class="vrt-header">High</th>
                <th class="vrt-header">Medium</th>
                <th class="vrt-header">Low</th>
                <th class="vrt-header">High</th>
                <th class="vrt-header">Medium</th>
                <th class="vrt-header">Low</th>
                <th class="vrt-header">High</th>
                <th class="vrt-header">Medium</th>
                <th class="vrt-header">Low</th>
                <th class="vrt-header">High</th>
                <th class="vrt-header">Medium</th>
                <th class="vrt-header">Low</th>
                <th class="vrt-header">High</th>
                <th class="vrt-header">Medium</th>
                <th class="vrt-header">Low</th>
            </tr>
            </thead>
            <tbody id="tablebodycontainer">';

            foreach($data['data']['data_array'] as $cc_key => $cc_data)
            {        
                foreach($cc_data as $c_key => $c_data):
        
                if(array_key_exists($c_key, $data['data']['sortedBroaderAreaKeys']))
                {
                    foreach ($c_data['borader_area'] as $c_broader_area_id => $c_broader_area_details)
                    {
                        foreach ($c_broader_area_details['category'] as $c_risk_id => $c_risk_details)
                        {
                            $res_markup .= '<tr>
                                <td>'. $cc_data['audit_unit_code'] .'</td>
                                <td>'. $cc_data['branch_name'] .'</td>
                                <td>'. $c_risk_details['title'] .'</td>
                                <td>'. $c_data['title'] .'</td>
                                <td>'. $c_broader_area_details['name'] .'</td>';
        
                                $c_risk_array = array_chunk(array_keys($c_risk_details['qual']), ceil(count($c_risk_details['qual']) / 3));
                                
                                foreach($c_risk_array as $c_risk_matrix_keys_array)
                                {
                                    $c_quan_markup = '';
                                    foreach($c_risk_matrix_keys_array as $c_risk_matrix_keys)
                                    {
                                        $res_markup .= '<td>'. $c_risk_details['qual'][ $c_risk_matrix_keys ] .'</td>' . "\n";
                                        $c_quan_markup .= '<td>'. $c_risk_details['quan'][ $c_risk_matrix_keys ] .'</td>' . "\n";
                                    }
        
                                    $res_markup .= $c_quan_markup;
                                }
                                
                                $res_markup .= '<td>'. $c_risk_details['qual_tot'] .'</td>
                                <td>'. $c_risk_details['quan_tot'] .'</td>
                                <td>'. $c_risk_details['total_qual_quan'] .'</td>
                                <td>'. $c_risk_details['acc_non_compliant'] .'</td>
                                <td>'. $c_risk_details['no_of_acc_checked'] .'</td>
                                <td>'. $c_risk_details['avg_quan_score'] .'</td>
                                <td>'. $c_risk_details['tot_avg_score'] .'</td>
                                <td>'. $c_risk_details['no_of_audit_conduct'] .'</td>
                                <td>'. $c_risk_details['avg_tot_score_per_audit'] .'</td>
                                <td>'. $c_risk_details['risk_weight'] .'</td>
                                <td>'. $c_risk_details['weighted_score'] .'</td>
                            </tr>';
        
                        }
                    }
                }
        
                endforeach;
            }

            $res_markup .= '</tbody>
        </table>';

        echo $res_markup;
            
        echo '</div>' . "\n";

    echo '</div>' . "\n";
}

// print_r($data['data']['data_array']);

?>