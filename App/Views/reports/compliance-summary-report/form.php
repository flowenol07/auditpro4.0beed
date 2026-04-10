<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "audit-complete-report", "action" => "" ]);

?>

<div class="row">
    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-units.php'; ?>
    </div>

    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-assesments.php'; ?>
    </div>

    <div class="col-lg-6 mb-3">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/risk-category-checkbox.php'; ?>
    </div>

    <div class="col-lg-6 mb-3">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/business-risk-checkbox.php'; ?>
        <div class="w-100" style="margin-bottom:12px"></div>
        <?php require_once REPORTS_VIEW . DS . '_report-partials/control-risk-checkbox.php'; ?>
    </div>

    <?php echo FormElements::generateInput([
        "id" => "complianceNeeded", "name" => "complianceNeeded", 
        "type" => "hidden", "value" => 1 ]); ?>
</div>

<?php
    $btnArray = array('find');

        if( array_key_exists('data_array', $data['data']) && is_array($data['data']['data_array']) && 
        array_key_exists('ans_data', $data['data']['data_array']) && is_array($data['data']['data_array']['ans_data']) && 
        sizeof($data['data']['data_array']['ans_data']) > 0 )
            array_push($btnArray, 'print','reset');

    generate_report_buttons($btnArray);
?>