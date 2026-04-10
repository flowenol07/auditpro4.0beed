<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "audit-secion-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //name
                $markup = FormElements::generateLabel('name', 'Section Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Section Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Section'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Section';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>