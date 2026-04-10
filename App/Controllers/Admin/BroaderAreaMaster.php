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


class BroaderAreaMaster extends Controller  {

    public $me = null, $data, $request, $broaderId;
    public $broaderAreaModel;

    public function __construct($me) {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('broaderAreaMaster') ],
        ];

        // request object created
        $this -> request = new Request();

        // find current audit unit model
        $this -> broaderAreaModel = $this -> model('BroaderAreaModel');
    }

    private function validateData($methodType = 'add', $broaderId = '')
    {
        $uniqueWhere = [
            'model' => $this -> broaderAreaModel,
            'where' => 'name = :name AND deleted_at IS NULL',
            'params' => [ 
                'name' => $this -> request -> input('name')
            ]
        ];

        if($methodType == 'update' && !empty($broaderId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $broaderId;
        }

        $validationArray = [
            'name' => 'required|regex[alphaNumricRegex, name]|is_unique[unique_data, broaderArea]',
            'appetite_percent' => 'required|regex[floatNumberRegex, broaderNumber]',
            'occurance_percent' => 'required|regex[floatNumberRegex, broaderNumber]',
            'magnitude' => 'required|regex[numberRegex, broaderNumber]',
            'frequency' => 'required|regex[numberRegex, broaderNumber]',
            'average_qualitative_count' => 'required|regex[numberRegex, broaderNumber]',
            'average_quantitative_count' => 'required|regex[numberRegex, broaderNumber]'
        ];

        Validation::validateData($this -> request, $validationArray,
        ['unique_data' => $uniqueWhere]);

        //check unit ( HO department )

        if(!$this -> request -> has('section_type_id_err') && 
            $this -> request -> input('section_type_id') != '' && 
            $this -> request -> input('section_type_id') != 1)
        {
            $filterArr = ['section_type_id' => $this -> request -> input('section_type_id')];

            //check HO record exist
            $checkAuditUnit = $this -> broaderAreaModel -> getSingleBroaderArea($filterArr);

            if(!is_object($checkAuditUnit))
            {
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('section_type_id_err', 'auditSectionDupliacateSelect');
            }
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
            'name' => $this -> request -> input('name'),
            'appetite_percent' => get_decimal($this -> request -> input('appetite_percent'), 2),
            'occurance_percent' => get_decimal($this -> request -> input('occurance_percent'), 2),
            'magnitude' => $this -> request -> input('magnitude'),
            'frequency' => $this -> request -> input('frequency'),
            'average_qualitative_count' => $this -> request -> input('average_qualitative_count'),
            'average_quantitative_count' => $this -> request -> input('average_quantitative_count'),
            'admin_id' => Session::get('emp_id'),
        );

        return $dataArray;
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('broaderAreaMaster') . '/add' ],
        ];

        $this -> data['need_datatable'] = true;

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> broaderAreaModel, 
            $this -> broaderAreaModel -> getTableName(), [
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
        $funcData = generate_datatable_data($this, $this -> broaderAreaModel, ["name"]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            // $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0, 'super_access' => 1]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cBroaderAreaId => $cBroaderAreaDetails)
            {
                $markup = '<p class="text-primary mb-1">' . $cBroaderAreaDetails -> name . '</p>

                <p class="font-sm mb-0">Appetite Percent:' . $cBroaderAreaDetails -> appetite_percent . ', Occurance Percent: ' . $cBroaderAreaDetails -> occurance_percent . '</p>

                <p class="font-sm mb-0">Magnitude: '. $cBroaderAreaDetails -> magnitude . ', Frequency: '. $cBroaderAreaDetails -> frequency . ', Average Qualitative Count: ' . $cBroaderAreaDetails -> average_qualitative_count . ', Average Quantitative Count: ' .  $cBroaderAreaDetails -> average_quantitative_count . '</p>';

                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "name" => $markup,
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                // if($CHECK_ADMIN_ACTION)
                // {                         
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cBroaderAreaDetails -> id), 'extra' => view_tooltip('Update') ]);
                // }                
                // else
                //     $cDataArray["action"] .= '';

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
        $this -> me -> pageHeading = 'Add Broader Area';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> broaderAreaModel -> emptyInstance();
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
                $result = $this -> broaderAreaModel::insert(
                    $this -> broaderAreaModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to broader area dashboard
                Validation::flashErrorMsg('broaderAreaAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('broaderAreaMaster') );

            }

        });

    }

    public function update($getRequest) {

        $this -> broaderId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> broaderId));
        $this -> me -> pageHeading = 'Update Broader Area';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> broaderId]) ;

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
            if(!$this -> validateData('update', $this -> broaderId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> broaderAreaModel::update(
                    $this -> broaderAreaModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> broaderId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to audit unit dashboard
                Validation::flashErrorMsg('broaderAreaUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('broaderAreaMaster') );
            }
        });
    }

    // Commented as per advice of Omkar Sir
    // public function delete($getRequest) {

    //     $this -> broaderId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> broaderId, 'deleted_at' => NULL]) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];

    //     $result = $this -> broaderAreaModel::delete($this -> broaderAreaModel -> getTableName(),[
    //         'where' => 'id = :id',
    //         'params' => [ 'id' => $this -> broaderId ]
    //     ]);

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorDeleting') );

    //     //after insert data redirect to audit unit dashboard
    //     Validation::flashErrorMsg('broaderAreaDeletedSuccess', 'success');
    //     Redirect::to( SiteUrls::getUrl('broaderAreaMaster') );
    // }

    // public function status($getRequest) {

    //     $this -> broaderId = isset($getRequest['val_1']) ? $getRequest['val_1'] : '';

    //     // get data //method call
    //     $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> broaderId], 2) ;

    //     //return if data not found
    //     if(!is_object($this -> data['db_data']))
    //         return $this -> data['db_data'];
        
    //     $updateStatus = ($this -> data['db_data'] -> is_active == 1) ? 0 : 1 ;

    //     $result = $this -> broaderAreaModel::update(
    //         $this -> broaderAreaModel -> getTableName(),
    //         [ 'is_active' => $updateStatus], 
    //         [
    //             'where' => 'id = :id',
    //             'params' => [ 'id' => $this -> broaderId ]
    //         ]
    //     );

    //     if(!$result)
    //         return Except::exc_404( Notifications::getNoti('errorSaving') );

    //     //after insert data redirect to audit unit dashboard
    //     Validation::flashErrorMsg((($updateStatus == 1 ) ? 'statusActive' : 'statusInactive'), 'success');
    //     Redirect::to( SiteUrls::getUrl('broaderAreaMaster') );
    // }

    private function getDataOr404($filter, $optional = null) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> broaderId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> broaderAreaModel -> getSingleBroaderArea($filter);

        if(empty($this -> broaderId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>