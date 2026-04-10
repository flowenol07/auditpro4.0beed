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

class ComplianceCircularSetMaster extends Controller  {

    public $me = null, $data, $request, $setId;
    public $circularSetModel, $circularAuthorityData;

    public function __construct($me) {

        $this -> me = $me;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('complianceCircularSetMaster') ],
        ];
        
        // request object created
        $this -> request = new Request();

        // find current question set model
        $this -> circularSetModel = $this -> model('ComplianceCircularSetModel');
        
        $model = $this -> model('ComplianceCircularAuthorityModel');
        $this -> data['circularAuthority'] = $model -> getAllCircularAuthority(['where' => 'is_active = 1 AND deleted_at IS NULL']);
        $this -> data['circularAuthority'] = generate_data_assoc_array($this -> data['circularAuthority'], 'id');

        $this -> data['init_frequency'] = COMPLIANCE_PRO_ARRAY['compliance_frequency'];
    }

    private function validateData($methodType = 'add', $setId = '')
    {
        $validationArray = [
            'authority_id' => 'required|array_key[authority_array, authorityType]',
            'set_type_id' => 'required|array_key[set_type_array, circularTypeError]',
            'ref_no' => 'regex[alphaNumericSymbolsRegex, refNoError]',
            'name' => 'required',
            'circular_date' => 'required|regex[dateRegex, dateError]',
            'priority_id' => 'array_key[priority_array, priorityIdError]'
        ];

        // if checked
        if($this -> request -> has('is_penalty_applicable'))
            $validationArray['penalty_amt'] = 'required|regex[floatNumberRegex, amount]';

        Validation::validateData($this -> request, $validationArray, [
            'set_type_array' => COMPLIANCE_PRO_ARRAY['compliance_categories'],
            'priority_array' => COMPLIANCE_PRO_ARRAY['compliance_priority'],
            'authority_array' => $this -> data['circularAuthority'],
            'init_frequency_array' => $this -> data['init_frequency']
        ]);

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
            'set_type_id' => $this -> request -> input('set_type_id'),
            'authority_id' => $this -> request -> input('authority_id'),
            'ref_no' => $this -> request -> input('ref_no'),
            'name' => $this -> request -> input('name'),
            'description' => trim_str($this -> request -> input('description')),
            'from_portal' => trim_str($this -> request -> input('from_portal')),
            'circular_date' => $this -> request -> input('circular_date'),
            'is_penalty_applicable' => 0,
            'penalty_amt' => get_decimal(0, 2),
            'penalty_description' => '',            
            'priority_id' => '1',            
            'is_compliance' => /*!empty($this -> request -> input('is_compliance')) ? $this -> request -> input('is_compliance') : 0*/ 1, // default compliance
            'admin_id' => Session::get('emp_id'),
        );

        // is penalty 
        if($this -> request -> has('is_penalty_applicable'))
        {
            $dataArray['is_penalty_applicable'] = 1;
            $dataArray['penalty_amt'] = get_decimal($this -> request -> input('penalty_amt'), 2);
            $dataArray['penalty_description'] = trim_str($this -> request -> input('penalty_description'));
        }

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('complianceCircularSetMaster') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> circularSetModel, 
            $this -> circularSetModel -> getTableName(), [
                'where' => 'deleted_at IS NULL']);

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // need audit assesment js
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance-pro-applicable.min.js';

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
         $whereArray = [
            'where' => 'deleted_at IS NULL',
            'params' => [ ]
        ];
        
        $funcData = generate_datatable_data($this, $this -> circularSetModel, ["name", "authority_id"], $whereArray);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

            $srNo = 1;

            foreach($funcData['dbData'] as $cQuestionSetId => $cQuestionSetDetails)
            {
                $name = '<p class="text-primary mb-0">' . $cQuestionSetDetails -> name . '</p>';
                $idEncrypt = encrypt_ex_data($cQuestionSetDetails -> id);

                // add circular type
                $name .= '<p class="font-sm mb-0"><span class="font-medium">Circular Type: </span>'. (isset(COMPLIANCE_PRO_ARRAY['compliance_categories'][ $cQuestionSetDetails -> set_type_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_categories'][ $cQuestionSetDetails -> set_type_id ] : ERROR_VARS['notFound']) .'</p>';

                $isApplicable = $cQuestionSetDetails -> is_applicable == 1;

                $name .= '<div class="form-check form-switch mt-2 circular-applicable-switch-class">
                    <input class="form-check-input circular-applicable-checkbox" type="checkbox" id="applicable_'. $srNo .'"'. ( $isApplicable ? ' checked' : '' ) .' data-circularid="'. $idEncrypt .'" data-url="'. SiteUrls::setUrl( $this -> me -> url ) . '/applicable-status' .'">
                    <label class="form-check-label'. ( $isApplicable ? ' text-success' : ' text-danger') .'" for="applicable_'. $srNo .'">'. ( !$isApplicable ? 'Not ' : '' ) .'Applicable</label>
                    <div class="circular-applicable-switch-status"></div>
                </div>';

                // authority
                $authority = ERROR_VARS['notFound'];

                if( is_array($this -> data['circularAuthority']) && 
                    array_key_exists($cQuestionSetDetails -> authority_id, $this -> data['circularAuthority']) )
                    $authority = $this -> data['circularAuthority'][ $cQuestionSetDetails -> authority_id ] -> name;

                $cDataArray = [
                    "sr_no" => $srNo,
                    "authority_id" => $authority,
                    "name"  => $name,
                    "circular_date"  => $cQuestionSetDetails -> circular_date,
                    "status" => check_active_status($cQuestionSetDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {                      
                    if($cQuestionSetDetails -> is_active == 1) 
                    {                        
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . $idEncrypt, 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . $idEncrypt, 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . $idEncrypt, 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/view-circular/' . $idEncrypt, 'extra' => view_tooltip('View')]);
                    }
                    else 
                    {
                        $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . $idEncrypt, 'extra' => view_tooltip('Activate') ]);
                    }
                }                
                else
                    $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::getUrl('questionHeaderMaster') . '?set=' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Add / Update Header')]);

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
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add');
        $this -> me -> breadcrumb[] = $this -> me -> id;
        $this -> me -> pageHeading = 'Add Circular';
        $todayDate = date($GLOBALS['dateSupportArray'][1]);

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> circularSetModel -> emptyInstance();
        $this -> data['need_calender'] = true;
        $this -> data['btn_type'] = 'add';
        $this -> data['db_data'] -> circular_date = date($GLOBALS['dateSupportArray'][1]);

        // default set
        if(!$this -> request -> has('set_type_id'))
            $this -> request -> setInputCustom('set_type_id', 6);

        if(!$this -> request -> has('penalty_amt'))
            $this -> request -> setInputCustom('penalty_amt', 0);

        // post method after form submit
        $this -> request::method("POST", function() {

            // validation check
            if(!$this -> validateData())
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);
            } 
            else
            {  
                // insert in database
                $result = $this -> circularSetModel::insert(
                    $this -> circularSetModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                // after insert data redirect to set dashboard
                Validation::flashErrorMsg('circularSetAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl( $this -> me -> id ) );
            }

        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);

    }

    public function update($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> setId));
        $this -> me -> pageHeading = 'Update Circular';

        // get data // method call
        $this -> getDataOr404(null);

        $this -> data['btn_type'] = 'update';
        $this -> data['need_calender'] = true;

        // post method after form submit
        $this -> request::method("POST", function() {

            // validation check
            if(!$this -> validateData('update', $this -> setId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);
            } 
            else
            {
                $result = $this -> circularSetModel::update(
                    $this -> circularSetModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> setId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                // after insert data redirect to set dashboard
                Validation::flashErrorMsg('circularSetUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl($this -> me -> id) );
            }
        });

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    public function delete($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> getDataOr404(null);

        $result = $this -> circularSetModel::delete($this -> circularSetModel -> getTableName(), [
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> setId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to set dashboard
        Validation::flashErrorMsg('circularSetDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl($this -> me -> id) );
    }

    public function status($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data // method call
        $this -> getDataOr404(null, 0);
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> circularSetModel::update(
            $this -> circularSetModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> setId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        // after insert data redirect to set dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl($this -> me -> id) );
    }

    public function applicableStatus() {

        $res = [ 'err' => true, 'msg' => Notifications::getNoti('somethingWrong') ];
        $circularSetId = isset($_POST['id']) && !empty($_POST['id']) ? decrypt_ex_data($_POST['id']) : '';
        $circularSetDetails = null;

        if(!empty($circularSetId))
            $circularSetDetails = $this -> circularSetModel -> getSingleCircularSet([
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $circularSetId ]
            ]);

        if(!is_object($circularSetDetails))
            $res['msg'] = Notifications::getNoti('circularNotFound');
        else
        {
            // set found // check status
            $status = $circularSetDetails -> is_applicable == 1 ? 0 : 1;
            
            // update data
            $result = $this -> circularSetModel::update(
                $this -> circularSetModel -> getTableName(),
                [ 'is_applicable' => $status], 
                [
                    'where' => 'id = :id',
                    'params' => [ 'id' => $circularSetId ]
                ]
            );
    
            if(!$result)
                $res['msg'] = Notifications::getNoti('errorSaving');
            else
            {
                $res['msg'] = Notifications::getNoti('statusChange');
                $res['status'] = $status;
            }
        }

        echo json_encode($res);
        exit;
    }

    public function viewCircular($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data // method call
        $this -> getDataOr404(null);

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl( $this -> me -> id ) ],
            'add' => [ 'href' => SiteUrls::getUrl('complianceCircularTaskMaster') . '/add?circular=' . encrypt_ex_data($this -> setId) ],
        ];

        $this -> data['data_container'] = true;
        $this -> data['show_data'] = true;
        $this -> me -> breadcrumb[] = $this -> me -> id;
        $this -> me -> pageHeading = 'View Circular';

        // get circular docs
        $multiDocsData = get_multi_docs_data($this, 1, [
            'circulr_id' => $this -> data['db_data'] -> id,
            'type' => 1
        ]);

        if( is_array($multiDocsData) && sizeof($multiDocsData) > 0 )
            $this -> data['db_data'] -> multi_docs = $multiDocsData;

        // $this -> data['tasks_data']
        $query = "SELECT t.*, 
                    COALESCE(h.name, 'na') AS header_name,
                    COALESCE(rcm.risk_category, 'na') AS risk_category,
                    COALESCE(aam.name, 'na') AS audit_area_name
                FROM com_circular_task_master t 
                LEFT JOIN com_circular_header_master h 
                ON t.header_id = h.id 
                LEFT JOIN risk_category_master rcm
                ON t.risk_category_id = rcm.id
                LEFT JOIN audit_area_master aam
                ON t.area_of_audit_id = aam.id 
                WHERE t.set_id = '". $this -> setId ."'
                AND t.is_active = 1 
                AND t.deleted_at IS NULL
                AND h.deleted_at IS NULL
                AND rcm.deleted_at IS NULL
                AND aam.deleted_at IS NULL";

        // get all tasks
        $model = $this -> model('ComplianceCircularTaskModel');
        $this -> data['disable_action'] = check_compliance_data_strict($this, 1);

        $taskData = get_all_data_query_builder(2, $model, $model -> getTableName(), [ ], 'sql', $query);
        $this -> data['tasks_data'] = [];
        $taskIds = [];

        if(is_array($taskData) && sizeof($taskData) > 0)
        {
            $this -> data['remove_container'] = true;            

            foreach($taskData as $cTask)
            {
                // check header id // push header details
                if(!array_key_exists($cTask -> header_id, $this -> data['tasks_data']))
                    $this -> data['tasks_data'][ $cTask -> header_id ] = [
                        'id' => $cTask -> header_id,
                        'name' => string_operations(($cTask -> header_name != 'na' ? $cTask -> header_name : ERROR_VARS['notFound']), 'upper'),
                        'tasks' => []
                    ];

                // push tasks
                $this -> data['tasks_data'][ $cTask -> header_id ]['tasks'][ $cTask -> id ] = $cTask;

                if(!in_array($cTask -> id, $taskIds))
                    $taskIds[] = $cTask -> id;
            }
        }

        //define doc model 
        $this -> data['cco_docs_true'] = true;
        $this -> data['set_cco_docs_true'] = true;        

        if(sizeof($taskIds) > 0)
        {
            // find docs
            $multiDocsData = get_multi_docs_data($this, 2, [
                'circulr_id' => $this -> data['db_data'] -> id, 
                'task_ids' => $taskIds,
                'type' => 1
            ]);

            if(is_array($multiDocsData) && sizeof($multiDocsData) > 0)
            {
            
             
                foreach($multiDocsData as $cDocId => $cDocData)
                {
                    foreach($this -> data['tasks_data'] as $cHeaderId => $cHeaderDetails)
                    {
                      
              
                        if( isset($cHeaderDetails['tasks']) && 
                            is_array($cHeaderDetails['tasks']) &&
                            array_key_exists($cDocData -> task_id, $cHeaderDetails['tasks']))
                        {
                            // task found
                            if(!isset($this -> data['tasks_data'][ $cHeaderId ]['tasks'][ $cDocData -> task_id ] -> multi_docs))
                                $this -> data['tasks_data'][ $cHeaderId ]['tasks'][ $cDocData -> task_id ] -> multi_docs = [];

                            $this -> data['tasks_data'][ $cHeaderId ]['tasks'][ $cDocData -> task_id ] -> multi_docs[ $cDocData -> id ] = $cDocData;
                        }
                    }
                }
            }

            
        }

        //need audit assesment js
        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance-pro-docs-upload.min.js';

        return return2View($this, $this -> me -> viewDir . 'view', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    private function getDataOr404($filter, $optional = 1) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> setId ]
        ];

        if( $optional == 1 )
            $filter['where'] .= ' AND is_active = 1';

        // get data
        if(!empty($this -> setId))            
            $this -> data['db_data'] = $this -> circularSetModel -> getSingleCircularSet($filter);

        if(!isset($this -> data['db_data']) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }
    }
}

?>