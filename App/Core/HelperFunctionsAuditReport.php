<?php
if(!function_exists('is_ro_user')) {
    function is_ro_user() {
        // Check if session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check for emp_type (value 16 = RO) or user_type_id
        if(isset($_SESSION['emp_type']) && $_SESSION['emp_type'] == 16) {
            return true;
        }
        
        // Fallback to user_type_id if exists
        if(isset($_SESSION['user_type_id']) && $_SESSION['user_type_id'] == 16) {
            return true;
        }
        
        return false;
    }
}

if(!function_exists('get_user_type')) {
    function get_user_type() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Return emp_type if exists, otherwise user_type_id
        if(isset($_SESSION['emp_type'])) {
            return $_SESSION['emp_type'];
        }
        
        return isset($_SESSION['user_type_id']) ? $_SESSION['user_type_id'] : null;
    }
}

if(!function_exists('get_emp_details')) {
    function get_emp_details() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['emp_details']) ? $_SESSION['emp_details'] : null;
    }
}
?>
<?php

if(!function_exists('find_audit_observations_common_data'))
{
    //find common data
    function find_audit_observations_common_data($this_obj, $assessmentData) {

        $dataArray = array('active_menu' => null, 'active_category' => null, 'active_header' => null);

        // get all menu
        $model = $this_obj -> model('MenuModel');

        $checkCF = false;

        if(is_object($assessmentData) && !empty($assessmentData))
        {
            // function call
            $sortedMenu = check_menu_data_has_carry_forward($assessmentData -> menu_ids);

            if(sizeof($sortedMenu['menuIds']) > 0)
            {
                $dataArray['active_menu'] = $model -> getAllMenu([
                    'where' => 'id IN ('. implode(',', $sortedMenu['menuIds']) .') AND is_active = 1 AND deleted_at IS NULL',
                    'params' => []
                ]);
            }

            $checkCF = $sortedMenu['checkCF'];
        }
        else
        {
            if(isset($assessmentData) && is_array($assessmentData))
            {
                $reportAuditAssesmentData = [];

                    // $reportAuditAssesment = array_keys($assessmentData);

                    for($i = 0; $i < sizeof($assessmentData); $i++ )
                    {
                        $tempData = !empty($assessmentData[$i] -> menu_ids) ? explode(',', $assessmentData[$i] -> menu_ids) : [];

                        $reportAuditAssesmentData = array_merge($reportAuditAssesmentData, $tempData);                
                    }

                    $menuData = implode(',', array_unique($reportAuditAssesmentData));
            }

            // function call
            $sortedMenu = check_menu_data_has_carry_forward($menuData);

            $dataArray['active_menu'] = $model -> getAllMenu([
                'where' => 'id IN ('. $sortedMenu['menuIds'] .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);
            
            $checkCF = $sortedMenu['checkCF'];
        }

        // unset vars
        unset($sortedMenu);

        if($checkCF)
        {
            // ADD CARRY FORWARD MENU // MANUALLY ADD

            if( !is_array($dataArray['active_menu']) )
                $dataArray['active_menu'] = [];

            $dataArray['active_menu'][ CARRY_FORWARD_ARRAY['id'] ] = (object)[
                "id" => CARRY_FORWARD_ARRAY['id'],
                "section_type_id" => 1,
                "name" => CARRY_FORWARD_ARRAY['title'],
                "linked_table_id" => 0,
                "is_active" => 1
            ];
        }

        // helper function call
        $dataArray['active_menu'] = generate_data_assoc_array($dataArray['active_menu'], 'id');

        $model = $this_obj -> model('CategoryModel');

        if(is_object($assessmentData) && !empty($assessmentData))
        {
            $dataArray['active_category'] = $model -> getAllCategory([
                'where' => 'id IN ('. $assessmentData -> cat_ids .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);
        }
        else
        {
            if(isset($assessmentData) && is_array($assessmentData))
            {

                $reportAuditAssesmentData = [];

                    // $reportAuditAssesment = array_keys($assessmentData);

                    for($i = 0; $i < sizeof($assessmentData); $i++ )
                    {
                        $tempData = !empty($assessmentData[$i] -> cat_ids) ? explode(',', $assessmentData[$i] -> cat_ids) : [];

                        $reportAuditAssesmentData = array_merge($reportAuditAssesmentData, $tempData);                
                    }

                    $catData = implode(',', array_unique($reportAuditAssesmentData));
            }

            $dataArray['active_category'] = $model -> getAllCategory([
                'where' => 'id IN ('. $catData .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);            
        }

        // helper function call
        $dataArray['active_category'] = generate_data_assoc_array($dataArray['active_category'], 'id');

        $model = $this_obj -> model('QuestionHeaderModel');
        
        if(is_object($assessmentData) && !empty($assessmentData))
        {
            $dataArray['active_header'] = $model -> getAllQuestionHeader([
                'where' => 'id IN ('. $assessmentData -> header_ids .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);
        }
        else
        {
            if(isset($assessmentData) && is_array($assessmentData))
            {

                $reportAuditAssesmentData = [];

                    // $reportAuditAssesment = array_keys($assessmentData);

                    for($i = 0; $i < sizeof($assessmentData); $i++ )
                    {
                        $tempData = !empty($assessmentData[$i] -> header_ids) ? explode(',', $assessmentData[$i] -> header_ids) : [];

                        $reportAuditAssesmentData = array_merge($reportAuditAssesmentData, $tempData);                
                    }

                    $headerData = implode(',', array_unique($reportAuditAssesmentData));
            }

            $dataArray['active_header'] = $model -> getAllQuestionHeader([ 
                'where' => 'id IN ('. $headerData .') AND is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ]);            
        }

        // helper function call
        $dataArray['active_header'] = generate_data_assoc_array($dataArray['active_header'], 'id');

        return $dataArray;
    }
}

if(!function_exists('get_audit_question_type'))
{
    //function for check question type
    function get_audit_question_type($activeCategoryArray, $row)
    {
        $accQuestion = '';

        // CARRY FORWARD ANSWER
        if(check_carry_forward_strict() && $row -> answer_given == CARRY_FORWARD_ARRAY['id'])
            return $accQuestion;

        // check question type general / account
        if( is_array($activeCategoryArray) && 
            $activeCategoryArray[ $row -> category_id ] -> linked_table_id != '0' &&
            array_key_exists($activeCategoryArray[ $row -> category_id ] -> linked_table_id, $GLOBALS['schemeTypesArray']) && 
            $row -> dump_id != 0 )
        {
            $accQuestion =  ($activeCategoryArray[ $row -> category_id ] -> linked_table_id == '1') ? 'dump_deposits' : 'dump_advances';
        }

        return $accQuestion;
    }
}

if(!function_exists("generate_account_markup_for_report"))
{
    function generate_account_markup_for_report($data, $assesmentData, $dumpDetails, $extra = [])
    {
        $returnMarkup = '';

        if(!is_object($assesmentData) || !is_object($dumpDetails))
            return $returnMarkup;

        if(isset($extra['needTable']))
        {
            $returnMarkup .= '<div class="table-responsive">' . "\n";
            $returnMarkup .= '<table class="table table-bordered mb-0">' . "\n";
        }

        $acClass = isset($extra['needAcClass']) ? 'acc-tr acc-'. $dumpDetails -> id : '';

            $returnMarkup .= '<tr class="bg-light '. $acClass .'">' . "\n";
                $returnMarkup .= '<td class="font-medium">Branch Details</td>' . "\n";

                if( is_object($assesmentData -> audit_unit_id_details) ):
                    $returnMarkup .= '<td colspan="2">'. string_operations(($assesmentData -> audit_unit_id_details -> name . ' ( BR. Code: ' . $assesmentData -> audit_unit_id_details -> audit_unit_code . ' )'), 'upper') .'</td>' . "\n";
                else:
                    $returnMarkup .= '<td colspan="2">'. string_operations(ERROR_VARS['notFound'], 'upper') .'</td>' . "\n";
                endif;

                $returnMarkup .= '<td class="font-medium">A/C Number</td>' . "\n";
                $returnMarkup .= '<td colspan="2">'. string_operations($dumpDetails -> account_no, 'upper') .'</td>' . "\n";

            $returnMarkup .= '</tr>' . "\n";

            $returnMarkup .= '<tr class="'. $acClass .'">' . "\n";

                $returnMarkup .= '<td class="font-medium" width="140">Scheme Details</td>' . "\n";
                $returnMarkup .= '<td colspan="2">'. string_operations(($dumpDetails -> scheme_id_name  . ' ( Scheme Code: ' . $dumpDetails -> scheme_id_code . ' )'), 'upper') .'</td>' . "\n";

                $returnMarkup .= '<td class="font-medium" width="140">A/C Open Date</td>' . "\n";
                $returnMarkup .= '<td colspan="2">'. $dumpDetails -> account_opening_date .'</td>' . "\n";

            $returnMarkup .= '</tr>' . "\n";

            $returnMarkup .= '<tr class="bg-light '. $acClass .'">' . "\n";
                $returnMarkup .= '<td class="text-primary font-medium">A/C Holder Name</td>' . "\n";
                $returnMarkup .= '<td class="text-primary font-medium" colspan="5">'. string_operations($dumpDetails -> account_holder_name, 'upper') .'</td>' . "\n";
            $returnMarkup .= '</tr>' . "\n";

                $returnMarkup .= '<tr class="'. $acClass .'">' . "\n";

                    $returnMarkup .= '<td class="font-medium">UCIC</td>' . "\n";
                    $returnMarkup .= '<td>'. string_operations($dumpDetails -> ucic, 'upper') .'</td>' . "\n";
                
                    if(!isset($extra['hideData']))
                    {
                        $returnMarkup .= '<td class="font-medium">Customer Type</td>' . "\n";
                        $returnMarkup .= '<td>'. string_operations( ($dumpDetails -> customer_type ?? ERROR_VARS['notFound']), 'upper' ) .'</td>' . "\n";
                    
                        $returnMarkup .= '<td class="font-medium" width="140">Interest Rate</td>' . "\n";
                        $returnMarkup .= '<td>'. get_decimal($dumpDetails -> intrest_rate, 2) .'</td>' . "\n";

                    // close tag not required due to add action button in other componenet
                    $returnMarkup .= '</tr>' . "\n";
                }

            if(!isset($extra['hideData']))
            {
                $returnMarkup .= '<tr class="'. $acClass .'">' . "\n";

                    if(isset($dumpDetails -> principal_amount)):
                        $returnMarkup .= '<td class="font-medium">Principal Amount</td>' . "\n";
                        $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> principal_amount, 2) .'<td>' . "\n";
                    elseif(isset($dumpDetails -> sanction_amount)):
                        $returnMarkup .= '<td class="font-medium">Sanction Amount</td>' . "\n";
                        $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> sanction_amount, 2) .'<td>' . "\n";
                    endif;

                    if(isset($dumpDetails -> balance)):
                        $returnMarkup .= '<td class="font-medium">Balance</td>' . "\n";
                        $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> balance, 2) .'</td>' . "\n";
                    elseif(isset($dumpDetails -> outstanding_balance)):
                        $returnMarkup .= '<td class="font-medium">Outstanding Amount</td>' . "\n";
                        $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> outstanding_balance, 2) .'</td>' . "\n";
                    endif;

                    if(isset($dumpDetails -> balance_date)):
                        $returnMarkup .= '<td class="font-medium">Balance Date</td>' . "\n";
                        $returnMarkup .= '<td width="100">'. (!empty($dumpDetails -> balance_date) ? $dumpDetails -> balance_date : '-') .'</td>' . "\n";
                    elseif(isset($dumpDetails -> due_date)):
                        $returnMarkup .= '<td class="font-medium">Account Status</td>' . "\n";
                        $returnMarkup .= '<td>'. (!empty($dumpDetails -> account_status) ? $dumpDetails -> account_status : '-') .'</td>' . "\n";
                    endif;
                    
                $returnMarkup .= '</tr>' . "\n";

                $returnMarkup .= '<tr class="'. $acClass .'">' . "\n";

                    if(isset($dumpDetails -> maturity_amount)):
                        $returnMarkup .= '<td class="font-medium">Maturity Amount</td>' . "\n";
                        $returnMarkup .= '<td>'. get_decimal($dumpDetails -> maturity_amount, 2) .'</td>' . "\n";
                    elseif(isset($dumpDetails -> due_date)):
                        $returnMarkup .= '<td class="font-medium">Due Date</td>' . "\n";
                        $returnMarkup .= '<td>'. (!empty($dumpDetails -> due_date) ? $dumpDetails -> due_date : '-') .'</td>' . "\n";
                    endif;

                    if(isset($dumpDetails -> maturity_date)):
                        $returnMarkup .= '<td class="font-medium">Maturity Date</td>' . "\n";
                        $returnMarkup .= '<td>'. (!empty($dumpDetails -> maturity_date) ? $dumpDetails -> maturity_date : '-')  .'<td>' . "\n";
                    elseif(isset($dumpDetails -> balance_date)):
                        $returnMarkup .= '<td class="font-medium">Balance Date</td>' . "\n";
                        $returnMarkup .= '<td>'. (!empty($dumpDetails -> balance_date) ? $dumpDetails -> balance_date : '-') .'</td>' . "\n";
                    endif;

                    if(isset($dumpDetails -> account_status)):
                        $returnMarkup .= '<td class="font-medium">Account Status</td>' . "\n";
                        $returnMarkup .= '<td>'. (!empty($dumpDetails -> account_status) ? string_operations($dumpDetails -> account_status, 'upper') : '-') .'</td>' . "\n";
                    elseif(isset($dumpDetails -> npa_status)):
                        $returnMarkup .= '<td class="font-medium">NPA Status</td>' . "\n";
                        $returnMarkup .= '<td>'. (!empty($dumpDetails -> npa_status) ? string_operations($dumpDetails -> npa_status, 'upper') : '-') .'</td>' . "\n";
                    endif;
                    
                $returnMarkup .= '</tr>' . "\n";
            }

        if(isset($extra['needTable']))
        {
            $returnMarkup .= '</table>' . "\n";
            $returnMarkup .= '</div>' . "\n";
        }

        return $returnMarkup;
    }
}

if(!function_exists("find_question_in_question_data"))
{
    function find_question_in_question_data($quesId, $questionData, $needQuestion = false)
    {
        $question = null;

        if(!empty($quesId) && is_array($questionData) && sizeof($questionData) > 0)
        {
            // check id exists or not
            if(array_key_exists($quesId, $questionData))
                $question = $questionData[ $quesId ];

            else
            {
                // if question key not exists then check for subset questions
                foreach( $questionData as $cChkQuesId => $cChkQuesDetails )
                {
                    if( isset($cChkQuesDetails -> subset_data) && 
                        is_array($cChkQuesDetails -> subset_data) && 
                        sizeof($cChkQuesDetails -> subset_data) > 0 )
                    {
                        // loop on set data
                        foreach( $cChkQuesDetails -> subset_data as $cChkSetId => $cChkSetDetails )
                        {
                            if( isset($cChkSetDetails -> headers) && 
                                is_array($cChkSetDetails -> headers) && 
                                sizeof($cChkSetDetails -> headers) > 0 )
                            {
                                // loop on header data
                                foreach( $cChkSetDetails -> headers as $cChkHeaderId => $cChkHeaderDetails )
                                {
                                    if( isset($cChkHeaderDetails -> questions) && 
                                        is_array($cChkHeaderDetails -> questions) && 
                                        array_key_exists($quesId, $cChkHeaderDetails -> questions) > 0 )
                                    {
                                        // question found in subset
                                        $question = $cChkHeaderDetails -> questions[ $quesId ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if($needQuestion)
            return $question;

        return is_object($question) ? 1 : 0;
    }
}

if(!function_exists('get_audit_sorted_questions'))
{
    //function for sort questions
    function get_audit_sorted_questions($this_obj, $findObservation, $dataArray, $annexureData, $commonDataArray)
    {
        // function call
        $checkCF = check_carry_forward_strict();

        foreach($findObservation as $cAnsId => $cAnsRow)
        {
            $accQuestion = get_audit_question_type( $commonDataArray['active_category'], $cAnsRow );
            $isAnnexFound = true;
            $isCFAnswer = false;

            // check annexure question 
            if( ($cAnsRow -> option_id == 4 && $cAnsRow -> annexure_id > 0) || 
                ($checkCF && $cAnsRow -> answer_given == CARRY_FORWARD_ARRAY['id']) )
            {
                $cGenKey = $cAnsRow -> id;

                if( is_array($annexureData) && sizeof($annexureData) > 0 && array_key_exists($cGenKey, $annexureData))
                {
                    // minus 1 due to annex found
                    $dataArray['is_compliance_cnt']--;
                    $dataArray['is_compliance_cnt'] += sizeof($annexureData[ $cGenKey ]);
                    
                    // annexure found
                    $cAnsRow -> annex = $annexureData[ $cGenKey ];

                    // add ids for future use
                    $dataArray[ 'ans_annex_ids' ] = array_unique( array_merge($dataArray[ 'ans_annex_ids' ], array_keys($annexureData[ $cGenKey ]) ));

                    if( !empty($cAnsRow -> annexure_id) && !in_array($cAnsRow -> annexure_id, $dataArray['annex_tab_ids']) )
                        $dataArray['annex_tab_ids'][] = $cAnsRow -> annexure_id;
                }            
                else
                {
                    // other annexure ans given
                    // $isAnnexFound = false;
                    $dataArray['is_compliance_cnt']++;
                }
            }
            else
                $dataArray['is_compliance_cnt']++;

            // if(in_array($filterType, [4, 5]) && $cAnsRow -> option_id == 4 /*&& $cAnsRow['reviewer_accept_reject_compliance'] != '2'*/)
                // $isAnnexFound = false;

            // filter condition added 30.10.2024
            if(isset($commonDataArray['sortFilterApplied']) && $cAnsRow -> option_id == 4 && 
            ( /*!isset($cAnsRow -> annex) ||*/ (isset($cAnsRow -> annex) && !(sizeof($cAnsRow -> annex) > 0)) ) )
                $isAnnexFound = false;            

            //all data is ok //process other data
            if($isAnnexFound)
            {
                // push question ids
                if(!in_array($cAnsRow -> id , $dataArray[ 'ans_ids' ]))
                    $dataArray[ 'ans_ids' ][] = $cAnsRow -> id;

                // CHECK FOR CARRY FORWARD
                if($checkCF && $cAnsRow -> answer_given == CARRY_FORWARD_ARRAY['id'])
                {
                    $isCFAnswer = true;

                    if(isset($commonDataArray['active_menu'][ CARRY_FORWARD_ARRAY['id'] ]))
                    {
                        if(!array_key_exists(CARRY_FORWARD_ARRAY['id'], $dataArray['ans_data']))
                        {
                            $dataArray['ans_data'][ CARRY_FORWARD_ARRAY['id'] ] = array(
                                'menu' => $commonDataArray['active_menu'][ CARRY_FORWARD_ARRAY['id'] ], 
                                'ans_data' => []
                            );
                        }

                        // push cf answers to array
                        $dataArray['ans_data'][ CARRY_FORWARD_ARRAY['id'] ]['ans_data'] = $cAnsRow;
                    }
                }
                else
                {
                    // push MENU ID
                    if( !array_key_exists($cAnsRow -> menu_id, $dataArray['ans_data']) )
                    {
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ] = array(
                            'menu' => null, 'category' => []
                        );

                        // push menu details
                        if( is_array($commonDataArray['active_menu']) && array_key_exists($cAnsRow -> menu_id, $commonDataArray['active_menu']) ) 
                            $dataArray['ans_data'][ $cAnsRow -> menu_id ]['menu'] = $commonDataArray['active_menu'][ $cAnsRow -> menu_id ];
                    }
                }

                // push CATEGORY // skip cf category
                if( !$isCFAnswer &&
                    !array_key_exists($cAnsRow -> category_id, $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category']))
                {
                    $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] = null;

                    if( is_array($commonDataArray['active_category']) && 
                        array_key_exists($cAnsRow -> category_id, $commonDataArray['active_category']) ) 
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] = $commonDataArray['active_category'][ $cAnsRow -> category_id ];

                    //check question type general / account
                    if( $accQuestion != '' )
                    {
                        //loan or deposites question
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump = array();
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump_table = $accQuestion;
                    }
                    else //other category question
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> questions = [];
                }

                // push HEADER 
                if(!$isCFAnswer) // skip CF answers
                {
                    if($accQuestion != '') 
                    {
                        // push dump id for get data after on load
                        if( !in_array($cAnsRow -> dump_id, $dataArray[ $accQuestion ]) )
                            $dataArray[ $accQuestion ][] = $cAnsRow -> dump_id;

                        // push dump id in main array
                        if( !array_key_exists($cAnsRow -> dump_id, $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump) )
                            $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump[ $cAnsRow -> dump_id ] = [];
                        
                        // account wise question
                        if( !array_key_exists($cAnsRow -> header_id, $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump[ $cAnsRow -> dump_id ]))
                        {
                            $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump[ $cAnsRow -> dump_id ][ $cAnsRow -> header_id ] = array(
                                'header' => null, 'questions' => []
                            );

                            if( is_array($commonDataArray['active_header']) && 
                                array_key_exists($cAnsRow -> header_id, $commonDataArray['active_header']) ) 
                                $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump[ $cAnsRow -> dump_id ][ $cAnsRow -> header_id ]['header'] = $commonDataArray['active_header'][ $cAnsRow -> header_id ];
                        }

                        // push questions
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> dump[ $cAnsRow -> dump_id ][ $cAnsRow -> header_id ]['questions'][ $cAnsRow -> id ] = $cAnsRow;
                    }
                    else
                    {
                        if( !array_key_exists($cAnsRow -> header_id, $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> questions))
                        {
                            $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> questions[ $cAnsRow -> header_id ] = array(
                                'header' => null, 'questions' => []
                            );

                            if( is_array($commonDataArray['active_header']) && array_key_exists($cAnsRow -> header_id, $commonDataArray['active_header']) )
                                $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> questions[ $cAnsRow -> header_id ]['header'] = $commonDataArray['active_header'][ $cAnsRow -> header_id ];
                        }

                        //push questions
                        $dataArray['ans_data'][ $cAnsRow -> menu_id ]['category'][ $cAnsRow -> category_id ] -> questions[ $cAnsRow -> header_id ]['questions'][ $cAnsRow -> id ] = $cAnsRow;
                    }
                }

            }
        }

        return $dataArray;
    }
}

if(!function_exists('find_mixed_dump_data'))
{
    function find_mixed_dump_data($this_obj, $assessmentData, $whereArray = [], $type = 2) {
        
        //find dump_advances accounts
        $model = $this_obj -> model(($type == 2 ? 'DumpAdvancesModel' : 'DumpDepositeModel'));
        $table = $model -> getTableName();

        // JOIN QUERY DATA
        $select = "SELECT dt.*, 
                        COALESCE(sm.scheme_code, 'NA') AS scheme_code, 
                        COALESCE(sm.name, 'NA') AS scheme_name, 
                        COALESCE(aum.name, 'NA') AS audit_unit_name, 
                        COALESCE(aum.audit_unit_code, 'NA') AS audit_unit_code
                    FROM ". $table ." dt LEFT JOIN 
                    scheme_master sm ON dt.scheme_id = sm.id LEFT JOIN 
                    audit_unit_master aum ON dt.branch_id = aum.id";

        $dumpData = get_all_data_query_builder(2, $model, $table, $whereArray, 'sql', $select);
        $dumpData = generate_data_assoc_array($dumpData, 'id');

        return $dumpData;
    }
}

if(!function_exists('find_audit_observations'))
{
    // function for find observations
    function find_audit_observations($this_obj, $assessmentData, $FILTER_TYPE = 'AAP', $recompliance_status = 0)
    {
        // 1 AAP  = ALL AUDIT POINTS (WITH & WITHOUT COMPLIANCE)
        // 2 ACP  = ALL COMPLIANCE POINTS
        // 3 AACRP = ALL AUDIT IS COMPLIANCE REJECTED POINTS
        // 4 ACRP = ALL COMPLIANCE REJECTED POINTS
        // 5 PCDR = PENDING COMPLIANCE DETAILED REPORT

        $dataArray = array(
            'ans_data' => [], 'annex_tab_ids' => [], 'annex_tab_cols' => [], 
            'dump_advances' => [], 'dump_deposits' => [], 'ans_ids' => [], 'ans_annex_ids' => [], 'is_compliance_cnt' => 0, 'subset_master' => [] );

        //find observations
        $model = $this_obj -> model('AnswerDataModel');

        // DEFAULT QUERY
        $query = "SELECT ans.*, qm.question, qm.option_id, qm.annexure_id, qm.risk_category_id";
        
        if(check_evidence_upload_strict()) $query .= ", qm.audit_ev_upload, qm.compliance_ev_upload";

        $query .= " FROM answers_data ans JOIN question_master qm ON ans.question_id = qm.id WHERE ans.assesment_id = '". $assessmentData -> id ."'";

        if( $FILTER_TYPE == 'AAP' )
            $query .= " AND (
                                (ans.dump_id != '0' AND ans.is_compliance = '1') OR 
                                (ans.dump_id = '0')
                            )";
        elseif( $FILTER_TYPE == 'ACP' )
            $query .= " AND ans.is_compliance = '1'";
        elseif( $FILTER_TYPE == 'AACRP' )
            $query .= " AND ans.is_compliance = '1' AND ans.audit_status_id = '3' OR ans.ro_compliance_status_id = '3'";
        elseif( $FILTER_TYPE == 'ACRP' )
            $query .= " AND ans.is_compliance = '1' AND ans.compliance_status_id = '3' OR ans.ro_compliance_status_id = '3'";
        elseif( $FILTER_TYPE == 'PCDR')
            $query .= " AND ans.is_compliance = '1' AND ( ans.compliance_status_id = '3' OR qm.annexure_id > 0 )";  

        // add filter code 30.10.2024
        if(in_array($FILTER_TYPE, ['AAP', 'ACP']))
        {
            $filterQuery = '';

            if( $this_obj -> request -> has('risk_category_arr') && 
                is_array($this_obj -> request -> input('risk_category_arr')) && 
                sizeof($this_obj -> request -> input('risk_category_arr')) > 0)
                $filterQuery .= " qm.risk_category_id IN (". implode(',', $this_obj -> request -> input('risk_category_arr')) .")";

            if( $this_obj -> request -> has('business_risk_arr') && 
                is_array($this_obj -> request -> input('business_risk_arr')) && 
                sizeof($this_obj -> request -> input('business_risk_arr')) > 0)
                $filterQuery .= (!empty($filterQuery) ? " AND " : "") . " ans.business_risk IN (". implode(',', $this_obj -> request -> input('business_risk_arr')) .")";

            if( $this_obj -> request -> has('control_risk_arr') && 
                is_array($this_obj -> request -> input('control_risk_arr')) && 
                sizeof($this_obj -> request -> input('control_risk_arr')) > 0)
                $filterQuery .= (!empty($filterQuery) ? " AND " : "") . " ans.control_risk IN (". implode(',', $this_obj -> request -> input('control_risk_arr')) .")";

            if(!empty($filterQuery))
                $query .= " AND ( (". $filterQuery .") OR (qm.option_id = 4 AND ans.answer_given = qm.annexure_id) )";
        }

        // add period wise question ids
        $query .= " AND ans.question_id IN (".  $assessmentData -> question_ids .") AND ans.deleted_at IS NULL";

        // check for CF
        if(check_carry_forward_strict() && in_array($FILTER_TYPE, ['AAP', 'ACP', 'AACRP', 'ACRP']))
        {
            // UNION ALL
            $query .= " UNION ALL ";
            
            // CF QUERY
            $query .= " SELECT ans.*, 
                        NULL AS question, 
                        NULL AS option_id, 
                        NULL AS annexure_id, 
                        NULL AS risk_category_id";
        
            if(check_evidence_upload_strict()) $query .= ", NULL AS audit_ev_upload, NULL AS compliance_ev_upload";

            $query .= " FROM answers_data ans WHERE 
                        ans.assesment_id = '". $assessmentData -> id ."'
                        AND ans.is_compliance = '1' 
                        AND ans.answer_given = 'CF' 
                        AND ans.question_id = 0 
                        AND ans.deleted_at IS NULL";
        }
        
        // ADD ORDER BY 
        $query .= " ORDER BY dump_id, menu_id, category_id, header_id, question_id";

        $findObservation = $model -> getAllAnswers( [], 'sql', $query );

        if( is_array($findObservation) && sizeof($findObservation) > 0 )
        {
            // assign count
            // $dataArray['is_compliance_cnt'] = sizeof($findObservation);

            // find all subset data
            $model = $this_obj -> model('QuestionSetModel');

            $dataArray['subset_master'] = $model -> getAllQuestionSet([
                'where' => 'is_active = 1 AND deleted_at IS NULL AND set_type_id = 2',
                'params' => []
            ], 'sql', 'SELECT id, name FROM ' . $model -> getTableName());

            $dataArray['subset_master'] = generate_data_assoc_array($dataArray['subset_master'], 'id');

            // convert to array
            $findObservation = generate_data_assoc_array($findObservation, 'id');
            
            // function call
            if( in_array($FILTER_TYPE, ['ACP','AACRP','ACRP']) && 
                check_evidence_upload_strict() )   
                $findObservation = get_evidence_upload_data($assessmentData, $findObservation, array_keys($findObservation), 1);
             
            //find observations in annexure
            $model = $this_obj -> model('AnswerDataAnnexureModel');

            $annexWhereData = [
                'where' => 'assesment_id = :assesment_id AND answer_id IN ('. implode(',', array_keys($findObservation)) .') AND deleted_at IS NULL',
                'params' => [ 'assesment_id' => $assessmentData -> id ]
            ];

            if( $FILTER_TYPE == 'AACRP' )
                $annexWhereData['where'] .= " AND audit_status_id = '3'";
            elseif( in_array($FILTER_TYPE, ['ACRP', 'PCDR']) )
                $annexWhereData['where'] .= " AND compliance_status_id = '3'";

            $sortFilterApplied = false;

            // add filter code 30.10.2024
            if(in_array($FILTER_TYPE, ['AAP', 'ACP']))
            {
                $filterQuery = '';

                if( $this_obj -> request -> has('risk_category_arr') && 
                    is_array($this_obj -> request -> input('risk_category_arr')) && 
                    sizeof($this_obj -> request -> input('risk_category_arr')) > 0)
                    $filterQuery .= " risk_cat_id IN (". implode(',', $this_obj -> request -> input('risk_category_arr')) .")";

                if( $this_obj -> request -> has('business_risk_arr') && 
                    is_array($this_obj -> request -> input('business_risk_arr')) && 
                    sizeof($this_obj -> request -> input('business_risk_arr')) > 0)
                    $filterQuery .= (!empty($filterQuery) ? " AND " : "") . " business_risk IN (". implode(',', $this_obj -> request -> input('business_risk_arr')) .")";

                if( $this_obj -> request -> has('control_risk_arr') && 
                    is_array($this_obj -> request -> input('control_risk_arr')) && 
                    sizeof($this_obj -> request -> input('control_risk_arr')) > 0)
                    $filterQuery .= (!empty($filterQuery) ? " AND " : "") . " control_risk IN (". implode(',', $this_obj -> request -> input('control_risk_arr')) .")";

                if(!empty($filterQuery))
                {
                    $annexWhereData['where'] .= " AND " . $filterQuery;
                    $sortFilterApplied = true;
                }
            }

            $findAllAnnexure = $model -> getAllAnswerAnnexures($annexWhereData);

            $annexureAnsData = [];

            if(is_array($findAllAnnexure) && sizeof($findAllAnnexure) > 0)
            {
                $annexIds = [];

                foreach($findAllAnnexure as $cAnnexDetails)
                {
                    if( /*in_array($FILTER_TYPE, [1, 2, 3]) || 
                        (in_array($FILTER_TYPE, [4, 5]) && $row['reviewer_accept_reject_compliance'] == '2')*/ 1 )
                    {
                        $cGenKey = $cAnnexDetails -> answer_id;

                        if(!array_key_exists($cGenKey, $annexureAnsData))
                            $annexureAnsData[ $cGenKey ] = [];

                        // remove from above if 05.08.2024
                        if(!in_array($cAnnexDetails -> id, $annexIds))
                            $annexIds[] = $cAnnexDetails -> id;

                        $annexureAnsData[ $cGenKey ][ $cAnnexDetails -> id ] = $cAnnexDetails;
                    }
                }

                // function call for evidence upload
                if( in_array($FILTER_TYPE, ['ACP','AACRP','ACRP']) && 
                    check_evidence_upload_strict() && 
                    sizeof($annexIds) > 0 )
                {
                    $annexureAnsData = get_evidence_upload_data($assessmentData, $annexureAnsData, $annexIds, 2);
                }
            }

            //function call 
            $commonDataArray = find_audit_observations_common_data($this_obj, $assessmentData);
            
            if( is_array($commonDataArray) )
                $commonDataArray['sortFilterApplied'] = $sortFilterApplied;

            $dataArray = get_audit_sorted_questions($this_obj, $findObservation, $dataArray, $annexureAnsData, $commonDataArray);

            if(sizeof($dataArray['ans_data']) > 0)
            {
                ksort( $dataArray['ans_data'] ); // IMPORTANT

                // FIND LOAN ACCOUNTS
                if(sizeof($dataArray['dump_advances']) > 0)
                {
                    // helper function call
                    $dataArray['dump_advances'] = find_mixed_dump_data($this_obj, $assessmentData, [
                        'where' => 'dt.id IN ('. implode(',', $dataArray['dump_advances']) .') ORDER BY sm.scheme_code+0',
                        'params' => []
                    ]);
                }

                // FIND DEPOSIT ACCOUNTS
                if(sizeof($dataArray['dump_deposits']) > 0)
                {
                    // helper function call
                    $dataArray['dump_deposits'] = find_mixed_dump_data($this_obj, $assessmentData, [
                        'where' => 'dt.id IN ('. implode(',', $dataArray['dump_deposits']) .') ORDER BY sm.scheme_code+0',
                        'params' => []
                    ], 1);
                }

                // FIND ANNEXURES
                if(sizeof($dataArray['annex_tab_ids']) > 0)
                {
                    // find annexure
                    $model = $this_obj -> model('AnnexureMasterModel');

                    $dataArray['annex_tab_cols'] = $model -> getAllAnnexures([
                        'where' => 'id IN ('. implode(',', $dataArray['annex_tab_ids']) .')',
                        'params' => []
                    ]);
                    
                    if(is_array($dataArray['annex_tab_cols']) && sizeof($dataArray['annex_tab_cols']) > 0)
                    {
                        // helper function call
                        $dataArray['annex_tab_cols'] = generate_data_assoc_array($dataArray['annex_tab_cols'], 'id');

                        // find annexure columns
                        $model = $this_obj -> model('AnnexureColumnModel');

                        $annexColsData = $model -> getAllAnnexureColumns([
                            'where' => 'annexure_id IN ('. implode(',', array_keys($dataArray['annex_tab_cols'])) .')',
                            'params' => []
                        ]);

                        if(is_array($annexColsData) && sizeof($annexColsData) > 0)
                        {
                            foreach($annexColsData as $cAnnexColDetails)
                            {
                                if(array_key_exists($cAnnexColDetails -> annexure_id, $dataArray['annex_tab_cols']))
                                {
                                    if(!isset($dataArray['annex_tab_cols'][ $cAnnexColDetails -> annexure_id ] -> annex_cols))
                                        $dataArray['annex_tab_cols'][ $cAnnexColDetails -> annexure_id ] -> annex_cols = [];

                                    // push data
                                    $dataArray['annex_tab_cols'][ $cAnnexColDetails -> annexure_id ] -> annex_cols[ $cAnnexColDetails -> id ] = $cAnnexColDetails;
                                }
                            }
                        }

                        // unset data
                        unset($annexColsData);
                    }
                }
                else
                    $dataArray['annex_tab_cols'] = array();

                // scheme wise data sort 16.10.2024 for dump
                foreach($dataArray['ans_data'] as $cMenuId => $cMenuData)
                {
                    if( isset($cMenuData['category']) && 
                        is_array($cMenuData['category']) && 
                        sizeof($cMenuData['category']) > 0)
                    {
                        foreach($cMenuData['category'] as $cCatId => $cCatData)
                        {
                            if( isset($cCatData -> linked_table_id) && 
                                in_array($cCatData -> linked_table_id, [1,2]) && 
                                isset($cCatData -> dump) && 
                                is_array($cCatData -> dump) && 
                                sizeof($cCatData -> dump) > 0)
                            {
                                $sortedDump = [];
                                $dumpData = [];

                                if( $cCatData -> linked_table_id == 1 && 
                                    isset($dataArray['dump_deposits']) && 
                                    is_array($dataArray['dump_deposits']) && 
                                    sizeof($dataArray['dump_deposits']) > 0) // for deposits
                                    $dumpData = $dataArray['dump_deposits'];
                                elseif( $cCatData -> linked_table_id == 2 && 
                                    isset($dataArray['dump_advances']) && 
                                    is_array($dataArray['dump_advances']) && 
                                    sizeof($dataArray['dump_advances']) > 0) // for deposits
                                    $dumpData = $dataArray['dump_advances'];

                                // loop
                                foreach($cCatData -> dump as $ccDumpId => $ccDumpData)
                                {
                                    if( array_key_exists($ccDumpId, $dumpData) && 
                                        !array_key_exists($dumpData[ $ccDumpId ] -> scheme_code, $sortedDump) )
                                        $sortedDump[ $dumpData[ $ccDumpId ] -> scheme_code ] = [];

                                    $sortedDump[ $dumpData[ $ccDumpId ] -> scheme_code ][ $ccDumpId ] = $ccDumpData;
                                }

                                if(sizeof($sortedDump) > 0)
                                {
                                    $dumpData = [];

                                    foreach($sortedDump as $cScCode => $scDumpData) {
                                        foreach($scDumpData as $ccDumpId => $ccDumpData)
                                            $dumpData[ $ccDumpId ] = $ccDumpData;
                                    }

                                    // overwrite data
                                    $dataArray['ans_data'][ $cMenuId ]['category'][ $cCatId ] -> dump = $dumpData;
                                }

                                unset($dumpData, $sortedDump);
                            }
                        }
                    }
                }
            }

            unset($findAllAnnexure);

        }

        return $dataArray;
    }
}

if(!function_exists('generate_report_td_markup'))
{
    function generate_report_td_markup($FILTER_TYPE, $cAnsDetails, $reviewActionArray, $colSelect, $type = 'annex')
    {
        $str = '';

        // Check if user is RO (user_type_id = 16)
        $is_ro_user = false;
        if(isset($_SESSION['emp_type']) && $_SESSION['emp_type'] == 16) {
            $is_ro_user = true;
        }

        $checkAnnexAns = (
            $type == 'gen' &&
            isset($cAnsDetails -> option_id) && 
            isset($cAnsDetails -> annexure_id) && 
            $cAnsDetails -> option_id == 4 && 
            $cAnsDetails -> annexure_id == $cAnsDetails -> answer_given
        );

        // ACCEPT REJECT AUDIT, COMPLIANCE - For non-RO users
        if(in_array($FILTER_TYPE, ['RVAU','RVCOM']) && !$is_ro_user)
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
                <textarea class="form-control" data-ansid="'. encrypt_ex_data($cAnsDetails -> id) .'" data-anstype="'. $type .'">'. urldecode_data($cAnsDetails -> audit_commpliance) .'</textarea>
                <small class="reponse-status d-block mt-1 mb-2"></small>
                <button class="btn btn-secondary btn-sm">Save Compliance</button>
            </td>' . "\n";
        }
        
        // REVIEWER COMMENT GIVE TEXTAREA - For non-RO users
        if(in_array($FILTER_TYPE, ['RVAU','RVCOM']) && !$is_ro_user)
        {
            $str .= '<td width="180px" class="comment-container">
                <textarea class="form-control" data-ansid="'. encrypt_ex_data($cAnsDetails -> id) .'" data-anstype="'. $type .'">'. ( ( $FILTER_TYPE == 'RVAU' ) ? trim_str( $cAnsDetails -> audit_reviewer_comment ) : trim_str( $cAnsDetails -> compliance_reviewer_comment ) ) .'</textarea>
                <small class="reponse-status d-block mt-1 mb-2"></small>
                <button class="btn btn-secondary btn-sm">Save Comment</button>
            </td>' . "\n";
        }

        // REVIEWER COMMENT DISPLAY - For non-RO users (View Only)
        if( in_array($FILTER_TYPE, ['REARP', 'RECOM']) && !$is_ro_user )
        {   
            $str .= '<td>'. ( ( $FILTER_TYPE == 'REARP' ) ? trim_str( array_key_exists($cAnsDetails -> audit_status_id, AUDIT_STATUS_ARRAY['audit_review_action']) ? AUDIT_STATUS_ARRAY['audit_review_action'][$cAnsDetails -> audit_status_id] : ERROR_VARS['notFound'] ) : trim_str( array_key_exists($cAnsDetails -> compliance_status_id, AUDIT_STATUS_ARRAY['compliance_review_action']) ? AUDIT_STATUS_ARRAY['compliance_review_action'][$cAnsDetails -> compliance_status_id] : ERROR_VARS['notFound'] ) ) .'</td>';

            $reviewerComment = ( ( $FILTER_TYPE == 'REARP' ) ? trim_str( $cAnsDetails -> audit_reviewer_comment ) : trim_str( $cAnsDetails -> compliance_reviewer_comment ) );

            $str .= '<td>'. ( !empty($reviewerComment) ? $reviewerComment : '-' ) .'</td>';

            unset($reviewerComment);
        }

        // =============================================
        // REMOVED: RO COLUMNS DISPLAY from here
        // They are now handled by generate_report_ro_comment_columns function
        // =============================================
        
        // RO SPECIFIC COLUMNS - For RO users only (Editable)
        if (in_array($FILTER_TYPE, ['REARP', 'RECOM', 'RVCOM']) && $is_ro_user)
{   
    $roReviewActionArray = AUDIT_STATUS_ARRAY['compliance_review_action'];
    
    if ($FILTER_TYPE == 'REARP') {
        $currentStatus = isset($cAnsDetails->ro_audit_status_id) ? $cAnsDetails->ro_audit_status_id : 2;
        $actionType = 'ro_audit';
    } else {
        $currentStatus = isset($cAnsDetails->ro_compliance_status_id) ? $cAnsDetails->ro_compliance_status_id : 2;
        $actionType = 'ro_compliance';
    }
    
    // ✅ ACTION COLUMN
    $str .= '<td width="160px" class="reviewer-action">
        <select class="form-control form-select" 
            data-ansid="'. encrypt_ex_data($cAnsDetails->id) .'" 
            data-anstype="'. $type .'" 
            data-slctact="'. $actionType .'">';

    foreach ($roReviewActionArray as $statusId => $statusLabel) {
        $selected = ($currentStatus == $statusId) ? ' selected="selected"' : '';
        $str .= '<option value="'. $statusId .'"'. $selected .'>'. $statusLabel .'</option>';
    }

    $str .= '</select>
        <small class="reponse-status d-block mt-1 mb-2"></small>
    </td>' . "\n";   // ✅ FIXED (was wrong before)

    // ✅ COMMENT COLUMN
    $roReviewerComment = trim_str($cAnsDetails->ro_reviewer_comment ?? '');

    $str .= '<td class="comment-container">
        <textarea class="form-control" 
            data-ansid="'. encrypt_ex_data($cAnsDetails->id) .'" 
            data-anstype="'. $type .'" 
            data-slctact="'. $actionType .'_comment">'. $roReviewerComment .'</textarea>
        
        <small class="reponse-status d-block mt-1 mb-2"></small>
        
        <button class="btn btn-secondary btn-sm save-comment-btn">
            Save Comment
        </button>
    </td>' . "\n";

    unset($roReviewerComment, $roReviewActionArray);
}

        return $str;
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
                    $str .= '<td>'. string_operations((is_object($data['data']['risk_category_data'][ $cAnsDetails -> risk_cat_id ]) ? $data['data']['risk_category_data'][ $cAnsDetails -> risk_cat_id ] -> risk_category : $data['data']['risk_category_data'][ $cAnsDetails -> risk_cat_id ]), 'upper') .'<td>';
                else
                    $str .= '<td></td>';
            }
            else
                $str .= '<td></td>';
        }

        return $str;
    }
}
if(!function_exists('generate_report_ro_comment_columns'))
{
    function generate_report_ro_comment_columns($data, $FILTER_TYPE, $cAnsDetails, $onlyCols = false)
    {
        $str = '';

        if($onlyCols)
        {
            if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'RVCOM', 'RECOM', 'PCDR']))
                $str .= '<th>RO Review Status</th>';

            if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'RVCOM', 'RECOM', 'PCDR']))
                $str .= '<th>RO Comment</th>';

            return $str;
        }

        // RO REVIEW STATUS
        if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'RVCOM', 'RECOM', 'PCDR']))
        {
            $roStatusId = null;
            if( in_array($FILTER_TYPE, ['REARP', 'RVAU']) ) {
                $roStatusId = isset($cAnsDetails->ro_audit_status_id) ? $cAnsDetails->ro_audit_status_id : 2;
            } else {
                $roStatusId = isset($cAnsDetails->ro_compliance_status_id) ? $cAnsDetails->ro_compliance_status_id : 2;
            }
            
            $roStatusText = 'Pending RO Review';
            if(defined('AUDIT_STATUS_ARRAY') && isset(AUDIT_STATUS_ARRAY['compliance_review_action'][$roStatusId])) {
                $roStatusText = AUDIT_STATUS_ARRAY['compliance_review_action'][$roStatusId];
            }
            
            $str .= '<td>' . $roStatusText . '</td>';
        }

        // RO COMMENT
        if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'RVCOM', 'RECOM', 'PCDR']))
        {
            $roComment = '-';
            if(isset($cAnsDetails->ro_reviewer_comment) && !empty(trim($cAnsDetails->ro_reviewer_comment)))
            {
                $roComment = string_operations(trim($cAnsDetails->ro_reviewer_comment), 'upper');
            }
            $str .= '<td>' . $roComment . '</td>';
        }

        return $str;
    }
}
if(!function_exists('generate_evidence_checkbox_markup'))
{ 
    //  function generate evidence checkbox markup
    function generate_evidence_checkbox_markup($ans, $filterType = [], $eviType = 1, $val = 1)
    {
        // $eviType = 1 for audit / 2 for compliance
        $str = null;

        $eviTypeCol = ($eviType == 1) ? 'audit_compulsary_ev_upload' : 'compliance_compulsary_ev_upload';

        $str = '<label class="'. (($eviType == 1) ? 'audit' : 'compliance') .'-evidence-checkbox font-sm font-medium d-block">
                <input class="'. (($eviType == 1) ? 'audit' : 'compliance') .'-evidence-chckbox" type="checkbox" value="'. $val .'" '. ((is_object($ans) && in_array($ans -> { $eviTypeCol }, $filterType)) ? ' checked="checked"' : '');
        
        if(is_object($ans) && isset($ans -> answer_id) )
        {
            // for annex
            $str .= ' data-ansid="'. encrypt_ex_data($ans -> answer_id) .'" data-annexid="'. encrypt_ex_data($ans -> id) .'"';
            $ajxUrl = EVIDENCE_UPLOAD['control_url'] . 'annex-upload-status/';
        }
        else
        {
            // for ans
            $str .= ' data-ansid="'. encrypt_ex_data($ans -> id) .'"';
            $ajxUrl = EVIDENCE_UPLOAD['control_url'] . 'ans-upload-status/';
        }

        $str .= ' data-ajxurl="'. $ajxUrl .'" /> <span>'. EVIDENCE_UPLOAD['checkbox_text'] .' '. ( ($eviType == 1) ? '(Audit)' : ('(Compliance)' . ($ans -> { $eviTypeCol } == 2 ? ' - Auditor Checked' : '')) ) .'</span></label>' . "\n";

        return $str;
    }
}

