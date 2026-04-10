<?php

namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Except;
use Core\Notifications;
use Core\DBCommonFunc;

// need broader area helper 25.08.2024
require_once APP_CORE . DS . 'HelperFunctionBroaderAreaReport.php';

class Dashboard extends Controller
{

    public $me = null, $empType, $data, $request, $year, $riskCategoryWeight, $riskCategoryData;
    public $assesmentModel, $auditUnitModel;

    public function __construct($me)
    {

        $auditUnitModel = $this->model('AuditUnitModel');

        $this->request = new Request();

        $this->me = $me;

        $this->empType = Session::get('emp_type');

        $this->data['need_dashboard'] = true;

        // find current year model
    }

    private function getEmployeeDetails()
    {
        if (!Session::has('emp_id') || empty(Session::get('emp_id')))
            return null;

        // find current employee model
        $model = $this->model('EmployeeModel');

        // find current emp details
        $employeeDetails = $model->getSingleEmploye([
            'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
            'params' => ['id' => Session::get('emp_id')]
        ]);

        //return data
        return $employeeDetails;
    }

    private function getAuditUnitWiseAssesmentData($employeeDetails, $resData)
    {
        if (!is_array($resData) || (is_array($resData) && !sizeof($resData) > 0))
            return null;

        if (is_array($resData) || (is_array($resData) && sizeof($resData) > 0)) {
            //find assesment data // find current year model
            $model = $this->model('YearModel');

            $this->data['db_year_data'] = $model->getAllYears([
                'where' => 'deleted_at IS NULL'
            ]);

            if (is_array($this->data['db_year_data']) && sizeof($this->data['db_year_data']) > 0) {
                //has year data
                $this->data['db_year_data'] = generate_data_assoc_array($this->data['db_year_data'], 'id');

                //find assesment data // find current audit unit model
                $model = $this->model('AuditAssesmentModel');

                $this->data['db_assesment_data'] = $model->getAllAuditAssesment([
                    'where' => 'year_id IN (' . implode(',', array_keys($this->data['db_year_data'])) . ') AND audit_unit_id IN (' . implode(',', array_keys($resData)) . ') AND deleted_at IS NULL'
                ]);

                if (is_array($this->data['db_assesment_data']) && sizeof($this->data['db_assesment_data']) > 0) {
                    $db_assesment_data = $this->data['db_assesment_data'];
                    $this->data['db_assesment_data'] = [];

                    foreach ($db_assesment_data as $cAuditAssesment) {
                        // check key exits 
                        if (array_key_exists($cAuditAssesment->audit_unit_id, $resData)) {
                            // year_data not exists
                            if (!isset($resData[$cAuditAssesment->audit_unit_id]->year_data))
                                $resData[$cAuditAssesment->audit_unit_id]->year_data = [];

                            // year id not exists
                            if (!array_key_exists($cAuditAssesment->year_id, $resData[$cAuditAssesment->audit_unit_id]->year_data))
                                $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id] = json_decode('{}');

                            // if( !isset($resData[ $cAuditAssesment -> audit_unit_id ] -> year_data[ $cAuditAssesment -> year_id ] -> id) && 
                            //     is_array($this -> data['db_year_data']) && 
                            //     array_key_exists($cAuditAssesment -> year_id, $this -> data['db_year_data']))
                            // $resData[ $cAuditAssesment -> audit_unit_id ] -> year_data[ $cAuditAssesment -> year_id ] = $this -> data['db_year_data'][ $cAuditAssesment -> year_id ];

                            // assesment not exists
                            if (!isset($resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->assesment_data)) {
                                $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->assesment_cnt = 12;
                                $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->pending_assesment = false;

                                $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->assesment_data = [];
                            }

                            // push assesment details
                            // echo $cAuditAssesment -> audit_unit_id;
                            $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->assesment_data[$cAuditAssesment->id] = $cAuditAssesment;

                            // less assesment
                            $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->assesment_cnt -= $cAuditAssesment->frequency;

                            // if pending assesment
                            if (!($cAuditAssesment->audit_status_id > 3))
                                $resData[$cAuditAssesment->audit_unit_id]->year_data[$cAuditAssesment->year_id]->pending_assesment  = true;
                        }
                    }
                }
            }
        }

