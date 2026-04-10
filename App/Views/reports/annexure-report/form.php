<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-4">
        <?php
            //annexure	
            $markup = FormElements::generateLabel('annexure', 'Select Annexure');

            if(is_array($data['data']['annex_master_data']) && sizeof($data['data']['annex_master_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "annexure", "name" => "annexure", 
                    "default" => ["", "Please select annexure"],
                    "options" => $data['data']['annex_master_data'],
                    "options_db" => ["type" => "obj", "val" => "name"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=1"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'annexure');
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
                    "options" => $data['data']['risk_category_data'],
                    "options_db" => ["type" => "obj", "val" => "risk_category"],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=2"

                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'risk');
        ?>
    </div>

    <div class="col-md-4">
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
                    "extra" => "data-sort=6"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'status');
        ?>
    </div>
</div>

<?php generate_report_buttons(['filter', 'print', 'reset', 'excel']); ?>