if(!function_exists('generate_display_questions_markup'))
{ 
    //function display questions
    function generate_display_questions_markup($data, $questionsArray, $dataArray, $colspan, $FILTER_TYPE = 'RVAU')
    {
        // 1  RVAU = REVIEW AUDIT - RA
        // 2  REARP = RE AUDIT REPORT - REAR
        // 3  RVCOM = REVIEW COMPLIANCE - RC
        // 4  COM = COMPLIANCE
        // 5  RECOM = RE COMPLIANCE - REC
        // 6  ARCRP = AUDIT REPORT COMPLETE REPORT - ARCR
        // 7  COMRP = COMPLIANCE REPORT - CR 
        // 8  CRPWC = COMPLIANCE REPORT WITH COMMENT - CRWC
        // 9  PCDR = PENDING COMPLIANCE DETAILED REPORT

        // Check if user is RO (user_type_id = 16)
        $is_ro_user = false;
        if(isset($_SESSION['emp_type']) && $_SESSION['emp_type'] == 16) {
            $is_ro_user = true;
        }

        $str = '';
        $res = [ 'aud_reject' => 0, 'com_reject' => 0, 'com_pending' => 0 ];
        $checkCF = check_carry_forward_strict();

        foreach ($questionsArray as $cHeaderId => $cHeaderDetails)
        {
            // re assign cf 08.09.2024
            $checkCF = ($checkCF && $cHeaderId == CARRY_FORWARD_ARRAY['id']);

            $str .= '<tr><td colspan="'. $colspan .'" class="bg-light-gray font-medium"><u>'. strtoupper('Header: ' . (is_object($cHeaderDetails['header']) ? $cHeaderDetails['header'] -> name : ERROR_VARS['notFound'])) .'</u></td></tr>' . "\n";

            $str .= '<tr>
                <th class="text-center">#</th>
                <th>Question</th>
                <th>Audit Point</th>
                <th>Audit Comment</th>';

                if(in_array($FILTER_TYPE, ['CRPWC', 'COM', 'RECOM', 'RVCOM', 'PCDR']))
                    $str .= '<th>Compliance</th>';

                if( in_array($FILTER_TYPE, ['']))
                    $str .= '<th>Action</th>';

                if( in_array($FILTER_TYPE, ['COM', 'RECOM']) && 
                    check_evidence_upload_strict() )
                    $str .= '<th>Evidence</th>';

                // function call
                $str .= generate_report_risk_columns(null, $FILTER_TYPE, null, 1);

                // For non-RO users: Show Review Status and Reviewer Comment
                if( !$is_ro_user ) {
                    if( in_array($FILTER_TYPE, ['REARP', 'RVAU', 'RVCOM', 'RECOM']))
                        $str .= '<th>Review Status</th>';

                    if( in_array($FILTER_TYPE, ['REARP', 'RVAU', 'RVCOM', 'RECOM', 'PCDR']))
                        $str .= '<th>Reviewer Comment</th>';
                    
                    // ONLY show RO columns for these filter types - CALL THIS ONCE
                    if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'RVCOM', 'RECOM']))
                        $str .= generate_report_ro_comment_columns($data, $FILTER_TYPE, null, true);
                }

                // For RO users: Show RO specific columns
                if( $is_ro_user ) {
                    if( in_array($FILTER_TYPE, ['REARP', 'RECOM', 'RVCOM']))
                        $str .= generate_report_ro_comment_columns($data, $FILTER_TYPE, null, true);
                }

            $str .= '</tr>';

            $srNo = 1;

            foreach($cHeaderDetails['questions'] as $cQueId => $cQueDetails)
            {
                $compulsaryClassStyle = (in_array($FILTER_TYPE, ['RVAU', 'COM', 'RECOM', 'RVCOM'])) ? 'text-danger' : '';

                // REVIEW AUDIT CHECK
                if( in_array($FILTER_TYPE, ['RVAU', 'RVCOM']) && 
                    ($cQueDetails -> audit_status_id != 3 || $cQueDetails -> compliance_status_id != 3) )
                    $compulsaryClassStyle = '';

                // trim data 
                $cQueDetails -> question = trim_str(urldecode_data($cQueDetails -> question));
                $cQueDetails -> answer_given = trim_str(urldecode_data($cQueDetails -> answer_given));
                $cQueDetails -> audit_comment = trim_str(urldecode_data($cQueDetails -> audit_comment));
                $cQueDetails -> audit_commpliance = trim_str(urldecode_data($cQueDetails -> audit_commpliance));
                $cQueDetails -> audit_reviewer_comment = trim_str(urldecode_data($cQueDetails -> audit_reviewer_comment));
                $cQueDetails -> compliance_reviewer_comment = trim_str(urldecode_data($cQueDetails -> compliance_reviewer_comment));

                $activateEvidence = 0;

                if(check_evidence_upload_strict())
                {
                    if(in_array($FILTER_TYPE, ['RVAU']))
                        $activateEvidence = 1;

                    if( in_array($FILTER_TYPE, ['COM', 'RECOM']) && in_array($cQueDetails -> compliance_compulsary_ev_upload, [1,2]) )
                        $activateEvidence = 1;
                }

                if( empty($cQueDetails -> audit_commpliance) && $cQueDetails -> option_id != 4 )
                    $res['com_pending']++;

                if( in_array($FILTER_TYPE, ['REARP']) )
                    $compulsaryClassStyle = '';
                else if(in_array($FILTER_TYPE, ['COM']) && !empty($cQueDetails -> audit_commpliance))
                    $compulsaryClassStyle = '';
                else if(in_array($FILTER_TYPE, ['RECOM']) && 
                        isset($data['db_assesment']) && 
                        $cQueDetails -> batch_key == $data['db_assesment'] -> batch_key )
                        $compulsaryClassStyle = '';
                else if(in_array($FILTER_TYPE, ['RVCOM']) && 
                        $cQueDetails -> compliance_status_id == 2 )
                        $compulsaryClassStyle = '';

                $str .= '<tr class="'. $compulsaryClassStyle .'">' . "\n";
                
                    // SR NO
                    $str .= '<td class="text-center">'. $srNo .'</td>' . "\n";
                    $srNo++;

                    // QUESTION DESCRIPTION
                    if($checkCF)
                        $str .= '<td>'. $cHeaderDetails['header'] -> name .'</td>' . "\n";
                    else
                        $str .= '<td>'. $cQueDetails -> question .'</td>' . "\n";

                    // ANSWER GIVEN
                    if(!$checkCF)
                    {
                        if( $cQueDetails -> option_id == '5' && 
                            isset($dataArray['subset_master']) && 
                            is_array($dataArray['subset_master']) && 
                            array_key_exists($cQueDetails -> answer_given, $dataArray['subset_master']))
                            $str .= '<td>'. string_operations($dataArray['subset_master'][ $cQueDetails -> answer_given ] -> name, 'upper');
                        else
                            $str .= '<td>'. string_operations((($cQueDetails -> option_id == '4' && !empty($cQueDetails -> annexure_id) && $cQueDetails -> answer_given == $cQueDetails -> annexure_id ) ? AS_PER_ANNEXURE : $cQueDetails -> answer_given), 'upper');

                        // ADD EVIDANCE CHECK BOX
                        if( in_array($FILTER_TYPE, ['RVAU','RVCOM']) && 
                            check_evidence_upload_strict() )
                        {
                            $str .= '<div class="mt-2">' . "\n";

                            if( $FILTER_TYPE == 'RVAU')
                                $str .= generate_evidence_checkbox_markup($cQueDetails, [1], 1, 2);
                            
                            $str .= generate_evidence_checkbox_markup($cQueDetails, [1,2], 2, 2);

                            $str .= '</div>' . "\n";
                        }

                        // FOR EVIDENCE MARKUP
                        if( in_array($FILTER_TYPE, ['RVAU','RVCOM','COM']) && 
                            check_evidence_upload_strict() && 
                            isset($cQueDetails -> audit_evidence) )
                        {
                            $str .= '<div class="mt-2">' . "\n";
                                $str .= display_evidence_markup($cQueDetails, 1);
                            $str .= '</div>' . "\n";
                        }

                        $str .= '</td>' . "\n";
                    }
                    else
                        $str .= '<td></td>';

                    // AUDIT COMMENT
                    if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'COM', 'RECOM', 'RVCOM', 'PCDR', 'COMRP']) )
                        $str .= '<td>'. (!empty($cQueDetails -> audit_comment) ? string_operations($cQueDetails -> audit_comment, 'comma_space') : '') .'</td>' . "\n";

                    // VIEW COMPLIANCE
                    if(in_array($FILTER_TYPE, ['CRPWC', 'RVCOM', 'PCDR']))
                    {
                        $str .= '<td>'. string_operations($cQueDetails -> audit_commpliance, 'comma_space');

                            if( in_array($FILTER_TYPE, ['RVAU', 'RVCOM']) && 
                                check_evidence_upload_strict() && 
                                isset($cQueDetails -> compliance_evidence) )
                            {
                                $str .= '<div class="mt-2">' . "\n";
                                $str .= display_evidence_markup($cQueDetails, 2);
                                $str .= '</div>' . "\n";
                            }
                        
                        $str .= '</td>';
                    }

                    if(in_array($FILTER_TYPE, ['PCDR']))
                    {
                        $str .= '<td>'. string_operations($cQueDetails -> compliance_reviewer_comment, 'comma_space');
                    }                    

                    // risk type data
                    $str .= generate_report_risk_columns($data, $FILTER_TYPE, $cQueDetails);
                    $reviewActionArray = null;
                    $colSelect = null;

                    // DEFAULT ACTION DISPLAY
                    if( in_array($FILTER_TYPE, ['REARP', 'RVAU']) )
                    {
                        $reviewActionArray = AUDIT_STATUS_ARRAY['audit_review_action'];
                        $colSelect = 'audit_status_id';
                    }
                    elseif( in_array($FILTER_TYPE, ['RVCOM', 'RECOM']) )
                    {
                        $reviewActionArray = AUDIT_STATUS_ARRAY['compliance_review_action'];
                        $colSelect = 'compliance_status_id';
                    }

                    // Replace the existing RO column data generation with:

                    // For non-RO users
                    if( !$is_ro_user ) {
                        if( in_array($FILTER_TYPE, ['REARP', 'RVAU', 'RVCOM', 'RECOM']))
                            $str .= generate_report_td_markup($FILTER_TYPE, $cQueDetails, $reviewActionArray, $colSelect, 'gen');
                        
                        // CALL THIS ONCE for RO columns data
                        if( in_array($FILTER_TYPE, ['REARP', 'ARCRP', 'CRPWC', 'RVAU', 'RVCOM', 'RECOM']))
                            $str .= generate_report_ro_comment_columns($data, $FILTER_TYPE, $cQueDetails, false);
                    }

                    // For RO users
                    if( $is_ro_user ) {
                        if( in_array($FILTER_TYPE, ['REARP', 'RECOM', 'RVCOM']))
                            $str .= generate_report_td_markup($FILTER_TYPE, $cQueDetails, $reviewActionArray, $colSelect, 'gen');
                        
                        // For RO users, you might not need to show RO columns as display since they have edit controls
                        // But if you need to show them, uncomment below:
                        // if( in_array($FILTER_TYPE, ['REARP', 'RECOM', 'RVCOM']))
                        //     $str .= generate_report_ro_comment_columns($data, $FILTER_TYPE, $cQueDetails, false);
                    }

                    // Evidence section for compliance
                    if( in_array($FILTER_TYPE, ['COM','RECOM']) && 
                        check_evidence_upload_strict() && $activateEvidence && !$is_ro_user )
                    {
                        $activatEviBtn = true;

                        if( isset($cQueDetails -> compliance_evidence) && 
                            is_array($cQueDetails -> compliance_evidence) && sizeof($cQueDetails -> compliance_evidence) > 0 )
                            $activatEviBtn = false;

                        $str .= '<td>
                            <div class="my-2 position-relative question-row" data-ansid="' . encrypt_ex_data($cQueDetails -> id) .'">
                                
                                <button class="evidence-upload-btn compliance-evi-btn" '. (!$activatEviBtn ? 'style="display:none"' : '') .''. view_tooltip('Upload File') .'>Evidence</button>                            
                                
                                <div class="evidence-upload-container compliance-evi-container">';
                                
                                $str .= display_evidence_markup($cQueDetails, 2);
                                
                            $str .= '</div>
                            </div>';

                            if( in_array($cQueDetails -> compliance_compulsary_ev_upload, [1,2]) )
                                $str .= '<p class="font-sm text-danger"><b>Note:</b> Compulsary evidence upload from ('. (($cQueDetails -> compliance_compulsary_ev_upload == 1) ? 'Reviewer' : 'Auditor') .')</p>';

                        $str .= '</td>';
                    }
                    else if(in_array($FILTER_TYPE, ['COM','RECOM']) && check_evidence_upload_strict() && !$is_ro_user )
                        $str .= '<td>-</td>';

                $str .= '</tr>' . "\n";

                // Annexure section (same for both RO and non-RO)
                $cGenKey = $cQueDetails -> id . '_0_2';

                if(( $cQueDetails -> option_id == '4') && 
                    sizeof($dataArray['annex_tab_cols']) > 0 &&
                    array_key_exists($cQueDetails -> annexure_id, $dataArray['annex_tab_cols']) && 
                    isset($dataArray['annex_tab_cols'][$cQueDetails -> annexure_id] -> annex_cols) && 
                    isset($cQueDetails -> annex) || 
                    (   $checkCF && 
                        isset($cQueDetails -> annex) && 
                        is_array($cQueDetails -> annex) && 
                        sizeof($cQueDetails -> annex) > 0
                    ) )
                {
                    // START EMPTY TR
                    $str .= '<tr class="has-annexure-data"><td colspan="'. $colspan .'">' . "\n";
                    $annex_col_count = (!$checkCF) ? sizeof($dataArray['annex_tab_cols'][$cQueDetails -> annexure_id] -> annex_cols) : 1;

                    $str .= '<table class="table table-sm table-bordered mb-0"><tr>' . "\n";

                            if(!$checkCF)
                            {
                                foreach($dataArray['annex_tab_cols'][$cQueDetails -> annexure_id] -> annex_cols as $cAnnexColId => $cAnnexColDetails)
                                {
                                    $str .= '<th>'. string_operations($cAnnexColDetails -> name, 'upper') .'</th>' . "\n";   
                                }
                            }
                            else
                                $str .= '<th>'. string_operations($cHeaderDetails['header'] -> name, 'upper') .'</th>' . "\n";   

                            if(in_array($FILTER_TYPE, ['CRPWC', 'COM', 'RECOM', 'RVCOM']))
                                $str .= '<th'. (($FILTER_TYPE == 'RECOM') ? ' colspan="0"' : '') .'>Compliance</th>' . "\n"; 

                            if(in_array($FILTER_TYPE, ['PCDR']))
                                $str .= '<th'. (($FILTER_TYPE == 'RECOM') ? ' colspan="4"' : '') .'>Compliance</th>' . "\n";

                            $str .= generate_report_risk_columns(null, $FILTER_TYPE, null, 1);

                            // For non-RO users
                            if( !$is_ro_user ) {
                                if( in_array($FILTER_TYPE, ['REARP', 'RVAU', 'RVCOM']))
                                    $str .= '<th>Review Status</th>';
                                
                                if( in_array($FILTER_TYPE, ['REARP', 'RVAU', 'RVCOM', 'PCDR']))
                                    $str .= '<th>Reviewer Comment</th>';
                            }
                            
                            // For RO users
                            if( $is_ro_user ) {
                                if( in_array($FILTER_TYPE, ['REARP', 'RVCOM', 'RECOM']))
                                    $str .= '<th>RO Review Status</th>';
                                
                                if( in_array($FILTER_TYPE, ['REARP', 'RVCOM', 'RECOM', 'PCDR']))
                                    $str .= '<th>RO Comment</th>';
                                
                                if( in_array($FILTER_TYPE, ['REARP', 'RECOM']))
                                    $str .= '<th>RO Review Date</th>';
                            }

                            if( in_array($FILTER_TYPE, ['RVAU', 'COM', 'RECOM', 'RVCOM']) && 
                                check_evidence_upload_strict() && !$is_ro_user )
                                $str .= '<th>Evidence</th>';

                        $str .= '</tr>' . "\n";

                        if( is_array($cQueDetails -> annex) && 
                            sizeof($cQueDetails -> annex) > 0 )
                        {
                            foreach($cQueDetails -> annex as $cAnnexId => $cAnsAnnex)
                            {
                                $compulsaryClassStyle = (in_array($FILTER_TYPE, ['RVAU', 'COM', 'RECOM'])) ? 'text-danger' : '';
                                
                                $cAnsAnnex -> audit_comment = trim_str(urldecode_data($cAnsAnnex -> audit_comment));
                                $cAnsAnnex -> audit_reviewer_comment = trim_str(urldecode_data($cAnsAnnex -> audit_reviewer_comment));
                                $cAnsAnnex -> audit_commpliance = trim_str(urldecode_data($cAnsAnnex -> audit_commpliance));
                                $cAnsAnnex -> compliance_reviewer_comment = trim_str(urldecode_data($cAnsAnnex -> compliance_reviewer_comment));

                                if( in_array($FILTER_TYPE, ['RVAU', 'RVCOM']) && 
                                    ($cAnsAnnex -> audit_status_id != 3 || $cAnsAnnex -> compliance_status_id != 3) )
                                    $compulsaryClassStyle = '';
                                else if(in_array($FILTER_TYPE, ['COM']) && !empty($cAnsAnnex -> audit_commpliance))
                                    $compulsaryClassStyle = '';

                                if( empty($cAnsAnnex -> audit_commpliance) )
                                    $res['com_pending']++;

                                $str .= '<tr class="'. $compulsaryClassStyle .'">' . "\n";

                                    $jsonConvertBool = false;

                                    try 
                                    {
                                        $cJsonData = json_decode($cAnsAnnex -> answer_given);

                                        if(is_object($cJsonData))
                                        {
                                            $jsonConvertBool = true;

                                            if(!$checkCF)
                                            {
                                                foreach($cJsonData as $cKey => $cJsonDetails)
                                                {
                                                    if(!in_array($cKey, ['br', 'cr', 'rt']))
                                                        $str .= '<td>'. trim_str($cJsonDetails) .'</td>';
                                                }
                                            }
                                            else
                                            {
                                                $str .= '<td>'. generate_cf_markup_row($cAnsAnnex) .'</td>';
                                            }
                                        }   
                                        
                                    } catch (Exception $e) { }

                                    if(!$jsonConvertBool)
                                        $str .= '<td colspan="'. sizeof($dataArray['annex_tab_cols'][ $cQueDetails -> annexure_id ] -> annex_cols) .'" class="text-danger">'. $data['noti']::getNoti('noDataFound') .'</td>';

                                    if( in_array($FILTER_TYPE, ['CRPWC', 'RVCOM', 'PCDR']) )
                                        $str .= '<td>'. (!empty($cAnsAnnex -> audit_commpliance) ? $cAnsAnnex -> audit_commpliance : '-') .'</td>';

                                    $str .= generate_report_risk_columns($data, $FILTER_TYPE, $cAnsAnnex);

                                    // For non-RO users
                                    if( !$is_ro_user ) {
                                        $str .= generate_report_td_markup($FILTER_TYPE, $cAnsAnnex, $reviewActionArray, $colSelect, 'annex');

                                        if( in_array($FILTER_TYPE, ['RECOM', 'PCDR'] ))
                                            $str .= '<td>'. (trim_str($cAnsAnnex -> compliance_reviewer_comment) != '' ? trim_str($cAnsAnnex -> compliance_reviewer_comment) : '') .'</td>';
                                    }
                                    
                                    // For RO users
                                    if( $is_ro_user ) {
                                        $str .= generate_report_td_markup($FILTER_TYPE, $cAnsAnnex, $reviewActionArray, $colSelect, 'annex');
                                    }

                                    $activateEvidence = 0;

                                    // FOR EVIDENCE
                                    if( in_array($FILTER_TYPE, ['RVAU', 'RVCOM','COM','RECOM']) && check_evidence_upload_strict() && !$is_ro_user )
                                    {
                                        $str .= '<td>' . "\n";

                                        if( in_array($FILTER_TYPE, ['RVAU']) )
                                        {
                                            $str .= '<div class="mt-2">' . "\n";
                                                if( $FILTER_TYPE == 'RVAU' )
                                                    $str .= generate_evidence_checkbox_markup($cAnsAnnex, [1], 1, 2);
                                                $str .= generate_evidence_checkbox_markup($cAnsAnnex, [1,2], 2, 2);
                                            $str .= '</div>' . "\n";
                                        }

                                        $mt = 0;

                                        $str .= '<div class="p-2">' . "\n";

                                            if( isset($cAnsAnnex -> audit_evidence) ):
                                                if( isset($cAnsAnnex -> audit_evidence) && 
                                                    is_array($cAnsAnnex -> audit_evidence) && 
                                                    sizeof($cAnsAnnex -> audit_evidence) > 0 )
                                                {
                                                    $str .= '<p class="font-medium font-sm mb-1 text-secondary">Audit Evidences &raquo;</p>' . "\n";
                                                    $str .= display_evidence_markup($cAnsAnnex, 1);
                                                    $mt = 2;
                                                }         
                                            endif;

                                            $activateEvidence = in_array($cAnsAnnex -> compliance_compulsary_ev_upload, [1,2]);
                                                
                                            if( in_array($FILTER_TYPE, ['COM','RECOM']) && 
                                                check_evidence_upload_strict() && $activateEvidence )
                                            {
                                                $str .= '<p class="font-medium font-sm mb-1 mt-'. $mt .' text-secondary">Compliance Evidences &raquo;</p>' . "\n";

                                                $activatEviBtn = true;
                                                $annexEviMarkup = '';
                                                
                                                if( is_object($cAnsAnnex) && 
                                                    isset($cAnsAnnex -> compliance_evidence) && 
                                                    is_array($cAnsAnnex -> compliance_evidence) && 
                                                    sizeof($cAnsAnnex -> compliance_evidence) > 0)
                                                {
                                                    $activatEviBtn = false;
                                                    $str .= display_evidence_markup($cAnsAnnex, 2);
                                                }
                                                
                                                $str .= '<button class="annex-evidence-upload-btn compliance-evi-btn" '. (!$activatEviBtn ? 'style="display:none"' : '') .''. view_tooltip('Upload File') .'>Evidence</button>
                                                <div class="annex-evidence-upload-container" data-annexid="'. encrypt_ex_data($cAnsAnnex -> id) .'" data-ansid="'. encrypt_ex_data($cAnsAnnex -> answer_id) .'">'. $annexEviMarkup .'</div>' . "\n";

                                                $str .= '<p class="font-sm text-danger"><b>Note:</b> Compulsary evidence upload from ('. (($cAnsAnnex -> compliance_compulsary_ev_upload == 1) ? 'Reviewer' : 'Auditor') .')</p>';
                                            }

                                        $str .= '</div>' . "\n";
                                        
                                        $str .= '</td>' . "\n";
                                    }

                                $str .= '</tr>' . "\n";
                            }
                        }
                        else { }

                    $str .= '</table>' . "\n";

                    $str .= '</td></td>' . "\n";
                }
            }
        }
        
        return $str;
    }
}


