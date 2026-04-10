<?php

use Core\FormElements;

if(is_object($data['data']['db_data'])):


echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "employee-master", "action" => $data['me'] -> url]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php
                //employee id
                $markup = FormElements::generateLabel('id', 'Employee');

                if(is_array($data['data']['db_employee_data']) && sizeof($data['data']['db_employee_data']) > 0 ){

                    $markup .= FormElements::generateSelect([
                        "id" => "id", "name" => "id", 
                        "default" => ["", "Please select employee"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['db_employee_data'],
                        "selected" => $data['request'] -> input('id', $data['data']['db_data'] -> id)
                    ]);
                }
                else 
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                
                echo FormElements::generateFormGroup($markup, $data, 'id');
            ?>
        </div>

        <div class="col-md-6">
        </div>

        <div class="col-md-6">               
            <?php
                //new password 
                $markup = FormElements::generateLabel('password', 'Password');
        
                $markup .= FormElements::generateInput([
                    "id" => "password", "name" => "password", 
                    "type" => "password", "value" => '', 
                    "placeholder" => "Password"
                ]);
        
                echo FormElements::generateFormGroup($markup, $data, 'password');
            ?>
        </div>

        <div class="col-md-6">
        </div>

        <div class="col-md-6">
            <?php
            
                require_once APP_VIEWS . '/password-policy/password-policy-markup.php';        
                echo '</div>' . "\n";
            ?>
        </div>
<?php
            
$btnArray = [ 'name' => 'submit', 'value' => 'Set Password', 'btn_type' => 'update'];     

echo FormElements::generateSubmitButton('update', $btnArray );    

echo FormElements::generateFormClose();

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>