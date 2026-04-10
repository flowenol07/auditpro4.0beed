<?php

namespace Controllers\CompliancePro;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Except;
use Core\Notifications;
use Core\DBCommonFunc;

class Dashboard extends Controller {

    public $me = null, $empType, $empId, $data, $request, $year, $dateFormat, $todayDate;
    public $comAssesModel, $comTaskSetModel;

    public function __construct($me) {

        $this -> me = $me;

        $this -> request = new Request();

        $this -> empType = Session::get('emp_type');
        $this -> empId = Session::get('emp_id');
        
        $this -> comAssesModel = $this -> model('ComplianceCircularAssesMasterModel');
        $this -> comTaskSetModel = $this -> model('ComplianceCircularTaskSetModel');
        $this -> dateFormat = $GLOBALS['dateSupportArray']['1'];
        $this -> todayDate = date($this -> dateFormat);

        // year model
        $yearModel = $this -> model('YearModel');
        $this -> year = $yearModel -> getSingleYear(['where' => 'deleted_at IS NULL ORDER BY year']);
        $this -> data['year_data'] = $this -> year;
    }

    private function generateMultiComAssesArray($circularData, $assignedCircularData, $type = 1)
    {
        // function call
        $extra = [ 'type' => $type, 'todayDate' => $this -> todayDate ];

        if(isset($this -> data['auditUnitData']) && is_object($this -> data['auditUnitData']))
            $extra['auditUnitData'] = $this -> data['auditUnitData'];

        return generate_multi_com_asses_array($circularData, $assignedCircularData, $extra);
    }