if(!function_exists('generate_table_markup'))
{ 
    //function for generate table markup
    function generate_table_markup($data, $dataArray, $filterType = 1, $branchHeader = 0, $assesArray = [])
    {
        $is_ro_user = false;
        if(isset($_SESSION['emp_type']) && $_SESSION['emp_type'] == 16) {
            $is_ro_user = true;
        }
        // SET COLSPAN FIRST
        if(in_array($filterType, ['REARP']))
            $colspan = 9;
        elseif(in_array($filterType, ['CRPWC']))
            $colspan = 8;
        elseif(in_array($filterType, ['PCDR']))
            $colspan = 6;
        elseif(in_array($filterType, ['COMRP']))     
            $colspan = 4;
        else            
            $colspan = 7;

        $mrk_str = '';

        if($branchHeader)
        {
            // $mrk_str .= '<div class="card-header pb-1 font-medium text-uppercase mb-0">ASSESMENT DETAILS: </div>' . "\n";

            $mrk_str .= '<h4 class="text-center"><span class="font-medium mb-1">Branch: <u>'. $data['data']['audit_unit_data'][$assesArray -> audit_unit_id] -> combined_name .'</u></span></h4>' . "\n";

            // assesment period
            $mrk_str .= '<p class="text-center mb-2">Period: '. $assesArray -> assesment_period_from . ' to ' . $assesArray -> assesment_period_to . '</p>' . "\n";

            // $mrk_str .= '</div>' . "\n";
        }

        $mrk_str .= '<table class="table audit-report-table table-bordered mb-4">' . "\n";

        foreach($dataArray['ans_data'] as $cMenuId => $cMenuDetails)
        {            
            $mrk_str .= '<tr>
                <th colspan="'. $colspan .'" class="text-primary"><u>MENU: ' . "\n";
                $mrk_str .= string_operations( (is_object($cMenuDetails['menu']) ? $cMenuDetails['menu'] -> name : ERROR_VARS['notFound']), 'upper' ) . '</u>';
            $mrk_str .= '</th><tr>' . "\n";

            // check for carry 22.08.2024
            if(check_carry_forward_strict() && $cMenuId == CARRY_FORWARD_ARRAY['id'])
            {
                if( isset($cMenuDetails['ans_data']) && 
                    isset($cMenuDetails['ans_data'] -> annex) &&
                    is_array($cMenuDetails['ans_data'] -> annex) && 
                    sizeof($cMenuDetails['ans_data'] -> annex) > 0 )
                {
                    // has data has carry forward points // general question function call
                    $mrk_str .= generate_display_questions_markup($data, [
                        CARRY_FORWARD_ARRAY['id'] => [
                            'header' => (object) [
                                'id' => CARRY_FORWARD_ARRAY['id'] . '_header',
                                'name' => CARRY_FORWARD_ARRAY['title']
                            ],
                            'questions' => [
                                CARRY_FORWARD_ARRAY['id'] => $cMenuDetails['ans_data']
                            ]
                        ]
                    ], $dataArray, $colspan, $filterType);
                }
                else
                {
                    // has no cf data found
                    $mrk_str .= '<tr>
                        <td colspan="'. $colspan .'" class="text-danger">' . "\n";
                        $mrk_str .= 'Error: Carry forward points data not found';
                    $mrk_str .= '</td></tr>' . "\n";
                }
                // print_r($cMenuDetails);
                // exit;
            }
            else
            {
                foreach($cMenuDetails['category'] as $cCatId => $cCatDetails)
                {
                    $mrk_str .= '<tr>
                        <td colspan="'. $colspan .'" class="font-medium lead">' . "\n";
                        $mrk_str .= '<u>' . string_operations(('Category: ' . ( is_object($cCatDetails) ? $cCatDetails -> name : ERROR_VARS['notFound'])), 'upper' ) . '</u>';
                    $mrk_str .= '' . "\n";

                    if( is_object($cCatDetails) && isset($cCatDetails -> dump) )
                    {
                        foreach($cCatDetails -> dump as $cDumpId => $cDumpQuestions)
                        {
                            $cAccDetailsBool = false;

                            if(array_key_exists($cDumpId, $dataArray[ $cCatDetails -> dump_table ])) 
                                $cAccDetailsBool = true;

                            $mrk_str .= '<tr><td colspan="'. $colspan .'" style="background-color: #f6f6f6">' . "\n";
                                $mrk_str .= '<table class="table table-sm table-bordered mb-0">' . "\n";

                                    if($cAccDetailsBool)
                                    {
                                        $cAccDetailsBool = $dataArray[ $cCatDetails -> dump_table ][ $cDumpId ];

                                        $mrk_str .= '<tr><th colspan="3" class="text-primary px-3">Account Details: '. trim_str($cAccDetailsBool -> account_holder_name) .'</th><tr>';

                                        // common data both table
                                        $mrk_str .= '<tr>
                                            <td class="px-3">
                                                <span class="font-medium">Branch Name:</span> '. string_operations(($cAccDetailsBool -> audit_unit_name != 'NA' ? $cAccDetailsBool -> audit_unit_name : ERROR_VARS['notFound']), 'upper') .' ( BR. CODE: '. ($cAccDetailsBool -> audit_unit_code != 'NA' ? $cAccDetailsBool -> audit_unit_code : ERROR_VARS['notFound']) .' )
                                            </td>

                                            <td class="px-3">
                                                <span class="font-medium">Scheme Code:</span> '. ($cAccDetailsBool -> scheme_code != 'NA' ? $cAccDetailsBool -> scheme_code : ERROR_VARS['notFound']) .'
                                            </td>

                                            <td class="px-3">
                                                <span class="font-medium">Scheme Name:</span> '. string_operations(($cAccDetailsBool -> scheme_name != 'NA' ? $cAccDetailsBool -> scheme_name : ERROR_VARS['notFound']), 'upper') .'
                                            </td>
                                        </tr>';

                                        $mrk_str .= '<tr>
                                            <td class="px-3">
                                                <span class="font-medium">Account Number:</span> '. trim_str($cAccDetailsBool -> account_no) .'
                                            </td>

                                            <td class="px-3">
                                                <span class="font-medium">UCIC:</span> '. trim_str($cAccDetailsBool -> ucic) .'
                                            </td>

                                            <td class="px-3">
                                                <span class="font-medium">Account Open Date:</span> '. trim_str($cAccDetailsBool -> account_opening_date) .'
                                            </td>
                                        </tr>';

                                        if($cCatDetails -> dump_table == 'dump_advances')
                                        {
                                            $mrk_str .= '<tr>
                                                <td class="px-3">
                                                    <span class="font-medium">Intrest Rate:</span> '. get_decimal(trim_str($cAccDetailsBool -> intrest_rate), 2) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Sanction Amount:</span> '. get_decimal(trim_str($cAccDetailsBool -> sanction_amount), 2) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Outstanding Balance:</span> '. get_decimal(trim_str($cAccDetailsBool -> outstanding_balance), 2) .'
                                                </td>
                                            </tr>';

                                            $mrk_str .= '<tr>
                                                <td class="px-3">
                                                    <span class="font-medium">Customer Type:</span> '. trim_str($cAccDetailsBool -> customer_type) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Due Date:</span> '. trim_str($cAccDetailsBool -> due_date) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Balance As On:</span> '. trim_str($cAccDetailsBool -> balance_date) .'
                                                </td>
                                            </tr>';

                                            $mrk_str .= '<tr>
                                                <td class="px-3">
                                                    <span class="font-medium">NPA Status:</span> '. trim_str($cAccDetailsBool -> npa_status) .'
                                                </td>';

                                                // check renewal date
                                                $renewalDate = trim_str($cAccDetailsBool -> renewal_date);

                                                if(!empty($renewalDate))
                                                    $mrk_str .= '<td class="px-3">
                                                        <span class="font-medium">Renewal Date:</span> '. $renewalDate .'
                                                    </td>';

                                                $mrk_str .= '<td class="px-3" '. (empty($renewalDate) ? 'colspan="2"' : '') .'>
                                                    <span class="font-medium">Account Status:</span> '. trim_str($cAccDetailsBool -> account_status) .'
                                                </td>
                                            </tr>';
                                        }
                                        else
                                        {
                                            // for deposits
                                            $mrk_str .= '<tr>
                                                <td class="px-3">
                                                    <span class="font-medium">Intrest Rate:</span> '. get_decimal(trim_str($cAccDetailsBool -> intrest_rate), 2) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Principal Amount:</span> '. get_decimal(trim_str($cAccDetailsBool -> principal_amount), 2) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Balance Amount:</span> '. get_decimal(trim_str($cAccDetailsBool -> balance), 2) .'
                                                </td>
                                            </tr>';

                                            $mrk_str .= '<tr>
                                                <td class="px-3">
                                                    <span class="font-medium">Balance Date:</span> '. trim_str($cAccDetailsBool -> balance_date) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Maturity Date:</span> '. trim_str($cAccDetailsBool -> maturity_date) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Maturity Amount:</span> '. trim_str($cAccDetailsBool -> maturity_amount) .'
                                                </td>
                                            <tr>';

                                            $mrk_str .= '<tr>
                                                <td class="px-3">
                                                    <span class="font-medium">Close Date:</span> '. trim_str($cAccDetailsBool -> close_date) .'
                                                </td>

                                                <td class="px-3">
                                                    <span class="font-medium">Customer Type:</span> '. trim_str($cAccDetailsBool -> customer_type) .'
                                                </td>
                                                
                                                <td class="px-3">
                                                    <span class="font-medium">Account Status:</span> '. trim_str($cAccDetailsBool -> account_status) .'
                                                </td>
                                                
                                             etxek';
                                        }
                                    }
                                    else
                                    {
                                        $mrk_str .= '<tr><th colspan="3">Account Details:</th><tr>';
                                        $mrk_str .= '<tr><td colspan="3">Error: Account Details Not Found!<\/td></tr>';
                                    }

                                $mrk_str .=  '</table>';
                            $mrk_str .=  'NonNullable' . "\n";

                            //general question
                            $mrk_str .= generate_display_questions_markup($data, $cDumpQuestions, $dataArray, $colspan, $filterType);
                        }
                    }
                    else
                    {
                        //general question
                        $mrk_str .= generate_display_questions_markup($data, $cCatDetails -> questions, $dataArray, $colspan, $filterType);
                    }
                }
            }
        }

        $mrk_str .= '</table>' . "\n";

        return $mrk_str;
    }
}

