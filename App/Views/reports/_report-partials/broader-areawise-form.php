<?php 

use Core\FormElements; 

// search type
$markup = FormElements::generateLabel('selectBroaderAreaFilter', 'Search Type');

$formElementArray = [
    "id" => "selectBroaderAreaFilter", "name" => "selectBroaderAreaFilter", 
    "default" => ["", "Please select search type"],
    "selected" => $data['request'] -> input('selectBroaderAreaFilter'),
    "options" => $data['data']['search_type_array'],
    "options_db" => [ "type" => "arr", "val" => "title" ], 
    "optionDataAttributes" => ['showhide']
];        

$markup .= FormElements::generateSelect($formElementArray);
echo FormElements::generateFormGroup($markup, $data, 'selectBroaderAreaFilter');

?>

<div id="selectBranchContainer" class="show_hide_container d-none">
    <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-units.php'; ?>
</div>

<div id="selectHOContainer" class="show_hide_container d-none">
    <?php require_once REPORTS_VIEW . DS . '_report-partials/ho-audit-units.php'; ?>
</div>

<div id="selectAssessmentContainer" class="show_hide_container d-none">
    <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-assesments.php'; ?>
</div>

<div id="dateFilterContainer" class="row show_hide_container d-none">
    <?php require_once REPORTS_VIEW . DS . '_report-partials/date-filters.php'; ?>
</div>

<div id="rmvPendingContainer" class="row">
    <?php require_once REPORTS_VIEW . DS . '_report-partials/remove-pending-assesments.php'; ?>
</div>