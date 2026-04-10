<?php

use Core\FormElements;

// require_once 'single-audit-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "single-deposit", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php
                //branch_id
                $markup = FormElements::generateLabel('branch_id', 'Branch');

                if(is_array($data['data']['db_audit_unit']) && sizeof($data['data']['db_audit_unit']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "branch_id", "name" => "branch_id", 
                        "default" => ["", "Please select branch"], "appendClass" => "select2search",
                        "options" => $data['data']['db_audit_unit'],
                        "selected" => $data['request'] -> input('branch_id', $data['data']['db_data'] -> branch_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'branch_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //scheme_id
                $markup = FormElements::generateLabel('scheme_id', 'Scheme Code');

                if(is_array($data['data']['db_deposit_scheme_name_code']) && sizeof($data['data']['db_deposit_scheme_name_code']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "scheme_id", "name" => "scheme_id", 
                        "default" => ["", "Please select scheme code"], "appendClass" => "select2search",
                        "options" => $data['data']['db_deposit_scheme_name_code'],
                        "options_db" => ["type" => "obj", "val" => "combined_name"],
                        "selected" => $data['request'] -> input('scheme_id', $data['data']['db_data'] -> scheme_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'scheme_id');
            ?>
        </div>

        <div class="col-6">    
            <?php

                //account_no
                $markup = FormElements::generateLabel('account_no', 'Account Number');

                $markup .= FormElements::generateInput([
                    "id" => "account_no", "name" => "account_no", 
                    "type" => "text", "value" => $data['request'] -> input('account_no', $data['data']['db_data'] -> account_no), 
                    "placeholder" => "Enter Account Number"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'account_no');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //account_holder_name
                $markup = FormElements::generateLabel('account_holder_name', 'Account Holder Name');

                $markup .= FormElements::generateInput([
                    "id" => "account_holder_name", "name" => "account_holder_name", 
                    "type" => "text", "value" => $data['request'] -> input('account_holder_name', $data['data']['db_data'] -> account_holder_name), 
                    "placeholder" => "Enter Account Holder Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'account_holder_name');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //ucic
                $markup = FormElements::generateLabel('ucic', 'Customer Id');

                $markup .= FormElements::generateInput([
                    "id" => "ucic", "name" => "ucic", 
                    "type" => "text", "value" => $data['request'] -> input('ucic', $data['data']['db_data'] -> ucic), 
                    "placeholder" => "Enter Customer Id"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'ucic');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //customer_type
                $markup = FormElements::generateLabel('customer_type', 'Customer Type');

                $markup .= FormElements::generateInput([
                    "id" => "customer_type", "name" => "customer_type", 
                    "type" => "text", "value" => $data['request'] -> input('customer_type', $data['data']['db_data'] -> customer_type), 
                    "placeholder" => "Enter Customer Type"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'customer_type');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //intrest_rate
                $markup = FormElements::generateLabel('intrest_rate', 'Rate of Interest');

                $markup .= FormElements::generateInput([
                    "id" => "intrest_rate", "name" => "intrest_rate", 
                    "type" => "text", "value" => $data['request'] -> input('intrest_rate', $data['data']['db_data'] -> intrest_rate), 
                    "placeholder" => "Enter ROI"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'intrest_rate');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //principal_amount
                $markup = FormElements::generateLabel('principal_amount', 'Principal Amount');

                $markup .= FormElements::generateInput([
                    "id" => "principal_amount", "name" => "principal_amount", 
                    "type" => "text", "value" => $data['request'] -> input('principal_amount', $data['data']['db_data'] -> principal_amount), 
                    "placeholder" => "Enter Principal Amount"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'principal_amount');

            ?>
        </div>

        <div class="col-md-6">
            <?php 

            //account_opening_date
            $markup = FormElements::generateLabel('account_opening_date', 'Account Open Date');

            $markup .= FormElements::generateInput([
                "id" => "account_opening_date", "name" => "account_opening_date", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('account_opening_date', $data['data']['db_data'] -> account_opening_date),
                "placeholder" => "Account Open Date"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'account_opening_date');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //balance
                $markup = FormElements::generateLabel('balance', 'Balance');

                $markup .= FormElements::generateInput([
                    "id" => "balance", "name" => "balance", 
                    "type" => "text", "value" => $data['request'] -> input('balance', $data['data']['db_data'] -> balance), 
                    "placeholder" => "Enter balance"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'balance');

            ?>
        </div>

        <div class="col-md-6">
            <?php 

            //balance_date
            $markup = FormElements::generateLabel('balance_date', 'Balance Date');

            $markup .= FormElements::generateInput([
                "id" => "balance_date", "name" => "balance_date", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('balance_date', $data['data']['db_data'] -> balance_date),
                "placeholder" => "Balance Date"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'balance_date');

            ?>
        </div>

        <div class="col-md-6">
            <?php 

            //maturity_date
            $markup = FormElements::generateLabel('maturity_date', 'Maturity Date');

            $markup .= FormElements::generateInput([
                "id" => "maturity_date", "name" => "maturity_date", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('maturity_date', $data['data']['db_data'] -> maturity_date),
                "placeholder" => "Maturity Date"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'maturity_date');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //maturity_amount
                $markup = FormElements::generateLabel('maturity_amount', 'Maturity Amount');

                $markup .= FormElements::generateInput([
                    "id" => "maturity_amount", "name" => "maturity_amount", 
                    "type" => "text", "value" => $data['request'] -> input('maturity_amount', $data['data']['db_data'] -> maturity_amount), 
                    "placeholder" => "Enter maturity amount"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'maturity_amount');

            ?>
        </div>

        <div class="col-md-6">
            <?php 

            //close_date
            $markup = FormElements::generateLabel('close_date', 'Close Date');

            $markup .= FormElements::generateInput([
                "id" => "close_date", "name" => "close_date", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('close_date', $data['data']['db_data'] -> close_date),
                "placeholder" => "Close Date"
            ]);
            echo FormElements::generateFormGroup($markup, $data, 'close_date');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //account_status
                $markup = FormElements::generateLabel('account_status', 'Account Status');

                $markup .= FormElements::generateInput([
                    "id" => "account_status", "name" => "account_status", 
                    "type" => "text", "value" => $data['request'] -> input('account_status', $data['data']['db_data'] -> account_status), 
                    "placeholder" => "Account Status"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'account_status');

            ?>
        </div>

        <div class="col-6"></div>

        <div class="col-md-6">
            <?php 

            //upload_period_from
            $markup = FormElements::generateLabel('upload_period_from', 'Upload Period From');

            $markup .= FormElements::generateInput([
                "id" => "upload_period_from", "name" => "upload_period_from", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('upload_period_from', $data['data']['db_data'] -> upload_period_from),
                "placeholder" => "Upload Period From"
            ]);
            echo FormElements::generateFormGroup($markup, $data, 'upload_period_from');

            ?>
        </div>

        <div class="col-md-6">
            <?php 

            //upload_period_to
            $markup = FormElements::generateLabel('upload_period_to', 'Upload Period To');

            $markup .= FormElements::generateInput([
                "id" => "upload_period_to", "name" => "upload_period_to", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('upload_period_to', $data['data']['db_data'] -> upload_period_to),
                "placeholder" => "Upload Period To"
            ]);
            echo FormElements::generateFormGroup($markup, $data, 'upload_period_to');

            ?>
        </div>

    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Account'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Account';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>