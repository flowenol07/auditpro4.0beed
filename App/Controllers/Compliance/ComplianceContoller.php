<?php

namespace Controllers\Compliance;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

// extra common functions 31.08.2024
require_once APP_CORE . DS . 'HelperFunctionsAuditReport.php';

class ComplianceContoller extends Controller  {

    public $me = null, $request, $data, $assesId, $auditAssesmentModel, $assesmentData;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> auditAssesmentModel = $this -> model('AuditAssesmentModel');
    }

    public function getAssesmentData($withoutExcept = true)
    {
        //get data from session
        $this -> assesId = decrypt_ex_data(Session::get('audit_id'));

        // helper function call
        $this -> assesmentData = get_assesment_details($this, Session::get('emp_id'), $this -> assesId);

        if( $withoutExcept && !is_object($this -> assesmentData) )
        {
            Except::exc_404( Notifications::getNoti($this -> assesmentData) );
            exit;
        }
    }

    private function checkAudit()
    {
        $status = false;
        $empType = Session::get('emp_type');

        if( $empType == 3 && 
            in_array($this -> assesmentData -> audit_status_id, [ 
                ASSESMENT_TIMELINE_ARRAY[4]['status_id'], ASSESMENT_TIMELINE_ARRAY[6]['status_id'] ])
            ) //compliance
            $status = true;

        return $status;
    }

    private function completeCompliance($auditStatus, $timelineSatus = 15/*, $type = 1*/)
    {
        // type = 0

        $insertArray = array(
            'id' => $this -> assesmentData -> id,
            'type' => 2,
            'status' => /*ASSESMENT_TIMELINE_ARRAY[ $timelineSatus ]['status_id']*/ 15,
            'rejected_cnt' => 0,
            'emp_id' => Session::get('emp_id'),
            'batch_key' => $this -> assesmentData -> batch_key,
        );

        // check for reviewer
        if(!array_key_exists(2, $GLOBALS['userTypesArray']))
            $insertArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[7]['status_id'];
            
        if(!audit_assesment_timeline_insert($this, $insertArray))
        {
            Except::exc_404( Notifications::getNoti('errorSaving') );
            exit;
        }

        // change audit assesment status
        $assesmentUpdateArray = [];

        // key not exists mark as complete
        if(!array_key_exists(2, $GLOBALS['userTypesArray']))
            $assesmentUpdateArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[ 7 ]['status_id'];
        else
            $assesmentUpdateArray['audit_status_id'] = ASSESMENT_TIMELINE_ARRAY[ 15 ]['status_id'];

        $assesmentUpdateArray['compliance_emp_id'] = Session::get('emp_id');
        $assesmentUpdateArray['compliance_end_date'] = date($GLOBALS['dateSupportArray'][1]);

        $auditAssesmentTimelineCount = 0;

        // check audit assesment timeline for reject limit
        if($assesmentUpdateArray['audit_status_id'] > 4)
        {
            $auditAssesmentTimelineCount = get_all_data_query_builder(1, $this -> auditAssesmentModel, 'audit_assesment_timeline', [
                'where' => 'type_id = 2 AND status_id = "'. ASSESMENT_TIMELINE_ARRAY[6]['status_id'] .'" AND deleted_at IS NULL', 'params' => [ ]
            ], 'sql', "SELECT COUNT(*) total_assesment_timeline_count FROM audit_assesment_timeline");

            $auditAssesmentTimelineCount = $auditAssesmentTimelineCount -> total_assesment_timeline_count;

            $auditAssesmentTimelineCount = ($auditAssesmentTimelineCount >= $this -> assesmentData -> compliance_review_reject_limit) ? 1 : 0;

            // add key in update array
            $assesmentUpdateArray['is_limit_blocked'] = $auditAssesmentTimelineCount;
        }

        // print_r($assesmentUpdateArray);

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
        Validation::flashErrorMsg( (($assesmentUpdateArray['audit_status_id'] != 7) ? 'assesmentSendReviewerSuccess' : 'assesmentCompletedAuditSuccess'), 'success');
        Redirect::to( SiteUrls::getUrl('dashboard') );
    }

    public function index()
    {
        $this -> getAssesmentData(); //method call

        if(!$this -> checkAudit())
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // check for compliance
        if( $this -> assesmentData -> audit_status_id != ASSESMENT_TIMELINE_ARRAY[4]['status_id'] )
        {
            Except::exc_access_restrict();
            exit; 
        }

        $this -> findData('ACP');
        $this -> me -> pageHeading = 'Audit Compliance';
        $this -> me -> menuKey = 'auditCompliance';

        $this -> data['filter_type'] = 'COM';

        //need audit assesment js
        $this -> data['js'][] = 'compliance_comment_save_script.js';

        if( check_evidence_upload_strict() )
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidence-auditpro.min.js';
        
        //post method after form submit
        $this -> request::method("POST", function() {
            
            $complianceNeeded = $this -> checkAllComplianceRequired()['compliance'];

            if($complianceNeeded > 0)
            {
                // flash error
                Validation::flashErrorMsg('allCompliaceWarning', 'danger');
            }
            else
            {
                // method call
                $this -> completeCompliance(2);
                exit;
            }
        });

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        return return2View($this, $this -> me -> viewDir . 'compliance', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);        
    }

    public function reCompliance()
    {
        $this -> getAssesmentData(); //method call

        if(!$this -> checkAudit())
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // check for compliance
        if( $this -> assesmentData -> audit_status_id != ASSESMENT_TIMELINE_ARRAY[6]['status_id'] )
        {
            Except::exc_access_restrict();
            exit; 
        }

        $this -> findData('ACRP');
        $this -> me -> pageHeading = 'Audit Re-Compliance';
        $this -> me -> menuKey = 'auditReCompliance';

        $this -> data['filter_type'] = 'RECOM';

        // get timeline answer data all
        $this -> data['ans_data_timeline'] = get_answer_data_timeline_data($this, $this -> assesmentData, 2);

        // print_r($this -> data['ans_data_timeline']);

        //need audit assesment js
        $this -> data['js'][] = 'compliance_comment_save_script.js';

        if( check_evidence_upload_strict() )
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidence-auditpro.min.js';
        
        //post method after form submit
        $this -> request::method("POST", function() {

            $complianceNeeded = $this -> checkAllComplianceRequired(1)['compliance'];

            if($complianceNeeded > 0)
            {
                // flash error
                Validation::flashErrorMsg('allCompliaceWarning', 'danger');
            }
            else
            {
                // method call
                $this -> completeCompliance(2);
                exit;
            }
        });

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        return return2View($this, $this -> me -> viewDir . 'compliance', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);        
    }

    public function saveCompliance()
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
        $findCurrentAnsData = null;

        if( ( isset($requestData -> ans_id) && !empty($requestData -> ans_id) ) &&
            ( isset($requestData -> ans_type) && !empty($requestData -> ans_type) ) &&
              in_array( $requestData -> ans_type, ['gen', 'annex'] ) )
        {  
            //find ans
            $whereData = [
                'where' => 'id = :id AND assesment_id = :assesment_id',
                'params' => [ 
                    'id' =>  decrypt_ex_data($requestData -> ans_id),
                    'assesment_id' => $this -> assesmentData -> id
                ]
            ];
            
            if($requestData -> ans_type == 'gen')
		    {
                // general answer
                $model = $this -> model('AnswerDataModel');

                $findCurrentAnsData = $model -> getSingleAnswer($whereData);
            }
            else
            {
                // annex answer
                $model = $this -> model('AnswerDataAnnexureModel');

                $findCurrentAnsData = $model -> getSingleAnswerAnnexure($whereData);
            }

            if(is_object($findCurrentAnsData))
                $checkActionVal = true;
        }

        if($checkActionVal && is_object($findCurrentAnsData))
        {
            // check for batch key
            if( $findCurrentAnsData -> compliance_status_id == 3 &&
                $findCurrentAnsData -> batch_key != $this -> assesmentData -> batch_key )
            {
                // if batch key changed shift answer to the timeline
                $ansTimelineModel = $this -> model('AnswerDataTimelineModel');

                $insertArray = array(
                    "answer_id" => isset( $findCurrentAnsData -> answer_id ) ? $findCurrentAnsData -> answer_id : $findCurrentAnsData -> id,
                    "annex_id" => isset( $findCurrentAnsData -> answer_id ) ? $findCurrentAnsData -> id : 0,
                    "assesment_id" => $findCurrentAnsData -> assesment_id,
                    "last_updated_at" => $findCurrentAnsData -> updated_at,
                    "answer_type" => 2, //for compliance
                    "answer_given" => $findCurrentAnsData -> answer_given,
                    "audit_comment" => $findCurrentAnsData -> audit_comment,
                    "audit_emp_id" => $findCurrentAnsData -> audit_emp_id,
                    "audit_status_id" => $findCurrentAnsData -> audit_status_id,
                    "audit_reviewer_emp_id" => $findCurrentAnsData -> audit_reviewer_emp_id,
                    "audit_reviewer_comment" => $findCurrentAnsData -> audit_reviewer_comment,
                    "audit_commpliance" => $findCurrentAnsData -> audit_commpliance,
                    "compliance_evidance_upload" => $findCurrentAnsData -> compliance_evidance_upload,
                    "compliance_emp_id" => $findCurrentAnsData -> compliance_emp_id,
                    "compliance_status_id" => $findCurrentAnsData -> compliance_status_id,
                    "compliance_reviewer_emp_id" => $findCurrentAnsData -> compliance_reviewer_emp_id,
                    "compliance_reviewer_comment" => $findCurrentAnsData -> compliance_reviewer_comment,
                    "business_risk" => $findCurrentAnsData -> business_risk,
                    "control_risk" => $findCurrentAnsData -> control_risk,
                    "risk_cat_id" => (isset($findCurrentAnsData -> risk_cat_id) ? $findCurrentAnsData -> risk_cat_id : 0),
                    "instances_count" => (isset($findCurrentAnsData -> instances_count) ? $findCurrentAnsData -> instances_count : 0),
                    "batch_key" => $findCurrentAnsData -> batch_key
                );

                // insert in database
                $result = $ansTimelineModel::insert(
                    $ansTimelineModel -> getTableName(), $insertArray
                );

                if(!$result)
                    $checkActionVal = false;
            }

            // for compliance
            $res_array = save_assesment_message($this, Session::get('emp_id'), json_encode($requestData), 'com', 1);

            // change notification
            $res_array['msg'] = Notifications::getNoti($res_array['msg']);
        }

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

        /*if(!is_array($dataArray['ans_data']) || 
        ( is_array($dataArray['ans_data']) && !sizeof($dataArray['ans_data']) > 0 ) )
        {
            Except::exc_404( Notifications::getNoti('noPendingAuditObservations') );
            exit;
        }*/

        $this -> data['data_array'] = $dataArray;
        $this -> data['assesmentData'] = $this -> assesmentData;       

        // unset vars
        unset($dataArray);

        //need audit assesment js
        // $this -> data['js'][] = 'report_reviewer_action_script.js';

        //top data container
        $this -> data['data_container'] = true;
    }

    private function checkAllComplianceRequired($batchKeyCheck = false)
    {
        $complianceNeeded = 0;

        if( array_key_exists('data_array', $this -> data) && 
            is_array($this -> data['data_array']['ans_data']) && 
            sizeof($this -> data['data_array']['ans_data']) > 0 ):

            foreach($this -> data['data_array']['ans_data'] as $cMenuId => $cMenuDetails)
            {
                if(array_key_exists('category', $cMenuDetails) && sizeof($cMenuDetails['category']) > 0)
                {
                    // has category data
                    foreach($cMenuDetails['category'] as $cCatId => $cCatDetails)
                    {
                        if( isset($cCatDetails -> questions) && 
                            is_array($cCatDetails -> questions) && 
                            sizeof($cCatDetails -> questions) > 0)
                        {
                            // method call
                            $resData = $this -> multiQuestionAnsCheck(
                                $cCatDetails -> questions, $complianceNeeded, $batchKeyCheck);

                            $complianceNeeded = $resData['compliance'];
                        }

                        if( isset($cCatDetails -> dump) && 
                            is_array($cCatDetails -> dump) && 
                            sizeof($cCatDetails -> dump) > 0)
                        {
                            foreach($cCatDetails -> dump as $cDumpId => $cDumpDetails)
                            {
                                $resData = $this -> multiQuestionAnsCheck(
                                    $cDumpDetails, $complianceNeeded, $batchKeyCheck);

                                $complianceNeeded = $resData['compliance'];                                                       
                            }
                        }
                    }
                }
            }

        endif;

        return [ 'compliance' => $complianceNeeded ];
    }

    private function multiQuestionAnsCheck($questionAnsData, $complianceNeeded, $batchKeyCheck = false)
    {
        // has headers
        foreach($questionAnsData as $cHeaderId => $cHeaderDetails)
        {
            if(array_key_exists('questions', $cHeaderDetails) && sizeof($cHeaderDetails['questions']) > 0)
            {
                // has question ans data

                foreach($cHeaderDetails['questions'] as $cQuesId => $cQuesDetails)
                {
                    if( $cQuesDetails -> is_compliance == 1 && ( 
                        // $cQuesDetails -> compliance_status_id != 2 || 
                        empty(trim_str($cQuesDetails -> audit_commpliance)) ||
                        ( $batchKeyCheck && $cQuesDetails -> batch_key != $this -> assesmentData -> batch_key ) ))
                    {
                        // print_R($cQuesDetails);
                        $complianceNeeded++;
                    }

                    // check for evidence 07-06-2024
                    if( $cQuesDetails -> is_compliance == 1 && 
                        check_evidence_upload_strict() &&
                        in_array($cQuesDetails -> compliance_compulsary_ev_upload, [1,2]))
                    {
                        // function call
                        $complianceNeeded = check_evidence_upload_compulsary_count($complianceNeeded, $cQuesDetails, 'compliance');
                    }

                    // check for annexure
                    if( isset($cQuesDetails -> annex) && 
                        is_array($cQuesDetails -> annex) && 
                        sizeof($cQuesDetails -> annex) > 0)
                    {
                        foreach($cQuesDetails -> annex as $cAnnexId => $cAnnexDetails)
                        {
                            if( /*$cAnnexDetails -> is_compliance &&*/ ( 
                                // $cAnnexDetails -> compliance_status_id != 2 || 
                                empty(trim_str($cAnnexDetails -> audit_commpliance)) ||
                                ( $batchKeyCheck && $cAnnexDetails -> batch_key != $this -> assesmentData -> batch_key ) ))
                                $complianceNeeded++;

                            // check for evidence 07-06-2024
                            if( check_evidence_upload_strict() &&
                                in_array($cAnnexDetails -> compliance_compulsary_ev_upload, [1,2]))
                            {
                                // function call
                                $complianceNeeded = check_evidence_upload_compulsary_count($complianceNeeded, $cAnnexDetails, 'compliance');
                            }
                        }
                    }                                    
                }
            }
        }

        return [ 'compliance' => $complianceNeeded ];
    }
    
}

?>