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

class QuestionMaster extends Controller  {

    public $me = null, $setId, $quesId, $data, $request, $rmvId;
    public $questionMasterModel, $questionSetModel;

    public function __construct($me) {

        $this -> me = $me;

        // request object created
        $this -> request = new Request();    

        //Search in Select 
        $this -> data['need_select'] = true;

        // ------------------- question set logic ------------------

        $this -> setId = decrypt_ex_data($this -> request -> input('set'));

        // require question set model
        $this -> questionSetModel = $this -> model('QuestionSetModel');

        // find model
        $this -> questionMasterModel = $this -> model('QuestionMasterModel');
        $broaderAreaModel = $this -> model('BroaderAreaModel');
        // $this -> questionMasterModel = $this -> model('QuestionMasterModel');

        // -------------------------------------------------

        //select risk category
        $defaultModel = $this -> model('RiskCategoryModel');

        $this -> data['db_risk_category'] = $defaultModel -> getAllRiskCategory([
            'where' => 'deleted_at IS NULL AND is_active = 1', 'params' => [ ]
        ]);

        //convert to select 
        $this -> data['db_risk_category'] = generate_array_for_select($this -> data['db_risk_category'], 'id', 'risk_category');

        // -------------------------------------------------

        //select risk control
        $defaultModel = $this -> model('RiskControlModel');

        $this -> data['db_risk_control'] = $defaultModel -> getAllRiskControl([
            'where' => 'deleted_at IS NULL AND is_active = 1', 'params' => [ ]
        ]);

        //convert array
        $this -> data['db_risk_control'] = generate_data_assoc_array($this -> data['db_risk_control'], 'id');

        // -------------------------------------------------

        //select area of audit
        $defaultModel = $this -> model('BroaderAreaModel');

        $this -> data['db_area_of_audit'] = $defaultModel -> getAllBroaderArea([
            'where' => 'deleted_at IS NULL', 'params' => [ ]
        ]);

        //convert array
        $this -> data['db_area_of_audit'] = generate_data_assoc_array($this -> data['db_area_of_audit'], 'id');

        // -------------------------------------------------

        //select annexure
        $defaultModel = $this -> model('AnnexureMasterModel');

        $this -> data['db_annexures'] = $defaultModel -> getAllAnnexures([
            'where' => 'is_active = 1 AND deleted_at IS NULL', 'params' => [ ]
        ]);

        $this -> data['db_annexures'] = generate_data_assoc_array($this -> data['db_annexures'], 'id');

        // -------------------------------------------------

        //select subset
        $defaultModel = $this -> model('QuestionSetModel');

        $this -> data['db_sub_sets'] = $defaultModel -> getAllQuestionSet([
            'where' => 'set_type_id = 2 AND is_active = 1 AND deleted_at IS NULL', 'params' => [ ]
        ]);

        $this -> data['db_sub_sets'] = generate_data_assoc_array($this -> data['db_sub_sets'], 'id');

        // -------------------------------------------------

        //convert to select
        $GLOBALS['applicableToArray'] = generate_array_for_select($GLOBALS['applicableToArray'], 'id', 'title', 'array');
        $GLOBALS['questionInputMethodArray'] = generate_array_for_select($GLOBALS['questionInputMethodArray'], 'id', 'title', 'array');

        //top data container
        $this -> data['data_container'] = true;
        $this -> me -> menuKey = 'questionSetMaster';
    }

    private function checkSet($setId = null)
    {
        // ------------------- question set logic ------------------

        if(!empty($setId))
            $this -> setId = $setId;

        $this -> data['db_set'] = null;

        //get single set details
        if(!empty($this -> setId))
            $this -> data['db_set'] = $this -> questionSetModel -> getSingleQuestionSet([
                'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1 AND id != 1',
                'params' => [ 'id' => $this -> setId ]
            ]);

        if( !is_object($this -> data['db_set']) ) {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }
    }

