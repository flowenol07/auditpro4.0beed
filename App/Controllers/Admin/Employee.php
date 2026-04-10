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
use Controllers\PasswordPolicy;

class Employee extends Controller  {

    public $me = null, $data, $request, $auditUnit, $employee, $empId;
    public $auditUnitModel, $employeeModel, $userModel;

    public function __construct($me) 
    {
        $this -> me = $me;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('employeeMaster') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current employee model
        $this -> employeeModel = $this -> model('EmployeeModel'); 

        $this -> auditUnitModel = $this -> model('AuditUnitModel'); 


        // get all audit unit
        $auditUnit = $this -> auditUnitModel -> getAllAuditUnit(['where' => 'deleted_at IS NULL']);
        $this -> data['db_audit_unit_data'] = generate_array_for_select($auditUnit, 'id', 'name');

        //get all employee
        $db_employee_data = DBCommonFunc::getAllEmployeeData($this -> employeeModel, ['where' => 'is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_employee_data_arr'] = generate_data_assoc_array($db_employee_data, 'id');

        $db_employee_data = generate_array_for_select($db_employee_data, 'id', 'combined_name');
        $this -> data['db_employee_data'] =  $db_employee_data;

        unset($db_employee_data);
    }

    private function validateData($empId = '')
    {
        $uniqueWhere = [
            'model' => $this -> employeeModel,
            'where' => 'emp_code = :emp_code AND deleted_at IS NULL',
            'params' => [ 
                'emp_code' => $this -> request -> input('emp_code')
            ]
        ];

        $uniqueEmailWhere = [
            'model' => $this -> employeeModel,
            'where' => 'email = :email AND deleted_at IS NULL',
            'params' => [ 
                'email' => $this -> request -> input('email'),
            ]
        ];

        $uniqueMobileWhere = [
            'model' => $this -> employeeModel,
            'where' => 'mobile = :mobile AND deleted_at IS NULL',
            'params' => [
                'mobile' => $this -> request -> input('mobile')
            ]
        ];

        if(!empty($empId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $empId;

            $uniqueEmailWhere['where'] .= ' AND id != :id';
            $uniqueEmailWhere['params']['id'] = $empId;

            $uniqueMobileWhere['where'] .= ' AND id != :id';
            $uniqueMobileWhere['params']['id'] = $empId;
        }

        Validation::validateData($this -> request, [
            'emp_code' => 'required|regex[alphaNumricRegex, emp_code]|is_unique[unique_data, employeeCode]',
            'user_type' => 'required|array_key[user_types_array, user_type]',
            'name' => 'required|regex[charRegex, name]',
            'email' => 'required|regex[emailRegex, email]|is_unique[unique_email_data, emailDuplicate]',
            'mobile' => 'required|regex[mobileRegex, mobile]|is_unique[unique_mobile_data, mobileDuplicate]',
            'designation' => 'required|regex[alphaNumricRegex, designation]',
            'gender' => 'required|array_key[gender_array, gender]',
        ],[
            'gender_array'  =>  $GLOBALS['userGenderArray'],
            'user_types_array'  =>  $GLOBALS['userTypesArray'],
            'unique_data' => $uniqueWhere,
            'unique_email_data' => $uniqueEmailWhere,
            'unique_mobile_data' => $uniqueMobileWhere,
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
            'emp_code' => $this -> request -> input('emp_code' ),
            'user_type_id' => $this -> request -> input('user_type'),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'email' => $this -> request -> input('email'),
            'mobile' => $this -> request -> input('mobile'),
            'designation' => $this -> request -> input('designation'),
            'gender' => $this -> request -> input('gender'),
            'password' => 'Emp@' . date('Y'),
            'password_policy' => 1,
            'admin_id' => Session::get('emp_id'),
        );

        if($methodType == 'update')
            unset($dataArray['password'], $dataArray['password_policy'], $dataArray['admin_id'] );

        return $dataArray;
    }

    public function updateProfile()
    {
        $this -> request = new Request();

        $PasswordPolicyController = new PasswordPolicy(null);

        //find password policy data
        $passwordPolicyModel = $this -> model('PasswordPolicyModel');
        $this -> data['db_data'] = $passwordPolicyModel -> getSinglePasswordPolicy([ 'where' => 'deleted_at IS NULL', 'params' => [] ]);

        $this -> data['allowedChars'] = $PasswordPolicyController -> allowedChars;
        $this -> data['markupAlign'] = 'left';

        $this -> data = $PasswordPolicyController -> generateREGEXPasswordPolicy($this -> data);

        //find current employee details
        $this -> userModel = $this -> model('EmployeeModel');
        $this -> data['user_data'] = $this -> userModel -> getSingleEmploye([
            'where' => 'id = :id',
            'params' => [ 'id' => Session::get('emp_id') ]
        ]);

        //return error
        if( !is_object($this -> data['user_data']) || 
            !isset($this -> data['user_data'] -> emp_code) )
            return Except::exc_404( Notifications::getNoti('somethingWrong') );

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
        ];

        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'update-profile', [
                'me' => $this -> me,
                'request' => $this -> request,
                'data' => $this -> data
            ]);
            
        });

