<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "question-set-master", "action" => $data['me'] -> url ]);

    function riskParameterTable($val, $name, $data, $disabled, $nameId, $value, $dbVal)
    {    
        $riskParameter = (isset($data['data']['db_data'][$dbVal] -> risk_parameter) ? ($data['data']['db_data'][$dbVal] -> risk_parameter) : 0);

        echo '<tr>
                <td>
                    <div class="col-md-12">';

                            $markup = FormElements::generateSelect([
                                "id" => "risk_parameter", "name" => "risk_parameter_$nameId", 
                                "default" => [$val, $name],
                                "options" =>"",
                                "selected" => $name,
                                "disabled" => false,
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'risk_parameter_' . $nameId);
                    
                echo '</div>
                </td>

                <td>
                    <div class="col-md-12">';
                        if($riskParameter == $val)
                        {
                            if(isset($data['data']['db_data'][$dbVal] -> business_risk_app))
                            {
                                if($data['request'] -> input('business_risk_app', $data['data']['db_data'][$dbVal] -> business_risk_app) == 0)
                                    $checked = false;
                                else
                                    $checked = true;
                            }
                            else
                            {
                                if(empty($data['data']['db_data'] ))
                                    $checked = false;
                                else
                                    $checked = true;
                            }
                        }
                        else
                            $checked = false;

                            $markup = FormElements::generateCheckboxOrRadio([
                                "id" => "business_risk_app", "name" => "business_risk_app_$nameId", "appendClass" => "business_risk_app ms-3",
                                "text" => '',
                                "checked" => ($data['request'] -> input('business_risk_app_' . $nameId)) ?? $checked,
                                "value" => 1,
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'business_risk_app_' . $nameId);
                    
                echo '</div>
                </td>

                <td>
                    <div class="col-md-12">';
                        if($riskParameter == $val)
                        {
                            $businessRiskScore = $data['request'] -> input('business_risk_score_' . $nameId, $data['data']['db_data'][$dbVal] -> business_risk_score);
                        }
                        else    
                            $businessRiskScore = ($data['request'] -> input('business_risk_score_' . $nameId)) ?? '';

                            $markup = FormElements::generateInput([
                                "id" => "business_risk_score", "name" => "business_risk_score_$nameId",
                                "type" => "text", 
                                "value" => ($value == 1) ? 0 : $businessRiskScore, 
                                "placeholder" => "Business Risk Score",
                                "disabled" => $disabled,
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'business_risk_score_' . $nameId);
                    
                    echo '</div>
                </td>

                <td>
                    <div class="col-md-12">';
                        if($riskParameter == $val)
                        {
                            if(isset($data['data']['db_data'][$dbVal] -> control_risk_app))
                            {
                                if($data['request'] -> input('control_risk_app', $data['data']['db_data'][$dbVal] -> control_risk_app) == 0)
                                    $checked = false;
                                else
                                    $checked = true;
                            }
                            else
                            {
                                if(empty($data['data']['db_data'] ))
                                    $checked = false;
                                else
                                    $checked = true;
                            }
                        }
                        else
                            $checked = false;

                            $markup = FormElements::generateCheckboxOrRadio([
                                "id" => "control_risk_app", "name" => "control_risk_app_$nameId", "appendClass" => "control_risk_app ms-3",
                                "text" => '',
                                "checked" => ($data['request'] -> input('control_risk_app_' . $nameId)) ?? $checked,
                                "value" => 1,
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'control_risk_app_' . $nameId);
                    
                    echo '</div>
                </td>

                <td>
                    <div class="col-md-12">';
                        if($riskParameter == $val)
                            $controlRiskScore = (isset($data['data']['db_data'][$dbVal] -> control_risk_score) ? ($data['data']['db_data'][$dbVal] -> control_risk_score) : ($data['request'] -> input('control_risk_score_' . $nameId)));
                        else    
                            $controlRiskScore = ($data['request'] -> input('control_risk_score_' . $nameId)) ?? '';

                            $markup = FormElements::generateInput([
                                "id" => "control_risk_score", "name" => "control_risk_score_$nameId", 
                                "type" => "text", 
                                "value" => ($value == 1) ? 0 : $controlRiskScore,                        
                                "placeholder" => "Control Risk Score",
                                "disabled" => $disabled,
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'control_risk_score_'. $nameId);
                    
                echo '</td>

                <td>';  
                    if($riskParameter == $val)
                    {
                        if(isset($data['data']['db_data'][$dbVal] -> residual_risk_app))
                        {
                            if($data['request'] -> input('residual_risk_app', $data['data']['db_data'][$dbVal] -> residual_risk_app) == 0)
                                $checked = false;
                            else
                                $checked = true;
                        }
                        else
                        {
                            if(empty($data['data']['db_data'] ))
                                $checked = false;
                            else
                                $checked = true;
                        }
                    }
                    else
                        $checked = false;

                        $markup = FormElements::generateCheckboxOrRadio([
                            "id" => "residual_risk_app", "name" => "residual_risk_app_$nameId", "appendClass" => "ms-3",
                            "text" => '',
                            "checked" => ($data['request'] -> input('residual_risk_app_' . $nameId)) ?? $checked,
                            "value" => 1,
                        ]);

                        echo FormElements::generateFormGroup($markup, $data, 'residual_risk_app_' .$nameId);
                    
            echo '</td>
        </tr>';
    }

?>
    <table class="table kp-table v-table">
        <thead>
            <tr>
                <th>Risk Parameter</th>
                <th>Business Risk</th>
                <th>Business Risk Score</th>
                <th>Control Risk</th>
                <th>Control Risk Score</th>
                <th>Residual Risk</th>
            </tr>
        </thead>

        <?php          
            riskParameterTable(1, 'HIGH RISK', $data, false, 1, 0, 1);
            riskParameterTable(2, 'MEDIUM RISK', $data, false, 2, 0, 2);
            riskParameterTable(3, 'LOW RISK', $data, false, 3, 0, 3);
            riskParameterTable(4, 'NO RISK', $data, true, 4, 1, 4);

        ?>            
    </table>
<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Risk Matrix'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Risk Matrix';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose();

?>

    <table class="table kp-table v-table mt-3">
        <thead>
            <tr>
                <th style="text-align:center">Risk Parameter</th>
                <th style="text-align:center">Business Risk Score</th>
                <th style="text-align:center">Control Risk Score</th>
                <th style="text-align:center">Total Score</th>
            </tr>
        </thead>
        <tbody>
        <?php 
            $riskArray = [1 => 'HIGH', 2 => 'MEDIUM', 3 => 'LOW'];

            for($i = 1; $i < 4; $i++ ) { ?>
                <tr>
                    <td align="center"><?php echo $riskArray[$i] ?> RISK</td>
                    <td align="center">
                    <?= isset($data['data']['db_data'][$i] -> business_risk_score) ? $data['data']['db_data'][$i] -> business_risk_score : 0 ?>
                    </td>
                    <td>
                        <table class="table table-bordered mb-0" >
                            <tbody>
                            <?php for($j = 1; $j < 4 ; $j++) { ?>
                                <tr>
                                    <td width="30"><?php echo $riskArray[$j] ?> </td>
                                    <td width="70" align="center">
                                        <?=isset($data['data']['db_data'][$j] -> control_risk_score) ? $data['data']['db_data'][$j] -> control_risk_score : 0  ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table class="table table-bordered mb-0">
                            <tbody>
                            <?php for($k = 1; $k < 4; $k++) { ?>
                                <tr>
                                    <td align="center">
                                    <?= (isset($data['data']['db_data'][$i] -> business_risk_score) && isset($data['data']['db_data'][$k] -> control_risk_score)) ? (($data['data']['db_data'][$i] -> business_risk_score) + ($data['data']['db_data'][$k] -> control_risk_score)) : 0 ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php } ?>
        
                <tr>
                    <td style="text-align:center">NO RISK</td>
                    <td style="text-align:center">0</td>
                    <td style="text-align:center">0</td>
                    <td style="text-align:center">0</td>
                </tr>
        </tbody>
    </table>