<?php

function generate_table_markup_assesment($data, $action = true)
{
    $srNo = 1;

    $tableMrk = '<table class="table table-bordered v-table mb-0">' . "\n";

        $tableMrk .= '<tr>
            <th>Sr. No.</th>
            <th>Assesment Details</th>
            <th>Audit Status</th>
            <th>Compliance Status</th>';

            if($action)
               $tableMrk .= '<th>Action</th>' . "\n";

        $tableMrk .= '</tr>' . "\n";

        if(!is_array($data['data']['db_data']))
        {
            $tempDBData = $data['data']['db_data'];
            
            $data['data']['db_data'] = [];
            $data['data']['db_data'][ $tempDBData -> id ] = $tempDBData;

            unset($tempDBData);
        }

        foreach($data['data']['db_data'] as $cAssesmentDetails)
        {
            
            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td>' . $srNo++ . '</td>' . "\n";

                $tableMrk .= '<td>' . "\n";

                    $tableMrk .= '<h4 class="lead text-primary font-medium mb-1">'. string_operations( (is_array($data['data']['audit_unit_data']) && array_key_exists($cAssesmentDetails -> audit_unit_id, $data['data']['audit_unit_data']) ? $data['data']['audit_unit_data'][ $cAssesmentDetails -> audit_unit_id ] -> combined_name : ''), 'upper' ) .'</h4>' . "\n";

                    $tableMrk .= $cAssesmentDetails -> assesment_period_from . ' - ' . $cAssesmentDetails -> assesment_period_to . ' <span class="d-inline-block text-secondary font-sm">( Frequency - '. $cAssesmentDetails -> frequency .' Months )</span>';

                    if( in_array($cAssesmentDetails -> audit_status_id, [
                        ASSESMENT_TIMELINE_ARRAY[1]['status_id'], 
                        ASSESMENT_TIMELINE_ARRAY[2]['status_id'], 
                        ASSESMENT_TIMELINE_ARRAY[3]['status_id']]
                    )):
                        $tableMrk .= '<p class="font-sm mb-0 text-danger font-medium">Audit Due Date: '. $cAssesmentDetails -> audit_due_date .'</p>' . "\n";

                    elseif( in_array($cAssesmentDetails -> audit_status_id, [
                        ASSESMENT_TIMELINE_ARRAY[4]['status_id'], 
                        ASSESMENT_TIMELINE_ARRAY[5]['status_id'], 
                        ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                    ])):
                        $tableMrk .= '<p class="font-sm mb-0 text-danger font-medium">Compliance Due Date: '. $cAssesmentDetails -> compliance_due_date .'</p>' . "\n";
                    endif;
                    
                $tableMrk .= '</td>' . "\n";

                // audit status
                $tableMrk .= '<td>' . "\n";

                    $tempStatus = 'Pending';

                    if($cAssesmentDetails -> audit_status_id > 3)
                        $tempStatus = string_operations($data['noti']::getNoti('auditCompletedShort'), 'upper');
                    else
                    {
                        if(in_array( $cAssesmentDetails -> audit_status_id, [ 
                            ASSESMENT_TIMELINE_ARRAY[1]['status_id'], 
                            ASSESMENT_TIMELINE_ARRAY[2]['status_id'], 
                            ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                        ] ))
                            $tempStatus = ASSESMENT_TIMELINE_ARRAY[ $cAssesmentDetails -> audit_status_id ]['title'];

                        if( $cAssesmentDetails -> is_limit_blocked && $cAssesmentDetails -> audit_status_id <= 3 )
                            $tempStatus .= '<span class="d-block font-sm text-secondary text-danger">Status: '. string_operations($data['noti']::getNoti('auditBlockedShort'), 'upper') . '</span>';
                        else if( in_array( $cAssesmentDetails -> audit_status_id, [ 
                                    ASSESMENT_TIMELINE_ARRAY[1]['status_id'], 
                                    ASSESMENT_TIMELINE_ARRAY[2]['status_id'], 
                                    ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                            ] ) && strtotime($cAssesmentDetails -> audit_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1])))
                            $tempStatus .= '<span class="d-block font-sm text-secondary text-danger">Status: ' . string_operations($data['noti']::getNoti('auditDueExpiredShort'), 'upper') . '</span>';
                    }

                    $tableMrk .= $tempStatus;

                $tableMrk .= '</td>' . "\n";

                // compliance status
                $tableMrk .= '<td>' . "\n";

                    $tempStatus = '-';

                    if($cAssesmentDetails -> audit_status_id > 6 )
                        $tempStatus = string_operations($data['noti']::getNoti('complianceCompletedShort'), 'upper');
                    else
                    {
                        if(in_array( $cAssesmentDetails -> audit_status_id, [ 
                            ASSESMENT_TIMELINE_ARRAY[4]['status_id'], 
                            ASSESMENT_TIMELINE_ARRAY[5]['status_id'], 
                            ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                        ] ))
                            $tempStatus = ASSESMENT_TIMELINE_ARRAY[ $cAssesmentDetails -> audit_status_id ]['title'];

                        if( $cAssesmentDetails -> is_limit_blocked && $cAssesmentDetails -> audit_status_id > 3 )
                            $tempStatus .= '<span class="d-block font-sm text-secondary text-danger">Status: '. string_operations($data['noti']::getNoti('complianceBlockedShort'), 'upper') . '</span>';
                        else if( in_array( $cAssesmentDetails -> audit_status_id, [ 
                                ASSESMENT_TIMELINE_ARRAY[4]['status_id'], ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                            ] ) && strtotime($cAssesmentDetails -> compliance_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1])))
                            $tempStatus .= '<span class="d-block font-sm text-secondary text-danger">Status: ' . string_operations($data['noti']::getNoti('complianceDueExpiredShort'), 'upper') . '</span>';
                    }

                    $tableMrk .= $tempStatus;

                $tableMrk .= '</td>' . "\n";

                if($action)
                {
                    // action
                    $tableMrk .= '<td>' . "\n";

                        if( !($cAssesmentDetails -> audit_status_id > 6) )
                        {
                            // not completed
                            $tableMrk .= generate_link_button('link', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/assesment-view/' . encrypt_ex_data($cAssesmentDetails -> id), 'extra' => view_tooltip('View') ]);
                        }

                    $tableMrk .= '</td>' . "\n";
                }

                $tableMrk .= '</tr>' . "\n";
            
            if(!$action):
            
            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Audit Start Date</td>' . "\n";
                $tableMrk .= '<td>'. ( $cAssesmentDetails -> audit_start_date ?? '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Audit End Date</td>' . "\n";
                $tableMrk .= '<td>'. ( $cAssesmentDetails -> audit_end_date ?? '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Compliance Start Date</td>' . "\n";
                $tableMrk .= '<td>'. ( $cAssesmentDetails -> compliance_start_date ?? '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Compliance End Date</td>' . "\n";
                $tableMrk .= '<td>'. ( $cAssesmentDetails -> compliance_end_date ?? '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Audior</td>' . "\n";
                $tableMrk .= '<td>'. (isset($cAssesmentDetails -> audit_head_details) ? string_operations(($cAssesmentDetails -> audit_head_details -> name . ' ( EMP. ' . $cAssesmentDetails -> audit_head_details -> emp_code . ' )'), 'upper') : '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Branch Head</td>' . "\n";
                $tableMrk .= '<td>'. (isset($cAssesmentDetails -> branch_head_details) ? string_operations(($cAssesmentDetails -> branch_head_details -> name . ' ( EMP. ' . $cAssesmentDetails -> branch_head_details -> emp_code . ' )'), 'upper') : '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Branch Sub-Head</td>' . "\n";
                $tableMrk .= '<td>'. (isset($cAssesmentDetails -> branch_subhead_details) ? string_operations(($cAssesmentDetails -> branch_subhead_details -> name . ' ( EMP. ' . $cAssesmentDetails -> branch_subhead_details -> emp_code . ' )'), 'upper') : '-' ) .'</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            $tableMrk .= '<tr>' . "\n";

                $tableMrk .= '<td></td>' . "\n";
                $tableMrk .= '<td>Other Compliance Employees</td>' . "\n";
                $tableMrk .= '<td>';
                
                if(isset($cAssesmentDetails -> multi_compliance_array) && sizeof($cAssesmentDetails -> multi_compliance_array) > 0)
                {
                    $tempStr = '';

                    foreach($cAssesmentDetails -> multi_compliance_array as $cEmpDetails)
                        $tempStr .= string_operations(($cEmpDetails -> name . ' ( EMP. ' . $cEmpDetails -> emp_code . ' ), '), 'upper'); 

                    $tableMrk .= substr($tempStr, 0, -2);
                }
                else
                    $tableMrk .= '-';
                
                $tableMrk .= '</td>' . "\n";
                $tableMrk .= '<td></td>' . "\n";

            $tableMrk .= '</tr>' . "\n";

            endif;
        }

    $tableMrk .= '</table>' . "\n";

    return $tableMrk;
}

?>