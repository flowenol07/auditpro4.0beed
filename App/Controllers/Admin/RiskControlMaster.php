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


class RiskControlMaster extends Controller  {

    public $me = null, $data, $request, $controlId, $riskCategoryWeight, $year;
    public $riskCategoryWeightModel, $riskControlModel, $yearModel;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskControlMaster') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current risk control model
        $this -> riskControlModel = $this -> model('RiskControlModel');

        // $this -> riskCategoryWeightModel = $this -> model('RiskCategoryWeightModel');        
    }

    private function validateData($methodType = 'add', $controlId = '')
    {

        $uniqueWhere = [
            'model' => $this -> riskControlModel,
            'where' => 'name = :name AND deleted_at IS NULL',
            'params' => 
            [ 
                'name' => $this -> request -> input('name'),
            ]
        ];

        if(!empty($controlId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $controlId;
        }

        $validationArray = [
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, riskcontrolDuplicate]',
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
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('riskControlMaster') . '/add' ],
        ];

        // $this -> data['db_data'] = $this -> riskControlModel -> getAllRiskControl([ 'where' => 'deleted_at IS NULL' ]);

        // //coverted to array
        // $this -> data['db_data'] = generate_data_assoc_array($this -> data['db_data'], 'id');

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> riskControlModel, 
            $this -> riskControlModel -> getTableName(), [
                'where' => 'deleted_at IS NULL',
                'params' => []
            ]
        );

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> riskControlModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cRiskControlId => $cRiskControlDetails)
            {
                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "name" => $cRiskControlDetails -> name,
                    "status" => check_active_status($cRiskControlDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
                
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    if($cRiskControlDetails -> is_active == 1) {
                        
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cRiskControlDetails -> id), 'extra' => view_tooltip('Update') ]);

                        // $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cRiskControlDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                        $cDataArray["action"] .= generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskControlDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);

                        $cDataArray["action"] .= generate_link_button('link', ['href' => SiteUrls::getUrl('riskControlKeyAspect') . '?control=' . encrypt_ex_data($cRiskControlDetails -> id), 'extra' => view_tooltip('Add/View Key Aspects')]);
                    }
                    else {

                        $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskControlDetails -> id), 'extra' => view_tooltip('Activate') ]);
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

    public function add()
    {
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add');
        $this -> me -> pageHeading = 'Add Control Risk Type';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> riskControlModel -> emptyInstance();
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
                $result = $this -> riskControlModel::insert(
                    $this -> riskControlModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk control dashboard
                Validation::flashErrorMsg('riskcontrolAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskControlMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> controlId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> controlId));
        $this -> me -> pageHeading = 'Update Control Risk Type';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> controlId]) ;

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
            if(!$this -> validateData('update', $this -> controlId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> riskControlModel::update(
                    $this -> riskControlModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> controlId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk control dashboard
                Validation::flashErrorMsg('riskcontrolUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskControlMaster') );
            }
        });
    }

    // Commented as per advice of Omkar Sir
    // public function delete($getRequest) {

    //     $this -> controlId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> controlId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $result = $this -> riskControlModel::delete($this -> riskControlModel -> getTableName(),[
    //         'where' => 'id = :id',
    //         'params' => [ 'id' => $this -> controlId ]
    //     ]);

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorDeleting') );

    //     //after insert data redirect to risk control dashboard
    //     Validation::flashErrorMsg('riskcontrolDeletedSuccess', 'success');
    //     Redirect::to( SiteUrls::getUrl('riskControlMaster') );
    // }

    public function status($getRequest) {

        $this -> controlId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> controlId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> riskControlModel::update(
            $this -> riskControlModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> controlId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to risk control dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('riskControlMaster') );
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> controlId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> riskControlModel -> getSingleRiskControl($filter);

        if(empty($this -> controlId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>