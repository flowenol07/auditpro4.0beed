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
                    <th width="10%">Report Date</th>
                    <th>Authority</th>
                    <th>Entity Name</th>
                    <th>Compliance Evaluation</th>
                    <th>Key Compliance Areas</th>
                    <th>Total Requirements</th>
                    <th>Complied Requirements</th>
                    <th>Pending Actions</th>
                    <th>Escalated Issues</th>
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
                            <td class="text-center">' . $cAuthData[5] . '</td>
                            <td class="text-center">' . $cAuthData[6] . '</td>
                            <td class="text-center">' . $cAuthData[7] . '</td>
                            <td class="text-center">' . $cAuthData[8] . '</td>
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