if(!function_exists('accept_review_audit'))
{ 
    //function for accept review
    function accept_review_audit($con, $data_array, $empty_action = 0, $filter_type = 1)
    {
        $return_res = false;

        if( /*$filter_type == 1 && */is_array($data_array['ans_ids']) && sizeof($data_array['ans_ids']) > 0)
            $return_res = true;

        if($return_res)
        {
            try 
            {
                $con -> autocommit(FALSE); //turn on transactions    

                $sql = "UPDATE answers_data SET ";

                if($empty_action == 1) //remove accept reject
                    $sql .= "reviewer_accept_reject_compliance = ''";
                elseif($empty_action == 2) //remove accept reject & comment
                    $sql .= "reviewer_accept_reject_compliance = '', reviewer_comment = ''";
                else
                    $sql .= "reviewer_accept_reject_compliance = 1";

                //append where
                $sql .= " WHERE period_of_assesment_id = ? AND answer_id IN (". implode(',', $data_array['ans_ids']) .")";

                if(!in_array($empty_action, [1, 2]))
                    $sql .= " AND (reviewer_accept_reject_compliance IS NULL OR reviewer_accept_reject_compliance = 0 OR reviewer_accept_reject_compliance = 1)";

                //update main questions
                $stmt = $con -> prepare($sql);
                $stmt -> bind_param("i", $data_array['assessment_details']['details_of_audit_id']);
                $stmt -> execute();

                if( $filter_type == 3 && is_array($data_array['ans_annex_ids']) && sizeof($data_array['ans_annex_ids']) > 0)
                {
                    $sql = "UPDATE answers_data_annexure SET ";

                    if($empty_action == 1) //remove accept reject
                        $sql .= "reviewer_accept_reject_compliance = ''";
                    elseif($empty_action == 2) //remove accept reject & comment
                        $sql .= "reviewer_accept_reject_compliance = '', reviewer_comment = ''";
                    else
                        $sql .= "reviewer_accept_reject_compliance = 1";

                    //append where
                    $sql .= " WHERE period_of_assesment_id = ? AND auto_id IN (". implode(',', $data_array['ans_annex_ids']) .")";

                    if(!in_array($empty_action, [1, 2]))
                        $sql .= " AND (reviewer_accept_reject_compliance IS NULL OR reviewer_accept_reject_compliance = 0 OR reviewer_accept_reject_compliance = 1)";
                        
                    $stmt = $con -> prepare($sql);
                    $stmt -> bind_param("i", $data_array['assessment_details']['details_of_audit_id']);
                    $stmt -> execute();
                }

                // $stmt -> close();
                $con -> autocommit(TRUE); //turn off transactions + commit queued queries
            }
            catch(Exception $e) {
                $con -> rollback(); //remove all queries from queue if error (undo)
                // throw $e;
                // echo $e;

                $return_res = false;
            }
        }

        return $return_res;
    }
}

