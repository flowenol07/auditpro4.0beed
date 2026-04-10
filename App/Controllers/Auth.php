<?php

namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;

class Auth extends Controller  {

    public $me = null, $request;

    public function __construct($me) {
        $this -> me = $me;
    }

    public function index()
{
    // Check if password policy needs to be updated
    if (Session::has("need_password_policy")) {
        Redirect::to(SiteUrls::getUrl('password-policy'));
    }

    // If user is already logged in, go to dashboard
    if (Session::has("emp_id")) {
        Redirect::to(SiteUrls::getUrl('dashboard'));
    }

    // Decide which login page to show based on multi-audit flag
    if (IS_MULTI_AUDIT['isValid'] == 1) {
        // Multi-DB / Multi-Audit enabled → go to branch selection page
        Redirect::to(SiteUrls::getUrl('auth') . '/login_with_audit_type');
    } else {
        // Single DB → go to normal login
        Redirect::to(SiteUrls::getUrl('auth') . '/login');
    }
}

    public function login()
    {
        //check login
        if( Session::has("emp_id") )
            Redirect::to( SiteUrls::getUrl('dashboard') );

        $this -> request = new Request();

        $this -> request::method('GET', function() {

            // load view
            return $this -> view( $this -> me -> viewDir . 'login', [
                'me' => $this -> me,
                'request' => $this -> request,
            ], 'login' );
            
        });

        $this -> request::method("POST", function() {

            Validation::validateData($this -> request, [
                'emp_code' => 'required',
                'password' => 'required'
            ]);

            // if user logged in redirect to index
            if($this -> request -> input( 'error' ) > 0)
            {    
                // load view //error data
                return $this -> view( $this -> me -> viewDir . 'login', [
                    'me' => $this -> me,
                    'request' => $this -> request,
                ], 'login' );

            } 
            else
            {
                //get data from dband validate
                $employee_model = $this -> model('EmployeeModel');
                
                $data = $employee_model -> getSingleEmploye( [
                    'where' => 'emp_code = :emp_code AND deleted_at IS NULL',
                    'params' => ['emp_code' => $this -> request -> input( 'emp_code' )]
                ] );
                
                //email not exits
                if(!is_object($data))
                {
                    //email not found //method call
                    Validation::incrementError($this -> request);
                    Validation::flashErrorMsg();
                    $this -> request -> setInputCustom('emp_code_err', 'empCodeNotFound');
                }

                //match password
                if(is_object($data) && !verifyPasswordDb($this -> request -> input('password'), $data -> password) )
                {
                    //if password not match check with old one
                    if($this -> request -> input('password') != trim_str($data -> password))
                    {
                        //passwoed not found //method call
                        Validation::incrementError($this -> request);
                        Validation::flashErrorMsg(/*$this -> request, */'logincredentialsError', 'danger');
                        $this -> request -> setInputCustom('password_err', 'password');
                    }
                }

                //check blocked
                if(is_object($data) && $data -> is_active != 1)
                {
                    //email not found //method call
                    Validation::incrementError($this -> request);
                    Validation::flashErrorMsg(/*$this -> request, */'empCodeBlocked', 'danger');
                    // $this -> request -> setInputCustom('password_err', 'password');
                }

                if($this -> request -> input('error') > 0)
                {
                    // load view //error data
                    return $this -> view( $this -> me -> viewDir . 'login', [
                        'me' => $this -> me,
                        'request' => $this -> request,
                    ], 'login' );
                }
                else
                {
                    //insert data in session like emp code, name, profile, designation
                    $employeeDetails = array(
                        'emp_id' => $data -> id,
                        'emp_type' => $data -> user_type_id,
                        'emp_name' => trim_str($data -> name),
                        'emp_gender' => trim_str($data -> gender),
                        'emp_design' => null,
                        'emp_profile' => null
                    );

                    //user designations
                    $employeeDetails['emp_design'] = check_array_exists($GLOBALS['userTypesArray'], $data -> user_type_id, ERROR_VARS['notAvailable']);

                    $employeeDetails = generate_profile_img_url($data, $employeeDetails);

                    //push session emp id
                    Session::set('emp_id', $data -> id);
                    Session::set('emp_type', $data -> user_type_id);
                    Session::set('emp_details', $employeeDetails);

                    //password policy changed
                    if($data -> password_policy)
                    {
                        Session::set('need_password_policy', true);
                        Redirect::to( SiteUrls::getUrl('passwordPolicy') );
                    }

                    if($data -> user_type_id == 2 || $data -> user_type_id == 4 || $data -> user_type_id == 16)
                        Redirect::to( SiteUrls::getUrl('dashboard') . '/select-audit-unit' );
                    elseif($data -> user_type_id == 3)
                        Redirect::to( SiteUrls::getUrl('dashboard'));
                    elseif(in_array($data -> user_type_id, [1,9])) // for admin and lite admin
                        Redirect::to( SiteUrls::getUrl('dashboard'));
                    elseif($data -> user_type_id == 5)
                        Redirect::to( SiteUrls::getUrl('dashboard'));

                    // Compliance Pro 16.09.2024 Login
                    elseif( in_array($data -> user_type_id, [6,7]) )
                        Redirect::to( SiteUrls::getUrl('dashboard'));
                    elseif($data->user_type_id == 10)
                        Redirect::to( SiteUrls::getUrl('supportDashboard'));
                    //super admin
                    elseif($data -> user_type_id == 11)
                        Redirect::to( SiteUrls::getUrl('superAdminDashboard') );
                }

                
            }
        });
    }
    public function login_with_audit_type()
{
    if (Session::has("emp_id"))
        Redirect::to(SiteUrls::getUrl('dashboard'));

    $this->request = new Request();

    $this->request::method('GET', function () {
        return $this->view($this->me->viewDir . 'login_with_audit_type', [
            'me' => $this->me,
            'request' => $this->request,
        ], 'login');
    });

    $this->request::method("POST", function () {

        Validation::validateData($this->request, [
            'audit-type' => 'required'
        ]);

        if ($this->request->input('error') > 0) {
            return $this->view($this->me->viewDir . 'login_with_audit_type', [
                'me' => $this->me,
                'request' => $this->request,
            ], 'login');
        }

        $branch = $this->request->input('audit-type');
        Session::set('audit-type', $branch);

        Redirect::to(SiteUrls::getUrl('auth') . '/login');
    });
}
    public function logout()
{
    // clear session
    Session::delete('emp_id');
    Session::delete('audit-type'); // 🔥 important (reset DB selection)

    session_destroy();

    // redirect to branch selection page
    Redirect::to(SiteUrls::getUrl('auth') . '/login_with_audt_type');
}
}

?>