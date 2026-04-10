<?php

use Core\FormElements;

if(!function_exists('generatePasswordHash')) {

    //generate password hash
    function generatePasswordHash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

}

if(!function_exists('reverse_slash')) {

    // reverse slash 17.09.2024
    function reverse_slash($str, $default = '\\') {

        if(!empty($str))
        {
            if ($default === '\\')
                $str = preg_replace('/\//', '\\\\', $str);
            else if ($default === '/')
                $str = preg_replace('/\\\\/', '/', $str);
        }

        return $str;
    }

}

if(!function_exists('find_and_remove_index_array')) {

    // trim data
    function find_and_remove_index_array($array, $val) {

        if(is_array($array) && sizeof($array) > 0) {
            foreach($array as $index => $cVal) {
                if($cVal == $val) unset($array[ $index ]);
            }
        }

        if(is_array($array) && sizeof($array) > 0)
            $array = array_values($array);

        return $array;
    }

}

if(!function_exists('trim_str')) {

    // trim data
    function trim_str($str) {

        if( empty($str) )
            return $str;

        $str = trim( $str );

        return $str;
    }

}

if(!function_exists('removeHtmlSpecialChars')) {

    function removeHtmlSpecialChars($string) {
        
        $decodedString = html_entity_decode($string, ENT_QUOTES | ENT_HTML5);
        $cleanString = strip_tags($decodedString);
        return $cleanString;
    }
}

if(!function_exists('string_operations')) {

    // trim data
    function string_operations($str, $strFunc = 'lower', $dataArray = []) {

        if( empty($str) )
            return null;

        $str = trim_str( $str );
        $str = removeHtmlSpecialChars($str);

        if( $strFunc == 'lower')
            $str = strtolower( $str );
        elseif( $strFunc == 'upper')
            $str = strtoupper( $str );
        elseif( $strFunc == 'comma_space')
            $str = preg_replace('/,\s*/', ', ', $str);
        elseif( $strFunc == 'url_decode')
            $str = urldecode($str);
        elseif( $strFunc == 'replace')
            $str = str_replace($dataArray[0], $dataArray[1], $str);
        
        return $str;
    }
}

if(!function_exists('urldecode_data')) {

    //generate password hash
    function urldecode_data($str) {

        if(empty($str)) return $str;

        $str = trim_str( $str );
        
        try { $str = urldecode($str); } 
        catch (Exception $e) { }

        return $str;
    }

}

if(!function_exists('verifyPasswordDb')) {

    //verify generated password hash
    function verifyPasswordDb($password, $db_hash) {
        return password_verify($password, $db_hash) ? true : false;
    }
    
}

if(!function_exists('accessControlCheck')) {

    //verify generated password hash
    function accessControlCheck($me) {
        
        // create session object 
        $session = new \Core\Session();
        $except = new \Core\Except();

        if($session::has('emp_type') && /*empty($session::get('emp_type')) || */
        isset($me -> accessControl) && !in_array($session::get('emp_type'), $me -> accessControl) )
        {
            $except::exc_access_restrict( );
            exit;
        }
    }
    
}

if(!function_exists('getFYOnDate'))
{
    function getFYOnDate($cDate)
    {
        if(in_array(date('m', strtotime($cDate)), ['01','02','03']))
        {
            //JAN, FEB, MAR //get previous year APR - MAR
            $fy = date('Y', strtotime($cDate . ' -1 year'));
        }
        else
            $fy = date('Y', strtotime($cDate));

        return $fy;
    }
}

if(!function_exists('check_active_status')) {

    //check active status
    function check_active_status($checkVal, $needMarkup = true, $defaultCheck = 1, $needBadge = 0) {

        if(!$needMarkup)
            return ($checkVal == $defaultCheck) ? true : false;

        if($checkVal == $defaultCheck)
            return "<span class='". (($needBadge) ? 'badge bg-success' : 'text-success font-medium') ."'>Active</span>";
        else
            return "<span class='". (($needBadge) ? 'badge bg-danger' : 'text-danger font-medium') ."'>Inactive</span>";
    }
}

if(!function_exists('get_convert_date_format')) {

    //convert date format on given date
    function get_convert_date_format($date, $format = 'Y-m-d') {

        if(empty(trim_str($date)))
            return NULL;

        if( $format == 'dmy')
            $format = 'd-m-Y';

        return date($format, strtotime($date));
    }
    
}

if(!function_exists('generate_batch_key')) {

    //convert date format on given date
    function generate_batch_key($str = 'A', $format = 'YmdHis') {
        return $str . date($format);
    }
    
}

if(!function_exists('encrypt_ex_data')) {

    function encrypt_ex_data($str)
    {
        if(!isset(ENCRYPT_EXT['encrypt']) || !ENCRYPT_EXT['encrypt'])
            return $str;

        $ivLength = openssl_cipher_iv_length( ENCRYPT_EXT['cipherMethod']);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encryptedData = openssl_encrypt($str,  ENCRYPT_EXT['cipherMethod'],  ENCRYPT_EXT['encryptionKey'], 0, $iv);

        // Combine encrypted data with IV for decryption
        $encryptedDataWithIv = base64_encode($iv . $encryptedData);

        // Make the result URL-safe
        $urlSafeEncryptedData = strtr($encryptedDataWithIv, '+/', '-_');

        return $urlSafeEncryptedData;
    }
}

if(!function_exists('decrypt_ex_data')) {

    function decrypt_ex_data($str)
    {
        if(!isset(ENCRYPT_EXT['encrypt']) || !ENCRYPT_EXT['encrypt'])
            return $str;

        // Convert back to base64
        $encryptedDataWithIv = !empty($str) ? strtr($str, '-_', '+/') : '';

        $ivLength = openssl_cipher_iv_length(ENCRYPT_EXT['cipherMethod']);

        // Extract IV from the beginning of the encrypted data
        $iv = substr(base64_decode($encryptedDataWithIv), 0, $ivLength);

        if (strlen($iv) !== $ivLength) {
            // throw new RuntimeException('Invalid IV length');
            return null;
        }

        $encryptedData = substr(base64_decode($encryptedDataWithIv), $ivLength);

        $decryptedData = openssl_decrypt($encryptedData, ENCRYPT_EXT['cipherMethod'], ENCRYPT_EXT['encryptionKey'], 0, $iv);

        return $decryptedData;
    }
}

if(!function_exists('return2View')) {

    function return2View($thisObj, $view, $extra = []) {

        $data = [ 'me' => $thisObj -> me, 'data' => (isset($thisObj -> data) ? $thisObj -> data : []) ];

        if(!empty($extra) && is_array($extra))
            $data += $extra;

        return $thisObj -> view( $view, $data);

    }
}

if(!function_exists('generateUniqueKey')) {
    
    function generateUniqueKey() {
        // Get the current date and time
        $dateTime = new DateTime();
        $timestamp = $dateTime->format('YmdHis');

        // Generate a random number for additional uniqueness
        $randomNumber = mt_rand(100, 999);

        // Combine the timestamp and random number
        $uniqueKey = $timestamp . $randomNumber;
        $uniqueKey = substr($uniqueKey, 0, 16);
        
        return $uniqueKey;
    }
}

if(!function_exists('check_array_exists')) {

    // function for check isset or array key exists
    function check_array_exists($dataArray, $checkKey, $defaultVal = null, $type = 'array_key') {

        if($type == 'array_key')
            return array_key_exists($checkKey, $dataArray) ? $dataArray[ $checkKey ] : $defaultVal;
        
        if($type == 'isset')
            return isset($dataArray[ $checkKey ]) ? $dataArray[ $checkKey ] : $defaultVal;

        return $defaultVal;
    }
}

if(!function_exists('generate_profile_img_url')) {

    function generate_profile_img_url($data, $employeeDetails) {
       
        //profile pic
        if(!empty($data -> profile_pic) && file_exists( PROFILE_IMG_ROOT . $data -> profile_pic))
            $employeeDetails['emp_profile'] =  PROFILE_IMG . $data -> profile_pic;
        else
        {
            //default images
            if(array_key_exists($data -> gender, $GLOBALS['userGenderArray']))
                $employeeDetails['emp_profile'] =  PROFILE_IMG . $GLOBALS['userGenderArray'][ $data -> gender ];
            else
                $employeeDetails['emp_profile'] = PROFILE_IMG . $GLOBALS['userGenderArray'][ 'mr' ];
        }

        return $employeeDetails;
    }
}

if(!function_exists('generate_bread_crumb')) {

    function generate_breadcrumb($siteUrls, $me) {

        $mrkup = '<ol class="breadcrumb-custom">';
        
        if(!empty($me -> breadcrumb))
        {
            foreach($me -> breadcrumb as $c_bredcrumb_nav)
            {
                $cGetMe = $siteUrls::get($c_bredcrumb_nav);

                $cGetMe -> pageHeading = ( $c_bredcrumb_nav == 'dashboard' ) ? 'Dashboard' : $cGetMe -> pageHeading;

                if(is_object($cGetMe))
                    $mrkup .= '<li><a href="'. $siteUrls::getUrl($c_bredcrumb_nav) .'">'. $cGetMe -> pageHeading .'</a></li>';
            }
        }

        $me -> pageHeading = ( $me -> id == 'dashboard' ) ? 'Welcome to the dashboard! Let\'s get started with your work.' : $me -> pageHeading;

        $mrkup .= '<li>'. $me -> pageHeading .'</li>' . "\n";

        $mrkup .= '</ol>' . "\n";

        return $mrkup;
    }
}

// Helper method to generate data attributes
if(!function_exists("generate_data_attributes"))
{
    function generate_data_attributes($params) {

        $dataAttributes = '';

        if(is_array($params) && array_key_exists('dataAttributes', $params))
        {
            foreach ($params['dataAttributes'] as $key => $value) {

                if (strpos($key, 'data-') === 0) {
                    $dataAttributes .= " " . $key . "='". $value ."'";
                }
            }
        }

        return $dataAttributes;
    }
}

