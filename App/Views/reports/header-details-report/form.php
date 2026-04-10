<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "employee-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //set	
            $markup = FormElements::generateLabel('set', 'Select Set');

            if(is_array($data['data']['set_select_data']) && sizeof($data['data']['set_select_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "set", "name" => "set", 
                    "default" => ["", "Please select set"],
                    "options" => $data['data']['set_select_data'],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=1"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'set');
        ?>
    </div>

    <div class="col-md-6">
        <?php
            //setType	
            $markup = FormElements::generateLabel('setType', 'Select Set Type');

            if(is_array($GLOBALS['setTypesArray']) && sizeof($GLOBALS['setTypesArray']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "setType", "name" => "setType", 
                    "default" => ["", "Please select set type"],
                    "options" => $GLOBALS['setTypesArray'],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=3"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'setType');
        ?>
    </div>
</div>

<?php generate_report_buttons(['filter', 'print', 'excel', 'reset']); ?>