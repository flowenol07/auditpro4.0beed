<?php use Core\FormElements; ?>

</div>

<div class="card rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Audit Remove Blocked
  </div>
  <div class="card-body">

<?php

echo FormElements::generateFormStart(["name" => "remove-block"]);

if(in_array($data['data']['db_data'] -> audit_status_id, [ 
    ASSESMENT_TIMELINE_ARRAY[1]['status_id'], 
    ASSESMENT_TIMELINE_ARRAY[2]['status_id'],
    ASSESMENT_TIMELINE_ARRAY[3]['status_id'] ])):
    echo '<h4 class="lead font-medim text-danger">Audit - Reviewer blocked due to exceed limit</h4>' . "\n";
else:
    echo '<h4 class="lead font-medim text-danger">Compliance - Reviewer blocked due to exceed limit</h4>' . "\n";
endif;

//increase_limit_id
$markup = FormElements::generateLabel('increase_limit_id', 'Increase Limit
');

if(is_array($data['data']['increase_blocked_array']) && sizeof($data['data']['increase_blocked_array']) > 0)
{
    $markup .= FormElements::generateSelect([
        "id" => "increase_limit_id", "name" => "increase_limit_id", 
        "default" => ["", "Please select section"],
        "options" => $data['data']['increase_blocked_array'],
        "selected" => $data['request'] -> input('increase_limit_id')
    ]);
}
else
    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

echo FormElements::generateFormGroup($markup, $data, 'increase_limit_id');

$btnArray = [ 'name' => 'increase_limit', 'value' => 'Update Limit', 'btn_type' => 'update'];     

echo FormElements::generateSubmitButton('update', $btnArray );

echo FormElements::generateFormClose(); 

?>

    </div>
</div>