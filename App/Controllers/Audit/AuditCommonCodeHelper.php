<?php

// AUDIT & RE AUDIT FUNCTIONS 03.07.2024

if(!function_exists("get_menu_category_mix"))
{
    function get_menu_category_mix($this_obj, $assesmentData = null, $reAssessment = null)
    {
        $returnData = [];

        if(!empty($assesmentData -> menu_ids) && !empty($assesmentData -> cat_ids))
        {
            //if not empty
            $model = $this_obj -> model('MenuModel');
            $cfHasData = false;

            $whereData = [ 'where' => 'is_active = 1 AND deleted_at IS NULL', 'params' => [] ];            
            $c_menu_ids = [];

            //if has assesment history
            if(is_array($reAssessment) && isset($reAssessment['menu']))
            {
                // re assign to var
                $c_menu_ids = $reAssessment['menu'];
            }
            elseif(is_object($assesmentData))
            {
                $c_menu_ids = !empty($assesmentData -> menu_ids) ? explode(',', $assesmentData -> menu_ids) : [];
            }

            if(!empty($c_menu_ids))
            {
                if( check_carry_forward_strict() &&      
                    is_array($c_menu_ids) && 
                    in_array(CARRY_FORWARD_ARRAY['id'], $c_menu_ids))
                    $cfHasData = true;

                $c_menu_ids = '"' . implode('","', $c_menu_ids) . '"';
                $whereData['where'] .= ' AND id IN ('. $c_menu_ids .')';
            }

            // method call
            $returnData = $model -> getAllMenu($whereData);

            if($cfHasData)
            {
                $returnData = !is_array($returnData) ? [] : $returnData;
                $returnData[ CARRY_FORWARD_ARRAY['id'] ] = (object)[
                    "id" => CARRY_FORWARD_ARRAY['id'],
                    "section_type_id" => 1,
                    "name" => CARRY_FORWARD_ARRAY['title'],
                    "linked_table_id" => 0,
                    "is_active" => 1,
                    "admin_id" => 1,
                    "deleted_at" => NULL
                ];
            }

            if(is_array($returnData) && sizeof($returnData) > 0)
            {
                $returnData = generate_data_assoc_array($returnData, 'id');

                $model = $this_obj -> model('CategoryModel');

                $whereData = [ 'where' => 'is_active = 1 AND deleted_at IS NULL', 'params' => [] ];

                //if has assesment history
                if(is_array($reAssessment) && isset($reAssessment['category']) && sizeof($reAssessment['category']) > 0)
                    $whereData['where'] .= ' AND id IN ('. implode(',', $reAssessment['category']) .')';
                elseif(is_object($assesmentData))
                    $whereData['where'] .= ' AND id IN ('. $assesmentData -> cat_ids .')';

                // method call
                $categoryData = $model -> getAllCategory($whereData);

                if(is_array($categoryData) && sizeof($categoryData) > 0)
                {
                    foreach ($categoryData as $cData)
                    {
                        //if menu exists
                        if( array_key_exists($cData -> menu_id, $returnData) )
                        {
                            //push key
                            if(!isset($returnData[ $cData -> menu_id ] -> categories))
                                $returnData[ $cData -> menu_id ] -> categories = [];

                            // encrypt category
                            if(!isset($cData -> decrypt_cat_id))
                                $cData -> decrypt_cat_id = encrypt_ex_data( $cData -> id );

                            //push categories
                            $returnData[ $cData -> menu_id ] -> categories[ $cData -> id ] = $cData;
                        }
                    }
                }

                //unset vars
                unset($categoryData, $model);

                //unset keys which has no categories
                foreach($returnData as $cMenuId => $cMenuDetails)
                {
                    // encrypt menu
                    if(!isset($cMenuDetails -> decrypt_menu_id))
                        $returnData[ $cMenuId ] -> decrypt_menu_id = encrypt_ex_data( $cMenuId );

                    if($cMenuDetails -> id != 1 && !isset($cMenuDetails -> categories) && (!$cfHasData || ($cfHasData && !array_key_exists(CARRY_FORWARD_ARRAY['id'], $returnData))))
                        unset($returnData[ $cMenuId ]);
                }
            }
        }

        return $returnData;
    }
}