if(!function_exists("generate_link_button"))
{
    function generate_link_button($btnType = '', $params = []) {

        $params['value'] = $params['value'] ?? '';

        if($btnType == 'add' || $btnType == 'update')
        {
            $params['class'] = 'btn btn-success icn-grid icn-bf';

            if($btnType == 'add')
                $params['class'] .= ' icn-add';
            else
                $params['class'] .= ' icn-update';
        }
        else if($btnType == 'active')
            $params['class'] = 'btn btn-success icn-grid icn-bf icn-check border-success';
        else if($btnType == 'inactive')
            $params['class'] = 'btn btn-danger icn-grid icn-bf icn-ban border-danger';
        else if($btnType == 'delete')
            $params['class'] = 'btn btn-danger icn-grid icn-bf icn-delete border-danger';
        else if($btnType == 'arrow-left')
            $params['class'] = 'btn btn-light icn-grid icn-bf icn-arrow-left icn-arrow-left-black border';
        else if($btnType == 'arrow-right')
            $params['class'] = 'btn btn-light icn-grid icn-bf icn-arrow-right icn-arrow-right-black border';
        else if($btnType == 'link')
            $params['class'] = 'btn btn-primary icn-grid icn-bf icn-link border-primary';
        else
            $params['class'] = 'btn btn-primary border-primary';

        if(empty($params['value']))
            $params['class'] .= ' no-text';
        
        $params['id']    = $params['id'] ?? '';
        $params['href']  = $params['href'] ?? '#';
        $params['dataAttributes'] = generate_data_attributes($params);
        $params['extra'] = $params['extra'] ?? '';
        $params['class'] .= isset($params['appendClass']) ? (" " . $params['appendClass']) : '';

        $inputMarkup  = "<a";
        $inputMarkup .= $params['href'] ? " href='". $params['href'] ."'" : '';
        $inputMarkup .= $params['id'] ? " id='". $params['id'] ."'" : '';
        $inputMarkup .= $params['class'] ? " class='". $params['class'] ."'" : '';
        $inputMarkup .= $params['dataAttributes'] ? $params['dataAttributes'] : '';
        $inputMarkup .= $params['extra'] ? (' ' . $params['extra']) : '';
        $inputMarkup .= ">";
        $inputMarkup .= $params['value'] ? $params['value'] : '';
        $inputMarkup .= '</a>';

        return $inputMarkup;
    }
}

if(!function_exists("generate_page_btn_array"))
{
    function generate_page_btn_array($paramData = [])
    {
        $data = [ 
            'default' => ['type' => 'arrow-left', 'href' => null, 'value' => null], 
            'add' => ['type' => 'add', 'href' => null, 'value' => null], 
            'link' => ['type' => 'link', 'href' => null, 'value' => null]
        ];

        $returnMarkup = '';

        if(is_array($paramData) && !empty($paramData))
        {
            foreach($paramData as $cKey => $cLinkData)
            {
                if(array_key_exists($cKey, $data))
                {
                    foreach($cLinkData as $ccKey => $ccData)
                        $data[ $cKey ][ $ccKey ] = $ccData; //assign link
                }
            }
        }

        foreach($data as $cKey => $cLinkData)
        {
            if($cLinkData['href'] != '')
                $returnMarkup .= generate_link_button($cLinkData['type'], $cLinkData);
        }

        return $returnMarkup;
    }
}

if(!function_exists("view_tooltip"))
{    
    //function for view tool tip
    function view_tooltip($title, $location = 'top')
    {
        //location - top, right, bottom, left
        return ' data-bs-toggle="tooltip" data-bs-placement="'. $location .'" title="'. $title .'"';
    }
}

if(!function_exists("get_all_data_query_builder"))
{
    //function for get all data from db
    function get_all_data_query_builder($fun_type, $this_obj, $table, $filters = [], $query_type = null, $sql = '')
    {
        if($fun_type === 1)
        {
            //call select single
            if($query_type != 'sql')
                return $this_obj::selectSingle($table, $filters);
            else
                return $this_obj::selectSingle('sql', $filters, $sql);
        }
        else
        {
            //call select multi //database helper function call
            if($query_type != 'sql')
                return $this_obj::selectMultiple($table, $filters);
            else
                return $this_obj::selectMultiple('sql', $filters, $sql);
        }
    }
}

if(!function_exists("generate_data_assoc_array"))
{
    function generate_data_assoc_array($dbData, $arrKey, $arrVal = '', $dbDataType = 'obj') 
    {
        $returnData = [];

        if($dbDataType == 'obj' && !sizeof($dbData) > 0 )
            return $returnData;

        if($dbDataType == 'arr' && !sizeof($dbData) > 0 )
            return $returnData;

        if(sizeof($dbData) > 0 )
        {
            foreach($dbData as $cIndex => $cData)
            {
                if(!empty($arrVal))
                    $returnData[ ( ($dbDataType == 'obj') ? $cData -> $arrKey : $cData[$arrKey] ) ] = ( ($dbDataType == 'obj') ? $cData -> $arrVal : $cData[$arrVal] );
                else
                    $returnData[ ( ($dbDataType == 'obj') ? $cData -> $arrKey : $cData[$arrKey] ) ] = $cData;
            }
        }

        return $returnData;
    }
}

if(!function_exists("generate_array_for_select"))
{
    function generate_array_for_select($dbData, $arrKey, $arrVal, $dbDataType = 'obj') 
    {
        //below function call
        return generate_data_assoc_array($dbData, $arrKey, $arrVal, $dbDataType);
    }
}

if(!function_exists("get_decimal"))
{
    //function for display decimal number
    function get_decimal($number, $decimalPoint, $optional = NULL, $length = 2)
    {
        //return number_format(round((float)$number), $decimalPoint, '.', '');
        if($optional == 'left')
        {
            $numberArray = !empty($number) ? explode('.', $number) : [];
            $tmpDecimal = (sizeof($numberArray) > 1) ? $numberArray[1] : null;

            if(strlen($tmpDecimal) > 2)
            {
                //more than two digits
                $number = $numberArray[0] . '.' . substr($tmpDecimal, 0, $length);
            }
        }

        if($optional == 'float')
            return floatval(number_format((double) $number, $decimalPoint, '.', ''));

        return number_format((double) $number, $decimalPoint, '.', '');
    }
}

if(!function_exists("getMenuIsDump"))
{
    function get_menu_is_dump($ansDetails, $menuData = null)
    {
        if( !is_object($ansDetails) || 
            !is_array($menuData) || 
            (is_array($menuData) && !sizeof($menuData) > 0))
            return null;
        
        $res = null;

        foreach($menuData as $cMenuId => $cMenuDetails)
        {
            if( $cMenuDetails -> id == $ansDetails -> menu_id && 
                isset($cMenuDetails -> categories) && 
                array_key_exists($ansDetails -> category_id, $cMenuDetails -> categories))
            {
                if( array_key_exists($cMenuDetails -> categories[ $ansDetails -> category_id ] -> linked_table_id, $GLOBALS['schemeTypesArray']) )
                {
                    // 2 => 'ADVANCES'
                    if($cMenuDetails -> categories[ $ansDetails -> category_id ] -> linked_table_id == 2)
                        $res = 'advance_dump_id';

	                // 1 => 'DEPOSITS'
                    elseif($cMenuDetails -> categories[ $ansDetails -> category_id ] -> linked_table_id == 1)
                        $res = 'deposite_dump_id';

                    if(!empty($res))
                        break;
                }
            }
        }

        return $res;
    }
}

if(!function_exists("check_re_assesment_status"))
{
    function check_re_assesment_status($assesmentData)
    {
        if(!is_object($assesmentData))
            return false;

        return ($assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[3]['status_id']);
    }
}

if(!function_exists('get_single_assesment_details'))
{
    function get_single_assesment_details($this_obj, $filterArray = [])
    {
        if( empty($filterArray) )
            return null;

        $model = $this_obj -> model('AuditAssesmentModel');

        $filterArray['cols'] = isset($filterArray['cols']) ? $filterArray['cols'] : '*';

        $select = 'SELECT '. $filterArray['cols'] .' FROM audit_assesment_master';

        $whereArray = [ 'where' => '', 'params' => '' ];

        if(isset($filterArray['assesment_id']))
            $whereArray = [ 'where' => 'id = :id', 'params' => [ 'id' => $filterArray['assesment_id'] ] ];

        elseif(isset($filterArray['where']))
            $whereArray = [ 'where' => $filterArray['where'], 'params' => $filterArray['params'] ];

        if(isset($filterArray['default']))
            $whereArray['where'] .= (!empty($whereArray['where']) ? ' AND ' : '') . 'is_limit_blocked = 0 AND deleted_at IS NULL';
                
        // function call
        return get_all_data_query_builder(1, $model, 'audit_assesment_master', $whereArray, 'sql', $select);
    }
}

