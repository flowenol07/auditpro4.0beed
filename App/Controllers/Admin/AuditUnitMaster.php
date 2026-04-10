<?php

namespace Controllers\Admin;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;
use Core\DBCommonFunc;

class AuditUnitMaster extends Controller
{

    public $me = null, $data, $request, $auditUnit, $auditUnitId;
    public $auditUnitModel;

    public $auditSectionModel, $auditSection;

    public $employeeModel, $employeeData;

    public function __construct($me)
    {
        $this->me = $me;

        //top btn array
        $this->data['topBtnArr'] = [
            'default' => ['href' => SiteUrls::getUrl('auditUnitMaster')],
        ];

        // request object created
        $this->request = new Request();

        // find current audit unit model
        $this->auditUnitModel = $this->model('AuditUnitModel');

        $this->auditSectionModel = $this->model('AuditSectionModel');

        $this->employeeModel = $this->model('EmployeeModel');

        //Search in select dropdown
        $this->data['need_select'] = true;

        //get all sections
        $this->auditSection = $this->auditSectionModel->getAllAuditSection(['where' => 'is_active = 1 AND deleted_at IS NULL']);

        $this->data['db_audit_section'] = generate_array_for_select($this->auditSection, 'id', 'name');

        //get all units
        $this->auditUnit = $this->auditUnitModel->getAllAuditUnit(['where' => 'deleted_at IS NULL']);

        $this->data['db_audit_unit'] = generate_array_for_select($this->auditUnit, 'id', 'name');

        //get all employee
        $this->employeeData = $this->employeeModel->getAllEmployees(['where' => 'deleted_at IS NULL AND user_type_id = 3']);

        $this->data['db_employee_data'] = DBCommonFunc::getAllEmployeeData($this->employeeModel, ['where' => 'user_type_id = 3 AND is_active = 1 AND deleted_at IS NULL']);

        $this->data['db_employee_data'] = generate_data_assoc_array($this->data['db_employee_data'], 'id');
    }

    private function validateData($methodType = 'add', $auditUnitId = '')
    {
        $uniqueWhere = [
            'model' => $this->auditUnitModel,
            'where' => 'audit_unit_code = :audit_unit_code AND deleted_at IS NULL',
            'params' => [
                'audit_unit_code' => $this->request->input('audit_unit_code'),
            ]
        ];

        if ($methodType == 'update' && !empty($auditUnitId)) {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $auditUnitId;
        }

        $validationArray = [
            'section_type_id' => 'required|array_key[section_type_array, auditSectionSelect]',
            'audit_unit_code' => 'required|regex[alphaNumricRegex, auditUnitCodeSelect]|is_unique[unique_data, auditUnit]',
            'name' => 'required|regex[alphaNumricRegex, name]',
            'branch_head_id' =>  'required|array_key[employee_array, employeeSelect]',
            'branch_subhead_id' => 'array_key[employee_array, employeeSelect]|match[branch_head_id, headSubheadMatched]',
            'last_audit_date' => 'required|regex[dateRegex, dateError]',
        ];

        if ($methodType == 'update')
            unset($validationArray['last_audit_date']);

        Validation::validateData($this->request, $validationArray, [
            'section_type_array' => $this->data['db_audit_section'],
            'employee_array' => $this->data['db_employee_data'],
            'unique_data' => $uniqueWhere
        ]);

        //check unit ( HO department )
        if (
            !$this->request->has('section_type_id_err') &&
            $this->request->input('section_type_id') != '' &&
            $this->request->input('section_type_id') != 1
        ) {
            $filterArr = [
                'where' => 'section_type_id = :section_type_id AND deleted_at IS NULL',
                'params' => ['section_type_id' => $this->request->input('section_type_id')]
            ];

            //check HO record exist
            $checkAuditUnit = $this->auditUnitModel->getAllAuditUnit($filterArr);

            if ($methodType == 'update' && !empty($auditUnitId)) {
                if (is_array($checkAuditUnit) && sizeof($checkAuditUnit) > 1) {
                    Validation::incrementError($this->request);
                    $this->request->setInputCustom('section_type_id_err', 'auditSectionDupliacateSelect');
                }
            } else {
                if (is_array($checkAuditUnit) && sizeof($checkAuditUnit) >= 1) {
                    Validation::incrementError($this->request);
                    $this->request->setInputCustom('section_type_id_err', 'auditSectionDupliacateSelect');
                }
            }
        }

        /* $multiComplianceIds = null;

        if($this -> request -> input('multi_compliance_ids') != '')
            $multiComplianceIds = explode (",", $this -> request -> input('multi_compliance_ids'));

        $multiComplianceIds = is_array($multiComplianceIds) ? $multiComplianceIds : [];

        if(is_array($multiComplianceIds) && !empty($multiComplianceIds))
        {
            $diffArray = array_diff($multiComplianceIds, (array_keys($this -> data['db_employee_data'])));
    
            if((is_array($diffArray) && sizeof($diffArray) > 0 ) || sizeof($multiComplianceIds) > 5)
            {
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('multi_compliance_ids_err', 'employeeDataError');
            }
        } */

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
            'section_type_id' => $this->request->input('section_type_id'),
            'audit_unit_code' => $this->request->input('audit_unit_code'),
            'name' => string_operations($this->request->input('name'), 'upper'),
            'branch_head_id' => $this->request->input('branch_head_id'),
            'branch_subhead_id' => $this->request->input('branch_subhead_id'),
            'admin_id' => Session::get('emp_id'),
        );

