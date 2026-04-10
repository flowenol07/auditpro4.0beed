<?php

namespace Controllers\Audit;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

class AuditAssessment extends Controller  {

    public $me = null, $request, $data, $assesId, $cfDataInsert;
    public $auditAssesmentModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> auditAssesmentModel = $this -> model('AuditAssesmentModel');
    }

    private function checkMultiLevelControl($cGenKey, $multiLevelControlData, $section_type_id = 1) {

        // check key exists or not
        $cGenKeyStatus = array_key_exists($cGenKey, $multiLevelControlData) ? true : false;

        // check multi control data and assign
        if( $cGenKeyStatus && 
            !empty($multiLevelControlData[ $cGenKey ] -> menu_ids) &&
            !empty($multiLevelControlData[ $cGenKey ] -> cat_ids) &&
            !empty($multiLevelControlData[ $cGenKey ] -> header_ids) &&
            !empty($multiLevelControlData[ $cGenKey ] -> question_ids)
        )
        {
            // we don't check schemes, because of section HO departments has no accounts
            if( $section_type_id == 1 && 
                ( empty($multiLevelControlData[ $cGenKey ] -> advances_scheme_ids) ||
                  empty($multiLevelControlData[ $cGenKey ] -> deposits_scheme_ids)) )
                    $cGenKeyStatus = false;

            $this -> data['db_data']['menu_ids'] = $multiLevelControlData[ $cGenKey ] -> menu_ids;
            $this -> data['db_data']['cat_ids'] = $multiLevelControlData[ $cGenKey ] -> cat_ids;
            $this -> data['db_data']['header_ids'] = $multiLevelControlData[ $cGenKey ] -> header_ids;
            $this -> data['db_data']['question_ids'] = $multiLevelControlData[ $cGenKey ] -> question_ids;
            $this -> data['db_data']['advances_scheme_ids'] = $multiLevelControlData[ $cGenKey ] -> advances_scheme_ids;
            $this -> data['db_data']['deposits_scheme_ids'] = $multiLevelControlData[ $cGenKey ] -> deposits_scheme_ids;
        }
        else
            $cGenKeyStatus = false;

        return $cGenKeyStatus;
    }

    public function index($getRequest)
    {
        $empDetails = Session::get('emp_details');

        if(!is_array($empDetails) || $empDetails['emp_type'] != 2)
        {
            Except::exc_access_restrict( );
			exit;
        }

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ]
        ];

        if( !isset($getRequest['unit']) || empty($getRequest['unit']) || 
            !isset($getRequest['fy']) || empty($getRequest['fy']) )
        {
            Except::exc_404( Notifications::getNoti('auditAuthorityNotFound') );
            exit;
        }

        //find current audit unit
        $auditUnit = decrypt_ex_data($getRequest['unit']);

        $model = $this -> model('AuditUnitModel');

        $this -> data['db_audit_unit'] = $model -> getSingleAuditUnit([
            'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ 'id' => $auditUnit ]
        ]);

        //find fy year
        $fyYear = decrypt_ex_data($getRequest['fy']);

        $model = $this -> model('YearModel');

        $this -> data['db_fy_details'] = $model -> getSingleYear([
            'where' => 'year = :year AND deleted_at IS NULL',
            'params' => [ 'year' => $fyYear ]
        ]);

        $fyRemainMonths = 12;
        $pedingAssesments = false;

        if(is_object($this -> data['db_fy_details']) && is_object($this -> data['db_audit_unit']))
        {
            //find all assesment in GET['fy] year
            $this -> data['db_fy_details'] -> assesment_period_from = $this -> data['db_fy_details'] -> year . '-04-01';
            $this -> data['db_fy_details'] -> assesment_period_to = ($this -> data['db_fy_details'] -> year + 1) . '-03-31';

            $fyAssesmentData = $this -> auditAssesmentModel -> getAllAuditAssesment([
                'where' => 'year_id = :year_id AND audit_unit_id = :audit_unit_id AND assesment_period_from >= :assesment_period_from AND assesment_period_to <= :assesment_period_to AND deleted_at IS NULL',
                'params' => [ 
                    'year_id' => $this -> data['db_fy_details'] -> id,
                    'audit_unit_id' => $this -> data['db_audit_unit'] -> id,
                    'assesment_period_from' => $this -> data['db_fy_details'] -> assesment_period_from, 
                    'assesment_period_to' => $this -> data['db_fy_details'] -> assesment_period_to 
                ]
            ]);

            if(is_array($fyAssesmentData) && sizeof($fyAssesmentData) > 0)
            {
                //check pending assesments and frequency wise check
                foreach($fyAssesmentData as $cAssesmentDetails)
                {
                    if($cAssesmentDetails -> audit_status_id == 1) {
                        $pedingAssesments = true;
                        // break;   // disable for check audits
                    }

                    // check last audit end date
                    if($this -> data['db_fy_details'] -> assesment_period_to == $cAssesmentDetails -> assesment_period_to)
                        $fyRemainMonths = 0;

                    if($fyRemainMonths > 0)
                        $fyRemainMonths -= $cAssesmentDetails -> frequency;
                }
            }
        }

        if(is_object($this -> data['db_fy_details']) && is_object($this -> data['db_audit_unit']))
        {
            // Get the timestamp for the first day of the next month
            $auditStartDate = strtotime( 'first day of next month', strtotime($this -> data['db_audit_unit'] -> last_audit_date) );
            $auditStartDate = date('Y-m-d', $auditStartDate);

            // Add the specified number of months // Get the last day of the month
            $auditEndDate = strtotime("+" . ($this -> data['db_audit_unit'] -> frequency - 1) . " months", strtotime($auditStartDate));
            $auditEndDate = date('Y-m-t', $auditEndDate);      

            $fyStr = $this -> data['db_fy_details'] -> year . ' - ' . ($this -> data['db_fy_details'] -> year + 1);
            
            // Audit due date
            $auditDueDate = date( $GLOBALS['dateSupportArray'][1] , strtotime("+" . AUDIT_DUE_ARRAY[1] . " days", strtotime(date($GLOBALS['dateSupportArray'][1])) ));
            // $auditDueDate = date('Y-m-t', $auditDueDate);

            //add next audit details
            $this -> data['db_data'] = [
                'audit_type_id' => 1, // risk based audit
                'year_id' => $this -> data['db_fy_details'] -> id,
                'audit_unit_id' => $this -> data['db_audit_unit'] -> id,
                'section_type_id' => $this -> data['db_audit_unit'] -> section_type_id,
                'name' => $this -> data['db_audit_unit'] -> name,
                'frequency' => $this -> data['db_audit_unit'] -> frequency,
                'audit_head_id' => Session::get('emp_id'),
                'branch_head_id' => $this -> data['db_audit_unit'] -> branch_head_id,
                'branch_subhead_id' => $this -> data['db_audit_unit'] -> branch_subhead_id,
                'multi_compliance_ids' => $this -> data['db_audit_unit'] -> multi_compliance_ids,
                'last_audit_date' => $this -> data['db_audit_unit'] -> last_audit_date,
                'assesment_period_from' => $auditStartDate,
                'assesment_period_to' => $auditEndDate,
                'audit_start_date' => date($GLOBALS['dateSupportArray'][1]),
                'audit_due_date' => $auditDueDate,
                'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                'audit_review_reject_limit' => AUDIT_STATUS_ARRAY['review_reject_limit']['audit'],
                'compliance_review_reject_limit' => AUDIT_STATUS_ARRAY['review_reject_limit']['compliance'],
                'menu_ids' => NULL,
                'cat_ids' => NULL,
                'header_ids' => NULL,
                'question_ids' => NULL,
                'advances_scheme_ids' => NULL,
                'deposits_scheme_ids' => NULL,
                'error' => null
            ];

            //check audit date between
            if (  !(strtotime($this -> data['db_data']['assesment_period_from']) >= 
                    strtotime($this -> data['db_fy_details'] -> assesment_period_from) && 
                    strtotime($this -> data['db_data']['assesment_period_to']) <= 
                    strtotime($this -> data['db_fy_details']->assesment_period_to)) )
                $this -> data['db_data']['error'] = '<span class="font-bold">Note:</span> The audit assessment date range from <span class="font-bold">' . $this -> data['db_data']['assesment_period_from'] . ' - ' . $this -> data['db_data']['assesment_period_to'] . '</span> is not within the current year range of ' . $fyStr;

            //if no errors
            if($pedingAssesments)
                $this -> data['db_data']['error'] = 'pendingAudit';

            //check fy remain months audit using frequecy
            if($fyRemainMonths > 0 && $this -> data['db_data']['frequency'] > $fyRemainMonths)
                $this -> data['db_data']['error'] = '<span class="font-bold">Note:</span> Your current audit frequency is every <span class="font-medium">'. $this -> data['db_data']['frequency'] .' Months</span>. There are <span class="font-medium">'. $fyRemainMonths .' Months</span> remaining for the current audit cycle in the F.Y. ' . $fyStr . '. Please consider changing your audit frequency.';
            elseif($fyRemainMonths <= 0)
                $this -> data['db_data']['error'] = 'All audits have been completed in the current F.Y. ' . $fyStr . '.';

            //CHECK YEAR WISE RISK HERE AFTER TANVIR COMPLETE 12-03-2024

            //find multi level control data
            $model = $this -> model('RiskCategoryWeightModel');

            // get total risk type count
            $totalRiskCategory = get_all_data_query_builder(1, $model, 'risk_category_master', [], 'sql', "SELECT COUNT(*) total_risk_category FROM risk_category_master");
            $totalRiskCategory = is_object($totalRiskCategory) ? $totalRiskCategory -> total_risk_category : 0;

            if(!$totalRiskCategory > 0)
                $this -> data['db_data']['error'] = Notifications::getNoti('riskCategoryNoData');
            
            $totalRiskCategoryWeights = 0;
            $totalRiskMatrix = 0;

            if( $totalRiskCategory > 0 )
            {
                $totalRiskCategoryWeights = get_all_data_query_builder(1, $model, 'risk_category_weights', [
                    'where' => 'year_id = :year_id AND is_active = 1 AND deleted_at IS NULL AND risk_category_id != 10',
                    'params' => [ 'year_id' => $this -> data['db_data']['year_id'] ]
                ], 'sql', "SELECT COUNT(*) total_risk_weights FROM risk_category_weights");

                $totalRiskCategoryWeights = is_object($totalRiskCategoryWeights) ? $totalRiskCategoryWeights -> total_risk_weights : 0;

                $totalRiskMatrix = get_all_data_query_builder(1, $model, 'risk_matrix', [
                    'where' => 'year_id = :year_id AND deleted_at IS NULL',
                    'params' => [ 'year_id' => $this -> data['db_data']['year_id'] ]
                ], 'sql', "SELECT COUNT(*) total_risk_matrix FROM risk_matrix");

                $totalRiskMatrix = is_object($totalRiskMatrix) ? $totalRiskMatrix -> total_risk_matrix : 0;
            }

            if( !$totalRiskCategoryWeights > 0 || ( ($totalRiskCategory - 1) != $totalRiskCategoryWeights ) )
                $this -> data['db_data']['error'] = Notifications::getNoti('riskCategoryWeightNoData');

            if( !$totalRiskMatrix > 0 )
                $this -> data['db_data']['error'] = Notifications::getNoti('riskMatrixNoData');

            if( empty($this -> data['db_data']['error']) ) {

                //find multi level control data
                $model = $this -> model('MultiLevelControlMaster');

                $multiLevelControlData = $model -> getAllMultiLevelControls([
                    'where' => 'year_id = :year_id AND audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
                    'params' => [ 'year_id' => $this -> data['db_fy_details'] -> id, 'audit_unit_id' => $this -> data['db_audit_unit'] -> id ]
                ]);

                if(is_array($multiLevelControlData) && sizeof($multiLevelControlData) > 0)
                {
                    $tempMultiLevelControlData = $multiLevelControlData;
                    $multiLevelControlData = [];

                    foreach($tempMultiLevelControlData as $cData)
                    {
                        $cGenKey = $cData -> start_month_year . '_' . $cData -> end_month_year;

                        $multiLevelControlData[ $cGenKey ] = $cData;
                    }

                    // echo '<pre>';
                    // print_r($multiLevelControlData);

                    //unset vars 
                    unset($tempMultiLevelControlData, $cData);

                    //check current year data exists or not
                    $cGenKey = date('Y-m', strtotime($this -> data['db_data']['assesment_period_from'])) . '_' . date('Y-m', strtotime($this -> data['db_data']['assesment_period_to']));

                    //check key exists or not
                    $cGenKeyStatus = $this -> checkMultiLevelControl($cGenKey, $multiLevelControlData, $this -> data['db_data']['section_type_id']);

                    if(!$cGenKeyStatus)
                    {
                        //for safer side get full year
                        $cGenKey = $this -> data['db_fy_details'] -> year . '-04_' . ($this -> data['db_fy_details'] -> year + 1) . '-03';
                        
                        //check key exists or not
                        $cGenKeyStatus = $this -> checkMultiLevelControl($cGenKey, $multiLevelControlData, $this -> data['db_data']['section_type_id']);
                    }

                    //check multi control data and assign
                    if( !$cGenKeyStatus ) //multi level control data missing
                        $this -> data['db_data']['error'] = 'multiLevelControlNoData';
                }
                else //multi level control data missing
                    $this -> data['db_data']['error'] = 'multiLevelControlNoData';
            }
        }
        
        //if db data not found
        if(!isset($this -> data['db_data'])) 
        {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        $this -> me -> pageHeading = 'Start New Audit Assesment';
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/?unit=' . encrypt_ex_data($auditUnit) . '&fy=' . encrypt_ex_data($fyYear) );

        // 10.09.2024 // update
        if( check_carry_forward_strict() )
        {
            // call carry forward function for demo purpose
            $this -> cfDataInsert = $this -> checkCarryForward();
            // print_r($this -> cfDataInsert);
            // exit;
        }

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return $this -> view( $this -> me -> viewDir . 'form', [
                'me' => $this -> me,
                'data' => $this -> data,
                'request' => $this -> request
            ] );

        });

        //post method after form submit
        $this -> request::method("POST", function() {
            
            if( !empty($this -> data['db_data']['error']) )
            {
                Except::exc_404( Notifications::getNoti('somethingWrong') );
                exit;
            }

            //remove unwanted keys
            unset(
                $this -> data['db_data']['name'], 
                $this -> data['db_data']['section_type_id'], 
                $this -> data['db_data']['last_audit_date'],
                $this -> data['db_data']['error']
            );

            // add audit batch key // 06-02-2024
            $this -> data['db_data']['batch_key'] = generate_batch_key();
            
            // insert in database
            $result = $this -> auditAssesmentModel::insert(
                $this -> auditAssesmentModel -> getTableName(), 
                $this -> data['db_data']
            );

            if(!$result)
                return Except::exc_404( Notifications::getNoti('somethingWrong') );

            // echo 'CHECK YEAR WISE RISK HERE AFTER TANVIR COMPLETE';
            $lastInsertId = $this -> auditAssesmentModel::lastInsertId();

            // insert in timeline
            $insertArray = array(
                'id' => $lastInsertId,
                'type' => 1,
                'status' => ASSESMENT_TIMELINE_ARRAY[1]['status_id'],
                'rejected_cnt' => 0,
                'emp_id' => Session::get('emp_id'),
                'batch_key' => $this -> data['db_data']['batch_key'],
            );

            // function call
            audit_assesment_timeline_insert($this, $insertArray);

            // 10.09.2024 // update
            if( check_carry_forward_strict() )
            {
                // check CF answers 03.08.2024 // method call
                $this -> saveCarryForwardAnswer($lastInsertId, $this -> cfDataInsert);
            }

            // after insert data redirect to risk composite dashboard
            Validation::flashErrorMsg('auditAssesmentStartSuccess', 'success');
            Redirect::to( SiteUrls::getUrl('auditAssessment') . '/assesment/' . encrypt_ex_data($lastInsertId) );

        });        
    }

    public function assesment($getRequest) 
    {
        $this -> assesId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        $this -> data['db_data'] = null;

       // get data //method call
       $this -> data['db_data'] = $this -> getDataOr404($this -> assesId);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $empType = Session::get('emp_type');
        Session::set('audit_id', encrypt_ex_data($this -> assesId) );

        $redirectURL = null;

        switch($empType)
        {
            case '2' :
            {
                // audit & re audit
                if( in_array( $this -> data['db_data'] -> audit_status_id, [ 
                        ASSESMENT_TIMELINE_ARRAY[1]['status_id'], ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                    ] ))
                $redirectURL = SiteUrls::setUrl('audit');
                
                break;
            }

            case '3' :
            {
                // compliance
                if( in_array( $this -> data['db_data'] -> audit_status_id, [ 
                        ASSESMENT_TIMELINE_ARRAY[4]['status_id']
                    ] ))
                $redirectURL = SiteUrls::setUrl('compliance');

                // re compliance
                else if ( in_array( $this -> data['db_data'] -> audit_status_id, [ 
                        ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                    ] ))
                $redirectURL = SiteUrls::setUrl('compliance') . '/re-compliance';

                break;
            }

            case '4' :
            {
                // review audit
                if( in_array($this -> data['db_data'] -> audit_status_id, [
                        ASSESMENT_TIMELINE_ARRAY[2]['status_id']
                    ] ))
                $redirectURL = SiteUrls::getUrl('reviewer') . '/review-audit';

                 // review compliance
                else if( in_array($this -> data['db_data'] -> audit_status_id, [
                        ASSESMENT_TIMELINE_ARRAY[5]['status_id']
                    ] ))
                $redirectURL = SiteUrls::getUrl('reviewer') . '/review-compliance';

                break;
            }
            case '16' :
            {
                if( in_array($this -> data['db_data'] -> audit_status_id, [
                        ASSESMENT_TIMELINE_ARRAY[15]['status_id']
                    ] ))
                $redirectURL = SiteUrls::getUrl('reviewer') . '/review-compliance';

                break;
            }
        }

        if(empty( $redirectURL ))
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // print_r($this -> data['db_data']);

        // redirect to location
        Redirect::to( $redirectURL );
    }

    private function checkCarryForward()
    {
        $cfAnsData = [];
        $cfInsertData = [];
        $cOldUpdateData = [ 'ans_id' => [], 'annex_id' => [] ];
        $ansModel = $this -> model('AnswerDataModel');
        $annexModel = $this -> model('AnswerDataAnnexureModel');

        if( check_carry_forward_strict() && 
            is_object($this -> data['db_audit_unit']) && 
            is_object($this -> data['db_fy_details']) )
        {
            // get all assesment which has carry forward points grather than 0
            $getAllCFAssessments = $this -> auditAssesmentModel -> getAllAuditAssesment([
                'where' => 'audit_type_id = 1 AND year_id = :year_id AND audit_unit_id = :audit_unit_id AND	audit_status_id = :audit_status_id AND compliance_carry_forward_count > 0 AND deleted_at IS NULL',
                'params' => [
                    'year_id' => $this -> data['db_fy_details'] -> id,
                    'audit_unit_id' => $this -> data['db_audit_unit'] -> id,
                    'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[7]['status_id']
                ]
            ]);

            // print_r($getAllCFAssessments);

            if( is_array($getAllCFAssessments) && sizeof($getAllCFAssessments) > 0 )
            {
                // has assesment data // function call
                $getAllCFAssessments = generate_data_assoc_array($getAllCFAssessments, 'id');

                // CHECK FOR GENERAL ANSWER DATA // find carry forward data // method call
                $cfAnsData = $this -> checkCarryForwardAnsJOINData( $ansModel, 'general', [
                    'asses_ids' => array_keys($getAllCFAssessments)
                ]);

                // CHECK FOR ANNEXURE MIXED DATA LIKE IF PARENT ANS CF OR ONLY ANNEXURE HAS CF POINT
                $cfAnsData = $this -> checkCarryForwardAnsJOINData( $annexModel , 'annexure', [
                    'asses_ids' => array_keys($getAllCFAssessments), 
                    'cfAnsData' => $cfAnsData
                ]);
            }

            if( (isset($cfAnsData['ans']) && sizeof($cfAnsData['ans']) > 0) || 
                (isset($cfAnsData['ans_annex']) && sizeof($cfAnsData['ans_annex']) > 0) )
            {
                // HAS ANSWER DATA // FIND OTHER DETAILS
                $cfAnsData['ques_data'] = [];
                $cfAnsData['deposits_dump_data'] = [];
                $cfAnsData['advances_dump_data'] = [];

                // FIND QUESTION DATA
                $model = $this -> model('QuestionMasterModel');
                $tableName = $model -> getTableName();
                $query = "SELECT id, question, risk_category_id, option_id, annexure_id FROM " . $tableName . " WHERE id IN (". implode(',', $cfAnsData['ques']) .") AND deleted_at IS NULL";
                $dbData = get_all_data_query_builder(2, $model, $tableName, [], 'sql', $query); 
                $annexIds = [];
                $subsetIds = [];

                if(is_array($dbData) && sizeof($dbData) > 0)
                {
                    foreach($dbData as $cQuesData)
                    {
                        // FOR ANNEXURE
                        if($cQuesData -> option_id == 4 && !empty($cQuesData -> annexure_id))
                            $annexIds[ $cQuesData -> annexure_id ] = $cQuesData -> id;

                        // FOR SUBSET
                        if($cQuesData -> option_id == 5 && !empty($cQuesData -> subset_multi_id))
                        {
                            $cQuesData -> subset_multi_id = explode(',', $cQuesData -> subset_multi_id);

                            if( is_array($cQuesData -> subset_multi_id) && 
                                sizeof($cQuesData -> subset_multi_id) > 0 )
                                $subsetIds = array_merge($subsetIds, $cQuesData -> subset_multi_id);
                        }

                        if(!array_key_exists($cQuesData -> id, $cfAnsData['ques_data']))   
                            $cfAnsData['ques_data'][ $cQuesData -> id ] = $cQuesData;
                    }
                }

                // ANNEXURE MASTER
                if(sizeof($annexIds) > 0)
                {
                    $model = $this -> model('AnnexureMasterModel');
                    $tableName = $model -> getTableName();
                    
                    // FIND ANNEXURE DATA
                    $query = "SELECT am.id AS am_id, ac.id, ac.annexure_id, ac.name FROM annexure_master am JOIN annexure_columns ac ON am.id = ac.annexure_id WHERE am.id IN (". implode(',', array_keys($annexIds)) .") AND am.deleted_at IS NULL AND ac.deleted_at IS NULL GROUP BY ac.id, am.id, am.name, ac.annexure_id, ac.name";

                    $dbData = get_all_data_query_builder(2, $model, $tableName, [], 'sql', $query);

                    if(is_array($dbData) && sizeof($dbData) > 0)
                    {
                        foreach($dbData as $cAnnexData)
                        {
                            if(!isset($cfAnsData['ques_data'][ $annexIds[ $cAnnexData -> am_id ] ] -> annex_cols))
                                $cfAnsData['ques_data'][ $annexIds[ $cAnnexData -> am_id ] ] -> annex_cols = [];

                            $cfAnsData['ques_data'][ $annexIds[ $cAnnexData -> am_id ] ] -> annex_cols[ $cAnnexData -> id ] = $cAnnexData;
                        }
                    }
                }

                // SUBSET DATA
                if(sizeof($subsetIds) > 0)
                {
                    $model = $this -> model('QuestionSetModel');
                    $tableName = $model -> getTableName();

                    // FIND SUBSET DATA
                    $query = "SELECT id, name, set_type_id FROM " . $tableName . " WHERE id IN (". implode(',', $subsetIds) .") AND deleted_at IS NULL";                   

                    if(is_array($dbData) && sizeof($dbData) > 0)
                    {
                        foreach($cfAnsData['ques_data'] as $cQuesId => $cQuesDetails)
                        {
                            if( $cQuesDetails -> option_id == 5 && 
                                is_array($cQuesDetails -> subset_multi_id) &&
                                sizeof($cQuesDetails -> subset_multi_id) > 0)
                            {
                                if(!isset($cQuesDetails -> subset_data))
                                    $cfAnsData['ques_data'][ $cQuesId ] -> subset_data = [];

                                foreach($dbData as $cSubsetData)
                                {
                                    // push data
                                    if(in_array($cSubsetData -> id, $cQuesDetails -> subset_multi_id))
                                        $cfAnsData['ques_data'][ $cQuesId ] -> subset_data[ $cSubsetData -> id ] = $cSubsetData;
                                }
                            }
                        }
                    }
                }

                // FIND DEPOSITS
                if(sizeof($cfAnsData['deposits_dump']) > 0)
                {
                    $model = $this -> model('DumpDepositeModel');
                    $tableName = $model -> getTableName();

                    $query = "SELECT dt.id, dt.scheme_id, dt.account_no, 
                            dt.account_holder_name, dt.ucic, dt.account_opening_date,
                            COALESCE(sm.name, 'NA') AS scheme_name, COALESCE(sm.scheme_code, 'NA') AS scheme_code FROM " . $tableName . " dt JOIN scheme_master sm ON sm.id = dt.scheme_id WHERE dt.id IN (". implode(',', $cfAnsData['deposits_dump']) .")";

                    $cfAnsData['deposits_dump_data'] = get_all_data_query_builder(2, $model, $tableName, [], 'sql', $query); 
                    $cfAnsData['deposits_dump_data'] = generate_data_assoc_array($cfAnsData['deposits_dump_data'], 'id');
                }

                // FIND ADVANCES
                if(sizeof($cfAnsData['advances_dump']) > 0)
                {
                    $model = $this -> model('DumpAdvancesModel');
                    $tableName = $model -> getTableName();

                    $query = "SELECT dt.id, dt.scheme_id, dt.account_no, 
                            dt.account_holder_name, dt.ucic, dt.account_opening_date,
                            COALESCE(sm.name, 'NA') AS scheme_name, COALESCE(sm.scheme_code, 'NA') AS scheme_code FROM " . $tableName . " dt JOIN scheme_master sm ON sm.id = dt.scheme_id WHERE dt.id IN (". implode(',', $cfAnsData['advances_dump']) .")";

                    $cfAnsData['advances_dump_data'] = get_all_data_query_builder(2, $model, $tableName, [], 'sql', $query); 
                    $cfAnsData['advances_dump_data'] = generate_data_assoc_array($cfAnsData['advances_dump_data'], 'id');
                }

                // GENERATE SINGLE QUERY FOR EACH ANSWERS 01.08.2024
                foreach(['ans', 'ans_annex'] as $cCFDataCategory)
                {
                    if(is_array($cfAnsData[ $cCFDataCategory ]) && sizeof($cfAnsData[ $cCFDataCategory ]) > 0):

                    foreach($cfAnsData[ $cCFDataCategory ] as $cAnsId => $cAnsData)
                    {
                        // CF LIKE OTHER DISCREPANCIES SO APPEND DATA
                        $insert = [ 'assesment' => '', 'path' => '', 'question' => '', 'answer' => '', 
                                    'old_audit_comment' => '', 'old_audit_compliance' => '', 'old_compliance_reviewer_comment' => '',
                                    'rt' => 1, 'br' => 1, 'cr' => 1, 'ans_id' => 0, 'annex_id' => 0, 
                                    'cf_asses_ids' => [] ];

                        $cAssesData = $getAllCFAssessments[ $cAnsData -> assesment_id ];
                        $cQuesData = array_key_exists($cAnsData -> question_id, $cfAnsData['ques_data']) ? $cfAnsData['ques_data'][ $cAnsData -> question_id ] : null;
                        $cAnsAnnex = false;

                        $insert['ans_id'] = $cAnsData -> id;
                        $insert['cf_asses_ids'][] = $cAnsData -> assesment_id;

                        // append assesment data
                        $insert['assesment'] .= 'Assesment Period: ' . $cAssesData -> assesment_period_from . ' To ' . $cAssesData -> assesment_period_from . ' ( Frequency: ' . $cAssesData -> frequency . ' Months )';

                        // append question data
                        $insert['path'] = 'Menu: '. string_operations(($cAnsData -> menu_name != 'NA' ? $cAnsData -> menu_name : ERROR_VARS['notFound']), 'upper') .', Category: '. string_operations(($cAnsData -> category_name != 'NA' ? $cAnsData -> category_name : ERROR_VARS['notFound']), 'upper') .', Header: '. string_operations(($cAnsData -> header_name != 'NA' ? $cAnsData -> header_name : ERROR_VARS['notFound']), 'upper');
                        
                        $insert['question'] = trim_str( is_object($cQuesData) ? $cQuesData -> question : ERROR_VARS['notFound'] );

                        // check for dump
                        if(!empty($cAnsData -> dump_id))
                        {
                            $cDumpData = null;

                            // FOR ADVANCES
                            if( $cAnsData -> linked_table_id == 2 && 
                                is_array($cfAnsData['advances_dump_data']) && 
                                array_key_exists($cAnsData -> dump_id, $cfAnsData['advances_dump_data']) )
                            {
                                $cDumpData = $cfAnsData['advances_dump_data'][ $cAnsData -> dump_id ];
                                $insert['path'] .= ', Advance Dump: ';
                            }

                            // FOR DEPOSITS
                            else if( $cAnsData -> linked_table_id == 1 && 
                                    is_array($cfAnsData['deposits_dump_data']) && 
                                    array_key_exists($cAnsData -> dump_id, $cfAnsData['deposits_dump_data']))
                            {    
                                $cDumpData = $cfAnsData['deposits_dump_data'][ $cAnsData -> dump_id ];
                                $insert['path'] .= ', Deposit Dump: ';
                            }

                            if( is_object($cDumpData) )
                                $insert['path'] .= $cDumpData -> account_no . ', A/C Holder Name: ' . string_operations($cDumpData -> account_holder_name, 'upper') . ', Scheme Code: ' . string_operations(($cDumpData -> scheme_code != 'NA' ? $cDumpData -> scheme_code : ERROR_VARS['notFound']), 'upper') . ' ( Scheme: '. string_operations(($cDumpData -> scheme_name != 'NA' ? $cDumpData -> scheme_name : ERROR_VARS['notFound']), 'upper') .' )' ;
                        }

                        // assign default risk
                        if( is_object($cQuesData) )
                        {
                            $insert['rt'] = $cQuesData -> risk_category_id;

                            if( $cQuesData -> option_id == 4 && 
                                isset($cAnsData -> answer_id) && 
                                !empty($cAnsData -> answer_id))
                                $cAnsAnnex = true;
                        }

                        $insert['br'] = isset(RISK_PARAMETERS_ARRAY[ $cAnsData -> business_risk ]) ? RISK_PARAMETERS_ARRAY[ $cAnsData -> business_risk ]['id'] : 1;
                        $insert['cr'] = isset(RISK_PARAMETERS_ARRAY[ $cAnsData -> control_risk ]) ? RISK_PARAMETERS_ARRAY[ $cAnsData -> control_risk ]['id'] : 1;

                        if($cAnsAnnex)
                        {
                            // annex data loop each annex
                            $annexInsertArray = [];

                            // foreach($cAnsData -> annex_data as $cAnnexId => $cAnnexData) {
                                
                                $cOldUpdateData['annex_id'][] = $cAnsData -> id;
                                $annexInsert = $insert;
                                $annexInsert['rt'] = $cAnsData -> risk_cat_id;
                                $annexInsert['br'] = isset(RISK_PARAMETERS_ARRAY[ $cAnsData -> business_risk ]) ? RISK_PARAMETERS_ARRAY[ $cAnsData -> business_risk ]['id'] : 1;
                                $annexInsert['cr'] = isset(RISK_PARAMETERS_ARRAY[ $cAnsData -> control_risk ]) ? RISK_PARAMETERS_ARRAY[ $cAnsData -> control_risk ]['id'] : 1;
                                $annexInsert['annex_id'] = $cAnsData -> id;

                                try {
                                    
                                    $cAnnexDataArr = json_decode($cAnsData -> answer_given, 1);

                                    if(is_array($cAnnexDataArr) && sizeof($cAnnexDataArr) > 0)
                                    {
                                        $cAnnexCols = [];

                                        if( is_object($cQuesData) && 
                                            isset($cQuesData -> annex_cols) &&
                                            is_array($cQuesData -> annex_cols) && 
                                            sizeof($cQuesData -> annex_cols) > 0 )
                                            $cAnnexCols = array_values($cQuesData -> annex_cols);

                                        unset($cAnnexDataArr['rt'], $cAnnexDataArr['br'], $cAnnexDataArr['cr']);

                                        foreach($cAnnexDataArr as $cAnnexColIndex => $cAnnexColData) {

                                            if(isset($cAnnexCols[ $cAnnexColIndex ]))
                                                $annexInsert['answer'] .= $cAnnexCols[ $cAnnexColIndex ] -> name . ': ' . $cAnnexColData;
                                            else
                                                $annexInsert['answer'] .= 'Col: ' . $cAnnexColData;

                                            $annexInsert['answer'] .= ', ';
                                        }

                                        if(!empty($annexInsert['answer']))
                                            $annexInsert['answer'] = substr($annexInsert['answer'], 0, -2);

                                        // audit_comment
                                        $insert['old_audit_comment'] = trim_str($cAnsData -> audit_comment);

                                        // audit_commpliance
                                        $insert['old_audit_compliance'] = trim_str($cAnsData -> audit_commpliance);

                                        // compliance_reviewer_comment
                                        $insert['old_compliance_reviewer_comment'] = trim_str($cAnsData -> compliance_reviewer_comment);
                                    }
                                    else
                                        $annexInsert['answer'] .= 'ANNEXURE DATA MISSING';

                                } catch (Exception $e) { $annexInsert['question'] .= ', ANNEXURE DATA MISSING'; }

                                $annexInsertArray[ ] = $annexInsert;
                            // }

                            $cfInsertData = array_merge($cfInsertData, $annexInsertArray);
                        }
                        else
                        {
                            // general answer
                            $cAnsGiven = trim_str($cAnsData -> answer_given);
                            $cOldUpdateData['ans_id'][] = $cAnsData -> id;

                            if( is_object($cQuesData) && 
                                $cQuesData -> option_id == 5 &&
                                is_object($cQuesData -> subset_data) && 
                                is_array($cQuesData -> subset_data) && 
                                array_key_exists($cAnsGiven, $cQuesData -> subset_data))
                                $insert['answer'] .= trim_str($cQuesData -> subset_data[ $cAnsGiven ] -> name);
                            else
                                $insert['answer'] .= trim_str($cAnsGiven);

                            // audit_comment
                            $insert['old_audit_comment'] = trim_str($cAnsData -> audit_comment);

                            // audit_commpliance
                            $insert['old_audit_compliance'] = trim_str($cAnsData -> audit_commpliance);

                            // compliance_reviewer_comment
                            $insert['old_compliance_reviewer_comment'] = trim_str($cAnsData -> compliance_reviewer_comment);

                            $cfInsertData[] = $insert;
                        }
                    }

                    endif;
                }

                // print_r($cfInsertData);

                // unset vars
                unset($model, $tableName, $query, $dbData, $annexIds, $subsetIds);
            }
        }

        return [ 'cf_insert' => $cfInsertData, 'old_ans_id' => $cOldUpdateData['ans_id'], 'old_annex_id' => $cOldUpdateData['annex_id'] ];
    }

    private function checkCarryForwardAnsJOINData($model, $type = 'general', $extra = [])
    {
        $res = [ 'ans' => [], 'ans_annex' => [], 'ans_ids' => [], 'annex_ids' => [], 'ques' => [], 'deposits_dump' => [], 'advances_dump' => [] ];
        $tableName = $model -> getTableName();

        if(isset($extra['cfAnsData']))
            $res = $extra['cfAnsData'];

        if($type == 'annexure')
        {
            // ANNEXURE DATA JOIN QUERY HERE
            $query = "SELECT 
                        ada.*,
                        COALESCE(ad.menu_id, 'NA') AS menu_id,
                        COALESCE(ad.category_id, 'NA') AS category_id,
                        COALESCE(ad.header_id, 'NA') AS header_id,
                        COALESCE(ad.question_id, 'NA') AS question_id,
                        COALESCE(ad.dump_id, 'NA') AS dump_id,
                        COALESCE(ad.answer_given, 'NA') AS ad_answer_given,
                        COALESCE(mm.name, 'NA') AS menu_name,
                        COALESCE(cm.name, 'NA') AS category_name,
                        COALESCE(cm.linked_table_id, 'NA') AS linked_table_id,
                        COALESCE(qhm.name, 'NA') AS header_name,
                        COALESCE(qm.id, 'NA') AS qm_id,
                        COALESCE(qm.question, 'NA') AS qm_question,
                        COALESCE(qm.risk_category_id, 'NA') AS qm_risk_category_id,
                        COALESCE(qm.option_id, 'NA') AS qm_option_id,
                        COALESCE(qm.annexure_id, 'NA') AS qm_annexure_id
                    FROM 
                        answers_data_annexure ada
                    LEFT JOIN 
                        answers_data ad ON ada.answer_id = ad.id
                    LEFT JOIN 
                        menu_master mm ON ad.menu_id = mm.id
                    LEFT JOIN 
                        category_master cm ON ad.category_id = cm.id
                    LEFT JOIN 
                        question_header_master qhm ON ad.header_id = qhm.id
                    LEFT JOIN 
                        question_master qm ON ad.question_id = qm.id
                    WHERE   ad.is_compliance = 1 
                        AND ada.compliance_status_id = 5 
                        AND ada.deleted_at IS NULL 
                        AND ad.deleted_at IS NULL 
                        AND (ada.cf_asses_id IS NULL OR ada.cf_asses_id = 0) 
                        AND mm.deleted_at IS NULL 
                        AND cm.deleted_at IS NULL 
                        AND qhm.deleted_at IS NULL 
                        AND qm.deleted_at IS NULL";
        }
        else
        {
            // ANSWER DATA JOIN QUERY HERE
            $query = "SELECT 
                        ad.*,
                        COALESCE(mm.name, 'NA') AS menu_name,
                        COALESCE(cm.name, 'NA') AS category_name,
                        COALESCE(cm.linked_table_id, 'NA') AS linked_table_id,
                        COALESCE(qhm.name, 'NA') AS header_name,
                        COALESCE(qm.id, 'NA') AS qm_id,
                        COALESCE(qm.question, 'NA') AS qm_question,
                        COALESCE(qm.risk_category_id, 'NA') AS qm_risk_category_id,
                        COALESCE(qm.option_id, 'NA') AS qm_option_id,
                        COALESCE(qm.annexure_id, 'NA') AS qm_annexure_id
                    FROM 
                        answers_data ad
                    LEFT JOIN 
                        menu_master mm ON ad.menu_id = mm.id
                    LEFT JOIN 
                        category_master cm ON ad.category_id = cm.id
                    LEFT JOIN 
                        question_header_master qhm ON ad.header_id = qhm.id
                    LEFT JOIN 
                        question_master qm ON ad.question_id = qm.id
                    WHERE   ad.is_compliance = 1 
                        AND ad.compliance_status_id = 5 
                        AND ad.deleted_at IS NULL 
                        AND (ad.cf_asses_id IS NULL OR ad.cf_asses_id = 0) 
                        AND mm.deleted_at IS NULL 
                        AND cm.deleted_at IS NULL 
                        AND qhm.deleted_at IS NULL 
                        AND qm.deleted_at IS NULL";
        }

        if(isset($extra['asses_ids']) && !empty($extra['asses_ids']))
            $query .= " AND ad.assesment_id IN (". implode(',', $extra['asses_ids']) .")";

        $getAnsData = get_all_data_query_builder(2, $model, $tableName, [], 'sql', $query); 

        if(is_array($getAnsData) && sizeof($getAnsData) > 0)
        {
            foreach($getAnsData as $cAnsData)
            {
                if($type == 'general')
                {
                    // FOR GENERAL ANSWER
                    if(!array_key_exists($cAnsData -> id, $res['ans']))
                        $res['ans'][ $cAnsData -> id ] = $cAnsData;

                    if(!in_array($cAnsData -> id, $res['ans_ids']) && $type == 'general')
                        $res['ans_ids'][] = $cAnsData -> id;
                }
                else
                {
                    // FOR ANNEXURE ANSWER
                    if(!array_key_exists($cAnsData -> id, $res['ans_annex']))
                        $res['ans_annex'][ $cAnsData -> id ] = $cAnsData;                    

                    if(!in_array($cAnsData -> id, $res['annex_ids']) && $type == 'annexure')
                        $res['annex_ids'][] = $cAnsData -> id;

                    // CHECK FOR PARENT ANNEXRUE QUESTION
                    if(!array_key_exists($cAnsData -> answer_id, $res['ans']))
                        unset($res['ans'][ $cAnsData -> answer_id ]);
                }                    
                    
                if(!in_array($cAnsData -> question_id, $res['ques']))
                    $res['ques'][] = $cAnsData -> question_id;

                if(!empty($cAnsData -> dump_id) && 
                    array_key_exists($cAnsData -> linked_table_id, $GLOBALS['schemeTypesArray']))
                {
                    // dump data
                    if($cAnsData -> linked_table_id == 2)
                        $res['advances_dump'][] = $cAnsData -> dump_id;
                    else
                        $res['deposits_dump'][] = $cAnsData -> dump_id;
                }
            }
        }

        $res['ans_ids'] = array_unique($res['ans_ids']);
        $res['annex_ids'] = array_unique($res['annex_ids']);
        $res['ques'] = array_unique($res['ques']);
        $res['advances_dump'] = array_unique($res['advances_dump']);
        $res['deposits_dump'] = array_unique($res['deposits_dump']);

        return $res;
    }

    private function saveCarryForwardAnswer($newAssesId, $cfDataInsert)
    {
        // old_ans_id, old_annex_id, cf_insert
        $checkSameCount = sizeof($cfDataInsert['old_ans_id']) + sizeof($cfDataInsert['old_annex_id']);

        if(!empty($newAssesId) && 
            sizeof($cfDataInsert['cf_insert']) > 0 && 
            sizeof($cfDataInsert['cf_insert']) == $checkSameCount )
        {
            $ansModel = $this -> model('AnswerDataModel');
            $annexModel = $this -> model('AnswerDataAnnexureModel');

            // $this -> data['db_data']['batch_key'] = 'demo_key';
            $annexInsertIds = [];

            // has data
            $insertAnsData = array(
                "section_type_id" => $this -> data['db_data']['audit_type_id'],
                "assesment_id" => $newAssesId,
                "menu_id" => 0,
                "category_id" => 0,
                "header_id" => 0,
                "question_id" => 0,
                "dump_id" => 0,
                "answer_given" => 'CF',
                "audit_comment" => NULL,
                "audit_emp_id" => Session::get('emp_id'),
                "audit_status_id" => 0,
                "audit_reviewer_emp_id" => 0,
                "audit_reviewer_comment" => NULL,
                "is_compliance" => 1,
                "audit_commpliance" => NULL,
                "compliance_evidance_upload" => NULL,
                "compliance_emp_id" => 0,
                "compliance_status_id" => 0,
                "compliance_reviewer_emp_id" => 0,
                "compliance_reviewer_comment" => NULL,
                "business_risk" => 1,
                "control_risk" => 1,
                "instances_count" => 0,
                "batch_key" => $this -> data['db_data']['batch_key'],
            );

            $result = $ansModel::insert(
                $ansModel -> getTableName(), 
                $insertAnsData
            );

            if($result)
            {
                $lastAnsId = $this -> auditAssesmentModel::lastInsertId();
                $insertAnnexArray = [];                

                foreach($cfDataInsert['cf_insert'] as $cInsert)
                {
                    $insertAnnexArray[] = array(
                        "answer_id" => $lastAnsId,
                        "assesment_id" => $newAssesId,
                        "answer_given" => json_encode($cInsert, 256),
                        "audit_comment" => NULL,
                        "audit_emp_id" => Session::get('emp_id'),
                        "audit_status_id" => 1,
                        "audit_reviewer_emp_id" => 0,
                        "audit_reviewer_comment" => NULL,
                        "audit_commpliance" => NULL,
                        "compliance_evidance_upload" => NULL,
                        "compliance_emp_id" => 0,
                        "compliance_status_id" => 0,
                        "compliance_reviewer_emp_id" => 0,
                        "compliance_reviewer_comment" => NULL,
                        "business_risk" => $cInsert['br'],
                        "control_risk" => $cInsert['cr'],
                        "risk_cat_id" => $cInsert['rt'],
                        "batch_key" => $this -> data['db_data']['batch_key'],
                    );
                }

                $annexInsertIds = $annexModel::insertMultiple(
                    $annexModel -> getTableName(), 
                    $insertAnnexArray,
                    true
                );

                if( !is_array($annexInsertIds) || (is_array($annexInsertIds) && !(sizeof($annexInsertIds) > 0)) )
                {
                    // method call
                    $this -> removeCarryForwardAnswer($ansModel, $lastAnsId, null, null);
                }
            }

            // ALL OK // UPDATE OLD ANS IDS
            if(!empty($lastAnsId) && is_array($annexInsertIds) && sizeof($annexInsertIds) > 0)
            {
                $this -> oldAnswerDataModify($ansModel, $annexModel, $cfDataInsert, $newAssesId);

                // UPDATE MAIN ASSESMENT MENU to add CR
                $menuIds = $this -> data['db_data']['menu_ids'] . ',CF';

                $result = $this -> auditAssesmentModel::update(
                    $this -> auditAssesmentModel -> getTableName(), 
                    [  'menu_ids' => $menuIds ],
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $newAssesId ]
                    ]
                );

                if(!$result)
                {
                    // print_r($result);
                    // print_r($annexInsertIds);
                    // // exit;

                    // method call   
                    $this -> removeCarryForwardAnswer($ansModel, $lastAnsId, $annexModel, $annexInsertIds);
                    $this -> oldAnswerDataModify($ansModel, $annexModel, $cfDataInsert, $newAssesId, 'cf_empty');
                }
            }
        }      
    }

    private function removeCarryForwardAnswer($ansModel, $ansParentId, $annexModel, $ansChildIds)
    {
        $deleted_at = date($GLOBALS['dateSupportArray'][2]);
        $audit_comment = 'Error: Carry forward answer save error';

        // manually answer inserted so remove due to error
        $result = $ansModel::update(
            $ansModel -> getTableName(), 
            [ 
                'deleted_at' => $deleted_at,
                'audit_comment' => $audit_comment
            ],
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $ansParentId ]
            ]
        );

        if(is_array($ansChildIds) && sizeof($ansChildIds) > 0)
        {
            $annexRemoveArray = [ 'update' => [], 'where' => [] ];

            foreach( $ansChildIds as $cAnnexId ) {
                $annexRemoveArray['update'][] = [  'deleted_at' => $deleted_at, 'audit_comment' => $audit_comment ];
                $annexRemoveArray['where'][] =  [  'where' => 'id = :id', 'params' => [ 'id' => $cAnnexId ] ];
            }

            // update multiple
            $result = $annexModel::updateMultiple( $annexModel -> getTableName(), $annexRemoveArray['update'], $annexRemoveArray['where'] );
            unset($annexRemoveArray);
        }

        unset($deleted_at, $audit_comment);
    }

    private function oldAnswerDataModify($ansModel, $annexModel, $cfDataInsert, $newAssesId, $task = 'cf') {

        $transferDate = date($GLOBALS['dateSupportArray'][1]);

        // FOR OLD ANSWER
        if(sizeof($cfDataInsert['old_ans_id']) > 0)
        {
            // create update array
            $cfUpdateArray = [ 'update' => [], 'where' => [] ];
            
            foreach($cfDataInsert['old_ans_id'] as $cAnsId) {

                if($task == 'cf')
                    $cfUpdateArray['update'][] = [  'cf_asses_id' => $newAssesId, 'cf_transfer_date' => $transferDate ];
                else
                    $cfUpdateArray['update'][] = [  'cf_asses_id' => NULL, 'cf_transfer_date' => NULL ];

                $cfUpdateArray['where'][] =  [  'where' => 'id = :id', 'params' => [ 'id' => $cAnsId ] ];
            }

            $result = $ansModel::updateMultiple( $ansModel -> getTableName(), $cfUpdateArray['update'], $cfUpdateArray['where'] );
        }

        // FOR OLD ANNEXURE
        if(sizeof($cfDataInsert['old_annex_id']) > 0)
        {
            // create update array
            $cfUpdateArray = [ 'update' => [], 'where' => [] ];
            
            foreach($cfDataInsert['old_annex_id'] as $cAnnexId) {
                
                if($task == 'cf')
                    $cfUpdateArray['update'][] = [  'cf_asses_id' => $newAssesId, 'cf_transfer_date' => $transferDate ];
                else
                    $cfUpdateArray['update'][] = [  'cf_asses_id' => NULL, 'cf_transfer_date' => NULL ];
                
                $cfUpdateArray['where'][] =  [  'where' => 'id = :id', 'params' => [ 'id' => $cAnnexId ] ];
            }

            $result = $annexModel::updateMultiple( $annexModel -> getTableName(), $cfUpdateArray['update'], $cfUpdateArray['where'] );
        }
    }

    public function getDataOr404($assesId, $optional = null) {

        // CHECK AUDIT AUTHORITY TO ACCESS CURRENT AUDIT
        
        // helper function call
        $this -> data['db_data'] = get_assesment_details($this, Session::get('emp_id'), $this -> assesId);

        if( !is_object($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti($this -> data['db_data']) );
            exit;
        }

        return $this -> data['db_data'];
    }
}

?>