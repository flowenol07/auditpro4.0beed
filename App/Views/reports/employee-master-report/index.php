<?php

if(!empty($data['data']['employee_data'])):

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

?>

<div class="table-responsive">
    <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
        <thead>
            <tr class="bg-light-gray">
                <th style="width:20%" class="text-center">Employee Code</th>
                <th style="width:50%">Employee Name</th>
                <th style="width:20%" class="text-center">Employee Type</th>
                <th style="width:10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach($data['data']['employee_data'] as $cKey => $cEmpData)
                {   
                    echo
                    '<tr>
                        <td style="width:20%" class="text-center">' . $cEmpData -> emp_code . '</td>
                        <td style="width:50%">' . (($cEmpData -> gender != '') ? (string_operations($cEmpData -> gender, 'upper') . '.  ') : '') . $cEmpData -> name .'</td>
                        <td style="width:20%" class="text-center" class="employee_type_td">' . $GLOBALS['userTypesArray'][$cEmpData -> user_type_id] . '</td>
                        <td style="width:10%" class="text-center">' . check_active_status($cEmpData -> is_active) .'</td>
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