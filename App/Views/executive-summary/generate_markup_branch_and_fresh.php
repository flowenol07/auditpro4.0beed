<?php 

use Core\FormElements;

// unset hold and cf
$statusArray = AUDIT_STATUS_ARRAY['compliance_review_action'];
unset($statusArray[4], $statusArray[5]);

define('EXE_COM_STATUS_ARRAY', $statusArray);
unset($statusArray);

// $userType = 2 24.08.2024 removed
function generate_markup_branch_and_fresh($data, $userType, $branchPosition, $freshAccount) 
{
    $alpha = 0;     
    $saveEnable = true;      

    $glData = $freshAccount === 1 ?  BRANCH_FRESH_ACCOUNTS : BRANCH_FINANCIAL_POSITION;

    foreach($glData  as $cGlType => $cGlTypeDetails)
    {
        $alphaIndex = chr(ord('A') + $alpha);
        $alpha++;
        $j = 1;
        $mrk = '';
        $total_march = 0;
        $total_current = 0;

        foreach($cGlTypeDetails as $cGlTypeId => $cGlTypeName)
        {
            $gl_name = str_ireplace("(" . ucfirst($cGlType) .")", "", $data['data'][ ( $freshAccount === 1 ? 'branch_fresh_accounts' : 'branch_financial_position') ][$cGlTypeId]);

            // $gl_input_id = 'branch_position_deposit'. string_operations(str_replace(" ","_", $gl_name));

            $gl_input_class = string_operations(str_replace(" ","_", $cGlType)) . '_current_value';

            $gl_total_class = string_operations(str_replace(" ","_", $cGlType)) . '_total_value';

            $gl_input_branch_name = 'branch_position_type_' . $cGlTypeId;

            $gl_input_branch_comp_comment_name = 'branch_position_comment_type_' . $cGlTypeId;

            $gl_input_action_review_audit_name = 'review_audit_action_' . $cGlTypeId;

            $gl_input_comment_review_audit_name = 'review_audit_comment_' . $cGlTypeId;

            $gl_input_action_review_compliance_name = 'review_compliance_action_' . $cGlTypeId;

            $gl_input_comment_review_compliance_name = 'review_compliance_comment_' . $cGlTypeId;

            $gl_input_fresh_name = 'fresh_account_type_' . $cGlTypeId;

            $gl_input_fresh_comp_comment_name = 'fresh_account_comment_type_' . $cGlTypeId;

            // Total Calculation for Branch Position
            if($branchPosition)
            {
                $total_march += isset($data['data']['db_march_position'][ $cGlTypeId ]) ? $data['data']['db_march_position'][ $cGlTypeId ] : '0.00';

                $total_current += (is_array($data['data']['db_exe_branch_position']) && array_key_exists($cGlTypeId, $data['data']['db_exe_branch_position'])) ? $data['data']['db_exe_branch_position'][ $cGlTypeId ] -> amount : '0.00';

                $total_ytd = $total_current - $total_march;

                $march_value = isset($data['data']['db_march_position'][ $cGlTypeId ]) ? $data['data']['db_march_position'][ $cGlTypeId ] : '0.00';
            }
    
            // Branch Position Start --------------------------------------------
            if($branchPosition)
            {
                $inlineStyle = '';

                if($userType == 4 && (isset($data['data']['db_exe_branch_position'][ $cGlTypeId ] -> audit_status_id) && $data['data']['db_exe_branch_position'][ $cGlTypeId ] -> audit_status_id == 3))
                    $inlineStyle = 'class="text-danger"';

                $mrk .= '
                <tr data-typeid= ' . $cGlTypeId . ' ' . $inlineStyle . '>
                    <td>' . $j .'</td>
                    <td>' . $gl_name . '</td>';

                // Condition for Disabling on the basis of audit_status_id
                $disabled_audit = true;
                $disabled_comp = true;
                
                if($userType == 2 && $data['userDetails']['emp_type'] == 2 && ((isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 3 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 1 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 0)) || !isset($data['data']['db_exe_branch_position'][$cGlTypeId])))
                {
                    $disabled_audit = false;
                }
                elseif($userType == 3 && $data['userDetails']['emp_type'] == 3 && isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 3 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 1 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 0))
                {
                    $disabled_comp = false;
                }
                elseif(is_array($data['data']['db_exe_branch_position']) && empty($data['data']['db_exe_branch_position']))
                {
                    $disabled_audit = false;
                }

                // Value
                if(/*$userType == 2 && $data['userDetails']['emp_type'] == 2 && isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 3*/ 0)
                {
                    $value = "";
                }
                else
                {
                    $value = $data['request'] -> input($gl_input_branch_name, isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> amount) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> amount : "");
                }

                // Compliance
                if(/*$userType == 3 && $data['userDetails']['emp_type'] == 3 && isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 3*/0)
                {
                    $compliance = "";
                }
                else
                {
                    $compliance = $data['request'] -> input($gl_input_branch_comp_comment_name, isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_commpliance) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_commpliance : "");
                }

                $mrk .= '
                    <td class="text-center">' . $march_value .'</td>
                    <td>';
                        $markup = FormElements::generateInput([
                            "id" => $gl_input_branch_name, "name" => $gl_input_branch_name, "appendClass" => $gl_input_class, "type" => "text", "value" => $value, 
                            "placeholder" => "In Lakhs XXXXXX.XX",
                            "disabled" => $disabled_audit,
                        ]);
    
                        $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_branch_name, 'form-group');
                    $mrk .= '</td>';
                    $mrk .= '<td></td>';

                    if($branchPosition)
                    {
                        $branchPosiTimelineCnt = 1;

                        if($data['userDetails']['emp_type'] == 2 && isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 3 && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 2 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 1 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 0)))
                        {
                            $mrk .=
                            '<tr>
                                <th colspan="2">Last Audit</th>
                                <th>Reviewer Action</th>
                                <th>Reviewer Comment</th>
                                <th>Status Time</th>
                            </tr>';
                        
                            if(empty($data['data']['db_exe_branch_posi_timeline']))
                            {
                                $mrk .=
                                '<tr>
                                    <td colspan="2">' . $data['data']['db_exe_branch_position'][$cGlTypeId] -> amount . '</td>

                                    <td>' . AUDIT_STATUS_ARRAY['audit_review_action'][$data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id] . '</td>
                                    
                                    <td>' . $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_reviewer_comment . '</td>
                                    
                                    <td>' . $data['data']['db_exe_branch_position'][$cGlTypeId] -> updated_at . '</td>
                                </tr>';
                            }
                            if(!empty($data['data']['db_exe_branch_posi_timeline']))
                            {
                                foreach($data['data']['db_exe_branch_posi_timeline'] as $cId => $cTimelineData)
                                {
                                    if(isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> id) && $cTimelineData -> esbp_id  == $data['data']['db_exe_branch_position'][$cGlTypeId] -> id)
                                    {
                                        $mrk .='<tr>
                                            <td colspan="2">' . $cTimelineData -> amount . '</td>

                                            <td>' . AUDIT_STATUS_ARRAY['audit_review_action'][$cTimelineData -> audit_status_id] . '</td>
                                            
                                            <td>' . $cTimelineData -> audit_reviewer_comment . '</td>
                                            
                                            <td>' . $cTimelineData -> created_at . '</td>
                                        </tr>';

                                        $branchPosiTimelineCnt++;
                                    }
                                }
                            }
                        }

                        if($saveEnable && $branchPosiTimelineCnt >= $data['data']['assesmentData'] -> audit_review_reject_limit)
                            $saveEnable = false;
                    }
                
                    // <td> only for emp type = 3
                    if($userType == 3)
                    {
                        $mrk .= '<td>';
                            $markup = FormElements::generateTextArea([
                                "id" => $gl_input_branch_comp_comment_name, "name" => ($disabled_comp) ? '' : $gl_input_branch_comp_comment_name, "type" => "text", "value" => $compliance, 
                                "placeholder" => "Compliance Comment",
                                "rows" => 1,
                                "disabled" => $disabled_comp,
                            ]);
        
                            $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_branch_comp_comment_name, 'form-group');

                        $mrk .= '</td>';

                        if($branchPosition)
                        {
                            $branchPosiTimelineCnt = 1;

                            if($data['userDetails']['emp_type'] == 3 && isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 3 && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 1 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 2)))
                            {
                                $mrk .=
                                '<tr>
                                    <th colspan="3">Last Compliance</th>
                                    <th>Reviewer Action</th>
                                    <th>Reviewer Comment</th>
                                    <th>Status Time</th>
                                </tr>';

                                if(empty($data['data']['db_exe_branch_posi_timeline']))
                                {
                                $mrk .=
                                '<tr>
                                    <td colspan="3">' . $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_commpliance . '</td>

                                    <td>' . EXE_COM_STATUS_ARRAY[$data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id] . '</td>
                                    
                                    <td>' . $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_reviewer_comment . '</td>
                                    
                                    <td>' . $data['data']['db_exe_branch_position'][$cGlTypeId] -> updated_at . '</td>
                                </tr>';
                                }

                                if(!empty($data['data']['db_exe_branch_posi_timeline']))
                                {
                                    foreach($data['data']['db_exe_branch_posi_timeline'] as $cId => $cTimelineData)
                                    {
                                        if(isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> id) && $cTimelineData -> esbp_id  == $data['data']['db_exe_branch_position'][$cGlTypeId] -> id && $cTimelineData -> compliance_status_id != 0)
                                        {
                                            $mrk .='<tr>
                                                <td colspan="3">' . $cTimelineData -> audit_commpliance . '</td>

                                                <td>' . EXE_COM_STATUS_ARRAY[$cTimelineData -> compliance_status_id] . '</td>
                                                
                                                <td>' . $cTimelineData -> compliance_reviewer_comment . '</td>
                                                
                                                <td>' . $cTimelineData -> created_at . '</td>
                                            </tr>';

                                            $branchPosiTimelineCnt++;
                                        }
                                    }
                                }
                            }

                            if($saveEnable && $branchPosiTimelineCnt >= $data['data']['assesmentData'] -> compliance_review_reject_limit)
                                $saveEnable = false;
                        }
                    }
                    // <td> only for emp type = 4
                    elseif($userType == 4)
                    {
                        if($data['data']['assesmentData'] ->  audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'])
                        {
                            $mrk .= '<td>';
                                $markup = FormElements::generateTextArea([
                                    "id" => $gl_input_branch_comp_comment_name, "name" => $gl_input_branch_comp_comment_name, "type" => "text", "value" => $data['request'] -> input($gl_input_branch_comp_comment_name, isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_commpliance) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_commpliance : ""),
                                    "rows" => 1,
                                    "disabled" => true,
                                ]);
            
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_comment_review_audit_name, 'form-group');

                            $mrk .= '</td>';

                            $mrk .= '<td>';

                                if(is_array(EXE_COM_STATUS_ARRAY) && sizeof(EXE_COM_STATUS_ARRAY) > 0 )
                                {
                                    $markup = FormElements::generateSelect([
                                        "id" => $gl_input_action_review_compliance_name, "name" => $gl_input_action_review_compliance_name,
                                        "options" => EXE_COM_STATUS_ARRAY,
                                        "selected" => $data['request'] -> input('user_type', isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id : "")
                                    ]);
                
                                }
                                else    
                                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                
                
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_action_review_audit_name);

                            $mrk .= '</td>';

                            $mrk .= '<td>';
                                $markup = FormElements::generateTextArea([
                                    "id" => $gl_input_comment_review_compliance_name, "name" => $gl_input_comment_review_compliance_name, "type" => "text", "value" => $data['request'] -> input($gl_input_comment_review_compliance_name, isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_reviewer_comment) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_reviewer_comment : ""), 
                                    "placeholder" => "Comment",
                                    "rows" => 1,
                                ]);
            
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_comment_review_audit_name, 'form-group');

                            $mrk .= '</td>';

                        }
                        elseif($data['data']['assesmentData'] ->  audit_status_id == ASSESMENT_TIMELINE_ARRAY[2]['status_id'])
                        {   
                            
                            $mrk .= '<td>';

                                if(is_array(AUDIT_STATUS_ARRAY['audit_review_action']) && sizeof(AUDIT_STATUS_ARRAY['audit_review_action']) > 0 )
                                {
                                    $markup = FormElements::generateSelect([
                                        "id" => $gl_input_action_review_audit_name, "name" => $gl_input_action_review_audit_name,
                                        "options" => AUDIT_STATUS_ARRAY['audit_review_action'],
                                        "selected" => $data['request'] -> input('user_type', isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id : "")
                                    ]);
                
                                }
                                else    
                                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                
                
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_action_review_audit_name);

                            $mrk .= '</td>';

                            $mrk .= '<td>';
                                $markup = FormElements::generateTextArea([
                                    "id" => $gl_input_comment_review_audit_name, "name" => $gl_input_comment_review_audit_name, "type" => "text", "value" => $data['request'] -> input($gl_input_comment_review_audit_name, isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_reviewer_comment) ? $data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_reviewer_comment : ""), 
                                    "placeholder" => "Comment",
                                    "rows" => 2,
                                ]);
            
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_comment_review_audit_name, 'form-group');

                            $mrk .= '</td>';
                        }

                        // showing timeline 
                        if($branchPosition)
                        {
                            $branchPosiTimelineCnt = 1;

                            if(isset($data['data']['db_exe_branch_position'][$cGlTypeId]) && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> audit_status_id == 3 && ($data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 2 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 1 || $data['data']['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 0)) && !empty($data['data']['db_exe_branch_posi_timeline']))
                            {
                                $mrk .=
                                '<tr>
                                    <th colspan="3">Last Audit</th>
                                    <th>Reviewer Action</th>
                                    <th colspan="2">Reviewer Comment</th>
                                    <th>Status Time</th>
                                </tr>';

                                if(!empty($data['data']['db_exe_branch_posi_timeline']))
                                {
                                    foreach($data['data']['db_exe_branch_posi_timeline'] as $cId => $cTimelineData)
                                    {
                                        if(isset($data['data']['db_exe_branch_position'][$cGlTypeId] -> id) && $cTimelineData -> esbp_id  == $data['data']['db_exe_branch_position'][$cGlTypeId] -> id)
                                        {
                                            $mrk .='<tr>
                                                <td colspan="3">' . $cTimelineData -> amount . '</td>

                                                <td>' . AUDIT_STATUS_ARRAY['audit_review_action'][$cTimelineData -> audit_status_id] . '</td>
                                                
                                                <td colspan="2">' . $cTimelineData -> audit_reviewer_comment . '</td>
                                                
                                                <td>' . $cTimelineData -> created_at . '</td>
                                            </tr>';

                                            $branchPosiTimelineCnt++;
                                        }
                                    }
                                }
                            }

                            // if($saveEnable && $branchPosiTimelineCnt >= $data['data']['assesmentData'] -> audit_review_reject_limit)
                            //     $saveEnable = false;
                        }
                        
                    }
            }

            // Fresh Account Start --------------------------------------------

            if($freshAccount)
            {
                $inlineStyle = '';

                if($userType == 4 && (isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id) &&  $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 3))
                {
                    $inlineStyle = 'class="text-danger"';
                }
                
                $mrk .= '
                    <tr data-typeid= ' . $cGlTypeId . ' ' . $inlineStyle . '>
                        <td>' . $j .'</td>
                        <td>' . $gl_name . '</td>';

                // Condition for Disabling on the basis of audit_status_id
                $disabled_audit = true;
                $disabled_comp = true;
                
                if($userType == 2 && $data['userDetails']['emp_type'] == 2 && ((isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 3 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 1 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 0)) || !isset($data['data']['db_exe_fresh_account'][$cGlTypeId])))
                {
                    $disabled_audit = false;
                }
                elseif($userType == 3 && $data['userDetails']['emp_type'] == 3 && isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 3 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 1 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 0))
                {
                    $disabled_comp = false;
                }
                elseif(is_array($data['data']['db_exe_fresh_account']) && empty($data['data']['db_exe_fresh_account']))
                {
                    $disabled_audit = false;
                }
                
                // Value
                if(/*$userType == 2 && $data['userDetails']['emp_type'] == 2 && isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 3*/ 0)
                {
                    $value = "";
                }
                else
                {
                    $value = $data['request'] -> input($gl_input_fresh_name, isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> accounts) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> accounts : "");
                }

                // Compliance Comment
                if(/*$userType == 3 && $data['userDetails']['emp_type'] == 3 && isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 3*/ 0)
                {
                    $compliance = "";
                }
                else
                {
                    $compliance = $data['request'] -> input($gl_input_fresh_comp_comment_name, isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_commpliance) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_commpliance : "");
                }

                $mrk .= '<td colspan="2">';
                        $markup = FormElements::generateInput([
                            "id" => $gl_input_fresh_name, "name" => $gl_input_fresh_name, "type" => "text", "value" => $value, 
                            "placeholder" => "Number of Accounts",
                            "disabled" => $disabled_audit
                        ]);
    
                        $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_fresh_name, 'form-group');

                        if($freshAccount)
                        {
                            $branchFreshAccountCnt = 1;

                            if($data['userDetails']['emp_type'] == 2 && isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 3 && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 2 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 1 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 0)))
                            {
                                $mrk .=
                                '<tr>
                                    <th>Last Audit</th>
                                    <th>Reviewer Action</th>
                                    <th>Reviewer Comment</th>
                                    <th>Status Time</th>
                                </tr>';

                                if(empty($data['data']['db_exe_fresh_account_timeline']))
                                {
                                    $mrk .=
                                    '<tr>
                                        <td>' . $data['data']['db_exe_fresh_account'][$cGlTypeId] -> accounts . '</td>

                                        <td>' . AUDIT_STATUS_ARRAY['audit_review_action'][$data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id] . '</td>
                                        
                                        <td>' . $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_reviewer_comment . '</td>

                                        <td>' . $data['data']['db_exe_fresh_account'][$cGlTypeId] -> updated_at . '</td>
                                    </tr>';
                                }

                                if(!empty($data['data']['db_exe_fresh_account_timeline']))
                                {
                                    foreach($data['data']['db_exe_fresh_account_timeline'] as $cId => $cTimelineData)
                                    {
                                        if(isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> id) && ($cTimelineData -> esfa_id  == $data['data']['db_exe_fresh_account'][$cGlTypeId] -> id))
                                        {
                                            $mrk .='
                                            <tr>
                                                <td>' . $cTimelineData -> accounts . '</td>
            
                                                <td>' . AUDIT_STATUS_ARRAY['audit_review_action'][$cTimelineData -> audit_status_id] . '</td>
                                                
                                                <td>' . $cTimelineData -> audit_reviewer_comment . '</td>
            
                                                <td>' . $cTimelineData -> created_at . '</td>
                                            </tr>';

                                            $branchFreshAccountCnt++;
                                        }

                                    }
                                }
                            }

                            if($saveEnable && $branchFreshAccountCnt >= $data['data']['assesmentData'] -> audit_review_reject_limit)
                                $saveEnable = false;
                        }
                    $mrk .= '</td>';
                    
                    // <td> only for emp type = 3
                    if($userType == 3)
                    {
                        $mrk .= '<td>';
                            $markup = FormElements::generateTextArea([
                                "id" => $gl_input_fresh_comp_comment_name, "name" => $gl_input_fresh_comp_comment_name, "type" => "text", "value" => $compliance, 
                                "placeholder" => "Compliance Comment",
                                "rows" => 1,
                                "disabled" => $disabled_comp,
                            ]);
        
                            $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_fresh_comp_comment_name, 'form-group');
                        
                            if($freshAccount)
                            {
                                $branchFreshAccountCnt = 1;

                                if($data['userDetails']['emp_type'] == 3 && isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 3 && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 1 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 2 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 3)))
                                {
                                    $mrk .=
                                    '<tr>
                                        <th colspan="2">Last Compliance</th>
                                        <th>Reviewer Action</th>
                                        <th>Reviewer Comment</th>
                                        <th>Status Time</th>
                                    </tr>';

                                    if(empty($data['data']['db_exe_fresh_account_timeline']))
                                    {
                                        $mrk .= 
                                        '<tr>                                      
                                            <td colspan="2">' . $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_commpliance . '</td>

                                            <td>' . EXE_COM_STATUS_ARRAY[$data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id] . '</td>
                                            
                                            <td>' . $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_reviewer_comment . '</td>

                                            <td>' . $data['data']['db_exe_fresh_account'][$cGlTypeId] -> updated_at . '</td>
                                        </tr>';
                                    }

                                    if(!empty($data['data']['db_exe_fresh_account_timeline']))
                                    {
                                        foreach($data['data']['db_exe_fresh_account_timeline'] as $cId => $cTimelineData)
                                        {
                                            if(isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> id) && ($cTimelineData -> esfa_id  == $data['data']['db_exe_fresh_account'][$cGlTypeId] -> id) && $cTimelineData -> compliance_status_id != 0)
                                            {
                                                $mrk .='
                                                <tr>
                                                    <td colspan="2">' . $cTimelineData -> audit_commpliance . '</td>
                
                                                    <td>' . EXE_COM_STATUS_ARRAY[$cTimelineData -> compliance_status_id] . '</td>
                                                    
                                                    <td>' . $cTimelineData -> compliance_reviewer_comment . '</td>
                
                                                    <td>' . $cTimelineData -> created_at . '</td>
                                                </tr>';

                                                $branchFreshAccountCnt++;
                                            }
                                        }
                                    }
                                }

                                if($saveEnable && $branchFreshAccountCnt >= $data['data']['assesmentData'] -> compliance_review_reject_limit)
                                    $saveEnable = false;
                            }

                        $mrk .= '</td>';
                    }
                    // <td> only for emp type = 4
                    elseif($userType == 4)
                    {
                        if($data['data']['assesmentData'] ->  audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'])
                        {
                            $mrk .= '<td>';
                                $markup = FormElements::generateTextArea([
                                    "id" => $gl_input_branch_comp_comment_name, "name" => $gl_input_branch_comp_comment_name, "type" => "text", "value" => $data['request'] -> input($gl_input_branch_comp_comment_name, isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_commpliance) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_commpliance : ""),
                                    "rows" => 1,
                                    "disabled" => true,
                                ]);
            
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_comment_review_audit_name, 'form-group');

                            $mrk .= '</td>';

                            $mrk .= '<td>';

                                if(is_array(EXE_COM_STATUS_ARRAY) && sizeof(EXE_COM_STATUS_ARRAY) > 0 )
                                {
                                    $markup = FormElements::generateSelect([
                                        "id" => $gl_input_action_review_compliance_name, "name" => $gl_input_action_review_compliance_name,
                                        "options" => EXE_COM_STATUS_ARRAY,
                                        "selected" => $data['request'] -> input('user_type', isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id : "")
                                    ]);
                
                                }
                                else    
                                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                
                
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_action_review_audit_name);

                            $mrk .= '</td>';

                            $mrk .= '<td>';
                                $markup = FormElements::generateTextArea([
                                    "id" => $gl_input_comment_review_compliance_name, "name" => $gl_input_comment_review_compliance_name, "type" => "text", "value" => $data['request'] -> input($gl_input_comment_review_compliance_name, isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_reviewer_comment) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_reviewer_comment : ""), 
                                    "placeholder" => "Comment",
                                    "rows" => 1,
                                ]);
            
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_comment_review_audit_name, 'form-group');

                            $mrk .= '</td>';
                        }
                        elseif($data['data']['assesmentData'] ->  audit_status_id == ASSESMENT_TIMELINE_ARRAY[2]['status_id'])
                        {
                            $mrk .= '<td>';

                                if(is_array(AUDIT_STATUS_ARRAY['audit_review_action']) && sizeof(AUDIT_STATUS_ARRAY['audit_review_action']) > 0 )
                                {
                                    $markup = FormElements::generateSelect([
                                        "id" => $gl_input_action_review_audit_name, "name" => $gl_input_action_review_audit_name,
                                        "options" => AUDIT_STATUS_ARRAY['audit_review_action'],
                                        "selected" => $data['request'] -> input('user_type', isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id : "")
                                    ]);
                
                                }
                                else    
                                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');
                
                
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_action_review_audit_name);

                            $mrk .= '</td>';

                            $mrk .= '<td>';
                                $markup = FormElements::generateTextArea([
                                    "id" => $gl_input_comment_review_audit_name, "name" => $gl_input_comment_review_audit_name, "appendClass" => "deposit_value", "type" => "text", "value" => $data['request'] -> input($gl_input_comment_review_audit_name, isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_reviewer_comment) ? $data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_reviewer_comment : ""), 
                                    "placeholder" => "Comment",
                                    "rows" => 2,
                                ]);
            
                                $mrk .= FormElements::generateFormGroup($markup, $data, $gl_input_comment_review_audit_name, 'form-group');

                            $mrk .= '</td>';
                        }

                        if($freshAccount)
                        {
                            $branchFreshAccountCnt = 1;

                            if(isset($data['data']['db_exe_fresh_account'][$cGlTypeId]) && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> audit_status_id == 3 && ($data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 2 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 1 || $data['data']['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 0)) && !empty($data['data']['db_exe_fresh_account_timeline']))
                            {
                                $mrk .=
                                '<tr>
                                    <th colspan=2>Last Audit</th>
                                    <th>Reviewer Action</th>
                                    <th colspan=2>Reviewer Comment</th>
                                    <th>Status Time</th>
                                </tr>';

                                if(!empty($data['data']['db_exe_fresh_account_timeline']))
                                {
                                    foreach($data['data']['db_exe_fresh_account_timeline'] as $cId => $cTimelineData)
                                    {
                                        if(isset($data['data']['db_exe_fresh_account'][$cGlTypeId] -> id) && ($cTimelineData -> esfa_id  == $data['data']['db_exe_fresh_account'][$cGlTypeId] -> id))
                                        {
                                            $mrk .='
                                            <tr>
                                                <td colspan=2>' . $cTimelineData -> accounts . '</td>
            
                                                <td>' . AUDIT_STATUS_ARRAY['audit_review_action'][$cTimelineData -> audit_status_id] . '</td>
                                                
                                                <td colspan=2>' . $cTimelineData -> audit_reviewer_comment . '</td>
            
                                                <td>' . $cTimelineData -> created_at . '</td>
                                            </tr>';

                                            $branchFreshAccountCnt++;
                                        }

                                    }
                                }
                            }

                            // if($saveEnable && $branchFreshAccountCnt >= $data['data']['assesmentData'] -> audit_review_reject_limit)
                            //     $saveEnable = false;
                        }
                    }
            }                
                $mrk .= '</tr>';
            $j++;
        }

        echo "<tr class='bg-light-gray'>
                <td class='text-primary'><strong>" . $alphaIndex . "</strong></td>
                <td class='text-primary'><strong>" . string_operations($cGlType, 'upper') . "</strong></td>";
                if($branchPosition == 1)
                {
                    echo "<td class='text-center text-primary'><strong id= '" . string_operations($cGlType) . "_total'>" . get_decimal($total_march, 2) ."</strong></td>
                    <td class='text-center text-primary'><strong id='" . string_operations($cGlType) . "_total_current'>" . get_decimal($total_current, 2) . "</strong></td>
                    <td class='text-center text-primary'><strong id='ytm_id_" . string_operations($cGlType) . "'>" . get_decimal($total_ytd, 2) ."</strong></td>";
                }
                elseif($freshAccount == 1)
                {
                    echo"<td colspan='2'></td>";
                    
                }

                    if($userType == 4)
                    {
                        if($data['data']['assesmentData'] ->  audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'])
                        {
                            echo"<td></td>
                            <td></td>
                            <td></td>"; 
                        }
                        else
                        {
                            echo"<td></td>
                            <td></td>";
                        }
                    }
                    elseif($userType == 3)
                    {
                        echo"<td></td>";
                    }
        echo"</tr>"; 
            
        echo $mrk;
    }

    return $saveEnable;
}
?>