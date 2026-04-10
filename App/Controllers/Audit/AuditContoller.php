<?php

namespace Controllers\Audit;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;
use Exception;

// extra common functions 03.07.2024
require_once 'AuditCommonCodeHelper.php';
require_once APP_CORE . DS . 'HelperFunctionsAuditReport.php';

class AuditContoller extends Controller {

    public $me = null, $request, $data, $menuData, $assesId, $assesmentData, $catId, $needSampling, $accId, $questionsData;
    public $ansModel, $auditAssesmentModel, $ansAnnexModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> auditAssesmentModel = $this -> model('AuditAssesmentModel');
        $this -> ansModel = $this -> model('AnswerDataModel');        
        $this -> ansAnnexModel = $this -> model('AnswerDataAnnexureModel'); 
    }

    public function getAssesmentData($res = false)
    {
        //get data from session
        $this -> assesId = decrypt_ex_data(Session::get('audit_id'));

        // helper function call
        $this -> assesmentData = get_assesment_details($this, Session::get('emp_id'), $this -> assesId);

        if($res)
            return $this -> assesmentData;

        if( !is_object($this -> assesmentData) || !in_array($this -> assesmentData -> audit_status_id, [1,3]) )
        {
            Except::exc_404( Notifications::getNoti('assesmentNotFound') );
            exit;
        }

        //top data container
        $this -> data['data_container'] = true;

        $this -> assesmentData -> menu_ids_explode = !empty($this -> assesmentData -> menu_ids) ? explode(',', $this -> assesmentData -> menu_ids) : [];
        $this -> assesmentData -> cat_ids_explode = !empty($this -> assesmentData -> cat_ids) ? explode(',', $this -> assesmentData -> cat_ids) : [];
        $this -> assesmentData -> header_ids_explode = !empty($this -> assesmentData -> header_ids) ? explode(',', $this -> assesmentData -> header_ids) : [];
        $this -> assesmentData -> question_ids_explode = !empty($this -> assesmentData -> question_ids) ? explode(',', $this -> assesmentData -> question_ids) : [];
        $this -> assesmentData -> advances_scheme_ids_explode = !empty($this -> assesmentData -> advances_scheme_ids) ? explode(',', $this -> assesmentData -> advances_scheme_ids) : [];
        $this -> assesmentData -> deposits_scheme_ids_explode = !empty($this -> assesmentData -> deposits_scheme_ids) ? explode(',', $this -> assesmentData -> deposits_scheme_ids) : [];
    }

    public function getMenuData($reAuditFindData = null)
    {
        return get_menu_category_mix($this, $this -> assesmentData, $reAuditFindData);
    }
    
    public function index($getRequest)
    {
        // top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('dashboard') ]
        ];

        // CHECK FOR CARRY FORWARD
        if( check_carry_forward_strict() )
        {
            $CF_MENU_URL = isset($_GET['url']) ? $_GET['url'] : null;
        
            if(!strpos($CF_MENU_URL, 'category'))
            {
                $CF_MENU_URL = preg_replace('/^audit\//', '', $CF_MENU_URL);
                $CF_MENU_URL = decrypt_ex_data($CF_MENU_URL);
        
                if($CF_MENU_URL == CARRY_FORWARD_ARRAY['id'])
                {
                    //need audit assesment js
                    $this -> data['js'][] = 'audit-cf-save-script.js';
                    
                    $this -> displayCarryForwardPanel();
                    exit;
                }
            }
        }
        
        $this -> getAssesmentData(); //method call

        $reAuditFindData = null;

        // function call // 13.06.2024
        if( check_re_assesment_status($this -> assesmentData) )
            $reAuditFindData = get_re_audit_menu_data($this);

        if( check_re_assesment_status($this -> assesmentData) &&
            is_array($reAuditFindData) && 
            is_array($reAuditFindData['menu']) && 
            !(sizeof($reAuditFindData['menu']) > 0) )
        {
            Except::exc_404( Notifications::getNoti('somethingWrong') );
            exit;
        }

        $this -> menuData = $this -> getMenuData($reAuditFindData); // method call

        // helper function call
        $this -> assesmentData = get_assesment_all_details($this, null, $this -> assesmentData);

        $this -> me -> pageHeading = string_operations('Audit Details', 'upper');

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        return return2View($this, $this -> me -> viewDir . 'index', [ 
            'request' => $this -> request,
            'menu_data' => $this -> menuData,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);
    }

    private function reAuditFindData($returnData = null, $res = false, $extra = [])
    {
        // function call
        return get_re_audit_find_data($this, $returnData, $res, $extra);
    }

    private function reAuditSortDataCategoryWise($reAuditFindData, $whereData)
    {
        $ans = [ 'ans' => [], 'annex_ids' => [] ];

        // print_r($reAuditFindData['ans']);

        if(is_array($reAuditFindData['ans']) && sizeof($reAuditFindData['ans']) > 0)
        {
            $dumpId = isset($whereData['params']['dump_id']) ? $whereData['params']['dump_id'] : 0;

            foreach($reAuditFindData['ans'] as $cAnsId => $cAnsDetails)
            {
                // push ans
                if( $cAnsDetails -> category_id == $whereData['params']['category_id'] && 
                    $cAnsDetails -> dump_id == $dumpId )
                {
                    $ans['ans'][ $cAnsDetails -> id ] = $cAnsDetails;

                    // check ans has annex data
                    if( isset($cAnsDetails -> annex_ans) && 
                        is_array($cAnsDetails -> annex_ans) &&
                        sizeof($cAnsDetails -> annex_ans) > 0)
                    {
                        foreach($cAnsDetails -> annex_ans as $cAnnexId => $cAnnexData) {
                            $ans['annex_ids'][ ] = $cAnnexData -> id;
                        }   
                    }
                }
            }
        }

        return $ans;
    }

    public function menu($getRequest)
    {
        //currently not direct menu
        Except::exc_404( Notifications::getNoti('errorFinding') );
        exit;
    }

    public function category($getRequest)
    {
        $this -> catId = isset($getRequest['val_1']) ? decrypt_ex_data($getRequest['val_1']) : '';
        $this -> accId = isset($getRequest['ac']) ? decrypt_ex_data($getRequest['ac']) : '';
        $this -> needSampling = isset($getRequest['smpl']);
        
        $this -> getAssesmentData(); //method call

        $reAuditFindData = null;

        // CHECK FOR RE AUDIT
        if( check_re_assesment_status($this -> assesmentData) )
        {
            // function call // 13.06.2024
            $reAuditFindData = get_re_audit_menu_data($this);

            if( is_array($reAuditFindData) && 
                array_key_exists('category', $reAuditFindData) && 
                is_array($reAuditFindData['category']) && 
                sizeof($reAuditFindData['category']) > 0 &&
                !in_array($this -> catId, $reAuditFindData['category']))
            {
                $this -> catId = null;
            }
        }

        $model = $this -> model('CategoryModel');

        $this -> data['db_category'] = null;

        if(!empty($this -> catId))
        {
            $this -> data['db_category'] = $model -> getSingleCategory([
                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $this -> catId ]
            ]);
        }

        if( is_object($this -> data['db_category']) )
        {
            // find menu details
            $model = $this -> model('MenuModel');

            $this -> data['db_category'] -> db_menu = $model -> getSingleMenu([
                'where' => ' id = :id AND is_active = 1 AND deleted_at IS NULL',
                'params' => [ 'id' => $this -> data['db_category'] -> menu_id ]
            ]);
        }

        // if object not found
        if( !is_object($this -> data['db_category']) || 
            empty($this -> data['db_category'] -> question_set_ids) || (
                is_object($this -> data['db_category']) && !is_object($this -> data['db_category'] -> db_menu)
            ) )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        //change me
        $this -> me = SiteUrls::get('auditCategory');

        $this -> me -> pageHeading = string_operations($this -> data['db_category'] -> name, 'upper');
        $this -> me -> menuKey = 'cat_' . $this -> data['db_category'] -> id;
        // $this -> data['need_select'] = true;

        $this -> menuData = $this -> getMenuData($reAuditFindData); // method call

        // if re audit started then check for subset
        // if( is_object($this -> data['db_category']) && is_array($reAuditFindData) && 
        //     array_key_exists('category', $reAuditFindData) && 
        //     is_array($reAuditFindData['category']) && 
        //     sizeof($reAuditFindData['category']) > 0 &&
        //     in_array($this -> catId, $reAuditFindData['category']))
        // {
        //     // add sub set ids in set for reaudit find subset questions
        //     $this -> data['db_category'] = $this -> findSubsetSets($this -> data['db_category']);
        // }

        // re audit status // function call
        if( check_re_assesment_status($this -> assesmentData) )
            $reAuditFindData = $this -> reAuditFindData($reAuditFindData, false, [ 'catId' => $this -> data['db_category'] -> id ]);

        // print_r($reAuditFindData);
        // exit;

        //top data container
        $this -> data['data_container'] = true;

        $findDataBool = true;

        $this -> data['db_scheme_codes'] = null;
        $this -> data['sampling_link'] = SiteUrls::getUrl('auditCategory') . encrypt_ex_data($this -> catId) . '?smpl=1';
        $this -> data['get_acc_id'] = $this -> accId;

        $ansWhereData = [
            'where' => 'assesment_id = :assesment_id AND category_id = :category_id AND deleted_at IS NULL',
            'params' => [ 
                'assesment_id' => $this -> assesmentData -> id,
                'category_id' => $this -> data['db_category'] -> id 
            ]            
        ];

        // check for ADVANCES / DEPOSITS
        if( array_key_exists($this -> data['db_category'] -> linked_table_id, $GLOBALS['schemeTypesArray']) )
        {
            if($this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'])
            {
                if(isset($getRequest['rm']) && $getRequest['rm'] == 1 && !empty($this -> accId))
                {
                    $this -> removeDumpSampling($this -> data['db_category']);
                    exit();
                }

                if($this -> needSampling)
                {
                    // method call
                    $this -> dumpSampling();
                    exit();
                }
            }

            if(empty($this -> accId))
            {
                // method call
                $this -> dumpSelectSampling(1, $reAuditFindData);
                exit();
            }
            else
            {
                // find current account // with all account

                // method call
                $this -> dumpSelectSampling(0, $reAuditFindData);

                if( !is_array($this -> data['db_dump_data']) || 
                    !array_key_exists($this -> accId, $this -> data['db_dump_data']) )
                {
                    Except::exc_404( Notifications::getNoti('samplingAccNotFound') );
                    exit;
                }

                // accound details found
                if( is_array($this -> data['db_dump_data']) && 
                    array_key_exists($this -> accId, $this -> data['db_dump_data']) )
                {
                    if( isset($getRequest['mac']) )
                    {
                        // complete assesments
                        if($getRequest['mac'] == 'all')
                        {
                            //for all accounts
                            if( $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'])
                                $this -> dumpCompleteAssesment($this -> data['db_category'], array_keys($this -> data['db_dump_data']), true);
                        }
                        elseif( empty($this -> data['db_dump_data'][ $this -> accId ] -> assesment_period_id) )
                            $this -> dumpCompleteAssesment($this -> data['db_category'], $this -> data['db_dump_data'][ $this -> accId ]);
                    }
                }

                // account found
                $ansWhereData['where'] .= ' AND dump_id = :dump_id';
                $ansWhereData['params']['dump_id'] = $this -> accId;
            }
        }
        
        // get set
        $this -> data['db_sets'] = get_set_all_data(
            $this, 
            $this -> data['db_category'] -> question_set_ids, 
            $this -> assesmentData, false, 
            $reAuditFindData, 
            $this -> accId // for single account
        );

        // print_r($this -> data['db_category']);
        // exit;

        // divide array // convert to id
        $this -> questionsData = generate_data_assoc_array($this -> data['db_sets']['questionsData'], 'id');
        // print_r($this -> questionsData);

        $this -> data['db_sets'] = $this -> data['db_sets']['returnData'];

        if( !sizeof($this -> data['db_sets']) > 0 )
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        if( check_re_assesment_status($this -> assesmentData) )
        {
            // code update due single category wise data 16.05.2024
            $reAssesData = $this -> reAuditSortDataCategoryWise($reAuditFindData, $ansWhereData);

            $this -> data['db_ans'] = $reAssesData['ans'];

            if( is_array($this -> data['db_ans']) && 
                sizeof($this -> data['db_ans']) > 0 && 
                check_evidence_upload_strict() )
            {
                // find evidence 
                $this -> data['db_ans'] = $this -> getEvidenceData($this -> data['db_ans'], array_keys($this -> data['db_ans']));

                if(is_array($reAssesData['annex_ids']) && sizeof($reAssesData['annex_ids']) > 0)
                    $this -> getEvidenceData($this -> data['db_ans'], $reAssesData['annex_ids'], 2);
            }

            // unset vars
            unset($reAssesData);
        }
        else
        {
            // get all answers
            $this -> data['db_ans'] = $this -> ansModel -> getAllAnswers($ansWhereData);
            $this -> data['db_ans'] = generate_data_assoc_array($this -> data['db_ans'], 'id');        

            if(is_array($this -> data['db_ans']) && sizeof($this -> data['db_ans']) > 0 && check_evidence_upload_strict())
            {
                // find evidence 
                $this -> data['db_ans'] = $this -> getEvidenceData($this -> data['db_ans'], array_keys($this -> data['db_ans']));
            }

            // method call
            $this -> data['db_ans'] = $this -> getAnnexAnswers($this -> data['db_ans']);
        }

        $model = $this -> model('RiskMatrixModel');

        // get risks // business risk -------------------------
        $this -> data['db_business_risk_matrix'] = $model -> getAllRiskMatrix([
            'where' => 'year_id = :year_id AND business_risk_app = 1',
            'params' => [ 'year_id' => $this -> assesmentData -> year_id ]
        ]);

        //helper function call
        $this -> data['db_business_risk_matrix'] = convert_risk_matrix($this -> data['db_business_risk_matrix']);

        // get risks // control risk -------------------------
        $this -> data['db_control_risk_matrix'] = $model -> getAllRiskMatrix([
            'where' => 'year_id = :year_id AND control_risk_app = 1',
            'params' => [ 'year_id' => $this -> assesmentData -> year_id ]
        ]);

        $model = $this -> model('RiskCategoryModel');

        // get risks // risk category -------------------------
        $this -> data['db_risk_category'] = $model -> getAllRiskCategory([
            'where' => 'is_active = 1 AND deleted_at IS NULL',
            'params' => [ ]
        ]);

        //helper function call
        $this -> data['db_risk_category'] = generate_data_assoc_array($this -> data['db_risk_category'], 'id');

        //need audit assesment js
        $this -> data['js'][] = 'audit-assesment.js';
        $this -> data['js'][] = 'audit-remark-save-script.js';

        // annex upload csv if 
        if( $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'])
        {
            $this -> data['js'][] = 'audit-assesment-annex-upload.js';
            $this -> data['isCSVUpload'] = true;
        }

        if( check_evidence_upload_strict() )
        {
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidence-auditpro.min.js';
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidencet-compulsary-checkbox.js';
        }

        // remove explode keys
        unset( $this -> assesmentData -> menu_ids_explode, $this -> assesmentData -> cat_ids_explode, 
        $this -> assesmentData -> header_ids_explode, $this -> assesmentData -> question_ids_explode,
        $this -> assesmentData -> advances_scheme_ids_explode, $this -> assesmentData -> deposits_scheme_ids_explode );

        $this -> data['db_assesment_data'] = $this -> assesmentData;

        //post method after form submit
        $this -> request::method("POST", function() {

            // method call
            $res = [ 'msg' => Notifications::getNoti('blankAnswers'), 'data' => null ];

            if( is_array($this -> request -> input('data')) && 
                sizeof($this -> request -> input('data')) > 0 && 
                sizeof($this -> questionsData) > 0 )
            {
                // for single ans save
                $res = $this -> saveAnswers($res, $this -> request -> input('data'), true, true);
            }
            
            if( is_array($this -> request -> input('data_annex')) && is_array($this -> request -> input('data_annex_ques')) && 
                sizeof($this -> request -> input('data_annex')) > 0 && sizeof($this -> request -> input('data_annex_ques')) > 0 &&
                sizeof($this -> questionsData) > 0 )
            {
                // $res = $this -> questionsData;
                require_once APP_VIEWS . '/audit/audit-common-code.php';

                // for annexure save //method call
                $res = $this -> saveAnnexAnswers($res);

                if(isset($res['data']) && is_array($res['data']))
                {
                    $res['data_annex'] = $res['data_annex'];
                    $res['data_annex']['id'] = encrypt_ex_data($res['data_annex']['id']);
                    $res['msg'] = Notifications::getNoti( 'annexAnswerSaveSuccess' );
                    $res['success'] = true;

                    $res['data_annex_markup'] = generate_annex_ans_markup([ 'data' => $this -> data ], $this -> questionsData[ $res['data_db']['id'] ], $res['data_annex_obj'] );

                    // unset vars
                    unset( $res['data_question'], $res['data'], $res['data_db'], $res['data_annex_obj'],
                           $res['data_annex']['br'], $res['data_annex']['cr'], $res['data_annex']['rt'] );
                }
            }                

            echo json_encode($res);
            exit;

        });

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);
        $this -> data['accId'] = $this -> accId;

        return return2View($this, $this -> me -> viewDir . 'dynamic-form', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'menu_data' => $this -> menuData,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);

    }

    private function checkQuestionExists($genKey, $res, $type = 'insert')
    {
        $res_arr = null;

        if(is_array($res) && sizeof($res) > 0)
        {
            foreach($res as $cId => $cDetails)
            {
                $cGenKey = $cDetails['question_id'] . '_' . $cDetails['header_id'] . '_' . $cDetails['dump_id'];

                if($cGenKey == $genKey)
                {
                    $res_arr = $cDetails;

                    if($type == 'update')
                        $res_arr['id'] = $cId;
                }
            }
        }
    
        return $res_arr;
    }

    public function dumpCompleteAssesment($categoryDetails, $accDetails, $allAccounts = false)
    {
        $dumpModel = $this -> model('DumpAdvancesModel');
        $queryFire = true;

        if($allAccounts && 
          ( !is_array($accDetails) || is_array($accDetails) && !sizeof($accDetails) > 0) )
            $queryFire = false;

        if($categoryDetails -> linked_table_id == 1)
            $dumpModel = $this -> model('DumpDepositeModel');

        if($queryFire && $allAccounts)
            $whereData = [
                'where' => 'id IN ('. implode(',', $accDetails) .') AND branch_id = :branch_id AND account_opening_date BETWEEN :fromAcc AND :fromTo AND sampling_filter = 1 AND assesment_period_id = 0 AND deleted_at IS NULL',
                'params' => [ 
                    'fromAcc' => $this -> assesmentData -> assesment_period_from, 
                    'fromTo' => $this -> assesmentData -> assesment_period_to,
                    'branch_id' => $this -> assesmentData -> audit_unit_id
                ]
            ];
        else
            $whereData = [
                'where' => 'id = :id AND deleted_at IS NULL',
                'params' => [ 'id' => $accDetails -> id ]
            ];

        $result = false;

        if($queryFire)
        {
            // update account
            $result = $dumpModel::update(
                $dumpModel -> getTableName(), 
                [ 'assesment_period_id' => $this -> assesmentData -> id ],
                $whereData
            );
        }

        if(!$queryFire || !$result)
            Validation::flashErrorMsg('somethingWrong', 'warning');
        else
            Validation::flashErrorMsg('dumpMarkAsComplete', 'success');

        $redirect = SiteUrls::getUrl('auditCategory') . encrypt_ex_data($this -> catId);

        if(is_object($accDetails))
            $redirect .= '?ac=' . encrypt_ex_data($accDetails -> id);
        else if(is_array($accDetails) && sizeof($accDetails) > 0)
            $redirect .= '?ac=' . encrypt_ex_data(array_values($accDetails)[0]);

        Redirect::to( $redirect );
    }

    public function dumpSampling()
    {
        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('auditCategory') . encrypt_ex_data($this -> catId) ],
        ];
        
        $this -> data['sampling_filter'] = array(
            '1' => 'Block Sampling',
            '2' => 'High Value Sampling',
            '3' => 'Systematic Sampling - Below 1 Lakhs',
            '4' => 'Systematic Sampling - Between 1 Lakhs To 2 Lakhs',
            '5' => 'Systematic Sampling - Above 2 Lakhs',
        );

        if( $this -> request -> has('ft') && array_key_exists($this -> request -> input('ft'), $this -> data['sampling_filter']) )
        {
            // pr1 required every time
            if(!$this -> request -> has('pr1') || empty($this -> request -> input('pr1')))
            {
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('pr1_err', 'required');
            }

            // pr2 Block Sampling
            if($this -> request -> input('ft') == '1' && ( !$this -> request -> has('pr2') || empty($this -> request -> input('pr2')) ))
            {
                Validation::incrementError($this -> request);
                $this -> request -> setInputCustom('pr2_err', 'required');
            }
        }

        $this -> data['db_acc_found_count'] = 0;
        $this -> data['db_scheme_codes'] = null;

        if($this -> request -> input( 'error' ) > 0)
            Validation::flashErrorMsg();
        else
        {
            // find data
            $whereData = [ 'where' => '', 'params' => '' ];
            $amountCol = ($this -> data['db_category'] -> linked_table_id == 2) ? 'sanction_amount' : 'principal_amount';

            // '1' => 'Block Sampling'
            if($this -> request -> input('ft') == '1')
            {
                $whereData['where'] = 'account_no BETWEEN :fromAcc AND :fromTo';
                $whereData['params'] = [ 'fromAcc' => $this -> request -> input('pr1'), 'fromTo' => $this -> request -> input('pr2')];
            }
            else
            {                
                if($this -> request -> input('ft') == 3)
                    $whereData['where'] = $amountCol . ' < 100000';
                elseif($this -> request -> input('ft') == 4)
                    $whereData['where'] = $amountCol . ' BETWEEN 100000 AND 200000';
                elseif($this -> request -> input('ft') == 5)
                    $whereData['where'] = $amountCol . ' > 200000';
                else
                    $whereData['where'] = '';

                $whereData['params'] = [];
            }
            
            // helper function call
            $resData = get_category_dump_data($this, $this -> data['db_category'], $this -> assesmentData, 2, $whereData, ($amountCol . '+0 DESC'));
            $this -> data['db_dump_count'] = is_object($resData['count']) ? $resData['count'] -> total : 0;
            $this -> data['db_scheme_codes'] = $resData['scheme_data'];
            $this -> data['db_dump_data'] = $resData['dump_data'];
            $this -> data['db_display_percentage'] = $this -> data['db_dump_count'];

            // unset
            unset($resData);

            if(is_array($this -> data['db_scheme_codes']) && sizeof($this -> data['db_scheme_codes']) > 0) {
                // has data // filter with percentage
                if( in_array($this -> request -> input('ft'), [2,3,4,5]) && 
                    $this -> request -> input('pr1') > 0 && $this -> request -> input('pr1') <= 100)
                {
                    $this -> data['db_display_percentage'] = ($this -> data['db_display_percentage'] / 100) * $this -> request -> input('pr1');
                    $this -> data['db_display_percentage'] = ($this -> data['db_display_percentage'] > 1) ? $this -> data['db_display_percentage'] : 1;
                }
            }
        }

        // need sample account
        $this -> request::method("GET", function() {

            // function call
            $remarkArray = unset_remark_options($this -> assesmentData);

            return return2View($this, $this -> me -> viewDir . 'dump-sampling-form', [ 
                'request' => $this -> request,
                'data' => $this -> data,
                'menu_data' => $this -> menuData,
                'db_assesment' => $this -> assesmentData,
                'remarkTypeArray' => $remarkArray,
            ]);

        });

        //post method after form submit
        $this -> request::method("POST", function() {

            $validateBool = true;

            if( !is_array($this -> data['db_dump_data']) || 
                !sizeof($this -> data['db_dump_data']) > 0 || 
                empty($this -> request -> input('sampling')) )
                $validateBool = false;

            if($validateBool && is_array($this -> data['db_dump_data']) && is_array( $this -> request -> input('sampling') ) && sizeof(array_diff( $this -> request -> input('sampling'), array_keys($this -> data['db_dump_data']) )) > 0 )
                $validateBool = false;

            // for error
            if( !$validateBool )
            {
                Except::exc_404( Notifications::getNoti('somethingWrong') );
                exit;
            }

            $model = $this -> model( ($this -> data['db_category'] -> linked_table_id == 2) ? 'DumpAdvancesModel' : 'DumpDepositeModel' );

            $dataArray = array( 'sampling_filter' => 1 );

            $result = $model::update(
                $model -> getTableName(), 
                $dataArray, [
                    'where' => 'id IN ('. implode(',', $this -> request -> input('sampling') ) .')',
                    'params' => [ ]
                ]
            );

            if(!$result)
                return Except::exc_404( Notifications::getNoti('somethingWrong') );

            // update & data redirect
            Validation::flashErrorMsg('dumpSamplingSuccess', 'success');
            Redirect::to( SiteUrls::getUrl('auditCategory') . encrypt_ex_data($this -> catId) );

        });
    }

    public function removeDumpSampling($categoryDetails)
    {
        $dataFindNoti = null;

        $dumpModel = $this -> model('DumpAdvancesModel');

        $whereData = [
            'where' => 'id = :id AND branch_id = :branch_id',
            'params' => [
                'id' => $this -> accId,
                'branch_id' => $this -> assesmentData -> audit_unit_id,
                'audit_start_date' => $this -> assesmentData -> assesment_period_from,
                'audit_end_date' => $this -> assesmentData -> assesment_period_to  
            ]
        ];

        // strict check
        if($categoryDetails -> is_cc_acc_category == true && $categoryDetails -> linked_table_id == 2)
            $whereData['where'] .= ' AND ((account_opening_date BETWEEN :audit_start_date AND :audit_end_date) OR (renewal_date BETWEEN :audit_start_date AND :audit_end_date))';
        else            
            $whereData['where'] .= ' AND account_opening_date BETWEEN :audit_start_date AND :audit_end_date';

        $whereData['where'] .= ' AND assesment_period_id = 0 AND sampling_filter = 1 AND deleted_at IS NULL';

        if($categoryDetails -> linked_table_id == 1)
            $dumpModel = $this -> model('DumpDepositeModel');

        $accDetails = $dumpModel -> getSingleAccount($whereData);

        if(!is_object($accDetails) || empty($this -> data['db_category'] -> question_set_ids) )
            $dataFindNoti = 'errorFinding';
        
        if(empty($dataFindNoti))
        {
            // data found // check ans given or not
            $ansData = $this -> ansModel -> getSingleAnswer([
                'where' => 'assesment_id = :assesment_id AND category_id = :category_id AND dump_id = :dump_id AND deleted_at IS NULL',
                'params' => [ 
                    'assesment_id' => $this -> assesmentData -> id,
                    'category_id' => $this -> data['db_category'] -> id,
                    'dump_id' => $this -> accId
                ] 
            ]);

            if(is_object($ansData)) // has data
                $dataFindNoti = 'dumpHasAnsError';
        }

        if(!empty($dataFindNoti))
            return Except::exc_404( Notifications::getNoti($dataFindNoti) );

        if( empty($dataFindNoti) && is_object($accDetails) )
        {
            // remove sampling
            $result = $dumpModel::update(
                $dumpModel -> getTableName(), 
                [ 'sampling_filter' => 0 ],
                [
                    'where' => 'id = :id',
                    'params' => [ 'id' => $accDetails -> id ]
                ]
            );

            if(!$result)
                return Except::exc_404( Notifications::getNoti('somethingWrong') );
           
            //after insert data redirect to target Master dashboard
            Validation::flashErrorMsg('dumpSamplingRemoveSuccess', 'success');
            Redirect::to( SiteUrls::getUrl('auditCategory') . encrypt_ex_data($this -> catId) );
        }
    }

    public function removeAllAnnexAns($answerId, $needRes = false)
    {
        if(!isset($this -> assesmentData) || (isset($this -> assesmentData) && !is_object($this -> assesmentData)))
            $this -> getAssesmentData(true); //method call
        
        $res = [ 'msg' => Notifications::getNoti( 'errorFinding' ) ];

        if( is_object($this -> assesmentData) && $answerId != '' )
        {
            // assesment data found
            $model = $this -> model('AnswerDataAnnexureModel');

            $findAnnexAns = $model -> getAllAnswerAnnexures([
                'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                'params' => [ 'answer_id' => $answerId, 'assesment_id' => $this -> assesmentData -> id ]
            ]);

            if( is_array($findAnnexAns) && sizeof($findAnnexAns) > 0 )
            {
                $findAnnexAns = generate_data_assoc_array($findAnnexAns, 'id');
                
                // annex answer found
                $result = $model::delete( $model -> getTableName(), [
                    'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => [ 'answer_id' => $answerId, 'assesment_id' => $this -> assesmentData -> id ]
                ] );
        
                if($result)
                {
                    $res['msg'] = Notifications::getNoti( 'annexAnsDeleteSuccess' );
                    $res['success'] = true;
                }
            }
            else // 26.04.2024 update
                $res['success'] = true;
        }

        if(!$needRes)
            return $res;

        echo json_encode($res);
        exit;
    }

    public function removeAnnex()
    {
        $this -> getAssesmentData(true); //method call

        // method call
        $this -> removeAnnexMethod();
    }

    public function downloadSampleAnnex($annexId = null, $needArray = false)
    {
        if(isset($_POST['annexid']))
            $annexId = !empty($_POST['annexid']) ? decrypt_ex_data($_POST['annexid']) : '';

        $res = [ 'err' => true, 'msg' => Notifications::getNoti('somethingWrong'), 
                 'data' => [], 'risk_data' => null, 'annex_array' => null ];

        // $annexId = 35; // 10

        if(!empty($annexId))
        {
            // get all risk types
            $model = $this -> model('RiskCategoryModel');

            // skip not appliable
            $riskCatData = $model -> getAllRiskCategory([ 'where' => 'is_active = 1 AND id != 10 AND deleted_at IS NULL', 'params' => [] ]);
            $riskCatData = generate_data_assoc_array($riskCatData, 'id');
            $res['risk_data'] = $riskCatData;

            $model = $this -> model('AnnexureMasterModel');

            // find annexure // JOIN QUERY DATA
            $select = "SELECT am.name am_name, am.risk_defination_id, ac.* FROM annexure_master am INNER JOIN annexure_columns ac ON am.id = ac.annexure_id";

            $annexData = get_all_data_query_builder(2, $model, $model -> getTableName(), [
                'where' => 'am.id = :am_id AND am.deleted_at IS NULL AND ac.deleted_at IS NULL',
                'params' => [ 'am_id' => $annexId ]
            ], 'sql', $select); 

            $annexArray = [ 'headers' => [], 'data' => [] ];
            $annexArray['annex_cols'] = $annexData;
            $totalRows = 0;            

            if(is_array($annexData) && sizeof($annexData) > 0 && is_array($riskCatData) && sizeof($riskCatData) > 0)
            {
                foreach($annexData as $cIndex => $cAnnexCol)
                {
                    if(!isset($annexArray['risk_defination_id']))
                        $annexArray['risk_defination_id'] = $cAnnexCol -> risk_defination_id;

                    $annexArray['headers'][ $cIndex ] = string_operations($cAnnexCol -> name, 'upper');
                    $annexArray['data'][ $cIndex ] = [];

                    if($cAnnexCol -> column_type_id == 3)
                    {
                        try
                        {
                            $selectOptions = json_decode( $cAnnexCol -> column_options );

                            if(is_array($selectOptions) && sizeof($selectOptions) > 0)
                            {
                                foreach($selectOptions as $cOptionDetails)
                                {
                                    $annexArray['data'][ $cIndex ][] = trim_str($cOptionDetails -> column_option);
                                }
                            }
                        } 
                        catch (Exception $th) { }
                    }

                    if( sizeof($annexArray['data'][ $cIndex ]) > $totalRows )
                        $totalRows = sizeof($annexArray['data'][ $cIndex ]);
                }

                $annexArray['headers'][ ] = string_operations('Business Risk', 'upper');
                $annexArray['headers'][ ] = string_operations('Control Risk', 'upper');
                $annexArray['headers'][ ] = string_operations('Risk Type', 'upper');
                $cIndex++;

                if($annexArray['risk_defination_id'] == 1)
                {
                    // all risks
                    foreach(RISK_PARAMETERS_ARRAY as $cRiskId => $cRiskDetails)
                    {
                        $annexArray['data'][ $cIndex ][] = string_operations($cRiskDetails['title'], 'upper');
                        $annexArray['data'][ $cIndex + 1 ][] = string_operations($cRiskDetails['title'], 'upper');
                    }

                    $cIndex = $cIndex + 2;
                    
                    foreach($riskCatData as $cRiskCat => $cRiskCatDetails)
                        $annexArray['data'][ $cIndex ][] = string_operations($cRiskCatDetails -> risk_category, 'upper');

                    if( sizeof($riskCatData) > $totalRows )
                        $totalRows = sizeof($riskCatData);
                }
                else
                {
                    // single risks
                    if( !($totalRows > 0) ) $totalRows = 1;                    

                    $cIndex++;
                    $annexArray['data'][ $cIndex ][] = string_operations( RISK_PARAMETERS_ARRAY[1]['title'], 'upper' );

                    $cIndex++;
                    $annexArray['data'][ $cIndex ][] = string_operations( RISK_PARAMETERS_ARRAY[1]['title'], 'upper' );

                    $cIndex++;
                    $annexArray['data'][ $cIndex ][] = string_operations((isset($riskCatData[1]) ? $riskCatData[1] -> risk_category : ERROR_VARS['notFound']), 'upper');
                }
            }

            $res['annex_array'] = $annexArray;
            $csvData = [];

            if(sizeof($annexArray['headers']) > 0)
            {
                $csvData[0] = array_values($annexArray['headers']);

                for($i = 0; $i < $totalRows; $i++)
                {
                    $cDataArr = [];
                    
                    foreach($annexArray['data'] as $cIndex => $cData)
                    {
                        if(is_array($cData) && isset($cData[ $i ]))
                            $cDataArr[] = $cData[ $i ];
                        else
                            $cDataArr[] = '';
                    }

                    $csvData[ ] = $cDataArr;
                }

                $res['err'] = false;
            }

            // assign data
            $res['data'] = $csvData;
        }

        if($needArray) return $res;

        // unset vars
        unset($res['risk_data'], $res['annex_array']);

        if(!$res['err'])
            generate_CSV($res['data'], 'sample-annexure.csv');
    }

    public function uploadQuestionAnnexCSV()
    {
        $res = [ 'err' => true, 'msg' => Notifications::getNoti('somethingWrong'), 'markup' => '' ];

        // check validation
        $assesmentData = $this -> getAssesmentData(1); //method call

        if(is_object($assesmentData))
        {
            // assesment data found // file upload
            if ($_SERVER['REQUEST_METHOD'] === 'POST')
            {
                $annexCSVQuesId = isset($_POST['annex_csv_quesid']) && !empty($_POST['annex_csv_quesid']) ? decrypt_ex_data($_POST['annex_csv_quesid']) : '';
                $annexCSVCatId = isset($_POST['annex_csv_catid']) && !empty($_POST['annex_csv_catid']) ? decrypt_ex_data($_POST['annex_csv_catid']) : '';
                $annexCSVDumpId = isset($_POST['annex_csv_dumpid']) && !empty($_POST['annex_csv_dumpid']) ? decrypt_ex_data($_POST['annex_csv_dumpid']) : '';

                // $annexCSVQuesId = 3048; //2187;
                // $annexCSVCatId = 44;
                // $annexCSVDumpId = 48838;

                if( isset($_FILES['annex_csv_file']) && 
                    $_FILES['annex_csv_file']['error'] === UPLOAD_ERR_OK && 
                    !empty($annexCSVQuesId) && 
                    !empty($annexCSVCatId))
                // if(1)
                {
                    // validation for file
                    $file = $_FILES['annex_csv_file'];
                    $fileTmpPath = $_FILES['annex_csv_file']['tmp_name'];

                    $errCnt = 0;
                    
                    // file type check
                    if( !in_array($file['type'], array_values(FILE_UPLOADS_TYPES['csv'])) )
                    {
                        $res['msg'] = 'Only '. implode(', ', array_values(FILE_UPLOADS_TYPES['csv'])) .' files are allowed.';
                        $errCnt++;
                    }
                    
                    // file size check
                    if( $file['size'] > (FILE_UPLOADS_TYPES['csv_size'] * 1024 * 1024) )
                    {
                        $res['msg'] = 'File size exceeds the maximum limit of '. FILE_UPLOADS_TYPES['csv_size'] .'MB.';
                        $errCnt++;
                    }

                    $assesmentData -> ex_ques_ids = null;
                    $assesmentData -> ex_cat_ids = null;
                    $questionData = null; $categoryData = null;
                    $dumpData = null; $accData = null;
                    
                    // ABOVE CHECKED - VALIDATION
                    if(!($errCnt > 0))
                    {
                        if(!empty($assesmentData -> question_ids))
                            $assesmentData -> ex_ques_ids = explode(',', $assesmentData -> question_ids);

                        if(!empty($assesmentData -> cat_ids))
                            $assesmentData -> ex_cat_ids = explode(',', $assesmentData -> cat_ids);
                    
                        // check question exists in assesment object
                        if(!is_array($assesmentData -> ex_ques_ids) || 
                            (is_array($assesmentData -> ex_ques_ids) && !in_array($annexCSVQuesId, $assesmentData -> ex_ques_ids)) )
                        {
                            $res['msg'] = Notifications::getNoti('noQuestionFoundError');
                            $errCnt++;
                        }

                        // check category exists in assesment object
                        if(!is_array($assesmentData -> ex_cat_ids) || 
                            (is_array($assesmentData -> ex_cat_ids) && !in_array($annexCSVCatId, $assesmentData -> ex_cat_ids)) )
                        {
                            $res['msg'] = Notifications::getNoti('categoryNoDataError');
                            $errCnt++;
                        }

                        if(is_array($assesmentData -> ex_ques_ids) && in_array($annexCSVQuesId, $assesmentData -> ex_ques_ids))
                        {
                            // find in database
                            $model = $this -> model('QuestionMasterModel');

                            $questionData = $model -> getSingleQuestion([
                                'where' => 'id = :id AND is_active = 1 AND deleted_at IS NULL',
                                'params' => [ 'id' => $annexCSVQuesId ]
                            ]);

                            if(!is_object($questionData) || 
                               (is_object($questionData) && ($questionData -> option_id != 4 || empty($questionData -> annexure_id)) ))
                            {
                                $res['msg'] = Notifications::getNoti('noQuestionFoundError');
                                $errCnt++;
                            }
                        }

                        // find category
                        if(is_array($assesmentData -> ex_cat_ids) && in_array($annexCSVCatId, $assesmentData -> ex_cat_ids))
                        {
                            // find in database
                            $model = $this -> model('CategoryModel');
                            
                            $select = "SELECT mm.section_type_id, mm.name menu_name, cm.* FROM category_master cm INNER JOIN menu_master mm ON mm.id = cm.menu_id";

                            $categoryData = get_all_data_query_builder(1, $model, $model -> getTableName(), [
                                'where' => 'cm.id = :cm_id AND cm.is_active = 1 AND cm.deleted_at IS NULL AND mm.is_active = 1 AND mm.deleted_at IS NULL',
                                'params' => [ 'cm_id' => $annexCSVCatId ]
                            ], 'sql', $select); 

                            if(!is_object($categoryData))
                            {
                                $res['msg'] = Notifications::getNoti('categoryNoDataError');
                                $errCnt++;
                            }
                            else
                            {
                                // check category assigned to dump
                                if(array_key_exists($categoryData -> linked_table_id, $GLOBALS['schemeTypesArray']))
                                {
                                    if(empty($annexCSVDumpId))
                                    {
                                        $res['msg'] = Notifications::getNoti('samplingAccNotFound');
                                        $errCnt++;
                                    }
                                    else
                                    {
                                        // check account sampled or not
                                        if($categoryData -> linked_table_id == 1)
                                            $model = $this -> model('DumpDepositeModel');
                                        else
                                            $model = $this -> model('DumpAdvancesModel');

                                        $dumpData = $model -> getSingleAccount([
                                            'where' => 'id = :id AND branch_id = :branch_id AND sampling_filter = 1 AND deleted_at IS NULL',
                                            'params' => [ 'id' => $annexCSVDumpId, 'branch_id' => $assesmentData -> audit_unit_id ]
                                        ]);

                                        if(!is_object($dumpData))
                                        {
                                            $res['msg'] = Notifications::getNoti('samplingAccNotFound');
                                            $errCnt++;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // download sample annex records data
                    $dsaData = [];

                    // CHECK QUESTION DATA
                    if(is_object($questionData))
                    {
                        // question data found
                        $dsaData = $this -> downloadSampleAnnex($questionData -> annexure_id, 1);

                        if( !is_array($dsaData['data']) || 
                            !is_array($dsaData['annex_array']) || 
                            !( sizeof($dsaData['data']) > 0 ) || 
                            !isset($dsaData['annex_array']['headers']) )
                        {
                            $res['msg'] = Notifications::getNoti('somethingWrong');
                            $errCnt++;
                        }

                        // risk category data not found
                        if( !is_array($dsaData['risk_data']) || !(sizeof($dsaData['risk_data']) > 0) )
                        {
                            $res['msg'] = Notifications::getNoti('riskCategoryNoData');
                            $errCnt++;
                        }
                    }

                    $csvData = [ 'err_data' => [], 'insert_data' => [] ];
                    $annexHeaderCnt = sizeof($dsaData['annex_array']['headers']) - 3;
                    $headerColCount = sizeof($dsaData['annex_array']['headers']);
                    $errData = false; $firstRow = false;

                    // ABOVE CHECKED - QUESTION DATA
                    if(!($errCnt > 0))
                    {
                        // read csv data
                        if (($handle = fopen($fileTmpPath, 'r')) !== false) {

                            // Read and process the CSV file
                            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                                if($firstRow)
                                {
                                    // Trim the $data array to match the size of the $headerColCount array
                                    if (sizeof($data) > $headerColCount)
                                        $data = array_slice($data, 0, $headerColCount);

                                    // validate csv data with db
                                    foreach($dsaData['annex_array']['headers'] as $cIndex => $cAnnexHeaders) {

                                        if(!isset( $data[$cIndex] ))
                                        {
                                            $data[ $cIndex ] = '';
                                            $data[ $cIndex ] .= Notifications::getCustomAlertNoti('fillRequired', 'x');
                                            if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                        }
                                        else
                                        {
                                            if($cIndex >= $annexHeaderCnt)
                                            {
                                                // 3 type risk

                                                if($dsaData['annex_array']['risk_defination_id'] == 1)
                                                {
                                                    // multi select option
                                                    if(sizeof($dsaData['annex_array']['headers']) - 1 == $cIndex)
                                                    {
                                                        // risk category
                                                        $cRiskFound = false;

                                                        // RISK TYPE
                                                        foreach($dsaData['risk_data'] as $cRiskIndex => $cRiskData)
                                                        {
                                                            if(string_operations($data[$cIndex]) == string_operations($cRiskData -> risk_category))
                                                            {
                                                                $data[ 'rt' ] = $cRiskData -> id;
                                                                $cRiskFound = true;
                                                            }
                                                        }

                                                        if(!$cRiskFound)
                                                        {
                                                            $data[ $cIndex ] .= Notifications::getCustomAlertNoti('questionOptionNotFound', 'x');
                                                            if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        // common risk
                                                        $cRiskFound = false;

                                                        // BUSINESS RISK // CONTROL RISK
                                                        foreach(RISK_PARAMETERS_ARRAY as $cRiskIndex => $cRiskData)
                                                        {
                                                            if(string_operations($data[$cIndex]) == string_operations($cRiskData['title']))
                                                            {
                                                                // Business Risk
                                                                if(sizeof($dsaData['annex_array']['headers']) - 3 == $cIndex)
                                                                    $data[ 'br' ] = $cRiskData['id'];
                                                                else // Control Risk
                                                                    $data[ 'cr' ] = $cRiskData['id'];
                                                                    
                                                                $cRiskFound = true;
                                                            }
                                                        }

                                                        if(!$cRiskFound)
                                                        {
                                                            $data[ $cIndex ] .= Notifications::getCustomAlertNoti('questionOptionNotFound', 'x');
                                                            if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    // single
                                                    // multi select option
                                                    if(sizeof($dsaData['annex_array']['headers']) - 1 == $cIndex)
                                                    {
                                                        // RISK TYPE
                                                        if( isset($dsaData['risk_data'][1]) && 
                                                        string_operations($data[$cIndex]) == string_operations($dsaData['risk_data'][1] -> risk_category))
                                                        {
                                                            $data[ 'rt' ] = $dsaData['risk_data'][1] -> id;
                                                        }
                                                        else
                                                        {
                                                            $data[ $cIndex ] .= Notifications::getCustomAlertNoti('questionOptionNotFound', 'x');
                                                            if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                                        }
                                                    }
                                                    else
                                                    {
                                                        // COMMON RISK // BUSINESS RISK // CONTROL RISK
                                                        if(string_operations($data[$cIndex]) == string_operations(RISK_PARAMETERS_ARRAY[1]['title']))
                                                        {
                                                            // Business Risk
                                                            if(sizeof($dsaData['annex_array']['headers']) - 3 == $cIndex)
                                                                $data[ 'br' ] = RISK_PARAMETERS_ARRAY[1]['id'];
                                                            else // Control Risk
                                                                $data[ 'cr' ] = RISK_PARAMETERS_ARRAY[1]['id'];
                                                        }
                                                        else
                                                        {
                                                            $data[ $cIndex ] .= Notifications::getCustomAlertNoti('questionOptionNotFound', 'x');
                                                            if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                                        }
                                                    }
                                                }                                            
                                            }
                                            else
                                            {
                                                // actual column / headers data
                                                if(sizeof($dsaData['annex_array']['data'][ $cIndex ]) > 0)
                                                {
                                                    // multi options
                                                    if(!in_array(trim_str($data[ $cIndex ]), $dsaData['annex_array']['data'][ $cIndex ]))
                                                    {
                                                        $data[ $cIndex ] .= Notifications::getCustomAlertNoti('questionOptionNotFound', 'x');
                                                        if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                                    }
                                                }
                                                else
                                                {
                                                    // textbox / textarea
                                                    if(trim_str($data[ $cIndex ]) == '')
                                                    {
                                                        $data[ $cIndex] .= Notifications::getCustomAlertNoti('fillRequired', 'x');
                                                        if(!isset($data[ 'err' ])) $data[ 'err' ] = true;
                                                    }
                                                }
                                            }                                    
                                        }
                                    }

                                    // set true if single error found
                                    if(isset($data[ 'err' ]))
                                    {
                                        unset($data[ 'err' ], $data[ 'br' ], $data[ 'cr' ], $data[ 'rt' ]);
                                        $csvData['err_data'][] = $data;
                                    }
                                    else
                                    {
                                        // logic for remove default risk values
                                        unset($data[ 'err' ], $data[ $annexHeaderCnt ], $data[ $annexHeaderCnt + 1 ], $data[ $annexHeaderCnt + 2 ]);
                                        $csvData['insert_data'][] = $data;
                                    }
                                }
                                else
                                    $firstRow = true;
                            }

                            fclose($handle);

                        } else { 
                            // error open file
                            $res['msg'] = Notifications::getNoti('invalidRequestError');
                            $errCnt++; 
                        }
                    }

                    // ABOVE CHECKED - FILE OPEN AND CHECK VALIDATION AND SORT
                    if(!($errCnt > 0))
                    {
                        if(empty($csvData['insert_data']))
                        {
                            $res['msg'] = Notifications::getNoti('invalidForm');
                            $errCnt++;
                        }

                        if(sizeof($csvData['err_data']) > 0)
                        {
                            $res['msg'] = Notifications::getNoti('invalidForm');
                            $errCnt++;

                            $markup = '<div class="table-responsive">
                                        <table class="table table-bordered">' . "\n";                            
                            $markupBody = '';
                            $srNo = 1;

                                $markup .= '<tr>' . "\n";
                                    $markup .= '<th>SR. NO.</th>' . "\n";

                                    foreach($dsaData['annex_array']['headers'] as $cHeaderTitle) {
                                        $markup .= '<th>'. string_operations($cHeaderTitle, 'upper') .'</th>' . "\n";
                                    }
                                $markup .= '</tr>' . "\n";

                                // error data
                                foreach($csvData['err_data'] as $cHeadIndex => $cErrAnnexData) {

                                    $markupBody .= '<tr>' . "\n";

                                        $markupBody .= '<td>' . $srNo . '</td>' . "\n";
                                        // $markupBody .= '<td>' . $cErrAnnex[$cIndex] . '</td>' . "\n";
                                        $srNo++;

                                        foreach($cErrAnnexData as $cIndex => $cErrAnnex) { 

                                            if (strpos($cIndex, '_err') !== true && !in_array($cIndex, ['br_org','cr_org','rt_org'])):

                                                if(in_array($cIndex, ['br','cr','rt']))
                                                {
                                                    // risk data 
                                                    $markupBody .= '<td>' . trim_str($cErrAnnexData[ $cIndex . '_org']) . '</td>' . "\n";
                                                }
                                                else
                                                {
                                                    $markupBody .= '<td>' . trim_str($cErrAnnex) . '</td>' ."\n";                                                    
                                                }

                                            endif;
                                        }
                                    
                                    $markupBody .= '</tr>' . "\n";
                                }

                            $markup .= $markupBody;
                            $markup .= '</table"></div>' . "\n";

                            $res['markup'] = $markup;
                            unset($markup, $markupBody);
                        }
                    }

                    // ABOVE CHECKED - IF ANNEX ROW HAS ERROR TABLE MARKUP GENERATED
                    if(!($errCnt > 0))
                    {
                        // check ans exits or not 
                        $ansWhereArr = [ 'where' => '', 'params' => [] ];

                        $ansWhereArr['where'] = "assesment_id = :assesment_id AND menu_id = :menu_id AND category_id = :category_id AND header_id = :header_id AND question_id = :question_id";

                        $ansWhereArr['params'] = [
                            'assesment_id' => $assesmentData -> id,
                            'menu_id' => $categoryData -> menu_id,
                            'category_id' => $categoryData -> id,
                            'header_id' => $questionData -> header_id,
                            'question_id' => $questionData -> id,
                        ];

                        if(is_object($dumpData))
                        {
                            $ansWhereArr['where'] .= " AND dump_id = :dump_id";
                            $ansWhereArr['params']['dump_id'] = $dumpData -> id;
                        }

                        $ansWhereArr['where'] .= " AND deleted_at IS NULL";

                        $ansData = $this -> ansModel -> getSingleAnswer($ansWhereArr);

                        if(!is_object($ansData))
                        {
                            // insert ans // insert ans into array
                            $insertNewAnsArray = array(
                                "section_type_id" => $categoryData -> section_type_id	,
                                "assesment_id" => $assesmentData -> id,
                                "menu_id" => $categoryData -> menu_id,
                                "category_id" => $categoryData -> id,
                                "header_id" => $questionData -> header_id,
                                "question_id" => $questionData -> id,
                                "dump_id" => is_object($dumpData) ? $dumpData -> id : 0,
                                "answer_given" => $questionData -> annexure_id,
                                "audit_comment" => NULL,
                                "audit_emp_id" => Session::get('emp_id'),
                                "audit_status_id" => 0,
                                "audit_reviewer_emp_id" => 0,
                                "audit_reviewer_comment" => NULL,
                                "is_compliance" => 1,
                                "audit_commpliance" => NULL,
                                "compliance_evidance_upload" => NULL,
                                "compliance_emp_id" => 0,
                                "compliance_status_id" => 0,
                                "compliance_reviewer_emp_id" => 0,
                                "compliance_reviewer_comment" => NULL,
                                "business_risk" => 0,
                                "control_risk" => 0,
                                "instances_count" => 0,
                                "batch_key" => $assesmentData -> batch_key,
                            );

                            // insert in database
                            $result = $this -> ansModel::insert(
                                $this -> ansModel -> getTableName(), $insertNewAnsArray
                            );

                            if(!$result)
                            {
                                $res['msg'] = Notifications::getNoti('errorSaving');
                                $errCnt++;
                            }
                            else
                            {
                                $insertNewAnsArray['id'] = $this -> ansModel::lastInsertId();
                                $ansData = (object) $insertNewAnsArray;
                                $ansData -> manually_insert = true;
                            }

                            unset($insertNewAnsArray);
                        }
                        else
                        {
                            // check annex option selected or not
                            if($ansData -> answer_given != $questionData -> annexure_id)
                            {
                                $result = $this -> ansModel::update(
                                    $this -> ansModel -> getTableName(), [ 'answer_given' => $questionData -> annexure_id ],
                                    [
                                        'where' => 'id = :id',
                                        'params' => [ 'id' => $ansData -> id ]
                                    ]
                                );

                                if(!$result)
                                {
                                    $res['msg'] = Notifications::getNoti('errorSaving');
                                    $errCnt++;
                                }
                                else
                                { 
                                    //update answer
                                    $ansData -> answer_given = $questionData -> annexure_id;
                                    $ansData -> manually_update = true;
                                }
                            }
                        }
                    }

                    if(!($errCnt > 0))
                    {
                        // insert multiple data
                        $csvData['insert'] = [];
                        
                        foreach($csvData['insert_data'] as $cInsertData)
                        {
                            $csvData['insert'][] = array(
                                "answer_id" => $ansData -> id,
                                "assesment_id" => $assesmentData -> id,
                                "answer_given" => json_encode($cInsertData, 256),
                                "audit_comment" => NULL,
                                "audit_emp_id" => Session::get('emp_id'),
                                "audit_status_id" => $assesmentData -> audit_status_id,
                                "audit_reviewer_emp_id" => 0,
                                "audit_reviewer_comment" => NULL,
                                "audit_commpliance" => NULL,
                                "compliance_evidance_upload" => NULL,
                                "compliance_emp_id" => 0,
                                "compliance_status_id" => 0,
                                "compliance_reviewer_emp_id" => 0,
                                "compliance_reviewer_comment" => NULL,
                                "audit_compulsary_ev_upload" => 0,
                                "audit_compulsary_ev_upload" => 0,
                                "compliance_compulsary_ev_upload" => 0,
                                "business_risk" => $cInsertData['br'] ?? 0,
                                "control_risk" => $cInsertData['cr'] ?? 0,
                                "risk_cat_id" => $cInsertData['rt'] ?? 0,
                                "batch_key" => $assesmentData -> batch_key,
                            );
                        }

                        $result = $this -> ansAnnexModel::insertMultiple(
                            $this -> ansAnnexModel -> getTableName(), 
                            $csvData['insert'],
                            true
                        );

                        if(is_array($result) && sizeof($result) > 0)
                        {
                            // generate markup and return 
                            require_once APP_VIEWS . '/audit/audit-common-code.php';

                            $markup = '';

                            $riskMatrixArray = [];

                            foreach(RISK_PARAMETERS_ARRAY as $rpaId => $rpaDetails)
                            {
                                $riskMatrixArray[ $rpaId ] = (object) [
                                    'id' => $rpaDetails['id'],
                                    'title' => $rpaDetails['title'],
                                    'risk_parameter' => $rpaDetails['id']
                                ];
                            }

                            foreach($result as $cIndex => $cAnnexId)
                            {
                                $csvData['insert'][ $cIndex ] = (object) $csvData['insert'][ $cIndex ];
                                $csvData['insert'][ $cIndex ] -> id = $cAnnexId;

                                // update question
                                $questionData -> annexure_id_details = $dsaData['annex_array']['annex_cols'][0];
                                $questionData -> annexure_id_details -> annex_cols = generate_data_assoc_array($dsaData['annex_array']['annex_cols'], 'id');

                                $markup .= generate_annex_ans_markup([ 'data' => [
                                    'db_assesment_data' => $assesmentData,
                                    'db_business_risk_matrix' => $riskMatrixArray,
                                    'db_control_risk_matrix' => $riskMatrixArray,
                                    'db_risk_category' => $dsaData['risk_data'],
                                ] ], $questionData, $csvData['insert'][ $cIndex ] );
                            }

                            $res['markup'] = $markup;
                            $res['err'] = false;
                        }
                        else
                        {
                            // again update ans data
                            $res['msg'] = Notifications::getNoti('errorSaving');
                            $errCnt++;

                            if(isset($ansData -> manually_insert))
                            {
                                // manually answer inserted so remove due to error
                                $result = $this -> ansModel::update(
                                    $this -> ansModel -> getTableName(), 
                                    [ 
                                        'deleted_at' => date($GLOBALS['dateSupportArray'][2]),
                                        'audit_comment' => 'Error: ANNEX CSV UPLOAD ERROR'
                                    ],
                                    [
                                        'where' => 'id = :id',
                                        'params' => [ 'id' => $ansData -> id ]
                                    ]
                                );
                            }
                            else
                            {
                                // mannually answer changed // OR check any annex exists in annex table
                                // if any answer not exits change no or lowset risk if has risk check compliance or add no compliance

                                $checkRecord = $this -> ansAnnexModel::getSingleAnswerAnnexure([
                                    'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                                    'params' => [
                                        'answer_id' => $ansData,
                                        'assesment_id' => $assesmentData -> id
                                    ]
                                ]);

                                if(!is_object($checkRecord))
                                {
                                    $result = $this -> ansModel::update(
                                        $this -> ansModel -> getTableName(), 
                                        [ 
                                            'answer_given' => ERROR_VARS['notApplicable'],
                                            'business_risk' => 4,
                                            'control_risk' => 4,
                                            'is_compliance' => 0
                                        ],
                                        [
                                            'where' => 'id = :id',
                                            'params' => [ 'id' => $ansData -> id ]
                                        ]
                                    );
                                }

                                unset($checkRecord);
                            }
                            
                        }

                    }
                }
                else
                    $res['msg'] = Notifications::getNoti('invalidRequestError');
            }
        }
        else
            $res['msg'] = Notifications::getNoti('assesmentNotFound');

        echo json_encode($res);
        exit;
    }

    private function removeAnnexMethod( $annexId = null )
    {
        $res = [ 'msg' => Notifications::getNoti( 'errorFinding' ) ];
        $model = $this -> model('AnswerDataAnnexureModel');

        if( is_object($this -> assesmentData) && 
            $this -> request -> has('annex_id') && 
            $this -> request -> input('annex_id') != '' )
        {
            // assesment data found
            $annex_id = decrypt_ex_data($this -> request -> input('annex_id'));
        }
        elseif(!empty($annexId))
            $annex_id = $annexId;

        // find annex ans
        $whereData = [
            'where' => 'id = :id AND assesment_id = :assesment_id AND deleted_at IS NULL',
            'params' => [ 'id' => $annex_id, 'assesment_id' => $this -> assesmentData -> id ]
        ];

        $annexAnsData = $model -> getSingleAnswerAnnexure($whereData);

        if( is_object($annexAnsData) && $annexAnsData -> answer_id != '' )
        {
            // check before remove 1 annex compulsary
            $findAnnexAns = $model -> getAllAnswerAnnexures([
                'where' => 'answer_id = :answer_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                'params' => [ 'answer_id' => $annexAnsData -> answer_id, 'assesment_id' => $this -> assesmentData -> id ]
            ]);

            if( empty($annexId) && (!is_array($findAnnexAns) || ( is_array($findAnnexAns) && !sizeof($findAnnexAns) > 0 || sizeof($findAnnexAns) == 1 )) )
                $res['msg'] = Notifications::getNoti( 'singleAnnexAnsNeeded' );
            else
            {
                // annex answer found
                $result = $model::delete( $model -> getTableName(), $whereData );
        
                if($result)
                {
                    $res['msg'] = Notifications::getNoti( 'annexAnsDeleteSuccess' );
                    $res['success'] = true;
                }
            }
        }

        if(!empty($annexId))
            return $res;

        echo json_encode($res);
        exit;
    }

    public function dumpSelectSampling($needView, $reAuditFindData)
    {
        $startAuditLink = SiteUrls::getUrl('auditCategory') . encrypt_ex_data($this -> catId);

        $whereData = [ 'where' => '', 'params' => [] ];

        // helper function call
        $resData = get_category_dump_data($this, $this -> data['db_category'], $this -> assesmentData, 1, $whereData, null, $reAuditFindData);

        $this -> data['db_dump_count'] = is_object($resData['count']) ? $resData['count'] -> total : 0;
        $this -> data['db_scheme_codes'] = $resData['scheme_data'];
        $this -> data['db_dump_data'] = $resData['dump_data'];

        // unset
        unset($resData);
        
        if($needView)
        {
            // function call
            $remarkArray = unset_remark_options($this -> assesmentData);

            return return2View($this, $this -> me -> viewDir . 'dump-details-form', [ 
                'request' => $this -> request,
                'data' => $this -> data,
                'menu_data' => $this -> menuData,
                'db_assesment' => $this -> assesmentData,
                'start_audit_link' => $startAuditLink,
                'remarkTypeArray' => $remarkArray,
            ]);
        }
    }

    private function getAnnexAnswers($ansData)
    {
        if(is_array($ansData) && sizeof($ansData) > 0)
        {
            $model = $this -> model('AnswerDataAnnexureModel');

            $whereData = [
                'where' => 'assesment_id = :assesment_id AND answer_id IN ('. implode(',', array_keys($ansData)) .') AND deleted_at IS NULL',
                'params' => [ 'assesment_id' => $this -> assesmentData -> id ]
            ];

            $annexAnsData = $model -> getAllAnswerAnnexures($whereData);
            $annexIds = [];

            if(is_array($annexAnsData) && sizeof($annexAnsData) > 0)
            {
                foreach($annexAnsData as $cAnnexDetails)
                {
                    // check answer id exists or not
                    if(array_key_exists($cAnnexDetails -> answer_id, $ansData))
                    {
                        if(!isset($ansData[ $cAnnexDetails -> answer_id ] -> annex_ans))
                            $ansData[ $cAnnexDetails -> answer_id ] -> annex_ans = [];

                        $ansData[ $cAnnexDetails -> answer_id ] -> annex_ans[ $cAnnexDetails -> id ] = $cAnnexDetails;

                        if(!in_array($cAnnexDetails -> id, $annexIds))
                            $annexIds[] = $cAnnexDetails -> id;
                    }
                }
            }

            // function call
            if( check_evidence_upload_strict() && sizeof($annexIds) > 0 )
                $ansData = $this -> getEvidenceData($ansData, $annexIds, 2);
        }

        return $ansData;
    }

    private function checkAnswerBeforeSave($cAnsArray)
    {
        // check compliance and audit comment
        if($cAnsArray['is_compliance'] == true || !empty(trim_str( $cAnsArray['audit_comment'] )) )
            return true;

        elseif( !empty(trim_str( $cAnsArray['answer_given'] )) /* && (
            ( $cAnsArray['business_risk'] > 0 && $cAnsArray['business_risk'] < 4 ) ||
            ( $cAnsArray['control_risk'] > 0 && $cAnsArray['control_risk'] < 4 )
        )*/ )
            return true;

        return false;
    }

    private function saveAnswers($res, $postData, $isSave = true, $annexAnsCheck = false) {
        
        $errCnt = 0;
        // $postData = $this -> request -> input('data');   
        $saveDataArray = [];
        $saveQuesDataArray = [];

        // check question and answers exists or not
        foreach($postData as $cQuesId => $cQuesDetails)
        {
            $postData[$cQuesId]['err'] = null;
            $CQuesData = null;

            // check id exists or not
            $CQuesData = find_question_in_question_data($cQuesId, $this -> questionsData, 1);

            if(!is_object($CQuesData))
                $postData[$cQuesId]['err'] = Notifications::getNoti('questionNotExists');

            else
            {
                // question found // check header
                if( $CQuesData -> header_id != $cQuesDetails['header_id'] )
                    $postData[$cQuesId]['err'] = Notifications::getNoti('headerNotExists');

                // check for selected ans
                if( empty($postData[$cQuesId]['err']) && 
                    !array_key_exists($CQuesData -> option_id, $GLOBALS['questionInputMethodArray']) )
                    $postData[$cQuesId]['err'] = Notifications::getNoti('questionOptionNotFound');

                // check for options
                if( empty($postData[ $cQuesId ]['err']) )
                {
                    $forOptionValidation = false;

                    try 
                    {
                        $optionsArray = null;

                        // for all
                        if( in_array($CQuesData -> option_id, [1,2,4,5]) && 
                            !empty($CQuesData -> parameters) )
                            $optionsArray = json_decode($CQuesData -> parameters);

                        // option not found
                        /* if(empty($optionsArray) && in_array($CQuesData -> option_id, [1, 2]) )
                            $forOptionValidation = false; */

                        // for all options
                        if(is_array($optionsArray) )
                        {                            
                            foreach($optionsArray as $cRiskKey => $cRiskVal)
                            {
                                if(trim_str($cQuesDetails['answer_given']) == $cRiskVal -> rt)
                                {
                                    $forOptionValidation = true;

                                    // push new keys
                                    $postData[$cQuesId]['br'] = $cRiskVal -> br;
                                    $postData[$cQuesId]['cr'] = $cRiskVal -> cr;

                                    // check risk and assign compliance
                                    if( /* !$postData[$cQuesId]['is_compliance'] && */ ($cRiskVal -> br < 4 || $cRiskVal -> cr < 4) )
                                        $postData[ $cQuesId ]['is_compliance'] = true;

                                    break;
                                }
                            }
                        }

                        if( $CQuesData -> option_id == 3 )
                        {
                            // not option for general question
                            $forOptionValidation = true;

                            // Not Applicable // Or General Questions
                            $postData[$cQuesId]['br'] = 4;
                            $postData[$cQuesId]['cr'] = 4;
                        }

                        // for annexure = 4
                        if( !empty(trim_str($cQuesDetails['answer_given'])) && 
                            $CQuesData -> option_id == 4 && 
                            trim_str($cQuesDetails['answer_given']) == $CQuesData -> annexure_id )
                        {
                            $postData[ $cQuesId ]['is_compliance'] = false;
                        }

                        if( !$forOptionValidation && 
                            $CQuesData -> option_id == 4 && 
                            !empty($CQuesData -> annexure_id) && 
                            $CQuesData -> annexure_id == trim_str($cQuesDetails['answer_given']) )
                        {
                            // default true
                            $forOptionValidation = true;

                            if($annexAnsCheck)
                            {
                                $cAnnexAnsBool = false;

                                // check annexure rows inserted or not
                                if( is_array($this -> data['db_ans']) && sizeof($this -> data['db_ans']) > 0 )
                                {
                                    $cQuesGenKey = $CQuesData -> id . '_' . $CQuesData -> header_id . '_' . (($this -> accId != '') ? $this -> accId : 0);

                                    $res['cQuesGenKey'] = $cQuesGenKey;

                                    foreach($this -> data['db_ans'] as $cAnsId => $cAnsDetails)
                                    {
                                        $cGenKey = $cAnsDetails -> question_id . '_' . $cAnsDetails -> header_id . '_' . $cAnsDetails -> dump_id;
                                        $res['cQuesGenKey'] = $cGenKey;
                                        
                                        if( $cQuesGenKey == $cGenKey && 
                                            isset( $cAnsDetails -> annex_ans) && 
                                            sizeof($cAnsDetails -> annex_ans) > 0 )
                                            $cAnnexAnsBool = true;
                                    }                                
                                }

                                if(!$cAnnexAnsBool)
                                {
                                    $postData[$cQuesId]['err'] = Notifications::getNoti('annexAnsNotFound');
                                    $forOptionValidation = false;
                                }
                                
                                unset($cAnnexAnsBool);
                            }
                        }

                        // for subset = 5
                        if( !$forOptionValidation && $CQuesData -> option_id == 5)
                        {
                            $subsetData = null;
                            
                            if( !empty($CQuesData -> subset_multi_id) )
                                $subsetData = explode(',', $CQuesData -> subset_multi_id);

                            if( is_array($subsetData) && in_array(trim_str($cQuesDetails['answer_given']), $subsetData) )
                                $forOptionValidation = true;
                        }

                        // header wise questions
                        if(!array_key_exists( $CQuesData -> header_id, $saveDataArray))
                            $saveDataArray[ $CQuesData -> header_id ] = [];

                        if(!array_key_exists( $CQuesData -> header_id, $saveQuesDataArray))
                            $saveQuesDataArray[ $CQuesData -> id ] = $CQuesData;

                        // insert ans into array
                        $insertNewAnsArray = array(
                            "section_type_id" => $this -> data['db_category'] -> db_menu -> section_type_id	,
                            "assesment_id" => $this -> assesmentData -> id,
                            "menu_id" => $this -> data['db_category'] -> db_menu -> id,
                            "category_id" => $this -> data['db_category'] -> id,
                            "header_id" => $CQuesData -> header_id,
                            "question_id" => $CQuesData -> id,
                            "dump_id" => !empty($this -> accId) ? $this -> accId : 0,
                            "answer_given" => trim_str($postData[ $cQuesId ]['answer_given']),
                            "audit_comment" => trim_str($postData[ $cQuesId ]['audit_comment']),
                            "audit_emp_id" => Session::get('emp_id'),
                            "audit_status_id" => 0,
                            "audit_reviewer_emp_id" => 0,
                            "audit_reviewer_comment" => NULL,
                            "is_compliance" => ($postData[ $cQuesId ]['is_compliance'] == 'true') ? 1 : 0,
                            "audit_commpliance" => NULL,
                            "compliance_evidance_upload" => NULL,
                            "compliance_emp_id" => 0,
                            "compliance_status_id" => 0,
                            "compliance_reviewer_emp_id" => 0,
                            "compliance_reviewer_comment" => NULL,
                            "business_risk" => $postData[ $cQuesId ]['br'] ?? 0,
                            "control_risk" => $postData[ $cQuesId ]['cr'] ?? 0,
                            "instances_count" => 0,
                            "batch_key" => $this -> assesmentData -> batch_key,
                        );

                        if( check_evidence_upload_strict() && isset($postData[ $cQuesId ]['cc_evi_upload']) )
                            $insertNewAnsArray["compliance_compulsary_ev_upload"] = ($postData[ $cQuesId ]['cc_evi_upload'] == 'true') ? 2 : 0;

                        // push answer
                        $saveDataArray[ $CQuesData -> header_id ][] = $insertNewAnsArray;

                        // unset vars
                        unset($postData[ $cQuesId ]['br'], $postData[ $cQuesId ]['cr']);
                    } 
                    catch (Exception $th) 
                    {  $forOptionValidation = false; }

                    // for false data
                    if(!$forOptionValidation)
                    {
                        if(empty($postData[$cQuesId]['err']))
                            $postData[$cQuesId]['err'] = Notifications::getNoti('questionOptionNotFound');
                    }
                }

            }

            if( !empty($postData[$cQuesId]['err']) )
                $errCnt++;
        }

        // assign to response
        $res['data'] = $postData;

        // return $res;

        if( !$errCnt > 0 )
        {
            // insert or update process
            // $res['msg'] = 'insert';
            $insertAns = []; $updateAns = [];

            if(is_array($saveDataArray) && sizeof($saveDataArray) > 0)
            {
                $dbAnsV2 = array();

                // has data //sort data
                foreach($saveDataArray as $cHeaderId => $cHeaderArray)
                {
                    if(is_array($cHeaderArray) && sizeof($cHeaderArray) > 0):

                    // single question save
                    $defaultQuestionForHeader = null;
                    $headerInsertArray = [];
                    $checkHeaderUpdateQuestion = false;
                
                    // loop on question
                    foreach($cHeaderArray as $cIndex => $cAnsArray)
                    {
                        // check for update
                        $updateQuestion = false;

                        // helper function call
                        $tempCheckAns = check_answer_exists_on_question_id($cAnsArray["question_id"], $this -> data['db_ans']);

                        if( is_object($tempCheckAns) )
                        {
                            // answer found // send update
                            $dbAnsV2[ $tempCheckAns -> id ] = $tempCheckAns;

                            // push ans id
                            $saveDataArray[ $cIndex ]['ans_id'] = $tempCheckAns -> id;

                            $updateQuestion = true;
                        }

                        // assign first answer // Kunal Comment 15-07-2024
                        /* if(!is_array($defaultQuestionForHeader))
                            $defaultQuestionForHeader = $cAnsArray;

                        // check for answer given
                        if( !empty($cAnsArray['answer_given']) && 
                            is_array($defaultQuestionForHeader) && 
                            empty($defaultQuestionForHeader['answer_given']) )
                            $defaultQuestionForHeader = $cAnsArray; */

                        // update question  // Kunal Comment 15-07-2024
                        /* if( $updateQuestion )
                            $checkHeaderUpdateQuestion = true; */

                        if( !$updateQuestion )
                        {
                            // insert check for headers // because if ans has no risks then dont store in db
                            // if( $this -> checkAnswerBeforeSave($cAnsArray) ) // comment 26.04.2024
                                $headerInsertArray[] = $cAnsArray; // push answer
                        }                        
                        else // for update
                            $updateAns[ $saveDataArray[ $cIndex ]['ans_id'] ] = $cAnsArray;
                    }

                    // if not update record found and no risk ans found then insert first ans in current header  // Kunal Comment 15-07-2024
                    /* if(!sizeof($headerInsertArray) > 0 && !$checkHeaderUpdateQuestion && sizeof($defaultQuestionForHeader) > 0)
                        $headerInsertArray[] = $defaultQuestionForHeader; */

                    // merge with parent array
                    if(sizeof($headerInsertArray) > 0)
                        $insertAns = array_merge($insertAns, $headerInsertArray);

                    endif;
                }
            }

            $res['msg'] = Notifications::getNoti('somethingWrong');

            if(!$isSave)
            {
                $res['insertAns'] = $insertAns;
                $res['updateAns'] = $updateAns;
            }
            else
            {
                if( !sizeof($insertAns) > 0 && !sizeof($updateAns) > 0 )
                    $res['msg'] = Notifications::getNoti('somethingWrong');
                else
                {
                    $err = false;

                    // $res['msg'] = $insertAns;

                    if( sizeof($insertAns) > 0 )
                    {
                        // insert data
                        $result = $this -> ansModel::insertMultiple(
                            $this -> ansModel -> getTableName(), 
                            $insertAns, 
                            true
                        );

                        if(is_array($result) && sizeof($result) > 0)
                        {
                            // add last answer ids for evidence upload
                            foreach($insertAns as $cInsIndex => $cInsData) {
                                if(isset($result[ $cInsIndex ]) && array_key_exists($cInsData['question_id'], $res['data']))
                                    $res['data'][ $cInsData['question_id'] ]['ansid'] = encrypt_ex_data($result[ $cInsIndex ]);
                            }
                        }
                        else // if insert has error if(!$result)
                            $err = true;
                    }

                    if( !$err && sizeof($updateAns) > 0 )
                    {
                        $whereArr = [];

                        foreach($updateAns as $cAnsId => $cAnsDetails)
                        {
                            // check for annexure // remove all annexure
                            if( !empty(trim_str($cAnsDetails['answer_given'])) && 
                                array_key_exists($cAnsDetails['question_id'], $saveQuesDataArray) &&
                                $saveQuesDataArray[ $cAnsDetails['question_id'] ] -> option_id == 4 && 
                                trim_str($cAnsDetails['answer_given']) != $saveQuesDataArray[ $cAnsDetails['question_id'] ] -> annexure_id )
                            {
                                // method call
                                $methodRes = $this -> removeAllAnnexAns($cAnsId);

                                if(!array_key_exists('success', $methodRes))
                                {
                                    $err = true;
                                    break;
                                }
                            }

                            // check for subset // remove other questions
                            if(!empty(trim_str($cAnsDetails['answer_given'])) && 
                                array_key_exists($cAnsDetails['question_id'], $saveQuesDataArray) && 
                                $saveQuesDataArray[ $cAnsDetails['question_id'] ] -> option_id == 5 && 
                                !empty($saveQuesDataArray[ $cAnsDetails['question_id'] ] -> subset_multi_id) )
                            {
                                // valid subset
                                $subsetMultiId = explode(',', $saveQuesDataArray[ $cAnsDetails['question_id'] ] -> subset_multi_id);

                                // check subset id exists in array remove all
                                if(is_array($subsetMultiId) && in_array(trim_str($cAnsDetails['answer_given']), $subsetMultiId))
                                    $subsetMultiId = array_diff($subsetMultiId, [ trim_str($cAnsDetails['answer_given']) ]);

                                $removeAnsQuesId = [];

                                foreach($saveQuesDataArray as $ccQuesId => $ccQuesData):
                                
                                    if( isset($ccQuesData -> subset_data) && 
                                        is_array($ccQuesData -> subset_data) && 
                                        sizeof($ccQuesData -> subset_data) > 0 )
                                    {
                                        // loop on subset
                                        foreach($ccQuesData -> subset_data as $ccSubSetId => $ccSubSetDetails ):

                                            if( in_array($ccSubSetId, $subsetMultiId) &&
                                                isset($ccSubSetDetails -> headers) && 
                                                is_array($ccSubSetDetails -> headers) && 
                                                sizeof($ccSubSetDetails -> headers) > 0)
                                            {
                                                foreach($ccSubSetDetails -> headers as $ccHeaderId => $ccHeaderDetails ):

                                                if( isset($ccHeaderDetails -> questions) && 
                                                    is_array($ccHeaderDetails -> questions) && 
                                                    sizeof($ccHeaderDetails -> questions) > 0)
                                                {
                                                    // loop on subset questions
                                                    foreach($ccHeaderDetails -> questions as $ccHeaderQuesId => $ccHeaderQuesDetails )
                                                    {
                                                        if(in_array($ccHeaderQuesDetails -> set_id, $subsetMultiId))
                                                            $removeAnsQuesId[] = $ccHeaderQuesDetails -> id;
                                                    }
                                                }

                                                endforeach;
                                            }

                                        endforeach;
                                    }

                                endforeach;

                                if(is_array($removeAnsQuesId) && sizeof($removeAnsQuesId) > 0)
                                {
                                    $findAnsSubsetData = $this -> ansModel -> getAllAnswers([
                                        'where' => 'assesment_id = :assesment_id AND dump_id = :dump_id AND question_id IN ('. implode(',', $removeAnsQuesId) .') AND deleted_at IS NULL',
                                        'params' => [
                                            'assesment_id' => $cAnsDetails['assesment_id'],
                                            'dump_id' => $cAnsDetails['dump_id']
                                        ]
                                    ]);

                                    $findAnsSubsetData = generate_data_assoc_array($findAnsSubsetData, 'id');

                                    if(is_array($findAnsSubsetData) && sizeof($findAnsSubsetData)  > 0)
                                    {
                                        // find annex answers
                                        $model = $this -> model('AnswerDataAnnexureModel');
                                        
                                        $findAnnexAnsData = $model -> getAllAnswerAnnexures([
                                            'where' => 'assesment_id = :assesment_id AND answer_id IN ('. implode(',', array_keys($findAnsSubsetData)) .') AND deleted_at IS NULL',
                                            'params' => [
                                                'assesment_id' => $cAnsDetails['assesment_id'],
                                            ]
                                        ]);

                                        if(is_array($findAnnexAnsData) && sizeof($findAnnexAnsData) > 0)
                                        {
                                            $findAnnexAnsData = generate_data_assoc_array($findAnnexAnsData, 'id');

                                            // remove annex ans
                                            $resultAnnexAns = $model::delete($model -> getTableName(), [
                                                'where' => 'assesment_id = :assesment_id AND answer_id IN ('. implode(',', array_keys($findAnsSubsetData)) .') AND deleted_at IS NULL',
                                                'params' => [ 'assesment_id' => $cAnsDetails['assesment_id'] ]
                                            ]);

                                            if(!$resultAnnexAns)
                                            {
                                                $err = true;
                                                break;
                                            }
                                        }

                                        // remove ans
                                        $resultAns = $this -> ansModel::delete($this -> ansModel -> getTableName(), [
                                            'where' => 'assesment_id = :assesment_id AND dump_id = :dump_id AND question_id IN ('. implode(',', $removeAnsQuesId) .') AND deleted_at IS NULL',
                                            'params' => [
                                                'assesment_id' => $cAnsDetails['assesment_id'],
                                                'dump_id' => $cAnsDetails['dump_id']
                                            ]
                                        ]);

                                        if(!$resultAns)
                                        {
                                            $err = true;
                                            break;
                                        }
                                    }
                                }

                                unset($subsetMultiId, $removeAnsQuesId);
                            }

                            // unset audit_status_id
                            unset($updateAns[ $cAnsId ]["audit_status_id"]);
                            
                            $whereArr[] = array(
                                'where' => 'id = :id',
                                'params' => [ 'id' => $cAnsId ]
                            );
                        }

                        if(!$err && check_re_assesment_status($this -> assesmentData))
                        {
                            // on re audit // find all answers
                            $findOldAns = $this -> ansModel -> getAllAnswers([
                                'where' => 'id IN ('. implode(',', array_keys($updateAns)) .') AND deleted_at IS NULL AND batch_key != :batch_key',
                                'params' => [ 'batch_key' => $this -> assesmentData -> batch_key ]
                            ]);

                            if(is_array($findOldAns) && sizeof($findOldAns) > 0)
                            {
                                // has data
                                $insertMultiTimeLineArray = [];

                                // shift to timeline // if batch key changed shift answer to the timeline
                                $ansTimelineModel = $this -> model('AnswerDataTimelineModel');

                                foreach($findOldAns as $cFindOldAns)
                                {
                                    $insertMultiTimeLineArray[] = array(
                                        "answer_id" => $cFindOldAns -> id,
                                        "annex_id" => 0,
                                        "assesment_id" => $cFindOldAns -> assesment_id,
                                        "last_updated_at" => $cFindOldAns -> updated_at,
                                        "answer_type" => 1, //for audit
                                        "answer_given" => $cFindOldAns -> answer_given,
                                        "audit_comment" => $cFindOldAns -> audit_comment,
                                        "audit_emp_id" => $cFindOldAns -> audit_emp_id,
                                        "audit_status_id" => $cFindOldAns -> audit_status_id,
                                        "audit_reviewer_emp_id" => $cFindOldAns -> audit_reviewer_emp_id,
                                        "audit_reviewer_comment" => $cFindOldAns -> audit_reviewer_comment,
                                        "audit_commpliance" => $cFindOldAns -> audit_commpliance,
                                        "compliance_evidance_upload" => $cFindOldAns -> compliance_evidance_upload,
                                        "compliance_emp_id" => $cFindOldAns -> compliance_emp_id,
                                        "compliance_status_id" => $cFindOldAns -> compliance_status_id,
                                        "compliance_reviewer_emp_id" => $cFindOldAns -> compliance_reviewer_emp_id,
                                        "compliance_reviewer_comment" => $cFindOldAns -> compliance_reviewer_comment,
                                        "business_risk" => $cFindOldAns -> business_risk,
                                        "control_risk" => $cFindOldAns -> control_risk,
                                        "risk_cat_id" => (isset($cFindOldAns -> risk_cat_id) ? $cFindOldAns -> risk_cat_id : 0),
                                        "instances_count" => (isset($cFindOldAns -> instances_count) ? $cFindOldAns -> instances_count : 0),
                                        "batch_key" => $cFindOldAns -> batch_key
                                    );
                                }

                                // insert timeline answers
                                $result = $ansTimelineModel::insertMultiple(
                                    $ansTimelineModel -> getTableName(), 
                                    $insertMultiTimeLineArray
                                );

                                if(!$result) $err = true;
                            }
                        }

                        if( !$err )
                        {
                            // update data
                            $result = $this -> ansModel::updateMultiple(
                                $this -> ansModel -> getTableName(), 
                                array_values($updateAns), $whereArr
                            );

                            // if insert occure
                            if(!$result) $err = true;
                        }
                    }
                }

                if(!$err)
                {
                    $res['msg'] = Notifications::getNoti('answerSaveSuccess');
                    $res['success'] = true;
                }       
            }
        }

        return $res;
    }

    private function saveAnnexAnswers($res)
    {
        $res['data_annex'] = $this -> request -> input('data_annex');
        $res = $this -> saveAnswers($res, $this -> request -> input('data_annex_ques'), false, false);                

        if(array_key_exists('data', $res) && sizeof($res['data']) > 0 )
            $res['data_db'] = $res['data'][ array_keys($res['data'])[0] ];

        if( isset($res['data_db']) && 
            (!array_key_exists('err', $res['data_db']) || 
            (array_key_exists('err', $res['data_db']) && $res['data_db']['err'] == null )) )
        {
            $cGenKey = $res['data_db']['id'] . '_' . $res['data_db']['header_id'] . '_';

            if( array_key_exists($this -> data['db_category'] -> linked_table_id, $GLOBALS['schemeTypesArray']) )
                $cGenKey .= $this -> accId;
            else
                $cGenKey .= 0;

            // check in insert
            $res['data_question'] = $this -> checkQuestionExists($cGenKey, $res['insertAns']);

            // check in update
            if(!is_array($res['data_question']))
                $res['data_question'] = $this -> checkQuestionExists($cGenKey, $res['updateAns'], 'update');
        }

        $errCnt = 0;
        $errKey = null;
        $insertNewAnnex = null;

        if( array_key_exists('data', $res) && sizeof($res['data']) > 0 && 
            array_key_exists('data_question', $res) && sizeof($res['data_question']) > 0)
        {
            $answerGiven = $res['data_annex'];
            unset($answerGiven['cc_evi_upload']);
            
            // insert single annex row
            $insertNewAnnex = array(
                "answer_id" => 0 /*$res['data_question']['id']*/,
                "assesment_id" => $this -> assesmentData -> id,
                "answer_given" => json_encode($answerGiven, 256),
                "audit_comment" => NULL,
                "audit_emp_id" => Session::get('emp_id'),
                "audit_status_id" => $this -> assesmentData -> audit_status_id,
                "audit_reviewer_emp_id" => 0,
                "audit_reviewer_comment" => NULL,
                "audit_commpliance" => NULL,
                "compliance_evidance_upload" => NULL,
                "compliance_emp_id" => 0,
                "compliance_status_id" => 0,
                "compliance_reviewer_emp_id" => 0,
                "compliance_reviewer_comment" => NULL,
                "business_risk" => $answerGiven['br'],
                "control_risk" => $answerGiven['cr'],
                "risk_cat_id" => $answerGiven['rt'],
                "batch_key" => $this -> assesmentData -> batch_key,
            );

            if(isset($answerGiven['annex_id']))
            {
                unset($answerGiven['annex_id']);
                
                // update single annex row
                $insertNewAnnex = array(
                    "answer_given" => json_encode($answerGiven, 256),
                    "audit_emp_id" => Session::get('emp_id'),
                    "business_risk" => $answerGiven['br'],
                    "control_risk" => $answerGiven['cr'],
                    "risk_cat_id" => $answerGiven['rt'],
                    "batch_key" => $this -> assesmentData -> batch_key,
                );
            }

            if( check_evidence_upload_strict() && isset($res['data_annex']['cc_evi_upload']) )
                $insertNewAnnex["compliance_compulsary_ev_upload"] = ($res['data_annex']['cc_evi_upload'] == 'true') ? 2 : 0;

            // VALIDATE ANNEX ANSWERS // FIND ANNEX & ANNEX COLUMNS
            
            // check questionsData
            if( !is_array($this -> questionsData) ) 
            {
                $errKey = 'questionNotExists';
                $errCnt++;
            }

            // check for question exists
            elseif( is_array($this -> questionsData) && 
                    !array_key_exists($res['data_question']['question_id'], $this -> questionsData) )
            {
                $errKey = 'questionNotExists';
                $errCnt++;
            }

            // check annexure_id_details exists
            elseif( is_array($this -> questionsData) && 
                    array_key_exists($res['data_question']['question_id'], $this -> questionsData) && 
                    ( !isset( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details ) || 
                        ( isset( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details ) && 
                            !is_object( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details ) ) 
                    )
                )
            {
                $errKey = 'annexureNotFound';
                $errCnt++;
            }

            // check for annex_cols exists
            elseif( is_array($this -> questionsData) && 
                    array_key_exists($res['data_question']['question_id'], $this -> questionsData) && 
                    isset( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details) && 
                    is_object( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details ) && 
                    ( !isset( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols ) ||
                        !is_array( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols ) || ( is_array( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols ) && !sizeof( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols ) > 0 ) 
                    )
                )
            {
                $errKey = 'annexureColumnsNotFound';
                $errCnt++;
            }

            // ALL CONDITION SATISFY
            if( empty($errKey) )
            {
                // check column wise count and options 
                $tempDataAnnex = $res['data_annex'];
                unset($tempDataAnnex['br'], $tempDataAnnex['cr'], $tempDataAnnex['rt'], $tempDataAnnex['annex_id'], $tempDataAnnex['cc_evi_upload']);

                // skip last 3 keys and values // check columns count
                if( $errKey == null && 
                    sizeof( $tempDataAnnex) != sizeof( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols ) )
                {
                    $errKey = 'annexureColumnsAnsMisMatched';
                    $errCnt++;
                }
                elseif( $errKey == null && 
                        sizeof( $tempDataAnnex) == sizeof( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols ) )
                {
                    $i = 0;

                    // check column wise data
                    foreach($this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> annex_cols as $cAnnexColId => $CAnnexColDetails)
                    {
                        // '1' => 'TextBox' // '2' => 'TextArea'
                        if( $errKey == null && ($CAnnexColDetails -> column_type_id == 1 || $CAnnexColDetails -> column_type_id == 2) && 
                            trim_str($tempDataAnnex[$i]) == '')
                        {
                            $errKey = 'fillRequired';
                            $errCnt++;
                        }
                        elseif( $errKey == null && $CAnnexColDetails -> column_type_id == 3 )
                        {
                            try
                            {
                                $selectOptions = json_decode( $CAnnexColDetails -> column_options );

                                if(is_array($selectOptions) && sizeof($selectOptions) > 0)
                                {
                                    $ansCheck = false;
                                    
                                    foreach($selectOptions as $cOptionDetails)
                                    {
                                        if(trim_str($cOptionDetails -> column_option) == trim_str($tempDataAnnex[$i]))
                                        {
                                            $ansCheck = true;
                                            break;
                                        }
                                    }

                                    // valid option not selected
                                    if(!$ansCheck)
                                    {
                                        $errKey = 'fillRequired';
                                        $errCnt++;
                                    }
                                }
                                else
                                {
                                    $errKey = 'optionMismatch';
                                    $errCnt++;
                                }
                            } 
                            catch (Exception $th) { 
                                $errKey = 'optionMismatch';
                                $errCnt++;
                            }
                        }

                        $i++;
                    }

                    // check risk
                    if( $errKey == null ):

                    if ( $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> risk_defination_id == 1)
                    {
                        // for multiple select options

                        $ansCheck = false;

                        // business risk
                        if( is_array($this -> data['db_business_risk_matrix']) && 
                            sizeof($this -> data['db_business_risk_matrix']) > 0 )
                        {
                            foreach($this -> data['db_business_risk_matrix'] as $cRiskMatrix => $cRiskMatrixDetails)
                            {
                                if(trim_str($insertNewAnnex['business_risk']) == $cRiskMatrixDetails -> risk_parameter)
                                {
                                    $res['data_annex']['business_risk'] = array_key_exists($cRiskMatrixDetails -> risk_parameter, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cRiskMatrixDetails -> risk_parameter ]['id'] : 0;
                                    $ansCheck = true;
                                    break;
                                }
                            }
                        }

                        if(!$ansCheck)
                        {
                            $errKey = 'businessRiskValidSelect';
                            $errCnt++;
                        }
                        
                        // control risk
                        $ansCheck = false;

                        if( is_array($this -> data['db_control_risk_matrix']) && 
                            sizeof($this -> data['db_control_risk_matrix']) > 0 )
                        {
                            foreach($this -> data['db_control_risk_matrix'] as $cRiskMatrix => $cRiskMatrixDetails)
                            {
                                if(trim_str($insertNewAnnex['control_risk']) == $cRiskMatrixDetails -> risk_parameter)
                                {
                                    $res['data_annex']['control_risk'] = array_key_exists($cRiskMatrixDetails -> risk_parameter, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[ $cRiskMatrixDetails -> risk_parameter ]['id'] : 0;
                                    $ansCheck = true;
                                    break;
                                }
                            }
                        }

                        if(!$ansCheck)
                        {
                            $errKey = 'controlRiskValidSelect';
                            $errCnt++;
                        }

                        // Risk Type
                        $ansCheck = false;

                        if( is_array($this -> data['db_risk_category']) && 
                            sizeof($this -> data['db_risk_category']) > 0 )
                        {
                            foreach($this -> data['db_risk_category'] as $cRiskCat => $cRiskCatDetails)
                            {
                                if(trim_str($insertNewAnnex['risk_cat_id']) == $cRiskCatDetails -> id)
                                {    
                                    $res['data_annex']['risk_cat_id'] = $cRiskCatDetails -> id;
                                    $ansCheck = true;
                                    break;
                                }
                            }
                        }

                        if(!$ansCheck)
                        {
                            $errKey = 'riskTypeValidSelect';
                            $errCnt++;
                        }
                    }
                    else
                    {
                        // single option select with high risk

                        // business risk
                        if(!array_key_exists('business_risk', $insertNewAnnex) || 
                            ( array_key_exists('business_risk', $insertNewAnnex) && trim_str($insertNewAnnex['business_risk']) != RISK_PARAMETERS_ARRAY[1]['id'] ))
                        {
                            $errKey = 'businessRiskValidSelect';
                            $errCnt++;
                        }
                        else
                            $res['data_annex']['business_risk'] = RISK_PARAMETERS_ARRAY[1]['id'];


                        // control risk
                        if(!array_key_exists('control_risk', $insertNewAnnex) || 
                            ( array_key_exists('control_risk', $insertNewAnnex) && trim_str($insertNewAnnex['control_risk']) != RISK_PARAMETERS_ARRAY[1]['id'] ))
                        {
                            $errKey = 'controlRiskValidSelect';
                            $errCnt++;
                        }
                        else
                            $res['data_annex']['control_risk'] = RISK_PARAMETERS_ARRAY[1]['id'];

                        $dbRiskCategory = (is_array($this -> data['db_risk_category']) && array_key_exists($this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> risk_category_id, $this -> data['db_risk_category'])) ? $this -> data['db_risk_category'][ $this -> questionsData[ $res['data_question']['question_id'] ] -> annexure_id_details -> risk_category_id ] : null;

                        // Risk Type
                        if( !is_object($dbRiskCategory) || 
                            ( is_object($dbRiskCategory) && $dbRiskCategory -> id != trim_str($insertNewAnnex['risk_cat_id']) ) )
                        {
                            $errKey = 'riskTypeValidSelect';
                            $errCnt++;
                        }
                        else
                            $res['data_annex']['risk_cat_id'] = ( is_object($dbRiskCategory) ) ? string_operations($dbRiskCategory -> risk_category, 'upper') : ERROR_VARS['notFound'];
                    }
                    
                    endif;

                }
                else
                    $errCnt++;
                                    
            }

            $findOldAnnex = NULL;
            $annex_id = null;

            // check for annex update
            if(isset($res['data_annex']['annex_id']) && !empty($res['data_annex']['annex_id']))
            {
                // assesment data found
                $annex_id = decrypt_ex_data($res['data_annex']['annex_id']);

                if(empty($annex_id))
                {
                    $errCnt++;
                    $errKey = 'annexAnsFailedSaveSuccess';
                }
                else
                {
                    // method call
                    $findOldAnnex = $this -> findSingleAnnex($annex_id);

                    if(!is_object($findOldAnnex))
                    {
                        $errCnt++;
                        $errKey = 'annexAnsFailedSaveSuccess';
                    }
                    else
                    {
                        // update check for recompliance
                        if($findOldAnnex -> batch_key != $this -> assesmentData -> batch_key)
                        {
                            // shift to timeline // if batch key changed shift answer to the timeline
                            $ansTimelineModel = $this -> model('AnswerDataTimelineModel');

                            $insertArray = array(
                                "answer_id" => $findOldAnnex -> answer_id,
                                "annex_id" => $findOldAnnex -> id,
                                "assesment_id" => $findOldAnnex -> assesment_id,
                                "last_updated_at" => $findOldAnnex -> updated_at,
                                "answer_type" => 1, //for audit
                                "answer_given" => $findOldAnnex -> answer_given,
                                "audit_comment" => $findOldAnnex -> audit_comment,
                                "audit_emp_id" => $findOldAnnex -> audit_emp_id,
                                "audit_status_id" => $findOldAnnex -> audit_status_id,
                                "audit_reviewer_emp_id" => $findOldAnnex -> audit_reviewer_emp_id,
                                "audit_reviewer_comment" => $findOldAnnex -> audit_reviewer_comment,
                                "audit_commpliance" => $findOldAnnex -> audit_commpliance,
                                "compliance_evidance_upload" => $findOldAnnex -> compliance_evidance_upload,
                                "compliance_emp_id" => $findOldAnnex -> compliance_emp_id,
                                "compliance_status_id" => $findOldAnnex -> compliance_status_id,
                                "compliance_reviewer_emp_id" => $findOldAnnex -> compliance_reviewer_emp_id,
                                "compliance_reviewer_comment" => $findOldAnnex -> compliance_reviewer_comment,
                                "business_risk" => $findOldAnnex -> business_risk,
                                "control_risk" => $findOldAnnex -> control_risk,
                                "risk_cat_id" => $findOldAnnex -> risk_cat_id,
                                "instances_count" => (isset($findOldAnnex -> instances_count) ? $findOldAnnex -> instances_count : 0),
                                "batch_key" => $findOldAnnex -> batch_key
                            );

                            // insert in database
                            $result = $ansTimelineModel::insert(
                                $ansTimelineModel -> getTableName(), $insertArray
                            );

                            if(!$result)
                            {
                                $errCnt++;
                                $errKey = 'errorSaving';
                            }
                        }

                        // update annex
                        if(!$errCnt > 0 && is_array($insertNewAnnex) && sizeof($insertNewAnnex) > 0)
                        {
                            $errKey = 'annexAnswerSaveSuccess';

                            // insert new annex // insert current answer // insert data
                            $result = $this -> ansAnnexModel::update(
                                $this -> ansAnnexModel -> getTableName(), 
                                $insertNewAnnex,
                                [
                                    'where' => 'id = :id',
                                    'params' => [ 'id' => $findOldAnnex -> id ]
                                ]
                            );

                            // if not update
                            if(!$result)
                            {
                                $errCnt++;
                                $errKey = 'errorSaving';
                            }

                            $res['data_annex']['id'] = $annex_id;
                        }
                    }
                }
            }

            elseif(!is_object($findOldAnnex) && !$errCnt > 0 && is_array($insertNewAnnex) && sizeof($insertNewAnnex) > 0)
            {
                // INSERT ANS // insert new annex row but check data_question has id key
                if(!array_key_exists('id', $res['data_question']))
                {
                    // insert current answer // insert data
                    $result = $this -> ansModel::insert(
                        $this -> ansModel -> getTableName(), 
                        $res['data_question']
                    );

                    // get last inserted id
                    if($result)
                        $res['data_question']['id'] = $this -> ansModel::lastInsertId();
                }

                if(!array_key_exists('id', $res['data_question']))
                    $errCnt++;

                if(!$errCnt > 0)
                {
                    $errKey = 'annexAnswerSaveSuccess';
                    $insertNewAnnex['answer_id'] = $res['data_question']['id'];

                    // insert new annex // insert current answer // insert data                

                    $result = $this -> ansAnnexModel::insert(
                        $this -> ansAnnexModel -> getTableName(), 
                        $insertNewAnnex
                    );

                    // data not inserted
                    if(!$result)
                        $errCnt++;
                    else // get last inserted id
                    {
                        $res['data_annex']['id'] = $this -> ansAnnexModel::lastInsertId();
                        $annex_id = $res['data_annex']['id'];

                        // change annex ans it other option selected change to as per annex
                        $findAnnexAns = $this -> ansModel -> getSingleAnswer([
                            'where' => 'id = :id AND deleted_at IS NULL',
                            'params' => [ 'id' => $insertNewAnnex['answer_id'] ]
                        ]);

                        if(!is_object($findAnnexAns))
                        {
                            // not ans remove annex // method call
                            $this -> removeAnnexMethod( $res['data_annex']['id'] );
                            $errCnt++;
                        }
                        else
                        {
                            // check ans
                            if( $findAnnexAns -> answer_given != $res['data_db']['answer_given'] )
                            {
                                // udpate answer // insert current answer // insert data
                                $result = $this -> ansModel::update(
                                    $this -> ansModel -> getTableName(), 
                                    [ 'answer_given' => $res['data_db']['answer_given'] ], [
                                        'where' => 'id = :id',
                                        'params' => [ 'id' => $insertNewAnnex['answer_id'] ]
                                    ]
                                );

                                // if not update
                                if(!$result)
                                    $errCnt++;
                            }
                        }
                    }

                }
            }
            else
                $errCnt++;

            // find annex details for return markup
            if(!$errCnt > 0 && !empty($annex_id))
            {
                // method call
                $res['data_annex_obj'] = $this -> findSingleAnnex($annex_id);

                if(!is_object($res['data_annex_obj']))
                    $errCnt++;
            }
            else
                $errCnt++;
                

            if($errCnt > 0)
                $res = [ 'msg' => Notifications::getNoti( ($errKey != null) ? $errKey : 'somethingWrong' ) ];
            else
                $res[ 'msg' ] = Notifications::getNoti( ($errKey != null) ? $errKey : 'somethingWrong' );

            // $res[ 'msg' ] = $rssaaas;

            unset($res['insertAns'], $res['updateAns']);

        }

        return $res;
    }

    private function findSingleAnnex($annex_id)
    {
        if(empty($annex_id))
            return null;

        // annex update // find annex record
        return $this -> ansAnnexModel -> getSingleAnswerAnnexure([
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => [ 'id' => $annex_id ]
        ]);
    }

    public function endAssesment()
    {
        $this -> getAssesmentData(); // method call

        // change me
        $this -> me = SiteUrls::get('auditEndAssessment');

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('audit') ],
        ];

        // only end assesment // re assesment method is diffrent
        if($this -> assesmentData -> audit_status_id != 1) {

            Except::exc_404( Notifications::getNoti('endAssesmentAlreadyDoneError') );
            exit;
        }

        //top data container
        $this -> data['data_container'] = true;

        //need audit assesment js
        $this -> data['js'][] = 'audit-end-assesment.js';

        // find set data
        $data = get_all_question_data( $this, $this -> assesmentData );

        $this -> data['db_assesment_data'] = $this -> assesmentData;
        $this -> data['db_menu'] = $data['db_menu'];
        $this -> data['db_category'] = $data['db_category'];
        $this -> data['db_sets'] = $data['db_sets'];

        $this -> data['exe_summary_data'] = array(
            'rejected' => 0,
            'pending_reaudit' => 0
        );

        // method call // for advances
        $this -> endAssesmentDumpData(null, 2);

        // method call // for deposits
        $this -> endAssesmentDumpData(null, 1);

        if(!empty($this -> assesmentData -> question_ids))
        {
            // for question ids multiple // skip dump account answers data due to heavy load 20.07.2024 Kunal
            $whereArr = [
                'where' => 'assesment_id = :assesment_id AND deleted_at IS NULL',
                'params' => [ 'assesment_id' => $this -> assesmentData -> id ]
            ];

            if(check_carry_forward_strict())
                $whereArr['where'] .= ' AND (
                    ( question_id IN ('. $this -> assesmentData -> question_ids .') AND dump_id = 0 ) 
                    OR answer_given = "'. CARRY_FORWARD_ARRAY['id'] .'" )';
            else
                $whereArr['where'] .= ' AND question_id IN ('. $this -> assesmentData -> question_ids .') AND dump_id = 0';

            // find assesment answers // get all answers        
            $this -> data['db_ans'] = $this -> ansModel -> getAllAnswers($whereArr);
            $this -> data['db_ans'] = generate_data_assoc_array($this -> data['db_ans'], 'id');

            if(is_array($this -> data['db_ans']) && sizeof($this -> data['db_ans']) > 0)
            {
                // find assesment annex answers // get all annex answers        
                $annexModel = $this -> model('AnswerDataAnnexureModel');      

                $whereArr = [
                    'where' => 'assesment_id = :assesment_id AND answer_id IN ('. implode(',', array_keys($this -> data['db_ans'])) .') AND deleted_at IS NULL',
                    'params' => [ 'assesment_id' => $this -> assesmentData -> id ]
                ];

                $dbAnnexAns = $annexModel -> getAllAnswerAnnexures($whereArr);

                if(is_array($dbAnnexAns) && sizeof($dbAnnexAns) > 0)
                {
                    foreach($dbAnnexAns as $cAnnexDetails)
                    {
                        if(array_key_exists($cAnnexDetails -> answer_id, $this -> data['db_ans']))
                        {
                            // if not key exists
                            if(!isset($this -> data['db_ans'][ $cAnnexDetails -> answer_id ] -> annex_ans))
                                $this -> data['db_ans'][ $cAnnexDetails -> answer_id ] -> annex_ans = [];

                            $this -> data['db_ans'][ $cAnnexDetails -> answer_id ] -> annex_ans[ $cAnnexDetails -> id ] = $cAnnexDetails;
                        }
                    }
                }
            }
        }

        // helper function call // for account purpose
        $this -> data['db_ans'] = modified_answers_data($this -> data['db_ans']);

        // check executive summary // method call
        $this -> data['exe_summary_data'] = $this -> endAssesmentExecutiveSummaryCheck();

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        return return2View($this, $this -> me -> viewDir . 'end-assesment', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);
    }

    public function endReAssesment()
    {
        $this -> getAssesmentData(); // method call

        // change me
        $this -> me = SiteUrls::get('auditEndAssessment');

        //top btn array
        $this -> data['topBtnArr'] = [
            'default' => [ 'href' => SiteUrls::getUrl('audit') ],
        ];

        // only end assesment // re assesment method is diffrent
        if($this -> assesmentData -> audit_status_id != 3) {

            Except::exc_404( Notifications::getNoti('endAssesmentAlreadyDoneError') );
            exit;
        }

        //top data container
        $this -> data['data_container'] = true;
        
        // function call // 13.06.2024
        $reAuditFindData = get_re_audit_menu_data($this);

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);

        // RE AUDIT CHECK
        if(empty($reAuditFindData['menu']) && empty($reAuditFindData['category']))
        {
            return return2View($this, $this -> me -> viewDir . 'empty-end-reassesment', [ 
                'request' => $this -> request,
                'data' => $this -> data,
                'db_assesment' => $this -> assesmentData,
                'remarkTypeArray' => $remarkArray,
            ]);
        }

        if(empty($reAuditFindData['menu']) || empty($reAuditFindData['category'])) {

            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        $this -> menuData = $this -> getMenuData($reAuditFindData); // method call
        $ansData = $this -> reAuditFindData($reAuditFindData, false, [ 'menu' => $reAuditFindData['menu'] ]);

        // reassign dump data
        $reAuditFindData['advance_dump_id'] = $ansData['advance_dump_id'];
        $reAuditFindData['deposite_dump_id'] = $ansData['deposite_dump_id'];

        // find set data
        $data = get_all_question_data( $this, $this -> assesmentData, $ansData );

        $this -> data['db_assesment_data'] = $this -> assesmentData;
        $this -> data['db_menu'] = $data['db_menu'];
        $this -> data['db_category'] = $data['db_category'];
        $this -> data['db_sets'] = $data['db_sets'];

        $this -> data['exe_summary_data'] = array(
            'rejected' => 0,
            'pending_reaudit' => 0
        );

        // method call // for advances
        $this -> endAssesmentDumpData($reAuditFindData, 2);

        // method call // for deposits
        $this -> endAssesmentDumpData($reAuditFindData, 1);

        // helper function call // for account purpose
        $this -> data['db_ans'] = modified_answers_data($ansData['ans']);
        $this -> data['reaudit_ans'] = $ansData['reaudit_ans'];
        $this -> data['reaudit_annex'] = $ansData['reaudit_annex'];
        
        // check executive summary // method call
        $this -> data['exe_summary_data'] = $this -> endAssesmentExecutiveSummaryCheck();

        return return2View($this, $this -> me -> viewDir . 'end-reassesment', [ 
            'request' => $this -> request,
            'data' => $this -> data,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);
    }

    private function checkAccAssesmentComplete($dumpData)
    {
        $pending = 0;

        if(is_array($dumpData) && sizeof($dumpData) > 0)
        {
            foreach($dumpData as $cDumpId => $cDumpDetails)
            {
                if(  $cDumpDetails -> sampling_filter == 1 && 
                    ($cDumpDetails -> assesment_period_id == 0 || $cDumpDetails -> assesment_period_id == ''))
                    $pending++;
            }
        }

        return $pending;
    }

    private function findSubsetSets($categoryDetails)
    {
        $model = $this -> model('QuestionMasterModel');

        $findSubsetQuestions = $model -> getAllQuestions([
            'where' => 'set_id = :set_id AND option_id = 5 AND is_active = 1 AND deleted_at IS NULL',
            'params' => [ 'set_id' => $categoryDetails -> question_set_ids ]
        ]);

        if(is_array($findSubsetQuestions) && sizeof($findSubsetQuestions) > 0)
        {
            $categoryDetails -> question_set_ids = !empty($categoryDetails -> question_set_ids) ? explode(',', $categoryDetails -> question_set_ids) : [];

            foreach($findSubsetQuestions as $cSubsetQuesDetails)
            {
                $cSubsetQuesDetails -> subset_multi_id = !empty($cSubsetQuesDetails -> subset_multi_id) ? explode(',', $cSubsetQuesDetails -> subset_multi_id) : [];
                
                if(is_array($cSubsetQuesDetails -> subset_multi_id) && sizeof($cSubsetQuesDetails -> subset_multi_id) > 0)
                {
                    foreach($cSubsetQuesDetails -> subset_multi_id as $ccSubsetId)
                    {
                        if(!empty($ccSubsetId) && !in_array($ccSubsetId, $categoryDetails -> question_set_ids))
                            $categoryDetails -> question_set_ids[] = $ccSubsetId;
                    }
                }
            }

            $categoryDetails -> question_set_ids = implode(',', $categoryDetails -> question_set_ids);
        }

        return $categoryDetails;
    }

    private function endAssesmentDumpData($reAudit = null, $type = '2') {

        $typesArr = [ '2' => 'advances', '1' => 'deposits' ];
        $this -> data['pending_asses_' . $typesArr[ $type ] ] = 0;

        // get advance accounts // helper function call
        $returnData = get_category_dump_data_report($this, $this -> data['db_category'], $this -> assesmentData, $sampling = 1, $type, [], null, $reAudit);

        if(is_array($returnData) && sizeof($returnData) > 0)
        {
            $this -> data['db_category'] = $returnData['db_category'];
            $this -> data['db_scheme_data_' . $type] = $returnData['scheme_data'];
            $this -> data['db_dump_data_' . $type] = $returnData['dump_data'];

            // method call
            $this -> data[ 'pending_asses_' . $typesArr[ $type ] ] = $this -> checkAccAssesmentComplete($this -> data['db_dump_data_' . $type]);
        }
    }

    private function endAssesmentExecutiveSummaryCheck() {

        $exRes = array( 'rejected' => 0, 'pending_reaudit' => 0 );

        if( !is_object($this -> assesmentData) )
            return $exRes;

        $tempMenuData = !empty($this -> assesmentData -> menu_ids) ? explode(',', $this -> assesmentData -> menu_ids) : [];

        if(is_array($tempMenuData) && in_array(1, $tempMenuData)) {

            $checkReAudit = check_re_assesment_status($this -> assesmentData);

            // check for excutive summary
            $findESBasicDetailsData = null; $findESBranchPosData = null; $findESFinancialPosData = null;            

            // check for excutive summary basic details
            if(!$checkReAudit) //check only status = 1
            {
                $esModel = $this -> model('ExeSummaryBasicModel');

                $findESBasicDetailsData = get_all_data_query_builder(1, $esModel, $esModel -> getTableName(), [
                    'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => [
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id
                    ]
                ], 'sql', "SELECT COUNT(*) total_count FROM " . $esModel -> getTableName());

                if( !($findESBasicDetailsData -> total_count > 0) )
                    $exRes['pending_reaudit']++;
            }

            $esModel = $this -> model('ExeSummaryBranchPositionModel');

            // excutive summary branch position
            if( $checkReAudit ) // for re audit
            {
                $findESBranchPosData = $esModel -> getAllBranchPosition([
                    'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND audit_status_id = :audit_status_id AND batch_key != :batch_key AND deleted_at IS NULL',
                    'params' => [
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id,
                        'batch_key' => $this -> assesmentData -> batch_key,
                        'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                    ]
                ]);

                if(is_array($findESBranchPosData) && sizeof($findESBranchPosData) > 0)
                {
                    foreach($findESBranchPosData as $cESBranchPos)
                    {
                        // for pending
                        $exRes['rejected']++;

                        // check for batch key // reaudit
                        if($cESBranchPos -> batch_key != $this -> assesmentData -> batch_key)
                            $exRes['pending_reaudit']++;
                    }
                }
            }
            else
            {
                // pending assesment
                $findESBranchPosData = get_all_data_query_builder(1, $esModel, $esModel -> getTableName(), [
                    'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => [
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id
                    ]
                ], 'sql', "SELECT COUNT(*) total_count FROM " . $esModel -> getTableName());

                if( !($findESBranchPosData -> total_count > 0) )
                    $exRes['pending_reaudit']++;
            }

            $esModel = $this -> model('ExeSummaryFreshAccountModel');

            // excutive summary last march position
            if( $checkReAudit ) // for re audit
            {
                $findESFinancialPosData = $esModel -> getAllFreshAccount([
                    'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND audit_status_id = :audit_status_id AND batch_key != :batch_key AND deleted_at IS NULL',
                    'params' => [
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id,
                        'batch_key' => $this -> assesmentData -> batch_key,
                        'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[3]['status_id']
                    ]
                ]);

                if(is_array($findESFinancialPosData) && sizeof($findESFinancialPosData) > 0)
                {
                    foreach($findESFinancialPosData as $cESFinancialPos)
                    {
                        // for pending
                        $exRes['rejected']++;

                        // check for batch key // reaudit
                        if($cESFinancialPos -> batch_key != $this -> assesmentData -> batch_key)
                            $exRes['pending_reaudit']++;
                    }
                }
            }
            else
            {
                $findESFinancialPosData = get_all_data_query_builder(1, $esModel, $esModel -> getTableName(), [
                    'where' => 'year_id = :year_id AND assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => [
                        'year_id' => $this -> assesmentData -> year_id,
                        'assesment_id' => $this -> assesmentData -> id
                    ]
                ], 'sql', "SELECT COUNT(*) total_count FROM " . $esModel -> getTableName());

                if( !($findESFinancialPosData -> total_count > 0) )
                    $exRes['pending_reaudit']++;
            }

            unset($findESBasicDetailsData, $findESBranchPosData, $findESFinancialPosData, $esModel);

        }
        
        return $exRes;
    }

    public function endAssesmentSubmit() {

        // post method after form submit
        $this -> request::method("POST", function() {

            $this -> getAssesmentData(); // method call

            if( !$this -> request -> has('pending_points') || 
                !$this -> request -> has('submit') || 
                ($this -> request -> has('pending_points') && !($this -> request -> has('pending_points') > 0)))
            {
                Except::exc_404( Notifications::getNoti('errorSaving') );
                exit;
            }

            $insertArray = array(
                'id' => $this -> assesmentData -> id,
                'type' => 1,
                'status' => ASSESMENT_TIMELINE_ARRAY[ 2 ]['status_id'],
                'rejected_cnt' => 0,
                'emp_id' => Session::get('emp_id'),
                'batch_key' => $this -> assesmentData -> batch_key,
            );
                
            if(!audit_assesment_timeline_insert($this, $insertArray))
            {
                Except::exc_404( Notifications::getNoti('errorSaving') );
                exit;
            }

            // check audit assesment timeline for reject limit
            $auditAssesmentTimelineCount = get_all_data_query_builder(1, $this -> auditAssesmentModel, 'audit_assesment_timeline', [
                'where' => 'assesment_id = "'. $this -> assesmentData -> id .'" AND type_id = 1 AND status_id = "'. ASSESMENT_TIMELINE_ARRAY[3]['status_id'] .'" AND deleted_at IS NULL', 'params' => [ ]
            ], 'sql', "SELECT COUNT(*) total_assesment_timeline_count FROM audit_assesment_timeline");

            $auditAssesmentTimelineCount = $auditAssesmentTimelineCount -> total_assesment_timeline_count;

            $auditAssesmentTimelineCount = ($auditAssesmentTimelineCount >= $this -> assesmentData -> audit_review_reject_limit) ? 1 : 0;

            $updateArray = [
                'audit_end_date' => date($GLOBALS['dateSupportArray'][1]),
                'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[2]['status_id'],
                'audit_emp_id' => Session::get('emp_id')
            ]; 

            // check for reviewer
            if(!array_key_exists(2, $GLOBALS['userTypesArray']))
            {
                $updateArray = [
                    'audit_end_date' => date($GLOBALS['dateSupportArray'][1]),
                    'audit_status_id' => ASSESMENT_TIMELINE_ARRAY[4]['status_id'],
                    'audit_emp_id' => Session::get('emp_id'),
                ];  
            }

            // add key in update array
            $updateArray['is_limit_blocked'] = $auditAssesmentTimelineCount;

            $result = $this -> auditAssesmentModel::update(
                $this -> auditAssesmentModel -> getTableName(), 
                $updateArray, [
                    'where' => 'id = :id',
                    'params' => [ 'id' => $this -> assesmentData -> id ]
                ]
            );

            // update last march position
            if($result && $this -> assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'])
            {
                // find audit unit
                $model = $this -> model('AuditUnitModel');

                $result = $model::update(
                    $model -> getTableName(), [
                        'last_audit_date' => $this -> assesmentData -> assesment_period_to
                    ], [
                        'where' => 'id = :id',
                        'params' => [ 'id' => $this -> assesmentData -> audit_unit_id ]
                    ]
                );
            }

            if(!$result)
                return Except::exc_404( Notifications::getNoti('somethingWrong') );

            //after insert data redirect to target Master dashboard
            Validation::flashErrorMsg('endAssesmentSuccess', 'success');
            Redirect::to( SiteUrls::getUrl('dashboard') . '?auditUnit=' . encrypt_ex_data($this -> assesmentData -> audit_unit_id) );
            exit;

        });

        Except::exc_404();
        exit;
    }

    private function getEvidenceData($ansData, $ansIds, $type = 1)
    {
        require EVIDENCE_UPLOAD['controller'];

        // function call
        return get_evidence_controller_data($ENV_AT, $this -> assesmentData, $ansData, $ansIds, $type); 
    }

    // CARRY FORWARD MODELS 10.08.2024
    public function displayCarryForwardPanel() 
    {
        $this -> getAssesmentData(); //method call
        $exceptErr = false;
        $cfData = null;

        // CHECK CF DATA EXITS OR NOT
        if( !isset($this -> assesmentData -> menu_ids_explode) || 
            !is_array($this -> assesmentData -> menu_ids_explode) || 
            !in_array(CARRY_FORWARD_ARRAY['id'], $this -> assesmentData -> menu_ids_explode) )
            $exceptErr = true;

        if(!$exceptErr)
        {
            // check in database JOIN QUERY
            // $select = "SELECT 
            //             ad.id ad_id, 
            //             aad.*
            //         FROM 
            //             answers_data ad
            //         RIGHT JOIN 
            //             answers_data_annexure aad ON ad.id = aad.answer_id
            //         WHERE
            //                 ad.answer_given = '". CARRY_FORWARD_ARRAY['id'] ."' 
            //             AND ad.assesment_id = '". $this -> assesmentData -> id ."'
            //             AND aad.assesment_id = '". $this -> assesmentData -> id ."' 
            //         GROUP BY 
            //             ad.id, aad.id";

                $select = "SELECT *
                    FROM 
                        answers_data ad
                    WHERE
                        ad.answer_given = '". CARRY_FORWARD_ARRAY['id'] ."' 
                        AND ad.assesment_id = '". $this -> assesmentData -> id ."'
                    GROUP BY 
                        ad.id";

            $cfData = get_all_data_query_builder(2, $this -> ansModel, $this -> ansModel -> getTableName(), [], 'sql', $select); 

            if(!is_array($cfData) || ( is_array($cfData) && !(sizeof($cfData) > 0) ))
                $exceptErr = true;

            if(!$exceptErr)
            {
                // find annex or CF points // method call
                $cfData = generate_data_assoc_array($cfData, 'id');
                $cfData = $this -> getAnnexAnswers($cfData);

                // shift single answer to $var
                $cfData = $cfData[ array_keys($cfData)[0] ];

                if( !isset($cfData -> annex_ans) || 
                    !is_array($cfData -> annex_ans) || 
                    (is_array($cfData -> annex_ans) && !(sizeof($cfData -> annex_ans) > 0)) )
                    $exceptErr = true;                    
            }
        }

        if($exceptErr)
        {
            // CF not exits in menu
            Except::exc_404( Notifications::getNoti('cfDataNotFound') );
            exit;
        }

        // get evidence
        if(check_evidence_upload_strict())            
        {
            $cfData = $this -> getEvidenceData( $cfData, array_keys($cfData -> annex_ans) );
            $this -> data['js'][] = EVIDENCE_UPLOAD['assets'] . 'evidence-auditpro.min.js';
        }

        // function call
        $remarkArray = unset_remark_options($this -> assesmentData);
        
        // Change me data
        $formPath = str_replace('/assesment', '', $this -> me -> viewDir);
        $this -> me -> pageTitle = $this -> me -> pageHeading = CARRY_FORWARD_ARRAY['title'];
        $this -> me -> id = $this -> me -> menuKey = 'menu_' . CARRY_FORWARD_ARRAY['id'];

        $reAuditFindData = null;

        // function call // 13.06.2024
        if( check_re_assesment_status($this -> assesmentData) )
            $reAuditFindData = get_re_audit_menu_data($this);

        $this -> menuData = $this -> getMenuData($reAuditFindData); // method call

        return return2View($this, $formPath . 'carry-forward-dynamic-form', [ 
            'request' => $this -> request,
            'cf_data' => $cfData,
            'menu_data' => $this -> menuData,
            'db_assesment' => $this -> assesmentData,
            'remarkTypeArray' => $remarkArray,
        ]);

        // exit;
    }

    // CARRY FORWARD MODEL COMMENT SAVE 19.08.2024
    public function cfCommentSave()
    {
        $this -> getAssesmentData(1); //method call

        $res_array = [ 'msg' => 'somethingWrong', 'res' => 'err' ];
        
        $requestData = isset($_POST['data']) ? $_POST['data'] : null;

        if( !is_object($this -> assesmentData) || empty($requestData) )
        {
            echo json_encode($res_array);
            exit;
        }

        $findCurrentAnsData = null;
        $requestData = json_decode($requestData);

        // validation
        if( !isset( $requestData -> compliance ) )
            $res_array['msg'] = 'validComment';

        else if( isset($requestData -> ans_id) && !empty($requestData -> ans_id) )
        {
            //find ans
            $whereData = [
                'where' => 'id = :id AND assesment_id = :assesment_id',
                'params' => [ 
                    'id' =>  decrypt_ex_data($requestData -> ans_id),
                    'assesment_id' => $this -> assesmentData -> id
                ]
            ];

            $findCurrentAnsData = $this -> ansAnnexModel -> getSingleAnswerAnnexure($whereData);
        }

        if(is_object($findCurrentAnsData))
        {
            // update array
            $updateArray = [ 
                'audit_comment' => $requestData -> compliance, 
                'audit_emp_id' => Session::get('emp_id') 
            ];

            $timelineErr = false;

            // check re assesment 08.09.2024 // update check for recompliance
            if($findCurrentAnsData -> batch_key != $this -> assesmentData -> batch_key)
            {
                // update batch key
                $updateArray['batch_key'] = $this -> assesmentData -> batch_key;

                // shift to timeline // if batch key changed shift answer to the timeline
                $ansTimelineModel = $this -> model('AnswerDataTimelineModel');

                $insertArray = array(
                    "answer_id" => $findCurrentAnsData -> answer_id,
                    "annex_id" => $findCurrentAnsData -> id,
                    "assesment_id" => $findCurrentAnsData -> assesment_id,
                    "last_updated_at" => $findCurrentAnsData -> updated_at,
                    "answer_type" => 1, //for audit
                    "answer_given" => $findCurrentAnsData -> answer_given,
                    "audit_comment" => $findCurrentAnsData -> audit_comment,
                    "audit_emp_id" => $findCurrentAnsData -> audit_emp_id,
                    "audit_status_id" => $findCurrentAnsData -> audit_status_id,
                    "audit_reviewer_emp_id" => $findCurrentAnsData -> audit_reviewer_emp_id,
                    "audit_reviewer_comment" => $findCurrentAnsData -> audit_reviewer_comment,
                    "audit_commpliance" => $findCurrentAnsData -> audit_commpliance,
                    "compliance_evidance_upload" => $findCurrentAnsData -> compliance_evidance_upload,
                    "compliance_emp_id" => $findCurrentAnsData -> compliance_emp_id,
                    "compliance_status_id" => $findCurrentAnsData -> compliance_status_id,
                    "compliance_reviewer_emp_id" => $findCurrentAnsData -> compliance_reviewer_emp_id,
                    "compliance_reviewer_comment" => $findCurrentAnsData -> compliance_reviewer_comment,
                    "business_risk" => $findCurrentAnsData -> business_risk,
                    "control_risk" => $findCurrentAnsData -> control_risk,
                    "risk_cat_id" => $findCurrentAnsData -> risk_cat_id,
                    "instances_count" => (isset($findCurrentAnsData -> instances_count) ? $findCurrentAnsData -> instances_count : 0),
                    "batch_key" => $findCurrentAnsData -> batch_key
                );

                // insert in database
                $result = $ansTimelineModel::insert(
                    $ansTimelineModel -> getTableName(), $insertArray
                );

                if(!$result)
                {
                    $res_array['msg'] = 'errorSaving';
                    $timelineErr = true;
                }
            }

            if(!$timelineErr)
            {
                $result = $this -> ansAnnexModel::update(
                    $this -> ansAnnexModel -> getTableName(), $updateArray, $whereData
                );

                if($result)
                {
                    $res_array['msg'] = 'auditCommentSuccess';
                    $res_array['res'] = "success";
                }
                else
                    $res_array['msg'] = 'errorSaving';
            }
        }

        $res_array['msg'] = Notifications::getNoti($res_array['msg']);

        echo json_encode($res_array);
        exit;
    }
    /**
 * Unified function to handle soft delete and update for questions and headers
 * 
 * @param string $type - 'question' or 'header'
 * @param int $id - ID of the record to update/delete
 * @param string $action - 'delete' or 'update'
 * @param array $data - Update data (for update action)
 * @return array Response with status and message
 */
private function handleQuestionHeaderOperation($type, $id, $action = 'delete', $data = [])
{
    $response = [
        'success' => false,
        'message' => Notifications::getNoti('somethingWrong'),
        'data' => null
    ];

    // Validate assessment data
    if (!isset($this->assesmentData) || !is_object($this->assesmentData)) {
        $this->getAssesmentData(true);
    }

    if (!is_object($this->assesmentData)) {
        $response['message'] = Notifications::getNoti('assesmentNotFound');
        return $response;
    }

    // Decrypt ID if encrypted
    $decryptedId = decrypt_ex_data($id);
    $recordId = $decryptedId ?: $id;

    // Determine model and table based on type
    $model = null;
    $tableName = '';
    $idField = 'id';
    
    switch ($type) {
        case 'question':
            $model = $this->model('QuestionMasterModel');
            $tableName = $model->getTableName();
            break;
        case 'header':
            $model = $this->model('HeaderMasterModel');
            $tableName = $model->getTableName();
            break;
        case 'answer':
            $model = $this->ansModel;
            $tableName = $model->getTableName();
            break;
        case 'answer_annex':
            $model = $this->ansAnnexModel;
            $tableName = $model->getTableName();
            break;
        default:
            $response['message'] = Notifications::getNoti('invalidType');
            return $response;
    }

    // Check if record exists
    $whereData = [
        'where' => "id = :id AND deleted_at IS NULL",
        'params' => ['id' => $recordId]
    ];

    $record = null;
    
    if ($type == 'answer') {
        $record = $model->getSingleAnswer($whereData);
    } elseif ($type == 'answer_annex') {
        $record = $model->getSingleAnswerAnnexure($whereData);
    } else {
        $record = get_all_data_query_builder(1, $model, $tableName, $whereData);
    }

    if (!$record) {
        $response['message'] = Notifications::getNoti('recordNotFound');
        return $response;
    }

    // Handle actions
    switch ($action) {
        case 'delete':
            // Soft delete
            $updateData = [
                'deleted_at' => date($GLOBALS['dateSupportArray'][2]),
                'updated_at' => date($GLOBALS['dateSupportArray'][1])
            ];

            // If it's an answer, also handle related annex answers
            if ($type == 'answer') {
                // First, soft delete all related annex answers
                $annexResult = $this->ansAnnexModel::update(
                    $this->ansAnnexModel->getTableName(),
                    $updateData,
                    [
                        'where' => 'answer_id = :answer_id AND deleted_at IS NULL',
                        'params' => ['answer_id' => $recordId]
                    ]
                );

                if (!$annexResult) {
                    $response['message'] = Notifications::getNoti('errorDeletingAnnex');
                    return $response;
                }
            }

            // Perform the soft delete
            $result = $model::update(
                $tableName,
                $updateData,
                [
                    'where' => 'id = :id',
                    'params' => ['id' => $recordId]
                ]
            );

            if ($result) {
                $response['success'] = true;
                $response['message'] = Notifications::getNoti(ucfirst($type) . 'DeletedSuccess');
            }
            break;

        case 'update':
            // Validate update data
            if (empty($data)) {
                $response['message'] = Notifications::getNoti('noUpdateData');
                return $response;
            }

            // Add updated timestamp
            $data['updated_at'] = date($GLOBALS['dateSupportArray'][1]);

            // If it's a question, handle special fields
            if ($type == 'question') {
                // Handle JSON fields if present
                if (isset($data['parameters']) && is_array($data['parameters'])) {
                    $data['parameters'] = json_encode($data['parameters']);
                }
                if (isset($data['subset_multi_id']) && is_array($data['subset_multi_id'])) {
                    $data['subset_multi_id'] = implode(',', $data['subset_multi_id']);
                }
            }

            // If it's an answer and we're updating answer_given, check for annex
            if ($type == 'answer' && isset($data['answer_given'])) {
                // Check if this answer has annex records
                $annexCount = get_all_data_query_builder(1, $this->ansAnnexModel, $this->ansAnnexModel->getTableName(), [
                    'where' => 'answer_id = :answer_id AND deleted_at IS NULL',
                    'params' => ['answer_id' => $recordId]
                ], 'sql', "SELECT COUNT(*) as total FROM " . $this->ansAnnexModel->getTableName());

                if ($annexCount && $annexCount->total > 0) {
                    // Option 1: Prevent update if annex exists
                    $response['message'] = Notifications::getNoti('cannotUpdateAnswerWithAnnex');
                    return $response;
                    
                    // Option 2: Auto-delete annex records (uncomment if needed)
                    /*
                    $this->ansAnnexModel::update(
                        $this->ansAnnexModel->getTableName(),
                        ['deleted_at' => date($GLOBALS['dateSupportArray'][2])],
                        [
                            'where' => 'answer_id = :answer_id',
                            'params' => ['answer_id' => $recordId]
                        ]
                    );
                    */
                }
            }

            // Perform the update
            $result = $model::update(
                $tableName,
                $data,
                [
                    'where' => 'id = :id',
                    'params' => ['id' => $recordId]
                ]
            );

            if ($result) {
                $response['success'] = true;
                $response['message'] = Notifications::getNoti(ucfirst($type) . 'UpdatedSuccess');
                
                // Fetch updated record
                if ($type == 'answer') {
                    $response['data'] = $model->getSingleAnswer([
                        'where' => 'id = :id',
                        'params' => ['id' => $recordId]
                    ]);
                } elseif ($type == 'answer_annex') {
                    $response['data'] = $model->getSingleAnswerAnnexure([
                        'where' => 'id = :id',
                        'params' => ['id' => $recordId]
                    ]);
                } else {
                    $response['data'] = get_all_data_query_builder(1, $model, $tableName, [
                        'where' => 'id = :id',
                        'params' => ['id' => $recordId]
                    ]);
                }
            }
            break;

        default:
            $response['message'] = Notifications::getNoti('invalidAction');
            return $response;
    }

    // If this is part of a re-assessment, handle timeline
    if ($response['success'] && check_re_assesment_status($this->assesmentData)) {
        $this->moveToTimelineIfNeeded($type, $recordId, $record);
    }

    return $response;
}

/**
 * Move record to timeline if it's part of re-assessment
 */
private function moveToTimelineIfNeeded($type, $recordId, $oldRecord)
{
    if (!isset($oldRecord->batch_key) || $oldRecord->batch_key == $this->assesmentData->batch_key) {
        return;
    }

    $timelineModel = $this->model('AnswerDataTimelineModel');
    
    if ($type == 'answer') {
        $timelineData = [
            "answer_id" => $oldRecord->id,
            "annex_id" => 0,
            "assesment_id" => $oldRecord->assesment_id,
            "last_updated_at" => $oldRecord->updated_at,
            "answer_type" => 1,
            "answer_given" => $oldRecord->answer_given,
            "audit_comment" => $oldRecord->audit_comment,
            "audit_emp_id" => $oldRecord->audit_emp_id,
            "audit_status_id" => $oldRecord->audit_status_id,
            "audit_reviewer_emp_id" => $oldRecord->audit_reviewer_emp_id,
            "audit_reviewer_comment" => $oldRecord->audit_reviewer_comment,
            "audit_commpliance" => $oldRecord->audit_commpliance,
            "compliance_evidance_upload" => $oldRecord->compliance_evidance_upload,
            "compliance_emp_id" => $oldRecord->compliance_emp_id,
            "compliance_status_id" => $oldRecord->compliance_status_id,
            "compliance_reviewer_emp_id" => $oldRecord->compliance_reviewer_emp_id,
            "compliance_reviewer_comment" => $oldRecord->compliance_reviewer_comment,
            "business_risk" => $oldRecord->business_risk,
            "control_risk" => $oldRecord->control_risk,
            "risk_cat_id" => $oldRecord->risk_cat_id ?? 0,
            "instances_count" => $oldRecord->instances_count ?? 0,
            "batch_key" => $oldRecord->batch_key
        ];
        
        $timelineModel::insert($timelineModel->getTableName(), $timelineData);
    }
}

/**
 * AJAX endpoint for handling operations
 */
public function handleOperation()
{
    $this->getAssesmentData(true);
    
    $response = ['success' => false, 'message' => Notifications::getNoti('invalidRequest')];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $type = $this->request->input('type');
        $id = $this->request->input('id');
        $action = $this->request->input('action', 'delete');
        $data = $this->request->input('data', []);
        
        if ($type && $id) {
            $response = $this->handleQuestionHeaderOperation($type, $id, $action, $data);
        }
    }
    
    echo json_encode($response);
    exit;
}
}

?>