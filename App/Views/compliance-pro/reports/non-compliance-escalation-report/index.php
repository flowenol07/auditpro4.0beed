<?php

if(!empty($data['data']['db_data'])):

    echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

    echo '<div class="hide-this mb-3">' . "\n";
        $btnArray = array('print');
        generate_report_buttons($btnArray);
    echo '</div>' . "\n";

?>
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th>Issue ID</th>
                    <th>Reference</th>
                    <th>Compliance Area</th>
                    <th>Non-compliance Date</th>
                    <th>Responsible Department</th>
                    <th>Priority</th>
                    <th>Escalation Date</th>
                    <th>Escalated To</th>
                    <th>Resolution Status</th>
                    <th>Resolution Deadline</th>
                </tr>
            </thead>
            <tbody>
                <?php

                    $srNo = 1;

                    foreach($data['data']['db_data'] as $cAuthData)
                    {   
                        echo '<tr>
                            <td>' . $cAuthData[0] . '</td>
                            <td>' . $cAuthData[1] . '</td>
                            <td>' . $cAuthData[2] . '</td>
                            <td>' . $cAuthData[3] . '</td>
                            <td>' . $cAuthData[4] . '</td>
                            <td>' . $cAuthData[5] . '</td>
                            <td>' . $cAuthData[6] . '</td>
                            <td>' . $cAuthData[7] . '</td>
                            <td>' . $cAuthData[8] . '</td>
                            <td>' . $cAuthData[9] . '</td>
                        </tr>';

                        $srNo++;
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