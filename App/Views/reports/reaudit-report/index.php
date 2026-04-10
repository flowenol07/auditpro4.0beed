<?php 

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

        echo '<div class="hide-this">' . "\n";
            generate_report_buttons(['print']);
        echo '</div>' . "\n";

        echo '<div class="mb-2"></div>' . "\n";

        echo '<div class="">' . "\n";
            echo generate_table_markup($data, $data['data']['data_array'], $data['data']['filter_type']);
        echo '</div>' . "\n";

    echo '</div>' . "\n";
}

?>