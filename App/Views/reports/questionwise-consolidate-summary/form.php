<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => ""]);

?>

<div class="row">
    <div class="col-md-12">
        <?php
            //scheme_id
            $markup = FormElements::generateLabel('audit_id', 'Audit Unit');

            if(is_array($data['data']['audit_unit_data']) && sizeof($data['data']['audit_unit_data']) > 0 )
            {
                $markup .= FormElements::generateSelect([
                    "id" => "audit_unit_id", "name" => "audit_unit_id", 
                    "default" => ["", "Please select Audit Unit"], "appendClass" => "select2search",
                    "options" => $data['data']['audit_unit_data'],
                    "options_db" => ["type" => "obj", "val" => "combined_name"],
                    "selected" => $data['request'] -> input('audit_unit_id', $data['data']['db_data'] -> id ?? '' )
                ]);

            }
            else    
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


            echo FormElements::generateFormGroup($markup, $data, 'scheme_id');
        ?>
    </div>
    <div class="col-md-12">
        <?php
            //emp_code
            $markup = FormElements::generateLabel('name', 'Enter Questions ');

            $markup .= FormElements::generateInput([
                "id" => "search_question", "name" => "search_question", 
                "type" => "text", "value" => $data['request'] -> input('search_question', ''), 
                "placeholder" => "Enter Questions"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'search_question');
        ?>
    </div>
</div>

<?php 
    $btnArray = array('find');

    if(!empty($data['data']['question_consolidate']) && sizeof($data['data']['question_consolidate']) > 0)
        array_push($btnArray, 'print', 'excel', 'reset');   
    
    generate_report_buttons($btnArray);
?>
