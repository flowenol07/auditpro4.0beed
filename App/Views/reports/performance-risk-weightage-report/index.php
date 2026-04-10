<?php require_once('form.php'); 

if( array_key_exists('data_array', $data['data']) && empty($data['data']['data_array']))
{
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

//has data
elseif( array_key_exists('data_array', $data['data']) && 
        is_array($data['data']['data_array']))
{
    echo '<div class="mb-3"></div>';

    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);

        $resMarkupArray = array( 'markup' => '');

        $totArray = [
            'total_questions' => 0,
            'total_na_questions' => 0,
            'total_questions_t1' => 0,
            'total_annex_t2' => 0,
            'total_highest_score' => 0,
            'total_highest_score_weighted' => 0,
            'total_obtained_score' => 0,
            'total_obtained_score_weighted' => 0,
            'to_score' => 0
        ];

        $resMarkup = '';
        $srNo = 1;

        foreach($data['data']['data_array']['risk_category'] as $cRiskId => $cRiskData)
        {
            if(array_key_exists($cRiskId, $data['data']['has_data']))
            {
                $resMarkup .= '<tr>';

                    // sr no
                    $resMarkup .= '<td class="text-center">'. $srNo .'</td>';

                    // risk category
                    $resMarkup .= '<td>'. string_operations($cRiskData['risk_category'], 'upper') .'</td>';

                    // risk weight
                    $resMarkup .= '<td class="text-center">'. get_decimal($cRiskData['risk_weightage'], 0) .'</td>';

                    $totArray['total_questions'] += $cRiskData['total_questions'];
                    $resMarkup .= '<td align="right">'. $cRiskData['total_questions'] .'</td>';

                    $totArray['total_na_questions'] += $cRiskData['total_na_questions'];
                    $resMarkup .= '<td align="right">'. $cRiskData['total_na_questions'] .'</td>';

                    $totArray['total_questions_t1'] += $cRiskData['total_questions_t1'];
                    $resMarkup .= '<td align="right">'. $cRiskData['total_questions_t1'] .'</td>';

                    $totArray['total_annex_t2'] += $cRiskData['total_annex_t2'];
                    $resMarkup .= '<td align="right">'. $cRiskData['total_annex_t2'] .'</td>';

                    $resMarkup .= '<td align="right">'. ($cRiskData['total_questions_t1'] + $cRiskData['total_annex_t2']) .'</td>';

                    $totArray['total_highest_score'] += $cRiskData['total_highest_score'];
                    $totArray['total_highest_score_weighted'] += $cRiskData['total_highest_score_weighted'];
                    $resMarkup .= '<td align="right">'. get_decimal($cRiskData['total_highest_score_weighted'], 2) .'</td>';

                    $totArray['total_obtained_score'] += $cRiskData['total_obtained_score'];
                    $totArray['total_obtained_score_weighted'] += $cRiskData['total_obtained_score_weighted'];
                    $resMarkup .= '<td align="right">'. get_decimal($cRiskData['total_obtained_score_weighted'], 2) .'</td>';

                    $toScore = 0;

                    if( $cRiskData['total_obtained_score_weighted'] > 0 &&
                        $data['data']['data_array']['total_obtained_score_weighted'] > 0)
                    {
                        $toScore = $cRiskData['total_obtained_score_weighted'] / $data['data']['data_array']['total_obtained_score_weighted'];
                        $totArray['to_score'] += $toScore;
                    }

                    $resMarkup .= '<td align="right">'. get_decimal($cRiskData['relative_performance'], 2) .'</td>';
                    $resMarkup .= '<td align="right">'. get_decimal(($toScore * 100), 2) .'</td>';

                $resMarkup .= '</tr>';

                $srNo++;
            }
        }

        $resMarkupArray['markup'] = $resMarkup;
        
        $resMarkup = '<div class="table-responsive">' . "\n";
        
        $resMarkup .= '<table id="exportToExcelTable" class="table table-bordered v-table">
            <thead>
                <tr>
                    <td colspan="6"><span class="font-medium">Branch:</span> '. $data['data']['data_array']['combined_name'] .'</td>
                    <td colspan="6"><span class="font-medium">Number of Audits Conducted:</span> '. sizeof($data['data']['data_array']['no_of_asses']) .'</td>
                </tr>

                <tr>
                    <td colspan="6"><span class="font-medium">Number of Accounts (Deposits):</span> '. $data['data']['data_array']['total_deposits'] .'</td>
                    <td colspan="6"><span class="font-medium">Number of Accounts (Advances):</span> '. $data['data']['data_array']['total_advances'] .'</td>
                </tr>

                <tr>
                    <td colspan="6"><span class="font-medium">Number of Accounts - Sampled (Deposits):</span> '. $data['data']['data_array']['total_deposits_sampling'] .'</td>
                    <td colspan="6"><span class="font-medium">Number of Accounts - Sampled (Advances):</span> '. $data['data']['data_array']['total_advances_sampling'] .'</td>
                </tr>';

        if( $data['request'] -> has('selectSearchTypeFilter') && 
            in_array($data['request'] -> input('selectSearchTypeFilter'), [3,4]))
            $resMarkup .= '<tr><td colspan="12"><span class="font-medium">Assesment Period:</span> '. $data['data']['data_array']['no_of_asses'][ array_keys($data['data']['data_array']['no_of_asses'])[0] ] .'</td></tr>';
        elseif( $data['request'] -> has('selectSearchTypeFilter') && 
                in_array($data['request'] -> input('selectSearchTypeFilter'), [5,6]))
                $resMarkup .= '<tr><td colspan="12"><span class="font-medium">Period:</span> '. $data['request'] -> input('startDate') . ' - ' . $data['request'] -> input('endDate') .'</td></tr>';
                
        $resMarkup .= '<tr class="bg-light-gray">
                    <th class="text-center">Sr. No.</th>
                    <th>Risk Type</th>
                    <th>Risk Weight</th>
                    <th>Total Questions Available</th>
                    <th>Questions Not Applicable</th>
                    <th>Total Questions Applicable (T1)</th>
                    <th>Total Annexures (T2)</th>
                    <th>Total Questions (T1 + T2)</th>
                    <th>Highest Possible Score (Weighted)</th>
                    <th>Total Score Obtained (Weighted)</th>
                    <th>Relative Performance (%)</th>
                    <th>% To Total Score Obtained (Weighted)</th>
                </tr>';

        $resMarkup .= '</thead><tbody>';

        $resMarkup .= $resMarkupArray['markup'];

        $resMarkup .= '<tr>
            <td colspan="3" class="text-center font-bold">Total</td>
            <td align="right" class="font-bold">'. $totArray['total_questions'] .'</td>
            <td align="right" class="font-bold">'. $totArray['total_na_questions'] .'</td>
            <td align="right" class="font-bold">'. $totArray['total_questions_t1'] .'</td>
            <td align="right" class="font-bold">'. $totArray['total_annex_t2'] .'</td>
            <td align="right" class="font-bold">'. ($totArray['total_questions_t1'] + $totArray['total_annex_t2']) .'</td>
            <td align="right" class="font-bold">'. get_decimal($totArray['total_highest_score_weighted'], 2) .'</td>
            <td align="right" class="font-bold">'. get_decimal($totArray['total_obtained_score_weighted'], 2) .'</td>
            <td align="right"></td>
            <td align="right" class="font-bold">'. get_decimal(($totArray['to_score'] * 100), 2) .'</td>
        </tr>';

        $resMarkup .= '</tbody></table>';
        $resMarkup .= '</div>';

    echo $resMarkup;

    echo '</div>';
}

?>