if(!function_exists("get_re_audit_find_data"))
{
    function get_re_audit_find_data($thisObj, $returnData = null, $res = false, $extra = [])
    {
        $returnData = !empty($returnData) ? $returnData : null;

        if($res)
            return $returnData;

        $checkCF = check_carry_forward_strict();

        if( is_array($returnData['menu']) && sizeof($returnData['menu']) > 0 /*&& 
            is_array($returnData['category']) && sizeof($returnData['category']) > 0*/)
        {   
            foreach($thisObj -> menuData as $cMenuId => $cMenuDetails)
            {
                // skip executive summary
                if($cMenuDetails -> id != 1 && ($checkCF && CARRY_FORWARD_ARRAY['id'] != $cMenuId))
                {
                    // for category
                    if( isset($cMenuDetails -> categories) && 
                        is_array($cMenuDetails -> categories) && 
                        sizeof($cMenuDetails -> categories) > 0 )
                    {
                        foreach($cMenuDetails -> categories as $cCatId => $cCatDetails)
                        {
                            if(/*$cMenuDetails -> id != 1 &&*/ !in_array($cCatDetails -> id, $returnData['category']))
                                unset($thisObj -> menuData[ $cMenuDetails -> id ] -> categories[ $cCatDetails -> id ]);        
                        }
                    }

                    // for menu
                    if( !in_array($cMenuDetails -> id, $returnData['menu']) || 
                        !isset($cMenuDetails -> categories) || 
                        ( is_array($cMenuDetails -> categories) && !sizeof($cMenuDetails -> categories) > 0 ))
                        unset($thisObj -> menuData[ $cMenuDetails -> id ]);                    
                }
            }
        }
        else
            $thisObj -> menuData = null;

        if(is_array($thisObj -> menuData) && sizeof($thisObj -> menuData) > 0)
        {
            $whereArray = [
                'where' => 'assesment_id = :assesment_id AND (
                    (is_compliance = 1 AND audit_status_id = 3) OR 
                    batch_key = :batch_key
                ) AND deleted_at IS NULL',
                'params' => [ 
                    'assesment_id' => $thisObj -> assesmentData -> id,
                    'batch_key' => $thisObj -> assesmentData -> batch_key
                ]
            ];

            // if category provide
            if(isset($extra['catId']))
            {
                // reaudit ans find
                $whereArray['where'] .= ' AND category_id = :category_id';
                $whereArray['params']['category_id'] = $extra['catId'];
            }

            // if dump id provide
            if(isset($extra['dumpId']))
            {
                // reaudit ans find
                $whereArray['where'] .= ' AND dump_id = :dump_id';
                $whereArray['params']['dump_id'] = $extra['dumpId'];
            }

            // check deleted at
            $whereArray['where'] .= ' AND deleted_at IS NULL';

            // find ans data
            $ansModel = $thisObj -> model('AnswerDataModel');
            $ansData = $ansModel -> getAllAnswers($whereArray); // method call

            if(is_array($ansData) && sizeof($ansData) > 0)
            {
                $returnData['reaudit_ans'] = [];
                $returnData['reaudit_annex'] = [];

                // loop on ans data
                foreach($ansData as $cAnsData)
                {
                    // push on ans data
                    $returnData['ans'][ $cAnsData -> id ] = $cAnsData;

                    if(!in_array($cAnsData -> id, $returnData['ans_ids']))
                        $returnData['ans_ids'][] = $cAnsData -> id;

                    if(!in_array($cAnsData -> header_id, $returnData['header']))
                        $returnData['header'][] = $cAnsData -> header_id;

                    if(!in_array($cAnsData -> question_id, $returnData['questions']))
                        $returnData['questions'][] = $cAnsData -> question_id;

                    // push ans id
                    if(!in_array($cAnsData -> id, $returnData['reaudit_ans']))
                        $returnData['reaudit_ans'][] = $cAnsData -> id;

                    // push dump ids on 
                    if( array_key_exists($cAnsData -> menu_id, $thisObj -> menuData) && 
                        array_key_exists($thisObj -> menuData[ $cAnsData -> menu_id ] -> linked_table_id, $GLOBALS['schemeTypesArray']))
                    {
                        if( $thisObj -> menuData[ $cAnsData -> menu_id ] -> linked_table_id == 2 && 
                            !in_array($cAnsData -> dump_id, $returnData['advance_dump_id'])) // for advances
                            $returnData['advance_dump_id'][] = $cAnsData -> dump_id;

                        elseif( $thisObj -> menuData[ $cAnsData -> menu_id ] -> linked_table_id == 1 && 
                                !in_array($cAnsData -> dump_id, $returnData['advance_dump_id'])) // for deposit
                            $returnData['deposite_dump_id'][] = $cAnsData -> dump_id;
                    }
                }

                // find annex rejected points
                $ansAnnexModel = $thisObj -> model('AnswerDataAnnexureModel');

                $annexRejectedData = $ansAnnexModel -> getAllAnswerAnnexures([
                    'where' => 'assesment_id = :assesment_id AND audit_status_id = 3 AND deleted_at IS NULL AND answer_id IN ('. implode(',', array_keys($returnData['ans'])) .')',
                    'params' => [ 'assesment_id' => $thisObj -> assesmentData -> id ]
                ]);

                // function call
                $tempRes = mix_re_audit_annex_data($annexRejectedData, $returnData['ans'], $returnData);
                $returnData = $tempRes['return_data'];

                $returnData['reaudit_ans'] = sizeof($returnData['reaudit_ans']);
                $returnData['reaudit_annex'] = sizeof($returnData['reaudit_annex']);

                // unset vars
                unset($tempRes);
            }

            // unset vars
            unset($ansData, $ansModel);

            // print_r($returnData);            
        }

        // echo '<pre>';
        // print_R($returnData);
        return $returnData;
    }
}

