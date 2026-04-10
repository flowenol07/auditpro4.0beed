<?php

if(!empty($data['data']['menu_data'])):
    
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
                    <th style="width:20%" class="text-center">Section Code</th>
                    <th style="width:30%">Audit Section</th>
                    <th style="width:20%" class="text-center">Menu Id</th>
                    <th style="width:30%">Menu Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['menu_data'] as $cKey => $cMenuData)
                    {   
                        echo
                        '<tr>
                            <td style="width:20%" class="text-center">' . $cMenuData -> section_type_id . '</td>
                            <td style="width:30%">' . ((array_key_exists($cMenuData -> section_type_id, $data['data']['audit_section_data'])) ? $data['data']['audit_section_data'][$cMenuData -> section_type_id] -> name : ERROR_VARS['notFound']) . '</td>
                            <td style="width:20%" class="text-center">' . $cMenuData -> id . '</td>
                            <td style="width:30%">' . $cMenuData -> name . '</td>                    
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