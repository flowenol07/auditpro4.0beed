<?php

if(!empty($data['data']['audit_unit_data'])):
    
echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

echo '<div id="printContainer">' . "\n";
generate_report_header($data['data']);

?>
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:10%">Section</th>
                    <th style="width:10%" class="text-center">Audit Unit Code</th>
                    <th style="width:10%">Audit Unit Name</th>
                    <th style="width:15%">Head Name</th>
                    <th style="width:10%">Contact Of Head</th>
                    <th style="width:15%">Sub-Head Name</th>
                    <th style="width:10%">Contact Of Sub-Head</th>
                    <th style="width:10%">Last Assesment Date</th>
                    <th style="width:10%" class="text-center">Status</th>
                </tr>
            </thead>

            <tbody>
            <?php
                foreach($data['data']['audit_unit_data'] as $cKey => $cUnitData)
                {   
                    echo
                    '<tr>
                        <td style="width:10%">' . (array_key_exists($cUnitData -> section_type_id, $data['data']['db_audit_section_data']) ? $data['data']['db_audit_section_data'][ $cUnitData -> section_type_id ] -> name : ERROR_VARS['notFound']) . '</td>
                        <td style="width:10%" class="text-center">' . $cUnitData -> audit_unit_code . '</td>
                        <td style="width:10%">' . string_operations($cUnitData -> name, 'upper') . '</td>';

                        $emp = array_key_exists($cUnitData -> branch_head_id, $data['data']['db_employee_data']) ? $data['data']['db_employee_data'][ $cUnitData -> branch_head_id ] : null;

                        echo '<td style="width:15%">' . string_operations((is_object($emp) ? $emp -> name : ERROR_VARS['notFound']), 'upper') . '</td>';
                        echo '<td style="width:10%">' . (is_object($emp) ? $emp -> mobile : ERROR_VARS['notFound']) . '</td>';

                        $emp = array_key_exists($cUnitData -> branch_subhead_id, $data['data']['db_employee_data']) ? $data['data']['db_employee_data'][ $cUnitData -> branch_subhead_id ] : null;
                        
                        echo '<td style="width:15%">' . string_operations((is_object($emp) ? $emp -> name : ERROR_VARS['notFound']), 'upper') . '</td>';
                        echo '<td style="width:10%">' . (is_object($emp) ? $emp -> mobile : ERROR_VARS['notFound']) . '</td>';
                        
                        echo '<td style="width:10%">' . $cUnitData -> last_audit_date . '</td>
                        <td style="width:10%" class="text-center">' . check_active_status($cUnitData -> is_active) .'</td>
                    </tr>';
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

?>