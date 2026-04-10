<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => ""]);

// print_r($this -> data['audit_unit_data']);
?>

<div class="row">
    <div class="col-md-12">
        <?php

            if(!isset($data['data']['empType']) || $data['data']['empType'] != 3)
            {
                //audit_units	
                $markup = FormElements::generateLabel('audit_unit_id', 'Select Branch');

                if(is_array($data['data']['audit_unit_data']) && sizeof($data['data']['audit_unit_data']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "audit_unit_id", "name" => "audit_unit_id", 
                        "default" => ["", "Please select audit units"],
                        "options" => $data['data']['audit_unit_data'],
                        "options_db" => ["type" => "obj", "val" => "combined_name"],
                        "selected" => isset($data['data']['auditAssesData'][0] -> audit_unit_id) ? ($data['request'] -> input('audit_unit_id', $data['data']['auditAssesData'][0] -> audit_unit_id)) : $data['request'] -> input('audit_unit_id'),
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'audit_unit_id');
            }
        ?>
    </div>

    <div class="col-md-6">
    <?php
            //financial_year	
            $markup = FormElements::generateLabel('financial_year', 'Select Financial Year');

            if(is_array($data['data']['db_year_data']) && sizeof($data['data']['db_year_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "financial_year", "name" => "financial_year", 
                    "default" => ["all", "ALL YEARS"],
                    "options" => $data['data']['db_year_data'],
                    "selected" => isset($data['data']['auditAssesData'][0] -> year_id) ? ($data['request'] -> input('financial_year', $data['data']['auditAssesData'][0] -> year_id)) : $data['request'] -> input('financial_year'),
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'financial_year');
        ?>
    </div>

    <div class="col-md-6">
        <?php
            //audit_status	
            $markup = FormElements::generateLabel('audit_status', 'Select Audit Status');

            if(is_array($data['data']['auditStatusArray']) && sizeof($data['data']['auditStatusArray']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "audit_status", "name" => "audit_status",
                    "options" => $data['data']['auditStatusArray'],
                    "options_db" => ["type" => "arr", "val" => "status"],
                    "selected" => isset($data['data']['auditAssesData'][0] -> audit_status_id) ? ($data['request'] -> input('audit_status', $data['data']['auditAssesData'][0] -> audit_status_id)) : $data['request'] -> input('audit_status'),
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'audit_status');
        ?>
    </div>
</div>

<?php 
    $btnArray = array('find', 'reset');

    if(!empty($data['data']['details_of_audit_data']) && sizeof($data['data']['details_of_audit_data']) > 0)
        array_push($btnArray, 'print', 'excel');
    generate_report_buttons($btnArray);

?>
