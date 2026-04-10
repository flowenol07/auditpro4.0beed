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


class CategoryMaster extends Controller  {

    public $me = null, $data, $request, $categoryId, $categoryArr;
    public $categoryModel;

    public $menuModel, $menuName;

    public $questionSetModel, $mainSet, $schemeCodeDeposit;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('categoryMaster') ],
        ];

        //Search in Select 
        $this -> data['need_select'] = true;

        // request object created
        $this -> request = new Request();

        // find current category model
        $this -> categoryModel = $this -> model('CategoryModel'); 

        $this -> menuModel = $this -> model('MenuModel');

        $this -> questionSetModel = $this -> model('QuestionSetModel');
        
        //get all menu
        $this -> menuName = $this -> menuModel -> getAllMenu(['where' => 'is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_menu'] = generate_array_for_select($this -> menuName, 'id', 'name');

        $this -> data['db_menu_linked_table'] = generate_array_for_select($this -> menuName, 'id', 'linked_table_id');

    }

    private function validateData($methodType = 'add', $categoryId = '')
    {
        $uniqueWhere = [
            'model' => $this -> categoryModel,
            'where' => 'name = :name AND menu_id = :menu_id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 
            'name' => $this -> request -> input('name'),
            'menu_id' => $this -> request -> input('menu_id'),
            ]
        ];

        if($methodType == 'update' && !empty($categoryId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $categoryId;
        }

        $validationArray = [
            'menu_id' => 'required|array_key[menu_array, menuSelect]',
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, categoryDuplicate]'
        ];

        Validation::validateData($this -> request, $validationArray,
        [
            'menu_array' => $this -> data['db_menu'],
            'unique_data' => $uniqueWhere,
        ]);

        if($this -> request -> input('menu_id') == 1)
        {
            Validation::incrementError($this -> request);
            $this -> request -> setInputCustom('menu_id_err', 'executiveSummaryDuplicate');
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

    private function postArray($methodType = 'add')
    {
        $dataArray = array(
            'menu_id' => $this -> request -> input('menu_id'),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'is_cc_acc_category' => ($this -> request -> input('is_cc_acc_category') ?? 0),
            'linked_table_id' => $this -> data['db_menu_linked_table'][$this -> request -> input('menu_id')],
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() 
    {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('categoryMaster') . '/add' ],
        ];

        // $this -> data['db_data'] = $this -> categoryModel -> getAllCategory([ 'where' => 'deleted_at IS NULL' ]);

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> categoryModel, 
            $this -> categoryModel -> getTableName(), [
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
        $funcData = generate_datatable_data($this, $this -> categoryModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cCategoryId => $cCategoryDetails)
            {
                $menuName = (isset($this -> data['db_menu'][$cCategoryDetails -> menu_id]) ? $this -> data['db_menu'][$cCategoryDetails -> menu_id] : ERROR_VARS['notFound']);

                $categoryName = '';

                if($cCategoryDetails -> is_cc_acc_category == 1)
                    $categoryName =  $cCategoryDetails -> name . '<p class="font-sm mt-1 mb-0"><span class="font-medium text-primary">CC Category: </span>Yes</p>'; 
                else
                    $categoryName =  $cCategoryDetails -> name;               

                $cDataArray = [
                    "menu_id" =>  $menuName,
                    "name" => $categoryName,
                    "status" => check_active_status($cCategoryDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start

                if($cCategoryDetails -> id != 1)
                {
                    if($CHECK_ADMIN_ACTION)
                    {
                        if($cCategoryDetails -> is_active == 1) 
                        {                        
                            $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cCategoryDetails -> id), 'extra' => view_tooltip('Update') ]);

                            $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cCategoryDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                            $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cCategoryDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);

                            $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/mapping/' . encrypt_ex_data($cCategoryDetails -> id), 'extra' => view_tooltip('Question Mapping')]);
                        }
                        else 
                        {
                            $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cCategoryDetails -> id), 'extra' => view_tooltip('Activate') ]);
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
        $this -> me -> pageHeading = 'Add Category';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> categoryModel -> emptyInstance();
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
                //insert in database
                $result = $this -> categoryModel::insert(
                    $this -> categoryModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to category dashboard
                Validation::flashErrorMsg('categoryAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('categoryMaster') );
            }

        });

    }

    public function update($getRequest) 
    {

        $this -> categoryId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> categoryId));
        $this -> me -> pageHeading = 'Update Category';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> categoryId]) ;

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
            if(!$this -> validateData('update', $this -> categoryId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> categoryModel::update(
                    $this -> categoryModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> categoryId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to category dashboard
                Validation::flashErrorMsg('categoryUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('categoryMaster') );
            }
        });
    }

    public function delete($getRequest) 
    {

        $this -> categoryId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> categoryId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> categoryModel::delete($this -> categoryModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> categoryId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to category dashboard
        Validation::flashErrorMsg('categoryDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('categoryMaster') );
    }

    public function status($getRequest) {

        $this -> categoryId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> categoryId], 2) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> categoryModel::update(
            $this -> categoryModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> categoryId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to category dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('categoryMaster') );
    }

    public function mapping($getRequest) {

        $this -> categoryId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/mapping/' . encrypt_ex_data($this -> categoryId));
        $this -> me -> pageHeading = 'Question Mapping';
        
        // top data container 
        $this -> data['data_container'] = true;

        $this -> me -> breadcrumb[] = $this -> me -> id ;

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> categoryId);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';

        //get all question set (mainset)
        $this -> mainSet = $this -> questionSetModel -> getAllQuestionSet(['where' => 'is_active = 1 AND deleted_at IS NULL AND set_type_id = 1'], 'sql', 'SELECT id, CONCAT(name, " ( ", id , " ) ") AS combined_id FROM ' . $this -> questionSetModel -> getTableName());

        $this -> data['db_mainset'] = generate_array_for_select($this -> mainSet, 'id', 'combined_id');

        //get all category
        $this -> categoryArr = $this -> categoryModel -> getAllCategory([
            'where' => 'is_active = 1 AND deleted_at IS NULL AND linked_table_id IN (1, 2) AND id != :id',
            'params' => ['id' => $this -> categoryId]
        ]);

        $existingQuestionsIdArr = [];

        // for mainset data filter
        if(is_array( $this -> data['db_mainset'] ) && sizeof($existingQuestionsIdArr) > 0)
            $this -> data['db_mainset'] = array_diff_key($this -> data['db_mainset'], array_flip($existingQuestionsIdArr));

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form_mapping', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            Validation::validateData($this -> request, [
                'question_set_ids' => 'required',
            ]);

            //validation check
            if($this -> request -> input('error') > 0)
            {    
                Validation::flashErrorMsg();

                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_mapping', [ 'request' => $this -> request ]);
            } 
            else
            {
                $updateDataArray = array(
                    'question_set_ids' => $this -> request -> input( 'question_set_ids'),
                    'admin_id' => Session::get('emp_id')
                );   

                $result = $this -> categoryModel::update($this -> categoryModel -> getTableName(), 
                    $updateDataArray, [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> categoryId ]
                    ]
                );      

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to category master dashboard
                Validation::flashErrorMsg('questionMappingSavedSuccess', 'success');
                Redirect::to(SiteUrls::getUrl('categoryMaster'));
            }
        });
    }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $this -> categoryId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> categoryModel -> getSingleCategory($filter);

        if(empty($this -> categoryId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding'));

        if(!empty($this -> categoryId) && $this -> categoryId == 1)
            return Except::exc_404( Notifications::getNoti('errorFinding'));

        return $this -> data['db_data'];
    }
}

?>