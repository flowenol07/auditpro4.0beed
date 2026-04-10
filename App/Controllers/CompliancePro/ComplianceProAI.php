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
use Core\DBCommonFunc;
use Smalot\PdfParser\Parser;

require_once APP_CORE . DS . 'HelperFunctionsCompliancePro.php';

class ComplianceProAI extends Controller  {

    public $me = null, $data, $request, $empType, $empId;
    public $model;

    public function __construct($me) 
    {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('complianceProReports')],
        ];      

        $this -> empType = Session::get('emp_type');
        $this -> empId = Session::get('emp_id');

        // request object created
        $this -> request = new Request();
    }

    public function index() 
    {

        return return2View($this, $this -> me -> viewDir . 'index');
    }

    private function generateMe ($meKey, $pageSize = null) 
    {
        $this -> me = SiteUrls::get($meKey);

        $this -> data['pageTitle'] = $this -> me -> pageTitle;
        
        // menuKey for active menu
        $this -> me -> menuKey = 'complianceProReports';

        $this -> data['need_print'] = true;
        $this -> data['need_excel'] = true;
    }

    // common methods
    private function getAllComplianceAuthority($filter = [])
    {
        $model = $this -> model('ComplianceCircularAuthorityModel');

        if(empty($filter))
            $filter = [
                'where' => 'is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ];

        $authorityData = $model -> getAllCircularAuthority($filter);
        $authorityData = convert_masters_data($authorityData);

        // unset var
        unset($model);
        return $authorityData;
    }

    private function getAllCirculars($filter = [])
    {
        $model = $this -> model('ComplianceCircularSetModel');
        $isApplicable = false;

        if(is_array($filter) && sizeof($filter) == 1 && isset($filter['applicable']))
        {
            $filter = [];
            $isApplicable = true;
        }


        if(empty($filter))
        {
            $filter = [
                'where' => 'ccsm.is_active = 1 AND ccsm.deleted_at IS NULL AND cca.deleted_at IS NULL',
                'params' => []
            ];

            if($isApplicable)
                $filter['where'] .= ' AND ccsm.is_applicable = 1';
        }

        $select = " SELECT ccsm.*, COALESCE(cca.name, '". ERROR_VARS['notFound'] ."') AS auth_name FROM 
                    com_circular_set_master ccsm LEFT JOIN com_circular_authority cca ON ccsm.authority_id = cca.id";

        $findData = get_all_data_query_builder(2, $model, 'com_circular_set_master', $filter, 'sql', $select);
        $findData = generate_data_assoc_array($findData, 'id');

        return $findData;
    }

    private function convertFrequencySelect($str = true)
    {
        $res = [];

        foreach(COMPLIANCE_PRO_ARRAY['compliance_frequency'] as $cfrqId => $cfrqData)
        {
            if($str)
                $res[ $cfrqData['title'] ] = $cfrqData['title'];
            else
                $res[ $cfrqData['id'] ] = $cfrqData['title'];
        }

        return $res;
    }

    private function getAllAuditUnitsData($filter = [])
    {
        if(empty($filter))
            $filter = [
                'where' => 'is_active = 1 AND deleted_at IS NULL',
                'params' => []
            ];

        $model = $this -> model('AuditUnitModel');
        $auditUnitData = DBCommonFunc::getAllAuditUnitData($model, $filter);
        $auditUnitData = convert_masters_data($auditUnitData, ['selectKey' => 'combined_name', 'selectVal' => 'combined_name']);

        // unset var
        unset($model);
        return $auditUnitData;
    }

    // 1. authority report
    public function authorityReport()
    {
        // method call
        $this -> data['authority_data'] = $this -> getAllComplianceAuthority();

        $this -> generateMe('complianceAuthorityReport');

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
        ]);
    }

    // 2. Circulars Report
    public function circularsReport()
    {
        $this -> generateMe('complianceCircularsReport');

        $this -> data['circular_data'] = $this -> getAllCirculars();
        $this -> data['applicable_status'] = [
            'Applicable' => 'Applicable',
            'Not Applicable' => 'Not Applicable',
        ];

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]); 
    }

    private function generateStatusReportDateArray($extra)
    {
        $res = [
            'gen_key' => $extra['from'] . 'TO'. $extra['to'],
            'com_period_from' => $extra['from'],
            'com_period_to' => $extra['to'],
            'freq' => $extra['freq'],
            'com_asses' => []
        ];

        return $res;
    }

    // 3. Compliance Status Report
    public function statusReport()
    {
        $this -> generateMe('complianceStatusReport');
        $this -> me -> url = SiteUrls::setUrl($this -> me -> url);

        //post method after form submit
        $this -> request::method("POST", function() 
        {
            Validation::validateData($this -> request, [
                'startDate' => 'required|regex[dateRegex, dateError]',
                'endDate' => 'required|regex[dateRegex, dateError]'
            ]);

            if(!($this -> request -> input( 'error' ) > 0))
            {
                $noti = new Notifications();
                date_validation_helper($this -> request, [], $noti);
                $err = 0; 

                if($this -> request -> has('period_to_err'))
                {
                    $this -> request -> setInputCustom( 'endDate_err', $this -> request -> input('period_to_err'));
                    $this -> request -> setInputCustom( 'error', 1);
                }
            }

            if(!($this -> request -> input( 'error' ) > 0))
            {
                // form has no error
                $FROM_DATE = $this -> request -> input('startDate');
                $TO_DATE = $this -> request -> input('endDate');

                // get all applicable circulars with assign master
                $model = $this -> model('ComplianceCircularSetModel');

                $query = "SELECT ccsm.*, 
                    ccmcts.id ccmctsId,
                    ccmcts.audit_unit_ids ccmcts_audit_unit_ids,
                    ccmcts.name ccmcts_set_name,
                    ccmcts.header_ids ccmcts_header_ids,
                    ccmcts.task_ids ccmcts_task_ids,
                    ccmcts.frequency frequency,
                    ccmcts.reporting_date_1 reporting_date_1,
                    ccmcts.due_date_1 due_date_1,
                    ccmcts.reporting_date_2 reporting_date_2,
                    ccmcts.due_date_2 due_date_2,
                    ccmcts.otu_start_date otu_start_date,
                    ccmcts.otu_end_date otu_end_date,
                    ccmcts.schedule_start_date schedule_start_date,
                    ccmcts.schedule_end_date schedule_end_date
                    FROM com_circular_set_master ccsm 
                    JOIN com_circular_multi_control_task_set ccmcts ON ccsm.id = ccmcts.circular_id
                    LEFT JOIN com_circular_task_master cctm ON ccsm.id = cctm.set_id AND cctm.deleted_at IS NULL
                    WHERE ccsm.is_active = 1 
                    AND ccsm.deleted_at IS NULL
                    AND ccsm.is_applicable = 1
                    AND ccmcts.deleted_at IS NULL
                    AND ccmcts.audit_unit_ids IS NOT NULL
                    AND ccmcts.header_ids IS NOT NULL
                    AND ccmcts.task_ids IS NOT NULL
                    AND ( (ccmcts.schedule_start_date BETWEEN '". $FROM_DATE ."' AND '". $TO_DATE ."') OR
                          (ccmcts.schedule_end_date BETWEEN '". $FROM_DATE ."' AND '". $TO_DATE ."') OR
                          ('". $FROM_DATE ."' BETWEEN ccmcts.schedule_start_date AND ccmcts.schedule_end_date) OR
                          ('". $TO_DATE ."' BETWEEN ccmcts.schedule_start_date AND ccmcts.schedule_end_date) )
                    GROUP BY ccsm.id, ccmcts.id";

                $circularData = get_all_data_query_builder(2, $model, 'com_circular_set_master', [], 'sql', $query);

                // print_r($circularData);
                // exit;

                // CIRCULAR GENERATE KEY
                if( is_array($circularData) && 
                    sizeof($circularData) > 0 )
                {
                    // sort date
                    $tempCircularData = $circularData;
                    $circularData = [];
                    $assignMasterData = [];
                    $auditUnitData = [];
                    $this -> data['circular_set_data'] = [];
                    $this -> data['need_select'] = true;

                    // sort array
                    foreach($tempCircularData as $cCircularData)
                    {
                        $circularData[ $cCircularData -> id ] = $cCircularData;

                        // set name
                        $this -> data['circular_set_data'][ $cCircularData -> name ] = $cCircularData -> name;

                        $assignMasterData[ $cCircularData -> ccmctsId ] = (object) [
                            'id' => $cCircularData -> ccmctsId,
                            'circular_id' => $cCircularData -> id,
                            'name' => $cCircularData -> ccmcts_set_name,
                            'audit_unit_ids' => $cCircularData -> ccmcts_audit_unit_ids,
                            'header_ids' => $cCircularData -> ccmcts_header_ids,
                            'task_ids' => $cCircularData -> ccmcts_task_ids,
                            'frequency' => $cCircularData -> frequency,
                            'reporting_date_1' => $cCircularData -> reporting_date_1,
                            'due_date_1' => $cCircularData -> due_date_1,
                            'reporting_date_2' => $cCircularData -> reporting_date_2,
                            'due_date_2' => $cCircularData -> due_date_2,
                            'otu_start_date' => $cCircularData -> otu_start_date,
                            'otu_end_date' => $cCircularData -> otu_end_date,
                            'schedule_start_date' => $cCircularData -> schedule_start_date,
                            'schedule_end_date' => $cCircularData -> schedule_end_date
                        ];

                        $cAssignedAuditUnit = (!empty($cCircularData -> ccmcts_audit_unit_ids)) ? explode(',', $cCircularData -> ccmcts_audit_unit_ids) : [];
                        
                        if(!empty($cAssignedAuditUnit)) // audit unit ids merged
                            $auditUnitData = array_merge($auditUnitData, $cAssignedAuditUnit);
                    }

                    $auditUnitData = array_values(array_unique($auditUnitData));

                    // unset array
                    unset($tempCircularData);

                    // function call
                    $comAssesDateArray = generate_multi_com_asses_array($circularData, $assignMasterData, [
                        'todayDate' => date($GLOBALS['dateSupportArray']['1']),
                        'auditUnitData' => $auditUnitData
                    ]);

                    $circularData = $comAssesDateArray['circular_data'];

                    // method call
                    $this -> data['db_audit_unit_data'] = $this -> getAllAuditUnitsData();

                    $model = $this -> model('ComplianceCircularAssesMasterModel');
                    // name, audit_unit_code, CONCAT(name, " - ( BR. " , audit_unit_code, " )") AS combined_name

                    $query = "SELECT cccm.*, aum.name, aum.audit_unit_code, CONCAT(aum.name, ' - ( BR. ' , aum.audit_unit_code, ' )') AS combined_audit_name FROM com_circular_compliance_master cccm JOIN audit_unit_master aum ON cccm.audit_unit_id = aum.id 
                    WHERE cccm.circular_id IN (". implode(',', array_keys($circularData)) .") 
                    AND cccm.deleted_at IS NULL 
                    AND cccm.com_period_from >= '" . $FROM_DATE . "'
                    AND cccm.com_period_to <= '" . $TO_DATE . "'";
                    $comAssesData = get_all_data_query_builder(2, $model, 'com_circular_compliance_master', [], 'sql', $query);

                    if( is_array($comAssesData) && 
                        sizeof($comAssesData) > 0 )
                    {
                        // find submitted data
                        $combinedBatchKey = [];

                        foreach($comAssesData as $cComAssesData)
                        {
                            if( array_key_exists($cComAssesData -> circular_id, $circularData) && 
                                isset($circularData[ $cComAssesData -> circular_id ] -> com_asses_data) && 
                                is_array($circularData[ $cComAssesData -> circular_id ] -> com_asses_data) && 
                                sizeof($circularData[ $cComAssesData -> circular_id ] -> com_asses_data) > 0)
                            {
                                $generateBulkKey = generate_bulk_batch_key_compliance_pro([
                                    'from' => $cComAssesData -> com_period_from,
                                    'to' => $cComAssesData -> com_period_to,
                                    'circular_id' => $cComAssesData -> circular_id,
                                    'task_set_id' => $cComAssesData -> task_set_id
                                ]);

                                if(array_key_exists($generateBulkKey, $circularData[ $cComAssesData -> circular_id ] -> com_asses_data))
                                {
                                    // push asses data
                                    $circularData[ $cComAssesData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['com_asses_data'][ $cComAssesData -> id ] = $cComAssesData;

                                    // push gen key
                                    if(!in_array($generateBulkKey, $combinedBatchKey))
                                        $combinedBatchKey[] = $generateBulkKey;

                                    // remove from assigned audit units // unset value // function call
                                    $circularData[ $cComAssesData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['assigned_audit_units'] = find_and_remove_index_array($circularData[ $cComAssesData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['assigned_audit_units'], $cComAssesData -> audit_unit_id);
                                }
                                else
                                {
                                    // genekey not exists create key
                                    $circularData[ $cComAssesData -> circular_id ] -> com_asses_data[ $generateBulkKey ] = [
                                        'com_period_from' => $cComAssesData -> com_period_from,
                                        'com_period_to' => $cComAssesData -> com_period_to,
                                        'frequency' => $cComAssesData -> com_period_to,
                                        'reporting_date' => $circularData[ $cComAssesData -> circular_id ] -> reporting_date_1,
                                        'due_date' => $cComAssesData -> com_period_to,
                                        'com_asses_data' => [],
                                        'assigned_audit_units' => [ $cComAssesData -> audit_unit_id ],
                                        'header_ids' => $cComAssesData -> header_ids,
                                        'task_ids' => $cComAssesData -> task_ids,
                                    ];
                                    
                                    $circularData[ $cComAssesData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['com_asses_data'][ $cComAssesData -> id ] = $cComAssesData;
                                }
                            }
                        }

                        if(!empty($combinedBatchKey))
                        {
                            // find send auth data
                            $model = $this -> model('ComplianceSubmitAuthorityModel');

                            $comSubmittedData = $model -> getAllSubmittedReport([
                                'where' => 'bulk_batch_key IN ("'. implode('","', $combinedBatchKey) .'") AND deleted_at IS NULL ORDER BY reporting_date DESC',
                                'params' => []
                            ]);

                            if(is_array($comSubmittedData) && sizeof($comSubmittedData) > 0)
                            {
                                foreach($comSubmittedData as $cSubmitData)
                                {
                                    $com_asses_ids = !empty($cSubmitData) ? explode(',', $cSubmitData -> com_asses_ids) : [];

                                    if( is_array($com_asses_ids) && 
                                        sizeof($com_asses_ids) > 0 &&
                                        array_key_exists($cSubmitData -> circular_id, $circularData) &&
                                        isset($circularData[ $cSubmitData -> circular_id ] -> com_asses_data) &&
                                        is_array($circularData[ $cSubmitData -> circular_id ] -> com_asses_data) &&
                                        sizeof($circularData[ $cSubmitData -> circular_id ] -> com_asses_data) > 0 &&
                                        isset($circularData[ $cSubmitData -> circular_id ] -> com_asses_data[ $cSubmitData -> bulk_batch_key ]) && 
                                        isset($circularData[ $cSubmitData -> circular_id ] -> com_asses_data[ $cSubmitData -> bulk_batch_key ]['com_asses_data']) &&
                                        is_array($circularData[ $cSubmitData -> circular_id ] -> com_asses_data[ $cSubmitData -> bulk_batch_key ]['com_asses_data'])
                                    )
                                    {
                                        // circular data exits
                                        foreach($com_asses_ids as $cAssesId)
                                        {
                                            if(array_key_exists(
                                                $cAssesId, 
                                                $circularData[ $cSubmitData -> circular_id ] -> com_asses_data[ $cSubmitData -> bulk_batch_key ]['com_asses_data']
                                            ))
                                            {
                                                if(!isset($circularData[ $cSubmitData -> circular_id ] -> com_asses_data[ $cSubmitData -> bulk_batch_key ]['com_asses_data'][ $cAssesId ] -> submitted_report_date))   
                                                {
                                                    $circularData[ $cSubmitData -> circular_id ] -> com_asses_data[ $cSubmitData -> bulk_batch_key ]['com_asses_data'][ $cAssesId ] -> submitted_report_date = $cSubmitData -> reporting_date;
                                                }
                                            }
                                        }
                                    }
                                }
                            }                           
                        }
                    }

                    // print_r($circularData);
                    // exit;

                    // LOOP DATA AND REMOVE NOT IN PERIOD 
                    foreach($circularData as $cCircularId => $cCircularData)
                    {
                        if(is_array($cCircularData -> com_asses_data) && sizeof($cCircularData -> com_asses_data) > 0)
                        {
                            foreach($cCircularData -> com_asses_data as $cGenKey => $cGenData)
                            {
                                // gen data
                                $isFromOutsideRange = ($cGenData['com_period_from'] < $FROM_DATE || $cGenData['com_period_from'] > $TO_DATE);
                                $isToOutsideRange = ($cGenData['com_period_to'] < $FROM_DATE || $cGenData['com_period_to'] > $TO_DATE);

                                if ( !in_array($cGenData['frequency'], [5]) && $isFromOutsideRange && $isToOutsideRange) // unset var
                                    unset($circularData[ $cCircularId ] -> com_asses_data[ $cGenKey ]);
                            }

                            // unset circular
                            if( empty($cCircularData -> com_asses_data) )
                                unset($circularData[ $cCircularId ]);
                        }
                        // else // remove circular
                            // unset($circularData[ $cCircularId ]);

                        // 
                        // if assign data not found
                        if( empty($cCircularData -> assign_master) )
                            unset($circularData[ $cCircularId ]);
                    }

                    // print_r($circularData);
                    // exit;
                }

                $this -> data['circular_data'] = $circularData;
            }

        });        

        // method call
        $this -> data['db_authority_data'] = $this -> getAllComplianceAuthority();
        $this -> data['db_audit_unit_data'] = $this -> getAllAuditUnitsData();

        // method call
        $this -> data['frequency_data'] = $this -> convertFrequencySelect();

        $this -> data['status_data'] = [
            'NOT STARTED' => 'NOT STARTED',
            'PENDING' => 'PENDING',
            'RE COMPLIANCE' => 'RE COMPLIANCE',
            'IN REVIEW' => 'IN REVIEW',
            'COMPLETED' => 'COMPLETED',
            'DELAYED' => 'DELAYED',
            // 'CANCELLED' => 'CANCELLED'
        ];
        
        $this -> data['page'] = 'A4L';
        $this -> data['need_calender'] = true;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request
        ]);
    }

    // 4. Compliance Summary Report
    public function complianceSummaryReport()
    {
        $this -> generateMe('complianceSummaryReport');

        $this -> data['db_data'] = [
            ['2024-09-01', 'RESERVE BANK OF INDIA (RBI)', 'Circular No. 123 (DLM)', 'PASSED', 'KYC, AML, Data Privacy, Risk Mgmt.', '30', '30', '0', '0'],
            ['2024-09-01', 'GST DEPARTMENT', 'GSTR-1', 'PARTIALLY PASSED', 'KYC, AML, Data Privacy', '25', '22', '3', '0'],
            ['2024-09-01', 'INCOME TAX (ITR)', 'TDS Deposit', 'FAILED', 'AML, Data Privacy, Cybersecurity', '20', '5', '0', '15'],
            ['2024-09-01', 'RESERVE BANK OF INDIA (RBI)', 'Circular No. 456 (ALM Return)', 'PASSED', 'KYC, AML, Risk Mgmt., Cybersecurity', '18', '18', '0', '0'],
            ['2024-09-01', 'STATUTORY AUDIT', '2023-24', 'PARTIALLY PASSED', 'Data Privacy, Cybersecurity', '15', '12', '3', '0']
        ];

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
        ]);
    }

    // 5. Non-compliance Escalation Report
    public function nonComplianceEscalationReport()
    {
        $this -> generateMe('nonComplianceEscalationReport');

        $this -> data['db_data'] = [
            ['NC-001', 'RBI/2023-24/117', 'AML', '2024-08-25', 'Compliance Department', 'Critical', '2024-08-27', 'Compliance Head', 'Pending', '2024-09-10'],
            ['NC-002', 'RBI/2023-24/117', 'AML', '2024-08-25', 'Compliance Department', 'Critical', '2024-08-27', 'Compliance Head', 'Pending', '2024-09-10'],
            ['NC-002', 'RBI/2023-24/87', 'Data Privacy', '2024-08-20', 'IT Department', 'Critical', '2024-08-22', 'Chief Technology Officer', 'Resolved', '2024-08-29'],
            ['NC-003', 'ITR-5', 'Cybersecurity', '2024-08-28', 'Risk Management', 'Critical', '2024-08-30', 'Risk Management Head', 'Pending', '2024-09-05'],
            ['NC-004', 'RBI/2024-25/18', 'KYC', '2024-08-18', 'Business Operations', 'Critical', '2024-08-20', 'Head of Operations', 'Resolved', '2024-08-25'],
            ['NC-005', 'GSTR-1', 'AML', '2024-08-21', 'Compliance Department', 'Critical', '2024-08-23', 'Compliance Head', 'Resolved', '2024-09-01']
        ];

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
        ]);
    }

}

?>