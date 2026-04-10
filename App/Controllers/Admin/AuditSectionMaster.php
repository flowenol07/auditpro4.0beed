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


class AuditSectionMaster extends Controller  {

    public $me = null, $request, $data, $sectionId;
    public $auditSectionModel;

    public function __construct($me) {
        $this -> me = $me;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('auditSectionMaster') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current audit section model
        $this -> auditSectionModel = $this -> model('AuditSectionModel');   
    }

    private function validateData($sectionId = '')
    {
        $uniqueWhere = [
            'model' => $this -> auditSectionModel,
            'where' => 'name = :name AND deleted_at IS NULL',
            'params' => [ 'name' => $this -> request -> input('name') ]
        ];

        if(!empty($sectionId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $sectionId;
        }

        Validation::validateData($this -> request, [
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, auditSection]'
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
            'name' => string_operations($this -> request -> input( 'name' ), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('auditSectionMaster') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> auditSectionModel, 
            $this -> auditSectionModel -> getTableName(), [
                'where' => 'deleted_at IS NULL',
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
        $funcData = generate_datatable_data($this, $this -> auditSectionModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cSectionId => $cSectionDetails)
            {
                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "name" => $cSectionDetails -> name,
                    "status" => check_active_status($cSectionDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
                
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    if($cSectionDetails -> is_active == 1) {
                
                        $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cSectionDetails -> id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .= generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cSectionDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                    }
                    else {

                        $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cSectionDetails -> id), 'extra' => view_tooltip('Activate') ]);
                    }
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
        $this -> me -> pageHeading = 'Add Audit Section';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> auditSectionModel -> emptyInstance();
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

                $result = $this -> auditSectionModel::insert(
                    $this -> auditSectionModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to audit section dashboard
                Validation::flashErrorMsg('auditSectionAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('auditSectionMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> sectionId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> sectionId));
        $this -> me -> pageHeading = 'Update Audit Section';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> sectionId ]) ;

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
            if(!$this -> validateData($this -> sectionId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> auditSectionModel::update(
                    $this -> auditSectionModel -> getTableName(), 
                    $this -> postArray('update'), 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> sectionId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to audit section dashboard
                Validation::flashErrorMsg('auditSectionUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('auditSectionMaster') );
            }
        });
    }

    // Commented as per advice of Omkar Sir
    // public function delete($getRequest) {

    //     $this -> sectionId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> sectionId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $result = $this -> auditSectionModel::delete($this -> auditSectionModel -> getTableName(), 
    //     [
    //         'where' => 'id = :id',
    //         'params' => [ 'id' => $this -> sectionId ]
    //     ] );

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorDeleting') );

    //     //after insert data redirect to audit section dashboard
    //     Validation::flashErrorMsg('auditSectionDeletedSuccess', 'success');
    //     Redirect::to( SiteUrls::getUrl('auditSectionMaster') );
    // }

    public function status($getRequest) {

        $this -> sectionId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> sectionId, 2 );

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> auditSectionModel::update(
            $this -> auditSectionModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> sectionId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to audit section dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('auditSectionMaster') );
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> sectionId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> auditSectionModel -> getSingleAuditSection($filter);

        if(empty($this -> sectionId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>