if(!function_exists("get_assesment_details"))
{
    function get_assesment_details($this_obj, $empId, $assesmentId)
    {
        // get assesment model
        $model = $this_obj -> model('AuditAssesmentModel');
        $errData = null;

        //method call
        $assesmentData = $model -> getSingleAuditAssesment([
            'where' => 'id = :id AND is_limit_blocked = 0 AND deleted_at IS NULL',
            'params' => [ 'id' => $assesmentId ]
        ]);

        if(!is_object($assesmentData))
            $errData = 'errorFinding';

        if(!empty($errData))
            return $errData;

        // check expired audit / compliance
        if($assesmentData -> audit_status_id < 4)
        {
            // for audit
            if( empty($assesmentData -> audit_due_date) || 
              !(strtotime($assesmentData -> audit_due_date) >= strtotime(date($GLOBALS['dateSupportArray'][1]))) )
            {
                $errData = 'auditDueExpired';
                $assesmentData = null;
            }
        }
        elseif($assesmentData -> audit_status_id >= 4)
        {
            // for compliance
            if( empty($assesmentData -> compliance_due_date) || 
              !(strtotime($assesmentData -> compliance_due_date) >= strtotime(date($GLOBALS['dateSupportArray'][1]))) )
            {
                $errData = 'complianceDueExpired';
                $assesmentData = null;
            }
        }

        // find employee details
        if(empty($errData))
        {
            $model = $this_obj -> model('EmployeeModel');

            $empDetails = $model -> getSingleEmploye([
                'where' => 'id = :emp_id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'emp_id' => $empId ]
            ]);

            // for audit OR Reviewer
            if(is_object($empDetails) && ($empDetails -> user_type_id == 2 || $empDetails -> user_type_id == 4))
            {
                $audit_unit_authority = !empty($empDetails -> audit_unit_authority) ? explode(',', $empDetails -> audit_unit_authority) : [];

                if(  is_array($audit_unit_authority) && 
                    !in_array($assesmentData -> audit_unit_id, $audit_unit_authority))
                    $errData = 'noAssesmentAuthority';

                if($empDetails -> user_type_id == 4 && $errData == 'noAssesmentAuthority')
                    $errData = 'noReviewerAuthority';
            }

            // for compliance user
            elseif(is_object($empDetails) && $empDetails -> user_type_id == 3)
            {
                // print_r($empDetails);
                /*if( $assesmentData -> branch_head_id != $empDetails -> id && 
                    $assesmentData -> branch_subhead_id != $empDetails -> id )
                    $errData = 'noComplianceAuthority';
                
                if($errData == 'noComplianceAuthority' && !empty($assesmentData -> multi_compliance_ids))
                {
                    // check for multi compliance
                    $multi_compliance_ids = explode(',', $assesmentData -> multi_compliance_ids);

                    if( is_array($multi_compliance_ids) && 
                        !in_array($empDetails -> id, $multi_compliance_ids))
                        $errData = 'noComplianceAuthority';
                    else 
                        $errData = null;
                }*/
            }
        }    
        
        if(!empty($errData))
        {
            // return error message
            return $errData;
        }
        else
        {
            $assesmentData -> current_emp_details = $empDetails;

            $assesmentData -> audit_unit_id_details = null;

            if(!empty($assesmentData -> audit_unit_id))
            {
                // find audit unit
                $model = $this_obj -> model('AuditUnitModel');

                $assesmentData -> audit_unit_id_details = $model -> getSingleAuditUnit([
                    'where' => 'id = :id AND deleted_at IS NULL',
                    'params' => [ 'id' => $assesmentData -> audit_unit_id ]
                ]);
            }
        }

        return $assesmentData;
    }
}

if(!function_exists("get_assesment_all_details"))
{
    function get_assesment_all_details($this_obj, $assesmentId = null, $assesmentData = null)
    {
        if( empty($assesmentId) && empty($assesmentData) )
            return null;

        $model = $this_obj -> model('AuditAssesmentModel');

        if( !empty($assesmentId) )
            $assesmentData = $model -> getSingleAuditAssesment([
                'where' => 'id = :id AND deleted_at IS NULL',
                'params' => [ 'id' => $assesmentId ]
            ]);

        if(!is_object($assesmentData))
            return null;

        // find data // year details
        $model = $this_obj -> model('YearModel');

        $assesmentData -> year_details = $model -> getSingleYear([
            'where' => 'id = :id', // AND is_active = 1 AND deleted_at IS NULL
            'params' => [ 'id' => $assesmentData -> year_id ]
        ]);

        // find data // audit unit with code
        $model = $this_obj -> model('AuditUnitModel');

        $assesmentData -> audit_unit_details = $model -> getSingleAuditUnit([
            'where' => 'id = :id', // AND is_active = 1 AND deleted_at IS NULL
            'params' => [ 'id' => $assesmentData -> audit_unit_id ]
        ]);

        $model = $this_obj -> model('EmployeeModel');
        $searchEmployees = [];

        // for audit head
        if(!empty($assesmentData -> audit_head_id))
            $searchEmployees[] = $assesmentData -> audit_head_id;

        // for branch_head_id
        if(!empty($assesmentData -> branch_head_id))
            $searchEmployees[] = $assesmentData -> branch_head_id;

        // for branch_subhead_id
        if(!empty($assesmentData -> branch_subhead_id))
            $searchEmployees[] = $assesmentData -> branch_subhead_id;

        $assesmentData -> audit_head_details = null;
        $assesmentData -> branch_head_details = null;
        $assesmentData -> branch_subhead_details = null;
        
        if(sizeof($searchEmployees) > 0)
        {
            $searchEmployees = $model -> getAllEmployees([
                'where' => 'id IN ('. implode(',', $searchEmployees) .') AND is_active = 1 AND deleted_at IS NULL'
            ]);

            // helper function call
            $searchEmployees = generate_data_assoc_array($searchEmployees, 'id');

            // audit_head_id
            if(is_array($searchEmployees) && array_key_exists($assesmentData -> audit_head_id, $searchEmployees))
                $assesmentData -> audit_head_details = $searchEmployees[ $assesmentData -> audit_head_id ];

            // branch_head_id
            if(is_array($searchEmployees) && array_key_exists($assesmentData -> branch_head_id, $searchEmployees))
                $assesmentData -> branch_head_details = $searchEmployees[ $assesmentData -> branch_head_id ];

            // branch_subhead_id
            if(is_array($searchEmployees) && array_key_exists($assesmentData -> branch_subhead_id, $searchEmployees))
                $assesmentData -> branch_subhead_details = $searchEmployees[ $assesmentData -> branch_subhead_id ];
        }

        // unset vars
        unset($searchEmployees, $model);

        return $assesmentData;
    }
}

if(!function_exists("convert_risk_matrix"))
{
    function convert_risk_matrix($riskMatrixData)
    {
        if(is_array($riskMatrixData) && sizeof($riskMatrixData) > 0)
        {
            $tempRiskMatrixData = $riskMatrixData;
            $riskMatrixData = [];

            foreach($tempRiskMatrixData as $cRiskMatrix)
            {
                if(!array_key_exists( $cRiskMatrix -> risk_parameter, $riskMatrixData ))
                    $riskMatrixData[ $cRiskMatrix -> risk_parameter ] = $cRiskMatrix;

                // default
                $riskMatrixData[ $cRiskMatrix -> risk_parameter ] -> risk_parameter_txt = ERROR_VARS['notFound'];

                //check for title
                if(array_key_exists( $cRiskMatrix -> risk_parameter, RISK_PARAMETERS_ARRAY ))
                    $riskMatrixData[ $cRiskMatrix -> risk_parameter ] -> risk_parameter_txt = RISK_PARAMETERS_ARRAY[ $cRiskMatrix -> risk_parameter ]['title'];
            }
        }

        return $riskMatrixData;
    }
}

if(!function_exists("generate_header_wise_question_array"))
{
    function generate_header_wise_question_array($ansData)
    {
        $returnData = array();

        if( is_array($ansData) && sizeof($ansData) > 0 )
        {
            foreach($ansData as $cAnsId => $cAnsDetails)
            {
                if( !array_key_exists($cAnsDetails -> header_id, $returnData) )
                    $returnData[ $cAnsDetails -> header_id ] = [];

                // push answer
                $returnData[ $cAnsDetails -> header_id ][ $cAnsDetails -> question_id ] = $cAnsDetails;
            }
        }

        return $returnData;
    }
}

if(!function_exists('get_answer_data_timeline_data'))
{ 
    //function for accept review
    function get_answer_data_timeline_data($this_obj, $assesmentData, $filter_type = 1, $whereData = null)
    {
        // $filter_type = 1 for audit // 2 = compliance
        $model = $this_obj -> model('AnswerDataTimelineModel');
        $resData = [];

        if(!is_array($whereData))
        {
            $whereData = [
                'where' => 'assesment_id = :assesment_id AND answer_type = :answer_type AND deleted_at IS NULL',
                'params' => [ 'assesment_id' => $assesmentData -> id, 'answer_type' => $filter_type ]
            ];
        }

        $answerDataTimeline = $model -> getAllAnswersTimeline($whereData);

        if(is_array($answerDataTimeline) && sizeof($answerDataTimeline) > 0)
        {
            foreach($answerDataTimeline as $cAnsData)
            {
                $cGenKey = $cAnsData -> answer_id . '_' . $cAnsData -> annex_id . '_' . $cAnsData -> answer_type;

                if(!array_key_exists($cGenKey, $resData))
                    $resData[ $cGenKey ] = [];

                $resData[ $cGenKey ][ $cAnsData -> id ] = $cAnsData;
            }
        }

        return (is_array($resData) && sizeof($resData) > 0) ? $resData : null;
    }
}

