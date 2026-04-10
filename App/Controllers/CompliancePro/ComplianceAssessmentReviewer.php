<?php

namespace Controllers\CompliancePro;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

// extra common functions 25.09.2024
require_once APP_CORE . DS . 'HelperFunctionsComplianceProReport.php';

class ComplianceAssessmentReviewer extends Controller  {

    public $me = null, $request, $data, $comId, $comAssesData, $empId;
    public $comAssesModel, $answerDataModel;
    // public $comAssesModel, $auditAssesmentTimelineModel, $answerDataModel, $answerDataAnnexureModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> comAssesModel = $this -> model('ComplianceCircularAssesMasterModel');
        // $this -> auditAssesmentTimelineModel = $this -> model('AuditAssesmentTimelineModel');
        $this -> answerDataModel = $this -> model('ComplianceCircularAnswerDataModel');
        // $this -> answerDataAnnexureModel = $this -> model('AnswerDataAnnexureModel');
        $this -> empId = Session::get('emp_id');

        $this -> data['rv_compliance_status'] = COMPLIANCE_PRO_ARRAY['review_compliance_status'];
        unset($this -> data['rv_compliance_status'][4]);
    }

    public function index()
    {
        Except::exc_access_restrict( );
        exit;        
    }

    private function checkAudit()
    {
        $status = false;
        $empType = Session::get('emp_type');

        if($empType == 6 && 
            $this -> comAssesData -> com_status_id == COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][2]['status_id'] ) // review compliance
            $status = true;

        return $status;
    }

    private function getComplianceAssesData($withoutExcept = true)
    {
        // get data from session
        $this -> comId = decrypt_ex_data(Session::get('compliance_id'));

        // helper function call
        $this -> comAssesData = get_compliance_assesment_details($this, Session::get('emp_id'), $this -> comId);

        if( $withoutExcept && !is_object($this -> comAssesData) )
        {
            Except::exc_404( Notifications::getNoti($this -> comAssesData) );
            exit;
        }
    }

    private function findData($filterType)
    {
        $this -> getComplianceAssesData(); //method call

        if(!$this -> checkAudit())
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // check for compliance
        if( !in_array($this -> comAssesData -> com_status_id, [2]) )
        {
            
            Except::exc_access_restrict();
            exit; 
        }

        // helper function call
        $dataArray = find_complinace_pro_tasks_observations($this, $this -> comAssesData, $filterType);

        $this -> data['data_array'] = $dataArray;
        $this -> data['comAssesData'] = $this -> comAssesData;

        // unset vars
        unset($dataArray);

        //need audit assesment js
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance_pro_report_reviewer_action_script.js';
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance_pro_comment_save_script.js';

        // top data container
        $this -> data['data_container'] = true;
    }

    // for review compliance 02.09.2024
    public function reviewCompliance()
    {
        // method call
        $this -> findData('ACP');

        // default accept all compliance points 02.09.2024
        $this -> defaultAcceptAll( 'compliance' );

        $this -> me -> pageHeading = 'Review Compliance';
        $this -> me -> menuKey = 'complianceCircularAssign';

        $this -> data['review_timeline_status'] = AUDIT_STATUS_ARRAY['review_timeline_status'];
        $this -> data['filter_type'] = 'RVCOM';

        // if( check_evidence_upload_strict() )
        // {
        //     $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'reviewer-evidence-auditpro.min.js';
        //     $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidencet-compulsary-checkbox.js';
        // }        

        // function call
        // $remarkArray = unset_remark_options($this -> comAssesData);

        $this -> request::method("POST", function() {
            
            // method call
            // $this -> observationActionSubmit( 'compliance' );
            exit;

        });

        return return2View($this, $this -> me -> viewDir . 'audit-review', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> comAssesData,
            // 'remarkTypeArray' => $remarkArray,
        ]);
    }

    private function defaultAcceptAll($type = 'audit', $extra = [])
    {
        $status = isset($extra['status']) ? $extra['status'] : 2; // accepted
        $updateArray = [ 'audit_status_id' => $status ];
        $res = false;

        $whereArray = [
            'where' => 'com_master_id = :com_master_id AND deleted_at IS NULL',
            'params' => [ 'com_master_id' => $this -> comAssesData -> id ]
        ];

        if( $type != 'audit' )
        {   
            // for compliance
            $updateArray = [ 'compliance_status_id' => $status ];

            if( !isset($extra['forceAll']) )
                $whereArray['where'] .= ' AND compliance_status_id = "0"';
        }

        // for answer data // here we accept all answer like no compliance answers also
        $result = $this -> answerDataModel::update(
            $this -> answerDataModel -> getTableName(), 
            $updateArray, $whereArray
        );

        // for answer data annexure 
        // $result2 = $this -> answerDataAnnexureModel::update(
        //     $this -> answerDataAnnexureModel -> getTableName(), 
        //     $updateArray, $whereArray
        // );

        if( $result /*&& $result2*/ )
            $res = true;

        return $res;
    }

    public function saveStatus()
    {
        $this -> getComplianceAssesData(0); //method call

        $res_array = [ 'msg' => Notifications::getNoti('somethingWrong'), 'res' => 'err' ];
        
        $requestData = isset($_POST['data']) ? $_POST['data'] : null;

        if( !is_object($this -> comAssesData) || empty($requestData) )
        {
            echo json_encode($res_array);
            exit;
        }

        $requestData = json_decode($requestData);

        $checkActionVal = false;

        // compliance action
        if( isset($requestData -> slctact) && $requestData -> slctact == 'com' && 
            array_key_exists($requestData -> action, $this -> data['rv_compliance_status']) )
            $checkActionVal = true;

        if( $checkActionVal && (isset($requestData -> ans_id) && !empty($requestData -> ans_id)) &&
            (isset($requestData -> ans_type) && !empty($requestData -> ans_type)) &&
            in_array( $requestData -> ans_type, ['gen', 'annex'] ) 
        )
        {          
            $columnTab = 'compliance_status_id';
            $updateEmp = 'compliance_reviewer_emp_id';
            $findAnnexDetails = null;
            $annexUpdate = true;
            
            // if($requestData -> ans_type == 'gen')
            $model = $this -> model('ComplianceCircularAnswerDataModel');
            // $annexModel = $this -> model('AnswerDataAnnexureModel');
            $requestData -> ans_id = decrypt_ex_data($requestData -> ans_id);

            /*if($requestData -> ans_type == 'annex')
            {
                // for annexure // get annex details
                $findAnnexDetails = $annexModel -> getSingleAnswerAnnexure([
                    'where' => 'id = :id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => [ 
                        'assesment_id' => $this -> comAssesData -> id,
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
                            'assesment_id' => $this -> comAssesData -> id,
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
                            'assesment_id' => $this -> comAssesData -> id,
                            'id' =>  $requestData -> ans_id 
                        ]
                    ];

                    $updateDBArray = [
                        $columnTab => $requestData -> action,
                        $updateEmp => $this -> empId
                    ];

                    // only for review compliance
                    if( $this -> comAssesData -> audit_status_id == COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 2 ]['status_id'] )
                        $updateDBArray['batch_key'] = $this -> comAssesData -> batch_key;

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
            }*/

            $result = false;

            if($annexUpdate)
            {
                $whereData = [
                    'where' => 'id = :id AND com_master_id = :com_master_id',
                    'params' => [ 
                        'com_master_id' => $this -> comAssesData -> id,
                        'id' =>  $requestData -> ans_id 
                    ]
                ];

                $updateDBArray = [
                    $columnTab => $requestData -> action,
                    $updateEmp => $this -> empId
                ];

                // only for review compliance
                if( $this -> comAssesData -> com_status_id == COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 2 ]['status_id'] )
                    $updateDBArray['batch_key'] = $this -> comAssesData -> batch_key;

                // check for on hold and carry forward // it will be accepted
                // if( is_object($findAnnexDetails) && 
                //     in_array($updateDBArray[ $columnTab ], [4,5]) )
                //     $updateDBArray[ $columnTab ] = 2;
                
                $result = $model::update(
                    $model -> getTableName(), $updateDBArray, $whereData
                );

                /*if($result && $requestData -> ans_type == 'gen')
                {
                    // check for annex
                    $findAllAnnexDetails = $annexModel -> getAllAnswerAnnexures([
                        'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                        'params' => [ 
                            'assesment_id' => $this -> comAssesData -> id,
                            'answer_id' => $requestData -> ans_id,
                        ]
                    ]);

                    if(is_array($findAllAnnexDetails) && sizeof($findAllAnnexDetails) > 0)
                    {
                        // update all annex data
                        $whereData = [
                            'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                            'params' => [ 
                                'assesment_id' => $this -> comAssesData -> id,
                                'answer_id' =>  $requestData -> ans_id 
                            ]
                        ];

                        $updateDBArray = [
                            $columnTab => $requestData -> action,
                            $updateEmp => $this -> empId
                        ];
        
                        // only for review compliance
                        if( $this -> comAssesData -> audit_status_id == COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 2 ]['status_id'] )
                            $updateDBArray['batch_key'] = $this -> comAssesData -> batch_key;
    
                        // update all annex        
                        $result = $annexModel::update(
                            $annexModel -> getTableName(), $updateDBArray, $whereData
                        );
                    }
                }*/
            }

            if($result)
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
        $this -> getComplianceAssesData(0); //method call

        $res_array = [ 'msg' => Notifications::getNoti('somethingWrong'), 'res' => 'err' ];
        
        $requestData = isset($_POST['data']) ? $_POST['data'] : null;

        if( !is_object($this -> comAssesData) || empty($requestData) )
        {
            echo json_encode($res_array);
            exit;
        }

        $type = 'com_rew';

        // for compliance
        $res_array = compliance_pro_save_assesment_message($this, $this -> empId, $requestData, $type);

        // change notification
        $res_array['msg'] = Notifications::getNoti($res_array['msg']);

        echo json_encode($res_array);
        exit;
    }

    private function assesmentTimelineAction($extra, $type = 'compliance')
    {
        // type = 2 = compliance

        /*$insertTimelineArray = array(
            'id' => $this -> comAssesData -> id,
            'type' => 1, // default 1 for audit
            'status' => ASSESMENT_TIMELINE_ARRAY[ $extra['timeline_status'] ]['status_id'],
            'rejected_cnt' => 0,
            'emp_id' => $this -> empId,
            'batch_key' => $this -> comAssesData -> batch_key,
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

                if($insertTimelineArray['rejected_cnt'] > 0) // back to re compliance
                    $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 6 ]['status_id'];
                else // mark complete audit
                    $insertTimelineArray['status'] = ASSESMENT_TIMELINE_ARRAY[ 7 ]['status_id'];
            }
        }

        if(!audit_assesment_timeline_insert($this, $insertTimelineArray))
        {
            Except::exc_404( Notifications::getNoti('errorSaving') );
            exit;
        }*/

        // change compliance assesment status
        $comAssesUpdateArray = [];
        $noti = 'somethingWrong';
        $auditStatus = 1; // default pending
        $rejected = $this -> data['observation']['observation']['failed'] + $this -> data['observation']['observation']['partially_passed'];

        // for compliance
        // CHANGE TO RE COMPLIANCE IF REJECTED POINTS IN COMPLIANCE
        $auditStatus = ( $rejected > 0 ) ? 3 : 4;
        
        // default update
        $assesmentUpdateArray['com_status_id'] = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ $auditStatus ]['status_id'];
        $assesmentUpdateArray['compliance_review_emp_id'] = $this -> empId;
        $assesmentUpdateArray['compliance_review_date'] = date($GLOBALS['dateSupportArray'][1]);

        // CHANGE NOTIFICATION
        $noti = 'circularBackComplianceSuccess';

        // CHANGE BATCH KEY IF NEEDED
        if(in_array($assesmentUpdateArray['com_status_id'], [ 
            COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 3 ]['status_id']
        ])) 
            $assesmentUpdateArray['batch_key'] = generate_batch_key( 'C' );
        
        // AUDIT & COMPLIANCE COMPLETE
        if($auditStatus == 4)
            $noti = "circularCompletedComplianceSuccess";

        // echo $noti;
        // echo '<hr />';
        // print_r($insertTimelineArray);
        // echo '<hr />';
        // print_r($assesmentUpdateArray);
        // exit;

        $result = $this -> comAssesModel::update(
            $this -> comAssesModel -> getTableName(), 
            $assesmentUpdateArray,
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> comAssesData -> id ]
            ]
        );

        if(!$result)
        {
            Except::exc_404( Notifications::getNoti('errorSaving') );
            exit;
        }

        // redirect
        Validation::flashErrorMsg($noti, 'success');
        Redirect::to( SiteUrls::getUrl('complianceCircularAssesData') . '/view-circular/' . encrypt_ex_data($this -> comAssesData -> circular_id) . '?tsid='. $this -> comAssesData -> task_set_id .'' );
    }

    private function findDataCount($type = 'audit') 
    {
        $this -> getComplianceAssesData(); //method call

        if(!$this -> checkAudit())
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        $statusCol = 'compliance_status_id';

        $this -> data['observation'] = [
            'observation' => [ 'passed' => 0, 'failed' => 0, 'partially_passed' => 0 ],
        ];

        $menuIds = null;

        if(!empty($this -> comAssesData -> menu_ids))
            $menuIds = explode(',', $this -> comAssesData -> menu_ids);

        // for general answer
        $this -> data['observation']['observation'] = $this -> findCountAssign($statusCol, $this -> answerDataModel);

        // for annexure
        // $res = $this -> findCountAssign($statusCol, $this -> answerDataAnnexureModel, 'annex');
        // $this -> data['observation']['observation']['passed'] += $res['passed'];
        // $this -> data['observation']['observation']['failed'] += $res['failed'];
        // $this -> data['observation']['observation']['partially_passed'] += $res['partially_passed'];

        // $this -> data['observation']['observation']['title'] = 'Total ' . (($type == 'audit') ? 'Audit' : 'Compliance') . ' Observations';
    }

    private function findCountAssign($statusCol, $model, $type = 'answer_data') {

        $table = $model -> getTableName();
        $res = [ 'passed' => 0, 'failed' => 0, 'partially_passed' => 0 ];

        $where = 'com_master_id = :com_master_id';        
        $where .= ' AND '. $statusCol .' IN ('. implode(',', array_keys($this -> data['rv_compliance_status'])) .') AND deleted_at IS NULL GROUP BY ' . $statusCol;

        $result = get_all_data_query_builder(2, $model, $table, [
            'where' => $where, 
            'params' => [ 'com_master_id' => $this -> comAssesData -> id ]
        ], 'sql', "SELECT ". $statusCol .", COUNT(*) AS count FROM ". $table);

        if(is_array($result) && sizeof($result) > 0)
        {
            // has data
            foreach($result as $row)
            {
                if( $row -> { $statusCol } == 1 ) // passed
                    $res['passed'] += $row -> count;
                else if( $row -> { $statusCol } == 2 ) // failed
                    $res['failed'] += $row -> count;
                else // partially_passed
                    $res['partially_passed'] += $row -> count;
            }
        }

        return $res;
    }

    // submit compliance review 02.09.2024
    public function submitComplianceReview() {

        // method call
        $this -> findDataCount( 'compliance' );

        $this -> data['assesmentData'] = $this -> comAssesData;

        $this -> me -> pageHeading = 'Submit Compliance Review';
        // $this -> me -> menuKey = 'submitComplianceReview';

        // function call
        // $remarkArray = unset_remark_options($this -> comAssesData);

        // top data container
        $this -> data['data_container'] = true;

        $this -> request::method("POST", function() {

            // check for submit review
            if($this -> request -> has('submit_review'))
            {
                // check count and redirect to re compliance or complete
                $this -> assesmentTimelineAction([
                    'timeline_status' => 4, // default send 
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
            'db_assesment' => $this -> comAssesData,
            // 'remarkTypeArray' => $remarkArray,
        ]);
    }
}

?>