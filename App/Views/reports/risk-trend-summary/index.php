<?php require "form.php"; ?>

<div class="col-12 mb-4 mt-3" id="printContainer">
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
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Frequency</th>
                            <th>Weighted Score</th>
                            <th>Trend</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (isset($data['data']['audit_scoring_data']) && !empty($data['data']['audit_scoring_data'])): ?>

                            <?php
                            $previousScore = null;
                            ?>

                            <?php foreach ($data['data']['audit_scoring_data'] as $key => $value): ?>

                                <?php
                                /* =========================
                                   Frequency Calculation
                                ========================== */
                                $fromDate = new DateTime($value->assesment_period_from);
                                $toDate   = new DateTime($value->assesment_period_to);

                                $interval = $fromDate->diff($toDate);
                                $monthsDiff = ($interval->y * 12) + $interval->m;

                                $frequency = ($monthsDiff > 0)
                                    ? $monthsDiff . '' . ($monthsDiff > 1 ?' ':'')
                                    : '1';


                                /* =========================
                                   Trend Calculation
                                ========================== */
                                $trendHtml = '-';

                                if ($previousScore !== null) {
                                    if ($value->weighted_score > $previousScore) {
                                        $trendHtml = '<span class="btn btn-danger btn-sm text-white fw-bold">▲ Increasing</span>';
                                    } elseif ($value->weighted_score < $previousScore) {
                                        $trendHtml = '<span class="btn btn-success btn-sm text-white fw-bold">▼ Decreasing</span>';
                                    } else {
                                        $trendHtml = '<span style="color:gray;">— No Change</span>';
                                    }
                                }

                                $previousScore = $value->weighted_score;
                                ?>

                                <tr>
                                    <td><?php echo $key + 1; ?></td>
                                    <td>
                                        <?php echo $value->assesment_period_from . ' - To - ' . $value->assesment_period_to; ?>
                                    </td>
                                    <td><?php echo $value->audit_start_date; ?></td>
                                    <td><?php echo $value->audit_end_date; ?></td>
                                    <td><?php echo $frequency; ?></td>
                                    <td><?php echo $value->weighted_score; ?></td>
                                    <td><?php echo $trendHtml; ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No Data Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
