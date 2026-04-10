<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "annexure-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //emp_code
                $markup = FormElements::generateLabel('name', 'Annexure Name    ');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Annexure Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //risk_defination_id

                $checked_custom = (($data['request'] -> input('risk_defination_id', $data['data']['db_data'] -> risk_defination_id)) == '1') ? true : false;
                
                $markup = FormElements::generateLabel('risk_defination_id', 'Risk Defination');                      
                $markup .= FormElements::generateCheckboxOrRadio([
                    'type' => 'radio', 'name' => 'risk_defination_id', 'value' => '1', 'text' => 'Custom', 'checked' => $checked_custom,
                ]);

                $checked_default = (($data['request'] -> input('risk_defination_id', $data['data']['db_data'] -> risk_defination_id)) == '2') ? true : false;

                $markup .= FormElements::generateCheckboxOrRadio([
                    'type' => 'radio', 'name' => 'risk_defination_id', 'value' => '2', 'text' => 'Default', 'checked' => $checked_default,
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'gender');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //risk_category_id
                $markup = FormElements::generateLabel('risk_category_id', 'Default Business Risk Category');

                if(is_array($data['data']['db_risk_category_data']) && sizeof($data['data']['db_risk_category_data']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "risk_category_id", "name" => "risk_category_id", 
                        "default" => ["", "Please select risk category"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['db_risk_category_data'],
                        "selected" => $data['request'] -> input('risk_category_id', $data['data']['db_data'] -> risk_category_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'risk_category_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //business_risk
                $markup = FormElements::generateLabel('business_risk', 'Default Business Risk Per Entry');

                if(RISK_PARAMETERS_ARRAY && sizeof(RISK_PARAMETERS_ARRAY) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "business_risk", "name" => "business_risk", 
                        "default" => ["", "Please select business risk type"],
                        "options" => RISK_PARAMETERS_ARRAY, "options_db" => ["type" => "arr", "val" => "title"],
                        "selected" => $data['request'] -> input('business_risk', $data['data']['db_data'] -> business_risk)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'business_risk');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //control_risk
                $markup = FormElements::generateLabel('control_risk', 'Default Control Risk Per Entry');

                if(RISK_PARAMETERS_ARRAY && sizeof(RISK_PARAMETERS_ARRAY) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "control_risk", "name" => "control_risk", 
                        "default" => ["", "Please select control risk type"],
                        "options" => RISK_PARAMETERS_ARRAY, "options_db" => ["type" => "arr", "val" => "title"],
                        "selected" => $data['request'] -> input('control_risk', $data['data']['db_data'] -> control_risk)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'control_risk');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Annexure'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Annexure';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>