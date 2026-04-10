<?php

if(!function_exists('find_compliance_pro_observations_common_data'))
{
    //find common data
    function find_compliance_pro_observations_common_data($this_obj, $comAssestData) {

        $dataArray = array('active_header' => null);

        $model = $this_obj -> model('ComplianceCircularHeaderModel');
        
        if(is_object($comAssestData) && !empty($comAssestData))
        {
            $dataArray['active_header'] = $model -> getAllCircularHeader([
                'where' => 'id IN ('. $comAssestData -> header_ids .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);
        }
        else
        {
            if(isset($comAssestData) && is_array($comAssestData))
            {
                $reportComplianceAssesData = [];

                for($i = 0; $i < sizeof($comAssestData); $i++ )
                {
                    $tempData = !empty($comAssestData[$i] -> header_ids) ? explode(',', $comAssestData[$i] -> header_ids) : [];

                    $reportComplianceAssesData = array_merge($reportComplianceAssesData, $tempData);                
                }

                $headerData = implode(',', array_unique($reportComplianceAssesData));
            }

            $dataArray['active_header'] = $model -> getAllCircularHeader([ 
                'where' => 'id IN ('. $headerData .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);            
        }

        // helper function call
        $dataArray['active_header'] = generate_data_assoc_array($dataArray['active_header'], 'id');

        return $dataArray;
    }
}

if(!function_exists('get_compliance_pro_sorted_tasks'))
{
    // function for sort questions
    function get_compliance_pro_sorted_tasks($this_obj, $findObservation, $dataArray, $annexureData, $commonDataArray)
    {
        foreach($findObservation as $cAnsId => $cAnsRow)
        {
            $isAnnexFound = true;
            $dataArray['is_compliance_cnt']++;

            //all data is ok //process other data
            if($isAnnexFound)
            {
                // push question ids
                if(!in_array($cAnsRow -> id , $dataArray[ 'ans_ids' ]))
                    $dataArray[ 'ans_ids' ][] = $cAnsRow -> id;

                if( !array_key_exists($cAnsRow -> header_id, $dataArray['ans_data']) )
                    $dataArray['ans_data'][ $cAnsRow -> header_id ] = array( 'header' => null, 'tasks' => [] );

                if( is_array($commonDataArray['active_header']) && 
                    array_key_exists($cAnsRow -> header_id, $commonDataArray['active_header']) )
                    $dataArray['ans_data'][ $cAnsRow -> header_id ]['header'] = $commonDataArray['active_header'][ $cAnsRow -> header_id ];

                //push questions
                $dataArray['ans_data'][ $cAnsRow -> header_id ]['tasks'][ $cAnsRow -> id ] = $cAnsRow;
            }
        }

        return $dataArray;
    }
}

if(!function_exists('find_complinace_pro_tasks_observations'))
{
    // function for find observations
    function find_complinace_pro_tasks_observations($this_obj, $comAssesData, $FILTER_TYPE = '', $recompliance_status = 0)
    {
        $dataArray = array(
            'ans_data' => [], 'annex_tab_ids' => [], 'annex_tab_cols' => [], 
            'dump_advances' => [], 'dump_deposits' => [], 'ans_ids' => [], 'ans_annex_ids' => [], 'is_compliance_cnt' => 0 );

        //find observations
        $model = $this_obj -> model('ComplianceCircularAnswerDataModel');

        // DEFAULT QUERY
        $query = "SELECT ans.*, cctm.task, cctm.risk_category_id";
        
        // if(check_evidence_upload_strict()) $query .= ", qm.audit_ev_upload, qm.compliance_ev_upload";

        $query .= " FROM com_circular_answers_data ans JOIN com_circular_task_master cctm ON ans.task_id = cctm.id WHERE ans.com_master_id = '". $comAssesData -> id ."'";

        // if( $FILTER_TYPE == 'AAP' )
        //     $query .= " AND (
        //                         (ans.dump_id != '0' AND ans.is_compliance = '1') OR 
        //                         (ans.dump_id = '0')
                            // )";
        // elseif( $FILTER_TYPE == 'ACP' )
        //     $query .= " AND ans.is_compliance = '1'";
        // else
        if( $FILTER_TYPE == 'ACRP' )
            $query .= " AND ans.compliance_status_id IN (2,3)";
        // elseif( $FILTER_TYPE == 'ACRP' )
        //     $query .= " AND ans.is_compliance = '1' AND ans.compliance_status_id = '3'";
        // elseif( $FILTER_TYPE == 'PCDR')
        //     $query .= " AND ans.is_compliance = '1' AND ( ans.compliance_status_id = '3' OR qm.annexure_id > 0 )";

        // add period wise question ids
        $query .= " AND ans.task_id IN (".  $comAssesData -> task_ids .") AND ans.deleted_at IS NULL";
        
        // ADD ORDER BY 
        $query .= " ORDER BY dump_id, header_id, task_id";

        $findObservation = $model -> getAllCircularAnswerData( [], 'sql', $query );

        if( is_array($findObservation) && sizeof($findObservation) > 0 )
        {
            // assign count
            // $dataArray['is_compliance_cnt'] = sizeof($findObservation);

            // find all subset data
            $model = $this_obj -> model('ComplianceCircularSetModel');

            // convert to array
            $findObservation = generate_data_assoc_array($findObservation, 'id');

            // find docs
            $multiDocsData = get_multi_docs_data($this_obj, 5, [
                'assesment_id' => $comAssesData -> id, 
                'ans_ids' => array_keys($findObservation),
                'type' => 5,
                'com_asses' => $comAssesData
            ]);

            if(is_array($multiDocsData) && sizeof($multiDocsData) > 0)
            {
                foreach($multiDocsData as $cDocId => $cDocData)
                {
                    if( !empty($cDocData -> answer_id) && 
                        array_key_exists($cDocData -> answer_id, $findObservation))
                    {
                        // ans found
                        if(!isset($findObservation[ $cDocData -> answer_id ] -> multi_docs))
                        $findObservation[ $cDocData -> answer_id ] -> multi_docs = [];

                        $findObservation[ $cDocData -> answer_id ] -> multi_docs[ $cDocData -> id ] = $cDocData;
                    }
                }
            }
            
            // function call
            // if( in_array($FILTER_TYPE, ['ACP','AACRP','ACRP']) && 
            //     check_evidence_upload_strict() )   
            //     $findObservation = get_evidence_upload_data($assessmentData, $findObservation, array_keys($findObservation), 1);
             
            //find observations in annexure
            // $model = $this_obj -> model('AnswerDataAnnexureModel');

            // $annexWhereData = [
            //     'where' => 'assesment_id = :assesment_id AND answer_id IN ('. implode(',', array_keys($findObservation)) .') AND deleted_at IS NULL',
            //     'params' => [ 'assesment_id' => $assessmentData -> id ]
            // ];

            // if( $FILTER_TYPE == 'AACRP' )
            //     $annexWhereData['where'] .= " AND audit_status_id = '3'";
            // elseif( in_array($FILTER_TYPE, ['ACRP', 'PCDR']) )
            //     $annexWhereData['where'] .= " AND compliance_status_id = '3'";

            // $findAllAnnexure = $model -> getAllAnswerAnnexures($annexWhereData);

            $annexureAnsData = [];

            // if(is_array($findAllAnnexure) && sizeof($findAllAnnexure) > 0)
            // {
            //     $annexIds = [];

            //     foreach($findAllAnnexure as $cAnnexDetails)
            //     {
            //         if( /*in_array($FILTER_TYPE, [1, 2, 3]) || 
            //             (in_array($FILTER_TYPE, [4, 5]) && $row['reviewer_accept_reject_compliance'] == '2')*/ 1 )
            //         {
            //             $cGenKey = $cAnnexDetails -> answer_id;

            //             if(!array_key_exists($cGenKey, $annexureAnsData))
            //                 $annexureAnsData[ $cGenKey ] = [];

            //             // remove from above if 05.08.2024
            //             if(!in_array($cAnnexDetails -> id, $annexIds))
            //                 $annexIds[] = $cAnnexDetails -> id;

            //             $annexureAnsData[ $cGenKey ][ $cAnnexDetails -> id ] = $cAnnexDetails;
            //         }
            //     }

            //     // function call for evidence upload
            //     if( in_array($FILTER_TYPE, ['ACP','AACRP','ACRP']) && 
            //         check_evidence_upload_strict() && 
            //         sizeof($annexIds) > 0 )
            //     {
            //         $annexureAnsData = get_evidence_upload_data($assessmentData, $annexureAnsData, $annexIds, 2);
            //     }
            // }

            //function call 
            $commonDataArray = find_compliance_pro_observations_common_data($this_obj, $comAssesData);

            $dataArray = get_compliance_pro_sorted_tasks($this_obj, $findObservation, $dataArray, $annexureAnsData, $commonDataArray);

            if(sizeof($dataArray['ans_data']) > 0)
            {
                ksort( $dataArray['ans_data'] ); // IMPORTANT

                // FIND LOAN ACCOUNTS
                // if(sizeof($dataArray['dump_advances']) > 0)
                // {
                //     // helper function call
                //     $dataArray['dump_advances'] = find_mixed_dump_data($this_obj, $assessmentData, [
                //         'where' => 'dt.id IN ('. implode(',', $dataArray['dump_advances']) .')',
                //         'params' => []
                //     ]);
                // }

                // FIND DEPOSIT ACCOUNTS
                // if(sizeof($dataArray['dump_deposits']) > 0)
                // {
                //     // helper function call
                //     $dataArray['dump_deposits'] = find_mixed_dump_data($this_obj, $assessmentData, [
                //         'where' => 'dt.id IN ('. implode(',', $dataArray['dump_deposits']) .')',
                //         'params' => []
                //     ], 1);
                // }

                // FIND ANNEXURES
                // if(sizeof($dataArray['annex_tab_ids']) > 0)
                // {
                //     // find annexure
                //     $model = $this_obj -> model('AnnexureMasterModel');

                //     $dataArray['annex_tab_cols'] = $model -> getAllAnnexures([
                //         'where' => 'id IN ('. implode(',', $dataArray['annex_tab_ids']) .')',
                //         'params' => []
                //     ]);
                    
                //     if(is_array($dataArray['annex_tab_cols']) && sizeof($dataArray['annex_tab_cols']) > 0)
                //     {
                //         // helper function call
                //         $dataArray['annex_tab_cols'] = generate_data_assoc_array($dataArray['annex_tab_cols'], 'id');

                //         // find annexure columns
                //         $model = $this_obj -> model('AnnexureColumnModel');

                //         $annexColsData = $model -> getAllAnnexureColumns([
                //             'where' => 'annexure_id IN ('. implode(',', array_keys($dataArray['annex_tab_cols'])) .')',
                //             'params' => []
                //         ]);

                //         if(is_array($annexColsData) && sizeof($annexColsData) > 0)
                //         {
                //             foreach($annexColsData as $cAnnexColDetails)
                //             {
                //                 if(array_key_exists($cAnnexColDetails -> annexure_id, $dataArray['annex_tab_cols']))
                //                 {
                //                     if(!isset($dataArray['annex_tab_cols'][ $cAnnexColDetails -> annexure_id ] -> annex_cols))
                //                         $dataArray['annex_tab_cols'][ $cAnnexColDetails -> annexure_id ] -> annex_cols = [];

                //                     // push data
                //                     $dataArray['annex_tab_cols'][ $cAnnexColDetails -> annexure_id ] -> annex_cols[ $cAnnexColDetails -> id ] = $cAnnexColDetails;
                //                 }
                //             }
                //         }

                //         // unset data
                //         unset($annexColsData);
                //     }
                // }
                // else
                    $dataArray['annex_tab_cols'] = array();
            }

            unset($findAllAnnexure);

        }

        return $dataArray;
    }
}

if(!function_exists('generate_table_markup'))
{ 
    //function for generate table markup
    function generate_table_markup($data, $dataArray, $filterType = 1, $branchHeader = 0, $assesArray = [])
    {
        // SET COLSPAN FIRST
        if(in_array($filterType, ['REARP']))
            $colspan = 9;
        // elseif(in_array($filterType, ['CRPWC']))
        //     $colspan = 8;
        elseif(in_array($filterType, ['RECOM']))
            $colspan = 6;
        elseif(in_array($filterType, ['COM']))     
            $colspan = 5;
        else     
            $colspan = 7;

        $mrk_str = '';

        // if($branchHeader)
        // {
        //     // $mrk_str .= '<div class="card-header pb-1 font-medium text-uppercase mb-0">ASSESMENT DETAILS: </div>' . "\n";

        //     $mrk_str .= '<h4 class="text-center"><span class="font-medium mb-1">Branch: <u>'. $data['data']['audit_unit_data'][$assesArray -> audit_unit_id] -> combined_name .'</u></span></h4>' . "\n";

        //     // assesment period
        //     $mrk_str .= '<p class="text-center mb-2">Period: '. $assesArray -> assesment_period_from . ' to ' . $assesArray -> assesment_period_to . '</p>' . "\n";

        //     // $mrk_str .= '</div>' . "\n";
        // }

        $mrk_str .= '<table class="table audit-report-table table-bordered mb-4">' . "\n";

        foreach($dataArray['ans_data'] as $cHeaderId => $cHeaderDetails)
        {            
            //general question
            $mrk_str .= generate_display_questions_markup($data, $cHeaderDetails, $dataArray, $colspan, $filterType);
        }

        $mrk_str .= '</table>' . "\n";

        return $mrk_str;
    }
}

if(!function_exists('generate_report_risk_columns'))
{
    function generate_report_risk_columns($data, $FILTER_TYPE, $cAnsDetails, $onlyCols = false)
    {
        $str = '';

        if($onlyCols)
        {
            if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC']))
                $str .= '<th>Business Risk</th>';

            if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC']))
                $str .= '<th>Control Risk</th>';

            if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC']))
                $str .= '<th>Risk Type</th>';

            return $str;
        }

        // BUSINESS RISK
        if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC']))
            $str .= '<td>'. (array_key_exists($cAnsDetails -> business_risk, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cAnsDetails -> business_risk ]['title'] : '-') .'</td>';

        // CONTROL RISK
        if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC']))
            $str .= '<td>'. (array_key_exists($cAnsDetails -> control_risk, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cAnsDetails -> control_risk ]['title'] : '-') .'</td>';

            // print_r($data['data']);
            // exit;

        // RISK TYPE
        if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC']))
        {
            if( isset($data['data']['risk_category_data']) && 
                is_array($data['data']['risk_category_data']) )
            {
                if( isset($cAnsDetails -> risk_category_id) && 
                    array_key_exists($cAnsDetails -> risk_category_id, $data['data']['risk_category_data']) )
                    $str .= '<td>'. string_operations( (is_object($data['data']['risk_category_data'][ $cAnsDetails -> risk_category_id ]) ? $data['data']['risk_category_data'][ $cAnsDetails -> risk_category_id ] -> risk_category : $data['data']['risk_category_data'][ $cAnsDetails -> risk_category_id ]), 'upper') .'</td>';
                elseif( isset($cAnsDetails -> risk_cat_id) && 
                        array_key_exists($cAnsDetails -> risk_cat_id, $data['data']['risk_category_data']) )
                    $str .= '<td>'. string_operations((is_object($data['data']['risk_category_data'][ $cAnsDetails -> risk_cat_id ]) ? $data['data']['risk_category_data'][ $cAnsDetails -> risk_cat_id ] -> risk_category : $data['data']['risk_category_data'][ $cAnsDetails -> risk_cat_id ]), 'upper') .'</td>';
                else
                    $str .= '<td></td>';
            }
            else
                $str .= '<td></td>';
        }

        return $str;
    }
}

if(!function_exists('generate_report_td_markup'))
{
    function generate_report_td_markup($FILTER_TYPE, $cAnsDetails, $reviewActionArray, $colSelect, $type = 'annex')
    {
        $str = '';

        $checkAnnexAns = (
            $type == 'gen' &&
            isset($cAnsDetails -> option_id) && 
            isset($cAnsDetails -> annexure_id) && 
            $cAnsDetails -> option_id == 4 && 
            $cAnsDetails -> annexure_id == $cAnsDetails -> answer_given
        );

        // ACCEPT REJECT AUDIT, COMPLIANCE
        if(in_array($FILTER_TYPE, ['RVAU','RVCOM']) )
        {
            $str .= '<td width="160px" class="reviewer-action">
                    <select class="form-control form-select'. ($checkAnnexAns ? ' has-annexure' : '') .'" data-ansid="'. encrypt_ex_data($cAnsDetails -> id) .'" data-anstype="'. $type .'" data-slctact="'. (($FILTER_TYPE == 'RVAU') ?  'aud' : 'com') .'">';

            foreach($reviewActionArray as $c_review_ac_id => $c_review_ac_val)
            {
                $str .= '<option value="'. $c_review_ac_id .'"'. (( $cAnsDetails -> { $colSelect } == $c_review_ac_id ) ? ' selected="selected"' : '') .' tt="'. $cAnsDetails -> { $colSelect } .'">'. $c_review_ac_val .'</option>';
            }
                        
            $str .= '</select>
                <small class="reponse-status d-block mt-1 mb-2"></small>
                </td>' . "\n";
        }
        
        // COMPLIANCE GIVE TEXTAREA
        if(in_array($FILTER_TYPE, ['COM','RECOM']))
        {
            $str .= '<td width="180px" class="p-3 compliance-container"'. (($FILTER_TYPE == 'RECOM') ? ' colspan="0"' : '') .'>
                <textarea class="form-control" data-ansid="'. encrypt_ex_data($cAnsDetails -> id) .'" data-anstype="'. $type .'">'. urldecode_data($cAnsDetails -> compliance) .'</textarea>
                <small class="reponse-status d-block mt-1 mb-2"></small>
                <button class="btn btn-secondary btn-sm">Save Compliance</button>
            </td>' . "\n";
        }
        
        // REVIEWER COMMENT GIVE TEXTAREA
        if(in_array($FILTER_TYPE, ['RVAU', 'RVCOM']))
        {
            $str .= '<td width="180px" class="comment-container">
                <textarea class="form-control" data-ansid="'. encrypt_ex_data($cAnsDetails -> id) .'" data-anstype="'. $type .'">'. ( ( $FILTER_TYPE == 'RVAU' ) ? trim_str( $cAnsDetails -> audit_reviewer_comment ) : trim_str( $cAnsDetails -> compliance_reviewer_comment ) ) .'</textarea>
                <small class="reponse-status d-block mt-1 mb-2"></small>
                <button class="btn btn-secondary btn-sm">Save Comment</button>
            </td>' . "\n";
        }

        // REVIEWER COMMENT DISPLAY
        if( in_array($FILTER_TYPE, ['REARP']) )
        {   
            $str .= '<td>'. ( ( $FILTER_TYPE == 'REARP' ) ? trim_str( array_key_exists($cAnsDetails -> audit_status_id, AUDIT_STATUS_ARRAY['audit_review_action']) ? AUDIT_STATUS_ARRAY['audit_review_action'][$cAnsDetails -> audit_status_id] : ERROR_VARS['notFound'] ) : trim_str( array_key_exists($cAnsDetails -> compliance_status_id, AUDIT_STATUS_ARRAY['compliance_review_action']) ? AUDIT_STATUS_ARRAY['compliance_review_action'][$cAnsDetails -> compliance_status_id] : ERROR_VARS['notFound'] ) ) .'</td>';

            $reviewerComment = ( ( $FILTER_TYPE == 'REARP' ) ? trim_str( $cAnsDetails -> audit_reviewer_comment ) : trim_str( $cAnsDetails -> compliance_reviewer_comment ) );

            $str .= '<td>'. ( !empty($reviewerComment) ? $reviewerComment : '-' ) .'</td>';

            unset($reviewerComment);
        }

        return $str;
    }
}

if(!function_exists('generate_display_questions_markup'))
{ 
    //function display questions
    function generate_display_questions_markup($data, $tasksArray, $dataArray, $colspan, $FILTER_TYPE = 'RVAU')
    {
        // 4  COM = COMPLIANCE
        // 3  RVCOM = REVIEW COMPLIANCE - RC

        
        // 1  RVAU = REVIEW AUDIT - RA
        // 2  REARP = RE AUDIT REPORT - REAR
        // 5  RECOM = RE COMPLIANCE - REC
        // 6  ARCRP = AUDIT REPORT COMPLETE REPORT - ARCR
        // 7  COMRP = COMPLIANCE REPORT - CR 
        // 8  CRPWC = COMPLIANCE REPORT WITH COMMENT - CRWC
        // 9  PCDR = PENDING COMPLIANCE DETAILED REPORT

        $str = '';
        $res = [ 'com_reject' => 0, 'com_pending' => 0 ];
        $checkCF = 0;

        // echo '<pre>';
        // print_r($tasksArray);
        // exit;

        $str .= '<tr><td colspan="'. $colspan .'" class="bg-light-gray font-medium"><u>'. strtoupper('Header: ' . (is_object($tasksArray['header']) ? $tasksArray['header'] -> name : ERROR_VARS['notFound'])) .'</u></td></tr>' . "\n";

        $str .= '<tr>
                <th class="text-center">#</th>
                <th>Circular Tasks</th>';

                // <th>Task Point</th>
                // '<th>CCO Comment</th>';

                if(in_array($FILTER_TYPE, ['COM', 'RVCOM', 'RECOM']))
                    $str .= '<th>Documents</th>';

                // 'CRPWC', 'PCDR'
                if(in_array($FILTER_TYPE, ['COM', 'RVCOM', 'RECOM']))
                    $str .= '<th>Compliance</th>';

                if( in_array($FILTER_TYPE, ['']))
                    $str .= '<th>Action</th>';

                // if( in_array($FILTER_TYPE, ['COM', 'RECOM']) && 
                //     check_evidence_upload_strict() )
                //     $str .= '<th>Evidence</th>';

                // function call
                $str .= generate_report_risk_columns(null, $FILTER_TYPE, null, 1);

                // 'REARP', 'RVAU', 'RECOM'
                if( in_array($FILTER_TYPE, ['RECOM', 'RVCOM']))
                    $str .= '<th>CCO Status</th>';

                // 'REARP', 'RVAU', 'PCDR'
                if( in_array($FILTER_TYPE, ['RECOM', 'RVCOM']))
                    $str .= '<th>CCO Remark</th>';

        $str .= '</tr>';

        $srNo = 1;

        // if($checkCF)
        // print_r($cHeaderDetails['questions']);
      

        foreach ($tasksArray['tasks'] as $cTaskId => $cTaskDetails)
        {
           
        

            // 'RVAU', , 'RECOM', 'RVCOM'
            $compulsaryClassStyle = (in_array($FILTER_TYPE, ['COM'])) ? 'text-danger' : '';

            // REVIEW AUDIT CHECK
            // if( in_array($FILTER_TYPE, ['RVAU', 'RVCOM']) && 
            //     ($cTaskDetails -> audit_status_id != 3 || $cTaskDetails -> compliance_status_id != 3) )
            //     $compulsaryClassStyle = '';

            // trim data 
            $cTaskDetails -> task = trim_str(urldecode_data($cTaskDetails -> task));
            $cTaskDetails -> answer_given = trim_str(urldecode_data($cTaskDetails -> answer_given));
            $cTaskDetails -> cco_comment = trim_str(urldecode_data($cTaskDetails -> cco_comment));
            $cTaskDetails -> compliance = trim_str(urldecode_data($cTaskDetails -> compliance));
            // $cTaskDetails -> audit_reviewer_comment = trim_str(urldecode_data($cTaskDetails -> audit_reviewer_comment));
            $cTaskDetails -> compliance_reviewer_comment = trim_str(urldecode_data($cTaskDetails -> compliance_reviewer_comment));

            $activateEvidence = 0;

            // if(check_evidence_upload_strict())
            // {
            //     // CHECK EVIDENCE UPLOAD
            //     if(in_array($FILTER_TYPE, ['RVAU']))
            //         $activateEvidence = 1;

            //     if( in_array($FILTER_TYPE, ['COM', 'RECOM']) && in_array($cTaskDetails -> compliance_compulsary_ev_upload, [1,2]) )
            //         $activateEvidence = 1;
            // }

            // $activateEvidence = (check_evidence_upload_strict() && ($cTaskDetails -> compliance_ev_upload == 1 || (isset($cTaskDetails -> compliance_compulsary_ev_upload) && in_array($cTaskDetails -> compliance_compulsary_ev_upload, [1,2]) ? 1 : 0) ));

            // SKIP ANNEXURE
            if( empty($cTaskDetails -> compliance) /*&& $cTaskDetails -> option_id != 4*/ )
                $res['com_pending']++;

            // 'REARP'
            if( in_array($FILTER_TYPE, []) )
                $compulsaryClassStyle = '';

            // COM 
            else if(in_array($FILTER_TYPE, ['COM']) && !empty($cTaskDetails -> compliance))
                $compulsaryClassStyle = '';

            // RECOM 'RECOM'
            else if(in_array($FILTER_TYPE, []) && 
                    isset($data['db_assesment']) && 
                    $cTaskDetails -> batch_key == $data['db_assesment'] -> batch_key )
                    $compulsaryClassStyle = '';

            // RVCOM
            else if(in_array($FILTER_TYPE, ['RVCOM']) && 
                    $cTaskDetails -> compliance_status_id == 2 )
                    $compulsaryClassStyle = '';

            $str .= '<tr class="'. $compulsaryClassStyle .'">' . "\n";
            
                // SR NO
                $str .= '<td class="text-center">'. $srNo .'</td>' . "\n";
                $srNo++;
        

                $taskIds[] = $cTaskDetails->task_id;
                $multiDocsData = get_multi_docs_data(null, 2, [
                'circulr_id' => $tasksArray['header']->circular_set_id, 
                'task_ids' => $taskIds,
                'type' => 1
                ]);
                    
                if (is_array($multiDocsData) && !empty($multiDocsData)) {
                        foreach ($multiDocsData as $docId => $docData) {
                            if (isset($docData->task_id) && isset($tasksArray['tasks'][$docData->task_id])) {
                                // Append multiple markups instead of overwriting
                                if (!isset($tasksArray['tasks'][$docData->task_id]->markup)) {
                                    $tasksArray['tasks'][$docData->task_id]->markup = '';
                                }
                                $tasksArray['tasks'][$docData->task_id]->markup .= $docData->markup;
                            }
                        }
                }

      
                // QUESTION DESCRIPTION
                // if($checkCF)
                //     $str .= '<td>'. $cHeaderDetails['header'] -> name .'</td>' . "\n";
                // else
                  $str .= '<td>' . $cTaskDetails->task . '<br>' . ($cTaskDetails->markup ?? '') . '</td>' . "\n";


                // if($cTaskDetails -> option_id == '5')
                //     print_r($cTaskDetails);

                // ANSWER GIVEN // SUBSET ANSWER 
                // if(!$checkCF)
                // {
                    // if( $cTaskDetails -> option_id == '5' && 
                    //     isset($dataArray['subset_master']) && 
                    //     is_array($dataArray['subset_master']) && 
                    //     array_key_exists($cTaskDetails -> answer_given, $dataArray['subset_master']))
                    //     $str .= '<td>'. string_operations($dataArray['subset_master'][ $cTaskDetails -> answer_given ] -> name, 'upper');
                    // else // OTHER ANSWERS
                        // $str .= '<td>'. string_operations((/*($cTaskDetails -> option_id == '4' && !empty($cTaskDetails -> annexure_id) && $cTaskDetails -> answer_given == $cTaskDetails -> annexure_id ) ? AS_PER_ANNEXURE :*/ $cTaskDetails -> answer_given), 'upper');

                    // ADD EVIDANCE CHECK BOX
                    // if( in_array($FILTER_TYPE, ['RVAU','RVCOM']) && 
                    //     check_evidence_upload_strict() )
                    // {
                    //     $str .= '<div class="mt-2">' . "\n";

                    //     if( $FILTER_TYPE == 'RVAU') // function call
                    //         $str .= generate_evidence_checkbox_markup($cTaskDetails, [1], 1, 2);
                        
                    //     // function call
                    //     $str .= generate_evidence_checkbox_markup($cTaskDetails, [1,2], 2, 2);

                    //     $str .= '</div>' . "\n";
                    // }

                    // FOR EVIDENCE MARKUP
                    // if( in_array($FILTER_TYPE, ['RVAU','RVCOM','COM']) && 
                    //     check_evidence_upload_strict() && 
                    //     isset($cTaskDetails -> audit_evidence) )
                    // {
                    //     $str .= '<div class="mt-2">' . "\n";

                    //         // function call
                    //         $str .= display_evidence_markup($cTaskDetails, 1);

                    //     $str .= '</div>' . "\n";
                    // }

                //     $str .= '</td>' . "\n";
                // }
                // else
                //     $str .= '<td></td>';

                // AUDIT COMMENT
                // 'REARP', 'ARCRP', 'CRPWC', 'RVAU', , 'RECOM', 'RVCOM', 'PCDR', 'COMRP'
                // if( in_array($FILTER_TYPE, ['COM', 'RVCOM', 'RECOM']) )
                //     $str .= '<td>'. (!empty($cTaskDetails -> cco_comment) ? string_operations($cTaskDetails -> cco_comment, 'comma_space') : '') .'</td>' . "\n";

                // function call // risk type data
                $str .= generate_report_risk_columns($data, $FILTER_TYPE, $cTaskDetails);
                $reviewActionArray = null;
                $colSelect = null;

                // DEFAULT ACTION DISPLAY
                if( in_array($FILTER_TYPE, ['RVCOM']) && isset($data['data']['rv_compliance_status']) )
                {
                    $reviewActionArray = $data['data']['rv_compliance_status'];
                    $colSelect = 'compliance_status_id';
                }

                // documents
                if( in_array($FILTER_TYPE, ['COM','RECOM', 'RVCOM']) )
                {
                    $str .= '<td style="min-width: 140px">';

                        $docsMrk = generate_circular_docs_markup($cTaskDetails, [ 'container' => 1, 'mt' => 1 ]);

                        $extra = [ 
                            'mb' => 1, 
                            'circular_id' => $data['db_assesment'] -> circular_id,
                            'task_id' => $cTaskDetails -> task_id, 
                            'ans_id' => $cTaskDetails -> id,
                            'com_asses_id' => $cTaskDetails -> com_master_id
                        ];

                        if(empty($docsMrk))
                            $extra['need_container'] = 1;

                        if( in_array($data['db_assesment'] -> com_status_id, [1,3]) )
                            $str .= generate_compliance_doc_btn($extra, 5);

                        if(!empty($docsMrk))
                            $str .= $docsMrk;
                    
                        // <div class="my-2 position-relative question-row" data-ansid="' . encrypt_ex_data($cTaskDetails -> id) .'">
                            
                        //     <button class="evidence-upload-btn compliance-evi-btn" '. (!$activatEviBtn ? 'style="display:none"' : '') .''. view_tooltip('Upload File') .'>Evidence</button>                            
                            
                        //     <div class="evidence-upload-container compliance-evi-container">';
                            
                        //     // function call
                        //     // $str .= display_evidence_markup($cTaskDetails, 2);
                            
                        // $str .= '</div>
                        // </div>';

                        // if compulsary
                        // if( in_array($cTaskDetails -> compliance_compulsary_ev_upload, [1,2]) )
                        //     $str .= '<p class="font-sm text-danger"><b>Note:</b> Compulsary evidence upload from ('. (($cTaskDetails -> compliance_compulsary_ev_upload == 1) ? 'Reviewer' : 'Auditor') .')</p>';

                    $str .= '</td>';
                }

                // VIEW COMPLIANCE // 'CRPWC', 'RVCOM', 'PCDR'
                if(in_array($FILTER_TYPE, ['RVCOM']))
                    $str .= '<td>'. string_operations($cTaskDetails -> compliance, 'comma_space') .'</td>';

                // function call
                if( in_array($FILTER_TYPE, ['COM', 'RECOM']) )
                    $str .= generate_report_td_markup($FILTER_TYPE, $cTaskDetails, $reviewActionArray, $colSelect, 'gen');

                // function call // also display comment // 'REARP', 'RVCOM', 'RVAU'
                if( in_array($FILTER_TYPE, ['RVCOM']) )
                    $str .= generate_report_td_markup($FILTER_TYPE, $cTaskDetails, $reviewActionArray, $colSelect, 'gen');

                // VIEW REVIWER STATUS
                if(in_array($FILTER_TYPE, ['RECOM']))
                    $str .= '<td>'. string_operations(( isset(COMPLIANCE_PRO_ARRAY['review_compliance_status'][ $cTaskDetails -> compliance_status_id ]) ? COMPLIANCE_PRO_ARRAY['review_compliance_status'][ $cTaskDetails -> compliance_status_id ] : ERROR_VARS['notFound'] ), 'upper');

                // VIEW REVIWER COMMENT 
                if(in_array($FILTER_TYPE, ['RECOM']))
                    $str .= '<td>'. string_operations($cTaskDetails -> compliance_reviewer_comment, 'comma_space');

            $str .= '</tr>' . "\n";

         
      

        }
        
        return $str;
    }
}



?>