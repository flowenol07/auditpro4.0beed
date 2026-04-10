<?php

// find question set wise data
if(!function_exists('PERFORMANCE_REPORT_FIND_SET_WISE_QUESTION_DATA')) {

    function PERFORMANCE_REPORT_FIND_SET_WISE_QUESTION_DATA($thisObj, $extra = [])
    {
        $model = $thisObj -> model('QuestionMasterModel');
        $dbTable = $model -> getTableName();
        $res = [];

        $questionData = get_all_data_query_builder(2, $model, $dbTable, $extra['filter'], 'sql', "SELECT id, set_id, parameters, risk_category_id, option_id, annexure_id, subset_multi_id FROM " . $dbTable);

        if(is_array($questionData) && sizeof($questionData) > 0)
        {
            foreach($questionData as $cQuesData)
            {
                $cRiskCnt = 0;

                // calculate highest risk // calculate risk matrix // skip general question
                if( !empty($cQuesData -> parameters) && 
                    !in_array($cQuesData -> option_id, [3]) )
                {
                    try {
                                                
                        $parameter = null;
                        
                        $parameters = json_decode($cQuesData -> parameters);
                        
                        if( is_array($parameters) ):

                            foreach($parameters as $cParam):

                                $cRiskWeight = $cParam -> br . '.' . $cParam -> cr;

                                // check key exists
                                if(array_key_exists($cRiskWeight, $extra['matrixRisk']))
                                {
                                    if($extra['matrixRisk'][ $cRiskWeight ] > $cRiskCnt)
                                        $cRiskCnt = $extra['matrixRisk'][ $cRiskWeight ];
                                }                        
                            
                            endforeach;

                        endif;

                    } catch (Exception $th) { /* throw $th; */ }
                }

                $cQuesData -> highest_risk = $cRiskCnt;
                $cSetId = $cQuesData -> set_id;

                // unset keys
                unset(
                    $cQuesData -> set_id, 
                    $cQuesData -> parameters
                );
                
                if(!array_key_exists($cSetId, $res))
                    $res[ $cSetId ] = [];

                $res[ $cSetId ][ $cQuesData -> id ] = (array) $cQuesData;
            }
        }

        return $res;
    }
}

if(!function_exists('PERFORMANCE_REPORT_SHORT_FUNCTION_GET_DUMP_DATA')) {

    function PERFORMANCE_REPORT_SHORT_FUNCTION_GET_DUMP_DATA($thisObj, $NEW_ASSES_DATA, $dateFilters, $type = 1)
    {
        $query = "";

        if( $type == 2 )
        {
            // FOR ADVANCES
            $query = " AND (
                        dt.account_opening_date BETWEEN '". $dateFilters['start_date'] ."' AND '". $dateFilters['end_date'] ."' 
                        OR dt.renewal_date BETWEEN '". $dateFilters['start_date'] ."' AND '". $dateFilters['end_date'] ."'
                    )";
            $model = $thisObj -> model( 'DumpAdvancesModel' );
        }
        else
        {
            // FOR DEPOSITS
            $model = $thisObj -> model( 'DumpDepositeModel' );
            $query = " AND dt.account_opening_date BETWEEN '". $dateFilters['start_date'] ."' AND '". $dateFilters['end_date'] ."'";
        }

        $dbTable = $model -> getTableName();

        $query = "SELECT 
                    dt.id, 
                    dt.branch_id, 
                    dt.account_opening_date, 
                    dt.sampling_filter, 
                    dt.assesment_period_id, 
                    COALESCE(sm.category_id, 0) AS cat_id 
                FROM 
                    ". $dbTable ." dt 
                LEFT JOIN 
                    scheme_master sm 
                ON 
                    dt.scheme_id = sm.id 
                WHERE 
                    dt.deleted_at IS NULL 
                    AND dt.branch_id IN (". implode(',', $dateFilters['branch_ids']) .")" . $query;        

        $dumpData = get_all_data_query_builder(2, $model, $dbTable, [ ], 'sql', $query);

        if(is_array($dumpData) && sizeof($dumpData) > 0)
        {
            foreach($dumpData as $cDumpDetails)
            {
                foreach($NEW_ASSES_DATA as $cAssesId => $cAssesData)
                {
                    if( $cAssesData['audit_unit_id'] == $cDumpDetails -> branch_id && 
                        strtotime($cAssesData['assesment_period_from']) <= strtotime($cDumpDetails -> account_opening_date) && 
                        strtotime($cDumpDetails -> account_opening_date) <= strtotime($cAssesData['assesment_period_to']) )
                    {
                        // check category exists or not
                        if( !empty($cDumpDetails -> cat_id) &&
                            isset($cAssesData['active_category_data']) && 
                            is_array($cAssesData['active_category_data']) && 
                            array_key_exists($cDumpDetails -> cat_id, $cAssesData['active_category_data']) && 
                            in_array($cAssesData['active_category_data'][ $cDumpDetails -> cat_id ]['linked_table_id'], [1,2]) &&
                            !in_array($cDumpDetails -> id, $cAssesData['active_category_data'][ $cDumpDetails -> cat_id ]['acc_data']))
                        {
                            // account found in period
                            $NEW_ASSES_DATA[ $cAssesId ][ $type == 1 ? 'total_deposits' : 'total_advances' ]++;

                            if( $cDumpDetails -> sampling_filter == 1)
                            {
                                $NEW_ASSES_DATA[ $cAssesId ]['active_category_data'][ $cDumpDetails -> cat_id ]['acc_data'][ ] = $cDumpDetails -> id;
                                $NEW_ASSES_DATA[ $cAssesId ][ $type == 1 ? 'total_deposits_sampling' : 'total_advances_sampling' ]++;
                            }
                        }
                    }                    
                }
            }
        }

        return $NEW_ASSES_DATA;
    }
}

