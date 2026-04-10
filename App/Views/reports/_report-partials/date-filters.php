<?php use Core\FormElements; ?>

<div id="startDateContainer" class="col-md-6">

<?php

// startDate
$markup = FormElements::generateLabel('start_date', 'Start Date');

$markup .= FormElements::generateInput([
    "id" => "start_date", "name" => "startDate", 
    "type" => "text", "value" => $data['request'] -> input('startDate', date('Y-m-01')), 
    "placeholder" => "Start Date"
]);

echo FormElements::generateFormGroup($markup, $data, 'startDate');

?>

</div>

<div id="endDateContainer" class="col-md-6">

<?php

// endDate
$markup = FormElements::generateLabel('end_date', 'End Date');

$markup .= FormElements::generateInput([
    "id" => "end_date", "name" => "endDate", 
    "type" => "text", "value" => $data['request'] -> input('endDate', date('Y-m-t')), 
    "placeholder" => "End Date"
]);

echo FormElements::generateFormGroup($markup, $data, 'endDate');

?>

</div>