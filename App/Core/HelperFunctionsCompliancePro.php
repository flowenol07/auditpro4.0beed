<?php
use Core\SiteUrls;
// function for generate bulk batch key
if(!function_exists('generate_bulk_batch_key_compliance_pro'))
{
    function generate_bulk_batch_key_compliance_pro($extra) {

        $extra['from'] = string_operations($extra['from'], 'replace', ['-', '']);
        $extra['to'] = string_operations($extra['to'], 'replace', ['-', '']);
        return 'BBK_' . $extra['from'] . '_' . $extra['to'] . '_' . $extra['circular_id'] . '_' . $extra['task_set_id'];
    }
}

// function for get mixed circular data
if(!function_exists('get_com_circular_details'))
{
    function get_com_circular_details($this_obj, $filter = [], $extra = [])
    {
        $model = $this_obj -> model('ComplianceCircularSetModel');

        $query = "SELECT ccsm.*, 
                COALESCE(cca.name, '". ERROR_VARS['notFound'] ."') AS auth_name FROM com_circular_set_master ccsm JOIN 
                com_circular_authority cca ON ccsm.authority_id = cca.id";

        $circularData = get_all_data_query_builder(1, $model, 'com_circular_set_master', $filter, 'sql', $query);

        if(isset($extra['needDocs']) && is_object($circularData))
        {
            $temp = [ 'circulr_id' => $circularData -> id ];

            if(isset($extra['type']))
                $temp['type'] = $extra['type'];

            // get circular docs
            $multiDocsData = get_multi_docs_data($this_obj, 1, $temp);

            if( is_array($multiDocsData) && sizeof($multiDocsData) > 0 )
                $circularData -> multi_docs = $multiDocsData;
        }

        return $circularData;
    }
}

// COMPLIANCE PRO BASIC FUNCTIONS ------------------------------

if(!function_exists('check_compliance_pro_strict'))
{
    function check_compliance_pro_strict()
    {
        // check defined in init
        return defined( 'COMPLIANCE_PRO_ARRAY' );
    }
}

if(!function_exists('check_compliance_data_strict'))
{
    function check_compliance_data_strict($this_obj, $disable = false, $yearCheck = false, $yearId = 0, $singleRecord = false)
    {
        return true;
        
        // $details_of_asses_data = '';

        // if($disable == true)
        // {
        //     $model = $this_obj -> model('AuditAssesmentModel');

        //     if($yearCheck)
        //         $select = 'SELECT aam.year_id FROM audit_assesment_master aam WHERE year_id = ' . $yearId;
        //     else    
        //         $select = 'SELECT * FROM audit_assesment_master aam';

        //     $singleRecord = $singleRecord ? 1 : 2;
        //     $details_of_asses_data = get_all_data_query_builder($singleRecord, $model, 'audit_assesment_master', [], 'sql', $select);
        // }

        // return $details_of_asses_data;
    }
}

if(!function_exists('generate_hidden_docs_upload_form'))
{
    function generate_hidden_docs_upload_form( /*$type = null*/ )
    {
        $res = null;

        if( check_compliance_pro_strict() )
        {
            $res = '<form id="com_docs_upload_form" style="display:none;" enctype="multipart/form-data" data-action="'. COMPLIANCE_PRO_ARRAY['compliance_docs_array']['control_url'] .'upload">
                <input type="file" name="com_docs_file" id="com_docs_file" />
            </form>' . "\n";
        }

        return $res;
    }
}

if(!function_exists("get_compliance_assesment_details"))
{
    function get_compliance_assesment_details($this_obj, $empId, $comId)
    {
        // get assesment model
        $model = $this_obj -> model('ComplianceCircularAssesMasterModel');;
        $errData = null;

        // method call
        $comAssesData = $model -> getSingleCircularComplianceMaster([
            'where' => 'id = :id AND is_limit_blocked = 0 AND deleted_at IS NULL',
            'params' => [ 'id' => $comId ]
        ]);

        if(!is_object($comAssesData))
            $errData = 'errorFinding';

        if(!empty($errData))
            return $errData;

        // check expired audit / compliance
        if($comAssesData -> com_status_id >= 4)
        {
            // for compliance
            if( empty($comAssesData -> compliance_due_date) || 
              !(strtotime($comAssesData -> compliance_due_date) >= strtotime(date($GLOBALS['dateSupportArray'][1]))) )
            {
                $errData = 'complianceDueExpired';
                $comAssesData = null;
            }
        }

        // find employee details
        if(empty($errData))
        {
            $model = $this_obj -> model('EmployeeModel');
            $empDetails = get_all_data_query_builder(1, $model, $model -> getTableName(), [
                'where' => 'id = :emp_id AND is_active = 1 AND deleted_at IS NULL', 
                'params' => [ 'emp_id' => $empId ]
            ], 'sql', "SELECT id, user_type_id, emp_code, gender, name, designation, audit_unit_authority FROM " . $model -> getTableName());

            // for audit OR Reviewer
            if(is_object($empDetails) && $empDetails -> user_type_id == 6)
            {
                $audit_unit_authority = !empty($empDetails -> audit_unit_authority) ? explode(',', $empDetails -> audit_unit_authority) : [];

                if(  is_array($audit_unit_authority) && 
                    !in_array($comAssesData -> audit_unit_id, $audit_unit_authority))
                    $errData = 'noAssesmentAuthority';

                if($empDetails -> user_type_id == 4 && $errData == 'noAssesmentAuthority')
                    $errData = 'noReviewerAuthority';
            }

            // for compliance user
            elseif(is_object($empDetails) && $empDetails -> user_type_id == 3)
            {
                // print_r($empDetails);
                if( $comAssesData -> branch_head_id != $empDetails -> id && 
                    $comAssesData -> branch_subhead_id != $empDetails -> id )
                    $errData = 'noComplianceAuthority';
                
                if($errData == 'noComplianceAuthority' && !empty($comAssesData -> multi_compliance_ids))
                {
                    // check for multi compliance
                    $multi_compliance_ids = explode(',', $comAssesData -> multi_compliance_ids);

                    if( is_array($multi_compliance_ids) && 
                        !in_array($empDetails -> id, $multi_compliance_ids))
                        $errData = 'noComplianceAuthority';
                    else 
                        $errData = null;
                }
            }
        }    
        
        if(!empty($errData))
        {
            // return error message
            return $errData;
        }
        else
        {
            $comAssesData -> current_emp_details = $empDetails;

            $comAssesData -> audit_unit_id_details = null;

            if(!empty($comAssesData -> audit_unit_id))
            {
                // find audit unit
                $model = $this_obj -> model('AuditUnitModel');

                $comAssesData -> audit_unit_id_details = get_all_data_query_builder(1, $model, $model -> getTableName(), [
                    'where' => 'id = :id AND deleted_at IS NULL', 
                    'params' => [ 'id' => $comAssesData -> audit_unit_id ]
                ], 'sql', "SELECT id, section_type_id, audit_unit_code, name, branch_head_id, branch_subhead_id, multi_compliance_ids, frequency FROM " . $model -> getTableName());
            }
        }

        return $comAssesData;
    }
}