if(!function_exists('generate_assesment_top_markup'))
{
    //function for generate top markup
    function generate_assesment_top_markup($assessmentData, $type = 1) {
        
        $mrk_str = '';

        $mrk_str .= '<div class="card apcard mb-4">' . "\n";
            $mrk_str .= '<div class="card-header">Assesment Details</div>' . "\n";
    
            $mrk_str .= '<div class="card-body border border-top-0">' . "\n";

                $brachObj = null;
                $assessTimelineCnt = 1;

                if( isset($assessmentData -> audit_unit_id_details) && is_object($assessmentData -> audit_unit_id_details) )
                    $brachObj = $assessmentData -> audit_unit_id_details;

                $mrk_str .= '<h5 class="font-medium site-purple mb-1">'. string_operations( ( is_object($brachObj) ? $brachObj -> name : ERROR_VARS['notFound'] ), 'upper') . ' <span class="d-inline-block">( BR. CODE: '. trim_str( is_object($brachObj) ? $brachObj -> audit_unit_code : ERROR_VARS['notFound'] ) .' )</span>' .'</h5>' . "\n";

                $mrk_str .=  '<p class="mb-1">Assessment Period: '. trim_str($assessmentData -> assesment_period_from) . ' To ' . trim_str($assessmentData -> assesment_period_to) . ' <span class="d-inline-block font-sm text-secondary">( Frequency: '. $assessmentData -> frequency .' Months )</span>' .'</p>' . "\n";

                $mrk_str .= '<div class="row mt-3">' . "\n";

                    $col = (array_key_exists('4', $GLOBALS['userTypesArray']) ? 'col-md-6 col-lg-3' : 'col-md-6') . ' mb-2';

                    // for audit
                    $mrk_str .= '<div class="'. $col . ' assess-timeline-container">' . "\n";
                        $mrk_str .= '<span class="assess-timeline">'. $assessTimelineCnt++ .'</span>' . "\n";
                        $mrk_str .= '<div class="border h-100 d-flex align-items-center justify-content-center '. ( ($assessmentData -> audit_status_id == 2 || $assessmentData -> audit_status_id > 3)  ? 'bg-success text-white border-success' : ( in_array($assessmentData -> audit_status_id, [1, 3]) ? 'bg-light-gray' : '' ) ) .' text-center">' . "\n";

                            $mrk_str .=  '<div>' . "\n";

                            $mrk_str .= '<p class="font-sm mb-0">AUDIT STATUS</p>' . "\n";

                            $tempStatus = string_operations('Completed', 'upper');

                            if( in_array($assessmentData -> audit_status_id, [1,3]))
                                $tempStatus = ASSESMENT_TIMELINE_ARRAY[ $assessmentData -> audit_status_id ]['title'];

                            $mrk_str .= '<p class="font-bold mb-0">'. $tempStatus .'</p>' . "\n";

                            $mrk_str .=  '</div>' . "\n";

                        $mrk_str .= '</div>' . "\n";
                    $mrk_str .= '</div>' . "\n";

                    // for review audit
                    if(array_key_exists('4', $GLOBALS['userTypesArray']))
                    {
                        $mrk_str .= '<div class="'. $col . ' assess-timeline-container">' . "\n";
                            $mrk_str .= '<span class="assess-timeline">'. $assessTimelineCnt++ .'</span>' . "\n";
                            $mrk_str .= '<div class="border h-100 d-flex align-items-center justify-content-center '. ( $assessmentData -> audit_status_id > 3 ? 'bg-success text-white border-success' : ( in_array($assessmentData -> audit_status_id, [2])  ? 'bg-light-gray' : '' ) ) .' text-center">' . "\n";

                                $mrk_str .=  '<div>' . "\n";

                                $mrk_str .= '<p class="font-sm mb-0">AUDIT REVIEW STATUS</p>' . "\n";

                                $tempStatus = string_operations('Completed', 'upper');

                                if( in_array($assessmentData -> audit_status_id, [2]))
                                    $tempStatus = ASSESMENT_TIMELINE_ARRAY[ $assessmentData -> audit_status_id ]['title'];

                                if( !($assessmentData -> audit_status_id > 1) ) $tempStatus = '<span class="text-light-gray">' . ERROR_VARS['notAvailable'] . '</span>';

                                $mrk_str .= '<p class="font-bold mb-0">'. $tempStatus .'</p>' . "\n";

                                $mrk_str .=  '</div>' . "\n";

                            $mrk_str .= '</div>' . "\n";
                        $mrk_str .= '</div>' . "\n";
                    }

                    // for compliance
                    $mrk_str .= '<div class="'. $col . ' assess-timeline-container">' . "\n";
                        $mrk_str .= '<span class="assess-timeline">'. $assessTimelineCnt++ .'</span>' . "\n";
                        $mrk_str .= '<div class="border h-100 d-flex align-items-center justify-content-center '. ( ($assessmentData -> audit_status_id == 5 || $assessmentData -> audit_status_id > 6) ? 'bg-success text-white border-success' : ( in_array($assessmentData -> audit_status_id, [4, 6]) ? 'bg-light-gray' : '' ) ) .' text-center">' . "\n";

                            $mrk_str .=  '<div>' . "\n";

                            $mrk_str .= '<p class="font-sm mb-0">COMPLIANCE STATUS</p>' . "\n";

                            $tempStatus = string_operations('Completed', 'upper');

                            if( array_key_exists($assessmentData -> audit_status_id, ASSESMENT_TIMELINE_ARRAY) && 
                                in_array($assessmentData -> audit_status_id, [4,6]))
                                $tempStatus = ASSESMENT_TIMELINE_ARRAY[ $assessmentData -> audit_status_id ]['title'];

                            if( !($assessmentData -> audit_status_id > 3) ) $tempStatus = '<span class="text-light-gray">' . ERROR_VARS['notAvailable'] . '</span>';

                            $mrk_str .= '<p class="font-bold mb-0">'. $tempStatus .'</p>' . "\n";

                            $mrk_str .=  '</div>' . "\n";

                        $mrk_str .= '</div>' . "\n";
                    $mrk_str .= '</div>' . "\n";

                    // for review compliance
                    if(array_key_exists('4', $GLOBALS['userTypesArray']))
                    {
                        $mrk_str .= '<div class="'. $col . ' assess-timeline-container">' . "\n";
                            $mrk_str .= '<span class="assess-timeline">'. $assessTimelineCnt++ .'</span>' . "\n";
                            $mrk_str .= '<div class="border h-100 d-flex align-items-center justify-content-center '. ( $assessmentData -> audit_status_id == 7 ? 'bg-success text-white border-success' : ( in_array($assessmentData -> audit_status_id, [5]) ? 'bg-light-gray' : '' ) ) .' text-center">' . "\n";

                                $mrk_str .=  '<div>' . "\n";

                                $mrk_str .= '<p class="font-sm mb-0">COMPLIANCE REVIEW STATUS</p>' . "\n";

                                $tempStatus = string_operations('Completed', 'upper');

                                if(in_array($assessmentData -> audit_status_id, [5]))
                                    $tempStatus = ASSESMENT_TIMELINE_ARRAY[ 5 ]['title'];

                                if( !($assessmentData -> audit_status_id > 5) ) $tempStatus = '<span class="text-light-gray">' . ERROR_VARS['notAvailable'] . '</span>';

                                $mrk_str .= '<p class="font-bold mb-0">'. $tempStatus .'</p>' . "\n";

                                $mrk_str .=  '</div>' . "\n";

                            $mrk_str .= '</div>' . "\n";
                        $mrk_str .= '</div>' . "\n";
                    }

                $mrk_str .= '</div>' . "\n";

            $mrk_str .= '</div>' . "\n";
        $mrk_str .= '</div>' . "\n";

        return $mrk_str;
    }
}

if(!function_exists('audit_assesment_timeline_insert'))
{
    function audit_assesment_timeline_insert($this_obj, $insertArray/*, $type = 'com'*/)
    {
        // type_id = 1 audit, 2 = compliance

        $model = $this_obj -> model('AuditAssesmentTimelineModel');

        $result = $model::insert(
            $model -> getTableName(), 
            [
                "assesment_id" => $insertArray['id'],
                "type_id" => $insertArray['type'],
                "status_id" => $insertArray['status'],
                "rejected_cnt" => $insertArray['rejected_cnt'],
                "reviewer_emp_id" => $insertArray['emp_id'],
                "batch_key" => $insertArray['batch_key']
            ]
        );

        return (!$result) ? false : true;
    }
}

if(!function_exists('generate_multiple_checkboxes'))
{
    function generate_multiple_checkboxes($data, $db_checked_data, $checkBoxName, $showKeyType = 'scheme')
    {
        $returnMrk = '';

        if(!is_array($data))
            return $returnMrk;

        $i = 0;

        foreach($data as $cId => $cData)
        {
            $i++;
            $checked = false;

            if($i == 1)
                $returnMrk .= '<tr>';
        
            if(in_array($cId, $db_checked_data))
                $checked = true;
        
            $returnMrk .= '<td width="50%">';

            if($showKeyType == 'scheme')
            {
                $text = string_operations(('[ SCH. ' . $cData -> scheme_code . ' ] ' . $cData -> name), 'upper');
                $text .= isset($cData -> cat_name) ? ('<span class="font-sm d-block text-secondary ps-4">[ Mapped Category - '. string_operations($cData -> cat_name, 'upper') .' ]</span>') : '';
            }
            elseif($showKeyType == 'category')
            {
                $text = string_operations($cData -> name, 'upper');
                $text .= isset($cData -> menu_name) ? ('<span class="font-sm d-block text-secondary ps-4">[ Mapped Menu - '. string_operations($cData -> menu_name, 'upper') .' ]</span>') : '';
            }
            elseif($showKeyType == 'employee')
                $text = string_operations($cData -> combined_name, 'upper');
            elseif($showKeyType == 'audit_unit')
                $text = string_operations($cData -> combined_name, 'upper');
            elseif($showKeyType == 'questionSet')
                $text = string_operations($cData, 'upper');
            else
                $text = string_operations($cData -> name, 'upper');

                $returnMrk .= FormElements::generateCheckboxOrRadio([
                    /*"name" => ($checkBoxName . "[]"),*/ "appendClass" => 'multi-checkbox-ids',
                    "text" => $text, "checked" => $checked, "value" => $cId,
                ]);
        
            $returnMrk .= '</td>';
        
            if($i == 2) 
            {
                $returnMrk .= '</tr>';
                $i = 0;
            }
        }

        if($i == 1)
            $returnMrk .= '<td></td></tr>';

        return $returnMrk;

    }
}

