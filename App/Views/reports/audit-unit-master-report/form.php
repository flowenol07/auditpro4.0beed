<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "audit-unit-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //status	
            $markup = FormElements::generateLabel('status', 'Select Status');

            if(is_array(STATUS_ARRAY) && sizeof(STATUS_ARRAY) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "status", "name" => "status", 
                    "default" => ["", "Please select status"],
                    "options" => STATUS_ARRAY,
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=8"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'status');
        ?>
    </div>
</div>

<?php  generate_report_buttons(['filter', 'print', 'excel', 'reset']); ?>