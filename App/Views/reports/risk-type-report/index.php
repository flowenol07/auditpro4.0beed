<?php

echo '<div class="no-print mb-3">';
require_once('form.php');
echo '</div>';

if(isset($data['data']['risk_category_weigth_data'])):

    if(!empty($data['data']['risk_category_weigth_data']))
    {
        $fy = $data['data']['db_year_data'][ $data['data']['risk_category_weigth_data'][0] -> year_id ];

        echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data'], true, $fy);
    
?>

        <div class="table-responsive"> 
            <table id="DataTable" class="table table-bordered v-table mt-2 exportToExcelTable">
                <thead>
                    <tr class="bg-light-gray">
                        <th style="width:40%">Risk Category Type</th>
                        <th style="width:20%" class="text-center">Risk Weight</th>
                        <th style="width:20%" class="text-center">Risk Appetite Percent</th>
                        <th style="width:20%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php      
                        foreach($data['data']['risk_category_weigth_data'] as $cKey => $cRiskCategoryData)
                        {  
                            echo
                            '<tr>
                                <td style="width:40%">' . $data['data']['risk_category_data'][ $cRiskCategoryData -> risk_category_id ] . '</td>
                                <td style="width:20%" class="text-center">' . $cRiskCategoryData -> risk_weight .'</td>
                                <td style="width:20%" class="text-center">' . $cRiskCategoryData -> risk_appetite_percent.'</td>
                                <td style="width:20%" class="text-center">' . check_active_status($cRiskCategoryData -> is_active) .'</td>
                            </tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>

<?php 
        echo '</div>' . "\n";
    }
    else
    {
        echo '<div class="mt-2">' . 
        $data['noti']::getCustomAlertNoti('noDataFound') . '
        </div>';
    }

endif;

?>