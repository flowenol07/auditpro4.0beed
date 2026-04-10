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

class ComplianceProDocsUpload extends Controller  {

    public $me = null, $data, $request, $res, $empId;
    public $model;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> res = [ 'err' => true, 'msg' => Notifications::getNoti('somethingWrong'), 'markup' => '' ];
        $this -> empId = Session::get('emp_id');

        $this -> model = $this -> model('ComplianceCircularDocModel');
    }

    public function index() {
        $this -> errorAndExit(); // method call
    }

    private function errorAndExit()
    {
        echo json_encode($this -> res);
        exit;
    }

    private function checkDocsUploadLimit($type, $filter = [])
    {
        $res = [ 'err' => true, 'msg' => Notifications::getNoti('comDocsLimitError') ];
        $limit = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['multi_limit'];
        
        if( $limit == 'infinite' )
        {
            $res['err'] = false;
            return $res;
        }
        else if( $limit != 'infinite' )
        {
            // check limit
            $table = $this -> model -> getTableName();

            $totalDocs = get_all_data_query_builder(1, $this -> model, $table, $filter, 'sql', 
            "SELECT COUNT(*) total_docs FROM " . $table) -> total_docs;

            if( $totalDocs < $limit )
                $res['err'] = false;
        }

        return $res;
    }

    private function check_cco_upload_file_move($FILES, $res, $extra = 'validate')
    {
        if(!isset($FILES['com_docs_file']))
        {
            $res['msg_err'] = $res['msg'];
            return $res;
        }

        $file = $FILES['com_docs_file'];
        $fileParts = pathinfo($file["name"]);

        // Validate file type and size
        if( in_array($file['type'], array_values(COMPLIANCE_PRO_ARRAY['compliance_docs_array']['file_types'])) && 
            $file['size'] <= (COMPLIANCE_PRO_ARRAY['compliance_docs_array']['size'] * 1024 * 1024) ) 
        {
            if($extra == 'move')
            {
                $uploadDir = generate_com_circular_dir($res['circular_id'], 1);
                $fileTypeId = array_search($file['type'], COMPLIANCE_PRO_ARRAY['compliance_docs_array']['file_types']);

                $circularDir = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['upload_dir'] . $uploadDir;
                
                // if folder not exists create folder with permission
                if (!is_dir($circularDir)) mkdir($circularDir, 0755, true);

                $comAssesDir = '';

                if($res['assesment_id'] != 0)
                {
                    $comAssesDir = generate_com_circular_dir($res['assesment_id'], 2);
                    if (!is_dir($circularDir . $comAssesDir)) mkdir($circularDir . $comAssesDir, 0755, true);
                }

                $uploadFile = sanitize_file_name($fileParts["filename"], $fileParts['extension']);
                $fullLocation = $circularDir . (!empty($comAssesDir) ? ($comAssesDir) : '') . $uploadFile;

                if ($fileTypeId !== false && 
                    move_uploaded_file($file['tmp_name'], $fullLocation)) {

                    // move success
                    $res['err'] = false;
                    $res['file'] = $uploadFile;
                    $res['file_type'] = $fileTypeId;
                    $res['dir'] = $uploadDir;
                    $res['full_location'] = $fullLocation;

                    unset($res['msg_err']);
                } 
                else
                {
                    // error file move
                    $res['msg_err'] = 'Error: Failed to move uploaded file.';
                }
            }
        } 
        else 
        {
            $res['msg_err'] = 'Error: Invalid file. ';

            if( !in_array($file['type'], COMPLIANCE_PRO_ARRAY['compliance_docs_array']['file_types']) )
                $res['msg_err'] .= 'Only JPG, JPEG, PNG, and PDF files are allowed. ';
            
            if( $file['size'] > (COMPLIANCE_PRO_ARRAY['compliance_docs_array']['size'] * 1024 * 1024) )
                $res['msg_err'] .= 'File size exceeds the maximum limit of '. COMPLIANCE_PRO_ARRAY['compliance_docs_array']['size'] .'MB.';
        }

        return $res;
    }

    public function uploadDocs()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this -> errorAndExit(); // method call

        $postData = $_POST;
        $fileData = $_FILES;
        $res = $this -> res;

        $docCatType = (isset($postData['doc_cat_type']) && !empty($postData['doc_cat_type'])) ? decrypt_ex_data($postData['doc_cat_type']) : 0;

        if( !in_array($docCatType, [1,2,3,4,5,6,7,8]) )
            $this -> errorAndExit(); // method call

        // validate file
        $res = $this -> check_cco_upload_file_move($fileData, $res);
        $circulr_id = (isset($postData['circulr_id']) && !empty($postData['circulr_id'])) ? decrypt_ex_data($postData['circulr_id']) : 0;
        $task_id = (isset($postData['task_id']) && !empty($postData['task_id'])) ? decrypt_ex_data($postData['task_id']) : 0;
        $ans_id = (isset($postData['ans_id']) && !empty($postData['ans_id'])) ? decrypt_ex_data($postData['ans_id']) : 0;
        $annex_id = (isset($postData['annex_id']) && !empty($postData['annex_id'])) ? decrypt_ex_data($postData['annex_id']) : 0;
        $com_asses_id = (isset($postData['com_asses_id']) && !empty($postData['com_asses_id'])) ? decrypt_ex_data($postData['com_asses_id']) : 0;
        $submit_auth_id = (isset($postData['submit_auth_id']) && !empty($postData['submit_auth_id'])) ? decrypt_ex_data($postData['submit_auth_id']) : 0;

        $insertData = [
            'circular_id' => 0,
            'task_id' => 0,
            'answer_id' => 0,
            'annex_id' => 0,
            'assesment_id' => 0,
            'submit_auth_id' => 0,
            'doc_type' => 0,
            'file_name' => 0,
            'file_type' => 0,
            'description' => 0,
            'emp_id' => $this -> empId,
            'status_id' => 0,
            'err' => 1,
            'msg' => $this -> res['msg']
        ];

        if(isset($res['msg_err']))
        {
            $res['msg'] = $res['msg_err'];
            unset($res['msg_err']);

            echo json_encode($res);
            exit;
        }

        switch($docCatType)
        {
            case '1': {

                $circularData = null;

                // ENTIRE CIRCULAR DOCS
                if(!empty($circulr_id))
                {
                    // find circular exists or not
                    $model = $this -> model('ComplianceCircularSetModel');

                    $circularData = $model -> getSingleCircularSet([
                        'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                        'params' => [ 'id' => $circulr_id ]
                    ]);

                    if(is_object($circularData))
                    {
                        // check multi upload limit // method call
                        $res2 = $this -> checkDocsUploadLimit($docCatType, [
                            'where' => 'circular_id = :circular_id AND doc_type = :doc_type AND deleted_at IS NULL',
                            'params' => [ 'circular_id' => $circularData -> id, 'doc_type' => $docCatType ]
                        ]);

                        if(!$res2['err'])
                        {
                            $insertData['circular_id'] = $circularData -> id;
                            $insertData['err'] = 0;
                        }
                        else { $insertData['err'] = 1; $insertData['msg'] = $res2['msg']; }
                    }                        
                }

                if(!is_object($circularData))
                    $insertData['msg'] = Notifications::getNoti('circularNotFound');

                break;
            }

            case '2': {
                
                // SINGLE TASKS DOCS
                if(!empty($circulr_id) && !empty($task_id))
                {
                    // find circular exists or not
                    $model = $this -> model('ComplianceCircularTaskModel');
                    $table = $model -> getTableName();

                    $taskData = get_all_data_query_builder(1, $model, $table, [
                        'where' => 'cctm.id = :id AND set_id = :set_id AND cctm.is_active = 1 AND cctm.deleted_at IS NULL AND ccsm.is_active = 1 AND ccsm.deleted_at IS NULL',
                        'params' => [ 'id' => $task_id, 'set_id' => $circulr_id ]
                    ], 'sql', "SELECT cctm.*, ccsm.id ccsm_id FROM ". $table ." cctm LEFT JOIN com_circular_set_master ccsm ON cctm.set_id = ccsm.id");

                    if(is_object($taskData))
                    {
                        // check multi upload limit // method call
                        $res2 = $this -> checkDocsUploadLimit($docCatType, [
                            'where' => 'circular_id = :circular_id AND task_id = :task_id AND doc_type = :doc_type AND deleted_at IS NULL',
                            'params' => [ 
                                'circular_id' => $taskData -> set_id, 
                                'task_id' => $taskData -> id, 
                                'doc_type' => $docCatType
                            ]
                        ]);

                        if(!$res2['err'])
                        {
                            $insertData['circular_id'] = $taskData -> set_id;
                            $insertData['task_id'] = $taskData -> id;
                            $insertData['err'] = 0;
                        }
                        else { $insertData['err'] = 1; $insertData['msg'] = $res2['msg']; }
                    }
                }

                if(!is_object($taskData))
                    $insertData['msg'] = Notifications::getNoti('circularTaskNotFound');

                break;
            }

            case '3': {
                // SINGLE ANSWER DOCS
                $this -> errorAndExit(); // method call
                break;
            }

            case '4': {
                // SINGLE ANNEX DOCS
                $this -> errorAndExit(); // method call
                break;
            }

            case '5': {
                // SINGLE COMPLIANCE DOCS
                if( !empty($circulr_id) && 
                    !empty($task_id) && 
                    !empty($ans_id) && 
                    !empty($com_asses_id))
                {
                    // find answer // find circular exists or not
                    $model = $this -> model('ComplianceCircularAnswerDataModel');
                    $table = $model -> getTableName();

                    $ansData = get_all_data_query_builder(1, $model, $table, [
                        'where' => 'cccm.circular_id = :circular_id AND ccad.task_id = :task_id AND ccad.id = :id AND ccad.com_master_id = :com_asses_id AND cccm.deleted_at IS NULL AND ccad.deleted_at IS NULL',
                        'params' => [ 
                            'circular_id' => $circulr_id, 'task_id' => $task_id,
                            'id' => $ans_id, 'com_asses_id' => $com_asses_id
                        ]
                    ], 'sql', "SELECT ccad.id, cccm.circular_id, cccm.com_status_id FROM ". $table ." ccad LEFT JOIN com_circular_compliance_master cccm ON ccad.com_master_id = cccm.id");

                    if(is_object($ansData))
                    {
                        // check multi upload limit // method call
                        $res2 = $this -> checkDocsUploadLimit($docCatType, [
                            'where' => '
                                circular_id = :circular_id AND 
                                task_id = :task_id AND 
                                answer_id = :answer_id AND 
                                assesment_id = :assesment_id AND 
                                doc_type = :doc_type AND 
                                deleted_at IS NULL',
                            'params' => [ 
                                'circular_id' => $circulr_id, 
                                'task_id' => $task_id, 
                                'answer_id' => $ans_id, 
                                'assesment_id' => $com_asses_id, 
                                'doc_type' => $docCatType
                            ]
                        ]);

                        if(!$res2['err'])
                        {
                            $insertData['circular_id'] = $circulr_id; $insertData['task_id'] = $task_id;
                            $insertData['answer_id'] = $ans_id; $insertData['assesment_id'] = $com_asses_id;
                            $insertData['err'] = 0;
                        }
                        else { $insertData['err'] = 1; $insertData['msg'] = $res2['msg']; }
                    }
                }

                if(!is_object($ansData))
                    $insertData['msg'] = Notifications::getNoti('ansDataNotFound');

                break;
            }

            case '6': {
                // SINGLE COMPLIANCE ANNEX DOCS
                $this -> errorAndExit(); // method call
                break;
            }

            case '7': {
                // ENTIRE COMPLIANCE ASSESMENT DOCS
                $this -> errorAndExit(); // method call
                break;
            }

            case '8': {
                // DOCS SUBMIT TO AUTHORITY
                $comSubmitData = null;

                // ENTIRE CIRCULAR DOCS
                if(!empty($submit_auth_id))
                {
                    // find submit exists or not
                    $model = $this -> model('ComplianceSubmitAuthorityModel');

                    $comSubmitData = $model -> getSingleSubmittedReport([
                        'where' => 'id = :id AND deleted_at IS NULL',
                        'params' => [ 'id' => $submit_auth_id ]
                    ]);

                    if(is_object($comSubmitData))
                    {
                        // check multi upload limit // method call
                        $res2 = $this -> checkDocsUploadLimit($docCatType, [
                            'where' => 'circular_id = :circular_id AND submit_auth_id = :submit_auth_id AND doc_type = :doc_type AND deleted_at IS NULL',
                            'params' => [ 
                                'circular_id' => $comSubmitData -> circular_id, 
                                'submit_auth_id' => $comSubmitData -> id, 
                                'doc_type' => $docCatType 
                            ]
                        ]);

                        if(!$res2['err'])
                        {
                            $insertData['circular_id'] = $comSubmitData -> circular_id;
                            $insertData['submit_auth_id'] = $comSubmitData -> id;
                            $insertData['err'] = 0;
                        }
                        else { $insertData['err'] = 1; $insertData['msg'] = $res2['msg']; }
                    }
                }

                if(!is_object($comSubmitData))
                    $insertData['msg'] = Notifications::getNoti('comSubmitNotFound');

                break;
            }
        }

        if(!$insertData['err'])
        {
            // no error found // move file particular folder
            $res2 = $this -> check_cco_upload_file_move($fileData, array_merge($this -> res, [
                'circular_id' => $insertData['circular_id'],
                'assesment_id' => $insertData['assesment_id']
            ]), 'move' );

            if(!$res2['err'])
            {
                // insert into database and return response // update insert array
                $insertData['file_name'] = $res2['file'];
                $insertData['doc_type'] = $docCatType;
                $insertData['file_type'] = $res2['file_type'];

                // unset keys
                unset($insertData['err'], $insertData['msg']);

                // insert in database
                $result = $this -> model::insert(
                    $this -> model -> getTableName(), $insertData
                );

                if($result)
                {
                    // data insert success // method call
                    $insertData['id'] = $this -> model::lastInsertId();
                    $dir = generate_com_circular_dir($insertData['circular_id'], 1, [
                        'host' => true, 'com_asses_id' => $insertData['assesment_id']
                    ]);

                    $extraParam = [ 'type' => 1 ];

                    if( isset($ansData) )
                        $extraParam = [ 'com_asses' => $ansData ];

                    // generate markup
                    $res['msg'] = null;
                    $res['markup'] = generate_com_docs_markup( (object)$insertData, $dir, $extraParam);
                    $res['err'] = false;
                }
            }
        }

        if($res['err'])
        {
            $this -> res['msg'] = !empty($insertData['msg']) ? $insertData['msg'] : $this -> res['msg'];
            $this -> errorAndExit(); // method call
        }

        echo json_encode($res);
        exit;
    }

    public function remove()
    {
        $docId = explode('/', $_GET['url']);
        $docUploadDetails = null;
        $res = $this -> res;

        if(sizeof($docId) == 3)
        {
            $docId = decrypt_ex_data($docId[2]);

            if(!empty($docId)) // find doc details
                $docUploadDetails = $this -> model -> getSingleCircularDoc([
                    'where' => 'id = :id AND deleted_at IS NULL',
                    'params' => [ 'id' => $docId ]
                ]);
        }

        if(!is_object($docUploadDetails))
            $this -> errorAndExit(); // method call

        // // check for assesment later
        $result = $this -> model::update(
            $this -> model -> getTableName(), 
            [
                'deleted_by_emp_id' => $this -> empId,
                'deleted_at' => date($GLOBALS['dateSupportArray'][2])
            ],
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $docId ]
            ]
        );

        if($result)
        {
            $res['err'] = false;
            $res['msg'] = Notifications::getNoti('comCircularDocRemovedSuccess');
        }

        echo json_encode($res);
        exit;
    }
}

?>