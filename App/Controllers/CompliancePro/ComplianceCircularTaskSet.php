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

class ComplianceCircularTaskSet extends Controller  {

    public $me = null, $data, $request, $circularId, $empId, $taskSetId;
    public $circularTaskSetModel,$circular_Id, $frequency;

    public function __construct($me) {

        $this -> me = $me;

        // top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('complianceCircularTaskSet') . '/add' ],
        ];

        // request object created
        $this -> request = new Request();

        $this -> circularTaskSetModel = $this -> model('ComplianceCircularTaskSetModel');

        // get all active circulars
        $model = $this -> model('ComplianceCircularSetModel');
        $table = $model -> getTableName();

        $select = " SELECT ccs.id, ccs.authority_id, ccs.ref_no, ccs.name, ccs.circular_date, ccs.is_applicable, 
        COALESCE(cca.name, '". ERROR_VARS['notFound'] ."') AS authority FROM 
        ". $table ." ccs LEFT JOIN com_circular_authority cca ON ccs.authority_id = cca.id";

        $this -> data['circularData'] = get_all_data_query_builder(2, $model, $table, [ 'where' => 'ccs.is_applicable = 1 AND ccs.is_active = 1 AND ccs.deleted_at IS NULL', 'params' => [] ], 'sql', $select);
        $this -> data['circularData'] = generate_data_assoc_array($this -> data['circularData'], 'id');

