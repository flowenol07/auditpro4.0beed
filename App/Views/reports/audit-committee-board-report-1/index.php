<?php require_once('form.php'); 

if( !function_exists('get_branch_risk_category') )
{
    function get_branch_risk_category($total_score_all_branch, $risk_position_array)
    {
        $c_risk_str = '-';

        foreach($risk_position_array as $c_risk_condition => $c_rsk_str)
        {
            $temp = !empty($c_risk_condition) ? explode('-', $c_risk_condition) : [];

            if(sizeof($temp) == 1 && $total_score_all_branch >= $temp[0])
                $c_risk_str = $c_rsk_str;
            elseif(sizeof($temp) == 2 && $total_score_all_branch >= $temp[0] && $temp[0] < $total_score_all_branch)
                $c_risk_str = $c_rsk_str;

            if($c_risk_str != '-')
                break;
        }

        return $c_risk_str;
    }
}

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

    $trendArray = [
        'increasing' => 0, 'decreasing' => 0, 'stable' => 0
    ];

    $res_markup = '<div class="table-responsive">' . "\n";

    $res_markup .= '<table class="table table-bordered v-table mb-0">
        <thead>
            <tr class="bg-light-gray">
                <th style="width:5%" class="text-center" rowspan="2">Sr. No.</th>
                <th style="width:18%" rowspan="2">Audit Unit Details</th>
                <th style="width:8%" rowspan="2">Total Risk</th>
                <th style="width:20%" class="text-center" colspan="2">Period 1</th>
                <th style="width:20%" class="text-center" colspan="2">Period 2</th>
                <th style="width:8%" rowspan="2">Total Risk</th>
                <th style="width:12%" rowspan="2">Trend</th>
                <th style="width:10%" rowspan="2">Change in Risk Score</th>
            </tr>

            <tr class="bg-light-gray">
                <th class="text-center" colspan="2">'. $data['data']['extra_array']['startMonth'] . ' to ' . $data['data']['extra_array']['endMonth'] .'</th>
                <th class="text-center" colspan="2">'. $data['data']['extra_array']['startMonth2'] . ' to ' . $data['data']['extra_array']['endMonth2'] .'</th>
            </tr>
        </thead>
        <tbody id="tablebodycontainer">';

        // $total_avg_score = 0;
        // $total_weighted_score = 0;
        // $tmp = 0;
        $srNo = 1;

        foreach($data['data']['data_array']['audit_units'] as $cAuditUnitId => $cAuditUnitDetails)
        {
            $res_markup .= '<tr>' . "\n";

                // sr no
                $res_markup .= '<td style="width:5%" class="text-center">'. $srNo .'</td>' . "\n";

                // audit unit
                $res_markup .= '<td style="width:18%">'. string_operations($cAuditUnitDetails -> combined_name, 'upper') .'</td>' . "\n";
                $total_score_all_branch_1 = 0; $total_score_all_branch_2 = 0;
                $c_score_branch_1 = 0; $c_score_branch_2 = 0;
                $period_1_risk = null; $period_2_risk = null;

                // period 1
                if( isset($cAuditUnitDetails -> period_1_cnt) &&
                    !($cAuditUnitDetails -> period_1_cnt > 0) && 
                    isset($cAuditUnitDetails -> period_1) &&
                    is_array($cAuditUnitDetails -> period_1) && 
                    sizeof($cAuditUnitDetails -> period_1) > 0 )
                {
                    $c_score_branch_1 = get_decimal($cAuditUnitDetails -> period_1['wg_sc'], 2);
                    $total_score_all_branch_1 = ($data['data']['data_array']['p1_tot'] > 0) ? get_decimal(($c_score_branch_1 * 100) / $data['data']['data_array']['p1_tot'], 2) : '0.00';
                    $period_1_risk = get_branch_risk_category($cAuditUnitId, $total_score_all_branch_1, $data['data']['data_array']['branch_rating']);

                    // score
                    $res_markup .= '<td style="width:10%" class="text-end">'. get_decimal($cAuditUnitDetails -> period_1['wg_sc'], 2) .'</td>' . "\n";
                    $res_markup .= '<td style="width:10%" class="text-center">'. get_decimal($total_score_all_branch_1, 2) .'%</td>' . "\n";
                    $res_markup .= '<td style="width:10%">'. string_operations($period_1_risk, 'upper') .'</td>' . "\n";
                }
                else
                    $res_markup .= '<td colspan="3">Data not found in period / assesment missing</td>' . "\n";

                // period 2
                if( isset($cAuditUnitDetails -> period_2_cnt) &&
                    !($cAuditUnitDetails -> period_2_cnt > 0) && 
                    isset($cAuditUnitDetails -> period_2) &&
                    is_array($cAuditUnitDetails -> period_2) && 
                    sizeof($cAuditUnitDetails -> period_2) > 0 )
                {
                    $c_score_branch_2 = get_decimal($cAuditUnitDetails -> period_2['wg_sc'], 2);
                    $total_score_all_branch_2 = ($data['data']['data_array']['p2_tot'] > 0) ? get_decimal(($c_score_branch_2 * 100) / $data['data']['data_array']['p2_tot'], 2) : '0.00';
                    $period_2_risk = get_branch_risk_category($cAuditUnitId, $total_score_all_branch_2, $data['data']['data_array']['branch_rating']);

                    $res_markup .= '<td style="width:10%" class="text-center">'. get_decimal($total_score_all_branch_2, 2) .'%</td>' . "\n";
                    $res_markup .= '<td style="width:10%">'. string_operations($period_2_risk, 'upper') .'</td>' . "\n";

                    // score
                    $res_markup .= '<td style="width:10%" class="text-end">'. get_decimal($cAuditUnitDetails -> period_2['wg_sc'], 2) .'</td>' . "\n";
                    
                }
                else
                    $res_markup .= '<td colspan="3">Data not found in period / assesment missing</td>' . "\n";

                $trend = '';

                if($data['data']['extra_array']['trend'] == 'rwt')
                {
                    // risk wise trend
                    if($period_1_risk == $period_2_risk)
                    {
                        $trend = 'stable';
                        $trendArray['stable']++;
                    }
                }
                else
                {
                    // score wise trend
                    if($c_score_branch_1 == $c_score_branch_2)
                    {
                        $trend = 'Stable';
                        $trendArray['stable']++;
                    }
                }
                    
                if(empty($trend))
                {
                    $trend = ($c_score_branch_1 > $c_score_branch_2) ? 'decreasing' : 'increasing';
                    $trendArray[ $trend ]++;
                }

                // $res_markup .= '<td>Increase - Decrease</td>' . "\n";
                // $res_markup .= '<td>Increase - Decrease</td>' . "\n";

                // change in score
                $res_markup .= '<td style="width:12%">'. string_operations($trend, 'upper') .'</td>' . "\n";

                // change in risk score
                $changeScore = ($c_score_branch_1 - $c_score_branch_2);
                $changeScore = get_decimal( (($changeScore < 0) ? -$changeScore : $changeScore), 2);
                $res_markup .= '<td style="width:10%" class="text-end">'. $changeScore .'</td>' . "\n";

            $res_markup .= '</tr>' . "\n";

            $srNo++;
        }

        $data['data']['data_array']['p1_tot'] = get_decimal($data['data']['data_array']['p1_tot'], 2);
        $data['data']['data_array']['p2_tot'] = get_decimal($data['data']['data_array']['p2_tot'], 2);

        // over all trend
        $overAllTrend = $data['data']['data_array']['p1_tot'] - $data['data']['data_array']['p2_tot'];
        $overAllTrend = get_decimal( (($overAllTrend < 0) ? -$overAllTrend : $overAllTrend), 2);

        $trend = '';

        if($data['data']['data_array']['p1_tot'] == $data['data']['data_array']['p2_tot'])
            $trend = 'stable';
        else
            $trend = ($data['data']['data_array']['p1_tot'] > $data['data']['data_array']['p2_tot']) ? 'decreasing' : 'increasing';

        $res_markup .= '<tr class="font-medium">
            <td colspan="2" class="text-center">Total</td>
            <td class="text-end">'. $data['data']['data_array']['p1_tot'] .'</td>
            <td colspan="4"></td>
            <td class="text-end">'. $data['data']['data_array']['p2_tot'] .'</td>
            <td>'. string_operations($trend, 'upper') .'</td>
            <td class="text-end">'. $overAllTrend .'</td>
        </tr>';

        $res_markup .= '</tbody>
    </table>';

    $res_markup .= '</div>' . "\n";

    arsort($trendArray);

    echo '<div>
            <h5 class="font-medium">Trend of Risk</h5>
            <p class="mb-0">A. Overall Trend: <strong>'. ucfirst( $trend ) .'</strong></p>
            <p class="mb-3">B. Audit Unit Wise</p>
            <div class="table-responsive">
                <table class="table table-bordered mb-3"><tr class="bg-light-gray">';

                $trendStr = '<tr>';
                
                foreach($trendArray as $cTrendKey => $cTrendCount)
                {
                    echo '<th class="text-center">'. string_operations($cTrendKey, 'upper') .'</th>';
                    $trendStr .= '<td class="text-center">'. $cTrendCount .'</td>';
                }

                $trendStr .= '</tr>';

                echo '</tr>';
                echo $trendStr;
                unset($trendStr);
                  
            echo '</table>
        </div>
    </div>';

    echo '<h5 class="font-medium mb-2">Audit Unit Wise Trend</h5>';
    echo $res_markup;

    echo '</div>';
}

// print_r($data['data']['data_array']);

?>