if(!function_exists('generate_compliance_asses_top_markup'))
{
    // function for generate top markup
    function generate_compliance_asses_top_markup($comAssesData, $type = 1, $extra = []) {
        
        $mrk_str = '';

        $mrk_str .= '<div class="card apcard mb-4">' . "\n";
            $mrk_str .= '<div class="card-header">Compliance Details</div>' . "\n";
    
            $mrk_str .= '<div class="card-body border border-top-0">' . "\n";

                $brachObj = null;
                $assessTimelineCnt = 1;
                $cFreqStr = isset(COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $comAssesData -> frequency_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $comAssesData -> frequency_id ]['title'] : ERROR_VARS['notFound'];
                $cFreqStr = string_operations($cFreqStr, 'upper');

                if( isset($comAssesData -> audit_unit_id_details) && is_object($comAssesData -> audit_unit_id_details) )
                    $brachObj = $comAssesData -> audit_unit_id_details;

                $mrk_str .= '<h5 class="font-medium site-purple mb-1">'. string_operations( ( is_object($brachObj) ? $brachObj -> name : ERROR_VARS['notFound'] ), 'upper') . ' <span class="d-inline-block">( BR. CODE: '. trim_str( is_object($brachObj) ? $brachObj -> audit_unit_code : ERROR_VARS['notFound'] ) .' )</span>' .'</h5>' . "\n";

                $mrk_str .=  '<p class="mb-1">Compliance Period: '. trim_str($comAssesData -> com_period_from) . ' To ' . trim_str($comAssesData -> com_period_to) . ' <span class="d-inline-block font-sm text-secondary">( Frequency: '. $cFreqStr .' )</span>' .'</p>' . "\n";

                $mrk_str .= '<div class="row mt-3">' . "\n";

                    $col = 'col-md-6' . ' mb-2';

                    // for compliance
                    $mrk_str .= '<div class="'. $col . ' assess-timeline-container">' . "\n";
                        $mrk_str .= '<span class="assess-timeline">'. $assessTimelineCnt++ .'</span>' . "\n";
                        $mrk_str .= '<div class="border h-100 d-flex align-items-center justify-content-center '. ( ($comAssesData -> com_status_id == 2 || $comAssesData -> com_status_id > 3) ? 'bg-success text-white border-success' : ( in_array($comAssesData -> com_status_id, [1, 3]) ? 'bg-light-gray' : '' ) ) .' text-center">' . "\n";

                            $mrk_str .=  '<div>' . "\n";

                            $mrk_str .= '<p class="font-sm mb-0">COMPLIANCE STATUS</p>' . "\n";

                            $tempStatus = string_operations('Completed', 'upper');

                            if( array_key_exists($comAssesData -> com_status_id, COMPLIANCE_PRO_ARRAY['timeline_compliance_status']) && 
                                in_array($comAssesData -> com_status_id, [1,3]))
                                $tempStatus = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ $comAssesData -> com_status_id ]['title'];

                            // if( !($comAssesData -> com_status_id > 3) ) $tempStatus = '<span class="text-light-gray">' . ERROR_VARS['notAvailable'] . '</span>';

                            $mrk_str .= '<p class="font-bold mb-0">'. $tempStatus .'</p>' . "\n";

                            $mrk_str .=  '</div>' . "\n";

                        $mrk_str .= '</div>' . "\n";
                    $mrk_str .= '</div>' . "\n";

                    // for review compliance
                    if(array_key_exists('4', $GLOBALS['userTypesArray']))
                    {
                        $mrk_str .= '<div class="'. $col . ' assess-timeline-container">' . "\n";
                            $mrk_str .= '<span class="assess-timeline">'. $assessTimelineCnt++ .'</span>' . "\n";
                            $mrk_str .= '<div class="border h-100 d-flex align-items-center justify-content-center '. ( $comAssesData -> com_status_id == 4 ? 'bg-success text-white border-success' : ( in_array($comAssesData -> com_status_id, [2]) ? 'bg-light-gray' : '' ) ) .' text-center">' . "\n";

                                $mrk_str .=  '<div>' . "\n";

                                $mrk_str .= '<p class="font-sm mb-0">COMPLIANCE REVIEW STATUS</p>' . "\n";

                                $tempStatus = string_operations('Completed', 'upper');

                                if(in_array($comAssesData -> com_status_id, [2]))
                                    $tempStatus = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 2 ]['title'];

                                if( !($comAssesData -> com_status_id > 3) ) $tempStatus = '<span class="text-light-gray">' . ERROR_VARS['notAvailable'] . '</span>';

                                $mrk_str .= '<p class="font-bold mb-0">'. $tempStatus .'</p>' . "\n";

                                $mrk_str .=  '</div>' . "\n";

                            $mrk_str .= '</div>' . "\n";
                        $mrk_str .= '</div>' . "\n";
                    }

                $mrk_str .= '</div>' . "\n";

            // display circular details
            if( isset($extra['circular_data_show']) && 
                isset($extra['circular_data']) && 
                is_object($extra['circular_data']))
            {
                $mrk_str .= '<div class="table-responsive mt-3">' . "\n";
                    $mrk_str .= '<table class="table table-bordered v-table mb-0">' . "\n";

                    $mrk_str .= '<tr class="bg-light-gray"><th colspan="4">Circular Details</th></tr>' . "\n";
                    $mrk_str .= '<tr>
                        <td colspan="4">
                            <p class="font-medium text-primary lead mb-0">'. $extra['circular_data'] -> name .'</p>' . "\n";

                            if(!empty($extra['circular_data'] -> description)):
                                $mrk_str .= '<p class="font-sm mb-2"><span class="font-medium">Circular Description: </span>'. (!empty($extra['circular_data'] -> description) ? $extra['circular_data'] -> description : '') .'</p>' . "\n";
                            endif;

                            $mrk_str .= '<p class="text-secondary font-sm mb-0">Status: '. check_active_status($extra['circular_data'] -> is_active) .', Created: '. date($GLOBALS['dateSupportArray'][2], strtotime($extra['circular_data'] -> created_at)) .'</p>                  
                        </td>
                    </tr>' . "\n";

                    $mrk_str .= '<tr>
                        <td class="font-medium">Circular No.</td>
                        <td>'. (!empty( $extra['circular_data'] -> ref_no ) ? $extra['circular_data'] -> ref_no : '-') .'</td>

                        <td class="font-medium">Authority</td>
                        <td>'. $extra['circular_data'] -> auth_name .'</td>
                    </tr>' . "\n";

                    $dueDate = $comAssesData -> compliance_due_date;

                    if(in_array($comAssesData -> com_status_id, [1,3]))
                    {
                        $mrk_str .= '<tr class="blink-tr">
                            <td class="font-medium">Due Date</td>
                            <td colspan="4">'. $dueDate .'</td>
                        </tr>' . "\n";
                    }

                    $mrk_str .= '</table>
                </div>' . "\n";

                if( 1/*isset($data['data']['cco_docs_true'])*/ )
                {     
                    $docsMrk = generate_circular_docs_markup($extra['circular_data'], [ 'container' => 1, 'mt' => 1 ]);

                    $extra = [ 'mt' => 1, 'circular_id' => $extra['circular_data'] -> id ];

                    if(!empty($docsMrk))
                        $mrk_str .= $docsMrk;
                }
            }

            $mrk_str .= '</div>' . "\n";
        $mrk_str .= '</div>' . "\n";

        return $mrk_str;
    }
}

