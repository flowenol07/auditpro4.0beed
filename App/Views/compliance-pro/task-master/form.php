<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "task-master", "action" => $data['me'] -> url . $data['data']['backto']]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php

                // circular_id
                $markup = FormElements::generateLabel('circular_id', 'Circular');

                if(is_array($data['data']['circularData']) && sizeof($data['data']['circularData']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "circular_id", "name" => "circular_id", 
                        "default" => ["", "Please select circular"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['circularData'],
                        "options_db" => ["type" => "obj", "val" => "name"],
                        "disabled" => $data['data']['disableC&H'],
                        "selected" => $data['request'] -> input('circular_id', $data['data']['db_task_data'] -> set_id),
                        "dataAttributes" => ['data-url' => $data['siteUrls']::getUrl('complianceCircularTaskMaster') . '/header-ajx' ],
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'circular_id');
                echo '<span class="d-block font-sm text-danger mt-1"></span>' . "\n";
            ?>
        </div>
        
        <div class="col-md-6">
            <?php

                // header_id
                $markup = FormElements::generateLabel('header_id', 'Header <a href="'. $data['siteUrls']::getUrl('complianceCircularHeaderMaster') .'/add' .'" class="text-primary font-sm">[ Click Here For Add New Header ]</a>');

                $markup .= FormElements::generateSelect([
                    "id" => "header_id", "name" => "header_id",
                    "default" => ["", "Please select header"],
                    "options" => $data['data']['headerData'],
                    "options_db" => ["type" => "obj", "val" => "name"],
                    "disabled" => $data['data']['disableC&H'],
                    "selected" => $data['request'] -> input('header_id', $data['data']['db_task_data'] -> header_id)
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'header_id');

            ?>
        </div>

        <?php 
        
        if($data['data']['disableC&H'])
        {
            
            echo '<div class="col-md-12">' . "\n";
                echo $data['noti']::cError('<span class="font-bold">Note:</span> The task is already assigned to a task set, which is why the circular and header are disabled. To make changes, please remove it from the task set, after which they will be enabled for editing.', 'warning');
            echo '</div>' . "\n";
        }
        
        ?>

        <!-- <div class="col-md-12"> -->
            <?php

            //     // priority_id
            //     $markup = FormElements::generateLabel('priority_id', 'Priority');

            //     if(isset(COMPLIANCE_PRO_ARRAY['compliance_priority']) && sizeof(COMPLIANCE_PRO_ARRAY['compliance_priority']) > 0)
            //     {
            //         $markup .= FormElements::generateSelect([
            //             "id" => "priority_id", "name" => "priority_id", 
            //             "default" => ["", "Please select priority"],
            //             "options" => COMPLIANCE_PRO_ARRAY['compliance_priority'],
            //             "selected" => $data['request'] -> input('priority_id', $data['data']['db_task_data'] -> priority_id)
            //         ]);
            //     }
            //     else
            //         $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            //     echo FormElements::generateFormGroup($markup, $data, 'priority_id');
            // ?>
        <!-- </div> -->

        <div class="col-md-12">
            <?php
                // task
                $markup = FormElements::generateLabel('task', 'Circular Task');

                $markup .= FormElements::generateTextArea([
                    "id" => "task", "name" => "task", 
                    "type" => "text", "value" => $data['request'] -> input('task', $data['data']['db_task_data'] -> task), 
                    "placeholder" => "Circular Task"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'task');
            ?>
        </div>

        <?php /* if($data['data']['db_data'] -> is_compliance == 1): ?>

        <?php /*
        <div class="col-md-12">
            <?php
                // answer_given
                $markup = FormElements::generateLabel('answer_given', 'Answer Given');

                $markup .= FormElements::generateInput([
                    "id" => "answer_given", "name" => "answer_given",
                    "type" => "text", "value" => $data['request'] -> input('answer_given', $data['data']['db_task_data'] -> answer_given), 
                    "placeholder" => "Answer Given"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'answer_given');
            ?>
        </div>

        <?php else: ?>

        <div class="col-md-12">
            <?php

                // option_id
                $markup = FormElements::generateLabel('option_id', 'Input Method');

                if(is_array($this -> data['question_input']) && sizeof($this -> data['question_input']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "option_id", "name" => "option_id", 
                        "default" => ["", "Please select input method"],
                        "options" => $this -> data['question_input'],
                        "options_db" => ['type' => 'arr', 'val' => 'title' ],
                        "selected" => $data['request'] -> input('option_id', $data['data']['db_task_data'] -> option_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'option_id');
            ?>
        </div>

        <?php endif; */ ?>

        <!-- <div class="col-md-12"> -->
            <?php

                // // risk_category_id
                // $markup = FormElements::generateLabel('risk_category_id', 'Risk Category');

                // if(is_array($data['data']['rcData']) && sizeof($data['data']['rcData']) > 0)
                // {
                //     $markup .= FormElements::generateSelect([
                //         "id" => "risk_category_id", "name" => "risk_category_id", 
                //         "default" => ["", "Please select risk category"],
                //         "options" => $data['data']['rcData'],
                //         "options_db" => ["type" => "obj", "val" => "risk_category"],
                //         "selected" => $data['request'] -> input('risk_category_id', $data['data']['db_task_data'] -> risk_category_id)
                //     ]);
                // }
                // else
                //     $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                // echo FormElements::generateFormGroup($markup, $data, 'risk_category_id');
            ?>
        <!-- </div> -->

        <!-- <div class="col-md-6"> -->
            <?php

                // // business_risk
                // $markup = FormElements::generateLabel('business_risk', 'Business Risk');

                // if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
                // {
                //     $markup .= FormElements::generateSelect([
                //         "id" => "business_risk", "name" => "business_risk", 
                //         "default" => ["", "Please select business risk"],
                //         "options" => RISK_PARAMETERS_ARRAY, "options_db" => ['type' => 'arr', 'val' => 'title' ],
                //         "selected" => $data['request'] -> input('business_risk', $data['data']['db_task_data'] -> business_risk)
                //     ]);
                // }
                // else
                //     $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                // echo FormElements::generateFormGroup($markup, $data, 'business_risk');
            ?>
        <!-- </div> -->

        <!-- <div class="col-md-6"> -->
            <?php

            //     // control_risk
            //     $markup = FormElements::generateLabel('control_risk', 'Control Risk');

            //     if(is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0)
            //     {
            //         $markup .= FormElements::generateSelect([
            //             "id" => "control_risk", "name" => "control_risk", 
            //             "default" => ["", "Please select control risk"],
            //             "options" => RISK_PARAMETERS_ARRAY, "options_db" => ['type' => 'arr', 'val' => 'title' ],
            //             "selected" => $data['request'] -> input('control_risk', $data['data']['db_task_data'] -> control_risk)
            //         ]);
            //     }
            //     else
            //         $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            //     echo FormElements::generateFormGroup($markup, $data, 'control_risk');
            // ?>
        <!-- </div> -->

        <!-- <div class="col-md-12"> -->
            <!-- <?php

                // //area_of_audit_id
                // $markup = FormElements::generateLabel('area_of_audit_id', 'Broader Area of Audit Non-Compliance');

                // if(is_array($data['data']['db_area_of_audit']) && sizeof($data['data']['db_area_of_audit']) > 0)
                // {
                //     $markup .= FormElements::generateSelect([
                //         "id" => "area_of_audit_id", "name" => "area_of_audit_id", 
                //         "default" => ["", "Please select broader area of audit non-compliance"],
                //         "appendClass" => "select2search",
                //         "options" => $data['data']['db_area_of_audit'], "options_db" => ['type' => 'obj', 'val' => 'name' ],
                //         "selected" => $data['request'] -> input('area_of_audit_id', $data['data']['db_task_data'] -> area_of_audit_id)
                //     ]);
                // }
                // else
                //     $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                // echo FormElements::generateFormGroup($markup, $data, 'area_of_audit_id');
            ?> -->
        <!-- </div> -->

    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Task'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Task';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function() {

const err_msg = "Something went wrong! Please try after sometime.";

function get_header_data(this_obj) {
    let circularSelect = $(this_obj);
    let resSpan = circularSelect.parent().find(".text-danger");

    $( resSpan ).html("");
    $("#header_id option:not(:first-child)").remove();

    if ( circularSelect.val().length > 0 &&
         circularSelect.attr("data-url").length > 0 )
    {  
        $postData = { circular_id: circularSelect.val() };

        $.ajax({
  
            url: circularSelect.attr("data-url"),
            type: "POST", data: $postData,
  
            success: function (res) {
  
              try {
  
                res = JSON.parse(res);
  
                if(res.data !== undefined && res.success !== undefined)
                {
                  $.each(res.data, function(index, dataObj) {
                      $("#header_id").append(
                          $("<option></option>")
                          .attr("value", dataObj.id)
                          .text(dataObj.name)
                      ); 
                  });
                }
                else // has errors
                  $( resSpan ).text(res.msg);
  
              } catch (error) { $(resSpan).text(err_msg); }
            },
  
            error: function (res) { $(resSpan).text(err_msg); }
  
          });
    }
}

if( $("#circular_id")) {

    $("#circular_id").on("change", function() {
        get_header_data($(this));
    });
          
}

});
</script>';
?>