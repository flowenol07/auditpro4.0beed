<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "compliance-circular-master", "action" => $data['me'] -> url ]);

?>
<div class="row">
    <div class="col-md-4">
        <?php
            //section	
            $markup = FormElements::generateLabel('section', 'Select Circular Applicability');

            if(is_array($data['data']['applicable_status']) && sizeof($data['data']['applicable_status']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "section", "name" => "section", 
                    "default" => ["", "Please select applicability"],
                    "options" => $data['data']['applicable_status'],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=3"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'section');
        ?>
    </div>
</div>

<?php  generate_report_buttons(['filter', 'print', 'excel', 'reset']); ?>