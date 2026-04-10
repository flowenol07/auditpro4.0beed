<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "header-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">

        <div class="col-md-12">
            <?php

                // circular_set_id
                $markup = FormElements::generateLabel('circular_set_id', 'Circular');

                if(is_array($data['data']['circularData']) && sizeof($data['data']['circularData']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "circular_set_id", "name" => "circular_set_id", 
                        "default" => ["", "Please select circular"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['circularData'],
                        "options_db" => ["type" => "obj", "val" => "name"],
                        "selected" => $data['request'] -> input('circular_set_id', $data['data']['db_data'] -> circular_set_id),
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'circular_set_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                // header
                $markup = FormElements::generateLabel('name', 'Header Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Header Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>
    </div>

    <?php if($data['data']['btn_type'] == 'add'): ?>

        <div class="col-md-12">
            <?php
                // back to task master
                $markup = FormElements::generateCheckboxOrRadio([
                    'type' => 'checkbox', 'id' => 'send_back', 'name' => 'send_back', 
                    'value' => '1', 'text' => 'Send Back to Task Add',
                    'checked' => (($data['request'] -> input('send_back') == 1 ) ? true : false), 
                    'customLabelClass' => 'font-medium text-primary'
                ]);
            
                echo FormElements::generateFormGroup($markup);
            ?>
        </div>

    <?php endif; ?>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Header'];

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Header';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>