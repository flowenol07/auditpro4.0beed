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


class RiskMatrix extends Controller  {

    public $me = null, $data, $request, $yId, $year, $matrixId, $validateType;
    public $yearModel, $riskMatrixModel;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('riskMatrix') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current risk matrix model
        $this -> riskMatrixModel = $this -> model('RiskMatrixModel');

        $this -> yId = decrypt_ex_data($this -> request -> input('yId'));        
        
        $this -> data['db_data'] = $this -> riskMatrixModel -> getAllRiskMatrix([ 
            'where' => 'year_id = :year_id AND deleted_at IS NULL',
            'params' => ['year_id' => $this -> yId] ]);

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $filter = ['where' => 'deleted_at IS NULL', 'params' => []];
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, $filter);

        $this -> data['db_year'] = generate_array_for_select($this -> year, 'id', 'year');  
        
        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, [
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => ['id' => $this -> yId]
        ]);
        
        if(empty($this -> year) && $this -> yId != '') {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }
    }

    private function validateData($methodType = 'add', $yId = '')
    {
        $validationArray = [];

        for ($i = 1; $i <= 4 ; $i++)
        { 
            $required = ($i != 4) ? 'required|' : '';
            $validationArray['risk_parameter_' . $i] = 'regex[numberRegex, risk_parameter]';
            $validationArray['business_risk_app_' . $i] = 'regex[numberRegex, business_risk_app]';
            $validationArray['business_risk_score_' . $i] = $required . 'regex[numberRegex, businessRiskScore]';
            $validationArray['control_risk_app_' . $i] = 'regex[numberRegex, control_risk_app],';
            $validationArray['control_risk_score_' . $i] = $required . 'regex[numberRegex, controlRiskScore]';
            $validationArray['residual_risk_app_' . $i] = 'regex[numberRegex, residual_risk_app]';
        }
        
        Validation::validateData($this -> request, $validationArray);

        //validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    private function postArray($methodType = 'add', $dbData = [])
    {
        $dataArray = [];

        $riskParamArray = array_keys(RISK_PARAMETERS_ARRAY);

        if($methodType == 'update' && is_array($dbData) && sizeof($dbData) > 0)
            $riskParamArray = array_keys($dbData);            

        foreach($riskParamArray as $i)
        {
            $dataArray[$i] = array(
                'risk_parameter' => $this -> request -> input('risk_parameter_' . $i),
                'business_risk_app' => $this -> request -> input('business_risk_app_' . $i, 0),

                'business_risk_score' => $this -> request -> input('business_risk_score_' . $i, 0),
                'control_risk_app' => $this -> request -> input('control_risk_app_' . $i, 0),

                'control_risk_score' => $this -> request -> input('control_risk_score_' . $i, 0),
                'residual_risk_app' => $this -> request -> input('residual_risk_app_' . $i, 0),

                'year_id' => decrypt_ex_data($this -> request -> input('yId')),
                'admin_id' => Session::get('emp_id')
            );
        }
    
        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> yearModel, 
            $this -> yearModel -> getTableName(), [
                'where' => 'deleted_at IS NULL',
                'params' => []
            ]
        );

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        return return2View($this, $this -> me -> viewDir . 'index');

    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> yearModel, ["year"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cYearId => $cYearDetails)
            {
                if(array_key_exists($cYearDetails -> id, $this -> data['db_year']))
                {    $cDataArray = [
                        "sr_no" =>  $srNo,
                        "year" => $this -> data['db_year'][$cYearDetails -> id],
                        "action" => ""
                    ];
                    
                    $srNo++;

                    // For Enable of Action on Assement Start
                    if($CHECK_ADMIN_ACTION)
                    { 
                        $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/view-risk-matrix?yId=' . encrypt_ex_data($cYearDetails -> id), 'extra' => view_tooltip('Risk Matrix')]);
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

    public function viewRiskMatrix() {

        $this -> yId = decrypt_ex_data($this -> request -> input('yId'));
                
        // print_r($this -> data['db_data']);

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/view-risk-matrix?yId=' . encrypt_ex_data($this -> yId));
        $this -> me -> pageHeading = 'Risk Matrix';

        $this -> me -> breadcrumb[] = $this -> me -> id ;

        // get data //method call
        // $this -> data['db_data'] = $this -> getDataOr404($this -> yId);

        $this -> data['db_data'] = $this -> riskMatrixModel -> getAllRiskMatrix([ 
            'where' => 'year_id = :year_id AND deleted_at IS NULL',
            'params' => ['year_id' => $this -> yId] ]);

        $this -> data['db_data'] = generate_data_assoc_array($this -> data['db_data'], 'risk_parameter');

        if(empty($this -> data['db_data']))
        {
            // create empty instance for default values in form
            $this -> data['btn_type'] = 'add';
            $this -> validateType = 'add';
        }
        else
        {
            $this -> data['btn_type'] = 'update';
            $this -> validateType = 'update';
        }

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData($this -> validateType, $this -> yId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                if($this -> validateType == 'add')
                {
                        // insert in database
                        $result = $this -> riskMatrixModel::insertMultiple(
                                    $this -> riskMatrixModel -> getTableName(), 
                                    $this -> postArray()
                                    );                    

                    if(!$result)
                        return Except::exc_404( Notifications::getNoti('somethingWrong') );

                    //after insert data redirect to risk matrix dashboard
                    Validation::flashErrorMsg('riskMatrixAddedSuccess', 'success');
                    Redirect::to( SiteUrls::getCurrentUrl() );
                }
                else
                {
                    $dataArray = $this -> postArray();

                    $diffArray = array_diff_key($dataArray, $this -> data['db_data']);

                    if(!empty($diffArray))
                    {
                        // insert in database
                        $result = $this -> riskMatrixModel::insertMultiple(
                                $this -> riskMatrixModel -> getTableName(), 
                                $diffArray
                        );
                    }
                    
                    if(is_array($this -> data['db_data']) && sizeof($this -> data['db_data']) > 0)
                    {
                        $whereArr = [];

                        foreach($this -> data['db_data'] as $cRPDetails)
                        {
                            $whereArr[$cRPDetails -> risk_parameter] = array(
                                'where' => 'id = :id AND year_id =:year_id',
                                'params' => [ 
                                    'year_id' => $this -> yId,
                                    'id' => $cRPDetails -> id,
                                ]
                            );
                        }

                        // update in database
                        $result = $this -> riskMatrixModel::updateMultiple(
                            $this -> riskMatrixModel -> getTableName(), 
                            $this -> postArray('update', $this -> data['db_data']), 
                            $whereArr
                        );
                    }

                    if(!$result)
                        return Except::exc_404( Notifications::getNoti('somethingWrong') );

                    //after update data redirect to matrix dashboard
                    Validation::flashErrorMsg('riskMatrixUpdatedSuccess', 'success');
                    Redirect::to( SiteUrls::getCurrentUrl() );
                }  
            }
        });
    }
    

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> yId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> riskMatrixModel -> getSingleRiskMatrix($filter);

        if(empty($this -> yId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>