<?php

use Core\FormElements;

require_once APP_VIEWS . '/admin/question-header-master/single-set-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "question-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">

        <?php if($data['data']['disable_action']): ?>

        <div class="col-md-12">
            <?php

                //header_id
                $markup = FormElements::generateLabel('header_id', 'Header
                ');

                if(is_array($data['data']['db_headers']) && sizeof($data['data']['db_headers']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "header_id", "name" => "header_id", "appendClass" => "chosen-select select2search",
                        "default" => ["", "Please select question header"],
                        "options" => $data['data']['db_headers'],
                        "selected" => $data['request'] -> input('header_id', $data['data']['db_data'] -> header_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'header_id');

            ?>
        </div>

        <?php endif; ?>

        <div class="col-md-12">
            <?php
                //question
                $markup = FormElements::generateLabel('question', 'Question Description');

                $markup .= FormElements::generateTextArea([
                    "id" => "question", "name" => "question", 
                    "type" => "text", "value" => $data['request'] -> input('question', $data['data']['db_data'] -> question), 
                    "placeholder" => "Question Description"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'question');
            ?>
        </div>

        <?php if($data['data']['disable_action']): ?>

        <div class="col-md-12">
            <?php

                //risk_category_id
                $markup = FormElements::generateLabel('risk_category_id', 'Business Risk Category');

                if(is_array($data['data']['db_risk_category']) && sizeof($data['data']['db_risk_category']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "risk_category_id", "name" => "risk_category_id", 
                        "default" => ["", "Please select business risk category"],
                        "options" => $data['data']['db_risk_category'],
                        "selected" => $data['request'] -> input('risk_category_id', $data['data']['db_data'] -> risk_category_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'risk_category_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //header master
                $markup = FormElements::generateLabel('control_risk_id', 'Control Risk Category');

                if(is_array($data['data']['db_risk_control']) && sizeof($data['data']['db_risk_control']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "control_risk_id", "name" => "control_risk_id", 
                        "default" => ["", "Please select control risk category"],
                        "options" => $data['data']['db_risk_control'], "options_db" => [ 'type' => 'obj', 'val' => 'name' ],
                        "selected" => $data['request'] -> input('control_risk_id', $data['data']['db_data'] -> header_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'control_risk_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //key_aspect_id
                $markup = FormElements::generateLabel('key_aspect_id', 'Key Aspect');

                $markup .= FormElements::generateSelect([
                    "id" => "key_aspect_id", "name" => "key_aspect_id", 
                    "default" => ["", "Please select key aspect"],
                    "options" => $data['data']['db_key_aspect'], "options_db" => [ 'type' => 'obj', 'val' => 'name' ],                    
                    "selected" => $data['request'] -> input('key_aspect_id', $data['data']['db_data'] -> key_aspect_id)
                ]);                

                echo FormElements::generateFormGroup($markup, $data, 'key_aspect_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //residual_risk_id
                $markup = FormElements::generateLabel('residual_risk_id', 'Residual Risk');

                if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "residual_risk_id", "name" => "residual_risk_id", 
                        "default" => ["", "Please select residual risk"],
                        "options" => RISK_PARAMETERS_ARRAY, "options_db" => ['type' => 'arr', 'val' => 'title' ],
                        "selected" => $data['request'] -> input('residual_risk_id', $data['data']['db_data'] -> residual_risk_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'residual_risk_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //applicable_id
                $markup = FormElements::generateLabel('applicable_id', 'Applicable To');

                if(is_array($GLOBALS['applicableToArray']) && sizeof($GLOBALS['applicableToArray']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "applicable_id", "name" => "applicable_id", 
                        "default" => ["", "Please select applicable to"],
                        "options" => $GLOBALS['applicableToArray'],
                        "selected" => $data['request'] -> input('applicable_id', $data['data']['db_data'] -> applicable_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'applicable_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //question_type_id
                $markup = FormElements::generateLabel('question_type_id', 'Question Type');

                if(is_array($GLOBALS['questionTypeArray']) && sizeof($GLOBALS['questionTypeArray']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "question_type_id", "name" => "question_type_id", 
                        "default" => ["", "Please select question type"],
                        "options" => $GLOBALS['questionTypeArray'],
                        "selected" => $data['request'] -> input('question_type_id', $data['data']['db_data'] -> question_type_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'question_type_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //area_of_audit_id
                $markup = FormElements::generateLabel('area_of_audit_id', 'Broader Area of Audit Non-Compliance');

                if(is_array($data['data']['db_area_of_audit']) && sizeof($data['data']['db_area_of_audit']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "area_of_audit_id", "name" => "area_of_audit_id", 
                        "default" => ["", "Please select broader area of audit non-compliance"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['db_area_of_audit'], "options_db" => ['type' => 'obj', 'val' => 'name' ],
                        "selected" => $data['request'] -> input('area_of_audit_id', $data['data']['db_data'] -> area_of_audit_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'area_of_audit_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php

                //header master
                $markup = FormElements::generateLabel('option_id', 'Input Method');

                if(is_array($GLOBALS['questionInputMethodArray']) && sizeof($GLOBALS['questionInputMethodArray']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "option_id", "name" => "option_id", 
                        "default" => ["", "Please select input method"],
                        "options" => $GLOBALS['questionInputMethodArray'],
                        "selected" => $data['request'] -> input('option_id', $data['data']['db_data'] -> option_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'option_id');
            ?>
        </div>

        <div id="annexure_id_container" class="col-md-12 d-none">
            <?php

                //annexure_id
                $markup = FormElements::generateLabel('annexure_id', 'Annexure');

                if(is_array($data['data']['db_annexures']) && sizeof($data['data']['db_annexures']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "annexure_id", "name" => "annexure_id", 
                        "default" => ["", "Please select annexure"],
                        "options" => $data['data']['db_annexures'], "options_db" => ['type' => 'obj', 'val' => 'name'],
                        "selected" => $data['request'] -> input('annexure_id', $data['data']['db_data'] -> annexure_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'annexure_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //show_instances
                $markup = FormElements::generateLabel('show_instances', 'Show Instances');

                $markup .= FormElements::generateInput([
                    "id" => "show_instances", "name" => "show_instances", 
                    "type" => "text", "value" => $data['request'] -> input('show_instances', $data['data']['db_data'] -> show_instances), 
                    "placeholder" => "Show Instances"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'show_instances');
            ?>
        </div>

        <div id="subset_multi_id_container" class="col-md-12 d-none">
            <?php 

            echo FormElements::generateLabel('', 'Subset List');
            
            if(is_array($data['data']['db_sub_sets']) && sizeof($data['data']['db_sub_sets']) > 0): 

                echo '<table class="table table-bordered">
                    <tr>';
            
                    $i = 0;
                    $temp_subset_multi_id = [];

                    //check for update
                    if(!empty($data['data']['db_data'] -> subset_multi_id))
                        $temp_subset_multi_id = explode(',', $data['data']['db_data'] -> subset_multi_id);

                    foreach($data['data']['db_sub_sets'] as $cIndex => $cData)
                    {
                        $i++;
                        $checked = false;
                    
                        if( is_array($data['request'] -> input('subset_multi_id', $temp_subset_multi_id)) && 
                            in_array($cIndex, $data['request'] -> input('subset_multi_id', $temp_subset_multi_id)))
                            $checked = true;
                    
                        echo '<td>';
                    
                            echo $markup = FormElements::generateCheckboxOrRadio([
                                "id" => "subset_multi_id", "name" => "subset_multi_id[]",
                                "text" => $cData -> name, "checked" => $checked, "value" => $cIndex,
                            ]);
                    
                        echo '</td>';
                    
                        if($i == 2) 
                        {
                            echo '</tr><tr>';
                            $i = 0;
                        }
                    }

                    echo '</tr>
                </table>';

            echo $data['noti']::getInputNoti($data['request'], 'subset_multi_id_err');
            
            else: 
                echo $data['noti']::getCustomAlertNoti('noDataFound');            
            endif; 
            
            ?>
        </div> 
        
        <div class="col-md-6">
            <?php
                // audit_ev_upload
                $markup = FormElements::generateCheckboxOrRadio([
                    'type' => 'checkbox', 'id' => 'audit_ev_upload', 'name' => 'audit_ev_upload', 
                    'value' => '1', 'text' => 'Auditor Evidence Upload',
                    'checked' => (($data['request'] -> input('audit_ev_upload', $data['data']['db_data'] -> audit_ev_upload) == 1 ) ? true : false), 
                    'customLabelClass' => 'font-medium text-primary'
                ]);
            
                echo FormElements::generateFormGroup($markup);
            ?>
        </div>

        <div class="col-md-6">
            <?php
                // compliance_ev_upload
                $markup = FormElements::generateCheckboxOrRadio([
                    'type' => 'checkbox', 'id' => 'compliance_ev_upload', 'name' => 'compliance_ev_upload', 
                    'value' => '1', 'text' => 'Compliance Evidence Upload',
                    'checked' => (($data['request'] -> input('compliance_ev_upload', $data['data']['db_data'] -> compliance_ev_upload) == 1 ) ? true : false), 
                    'customLabelClass' => 'font-medium text-primary'
                ]);
            
                echo FormElements::generateFormGroup($markup);
            ?>
        </div>

        <?php endif; ?>
        
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Question'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Question';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 


$data['data']['inline_js'] = "\n" . '
<script>

$(document).ready(function(){
    
    function change_key_aspect(selectedVal) {
        let key_aspect = $("#key_aspect_id");
        let errSpan = "<span id=\'errSpan\' class=\'d-block text-danger font-sm\'>Error: Data not found!</span>";

        if(selectedVal != "")
        {
            $.ajax({
                type: "GET",
                url: "'. ($data['siteUrls']::getUrl( 'riskControlKeyAspect' ) . '/ajax-risk-control-key-aspect') .'",
                data: "cr_id=" + selectedVal,
                success: function(res){

                    $( key_aspect.parent() ).find("#errSpan").remove();
                    
                    try 
                    {
                        res = JSON.parse(res);

                        if(res.length > 0)
                        {                        
                            key_aspect.find("option:gt(0)").remove();

                            $.each(res, function(index, item) {

                                key_aspect.append($("<option>", {
                                    value: item.id, text: item.name
                                }));
                            });
                        }
                        else
                            $( key_aspect.parent() ).last().append(errSpan);
                    }
                    catch (e) {
                        $( key_aspect.parent() ).last().append(errSpan);
                    };
                }
            });
        }
        else
            key_aspect.find("option:gt(0)").remove();
    }

    $("#control_risk_id").on("change", function() {
        //function call
        change_key_aspect($(this).val());
    });

    function show_hide_divs(selectedVal) {

        switch(selectedVal)
        {
            case "4" : {
                $("#annexure_id_container").removeClass("d-none");
                $("#subset_multi_id_container").addClass("d-none");
                break;
            }

            case "5" : {
                $("#subset_multi_id_container").removeClass("d-none");
                $("#annexure_id_container").addClass("d-none");
                break;
            }

            default : {
                $("#annexure_id_container").addClass("d-none");
                $("#subset_multi_id_container").addClass("d-none");
            }
        }
    }

    // change_key_aspect($("#control_risk_id").val());

    $("#option_id").on("change", function() {
        show_hide_divs($(this).val());  //function call
    });

    show_hide_divs($("#option_id").val());  //function call
});

</script>';

?>