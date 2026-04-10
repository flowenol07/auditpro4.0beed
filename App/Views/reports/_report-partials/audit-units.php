<?php
use Core\FormElements;
// audit units	
$markup = FormElements::generateLabel('reportAuditUnit', 'Audit Unit');

if(is_array($data['data']['audit_unit_data']) && sizeof($data['data']['audit_unit_data']) > 0)
{
    $markup .= FormElements::generateSelect([
        "id" => "reportAuditUnit", "name" => "reportAuditUnit", 
        "default" => ["", "Please select audit units"],
        "options" => $data['data']['audit_unit_data'], "options_db" => ["type" => "obj", "val" => "combined_name"],
        "dataAttributes" => ['data-url' => $data['siteUrls']::getUrl('reports') . '/report-audit-units-ajx' ],
        "selected" => $data['request'] -> input('reportAuditUnit')
    ]);
}
else
    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

echo FormElements::generateFormGroup($markup, $data, 'reportAuditUnit');

?>