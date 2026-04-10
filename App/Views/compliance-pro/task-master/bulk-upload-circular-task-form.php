<?php

use Core\FormElements;

//function for check error
function check_error_task_msg($key, $details_array)
{
    $return_str = $details_array[ $key ];

    if(array_key_exists('error', $details_array) && array_key_exists($key, $details_array['error']))
    {
        $return_str .= '<small class="text-danger d-block">'. $details_array['error'][ $key ] .'</small>';
    }

    return $return_str;
}

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "bulk-upload-circular-task", "action" => $data['me'] -> url, "enctype" => "multipart/form-data" ]);

?>
    <div class="row">

        <div class="col-md-12">
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
                        "selected" => $data['request'] -> input('circular_id'),
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'circular_id');
                echo '<span class="d-block font-sm text-danger mt-1"></span>' . "\n";
            ?>
        </div>

        <div class="col-md-12">
            <?php echo $data['noti']::getCustomAlertNoti('<span class="font-medium">Download: </span> Sample CSV file. <a class="text-danger" href="'. $data['data']['sample_csv'] .'">Click Here</a>', 'warning'); ?>
        </div>

        <div class="col-md-12">
            <?php 

                //csv_file_upload
                $markup = FormElements::generateLabel('csv_file_upload', 'CSV File Upload <span class="text-danger font-sm">(CSV format with comma "," separator data)</span>');

                $markup .= FormElements::generateInput([
                    "id" => "csv_file_upload", "name" => "csv_file_upload", "appendClass" => "form-control-file", "type" => "file", "value" => ""
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'csv_file_upload');

            ?>
        </div>

    </div>

<?php 

        $btnArray = [ 'name' => 'submit', 'value' => 'Add Bulk Task'];
    
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

if(isset($data['data']['err_task_data']) && sizeof($data['data']['err_task_data']) > 0):

$srr_no = 1;

?>

<h5 class="text-primary mt-4 mb-3">Total Tasks: <?= $data['data']['csv_data']; ?> | Total Error Tasks: <?= sizeof($data['data']['err_task_data']); ?></h5>

<div class="table-responsive height-600">
<table class="table table-bordered v-table">
    <tr>
        <th class="text-center">Sr. No.</th>
        <th>Header</th>
        <th>Task</th>
        <th>Priority</th>
        <th>Risk Category</th>
        <th>Business Risk</th>
        <th>Control Risk</th>
        <th>Broader Area</th>
    </tr>

    <?php 
        foreach($data['data']['err_task_data'] as $cTaskDetails):
        
            echo '<tr>' . "\n";
                echo '<td class="text-center">'. $srr_no .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('header_id', $cTaskDetails) .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('task', $cTaskDetails) .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('priority_id', $cTaskDetails) .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('risk_category_id', $cTaskDetails) .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('business_risk', $cTaskDetails) .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('control_risk', $cTaskDetails) .'</td>' . "\n";
                echo '<td>'. check_error_task_msg('area_of_audit_id', $cTaskDetails) .'</td>' . "\n";
            echo '</tr>' . "\n";
            $srr_no++;

        endforeach; 
    ?>

</table>
</div>

<?php endif; ?>