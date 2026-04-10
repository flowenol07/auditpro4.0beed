<?php

namespace Controllers\CompliancePro;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

class ComplianceCircularHeaderMaster extends Controller  {

    public $me = null, $data, $request, $headerId, $setId;
    public $headerModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        // get all active circulars -----------------------
        $model = $this -> model('ComplianceCircularSetModel');
        $table = $model -> getTableName();

        $select = " SELECT ccs.id, ccs.authority_id, ccs.ref_no, ccs.name, ccs.circular_date, ccs.is_applicable, 
        COALESCE(cca.name, '". ERROR_VARS['notFound'] ."') AS authority FROM 
        ". $table ." ccs LEFT JOIN com_circular_authority cca ON ccs.authority_id = cca.id";

        $this -> data['circularData'] = get_all_data_query_builder(2, $model, $table, [ 'where' => 'ccs.is_applicable = 1 AND ccs.is_active = 1 AND ccs.deleted_at IS NULL', 'params' => [] ], 'sql', $select);
        $this -> data['circularData'] = generate_data_assoc_array($this -> data['circularData'], 'id');

        // top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl( $this -> me -> id ) . '/add' ],
        ];

        // find current menu model
        $this -> headerModel = $this -> model('ComplianceCircularHeaderModel');
    }

    private function validateData($methodType = 'add', $headerId = '')
    {
        $uniqueWhere = [
            'model' => $this -> headerModel,
            'where' => 'name = :name AND circular_set_id = :circular_set_id AND deleted_at IS NULL',
            'params' => [ 
                'name' => $this -> request -> input('name'),
                'circular_set_id' => $this -> request -> input('circular_set_id'),
            ]
        ];

        if($methodType == 'update' && !empty($headerId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $headerId;
        }

        $validationArray = [
            'circular_set_id' => 'required|array_key[circular_array, selectCircularError]',
            'name' => 'required|regex[alphaNumericSymbolsRegex, name]|is_unique[unique_data, headerDuplicate]'
        ];

        Validation::validateData($this -> request, $validationArray, [
            'unique_data' => $uniqueWhere,
            'circular_array' => $this -> data['circularData'],
        ]);

        // validation check
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
            'circular_set_id' => $this -> request -> input('circular_set_id'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> headerModel, 
            $this -> headerModel -> getTableName(), [
                'where' => 'deleted_at IS NULL'
            ]);

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [ 'request' => $this -> request ]);
    }

    public function dataTableAjax()
    {
        $whereArray = [
            'where' => 'deleted_at IS NULL',
            'params' => [ ]
        ];

        $funcData = generate_datatable_data($this, $this -> headerModel, ["circular_set_id", "name"], $whereArray);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = 1 /*check_admin_action($this, ['lite_access' => 0])*/;

            $srNo = 1;

            foreach($funcData['dbData'] as $cHeaderId => $cHeaderDetails)
            {
                $idEncrypt = encrypt_ex_data($cHeaderDetails -> id);

                $circularName = (is_array($this -> data['circularData']) && isset($this -> data['circularData'][ $cHeaderDetails -> circular_set_id ])) ? $this -> data['circularData'][ $cHeaderDetails -> circular_set_id ] -> name : ERROR_VARS['notFound'];

                $cDataArray = [
                    "sr_no" => $srNo,
                    "circular_set_id" => $circularName,
                    "name" => $cHeaderDetails -> name,
                    // "status" => check_active_status($cHeaderDetails -> is_active, 1, 1, 1),
                    "action" => ""
                ];
            
                $srNo++;

                $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . $idEncrypt, 'extra' => view_tooltip('Update') ]);

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }

            unset($headerData);
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function add()
    {
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add' );
        $this -> me -> pageHeading = 'Add Header';

        // top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl( $this -> me -> id ) ]
        ];

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> headerModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';
        $this -> data['need_select'] = true;

        //post method after form submit
        $this -> request::method("POST", function() {

            // validation check
            if($this -> validateData())
            {  
                // insert in database
                $result = $this -> headerModel::insert(
                    $this -> headerModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                // after insert data redirect to menu dashboard
                Validation::flashErrorMsg('questionheaderAddedSuccess', 'success');

                if($this -> request -> has('send_back'))
                    Redirect::to( SiteUrls::getUrl( 'complianceCircularTaskMaster' ) . '/add');
                else
                    Redirect::to( SiteUrls::getUrl( $this -> me -> id ) );
            }
        });

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    public function update($getRequest) {

        $this -> headerId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> headerId));
        $this -> me -> pageHeading = 'Update Header';

        // get data // method call
        $this -> getDataOr404(' AND is_active = 1');

        $this -> data['btn_type'] = 'update';

        //post method after form submit
        $this -> request::method("POST", function() {

            //validation check
            if($this -> validateData('update', $this -> headerId))
            {
                $result = $this -> headerModel::update(
                    $this -> headerModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> headerId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to menu dashboard
                Validation::flashErrorMsg('questionheaderUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl( $this -> me -> id ) );
            }
        });

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'form', [ 
            'request' => $this -> request,
            'data' => $this -> data
        ]);
    }

    private function getDataOr404($optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> headerId ]
        ];

        if(!empty($optional))
            $filter['where'] .= $optional;

        // get data
        if(!empty($this -> headerId))
            $this -> data['db_data'] = $this -> headerModel -> getSingleCircularHeader($filter);

        if(!isset($this -> data['db_data']) || empty($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }
    }
}

?>