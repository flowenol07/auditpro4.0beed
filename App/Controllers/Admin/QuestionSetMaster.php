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


class QuestionSetMaster extends Controller  {

    public $me = null, $data, $request, $setId;
    public $questionSetModel;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('questionSetMaster') ],
        ];
        
        // request object created
        $this -> request = new Request();

        // find current question set model
        $this -> questionSetModel = $this -> model('QuestionSetModel');
        
    }

    private function validateData($methodType = 'add', $setId = '')
    {
        $validationArray = [
            'set_type_id' => 'required|array_key[set_type_array, auditSectionSelect]',
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]'
        ];

        Validation::validateData($this -> request, $validationArray,
        [
            'set_type_array' => $GLOBALS['setTypesArray'],
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
            'set_type_id' => $this -> request -> input('set_type_id'),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('questionSetMaster') . '/add' ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> questionSetModel, 
            $this -> questionSetModel -> getTableName(), [
                'where' => 'deleted_at IS NULL']);

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> questionSetModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cQuestionSetId => $cQuestionSetDetails)
            {
                $set_type_id = ($GLOBALS['setTypesArray'][$cQuestionSetDetails -> set_type_id] ?? ERROR_VARS['notFound'] );

                $name = '<p class="text-primary mb-0">' . $cQuestionSetDetails -> name . '</p>';

                if($cQuestionSetDetails -> is_active == 1)
                    $name .= '<a class="d-block text-danger" href=" ' . SiteUrls::getUrl('questionMaster') . '?set='. encrypt_ex_data($cQuestionSetDetails -> id) . '">Manage Question Set &raquo;</a>';

                $cDataArray = [
                    "sr_no" => $srNo,                    
                    "set_type_id" => $set_type_id,
                    "name" =>  $name,
                    "status" => check_active_status($cQuestionSetDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {                      
                    if($cQuestionSetDetails -> is_active == 1) 
                    {                        
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::getUrl('questionHeaderMaster') . '?set=' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Add / Update Header')]);
                    }
                    else 
                    {
                        $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Activate') ]);
                    }
                }                
                else
                    $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::getUrl('questionHeaderMaster') . '?set=' . encrypt_ex_data($cQuestionSetDetails -> id), 'extra' => view_tooltip('Add / Update Header')]);
                
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
        $this -> me -> pageHeading = 'Add Set';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> questionSetModel -> emptyInstance();
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
                $result = $this -> questionSetModel::insert(
                    $this -> questionSetModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to set dashboard
                Validation::flashErrorMsg('questionsetAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('questionSetMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> setId));
        $this -> me -> pageHeading = 'Update Set';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> setId]) ;

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
            if(!$this -> validateData('update', $this -> setId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> questionSetModel::update(
                    $this -> questionSetModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> setId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to set dashboard
                Validation::flashErrorMsg('questionsetUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('questionSetMaster') );
            }
        });
    }

    public function delete($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> setId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> questionSetModel::delete($this -> questionSetModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> setId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to set dashboard
        Validation::flashErrorMsg('questionsetDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('questionSetMaster') );
    }

    public function status($getRequest) {

        $this -> setId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> setId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> questionSetModel::update(
            $this -> questionSetModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> setId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to set dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('questionSetMaster') );
    }

    private function getDataOr404($filter, $optional = null) {

        // method call for admin lite 29.11.2024
        $this -> checkAdminLite();

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> setId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> questionSetModel -> getSingleQuestionSet($filter);

        if(empty($this -> setId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }

    // new method add 29.11.2024
    private function checkAdminLite() {

        if(!Session::has('emp_type') || !in_array(Session::get('emp_type'), [1])) {
            Except::exc_access_restrict( );
            exit;
        }   
    }
}

?>