<?php

// require other files
require_once APP_ROOT . '/Core/Notifications.php';
require_once APP_ROOT . '/Core/Session.php';

if(!function_exists('get_evi_assesment_details'))
{
    function get_evi_assesment_details($pdo, $assesId, $cols = '')
    {
        $res = null;

        if( empty($cols) )
            $cols = 'id, year_id, audit_unit_id, audit_status_id, assesment_period_from, assesment_period_to, is_limit_blocked';

        if($assesId != '')
        {
            // check for
            $stmt = $pdo -> prepare('SELECT '. $cols .' FROM audit_assesment_master WHERE id = :id AND deleted_at IS NULL');

            $stmt -> bindParam(':id', $assesId);
            $stmt -> execute();
            $res = $stmt -> fetch(PDO::FETCH_OBJ);
        }

        return $res;
    }
}

if(!function_exists('check_ev_answer'))
{
    function check_ev_answer($pdo, $ENV_AT, $ans_id, $annex_id = null, $extra = [])
    {
        $res = null;

        if( !isset($extra['annex']) ) $extra['annex'] = false;

        if(!empty($ans_id))
        {
            if(!empty($annex_id) && $extra['annex'])
            {
                // join query for ans and annex
                $stmt = $pdo -> prepare("SELECT ans.id ans_id, annex.id annex_id FROM " . $ENV_AT['annex_ans_table'] . " annex JOIN " . $ENV_AT['answer_table'] . " ans ON annex.answer_id = ans.id WHERE ans.deleted_at IS NULL AND annex.deleted_at IS NULL AND ans.assesment_id = :assesment_id AND annex.assesment_id = :assesment_id AND ans.id = :ans_id AND annex.id = :annex_id");

                $stmt -> bindParam(':assesment_id', $ENV_AT['ass_details'] -> id);
                $stmt -> bindParam(':ans_id', $ans_id);
                $stmt -> bindParam(':annex_id', $annex_id);

                $stmt -> execute();
                $res = $stmt -> fetch(PDO::FETCH_OBJ);

            }
            elseif( !$extra['annex'] )
            {
                $stmt = $pdo -> prepare("SELECT ans.id ans_id FROM " . $ENV_AT['answer_table'] . " ans WHERE deleted_at IS NULL AND assesment_id = :assesment_id AND id = :ans_id");
                $stmt -> bindParam(':assesment_id', $ENV_AT['ass_details'] -> id);
                $stmt -> bindParam(':ans_id', $ans_id);

                $stmt -> execute();
                $res = $stmt -> fetch(PDO::FETCH_OBJ);
            }
        }

        return $res;
    }
}

if(!function_exists('check_ev_file_move'))
{
    function check_ev_file_move($FILES, $ENV_AT, $res)
    {
        $file = $FILES['evi_file'];
        $fileParts = pathinfo($file["name"]);

        // Validate file type and size
        if( in_array($file['type'], array_values(EVIDENCE_UPLOAD['file_types'])) && 
            $file['size'] <= (EVIDENCE_UPLOAD['size'] * 1024 * 1024) ) 
        {
            $uploadDir = generate_evi_dir_name($ENV_AT['ass_details'] -> id);
            $fileTypeId = array_search($file['type'], EVIDENCE_UPLOAD['file_types']);
            
            // if folder not exists create folder with permission
            if (!is_dir(EVIDENCE_UPLOAD['upload_dir'] . $uploadDir)) mkdir(EVIDENCE_UPLOAD['upload_dir'] . $uploadDir, 0755, true);

            $uploadFile = uniqid() . '-' . date('YmdHis') . '.' . $fileParts['extension'];

            if ($fileTypeId !== false && move_uploaded_file($file['tmp_name'], EVIDENCE_UPLOAD['upload_dir'] . $uploadDir . $uploadFile)) {

                // move success
                $res['err'] = false;
                $res['file'] = $uploadFile;
                $res['file_type'] = $fileTypeId;
                $res['dir'] = $uploadDir;
                $res['full_location'] = EVIDENCE_UPLOAD['upload_dir'] . $uploadDir . $uploadFile;
            } 
            else
            {
                // error file move
                $res['msg'] = 'Error: Failed to move uploaded file.';
            }
        } 
        else 
        {
            $res['msg'] = 'Error: Invalid file. ';

            if( !in_array($file['type'], EVIDENCE_UPLOAD['file_types']) )
                $res['msg'] .= 'Only JPG, JPEG, PNG, and PDF files are allowed. ';
            
            if( $file['size'] > (EVIDENCE_UPLOAD['size'] * 1024 * 1024) )
                $res['msg'] .= 'File size exceeds the maximum limit of '. EVIDENCE_UPLOAD['size'] .'MB.';
        }

        return $res;
    }
}

