<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "employee-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php
                //emp_code
                $markup = FormElements::generateLabel('emp_code', 'Employee Code');

                $markup .= FormElements::generateInput([
                    "id" => "emp_code", "name" => "emp_code", 
                    "type" => "text", "value" => $data['request'] -> input('emp_code', $data['data']['db_data'] -> emp_code), 
                    "placeholder" => "Employee Code"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'emp_code');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //user_type
                $markup = FormElements::generateLabel('user_type', 'User Type');

                if(is_array($GLOBALS['userTypesArray']) && sizeof($GLOBALS['userTypesArray']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "user_type", "name" => "user_type", 
                        "default" => ["", "Please select user type"],
                        "options" => $GLOBALS['userTypesArray'],
                        "selected" => $data['request'] -> input('user_type', $data['data']['db_data'] -> user_type_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'user_type');
            ?>
        </div>

        <div class="col-12">    
            <?php

                //name
                $markup = FormElements::generateLabel('name', 'Employee Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Employee Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');

            ?>
        </div>

        <div class="col-md-6">
            <?php
                //email
                $markup = FormElements::generateLabel('email', 'Email ID');

                $markup .= FormElements::generateInput([
                    "id" => "email", "name" => "email", 
                    "type" => "text", "value" => $data['request'] -> input('email', $data['data']['db_data'] -> email), 
                    "placeholder" => "Email ID"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'email');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //mobile
                $markup = FormElements::generateLabel('mobile', 'Mobile Number');

                $markup .= FormElements::generateInput([
                    "id" => "mobile", "name" => "mobile", 
                    "type" => "text", "value" => $data['request'] -> input('mobile', $data['data']['db_data'] -> mobile), 
                    "placeholder" => "Mobile Number"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'mobile');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //designation
                $markup = FormElements::generateLabel('designation', 'Designation');

                $markup .= FormElements::generateInput([
                    "id" => "designation", "name" => "designation", 
                    "type" => "text", "value" => $data['request'] -> input('designation', $data['data']['db_data'] -> designation), 
                    "placeholder" => "Designation"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'designation');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //gender
                $markup = FormElements::generateLabel('gender', 'Gender');

                if(is_array($GLOBALS['userGenderArray']))
                {
                    foreach($GLOBALS['userGenderArray'] as $c_gender_key => $c_gender_profile)
                    {
                        $checked = ($data['request'] -> input('gender') == string_operations($c_gender_key)) ? true : false;

                        if(!$checked)
                            $checked = (string_operations($c_gender_key) == string_operations($data['data']['db_data'] -> gender)) ? true : false;
                        
                        $markup .= FormElements::generateCheckboxOrRadio(['type' => 'radio', 'name' => 'gender', 'value' => $c_gender_key, 'text' => ucfirst($c_gender_key), 'checked' => $checked]);
                    }
                }

                echo FormElements::generateFormGroup($markup, $data, 'gender');
            ?>
        </div>

    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Employee'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Employee';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>