<?php

use Core\FormElements;

require_once 'single-control-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "risk-control-key-aspect", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //key aspect name
                $markup = FormElements::generateLabel('name', 'Key Aspect');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Key Aspect"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Key Aspect'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Key Aspect';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>