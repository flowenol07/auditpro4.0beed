<?php

namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

// extra common functions 03.07.2024
require_once 'Audit/AuditCommonCodeHelper.php';

class ExecutiveSummary extends Controller {

    public $me = null, $request, $data, $report_submitted_date, $empId, $assesmentData, $menuData;
    public $exeBasicModel, $exeBranchPositionModel, $exeSummaryModel, $exeFreshAccountModel;

    // public $targetMasterModel, $exeBasicModel, $exeBranchPositionModel, $exeSummaryModel, $exeFreshAccountModel, $esbpTimelineModel, $esfaTimelineModel, $menuModel, $auditAssesmentModel;

    public function __construct($me) {

        $this -> me = $me;
        $this -> me -> menuKey = 'menu_1';
        $this -> empId = Session::get('emp_id');

        $this -> report_submitted_date = date('Y-m-01');

        // request object created
        $this -> request = new Request();

        // ---------------- get GL Type Array----------------------
        $this -> data['branch_financial_position'] = BRANCH_FINANCIAL_POSITION['deposits'] + BRANCH_FINANCIAL_POSITION['advances'] + BRANCH_FINANCIAL_POSITION['npa'];
        $this -> data['branch_fresh_accounts'] = BRANCH_FRESH_ACCOUNTS['deposits'] + BRANCH_FRESH_ACCOUNTS['advances'] + BRANCH_FRESH_ACCOUNTS['npa'];

        // top data container
        $this -> data['data_container'] = true;
        $this -> data['need_calender'] = true;

        // need audit assesment js	
        $this -> data['js'][] = 'executive-summary-audit.js';
    }

    private function getAssesmentData($assesId)
    {
        // helper function call
        if(!empty($assesId))
        {
            $assesId = decrypt_ex_data($assesId);
            $this -> assesmentData = get_assesment_details($this, $this -> empId, $assesId);
        }

        if( !is_object($this -> assesmentData) )
        {
            Except::exc_404( Notifications::getNoti($this -> assesmentData) );
            exit;
        }

        // GET OTHER DETAILS
        $this -> assesmentData = get_assesment_all_details($this, NULL, $this -> assesmentData);
        $this -> data['assesmentData'] = $this -> assesmentData;
    }

    private function checkAccessRestrict($type = 1)
    {
        // for audit 1, // for compliance = 3, // for reviewer = 2
        $typeRestrictions = [ 1 => 2, 2 => 4, 3 => 3 ];

        $reviewCheck = ($type == 2 && !ENV_CONFIG['executive_summary_review']) ? false : true;

        if(!$reviewCheck || isset($typeRestrictions[ $type ]) && Session::get('emp_type') != $typeRestrictions[ $type ])
        {
            Except::exc_access_restrict();
            exit;
        }
    }

    private function checkMenuActive()
    {
        // menu id 1 exist check in assesment data
        $assesMenuArray = !empty($this -> assesmentData -> menu_ids) ? explode(",", $this -> assesmentData -> menu_ids) : [];

        $menuIdActive = (is_array($assesMenuArray) && in_array(1, $assesMenuArray));

        // menu data for checking if menu id 1 is deleted or not
        $model = $this -> model('MenuModel');

        // get single menu data
        $menuData = $model -> getSingleMenu(['where' => 'id = 1 AND deleted_at IS NULL']);
        $menuDataActive = is_object($menuData);

        if(!$menuIdActive || !$menuDataActive)
        {
            Except::exc_404( Notifications::getNoti('somethingWrong'));
            exit;
        }
    }

    private function reAuditFindData($returnData = null, $res = false) {
        // function call
        return get_re_audit_find_data($this, $returnData, $res);
    }

    private function getMenuData($reAuditFindData = null) {
        return get_menu_category_mix($this, $this -> assesmentData, $reAuditFindData);
    }   