if(!function_exists('remove_ev_file'))
{
    function remove_ev_file($res, $ENV_AT)
    {
        if(!empty($res['file']) && !empty($res['file']) && !empty($res['full_location']) && 
            file_exists($res['full_location']))
            unlink($res['full_location']); //remove image
        else
            $res = [ 'err' => true, 'msg' => $ENV_AT['noti']::getNoti('somethingWrong'), 'markup' => '' ];

        return $res;
    }
}

if(!function_exists('insert_ev_file_record'))
{
    function insert_ev_file_record($pdo, $insertArray)
    {
        // Auto update timestamps
        $insertArray['created_at'] = date('Y-m-d H:i:s');
        $insertArray['updated_at'] = date('Y-m-d H:i:s');

        $stmt = $pdo -> prepare("INSERT INTO evidence_master (answer_id, annex_id, assesment_id, evi_type, file_name, file_type, description, emp_id, status_id, created_at, updated_at) VALUES ('". $insertArray['answer_id'] ."', '". $insertArray['annex_id'] ."', '". $insertArray['assesment_id'] ."', '". $insertArray['evi_type'] ."', '". $insertArray['file_name'] ."', '". $insertArray['file_type'] ."', '". $insertArray['description'] ."', '". $insertArray['emp_id'] ."', '". $insertArray['status_id'] ."','". $insertArray['created_at'] ."', '". $insertArray['updated_at'] ."')");

        // Execute the query and check if it was successful
        if ( $stmt -> execute() )
            return $pdo -> lastInsertId();

        return null;
    }
}

if(!function_exists('remove_ev_file_record'))
{
    function remove_ev_file_record($ENV_AT, $insertId)
    {
        $deleted_at = date('Y-m-d H:i:s');
        $stmt = $ENV_AT['db_evi'] -> prepare("UPDATE evidence_master SET deleted_by_emp_id = '". $ENV_AT['session']::get('emp_id') ."', deleted_at = '". $deleted_at ."' WHERE id = '". $insertId ."' AND deleted_at IS NULL");
        return $stmt -> execute() ? 1 : 0;
    }
}

if(!function_exists('generate_evi_dir_name'))
{
    function generate_evi_dir_name($assesId) {
        return EVIDENCE_UPLOAD['upload_folder_create'] . $assesId . '/';
    }
}

if(!function_exists('get_ev_file_records'))
{
    function get_ev_file_records($ENV_AT, $extra = [])
    {
        $pdo = null;

        // connect database
        if(empty($pdo)) {
            $ENV_AT = connect_to_evi_database($ENV_AT);
            $pdo = $ENV_AT['db_evi'];
        }

        if(isset($extra['cols1']))
            $extra['cols'] = $extra['cols1'];
        elseif(!isset($extra['cols']))
            $extra['cols'] = '*';

        $res = null;
        $query = "SELECT ". $extra['cols'] ." FROM evidence_master";

        if( isset($extra['where']) )
            $query .= " WHERE " . $extra['where'];

        $stmt = $pdo -> prepare($query);
        $stmt -> execute();

        if( $stmt->rowCount() > 0 )
        {
            if(isset($extra['single']))
                $res = $stmt -> fetch(PDO::FETCH_OBJ);
            else
                $res = $stmt -> fetchAll(PDO::FETCH_OBJ);
        }
        
        return $res;
    }
}

