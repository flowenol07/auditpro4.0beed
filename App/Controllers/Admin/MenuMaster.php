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


class MenuMaster extends Controller  {

    public $me = null, $data, $request, $menuId;
    public $menuModel;

    public $auditSectionModel, $auditSection;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('menuMaster') ],
        ];

        //Search in Select 
        $this -> data['need_select'] = true;

        // request object created
        $this -> request = new Request();

        // find current menu model
        $this -> menuModel = $this -> model('MenuModel');         

        $this -> auditSectionModel = $this -> model('AuditSectionModel');
        
        //get all sections
        $this -> auditSection = $this -> auditSectionModel -> getAllAuditSection(['where' => 'is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_audit_section'] = generate_array_for_select($this -> auditSection, 'id', 'name');
    }

    private function validateData($methodType = 'add', $menuId = '')
    {
        $uniqueWhere = [
            'model' => $this -> menuModel,
            'where' => 'name = :name AND section_type_id = :section_type_id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 
            'name' => $this -> request -> input('name'),
            'section_type_id' => $this -> request -> input('section_type_id'),
            ]
        ];

        if($methodType == 'update' && !empty($menuId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $menuId;
        }

        $validationArray = [
            'section_type_id' => 'required|array_key[section_type_array, auditSectionSelect]',
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, menuDuplicate]'
        ];

        Validation::validateData($this -> request, $validationArray,
        [
            'section_type_array' => $this -> data['db_audit_section'],
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
            'section_type_id' => $this -> request -> input('section_type_id'),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'linked_table_id' => string_operations($this -> request -> input('linked_table_id'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('menuMaster') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> menuModel, 
            $this -> menuModel -> getTableName()
        );

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        $this -> data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> menuModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            foreach($funcData['dbData'] as $cMenuId => $cMenuDetails)
            {
                $cDataArray = [
                    "section_type_id" =>  ($this -> data['db_audit_section'][ $cMenuDetails -> section_type_id ] ?? ERROR_VARS['notFoundSpan'] ),
                    "name" => $cMenuDetails -> name,
                    "status" => check_active_status($cMenuDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
                
                if($cMenuDetails -> id != 1)
                {
                    // For Enable of Action on Assement Start
                    if($CHECK_ADMIN_ACTION)
                    {
                        if($cMenuDetails -> is_active == 1) {
                    
                            $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Update') ]);

                            $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                            $cDataArray["action"] .= generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                        }
                        else {

                            $cDataArray["action"] .= generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cMenuDetails -> id), 'extra' => view_tooltip('Activate') ]);
                        } 
                    }             
                    else
                        $cDataArray["action"] .= '';
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
        $this -> me -> pageHeading = 'Add Menu';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> menuModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';
        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form', [ 
                'request' => $this -> request,
                'data' => $this -> data
            ]);
        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData())
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 
                    'request' => $this -> request,
                    'data' => $this -> data
                ]);
            } 
            else
            {  
                // insert in database
                $result = $this -> menuModel::insert(
                    $this -> menuModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to menu dashboard
                Validation::flashErrorMsg('menuAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('menuMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> menuId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> menuId));
        $this -> me -> pageHeading = 'Update Menu';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> menuId]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';
        $this -> data['need_calender'] = true;

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if(!$this -> validateData('update', $this -> menuId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> menuModel::update(
                    $this -> menuModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> menuId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to menu dashboard
                Validation::flashErrorMsg('menuUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('menuMaster') );
            }
        });
    }

    public function delete($getRequest) {

        $this -> menuId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> menuId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> menuModel::delete($this -> menuModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> menuId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to menu dashboard
        Validation::flashErrorMsg('menuDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('menuMaster') );
    }

    public function status($getRequest) {

        $this -> menuId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> menuId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> menuModel::update(
            $this -> menuModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> menuId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to menu dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('menuMaster') );
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> menuId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> menuModel -> getSingleMenu($filter);

        if(empty($this -> menuId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        if(!empty($this -> menuId) && $this -> menuId == 1 )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>