if(!function_exists('compliance_pro_save_assesment_message'))
{
    function compliance_pro_save_assesment_message($this_obj, $empId, $requestData, $type = 'com', $changeBatchKey = false)
    {
        $res_array = ['msg' => 'somethingWrong', 'res' => 'err'];

        $res_array['msg'] = json_encode($requestData);

        $requestData = json_decode($requestData);

        $checkActionVal = false;
        $updateArray = [];

        if( $type == 'com' && !isset( $requestData -> compliance ) )
            $res_array['msg'] = 'validCompliance';

        elseif( $type == 'com' && isset( $requestData -> compliance ) )
        {
            $updateArray = [ 'compliance' => $requestData -> compliance, 'compliance_emp_id' => $empId ];

            if($changeBatchKey) //change batch_key
                $updateArray['batch_key'] = $this_obj -> comAssesData -> batch_key;

            $checkActionVal = true;
        }

        elseif( $type == 'com_rew' && !isset( $requestData -> comment ) )
            $res_array['msg'] = 'validReviewComment';

        elseif( $type == 'com_rew' && isset( $requestData -> comment ) )
        {
            $updateArray[ 'compliance_reviewer_comment' ] = $requestData -> comment;
            $updateArray[ 'compliance_reviewer_emp_id' ] = $empId;
            $checkActionVal = true;
        }

        if( $checkActionVal && sizeof($updateArray) > 0 &&
          ( isset($requestData -> ans_id) && !empty($requestData -> ans_id) ) &&
          ( isset($requestData -> ans_type) && !empty($requestData -> ans_type) ) &&
            in_array( $requestData -> ans_type, ['gen', 'annex'] )
          )
        {          
            if($requestData -> ans_type == 'gen')
		    {
                // general answer
                $model = $this_obj -> model('ComplianceCircularAnswerDataModel');
            }
            // else
            // {
            //     // annex answer
            //     $model = $this_obj -> model('AnswerDataAnnexureModel');
            // }

            $whereData = [
                'where' => 'id = :id AND com_master_id = :com_master_id',
                'params' => [ 
                    'id' =>  decrypt_ex_data($requestData -> ans_id),
                    'com_master_id' => $this_obj -> comAssesData -> id
                ]
            ];

            // $res_array['msg'] = json_encode($updateArray);

            $result = $model::update(
                $model -> getTableName(), $updateArray, $whereData
            );

            if($result)
            {
                if($type == 'com')
                    $res_array['msg'] = 'auditComplianceSuccess';
                elseif( $type == 'com_rew' )
                    $res_array['msg'] = 'reviewCommentSuccess';
            
                $res_array['res'] = "success";
            }
            else
                $res_array['msg'] = 'errorSaving';
        }

        return $res_array;
    }
}

