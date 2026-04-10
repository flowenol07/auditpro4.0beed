<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

// Determine if form should be disabled
$disabled = '';
if($data['data']['btn_type'] == 'update' && isset($data['data']['can_update_audit_unit']) && !$data['data']['can_update_audit_unit']) {
    $disabled = 'disabled';
}

echo FormElements::generateFormStart(["name" => "audit-unit-master", "action" => $data['me'] -> url, "appendClass" => "multi-checkbox-check-form" ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php

                //section_type_id
                $markup = FormElements::generateLabel('section_type_id', 'Audit Section
                ');

                if(is_array($data['data']['db_audit_section']) && sizeof($data['data']['db_audit_section']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "section_type_id", "name" => "section_type_id", 
                        "default" => ["", "Please select section"],
                        "options" => $data['data']['db_audit_section'],
                        "appendClass" => "select2search",
                        "selected" => $data['request'] -> input('section_type_id', $data['data']['db_data'] -> section_type_id),
                        "disabled" => $disabled
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'section_type_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //audit_unit_code
                $markup = FormElements::generateLabel('audit_unit_code', 'Audit Unit Code');

                $markup .= FormElements::generateInput([
                    "id" => "audit_unit_code", "name" => "audit_unit_code", 
                    "type" => "text", "value" => $data['request'] -> input('audit_unit_code', $data['data']['db_data'] -> audit_unit_code), 
                    "placeholder" => "Audit Unit Code",
                    "disabled" => $disabled
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'audit_unit_code');
            ?>
        </div>

        <div class="col-md-6">    
            <?php

                //name
                $markup = FormElements::generateLabel('name', 'Audit Unit Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Audit Unit Name",
                    "disabled" => $disabled
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');

            ?>
        </div>

        <div class="col-md-6">
            <?php

                //branch_head_id	
                $markup = FormElements::generateLabel('branch_head_id', 'Name of Head of Audit Unit');

                if(is_array($data['data']['db_employee_data']) && sizeof($data['data']['db_employee_data']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "branch_head_id", "name" => "branch_head_id", 
                        "default" => ["", "Please select head"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['db_employee_data'],
                        "options_db" => ["type" => "obj", "val" => "combined_name"],
                        "selected" => $data['request'] -> input('branch_head_id', $data['data']['db_data'] -> branch_head_id),
                        "disabled" => $disabled
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'branch_head_id');
            ?>
        </div>

        <div class="col-md-6">
            <?php

                //branch_subhead_id	
                $markup = FormElements::generateLabel('branch_subhead_id', 'Name of Assistant to Head of Audit Unit');

                if(is_array($data['data']['db_employee_data']) && sizeof($data['data']['db_employee_data']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "branch_subhead_id", "name" => "branch_subhead_id", 
                        "default" => ["", "Please select sub head"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['db_employee_data'],
                        "options_db" => ["type" => "obj", "val" => "combined_name"],
                        "selected" => $data['request'] -> input('branch_subhead_id', $data['data']['db_data'] -> branch_subhead_id),
                        "disabled" => $disabled
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'branch_subhead_id');
            ?>
        </div>

        <div class="col-md-6">    
            <?php
                if(empty($data['data']['db_data'] -> id))
                {
                    //last_audit_date
                    $markup = FormElements::generateLabel('last_audit_date', 'Last Assesment Done Date');

                    $markup .= FormElements::generateInput([
                        "id" => "last_audit_date", "name" => "last_audit_date", "appendClass" => "date_cls",
                        "type" => "text", "value" => $data['request'] -> input('last_audit_date', $data['data']['db_data'] -> last_audit_date), 
                        "placeholder" => "Last Assesment Done Date",
                        "disabled" => $disabled
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'last_audit_date');
                }
            ?>
        </div>

        <?php 
        
        // Check if this is update mode and has active assessment and update is allowed
        if($data['data']['btn_type'] == 'update' && isset($data['data']['has_active_assessment']) && $data['data']['has_active_assessment'] && $data['data']['can_update_audit_unit']): 
        ?>
        <div class="col-md-12 mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="assign_audit_head" id="assign_audit_head" value="1">
                <label class="form-check-label font-medium" for="assign_audit_head">
                    Assign Branch Head to the Current Assesment
                </label>
                <small class="d-block text-muted">This will assign the selected Branch Head to the current active assessment</small>
            </div>
        </div>
        <?php endif; ?>

        <?php /*
        
        <div class="col-md-12 mb-1">
            <?= FormElements::generateLabel('multi_compliance_ids', 'Mutiple Compliance Employees') ?>
        </div>

        <?php
            $multiComplianceBool = (is_array($data['data']['db_employee_data']) && sizeof($data['data']['db_employee_data']) > 0);
        ?>

        <div class="col-md-12 <?php echo ($multiComplianceBool ? 'height-400' : '') ?>">
            <?php
            if($multiComplianceBool)
            {
                // checkboxes for employee
                
                $employee_data_arr = $data['request'] -> input('multi_compliance_ids', $data['data']['db_data'] -> multi_compliance_ids);

                if(!is_array($employee_data_arr))
                    $employee_data_arr = !empty($employee_data_arr) ? explode (",", $employee_data_arr) : [];

                echo '<table class="table table-bordered">
                        <tr>';

                    // function call
                    echo generate_multiple_checkboxes($data['data']['db_employee_data'], $employee_data_arr, null, 'employee');

                echo '</tr>
                    </table>';
            }
            else
                echo $data['noti']::getCustomAlertNoti('noDataFound');
            ?>
        </div>
        
        <div class="col-md-12">
            <?php
                echo $data['noti']::getInputNoti($data['request'], 'multi_compliance_ids_err');

                    echo FormElements::generateInput([
                        "id" => "multi_type_check", "name" => "multi_compliance_ids", 
                        "type" => "hidden", "value" => '' ]);

                    echo "<div class='mb-2'></div>";
            ?>
        </div> */ ?>

    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Unit'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Unit';
        
        // Disable submit button if update is not allowed
        if($disabled) {
            $btnArray['disabled'] = 'disabled';
            $btnArray['extra'] = 'style="opacity:0.5;cursor:not-allowed;" title="Cannot update - Assessment status must be ≤ 4"';
        }
        
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>