    private function setBackBtn()
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('questionMaster') . '?set=' . encrypt_ex_data($this -> setId) ],
        ];
    }

    private function getAllSetHeaders()
    {
        //select header in current set
        $defaultModel = $this -> model('QuestionHeaderModel');

        $this -> data['db_headers'] = $defaultModel -> getAllQuestionHeader([
            'where' => 'question_set_id = :question_set_id AND deleted_at IS NULL AND is_active = 1 ORDER BY NAME',
            'params' => [ 'question_set_id' => $this -> setId ]
        ]);

        //convert to select 
        $this -> data['db_headers'] = generate_array_for_select($this -> data['db_headers'], 'id', 'name');
    }

    private function searchAllKeyAspect()
    {
        //select subset
        $defaultModel = $this -> model('RiskControlKeyAspectModel');

        if( empty($this -> request -> input('control_risk_id')) )
            $this -> data['db_key_aspect'] = null;
        else
        {
            $this -> data['db_key_aspect'] = $defaultModel -> getAllRiskControlKeyAspect([
                'where' => 'risk_control_id = :risk_control_id AND is_active = 1 AND deleted_at IS NULL', 'params' => [ 
                    'risk_control_id' => $this -> request -> input('control_risk_id')
                ]
            ]);

            $this -> data['db_key_aspect'] = generate_data_assoc_array($this -> data['db_key_aspect'], 'id');
        }
    }

    private function validateData($methodType = 'add', $questionId = '')
    {
        $uniqueQuestionWhere = [
            'model' => $this -> questionMasterModel,
            'where' => 'question = :question AND set_id = :set_id AND header_id = :header_id AND deleted_at IS NULL',
            'params' => [ 
                'question'  => $this -> request -> input('question'),
                'set_id'    => $this -> setId,
                'header_id' => $this -> request -> input('header_id') 
            ]
        ];

        if(!empty($questionId))
        {
            $uniqueQuestionWhere['where'] .= ' AND id != :id';
            $uniqueQuestionWhere['params']['id'] = $questionId;
        }

        $validationArray = [ 'question' => 'required|is_unique[question_unique_data, questionDupliacte]' ];
        $refDataArray = [ 'question_unique_data' => $uniqueQuestionWhere ];

        if($this -> data['disable_action']):

            $validationArray = [
                'header_id' => 'required|array_key[header_array, headerSelect]',
                'question' => 'required|is_unique[question_unique_data, questionDupliacte]',
                'show_instances' => 'required|regex[numberRegex, errorNumber]',
                'risk_category_id' => 'required|array_key[risk_category_array, riskCategoryError]',
                'control_risk_id' => 'required|array_key[control_risk_array, riskControlError]',
                'key_aspect_id' => 'required|array_key[key_aspect_array, riskKeyAspectError]',
                'residual_risk_id' => 'required|array_key[residual_risk_array, residualRiskError]',
                'applicable_id' => 'required|array_key[applicable_array, applicableError]',
                'question_type_id' => 'required|array_key[question_type_array, questionTypeError]',
                'option_id' => 'required|array_key[option_id_array, optionError]',
                'area_of_audit_id' => 'required|array_key[area_of_audit_array, broaderAreaSelect]',
            ];

            $refDataArray = [
                'header_array' => $this -> data['db_headers'],
                'risk_category_array' => $this -> data['db_risk_category'],
                'control_risk_array' => $this -> data['db_risk_control'],
                'key_aspect_array' => $this -> data['db_key_aspect'],
                'residual_risk_array' => RISK_PARAMETERS_ARRAY,
                'applicable_array' => $GLOBALS['applicableToArray'],
                'question_type_array' => $GLOBALS['questionTypeArray'],
                'option_id_array' => $GLOBALS['questionInputMethodArray'],
                'area_of_audit_array' => $this -> data['db_area_of_audit'],            
                'question_unique_data' => $uniqueQuestionWhere
            ];

            //annexure
            if($this -> request -> input('option_id') == 4)
            {
                $validationArray['annexure_id'] = 'required|array_key[annexure_data_array, broaderAreaSelect]';
                $refDataArray['annexure_data_array'] = $this -> data['db_annexures']; 
            }

            //subset
            elseif($this -> request -> input('option_id') == 5)
                $validationArray['subset_multi_id'] = 'required';

        endif;

        //method call
        Validation::validateData($this -> request, $validationArray, $refDataArray);

        //validation check
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
        if(!$this -> data['disable_action']):

            $dataArray = array( 
                'question' => ($this -> request -> input('question')),
                'admin_id' => Session::get('emp_id')
            );

            return $dataArray;

        endif;

        $dataArray = array(
            'header_id' => $this -> request -> input('header_id'),
            'set_id' => $this -> setId,
            'question' => ($this -> request -> input('question')),
            'risk_category_id' => $this -> request -> input('risk_category_id'),
            'option_id' => $this -> request -> input('option_id'),
            'question_type_id' => $this -> request -> input('question_type_id'),
            'annexure_id' => 0,
            'subset_multi_id' => 0,
            'area_of_audit_id' => $this -> request -> input('area_of_audit_id'),
            'applicable_id' => $this -> request -> input('applicable_id'),
            'control_risk_id' => $this -> request -> input('control_risk_id'),
            'key_aspect_id' => $this -> request -> input('key_aspect_id'),
            'residual_risk_id' => $this -> request -> input('residual_risk_id'),
            'show_instances' => $this -> request -> input('show_instances'),
            'audit_ev_upload' => ($this -> request -> input('audit_ev_upload') ?? 0),
            'compliance_ev_upload' => ($this -> request -> input('compliance_ev_upload') ?? 0),
            'admin_id' => Session::get('emp_id')
        );

        //annexure
        if($this -> request -> input('option_id') == 4)
            $dataArray['annexure_id'] = $this -> request -> input('annexure_id');

        //subset
        elseif( $this -> request -> input('option_id') == 5 && 
                is_array($this -> request -> input('subset_multi_id')) && 
                sizeof($this -> request -> input('subset_multi_id')) > 0)
                $dataArray['subset_multi_id'] = implode(',', $this -> request -> input('subset_multi_id'));

        return $dataArray;
    }

    public function index() {

        $this -> checkSet(); //method call
        // $this -> setBackBtn(); //method call

        $this -> data['db_data'] = $this -> questionMasterModel -> getAllQuestions([ 
            'where' => 'set_id = :set_id AND deleted_at IS NULL',
            'params' => [ 'set_id' => $this -> setId ]
        ]);

        if( is_array($this -> data['db_data']) && sizeof($this -> data['db_data']) > 0 )
        {
            //sort data by header
            $tempDBData = $this -> data['db_data'];
            $dbData = [];
            $this -> getAllSetHeaders(); //method call

            foreach($tempDBData as $cQuestionData)
            {
                //push header
                if(!array_key_exists($cQuestionData -> header_id, $dbData))
                {
                    $dbData[ $cQuestionData -> header_id ] = array(
                        'name' => ERROR_VARS['notFound'],
                        'questions' => []
                    );

                    //push header name
                    if(is_array($this -> data['db_headers']) && array_key_exists($cQuestionData -> header_id, $this -> data['db_headers']))
                        $dbData[ $cQuestionData -> header_id ]['name'] = string_operations($this -> data['db_headers'][ $cQuestionData -> header_id ], 'upper');
                }

                //push broader area
                $cQuestionData -> area_of_audit_name = ERROR_VARS['notFound'];

                if( is_array($this -> data['db_area_of_audit']) && 
                    array_key_exists($cQuestionData -> area_of_audit_id, $this -> data['db_area_of_audit']) )
                    $cQuestionData -> area_of_audit_name = $this -> data['db_area_of_audit'][ $cQuestionData -> area_of_audit_id ] -> name;

                //risk category
                $cQuestionData -> risk_category_name = ERROR_VARS['notFound'];

                if( is_array($this -> data['db_risk_category']) && 
                array_key_exists($cQuestionData -> risk_category_id, $this -> data['db_risk_category']) )
                    $cQuestionData -> risk_category_name = $this -> data['db_risk_category'][ $cQuestionData -> risk_category_id ];

                //question type
                $cQuestionData -> option_id_name = ERROR_VARS['notFound'];

                if( is_array($GLOBALS['questionInputMethodArray']) && 
                    array_key_exists($cQuestionData -> option_id, $GLOBALS['questionInputMethodArray']))
                    $cQuestionData -> option_id_name = $GLOBALS['questionInputMethodArray'][ $cQuestionData -> option_id ];

                //push question
                $dbData[ $cQuestionData -> header_id ]['questions'][ $cQuestionData -> id ] = $cQuestionData;
                
            }

            //set details
            $dbSet = $this -> data['db_set'];

            //unset all data
            $this -> data = [];
            
            $this -> data['db_data'] = $dbData;
            $this -> data['db_set'] = $dbSet;
            $this -> data['data_container'] = true;
            $this -> data['remove_container'] = true;

            // method call
            $this -> data['disable_action'] = $this -> actionDisableAction();

            // check for admin lite // method call 29.11.2024
            if($this -> data['disable_action'])
                $this -> data['disable_action'] = $this -> checkAdminLite(0);

            //unset all data
            unset($dbData, $dbSet, $tempDBData);
        }

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('questionSetMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('questionMaster') . '/add?set=' . encrypt_ex_data($this -> setId) ],
        ];

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function add()
    {
        $this -> checkSet(); //method call
        $this -> searchAllKeyAspect(); //method call
        $this -> getAllSetHeaders(); //method call
        $this -> setBackBtn(); //method call

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?set=' . encrypt_ex_data($this -> setId) );
        $this -> me -> pageHeading = 'Add Question';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> questionMasterModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

        $this -> data['disable_action'] = 1; // enable all options

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form', [ 
                'request' => $this -> request,
                'data' => $this -> data
            ]);
        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //for get data
            $this -> searchAllKeyAspect(); //method call

            //validation check
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
                $result = $this -> questionMasterModel::insert(
                    $this -> questionMasterModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to set dashboard
                Validation::flashErrorMsg('questionAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('questionMaster') . '/?set=' . encrypt_ex_data($this -> setId) );
            }

        });

    }

    public function update($getRequest) {

        $this -> quesId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> quesId));
        $this -> me -> pageHeading = 'Update Question';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> quesId]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        //after data found
        $this -> checkSet($this -> data['db_data'] -> set_id); //method call
        
        $this -> getAllSetHeaders(); //method call

        //set input
        if(!$this -> request -> has('control_risk_id'))
            $this -> request -> setInputCustom('control_risk_id', $this -> data['db_data'] -> control_risk_id);

        $this -> searchAllKeyAspect(); //method call

        $this -> setBackBtn(); //method call
        $this -> data['btn_type'] = 'update';

        // method call
        $this -> data['disable_action'] = $this -> actionDisableAction();

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('update', $this -> quesId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> questionMasterModel::update(
                    $this -> questionMasterModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> quesId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to set dashboard
                Validation::flashErrorMsg('questionUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('questionMaster') . '/?set=' . encrypt_ex_data($this -> setId) );
            }
        });
    }

    public function delete($getRequest) {

        // method call for admin lite 29.11.2024
        $this -> checkAdminLite();

        // method call
        $disable_action = $this -> actionDisableAction();

        if(!$disable_action)
            return Except::exc_404( Notifications::getNoti('auditStartDisableAction') );

        $this -> quesId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> quesId ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        //after data found
        $this -> checkSet($this -> data['db_data'] -> set_id); //method call

        $result = $this -> questionMasterModel::delete($this -> questionMasterModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> quesId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to set dashboard
        Validation::flashErrorMsg('questionDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('questionMaster') . '/?set=' . encrypt_ex_data($this -> setId) );
    }

    public function status($getRequest) {

        // method call for admin lite 29.11.2024
        $this -> checkAdminLite();

        // method call
        $disable_action = $this -> actionDisableAction();

        if(!$disable_action)
            return Except::exc_404( Notifications::getNoti('auditStartDisableAction') );

        $this -> quesId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> quesId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        //after data found
        $this -> checkSet($this -> data['db_data'] -> set_id); //method call        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> questionMasterModel::update(
            $this -> questionMasterModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> quesId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to set dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('questionMaster') . '/?set=' . encrypt_ex_data($this -> setId) );
    }

    public function riskMapping($getRequest) {

        $this -> quesId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');
        $this -> rmvId = decrypt_ex_data(isset($getRequest['rmv']) ? $getRequest['rmv'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> quesId]) ;

        // method call
        $this -> data['disable_action'] = $this -> actionDisableAction();

        //return if data not found
        /*if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];*/

        //after data found
        $this -> checkSet($this -> data['db_data'] -> set_id); //method call        

        if(!in_array($this -> data['db_data'] -> option_id, [1,2,4,5]))
            return Except::exc_404( Notifications::getNoti('questionRiskMappingError') );

        // yes no data type array
        $this -> data['yes_no_data'] = array( 'YES' => 'YES', 'NO' => 'NO' );
        $this -> setBackBtn(); //method call

        //question type
        $this -> data['db_data'] -> option_id_name = ERROR_VARS['notFound'];

        if( is_array($GLOBALS['questionInputMethodArray']) && 
            array_key_exists($this -> data['db_data'] -> option_id, $GLOBALS['questionInputMethodArray']))
            $this -> data['db_data'] -> option_id_name = $GLOBALS['questionInputMethodArray'][ $this -> data['db_data'] -> option_id ];

        $this -> data['db_data'] -> parametersArr = null;  

        // parameters decode
        try {
            
            if(!empty($this -> data['db_data'] -> parameters))
                $this -> data['db_data'] -> parametersArr = json_decode($this -> data['db_data'] -> parameters);

        } catch (Exception $e) { }

        if($this -> rmvId != '')
        {
            // remove option // method call
            $this -> updateQuestionParameter('remove');
        }

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'risk-mapping-form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            // method call
            $this -> updateQuestionParameter();

            return return2View($this, $this -> me -> viewDir . 'risk-mapping-form', [ 'request' => $this -> request ]);

        });

    }

    private function updateQuestionParameter($noti = 'update')
    {
        // for yes not type = 2

        // method call
        $disable_action = $this -> actionDisableAction();

        if(!$disable_action)
        {
            Except::exc_404( Notifications::getNoti('auditStartDisableAction') );
            exit;
        }


        if($noti == 'update')
        {
            if( $this -> data['db_data'] -> option_id == 2 && 
            is_array($this -> data['db_data'] -> parametersArr) && 
            sizeof($this -> data['db_data'] -> parametersArr) == 2 )
            {
                // flash error
                Validation::flashErrorMsg('questionYesNoOptionError', 'warning');
            }
            else
            {
                Validation::validateData($this -> request, [
                    'risk_type' => 'required',
                    'business_risk' => 'required|array_key[risk_param_array, user_type]',
                    'control_risk' => 'required|array_key[risk_param_array, user_type]',
                ],[
                    'gender_array' => $GLOBALS['userGenderArray'],
                    'risk_param_array' => RISK_PARAMETERS_ARRAY,
                ]);
            }
        }

        $optionParam = [];

        if(!$this -> request -> input( 'error' ) > 0 && is_array($this -> data['db_data'] -> parametersArr))
        {
            // check answer exists in array
            foreach($this -> data['db_data'] -> parametersArr as $cParamObj)
            {
                // push data
                $optionParam[] = $cParamObj;

                if( $noti == 'update' && string_operations($cParamObj -> rt) == string_operations($this -> request -> input('risk_type')) )
                {
                    // has error duplicate
                    $this -> request -> setInputCustom( 'risk_type_err', Notifications::getNoti('duplicateQuesRiskTypeError'));
                    $this -> request -> setInputCustom( 'error', 1);
                    break;
                }
            }

            // check limit
            if( $noti == 'update' && sizeof($optionParam) == ENV_CONFIG['question_parameter_limit'])
            {
                $this -> request -> setInputCustom( 'risk_type_err', Notifications::getNoti('quesOptionLimitError'));
                $this -> request -> setInputCustom( 'error', 1);
            }  
        }

        //validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            // return false;
        } 
        else 
        {
            // 1 option needed
            if( $noti == 'remove' && sizeof($optionParam) == 1)
            {
                Validation::flashErrorMsg('oneQuesOptionNeededError', 'warning');
                $this -> request -> setInputCustom( 'error', 1);
            }

            // check array key
            elseif( $noti == 'remove' && $this -> rmvId != '' && !array_key_exists($this -> rmvId, $optionParam) )
            {
                Validation::flashErrorMsg('quesOptionNotExistsError', 'warning');
                $this -> request -> setInputCustom( 'error', 1);
            }

            if( $noti == 'update' )
            {
                // insert data //push new risk
                $optionParam[] = [
                    'rt' => string_operations($this -> request -> input('risk_type'), 'upper'),
                    'br' => $this -> request -> input('business_risk'),
                    'cr' => $this -> request -> input('control_risk'),
                ];
            }
            elseif( $noti == 'remove' && $this -> rmvId != '' && sizeof($optionParam) > 1)
                unset($optionParam[ $this -> rmvId ]);

            if(!sizeof($optionParam) > 0)
            {
                Except::exc_404( Notifications::getNoti('somethingWrong') );
                exit;
            }

            if(!$this -> request -> input( 'error' ) > 0)
            {
                $optionParam = array_values($optionParam);
                $optionParam = json_encode($optionParam);

                $result = $this -> questionMasterModel::update(
                    $this -> questionMasterModel -> getTableName(),
                    [ 'parameters' => $optionParam ], 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> data['db_data'] -> id ]
                    ]
                );

                if(!$result)
                {
                    Except::exc_404( Notifications::getNoti('errorSaving') );
                    exit;
                }

                //after insert data redirect to set dashboard
                Validation::flashErrorMsg(( $noti == 'update' ? 'quesAnsAddedSuccess' : 'quesAnsRemovedSuccess'), 'success');
                Redirect::to( SiteUrls::setUrl( $this -> me -> url ) . '/risk-mapping/' . encrypt_ex_data($this -> data['db_data'] -> id) );
            }
        }
    }

    private function actionDisableAction()
    {
        // function call
        $disable_action = check_admin_action($this, ['lite_access' => 0]);

        return is_object($disable_action) ? 0 : 1;
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> quesId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> questionMasterModel -> getSingleQuestion($filter);

        if(empty($this -> quesId) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        return $this -> data['db_data'];
    }

    // new method add 29.11.2024
    private function checkAdminLite($except = true) {

        $res = true;

        if(!Session::has('emp_type') || !in_array(Session::get('emp_type'), [1])) {

            if($except)
            {
                Except::exc_access_restrict( );
                exit;
            }

            $res = false;
        }   

        return $res;
    }
}

?>