if(!function_exists('date_validation_helper'))
{
    function date_validation_helper($requestObj, $validationArray, $notiObj, $otherInputs = [])
    {
        if($requestObj -> has( 'startDate' ))
        {
            $validationArray['validation']['startDate'] = 'required|regex[dateRegex, dateError]';
            $validationArray['validation']['endDate'] = 'required|regex[dateRegex, dateError]';

            $startDate = $requestObj -> input( 'startDate' );
            $endDate = $requestObj -> input( 'endDate' );
            $endDateErr = 'endDate_err';
        }
        else
        {
            $periodFromText = 'period_from';
            $startDate = null;

            $periodToText = 'period_to';
            $endDate = null;

            if(is_array($otherInputs) && sizeof($otherInputs) > 0)
            {
                $startDate = $requestObj -> input( $otherInputs[0] );
                $periodFromText = $otherInputs[0];
                $endDate = $requestObj -> input( $otherInputs[1] );
                $periodToText = $otherInputs[1];
            }

            $validationArray['validation'][ $periodFromText ] = 'required|regex[dateRegex, dateError]';
            $validationArray['validation'][ $periodToText ] = 'required|regex[dateRegex, dateError]';
            $endDateErr = $periodToText . '_err';
        }

        if( !empty($startDate) && !empty($endDate) && 
            strtotime($startDate) > strtotime($endDate) )
        {
            $requestObj -> setInputCustom( $endDateErr, $notiObj::getNoti('endDateGratorError'));
            $requestObj -> setInputCustom( 'error', 1);
        }
        else
        {
            // Calculate the difference in years
            $monthsDiff = round(( strtotime($endDate) - strtotime($startDate) ) / (30 * 24 * 60 * 60));
            $err = false;

            if($monthsDiff > 12)
                $err = true;

            if(!$err)
            {
                //check date in fiancial year
                $fYear = getFYOnDate($startDate);
                $fyStartDate = $fYear . '-04-01';
                $fyEndDate = ($fYear + 1) . '-03-31';

                if (  !(strtotime($startDate) >= strtotime($fyStartDate) && 
                        strtotime($endDate) <= strtotime($fyEndDate)) )
                        $err = true;
            }
                
            if($err)
            {
                $requestObj -> setInputCustom( $endDateErr, $notiObj::getNoti('notFYMonthError'));
                $requestObj -> setInputCustom( 'error', 1);
            }
            
        }

        return $validationArray;
    }
}

if(!function_exists('generate_report_buttons'))
{
    function generate_report_buttons($btnArray)
    {
        $formElement = new FormElements();

        if(in_array('find', $btnArray))
            echo $formElement::generateSubmitButton('find', [ 'value' => 'Find Data', 'id' => 'findBtn', 'appendClass' => '']);
        if(in_array('filter', $btnArray))
            echo $formElement::generateSubmitButton('filter', [ 'value' => 'Apply Filter', 'type' => 'button', 'id' => 'filterBtn', 'appendClass' => '']);
        if(in_array('reset', $btnArray))
            echo $formElement::generateSubmitButton('reset', [ 'value' => 'Reset', 'type' => 'button', 'id' => 'resetBtn', 'appendClass' => '']);
        if(in_array('print', $btnArray))
            echo $formElement::generateSubmitButton('print', [ 'value' => 'Print', 'type' => 'button', 'id' => 'printBtn', 'dataAttributes' => [ 'data-url' => URL . 'print' ] ]);
        if(in_array('excel', $btnArray))
            echo $formElement::generateSubmitButton('excel', [ 'value' => 'Excel', 'type' => 'button', 'id' => 'excelBtn', 'appendClass' => '']);
        
    }
}

if(!function_exists('generate_report_header'))
{
    function generate_report_header($data, $fy = false, $fyData = 0, $asses = false)
    {
        echo '<div class="row mb-3">' . "\n";

            echo '<div class="col-md-12">' . "\n";

                echo '<p class="mb-0"><span class="font-medium">Report: </span> ' . $data['pageTitle'] . '</p>' . "\n";
                echo '<p class="mb-0"><span class="font-medium">Report Run Date: </span> ' . date($GLOBALS['dateSupportArray'][1]) . '</p>' . "\n";

                if($fy) echo '<p class="mb-0"><span class="font-medium">Financial Year: </span> ' . $fyData . '</p>';

                if($asses)
                {
                    echo '<p class="mb-0"><span class="font-medium">Assesment Period: </span> ' . $data['assesmentData'] -> assesment_period_from . ' to ' . $data['assesmentData'] -> assesment_period_to . '</p>';

                    echo '<p class="mb-0"><span class="font-medium">Audit Unit: </span> ' . $data['audit_unit_data'][ $data['assesmentData'] -> audit_unit_id ] -> name . '</p>';
                }
                
            echo '</div>'. "\n";

        echo '</div>';
    }
}

if(!function_exists('check_admin_action'))
{
    function check_admin_action($this_obj, $extra = [])
    {
        // multiple user types like 1 - super admin which has all access, 9 admin lite - limited

        // create session object 
        $session = new \Core\Session();
        $except = new \Core\Except();
        $accessFalse = false;

        if($session::has('emp_type') && $session::get('emp_type') == 9) // ADMIN LITE
        {
            // enable access
            $accessFalse = isset($extra['lite_access']) ? false : true;
        }
        else if($session::has('emp_type') && $session::get('emp_type') == 1) // SUPER ADMIN
        {
            if(ADMIN_ACT_DISABLE === 1) // config val
                $accessFalse = true;
            else
            {
                if(!isset($extra['super_access']))
                {
                    // check any assesment started // here only get count
                    $model = $this_obj -> model('AuditAssesmentModel');
                    $table = $model -> getTableName();

                    $select = 'SELECT COUNT(*) as tot_asses FROM '. $table .' aam';

                    if(isset($extra['where']))
                        $select .= ' ' . $extra['where'];

                    $filter = isset($extra['filter']) ? $extra['filter'] : [];

                    $details_of_asses_data = get_all_data_query_builder(1, $model, $table, $filter, 'sql', $select);

                    if( is_object($details_of_asses_data) )
                        $accessFalse = $details_of_asses_data -> tot_asses > 0 ? false : true;
                }
                else
                    $accessFalse = $extra['super_access'];
            }
        }
        else
        {
            $except::exc_access_restrict( );
            exit;
        }

        return $accessFalse;
    }
}

if(!function_exists('get_db_table_sql_count'))
{
    function get_db_table_sql_count($this_obj, $model, $dbTableName, $filterArray = [], $checkDeletedAt = true, $extra = [])
    {
        if(!is_object($model))
            $model = $this_obj -> model($model);

        if(!sizeof($filterArray) > 0)
            $filterArray = [ 
                'where' => ( ($checkDeletedAt) ? ' deleted_at IS NULL' : '' ), 
                'params' => [ ] 
            ];

        // super admin disable
        if( $dbTableName == 'employee_master' )
            $filterArray['where'] .= ( $filterArray['where'] != '' ? ' AND ' : '' ) . ' emp_code != 1';

        // extra parameters like order by and group
        if(isset($filterArray['extra_params']) && $filterArray['extra_params'] != '')
            $filterArray['where'] .= ' ' . $filterArray['extra_params'];

        if(isset($extra['countQuery']))
            $select = $extra['countQuery'];
        else
            $select = "SELECT COUNT(*) total_records FROM " . $dbTableName;

        // total number of records without filtering
        $db_data = get_all_data_query_builder(2, $model, $dbTableName, $filterArray, 'sql', $select);

        $resArray['total_records'] = 0;

        if( is_array($db_data) && sizeof($db_data) > 1)
            $resArray['total_records'] = sizeof($db_data);
        elseif(is_array($db_data) && sizeof($db_data) == 1)
        {
            if(isset($filterArray['combined_count']))
                $resArray['total_records'] = 1;
            else
                $resArray['total_records'] = $db_data[0] -> total_records;
        }

        // print_r($resArray);
        return json_decode(json_encode($resArray));
    }
}