if(!function_exists('update_ans_data'))
{
    function update_ans_data( $pdo, $updateArr, $ENV_AT, $extra = [] )
    {
        $res = false;

        try {

            if(isset($extra['annex_id']) && isset($extra['ans_id']))
                $query = "UPDATE " . $ENV_AT['annex_ans_table'] . " SET " . $updateArr ." WHERE id = '". $extra['annex_id'] ."' AND answer_id = '". $extra['ans_id'] ."' AND assesment_id = '". $ENV_AT['ass_details'] -> id ."' AND deleted_at IS NULL";
            elseif(isset($extra['ans_id']))
                $query = "UPDATE " . $ENV_AT['answer_table'] . " SET " . $updateArr ." WHERE id = '". $extra['ans_id'] ."' AND assesment_id = '". $ENV_AT['ass_details'] -> id ."' AND deleted_at IS NULL";

            // echo $query;

            $stmt = $pdo -> prepare( $query );
            $res = $stmt -> execute() ? 1 : 0;

        }
        catch(Exception $e) { $res = false; }

        return $res;
    }
}

if(!function_exists('connect_to_evi_database'))
{
    function connect_to_evi_database($ENV_AT)
    {
        if (empty($ENV_AT['db_risk']) || empty($ENV_AT['db_evi']))
        {
            // connect both database
            $ENV_AT['db_risk'] = new PDO( 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS );
            $ENV_AT['db_risk'] -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $ENV_AT['db_evi'] = new PDO( 'mysql:host=' . EVIDENCE_UPLOAD['database']['db_host'] . ';dbname=' . EVIDENCE_UPLOAD['database']['db_name'], EVIDENCE_UPLOAD['database']['db_user'], EVIDENCE_UPLOAD['database']['db_pass'] );
            $ENV_AT['db_evi'] -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }      

        return $ENV_AT;
    }
}

if(!function_exists('close_evi_database'))
{
    function close_evi_database($ENV_AT)
    {
        // close connection
        $ENV_AT['db_evi'] = null; $ENV_AT['db_risk'] = null;
        return $ENV_AT;
    }
}

if(!function_exists('generate_evi_markup'))
{
    function generate_evi_markup($eviData, $dir, $extra = [])
    {
        $mrkup = '';

        if(empty($extra)) $extra = [ 'wrapper' => 'default' ];

        if(is_array($eviData) || is_object($eviData))
        {
            if(is_array($eviData))
                $eviData = (object)$eviData;

            // if assesment not exists
            if(!isset($extra['assesment']))
                $extra['assesment'] = null;

            if(isset($extra['wrapper']))
                $mrkup = '<div class="'. (($extra['wrapper'] == 'default') ? 'evidence-uploader' : $extra['wrapper']) .'">' . "\n";

                $mrkup .= '<ul class="evidence-status">
                    <li class="font-medium text-danger">File: '. $eviData -> file_name . ($eviData -> status_id == 1 ? ' ( Evidence Accepted )' : '') .'</li>
                    <li><a href="'. (EVIDENCE_UPLOAD['upload_url'] . $dir . $eviData -> file_name) .'" target="_blank">View</a></li>';

                    if( is_object($extra['assesment']) && 
                        in_array($extra['assesment'] -> audit_status_id, [1,3,4,6]) && 
                        $eviData -> status_id == 0 )
                    {
                        if( in_array($extra['assesment'] -> audit_status_id, [1,3]) || 
                        (in_array($extra['assesment'] -> audit_status_id, [4,6]) && $eviData -> evi_type == 2))
                            $mrkup .= '<li><a class="remove-evi-upload" href="'. EVIDENCE_UPLOAD['control_url'] . 'remove/' . encrypt_ex_data($eviData -> id) .'">Remove</a></li>' . "\n";
                    }
                    elseif( is_object($extra['assesment']) && in_array($extra['assesment'] -> audit_status_id, [2,5]) )
                    {
                        // if assesement send to compliance // remove status button for audit evidence
                        if( ($eviData -> evi_type == 1 && $extra['assesment'] -> audit_status_id == 2) || $eviData -> evi_type == 2)
                        {
                            $statusText = ( $eviData -> status_id == 1 ) ? 'Accepted' : 'Rejected';
                            $statusText = ( $eviData -> status_id == 0 ) ? 'Accept' : $statusText;

                            $mrkup .= '<li><a class="reviewer-status-evi-upload" href="'. EVIDENCE_UPLOAD['control_url'] . 'status/' . encrypt_ex_data($eviData -> id) .'">'. $statusText .'</a></li>' . "\n";
                        }
                    }
            $mrkup .= '</ul>';

            if(isset($extra['wrapper']))
                $mrkup .= '</div>' . "\n";
        }

        return $mrkup;
    }
}

