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
                    "id" => "audit_id", "name" => "audit_id", 
                    "default" => ["", "Please select Audit Unit"], "appendClass" => "select2search",
                    "options" => $data['data']['audit_unit_data'],
                    "options_db" => ["type" => "obj", "val" => "combined_name"],
                    "selected" => $data['request'] -> input('audit_id', $data['data']['db_data'] -> id ?? '' )
                ]);

            }
            else    
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


            echo FormElements::generateFormGroup($markup, $data, 'scheme_id');
        ?>
    </div>
</div>

<?php 
    $btnArray = array('find');

    if(!empty($data['data']['audit_scoring_data']) && sizeof($data['data']['audit_scoring_data']) > 0){
        array_push($btnArray, 'print', 'excel', 'reset'); 
        generate_report_header($data['data']); 
    }
    generate_report_buttons($btnArray);
?>
