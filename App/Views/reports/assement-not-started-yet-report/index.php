<?php
echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(isset($data['data']['not_started_branches']))
{
    if(!empty($data['data']['not_started_branches']) && 
        sizeof($data['data']['not_started_branches']) > 0 ):
        
    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);

    ?>
    
        <div class="table-responsive">
            <table class="table table-bordered v-table mt-2 exportToExcelTable">
                <thead>
                    <tr class="bg-light-gray">
                        <th style="width:10%" class="text-center">Sr. No</th>
                        <th style="width:25%">Audit Unit</th>
                        <th style="width:25%">Assesment Period</th>
                        <th style="width:10%" class="text-center">Frequency</th>
                        <th style="width:30%">Audit Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $i = 0;
                        foreach($this -> data['not_started_branches'] as $cKey => $cAssesData)
                        {
                            $i++;

                            echo
                            '<tr>
                                <td style="width:10%" class="text-center">' . $i . '</td>

                                <td style="width:25%">'. $this -> data['audit_unit_data'][$cAssesData['id']]-> combined_name .'</td>

                                <td style="width:25%">'. $cAssesData['assesment_period']  . '</td>
                                <td style="width:10%" class="text-center">'. $cAssesData['frequency']  . '</td>
                                <td style="width:30%">Assement Not Started Yet</td>
                            </tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php else: ?>
        <div class="mt-2">
            <?= $data['noti']::getCustomAlertNoti('noDataFound'); ?>
        </div>
    <?php endif; 
    
}

?>