<?php

namespace Controllers\Reviewer;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

// need audit report helper 25.08.2024
require_once APP_CORE . DS . 'HelperFunctionsAuditReport.php';

class ReviewerController extends Controller  {

    public $me = null, $request, $data, $assesId, $assesmentData, $empId;
    public $auditAssesmentModel, $auditAssesmentTimelineModel, $esbpModel, $esfaModel, $answerDataModel, $answerDataAnnexureModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> auditAssesmentModel = $this -> model('AuditAssesmentModel');
        $this -> auditAssesmentTimelineModel = $this -> model('AuditAssesmentTimelineModel');
        $this -> esbpModel = $this -> model('ExeSummaryBranchPositionModel');
        $this -> esfaModel = $this -> model('ExeSummaryFreshAccountModel');
        $this -> answerDataModel = $this -> model('AnswerDataModel');
        $this -> answerDataAnnexureModel = $this -> model('AnswerDataAnnexureModel');
        $this -> empId = Session::get('emp_id');
    }

    private function checkAudit()
    {
        $status = false;
        $empType = Session::get('emp_type');

        if( $empType == 4 && 
            $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[2]['status_id'] ) // review audit
            $status = true;

        else if($empType == 4 && 
            $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'] ) // review compliance
            $status = true;

        else if($empType == 16 && 
            $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[15]['status_id'] ) // RO officer review
             $status = true;

        return $status;
    }

    private function getAssesmentData($withoutExcept = true)
    {
        // get data from session
        $this -> assesId = decrypt_ex_data(Session::get('audit_id'));

        // helper function call
        $this -> assesmentData = get_assesment_details($this, $this -> empId, $this -> assesId);

        if( $withoutExcept && !is_object($this -> assesmentData) )
        {
            Except::exc_404( Notifications::getNoti($this -> assesmentData) );
            exit;
        }
    }

    public function index()
    {
        Except::exc_access_restrict( );
        exit;        
    }

    public function saveStatus()
{
    $this -> getAssesmentData(0); //method call

    $res_array = [ 'msg' => Notifications::getNoti('somethingWrong'), 'res' => 'err' ];
    
    $requestData = isset($_POST['data']) ? $_POST['data'] : null;

    if( !is_object($this -> assesmentData) || empty($requestData) )
    {
        echo json_encode($res_array);
        exit;
    }

    $requestData = json_decode($requestData);

    $checkActionVal = false;

    // audit action
    if( isset($requestData -> slctact) && $requestData -> slctact == 'aud' && 
        array_key_exists($requestData -> action, AUDIT_STATUS_ARRAY['audit_review_action']) )
        $checkActionVal = true;

    // compliance action
    if( isset($requestData -> slctact) && $requestData -> slctact == 'com' && 
        array_key_exists($requestData -> action, AUDIT_STATUS_ARRAY['compliance_review_action']) )
        $checkActionVal = true;

    // RO Audit action
    if( isset($requestData -> slctact) && $requestData -> slctact == 'ro_audit' && 
        array_key_exists($requestData -> action, AUDIT_STATUS_ARRAY['compliance_review_action']) )
        $checkActionVal = true;

    // RO Compliance action
    if( isset($requestData -> slctact) && $requestData -> slctact == 'ro_compliance' && 
        array_key_exists($requestData -> action, AUDIT_STATUS_ARRAY['compliance_review_action']) )
        $checkActionVal = true;

    if( $checkActionVal && (isset($requestData -> ans_id) && !empty($requestData -> ans_id)) &&
        (isset($requestData -> ans_type) && !empty($requestData -> ans_type)) &&
        in_array( $requestData -> ans_type, ['gen', 'annex'] )
    )
    {          
        $columnTab = 'audit_status_id';
        $updateEmp = 'audit_reviewer_emp_id';
        $findAnnexDetails = null;
        $annexUpdate = true;

        // Check for RO status first
        if( isset($requestData -> slctact) && in_array($requestData -> slctact, ['ro_audit', 'ro_compliance']) )
        {
            if($requestData -> slctact == 'ro_audit')
            {
                $columnTab = 'ro_audit_status_id';
                $updateEmp = 'ro_review_emp_id';
            }
            else
            {
                $columnTab = 'ro_compliance_status_id';
                $updateEmp = 'ro_review_emp_id';
            }
        }
        else if( $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 5 ]['status_id'] || 
            $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 15 ]['status_id'] )
        {
            $columnTab = 'compliance_status_id';
            $updateEmp = 'compliance_reviewer_emp_id';
        }
        
        // if($requestData -> ans_type == 'gen')
        $model = $this -> model('AnswerDataModel');
        $annexModel = $this -> model('AnswerDataAnnexureModel');
        $requestData -> ans_id = decrypt_ex_data($requestData -> ans_id);

        if($requestData -> ans_type == 'annex')
        {
            // for annexure // get annex details
            $findAnnexDetails = $annexModel -> getSingleAnswerAnnexure([
                'where' => 'id = :id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                'params' => [ 
                    'assesment_id' => $this -> assesmentData -> id,
                    'id' => $requestData -> ans_id
                ]
            ]);

            if(is_object($findAnnexDetails))
            {
                $status = 2; // default accepted

                // find other annex answer
                $findAllAnnexDetails = $annexModel -> getAllAnswerAnnexures([
                    'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND id != :id AND deleted_at IS NULL',
                    'params' => [ 
                        'assesment_id' => $this -> assesmentData -> id,
                        'answer_id' =>  $findAnnexDetails -> answer_id,
                        'id' =>  $findAnnexDetails -> id,
                    ]
                ]);

                if(is_array($findAllAnnexDetails) && sizeof($findAllAnnexDetails) > 0)
                {
                    foreach($findAllAnnexDetails as $cAnnexDetails)
                    {
                        if(in_array($cAnnexDetails -> $columnTab, [3]))
                        {
                            // other than accept // $status = 2
                            $status = $cAnnexDetails -> $columnTab;
                            break;
                        }
                    }
                }

                if( $status == 2 && in_array($requestData -> action, [3]) )
                    $status = $requestData -> action;

                $whereData = [
                    'where' => 'id = :id AND assesment_id = :assesment_id',
                    'params' => [ 
                        'assesment_id' => $this -> assesmentData -> id,
                        'id' =>  $requestData -> ans_id 
                    ]
                ];

                $updateDBArray = [
                    $columnTab => $requestData -> action,
                    $updateEmp => $this -> empId
                ];

                // only for review compliance (not for RO)
                if( !in_array($requestData -> slctact, ['ro_audit', 'ro_compliance']) && 
                    ($this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 5 ]['status_id'] || 
                    $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 15 ]['status_id']) )
                    $updateDBArray['batch_key'] = $this -> assesmentData -> batch_key;

                // update current annex        
                $result = $annexModel::update(
                    $annexModel -> getTableName(), $updateDBArray, $whereData );

                if(!$result)
                    $annexUpdate = false;
                else
                {
                    $requestData -> ans_id = $findAnnexDetails -> answer_id;
                    $requestData -> action = $status;
                }
            }
            else
                $annexUpdate = false;
        }
        
        $result = false;

        if($annexUpdate)
        {
            $whereData = [
                'where' => 'id = :id AND assesment_id = :assesment_id',
                'params' => [ 
                    'assesment_id' => $this -> assesmentData -> id,
                    'id' =>  $requestData -> ans_id 
                ]
            ];

            $updateDBArray = [
                $columnTab => $requestData -> action,
                $updateEmp => $this -> empId
            ];

            // only for review compliance (not for RO)
            if( !in_array($requestData -> slctact, ['ro_audit', 'ro_compliance']) && 
                ($this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 5 ]['status_id'] || 
                $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 15 ]['status_id']) )
                $updateDBArray['batch_key'] = $this -> assesmentData -> batch_key;

            // check for on hold and carry forward // it will be accepted
            if( is_object($findAnnexDetails) && 
                in_array($updateDBArray[ $columnTab ], [4,5]) )
                $updateDBArray[ $columnTab ] = 2;
            
            $result = $model::update(
                $model -> getTableName(), $updateDBArray, $whereData
            );

            if($result && $requestData -> ans_type == 'gen')
            {
                // check for annex
                $findAllAnnexDetails = $annexModel -> getAllAnswerAnnexures([
                    'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => [ 
                        'assesment_id' => $this -> assesmentData -> id,
                        'answer_id' => $requestData -> ans_id,
                    ]
                ]);

                if(is_array($findAllAnnexDetails) && sizeof($findAllAnnexDetails) > 0)
                {
                    // update all annex data
                    $whereData = [
                        'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                        'params' => [ 
                            'assesment_id' => $this -> assesmentData -> id,
                            'answer_id' =>  $requestData -> ans_id 
                        ]
                    ];

                    $updateDBArray = [
                        $columnTab => $requestData -> action,
                        $updateEmp => $this -> empId
                    ];
    
                    // only for review compliance (not for RO)
                    if( !in_array($requestData -> slctact, ['ro_audit', 'ro_compliance']) && 
                        ($this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 5 ]['status_id'] || 
                        $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[ 15 ]['status_id']) )
                        $updateDBArray['batch_key'] = $this -> assesmentData -> batch_key;

                    // update all annex        
                    $result = $annexModel::update(
                        $annexModel -> getTableName(), $updateDBArray, $whereData
                    );
                }
            }
        }

        if($result !== false)
        {
            $res_array['msg'] = Notifications::getNoti("reviewActionSavedSuccess");
            $res_array['res'] = "success";
        }
        else
            $res_array['msg'] = Notifications::getNoti("reviewFailedSaveeSuccess");
    }

    echo json_encode($res_array);
    exit;
}

