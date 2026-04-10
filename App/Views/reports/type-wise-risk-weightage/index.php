<?php
require_once('form.php');

/* ================= NO DATA ================= */

if (
    array_key_exists('data_array', $data['data']) &&
    empty($data['data']['data_array'])
) {
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

/* ================= HAS DATA ================= */

elseif (
    array_key_exists('data_array', $data['data']) &&
    is_array($data['data']['data_array'])
) {

    echo '<div class="mb-3"></div>';
    echo '<div id="printContainer">';

    /* ================= HEADER ================= */

    generate_report_header($data['data']);

    echo '<p class="mb-0"><span class="font-medium">Audit Unit:</span> ' .
        string_operations(
            (is_object($data['data']['audit_details'])
                ? $data['data']['audit_details']->name
                : '-'),
            'upper'
        ) .
        '</p>';

    if (
        in_array($data['data']['select_search_type'], [3, 4]) &&
        !empty($data['data']['period'])
    ) {
        echo '<p class="mb-3"><span class="font-medium">Assessment Period:</span> ' .
            $data['data']['period'] .
            '</p>';
    }

    if (
        in_array($data['data']['select_search_type'], [5, 6]) &&
        !empty($data['data']['period'])
    ) {
        echo '<p class="mb-3"><span class="font-medium">Period:</span> ' .
            $data['data']['period'] .
            '</p>';
    }

    /* ================= BUILD CALCULATED DATA MAP ================= */

    $riskDataMap = [];

    foreach ($data['data']['sortedBroaderAreaKeys'] as $c_gen_key => $c_gen_cat_details) {

        if (!isset($data['data']['data_array'][$c_gen_key])) {
            continue;
        }

        foreach ($data['data']['data_array'][$c_gen_key] as $c_lov_id => $riskData) {

            // Normalize title
            $riskTitle = trim(strtoupper($riskData['title']));

            if (!isset($riskDataMap[$riskTitle])) {
                $riskDataMap[$riskTitle] = [
                    'no_of_audit_conduct' => 0,
                    'weighted_score'     => 0
                ];
            }

            $riskDataMap[$riskTitle]['no_of_audit_conduct'] += $riskData['no_of_audit_conduct'];
            $riskDataMap[$riskTitle]['weighted_score']     += $riskData['weighted_score'];
        }
    }

    /* ================= TABLE ================= */

    echo '<div class="table-responsive">';
    echo '<table id="exportToExcelTable" class="table table-bordered v-table">
        <thead>
            <tr>
                <th class="text-center" style="width:10%">Branch Code</th>
                <th style="width:20%">Branch Name</th>
                <th style="width:20%">Risk Type</th>
                <th class="text-center" style="width:10%">Number of Audits</th>
                <th class="text-right" style="width:10%">Weighted Score</th>
                <th class="text-right" style="width:10%">% To Total Weighted Score</th>
            </tr>
        </thead>
        <tbody>';

    $totalPercentage = 0;

    /* ================= LOOP MASTER RISK LIST ================= */

    foreach ($data['data']['risk_category'] as $c_lov_id => $c_lov_details) {

        // ✅ Correct property
        $riskTitle = trim(strtoupper($c_lov_details->risk_category));

        $auditCount    = $riskDataMap[$riskTitle]['no_of_audit_conduct'] ?? 0;
        $weightedScore = $riskDataMap[$riskTitle]['weighted_score'] ?? 0;

        $percentage = 0;
        if ($weightedScore > 0 && $data['data']['tot_weighted_score'] > 0) {
            $percentage = ($weightedScore / $data['data']['tot_weighted_score']) * 100;
        }

        $totalPercentage += $percentage;

        echo '<tr>';

        echo '<td class="text-center">' .
            (is_object($data['data']['audit_details'])
                ? $data['data']['audit_details']->audit_unit_code
                : '-') .
            '</td>';

        echo '<td>' .
            string_operations(
                (is_object($data['data']['audit_details'])
                    ? $data['data']['audit_details']->name
                    : '-'),
                'upper'
            ) .
            '</td>';

        echo '<td>' . $riskTitle . '</td>';
        echo '<td class="text-center">' . 1 . '</td>';
        echo '<td class="text-right">' . get_decimal($weightedScore, 2) . '</td>';
        echo '<td class="text-right">' . get_decimal($percentage, 2) . '</td>';

        echo '</tr>';
    }

    /* ================= TOTAL ROW ================= */

    echo '<tr>
        <td colspan="4" class="font-medium text-center">Total</td>
        <td class="font-medium text-right">' .
            get_decimal($data['data']['tot_weighted_score'], 2) .
        '</td>
        <td class="font-medium text-right">' .
            get_decimal($totalPercentage, 2) .
        '</td>
    </tr>';

    echo '</tbody></table></div>';
    echo '</div>';
}
?>
