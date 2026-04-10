<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(isset($data['data']['circular_data'])) { 

if(!empty($data['data']['circular_data'])):

echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

function generate_table_tr_markup($data, $assesData, $extra = [])
{
    $markup = '';
    $cFreq = isset(COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $assesData -> frequency ]) ? COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $assesData -> frequency ]['title'] : ERROR_VARS['notFound'];

    $cAuditUnit = is_array($data['data']['db_audit_unit_data']['data']) && isset($data['data']['db_audit_unit_data']['data'][ $assesData -> audit_unit_id ]) ? $data['data']['db_audit_unit_data']['data'][ $assesData -> audit_unit_id ] -> combined_name : ERROR_VARS['notFound'];

    $cReportingDate = trim_str($assesData -> reporting_date);
    $cDueDate = $assesData -> compliance_due_date;
    $reDay = date('d', strtotime($cReportingDate));

    // assesment not started
    $markup .= '<tr>' . "\n";

        // sr no 
        $markup .= '<td class="text-center">'. $extra['srNo'] .'</td>' . "\n";

        // common data
        $markup .= '<td>'. $extra['common']['authority'] .'</td>' . "\n";
        $markup .= '<td>'. $extra['common']['circular_name'] .'</td>' . "\n";
        $markup .= '<td>'. $extra['common']['circular_date'] .'</td>' . "\n";
        $markup .= '<td>'. $extra['common']['task_set_name'] .'</td>' . "\n";

        // audit unit data
        $markup .= '<td>'. string_operations($cAuditUnit, 'upper') .'</td>' . "\n";

        // frequency
        $markup .= '<td>'. $cFreq .'</td>' . "\n";

        // compliance period
        $markup .= '<td>'. $assesData -> com_period_from . ' - ' . $assesData -> com_period_to .'</td>' . "\n";

        // generate reporting date
        $markup .= '<td>'. $cReportingDate .'</td>' . "\n";

        // generate due date
        $markup .= '<td>'. $cDueDate .'</td>' . "\n";

        // submisson date
        $markup .= '<td>'. $assesData -> submitted_report_date .'</td>' . "\n";

        // not started
        $status = string_operations('NOT STARTED', 'upper');

        if($assesData -> com_status_id == 1) // PENDING
            $status = 'PENDING';
        else if($assesData -> com_status_id == 2) //IN REVIEW
            $status = 'IN REVIEW';
        else if($assesData -> com_status_id == 3) // PARTIAL FAILED
            $status = 'RE COMPLIANCE';
        else if($assesData -> com_status_id == 4) // COMPLETED
            $status = 'COMPLETED';

        // check over due
        if( !isset($extra['notStarted']) && $assesData -> com_status_id < 4 && strtotime(date($GLOBALS['dateSupportArray']['1'])) > strtotime($cDueDate) )
            $status = 'DELAYED';

        // compliance status
        $markup .= '<td>'. string_operations($status, 'upper') /*. ' ' . $assesData -> com_status_id*/ .'</td>' . "\n";
        // $markup .= '<td></td>' . "\n";
        $markup .= '<td></td>' . "\n";

    $markup .= '</tr>' . "\n";

    return $markup;
}