        return $resData;
    }

    private function getAssesmentData($singleAssesment = 0, $auditUnitId = 0)
    {
        $resData = null;

        // getAllAuditAssesment
        $employeeDetails = $this->getEmployeeDetails();

        $model = $this->model('AuditUnitModel');

        if (in_array($this->empType, [2, 4, 5, 16]) && is_object($employeeDetails) && !empty($employeeDetails->audit_unit_authority)) {
            //has audit units // find current audit unit model
            // $this -> getAuditUnitWiseAssesmentData($employeeDetails -> audit_unit_authority);

            if ($singleAssesment) {
                $resData = $model->getSingleAuditUnit([
                    'where' => 'id = ' . $auditUnitId . ' AND is_active = 1 AND deleted_at IS NULL'
                ]);

                $resData = [$resData];
            } else
                $resData = $model->getAllAuditUnit([
                    'where' => 'id IN (' . $employeeDetails->audit_unit_authority . ') AND is_active = 1 AND deleted_at IS NULL'
                ]);

            $resData = generate_data_assoc_array($resData, 'id');
        } elseif ($this->empType == 3) //for compliance
        {
            $resData = $model->getAllAuditUnit([
                'where' => 'is_active = 1 AND deleted_at IS NULL'
            ]);

            $auditUnitAuthority = [];

            if (is_array($resData) && sizeof($resData) > 0) {
                foreach ($resData as $cAuditUnitId => $cAuditUnitDetails) {
                    if (
                        $cAuditUnitDetails->branch_head_id == Session::get('emp_id') ||
                        $cAuditUnitDetails->branch_subhead_id == Session::get('emp_id')
                    ) {
                        $auditUnitAuthority[$cAuditUnitDetails->id] = $cAuditUnitDetails;
                        break;
                    }

                    /*elseif($cAuditUnitDetails -> multi_compliance_ids != '')
                    {
                        $multiComplianceIds = explode(',', $cAuditUnitDetails -> multi_compliance_ids);

                        if(is_array($multiComplianceIds) && !empty($multiComplianceIds) && in_array(Session::get('emp_id'), $multiComplianceIds))
                            $auditUnitAuthority[ $cAuditUnitDetails -> id ] = $cAuditUnitDetails;


                        // foreach($multiComplianceIds as $cEmpIds)
                        // {
                        //     if( array_key_exists($cEmpIds, $resData) )
                        //         $auditUnitAuthority[ $cAuditUnitDetails -> id ] = $cAuditUnitDetails;
                        // }
                    }*/
                }
            }

            // re assign
            $resData = $auditUnitAuthority;
        }

        //error for all user types if audit units not found
        if (!is_array($resData) || (is_array($resData) && !sizeof($resData) > 0)) {
            Except::exc_404(Notifications::getNoti('auditAuthorityNotFound'));
            exit;
        }

        // method call
        $resData = $this->getAuditUnitWiseAssesmentData($employeeDetails, $resData);

        //add container
        // $this -> data['data_container'] = true;

        return $resData;
    }

    private function riskArray($auditId)
    {
        $todayDate = date($GLOBALS['dateSupportArray'][1]);

        // function call
        BROADER_AREA_STORE_SUMMARY_HELPER($this, [], 0, ['date' => $todayDate]);

        // function call
        $getFy = getFYOnDate(date($GLOBALS['dateSupportArray'][1]));

        $model = $this->model('ReportScoringMasterModel');
        $riskData = $model->getAllReportScore([
            'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id > "' . ASSESMENT_TIMELINE_ARRAY[3]['status_id'] . '" AND year = :year AND deleted_at IS NULL',
            'params' => ['year' => $getFy, 'audit_unit_id' => $auditId]
        ]);

        return $riskData;
    }

    public function index()
    {

        // year model
        $yearModel = $this->model('YearModel');

        $this->year = $yearModel->getAllYears(['where' => 'deleted_at IS NULL ORDER BY `id` DESC LIMIT 1']);

        $this->data['lastYearId'] = generate_array_for_select($this->year, 'id', 'year');

        // UNSET AUDIT ID // 
        Session::delete('audit_id');

        //check user type
        $this->data['db_data'] = null;

        switch ($this->empType) {
            case '1':
            case '9': 
            case '11': { //super admin
                    // Admin
                    $model = $this->model('EmployeeModel');
                    $this->data['data_container'] = true;
                    $this->data['need_dashboard'] = true;

                    // Get total counts
                    $this->data['db_data']['total_employees'] = get_all_data_query_builder(1, $model, 'employee_master', [
                        'where' => 'deleted_at IS NULL',
                        'params' => []
                    ], 'sql', "SELECT COUNT(*) total_employees FROM employee_master")->total_employees;

                    // Audit Unit Model
                    $this->data['db_data']['total_branch'] = get_all_data_query_builder(1, $model, 'audit_unit_master', [
                        'where' => 'section_type_id = 1 AND deleted_at IS NULL',
                        'params' => []
                    ], 'sql', "SELECT COUNT(*) total_branch FROM audit_unit_master")->total_branch;

                    $this->data['db_data']['total_head_office'] = get_all_data_query_builder(1, $model, 'audit_unit_master', [
                        'where' => 'section_type_id != 1 AND deleted_at IS NULL',
                        'params' => []
                    ], 'sql', "SELECT COUNT(*) total_head_office FROM audit_unit_master")->total_head_office;

                    $this->data['db_data']['total_schemes'] = get_all_data_query_builder(1, $model, 'scheme_master', [
                        'where' => 'deleted_at IS NULL',
                        'params' => []
                    ], 'sql', "SELECT COUNT(*) total_schemes FROM scheme_master")->total_schemes;

                    // Assuming you have a function to run SQL queries and return results
                    function get_audit_summary($model)
                    {
                        $query = "
                        SELECT 
                            COUNT(CASE WHEN audit_status_id = 1 AND deleted_at IS NULL THEN 1 END) AS total_pending_audit,
                            COUNT(CASE WHEN audit_status_id > 3 AND deleted_at IS NULL THEN 1 END) AS total_completed_audit,
                            COUNT(CASE WHEN audit_status_id > 6 AND deleted_at IS NULL THEN 1 END) AS total_completed_compliance,
                            COUNT(CASE WHEN is_limit_blocked = 1 AND deleted_at IS NULL THEN 1 END) AS total_blocked_assesment,
                            COUNT(CASE WHEN year_id != 0 AND audit_unit_id != 0 AND audit_status_id IN (1, 3) AND audit_due_date < '" . date($GLOBALS['dateSupportArray'][1]) . "' AND deleted_at IS NULL THEN 1 END) AS total_expired_audit,
                            COUNT(CASE WHEN year_id != 0 AND audit_unit_id != 0 AND audit_status_id IN (4, 6) AND compliance_due_date < '" . date($GLOBALS['dateSupportArray'][1]) . "' AND deleted_at IS NULL THEN 1 END) AS total_expired_compliance
                        FROM audit_assesment_master
                    ";

                        // Replace with actual query execution
                        return get_all_data_query_builder(1, $model, 'audit_assesment_master', [], 'sql', $query);
                    }

                    // Execute the optimized query
                    $audit_summary = get_audit_summary($model);
                    $this->data['db_data']['total_audit_count'] = 0;

                    // Extract results
                    $this->data['db_data']['total_pending_audit'] = $audit_summary->total_pending_audit;
                    $this->data['db_data']['total_completed_audit'] = $audit_summary->total_completed_audit;
                    $this->data['db_data']['total_completed_compliance'] = $audit_summary->total_completed_compliance;
                    $this->data['db_data']['total_blocked_assesment'] = $audit_summary->total_blocked_assesment;
                    $this->data['db_data']['total_expired_audit'] = $audit_summary->total_expired_audit;
                    $this->data['db_data']['total_expired_compliance'] = $audit_summary->total_expired_compliance;

                    // Process 'not yet started' audits
                    $model = $this->model('AuditUnitModel');

                    $query_branch = " WHERE section_type_id = 1 AND frequency != 0 AND is_active = 1 AND deleted_at IS NULL";
                    $this->data['db_data']['audit_unit_branch'] = audit_unit_details_for_not_started($query_branch, $model);
                    $this->data['db_data']['total_not_yet_startd_audit_branch'] = audit_assesment_not_started($this->data['db_data']['audit_unit_branch'], $this->model('AuditAssesmentModel'));

                    $query_ho = " WHERE section_type_id != 1 AND frequency != 0 AND is_active = 1 AND deleted_at IS NULL";
                    $this->data['db_data']['audit_unit_ho'] = audit_unit_details_for_not_started($query_ho, $model);
                    $this->data['db_data']['total_not_yet_startd_audit_ho'] = audit_assesment_not_started($this->data['db_data']['audit_unit_ho'], $this->model('AuditAssesmentModel'));

                    $this->data['db_data']['total_audit_count'] = $this->data['db_data']['total_pending_audit'] +
                        $this->data['db_data']['total_completed_audit'] +
                        $this->data['db_data']['total_completed_compliance'] +
                        $this->data['db_data']['total_blocked_assesment'] +
                        $this->data['db_data']['total_expired_audit'] +
                        $this->data['db_data']['total_expired_compliance'] +
                        sizeof($this->data['db_data']['total_not_yet_startd_audit_branch']) +
                        sizeof($this->data['db_data']['total_not_yet_startd_audit_ho']);

                    $this->data['db_data']['data_array_chart'] = [
                        [
                            'y' => $this->data['db_data']['total_pending_audit'],
                            'name' => 'Total Audit Pending'
                        ],
                        [
                            'y' => $this->data['db_data']['total_completed_audit'],
                            'name' => 'Total Audit Completed'
                        ],
                        [
                            'y' => $this->data['db_data']['total_completed_compliance'],
                            'name' => 'Total Compliance Completed'
                        ],
                        [
                            'y' => $this->data['db_data']['total_blocked_assesment'],
                            'name' => 'Total Blocked Audit',
                        ],
                        [
                            'y' => $this->data['db_data']['total_expired_audit'],
                            'name' => 'Total Expired Audit'
                        ],
                        [
                            'y' => $this->data['db_data']['total_expired_compliance'],
                            'name' => 'Total Expired Compliance',
                        ],
                        [
                            'y' => sizeof($this->data['db_data']['total_not_yet_startd_audit_branch']),
                            'name' => 'Total Not Started Branches'
                        ],
                        [
                            'y' => sizeof($this->data['db_data']['total_not_yet_startd_audit_ho']),
                            'name' => 'Total Not Started Head Office'
                        ],

                    ];

                    // load view
                    return return2View($this, $this->me->viewDir . 'dashboard', ['request' => $this->request]);

                    break;
                }

            case '2':
            case '4': 
            case '16':    
                {

                    $this->data['data_container'] = true;
                    $this->data['need_dashboard'] = true;

                    $auditId = decrypt_ex_data($this->request->input('auditUnit'));

                    if ($auditId == '')
                        return Except::exc_404(Notifications::getNoti('errorFinding'), 1);


                    $this->data['auditId'] = $auditId;

                    $this->data['empType'] = $this->empType;

                    ///---------------------------

                    //get all year 
                    $this->year = $yearModel->getAllYears(['where' => 'deleted_at IS NULL']);

                    $this->data['fin_year'] = generate_array_for_select($this->year, 'year', 'id');

                    // risk category weight model
                    $riskcategoryModel = $this->model('RiskCategoryWeightModel');

                    //get all risk category weight
                    $this->riskCategoryWeight = $riskcategoryModel->getAllRiskCategoryWeight(['where' => 'deleted_at IS NULL']);

                    $this->data['riskCategoryWeightData'] = generate_data_assoc_array($this->riskCategoryWeight, 'id');

                    //get risk data
                    $this->data['riskData'] = $this->riskArray($auditId);

                    ///------------------------------

                    //audit unit data
                    $auditUnitModel = $this->model('AuditUnitModel');

                    $this->data['auditUnitData'] = $auditUnitModel->getAllAuditUnit(
                        [
                            'where' => 'id = :id AND deleted_at IS NULL',
                            'params' => ['id' => $auditId]
                        ]
                    );

                    if (empty($this->data['auditUnitData']))
                        return Except::exc_404(Notifications::getNoti('errorFinding'), 1);

                    //validation
                    $this->data['db_data'] = $this->getAssesmentData(1, $auditId); // method call
                    $this->data['emp_data'] = $this->getEmployeeDetails();

                    $audit_unit_authority = $this->data['emp_data']->audit_unit_authority;
                    $this->data['empCnt'] = 0;

                    if (!empty($audit_unit_authority)) {
                        $query = "
                    SELECT COUNT(id) as total 
                    FROM audit_unit_master 
                ";
                    }

                    $result = get_all_data_query_builder(1, $auditUnitModel, 'audit_unit_master', [
                        'where' => 'id IN (' . $audit_unit_authority . ') AND deleted_at IS NULL',
                        'params' => []
                    ], 'sql', $query);

                    $this->data['empCnt'] = $result->total ?? 0;

                    //assesment not started
                    $this->data['auditNotStartedData'] = audit_assesment_not_started_common_code($this->data['auditUnitData'][0], $this->model('AuditAssesmentModel'));

                    // load view
                    return return2View($this, $this->me->viewDir . 'dashboard', ['request' => $this->request]);

                    break;
                }

            case '3': {

                    $this->data['data_container'] = true;
                    $this->data['need_dashboard'] = true;

                    $this->data['empType'] = $this->empType;

                    $auditUnitModel = $this->model('AuditUnitModel');

                    $auditId = decrypt_ex_data($this->request->input('auditUnit'));

                    if (isset($auditId) && $auditId != '') {
                        if ($auditId == '')
                            return Except::exc_404(Notifications::getNoti('errorFinding'), 1);

                        $this->data['db_data'] = $this->getAssesmentData(1, $auditId);

                        $this->data['auditId'] = $auditId;
                    } else {
                        $this->data['db_data'] = $this->getAssesmentData(); //method call 

                        $auditId = '';

                        foreach ($this->data['db_data'] as $cDataId => $cDataDetails) {
                            $auditId = $cDataDetails->id;
                        }

                        $this->data['auditId'] = $auditId;
                    }

                    $this->data['emp_data'] = $this->getEmployeeDetails();
                    $this->data['empCnt'] = 0;

                    // $audit_unit_authority = $this->data['emp_data']->audit_unit_authority;  

                    // if(!empty($audit_unit_authority)) {
                    //     $query = "
                    //     SELECT COUNT(id) as total 
                    //     FROM audit_unit_master 
                    // ";}

                    // $result = get_all_data_query_builder(1, $auditUnitModel, 'audit_unit_master', [
                    //     'where' => 'id IN (' . $audit_unit_authority . ') AND deleted_at IS NULL',
                    //     'params' => []
                    // ], 'sql', $query);

                    $query = "SELECT COUNT(id) as total 
                    FROM audit_unit_master";

                    $result = get_all_data_query_builder(1, $auditUnitModel, 'audit_unit_master', [
                        'where' => 'id IN (' . $auditId . ') AND deleted_at IS NULL',
                        'params' => []
                    ], 'sql', $query);

                    $this->data['empCnt'] = $result->total ?? 0;

                    ///---------------------------
                    //get all year 
                    $this->year = $yearModel->getAllYears(['where' => 'deleted_at IS NULL']);

                    $this->data['fin_year'] = generate_array_for_select($this->year, 'year', 'id');

                    // risk category weight model
                    $riskcategoryModel = $this->model('RiskCategoryWeightModel');

                    //get all risk category weight
                    $this->riskCategoryWeight = $riskcategoryModel->getAllRiskCategoryWeight(['where' => 'deleted_at IS NULL']);

                    $this->data['riskCategoryWeightData'] = generate_data_assoc_array($this->riskCategoryWeight, 'id');

                    //get risk data
                    $this->data['riskData'] = $this->riskArray($auditId);

                    ///------------------------------

                    //compliance

                    $this->data['auditUnitData'] = $auditUnitModel->getSingleAuditUnit(
                        [
                            'where' => 'id = :id AND deleted_at IS NULL',
                            'params' => ['id' => $auditId]
                        ]
                    );

                    //assesment not started
                    $this->data['auditNotStartedData'] = audit_assesment_not_started_common_code($this->data['auditUnitData'], $this->model('AuditAssesmentModel'));

                    // load view
                    return return2View($this, $this->me->viewDir . 'dashboard', ['request' => $this->request]);
                    break;
                }

            case '5': {
                    $this->data['data_container'] = true;
                    $this->data['need_dashboard'] = true;

                    $this->data['empType'] = $this->empType;

                    //get all year 
                    $this->year = $yearModel->getAllYears(['where' => 'deleted_at IS NULL']);

                    $this->data['fin_year'] = generate_array_for_select($this->year, 'year', 'id');

                    //review
                    $this->data['db_data'] = $this->getAssesmentData(); //method call

                    //audit unit data
                    $auditUnitModel = $this->model('AuditUnitModel');

                    $this->data['auditUnitData'] = $auditUnitModel->getAllAuditUnit(
                        [
                            'where' => 'deleted_at IS NULL',
                        ]
                    );

                    $this->data['auditUnitData'] = generate_array_for_select($this->data['auditUnitData'], 'id', 'name');

                    $this->data['riskData'] = [];

                    foreach ($this->data['db_data'] as $auditId => $auditData) {
                        //get risk data
                        $this->data['riskData'][$auditId] = $this->riskArray($auditId);
                    }

                    // load view
                    return return2View($this, $this->me->viewDir . 'dashboard', ['request' => $this->request]);
                    break;
                }

                // compliance pro 13.09.2024
            case '6':
            case '7': {

                    if (check_compliance_pro_strict()) {
                        // redirect to other view
                        Redirect::to(SiteUrls::getUrl('complianceProDashboard'));
                    } else {
                        Except::exc_access_restrict();
                        exit;
                    }
                }

            default: {

                    //no data
                    Except::exc_access_restrict();
                    exit;
                }
        }
    }

    public function selectAuditUnit()
    {
        $this->data['data_container'] = true;
        $this->data['need_dashboard'] = true;

        $this->me->id .= '1';
        $this->me->pageHeading = 'Select Audit Unit';
        $this->me->pageTitle = 'Select Audit Unit';

        $this->data['db_data'] = $this->getAssesmentData();

        if ($this->empType == 2 || $this->empType == 4 || $this->empType == 16)
            //load view
            return return2View($this, $this->me->viewDir . 'select-audit-unit', ['request' => $this->request]);
        elseif ($this->empType == 3 && is_array($this->data['db_data']) && sizeof($this->data['db_data']) > 1)
            //load view
            Redirect::to(SiteUrls::getUrl('dashboard'));
        elseif ($this->empType == 3 && is_array($this->data['db_data']) && sizeof($this->data['db_data']) == 1) {
            Redirect::to(SiteUrls::getUrl('dashboard'));
            //load view
            return return2View($this, $this->me->viewDir . 'dashboard', ['request' => $this->request]);
        }
    }

    public function chartDataAjx()
    {
        // Risk category data
        $riskCategoryModel = $this->model('RiskCategoryModel');

        $this->riskCategoryData = $riskCategoryModel->getAllRiskCategory(
            ['where' => 'deleted_at IS NULL']
        );

        $this->data['riskCategoryData'] = generate_array_for_select($this->riskCategoryData, 'id', 'risk_category');

        $assesId = $_POST['asses_id'];
        $auditId = $_POST['audit_id'];

        $res = ['err' => true, 'msg' => Notifications::getNoti('somethingWrong'), 'data' => ''];

        if (!empty($_POST['asses_id'])) {
            $riskTypeWiseScore = [];
            $riskSortedData = [];
            $riskAllData = [];
            $riskTypeWiseScoreArray = [];
            $totalHighRisk = 0;
            $totalMediumRisk = 0;
            $totalLowRisk = 0;

            $model = $this->model('ReportScoringMasterModel');

            if ($this->request->input('asses_id') == 'all')
                $riskData = $model->getAllReportScore([
                    'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id > "' . ASSESMENT_TIMELINE_ARRAY[3]['status_id'] . '" AND deleted_at IS NULL',
                    'params' => ['audit_unit_id' => $auditId]
                ]);
            else
                $riskData = $model->getAllReportScore([
                    'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id > "' . ASSESMENT_TIMELINE_ARRAY[3]['status_id'] . '" AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => ['assesment_id' => $assesId, 'audit_unit_id' => $auditId]
                ]);

            if (!empty($riskData)) {
                foreach ($riskData as $cAssesId => $cAssesDetails) {
                    $riskSortedData[] = [
                        'assesment_id' => $cAssesDetails->assesment_id,
                        'assesment_period_from' => $cAssesDetails->assesment_period_from,
                        'assesment_period_to' => $cAssesDetails->assesment_period_to,
                        'weighted_score' => $cAssesDetails->weighted_score,
                        'risk_data' => (array)json_decode($cAssesDetails->risk_data),
                    ];
                }

                foreach ($riskSortedData as $cRiskId => $cRiskDetails) {
                    $countX = 0;
                    $assesmentTotalQuestions = 0;

                    // Calculate Total Questions first for this assessment
                    foreach ($cRiskDetails['risk_data'] as $cDataDetails) {
                        if ($cDataDetails->avg_sc > 0) {
                            $assesmentTotalQuestions += $cDataDetails->{1} + $cDataDetails->{2} + $cDataDetails->{3};
                        }
                    }

                    // Calculating Scores
                    foreach ($cRiskDetails['risk_data'] as $cDataId => $cDataDetails) {
                        //risk Type Wise Score
                        if (!empty($riskTypeWiseScore) && array_key_exists($cDataId, $riskTypeWiseScore)) {
                            $riskTypeWiseScore[$countX]['label'] =  $this->data['riskCategoryData'][$cDataId];
                            $riskTypeWiseScore[$countX]['y'] += (float) $cDataDetails->wg_sc;
                        } else {
                            $riskTypeWiseScore[$countX]['label'] =  $this->data['riskCategoryData'][$cDataId];
                            $riskTypeWiseScore[$countX]['y'] = (float) $cDataDetails->wg_sc;
                        }


                        if ($cDataDetails->avg_sc > 0 && $assesmentTotalQuestions > 0) {
                            $totalHighRisk += (($cDataDetails->{1} / $assesmentTotalQuestions) * $cRiskDetails['weighted_score']);
                            $totalMediumRisk += (($cDataDetails->{2} / $assesmentTotalQuestions) * $cRiskDetails['weighted_score']);
                            $totalLowRisk += (($cDataDetails->{3} / $assesmentTotalQuestions) * $cRiskDetails['weighted_score']);
                        }

                        $countX++;
                    }
                }
            }

            foreach ($riskTypeWiseScore as $cDetails) {
                $riskTypeWiseScoreArray[] = $cDetails;
            }

            $riskAllData = [
                'riskTypeWiseScore' => $riskTypeWiseScore,
                'riskCategoryScore' => [
                    [
                        'y' => $totalHighRisk,
                        'label' => 'High Risk',
                    ],
                    [
                        'y' => $totalMediumRisk,
                        'label' => 'Medium Risk',
                    ],
                    [
                        'y' => $totalLowRisk,
                        'label' => 'Low Risk',
                    ],
                ],
            ];

            if (is_array($riskAllData)) {
                $res['err'] = false;
                $res['data'] = (object) $riskAllData;
            } else
                $res['msg'] = Notifications::getNoti('noDataFound');
        }

        echo json_encode($res);
        exit;
    }

    public function assesDaysBarDataAjx()
    {
        $auditUnitId = $_POST['audit_id'];

        $res = ['err' => true, 'msg' => Notifications::getNoti('somethingWrong'), 'data' => ''];

        $assesData = '';

        if (!empty($auditUnitId)) {
            // No of Days taken for assesment --------------------------------
            $assesAllData = [];
            $daysAudit = [];
            $daysAuditReview = [];
            $daysCompliance = [];
            $daysComplianceReview = [];

            $model = $this->model('ReportScoringMasterModel');

            $assesData = $model->getAllReportScore([
                'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id > "' . ASSESMENT_TIMELINE_ARRAY[3]['status_id'] . '" AND deleted_at IS NULL',
                'params' => ['audit_unit_id' => $auditUnitId]
            ]);

            foreach ($assesData as $assesId => $assesDetailsData) {
                $dateFrom = date_create($assesDetailsData->assesment_period_from);
                $dateTo = date_create($assesDetailsData->assesment_period_from);
                $months = (date_diff($dateFrom, $dateTo)->format("%m") + 1);

                $monthFrom = $dateFrom->format("m");
                $monthTo = $dateTo->format("m");

                //Assement Period format for label
                if ($months > 1)
                    $assementForLabel = '(' . $dateFrom->format("Y")  . ') ' .  $monthFrom  . ' - ' . $monthTo;
                else
                    $assementForLabel = $dateFrom->format("Y") . '-' . $monthFrom;

                // Audit Days Count

                $dateFrom = date_create($assesDetailsData->audit_start_date);
                $dateTo = date_create($assesDetailsData->audit_end_date);
                $days = (date_diff($dateFrom, $dateTo)->format("%d") + 1);

                $daysAudit[] = ['label' => $assementForLabel, 'y' =>  (float) get_decimal($days, 2)];

                // Review Audit Days Count

                $dateFrom = date_create($assesDetailsData->audit_end_date);
                $dateTo = date_create($assesDetailsData->audit_review_date);
                $days = (date_diff($dateFrom, $dateTo)->format("%d") + 1);

                $daysAuditReview[] = ['label' => $assementForLabel, 'y' =>  (float) get_decimal($days, 2)];

                // Compliance Days Count

                $dateFrom = date_create($assesDetailsData->compliance_start_date);
                $dateTo = date_create($assesDetailsData->compliance_end_date);
                $days = (date_diff($dateFrom, $dateTo)->format("%d") + 1);

                $daysCompliance[] = ['label' => $assementForLabel, 'y' =>  (float) get_decimal($days, 2)];


                // Review Compliance Days Count

                $dateFrom = date_create($assesDetailsData->compliance_end_date);
                $dateTo = date_create($assesDetailsData->compliance_review_date);
                $days = (date_diff($dateFrom, $dateTo)->format("%d") + 1);

                $daysComplianceReview[] = ['label' => $assementForLabel, 'y' =>  (float) get_decimal($days, 2)];
            }

            // Performance Data ----------------------------------------------------

            $this->data['db_data'] = $this->getAssesmentData(); //method call

            $this->data['riskData'] = [];

            foreach ($this->data['db_data'] as $cAuditId => $auditData) {
                //get risk data
                $this->data['riskData'][$cAuditId] = $this->riskArray($cAuditId);
            }

            $riskData = [];
            $riskWeigthedAssesCountData = [];
            $riskTypeWiseScore = [];
            $totalWeightedRiskScore = 0;
            $assesmentWiseScore = [];
            $totalHighRiskJson = [];
            $totalMediumRiskJson = [];
            $totalLowRiskJson = [];
            $allRiskData = [];

            //Risk Data Sorting
            foreach ($this->data['riskData'] as $auditId => $cAssesDetails) {

                $highRiskQuesCount = 0;
                $mediumRiskQuesCount = 0;
                $lowRiskQuesCount = 0;

                $riskWeigthedAssesCountData[$auditId] = count($cAssesDetails);
                foreach ($cAssesDetails as $assesData) {
                    if (!array_key_exists($auditId, $riskData)) {
                        $riskData[$auditId] = ['weighted_score' => 0, 'avg_weighted_score' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'assesWiseRiskData' => []];
                    }

                    $riskData[$auditId]['weighted_score'] += $assesData->weighted_score;

                    $riskData[$auditId]['avg_weighted_score'] = number_format(($riskData[$auditId]['weighted_score'] / $riskWeigthedAssesCountData[$auditId]), 2, ".", "");

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['period'] = $assesData->assesment_period_from . ' to ' . $assesData->assesment_period_to;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['wg_sc'] = 0;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['avg_sc'] = 0;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['1'] = 0;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['2'] = 0;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['3'] = 0;

                    $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['data'] = [];

                    $totalWeightedRiskScore += $assesData->weighted_score;

                    $assesRiskData = (array) $assesData->risk_data;


                    foreach ($assesRiskData as $riskId => $riskScoreData) {
                        $riskScoreDataArr = json_decode($riskScoreData);
                        $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['data'] = $riskScoreDataArr;

                        foreach ($riskScoreDataArr as $riskCatId => $riskCatDetails) {
                            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['wg_sc'] += $riskCatDetails->wg_sc;

                            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['avg_sc'] += $riskCatDetails->avg_sc;

                            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['1'] += $riskCatDetails->{1};

                            $highRiskQuesCount += $riskCatDetails->{1};

                            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['2'] += $riskCatDetails->{2};

                            $mediumRiskQuesCount += $riskCatDetails->{2};

                            $riskData[$auditId]['assesWiseRiskData'][$assesData->assesment_id]['3'] += $riskCatDetails->{3};

                            $lowRiskQuesCount += $riskCatDetails->{3};
                        }
                    }

                    $riskData[$auditId]['high'] = $highRiskQuesCount;
                    $riskData[$auditId]['medium'] = $mediumRiskQuesCount;
                    $riskData[$auditId]['low'] = $lowRiskQuesCount;
                }
            }

            $allRiskData = $riskData[$auditUnitId];

            $allRiskData = [
                [
                    'label' => 'High Risk',
                    'y' => $allRiskData['high']
                ],
                [
                    'label' => 'Medium Risk',
                    'y' => $allRiskData['medium']
                ],
                [
                    'label' => 'Low Risk',
                    'y' => $allRiskData['low']
                ],
            ];

            //----------------------------------------------------

            $assesAllData = [
                'auditDays' => $daysAudit,
                'auditReviewDays' => $daysAuditReview,
                'complianceDays' => $daysCompliance,
                'complianceReviewDays' => $daysComplianceReview,
                'allRiskData' => $allRiskData,
                'branchWisetotalWeightedScore' => number_format($riskData[$auditUnitId]['weighted_score'], '2', '.', ''),
                'branchWiseAvgWeightedScore' => number_format($riskData[$auditUnitId]['avg_weighted_score'], '2', '.', ''),
            ];

            if (is_array($daysAudit) && is_array($daysAuditReview) && is_array($daysCompliance) && is_array($daysComplianceReview)) {
                $res['err'] = false;
                $res['data'] = (object) $assesAllData;
            } else
                $res['msg'] = Notifications::getNoti('noDataFound');
        }

        echo json_encode($res);
        exit;
    }
}
