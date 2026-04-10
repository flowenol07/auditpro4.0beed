<style>
    * {
        box-sizing: border-box;
    }

    body {
        background-color: #f4f6fb;
        font-family: "Inter", "Segoe UI", Roboto, Arial, sans-serif;
        color: #1f2937;
    }

    .dash-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
        margin-bottom: 20px;
    }

    .dash-card {
        background: linear-gradient(180deg, #ffffff, #f9fafb);
        border-radius: 18px;
        padding: 22px;
        text-align: center;
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.06);
    }

    .dash-details h4 {
        font-size: 34px;
        font-weight: 700;
        margin: 0;
        color: #111827;
    }

    .dash-header {
        margin-top: 6px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.4px;
        color: #6b7280;
        text-transform: uppercase;
    }

    .card-risk {
        box-shadow: inset 0 4px 0 #ef4444;
    }

    .card-not-started {
        box-shadow: inset 0 4px 0 #f59e0b;
    }

    .card-expired {
        box-shadow: inset 0 4px 0 #fb923c;
    }

    .card-pending {
        box-shadow: inset 0 4px 0 #2563eb;
    }

    .dashboard-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }

    .dashboard-card .card-header {
        padding: 16px 22px;
        border-bottom: 1px solid #eef2f7;
        background: #ffffff;
    }

    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title::before {
        content: "";
        width: 6px;
        height: 20px;
        background: #2563eb;
        border-radius: 4px;
    }

    .dashboard-card .card-body {
        padding: 22px;
    }

    .filter-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.05);
    }

    .filter-row {
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-row label {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }

    .filter-row select {
        min-width: 260px;
        height: 42px;
        border-radius: 10px;
        border: 1px solid #d1d5db;
        padding: 6px 12px;
        font-size: 14px;
    }

    .filter-row select:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    #riskChart,
    #riskWiseScoreChart {
        height: 300px;
        width: 100%;
    }

    #assesmentWiseScoreChart {
        height: 380px;
        width: 100%;
    }

    .kp-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .kp-table thead {
        background: #f1f5f9;
    }

    .kp-table thead th {
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        border-bottom: 1px solid #e5e7eb;
    }

    .kp-table tbody td {
        padding: 14px 16px;
        font-size: 14px;
        color: #475569;
        border-bottom: 1px solid #f1f5f9;
    }

    .kp-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Rounded table edges */
    .kp-table thead th:first-child {
        border-top-left-radius: 10px;
    }

    .kp-table thead th:last-child {
        border-top-right-radius: 10px;
    }

    /* ===============================
   TREND BADGES
================================ */
    .trend-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }

    .trend-up {
        color: #f7f7f7;
        background: #f14848;
    }

    .trend-down {
        color: #f7f7f7;
        background: #15803d;
    }

    .trend-neutral {
        color: #475569;
        background: #e5e7eb;
    }

    /* ===============================
   RESPONSIVE
================================ */
    @media (max-width: 992px) {

        .col-5,
        .col-7 {
            flex: 0 0 100%;
            max-width: 100%;
        }

        .filter-row {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-row select {
            width: 100%;
        }
    }
</style>
<?php
use Core\FormElements;
use Core\SiteUrls;

if (in_array($data['userDetails']['emp_type'], [1, 9])): ?>
    <!-- For Master Count -->
    <div class="row d-flex">
        <?php
        $metrics = [
            ['title' => 'Total Employee', 'img' => 'employees.jpg', 'count' => $data['data']['db_data']['total_employees']],
            ['title' => 'Total Branchs', 'img' => 'branchs.jpg', 'count' => $data['data']['db_data']['total_branch']],
            ['title' => 'Total Head Office', 'img' => 'head-office.jpg', 'count' => $data['data']['db_data']['total_head_office']],
            ['title' => 'Total Schemes', 'img' => 'schemes.jpg', 'count' => $data['data']['db_data']['total_schemes']],
        ];

        foreach ($metrics as $metric): ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="h-100 p-3 border bg-light shadow-sm text-center">
                    <img class="img-fluid rounded-circle border" src="<?= IMAGES . $metric['img'] ?>"
                        alt="<?= $metric['title'] ?>" />
                    <h4 class="font-bold font-md-2 mt-2 mb-0"><?= $metric['count'] ?></h4>
                    <p class="text-uppercase font-sm font-medium text-secondary mb-0"><?= $metric['title'] ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- For Assessment Count -->
    <?php
    $totalAuditCount = $data['data']['db_data']['total_audit_count'];
    $assessmentData = [
        ['title' => 'Total Audit Pending', 'count' => $data['data']['db_data']['total_pending_audit']],
        ['title' => 'Total Audit Completed', 'count' => $data['data']['db_data']['total_completed_audit']],
        ['title' => 'Total Compliance Completed', 'count' => $data['data']['db_data']['total_completed_compliance']],
        ['title' => 'Total Blocked Audit', 'count' => $data['data']['db_data']['total_blocked_assesment']],
        ['title' => 'Total Expired Audit', 'count' => $data['data']['db_data']['total_expired_audit']],
        ['title' => 'Total Expired Compliance', 'count' => $data['data']['db_data']['total_expired_compliance']],
        ['title' => 'Total Not Started Branches', 'count' => count($data['data']['db_data']['total_not_yet_startd_audit_branch'])],
        ['title' => 'Total Not Started Head Office', 'count' => count($data['data']['db_data']['total_not_yet_startd_audit_ho'])],
    ];

    foreach ($assessmentData as &$assessment) {
        $assessment['progress'] = ($assessment['count'] / $totalAuditCount) * 100;
    }

    unset($assessment);
    ?>

    <div class="col-md-6 col-lg-12 mb-4 grid-margin p-2 stretch-card border bg-white shadow-md rounded">
        <div id="chartContainer" style="height: 370px; max-width: 920px; margin: 0px auto;"></div>
        <div id="json-data_dough-nut-chart" style="display: none;">
            <?= json_encode($data['data']['db_data']['data_array_chart']) ?>
        </div>
    </div>

    <div class="card apcard mb-4 audit_container_div">

        <div class="card-header">Assessment Details</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered v-table mb-0">
                    <thead>
                        <tr class="bg-light-gray border-top">
                            <th width="80" class="text-center">Sr. No.</th>
                            <th width="300">Stage</th>
                            <th width="200">Count</th>
                            <th>Progress</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($assessmentData as $cIndex => $assessment): ?>
                            <tr>
                                <td class="text-center"><?= ($cIndex + 1) ?></td>
                                <td><?= $assessment['title'] ?></td>
                                <td>
                                    <h6 class="font-bold"><?= $assessment['count'] ?> Audits</h6>
                                </td>
                                <td>
                                    <span>
                                        <progress-meter>
                                            <progress-percent style="--progress: <?= $assessment['progress'] ?>"
                                                class="progress-bar progress-bar-striped progress-bar-animated"></progress-percent>
                                        </progress-meter>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <?php
elseif (in_array($data['userDetails']['emp_type'], [2, 3, 4, 16])):

    // // Risk Category Weight Sorted Data
    $riskData = [];
    $riskTypeWiseScore = [];
    $totalWeightedRiskScore = 0;
    $assesmentWiseScore = [];
    $totalHighRiskJson = [];
    $totalMediumRiskJson = [];
    $totalLowRiskJson = [];
    $currentYear = 0;

    foreach ($data['data']['riskData'] as $cAssesId => $cAssesDetails) {
        $currentYear = $cAssesDetails->year;

        $riskData[] = [
            'assesment_id' => $cAssesDetails->assesment_id,
            'assesment_period_from' => $cAssesDetails->assesment_period_from,
            'assesment_period_to' => $cAssesDetails->assesment_period_to,
            'weighted_score' => $cAssesDetails->weighted_score,
            'risk_data' => (array) json_decode($cAssesDetails->risk_data),
        ];

        $totalWeightedRiskScore += $cAssesDetails->weighted_score;
    }


    foreach ($riskData as $cRiskId => $cRiskDetails) {
        $totalHighRisk = 0;
        $totalMediumRisk = 0;
        $totalLowRisk = 0;
        $totalQuesCount = 0;
        $perHighRisk = 0;
        $perMediumRisk = 0;
        $perLowRisk = 0;
        $assementForLabel = '';

        // Calculating Total Questions Count of All individuals Risks
        foreach ($cRiskDetails['risk_data'] as $cDataId => $cDataDetails) {
            //risk Type Wise Score
            if (!empty($riskTypeWiseScore) && array_key_exists($cDataId, $riskTypeWiseScore))
                $riskTypeWiseScore[$cDataId] += $cDataDetails->wg_sc;
            else
                $riskTypeWiseScore[$cDataId] = $cDataDetails->wg_sc;


            if ($cDataDetails->avg_sc > 0) {
                $totalHighRisk += $cDataDetails->{1};
                $totalMediumRisk += $cDataDetails->{2};
                $totalLowRisk += $cDataDetails->{3};
                $totalQuesCount += $cDataDetails->{1} + $cDataDetails->{2} + $cDataDetails->{3};
            }
        }

        foreach ($cRiskDetails['risk_data'] as $cDataId => $cDataDetails) {
            if ($cDataDetails->avg_sc > 0) {
                $perHighRisk += (($cDataDetails->{1} / $totalQuesCount * 100) * ($cRiskDetails['weighted_score'] / 100));

                $perMediumRisk += (($cDataDetails->{2} / $totalQuesCount * 100) * ($cRiskDetails['weighted_score'] / 100));

                $perLowRisk += (($cDataDetails->{3} / $totalQuesCount * 100) * ($cRiskDetails['weighted_score'] / 100));
            }
        }

        $dateFrom = date_create($cRiskDetails['assesment_period_from']);
        $dateTo = date_create($cRiskDetails['assesment_period_to']);
        $months = (date_diff($dateFrom, $dateTo)->format("%m") + 1);

        $monthFrom = $dateFrom->format("m");
        $monthTo = $dateTo->format("m");

        //Assement Period format for label
        if ($months > 1)
            $assementForLabel = '(' . $dateFrom->format("Y") . ') ' . $monthFrom . ' - ' . $monthTo;
        else
            $assementForLabel = $dateFrom->format("Y") . '-' . $monthFrom;

        //Assigning to an array
        $assesmentWiseScore[$cRiskDetails['assesment_id']] = [
            'assesment_id' => $cRiskDetails['assesment_id'],
            'frequency' => $months,
            'assesment_period' => $cRiskDetails['assesment_period_from'] . ' to ' . $cRiskDetails['assesment_period_to'],
            'weighted_score' => $cRiskDetails['weighted_score'],
            'totalQuestions' => $totalQuesCount,
            'totalHighRisk' => $totalHighRisk,
            'totalMediumRisk' => $totalMediumRisk,
            'totalLowRisk' => $totalLowRisk,
            'perHighRisk' => $perHighRisk,
            'perMediumRisk' => $perMediumRisk,
            'perLowRisk' => $perLowRisk,
        ];


        $totalHighRiskJson[] = ['label' => $assementForLabel, 'y' => (float) get_decimal($perHighRisk, 2)];

        $totalMediumRiskJson[] = ['label' => $assementForLabel, 'y' => (float) get_decimal($perMediumRisk, 2)];

        $totalLowRiskJson[] = ['label' => $assementForLabel, 'y' => (float) get_decimal($perLowRisk, 2)];
    }

    // Expired Count
    $complianceExpCount = 0;
    $auditExpCount = 0;
    $auditPending = 0;
    $auditAssesments = [];

    foreach ($data['data']['db_data'] as $cAuditId => $cAuditData) {
        if ($data['data']['auditId'] == $cAuditId) {
            if (isset($cAuditData->year_data)) {
                foreach ($cAuditData->year_data as $cYearId => $cAllYearData) {
                    if (isset($cAuditData->year_data) && !empty($cAuditData->year_data)) {
                        foreach ($cAllYearData->assesment_data as $cAssesId => $cAssesData) {
                            if (isset($data['data']['fin_year'][$currentYear]) && $cYearId == $data['data']['fin_year'][$currentYear])
                                $auditAssesments[$cAssesId] = $cAssesData->assesment_period_from . ' to ' . $cAssesData->assesment_period_to;

                            if (
                                in_array($cAssesData->audit_status_id, [
                                    ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                                    ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                                ]) &&
                                strtotime($cAssesData->compliance_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))
                            )
                                $complianceExpCount++;
                            elseif (
                                in_array($cAssesData->audit_status_id, [
                                    ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                                    ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                                ]) &&
                                strtotime($cAssesData->audit_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))
                            )
                                $auditExpCount++;
                            elseif ($cAssesData->audit_status_id == 1 && $data['data']['empType'] == 2)
                                $auditPending++;
                            elseif ($cAssesData->audit_status_id == 4 && $data['data']['empType'] == 3)
                                $auditPending++;
                            elseif (($cAssesData->audit_status_id == 2 || $cAssesData->audit_status_id == 5) && $data['data']['empType'] == 4 && $data['data']['empType'] == 16)
                                $auditPending++;

                        }
                    }
                }
            }
        }
    }
    ?>
    <div class="row">
        <div class="col-3">
            <article class="dash-information dash-card card-risk shadow-sm">
                <h4><?= $totalWeightedRiskScore ?></h4>
                <h5 class="dash-header">Total Weighted Risk</h5>
            </article>
        </div>
        <div class="col-3">
            <article class="dash-information dash-card card-not-started shadow-sm">
                <h4><?= (is_array($data['data']['auditNotStartedData']) ? sizeof($data['data']['auditNotStartedData']) : 0) ?>
                </h4>
                <h5 class="dash-header">Assessment Not Started</h5>
            </article>
        </div>
        <div class="col-3">
            <article class="dash-information dash-card card-expired shadow-sm">
                <h4>
                    <?php
                    if ($data['userDetails']['emp_type'] == 2)
                        echo $auditExpCount;
                    elseif ($data['userDetails']['emp_type'] == 4)
                        echo $complianceExpCount + $auditExpCount;
                    elseif ($data['userDetails']['emp_type'] == 16)
                        echo $complianceExpCount + $auditExpCount;
                    elseif ($data['userDetails']['emp_type'] == 3)
                        echo $complianceExpCount;
                    ?>
                </h4>
                <h5 class="dash-header">Assessment Expired</h5>
            </article>
        </div>
        <div class="col-3">
            <article class="dash-information dash-card card-pending shadow-sm">
                <h4><?= $auditPending ?></h4>
                <h5 class="dash-header">Assessment Pending</h5>
            </article>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-4 mt-3">
            <div class="dashboard-card filter-card">
                <div class="card-header">
                    <span>Filter Assessment Data</span>
                </div>

                <div class="card-body">
                    <?php
                    echo FormElements::generateFormStart([
                        "name" => "chart",
                        "action" => $data['me']->url,
                        "class" => "filter-form"
                    ]);

                    $markup = '<div class="filter-row">';
                    $markup .= FormElements::generateLabel(
                        'asses_period',
                        'Select Assessment Period'
                    );

                    if (is_array($auditAssesments) && sizeof($auditAssesments) > 0) {
                        $markup .= FormElements::generateSelect([
                            "id" => "asses_period",
                            "name" => "asses_period",
                            "default" => ["all", "All"],
                            "options" => $auditAssesments,
                            "selected" => $data['request']->input('asses_period'),
                            "dataAttributes" => [
                                "data-asses-id-url" =>
                                    $data['siteUrls']::getUrl('dashboard') . '/chartDataAjx',
                                "data-auditId" => $data['data']['auditId']
                            ],
                            "class" => "form-select dashboard-select"
                        ]);
                    } else {
                        $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                    }

                    $markup .= '</div>';

                    echo FormElements::generateFormGroup($markup, $data, 'asses_period');
                    echo FormElements::generateFormClose();
                    ?>
                </div>
            </div>
        </div>

        <div class="col-5 mb-4 mt-3">
            <div class="dashboard-card">
                <div class="card-header">
                    <span>Risk Distribution</span>
                </div>
                <div class="card-body">
                    <div id="riskChart"></div>
                </div>
            </div>
        </div>

        <div class="col-7 mb-4 mt-3">
            <div class="dashboard-card">
                <div class="card-header">
                    <span>Risk Wise Score</span>
                </div>
                <div class="card-body">
                    <div id="riskWiseScoreChart"></div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4 mt-3">
            <div class="dashboard-card">
                <div class="card-header">
                    <span>Assessment Wise Risk Trend</span>
                </div>

                <div class="card-body">
                    <div id="assesmentWiseScoreChart"></div>

                    <div id="json-data-assesmentWiseScoreChart-high" style="display:none;">
                        <?php echo json_encode($totalHighRiskJson); ?>
                    </div>

                    <div id="json-data-assesmentWiseScoreChart-medium" style="display:none;">
                        <?php echo json_encode($totalMediumRiskJson); ?>
                    </div>

                    <div id="json-data-assesmentWiseScoreChart-low" style="display:none;">
                        <?php echo json_encode($totalLowRiskJson); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4 mt-3">
            <div class="dashboard-card">
                <div class="card-header">
                    <span>Assessment Wise Risk Trend</span>
                </div>
                <div class="card-body">
                    <div id="assesmentWiseScoreChartBar" style="height: 360px; width: 100%;"></div>

                    <div id="totalHighRiskJson" style="display:none;">
                        <?php echo json_encode($totalHighRiskJson); ?>
                    </div>

                    <div id="totalMediumRiskJson" style="display:none;">
                        <?php echo json_encode($totalMediumRiskJson); ?>
                    </div>

                    <div id="totalLowRiskJson" style="display:none;">
                        <?php echo json_encode($totalLowRiskJson); ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            window.onload = function () {

                const highData   = JSON.parse(document.getElementById("totalHighRiskJson").innerHTML);
                const mediumData = JSON.parse(document.getElementById("totalMediumRiskJson").innerHTML);
                const lowData    = JSON.parse(document.getElementById("totalLowRiskJson").innerHTML);

                const chart = new CanvasJS.Chart("assesmentWiseScoreChartBar", {
                    animationEnabled: true,
                    theme: "light2",
                    title: {
                        text: "Assessment Wise Risk Trend"
                    },
                    axisY: {
                        title: "Risk Score"
                    },
                    toolTip: {
                        shared: true
                    },
                    legend: {
                        cursor: "pointer",
                        horizontalAlign: "center",
                        verticalAlign: "bottom"
                    },
                    data: [
                        {
                            type: "column",
                            name: "High Risk",
                            showInLegend: true,
                            color: "#e53935",
                            dataPoints: highData
                        },
                        {
                            type: "column",
                            name: "Medium Risk",
                            showInLegend: true,
                            color: "#fb8c00",
                            dataPoints: mediumData
                        },
                        {
                            type: "column",
                            name: "Low Risk",
                            showInLegend: true,
                            color: "#43a047",
                            dataPoints: lowData
                        }
                    ]
                });

                chart.render();
            };
        </script>

        <div class="col-12 mb-4 mt-3">
            <div class="dashboard-card">
                <div class="card-header">
                    <span>Assessment Wise Score Summary</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table kp-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Assessment Period</th>
                                    <th>Frequency</th>
                                    <th>Weighted Score</th>
                                    <th>High</th>
                                    <th>Medium</th>
                                    <th>Low</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $srNo = 0;
                                $prevScore = null;
                                // $cAssesData['assesment_period']
                                foreach ($assesmentWiseScore as $cAssesId => $cAssesData):
                            ?>
                                <tr>
                                    <td><?php echo $srNo = $srNo + 1; ?></td>
                                    <td><?php echo $cAssesData['assesment_period']; ?></td>
                                    <td><?php echo $cAssesData['frequency']; ?></td>
                                    <td><?php echo $cAssesData['weighted_score']; ?></td>
                                    <td><?php echo number_format($cAssesData['perHighRisk'], 2);; ?></td>
                                    <td><?php echo number_format($cAssesData['perMediumRisk'], 2); ?></td>
                                    <td><?php echo number_format($cAssesData['perLowRisk'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mb-4 mt-3">
            <div class="dashboard-card">
                <div class="card-header">
                    <span>Assessment Wise Score Summary</span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table kp-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Assessment Period</th>
                                    <th>Frequency</th>
                                    <th>Weighted Score</th>
                                    <th>Change</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $srNo = 1;
                                $prevScore = null;

                                foreach ($assesmentWiseScore as $cAssesId => $cAssesData) {

                                    $currentScore = (float)$cAssesData['weighted_score'];

                                    // Trend logic
                                    if ($prevScore === null) {
                                        $trend = '-';
                                        $change = '-';
                                    } else {
                                        $diff = $currentScore - $prevScore;
                                        $change = number_format($diff, 2);

                                        if ($diff > 0)
                                            $trend = 'Increasing';
                                        elseif ($diff < 0)
                                            $trend = 'Decreasing';
                                        else
                                            $trend = '-';
                                    }

                                    echo '<tr>
                                        <td>' . $srNo . '</td>
                                        <td>' . $cAssesData['assesment_period'] . '</td>
                                        <td>' . $cAssesData['frequency'] . '</td>
                                        <td><strong>' . number_format($currentScore, 2) . '</strong></td>
                                        <td>';

                                    // Change column with color
                                    if ($change === '-') {
                                        echo '<span class="trend-badge trend-neutral">—</span>';
                                    } elseif ($diff > 0) {
                                        echo '<span class="trend-badge trend-up">+' . $change . '</span>';
                                    } else {
                                        echo '<span class="trend-badge trend-down">' . $change . '</span>';
                                    }

                                    echo '</td><td>';

                                    // Trend badge
                                    if ($trend === 'Increasing')
                                        echo '<span class="trend-badge trend-up">▲ Increasing</span>';
                                    elseif ($trend === 'Decreasing')
                                        echo '<span class="trend-badge trend-down">▼ Decreasing</span>';
                                    else
                                        echo '<span class="trend-badge trend-neutral">—</span>';

                                    echo '</td></tr>';

                                    $prevScore = $currentScore;
                                    $srNo++;
                                }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (is_array($data['data']['db_data']) && sizeof($data['data']['db_data']) > 0): ?>
        <?php foreach ($data['data']['db_data'] as $cAuditId => $cAuditDetails): ?>

            <?php if ($data['data']['auditId'] == $cAuditId): ?>

                <div id="<?= 'audit_' . $cAuditId . '_container_div'; ?>" class="card apcard mb-4 audit_container_div">

                    <div class="card-header">
                        Audit Unit:
                        <?= string_operations(($cAuditDetails->name . ' (' . $cAuditDetails->audit_unit_code . ')'), 'upper'); ?>
                    </div>

                    <div class="card-body">
                        <?php

                        if (is_array($data['data']['db_year_data']) && sizeof($data['data']['db_year_data']) > 0):

                            foreach ($data['data']['db_year_data'] as $cIndex => $cYearDetails):

                                $assesmentBtn = true;
                                $yearLastAuditDate = ($cYearDetails->year + 1) . '-03-31';

                                if (
                                    isset($cAuditDetails->year_data) &&
                                    array_key_exists($cYearDetails->id, $cAuditDetails->year_data) &&
                                    $cAuditDetails->year_data[$cYearDetails->id]->pending_assesment
                                )
                                    $assesmentBtn = false;
                                ?>

                                <div class="table-responsive-md">
                                    <?php if (array_key_exists($cYearDetails->id, $data['data']['lastYearId'])): ?>
                                        <table class="table table-bordered kp-table v-table border-top-0">

                                            <?php

                                            $tableMrk = '';

                                            $tableHeadMrk = '<tr class="top-border">
                                        <th width="80">Sr. No.</th>
                                        <th>Assesment Details</th>
                                        <th>Audit Status</th>
                                        <th>Compliance Status</th>
                                        <th>RO OFFICER Status</th>
                                        <th>Action</th>
                                    </tr>' . "\n";

                                            if (
                                                isset($cAuditDetails->year_data) &&
                                                array_key_exists($cYearDetails->id, $cAuditDetails->year_data) &&
                                                is_array($cAuditDetails->year_data[$cYearDetails->id]->assesment_data) &&
                                                sizeof($cAuditDetails->year_data[$cYearDetails->id]->assesment_data) > 0
                                            ):

                                                $srNo = 1;

                                                foreach ($cAuditDetails->year_data[$cYearDetails->id]->assesment_data as $cAssesmentId => $cAssesmentDetails):

                                                    // check last assesment
                                                    if ($yearLastAuditDate == $cAssesmentDetails->assesment_period_to)
                                                        $assesmentBtn = false;

                                                    $tableMrk .= '<tr>' . "\n";
                                                    $tableMrk .= '<td>' . $srNo++ . '</td>' . "\n";

                                                    $tableMrk .= '<td>' . "\n";
                                                    $tableMrk .= $cAssesmentDetails->assesment_period_from . ' - ' . $cAssesmentDetails->assesment_period_to . ' <span class="d-inline-block text-secondary font-sm">( Frequency - ' . $cAssesmentDetails->frequency . ' Months )</span>';

                                                    if (
                                                        in_array(
                                                            $cAssesmentDetails->audit_status_id,
                                                            [
                                                                ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                                                                ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                                                            ]
                                                        )
                                                    ):
                                                        $tableMrk .= '<p class="font-sm mb-0 text-danger font-medium">Audit Due Date: ' . $cAssesmentDetails->audit_due_date . '</p>' . "\n";

                                                    elseif (
                                                        in_array($cAssesmentDetails->audit_status_id, [
                                                            ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                                                            ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                                                        ])
                                                    ):
                                                        $tableMrk .= '<p class="font-sm mb-0 text-danger font-medium">Compliance Due Date: ' . $cAssesmentDetails->compliance_due_date . '</p>' . "\n";
                                                    endif;
                                                    $tableMrk .= '</td>' . "\n";

                                                    $tableMrk .= '<td>' . "\n";

                                                    $tempStatus = string_operations('Completed', 'upper');

                                                    if (
                                                        array_key_exists($cAssesmentDetails->audit_status_id, ASSESMENT_TIMELINE_ARRAY) &&
                                                        in_array($cAssesmentDetails->audit_status_id, [
                                                            ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                                                            ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                                                        ])
                                                    )
                                                        $tempStatus = ASSESMENT_TIMELINE_ARRAY[$cAssesmentDetails->audit_status_id]['title'];

                                                    $tableMrk .= $tempStatus;

                                                    $tempStatus = string_operations('Completed', 'upper');

                                                    if (in_array($cAssesmentDetails->audit_status_id, [ASSESMENT_TIMELINE_ARRAY[2]['status_id']]))
                                                        $tempStatus = ASSESMENT_TIMELINE_ARRAY[ASSESMENT_TIMELINE_ARRAY[2]['status_id']]['title'];

                                                    if ($cAssesmentDetails->audit_status_id > ASSESMENT_TIMELINE_ARRAY[1]['status_id']):
                                                        $tableMrk .= '<p class="mb-0 text-danger font-sm font-medium">Reviewer Status: ' . $tempStatus . '</p>' . "\n";
                                                    endif;

                                                    $tableMrk .= '</td>' . "\n";

                                                    $tableMrk .= '<td>' . "\n";

                                                    $tempStatus = string_operations('Completed', 'upper');

                                                    if (
                                                        array_key_exists($cAssesmentDetails->audit_status_id, ASSESMENT_TIMELINE_ARRAY) &&
                                                        in_array($cAssesmentDetails->audit_status_id, [
                                                            ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                                                            ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                                                        ])
                                                    )
                                                        $tempStatus = ASSESMENT_TIMELINE_ARRAY[$cAssesmentDetails->audit_status_id]['title'];

                                                    if (!($cAssesmentDetails->audit_status_id > ASSESMENT_TIMELINE_ARRAY[3]['status_id']))
                                                        $tempStatus = '-';

                                                    $tableMrk .= $tempStatus;

                                                    $tempStatus = string_operations('Completed', 'upper');

                                                    if (in_array($cAssesmentDetails->audit_status_id, [ASSESMENT_TIMELINE_ARRAY[5]['status_id']]))
                                                        $tempStatus = ASSESMENT_TIMELINE_ARRAY[ASSESMENT_TIMELINE_ARRAY[5]['status_id']]['title'];

                                                    if ($cAssesmentDetails->audit_status_id > 4):
                                                        $tableMrk .= '<p class="mb-0 text-danger font-sm font-medium">Reviewer Status: ' . $tempStatus . '</p>' . "\n";
                                                    endif;

                                                    $tableMrk .= '</td>' . "\n";

                                                    $tableMrk .= '<td>' . "\n";

                                                    $tempStatus = string_operations('Completed', 'upper');

                                                    if (
                                                        array_key_exists($cAssesmentDetails->audit_status_id, ASSESMENT_TIMELINE_ARRAY) &&
                                                        in_array($cAssesmentDetails->audit_status_id, [
                                                            ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                                                            ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                                                        ])
                                                    )
                                                        $tempStatus = ASSESMENT_TIMELINE_ARRAY[$cAssesmentDetails->audit_status_id]['title'];

                                                    if (!($cAssesmentDetails->audit_status_id > ASSESMENT_TIMELINE_ARRAY[3]['status_id']))
                                                        $tempStatus = '-';

                                                    $tableMrk .= $tempStatus;

                                                    $tempStatus = string_operations('Completed', 'upper');

                                                    if (in_array($cAssesmentDetails->audit_status_id, [ASSESMENT_TIMELINE_ARRAY[15]['status_id']]))
                                                        $tempStatus = ASSESMENT_TIMELINE_ARRAY[ASSESMENT_TIMELINE_ARRAY[15]['status_id']]['title'];

                                                    if ($cAssesmentDetails->audit_status_id > 4):
                                                        $tableMrk .= '<p class="mb-0 text-danger font-sm font-medium">RO OFFICER Status: ' . $tempStatus . '</p>' . "\n";
                                                    endif;

                                                    $tableMrk .= '</td>' . "\n";

                                                    $tableMrk .= '<td>' . "\n";


                                                    $cShowBtn = false;
                                                    $cShowBtnText = '';

                                                    if (!($cAssesmentDetails->is_limit_blocked)) {
                                                        // for audit
                                                        if (
                                                            $data['userDetails']['emp_type'] == 2 &&
                                                            in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                                                                ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                                                            ]) && (strtotime($cAssesmentDetails->audit_due_date) >= strtotime(date($GLOBALS['dateSupportArray'][1])))
                                                        ) {
                                                            $cShowBtn = true;
                                                            $cShowBtnText = ($cAssesmentDetails->audit_status_id == ASSESMENT_TIMELINE_ARRAY[3]['status_id']) ? 'DO RE-ASSESMENT' : 'DO ASSESMENT';
                                                        }

                                                        // for review audit
                                                        else if (
                                                            $data['userDetails']['emp_type'] == 4 &&
                                                            in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[2]['status_id']
                                                            ])
                                                        ) {
                                                            $cShowBtn = true;
                                                            $cShowBtnText = 'REVIEW AUDIT';
                                                        }
                                                        // else if (
                                                        //     $data['userDetails']['emp_type'] == 16 &&
                                                        //     in_array($cAssesmentDetails->audit_status_id, [
                                                        //         ASSESMENT_TIMELINE_ARRAY[15]['status_id']
                                                        //     ])
                                                        // ) {
                                                        //     $cShowBtn = true;
                                                        //     $cShowBtnText = 'REVIEW AUDIT';
                                                        // }

                                                        // for compliance & re compliance
                                                        else if (
                                                            $data['userDetails']['emp_type'] == 3 &&
                                                            in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                                                                ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                                                            ]) && strtotime($cAssesmentDetails->compliance_due_date) >= strtotime(date($GLOBALS['dateSupportArray'][1]))
                                                        ) {
                                                            $cShowBtn = true;
                                                            $cShowBtnText = '';
                                                            $cShowBtnText = ($cAssesmentDetails->audit_status_id == ASSESMENT_TIMELINE_ARRAY[6]['status_id']) ? 'DO RE-COMPLIANCE' : 'DO COMPLIANCE';
                                                        }


                                                        // for review compliance
                                                        else if (
                                                            $data['userDetails']['emp_type'] == 4 && in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[5]['status_id']
                                                            ])
                                                        ) {
                                                            $cShowBtn = true;
                                                            $cShowBtnText = 'REVIEW COMPLIANCE';
                                                        }
                                                        else if (
                                                            $data['userDetails']['emp_type'] == 16 && in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[15]['status_id']
                                                            ])
                                                        ) {
                                                            $cShowBtn = true;
                                                            $cShowBtnText = 'REVIEW COMPLIANCE';
                                                        }

                                                        if ($cShowBtn):
                                                            $tableMrk .= generate_link_button('link', ['value' => $cShowBtnText, 'href' => $data['siteUrls']::getUrl('auditAssessment') . '/assesment/' . encrypt_ex_data($cAssesmentId), 'extra' => view_tooltip('View / Edit')]);

                                                            // for re assesment
                                                            if ($cAssesmentDetails->audit_status_id == ASSESMENT_TIMELINE_ARRAY[3]['status_id'])
                                                                $tableMrk .= "\n" . '<a class="d-block text-danger font-sm" href="' . $data['siteUrls']::getUrl('reports') . '/reaudit-report/' . encrypt_ex_data($cAssesmentId) . '" target="_blank">Audit Review Report &raquo;</a>' . "\n";

                                                        endif;

                                                        if ($cAssesmentDetails->audit_status_id == ASSESMENT_TIMELINE_ARRAY[7]['status_id']) {
                                                            $tableMrk .= '<span class="d-block text-success font-medium text-uppercase">Completed</span>' . "\n";

                                                            // check on hold points
                                                            if (check_on_hold_strict() && $cAssesmentDetails->compliance_onhold_count > 0) {
                                                                $tableMrk .= "\n" . '<a class="d-block text-danger font-sm" href="' . $data['siteUrls']::getUrl('reports') . '/reaudit-report/' . encrypt_ex_data($cAssesmentId) . '" target="_blank">DO COMPLIANCE (ON HOLD POINTS) &raquo;</a>' . "\n";
                                                            }
                                                        } elseif (
                                                            in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                                                                ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                                                            ]) &&
                                                            strtotime($cAssesmentDetails->audit_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))
                                                        ) {
                                                            $tableMrk .= '<span class="d-block text-danger font-medium text-uppercase">Audit Period Expired</span>' . "\n";
                                                        } elseif (
                                                            in_array($cAssesmentDetails->audit_status_id, [
                                                                ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                                                                ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                                                            ]) &&
                                                            strtotime($cAssesmentDetails->compliance_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))
                                                        ) {
                                                            $tableMrk .= '<span class="d-block text-danger font-medium text-uppercase">Compliance Period Expired</span>' . "\n";
                                                        }
                                                    } else {
                                                        $tempStatus = '-';

                                                        if ($cAssesmentDetails->audit_status_id <= 3) //for compliance
                                                            $tempStatus = 'Audit Blocked';
                                                        else if ($cAssesmentDetails->audit_status_id > 3) //for compliance
                                                            $tempStatus = 'Compliance Blocked';

                                                        $tableMrk .= '<span class="d-block text-danger font-medium text-uppercase">' . $tempStatus . '</span>' . "\n";
                                                    }

                                                    $tableMrk .= '</td>' . "\n";
                                                    $tableMrk .= '</tr>' . "\n";
                                                endforeach;

                                            else:
                                                $tableMrk .= '<tr><td colspan="6">' . $data['noti']::getCustomAlertNoti('noDataFound', 'warning', null, 1) . '</td></tr>' . "\n";
                                            endif; ?>



                                            <thead>
                                                <tr class="bg-white border-0">
                                                    <td class="border-0" colspan="4">
                                                        <h5 class="text-primary">Financial Year: <span class="font-medium"><?= $cYearDetails->year . ' - ' . ($cYearDetails->year + 1) ?></span></h5>
                                                    </td>
                                                    <?php if ($data['userDetails']['emp_type'] == '2'): ?>
                                                        <td class="border-0 text-end" colspan="2">
                                                            <?php if ($assesmentBtn): ?>
                                                                <div class="d-flex justify-content-end">
                                                                    <?= generate_link_button('link', ['value' => 'Start Assessment', 'href' => $data['siteUrls']::getUrl('auditAssessment') . '/?unit=' . encrypt_ex_data($cAuditId) . '&fy=' . encrypt_ex_data($cYearDetails->year), 'extra' => view_tooltip('Start Assessment'), 'class' => 'btn btn-primary btn-sm']); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php else: ?>
                                                        <td class="border-0" colspan="2"></td>
                                                    <?php endif; ?>
                                                </tr>
                                                <?= $tableHeadMrk; ?>
                                            </thead>

                                            <?= $tableMrk; ?>

                                        </table>
                                    <?php endif; ?>
                                </div>

                            <?php
                            endforeach;
                        else:
                            echo $data['noti']::getCustomAlertNoti('noDataFound');
                        endif; ?>
                    </div>

                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    <?php endif;

