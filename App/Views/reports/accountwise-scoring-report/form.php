<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => ""]);


?>

<div class="row">

    <?php require_once REPORTS_VIEW . DS . '_report-partials/date-filters.php'; ?>

    <div class="col-md-6">
        <?php
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
        ?>
    </div>

    <div class="col-md-6">
    <?php
            //scheme_type	
            $markup = FormElements::generateLabel('scheme_type', 'Select Type');

            if(is_array($GLOBALS['schemeTypesArray']) && sizeof($GLOBALS['schemeTypesArray']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "scheme_type", "name" => "scheme_type", 
                    "default" => ["", "Please select type"],
                    "options" => $GLOBALS['schemeTypesArray'],
                    "selected" => isset($data['data']['auditAssesData'][0] -> year_id) ? ($data['request'] -> input('scheme_type', $data['data']['auditAssesData'][0] -> year_id)) : $data['request'] -> input('scheme_type'),
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'scheme_type');
        ?>
    </div>
</div>

<?php 
    $btnArray = array('find');

    if(!empty($data['data']['details_of_account_data']) && sizeof($data['data']['details_of_account_data']) > 0)
        array_push($btnArray, 'print', 'excel', 'reset');
    
    generate_report_buttons($btnArray);
?>
