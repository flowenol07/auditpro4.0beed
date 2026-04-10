<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "audit-frequency-last_asses", "action" => ""]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //branch	
            $markup = FormElements::generateLabel('branch', 'Select Branch');

            if(is_array($data['data']['audit_unit_data']) && sizeof($data['data']['audit_unit_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "branch", "name" => "branch", 
                    "default" => ["", "Please select branch"],
                    "options" => $data['data']['audit_unit_data'],
                    "options_db" => ["type" => "obj", "val" => "name"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=2"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'branch');
        ?>
    </div>    
</div>

<?php  generate_report_buttons(['filter', 'print','excel','reset']); ?>