if(!function_exists('generate_compliance_asses_table'))
{ 
    // function for dashboard
    function generate_compliance_asses_table($data, $type = 'com')
    {
        if($type == 'review')
            $formElements = new \Core\FormElements();

        $res = [ 'mrk' => '', 'pending' => 0, 'review' => 0, 'completed' => 0, 'recompliance' => 0 ];
        $comSubmittedAssesArr = [];

        if(isset($data['request']))
        {
            $comSubmittedAssesArr = $data['request'] -> input('multi_type_check', ( isset($data['data']['dbComSubmit']) && is_object($data['data']['dbComSubmit']) ? $data['data']['dbComSubmit'] -> com_asses_ids : null ));
            $comSubmittedAssesArr = !empty($comSubmittedAssesArr) ? explode(',', $comSubmittedAssesArr) : [];
        }

        if(isset($data['selectedTaskSet']))
            $res['mrk'] .= '<h5 class="text-primary my-2">'. $data['selectedTaskSet'] -> combined_name .'</h5>';
        else
            $res['mrk'] .= '<h5 class="text-primary my-2">Financial Year: <span class="font-medium">'. $data['data']['year_data'] -> year . ' - ' . ($data['data']['year_data'] -> year + 1) .'</span></h5>';

        if( isset($data['data']['check_all_str']) && 
            !empty($data['data']['check_all_str']))
            $res['mrk'] .= $data['data']['check_all_str'];

        if( isset($data['data']['completed_count']) && $data['data']['completed_count'] > 0 )
            $res['mrk'] .= '<h4 class="lead font-medium text-primary">Total Completed: '. $data['data']['completed_count'] .'</h4>';

        $res['mrk'] .= '<div class="table-responsive-md">

                <table class="table table-bordered kp-table v-table border-top-0">
                    <thead>    
                        <tr class="top-border">' . "\n";

                        if( isset($data['selectedTaskSet']) && $type == 'review' )
                            $res['mrk'] .= '<th width="80" class="text-center">#</th>' . "\n";

                            $res['mrk'] .= '<th width="80" class="text-center">Sr. No.</th>
                            <th width="360">Compliance Details</th>';

                            if($type == 'review')
                            {
                                $res['mrk'] .= '<th width="180" class="text-center">Reporting Date</th>
                                    <th width="180" class="text-center">Due Date</th>';
                            }

                            $res['mrk'] .= '<th width="180" class="text-center">Compliance Status</th>
                            <th width="180" class="text-center">Submit Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>';
                        
                        if(is_array($data['data']['assesData']) && sizeof($data['data']['assesData']) > 0):
                            
                            $srNo = 1;

                            foreach($data['data']['assesData'] as $cAssesId => $cAssesDetails)
                            {
                                $cAssesmentId = $cAssesDetails -> id;
                                $cFreqStr = isset(COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $cAssesDetails -> frequency_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $cAssesDetails -> frequency_id ]['title'] : ERROR_VARS['notFound'];
                                $cFreqStr = string_operations($cFreqStr, 'upper');
                                $tableMrk = '';

                                $tableMrk .= '<tr>' . "\n";

                                    if( isset($data['selectedTaskSet']) && $type == 'review' )
                                    {
                                        if($cAssesDetails -> com_status_id == 4)
                                        {
                                            $checked = in_array($cAssesDetails -> id, $comSubmittedAssesArr) ? 1 : 0;

                                            $checkboxMrk = $formElements::generateCheckboxOrRadio([
                                                "appendClass" => 'multi-checkbox-ids d-inlin-block ms-4',
                                                "checked" => $checked, "value" => $cAssesDetails -> id
                                            ]);
                                        }
                                        else
                                            $checkboxMrk = '';

                                        $tableMrk .= '<td rowspan="2">'. $checkboxMrk .'</td>' . "\n";
                                    }

                                    // sr no
                                    $tableMrk .= '<td class="text-center" rowspan="2">'. $srNo .'</td>' . "\n";

                                    $tableMrk .= '<td>' . "\n";

                                        // branch details
                                        $tableMrk .= '<p class="font-medium text-primary font-sm mb-0">'. $cAssesDetails -> combined_name .'</p>' . "\n";

                                        // period
                                        $tableMrk .= '<p class="mb-0"><span class="font-medium">Compliance Period:</span> ' . $cAssesDetails -> com_period_from . ' - ' . $cAssesDetails -> com_period_to . ' <span class="text-secondary font-sm">( Frequency - '. $cFreqStr .' )</span></p>';

                                        // if( in_array($cAssesDetails -> com_status_id, [
                                        //     COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][1]['status_id'], 
                                        //     COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id'] ]
                                        // )):
                                            $tableMrk .= '<p class="font-sm mb-0 text-danger font-medium">Compliance Due Date: '. $cAssesDetails -> compliance_due_date .'</p>' . "\n";
                                        // endif;

                                    $tableMrk .= '</td>' . "\n";

                                    if($type == 'review')
                                    {
                                        $tableMrk .= '<td class="text-center">'. $cAssesDetails -> com_period_from .'</td>';
                                        $tableMrk .= '<td class="text-center">'. $cAssesDetails -> com_period_to .'</td>';
                                    }

                                    // status count update
                                    if($cAssesDetails -> com_status_id == 1) //pending
                                        $res['pending']++;
                                    else if($cAssesDetails -> com_status_id == 2) //review
                                        $res['review']++;
                                    else if($cAssesDetails -> com_status_id == 3) //recompliance
                                        $res['recompliance']++;
                                    else //completed
                                        $res['completed']++;

                                    // status
                                    $tableMrk .= '<td class="text-center">' . "\n";
            
                                        $tempStatus = string_operations('Completed', 'upper');

                                        if( array_key_exists($cAssesDetails -> com_status_id, COMPLIANCE_PRO_ARRAY['timeline_compliance_status']) && 
                                            in_array($cAssesDetails -> com_status_id, [
                                                COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][1]['status_id'], 
                                                COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id']
                                        ]))
                                            $tempStatus = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ $cAssesDetails -> com_status_id ]['title'];                                   

                                        $tableMrk .= $tempStatus;

                                        $tempStatus = string_operations('Completed', 'upper');

                                        if( in_array($cAssesDetails -> com_status_id, [ COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][2]['status_id'] ]) )
                                            $tempStatus = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 
                                                            COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][2]['status_id'] 
                                                        ]['title'];

                                        if( $cAssesDetails -> com_status_id == 2 ):
                                            $tableMrk .= '<p class="mb-0 text-danger font-sm font-medium">CCO Status: '. $tempStatus .'</p>' . "\n";
                                        endif;

                                    $tableMrk .= '</td>' . "\n";

                                    // submit status
                                    $tableMrk .= '<td class="text-center">'. (isset($cAssesDetails -> reporting_date) ? $cAssesDetails -> reporting_date : '' ) .'</td>' . "\n";

                                    // action
                                    $tableMrk .= '<td class="text-center">' . "\n";

                                        $cShowBtn = false;
                                        $cShowBtnText = '';

                                        if( !($cAssesDetails -> is_limit_blocked) )
                                        {
                                            // for compliance & re compliance
                                            if( $data['userDetails']['emp_type'] == 3 && 
                                                in_array( $cAssesDetails -> com_status_id, [ 
                                                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][1]['status_id'], 
                                                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id']
                                                ] ) && strtotime($cAssesDetails -> compliance_due_date) > strtotime(date($GLOBALS['dateSupportArray'][1])) )
                                            {
                                                $cShowBtn = true;
                                                $cShowBtnText = '';
                                                $cShowBtnText = ( $cAssesDetails -> com_status_id == COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id'] ) ? 'DO RE-COMPLIANCE' : 'DO COMPLIANCE';
                                            }

                                            // for review compliance
                                            else if( $data['userDetails']['emp_type'] == 6 && in_array( $cAssesDetails -> com_status_id, [ 
                                                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][2]['status_id']
                                                ] ))
                                            {
                                                $cShowBtn = true;
                                                $cShowBtnText = 'REVIEW COMPLIANCE';
                                            }

                                            if( $cShowBtn ):
                                                $tableMrk .= generate_link_button('link', ['value' => $cShowBtnText, 'href' => $data['siteUrls']::getUrl( 'complianceAssessment' ) . '/com-action/' . encrypt_ex_data($cAssesmentId), 'extra' => view_tooltip('View / Edit')]);
                                            endif; 
                                            
                                            if( $cAssesDetails -> com_status_id == COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][4]['status_id'] )
                                            {
                                                $tableMrk .= '<span class="d-block text-success font-medium text-uppercase">Completed</span>' . "\n";
                                            }
                                            elseif(
                                                in_array( $cAssesDetails -> com_status_id, [ 
                                                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][1]['status_id'], 
                                                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id']
                                                ] ) &&
                                                strtotime($cAssesDetails -> compliance_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1])))
                                            {
                                                $tableMrk .= '<span class="d-block text-danger font-medium text-uppercase">Compliance Period Expired</span>' . "\n";
                                            }
                                        }
                                        else
                                        {
                                            $tempStatus = 'Compliance Blocked';
                                            $tableMrk .= '<span class="d-block text-danger font-medium text-uppercase">'. $tempStatus .'</span>' . "\n";
                                        }

                                    $tableMrk .= '</td>' . "\n";

                                $tableMrk .= '</tr>' . "\n";

                                $tableMrk .= '<tr>' . "\n";

                                    // frequency 
                                    $tableMrk .= '<td'. ($type == 'review' ? ' colspan="6"' : ' colspan="4"') .' >' . "\n";

                                        // circular details
                                        $tableMrk .= '<p class="font-sm mb-0 text-secondary"><span class="site-black font-medium">Circular Details: </span>Ref No: '. $cAssesDetails -> ref_no .'</p>' . "\n";
                                        $tableMrk .= '<h6 class="font-medium text-primary mb-0">'. $cAssesDetails -> ccsm_name .'</h6>' . "\n";

                                        // set name 
                                        $tableMrk .= '<p class="font-sm mb-0"><span class="font-medium">Circular Task Set:</span> '. $cAssesDetails -> task_set_name .'</p>' . "\n";

                                    $tableMrk .= '</td>' . "\n";

                                $tableMrk .= '</tr>' . "\n";

                                $res['mrk'] .= $tableMrk;

                                $srNo++;
                            }

                        else:
                            $res['mrk'] .= '<tr><td colspan="5">'. $data['noti']::getCustomAlertNoti('noDataFound') .'</td></tr>';
                        endif;

                    $res['mrk'] .= '</tbody>
                </table>

            </div>';

        return $res;
    }
}