        if ($methodType == 'add') // default 1 month
        {
            $dataArray['frequency'] = 1;
            $dataArray['last_audit_date'] = $this->request->input('last_audit_date');
        }

        // $dataArray['multi_compliance_ids'] = $this -> request -> input('multi_compliance_ids');

        return  $dataArray;
    }

    public function index()
    {

        //top btn array
        $this->data['topBtnArr'] = [
            'default' => ['href' => SiteUrls::getUrl('dashboard')]
        ];

        // check admin access
        if (check_admin_action($this, ['lite_access' => 0, 'super_access' => 1]))
            $this->data['topBtnArr']['add'] = ['href' => SiteUrls::getUrl('auditUnitMaster') . '/add'];

        // total number of records without filtering // function call
        $this->data['db_data_count'] = get_db_table_sql_count(
            $this,
            $this->auditUnitModel,
            $this->auditUnitModel->getTableName()
        );

        // re assign
        $this->data['db_data_count'] = $this->data['db_data_count']->total_records;

        $this->data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this->me->viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this->auditUnitModel, ["audit_unit_code", "section_type_id", "name", "last_audit_date"]);

        $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0, 'super_access' => 1]);

        if (is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0) {
            foreach ($funcData['dbData'] as $cAuditUnitId => $cAuditUnitDetails) {
                $cDataArray = [
                    "audit_unit_code" => $cAuditUnitDetails->audit_unit_code,
                    "section_type_id" => ($this->data['db_audit_section'][$cAuditUnitDetails->section_type_id] ?? ERROR_VARS['notFoundSpan']),
                    "name" => "",
                    "last_audit_date" => $cAuditUnitDetails->last_audit_date,
                    "status" => check_active_status($cAuditUnitDetails->is_active, 1, 1, 1),
                    "action" => ""
                ];

                $cDataArray["name"] = '<p class="font-medium text-primary mb-0">' . $cAuditUnitDetails->name . '</p>
                    <p class="font-sm text-secondary mb-0"><span class="font-medium">Head : </span>' . ($this->data['db_employee_data'][$cAuditUnitDetails->branch_head_id]->combined_name ?? ERROR_VARS['notFoundSpan']) . '</p>
                    <p class="font-sm text-secondary mb-0"><span class="font-medium">Sub Head : </span>' . ($this->data['db_employee_data'][$cAuditUnitDetails->branch_subhead_id]->combined_name ?? ERROR_VARS['notFoundSpan']) . '</p>';

                if ($cAuditUnitDetails->is_active == 1) {

                    $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl($this->me->url) . '/update/' . encrypt_ex_data($cAuditUnitDetails->id), 'extra' => view_tooltip('Update')]);

                    if ($CHECK_ADMIN_ACTION) {
                        $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl($this->me->url) . '/delete/' . encrypt_ex_data($cAuditUnitDetails->id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"']);

                        $cDataArray["action"] .= generate_link_button('inactive', ['href' => SiteUrls::setUrl($this->me->url) . '/status/' . encrypt_ex_data($cAuditUnitDetails->id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"']);

                        if ($cAuditUnitDetails->section_type_id == 1)
                            $cDataArray["action"] .= generate_link_button('link', ['href' => SiteUrls::getUrl('targetMaster') . '?audit=' . encrypt_ex_data($cAuditUnitDetails->id), 'extra' => view_tooltip('Add Target')]);
                    }
                } else {

                    if ($CHECK_ADMIN_ACTION)
                        $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl($this->me->url) . '/status/' . encrypt_ex_data($cAuditUnitDetails->id), 'extra' => view_tooltip('Activate')]);
                }

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function add()
    {
        // check admin access
        if (!check_admin_action($this, ['lite_access' => 0, 'super_access' => 1])) {
            Except::exc_access_restrict();
            exit;
        }

        //set form url
        $this->me->url = SiteUrls::setUrl($this->me->url . '/add');
        $this->me->pageHeading = 'Add Audit Unit';

        // create empty instance for default values in form
        $this->data['db_data'] = $this->auditUnitModel->emptyInstance();
        $this->data['db_data']->last_audit_date = date('Y') . '-03-31';
        $this->data['btn_type'] = 'add';
        $this->data['need_calender'] = true;

        //default get method
        $this->request::method('GET', function () {

            // load view //helper function call
            return return2View($this, $this->me->viewDir . 'form', [
                'request' => $this->request,
                'data' => $this->data
            ]);
        });

        //post method after form submit
        $this->request::method("POST", function () {

            //validation check
            if (!$this->validateData()) {
                // load view
                return return2View($this, $this->me->viewDir . 'form', [
                    'request' => $this->request,
                    'data' => $this->data
                ]);
            } else {
                // insert in database
                $result = $this->auditUnitModel::insert(
                    $this->auditUnitModel->getTableName(),
                    $this->postArray()
                );

                if (!$result)
                    return Except::exc_404(Notifications::getNoti('somethingWrong'));

                //after insert data redirect to audit unit dashboard
                Validation::flashErrorMsg('auditUnitAddedSuccess', 'success');
                Redirect::to(SiteUrls::getUrl('auditUnitMaster'));
            }
        });
    }

    public function update($getRequest)
    {

        // print_r($this -> postArray('update'));
        $this->auditUnitId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this->me->url = SiteUrls::setUrl($this->me->url . '/update/' . encrypt_ex_data($this->auditUnitId));
        $this->me->pageHeading = 'Update Audit Unit';

        // get data //method call
        $this->data['db_data'] = $this->getDataOr404(['id' => $this->auditUnitId]);

        //return if data not found
        if (!is_object($this->data['db_data']))
            return $this->data['db_data'];

        $this->data['btn_type'] = 'update';
        $this->data['need_calender'] = true;

        // Check if there's an active assessment for this audit unit
        $assessmentModel = $this->model('AuditAssesmentModel');
        $activeAssessment = $assessmentModel->getAllAuditAssesment([
            'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14) AND deleted_at IS NULL',
            'params' => ['audit_unit_id' => $this->auditUnitId],
            'order' => 'id DESC'
        ]);

        // Check if any assessment has status > 4
        $canUpdate = true;
        $restrictedStatus = '';
        $restrictedAssessmentId = '';

        if (!empty($activeAssessment)) {
            foreach ($activeAssessment as $assessment) {
                // If assessment status is greater than 4, block the update
                if ($assessment->audit_status_id > 4) {
                    $canUpdate = false;

                    // Get status title from ASSESMENT_TIMELINE_ARRAY
                    $statusTitle = 'Unknown';
                    if (defined('ASSESMENT_TIMELINE_ARRAY') && isset(ASSESMENT_TIMELINE_ARRAY[$assessment->audit_status_id])) {
                        $statusTitle = ASSESMENT_TIMELINE_ARRAY[$assessment->audit_status_id]['title'];
                    }

                    $restrictedStatus = $statusTitle;
                    $restrictedAssessmentId = $assessment->id;
                    break;
                }
            }
        }

        // Store the update permission in data array for view
        $this->data['can_update_audit_unit'] = $canUpdate;
        $this->data['restricted_status'] = $restrictedStatus;
        $this->data['has_active_assessment'] = !empty($activeAssessment);

        //form
        $this->request::method('GET', function () {

            // load view
            return return2View($this, $this->me->viewDir . 'form', ['request' => $this->request]);
        });

        //post method after form submit
        $this->request::method("POST", function () {

            // Check again if update is allowed (double-check for security)
            $assessmentModel = $this->model('AuditAssesmentModel');
            $activeAssessment = $assessmentModel->getAllAuditAssesment([
                'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14) AND deleted_at IS NULL',
                'params' => ['audit_unit_id' => $this->auditUnitId]
            ]);

            $canUpdate = true;
            $restrictedStatus = '';

            if (!empty($activeAssessment)) {
                foreach ($activeAssessment as $assessment) {
                    // Only allow update if status is <= 4
                    if ($assessment->audit_status_id > 4) {
                        $canUpdate = false;

                        $statusTitle = 'Unknown';
                        if (defined('ASSESMENT_TIMELINE_ARRAY') && isset(ASSESMENT_TIMELINE_ARRAY[$assessment->audit_status_id])) {
                            $statusTitle = ASSESMENT_TIMELINE_ARRAY[$assessment->audit_status_id]['title'];
                        }

                        $restrictedStatus = $statusTitle . ' (Status ID: ' . $assessment->audit_status_id . ')';
                        break;
                    }
                }
            }

            if (!$canUpdate) {
                // Cannot update because assessment is in restricted status
                $errorMessage = 'You cannot update this Audit Unit because it has an active Assessment under ' . $restrictedStatus;

                // Store error message in session
                Session::set('error', $errorMessage);
                Session::set('flash', ['type' => 'error', 'message' => $errorMessage]);
                $_SESSION['error'] = $errorMessage;

                // Also try Validation class
                Validation::flashErrorMsg($errorMessage, 'error');

                // Redirect back to audit unit list
                Redirect::to(SiteUrls::getUrl('auditUnitMaster'));
                return;
            }

            //validation check
            if (!$this->validateData('update', $this->auditUnitId)) {
                // load view
                return return2View($this, $this->me->viewDir . 'form', ['request' => $this->request]);
            } else {
                // FIRST, update the audit unit
                $result = $this->auditUnitModel::update(
                    $this->auditUnitModel->getTableName(),
                    $this->postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => ['id' => $this->auditUnitId]
                    ]
                );

                if (!$result)
                    return Except::exc_404(Notifications::getNoti('somethingWrong'));

                // Check if assign audit head checkbox is checked
                if ($this->request->input('assign_audit_head') == '1' && $this->data['has_active_assessment']) {

                    // Get the newly selected branch_head_id from the form submission
                    $newBranchHeadId = $this->request->input('branch_head_id');

                    if (!empty($newBranchHeadId)) {
                        // Get current active assessment for this audit unit
                        $currentAssessment = $assessmentModel->getAllAuditAssesment([
                            'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id IN (1,2,3,4) AND deleted_at IS NULL',
                            'params' => ['audit_unit_id' => $this->auditUnitId],
                            'order' => 'id DESC',
                            'limit' => 1
                        ]);

                        if (!empty($currentAssessment)) {
                            $assessment = reset($currentAssessment);

                            // Update the branch_head_id in assessment with the NEW value from form
                            $updateAssessmentResult = $assessmentModel::update(
                                $assessmentModel->getTableName(),
                                ['branch_head_id' => $newBranchHeadId], // Use the new value from form
                                [
                                    'where' => 'id = :id',
                                    'params' => ['id' => $assessment->id]
                                ]
                            );

                            if ($updateAssessmentResult) {
                                // Optional: Add a success message for the assignment
                                Session::set('success', 'Branch head successfully assigned to current assessment');
                            } else {
                                Session::set('error', 'Failed to assign branch head to assessment');
                            }
                        }
                    }
                }

                //after insert data redirect to audit unit dashboard
                Validation::flashErrorMsg('auditSectionUpdatedSuccess', 'success');
                Redirect::to(SiteUrls::getUrl('auditUnitMaster'));
            }
        });
    }

    public function delete($getRequest)
    {

        $this->auditUnitId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this->data['db_data'] = $this->getDataOr404(['id' => $this->auditUnitId, 'deleted_at' => NULL, 'is_active' => 1]);

        //return if data not found
        if (!is_object($this->data['db_data']))
            return $this->data['db_data'];

        $result = $this->auditUnitModel::delete($this->auditUnitModel->getTableName(), [
            'where' => 'id = :id',
            'params' => ['id' => $this->auditUnitId]
        ]);

        if (!$result)
            return Except::exc_404(Notifications::getNoti('errorDeleting'));

        //after insert data redirect to audit unit dashboard
        Validation::flashErrorMsg('auditSectionDeletedSuccess', 'success');
        Redirect::to(SiteUrls::getUrl('auditUnitMaster'));
    }

    public function status($getRequest)
    {

        $this->auditUnitId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this->data['db_data'] = $this->getDataOr404(['id' => $this->auditUnitId], 2);

        //return if data not found
        if (!is_object($this->data['db_data']))
            return $this->data['db_data'];

        $updateStatus = ($this->data['db_data']->is_active == 1) ? 0 : 1;

        $result = $this->auditUnitModel::update(
            $this->auditUnitModel->getTableName(),
            ['is_active' => $updateStatus],
            [
                'where' => 'id = :id',
                'params' => ['id' => $this->auditUnitId]
            ]
        );

        if (!$result)
            return Except::exc_404(Notifications::getNoti('errorSaving'));

        //after insert data redirect to audit unit dashboard
        Validation::flashErrorMsg((($updateStatus == 1) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to(SiteUrls::getUrl('auditUnitMaster'));
    }

    public function dataTableAjaxFrequency()
    {
        $funcData = generate_datatable_data($this, $this->auditUnitModel, ["audit_unit_code", "name", "frequency"], [
            'where' => 'is_active = 1 AND (frequency != 0 OR frequency != "")'
        ]);

        if (is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0) {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            // $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach ($funcData['dbData'] as $cUnitFrequencyId => $cUnitFrequencyDetails) {
                $frequency = $cUnitFrequencyDetails->frequency . ' Months';

                $cDataArray = [
                    "audit_unit_code" => $cUnitFrequencyDetails->audit_unit_code,
                    "name" => $cUnitFrequencyDetails->name,
                    "frequency" =>  $frequency,
                    "action" => ""
                ];

                $srNo++;

                if ($cUnitFrequencyDetails->is_active == 1) {
                    $cDataArray["action"] .=  generate_link_button('update', ['href' =>  SiteUrls::setUrl($this->me->url) . '/frequency/' . encrypt_ex_data($cUnitFrequencyDetails->id), 'extra' => view_tooltip('Update')]) . " ";
                }

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function frequency($getRequest)
    {

        $this->auditUnitId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this->me->url = SiteUrls::setUrl($this->me->url . '/frequency/');
        $this->me->pageHeading = 'Audit Unit Frequency';

        $this->me->breadcrumb[] = $this->me->id;
        $this->me->menuKey = 'auditUnitMasterFrequency';

        // get data //method call
        $this->data['db_data'] = $this->auditUnitModel->getAllAuditUnit([
            'where' => 'is_active = 1 AND frequency != 0 AND deleted_at IS NULL'
        ]);

        // get db_unit_data //method call
        $this->data['db_unit_data'] = $this->auditUnitModel->getAllAuditUnit([
            'where' => 'id = :id AND is_active = 1 AND frequency != 0 AND deleted_at IS NULL',
            'params' => ['id' => $this->auditUnitId]
        ]);

        if (empty($this->data['db_unit_data']) && $this->auditUnitId != '') {
            Except::exc_404(Notifications::getNoti('somethingWrong'));
            exit;
        }

        // For each audit unit in db_data, check if frequency can be changed (only if status = 1)
        $frequencyChangeAllowed = [];
        $assessmentModel = $this->model('AuditAssesmentModel');

        foreach ($this->data['db_data'] as $unit) {
            // Check if this unit has any assessment with status != 1
            $restrictedAssessments = $assessmentModel->getAllAuditAssesment([
                'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id != 1 AND deleted_at IS NULL',
                'params' => ['audit_unit_id' => $unit->id],
                'limit' => 1
            ]);

            $frequencyChangeAllowed[$unit->id] = empty($restrictedAssessments);
        }
        $this->data['frequency_change_allowed'] = $frequencyChangeAllowed;

        //total number of records without filtering // function call
        $this->data['db_data_count'] = get_db_table_sql_count(
            $this,
            $this->auditUnitModel,
            $this->auditUnitModel->getTableName(),
            [
                'where' => 'is_active = 1 AND (frequency != 0 OR frequency != "") AND deleted_at IS NULL'
            ]
        );

        //re assign
        $this->data['db_data_count'] = $this->data['db_data_count']->total_records;

        if ($this->data['db_data_count'] > 0)
            $this->data['need_datatable'] = true;


        if (!$this->request->has('audit_id') && !empty($this->auditUnitId) && $this->data['db_data_count'] > 0) {
            foreach ($this->data['db_data'] as $cIndex => $cAuditDetails) {
                if ($this->auditUnitId == $cAuditDetails->id) {
                    $this->request->setInputCustom('audit_id', $cAuditDetails->id);
                    $this->request->setInputCustom('frequency', $cAuditDetails->frequency);
                    break;
                }
            }
        }

        //form
        $this->request::method('GET', function () {

            //load view
            return return2View($this, $this->me->viewDir . 'form_frequency', [
                'request' => $this->request,
                'data' => $this->data
            ]);
        });

        //post method after form submit
        $this->request::method("POST", function () {

            Validation::validateData($this->request, [

                'audit_id' => 'required|array_key[audit_id_array, audit_id]',
                'frequency' => 'required|array_key[frequency_array, frequency]',
            ], [
                'audit_id_array'  =>  $this->data['db_audit_unit'],
                'frequency_array'  =>  $GLOBALS['auditFrequencyArray'],
            ]);

            //validation check
            if ($this->request->input('error') > 0) {
                Validation::flashErrorMsg();

                // load view //error data
                return return2View($this, $this->me->viewDir . 'form_frequency', ['request' => $this->request]);
            } else {
                $auditUnitId = $this->request->input('audit_id');
                $newFrequency = $this->request->input('frequency');

                // Get all active assessments for this audit unit to check their status
                $assessmentModel = $this->model('AuditAssesmentModel');

                // Check if any assessment has status != 1
                $restrictedAssessments = $assessmentModel->getAllAuditAssesment([
                    'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id != 1 AND deleted_at IS NULL',
                    'params' => ['audit_unit_id' => $auditUnitId]
                ]);

                $canChangeFrequency = true;
                $restrictedStatus = '';

                if (!empty($restrictedAssessments)) {
                    $canChangeFrequency = false;
                    $assessment = reset($restrictedAssessments);

                    // Get status title from ASSESMENT_TIMELINE_ARRAY
                    $statusTitle = 'Unknown';
                    if (defined('ASSESMENT_TIMELINE_ARRAY') && isset(ASSESMENT_TIMELINE_ARRAY[$assessment->audit_status_id])) {
                        $statusTitle = ASSESMENT_TIMELINE_ARRAY[$assessment->audit_status_id]['title'];
                    }

                    $restrictedStatus = $statusTitle . ' (Status ID: ' . $assessment->audit_status_id . ')';
                }

                if (!$canChangeFrequency) {
                    // Replace {status} placeholder with actual status
                    $errorMessage = str_replace('{status}', $restrictedStatus, Notifications::getNoti('frequencyChangeRestricted'));

                    // Store in session
                    Session::set('danger', $errorMessage);

                    Redirect::to($this->me->url . encrypt_ex_data($auditUnitId));
                    return;
                }

                // Update audit unit frequency
                $updateDataArray = array(
                    'frequency' => $newFrequency,
                    'admin_id' => Session::get('emp_id')
                );

                $result = $this->auditUnitModel::update(
                    $this->auditUnitModel->getTableName(),
                    $updateDataArray,
                    [
                        'where' => 'id = :id',
                        'params' => ['id' => $auditUnitId]
                    ]
                );

                if (!$result)
                    return Except::exc_404(Notifications::getNoti('somethingWrong'));

                // Update only those assessments that have status = 1
                $assessmentsToUpdate = $assessmentModel->getAllAuditAssesment([
                    'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id = 1 AND deleted_at IS NULL',
                    'params' => ['audit_unit_id' => $auditUnitId]
                ]);

                if (!empty($assessmentsToUpdate)) {
                    foreach ($assessmentsToUpdate as $assessment) {
                        // Recalculate assessment periods based on new frequency
                        $newPeriods = $this->calculateAssessmentPeriods($assessment->assesment_period_from, $newFrequency);

                        $updateAssessmentData = [
                            'frequency' => $newFrequency,
                            'assesment_period_from' => $newPeriods['from'],
                            'assesment_period_to' => $newPeriods['to'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ];

                        $assessmentModel::update(
                            $assessmentModel->getTableName(),
                            $updateAssessmentData,
                            [
                                'where' => 'id = :id',
                                'params' => ['id' => $assessment->id]
                            ]
                        );
                    }
                }

                // Success message using existing notification key
                Validation::flashErrorMsg('auditFrequencySavedSuccess', 'success');
                Redirect::to($this->me->url . encrypt_ex_data($auditUnitId));
            }
        });
    }
    /**
     * Calculate new assessment periods based on frequency
     * 
     * @param string $currentFromDate Current assessment period from date
     * @param int $newFrequency New frequency in months
     * @return array ['from' => new_from_date, 'to' => new_to_date]
     */
    private function calculateAssessmentPeriods($currentFromDate, $newFrequency)
    {
        // If no current from date, calculate from current date
        if (empty($currentFromDate)) {
            $currentFromDate = date('Y-m-d');
        }

        // Parse the current from date
        $fromDate = new \DateTime($currentFromDate);

        // Calculate to date by adding frequency months
        $toDate = clone $fromDate;
        $toDate->modify('+' . $newFrequency . ' months');
        $toDate->modify('-1 day'); // Last day of the previous month

        // Format dates
        $newFromDate = $fromDate->format('Y-m-d');
        $newToDate = $toDate->format('Y-m-d');

        return [
            'from' => $newFromDate,
            'to' => $newToDate
        ];
    }


    private function getDataOr404($filter, $optional = null)
    {

        $filter = [
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => ['id' => $this->auditUnitId]
        ];

        if ($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this->data['db_data'] = $this->auditUnitModel->getSingleAuditUnit($filter);

        if (empty($this->auditUnitId) || empty($this->data['db_data']))
            return Except::exc_404(Notifications::getNoti('errorFinding'));

        return $this->data['db_data'];
    }

    /**
     * Last March Position - Display and update March position data
     */
    public function lastMarchPosition($getRequest = null)
    {
        // Set page heading and URL
        $this->me->url = SiteUrls::setUrl($this->me->url . '/last-march-position/');
        $this->me->pageHeading = 'Last March Position';
        $this->me->menuKey = 'auditUnitMasterLastMarch';

        // Set breadcrumb
        $this->me->breadcrumb[] = $this->me->id;

        // Get the target year for last March
        $currentYear = date('Y');
        $currentMonth = date('m');
        $targetYear = ($currentMonth < 3) ? ($currentYear - 1) : $currentYear;

        // Get the year_id from year_master table for the target year
        $yearModel = $this->model('YearModel');
        $yearData = $yearModel->getSingleYear([
            'where' => 'year = :year AND deleted_at IS NULL',
            'params' => ['year' => $targetYear]
        ]);

        $yearId = $yearData->id ?? null;

        // If no year found, get the latest year
        if (!$yearId) {
            $allYears = $yearModel->getAllYears([
                'where' => 'deleted_at IS NULL',
                'order' => 'year DESC',
                'limit' => 1
            ]);
            if (!empty($allYears)) {
                $yearId = $allYears[0]->id;
            }
        }

        // Store year_id in data for view
        $this->data['year_id'] = $yearId;

        // Handle POST request
        $this->request::method('POST', function () use ($yearId) {
            // Check if CSV upload button was clicked
            if (isset($_POST['upload_csv'])) {
                return $this->handleCsvUpload($yearId);
            }

            // Regular View Position button click
            $selectedAuditId = $this->request->input('audit_id');

            if (!empty($selectedAuditId)) {
                // Get the audit unit details
                $auditUnit = $this->auditUnitModel->getSingleAuditUnit([
                    'where' => 'id = :id AND deleted_at IS NULL',
                    'params' => ['id' => $selectedAuditId]
                ]);

                $this->data['selected_audit_unit_name'] = $auditUnit->name ?? 'Selected Unit';

                // Fetch March position data from exe_summary table
                $exeSummaryModel = $this->model('ExeSummaryModel');

                if ($yearId) {
                    $this->data['march_position_data'] = $exeSummaryModel->getAllMarchPosition([
                        'where' => 'audit_unit_id = :audit_unit_id AND year_id = :year_id AND deleted_at IS NULL',
                        'params' => [
                            'audit_unit_id' => $selectedAuditId,
                            'year_id' => $yearId
                        ],
                        'order' => 'gl_type_id ASC'
                    ]);
                } else {
                    $this->data['march_position_data'] = $exeSummaryModel->getAllMarchPosition([
                        'where' => 'audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
                        'params' => ['audit_unit_id' => $selectedAuditId],
                        'order' => 'gl_type_id ASC'
                    ]);
                }
            }

            // Get all audit units for dropdown
            $this->data['db_audit_unit'] = generate_array_for_select(
                $this->auditUnitModel->getAllAuditUnit(['where' => 'deleted_at IS NULL AND is_active = 1']),
                'id',
                'name'
            );

            // Store the selected ID in request to show in dropdown
            $this->request->setInputCustom('audit_id', $selectedAuditId);

            // Load view with data
            return return2View($this, $this->me->viewDir . 'form_last_march_position', [
                'request' => $this->request,
                'data' => $this->data
            ]);
        });

        // Handle GET request (initial page load)
        $this->request::method('GET', function () {
            // Get all audit units for dropdown
            $this->data['db_audit_unit'] = generate_array_for_select(
                $this->auditUnitModel->getAllAuditUnit(['where' => 'deleted_at IS NULL AND is_active = 1']),
                'id',
                'name'
            );

            // Initialize empty data for GET request
            $this->data['march_position_data'] = [];
            $this->data['selected_audit_unit_name'] = '';

            return return2View($this, $this->me->viewDir . 'form_last_march_position', [
                'request' => $this->request,
                'data' => $this->data
            ]);
        });
    }


    /**
     * Handle CSV file upload and update March positions
     */
    private function handleCsvUpload($yearId)
    {
        // Check if file was uploaded
        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Validation::flashErrorMsg('Please upload a valid CSV file.', 'error');
            return $this->redirectBack();
        }

        $file = $_FILES['csv_file'];
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Validate file type
        if (strtolower($fileType) !== 'csv') {
            Validation::flashErrorMsg('Only CSV files are allowed.', 'error');
            return $this->redirectBack();
        }

        // Open and read CSV file
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Validation::flashErrorMsg('Unable to read the CSV file.', 'error');
            return $this->redirectBack();
        }

        $updatedCount = 0;
        $notFoundCount = 0;
        $errors = [];
        $rowNumber = 0;

        // Get ExeSummaryModel instance
        $exeSummaryModel = $this->model('ExeSummaryModel');

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (count($row) < 3) {
                continue;
            }

            // Skip header row if exists
            if ($rowNumber == 1) {
                $firstCell = strtolower(trim($row[0] ?? ''));
                if (in_array($firstCell, ['audit_unit_id', 'audit unit id', 'audit unit'])) {
                    continue;
                }
            }

            $auditUnitId = trim($row[0]);
            $glTypeId = trim($row[1]);
            $marchPosition = trim($row[2]);

            // Skip if any value is empty (but allow 0)
            if ($auditUnitId === '' || $glTypeId === '' || $marchPosition === '') {
                continue;
            }

            // Check if record exists using model
            $existing = $exeSummaryModel->getSingleExeSummary([
                'where' => 'audit_unit_id = :audit_unit_id AND gl_type_id = :gl_type_id AND year_id = :year_id AND deleted_at IS NULL',
                'params' => [
                    'audit_unit_id' => $auditUnitId,
                    'gl_type_id' => $glTypeId,
                    'year_id' => $yearId
                ]
            ]);

            if ($existing) {
                // Update existing record using model
                $result = $exeSummaryModel::update(
                    $exeSummaryModel->getTableName(),
                    [
                        'march_position' => $marchPosition,
                        'updated_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'where' => 'id = :id',
                        'params' => ['id' => $existing->id]
                    ]
                );

                if ($result) {
                    $updatedCount++;
                } else {
                    $errors[] = "Row {$rowNumber}: Update failed";
                }
            } else {
                $notFoundCount++;
            }
        }
        fclose($handle);

        // Display results using Validation::flashErrorMsg
        if ($updatedCount > 0) {
            $successMessage = "March position Updated Successfully!<br>";
            $successMessage .= "Updated: {$updatedCount} records<br>";
            if ($notFoundCount > 0) {
                $successMessage .= "Skipped: {$notFoundCount} records (no matching records found)";
            }
            Validation::flashErrorMsg($successMessage, 'success');
        } elseif ($notFoundCount > 0) {
            Validation::flashErrorMsg("No matching records found to update. Skipped: {$notFoundCount} records", 'warning');
        } elseif ($rowNumber > 1) {
            Validation::flashErrorMsg("No records were processed. Please check your CSV file format.", 'info');
        }

        if (!empty($errors)) {
            $errorMessage = 'CSV Upload Completed with Errors:<br>' . implode('<br>', array_slice($errors, 0, 20));
            Validation::flashErrorMsg($errorMessage, 'error');
        }

        return $this->redirectBack();
    }
    /**
     * Redirect back to the same page
     */
    private function redirectBack()
    {
        $currentUrl = $this->me->url;
        header("Location: " . $currentUrl);
        exit;
    }

    /**
     * DataTable AJAX for Last March Position
     */
    public function dataTableAjaxLastMarchPosition()
    {
        $funcData = generate_datatable_data($this, $this->auditUnitModel, ["audit_unit_code", "name"], [
            'where' => 'deleted_at IS NULL AND is_active = 1'
        ]);

        if (is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0) {
            // Get last March date
            $currentYear = date('Y');
            $currentMonth = date('m');
            $targetYear = ($currentMonth < 3) ? ($currentYear - 1) : $currentYear;
            $lastMarchDate = $targetYear . '-03-31';

            $assessmentModel = $this->model('AuditAssesmentModel');

            foreach ($funcData['dbData'] as $unit) {
                // Get assessment for last March
                $lastMarchAssessment = $assessmentModel->getAllAuditAssesment([
                    'where' => 'audit_unit_id = :audit_unit_id AND 
                           assesment_period_from <= :target_date AND 
                           (assesment_period_to >= :target_date OR assesment_period_to IS NULL) 
                           AND deleted_at IS NULL',
                    'params' => [
                        'audit_unit_id' => $unit->id,
                        'target_date' => $lastMarchDate
                    ],
                    'order' => 'id DESC',
                    'limit' => 1
                ]);

                $assessment = !empty($lastMarchAssessment) ? reset($lastMarchAssessment) : null;

                $cDataArray = [
                    "audit_unit_code" => $unit->audit_unit_code,
                    "name" => $unit->name,
                    "position_as_on_march" => $this->formatPositionData($assessment),
                    "assessment_period" => $assessment ? $assessment->assesment_period_from . ' to ' . $assessment->assesment_period_to : 'N/A',
                    "action" => $assessment ? generate_link_button('view', [
                        'href' => SiteUrls::getUrl('auditAssessment') . '/view/' . encrypt_ex_data($assessment->id),
                        'extra' => view_tooltip('View Assessment')
                    ]) : ''
                ];

                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }
        }

        $dataResArray = unset_datatable_vars($funcData);
        echo json_encode($dataResArray);
    }

    /**
     * Format position data for display
     */
    private function formatPositionData($assessment)
    {
        if (!$assessment) {
            return '<span class="text-muted">No assessment found</span>';
        }

        // Get assessment score or status
        $score = $assessment->total_score ?? 0;
        $status = $assessment->audit_status_id ?? 0;

        $statusText = '';
        if (defined('ASSESMENT_TIMELINE_ARRAY') && isset(ASSESMENT_TIMELINE_ARRAY[$status])) {
            $statusText = ASSESMENT_TIMELINE_ARRAY[$status]['title'];
        }

        return '<div>
        <span class="font-medium">Score: ' . $score . '%</span><br>
        <span class="text-secondary">Status: ' . $statusText . '</span>
    </div>';
    }
    /**
 * Download sample CSV file for March position upload
 */
public function downloadSampleCsv() {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sample_march_position.csv"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, ['audit_unit_id', 'gl_type_id', 'march_position']);
    
    // Add sample data rows
    $sampleData = [
        [1, 1, 0.00],
        [1, 2, 0.00],
        [1, 3, 0.00],
        [1, 4, 0.00],
        [1, 5, 0.00],
        [1, 6, 0.00],
        [1, 7, 0.00],
        [1, 9, 0.00],
        [1, 10, 0.00],
        [1, 11, 0.00],
        [1, 12, 0.00],
        [1, 13, 0.00]
    ];
    
    foreach($sampleData as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}
}
