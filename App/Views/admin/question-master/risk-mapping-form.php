<?php

use Core\FormElements;

require_once APP_VIEWS . '/admin/question-header-master/single-set-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "question-risk-mapping"]);

?>
    <div class="row">

        <div class="col-md-12">
            <?php

            // question
            echo '<div class="border p-3 mb-3 bg-light-gray">' . "\n";
                echo FormElements::generateLabel('', 'Question');
                echo '<p class="mb-2">'. $data['data']['db_data'] -> question .'</p>' . "\n";
                echo '<p class="text-danger mb-0"><span class="font-medium">Answer Type:</span> '. $data['data']['db_data'] -> option_id_name .'</p>' . "\n";
            echo '</div>' . "\n";

            ?>
        </div>

        <div class="col-md-12">

        <?php 
        
        //Risk Type
        $markup = FormElements::generateLabel('risk_type', 'Risk Type');

        if($data['data']['db_data'] -> option_id == 2): 

            if(is_array($data['data']['yes_no_data']) && sizeof($data['data']['yes_no_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "risk_type", "name" => "risk_type", 
                    "default" => ["", "Please select risk type"],
                    "options" => $data['data']['yes_no_data'],
                    "selected" => $data['request'] -> input('risk_type')
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

        else:

            $markup .= FormElements::generateInput([
                "id" => "risk_type", "name" => "risk_type", 
                "type" => "text", "value" => $data['request'] -> input('risk_type'), 
                "placeholder" => "Risk Type"
            ]);

        endif; 

        echo FormElements::generateFormGroup($markup, $data, 'risk_type'); ?>
        
        </div>
        

        <div class="col-md-6">
            <?php

                //business_risk
                $markup = FormElements::generateLabel('business_risk', 'Business Risk');

                if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "business_risk", "name" => "business_risk", 
                        "default" => ["", "Please select business risk"],
                        "options" => RISK_PARAMETERS_ARRAY, "options_db" => ['type' => 'arr', 'val' => 'title' ],
                        "selected" => $data['request'] -> input('business_risk')
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'business_risk');
            ?>
        </div>
        
        <div class="col-md-6">
            <?php

                //control_risk
                $markup = FormElements::generateLabel('residual_control_riskrisk_id', 'Control Risk');

                if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "control_risk", "name" => "control_risk", 
                        "default" => ["", "Please select control risk"],
                        "options" => RISK_PARAMETERS_ARRAY, "options_db" => ['type' => 'arr', 'val' => 'title' ],
                        "selected" => $data['request'] -> input('control_risk')
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'control_risk');
            ?>
        </div>
        
    </div>

    <?php

        if( is_array($data['data']['db_data'] -> parametersArr) && 
            sizeof($data['data']['db_data'] -> parametersArr) == ENV_CONFIG['question_parameter_limit'] )
        {
            echo $data['noti']::getCustomAlertNoti('quesOptionLimitError');
        }    
        else
        {
            $btnArray = [ 'name' => 'submit', 'value' => 'Add Option'];     
            echo FormElements::generateSubmitButton('add', $btnArray );
            echo '<div class="w-100 mb-2"></div>' . "\n";
        }
        
        echo FormElements::generateLabel('risk_type', 'Question Answers');
        echo '<div class="w-100 mb-2"></div>' . "\n";
    
        $parametersArray = null;

        if(is_array($data['data']['db_data'] -> parametersArr) && sizeof( $data['data']['db_data'] -> parametersArr ) > 0): ?>
            
            <div class="table-responsive">
                <table class="table table-bordered v-table">
                    <tr class="bg-light-gray">
                        <th>Sr. No.</th>
                        <th>Risk Type</th>
                        <th>Business Risk</th>
                        <th>Control Risk</th>
                        <th>Action</th>
                    </tr>

                    <?php foreach($data['data']['db_data'] -> parametersArr as $cIndex => $cParamObj): ?>
                        <tr>
                            <td><?= ($cIndex + 1) ?></td>
                            <td><?= string_operations($cParamObj -> rt, 'upper') ?></td>
                            <td><?= string_operations( (is_array(RISK_PARAMETERS_ARRAY) && array_key_exists($cParamObj -> br, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cParamObj -> br ]['title'] : ERROR_VARS['notFound']), 'upper' ) ?></td>
                            <td><?= string_operations( (is_array(RISK_PARAMETERS_ARRAY) && array_key_exists($cParamObj -> cr, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cParamObj -> cr ]['title'] : ERROR_VARS['notFound']), 'upper' ) ?></td>

                            <td>
                                <?php if($data['data']['disable_action']): ?>
                                    <?php echo generate_link_button('delete', ['href' => (URL . explode('url=', $_SERVER['QUERY_STRING'])[1]) . '?rmv=' . encrypt_ex_data($cIndex), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]); ?>
                                <?php else: echo '-'; endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>

        <?php
        else:
            echo $data['noti']::getCustomAlertNoti('noDataFound');
        endif;
    
    
    ?>

<?php 

echo FormElements::generateFormClose(); 

?>