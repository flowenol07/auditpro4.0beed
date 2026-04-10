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
    echo '<div class="mb-3"></div>';

    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);

        $totalBottomArray = array( 'tot_risk' => 0, 'tot_score' => 0 );
        $resMarkupArray = array( 'th_1_row' => '', 'th_flag' => false, 'th_2_row' => '', 'markup' => '', 'bottom_strip' => '' );
        $resMarkup = '';    

        foreach($data['data']['data_array'] as $cAuditUnitId => $cRiskScores)
        {
            $resMarkup .= '<tr>';

                //audit unit code
                if($data['data']['select_search_type'] == 1 && array_key_exists($cAuditUnitId, $data['data']['audit_unit_data']))
                {
                    $resMarkup .= '<td>'. $data['data']['audit_unit_data'][ $cAuditUnitId ] -> audit_unit_code .'</td>
                    <td>'. string_operations($data['data']['audit_unit_data'][ $cAuditUnitId ] -> name, 'upper') .'</td>';
                }
                elseif($data['data']['select_search_type'] == 2 && array_key_exists($cAuditUnitId, $data['data']['ho_audit_unit_data']))
                {
                    $resMarkup .= '<td>'. $data['data']['ho_audit_unit_data'][$cAuditUnitId] -> audit_unit_code .'</td>
                    <td>'. string_operations($data['data']['ho_audit_unit_data'][$cAuditUnitId] -> name, 'upper') .'</td>';
                }
                else
                    $resMarkup .= '<td>0</td>
                    <td>'. string_operations(ERROR_VARS['notFound'], 'upper') .'</td>';

                $resMarkupArray['bottom_strip'] = '';

                foreach($data['data']['risk_category'] as $cRiskCatId => $cRiskCatDetails)
                {
                    if(!$resMarkupArray['th_flag'])
                    {
                        $resMarkupArray['th_1_row'] .= '<th colspan="3" class="text-center">'. $cRiskCatDetails -> risk_category .'</th>';
                        $resMarkupArray['th_2_row'] .= '<th>Total Risk Score</th>
                        <th>'. $cRiskCatDetails -> risk_category .' (%) To Total Branch Risk </th>
                        <th>'. $cRiskCatDetails -> risk_category .' (%) To All Branch Risk </th>';
                    }

                    //Total Risk Score	
                    if($cRiskScores['tot'] > 0)
                        $totalBranchRisk = get_decimal((($cRiskScores[ $cRiskCatId ] * 100) / $cRiskScores['tot']), 2);
                    else
                        $totalBranchRisk = '0.00';

                    $totalAllBranchRisk = array_key_exists($cRiskCatId, $data['data']['total_audit_unit_wise_count']) ? $data['data']['total_audit_unit_wise_count'][ $cRiskCatId ] : 0;
                    $totalAllBranchRisk = ($totalAllBranchRisk > 0) ? get_decimal((($cRiskScores[ $cRiskCatId ] * 100) / $totalAllBranchRisk), 2) : '0.00';

                    $resMarkup .= '<td>'. get_decimal($cRiskScores[ $cRiskCatId ], 2) .'</td>
                    <td>'. $totalBranchRisk .'</td><td>'. $totalAllBranchRisk .'</td>';

                    if(!array_key_exists($cRiskCatId, $totalBottomArray))
                        $totalBottomArray[$cRiskCatId] = 0;

                    $totalBottomArray[$cRiskCatId] = get_decimal(($totalBottomArray[$cRiskCatId] + $cRiskScores[ $cRiskCatId ]), 2);

                    //last bottom strip
                    $resMarkupArray['bottom_strip'] .= '<td>'. $totalBottomArray[$cRiskCatId] .'</td><td></td><td></td>';
                }

                //total score
                $totalScoreAllBranch = ($data['data']['total_audit_unit_wise_count']['tot'] > 0) ? get_decimal((($cRiskScores['tot'] * 100) / $data['data']['total_audit_unit_wise_count']['tot']), 2) : '0.00';
                $resMarkup .= '<td>'. get_decimal($cRiskScores['tot'], 2) .'</td>';
                $resMarkup .= '<td>'. $totalScoreAllBranch .'</td>';

                $totalBottomArray['tot_risk'] = get_decimal(($totalBottomArray['tot_risk'] + $cRiskScores['tot']), 2);

                // function call
                $cRiskStr = get_branch_risk_category($cAuditUnitId, $totalScoreAllBranch, $data['data']['branch_rating']);

                $resMarkup .= '<td>'. $cRiskStr .'</td>';

                $resMarkupArray['th_flag'] = true;

            $resMarkup .= '</tr>';
            
        }

        $resMarkupArray['markup'] = $resMarkup;
        
        $resMarkup = '<div class="table-responsive">' . "\n";
        
        $resMarkup .= '<table id="exportToExcelTable" class="table table-bordered v-table"><thead><tr>  
            <tr>
                <th rowspan="2">BR Code</th>
                <th rowspan="2">Branch / HO</th>
                '.  $resMarkupArray['th_1_row'] .'
                <th rowspan="2">Total Score</th>
                <th rowspan="2">Total Score % to All Branches / HO Departments</th>
                <th rowspan="2">Branch Rating</th>
            </tr>';

        $resMarkup .= '<tr>'. $resMarkupArray['th_2_row'] .'</tr></thead><tbody>';

        $resMarkup .= $resMarkupArray['markup'];

        $resMarkup .= '<tr>
            <td colspan="2">Total</td>
            '. $resMarkupArray['bottom_strip'] .'
            <td>'. $totalBottomArray['tot_risk'] .'</td>
            <td colspan="2"></td>
        </tr>';

        $resMarkup .= '</tbody></table>';
        $resMarkup .= '</div>';

    echo $resMarkup;

    echo '</div>';
}

?>