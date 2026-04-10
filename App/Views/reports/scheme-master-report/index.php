<?php

if(!empty($data['data']['scheme_data'])):
    
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
                <th style="width:25%">Scheme Type</th>
                <th style="width:10%" class="text-center">Scheme Code</th>
                <th style="width:20%">Mapped Category</th>
                <th style="width:40%">Scheme Name</th>
                <th style="width:10%" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $val = 1;

                foreach($data['data']['scheme_data'] as $cKey => $cSchemeData)
                {   
                    echo
                    '<tr data-filter="value' . $val . '">
                        <td style="width:25%">' . $GLOBALS['schemeTypesArray'][$cSchemeData -> scheme_type_id] . '</td>
                        <td style="width:10%" class="text-center">' . $cSchemeData -> scheme_code .'</td>

                        <td style="width:20%">' . ((array_key_exists($cSchemeData -> category_id, $data['data']['db_category_data'])) ? $data['data']['db_category_data'][$cSchemeData -> category_id] : ERROR_VARS['notFound']) . '</td>

                        <td style="width:40%" class="employee_type_td">' . $cSchemeData -> name . '</td>
                        <td style="width:10%" class="text-center">' . check_active_status($cSchemeData -> is_active) .'</td>
                    </tr>';

                    $val++;
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