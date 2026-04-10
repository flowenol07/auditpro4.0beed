<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "compliance-circular-set-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php

                // authority_id
                $markup = FormElements::generateLabel('authority_id', 'Authority');

                if(is_array($data['data']['circularAuthority']) && sizeof($data['data']['circularAuthority']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "authority_id", "name" => "authority_id", 
                        "default" => ["", "Please select authority"],
                        "options" => $data['data']['circularAuthority'],
                        "options_db" => ["type" => "obj", "val" => "name"],
                        "selected" => $data['request'] -> input('authority_id', $data['data']['db_data'] -> authority_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'authority_id');

            ?>
        </div>

        <div class="col-md-6">    
            <?php
                // ref_no
                $markup = FormElements::generateLabel('ref_no', 'Circular No.');

                $markup .= FormElements::generateInput([
                    "id" => "ref_no", "name" => "ref_no",
                    "type" => "text", "value" => $data['request'] -> input('ref_no', $data['data']['db_data'] -> ref_no), 
                    "placeholder" => "Circular No."
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'ref_no');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                // circular name
                $markup = FormElements::generateLabel('name', 'Circular Title');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Circular Title"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-6">    
            <?php
                // circular_date
                $markup = FormElements::generateLabel('circular_date', 'Circular Date');

                $markup .= FormElements::generateInput([
                    "id" => "circular_date", "name" => "circular_date", "appendClass" => "date_cls",
                    "type" => "text", "value" => $data['request'] -> input('circular_date', $data['data']['db_data'] -> circular_date), 
                    "placeholder" => "Circular Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'circular_date');
            ?>
        </div>

        <!-- <div class="col-md-6"> -->
            <?php

                // priority_id
            //     $markup = FormElements::generateLabel('priority_id', 'Priority');

            //     if(isset(COMPLIANCE_PRO_ARRAY['compliance_priority']) && sizeof(COMPLIANCE_PRO_ARRAY['compliance_priority']) > 0)
            //     {
            //         $markup .= FormElements::generateSelect([
            //             "id" => "priority_id", "name" => "priority_id", 
            //             "default" => ["", "Please select priority"],
            //             "options" => COMPLIANCE_PRO_ARRAY['compliance_priority'],
            //             "selected" => $data['request'] -> input('priority_id', $data['data']['db_data'] -> priority_id)
            //         ]);
            //     }
            //     else
            //         $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            //     echo FormElements::generateFormGroup($markup, $data, 'priority_id');
            // ?>
        <!-- </div> -->

        <div class="col-md-12">
            <?php

                // circular type
                $markup = FormElements::generateLabel('set_type_id', 'Circular Type');

                if(isset(COMPLIANCE_PRO_ARRAY['compliance_categories']) && sizeof(COMPLIANCE_PRO_ARRAY['compliance_categories']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "set_type_id", "name" => "set_type_id", 
                        "default" => ["", "Please select circular type"],
                        "options" => COMPLIANCE_PRO_ARRAY['compliance_categories'],
                        "selected" => $data['request'] -> input('set_type_id', $data['data']['db_data'] -> set_type_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'set_type_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                // description
                $markup = FormElements::generateLabel('description', 'Circular Description');

                $markup .= FormElements::generateTextArea([
                    "id" => "description", "name" => "description", 
                    "type" => "text", "value" => $data['request'] -> input('description', $data['data']['db_data'] -> description), 
                    "placeholder" => "Circular Description"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'description');
            ?>
        </div>

        <!-- <div class="col-md-12"> -->

            <?php
                
                //$markup = FormElements::generateLabel('from_portal', 'Circular Portal / Website');

             //   $markup .= FormElements::generateInput([
               //     "id" => "from_portal", "name" => "from_portal", 
               //     "type" => "text", "value" => $data['request'] -> input('from_portal', $data['data']['db_data'] -> from_portal), 
              //      "placeholder" => "Circular Portal / Website"
               // ]);

              //  echo FormElements::generateFormGroup($markup, $data, 'from_portal');
           // ?>

        <!-- </div> -->

        <div class="col-md-12 mt-2">

            <?php
                //is_penalty_applicable
                $markup = FormElements::generateCheckboxOrRadio([
                    'type' => 'checkbox', 'id' => 'is_penalty_applicable', 'name' => 'is_penalty_applicable', 
                    'value' => '1', 'text' => 'Is Penalty Applicable',
                    'checked' => (($data['request'] -> input('is_penalty_applicable', $data['data']['db_data'] -> is_penalty_applicable) == 1 ) ? true : false), 
                    'customLabelClass' => 'font-medium text-primary'
                ]);
            
                echo FormElements::generateFormGroup($markup);
            ?>
        </div>

        <div id="is_penalty_applicable_container" class="d-none">

            <div class="col-md-12">
                <?php
                    // penalty_amt
                    $markup = FormElements::generateLabel('penalty_amt', 'Penalty Amount');

                    $markup .= FormElements::generateInput([
                        "id" => "penalty_amt", "name" => "penalty_amt", 
                        "type" => "text", "value" => $data['request'] -> input('penalty_amt', $data['data']['db_data'] -> penalty_amt), 
                        "placeholder" => "Penalty Amount"
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'penalty_amt');
                ?>
            </div>

            <div class="col-md-12">
                <?php
                    // penalty_description
                    $markup = FormElements::generateLabel('penalty_description', 'Penalty Description');

                    $markup .= FormElements::generateTextArea([
                        "id" => "penalty_description", "name" => "penalty_description", 
                        "type" => "text", "value" => $data['request'] -> input('penalty_description', $data['data']['db_data'] -> penalty_description), 
                        "placeholder" => "Penalty Description"
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'penalty_description');
                ?>
            </div>

        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Circular'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Circular';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function(){

    function show_hide_container (checkBox) {
        if (checkBox.is(":checked")) 
            $("#is_penalty_applicable_container").removeClass("d-none");
        else
            $("#is_penalty_applicable_container").addClass("d-none");
    }

    $("#is_penalty_applicable").change( function() {
        show_hide_container($(this));
    });

    show_hide_container($("#is_penalty_applicable"));

});
</script>';

?>