if(!function_exists('generate_compliance_doc_btn'))
{ 
    // function for generate_compliance_doc_btn
    function generate_compliance_doc_btn($extra, $type)
    {
        $res = '';

        if(in_array($type, [1,2,3,4,5,6,7,8]))
        {
            if(isset($extra['mt'])) $res = '<div class="w-100 mt-3"></div>' . "\n";
            
            $res .= '<button class="cco-docs-upload-btn"'. view_tooltip('Upload File');

            $res .= ' data-doc_cat_type="' . encrypt_ex_data($type) . '"';

            if(isset($extra['circular_id']))
                $res .= ' data-circulr_id="' . encrypt_ex_data($extra['circular_id']) . '"';

            if(isset($extra['task_id']))
                $res .= ' data-task_id="' . encrypt_ex_data($extra['task_id']) . '"';

            if(isset($extra['ans_id']))
                $res .= ' data-ans_id="' . encrypt_ex_data($extra['ans_id']) . '"';

            if(isset($extra['annex_id']))
                $res .= ' data-annex_id="' . encrypt_ex_data($extra['annex_id']) . '"';

            if(isset($extra['com_asses_id']))
                $res .= ' data-com_asses_id="' . encrypt_ex_data($extra['com_asses_id']) . '"';

            if(isset($extra['submit_auth_id']))
                $res .= ' data-submit_auth_id="' . encrypt_ex_data($extra['submit_auth_id']) . '"';

            if(isset($extra['bbk']))
                $res .= ' data-com_bbk="' . encrypt_ex_data($extra['bbk']) . '"';
            
            $res .= '>Upload Docs</button>';

            if(isset($extra['mb'])) $res .= '<div class="w-100 mb-3"></div>' . "\n";

            if(isset($extra['need_container']))
                $res .= '<div class="cco-docs-upload-container"></div>';
            
        }

        return $res;
    }
}

if(!function_exists('get_compliance_asses_data_mix'))
{
    function get_compliance_asses_data_mix($this_obj, $extra = []) 
    {
        $model = $this_obj -> model('ComplianceCircularAssesMasterModel');
        $table = $model -> getTableName();

        $filter = [
            'where' => 'cccm.year_id = :year_id 
                AND cccm.deleted_at IS NULL 
                AND ccsm.is_active = 1 
                AND ccsm.deleted_at IS NULL',
            'params' => [ 
                'year_id' => $extra['year_data']
            ]
        ];

        if(isset($extra['audit_unit_id'])) {
            $filter['where'] .= ' AND cccm.audit_unit_id = :audit_unit_id';
            $filter['params']['audit_unit_id'] = $extra['audit_unit_id'];
        }

        if(isset($extra['authority_id'])) {
            $filter['where'] .= ' AND ccsm.authority_id = :authority_id';
            $filter['params']['authority_id'] = $extra['authority_id'];
        } 

        if(isset($extra['circular_id'])) {
            $filter['where'] .= ' AND cccm.circular_id = :circular_id';
            $filter['params']['circular_id'] = $extra['circular_id'];
        }
        
        if(isset($extra['task_set_id'])) {
            $filter['where'] .= ' AND cccm.task_set_id = :task_set_id';
            $filter['params']['task_set_id'] = $extra['task_set_id'];
        }

        if(isset($extra['period'])) {
            $filter['where'] .= ' AND cccm.com_period_from = :com_period_from AND cccm.com_period_to = :com_period_to';
            $filter['params']['com_period_from'] = $extra['period']['com_period_from'];
            $filter['params']['com_period_to'] = $extra['period']['com_period_to'];
        }

        if(isset($extra['com_status_id'])) {
            $filter['where'] .= ' AND cccm.com_status_id = :com_status_id';
            $filter['params']['com_status_id'] = $extra['com_status_id'];
        }

        // add date wise filter
        $filter['where'] .= ' ORDER BY com_period_to+0 DESC';

        $res = [ 'asses_data' => [], 'completed' => 0 ];

        $query = "SELECT cccm.*, ccsm.set_type_id ccsm_set_type_id, ccsm.authority_id, ccsm.ref_no, ccsm.name ccsm_name, COALESCE( CONCAT(aum.name, ' - ( BR. ', aum.audit_unit_code, ' )'), '". ERROR_VARS['notFound'] ."') AS combined_name,
        COALESCE(ccmcts.name, '". ERROR_VARS['notFound'] ."') as task_set_name FROM ". $table ." cccm 
        LEFT JOIN com_circular_set_master ccsm ON cccm.circular_id = ccsm.id 
        LEFT JOIN audit_unit_master aum ON cccm.audit_unit_id = aum.id
        LEFT JOIN com_circular_multi_control_task_set ccmcts ON cccm.task_set_id = ccmcts.id";

        // get asses data
        $assesData = get_all_data_query_builder(2, $model, $table, $filter, 'sql', $query);

        if(is_array($assesData) && sizeof($assesData) > 0)
        {
            foreach($assesData as $cAssesData)
            {
                $res['asses_data'][ $cAssesData -> id ] = $cAssesData;

                if( $cAssesData -> com_status_id == 4) // completed count
                    $res['completed']++;
            }
        }

        // unset var
        unset($assesData);
        
        return $res;
    }
}

