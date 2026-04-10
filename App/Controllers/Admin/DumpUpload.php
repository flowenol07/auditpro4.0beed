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

class DumpUpload extends Controller  {

    public $me = null, $request, $data, $acctId, $upload_period_from, $upload_period_to, $dumpType;
    public $dumpDepositModel, $dumpAdvanceModel, $branchModel, $schemeModel;
    public $dbDataArray = array(), $dbSchemeArray = array(), $dbBranchArray = array();
    public $list_upload_data = array();

    public function __construct($me) {

        $this -> me = $me;
        
        // request object created
        $this -> request = new Request();

        //Search in Select 
        $this -> data['need_select'] = true;

        // find current models
        $this -> dumpDepositModel = $this -> model('DumpDepositeModel');   
        $this -> dumpAdvanceModel = $this -> model('DumpAdvancesModel');  

        //find all active branch
        $this -> branchModel = $this -> model('AuditUnitModel');
        $this -> schemeModel = $this -> model('SchemeModel'); 

        $this -> upload_period_from = date('Y-m-01');
        $this -> upload_period_to = date("Y-m-t", strtotime($this -> upload_period_from));
        
        //get all audit unit
        $this -> data['db_audit_unit'] = $this -> branchModel -> getAllAuditUnit([
            'where' => 'section_type_id = 1 AND is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_audit_unit'] = generate_array_for_select( $this -> data['db_audit_unit'], 'id', 'name');

        //get all scheme code
        $this -> data['db_deposit_scheme'] = $this -> schemeModel -> getAllSchemes([
            'where' => 'scheme_type_id = 1 AND is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_deposit_scheme'] = generate_array_for_select( $this -> data['db_deposit_scheme'], 'id', 'name');

        $this -> data['db_deposit_scheme_name_code'] = DBCommonFunc::getAllSchemeData($this -> schemeModel, ['where' => 'scheme_type_id = 1 AND is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_deposit_scheme_name_code'] = generate_data_assoc_array($this -> data['db_deposit_scheme_name_code'], 'id');
            
        // for advances
        $this -> data['db_advances_scheme'] = $this -> schemeModel -> getAllSchemes([
            'where' => 'scheme_type_id = 2 AND is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_advances_scheme'] = generate_array_for_select( $this -> data['db_advances_scheme'], 'id', 'name');

        $this -> data['db_advances_scheme_name_code'] = DBCommonFunc::getAllSchemeData($this -> schemeModel, ['where' => 'scheme_type_id = 2 AND is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_advances_scheme_name_code'] = generate_data_assoc_array($this -> data['db_advances_scheme_name_code'], 'id');
    }

    //function for generate key
    private function generateAccKey($acc_details, $dumpType = 1)
    {
        $str = string_operations($acc_details['branch_code'] . '_' . $acc_details['scheme_code'] . '_' . $acc_details['account_no'] . '_' . str_replace('-', '', $acc_details['account_opening_date']));

        if($dumpType == 2 && !empty($acc_details['renewal_date']))
            $str .= '_' . str_replace('-', '', $acc_details['renewal_date']);

        //return data
        return $str;
    }

    //function for check validations
    private function validateUploadedData($c_key, $cc_data, $dumpType)
    {
        if($cc_data['branch_code'] == '' || !is_array($this -> dbBranchArray))
            $cc_data['error']['branch_code'] = 'Error: Empty Branch Code';

        //check branch code exists
        if( is_array($this -> dbBranchArray) )
        { 
            if(!array_key_exists($cc_data['branch_code'], $this -> dbBranchArray) || 
                $this -> dbBranchArray[ $cc_data['branch_code'] ] -> is_active != 1)
                $cc_data['error']['branch_code'] = 'Error: Branch Code Not Exists';
            else //push id
                $cc_data['branch_id'] = $this -> dbBranchArray[ $cc_data['branch_code'] ] -> id;
        }

        // -------------------------------------------

        //check scheme code
        if($cc_data['scheme_code'] == '' || !is_array($this -> dbSchemeArray))
            $cc_data['error']['scheme_code'] = 'Error: Empty Scheme Code';

        if( is_array($this -> dbSchemeArray) )
        {
            if(!array_key_exists($cc_data['scheme_code'], $this -> dbSchemeArray) || 
                $this -> dbSchemeArray[ $cc_data['scheme_code'] ] -> is_active != 1)
                $cc_data['error']['scheme_code'] = 'Error: Scheme Code Not Exists';
            else //push id
                $cc_data['scheme_id'] = $this -> dbSchemeArray[ $cc_data['scheme_code'] ] -> id;
        }

        // -------------------------------------------

        // account_no
        if($cc_data[ 'account_no' ] == '')
            $cc_data['error'][ 'account_no' ] = 'Error: Empty Account Number';

        // account_holder_name
        if($cc_data[ 'account_holder_name' ] == '')
            $cc_data['error'][ 'account_holder_name' ] = 'Error: Empty Account Holder Name';

        $isRenewal = false;

        //for renewal date
        if( $dumpType == 2 && $cc_data[ 'renewal_date' ] != '' && 
            !preg_match(Validation::$regexArray['dateRegex'], $cc_data[ 'renewal_date' ]))
            $cc_data['error'][ 'renewal_date' ] = 'Error: Enter valid date format (YYYY-MM-DD)';
        else if( $dumpType == 2 && !empty($cc_data['renewal_date']) && preg_match(Validation::$regexArray['dateRegex'], $cc_data[ 'renewal_date' ]))
        {
            $renewal_date = /*date('Y-m-d',*/ strtotime($cc_data[ 'renewal_date' ]) /*)*/;
            $upload_period_from = /*date('Y-m-d',*/ strtotime($cc_data['upload_period_from']) /*)*/;
            $upload_period_to = /*date('Y-m-d',*/ strtotime($cc_data['upload_period_to']) /*)*/;

            if (($renewal_date < $upload_period_from) || ($renewal_date > $upload_period_to))
                $cc_data['error'][ 'account_opening_date' ] = 'Error: Account renewal date ('. $cc_data[ 'account_opening_date' ] .') is not between ' . $cc_data['upload_period_from'] . ' - ' . $cc_data['upload_period_to'] ;
            else
                $isRenewal = true;
        }
        
        // account_opening_date
        if( $cc_data[ 'account_opening_date' ] == '' || 
            !preg_match(Validation::$regexArray['dateRegex'], $cc_data[ 'account_opening_date' ]))
            $cc_data['error'][ 'account_opening_date' ] = 'Error: Enter valid date format (YYYY-MM-DD)';
        else
        {
            if($dumpType == 1 || !$isRenewal)
            {
                $acc_open_date = /*date('Y-m-d',*/ strtotime($cc_data[ 'account_opening_date' ]) /*)*/;
                $upload_period_from = /*date('Y-m-d',*/ strtotime($cc_data['upload_period_from']) /*)*/;
                $upload_period_to = /*date('Y-m-d',*/ strtotime($cc_data['upload_period_to']) /*)*/;

                if (($acc_open_date < $upload_period_from) || ($acc_open_date > $upload_period_to))
                    $cc_data['error'][ 'account_opening_date' ] = 'Error: Account open date ('. $cc_data[ 'account_opening_date' ] .') is not between ' . $cc_data['upload_period_from'] . ' - ' . $cc_data['upload_period_to'] ;
            }
        }        

        //check duplicate account number
        if(is_array($this -> dbDataArray) && array_key_exists($c_key, $this -> dbDataArray))
            $cc_data['error'][ 'account_no' ] = 'Error: Duplicate account details exists';            

        return $cc_data;
    }

    //find db data
    private function getAllDBData($dumpType)
    {
        //find all active branch
        $this -> dbBranchArray = $this -> branchModel -> getAllAuditUnit(['where' => 'section_type_id = 1 AND deleted_at IS NULL']);

        //helper function call
        $tempDbBranchArray = generate_data_assoc_array($this -> dbBranchArray, 'id');
        $this -> dbBranchArray = generate_data_assoc_array($this -> dbBranchArray, 'audit_unit_code');

        if($dumpType == 2) 
        {
            //for advance
            $dbDataArray = $this -> dumpAdvanceModel -> getAllAccounts([ "where" => "deleted_at IS NULL" ]);

            //find all active schemes
            $this -> dbSchemeArray = $this -> schemeModel -> getAllSchemes([
                'where' => 'scheme_type_id = 2 AND deleted_at IS NULL'
            ]);
        }
        else 
        {
            //for deposit
            $dbDataArray = $this -> dumpDepositModel -> getAllAccounts([ "where" => "deleted_at IS NULL" ]);

            //find all active schemes
            $this -> dbSchemeArray = $this -> schemeModel -> getAllSchemes([
                'where' => 'scheme_type_id = 1 AND deleted_at IS NULL'
            ]);
        }

        //helper function call
        $tempDbSchemeArray = generate_data_assoc_array($this -> dbSchemeArray, 'id');
        $this -> dbSchemeArray = generate_data_assoc_array($this -> dbSchemeArray, 'scheme_code');

        if(is_array($dbDataArray) > 0)
        {
            foreach($dbDataArray as $cAccDetails)
            {
                $c_acc_details = array(
                    'id'                => trim_str($cAccDetails -> id),
                    'branch_id'         => trim_str($cAccDetails -> branch_id),
                    'branch_code'       => null,
                    'scheme_id'         => trim_str($cAccDetails -> scheme_id),
                    'scheme_code'       => null,
                    'account_no'        => trim_str($cAccDetails -> account_no),
                    'ucic'              => trim_str($cAccDetails -> ucic),
                    'account_opening_date' => trim_str($cAccDetails -> account_opening_date),
                    'upload_period_from' => trim_str($cAccDetails -> upload_period_from),
                    'upload_period_to'  => trim_str($cAccDetails -> upload_period_to),
                    'upload_date'       => trim_str($cAccDetails -> upload_date),
                );

                if($dumpType == 2) //for advances
                    $c_acc_details['renewal_date'] = trim_str($cAccDetails -> renewal_date);

                //check branch code exists //push unit code
                if( is_array($tempDbBranchArray) && array_key_exists($c_acc_details['branch_id'], $tempDbBranchArray))
                    $c_acc_details['branch_code'] = $tempDbBranchArray[ $c_acc_details['branch_id'] ] -> audit_unit_code;

                //check scheme exists //push scheme code
                if( is_array($tempDbSchemeArray) && array_key_exists($c_acc_details['scheme_id'], $tempDbSchemeArray))
                    $c_acc_details['scheme_code'] = $tempDbSchemeArray[ $c_acc_details['scheme_id'] ] -> scheme_code;

                $c_key = $this -> generateAccKey($c_acc_details, $dumpType);

                $this -> dbDataArray[ $c_key ] = $c_acc_details['account_opening_date'];

                if( !is_array($this -> list_upload_data) || 
                    !array_key_exists($c_acc_details['upload_date'], $this -> list_upload_data) )
                    $this -> list_upload_data[ $c_acc_details['upload_date'] ] = [
                        "upload_period_from" => $c_acc_details['upload_period_from'],
                        "upload_period_to"   => $c_acc_details['upload_period_to'],
                        "upload_date"        => $c_acc_details['upload_date']
                    ];
            }
        }

        //unset vars
        unset($tempDbBranchArray, $tempDbSchemeArray);
    }

    private function commonCodeDumpUpload($dumpType = 1)
    {
        $this -> dumpType = $dumpType;
        $this -> data['need_calender'] = true;

        $this -> data['db_data'] = (object) [];
        $this -> data['db_data'] -> upload_date = date($GLOBALS['dateSupportArray'][1]);
        $this -> data['db_data'] -> upload_period_from = $this -> upload_period_from;
        $this -> data['db_data'] -> upload_period_to = $this -> upload_period_to;

        //sample csv
        $this -> data['sample_csv'] = URL . 'docs/sample/' . (($dumpType == 2) ? 'sample-csv-advances.csv' : 'sample-csv-deposites.csv');

        //method call 
        $this -> getAllDBData($dumpType);

        $this -> data['list_upload_data'] = null;
        $this -> data['last_upload_data'] = null;

        if(is_array($this -> list_upload_data) && sizeof($this -> list_upload_data) > 0)
        {
            $this -> data['list_upload_data'] = $this -> list_upload_data;
            arsort( $this -> data['list_upload_data'] );
            
            $this -> data['last_upload_data'] = current($this -> data['list_upload_data']);
        }

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'dump-upload-form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            $validationArray = [
                'upload_date' => 'required|regex[alphaNumricRegex, dateError]',
                // 'upload_period_from' => 'required|regex[alphaNumricRegex, dateError]',
                // 'upload_period_to' => 'required|regex[alphaNumricRegex, dateError]',
                'csv_file_upload' => 'file_upload[csv]'
            ];

            $notiObj = new Notifications;

            $validationArray = array_merge($validationArray, date_validation_helper($this -> request, $validationArray, $notiObj, ['upload_period_from', 'upload_period_to'])['validation']);

            Validation::validateData($this -> request, $validationArray);

            $upload_period_from =  $this -> request -> input('upload_period_from');
            $upload_period_to =  $this -> request -> input('upload_period_to');

            $csv_data = array();
            $error_data = array();
            $col_mismatch = FALSE;

            if( !$this -> request -> input( 'error' ) > 0 && !(strtotime($upload_period_to) > strtotime($upload_period_from)) ) {
                
                //date error
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('upload_period_to_err', 'dateGratorError');
            }
    
            //validation check
            if(!$this -> request -> input( 'error' ) > 0) 
            {
                $row = 1;
                $uploadDumpKey = generateUniqueKey(); //helper function call

                if (($handle = fopen($_FILES['csv_file_upload']['tmp_name'], "r")) !== FALSE)
                {
                    while (($c_data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                        if(sizeof($c_data) != 17)
                        {
                          $col_mismatch = true;
                          break;
                        }
                
                        if($row > 1)
                        {
                            if($this -> dumpType == 2)
                            {
                                $cc_data = array(
                                    'branch_id'         => null,
                                    'branch_code'       => trim_str($c_data[0]), 
                                    'branch_name'       => trim_str($c_data[1]),
                                    'scheme_id'         => null,
                                    'scheme_code'       => trim_str($c_data[2]),
                                    'scheme_name'       => trim_str($c_data[3]),
                                    'account_no'        => trim_str($c_data[4]),
                                    'account_holder_name' => string_operations($c_data[5], 'upper'),
                                    'ucic'              => string_operations($c_data[6], 'upper'),
                                    'customer_type'     => string_operations($c_data[7], 'upper'),
                                    'account_opening_date' => trim_str($c_data[8]),
                                    'renewal_date'      => get_convert_date_format($c_data[9]),
                                    'sanction_amount'   => get_decimal(trim_str($c_data[10]), 2),
                                    'intrest_rate'      => get_decimal(trim_str($c_data[11]), 2),
                                    'due_date'          => get_convert_date_format($c_data[12]),
                                    'outstanding_balance' => get_decimal(trim_str($c_data[13]), 2),
                                    'balance_date'      => get_convert_date_format($c_data[14]),
                                    'npa_status'        => string_operations($c_data[15], 'upper'),
                                    'account_status'    => string_operations($c_data[16], 'upper')
                                );
                            }
                            else
                            {
                                $cc_data = array(
                                    'branch_id'         => null,
                                    'branch_code'       => trim_str($c_data[0]),
                                    'branch_name'       => trim_str($c_data[1]),
                                    'scheme_id'         => null,
                                    'scheme_code'       => trim_str($c_data[2]),
                                    'scheme_name'       => trim_str($c_data[3]),
                                    'account_no'        => trim_str($c_data[4]),
                                    'account_holder_name' => string_operations($c_data[5], 'upper'),
                                    'ucic'              => string_operations($c_data[6], 'upper'),
                                    'customer_type'     => string_operations($c_data[7], 'upper'),
                                    'intrest_rate'      => get_decimal(trim_str($c_data[8]), 2),
                                    'principal_amount'  => get_decimal(trim_str($c_data[9]), 2),
                                    'account_opening_date' => trim_str($c_data[10]),
                                    'balance'           => get_decimal(trim_str($c_data[11]), 2),
                                    'balance_date'      => get_convert_date_format($c_data[12]),
                                    'maturity_date'     => get_convert_date_format($c_data[13]),
                                    'maturity_amount'   => get_decimal(trim_str($c_data[14]), 2),
                                    'close_date'        => get_convert_date_format($c_data[15]),
                                    'account_status'    => string_operations($c_data[16], 'upper'),
                                );
                            }

                            $cc_data = array_merge($cc_data, [
                                'upload_date'           => $this -> request -> input('upload_date') . ' ' . date('H:i:s'),
                                'upload_period_from'    => $this -> request -> input('upload_period_from'),
                                'upload_period_to'      => $this -> request -> input('upload_period_to'),
                                'upload_key'            => $uploadDumpKey,
                                'sampling_filter'       => 0,
                                'assesment_period_id'   => 0,
                                'admin_id'              => Session::get('emp_id'),
                                'error'                 => array()
                            ]);

                          $c_key = $this -> generateAccKey($cc_data, $this -> dumpType);
                
                          //function call
                          $cc_data = $this -> validateUploadedData($c_key, $cc_data, $this -> dumpType);

                          //push data in array
                          $csv_data[ $c_key ] = $cc_data;
                
                          if(is_array($cc_data['error']) && sizeof($cc_data['error']) > 0)
                            $error_data[ $c_key ] = $cc_data;
                        }
                
                        $row++;        
                    }

                }

                // print_r($this -> dbDataArray);

                //file close
                fclose($handle);
            }

            if(!$this -> request -> input( 'error' ) > 0) 
            {
                if(!is_array($csv_data) || !sizeof($csv_data) > 0)
                {
                    //date error
                    Validation::incrementError($this -> request);
                    $this -> request -> setInputCustom('upload_period_to_err', 'csvNoData');
                }

                if(is_array($error_data) && sizeof($error_data) > 0 || $col_mismatch)
                {
                    //date error
                    Validation::incrementError($this -> request);
                    $this -> request -> setInputCustom('upload_period_to_err', 'errAccount');

                    $this -> data['csv_data'] = sizeof($csv_data);
                    $this -> data['err_acc_data'] = $error_data;
                    unset($error_data);
                }
            }

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                return return2View($this, $this -> me -> viewDir . 'dump-upload-form', [ 'request' => $this -> request ]);
            } 
            else 
            {
                // echo '<pre>';
                // print_r($csv_data);

                if($this -> dumpType == 2) //for advances
                $res = $this -> dumpAdvanceModel -> accInsertBulkData( $this -> dumpAdvanceModel -> getTableName(), $csv_data, $this -> dumpType);
            else
                $res = $this -> dumpDepositModel -> accInsertBulkData( $this -> dumpDepositModel -> getTableName(), $csv_data, $this -> dumpType);

            if(!$res) {
                Notifications::getNoti('somethingWrong');
            } else {
                // Calculate scheme-wise counts
                $schemeCounts = [];
                foreach($csv_data as $account) {
                    $schemeCode = $account['scheme_code'];
                    $schemeName = $account['scheme_name'] ?? $schemeCode;
                    if(!isset($schemeCounts[$schemeCode])) {
                        $schemeCounts[$schemeCode] = [
                            'name' => $schemeName,
                            'count' => 0
                        ];
                    }
                    $schemeCounts[$schemeCode]['count']++;
                }
                
                // Build summary HTML
                $summaryHtml = '<div class="upload-summary">';
                $summaryHtml .= '<h5 class="text-success mb-3">Upload Summary</h5>';
                $summaryHtml .= '<div class="row mb-2">';
                $summaryHtml .= '<div class="col-md-4"><strong>Total Accounts Uploaded:</strong> <span class="badge bg-success">' . count($csv_data) . '</span></div>';
                $summaryHtml .= '<div class="col-md-4"><strong>Upload Date:</strong> ' . $this->request->input('upload_date') . '</div>';
                $summaryHtml .= '<div class="col-md-4"><strong>Period:</strong> ' . $this->request->input('upload_period_from') . ' TO ' . $this->request->input('upload_period_to') . '</div>';
                $summaryHtml .= '</div>';
                
                if(!empty($schemeCounts)) {
                    $summaryHtml .= '<div class="mt-3">';
                    $summaryHtml .= '<h6>Scheme-wise Distribution:</h6>';
                    $summaryHtml .= '<table class="table table-sm table-bordered" style="max-width: 500px;">';
                    $summaryHtml .= '<thead><tr><th>Scheme Code</th><th>Scheme Name</th><th>Count</th></tr></thead>';
                    $summaryHtml .= '<tbody>';
                    
                    foreach($schemeCounts as $schemeCode => $schemeData) {
                        $summaryHtml .= '<tr>';
                        $summaryHtml .= '<td>' . $schemeCode . '</td>';
                        $summaryHtml .= '<td>' . $schemeData['name'] . '</td>';
                        $summaryHtml .= '<td class="text-center">' . $schemeData['count'] . '</td>';
                        $summaryHtml .= '</tr>';
                    }
                    
                    $summaryHtml .= '</tbody></table></div>';
                }
                $summaryHtml .= '</div>';
                
                // Store summary in session flash
                Session::flash('upload_summary', $summaryHtml);
            }

            //after insert data redirect to scheme
            Validation::flashErrorMsg('dataUploadedSuccess', 'success');

            $redirect = ($this -> dumpType == 2) ? 'bulkUploadAdvance' : 'bulkUploadDeposit';
            Redirect::to( SiteUrls::getUrl($redirect) );  
            }
            
        });
    }

    //type = 2 //Advances
    public function dumpUploadAdvance() 
    {

        //method call
        $this -> commonCodeDumpUpload(2);

    }

    //type = 1 //Deposits
    public function dumpUploadDeposit() 
    {

        //method call
        $this -> commonCodeDumpUpload();

    }

    private function commonCodeManageAccounts($dumpType = 1)
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('manageAccountsDeposits') . '/addDeposit' ],
        ];

