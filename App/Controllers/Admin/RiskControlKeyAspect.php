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

class RiskControlKeyAspect extends Controller  {

    public $me = null, $controlId, $data, $request, $keyId, $riskCategory;
    public $riskControlModel, $riskControlKeyAspectModel;

    public function __construct($me) {
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        // menuKey for active menu
        $this -> me -> menuKey = 'riskControlMaster';

        $this -> controlId = decrypt_ex_data($this -> request -> input('control'));
        
        if(!empty($this -> controlId))
        {
            // top btn array
            $this -> data['topBtnArr'] = [
                'default' => [ 'href' => SiteUrls::getUrl('riskControlKeyAspect') . '?control=' . encrypt_ex_data($this -> controlId) ],
            ];
        }

        // find current question header model
        $this -> riskControlKeyAspectModel = $this -> model('RiskControlKeyAspectModel');

        // top data container 
        $this -> data['data_container'] = true;
        
    }

    private function checkCI(){
        // require risk category model
        $this -> riskControlModel = $this -> model('RiskControlModel');    

        $this -> data['db_control'] = null;

        //get single control risk type details
        if(!empty($this -> controlId))
            $this -> data['db_control'] = $this -> riskControlModel -> getSingleRiskControl([
                'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
                'params' => [ 'id' => $this -> controlId ]
            ]);

        if( !is_object($this -> data['db_control']) ) {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        //unset var
        unset($this -> riskControlModel);
    }

    private function validateData($methodType = 'add', $keyId = '', $controlId = '')
    {
        //calling checkCI function
        $this -> checkCI();

        $uniqueWhere = [
            'model' => $this -> riskControlKeyAspectModel,
            'where' => 'name = :name AND risk_control_id = :risk_control_id AND deleted_at IS NULL',
            'params' => 
            [ 'name' => $this -> request -> input('name'),
              'risk_control_id' => $this -> controlId,
            ]
        ];

        if(!empty($keyId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $keyId;
        }

        $validationArray = [
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, riskcontrolaspectkeyDuplicate]',
        ];

        Validation::validateData($this -> request, $validationArray,
        [
            'unique_data' => $uniqueWhere,
        ]);


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
        $dataArray = array(
            'risk_control_id' => $this -> controlId,
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index($getRequest) 
    {
        //calling checkCI function
        $this -> checkCI();

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskControlMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('riskControlKeyAspect') . '/add?control=' . encrypt_ex_data($this -> controlId) ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> riskControlKeyAspectModel, 
            $this -> riskControlKeyAspectModel -> getTableName(), [
                'where' => 'deleted_at IS NULL AND risk_control_id =:risk_control_id',
                'params' => [ 'risk_control_id' => $this -> controlId ]]);

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view 
        //helper function call
        return return2View($this, $this -> me -> viewDir . 'index', ['request' => $this -> request]);
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> riskControlKeyAspectModel, ["name"], [
            'where' => 'risk_control_id = :risk_control_id AND deleted_at IS NULL',
            'params' => ['risk_control_id' => $this -> controlId]
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cRiskContorlKeyId => $cRiskControlKeyDetails)
            {
                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "name" => $cRiskControlKeyDetails -> name,
                    "status" => check_active_status($cRiskControlKeyDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    if($cRiskControlKeyDetails -> is_active == 1) {
                        
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cRiskControlKeyDetails -> id) . '?control=' . encrypt_ex_data($this -> controlId), 'extra' => view_tooltip('Update') ]);

                        // $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cRiskControlKeyDetails -> id) .'?control='. encrypt_ex_data($this -> controlId), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskControlKeyDetails -> id) . '?control=' . encrypt_ex_data($this -> controlId), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                    }
                    else 
                    {
                        $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskControlKeyDetails -> id) . '?control=' . encrypt_ex_data($this -> controlId), 'extra' => view_tooltip('Activate') ]);
                    }
                }                
                else
                    $cDataArray["action"] .= '';

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function add($getRequest)
    {
        //calling checkCI function
        $this -> checkCI();

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?control=' . encrypt_ex_data($this -> controlId));
        $this -> me -> pageHeading = 'Add Key Aspect';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> riskControlKeyAspectModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

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

                //print_r($this -> postArray());
                // insert in database
                $result = $this -> riskControlKeyAspectModel::insert(
                    $this -> riskControlKeyAspectModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to key aspect dashboard
                Validation::flashErrorMsg('riskkeyaspectAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskControlKeyAspect') . '/?control=' . encrypt_ex_data($this -> controlId) );

            }

        });

    }

    public function update($getRequest) 
    {
        //calling checkCI function
        $this -> checkCI();

        $this -> keyId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');
         
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> keyId) . '?control=' . encrypt_ex_data($this -> controlId));
        $this -> me -> pageHeading = 'Update Risk Weight';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> keyId]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('update', $this -> keyId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> riskControlKeyAspectModel::update(
                    $this -> riskControlKeyAspectModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> keyId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect tokey aspect dashboard
                Validation::flashErrorMsg('riskkeyaspectUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskControlKeyAspect')  . '/?control=' . encrypt_ex_data($this -> controlId) );
            }
        });
    }

    // Commented as per advice of Omkar Sir
    // public function delete($getRequest) 
    // {
    //     //calling checkCI function
    //     $this -> checkCI();

    //     $this -> keyId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> keyId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $result = $this -> riskControlKeyAspectModel::delete($this -> riskControlKeyAspectModel -> getTableName(),[
    //         'where' => 'id = :id',
    //         'params' => [ 'id' => $this -> keyId ]
    //     ]);

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorDeleting') );

    //     //after insert data redirect to key aspect dashboard
    //     Validation::flashErrorMsg('riskkeyaspectDeletedSuccess', 'success');
    //     Redirect::to( SiteUrls::getUrl('riskControlKeyAspect') . '/?control=' . encrypt_ex_data($this -> controlId));
    // }

    public function status($getRequest) 
    {
        //calling checkCI function
        $this -> checkCI();

        $this -> keyId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> keyId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> riskControlKeyAspectModel::update(
            $this -> riskControlKeyAspectModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> keyId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to risk category dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('riskControlKeyAspect') .'/?control=' . encrypt_ex_data($this -> controlId) );
    }

    public function ajaxKeyAspectRatio($getRequest)
    {
        $crId = (isset($getRequest['cr_id']) ? $getRequest['cr_id'] : '');

        if(empty($crId))
            return null;

        //find data
        $returnData = $this -> riskControlKeyAspectModel -> getAllRiskControlKeyAspect([
            'where' => 'risk_control_id = :risk_control_id AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ 'risk_control_id' => $crId ]
        ]);

        echo json_encode($returnData);
    }

    private function getDataOr404($filter, $optional = null) 
    {  
        //calling checkCI function
        $this -> checkCI();

        $filter = [ 
            'where' => 'id = :id AND risk_control_id = :risk_control_id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> keyId, 'risk_control_id' => $this -> controlId]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND risk_control_id = :risk_control_id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> riskControlKeyAspectModel -> getSingleRiskControlKeyAspect($filter);

        if(empty($this -> keyId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>