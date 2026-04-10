<?php

// IMPORTANT FUNCTIONS FOR BROADER AREA WISE SCORE 21.06.2024
if(!function_exists('GET_ALL_BROADER_AREA_HELPER')) {

    function GET_ALL_BROADER_AREA_HELPER($thisObj, $filter = null, $sortSelect = false)
    {
        if(!is_array($filter))
            $filter = [
                'where' => 'deleted_at IS NULL',
                'params' => []
            ];
        
        // find audit section model
        $model = $thisObj -> model('BroaderAreaModel');
        $dbTable = $model -> getTableName();

        $broaderAreaData = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT id, name FROM " . $dbTable);

        if($sortSelect)
            return generate_array_for_select($broaderAreaData, 'id', 'name');

        return generate_data_assoc_array($broaderAreaData, 'id');
    }
}

if(!function_exists('GET_MATRIX_RISK_CALCULATIONS_HELPER'))
{
    function GET_MATRIX_RISK_CALCULATIONS_HELPER($thisObj, $fyId, $filter = null)
    {
        if(!is_array($filter))
            $filter = [
                'where' => 'year_id = :year_id AND risk_parameter != "4" AND deleted_at IS NULL ORDER BY risk_parameter',
                'params' => [ 'year_id' => $fyId ]
            ];
        
        // find audit section model
        $model = $thisObj -> model('RiskMatrixModel');
        $matrixRiskArray = $model -> getAllRiskMatrix($filter);
        $matrixRiskArray = generate_data_assoc_array($matrixRiskArray, 'risk_parameter');
        $matrixRiskZeroArray = array();
        $matrixRiskTypeArray = array();

        if(is_array($matrixRiskArray) && sizeof($matrixRiskArray) > 0)
        {
            $tempArray = [];

            foreach (array_keys($matrixRiskArray) as $cRiskParameter) 
            {
                foreach (array_keys($matrixRiskArray) as $cRiskParameter2) 
                {
                    $cScore = $matrixRiskArray[ $cRiskParameter ] -> business_risk_score + $matrixRiskArray[ $cRiskParameter2 ] -> control_risk_score;
                    $tempArray[ $cRiskParameter . '.' . $cRiskParameter2] = $cScore;
                    $matrixRiskZeroArray[ $cRiskParameter . '.' . $cRiskParameter2 ] = 0;

                    if( in_array($cScore, [2,3,4]) )
                        $cScore = array_keys(RISK_PARAMETERS_ARRAY)[2];
                    elseif( in_array($cScore, [5,6,7]) )
                        $cScore = array_keys(RISK_PARAMETERS_ARRAY)[1];
                    elseif( in_array($cScore, [8,9,10]) )
                        $cScore = array_keys(RISK_PARAMETERS_ARRAY)[0];
                    else
                        $cScore = array_keys(RISK_PARAMETERS_ARRAY)[3];

                    $matrixRiskTypeArray[ $cRiskParameter . '.' . $cRiskParameter2 ] = $cScore;
                }
            }

            $matrixRiskArray = $tempArray;
        }

        return [ 'matrixRisk' => $matrixRiskArray, 'matrixRiskZero' => $matrixRiskZeroArray, 'matrixRiskType' => $matrixRiskTypeArray ];
    }
}

if(!function_exists('BROADER_AREA_COMMON_DATA_HELPER')) {
    
    // update $extra var 12.11.2024
    function BROADER_AREA_COMMON_DATA_HELPER($thisObj, $fyId, $extra = [])
    {
        // get broader area
        $resData = [ 'broaderArea' => null, 'matrixRisk' => null, 'riskCategory' => null, 'accCategory' => null, 'err' => null ];
        
        // update 12.11.2024
        if(!isset($extra['noBoraderAreaNeeded']))
        {
            // helper function call
            $resData['broaderArea'] = GET_ALL_BROADER_AREA_HELPER($thisObj);

            if(!is_array($resData['broaderArea']) || (is_array($resData['broaderArea']) && empty($resData['broaderArea'])) )
                $resData['err'] = 'broaderAreaNotFound'; // noti key
        }

        if(empty($resData['err']))
        {
            // matrix array // helper function call
            $resDataMatrixRisk = GET_MATRIX_RISK_CALCULATIONS_HELPER($thisObj, $fyId);
            $resData['matrixRisk'] = $resDataMatrixRisk['matrixRisk'];
            $resData['matrixRiskZero'] = $resDataMatrixRisk['matrixRiskZero'];
            $resData['matrixRiskType'] = $resDataMatrixRisk['matrixRiskType'];
            unset($resDataMatrixRisk);

            if(!is_array($resData['matrixRisk']) || (is_array($resData['matrixRisk']) && empty($resData['matrixRisk'])))
                $resData['err'] = 'riskMatrixNoData'; // noti key
        }

        if(empty($resData['err']))
        {
            $DBCommonFunc = new Core\DBCommonFunc();

            $model = $thisObj -> model('RiskCategoryModel');
            $resData['riskCategory'] = $DBCommonFunc::getAllRiskWeightage($model, $fyId);
            $resData['riskCategoryStore'] = [];

            if(is_array($resData['riskCategory']) && sizeof($resData['riskCategory']) > 0)
            {
                foreach($resData['riskCategory'] as $cRiskId => $cRiskDetails )
                {
                    $resData['riskCategoryStore'][ $cRiskId ] = [ 'wg_sc' => 0, 'avg_sc' => 0 ];

                    foreach(RISK_PARAMETERS_ARRAY as $rTyId => $rTyDetails) {
                        $resData['riskCategoryStore'][ $cRiskId ][ $rTyId ] = 0;
                    }
                }
            }

            if(!is_array($resData['riskCategory']) || 
              (is_array($resData['riskCategory']) && !sizeof($resData['riskCategory']) > 0))
                $resData['err'] = 'riskCategoryNoData'; // noti key
        }

        if(empty($resData['err']))
        {
            // select category for diffrentiate question with deposits / advances / general
            $model = $thisObj -> model('CategoryModel');
            $dbTable = $model -> getTableName();
            $filter = [ 'where' => 'linked_table_id != 0 AND is_active = 1 AND deleted_at IS NULL', 'params' => [] ];

            $resData['accCategory'] = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT id, menu_id, name, linked_table_id, question_set_ids, is_cc_acc_category, is_active FROM " . $dbTable);

            $resData['accCategory'] = generate_data_assoc_array($resData['accCategory'], 'id');
        }

        return $resData;
    }
}

