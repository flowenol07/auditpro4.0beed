<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "menu-master", "action" => $data['me'] -> url ]);

?>
<div class="row">
    <div class="col-md-4">
        <?php
            //section	
            $markup = FormElements::generateLabel('section', 'Select Audit Section');

            if(is_array($data['data']['audit_section_data']) && sizeof($data['data']['audit_section_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "section", "name" => "section", 
                    "default" => ["", "Please select section"],
                    "options" => $data['data']['audit_section_data'], "options_db" => ["type" => "obj", "val" => "name"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=1"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'section');
        ?>
    </div>
</div>

<?php  generate_report_buttons(['filter', 'print', 'excel', 'reset']); ?>