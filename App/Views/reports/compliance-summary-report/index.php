<?php

echo '<div class="no-print mb-3">';
require_once('form.php');
echo '</div>';

if( array_key_exists('data_array', $data['data']) && empty($data['data']['data_array']))
{
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

//has data
elseif( array_key_exists('data_array', $data['data']) && is_array($data['data']['data_array']) && 
    array_key_exists('ans_data', $data['data']['data_array']) && is_array($data['data']['data_array']['ans_data']) && 
    sizeof($data['data']['data_array']['ans_data']) > 0 )
{
    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], false, 0, true);
        
        echo '<div class="mb-2"></div>' . "\n";

        echo '<style>

            .audit-report-table > tbody > tr > th:nth-child(1), .audit-report-table > tbody > tr > td:nth-child(1) { width:5% }
            .audit-report-table > tbody > tr > th:nth-child(2), .audit-report-table > tbody > tr > td:nth-child(2) { width:31% }
            .audit-report-table > tbody > tr > th:nth-child(3), .audit-report-table > tbody > tr > td:nth-child(3) { width:10% }
            .audit-report-table > tbody > tr > th:nth-child(4), .audit-report-table > tbody > tr > td:nth-child(4) { width:10% }
            .audit-report-table > tbody > tr > th:nth-child(5), .audit-report-table > tbody > tr > td:nth-child(5) { width:20% }
            .audit-report-table > tbody > tr > th:nth-child(6), .audit-report-table > tbody > tr > td:nth-child(6) { width:8% }
            .audit-report-table > tbody > tr > th:nth-child(7), .audit-report-table > tbody > tr > td:nth-child(7) { width:8% }
            .audit-report-table > tbody > tr > th:nth-child(8), .audit-report-table > tbody > tr > td:nth-child(8) { width:8%; }

            .audit-report-table > tbody > tr > td[colspan] { width: 100% !important; }
            .audit-report-table { font-size: 13px }

        </style>';

        echo '<div class="">' . "\n";
            echo generate_table_markup($data, $data['data']['data_array'], $data['data']['filter_type']);
        echo '</div>' . "\n";

    echo '</div>' . "\n";
}

?>