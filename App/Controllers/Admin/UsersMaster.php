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

class UsersMaster extends Controller  {

    public $me = null, $request, $data, $userId;
    public $userModel;

    public function __construct($me) {

        $this -> me = $me;

        // Admin Array
        $this->data['adminArray'] = [
            'ADMIN' => 'ADMIN',
            'USER' => 'USER'
        ];

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('user') ],
        ];

        //Search in Select 
        $this -> data['need_select'] = true;
        
        // request object created
        $this -> request = new Request();

        // find current scheme model
        $this -> userModel = $this -> model('UserModel');   
    }

    private function validateData($userId = '')
    {
        Validation::validateData($this -> request, [
            'full_name' => 'required',
            'role_base' => 'required'
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

    private function postArray()
    {
        $dataArray = array(
            'full_name' => $this -> request -> input( 'full_name' ),
            'role_base' => $this -> request -> input( 'role_base' )
        );

        return $dataArray;
    }

    public function index() 
    {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('manageUsers') . '/add'. '?salary=' . encrypt_ex_data(4) ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> userModel, 
            $this -> userModel -> getTableName(), [
                'where' => 'deleted_at IS NULL']);

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> userModel, ["full_name", "role_base"], [
            'where' => 'deleted_at IS NULL'
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cUserId => $cUserDetails)
            {
                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "full_name" => $cUserDetails -> full_name,
                    "role_base" => $cUserDetails -> role_base,
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if(1)
                {                         
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cUserDetails -> id), 'extra' => view_tooltip('Update') ]);

                    $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cUserDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                    $cDataArray["action"] .= generate_link_button('link', ['href' => SiteUrls::getUrl('subuserManage') . '?salary=' . encrypt_ex_data($cUserDetails -> id), 'extra' => view_tooltip('Sub-Category')]);
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
        $this -> me -> pageHeading = 'Add User';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> userModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData())
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> userModel::insert(
                    $this -> userModel -> getTableName(), 
                    $this -> postArray() //method call
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //after insert data redirect to scheme
                Validation::flashErrorMsg('User added successfully', 'success');
                Redirect::to( SiteUrls::getUrl('manageUsers') );
            }

        });

    }

    public function update($getRequest) 
    {

        $this -> userId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> userId));
        $this -> me -> pageHeading = 'Update User';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> userId);

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
            if(!$this -> validateData($this -> userId))
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> userModel::update($this -> userModel -> getTableName(), 
                    $this -> postArray(), 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> userId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('errorSaving') );

                //after insert data redirect to scheme
                Validation::flashErrorMsg('User updated successfully', 'success');
                Redirect::to( SiteUrls::getUrl('manageUsers') );
            }
        });
    }

    public function delete($getRequest) 
    {

        $this -> userId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> userId ) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> userModel::delete(
            $this -> userModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> userId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to scheme
        Validation::flashErrorMsg('schemeDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('manageUsers') );
    }

    private function getDataOr404($userId, $optional = null) 
    {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $userId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> userModel -> getSingleUser($filter);

        if(empty($userId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>