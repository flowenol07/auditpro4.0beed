<?php

use Core\FormElements;

if(!empty($data['data']['broader_area_data'])):

echo '<div class="no-print mb-3">';
    generate_report_buttons(['print', 'excel']);
echo '</div>';

echo '<div id="printContainer">' . "\n";
generate_report_header($data['data']);

?>
    <div class="table-responsive">
        <table id="employeeDataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:20%" class="text-center">Section Code</th>
                    <th style="width:80%">Section Name</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['broader_area_data'] as $cKey => $cBroaderAreaData)
                    {   
                        echo '<tr>
                            <td style="width:20%" class="text-center">' . $cBroaderAreaData -> id . '</td>
                            <td style="width:80%" class="section_td">' . $cBroaderAreaData -> name . '</td>                            
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