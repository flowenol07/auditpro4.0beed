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

class ComplianceCircularTaskMaster extends Controller
{

    public $me = null, $data, $request, $taskId;
    public $circularTaskModel, $model, $headerModel;

    public function __construct($me)
    {

        $this->me = $me;

        // top btn array
        $this->data['topBtnArr'] = [
            'default' => ['href' => SiteUrls::getUrl('dashboard')],
            'add' => ['href' => SiteUrls::getUrl('complianceCircularTaskMaster') . '/add'],
        ];

        // request object created
        $this->request = new Request();
        $this->circularTaskModel = $this->model('ComplianceCircularTaskModel');
        $this->headerModel = $this->model('ComplianceCircularHeaderModel');

        // db broader area -----------------------
        $model = $this->model('BroaderAreaModel');
        $table = $model->getTableName();

        $this->data['db_area_of_audit'] = get_all_data_query_builder(2, $model, $table, ['where' => 'deleted_at IS NULL', 'params' => []], 'sql', "SELECT id, name FROM " . $table);

        $this->data['db_area_of_audit'] = generate_data_assoc_array($this->data['db_area_of_audit'], 'id');

        // get all active circulars -----------------------
        $model = $this->model('ComplianceCircularSetModel');
        $table = $model->getTableName();

        $select = " SELECT ccs.id, ccs.authority_id, ccs.ref_no, ccs.name, ccs.circular_date, ccs.is_applicable, 
        COALESCE(cca.name, '" . ERROR_VARS['notFound'] . "') AS authority FROM 
        " . $table . " ccs LEFT JOIN com_circular_authority cca ON ccs.authority_id = cca.id";

        $this->data['circularData'] = get_all_data_query_builder(2, $model, $table, ['where' => 'ccs.is_applicable = 1 AND ccs.is_active = 1 AND ccs.deleted_at IS NULL', 'params' => []], 'sql', $select);
        $this->data['circularData'] = generate_data_assoc_array($this->data['circularData'], 'id');

        // get all active circulars
        $model = $this->model('RiskCategoryModel');
        $table = $model->getTableName();

        $select = " SELECT id, risk_category FROM " . $table . "";

        $this->data['rcData'] = get_all_data_query_builder(2, $model, $table, ['where' => 'is_active = 1 AND deleted_at IS NULL', 'params' => []], 'sql', $select);
        $this->data['rcData'] = generate_data_assoc_array($this->data['rcData'], 'id');
    }

    public function index()
    {


        $this->data['db_data_count'] = get_db_table_sql_count(
            $this,
            $this->circularTaskModel,
            $this->circularTaskModel->getTableName(),
            [],
            0,
            [
                'countQuery' => "SELECT COUNT(*) total_records FROM " . $this->circularTaskModel->getTableName() . " as ctm"
            ]
        );

        // re assign
        $this->data['db_data_count'] = $this->data['db_data_count']->total_records;

        if ($this->data['db_data_count'] > 0)
            $this->data['need_datatable'] = true;

        // load view // helper function call
        return return2View($this, $this->me->viewDir . 'index', ['request' => $this->request]);
    }

