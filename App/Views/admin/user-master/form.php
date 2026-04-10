<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "user-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-6">

    <?php

        //name
        $markup = FormElements::generateLabel('full_name', 'User Name');

        $markup .= FormElements::generateInput([
            "id" => "full_name", "name" => "full_name", 
            "type" => "text", "value" => $data['request'] -> input('full_name', $data['data']['db_data'] -> full_name), 
            "placeholder" => "Username Name"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'full_name');
        
    ?>

    </div>

    <div class="col-md-6">
    <div id="username">
        <?php

        //name
        $markup = FormElements::generateLabel('role_base', 'Role Base');

        $markup .= FormElements::generateSelect([
            "id" => "role_base", "name" => "role_base", 
            "default" => ["", "Please select user base"],
            "options" => $data['data']['adminArray'],
            "selected" => $data['request'] -> input('role_base', $data['data']['db_data'] -> role_base)
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'role_base');
        
        ?>
    </div>
    

    </div>

</div>

    <?php

    $btnArray = [ 'name' => 'submit', 'value' => 'Add User'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Scheme';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

echo FormElements::generateFormClose();