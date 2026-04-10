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

class TargetMaster extends Controller  {

    public $me = null, $request, $auditUnit, $data, $auditId, $yearModel, $tgId, $year;
    public $auditUnitModel, $targetModel;

    public function __construct($me) {
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        // ------------------- audit logic ------------------

        $this -> auditId = decrypt_ex_data($this -> request -> input('audit'));

        // require audit model
        $this -> auditUnitModel = $this -> model('AuditUnitModel');        

        $this -> data['db_audit_unit_data'] = null;

        //get single audit details
        if(!empty($this -> auditId))
            $this -> data['db_audit_unit_data'] = $this -> auditUnitModel -> getSingleAuditUnit([
                'where' => 'id = :id AND is_active = 1 AND section_type_id = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $this -> auditId ]
            ]);

        if( !is_object($this -> data['db_audit_unit_data']) ) {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        //unset var
        unset($this -> auditUnitModel);

        // ------------------- audit logic ------------------

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('targetMaster') . '?audit=' . encrypt_ex_data($this -> auditId) ],
        ];

        // find current target model
        $this -> targetModel = $this -> model('TargetMasterModel'); 

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, ['where' => 'deleted_at IS NULL']);

        $this -> data['db_year'] = generate_array_for_select($this -> year, 'id', 'year');
        
        // top data container 
        $this -> data['data_container'] = true;

    }

    private function validateData($tgId = '')
    {

        $uniqueWhere = [
            'model' => $this -> targetModel,
            'where' => 'year_id = :year_id AND audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
            'params' => [ 
                'audit_unit_id' => $this -> auditId,
                'year_id' => $this -> request -> input('year_id'),
             ]
        ];

        if(!empty($tgId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $tgId;
        }

        Validation::validateData($this -> request, [
            'year_id' => 'required|regex[numberRegex, emp_code]|is_unique[unique_data, yearDuplicate]',
            'deposit_target' => 'required|regex[floatNumberRegex, target]',
            'advances_target' => 'required|regex[floatNumberRegex, target]',
            'npa_target' => 'required|regex[floatNumberRegex, target]',
        ],[
            'unique_data' => $uniqueWhere
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
            'audit_unit_id' => $this -> auditId,
            'year_id' => $this -> request -> input('year_id'),
            'deposit_target' => get_decimal($this -> request -> input('deposit_target'), 2),
            'advances_target' => get_decimal($this -> request -> input('advances_target'), 2),
            'npa_target' => get_decimal($this -> request -> input('npa_target'), 2),
            'admin_id' => Session::get('emp_id'),
        );

        if($methodType == 'update')
            unset($dataArray['password'], $dataArray['password_policy'], $dataArray['admin_id'] );

        return $dataArray;
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('auditUnitMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('targetMaster') . '/add?audit=' . encrypt_ex_data($this -> auditId) ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> targetModel, 
            $this -> targetModel -> getTableName(), [
                'where' => 'audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
                'params' => ['audit_unit_id' => $this -> auditId]
            ]
        );

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index', ['request' => $this -> request]);
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> targetModel, ["year_id", "deposit_target", "advances_target", "npa_target"], [
            'where' => 'audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
            'params' => ['audit_unit_id' => $this -> auditId]
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            foreach($funcData['dbData'] as $cTargetId => $cTargetDetails)
            {
                if(array_key_exists($cTargetDetails -> year_id, $this -> data['db_year']))
                {    $cDataArray = [
                        "year_id" => $this -> data['db_year'][$cTargetDetails -> year_id],
                        "deposit_target" => $cTargetDetails -> deposit_target,
                        "advances_target" => $cTargetDetails -> advances_target,
                        "npa_target" => $cTargetDetails -> npa_target,
                        "action" => ""
                    ];

                    // For Enable of Action on Assement Start
                    if($CHECK_ADMIN_ACTION)
                    { 
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cTargetDetails -> id) . '?audit=' . encrypt_ex_data($this -> auditId), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cTargetDetails -> id) . '?audit=' . encrypt_ex_data($this -> auditId), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
                    }                
                    // else
                        // $cDataArray["action"] .= '';

                    $cDataArray["action"] .= generate_link_button('link', ['href' => SiteUrls::getUrl('exeSummaryAdmin') . '?audit=' . encrypt_ex_data($cTargetDetails -> audit_unit_id)  . '&year=' . encrypt_ex_data($cTargetDetails -> year_id), 'extra' => view_tooltip('Add March Position')]);

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

    public function add()
    {
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?audit=' . encrypt_ex_data($this -> auditId));
        $this -> me -> pageHeading = 'Add Target Details';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> targetModel -> emptyInstance();
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
                $result = $this -> targetModel::insert(
                    $this -> targetModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to target Master dashboard
                Validation::flashErrorMsg('targetAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('targetMaster') . '/?audit=' . encrypt_ex_data($this -> auditId));

            }

        });

    }

    public function update($getRequest) 
    {

        $this -> tgId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> tgId) . '?audit=' . encrypt_ex_data($this -> auditId));
        $this -> me -> pageHeading = 'Update Target Details';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> tgId );

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
            if(!$this -> validateData($this -> tgId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> targetModel::update(
                    $this -> targetModel -> getTableName(), 
                    $this -> postArray('update'),[
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> tgId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to target Master dashboard
                Validation::flashErrorMsg('targetUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('targetMaster') . '/?audit=' . encrypt_ex_data($this -> auditId));
            }
        });
    }

    public function delete($getRequest) 
    {

        $this -> tgId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> tgId ) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> targetModel::delete(
            $this -> targetModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> tgId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to target Master dashboard
        Validation::flashErrorMsg('targetDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('targetMaster') . '/?audit=' . encrypt_ex_data($this -> auditId));
    }

    private function getDataOr404($tgId, $optional = null) 
    {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $tgId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> targetModel -> getSingleTarget($filter);

        if(empty($this -> tgId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>