<?php
use Core\FormElements;

if(!empty($GLOBALS['userTypesArray'])):
    
echo '<div class="no-print mb-3">';
    generate_report_buttons(['print', 'excel']);
echo '</div>';

    // generate header 
    generate_report_header($data['data']);
?>
    <div class="table-responsive">
        <table id="exportToExcelTable" class="table table-bordered v-table mt-3">
            <thead>
                <tr>
                    <th>User Type Code</th>
                    <th>User Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($GLOBALS['userTypesArray'] as $cKey => $cYearData)
                    {   
                        echo'
                        <tr>
                            <td>' . $cKey . '</td>
                            <td>' . $cYearData .'</td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>
<?php
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');;
endif;
?>