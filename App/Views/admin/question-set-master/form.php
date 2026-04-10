<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "question-set-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //set name
                $markup = FormElements::generateLabel('name', 'Add Set Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Set Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-12">
            <?php

                //set_type_id
                $markup = FormElements::generateLabel('set_type_id', 'Set Type
                ');

                if($GLOBALS['setTypesArray'] && sizeof($GLOBALS['setTypesArray']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "set_type_id", "name" => "set_type_id", 
                        "default" => ["", "Please select set type"],
                        "options" => $GLOBALS['setTypesArray'],
                        "selected" => $data['request'] -> input('set_type_id', $data['data']['db_data'] -> set_type_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'set_type_id');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Set'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Set';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>