// function for datatable data
if(!function_exists('generate_datatable_data'))
{
    function generate_datatable_data($this_obj, $model, $searchKeys, $filterArray = [], $checkDeletedAt = true, $extra = [])
    {
        if(!is_object($model))
            $model = $this_obj -> model($model);

        // get table name
        $dbTableName = $model -> getTableName();

        $dataResArray = [
            "draw" => $this_obj -> request -> input('draw'),
            "row" => $this_obj -> request -> input('start', 0),
            "row_per_page" => $this_obj -> request -> input('length', 10), // Rows display per page
            "order" => $this_obj -> request -> input('order'),
            "column_index" => null,
            "column_name" => null,
            "column_sort_order" => null,
            "search_value" => null, // Search value
            "total_records" => 0,
            "total_records_with_filter" => 0,
            "aaData" => null
        ];

        if( is_array( $this_obj -> request -> input('search') ) && 
            array_key_exists('value', $this_obj -> request -> input('search')) )
            $dataResArray['search_value'] = $this_obj -> request -> input('search')['value'];

        if(is_array($dataResArray['order']) && sizeof($dataResArray['order']) > 0)
        {
            $dataResArray['column_index'] = $dataResArray['order'][0]['column']; // Column index

            $column = $this_obj -> request -> input('columns');

            if( is_array($column) && 
                $dataResArray['column_index'] != '' && 
                array_key_exists($dataResArray['column_index'], $column) &&
                is_array( $column[ $dataResArray['column_index'] ]) && 
                array_key_exists('data', $column[ $dataResArray['column_index'] ]))
                $dataResArray['column_name'] = $column[ $dataResArray['column_index'] ]['data']; // Column name

            $dataResArray['column_sort_order'] = $dataResArray['order'][0]['dir']; // asc or desc
        }

        if(!sizeof($filterArray) > 0)
            $filterArray = [ 'where' => '', 'params' => []];

        if($dataResArray['search_value'] != '')
        {
            if(is_array($searchKeys) && sizeof($searchKeys) > 0)
            {
                if(!empty($filterArray['where']))
                    $filterArray['where'] .= ' AND ';

                $filterArray['where'] .= "( ";

                foreach($searchKeys as $cIndex => $cSearchKey)
                {
                    $paramKey = preg_replace('/\./', '_', $cSearchKey);

                    $filterArray['where'] .= $cSearchKey . ' LIKE :' . $paramKey;
                    $filterArray['params'][ $paramKey ] = '%'. $dataResArray['search_value'] .'%';

                    if($cIndex != (sizeof($searchKeys) - 1))
                        $filterArray['where'] .= ' OR ';
                }

                $filterArray['where'] .= " )";
                // print_r($filterArray);
            }
            elseif(!is_array($searchKeys) && $searchKeys != '')
            {
                if(!empty($filterArray['where']))
                    $filterArray['where'] .= ' AND ';

                $filterArray['where'] .= $searchKeys . ' LIKE :' . $searchKeys;
                $filterArray['params'][ $searchKeys ] = '%'. $dataResArray['search_value'] .'%';
            }                
        }

        // add deleted at
        // if(!empty($filterArray['where']))
        //     $filterArray['where'] .= ' AND ';

        // for deleted at
        if($checkDeletedAt) {
    if(!empty($filterArray['where']))
        $filterArray['where'] .= ' AND deleted_at IS NULL';
    else
        $filterArray['where'] .= 'deleted_at IS NULL';
}

        // super admin disable
        if($dbTableName == 'employee_master')
            $filterArray['where'] .= ' AND emp_code != 1';

        // extra parameters like order by and group
        if(isset($filterArray['extra_params']) && $filterArray['extra_params'] != '')
        {
            $filterArray['where'] .= ' ' . $filterArray['extra_params'];
            unset($filterArray['extra_params']);
        }        

        // total number of records without filtering // function call
        $dataResArray['total_records'] = get_db_table_sql_count($this_obj, $model, $dbTableName, [], $checkDeletedAt);

        // re assign
        $dataResArray['total_records'] = $dataResArray['total_records'] -> total_records;

        // total number of records with filtering
        $dataResArray['total_records_with_filter'] = get_db_table_sql_count($this_obj, $model, $dbTableName, $filterArray, $checkDeletedAt, $extra);

        // re assign
        $dataResArray['total_records_with_filter'] = $dataResArray['total_records_with_filter'] -> total_records;

        $defaultColumnIndex = 0;
        $defaultSortDir = 'asc';

        $isDefaultSort = (
            $dataResArray['column_index'] != null && $dataResArray['column_index'] == $defaultColumnIndex &&
            $dataResArray['column_sort_order'] != null && $dataResArray['column_sort_order'] == $defaultSortDir
        );

        // column order
        if( !$isDefaultSort &&
            $dataResArray['column_name'] != "" && 
            !in_array($dataResArray['column_name'], ["sr_no"]))
        {
            $setOrderByComma = '';

            // if custom order by then need to remove
            if(!empty($filterArray['where']) && 
                preg_match('/ORDER BY/', $filterArray['where']))
                $filterArray['where'] = explode('ORDER BY', $filterArray['where'])[0];

            $filterArray['where'] .= " ORDER BY ";

            if( $dataResArray['column_name'] == "status" )
                $filterArray['where'] .= 'is_active';
            else
                $filterArray['where'] .= $dataResArray['column_name'] . '+0';

            // print_r($filterArray['where']);
        
            $filterArray['where'] .= " ". $dataResArray['column_sort_order'];
        }
        
        // limit        
        $filterArray['where'] .= " LIMIT ". $dataResArray['row'] .", " . $dataResArray['row_per_page'];

        if(isset($extra['query']))
            $select = $extra['query'];
        else
            $select = "SELECT * FROM " . $dbTableName;
        
        $dbData = get_all_data_query_builder(null, $model, $dbTableName, $filterArray, 'sql', $select);

        return [ 'dataResArray' => $dataResArray, 'dbData' => $dbData ];
    }
}

// function for unset vars data
if(!function_exists('unset_datatable_vars'))
{
    function unset_datatable_vars($dataResArray)
    {
        // reassign
        $dataResArray = $dataResArray['dataResArray'];

        $dataResArray["iTotalRecords"] = $dataResArray["total_records"];
        $dataResArray["iTotalDisplayRecords"] = $dataResArray["total_records_with_filter"];

        // unset vars
        unset(
            $dataResArray["row"],
            $dataResArray["row_per_page"],
            $dataResArray["order"],
            $dataResArray["column_index"],
            $dataResArray["column_name"],
            $dataResArray["column_sort_order"],
            $dataResArray["search_value"],
            $dataResArray["total_records"],
            $dataResArray["total_records_with_filter"]
        );

        return $dataResArray;
    }
}

if(!function_exists('generate_datatable_javascript'))
{
    function generate_datatable_javascript( $htmlTableId, $ajaxUrl, $dataCols, $disableSearch = false, $orderStart = 0, $postData = NULL )
    {
        if(!is_array($dataCols) || (is_array($dataCols) && !(sizeof($dataCols) > 0)))
            return null;

        $tempDataCols = [];

        foreach($dataCols as $cDataCol)
            $tempDataCols[] = [ 'data' => $cDataCol ];

        // re assign
        $dataCols = $tempDataCols;
        
        unset($tempDataCols);

        $return_str = '<script> $(document).ready(function(){$("#'. $htmlTableId .'").DataTable({lengthChange:!1,';
            
        if($disableSearch)
            $return_str .= 'searching:!1,';
            
        if(is_array($postData) && sizeof($postData) > 0)
            $postData = ', data:' . json_encode($postData);
        else
            $postData = '';

        $return_str .= 'processing:!0,serverSide:!0,serverMethod:"post",order: ['. ( 
            ($orderStart >= 0) ? ('['. $orderStart .',"asc"]') : '') .'],pagingType:"first_last_numbers",language:{search:"Search / Filter Records:",searchPlaceholder:"Type here . . ."},aoColumnDefs:[{bSortable:!1,aTargets:["nosort"]}],ajax:{url:"'. $ajaxUrl .'"' . $postData . '},columns:'. json_encode($dataCols) .',"drawCallback": function(settings) { shiftFooter(); var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\')); var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); }); } }); }); </script>';

        return $return_str;

        /*'<script>

        $(document).ready(function() {

            $("#empMasterTable").DataTable({
                
                "lengthChange" : false,
                "processing" : true,
                "serverSide" : true,
                "serverMethod" : "post",
                "pagingType" : "first_last_numbers",
                "language": { 
                    "search" : "Search / Filter Records:",
                    "searchPlaceholder" : "Type here . . ."
                },
    
                "aoColumnDefs": [{
                    "bSortable": false,
                    "aTargets": ["nosort"]
                }],
                
                "ajax": { 
                    "url" : "'. $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/data-table-ajx"
                },
    
                "columns" : [
                    { data: "emp_code" },
                    { data: "name" },
                    { data: "user_type_id" },
                    { data: "email" },
                    { data: "mobile" },
                    { data: "status" },
                    { data: "action" },
                ],

                "drawCallback": function(settings) { 
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\')); 
                    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });
                }
                
            });
        });

        <script>';*/

    }


}

if(!function_exists('check_evidence_upload_strict'))
{
    function check_evidence_upload_strict( $type = null )
    {
        $res = null;

        if( $type != '' && check_evidence_upload_strict() )
        {
            if( $type == 'file_upload' )
            {
                $res = '<form id="evidence_upload_form" style="display:none;" enctype="multipart/form-data" data-action="'. EVIDENCE_UPLOAD['control_url'] .'upload">
                            <input type="text" name="evi_answer_id" id="evi_answer_id">
                            <input type="text" name="evi_annex_id" id="evi_annex_id">
                            <input type="file" name="evi_file" id="evi_file">
                        </form>' . "\n";
            }
        }
        else        
            $res = defined( 'EVIDENCE_UPLOAD' );

        return $res;
    }
}

if(!function_exists('get_evidence_upload_data'))
{
    function get_evidence_upload_data($assesmentData, $ansData, $ansIds, $type = 1, $extra = [])
    {
        require EVIDENCE_UPLOAD['controller'];

        // function call
        return get_evidence_controller_data($ENV_AT, $assesmentData, $ansData, $ansIds, $type, $extra);
    }
}

if(!function_exists('display_evidence_markup'))
{
    function display_evidence_markup($ansData, $col = 1)
    {
        $mrkUp = '';
        
        $col = ($col == 1) ? 'audit_evidence' : 'compliance_evidence';

        if(isset($ansData -> { $col }) && is_array($ansData -> { $col }))
        {
            foreach($ansData -> { $col } as $cEVId => $cEVDetails)
            {
                if( isset($cEVDetails -> evi_markup) )
                    $mrkUp .= $cEVDetails -> evi_markup;
            }
        }

        return $mrkUp;
    }
}

if(!function_exists('check_evidence_upload_compulsary_count'))
{
    function check_evidence_upload_compulsary_count($cnt, $checkData, $checkKey = 'audit')
    {
        $checkKey = $checkKey . '_evidence';

        if( !isset($checkData -> { $checkKey }) || 
            !is_array($checkData -> { $checkKey }))
            $cnt++;
        elseif( isset($checkData -> { $checkKey }) && 
                is_array($checkData -> { $checkKey }) && 
                sizeof($checkData -> { $checkKey }) > 0)
        {
            $activeEvi = false;
            

            foreach($checkData -> { $checkKey } as $cEviId => $cEviDetails)
            {
                if( in_array($cEviDetails -> status_id, [0, 1]) )
                    $activeEvi = true;
            }

            // no evidence found
            if(!$activeEvi)
                $cnt++;
        }

        return $cnt;
    }
}

