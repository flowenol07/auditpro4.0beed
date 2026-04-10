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

class ComplianceProController extends Controller  {

    public $me = null, $request, $data, $comId, $comAssesData;
    public $comAssesModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> comAssesModel = $this -> model('ComplianceCircularAssesMasterModel');

        $this -> data['cco_docs_true'] = false;
        $this -> data['set_cco_docs_true'] = false;   
    }

    public function getComplianceAssesData($withoutExcept = true) 
    {
        // get data from session
        $this -> comId = decrypt_ex_data(Session::get('compliance_id'));

        // helper function call
        $this -> comAssesData = get_compliance_assesment_details($this, Session::get('emp_id'), $this -> comId);

        if(is_object($this -> comAssesData) && !empty($this -> comAssesData -> circular_id))
        {
            $model = $this -> model('ComplianceCircularSetModel');
            $this -> comAssesData -> circular_data = $model -> getSingleCircularSet([
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $this -> comAssesData -> circular_id ]
            ]);
        }

        if( $withoutExcept && !is_object($this -> comAssesData) )
        {
            Except::exc_404( Notifications::getNoti($this -> comAssesData) );
            exit;
        }
    }

    private function checkAudit()
    {
        $status = false;
        $empType = Session::get('emp_type');

        if( $empType == 3 && 
            in_array($this -> comAssesData -> com_status_id, [ 
                COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][1]['status_id'],
                COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id'] ])
            ) //compliance
            $status = true;

        return $status;
    }

    private function completeCompliance($auditStatus, $timelineSatus = 4/*, $type = 1*/)
    {
        // type = 0

        // $insertArray = array(
        //     'id' => $this -> comAssesData -> id,
        //     'type' => 2,
        //     'status' => COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ $timelineSatus ]['status_id'],
        //     'rejected_cnt' => 0,
        //     'emp_id' => Session::get('emp_id'),
        //     'batch_key' => $this -> comAssesData -> batch_key,
        // );

        // // check for reviewer
        // if(!array_key_exists(2, $GLOBALS['userTypesArray']))
        //     $insertArray['audit_status_id'] = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][7]['status_id'];
            
        // if(!audit_assesment_timeline_insert($this, $insertArray))
        // {
        //     Except::exc_404( Notifications::getNoti('errorSaving') );
        //     exit;
        // }

        // change audit assesment status
        $assesmentUpdateArray = [];

        // key not exists mark as complete
        if(!array_key_exists(2, $GLOBALS['userTypesArray']))
            $assesmentUpdateArray['com_status_id'] = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 4 ]['status_id'];
        else
            $assesmentUpdateArray['com_status_id'] = COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][ 2 ]['status_id'];

        $assesmentUpdateArray['compliance_emp_id'] = Session::get('emp_id');
        $assesmentUpdateArray['compliance_end_date'] = date($GLOBALS['dateSupportArray'][1]);

        $auditAssesmentTimelineCount = 0;

        // check audit assesment timeline for reject limit
        if($assesmentUpdateArray['com_status_id'] > 1)
        {
            // $auditAssesmentTimelineCount = get_all_data_query_builder(1, $this -> comAssesModel, 'audit_assesment_timeline', [
            //     'where' => 'type_id = 2 AND status_id = "'. ASSESMENT_TIMELINE_ARRAY[6]['status_id'] .'" AND deleted_at IS NULL', 'params' => [ ]
            // ], 'sql', "SELECT COUNT(*) total_assesment_timeline_count FROM audit_assesment_timeline");

            // $auditAssesmentTimelineCount = $auditAssesmentTimelineCount -> total_assesment_timeline_count;

            // $auditAssesmentTimelineCount = ($auditAssesmentTimelineCount >= $this -> comAssesData -> compliance_review_reject_limit) ? 1 : 0;

            // // add key in update array
            // $assesmentUpdateArray['is_limit_blocked'] = $auditAssesmentTimelineCount;
        }

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

        $returnUrl = SiteUrls::getUrl('complianceProDashboard');

        if( isset($this -> comAssesData -> circular_data) && 
            is_object($this -> comAssesData -> circular_data) )
            $returnUrl .= '/com-authority/' . encrypt_ex_data($this -> comAssesData -> circular_data -> authority_id);

        // redirect
        Validation::flashErrorMsg( (($assesmentUpdateArray['com_status_id'] != 4) ? 'complianceSendCCOSuccess' : 'complianceCompletedAuditSuccess'), 'success');
        Redirect::to( $returnUrl );
    }

    public function index() {
        Except::exc_access_restrict( );
        exit;
    }

    public function compliance()
    {
        
        $this -> findData('ACP');
        $this -> me -> pageHeading = 'Compliance';
        $this -> me -> menuKey = 'complianceProDashboard';

        $this -> data['filter_type'] = 'COM';

        //need audit assesment js
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance_pro_comment_save_script.js';

        // need audit assesment js
        $this -> data['cco_docs_true'] = true;
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance-pro-docs-upload.min.js';
        
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
        // $remarkArray = unset_remark_options($this -> comAssesData);

        return return2View($this, $this -> me -> viewDir . 'compliance', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> comAssesData,
            // 'remarkTypeArray' => $remarkArray,
        ]);
    }

    public function reCompliance()
    {
        // $this -> getAssesmentData(); //method call

        // if(!$this -> checkAudit())
        // {
        //     Except::exc_404( Notifications::getNoti('errorFinding') );
        //     exit;
        // }

        // // check for compliance
        // if( $this -> comAssesData -> audit_status_id != ASSESMENT_TIMELINE_ARRAY[6]['status_id'] )
        // {
        //     Except::exc_access_restrict();
        //     exit; 
        // }

        $this -> findData('ACRP');
        $this -> me -> pageHeading = 'Re-Compliance';
        $this -> me -> menuKey = 'auditReCompliance';

        $this -> data['filter_type'] = 'RECOM';

        // get timeline answer data all
        $this -> data['ans_data_timeline'] = get_answer_data_timeline_data($this, $this -> comAssesData, 2);

        // print_r($this -> data['ans_data_timeline']);

        //need audit assesment js
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance_pro_comment_save_script.js';

        // need audit assesment js
        $this -> data['cco_docs_true'] = true;
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance-pro-docs-upload.min.js';
        
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
        // $remarkArray = unset_remark_options($this -> comAssesData);

        return return2View($this, $this -> me -> viewDir . 'compliance', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> comAssesData,
            // 'remarkTypeArray' => $remarkArray,
        ]);        
    }

    private function findData($filterType)
    {
        $this -> getComplianceAssesData(); //method call

        if(!$this -> checkAudit())
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        if(!empty($this -> comAssesData -> circular_id))
        {
            // find circular details
            $this -> data['circular_data'] = get_com_circular_details($this, [
                'where' => 'ccsm.id = :id AND ccsm.is_active = 1 AND ccsm.deleted_at IS NULL',
                'params' => [ 'id' => $this -> comAssesData -> circular_id ]
            ], ['needDocs' => 1]);

            if(is_object($this -> data['circular_data']))
                $this -> data['circular_data_show'] = 1;
        }


        // check for compliance
        if( !in_array($this -> comAssesData -> com_status_id, [1,3]) )
        {
            Except::exc_access_restrict();
            exit; 
        }

        // helper function call
        $dataArray = find_complinace_pro_tasks_observations($this, $this -> comAssesData, $filterType);

        /*if(!is_array($dataArray['ans_data']) || 
        ( is_array($dataArray['ans_data']) && !sizeof($dataArray['ans_data']) > 0 ) )
        {
            Except::exc_404( Notifications::getNoti('noPendingAuditObservations') );
            exit;
        }*/

        $this -> data['data_array'] = $dataArray;
        $this -> data['comAssesData'] = $this -> comAssesData;       

        // unset vars
        unset($dataArray);

        //need audit assesment js
        // $this -> data['js'][] = 'report_reviewer_action_script.js';

        //top data container
        $this -> data['data_container'] = true;
    }

    public function saveCompliance()
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
        $findCurrentAnsData = null;

        if( ( isset($requestData -> ans_id) && !empty($requestData -> ans_id) ) &&
            ( isset($requestData -> ans_type) && !empty($requestData -> ans_type) ) &&
              in_array( $requestData -> ans_type, ['gen', 'annex'] ) )
        {  
            //find ans
            $whereData = [
                'where' => 'id = :id AND com_master_id = :com_master_id',
                'params' => [ 
                    'id' =>  decrypt_ex_data($requestData -> ans_id),
                    'com_master_id' => $this -> comAssesData -> id
                ]
            ];
            
            if($requestData -> ans_type == 'gen')
		    {
                // general answer
                $model = $this -> model('ComplianceCircularAnswerDataModel');
                $findCurrentAnsData = $model -> getSingleCircularAnswerData($whereData);                
            }
            // else
            // {
            //     // annex answer
            //     $model = $this -> model('AnswerDataAnnexureModel');

            //     $findCurrentAnsData = $model -> getSingleAnswerAnnexure($whereData);
            // }

            if(is_object($findCurrentAnsData))
                $checkActionVal = true;
        }

        if($checkActionVal && is_object($findCurrentAnsData))
        {
            // check for batch key
            if( in_array($findCurrentAnsData -> compliance_status_id, [2,3]) &&
                $findCurrentAnsData -> batch_key != $this -> comAssesData -> batch_key )
            {
                // if batch key changed shift answer to the timeline
                $ansTimelineModel = $this -> model('ComplianceCircularAnswerDataTimelineModel');

                $insertArray = array(
                    "answer_id" => isset( $findCurrentAnsData -> answer_id ) ? $findCurrentAnsData -> answer_id : $findCurrentAnsData -> id,
                    "annex_id" => isset( $findCurrentAnsData -> answer_id ) ? $findCurrentAnsData -> id : 0,
                    "com_master_id" => $findCurrentAnsData -> com_master_id,
                    "last_updated_at" => $findCurrentAnsData -> updated_at,
                    "answer_type" => 2, // for compliance
                    "answer_given" => $findCurrentAnsData -> answer_given,
                    "cco_comment" => $findCurrentAnsData -> cco_comment,
                    "cco_emp_id" => $findCurrentAnsData -> cco_emp_id,
                    "compliance" => $findCurrentAnsData -> compliance,
                    "compliance_emp_id" => $findCurrentAnsData -> compliance_emp_id,
                    "compliance_status_id" => $findCurrentAnsData -> compliance_status_id,
                    "compliance_reviewer_emp_id" => $findCurrentAnsData -> compliance_reviewer_emp_id,
                    "compliance_reviewer_comment" => $findCurrentAnsData -> compliance_reviewer_comment,
                    "risk_category_id" => $findCurrentAnsData -> risk_category_id,
                    "business_risk" => $findCurrentAnsData -> business_risk,
                    "control_risk" => $findCurrentAnsData -> control_risk,
                    "batch_key" => $findCurrentAnsData -> batch_key
                );

                // print_r($insertArray);

                // insert in database
                $result = $ansTimelineModel::insert(
                    $ansTimelineModel -> getTableName(), $insertArray
                );

                // if(!$result)
                //     $checkActionVal = false;
            }

            // for compliance
            $res_array = compliance_pro_save_assesment_message($this, Session::get('emp_id'), json_encode($requestData), 'com', 1);

            // change notification
            $res_array['msg'] = Notifications::getNoti($res_array['msg']);
        }

        echo json_encode($res_array);
        exit;
    }

    private function checkAllComplianceRequired($batchKeyCheck = false)
    {
        $complianceNeeded = 0;

        if( array_key_exists('data_array', $this -> data) && 
            is_array($this -> data['data_array']['ans_data']) && 
            sizeof($this -> data['data_array']['ans_data']) > 0 ):

            foreach($this -> data['data_array']['ans_data'] as $cHeaderId => $cHeaderDetails)
            {
                if( isset($cHeaderDetails['tasks']) && 
                    is_array($cHeaderDetails['tasks']) && 
                    sizeof($cHeaderDetails['tasks']) > 0)
                {
                    // method call
                    $resData = $this -> multiQuestionAnsCheck(
                        $cHeaderDetails['tasks'], $complianceNeeded, $batchKeyCheck);

                    $complianceNeeded = $resData['compliance'];
                }

                // if( isset($cCatDetails -> dump) && 
                //     is_array($cCatDetails -> dump) && 
                //     sizeof($cCatDetails -> dump) > 0)
                // {
                //     foreach($cCatDetails -> dump as $cDumpId => $cDumpDetails)
                //     {
                //         $resData = $this -> multiQuestionAnsCheck(
                //             $cDumpDetails, $complianceNeeded, $batchKeyCheck);

                //         $complianceNeeded = $resData['compliance'];                                                       
                //     }
                // }
            }

        endif;

        return [ 'compliance' => $complianceNeeded ];
    }

    private function multiQuestionAnsCheck($questionAnsData, $complianceNeeded, $batchKeyCheck = false)
    {
        foreach($questionAnsData as $cQuesId => $cQuesDetails)
        {
            if( empty(trim_str($cQuesDetails -> compliance)) ||
                ( $batchKeyCheck && $cQuesDetails -> batch_key != $this -> comAssesData -> batch_key ) )
            {
                // print_R($cQuesDetails);
                $complianceNeeded++;
            }

            // check for evidence 07-06-2024
            // if( $cQuesDetails -> is_compliance == 1 && 
            //     check_evidence_upload_strict() &&
            //     in_array($cQuesDetails -> compliance_compulsary_ev_upload, [1,2]))
            // {
            //     // function call
            //     $complianceNeeded = check_evidence_upload_compulsary_count($complianceNeeded, $cQuesDetails, 'compliance');
            // }

            // check for annexure
            // if( isset($cQuesDetails -> annex) && 
            //     is_array($cQuesDetails -> annex) && 
            //     sizeof($cQuesDetails -> annex) > 0)
            // {
            //     foreach($cQuesDetails -> annex as $cAnnexId => $cAnnexDetails)
            //     {
            //         if( /*$cAnnexDetails -> is_compliance &&*/ ( 
            //             // $cAnnexDetails -> compliance_status_id != 2 || 
            //             empty(trim_str($cAnnexDetails -> compliance)) ||
            //             ( $batchKeyCheck && $cAnnexDetails -> batch_key != $this -> comAssesData -> batch_key ) ))
            //             $complianceNeeded++;

            //         // check for evidence 07-06-2024
            //         if( check_evidence_upload_strict() &&
            //             in_array($cAnnexDetails -> compliance_compulsary_ev_upload, [1,2]))
            //         {
            //             // function call
            //             $complianceNeeded = check_evidence_upload_compulsary_count($complianceNeeded, $cAnnexDetails, 'compliance');
            //         }
            //     }
            // }                                    
        }

        return [ 'compliance' => $complianceNeeded ];
    }
}

?>