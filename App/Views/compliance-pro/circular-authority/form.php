<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "compliance-circular-authority", "action" => $data['me'] -> url ]);

?>
    <div class="row">

        <div class="col-md-12">
            <?php
                // circular authority
                $markup = FormElements::generateLabel('name', 'Circular Authority');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Circular Authority"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add authority'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update authority';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>