if(!function_exists('CALCULATE_QUAL_QUAN_SCORE_HELPER')) 
{
    function CALCULATE_QUAL_QUAN_SCORE_HELPER($qqArray)
    {
        $total = 0;

        if(is_array($qqArray) && sizeof($qqArray) > 0)
        {
            foreach($qqArray as $c_gen_key => $c_score)
            {
                $total += $c_score;
            }
        }

        return get_decimal($total, 2);
    }
}

if(!function_exists('BROADER_AREA_RISK_WISE_ANS_SORT_HELPER'))
{
    function BROADER_AREA_RISK_WISE_ANS_SORT_HELPER($dataArray, $resData, $ansDataArray, $extra = [])
    {
        //$ansType = general OR annexure
        $extra['ans_type'] = !isset($extra['ans_type']) ? 'general' : $extra['ans_type'];
        $extra['annex_data'] = [];

        if(is_array($ansDataArray) && sizeof($ansDataArray) > 0):

        foreach($ansDataArray as $cAnsId => $row)
        {
            // check which category question // default general
            $C_SORT_CAT = array_keys($dataArray['SORTED_BORADER_AREA_KEYS'])[0];
            $C_ASSES_ID = $row -> assesment_id;

            // recompliance
            $cComplianceMarkCnt = 0;

            // advances / deposits
            if( $row -> dump_id != '0' && 
                is_array($extra['common_data']['accCategory']) && 
                array_key_exists($row -> category_id, $extra['common_data']['accCategory']) )
            {
                $cDumpTable = $extra['common_data']['accCategory'][ $row -> category_id ] -> linked_table_id;

                if( array_key_exists($cDumpTable, $GLOBALS['schemeTypesArray']))
                    $C_SORT_CAT = string_operations($GLOBALS['schemeTypesArray'][ $cDumpTable ]);

                // compliance for account
                if( $row -> is_compliance == 1 || $row -> is_compliance != 'undefined' )
                    $cComplianceMarkCnt = 1;
            }

            if( $row -> option_id == '4' /*'annexure'*/ && $row -> dump_id == '0' )
            {
                if( $extra['ans_type'] == 'general' && /*$row -> annexure_json != 'undefined' &&*/ intval($row -> answer_given) > 0)
                    $C_SORT_CAT = array_keys($dataArray['SORTED_BORADER_AREA_KEYS'])[0];
            }

            $C_AUDIT_AREA = $row -> audit_area;

            if( is_array($extra['common_data']['broaderArea']) && 
                array_key_exists($C_AUDIT_AREA, $extra['common_data']['broaderArea']) &&
                is_array($extra['common_data']['riskCategory']) &&
                array_key_exists($row -> risk_category_id, $extra['common_data']['riskCategory']) )
            {
                if( !array_key_exists($C_AUDIT_AREA, $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area']) )            
                {
                    $cBroaderArea = $extra['common_data']['broaderArea'][ $C_AUDIT_AREA ];

                    // push data
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $C_AUDIT_AREA ] = (object) [
                        'id' => $cBroaderArea -> id, 'name' => trim_str($cBroaderArea -> name)
                    ];

                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $C_AUDIT_AREA ] -> data_found = false;
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $C_AUDIT_AREA ] -> category = [];

                    // unset var
                    unset($cBroaderArea);
                }

                $pushArray = [

                    // push risk category name
                    'risk_category_details' => [
                        'title' => $extra['common_data']['riskCategory'][ $row -> risk_category_id ] -> risk_category,
                        'qual' => $extra['common_data']['matrixRiskZero'], 'qual_tot' => 0,
                        'quan' => $extra['common_data']['matrixRiskZero'], 'quan_tot' => 0,
                        'total_qual_quan' => 0, 'acc_non_compliant' => 0, 'no_of_acc_checked' => 0,
                        'avg_quan_score' => 0.00, 'tot_avg_score' => 0, 'no_of_audit_conduct' => 0,
                        'avg_tot_score_per_audit' => 0, 'risk_weight' => 0, 'weighted_score' => 0,
                        'total_annex' => 0, /* 'ans' => [] */
                    ]
                ];

                // check and add risk category
                if( !array_key_exists($row -> risk_category_id, $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category) )
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ] = $pushArray;

                // add score
                $cMatrixGenKey = $row -> business_risk . '.' . $row -> control_risk;

                // check matrix exists
                if( $row -> business_risk != 0 && $row -> control_risk != 0 && 
                    array_key_exists($cMatrixGenKey, $extra['common_data']['matrixRisk']) )
                {
                    // push in risk
                    $cAnsRisk = $extra['common_data']['matrixRiskType'][ $cMatrixGenKey ];
                    
                    if(isset($resData[ $C_ASSES_ID ] -> risk_data[ $row -> risk_category_id ]))
                        $resData[ $C_ASSES_ID ] -> risk_data[ $row -> risk_category_id ][ $cAnsRisk ]++;

                    // shift annexure risk to quantitative
                    if( $C_SORT_CAT == 'general' && $extra['ans_type'] == 'annexure' &&
                        $row -> option_id == '4' /*'annexure'*/ && $row -> dump_id == '0' )
                    {
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details'][ 'quan' ][ $cMatrixGenKey ] += $extra['common_data']['matrixRisk'][ $cMatrixGenKey ];
                    }
                    else
                    {                            
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details'][ $dataArray['SORTED_BORADER_AREA_KEYS'][ $C_SORT_CAT ] ][ $cMatrixGenKey ] += $extra['common_data']['matrixRisk'][ $cMatrixGenKey ];
                    }

                    // $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['ans'][] = $C_SORT_CAT;

                    // calculations // helper function call
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['qual_tot'] = CALCULATE_QUAL_QUAN_SCORE_HELPER($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['qual']);

                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['quan_tot'] = CALCULATE_QUAL_QUAN_SCORE_HELPER($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['quan']);

                    // total calculation 
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['total_qual_quan'] = get_decimal( $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['qual_tot'] + $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['quan_tot'], 2 );

                    // add non compliant
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['acc_non_compliant'] += $cComplianceMarkCnt;

                    $isAnnex = true;

                    if( $extra['ans_type'] == 'annexure' ) // increase annex count // 23.10.2024
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['total_annex']++;

                    if( $extra['ans_type'] == 'annexure' && 
                        in_array($C_SORT_CAT, ['general', 'deposits', 'advances']) )
                    {
                        // increase annexure cnt in deposit and loans
                        if( $C_SORT_CAT == 'general' || 
                            $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked'] > 0 )
                        {
                            $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked']++;
                            $isAnnex = false;
                        }
                    }

                    // no of acc checked
                    if($C_SORT_CAT == 'advances' && $isAnnex)
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked'] = $resData[ $C_ASSES_ID ] -> total_advances_sampling;

                    elseif($C_SORT_CAT == 'deposits' && $isAnnex)
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked'] = $resData[ $C_ASSES_ID ] -> total_deposits_sampling;

                    /*elseif($C_SORT_CAT == 'general' && $extra['ans_type'] == 'annexure' && $isAnnex)
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked'] = 1; */

                    // if first time entry added in array but annexure started already
                    if( $extra['ans_type'] == 'annexure' && in_array($C_SORT_CAT, ['deposits', 'advances']) && $isAnnex)
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked']++;

                    // Averaged Quantitative Score	
                    if( $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['quan_tot'] > 0)
                    {
                        $temp_no_of_acc_checked = ($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked'] > 0) ? $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_acc_checked'] : 1;

                        $temp_no_of_acc_checked = ($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['quan_tot'] / $temp_no_of_acc_checked);

                        $temp_no_of_acc_checked = ($temp_no_of_acc_checked > 0) ? get_decimal($temp_no_of_acc_checked, 2) : 0;

                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['avg_quan_score'] = $temp_no_of_acc_checked;
                    }

                    // Total Averaged Score	
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['tot_avg_score'] = get_decimal($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['qual_tot'] + $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['avg_quan_score'], 2);

                    // no of audit conduct
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['no_of_audit_conduct'] = /*sizeof($resData[ $C_ASSES_ID ]['no_of_audits'])*/ 1;

                    // Averaged Total Score Per Audit
                    if($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['tot_avg_score'] > 0)
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['avg_tot_score_per_audit'] = get_decimal($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['tot_avg_score'] / /*sizeof($resData[ $C_ASSES_ID ]['no_of_audits'])*/ 1, 2);

                    // risk weightage
                    if( is_array($extra['common_data']['riskCategory']) && 
                        array_key_exists($row -> risk_category_id, $extra['common_data']['riskCategory']) )
                    {
                        $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['risk_weight'] = ($extra['common_data']['riskCategory'][ $row -> risk_category_id ] -> risk_weightage > 0) ? $extra['common_data']['riskCategory'][ $row -> risk_category_id ] -> risk_weightage : 0;
                    }

                    // Weighted Score
                    $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['weighted_score'] = get_decimal($resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['risk_weight'] * $resData[ $C_ASSES_ID ] -> { $C_SORT_CAT }['borader_area'][ $row -> audit_area ] -> category[ $row -> risk_category_id ]['risk_category_details']['avg_tot_score_per_audit'], 2);
                }

                // push question id in array
                if( $extra['ans_type'] == 'general' && 
                    !in_array($row -> id, $extra['annex_data']) && 
                    $row -> option_id == '4' && intval($row -> answer_given) > 0 )
                    $extra['annex_data'][] = $row -> id;

            }
        }

        endif;
        
        return [ 'res_data' => $resData, 'annex_data' => $extra['annex_data'] ];
    }
}

