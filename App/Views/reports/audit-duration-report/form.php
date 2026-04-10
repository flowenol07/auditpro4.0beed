<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "last_march_report", "action" => ""]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //financial_year	
            $markup = FormElements::generateLabel('financial_year', 'Select Financial Year');

            if(is_array($data['data']['db_year_data']) && sizeof($data['data']['db_year_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "financial_year", "name" => "financial_year", 
                    "default" => ["", "Please select financial year"],
                    "options" => $data['data']['db_year_data'],
                    "selected" => isset($data['data']['assesment_data'][0] -> year_id) ? ($data['request'] -> input('financial_year', $data['data']['assesment_data'][0] -> year_id)) : '',
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'financial_year');

        ?>
    </div>

<?php
    if(!empty($data['data']['assesment_data']))
    { 
?>
    <div class="col-md-6">
        <?php
            //branch	
            $markup = FormElements::generateLabel('branch', 'Select Branch');

            if(is_array($data['data']['db_audit_unit_data']) && sizeof($data['data']['db_audit_unit_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "branch", "name" => "branch", 
                    "default" => ["", "Please select branch"],
                    "options" => $data['data']['db_audit_unit_data'],
                    "options_db" => ["type" => "obj", "val" => "combined_name"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=0"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'branch');
        ?>
    </div>
    <?php } ?>
</div>

<?php
    $btnArray = array('find');

            if(!empty($data['data']['assesment_data']))
                array_push($btnArray, 'filter', 'print','excel','reset');

            generate_report_buttons($btnArray);
?>