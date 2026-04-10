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
use Controllers\PasswordPolicy;

class AnnexureMaster extends Controller  {

    public $me = null, $request, $annexureModel, $data, $annexId;

    public $riskCategoryModel, $riskCategory;


    public function __construct($me) {
        $this -> me = $me;

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('annexureMaster') ],
        ];

        //Search in Select 
        $this -> data['need_select'] = true;

        // request object created
        $this -> request = new Request();

        // find current risk category model
        $this -> annexureModel = $this -> model('AnnexureMasterModel'); 

        $this -> riskCategoryModel = $this -> model('RiskCategoryModel'); 

        //get all risk category
        $this -> riskCategory = $this -> riskCategoryModel -> getAllRiskCategory(['where' => 'is_active = 1 AND deleted_at IS NULL']);

        $this -> data['db_risk_category_data'] = generate_array_for_select($this -> riskCategory, 'id', 'risk_category');
    }

    private function validateData($annexId = '')
    {

        $uniqueWhere = [
            'model' => $this -> annexureModel,
            'where' => 'name = :name AND deleted_at IS NULL',
            'params' => [ 'name' => $this -> request -> input('name') ]
        ];

        if(!empty($annexId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $annexId;
        }

        Validation::validateData($this -> request, [
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, annexureName]',
            'risk_category_id' => 'required|array_key[risk_category_id_array, risk_category_id]',
            'business_risk' => 'required|array_key[business_risk_array, business_risk]',
            'control_risk' => 'required|array_key[business_risk_array, control_risk]',
        ],[
            'business_risk_array'  =>  RISK_PARAMETERS_ARRAY,
            'risk_category_id_array'  =>  $this -> data['db_risk_category_data'],
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
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'risk_defination_id' => $this -> request -> input('risk_defination_id'),
            'risk_category_id' => $this -> request -> input('risk_category_id'),
            'business_risk' => $this -> request -> input('business_risk'),
            'control_risk' => $this -> request -> input('control_risk'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('annexureMaster') . '/add' ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> annexureModel, 
            $this -> annexureModel -> getTableName(), [
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
        $funcData = generate_datatable_data($this, $this -> annexureModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cAnnexureId => $cAnnexureDetails)
            {
                $name = '<p class="font-sm my-2"><span class="font-medium text-danger">Risk Category: </span> ' . ($this -> data['db_risk_category_data'][$cAnnexureDetails -> risk_category_id] ?? ERROR_VARS['notFound'] ) . '</p>
                <p class="text-primary font-medium mb-0">' .  $cAnnexureDetails -> name  . '</p>';

                $cDataArray = [
                    "sr_no" => $srNo,
                    "name" => $name,
                    "business_risk" => (RISK_PARAMETERS_ARRAY[$cAnnexureDetails -> business_risk]['title'] ?? ERROR_VARS['notFound'] ),

                    "control_risk" => (RISK_PARAMETERS_ARRAY[$cAnnexureDetails -> control_risk]['title'] ?? ERROR_VARS['notFound'] ),

                    "risk_defination_id" => (($cAnnexureDetails -> risk_defination_id) == 1 ? 'Custom' : 'Default'),
                    
                    "status" => check_active_status($cAnnexureDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                { 
                    if($cAnnexureDetails -> is_active == 1) 
                    {                        
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cAnnexureDetails -> id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cAnnexureDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                        $cDataArray["action"] .=  generate_link_button('inactive', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cAnnexureDetails -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                    }
                    else 
                    {
                        $cDataArray["action"] .=  generate_link_button('active', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/status/' . encrypt_ex_data($cAnnexureDetails -> id), 'extra' => view_tooltip('Activate') ]);
                    }
                }                
                // else
                    // $cDataArray["action"] .= '';

                $cDataArray["action"] .=  generate_link_button('link', ['href' => SiteUrls::getUrl('annexureColumns') . '?annex=' . encrypt_ex_data($cAnnexureDetails -> id), 'extra' => view_tooltip('Add Columns')]);

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
        $this -> me -> pageHeading = 'Add Annexure';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> annexureModel -> emptyInstance();
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
                $result = $this -> annexureModel::insert(
                    $this -> annexureModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to annexure master dashboard
                Validation::flashErrorMsg('annexureAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('annexureMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> annexId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> annexId));
        $this -> me -> pageHeading = 'Update Annexure';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> annexId );

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
            if(!$this -> validateData($this -> annexId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> annexureModel::update(
                    $this -> annexureModel -> getTableName(), 
                    $this -> postArray('update'),[
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> annexId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after update data redirect to annexure master dashboard
                Validation::flashErrorMsg('annexureUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('annexureMaster') );
            }
        });
    }

    public function delete($getRequest) {

        $this -> annexId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> annexId ) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> annexureModel::delete(
            $this -> annexureModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> annexId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to annexure master dashboard
        Validation::flashErrorMsg('annexureDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('annexureMaster') );
    }

    public function status($getRequest) {

        $this -> annexId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> annexId, 2 );

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];
        
        $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

        $result = $this -> annexureModel::update(
            $this -> annexureModel -> getTableName(),
            [ 'is_active' => $updateStatus], 
            [
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> annexId ]
            ]
        );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorSaving') );

        //after insert data redirect to employee master dashboard
        Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
        Redirect::to( SiteUrls::getUrl('annexureMaster') );
    }

    public function columns($getRequest) {

        $this -> annexId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/columns/' . $this -> annexId);
        $this -> me -> pageHeading = 'Authority Management';

        $this -> me -> breadcrumb[] = $this -> me -> id ;

       // get data //method call
       $this -> data['db_data'] = $this -> getDataOr404($this -> annexId);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $this -> data['btn_type'] = 'update';

        //form
        $this -> request::method('GET', function() {

            // load view
            return return2View($this, $this -> me -> viewDir . 'form_columns', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {
            
            if(is_array($this -> request -> input('audit_name')))
            {
                $diffArray = array_diff($this -> request -> input('audit_name'), (array_keys($this -> data['db_audit_unit_data'])));
        
                if(is_array($diffArray) && sizeof($diffArray) > 0)
                {
                    Validation::incrementError($this -> request);
                    $this -> request -> setInputCustom('audit_name_err', 'auditAuthorityError');
                }
            }

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form_columns', [ 'request' => $this -> request ]);
            } 
            else
            {   
                //if there is an blank array then below code

                $updateDataArray = array(
                    'audit_unit_authority' => '', 'admin_id' => Session::get('emp_id')
                );
                
                //if there is an array then below code

                if(is_array($this -> request -> input( 'audit_name' )))

                    $updateDataArray['audit_unit_authority'] = implode(",", $this -> request -> input( 'audit_name' )); 
                

                $result = $this -> annexureModel::update($this -> annexureModel -> getTableName(), 
                    $updateDataArray, [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> annexId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to frequency dashboard
                Validation::flashErrorMsg('auditAuthoritySavedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('annexureMaster') );
            }
        });
    }

    private function getDataOr404($annexId, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL AND is_active = 1',
            'params' => [ 'id' => $annexId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> annexureModel -> getSingleAnnexure($filter);

        if(empty($this -> annexId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>