        $this -> data['init_frequency'] = COMPLIANCE_PRO_ARRAY['compliance_frequency'];
        $this -> empId = Session::get('emp_id');
    }

    private function validateData($methodType = 'add', $setId = '')
    {
        $validationArray = [
            'circular_id' => 'required|array_key[circular_array, selectCircularError]',
            'name' => 'required',
            'frequency' => 'required|array_key[init_frequency_array, circularFrequency]',
            'schedule_start_date' => 'required|regex[dateRegex, dateError]',
            'schedule_end_date' => 'required|regex[dateRegex, dateError]',
            'reporting_date_1' => 'required|regex[dateRegex, dateError]',
            'due_date_1' => 'required|regex[dateRegex, dateError]'
        ];

        $fortNight = false;

        if( $this -> request -> has('frequency') && $this -> request -> input('frequency') == 1 )
        {
            $validationArray['reporting_date_2'] = 'required|regex[dateRegex, dateError]';
            $validationArray['due_date_2'] = 'required|regex[dateRegex, dateError]';
            $fortNight = true;
        }

        Validation::validateData($this -> request, $validationArray, [
            'init_frequency_array' => $this -> data['init_frequency'],
            'circular_array' => $this -> data['circularData'],
        ]);

        // check FY date error
        if(!($this -> request -> input( 'error' ) > 0))
        {
            $noti = new \Core\Notifications; // function call
            date_validation_helper($this -> request, $validationArray, $noti, ['schedule_start_date', 'schedule_end_date']);
        }

        if(!($this -> request -> input( 'error' ) > 0))
        {
            $due_date_1 = $this -> request -> input('due_date_1');
            $due_date_2 = $this -> request -> input('due_date_2');
            $reporting_date_1 = $this -> request -> input('reporting_date_1');
            $reporting_date_2 = $this -> request -> input('reporting_date_2');

            $schedule_start_date = $this -> request -> input('schedule_start_date');
            $schedule_end_date = $this -> request -> input('schedule_end_date');

            $circular_date = $this -> data['circularData'][ $this -> request -> input('circular_id') ] -> circular_date;

            if ( !(strtotime($schedule_start_date) < strtotime($schedule_end_date)) )
            {
                $this -> request -> setInputCustom( 'error', 1);
                $this -> request -> setInputCustom( 'schedule_end_date_err', Notifications::getNoti('endDateGratorError'));
            }

            // has no error // check reporting_date_1 && due_date_1
            if ( !(strtotime($due_date_1) < strtotime($reporting_date_1)) )
            {
                $this -> request -> setInputCustom( 'error', 1);
                $this -> request -> setInputCustom( 'due_date_1_err', Notifications::getNoti('dueDateEarlierError'));
            }

            if($fortNight && !(strtotime($due_date_2) < strtotime($reporting_date_2)) )
            {
                $this -> request -> setInputCustom( 'error', 1);
                $this -> request -> setInputCustom( 'due_date_2_err', Notifications::getNoti('dueDateEarlierError'));
            }

            // reporting_date_1 must grater than circular_date
            if ( !( strtotime($reporting_date_1) > strtotime($circular_date) ) )
            {
                $this -> request -> setInputCustom( 'error', 1);
                $this -> request -> setInputCustom( 'reporting_date_1_err', Notifications::getNoti('reportingDateEarlierError'));
            }
            
            if ( $fortNight && !( strtotime($reporting_date_2) > strtotime($circular_date) ) )
            {
                $this -> request -> setInputCustom( 'error', 1);
                $this -> request -> setInputCustom( 'reporting_date_2_err', Notifications::getNoti('reportingDateEarlierError'));
            }
        }

        // validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    private function postArray($methodType = 'add')
    {
        $dataArray = array(
            'circular_id' => $this -> request -> input('circular_id'),
            'name' => trim_str($this -> request -> input('name')),
            'description' => trim_str($this -> request -> input('description')),
            'schedule_start_date' => $this -> request -> input('schedule_start_date'),
            'schedule_end_date' => $this -> request -> input('schedule_end_date'),
            'frequency' => $this -> request -> input('frequency'),
            'reporting_date_1' => $this -> request -> input('reporting_date_1'),
            'due_date_1' => $this -> request -> input('due_date_1'),
            'admin_id' => Session::get('emp_id'),
        );

        if($dataArray['frequency'] == 1) // 15 days freq
        {
            $dataArray['reporting_date_2'] = $this -> request -> input('reporting_date_2');
            $dataArray['due_date_2'] = $this -> request -> input('due_date_2');
        }
        else if($dataArray['frequency'] == 6) // one time use
        {
            $dataArray['otu_start_date'] = $dataArray['schedule_start_date'];
            $dataArray['otu_end_date'] = $dataArray['schedule_end_date'];
        }

        return $dataArray;
    }

    public function index() {

        // $this -> me -> pageTitle = 'Applicable Circulars';
        // $this -> me -> pageHeading = 'Applicable Circulars';
        
        $whereArray = $this -> indexWhereGenerate();

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> circularTaskSetModel, 
            $this -> circularTaskSetModel -> getTableName(), $whereArray);

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [ 'request' => $this -> request ]);
    }

     private function indexWhereGenerate() {

        $whereArray = [
            'where' => 'deleted_at IS NULL',
            'params' => [],
        ];

        if(!empty($this -> request -> input('circular_id'))) {
            $whereArray['where'] .= ' AND circular_id = :circular_id';
            $whereArray['params']['circular_id'] = $this -> request -> input('circular_id');
        }

        if(!empty($this -> request -> input('frequency'))) {
            $whereArray['where'] .= ' AND frequency = :frequency';
            $whereArray['params']['frequency'] = $this -> request -> input('frequency');
        }

        return $whereArray;
    }


    public function dataTableAjax()
    {   

        $whereArray = $this -> indexWhereGenerate();

        //print_r($whereArray);
       

        $funcData = generate_datatable_data($this, $this -> circularTaskSetModel, ["circular_id", "name"], $whereArray);


        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

            $srNo = 1;

            foreach($funcData['dbData'] as $cTaskSetId => $cTaskSetDetails)
            {
                $idEncrypt = encrypt_ex_data($cTaskSetDetails -> id);

                $circularName = (is_array($this -> data['circularData']) && isset($this -> data['circularData'][ $cTaskSetDetails -> circular_id ])) ? $this -> data['circularData'][ $cTaskSetDetails -> circular_id ] -> name : ERROR_VARS['notFound'];

                $cDataArray = [
                    "sr_no" => $srNo,
                    "circular_id" => $circularName,
                    "name"  => $cTaskSetDetails -> name,
                    "frequency"  => isset($this -> data['init_frequency'][ $cTaskSetDetails -> frequency ]) ? $this -> data['init_frequency'][ $cTaskSetDetails -> frequency ]['title'] : ERROR_VARS['notFound'],
                    "status" => check_active_status($cTaskSetDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                if($cTaskSetDetails -> is_active == 1) 
                {                        
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . $idEncrypt, 'extra' => view_tooltip('Update') ]);

                    $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . $idEncrypt, 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                }
                else 
                {
                    $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . $idEncrypt, 'extra' => view_tooltip('Activate') ]);
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
        $this -> me -> pageTitle = 'Add Task Set';
        $this -> me -> pageHeading = 'Add Task Set';
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add');

        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('complianceCircularTaskSet') ],
        ];

        $todayDate = date($GLOBALS['dateSupportArray'][1]);

        $this -> data['need_calender'] = true;
        $this -> data['need_select'] = true;
        $this -> data['data_container'] = true;

        $this -> data['db_data'] = $this -> circularTaskSetModel -> emptyInstance();

        $this -> data['db_data'] -> schedule_start_date = date('Y-m-01');
        $this -> data['db_data'] -> schedule_end_date = date('Y-m-t');

        $this -> data['db_data'] -> reporting_date_1 = date('Y-m-10');
        $this -> data['db_data'] -> due_date_1 = date('Y-m-05');

        $this -> data['db_data'] -> reporting_date_2 = date('Y-m-t');
        $this -> data['db_data'] -> due_date_2 = date('Y-m-25');

        // post method after form submit
        $this -> request::method("POST", function() {

            // validation check
            if($this -> validateData())
            {
                // insert in database
                $result = $this -> circularTaskSetModel::insert(
                    $this -> circularTaskSetModel -> getTableName(), 
                    $this -> postArray()
                );

                // method call
                $this -> somethingWrong($result);

                $lastInsertId = $this -> circularTaskSetModel::lastInsertId();

                // after insert data redirect to set dashboard
                Validation::flashErrorMsg('circularTaskSetAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl( $this -> me -> id ) . '/update/' . encrypt_ex_data($lastInsertId) );
            }
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    public function update($getRequest) {

        $this -> taskSetId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');
        $this -> data['card_task'] = $this -> request -> has('task') && in_array($this -> request -> input('task'), ['au', 'multi_task']) ? $this -> request -> input('task') : 'cts';

        // set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> taskSetId));
        $this -> me -> pageHeading = 'Update Task Set';

        // get data // method call
        $this -> getDataOr404();

        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('complianceCircularTaskSet') ],
        ];

        $this -> data['need_calender'] = true;
        $this -> data['need_select'] = true;
        $this -> data['data_container'] = true;
        $this -> data['btn_type'] = 'update';
        
        // find all audit units
        $model = $this -> model('AuditUnitModel');

        $this -> data['db_audit_unit_data'] = DBCommonFunc::getAllAuditUnitData($model, [
            'where' => 'is_active = 1 AND deleted_at IS NULL ORDER BY section_type_id+0, audit_unit_code+0', 'params' => []
        ]);

        // FIND AUDIT UNIT DATA
        if(is_array($this -> data['db_audit_unit_data']) && sizeof($this -> data['db_audit_unit_data']) > 0)
        {
            // sort array
            $temp = $this -> data['db_audit_unit_data'];
            $this -> data['db_audit_unit_data'] = [ 'branch' => [], 'ho' => [] ];

            foreach($temp as $cAuditUnitData)
            {
                $dept = ($cAuditUnitData -> section_type_id == 1) ? 'branch' : 'ho';
                $this -> data['db_audit_unit_data'][ $dept ][ $cAuditUnitData -> id ] = $cAuditUnitData;
            }

            asort($this -> data['db_audit_unit_data']['branch']);
            asort($this -> data['db_audit_unit_data']['ho']);
        }

        // FIND CIRCULAR AND CIRCULAR TASKS
        $model = $this -> model('ComplianceCircularSetModel');
        $this -> data['db_tasks_data'] = [];

        $findCircularSet = $model -> getSingleCircularSet([
            'where' => 'id = :id AND is_active = 1 AND is_applicable = 1 AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> data['db_data'] -> circular_id ]
        ]);

        if(is_object($findCircularSet))
        {
            // find header and tasks
            $model = $this -> model('ComplianceCircularTaskModel');
            $table = $model -> getTableName();

            $select = "SELECT cctm.id, cctm.header_id, cctm.task, COALESCE(cchm.name, '". ERROR_VARS['notFound'] ."') AS header_name FROM com_circular_task_master cctm JOIN com_circular_header_master cchm ON cctm.header_id = cchm.id";

            $this -> data['db_tasks_data'] = get_all_data_query_builder(2, $model, $table, [ 
                'where' => 'cctm.set_id = :set_id AND cctm.is_active = 1 AND cctm.deleted_at IS NULL GROUP BY cctm.header_id, cctm.id', 
                'params' => [ 'set_id' => $this -> data['db_data'] -> circular_id ] 
            ], 'sql', $select);
        }

        // post method after form submit
        $this -> request::method("POST", function() {
            
            if($this -> request -> has('submitTaskSet'))
            {
                if($this -> validateData())
                {
                    // insert in database
                    $result = $this -> circularTaskSetModel::update(
                        $this -> circularTaskSetModel -> getTableName(), 
                        $this -> postArray(), 
                        [
                            'where' => 'id = :id',
                            'params' => [ 'id' => $this -> taskSetId ]
                        ]
                    );

                    // method call
                    $this -> somethingWrong($result);

                    // after insert data redirect to set dashboard
                    Validation::flashErrorMsg('circularTaskSetUpdatedSuccess', 'success');
                    Redirect::to( SiteUrls::getUrl( $this -> me -> id ) . '/update/' . encrypt_ex_data($this -> taskSetId) . '?task=au' );
                }
            }
            else if($this -> request -> has('submitAssign'))
            {
                // method call
                $this -> updateAssign();
            }
            else if($this -> request -> has('submitTasks'))
            {
                // method call
                $this -> updateTaskAssign();
            }
            else
            {
                // method call
                $this -> somethingWrong();
            }            

        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    private function validateAssignData()
    {
        $validationArray['multi_type_check'] = 'required';

        Validation::validateData($this -> request, $validationArray);

        // validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    private function updateAssign() {

        if($this -> validateAssignData())
        {
            $result = $this -> circularTaskSetModel::update(

                $this -> circularTaskSetModel -> getTableName(), [
                    'audit_unit_ids' => $this -> request -> input('multi_type_check'),
                    'admin_id' => $this -> empId ], 
                [
                    'where' => 'id = :id',
                    'params' => [ 'id' => $this -> taskSetId ]
                ]
            );

            // method call
            $this -> somethingWrong($result);

            // after insert data redirect to set dashboard
            Validation::flashErrorMsg('circularTaskSetAssignSuccess', 'success');
            Redirect::to( SiteUrls::getUrl( $this -> me -> id ) . '/update/' . encrypt_ex_data($this -> taskSetId) . '?task=multi_task' );
        }
    }

    private function updateTaskAssign() {

        if($this -> validateAssignData())
        {
            $header_ids = [];
            $task_ids = [];
            $multiTypeTasks = $this -> request -> input('multi_type_check');
            $multiTypeTasks = !empty($multiTypeTasks) ? explode(',', $multiTypeTasks) : [];

            if(sizeof($multiTypeTasks) > 0)
            {
                foreach($this -> data['db_tasks_data'] as $cTaskData) {

                    if(in_array($cTaskData -> id, $multiTypeTasks))
                    {
                        // push data
                        $task_ids[] = $cTaskData -> id;
                        $header_ids[] = $cTaskData -> header_id;
                    }

                }
            }

            if(empty($task_ids) || empty($header_ids))
            {
                // method call
                $this -> somethingWrong();
            }

            $task_ids = array_unique($task_ids);
            $header_ids = array_unique($header_ids);

            $result = $this -> circularTaskSetModel::update(

                $this -> circularTaskSetModel -> getTableName(), [
                    'header_ids' => implode(',', $header_ids),
                    'task_ids' => implode(',', $task_ids),
                    'admin_id' => $this -> empId ], 
                [
                    'where' => 'id = :id',
                    'params' => [ 'id' => $this -> taskSetId ]
                ]
            );

            // method call
            $this -> somethingWrong($result);

            // after insert data redirect to set dashboard
            Validation::flashErrorMsg('circularTaskSetUpdatedSuccess', 'success');
            Redirect::to( SiteUrls::getUrl( $this -> me -> id ) . '/update/' . encrypt_ex_data($this -> taskSetId) . '?task=multi_task' );
        }
    }

    public function status($getRequest) {

        $this -> taskSetId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data // method call
        $this -> getDataOr404();
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> circularTaskSetModel::update(
            $this -> circularTaskSetModel -> getTableName(),
            [ 'is_active' => $updateStatus ], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> taskSetId ]
            ]
        );

        // method call
        $this -> somethingWrong($result);

        // after insert data redirect to set dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl($this -> me -> id) );
    }

    private function somethingWrong($result) {

        if(empty($result) || !$result)
        {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        } 
    }

    private function getDataOr404($optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> taskSetId ]
        ];

        // get data
        if(!empty($this -> taskSetId))
            $this -> data['db_data'] = $this -> circularTaskSetModel -> getSingleCircularTaskSetData($filter);

        if(!isset($this -> data['db_data']) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }
    }
}

?>