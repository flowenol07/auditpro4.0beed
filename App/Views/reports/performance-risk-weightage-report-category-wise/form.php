<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "performance-risk-weightage-report-category-wise", "action" => "" ]);

require_once REPORTS_VIEW . DS . '_report-partials/search-type-filter.php';
 
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

<?php

$btnArray = array('find', 'reset');

if( array_key_exists('data_array', $data['data']) && 
    is_array($data['data']['data_array']))
    array_push($btnArray, 'excel');

    generate_report_buttons($btnArray);

?>