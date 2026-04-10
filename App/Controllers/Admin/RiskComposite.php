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


class RiskComposite extends Controller  {

    public $me = null, $data, $request, $criskId; 
    public $riskCompositeModel;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskComposite') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current question set model
        $this -> riskCompositeModel = $this -> model('RiskCompositeModel');
        
    }

    private function validateData($methodType = 'add', $criskId = '')
    {
        $uniqueWhere = [
            'model' => $this -> riskCompositeModel,
            'where' => 'business_risk = :business_risk AND control_risk = :control_risk AND deleted_at IS NULL',
            'params' => 
            [ 
              'business_risk' => $this -> request -> input('business_risk'),
              'control_risk' => $this -> request -> input('control_risk'),
            ]
        ];

        $uniqueName = [
            'model' => $this -> riskCompositeModel,
            'where' => 'name = :name AND deleted_at IS NULL',
            'params' => 
            [ 
              'name' => $this -> request -> input('name'),
            ]
        ];

        if(!empty($criskId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $criskId;
            
            $uniqueName['where'] .= ' AND id != :id';
            $uniqueName['params']['id'] = $criskId;
        }

        $validationArray = [
            'business_risk' => 'required|array_key[business_risk_array, riskSelect]',
            'control_risk' => 'required|array_key[business_risk_array, riskSelect]|is_unique[unique_data, compositeControlRiskDuplicate]',
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_name, compositeNameDuplicate]',
        ];

        Validation::validateData($this -> request, $validationArray,
        [
            'business_risk_array' => RISK_PARAMETERS_ARRAY,
            'unique_data' => $uniqueWhere,
            'unique_name' => $uniqueName,
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
            'business_risk' => $this -> request -> input('business_risk'),
            'control_risk' => $this -> request -> input('control_risk'),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('riskComposite') . '/add' ],
        ];

        // $this -> data['db_data'] = $this -> riskCompositeModel -> getAllRiskComposite([ 'where' => 'deleted_at IS NULL' ]);

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> riskCompositeModel, 
            $this -> riskCompositeModel -> getTableName(), [
                'where' => 'deleted_at IS NULL',
                'params' => []
            ]
        );

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> riskCompositeModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cCompositeId => $cCompositeDetails)
            {
                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "business_risk" => RISK_PARAMETERS_ARRAY[$cCompositeDetails -> business_risk]['title'],
                    "control_risk" => RISK_PARAMETERS_ARRAY[$cCompositeDetails -> control_risk]['title'],
                    "name" => $cCompositeDetails -> name,
                    "action" => ""
                ];
                
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cCompositeDetails -> id), 'extra' => view_tooltip('Update') ]);

                    $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cCompositeDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
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

    public function add()
    {
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add');
        $this -> me -> pageHeading = 'Add Composite Risk';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> riskCompositeModel -> emptyInstance();
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
                // insert in database
                $result = $this -> riskCompositeModel::insert(
                    $this -> riskCompositeModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk composite dashboard
                Validation::flashErrorMsg('riskcompositeAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskComposite') );

            }

        });

    }

    public function update($getRequest) {

        $this -> criskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> criskId));
        $this -> me -> pageHeading = 'Update Composite Risk';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> criskId]) ;

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
            if(!$this -> validateData('update', $this -> criskId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> riskCompositeModel::update(
                    $this -> riskCompositeModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> criskId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to composite risk dashboard
                Validation::flashErrorMsg('riskcompositeUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskComposite') );
            }
        });
    }

    public function delete($getRequest) {

        $this -> criskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> criskId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> riskCompositeModel::delete($this -> riskCompositeModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> criskId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to composite risk dashboard
        Validation::flashErrorMsg('riskcompositeDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('riskComposite') );
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> criskId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> riskCompositeModel -> getSingleRiskComposite($filter);

        if(empty($this -> criskId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>