if(!function_exists('get_evidence_controller_data'))
{
    function get_evidence_controller_data($ENV_AT, $assesmentData, $ansData, $ansIds, $type = 1, $extra = [])
    {
        $cols = 'id, answer_id, annex_id, evi_type, file_name, file_type, status_id';
        $findCol = 'answer_id IN ('. implode(',', $ansIds) .') AND annex_id = 0';

        // for annex
        if($type == 2) $findCol = 'annex_id IN ('. implode(',', $ansIds) .')';

        $whereArray = [ 
            'where' => $findCol . ' AND assesment_id = "'. $assesmentData -> id .'" AND deleted_at IS NULL',
            'cols1' => $cols 
        ];

        // FOR RE AUDIT 13.08.2024
        if( in_array($assesmentData -> audit_status_id, [3]) )
            $whereArray['where'] .= ' AND status_id != "2"';

        if( in_array($assesmentData -> audit_status_id, [1,3]) )
            $whereArray['where'] .= ' AND evi_type = "1"'; //'. $eviType .'

        if(!empty($extra) && isset($extra['where']))
            $whereArray['where'] .= ' AND '. $extra['where'];

        // function call
        $eviResponse = get_ev_file_records($ENV_AT, $whereArray);

        if(is_array($eviResponse) && sizeof($eviResponse))
        {   
            foreach($eviResponse as $cEviDetails)
            {
                if(array_key_exists($cEviDetails -> answer_id, $ansData))
                {
                    $cEviDetails -> evi_markup = generate_evi_markup($cEviDetails, generate_evi_dir_name($assesmentData -> id), [ 
                        'assesment' => $assesmentData, 'wrapper' => 'default',
                    ]);
                    
                    $eviTypeKey = ($cEviDetails -> evi_type == 1) ? 'audit_evidence' : 'compliance_evidence';

                    // answer data exists
                    if($type == 1)
                    {
                        // for ans
                        if(!isset($ansData[ $cEviDetails -> answer_id ] -> audit_evidence))
                        {
                            $ansData[ $cEviDetails -> answer_id ] -> audit_evidence = [];
                            $ansData[ $cEviDetails -> answer_id ] -> compliance_evidence = [];
                        }

                        // push ans
                        $ansData[ $cEviDetails -> answer_id ] -> { $eviTypeKey }[ $cEviDetails -> id ] = $cEviDetails;
                    }
                    else
                    {
                        // for annex
                        if( isset($ansData[ $cEviDetails -> answer_id ] -> annex_ans) && 
                            is_array($ansData[ $cEviDetails -> answer_id ] -> annex_ans) && 
                            array_key_exists($cEviDetails -> annex_id, $ansData[ $cEviDetails -> answer_id ] -> annex_ans) )
                        {
                            if(!isset($ansData[ $cEviDetails -> answer_id ] -> annex_ans[ $cEviDetails -> annex_id ] -> audit_evidence))
                            {
                                $ansData[ $cEviDetails -> answer_id ] -> annex_ans[ $cEviDetails -> annex_id ] -> audit_evidence = [];
                                $ansData[ $cEviDetails -> answer_id ] -> annex_ans[ $cEviDetails -> annex_id ] -> compliance_evidence = [];
                            }

                            // push ans
                            $ansData[ $cEviDetails -> answer_id ] -> annex_ans[ $cEviDetails -> annex_id ] -> { $eviTypeKey }[ $cEviDetails -> id ] = $cEviDetails;
                        }
                        elseif( array_key_exists($cEviDetails -> answer_id, $ansData) && 
                                array_key_exists($cEviDetails -> annex_id, $ansData[ $cEviDetails -> answer_id ]) && $type == 2)
                        {
                            if(!isset($ansData[ $cEviDetails -> answer_id ][ $cEviDetails -> annex_id ] -> audit_evidence))
                            {
                                $ansData[ $cEviDetails -> answer_id ][ $cEviDetails -> annex_id ] -> audit_evidence = [];
                                $ansData[ $cEviDetails -> answer_id ][ $cEviDetails -> annex_id ] -> compliance_evidence = [];
                            }

                            // push ans
                            $ansData[ $cEviDetails -> answer_id ][ $cEviDetails -> annex_id ] -> { $eviTypeKey }[ $cEviDetails -> id ] = $cEviDetails;
                        }
                    }
                }
                
            }
        }

        // close evi database
        $ENV_AT = close_evi_database($ENV_AT);
        unset($ENV_AT, $GLOBALS['ENV_AT']);

        // return response
        return $ansData;
    }
}

