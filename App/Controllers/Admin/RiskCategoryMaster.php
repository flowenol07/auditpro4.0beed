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


class RiskCategoryMaster extends Controller  {

    public $me = null, $data, $request, $riskId, $riskCategoryWeight, $year;
    public $riskCategoryWeightModel, $riskCategoryModel, $yearModel;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskCategoryMaster') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current risk category model
        $this -> riskCategoryModel = $this -> model('RiskCategoryModel');

        $this -> riskCategoryWeightModel = $this -> model('RiskCategoryWeightModel');

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, ['where' => 'deleted_at IS NULL']);

        $this -> data['db_year'] = generate_array_for_select($this -> year, 'id', 'year');
        
    }

    private function validateData($methodType = 'add', $riskId = '')
    {

        $uniqueWhere = [
            'model' => $this -> riskCategoryModel,
            'where' => 'risk_category = :risk_category AND deleted_at IS NULL',
            'params' => 
            [ 
                'risk_category' => $this -> request -> input('risk_category'),
            ]
        ];

        if(!empty($riskId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $riskId;
        }

        $validationArray = [
            'risk_category' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, riskcategoryDuplicate]',
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
            'risk_category' => string_operations($this -> request -> input('risk_category'), 'upper'),
            'risk_weight' => 0,
            'risk_appetite_percent_from' => 0,
            'risk_appetite_percent_to' => 0,
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('riskCategoryMaster') . '/add' ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> riskCategoryModel, 
            $this -> riskCategoryModel -> getTableName(), [
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
        $funcData = generate_datatable_data($this, $this -> riskCategoryModel, ["risk_category"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            $this -> data['db_risk_weight'] = $this -> riskCategoryWeightModel -> getAllRiskCategoryWeight([ 'where' => 'deleted_at IS NULL ORDER BY year_id']);

            foreach($this -> data['db_risk_weight'] as $riskIndex => $riskData)
            {
                if(array_key_exists( $riskData -> risk_category_id, $funcData['dbData'] ))
                {
                    if(!isset( $funcData['dbData'][$riskData -> risk_category_id] -> years ))
                        $funcData['dbData'][$riskData -> risk_category_id] -> years = [];
                
                    $funcData['dbData'][$riskData -> risk_category_id] -> years[$riskData -> year_id] = $riskData;
                }
            }

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cRiskCategoryId => $cRiskCategoryDetails)
            {
                $markup = '
                <p class="font-medium text-primary mb-0">' . $cRiskCategoryDetails -> risk_category . '</p>';

                if(isset($cRiskCategoryDetails -> years))
                {
                    $markup .= '<table class="table table-bordered table-sm v-table">
                        <tr>
                            <th class="font-sm text-secondary">Financial Year</th>
                            <th class="font-sm text-secondary">Risk Weight</th>
                            <th class="font-sm text-secondary">Appetite Percent</th>
                        </tr>';

                    foreach($cRiskCategoryDetails -> years as $riskIndex => $riskData)
                    {
                        $markup .= '<tr>
                            <td>' . ((array_key_exists($riskData -> year_id, $this -> data['db_year'])) ? $this -> data['db_year'][$riskData -> year_id] : ERROR_VARS['notFoundSpan']) . '</td>
                            <td> ' . $riskData -> risk_weight . '</td>
                            <td>' . $riskData -> risk_appetite_percent . '</td>
                        </tr>';
                    }

                    $markup .= '</table>';

                }
                else
                    $markup .= ERROR_VARS['notFoundSpan'];

                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "risk_category" => $markup,
                    "status" => check_active_status($cRiskCategoryDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    if($cRiskCategoryDetails -> is_active == 1) {
                        
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cRiskCategoryDetails -> id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskCategoryDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::getUrl('riskCategoryWeight') . '?rc=' . encrypt_ex_data($cRiskCategoryDetails -> id), 'extra' => view_tooltip('Add Weight')]);
                    }
                    else 
                    {
                        $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cRiskCategoryDetails -> id), 'extra' => view_tooltip('Activate') ]);
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
        $this -> me -> pageHeading = 'Add Risk Category';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> riskCategoryModel -> emptyInstance();
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
                $result = $this -> riskCategoryModel::insert(
                    $this -> riskCategoryModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk category dashboard
                Validation::flashErrorMsg('riskcategoryAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskCategoryMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> riskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> riskId));
        $this -> me -> pageHeading = 'Update Risk Category';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> riskId]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';
        $this -> data['need_calender'] = true;

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('update', $this -> riskId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> riskCategoryModel::update(
                    $this -> riskCategoryModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> riskId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to risk category dashboard
                Validation::flashErrorMsg('riskcategoryUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('riskCategoryMaster') );
            }
        });
    }

    // Commented as per advice of Omkar Sir
    // public function delete($getRequest) {

    //     $this -> riskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> riskId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $result = $this -> riskCategoryModel::delete($this -> riskCategoryModel -> getTableName(),[
    //         'where' => 'id = :id',
    //         'params' => [ 'id' => $this -> riskId ]
    //     ]);

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorDeleting') );

    //     //after insert data redirect to risk category dashboard
    //     Validation::flashErrorMsg('riskcategoryDeletedSuccess', 'success');
    //     Redirect::to( SiteUrls::getUrl('riskCategoryMaster') );
    // }

    public function status($getRequest) {

        $this -> riskId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> riskId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> riskCategoryModel::update(
            $this -> riskCategoryModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> riskId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to risk category dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('riskCategoryMaster') );
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> riskId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> riskCategoryModel -> getSingleRiskCategory($filter);

        if(empty($this -> riskId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>