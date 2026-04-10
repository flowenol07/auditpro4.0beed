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

class YearMaster extends Controller  {

    public $me = null, $request, $data, $yearId;
    public $yearModel;

    public function __construct($me) {

        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('yearMaster') ],
        ];
        
         // request object created
         $this -> request = new Request();

        // find current year model
        $this -> yearModel = $this -> model('YearModel');   
    }

    public function index() {

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
            'add' => [ 'href' => SiteUrls::getUrl('yearMaster') . '/add' ],
        ];

        // total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> yearModel, 
            $this -> yearModel -> getTableName(),
        );

        // re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index');
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> yearModel, ['year']);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $srNo = 1;

            foreach($funcData['dbData'] as $cYearId => $cYearDetails)
            {
                $cDataArray = [
                    "sr_no" => $srNo,
                    "year" => "F.Y. " . $cYearDetails -> year . " - " . ($cYearDetails -> year + 1),
                    "action" => ""
                ];

                $srNo++;

                // For Enable of Action on Assement Start             
                $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {            
                    $cDataArray["action"] .= generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cYearDetails -> id), 'extra' => view_tooltip('Update') ]);

                    $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cYearDetails -> id),
                    'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"']);
                }
                else
                    $cDataArray["action"] .= Notifications::cError( Notifications::getNoti('auditStartDisableAction') );

                // push in array
                $funcData['dataResArray']["aaData"][] = $cDataArray;
            }
        }

        // function call
        $dataResArray = unset_datatable_vars($funcData);
        unset($funcData);

        echo json_encode($dataResArray);
    }

    public function add() {

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add');
        $this -> me -> pageHeading = 'Add Year';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> yearModel -> emptyInstance();
        $this -> data['btn_type'] = 'add';

        //default get method
        $this -> request::method('GET', function() {

            // load view //helper function call
            return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            $uniqueWhere = [
                'model' => $this -> yearModel,
                'where' => 'year = :year AND deleted_at IS NULL',
                'params' => [ 
                    'year' => $this -> request -> input('year'),
                ]
            ];
    
            if(!empty($this -> yearId))
            {
                $uniqueWhere['where'] .= ' AND id != :id';
                $uniqueWhere['params']['id'] = $this -> yearId;
            }
            //check validation
            Validation::validateData($this -> request, [
                'year' => 'required|regex[numberRegex, year]|is_unique[unique_data, yearExist]'
            ],[
            'unique_data' => $uniqueWhere]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $addDataArray = array(
                    'year' => $this -> request -> input( 'year' ),
                    'admin_id' => Session::get('emp_id')
                );

                $result = $this -> yearModel::insert($this -> yearModel -> getTableName(), 
                    $addDataArray
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong'));

                //after insert data redirect to year dashboard
                Validation::flashErrorMsg('yearAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('yearMaster') );
            }

        });

    }

    public function update($getRequest) {

        $this -> yearId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> yearId));
        $this -> me -> pageHeading = 'Update Year';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> yearId]) ;

        // For Enable of Action on Assement Start             
        $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

        if($CHECK_ADMIN_ACTION)
            return Except::exc_404( Notifications::getNoti('auditStartDisableAction') );

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

            $uniqueWhere = [
                'model' => $this -> yearModel,
                'where' => 'year = :year AND deleted_at IS NULL',
                'params' => [ 
                    'year' => $this -> request -> input('year'),
                ]
            ];
    
            if(!empty($this -> yearId))
            {
                $uniqueWhere['where'] .= ' AND id != :id';
                $uniqueWhere['params']['id'] = $this -> yearId;
            }
            //check validation
            Validation::validateData($this -> request, [
                'year' => 'required|regex[numberRegex, year]|is_unique[unique_data, yearExist]'
            ],[
            'unique_data' => $uniqueWhere]);

            //validation check
            if($this -> request -> input( 'error' ) > 0)
            {    
                Validation::flashErrorMsg();
                
                // load view //error data
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $updateDataArray = array(
                    'year' => $this -> request -> input( 'year' ),
                    'admin_id' => Session::get('emp_id')
                );

                $result = $this -> yearModel::update($this -> yearModel -> getTableName(), 
                    $updateDataArray,[
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> yearId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('errorSaving') );

                //after insert data redirect to year dashboard
                Validation::flashErrorMsg('yearUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('yearMaster') );
            }
        });
    }

    public function delete($getRequest) {

        $this -> yearId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> yearId, 'deleted_at' => NULL ]) ;

        // For Enable of Action on Assement Start             
        $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

        if($CHECK_ADMIN_ACTION)
            return Except::exc_404( Notifications::getNoti('auditStartDisableAction') );

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> yearModel::delete($this -> yearModel -> getTableName(), [
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> yearId ]
        ] );

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to year dashboard
        Validation::flashErrorMsg('yearDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('yearMaster') );
    }

    private function getDataOr404($filter) {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> yearId ]
        ];

        // get data
        $this -> data['db_data'] = $this -> yearModel -> getSingleYear($filter);

        if(empty($this -> yearId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>