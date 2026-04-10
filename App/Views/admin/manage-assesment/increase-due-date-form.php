<?php use Core\FormElements; ?>

</div>

<div class="card rounded-0 mt-3">
  <div class="card-header pb-1 font-medium">
    Period Expired
  </div>
  <div class="card-body">

<?php

echo FormElements::generateFormStart(["name" => "remove-block"]);

if(in_array( $data['data']['db_data'] -> audit_status_id, [ 
    ASSESMENT_TIMELINE_ARRAY[1]['status_id'], 
    ASSESMENT_TIMELINE_ARRAY[2]['status_id'],
    ASSESMENT_TIMELINE_ARRAY[3]['status_id'] ])):
    echo '<h4 class="lead font-medim text-danger">Audit period expired. (Expired at '. $data['data']['db_data'] -> audit_due_date .')</h4>' . "\n";
else:
    echo '<h4 class="lead font-medim text-danger">Compliance period expired. (Expired at '. $data['data']['db_data'] -> compliance_due_date .')</h4>' . "\n";
endif;

// increase_due_date_days
$markup = FormElements::generateLabel('increase_due_date_days', 'Increase Due Date');

$markup .= FormElements::generateInput([
    "id" => "increase_due_date_days", "name" => "increase_due_date_days", "appendClass" => "date_cls",
    "type" => "text", "value" => $data['request'] -> input('increase_due_date_days', date('Y-m-t')), 
    "placeholder" => "Select Date"
]);

echo FormElements::generateFormGroup($markup, $data, 'increase_due_date_days');

$btnArray = [ 'name' => 'increase_due_date', 'value' => 'Update Due Date', 'btn_type' => 'update'];     

echo FormElements::generateSubmitButton('update', $btnArray );

echo FormElements::generateFormClose(); 

?>

    </div>
</div>