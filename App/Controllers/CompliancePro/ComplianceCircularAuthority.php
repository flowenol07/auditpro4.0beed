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

class ComplianceCircularAuthority extends Controller  {

    public $me = null, $data, $request, $authId;
    public $authorityModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('complianceCircularAuthority') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current menu model
        $this -> authorityModel = $this -> model('ComplianceCircularAuthorityModel');
    }

    private function validateData($methodType = 'add', $authId = '')
    {
        $uniqueWhere = [
            'model' => $this -> authorityModel,
            'where' => 'name = :name AND deleted_at IS NULL',
            'params' => [ 
                'name' => $this -> request -> input('name'),
            ]
        ];

        if($methodType == 'update' && !empty($authId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $authId;
        }

        $validationArray = [
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, authorityDuplicate]'
        ];

        Validation::validateData($this -> request, $validationArray, [
            'unique_data' => $uniqueWhere
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
            'add' => [ 'href' => SiteUrls::getUrl('complianceCircularAuthority') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> authorityModel, 
            $this -> authorityModel -> getTableName()
        );

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        $this -> data['need_datatable'] = true;

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> authorityModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;
            $srNo = 1;

            foreach($funcData['dbData'] as $cMenuId => $cMenuDetails)
            {
                $cDataArray = [
                    "sr_no" => $srNo,
                    "name" => $cMenuDetails -> name,
                    "status" => check_active_status($cMenuDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
                
                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {
                    if($cMenuDetails -> is_active == 1) {
                
                        $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .= generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                    }
                    else {

                        $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Activate') ]);
                    } 
                }             
                else
                    $cDataArray["action"] .= '-';

                $srNo++;

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
        $this -> me -> breadcrumb[] = $this -> me -> id;
        $this -> me -> pageHeading = 'Add Authority';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> authorityModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

        //post method after form submit
        $this -> request::method("POST", function() {

            // validation check
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
                $result = $this -> authorityModel::insert(
                    $this -> authorityModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                // after insert data redirect to menu dashboard
                Validation::flashErrorMsg('authorityAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('complianceCircularAuthority') );
            }
        });

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    public function update($getRequest) {

        $this -> authId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> authId));
        $this -> me -> breadcrumb[] = $this -> me -> id;
        $this -> me -> pageHeading = 'Update Authority';

        // get data // method call
        $this -> getDataOr404(' AND is_active = 1');

        $this -> data['btn_type'] = 'update';

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('update', $this -> authId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);
            } 
            else
            {
                $result = $this -> authorityModel::update(
                    $this -> authorityModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> authId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to menu dashboard
                Validation::flashErrorMsg('authorityUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl( $this -> me -> id ) );
            }
        });

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    public function status($getRequest) {

        $this -> authId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data // method call
        $this -> getDataOr404() ;
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1;

        $result = $this -> authorityModel::update(
            $this -> authorityModel -> getTableName(),
            [ 'is_active' => $updateStatus ], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> authId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to menu dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl( $this -> me -> id ) );
    }

    private function getDataOr404($optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> authId ]
        ];

        if(!empty($optional))
            $filter['where'] .= $optional;

        // get data
        if(!empty($this -> authId))
            $this -> data['db_data'] = $this -> authorityModel -> getSingleCircularAuthority($filter);

        if(!isset($this -> data['db_data']) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }
    }
}

?>