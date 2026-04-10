<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

if(is_array($data['data']['taskSetData']) && sizeof($data['data']['taskSetData']) > 0):

    echo FormElements::generateFormStart(["name" => "compliance-status-sort-form", "method" => "get", "action" => $data['me'] -> url ]);

?>
        <div class="row">

            <div class="col-md-6">
                <?php

                    // Circular Task Set
                    $markup = FormElements::generateLabel('task_set_id', 'Select Task Set');

                    $markup .= FormElements::generateSelect([
                        "id" => "task_set_id", "name" => "tsid", 
                        "default" => ["", "Please select task set"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['taskSetData'],
                        "options_db" => ["type" => "obj", "val" => "combined_name"],
                        "selected" => $data['request'] -> input('tsid'),
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'tsid');
                ?>
            </div>

            <div class="col-md-6">
                <?php

                    // Circular Task Set
                    $markup = FormElements::generateLabel('com_status_id', 'Select Compliance Status');

                    $markup .= FormElements::generateSelect([
                        "id" => "com_status_id", "name" => "csid", 
                        "default" => ["", "Please select compliance status"],
                        "options" => $data['data']['complianceStatus'],
                        "options_db" => ["type" => "arr", "val" => "title"],
                        "selected" => $data['request'] -> input('csid'),
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'csid');
                ?>
            </div>

        </div>

<?php 

        $btnArray = [ 'value' => 'Find'];

        echo FormElements::generateSubmitButton('search', $btnArray );

    echo FormElements::generateFormClose(); 
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;


?>