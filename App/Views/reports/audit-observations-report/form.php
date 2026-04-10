<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "audit-complete-report", "action" => "" ]);

?>

<div class="row">
    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-units.php'; ?>
    </div>

    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-assesments.php'; ?>
    </div>

    <?php
        if(array_key_exists('data_array', $data['data']) && is_array($data['data']['data_array']) && 
        array_key_exists('ans_data', $data['data']['data_array']) && is_array($data['data']['data_array']['ans_data']) && 
        sizeof($data['data']['data_array']['ans_data']) > 0 )
        {
    ?>

    <div class="col-md-4">
        <?php
            //businessRisk	
            $markup = FormElements::generateLabel('businessRisk', 'Select Business Risk');

            if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "businessRisk", "name" => "businessRisk", 
                    "default" => ["", "Please select business risk"],
                    "options" => RISK_PARAMETERS_ARRAY,
                    "options_db" => ["type" => "arr", "val" => "title"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=3"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'businessRisk');
        ?>
    </div>

    <div class="col-md-4">
        <?php
            //controlRisk	
            $markup = FormElements::generateLabel('controlRisk', 'Select Control Risk');

            if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "controlRisk", "name" => "controlRisk", 
                    "default" => ["", "Please select control risk"],
                    "options" => RISK_PARAMETERS_ARRAY,
                    "options_db" => ["type" => "arr", "val" => "title"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=4"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'controlRisk');
        ?>
    </div>

    <div class="col-md-4">
        <?php
            //risk	
            $markup = FormElements::generateLabel('risk', 'Select Risk Category');

            if(is_array($data['data']['risk_category_data']) && sizeof($data['data']['risk_category_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "risk", "name" => "risk", 
                    "default" => ["", "Please select risk category"],
                    "options" => $data['data']['risk_category_data'], "options_db" => ["type" => "obj", "val" => "risk_category"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=5"

                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'risk');
        ?>
    </div>
    <?php 
        }
    ?>
</div>

<?php

$btnArray = array('find');

    if(array_key_exists('data_array', $data['data']) && is_array($data['data']['data_array']) && 
        array_key_exists('ans_data', $data['data']['data_array']) && is_array($data['data']['data_array']['ans_data']) && 
        sizeof($data['data']['data_array']['ans_data']) > 0 )
            array_push($btnArray, 'filter', 'print','reset');

generate_report_buttons($btnArray);
?>