if(!function_exists("get_re_audit_menu_data"))
{
    function get_re_audit_menu_data($thisObj)
    {
        $returnData = [ 
            'menu' => [], 'category' => [], 'header' => [], 
            'questions' => [], 'ans' => [], 'ans_ids' => [], 'ans_annex_ids' => [], 
            'advance_dump_id' => [], 'deposite_dump_id' => [] ];

        $getClassName = explode("\\", get_class($thisObj));
        $getClassName = $getClassName[ sizeof($getClassName) - 1 ];

        if($getClassName != 'AuditContoller')
        {
            // create new object for Audit Controller
            require_once(CONTROLLER . DS . 'Audit' . DS . 'AuditContoller.php');
            $auditController = new \Controllers\Audit\AuditContoller(null);
        }
        else
            $auditController = $thisObj;

        // menu ids not found
        if( empty($thisObj -> assesmentData -> menu_ids) )
            return $returnData;

        $tempMenuIds = null;

        if(!empty($thisObj -> assesmentData -> menu_ids))
            $tempMenuIds = explode(',', $thisObj -> assesmentData -> menu_ids);

        // EXECUTIVE SUMMARY DATA CHECK - REJECTED POINTS
        if(is_array($tempMenuIds) && in_array('1', $tempMenuIds)):

            // check for executive summary 
            $model = $thisObj -> model('ExeSummaryBranchPositionModel');

            $findESBranchPosData = $model -> getSingleBranchPosition([
                'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND audit_status_id = 3 AND deleted_at IS NULL',
                'params' => [
                    'year_id' => $thisObj -> assesmentData -> year_id,
                    'assesment_id' => $thisObj -> assesmentData -> id,
                ]
            ]);

            // ADD EXECUTIVE SUMMARY
            if( is_object($findESBranchPosData) ) $returnData['menu'][] = 1;

            if(!in_array(1, $returnData['menu']))
            {
                $model = $thisObj -> model('ExeSummaryFreshAccountModel');

                $findESFinancialPosData = $model -> getSingleFreshAccount([
                    'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND audit_status_id = 3 AND deleted_at IS NULL',
                    'params' => [
                        'year_id' => $thisObj -> assesmentData -> year_id,
                        'assesment_id' => $thisObj -> assesmentData -> id,
                    ]
                ]);

                // ADD EXECUTIVE SUMMARY
                if( is_object($findESFinancialPosData) ) $returnData['menu'][] = 1;
            }

        endif;

        // find ans data
        $ansModel = $thisObj -> model('AnswerDataModel');

        // only get menu and categories
        $select = "SELECT DISTINCT category_id, menu_id, answer_given FROM answers_data";

        $ansData = get_all_data_query_builder(2, $ansModel, 'answers_data', [ 
            'where' => 'assesment_id = :assesment_id AND (
                (is_compliance = 1 AND audit_status_id = 3) OR 
                batch_key = :batch_key
            ) AND deleted_at IS NULL', 
            'params' => [ 
                'assesment_id' => $thisObj -> assesmentData -> id, 
                'batch_key' => $thisObj -> assesmentData -> batch_key
            ] 
        ], 'sql', $select);

        if(is_array($ansData) && sizeof($ansData) > 0)
        {
            $checkCF = check_carry_forward_strict();

            foreach($ansData as $cAnsDetails)
            {
                // check for carry forward
                if( $checkCF && 
                    CARRY_FORWARD_ARRAY['id'] == string_operations($cAnsDetails -> answer_given, 'upper'))
                {
                    if(!in_array(CARRY_FORWARD_ARRAY['id'], $returnData['menu']))
                        $returnData['menu'][] = CARRY_FORWARD_ARRAY['id'];
                }
                else
                {
                    if(!in_array($cAnsDetails -> menu_id, $returnData['menu']))
                        $returnData['menu'][] = $cAnsDetails -> menu_id;

                    if(!in_array($cAnsDetails -> category_id, $returnData['category']))
                        $returnData['category'][] = $cAnsDetails -> category_id;
                }
            }
        }

        return $returnData;
    }
}

if(!function_exists("mix_re_audit_annex_data"))
{
    function mix_re_audit_annex_data($annexData, $ansData, $returnData)
    {
        if( is_array($annexData) && sizeof($annexData) > 0 )
        {
            foreach($annexData as $cAnnexDetails)
            {
                if(array_key_exists($cAnnexDetails -> answer_id, $ansData))
                {
                    // push ans id // remove ans id
                    if(($key = array_search($cAnnexDetails -> answer_id, $returnData['reaudit_ans'])) !== false) {
                        unset($returnData['reaudit_ans'][$key]);
                        $returnData['reaudit_ans'] = array_values($returnData['reaudit_ans']);
                    }

                    if(!in_array($cAnnexDetails -> id, $returnData['reaudit_annex']))
                        $returnData['reaudit_annex'][] = $cAnnexDetails -> id;

                    // push annex answer id
                    if(!in_array($cAnnexDetails -> id, $returnData['ans_annex_ids']))
                        $returnData['ans_annex_ids'][] = $cAnnexDetails -> id;

                    if(!isset( $ansData[ $cAnnexDetails -> answer_id ] -> annex_ans))
                        $ansData[ $cAnnexDetails -> answer_id ] -> annex_ans = [];

                    $ansData[ $cAnnexDetails -> answer_id ] -> annex_ans[ $cAnnexDetails -> id ] = $cAnnexDetails;

                    if( $cAnnexDetails -> audit_status_id == 3 && 
                        $ansData[ $cAnnexDetails -> answer_id ] -> is_compliance == 1 /*&& 
                        $ansData[ $cAnnexDetails -> answer_id ] -> audit_status_id == 3*/ )
                    {
                        if(!isset($ansData[ $cAnnexDetails -> answer_id ] -> rejected))    
                            $ansData[ $cAnnexDetails -> answer_id ] -> rejected = true;
                    }
                }
            }
        }

        return [ 'ans_data' => $ansData, 'return_data' => $returnData ];
    }
}

