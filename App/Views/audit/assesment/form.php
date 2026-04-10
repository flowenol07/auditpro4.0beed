<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

if($data['data']['db_data']['error'] == null)
    echo FormElements::generateFormStart(["name" => "audit-assesment", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php
            
                //audit unit
                $markup = FormElements::generateLabel('audit_unit', 'Audit Unit');

                $markup .= FormElements::generateInput([
                    "type" => "text", "value" => $data['data']['db_data']['name'], 
                    "placeholder" => "Audit Unit", "disabled" => true
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'audit_unit');
            ?>
        </div>

        <div class="col-md-6">
            <?php
            
                //frequency
                $markup = FormElements::generateLabel('frequency', 'Frequency of Assesment');

                $markup .= FormElements::generateInput([
                    "type" => "text", "value" => ($data['data']['db_data']['frequency'] . ' Months'), 
                    "placeholder" => "Frequency of Assesment", "disabled" => true
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'frequency');
            ?>
        </div>

        <div class="col-md-12">
            <?php
            
                //Last Audit Done Date
                $markup = FormElements::generateLabel('last_audit_done', 'Last Audit Done Date');

                $markup .= FormElements::generateInput([
                    "type" => "text", "value" => $data['data']['db_data']['last_audit_date'], 
                    "placeholder" => "Last Audit Done Date", "disabled" => true
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'last_audit_date');
            ?>
        </div>

        <div class="col-md-12">
            <?php
            
                //Audit Start Date
                $markup = FormElements::generateLabel('last_audit_done', 'Audit Start Date');

                $markup .= FormElements::generateInput([
                    "type" => "text", "value" => $data['data']['db_data']['audit_start_date'], 
                    "placeholder" => "Audit Start Date", "disabled" => true
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'audit_unit');
            ?>
        </div>

        <div class="col-md-12">
            <?= $data['noti']::getCustomAlertNoti('<span class="font-bold">Note:</span> The audit due date is: <span class="font-bold">' . $data['data']['db_data']['audit_due_date'] . '</span> ('. AUDIT_DUE_ARRAY[1] .' days from the audit start date). After this date, you will not be allowed to conduct audits in the current assessment.', 'danger'); ?>

            <h4 class="lead text-primary font-medium">New Assesment Period</h4>
        </div>

        <div class="col-md-6">
            <?php
            
                //Assesment Period From
                $markup = FormElements::generateLabel('assesment_period_from', 'Assesment Period From');

                $markup .= FormElements::generateInput([
                    "type" => "text", "value" => $data['data']['db_data']['assesment_period_from'], 
                    "placeholder" => "Assesment Period From", "disabled" => true
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'assesment_period_from');
            ?>
        </div>

        <div class="col-md-6">
            <?php
            
                //Assesment Period To
                $markup = FormElements::generateLabel('assesment_period_to', 'Assesment Period To');

                $markup .= FormElements::generateInput([
                    "type" => "text", "value" => $data['data']['db_data']['assesment_period_to'], 
                    "placeholder" => "Assesment Period To", "disabled" => true
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'assesment_period_to');
            ?>
        </div>


    </div>

<?php 

    if($data['data']['db_data']['error'] == null)
    {
        echo FormElements::generateSubmitButton('add', [ 'name' => 'submit', 'value' => 'Start Audit'] );
        echo FormElements::generateFormClose(); 
    }
    else
        echo $data['noti']::getCustomAlertNoti($data['data']['db_data']['error']);    

?>