if(!function_exists('unset_remark_options'))
{
    function unset_remark_options($assesmentData)
    {
        $remarkArray = [ 'remark_array' => [], 'remark_data' => null ];

        $remarkTypesArray = $GLOBALS['remarkTypesArray'];

        if(!is_array($remarkTypesArray) || !is_object($assesmentData))
            return null;

        // for auditor // audit & reaudit
        if(in_array($assesmentData -> audit_status_id, [1,3]))
            unset($remarkTypesArray[1], $remarkTypesArray[5]);

        // for reviewer // audit side
        elseif(in_array($assesmentData -> audit_status_id, [2]))
            unset($remarkTypesArray[2], $remarkTypesArray[4]);

        // for reviewer // compliance side
        elseif(in_array($assesmentData -> audit_status_id, [5]))
            unset($remarkTypesArray[1], $remarkTypesArray[2], $remarkTypesArray[4], $remarkTypesArray[5]);

        // for compliance // compliance side
        elseif(in_array($assesmentData -> audit_status_id, [4,6]))
            unset($remarkTypesArray[1], $remarkTypesArray[3], $remarkTypesArray[4], $remarkTypesArray[5]);

        $remarkArray['remark_array'] = $remarkTypesArray;

        // return array
        return $remarkArray;
    }
}

if(!function_exists('check_audit_remark_active_popup')) {
    function check_audit_remark_active_popup($data) {
        return (( isset($data['db_assesment']) && is_object($data['db_assesment']) ) || 
               ( isset($data['data']['db_assesment']) && is_object($data['data']['db_assesment']) ))
                 && isset($data['remarkTypeArray']);
    }

}

if (!function_exists('audit_assesment_not_started_common_code')) {

    function audit_assesment_not_started_common_code($branchDetails, $model) {

        $notStartedBranches = [];

        $currentDate = date("Y-m-d");

        $lastAssessmentDate = date("Y-m-d", strtotime($branchDetails->last_audit_date));

        if ($lastAssessmentDate < $currentDate) {

            $nextAssessmentDate = date_modify(date_create($lastAssessmentDate), '+' . $branchDetails->frequency . ' months');

            $nextAssessmentEndDate = date_format(date_modify(clone $nextAssessmentDate, '-1 days'), "Y-m-d");

            $currentDateDiff = date_diff(date_create($lastAssessmentDate), date_create($currentDate))->format("%m");

            $select = "SELECT audit_unit_id, frequency, assesment_period_from, assesment_period_to 
                    FROM audit_assesment_master 
                    WHERE audit_unit_id = " . $branchDetails->id . " 
                    AND assesment_period_to = '" . $nextAssessmentEndDate . "'";

            $detailsOfAssementData = get_all_data_query_builder(2, $model, 'audit_assesment_master', [], 'sql', $select);

            if (empty($detailsOfAssementData)) {
                $assessmentCount = max(floor($currentDateDiff / $branchDetails->frequency), 1);

                for ($i = 0; $i < $assessmentCount; $i++) {
                    $assment_start_date = date_modify(clone date_create($lastAssessmentDate), '+' . ($i * $branchDetails->frequency) . ' months +1 day');
                    $current_start_date_formatted = $assment_start_date->format("Y-m-d");
                    $current_end_date_formatted = date_format(date_modify(clone $assment_start_date, '+' . $branchDetails->frequency . ' months -1 day'), "Y-m-d");

                    $notStartedBranches[] = [
                        'id' => $branchDetails->id,
                        'name' => $branchDetails->name,
                        'last_audit_date' => $lastAssessmentDate,
                        'frequency' => $branchDetails->frequency,
                        'assesment_period' => "$current_start_date_formatted to $current_end_date_formatted",
                    ];
                }
            }
        }

        return $notStartedBranches;
    }
}

if (!function_exists('audit_assesment_not_started')) {
    
    function audit_assesment_not_started($auditData, $model) {
        $notStartedBranches = [];
    
        if (!empty($auditData)) {
            foreach ($auditData as $branchDetails) {
                $notStartedBranches = array_merge($notStartedBranches, audit_assesment_not_started_common_code($branchDetails, $model));
            }
        }
    
        return $notStartedBranches;
    }    
}

if (!function_exists('audit_unit_details_for_not_started')) {
    
    function audit_unit_details_for_not_started($query, $model) {
        
        $select = "SELECT id, audit_unit_code, name, frequency, last_audit_date FROM audit_unit_master";

        $select .= $query;

        $audit_data = [];
        $details_of_pending_audit_data = get_all_data_query_builder(2, $model, 'audit_unit_master', [], 'sql', $select);

        if(!empty($details_of_pending_audit_data)) 
        { 
            foreach ($details_of_pending_audit_data as $cUnitDetails) 
            {
                $audit_data[$cUnitDetails->id] = $cUnitDetails;
            }
        }
    
        return $audit_data;
    }    
}

if(!function_exists('calculate_days_diffrence'))
{
    function calculate_days_diffrence($date1, $date2) {

        // Convert dates to Unix timestamps
        $timestamp1 = strtotime($date1);
        $timestamp2 = strtotime($date2);

        // Calculate the difference in seconds
        $difference = abs($timestamp2 - $timestamp1);

        // Convert seconds to days
        $days_difference = floor($difference / (60 * 60 * 24)) + 1;

        return $days_difference;
    }
}

// 28.06.2024
if(!function_exists('getYearMasterData'))
{
    function getYearMasterData($thisObj, $extra = [])
    {
        $model = $thisObj -> model('YearModel');
        $filterArray = [ 'where' => '', 'params' => [] ];
        $single = true;

        if(isset($extra['id']) && !empty($extra['id']))
        {
            // for single ids
            $filterArray['where'] .= 'id = :id';
            $filterArray['params']['id'] = $extra['id'];
        }
        elseif(isset($extra['ids']) && !empty($extra['ids']))
        {
            // for multiple ids
            $filterArray['where'] .= 'id IN ('. implode(',', $extra['ids']) .')';
            $single = false;
        }
        elseif(isset($extra['year']) && !empty($extra['year']))
        {
            // for single year
            $filterArray['where'] .= 'year = :year';
            $filterArray['params']['year'] = $extra['year'];
        }
        elseif(isset($extra['years']) && !empty($extra['years']))
        {
            // for multiple ids
            $filterArray['where'] .= 'year IN ('. implode(',', $extra['years']) .')';
            $single = false;
        }

        // for deleted at
        if(isset($extra['deleted']))
            $filterArray['where'] .= (!empty($filterArray['where'] ) ? ' AND ' : '') . ' deleted_at IS NULL';

        if($single)
            return get_all_data_query_builder(1, $model, $model -> getTableName(), $filterArray, 'sql', "SELECT id, year FROM " . $model -> getTableName());

        // for multiple records
        return get_all_data_query_builder(2, $model, $model -> getTableName(), $filterArray, 'sql', "SELECT id, year FROM " . $model -> getTableName());
    }
}

// 28.06.2024
if(!function_exists('getTrendYearARiskData'))
{
    function getTrendYearARiskData($thisObj, $resArray)
    {
        $resArray['risk_category'] = null;

        // find year data and risk weightage // function call
        $yearData = getYearMasterData($thisObj, [ 'years' => array($resArray['p1_year'], $resArray['p2_year']) ]);

        if(is_array($yearData) && sizeof($yearData) > 0)
        {
            foreach($yearData as $cYearData)
            {
                // for period 1
                if($resArray['p1_year'] == trim_str($cYearData -> year) && !is_object($resArray['p1_year_obj']))
                    $resArray['p1_year_obj'] = $cYearData;

                // for period 2
                if($resArray['p2_year'] == trim_str($cYearData -> year) && !is_object($resArray['p2_year_obj']))
                    $resArray['p2_year_obj'] = $cYearData;
            }
        }

        $filterArray = [ 'where' => [], 'params' => [] ];

        if(is_object($resArray['p1_year_obj']))
            $filterArray['where'][] = $resArray['p1_year_obj'] -> id;

        if(is_object($resArray['p2_year_obj']))
            $filterArray['where'][] = $resArray['p2_year_obj'] -> id;

        // find risk data
        if(!empty($filterArray['where']))
        {
            // find risk category
            $model = $thisObj -> model('RiskCategoryModel');
            $resArray['risk_category'] = get_all_data_query_builder(2, $model, $model -> getTableName(), [ 'where' => 'is_active = 1 AND deleted_at IS NULL', 'params' => [] ], 'sql', "SELECT id, risk_category, risk_weight FROM " . $model -> getTableName());
            $resArray['risk_category'] = generate_data_assoc_array($resArray['risk_category'], 'id');

            $filterArray['where'] = 'year_id IN ('. implode(',', $filterArray['where'] ) .') AND is_active = 1 AND deleted_at IS NULL';

            $model = $thisObj -> model('RiskCategoryWeightModel');
            $riskData = get_all_data_query_builder(2, $model, $model -> getTableName(), $filterArray, 'sql', "SELECT id, year_id, risk_category_id, risk_weight FROM " . $model -> getTableName());

            if(is_array($riskData) && sizeof($riskData) > 0)
            {
                foreach($riskData as $cRiskData)
                {
                    $yearKey = null;

                    if($resArray['p1_year_obj'] -> id == $cRiskData -> year_id)
                        $yearKey = 'p1_risk';

                    if(!empty($yearKey))
                        $resArray[ $yearKey ][ $cRiskData -> risk_category_id ] = $cRiskData;
                    
                    if($resArray['p2_year_obj'] -> id == $cRiskData -> year_id)
                        $yearKey = 'p2_risk';

                    if(!empty($yearKey))
                        $resArray[ $yearKey ][ $cRiskData -> risk_category_id ] = $cRiskData;
                }
            }
        }

        // unset var
        unset($yearData);

        return $resArray;
    }
}

