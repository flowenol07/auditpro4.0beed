<?php
use Core\FormElements;

if(!empty($data['data']['year_data']) && !empty($data['data']['audit_units_data'])):

    // generate button
    echo '<div class="no-print mb-3">' . "\n";
        generate_report_buttons(['print','excel']);
    echo '</div>' . "\n";
    
    echo '<div id="printContainer">' . "\n";

    // generate header 
    generate_report_header($data['data']);   
?>

<style>
    .audit-report-table { font-size: 12px; }
</style>
    
<div class="table-responsive">
    <table id="exportToExcelTable" class="table table-bordered audit-report-table v-table mt-3">

        <thead>
        <tr class="bg-light-gray">
            <th style="width:4%" class="text-center">Sr. No.</th>
            <th style="width:8%">Audit Unit</th>
            <th style="width:8%">Audit Details</th>
            <?php

            $i = 4;
            $fy = $data['data']['year_data'] -> year;
            
            while(1) {

                echo '<th style="width:7%">' . "\n";
                    echo date('M - y', strtotime($fy . '-' . $i . '-01'));
                echo '</th>' . "\n";

                $i++;

                if($i > 12) { $i = 1; $fy++; }
                if($i == 4) break;
            }
            
            ?>
        </tr>
        </thead>

        <tbody>
        
        <?php

            $srNo = 1;

            foreach ($data['data']['audit_units_data'] as $cAuditUnitId => $cAuditUnitDetails) {
                $row1 = '';
                $row2 = '';
                $row3 = '';
            
                $i = 4;
                $tempAssesData = $cAuditUnitDetails -> asses_data;
                
                while (true) {
                    
                    $nodata = false;
            
                    if (sizeof($tempAssesData) > 0) {

                        foreach ($tempAssesData as $cAssesId => $cAssesData) {

                            $cMonth = date('m', strtotime($cAssesData->assesment_period_from));
            
                            if ($i == ltrim($cMonth, '0'))
                            {
                                $colspan = $cAssesData -> frequency > 1 ? $cAssesData -> frequency : 1;
                                $reviewDate = $cAssesData -> audit_review_date;
                                $complianceDate = $cAssesData -> compliance_start_date;

                                $row1 .= '<td style="width:7%"' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : '') . '>' . get_convert_date_format($cAssesData -> audit_start_date, 'dmy') . '</td>';
                                $row2 .= '<td style="width:7%"' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : '') . '>' . get_convert_date_format($reviewDate, 'dmy') . '</td>';
                                $row3 .= '<td style="width:7%"' . ($colspan > 1 ? ' colspan="' . $colspan . '"' : '') . '>' . get_convert_date_format($complianceDate, 'dmy') . '</td>';
            
                                $nodata = true;
                                $i += $colspan;
                                unset($tempAssesData[$cAssesId]);
                                break;
                            }
                        }
                    }
            
                    if (!$nodata) {
                        // If no data found, add an empty cell for all three rows
                        $row1 .= '<td style="width:7%"></td>';
                        $row2 .= '<td style="width:7%"></td>';
                        $row3 .= '<td style="width:7%"></td>';
                        $i++;
                    }
            
                    if ($i > 12) $i = 1;
                    if ($i == 4) break;
                }
            
                // Output rows
                echo '<tr>' . "\n";
                    echo '<td style="width:4%" class="text-center" rowspan="3">' . $srNo . '</td>' . "\n";
                    echo '<td style="width:8%" rowspan="3">' . $cAuditUnitDetails -> combined_name . '</td>' . "\n";
                    echo '<td style="width:8%">Audit Dt.</td>' . "\n";
                    echo $row1;
                echo '</tr>' . "\n";
            
                echo '<tr>' . "\n";
                    echo '<td style="width:8%">Report Send Dt.</td>' . "\n";
                    echo $row2;
                echo '</tr>' . "\n";
            
                echo '<tr>' . "\n";
                    echo '<td style="width:8%">Compl. Recv. Dt.</td>' . "\n";
                    echo $row3;
                echo '</tr>' . "\n";
            
                $srNo++;
            }
        ?>

        </tbody>

    </table>
</div>


<?php
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');;
endif;
?>
