<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //category	
            $markup = FormElements::generateLabel('category', 'Select Category');

            if(is_array($data['data']['category_select_data']) && sizeof($data['data']['category_select_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "category", "name" => "category", 
                    "default" => ["", "Please select category"],
                    "options" => $data['data']['category_select_data'],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=1"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'scheme_type');
        ?>
    </div>

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
                    "extra" => "data-sort=7"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'status');
        ?>
    </div>
</div>

<?php generate_report_buttons(['filter', 'print', 'reset', 'excel']) ?>