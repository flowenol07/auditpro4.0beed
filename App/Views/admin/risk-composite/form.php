<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "risk-composite", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //composite risk name
                $markup = FormElements::generateLabel('name', 'Composite Risk');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Composite Risk"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-12">
            <?php

                //business_risk
                $markup = FormElements::generateLabel('business_risk', 'Business Risk
                ');

                if(RISK_PARAMETERS_ARRAY && sizeof(RISK_PARAMETERS_ARRAY) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "business_risk", "name" => "business_risk", 
                        "default" => ["", "Please select business risk"],
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
                $markup = FormElements::generateLabel('control_risk', 'Control Risk
                ');

                if(RISK_PARAMETERS_ARRAY && sizeof(RISK_PARAMETERS_ARRAY) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "control_risk", "name" => "control_risk", 
                        "default" => ["", "Please select control risk"],
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

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Composite Risk'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Composite Risk';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>