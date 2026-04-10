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

        $res_markup = '<p class="mb-0"><span class="font-medium">Audit Unit:</span> '. string_operations((is_object($data['data']['audit_details']) ? $data['data']['audit_details'] -> name : '-'), 'upper') .'</p>' . "\n";

        if(in_array($data['data']['select_search_type'], [3, 4]) && !empty($data['data']['period']))
            $res_markup .= '<p class="mb-3"><span class="font-medium">Assesment Period:</span> '. $data['data']['period'] .'</p>' . "\n";

        if(in_array($data['data']['select_search_type'], [5, 6]) && !empty($data['data']['period']))
            $res_markup .= '<p class="mb-3"><span class="font-medium">Period:</span> '. $data['data']['period'] .'</p>' . "\n";

        $res_markup .= '<div class="table-responsive">' . "\n";

        $res_markup .= '<table id="exportToExcelTable" class="table table-bordered v-table">
            <thead>
                <tr>
                    <th style="width:10%" class="text-center">Branch Code</th>
                    <th style="width:20%">Branch Name</th>
                    <th style="width:10%">Category</th>
                    <th style="width:15%">Risk Type</th>
                    <th style="width:10%" class="text-center">Total Score</th>
                    <th style="width:10%">Number of Audits Conducted</th>
                    <th style="width:10%">Averaged Total Score Per Audit</th>
                    <th style="width:5%" class="text-center">Risk Weight</th>
                    <th style="width:10%">Weighted Score</th>
                    <th style="width:10%">% To Total Weighted Score</th>
                </tr>
            </thead>
            <tbody id="tablebodycontainer">';

            $total_avg_score = 0;
            $total_weighted_score = 0;
            $tmp = 0;

            foreach($data['data']['sortedBroaderAreaKeys'] as $c_gen_key => $c_gen_cat_details)
            {
                if(array_key_exists($c_gen_key, $data['data']['data_array']))
                {
                    foreach($data['data']['risk_category'] as $c_lov_id => $c_lov_details)
                    {
                        if(!array_key_exists($c_lov_id, $data['data']['data_array'][ $c_gen_key ]))
                        {
                            $data['data']['data_array'][ $c_gen_key ][ $c_lov_id ] = array(
                                'title' => $c_lov_details -> risk_category,
                                'no_of_audit_conduct' => $data['data']['no_of_assessment'],
                                'risk_weight' =>  0,
                                'total_qual_quan' => 0, 'tot_avg_score' => 0,
                                'avg_tot_score_per_audit' => 0,
                                'weighted_score' => 0
                            );
                        }

                        $c_risk_data = $data['data']['data_array'][ $c_gen_key ][ $c_lov_id ];

                        $res_markup .= '<tr>';

                            // Branch Code
                            $res_markup .= '<td style="width:10%" class="text-center">'. (is_object($data['data']['audit_details']) ? $data['data']['audit_details'] -> audit_unit_code : '-') .'</td>';

                            // Branch Name
                            $res_markup .= '<td style="width:20%">'. string_operations((is_object($data['data']['audit_details']) ? $data['data']['audit_details'] -> name  : '-'), 'upper') .'</td>';

                            // Catergory
                            $res_markup .= '<td style="width:10%">'. string_operations($c_gen_key, 'upper') .'</td>';

                            // Risk Type
                            $res_markup .= '<td style="width:15%">'. $c_risk_data['title'] .'</td>';

                            // Total Score
                            $res_markup .= '<td style="width:10%" class="text-center">'. get_decimal($c_risk_data['tot_avg_score'], 2) .'</td>';

                            // Number of Audits Conducted
                            $res_markup .= '<td style="width:10%" class="text-center">'. $data['data']['no_of_assessment'] .'</td>';

                            // Averaged Total Score Per Audit
                            $res_markup .= '<td style="width:10%" class="text-right">'. get_decimal($c_risk_data['avg_tot_score_per_audit'], 2) .'</td>';

                            // Risk Weight
                            $res_markup .= '<td style="width:5%" class="text-center">'. get_decimal($c_lov_details -> risk_weightage, 0) .'</td>';

                            // Weighted Score
                            $res_markup .= '<td style="width:10%" class="text-right">'. get_decimal($c_risk_data['weighted_score'], 2) .'</td>';

                            // % To Total Weighted Score

                            $current_weighted_score = 0;

                            if($c_risk_data['weighted_score'] > 0 && $data['data']['tot_weighted_score'] > 0)
                            {
                                $current_weighted_score = ($c_risk_data['weighted_score'] / $data['data']['tot_weighted_score']) * 100;
                            }

                            $total_weighted_score += $current_weighted_score;

                            $res_markup .= '<td style="width:10%" class="text-right">'. get_decimal( $current_weighted_score, 2 ) .'</td>';

                            $total_avg_score += get_decimal($c_risk_data['avg_tot_score_per_audit'], 2);
                            // $total_weighted_score += convert_to_float($c_risk_data['weighted_score']);

                            $tmp += get_decimal( $c_risk_data['tot_avg_score'], 2 );

                        $res_markup .= '</tr>';

                    }
                }

            }

            $res_markup .= '<tr>
                <td colspan="8" class="font-medium text-center">Total</td>
                <td class="font-medium text-right">'. get_decimal($data['data']['tot_weighted_score'], 2) .'</td>
                <td class="font-medium text-right">'. get_decimal($total_weighted_score, 2) .'</td>
            </tr>';

            $res_markup .= '</tbody>
        </table>';

        $res_markup .= '</div>' . "\n";

        echo $res_markup;

    echo '</div>' . "\n";
}

// print_r($data['data']['data_array']);

?>