if(!function_exists("get_set_all_data"))
{
    function get_set_all_data($this_obj, $questionSetIds, $assesmentData = null, $subset = false, $reAuditFindData = null, $dumpId = null)
    {
        $returnData = [ 'returnData' => [], 'questionsData' => [] ];

        $questionSetIds = !empty($questionSetIds) ? explode(',', $questionSetIds) : [];

        $questionSetModel = $this_obj -> model('QuestionSetModel');
        $questionMasterModel = $this_obj -> model('QuestionMasterModel');
        $subsetCheckReAudit = true;

        if( is_array($questionSetIds) &&
            sizeof($questionSetIds) > 0 && !$subset && 
            check_re_assesment_status($assesmentData) &&
            is_array($reAuditFindData) && sizeof($reAuditFindData['header']) > 0 )
        {
            if( !empty($dumpId) )
            {
                // single dump wise questions 17.05.2024
                if( is_array($reAuditFindData['ans']) && sizeof($reAuditFindData['ans']) > 0 )
                {
                    $reAuditFindData['questions'] = [];

                    foreach($reAuditFindData['ans'] as $cAnsDetails)
                    {
                        if( $dumpId == $cAnsDetails -> dump_id && 
                            !in_array($cAnsDetails -> question_id, $reAuditFindData['questions']))
                            $reAuditFindData['questions'][] = $cAnsDetails -> question_id;
                    }
                }
                else
                    $reAuditFindData['questions'] = null;
            }
            
            // find set for re audit 17.05.2024
            $whereData = [ 
                'where' => 'set_id IN ('. implode(',', $questionSetIds) .') AND option_id = 5 AND is_active = 1 AND deleted_at IS NULL', 
                'params' => [ ] 
            ];

            // print_r($reAuditFindData);

            $questionData = $questionMasterModel -> getAllQuestions( $whereData );     

            if(is_array($questionData) && sizeof($questionData) > 0)
            {
                foreach($questionData as $cQuesDetails)
                {
                    if(!empty($cQuesDetails -> subset_multi_id))
                    {
                        $tempSubsetMultiIds = explode(',', $cQuesDetails -> subset_multi_id);

                        if( is_array($tempSubsetMultiIds) && sizeof($tempSubsetMultiIds) > 0 )
                            $questionSetIds = array_merge($questionSetIds, $tempSubsetMultiIds);

                        // check question exists in re audit
                        if( is_array($reAuditFindData['questions']) && 
                            in_array($cQuesDetails -> id, $reAuditFindData['questions']))
                            $subsetCheckReAudit = false;
                    }
                }
            }

            $questionSetIds = array_unique($questionSetIds);

            unset($questionData);
        }       

        // if not size of
        if( !sizeof($questionSetIds) > 0 )
            return [ 'returnData' => [], 'questionsData' => [] ];

        // if has assesment data 
        if( is_object($assesmentData) && !sizeof($questionSetIds) > 0 )
            return [ 'returnData' => [], 'questionsData' => [] ];

        $whereData = [ 'where' => 'is_active = 1 AND deleted_at IS NULL', 'params' => [] ];

        if(is_object($assesmentData))
            $whereData['where'] .= ' AND id IN ('. implode(',', $questionSetIds) .')';

        if(!$subset && !$subsetCheckReAudit)
            $whereData['where'] .= ' AND set_type_id = 1';

        // method call
        $returnData['returnData'] = $questionSetModel -> getAllQuestionSet($whereData);
        $returnData['returnData'] = generate_data_assoc_array($returnData['returnData'], 'id');

        if(is_array($returnData['returnData']) && sizeof($returnData['returnData']) > 0)
        {
            // has data // find headers
            $questionHeaderModel = $this_obj -> model('QuestionHeaderModel');

            // check for header
            if( is_object($assesmentData) && !sizeof($assesmentData -> header_ids_explode) > 0 )
                return $returnData;

            $whereData = [ 
                'where' => 'question_set_id IN ('. implode(',', array_keys($returnData['returnData'])) .') AND is_active = 1 AND deleted_at IS NULL', 
                'params' => [ ] 
            ];

            if( $reAuditFindData == null && is_object($assesmentData) && sizeof($assesmentData -> header_ids_explode) > 0 )
                $whereData['where'] .= ' AND id IN ('. implode(',', $assesmentData -> header_ids_explode) .')';
            elseif( is_array($reAuditFindData) && sizeof($reAuditFindData['header']) > 0 )
                $whereData['where'] .= ' AND id IN ('. implode(',', $reAuditFindData['header']) .')';

            if( ( $reAuditFindData == null && $assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'] ) || 
                ( check_re_assesment_status($assesmentData) && 
                  is_array($reAuditFindData) && sizeof($reAuditFindData['header']) > 0 ) )
                $headerData = $questionHeaderModel -> getAllQuestionHeader( $whereData );     
            else
                $headerData = null;

            // print_r($headerData);

            if( is_array($headerData) && sizeof($headerData) > 0 )
            {
                $activeHeaders = [];

                foreach($headerData as $cHeaderIndex => $cHeaderDetails)
                {
                    // push data
                    if( array_key_exists( $cHeaderDetails -> question_set_id, $returnData['returnData'] ) )
                    { 
                        if(/*!$subset && !$subsetCheckReAudit && */
                            $returnData['returnData'][ $cHeaderDetails -> question_set_id ] -> set_type_id == 2 )
                            $cHeaderDetails -> name = 'SUBSET: ' . trim_str($cHeaderDetails -> name) . ' ('. trim_str($returnData['returnData'][ $cHeaderDetails -> question_set_id ] -> name) .')';


                        if( !isset($returnData['returnData'][ $cHeaderDetails -> question_set_id ] -> headers) )
                            $returnData['returnData'][ $cHeaderDetails -> question_set_id ] -> headers = [];

                        // push headers data
                        $returnData['returnData'][ $cHeaderDetails -> question_set_id ] -> headers[ $cHeaderDetails -> id ] = $cHeaderDetails;
                        $activeHeaders[] = $cHeaderDetails -> id;
                    }
                }

                $whereData = [
                    'where' => 'header_id IN ('. implode(',', $activeHeaders) .') AND is_active = 1 AND deleted_at IS NULL', 
                    'params' => [ ]
                ];

                $findData = true;

                if( is_object($assesmentData) && empty($assesmentData -> question_ids) )
                    $findData = false;
                elseif( $reAuditFindData == null && is_object($assesmentData) && !empty($assesmentData -> question_ids) )
                    $whereData['where'] .= ' AND id IN ('. $assesmentData -> question_ids .')';
                elseif( is_array($reAuditFindData) && sizeof($reAuditFindData['questions']) > 0 )
                    $whereData['where'] .= ' AND id IN ('. implode(',', $reAuditFindData['questions']) .')';

                if( $findData && check_re_assesment_status($assesmentData) && 
                    (!is_array($reAuditFindData) || (is_array($reAuditFindData) && !sizeof($reAuditFindData['questions']) > 0)) )
                    $findData = false;

                if($findData)
                {
                    // has data //find questions
                    $questionData = $questionMasterModel -> getAllQuestions($whereData);

                    if( is_array($questionData) && sizeof($questionData) > 0 )
                    {
                        // find annexure data with columns
                        $model = $this_obj -> model('AnnexureMasterModel');

                        // get all without delete check
                        $annexMasterData = $model -> getAllAnnexures([ 'where' => 'deleted_at IS NULL', 'params' => [] ]);
                        $annexMasterData = generate_data_assoc_array($annexMasterData, 'id');

                        if(is_array($annexMasterData) && sizeof($annexMasterData) > 0)
                        {
                            // find annexure columns
                            $model = $this_obj -> model('AnnexureColumnModel');

                            $annexColumnData = $model -> getAllAnnexureColumns([
                                'where' => 'annexure_id IN ('. implode(',', array_keys($annexMasterData)) .')'
                            ]);

                            if(is_array($annexColumnData) && sizeof($annexColumnData) > 0)
                            {
                                foreach($annexColumnData as $cAnnexColumnDetails)
                                {
                                    if(array_key_exists($cAnnexColumnDetails -> annexure_id, $annexMasterData))
                                    {
                                        // id exists in data
                                        if(!isset($annexMasterData[ $cAnnexColumnDetails -> annexure_id ] -> annex_cols))
                                            $annexMasterData[ $cAnnexColumnDetails -> annexure_id ] -> annex_cols = [];

                                        //push data
                                        $annexMasterData[ $cAnnexColumnDetails -> annexure_id ] -> annex_cols[ $cAnnexColumnDetails -> id ] = $cAnnexColumnDetails;
                                    
                                    }
                                }
                            }
                        }

                        foreach($questionData as $cQuestionIndex => $cQuestionDetails)
                        {
                            // push data
                            if( array_key_exists( $cQuestionDetails -> set_id, $returnData['returnData'] ) && 
                                isset( $returnData['returnData'][ $cQuestionDetails -> set_id ] -> headers ) &&
                                array_key_exists( $cQuestionDetails -> header_id, $returnData['returnData'][ $cQuestionDetails -> set_id ] -> headers )
                            )
                            {
                                if(!isset( $returnData['returnData'][ $cQuestionDetails -> set_id ] -> headers[ $cQuestionDetails -> header_id ] -> questions ) )
                                    $returnData['returnData'][ $cQuestionDetails -> set_id ] -> headers[ $cQuestionDetails -> header_id ] -> questions = [];

                                // check for annexure
                                if($cQuestionDetails -> option_id == 4)
                                {
                                    $cQuestionDetails -> annexure_id_details = null;

                                    // push data
                                    if(is_array($annexMasterData) && array_key_exists($cQuestionDetails -> annexure_id, $annexMasterData))
                                        $cQuestionDetails -> annexure_id_details = $annexMasterData[ $cQuestionDetails -> annexure_id ];
                                }

                                // check for subset // $subset do not want subset under subset
                                elseif(!$subset && $cQuestionDetails -> option_id == 5 && !empty($cQuestionDetails -> subset_multi_id))
                                {
                                    $cQuestionDetails -> subset_data = get_set_all_data($this_obj, $cQuestionDetails -> subset_multi_id, $assesmentData, true, $reAuditFindData)['returnData'];
                                    $cQuestionDetails -> subset_index = [];

                                    if( is_array($cQuestionDetails -> subset_data) && 
                                        sizeof($cQuestionDetails -> subset_data) )
                                    {
                                        foreach($cQuestionDetails -> subset_data as $cSubSetId => $cSubSetDetails)
                                            $cQuestionDetails -> subset_index[ $cSubSetId ] = $cSubSetDetails -> name;
                                    }
                                }

                                // push questions data
                                $returnData['returnData'][ $cQuestionDetails -> set_id ] -> headers[ $cQuestionDetails -> header_id ] -> questions[ $cQuestionDetails -> id ] = $cQuestionDetails;
                            }
                        }
                    }

                    unset($questionData);
                }
            }

            unset($headerData);

            // loop data and remove empty
            foreach($returnData['returnData'] as $cSetId => $cSetDetails)
            {
                if( isset($cSetDetails -> headers) && sizeof($cSetDetails -> headers) > 0 )
                {
                    foreach($cSetDetails -> headers as $cSetHeaderId => $cSetHeaderDetails)
                    {
                        if(!isset($cSetHeaderDetails -> questions) || 
                           (isset($cSetHeaderDetails -> questions) && !sizeof($cSetHeaderDetails -> questions) > 0) )
                            unset($returnData['returnData'][ $cSetId ] -> headers[ $cSetHeaderId ]);

                        // push array
                        if( isset($cSetHeaderDetails -> questions) && sizeof($cSetHeaderDetails -> questions) > 0 )
                        $returnData['questionsData'] = array_merge($returnData['questionsData'], $cSetHeaderDetails -> questions);
                    }
                }

                // check for headers
                if(!isset($cSetDetails -> headers) || (isset($cSetDetails -> headers) && !sizeof($cSetDetails -> headers) > 0) )
                    unset($returnData['returnData'][ $cSetId ]);
            }
        }

        // if(!$subset)
        // {
        //     echo '<pre>';
        //     print_r($returnData['returnData']);
        // }

        return $returnData;
    }
}

if(!function_exists("get_all_question_data"))
{
    function get_all_question_data($this_obj, $assesmentData, $reAssessment = null)
    {
        $returnData = [ 'db_menu' => [], 'db_category' => [], 'db_sets' => [] ];

        if(!is_object($assesmentData))
            return null;

        // find menu and categories
        $returnData['db_menu'] = get_menu_category_mix($this_obj, $assesmentData, $reAssessment);

        $setsArray = array();

        // loop data
        if(is_array($returnData['db_menu']) && sizeof($returnData['db_menu']) > 0)
        {
            $cfId = check_carry_forward_strict() ? CARRY_FORWARD_ARRAY['id'] : null;

            foreach($returnData['db_menu'] as $cMenuId => $cMenuDetails)
            {
                // NOT CF
                if( empty($cfId) || $cfId != $cMenuId)
                {
                    // remove executive summary // OR categories not exists
                    if($cMenuId == '1' || ( !isset($cMenuDetails -> categories) || !is_array($cMenuDetails -> categories) || ( is_array($cMenuDetails -> categories) && !sizeof($cMenuDetails -> categories) > 0 ) ))
                        unset($returnData['db_menu'][ $cMenuId ]);
                    else
                    {                    
                        foreach($cMenuDetails -> categories as $cCatId => $cCatDetails)
                        {
                            if(!array_key_exists($cCatDetails -> id, $returnData['db_category']))
                                $returnData['db_category'][ $cCatDetails -> id ] = $cCatDetails;
            
                            if( !empty($cCatDetails -> question_set_ids) )
                            {
                                $questionSetIds = explode(',', $cCatDetails -> question_set_ids);
            
                                if( is_array($questionSetIds) && sizeof($questionSetIds) > 0 )
                                {
                                    foreach($questionSetIds as $cSetId)
                                    {
                                        // push set id in array
                                        if(!in_array($cSetId, $setsArray))
                                            $setsArray[] = $cSetId;
                                    }
                                }
                            }
                        }
            
                        unset( $tempDbCategory );
                    }
                }
            }

            if( sizeof($setsArray) > 0 )
            {
                // find questions with subset sets
                $model = $this_obj -> model('QuestionMasterModel');
                
                $findSubsetQuestions = $model -> getAllQuestions([
                    'where' => 'set_id IN ('. implode(',', $setsArray) .') AND option_id = 5 AND deleted_at IS NULL', 
                    'params' => []
                ]);

                if(is_array($findSubsetQuestions) && sizeof($findSubsetQuestions) > 0)
                {
                    foreach($findSubsetQuestions as $cQuesDetails)
                    {
                        if(!empty($cQuesDetails -> subset_multi_id))
                        {
                            $cQuesDetails -> subset_multi_id = explode(',', $cQuesDetails -> subset_multi_id);
                            $setsArray = array_merge($setsArray, $cQuesDetails -> subset_multi_id);
                        }
                    }
                }
                
                $returnData['db_sets'] = get_set_all_data($this_obj, implode(',', $setsArray), $assesmentData, false, $reAssessment);

                $returnData['db_questions'] = $returnData['db_sets']['questionsData'];
                $returnData['db_sets'] = $returnData['db_sets']['returnData'];
                
            }
        }

        return $returnData;
    }
}

if(!function_exists("get_category_dump_data"))
{
    function get_category_dump_data($this_obj, $categoryDetails, $assesmentData, $sampling = 1, $dumpWhereData = [ 'where' => '', 'params' => [] ], $orderBy = null, $reAuditFindData = null)
    {
        $returnData = [];
        $withoutSamplingCount = null;

        if(!is_object($assesmentData))
            return $returnData;

        $model = $this_obj -> model('SchemeModel');

        $whereData = [
            'where' => 'scheme_type_id = :scheme_type_id AND category_id = :category_id AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ 
                'scheme_type_id' => $categoryDetails -> linked_table_id, 
                'category_id' => $categoryDetails -> id 
            ]
        ];

        $dumpModel = $this_obj -> model('DumpAdvancesModel');

        if($categoryDetails -> linked_table_id == 1)
        {
            $whereData['where'] .= ' AND id IN ('. implode(',', $assesmentData -> deposits_scheme_ids_explode) .')';
            $dumpModel = $this_obj -> model('DumpDepositeModel');
        }
        else /*if($this -> data['db_category'] -> linked_table_id == 2)*/
            $whereData['where'] .= ' AND id IN ('. implode(',', $assesmentData -> advances_scheme_ids_explode) .')';

        // no account selected display all accounts
        $returnData = $model -> getAllSchemes( $whereData );
        $returnData = generate_data_assoc_array($returnData, 'id');
        $accountsData = [];

        if(is_array( $returnData ) && sizeof($returnData) > 0)
        {
            // find accounts
            if(!empty($dumpWhereData['where']))
                $dumpWhereData['where'] .= ' AND ';

            // where
            $dumpWhereData['where'] .= 'branch_id = :branch_id AND scheme_id IN ('. implode(',', array_keys($returnData)) .')';
            
            // strict check
            if($categoryDetails -> is_cc_acc_category == true && $categoryDetails -> linked_table_id == 2)
                $dumpWhereData['where'] .= ' AND ((account_opening_date BETWEEN :audit_start_date AND :audit_end_date) OR (renewal_date BETWEEN :audit_start_date AND :audit_end_date))';
            else            
                $dumpWhereData['where'] .= ' AND account_opening_date BETWEEN :audit_start_date AND :audit_end_date';
                
            $dumpWhereData['where'] .= ' AND deleted_at IS NULL';

            // params
            $dumpWhereData['params']['branch_id'] = $assesmentData -> audit_unit_id;
            $dumpWhereData['params']['audit_start_date'] = $assesmentData -> assesment_period_from;
            $dumpWhereData['params']['audit_end_date'] = $assesmentData -> assesment_period_to;                

            $withoutSamplingCount = $dumpModel -> getSingleAccount($dumpWhereData, 'sql', 'SELECT COUNT(id) as total FROM ' . $dumpModel -> getTableName());

            if($sampling == 1)
                $dumpWhereData['where'] .= ' AND sampling_filter = 1';
            elseif($sampling == 2)
                $dumpWhereData['where'] .= ' AND sampling_filter = 0';

            // re assessment // 17.05.2024
            if( check_re_assesment_status($assesmentData) && is_array($reAuditFindData) )
            {
                // for advances
                if( $categoryDetails -> linked_table_id == 2 && 
                    is_array($reAuditFindData['advance_dump_id']) && 
                    sizeof($reAuditFindData['advance_dump_id']) > 0)
                    $dumpWhereData['where'] .= ' AND id IN ('. implode(',', $reAuditFindData['advance_dump_id']) .')';

                // for deposits
                elseif( is_array($reAuditFindData['deposite_dump_id']) && 
                        sizeof($reAuditFindData['deposite_dump_id']) > 0)
                        $dumpWhereData['where'] .= ' AND id IN ('. implode(',', $reAuditFindData['deposite_dump_id']) .')';
            }

            if(!empty($orderBy))
            {
                // add account no
                if(!preg_match("/\baccount_no\b/", $orderBy))
                    $orderBy .= ', account_no+0';

                $dumpWhereData['where'] .= ' ORDER BY ' . $orderBy;
            }
            else
                $dumpWhereData['where'] .= ' ORDER BY account_no+0';

            $accountsData = $dumpModel -> getAllAccounts($dumpWhereData);

            if( is_array($accountsData) && sizeof($accountsData) > 0 )
            {
                $tempAccountData = $accountsData;
                $accountsData = [];

                foreach($tempAccountData as $cAccDetails)
                {
                    // add scheme code and scheme name
                    $accountsData[ $cAccDetails -> id ] = $cAccDetails;
                    $accountsData[ $cAccDetails -> id ] -> scheme_id_code = ERROR_VARS['notFound'];
                    $accountsData[ $cAccDetails -> id ] -> scheme_id_name = ERROR_VARS['notFound'];

                    if( array_key_exists($cAccDetails -> scheme_id, $returnData) )
                    {
                        $accountsData[ $cAccDetails -> id ] -> scheme_id_code = string_operations($returnData[ $cAccDetails -> scheme_id ] -> scheme_code, 'upper');
                        $accountsData[ $cAccDetails -> id ] -> scheme_id_name = string_operations($returnData[ $cAccDetails -> scheme_id ] -> name, 'upper');
                    }
                }
            }

            // unset var
            unset($tempAccountData);

            // remove unwated schemes
            /* foreach($returnData as $cSchemeId => $cSchemeDetails)
            {
                if(!isset($cSchemeDetails -> accounts) || 
                    (isset($cSchemeDetails -> accounts) && !sizeof($cSchemeDetails -> accounts) > 0) )
                    unset($returnData[ $cSchemeId ]);
            } */
        }

        return [ 'dump_data' => $accountsData, 'scheme_data' => $returnData, 'count' => $withoutSamplingCount ];
    }
}

