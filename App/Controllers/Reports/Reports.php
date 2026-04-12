<?php

namespace Controllers\Reports;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;
use Core\DBCommonFunc;
use Exception;

// extra common functions 28.08.2024
require_once APP_CORE . DS . 'HelperFunctionsAuditReport.php';
require_once APP_CORE . DS . 'HelperFunctionBroaderAreaReport.php';
require_once APP_CORE . DS . 'HelperFunctionPerformanceReport.php';

class Reports extends Controller  {

    public $me = null, $data, $request, $empType, $empId, $auditId;
    public $model;

    public function __construct($me) 
    {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('reports')],
        ];      

        $this -> empType = Session::get('emp_type');
        $this -> empId = Session::get('emp_id');

        // request object created
        $this -> request = new Request();
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ]
        ];

        if($this -> empType != 3)
            $this -> data['reports'] = [
                'master_reports' => [
                    'title' => 'Master Reports',
                    'reports' => [ 'financialYearReport', 'financialYearWiseRiskMatrixReport', 'employeeMasterReport', 'auditSectionReport', 'auditUnitMasterReport','broaderAreaAuditUnitReport', 'schemeMasterReport', 'menuMasterReport', 'auditUnitWiseFinancialReport', 'auditUnitWiseLastMarchPositionReport', 'auditUnitWiseAccountsTargetReport', 'auditFrequencyAndLastAssesmentDoneReport', 'auditDurationReport', 'riskTypeReport', 'controlRiskKeyAspectReport', 'categoryMasterReport', 'cbsDepositReport', 'cbsAdvancesReport', 'vouchingErrorCategoryReport', 'annexureReport', 'headerDetailsReport', 'questionSetWiseMappingReport', 'accountwiseScoringReport']
                ]
            ];
        else
            $this -> data['reports'] = [];

        // KUNAL REPORTS ---------------->
        
        if($this -> empType != 3)
        {
            $this -> data['reports']['audit_reports'] = [
                'title' => 'Audit Reports',
                'reports' => [ 
                    // tanvir reports
                    'auditStatusReport','auditStatusExpiredReport','executiveSummaryAuditReport', 'executiveSummaryComplianceReport', 'assesmentTimelineReport', 'assementNotStartedYetReport', 

                    // kunal reports
                    'auditCompleteReport', 'complianceReport', 'complianceSummaryReport', 'auditObservationsReport' ]
            ];

            $this -> data['reports']['advanced_reports'] = [
                'title' => 'Advanced Reports',
                'reports' => [ 'riskWeightageReport', 'broaderAreaWiseScoringReport', 'riskWiseAuditUnitsReport', 'auditObservationCountReport', 'pendingComplianceDetailReport', 'auditCommitteeBoardReport1'/*, 'questionwiseBroaderAreaReport'*/,
                /*'internalAssesmentReport',*/'performanceRiskWeightageReport', 'performanceRiskWeightageReportCategoryWise', 'RBIAPerformanceRiskWeightageReportAllUnits', 
                
                // Sahil Reports
                'categoryWiseRiskWeightageReports', 'borderAriaWiseRiskWeightageReport', 'riskCategorySummaryReport', 'questionWiseConsolidateSummary', 'typewiseRiskWeightageReport', 'riskTrendSummaryReport','riskTypeWiseRiskWeightageReport','roOfficerReviewReport', /*'riskTredSummaryAnalysis'*/]
            ];
        }
        else
        {
            $this -> data['reports']['audit_reports'] = [
                'title' => 'Audit Reports',
                'reports' => [ 
                    // tanvir reports
                    'auditStatusReport', 'auditStatusExpiredReport','executiveSummaryAuditReport', 'executiveSummaryComplianceReport',

                    // kunal reports
                    'auditCompleteReport', 'complianceReport' ]
            ];
        }

        $this -> data['data_container'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    private function generateMe ($meKey, $pageSize = null) 
    {
        $this -> me = SiteUrls::get($meKey);

        $this -> data['pageTitle'] = $this -> me -> pageTitle;
        
        // menuKey for active menu
        $this -> me -> menuKey = 'reports';

        $this -> data['need_print'] = true;
        $this -> data['need_excel'] = true;
    }

    // ALL PRIVATE METHODS
    private function getYearData()
    {
        // common yearData for all // find year model
        $model = $this -> model('YearModel');

        //get all year data
        $this -> data['year_data'] = $model -> getAllYears([
            'where' => 'deleted_at IS NULL'
        ]);

        //get all year 
        $year = DBCommonFunc::yearMasterData($model, ['where' => 'deleted_at IS NULL']);

        $this -> data['select_year_data'] = generate_array_for_select($year, 'id', 'year');

        $this -> data['db_year_data'] = generate_data_assoc_array($year, 'id', 'year');
    }

    private function getCommonData($dataArray = [])
    {
        $res = [ 'audit_section' => null, 'risk_category' => null, 'employee_data' => null ];
        
        if(isset($dataArray['audit_section']))
        {
            // find audit section model // get all audit section data
            $model = $this -> model('AuditSectionModel');
            $res['audit_section'] = $model -> getAllAuditSection([ 'where' => 'deleted_at IS NULL' ]);
            $res['audit_section'] = generate_data_assoc_array($res['audit_section'], 'id');
        }

        if(isset($dataArray['risk_category']))
        {
            $model = $this -> model('RiskCategoryModel');
            $res['risk_category'] = $model -> getAllRiskCategory([ 'where' => 'deleted_at IS NULL' ]);
            $res['risk_category'] = generate_data_assoc_array($res['risk_category'], 'id');
        }

        if(isset($dataArray['employee_data']))
        {
            $model = $this -> model('EmployeeModel');    
             
            if($dataArray['employee_data'] === 'DB')
            {
                $res['employee_data'] = DBCommonFunc::getAllEmployeeData($model, [
                    'where' => 'emp_code != "ADMIN" AND deleted_at IS NULL ORDER BY emp_code+0 ASC'
                ]);
            }
            else
            {
                $res['employee_data'] = $model -> getAllEmployees([
                    'where' => 'emp_code != "ADMIN" AND deleted_at IS NULL ORDER BY emp_code+0 ASC'
                ]);
            }

            $res['employee_data'] = generate_data_assoc_array($res['employee_data'], 'id');
        }

        return $res;
    }

    private function getAllAuditUnits($filter = null, $sortSelect = false, $dbCommon = false)
    {
        if(!is_array($filter))
            $filter = [
                'where' => 'is_active = 1 AND deleted_at IS NULL ORDER BY audit_unit_code+0',
                'params' => [],
            ];

        // find audit unit model
        $model = $this -> model('AuditUnitModel');

        //get all audit unit data
        if(!$dbCommon)
            $auditUnitData = $model -> getAllAuditUnit($filter);
        else
            $auditUnitData = DBCommonFunc::getAllAuditUnitData($model, $filter);

        if($sortSelect)
            $auditUnitData = generate_array_for_select($auditUnitData, 'id', 'name');
        else
            $auditUnitData = generate_data_assoc_array($auditUnitData, 'id');

        return $auditUnitData;
    }

    public function reportAuditUnitsAjax($returnRes = false, $compliance = false)
    {
        $res_array = [ 'msg' => Notifications::getNoti('somethingWrong'), 'res' => 'err' ];

        $auditUnitId = null;

        if($this -> request -> has('audit_unit_id'))
            $auditUnitId = $this -> request -> input('audit_unit_id');
        else if($this -> request -> has('reportAuditUnit'))
            $auditUnitId = $this -> request -> input('reportAuditUnit');        

        if($this -> request -> has('complianceNeeded'))
            $compliance = true;

        $recompliance_status = $this -> request -> has('pendingReCompliance');

        if(!empty($auditUnitId))
        {
            // find audit unit details
            $auditUnitDetails = $this -> getAllAuditUnits([
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL ORDER BY section_type_id+0, name',
                'params' => [ 'id' => $auditUnitId ]
            ]);

            if(is_array($auditUnitDetails) && array_key_exists($auditUnitId, $auditUnitDetails))
            {
                // convert to single object
                $auditUnitDetails = $auditUnitDetails[ $auditUnitId ];

                $res_array['audit_unit_data'] = $auditUnitDetails;

                // find assesments
                $model = $this -> model('AuditAssesmentModel');

                if($recompliance_status)
                {
                    $whereData = [
                        'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id = 6 AND deleted_at IS NULL',
                        'params' => [ 'audit_unit_id' => $auditUnitId ]
                    ];
                }
                else
                {
                    $whereData = [
                        'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id > 1 AND deleted_at IS NULL',
                        'params' => [ 'audit_unit_id' => $auditUnitId ]
                    ];
                }

                if($compliance)
                    $whereData['where'] .= ' AND audit_status_id > 4';

                $auditAssesmentDetails = DBCommonFunc::getAllAuditAssesment($model, $whereData);

                if(is_array($auditAssesmentDetails) && sizeof($auditAssesmentDetails) > 0)
                {
                    $res_array['msg'] = null;
                    $res_array['data'] = generate_data_assoc_array($auditAssesmentDetails, 'id');
                    $res_array['success'] = true;
                }
                else
                    $res_array['msg'] = Notifications::getNoti('noDataFound');
            }
            else
                $res_array['msg'] = Notifications::getNoti('auditUnitSelect');
        }
        else
            $res_array['msg'] = Notifications::getNoti('auditUnitSelect');

        if($returnRes)
            return $res_array;

        echo json_encode($res_array);
    }

    private function getAllRiskCategories($filter = null)
    {
        if(!is_array($filter))
            $filter = [
                'where' => 'is_active = 1 AND deleted_at IS NULL',
                'params' => [],
            ];

        // find audit unit model
        $model = $this -> model('RiskCategoryModel');

        $getAllRiskCategories = $model -> getAllRiskCategory($filter);

        return generate_data_assoc_array($getAllRiskCategories, 'id');
    }

    private function dateValidation($validationArray)
    {
        $notiObj = new Notifications;

        // function call
        $validationArray = array_merge($validationArray, date_validation_helper($this -> request, $validationArray, $notiObj)['validation']);

        return $validationArray;
    }

    // 1. Financial Year Setup Report
    public function financialYearReport()
    {  
        // getting year data
        $this -> getYearData();

        $this -> generateMe('financialYearReport', 1);

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
        ]);
    }

    // 2. Financial Year Wise Risk Matrix Report
    public function financialYearWiseRiskMatrixReport()
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe('financialYearWiseRiskMatrixReport', 1);

        // find risk matrix model
        $this -> model = $this -> model('RiskMatrixModel');

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [
                'financial_year' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> data['risk_matrix_data'] = $this -> model -> getAllRiskMatrix([
                    'where' => 'deleted_at IS NULL AND year_id = :year_id',
                    'params' => ['year_id' => $this -> request -> input( 'financial_year' )]
                ]); 
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 3. Employee Master Report
    public function employeeMasterReport()
    {
        $this -> generateMe('employeeMasterReport');
        $this -> data['employee_data'] = $this -> getCommonData(['employee_data' => true])['employee_data'];

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 4. Audit Section Report
    public function auditSectionReport()
    {
        $this -> generateMe('auditSectionReport');

        $this -> data['audit_section_data'] = $this -> getCommonData([ 'audit_section' => true ])['audit_section'];

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 5. Audit Unit Master Report
    public function auditUnitMasterReport()
    {
        $this -> generateMe('auditUnitMasterReport');

        $this -> data['db_audit_section_data'] = $this -> getCommonData([ 'audit_section' => true ])['audit_section'];

        // get all audit unit data
        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, false);
        $this -> data['db_audit_unit_data'] = $this -> getAllAuditUnits(null, true, false);

        // get all employee data
        $this -> data['db_employee_data'] = $this -> getCommonData(['employee_data' => true])['employee_data'];
        $this -> data['page'] = 'A4L';

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]); 
    }

    // 6. Broader Area of Audit Non-compliance Master Report
    public function broaderAreaAuditUnitReport()
    {
        $this -> generateMe('broaderAreaAuditUnitReport');

        // find audit section model
        $model = $this -> model('BroaderAreaModel');

        //get all audit section data
        $this -> data['broader_area_data'] = $model -> getAllBroaderArea([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['db_broader_area_data'] = generate_array_for_select($this -> data['broader_area_data'], 'id', 'name');

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 7. Scheme Master Report
    public function schemeMasterReport()
    {
        $this -> generateMe('schemeMasterReport');

        // find scheme model
        $model = $this -> model('SchemeModel');

        // get all scheme data
        $this -> data['scheme_data'] = $model -> getAllSchemes([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['db_scheme_data'] = generate_array_for_select($this -> data['scheme_data'], 'id', 'name');

        // find category model
        $model = $this -> model('CategoryModel');

        // get all category data
        $this -> data['category_data'] = $model -> getAllCategory([
            'where' => 'deleted_at IS NULL AND linked_table_id != 0'
        ]);

        $this -> data['db_category_data'] = generate_array_for_select($this -> data['category_data'], 'id', 'name');

        $this -> data['db_category_data'][] = 'Not Found';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 8. Menu Master Report
    public function menuMasterReport()
    {
        $this -> generateMe('menuMasterReport');

        // find audit section model
        $model = $this -> model('MenuModel');

        // get all audit section data
        $this -> data['menu_data'] = $model -> getAllMenu([
            'where' => 'deleted_at IS NULL ORDER BY section_type_id+0 ASC'
        ]);

        $this -> data['audit_section_data'] = $this -> getCommonData([ 'audit_section' => true ])['audit_section'];

        $model = $this -> model('AuditSectionModel');
        $extraOption = $model -> emptyInstance();
        $extraOption -> name = ERROR_VARS['notFound']; 

        $this -> data['audit_section_data'][] = $extraOption;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 9. Audit Unit Wise Financial Report
    public function auditUnitWiseFinancialReport()
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe('auditUnitWiseFinancialReport');

        // get all audit unit data
        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, true);
        $this -> data['db_audit_unit_data'] = $this -> getAllAuditUnits(null, true, false);

        // find target model
        $this -> model = $this -> model('TargetMasterModel');

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [
                'financial_year' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> data['target_data'] = $this -> model -> getAllTarget([
                    'where' => 'deleted_at IS NULL AND year_id =:year_id',
                    'params' => ['year_id' => $this -> request -> input( 'financial_year' )]
                ]);
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    private function commonDataLastMarchPosition()
    {
        // getting year data
        $this -> getYearData(); 

        $this -> data['db_audit_unit_data'] = $this -> getAllAuditUnits(null, false, true);

        // find target model
        $this -> model = $this -> model('ExeSummaryModel');

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [
                'financial_year' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> data['executive_summary_data'] = $this -> model -> getAllMarchPosition([
                    'where' => 'deleted_at IS NULL AND year_id = :year_id ORDER BY audit_unit_id+0',
                    'params' => [ 'year_id' => $this -> request -> input( 'financial_year' )]
                ]);
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 10. Audit Unit Wise Last March Position Report
    public function auditUnitWiseLastMarchPositionReport()
    {

        $this -> generateMe('auditUnitWiseLastMarchPositionReport');

        $this -> commonDataLastMarchPosition();
    }

    // 11. Audit Unit Wise Accounts Target Report
    public function auditUnitWiseAccountsTargetReport()
    {
        $this -> generateMe('auditUnitWiseAccountsTargetReport', 2);
        $this -> data['page'] = 'A4L';

        $this -> commonDataLastMarchPosition();
    }

    // 12. Audit Frequency and Last Assesment Done Report
    public function auditFrequencyAndLastAssesmentDoneReport()
    {
        $this -> generateMe('auditFrequencyAndLastAssesmentDoneReport', 2);

        $this -> data['db_audit_section_data'] = $this -> getCommonData([ 'audit_section' => true ])['audit_section'];
        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, false);

        // get all employee data
        $this -> data['employee_data'] = $this -> getCommonData(['employee_data' => true])['employee_data'];
        $this -> data['page'] = 'A4L';

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 13. Audit Duration Report
    public function auditDurationReport()
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe('auditDurationReport', 2);

        $this -> data['db_audit_unit_data'] = $this -> getAllAuditUnits(null, false, true);
        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, true, false);

        // get all employee data
        $this -> data['db_employee_data'] = $this -> getCommonData(['employee_data' => true])['employee_data'];

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [
                'financial_year' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> model = $this -> model('AuditAssesmentModel');
                
                $this -> data['assesment_data'] = DBCommonFunc::getAllAuditAssesment($this -> model, [
                    'where' => 'deleted_at IS NULL AND year_id = :year_id AND audit_end_date IS NOT NULL',
                    'params' => ['year_id' => $this -> request -> input( 'financial_year' )]
                ], 'id, year_id, audit_unit_id, branch_head_id, branch_subhead_id, frequency, assesment_period_from, assesment_period_to, audit_start_date, audit_end_date, audit_emp_id, compliance_emp_id,multi_compliance_ids');
            }
        });

        $this -> data['page'] = 'A4L';

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 14. Risk Type Report
    public function riskTypeReport()
    {
        // getting year data
        $this -> getYearData();
        
        $this -> generateMe('riskTypeReport');

        $this -> model = $this -> model('RiskCategoryModel');

        $this -> data['risk_category_data'] = $this -> model -> getAllRiskCategory([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['risk_category_data'] = generate_array_for_select($this -> data['risk_category_data'], 'id', 'risk_category');

        // post method after form submit
        $this -> request::method("POST", function() {
            
            //check validation
            Validation::validateData($this -> request, [
                'financial_year' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> model = $this -> model('RiskCategoryWeightModel');
                
                $this -> data['risk_category_weigth_data'] = $this -> model -> getAllRiskCategoryWeight([
                    'where' => 'deleted_at IS NULL AND year_id = :year_id',
                    'params' => ['year_id' => $this -> request -> input( 'financial_year' )]
                ]);
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 15. Control Risk - Key Aspects Report
    public function controlRiskKeyAspectReport ()
    {
        $this -> generateMe('controlRiskKeyAspectReport');

        // Risk Control Key Aspect Model
        $this -> model = $this -> model('RiskControlKeyAspectModel');

        $this -> data['risk_control_key_aspec_data'] = $this -> model -> getAllRiskControlKeyAspect([
            'where' => 'deleted_at IS NULL'
        ]);

        // Risk Control Model
        $this -> model = $this -> model('RiskControlModel');

        $this -> data['risk_control_data'] = $this -> model -> getAllRiskControl([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['risk_control_data'] = generate_array_for_select($this -> data['risk_control_data'], 'id', 'name');


        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 16. Category Master Report
    public function categoryMasterReport ()
    {
        $this -> generateMe('categoryMasterReport');

        // Category Model
        $this -> model = $this -> model('CategoryModel');

        $this -> data['category_data'] = $this -> model -> getAllCategory([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['category_data'] = generate_data_assoc_array($this -> data['category_data'], 'id');

        $this -> data['category_select_data'] = generate_array_for_select($this -> data['category_data'], 'id', 'name');

        // find menu model
        $this -> model = $this -> model('MenuModel');

        //get menu data
        $this -> data['menu_data'] = $this -> model -> getAllMenu([
            'where' => 'deleted_at IS NULL ORDER BY section_type_id+0 ASC'
        ]);

        $this -> data['menu_data'] = generate_array_for_select($this -> data['menu_data'], 'id', 'name');

        // Set Model
        $this -> model = $this -> model('QuestionSetModel');

        $this -> data['set_data'] = $this -> model -> getAllQuestionSet([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['set_select_data'] = generate_array_for_select($this -> data['set_data'], 'id', 'name');

        // Shceme Model
        $this -> model = $this -> model('SchemeModel');

        $this -> data['scheme_data'] = $this -> model -> getAllSchemes([
            'where' => 'deleted_at IS NULL AND category_id != 0 '
        ]);

        if(is_array($this -> data['scheme_data']) && sizeof($this -> data['scheme_data']) > 0 && is_array($this -> data['category_data']))
        {
            foreach($this -> data['scheme_data'] as $cSchemeDetails)
            {
                if(array_key_exists($cSchemeDetails -> category_id, $this -> data['category_data']))
                {
                    if(!isset($this -> data['category_data'][$cSchemeDetails -> category_id] -> scheme_data))
                        $this -> data['category_data'][$cSchemeDetails -> category_id] -> scheme_data = [];

                    $this -> data['category_data'][$cSchemeDetails -> category_id] -> scheme_data[$cSchemeDetails -> id] = $cSchemeDetails;                        
                }
            }
        }

        $this -> data['page'] = 'A4L';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    private function cbsCommonData ($dumpType)
    {
        // getting year data
        $this -> getYearData();

        if($dumpType == 1)
            $this -> model = $this -> model('DumpDepositeModel');
        elseif($dumpType == 2)
            $this -> model = $this -> model('DumpAdvancesModel');

            //post method after form submit
            $this -> request::method("POST", function() 
            {
                //check validation
                Validation::validateData($this -> request, [
                    'financial_year' => 'required'
                ]);

                //validation check
                if($this -> request -> input( 'error' ) > 0)    
                    Validation::flashErrorMsg(); 
                else
                {
                    $fYear = $this -> data['select_year_data'][ $this -> request -> input('financial_year') ];
                    $fYear = explode('-', $fYear);
                    $fYear = trim_str($fYear[0]);

                    if($this -> request -> input( 'financial_year' ) != '')
                    {
                        $this -> data['dump_data'] = $this -> model -> getAllAccounts([
                            'where' => 'deleted_at IS NULL AND (upload_period_from BETWEEN :upload_period_from AND :upload_period_to) AND (upload_period_to BETWEEN :upload_period_from AND :upload_period_to) GROUP BY upload_key',
                            'params' => [
                                'upload_period_from' => $fYear . "-04-01",
                                'upload_period_to' => ( $fYear + 1 ) . "-03-31",
                            ]
                        ]);
                    }
                    else
                        $this -> data['dump_data'] = [] ;

                    if(is_array($this -> data['dump_data']) && sizeof($this -> data['dump_data']) > 0)
                    {
                        foreach($this -> data['dump_data'] as $ckey => $cDepositDetails)
                        {
                            if(!isset($this -> data['dump_data'][$ckey] -> year_id))
                                $this -> data['dump_data'][$ckey] -> year_id = [];

                            $this -> data['dump_data'][$ckey] -> year_id[] = $this -> request -> input( 'financial_year');                        
                        }
                    }
                }
            });

            //load view // helper function call
            return return2View($this, $this -> me -> viewDir . 'index', [
                'data' => $this -> data,
                'request' => $this -> request,
            ]);
    }

    // 17. Deposit - CBS Data Upload Status Report
    public function cbsDepositReport ()
    {
        $this -> generateMe('cbsDepositReport');

        $this -> cbsCommonData(1);
    }

    // 18. Advances - CBS Data Upload Status Report
    public function cbsAdvancesReport ()
    {
        $this -> generateMe('cbsAdvancesReport');

        $this -> cbsCommonData(2);        
    }

    // 19. Vouching Error Category Report
    public function vouchingErrorCategoryReport ()
    {
        $this -> generateMe('vouchingErrorCategoryReport');

        // Annexure Column Model
        $this -> model = $this -> model('AnnexureColumnModel');

        $this -> data['annex_col_data'] = $this -> model -> getSingleAnnexureColumns([
            'where' => 'id = 8 AND deleted_at IS NULL'
        ]);

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 20. Annexure Report
    public function annexureReport ()
    {
        $this -> generateMe('annexureReport', 2);

        // Annexure Master Model
        $this -> model = $this -> model('AnnexureMasterModel');

        $this -> data['annex_master_data'] = $this -> model -> getAllAnnexures([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['annex_master_data'] = generate_data_assoc_array($this -> data['annex_master_data'], 'id');

        // Risk Category Master Model
        $this -> model = $this -> model('RiskCategoryModel');

        $this -> data['risk_category_data'] = $this -> model -> getAllRiskCategory([
            'where' => 'deleted_at IS NULL AND is_active = 1'
        ]);

        $this -> data['risk_category_select_data'] = generate_array_for_select($this -> data['risk_category_data'], 'id', 'risk_category');

        $this -> data['risk_category_data'] = generate_data_assoc_array($this -> data['risk_category_data'], 'id');

        // Annexure Column Model
        $this -> model = $this -> model('AnnexureColumnModel');

        $this -> data['annex_col_data'] = $this -> model -> getAllAnnexureColumns([
            'where' => 'deleted_at IS NULL'
        ]);

        if(is_array($this -> data['annex_col_data']) && sizeof($this -> data['annex_col_data']) > 0 && is_array($this -> data['annex_master_data']))
        {
            foreach($this -> data['annex_col_data'] as $cColDetails)
            {
                if(array_key_exists($cColDetails -> annexure_id, $this -> data['annex_master_data']))
                {
                    if(!isset($this -> data['annex_master_data'][$cColDetails -> annexure_id] -> column_data))
                        $this -> data['annex_master_data'][$cColDetails -> annexure_id] -> column_data = [];

                    $this -> data['annex_master_data'][$cColDetails -> annexure_id] -> column_data[$cColDetails -> id] = $cColDetails;                        
                }
            }
        }        

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 21. Header Details Report
    public function headerDetailsReport ()
    {
        $this -> generateMe('headerDetailsReport');

        // Header Model
        $this -> model = $this -> model('QuestionHeaderModel');

        $this -> data['header_data'] = $this -> model -> getAllQuestionHeader([
            'where' => 'deleted_at IS NULL'
        ]);

        // Set Model
        $this -> model = $this -> model('QuestionSetModel');

        $this -> data['set_data'] = $this -> model -> getAllQuestionSet([
            'where' => 'deleted_at IS NULL'
        ]);

        $this -> data['set_select_data'] = generate_array_for_select($this -> data['set_data'], 'id', 'name');

        $this -> data['set_type_data'] = generate_array_for_select($this -> data['set_data'], 'id', 'set_type_id');

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 22. Question Set Wise Mapping Report
    public function questionSetWiseMappingReport ()
    {
        $this -> generateMe('questionSetWiseMappingReport', 2);

        // Set Model
        $this -> model = $this -> model('QuestionSetModel');

        // id = 1 executive summary skip
        $this -> data['set_data'] = $this -> model -> getAllQuestionSet([
            'where' => 'id != 1 AND deleted_at IS NULL AND is_active = 1'
        ]);

        $this -> data['set_data'] = generate_data_assoc_array($this -> data['set_data'], 'id');

        // Header Model
        $this -> model = $this -> model('QuestionHeaderModel');

        $this -> data['header_data'] = $this -> model -> getAllQuestionHeader([
            'where' => 'deleted_at IS NULL AND is_active = 1'
        ]);

        $this -> data['header_data'] = generate_data_assoc_array($this -> data['header_data'], 'id');

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [ 'set' => 'required' ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> model = $this -> model('QuestionMasterModel');

                $this -> data['questions_data'] = $this -> model -> getAllQuestions([
                    'where' => 'set_id =:set_id AND deleted_at IS NULL',
                    'params' => [ 'set_id' => $this -> request -> input( 'set' ) ]
                ]);
            }
        });

        // set page
        $this -> data['page'] = 'A4L';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 23. Account-Wise Scoring Report
    public function accountwiseScoringReport ()
    {
        $this -> generateMe('accountwiseScoringReport');

        $this -> data['need_calender'] = true;

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, 1);        

        $this -> data['audit_unit_data']  = ([
            'all_branches' => (object)['id' => 'all_branches', 'combined_name' => string_operations('All Branches', 'upper')],
            'all_head_of_dept' => (object)['id' => 'all_head_of_dept', 'combined_name' => string_operations('All Head Of Departments', 'upper')]
        ] + $this -> data['audit_unit_data']);

        //post method after form submit
        $this -> request::method("POST", function() 
        {
            //check validation
            Validation::validateData($this -> request, [
                'startDate' => 'required',
                'endDate' => 'required',
                'audit_unit_id' => 'required',
                'scheme_type' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'index', [ 'request' => $this -> request ]);
            } 
            else
            {   
                // Scheme Model
                $this -> model = $this -> model('SchemeModel');

                $this -> data['scheme_data'] = $this -> model -> getAllSchemes([
                    'where' => 'deleted_at IS NULL AND is_active = 1'
                ]);

                $this -> data['scheme_data'] = generate_data_assoc_array($this -> data['scheme_data'], 'id');
                
                // condition for advances
                if($this -> request -> input('scheme_type') == 2)
                {
                    $select = "SELECT loan_dump.branch_id, loan_dump.scheme_id, loan_dump.account_no, loan_dump.account_holder_name, loan_dump.sanction_amount,
                    loan_dump.account_opening_date, ";
                }
                // condition for deposits
                elseif($this -> request -> input('scheme_type') == 1)
                {
                    $select = "SELECT deposit_dump.branch_id, deposit_dump.scheme_id, deposit_dump.account_no, deposit_dump.account_holder_name,
                    deposit_dump.account_opening_date, ";
                } 
                    
                $select .= "(SELECT name FROM audit_area_master WHERE id  = qm.area_of_audit_id) broader_area, 
                (SELECT risk_category FROM risk_category_master WHERE id  = qm.risk_category_id) risk_type, 
                (SELECT DISTINCT business_risk_score FROM risk_matrix WHERE risk_parameter = ans.business_risk) business_risk_total,
                (SELECT DISTINCT control_risk_score FROM risk_matrix WHERE risk_parameter = ans.control_risk) control_risk_total,
                ans.question_id, aum.section_type_id, ans.assesment_id ";

                // condition for advances
                if($this -> request -> input('scheme_type') == 2)
                    $select .= "FROM answers_data ans, dump_advances loan_dump, question_master qm, audit_assesment_master aam JOIN audit_unit_master aum ON aam.audit_unit_id = aum.id WHERE ans.menu_id = 8 AND ans.question_id = qm.id
                    AND ans.dump_id = loan_dump.id ";

                // condition for deposits
                elseif($this -> request -> input('scheme_type') == 1)
                    $select .= "FROM answers_data ans, dump_deposits deposit_dump, question_master qm, audit_assesment_master aam JOIN audit_unit_master aum ON aam.audit_unit_id = aum.id WHERE ans.menu_id = 9 AND ans.question_id = qm.id
                    AND ans.dump_id = deposit_dump.id ";
                    
                $select .="AND ((ans.business_risk IN(1,2,3) OR ans.control_risk IN(1,2,3)) OR ans.is_compliance = 1)
                AND ans.assesment_id 
                IN
                ( SELECT id FROM audit_assesment_master WHERE 
                assesment_period_from >= '" . $this -> request -> input('startDate') . "'
                AND assesment_period_to <= '" . $this -> request -> input('endDate') . "'
                AND audit_status_id = 7 ";

                if($this -> request -> input('audit_unit_id') == 'all_branches')
                    $select .= "AND aum.section_type_id = 1) ";
                elseif($this -> request -> input('audit_unit_id') == 'all_head_of_dept')
                    $select .= "AND aum.section_type_id = 2) ";
                elseif($this -> request -> input('audit_unit_id') != 'all_branches' || $this -> request -> input('audit_unit_id') != 'all_head_of_dept')
                    $select .= "AND audit_unit_id IN (" . $this -> request -> input('audit_unit_id') . ")) ";
                else
                    $select .= ") ";

                // condition for advances
                if($this -> request -> input('scheme_type') == 2)    
                    $select .= "group by 
                    loan_dump.scheme_id,
                    loan_dump.account_no,
                    qm.area_of_audit_id,
                    qm.risk_category_id,
                    ans.assesment_id,
                    ans.business_risk,
                    ans.control_risk";
                    
                // condition for deposits
                elseif($this -> request -> input('scheme_type') == 1)
                    $select .= "group by 
                    deposit_dump.scheme_id,
                    deposit_dump.account_no,
                    qm.area_of_audit_id,
                    qm.risk_category_id,
                    ans.assesment_id,
                    ans.business_risk,
                    ans.control_risk";

                $model = $this -> model('AuditAssesmentModel');                

                // echo $select;
                $this -> data['details_of_account_data'] = get_all_data_query_builder(2, $model, 'audit_assesment_master', [], 'sql', $select);
            }
        });

        // set page
        $this -> data['page'] = 'A4L';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    private function prepareAuditStatusData($type)
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe($type, 2);

        // get all employee data
        $this -> data['db_employee_data'] = $this -> getCommonData(['employee_data' => 'DB'])['employee_data'];

        $presentDate = date($GLOBALS['dateSupportArray'][1]);

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, 1);

        $this -> data['audit_unit_data']  = ([
            'all_branches' => (object)['id' => 'all_branches', 'combined_name' => string_operations('All Branches', 'upper')],
            'all_head_of_dept' => (object)['id' => 'all_head_of_dept', 'combined_name' => string_operations('All Head Of Departments', 'upper')]
        ] + $this -> data['audit_unit_data']);
    }

    private function handleAuditStatusPostRequest()
    {
        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);
            $this -> data['empType'] =   $this -> empType;
        }
        
        $this -> request::method("POST", function() 
        {
            $presentDate = date($GLOBALS['dateSupportArray'][1]);

            if($this -> empType == 3)
                // check validation
                Validation::validateData($this->request, [
                    'financial_year' => 'required',
                ]);
            else 
                // check validation
                Validation::validateData($this->request, [
                    'audit_unit_id' => 'required',
                    'financial_year' => 'required',
                ]);

            // validation check
            if ($this -> request -> input('error') > 0)
            {    
                Validation::flashErrorMsg();
                return return2View($this, $this->me->viewDir . 'index', ['request' => $this->request]);
            }
            else
            {
                $select = "SELECT asm.*, aum.id, audit_unit_id, aum.audit_unit_code, aum.name, aum.section_type_id FROM audit_assesment_master as asm JOIN audit_unit_master as aum ON asm.audit_unit_id = aum.id";
                
                // Report for Branch Logic
                if($this -> empType == 3)
                {
                    $select .= " WHERE asm.audit_unit_id = '". $this -> auditId ."' ";
                }
                else
                {
                    // append search type 
                    if ($this -> request -> input('audit_unit_id') == 'all_branches')
                        $select .= " WHERE aum.section_type_id = 1 ";
                    else if ($this -> request -> input('audit_unit_id') == 'all_head_of_dept')
                        $select .= " WHERE aum.section_type_id > 1 ";
                    else 
                        $select .= " WHERE asm.audit_unit_id = '". $this -> request -> input('audit_unit_id') ."' ";
                }

                // append financial year
                if ($this -> request -> input('financial_year') != 'all')
                    $select .= " AND asm.year_id = " . $this -> request -> input('financial_year') . " ";

                // append audit/compliance status query
                if (isset($this -> data['complianceStatusArray'][$this -> request -> input('audit_status')])) {
                    $select .= $this -> data['complianceStatusArray'][$this -> request -> input('comp_status')]['query'] . " ";

                    if (!in_array($this -> request -> input('comp_status'), [10, 11, 'all']))
                        $select .= $this -> data['complianceStatusArray'][$this -> request -> input('comp_status')]['query'] . " ";

                } else {
                    $select .= $this->data['auditStatusArray'][$this->request->input('audit_status')]['query'] . " ";
                }

                $select .= "ORDER BY aum.audit_unit_code+0 ASC, asm.audit_unit_id ASC";

                $model = $this -> model('AuditAssesmentModel');             

                $this->data['details_of_audit_data'] = get_all_data_query_builder(2, $model, 'audit_assesment_master', [], 'sql', $select);
            }
        });

        // set page
        $this -> data['page'] = 'A4L';

        return return2View($this, $this->me->viewDir . 'index', [
            'data' => $this->data,
            'request' => $this->request,
        ]);
    }

    // 24. Audit Status Report
    public function auditStatusReport()
    {
        $this -> prepareAuditStatusData('auditStatusReport');

        $this -> data['auditStatusArray'] = [
            'all' => ['status' => 'All AUDIT', 'query' => ''],
            1 => ['status' => 'PENDING', 'query' => 'AND audit_status_id = 1 '],
            2 => ['status' => 'REVIEW PENDING', 'query' => 'AND audit_status_id = 2 '],
            3 => ['status' => 'RE-AUDIT NEEDED', 'query' => 'AND audit_status_id = 3 '],
            4 => ['status' => 'COMPLETED', 'query' => 'AND audit_status_id >= 4 '],
            12 => ['status' => 'BLOCKED', 'query' => 'AND audit_status_id < 4 AND is_limit_blocked = 1']
        ];

        $this -> data['complianceStatusArray'] = [
            'all' => ['status' => 'All COMPLIANCE', 'query' => ''],
            4 => ['status' => 'PENDING', 'query' => 'AND audit_status_id = 4 '],
            5 => ['status' => 'REVIEW PENDING', 'query' => 'AND audit_status_id = 5 '],
            6 => ['status' => 'RE-COMPLIANCE NEEDED', 'query' => 'AND audit_status_id = 6 '],
            7 => ['status' => 'COMPLETED', 'query' => 'AND audit_status_id = 7 '],
            10 => ['status' => 'BLOCKED', 'query' => 'AND ( audit_status_id BETWEEN 4 AND 7 ) AND is_limit_blocked = 1']
        ];

        $this -> handleAuditStatusPostRequest();
    }

    // 25. Audit Status Expired Report
    public function auditStatusExpiredReport()
    {
        $this->prepareAuditStatusData('auditStatusExpiredReport');

        $presentDate = date($GLOBALS['dateSupportArray'][1]);

        $this -> data['auditStatusArray'] = [
            13 => ['status' => 'AUDIT EXPIRED', 'query' => 'AND audit_status_id IN (1,3) AND is_limit_blocked = 0 AND audit_due_date < "' . $presentDate . '"'],
            11 => ['status' => 'COMPLIANCE EXPIRED', 'query' => 'AND audit_status_id IN (4,6)  AND is_limit_blocked = 0 AND compliance_due_date < "' . $presentDate . '"']
        ];

        $this -> handleAuditStatusPostRequest();
    }

    private function getAuditId($empId)
    {   
        $auditUnitModel = $this -> model('AuditUnitModel');
        
        $query = " SELECT id FROM audit_unit_master ";

        $result = get_all_data_query_builder(1, $auditUnitModel, 'audit_unit_master', [
            'where' => ' (branch_head_id = ' . $empId . ' OR branch_subhead_id = ' . $empId . ') AND deleted_at IS NULL',
            'params' => []
        ], 'sql', $query);

        if(is_object($result))
            return $result -> id;

        return 0;
    }

    private function commonExecutiveSummaryData ()
    {   
        $this -> data['empType'] =   $this -> empType;

        // $this -> request -> setInputCustom('audit_unit_id',1);

        // $this -> reportAuditUnitsAjax(1);

        //executive summary audit js
        $this -> data['js'][] = 'executive-summary-audit.js';

        // get all employee data
        $this -> data['employee_data'] = $this -> getCommonData(['employee_data' => 'DB'])['employee_data'];

        //post method after form submit
        $this -> request::method("POST", function() 
        {   
            //check validation
            Validation::validateData($this -> request, [
                'reportAuditUnit' => 'required',
                'reportAuditAssesment' => 'required',
            ]);     

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> data['assesment_id'] = $this -> request -> input( 'reportAuditAssesment' );
                $exeBasicModel = $this -> model('ExeSummaryBasicModel');
                $exeBranchModel = $this -> model('ExeSummaryBranchPositionModel');
                $exeFreshModel = $this -> model('ExeSummaryFreshAccountModel');

                //basic data
                $this -> data['exeBasicData'] = $exeBasicModel -> getSingleBasicDetails([
                    'where' => 'assesment_id =:assesment_id AND deleted_at IS NULL',
                    'params' => [
                        'assesment_id' => $this -> request -> input( 'reportAuditAssesment' ),
                    ]
                ]);

                $this -> data['exeBranchData'] = $exeBranchModel -> getAllBranchPosition([
                    'where' => 'assesment_id =:assesment_id AND deleted_at IS NULL',
                    'params' => [
                        'assesment_id' => $this -> request -> input( 'reportAuditAssesment'),
                    ]
                ]);

                $this -> data['exeBranchData'] = generate_data_assoc_array($this -> data['exeBranchData'], 'type_id');

                $this -> data['exeFreshData'] = $exeFreshModel -> getAllFreshAccount([
                    'where' => 'assesment_id =:assesment_id AND deleted_at IS NULL',
                    'params' => [
                        'assesment_id' => $this -> request -> input( 'reportAuditAssesment' ),
                    ]
                ]);

                $this -> data['exeFreshData'] = generate_data_assoc_array($this -> data['exeFreshData'], 'type_id');

                // model of target details
                $model = $this -> model('TargetMasterModel');

                //get target
                $targetDetails = $model -> getAllTarget([
                    'where' => 'deleted_at IS NULL AND audit_unit_id = :audit_unit_id AND year_id = :year_id',
                    'params' => [
                        'audit_unit_id' => $this -> data['assesmentData'] -> audit_unit_id,
                        'year_id' => $this -> data['assesmentData'] -> year_id
                    ]
                ]);

                $this -> data['db_target'] = $targetDetails;

                // model of executive summary ( march position )
                $model = $this -> model('ExeSummaryModel');

                // march position array
                $exeSummary = $model -> getAllMarchPosition([
                    'where' => 'deleted_at IS NULL AND year_id = :year_id AND audit_unit_id = :audit_unit_id',
                    'params' => [
                        'year_id' => $this -> data['assesmentData'] -> year_id,
                        'audit_unit_id' => $this -> data['assesmentData'] -> audit_unit_id,
                    ]
                ]);

                $this -> data['db_march_position'] = generate_data_assoc_array($exeSummary, 'gl_type_id', 'march_position');

                // ----------------get GL Type Array----------------------
                $this -> data['gl_type_bfp'] = BRANCH_FINANCIAL_POSITION['deposits'] + BRANCH_FINANCIAL_POSITION['advances'] + BRANCH_FINANCIAL_POSITION['npa']+ BRANCH_FINANCIAL_POSITION['other'];
                $this -> data['gl_type_bfa'] = BRANCH_FRESH_ACCOUNTS['deposits'] + BRANCH_FRESH_ACCOUNTS['advances'] + BRANCH_FRESH_ACCOUNTS['npa'];
            }
        });
    }

    private function auditReportFindData($compliance = false, $onlyAssesment = false, $reportBranch = false, $auditId = 0)
    {
        if($reportBranch)
        {
            $filter = [
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $auditId ],
            ];

            $this -> data['audit_unit_data'] = $this -> getAllAuditUnits($filter, false, true);
        }
        else
            $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, true);

        //need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';

        $this -> data['rpt_compliance'] = $compliance;
        $this -> data['rpt_onlyAssesment'] = $onlyAssesment;

        // get risk data
        $this -> data['risk_category_data'] = $this -> getCommonData(['risk_category' => true])['risk_category'];

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_array'] = null;
            $this -> data['data_error'] = 'noDataFound';
            
            $reportAuditAssesment = $this -> request -> input('reportAuditAssesment');

            // method call
            $resData = $this -> reportAuditUnitsAjax(1, $this -> data['rpt_compliance']);

            if(array_key_exists('success', $resData))
            {
                // assesment data assign
                $this -> data['audit_assesment_data'] = $resData['data'];

                if( !empty($reportAuditAssesment) && array_key_exists($reportAuditAssesment, $resData['data']) )
                {
                    // find assesments
                    $model = $this -> model('AuditAssesmentModel');

                    $auditAssesmentDetails = $model -> getSingleAuditAssesment([
                        'where' => 'id = :id AND audit_status_id > 1 AND deleted_at IS NULL',
                        'params' => [ 'id' => $reportAuditAssesment ]
                    ]);

                    if(!$this -> data['rpt_onlyAssesment'])
                    {
                        // assesment details found // helper function call
                        $dataArray = find_audit_observations($this, $auditAssesmentDetails, $this -> data['filter_type']);

                        $this -> data['riskCategoryMaster'] = $this -> getAllRiskCategories();

                        // push data if has data
                        if( isset($dataArray['ans_data']) && 
                            !empty($dataArray['ans_data']) )
                            $this -> data['data_array'] = $dataArray;
                    }

                    $this -> data['assesmentData'] = $auditAssesmentDetails;
                    
                    // unset vars
                    unset($dataArray);
                }
                else
                    $this -> data['data_error'] = 'assesmentNotFound';
            }

        });
    }

    // 26. Executive Summary Audit Report
    public function executiveSummaryAuditReport () 
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe('executiveSummaryAuditReport');

        $this -> data['report_type'] = 1;
        
        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);

            $this -> auditReportFindData(false, true, 1, $this -> auditId);
        }
        else
            $this -> auditReportFindData(false,true);
        
        $this -> commonExecutiveSummaryData();

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 27. Executive Summary Compliance Report
    public function executiveSummaryComplianceReport ()
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe('executiveSummaryComplianceReport');

        $this -> data['report_type'] = 2;

        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);

            $this -> auditReportFindData(false, true, 1, $this -> auditId);
        }
        else
            $this -> auditReportFindData(false,true);

        $this -> commonExecutiveSummaryData();

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 28. Assesment Timeline Report
    public function assesmentTimelineReport ()
    {
        $this -> generateMe('assesmentTimelineReport', 2);

        $this -> auditReportFindData(false,true);

        $this -> data['typeArray'] = array(
            1 => 'AUDIT',
            2 => 'COMPLIANCE',
            3 => 'ADMIN'
        ); 

        // get all employee data
        $this -> data['employee_data'] = $this -> getCommonData(['employee_data' => 'DB'])['employee_data'];

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [
                'reportAuditUnit' => 'required',
                'reportAuditAssesment' => 'required',
            ]);     

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                if($this -> request -> input( 'reportAuditAssesment') != '')
                {
                    $select = "SELECT asm.year_id, asm.audit_unit_id, aut.id, aut.type_id, assesment_id, aut.status_id, aut.rejected_cnt, aut.reviewer_emp_id, aut.created_at FROM audit_assesment_master as asm JOIN audit_assesment_timeline as aut ON asm.id = aut.assesment_id WHERE aut.assesment_id = " . $this -> request -> input( 'reportAuditAssesment' ) ." AND aut.deleted_at IS NULL ORDER BY aut.type_id+0 ASC";

                    $model = $this -> model('AuditAssesmentModel');                

                    // echo $select;
                    $this -> data['details_of_audit_data'] = get_all_data_query_builder(2, $model, 'audit_assesment_master', [], 'sql', $select);
                }
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 29. Assement Not Started Yet Report
    public function assementNotStartedYetReport ()
    {
        $this -> generateMe('assementNotStartedYetReport', 2);

        $presentDate = date($GLOBALS['dateSupportArray'][1]);

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, true);

        $this -> data['options_data']  = ([
            'all_branches' => (object)['id' => 'all_branches', 'combined_name' => string_operations('All Branches', 'upper')],
            'all_head_of_dept' => (object)['id' => 'all_head_of_dept', 'combined_name' => string_operations('All Head Of Departments', 'upper')]
        ] + $this -> data['audit_unit_data']);

        $this -> data['query'] = [
            'all_branches' => ' WHERE section_type_id = 1 AND frequency != 0 AND is_active = 1 AND deleted_at IS NULL',
            'all_head_of_dept' => ' WHERE section_type_id != 1 AND frequency != 0 AND is_active = 1 AND deleted_at IS NULL',
        ];

        //post method after form submit
        $this -> request::method("POST", function() 
        {
            $presentDate = date($GLOBALS['dateSupportArray'][1]);

            //check validation
            Validation::validateData($this -> request, [
                'audit_unit_id' => 'required',
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'index', [ 'request' => $this -> request ]);
            } 
            else
            {
                // // Get Data from Audit Unit Master
                $model = $this->model('AuditUnitModel'); 

                if( $this -> request -> input('audit_unit_id') == 'all_branches' || 
                    $this -> request -> input('audit_unit_id') == 'all_head_of_dept' )
                {
                    $query = $this -> data['query'][ $this -> request -> input('audit_unit_id') ];

                    $this -> data['audit_data'] = audit_unit_details_for_not_started($query, $model);
                }
                else
                {
                    $query = ' WHERE id = ' . $this->request->input('audit_unit_id') . ' AND frequency != 0 AND is_active = 1 AND deleted_at IS NULL';

                    $this -> data['audit_data'] = audit_unit_details_for_not_started($query, $model);

                }
                
                $this -> data['not_started_branches'] = audit_assesment_not_started($this -> data['audit_data'], $this -> model('AuditAssesmentModel'));
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 30. Audit Complete Report
    public function auditCompleteReport()
    {
        $this -> generateMe('auditCompleteReport');

        $this -> data['filter_type'] = 'AAP';

        // method call
        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);

            $this -> auditReportFindData(false, false, 1, $this -> auditId);
        }
        else
            $this -> auditReportFindData();

        $this -> data['filter_type'] = 'ARCRP';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 31. Compliance Report
    public function complianceReport()
    {
        $this -> generateMe('complianceReport');

        $this -> data['filter_type'] = 'ACP';

        // method call
        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);

            $this -> auditReportFindData(0,false,1,$this -> auditId);
        }
        else
            $this -> auditReportFindData(0);

        $this -> data['filter_type'] = 'ARCRP';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 32. Compliance Summary Report
    public function complianceSummaryReport()
    {
        $this -> generateMe('complianceSummaryReport');

        $this -> data['filter_type'] = 'ACP';

        // method call
        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);

            $this -> auditReportFindData(1,false,1,$this -> auditId);
        }
        else
            $this -> auditReportFindData(1);

        $this -> data['filter_type'] = 'CRPWC';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 33. Audit Observations Report
    public function auditObservationsReport()
    {
        $this -> generateMe('auditObservationsReport');

        $this -> data['filter_type'] = 'REARP';

        // method call
        $this -> auditReportFindData();

        $this -> data['filter_type'] = 'COMRP';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    private function reportAuditUnitData($extra = [])
    {
        $order = isset($extra['order']) ? $extra['order'] : "section_type_id+0, name";

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits([
            'where' => 'section_type_id = 1 AND is_active = 1 AND deleted_at IS NULL ORDER BY ' . $order,
            'params' => [],
        ], false, true);

        $this -> data['ho_audit_unit_data'] = $this -> getAllAuditUnits([
            'where' => 'section_type_id != 1 AND is_active = 1 AND deleted_at IS NULL ORDER BY ' . $order,
            'params' => [],
        ], false, true);
    }

    private function generateBroaderAreaFilterArray($removeKeys = null)
    {
        $this -> data['search_type_array'] = array(
            1 => ["title" => "All Branches"],
            2 => ["title" => "All Head Of Departments"],
            3 => ["title" => "Single Branch (Assessment Wise)"],
            4 => ["title" => "Single Department (Assessment Wise)"],
            5 => ["title" => "Single Branch Wise"],
            6 => ["title" => "Single Head Of Department Wise"],
        );

        if(is_array($removeKeys))
        {
            // remove keys
            foreach($removeKeys as $cKey)
                unset($this -> data['search_type_array'][ $cKey ]);
        }
    }

    // private method for assign assesment ajax data 14.08.2024
    private function assignAssesmentAjaxData($returnRes = false, $compliance = false) {

        // find assesmend data
        if( in_array($this -> request -> input('selectSearchTypeFilter'), [2,3]) );
        {
            // method call
            $resData = $this -> reportAuditUnitsAjax($returnRes, $compliance);

            if(array_key_exists('success', $resData))
            {
                // assesment data assign
                $this -> data['audit_assesment_data'] = $resData['data'];
            }
        }
    }

    private function broaderAreaWiseScoringReportValidation($recompliance_status = 0, $extra = [])
    {
        $res_array = [ 'err_ids' => [] ];

        if( !$this -> request -> has('selectSearchTypeFilter') || 
            !array_key_exists($this -> request -> input('selectSearchTypeFilter'), $this -> data['search_type_array']) )
        {
            $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('filterError'));
            $this -> request -> setInputCustom( 'error', 1);
        }
        else
        {
            // validate other data
            switch( $this -> request -> input('selectSearchTypeFilter') )
            {
                case '1': {
                    
                    // method call
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array);
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'branch');                    

                    if($recompliance_status)
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                        $res_array = $this -> findAllBranchOrHOAssessment($res_array, 1, 1, $extra);
                    }
                    else
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                        $res_array = $this -> findAllBranchOrHOAssessment($res_array, 1, 0, $extra);
                    }                    

                    break;
                }

                case '2': {

                    // method call
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array);
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'head_of_dept');

                    if($recompliance_status)
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                            $res_array = $this -> findAllBranchOrHOAssessment($res_array, 2, 1, $extra);
                    }
                    else
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call                    
                            $res_array = $this -> findAllBranchOrHOAssessment($res_array, 2, 0, $extra);
                    }

                    break;
                }

                case '3': {

                    // method call
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'branch');
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'assessment_id');

                    if($recompliance_status)
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                        $res_array = $this -> findSingleBranchOrHOAssessment($res_array, 3, 1, $extra);
                    }
                    else
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                            $res_array = $this -> findSingleBranchOrHOAssessment($res_array, 3, 0, $extra);
                    }
                    break;
                }

                case '4': {

                    // method call
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'head_of_dept');
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'assessment_id');

                    if($recompliance_status)
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                        $res_array = $this -> findSingleBranchOrHOAssessment($res_array, 4, 1, $extra);
                    }
                    else
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                            $res_array = $this -> findSingleBranchOrHOAssessment($res_array, 4, 0, $extra);
                    }

                    break;
                }

                case '5': {

                    // method call
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array);
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'branch');

                    if($recompliance_status)
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                        $res_array = $this -> findAllBranchOrHOAssessment($res_array, 5, 1, $extra);
                    }
                    else
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                            $res_array = $this -> findAllBranchOrHOAssessment($res_array, 5, 0, $extra);
                    }

                    break;
                }

                case '6': {

                    // method call
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array);
                    $res_array = $this -> broaderAreaWiseScoringReportValidateData($res_array, 'head_of_dept');

                    if($recompliance_status)
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                        $res_array = $this -> findAllBranchOrHOAssessment($res_array, 6, 1, $extra);
                    }
                    else
                    {
                        if(!sizeof($res_array['err_ids']) > 0) //function call
                            $res_array = $this -> findAllBranchOrHOAssessment($res_array, 6, 0, $extra);
                    }

                    break;
                }

                // default: {
                //     $res_array['err_ids']['selectSearchTypeFilter'] = 'Error: Please select valid search type';
                //     break;
                // }
            }
        }

        // add error
        if(sizeof($res_array['err_ids']) > 0) //function call
            $this -> request -> setInputCustom( 'error', 1);

        return $res_array;
    }

    private function broaderAreaWiseScoringReportValidateData($res_array, $type = 'date')
    {
        // $date_regex = "/^\d{4}-\d{2}-\d{2}$/";
        $validationArray = array( 'validation' => [], 'params' => [] );
        $selectSearchTypeFilter = $this -> request -> input( 'selectSearchTypeFilter' );

        switch ($type)
        {
            case 'date': {

                // date validation
                $validationArray = $this -> dateValidation($validationArray);
    
                break;
            }
    
            case 'branch': {
    
                $res_var = (in_array($selectSearchTypeFilter, ['1', '2'])) ? 'selectSearchTypeFilter' : 'select_branch';
    
                if(!is_array($this -> data['audit_unit_data']) || 
                  ( is_array($this -> data['audit_unit_data']) && !sizeof($this -> data['audit_unit_data']) > 0) )
                    $this -> request -> setInputCustom( 'reportAuditUnit_err', Notifications::getNoti('noDataFound'));
    
                if( $res_var != 'selectSearchTypeFilter' && 
                    is_array($this -> data['audit_unit_data']) && 
                    !array_key_exists($this -> request -> input('reportAuditUnit'), $this -> data['audit_unit_data']))
                    $this -> request -> setInputCustom( 'reportAuditUnit_err', Notifications::getNoti('audit_id'));
                    
                if( $this -> request -> has('reportAuditUnit_err') )
                    $this -> request -> setInputCustom( 'error', 1);
    
                break;
            }
    
            case 'head_of_dept': {
                
                $res_var = (in_array($selectSearchTypeFilter, ['1', '2'])) ? 'selectSearchTypeFilter' : 'select_branch_ho';
    
                if(!is_array($this -> data['ho_audit_unit_data']) || 
                  ( is_array($this -> data['ho_audit_unit_data']) && !sizeof($this -> data['ho_audit_unit_data']) > 0) )
                    $this -> request -> setInputCustom( 'reportHOAuditUnit_err', Notifications::getNoti('noDataFound'));
    
                if( $res_var != 'selectSearchTypeFilter' && 
                    is_array($this -> data['ho_audit_unit_data']) && 
                    !array_key_exists($this -> request -> input('reportHOAuditUnit'), $this -> data['ho_audit_unit_data']))
                    $this -> request -> setInputCustom( 'reportHOAuditUnit_err', Notifications::getNoti('audit_id'));

                if( $this -> request -> has('reportHOAuditUnit_err') )
                    $this -> request -> setInputCustom( 'error', 1);
    
                break;
            }
    
            case 'assessment_id': {
    
                $validationArray['validation']['reportAuditAssesment'] = 'required';
                
                /*if( $this -> request -> has('reportAuditAssesment') && ( 
                  ( $this -> request -> has('reportAuditUnit') && !empty( $this -> request -> input('reportAuditUnit')) ) || 
                    $this -> request -> has('reportHOAuditUnit') && !empty( $this -> request -> input('reportHOAuditUnit')) ))
                { 
                    //find valid assessment                     
                }*/
    
                break;
            }
        }

        if(sizeof($validationArray['validation']) > 0 && !$this -> request -> has('error') )
        {
            // validation method call
            Validation::validateData($this -> request, $validationArray['validation']);
        }
    
        return $res_array;
    }

    // function for type 1 and 2 query data
    private function findSingleBranchOrHOAssessment($res_array, $search_type = 3, $recompliance_status = 0, $extra = [])
    {
        //check assesment pending
        $audit_id = ($search_type == 3) ? 
                        $this -> request -> input('reportAuditUnit') : 
                        $this -> request -> input('reportHOAuditUnit');

        // check any pending assesment in current period
        $model = $this -> model('AuditAssesmentModel');

        if($recompliance_status)
        {
            if(!isset($extra['select_cols']))
                $extra['select_cols'] = 'id, year_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id, audit_start_date, audit_end_date';

            $findAssesmentData = DBCommonFunc::getAllAuditAssesment($model, [
                'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id = "'. ASSESMENT_TIMELINE_ARRAY[6]['status_id'] .'" AND id = :id AND deleted_at IS NULL',
                'params' => [
                    'id' => $this -> request -> input('reportAuditAssesment'),
                    'audit_unit_id' => $audit_id
                ]
            ], $extra['select_cols']);

        }
        else
        {
            if(!isset($extra['select_cols']))
                $extra['select_cols'] = 'id, year_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id';

            $findAssesmentData = DBCommonFunc::getAllAuditAssesment($model, [
                'where' => 'audit_unit_id = :audit_unit_id AND audit_status_id > "'. ASSESMENT_TIMELINE_ARRAY[3]['status_id'] .'" AND id = :id AND deleted_at IS NULL',
                'params' => [
                    'id' => $this -> request -> input('reportAuditAssesment'),
                    'audit_unit_id' => $audit_id
                ]
            ], $extra['select_cols']);
        }

        if(!is_array($findAssesmentData) || (is_array($findAssesmentData) && !sizeof($findAssesmentData) > 0))
            $res_array['err_ids']['selectSearchTypeFilter'] = Notifications::getNoti('noAssesmentFound');
        else
            $res_array['data'] = generate_data_assoc_array($findAssesmentData, 'id');

        return $res_array;
    }

    // 34. Risk Weightage Report
    public function riskWeightageReport()
    {
        $this -> generateMe('riskWeightageReport');

        // method call get audit unit OR HO
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        // need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray([1,2]);

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_error'] = 'noDataFound';
            $this -> data['audit_unit_ids_array'] = array();

            // private method call
            $this -> assignAssesmentAjaxData(1);

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();
            $this -> data['data_array'] = array();

            // print_r($resData);
            // exit;

            $fyId = 0;
            $commonResData = null;

            if( !$this -> request -> input( 'error' ) > 0 && 
                isset($resData['data']) && 
                sizeof($resData['data']) > 0 )
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // push data for view
                $this -> data['select_search_type'] = $this -> request -> input('selectSearchTypeFilter');
                $this -> data['period'] = null;

                if( in_array($this -> data['select_search_type'], [3, 4]) )
                    $this -> data['period'] = $resData['data'][ array_keys($resData['data'])[0] ] -> combined_period;

                else if( in_array($this -> data['select_search_type'], [5, 6]))
                    $this -> data['period'] = $this -> request -> input('startDate') . ' - ' . $this -> request -> input('endDate') .'</p>' . "\n";

                // method call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }

                if(!$this -> request -> input( 'error' ) > 0)
                {
                    // has data 
                    foreach ($resData['data'] as $cAssesId => $cAssesDetails) {
                        $this -> data['audit_unit_id_array'] = [];

                        if(!isset($this -> data['audit_unit_id']))
                            $this -> data['audit_unit_id'] = $cAssesDetails -> audit_unit_id;
    
                        if(is_array($commonResData['riskCategory']) && sizeof($commonResData['riskCategory']) > 0)
                        {
                            foreach ($commonResData['riskCategory'] as $cRiskCatId => $cRiskCatDetails) {
                                $this -> data['audit_unit_id_array'][ $cRiskCatId ] = 0;
                            }
    
                            $this -> data['audit_unit_id_array']['tot'] = 0;
                        }

                        // single audit unit data 12.08.2024 Kunal
                        break;
                    }

                    if(!isset($this -> data['audit_unit_id_array']))
                    {
                        $assesErr = in_array($this -> request -> input('selectSearchTypeFilter'), [3,5]) ? 'auditUnitNoDataFound' : 'HOAuditUnitNoDataFound';
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($assesErr));
                        $this -> request -> setInputCustom( 'error', 1);
                    }
                }
            }

            if(!$this -> request -> input( 'error' ) > 0)
            {
                // define categories // method call
                $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                    'res_data' => $resData, 
                    'common_data' => $commonResData 
                ]);

                $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                if(isset($this -> data['audit_unit_data']))
                    $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                if(isset($this -> data['ho_audit_unit_data']))
                    $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                // assign sorted broader area keys
                $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'];

                // function call // overwrite data
                $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                if(!is_array($res) || empty($res))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                    $this -> request -> setInputCustom( 'error', 1);
                    unset($this -> data['sortedBroaderAreaKeys']);
                }
                else
                {
                    // add response
                    $this -> data['data_array'] = $res[ array_keys($res)[0] ];
                }
            }

            if(!is_array($res) || empty($res))
            {
                // error
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                $this -> request -> setInputCustom( 'error', 1);
                unset($this -> data['sortedBroaderAreaKeys']);
            }

            // validation check
            if($this -> request -> input( 'error' ) > 0)
                Validation::flashErrorMsg();
            else
            {
                // re assign
                $this -> data['risk_category'] = $commonResData['riskCategory'];
                unset($resData, $res);

                $sortedRiskTypeWeightArray = array();
                $cAuditDetails = $this -> data['audit_unit_id'];
                $noOfAssessment = sizeof($this -> data['data_array']['no_of_audits']);

                //check branch
                if( is_array($this -> data['audit_unit_data']) && 
                    array_key_exists($cAuditDetails, $this -> data['audit_unit_data']) )
                    $cAuditDetails = $this -> data['audit_unit_data'][ $cAuditDetails ];

                elseif( is_array($this -> data['ho_audit_unit_data']) &&
                        array_key_exists($cAuditDetails, $this -> data['ho_audit_unit_data'] ) )
                    $cAuditDetails = $this -> data['ho_audit_unit_data'][ $cAuditDetails ];

                // $this -> data['data_array'] = $this -> data['data_array']['res_data']['data'];
                $totWeightedScore = 0; 
                $totalQualQuan = 0;

                // print_r($this -> data['data_array']);
                // exit;

                // foreach($this -> data['data_array'] as $cAssesId => $cAssesDetails):

                foreach($this -> data['sortedBroaderAreaKeys'] as $cGenKey => $cGenKeyStr):

                    if(array_key_exists($cGenKey, $this -> data['data_array']))
                    {
                        foreach ($this -> data['data_array'][ $cGenKey ]['borader_area'] as $cBroaderAreaId => $cBroaderAreaDetails)
                        {
                            foreach ($cBroaderAreaDetails['category'] as $cRiskId => $cRiskDetails)
                            {
                                //insert gen key            
                                if(!array_key_exists($cGenKey, $sortedRiskTypeWeightArray))
                                    $sortedRiskTypeWeightArray[ $cGenKey ] = array();

                                //check risk cat id
                                if(!array_key_exists($cRiskId, $sortedRiskTypeWeightArray[ $cGenKey ]))
                                    $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ] = array(
                                        'title' => $cRiskDetails['title'],
                                        'no_of_audit_conduct' => $noOfAssessment,
                                        'risk_weight' =>  $cRiskDetails['risk_weight'],
                                        'total_qual_quan' => 0, 'tot_avg_score' => 0,
                                        'avg_tot_score_per_audit' => 0,
                                        'weighted_score' => 0
                                    );

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['total_qual_quan'] += $cRiskDetails['total_qual_quan'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['tot_avg_score'] += $cRiskDetails['tot_avg_score'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['avg_tot_score_per_audit'] += $cRiskDetails['avg_tot_score_per_audit'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['weighted_score'] += $cRiskDetails['weighted_score'];

                                $totalQualQuan += $cRiskDetails['total_qual_quan'];                          

                                $totWeightedScore  += $cRiskDetails['weighted_score'];  
                            }
                        }
                    }
                    
                endforeach;

                // echo $totWeightedScore;
                // print_r($sortedRiskTypeWeightArray);
                // exit;

                unset(
                    $this -> data['audit_unit_ids_array'],
                    $this -> data['audit_section_data']
                );

                // re assign data
                $this -> data['audit_details'] = $cAuditDetails;
                $this -> data['no_of_assessment'] = $noOfAssessment;
                $this -> data['data_array'] = $sortedRiskTypeWeightArray;
                $this -> data['tot_weighted_score'] = $totWeightedScore;
                // $this -> data['branch_rating'] = $this -> getBranchRatingData($fyId);
            }
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 35. Broader Areawise Scoring Report
    public function broaderAreaWiseScoringReport()
    {
        // change broader area function for single assesment 21.06.2024
        $this -> generateMe('broaderAreaWiseScoringReport');

        // method call
        $this -> reportAuditUnitData();

        // set page
        $this -> data['page'] = 'A4L';
        $this -> data['need_calender'] = true;

        //need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray();

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_error'] = 'noDataFound';
            $this -> data['data_array'] = array();

            // find assesmend data
            if( in_array($this -> request -> input('selectSearchTypeFilter'), [2,3]) );
            {
                // method call
                $resData = $this -> reportAuditUnitsAjax(1);

                if(array_key_exists('success', $resData))
                {
                    // assesment data assign
                    $this -> data['audit_assesment_data'] = $resData['data'];
                }
            }

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();

            if(!$this -> request -> input( 'error' ) > 0 && isset($resData['data']) && sizeof($resData['data']) > 0)
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // method call // helper function call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                // has error
                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }
                else
                {
                    // no errors // helper funtion call
                    $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                        'res_data' => $resData, 
                        'common_data' => $commonResData 
                    ]);

                    $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                    if(isset($this -> data['audit_unit_data']))
                        $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                    if(isset($this -> data['ho_audit_unit_data']))
                        $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                    // assign sorted broader area keys
                    $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'] ;

                    // function call // overwrite data
                    $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                    // print_r($res);
                    // exit;

                    if(!is_array($res) || empty($res))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                        $this -> request -> setInputCustom( 'error', 1);
                        unset($this -> data['sortedBroaderAreaKeys']);
                    }
                    else
                    {
                        // add response
                        $this -> data['data_array'] = $res;
                    }
                }
            }

            if( empty($this -> data['data_array']) )
                unset($this -> data['data_array']);

            // validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
                // unset($this -> data['data_array']);
            }

        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // function for type 1 and 2 query data
    private function findAllBranchOrHOAssessment($res_array, $search_type = 1, $recompliance_status = 0, $extra = [])
    {
        if( ($search_type == 1 && (!is_array($this -> data['audit_unit_data']) || !sizeof($this -> data['audit_unit_data']) > 0)) || 
            ($search_type == 2 && (!is_array($this -> data['ho_audit_unit_data']) || !sizeof($this -> data['ho_audit_unit_data']) > 0)) )
        {
            $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('noAuditUnitsError'));
            $this -> request -> setInputCustom( 'error', 1);
        }
        else
        {    
            // check $comFlex update 07-03-2024
            $comFlex = $this -> request -> has('rmv_pending_assesments') ? 1 : 0;
    
            //check pending records
            if(in_array($search_type, [1, 2]))
                $audit_ids = ($search_type == 1) ? 
                                implode(',', array_keys($this -> data['audit_unit_data'])) : 
                                implode(',', array_keys($this -> data['ho_audit_unit_data']));
            else
                $audit_ids = ($search_type == 5) ? 
                                $this -> request -> input('reportAuditUnit') : 
                                $this -> request -> input('reportHOAuditUnit');

            // check any pending assesment in current period
            $model = $this -> model('AuditAssesmentModel');

            if($recompliance_status)
            {
                $pendingAssesment = $model -> getSingleAuditAssesment([
                    'where' => 'audit_status_id = '. ASSESMENT_TIMELINE_ARRAY[6]['status_id'] .' AND audit_unit_id IN ('. $audit_ids .') AND assesment_period_from >= :assesment_period_from AND assesment_period_to <= :assesment_period_to AND deleted_at IS NULL',
                    'params' => [
                        'assesment_period_from' => $this -> request -> input('startDate'),
                        'assesment_period_to' => $this -> request -> input('endDate')
                    ]
                ]);
            }
            else
            {
                $pendingAssesment = $model -> getSingleAuditAssesment([
                    'where' => 'audit_status_id < '. ASSESMENT_TIMELINE_ARRAY[4]['status_id'] .' AND audit_unit_id IN ('. $audit_ids .') AND assesment_period_from >= :assesment_period_from AND assesment_period_to <= :assesment_period_to AND deleted_at IS NULL',
                    'params' => [
                        'assesment_period_from' => $this -> request -> input('startDate'),
                        'assesment_period_to' => $this -> request -> input('endDate')
                    ]
                ]);    
            }      

            if(!$comFlex && is_object($pendingAssesment))
            {
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('pendingAudit'));
                $this -> request -> setInputCustom( 'error', 1);
            }
            else
            {
                if($recompliance_status)
                {
                    if(!isset($extra['select_cols']))
                        $extra['select_cols'] = 'id, year_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id';

                    $findAssesmentData = DBCommonFunc::getAllAuditAssesment($model, [
                        'where' => 'audit_status_id = "'. ASSESMENT_TIMELINE_ARRAY[6]['status_id'] .'" AND audit_unit_id IN ('. $audit_ids .') AND assesment_period_from >= :assesment_period_from AND assesment_period_to <= :assesment_period_to AND deleted_at IS NULL ORDER BY audit_unit_id ASC',
                        'params' => [
                            'assesment_period_from' => $this -> request -> input('startDate'),
                            'assesment_period_to' => $this -> request -> input('endDate')
                        ]
                    ], $extra['select_cols']);
                }
                else
                {
                    if(!isset($extra['select_cols']))
                        $extra['select_cols'] = 'id, year_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id, audit_start_date, audit_end_date';

                    $findAssesmentData = DBCommonFunc::getAllAuditAssesment($model, [
                        'where' => 'audit_status_id > "'. ASSESMENT_TIMELINE_ARRAY[3]['status_id'] .'" AND audit_unit_id IN ('. $audit_ids .') AND assesment_period_from >= :assesment_period_from AND assesment_period_to <= :assesment_period_to AND deleted_at IS NULL ORDER BY audit_unit_id ASC',
                        'params' => [
                            'assesment_period_from' => $this -> request -> input('startDate'),
                            'assesment_period_to' => $this -> request -> input('endDate')
                        ]
                    ], $extra['select_cols']);
                }
    
                if(!is_array($findAssesmentData) || is_array($findAssesmentData) && !sizeof($findAssesmentData) > 0)
                    $res_array['err_ids']['selectSearchTypeFilter'] = Notifications::getNoti('noAssesmentFound');
                else
                    $res_array['data'] = generate_data_assoc_array($findAssesmentData, 'id');

                unset($findAssesmentData);
            }

            unset($pendingAssesment);
        }
    
        return $res_array;
    }

    private function getBranchRatingData($fyId, $auditType = 1)
    {
        if( $fyId == '' )
            return null;

        // find branch rating
        $model = $this -> model('BranchRatingModel');

        $branchRatingData = $model -> getAllBranchRating([
            'where' => 'year_id = :year_id AND audit_type_id = :audit_type_id AND deleted_at IS NULL',
            'params' => [
                'year_id' => $fyId,
                'audit_type_id' => $auditType
            ]
        ]);

        if( !is_array($branchRatingData) || ( is_array($branchRatingData) && !sizeof($branchRatingData) > 0 ))
            return null;

        $resData = [];

        foreach( $branchRatingData as $cBRDetails)
        {
            // audit unit id
            if(!array_key_exists($cBRDetails -> audit_unit_id, $resData))
                $resData[ $cBRDetails -> audit_unit_id ] = [];

            $cRisk = $cBRDetails -> range_from . '-' . $cBRDetails -> range_to;

            // push keys
            if(!array_key_exists($cRisk, $resData[ $cBRDetails -> audit_unit_id ]))
                $resData[ $cBRDetails -> audit_unit_id ][ $cRisk ] = string_operations( (array_key_exists($cBRDetails -> risk_type_id, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cBRDetails -> risk_type_id ]['title'] : ERROR_VARS['notFound']), 'upper');
        }

        return $resData;
    }

    // 36. Risk Wise Audit Units Report
    public function riskWiseAuditUnitsReport()
    {
        $this -> generateMe('riskWiseAuditUnitsReport');

        // method call get audit unit OR HO
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        // need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray([3,4,5,6]);

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_array'] = null;
            $this -> data['data_error'] = 'noDataFound';
            $this -> data['audit_unit_ids_array'] = array();

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();
            $this -> data['data_array'] = array();

            // print_r($resData);
            // exit;

            $fyId = 0;
            $commonResData = null;

            if( !$this -> request -> input( 'error' ) > 0 && 
                isset($resData['data']) && 
                sizeof($resData['data']) > 0 )
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // method call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }

                if(!$this -> request -> input( 'error' ) > 0)
                {
                    // has data 
                    foreach ($resData['data'] as $cAssesId => $cAssesDetails) {
                        if(!array_key_exists($cAssesDetails -> audit_unit_id, $this -> data['audit_unit_ids_array']))
                            $this -> data['audit_unit_ids_array'][ $cAssesDetails -> audit_unit_id ] = [];
    
                        if(is_array($commonResData['riskCategory']) && sizeof($commonResData['riskCategory']) > 0)
                        {
                            foreach ($commonResData['riskCategory'] as $cRiskCatId => $cRiskCatDetails) {
                                $this -> data['audit_unit_ids_array'][ $cAssesDetails -> audit_unit_id ][ $cRiskCatId ] = 0;
                            }
    
                            $this -> data['audit_unit_ids_array'][ $cAssesDetails -> audit_unit_id ]['tot'] = 0;
                        }
                    }

                    $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                        'res_data' => $resData, 
                        'common_data' => $commonResData 
                    ]);

                    $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                    if(isset($this -> data['audit_unit_data']))
                        $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                    if(isset($this -> data['ho_audit_unit_data']))
                        $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                    // assign sorted broader area keys
                    $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'] ;

                    // function call // overwrite data
                    $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                    if(!is_array($res) || empty($res))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                        $this -> request -> setInputCustom( 'error', 1);
                        unset($this -> data['sortedBroaderAreaKeys']);
                    }
                    else
                    {
                        // add response
                        $this -> data['data_array'] = $res;
                    }
                }
            }

            if( empty($this -> data['data_array']) )
                unset($this -> data['data_array']);

            // validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
                // unset($this -> data['data_array']);
            }
            else
            {
                // re assign
                $this -> data['risk_category'] = $commonResData['riskCategory'];

                unset($resData, $res);

                $totalAuditUnitWiseCount = array('tot' => 0);
                            
                foreach($this -> data['data_array'] as $cAuditUnitId => $cAuditUnitDetails)
                {
                    foreach($cAuditUnitDetails as $cGenKey => $cGenData):
                
                    if(array_key_exists($cGenKey, $this -> data['sortedBroaderAreaKeys']))
                    {
                        foreach ($cGenData['borader_area'] as $cBroaderAreaId => $cBroaderAreaDetails)
                        {            
                            // print_r($this -> data['audit_unit_ids_array']);
                            // exit;

                            foreach ($cBroaderAreaDetails['category'] as $cRiskId => $cRiskDetails)
                            {
                                $this -> data['audit_unit_ids_array'][ $cAuditUnitId ][ $cRiskId ] = get_decimal((
                                    $this -> data['audit_unit_ids_array'][ $cAuditUnitId ][ $cRiskId ] + $cRiskDetails['weighted_score']), 2);

                                //add in total
                                $this -> data['audit_unit_ids_array'][ $cAuditUnitId ]['tot'] = get_decimal(($this -> data['audit_unit_ids_array'][ $cAuditUnitId ]['tot'] + $cRiskDetails['weighted_score']), 2);

                                if(!array_key_exists($cRiskId, $totalAuditUnitWiseCount))
                                    $totalAuditUnitWiseCount[ $cRiskId ] = 0;

                                $totalAuditUnitWiseCount[ $cRiskId ] = get_decimal(($totalAuditUnitWiseCount[ $cRiskId ] + $cRiskDetails['weighted_score']), 2);

                                $totalAuditUnitWiseCount['tot'] = get_decimal(($totalAuditUnitWiseCount['tot'] + $cRiskDetails['weighted_score']), 2);
                            }
                        }
                    }

                    endforeach;
                }

                // re assign data
                $this -> data['data_array'] = $this -> data['audit_unit_ids_array'];
                $this -> data['select_search_type'] = $this -> request -> input('selectSearchTypeFilter');
                $this -> data['total_audit_unit_wise_count'] = $totalAuditUnitWiseCount;
                $this -> data['branch_rating'] = $this -> getBranchRatingData($fyId);
            }
        });

        // set page
        $this -> data['page'] = 'A5L';

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    private function generateObsevationCount($auditAnswerData, $detailsOfAuditData)
    {
        if(is_array($auditAnswerData) && sizeof($auditAnswerData) > 0)
        {
            foreach ($auditAnswerData as $cAnswerId => $cAnswerDetails)
            {
                if( !isset($detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_observ) )
                {
                    // push keys
                    $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_observ = 0;
                    // $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> complete_observ = 0;
                    $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> pending_observ = 0;
                }

                // default increment because already filtered data
                $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_observ++;

                if(in_array($detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id, [
                    ASSESMENT_TIMELINE_ARRAY[2]['status_id'], ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                ]))
                {
                    // check audit stage // reviewer
                    if( $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[2]['status_id'] && !in_array($cAnswerDetails -> audit_status_id, [2,1]))
                        $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> pending_observ++;

                    // check audit stage // re-audit
                    elseif( $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[3]['status_id'] && !in_array($cAnswerDetails -> audit_status_id, [2,1]) )
                        $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> pending_observ++;
                }
                else if(in_array($detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id, [
                    ASSESMENT_TIMELINE_ARRAY[4]['status_id'], ASSESMENT_TIMELINE_ARRAY[5]['status_id'], ASSESMENT_TIMELINE_ARRAY[6]['status_id']
                ]))
                {
                    // check audit stage // compliance
                    if( $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[4]['status_id'] )
                        $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> pending_observ++;

                    // check audit stage // review compliance
                    elseif( $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[5]['status_id'] && !in_array($cAnswerDetails -> compliance_status_id, [2,1] ))
                        $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> pending_observ++;

                    // check audit stage // re-compliance
                    elseif( $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[6]['status_id'] && !in_array($cAnswerDetails -> compliance_status_id, [2,1] ))
                        $detailsOfAuditData[ $cAnswerDetails -> assesment_id ] -> pending_observ++;
                }
            }
        }

        return $detailsOfAuditData;
    }

    // 37. Audit Observation Count Report
    public function auditObservationCountReport()
    {
        $this -> generateMe('auditObservationCountReport');

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits([
            'where' => 'is_active = 1 AND deleted_at IS NULL ORDER BY section_type_id+0, name',
            'params' => [],
        ], false, true);

        $this -> data['audit_unit_data']  = ([
            'all_branches' => (object)['id' => 'all_branches', 'combined_name' => string_operations('All Branches', 'upper')],
            'all_head_of_dept' => (object)['id' => 'all_head_of_dept', 'combined_name' => string_operations('All Head Of Departments', 'upper')]
        ] + $this -> data['audit_unit_data']);

        $this -> data['need_calender'] = true;

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_array'] = null;
            $this -> data['data_error'] = 'noDataFound';

            $validationArray = array( 
                'validation' => [ 'reportAuditUnit' => 'required|array_key[audit_unit_data_array, auditUnitNotExists]' ], 
                'params' => [ 'audit_unit_data_array' => $this -> data['audit_unit_data'] ] );
            
            // for date validation
            $validationArray = $this -> dateValidation($validationArray);

            if(!$this -> request -> input('error') > 0)
                Validation::validateData($this -> request, $validationArray['validation'], $validationArray['params']);

            if($this -> request -> input('error') > 0)
                Validation::flashErrorMsg();
            else
            {
                $select = "SELECT asm.*, aum.id audit_unit_id, aum.audit_unit_code, aum.name, aum.section_type_id, CONCAT(aum.name, ' - ( BR. ' , aum.audit_unit_code, ' )') AS combined_name FROM audit_assesment_master as asm JOIN audit_unit_master as aum ON asm.audit_unit_id = aum.id";

                // append search type 
                if($this -> request -> input('reportAuditUnit') == 'all_branches')
                    $select .= " WHERE aum.section_type_id = 1";
                else if($this -> request -> input('reportAuditUnit') == 'all_head_of_dept')
                    $select .= " WHERE aum.section_type_id != 1";
                else 
                    $select .= " WHERE asm.audit_unit_id = '". $this -> request -> input('reportAuditUnit') ."'";

                $select .= " AND asm.assesment_period_from >= '" . $this -> request -> input('startDate') . "' AND asm.assesment_period_to <= '" . $this -> request -> input('endDate') . "' AND asm.audit_status_id > 1 ORDER BY aum.audit_unit_code+0 ASC, asm.audit_unit_id ASC";

                $model = $this -> model('AuditAssesmentModel');

                $detailsOfAuditData = get_all_data_query_builder(2, $model, 'audit_assesment_master', [], 'sql', $select);
                $detailsOfAuditData = generate_data_assoc_array($detailsOfAuditData, 'id');

                if(is_array($detailsOfAuditData) && sizeof($detailsOfAuditData) > 0)
                {
                    // has data // fetch counts

                    // select without annexure
                    $select = "SELECT ans.id answer_id, ans.assesment_id, ans.audit_commpliance, ans.audit_status_id, ans.compliance_status_id FROM answers_data ans JOIN question_master qm ON ans.question_id = qm.id WHERE ans.assesment_id IN (". implode(',', array_keys($detailsOfAuditData)) .") AND qm.option_id != 4 AND ans.is_compliance = '1' AND ans.deleted_at IS NULL";

                    $auditAnswerData = get_all_data_query_builder(2, $model, 'answers_data', [], 'sql', $select);

                    //function call
                    $detailsOfAuditData = $this -> generateObsevationCount($auditAnswerData, $detailsOfAuditData);
                    $auditAnswerData = generate_data_assoc_array($auditAnswerData, 'answer_id');

                    if(is_array($auditAnswerData) && sizeof($auditAnswerData) > 0)
                    {
                        // select annexure compliance
                        $select = "SELECT ax.id, ax.answer_id, ax.assesment_id, ax.audit_status_id, ax.compliance_status_id FROM answers_data_annexure ax JOIN answers_data ans ON ax.answer_id = ans.id JOIN question_master qm ON ans.question_id = qm.id WHERE ax.assesment_id IN (". implode(',', array_keys($detailsOfAuditData)) .") AND qm.option_id = 4 AND ( ax.business_risk IN (1,2,3) OR ax.control_risk in (1,2,3) ) AND ax.deleted_at IS NULL";

                        $auditAnswerData = get_all_data_query_builder(2, $model, 'answers_data_annexure', [], 'sql', $select);
                        $detailsOfAuditData = $this -> generateObsevationCount($auditAnswerData, $detailsOfAuditData);
                    }

                    unset($auditAnswerData);
                }
  
                $this -> data['data_array'] = $detailsOfAuditData;

                unset($detailsOfAuditData);
            }
        });

        // set page
        $this -> data['page'] = 'A4L';

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 38. Pending Compliance Detailed Report
    public function pendingComplianceDetailReport ()
    {
        $this -> generateMe('pendingComplianceDetailReport');

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits([
            'where' => 'is_active = 1 AND deleted_at IS NULL ORDER BY section_type_id+0, name',
            'params' => [],
        ], false, true);

        // method call
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        $this -> data['filter_type'] = 'PCDR';

        //need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray();

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_array'] = null;
            $this -> data['data_error'] = 'noDataFound';

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();
            $this -> data['data_array'] = array();    
            
            if( is_array($resData) && 
                isset($resData['err_ids']) && 
                sizeof($resData['err_ids']) > 0 )
            {
                foreach($resData['err_ids'] as $cInputKey => $cInputMsg) {
                    $this -> request -> setInputCustom( $cInputKey . '_err', $cInputMsg);
                }

                $this -> request -> setInputCustom( 'error', 1);
            }
            
            if(!$this -> request -> input( 'error' ) > 0 && isset($resData['data']) && sizeof($resData['data']) > 0)
            {
                $this -> data['data_array'] = null;
                $this -> data['data_error'] = 'noDataFound';

                if(isset($resData['data']) && sizeof($resData['data']) > 1)
                {
                    $reportAuditAssesmentData = [];

                    $reportAuditAssesment = array_keys($resData['data']);

                    for($i = 0; $i < sizeof($reportAuditAssesment); $i++ )
                    {
                        $tempData = !empty($reportAuditAssesment[$i]) ? explode(',', $reportAuditAssesment[$i]) : [];

                        $reportAuditAssesmentData = array_merge($reportAuditAssesmentData, $tempData);                
                    }

                    $reportAuditAssesment = implode(',', array_unique($reportAuditAssesmentData));
                }
                else if(isset($resData['data']) && sizeof($resData['data']) == 1)
                { 
                    $reportAuditAssesment = $resData['data'];
                }

                $this -> data['audit_assesment_data'] = $resData['data'];

                if( !empty($reportAuditAssesment))
                {
                    // find assesments
                    $model = $this -> model('AuditAssesmentModel');

                    if(isset($resData['data']) && sizeof($resData['data']) > 1)
                    {
                        $auditAssesmentDetails = $model -> getAllAuditAssesment([
                            'where' => 'id IN (' . $reportAuditAssesment . ') AND audit_status_id = 6 AND deleted_at IS NULL'
                        ]);

                        foreach($auditAssesmentDetails as $cAssesId => $cAssesDetails)
                        {
                            $dataArray = find_audit_observations($this, $cAssesDetails, $this -> data['filter_type']);

                            $this -> data['riskCategoryMaster'] = $this -> getAllRiskCategories();

                            $this -> data['data_array'][$cAssesId] = $dataArray;

                            $this -> data['assesmentData'] = $auditAssesmentDetails;

                            $this -> data['assesmentType'] = 2;
                        }
                        
                        // unset vars
                        unset($dataArray);
                    }
                    else
                    {
                        $auditAssesmentDetails = $model -> getSingleAuditAssesment([
                            'where' => 'id = :id AND audit_status_id = 6 AND deleted_at IS NULL',
                            'params' => [ 'id' => array_keys($reportAuditAssesment)[0] ]
                        ]);

                        $dataArray = find_audit_observations($this, $auditAssesmentDetails, $this -> data['filter_type']);

                        $this -> data['riskCategoryMaster'] = $this -> getAllRiskCategories();

                        $this -> data['data_array'] = $dataArray;

                        $this -> data['assesmentData'] = $auditAssesmentDetails;

                        $this -> data['assesmentType'] = 1;
                        
                        // unset vars
                        unset($dataArray);
                    }

                }
                else
                    $this -> data['data_error'] = 'assesmentNotFound'; 
                
            }
            
            if(is_array($this -> data['data_array']) && !sizeof($this -> data['data_array']) > 0 && !$this -> request -> input( 'error' ) > 0)
            {
                // error
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                $this -> request -> setInputCustom( 'error', 1);
            }

            // validation check
            if($this -> request -> input( 'error' ) > 0)
                Validation::flashErrorMsg();

            unset($resData);
            
            if( in_array($this -> request -> input('selectSearchTypeFilter'), [2,3]) );
            {
                // method call
                $resData = $this -> reportAuditUnitsAjax(1, false);

                if(array_key_exists('success', $resData))
                {
                    // assesment data assign
                    $this -> data['audit_assesment_data'] = $resData['data'];
                }
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 39. Audit Committee Board Report - 1 // trend report 27.06.2024
    public function auditCommitteeBoardReport1()
    {
        $this -> generateMe('auditCommitteeBoardReport1');

        // method call
        $this -> generateBroaderAreaFilterArray([3,4,5,6]);

        $this -> data['trend_on'] = [ 'rwt' => 'Risk Wise Trend', 'rswt' => 'Risk Score Wise Trend' ];

        // post method after form submit
        $this -> request::method("POST", function() {

            // get all audit unit // method call
            $this -> reportAuditUnitData(['order' => 'audit_unit_code+0']);

            if($this -> request -> input('selectSearchTypeFilter') == 1 && 
            (!is_array($this -> data['audit_unit_data']) || (is_array($this -> data['audit_unit_data']) && !(sizeof($this -> data['audit_unit_data']) > 0))))
            {
                // for audit unit
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('auditUnitNoDataFound'));
                $this -> request -> setInputCustom( 'error', 1);
            }
            elseif($this -> request -> input('selectSearchTypeFilter') == 2&& 
            (!is_array($this -> data['audit_unit_data']) || (is_array($this -> data['audit_unit_data']) && !(sizeof($this -> data['audit_unit_data']) > 0))))
            {
                // for HO
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('HOAuditUnitNoDataFound'));
                $this -> request -> setInputCustom( 'error', 1);
            }

            if(!($this -> request -> input( 'error' ) > 0))
            {
                // validations
                Validation::validateData($this -> request, [
                    'selectSearchTypeFilter' => 'required|array_key[search_type_array, user_type]',
                    'trend' => 'required|array_key[trend_array, validTrend]',
                    'startMonth' => 'required|regex[yearMonthRegex, yearMonthError]',
                    'endMonth' => 'required|regex[yearMonthRegex, yearMonthError]',
                    'startMonth2' => 'required|regex[yearMonthRegex, yearMonthError]',
                    'endMonth2' => 'required|regex[yearMonthRegex, yearMonthError]',
                ], [
                    'search_type_array' => $this -> data['search_type_array'],
                    'trend_array' => $this -> data['trend_on'],
                ]);
            }

            $extra = [ 'trend' => $this -> request -> input( 'error' ) ];
            $resArray = [];

            if(!($this -> request -> input( 'error' ) > 0))
            {
                $notiObj = new Notifications;

                // check both trend dates within financial year
                $extra['startMonth'] = date( 'Y-m-01', strtotime($this -> request -> input('startMonth')) );
                $extra['endMonth'] = date( 'Y-m-t', strtotime($this -> request -> input('endMonth')) );
                $err = 0;

                // function call
                date_validation_helper($this -> request, [], $notiObj, [ $extra['startMonth'], $extra['endMonth'] ]);

                if($this -> request -> has('period_to_err'))
                {
                    $this -> request -> setInputCustom( 'endMonth_err', $this -> request -> input('period_to_err'));
                    $err++;
                }

                $extra['startMonth2'] = date( 'Y-m-01', strtotime($this -> request -> input('startMonth2')) );
                $extra['endMonth2'] = date( 'Y-m-t', strtotime($this -> request -> input('endMonth2')) );

                // function call
                date_validation_helper($this -> request, [], $notiObj, [ $extra['startMonth2'], $extra['endMonth2'] ]);

                if($this -> request -> has('period_to_err'))
                {
                    $this -> request -> setInputCustom( 'endMonth2_err', $this -> request -> input('period_to_err'));
                    $err++;
                }

                // Check if periods overlap
                if( !($err > 0) && $extra['startMonth'] <= $extra['endMonth2'] && 
                    $extra['startMonth2'] <= $extra['endMonth'] )
                {
                    $this -> request -> setInputCustom( 'endMonth2_err', Notifications::getNoti('overlapTrendDates'));
                    $err++;
                }

                // remove err
                $this -> request -> remove('period_to_err');

                if($err > 0) 
                    $this -> request -> setInputCustom( 'error', 1);
                else
                {
                    $resArray['p1_year'] = getFYOnDate($extra['startMonth']);
                    $resArray['p2_year'] = getFYOnDate($extra['startMonth2']);
                    $resArray['p1_year_obj'] = null; $resArray['p2_year_obj'] = null;
                    $resArray['p1_risk'] = null; $resArray['p2_risk'] = null;
                    $resArray['branch_rating'] = null;
                }
            }

            if( !( $this -> request -> input( 'error' ) > 0 ) && 
                !empty($resArray['p1_year']) && !empty($resArray['p2_year']) )
            {                
                // function call
                $resArray = getTrendYearARiskData($this, $resArray);
                $err = 0;

                // display year error
                if(!is_object($resArray['p1_year_obj']))
                {
                    $this -> request -> setInputCustom( 'endMonth_err', Notifications::getNoti('yearDataNotFound'));
                    $err++;
                }

                if(!is_object($resArray['p2_year_obj']))
                {
                    $this -> request -> setInputCustom( 'endMonth2_err', Notifications::getNoti('yearDataNotFound'));
                    $err++;
                }

                if(empty($resArray['p1_risk']))
                {
                    $this -> request -> setInputCustom( 'endMonth_err', Notifications::getNoti('riskCategoryWeightNoData'));
                    $err++;
                }

                if(empty($resArray['p2_risk']))
                {
                    $this -> request -> setInputCustom( 'endMonth2_err', Notifications::getNoti('riskCategoryWeightNoData'));
                    $err++;
                }

                if($err) $this -> request -> setInputCustom( 'error', 1);

                unset($err);
            }    
            
            // get branch rating data 07.08.2024
            if( !( $this -> request -> input( 'error' ) > 0 ) && 
                is_object($resArray['p1_year_obj']))
                $resArray['branch_rating'] = $this -> getBranchRatingData($resArray['p1_year_obj'] -> id, 1);

            if(!is_array($resArray['branch_rating']) || (is_array($resArray['branch_rating']) && !(sizeof($resArray['branch_rating']) > 0)) )
            {
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('branchRatingDataNotFound'));
                $this -> request -> setInputCustom( 'error', 1);
            }
            
            // validation check
            if($this -> request -> input( 'error' ) > 0)
                Validation::flashErrorMsg();
            else
            {
                // validation ok
                $model = $this -> model('AuditAssesmentModel');
                $resArray['audit_units'] = ($this -> request -> input('selectSearchTypeFilter') == 1) ? $this -> data['audit_unit_data'] : $this -> data['ho_audit_unit_data'];

                $findAssesmentData = DBCommonFunc::getAllAuditAssesment($model, [
                    'where' => 'audit_status_id > "'. ASSESMENT_TIMELINE_ARRAY[3]['status_id'] .'" AND audit_unit_id IN ('. implode(',', array_keys($resArray['audit_units'])) .') AND (
                        (assesment_period_from >= :startMonth AND assesment_period_to <= :endMonth) OR 
                        (assesment_period_from >= :startMonth2 AND assesment_period_to <= :endMonth2)
                    ) AND deleted_at IS NULL ORDER BY audit_unit_id ASC',
                    'params' => [
                        'startMonth' => $extra['startMonth'], 'endMonth' => $extra['endMonth'],
                        'startMonth2' => $extra['startMonth2'], 'endMonth2' => $extra['endMonth2']
                    ]
                ], 'id, year_id, audit_type_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id, audit_start_date, audit_end_date, updated_at');

                if(is_array($findAssesmentData) && sizeof($findAssesmentData) > 0)
                {
                    // has data // function call for broader area report update
                    BROADER_AREA_STORE_SUMMARY_HELPER($this, $findAssesmentData);
                    $resArray['assesment_data'] = [];
                }
                    
                $filterArray = [
                    'where' => 'audit_status_id > :audit_status_id AND audit_unit_id IN ('. implode(',', array_keys($resArray['audit_units'])) .') AND (
                            (year = :year1 AND assesment_period_from >= :startMonth AND assesment_period_to <= :endMonth) OR 
                            (year = :year2 AND assesment_period_from >= :startMonth2 AND assesment_period_to <= :endMonth2)
                        ) AND deleted_at IS NULL ORDER BY audit_unit_id ASC',
                    'params' => [
                        'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[3]['status_id'],
                        'year1' => getFYOnDate($extra['startMonth']), 'startMonth' => $extra['startMonth'], 'endMonth' => $extra['endMonth'],
                        'year2' => getFYOnDate($extra['startMonth2']), 'startMonth2' => $extra['startMonth2'], 'endMonth2' => $extra['endMonth2']
                    ]
                ];

                //  PASS ONLY ASSESMENT DATA

                $model = $this -> model('ReportScoringMasterModel');
                $findAssesmentDataReport = get_all_data_query_builder(2, $model, $model -> getTableName(), $filterArray, 'sql', "SELECT * FROM " . $model -> getTableName());

                if(is_array($findAssesmentDataReport) && sizeof($findAssesmentDataReport) > 0)
                {
                    $resArray['p1_tot'] = 0;
                    $resArray['p2_tot'] = 0;

                    foreach($findAssesmentDataReport as $row)
                    {
                        // push data
                        $resArray['assesment_data'][ $row -> id ] = $row;

                        if( !isset( $resArray['audit_units'][ $row -> audit_unit_id ] -> period_1 ) )
                        {
                            $resArray['audit_units'][ $row -> audit_unit_id ] -> period_1 = [ 'wg_sc' => 0, 'avg' => 0, 'risk_data' => [], 'audits' => [] ];
                            $resArray['audit_units'][ $row -> audit_unit_id ] -> period_1_cnt = calculate_days_diffrence($extra['startMonth'], $extra['endMonth']);

                            $resArray['audit_units'][ $row -> audit_unit_id ] -> period_2 = [ 'wg_sc' => 0, 'avg' => 0, 'risk_data' => [], 'audits' => [] ];
                            $resArray['audit_units'][ $row -> audit_unit_id ] -> period_2_cnt = calculate_days_diffrence($extra['startMonth2'], $extra['endMonth2']);
                        }

                        try
                        {
                            $cRiskJSON = json_decode($row -> risk_data, true);

                            if(is_array($cRiskJSON) && sizeof($cRiskJSON) > 0)
                            {
                                $key = null;

                                // period 1
                                if( $row -> assesment_period_from >= $extra['startMonth'] && 
                                    $row -> assesment_period_to <= $extra['endMonth'] )
                                {
                                    $resArray['audit_units'][ $row -> audit_unit_id ] -> period_1['audits'][ $row -> id ] = $row -> id;
                                    $resArray['audit_units'][ $row -> audit_unit_id ] -> period_1_cnt -= calculate_days_diffrence($row -> assesment_period_from, $row -> assesment_period_to);
                                    $key = '1';
                                }

                                // period 2
                                elseif( $row -> assesment_period_from >= $extra['startMonth2'] && 
                                        $row -> assesment_period_to <= $extra['endMonth2'] )
                                {
                                    $resArray['audit_units'][ $row -> audit_unit_id ] -> period_2['audits'][ $row -> id ] = $row -> id;
                                    $resArray['audit_units'][ $row -> audit_unit_id ] -> period_2_cnt -= calculate_days_diffrence($row -> assesment_period_from, $row -> assesment_period_to);
                                    $key = '2';
                                }

                                if(!empty($key))
                                { 
                                    foreach($cRiskJSON as $cRiskId => $cRiskDetails)
                                    {
                                        if(is_array($resArray['risk_category']) && array_key_exists($cRiskId, $resArray['risk_category'])):

                                            $cKey = 'period_' . $key;
                                        
                                            if(!array_key_exists($cRiskId, $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data']))
                                                $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ] = [ 'wg_sc' => 0, 'tot_sc' => 0, 'avg' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0 ];

                                            // addtion data
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['tot_sc'] += $cRiskDetails['avg_sc'];
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['1'] += $cRiskDetails['1'];
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['2'] += $cRiskDetails['2'];
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['3'] += $cRiskDetails['3'];
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['4'] += $cRiskDetails['4'];

                                            $cRiskKey = 'p' . $key . '_risk';

                                            if( is_array($resArray[ $cRiskKey ]) && 
                                                array_key_exists($cRiskId, $resArray[ $cRiskKey ]) )
                                            {
                                                // calculation everytime change
                                                $cWeight = $resArray[ $cRiskKey ][ $cRiskId ] -> risk_weight;

                                                if($cWeight > 0 && $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['tot_sc'] > 0)
                                                {
                                                    $avgScore = get_decimal(
                                                        get_decimal($resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['tot_sc'], 2) / 
                                                        sizeof($resArray['audit_units'][ $row -> audit_unit_id ] -> { 'period_' . $key }['audits']), 
                                                    2);
                                                    
                                                    // avg score
                                                    $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['avg'] = $avgScore;

                                                    $avgScore = get_decimal(($avgScore * $cWeight), 2);
                                                    $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'][ $cRiskId ]['wg_sc'] = $avgScore;
                                                }
                                            }

                                        endif;
                                    }

                                    // calculate total weightage score
                                    if( isset($resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data']) &&
                                        is_array($resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data']) && 
                                        sizeof($resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data']) > 0 )
                                    {
                                        $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['wg_sc'] = 0;
                                        $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['avg'] = 0;

                                        foreach ($resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['risk_data'] as $cRiskId => $cRiskData)
                                        {
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['wg_sc'] += $cRiskData['wg_sc'];
                                            $resArray['audit_units'][ $row -> audit_unit_id ] -> { $cKey }['avg'] += $cRiskData['avg'];
                                        }
                                    }
                                }
                            }

                        } catch (Exception $e) { }

                        // total calculation
                        if( $resArray['audit_units'][ $row -> audit_unit_id ] -> period_1_cnt == 0 && 
                            is_array($resArray['audit_units'][ $row -> audit_unit_id ] -> period_1) && 
                            isset($resArray['audit_units'][ $row -> audit_unit_id ] -> period_1['wg_sc']))
                            $resArray['p1_tot'] += get_decimal($resArray['audit_units'][ $row -> audit_unit_id ] -> period_1['wg_sc'], 2);

                        if( $resArray['audit_units'][ $row -> audit_unit_id ] -> period_2_cnt == 0 && 
                            is_array($resArray['audit_units'][ $row -> audit_unit_id ] -> period_2) && 
                            isset($resArray['audit_units'][ $row -> audit_unit_id ] -> period_2['wg_sc']))
                            $resArray['p2_tot'] += get_decimal($resArray['audit_units'][ $row -> audit_unit_id ] -> period_2['wg_sc'], 2);
                    }

                    $this -> data['data_array'] = $resArray;
                    $this -> data['extra_array'] = $extra;
                    // print_r(array_keys($resArray));
                    // print_r($resArray);
                    // exit;

                }
                else
                {
                    // data not found
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('noDataFound'));
                    $this -> request -> setInputCustom( 'error', 1);
                }
            }

        });

        // print_r($this -> data);
        // exit;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // external report - 1 // re assesment points report
    public function reAuditReport($getRequest)
    {
        $this -> generateMe('reAuditReport');

        $assesId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';
        $assesmentData = null;

        if(!empty($assesId))
            $assesmentData = get_single_assesment_details($this, [ 
                'where' => 'id = :id AND audit_status_id = :audit_status_id AND deleted_at IS NULL', 
                'params' => [ 'id' => $assesId, 'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[3]['status_id'] ] 
            ]);

        if(!is_object($assesmentData))
        {
            // data not found
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // find other detailed data
        $assesmentData = get_assesment_all_details($this, $assesId, $assesmentData);        

        // audit rejected observation data find // helper function call
        $dataArray = find_audit_observations($this, $assesmentData, 'AACRP');

        $this -> data['data_array'] = $dataArray;
        $this -> data['assesmentData'] = $assesmentData;
        $this -> data['audit_unit_data'][ $assesmentData -> audit_unit_id ] = $assesmentData -> audit_unit_details;

        // get risk data
        $this -> data['risk_category_data'] = $this -> getCommonData(['risk_category' => true])['risk_category'];

        // unset vars
        unset($dataArray, $assesId, $assesmentData);

        $this -> data['filter_type'] = 'REARP';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 40. Internal Assesment Report
    public function internalAssesmentReport()
    {
        // getting year data
        $this -> getYearData();

        $this -> generateMe('internalAssesmentReport', 1);
        $this -> data['audit_units_data'] = [];
        $this -> data['page'] = 'A4L';

        if(is_array($this -> data['year_data']) && sizeof($this -> data['year_data']) > 0)
        {
            // re assign data
            $this -> data['year_data'] = $this -> data['year_data'][0]; // re assign and convert to object

            // get audit units
            $model = $this -> model('AuditUnitModel');

            $this -> data['audit_units_data'] = DBCommonFunc::getAllAuditUnitData($model, [
                'where' => 'is_active = 1 AND deleted_at IS NULL ORDER BY audit_unit_code+0, section_type_id+0',
                'params' => []
            ]);
            
            if(is_array($this -> data['audit_units_data']) && sizeof($this -> data['audit_units_data']) > 0)
            {
                $auditUnitData = $this -> data['audit_units_data'];
                $this -> data['audit_units_data'] = [];

                foreach($auditUnitData as $cAuditUnit) {
                    $cAuditUnit -> asses_data = [];
                    $this -> data['audit_units_data'][ $cAuditUnit -> id ] = $cAuditUnit;
                }

                // unset val
                unset($auditUnitData);

                $model = $this -> model('AuditAssesmentModel');
                $select = "SELECT id, year_id, audit_unit_id, frequency, assesment_period_from, assesment_period_to, audit_status_id, audit_start_date, audit_review_date, compliance_start_date FROM audit_assesment_master WHERE deleted_at IS NULL AND year_id = '". $this -> data['year_data'] -> id ."' AND audit_unit_id IN (". implode(',', array_keys($this -> data['audit_units_data'])) .")";

                // get all asses data
                $getAllAssesData = get_all_data_query_builder(2, $model, 'audit_assesment_master', [], 'sql', $select);

                if(is_array($getAllAssesData) && sizeof($getAllAssesData) > 0)
                {
                    // has asses data // push data
                    foreach($getAllAssesData as $cAssesData) {
                        $this -> data['audit_units_data'][ $cAssesData -> audit_unit_id ] -> asses_data[ $cAssesData -> id ] = $cAssesData;
                    }
                }
            }
        }

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
        ]);
    }

    private function performanceReportFindData($extra, $post = false)
    {
        // method call get audit unit OR HO
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        if(!$post)
        {
            // need audit assesment js
            $this -> data['js'][] = 'reports/report-audit-assesment.js';
            $this -> data['js'][] = 'reports/report-search-type-filter.js';
        }

        // method call
        $this -> generateBroaderAreaFilterArray($extra['selectType']);

        if($post)
        {
            $this -> data['data_error'] = 'noDataFound';

            // validation method call // and get assesment data
            $resData = $this -> broaderAreaWiseScoringReportValidation(0, [
                'select_cols' => 'id, year_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id, menu_ids, cat_ids, header_ids, question_ids'
            ]);

            $this -> data['data_array'] = array();

            if( !$this -> request -> input( 'error' ) > 0 && 
                isset($resData['data']) && 
                sizeof($resData['data']) > 0 )
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // method call // get matrixRisk, riskCategory, accCategory, matrixRiskZero, matrixRiskType, riskCategoryStore
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId, ['noBoraderAreaNeeded' => 1]);

                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }

                // STEP 1 - FIND QUESTION DATA
                if(!$this -> request -> input( 'error' ) > 0)
                {
                    // data found find common data // function call
                    $res = PERFORMANCE_REPORT_FIND_CATEGORY_AND_QUESTION_DATA($this, [ 
                        'ASSES_DATA' => $resData['data'], 
                        'matrixRisk' => $commonResData['matrixRisk'] 
                    ]);

                    if(!empty($res['err']))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($res['err']) );
                        $this -> request -> setInputCustom( 'error', 1);
                    }
                    else // assign data
                        $resData['data'] = $res['ASSES_DATA'];
                }

                // STEP 2 - FIND TOTAL ACCOUNTS DATA ASSESMENT WISE // function call
                if(!$this -> request -> input( 'error' ) > 0)
                    $resData['data'] = PERFORMANCE_REPORT_FIND_DUMP_DATA_ASSESMENT_WISE($this, [ 'ASSES_DATA' => $resData['data'] ])['ASSES_DATA'];

                // STEP 3 - FIND ANSWERS DATA
                if(!$this -> request -> input( 'error' ) > 0)
                {
                    $res = PERFORMANCE_REPORT_FIND_ASSESMENT_ANSWERS($this, [ 
                        'ASSES_DATA' => $resData['data'],
                        'matrixRisk' => $commonResData['matrixRisk'] 
                    ]);            

                    if(!empty($res['err']))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($res['err']) );
                        $this -> request -> setInputCustom( 'error', 1);
                    }
                    else // assign data
                        $resData['data'] = $res['ASSES_DATA'];
                }
            }

            if( empty($this -> data['data_array']) )
                unset($this -> data['data_array']);

            // FIND ANSWERS DATA
            if(!$this -> request -> input( 'error' ) > 0)
            {
                $sendData = [ 
                    'ASSES_DATA' => $resData['data'],
                    'riskCategory' => $commonResData['riskCategory'],
                    'audit_unit_data' => $this -> data['audit_unit_data'],
                    'ho_audit_unit_data' => $this -> data['ho_audit_unit_data']
                ];

                if( isset($extra['combined']) )
                    $sendData['combined'] = 1;

                $res = PERFORMANCE_REPORT_MIX_ASSES_DATA_TO_AUDIT_UNIT($this, $sendData);

                // assign data
                $resData['data'] = $res['mix_data'];
                $resData['has_data'] = $res['has_data'];
                $resData['total_risk_scores'] = $res['total_risk_scores'];
                unset($res);
            }

            // unset vals
            unset($res);

            // validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
                // unset($this -> data['data_array']);
            }
            else
            {
                // return data
                return [ 'data' => $resData['data'], 'has_data' => $resData['has_data'], 'total_risk_scores' => $resData['total_risk_scores'] ];
            }
        }
    }

    // 41. Performance Risk Weightage Report
    public function performanceRiskWeightageReport()
    {
        $this -> generateMe('performanceRiskWeightageReport');
        
        $this -> data['extra'] = [
            'selectType' => [1,2],
            'combined' => 1
        ];

        // method call
        $this -> performanceReportFindData($this -> data['extra']);

        // post method after form submit
        $this -> request::method("POST", function() {
            
            // private method call
            $this -> assignAssesmentAjaxData(1);

            $res = $this -> performanceReportFindData($this -> data['extra'], 1);

            if(!empty($res))
            {
                $this -> data['data_array'] = $res['data'][ array_keys($res['data'])[0] ];
                $this -> data['has_data'] = $res['has_data'];
            }
            else
                $this -> data['data_array'] = null;

            // unset val
            unset($res);
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 42. Performance Risk Weightage Report - Category wise
    public function performanceRiskWeightageReportCategoryWise()
    {
        $this -> generateMe('performanceRiskWeightageReportCategoryWise');

        $this -> data['extra'] = [
            'selectType' => [1,2],
            // 'combined' => 1
        ];

        // method call
        $this -> performanceReportFindData($this -> data['extra']);

        // post method after form submit
        $this -> request::method("POST", function() {
            
            // private method call
            $this -> assignAssesmentAjaxData(1);

            $res = $this -> performanceReportFindData($this -> data['extra'], 1);

            if(!empty($res))
            {
                $this -> data['data_array'] = $res['data'][ array_keys($res['data'])[0] ];
                $this -> data['has_data'] = $res['has_data'];
            }
            else
                $this -> data['data_array'] = null;
            
            // unset val
            unset($res);
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 43. Performance Risk Weightage Report All Units
    public function RBIAPerformanceRiskWeightageReportAllUnits()
    {
        $this -> generateMe('RBIAPerformanceRiskWeightageReportAllUnits');
        
        $this -> data['extra'] = [
            'selectType' => [3,4,5,6],
            'combined' => 1
        ];

        // method call
        $this -> performanceReportFindData($this -> data['extra']);

        // post method after form submit
        $this -> request::method("POST", function() {
            
            // private method call
            $this -> assignAssesmentAjaxData(1);

            $res = $this -> performanceReportFindData($this -> data['extra'], 1);

            if(!empty($res))
            {
                $this -> data['data_array'] = $res['data'];
                $this -> data['has_data'] = $res['has_data'];
                $this -> data['total_risk_scores'] = $res['total_risk_scores'];
            }
            else
                $this -> data['data_array'] = null;

            // unset val
            unset($res);
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 44. Performance Risk Weightage Report All Units - Category Wise
    public function categoryWiseRiskWeightageReports()
    {
        $this -> generateMe('categoryWiseRiskWeightageReports');

        // method call get audit unit OR HO
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        // need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray([1,2]);

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_error'] = 'noDataFound';
            $this -> data['audit_unit_ids_array'] = array();

            // private method call
            $this -> assignAssesmentAjaxData(1);

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();
            $this -> data['data_array'] = array();

            // print_r($resData);
            // exit;

            $fyId = 0;
            $commonResData = null;

            if( !$this -> request -> input( 'error' ) > 0 && 
                isset($resData['data']) && 
                sizeof($resData['data']) > 0 )
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // push data for view
                $this -> data['select_search_type'] = $this -> request -> input('selectSearchTypeFilter');
                $this -> data['period'] = null;

                if( in_array($this -> data['select_search_type'], [3, 4]) )
                    $this -> data['period'] = $resData['data'][ array_keys($resData['data'])[0] ] -> combined_period;

                else if( in_array($this -> data['select_search_type'], [5, 6]))
                    $this -> data['period'] = $this -> request -> input('startDate') . ' - ' . $this -> request -> input('endDate') .'</p>' . "\n";

                // method call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }

                if(!$this -> request -> input( 'error' ) > 0)
                {
                    // has data 
                    foreach ($resData['data'] as $cAssesId => $cAssesDetails) {
                        $this -> data['audit_unit_id_array'] = [];

                        if(!isset($this -> data['audit_unit_id']))
                            $this -> data['audit_unit_id'] = $cAssesDetails -> audit_unit_id;
    
                        if(is_array($commonResData['riskCategory']) && sizeof($commonResData['riskCategory']) > 0)
                        {
                            foreach ($commonResData['riskCategory'] as $cRiskCatId => $cRiskCatDetails) {
                                $this -> data['audit_unit_id_array'][ $cRiskCatId ] = 0;
                            }
    
                            $this -> data['audit_unit_id_array']['tot'] = 0;
                        }

                        // single audit unit data 12.08.2024 Kunal
                        break;
                    }

                    if(!isset($this -> data['audit_unit_id_array']))
                    {
                        $assesErr = in_array($this -> request -> input('selectSearchTypeFilter'), [3,5]) ? 'auditUnitNoDataFound' : 'HOAuditUnitNoDataFound';
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($assesErr));
                        $this -> request -> setInputCustom( 'error', 1);
                    }
                }
            }

            if(!$this -> request -> input( 'error' ) > 0)
            {
                // define categories // method call
                $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                    'res_data' => $resData, 
                    'common_data' => $commonResData 
                ]);

                $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                if(isset($this -> data['audit_unit_data']))
                    $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                if(isset($this -> data['ho_audit_unit_data']))
                    $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                // assign sorted broader area keys
                $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'];

                // function call // overwrite data
                $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                if(!is_array($res) || empty($res))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                    $this -> request -> setInputCustom( 'error', 1);
                    unset($this -> data['sortedBroaderAreaKeys']);
                }
                else
                {
                    // add response
                    $this -> data['data_array'] = $res[ array_keys($res)[0] ];
                }
            }

            if(!is_array($res) || empty($res))
            {
                // error
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                $this -> request -> setInputCustom( 'error', 1);
                unset($this -> data['sortedBroaderAreaKeys']);
            }

            // validation check
            if($this -> request -> input( 'error' ) > 0)
                Validation::flashErrorMsg();
            else
            {
                // re assign
                $this -> data['risk_category'] = $commonResData['riskCategory'];
                unset($resData, $res);

                $sortedRiskTypeWeightArray = array();
                $cAuditDetails = $this -> data['audit_unit_id'];
                $noOfAssessment = sizeof($this -> data['data_array']['no_of_audits']);

                //check branch
                if( is_array($this -> data['audit_unit_data']) && 
                    array_key_exists($cAuditDetails, $this -> data['audit_unit_data']) )
                    $cAuditDetails = $this -> data['audit_unit_data'][ $cAuditDetails ];

                elseif( is_array($this -> data['ho_audit_unit_data']) &&
                        array_key_exists($cAuditDetails, $this -> data['ho_audit_unit_data'] ) )
                    $cAuditDetails = $this -> data['ho_audit_unit_data'][ $cAuditDetails ];

                // $this -> data['data_array'] = $this -> data['data_array']['res_data']['data'];
                $totWeightedScore = 0; 
                $totalQualQuan = 0;

                // print_r($this -> data['data_array']);
                // exit;

                // foreach($this -> data['data_array'] as $cAssesId => $cAssesDetails):

                foreach($this -> data['sortedBroaderAreaKeys'] as $cGenKey => $cGenKeyStr):

                    if(array_key_exists($cGenKey, $this -> data['data_array']))
                    {
                        foreach ($this -> data['data_array'][ $cGenKey ]['borader_area'] as $cBroaderAreaId => $cBroaderAreaDetails)
                        {
                            foreach ($cBroaderAreaDetails['category'] as $cRiskId => $cRiskDetails)
                            {
                                //insert gen key            
                                if(!array_key_exists($cGenKey, $sortedRiskTypeWeightArray))
                                    $sortedRiskTypeWeightArray[ $cGenKey ] = array();

                                //check risk cat id
                                if(!array_key_exists($cRiskId, $sortedRiskTypeWeightArray[ $cGenKey ]))
                                    $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ] = array(
                                        'title' => $cRiskDetails['title'],
                                        'no_of_audit_conduct' => $noOfAssessment,
                                        'risk_weight' =>  $cRiskDetails['risk_weight'],
                                        'total_qual_quan' => 0, 'tot_avg_score' => 0,
                                        'avg_tot_score_per_audit' => 0,
                                        'weighted_score' => 0
                                    );

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['total_qual_quan'] += $cRiskDetails['total_qual_quan'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['tot_avg_score'] += $cRiskDetails['tot_avg_score'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['avg_tot_score_per_audit'] += $cRiskDetails['avg_tot_score_per_audit'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['weighted_score'] += $cRiskDetails['weighted_score'];

                                $totalQualQuan += $cRiskDetails['total_qual_quan'];                          

                                $totWeightedScore  += $cRiskDetails['weighted_score'];  
                            }
                        }
                    }
                    
                endforeach;

                // echo $totWeightedScore;
                // print_r($sortedRiskTypeWeightArray);
                // exit;

                unset(
                    $this -> data['audit_unit_ids_array'],
                    $this -> data['audit_section_data']
                );

                // re assign data
                $this -> data['audit_details'] = $cAuditDetails;
                $this -> data['no_of_assessment'] = $noOfAssessment;
                $this -> data['data_array'] = $sortedRiskTypeWeightArray;
                $this -> data['tot_weighted_score'] = $totWeightedScore;
                // $this -> data['branch_rating'] = $this -> getBranchRatingData($fyId);
            }
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }
    
    // 45. Broader Area Wise Risk Weightage Report
    public function borderAriaWiseRiskWeightageReport()
    {
        // change broader area function for single assesment 21.06.2024
        $this -> generateMe('borderAriaWiseRiskWeightageReport');

        // method call
        $this -> reportAuditUnitData();

        // set page
        $this -> data['page'] = 'A4L';
        $this -> data['need_calender'] = true;

        //need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray();

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_error'] = 'noDataFound';
            $this -> data['data_array'] = array();

            // find assesmend data
            if( in_array($this -> request -> input('selectSearchTypeFilter'), [2,3]) );
            {
                // method call
                $resData = $this -> reportAuditUnitsAjax(1);

                if(array_key_exists('success', $resData))
                {
                    // assesment data assign
                    $this -> data['audit_assesment_data'] = $resData['data'];
                }
            }

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();

            if(!$this -> request -> input( 'error' ) > 0 && isset($resData['data']) && sizeof($resData['data']) > 0)
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // method call // helper function call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                // has error
                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }
                else
                {
                    // no errors // helper funtion call
                    $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                        'res_data' => $resData, 
                        'common_data' => $commonResData 
                    ]);

                    $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                    if(isset($this -> data['audit_unit_data']))
                        $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                    if(isset($this -> data['ho_audit_unit_data']))
                        $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                    // assign sorted broader area keys
                    $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'] ;

                    // function call // overwrite data
                    $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                    // print_r($res);
                    // exit;

                    if(!is_array($res) || empty($res))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                        $this -> request -> setInputCustom( 'error', 1);
                        unset($this -> data['sortedBroaderAreaKeys']);
                    }
                    else
                    {
                        // add response
                        $this -> data['data_array'] = $res;
                    }
                }
            }

            if( empty($this -> data['data_array']) )
                unset($this -> data['data_array']);

            // validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
                // unset($this -> data['data_array']);
            }

        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 36. Risk Category Summary Report
    public function riskCategorySummaryReport()
    {
        $this -> generateMe('riskCategorySummaryReport');

        // method call get audit unit OR HO
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        // need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray([3,4,5,6]);

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_array'] = null;
            $this -> data['data_error'] = 'noDataFound';
            $this -> data['audit_unit_ids_array'] = array();

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();
            $this -> data['data_array'] = array();

            // print_r($resData);
            // exit;

            $fyId = 0;
            $commonResData = null;

            if( !$this -> request -> input( 'error' ) > 0 && 
                isset($resData['data']) && 
                sizeof($resData['data']) > 0 )
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // method call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }

                if(!$this -> request -> input( 'error' ) > 0)
                {
                    // has data 
                    foreach ($resData['data'] as $cAssesId => $cAssesDetails) {
                        if(!array_key_exists($cAssesDetails -> audit_unit_id, $this -> data['audit_unit_ids_array']))
                            $this -> data['audit_unit_ids_array'][ $cAssesDetails -> audit_unit_id ] = [];
    
                        if(is_array($commonResData['riskCategory']) && sizeof($commonResData['riskCategory']) > 0)
                        {
                            foreach ($commonResData['riskCategory'] as $cRiskCatId => $cRiskCatDetails) {
                                $this -> data['audit_unit_ids_array'][ $cAssesDetails -> audit_unit_id ][ $cRiskCatId ] = 0;
                            }
    
                            $this -> data['audit_unit_ids_array'][ $cAssesDetails -> audit_unit_id ]['tot'] = 0;
                        }
                    }

                    $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                        'res_data' => $resData, 
                        'common_data' => $commonResData 
                    ]);

                    $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                    if(isset($this -> data['audit_unit_data']))
                        $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                    if(isset($this -> data['ho_audit_unit_data']))
                        $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                    // assign sorted broader area keys
                    $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'] ;

                    // function call // overwrite data
                    $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                    if(!is_array($res) || empty($res))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                        $this -> request -> setInputCustom( 'error', 1);
                        unset($this -> data['sortedBroaderAreaKeys']);
                    }
                    else
                    {
                        // add response
                        $this -> data['data_array'] = $res;
                    }
                }
            }

            if( empty($this -> data['data_array']) )
                unset($this -> data['data_array']);

            // validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
                // unset($this -> data['data_array']);
            }
            else
            {
                // re assign
                $this -> data['risk_category'] = $commonResData['riskCategory'];

                unset($resData, $res);

                $totalAuditUnitWiseCount = array('tot' => 0);
                            
                foreach($this -> data['data_array'] as $cAuditUnitId => $cAuditUnitDetails)
                {
                    foreach($cAuditUnitDetails as $cGenKey => $cGenData):
                
                    if(array_key_exists($cGenKey, $this -> data['sortedBroaderAreaKeys']))
                    {
                        foreach ($cGenData['borader_area'] as $cBroaderAreaId => $cBroaderAreaDetails)
                        {            
                            // print_r($this -> data['audit_unit_ids_array']);
                            // exit;

                            foreach ($cBroaderAreaDetails['category'] as $cRiskId => $cRiskDetails)
                            {
                                $this -> data['audit_unit_ids_array'][ $cAuditUnitId ][ $cRiskId ] = get_decimal((
                                    $this -> data['audit_unit_ids_array'][ $cAuditUnitId ][ $cRiskId ] + $cRiskDetails['weighted_score']), 2);

                                //add in total
                                $this -> data['audit_unit_ids_array'][ $cAuditUnitId ]['tot'] = get_decimal(($this -> data['audit_unit_ids_array'][ $cAuditUnitId ]['tot'] + $cRiskDetails['weighted_score']), 2);

                                if(!array_key_exists($cRiskId, $totalAuditUnitWiseCount))
                                    $totalAuditUnitWiseCount[ $cRiskId ] = 0;

                                $totalAuditUnitWiseCount[ $cRiskId ] = get_decimal(($totalAuditUnitWiseCount[ $cRiskId ] + $cRiskDetails['weighted_score']), 2);

                                $totalAuditUnitWiseCount['tot'] = get_decimal(($totalAuditUnitWiseCount['tot'] + $cRiskDetails['weighted_score']), 2);
                            }
                        }
                    }

                    endforeach;
                }

                // re assign data
                $this -> data['data_array'] = $this -> data['audit_unit_ids_array'];
                $this -> data['select_search_type'] = $this -> request -> input('selectSearchTypeFilter');
                $this -> data['total_audit_unit_wise_count'] = $totalAuditUnitWiseCount;
                $this -> data['branch_rating'] = $this -> getBranchRatingData($fyId);
            }
        });

        // set page
        $this -> data['page'] = 'A5L';

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }
    
    // 46. Question Set Wise Mapping Report
    public function questionWiseConsolidateSummary ()
    {
        $this -> generateMe('questionWiseConsolidateSummary', 2);

        // audit unit data
        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(2, false, true);

        //post method after form submit
        $this -> request::method("POST", function() {

            //check validation
            Validation::validateData($this -> request, [
                'search_question' => 'required',
                'audit_unit_id' => 'required'
            ]);
            
            if($this -> request -> input( 'error' ) > 0):    
                Validation::flashErrorMsg();
            else:
                // get Assesment Data
                $this -> model = $this -> model('AuditAssesmentModel');
                $this -> data['audit_assesment_id'] = $this -> model -> getAllAuditAssesment([
                    'where' => 'asm.deleted_at IS NULL AND asm.audit_unit_id = :audit_unit_id',
                    'params' => ['audit_unit_id' => $this -> request -> input( 'audit_unit_id' )]
                ], 'sql', 'SELECT aum.id AS audit_id, aum.name AS branch_name, asm.id, asm.assesment_period_from, asm.assesment_period_to, asm.id AS assesment_id, asm.audit_status_id FROM audit_assesment_master asm INNER JOIN audit_unit_master aum ON asm.audit_unit_id = aum.id');

                // find Question Data
                $this -> model = $this -> model('QuestionMasterModel');
                $this -> data['questions_id'] = $this -> model -> getSingleQuestion([
                    'where' => 'deleted_at IS NULL AND question LIKE :question',
                    'params' => ['question' => '%'.$this -> request -> input( 'search_question' ).'%']
                ]); 
                $this -> data['question_id'] = $this -> data['questions_id'] -> id;
                
                // Answer Data
                $this -> data['answer_array'] = $this -> model = $this -> model('AnswerDataModel');
                $this -> data['answer_data'] = $this -> model -> getAllAnswers([
                    'where' => 'ad.deleted_at IS NULL AND ad.question_id = :question_id AND assesment_id IN ('.implode(',', array_column($this -> data['audit_assesment_id'], 'id')).')',
                    'params' => ['question_id' => $this -> data['question_id']]
                ],
                'sql', 'SELECT ad.id, ad.assesment_id, ad.answer_given, ad.audit_comment, ad.audit_reviewer_comment, ad.audit_commpliance, ad.compliance_reviewer_comment, ad.business_risk, ad.control_risk, cm.name AS category_name FROM answers_data ad INNER JOIN category_master cm ON cm.id = ad.category_id');

                // Array format for view
                $this -> data['question_consolidate'] = array(
                    'question_search' => $this -> request -> input( 'search_question' ),
                    'assesment_id' => $this -> data['audit_assesment_id'],
                    'answer_data' => $this -> data['answer_data']
                );
            endif;
        });

        // set page
        $this -> data['page'] = 'A4L';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    // 44. Performance Risk Weightage Report All Units - Category Wise
    public function typewiseRiskWeightageReport()
    {
        $this -> generateMe('typewiseRiskWeightageReport');

        // method call get audit unit OR HO
        $this -> reportAuditUnitData();

        $this -> data['need_calender'] = true;

        // need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray([1,2]);

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_error'] = 'noDataFound';
            $this -> data['audit_unit_ids_array'] = array();

            // private method call
            $this -> assignAssesmentAjaxData(1);

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();
            $this -> data['data_array'] = array();

            // print_r($resData);
            // exit;

            $fyId = 0;
            $commonResData = null;

            if( !$this -> request -> input( 'error' ) > 0 && 
                isset($resData['data']) && 
                sizeof($resData['data']) > 0 )
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // push data for view
                $this -> data['select_search_type'] = $this -> request -> input('selectSearchTypeFilter');
                $this -> data['period'] = null;

                if( in_array($this -> data['select_search_type'], [3, 4]) )
                    $this -> data['period'] = $resData['data'][ array_keys($resData['data'])[0] ] -> combined_period;

                else if( in_array($this -> data['select_search_type'], [5, 6]))
                    $this -> data['period'] = $this -> request -> input('startDate') . ' - ' . $this -> request -> input('endDate') .'</p>' . "\n";

                // method call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }

                if(!$this -> request -> input( 'error' ) > 0)
                {
                    // has data 
                    foreach ($resData['data'] as $cAssesId => $cAssesDetails) {
                        $this -> data['audit_unit_id_array'] = [];

                        if(!isset($this -> data['audit_unit_id']))
                            $this -> data['audit_unit_id'] = $cAssesDetails -> audit_unit_id;
    
                        if(is_array($commonResData['riskCategory']) && sizeof($commonResData['riskCategory']) > 0)
                        {
                            foreach ($commonResData['riskCategory'] as $cRiskCatId => $cRiskCatDetails) {
                                $this -> data['audit_unit_id_array'][ $cRiskCatId ] = 0;
                            }
    
                            $this -> data['audit_unit_id_array']['tot'] = 0;
                        }

                        // single audit unit data 12.08.2024 Kunal
                        break;
                    }

                    if(!isset($this -> data['audit_unit_id_array']))
                    {
                        $assesErr = in_array($this -> request -> input('selectSearchTypeFilter'), [3,5]) ? 'auditUnitNoDataFound' : 'HOAuditUnitNoDataFound';
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($assesErr));
                        $this -> request -> setInputCustom( 'error', 1);
                    }
                }
            }

            if(!$this -> request -> input( 'error' ) > 0)
            {
                // define categories // method call
                $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                    'res_data' => $resData, 
                    'common_data' => $commonResData 
                ]);

                $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                if(isset($this -> data['audit_unit_data']))
                    $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                if(isset($this -> data['ho_audit_unit_data']))
                    $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                // assign sorted broader area keys
                $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'];

                // function call // overwrite data
                $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                if(!is_array($res) || empty($res))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                    $this -> request -> setInputCustom( 'error', 1);
                    unset($this -> data['sortedBroaderAreaKeys']);
                }
                else
                {
                    // add response
                    $this -> data['data_array'] = $res[ array_keys($res)[0] ];
                }
            }

            if(!is_array($res) || empty($res))
            {
                // error
                $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                $this -> request -> setInputCustom( 'error', 1);
                unset($this -> data['sortedBroaderAreaKeys']);
            }

            // validation check
            if($this -> request -> input( 'error' ) > 0)
                Validation::flashErrorMsg();
            else
            {
                // re assign
                $this -> data['risk_category'] = $commonResData['riskCategory'];
                unset($resData, $res);

                $sortedRiskTypeWeightArray = array();
                $cAuditDetails = $this -> data['audit_unit_id'];
                $noOfAssessment = sizeof($this -> data['data_array']['no_of_audits']);

                //check branch
                if( is_array($this -> data['audit_unit_data']) && 
                    array_key_exists($cAuditDetails, $this -> data['audit_unit_data']) )
                    $cAuditDetails = $this -> data['audit_unit_data'][ $cAuditDetails ];

                elseif( is_array($this -> data['ho_audit_unit_data']) &&
                        array_key_exists($cAuditDetails, $this -> data['ho_audit_unit_data'] ) )
                    $cAuditDetails = $this -> data['ho_audit_unit_data'][ $cAuditDetails ];

                // $this -> data['data_array'] = $this -> data['data_array']['res_data']['data'];
                $totWeightedScore = 0; 
                $totalQualQuan = 0;

                // print_r($this -> data['data_array']);
                // exit;

                // foreach($this -> data['data_array'] as $cAssesId => $cAssesDetails):

                foreach($this -> data['sortedBroaderAreaKeys'] as $cGenKey => $cGenKeyStr):

                    if(array_key_exists($cGenKey, $this -> data['data_array']))
                    {
                        foreach ($this -> data['data_array'][ $cGenKey ]['borader_area'] as $cBroaderAreaId => $cBroaderAreaDetails)
                        {
                            foreach ($cBroaderAreaDetails['category'] as $cRiskId => $cRiskDetails)
                            {
                                //insert gen key            
                                if(!array_key_exists($cGenKey, $sortedRiskTypeWeightArray))
                                    $sortedRiskTypeWeightArray[ $cGenKey ] = array();

                                //check risk cat id
                                if(!array_key_exists($cRiskId, $sortedRiskTypeWeightArray[ $cGenKey ]))
                                    $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ] = array(
                                        'title' => $cRiskDetails['title'],
                                        'no_of_audit_conduct' => $noOfAssessment,
                                        'risk_weight' =>  $cRiskDetails['risk_weight'],
                                        'total_qual_quan' => 0, 'tot_avg_score' => 0,
                                        'avg_tot_score_per_audit' => 0,
                                        'weighted_score' => 0
                                    );

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['total_qual_quan'] += $cRiskDetails['total_qual_quan'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['tot_avg_score'] += $cRiskDetails['tot_avg_score'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['avg_tot_score_per_audit'] += $cRiskDetails['avg_tot_score_per_audit'];

                                $sortedRiskTypeWeightArray[ $cGenKey ][ $cRiskId ]['weighted_score'] += $cRiskDetails['weighted_score'];

                                $totalQualQuan += $cRiskDetails['total_qual_quan'];                          

                                $totWeightedScore  += $cRiskDetails['weighted_score'];  
                            }
                        }
                    }
                    
                endforeach;

                // echo $totWeightedScore;
                // print_r($sortedRiskTypeWeightArray);
                // exit;

                unset(
                    $this -> data['audit_unit_ids_array'],
                    $this -> data['audit_section_data']
                );

                // re assign data
                $this -> data['audit_details'] = $cAuditDetails;
                $this -> data['no_of_assessment'] = $noOfAssessment;
                $this -> data['data_array'] = $sortedRiskTypeWeightArray;
                $this -> data['tot_weighted_score'] = $totWeightedScore;
                // $this -> data['branch_rating'] = $this -> getBranchRatingData($fyId);
            }
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    public function riskTrendSummaryReport ()
    {
        $this -> generateMe('riskTrendSummaryReport', 2);

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, 1);

        $this -> data['audit_scoring_data'] = null;

         // find risk matrix model
        $this -> model = $this -> model('RiskMatrixModel');

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> model = $this -> model('ReportScoringMasterModel');

            //check validation 
            Validation::validateData($this -> request, [
                'audit_id' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> data['audit_scoring_data'] = $this -> model -> getAllReportScore([
                    'where' => 'deleted_at IS NULL AND audit_unit_id = :audit_unit_id',
                    'params' => ['audit_unit_id' => $this -> request -> input( 'audit_id' )]
                ]); 
            }
        });

        // set page
        $this -> data['page'] = 'A4L';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }

    public function riskTredSummaryAnalysis ()
    {
        $this -> generateMe('riskTredSummaryAnalysis', 2);

        $this -> data['audit_unit_data'] = $this -> getAllAuditUnits(null, false, 1);

        $this -> data['audit_scoring_data'] = null;

         // find risk matrix model
        $this -> model = $this -> model('RiskMatrixModel');

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> model = $this -> model('ReportScoringMasterModel');

            //check validation 
            Validation::validateData($this -> request, [
                'audit_id' => 'required'
            ]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)    
                Validation::flashErrorMsg(); 
            else
            {
                $this -> data['audit_scoring_data'] = $this -> model -> getAllReportScore([
                    'where' => 'deleted_at IS NULL AND audit_unit_id = :audit_unit_id',
                    'params' => ['audit_unit_id' => $this -> request -> input( 'audit_id' )]
                ]); 
            }
        });

        // set page
        $this -> data['page'] = 'A4L';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }
    public function riskTypeWiseRiskWeightageReport()
    {
        // change broader area function for single assesment 21.06.2024
        $this -> generateMe('riskTypeWiseRiskWeightageReport');

        // method call
        $this -> reportAuditUnitData();

        // set page
        $this -> data['page'] = 'A4L';
        $this -> data['need_calender'] = true;

        //need audit assesment js
        $this -> data['js'][] = 'reports/report-audit-assesment.js';
        $this -> data['js'][] = 'reports/report-search-type-filter.js';

        // method call
        $this -> generateBroaderAreaFilterArray();

        //post method after form submit
        $this -> request::method("POST", function() {

            $this -> data['data_error'] = 'noDataFound';
            $this -> data['data_array'] = array();

            // find assesmend data
            if( in_array($this -> request -> input('selectSearchTypeFilter'), [2,3]) );
            {
                // method call
                $resData = $this -> reportAuditUnitsAjax(1);

                if(array_key_exists('success', $resData))
                {
                    // assesment data assign
                    $this -> data['audit_assesment_data'] = $resData['data'];
                }
            }

            // validation method call
            $resData = $this -> broaderAreaWiseScoringReportValidation();

            if(!$this -> request -> input( 'error' ) > 0 && isset($resData['data']) && sizeof($resData['data']) > 0)
            {
                // has data // get financial year on date // note if single assessment seleced then year will be change
                $fyId = $resData['data'][ array_keys($resData['data'])[0] ] -> year_id;

                // method call // helper function call
                $commonResData = BROADER_AREA_COMMON_DATA_HELPER($this, $fyId);

                // has error
                if(!empty($commonResData['err']))
                {
                    $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti($commonResData['err']));
                    $this -> request -> setInputCustom( 'error', 1);
                }
                else
                {
                    // no errors // helper funtion call
                    $res = BROADER_AREA_QUESTIONS_ANS_HELPER($this, [ 
                        'res_data' => $resData, 
                        'common_data' => $commonResData 
                    ]);

                    $extra = [ 'audit_unit_data' => null, 'ho_audit_unit_data' => null ];

                    if(isset($this -> data['audit_unit_data']))
                        $extra['audit_unit_data'] = $this -> data['audit_unit_data'];

                    if(isset($this -> data['ho_audit_unit_data']))
                        $extra['ho_audit_unit_data'] = $this -> data['ho_audit_unit_data'];

                    // assign sorted broader area keys
                    $this -> data['sortedBroaderAreaKeys'] = $res['SORTED_BORADER_AREA_KEYS'] ;

                    // function call // overwrite data
                    $res = BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra);

                    // print_r($res);
                    // exit;

                    if(!is_array($res) || empty($res))
                    {
                        $this -> request -> setInputCustom( 'selectSearchTypeFilter_err', Notifications::getNoti('somethingWrong'));
                        $this -> request -> setInputCustom( 'error', 1);
                        unset($this -> data['sortedBroaderAreaKeys']);
                    }
                    else
                    {
                        // add response
                        $this -> data['data_array'] = $res;
                    }
                }
            }

            if( empty($this -> data['data_array']) )
                unset($this -> data['data_array']);

            // validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
                // unset($this -> data['data_array']);
            }

        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }
    public function roOfficerReviewReport()
    {
        $this -> generateMe('roOfficerReviewReport');

        $this -> data['filter_type'] = 'ACP';

        // method call
        if($this -> empType == 3)
        {
            $this -> auditId = $this -> getAuditId($this -> empId);

            $this -> auditReportFindData(1,false,1,$this -> auditId);
        }
        else
            $this -> auditReportFindData(1);

        $this -> data['filter_type'] = 'CRPWC';

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }       
    
}

?>