    private function checkComplianceAdd()
    {
        // get all active circulars // query update 21.10.2024
        $query = "SELECT ccsm.*, 
        ccmcts.id ccmctsId,
        ccmcts.audit_unit_ids ccmcts_audit_unit_ids,
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
        WHERE ccsm.is_active = 1 
        AND ccsm.deleted_at IS NULL
        AND ccsm.is_applicable = 1
        AND ccmcts.is_active = 1
        AND ccmcts.deleted_at IS NULL
        AND ccmcts.header_ids IS NOT NULL 
        AND ccmcts.task_ids IS NOT NULL 
        AND FIND_IN_SET('". $this -> data['auditUnitData'] -> id ."', ccmcts.audit_unit_ids) > 0
        AND ('". $this -> todayDate ."' BETWEEN ccmcts.schedule_start_date AND ccmcts.schedule_end_date)
        GROUP BY ccsm.id, ccmcts.id";

        $circularData = get_all_data_query_builder(2, 
            $this -> comTaskSetModel, 
            $this -> comTaskSetModel -> getTableName(), [ ], 
            'sql', $query);

        $genAssesData = [];
        $getCircularIds = [];

        if( is_array($circularData) && 
            sizeof($circularData) > 0 )
        {
            $tempCircularData = $circularData;
            $circularData = [];
            $assignMasterData = [];
            $taskIdsData = [];

            // sort array
            foreach($tempCircularData as $cCircularData)
            {
                $cTaskIdsData = !empty($cCircularData -> ccmcts_task_ids) ? explode(',', $cCircularData -> ccmcts_task_ids) : [];

                // another filter added
                if( !empty($cTaskIdsData) && 
                    !empty($cCircularData -> ccmcts_header_ids) && 
                    !empty($cCircularData -> ccmcts_audit_unit_ids) )
                {
                    $assignMasterData[ $cCircularData -> ccmctsId ] = (object) [
                        'id' => $cCircularData -> ccmctsId,
                        'circular_id' => $cCircularData -> id,
                        'audit_unit_ids' => $cCircularData -> ccmcts_audit_unit_ids,
                        // 'cco_id' => $cCircularData -> assign_cco_id,
                        'header_ids' => $cCircularData -> ccmcts_header_ids,
                        'task_ids' => $cCircularData -> ccmcts_task_ids,
                        'task_ids_explode' => [],
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

                    // explode task data
                    if(!empty($assignMasterData[ $cCircularData -> ccmctsId ] -> task_ids))
                        $assignMasterData[ $cCircularData -> ccmctsId ] -> task_ids_explode = explode( ',', $assignMasterData[ $cCircularData -> ccmctsId ] -> task_ids );

                    // unset vals
                    unset(
                        $cCircularData -> ccmctsId,
                        $cCircularData -> ccmcts_audit_unit_ids,
                        $cCircularData -> ccmcts_header_ids,
                        $cCircularData -> ccmcts_task_ids,
                        $cCircularData -> frequency,
                        $cCircularData -> reporting_date_1,
                        $cCircularData -> due_date_1,
                        $cCircularData -> reporting_date_2,
                        $cCircularData -> due_date_2,
                        $cCircularData -> otu_start_date,
                        $cCircularData -> otu_end_date,
                        $cCircularData -> schedule_start_date,
                        $cCircularData -> schedule_end_date,
                    );

                    $circularData[ $cCircularData -> id ] = $cCircularData;

                    // mix task ids
                    $taskIdsData = array_merge($taskIdsData, $cTaskIdsData);
                }
            }

            // unset array
            unset($tempCircularData);

            if( sizeof($circularData) > 0 && 
                sizeof($taskIdsData) > 0 && 
                sizeof($assignMasterData) > 0 )
            {
                // get unique data
                $taskIdsData = array_values(array_unique($taskIdsData));

                // get all tasks data
                $query = "SELECT 
                    cctm.*, 
                    cchm.id cchm_header_id                        
                FROM 
                    com_circular_header_master cchm
                LEFT JOIN 
                    com_circular_task_master cctm ON cchm.id = cctm.header_id
                WHERE 
                    cctm.id IN (". implode(",", $taskIdsData) .") 
                    AND cchm.is_active = 1 AND cchm.deleted_at IS NULL
                    AND cctm.is_active = 1 AND cctm.deleted_at IS NULL";

                $model = $this -> model('ComplianceCircularHeaderModel');
                $taskData = get_all_data_query_builder(2, $model, $model -> getTableName(), [], 'sql', $query);

                if(is_array($taskData) && sizeof($taskData) > 0)
                {
                    $tmpTaskData = $taskData;
                    $taskData = [ 'assign_data' => [], 'task_ids' => [] ];

                    foreach($tmpTaskData as $cTaskData)
                    {
                        foreach($assignMasterData as $cAssignId => $cAssignData)
                        {
                            if( is_array($cAssignData -> task_ids_explode) && 
                                in_array($cTaskData -> id, $cAssignData -> task_ids_explode) )
                            {
                                if(!array_key_exists($cAssignData -> id, $taskData['assign_data']))
                                    $taskData['assign_data'][ $cAssignData -> id ] = [
                                        'assign_id' => $cAssignData -> id,
                                        'circular_id' => $cTaskData -> set_id,
                                        'header_ids' => [],
                                        'task_data' => [],
                                    ];
                                
                                // insert header id
                                if(!in_array($cTaskData -> header_id, $taskData['assign_data'][ $cAssignData -> id ]['header_ids']))
                                    $taskData['assign_data'][ $cAssignData -> id ]['header_ids'][ ] = $cTaskData -> header_id;
                                
                                // insert task
                                if(!array_key_exists($cTaskData -> id, $taskData['assign_data'][ $cAssignData -> id ]['task_data']))
                                    $taskData['assign_data'][ $cAssignData -> id ]['task_data'][ $cTaskData -> id ] = $cTaskData;

                                // push task ids
                                if(!in_array($cTaskData -> id, $taskData['task_ids']))
                                    $taskData['task_ids'][] = $cTaskData -> id;
                            }
                        }
                    }

                    unset($tmpTaskData);

                    if(isset($taskData['assign_data']) && sizeof($taskData['assign_data']) > 0)
                    {
                        foreach($taskData['assign_data'] as $cAssignId => $cAssignData)
                        {
                            if( (isset($cAssignData['header_ids']) && empty($cAssignData['header_ids'])) || 
                                (isset($cAssignData['task_data']) && empty($cAssignData['task_data'])) )
                                unset($taskData['assign_data'][ $cAssignId ]);
                        }
                    }
                }

                $comAssesDateArray = $this -> generateMultiComAssesArray($circularData, $assignMasterData, 2);
                $circularData = $comAssesDateArray['circular_data'];

                // print_r($taskData);
                // exit;

                foreach($circularData as $cCircularData)
                {
                    if( isset($cCircularData -> com_asses_data) && 
                        is_array($cCircularData -> com_asses_data) && 
                        sizeof($cCircularData -> com_asses_data) > 0)
                    {
                        // has asses data
                        foreach($cCircularData -> com_asses_data as $cGenKey => $cCircularAssesData)
                        {
                            // push circular id
                            $cCircularAssesData['circular_id'] = $cCircularData -> id;
                            
                            if( isset($taskData['assign_data']) && 
                                is_array($taskData['assign_data']) && 
                                array_key_exists($cCircularAssesData['task_set_id'], $taskData['assign_data']))
                            {
                                if( is_array($this -> data['assesData']) &&
                                    sizeof($this -> data['assesData']) > 0)
                                {
                                    $assesFound = false;

                                    foreach($this -> data['assesData'] as $cAssId => $cAssData)
                                    {
                                        if( $cAssData -> circular_id == $cCircularData -> id &&
                                            $cAssData -> com_period_from == $cCircularAssesData['com_period_from'] &&
                                            $cAssData -> com_period_to == $cCircularAssesData['com_period_to'] &&
                                            $cAssData -> bulk_batch_key == $cGenKey )
                                            $assesFound = true;
                                    }

                                    if(!$assesFound)
                                        $genAssesData[ ] = $cCircularAssesData;
                                }
                                else // push data
                                    $genAssesData[ ] = $cCircularAssesData;
                            }
                        }
                    }
                }
            }
        }

        // print_r($genAssesData);
        // exit;

        if(sizeof($genAssesData) > 0)
        {
            // insert multiple records
            $insertMulti = [];
            $ansModel = $this -> model('ComplianceCircularAnswerDataModel');

            foreach($genAssesData as $cAssesData)
            {                
                $generateBulkKey = generate_bulk_batch_key_compliance_pro([
                    'from' => date('Ymd', strtotime($cAssesData['com_period_from'])),
                    'to' => date('Ymd', strtotime($cAssesData['com_period_to'])),
                    'circular_id' => $cAssesData['circular_id'],
                    'task_set_id' => $cAssesData['task_set_id']
                ]);

                $insertComplianceAsses = [
                    "year_id" => $this -> year -> id,
                    "audit_unit_id" => $this -> data['auditUnitData'] -> id,
                    "frequency_id" => $cAssesData['frequency'],
                    "cco_emp_id" => $this -> empId,
                    "branch_head_id" => $this -> data['auditUnitData'] -> branch_head_id,
                    "branch_subhead_id" => $this -> data['auditUnitData'] -> branch_subhead_id,
                    "multi_compliance_ids" => $this -> data['auditUnitData'] -> multi_compliance_ids,
                    "com_period_from" => $cAssesData['com_period_from'],
                    "com_period_to" => $cAssesData['com_period_to'],
                    "com_status_id" => 1,
                    "compliance_start_date" => $this -> todayDate,
                    "compliance_due_date" => $cAssesData['due_date'],
                    "batch_key" => generate_batch_key('C'),
                    "bulk_batch_key" => $generateBulkKey,
                    "task_set_id" => $cAssesData['task_set_id'],
                    "circular_id" => $cAssesData['circular_id'],
                    "header_ids" => implode(',', $cAssesData['header_ids']),
                    "task_ids" => implode(',', $cAssesData['task_ids'])
                ];

                // print_r($insertComplianceAsses);
                // exit;

                // insert data
                $result = $this -> comAssesModel::insert($this -> comAssesModel -> getTableName(), $insertComplianceAsses);
                $lastInsertId = null;

                if($result)
                {
                    $lastInsertId = $this -> comAssesModel::lastInsertId();
                    $insertMultiTask = [];

                    // create answers multi array
                    foreach($taskData['assign_data'][ $cAssesData['task_set_id'] ]['task_data'] as $cTaskId => $cTaskData)
                    {
                        $insertMultiTask[] = [
                            "com_master_id" => $lastInsertId,
                            "header_id" => $cTaskData -> header_id,
                            "task_id" => $cTaskData -> id,
                            "dump_id" => 0,
                            "answer_given" => trim_str($cTaskData -> answer_given),
                            "cco_emp_id" => $this -> empId,
                            "compliance_status_id" => 1,
                            "risk_category_id" => $cTaskData -> risk_category_id,
                            "business_risk" => $cTaskData -> business_risk,
                            "control_risk" => $cTaskData -> control_risk,
                            "batch_key" => $insertComplianceAsses['batch_key']
                        ];
                    }

                    $result2 = $ansModel::insertMultiple($ansModel -> getTableName(), $insertMultiTask);

                    if(!$result2)
                        $result = false;
                }

                // Task not inserted // remove assesment
                if(!$result && !empty($lastInsertId))
                {
                    $this -> comAssesModel::delete(
                        $this -> comAssesModel -> getTableName(), [ 
                            'where' => 'id = :id',
                            'params' => [ 'id' => $lastInsertId ]
                        ]);
                }
            }

            // if($result) // method call
            $this -> getAssesData();
        }
    }

    private function getAssesData($extra = []) {

        if(is_object($this -> data['year_data']))
        {
            $extra['year_data'] = $this -> data['year_data'] -> id;

            // function call
            $this -> data['assesData'] = get_compliance_asses_data_mix($this, $extra)['asses_data'];
        }
    }

    private function generateCircularAuthorityArray() {

        $model = $this -> model('ComplianceCircularAuthorityModel');
        $table = $model -> getTableName();

        $circularAuthData = get_all_data_query_builder(2, $model, $table, [], 'sql', "SELECT id, name, updated_at FROM ". $table ." WHERE is_active = 1 AND deleted_at IS NULL");

        $this -> data['circular_authority_data'] = [];
        
        if( is_array($circularAuthData) && 
            sizeof($circularAuthData) > 0 )
        {
            foreach($circularAuthData as $cAuthData)
            {
                $this -> data['circular_authority_data'][ $cAuthData -> id ] = $cAuthData;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_applicable_circulars = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_tasks_assign = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_tasks_completed = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_tasks_pending = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_tasks_overdue = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_penalty = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_not_started = 0;
                $this -> data['circular_authority_data'][ $cAuthData -> id ] -> total_compliance_completed = 0;
            }
        }

        unset($circularAuthData);
    }

    public function index() {

        if(!is_object($this -> year))
        {
            Except::exc_404( Notifications::getNoti('yearDataNotFound') );
            exit;
        }

        switch($this -> empType)
        {
            case '3' : {

                // method call
                $this -> getAuditUnitData();
                $this -> getAssesData([ 'audit_unit_id' => $this -> data['auditUnitData'] -> id ]);

                // method call
                $this -> checkComplianceAdd();
                $this -> data['data_container'] = true;

                $this -> data['total_data'] = [ 
                    'branches' => 0, 'ho' => 0, 'pending' => 0, 'overdue' => 0, 
                    'total_circular' => 0, 'total_appliable_circular' => 0, 'total_appliable_tasks' => 0 ];

                // method call
                $this -> generateCircularAuthorityArray();

                $this -> me -> pageHeading = 'Other Compliance Dashboard';

                // method call
                $this -> dashboardAuthorityReport(2);

                // print_r($this -> data);
                // exit;

                // load view
                return return2View($this, $this -> me -> viewDir . 'compliance-dashboard', [ 'request' => $this -> request ]);
                break;
            }

            case '6' : {

                $this -> data['data_container'] = true;
                $this -> data['need_dashboard'] = true;
                $this -> me -> menuKey = 'complianceProDashboard';

                // Load the model
                $model = $this -> model('AuditUnitModel');
                $table = $model -> getTableName();

                $this -> data['circular_types_data'] = [];
                $this -> data['total_data'] = [ 
                    'branches' => 0, 'ho' => 0, 'pending' => 0, 'overdue' => 0, 
                    'total_circular' => 0, 'total_appliable_circular' => 0, 'total_appliable_tasks' => 0 ];

                // Fetch total branch count
                $this -> data['total_data']['branches'] = get_all_data_query_builder(1, $model, $table, [
                    'where' => 'section_type_id = 1 AND deleted_at IS NULL', 'params' => []
                ], 'sql', "SELECT COUNT(*) total_branch FROM " . $table) -> total_branch;

                // Fetch total head office count
                $this -> data['total_data']['ho'] = get_all_data_query_builder(1, $model, $table, [
                    'where' => 'section_type_id != 1 AND deleted_at IS NULL', 'params' => []
                ], 'sql', "SELECT COUNT(*) total_head_office FROM " . $table) -> total_head_office;

                // method call
                $this -> dashboardAuthorityReport();
                
                // Load the view with the data
                return return2View($this, $this -> me -> viewDir . 'cco-dashboard', [
                    'data' => $this -> data
                ]);

                break;
            } 

            default : {

                //no data
                Except::exc_access_restrict( );
			    exit;
                
            }
        }
    }

    private function dashboardAuthorityReport($type = 1) {

        // function call
        $this -> generateCircularAuthorityArray();
                
        // get all circulars
        $model = $this -> model('ComplianceCircularSetModel');
        $table = $model -> getTableName();

        $circularData = get_all_data_query_builder(2, $model, $table, [], 'sql', "SELECT ccsm.*, COUNT(cctm.id) AS total_tasks FROM ". $table ." ccsm LEFT JOIN com_circular_task_master cctm ON ccsm.id = cctm.set_id WHERE ccsm.is_active = 1 AND ccsm.deleted_at IS NULL AND cctm.is_active = 1 AND cctm.deleted_at IS NULL GROUP BY ccsm.id");

        if(is_array($circularData) && sizeof($circularData) > 0)
        {
            // function call
            $circularData = generate_data_assoc_array($circularData, 'id');

            // ---------------------------- GET CIRCULAR TASK SET DATA ----------------------------
            $model = $this -> model('ComplianceCircularTaskSetModel');

            $whereArray = [
                'where' => 'circular_id IN ('. implode(',', array_keys($circularData)) .') AND (:todayDate BETWEEN schedule_start_date AND schedule_end_date) AND deleted_at IS NULL AND header_ids IS NOT NULL AND task_ids IS NOT NULL ',
                'params' => [ 'todayDate' => $this -> todayDate ]
            ];

            // assign audit unit for compliance
            if($type == 2) 
                $whereArray['where'] .= ' AND FIND_IN_SET("'. $this -> data['auditUnitData'] -> id .'", audit_unit_ids) > 0';
            else
                $whereArray['where'] .= ' AND audit_unit_ids IS NOT NULL';

            $assignedCircularData = $model -> getAllCircularTaskSetData($whereArray);

            // method call
            $comAssesDateArray = $this -> generateMultiComAssesArray($circularData, $assignedCircularData, $type);
            $circularData = $comAssesDateArray['circular_data'];

            // ---------------------------- GET CIRCULAR TASK SET DATA ----------------------------

            if(is_array($circularData) && sizeof($circularData) > 0):

                // ---------------------------- GET COMPLIANCE ASSES DATA ----------------------------
                $model = $this -> model('ComplianceCircularAssesMasterModel');
                $table = $model -> getTableName();

                $whereArray = [
                    'where' => 'circular_id IN ('. implode(',', array_keys($circularData)) .') AND com_period_from >= :com_period_from AND com_period_to <= :com_period_to AND deleted_at IS NULL',
                    'params' => [
                        'com_period_from' => $comAssesDateArray['start_date'],
                        'com_period_to' => $comAssesDateArray['end_date'],
                    ]
                ];

                // assign audit unit for compliance
                if($type == 2) $whereArray['where'] .= ' AND audit_unit_id = "'. $this -> data['auditUnitData'] -> id .'"';

                $complianceAssesData = get_all_data_query_builder(2, $model, $table, $whereArray, 'sql', "SELECT id, audit_unit_id, com_period_from, com_period_to, compliance_due_date, com_status_id, task_set_id, circular_id, header_ids, task_ids FROM ". $table);

                if(is_array($complianceAssesData) && sizeof($complianceAssesData) > 0)
                {
                    foreach($complianceAssesData as $cAssData)
                    {
                        if(isset($circularData[ $cAssData -> circular_id ] -> com_asses_data))
                        {
                            // generate key
                            $generateBulkKey = generate_bulk_batch_key_compliance_pro([
                                'from' => $cAssData -> com_period_from,
                                'to' => $cAssData -> com_period_to,
                                'circular_id' => $cAssData -> circular_id,
                                'task_set_id' => $cAssData -> task_set_id
                            ]);

                            if(!array_key_exists($generateBulkKey, $circularData[ $cAssData -> circular_id ] -> com_asses_data))
                            {
                                $comAssesData = get_from_to_date_on_frequency(
                                    $circularData[ $cAssData -> circular_id ] -> frequency, 
                                    $circularData[ $cAssData -> circular_id ], 
                                    $cAssData -> com_period_from
                                );

                                $comAssesData['com_asses_data'] = [];
                                $comAssesData['assigned_audit_units'] = $comAssesDateArray['assignAuditUnits'];
                                $comAssesData['header_ids'] = explode(',', $cAssData -> header_ids);
                                $comAssesData['task_ids'] = explode(',', $cAssData -> task_ids);

                                // push data
                                $circularData[ $cAssData -> circular_id ] -> com_asses_data[ $generateBulkKey ] = $comAssesData;
                            }

                            // push data
                            $circularData[ $cAssData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['com_asses_data'][ $cAssData -> id ] = $cAssData;

                            // unset value // function call
                            $circularData[ $cAssData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['assigned_audit_units'] = find_and_remove_index_array($circularData[ $cAssData -> circular_id ] -> com_asses_data[ $generateBulkKey ]['assigned_audit_units'], $cAssData -> audit_unit_id);
                        }
                    }
                }
                // ---------------------------- GET COMPLIANCE ASSES DATA ----------------------------

            endif;

            // print_r($circularData);
            // exit;

            // remove circulars
            if(is_array($circularData) && sizeof($circularData) > 0)
            {
                foreach($circularData as $cCircularId => $cCircularData)
                {
                    // incremenet circular
                    $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_applicable_circulars++;

                    // add penalty amount
                    $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_penalty += get_decimal($cCircularData -> penalty_amt, 2);
                    
                    if( isset($cCircularData -> com_asses_data) && 
                        is_array($cCircularData -> com_asses_data) && 
                        sizeof($cCircularData -> com_asses_data) > 0)
                    {
                        foreach($cCircularData -> com_asses_data as $cGenKey => $cAssesData)
                        {                                    
                            if(sizeof($cAssesData['com_asses_data']) > 0)
                            {
                                foreach($cAssesData['com_asses_data'] as $cComAssesId => $cComAssesData)
                                {
                                    $cTasksIds = !empty($cComAssesData -> task_ids) ? explode(',', $cComAssesData -> task_ids) : [];
                                    $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_assign += sizeof($cTasksIds);

                                    // find completed assesment
                                    if( $cComAssesData -> com_status_id == 4 )
                                    {
                                        $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_compliance_completed++;
                                        $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_completed += sizeof($cTasksIds);
                                    }

                                    // check over due compliance
                                    if( in_array($cComAssesData -> com_status_id, [1,2,3]))
                                    {
                                        $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_pending += sizeof($cTasksIds);

                                        if( strtotime($cComAssesData -> compliance_due_date) < strtotime($this -> todayDate) )
                                            $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_overdue += sizeof($cTasksIds);
                                    }
                                }
                            }

                            if(sizeof($cAssesData['assigned_audit_units']) > 0)
                            {
                                // over due count
                                $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_not_started += sizeof($cAssesData['assigned_audit_units']);

                                if(sizeof($cAssesData['task_ids']) > 0)
                                {
                                    $totTasks = sizeof($cAssesData['task_ids']) *  sizeof($cAssesData['assigned_audit_units']);

                                    // pending task
                                    $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_pending += $totTasks;
                                    $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_assign += $totTasks;

                                    // check overdue
                                    if (strtotime($cAssesData['due_date']) < strtotime($this -> todayDate))
                                        $this -> data['circular_authority_data'][ $cCircularData -> authority_id ] -> total_tasks_overdue += $totTasks;
                                }
                            }
                        }
                    }                                
                    else // remove circular from array
                        unset($circularData[ $cCircularId ]);
                }
            }
        }
    }

    public function complianceAuthority($getRequest) {

        if(!in_array($this -> empType, [3]))
        {
            // no data
            Except::exc_access_restrict( );
            exit;
        }

        $comAuthId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';
        $this -> data['authorityData'] = null;

        // check authority exists or not
        if( !empty($comAuthId) )
        {
            $model = $this -> model('ComplianceCircularAuthorityModel');

            $this -> data['authorityData'] = $model -> getSingleCircularAuthority([
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $comAuthId ]
            ]);
        }

        if(!is_object($this -> data['authorityData']))
        {
            Except::exc_404('errorFinding');
            exit;
        }

        $this -> getAuditUnitData();

        $this -> me -> pageHeading = 'View Compliance';
        $this -> data['data_container'] = true;

        // method call
        $this -> getAssesData([
            'audit_unit_id' => $this -> data['auditUnitData'] -> id,
            'authority_id' => $this -> data['authorityData'] -> id
        ]);

        // Load the view with the data
        return return2View($this, $this -> me -> viewDir . 'compliance-view-index', [
            'data' => $this -> data
        ]);
    }

    private function getAuditUnitData() {

        // audit unit data
        $auditUnitModel = $this -> model('AuditUnitModel');

        $this -> data['auditUnitData'] = $auditUnitModel -> getSingleAuditUnit([
            'where' => '(
                branch_head_id = :branch_head_id 
                OR branch_subhead_id = :branch_subhead_id) 
                AND is_active = 1 
                AND deleted_at IS NULL',
            'params' => [ 'branch_head_id' => $this -> empId, 'branch_subhead_id' => $this -> empId ]
            ]
        );

        if(empty($this -> data['auditUnitData']))
        {
            Except::exc_404( Notifications::getNoti('errorFinding'), 1);
            exit;
        }
    }
}

?>