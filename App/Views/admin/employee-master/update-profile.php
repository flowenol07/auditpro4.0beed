<?php

use Core\FormElements;


echo FormElements::generateFormStart(["name" => "update-profile", "action" => $data['siteUrls']::getUrl('updateProfile')]);

    echo $data['noti']::getSessionAlertNoti();    

    //emp_code
    $markup = FormElements::generateLabel('', 'Employee Code');

    $markup .= FormElements::generateInput([ 
        "value" => $data['request'] -> input('emp_code', $data['data']['user_data'] -> emp_code), 
        "placeholder" => "Enter employee code", 'disabled' => true
    ]);

    echo FormElements::generateFormGroup($markup, $data, 'emp_code');

    //name
    $markup = FormElements::generateLabel('name', 'Employee Name');

    $markup .= FormElements::generateInput([
        "id" => "name", "name" => "name", 
        "value" => $data['request'] -> input('name', $data['data']['user_data'] -> name), 
        "placeholder" => "Enter employee name"
    ]);

    echo FormElements::generateFormGroup($markup, $data, 'name');

    //gender
    $markup = FormElements::generateLabel('gender', 'Gender');

    if(is_array($GLOBALS['userGenderArray']))
    {
        foreach($GLOBALS['userGenderArray'] as $c_gender_key => $c_gender_profile)
        {
            $checked = ($data['request'] -> input('gender') == string_operations($c_gender_key)) ? true : false;

            if(!$checked)
                $checked = (string_operations($c_gender_key) == string_operations($data['data']['user_data'] -> gender)) ? true : false;
            
            $markup .= FormElements::generateCheckboxOrRadio(['type' => 'radio', 'name' => 'gender', 'value' => $c_gender_key, 'text' => ucfirst($c_gender_key), 'checked' => $checked]);
        }
    }

    echo FormElements::generateFormGroup($markup, $data, 'gender');

    echo '<div class="row">' . "\n";

        echo '<div class="col-md-6">' . "\n";

            //email
            $markup = FormElements::generateLabel('', 'Email');

            $markup .= FormElements::generateInput([ 
                "value" => $data['request'] -> input('email', $data['data']['user_data'] -> email), 
                "placeholder" => "Enter email address", 'disabled' => true
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'email');

        echo '</div>' . "\n";
        
        echo '<div class="col-md-6">' . "\n";

            //mobile
            $markup = FormElements::generateLabel('mobile', 'Mobile');

            $markup .= FormElements::generateInput([
                "id" => "mobile", "name" => "mobile", 
                "value" => $data['request'] -> input('mobile', $data['data']['user_data'] -> mobile), 
                "placeholder" => "Enter mobile number"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'mobile');

        echo '</div>' . "\n";

    echo '</div>' . "\n";

    $markup = FormElements::generateCheckboxOrRadio([
        'type' => 'checkbox', 'id' => 'change_password', 'name' => 'change_password', 
        'value' => 'change_password', 'text' => 'Do you want to change your password ?',
        'checked' => (($data['request'] -> input('change_password') == 'change_password') ? 1 : 0), 
        'customLabelClass' => 'font-medium text-primary',
        'dataAttributes' => [ 'data-bs-toggle' => 'collapse', 'data-bs-target' => '#change_password_div' ],
        'extra' => 'role="button" aria-expanded="false" aria-controls="change_password_div"'
    ]);

    echo FormElements::generateFormGroup($markup);

    echo '<div id="change_password_div" class="collapse'. (($data['request'] -> input('change_password') == 'change_password') ? ' show' : '') .'">' . "\n";

        if(isset($data['data']['db_data']) && is_object($data['data']['db_data'])):

        //old password
        $markup = FormElements::generateLabel('current_password', 'Old / Current Password');

        $markup .= FormElements::generateInput([
            "id" => "current_password", "name" => "current_password", 
            "type" => "password", "value" => '', 
            "placeholder" => "Old / Current password"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'current_password');

        //new password 
        $markup = FormElements::generateLabel('password', 'Password');

        $markup .= FormElements::generateInput([
            "id" => "password", "name" => "password", 
            "type" => "password", "value" => '', 
            "placeholder" => "Password"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'password');

        //confirm password
        $markup = FormElements::generateLabel('confirm_password', 'Confirm Password');

        $markup .= FormElements::generateInput([
            "id" => "confirm_password", "name" => "confirm_password", 
            "type" => "password", "value" => '',
            "placeholder" => "Confirm password"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'confirm_password');

        require_once APP_VIEWS . DS . 'password-policy/password-policy-markup.php';

        else:

            echo $data['noti']::getCustomAlertNoti('passwordPolicyNotFound');
            
        endif;

        echo '<div class="mb-3"></div>' . "\n";

    echo '</div>' . "\n";

    $btnArray = [ 'name' => 'submit', 'value' => 'Update Profile'];     

    echo FormElements::generateSubmitButton('update', $btnArray );
 

echo FormElements::generateFormClose();

?>