<?php require_once('form.php');

if (array_key_exists('data_array', $data['data']) && empty($data['data']['data_array'])) {
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

//has data
elseif (
    array_key_exists('data_array', $data['data']) &&
    is_array($data['data']['data_array']) &&
    sizeof($data['data']['data_array']) > 0
) {
    echo '<div class="mb-3"></div>' . "\n";
    echo '<div id="printContainer">' . "\n";

    generate_report_header($data['data']);

    echo '<div class="table-responsive height-400">' . "\n";

    /* ================= TOTAL VARIABLES ================= */

    $totalTotAvgScore      = 0;
    $totalAuditCount       = 0;
    $totalWeightedScore    = 0;
    $totalRiskWeight       = 0;

    $res_markup = '<table class="table table-bordered v-table exportToExcelTable">
        <thead>
        <tr>
            <th class="vrt-header">Audit Unit Code</th>
            <th class="vrt-header">Branch</th>
            <th class="vrt-header">Risk Type</th>
            <th class="vrt-header">Category</th>
            <th>Broader Area of Audit Non-Compliance</th>
            <th class="vrt-header">Total Averaged Score</th>
            <th class="vrt-header">Number of Audits Conducted</th>
            <th class="vrt-header">Averaged Total Score Per Audit</th>
            <th class="vrt-header">Risk Weight</th>
            <th class="vrt-header">Weighted Score</th>
        </tr>
        </thead>
    <tbody id="tablebodycontainer">';

    foreach ($data['data']['data_array'] as $cc_key => $cc_data) {
        foreach ($cc_data as $c_key => $c_data) {
            if (array_key_exists($c_key, $data['data']['sortedBroaderAreaKeys'])) {
                foreach ($c_data['borader_area'] as $c_broader_area_details) {
                    foreach ($c_broader_area_details['category'] as $c_risk_details) {

                        /* ================= ADD TOTALS ================= */

                        $totalTotAvgScore   += $c_risk_details['tot_avg_score'];
                        $totalAuditCount    += $c_risk_details['no_of_audit_conduct'];
                        $totalWeightedScore += $c_risk_details['weighted_score'];
                        $totalRiskWeight    += $c_risk_details['risk_weight'];

                        $res_markup .= '<tr>
                            <td>' . $cc_data['audit_unit_code'] . '</td>
                            <td>' . $cc_data['branch_name'] . '</td>
                            <td>' . $c_risk_details['title'] . '</td>
                            <td>' . $c_data['title'] . '</td>
                            <td>' . $c_broader_area_details['name'] . '</td>
                            <td class="text-right">' . get_decimal($c_risk_details['tot_avg_score'], 2) . '</td>
                            <td class="text-center">' . $c_risk_details['no_of_audit_conduct'] . '</td>
                            <td class="text-right">' . get_decimal($c_risk_details['avg_tot_score_per_audit'], 2) . '</td>
                            <td class="text-center">' . $c_risk_details['risk_weight'] . '</td>
                            <td class="text-right">' . get_decimal($c_risk_details['weighted_score'], 2) . '</td>
                        </tr>';
                    }
                }
            }
        }
    }

    /* ================= FINAL CALCULATED TOTAL ================= */

    $finalAvgPerAudit = ($totalAuditCount > 0)
        ? ($totalWeightedScore / $totalAuditCount)
        : 0;

    /* ================= TOTAL ROW ================= */

    $res_markup .= '<tr class="font-medium bg-light">
        <td colspan="5" class="text-center">TOTAL</td>
        <td class="text-right">' . get_decimal($totalTotAvgScore, 2) . '</td>
        <td class="text-center">' . $totalAuditCount . '</td>
        <td class="text-right">' . get_decimal($finalAvgPerAudit, 2) . '</td>
        <td class="text-center">' . $totalRiskWeight . '</td>
        <td class="text-right">' . get_decimal($totalWeightedScore, 2) . '</td>
    </tr>';

    $res_markup .= '</tbody></table>';

    echo $res_markup;

    echo '</div>' . "\n";
    echo '</div>' . "\n";
}
?>