if(!function_exists('BROADER_AREA_REMOVE_BLANK_HELPER'))
{
    // function remove blank broader area data
    function BROADER_AREA_REMOVE_BLANK_HELPER($dataArray, $extra = [])
    {
        if(is_array($dataArray) && sizeof($dataArray) > 0 && isset($extra['SORTED_BORADER_AREA_KEYS']))
        {
            // loop and remove zero data
            foreach($dataArray as $cAssesId => $cAssesUnitData)
            { 
                // assesment loop       
                foreach($cAssesUnitData as $cCustomKey => $cCustomData)
                { 
                    // report category loop like general, loans
                    if(array_key_exists($cCustomKey, $extra['SORTED_BORADER_AREA_KEYS']))
                    {
                        // loop on broader area keys
                        foreach ($cCustomData['borader_area'] as $cBroaderAreaId => $cBroaderAreaDetails)
                        {                                
                            // loop on diffrent risk category in broader area
                            foreach ($cBroaderAreaDetails -> category as $cRiskId => $cRiskDetails)
                            {
                                if( $cRiskDetails['risk_category_details']['qual_tot'] == 0 && 
                                    $cRiskDetails['risk_category_details']['quan_tot'] == 0 )
                                    unset($dataArray[ $cAssesId ] -> { $cCustomKey }['borader_area'][ $cBroaderAreaId ] -> category[ $cRiskId ]);
                                    
                                elseif( $cRiskDetails['risk_category_details']['weighted_score'] > 0 )
                                {
                                    $dataArray[ $cAssesId ] -> risk_data[ $cRiskId ]['wg_sc'] = get_decimal(($dataArray[ $cAssesId ] -> risk_data[ $cRiskId ]['wg_sc'] + $cRiskDetails['risk_category_details']['weighted_score']), 2);

                                    $dataArray[ $cAssesId ] -> risk_data[ $cRiskId ]['avg_sc'] = get_decimal(($dataArray[ $cAssesId ] -> risk_data[ $cRiskId ]['avg_sc'] + $cRiskDetails['risk_category_details']['tot_avg_score']), 2);

                                    $dataArray[ $cAssesId ] -> total_weighted_score = get_decimal(($dataArray[ $cAssesId ] -> total_weighted_score + $cRiskDetails['risk_category_details']['weighted_score']), 2);
                                }
                            }

                            if(!sizeof($dataArray[ $cAssesId ] -> { $cCustomKey }['borader_area'][$cBroaderAreaId] -> category) > 0)
                                unset($dataArray[ $cAssesId ] -> { $cCustomKey }['borader_area'][ $cBroaderAreaId ]);
                        }

                        if(!sizeof($dataArray[ $cAssesId ] -> { $cCustomKey }['borader_area']) > 0)
                            unset($dataArray[ $cAssesId ] -> { $cCustomKey });
                    }
                }

                // if(!sizeof($dataArray[ $cAssesId ]) > 0)
                //     unset($dataArray[ $cAssesId ]);
            }
        }

        return $dataArray;
    }
}

