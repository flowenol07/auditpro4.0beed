<?php

if(!empty($data['data']['audit_section_data'])):

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
                    <th style="width:70%">Section Name</th>
                    <th style="width:10%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['audit_section_data'] as $cKey => $cSectionData)
                    {   
                        echo
                        '<tr>
                            <td style="width:20%" class="text-center">' . $cSectionData -> id . '</td>
                            <td style="width:70%" class="section_td">' . $cSectionData -> name . '</td>
                            <td style="width:10%" class="text-center">' . check_active_status($cSectionData -> is_active) .'</td>
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