// find dump data assesment wise
if(!function_exists('PERFORMANCE_REPORT_FIND_DUMP_DATA_ASSESMENT_WISE')) {

    function PERFORMANCE_REPORT_FIND_DUMP_DATA_ASSESMENT_WISE($thisObj, $extra = [])
    {
        $extra['ASSES_DATA'] = isset($extra['ASSES_DATA']) && !empty($extra['ASSES_DATA']) ? $extra['ASSES_DATA'] : [];
        $ERR_VAR = null;

        if(is_array($extra['ASSES_DATA']) && sizeof($extra['ASSES_DATA']) > 0)
        {
            $dateFilters = [ 'start_date' => '', 'end_date' => '', 'branch_ids' => [] ];

            // generate_query
            foreach($extra['ASSES_DATA'] as $cAssesId => $cAssesData)
            {
                if( empty($dateFilters['start_date']))
                {
                    $dateFilters['start_date'] = $cAssesData['assesment_period_from'];
                    $dateFilters['end_date'] = $cAssesData['assesment_period_to'];
                }

                // push branch ids
                if(!in_array($cAssesData['audit_unit_id'], $dateFilters['branch_ids']))
                    $dateFilters['branch_ids'][] = $cAssesData['audit_unit_id'];

                // add cols
                $extra['ASSES_DATA'][ $cAssesId ]['total_advances'] = 0;
                $extra['ASSES_DATA'][ $cAssesId ]['total_advances_sampling'] = 0;
                $extra['ASSES_DATA'][ $cAssesId ]['total_deposits'] = 0;
                $extra['ASSES_DATA'][ $cAssesId ]['total_deposits_sampling'] = 0;

                // start date
                if( strtotime($cAssesData['assesment_period_from']) < strtotime($dateFilters['start_date']))
                    $dateFilters['start_date'] = $cAssesData['assesment_period_from'];

                // end date
                if( strtotime($cAssesData['assesment_period_to']) > strtotime($dateFilters['end_date']))
                    $dateFilters['end_date'] = $cAssesData['assesment_period_to'];
            }

            // find advances count
            $extra['ASSES_DATA'] = PERFORMANCE_REPORT_SHORT_FUNCTION_GET_DUMP_DATA($thisObj, $extra['ASSES_DATA'], $dateFilters, 2);

            // for deposits count
            $extra['ASSES_DATA'] = PERFORMANCE_REPORT_SHORT_FUNCTION_GET_DUMP_DATA($thisObj, $extra['ASSES_DATA'], $dateFilters);
        }

        return [ 'ASSES_DATA' => $extra['ASSES_DATA'], 'err' => $ERR_VAR ];
    }
}

