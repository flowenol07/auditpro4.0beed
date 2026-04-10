<?php
require 'form.php';

echo '<div class="no-print mb-3">';
require_once 'form.php';
echo '</div>';

if (
    empty($data['data']['question_consolidate']) ||
    empty($data['data']['question_consolidate']['assesment_id'])
) {
    echo $data['noti']::getCustomAlertNoti('noDataFound');
    return;
}

$qc = $data['data']['question_consolidate'];

// Build answer lookup by assesment_id
$answerMap = [];
if (!empty($qc['answer_data'])) {
    foreach ($qc['answer_data'] as $ans) {
        $answerMap[$ans->assesment_id] = $ans;
    }
}

echo '<div id="printContainer">';

$sr = 1;

foreach ($qc['assesment_id'] as $ass) :

    if (!isset($answerMap[$ass->assesment_id])) {
        continue; // skip assessments without data
    }

    $answer = $answerMap[$ass->assesment_id] ?? null;
?>

<table class="table table-bordered v-table exportToExcelTable mb-4" width="100%">

    <!-- CATEGORY -->
    <tr>
        <td width="15%">Category</td>
        <td colspan="7"><?= $answer->category_name ?? '-' ?></td>
    </tr>

    <!-- BRANCH -->
    <tr>
        <td>Branch</td>
        <td colspan="7"><?= $ass->branch_name ?></td>
    </tr>

    <!-- ASSESSMENT PERIOD -->
    <tr>
        <td>Assessment Periods</td>
        <td colspan="3"><?= $ass->assesment_period_from ?></td>
        <td colspan="4"><?= $ass->assesment_period_to ?></td>
    </tr>

    <!-- HEADER -->
    <tr>
        <td>#</td>
        <td>A Question</td>
        <td>Audit Points</td>
        <td>Audit Comment</td>
        <td>Compliance</td>
        <td>Business Risk</td>
        <td>Control Risk</td>
        <td>Status</td>
    </tr>

    <!-- DATA -->
    <tr>
        <td><?= $sr++ ?></td>
        <td><?= $qc['question_search'] ?></td>
        <td><?= $answer->answer_given ?? '-' ?></td>
        <td><?= $answer->audit_comment ?? '-' ?></td>
        <td><?= $answer->audit_commpliance ?? '-' ?></td>
        <td><?= RISK_PARAMETERS_ARRAY[$answer->business_risk]['title'] ?? '-' ?></td>
        <td><?= RISK_PARAMETERS_ARRAY[$answer->control_risk]['title'] ?? '-' ?></td>
        <td><?= ASSESMENT_TIMELINE_ARRAY[$ass->audit_status_id]['title'] ?? 'UNKNOWN STATUS' ?></td>
    </tr>
</table>

<?php endforeach; ?>

</div>