if(!function_exists('get_multi_docs_data'))
{

    function get_multi_docs_data($this_obj, $type, $extra = []) 
    {
         // If object is passed, use it. Else fallback to direct new
    if ($this_obj !== null) {
        $model = $this_obj->model('ComplianceCircularDocModel');
    } else {
        $model = new ComplianceCircularDocModel();
    }
        // 1 2 3 4 5 6 7 8
       
        $table = $model -> getTableName();

        $res = null;
        $filter = [ 'where' => '', 'params' => [] ];

        switch($type)
        {
            case '1': {

                // ENTIRE CIRCULAR DOCS
                if(isset($extra['circulr_id']) && !empty($extra['circulr_id']))
                {
                    $filter['where'] = 'circular_id = :circular_id AND doc_type = 1 AND deleted_at IS NULL';
                    $filter['params']['circular_id'] = $extra['circulr_id'];
                }

                break;
            }

            case '2': {

                // MULTI TASKS DOCS
                if( isset($extra['circulr_id']) && !empty($extra['circulr_id']) && 
                    isset($extra['task_ids']) && !empty($extra['task_ids']) && is_array($extra['task_ids']))
                {
                    $filter['where'] = 'circular_id = :circular_id AND task_id IN ('. implode(',', $extra['task_ids']) .') AND doc_type = 2 AND deleted_at IS NULL';
                    $filter['params']['circular_id'] = $extra['circulr_id'];
                }

                break;
            }

            case '3': {

                // MULTI ANSWER DOCS
                if( isset($extra['ans_ids']) && !empty($extra['ans_ids']) && is_array($extra['ans_ids']) &&
                    isset($extra['assesment_id']) && !empty($extra['assesment_id']) )
                {
                    $filter['where'] = 'answer_id IN ('. implode(',', $extra['ans_ids']) .') AND assesment_id = :assesment_id AND doc_type = 3 AND deleted_at IS NULL';
                    $filter['params']['assesment_id'] = $extra['assesment_id'];
                }

                break;
            }

            case '4': {
                
                // MULTI ANNEX DOCS
                if( isset($extra['annex_ids']) && !empty($extra['annex_ids']) && is_array($extra['annex_ids']) &&
                    isset($extra['assesment_id']) && !empty($extra['assesment_id']) )
                {
                    $filter['where'] = 'annex_id IN ('. implode(',', $extra['annex_ids']) .') AND assesment_id = :assesment_id AND doc_type = 4 AND deleted_at IS NULL';
                    $filter['params']['assesment_id'] = $extra['assesment_id'];
                }

                break;
            }

            case '5': {

                // MULTI COMPLIANCE DOCS
                if( isset($extra['ans_ids']) && !empty($extra['ans_ids']) && is_array($extra['ans_ids']) &&
                    isset($extra['assesment_id']) && !empty($extra['assesment_id']) )
                {
                    $filter['where'] = 'answer_id IN ('. implode(',', $extra['ans_ids']) .') AND assesment_id = :assesment_id AND doc_type = 5 AND deleted_at IS NULL';
                    $filter['params']['assesment_id'] = $extra['assesment_id'];
                }
                
                break;
            }

            case '6': {

                // MULTI COMPLIANCE ANNEX DOCS
                if( isset($extra['annex_ids']) && !empty($extra['annex_ids']) && is_array($extra['annex_ids']) &&
                    isset($extra['assesment_id']) && !empty($extra['assesment_id']) )
                {
                    $filter['where'] = 'annex_id IN ('. implode(',', $extra['annex_ids']) .') AND assesment_id = :assesment_id AND doc_type = 6 AND deleted_at IS NULL';
                    $filter['params']['assesment_id'] = $extra['assesment_id'];
                }

                break;
            }

            case '7': {

                // ENTIRE COMPLIANCE ASSESMENT DOCS
                if( isset($extra['assesment_id']) && !empty($extra['assesment_id']) )
                {
                    $filter['where'] = 'assesment_id = :assesment_id AND doc_type = 7 AND deleted_at IS NULL';
                    $filter['params']['assesment_id'] = $extra['assesment_id'];
                }

                break;
            }

            case '8': {

                // DOCS SUBMIT TO AUTHORITY
                if( isset($extra['circular_id']) && !empty($extra['circular_id']) &&
                    isset($extra['submit_auth_id']) && !empty($extra['submit_auth_id']) )
                {
                    $filter['where'] = 'circular_id = :circular_id AND submit_auth_id = :submit_auth_id AND doc_type = 8 AND deleted_at IS NULL';
                    $filter['params']['circular_id'] = $extra['circular_id'];
                    $filter['params']['submit_auth_id'] = $extra['submit_auth_id'];
                }

                break;
            }
        }

        if(!empty($filter['where']))
        {
            // fire query
            $docsData = get_all_data_query_builder(2, $model, $table, $filter, 'sql', "SELECT * FROM " . $table);

            if(is_array($docsData) && sizeof($docsData) > 0)
            {
                foreach($docsData as $cDocs)
                {
                    $dir = [ 'host' => true ];

                    if(!empty($cDocs -> assesment_id))
                        $dir['assesment_id'] = $cDocs -> assesment_id;

                    // function call
                    $dir = generate_com_circular_dir($cDocs -> circular_id, 1, $dir);
                    $passExtra = [];

                    if( isset($extra['type']) )
                        $passExtra['type'] = $extra['type'];
                    if( isset($extra['com_asses']) )
                        $passExtra['com_asses'] = $extra['com_asses'];
                    
                    $cDocs -> markup = generate_com_docs_markup($cDocs, $dir, $passExtra);
                    $res[ $cDocs -> id ] = $cDocs;
                }
            }
        }

        return $res;
    }
}

if(!function_exists('generate_com_docs_markup'))
{
    function generate_com_docs_markup($docsData, $dir, $extra = [])
    {
        $mrkup = '';

        if(empty($extra) || !isset($extra['wrapper'])) $extra['wrapper'] = 'default';

        if(is_array($docsData) || is_object($docsData))
        {
            if(is_array($docsData))
                $docsData = (object)$docsData;

            // if com assesment not exists
            if( !empty($docsData -> assesment_id) && !isset($extra['com_asses']))
                $extra['com_asses'] = null;

            if(isset($extra['wrapper']))
                $mrkup = '<div class="'. (($extra['wrapper'] == 'default') ? 'cco-docs-uploader' : $extra['wrapper']) .'">' . "\n";

                $mrkup .= '<ul class="docs-status">
                    <li class="font-medium text-danger">File: '. $docsData -> file_name . ( !empty($docsData -> assesment_id) && $docsData -> status_id == 1 ? ' ( Evidence Accepted )' : '') . '</li>
                    <li><a class="doc-action-btn" href="'. ($dir . $docsData -> file_name) . '" target="_blank">View</a></li><li><a class="doc-action-btn" href="' . SiteUrls::getUrl('complianceProAI') . '?file=' . urlencode($dir . $docsData->file_name) . '" target="_blank">View with AI</a></li> ';


                if( empty($docsData -> assesment_id) && isset($extra['type']) && in_array($extra['type'], [1,2,8]) )
                    $mrkup .= '<li><a class="doc-action-btn remove-com-docs-upload" href="'. COMPLIANCE_PRO_ARRAY['compliance_docs_array']['control_url'] . 'remove/' . encrypt_ex_data($docsData -> id) .'">Remove</a></li>' . "\n";

                if( isset($extra['com_asses']) && 
                    is_object($extra['com_asses']) && 
                    in_array($extra['com_asses'] -> com_status_id, [1]) /*&& 
                    $docsData -> status_id == 0*/ )
                {
                    $mrkup .= '<li><a class="doc-action-btn remove-com-docs-upload" href="'. COMPLIANCE_PRO_ARRAY['compliance_docs_array']['control_url'] . 'remove/' . encrypt_ex_data($docsData -> id) .'">Remove</a></li>' . "\n";
                }
                /*elseif( isset($extra['com_asses']) && 
                        is_object($extra['com_asses']) && 
                        in_array($extra['com_asses'] -> com_status_id, [2]) )
                {
                    // if assesement send to compliance // remove status button for audit evidence
                    if( $docsData -> doc_type == 2)
                    {
                        $statusText = ( $docsData -> status_id == 1 ) ? 'Accepted' : 'Rejected';
                        $statusText = ( $docsData -> status_id == 0 ) ? 'Accept' : $statusText;

                        $mrkup .= '<li><a class="status-com-docs-upload" href="'. COMPLIANCE_PRO_ARRAY['compliance_docs_array']['control_url'] . 'status/' . encrypt_ex_data($docsData -> id) .'">'. $statusText .'</a></li>' . "\n";
                    }
                }*/
                
            $mrkup .= '</ul>';

            if(isset($extra['wrapper']))
                $mrkup .= '</div>' . "\n";
        }

        return $mrkup;
    }
}