if(!function_exists('change_compulsary_evi_upload_status_answer'))
{
    function change_compulsary_evi_upload_status_answer($ENV_AT, $res, $postData, $type = 1)
    {
        // $type = 1 for ans // 2 for annex
        $ASSID = $ENV_AT['session']::get('audit_id');
        $ASSID = ($ASSID != '') ? decrypt_ex_data($ASSID) : $ASSID;

        $postData['ans_id'] = isset($postData['ans_id']) ? decrypt_ex_data($postData['ans_id']) : 0;
        $postData['annex_id'] = isset($postData['annex_id']) ? decrypt_ex_data($postData['annex_id']) : 0;
        $postData['ans_type'] = isset($postData['ans_type']) ? $postData['ans_type'] : 0;
        $postData['checkbox'] = isset($postData['checkbox']) ? $postData['checkbox'] : 0;

        if(empty($postData['ans_type']))
            return $res;
        if( $type == 1 && empty($postData['ans_id']) )
            return $res;
        elseif( $type == 2 && (empty($postData['ans_id']) || empty($postData['annex_id'])) )
            return $res;

        $ENV_AT['ass_details'] = get_evi_assesment_details($ENV_AT['db_risk'], $ASSID);

        // only for reviewer no other user type
        if( is_object($ENV_AT['ass_details']))
        {
            // assesment data found // file upload
            $is_annex = ($type == 2);
            $ansData = check_ev_answer($ENV_AT['db_risk'], $ENV_AT, $postData['ans_id'], $postData['annex_id'], ['annex' => $is_annex]);

            if(is_object($ansData))
            {
                $postData['checkbox'] = ($postData['checkbox'] == 'true') ? 1 : 0;

                if(in_array($ENV_AT['ass_details'] -> audit_status_id, [1]) && $postData['checkbox'] == 1)
                    $postData['checkbox'] = 2;

                if( $postData['ans_type'] == 1) // for audit
                    $updateAnsArr = "audit_compulsary_ev_upload = '". $postData['checkbox'] ."', audit_reviewer_emp_id = '". $ENV_AT['session']::get('emp_id') ."'";
                else // for complinace
                    $updateAnsArr = "compliance_compulsary_ev_upload = '". $postData['checkbox'] ."', compliance_reviewer_emp_id = '". $ENV_AT['session']::get('emp_id') ."'";

                // check for annex ans update
                if($is_annex)
                    $updateAns = update_ans_data( $ENV_AT['db_risk'], $updateAnsArr, $ENV_AT, [ 'ans_id' => $postData['ans_id'], 'annex_id' => $postData['annex_id'] ] );
                else
                    $updateAns = update_ans_data( $ENV_AT['db_risk'], $updateAnsArr, $ENV_AT, [ 'ans_id' => $postData['ans_id'] ] );

                if($updateAns)
                {
                    // has updated
                    $res['err'] = false;
                    $res['msg'] = $ENV_AT['noti']::getNoti($postData['checkbox'] ? 'checkChecked' : 'checkRemoved'); 
                    // $res['markup'] = $postData['ans_type']. $updateAnsArr;
                    $res['markup'] = EVIDENCE_UPLOAD['checkbox_text'] .' '. ( ($postData['ans_type'] == 1) ? '(Audit)' : '(Compliance)'); 
                }
            }
            else // ans data not found
                $res['msg'] = $ENV_AT['noti']::getNoti('ansDataNotFound'); 
        }
        else
            $res['msg'] = $ENV_AT['noti']::getNoti('assesmentNotFound');

        return $res;
    }
}

$ENV_AT = [ 'answer_table' => 'answers_data', 'annex_ans_table' => 'answers_data_annexure', 'db_risk' => null, 'db_evi' => null ];

