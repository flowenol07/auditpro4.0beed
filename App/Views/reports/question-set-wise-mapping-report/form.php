<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => ""]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //set	
            $markup = FormElements::generateLabel('set', 'Select Set');

            if(is_array($data['data']['set_data']) && sizeof($data['data']['set_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "set", "name" => "set", 
                    "default" => ["", "Please select set"],
                    "options" => $data['data']['set_data'],
                    "options_db" => ["type" => "obj", "val" => "name"],
                    "selected" => isset($data['data']['questions_data'][0] -> set_id) ? ($data['request'] -> input('set', $data['data']['questions_data'][0] -> set_id)) : '',
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'set');
        ?>
    </div>
</div>

<?php 
    $btnArray = array('find');

    if(!empty($data['data']['questions_data']) && sizeof($data['data']['questions_data']) > 0)
         array_push($btnArray, 'print', 'excel', 'reset');   
    
    generate_report_buttons($btnArray);
?>