if(!function_exists("get_category_dump_data_report"))
{
    function get_category_dump_data_report($this_obj, $dbCategory, $assesmentData, $sampling = 1, $linkedTableId = 2, $dumpWhereData = [], $orderBy = null, $reAuditFindData = null)
    {
        $returnData = [];

        if( empty($dumpWhereData) )
            $dumpWhereData = [ 'where' => '', 'params' => [] ];

        if(!is_object($assesmentData) || (!is_array($dbCategory) || is_array($dbCategory) && !sizeof($dbCategory) > 0))
            return $returnData;

        $catArray = [];

        // push category ids
        foreach($dbCategory as $cCatId => $cCatDetails) {
            if( $cCatDetails -> linked_table_id == $linkedTableId && !in_array($cCatId, $catArray) )
                $catArray[] = $cCatId;
        }

        if(!sizeof($catArray) > 0)
            return $returnData;

        $model = $this_obj -> model('SchemeModel');

        $whereData = [
            'where' => 'scheme_type_id = :scheme_type_id AND category_id IN ('. implode(',', $catArray) .') AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ 'scheme_type_id' => $linkedTableId ]
        ];

        $dumpModel = $this_obj -> model('DumpAdvancesModel');

        if($linkedTableId == 1)
        {
            $whereData['where'] .= ' AND id IN ('. implode(',', $assesmentData -> deposits_scheme_ids_explode) .')';
            $dumpModel = $this_obj -> model('DumpDepositeModel');
        }
        else /* if($this -> data['db_category'] -> linked_table_id == 2) */
            $whereData['where'] .= ' AND id IN ('. implode(',', $assesmentData -> advances_scheme_ids_explode) .')';

        // no account selected display all accounts
        $returnData = $model -> getAllSchemes( $whereData );
        $returnData = generate_data_assoc_array($returnData, 'id');
        $accountsData = [];

        if(is_array( $returnData ) && sizeof($returnData) > 0)
        {
            // find accounts
            if(!empty($dumpWhereData['where']))
                $dumpWhereData['where'] .= ' AND ';

            // where
            $dumpWhereData['where'] .= 'branch_id = :branch_id AND scheme_id IN ('. implode(',', array_keys($returnData)) .')';
            $periodData = true;
            // assesment_period_id = 0 AND 
 
            // strict check //category not available due to we check all renewal accounts
            if( /*$dbCategory -> is_cc_acc_category == true && $dbCategory ->*/ $linkedTableId == 2)
            {
                if( check_re_assesment_status($assesmentData) && 
                    is_array($reAuditFindData) && sizeof($reAuditFindData) > 0 && 
                    sizeof($reAuditFindData['advance_dump_id']) > 0 )
                {
                    $dumpWhereData['where'] .= ' AND id IN ('. implode(',', $reAuditFindData['advance_dump_id']) .')';
                    $periodData = false;
                }
                else
                    $dumpWhereData['where'] .= ' AND ((account_opening_date BETWEEN :audit_start_date AND :audit_end_date) OR (renewal_date BETWEEN :audit_start_date AND :audit_end_date))';
            }
            else            
            {
                if( check_re_assesment_status($assesmentData) && 
                    is_array($reAuditFindData) && sizeof($reAuditFindData) > 0 && 
                    sizeof($reAuditFindData['deposite_dump_id']) > 0 )
                {
                    $dumpWhereData['where'] .= ' AND id IN ('. implode(',', $reAuditFindData['deposite_dump_id']) .')';
                    $periodData = false;
                }
                else
                    $dumpWhereData['where'] .= ' AND account_opening_date BETWEEN :audit_start_date AND :audit_end_date';                
            }
                
            $dumpWhereData['where'] .= ' AND deleted_at IS NULL';

            // params
            $dumpWhereData['params']['branch_id'] = $assesmentData -> audit_unit_id;

            if($periodData)
            {
                $dumpWhereData['params']['audit_start_date'] = $assesmentData -> assesment_period_from;
                $dumpWhereData['params']['audit_end_date'] = $assesmentData -> assesment_period_to;
            }

            // $withoutSamplingCount = $dumpModel -> getSingleAccount($dumpWhereData, 'sql', 'SELECT COUNT(id) as total FROM ' . $dumpModel -> getTableName());

            if($sampling == 1)
                $dumpWhereData['where'] .= ' AND sampling_filter = 1';
            elseif($sampling == 2)
                $dumpWhereData['where'] .= ' AND sampling_filter = 0';

            if(!empty($orderBy))
                $dumpWhereData['where'] .= ' ORDER BY ' . $orderBy;

                // print_r($dumpWhereData);

            $accountsData = $dumpModel -> getAllAccounts($dumpWhereData);

            if( is_array($accountsData) && sizeof($accountsData) > 0 )
            {
                $tempAccountData = $accountsData;
                $accountsData = [];

                foreach($tempAccountData as $cAccDetails)
                {
                    // add scheme code and scheme name
                    $accountsData[ $cAccDetails -> id ] = $cAccDetails;
                    $accountsData[ $cAccDetails -> id ] -> scheme_id_code = ERROR_VARS['notFound'];
                    $accountsData[ $cAccDetails -> id ] -> scheme_id_name = ERROR_VARS['notFound'];

                    if( array_key_exists($cAccDetails -> scheme_id, $returnData) )
                    {
                        // push dunp id
                        if(!isset($returnData[ $cAccDetails -> scheme_id ] -> accounts))
                            $returnData[ $cAccDetails -> scheme_id ] -> accounts = [];

                        $returnData[ $cAccDetails -> scheme_id ] -> accounts[] = $cAccDetails -> id;

                        $accountsData[ $cAccDetails -> id ] -> scheme_id_code = string_operations($returnData[ $cAccDetails -> scheme_id ] -> scheme_code, 'upper');
                        $accountsData[ $cAccDetails -> id ] -> scheme_id_name = string_operations($returnData[ $cAccDetails -> scheme_id ] -> name, 'upper');
                    }
                }
            }

            // unset var
            unset($tempAccountData);

            // remove unwated schemes
            foreach($returnData as $cSchemeId => $cSchemeDetails)
            {
                if( !isset($cSchemeDetails -> accounts) || 
                    (isset($cSchemeDetails -> accounts) && !sizeof($cSchemeDetails -> accounts) > 0) )
                    unset($returnData[ $cSchemeId ]);
                else
                {
                    //category sort and push keys
                    if(array_key_exists($cSchemeDetails -> category_id, $dbCategory))
                    {
                        if(!isset( $dbCategory[ $cSchemeDetails -> category_id ] -> schemes ))
                            $dbCategory[ $cSchemeDetails -> category_id ] -> schemes = [];

                        if(!in_array( $cSchemeId, $dbCategory[ $cSchemeDetails -> category_id ] -> schemes ))
                            $dbCategory[ $cSchemeDetails -> category_id ] -> schemes[ $cSchemeId ] = sizeof($cSchemeDetails -> accounts);
                    }
                }
            }

            // print_r($dbCategory);
        }

        return [ 'dump_data' => $accountsData, 'scheme_data' => $returnData, 'db_category' => $dbCategory ];
    }
}

if(!function_exists('modified_answers_data'))
{
    function modified_answers_data($answersData)
    {
        $returnData = [];

        if(!is_array($answersData) || ( is_array($answersData) && !sizeof($answersData) > 0 ))
            return $returnData;

        // function call
        $cfCheck = check_carry_forward_strict();

        foreach($answersData as $cAns)
        {
            $cGenKey = $cAns -> header_id . '_' . $cAns -> question_id . '_' . $cAns -> dump_id;

            if($cfCheck && $cAns -> answer_given == CARRY_FORWARD_ARRAY['id'])
                $cGenKey .= '_' . CARRY_FORWARD_ARRAY['id'];

            $returnData[ $cGenKey ] = $cAns;
        }

        unset($answersData);

        return $returnData;
    }
}

if(!function_exists('check_answer_exists_on_question_id'))
{
    function check_answer_exists_on_question_id($questionId, $questionData)
    {
        $returnData = null;

        if( is_array($questionData) && sizeof($questionData) > 0 )
        {
            foreach($questionData as $cAnsId => $cAnsDetails)
            {
                // answer found // send update
                if( $cAnsDetails -> question_id == $questionId )
                    $returnData = $cAnsDetails;
            }
        }

        return $returnData;
    }
}

?>