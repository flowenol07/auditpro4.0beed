<?php

use Core\FormElements;

// audit units	
$markup = FormElements::generateLabel('reportHOAuditUnit', 'Head Of Department');

if(is_array($data['data']['ho_audit_unit_data']) && sizeof($data['data']['ho_audit_unit_data']) > 0)
{
    $markup .= FormElements::generateSelect([
        "id" => "reportHOAuditUnit", "name" => "reportHOAuditUnit", 
        "default" => ["", "Please select head of department"],
        "options" => $data['data']['ho_audit_unit_data'], "options_db" => ["type" => "obj", "val" => "combined_name"],
        "dataAttributes" => ['data-url' => $data['siteUrls']::getUrl('reports') . '/report-audit-units-ajx' ],
        "selected" => $data['request'] -> input('reportHOAuditUnit')
    ]);
}
else
    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

echo FormElements::generateFormGroup($markup, $data, 'reportHOAuditUnit');

?>