<?php

namespace Controllers;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

class PasswordPolicy extends Controller  {

    public $me = null, $request, $ppId, $data, $allowedChars = "!@#$%^&*-";
    public $passwordPolicyModel, $employeeModel;

    public function __construct($me) {
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> passwordPolicyModel = $this -> model('PasswordPolicyModel');
        $this -> employeeModel = $this -> model('EmployeeModel');
    }

    public function index()
    {
        if(!Session::has('need_password_policy'))
        {
            // load view
            return Except::exc_404('Unauthorised access');
        }

        //find password policy data
        $passwordPolicyModel = $this -> model('PasswordPolicyModel');
        $this -> data['db_data'] = $passwordPolicyModel -> getSinglePasswordPolicy(['where' => 'deleted_at IS NULL', 'params' => []]);

        $this -> data['allowedChars'] = $this -> allowedChars;

        $this -> request = new Request();

        //regex refered ///^(?=(?:[^a-z]*[a-z]){0})(?=(?:[^A-Z]*[A-Z]){1})(?=(?:\D*\d){1})(?=(?:[^\!@\#\$%\^&\*\-]*[\!@\#\$%\^&\*\-]){2})[a-zA-Z\d\!@\#\$%\^&\*\-]{8,}$/

        $this -> data = $this -> generateREGEXPasswordPolicy( $this -> data );

        $this -> request::method('GET', function() {

            // load view
            return $this -> view( $this -> me -> viewDir . 'password-policy', [
                'me' => $this -> me,
                'request' => $this -> request,
                'data' => $this -> data
            ], 'login' );
            
        });

        $this -> request::method("POST", function() {

            $this -> request -> setInputCustom('custom_regex', $this -> data['db_data'] -> regex);
            
            Validation::validateData($this -> request, [
                'password' => 'required|regex[custom_regx, passwordPolicyNotMatched]',
                'confirm_password' => 'required|not_match[password, confirmPasswordNotMatch]'
            ]);

            if($this -> request -> input( 'error' ) > 0)
            {    
                // load view //error data
                return $this -> view( $this -> me -> viewDir . 'password-policy', [
                    'me' => $this -> me,
                    'request' => $this -> request,
                    'data' => $this -> data
                ], 'login' );

            } 
            else
            {
                // echo 'all ok';
                $user_model = $this -> model('EmployeeModel');

                $this -> request -> setInputCustom( 'password', generatePasswordHash($this -> request -> input('password')) );

                $result = $user_model::update(
                    $user_model -> getTableName(), 
                    [ 'password' => $this -> request -> input( 'password' ), 'password_policy' => 0 ],
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => Session::get('emp_id') ]
                ]);

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //unset session vars
                Session::delete('emp_id');
                Session::delete('emp_type');
                Session::delete('emp_details');
                Session::delete('need_password_policy');

                Validation::flashErrorMsg(/*$this -> request, */'passwordChangedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('login') );
            }
        });
    }

    public function generateREGEXPasswordPolicy($data)
    {
        if(is_object($data['db_data']))
        {
            $data['db_data'] -> regex = '^';

            if($data['db_data'] -> lowercase_cnt > 0)
            {
                //lowercase
                $data['db_data'] -> lowercase_cnt_regex = '(?=(?:[^a-z]*[a-z]){'. $data['db_data'] -> lowercase_cnt .'})';
                $data['db_data'] -> regex .= $data['db_data'] -> lowercase_cnt_regex;
            }

            if($data['db_data'] -> uppercase_cnt > 0)
            {
                //uppercase
                $data['db_data'] -> uppercase_cnt_regex = '(?=(?:[^A-Z]*[A-Z]){'. $data['db_data'] -> uppercase_cnt .'})';
                $data['db_data'] -> regex .= $data['db_data'] -> uppercase_cnt_regex;
            }

            if($data['db_data'] -> num_cnt > 0)
            {
                //num count
                $data['db_data'] -> num_cnt_regex = '(?=(?:\D*\d){'. $data['db_data'] -> num_cnt .'})';
                $data['db_data'] -> regex .= $data['db_data'] -> num_cnt_regex;
            }

            if($data['db_data'] -> symbol_cnt > 0)
            {
                //symbol_cnt
                $data['db_data'] -> symbol_cnt_regex = '(?=(?:[^\!@\#\$%\^&\*\-]*[\!@\#\$%\^&\*\-]){'. $data['db_data'] -> symbol_cnt .'})';
                $data['db_data'] -> regex .= $data['db_data'] -> symbol_cnt_regex;
            }

            $data['db_data'] -> regex .= '['. ( ($data['db_data'] -> lowercase_cnt > 0) ? 'a-z' : '' ) . 
                                           ( ($data['db_data'] -> uppercase_cnt > 0) ? 'A-Z' : '' ) .
                                           ( ($data['db_data'] -> num_cnt > 0) ? '\d' : '' ) .
                                           ( ($data['db_data'] -> symbol_cnt > 0) ? '\!@\#\$%\^&\*\-' : '' ) . 
                                      ']{'. $data['db_data'] -> min_length .',}$';

            //need password policy js
            $data['js'][] = 'password-policy.js';
        }

        return $data;
    }

    public function update($getRequest) {

        $this -> me = SiteUrls::get('passwordPolicyAdmin');
        $this -> me -> menuKey = 'passwordPolicyAdmin';

        // manually authority check
        accessControlCheck($this -> me);

        $this -> ppId = 1;
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update');
        $this -> me -> viewDir = 'admin/password-policy/';

        //find password policy data
        
        $this -> data['db_data'] = $this -> passwordPolicyModel -> getSinglePasswordPolicy(['where' => 'deleted_at IS NULL', 'params' => []]);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            $this -> data['db_data'] = $this -> passwordPolicyModel -> emptyInstance();

        $this -> data['btn_type'] = 'update';
        $this -> data['allowedChars'] = $this -> allowedChars;

        // print_r($this -> me -> viewDir);
        
        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'index', [
                'me' => $this -> me,
                'request' => $this -> request,
                'data' => $this -> data
            ]);
            
        });

        //post method after form submit
        $this -> request::method("POST", function() {

            $validateArray = [
                'min_length' => 'required|regex[numberRegex, min_length]',
                'num_cnt' => 'required|regex[numberRegex, num_cnt]',
                'uppercase_cnt' => 'required|regex[numberRegex, uppercase_cnt]',
                'lowercase_cnt' => 'required|regex[numberRegex, lowercase_cnt]',
                'symbol_cnt' => 'required|regex[numberRegex, symbol_cnt]',
            ];

            Validation::validateData($this -> request,$validateArray);

            $minLength = $this -> request -> input( 'min_length');
            $numCount = $this -> request -> input('num_cnt');
            $upperCount = $this -> request -> input('uppercase_cnt');
            $lowerCount = $this -> request -> input('lowercase_cnt');
            $symCount = $this -> request -> input('symbol_cnt');

            $totalCount = intval($numCount) + intval($upperCount) + intval($lowerCount) + intval($symCount);

            if(isset($validateArray['min_length']) && $minLength > 56)
            {
                //min length error validation //method call
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('min_length_err', 'minLength');
            }
            
            if(isset($validateArray['min_length']) && $totalCount > 0 && $totalCount > $minLength )
            {
                //total of count should be less then min length error validation //method call
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('min_length_err', 'totalLength');
            }

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'index', [ 'request' => $this -> request ]);
            } 
            else
            {
                $db_data = $this -> passwordPolicyModel -> getSinglePasswordPolicy(['where' => 'deleted_at IS NULL', 'params' => []]);

                $updateDataArray = array(
                    'min_length' => $this -> request -> input( 'min_length' ),
                    'num_cnt' => $this -> request -> input( 'num_cnt' ),
                    'uppercase_cnt' => $this -> request -> input( 'uppercase_cnt' ),
                    'lowercase_cnt' => $this -> request -> input( 'lowercase_cnt' ),
                    'symbol_cnt' => $this -> request -> input( 'symbol_cnt' ),
                    'admin_id' => Session::get('emp_id')
                );

                if(is_object($db_data))
                {
                    $result = $this -> passwordPolicyModel::update(
                        $this -> passwordPolicyModel -> getTableName(), 
                        $updateDataArray,[
                            'where' => 'id = :id',
                            'params' => [ 'id' => $this -> ppId]
                        ]
                    );
                }
                else
                {
                    $result = $this -> passwordPolicyModel::insert(
                        $this -> passwordPolicyModel -> getTableName(), 
                        $updateDataArray
                    );
                }

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                else
                {
                    $policyArray = array(
                        'password_policy' => 1,
                    );

                    $this -> employeeModel::update(
                        $this -> employeeModel -> getTableName(), 
                        $policyArray,[
                            'where' => ''
                        ]
                    );
                }

                //after insert data redirect to password policy dashboard
                Validation::flashErrorMsg('passwordPolicySuccess', 'success');
                Redirect::to( SiteUrls::getUrl('passwordPolicy') . '/update' );
            }
        });
    }
}

?>