    private function getBasicDetailsData() {

        // --------------------- get target data // model of target details ---------------------
        $model = $this -> model('TargetMasterModel');

        // get target
        $this -> data['db_target'] = $model -> getAllTarget([
            'where' => 'deleted_at IS NULL AND audit_unit_id = :audit_unit_id AND year_id = :year_id',
            'params' => [
                'audit_unit_id' => $this -> assesmentData -> audit_unit_id,
                'year_id' => $this -> assesmentData -> year_id
            ]
        ]);

        // --------------------- model of executive summary basic details ---------------------
        $this -> exeBasicModel = $this -> model('ExeSummaryBasicModel');

        // get single basic details
        $this -> data['db_exe_basic_details'] = $this -> exeBasicModel -> getSingleBasicDetails([
            'where' => 'deleted_at IS NULL AND assesment_id = :assesment_id AND year_id = :year_id',
            'params' => [
                'assesment_id' => $this -> assesmentData -> id,
                'year_id' => $this -> assesmentData -> year_id
            ]
        ]);
        
        if(!is_object($this -> data['db_exe_basic_details']))
        {
            // create empty instance for default values in form
            $this -> data['db_exe_basic_details'] = $this -> exeBasicModel -> emptyInstance();
            $this -> data['db_exe_basic_details'] -> report_submitted_date = $this -> report_submitted_date;
            $this -> data['btn_type_basic'] = 'add';
        }
        else    
            $this -> data['btn_type_basic'] = 'update';
    }

    private function getBranchPositionData() {

        // model of executive summary branch position
        $this -> exeBranchPositionModel = $this -> model('ExeSummaryBranchPositionModel');

        //get branch position details
        $this -> data['db_exe_branch_position'] = $this -> exeBranchPositionModel -> getAllBranchPosition([
            'where' => 'deleted_at IS NULL AND assesment_id = :assesment_id AND year_id = :year_id',
            'params' => [
                'assesment_id' => $this -> assesmentData -> id,
                'year_id' => $this -> assesmentData -> year_id
            ]
        ]);

        $this -> data['db_exe_branch_position'] = generate_data_assoc_array($this -> data['db_exe_branch_position'], 'type_id');

        // -----------------------------------------

        // model of executive summary ( march position )
        $this -> exeSummaryModel = $this -> model('ExeSummaryModel');

        // march position array
        $this -> data['db_march_position'] = $this -> exeSummaryModel -> getAllMarchPosition([
            'where' => 'deleted_at IS NULL AND year_id = :year_id AND audit_unit_id = :audit_unit_id',
            'params' => [
                'year_id' => $this -> assesmentData -> year_id,
                'audit_unit_id' => $this -> assesmentData -> audit_unit_id,
            ]
        ]);

        $this -> data['db_march_position'] = generate_data_assoc_array($this -> data['db_march_position'], 'gl_type_id', 'march_position');
    }

    private function getFreshAccountsData() {

        // model of executive summary branch position
        $this -> exeFreshAccountModel = $this -> model('ExeSummaryFreshAccountModel');

        // get fresh accounts
        $this -> data['db_exe_fresh_account'] = $this -> exeFreshAccountModel -> getAllFreshAccount([
            'where' => 'deleted_at IS NULL AND assesment_id = :assesment_id AND year_id = :year_id',
            'params' => [
                'assesment_id' => $this -> assesmentData -> id,
                'year_id' => $this -> assesmentData -> year_id
            ]
        ]);

        $this -> data['db_exe_fresh_account'] = generate_data_assoc_array($this -> data['db_exe_fresh_account'], 'type_id');
    }