// find category and questions data
if(!function_exists('PERFORMANCE_REPORT_FIND_CATEGORY_AND_QUESTION_DATA')) {

    function PERFORMANCE_REPORT_FIND_CATEGORY_AND_QUESTION_DATA($thisObj, $extra = [])
    {
        $ASSES_DATA =  isset($extra['ASSES_DATA']) && !empty($extra['ASSES_DATA']) ? $extra['ASSES_DATA'] : [];
        $NEW_ASSES_DATA = []; $ERR_VAR = null;

        if(is_array($ASSES_DATA) && sizeof($ASSES_DATA) > 0)
        {
            // has asses data
            $findCommonData = [ 'category' => [], 'questions' => [] ];

            foreach($ASSES_DATA as $cAssesId => $cAssesDetails)
            {
                // explode data
                $NEW_ASSES_DATA[ $cAssesId ] = (array) $cAssesDetails;
                $NEW_ASSES_DATA[ $cAssesId ]['cat_ids_explode'] = !empty($cAssesDetails -> cat_ids) ? explode(',', $cAssesDetails -> cat_ids) : [];
                $NEW_ASSES_DATA[ $cAssesId ]['question_ids_explode'] = !empty($cAssesDetails -> question_ids) ? explode(',', $cAssesDetails -> question_ids) : [];
                $NEW_ASSES_DATA[ $cAssesId ]['active_category_data'] = [];

                $findCommonData['category'] = array_merge($findCommonData['category'], $NEW_ASSES_DATA[ $cAssesId ]['cat_ids_explode']);
                $findCommonData['questions'] += array_merge($findCommonData['questions'], $NEW_ASSES_DATA[ $cAssesId ]['question_ids_explode']);
            }

            $findCommonData['category'] = array_unique($findCommonData['category']);
            $findCommonData['questions'] = array_unique($findCommonData['questions']);

            if( is_array($findCommonData['category']) && 
                sizeof($findCommonData['category']) > 0 )
            {
                // find category data
                $model = $thisObj -> model('CategoryModel');
                $dbTable = $model -> getTableName();
                $filter = [ 'where' => 'id IN ('. implode(',', $findCommonData['category']) .') AND is_active = 1 AND deleted_at IS NULL', 'params' => [] ];

                $categoryData = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT id, menu_id, name, linked_table_id, question_set_ids FROM " . $dbTable);

                if(is_array($categoryData) && sizeof($categoryData) > 0)
                {
                    foreach($categoryData as $cCatDetails)
                    {
                        foreach($NEW_ASSES_DATA as $cAssesId => $cAssesDetails)
                        {
                            if( is_array($cAssesDetails['cat_ids_explode']) && 
                                in_array($cCatDetails -> id, $cAssesDetails['cat_ids_explode']))
                            {
                                $cCat = $cCatDetails;
                                $cCat -> acc_data = []; 
                                $cCat -> questions_data = []; 
                                $cCat -> answers_data = [];

                                if(!is_array($cCat -> question_set_ids))
                                    $cCat -> question_set_ids = !empty($cCat -> question_set_ids) ? explode(',', $cCat -> question_set_ids) : [];
                                else
                                    $cCat -> question_set_ids = $cCat -> question_set_ids;
                                
                                // has set data then push to category data
                                if(!empty($cCat -> question_set_ids))
                                    $NEW_ASSES_DATA[ $cAssesId ]['active_category_data'][ $cCat -> id ] = (array) $cCat;
                            }
                        }
                    }

                    if( is_array($findCommonData['questions']) && 
                        sizeof($findCommonData['questions']) > 0 )
                    {
                        // questions data
                        $questionData = PERFORMANCE_REPORT_FIND_SET_WISE_QUESTION_DATA($thisObj, [
                            'matrixRisk' => $extra['matrixRisk'],
                            'filter' => [
                                'where' => 'id IN ('. implode(',', $findCommonData['questions']) .') AND is_active = 1 AND deleted_at IS NULL',
                                'params' => []
                            ]
                        ]);

                        // subset question sort
                        if(is_array($questionData) && sizeof($questionData) > 0)
                        {
                            foreach($questionData as $cSetId => $cSetData)
                            {
                                foreach($cSetData as $cQuesId => $cQuesData)
                                {
                                    // for subset
                                    if($cQuesData['option_id'] == 5 && !empty($cQuesData['subset_multi_id']))
                                    {
                                        $cQuesData['subset_multi_id'] = explode(',', $cQuesData['subset_multi_id']);

                                        if(is_array($cQuesData['subset_multi_id']) && sizeof($cQuesData['subset_multi_id']) > 0)
                                        {
                                            foreach($cQuesData['subset_multi_id'] as $cSubsetId)
                                            {
                                                if(array_key_exists($cSubsetId, $questionData))
                                                {
                                                    // has set found
                                                    if(!isset($questionData[ $cSetId ][ $cQuesId ]['subset_data']))
                                                        $questionData[ $cSetId ][ $cQuesId ]['subset_data'] = [];

                                                    // push set data
                                                    $questionData[ $cSetId ][ $cQuesId ]['subset_data'][ $cSubsetId ] = $questionData[ $cSubsetId ];
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            // push questions data to category 
                            foreach($NEW_ASSES_DATA as $cAssesId => $cAssesDetails)
                            {
                                foreach($cAssesDetails['active_category_data'] as $cCatId => $cCatDetails)
                                {
                                    if( is_array($cCatDetails['question_set_ids']) && 
                                        sizeof($cCatDetails['question_set_ids']) > 0)
                                    {
                                        foreach($cCatDetails['question_set_ids'] as $cSetId)
                                        {
                                            if( array_key_exists($cSetId, $questionData) && 
                                                sizeof($questionData[ $cSetId ]) > 0 )
                                            {
                                                $cSetData = $questionData[ $cSetId ];

                                                // questions loop
                                                foreach($cSetData as $cQuesData)
                                                {
                                                    // check period wise questions
                                                    if( is_array($cAssesDetails['question_ids_explode']) && 
                                                        !in_array($cQuesData['id'], $cAssesDetails['question_ids_explode']))
                                                        unset($cSetData[ $cQuesData['id'] ]);
                                                }

                                                // push set data
                                                if( is_array($cSetData) && sizeof($cSetData) > 0 )
                                                    $NEW_ASSES_DATA[ $cAssesId ]['active_category_data'][ $cCatId ]['questions_data'][ $cSetId ] = $cSetData;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        else
                            $ERR_VAR = 'noQuestionFoundError';
                    }
                    else
                        $ERR_VAR = 'noQuestionFoundError';
                }
                else
                    $ERR_VAR = 'categoryNoDataError';

                // unset var
                unset($categoryData);
            }
            else
                $ERR_VAR = 'categoryNoDataError';
        }

        unset($ASSES_DATA);

        return [ 'ASSES_DATA' => $NEW_ASSES_DATA, 'err' => $ERR_VAR ];
    }
}

// generate key
if(!function_exists('PERFORMANCE_REPORT_GENERATE_KEY')) {
    function PERFORMANCE_REPORT_GENERATE_KEY($extra) {
        return $extra['menu_id'] . '_' . $extra['category_id'] . '_' . $extra['dump_id'] . '_' . $extra['question_id']/* . '_' . $extra['asses_id']*/;
    }
}

// find assesment wise answers data
if(!function_exists('PERFORMANCE_REPORT_FIND_ASSESMENT_ANSWERS')) {

    function PERFORMANCE_REPORT_FIND_ASSESMENT_ANSWERS($thisObj, $extra = [])
    {
        $extra['ASSES_DATA'] = isset($extra['ASSES_DATA']) && !empty($extra['ASSES_DATA']) ? $extra['ASSES_DATA'] : [];
        $ERR_VAR = null;

        if(is_array($extra['ASSES_DATA']) && sizeof($extra['ASSES_DATA']) > 0)
        {
            // find answers data
            $model = $thisObj -> model('AnswerDataModel');
            $dbTable = $model -> getTableName();

            // ad.is_compliance = 1 AND removed compliance - 16.11.2024
            $filter = [ 'where' => 'ad.assesment_id IN ('. implode(',', array_keys($extra['ASSES_DATA'])) .') AND ad.deleted_at IS NULL', 'params' => [] ];

            $answersData = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT ad.id, ad.assesment_id, ad.menu_id, ad.category_id, ad.header_id, ad.question_id, ad.dump_id, ad.answer_given, ad.business_risk, ad.control_risk, qm.option_id, qm.annexure_id, qm.risk_category_id FROM " . $dbTable . " ad JOIN question_master qm ON ad.question_id = qm.id");

            if(is_array($answersData) && sizeof($answersData) > 0)
            {
                // answers data
                $temp = [];
                $annxAnsData = [];

                foreach($answersData as $cAnsData)
                {
                    $cAnsData -> risk_score = 0;
                    $cJoinRisk = $cAnsData -> business_risk . '.' . $cAnsData -> control_risk;

                    if( isset($extra['matrixRisk']) && 
                        is_array($extra['matrixRisk']) && 
                        array_key_exists($cJoinRisk, $extra['matrixRisk']))
                        $cAnsData -> risk_score = $extra['matrixRisk'][ $cJoinRisk ];
                        
                    // push data
                    $temp[ $cAnsData -> id ] = (array) $cAnsData;

                    // check for annex 
                    if( $cAnsData -> option_id == 4 && 
                        trim_str($cAnsData -> answer_given) == trim_str($cAnsData -> annexure_id) )
                        $annxAnsData[] = $cAnsData -> id;
                }

                $answersData = $temp;

                if(sizeof($annxAnsData) > 0)
                {
                    // find annex data
                    $model = $thisObj -> model('AnswerDataAnnexureModel');
                    $dbTable = $model -> getTableName();
                    $filter = [ 'where' => 'assesment_id IN ('. implode(',', array_keys($extra['ASSES_DATA'])) .') AND answer_id IN ('. implode(',', $annxAnsData) .')', 'params' => [] ];

                    $annexData = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT id, answer_id, assesment_id, business_risk, control_risk, risk_cat_id FROM " . $dbTable);

                    if(is_array($annexData) && sizeof($annexData) > 0)
                    {
                        foreach($annexData as $cAnnex)
                        {
                            // calculate risk score
                            $cAnnex -> risk_score = 0;
                            $cJoinRisk = $cAnnex -> business_risk . '.' . $cAnnex -> control_risk;

                            if( isset($extra['matrixRisk']) && 
                                is_array($extra['matrixRisk']) && 
                                array_key_exists($cJoinRisk, $extra['matrixRisk']))
                                $cAnnex -> risk_score = $extra['matrixRisk'][ $cJoinRisk ];

                            if(array_key_exists($cAnnex -> answer_id, $answersData))
                            {
                                // answers data found
                                if(!isset($answersData[ $cAnnex -> answer_id ]['annex_data']))
                                {
                                    $answersData[ $cAnnex -> answer_id ]['annex_data'] = [];
                                    $answersData[ $cAnnex -> answer_id ]['annex_cnt'] = 0;
                                }

                                // push data
                                $answersData[ $cAnnex -> answer_id ]['annex_data'][ $cAnnex -> id ] = (array) $cAnnex;
                                $answersData[ $cAnnex -> answer_id ]['annex_cnt']++;
                            }
                        }
                    }
                }

                unset($temp, $annxAnsData); // unset val

                // loop again to push data in category
                foreach($answersData as $cAnsData)
                {   
                    // check category
                    if( isset($extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]) && 
                        is_array($extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data']) && 
                        isset($extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]))
                    {                        
                        // check question
                        if( is_array($extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]['questions_data']))
                        {
                            foreach($extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]['questions_data'] as $cSetId => $cSetArray)
                            {
                                if(isset($cSetArray[ $cAnsData['question_id'] ]))
                                {
                                    // create genkey
                                    $cGenKey = PERFORMANCE_REPORT_GENERATE_KEY([
                                        'menu_id' => $cAnsData['menu_id'],
                                        'category_id' => $cAnsData['category_id'],
                                        'dump_id' => $cAnsData['dump_id'],
                                        'question_id' => $cAnsData['question_id'],
                                        // 'asses_id' => $cAnsData['assesment_id']
                                    ]);

                                    // check account data
                                    if( !empty($cAnsData['dump_id']) && 
                                        in_array($extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]['linked_table_id'], [1,2]) &&
                                        !in_array($cAnsData['dump_id'], $extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]['acc_data']))
                                        $extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]['acc_data'][] = $cAnsData['dump_id'];

                                    // push answers to array
                                    $extra['ASSES_DATA'][ $cAnsData['assesment_id'] ]['active_category_data'][ $cAnsData['category_id'] ]['answers_data'][ $cGenKey ] = $cAnsData;
                                }
                            }
                        }
                    }
                }
            }
            else // no answers data found
                $ERR_VAR = 'ansDataNotFound';
        }

        return [ 'ASSES_DATA' => $extra['ASSES_DATA'], 'err' => $ERR_VAR ];
    }
}

// check key exists or not
if(!function_exists('PERFORMANCE_REPORT_MIX_ASSES_DATA_ADD_KEYS')) {

    function PERFORMANCE_REPORT_MIX_ASSES_DATA_ADD_KEYS($currentArr)
    {
        if(!isset($currentArr['total_questions']))
        {
            $currentArr['total_questions'] = 0;
            $currentArr['total_na_questions'] = 0;
            $currentArr['total_questions_t1'] = 0;
            $currentArr['total_annex_t2'] = 0;
            $currentArr['total_highest_score'] = 0;
            $currentArr['total_obtained_score'] = 0;
            $currentArr['relative_performance'] = 0;
        }

        return $currentArr;
    }
}

if(!function_exists('PERFORMANCE_REPORT_ANNEX_SCORE_CALCULATIONS')) {

    function PERFORMANCE_REPORT_ANNEX_SCORE_CALCULATIONS($cQuesDetails, $cAnsArr, $selectKey, $extra)
    {
        $cDataNotFound = false;

        if( is_array($cAnsArr) && 
            isset($cAnsArr['annex_data']) && 
            is_array($cAnsArr['annex_data']) && 
            sizeof($cAnsArr['annex_data']) > 0)
        {
            $cDataNotFound = true;
            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions_t1']++;

            foreach($cAnsArr['annex_data'] as $cAnnexData)
            {                                    
                if(isset($extra['mix_data'][ $selectKey ][ $cAnnexData['risk_cat_id'] ]))
                {
                    // check keys exists or not // function call
                    $extra['mix_data'][ $selectKey ][ $cAnnexData['risk_cat_id'] ] = PERFORMANCE_REPORT_MIX_ASSES_DATA_ADD_KEYS($extra['mix_data'][ $selectKey ][ $cAnnexData['risk_cat_id'] ]);

                    $extra['mix_data'][ $selectKey ][ $cAnnexData['risk_cat_id'] ]['total_annex_t2']++;

                    // total_highest_score
                    $extra['mix_data'][ $selectKey ][ $cAnnexData['risk_cat_id'] ]['total_highest_score'] += $cAnnexData['risk_score'];
                    $extra['mix_data'][ $selectKey ][ $cAnnexData['risk_cat_id'] ]['total_obtained_score'] += $cAnnexData['risk_score'];
                }
            }
        }

        if($cDataNotFound)
        {
            // total_highest_score
            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_highest_score'] += $cQuesDetails['highest_risk'];
            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_obtained_score'] += 0;
        }

        return $extra['mix_data'];
    }
}

if(!function_exists('PERFORMANCE_REPORT_CALCULATIONS')) {

    function PERFORMANCE_REPORT_CALCULATIONS($extra, $type = 3)
    {    
        // $type = 1 = deposit, 2 = advance, 3 = general
        if(in_array($type, [1,2]))
        {
            // FOR DUMP DATA // re cursive function call
            $extra['mix_data'] = PERFORMANCE_REPORT_CALCULATIONS($extra, 3);
        }

        if($type == 3)
        {
            // we use as common call
            if( isset($extra['cat_data']['questions_data']) && 
                sizeof($extra['cat_data']['questions_data']) > 0 )
            {
                $selectKey = (isset($extra['selectKey']) ? $extra['selectKey'] : 'general');
                $accData = [];

                if(in_array($selectKey, ['advances', 'deposits']))
                {
                    // FOR DUMP
                    if( is_array($extra['cat_data']['acc_data']) && 
                        sizeof($extra['cat_data']['acc_data']) > 0)
                        $accData = $extra['cat_data']['acc_data'];
                }
                else
                    $accData = [0];

                // LOOP DATA
                foreach($accData as $cDumpId)
                {
                    // LOOP ON SET
                    foreach($extra['cat_data']['questions_data'] as $cSetId => $cQuesData)
                    {
                        if(is_array($cQuesData) && sizeof($cQuesData) > 0)
                        {
                            // LOOP ON QUESTION MASTER
                            foreach($cQuesData as $cQuesId => $cQuesDetails)
                            {
                                // check risk category exists or not // skip general question
                                if( $cQuesDetails['option_id'] != 3 &&
                                    isset($extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]))
                                {
                                    // check keys exists or not // function call
                                    $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ] = PERFORMANCE_REPORT_MIX_ASSES_DATA_ADD_KEYS($extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]);

                                    $cGenKey = PERFORMANCE_REPORT_GENERATE_KEY([
                                        'menu_id'       => $extra['cat_data']['menu_id'],
                                        'category_id'   => $extra['cat_data']['id'],
                                        'dump_id'       => $cDumpId,
                                        'question_id'   => $cQuesDetails['id'],
                                        // 'asses_id'      => $AU_ID
                                    ]);

                                    $notApplicable = false;
                                    $cAnsArr = null;

                                    // check ans exists or not
                                    if( isset($extra['cat_data']['answers_data']) && 
                                        is_array($extra['cat_data']['answers_data']) &&
                                        array_key_exists($cGenKey, $extra['cat_data']['answers_data']) )
                                    {
                                        $cAnsArr = $extra['cat_data']['answers_data'][ $cGenKey ];

                                        if( trim_str($cAnsArr['answer_given']) == 'NOT APPLICABLE' || 
                                            trim_str($cAnsArr['answer_given']) == 'select')
                                        {
                                            $notApplicable = true;
                                            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_na_questions']++;        
                                        }
                                    }

                                    // total question add
                                    $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions']++;

                                    if(!$notApplicable)
                                    {
                                        // if(empty($cAnsArr) && ( 
                                        //     ( 
                                        //         $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions_t1'] + $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_na_questions']
                                        //     ) < $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions']  )) // increament count due to answers not found 21.11.2024
                                        //     $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions_t1']++;

                                        // FOR ANNEX
                                        if($cQuesDetails['option_id'] == 4)
                                        {
                                            // function call
                                            $extra['mix_data'] = PERFORMANCE_REPORT_ANNEX_SCORE_CALCULATIONS($cQuesDetails, $cAnsArr, $selectKey, $extra);
                                        }

                                        // FOR SUBSET
                                        else if($cQuesDetails['option_id'] == 5)
                                        {
                                            $cDataNotFound = false;

                                            if( is_array($cQuesDetails['subset_multi_id']) && 
                                                is_array($cAnsArr) && 
                                                in_array($cAnsArr['answer_given'], $cQuesDetails['subset_multi_id']) &&
                                                isset($cQuesDetails['subset_data']) && 
                                                isset($cQuesDetails['subset_data'][ $cAnsArr['answer_given'] ]))
                                            {
                                                // has subset ansers
                                                foreach($cQuesDetails['subset_data'][ $cAnsArr['answer_given'] ] as $cSubsetQues)
                                                {
                                                    // check keys exists or not // function call
                                                    $extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ] = PERFORMANCE_REPORT_MIX_ASSES_DATA_ADD_KEYS($extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ]);

                                                    $cGenKey2 = PERFORMANCE_REPORT_GENERATE_KEY([
                                                        'menu_id'       => $extra['cat_data']['menu_id'],
                                                        'category_id'   => $extra['cat_data']['id'],
                                                        'dump_id'       => $cDumpId,
                                                        'question_id'   => $cSubsetQues['id'],
                                                        // 'asses_id' => $AU_ID
                                                    ]);

                                                    $notApplicable2 = false;
                                                    $cAnsArr2 = null;

                                                    // check ans exists or not
                                                    if( isset($extra['cat_data']['answers_data']) && 
                                                        is_array($extra['cat_data']['answers_data']) &&
                                                        array_key_exists($cGenKey2, $extra['cat_data']['answers_data']) )
                                                    {
                                                        $cAnsArr2 = $extra['cat_data']['answers_data'][ $cGenKey2 ];

                                                        if( $cAnsArr2['answer_given'] == 'NOT APPLICABLE' )
                                                        {
                                                            $notApplicable = true;
                                                            $extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ]['total_na_questions']++;        
                                                        }
                                                    }

                                                    // total question add
                                                    $extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ]['total_questions']++;

                                                    if(!$notApplicable)
                                                    {
                                                        // FOR ANNEX
                                                        if($cSubsetQues['option_id'] == 4)
                                                        {
                                                            // function call
                                                            $extra['mix_data'] = PERFORMANCE_REPORT_ANNEX_SCORE_CALCULATIONS($cSubsetQues, $cAnsArr2, $selectKey, $extra);
                                                        }
                                                        else
                                                        {
                                                            $extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ]['total_questions_t1']++;

                                                            // total_highest_score
                                                            $extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ]['total_highest_score'] += $cSubsetQues['highest_risk'];

                                                            if( is_array($cAnsArr2) )
                                                                $extra['mix_data'][ $selectKey ][ $cSubsetQues['risk_category_id'] ]['total_obtained_score'] += $cAnsArr2['risk_score'];
                                                        }
                                                    }
                                                }
                                            }
                                            else
                                                $cDataNotFound = true;

                                            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions_t1']++;

                                            if($cDataNotFound)
                                            {
                                                // total_highest_score
                                                $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_highest_score'] += $cQuesDetails['highest_risk'];

                                                if( is_array($cAnsArr) )
                                                    $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_obtained_score'] += $cAnsArr['risk_score'];
                                            }
                                        }

                                        // ALL OTHER QUESTION
                                        else
                                        {
                                            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_questions_t1']++;

                                            // total_highest_score
                                            $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_highest_score'] += $cQuesDetails['highest_risk'];

                                            if( is_array($cAnsArr) )
                                                $extra['mix_data'][ $selectKey ][ $cQuesDetails['risk_category_id'] ]['total_obtained_score'] += $cAnsArr['risk_score'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }
        
        return $extra['mix_data'];
    }
}

// mix audit data to branch wise and risk wise
if(!function_exists('PERFORMANCE_REPORT_MIX_ASSES_DATA_TO_AUDIT_UNIT')) {

    function PERFORMANCE_REPORT_MIX_ASSES_DATA_TO_AUDIT_UNIT($thisObj, $extra = [])
    {
        $extra['ASSES_DATA'] = isset($extra['ASSES_DATA']) && !empty($extra['ASSES_DATA']) ? $extra['ASSES_DATA'] : [];
        $MIX_DATA = []; $ERR_VAR = null;
        $HAS_DATA_RISK_CATEGORY = [];
        $ASSES_DATA_COMBINED = [];
        $TOTAL_RISK_SCORES = [];

        if(is_array($extra['ASSES_DATA']) && sizeof($extra['ASSES_DATA']) > 0)
        {
            foreach($extra['ASSES_DATA'] as $cAssesId => $cAssesData)
            {
                $AU_ID = $cAssesData['audit_unit_id'];

                if(!array_key_exists($AU_ID, $MIX_DATA))
                {
                    // find unit data
                    $auditUnitDetails = null;

                    if( is_array($extra['audit_unit_data']) && 
                        array_key_exists($AU_ID, $extra['audit_unit_data']) )
                        $auditUnitDetails = $extra['audit_unit_data'][ $AU_ID ];
                    else if( is_array($extra['ho_audit_unit_data']) && 
                        array_key_exists($AU_ID, $extra['ho_audit_unit_data']) )
                        $auditUnitDetails = $extra['ho_audit_unit_data'][ $AU_ID ];

                    $MIX_DATA[ $AU_ID ] = [
                        'id' => $AU_ID,
                        'no_of_asses' => [ ],
                        'name' => is_object($auditUnitDetails) ? $auditUnitDetails -> name : ERROR_VARS['notFound'], 
                        'audit_unit_code' => is_object($auditUnitDetails) ? $auditUnitDetails -> audit_unit_code : ERROR_VARS['notFound'],
                        'combined_name' => is_object($auditUnitDetails) ? $auditUnitDetails -> combined_name : ERROR_VARS['notFound'],
                        'total_advances' => 0,
                        'total_advances_sampling' => 0,
                        'total_deposits' => 0,
                        'total_deposits_sampling' => 0,
                        'total_highest_score' => 0,
                        'total_highest_score_weighted' => 0,
                        'total_obtained_score' => 0,
                        'total_obtained_score_weighted' => 0
                    ];

                    $riskCatData = [];

                    foreach($extra['riskCategory'] as $cRiskData)
                    {
                        $riskCatData[ $cRiskData -> id ] = [
                            'id' => $cRiskData -> id,
                            'risk_category' => $cRiskData -> risk_category,
                            'risk_weightage' => $cRiskData -> risk_weightage
                        ];
                    }

                    // upper case convert
                    $MIX_DATA[ $AU_ID ]['name'] = string_operations($MIX_DATA[ $AU_ID ]['name'], 'upper');
                    $MIX_DATA[ $AU_ID ]['audit_unit_code'] = string_operations($MIX_DATA[ $AU_ID ]['audit_unit_code'], 'upper');
                    $MIX_DATA[ $AU_ID ]['combined_name'] = string_operations($MIX_DATA[ $AU_ID ]['combined_name'], 'upper');
                    $ASSES_DATA_COMBINED[ $AU_ID ] = $MIX_DATA[ $AU_ID ];

                    $MIX_DATA[ $AU_ID ]['general'] = $riskCatData;
                    $MIX_DATA[ $AU_ID ]['advances'] = $riskCatData;
                    $MIX_DATA[ $AU_ID ]['deposits'] = $riskCatData;
                    $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'] = $riskCatData;

                    // unset val
                    unset($ASSES_DATA_COMBINED[ $AU_ID ]['general'], $ASSES_DATA_COMBINED[ $AU_ID ]['advances'], $ASSES_DATA_COMBINED[ $AU_ID ]['deposits']);
                }

                // addition accounts
                $MIX_DATA[ $AU_ID ]['no_of_asses'][ $cAssesData['id'] ] = $cAssesData['combined_period'];
                $MIX_DATA[ $AU_ID ]['total_advances'] += $cAssesData['total_advances'];
                $MIX_DATA[ $AU_ID ]['total_advances_sampling'] += $cAssesData['total_advances_sampling'];
                $MIX_DATA[ $AU_ID ]['total_deposits'] += $cAssesData['total_deposits'];
                $MIX_DATA[ $AU_ID ]['total_deposits_sampling'] += $cAssesData['total_deposits_sampling'];

                $ASSES_DATA_COMBINED[ $AU_ID ]['no_of_asses'][ $cAssesData['id'] ] = $cAssesData['combined_period'];
                $ASSES_DATA_COMBINED[ $AU_ID ]['total_advances'] += $cAssesData['total_advances'];
                $ASSES_DATA_COMBINED[ $AU_ID ]['total_advances_sampling'] += $cAssesData['total_advances_sampling'];
                $ASSES_DATA_COMBINED[ $AU_ID ]['total_deposits'] += $cAssesData['total_deposits'];
                $ASSES_DATA_COMBINED[ $AU_ID ]['total_deposits_sampling'] += $cAssesData['total_deposits_sampling'];

                // make calculations
                if( isset($cAssesData['active_category_data']) && 
                    sizeof($cAssesData['active_category_data']) > 0 )
                {
                    foreach($cAssesData['active_category_data'] as $cCatDetails)
                    {
                        if(!in_array($cCatDetails['linked_table_id'], [1,2]))
                        {
                            // general questions
                            $MIX_DATA[ $AU_ID ] = PERFORMANCE_REPORT_CALCULATIONS([
                                'cat_data' => $cCatDetails,
                                'mix_data' => $MIX_DATA[ $AU_ID ]
                            ]);                            
                        }
                        else
                        {
                            // dump questions
                            $dumpCat = $cCatDetails['linked_table_id'] == 1 ? 'deposits' : 'advances';
                            
                            $MIX_DATA[ $AU_ID ] = PERFORMANCE_REPORT_CALCULATIONS([
                                'cat_data' => $cCatDetails,
                                'mix_data' => $MIX_DATA[ $AU_ID ],
                                'selectKey' => $dumpCat
                            ]);
                        }
                    }
                }

                // get active risk category
                foreach(['general', 'advances', 'deposits'] as $cGenKey)
                {
                    if( is_array($MIX_DATA[ $AU_ID ][ $cGenKey ]) && 
                        sizeof($MIX_DATA[ $AU_ID ][ $cGenKey ]) > 0)
                    {
                        foreach($MIX_DATA[ $AU_ID ][ $cGenKey ] as $cRiskData)
                        {
                            if(isset($cRiskData['total_questions']) && 
                              !isset($MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_highest_score_weighted']))
                            {
                                $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_highest_score_weighted'] = 0;
                                $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_obtained_score_weighted'] = 0;
                                $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['relative_performance'] = 0;
                            }

                            if(isset($cRiskData['total_questions']))
                            {
                                // total NA question count not match adding patch work 21.11.2024
                                if($cRiskData['id'] == 10)
                                {
                                    $cAddNAT1 = (  $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_na_questions'] + 
                                                   $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_questions_t1'] );

                                    if( $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_questions'] > 0 &&
                                        $cAddNAT1 != $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_questions'] )
                                    {
                                        $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_questions_t1'] += ($MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_questions'] - $cAddNAT1);
                                    }
                                }

                                if(!in_array($cRiskData['id'], $HAS_DATA_RISK_CATEGORY))
                                    $HAS_DATA_RISK_CATEGORY[ $cRiskData['id'] ] = [
                                        'id' => $cRiskData['id'],
                                        'risk_category' => string_operations($cRiskData['risk_category'], 'upper'),
                                        'risk_weightage' => $cRiskData['risk_weightage']
                                    ];
                                    
                                // calculations
                                if($cRiskData['risk_weightage'] > 0)
                                {
                                    $cTotHighestScore = get_decimal(($cRiskData['risk_weightage'] * $cRiskData['total_highest_score']), 2);
                                    $cTotObtainedScore = get_decimal(($cRiskData['risk_weightage'] * $cRiskData['total_obtained_score']), 2);

                                    $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_highest_score_weighted'] = $cTotHighestScore;
                                    $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_obtained_score_weighted'] = $cTotObtainedScore;
                                    $MIX_DATA[ $AU_ID ]['total_highest_score'] += get_decimal($cRiskData['total_highest_score'], 2);
                                    $MIX_DATA[ $AU_ID ]['total_highest_score_weighted'] += $cTotHighestScore;
                                    $MIX_DATA[ $AU_ID ]['total_obtained_score'] += get_decimal($cRiskData['total_obtained_score'], 2);
                                    $MIX_DATA[ $AU_ID ]['total_obtained_score_weighted'] += $cTotObtainedScore;

                                    // calculate relative performance
                                    if( $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_obtained_score_weighted'] > 0 && 
                                        $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_highest_score_weighted'] > 0 )
                                    {
                                        $cRelPerformance = $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_obtained_score_weighted'] / $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['total_highest_score_weighted'];
                                        $cRelPerformance = get_decimal(($cRelPerformance * 100), 2);
                                        $MIX_DATA[ $AU_ID ][ $cGenKey ][ $cRiskData['id'] ]['relative_performance'] = $cRelPerformance;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // loop on mix data
            foreach($MIX_DATA as $AU_ID => $cAUData)
            {
                foreach(['general', 'advances', 'deposits'] as $cGenKey)
                {
                    foreach($cAUData[ $cGenKey ] as $cRiskData)
                    {
                        if(isset($cRiskData['total_questions']))
                        {
                            // check keys exits or not
                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ] = PERFORMANCE_REPORT_MIX_ASSES_DATA_ADD_KEYS($ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]);

                            // add keys in total
                            if(!isset($TOTAL_RISK_SCORES[ $cRiskData['id'] ]))
                            {
                                $TOTAL_RISK_SCORES[ $cRiskData['id'] ] = [
                                    'id' => $cRiskData['id'],
                                    'risk_category' => string_operations($cRiskData['risk_category'], 'upper'),
                                    'risk_weightage' => $cRiskData['risk_weightage'],
                                    'total_highest_score' => 0,
                                    'total_highest_score_weighted' => 0,
                                    'total_obtained_score' => 0,
                                    'total_obtained_score_weighted' => 0
                                ];
                            }

                            if(!isset($ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted']))
                            {
                                $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted'] = 0;
                                $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted'] = 0;
                                $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['relative_performance'] = 0;
                            }

                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_questions'] += $cRiskData['total_questions'];
                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_na_questions'] += $cRiskData['total_na_questions'];
                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_questions_t1'] += $cRiskData['total_questions_t1'];
                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_annex_t2'] += $cRiskData['total_annex_t2'];
                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_highest_score'] += $cRiskData['total_highest_score'];
                            $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_obtained_score'] += $cRiskData['total_obtained_score'];

                            $ASSES_DATA_COMBINED[ $AU_ID ]['total_highest_score'] += $cRiskData['total_highest_score'];
                            $ASSES_DATA_COMBINED[ $AU_ID ]['total_obtained_score'] += $cRiskData['total_obtained_score'];

                            // calculations
                            if($cRiskData['risk_weightage'] > 0)
                            {
                                $cTotHighestScore = get_decimal(($cRiskData['risk_weightage'] * $cRiskData['total_highest_score']), 2);
                                $cTotObtainedScore = get_decimal(($cRiskData['risk_weightage'] * $cRiskData['total_obtained_score']), 2);

                                $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted'] += $cTotHighestScore;
                                $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted'] += $cTotObtainedScore;
                                $ASSES_DATA_COMBINED[ $AU_ID ]['total_highest_score_weighted'] += $cTotHighestScore;
                                $ASSES_DATA_COMBINED[ $AU_ID ]['total_obtained_score_weighted'] += $cTotObtainedScore;

                                // calculate relative performance
                                if( $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted'] > 0 && 
                                    $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted'] > 0 )
                                {
                                    $cRelPerformance = $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_obtained_score_weighted'] / $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['total_highest_score_weighted'];
                                    $cRelPerformance = get_decimal(($cRelPerformance * 100), 2);
                                    $ASSES_DATA_COMBINED[ $AU_ID ]['risk_category'][ $cRiskData['id'] ]['relative_performance'] = $cRelPerformance;
                                }

                                // total calculations
                                $TOTAL_RISK_SCORES[ $cRiskData['id'] ]['total_highest_score'] += get_decimal($cRiskData['total_highest_score'], 2);
                                $TOTAL_RISK_SCORES[ $cRiskData['id'] ]['total_highest_score_weighted'] += $cTotHighestScore;
                                $TOTAL_RISK_SCORES[ $cRiskData['id'] ]['total_obtained_score'] += get_decimal($cRiskData['total_obtained_score'], 2);
                                $TOTAL_RISK_SCORES[ $cRiskData['id'] ]['total_obtained_score_weighted'] += $cTotObtainedScore;
                            }
                        }
                    }
                }
            }
        }

        if(empty($HAS_DATA_RISK_CATEGORY))
            $ERR_VAR = 'noDataFound';

        if(isset($extra['combined']))
            $MIX_DATA = $ASSES_DATA_COMBINED;

        return [ 'mix_data' => $MIX_DATA, 'has_data' => $HAS_DATA_RISK_CATEGORY, 'err' => $ERR_VAR, 'total_risk_scores' => $TOTAL_RISK_SCORES ];
    }
}

?>