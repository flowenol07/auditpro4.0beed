<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(isset($data['data']['details_of_audit_data']))
{
    if(!empty($data['data']['details_of_audit_data'])):

    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], false, 0, true);
?>

        <div class="table-responsive-lg">
            <table class="table table-bordered v-table exportToExcelTable">
                
                <thead>
                    <tr class="bg-light-gray">
                        <th style="width:10%">Sr. No.</th>
                        <th style="width:20%">Inspection Type</th>
                        <th style="width:10%" class="text-center">Rejected Count</th>
                        <th style="width:10%">Employee Name</th>
                        <th style="width:40%">Status</th>
                        <th style="width:20%">Status Changed On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        
                        $i = 0;
                        foreach($data['data']['details_of_audit_data'] as $cKey => $cAssesData)
                        {  
                            $i++;

                            echo
                            '<tr>
                                <td style="width:10%">' . $i . '</td>

                                <td style="width:20%">' . (isset($cAssesData -> type_id) ? $data['data']['typeArray'][ $cAssesData -> type_id ] : " - ") .'</td>

                                <td style="width:10%" class="text-center">' . (($cAssesData -> status_id == 3 || $cAssesData -> status_id == 6) ? $cAssesData -> rejected_cnt : " - ").'</td>

                                <td style="width:10%">' . (isset($cAssesData -> reviewer_emp_id) && array_key_exists($cAssesData -> reviewer_emp_id, $data['data']['employee_data']) ? $data['data']['employee_data'][ $cAssesData -> reviewer_emp_id ] -> name : ERROR_VARS['notFound'] ). '</td>

                                <td style="width:40%">' . (isset($cAssesData -> status_id) ? ASSESMENT_TIMELINE_ARRAY[$cAssesData -> status_id]['title'] : ERROR_VARS['notFound']) .'</td>
                                
                                <td style="width:20%">' . $cAssesData -> created_at.'</td>
                            </tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

<?php else: ?>

    <div class="mt-2">
        <?= $data['noti']::getCustomAlertNoti('noDataFound'); ?>
    </div>

<?php endif; 

}

?>