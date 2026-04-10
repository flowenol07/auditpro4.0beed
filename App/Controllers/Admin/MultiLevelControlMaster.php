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

class MultiLevelControlMaster extends Controller  {

    public $me = null, $request, $data, $periodWiseData, $methodArray, $periodId, $methodType, $empId;
    public $MLCMModel;

    public function __construct($me) {

        $this -> me = $me;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('multiLevelControlMaster') ],
        ];

        //Search in Select 
        $this -> data['need_select'] = true;
        
        // request object created
        $this -> request = new Request();

        // find current multi level control model
        $this -> MLCMModel = $this -> model('MultiLevelControlMaster');   

        //get all audit unit
        $model = $this -> model('YearModel'); 
        $this -> data['year_data'] = DBCommonFunc::yearMasterData($model, ['where' => 'deleted_at IS NULL']);
        $this -> data['year_data'] = generate_data_assoc_array($this -> data['year_data'], 'id');

        //get all audit unit
        $model = $this -> model('AuditUnitModel'); 
        $this -> data['audit_unit_data'] = DBCommonFunc::getAllAuditUnitData($model, ['where' => 'is_active = 1 AND deleted_at IS NULL']);
        $this -> data['audit_unit_data'] = generate_data_assoc_array($this -> data['audit_unit_data'], 'id');
        
        // add user type
        $this -> data['userType'][2] = $GLOBALS['userTypesArray'][2];
        $this -> empId = Session::get('emp_id');
    }

    private function findPeriodwiseData($userTypeId, $fyId, $auditUnitId, $periodId = null)
    {
        $resData = [];

        if(empty($fyId) || empty($auditUnitId))
            return $resData;

        $whereArray = [
            'where' => 'year_id = :year_id AND user_type_id = :user_type_id AND audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
            'params' => [
                'year_id' => $fyId,
                'user_type_id' => $userTypeId,
                'audit_unit_id' => $auditUnitId,
            ]
        ];

        if(!empty($periodId))
        {
            $whereArray['where'] .= ' AND id != :id';
            $whereArray['params']['id'] = $periodId;
        }

        // find data
        $resData = $this -> MLCMModel -> getAllMultiLevelControls($whereArray);

        if(is_array($resData) && sizeof($resData) > 0)
        {
            $tempResData = [];

            foreach($resData as $cMlcmData)
            {
                $cGenKey = $cMlcmData -> start_month_year . '_' . $cMlcmData -> end_month_year;

                if(!array_key_exists($cGenKey, $tempResData))
                    $tempResData[ $cGenKey ] = $cMlcmData;
            }

            $resData = $tempResData;
            unset($tempResData);
        }

        return $resData;
    }

    private function validateData(/*$validationType = 'add'*/)
    {
        Validation::validateData($this -> request, [
            'user_type_id' => 'required|array_key[user_data_array, user_type]',
            'year_id' => 'required|array_key[year_data_array, yearNotExist]',
            'audit_unit_id' => 'required|array_key[audit_unit_data_array, auditUnitNotExists]',
            'start_month_year' => 'required|regex[yearMonthRegex, yearMonthError]',
            'end_month_year' => 'required|regex[yearMonthRegex, yearMonthError]',
        ],[
            'user_data_array' => $this -> data['userType'],
            'year_data_array' => $this -> data['year_data'],
            'audit_unit_data_array' => $this -> data['audit_unit_data']
        ]);

        if(!$this -> request -> input( 'error' ) > 0)
        {
            $err = false;
            $newStartDate = $this -> request -> input( 'start_month_year' ) . '-01';
            $newEndDate = date('Y-m-t', strtotime($this -> request -> input( 'end_month_year' )) );

            $fYear = $this -> data['year_data'][ $this -> request -> input( 'year_id' ) ] -> fyear;
            $startDate = $fYear . '-04-01';
            $endDate = ($fYear + 1) . '-03-31';

            if( !($newEndDate > $newStartDate) )
            {
                $this -> request -> setInputCustom( 'end_month_year_err', Notifications::getNoti('endMonthGratorError') );
                $err = true;
            }
            
            if(!$err)
            {
                // check date within financial year
                if( !($newStartDate >= $startDate && $newStartDate <= $endDate) || 
                    !($newEndDate >= $startDate && $newEndDate <= $endDate))
                {
                    $this -> request -> setInputCustom( 'end_month_year_err', Notifications::getNoti('notFYMonthError') );
                    $err = true;
                }
            }

            if(!$err && isset($this -> periodWiseData) && is_array($this -> periodWiseData))
            {
                // check year data
                if(!sizeof($this -> periodWiseData) && ($startDate != $newStartDate || $endDate != $newEndDate) )
                {
                    $this -> request -> setInputCustom( 'year_id_err', Notifications::getNoti('yearlyDataNotExists') );
                    $err = true;
                }

                $cGenKey = $this -> request -> input( 'start_month_year' ) . '_' . $this -> request -> input( 'end_month_year' );

                if(!$err && array_key_exists($cGenKey, $this -> periodWiseData))
                {
                    $this -> request -> setInputCustom( 'year_id_err', Notifications::getNoti('periodWiseDataExists') );
                    $err = true;
                }
            }

            if($err)
                $this -> request -> setInputCustom( 'error', 1 );
        }

        //validation check
        if($this -> request -> input( 'error' ) > 0)   
            return false;
        else 
            return true;
    }

    private function postArray()
    {
        $dataArray = array(
            'user_type_id' => $this -> request -> input( 'user_type_id' ),
            'year_id' => $this -> request -> input( 'year_id' ),
            'section_type_id' => $this -> data['audit_unit_data'][ $this -> request -> input( 'audit_unit_id' ) ] -> section_type_id,
            'audit_unit_id' => $this -> request -> input( 'audit_unit_id' ),
            'start_month_year' => $this -> request -> input( 'start_month_year' ),
            'end_month_year' => $this -> request -> input( 'end_month_year' ),
            'admin_id' => $this -> empId,
        );

        return $dataArray;
    }

    public function index() {

        // top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('multiLevelControlMaster') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> MLCMModel, 
            $this -> MLCMModel -> getTableName(), [
                'where' => 'deleted_at IS NULL']);

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if( $this -> data['db_data_count'] > 0 )
            $this -> data['need_datatable'] = true;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> MLCMModel, ["audit_unit_id"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

            $srNo = 1;

            foreach($funcData['dbData'] as $cMultiLevelId => $cMultiLevelDetails)
            {
                $name = '<h4 class="lead font-medium text-primary mb-1">' . ($this -> data['audit_unit_data'][$cMultiLevelDetails -> audit_unit_id] -> name ?? ERROR_VARS['notFound'] ) . '</h4>

                <span class="d-block font-sm font-medium text-secondary">Period: ' . $cMultiLevelDetails -> start_month_year . ' - ' . $cMultiLevelDetails -> end_month_year . ' ( F.Y. ' . ($this -> data['year_data'][$cMultiLevelDetails -> year_id] -> fyear ?? ERROR_VARS['notFound'] ) . ' )</span>';

                $userType = string_operations(($this -> data['userType'][$cMultiLevelDetails -> user_type_id] ?? ERROR_VARS['notFound']), 'upper');

                $cDataArray = [
                    "sr_no" => $srNo,
                    "audit_unit_id" =>  $name,
                    "user_type_id" => $userType,
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {                      
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cMultiLevelDetails -> id), 'extra' => view_tooltip('Update') ]);

                    $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cMultiLevelDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                    $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/period-view/' . encrypt_ex_data($cMultiLevelDetails -> id), 'extra' => view_tooltip('View') ]);
                }                
                /*else
                    $cDataArray["action"] .= '-';*/

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function add() {

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add');
        $this -> me -> pageHeading = 'Add Periodwise Set';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> MLCMModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            // find data
            $this -> periodWiseData = $this -> findPeriodwiseData(
                $this -> request -> input('user_type_id'),
                $this -> request -> input('year_id'), 
                $this -> request -> input('audit_unit_id'));

            //validation check
            if(!$this -> validateData())
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> MLCMModel::insert(
                    $this -> MLCMModel -> getTableName(), 
                    $this -> postArray() //method call
                );

                if(!$result)
                    return Except::exc_404('Something Went Wrong');

                //after insert data redirect to scheme
                Validation::flashErrorMsg('periodWiseDataAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('multiLevelControlMaster') );
            }

        });

    }

    public function update($getRequest) {

        $this -> periodId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> periodId));
        $this -> me -> pageHeading = 'Update Periodwise Set';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> periodId);

        $this -> data['btn_type'] = 'update';

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            // find data
            $this -> periodWiseData = $this -> findPeriodwiseData(
                $this -> request -> input('user_type_id'),
                $this -> request -> input('year_id'), 
                $this -> request -> input('audit_unit_id'), 
                $this -> periodId );

            //validation check
            if(!$this -> validateData())
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> MLCMModel::update(
                    $this -> MLCMModel -> getTableName(), 
                    $this -> postArray(), 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> periodId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('errorSaving') );

                //after insert data redirect to scheme
                Validation::flashErrorMsg('periodWiseDataUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('multiLevelControlMaster') );
            }
        });
    }

    public function delete($getRequest) {

        $this -> periodId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> periodId ) ;

        $result = $this -> MLCMModel::delete(
            $this -> MLCMModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> periodId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to scheme
        Validation::flashErrorMsg('periodWiseDataDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('multiLevelControlMaster') );
    }

    public function periodView($getRequest) {

        $this -> periodId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> periodId );
        $this -> me -> pageHeading = 'View Periodwise Set';

        $this -> data['db_data'] -> advances_scheme_data = null;
        $this -> data['db_data'] -> deposits_scheme_data = null;
        $this -> data['db_menu_data'] = null;
        $this -> data['db_category_data'] = null;
        $this -> data['db_header_data'] = null;
        $this -> data['db_question_data'] = [];

        // find schemes
        if(!empty($this -> data['db_data'] -> advances_scheme_ids))
        {
            $this -> data['db_data'] -> advances_scheme_ids;
            $this -> data['db_data'] -> advances_scheme_data = $this -> findSchemes($this -> data['db_data'] -> advances_scheme_ids, 2);
        }

        if(!empty($this -> data['db_data'] -> deposits_scheme_ids))
            $this -> data['db_data'] -> deposits_scheme_data = $this -> findSchemes($this -> data['db_data'] -> deposits_scheme_ids);

        // find menu
        if(!empty($this -> data['db_data'] -> menu_ids))
            $this -> data['db_menu_data'] = $this -> findMenus( $this -> data['db_data'] -> menu_ids, $this -> data['db_data'] -> section_type_id );

        // find categories
        if( is_array($this -> data['db_menu_data']) && 
            sizeof($this -> data['db_menu_data']) > 0 &&
            !empty($this -> data['db_data'] -> cat_ids) )
            $this -> data['db_category_data'] = $this -> findCategories(array_keys($this -> data['db_menu_data']), $this -> data['db_data'] -> cat_ids);

        // find header
        if( is_array($this -> data['db_category_data']) && 
            sizeof($this -> data['db_category_data']) > 0 &&
            !empty($this -> data['db_data'] -> header_ids) )
            $this -> data['db_header_data'] = $this -> findHeader($this -> data['db_category_data'], $this -> data['db_data'] -> header_ids);

        $resData = [];

        if( is_array($this -> data['db_header_data']) && 
            sizeof($this -> data['db_header_data']) > 0 &&
            !empty($this -> data['db_data'] -> question_ids) )        
            $resData = $this -> findQuestions(array_keys($this -> data['db_header_data']), $this -> data['db_data'] -> question_ids);

        if( isset($resData['questions']) && sizeof($resData['questions']) > 0 )
            $this -> data['db_question_data'] = $resData['questions'];

        if( isset($resData['headers']) && sizeof($resData['headers']) > 0 )
            $this -> data['db_header_data'] += $resData['headers'];

        // if( !is_array($this -> data['db_question_data']) || 
        //     (is_array($this -> data['db_question_data']) && !(sizeof($this -> data['db_question_data']) > 0)) )
        //     $this -> generateExeception('noQuestionFoundError');

        unset($resData);

        // mix header questions data
        $this -> data['db_question_data_mix'] = $this -> headerQuestionMix(
            $this -> data['db_question_data'], 
            $this -> data['db_header_data'] );

        // method call
        $this -> seprateSubsetData();

        // method call
        // $this -> data['db_data'] = $this -> findCommonData($this -> data['db_data']);

        // print_r($this -> data['db_data']);
        // exit;

        // top data container 
        $this -> data['data_container'] = true;
        $this -> data['remove_container'] = true;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'view');
    }

    private function generateBackBtn() {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('multiLevelControlMaster') . '/period-view/' . encrypt_ex_data($this -> data['db_data'] -> id) ],
        ];
    }

    public function updateSchemeDeposits($getRequest) {
        
        // method call
        $this -> controlPeriodWiseUpdateMethods($getRequest, 1);

    }

    public function updateSchemeAdvances($getRequest) {

        // method call
        $this -> controlPeriodWiseUpdateMethods($getRequest, 2);
    }

    public function updateMenu($getRequest) {

        // method call
        $this -> controlPeriodWiseUpdateMethods($getRequest, 3);
    }

    public function updateCategory($getRequest) {

        // method call
        $this -> controlPeriodWiseUpdateMethods($getRequest, 4);
    }

    public function updateQuestions($getRequest) {

        // method call
        $this -> controlPeriodWiseUpdateMethods($getRequest, 5);
    }

    // new update kunal 03.01.2024
    public function updateToAllAuditUnits($getRequest) {

        $this -> periodId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/period-view/' . encrypt_ex_data($this -> periodId));

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> periodId);

        // check section_type_id
        if($this -> data['db_data'] -> section_type_id != 1)
        {
            Except::exc_404();
            exit;
        }

        $err = null;

        if(empty($this -> data['db_data'] -> menu_ids))
            $err = 'Please select menu data';

        if(empty($this -> data['db_data'] -> cat_ids))
            $err = 'Please select category data';

        if(empty($this -> data['db_data'] -> advances_scheme_ids))
            $err = 'Please select advances scheme data';

        if(empty($this -> data['db_data'] -> deposits_scheme_ids))
            $err = 'Please select deposits scheme data';

        if(empty($this -> data['db_data'] -> header_ids) || empty($this -> data['db_data'] -> question_ids))
            $err = 'Please select questions data';

        if( !empty($err) )
        {
            Validation::flashErrorMsg( ('<b>Warning:</b>' . $err), 'warning');
            Redirect::to( $this -> me -> url );
        }

        // update to all audti units with section_type_id = 1 and period
        $result = $this -> MLCMModel::update(
            $this -> MLCMModel -> getTableName(), 
            [
                'menu_ids' => $this -> data['db_data'] -> menu_ids,
                'cat_ids' => $this -> data['db_data'] -> cat_ids,
                'header_ids' => $this -> data['db_data'] -> header_ids,
                'question_ids' => $this -> data['db_data'] -> question_ids,
                'advances_scheme_ids' => $this -> data['db_data'] -> advances_scheme_ids,
                'deposits_scheme_ids' => $this -> data['db_data'] -> deposits_scheme_ids,
                'admin_id' => $this -> empId,
            ], 
            [
                'where' => 'id != :id 
                    AND user_type_id = :user_type_id
                    AND section_type_id = :section_type_id
                    AND start_month_year = :start_month_year 
                    AND end_month_year = :end_month_year 
                    AND deleted_at IS NULL',
                'params' => [ 
                    'id' => $this -> data['db_data'] -> id,
                    'user_type_id' => $this -> data['db_data'] -> user_type_id,
                    'section_type_id' => $this -> data['db_data'] -> section_type_id,
                    'start_month_year' => $this -> data['db_data'] -> start_month_year,
                    'end_month_year' => $this -> data['db_data'] -> end_month_year
                ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        // after insert data redirect to scheme
        Validation::flashErrorMsg('periodWiseDataUpdatedSuccess', 'success');
        Redirect::to( $this -> me -> url );

    }
/**
 * Method to update audit_assesment_master based on radio button selection
 */
public function updateAssessment($getRequest) {

    $this -> periodId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //set form url
    $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/period-view/' . encrypt_ex_data($this -> periodId));

    // get data //method call
    $this -> data['db_data'] = $this -> getDataOr404($this -> periodId);

    // Get update type from POST data
    $updateType = isset($_POST['update_type']) ? $_POST['update_type'] : '';
    
    // If not in POST, try to get from request object
    if(empty($updateType)) {
        $updateType = $this -> request -> input('update_type');
    }
    
    // Default to 'current' if not set
    if(empty($updateType)) {
        $updateType = 'current';
    }

    // check section_type_id
    if($this -> data['db_data'] -> section_type_id != 1)
    {
        Except::exc_404();
        exit;
    }

    $err = null;

    if(empty($this -> data['db_data'] -> menu_ids))
        $err = 'Please select menu data';

    if(empty($this -> data['db_data'] -> cat_ids))
        $err = 'Please select category data';

    if(empty($this -> data['db_data'] -> advances_scheme_ids))
        $err = 'Please select advances scheme data';

    if(empty($this -> data['db_data'] -> deposits_scheme_ids))
        $err = 'Please select deposits scheme data';

    if(empty($this -> data['db_data'] -> header_ids) || empty($this -> data['db_data'] -> question_ids))
        $err = 'Please select questions data';

    if( !empty($err) )
    {
        Validation::flashErrorMsg( ('<b>Warning:</b>' . $err), 'warning');
        Redirect::to( $this -> me -> url );
    }

    $postArray = [
        'menu_ids' => $this -> data['db_data'] -> menu_ids,
        'cat_ids' => $this -> data['db_data'] -> cat_ids,
        'header_ids' => $this -> data['db_data'] -> header_ids,
        'question_ids' => $this -> data['db_data'] -> question_ids,
        'advances_scheme_ids' => $this -> data['db_data'] -> advances_scheme_ids,
        'deposits_scheme_ids' => $this -> data['db_data'] -> deposits_scheme_ids,
    ];

    // Get the audit assessment model
    $auditAssessmentModel = $this -> model('AuditAssesmentModel');

    if(!$auditAssessmentModel) {
        return Except::exc_404('Audit Assessment model not found');
    }

    // Get the table name
    $table = $auditAssessmentModel->getTableName();

    // Get the financial year from the year_id
    $yearId = $this -> data['db_data'] -> year_id;
    $financialYear = isset($this -> data['year_data'][$yearId]) ? $this -> data['year_data'][$yearId]->fyear : date('Y');
    
    // Define financial year range
    $financialYearStart = $financialYear . '-04-01';
    $financialYearEnd = ($financialYear + 1) . '-03-31';

    $result = false;
    $updatedCount = 0;
    $successMessage = '';

    if($updateType == 'current') {
        // Update only the current assessment for this specific audit unit
        $whereArray = [
            'where' => 'audit_unit_id = :audit_unit_id 
                AND assesment_period_from >= :financial_year_start 
                AND assesment_period_to <= :financial_year_end 
                AND audit_status_id = 1 
                AND deleted_at IS NULL',
            'params' => [
                'audit_unit_id' => $this -> data['db_data'] -> audit_unit_id,
                'financial_year_start' => $financialYearStart,
                'financial_year_end' => $financialYearEnd
            ]
        ];
        
        // Get all assessments for this audit unit in the financial year
        $assessments = $auditAssessmentModel->getAllAuditAssesment($whereArray);
        $foundCount = is_array($assessments) ? count($assessments) : 0;
        
        if($foundCount > 0) {
            // Update all matching assessments for this audit unit
            $result = $auditAssessmentModel::update(
                $table,
                $postArray,
                $whereArray
            );
            $updatedCount = $result ? $foundCount : 0;
            
            $successMessage = $updatedCount . ' branch assessment(s) for Audit Unit ID ' . $this -> data['db_data'] -> audit_unit_id . ' updated successfully.';
        } else {
            $successMessage = 'No matching assessment found for Audit Unit ID: ' . $this -> data['db_data'] -> audit_unit_id . ' in financial year ' . $financialYear;
        }
        
    } else if($updateType == 'all') {
        // First check if there are ANY active assessments in this financial year
        $checkWhere = [
            'where' => 'assesment_period_from >= :financial_year_start 
                AND assesment_period_to <= :financial_year_end 
                AND audit_status_id = 1 
                AND deleted_at IS NULL',
            'params' => [
                'financial_year_start' => $financialYearStart,
                'financial_year_end' => $financialYearEnd
            ]
        ];
        
        $activeAssessments = $auditAssessmentModel->getAllAuditAssesment($checkWhere);
        $activeCount = is_array($activeAssessments) ? count($activeAssessments) : 0;
        
        if($activeCount == 0) {
            // No active assessments found in this financial year
            Validation::flashErrorMsg('Assessment not found with status = 1 (Active) for financial year ' . $financialYear, 'error');
            Redirect::to( $this -> me -> url );
        }
        
        // Update ALL assessments across ALL audit units with audit_status_id = 1 in this financial year
        $whereArray = [
            'where' => 'assesment_period_from >= :financial_year_start 
                AND assesment_period_to <= :financial_year_end 
                AND audit_status_id = 1 
                AND deleted_at IS NULL',
            'params' => [
                'financial_year_start' => $financialYearStart,
                'financial_year_end' => $financialYearEnd
            ]
        ];
        
        // Perform the update on ALL active assessments in this financial year
        $result = $auditAssessmentModel::update(
            $table,
            $postArray,
            $whereArray
        );
        
        if($result) {
            $updatedCount = $activeCount;
            $successMessage = $updatedCount . ' branch assessment(s) updated successfully across all audit units';
        } else {
            $successMessage = 'Update failed for branch assessments';
        }
    }

    if(!$result || $updatedCount == 0) {
        Validation::flashErrorMsg($successMessage, 'warning');
    } else {
        Validation::flashErrorMsg($successMessage, 'success');
    }
    
    Redirect::to( $this -> me -> url );
}
    private function generateExeception($msg = '')
    {
        if(!empty($msg))
            $msg = Notifications::getNoti($msg);

        if(empty($msg))
            $msg = Notifications::getNoti('somethingWrong');

        Except::exc_404( $msg );
        exit;
    }

    private function headerQuestionMix($dbQuestionData, $dbHeaderData) {

        $dbQuestionDataMix = [];

        // mix data
        if(is_array($dbQuestionData) && sizeof($dbQuestionData) > 0)
        {
            foreach ($dbQuestionData as $cQuesId => $cQuesData)
            {
                if(!array_key_exists($cQuesData -> header_id, $dbQuestionDataMix))
                {
                    $dbQuestionDataMix[ $cQuesData -> header_id ] = $dbHeaderData[ $cQuesData -> header_id ];

                    // add header key
                    $dbQuestionDataMix[ $cQuesData -> header_id ] -> db_questions = [];
                }

                // push question
                $dbQuestionDataMix[ $cQuesData -> header_id ] -> db_questions[ $cQuesData -> id ] = $cQuesData;
            }
        }

        return $dbQuestionDataMix;
    }

    private function controlPeriodWiseUpdateMethods($getRequest, $methodType = 1)
    {
        $this -> periodId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        $methodTypeArray = [

            1 => [ 'title' => 'Periodwise Set - Deposit Schemes', 
                    'view' => 'scheme-form', 
                    'db_data' => 'db_scheme_data', 
                    'checkbox_key' => 'multi_type_check',
                    'checkbox_err' => 'schemeCheckError'
                ],

            2 => [ 'title' => 'Periodwise Set - Advances Schemes', 
                    'view' => 'scheme-form', 
                    'db_data' => 'db_scheme_data', 
                    'checkbox_key' => 'multi_type_check',
                    'checkbox_err' => 'schemeCheckError',
                ],

            3 => [ 'title' => 'Periodwise Set - Menus', 
                    'view' => 'menu-form', 
                    'db_data' => 'db_menu_data', 
                    'checkbox_key' => 'multi_type_check',
                    'checkbox_err' => 'menuCheckError'
                ],

            4 => [ 'title' => 'Periodwise Set - Categories', 
                    'view' => 'category-form', 
                    'db_data' => 'db_category_data', 
                    'checkbox_key' => 'multi_type_check',
                    'checkbox_err' => 'categoryCheckError'
                ],

            5 => [ 'title' => 'Periodwise Set - Question', 
                    'view' => 'question-form', 
                    'db_data' => 'db_question_data', 
                    'checkbox_key' => 'multi_type_check',
                    'checkbox_err' => 'questionCheckError'
                ]
        ];

        if( !array_key_exists($methodType, $methodTypeArray) )
        {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> periodId );

        $this -> methodType = $methodType;
        $this -> methodArray = $methodTypeArray[ $methodType ];
        $this -> me -> pageHeading = $this -> methodArray['title'];
        unset($methodType);

        // find menu
        if(in_array($this -> methodType, [4,5]) && !empty($this -> data['db_data'] -> menu_ids))
        {
            $this -> data['db_menu_data'] = $this -> findMenus( 
                $this -> data['db_data'] -> menu_ids, 
                $this -> data['db_data'] -> section_type_id 
            );
        }

        // find category
        if( in_array($this -> methodType, [5]) && 
            !empty($this -> data['db_data'] -> cat_ids) && 
            isset($this -> data['db_menu_data']) && 
            sizeof($this -> data['db_menu_data']) > 0)
        {
            $this -> data['db_category_data'] = $this -> findCategories( 
                array_keys($this -> data['db_menu_data']), 
                $this -> data['db_data'] -> cat_ids 
            );
        }

        // for both schemes advances // deposits
        if( in_array($this -> methodType, [1,2]) )
        {
            $this -> data['db_data'] -> scheme_type = $this -> methodType;

            // find active schemes
            $this -> data['db_scheme_data'] = $this -> findSchemes( null, $this -> methodType );
        }
        elseif($this -> methodType == 3)
        {
            // find active menus
            $this -> data['db_menu_data'] = $this -> findMenus( null, $this -> data['db_data'] -> section_type_id );
        }
        elseif($this -> methodType == 4)
        {
            // need menu
            if( !isset($this -> data['db_menu_data']) || 
                (isset($this -> data['db_menu_data']) && !sizeof($this -> data['db_menu_data']) > 0))
                $this -> generateExeception('menuDataError');

            $this -> data['db_category_data'] = $this -> findCategories(array_keys($this -> data['db_menu_data']), null);
        }
        elseif($this -> methodType == 5)
        {
            // need menu
            if( !isset($this -> data['db_menu_data']) || 
                (isset($this -> data['db_menu_data']) && !sizeof($this -> data['db_menu_data']) > 0))
                $this -> generateExeception('menuDataError');

            // need categry
            if( !isset($this -> data['db_category_data']) || 
                (isset($this -> data['db_category_data']) && !sizeof($this -> data['db_category_data']) > 0))
                $this -> generateExeception('categoryDataError');

            // need header
            $this -> data['db_header_data'] = $this -> findHeader($this -> data['db_category_data'], null);

            if( !is_array($this -> data['db_header_data']) || 
                (is_array($this -> data['db_header_data']) && !sizeof($this -> data['db_header_data']) > 0))
                $this -> generateExeception('headerDataError');

            $this -> data['db_question_data'] = [];
            $resData = $this -> findQuestions(array_keys($this -> data['db_header_data']), null);

            if(sizeof($resData['questions']) > 0)
                $this -> data['db_question_data'] = $resData['questions'];

            if(sizeof($resData['headers']) > 0)
                $this -> data['db_header_data'] += $resData['headers'];

            if( !is_array($this -> data['db_question_data']) || 
                (is_array($this -> data['db_question_data']) && !sizeof($this -> data['db_question_data']) > 0))
                $this -> generateExeception('noQuestionFoundError');

            unset($resData);

            // mix header questions data
            $this -> data['db_question_data_mix'] = $this -> headerQuestionMix(
                $this -> data['db_question_data'], 
                $this -> data['db_header_data'] );

            // method call
            $this -> seprateSubsetData();
        }

        // top data container 
        $this -> data['data_container'] = true;
        $this -> generateBackBtn(); //method call

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . $this -> methodArray['view'], [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            $checkedCheckBoxes = $this -> request -> input($this -> methodArray['checkbox_key']);

            if(!empty($checkedCheckBoxes))
                $checkedCheckBoxes = explode( ',', $checkedCheckBoxes );
            
            $checkedCheckBoxes = is_array($checkedCheckBoxes) ? $checkedCheckBoxes : [];
            
            // validation check
            if( !sizeof($this -> data[ $this -> methodArray['db_data'] ]) || 
                !$this -> request -> has($this -> methodArray['checkbox_key']) ||
                !sizeof($checkedCheckBoxes) ||
                sizeof( array_diff($checkedCheckBoxes, array_keys($this -> data[ $this -> methodArray['db_data'] ])) ) > 0 )
            {   
                $this -> request -> setInputCustom(
                    ($this -> methodArray['checkbox_key'] . '_err'), 
                    Notifications::getNoti($this -> methodArray['checkbox_err']) 
                );

                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . $this -> methodArray['view'], [ 'request' => $this -> request ]);
            } 
            else
            {
                // update // method call
                $postArray = [];

                switch ($this -> methodType) {

                    case '1':
                        $postArray['deposits_scheme_ids'] = implode(',', $checkedCheckBoxes);
                        break;

                    case '2':
                        $postArray['advances_scheme_ids'] = implode(',', $checkedCheckBoxes);
                        break;

                    case '3':
                        $postArray['menu_ids'] = implode(',', $checkedCheckBoxes);
                        break;

                    case '4':
                        $postArray['cat_ids'] = implode(',', $checkedCheckBoxes);
                        break;

                    case '5': {

                        $postArray['header_ids'] = [];

                        // push header ids
                        foreach($checkedCheckBoxes as $cQuesId)
                        {
                            if( array_key_exists($cQuesId, $this -> data['db_question_data']) && 
                                !in_array( $this -> data['db_question_data'][ $cQuesId ] -> header_id, $postArray['header_ids']))
                                $postArray['header_ids'][] = $this -> data['db_question_data'][ $cQuesId ] -> header_id;
                        }

                        if(sizeof($postArray['header_ids']) > 0)
                        {
                            $postArray['header_ids'] = implode(',', $postArray['header_ids']);
                            $postArray['question_ids'] = implode(',', $checkedCheckBoxes);
                        }
                        else
                            $postArray = [];
                                                
                        break;
                    }
                }

                // error happen
                if(!sizeof($postArray) > 0)
                    $this -> generateExeception('somethingWrong');
                
                // print_r($postArray);

                // method call
                $this -> updatePeriodWiseData($postArray);

                // method call
                // if(in_array($this -> methodType, [3,4,5]))
                //     $this -> manageMultiControl($this -> data['db_data'] -> id);
            }

        });
    }

    private function updatePeriodWiseData($postArray)
    {
        $result = $this -> MLCMModel::update(
            $this -> MLCMModel -> getTableName(), 
            $postArray, 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> data['db_data'] -> id ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        // after insert data redirect to scheme
        Validation::flashErrorMsg('periodWiseDataUpdatedSuccess', 'success');
        Redirect::to( $this -> data['topBtnArr']['default']['href'] );
    }

    private function findCommonData($dbData)
    {
        $dbData -> advances_scheme_ids_arr = [];

        // find advance scheme
        if(!empty($dbData -> advances_scheme_ids))
            $dbData -> advances_scheme_ids_arr = $this -> findSchemes($dbData -> advances_scheme_ids, 2);

        $dbData -> deposits_scheme_ids_arr = [];

        // find deposit scheme
        if(!empty($dbData -> deposits_scheme_ids))
            $dbData -> deposits_scheme_ids_arr = $this -> findSchemes($dbData -> deposits_scheme_ids);

        $dbData -> menu_ids_arr = [];

        // find menu
        if(!empty($dbData -> menu_ids))
            $dbData -> menu_ids_arr = $this -> findMenus($dbData -> menu_ids);

        $dbData -> cat_ids_arr = [];

        // find category
        if(!empty($dbData -> cat_ids) && sizeof($dbData -> menu_ids_arr) > 0)
            $dbData -> cat_ids_arr = $this -> findCategories(array_keys($dbData -> menu_ids_arr), $dbData -> cat_ids);

        $dbData -> header_ids_arr = [];

        // find header
        if(!empty($dbData -> header_ids) && sizeof($dbData -> cat_ids_arr) > 0)
            $dbData -> header_ids_arr = $this -> findHeader($dbData -> cat_ids_arr, $dbData -> header_ids);

        $dbData -> question_ids_arr = [];

        // find questions
        if(!empty($dbData -> question_ids) && sizeof($dbData -> header_ids_arr) > 0)
        {
            $resData = $this -> findQuestions(array_keys($dbData -> header_ids_arr), $dbData -> question_ids);

            if(sizeof($resData['questions']) > 0)
                $dbData -> question_ids_arr = $resData['questions'];

            if(sizeof($resData['headers']) > 0)
                $dbData -> header_ids_arr += $resData['headers'];

            unset($resData);
        }

        $dbData -> db_question_data_mix = $this -> headerQuestionMix(
            $dbData -> question_ids_arr, 
            $dbData -> header_ids_arr );

        return $dbData;
    }

    private function findSchemes($schemeIds = null, $type = 1) {

        $model = $this -> model('SchemeModel'); 
        $table = $model -> getTableName();

        $select = " SELECT sch.id, sch.scheme_type_id, sch.category_id, sch.scheme_code, sch.name, 
                    COALESCE(cm.name, '". ERROR_VARS['notFound'] ."') AS cat_name FROM 
                    ". $table ." sch LEFT JOIN category_master cm ON sch.category_id = cm.id";

        $whereArray = array(
            'where' => 'sch.scheme_type_id = :scheme_type_id AND sch.is_active = 1 AND sch.deleted_at IS NULL',
            'params' => [ 'scheme_type_id' => $type ]
        );

        if(!empty($schemeIds))
            $whereArray['where'] .= ' AND sch.id IN ('. $schemeIds .')';

        $findData = get_all_data_query_builder(2, $model, $table, $whereArray, 'sql', $select);
        $findData = generate_data_assoc_array($findData, 'id');

        return $findData;
    }

    private function findMenus($menuIds = null, $type = 1) {

        $model = $this -> model('MenuModel'); 

        $whereArray = array(
            'where' => 'section_type_id = :section_type_id AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ 'section_type_id' => $type ]
        );

        if(!empty($menuIds))
            $whereArray['where'] .= ' AND id IN ('. $menuIds .')';

        $findData = $model -> getAllMenu($whereArray);
        $findData = generate_data_assoc_array($findData, 'id');

        return $findData;

    }

    private function findCategories($menuIds, $dbCatIds = null) {

        $model = $this -> model('CategoryModel');
        $table = $model -> getTableName();

        if(!is_array($menuIds))
            return [];

        $select = " SELECT cm.id, cm.menu_id, cm.name, cm.linked_table_id, cm.question_set_ids,
                    COALESCE(mm.name, '". ERROR_VARS['notFound'] ."') AS menu_name FROM 
                    ". $table ." cm LEFT JOIN menu_master mm ON cm.menu_id = mm.id";
        
        $whereArray = array(
            'where' => 'cm.menu_id IN ('. implode(',', $menuIds) .') AND cm.is_active = 1 AND cm.deleted_at IS NULL',
            'params' => [ ]
        );

        if(!empty($dbCatIds))
            $whereArray['where'] .= ' AND cm.id IN ('. $dbCatIds .')';

        $findData = get_all_data_query_builder(2, $model, $table, $whereArray, 'sql', $select);
        $findData = generate_data_assoc_array($findData, 'id');

        return $findData;
    }

    private function findHeader($categoryData, $dbHeaderId = null) {

        if(!is_array($categoryData))
            return [];

        $setData = [];

        // loop to category data for get set
        foreach($categoryData as $cCatId => $cCatDetails)
        {
            $cSetData = null;

            if(!empty($cCatDetails -> question_set_ids))
                $cSetData = explode(',', $cCatDetails -> question_set_ids);

            if(is_array($cSetData) && sizeof($cSetData) > 0)
            {
                foreach($cSetData as $cSetId)
                {
                    if(!in_array($cSetId, $setData))
                        $setData[] = $cSetId;
                }
            }
        }

        if(!sizeof($setData) > 0)
            return [];

        $model = $this -> model('QuestionHeaderModel'); 

        $whereArray = array(
            'where' => 'question_set_id IN ('. implode(',', $setData) .') AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ ]
        );

        if(!empty($dbHeaderId))
            $whereArray['where'] .= ' AND id IN ('. $dbHeaderId .')';

        $findData = $model -> getAllQuestionHeader($whereArray);
        $findData = generate_data_assoc_array($findData, 'id');

        return $findData;
    }

    private function findQuestions($headerIds, $dbQuestionId = null, $subsetCheck = false) {

        $model = $this -> model('QuestionMasterModel'); 

        if(!is_array($headerIds))
            return null;

        $resData = [ 'questions' => [], 'headers' => [] ];

        $whereArray = array(
            'where' => 'header_id IN ('. implode(',', $headerIds) .') AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ ]
        );

        if(!empty($dbQuestionId))
            $whereArray['where'] .= ' AND id IN ('. $dbQuestionId .')';

        $findData = $model -> getAllQuestions($whereArray);

        if(is_array($findData) && sizeof($findData) > 0)
        {
            $tempFindData = $findData;
            $findData = [];

            foreach($tempFindData as $cQuesDetails)
            {
                if(!array_key_exists($cQuesDetails -> id, $findData))
                {
                    $resData['questions'][ $cQuesDetails -> id ] = $cQuesDetails;

                    if(!$subsetCheck && 
                        $cQuesDetails -> option_id == 5 && 
                        !empty($cQuesDetails -> subset_multi_id))
                    {
                        // get headers
                        $findHeader = $this -> findHeader([ (object) [
                            'id' => 0,
                            'question_set_ids' => $cQuesDetails -> subset_multi_id
                        ]]);

                        if(is_array($findHeader) && sizeof($findHeader) > 0)
                        {
                            // find questions
                            $recursiveResData = $this -> findQuestions(array_keys($findHeader), $dbQuestionId, 1);

                            if( is_array($recursiveResData) && 
                                sizeof($recursiveResData['questions']) > 0)
                            {
                                foreach($findHeader as $cHeaderId => $cHeaderDetails)
                                    $findHeader[ $cHeaderId ] -> name = string_operations(('Subset - ' . $cHeaderDetails -> name), 'upper');

                                $resData['headers'] += $findHeader;
                                $resData['questions'] += $recursiveResData['questions'];
                            }

                            unset($recursiveResData);
                        }

                        unset($findHeader);
                    }
                }
            }
        }

        unset($findData);

        return $resData;
    }

    private function manageMultiControl($periodId)
    {
        // after update check data and update
        if(!empty($periodId))
        {
            // get data // method call
            $this -> data['db_data'] = $this -> getDataOr404( $periodId );
            
            $menuIds = $this -> data['db_data'] -> menu_ids;

            if(!empty($menuIds))
                $menuIds = explode(',', $menuIds);

            $updateArray = [];
            
            if(is_array($menuIds) && sizeof($menuIds) > 0)
            {
                // has data find matching categories
                $categoryData = $this -> findCategories($menuIds);

                if(is_array($categoryData) && sizeof($categoryData) > 0)
                {
                    
                }
            }
        }
    }

    private function seprateSubsetData() {

        // find subset data
        $model = $this -> model('QuestionSetModel');
        
        $subsetData = $model -> getAllQuestionSet([
            'where' => 'set_type_id = 2 AND is_active = 1 AND deleted_at IS NULL',
            'params' => []
        ]);

        $subsetData = generate_data_assoc_array($subsetData, 'id');

        $setData = [ 'set' => [], 'subset' => [] ];            

        if( is_array($this -> data['db_question_data_mix']) && 
            sizeof($this -> data['db_question_data_mix']) > 0 )
        {
            // loop on header and question data
            foreach($this -> data['db_question_data_mix'] as $cHeaderId => $cHeaderDetails)
            {
                // push set wise data // check for subet
                $subset = false;

                if( is_array($subsetData) && 
                    array_key_exists($cHeaderDetails -> question_set_id, $subsetData) && 
                    !array_key_exists($cHeaderDetails -> question_set_id, $setData['subset']))
                    $setData['subset'][ $cHeaderDetails -> question_set_id ] = [];

                // push in subset
                if(array_key_exists($cHeaderDetails -> question_set_id, $setData['subset']))
                {
                    if(is_array($subsetData) && array_key_exists($cHeaderDetails -> question_set_id, $subsetData))
                    {
                        // subset name
                        $cHeaderDetails -> name .= ' ('. $subsetData[ $cHeaderDetails -> question_set_id ] -> name .')';
                    }

                    $setData['subset'][ $cHeaderDetails -> question_set_id ][ $cHeaderId ] = $cHeaderDetails;
                    $subset = true;
                }

                if(!$subset && !array_key_exists($cHeaderDetails -> question_set_id, $setData['set']))
                    $setData['set'][ $cHeaderDetails -> question_set_id ] = [];

                if(!$subset && array_key_exists($cHeaderDetails -> question_set_id, $setData['set']))
                    $setData['set'][ $cHeaderDetails -> question_set_id ][ $cHeaderId ] = $cHeaderDetails;
            }
        }

        // $this -> data['db_set_data'] = $setData;

        // print_r($setData);
        // exit;

        if(is_array($setData['subset']) && sizeof($setData['subset']) > 0)
        {
            // add menu // subset
            $this -> data['db_menu_data']['subset'] = (object) [
                'name' => 'Common Subsets',
                'category_data' => [
                    'subset' => (object) [
                        'name' => 'Common Subsets',
                        'set_data' =>  $setData['subset']
                    ],
                ]
            ];
        }

        // sort data menu, category, header, question
        $sortData = $this -> data['db_menu_data'];

        if(is_array($this -> data['db_category_data']) && sizeof($this -> data['db_category_data']) > 0):

            // category loop
            foreach($this -> data['db_category_data'] as $cCatId => $cCatDetails)
            {
                if(!array_key_exists($cCatDetails -> menu_id, $sortData))
                    $sortData[ $cCatDetails -> menu_id ] -> category_data = [];

                $cCatDetails -> set_ids = !empty($cCatDetails -> question_set_ids) ? explode(',', $cCatDetails -> question_set_ids) : [];
                $cCatDetails -> set_data = [];

                if(is_array($cCatDetails -> set_ids) && sizeof($cCatDetails -> set_ids) > 0)
                {
                    foreach($cCatDetails -> set_ids as $cSetId)
                    {
                        if(!array_key_exists($cSetId, $cCatDetails -> set_data) && 
                            is_array($setData['set']) && sizeof($setData['set']) > 0 && 
                            array_key_exists($cSetId, $setData['set']))
                        {
                            $cCatDetails -> set_data[ $cSetId ] = $setData['set'][ $cSetId ];
                        }
                    }
                }

                // push category
                $sortData[ $cCatDetails -> menu_id ] -> category_data[ $cCatId ] = $cCatDetails;
            }

            // re assign data
            $this -> data['db_category_data'] = $sortData;

        endif;

        // unset vars
        unset($setData, $sortData);
    }

    private function getDataOr404($periodId, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $periodId ]
        ];

        // get data
        $this -> data['db_data'] = $this -> MLCMModel -> getSingleMultiLevelControl($filter);

        if(empty($periodId) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        return $this -> data['db_data'];
    }
}

?>