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

                if(is_array($data['data']['db_advances_scheme_name_code']) && sizeof($data['data']['db_advances_scheme_name_code']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "scheme_id", "name" => "scheme_id", 
                        "default" => ["", "Please select scheme code"], "appendClass" => "select2search",
                        "options" => $data['data']['db_advances_scheme_name_code'],
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

        <div class="col-md-6">
            <?php 

            //renewal_date
            $markup = FormElements::generateLabel('renewal_date', 'Renewal Date');

            $markup .= FormElements::generateInput([
                "id" => "renewal_date", "name" => "renewal_date", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('renewal_date', $data['data']['db_data'] -> renewal_date),
                "placeholder" => "Renewal Date"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'renewal_date');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //sanction_amount
                $markup = FormElements::generateLabel('sanction_amount', 'Sanction Ammount');

                $markup .= FormElements::generateInput([
                    "id" => "sanction_amount", "name" => "sanction_amount", 
                    "type" => "text", "value" => $data['request'] -> input('sanction_amount', $data['data']['db_data'] -> sanction_amount), 
                    "placeholder" => "Enter Sanction Amount"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'sanction_amount');

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

        <div class="col-md-6">
            <?php 

            //due_date
            $markup = FormElements::generateLabel('due_date', 'Due Date');

            $markup .= FormElements::generateInput([
                "id" => "due_date", "name" => "due_date", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('due_date', $data['data']['db_data'] -> due_date),
                "placeholder" => "Due Date"
            ]);
            echo FormElements::generateFormGroup($markup, $data, 'due_date');

            ?>
        </div>

        <div class="col-6">    
            <?php

                //outstanding_balance
                $markup = FormElements::generateLabel('outstanding_balance', 'Outstanding Balance');

                $markup .= FormElements::generateInput([
                    "id" => "outstanding_balance", "name" => "outstanding_balance", 
                    "type" => "text", "value" => $data['request'] -> input('outstanding_balance', $data['data']['db_data'] -> outstanding_balance), 
                    "placeholder" => "Enter Outstanding Balance"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'outstanding_balance');

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

        <div class="col-6">    
            <?php

                //npa_status
                $markup = FormElements::generateLabel('npa_status', 'NPA Status');

                $markup .= FormElements::generateInput([
                    "id" => "npa_status", "name" => "npa_status", 
                    "type" => "text", "value" => $data['request'] -> input('npa_status', $data['data']['db_data'] -> npa_status), 
                    "placeholder" => "Enter NPA Status"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'npa_status');

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