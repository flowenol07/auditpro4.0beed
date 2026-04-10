<?php

use Core\FormElements;

echo FormElements::generateFormStart([
    "name" => "audit-complete-report",
    "action" => "",
    "method" => "POST"
]);

?>

<?php
echo '<div class="no-print mb-3">';
echo '<div class="border bg-white p-4">' . "\n"; // White box for Audit Unit + Assessment
require_once('form.php'); // your audit unit + audit assessment partials
echo '</div>' . "\n";
echo '</div>';

echo $data['noti']::getSessionAlertNoti();
?>

<!-- Hidden field for assessment ID -->
<?php if (!empty($data['data']['assesmentData']->id)): ?>
    <input type="hidden" name="reportAuditAssesment" value="<?= $data['data']['assesmentData']->id ?>">
<?php endif; ?>

<?php if (!empty($data['request']->input('reportAuditUnit'))): ?>
    <input type="hidden" name="reportAuditUnit" value="<?= $data['request']->input('reportAuditUnit') ?>">
<?php endif; ?>



<?php if (!empty($data['data']['assesmentData'])): ?>
    <div class="border bg-white mt-4 p-4"> <!-- White box for dates section -->
        <div class="row">
            <!-- Start Date -->
            <div class="col-md-6">
                <?php
                    $startValue = !empty($data['data']['audit_start_date'])
                        ? $data['data']['audit_start_date']
                        : $data['request']->input('audit_start_date', '');

                    $markup = FormElements::generateLabel('audit_start_date', 'Audit Start Date');
                    $markup .= FormElements::generateInput([
                        "id" => "audit_start_date",
                        "name" => "audit_start_date",
                        "type" => "text",
                        "value" => $startValue,
                        "placeholder" => "Start Date",
                        "appendClass" => 'date_cls'
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'startDate');
                ?>
            </div>

            <!-- End Date -->
            <div class="col-md-6">
                <?php
                    $endValue = !empty($data['data']['audit_end_date'])
                        ? $data['data']['audit_end_date']
                        : $data['request']->input('audit_end_date', '');

                    $markup = FormElements::generateLabel('audit_end_date', 'Audit End Date');
                    $markup .= FormElements::generateInput([
                        "id" => "audit_end_date",
                        "name" => "audit_end_date",
                        "type" => "text",
                        "value" => $endValue,
                        "placeholder" => "End Date",
                        "appendClass" => 'date_cls'
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'endDate');
                ?>
            </div>
        </div>

        <div class="row">
            <!-- Compliance Start Date -->
            <div class="col-md-6">
                <?php
                    $complianceStartValue = !empty($data['data']['compliance_start_date'])
                        ? $data['data']['compliance_start_date']
                        : $data['request']->input('compliance_start_date', '');

                    $markup = FormElements::generateLabel('compliance_start_date', 'Compliance Start Date');
                    $markup .= FormElements::generateInput([
                        "id" => "compliance_start_date",
                        "name" => "compliance_start_date",
                        "type" => "text",
                        "value" => $complianceStartValue,
                        "placeholder" => "Compliance Start Date",
                        "appendClass" => 'date_cls'
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'complianceStartDate');
                ?>
            </div>

            <!-- Compliance End Date -->
            <div class="col-md-6">
                <?php
                    $complianceEndValue = !empty($data['data']['compliance_end_date'])
                        ? $data['data']['compliance_end_date']
                        : $data['request']->input('compliance_end_date', '');

                    $markup = FormElements::generateLabel('compliance_end_date', 'Compliance End Date');
                    $markup .= FormElements::generateInput([
                        "id" => "compliance_end_date",
                        "name" => "compliance_end_date",
                        "type" => "text",
                        "value" => $complianceEndValue,
                        "placeholder" => "Compliance End Date",
                        "appendClass" => 'date_cls'
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'complianceEndDate');
                ?>
            </div>
        </div>

        <div class="row">
            <!-- Report Submitted Date -->
            <div class="col-md-6">
                <?php
                    $submittedValue = !empty($data['data']['report_submitted_date'])
                        ? $data['data']['report_submitted_date']
                        : $data['request']->input('report_submitted_date', '');

                    $markup = FormElements::generateLabel('report_submitted_date', 'Report Submitted Date');
                    $markup .= FormElements::generateInput([
                        "id" => "report_submitted_date",
                        "name" => "report_submitted_date",
                        "type" => "text",
                        "value" => $submittedValue,
                        "placeholder" => "Report Submitted Date",
                        "appendClass" => 'date_cls'
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'reportSubmittedDate');
                ?>
            </div>

            <div class="col-md-12 mt-3">
                <button type="submit" name="update_dates" value="1" class="btn btn-success">
                    Update Dates
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php echo FormElements::generateFormClose(); ?>