if(!function_exists('BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER')) {

    function BROADER_AREA_ANS_MIX_AUDIT_UNITS_HELPER($res, $extra = [])
    {
        $resData = [];

        if( is_array($res) && 
            isset($res['res_data']['data']) && 
            is_array($res['res_data']['data']) && 
            sizeof($res['res_data']['data']) > 0 )
        {
            foreach($res['res_data']['data'] as $cAssesId => $cAssesData)
            {
                $cAuditId = $cAssesData -> audit_unit_id;

                if(!array_key_exists($cAuditId, $resData))
                {
                    $resData[ $cAuditId ] = [ 
                        'total_advances_sampling' => 0, 
                        'total_deposits_sampling' => 0,
                        'data_found' => true, 
                        'branch_name' => string_operations(ERROR_VARS['notFound'], 'upper'), 
                        'combined_name' => string_operations(ERROR_VARS['notFound'], 'upper'),
                        'no_of_audits' => [], 
                        'audit_unit_code' => 0
                    ];

                    foreach(array_keys($res['SORTED_BORADER_AREA_KEYS']) as $cKey) {

                        $resData[ $cAuditId ][ $cKey ] = [ 
                            'title' => ucfirst($cKey), 
                            'borader_area' => [] 
                        ];
                    }

                    if( isset($extra['audit_unit_data']) && 
                        is_array($extra['audit_unit_data']) && 
                        array_key_exists($cAuditId, $extra['audit_unit_data']))
                    {
                        $resData[ $cAuditId ]['audit_unit_code'] = $extra['audit_unit_data'][ $cAuditId ] -> audit_unit_code;
                        $resData[ $cAuditId ]['branch_name'] = string_operations($extra['audit_unit_data'][ $cAuditId ] -> name, 'upper');
                        $resData[ $cAuditId ]['combined_name'] = string_operations($extra['audit_unit_data'][ $cAuditId ] -> combined_name, 'upper');
                    }
                    elseif(
                        isset($extra['ho_audit_unit_data']) && 
                        is_array($extra['ho_audit_unit_data']) && 
                        array_key_exists($cAuditId, $extra['ho_audit_unit_data'])
                    )
                    {
                        $resData[ $cAuditId ]['audit_unit_code'] = $extra['ho_audit_unit_data'][ $cAuditId ] -> audit_unit_code;
                        $resData[ $cAuditId ]['branch_name'] = string_operations($extra['ho_audit_unit_data'][ $cAuditId ] -> name, 'upper');
                        $resData[ $cAuditId ]['combined_name'] = string_operations($extra['ho_audit_unit_data'][ $cAuditId ] -> combined_name, 'upper');
                    }
                }
                
                if(!in_array($cAssesId, $resData[ $cAuditId ]['no_of_audits']))
                    $resData[ $cAuditId ]['no_of_audits'][ ] = $cAssesId;

                // increase dump count
                $resData[ $cAuditId ]['total_advances_sampling'] += $cAssesData -> total_advances_sampling;
                $resData[ $cAuditId ]['total_deposits_sampling'] += $cAssesData -> total_deposits_sampling;

                // merge data here... // loop on sort categories
                foreach(array_keys($res['SORTED_BORADER_AREA_KEYS']) as $cKey)
                {
                    if( isset($cAssesData -> { $cKey }) && 
                        isset($cAssesData -> { $cKey }['borader_area']) &&
                        is_array($cAssesData -> { $cKey }['borader_area']) &&
                        sizeof($cAssesData -> { $cKey }['borader_area']) > 0 )
                    {
                        // broader area report loop
                        foreach($cAssesData -> { $cKey }['borader_area'] as $cBAId => $cBAData)
                        {
                            // check category
                            if( is_array($cBAData -> category) && 
                                sizeof($cBAData -> category) > 0 )
                            {
                                if(!array_key_exists($cBAId, $resData[ $cAuditId ][ $cKey ]['borader_area']))
                                    $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ] = [
                                        'id' => $cBAData -> id,
                                        'name' =>  $cBAData -> name,
                                        'data_found' =>  false,
                                        'category' => []
                                    ];

                                // loop on category
                                foreach($cBAData -> category as $cRiskId => $cRiskData)
                                {       
                                    if(!array_key_exists($cRiskId, $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category']))
                                    {
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ] = $cRiskData['risk_category_details'];
                                    }
                                    else
                                    {
                                        // addition data // next loop data
                                        foreach($cRiskData['risk_category_details'] as $ccKey => $ccData)
                                        {
                                            if(!in_array($ccKey, [
                                                'title', 
                                                'risk_weight', 'avg_tot_score_per_audit', 'weighted_score',
                                                'no_of_acc_checked', 'total_annex'
                                            ]))
                                            {
                                                // qual, quan array loop
                                                if(in_array($ccKey, ['qual', 'quan']))
                                                {
                                                    foreach($ccData as $cMatrixKey => $cMatrixVal) {
                                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ $ccKey ][ $cMatrixKey ] += $cMatrixVal;
                                                    }
                                                }
                                                else
                                                {
                                                    // regular keys
                                                    $cVal = $ccData;

                                                    if(!in_array($ccKey, ['acc_non_compliant', 'avg_quan_score', 'no_of_audit_conduct']))
                                                        $cVal = get_decimal($cVal, 2);

                                                    // addition
                                                    $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ $ccKey ] += $cVal;
                                                }
                                            }
                                        }
                                    }

                                    // addition of total annex and dump total 23.10.2024
                                    if(!isset($resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ 'first_attempt' ]))
                                    {
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ 'first_attempt' ] = true;
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ 'no_of_acc_checked' ] = 0;

                                        if( in_array($cKey, ['deposits', 'advances']) )
                                            $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ 'no_of_acc_checked' ] = $res['res_data']['dump_data_count'][ $cAuditId ]['total_'. $cKey .'_sampling'];
                                    }

                                    /* if(in_array($cKey, ['deposits', 'advances']))
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ 'no_of_acc_checked' ] += ( $cRiskData['risk_category_details']['total_annex'] + $resData[ $cAuditId ]['total_'. $cKey .'_sampling'] );
                                    else */
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ][ 'no_of_acc_checked' ] += ( $cRiskData['risk_category_details']['total_annex'] );

                                    // re calculation
                                    if( $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['quan_tot'] > 0)
                                    {
                                        $temp_no_of_acc_checked = ($resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['no_of_acc_checked'] > 0) ? $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['no_of_acc_checked'] : 1;

                                        $temp_no_of_acc_checked = ($resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['quan_tot'] / $temp_no_of_acc_checked);

                                        $temp_no_of_acc_checked = ($temp_no_of_acc_checked > 0) ? get_decimal($temp_no_of_acc_checked, 2) : 0;

                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['avg_quan_score'] = $temp_no_of_acc_checked;
                                    }

                                    // total Averaged Score	
                                    $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['tot_avg_score'] = get_decimal(
                                        (   $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['qual_tot'] + 
                                            $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['avg_quan_score']), 2);

                                    // no of audits count
                                    if(array_key_exists($cAuditId, $res['no_of_audits']))
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['no_of_audit_conduct'] = sizeof($res['no_of_audits'][ $cAuditId ]);
                                    else
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['no_of_audit_conduct'] = 1;

                                    // Averaged Total Score Per Audit // avg_tot_score_per_audit calculation
                                    if( $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['tot_avg_score'] > 0)
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['avg_tot_score_per_audit'] = get_decimal(
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['tot_avg_score'] / 
                                        $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['no_of_audit_conduct'], 2);

                                    // Weighted Score // weighted_score calculation
                                    $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['weighted_score'] = get_decimal(
                                    $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['risk_weight'] * 
                                    $resData[ $cAuditId ][ $cKey ]['borader_area'][ $cBAId ]['category'][ $cRiskId ]['avg_tot_score_per_audit'], 2);
                                }
                            }
                        }
                    }
                }
            }
            
            // print_r($resData);
            // exit;
        }

        return $resData;
    }
}

