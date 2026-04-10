<?php

use Core\FormElements;

if(is_object($data['data']['db_data'])):

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "password-policy", "action" => $data['me'] -> url ]);
?>

<div class="row">
        <div class="col-md-12">
            <?php
                //min_length
                $markup = FormElements::generateLabel('min_length', 'Min Length');

                $markup .= FormElements::generateInput([
                    "id" => "min_length", "name" => "min_length", 
                    "type" => "text", "value" => $data['request'] -> input('min_length', $data['data']['db_data'] -> min_length), 
                    "placeholder" => "Minimum password length"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'min_length');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //num_cnt
                $markup = FormElements::generateLabel('num_cnt', 'Count Of Number');

                $markup .= FormElements::generateInput([
                    "id" => "num_cnt", "name" => "num_cnt", 
                    "type" => "text", "value" => $data['request'] -> input('num_cnt', $data['data']['db_data'] -> num_cnt), 
                    "placeholder" => "Numbers like [0-9]"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'num_cnt');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //symbol_cnt
                $markup = FormElements::generateLabel('symbol_cnt', 'Count Of Special Characters');

                $markup .= FormElements::generateInput([
                    "id" => "symbol_cnt", "name" => "symbol_cnt", 
                    "type" => "text", "value" => $data['request'] -> input('symbol_cnt', $data['data']['db_data'] -> symbol_cnt), 
                    "placeholder" => "Number of special characters like " . $data['data']['allowedChars']
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'symbol_cnt');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //uppercase_cnt
                $markup = FormElements::generateLabel('uppercase_cnt', 'Count Of Uppercase Letter');

                $markup .= FormElements::generateInput([
                    "id" => "uppercase_cnt", "name" => "uppercase_cnt", 
                    "type" => "text", "value" => $data['request'] -> input('uppercase_cnt', $data['data']['db_data'] -> uppercase_cnt), 
                    "placeholder" => "Number of uppercase characters [A-Z]"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'uppercase_cnt');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //lowercase_cnt
                $markup = FormElements::generateLabel('lowercase_cnt', 'Count Of Lowercase Letter');

                $markup .= FormElements::generateInput([
                    "id" => "lowercase_cnt", "name" => "lowercase_cnt", 
                    "type" => "text", "value" => $data['request'] -> input('lowercase_cnt', $data['data']['db_data'] -> lowercase_cnt), 
                    "placeholder" => "Number of lowercase characters like [a-z]"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'lowercase_cnt');
            ?>
        </div>
    </div>

<?php
    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Change Password Policy';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

echo FormElements::generateFormClose();

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>