public function saveComment()
{
    $this -> getAssesmentData(0); //method call

    $res_array = [ 'msg' => Notifications::getNoti('somethingWrong'), 'res' => 'err' ];
    
    $requestData = isset($_POST['data']) ? $_POST['data'] : null;

    if( !is_object($this -> assesmentData) || empty($requestData) )
    {
        echo json_encode($res_array);
        exit;
    }

    // Decode request data to check slctact
    $requestDataDecoded = json_decode($requestData);

    if( in_array($this -> assesmentData -> audit_status_id, [2]) )
        $type = 'aud_rew';
    elseif( in_array($this -> assesmentData -> audit_status_id, [5]) )
        $type = 'com_rew';
    elseif( in_array($this -> assesmentData -> audit_status_id, [15]) )
    {
        // For RO officer review - check slctact to determine correct type
        if(isset($requestDataDecoded->slctact)) {
            if($requestDataDecoded->slctact == 'ro_audit_comment') {
                $type = 'ro_audit_comment';
            } elseif($requestDataDecoded->slctact == 'ro_compliance_comment') {
                $type = 'ro_compliance_comment';
            } else {
                $type = 'ro_audit_comment'; // default
            }
        } else {
            $type = 'ro_audit_comment';
        }
    }

    // for compliance
    $res_array = save_assesment_message($this, $this -> empId, $requestData, $type);

    // change notification
    $res_array['msg'] = Notifications::getNoti($res_array['msg']);

    echo json_encode($res_array);
    exit;
}
    private function findData($filterType)
    {
        $this -> getAssesmentData(); //method call

        if(!$this -> checkAudit())
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // helper function call
        $dataArray = find_audit_observations($this, $this -> assesmentData, $filterType);

        $this -> data['data_array'] = $dataArray;
        $this -> data['assesmentData'] = $this -> assesmentData;       

        // unset vars
        unset($dataArray);

        //need audit assesment js
        $this -> data['js'][] = 'report_reviewer_action_script.js';
        $this -> data['js'][] = 'compliance_comment_save_script.js';

        // top data container
        $this -> data['data_container'] = true;
    }
    
    private function findCountAssign($statusCol, $model, $type = 'answer_data') {

    $table = $model -> getTableName();
    $res = [ 'accepted' => 0, 'rejected' => 0, 'onhold' => 0, 'cf_point' => 0 ];

    $where = 'assesment_id = :assesment_id';
    
    if( $type == 'answer_data' )
        $where .= ' AND is_compliance = 1';
    
    // Check if we're counting RO status and this is annexure table - skip annexure for RO
    $empType = Session::get('emp_type');
    $isROStatus = ($statusCol == 'ro_compliance_status_id' || $statusCol == 'ro_audit_status_id');
    
    if($isROStatus && $type != 'answer_data') {
        // Skip annexure for RO status since column doesn't exist
        return $res;
    }
    
    $where .= ' AND '. $statusCol .' IN (2,3'. (($statusCol == 'compliance_status_id' && !in_array($type, ['esbp', 'esfa'])) ? ',4,5' : '') .') AND deleted_at IS NULL GROUP BY ' . $statusCol;

    $result = get_all_data_query_builder(2, $model, $table, [
        'where' => $where, 
        'params' => [ 'assesment_id' => $this -> assesmentData -> id ]
    ], 'sql', "SELECT ". $statusCol .", COUNT(*) AS count FROM ". $table);

    if(is_array($result) && sizeof($result) > 0)
    {
        foreach($result as $row)
        {
            if( $row -> { $statusCol } == 2 )
                $res['accepted'] += $row -> count;
            else if( $row -> { $statusCol } == 4 )
                $res['onhold'] += $row -> count;
            else if( $row -> { $statusCol } == 5 )
                $res['cf_point'] += $row -> count;
            else
                $res['rejected'] += $row -> count;
        }
    }

    return $res;
}