if(!function_exists('save_assesment_message'))
{
    function save_assesment_message($this_obj, $empId, $requestData, $type = 'com', $changeBatchKey = false)
    {
        $res_array = ['msg' => 'somethingWrong', 'res' => 'err'];

        $requestData = json_decode($requestData);

        $checkActionVal = false;
        $updateArray = [];

        if( $type == 'com' && !isset( $requestData -> compliance ) )
            $res_array['msg'] = 'validCompliance';

        elseif( $type == 'com' && isset( $requestData -> compliance ) )
        {
            $updateArray = [ 'audit_commpliance' => $requestData -> compliance, 'compliance_emp_id' => $empId ];

            if($changeBatchKey)
                $updateArray['batch_key'] = $this_obj -> assesmentData -> batch_key;

            $checkActionVal = true;
        }

        elseif( ( $type == 'aud_rew' || $type == 'com_rew' ) && !isset( $requestData -> comment ) )
            $res_array['msg'] = 'validReviewComment';

        elseif( ( $type == 'aud_rew' || $type == 'com_rew' ) && isset( $requestData -> comment ) )
        {
            $updateArray[ ( ( $type == 'aud_rew' ) ? 'audit_reviewer_comment' : 'compliance_reviewer_comment' )] = $requestData -> comment;
            $updateArray[ ( ( $type == 'aud_rew' ) ? 'audit_reviewer_emp_id' : 'compliance_reviewer_emp_id' )] = $empId;
            $checkActionVal = true;
        }

        // RO STATUS UPDATE
        elseif( ( $type == 'ro_audit' || $type == 'ro_compliance' ) && isset( $requestData -> status ) )
        {
            $field = ($type == 'ro_audit') ? 'ro_audit_status_id' : 'ro_compliance_status_id';
            $updateArray[$field] = $requestData -> status;
            $updateArray['ro_review_emp_id'] = $empId;
            $updateArray['ro_review_date'] = date('Y-m-d H:i:s');
            $checkActionVal = true;
        }

        // RO COMMENT UPDATE
        elseif( ( $type == 'ro_audit_comment' || $type == 'ro_compliance_comment' ) && isset( $requestData -> comment ) )
        {
            $updateArray['ro_reviewer_comment'] = $requestData -> comment;
            $updateArray['ro_review_emp_id'] = $empId;
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
                $model = $this_obj -> model('AnswerDataModel');
            }
            else
            {
                $model = $this_obj -> model('AnswerDataAnnexureModel');
            }

            $whereData = [
                'where' => 'id = :id AND assesment_id = :assesment_id',
                'params' => [ 
                    'id' =>  decrypt_ex_data($requestData -> ans_id),
                    'assesment_id' => $this_obj -> assesmentData -> id
                ]
            ];

            $result = $model::update(
                $model -> getTableName(), $updateArray, $whereData
            );

            if($result !== false)
            {
                if($type == 'com')
                    $res_array['msg'] = 'auditComplianceSuccess';
                elseif( $type == 'aud_rew' || $type == 'com_rew' )
                    $res_array['msg'] = 'reviewCommentSuccess';
                elseif( $type == 'ro_audit_comment' || $type == 'ro_compliance_comment' )
                    $res_array['msg'] = 'reviewCommentSuccess';
                elseif( $type == 'ro_audit' || $type == 'ro_compliance' )
                    $res_array['msg'] = 'reviewActionSavedSuccess';

                $res_array['res'] = "success";
            }
            else
            {
                $res_array['msg'] = 'errorSaving';
                $res_array['res'] = 'err';
            }
        }

        return $res_array;
    }
}


if(!function_exists('check_menu_data_has_carry_forward'))
{
    function check_menu_data_has_carry_forward($menuIdStr) {
        
        $res = [ 'checkCF' => false, 'menuIds' => [] ];

        if(empty($menuIdStr))
            return $res;

        $checkCFDef = check_carry_forward_strict();

        $cMenuIds = explode(',', $menuIdStr);
        $cFilterMenuIds = [];

        if( is_array($cMenuIds) && 
            sizeof($cMenuIds) > 0)
        {
            // filter data
            foreach($cMenuIds as $cMenuId) {

                if( $checkCFDef && CARRY_FORWARD_ARRAY['id'] == $cMenuId )
                    $res['checkCF'] = true;
                else
                    $res['menuIds'][] = $cMenuId;
            }

            $res['menuIds'] = array_unique($res['menuIds']);
            sort($res['menuIds']);
        }

        return $res;
    }
}

?>