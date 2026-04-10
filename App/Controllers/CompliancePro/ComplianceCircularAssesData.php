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

class ComplianceCircularAssesData extends Controller  {

    public $me = null, $data, $request, $circularId, $empId;
    public $model, $circularSetModel, $cccmModel, $csaModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> circularSetModel = $this -> model('ComplianceCircularSetModel');
        $this -> cccmModel = $this -> model('ComplianceCircularAssesMasterModel');

        $model = $this -> model('ComplianceCircularAuthorityModel');
        $this -> data['circularAuthority'] = $model -> getAllCircularAuthority(['where' => 'is_active = 1 AND deleted_at IS NULL']);
        $this -> data['circularAuthority'] = generate_data_assoc_array($this -> data['circularAuthority'], 'id');
        
        $this -> empId = Session::get('emp_id');
    }

    public function index() {

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> circularSetModel, 
            $this -> circularSetModel -> getTableName(), [
                'where' => 'is_applicable = 1 AND is_active = 1 AND deleted_at IS NULL'
            ]);

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [ 'request' => $this -> request ]);
    }

    public function dataTableAjax()
    {
        $whereArray = [
            'where' => 'is_applicable = 1 AND is_active = 1 AND deleted_at IS NULL',
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
                    "action" => ""
                ];
            
                $srNo++;

                // View circular
                $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/view-circular/' . $idEncrypt, 'extra' => view_tooltip('View')]);

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

    private function viewValidateData()
    {
        $validationArray = [
            'tsid' => 'required|array_key[circular_set_array, circularTaskSetSelectError]',
        ];

        Validation::validateData($this -> request, $validationArray, [
            'circular_set_array' => $this -> data['taskSetData']
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

    private function submitReportValidateData()
    {
        Validation::validateData($this -> request, [
            'multi_type_check' => 'required',
            'reporting_date' => 'required|regex[dateRegex, dateError]',
            'remark' => 'required',
        ]);

        if(!($this -> request -> input( 'error' ) > 0))
        {
            // check asses compliance completed or not
            $multiTypeCheckArr = explode(',', $this -> request -> input('multi_type_check'));

            if(is_array($multiTypeCheckArr) && sizeof($multiTypeCheckArr) > 0)
            {
                // check completed
                $table = $this -> cccmModel -> getTableName();

                $comAssesData = get_all_data_query_builder(2, $this -> cccmModel, $table, 
                [   'where' => 'deleted_at IS NULL AND id IN ('. implode(",", $multiTypeCheckArr) .') AND com_status_id = 4', 
                    'params' => [  ] 
                ], 'sql', "SELECT id, com_status_id FROM " . $table);

                // $comAssesData[5] = 'test';

                // check completed com asses count
                if(!is_array($comAssesData) || sizeof($multiTypeCheckArr) != sizeof($comAssesData))
                {
                    $this -> request -> setInputCustom('multi_type_check_err', 'comCompletedAssesError');
                    Validation::incrementError($this -> request);
                }
            }
            else
            {
                // error
                $this -> request -> setInputCustom('multi_type_check_err', 'checkboxCheckError');
                Validation::incrementError($this -> request);
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

    private function submitReportPostArray()
    {
        $dataArray = array(
            "reporting_date" => $this -> request -> input( 'reporting_date' ),
            "remark" => $this -> request -> input( 'remark' ),
            "com_asses_ids" => $this -> request -> input('multi_type_check'),
            "circular_id" => $this -> data['db_data'] -> id,
            "authority_id" => $this -> data['db_data'] -> authority_id,
            "emp_id" => $this -> empId,
            "bulk_batch_key" => $this -> data['bbk']
        );

        return $dataArray;
    }

    public function viewCircular($getRequest) {

        $this -> circularId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data // method call
        $this -> getCircularDataOr404();

        // top btn array
        $this -> data['topBtnArr'] = [ 'default' => [ 'href' => SiteUrls::getUrl( $this -> me -> id ) ] ];

        // 'add' => [ 'href' => SiteUrls::getUrl( $this -> me -> id ) . '/add?circular=' . encrypt_ex_data($this -> circularId) ],

        $this -> data['data_container'] = true;
        $this -> data['show_data'] = true;
        $this -> me -> breadcrumb[] = $this -> me -> id;
        $this -> me -> pageTitle = 'View Circular';
        $this -> me -> pageHeading = 'View Circular';
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/view-circular/' . encrypt_ex_data($this -> circularId));

        $this -> data['emp_data'] = get_authority_wise_audit_units($this, $this -> empId);

        $this -> data['need_select'] = true;
        $this -> data['taskSetData'] = null;
        $this -> data['assesData'] = null;
        $this -> data['complianceStatus'] = [
            'all' => ['status_id' => 'all', 'title' => 'All COMPLIANCE'],
        ];

        // status addition
        $this -> data['complianceStatus'] = $this -> data['complianceStatus'] + COMPLIANCE_PRO_ARRAY['timeline_compliance_status'];

        // submit compliance report
        $this -> csaModel = $this -> model('ComplianceSubmitAuthorityModel');

        // year model
        $yearModel = $this -> model('YearModel');
        $this -> data['year_data'] = $yearModel -> getSingleYear(['where' => 'deleted_at IS NULL ORDER BY year']);

        if( is_object($this -> data['emp_data']) && 
            isset($this -> data['emp_data'] -> audit_unit_data) &&
            sizeof($this -> data['emp_data'] -> audit_unit_data) > 0 &&
            is_object($this -> data['year_data']) )
        {
            // method call
            $this -> getCircularAssesData();
        }

        if($this -> request -> has('tsid')):

            // get method after form submit

            // validation check
            if($this -> viewValidateData())
            {   
                $reqArr = [
                    'year_data' => $this -> data['year_data'] -> id, 
                    'circular_id' => $this -> data['db_data'] -> id
                ];

                if($this -> request -> input('tsid') != 'all')
                {
                    $this -> data['selectedTaskSet'] = $this -> data['taskSetData'][ $this -> request -> input('tsid') ];
                    $reqArr['task_set_id'] = $this -> data['selectedTaskSet'] -> task_set_id;
                    $reqArr['period'] = [ 
                        'com_period_from' => $this -> data['selectedTaskSet'] -> com_period_from,
                        'com_period_to' => $this -> data['selectedTaskSet'] -> com_period_to 
                    ];
                }

                if( !in_array($this -> request -> input('csid'), ['', 'all']) )
                    $reqArr['com_status_id'] = $this -> request -> input('csid');

                // get assign data
                $assesData = get_compliance_asses_data_mix($this, $reqArr);
                
                $this -> data['assesData'] = $assesData['asses_data'];
                $this -> data['assesCompletedCount'] = $assesData['completed'];
                unset($assesData);

                $this -> data['postSubmitted'] = true;

                if(sizeof($this -> data['assesData']) > 0)
                {
                    // has data of asses
                    $this -> data['need_calender'] = true;
                    $this -> data['bbk'] = $this -> data['assesData'][ array_keys($this -> data['assesData'])[0] ] -> bulk_batch_key;

                    if(!$this -> request -> has('reporting_date') && !$this -> request -> has('comSubmitId'))
                        $this -> request -> setInputCustom('reporting_date', date($GLOBALS['dateSupportArray'][1]) );                    

                    // create submit url
                    $this -> data['submitFormUrl'] = $this -> me -> url . '?' . http_build_query([
                        'tsid' => $this -> request -> input('tsid'),
                        'csid' => $this -> request -> input('csid')
                    ]);

                    // find compliance submit data
                    $this -> data['db_submitted_list'] = $this -> csaModel -> getAllSubmittedReport([
                        'where' => 'deleted_at IS NULL AND bulk_batch_key = :bulk_batch_key ORDER BY reporting_date DESC',
                        'params' => [ 'bulk_batch_key' => $this -> data['bbk'] ]
                    ]);

                    if(is_array($this -> data['db_submitted_list']) && sizeof($this -> data['db_submitted_list']) > 0)
                    {
                        // need audit assesment js // enable docs upload
                        $this -> data['js'][] = COMPLIANCE_PRO_ARRAY['compliance_docs_array']['assets'] . 'compliance-pro-docs-upload.min.js';
                        $this -> data['cco_docs_true'] = true;

                        // sort data
                        $tempSubmittedList = $this -> data['db_submitted_list'];
                        $this -> data['db_submitted_list'] = [];

                        foreach($tempSubmittedList as $cSubmittedList)
                        {
                            $com_asses_ids = !empty($cSubmittedList) ? explode(',', $cSubmittedList -> com_asses_ids) : [];
                            $comAssesIdsArray = [];
                            
                            if(is_array($com_asses_ids) && sizeof($com_asses_ids) > 0)
                            {
                                foreach($com_asses_ids as $cAssesId)
                                {
                                    if(array_key_exists($cAssesId, $this -> data['assesData']))
                                    {
                                        $comAssesIdsArray[ $cAssesId ] = [
                                            'combined_name' => $this -> data['assesData'][ $cAssesId ] -> combined_name,
                                            'frequency_id' => $this -> data['assesData'][ $cAssesId ] -> frequency_id,
                                            'com_period_from' => $this -> data['assesData'][ $cAssesId ] -> com_period_from,
                                            'com_period_to' => $this -> data['assesData'][ $cAssesId ] -> com_period_to
                                        ];

                                        // report submit data
                                        if(!isset($this -> data['assesData'][ $cAssesId ] -> reporting_date))
                                            $this -> data['assesData'][ $cAssesId ] -> reporting_date = $cSubmittedList -> reporting_date;
                                    }
                                }
                            }

                            $cSubmittedList -> com_asses_ids_array = $comAssesIdsArray;

                            // get multi docs
                            $multiDocsData = get_multi_docs_data($this, 8, [
                                'circular_id' => $cSubmittedList -> circular_id,
                                'submit_auth_id' => $cSubmittedList -> id,
                                'type' => 8
                            ]);

                            if( is_array($multiDocsData) && sizeof($multiDocsData) > 0 )
                                $cSubmittedList -> multi_docs = $multiDocsData;

                            // push data
                            $this -> data['db_submitted_list'][ $cSubmittedList -> id ] = $cSubmittedList;
                        }
                    }
                }
            }

            // print_r($this -> data['db_submitted_list']);
            // exit;

            if($this -> request -> has('comSubmitId'))
            {
                $comSubmitId = decrypt_ex_data($this -> request -> input('comSubmitId'));

                if(isset($this -> data['db_submitted_list']) && array_key_exists($comSubmitId, $this -> data['db_submitted_list']))
                {
                    // data found
                    $this -> data['dbComSubmit'] = $this -> data['db_submitted_list'][ $comSubmitId ];

                    // update submit url
                    $this -> data['submitFormUrl'] .= '&comSubmitId=' . encrypt_ex_data($comSubmitId);
                }
                else
                {
                    // error data not found
                    Except::exc_404( Notifications::getNoti('errorFinding') );
                    exit;
                }                
            }
            else
                $this -> data['dbComSubmit'] = $this -> csaModel -> emptyInstance();

            // post method after form submit
            $this -> request::method("POST", function() {

                // validation check
                if($this -> request -> has('submitReport') && $this -> submitReportValidateData())
                {
                    $this -> data['insert_array'] = $this -> submitReportPostArray();
                    $noti = 'comSubmitReport';
                    
                    if(!empty($this -> data['dbComSubmit'] -> id))
                    {
                        // update
                        $result = $this -> csaModel::update(
                            $this -> csaModel -> getTableName(), 
                            $this -> data['insert_array'], [
                                'where' => 'id = :id',
                                'params' => [ 'id' => $this -> data['dbComSubmit'] -> id ]
                            ]
                        );

                        $noti = 'comSubmitUpdateReport';
                    }
                    else
                    {
                        // insert
                        $result = $this -> csaModel::insert(
                            $this -> csaModel -> getTableName(), 
                            $this -> data['insert_array']
                        );
                    }

                    if(!$result)
                    {
                        Except::exc_404( Notifications::getNoti('somethingWrong') );
                        exit;
                    }

                    $position = strpos($this -> data['submitFormUrl'], '&comSubmitId');

                    // remove &comSubmitId beacuase it will again update form
                    if ($position !== false)
                        $this -> data['submitFormUrl'] = substr($this -> data['submitFormUrl'], 0, $position);

                    // after above operation done redirect to set dashboard
                    Validation::flashErrorMsg($noti, 'success');
                    Redirect::to( $this -> data['submitFormUrl'] );
                }
            });

        endif;

        return return2View($this, $this -> me -> viewDir . 'view', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    private function getCircularAssesData() {

        // find distinct records
        $table = $this -> cccmModel -> getTableName();

        $select = "SELECT DISTINCT 
            cccm.com_period_from, 
            cccm.com_period_to, 
            cccm.frequency_id, 
            cccm.task_set_id,
            COALESCE(ccmcts.name, '". ERROR_VARS['notFound'] ."') AS task_set_name
        FROM " . $table . " cccm LEFT JOIN com_circular_multi_control_task_set ccmcts 
        ON cccm.task_set_id = ccmcts.id";

        $this -> data['taskSetData'] = get_all_data_query_builder(2, $this -> cccmModel, $table, 
        [   'where' => 'cccm.deleted_at IS NULL AND cccm.circular_id = :circular_id', 
            'params' => [ 'circular_id' => $this -> circularId ] 
        ], 'sql', $select);

        if( is_array($this -> data['taskSetData']) && 
            sizeof($this -> data['taskSetData']) > 0)
        {
            $tempData = $this -> data['taskSetData'];
            $this -> data['taskSetData'] = [ 'all' => (object) [ 'combined_name' => 'All Compliances' ] ];
            $srNo = 1;

            foreach($tempData as $cSortedData)
            {
                $cSortedData -> combined_name = date($GLOBALS['dateSupportArray'][1], strtotime($cSortedData -> com_period_from)) . ' - ' . date($GLOBALS['dateSupportArray'][1], strtotime($cSortedData -> com_period_to)) . ' ('. ( isset(COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $cSortedData -> frequency_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $cSortedData -> frequency_id ]['title'] : ERROR_VARS['notFound']) .') <span class="d-block font-sm site-black"><span class="font-medium">Set Name:</span> ' . $cSortedData -> task_set_name . '</span>';

                $this -> data['taskSetData'][ $srNo ] = $cSortedData;
                $srNo++;
            }
        }
    }

    private function getCircularDataOr404() {

        $filter = [ 
            'where' => 'ccsm.id = :id AND ccsm.deleted_at IS NULL AND ccsm.is_active = 1 AND cca.deleted_at IS NULL',
            'params' => [ 'id' => $this -> circularId ]
        ];

        // get data
        if(!empty($this -> circularId))        
        {    
            $query = "SELECT ccsm.*, 
                COALESCE(cca.name, 'na') AS auth_name FROM com_circular_set_master ccsm JOIN 
                com_circular_authority cca ON ccsm.authority_id = cca.id";

            $this -> data['db_data'] = get_all_data_query_builder(1, $this -> circularSetModel, 'com_circular_set_master', $filter, 'sql', $query);
        }

        if(is_object($this -> data['db_data']))
        {
            // get circular docs
            $multiDocsData = get_multi_docs_data($this, 1, [
                'circulr_id' => $this -> data['db_data'] -> id
            ]);

            if( is_array($multiDocsData) && sizeof($multiDocsData) > 0 )
                $this -> data['db_data'] -> multi_docs = $multiDocsData;
        }
        
        if(!isset($this -> data['db_data']) || 
            empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }
    }

    private function getDataOr404($optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> headerId ]
        ];

        if(!empty($optional))
            $filter['where'] .= $optional;

        // get data
        if(!empty($this -> headerId))
            $this -> data['db_data'] = $this -> headerModel -> getSingleCircularHeader($filter);

        if(!isset($this -> data['db_data']) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }
    }
}

?>