    public function dataTableAjax()
    {
        $whereArray = [
            'where' => 'ctm.deleted_at IS NULL',
            'params' => []
        ];

        $select = "SELECT 
    ctm.*, 
    COALESCE(csm.name, 'Not Found') AS set_name
FROM 
    com_circular_task_master ctm
LEFT JOIN 
    com_circular_set_master csm 
    ON ctm.set_id = csm.id";

        $funcData = generate_datatable_data(
            $this,
            $this->circularTaskModel,
            ["csm.name", "ctm.task", "ctm.risk_category_id"], // search parameters
            $whereArray,
            0,
            [
                'query' => $select,
                'countQuery' => "SELECT COUNT(*) total_records FROM com_circular_task_master ctm LEFT JOIN com_circular_set_master csm ON ctm.set_id = csm.id"
            ]
        );

        //  $funcData = generate_datatable_data($this, $this -> circularTaskModel, ["set_id", "task", "risk_category_id"], $whereArray);


        if (is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0) {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

            $srNo = 1;
            $headerIds = [];
            $headerData = null;

            // loop through get header ids
            foreach ($funcData['dbData'] as $cTaskId => $cTaskDetails) {
                $headerIds[] = $cTaskDetails->header_id;
            }

            if (sizeof($headerIds) > 0) {
                $headerIds = array_unique($headerIds);
                $model = $this->model('ComplianceCircularHeaderModel');
                $table = $model->getTableName();

                $select = " SELECT id, name FROM " . $table . "";

                $headerData = get_all_data_query_builder(2, $model, $table, ['where' => 'id IN (' . implode(',', $headerIds) . ') AND is_active = 1 AND deleted_at IS NULL', 'params' => []], 'sql', $select);
                $headerData = generate_data_assoc_array($headerData, 'id');
            }

            foreach ($funcData['dbData'] as $cTaskId => $cTaskDetails) {
                $idEncrypt = encrypt_ex_data($cTaskDetails->id);

                $circularName = (is_array($this->data['circularData']) && isset($this->data['circularData'][$cTaskDetails->set_id])) ? $this->data['circularData'][$cTaskDetails->set_id]->name : ERROR_VARS['notFound'];

                $riskCategory = (is_array($this->data['rcData']) && isset($this->data['rcData'][$cTaskDetails->risk_category_id])) ? $this->data['rcData'][$cTaskDetails->risk_category_id]->risk_category : ERROR_VARS['notFound'];

                $cHeader = is_array($headerData) && isset($headerData[$cTaskDetails->header_id]) ? $headerData[$cTaskDetails->header_id]->name : ERROR_VARS['notFound'];

                $markup = '<p class="font-sm mb-1"><span class="font-medium">Header: </span>' . $cHeader . '</p>';
                $markup .= '<p class="text-primary mb-0">' . $cTaskDetails->task . '</p>';

                $cDataArray = [
                    "sr_no" => $srNo,
                    "set_id" => $circularName,
                    "task"  => $markup,
                    "risk_category_id"  => $riskCategory,
                    "status" => check_active_status($cTaskDetails->is_active, 1, 1, 1),
                    "action" => ""
                ];

                $srNo++;

                if ($cTaskDetails->is_active == 1) {
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl($this->me->url) . '/update/' . $idEncrypt, 'extra' => view_tooltip('Update')]);

                    $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl($this->me->url) . '/delete/' . $idEncrypt, 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"']);
                } else {
                    $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl($this->me->url) . '/status/' . $idEncrypt, 'extra' => view_tooltip('Activate')]);
                }

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }

            unset($headerData);
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    private function validateData($methodType = 'add', $setId = '')
    {
        $validationArray = [
            'circular_id' => 'required|array_key[circular_array, circularNotExists]',
            'header_id' => 'required|array_key[header_array, headerNotExists]',
            'priority_id' => '',
            'task' => 'required',
            'risk_category_id' => '',
            'business_risk' => '',
            'control_risk' => '',
            'area_of_audit_id' => '',
        ];

        // unset val
        if ($this->data['disableC&H'])
            unset($validationArray['circular_id'], $validationArray['header_id']);

        $validateDataArray = [
            'circular_array' => $this->data['circularData'],
            'header_array' => $this->data['headerData'],
            //'priority_array' => COMPLIANCE_PRO_ARRAY['compliance_priority'],
            //'risk_category_array' => $this -> data['rcData'],
            // 'risk_array' => RISK_PARAMETERS_ARRAY,
            //'area_of_audit_array' => $this -> data['db_area_of_audit'],
        ];

        Validation::validateData($this->request, $validationArray, $validateDataArray);

        // validation check
        if ($this->request->input('error') > 0) {
            Validation::flashErrorMsg();
            return false;
        } else
            return true;
    }

    private function postArray($methodType = 'add')
    {
        $dataArray = array(
            'header_id' => $this->request->input('header_id'),
            'set_id' => $this->request->input('circular_id'),
            'priority_id' => "1",
            'task' => $this->request->input('task'),
            'risk_category_id' => 10,
            'business_risk' => 1,
            'control_risk' => 1,
            'area_of_audit_id' => 5845,
            'answer_given' => NULL,
            'option_id' => NULL,
            'admin_id' => Session::get('emp_id'),
        );

        // unset val
        if ($this->data['disableC&H'])
            unset($dataArray['set_id'], $dataArray['header_id']);

        return $dataArray;
    }

    public function add()
    {
        //set form url
        $this->data['lastURL'] = SiteUrls::setUrl($this->me->url);
        $this->me->url = SiteUrls::setUrl($this->me->url . '/add');
        $this->me->pageHeading = 'Add Task';
        $this->data['btn_type'] = 'add';
        $this->data['need_select'] = true;

        // top btn array
        $this->data['topBtnArr'] = [
            'default' => ['href' => $this->data['lastURL']],
        ];

        // create empty instance for default values in form
        $this->data['db_task_data'] = $this->circularTaskModel->emptyInstance();
        $this->data['headerData'] = [];
        $this->data['disableC&H'] = false;
        $this->data['backto'] = '';

        // default set
        if (!$this->request->has('risk_category_id'))
            $this->request->setInputCustom('risk_category_id', 10);

        // default set
        if (!$this->request->has('business_risk'))
            $this->request->setInputCustom('business_risk', 1);

        // default set
        if (!$this->request->has('control_risk'))
            $this->request->setInputCustom('control_risk', 1);

        // default set
        if (!$this->request->has('area_of_audit_id'))
            $this->request->setInputCustom('area_of_audit_id', 5845);

        // post method after form submit
        $this->request::method("POST", function () {

            // find header data ajax call // method call
            $headerData = $this->findHeaderAjax(1);

            if (
                isset($headerData['data']) &&
                isset($headerData['success'])
            )
                $this->data['headerData'] = $headerData['data'];

            // unset val
            unset($headerData);

            // validation check
            if ($this->validateData()) {
                // insert in database
                $result = $this->circularTaskModel::insert(
                    $this->circularTaskModel->getTableName(),
                    $this->postArray()
                );

                if (!$result)
                    return Except::exc_404(Notifications::getNoti('somethingWrong'));

                // after insert data redirect to set dashboard
                Validation::flashErrorMsg('circularTaskAddedSuccess', 'success');
                Redirect::to($this->data['lastURL']);
            }
        });

        // load view // helper function call
        return return2View($this, $this->me->viewDir . 'form', [
            'request' => $this->request,
            'data' => $this->data
        ]);
    }

    public function update($getRequest)
    {

        $this->taskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // set form url
        $this->data['lastURL'] = SiteUrls::setUrl($this->me->url);
        $this->me->url = SiteUrls::setUrl($this->me->url . '/update/' . encrypt_ex_data($this->taskId));
        $this->me->pageHeading = 'Update Circular';

        // get data // method call
        $this->getDataOr404(1);
        $this->data['disableC&H'] = $this->checkTaskAssign(); // method call
        $this->data['backto'] = '';

        if ($this->request->has('backto')) {
            $this->data['backto'] = '?backto=1';
            $this->data['lastURL'] = SiteUrls::getUrl('complianceCircularSetMaster') . '/view-circular/' . encrypt_ex_data($this->data['db_task_data']->set_id);
        }

        // top btn array
        $this->data['topBtnArr'] = [
            'default' => ['href' => $this->data['lastURL']],
        ];

        $this->data['need_select'] = true;
        $this->data['btn_type'] = 'update';

        $this->data['headerData'] = [];

        // set header_id manually in ajax
        if (!$this->request->has("circular_id"))
            $this->request->setInputCustom("circular_id", $this->data['db_task_data']->set_id);

        // find header data ajax call // method call
        $headerData = $this->findHeaderAjax(1);

        if (
            isset($headerData['data']) &&
            isset($headerData['success'])
        )
            $this->data['headerData'] = $headerData['data'];

        // unset val
        unset($headerData);

        // post method after form submit
        $this->request::method("POST", function () {

            // validation check
            if ($this->validateData()) {
                // insert in database
                $result = $this->circularTaskModel::update(
                    $this->circularTaskModel->getTableName(),
                    $this->postArray(),
                    [
                        'where' => 'id = :id',
                        'params' => ['id' => $this->taskId]
                    ]
                );

                if (!$result)
                    return Except::exc_404(Notifications::getNoti('somethingWrong'));

                // after insert data redirect to set dashboard
                Validation::flashErrorMsg('circularTaskUpdatedSuccess', 'success');
                Redirect::to($this->data['lastURL']);
            }
        });

        // load view // helper function call
        return return2View($this, $this->me->viewDir . 'form', [
            'request' => $this->request,
            'data' => $this->data
        ]);
    }

    public function delete($getRequest)
    {

        $this->taskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data // method call
        $this->getDataOr404(1);
        $this->data['disableC&H'] = $this->checkTaskAssign(); // method call
        $this->data['lastURL'] = SiteUrls::getUrl($this->me->id);

        if ($this->request->has('backto')) {
            $this->data['lastURL'] = SiteUrls::getUrl('complianceCircularSetMaster') . '/view-circular/' . encrypt_ex_data($this->data['db_task_data']->set_id);
        }

        if ($this->data['disableC&H']) {
            // task assigned to task set or compliance
            Except::exc_404("The task is already assigned to a task set or compliance has started, so it cannot be removed.");
            exit;
        }

        $result = $this->circularTaskModel::delete($this->circularTaskModel->getTableName(), [
            'where' => 'id = :id',
            'params' => ['id' => $this->taskId]
        ]);

        if (!$result)
            return Except::exc_404(Notifications::getNoti('errorDeleting'));

        //after insert data redirect to set dashboard
        Validation::flashErrorMsg('circularTaskDeletedSuccess', 'success');
        Redirect::to($this->data['lastURL']);
    }

    public function findHeaderAjax($needData = false)
    {

        $res_array = ['msg' => Notifications::getNoti('somethingWrong'), 'res' => 'err'];
        $circularId = $this->request->has("circular_id") ? $this->request->input("circular_id") : null;

        if (!empty($circularId)) {
            $model = $this->model('ComplianceCircularHeaderModel');
            $table = $model->getTableName();

            $headerData = get_all_data_query_builder(2, $model, $table, [
                'where' => 'circular_set_id = :circular_set_id AND is_active = 1 AND deleted_at IS NULL',
                'params' => ['circular_set_id' => $circularId]
            ], 'sql', "SELECT id, name, is_active FROM " . $table);

            $headerData = generate_data_assoc_array($headerData, 'id');

            if (is_array($headerData) && sizeof($headerData) > 0) {
                $res_array['data'] = $headerData;
                $res_array['success'] = 1;
            } else
                $res_array['msg'] = Notifications::getNoti('noDataFound');

            unset($headerData);
        }

        if (!$needData) {
            echo json_encode($res_array);
            exit;
        }

        return $res_array;
    }

    public function bulkUploadCircularTask()
    {

        // set form url
        $this->data['lastURL'] = SiteUrls::setUrl($this->me->url);
        $this->me->url = SiteUrls::setUrl($this->me->url . '/bulk-upload-circular-task');
        $this->me->menuKey = 'complianceCircularBulkUploadTasks';

        $this->me->pageHeading = 'Add Bulk Task';
        $this->data['btn_type'] = 'add';
        $this->data['need_select'] = true;

        //sample csv
        $this->data['sample_csv'] = URL . 'docs/sample/compliance-pro-task-upload-sample.csv';

        // top btn array
        $this->data['topBtnArr'] = [
            'default' => ['href' => $this->data['lastURL']],
        ];

        $this->data['headersData'] = $this->headerModel->getAllCircularHeader([
            'where' => 'circular_set_id = :circular_set_id AND is_active = 1 AND deleted_at IS NULL',
            'params' => ['circular_set_id' => $this->request->input('circular_id')]
        ]);

        // post method after form submit
        $this->request::method("POST", function () {

            // validate form data
            $validationArray = [
                'circular_id' => 'required|array_key[circular_array, circularNotExists]',
                'csv_file_upload' => 'file_upload[csv]'
            ];

            Validation::validateData($this->request, $validationArray, [
                'circular_array' => $this->data['circularData']
            ]);

            // validation check
            if (!$this->request->input('error') > 0) {
                // GENERAL TASK add in header
                $csv_data = array('task' => [], 'header' => []);
                $error_data = array();
                $col_mismatch = FALSE;
                $row = 1;
                $sessionEmpId = Session::get('emp_id');

                if (($handle = fopen($_FILES['csv_file_upload']['tmp_name'], "r")) !== FALSE) {
                    while (($c_data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                        if (sizeof($c_data) != 7) {
                            $col_mismatch = true;
                            break;
                        }

                        if ($row > 1) {
                            $cc_data = array(
                                'header_id' => 0,
                                'header' => trim_str($c_data[0]),
                                'set_id' => $this->request->input('circular_id'),
                                'priority_id' => trim_str($c_data[2]),
                                'task' => trim_str($c_data[1]),
                                'risk_category_id' => trim_str($c_data[3]),
                                'business_risk' => trim_str($c_data[4]),
                                'control_risk' => trim_str($c_data[5]),
                                'area_of_audit_id' => trim_str($c_data[6]),
                                'answer_given' => NULL,
                                'option_id' => NULL,
                                'admin_id' => $sessionEmpId,
                            );

                            // VALIDATIONS START HEADER
                            if (empty($cc_data['header']))
                                $cc_data['error']['risk_category_id'] = 'Error: header missing';
                            else {

                                $headerFoundFalse = false;

                                // check header exists or not
                                if (
                                    is_array($this->data['headersData']) &&
                                    sizeof($this->data['headersData']) > 0
                                ) {
                                    foreach ($this->data['headersData'] as $cHeaderDetails) {

                                        if ($cc_data['header'] == $cHeaderDetails->name) {
                                            $cc_data['header_id'] = $cHeaderDetails->id;
                                            $headerFoundFalse = true;
                                            break;
                                        }
                                    }
                                }

                                if (!$headerFoundFalse) {
                                    if (
                                        $cc_data['header_id'] == 0 &&
                                        !array_key_exists($cc_data['header'], $csv_data['header'])
                                    )
                                        $csv_data['header'][$cc_data['header']] = [
                                            'circular_set_id' => $this->request->input('circular_id'),
                                            'name' => $cc_data['header'],
                                            'is_active' => 1,
                                            'admin_id' => $sessionEmpId
                                        ];
                                }
                            }

                            if (empty($cc_data['task']))
                                $cc_data['error']['task'] = 'Error: task missing';

                            // CHECK PRIORITY ---------------
                            $cc_data = $this->validateRiskParam($cc_data, 'priority_id', 'Error: priority not exists');

                            // CHECK BUSINESS RISK ---------------
                            $cc_data = $this->validateRiskParam($cc_data, 'business_risk', 'Error: business risk not exists');

                            // CHECK CONTROL RISK ---------------
                            $cc_data = $this->validateRiskParam($cc_data, 'control_risk', 'Error: control risk not exists');

                            // CHECK RISK CATEGORY ---------------
                            $riskId = 0;

                            if (is_array($this->data['rcData']) && sizeof($this->data['rcData']) > 0) {
                                foreach ($this->data['rcData'] as $rcId => $rcData) {

                                    if ($rcData->risk_category == $cc_data['risk_category_id']) {
                                        $riskId = $rcId;
                                        break;
                                    }
                                }
                            }

                            if ($riskId == 0)
                                $cc_data['error']['risk_category_id'] = 'Error: risk category not exists';
                            else
                                $cc_data['risk_category_id'] = $riskId;

                            unset($riskId);

                            // BROADER AREA ---------------
                            $broaderAreaId = 0;

                            if (is_array($this->data['db_area_of_audit']) && sizeof($this->data['db_area_of_audit']) > 0) {
                                foreach ($this->data['db_area_of_audit'] as $baId => $baData) {

                                    if ($baData->name == $cc_data['area_of_audit_id']) {
                                        $broaderAreaId = $baId;
                                        break;
                                    }
                                }
                            }

                            if ($broaderAreaId == 0)
                                $cc_data['error']['area_of_audit_id'] = 'Error: broader area not exists';
                            else
                                $cc_data['area_of_audit_id'] = $broaderAreaId;

                            unset($broaderAreaId);

                            //push data in array
                            $csv_data['task'][] = $cc_data;

                            if (
                                isset($cc_data['error']) &&
                                is_array($cc_data['error']) &&
                                sizeof($cc_data['error']) > 0
                            )
                                $error_data[] = $cc_data;
                        }

                        $row++;
                    }
                }

                if (is_array($error_data) && sizeof($error_data) > 0 || $col_mismatch) {
                    //date error
                    Validation::incrementError($this->request);
                    $this->request->setInputCustom('csv_file_upload_err', 'somethingWrong');

                    $this->data['csv_data'] = sizeof($csv_data);
                    $this->data['err_task_data'] = $error_data;
                    unset($error_data);
                } else
                    $this->data['csv_data'] = $csv_data;
            }

            if (!$this->request->input('error') > 0) {
                $err = false;

                // check new headers then insert 
                if (
                    is_array($this->data['csv_data']['header']) &&
                    sizeof($this->data['csv_data']['header']) > 0
                ) {
                    // header model
                    $result = $this->headerModel::insertMultiple($this->headerModel->getTableName(), array_values($this->data['csv_data']['header']));

                    if (!$result) {
                        Validation::incrementError($this->request);
                        $this->request->setInputCustom('csv_file_upload_err', 'somethingWrong');
                        $err = true;
                    }
                }

                if (!$err) {
                    // find all headers
                    $this->data['headersData'] = $this->headerModel->getAllCircularHeader([
                        'where' => 'circular_set_id = :circular_set_id AND is_active = 1 AND deleted_at IS NULL',
                        'params' => ['circular_set_id' => $this->request->input('circular_id')]
                    ]);

                    if (is_array($this->data['headersData']) && sizeof($this->data['headersData']) > 0) {
                        foreach ($this->data['headersData'] as $cHeaderData) {
                            foreach ($this->data['csv_data']['task'] as $cInd => $cTaskData) {

                                if ($cTaskData['header_id'] == 0 && $cHeaderData->name == $cTaskData['header']) {
                                    // assigned header id
                                    $this->data['csv_data']['task'][$cInd]['header_id'] = $cHeaderData->id;
                                }

                                if ($this->data['csv_data']['task'][$cInd]['header_id'] != 0)
                                    unset($this->data['csv_data']['task'][$cInd]['header']);
                            }
                        }
                    } else {
                        Validation::incrementError($this->request);
                        $this->request->setInputCustom('csv_file_upload_err', 'somethingWrong');
                    }
                }

                // header model
                $result = $this->circularTaskModel::insertMultiple($this->circularTaskModel->getTableName(), $this->data['csv_data']['task']);

                if (!$result)
                    Validation::flashErrorMsg('somethingWrong', 'warning');
                else
                    Validation::flashErrorMsg('circularTaskAddedSuccess', 'success');

                Redirect::to($this->data['lastURL']);
            }
        });

        // load view // helper function call
        return return2View($this, $this->me->viewDir . 'bulk-upload-circular-task-form', [
            'request' => $this->request,
            'data' => $this->data
        ]);
    }

    private function validateRiskParam($cc_data, $key, $errorMessage)
    {

        $riskId = 0;

        if (is_array(RISK_PARAMETERS_ARRAY) && sizeof(RISK_PARAMETERS_ARRAY) > 0) {
            foreach (RISK_PARAMETERS_ARRAY as $cRiskId => $cRiskData) {

                if ($cRiskData['title'] == $cc_data[$key]) {
                    $riskId = $cRiskId;
                    break;
                }
            }
        }

        if ($riskId == 0)
            $cc_data['error'][$key] = $errorMessage;
        else
            $cc_data[$key] = $riskId;

        return $cc_data;
    }

    private function getDataOr404($optional = 1)
    {

        $filter = [
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => ['id' => $this->taskId]
        ];

        if ($optional == 1)
            $filter['where'] .= ' AND is_active = 1';

        // get data
        if (!empty($this->taskId))
            $this->data['db_task_data'] = $this->circularTaskModel->getSingleCircularTask($filter);

        if (!isset($this->data['db_task_data']) || empty($this->data['db_task_data'])) {
            Except::exc_404(Notifications::getNoti('errorFinding'));
            exit;
        }
    }

    private function checkTaskAssign()
    {

        $resCount = 0;
        $model = $this->model('ComplianceCircularTaskSetModel');
        $table = $model->getTableName();

        $assignedData = get_all_data_query_builder(1, $model, $table, [], 'sql', "SELECT COUNT(*) AS count_of_rows FROM " . $table . " WHERE FIND_IN_SET('" . $this->data['db_task_data']->id . "', task_ids) > 0");

        if ($assignedData->count_of_rows > 0)
            $resCount = $assignedData->count_of_rows;
        else {
            // check in compliance master (assement)
            $model = $this->model('ComplianceCircularAssesMasterModel');
            $table = $model->getTableName();

            $assignedData = get_all_data_query_builder(1, $model, $table, [], 'sql', "SELECT COUNT(*) AS count_of_rows FROM " . $table . " WHERE FIND_IN_SET('" . $this->data['db_task_data']->id . "', task_ids) > 0");

            $resCount = $assignedData->count_of_rows;
        }

        return $resCount > 0;
    }
}