if(!function_exists('generate_com_circular_dir'))
{
    function generate_com_circular_dir($id, $type = 1, $extra = []) {
        
        // for circular
        $dir = $type == 1 ? 'com_circular_dir_name' : 'com_asses_dir_name';

        if(!isset($extra['com_asses_id']) && isset($extra['assesment_id']))
            $extra['com_asses_id'] = $extra['assesment_id'];
        
        if( isset($extra['host']) ) // circular folder path
            return COMPLIANCE_PRO_ARRAY['compliance_docs_array']['upload_url'] . COMPLIANCE_PRO_ARRAY['compliance_docs_array'][ $dir ] . $id . '/' . (( isset($extra['com_asses_id']) && !empty($extra['com_asses_id']) ) ? (COMPLIANCE_PRO_ARRAY['compliance_docs_array']['com_asses_dir_name'] . $extra['com_asses_id'] . '/') : '');

        // for compliance asses
        return COMPLIANCE_PRO_ARRAY['compliance_docs_array'][ $dir ] . $id . '/';
    }
}

if(!function_exists('generate_circular_docs_markup'))
{
    function generate_circular_docs_markup($data, $extra = []) {

        $docsMrk = ''; $temp = '';

        if( isset($data -> multi_docs) && 
            is_array($data -> multi_docs) && 
            sizeof($data -> multi_docs) > 0) {

          foreach($data -> multi_docs as $cDoc) {
            $docsMrk .= $cDoc -> markup;
          }
        }

        if(isset($extra['container']) && !empty($docsMrk))
        {
            $temp .= '<div class="cco-docs-upload-container'. ( isset($extra['mt']) ? ' mt-2' : '') .'">' . "\n";
                $temp .= $docsMrk;
            $temp .= '</div>' . "\n";

            $docsMrk = $temp;
        }        

        // unset
        unset($temp);

        return $docsMrk;
    }
}
if(!function_exists('generate_due_from_date'))
{
    function generate_due_from_date($date, $todayDate, $extra = [])
    {
        // Break down dates
        list($year, $month, $day) = explode('-', $date);
        list($currentYear, $currentMonth, $currentDay) = explode('-', $todayDate);

        // Calculate the new month and year based on today's date
        if ($month < $currentMonth) {
            $newYear = $currentYear;
            $newMonth = $currentMonth + 1;
        } else {
            $newYear = $currentYear;
            $newMonth = $month + ($currentMonth - $month) + 1;
        }

        if ($newMonth > 12) {
            $newMonth = 1;
            $newYear++;
        }

        $daysInNewMonth = date('t', strtotime("$newYear-$newMonth-01"));
        $newDay = ($day > $daysInNewMonth) ? $daysInNewMonth : $day;
        return sprintf('%04d-%02d-%02d', $newYear, $newMonth, $newDay);
    }
}

