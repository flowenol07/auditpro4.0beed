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
    $data['data']['assesmentType'] == 1 )
{
    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], false, 0, true);

        echo '<div class="mb-4"></div>' . "\n";

        echo '<div class="">' . "\n";
            echo generate_table_markup($data, $data['data']['data_array'], $data['data']['filter_type']);
        echo '</div>' . "\n";

    echo '</div>' . "\n";
}
elseif( array_key_exists('data_array', $data['data']) && 
    is_array($data['data']['data_array']) && 
    is_array($data['data']['data_array'][0]['ans_data']) && 
    $data['data']['assesmentType'] == 2 )
{
    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], false, 0);
        
        foreach($data['data']['data_array'] as $cDataId => $cDataDetails)
        {
            echo '<div class="mb-4"></div>' . "\n";

            echo '<div class="">' . "\n";

                echo generate_table_markup($data, $cDataDetails, $data['data']['filter_type'], 1, $data['data']['assesmentData'][$cDataId]);

            echo '</div>' . "\n";
        }

    echo '</div>' . "\n";
}

?>