?>
    <style>
        .exportToExcelTable th:nth-child(1), .exportToExcelTable td:nth-child(1) { width: 9%; } 
        .exportToExcelTable th:nth-child(2), .exportToExcelTable td:nth-child(2) { width: 15%; } 
        .exportToExcelTable th:nth-child(3), .exportToExcelTable td:nth-child(3) { width: 15%; } 
        .exportToExcelTable th:nth-child(4), .exportToExcelTable td:nth-child(4) { width: 10%; } 
        .exportToExcelTable th:nth-child(5), .exportToExcelTable td:nth-child(5) { width: 15%; } 
        .exportToExcelTable th:nth-child(6), .exportToExcelTable td:nth-child(6) { width: 10%; } 
        .exportToExcelTable th:nth-child(7), .exportToExcelTable td:nth-child(7) { width: 10%; } 
        .exportToExcelTable th:nth-child(8), .exportToExcelTable td:nth-child(8) { width: 8%; } 
        .exportToExcelTable th:nth-child(9), .exportToExcelTable td:nth-child(9) { width: 8%; }
    </style>

    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th class="text-center">Sr. No.</th>
                    <th>Authority</th>
                    <th>Form / Return No.</th>
                    <th>Circular Date</th>
                    <th>Task Set Name</th>
                    <th>Assigned To</th>
                    <th>Frequency</th>
                    <th>Compliance Period</th>
                    <th>Reporting Date</th>
                    <th>Due Date</th>
                    <th>Submission Date</th>
                    <th>Compliance Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    $srNo = 1;

                    foreach($data['data']['circular_data'] as $cCircularId => $cCircularData)
                    {   
                        // get authority
                        $cCircularAuth = is_array($data['data']['db_authority_data']['data']) && isset($data['data']['db_authority_data']['data'][ $cCircularData -> authority_id ]) ? $data['data']['db_authority_data']['data'][ $cCircularData -> authority_id ] -> name : ERROR_VARS['notFound'];

                        $commonTds = [
                            'authority' => string_operations($cCircularAuth, 'upper'),
                            'circular_name' => trim_str($cCircularData -> name),
                            'task_set_name' => '',
                            'circular_date' => trim_str($cCircularData -> circular_date),
                        ];

                        foreach($cCircularData -> com_asses_data as $cGenKey => $cGenData)
                        {
                            $getTaskSetName = explode('_', $cGenKey);
                            $getTaskSetName = isset($getTaskSetName[4]) ? $getTaskSetName[4] : 0;
                            $getTaskSetName = isset($cCircularData -> assign_master) && isset($cCircularData -> assign_master[ $getTaskSetName ]) ? $cCircularData -> assign_master[ $getTaskSetName ] -> name : ERROR_VARS['notFound'];

                            $commonTds['task_set_name'] = $getTaskSetName;

                            // check asses data
                            if(isset($cGenData['com_asses_data']) && sizeof($cGenData['com_asses_data']) > 0)
                            {
                                // has data
                                foreach($cGenData['com_asses_data'] as $cComAssesId => $cComAssesData)
                                {
                                    echo generate_table_tr_markup($data, (object)[
                                        'frequency' => $cGenData['frequency'],
                                        'audit_unit_id' => $cComAssesData -> audit_unit_id,
                                        'compliance_due_date' => $cComAssesData -> compliance_due_date,
                                        'reporting_date' => $cGenData['reporting_date'],
                                        'com_period_from' => $cGenData['com_period_from'],
                                        'com_period_to' => $cGenData['com_period_to'],
                                        'com_status_id' => $cComAssesData -> com_status_id,
                                        'submitted_report_date' => (isset($cComAssesData -> submitted_report_date) ? $cComAssesData -> submitted_report_date : '')
                                    ], [
                                        'srNo' => $srNo,
                                        'common' => $commonTds
                                    ]);

                                    $srNo++;
                                }
                            }

                            if(isset($cGenData['assigned_audit_units']) && sizeof($cGenData['assigned_audit_units']) > 0)
                            {
                                // has data
                                foreach($cGenData['assigned_audit_units'] as $cAuditUnitId)
                                {
                                    echo generate_table_tr_markup($data, (object)[
                                        'frequency' => $cGenData['frequency'],
                                        'audit_unit_id' => $cAuditUnitId,
                                        'compliance_due_date' => $cGenData['due_date'],
                                        'reporting_date' => $cGenData['reporting_date'],
                                        'com_period_from' => $cGenData['com_period_from'],
                                        'com_period_to' => $cGenData['com_period_to'],
                                        'com_status_id' => 0,
                                        'submitted_report_date' => null
                                    ], [
                                        'srNo' => $srNo,
                                        'common' => $commonTds,
                                        'notStarted' => 1
                                    ]);

                                    $srNo++;
                                }
                            }
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>

<?php

echo '</div>' . "\n";

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

}

?>