private function findDataCount($type = 'audit') 
{
    $this -> getAssesmentData(); //method call

    if(!$this -> checkAudit())
    {
        Except::exc_404( Notifications::getNoti('errorFinding') );
        exit;
    }

    $empType = Session::get('emp_type');
    
    // For RO Officer (emp_type == 16), use ro_compliance_status_id instead of compliance_status_id
    if($empType == 16 && $this->assesmentData->audit_status_id == 15) {
        $statusCol = 'ro_compliance_status_id';
    } else {
        $statusCol = ($type == 'audit') ? 'audit_status_id' : 'compliance_status_id';
    }

    $this -> data['observation'] = [
        'esbp' => [ 'accepted' => 0, 'rejected' => 0 ],
        'esfa' => [ 'accepted' => 0, 'rejected' => 0 ],
        'observation' => [ 'accepted' => 0, 'rejected' => 0, 'onhold' => 0, 'cf_point' => 0 ],
    ];

    $menuIds = null;

    if(!empty($this -> assesmentData -> menu_ids))
        $menuIds = explode(',', $this -> assesmentData -> menu_ids);

    if(is_array($menuIds) && in_array(1, $menuIds))
    {
        $this -> data['observation']['esbp'] = $this -> findCountAssign($statusCol, $this -> esbpModel, 'esbp');
        $this -> data['observation']['esbp']['title'] = 'Executive Summary Branch Position';

        $this -> data['observation']['esfa'] = $this -> findCountAssign($statusCol, $this -> esfaModel, 'esfa');
        $this -> data['observation']['esfa']['title'] = 'Executive Summary Fresh Accounts';
    }
    else
        unset($this -> data['observation']['esbp'], $this -> data['observation']['esfa']);

    // for general answer (main table - has RO columns)
    $this -> data['observation']['observation'] = $this -> findCountAssign($statusCol, $this -> answerDataModel);

    // for annexure - only count if NOT RO status (since annexure doesn't have RO columns)
    if($statusCol != 'ro_compliance_status_id' && $statusCol != 'ro_audit_status_id') {
        $res = $this -> findCountAssign($statusCol, $this -> answerDataAnnexureModel, 'annex');
        $this -> data['observation']['observation']['accepted'] += $res['accepted'];
        $this -> data['observation']['observation']['rejected'] += $res['rejected'];
        $this -> data['observation']['observation']['onhold'] += $res['onhold'];
        $this -> data['observation']['observation']['cf_point'] += $res['cf_point'];
    }

    $title = ($empType == 16 && $this->assesmentData->audit_status_id == 15) ? 'Total Compliance Observations (RO Review)' : 'Total ' . (($type == 'audit') ? 'Audit' : 'Compliance') . ' Observations';
    $this -> data['observation']['observation']['title'] = $title;
}
    private function defaultAcceptAll($type = 'audit', $extra = [])
{
    $status = isset($extra['status']) ? $extra['status'] : 2; // accepted
    $res = false;
    
    // Check if user is RO Officer
    $empType = Session::get('emp_type');
    $isROUser = ($empType == 16 && $this->assesmentData->audit_status_id == 15);
    
    // Determine which status column to update
    if($isROUser) {
        // For RO Officer - update RO status columns
        if($type == 'audit') {
            $updateArray = [ 'ro_audit_status_id' => $status ];
        } else {
            $updateArray = [ 'ro_compliance_status_id' => $status ];
        }
    } else {
        // For regular reviewers
        if($type != 'audit') {   
            $updateArray = [ 'compliance_status_id' => $status ];
        } else {
            $updateArray = [ 'audit_status_id' => $status ];
        }
    }
    
    $whereArray = [
        'where' => 'assesment_id = :assesment_id AND deleted_at IS NULL',
        'params' => [ 'assesment_id' => $this->assesmentData->id ]
    ];
    
    // Only add the status filter if NOT forcing all
    if(!isset($extra['forceAll'])) {
        if($isROUser) {
            if($type == 'audit') {
                $whereArray['where'] .= ' AND ro_audit_status_id = "0"';
            } else {
                $whereArray['where'] .= ' AND ro_compliance_status_id = "0"';
            }
        } else {
            if($type != 'audit') {
                $whereArray['where'] .= ' AND compliance_status_id = "0"';
            } else {
                $whereArray['where'] .= ' AND audit_status_id = "0"';
            }
        }
    }
    
    // Skip ESBP/ESFA for RO users as they don't have RO columns
    if(!$isROUser && !isset($extra['forceAll'])) {
        // executive summary branch position default accept 0 to 2
        $this->esbpModel::update(
            $this->esbpModel->getTableName(), 
            $updateArray, $whereArray
        );
        
        // executive summary fresh accounts default accept 0 to 2
        $this->esfaModel::update(
            $this->esfaModel->getTableName(), 
            $updateArray, $whereArray
        );
    }
    
    // For answer data
    $adWhere = $whereArray;
    if(isset($extra['forceAll'])) {
        $adWhere['where'] .= ' AND is_compliance = "1"';
    }
    
    $result = $this->answerDataModel::update(
        $this->answerDataModel->getTableName(), 
        $updateArray, $adWhere
    );
    
    // For answer data annexure - skip for RO since annexure doesn't have RO columns
    if(!$isROUser) {
        $this->answerDataAnnexureModel::update(
            $this->answerDataAnnexureModel->getTableName(), 
            $updateArray, $whereArray
        );
    }
    
    if($result !== false) {
        $res = true;
    }
    
    return $res;
}

    private function observationActionValidation()
    {
        // short action submited // validate
        $validationArray = [ 'observationAction' => 'required|array_key[observation_action_array, observationAction]' ];
        
        Validation::validateData($this -> request, $validationArray,
        [ 'observation_action_array' => $this -> data[ 'review_timeline_status' ] ]);

        //validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    private function observationActionSubmit($type = 'audit') {

        if($this -> request -> has('observationActionSubmit'))
        {
            // short action submited // validate
            if($this -> observationActionValidation()) 
            {
                // validation passed
                $extra = [ 'status' => 2, 'forceAll' => true ];

                // for reject all
                if( $this -> request -> input('observationAction') == 2 )
                    $extra['status'] = 3;

                $res = $this -> defaultAcceptAll($type, $extra);

                if(!$res)
                {
                    Except::exc_404( Notifications::getNoti('errorSaving') );
                    exit;
                }
                else
                {
                    Validation::flashErrorMsg( Notifications::getNoti('observationActionSubmitted'), 'success' );

                    // re direct due to status not changing 25.08.2024
                    $url = SiteUrls::getUrl('reviewer');
                    $url .= ($type == 'audit') ? '/review-audit' : '/review-compliance';
                    Redirect::to( $url );
                }
            }
        }
    }

