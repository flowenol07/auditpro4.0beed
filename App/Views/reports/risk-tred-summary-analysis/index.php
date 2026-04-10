<?php require "form.php"; ?>

<div class="col-12 mb-4 mt-3" id="printContainer">
    <div class="dashboard-card">
        <div class="card-header">
            <span>Assessment Wise Score Summary</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table kp-table mb-0 text-center">
                    <thead>
                        <tr>
                            <th>Sr. No.</th>
                            <th>Assessment Period</th>
                            <th>Frequency</th>
                            <th>Weighted Score</th>
                            <th>High</th>
                            <th>Low</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['data']['audit_scoring_data'])): ?>

                            <?php
                            // Step 1: Calculate max difference ONCE
                            $differences = [];
                            $prev = null;

                            foreach ($data['data']['audit_scoring_data'] as $row) {
                                if ($prev !== null) {
                                    $differences[] = abs($prev - $row->weighted_score);
                                }
                                $prev = $row->weighted_score;
                            }

                            $maxDifference = !empty($differences) ? max($differences) : 0;
                            $previousScore = null;
                            ?>

                            <?php foreach ($data['data']['audit_scoring_data'] as $key => $value): ?>

                                <?php
                                /* =========================
                                   Frequency Calculation
                                ========================== */
                                $fromDate = new DateTime($value->assesment_period_from);
                                $toDate = new DateTime($value->assesment_period_to);

                                $interval = $fromDate->diff($toDate);
                                $monthsDiff = ($interval->y * 12) + $interval->m;

                                $frequency = ($monthsDiff > 0)
                                    ? $monthsDiff . ' Month' . ($monthsDiff > 1 ? 's' : '')
                                    : 'Less than 1 Month';

                                /* =========================
                                   High / Low Arrow Logic
                                ========================== */
                                $highArrow = $lowArrow = '';

                                if ($previousScore !== null && $maxDifference > 0) {
                                    $difference = abs($previousScore - $value->weighted_score);
                                    $ratio = $difference / $maxDifference;

                                    if ($ratio >= 0.5) {
                                        $highArrow = '<button class="btn btn-danger btn-sm">▼</button>';
                                    } else {
                                        $lowArrow = '<button class="btn btn-success btn-sm">▼</button>';
                                    }
                                }

                                $previousScore = $value->weighted_score;
                                ?>

                                <tr>
                                    <td><?= $key + 1 ?></td>

                                    <td>
                                        <?= $value->assesment_period_from . ' - To - ' . $value->assesment_period_to ?>
                                    </td>

                                    <td><?= $frequency ?></td>

                                    <td><strong><?= number_format($value->weighted_score, 2) ?></strong></td>

                                    <td><?= $highArrow ?></td>
                                    <td><?= $lowArrow ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No Data Found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>


                </table>
            </div>
        </div>
    </div>
</div>