if( !function_exists('generate_CSV') )
{
    function generate_CSV($data, $fileName = 'file.csv' ) {
        
        // Set headers to indicate content type
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="'. $fileName .'"');

        $output = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }
}

if( !function_exists('get_branch_risk_category') )
{
    function get_branch_risk_category($auditUnitId, $score, $dbRatingArray)
    {
        $str = '';

        if( is_array($dbRatingArray) && 
            sizeof($dbRatingArray) > 0 && 
            array_key_exists($auditUnitId, $dbRatingArray))
        {
            $upperScore = 0;
            $lowerScore = 0;

            foreach($dbRatingArray[ $auditUnitId ] as $cMixedRate => $cRiskStr)
            {
                $temp = !empty($cMixedRate) ? explode('-', $cMixedRate) : [];

                if(sizeof($temp) == 1)
                {
                    $lowerBound = (float)$temp[0];

                    if ($score >= $lowerBound)
                        $str = $cRiskStr;

                    if ($lowerBound > $lowerScore)
                        $lowerScore = $lowerBound;
                }
                
                elseif(sizeof($temp) == 2)
                {
                    $upperBound = (float)$temp[0];
                    $lowerBound = (float)$temp[1];

                    if ($score <= $upperBound && $score > $lowerBound)
                        $str = $cRiskStr;

                    if ($upperBound > $upperScore)
                        $upperScore = $upperBound;

                    if ($lowerBound > $lowerScore)
                        $lowerScore = $lowerBound;
                }

                if(!empty($str))
                    break;
            }

            // Default high risk if not match
            if (empty($str) && ($lowerScore > 0 || $upperScore > 0))
            {
                // match with upper and lower
                if ($score >= $upperScore)
                    $str = 'High Risk';
                else if ($score <= $lowerScore)
                    $str = 'Low Risk';
            }
        }

        return string_operations($str, 'upper');
    }
}

if( !function_exists('get_cf_sorted_date') )
{
    function get_cf_sorted_date($cfData, $key = 'assesment', $dash = true)
    {
        $res = '';

        if( in_array($key, ['assesment', 'path', 'question', 'old_audit_comment', 'old_audit_compliance', 'old_compliance_reviewer_comment']) )
            $res = (isset($cfData[ $key ]) && !empty($cfData[ $key ])) ? $cfData[ $key ] : ERROR_VARS['notAvailable'];
        elseif( in_array($key, ['answer']) )
        {
            if( isset($cfData[ $key ]) && 
                !empty($cfData[ $key ]) )
            {
                $ansStr = '';

                try {

                    $tempJsonData = json_decode($cfData['answer'], 1);
                                            
                    if(is_array($tempJsonData))
                    {
                        $tempStr = '';
                        unset($tempJsonData['br'], $tempJsonData['cr'], $tempJsonData['rt']);

                        foreach($tempJsonData as $cJsonData) {
                            $tempStr .= $cJsonData . ', ';
                        }

                        if(!empty($tempStr)) 
                            $tempStr = substr($tempStr, 0, -2);

                        $ansStr .= $tempStr;
                        unset($tempStr);
                    }
                    else // general question
                        $ansStr .= $cfData['answer'];
                    
                } catch (Exception $e) { }

                if(empty($ansStr))
                    $ansStr .= ERROR_VARS['notAvailable'];

                $res = $ansStr;
                unset($ansStr);
            }           
        }

        if( empty($res) && $dash )
            $res = '-';

        return $res;
    }
}

if(!function_exists('check_carry_forward_strict'))
{
    function check_carry_forward_strict()
    {
        return defined( 'CARRY_FORWARD_ARRAY' );
    }
}

if(!function_exists('generate_cf_markup_row'))
{
    function generate_cf_markup_row($cCFData, $assesmentData = null, $extra = [])
    {
        $str = '';

        try {
                                
            $cfJsonData = json_decode($cCFData -> answer_given, 1);

            if(is_array($cfJsonData) && sizeof($cfJsonData) > 0)
            {
                // assesment data
                $str .= '<span class="d-block font-medium text-primary text-decoration-underline">'. get_cf_sorted_date($cfJsonData) .'</span>' . "\n";

                // path
                $str .= '<span class="d-block font-sm mb-2">Location: '. get_cf_sorted_date($cfJsonData, 'path') .'</span>' . "\n";

                // question
                $str .= '<span class="d-block mb-1"><span class="font-medium text-decoration-underline">Question:</span> '. get_cf_sorted_date($cfJsonData, 'question') .'</span>' . "\n";

                // answer
                $ansStr = '';
                
                $str .= '<span class="d-block mb-1"><span class="font-medium text-decoration-underline">Answer:</span> ';

                    $str .= get_cf_sorted_date($cfJsonData, 'answer');
                
                $str .= '</span>' . "\n";

                // old comment
                $str .= '<span class="d-block mb-1"><span class="font-medium text-primary text-decoration-underline">Old Audit Comment:</span> '. get_cf_sorted_date($cfJsonData, 'old_audit_comment') .'</span>' . "\n";

                // old compliance
                $str .= '<span class="d-block mb-1"><span class="font-medium text-primary text-decoration-underline">Old Compliance:</span> '. get_cf_sorted_date($cfJsonData, 'old_audit_compliance') .'</span>' . "\n";

                // compliance_reviewer_comment
                $str .= '<span class="d-block mb-2"><span class="font-medium text-primary text-decoration-underline">Reviewer Comment:</span> '. get_cf_sorted_date($cfJsonData, 'old_compliance_reviewer_comment') .'</span>' . "\n";

                // pending re assesment
                if( isset($extra['pending']) && 
                    is_object($assesmentData) && 
                    $assesmentData -> audit_status_id == 3 &&
                    $assesmentData -> batch_key != $cCFData -> batch_key)
                    $str .= '<p class="font-sm font-light text-primary mb-0">Error: Pending Re Assesment</p>' . "\n";
            }

        } catch (Exception $e) { /* no data found */ }

        return $str;
    }
}

// ON HOLD FUNCTIONS ------------------------------

if(!function_exists('check_on_hold_strict'))
{
    function check_on_hold_strict()
    {
        // check exists in action array
        return isset(AUDIT_STATUS_ARRAY['compliance_review_action'][4]);
    }
}

// find authority wise audit units 25.09.2024
if(!function_exists('get_authority_wise_audit_units'))
{
    function get_authority_wise_audit_units($this_obj, $empId, $extra = 'mix')
    {
        $res = null;

        // return empty response
        if(empty($empId)) return $res;

        // get emp details
        $model = $this_obj -> model('EmployeeModel');
        $table = $model -> getTableName();
        
        $res = get_all_data_query_builder(1, $model, $table, [
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $empId ]
        ], 'sql', "SELECT id, user_type_id, emp_code, gender, name, profile_pic, email, mobile, audit_unit_authority FROM " . $table );

        if(is_object($res) && in_array($res -> user_type_id, [2,4,6]))
        {
            // employee data found
            $cAuthority = null;
            $res -> audit_unit_data = [];

            if(!empty($res -> audit_unit_authority))
                $cAuthority = explode(',', $res -> audit_unit_authority);

            if(is_array($cAuthority) && sizeof($cAuthority) > 0)
            {
                // has authority data
                $model = $this_obj -> model('AuditUnitModel');
                $table = $model -> getTableName();

                $res -> audit_unit_data = get_all_data_query_builder(2, $model, $table, [
                    'where' => 'id IN ('. implode(',', $cAuthority) .') AND deleted_at IS NULL AND is_active = 1', 'params' => [ ]
                ], 'sql', "SELECT id, section_type_id, audit_unit_code, name, branch_head_id, branch_subhead_id, multi_compliance_ids, frequency, last_audit_date FROM " . $table );

                $res -> audit_unit_data = generate_data_assoc_array($res -> audit_unit_data, 'id');
            }
            
        }

        return $res;
    }
}

// function for sanitize file name
if(!function_exists('sanitize_file_name'))
{
    function sanitize_file_name($filename, $extension, $length = 200) {
        
        // Remove special characters and preserve dashes and slashes
        $filename = preg_replace('/[^a-zA-Z0-9\-\/ ]/', '-', $filename);
    
        // Convert to lowercase
        $filename = strtolower($filename);
    
        // Replace spaces with dashes
        $filename = str_replace(' ', '-', $filename);
    
        // Truncate the filename if longer than 200 characters
        if (strlen($filename) > 200) {
            $filename = substr($filename, 0, 200);
        }
    
        // Add unique ID and timestamp
        $uniqid = uniqid();
        $timestamp = date('YmdHis');
        
        // Combine the parts
        $filename = $filename . '-' . $uniqid . '-' . $timestamp . '.' . $extension;

        // Remove duplicate dashes
        $filename = preg_replace('/-+/', '-', $filename);
    
        return $filename;
    }
}

// function for sanitize file name
if(!function_exists('convert_masters_data'))
{
    function convert_masters_data($data, $extra = []) {

        $res = [ 'data' => null, 'select' => null ];

        if(!isset($extra['id'])) $extra['id'] = 'id';
        if(!isset($extra['selectKey'])) $extra['selectKey'] = 'id';
        if(!isset($extra['selectVal'])) $extra['selectVal'] = 'name';

        if(is_array($data) && sizeof($data) > 0)
        {
            foreach($data as $cData)
            {
                // push data
                $res['data'][ $cData -> { $extra['id'] } ] = $cData; 
                $res['select'][ $cData -> { $extra['selectKey'] } ] = $cData -> { $extra['selectVal'] }; 
            }
        }

        return $res;
    }
}
      



?>