<?php require_once('form.php'); 

if( array_key_exists('data_array', $data['data']) && empty($data['data']['data_array']))
{
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

//has data
elseif( array_key_exists('data_array', $data['data']) && 
        is_array($data['data']['data_array']) && 
        sizeof($data['data']['data_array']) > 0)
{
    $tempRiskArr = [ '1-10' => 'Low Risk', '10-20' => 'Medium Risk', '20-50' => 'High Risk' ];

    $riskRatingArray = [
        1 => $tempRiskArr, 2 => $tempRiskArr, 3 => $tempRiskArr, 4 => $tempRiskArr, 5 => $tempRiskArr,
        6 => $tempRiskArr, 7 => $tempRiskArr, 8 => $tempRiskArr, 9 => $tempRiskArr, 10 => $tempRiskArr
    ];

    echo '<div class="mb-3"></div>';

    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);

        $resMarkup = '<div class="table-responsive">' . "\n";
        
        $resMarkup .= '<table id="exportToExcelTable" class="table table-bordered v-table">' . "\n";

            $row2Tds = '';
            $lastCol = '';
            $totalTds = '';

            $resMarkup .= '<thead>
                <tr>
                    <th rowspan="2" class="text-center">BR Code</th>
                    <th rowspan="2">Branch / HO</th>
                    <th rowspan="2" class="text-center">No of Audits</th>';

                $allTotalScore = 0;
                $allTotalOutOfScore = 0;
                $allToScore = 0;

                foreach($data['data']['has_data'] as $cRiskId => $cRiskData)
                {
                    $resMarkup .= '<th class="text-center" colspan="4">'.  $cRiskData['risk_category'] .'</th>' . "\n";
                    
                    if(empty($lastCol))
                        $lastCol = '<th class="text-center">Total Score</th>
                            <th class="text-center">Out of Score</th>
                            <th class="text-center">%</th>
                            <th class="text-center">Risk</th>';

                    $row2Tds .= $lastCol;

                    // total tds
                    if($data['data']['total_risk_scores'][ $cRiskId ])
                    {
                        $thsw = get_decimal($data['data']['total_risk_scores'][ $cRiskId ]['total_highest_score_weighted'], 2);
                        $tosw = get_decimal($data['data']['total_risk_scores'][ $cRiskId ]['total_obtained_score_weighted'], 2);

                        $allTotalScore += $thsw;
                        $allTotalOutOfScore += $tosw;
                        $rel = 0;

                        if($thsw > 0 && $tosw > 0)
                        {
                            $rel = $tosw / $thsw;
                            $rel = get_decimal(($rel * 100), 2);
                        }                        

                        $totalTds .= '<td align="right" class="font-bold">'. $thsw .'</td>' . "\n";
                        $totalTds .= '<td align="right" class="font-bold">'. $tosw .'</td>' . "\n";
                        $totalTds .= '<td align="right" class="font-bold">'. $rel .'</td>' . "\n";
                        $totalTds .= '<td></td>' . "\n";
                    }
                }

            $resMarkup .= '<th colspan="4">Total Score</th>
                           <th rowspan="2">Total Score % to All Branches / HO Departments</th>
                           <th rowspan="2">Total Risk</th>';

            $resMarkup .= '</tr>
                <tr>'. $row2Tds . $lastCol .'</tr>';
            $resMarkup .= '<thead><tbody>' . "\n";

                foreach($data['data']['data_array'] as $cAuditUnitId => $cAuditUnitData)
                {                    
                    $resMarkup .= '<tr>' . "\n";

                        // branch code
                        $resMarkup .= '<td class="text-center">'. $cAuditUnitData['audit_unit_code'] .'</td>';
                        $resMarkup .= '<td>'. $cAuditUnitData['name'] .'</td>';
                        $resMarkup .= '<td class="text-center">'. sizeof($cAuditUnitData['no_of_asses']) .'</td>';

                        $totalScore = 0;
                        $totalOutOfScore = 0;

                        foreach($data['data']['has_data'] as $cRiskId => $cRiskData)
                        {
                            $toPercent = 0;
                            $riskStr = '-';

                            if( isset($cAuditUnitData['risk_category'][ $cRiskData['id'] ]) &&
                                isset($cAuditUnitData['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted']) )
                            {
                                $totalScore += get_decimal($cAuditUnitData['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted'], 2);
                                $totalOutOfScore += get_decimal($cAuditUnitData['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted'], 2);

                                $rel = get_decimal($cAuditUnitData['risk_category'][ $cRiskData['id'] ]['relative_performance'], 2);
                                if($rel > 0) $riskStr = get_branch_risk_category($cAuditUnitId, $rel, [ $cAuditUnitId => $riskRatingArray[ $cRiskData['id'] ] ]);

                                $resMarkup .= '<td align="right">'. get_decimal($cAuditUnitData['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted'], 2) .'</td>';
                                $resMarkup .= '<td align="right">'. get_decimal($cAuditUnitData['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted'], 2) .'</td>';
                                $resMarkup .= '<td align="right">'. $rel .'</td>';
                            }
                            else
                            {
                                $resMarkup .= '<td align="right">'. get_decimal(0, 2) .'</td>';
                                $resMarkup .= '<td align="right">'. get_decimal(0, 2) .'</td>';
                                $resMarkup .= '<td align="right">'. get_decimal(0, 2) .'%</td>';
                            }

                            $resMarkup .= '<td>'. $riskStr .'</td>';
                        }

                        $rel = 0;
                        $toScore = 0;

                        if( $totalScore > 0 && 
                            $totalOutOfScore > 0)
                        {
                            $rel = $totalOutOfScore / $totalScore;
                            $rel = $rel * 100;

                            // to score
                            $toScore = $totalOutOfScore / $allTotalOutOfScore;
                            $toScore = $toScore * 100;
                            $allToScore += $toScore;
                        }

                        $riskStr = '-';
                        if($rel > 0) $riskStr = get_branch_risk_category($cAuditUnitId, $rel, [ $cAuditUnitId => $riskRatingArray[ $cRiskData['id'] ] ]);

                        $resMarkup .= '<td align="right">'. get_decimal($totalScore, 2) .'</td>';
                        $resMarkup .= '<td align="right">'. get_decimal($totalOutOfScore, 2) .'</td>';
                        $resMarkup .= '<td align="right">'. get_decimal($rel, 2) .'</td>';
                        $resMarkup .= '<td align="right">'. $riskStr .'</td>';
                        $resMarkup .= '<td align="right">'. get_decimal($toScore, 2) .'</td>';

                        $riskStr = '-';
                        if($rel > 0) $riskStr = get_branch_risk_category($cAuditUnitId, $toScore, [ $cAuditUnitId => $riskRatingArray[ $cRiskData['id'] ] ]);
                        $resMarkup .= '<td align="right">'. $riskStr .'</td>';

                    $resMarkup .= '</tr>' . "\n";
                }


                $rel = 0;
                $allTotalScore = get_decimal($allTotalScore, 2);
                $allTotalOutOfScore = get_decimal($allTotalOutOfScore, 2);

                if($allTotalScore > 0 && $allTotalOutOfScore > 0)
                {
                    $rel = $allTotalOutOfScore / $allTotalScore;
                    $rel = get_decimal(($rel * 100), 2);
                }

                // total
                $resMarkup .= '<tr>' . "\n";
                    $resMarkup .= '<td colspan="3" class="text-center font-bold">Total</td>' . "\n";
                    $resMarkup .= $totalTds;
                    $resMarkup .= '<td align="right" class="font-bold">'. $allTotalScore .'</td>';
                    $resMarkup .= '<td align="right" class="font-bold">'. $allTotalOutOfScore .'</td>';
                    $resMarkup .= '<td align="right" class="font-bold">'. $rel .'</td><td></td>';
                    $resMarkup .= '<td align="right" class="font-bold">'. get_decimal($allToScore, 2) .'</td>';
                    $resMarkup .= '<td></td>';
                $resMarkup .= '</td>' . "\n";

            $resMarkup .= '</tbody>
        </table>' . "\n";
        $resMarkup .= '</div>';

    echo $resMarkup;

    echo '</div>';
}

?>