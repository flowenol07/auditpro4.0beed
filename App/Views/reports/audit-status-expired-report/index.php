<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(isset($data['data']['details_of_audit_data']))
{
    if(!empty($data['data']['details_of_audit_data']) && sizeof($data['data']['details_of_audit_data']) > 0):

        echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);

    ?>

        <div class="table-responsive">
            <table class="table table-bordered v-table mt-2 exportToExcelTable">
                <thead>
                    <tr class="bg-light-gray">
                        <th style="width:5%" class="text-center">Sr. No</th>
                        <th style="width:10%">Audit Unit</th>
                        <th style="width:15%">Auditor</th>
                        <th style="width:8%">Audit Start Date</th>
                        <th style="width:8%">Audit End Date</th>
                        <th style="width:15%">Assesment Period</th>
                        <th style="width:10%" class="text-center">Audit Status</th>
                        <th style="width:8%">Compliance Start Date</th>
                        <th style="width:8%">Compliance End Date</th>
                        <th style="width:13%">Compliance Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php

                    $i = 0;

                    foreach($data['data']['details_of_audit_data'] as $cKey => $cAssesData)
                    {
                        $auditStatus = false;
                        $i++;

                        echo
                        '<tr>
                            <td style="width:5%" class="text-center">' . $i . '</td>

                            <td style="width:10%">'. $data['data']['audit_unit_data'][ $cAssesData -> audit_unit_id ] -> combined_name .'</td>

                            <td style="width:15%">'. (isset($data['data']['db_employee_data'][$cAssesData -> audit_emp_id]) ? ($data['data']['db_employee_data'][$cAssesData -> audit_emp_id] -> combined_name) : '-') .'</td>

                            <td style="width:8%">'. $cAssesData -> audit_start_date . '</td> 
                            <td style="width:8%">' . $cAssesData -> audit_end_date .'</td>

                            <td style="width:15%">'
                                . $cAssesData -> assesment_period_from . '  to  ' . $cAssesData -> assesment_period_to .'
                                <span class="d-inline-block">( Frequency : ' . $cAssesData -> frequency . ' Months )</span>
                            </td>

                            <td style="width:10%" class="text-center">';

                                if(in_array($cAssesData -> audit_status_id, [1, 2, 3])) 
                                {
                                    echo "<span style='color:red; font-size:14px' class='text-center'>( EXPIRED ON " . $cAssesData -> audit_due_date . ' )</span>';
                                }
                                else
                                {
                                    $auditStatus = true;
                                    echo "COMPLETED";
                                }

                            echo '</td>';
                            
                            if($auditStatus)
                            {
                                echo'
                                <td style="width:8%">'. $cAssesData -> compliance_start_date .'</td>
                                <td style="width:8%">'. $cAssesData -> compliance_end_date .'</td>
                                <td style="width:13%">';

                                    if(in_array($cAssesData -> audit_status_id, [4, 5, 6])) 
                                    {
                                        echo "<span style='color:red; font-size:14px'>( EXPIRED ON " . $cAssesData -> compliance_due_date . ' )</span>';
                                    }
                                    else
                                        echo "COMPLETED";

                                echo'</td>';       
                            }
                            else
                                echo'
                                <td style="width:8%"> - </td>
                                <td style="width:8%"> - </td>
                                <td style="width:13%"> - </td>';                 
                        echo '</tr>';
                    }
                ?>
                </tbody>
            </table>
        </div>

    </div>

<?php     
else:
?>
    <div class="mt-2">
        <?= $data['noti']::getCustomAlertNoti('noDataFound'); ?>
    </div>
<?php
endif; 
    
}
?>