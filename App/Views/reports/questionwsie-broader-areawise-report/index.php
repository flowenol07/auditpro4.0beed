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

    // generate header function
    generate_report_header($data['data']);

    echo '<div class="table-responsive height-400">' . "\n";

    $res_markup = '<table class="table table-bordered v-table exportToExcelTable">
        <thead>
        <tr>
            <th rowspan="2">Audit Unit Code</th>
            <th rowspan="2">Branch</th>
            <th rowspan="2">Risk Type</th>
            <th rowspan="2">Category</th>
            <th rowspan="2">Broader Area of Audit Non-Compliance</th>
            <th rowspan="2">Menu & Category</th>
            <th rowspan="2">Questions</th>
            <th colspan="6">TOTAL SCORE</th>
        </tr>

         <tr>
            <th>Total Averaged Score</th>
            <th>Number of Audits Conducted</th>
            <th>Averaged Total Score Per Audit</th>
            <th>Risk Weight</th>
            <th>Weighted Score</th>
            <th>Total Broader Area Weighted Score</th>
        </tr>
        </thead>
        <tbody id="tablebodycontainer">';

        // print_r($data['data']['data_array']);

        $total_score_final = 0;

        foreach ($data['data']['data_array'] as $cc_key => $cc_data) {

            foreach ($cc_data as $c_key => $c_data) {
        
                if (array_key_exists($c_key, $data['data']['sortedBroaderAreaKeys'])) {
        
                    foreach ($c_data['borader_area'] as $c_broader_area_id => $c_broader_area_details) {
        
                        foreach ($c_broader_area_details['category'] as $c_risk_id => $c_risk_details) {

                            // Calculate rowspan based on the number of questions
                            $rowspan = isset($c_risk_details['question_cnt']) ? $c_risk_details['question_cnt'] : 1;
    
                            // Start building the table row
                            $res_markup .= '<tr>';
    
                            $res_markup .= '<td rowspan="' . $rowspan . '">' . $cc_data['audit_unit_code'] . '</td>';
                            $res_markup .= '<td rowspan="' . $rowspan . '">' . $cc_data['branch_name'] . '</td>';
                            $res_markup .= '<td rowspan="' . $rowspan . '">' . $c_risk_details['risk_category_details']['title'] . '</td>';
                            $res_markup .= '<td rowspan="' . $rowspan . '">' . $c_data['title'] . '</td>';
                            $res_markup .= '<td rowspan="' . $rowspan . '">' . $c_broader_area_details['name'] . '</td>';
    
                            // Initialize arrays to hold data for each question
                            $td_question_data = [];
                            $td_category_data = [];
                            $td_dump_data = [];
                            $td_risk_data = [];
    
                            foreach ($c_risk_details['questions'] as $c_ques_id => $c_ques_details) {
                                // Check if the current question has answers
                                if (isset($c_ques_details) && is_array($c_ques_details)) {
                                    foreach ($c_ques_details as $c_ans_id => $c_ans_details) {
                                        if ($c_ques_id > 0 && isset($c_ans_details['id']) && !in_array($c_ques_id, $td_question_data)) {
                                            $td_question_data[] = $c_ques_id;
                                        }
    
                                        if (isset($c_ans_details['category_id'])) {
                                            $td_category_data[$c_ques_id] = $c_ans_details['category_id'];
                                        }
    
                                        if (isset($c_ans_details['dump_id'])) {
                                            // Handle deposit or advance data accordingly
                                            if ($c_data['title'] == 'Deposits' && $c_ans_details['dump_id'] > 0) {
                                                $td_dump_data[$c_ques_id][] = $data['data']['deposits_data'][$c_ans_details['dump_id']];
                                            } elseif ($c_data['title'] == 'Advances' && $c_ans_details['dump_id'] > 0) {
                                                $td_dump_data[$c_ques_id][] = $data['data']['advances_data'][$c_ans_details['dump_id']];
                                            }
                                        }
    
                                        if (isset($c_ans_details['tot_avg_score']) &&
                                            isset($c_ans_details['no_of_audit_conduct']) &&
                                            isset($c_ans_details['risk_weight']) &&
                                            isset($c_ans_details['weighted_score'])) {
                                            // Initialize the array if it doesn't exist yet
                                            if (!isset($td_risk_data[$c_ques_id])) {
                                                $td_risk_data[$c_ques_id] = [
                                                    'tot_avg_score' => 0,
                                                    'no_of_audit_conduct' => 0,
                                                    'risk_weight' => 0,
                                                    'weighted_score' => 0
                                                ];
                                            }
    
                                            // Accumulate values from $c_ans_details to $td_risk_data[$c_ques_id]
                                            $td_risk_data[$c_ques_id]['tot_avg_score'] += $c_ans_details['tot_avg_score'];
                                            $td_risk_data[$c_ques_id]['no_of_audit_conduct'] += $c_ans_details['no_of_audit_conduct'];
                                            $td_risk_data[$c_ques_id]['risk_weight'] = $c_ans_details['risk_weight']; // Update risk weight per question
                                            $td_risk_data[$c_ques_id]['weighted_score'] += $c_ans_details['weighted_score'];
                                        }
                                    }
                                }
                            }
    
                            // Output data for the first question
                            if (isset($c_risk_details['questions']) && sizeof($c_risk_details['questions']) > 0) 
                            {
                                $res_markup .= '<td rowspan="' . $rowspan . '">' . '<strong>Menu : </strong>' . $data['data']['menu_data'][$data['data']['category_menu_data'][$td_category_data[$td_question_data[0]]]] . ' <br><strong>Category : </strong>' . $data['data']['category_data'][$td_category_data[$td_question_data[0]]] . '</td>';
    
                                $acct_str = '';
    
                                if (isset($td_dump_data[$td_question_data[0]])) {
                                    foreach ($td_dump_data[$td_question_data[0]] as $c_acct_id => $c_acct_data) {
                                        $acct_str .= $c_acct_data . ", ";
                                    }
                                }
    
                                $res_markup .= '<td>' . (isset($td_dump_data[$td_question_data[0]]) ? (  '<strong>Accounts : </strong>' . $acct_str . '<br><br>') : '') . $data['data']['question_data'][$td_question_data[0]] . '</td>';
    
                                $total_avg_score = get_decimal($td_risk_data[$td_question_data[0]]['tot_avg_score'], 2);

                                // $no_of_audit_conduct = $td_risk_data[$td_question_data[0]]['no_of_audit_conduct'];
                                $no_of_audit_conduct = sizeof($cc_data['no_of_audits']);

                                $avg_tot_score_per_audit = get_decimal(($td_risk_data[$td_question_data[0]]['tot_avg_score'] / $no_of_audit_conduct), 2);

                                $risk_weight = get_decimal($td_risk_data[$td_question_data[0]]['risk_weight'], 2);

                                $weighted_score = get_decimal(($avg_tot_score_per_audit * $td_risk_data[$td_question_data[0]]['risk_weight']), 2);

                                if(isset($c_risk_details['risk_category_details']) && isset($c_risk_details['risk_category_details']['weighted_score']))
                                    $total_score = get_decimal(($c_risk_details['risk_category_details']['weighted_score'] * $rowspan), 2);
                                else
                                    $total_score = 0.00;

                                $total_score_final += $total_score;                                

                                // $total_score = get_decimal($weighted_score, 2);
    
                                $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[0]]) ? ($total_avg_score) : '') . '</td>';
                                $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[0]]) ? ($no_of_audit_conduct) : '') . '</td>';
                                $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[0]]) ? ($avg_tot_score_per_audit) : '') . '</td>';
                                $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[0]]) ? ($risk_weight) : '') . '</td>';
                                $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[0]]) ? ($weighted_score) : '') . '</td>';
                                
                                $res_markup .= '<td rowspan="' . $rowspan . '">' . ((isset($c_risk_details['risk_category_details']) && isset($c_risk_details['risk_category_details']['weighted_score'])) ? (get_decimal(($c_risk_details['risk_category_details']['weighted_score'] * $rowspan), 2)) : '') . '</td>';
                            }
    
                            $res_markup .= '</tr>';
    
                            // Output data for additional questions if any
                            if (isset($c_risk_details['questions']) && sizeof($c_risk_details['questions']) > 1) {
                                for ($i = 1; $i < sizeof($td_question_data); $i++) {
                                    $acct_str = '';
    
                                    if (isset($td_dump_data[$td_question_data[$i]])) {
                                        foreach ($td_dump_data[$td_question_data[$i]] as $c_acct_id => $c_acct_data) {
                                            $acct_str .= $c_acct_data . ", ";
                                        }
                                    }
    
                                    $res_markup .= '<tr>';

                                    $res_markup .= '<td>' . (isset($td_dump_data[$td_question_data[$i]]) ? ('<strong>Accounts : </strong>' . $acct_str . '<br><br>') : '') . $data['data']['question_data'][$td_question_data[$i]] . '</td>';
    
                                    $total_avg_score = get_decimal($td_risk_data[$td_question_data[$i]]['tot_avg_score'], 2);

                                    // $no_of_audit_conduct = $td_risk_data[$td_question_data[$i]]['no_of_audit_conduct'];
                                    $no_of_audit_conduct = sizeof($cc_data['no_of_audits']);

                                    $avg_tot_score_per_audit = get_decimal(($td_risk_data[$td_question_data[$i]]['tot_avg_score'] / $no_of_audit_conduct), 2);

                                    $risk_weight = get_decimal($td_risk_data[$td_question_data[$i]]['risk_weight'], 2);

                                    $weighted_score = get_decimal(($avg_tot_score_per_audit * $td_risk_data[$td_question_data[$i]]['risk_weight']), 2);

                                    // $total_score = get_decimal($weighted_score + $total_score, 2);
    
                                    $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[$i]]) ? ($total_avg_score) : '') . '</td>';

                                    $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[$i]]) ? ($no_of_audit_conduct) : '') . '</td>';

                                    $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[$i]]) ? ($avg_tot_score_per_audit) : '') . '</td>';

                                    $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[$i]]) ? ($risk_weight) : '') . '</td>';

                                    $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[$i]]) ? ($weighted_score) : '') . '</td>';

                                    // $res_markup .= '<td>' . (isset($td_risk_data[$td_question_data[$i]]) ? ($total_score) : '') . '</td>';

                                    // $total_score_final += $total_score;

                                    $res_markup .= '</tr>';
                                }
                            }
                        }
                    }
                }
            }
        }

        $res_markup .= '<tr>
                            <td colspan="12" class="text-center">Total</td>
                            <td>' . get_decimal($total_score_final, 2) . '</td>
                        </tr>';

        $res_markup .= '</tbody>
    </table>';

    echo $res_markup;
        
    echo '</div>' . "\n";
}
// print_r($td_risk_data);

?>