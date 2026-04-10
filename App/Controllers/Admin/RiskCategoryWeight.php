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

class RiskCategoryWeight extends Controller  {

    public $me = null, $rcId, $data, $request, $weightId, $year;
    public $yearModel, $riskCategoryWeightModel;

    public $riskCategoryModel, $riskCategory;

    public function __construct($me) {
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        // menuKey for active menu
        $this -> me -> menuKey = 'riskCategoryMaster';

        $this -> rcId = decrypt_ex_data($this -> request -> input('rc'));

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskCategoryWeight') . '?rc=' . encrypt_ex_data($this -> rcId) ],
        ];

        // find current question header model
        $this -> riskCategoryWeightModel = $this -> model('RiskCategoryWeightModel');

        // top data container 
        $this -> data['data_container'] = true;

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, ['where' => 'deleted_at IS NULL']);

        $this -> data['fin_year'] = generate_array_for_select($this -> year, 'id', 'year');
        
    }

    private function checkRC()
    {
        // require risk category model
        $this -> riskCategoryModel = $this -> model('RiskCategoryModel');    

        $this -> data['db_rc'] = null;

        //get single set details
        if(!empty($this -> rcId))
            $this -> data['db_rc'] = $this -> riskCategoryModel -> getSingleRiskCategory([
                'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
                'params' => [ 'id' => $this -> rcId ]
            ]);

        if(!is_object($this -> data['db_rc']) ) {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        //unset var
        unset($this -> riskCategoryModel);
    }

    private function checkExistingYear($methodType ='index', $riskCategoryWeightId = NULL)
    {
        //calling checkRC function
        $this -> checkRC();

        // getting year for showing options in form

        $filter = [
            'where' => 'risk_category_id = :risk_category_id AND deleted_at IS NULL',
            'params' => [ 'risk_category_id' => $this -> rcId ]
        ];

        if(!empty($riskCategoryWeightId))
        {
            $filter['where'] .= ' AND id != :id';
            $filter['params']['id'] = $riskCategoryWeightId;
        }

        $db_option_year = $this -> riskCategoryWeightModel -> getSingleRiskCategoryWeight( $filter, 'sql', 'SELECT GROUP_CONCAT(year_id) AS existing_year FROM ' . $this -> riskCategoryWeightModel -> getTableName()
        );

        $filter = ['where' => 'deleted_at IS NULL', 'params' => []];

        if(is_object($db_option_year) && !empty($db_option_year -> existing_year)  && ($methodType == 'add' || $methodType == 'update'))
        {
            $filter['where'] .= ' AND id NOT IN (' . $db_option_year -> existing_year . ')';
            // $filter['params'] = ['existing_year' => $db_option_year -> existing_year];
        }

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, $filter);

        $this -> data['db_year'] = generate_array_for_select($this -> year, 'id', 'year');
    }

    private function validateData($methodType = 'add', $weightId = '', $rcId = '')
    {
        //calling checkRC function
        $this -> checkRC();

        $uniqueWhere = [
            'model' => $this -> riskCategoryWeightModel,
            'where' => 'year_id = :year_id AND risk_category_id = :risk_category_id AND deleted_at IS NULL',
            'params' => 
            [ 'year_id' => $this -> request -> input('year_id'),
              'risk_category_id' => decrypt_ex_data($this -> request -> input('rc')),
            ]
        ];

        if(!empty($weightId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $weightId;
        }

        $validationArray = [
            'year_id' => 'required|is_unique[unique_data, yearDuplicate]',
            'risk_weight' => 'required|regex[numberRegex, errorNumber]',            
            'risk_appetite_percent' => 'regex[floatNumberRegex, name]',
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
        //calling checkRC function
        $this -> checkRC();

        $dataArray = array(
            'year_id' => $this -> request -> input('year_id'),
            'risk_category_id' => decrypt_ex_data($this -> request -> input('rc')),
            'risk_weight' => $this -> request -> input('risk_weight'),
            'risk_appetite_percent' => get_decimal($this -> request -> input('risk_appetite_percent'), 2),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index($getRequest) 
    {
        // calling checkRC function
        $this -> checkRC();

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskCategoryMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('riskCategoryWeight') . '/add?rc=' . encrypt_ex_data($this -> rcId) ],
        ];

        //method call for year option
        $this -> checkExistingYear();

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> riskCategoryWeightModel, 
            $this -> riskCategoryWeightModel -> getTableName(), [
                'where' => 'deleted_at IS NULL AND risk_category_id = :risk_category_id',
                'params' => ['risk_category_id' => $this -> rcId ]
            ]
        );

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
        $funcData = generate_datatable_data($this, $this -> riskCategoryWeightModel, 
                    ["year_id", "risk_weight", "risk_appetite_percent"], 
                    [
                        'where' => 'deleted_at IS NULL AND risk_category_id = :risk_category_id',
                        'params' => ['risk_category_id' => $this -> rcId ]
                    ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cRiskCategoryWeightId => $cRiskCategoryWeightDetails)
            {
                if(array_key_exists($cRiskCategoryWeightDetails -> year_id, $this -> data['fin_year']))
                {    $cDataArray = [
                        "sr_no" =>  $srNo,
                        "year_id" => $this -> data['fin_year'][$cRiskCategoryWeightDetails -> year_id],
                        "risk_weight" => $cRiskCategoryWeightDetails -> risk_weight,
                        "risk_appetite_percent" => $cRiskCategoryWeightDetails -> risk_appetite_percent,
                        "status" => check_active_status($cRiskCategoryWeightDetails -> is_active, 1, 1, 1),
                        "action" => ""
                    ];
                    
                    $srNo++;

                    // For Enable of Action on Assement Start
                    if($CHECK_ADMIN_ACTION)
                    { 
                        if($cRiskCategoryWeightDetails -> is_active == 1)
                        {
                            $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cRiskCategoryWeightDetails -> id) . '&rc=' . encrypt_ex_data($this -> rcId), 'extra' => view_tooltip('Update') ]);

                            $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cRiskCategoryWeightDetails -> id) . '&rc=' . encrypt_ex_data($this -> rcId), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                            $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskCategoryWeightDetails -> id) . '&rc=' . encrypt_ex_data($this -> rcId), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                        }
                        else
                            $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskCategoryWeightDetails -> id) . '&rc=' . encrypt_ex_data($this -> rcId), 'extra' => view_tooltip('Activate') ]);
                    }                
                    else
                        $cDataArray["action"] .= '';

                    // push in array
                    $funcData['dataResArray']["aaData"][] = $cDataArray;
                }
            }
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function add($getRequest)
    {
        // calling checkRC function
        $this -> checkRC();

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?rc=' . encrypt_ex_data($this -> rcId));
        $this -> me -> pageHeading = 'Add Risk Weight';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> riskCategoryWeightModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

        //method call for year option
        $this -> checkExistingYear('add');

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
                $result = $this -> riskCategoryWeightModel::insert(
                    $this -> riskCategoryWeightModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk weight dashboard
                Validation::flashErrorMsg('riskweightAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskCategoryWeight') . '/?rc=' . encrypt_ex_data($this -> rcId) );

            }

        });

    }

    public function update($getRequest) 
    {
        // calling checkRC function
        $this -> checkRC();

        $this -> weightId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');
         
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' .encrypt_ex_data( $this -> weightId) . '&rc=' . encrypt_ex_data($this -> rcId));
        $this -> me -> pageHeading = 'Update Risk Weight';

        // get data //method call

        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> weightId]) ;
        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';

        //method call for year option
        $this -> checkExistingYear('update', $this -> weightId);

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('update', $this -> weightId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> riskCategoryWeightModel::update(
                    $this -> riskCategoryWeightModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> weightId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk weight dashboard
                Validation::flashErrorMsg('riskweightUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskCategoryWeight')  . '/?rc=' . encrypt_ex_data($this -> rcId) );
            }
        });
    }

    public function delete($getRequest) 
    {
        // calling checkRC function
        $this -> checkRC();

        $this -> weightId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> weightId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> riskCategoryWeightModel::delete($this -> riskCategoryWeightModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> weightId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to risk category dashboard
        Validation::flashErrorMsg('riskweightDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('riskCategoryWeight') . '/?rc=' . encrypt_ex_data($this -> rcId));
    }

    public function status($getRequest) 
    {
        // calling checkRC function
        $this -> checkRC();

        $this -> weightId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> weightId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> riskCategoryWeightModel::update(
            $this -> riskCategoryWeightModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> weightId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to risk category dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('riskCategoryWeight') .'/?rc=' . encrypt_ex_data($this -> rcId) );
    }

    private function getDataOr404($filter, $optional = null) 
    {   
        // //calling checkRc function
        // $this -> checkRC();

        $filter = [ 
            'where' => 'id = :id AND risk_category_id = :risk_category_id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> weightId, 'risk_category_id' => decrypt_ex_data($this -> request -> input('rc'))]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND risk_category_id = :risk_category_id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> riskCategoryWeightModel -> getSingleRiskCategoryWeight($filter);

        if(empty($this -> weightId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>