$ENV_AT['noti'] = new Core\Notifications();
$ENV_AT['session'] = new Core\Session();

$res = [ 'err' => true, 'msg' => $ENV_AT['noti']::getNoti('somethingWrong'), 'markup' => '' ];

// connect database
$ENV_AT = connect_to_evi_database($ENV_AT);

// check database connection
if (!$ENV_AT['db_risk'] || !$ENV_AT['db_evi'])
{
    $res['msg'] = 'Error: Failed to connect database! Please try again';
    echo json_encode($res);
    exit;
}

if( isset($url) && is_array($url) && !empty($url['method']) )
{
    switch($url['method'])
    {
        case 'upload': 
        {
            $ASSID = $ENV_AT['session']::get('audit_id');
            $ASSID = ($ASSID != '') ? decrypt_ex_data($ASSID) : $ASSID;

            $ENV_AT['ass_details'] = get_evi_assesment_details($ENV_AT['db_risk'], $ASSID);

            if(is_object($ENV_AT['ass_details']))
            {
                // assesment data found // file upload

                if ($_SERVER['REQUEST_METHOD'] === 'POST')
                {
                    // check for post data
                    if(isset($_FILES['evi_file']) && isset($_POST['evi_answer_id']) && isset($_POST['evi_annex_id']))
                    {
                        $answer_id = ($_POST['evi_answer_id'] != 0) ? decrypt_ex_data($_POST['evi_answer_id']) : null;
                        $annex_id = ($_POST['evi_annex_id'] != 0) ? decrypt_ex_data($_POST['evi_annex_id']) : 0;
                        $is_annex = isset($_POST['evi_annex']);

                        // check answer exists or not
                        $ansData = check_ev_answer($ENV_AT['db_risk'], $ENV_AT, $answer_id, $annex_id, ['annex' => $is_annex]);

                        if(is_object($ansData))
                        {
                            // ans data found // move file
                            $res = check_ev_file_move($_FILES, $ENV_AT, $res);

                            if(!$res['err'])
                            {
                                // check evidence uploaded or not // each answer has only 1 evidence upload facility
                                $where = "answer_id = '". $ansData -> ans_id ."'";

                                if(isset($ansData -> annex_id))
                                    $where .= " AND annex_id = '". $ansData -> annex_id ."'";
                                else
                                    $where .= " AND annex_id = '0'";

                                $where .= " AND evi_type = '". (in_array($ENV_AT['ass_details'] -> audit_status_id, [1,3]) ? 1 : 2) ."' AND deleted_at IS NULL";

                                $eviAns = get_ev_file_records($ENV_AT, [ 'single' => 1, 'where' => $where ]);

                                if(is_object($eviAns) && !in_array($eviAns -> status_id, [2]))
                                {
                                    $res['err'] = true;
                                    $res['msg'] = 'Error: Evidence document already uploaded! Please remove and try again';
                                }
                            }

                            if(!$res['err'])
                            {
                                // file move success fully

                                // insert new table
                                $insertArray = [
                                    "answer_id" => $answer_id,
                                    "annex_id" => $annex_id, 
                                    "assesment_id" => $ENV_AT['ass_details'] -> id, 
                                    "evi_type" => (in_array($ENV_AT['ass_details'] -> audit_status_id, [1,3]) ? 1 : 2),
                                    "file_name" => $res['file'], 
                                    "file_type" => $res['file_type'], 
                                    "description" => null, 
                                    "emp_id" => $ENV_AT['session']::get('emp_id'),
                                    "status_id" => 0
                                ];

                                // function call
                                $lastId = insert_ev_file_record($ENV_AT['db_evi'], $insertArray);

                                // remove image // function call
                                if(empty($lastId)) remove_ev_file($res, $ENV_AT);

                                if(in_array($ENV_AT['ass_details'] -> audit_status_id, [1,3]))
                                    $updateAnsArr = "audit_evidance_upload = '" . json_encode([ "id" => $lastId ]) . "'";
                                else
                                    $updateAnsArr = "compliance_evidance_upload = '" . json_encode([ "id" => $lastId ]) . "'";

                                if(isset($ansData -> annex_id))
                                {
                                    // check for annex ans update
                                    $updateAns = update_ans_data( $ENV_AT['db_risk'], $updateAnsArr, $ENV_AT, [ 'ans_id' => $answer_id, 'annex_id' => $ansData -> annex_id ] );
                                }
                                else
                                {
                                    // check for only ans data update
                                    $updateAns = update_ans_data( $ENV_AT['db_risk'], $updateAnsArr, $ENV_AT, [ 'ans_id' => $answer_id ] );
                                }

                                // if fail query
                                if(!$updateAns)
                                {
                                    $res['err'] = true;
                                    remove_ev_file_record($ENV_AT, $lastId);
                                }
                                else
                                {
                                    // all ok return response
                                    $res['err'] = false;
                                    $res['msg'] = 'success file move';

                                    $insertArray['id'] = $lastId;
                                    $insertArray = (object)$insertArray;

                                    $res['markup'] = generate_evi_markup($insertArray, generate_evi_dir_name($ENV_AT['ass_details'] -> id), [ 
                                        'assesment' => $ENV_AT['ass_details'], 'wrapper' => 'default'
                                    ]);
                                }

                                // unset values
                                unset($res['file'], $res['file_type'], $res['dir'], $res['full_location']);
                            }
                        
                        }
                        else // ans data not found
                            $res['msg'] = $ENV_AT['noti']::getNoti('ansDataNotFound');                        
                    }
                    else
                        $res['msg'] = $ENV_AT['noti']::getNoti('invalidRequestError');
                }
            }
            else
                $res['msg'] = $ENV_AT['noti']::getNoti('assesmentNotFound');

            break;
        }

        case 'remove' : {

            $eviId = explode('/', $_GET['url']);
            $eviUploadDetails = null;

            $ASSID = $ENV_AT['session']::get('audit_id');
            $ASSID = ($ASSID != '') ? decrypt_ex_data($ASSID) : $ASSID;

            if(sizeof($eviId) == 3)
            {
                $eviId = decrypt_ex_data($eviId[2]);
                $eviUploadDetails = get_ev_file_records($ENV_AT, [ 'single' => 1, 'where' => "id = '". $eviId ."' AND assesment_id = '". $ASSID ."' AND deleted_at IS NULL" ]);
            }

            // find evi data
            if(is_object($eviUploadDetails))
            {
                $ENV_AT['ass_details'] = get_evi_assesment_details($ENV_AT['db_risk'], $ASSID);

                // if status accepted / rejected do not remove evidence
                if( is_object($ENV_AT['ass_details']) && 
                    (in_array($ENV_AT['ass_details'] -> audit_status_id, [1,3]) && $eviUploadDetails -> evi_type == 1 && $eviUploadDetails -> status_id == 0) || 
                    (in_array($ENV_AT['ass_details'] -> audit_status_id, [4,6]) && $eviUploadDetails -> evi_type == 2 && $eviUploadDetails -> status_id == 0))
                {
                    // assesment data found // file upload
                    $res['err'] = remove_ev_file_record($ENV_AT, $eviUploadDetails -> id);

                    if($res['err'])
                    {
                        $updateAnsArr = "audit_evidance_upload = NULL";
                        $res['err'] = false;
                        $res['msg'] = 'Success: Evidence removed successfully!';
                        
                        // remove or update ans data
                        if($eviUploadDetails -> annex_id != 0)
                        {
                            // check for annex ans update
                            $updateAns = update_ans_data( $ENV_AT['db_risk'], $updateAnsArr, $ENV_AT, [ 'ans_id' => $eviUploadDetails -> answer_id, 'annex_id' => $eviUploadDetails -> annex_id ] );
                        }
                        else
                        {
                            // check for only ans data update
                            $updateAns = update_ans_data( $ENV_AT['db_risk'], $updateAnsArr, $ENV_AT, [ 'ans_id' => $eviUploadDetails -> answer_id ] );
                        }
                    }
                    else
                        $res['err'] = true;
                }
                else
                    $res['msg'] = $ENV_AT['noti']::getNoti('assesmentNotFound');
            }
            else
                $res['msg'] = $ENV_AT['noti']::getNoti('errorFinding');

            break;
        }

        case 'status' : {

            $eviId = explode('/', $_GET['url']);
            $eviUploadDetails = null;

            $ASSID = $ENV_AT['session']::get('audit_id');
            $ASSID = ($ASSID != '') ? decrypt_ex_data($ASSID) : $ASSID;

            if(sizeof($eviId) == 3)
            {
                $eviId = decrypt_ex_data($eviId[2]);
                $eviUploadDetails = get_ev_file_records($ENV_AT, [ 'single' => 1, 'where' => "id = '". $eviId ."' AND assesment_id = '". $ASSID ."' AND deleted_at IS NULL" ]);
            }

            // find evi data
            if(is_object($eviUploadDetails))
            {
                $ENV_AT['ass_details'] = get_evi_assesment_details($ENV_AT['db_risk'], $ASSID);

                // only for reviewer no other user type
if (
    is_object($ENV_AT['ass_details']) && ( 
        ($eviUploadDetails->evi_type == 1 && $ENV_AT['ass_details']->audit_status_id == 2) || 
        ($eviUploadDetails->evi_type == 2 && $ENV_AT['ass_details']->audit_status_id == 5)
    )
)
{
    // If currently accepted (1), change to rejected (0)
    // Otherwise accept (1)
    $statusId = ($eviUploadDetails->status_id == 1) ? 0 : 1;

    $updated_at = date('Y-m-d H:i:s');

    $stmt = $ENV_AT['db_evi'] -> prepare("UPDATE evidence_master SET status_id = '". $statusId ."', review_emp_id = '". $ENV_AT['session']::get('emp_id') ."', updated_at = '". $updated_at ."' WHERE id = '". $eviUploadDetails->id ."' AND deleted_at IS NULL");

    if ($stmt->execute())
    {
        $res['err'] = false;

        if ($statusId == 1)
        {
            // accepted evidence
            $res['msg'] = '<b>Success:</b> Evidence accepted successfully!';
            $eviUploadDetails->status_id = 1;
        }
        else
        {
            // rejected evidence
            $res['msg'] = '<b>Success:</b> Evidence rejected successfully!';
            $eviUploadDetails->status_id = 0;

            $updateAnsArr = "audit_status_id = 3,
                            audit_reviewer_emp_id = '". $ENV_AT['session']::get('emp_id') ."',
                            status_id = 0";

            if ($eviUploadDetails->evi_type == 2) {
                $updateAnsArr = "compliance_status_id = 3,
                                compliance_reviewer_emp_id = '". $ENV_AT['session']::get('emp_id') ."',
                                status_id = 0";
            }

            if ($eviUploadDetails->annex_id != 0)
            {
                update_ans_data(
                    $ENV_AT['db_risk'],
                    $updateAnsArr,
                    $ENV_AT,
                    [
                        'ans_id'   => $eviUploadDetails->answer_id,
                        'annex_id' => $eviUploadDetails->annex_id
                    ]
                );
            }

            update_ans_data(
                $ENV_AT['db_risk'],
                $updateAnsArr,
                $ENV_AT,
                [ 'ans_id' => $eviUploadDetails->answer_id ]
            );
        }

        $res['status_id'] = $statusId;
        $res['annex']     = ($eviUploadDetails->annex_id != 0);

        $res['markup'] = generate_evi_markup(
            $eviUploadDetails,
            generate_evi_dir_name($ENV_AT['ass_details']->id),
            [ 'assesment' => $ENV_AT['ass_details'] ]
        ) . '<div class="text-success font-sm">'. $res['msg'] .'</div>';
    }
    else
    {
        $res['msg'] = $ENV_AT['noti']::getNoti('errorSaving');
    }
}
                else
                    $res['msg'] = $ENV_AT['noti']::getNoti('assesmentNotFound');
            }
            else
                $res['msg'] = $ENV_AT['noti']::getNoti('errorFinding');

            break;
        }

        case 'ans-upload-status' : {
            $res = change_compulsary_evi_upload_status_answer($ENV_AT, $res, $_POST);
            break;
        }

        case 'annex-upload-status' : {
            $res = change_compulsary_evi_upload_status_answer($ENV_AT, $res, $_POST, 2);
            break;
        }
    }
}

if( isset($url) && is_array($url) && !empty($url['method']) )
{
    $ENV_AT = close_evi_database($ENV_AT);
    echo json_encode($res);
    exit;
}

?>