elseif (in_array($data['userDetails']['emp_type'], [5])):

    $riskData = [];
    $riskWeigthedAssesCountData = [];
    $riskTypeWiseScore = [];
    $totalWeightedRiskScore = 0;
    $assesmentWiseScore = [];
    $totalHighRiskJson = [];
    $totalMediumRiskJson = [];
    $totalLowRiskJson = [];
    $currentYear = 0;

    // year model
    $yearModel = $this->model('YearModel');

    $branchRatingModel = $this->model('BranchRatingModel');

    //get all year 
    $year = $yearModel->getAllYears(['where' => 'deleted_at IS NULL']);

    $yearData = generate_array_for_select($year, 'year', 'id');

    //Risk Data Sorting
    foreach ($data['data']['riskData'] as $auditId => $cAssesDetails) {

        $highRiskQuesCount = 0;
        $mediumRiskQuesCount = 0;
        $lowRiskQuesCount = 0;

        // print_r($cAssesDetails);

        $riskWeigthedAssesCountData[$auditId] = count($cAssesDetails);
        foreach ($cAssesDetails as $assesData) {
            if (!array_key_exists($auditId, $riskData)) {
                $riskData[$auditId] = ['weighted_score' => 0, 'avg_weighted_score' => 0, 'year_id' => 0, 'high_from' => 0, 'high_to' => 0, 'medium_from' => 0, 'medium_to' => 0, 'low_from' => 0, 'low_to' => 0, 'high_ques' => 0, 'medium_ques' => 0, 'low_ques' => 0, 'assesWiseRiskData' => []];
            }

            $currentYear = $assesData->year;

            $riskData[$auditId]['year_id'] = $yearData[$assesData->year];
            $riskData[$auditId]['weighted_score'] += $assesData->weighted_score;

            $riskData[$auditId]['avg_weighted_score'] = number_format(($riskData[$auditId]['weighted_score'] / $riskWeigthedAssesCountData[$auditId]), 2, ".", "");

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['period'] = $assesData->assesment_period_from . ' to ' . $assesData->assesment_period_to;

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['wg_sc'] = 0;

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['avg_sc'] = 0;

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['1'] = 0;

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['2'] = 0;

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['3'] = 0;

            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['data'] = [];

            //get all rating 
            $rating = $branchRatingModel->getAllBranchRating([
                'where' => 'audit_unit_id = :audit_unit_id AND year_id = :year_id AND deleted_at IS NULL',
                'params' => [
                    'audit_unit_id' => $auditId,
                    'year_id' => $yearData[$assesData->year],
                ]
            ]);

            $branch_rating = generate_data_assoc_array($rating, 'risk_type_id');

            if (!empty($branch_rating)) {
                $riskData[$auditId]['high_from'] = $branch_rating[1]->range_from;
                $riskData[$auditId]['high_to'] = $branch_rating[1]->range_to;
                $riskData[$auditId]['medium_from'] = $branch_rating[2]->range_from;
                $riskData[$auditId]['medium_to'] = $branch_rating[2]->range_to;
                $riskData[$auditId]['low_from'] = $branch_rating[3]->range_from;
                $riskData[$auditId]['low_to'] = $branch_rating[3]->range_to;
            }

            $totalWeightedRiskScore += $assesData->weighted_score;

            $assesRiskData = (array) $assesData->risk_data;


            foreach ($assesRiskData as $riskId => $riskScoreData) {
                $riskScoreDataArr = json_decode($riskScoreData);
                $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['data'] = $riskScoreDataArr;

                foreach ($riskScoreDataArr as $riskCatId => $riskCatDetails) {
                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['wg_sc'] += $riskCatDetails->wg_sc;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['avg_sc'] += $riskCatDetails->avg_sc;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['1'] += $riskCatDetails->{1};

                    $highRiskQuesCount += $riskCatDetails->{1};

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['2'] += $riskCatDetails->{2};

                    $mediumRiskQuesCount += $riskCatDetails->{2};

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['3'] += $riskCatDetails->{3};

                    $lowRiskQuesCount += $riskCatDetails->{3};
                }
            }

            $riskData[$auditId]['high_ques'] = $highRiskQuesCount;
            $riskData[$auditId]['medium_ques'] = $mediumRiskQuesCount;
            $riskData[$auditId]['low_ques'] = $lowRiskQuesCount;
        }
    }

    //Branches Risk Json

    $riskBranchesJson = [];
    foreach ($riskData as $auditId => $auditData) {
        if ($auditData['weighted_score'] > $auditData['high_to'])
            $color = 'rgba(220,20,60)';
        elseif ($auditData['weighted_score'] > $auditData['medium_to'] && $auditData['weighted_score'] <= $auditData['medium_from'])
            $color = 'rgba(255,165,0)';
        elseif ($auditData['weighted_score'] > $auditData['low_to'] && $auditData['weighted_score'] <= $auditData['low_from'])
            $color = 'rgba(34,139,34)';

        $riskBranchesJson[] = [
            'y' => $auditData['weighted_score'],
            'label' => $data['data']['auditUnitData'][$auditId],
            'color' => $color
        ];
    }

    // Expired Count
    $complianceExpCount = 0;
    $auditExpCount = 0;
    $auditPending = 0;
    $auditAssesments = [];
    $auditUnits = [];

    foreach ($data['data']['db_data'] as $cAuditId => $cAuditData) {
        $auditUnits[$cAuditId] = $cAuditData->name;

        if (isset($cAuditData->year_data)) {
            foreach ($cAuditData->year_data as $cYearId => $cAllYearData) {
                foreach ($cAllYearData->assesment_data as $cAssesId => $cAssesData) {
                    if ($cYearId == $data['data']['fin_year'][$currentYear])
                        $auditAssesments[$cAssesId] = $cAssesData->assesment_period_from . ' to ' . $cAssesData->assesment_period_to;

                    if (
                        in_array($cAssesData->audit_status_id, [
                            ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                            ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                        ]) &&
                        strtotime($cAssesData->compliance_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))
                    )
                        $complianceExpCount++;
                    elseif (
                        in_array($cAssesData->audit_status_id, [
                            ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                            ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                        ]) &&
                        strtotime($cAssesData->audit_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))
                    )
                        $auditExpCount++;
                    elseif ($cAssesData->audit_status_id == 1 && $data['data']['empType'] == 2)
                        $auditPending++;
                    elseif ($cAssesData->audit_status_id == 4 && $data['data']['empType'] == 3)
                        $auditPending++;
                    elseif (($cAssesData->audit_status_id == 2 || $cAssesData->audit_status_id == 5) && $data['data']['empType'] == 4)
                        $auditPending++;
                    elseif (($cAssesData->audit_status_id == 2 || $cAssesData->audit_status_id == 15) && $data['data']['empType'] == 16)
                        $auditPending++;
                    elseif ($data['data']['empType'] == 5)
                        $auditPending++;

                }
            }
        }
    }
    ?>
    <div class="row">
        <div class="col-3 mb-4 mt-3 p-2">
            <div class="dash-cards">
                <article class="dash-information dash-card  shadow-sm">
                    <dl class="dash-details">
                        <div>
                            <dd>
                                <h4><?= $totalWeightedRiskScore ?></h4>
                            </dd>
                        </div>
                    </dl>
                    <h5 class="dash-header text-center">Total Weighted Risk </h5>
                </article>
            </div>
        </div>

        <div class="col-3 mb-4 mt-3 p-2">
            <div class="dash-cards">
                <article class="dash-information dash-card  shadow-sm">
                    <dl class="dash-details">
                        <div>
                            <dd>
                                <h4><?= $auditPending ?></h4>
                            </dd>
                        </div>
                    </dl>
                    <h5 class="dash-header text-center">Total Audit Pendings </h5>
                </article>
            </div>
        </div>

        <div class="col-3 mb-4 mt-3 p-2">
            <div class="dash-cards">
                <article class="dash-information dash-card  shadow-sm">
                    <dl class="dash-details">
                        <div>
                            <dd>
                                <h4><?= $auditExpCount ?></h4>
                            </dd>
                        </div>
                    </dl>
                    <h5 class="dash-header text-center">Total Audit Expired </h5>
                </article>
            </div>
        </div>

        <div class="col-3 mb-4 mt-3 p-2">
            <div class="dash-cards">
                <article class="dash-information dash-card  shadow-sm">
                    <dl class="dash-details">
                        <div>
                            <dd>
                                <h4><?= $complianceExpCount ?></h4>
                            </dd>
                        </div>
                    </dl>
                    <h5 class="dash-header text-center">Total Compliance Expired</h5>
                </article>
            </div>
        </div>

        <?php
        echo FormElements::generateFormStart(["name" => "chart", "action" => $data['me']->url]);
        //asses_period
        $markup = FormElements::generateLabel('audit_unit', 'Select Assesment Period');

        if (is_array($auditUnits) && sizeof($auditUnits) > 0) {
            $markup .= FormElements::generateSelect([
                "id" => "audit_unit",
                "name" => "audit_unit",
                "options" => $auditUnits,
                "selected" => $data['request']->input('audit_unit'),
                "dataAttributes" => ["data-audit-unit-id-url" => $data['siteUrls']::getUrl('dashboard') . '/assesDaysBarDataAjx']
            ]);

        } else
            $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


        echo FormElements::generateFormGroup($markup, $data, 'asses_period');
        echo FormElements::generateFormClose();

        ?>

        <div class="col-12 mb-4 mt-2 rounded">
            <div class="pt-2 pb-2 border bg-white shadow-md rounded">
                <div id="assesmentDaysChart" style="height: 370px; max-width: 920px; margin: 0px auto;"></div>
            </div>
        </div>

        <div class="col-6 mb-4 mt-2 rounded">
            <div class="pt-2 pb-2 border bg-white shadow-md rounded">
                <div id="quesRiskPieChart" style="height: 300px; width: 100%;"></div>
            </div>
        </div>

        <div class="col-6 mb-4 mt-2 rounded">
            <div>
                <article class="dash-information dash-card-top shadow-sm">
                    <dl class="dash-details">
                        <div>
                            <dd>
                                <h4 id="totalRiskWeightedScoreSingle"></h4>
                            </dd>
                        </div>
                    </dl>
                    <h5 class="dash-header text-center">Total Weighted Risk Score of Branch</h5>
                </article>

                <article class="dash-information dash-card-top  shadow-sm">
                    <dl class="dash-details">
                        <div>
                            <dd>
                                <h4 id="avgRiskWeightedScoreSingle"></h4>
                            </dd>
                        </div>
                    </dl>
                    <h5 class="dash-header text-center">Average Weighted Risk Score of Branch</h5>
                </article>
            </div>
        </div>

        <div class="col-12 mb-4 mt-2">
            <div class="pt-2 pb-2 border bg-white shadow-md rounded">

                <div id="json-data-riskBranchesBarChart" style="display: none;">
                    <?php echo json_encode($riskBranchesJson); ?></div>

                <div id="riskBranchesBarChart" style="height: 300px; width: 100%;"></div>

            </div>
        </div>

        <div class="col-12 mb-4 mt-2">
            <div class="border bg-white shadow-md rounded">
                <table class="table v-table kp-table table-hover">
                    <thead style="background-color: #6777ef">
                        <tr>
                            <th>Sr. No.</th>
                            <th>Branch</th>
                            <th>Weighted Score</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $srNo = 1;
                        $tempVal = null; // Changed from an empty string to null for better comparison
                        foreach ($riskData as $cAuditId => $cAuditScoreData) {
                            if ($tempVal === null) {
                                $trend = '-';
                            } elseif ($tempVal > $cAuditScoreData['weighted_score']) {
                                $trend = 'Decreasing';
                            } elseif ($tempVal < $cAuditScoreData['weighted_score']) {
                                $trend = 'Increasing';
                            } else {
                                $trend = '-'; // In case the values are equal
                            }

                            // Removed <a> tag wrapping <tr> and included JavaScript for row click event
                            echo '<tr onclick="window.location.href=\'' . SiteUrls::getUrl('dashboard') . '/dashboard-toplevel?auditUnit=' . encrypt_ex_data($cAuditId) . '\'">';
                            echo '<td>' . $srNo . '</td>';
                            echo '<td>' . $data['data']['auditUnitData'][$cAuditId] . '</td>';
                            echo '<td>' . $cAuditScoreData['weighted_score'] . '</td>';
                            echo '<td>' . $trend . '</td>';
                            echo '</tr>';

                            $srNo++;
                            $tempVal = $cAuditScoreData['weighted_score'];
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
<?php endif; ?>