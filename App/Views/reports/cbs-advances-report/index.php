<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(!empty($data['data']['dump_data'])):

    $fy = $data['data']['db_year_data'][$data['data']['dump_data'][0] -> year_id[0]];

    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], true, $fy);
?>
        <div class="table-responsive">
            <table class="table table-bordered v-table mt-2 exportToExcelTable">
                <thead>
                    <tr class="bg-light-gray">
                        <th style="width:40%">Upload From Period</th>
                        <th style="width:40%">Upload To Period</th>
                        <th style="width:20%">Upload Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($data['data']['dump_data'] as $cKey => $cDumpData)
                        {   
                            echo'
                            <tr>
                                <td style="width:40%">' . $cDumpData -> upload_period_from . '</td>
                                <td style="width:40%">' . $cDumpData -> upload_period_to .'</td>
                                <td style="width:20%">' . date('Y-m-d', strtotime($cDumpData -> upload_date)) .'</td>
                            </tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

<?php endif; ?>