private function assesmentTimelineAction($extra, $type = 'audit')
{
    // type = 1 audit, 2 = compliance, 0 = back to re audit

    $insertTimelineArray = array(
        'id' => $this -> assesmentData -> id,
        'type' => 1, // default 1 for audit
        'status' => ASSESMENT_TIMELINE_ARRAY[ $extra['timeline_status'] ]['status_id'],
        'rejected_cnt' => 0,
        'emp_id' => $this -> empId,
        'batch_key' => $this -> assesmentData -> batch_key,
    );

    $emptyAuditAssesment = false;

    if(!in_array($type, ['sendBackAudit']))
    {
        // count rejected
        foreach($this -> data['observation'] as $cObv => $cObvDetails) {
            $insertTimelineArray['rejected_cnt'] += $cObvDetails['rejected'];

            if( $cObv == 'observation' && 
                $cObvDetails['accepted'] == 0 && 
                $cObvDetails['rejected'] == 0)
                $emptyAuditAssesment = true;
        }

        if($type == 'audit')
        {
            // for audit
            if($insertTimelineArray['rejected_cnt'] > 0) // back to re audit
                $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 3 ]['status_id'];
            else
            { 
                // mark complete audit
                if( $this -> data['observation']['observation']['accepted'] == 0 &&
                    $this -> data['observation']['observation']['rejected'] == 0 )
                    $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 7 ]['status_id'];
                else // send to compliance
                    $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 4 ]['status_id'];
            }
        }
        else
        {
            // for compliance
            $insertTimelineArray['type'] = 2;
            
            // Check if audit_status_id is 15 (RO officer review)
            if($this -> assesmentData -> audit_status_id == 15)
            {
                // For RO officer review - check rejected count
                if($insertTimelineArray['rejected_cnt'] > 0) {
                    // Has rejections - back to re compliance
                    $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 6 ]['status_id'];
                } else {
                    // No rejections - proceed to complete
                    $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 5 ]['status_id'];
                }
            }
            else if($insertTimelineArray['rejected_cnt'] > 0) // back to re compliance
            {
                $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 6 ]['status_id'];
            }
            else // mark complete audit
            {
                $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 7 ]['status_id'];
            }
        }
    }

    if(!audit_assesment_timeline_insert($this, $insertTimelineArray))
    {
        Except::exc_404( Notifications::getNoti('errorSaving') );
        exit;
    }

    // change audit assesment status
    $assesmentUpdateArray = [];
    $noti = 'somethingWrong';
    $auditStatus = 1; // default pending

    if(in_array($type, ['audit', 'sendBackAudit']))
    {
        // for audit
        // CHANGE TO RE AUDIT IF REJECTED POINTS IN AUDIT 
        $auditStatus = ( $insertTimelineArray['rejected_cnt'] > 0 ) ? 3 : 4;

        // if send back entire audit
        if( $insertTimelineArray['status'] == 14 )
            $auditStatus = 1;

        // if no compliance observation // complete audit
        if( !in_array($insertTimelineArray['status'], [14]) && $emptyAuditAssesment )
            $auditStatus = 7;

        // default update
        $assesmentUpdateArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[ $auditStatus ]['status_id'];
        $assesmentUpdateArray['audit_review_emp_id'] = $this -> empId;
        $assesmentUpdateArray['audit_review_date'] = date($GLOBALS['dateSupportArray'][1]);
        
        if($auditStatus == 4)
        {
            // NO REJECTED POINTS SEND TO COMPLIANCE
            $assesmentUpdateArray['compliance_start_date'] = date( $GLOBALS['dateSupportArray'][1] );
            $assesmentUpdateArray['compliance_due_date'] = date( $GLOBALS['dateSupportArray'][1], strtotime("+" . (AUDIT_DUE_ARRAY[3] + 1) . " days", strtotime(date($GLOBALS['dateSupportArray'][1])) ));
        }

        // CHANGE NOTIFICATION
        $noti = in_array($auditStatus, [1,3]) ? 'assesmentBackAuditSuccess' : 'assesmentSendComplianceSuccess';
    }
    else
    {
        // for compliance
        // Check if audit_status_id is 15 (RO officer review)
        if($this -> assesmentData -> audit_status_id == 15)
        {
            // For RO officer review - check rejected count
            if($insertTimelineArray['rejected_cnt'] > 0) {
                // Has rejections - back to re compliance (status 6)
                $auditStatus = 6;
                $assesmentUpdateArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[ 6 ]['status_id'];
                $assesmentUpdateArray['ro_review_emp_id'] = $this -> empId;
                $assesmentUpdateArray['ro_review_date'] = date($GLOBALS['dateSupportArray'][1]);
                
                $noti = 'assesmentBackComplianceSuccess';
            } else {
                // No rejections - complete (status 5)
                $auditStatus = 5;
                $assesmentUpdateArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[ 5 ]['status_id'];
                $assesmentUpdateArray['ro_review_emp_id'] = $this -> empId;
                $assesmentUpdateArray['ro_review_date'] = date($GLOBALS['dateSupportArray'][1]);
                
                $noti = 'assesmentROReviewCompleted';
            }
        }
        else
        {
            // CHANGE TO RE COMPLIANCE IF REJECTED POINTS IN COMPLIANCE
            $auditStatus = ( $insertTimelineArray['rejected_cnt'] > 0 ) ? 6 : 7;
            
            // default update
            $assesmentUpdateArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[ $auditStatus ]['status_id'];
            $assesmentUpdateArray['compliance_review_emp_id'] = $this -> empId;
            $assesmentUpdateArray['compliance_review_date'] = date($GLOBALS['dateSupportArray'][1]);

            if($auditStatus == 7 && isset($this -> data['observation']['observation'])) // assesment completed
            {
                if( $this -> data['observation']['observation']['onhold'] > 0 )
                    $assesmentUpdateArray['compliance_onhold_count'] = $this -> data['observation']['observation']['onhold'];

                if( $this -> data['observation']['observation']['cf_point'] > 0 )
                    $assesmentUpdateArray['compliance_carry_forward_count'] = $this -> data['observation']['observation']['cf_point'];
            }

            // CHANGE NOTIFICATION
            $noti = 'assesmentBackComplianceSuccess';
        }
    }

    // CHANGE BATCH KEY IF NEEDED
    if(isset($assesmentUpdateArray['audit_status_id']) && in_array($assesmentUpdateArray['audit_status_id'], [ 
        ASSESMENT_TIMELINE_ARRAY[ 3 ]['status_id'], 
        ASSESMENT_TIMELINE_ARRAY[ 6 ]['status_id']
    ])) 
        $assesmentUpdateArray['batch_key'] = generate_batch_key( ($type != 'audit') ? 'C' : 'A' );
    
    // AUDIT & COMPLIANCE COMPLETE
    if($auditStatus == 7)
        $noti = "assesmentCompletedAuditSuccess";

    $result = $this -> auditAssesmentModel::update(
        $this -> auditAssesmentModel -> getTableName(), 
        $assesmentUpdateArray,
        [
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> assesmentData -> id ]
        ]
    );

    if(!$result)
    {
        Except::exc_404( Notifications::getNoti('errorSaving') );
        exit;
    }

    // redirect
    Validation::flashErrorMsg($noti, 'success');
    Redirect::to( SiteUrls::getUrl('dashboard') . '?auditUnit=' . encrypt_ex_data($this -> assesmentData -> audit_unit_id) );
}

    // for review audit
    public function reviewAudit()
    {
        // method call
        $this -> findData('ACP');

        // check for audit
        if( !$this -> checkAudit() )
        {
            Except::exc_access_restrict();
            exit; 
        }

        // print_r($this -> data);
        // exit;

        // default accept all audit points 25.08.2024
        $this -> defaultAcceptAll();

        $this -> me -> pageHeading = 'Audit Review';
        $this -> me -> menuKey = 'auditReview';

        $this -> data['review_timeline_status'] = AUDIT_STATUS_ARRAY['review_timeline_status'];
        $this -> data['filter_type'] = 'RVAU';

        if( check_evidence_upload_strict() )
        {
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'reviewer-evidence-auditpro.min.js';
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidencet-compulsary-checkbox.js';
        }

        // print_r($this -> data);
        // exit;

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        $this -> request::method("POST", function() {

            // method call
            $this -> observationActionSubmit();

        });

        return return2View($this, $this -> me -> viewDir . 'audit-review', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);
    }

    // submit audit review 25.08.2024
    public function submitAuditReview() {

        // method call
        $this -> findDataCount();

        $this -> data['assesmentData'] = $this -> assesmentData;

        $this -> me -> pageHeading = 'Submit Audit Review';
        $this -> me -> menuKey = 'submitAuditReview';

        // check for send back entire audit
        $checkAssesmentTimeline = get_all_data_query_builder(1, 
            $this -> auditAssesmentTimelineModel, 
            $this -> auditAssesmentTimelineModel -> getTableName(), [
                'where' => 'assesment_id = :assesment_id',  // AND status_id = 1
                'params' => [ 'assesment_id' => $this -> assesmentData -> id ] ], 'sql', 
            "SELECT COUNT(*) total_tl_count FROM " . $this -> auditAssesmentTimelineModel -> getTableName() 
        );

        if( $checkAssesmentTimeline -> total_tl_count <= 2 )
            $this -> data['sendBack'] = true;

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        // top data container
        $this -> data['data_container'] = true;

        $this -> request::method("POST", function() {

            // check for send back entire audit
            if($this -> request -> has('send_back_audit') && isset($this -> data['sendBack']))
            {
                // direct send back to audit but check timeline count
                $this -> assesmentTimelineAction([
                    'timeline_status' => 14,
                ], 'sendBackAudit');
            }
            else if($this -> request -> has('submit_review'))
            {
                // check count and redirect to re audit or compliance
                $this -> assesmentTimelineAction([
                    'timeline_status' => 4, // default send 
                ], 'audit');
            }
            else
            {
                // error submit
                Except::exc_404( Notifications::getNoti('somethingWrong') );
                exit;
            }

        });

        return return2View($this, $this -> me -> viewDir . 'submit-review', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);
    } 

