<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(isset($data['data']['risk_matrix_data']) && !empty($data['data']['risk_matrix_data']))
{
    $fy = $data['data']['db_year_data'][$data['data']['risk_matrix_data'][0] -> year_id];

    echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data'], true, $fy);
?>
    <div class="table-responsive">
        <table id="exportToExcelTable" class="table table-bordered table-md v-table mt-2">
                <tr class="bg-light-gray">
                    <th>Risk Parameter</th>
                    <th width="">Business Risk</th>
                    <th class="text-center">Business Risk Score</th>
                    <th width="">Control Risk</th>
                    <th class="text-center">Control Risk Score</th>
                    <th width="">Residual Risk</th>
                </tr>

            <?php foreach($data['data']['risk_matrix_data'] as $dataKey => $dataRiskMatrix) { ?>
                <tr>
                <td>
                    <?php echo isset(RISK_PARAMETERS_ARRAY[$dataKey + 1]) ? RISK_PARAMETERS_ARRAY[$dataKey + 1]['title'] : ERROR_VARS['notFound']  ?>
                </td>

                <td>
                    <?php echo isset($data['data']['risk_matrix_data'][$dataKey] -> business_risk_app) && ($data['data']['risk_matrix_data'][$dataKey] -> business_risk_app == 1 )  ? 'Applicable' : ''; ?>
                </td>

                <td class="text-center">
                    <?php echo isset($data['data']['risk_matrix_data'][$dataKey] -> business_risk_score) ? $data['data']['risk_matrix_data'][$dataKey] -> business_risk_score : $dataKey ?>
                </td>

                <td>
                    <?php echo isset($data['data']['risk_matrix_data'][$dataKey] -> control_risk_app) && ($data['data']['risk_matrix_data'][$dataKey] -> control_risk_app == 1 )  ? 'Applicable' : ''; ?>
                </td>

                <td class="text-center">
                    <?php echo isset($data['data']['risk_matrix_data'][$dataKey] -> control_risk_score) ? $data['data']['risk_matrix_data'][$dataKey] -> control_risk_score : $dataKey ?>
                </td>

                <td>
                    <?php echo isset($data['data']['risk_matrix_data'][$dataKey] -> residual_risk_app) && ($data['data']['risk_matrix_data'][$dataKey] -> residual_risk_app == 1 )  ? 'Applicable' : ''; ?>
                </td>
            </tr>
            <?php } ?>
                <tr class="bg-light-gray">
                    <th class="text-center">Risk Parameter</th>
                    <th class="text-center">Business Risk Score</th>
                    <th class="text-center" colspan="2">Control Risk Score</th>
                    <th class="text-center" colspan="2">Total Score</th>
                </tr>
            <tbody id="risk_matrix">
                <tr>
                    <td class="text-center">HIGH RISK</td>
                    <td class="text-center">
                        <?php 
                            echo isset($data['data']['risk_matrix_data'][0] -> business_risk_score) ? $data['data']['risk_matrix_data'][0] -> business_risk_score : 0  
                        ?>
                    </td>
                    <td colspan="2">
                        <table class="table table-bordered" >
                            <tbody>    
                            <tr>
                                <td width="30">HIGH</td>
                                <td width="70" class="text-center">
                                    <?php 
                                        echo isset($data['data']['risk_matrix_data'][0] -> control_risk_score) ? $data['data']['risk_matrix_data'][0] -> control_risk_score : 0  
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="30">MEDIUM</td>
                                <td class="text-center">
                                    <?php 
                                        echo isset($data['data']['risk_matrix_data'][1] -> control_risk_score) ? $data['data']['risk_matrix_data'][1] -> control_risk_score : 0  
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>LOW</td>
                                <td class="text-center"><?php echo isset($data['data']['risk_matrix_data'][2] -> control_risk_score) ? $data['data']['risk_matrix_data'][2] -> control_risk_score : 0  ?></td>
                            </tr>
                            </tbody>
                        </table>    
                    </td>
                    <td colspan="2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][0] -> business_risk_score) && isset($data['data']['risk_matrix_data'][0] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][0] -> business_risk_score) + ($data['data']['risk_matrix_data'][0] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][0] -> business_risk_score) && isset($data['data']['risk_matrix_data'][1] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][0] -> business_risk_score) + ($data['data']['risk_matrix_data'][1] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="20" class="text-center">
                                    <?php
                                            echo (isset($data['data']['risk_matrix_data'][0] -> business_risk_score) && isset($data['data']['risk_matrix_data'][2] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][0] -> business_risk_score) + ($data['data']['risk_matrix_data'][2] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-center">MEDIUM RISK</td>
                    <td class="text-center">
                        <?php 
                            echo isset($data['data']['risk_matrix_data'][1] -> business_risk_score) ? $data['data']['risk_matrix_data'][1] -> business_risk_score : 0  
                        ?>
                    </td>
                    <td colspan="2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr></tr>
                                <tr>
                                    <td width="30">HIGH</td>
                                    <td width="70" class="text-center">
                                        <?php 
                                            echo isset($data['data']['risk_matrix_data'][0] -> control_risk_score) ? $data['data']['risk_matrix_data'][0] -> control_risk_score : 0  
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MEDIUM</td>
                                    <td class="text-center">
                                        <?php 
                                            echo isset($data['data']['risk_matrix_data'][1] -> control_risk_score) ? $data['data']['risk_matrix_data'][1] -> control_risk_score : 0  
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>LOW</td>
                                    <td class="text-center">
                                        <?php 
                                            echo isset($data['data']['risk_matrix_data'][2] -> control_risk_score) ? $data['data']['risk_matrix_data'][2] -> control_risk_score : 0  
                                        ?>
                                    </td>
                                </tr>
                                <tr>

                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td colspan="2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][1] -> business_risk_score) && isset($data['data']['risk_matrix_data'][0] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][1] -> business_risk_score) + ($data['data']['risk_matrix_data'][0] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][1] -> business_risk_score) && isset($data['data']['risk_matrix_data'][1] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][1] -> business_risk_score) + ($data['data']['risk_matrix_data'][1] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][1] -> business_risk_score) && isset($data['data']['risk_matrix_data'][2] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][1] -> business_risk_score) + ($data['data']['risk_matrix_data'][2] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-center">LOW RISK</td>
                    <td class="text-center">
                        <?php 
                            echo isset($data['data']['risk_matrix_data'][2] -> business_risk_score) ? $data['data']['risk_matrix_data'][2] -> business_risk_score : 0  
                        ?>
                    </td>
                    <td colspan="2">
                        <table class="table table-bordered" >
                            <tbody >
                                <tr></tr>
                                <tr>
                                    <td width="30">HIGH</td>
                                    <td width="70" class="text-center">
                                        <?php 
                                            echo isset($data['data']['risk_matrix_data'][0] -> control_risk_score) ? $data['data']['risk_matrix_data'][0] -> control_risk_score : 0  
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>MEDIUM</td>
                                    <td class="text-center">
                                        <?php 
                                            echo isset($data['data']['risk_matrix_data'][1] -> control_risk_score) ? $data['data']['risk_matrix_data'][1] -> control_risk_score : 0  
                                        ?>
                                    </td>
                                </tr>
                                <tr></tr>
                            </tbody>
                        </table>
                    </td>
                    <td colspan="2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][2] -> business_risk_score) && isset($data['data']['risk_matrix_data'][0] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][2] -> business_risk_score) + ($data['data']['risk_matrix_data'][0] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <?php
                                            echo (isset($data['data']['risk_matrix_data'][2] -> business_risk_score) && isset($data['data']['risk_matrix_data'][1] -> control_risk_score)) ? (($data['data']['risk_matrix_data'][2] -> business_risk_score) + ($data['data']['risk_matrix_data'][1] -> control_risk_score)) : 0;
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-center">NO RISK</td>
                    <td class="text-center">0</td>
                    <td class="text-center" colspan="2">0</td>
                    <td class="text-center" colspan="2">0</td>
                </tr>
            </tbody>
        </table>
    </div>

<?php

    echo '</div>' . "\n";
}
?>