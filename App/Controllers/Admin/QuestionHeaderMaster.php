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

class QuestionHeaderMaster extends Controller  {

    public $me = null, $setId, $data, $request, $headerId;
    public $questionHeaderModel;

    public $questionSetModel, $questionSet;

    public function __construct($me) {
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        // ------------------- question set logic ------------------

        $this -> setId = decrypt_ex_data($this -> request -> input('set'));

        // require question set model
        $this -> questionSetModel = $this -> model('QuestionSetModel');        

        $this -> data['db_set'] = null;

        //get single set details
        if(!empty($this -> setId))
            $this -> data['db_set'] = $this -> questionSetModel -> getSingleQuestionSet([
                'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
                'params' => [ 'id' => $this -> setId ]
            ]);

        if( !is_object($this -> data['db_set']) ) {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        //unset var
        unset($this -> questionSetModel);

        // ------------------- question set logic ------------------

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('questionHeaderMaster') . '?set=' . encrypt_ex_data($this -> setId) ],
        ];

        // find current question header model
        $this -> questionHeaderModel = $this -> model('QuestionHeaderModel');  
        
        // top data container 
        $this -> data['data_container'] = true;
    }

    private function validateData($methodType = 'add', $headerId = '', $setId = '')
    {

        $uniqueWhere = [
            'model' => $this -> questionHeaderModel,
            'where' => 'name = :name AND question_set_id = :question_set_id AND ( is_active = 0 OR is_active = 1) AND deleted_at IS NULL',
            'params' => 
            [ 'name' => $this -> request -> input('name'),
              'question_set_id' => decrypt_ex_data($this -> request -> input('set')),
            ]
        ];

        if(!empty($headerId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $headerId;
        }

        $validationArray = [
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, headerDuplicate]'
        ];

        Validation::validateData($this -> request, $validationArray,
        [
            'unique_data' => $uniqueWhere,
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
            'question_set_id' => decrypt_ex_data($this -> request -> input('set')),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index($getRequest) 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('questionSetMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('questionHeaderMaster') . '/add?set=' . encrypt_ex_data($this -> setId) ],
        ];
         
        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> questionHeaderModel, 
            $this -> questionHeaderModel -> getTableName(), [
                'where' => 'deleted_at IS NULL AND question_set_id =:question_set_id',
                'params' => [ 'question_set_id' => $this -> setId ]]);

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view 
        //helper function call
        return return2View($this, $this -> me -> viewDir . 'index', ['request' => $this -> request]);
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> questionHeaderModel, ["name"], [
            'where' => 'deleted_at IS NULL AND question_set_id =:question_set_id',
            'params' => [ 'question_set_id' => $this -> setId ]
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cQuestionHeaderId => $cQuestionHeaderDetails)
            {
                $name = '<p class="text-primary mb-1">' . $cQuestionHeaderDetails -> name . ' </p>';

                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "name" => $name,
                    "status" => check_active_status($cQuestionHeaderDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                if( $cQuestionHeaderDetails -> is_active == 1 )
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cQuestionHeaderDetails -> id) . '?set=' . encrypt_ex_data($this -> setId), 'extra' => view_tooltip('Update') ]);

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    if($cQuestionHeaderDetails -> is_active == 1) {

                        $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cQuestionHeaderDetails -> id) . '?set=' . encrypt_ex_data($this -> setId), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cQuestionHeaderDetails -> id) . '?set=' . encrypt_ex_data($this -> setId), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                    }
                    else 
                    {
                        $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cQuestionHeaderDetails -> id) . '?set=' . encrypt_ex_data($this -> setId), 'extra' => view_tooltip('Activate') ]);
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

    public function add($getRequest)
    {
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?set=' . encrypt_ex_data($this -> setId));
        $this -> me -> pageHeading = 'Add Set';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> questionHeaderModel -> emptyInstance();
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
                $result = $this -> questionHeaderModel::insert(
                    $this -> questionHeaderModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to header dashboard
                Validation::flashErrorMsg('questionheaderAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('questionHeaderMaster') . '/?set=' . encrypt_ex_data($this -> setId) );

            }

        });

    }

    public function update($getRequest) 
    {
        $this -> headerId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';
         
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> headerId) . '?set=' . encrypt_ex_data($this -> setId));
        $this -> me -> pageHeading = 'Update Set';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> headerId]) ;

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
            if(!$this -> validateData('update', $this -> headerId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> questionHeaderModel::update(
                    $this -> questionHeaderModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> headerId]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to header dashboard
                Validation::flashErrorMsg('questionheaderUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('questionHeaderMaster')  . '/?set=' . encrypt_ex_data($this -> setId) );
            }
        });
    }

    public function delete($getRequest) 
    {
        // method call for admin lite 29.11.2024
        $this -> checkAdminLite();
        
        $this -> headerId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> headerId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> questionHeaderModel::delete($this -> questionHeaderModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> headerId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to header dashboard
        Validation::flashErrorMsg('questionheaderDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('questionHeaderMaster') . '/?set=' . encrypt_ex_data($this -> setId));
    }

    public function status($getRequest) 
    {
        // method call for admin lite 29.11.2024
        $this -> checkAdminLite();

        $this -> headerId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> headerId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> questionHeaderModel::update(
            $this -> questionHeaderModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> headerId]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to header dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('questionHeaderMaster') .'/?set=' . encrypt_ex_data($this -> setId) );
    }

    private function getDataOr404($filter, $optional = null) 
    {   
        $filter = [ 
            'where' => 'id = :id AND question_set_id = :question_set_id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> headerId, 'question_set_id' => $this -> setId]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND question_set_id = :question_set_id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> questionHeaderModel -> getSingleQuestionHeader($filter);

        if(empty($this -> headerId) || empty($this -> data['db_data']) )
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