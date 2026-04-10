<?php

use Core\FormElements;

$cardTask = 'cts';

if(isset($data['data']['card_task']))
    $cardTask = $data['data']['card_task'];

echo $data['noti']::getSessionAlertNoti(); ?>

<div class="card apcard mb-4 rounded-0">
    <a class="card-header font-medium<?= (isset($data['data']['db_data'] -> id) && !empty($data['data']['db_data'] -> id)) ? ' custom-arrow-accord' : '' ?>" href="<?= $data['me'] -> url ?>"><?= $data['me'] -> pageHeading ?></a>
  
    <?php if($cardTask == 'cts'): ?>

        <div class="card-body<?= ($cardTask != 'cts' ? ' d-none' : '') ?>">

            <?php echo FormElements::generateFormStart(["name" => "compliance-circular-task-set", "action" => $data['me'] -> url ]); ?>

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
                                    "selected" => $data['request'] -> input('circular_id', $data['data']['db_data'] -> circular_id)
                                ]);
                            }
                            else
                                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                            echo FormElements::generateFormGroup($markup, $data, 'circular_id');

                        ?>
                    </div>

                    <div class="col-md-6">
                        <?php

                            // task set name
                            $markup = FormElements::generateLabel('name', 'Task Set Name');

                            $markup .= FormElements::generateInput([
                                "id" => "name", "name" => "name", 
                                "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                                "placeholder" => "Task Set Name"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'name');
                        ?>
                    </div>

                    <div class="col-md-12">
                        <?php

                            // description
                            $markup = FormElements::generateLabel('description', 'Task Description');

                            $markup .= FormElements::generateTextArea([
                                "id" => "description", "name" => "description", 
                                "type" => "text", "value" => $data['request'] -> input('description', $data['data']['db_data'] -> description), 
                                "placeholder" => "Task Description"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'description');
                        ?>
                    </div>

                    <div class="col-md-6">    
                        <?php

                            // schedule_start_date
                            $markup = FormElements::generateLabel('schedule_start_date', 'Start Date');
                            $markup .= FormElements::generateInput([
                                "id" => "schedule_start_date", "name" => "schedule_start_date", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('schedule_start_date', $data['data']['db_data'] -> schedule_start_date), 
                                "placeholder" => "Start Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'schedule_start_date');
                        ?>
                    </div>

                    <div class="col-md-6">    
                        <?php

                            // schedule_end_date
                            $markup = FormElements::generateLabel('schedule_end_date', 'End Date');
                            $markup .= FormElements::generateInput([
                                "id" => "schedule_end_date", "name" => "schedule_end_date", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('schedule_end_date', $data['data']['db_data'] -> schedule_end_date), 
                                "placeholder" => "End Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'schedule_end_date');
                        ?>
                    </div>

                    <div class="d-block clearfix"></div>
                        
                    <div class="col-md-12">
                        <?php

                            // circular frequency
                            $markup = FormElements::generateLabel('frequency', 'Task Set Frequency');
                            if( isset($data['data']['init_frequency']) && 
                                sizeof($data['data']['init_frequency']) > 0 )
                            {
                                $markup .= FormElements::generateSelect([
                                    "id" => "frequency", "name" => "frequency", 
                                    "default" => ["", "Please select circular frequency"],
                                    "options" => $data['data']['init_frequency'], "options_db" => ["type" => "arr", "val" => "title"],
                                    "selected" => $data['request'] -> input('frequency', $data['data']['db_data'] -> frequency)
                                ]);
                            }
                            else
                                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                            echo FormElements::generateFormGroup($markup, $data, 'frequency');
                        ?>
                    </div>

                    <div class="col-md-6">    
                        <?php

                            // reporting_date_1
                            $markup = FormElements::generateLabel('reporting_date_1', 'Reporting Date');
                            $markup .= FormElements::generateInput([
                                "id" => "reporting_date_1", "name" => "reporting_date_1", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('reporting_date_1', $data['data']['db_data'] -> reporting_date_1), 
                                "placeholder" => "Reporting Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'reporting_date_1');
                        ?>
                    </div>

                    <div class="col-md-6">    
                        <?php

                            // due_date_1
                            $markup = FormElements::generateLabel('due_date_1', 'Due Date <span class="font-sm">[For Branch / Department]</span>');
                            $markup .= FormElements::generateInput([
                                "id" => "due_date_1", "name" => "due_date_1", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('due_date_1', $data['data']['db_data'] -> due_date_1), 
                                "placeholder" => "Due Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'due_date_1');
                        ?>
                    </div>

                    <div class="col-md-6 date-2-cls d-none">    
                        <?php

                            // reporting_date_2
                            $markup = FormElements::generateLabel('reporting_date_2', 'Reporting Date <span class="font-sm">[For 2nd Period]</span>');
                            $markup .= FormElements::generateInput([
                                "id" => "reporting_date_2", "name" => "reporting_date_2", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('reporting_date_2', $data['data']['db_data'] -> reporting_date_2), 
                                "placeholder" => "Reporting Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'reporting_date_2');
                        ?>
                    </div>

                    <div class="col-md-6 date-2-cls d-none">    
                        <?php

                            // due_date_2
                            $markup = FormElements::generateLabel('due_date_2', 'Due Date <span class="font-sm">[For Branch / Department] [For 2nd Period]</span>');
                            $markup .= FormElements::generateInput([
                                "id" => "due_date_2", "name" => "due_date_2", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('due_date_2', $data['data']['db_data'] -> due_date_2), 
                                "placeholder" => "Due Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'due_date_2');
                        ?>
                    </div>
                            
                </div>    

                <?php 

                $btnArray = [ 'name' => 'submitTaskSet', 'value' => 'Add Task Set'];     

                if(isset($data['data']['btn_type']) && $data['data']['btn_type'] == 'update')
                {
                    $btnArray['value'] = 'Update Task Set';
                    echo FormElements::generateSubmitButton('update', $btnArray );
                }
                else
                    echo FormElements::generateSubmitButton('add', $btnArray );

            echo FormElements::generateFormClose(); ?>

        </div>
    <?php endif; ?>
</div>

<?php if(isset($data['data']['db_data'] -> id) && !empty($data['data']['db_data'] -> id)): ?>

<div class="card apcard mb-4 rounded-0">
    <a class="card-header font-medium custom-arrow-accord" href="<?= $data['me'] -> url . '?task=au' ?>">Assign Branch / HO Departments</a>

    <?php if($cardTask == 'au'): ?>
  
        <div class="card-body<?= ($cardTask != 'au' ? ' d-none' : '') ?>">

        <?php echo FormElements::generateFormStart(["name" => "compliance-circular-assign", "class" => "multi-checkbox-check-form", "action" => $data['me'] -> url . '?task=au' ]); ?>

            <div class="row">

                <div class="col-md-12">    

                <?php

                $isAuditUnitData = false;

                if(is_array($data['data']['db_audit_unit_data']))
                {
                    if( (isset($data['data']['db_audit_unit_data']['branch']) && 
                        is_array($data['data']['db_audit_unit_data']['branch']) && 
                        sizeof($data['data']['db_audit_unit_data']['branch']) > 0) || (
                        isset($data['data']['db_audit_unit_data']['ho']) && 
                        is_array($data['data']['db_audit_unit_data']['ho']) && 
                        sizeof($data['data']['db_audit_unit_data']['ho']) > 0
                        ) )
                    {
                        $isAuditUnitData = true;
                        echo FormElements::generateSubmitButton('', [ 'value' => 'Check All', 'type' => 'button', 'id' => 'checkAllCheckboxes'] );
                        echo '<div class="mb-3"></div>'; 
                    }

                    if( isset($data['data']['db_audit_unit_data']['branch']) && 
                        is_array($data['data']['db_audit_unit_data']['branch']) && 
                        sizeof($data['data']['db_audit_unit_data']['branch']) > 0 )
                    {                
                        echo FormElements::generateLabel('', 'Select Branches') . "\n";

                        echo '<table class="table table-bordered">';

                            $audit_unit_data_arr = !empty($data['data']['db_data'] -> audit_unit_ids) ? explode (",", $data['data']['db_data'] -> audit_unit_ids) : [];

                            // function call
                            echo generate_multiple_checkboxes($data['data']['db_audit_unit_data']['branch'], $audit_unit_data_arr, 'multi_audit_units_check', 'audit_unit');
                            
                        echo '</table>';
                    }

                    if( isset($data['data']['db_audit_unit_data']['ho']) && 
                        is_array($data['data']['db_audit_unit_data']['ho']) && 
                        sizeof($data['data']['db_audit_unit_data']['ho']) > 0 )
                    {                
                        echo FormElements::generateLabel('', 'Select Head of Departments') . "\n";
                        
                        echo '<table class="table table-bordered">';

                            $audit_unit_data_arr = !empty($data['data']['db_data'] -> audit_unit_ids) ? explode (",", $data['data']['db_data'] -> audit_unit_ids) : [];

                            // function call
                            echo generate_multiple_checkboxes($data['data']['db_audit_unit_data']['ho'], $audit_unit_data_arr, 'multi_audit_units_check', 'audit_unit');
                            
                        echo '</table>';
                    }

                    echo FormElements::generateInput([
                        "id" => "multi_type_check", "name" => "multi_type_check", 
                        "type" => "hidden", "value" => '' ]);

                }
                
                // no data found
                if(!$isAuditUnitData) echo $data['noti']::getCustomAlertNoti('noDataFound');

                if( $data['request'] -> has('multi_type_check_err'))
                    echo '<span class="d-block text-danger font-sm mb-2">'. $data['noti']::getNoti( $data['request'] -> input('multi_type_check_err') ) .'</span>' . "\n";

                ?>

                </div>
            </div>

            <?php 

            $btnArray = [ 'name' => 'submitAssign', 'value' => 'Assign Circular'];
            echo FormElements::generateSubmitButton('update', $btnArray );

        echo FormElements::generateFormClose(); ?>

        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php if(isset($data['data']['db_data'] -> id) && !empty($data['data']['db_data'] -> id)): ?>

<div class="card apcard mb-4 rounded-0">
    <a class="card-header font-medium custom-arrow-accord" href="<?= $data['me'] -> url . '?task=multi_task' ?>">Assign Tasks</a>

    <?php if($cardTask == 'multi_task'): ?>
  
        <div class="card-body<?= ($cardTask != 'multi_task' ? ' d-none' : '') ?>">

        <?php 

        echo FormElements::generateFormStart(["name" => "compliance-circular-assign", "class" => "multi-checkbox-check-form", "action" => $data['me'] -> url . '?task=multi_task' ]);
        
            if( is_array($data['data']['db_tasks_data']) && 
                sizeof($data['data']['db_tasks_data']) > 0)
            {
                echo FormElements::generateSubmitButton('', [ 'value' => 'Check All', 'type' => 'button', 'id' => 'checkAllCheckboxes'] );
                echo '<div class="mb-3"></div>';

                echo '<h4 class="font-medium lead mb-2 mb-0">Total Circular Tasks: '. sizeof($data['data']['db_tasks_data']) .'</h4>' . "\n";

                $str = '<div class="table-responsive">' . "\n";
                    $str .= '<table class="table table-bordered">' . "\n";

                    $lastHeaderId = null;
                    $task_data_arr = !empty($data['data']['db_data'] -> task_ids) ? explode (",", $data['data']['db_data'] -> task_ids) : [];

                    $qSrNo = 1;

                    foreach($data['data']['db_tasks_data'] as $cTaskData)
                    {
                        if($lastHeaderId != $cTaskData -> header_id)
                        {
                            // display header
                            $str .= '<tr class="bg-light-gray">' . "\n";
                                $str .= '<th class="text-center" width="60">'. string_operations('Header', 'upper') .'</th>' . "\n";
                                $str .= '<th>'. string_operations($cTaskData -> header_name, 'upper') .'</th>' . "\n";
                            $str .= '</tr>' . "\n";

                            $lastHeaderId = $cTaskData -> header_id;
                            $qSrNo = 1;
                        }

                        // checked 
                        $checked = is_array($task_data_arr) && in_array($cTaskData -> id, $task_data_arr) ? 1 : 0;

                        $str .= '<tr>' . "\n";
                                
                            $checkboxMrk = FormElements::generateCheckboxOrRadio([
                                "appendClass" => 'multi-checkbox-ids d-inlin-block ms-4',
                                "checked" => $checked, "value" => $cTaskData -> id,
                            ]);

                            $str .= '<td>'. $checkboxMrk .'</td>' . "\n";

                            $str .= '<td>'. $qSrNo . '. ' . $cTaskData -> task .'</td>' . "\n";

                        $str .= '</tr>' . "\n";
                        $qSrNo++;
                    }

                    $str .= '</table>' . "\n";
                $str .= '</div>' . "\n";

                echo $str;

                echo FormElements::generateInput([
                    "id" => "multi_type_check", "name" => "multi_type_check", 
                    "type" => "hidden", "value" => '' ]);
            }
            else
                echo $data['noti']::getCustomAlertNoti('noDataFound');

            if( $data['request'] -> has('multi_type_check_err'))
                echo '<span class="d-block text-danger font-sm mb-2">'. $data['noti']::getNoti( $data['request'] -> input('multi_type_check_err') ) .'</span>' . "\n";

            $btnArray = [ 'name' => 'submitTasks', 'value' => 'Update Task Set'];
            echo FormElements::generateSubmitButton('update', $btnArray );
            
        echo FormElements::generateFormClose();

        ?>

        </div>

    <?php endif; ?>
</div>

<?php endif; ?>

<?php

if($cardTask == 'cts')
{
    $data['data']['inline_js'] = "\n" . '
    <script>
    $(document).ready(function(){
        function show_hide_container (val) {
            $(".date-2-cls").addClass("d-none");
            if (val === "1") $(".date-2-cls").removeClass("d-none");
        }
        $("#frequency").change( function() {
            show_hide_container($(this).val());
        });
        show_hide_container($("#frequency").val());
    });
    </script>';
}

?>