    // SAVE BAIC DETAILS
    private function saveBasicDetails() {

        // Insert and Update of Basic Details
        $validateBasicArray = [
            'report_submitted_date' => 'required|regex[dateRegex, reportSubmittedDate]',
            'staff_count' => 'required|regex[numberRegex, staffCount]',
            'manual_challans_per_day' => 'required|regex[numberRegex, challansError]',
        ];           

        $basicDataArray = array(
            'year_id' => $this -> assesmentData -> year_id,
            'assesment_id' => $this -> assesmentData -> id,
            'report_submitted_date' => $this -> request -> input('report_submitted_date'),
            'staff_count' => $this -> request -> input('staff_count'),
            'manual_challans_per_day' => $this -> request -> input('manual_challans_per_day'),
            'admin_id' => $this -> empId,
        ); 

        // check validation
        Validation::validateData($this -> request, $validateBasicArray);

        //validation check
        if($this -> request -> input('error') > 0)
            Validation::flashErrorMsg();
        else
        {
            if($this -> request -> has('insertBasicDetails'))
            {
                $result = $this -> exeBasicModel::insert($this -> exeBasicModel -> getTableName(), $basicDataArray);

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to basic details form
                Validation::flashErrorMsg('basicDetailsAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('executiveSummary') . '/audit#basic_details' );
            }
            elseif($this -> request -> has('updateBasicDetails'))
            {
                $result = $this -> exeBasicModel::update($this -> exeBasicModel -> getTableName(), $basicDataArray, [
                    'where' => 'id = :id AND assesment_id = :assesment_id',
                    'params' => [ 
                        'assesment_id' => $this -> assesmentData -> id,
                        'id' => $this -> data['db_exe_basic_details'] -> id,
                    ]
                ]);

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //after updating data redirect to basic details form
                Validation::flashErrorMsg('basicDetailsUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('executiveSummary') . '/audit#basic_details');
            }
            else
            {
                Except::exc_404( Notifications::getNoti('somethingWrong'));
                exit;
            }
        }
    }

    // SAVE BRANCH POSITION
    private function saveBranchPosition($type = 1) {

        // $type = 1 audit, 3 = compliance, 2 = review
        $validateBranchPositionArray = [];
        $branchPositionDataArray = [];
        $branchPositionUpdateDataArray = [];
        $branchTimelineDataArray = [];
        $whereArr = [];
        $checkActionVal = true;        

        foreach($this -> data['branch_financial_position'] as $cGlTypeId => $cGlTypeName)
        {
            if($type == 1) // for audit
            {
                $input_name = 'branch_position_type_' . $cGlTypeId;

                if($this -> request -> has($input_name)):

                    $branchPositionData = array(
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id,
                        'type_id' => $cGlTypeId,
                        'amount' => get_decimal($this -> request -> input($input_name, 0), 2),
                        'business_risk' => 4,
                        'control_risk' => 4,
                        'risk_type' => 1,
                        'audit_status_id' => 1,
                        'audit_emp_id' => $this -> empId,
                        'audit_reviewer_emp_id' => 0,
                        'compliance_emp_id' => 0,
                        'compliance_status_id' => 0,
                        'compliance_reviewer_emp_id' => 0,
                        'batch_key' => $this -> assesmentData -> batch_key,
                    );

                    $branchPositionUpdateData = array(
                        'type_id' => $cGlTypeId,
                        'amount' => get_decimal($this -> request -> input($input_name, 0), 2),
                        'audit_emp_id' => $this -> empId,
                    );

                    $validateBranchPositionArray[ $input_name ] = 'required|regex[floatNumberRegex, branchPositionValue]';
                    $branchPositionDataArray[] = $branchPositionData;
                    $branchPositionUpdateDataArray[ $cGlTypeId ] = $branchPositionUpdateData;
                    
                endif;
            }
            elseif($type == 2) 
            {
                // for review // both audit and compliance
                $input_action_name = ( $this -> assesmentData -> audit_status_id == 2 ? 'review_audit_action_' : 'review_compliance_action_') . $cGlTypeId ;
                $input_comment_name = ( $this -> assesmentData -> audit_status_id == 2 ? 'review_audit_comment_' : 'review_compliance_comment_') . $cGlTypeId;

                if($this -> request -> has($input_action_name)):

                    if( $this -> assesmentData -> audit_status_id == 2 ) // for audit
                        $branchPositionUpdateData = array(
                            'audit_status_id' => $this -> request -> input($input_action_name),
                            'audit_reviewer_emp_id' => $this -> empId,
                            'audit_reviewer_comment' => $this -> request -> input($input_comment_name, NULL),
                        );
                    else // for compliance
                        $branchPositionUpdateData = array(
                            'compliance_status_id' => $this -> request -> input($input_action_name),
                            'compliance_reviewer_emp_id' => $this -> empId,
                            'compliance_reviewer_comment' => $this -> request -> input($input_comment_name, NULL),
                        );

                    // push new batch key
                    $branchPositionUpdateData['batch_key'] = $this -> assesmentData -> batch_key;
                    $branchPositionUpdateDataArray[ $cGlTypeId ] = $branchPositionUpdateData;  

                endif;
            }
            elseif($type == 3) 
            {
                // for compliance
                $input_compliance_comment_name = 'branch_position_comment_type_' . $cGlTypeId;

                if($this -> request -> has($input_compliance_comment_name)):

                    $branchPositionUpdateData = array(
                        'audit_commpliance' => $this -> request -> input($input_compliance_comment_name, NULL),
                        'compliance_emp_id' => $this -> empId,
                        'batch_key' => $this -> assesmentData -> batch_key,
                    );

                    if( isset($this -> data['db_exe_branch_position'][ $cGlTypeId ]) && 
                        $this -> data['db_exe_branch_position'][$cGlTypeId] -> compliance_status_id == 3 )
                        $branchPositionUpdateData['compliance_status_id'] = 3;
                    else
                        $branchPositionUpdateData['compliance_status_id'] = 1;

                    // push new batch key
                    $branchPositionUpdateDataArray[ $cGlTypeId ] = $branchPositionUpdateData;  

                endif;
            }

            // shift to timeline array
            if( in_array($type, [1,3]) && 
                is_array($this -> data['db_exe_branch_position']) &&
                isset($this -> data['db_exe_branch_position'][ $cGlTypeId ]) && (
                    ($type == 1 && $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_status_id == 3) ||
                    ($type == 3 && $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> compliance_status_id == 3)
                ) && 
                    $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> batch_key != $this -> assesmentData -> batch_key )
            {
                // if batch key changed shift answer to the timeline
                $insertArray = [
                    "esbp_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> id,
                    "assesment_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> assesment_id,
                    "last_updated_at" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> updated_at,
                    "answer_type" => $type == 1 ? 1 : 2,
                    "amount" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> amount,
                    "business_risk" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> business_risk,
                    "control_risk" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> control_risk,
                    "risk_type" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> risk_type,
                    "audit_comment" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_comment,
                    "audit_emp_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_emp_id,
                    "audit_status_id" =>  $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_status_id,
                    "audit_reviewer_emp_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_reviewer_emp_id,
                    "audit_reviewer_comment" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_reviewer_comment,
                    "audit_commpliance" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> audit_commpliance,
                    "compliance_emp_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> compliance_emp_id,
                    "compliance_status_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> compliance_status_id,
                    "compliance_reviewer_emp_id" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> compliance_reviewer_emp_id,
                    "compliance_reviewer_comment" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> compliance_reviewer_comment,
                    "batch_key" => $this -> data['db_exe_branch_position'][ $cGlTypeId ] -> batch_key
                ];         
                
                // push data
                $branchTimelineDataArray[] = $insertArray;
                $branchPositionUpdateDataArray[ $cGlTypeId ]['batch_key'] = $this -> assesmentData -> batch_key;
            }
        }

        // check validation
        if(is_array($validateBranchPositionArray) && sizeof($validateBranchPositionArray) > 0)
            Validation::validateData($this -> request, $validateBranchPositionArray);

        // validation check
        if( is_array($validateBranchPositionArray) && 
            sizeof($validateBranchPositionArray) > 0 && 
            $this -> request -> input('error') > 0 )
            Validation::flashErrorMsg();
        else
        {
            if( $type == 1 && 
                $this -> request -> has('insertBranchPosition'))
            {
                $result = $this -> exeBranchPositionModel::insertMultiple($this -> exeBranchPositionModel -> getTableName(), $branchPositionDataArray);

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to branch position details form
                Validation::flashErrorMsg('branchPositionAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('executiveSummary') . '/audit#branch_position' );
            }
            else if($this -> request -> has('updateBranchPosition') || 
                    $this -> request -> has('updateBranchPositionReviewAudit') || 
                    $this -> request -> has('updateBranchPositionReviewCompliance') ||
                    $this -> request -> has('updateBranchPositionCompliance') )
            {
                // check timeline data
                if(is_array($branchTimelineDataArray) && sizeof($branchTimelineDataArray) > 0)
                {
                    // insert in database
                    $model = $this -> model('ExeSummaryBranchPositionTimelineModel');
                    $result = $model::insertMultiple( $model -> getTableName(), $branchTimelineDataArray );

                    if(!$result) $checkActionVal = false;
                }

                if($checkActionVal)
                {
                    $diffArray = array_diff_key($branchPositionUpdateDataArray, $this -> data['db_exe_branch_position']);
                    $updateDataArray = array_diff_key($branchPositionUpdateDataArray, $diffArray);
                    $result = false;

                    if( empty($diffArray) && 
                        is_array($this -> data['db_exe_branch_position']) && 
                        sizeof($this -> data['db_exe_branch_position']) > 0 )
                    {
                        $whereArr = [];

                        foreach($this -> data['db_exe_branch_position'] as $cTypeIdDetails)
                        {
                            if(array_key_exists($cTypeIdDetails -> type_id, $updateDataArray))
                            {
                                $whereArr[ $cTypeIdDetails -> type_id ] = array(
                                    'where' => 'id = :id AND type_id = :type_id AND assesment_id = :assesment_id  AND year_id = :year_id',
                                    'params' => [ 
                                        'id' => $cTypeIdDetails -> id,
                                        'type_id' => $cTypeIdDetails -> type_id,
                                        'year_id' => $this -> assesmentData -> year_id,
                                        'assesment_id' => $this -> assesmentData -> id,
                                    ]
                                );
                            }
                        }

                        // update in database
                        $result = $this -> exeBranchPositionModel::updateMultiple($this -> exeBranchPositionModel -> getTableName(), $updateDataArray, $whereArr);
                    }

                    if(!$result)
                    {
                        Except::exc_404( Notifications::getNoti('somethingWrong'));
                        exit;
                    }

                    // after insert data redirect to branch position details form
                    Validation::flashErrorMsg('branchPositionUpdatedSuccess', 'success');

                    // for audit
                    $redirectUrl =  '/audit#branch_position';

                    if( $type == 2 )
                        $redirectUrl = ($this -> assesmentData -> audit_status_id == 2) ? '/review-audit#branch_position' : '/review-compliance#branch_position';
                    elseif( $type == 3 )
                        $redirectUrl = '/compliance#branch_position';

                    $redirectUrl = SiteUrls::getUrl('executiveSummary') . $redirectUrl;
                    Redirect::to( $redirectUrl );

                }
                else
                {
                    Except::exc_404( Notifications::getNoti('somethingWrong'));
                    exit;
                }
            }
            else
            {
                Except::exc_404( Notifications::getNoti('somethingWrong'));
                exit;
            }
        }
    }

    // SAVE FRESH ACCOUNTS
    private function saveFreshAccounts($type = 1) {

        // $type = 1 audit, 3 = compliance, 2 = reviewer
        $validateFreshAccountsArray = [];
        $freshAccountDataArray = [];
        $freshAccountUpdateDataArray = [];
        $branchTimelineDataArray = [];
        $whereArr = [];
        $checkActionVal = true;        

        foreach($this -> data['branch_fresh_accounts'] as $cGlTypeId => $cGlTypeName)
        {
            if($type == 1) // for audit
            {
                $input_name = 'fresh_account_type_' . $cGlTypeId;

                if($this -> request -> has($input_name)):

                    $freshAccountData = array(
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id,
                        'type_id' => $cGlTypeId,
                        'accounts' => $this -> request -> input($input_name, 0),
                        'business_risk' => 4,
                        'control_risk' => 4,
                        'risk_type' => 1,
                        'audit_emp_id' => Session::get('emp_id'),
                        'audit_status_id' => 1,
                        'audit_reviewer_emp_id' => 0,
                        'compliance_emp_id' => 0,
                        'compliance_status_id' => 0,
                        'compliance_reviewer_emp_id' => 0,
                        'batch_key' => $this -> assesmentData -> batch_key,
                    );

                    $freshAccountUpdateData = array(
                        'type_id' => $cGlTypeId,
                        'accounts' => $this -> request -> input($input_name, 0),
                        'audit_emp_id' => Session::get('emp_id'),
                    );

                    $validateFreshAccountsArray[ $input_name ] = 'required|regex[numberRegex, freshAccountValue]';      

                    $freshAccountDataArray[] = $freshAccountData;
                    $freshAccountUpdateDataArray[ $cGlTypeId ] = $freshAccountUpdateData;
                
                endif;
            }
            elseif($type == 2) 
            {
                // for review // both audit and compliance
                $input_action_name = ( $this -> assesmentData -> audit_status_id == 2 ? 'review_audit_action_' : 'review_compliance_action_') . $cGlTypeId ;
                $input_comment_name = ( $this -> assesmentData -> audit_status_id == 2 ? 'review_audit_comment_' : 'review_compliance_comment_') . $cGlTypeId;

                if($this -> request -> has($input_action_name)):

                    if( $this -> assesmentData -> audit_status_id == 2 ) // for audit
                        $freshAccountDataArray = array(
                            'audit_status_id' => $this -> request -> input($input_action_name),
                            'audit_reviewer_emp_id' => $this -> empId,
                            'audit_reviewer_comment' => $this -> request -> input($input_comment_name, NULL),
                        );
                    else // for compliance
                        $freshAccountDataArray = array(
                            'compliance_status_id' => $this -> request -> input($input_action_name),
                            'compliance_reviewer_emp_id' => $this -> empId,
                            'compliance_reviewer_comment' => $this -> request -> input($input_comment_name, NULL),
                        );

                    // push new batch key
                    $freshAccountDataArray['batch_key'] = $this -> assesmentData -> batch_key;
                    $freshAccountUpdateDataArray[ $cGlTypeId ] = $freshAccountDataArray;

                endif;
            }
            elseif($type == 3)
            {
                // for compliance
                $gl_input_fresh_comp_comment_name = 'fresh_account_comment_type_' . $cGlTypeId;

                if($this -> request -> has($gl_input_fresh_comp_comment_name)):

                    $freshAccountData = array(
                        'audit_commpliance' => $this -> request -> input($gl_input_fresh_comp_comment_name, NULL),
                        'compliance_emp_id' => Session::get('emp_id'),
                        'batch_key' => $this -> assesmentData -> batch_key,
                    );

                    if( isset($this -> data['db_exe_fresh_account'][ $cGlTypeId ]) && 
                        $this -> data['db_exe_fresh_account'][$cGlTypeId] -> compliance_status_id == 3 )
                        $freshAccountData['compliance_status_id'] = 3;
                    else
                        $freshAccountData['compliance_status_id'] = 1;

                    // push new batch key
                    $freshAccountUpdateDataArray[ $cGlTypeId ] = $freshAccountData;  

                endif;
            }

            // shift to timeline array
            if( in_array($type, [1,3]) && 
                is_array($this -> data['db_exe_fresh_account']) &&
                isset($this -> data['db_exe_fresh_account'][ $cGlTypeId ]) && (
                    ($type == 1 && $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_status_id == 3) ||
                    ($type == 3 && $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> compliance_status_id == 3)
                ) && 
                    $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> batch_key != $this -> assesmentData -> batch_key )
            {
                // if batch key changed shift answer to the timeline
                $insertArray = [
                    "esfa_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> id,
                    "assesment_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> assesment_id,
                    "last_updated_at" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> updated_at,
                    "answer_type" => $type == 1 ? 1 : 2, // for compliance
                    "accounts" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> accounts,
                    "business_risk" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> business_risk,
                    "control_risk" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> control_risk,
                    "risk_type" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> risk_type,
                    "audit_comment" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_comment,
                    "audit_emp_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_emp_id,
                    "audit_status_id" =>  $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_status_id,
                    "audit_reviewer_emp_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_reviewer_emp_id,
                    "audit_reviewer_comment" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_reviewer_comment,
                    "audit_commpliance" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> audit_commpliance,
                    "compliance_emp_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> compliance_emp_id,
                    "compliance_status_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> compliance_status_id,
                    "compliance_reviewer_emp_id" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> compliance_reviewer_emp_id,
                    "compliance_reviewer_comment" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> compliance_reviewer_comment,
                    "batch_key" => $this -> data['db_exe_fresh_account'][ $cGlTypeId ] -> batch_key,
                ];
                
                // push data
                $branchTimelineDataArray[] = $insertArray;
                $freshAccountUpdateDataArray[ $cGlTypeId ]['batch_key'] = $this -> assesmentData -> batch_key;
            }
        }

        // check validation
        if( is_array($validateFreshAccountsArray) && 
            sizeof($validateFreshAccountsArray) > 0 )
            Validation::validateData($this -> request, $validateFreshAccountsArray);

        // validation check
        if( is_array($validateFreshAccountsArray) && 
            sizeof($validateFreshAccountsArray) > 0 && 
            $this -> request -> input('error') > 0)
            Validation::flashErrorMsg();
        else
        {
            if($type == 1 && $this -> request -> has('insertFreshAccounts'))
            {
                $result = $this -> exeFreshAccountModel::insertMultiple($this -> exeFreshAccountModel -> getTableName(), $freshAccountDataArray);

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                // after insert data redirect to fresh account details form
                Validation::flashErrorMsg('freshAccountAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('executiveSummary') . '/audit#fresh_accounts' );
            }
            else if($this -> request -> has('updateFreshAccounts') || 
                    $this -> request -> has('updateFreshAccountsReviewAudit') || 
                    $this -> request -> has('updateFreshAccountsReviewCompliance') ||
                    $this -> request -> has('updateFreshAccountsCompliance') )
            {
                // check timeline data
                if(is_array($branchTimelineDataArray) && sizeof($branchTimelineDataArray) > 0)
                {
                    // insert in database
                    $model = $this -> model('ExeSummaryFreshAccountTimelineModel');
                    $result = $model::insertMultiple( $model -> getTableName(), $branchTimelineDataArray );

                    if(!$result) $checkActionVal = false;
                }

                if($checkActionVal)
                {
                    $diffArray = array_diff_key($freshAccountUpdateDataArray, $this -> data['db_exe_fresh_account']);
                    $updateDataArray = array_diff_key($freshAccountUpdateDataArray, $diffArray);
                    $result = false;

                    if( empty($diffArray) && 
                        is_array($this -> data['db_exe_fresh_account']) && 
                        sizeof($this -> data['db_exe_fresh_account']) > 0 )
                    {
                        $whereArr = [];

                        foreach($this -> data['db_exe_fresh_account'] as $cTypeIdDetails)
                        {
                            if(array_key_exists($cTypeIdDetails -> type_id, $updateDataArray))
                            {
                                $whereArr[$cTypeIdDetails -> type_id] = array(
                                    'where' => 'id = :id AND type_id = :type_id AND assesment_id = :assesment_id  AND year_id =:year_id',
                                    'params' => [ 
                                        'id' => $cTypeIdDetails -> id,
                                        'type_id' => $cTypeIdDetails -> type_id,
                                        'year_id' => $this -> assesmentData -> year_id,
                                        'assesment_id' => $this -> assesmentData -> id,
                                    ]
                                );
                            }
                        }

                        // update in database
                        $result = $this -> exeFreshAccountModel::updateMultiple($this -> exeFreshAccountModel -> getTableName(), $updateDataArray, $whereArr);
                    }

                    if(!$result)
                    {
                        Except::exc_404( Notifications::getNoti('somethingWrong'));
                        exit;
                    }

                    // after insert data redirect to branch fresh accounts details form
                    Validation::flashErrorMsg('freshAccountUpdatedSuccess', 'success');

                    // for audit
                    $redirectUrl =  '/audit#fresh_accounts';

                    if( $type == 2 )
                        $redirectUrl = ($this -> assesmentData -> audit_status_id == 2) ? '/review-audit#fresh_accounts' : '/review-compliance#fresh_accounts';
                    elseif( $type == 3 )
                        $redirectUrl = '/compliance#fresh_accounts';

                    $redirectUrl = SiteUrls::getUrl('executiveSummary') . $redirectUrl;
                    Redirect::to( $redirectUrl );
                }
                else
                {
                    Except::exc_404( Notifications::getNoti('somethingWrong'));
                    exit;
                }
            }
            else
            {
                Except::exc_404( Notifications::getNoti('somethingWrong'));
                exit;
            }
        }
    }
    
    public function audit() 
    {
        // FOR AUDIT // method call
        $this -> checkAccessRestrict();

        // GET ASSESMENT DATA // method call
        $this -> getAssesmentData( Session::get('audit_id') );

        // CHECK MENU = 1 ACTIVE
        $this -> checkMenuActive();

        $reAuditFindData = null;

        if($this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[3]['status_id'])
            $reAuditFindData = $this -> reAuditFindData(null, 1);

        if( is_array($reAuditFindData) && 
            is_array($reAuditFindData['menu']) && 
            !sizeof($reAuditFindData['menu']) > 0 )
        {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        $this -> menuData = $this -> getMenuData($reAuditFindData); // method call

        // method call // GET BASIC DETAILS
        $this -> getBasicDetailsData();
        $this -> getBranchPositionData();
        $this -> getFreshAccountsData();

        // post method after form submit
        $this -> request::method("POST", function() {

            if( $this -> request -> has('insertBasicDetails') || 
                $this -> request -> has('updateBasicDetails'))
            {
                // method call
                $this -> saveBasicDetails();
            }
            elseif( $this -> request -> has('insertBranchPosition') || 
                    $this -> request -> has('updateBranchPosition'))
            {
                // method call
                $this -> saveBranchPosition();
            }
            elseif( $this -> request -> has('insertFreshAccounts') || 
                    $this -> request -> has('updateFreshAccounts'))
            {
                // method call
                $this -> saveFreshAccounts();
            }
        });

        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'assesmentData' => $this -> assesmentData,
            'menu_data' => $this -> menuData,
        ]);
    }

    public function reviewAudit()
    {
        // FOR AUDIT // method call
        $this -> checkAccessRestrict(2);

        // GET ASSESMENT DATA // method call
        $this -> getAssesmentData( Session::get('audit_id') );

        // CHECK MENU = 1 ACTIVE
        $this -> checkMenuActive();

        // menuKey for active menu
        $this -> me -> menuKey = 'executiveSummary';

        // method call // GET BASIC DETAILS
        $this -> getBasicDetailsData();
        $this -> getBranchPositionData();
        $this -> getFreshAccountsData();

        // post method after form submit
        $this -> request::method("POST", function() {

            if($this -> request -> has('updateBranchPositionReviewAudit'))
            {
                // method call
                $this -> saveBranchPosition(2);
            }
            elseif($this -> request -> has('updateFreshAccountsReviewAudit'))
            {
                // method call
                $this -> saveFreshAccounts(2);
            }
        });

        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'assesmentData' => $this -> assesmentData,
            'menu_data' => $this -> menuData,
        ]);
    }

    public function compliance()
    {
        // FOR AUDIT // method call
        $this -> checkAccessRestrict(3);

        // GET ASSESMENT DATA // method call
        $this -> getAssesmentData( Session::get('audit_id') );

        // CHECK MENU = 1 ACTIVE
        $this -> checkMenuActive();

        // menuKey for active menu
        $this -> me -> menuKey = 'executiveSummary';

        // method call // GET BASIC DETAILS
        $this -> getBasicDetailsData();
        $this -> getBranchPositionData();
        $this -> getFreshAccountsData();

        $this -> request::method("POST", function(){

            if($this -> request -> has('updateBranchPositionCompliance'))
            {
                // method call
                $this -> saveBranchPosition(3);
            }
            elseif($this -> request -> has('updateFreshAccountsCompliance'))
            {
                // method call
                $this -> saveFreshAccounts(3);
            }

        });

        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'assesmentData' => $this -> assesmentData,
            'menu_data' => $this -> menuData,
        ]);
    }

    public function reviewCompliance()
    {
        // FOR AUDIT // method call
        $this -> checkAccessRestrict(2);

        // GET ASSESMENT DATA // method call
        $this -> getAssesmentData( Session::get('audit_id') );

        // CHECK MENU = 1 ACTIVE
        $this -> checkMenuActive();

        // menuKey for active menu
        $this -> me -> menuKey = 'executiveSummary';

        // method call // GET BASIC DETAILS
        $this -> getBasicDetailsData();
        $this -> getBranchPositionData();
        $this -> getFreshAccountsData();

        // post method after form submit
        $this -> request::method("POST", function() {

            if($this -> request -> has('updateBranchPositionReviewCompliance'))
            {
                // method call
                $this -> saveBranchPosition(2);
            }
            elseif($this -> request -> has('updateFreshAccountsReviewCompliance'))
            {
                // method call
                $this -> saveFreshAccounts(2);
            }
        });

        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'assesmentData' => $this -> assesmentData,
            'menu_data' => $this -> menuData,
        ]);
    }
}

?>