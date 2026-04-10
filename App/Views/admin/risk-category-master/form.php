<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "risk-category-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //risk_category
                $markup = FormElements::generateLabel('risk_category', 'Risk Category');

                $markup .= FormElements::generateInput([
                    "id" => "risk_category", "name" => "risk_category", 
                    "type" => "text", "value" => $data['request'] -> input('risk_category', $data['data']['db_data'] -> risk_category), 
                    "placeholder" => "Risk Category"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'risk_category');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Risk Category'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Risk Category';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>