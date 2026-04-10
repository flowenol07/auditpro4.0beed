<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => ""]);

// print_r($data['data']['audit_unit_data']);
?>

<div class="row">
    <div class="col-md-12">
        <?php
            //audit_units	
            $markup = FormElements::generateLabel('audit_unit_id', 'Search Type');

            if(is_array($data['data']['options_data']) && sizeof($data['data']['options_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "audit_unit_id", "name" => "audit_unit_id", 
                    "default" => ["", "Please select search types"],
                    "options" => $data['data']['options_data'],
                    "options_db" => ["type" => "obj", "val" => "combined_name"],
                    "selected" => isset($data['data']['auditAssesData'][0] -> audit_unit_id) ? ($data['request'] -> input('audit_unit_id', $data['data']['auditAssesData'][0] -> audit_unit_id)) : $data['request'] -> input('audit_unit_id'),
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'audit_unit_id');
        ?>
    </div>
</div>

<?php 
    $btnArray = array('find', 'reset');

    if(!empty($data['data']['not_started_branches']) && sizeof($data['data']['not_started_branches']) > 0)
        array_push($btnArray, 'print', 'excel');
    generate_report_buttons($btnArray);

?>