        if($dumpType == 2) //for advances
            $this -> data['topBtnArr']['add'] =  [ 'href' => SiteUrls::getUrl('manageAccountsAdvances') . '/addAdvances' ];

        $this -> data['filter_array'] = array(
            'all' => 'ALL ACCOUNTS',
            '1' => 'Search By Account Number',
            '2' => 'Search By Account Holder Name',
            '3' => 'Search By Customer UCIC',
        );

        $this -> data['db_data'] = (object) [];
        $this -> data['need_calender'] = true;
        $this -> data['db_data'] -> upload_period_from = $this -> upload_period_from;
        $this -> data['db_data'] -> upload_period_to = $this -> upload_period_to;

        $db_scheme_data = $this -> schemeModel -> getAllSchemes([
            'where' => 'scheme_type_id = "'. $dumpType .'" AND is_active = 1 AND deleted_at IS NULL'], 'sql', 'SELECT id, CONCAT(name, " ( ", scheme_code, " ) ") AS combined_scheme FROM ' . $this -> schemeModel -> getTableName());

        $this -> data['db_scheme_data_arr'] = generate_data_assoc_array($db_scheme_data, 'id');
        $db_scheme_data = generate_array_for_select($db_scheme_data, 'id', 'combined_scheme');
        $this -> data['db_scheme_data'] = array_replace(['all' => 'ALL SCHEMES'], $db_scheme_data);
        unset($db_scheme_data);

