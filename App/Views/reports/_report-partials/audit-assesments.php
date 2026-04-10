<?php
use Core\FormElements;

// audit units	
$markup = FormElements::generateLabel('reportAuditAssesment', 'Audit Assesments');

$formElementArray = [
    "id" => "reportAuditAssesment", "name" => "reportAuditAssesment", 
    "default" => ["", "Please select audit assesment"],
    "selected" => $data['request'] -> input('reportAuditAssesment')
];

if(array_key_exists('audit_assesment_data', $data['data']) && sizeof($data['data']['audit_assesment_data']))
{
    $formElementArray['options'] = $data['data']['audit_assesment_data'];
    $formElementArray['options_db'] = [ "type" => "obj", "val" => "combined_period" ];
}

$markup .= FormElements::generateSelect($formElementArray);
echo FormElements::generateFormGroup($markup, $data, 'reportAuditAssesment');

?>