if(!function_exists('BROADER_AREA_QUESTIONS_ANS_HELPER')) {

    function BROADER_AREA_QUESTIONS_ANS_HELPER($thisObj, $extra)
    {
        $dataArray = [];

        // Define categories
        $dataArray['SORTED_BORADER_AREA_KEYS'] = [ 'general' => 'qual', 'deposits' => 'quan', 'advances' => 'quan' ];
        $extra['no_of_audits'] = [];
        $extra['res_data']['dump_data_count'] = [];

        // TOTAL ASSESMENT WISE DEPOSITE SAMPLING
        $model = $thisObj -> model('DumpDepositeModel');
        $dbTable = $model -> getTableName();

        $filter = [
            'where' => 'sampling_filter = 1 AND assesment_period_id IN (' . implode(',', array_keys($extra['res_data']['data'])) . ') GROUP BY assesment_period_id',
            'params' => []
        ];

        $dataArray['deposits_data'] = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT assesment_period_id, COUNT(*) AS acc FROM " . $dbTable);
        $dataArray['deposits_data'] = generate_data_assoc_array($dataArray['deposits_data'], 'assesment_period_id');

        // TOTAL ASSESMENT WISE ADVANCES SAMPLING
        $model = $thisObj -> model('DumpAdvancesModel');
        $dbTable = $model -> getTableName();

        $dataArray['advances_data'] = get_all_data_query_builder(2, $model, $dbTable, $filter, 'sql', "SELECT assesment_period_id, COUNT(*) AS acc FROM " . $dbTable);
        $dataArray['advances_data'] = generate_data_assoc_array($dataArray['advances_data'], 'assesment_period_id');

        // print_r($extra['common_data']['riskCategory']);
        foreach ($extra['res_data']['data'] as $cAssId => $cAssDetails) {

            // push key
            if(!array_key_exists($cAssDetails -> audit_unit_id, $extra['res_data']['dump_data_count']))
                $extra['res_data']['dump_data_count'][ $cAssDetails -> audit_unit_id ] = [
                    'total_deposits_sampling' => 0, 'total_advances_sampling' => 0
                ];

            // Initialize categories
            foreach ($dataArray['SORTED_BORADER_AREA_KEYS'] as $key => $value) {
                $cAssDetails -> $key = [ 'title' => ucfirst($key), 'borader_area' => [] ];
            }

            $cAssDetails -> risk_data = $extra['common_data']['riskCategoryStore'];
            $cAssDetails -> total_weighted_score = 0;

            // TOTAL DEPOSITS & ADVANCES
            $cAssDetails -> total_deposits_sampling = (isset($dataArray['deposits_data'][ $cAssId ]) && is_object($dataArray['deposits_data'][ $cAssId ])) ? $dataArray['deposits_data'][ $cAssId ] -> acc : 0;
            $extra['res_data']['dump_data_count'][ $cAssDetails -> audit_unit_id ]['total_deposits_sampling'] += $cAssDetails -> total_deposits_sampling;

            $cAssDetails -> total_advances_sampling = (isset($dataArray['advances_data'][ $cAssId ]) && is_object($dataArray['advances_data'][ $cAssId ])) ? $dataArray['advances_data'][ $cAssId ] -> acc : 0;
            $extra['res_data']['dump_data_count'][ $cAssDetails -> audit_unit_id ]['total_advances_sampling'] += $cAssDetails -> total_advances_sampling;

            if (!isset($extra['no_of_audits'][ $cAssDetails -> audit_unit_id ]))
                $extra['no_of_audits'][ $cAssDetails -> audit_unit_id ] = [];

            if (!in_array($cAssId, $extra['no_of_audits'][$cAssDetails->audit_unit_id]))
                $extra['no_of_audits'][ $cAssDetails -> audit_unit_id ][] = $cAssId;

            // Update data
            $extra['res_data']['data'][ $cAssId ] = $cAssDetails;
        }

        // Unset vars
        unset($dataArray['deposits_data'], $dataArray['advances_data']);

        // FIND ANSWERS DATA
        $model = $thisObj -> model('AnswerDataModel');

        $select = "SELECT ans.id, ans.category_id, ans.dump_id, ans.business_risk, ans.control_risk, ans.question_id, ans.is_compliance, ans.assesment_id, ans.answer_given, qm.risk_category_id, qm.option_id, qm.area_of_audit_id audit_area, qm.question FROM answers_data ans JOIN question_master qm ON ans.question_id = qm.id WHERE ans.assesment_id IN (" . implode(',', array_keys($extra['res_data']['data'])) . ") AND (ans.business_risk IN (1,2,3) OR ans.control_risk IN (1,2,3) OR qm.option_id = 4)";
        
        $ansDataArray = get_all_data_query_builder(2, $model, 'answers_data', [], 'sql', $select);

        // print_r($ansDataArray);
        // exit;

        // Function call for GENERAL
        $res = BROADER_AREA_RISK_WISE_ANS_SORT_HELPER(
            $dataArray, 
            $extra['res_data']['data'], 
            $ansDataArray, [
                'common_data' => $extra['common_data']
        ]);

        $extra['res_data']['data'] = $res['res_data'];

        if (isset($res['annex_data']) && 
            sizeof($res['annex_data']) > 0)
        {
            // Find annexure data
            $select = "SELECT ax.id, ax.answer_id, ax.business_risk, ax.control_risk, ax.risk_cat_id risk_category_id, ax.audit_commpliance is_compliance, ax.assesment_id, ans.category_id, ans.dump_id, ans.question_id, ans.answer_given, qm.option_id, qm.area_of_audit_id audit_area FROM answers_data_annexure ax JOIN answers_data ans ON ax.answer_id = ans.id JOIN question_master qm ON ans.question_id = qm.id WHERE qm.option_id = 4 AND ax.answer_id IN (" . implode(',', $res['annex_data']) . ") AND ax.assesment_id IN (" . implode(',', array_keys($extra['res_data']['data'])) . ") AND (ax.business_risk IN (1,2,3) OR ax.control_risk IN (1,2,3))";

            $annexureAnsDataArray = get_all_data_query_builder(2, $model, 'answers_data_annexure', [], 'sql', $select);

            // Function call for ANNEXURE
            $res = BROADER_AREA_RISK_WISE_ANS_SORT_HELPER(
                $dataArray, 
                $extra['res_data']['data'], 
                $annexureAnsDataArray, [
                    'common_data' => $extra['common_data'],
                    'ans_type' => 'annexure'
                ]);
        }

        // Unset vars
        unset($res);

        // Function call for removing blank broader areas
        $extra['res_data']['data'] = BROADER_AREA_REMOVE_BLANK_HELPER(
            $extra['res_data']['data'], [
                'SORTED_BORADER_AREA_KEYS' => $dataArray['SORTED_BORADER_AREA_KEYS']
            ]);

        // print_r($extra['res_data']['data']);
        // exit;

        return [
            'data_array' => $dataArray,
            'no_of_audits' => $extra['no_of_audits'],
            'res_data' => $extra['res_data'],
            'SORTED_BORADER_AREA_KEYS' => $dataArray['SORTED_BORADER_AREA_KEYS']
        ];
    }
}

