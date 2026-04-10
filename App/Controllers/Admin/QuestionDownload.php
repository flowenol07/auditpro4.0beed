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

class QuestionDownload extends Controller  {

    public $me = null, $data, $request;
    public $questionMasterModel, $questionSetModel;

    public function __construct($me) 
    {
        $this -> me = $me;

         //top btn array
         $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('questionDownload') ],
        ];
        
        // request object created
        $this -> request = new Request();  

        // find current audit unit model

        $this -> questionSetModel = $this -> model('QuestionSetModel');

        $this -> questionMasterModel = $this -> model('QuestionMasterModel');   

        //get all questions
        $this -> data['questionData'] = $this -> questionMasterModel -> getAllQuestions(['where' => 'is_active = 1 AND deleted_at IS NULL']);

    }

    private function validateData($sectionId = '')
    {
        Validation::validateData($this -> request, 
        [ 'section_id' => 'required|array_key[section_type_array, auditSectionSelect]' ],
        [ 'section_type_array' => $this -> data['auditSectionData'] ]);

        // validation check
        if($this -> request -> input( 'error' ) > 0)
        {    
            Validation::flashErrorMsg();
            return false;
        } 
        else 
            return true;
    }

    private function customErrorFire($errString)
    {
        Validation::flashErrorMsg();
        $this -> request -> setInputCustom( 'error', 1 );
        $this -> request -> setInputCustom( 'section_id_err', Notifications::getNoti( $errString ) );
    }

    private function getQuestionSetWiseQuestions($setIds, $subset = null)
    {
        $setQuestionArray = [];

        if(is_array($setIds) && sizeof($setIds) > 0)
        {
            $select = "SELECT question_set_master.*, question_header_master.id as header_id, question_header_master.name as header_name FROM question_set_master JOIN question_header_master ON question_set_master.id = question_header_master.question_set_id";

            $model = $this -> model('QuestionSetModel');
            
            $findSetMaster = get_all_data_query_builder(2, $model, 'question_set_master', ['where' => "question_set_master.id IN (" . implode(',', array_keys($setIds)) . ")"], 'sql', $select);

            $findSetMaster = generate_data_assoc_array($findSetMaster, 'header_id');
            $headerIds = [];

            if(is_array($findSetMaster) && sizeof($findSetMaster) > 0)
            {
                foreach($findSetMaster as $cHeaderId => $cHeaderDetails)
                {
                    $cMenuId = $setIds[ $cHeaderDetails -> id ]['menu_id'];
                    $cCatId = $setIds[ $cHeaderDetails -> id ]['category_id'];

                    if(is_array($subset))
                    {
                        if(!array_key_exists($cHeaderDetails -> id, $setQuestionArray))
                            $setQuestionArray[ $cHeaderDetails -> id ] = array(
                                'set_name' => trim_str($cHeaderDetails -> name),
                                'header' => array()
                            );

                        // push header
                        if(!array_key_exists(
                            $cHeaderDetails -> header_id, 
                            $setQuestionArray[ $cHeaderDetails -> id ]['header']))
                            $setQuestionArray[ $cHeaderDetails -> id ]['header'][ $cHeaderDetails -> header_id ] = array(
                                'header_name' => trim_str($cHeaderDetails -> header_name),
                                'questions' => array()
                            );
                    }
                    else
                    {
                        // push set
                        if(!array_key_exists($cHeaderDetails -> id, $this -> data['queData'][ $cMenuId ]['category'][ $cCatId ]['set']))
                            $this -> data['queData'][ $cMenuId ]['category'][ $cCatId ]['set'][ $cHeaderDetails -> id] = array(
                                'set_name' => trim_str($cHeaderDetails -> name),
                                'header' => array()
                            );

                        // push header
                        if(!array_key_exists(
                            $cHeaderDetails -> header_id, 
                            $this -> data['queData'][ $cMenuId ]['category'][ $cCatId ]['set'][ $cHeaderDetails -> id ]['header']))
                            $this -> data['queData'][ $cMenuId ]['category'][ $cCatId ]['set'][ $cHeaderDetails -> id ]['header'][ $cHeaderDetails -> header_id ] = array(
                                'header_name' => trim_str($cHeaderDetails -> header_name),
                                'questions' => array()
                            );
                    }

                    if(!in_array($cHeaderDetails -> header_id, $headerIds))
                        $headerIds[] = $cHeaderDetails -> header_id;
                }
            }

            if(sizeof($headerIds) > 0)
            {
                // find question
                $findQuestions = $this -> questionMasterModel -> getAllQuestions([ 'where' => 'header_id IN ('. implode(',', $headerIds) .') AND set_id IN (' . implode(',', array_keys($setIds)) . ') AND is_active = 1 AND deleted_at IS NULL' ]);

                $findQuestions = generate_data_assoc_array($findQuestions, 'id');

                if(is_array($findQuestions) &&  sizeof($findQuestions) > 0)
                {
                    foreach($findQuestions as $cQueId => $cQueDetails)
                    {
                        $cMenuId = $setIds[ $cQueDetails -> set_id ]['menu_id'];
                        $cCatId = $setIds[ $cQueDetails -> set_id ]['category_id'];

                        // push question
                        if(is_array($subset))
                        {
                            $setQuestionArray[ $cQueDetails -> set_id ]['header'][ $cQueDetails -> header_id ]['questions'][ $cQueDetails -> id ] = $cQueDetails;
                        }
                        else
                        {
                            $this -> data['queData'][ $cMenuId ]['category'][ $cCatId ]['set'][ $cQueDetails -> set_id ]['header'][ $cQueDetails -> header_id ]['questions'][ $cQueDetails -> id ] = $cQueDetails;

                            if($cQueDetails -> option_id == 5 && $cQueDetails -> subset_multi_id != '')
                            {
                                $subsetArray = [
                                    'menu_id'     => $cMenuId,
                                    'category_id' => $cCatId,
                                    'set_id'      => $cQueDetails -> set_id,
                                    'header_id'   => $cQueDetails -> header_id,
                                    'question_id' => $cQueDetails -> id,
                                ];

                                $cSetIds = explode(',', $cQueDetails -> subset_multi_id);
                                $cSubSetIds = [];

                                if(is_array($cSetIds) && sizeof($cSetIds) > 0)
                                {
                                    foreach($cSetIds as $cSetId)
                                    {
                                        if(!array_key_exists($cSetId, $setIds))
                                            $cSubSetIds[ $cSetId ] = [
                                                'menu_id' => $subsetArray['menu_id'],
                                                'category_id' => $subsetArray['category_id'],
                                            ];
                                    }
                                }

                                // method call
                                $subsetData = $this -> getQuestionSetWiseQuestions($cSubSetIds, $subsetArray);

                                if( is_array($subsetData) && sizeof($subsetData) > 0 )
                                    $this -> data['queData'][ $cMenuId ]['category'][ $cCatId ]['set'][ $cQueDetails -> set_id ]['header'][ $cQueDetails -> header_id ]['questions'][ $cQueDetails -> id ] -> subset_data = $subsetData;
                            }
                        }
                    }
                }
            }
        }

        if(is_array($subset))
            return $setQuestionArray;
    }

    public function index() 
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ],
        ];

        $this -> data['need_excel'] = true;

        // get all audit sections ---------------------------

        $model = $this -> model('AuditSectionModel');
        $this -> data['auditSectionData'] =  $model -> getAllAuditSection(['where' => 'is_active = 1 AND deleted_at IS NULL']);
        $this -> data['auditSectionData'] = generate_data_assoc_array($this -> data['auditSectionData'], 'id');

        //post method after form submit
        $this -> request::method("POST", function() {

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
                // validation ok

                $sectionDetailsObj = $this -> data['auditSectionData'][ $this -> request -> input('section_id') ];

                $this -> data['section_details_obj'] = $sectionDetailsObj;

                $this -> data['fileName'] = str_replace([' '], ['-'], string_operations($sectionDetailsObj -> name));

                if( $this -> request -> has('broaderAreaWise') && $this -> request -> input('broaderAreaWise') == 1)
                {
                    // get all broader area ---------------------------

                    $model = $this -> model('BroaderAreaModel'); 
                    $this -> data['broaderAreaArray'] = $model -> getAllBroaderArea(['where' => 'deleted_at IS NULL']);
                    $this -> data['broaderAreaArray'] = generate_data_assoc_array($this -> data['broaderAreaArray'], 'id');

                    // get all broader area ---------------------------
                }

                // get all risk category ---------------------------

                $model = $this -> model('RiskCategoryModel');
                $this -> data['getAllRiskCategory'] = $model -> getAllRiskCategory(['where' => 'deleted_at IS NULL']);
                $this -> data['getAllRiskCategory'] = generate_data_assoc_array($this -> data['getAllRiskCategory'], 'id');

                // get all risk category ---------------------------

                // get all menu ---------------------------

                $model = $this -> model('MenuModel');
                $findMenuMaster = $model -> getAllMenu(['where' => 'section_type_id = '. $sectionDetailsObj -> id .' AND is_active = 1 AND deleted_at IS NULL']);
                $findMenuMaster = generate_data_assoc_array($findMenuMaster, 'id');
                
                // get all menu ---------------------------

                $this -> data['queData'] = [];

                if(is_array($findMenuMaster) && sizeof($findMenuMaster) > 0)
                {
                    // find all categories
                    $model = $this -> model('CategoryModel');
                    $findCategoryMaster = $model -> getAllCategory(['where' => 'menu_id IN ('. implode( ',', array_keys($findMenuMaster) ) .') AND is_active = 1 AND deleted_at IS NULL']);

                    $setIds = [];

                    if( is_array($findCategoryMaster) && sizeof($findCategoryMaster) > 0 )
                    {
                        // category data found

                        foreach($findCategoryMaster as $cCatId => $cCatDetails)
                        {
                            $cCatDetails -> menu_id = trim_str($cCatDetails -> menu_id);

                            // push menu
                            if(!array_key_exists( $cCatDetails -> menu_id, $this -> data['queData'] ))
                                $this -> data['queData'][ $cCatDetails -> menu_id ] = array(
                                    'menu_name' => array_key_exists($cCatDetails -> menu_id, $findMenuMaster) ? trim_str($findMenuMaster[ $cCatDetails -> menu_id ] -> name) : ERROR_VARS['notFound'],
                                    'category' => []
                                );

                            // push category
                            if(!array_key_exists( $cCatDetails -> id, $this -> data['queData'][ $cCatDetails -> menu_id ]['category'] ))
                                $this -> data['queData'][ $cCatDetails -> menu_id ]['category'][ $cCatDetails -> id ] = array(
                                    'category_name' => trim_str($cCatDetails -> name),
                                    'set' => array()
                                ); 

                            // push sets
                            if($cCatDetails -> question_set_ids != '')
                            {
                                $cSetIds = explode(',', $cCatDetails -> question_set_ids);

                                if(is_array($cSetIds) && sizeof($cSetIds) > 0)
                                {
                                    foreach($cSetIds as $cSetId)
                                    {
                                        if(!array_key_exists($cSetId, $setIds))
                                            $setIds[ $cSetId ] = [
                                                'menu_id' => $cCatDetails -> menu_id,
                                                'category_id' => $cCatDetails -> id,
                                            ];
                                    }
                                }
                            }
                        }

                        if(is_array($setIds) && sizeof($setIds) > 0)
                        {
                            // method call
                            $this -> getQuestionSetWiseQuestions($setIds);
                        }
                        else
                        {
                            // data not found // method call
                            $this -> customErrorFire('questionSetEmpty');    
                        }
                    }
                    else
                    {
                        // data not found // method call
                        $this -> customErrorFire('categoryEmpty');
                    }
                }
                else
                {
                    // data not found // method call
                    $this -> customErrorFire('menuEmpty');
                }
            }
        });

        //load view // helper function call
        return return2View($this, $this -> me -> viewDir . 'index', [
            'data' => $this -> data,
            'request' => $this -> request,
        ]);
    }
}

?>