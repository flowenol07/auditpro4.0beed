<?php

namespace Controllers\Audit;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

class AssesmentRemarkMaster extends Controller  {

    public $me = null, $remarkId, $data, $request, $annexId, $rmkId;
    public $remarkModel, $annexureColumnModel;

    public function __construct($me) { 

        $this -> me = $me; 
    
        // request object created
        $this -> request = new Request();

        $this -> remarkModel = $this -> model('AuditRemarkModel');
    }

    private function validateData($assesmentData, $methodType = 'add')
    {
        $remark_array = unset_remark_options($assesmentData);

        $validationArray = [
            'rmk_noti_type' => 'required|array_key[remark_array, auditRemarkType]',
            'rmk_subject' => 'required',
            'rmk_message' => 'required'
        ];
        
        Validation::validateData($this -> request, $validationArray, [
            'remark_array' => $remark_array['remark_array']
        ]);

        // validation check
        if($this -> request -> input( 'error' ) > 0)  
            return false;
        else 
            return true;
    }

    private function postArray($methodType = 'add')
    {
        $dataArray = array(
            'subject' => $this -> request -> input('rmk_subject'),
            'message' => $this -> request -> input('rmk_message'),
            'noti_type' => $this -> request -> input('rmk_noti_type'),
            'assesment_id' => decrypt_ex_data(Session::get('audit_id')),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index($getRequest) 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('annexureMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('annexureColumns') . '/add?annex=' . encrypt_ex_data($this -> annexId) ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> annexureColumnModel, 
            $this -> annexureColumnModel -> getTableName(), [
                'where' => 'deleted_at IS NULL AND annexure_id =:annexure_id',
                'params' => [ 'annexure_id' => $this -> annexId ]]);

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view 
        //helper function call
        return return2View($this, $this -> me -> viewDir . 'index', ['request' => $this -> request]);
    }

    public function add()
    {
        $res = ['err' => true, 'msg' => Notifications::getNoti('somethingWrong') ];

        // method call
        $assesmentData = $this -> getAssesmentData();

        if(is_object($assesmentData))
        {
            // assesment data found // validation check
            if(!$this -> validateData($assesmentData))
            {   
                // function call
                $res = $this -> pushErrorRes($res);
            } 
            else
            {  
                // insert in database
                $res['msg'] = json_encode($this -> postArray());

                $result = $this -> remarkModel::insert(
                    $this -> remarkModel -> getTableName(), 
                    $this -> postArray()
                );

                if($result)
                {
                    $res['err'] = false;
                    $res['msg'] = Notifications::getNoti('auditRemarkAddedSuccess');
                }
            }
        }
        else
            $res['msg'] = Notifications::getNoti('assesmentNotFound');

        echo json_encode($res);
        exit;
    }

    // public function update($getRequest) 
    // {
    //     /*$this -> colId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';
         
    //     //set form url
    //     $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> colId) . '?annex=' . encrypt_ex_data($this -> annexId));
    //     $this -> me -> pageHeading = 'Update Column';

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> colId]) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $this -> data['btn_type'] = 'update';

    //     //form
    //     $this -> request::method('GET', function() {

    //         // load view
    //         return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

    //     });

    //     //post method after form submit
    //     $this -> request::method("POST", function() {

    //         //validation check
    //         if(!$this -> validateData('update', $this -> colId))
    //         {   
    //             // load view
    //             return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
    //         } 
    //         else
    //         {
    //             $result = $this -> annexureColumnModel::update(
    //                 $this -> annexureColumnModel -> getTableName(), 
    //                 $this -> postArray('update'),
    //                 [
    //                     'where' => 'id = :id',
    //                     'params' => [ 'id' => $this -> colId]
    //                 ]
    //             );

    //             if(!$result)
    //                 return Except::exc_404( Notifications::getNoti('somethingWrong') );

    //             //after insert data redirect to annexure Column dashboard
    //             Validation::flashErrorMsg('annexColumnUpdatedSuccess', 'success');
    //             Redirect::to( SiteUrls::getUrl('annexureColumns')  . '/?annex=' . encrypt_ex_data($this -> annexId) );
    //         }
    //     });*/
    // }

    public function delete($getRequest) 
    {
        $res = ['err' => true, 'msg' => Notifications::getNoti('somethingWrong') ];

        $this -> rmkId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';

        // get data // method call
        $this -> data['db_data'] = $this -> getDataOr404();

        //return if data not found
        if( is_object($this -> data['db_data']) )
        {
            // data found // check for any view
            $model = $this -> model('AuditRemarkStatusModel');

            $checkData = $model -> getSingleAuditRemarkStatus([
                'where' => 'noti_id = :noti_id',
                'params' => [ 'noti_id' => $this -> data['db_data'] -> id ]
            ]);

            if(!is_object($checkData))
            {
                // start remove proccess
                $result = $this -> remarkModel::delete($this -> remarkModel -> getTableName(),[
                    'where' => 'id = :id',
                    'params' => [ 'id' => $this -> rmkId ]
                ]);
        
                if($result)
                {
                    $res['err'] = false;
                    $res['msg'] = Notifications::getNoti('auditRemarkDeletedSuccess');
                }
            }
            else
                $res['msg'] = Notifications::getNoti('auditRemarkViewedError');
        }

        echo json_encode($res);
        exit;
    }

    public function viewed($getRequest) 
    {
        $res = ['err' => true, 'msg' => Notifications::getNoti('somethingWrong') ];

        $this -> rmkId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';

        // get data // method call
        $this -> data['db_data'] = $this -> getDataOr404();

        // return if data not found
        if( is_object($this -> data['db_data']) )
        {
            // data found // check for any view
            $model = $this -> model('AuditRemarkStatusModel');
            $empId = Session::get('emp_id');

            $checkData = $model -> getSingleAuditRemarkStatus([
                'where' => 'noti_id = :noti_id AND emp_id = :emp_id',
                'params' => [ 'noti_id' => $this -> data['db_data'] -> id, 'emp_id' => $empId ]
            ]);

            if(!is_object($checkData))
            {
                // insert viewed data
                $result = $model::insert(
                    $model -> getTableName(), [
                        'noti_id' => $this -> rmkId,
                        'emp_id' => $empId,
                        'readed_at' => date('Y-m-d H:i:s')
                    ], false
                );
        
                if($result)
                {
                    $res['err'] = false; $res['msg'] = null;
                }
            }
            
            else
            {
                // already viewed
                $res['err'] = false; $res['msg'] = null;
            }
            /*else
                $res['msg'] = Notifications::getNoti('auditRemarkViewedError');*/
        }

        echo json_encode($res);
        exit;
    }

    public function getAuditRemarkData()
    {
        $res = [ 'err' => true, 'msg' => Notifications::getNoti('somethingWrong'), 'markup' => null, 'new' => false ];

        // method call
        $assesmentData = $this -> getAssesmentData();
        $remarkData = [];

        if(is_object($assesmentData))
        {
            $remarkData = $this -> getRemarkData($assesmentData, Session::get('emp_id'));

            if(!empty($remarkData['current']) || !empty($remarkData['other']))
            {
                // $res['err'] = false;
                $markup = [ 'current' => '', 'other' => '' ];

                foreach($remarkData as $cSortKey => $cSortData)
                {
                    if(is_array($cSortData) && sizeof($cSortData) > 0)
                    {
                        foreach($cSortData as $cRmkId => $cRmkDetails)
                        {
                            $cRmkIdEnc = 'rmk_con_' . $cRmkId;

                            $markup[ $cSortKey ] .= '<div class="single-rmk-container">';

                                $markup[ $cSortKey ] .= '<div class="rmk-subject-container icn-af icn-arrow-right-blue '. (($cRmkDetails -> action && $cSortKey == 'other') ? 'rmk-read' : '') .'" data-bs-toggle="collapse" data-bs-target="#'. $cRmkIdEnc .'" aria-expanded="false" aria-controls="'. $cRmkIdEnc .'"'. (($cRmkDetails -> action && $cSortKey == 'other') ? (' data-href="'. SiteUrls::getUrl('assesmentRemarkMaster') .'/viewed/'. encrypt_ex_data($cRmkId) .'"') : '') .'>';

                                    $markup[ $cSortKey ] .= '<p class="rmk-timestamp text-primary mb-1">'. ( array_key_exists($cRmkDetails -> noti_type, $GLOBALS['remarkTypesArray']) ? $GLOBALS['remarkTypesArray'][ $cRmkDetails -> noti_type ] : ERROR_VARS['notAvailable'] ) . '</p>';
                                    $markup[ $cSortKey ] .= '<h6 class="font-bold mb-0">'. $cRmkDetails -> subject .'</h6>';
                                    $markup[ $cSortKey ] .= '<p class="rmk-timestamp">'. ( is_object($cRmkDetails -> admin) ? ($cRmkDetails -> admin -> name . ' (Emp. ' . $cRmkDetails -> admin -> emp_code . ')') : ERROR_VARS['notAvailable'] ) . ' at ' . date('Y M d, H:i A', strtotime($cRmkDetails -> created_at)) . '</p>';
                                $markup[ $cSortKey ] .= '</div>';

                                $markup[ $cSortKey ] .= '<div id="'. $cRmkIdEnc .'" class="rmk-container collapse py-2 border-top bg-light-gray">';
                                    $markup[ $cSortKey ] .= '<span class="font-medium text-primary">Remark: </span>' . $cRmkDetails -> message;

                                    if($cSortKey == 'current')
                                    {
                                        $markup[ $cSortKey ] .= '<div class="w-100 mb-1"></div>';

                                        if($cRmkDetails -> action)
                                            $markup[ $cSortKey ] .= '<a class="btn btn-sm btn-danger rmk-remove-btn" href="'. SiteUrls::getUrl('assesmentRemarkMaster') .'/delete/'. encrypt_ex_data($cRmkId) .'">Remove Remark &raquo;</a>';
                                        else
                                            $markup[ $cSortKey ] .= '<p class="font-sm">Note: Assesment remark readed by other employees! Can\'t remove.</p>';
                                    }

                                    if($cSortKey == 'other' && $cRmkDetails -> action)
                                        $res['new'] = true;

                                $markup[ $cSortKey ] .= '</div>';

                            $markup[ $cSortKey ] .= '</div>';
                        }
                    }
                    else
                    {
                        $markup[ $cSortKey ] = Notifications::getCustomAlertNoti('noDataFound');
                    }
                }

                $res['err'] = false;
                $res['markup'] = $markup;
            }
            else
            {
                // method call
                $res['markup'] = $this -> checkEmptyResponse($remarkData, $res['markup']);

                $res['msg'] = Notifications::getNoti('noDataFound');
            }
        }
        else
        {
            $res['msg'] = Notifications::getNoti('assesmentNotFound');

            // method call
            $res['markup'] = $this -> checkEmptyResponse($remarkData, $res['markup']);
        }

        echo json_encode($res);
        exit;
    }

    private function checkEmptyResponse($remarkData, $markup) 
    {
        if(!isset($remarkData['current']) || empty($remarkData['current']))
            $markup[ 'current' ] = Notifications::getCustomAlertNoti('noDataFound');

        if(!isset($remarkData['other']) || empty($remarkData['other']))
            $markup[ 'other' ] = Notifications::getCustomAlertNoti('noDataFound');
    
        return $markup;
    }

    private function getRemarkData($assesmentData, $sessionEmpId)
    {
        if( !is_object($assesmentData) )
            return null;

        $model = $this -> model('AuditRemarkModel');
        $remarkArray = [];

        // find data // for audit
        if(in_array($assesmentData -> audit_status_id, [1,3]))
            $remarkArray = [1,5];

        // for reviewer
        elseif(in_array($assesmentData -> audit_status_id, [2,5]))
            $remarkArray = [2,4];

        // for complinace
        elseif(in_array($assesmentData -> audit_status_id, [4,6]))
            $remarkArray = [3,4,5];

        $whereArray = [
            'where' => 'assesment_id = :assesment_id AND deleted_at IS NULL AND (noti_type IN ('. implode(',', $remarkArray) .') OR admin_id = :admin_id) ORDER BY id DESC',
            'params' => [ 'assesment_id' => $assesmentData -> id, 'admin_id' => $sessionEmpId ]
        ];

        $findData = $model -> getAllAuditRemark($whereArray);
        $remarkArray = [ 'current' => [], 'other' => [] ];

        if(is_array($findData) && sizeof($findData) > 0)
        {
            // function call
            $extraArray = [ 'empIds' => [] ];

            foreach($findData as $cRemarkData)
            {
                if(!in_array($cRemarkData -> admin_id, $extraArray['empIds']))
                    $extraArray['empIds'][] = $cRemarkData -> admin_id;

                $cRemarkData -> action = false;
                $cRemarkData -> admin = null;
                $cRemarkData -> noti = null;

                // push data
                if($cRemarkData -> admin_id == $sessionEmpId)
                    $remarkArray['current'][ $cRemarkData -> id ] = $cRemarkData;
                else
                    $remarkArray['other'][ $cRemarkData -> id ] = $cRemarkData;
            }

            $extraArray['employeeData'] = [];
            $extraArray['remarkStatus'] = [];

            if(sizeof($extraArray['empIds']) > 0)
            {
                // find employee details
                $model = $this -> model('EmployeeModel');

                $select = "SELECT id, user_type_id, emp_code, gender, name FROM employee_master WHERE id IN (". implode(',', $extraArray['empIds']) .") AND is_active = 1 AND deleted_at IS NULL";
                $extraArray['employeeData'] = get_all_data_query_builder(2, $model, 'employee_master', [], 'sql', $select);
                $extraArray['employeeData'] = generate_data_assoc_array($extraArray['employeeData'], 'id');
            }

            // find view data
            if(sizeof($remarkArray['current']) > 0 || sizeof($remarkArray['other']) > 0)
            {
                // find views
                $model = $this -> model('AuditRemarkStatusModel');

                $cRmkIds = !empty($remarkArray['current']) ? array_keys($remarkArray['current']) : [0];
                $select = "SELECT DISTINCT(noti_id) as noti_id FROM audit_remark_status WHERE noti_id IN (". implode(',',  $cRmkIds) .")";

                $cRmkIds = !empty($remarkArray['other']) ? array_keys($remarkArray['other']) : [0];

                if(sizeof($remarkArray['other']) > 0)
                    $select .= " OR noti_id IN (". implode(',', $cRmkIds) .") ";

                $extraArray['remarkStatus'] = get_all_data_query_builder(2, $model, 'audit_remark_status', [], 'sql', $select);
                $extraArray['remarkStatus'] = generate_data_assoc_array($extraArray['remarkStatus'], 'noti_id');
            }

            // sort remark here admin wise and other
            foreach($remarkArray as $cType => $cData)
            {
                if(is_array($cData) && sizeof($cData) > 0)
                {
                    // has data sort
                    foreach($cData as $cRmkId => $cRmkDetails)
                    {
                        // check for employee details
                        if( is_array($extraArray['employeeData']) && 
                            array_key_exists($cRmkDetails -> admin_id, $extraArray['employeeData']))
                        {
                            $cEmp = $extraArray['employeeData'][ $cRmkDetails -> admin_id ];
                            $cEmp -> user_type = array_key_exists($cEmp -> user_type_id, $GLOBALS['userTypesArray']) ? $GLOBALS['userTypesArray'][ $cEmp -> user_type_id ] : ERROR_VARS['notFound'];

                            // assign employee data
                            $remarkArray[ $cType ][ $cRmkId ] -> admin = $cEmp;
                        }

                        // check for noti type
                        $remarkArray[ $cType ][ $cRmkId ] -> noti = array_key_exists($cRmkDetails -> noti_type, $GLOBALS['remarkTypesArray']) ? $GLOBALS['remarkTypesArray'][ $cRmkDetails -> noti_type ] : ERROR_VARS['notFound'];

                        // has data
                        if( is_array($extraArray['remarkStatus']) && 
                            !array_key_exists($cRmkId, $extraArray['remarkStatus']) > 0)                            
                            $remarkArray[ $cType ][ $cRmkId ] -> action = true;                        
                    }
                }
            }
        }

        return $remarkArray;
    }

    private function pushErrorRes($res)
    {
        if(intval($this -> request -> input('error')) > 0)
        {
            if(!array_key_exists('err_input', $res))
                $res['err_input'] = [];

            foreach($this -> request -> all() as $cKey => $cVal)
            {
                if( preg_match('/_err/i', $cKey) )
                    $res['err_input'][ str_replace('_err', '', $cKey) ] = Notifications::getNoti($cVal);
            }
        }

        return $res;
    }

    private function getAssesmentData()
    {
        // function call
        $assesId = decrypt_ex_data(Session::get('audit_id'));

        if(empty($assesId)) return null;

        return get_single_assesment_details($this, [
            'assesment_id' => $assesId,
            'default' => true
        ]);
    }

    private function getDataOr404($filter = [], $optional = null) 
    {   
        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> rmkId ]
        ];

        // get data
        $this -> data['db_data'] = $this -> remarkModel -> getSingleAuditRemark($filter);

        if( empty($this -> rmkId) || empty($this -> data['db_data']) )
            return null;

        return $this -> data['db_data'];
    }
}

?>