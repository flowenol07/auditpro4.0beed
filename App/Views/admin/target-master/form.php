<?php

use Core\FormElements;

require_once 'single-audit-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "target-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //year_id
                $markup = FormElements::generateLabel('year_id', 'Financial Year');

                if(is_array($data['data']['db_year']) && sizeof($data['data']['db_year']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "year_id", "name" => "year_id", 
                        "default" => ["", "Please select financial year"],
                        "options" => $data['data']['db_year'],
                        "selected" => $data['request'] -> input('year_id', $data['data']['db_data'] -> year_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'year_id');
            ?>
        </div>

        <div class="col-12">    
            <?php
                //deposit_target
                $markup = FormElements::generateLabel('deposit_target', 'Deposit Target (In Lakhs)');

                $markup .= FormElements::generateInput([
                    "id" => "deposit_target", "name" => "deposit_target", 
                    "type" => "text", "value" => $data['request'] -> input('deposit_target', $data['data']['db_data'] -> deposit_target), 
                    "placeholder" => "Enter Amount in Lakhs (xxxx.xx)"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'deposit_target');

            ?>
        </div>

        <div class="col-12">    
            <?php

                //advances_target
                $markup = FormElements::generateLabel('advances_target', 'Advance Target (In Lakhs)');

                $markup .= FormElements::generateInput([
                    "id" => "advances_target", "name" => "advances_target", 
                    "type" => "text", "value" => $data['request'] -> input('advances_target', $data['data']['db_data'] -> advances_target), 
                    "placeholder" => "Enter Amount in Lakhs (xxxx.xx)"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'advances_target');

            ?>
        </div>

        <div class="col-12">    
            <?php

                //npa_target
                $markup = FormElements::generateLabel('npa_target', 'NPA Target (In Lakhs)');

                $markup .= FormElements::generateInput([
                    "id" => "npa_target", "name" => "npa_target", 
                    "type" => "text", "value" => $data['request'] -> input('npa_target', $data['data']['db_data'] -> npa_target), 
                    "placeholder" => "Enter Amount in Lakhs (xxxx.xx)"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'npa_target');

            ?>
        </div>

    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Target'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Target';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>