if(!function_exists('get_from_to_date_on_frequency'))
{
    function get_from_to_date_on_frequency($frequencyType, $assignData, $todayDate, $extra = [])
    {
        $res = [ 'com_period_from' => null, 'com_period_to' => null, 'frequency' => 0, 'due_date' => null ];
        $currentDate = isset($extra['tdstt']) ? date('Y-m-d', $todayDate) : $todayDate;

        switch ($frequencyType) {

            // case '1':
            //     $res['com_period_from'] = $todayDate;
            //     $res['com_period_to'] = $res['com_period_from'];
            //     break;

            case '1': // FORTNIGHT - Every 15 Days

                $res['frequency'] = $frequencyType;

                if ( strtotime($currentDate) <= strtotime(date("Y-m-15", strtotime($currentDate))) )
                {
                    // If current date is between the 1st and 15th of the month
                    $res['com_period_from'] = date("Y-m-01", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-m-15", strtotime($currentDate));
                    $res['reporting_date'] = date('Y-m') . '-' . date('d', strtotime($assignData -> reporting_date_1));
                    $res['due_date'] = date('Y-m') . '-' . date('d', strtotime($assignData -> due_date_1));
                }
                else 
                {
                    // If current date is after 15th, use 16th to end of month
                    $res['com_period_from'] = date("Y-m-16", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-m-t", strtotime($currentDate));
                    $res['reporting_date'] = date('Y-m') . '-' . date('d', strtotime($assignData -> reporting_date_2));
                    $res['due_date'] = date('Y-m') . '-' . date('d', strtotime($assignData -> due_date_2));
                }

                break;

            case '2': 
                
                // MONTHLY - Every Month
                $res['frequency'] = $frequencyType;

                $res['com_period_from'] = date("Y-m-01", strtotime($currentDate));
                $res['com_period_to'] = date("Y-m-t", strtotime($currentDate));
                $res['reporting_date'] = generate_due_from_date($assignData -> reporting_date_1, $res['com_period_from']);
                $res['due_date'] = generate_due_from_date($assignData -> due_date_1, $res['com_period_from']);
                break;

            case '3':

                // Current quarter based on the current month
                $month = date('n', strtotime($currentDate));
                $res['frequency'] = $frequencyType;

                if ($month >= 3 && $month <= 6) {
                    $res['com_period_from'] = date("Y-03-01", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-06-t", strtotime($currentDate));
                } elseif ($month >= 7 && $month <= 9) {
                    $res['com_period_from'] = date("Y-07-01", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-09-t", strtotime($currentDate));
                } elseif ($month >= 10 && $month <= 12) {
                    $res['com_period_from'] = date("Y-10-01", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-12-t", strtotime($currentDate));
                } else {
                    $res['com_period_from'] = date("Y-01-01", strtotime("+1 year", strtotime($currentDate)));
                    $res['com_period_to'] = date("Y-03-t", strtotime("+1 year", strtotime($currentDate)));
                }

                $res['reporting_date'] = generate_due_from_date($assignData -> reporting_date_1, $res['com_period_from']);
                $res['due_date'] = generate_due_from_date($assignData -> due_date_1, $res['com_period_from']);
                break;

            case '4':

                // Two half-year periods: 1st half (Apr-Sep) and 2nd half (Oct-Mar)
                $month = date('n', strtotime($currentDate));
                $res['frequency'] = $frequencyType;

                if ($month >= 4 && $month <= 9) {
                    $res['com_period_from'] = date("Y-04-01", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-09-t", strtotime($currentDate));
                } else {
                    $res['com_period_from'] = date("Y-10-01", strtotime($currentDate));
                    $res['com_period_to'] = date("Y-03-31", strtotime("+1 year", strtotime($currentDate)));
                }

                $res['reporting_date'] = generate_due_from_date($assignData -> reporting_date_1, $res['com_period_from']);
                $res['due_date'] = generate_due_from_date($assignData -> due_date_1, $res['com_period_from']);
                break;

            case '5':

                // Full year
                $currentYear = getFYOnDate($currentDate);
                $res['frequency'] = $frequencyType;

                $res['com_period_from'] = $currentYear . "-04-01";
                $res['com_period_to'] = ($currentYear + 1) . "-03-31";
                $res['reporting_date'] = $assignData -> reporting_date_1;
                $res['due_date'] = $assignData -> due_date_1;
                break;

            case '6':

                // One Time Use
                $res['frequency'] = $frequencyType;

                $res['com_period_from'] = $assignData -> otu_start_date;
                $res['com_period_to'] = $assignData -> otu_end_date;
                $res['reporting_date'] = $assignData -> reporting_date_1;
                $res['due_date'] = $assignData -> due_date_1;
                break;
        }

        return $res;
    }
}

if(!function_exists('check_date_lowest_to_highest'))
{
    function check_date_lowest_to_highest($comAssesDateArray, $extra)
    {
        // check start_date
        if ($extra['start_date'] < $comAssesDateArray['start_date'])
            $comAssesDateArray['start_date'] = $extra['start_date'];

        // check end_date
        if ($extra['end_date'] > $comAssesDateArray['end_date'])
            $comAssesDateArray['end_date'] = $extra['end_date'];

        return $comAssesDateArray;
    }
}

if(!function_exists('generate_multi_com_asses_array'))
{
    function generate_multi_com_asses_array($circularData, $assignedCircularData, $extra = [])
    {
        if(!isset($extra['type'])) $extra['type'] = 1;

        $comAssesDateArray = [ 'start_date' => $extra['todayDate'], 'end_date' => $extra['todayDate'], 'assignAuditUnits' => null ];

        if((is_array($assignedCircularData) && sizeof($assignedCircularData) > 0) )
        {
            // has data
            foreach($assignedCircularData as $cAssignData)
            {
                if( !empty($cAssignData -> audit_unit_ids) &&
                    !empty($cAssignData -> header_ids) &&
                    !empty($cAssignData -> task_ids)
                )
                {
                    // generate bulk batch key
                    $cCircularData = $circularData[ $cAssignData -> circular_id ];
                    $cStartDate = $cAssignData -> schedule_start_date;

                    if(!isset($circularData[ $cAssignData -> circular_id ] -> assign_master))
                    {
                        $circularData[ $cAssignData -> circular_id ] -> assign_master = [];
                        $circularData[ $cAssignData -> circular_id ] -> com_asses_data = [];
                    }

                    // push assign master data
                    $circularData[ $cAssignData -> circular_id ] -> assign_master[ $cAssignData -> id ] = $cAssignData;

                    // re assign audit units
                    $assignedAuditUnits = ($extra['type'] == 1) ? 
                        explode(',', $cAssignData -> audit_unit_ids) : 
                        (isset($extra['auditUnitData']) ? [ $extra['auditUnitData'] -> id ] : []);

                    if(empty($comAssesDateArray['assignAuditUnits']))
                        $comAssesDateArray['assignAuditUnits'] = $assignedAuditUnits;
                    else
                        $comAssesDateArray['assignAuditUnits'] = array_merge($comAssesDateArray['assignAuditUnits'], $assignedAuditUnits);

                    $comAssesDateArray['assignAuditUnits'] = array_values(array_unique($comAssesDateArray['assignAuditUnits']));

                    $assignedAuditHeaderIds = explode(',', $cAssignData -> header_ids);
                    $assignedAuditTaskIds = explode(',', $cAssignData -> task_ids);

                    if(!in_array($cAssignData -> frequency, [5,6])) // recurring circulars
                    {
                        while(1) {
                                
                            $comAssesData = get_from_to_date_on_frequency($cAssignData -> frequency, $cAssignData, $cStartDate);
                            $comAssesData['com_asses_data'] = [];
                            $comAssesData['assigned_audit_units'] = $assignedAuditUnits;
                            $comAssesData['header_ids'] = $assignedAuditHeaderIds;
                            $comAssesData['task_ids'] = $assignedAuditTaskIds;
                            $comAssesData['task_set_id'] = $cAssignData -> id; // set id

                            $cStartDate = date($GLOBALS['dateSupportArray']['1'], strtotime($comAssesData['com_period_to'] . ' +1 Day'));                                        
                            
                            $generateBulkKey = generate_bulk_batch_key_compliance_pro([
                                'from' => $comAssesData['com_period_from'],
                                'to' => $comAssesData['com_period_to'],
                                'circular_id' => $cAssignData -> circular_id,
                                'task_set_id' => $comAssesData['task_set_id']
                            ]);

                            // push data
                            $circularData[ $cAssignData -> circular_id ] -> com_asses_data[ $generateBulkKey ] = $comAssesData;

                            // method call
                            $comAssesDateArray = check_date_lowest_to_highest($comAssesDateArray, [
                                'start_date' => $comAssesData['com_period_from'],
                                'end_date' => $comAssesData['com_period_to'],
                            ]);

                            if(!($cStartDate < $extra['todayDate']))
                                break;
                        }
                    }
                    else
                    {
                        // 1 time or yearly freequency
                        $comAssesData = get_from_to_date_on_frequency($cAssignData -> frequency, $cAssignData, $cStartDate);
                        $comAssesData['com_asses_data'] = [];
                        $comAssesData['assigned_audit_units'] = $assignedAuditUnits;
                        $comAssesData['header_ids'] = $assignedAuditHeaderIds;
                        $comAssesData['task_ids'] = $assignedAuditTaskIds;
                        $comAssesData['task_set_id'] = $cAssignData -> id; // set id
                        
                        $generateBulkKey = generate_bulk_batch_key_compliance_pro([
                            'from' => $comAssesData['com_period_from'],
                            'to' => $comAssesData['com_period_to'],
                            'circular_id' => $cAssignData -> circular_id,
                            'task_set_id' => $comAssesData['task_set_id']
                        ]);

                        // push data
                        $circularData[ $cAssignData -> circular_id ] -> com_asses_data[ $generateBulkKey ] = $comAssesData;

                        // method call
                        $comAssesDateArray = check_date_lowest_to_highest($comAssesDateArray, [
                            'start_date' => $comAssesData['com_period_from'],
                            'end_date' => $comAssesData['com_period_to'],
                        ]);
                    }
                }
                
            }
        }

        $comAssesDateArray['circular_data'] = $circularData;

        return $comAssesDateArray;
    }
}



?>