        $this -> request::method("POST", function() {
            
            $this -> request -> setInputCustom('custom_regex', $this -> data['db_data'] -> regex);

            $validationArray = [
                'name' => 'required|regex[charRegex, name]',
                'gender' => 'required|array_key[gender_array, gender]',
                'mobile' => 'required|regex[mobileRegex, mobile]',
            ];

            //change password
            if($this -> request -> has('change_password'))
            {
                $validationArray['current_password'] = 'required';
                $validationArray['password'] = 'required|regex[custom_regx, passwordPolicyNotMatched]';
                $validationArray['confirm_password'] = 'required|not_match[password, confirmPasswordNotMatch]';
            }
            
            Validation::validateData($this -> request, $validationArray, [
                'gender_array' => $GLOBALS['userGenderArray']
            ]);

            //match current_password
            if( isset($validationArray['current_password']) && 
                !$this -> request -> has('current_password_err') && 
                is_object($this -> data['user_data']) && 
                !verifyPasswordDb($this -> request -> input('current_password'), $this -> data['user_data'] -> password) )
            {
                //password not found //method call
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('current_password_err', 'old_password');
            }
            
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();

                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'update-profile', [
                    'me' => $this -> me,
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);

            } 
            else
            {
                $updateDataArray = array(
                    'name' => string_operations($this -> request -> input( 'name' ), 'upper'),
                    'gender' => $this -> request -> input( 'gender' ),
                    'mobile' => $this -> request -> input( 'mobile' ),
                );

                if(isset($validationArray['current_password']))
                {
                    $updateDataArray['password'] = generatePasswordHash($this -> request -> input('password'));
                    $updateDataArray['password_policy'] = 0;
                }

                $result = $this -> userModel::update(
                    $this -> userModel -> getTableName(), 
                    $updateDataArray, [ 
                        'where' => 'id = :id',
                        'params' => [ 'id' => Session::get('emp_id') ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                $employeeDetails = array(
                    'emp_id' => $this -> data['user_data'] -> id,
                    'emp_type' => $this -> data['user_data'] -> user_type_id,
                    'emp_name' => trim_str($updateDataArray['name']),
                    'emp_gender' => trim_str($updateDataArray['gender']),
                    'emp_design' => Session::get('emp_details')['emp_design']
                );

                $employeeDetails = generate_profile_img_url($this -> data['user_data'], $employeeDetails);
                Session::set('emp_details', $employeeDetails);

                Validation::flashErrorMsg(/*$this -> request, */'profileUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('updateProfile') );
            }            
        });
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('employeeMaster') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> employeeModel, 
            $this -> employeeModel -> getTableName(), [
                'where' => 'user_type_id NOT IN (1,9) AND deleted_at IS NULL',
                'params' => []
            ]
        );

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;
        
        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> employeeModel, ['emp_code', 'name', 'email', 'mobile'], [
            'where' => 'user_type_id NOT IN (1,9) AND deleted_at IS NULL'
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            foreach($funcData['dbData'] as $cEmpId => $cEmpDetails)
            {
                $name = '<p class="font-medium text-primary mb-0">'. (!empty($cEmpDetails -> gender) ? (ucfirst($cEmpDetails -> gender) .'. ') : '') . $cEmpDetails -> name .'</p>
                <p class="font-sm text-secondary mb-0"><span class="font-medium">Designation : </span>'. ($cEmpDetails -> designation ?? ERROR_VARS['notFoundSpan'] ) .'</p>';

                $cDataArray = [
                    "emp_code" => $cEmpDetails -> emp_code,
                    "name" => $name,
                    "user_type_id" => ($GLOBALS['userTypesArray'][$cEmpDetails -> user_type_id] ?? ERROR_VARS['notFoundSpan'] ),
                    "email" => !empty( $cEmpDetails -> email ) ? $cEmpDetails -> email : ERROR_VARS['notFoundSpan'],
                    "mobile" => !empty( $cEmpDetails -> mobile ) ? $cEmpDetails -> mobile : ERROR_VARS['notFoundSpan'],
                    "status" => check_active_status($cEmpDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];

                if($cEmpDetails -> is_active == 1) {
            
                    $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cEmpDetails -> id), 'extra' => view_tooltip('Update') ]);

                    // $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cEmpDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                    $cDataArray["action"] .= generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cEmpDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);

                    if(in_array($cEmpDetails -> user_type_id, [2,4,6]))
                        $cDataArray["action"] .= generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/authority/' . encrypt_ex_data($cEmpDetails -> id), 'extra' => view_tooltip('Give Authority')]);
                }
                else {

                    $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cEmpDetails -> id), 'extra' => view_tooltip('Activate') ]);
                }

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
        $this -> me -> pageHeading = 'Add Employee Details';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> employeeModel -> emptyInstance();
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
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {  
                $result = $this -> employeeModel::insert(
                    $this -> employeeModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to employee master dashboard
                Validation::flashErrorMsg('employeeAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('employeeMaster') );

            }

        });

    }

    public function update($getRequest) 
    {

        $this -> empId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> empId));
        $this -> me -> pageHeading = 'Update Employee Details';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> empId );

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
            if(!$this -> validateData($this -> empId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> employeeModel::update(
                    $this -> employeeModel -> getTableName(), 
                    $this -> postArray('update'),[
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> empId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to employee master dashboard
                Validation::flashErrorMsg('employeeUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('employeeMaster') );
            }
        });
    }

    // Commented delete code because of Omkar Sir's Advice
    // public function delete($getRequest) 
    // {

    //     $this -> empId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404( $this -> empId ) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $result = $this -> employeeModel::delete(
    //         $this -> employeeModel -> getTableName(), [ 
    //             'where' => 'id = :id',
    //             'params' => [ 'id' => $this -> empId ]
    //         ]);

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorDeleting') );

    //     //after insert data redirect to employee master dashboard
    //     Validation::flashErrorMsg('employeeDeletedSuccess', 'success');
    //     Redirect::to( SiteUrls::getUrl('employeeMaster') );
    // }

    public function status($getRequest) 
    {

        $this -> empId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> empId, 2 );

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> employeeModel::update(
            $this -> employeeModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> empId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to employee master dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('employeeMaster') );
    }

    public function authority($getRequest) 
    {
        $this -> empId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/authority/' . encrypt_ex_data($this -> empId));

        $this -> me -> pageHeading = 'Authority Management';

        $this -> me -> breadcrumb[] = $this -> me -> id ;

        // top data container 
        $this -> data['data_container'] = true;

       // get data //method call
       $this -> data['db_data'] = $this -> getDataOr404($this -> empId, 3);

        // if($this -> data['db_data'] -> user_type_id == 2 || $this -> data['db_data'] -> user_type_id == 4)
        // {
            //return if data not found
            if(!is_object($this -> data['db_data']))
                return $this -> data['db_data'];

            $this -> data['btn_type'] = 'update';

            //form
            $this -> request::method('GET', function() {

                // load view
                return return2View($this, $this -> me -> viewDir . 'form_authority', [ 'request' => $this -> request ]);

            });

            //post method after form submit
            $this -> request::method("POST", function() {
                
                if(is_array($this -> request -> input('audit_name')))
                {
                    $diffArray = array_diff($this -> request -> input('audit_name'), (array_keys($this -> data['db_audit_unit_data'])));
            
                    if(is_array($diffArray) && sizeof($diffArray) > 0)
                    {
                        Validation::incrementError($this -> request);
                        $this -> request -> setInputCustom('audit_name_err', 'auditAuthorityError');
                    }
                }

                //validation check
                if($this -> request -> input( 'error' ) > 0)
                {    
                    Validation::flashErrorMsg();
                    
                    // load view //error data
                    return return2View($this, $this -> me -> viewDir . 'form_authority', [ 'request' => $this -> request ]);
                } 
                else
                {   
                    //if there is an blank array then below code
                    $updateDataArray = array(
                        'audit_unit_authority' => '', 'admin_id' => Session::get('emp_id')
                    );
                    
                    //if there is an array then below code
                    if(is_array($this -> request -> input( 'audit_name' )))

                        $updateDataArray['audit_unit_authority'] = implode(",", $this -> request -> input( 'audit_name' )); 
                    

                    $result = $this -> employeeModel::update($this -> employeeModel -> getTableName(), 
                        $updateDataArray, [
                            'where' => 'id = :id',
                            'params' => [ 'id' => $this -> empId ]
                        ]
                    );

                    if(!$result)
                        return Except::exc_404( Notifications::getNoti('somethingWrong') );

                    //after insert data redirect to frequency dashboard
                    Validation::flashErrorMsg('auditAuthoritySavedSuccess', 'success');
                    Redirect::to( SiteUrls::getUrl('employeeMaster') . '/authority/' . encrypt_ex_data($this -> empId) );
                }
            });
        // }
        // else
        //     return Except::exc_404( Notifications::getNoti('somethingWrong') );


    }

    public function password($getRequest)
    {
        $this -> request = new Request();

        $PasswordPolicyController = new PasswordPolicy(null);

        //find password policy data
        $passwordPolicyModel = $this -> model('PasswordPolicyModel');
        $this -> data['db_data'] = $passwordPolicyModel -> getSinglePasswordPolicy([
            "where" => "deleted_at IS NULL"
        ]);

        $this -> data['allowedChars'] = $PasswordPolicyController -> allowedChars;
        $this -> data['markupAlign'] = 'left';

        $this -> data = $PasswordPolicyController -> generateREGEXPasswordPolicy($this -> data);

        //find employee details
        $this -> userModel = $this -> model('EmployeeModel');
        $this -> data['user_data'] = $this -> userModel -> getAllEmployees([
            "where" => "id != 1 AND is_active = 1 AND deleted_at IS NULL"
        ]);

        //return error
        if(!is_array($this -> data['user_data']))
            return Except::exc_404( Notifications::getNoti('noDataFound') );

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
        ];

        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/password/');
        $this -> me -> pageHeading = 'Set Password';
        $this -> me -> breadcrumb[] = $this -> me -> id ;
        $this -> me -> menuKey = 'setPassword';
        $this -> data['need_select'] = true;        

        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form_setpassword', [
                'me' => $this -> me,
                'request' => $this -> request,
                'data' => $this -> data
            ]);
            
        });

        $this -> request::method("POST", function() {
            
            $this -> request -> setInputCustom('custom_regex', $this -> data['db_data'] -> regex);            

            //change password
            $validationArray = [
                'id' => 'required',
                'password' => 'required|regex[custom_regx, passwordPolicyNotMatched]',
            ];
            
            Validation::validateData($this -> request, $validationArray);
            
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();

                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_setpassword', [
                    'me' => $this -> me,
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);
            } 
            else
            {
                $updateDataArray =[];
                
                $updateDataArray['password'] = generatePasswordHash($this -> request -> input('password'));

                $updateDataArray['password_policy'] = 0;
    

                $result = $this -> userModel::update(
                    $this -> userModel -> getTableName(), 
                    $updateDataArray, [ 
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> request -> input('id') ]
                    ]
                );

                if(!$result)
                    return Except::exc_404(Notifications::getNoti('somethingWrong'));

                Validation::flashErrorMsg('setPassword', 'success');
                Redirect::to( SiteUrls::getUrl('employeeMaster') . '/password' );
            }            
        });
    }

    private function getDataOr404($empId, $optional = null) 
    {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $empId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        if($optional == 3)
            $filter['where'] = 'id = :id AND ( user_type_id = 2 OR user_type_id = 4 OR user_type_id = 6 )AND is_active = 1 AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> employeeModel -> getSingleEmploye($filter);

        if(empty($this -> empId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>