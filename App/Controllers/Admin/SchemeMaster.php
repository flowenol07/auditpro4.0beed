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

class SchemeMaster extends Controller  {

    public $me = null, $request, $data, $schemeId, $categoryDeposit, $categoryAdvances;
    public $schemeModel, $categoryModel;

    public function __construct($me) {

        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('schemeMaster') ],
        ];

        //Search in Select 
        $this -> data['need_select'] = true;
        
        // request object created
        $this -> request = new Request();

        // find current scheme model
        $this -> schemeModel = $this -> model('SchemeModel');   

        // find current category model
        $this -> categoryModel = $this -> model('CategoryModel');

        //get all category for deposit
        $this -> categoryDeposit = $this -> categoryModel -> getAllCategory(['where' => 'linked_table_id = 1 AND deleted_at IS NULL AND is_active = 1']);

        $this -> data['db_deposit_category'] = generate_array_for_select($this -> categoryDeposit, 'id', 'name');

        //get all category for advances
        $this -> categoryAdvances = $this -> categoryModel -> getAllCategory(['where' => 'linked_table_id = 2 AND deleted_at IS NULL AND is_active = 1']);

        $this -> data['db_advances_category'] = generate_array_for_select($this -> categoryAdvances, 'id', 'name');
    }

    private function validateData($schemeId = '')
    {
        $uniqueWhere = [
            'model' => $this -> schemeModel,
            'where' => 'scheme_type_id = :scheme_type_id AND scheme_code = :scheme_code AND deleted_at IS NULL',
            'params' => [ 
                'scheme_type_id' => $this -> request -> input('scheme_type'),
                'scheme_code' => $this -> request -> input('scheme_code') 
            ]
        ];

        if(!empty($schemeId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $schemeId;
        }

        Validation::validateData($this -> request, [
            'scheme_type' => 'required|array_key[scheme_types_array, schemeType]',
            'scheme_code' => 'required|regex[alphaNumricRegex, schemeCode]|is_unique[unique_data, schemeCodeExists]',
            'category_id' => 'required',
            'name' => 'required'
        ],[
            'scheme_types_array' => $GLOBALS['schemeTypesArray'],
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

    private function postArray()
    {
        $dataArray = array(
            'scheme_type_id' => $this -> request -> input( 'scheme_type' ),
            'scheme_code' => $this -> request -> input( 'scheme_code' ),
            'name' => string_operations($this -> request -> input( 'name' ), 'upper'),
            'category_id' => $this -> request -> input( 'category_id' ),
            'admin_id' => Session::get('emp_id')
        );

        return $dataArray;
    }

    public function index() 
    {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('schemeMaster') . '/add' ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> schemeModel, 
            $this -> schemeModel -> getTableName(), [
                'where' => 'deleted_at IS NULL AND is_active = 1']);

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> schemeModel, ["name", "scheme_code"], [
            'where' => 'deleted_at IS NULL AND is_active = 1'
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cShcemeId => $cShcemeDetails)
            {
                $schemeTypeMarkup = '<span class="text-primary">' . ($GLOBALS['schemeTypesArray'][$cShcemeDetails -> scheme_type_id] ?? ERROR_VARS['notFound'] ) . '</span>';

                if($cShcemeDetails -> category_id != 0)
                {
                    if($cShcemeDetails -> scheme_type_id == 1)
                        $categoryMarkup = '<span class="text-primary">' . (isset($this -> data['db_deposit_category'][$cShcemeDetails -> category_id]) ? $this -> data['db_deposit_category'][$cShcemeDetails -> category_id] : '') . '</span>';

                    if($cShcemeDetails -> scheme_type_id == 2)
                        $categoryMarkup = '<span class="text-primary">' . (isset($this -> data['db_advances_category'][$cShcemeDetails -> category_id]) ? $this -> data['db_advances_category'][$cShcemeDetails -> category_id] : '') . '</span>';
                }
                else
                    $categoryMarkup = '<span class="text-danger">'. ERROR_VARS['notFound'] . '</span>';

                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "scheme_type_id" => $schemeTypeMarkup,
                    "scheme_code" => $cShcemeDetails -> scheme_code,
                    "name" => $cShcemeDetails -> name,
                    "category_id" => $categoryMarkup,
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {                         
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cShcemeDetails -> id), 'extra' => view_tooltip('Update') ]);

                    $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cShcemeDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
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
        $this -> me -> pageHeading = 'Add Scheme';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> schemeModel -> emptyInstance();
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
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> schemeModel::insert(
                    $this -> schemeModel -> getTableName(), 
                    $this -> postArray() //method call
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //after insert data redirect to scheme
                Validation::flashErrorMsg('schemeAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('schemeMaster') );
            }

        });

    }

    public function update($getRequest) 
    {

        $this -> schemeId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> schemeId));
        $this -> me -> pageHeading = 'Update Scheme';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> schemeId);

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
            if(!$this -> validateData($this -> schemeId))
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> schemeModel::update($this -> schemeModel -> getTableName(), 
                    $this -> postArray(), 
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> schemeId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('errorSaving') );

                //after insert data redirect to scheme
                Validation::flashErrorMsg('schemeUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('schemeMaster') );
            }
        });
    }

    public function delete($getRequest) 
    {

        $this -> schemeId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> schemeId ) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> schemeModel::delete(
            $this -> schemeModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> schemeId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to scheme
        Validation::flashErrorMsg('schemeDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('schemeMaster') );
    }

    private function getDataOr404($schemeId, $optional = null) 
    {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $schemeId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> schemeModel -> getSingleScheme($filter);

        if(empty($schemeId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>