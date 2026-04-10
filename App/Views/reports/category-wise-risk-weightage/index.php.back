<?php
require_once('form.php');

if (
    array_key_exists('data_array', $data['data']) &&
    empty($data['data']['data_array'])
) {
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

/* HAS DATA */
elseif (
    array_key_exists('data_array', $data['data']) &&
    is_array($data['data']['data_array']) &&
    sizeof($data['data']['data_array']) > 0
) {

    echo '<div class="mb-3"></div>';
    echo '<div id="printContainer">';

    generate_report_header($data['data']);

    echo '<div class="table-responsive">';
    echo '
    <table id="exportToExcelTable" class="table table-bordered v-table">
        <thead>
            <tr>
                <th style="width:10%" class="text-center">Branch Code</th>
                <th style="width:20%">Branch Name</th>
                <th style="width:15%">Category</th>
                <th style="width:10%">No. of Audits</th>
                <th style="width:15%">Total Weighted Score</th>
                <th style="width:15%">% To Total Weighted Score</th>
            </tr>
        </thead>
        <tbody>
    ';

    $grand_weighted_score = 0;
    $grand_percentage_score = 0;

    foreach ($data['data']['sortedBroaderAreaKeys'] as $category_key => $category_details) {

        if (!array_key_exists($category_key, $data['data']['data_array'])) {
            continue;
        }

        $category_weighted_score = 0;
        $category_percentage_score = 0;

        foreach ($data['data']['risk_category'] as $risk_id => $risk_details) {

            if (!isset($data['data']['data_array'][$category_key][$risk_id])) {
                continue;
            }

            $risk_data = $data['data']['data_array'][$category_key][$risk_id];

            /* SUM WEIGHTED SCORE */
            $category_weighted_score += floatval($risk_data['weighted_score']);

            /* CALCULATE % TO TOTAL */
            if (
                $risk_data['weighted_score'] > 0 &&
                $data['data']['tot_weighted_score'] > 0
            ) {
                $category_percentage_score +=
                    ($risk_data['weighted_score'] / $data['data']['tot_weighted_score']) * 100;
            }
        }

        $grand_weighted_score += $category_weighted_score;
        $grand_percentage_score += $category_percentage_score;

        echo '
        <tr>
            <td class="text-center">' .
                (is_object($data['data']['audit_details'])
                    ? $data['data']['audit_details']->audit_unit_code
                    : '-') .
            '</td>

            <td>' .
                string_operations(
                    (is_object($data['data']['audit_details'])
                        ? $data['data']['audit_details']->name
                        : '-'),
                    'upper'
                ) .
            '</td>

            <td>' . string_operations($category_key, 'upper') . '</td>

            <td class="text-center">' . $data['data']['no_of_assessment'] . '</td>

            <td class="text-right">' . get_decimal($category_weighted_score, 2) . '</td>

            <td class="text-right">' . get_decimal($category_percentage_score, 2) . '</td>
        </tr>';
    }

    /* GRAND TOTAL ROW */
    echo '
        <tr class="font-medium bg-light">
            <td colspan="4" class="text-center">Grand Total</td>
            <td class="text-right">' . get_decimal($grand_weighted_score, 2) . '</td>
            <td class="text-right">' . get_decimal($grand_percentage_score, 2) . '</td>
        </tr>
    ';

    echo '
        </tbody>
    </table>
    </div>
    </div>';
}
?>
