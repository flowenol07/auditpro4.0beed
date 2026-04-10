<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "task-master","action" => $data['me'] -> url,"method" => "GET"] );?>

<div class="row"> 
    <div class="col-md-6">
        <?php
            // circular_id
            $markup = FormElements::generateLabel('circular_id', 'Circular');

            if(is_array($data['data']['circularData']) && sizeof($data['data']['circularData']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "circular_id", "name" => "circular_id", 
                    "default" => ["", "Please select circular"],
                    "appendClass" => "select2search",
                    "options" => $data['data']['circularData'],
                    "options_db" => ["type" => "obj", "val" => "name"],
                    "selected" => $data['request'] -> input('circular_id')
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'circular_id');
        ?>
    </div>

            <div class="col-md-6">
            <?php

                // circular frequency
                $markup = FormElements::generateLabel('frequency', 'Task Set Frequency');
                if( isset($data['data']['init_frequency']) && 
                    sizeof($data['data']['init_frequency']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "frequency", "name" => "frequency", 
                        "default" => ["", "Please select circular frequency"],
                        "options" => $data['data']['init_frequency'], "options_db" => ["type" => "arr", "val" => "title"],
                        "selected" => $data['request'] -> input('frequency')
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'frequency');
            ?>
        </div>
</div>

<?php

    $btnArray = [ 'value' => 'Search'];
        echo FormElements::generateSubmitButton('search', $btnArray );

    echo FormElements::generateFormClose(); 

$data['data']['inline_js'] = "\n" . '
<script>
</script>';
?>