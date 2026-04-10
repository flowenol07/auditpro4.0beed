<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "audit-observation-count-report", "action" => "" ]);

require_once REPORTS_VIEW . DS . '_report-partials/audit-units.php';

echo '<div id="dateFilterContainer" class="row">' . "\n";
    require_once REPORTS_VIEW . '/_report-partials/date-filters.php';
echo '</div>' . "\n";

if( array_key_exists('data_array', $data['data']) && is_array($data['data']['data_array']) && sizeof($data['data']['data_array']) > 0 )
{
?>

<div class="col-md-4">
    <?php
        //auditStatus	
        $markup = FormElements::generateLabel('auditStatus', 'Select Audit Status');

        if(is_array(ASSESMENT_TIMELINE_ARRAY) && sizeof(ASSESMENT_TIMELINE_ARRAY) > 0)
        {
            $markup .= FormElements::generateSelect([
                "id" => "auditStatus", "name" => "auditStatus", 
                "default" => ["", "Please select audit status"],
                "options" => ASSESMENT_TIMELINE_ARRAY,
                "options_db" => ["type" => "arr", "val" => "title"],
                "appendClass" => "filter_dropdown",
                "extra" => "data-sort=3"
            ]);
        }
        else
            $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

        echo FormElements::generateFormGroup($markup, $data, 'auditStatus');
    ?>
</div>

<?php
}
$btnArray = array('find');

        if( array_key_exists('data_array', $data['data']) && 
        is_array($data['data']['data_array']) && 
        sizeof($data['data']['data_array']) > 0 )
            array_push($btnArray, 'filter', 'print','reset');

generate_report_buttons($btnArray);
?>