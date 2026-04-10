<?php

if(!empty($data['data']['db_year_data'])):

    echo '<div class="no-print mb-3">';
    require_once('form.php');
    echo '</div>';

    if(!empty($data['data']['assesment_data']))
    {   
        $fy = $data['data']['db_year_data'][$data['data']['assesment_data'][0] -> year_id];

        echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], true, $fy);
?>

<div class="table-responsive-lg">
    <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
        <thead>
            <tr class="bg-light-gray">
                <th style="width:15%">Audit Unit Name</th>
                <th style="width:15%">Audit Period</th>
                <th style="width:15%">Auditor Allocated</th>
                <th style="width:35%">Compliance Allocated</th>
                <th style="width:10%">Audit Start Date</th>
                <th style="width:10%">Audit End Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
                
                foreach($data['data']['assesment_data'] as $cKey => $cAssesData)
                {  
                    echo
                    '<tr>
                        <td style="width:15%">' . $data['data']['db_audit_unit_data'][$cAssesData -> audit_unit_id] -> combined_name . '</td>
                        <td style="width:15%">' . $cAssesData -> combined_period .'</td>';

                        echo '<td style="width:15%">';
                            echo ((array_key_exists($cAssesData -> audit_emp_id, $data['data']['db_employee_data'])) ? ($data['data']['db_employee_data'][ $cAssesData -> audit_emp_id ] -> name . ' [ ' . $cAssesData -> audit_emp_id . ' ]') : ERROR_VARS['notFound']);      
                        echo '</td>';

                        echo '<td style="width:35%">';

                            echo '<span class="font-sm text-primary d-block">Branch Head: </span>';

                            $emp = array_key_exists($cAssesData -> branch_head_id, $data['data']['db_employee_data']) ? $data['data']['db_employee_data'][ $cAssesData -> branch_head_id ] : null;

                            echo '<p class="mb-1">' . (is_object($emp) ? ( $emp -> name . ' [ ' . $emp -> emp_code . ' ]' ) : ERROR_VARS['notFound']) . '</p>';
                        
                            echo '<span class="font-sm text-primary d-block">Branch Sub Head: </span>';

                            $emp = array_key_exists($cAssesData -> branch_subhead_id, $data['data']['db_employee_data']) ? $data['data']['db_employee_data'][ $cAssesData -> branch_subhead_id ] : null;

                            echo '<p class="mb-1">' . (is_object($emp) ? ( $emp -> name . ' [ ' . $emp -> emp_code . ' ]' ) : ERROR_VARS['notFound']) . '</p>';

                            echo '<span class="font-sm text-primary d-block">Other Compliancer: </span>';

                            if( is_array(explode(',', $cAssesData -> multi_compliance_ids)) && 
                                sizeof(explode(',',$cAssesData -> multi_compliance_ids)) > 0)
                            {
                                $multiCompEmp = '';
                                
                                foreach(explode(',', $cAssesData -> multi_compliance_ids) as $bKey => $bMultiCom)
                                {
                                    $emp = array_key_exists($bMultiCom, $data['data']['db_employee_data']) ? $data['data']['db_employee_data'][ $bMultiCom ] : null;

                                    $multiCompEmp .= '<span class="d-inline-block">' . (is_object($emp) ? ( $emp -> name . ' [ ' . $emp -> emp_code . ' ]' ) : ERROR_VARS['notFound']) .'</span>, ';
                                }

                                echo substr(trim_str($multiCompEmp), 0, -1);
                            }
                            else
                                echo ERROR_VARS['notFound'];

                        echo '</td>';

                        echo '<td style="width:10%">' . $cAssesData -> audit_start_date.'</td>
                        <td style="width:10%">' . $cAssesData -> audit_end_date.'</td>
                    </tr>';
                }
            ?>
        </tbody>
        <?php 
        }
        elseif(isset($data['data']['assesment_data'])  && empty($data['data']['assesment_data']))
            echo '<div class="mt-2">' . 
             $data['noti']::getCustomAlertNoti('noDataFound') . '
            </div>';
    ?>
    </table>
</div>

<?php

echo '</div>' . "\n";

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>