        $db_audit_unit_data = $this -> branchModel -> getAllAuditUnit([
            'where' => 'section_type_id = 1 AND is_active = 1 AND deleted_at IS NULL'], 'sql', 'SELECT id, CONCAT(name, " ( ", audit_unit_code, " ) ") AS combined_branch_name FROM ' . $this -> branchModel -> getTableName());

        $this -> data['db_audit_unit_data_arr'] = generate_data_assoc_array($db_audit_unit_data, 'id');
        $db_audit_unit_data = generate_array_for_select($db_audit_unit_data, 'id', 'combined_branch_name');
        $this -> data['db_audit_unit_data'] = array_replace(['all' => 'ALL AUDIT UNITS'], $db_audit_unit_data);
        
        unset($db_audit_unit_data);

        if( $this -> request -> has('filter') &&
            $this -> request -> has('filter_text') &&
            $this -> request -> has('audit_unit') &&
            $this -> request -> has('scheme') &&
            $this -> request -> has('period_from') &&
            $this -> request -> has('period_to')
        )
        {
            //has get data //validate
            $validateArray = [
                'filter' => 'array_key[filter_array, filterError]',
                'audit_unit' => 'array_key[audit_unit_array, audit_id]',
                'scheme' => 'array_key[scheme_array, schemeError]',
                'period_from' => 'required',
                'period_to' => 'required'
            ];

            if($this -> request -> input('filter') != 'all')
                $validateArray['filter_text'] = 'required';

            Validation::validateData($this -> request, $validateArray,[
                'filter_array'      => $this -> data['filter_array'],
                'audit_unit_array'  => $this -> data['db_audit_unit_data'],
                'scheme_array'      => $this -> data['db_scheme_data']
            ]);

            $notiObj = new Notifications;

            // $validateArray = array_merge($validateArray, date_validation_helper($this -> request, $validateArray, $notiObj)['validation']);
    
            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {
                Validation::flashErrorMsg();
            }
            else
            {
                //all validation ok find data
                $filter = [
                    'where' => 'deleted_at IS NULL',
                    'params' => []
                ];

                $temp_where = '';

                switch($this -> request -> input('filter'))
                {
                    case '1' : { 
                        $temp_where .= ' AND account_no LIKE :filter_text';
                        break;
                    }

                    case '2' : { 
                        $temp_where .= ' AND account_holder_name LIKE :filter_text';
                        break;
                    }

                    case '3' : { 
                        $temp_where .= ' AND ucic LIKE :filter_text';
                        break;
                    }
                }

                if(!empty($temp_where))
                {
                    $filter['where'] .= $temp_where;
                    $filter['params']['filter_text'] = '%' . string_operations($this -> request -> input('filter_text')) . '%';
                }

                //for audit units
                if(!empty($this -> request -> input('audit_unit')) && $this -> request -> input('audit_unit') != 'all')
                {
                    $filter['where'] .= ' AND branch_id = :branch_id';
                    $filter['params']['branch_id'] = $this -> request -> input('audit_unit');
                }

                //for scheme
                if(!empty($this -> request -> input('scheme')) && $this -> request -> input('scheme') != 'all')
                {
                    $filter['where'] .= ' AND scheme_id = :scheme_id';
                    $filter['params']['scheme_id'] = $this -> request -> input('scheme');
                }
                
                if($dumpType == 2)
                {
                    $filter['where'] .= ' AND ((account_opening_date BETWEEN :period_from AND :period_to) OR (renewal_date BETWEEN :period_from AND :period_to)) LIMIT 100';
                }
                else
                    $filter['where'] .= ' AND (account_opening_date BETWEEN :period_from AND :period_to) LIMIT 100';

                
                $filter['params']['period_from'] = $this -> request -> input('period_from');
                $filter['params']['period_to'] = $this -> request -> input('period_to');

                // print_r($filter);

                //find data
                    //for advances
                if($dumpType == 2)
                {
                    $this -> data['db_acc_data'] = $this -> dumpAdvanceModel -> getAllAccounts($filter);

                    $this -> data['dumpType'] = array(2);
                }
                else
                {
                    $this -> data['db_acc_data'] = $this -> dumpDepositModel -> getAllAccounts($filter);

                    $this -> data['dumpType'] = array(1);
                }
            }
        }


        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'sort-dump-form', [ 'request' => $this -> request ]);

        });
    }

    //type = 2 //Advances
    public function manageAccountsAdvances() 
    {

        //method call
        $this -> commonCodeManageAccounts(2);

    }

    //type = 1 //Deposits
    public function manageAccountsDeposits() 
    {

        //method call
        $this -> commonCodeManageAccounts();

    }

    private function validateData($acctId = '', $dumpType = 1)
    {
        if($dumpType == 2)
            $model = $this -> dumpAdvanceModel;
        else
            $model = $this -> dumpDepositModel;


        if($dumpType == 2)
        {
            $uniqueWhere = [
                'model' => $model,
                'where' => 'account_no = :account_no AND deleted_at IS NULL',
                'params' => [
                    'account_no' => $this -> request -> input('account_no'),
                ]
            ];
    
            if(!empty($acctId))
            {
                $uniqueWhere['where'] .= ' AND id != :id';
                $uniqueWhere['params']['id'] = $acctId;
            }

            $validateArray = array(
                'branch_id' => 'required|array_key[branch_id_array, schemeType]',
                'scheme_id' => 'required|array_key[scheme_types_array, schemeType]',
                'account_holder_name' => 'required|regex[alphaNumricRegex, accountHolderName]',
                'ucic' => 'required|regex[numberRegex, customerId]',
                'customer_type' => 'required|regex[alphaNumricRegex, customer_type]',
                'sanction_amount' => 'required|regex[floatNumberRegex, amount]',
                'intrest_rate' => 'required|regex[floatNumberRegex, intrestRate]',
                'due_date' => 'required|regex[dateRegex, dateError]',
                'outstanding_balance' => 'required|regex[floatNumberRegex, amount]',
                'balance_date' => 'required|regex[dateRegex, dateError]',
                'npa_status' => 'required|regex[alphaNumericSymbolsRegex, status]',
                'account_status' => 'required|regex[alphaNumricRegex, status]',
                'upload_period_from' => 'required|regex[dateRegex, dateError]',
                'upload_period_to' => 'required|regex[dateRegex, dateError]',
            );

            if(($this -> request -> input('renewal_date')) == "")
                $validateArray['account_opening_date'] = 'required|regex[dateRegex, dateError]';
            elseif(($this -> request -> input('renewal_date')) != "")
            {
                $validateArray['account_opening_date'] = 'required|regex[dateRegex, dateError]';
                $validateArray['renewal_date'] = 'regex[dateRegex, dateError]';
            }

            if(($this -> request -> input('account_opening_date')) == ($this -> request -> input('renewal_date')))
            {
                $varValidateArray = [
                    'branch_id_array' => $this -> data['db_audit_unit'],
                    'scheme_types_array' => $this -> data['db_advances_scheme'],
                    'unique_data' => $uniqueWhere,
                ];

                $validateArray['account_no'] = 'required|regex[alphaNumericSymbolsRegex, accountNo]|is_unique[unique_data, accountDuplicate]';
            }
            else
            {
                $varValidateArray = [
                    'branch_id_array' => $this -> data['db_audit_unit'],
                    'scheme_types_array' => $this -> data['db_advances_scheme'],
                ];

                $validateArray['account_no'] = 'required|regex[alphaNumericSymbolsRegex, accountNo]';
            }
            
            Validation::validateData($this -> request, $validateArray, $varValidateArray);
        }
        else
        {
            $uniqueWhere = [
                'model' => $model,
                'where' => 'account_no = :account_no AND deleted_at IS NULL',
                'params' => [ 
                    'account_no' => $this -> request -> input('account_no'), 
                ]
            ];
    
            if(!empty($acctId))
            {
                $uniqueWhere['where'] .= ' AND id != :id';
                $uniqueWhere['params']['id'] = $acctId;
            }

            $validateArray = array(
                'branch_id' => 'required|array_key[branch_id_array, schemeType]',
                'scheme_id' => 'required|array_key[scheme_types_array, schemeType]',
                'account_no' => 'required|regex[alphaNumericSymbolsRegex, accountNo]|is_unique[unique_data, accountDuplicate]',
                'account_holder_name' => 'required|regex[alphaNumricRegex, accountHolderName]',
                'ucic' => 'required|regex[numberRegex, customerId]',
                'customer_type' => 'required|regex[alphaNumricRegex, customer_type]',
                'intrest_rate' => 'required|regex[floatNumberRegex, intrestRate]',
                'principal_amount' => 'required|regex[floatNumberRegex, amount]',
                'account_opening_date' => 'required|regex[dateRegex, dateError]',
                'balance' => 'required|regex[floatNumberRegex, amount]',
                'balance_date' => 'required|regex[dateRegex, dateError]',
                'maturity_date' => 'required|regex[dateRegex, dateError]',
                'maturity_amount' => 'required|regex[floatNumberRegex, amount]',
                'close_date' => 'regex[dateRegex, dateError]',
                'account_status' => 'required|regex[alphaNumricRegex, status]',
                'upload_period_from' => 'required|regex[dateRegex, dateError]',
                'upload_period_to' => 'required|regex[dateRegex, dateError]',
            );

            Validation::validateData($this -> request, $validateArray,[
                'branch_id_array' => $this -> data['db_audit_unit'],
                'scheme_types_array' => $this -> data['db_deposit_scheme'],
                'unique_data' => $uniqueWhere,
            ]);
        }

        $acctOpenDate = get_convert_date_format($this -> request -> input('account_opening_date'));

        $uploadDateFrom = get_convert_date_format($this -> request -> input('upload_period_from'));

        $uploadToDate = get_convert_date_format($this -> request -> input('upload_period_to'));

        if($acctOpenDate >= $uploadDateFrom && $acctOpenDate <= $uploadToDate)
            $isBetween = true;
        else
            $isBetween = false;

        if($dumpType == 2)
        {
            // account open date should be between upload from to upload to          
            $renewalDate = get_convert_date_format($this -> request -> input('renewal_date'));           

            if($renewalDate >= $uploadDateFrom && $renewalDate <= $uploadToDate)
                $isRenewalBetween = true;
            else
                $isRenewalBetween = false;

            if(isset($validateArray['account_opening_date']) && $isBetween == false  && $this -> request -> input('account_opening_date') != '')
            {
                if(isset($validateArray['renewal_date']) && $isRenewalBetween == false)
                {
                    //acoount open date not found //method call
                    Validation::incrementError($this -> request);
                    $this -> request -> setInputCustom('renewal_date_err', 'renewalDateBetween');
                }
                elseif((isset($validateArray['renewal_date']) && $isRenewalBetween == false) && (isset($validateArray['account_opening_date']) && $isBetween == false))
                {
                    //acoount open date not found //method call
                    Validation::incrementError($this -> request);
                    $this -> request -> setInputCustom('account_opening_date_err', 'accountOpenDateBetween');
                }
                elseif((!isset($validateArray['renewal_date']) || $validateArray['renewal_date'] == '')  && (isset($validateArray['account_opening_date']) && $isBetween == false))
                {
                    //acoount open date not found //method call
                    Validation::incrementError($this -> request);
                    $this -> request -> setInputCustom('account_opening_date_err', 'accountOpenDateBetween');
                }
            }

            if(isset($validateArray['renewal_date']) && $isRenewalBetween == false)
            {
                //acoount open date not found //method call
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('renewal_date_err', 'renewalDateBetween');
            }
        }
        else
        {
            if(isset($validateArray['account_opening_date']) && $isBetween == false && $this -> request -> input('account_opening_date') != '')
            {
                //acoount open date not found //method call
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('account_opening_date_err', 'accountOpenDateBetween');
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

    private function postArray($dumpType = 1)
    {
        $uploadDumpKey = generateUniqueKey(); //helper function call

        $dataCommon = array(
            'branch_id' => $this -> request -> input('branch_id'),
            'scheme_id' => $this -> request -> input('scheme_id'),
            'account_no' => $this -> request -> input('account_no'),
            'account_holder_name' => string_operations($this -> request -> input( 'account_holder_name' ), 'upper'),
            'ucic' => $this -> request -> input('ucic'),
            'customer_type' => $this -> request -> input('customer_type'),
            'intrest_rate' => get_decimal($this -> request -> input('intrest_rate'), 2),
            'account_opening_date' => get_convert_date_format($this -> request -> input('account_opening_date')),

            'balance_date' => get_convert_date_format($this -> request -> input('balance_date')),

            'account_status' => string_operations($this -> request -> input('account_status'), 'upper'),

            'upload_date' => date($GLOBALS['dateSupportArray'][2]),

            'upload_period_from' => get_convert_date_format($this -> request -> input('upload_period_from')),

            'upload_period_to' => get_convert_date_format($this -> request -> input('upload_period_to')),

            'upload_key' => $uploadDumpKey,

            'admin_id' => Session::get('emp_id'),
        );

        $dataDeposit = array(
            'principal_amount' => get_decimal($this -> request -> input('principal_amount'), 2),
            'balance' => get_decimal($this -> request -> input('balance'), 2),
            'maturity_date' => get_convert_date_format($this -> request -> input('maturity_date')),
            'maturity_amount' => get_decimal($this -> request -> input('maturity_amount'), 2),
            'close_date' => get_convert_date_format($this -> request -> input('close_date')),
        );

        $dataAdvances = array(
            'renewal_date' => get_convert_date_format($this -> request -> input('renewal_date')),
            'sanction_amount' => get_decimal($this -> request -> input('sanction_amount'), 2),
            'due_date' => get_convert_date_format($this -> request -> input('due_date')),
            'outstanding_balance' => get_decimal($this -> request -> input('outstanding_balance'), 2),              
            'npa_status' => string_operations($this -> request -> input('npa_status'), 'upper'),
            
        );

        if($dumpType == 2)
            $dataArray = array_merge($dataCommon, $dataAdvances);
        else
            $dataArray = array_merge($dataCommon, $dataDeposit);

        return $dataArray;
    }

    public function addDeposit() 
    {        
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/addDeposit');
        $this -> me -> pageHeading = 'Add Account of Deposits';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> dumpDepositModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';
        $this -> data['need_calender'] = true;
        $this -> data['need_select'] = true;
        $this -> data['db_data'] -> upload_period_from = $this -> upload_period_from;
        $this -> data['db_data'] -> upload_period_to = $this -> upload_period_to;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('manageAccountsDeposits') ],
        ];

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form_deposit', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('', 1))
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_deposit', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> dumpDepositModel::insert(
                    $this -> dumpDepositModel -> getTableName(), 
                    $this -> postArray(1) //method call
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //after insert data redirect to deposit dump dashboard
                Validation::flashErrorMsg('accountAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('manageAccountsDeposits') );
            }
        });
    }

    public function addAdvances() 
    {        
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/addAdvances');
        $this -> me -> pageHeading = 'Add Account of Advnaces';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> dumpAdvanceModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';
        $this -> data['need_calender'] = true;
        $this -> data['need_select'] = true;
        $this -> data['db_data'] -> upload_period_from = $this -> upload_period_from;
        $this -> data['db_data'] -> upload_period_to = $this -> upload_period_to;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('manageAccountsAdvances') ],
        ];

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form_advances', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('', 2))
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_advances', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> dumpAdvanceModel::insert(
                    $this -> dumpAdvanceModel -> getTableName(), 
                    $this -> postArray(2) //method call
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //after insert data redirect to advances dump dashboard
                Validation::flashErrorMsg('accountAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('manageAccountsAdvances') );
            }
        });
    }

    public function updateDeposit($getRequest) 
    {

        $this -> acctId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/updateDeposit/' . encrypt_ex_data($this -> acctId));
        $this -> me -> pageHeading = 'Update Account of Deposit';

        $this -> data['need_calender'] = true;
        $this -> data['need_select'] = true;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('manageAccountsDeposits') ],
        ];

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> acctId, 1);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form_deposit', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData($this -> acctId, 1))
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_deposit', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> dumpDepositModel::update($this -> dumpDepositModel -> getTableName(), 
                    $this -> postArray(1), 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> acctId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('errorSaving') );

                //after insert data redirect to deposit dump dashboard
                Validation::flashErrorMsg('accountUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('manageAccountsDeposits') );
            }
        });
    }

    public function updateAdvances($getRequest) 
    {

        $this -> acctId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/updateAdvances/' . encrypt_ex_data($this -> acctId));
        $this -> me -> pageHeading = 'Update Account of Deposit';

        $this -> data['need_calender'] = true;
        $this -> data['need_select'] = true;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('manageAccountsAdvances') ],
        ];

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> acctId, 2);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form_advances', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData($this -> acctId, 2))
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_advances', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> dumpAdvanceModel::update($this -> dumpAdvanceModel -> getTableName(), 
                    $this -> postArray(2), 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> acctId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('errorSaving') );

                //after insert data redirect to advances dump dashboard
                Validation::flashErrorMsg('accountUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('manageAccountsAdvances') );
            }
        });
    }

    public function deleteDeposit($getRequest) 
    {

        $this -> acctId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> acctId, 1, 3) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> dumpDepositModel::delete(
            $this -> dumpDepositModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> acctId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to deposit dump dashboard
        Validation::flashErrorMsg('accountDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('manageAccountsDeposits') );
    }

    public function deleteAdvances($getRequest) 
    {

        $this -> acctId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> acctId, 2, 3) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> dumpAdvanceModel::delete(
            $this -> dumpAdvanceModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> acctId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to advances dump dashboard
        Validation::flashErrorMsg('accountDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('manageAccountsAdvances') );
    }

    private function getDataOr404($acctId, $dumpType, $optional = null) 
    {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $acctId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        elseif($optional == 3)
            $filter['where'] = 'id = :id AND sampling_filter = 0 AND assesment_period_id = 0 AND deleted_at IS NULL';

        // get data
        if($dumpType == 1)
            $this -> data['db_data'] = $this -> dumpDepositModel -> getSingleAccount($filter);
        elseif($dumpType == 2)
            $this -> data['db_data'] = $this -> dumpAdvanceModel -> getSingleAccount($filter);

        if(empty($acctId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
    public function deleteLastUploadDeposit() 
    {
        return $this->deleteLastUpload(1); // 1 for deposits
    }

    public function deleteLastUploadAdvance() 
    {
        return $this->deleteLastUpload(2); // 2 for advances
    }

    private function deleteLastUpload($dumpType)
    {
        // Set dump type
        $this->dumpType = $dumpType;
        
        // Select the correct model based on dump type
        if($dumpType == 2) {
            $model = $this->dumpAdvanceModel;
            $redirect = 'bulkUploadAdvance';
            $type = 'Advance';
        } else {
            $model = $this->dumpDepositModel;
            $redirect = 'bulkUploadDeposit';
            $type = 'Deposit';
        }
        
        // Get ALL non-deleted accounts
        $allAccounts = $model->getAllAccounts([
            'where' => 'deleted_at IS NULL'
        ]);
        
        if(empty($allAccounts) || !is_array($allAccounts)) {
            Validation::flashErrorMsg('No uploads found to delete', 'error');
            Redirect::to(SiteUrls::getUrl($redirect));
            return;
        }
        
        // Get unique upload_keys with their latest date
        $uniqueUploads = [];
        foreach($allAccounts as $account) {
            $key = $account->upload_key;
            if(!isset($uniqueUploads[$key]) || $account->upload_date > $uniqueUploads[$key]['upload_date']) {
                $uniqueUploads[$key] = [
                    'upload_date' => $account->upload_date,
                    'upload_period_from' => $account->upload_period_from,
                    'upload_period_to' => $account->upload_period_to,
                    'upload_key' => $key
                ];
            }
        }
        
        // Sort by upload_date to get the latest
        usort($uniqueUploads, function($a, $b) {
            return strtotime($b['upload_date']) - strtotime($a['upload_date']);
        });
        
        // Check if there's at least one upload
        if(empty($uniqueUploads)) {
            Validation::flashErrorMsg('No uploads found to delete', 'error');
            Redirect::to(SiteUrls::getUrl($redirect));
            return;
        }
        
        $latestUpload = $uniqueUploads[0];
        $uploadKey = $latestUpload['upload_key'];
        $uploadDateTime = $latestUpload['upload_date'];
        $uploadPeriodFrom = $latestUpload['upload_period_from'];
        $uploadPeriodTo = $latestUpload['upload_period_to'];
        
        // Check if ANY records in this upload have sampling_filter = 1
        $sampledRecords = $model->getAllAccounts([
            'where' => 'upload_key = :upload_key AND deleted_at IS NULL AND sampling_filter = 1',
            'params' => ['upload_key' => $uploadKey]
        ]);
        
        $sampledCount = is_array($sampledRecords) ? count($sampledRecords) : 0;
        
        if($sampledCount > 0) {
            // Get total records for information
            $totalRecords = $model->getAllAccounts([
                'where' => 'upload_key = :upload_key AND deleted_at IS NULL',
                'params' => ['upload_key' => $uploadKey]
            ]);
            $totalCount = is_array($totalRecords) ? count($totalRecords) : 0;
            
            $errorMessage = "Cannot delete the last uploaded {$type} dump because it contains {$sampledCount} sampled records (sampling_filter = 1).<br>";
            $errorMessage .= "<strong>Upload Date:</strong> {$uploadDateTime}<br>";
            $errorMessage .= "<strong>Upload Period:</strong> {$uploadPeriodFrom} TO {$uploadPeriodTo}<br>";
            $errorMessage .= "<strong>Total Records:</strong> {$totalCount}<br>";
            $errorMessage .= "<strong>Sampled Records:</strong> {$sampledCount}<br><br>";
            $errorMessage .= "Records that have been sampled cannot be deleted. You can only delete uploads that contain no sampled records.";
            
            Validation::flashErrorMsg($errorMessage, 'error');
            Redirect::to(SiteUrls::getUrl($redirect));
            return;
        }
        
        // Get all non-sampled records with this upload_key
        $recordsWithSameKey = $model->getAllAccounts([
            'where' => 'upload_key = :upload_key AND deleted_at IS NULL AND sampling_filter = 0',
            'params' => ['upload_key' => $uploadKey]
        ]);
        
        $recordCount = is_array($recordsWithSameKey) ? count($recordsWithSameKey) : 0;
        
        if($recordCount == 0) {
            Validation::flashErrorMsg('No records found with the last upload key', 'error');
            Redirect::to(SiteUrls::getUrl($redirect));
            return;
        }
        
        // Check if this upload has already been deleted (should not happen due to our query, but just in case)
        // This is an extra safety check
        $deletedRecords = $model->getAllAccounts([
            'where' => 'upload_key = :upload_key AND deleted_at IS NOT NULL',
            'params' => ['upload_key' => $uploadKey]
        ]);
        
        if(!empty($deletedRecords)) {
            Validation::flashErrorMsg('This upload has already been partially deleted. Cannot delete again.', 'error');
            Redirect::to(SiteUrls::getUrl($redirect));
            return;
        }
        
        // Check if this is a confirmed deletion
        if(isset($_GET['confirm']) && $_GET['confirm'] == '1') {
            // Perform soft delete on all non-sampled records with this specific upload_key
            $result = $model::update(
                $model->getTableName(),
                ['deleted_at' => date('Y-m-d H:i:s')],
                [
                    'where' => 'upload_key = :upload_key AND deleted_at IS NULL AND sampling_filter = 0',
                    'params' => ['upload_key' => $uploadKey]
                ]
            );
            
            if($result) {
                Validation::flashErrorMsg("Last uploaded {$type} dump from {$uploadDateTime} (Period: {$uploadPeriodFrom} TO {$uploadPeriodTo}) deleted successfully. {$recordCount} records removed.", 'success');
            } else {
                Validation::flashErrorMsg('Error deleting last uploaded dump', 'error');
            }
            
            Redirect::to(SiteUrls::getUrl($redirect));
            return;
        }
        
        // Check if there's a next upload available after this one
        $nextUploadExists = count($uniqueUploads) > 1;
        
        // Show confirmation message with details
        $confirmMessage = "You are about to delete the last uploaded {$type} dump:<br>";
        $confirmMessage .= "<strong>Upload Date:</strong> {$uploadDateTime}<br>";
        $confirmMessage .= "<strong>Upload Period:</strong> {$uploadPeriodFrom} TO {$uploadPeriodTo}<br>";
        $confirmMessage .= "<strong>Records to Delete:</strong> {$recordCount}<br><br>";
        
        if($nextUploadExists) {
            $nextUpload = $uniqueUploads[1];
            $confirmMessage .= "<strong>Note:</strong> After deletion, the next available upload will be from {$nextUpload['upload_date']} (Period: {$nextUpload['upload_period_from']} TO {$nextUpload['upload_period_to']})<br><br>";
        } else {
            $confirmMessage .= "<strong>Note:</strong> After deletion, there will be no uploads left in the system.<br><br>";
        }
        
        $confirmMessage .= "This action cannot be undone. You can only delete the latest upload once.<br><br>";
        $confirmMessage .= "<a href='" . SiteUrls::getUrl($redirect) . "/deleteLastUpload" . ucfirst($type) . "?confirm=1' class='btn btn-danger'>Yes, Delete This Upload</a> ";
        $confirmMessage .= "<a href='" . SiteUrls::getUrl($redirect) . "' class='btn btn-secondary'>Cancel</a>";
        
        Validation::flashErrorMsg($confirmMessage, 'warning');
        Redirect::to(SiteUrls::getUrl($redirect));
    }
}

?>