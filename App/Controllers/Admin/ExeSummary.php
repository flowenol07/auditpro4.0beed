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
use Core\DBCommonFunc;

class ExeSummary extends Controller  {

    public $me = null, $request, $auditUnit, $auditId, $year, $yearId, $data, $marchId;
    public $auditUnitModel, $exeSummaryModel, $yearModel, $financialYear, $targetModel;

    public function __construct($me) 
    {
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        // ------------------- audit logic ------------------

        $this -> auditId = decrypt_ex_data($this -> request -> input('audit'));
        $this -> yearId = decrypt_ex_data($this -> request -> input('year'));

        // find current target model
        $this -> targetModel = $this -> model('TargetMasterModel');

        $this -> data['db_target_data'] = $this -> targetModel -> getSingleTarget([ 
            'where' => 'year_id = :year_id AND audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
            'params' => [ 
                'year_id' => $this -> yearId,
                'audit_unit_id' => $this -> auditId,            
            ]
        ]);

        // require audit model
        $this -> auditUnitModel = $this -> model('AuditUnitModel');        

        $this -> data['db_audit_unit_data'] = null;

        //get single audit details
        if(!empty($this -> auditId))
            $this -> data['db_audit_unit_data'] = $this -> auditUnitModel -> getSingleAuditUnit([
                'where' => 'id = :id AND is_active = 1 AND section_type_id = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $this -> auditId ]
            ]);

        if(!is_object($this -> data['db_audit_unit_data']) || (!is_object($this -> data['db_target_data']) && empty($this -> data['db_target_data'])) ){
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }
        //unset var
        unset($this -> auditUnitModel);

        // ------------------- audit logic ------------------

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('exeSummaryAdmin') . '?audit=' . encrypt_ex_data($this -> auditId).'&year='. encrypt_ex_data($this -> yearId) ],
        ];

        // find current target model
        $this -> exeSummaryModel = $this -> model('ExeSummaryModel'); 

        // find current year model
        $this -> yearModel = $this -> model('YearModel');

        //get all year 
        $this -> year = DBCommonFunc::yearMasterData($this -> yearModel, [
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => ['id' => $this -> yearId]
        ]);

        $this -> data['db_year'] = generate_array_for_select($this -> year, 'id', 'year');

        if(empty($this -> data['db_year'])){
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        //get all single year 
        $this -> data['db_single_year'] = $this -> yearModel -> getSingleYear([
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> yearId ]
        ]);

        //array merged for Gl type array
        $this -> data['gl_type'] = BRANCH_FINANCIAL_POSITION['deposits'] + BRANCH_FINANCIAL_POSITION['advances'] + BRANCH_FINANCIAL_POSITION['npa'];
        
        // top data container 
        $this -> data['data_container'] = true;

    }

    private function validateData($marchId = '')
    {
        $uniqueWhere = [
            'model' => $this -> exeSummaryModel,
            'where' => 'year_id = :year_id AND audit_unit_id = :audit_unit_id AND gl_type_id = :gl_type_id AND deleted_at IS NULL',
            'params' => [ 
                'audit_unit_id' => $this -> auditId,
                'year_id' => $this -> yearId,
                'gl_type_id' => $this -> request -> input('gl_type_id'),
             ]
        ];

        if(!empty($marchId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $marchId;
        }

        Validation::validateData($this -> request, [
            'gl_type_id' => 'required|is_unique[unique_data, glTypeDuplicate]',
            'march_position' => 'required|regex[floatNumberRegex, marchPosition]',
            'm_4' => 'regex[numberRegex, newAccount]',
            'm_5' => 'regex[numberRegex, newAccount]',
            'm_6' => 'regex[numberRegex, newAccount]',
            'm_7' => 'regex[numberRegex, newAccount]',
            'm_8' => 'regex[numberRegex, newAccount]',
            'm_9' => 'regex[numberRegex, newAccount]',
            'm_10' => 'regex[numberRegex, newAccount]',
            'm_11' => 'regex[numberRegex, newAccount]',
            'm_12' => 'regex[numberRegex, newAccount]',
            'm_1' => 'regex[numberRegex, newAccount]',
            'm_2' => 'regex[numberRegex, newAccount]',
            'm_3' => 'regex[numberRegex, newAccount]',
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
            'year_id' => $this -> yearId,
            'audit_unit_id' => $this -> auditId,
            'gl_type_id' => $this -> request -> input('gl_type_id'),
            'march_position' => get_decimal($this -> request -> input('march_position'), 2),

            'm_4' => empty($this -> request -> input('m_4')) ? 0 : $this -> request -> input('m_4'),

            'm_5' => empty($this -> request -> input('m_5')) ? 0 : $this -> request -> input('m_5'),

            'm_6' => empty($this -> request -> input('m_6')) ? 0 : $this -> request -> input('m_6'),

            'm_7' => empty($this -> request -> input('m_7')) ? 0 : $this -> request -> input('m_7'),

            'm_8' => empty($this -> request -> input('m_8')) ? 0 : $this -> request -> input('m_8'),

            'm_9' => empty($this -> request -> input('m_9')) ? 0 : $this -> request -> input('m_9'),

            'm_10' => empty($this -> request -> input('m_10')) ? 0 : $this -> request -> input('m_10'),

            'm_11' => empty($this -> request -> input('m_11')) ? 0 : $this -> request -> input('m_11'),

            'm_12' => empty($this -> request -> input('m_12')) ? 0 : $this -> request -> input('m_12'),

            'm_1' => empty($this -> request -> input('m_1')) ? 0 : $this -> request -> input('m_1'),

            'm_2' => empty($this -> request -> input('m_2')) ? 0 : $this -> request -> input('m_2'),

            'm_3' => empty($this -> request -> input('m_3')) ? 0 : $this -> request -> input('m_3'),

            'admin_id' => Session::get('emp_id'),
        );

        if($methodType == 'update')
            unset($dataArray['password'], $dataArray['password_policy'], $dataArray['admin_id'] );

        return $dataArray;
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('targetMaster') . '?audit=' . encrypt_ex_data($this -> auditId).'&year='. encrypt_ex_data($this -> yearId)]
        ];

        if(check_admin_action($this, ['lite_access' => 0]))
            $this -> data['topBtnArr']['add'] = [ 'href' => SiteUrls::getUrl('exeSummaryAdmin') . '/add?audit=' . encrypt_ex_data($this -> auditId).'&year='. encrypt_ex_data($this -> yearId)];

        $this -> data['db_data'] = $this -> exeSummaryModel -> getAllMarchPosition([
            'where' => 'audit_unit_id = :audit_unit_id AND year_id = :year_id AND deleted_at IS NULL',
            'params' => [
                'audit_unit_id' => $this -> auditId,
                'year_id' => $this -> yearId,
                ]
        ]);

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> exeSummaryModel, 
            $this -> exeSummaryModel -> getTableName(), [
                'where' => 'audit_unit_id = :audit_unit_id AND year_id = :year_id AND deleted_at IS NULL',
                'params' => ['audit_unit_id' => $this -> auditId,
                'year_id' => $this -> yearId]
            ]
        );

        //re assign
        $this -> data['db_data_count'] = $this -> data['db_data_count'] -> total_records;

        if($this -> data['db_data_count'] > 0)
            $this -> data['need_datatable'] = true;

        // // load view //helper function call
        return return2View($this, $this -> me -> viewDir . 'index', ['request' => $this -> request]);
    }

    public function dataTableAjax()
    {
        $funcData = generate_datatable_data($this, $this -> exeSummaryModel, [ "gl_type_id", "march_position"], [
            'where' => 'audit_unit_id = :audit_unit_id AND year_id = :year_id AND deleted_at IS NULL',
            'params' => ['audit_unit_id' => $this -> auditId,
            'year_id' => $this -> yearId]
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            foreach($funcData['dbData'] as $cExeSummaryId => $cExeSummaryDetails)
            {
                $glTypeName = isset($this -> data['gl_type'][$cExeSummaryDetails -> gl_type_id]) ? $this -> data['gl_type'][$cExeSummaryDetails -> gl_type_id] : '';

                $markup = "";

                if(!str_contains($glTypeName , '(NPA)'))
                {
                    for($i = 4 ; $i < 16; $i++)
                    {   
                        if($i <= 12)
                        {
                            $m = $i;
                            $name = 'm_' . $m;
                            $year = $this -> data['db_single_year'] -> year;
                        }
                        else
                        {
                            $m = $i - 12;
                            $name = 'm_' . $m;
                            $year = ($this -> data['db_single_year'] -> year ) + 1;
                        }

                        $month = date("M", strtotime("0000-" . $m . "-01"));


                        $value = ($cExeSummaryDetails -> $name) ? $cExeSummaryDetails -> $name : 0 ;

                        $markup .= '<span class="text-dark font-sm mb-0 d-inline-block"><span class="text-primary font-medium"> '. $month .'-'. $year . ' : </span> ' . $value . '</span>' . ($m == 3 ? "" : " | ");                    
                    }
                }

                if(array_key_exists($cExeSummaryDetails -> year_id, $this -> data['db_year']))
                {    $cDataArray = [
                        "gl_type_id" => $this -> data['gl_type'][$cExeSummaryDetails -> gl_type_id],
                        "march_position" => $cExeSummaryDetails -> march_position,
                        "months" => $markup,
                        "action" => ""
                    ];

                    // For Enable of Action on Assement Start
                    if($CHECK_ADMIN_ACTION)
                    { 
                        $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cExeSummaryDetails -> id) . '?audit=' . encrypt_ex_data($this -> auditId)  . '&year=' . encrypt_ex_data($cExeSummaryDetails -> year_id), 'extra' => view_tooltip('Update') ]);

                        $cDataArray["action"] .= generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cExeSummaryDetails -> id) . '?audit=' . encrypt_ex_data($this -> auditId)   . '&year=' . encrypt_ex_data($cExeSummaryDetails -> year_id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
                    }                
                    else
                        $cDataArray["action"] .= '';

                    // push in array
                    $funcData['dataResArray']["aaData"][] = $cDataArray;
                }
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
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?audit=' . encrypt_ex_data($this -> auditId) . '&year=' . encrypt_ex_data($this -> yearId));
        $this -> me -> pageHeading = 'Add March Position';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> exeSummaryModel -> emptyInstance();
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
                $result = $this -> exeSummaryModel::insert(
                    $this -> exeSummaryModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to march position dashboard
                Validation::flashErrorMsg('marchPositionAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('exeSummaryAdmin') . '/?audit=' . encrypt_ex_data($this -> auditId) . '&year=' . encrypt_ex_data($this -> yearId));

            }

        });

    }

    public function update($getRequest) 
    {

        $this -> marchId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> marchId) . '?audit=' . encrypt_ex_data($this -> auditId) . '&year=' . encrypt_ex_data($this -> yearId));
        $this -> me -> pageHeading = 'Update March Position';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> marchId );

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
            if(!$this -> validateData($this -> marchId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> exeSummaryModel::update(
                    $this -> exeSummaryModel -> getTableName(), 
                    $this -> postArray('update'),[
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> marchId ]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to march position dashboard
                Validation::flashErrorMsg('marchPositionUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('exeSummaryAdmin') . '/?audit=' . encrypt_ex_data($this -> auditId) . '&year=' . encrypt_ex_data($this -> yearId));
            }
        });
    }

    public function delete($getRequest) 
    {

        $this -> marchId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404( $this -> marchId ) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> exeSummaryModel::delete(
            $this -> exeSummaryModel -> getTableName(), [ 
                'where' => 'id = :id',
                'params' => [ 'id' => $this -> marchId ]
            ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to march position dashboard
        Validation::flashErrorMsg('marchPositionDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('exeSummaryAdmin') . '/?audit=' . encrypt_ex_data($this -> auditId) . '&year=' . encrypt_ex_data($this -> yearId));
    }

    private function getDataOr404($marchId, $optional = null) 
    {

        $filter = [ 
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $marchId ]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND deleted_at IS NULL';

        // get data
        $this -> data['db_data'] = $this -> exeSummaryModel -> getSingleMarchPosition($filter);

        if(empty($this -> marchId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>