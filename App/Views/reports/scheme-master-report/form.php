<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-4">
        <?php
            //scheme_type	
            $markup = FormElements::generateLabel('scheme_type', 'Select Scheme Type');

            if(is_array($GLOBALS['schemeTypesArray']) && sizeof($GLOBALS['schemeTypesArray']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "scheme_type", "name" => "scheme_type", 
                    "default" => ["", "Please select scheme type"],
                    "options" => $GLOBALS['schemeTypesArray'],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=0"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'scheme_type');
        ?>
    </div>

    <div class="col-md-4">
        <?php
            //mapped_category	
            $markup = FormElements::generateLabel('mapped_category', 'Select Category');

            if(is_array($data['data']['db_category_data']) && sizeof($data['data']['db_category_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "mapped_category", "name" => "mapped_category", 
                    "default" => ["", "Please select category"],
                    "options" => $data['data']['db_category_data'],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=2"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'mapped_category');
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
                    "extra" => "data-sort=4"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'status');
        ?>
    </div>
</div>

<?php  generate_report_buttons(['filter', 'print', 'excel', 'reset']); ?>