if(!function_exists('BROADER_AREA_STORE_SUMMARY_HELPER')) {

    // broader area report store in db
    function BROADER_AREA_STORE_SUMMARY_HELPER($thisObj, $dataArray = [], $force = 0, $extra = [])
    {
        $yearModel = $thisObj -> model('YearModel');
        $response = [ 'err' => true, 'msg' => 'somethingWrong' ];

        if(empty($dataArray))
        {
            // get first - year data
            $filterArray = [ 'where' => 'deleted_at IS NULL', 'params' => [] ];
            $fy = null;

            if(isset($extra['date']) && !empty($extra['date']))
                $fy = getFYOnDate($extra['date']);

            if(!empty($fy))
            {
                $filterArray['where'] .= " AND year = :year";
                $filterArray['params']['year'] = $fy;
            }

            $filterArray['where'] .= " ORDER BY id DESC";

            $extra['year'] = get_all_data_query_builder(1, $yearModel, $yearModel -> getTableName(), $filterArray, 'sql', "SELECT id, year FROM " . $yearModel -> getTableName());
            
            if(is_object($extra['year']))
            {
                $DBCommonFunc = new Core\DBCommonFunc();
                $model = $thisObj -> model('AuditAssesmentModel');

                $filterArray = [
                    'where' => 'audit_status_id > "'. ASSESMENT_TIMELINE_ARRAY[3]['status_id'] .'" AND year_id = :year_id AND deleted_at IS NULL',
                    'params' => [ 'year_id' => $extra['year'] -> id ]
                ];

                if(isset($extra['audit_unit_id']) && !empty($extra['audit_unit_id']))
                {
                    // for single audit unit
                    $filterArray['where'] .= ' AND audit_unit_id = :audit_unit_id';
                    $filterArray['params']['audit_unit_id'] = $extra['audit_unit_id'];
                }

                $filterArray['where'] .= ' ORDER BY audit_unit_id ASC';

                $dataArray = $DBCommonFunc::getAllAuditAssesment($model, $filterArray, 'id, year_id, audit_type_id, audit_unit_id, assesment_period_from, assesment_period_to, audit_status_id, audit_start_date, audit_end_date, updated_at');
            }
        }

        if(is_array($dataArray) && sizeof($dataArray) > 0)
        {
            // function call
            $dataArray = generate_data_assoc_array($dataArray, 'id');

            if(!isset($extra['year']) || !is_object($extra['year']))
            {
                $fyId = $dataArray[ array_keys($dataArray)[0] ] -> year_id;
                $extra['year'] = get_all_data_query_builder(1, $yearModel, $yearModel -> getTableName(), [
                    'where' => 'id = :id AND deleted_at IS NULL', 'params' => [ 'id' => $fyId ]
                ], 'sql', "SELECT id, year FROM " . $yearModel -> getTableName());
            }

            if(is_object($extra['year']))
            {
                // year found // check for report_scoring_master
                $model = $thisObj -> model('ReportScoringMasterModel');

                $reportData = $model -> getAllReportScore([
                    'where' => 'year = :year AND assesment_id IN ('. implode(',', array_keys($dataArray)) .') AND deleted_at IS NULL',
                    'params' => [ 'year' => $extra['year'] -> year ]
                ]);

                if(is_array($reportData) && sizeof($reportData) > 0)
                {   
                    $temp = [];

                    foreach($reportData as $cReportData)
                    {
                        $cAssData = $dataArray[ $cReportData -> assesment_id ];
                       
                        if( !$force && $cReportData -> audit_start_date == $cAssData -> audit_start_date && 
                            $cReportData -> audit_end_date == $cAssData -> audit_end_date && 
                            $cReportData -> last_updated_at == $cAssData -> updated_at)
                            unset($dataArray[ $cAssData -> id ]);

                        // push in array
                        $temp[ $cReportData -> assesment_id ] = $cReportData;
                    }

                    $reportData = $temp;
                    unset($temp);
                }

                // check again because we unset data from top
                if(sizeof($dataArray) > 0)
                {
                    // method call // helper function call
                    $commonResData = BROADER_AREA_COMMON_DATA_HELPER($thisObj, $extra['year'] -> id);

                    if(empty($commonResData['err']))
                    {
                        $res = BROADER_AREA_QUESTIONS_ANS_HELPER($thisObj, [ 
                            'res_data' => [ 'data' => $dataArray ], 
                            'common_data' => $commonResData 
                        ]);
                        
                        if(isset($res['res_data']['data']) && sizeof($res['res_data']['data']) > 0)
                        {
                            $extra['insert'] = []; $extra['update'] = []; $extra['where'] = []; 

                            foreach($res['res_data']['data'] as $cAssId => $cAssData)
                            {
                                $cDataArray = [
                                    "year" => $extra['year'] -> year,
                                    "assesment_id" => $cAssData -> id,
                                    "audit_type_id" => $cAssData -> audit_type_id,
                                    "audit_unit_id" => $cAssData -> audit_unit_id,
                                    "assesment_period_from" => $cAssData -> assesment_period_from,
                                    "assesment_period_to" => $cAssData -> assesment_period_to,
                                    "audit_status_id" => $cAssData -> audit_status_id,
                                    "audit_start_date" => $cAssData -> audit_start_date,
                                    "audit_end_date" => $cAssData -> audit_end_date,
                                    "risk_data" => json_encode($cAssData -> risk_data),
                                    "weighted_score" => get_decimal($cAssData -> total_weighted_score, 2),
                                    "advances_sampling" => $cAssData -> total_advances_sampling,
                                    "deposits_sampling" => $cAssData -> total_deposits_sampling,
                                    "last_updated_at" => $cAssData -> updated_at,
                                ];

                                if( is_array($reportData) && array_key_exists($cAssData -> id, $reportData) && 
                                    $reportData[ $cAssData -> id ] -> year == $cDataArray['year'] )
                                {
                                    // update data
                                    $extra['update'][] = $cDataArray;
                                    $extra['where'][] = [
                                        'where' => 'assesment_id = :assesment_id AND year = :year',
                                        'params' => [ 'assesment_id' => $cAssData -> id, 'year' => $cDataArray['year'] ]
                                    ];
                                }
                                else // add data
                                    $extra['insert'][] = $cDataArray;
                            }

                            // print_r($extra);
                            // exit;

                            $err = 0;

                            if(is_array($extra['insert']) && sizeof($extra['insert']) > 0)
                            {
                                // add multi
                                $result = $model::insertMultiple($model -> getTableName(), $extra['insert']);
                                if(!$result) $err++;
                            }

                            if(is_array($extra['insert']) && sizeof($extra['update']) > 0 && sizeof($extra['update']) == sizeof($extra['where']))
                            {
                                // update multi
                                $result = $model::updateMultiple($model -> getTableName(), $extra['update'], $extra['where']);
                                if(!$result) $err++;
                            }

                            if(!($err > 0)) { $response['err'] = false; $response['msg'] = null; }
                        }

                        // unset var
                        unset($res);
                    }
                }
            }
        }
        
        // unset var
        unset($dataArray, $reportData, $commonResData, $extra);

        // print_r($res);
        return $response;
    }
}

?>