// submit compliance review 02.09.2024
public function submitComplianceReview() {
    
    // Get emp_type from Session
    $empType = Session::get('emp_type');
    
    // method call for regular counts
    $this -> findDataCount( 'compliance' );
    
    // DO NOT call findRODataCount() - remove this line
    $this -> findRODataCount();

    $this -> data['assesmentData'] = $this -> assesmentData;

    $this -> me -> pageHeading = 'Submit Compliance Review';
    $this -> me -> menuKey = 'submitComplianceReview';

    // function call
    $remarkArray = unset_remark_options($this -> assesmentData);

    // top data container
    $this -> data['data_container'] = true;

    $this -> request::method("POST", function() use ($empType) {

        // check for submit review
        if($this -> request -> has('submit_review'))
        {
            // Determine timeline status based on emp_type
            $timelineStatus = 7; // default value
            
            // Check if emp_type is 16
            if($empType == 16) {
                $timelineStatus = 5;
            }
            
            // check count and redirect to re compliance or complete
            $this -> assesmentTimelineAction([
                'timeline_status' => $timelineStatus,
            ], 'compliance');
        }
        else
        {
            // error submit
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

    });

    return return2View($this, $this -> me -> viewDir . 'submit-review', [ 
        'request' => $this -> request,
        'data' => $this -> data,
        'db_assesment' => $this -> assesmentData,
        'remarkTypeArray' => $remarkArray,
    ]);
}

    // for review compliance 02.09.2024
    public function reviewCompliance() {

        // method call
        $this->findData('ACP');

        //check for compliance
        if ($this->assesmentData->audit_status_id != ASSESMENT_TIMELINE_ARRAY[5]['status_id'] && $this->assesmentData->audit_status_id != ASSESMENT_TIMELINE_ARRAY[15]['status_id']) {
            Except::exc_access_restrict();
            exit;
        }

        // default accept all compliance points
        $this->defaultAcceptAll('compliance');

        $this->me->pageHeading = 'Review Compliance';
        $this->me->menuKey = 'auditCompliance';

        $this->data['review_timeline_status'] = AUDIT_STATUS_ARRAY['review_timeline_status'];
        $this->data['filter_type'] = 'RVCOM';

        // pass audit_status_id to JS safely
        $this->data['audit_status_id'] = $this->assesmentData->audit_status_id;

        if (check_evidence_upload_strict()) {
            $this->data['js'][] = EVIDENCE_UPLOAD['assets'] . 'reviewer-evidence-auditpro.min.js';
            $this->data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidencet-compulsary-checkbox.js';
        }

        // Include compliance filter JS
        $this->data['js'][] = EVIDENCE_UPLOAD['assets'] . 'review-compliance-filter.js';

        $remarkArray = unset_remark_options($this->assesmentData);

        $this->request::method("POST", function () {
            $this->observationActionSubmit('compliance');
        });

        return return2View($this, $this->me->viewDir . 'audit-review', [
            'request' => $this->request,
            'data' => $this->data,
            'db_assesment' => $this->assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);
    }
private function findRODataCount() 
{
    $this->getAssesmentData();

    if(!$this->checkAudit())
    {
        Except::exc_404( Notifications::getNoti('errorFinding') );
        exit;
    }

    $this->data['ro_observation'] = [
        'observation' => [ 'accepted' => 0, 'rejected' => 0 ],
    ];

    // Use direct value concatenation instead of named parameters
    $assesmentId = $this->assesmentData->id;
    
    // Count RO Audit Status
    $sql = "SELECT ro_audit_status_id, COUNT(*) as count 
            FROM " . $this->answerDataModel->getTableName() . "
            WHERE assesment_id = {$assesmentId}
              AND is_compliance = 1 
              AND ro_audit_status_id IN (2,3)
              AND deleted_at IS NULL 
            GROUP BY ro_audit_status_id";
    
    $result = get_all_data_query_builder(2, $this->answerDataModel, $this->answerDataModel->getTableName(), [
        'params' => []
    ], 'sql', $sql);
    
    if(is_array($result) && sizeof($result) > 0)
    {
        foreach($result as $row)
        {
            if($row->ro_audit_status_id == 2)
                $this->data['ro_observation']['observation']['accepted'] += $row->count;
            else if($row->ro_audit_status_id == 3)
                $this->data['ro_observation']['observation']['rejected'] += $row->count;
        }
    }

    // Count RO Compliance Status
    $sql = "SELECT ro_compliance_status_id, COUNT(*) as count 
            FROM " . $this->answerDataModel->getTableName() . "
            WHERE assesment_id = {$assesmentId}
              AND is_compliance = 1 
              AND ro_compliance_status_id IN (2,3)
              AND deleted_at IS NULL 
            GROUP BY ro_compliance_status_id";
    
    $result = get_all_data_query_builder(2, $this->answerDataModel, $this->answerDataModel->getTableName(), [
        'params' => []
    ], 'sql', $sql);
    
    if(is_array($result) && sizeof($result) > 0)
    {
        foreach($result as $row)
        {
            if($row->ro_compliance_status_id == 2)
                $this->data['ro_observation']['observation']['accepted'] += $row->count;
            else if($row->ro_compliance_status_id == 3)
                $this->data['ro_observation']['observation']['rejected'] += $row->count;
        }
    }

    $this->data['ro_observation']['observation']['title'] = 'Total Compliance Observations (RO Review)';
}
private function findROCountAssign($statusCol, $model, $type = 'answer_data') 
{
    $res = [ 'accepted' => 0, 'rejected' => 0 ];
    
    // Only proceed for answer_data table
    if($type != 'answer_data') {
        return $res;
    }
    
    $where = "assesment_id = '" . $this->assesmentData->id . "' 
              AND is_compliance = 1 
              AND " . $statusCol . " IN (2,3) 
              AND deleted_at IS NULL 
              GROUP BY " . $statusCol;
    
    $results = $model->getAllAnswers([
        'where' => $where,
        'params' => []
    ], 'sql', "SELECT " . $statusCol . ", COUNT(*) as count");
    
    if(is_array($results) && sizeof($results) > 0)
    {
        foreach($results as $row)
        {
            if($row->{$statusCol} == 2) {
                $res['accepted'] += $row->count;
            } else {
                $res['rejected'] += $row->count;
            }
        }
    }
    
    return $res;
}
}

?>