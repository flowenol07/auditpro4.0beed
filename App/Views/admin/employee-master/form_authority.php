<?php 
use Core\FormElements;

    if(sizeof($data['data']['db_audit_unit_data']) > 0):
    ?>

    <div class="card rounded-0">
    <div class="card-header pb-1 font-medium">
        Current Employee Details
    </div>
    <div class="card-body">
        <h5 class="card-title font-medium text-primary mb-1"><?= $data['data']['db_employee_data_arr'][$data['data']['db_data'] -> id] -> combined_name ?></h5>
        <h6 class="card-title font-medium text-primary mb-1"><span class="card-title font-medium text-dark mb-1">User Type : </span><?=($GLOBALS['userTypesArray'][$data['data']['db_data'] -> user_type_id] ?? ERROR_VARS['notFound'] ) ?></h6>
        <p class="mb-0 text-secondary font-sm">Status: <?= check_active_status($data['data']['db_data'] -> is_active); ?>, Created: <?= date($GLOBALS['dateSupportArray'][2], strtotime($data['data']['db_data'] -> created_at)) ?></p>
    </div>
    </div>

    <div class='border bg-white p-4 mt-4'>

    <?php

    echo FormElements::generateFormStart(["name" => "employee-master", "action" => $data['me'] -> url ]);

        // button for check all

        $btnArray = ['id' => 'selectAll','class' => 'selectAll', 'value' => 'Select All', 'type' => true];

        echo FormElements::generateSubmitButton('select', $btnArray );
        echo "<div class='mb-4'></div>";

        // checkboxes for audit unit

        $i = 0;
        $j = 0;

        $authority_data_arr = !empty($data['data']['db_data'] -> audit_unit_authority) ? explode (",", $data['data']['db_data'] -> audit_unit_authority) : null;

        echo '<table class="table table-bordered">
                <tr>';

        foreach($data['data']['db_audit_unit_data'] as $cIndex => $cData)
        {
            $i++;

                if(is_array($authority_data_arr) && in_array($cIndex, $authority_data_arr))
                {
                    $checked = true;
                }
                else{
                    $checked = false;
                }
            echo '<td>';

                echo $markup = FormElements::generateCheckboxOrRadio([
                    "name" => "audit_name[]", "appendClass" => "audit_name",
                    "text" => $cData,
                    "checked" => $checked,
                    "value" => $cIndex,
                ]);

            echo '</td>';

            if($i == 3) 
            {
                echo '</tr><tr>';
                $i = 0;
            }

            if(is_array($authority_data_arr) && $j < count($authority_data_arr))
            {
                $j = 0;
            }

            $j++;
        }
        echo '</tr>
            </table>';

        echo $data['noti']::getInputNoti($data['request'], 'audit_name_err');
        echo "<div class='mb-2'></div>";
    

    $btnArray = [ 'name' => 'submit', 'value' => 'Save Authority', 'btn_type' => 'update'];     

    echo FormElements::generateSubmitButton('update', $btnArray );

    echo FormElements::generateFormClose();

    echo '</div>';

else:
    ?>
    <div class="border bg-white p-4">
<?php
    echo $data['noti']::getCustomAlertNoti('noDataFound');
?>
    </div>
<?php
endif;


$data['data']['inline_js'] = "\n" . '<script>
$(function(){

    checked = 0;

    $("#selectAll").click(function(){
        checked = (checked == 1) ? 0 : 1;
        $(".audit_name").prop("checked", checked);
        return false;
    });  
 });
</script>';

?>

