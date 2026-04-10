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

class BranchRatingMaster extends Controller  {

    public $me = null, $data, $request, $auditUnit, $yearId, $year, $auditId, $typeId;
    public $auditUnitModel, $yearModel, $branchRatingModel;

    public function __construct($me) 
    {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('branchRatingMaster') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current audit unit model
        $this -> auditUnitModel = $this -> model('AuditUnitModel');

        // find branch rating model
        $this -> branchRatingModel = $this -> model('BranchRatingModel');

        //Search in select dropdown
        $this -> data['need_select'] = true;

        //get all units
        $this -> auditUnit = $this -> auditUnitModel -> getAllAuditUnit(['where' => 'deleted_at IS NULL']);

        $this -> data['db_audit_unit'] = generate_array_for_select($this -> auditUnit, 'id', 'name');

        $this -> data['db_audit_unit_id'] = generate_array_for_select($this -> auditUnit, 'id', 'id');

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, ['where' => 'deleted_at IS NULL']);

        $this -> data['db_year'] = generate_array_for_select($this -> year, 'id', 'year');

        $this -> data['riskParameter'] = array(
            1 => "HIGH RISK",
            2 => "MEDIUM RISK",
            3 => "LOW RISK",
        );
    }

    private function validateData($brIds = null)
    {
        $uniqueWhere = [
            'model' => $this -> branchRatingModel,
            'where' => 'year_id = :year_id AND audit_unit_id = :audit_unit_id AND audit_type_id = :audit_type_id AND deleted_at IS NULL',
            'params' => [ 
                'year_id' => decrypt_ex_data($this -> request -> input('yearId')),
                'audit_unit_id' => $this -> request -> input('audit_unit_id'),
                'audit_type_id' => $this -> request -> input('audit_type_id')
            ]
        ];

        if(is_array($brIds))
            $uniqueWhere['where'] .= ' AND id NOT IN ('. implode(',', $brIds) .')';

        if($this -> request -> has('bulkData'))
            $validationArray = [
                'audit_type_id' => 'required',
            ];
        else
            $validationArray = [
                'audit_unit_id' => 'required|is_unique[unique_data, yearUnitDuplicate]',
                'audit_type_id' => 'required',
            ];

        for ($i = 1; $i <= 3 ; $i++)
        { 
            $required = 'required|';

            $validationArray['range_from_' . $i] = $required . 'regex[floatNumberRegex, rangeFrom]';

            $validationArray['range_to_' . $i] = $required . 'regex[floatNumberRegex, rangeFrom]';
        }     

        Validation::validateData($this -> request, $validationArray, [
            'unique_data' => $uniqueWhere
        ]);

        //check Range Validation

        if(!$this -> request -> has('range_to_1_err') && !$this -> request -> has('range_from_2_err') && !$this -> request -> has('range_to_2_err') && !$this -> request -> has('range_from_3_err'))
        {
            $rangeTo1 = $this -> request -> input('range_to_1');
            $rangeFrom2 = $this -> request -> input('range_from_2');
            $rangeTo2 = $this -> request -> input('range_to_2');
            $rangeFrom3 = $this -> request -> input('range_from_3');

            if($rangeTo1 != $rangeFrom2)
            {
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('range_from_2_err', 'rangeValidate');
            }

            if($rangeTo2 != $rangeFrom3)
            {
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('range_from_3_err', 'rangeValidate');
            }
        }

        //validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    private function postArray()
    {
        $dataArray = array();

        for ($i = 1; $i <= 3 ; $i++)
        {
            $dataArray[] = array(
                'risk_type_id' => $this -> request -> input('risk_type_' . $i),
                'audit_unit_id' => $this -> request -> input('audit_unit_id'),
                'range_from' => $this -> request -> input('range_from_' . $i, 0),
                'range_to' => $this -> request -> input('range_to_' . $i, 0),
                'audit_type_id' => $this -> request -> input('audit_type_id'),
                'year_id' => decrypt_ex_data($this -> request -> input('yearId')),
                'admin_id' => Session::get('emp_id'),
            );
        }

        return $dataArray;
    }

    public function index() 
    {
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

    public function dataTableAjaxYear()
    {
        $funcData = generate_datatable_data($this, $this -> yearModel, ["year"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

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
                        $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/index-single-details?yearId=' . encrypt_ex_data($cYearDetails -> id), 'extra' => view_tooltip('Branch Rating')]);
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

    public function indexSingleDetails() 
    { 
        $this -> yearId = decrypt_ex_data($this -> request -> input('yearId'));

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('branchRatingMaster') . '/' ],
            'add' => [ 'href' => SiteUrls::getUrl('branchRatingMaster') . '/add?yearId=' . encrypt_ex_data($this -> yearId)],
        ];       

        if(empty($this -> yearId))
            return Except::exc_404( Notifications::getNoti('somethingWrong') );

        //total number of records without filtering // function call
            $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> branchRatingModel, 
            $this -> branchRatingModel -> getTableName(), [
                'where' => 'year_id = :year_id',
                'params' => ['year_id' => $this -> yearId],
                'extra' => 'GROUP BY audit_unit_id, year_id, audit_type_id'
            ]);

        if(!empty($this -> data['db_data_count']))
        {
            //re assign
            $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

            if($this -> data['db_data_count'] > 0)
                $this -> data['need_datatable'] = true;
        }
        else
            $this -> data['db_data_count'] = 0;

        //load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index_details', [ 
            'request' => $this -> request
        ]);
    }

    public function dataTableAjax()
    {   
        $this -> yearId = decrypt_ex_data($this -> request -> input('yearId'));

        $funcData = generate_datatable_data($this, $this -> branchRatingModel, [], [
            'where' => 'year_id = :year_id',
            'params' => [ 'year_id' => $this -> yearId ],
            'extra_params' => 'GROUP BY audit_unit_id, year_id, audit_type_id',
            'combined_count' => true
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

            foreach($funcData['dbData'] as $cBranchRatingId => $cBranchRatingDetails)
            {
                $cDataArray = [
                    "audit_unit_id" =>  ($this ->data['db_audit_unit'][$cBranchRatingDetails -> audit_unit_id] ?? ERROR_VARS['notFound']),

                    "year_id" =>  ($this ->data['db_year'][$cBranchRatingDetails -> year_id] ?? ERROR_VARS['notFound']),

                    "audit_type_id" =>  (AUDIT_TYPE_ARRAY[$cBranchRatingDetails -> audit_type_id] ?? ERROR_VARS['notFound']),

                    "action" => ""
                ];

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {                         
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update?auditId='. encrypt_ex_data($cBranchRatingDetails -> audit_unit_id) . '&yearId=' . encrypt_ex_data($cBranchRatingDetails -> year_id) . '&typeId=' . encrypt_ex_data($cBranchRatingDetails -> audit_type_id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete?auditId='. encrypt_ex_data($cBranchRatingDetails -> audit_unit_id) . '&yearId=' . encrypt_ex_data($cBranchRatingDetails -> year_id) . '&typeId=' . encrypt_ex_data($cBranchRatingDetails -> audit_type_id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
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
        $this -> yearId = decrypt_ex_data($this -> request -> input('yearId'));

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('branchRatingMaster') . '/index-single-details?yearId=' . encrypt_ex_data($this -> yearId)],
        ];

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?yearId=' . encrypt_ex_data($this -> yearId));
        $this -> me -> pageHeading = 'Add Branch Risk';

        // create empty instance for default values in form
        $this -> data['db_data'][1] = $this -> branchRatingModel -> emptyInstance();
        $this -> data['db_data'][1] -> risk_type_id = 1;

        $this -> data['db_data'][2] = $this -> branchRatingModel -> emptyInstance();
        $this -> data['db_data'][2] -> risk_type_id = 2;

        $this -> data['db_data'][3] = $this -> branchRatingModel -> emptyInstance();
        $this -> data['db_data'][3] -> risk_type_id = 3;

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
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 
                    'request' => $this -> request,
                    'data' => $this -> data
                ]); 
            else
            {  
                if($this -> request -> has('bulkData') && $this -> request -> input('bulkData') == 1)
                {
                    foreach( $this -> data['db_audit_unit_id'] as $cUnitId => $cUnitDetails)
                    {
                        $dataArray = [];

                        for ($i = 1; $i <= 3 ; $i++)
                        {
                            $dataArray[] = array(
                                'risk_type_id' => $this -> request -> input('risk_type_' . $i),
                                'audit_unit_id' => $cUnitId,
                                'range_from' => $this -> request -> input('range_from_' . $i, 0),
                                'range_to' => $this -> request -> input('range_to_' . $i, 0),
                                'audit_type_id' => $this -> request -> input('audit_type_id'),
                                'year_id' => $this -> yearId,
                                'admin_id' => Session::get('emp_id'),
                            );
                        }
                        // insert in database
                        $result = $this -> branchRatingModel::insertMultiple(
                            $this -> branchRatingModel -> getTableName(), 
                            $dataArray
                        );
                    }
                }
                else
                {
                    // insert in database
                    $result = $this -> branchRatingModel::insertMultiple(
                        $this -> branchRatingModel -> getTableName(), 
                        $this -> postArray()
                    );
                }

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to branch rating dashboard
                Validation::flashErrorMsg('branchRatingAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('branchRatingMaster') . '/index-single-details?yearId=' . encrypt_ex_data($this -> yearId));
            }
        });
    }

    public function update($getRequest) 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('branchRatingMaster') . '/index-single-details?yearId=' . $this -> request -> input('yearId')],
        ];

        $this -> auditId = decrypt_ex_data($getRequest['auditId'] ?? '');

        $this -> yearId = decrypt_ex_data($getRequest['yearId'] ?? '');

        $this -> typeId = decrypt_ex_data($getRequest['typeId'] ?? '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update?auditId='. encrypt_ex_data($this -> auditId) . '&yearId=' . encrypt_ex_data($this -> yearId) . '&typeId=' . encrypt_ex_data($this -> typeId));

        $this -> me -> pageHeading = 'Update Branch Rating';

        // get data //method call
        $this -> data['db_data'] = $this -> branchRatingModel -> getAllBranchRating([ 
            
            'where' => 'audit_type_id = :audit_type_id AND year_id = :year_id AND audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
            'params' => [
                'year_id' => $this -> yearId,
                'audit_unit_id' => $this -> auditId,
                'audit_type_id' => $this -> typeId,
            ]
        ]);

        if(empty($this -> data['db_data']))
            return Except::exc_404( Notifications::getNoti('somethingWrong') );

        //return if data not found
        if(!is_array($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['db_data'] = generate_data_assoc_array($this -> data['db_data'], 'id');

        $this -> data['btn_type'] = 'update';

        //form
        $this -> request::method('GET', function() {
            
            //load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData(array_keys($this -> data['db_data'])))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {   
                $whereArr = array();

                for($i = 1; $i <= 3; $i++)
                {
                    $whereArr[] = array(
                        'where' => 'id = :id',
                        'params' => [ 
                            'id' => $this -> data['db_data'][$i] -> id,
                        ],
                    );
                }

                $result = $this -> branchRatingModel::updateMultiple(
                    $this -> branchRatingModel -> getTableName(), 
                    $this -> postArray(),$whereArr
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to branch rating dashboard
                Validation::flashErrorMsg('branchRatingUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('branchRatingMaster') . '/index-single-details?yearId=' . encrypt_ex_data($this -> yearId) );
            }
        });
    }

    public function delete($getRequest) 
    {
        $this -> auditId = decrypt_ex_data($getRequest['auditId'] ?? '');

        $this -> yearId = decrypt_ex_data($getRequest['yearId'] ?? '');

        $this -> typeId = decrypt_ex_data($getRequest['typeId'] ?? '');

        // get data //method call
        $this -> data['db_data'] = $this -> branchRatingModel -> getAllBranchRating([ 
            
            'where' => 'year_id = :year_id AND audit_unit_id =:audit_unit_id AND audit_type_id = :audit_type_id AND deleted_at IS NULL',

            'params' => [
                'year_id' => $this -> yearId,
                'audit_unit_id' => $this -> auditId,
                'audit_type_id' => $this -> typeId,
            ]
        ]);

        //return if data not found
        if(!is_array($this -> data['db_data']))
            return $this -> data['db_data'];

        $whereArr = array();
        $idList = array();

        for($i = 0; $i < 3; $i++)
        {
            $whereArr[$this -> data['db_data'][$i] -> id] = array(
                'where' => 'id =:id',
                'params' => [ 
                    'id' => $this -> data['db_data'][$i] -> id,
                ],
            );

            $idList += array(
                $this -> data['db_data'][$i] -> id => $this -> data['db_data'][$i] -> id,
            );
        }

        foreach($idList as $cKey => $cData)
        {
            $result = $this -> branchRatingModel::delete(
                $this -> branchRatingModel -> getTableName(), $whereArr[$cKey]);
    
            if(!$result)
                return Except::exc_404( Notifications::getNoti('errorDeleting') );
        }
        
        Validation::flashErrorMsg('branchRatingDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('branchRatingMaster') . '/index-single-details?yearId=' . encrypt_ex_data($this -> yearId));
    }
}

?>