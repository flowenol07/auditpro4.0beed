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

class ManageAssesmentController extends Controller  {

    public $me = null, $data, $request, $assesId;
    public $assesmentModel;

    public function __construct($me) {

        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('manageAssesment') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current assesment model
        $this -> assesmentModel = $this -> model('AuditAssesmentModel'); 

        $model = $this -> model('AuditUnitModel');

        $this -> data['audit_unit_data'] = DBCommonFunc::getAllAuditUnitData($model, [
            'where' => 'is_active = 1 AND deleted_at IS NULL'
        ]);

        $this -> data['audit_unit_data'] = generate_data_assoc_array($this -> data['audit_unit_data'], 'id');
    }

    private function validateData($methodType = 'add', $menuId = '')
    {
        $validationArray = [
            'auditUnit' => 'required|array_key[audit_unit_array, auditUnitSelect]',
        ];

        $notiObj = new Notifications;
        $validationArray = array_merge($validationArray, date_validation_helper($this -> request, $validationArray, $notiObj)['validation']);

        if(!$this -> request -> input( 'error' ) > 0)
        {
            Validation::validateData($this -> request, $validationArray,
            [ 'audit_unit_array' => $this -> data['audit_unit_data'] ]);
        }

        //validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    /*private function postArray($methodType = 'add')
    {
        $dataArray = array(
            'section_type_id' => $this -> request -> input('section_type_id'),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }*/

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ]
        ];

        //Search in Select 
        $this -> data['need_select'] = true;

        $this -> data['need_calender'] = true;

