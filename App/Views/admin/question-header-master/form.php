<?php

use Core\FormElements;

require_once 'single-set-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "question-header-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //header name
                $markup = FormElements::generateLabel('name', 'Add Header Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Header Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>
    </div>

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