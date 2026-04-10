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


class AnnexureColumn extends Controller  {

    public $me = null, $annexId, $data, $request, $annexure, $question, $colId;
    public $annexureModel, $annexureColumnModel, $questionModel;

    public function __construct($me) {
        $this -> me = $me;
        $this -> me -> menuKey = 'annexureMaster';

        // request object created
        $this -> request = new Request();

        // ------------------- annexure logic ------------------

        $this -> annexId = decrypt_ex_data($this -> request -> input('annex'));

        // require annexure model
        $this -> annexureModel = $this -> model('AnnexureMasterModel');        

        $this -> data['db_annex'] = null;

        //get single annex details
        if(!empty($this -> annexId))
            $this -> data['db_annex'] = $this -> annexureModel -> getSingleAnnexure([
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $this -> annexId ]
            ]);

        if( !is_object($this -> data['db_annex']) ) {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        //unset var
        unset($this -> annexureModel);

        // ------------------- annexure logic ------------------

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('annexureColumns') . '?annex=' . encrypt_ex_data($this -> annexId) ],
        ];

        // find current annexure columns model
        $this -> annexureColumnModel = $this -> model('AnnexureColumnModel');

        // find current question model
        $this -> questionModel = $this -> model('QuestionMasterModel');  

        //get all question
        $this -> question = $this -> questionModel -> getAllQuestions([
            'where' => 'annexure_id = :annexure_id AND is_active = 1 AND deleted_at IS NULL',
            'params' =>['annexure_id' => $this -> annexId],
        ]);

        $this -> data['db_question'] = generate_array_for_select($this -> question, 'id', 'question');

        // echo empty($this -> data['db_question']);
        
        // top data container 
        $this -> data['data_container'] = true;
    }

    private function validateData($methodType = 'add', $colId = '', $annexId = '')
    {

        $uniqueWhere = [
            'model' => $this -> annexureColumnModel,
            'where' => 'name = :name AND annexure_id = :annexure_id AND deleted_at IS NULL',
            'params' => 
            [ 'name' => $this -> request -> input('name'),
              'annexure_id' => decrypt_ex_data($this -> request -> input('annex')),
            ]
        ];

        if(!empty($colId))
        {
            $uniqueWhere['where'] .= ' AND id != :id';
            $uniqueWhere['params']['id'] = $colId;
        }

        $validationArray = [
            'name' => 'required|is_unique[unique_data, columnDuplicate]'
        ];

        Validation::validateData($this -> request, $validationArray,[
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
        if($this -> request -> input('column_type_id') == 3)
        {
            $optionArray = array(
                'option' => $this -> request -> input('option'),
            );

            $optArr = [];

            if(!isset($optionArray['option']))
            {
                $optArr[] = ["column_option" => ""];
            }
            else
            {
                for($i = 0; $i < count($optionArray['option']); $i++)
                {
                    $optArr[] = ["column_option" => $optionArray['option'][$i], "extrafield" => "0"];
                }
            }
        }
        else
        {
            $optArr[] = ["column_option" => ""];
        }

        $jsonArr = json_encode($optArr);

        $dataArray = array(
            'annexure_id' => decrypt_ex_data($this -> request -> input('annex')),
            'name' => string_operations($this -> request -> input('name'), 'upper'),
            'column_type_id' => $this -> request -> input('column_type_id'),
            'column_options' => $jsonArr,
            'admin_id' => Session::get('emp_id'),
        );

      return $dataArray;
    }

    public function index($getRequest) 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('annexureMaster') ],
            'add' => [ 'href' => SiteUrls::getUrl('annexureColumns') . '/add?annex=' . encrypt_ex_data($this -> annexId) ],
        ];

        //total number of records without filtering // function call
        $this -> data['db_data_count'] = get_db_table_sql_count(
            $this, 
            $this -> annexureColumnModel, 
            $this -> annexureColumnModel -> getTableName(), [
                'where' => 'deleted_at IS NULL AND annexure_id =:annexure_id',
                'params' => [ 'annexure_id' => $this -> annexId ]]);

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
        $funcData = generate_datatable_data($this, $this -> annexureColumnModel, ["name"], [
            'where' => 'deleted_at IS NULL AND annexure_id =:annexure_id',
            'params' => ['annexure_id' => $this -> annexId ]
        ]);

        if(is_array($funcData['dbData']) && sizeof($funcData['dbData']) > 0)
        {
            $funcData['dbData'] = generate_data_assoc_array($funcData['dbData'], 'id');

            // For Enable of Action on Assement Start             
            $CHECK_ADMIN_ACTION = check_admin_action($this, ['lite_access' => 0]);

            $srNo = 1;

            foreach($funcData['dbData'] as $cAnnexColId => $cAnnexColDetails)
            {
                $options = '';

                $name = '<p class="text-primary mb-1">' . $cAnnexColDetails -> name . '</p>';
                $column_type_id = '<p class="text-secondary mb-1">' . $GLOBALS['columnTypeArray'][$cAnnexColDetails -> column_type_id] . '</p>';

                if(json_decode($cAnnexColDetails -> column_options))
                {
                    for($i = 0; $i < count(json_decode($cAnnexColDetails -> column_options)); $i++)
                    {
                        if((json_decode($cAnnexColDetails -> column_options)[$i] -> column_option) == "")
                        {
                            $options = "<p></p>";
                        }
                        else
                        {
                            $options .= "<p class='text-secondary mb-1'> Option ". $i + 1 . "</p>
                            <span>" . json_decode($cAnnexColDetails -> column_options)[$i] -> column_option . "</span><br><hr>";
                        }
                    }
                }

                $cDataArray = [
                    "sr_no" =>  $srNo,
                    "name" => $name,
                    "column_type_id" => $column_type_id,
                    "options" => $options,
                    "action" => ""
                ];
            
                $srNo++;

                // For Enable of Action on Assement Start
                if($CHECK_ADMIN_ACTION)
                {                         
                    $cDataArray["action"] .=  generate_link_button('update', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/update/' . encrypt_ex_data($cAnnexColDetails -> id) . '?annex=' . encrypt_ex_data($this -> annexId), 'extra' => view_tooltip('Update') ]);

                    if(empty($this -> data['db_question']))
                        $cDataArray["action"] .=  generate_link_button('delete', ['href' => SiteUrls::setUrl( $this -> me -> url ) . '/delete/' . encrypt_ex_data($cAnnexColDetails -> id) .'?annex='. encrypt_ex_data($this -> annexId), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
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
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/add?annex=' . encrypt_ex_data($this -> annexId));
        $this -> me -> pageHeading = 'Add Column';

        // create empty instance for default values in form
        $this -> data['db_data'] = $this -> annexureColumnModel -> emptyInstance();
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
                $result = $this -> annexureColumnModel::insert(
                    $this -> annexureColumnModel -> getTableName(), 
                    $this -> postArray()
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to annexure column dashboard
                Validation::flashErrorMsg('annexColumnAddedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('annexureColumns') . '/?annex=' . encrypt_ex_data($this -> annexId) );

            }

        });

    }

    public function update($getRequest) 
    {
        $this -> colId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';
         
        //set form url
        $this -> me -> url = SiteUrls::setUrl( $this -> me -> url . '/update/' . encrypt_ex_data($this -> colId) . '?annex=' . encrypt_ex_data($this -> annexId));
        $this -> me -> pageHeading = 'Update Column';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> colId]) ;

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
            if(!$this -> validateData('update', $this -> colId))
            {   
                // load view
                return return2View($this, $this -> me -> viewDir . 'form', [ 'request' => $this -> request ]);
            } 
            else
            {
                $result = $this -> annexureColumnModel::update(
                    $this -> annexureColumnModel -> getTableName(), 
                    $this -> postArray('update'),
                    [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> colId]
                    ]
                );

                if(!$result)
                    return Except::exc_404( Notifications::getNoti('somethingWrong') );

                //after insert data redirect to annexure Column dashboard
                Validation::flashErrorMsg('annexColumnUpdatedSuccess', 'success');
                Redirect::to( SiteUrls::getUrl('annexureColumns')  . '/?annex=' . encrypt_ex_data($this -> annexId) );
            }
        });
    }

    public function delete($getRequest) 
    {
        $this -> colId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404([ 'id' => $this -> colId, 'deleted_at' => NULL, 'is_active' => 1 ]) ;

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $result = $this -> annexureColumnModel::delete($this -> annexureColumnModel -> getTableName(),[
            'where' => 'id = :id',
            'params' => [ 'id' => $this -> colId ]
        ]);

        if(!$result)
            return Except::exc_404( Notifications::getNoti('errorDeleting') );

        //after insert data redirect to annexure Column dashboard
        Validation::flashErrorMsg('annexColumnDeletedSuccess', 'success');
        Redirect::to( SiteUrls::getUrl('annexureColumns') . '/?annex=' . encrypt_ex_data($this -> annexId));
    }

    private function getDataOr404($filter, $optional = null) 
    {   
        $filter = [ 
            'where' => 'id = :id AND annexure_id = :annexure_id AND deleted_at IS NULL',
            'params' => [ 'id' => $this -> colId, 'annexure_id' => $this -> annexId]
        ];

        if($optional == 2)
            $filter['where'] = 'id = :id AND annexure_id = :annexure_id AND deleted_at IS NULL';


        // get data
        $this -> data['db_data'] = $this -> annexureColumnModel -> getSingleAnnexureColumns($filter);

        if(empty($this -> colId) || empty($this -> data['db_data']) )
            return Except::exc_404( Notifications::getNoti('errorFinding') );

        return $this -> data['db_data'];
    }
}

?>