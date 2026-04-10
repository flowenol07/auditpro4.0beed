<?php
if(!empty($data['data']['header_data'])):
    
echo '<div class="no-print mb-3">';
    require_once('form.php');
 echo '</div>';
    
 echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data']);
?>
    <div class="table-responsive">
        <table class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:10%" class>Set Id</th>
                    <th style="width:40%">Set Name</th>
                    <th style="width:40%">Header Name</th>
                    <th style="width:10%">Set Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['header_data'] as $cKey => $cHeaderData)
                    {   
                        echo
                        '<tr>
                            <td style="width:10%">' . $cHeaderData -> question_set_id . '</td>

                            <td style="width:40%">' . (isset($data['data']['set_select_data'][$cHeaderData -> question_set_id]) ? $data['data']['set_select_data'][$cHeaderData -> question_set_id] : ERROR_VARS['notFound']) . '</td>

                            <td style="width:40%">' . $cHeaderData -> name . '</td>
                            
                            <td style="width:10%">' . (isset($data['data']['set_select_data'][$cHeaderData -> question_set_id]) ? $GLOBALS['setTypesArray'][$data['data']['set_type_data'][$cHeaderData -> question_set_id]] : ERROR_VARS['notFound']) . '</td>
                            
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