<?php
use Core\FormElements;

if(!empty($data['data']['year_data'])):

    // generate button
    echo '<div class="no-print mb-3">' . "\n";
        generate_report_buttons(['print','excel']);
    echo '</div>' . "\n";
    
    echo '<div id="printContainer">' . "\n";

    // generate header 
    generate_report_header($data['data']);   
?>
    <div class="table-responsive">
        <table id="exportToExcelTable" class="table table-bordered v-table mt-3">
            <thead>
                <tr class="bg-light-gray">
                    <th class="text-center">Sr. No.</th>
                    <th class="text-center">Financial Year</th>
                    <th class="text-center">Financial Year Created Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 1;
                    foreach($data['data']['year_data'] as $cKey => $cYearData)
                    {   
                        echo'
                        <tr>
                            <td class="text-center">' . $i . '</td>
                            <td class="text-center">' . $data['data']['db_year_data'][$cYearData -> id] .'</td>
                            <td class="text-center">' . date( 'Y-m-d', strtotime($cYearData -> created_at)) .'</td>
                        </tr>';

                        $i++;
                    }
                ?>
            </tbody>
        </table>
    </div>

    </div>
<?php
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');;
endif;
?>
</div>