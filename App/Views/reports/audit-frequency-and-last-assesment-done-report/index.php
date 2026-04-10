<?php 

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(!empty($data['data']['audit_unit_data'])):

    echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data']);

?>  
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:10%">Section</th>
                    <th style="width:10%" class="text-center">Audit Unit Code</th>
                    <th style="width:20%">Audit Unit Name</th>
                    <th style="width:20%">Unit Head</th>
                    <th style="width:20%">Unit Sub Head</th>
                    <th style="width:10%" class="text-center">Audit Frequency</th>
                    <th style="width:10%">Last Assesment Done Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['audit_unit_data'] as $cKey => $cAuditData)
                    {  
                        echo '<tr>
                            <td style="width:10%">' . ( isset($data['data']['db_audit_section_data'][ $cAuditData -> section_type_id ]) ? $data['data']['db_audit_section_data'][ $cAuditData -> section_type_id ] -> name : ERROR_VARS['notFound'] )  . '</td>

                            <td style="width:10%" class="text-center">' . $cAuditData -> audit_unit_code .'</td>

                            <td style="width:20%">' . string_operations($cAuditData -> name, 'upper') .'</td>

                            <td style="width:20%">' . string_operations(((array_key_exists($cAuditData -> branch_head_id, $data['data']['employee_data'])) ? $data['data']['employee_data'][$cAuditData -> branch_head_id] -> name : ERROR_VARS['notFound']), 'upper') .'</td>
                            
                            <td style="width:20%">' . string_operations(((array_key_exists($cAuditData -> branch_subhead_id, $data['data']['employee_data'])) ? $data['data']['employee_data'][$cAuditData -> branch_subhead_id] -> name : ERROR_VARS['notFound']), 'upper') .'</td>

                            <td style="width:10%" class="text-center">' . $cAuditData -> frequency .'</td>
                            <td style="width:10%">' . $cAuditData -> last_audit_date .'</td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>

<?php

echo '</div>' . "\n";

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');;
endif;

?>