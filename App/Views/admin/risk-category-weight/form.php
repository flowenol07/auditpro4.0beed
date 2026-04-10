<?php

use Core\FormElements;

require_once 'single-risk-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "risk-category-weight", "action" => $data['me'] -> url ]);

?>
    <div class="row">

    <div class="col-md-12">
            <?php
                //year_id
                $markup = FormElements::generateLabel('year_id', 'Select Financial Year');

                if(is_array($data['data']['db_year']) && sizeof($data['data']['db_year']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "year_id", "name" => "year_id", 
                        "default" => ["", "Please select year"],
                        "options" => $data['data']['db_year'],
                        "selected" => $data['request'] -> input('year_id', $data['data']['db_data'] -> year_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                
                echo FormElements::generateFormGroup($markup, $data, 'year_id');
            ?>
        </div>
        <div class="col-md-12">
            <?php
                //risk_weight
                $markup = FormElements::generateLabel('risk_weight', 'Risk Weight');

                $markup .= FormElements::generateInput([
                    "id" => "risk_weight", "name" => "risk_weight", 
                    "type" => "text", "value" => $data['request'] -> input('risk_weight', $data['data']['db_data'] -> risk_weight), 
                    "placeholder" => "Risk Weight"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'risk_weight');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //risk_appetite_percent
                $markup = FormElements::generateLabel('risk_appetite_percent', 'Risk Appetite Percent');

                $markup .= FormElements::generateInput([
                    "id" => "risk_appetite_percent", "name" => "risk_appetite_percent", 
                    "type" => "text", "value" => $data['request'] -> input('risk_appetite_percent', $data['data']['db_data'] -> risk_appetite_percent), 
                    "placeholder" => "Risk Appetite Percent"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'risk_appetite_percent');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Weight'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Weight';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>