        // post method after form submit
        $this -> request::method("POST", function() {

            // validation check
            if(!$this -> validateData())
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'index', [ 
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);
            } 
            else
            {  
                // search data
                $this -> data['db_data'] = $this -> assesmentModel -> getAllAuditAssesment([
                    'where' => 'audit_unit_id = :audit_unit_id AND assesment_period_from >= :assesment_period_from AND assesment_period_to <= :assesment_period_to AND deleted_at IS NULL ORDER BY audit_unit_id ASC',
                    'params' => [
                        'audit_unit_id' => $this -> request -> input('auditUnit'),
                        'assesment_period_from' => $this -> request -> input('startDate'),
                        'assesment_period_to' => $this -> request -> input('endDate'),
                    ]
                ]);            

            }

        });        

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [ 'data' => $this -> data, 'request' => $this -> request ]);
    }

    public function assesmentView($getRequest) {
        
        $this -> assesId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> pageHeading = 'View Assesment Details';
        $this -> data['current_url'] = (URL . explode('url=', $_SERVER['QUERY_STRING'])[1]);

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> assesId]);

        // find employees
        $findEmployeesArray = [];

        // auditor
        if(!empty( $this -> data['db_data'] -> audit_head_id ))
            $findEmployeesArray[] = $this -> data['db_data'] -> audit_head_id;

        // head
        if(!empty( $this -> data['db_data'] -> branch_head_id ))
            $findEmployeesArray[] = $this -> data['db_data'] -> branch_head_id;

        // sub head
        if(!empty( $this -> data['db_data'] -> branch_subhead_id ))
            $findEmployeesArray[] = $this -> data['db_data'] -> branch_subhead_id;

        // audit other compliance ids
        if(!empty( $this -> data['db_data'] -> multi_compliance_ids ))
        {

            $tempFindEmployeesArray = explode(',', $this -> data['db_data'] -> multi_compliance_ids);
            $findEmployeesArray =  array_merge($findEmployeesArray, $tempFindEmployeesArray);

            $this -> data['db_data'] -> multi_compliance_array = [];
        }

        if(sizeof($findEmployeesArray) > 0)
        {
            $model = $this -> model('EmployeeModel');
            
            $findEmployeesArray = $model -> getAllEmployees([
                'where' => 'id IN ('. implode(',', $findEmployeesArray) .') AND id != 1'
            ]);

            if(is_array($findEmployeesArray) && sizeof($findEmployeesArray) > 0)
            {
                foreach($findEmployeesArray as $cEmpDetails)
                {
                    // auditor
                    if($this -> data['db_data'] -> audit_head_id == $cEmpDetails -> id)
                        $this -> data['db_data'] -> audit_head_details = $cEmpDetails;

                    // head
                    if($this -> data['db_data'] -> branch_head_id == $cEmpDetails -> id)
                        $this -> data['db_data'] -> branch_head_details = $cEmpDetails;

                    // sub head
                    if($this -> data['db_data'] -> branch_subhead_id == $cEmpDetails -> id)
                        $this -> data['db_data'] -> branch_subhead_details = $cEmpDetails;

                    // multi compliane id
                    if(!empty( $this -> data['db_data'] -> multi_compliance_ids ))
                    {
                        $tempMultiCompliance = explode(',', $this -> data['db_data'] -> multi_compliance_ids);

                        if(in_array($cEmpDetails -> id, $tempMultiCompliance))
                            $this -> data['db_data'] -> multi_compliance_array[] = $cEmpDetails;
                    }
                }
            }
        }

        $this -> data['increase_blocked_array'] = [
            '5' => 'Increase Limit By 5',
            '10' => 'Increase Limit By 10'
        ];

        // $this -> data['increase_due_date_array'] = [
        //     '15' => 'Increase Due Date By 15 Days',
        //     '30' => 'Increase Due Date By 30 Days'
        // ];

        $this -> data['need_calender'] = true;

        if(!($this -> data['db_data'] -> audit_status_id > 6))
        {
            // post method after form submit
            $this -> request::method("POST", function() {
                
                $validationArray = [ 'validation' => [], 'data_array' => [] ];

                if($this -> request -> has('increase_limit'))
                {
                    $validationArray = [
                        'validation' => [ 'increase_limit_id' => 'required|array_key[increase_blocked_array, increaseLimitSelectError]' ],
                        'data_array' => [ 'increase_blocked_array' => $this -> data['increase_blocked_array'] ]
                    ];
                }
                else if($this -> request -> has('increase_due_date'))
                {
                    $validationArray = [
                        'validation' => [ 'increase_due_date_days' => 'required|regex[dateRegex, dateError]' ],
                        'data_array' => [  ]
                    ];
                }

                if(sizeof($validationArray['validation']) > 0)
                {
                    Validation::validateData(
                        $this -> request, 
                        $validationArray['validation'],
                        $validationArray['data_array']
                    );

                    if( $this -> request -> has('increase_due_date') && 
                        !($this -> request -> input( 'error' ) > 0) )
                    {
                        // check date must grater than todays date
                        if(!($this -> request -> input('increase_due_date_days') > date('Y-m-d')))
                        {
                            $this -> request -> setInputCustom( 'increase_due_date_days_err', Notifications::getNoti('dateGratorTodayError'));
                            $this -> request -> setInputCustom( 'error', 1);
                        }
                    }
            
                    //validation check
                    if($this -> request -> input( 'error' ) > 0)
                        Validation::flashErrorMsg();
                    else
                    {
                        // update
                        $updateArray = [];

                        // insert timeline status
                        $insertTimeLineArray = array(
                            'id' => $this -> data['db_data'] -> id,
                            'type' => 3,
                            'status' => null,
                            'rejected_cnt' => 0,
                            'emp_id' => Session::get('emp_id'),
                            'batch_key' => $this -> data['db_data'] -> batch_key,
                        );

                        // increase reject limit
                        if($this -> request -> has('increase_limit'))
                        {
                            $cLimitKey = 'compliance_review_reject_limit';
                            $insertTimeLineArray['status'] = ASSESMENT_TIMELINE_ARRAY[11]['status_id'];

                            if( in_array($this -> data['db_data'] -> audit_status_id, [ 
                                ASSESMENT_TIMELINE_ARRAY[2]['status_id'], 
                                ASSESMENT_TIMELINE_ARRAY[3]['status_id'] ]))
                            {
                                $cLimitKey = 'audit_review_reject_limit';
                                $insertTimeLineArray['status'] = ASSESMENT_TIMELINE_ARRAY[10]['status_id'];
                            }

                            $updateArray = [
                                $cLimitKey => $this -> data['db_data'] -> $cLimitKey + $this -> request -> input('increase_limit_id'),
                                'is_limit_blocked' => 0
                            ];

                            $notiKey = 'increaseLimitSuccess';
                        }
                        else
                        {
                            // increase due date
                            
                            $cLimitKey = 'compliance_due_date';
                            $insertTimeLineArray['status'] = ASSESMENT_TIMELINE_ARRAY[13]['status_id'];

                            if( in_array($this -> data['db_data'] -> audit_status_id, [ 
                                ASSESMENT_TIMELINE_ARRAY[1]['status_id'], 
                                ASSESMENT_TIMELINE_ARRAY[2]['status_id'], 
                                ASSESMENT_TIMELINE_ARRAY[3]['status_id'] ]))
                            {
                                $cLimitKey = 'audit_due_date';
                                $insertTimeLineArray['status'] = ASSESMENT_TIMELINE_ARRAY[12]['status_id'];
                            }

                            $updateArray = [
                                $cLimitKey => date($GLOBALS['dateSupportArray'][1], strtotime( $this -> request -> input('increase_due_date_days') ))
                            ];

                            $notiKey = 'increaseDueDateSuccess';
                        }

                        // print_r($updateArray);

                        if(!audit_assesment_timeline_insert($this, $insertTimeLineArray))
                        {
                            Except::exc_404( Notifications::getNoti('errorSaving') );
                            exit;
                        }

                        $result = $this -> assesmentModel::update(
                            $this -> assesmentModel -> getTableName(),
                            $updateArray, 
                            [
                                'where' => 'id = :id',
                                'params' => [ 'id' => $this -> assesId ]
                            ]
                        );
                
                        if(!$result)
                            return Except::exc_404( Notifications::getNoti('errorSaving') );
                
                        // after update data redirect to menu dashboard
                        Validation::flashErrorMsg($notiKey, 'success');
                        Redirect::to( $this -> data['current_url'] );
                    }
                }

            });

        }

        return return2View($this, $this -> me -> viewDir . 'view', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);

    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND audit_status_id < 7',
            'params' => [ 'id' => $this -> assesId ]
        ];

        // get data
        $this -> data['db_data'] = $this -> assesmentModel -> getSingleAuditAssesment($filter);

        if(empty($this -> assesId) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        return $this -> data['db_data'];
    }
}

?>