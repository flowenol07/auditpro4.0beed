<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

// print_r($data['data']['shceme_data']);

if(isset($data['data']['details_of_account_data']))
{
    if(!empty($data['data']['details_of_account_data']) && sizeof($data['data']['details_of_account_data']) > 0):

        echo '<div id="printContainer">' . "\n";

        // generate header function
        echo generate_report_header($data['data']); ?>

        <div class="table-responsive">
            <table class="table table-bordered v-table mt-2">
                <thead>
                    <tr class="bg-light-gray">
                        <th style="width:10%">BRANCH</th>
                        <th style="width:10%">SCHEME</th>
                        <th style="width:15%">ACCOUNT NO.</th>
                        <th style="width:15%">NAME</th>
                        <?php if($data['request'] -> input('scheme_type') == 2) { ?> 
                        <th style="width:10%">SANCTION LIMIT</th> <?php } ?>
                        <th style="width:10%">ACCOUNT OPEN DATE</th>
                        <?php if($data['request'] -> input('scheme_type') == 2){ ?>
                        <th style="width:10%">BROADER AREA</th> <?php } 
                        elseif($data['request'] -> input('scheme_type') == 1){ ?> 
                        <th style="width:10%">DISCRIPENCY</th> <?php } ?>
                        <th style="width:10%">RISK TYPE</th>
                        <th style="width:10%" class="text-center">BR SCORE</th>
                        <th style="width:10%" class="text-center">CR SCORE</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        foreach($data['data']['details_of_account_data'] as $cKey => $cAssesData)
                        {   
                            echo
                            '<tr>
                                <td style="width:10%">' . (isset($data['data']['audit_unit_data'][$cAssesData -> branch_id]) ? $data['data']['audit_unit_data'][$cAssesData -> branch_id] -> combined_name : ERROR_VARS['notFound']) . '</td> 
                                <td style="width:10%">';

                                if( is_array($data['data']['scheme_data']) && 
                                    array_key_exists($cAssesData -> scheme_id, $data['data']['scheme_data']))
                                    echo $data['data']['scheme_data'][ $cAssesData -> scheme_id ] -> name . '( '. $data['data']['scheme_data'][ $cAssesData -> scheme_id ] -> scheme_code . ' )';
                                else
                                    echo ERROR_VARS['notFound'];

                                echo '</td>                
                                <td style="width:15%">' . $cAssesData -> account_no . '</td>                
                                <td style="width:15%">' . $cAssesData -> account_holder_name . '</td>';

                                if($data['request'] -> input('scheme_type') == 2)             
                                    echo '<td style="width:10%">' . $cAssesData -> sanction_amount . '</td>'; 

                                echo '<td style="width:10%">' . $cAssesData -> account_opening_date . '</td>
                                <td style="width:10%">' . $cAssesData -> broader_area . '</td>                
                                <td style="width:10%">' . $cAssesData -> risk_type . '</td>            
                                <td style="width:10%" class="text-center">' . $cAssesData -> business_risk_total . '</td>                
                                <td style="width:10%" class="text-center">' . $cAssesData -> control_risk_total . '</td>                
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
<?php

endif; 
    
}

?>