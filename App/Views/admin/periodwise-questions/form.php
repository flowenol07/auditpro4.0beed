<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "periodwise-set", "action" => $data['me'] -> url ]);

?>

<div class="row">

    <div class="col-md-12">

    <?php 

        // user_type_id
        $markup = FormElements::generateLabel('user_type_id', 'User Type');

        if(is_array($data['data']['userType']) && sizeof($data['data']['userType']) > 0)
        {
            $markup .= FormElements::generateSelect([
                "id" => "user_type_id", "name" => "user_type_id", 
                "default" => ["", "Please select user type"],
                "options" => $data['data']['userType'],
                "selected" => $data['request'] -> input('user_type_id', $data['data']['db_data'] -> user_type_id)
            ]);

        }
        else
            $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

        echo FormElements::generateFormGroup($markup, $data, 'user_type_id');

    ?>

    </div>

    <div class="col-md-6">

    <?php 

        //year_id
        $markup = FormElements::generateLabel('year_id', 'Financial Year');

        if(is_array($data['data']['year_data']) && sizeof($data['data']['year_data']) > 0)
        {
            $markup .= FormElements::generateSelect([
                "id" => "year_id", "name" => "year_id", 
                "default" => ["", "Please select financial year"],
                "options" => $data['data']['year_data'], "options_db" => ["type" => "obj", "val" => "year"],
                "selected" => $data['request'] -> input('year_id', $data['data']['db_data'] -> year_id)
            ]);

        }
        else
            $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

        echo FormElements::generateFormGroup($markup, $data, 'year_id');

    ?>

    </div>

    <div class="col-md-6">

    <?php 

        //audit_unit_id
        $markup = FormElements::generateLabel('audit_unit_id', 'Audit Unit');

        if(is_array($data['data']['audit_unit_data']) && sizeof($data['data']['audit_unit_data']) > 0)
        {
            $markup .= FormElements::generateSelect([
                "id" => "audit_unit_id", "name" => "audit_unit_id", 
                "default" => ["", "Please select audit unit"],
                "appendClass" => "select2search",
                "options" => $data['data']['audit_unit_data'], "options_db" => ["type" => "obj", "val" => "combined_name"],
                "selected" => $data['request'] -> input('audit_unit_id', $data['data']['db_data'] -> audit_unit_id)
            ]);

        }
        else
            $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

        echo FormElements::generateFormGroup($markup, $data, 'audit_unit_id');

    ?>

    </div>

    <div class="clearfix"></div>

    <div class="col-md-6">

    <?php 

        // start_month_year
        $markup = FormElements::generateLabel('start_month_year', 'Start Month');

        $markup .= FormElements::generateInput([
            "id" => "start_month_year", "name" => "start_month_year", 
            "type" => "text", "value" => $data['request'] -> input('start_month_year', $data['data']['db_data'] -> start_month_year), 
            "placeholder" => "Start Month"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'start_month_year');

    ?>

    </div>

    <div class="col-md-6">

    <?php 

        // end_month_year
        $markup = FormElements::generateLabel('end_month_year', 'End Month');

        $markup .= FormElements::generateInput([
            "id" => "end_month_year", "name" => "end_month_year", 
            "type" => "text", "value" => $data['request'] -> input('end_month_year', $data['data']['db_data'] -> end_month_year), 
            "placeholder" => "End Month"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'end_month_year');

    ?>

    </div>

</div>

<?php

$btnArray = [ 'name' => 'submit', 'value' => 'Add Period Wise Set'];     

if($data['data']['btn_type'] == 'update')
{
    $btnArray['value'] = 'Update Period Wise Set';
    echo FormElements::generateSubmitButton('update', $btnArray );
}
else
    echo FormElements::generateSubmitButton('add', $btnArray );

echo FormElements::generateFormClose(); 

?>