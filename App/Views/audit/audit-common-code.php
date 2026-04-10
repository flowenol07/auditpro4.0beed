<?php

use Core\FormElements;

if(!function_exists("generate_question_select_options"))
{
    function generate_question_select_options($data, $cQuesDetails, $selectedVal = null, $needSelect = true, $needBlank = true)
    {
        $returnMarkup = '';

        try
        {
            $parameters = null;

            if(!empty($cQuesDetails -> parameters))
                $parameters = json_decode($cQuesDetails -> parameters);

            if(is_array($parameters) && sizeof($parameters) > 0)
            {
                $appendClass = '';

                if($cQuesDetails -> option_id == 4)
                    $appendClass = 'annex-type-select';

                if($needSelect)
                    $returnMarkup .= '<select class="form-control form-select mb-2 '. $appendClass .'" data-ques="'. $cQuesDetails -> id .'">';
                
                if($needBlank)
                    $returnMarkup .= '<option value="">-- Select Answer --</option>';

                // for default option
                $optionsArray = [];
                $defaultOptionIndex = 0;
                $defaultOptionVal = 0;
                $oi = 0;
                $notApplicableDef = 0;

                $totRiskControl = 0;
                $prevTotRiskControl = 0;
                $noRiskQuestion = [ 'status' => true, 'option_index' => [], 'checkYesNo' => false ];
                
                foreach ($parameters as $cIndex => $optionDetails) 
                {
                    $optionDetails -> br = get_decimal($optionDetails -> br, 0);
                    $optionDetails -> cr = get_decimal($optionDetails -> cr, 0);

                    $CRP = $optionDetails -> br + $optionDetails -> cr;
                    $totRiskControl = $CRP;

                    // check options has yes no 24.12.2024
                    if( in_array(string_operations($optionDetails -> rt), ['yes', 'no']) && 
                        in_array($CRP, ['0.0', '4.4', '0', '8']))
                    {
                        $noRiskQuestion['checkYesNo'] = true;
                        $defaultOptionIndex = $oi;
                    }

                    if(!$noRiskQuestion['checkYesNo']) // update kunal 24.12.2024 as per omkar sir discussion final
                    {
                        // check no risk ans
                        if(!in_array($CRP, ['0.0', '4.4', '0', '8']))
                            $noRiskQuestion[ 'status' ] = false;

                        if($prevTotRiskControl < $totRiskControl)
                            $defaultOptionIndex = $oi;

                        //  assign risk
                        $prevTotRiskControl = $totRiskControl;

                        // if ( $CRP > $defaultOptionVal && ($CRP > 3 || $CRP < 1) )
                        // {
                        //     $defaultOptionIndex = $oi;
                        //     $defaultOptionVal = $CRP;
                        // }

                        // for not applicable 12.07.2024
                        if( string_operations('NOT APPLICABLE') == string_operations($optionDetails -> rt) && 
                            in_array($CRP, ['0.0', '4.4', '0', '8']) )
                        {
                            $notApplicableDef = $oi;
                            $noRiskQuestion[ 'status' ] = false;
                        }

                        // if( in_array($CRP, ['0.0', '4.4', '0', '8']) )
                            $noRiskQuestion[ 'option_index' ][] = $CRP;

                        // $parameters[ $cIndex ] -> rt .= $CRP;
                    }

                    $oi++;
                }

                $oi = 0;

                // no risk options
                if(!$noRiskQuestion['checkYesNo'] && $noRiskQuestion[ 'status' ] && sizeof($noRiskQuestion[ 'option_index' ]) > 0)
                {
                    $firstOi = $noRiskQuestion[ 'option_index' ][0];
                    $checkOi = true;

                    foreach ($noRiskQuestion[ 'option_index' ] as $oiVal) {
                        if ($oiVal !== $firstOi) {
                            $checkOi = false;
                            break;
                        }
                    }

                    if( $checkOi )
                        $defaultOptionIndex = sizeof($noRiskQuestion[ 'option_index' ]) - 1;
                }

                // new update kunal 08.10.2024
                if( $selectedVal == 'default_' && isset($parameters[ $defaultOptionIndex ]) )
                    $selectedVal = $parameters[ $defaultOptionIndex ] -> rt;

                // not applicable reassign 12.07.2024
                // if(!empty($notApplicableDef)) $defaultOptionIndex = $notApplicableDef;

                // re audit update 27.07.2024 Kunal
                if($cQuesDetails -> option_id != 4 || 
                  ($cQuesDetails -> option_id == 4 /*&& !check_re_assesment_status($data['data']['db_assesment_data'])*/))
                {
                    foreach ($parameters as $optionDetails) 
                    {
                        $returnMarkup .= '<option value="'. $optionDetails -> rt .'"'. ( ( $selectedVal != '' && trim_str($selectedVal) == $optionDetails -> rt ) ? ' selected="selected"' : '' ) . ( ($defaultOptionIndex == $oi) ? ' data-def="1"' : '' ) .'>'. string_operations($optionDetails -> rt, 'upper') /*. $defaultOptionVal*/ .'</option>';

                        $oi++;
                    }
                }

                if($cQuesDetails -> option_id == 4) // As per annexure
                    $returnMarkup .= '<option value="'. $cQuesDetails -> annexure_id .'"'. ( ( $selectedVal != '' && trim_str($selectedVal) == $cQuesDetails -> annexure_id ) ? ' selected="selected"' : '' ) .'>'. string_operations('As per annexure', 'upper') .'</option>';

                if($needSelect)
                    $returnMarkup .= '</select>';
            }
            
        } catch (Exception $th) { }

        return $returnMarkup;
    }
}

if(!function_exists("generate_annex_ans_markup"))
{
    function generate_annex_ans_markup($data, $cQuesDetails, $cAnsObj = null)
    {
        $returnData = ['header' => '', 'blank_row' => '', 'ans_rows' => ''];

        $returnData['blank_row'] = '<tr>' . "\n";

        $returnData['header'] .= '<tr>' . "\n";

        $objIndex = 0;

        $cJsonData = null;

        // function call
        $activateEvidence = (check_evidence_upload_strict() && ($cQuesDetails -> audit_ev_upload == 1 || (isset($cAnsObj -> audit_compulsary_ev_upload) && $cAnsObj -> audit_compulsary_ev_upload == 1 ? 1 : 0) ));

        if(!empty($cAnsObj -> answer_given))
        {
            // decode json data
            try { $cJsonData = json_decode($cAnsObj -> answer_given); } catch (Exception $e) { }
        }

        foreach($cQuesDetails -> annexure_id_details -> annex_cols as $CAnnexColId => $CAnnexColDetails):

            $returnData['header'] .= '<th>'. $CAnnexColDetails -> name .'</th>' . "\n";

            $cAnsSelectedVal = null;

            if(is_object($cJsonData) && isset($cJsonData -> $objIndex))
                $cAnsSelectedVal = $cJsonData -> $objIndex;

            // '1' => 'TextBox'
            if($CAnnexColDetails -> column_type_id == 1)
                $returnData['blank_row'] .= '<td><input class="form-control" type="text"'. (($cAnsSelectedVal != '') ? ' value="'. $cAnsSelectedVal .'"' : '') .' /></td>' . "\n";

            // '2' => 'TextArea'
            elseif($CAnnexColDetails -> column_type_id == 2)
                $returnData['blank_row'] .= '<td><textarea class="form-control">'. (($cAnsSelectedVal != '') ? $cAnsSelectedVal : '') .'</textarea></td>' . "\n";

            // '3' => 'Dropdown'
            elseif($CAnnexColDetails -> column_type_id == 3)
            {
                $returnData['blank_row'] .= '<td width="300">' . "\n";
                    $returnData['blank_row'] .= '<select class="form-control form-select" style="width:300px">
                        <option value="">-- Select Answer --</option>' . "\n";

                        try
                        {
                            $selectOptions = json_decode( $CAnnexColDetails -> column_options );

                            if(is_array($selectOptions) && sizeof($selectOptions) > 0)
                            {
                                foreach($selectOptions as $cOptionDetails)
                                {
                                    $returnData['blank_row'] .= '<option value="'. trim_str($cOptionDetails -> column_option) .'"'. (($cAnsSelectedVal != '' && trim_str($cOptionDetails -> column_option) == trim_str($cAnsSelectedVal)) ? ' selected="selected"' : '') .'>'. trim_str($cOptionDetails -> column_option) .'</option>';     
                                }
                            }
                        } 
                        catch (Exception $th) { }

                    $returnData['blank_row'] .= '</select>' . "\n";
                $returnData['blank_row'] .= '</td>' . "\n";
            }

            // no options
            else
                $returnData['blank_row'] .= '<td>-</td>' . "\n";

            $objIndex++;

        endforeach;

        $cBusinessRiskStr = '';
        $cControlRiskStr = '';
        $cRiskTypeStr = '';
            
        // risk select
        if ($cQuesDetails -> annexure_id_details -> risk_defination_id == 1)
        {
            // for multiple select options

            // business risk
            $cBusinessRiskStr = '';

            if( is_array($data['data']['db_business_risk_matrix']) && 
                sizeof($data['data']['db_business_risk_matrix']) > 0 )
            {
                foreach($data['data']['db_business_risk_matrix'] as $cRiskMatrix => $cRiskMatrixDetails)
                    $cBusinessRiskStr .= '<option value="'. $cRiskMatrixDetails -> risk_parameter .'"'. (( is_object($cAnsObj) && $cAnsObj -> business_risk == $cRiskMatrixDetails -> risk_parameter ) ? ' selected="selected"' : '') .'>'. string_operations( (RISK_PARAMETERS_ARRAY[ $cRiskMatrixDetails -> risk_parameter ]['title'] ?? ERROR_VARS['notFound']), 'upper' ) .'</option>' . "\n";
            }

            // control risk
            $cControlRiskStr = '';

            if( is_array($data['data']['db_control_risk_matrix']) && 
                sizeof($data['data']['db_control_risk_matrix']) > 0 )
            {
                foreach($data['data']['db_control_risk_matrix'] as $cRiskMatrix => $cRiskMatrixDetails)
                    $cControlRiskStr .= '<option value="'. $cRiskMatrixDetails -> risk_parameter .'"'. (( is_object($cAnsObj) && $cAnsObj -> control_risk == $cRiskMatrixDetails -> risk_parameter ) ? ' selected="selected"' : '') .'>'. string_operations( (RISK_PARAMETERS_ARRAY[ $cRiskMatrixDetails -> risk_parameter ]['title'] ?? ERROR_VARS['notFound']), 'upper' ) .'</option>' . "\n";
            }

            // Risk Type
            $cRiskTypeStr = '';

            if( is_array($data['data']['db_risk_category']) && 
                sizeof($data['data']['db_risk_category']) > 0 )
            {
                foreach($data['data']['db_risk_category'] as $cRiskCat => $cRiskCatDetails)
                    $cRiskTypeStr .= '<option value="'. $cRiskCatDetails -> id .'"'. (( is_object($cAnsObj) && $cAnsObj -> risk_cat_id == $cRiskCatDetails -> id ) ? ' selected="selected"' : '') .'>'. string_operations( $cRiskCatDetails -> risk_category, 'upper' ) .'</option>' . "\n";
            }
        }
        else
        {
            // single option select with high risk
            
            // business risk
            $cBusinessRiskStr = '<option value="'. RISK_PARAMETERS_ARRAY[1]['id'] .'"'. (( is_object($cAnsObj) && $cAnsObj -> business_risk == RISK_PARAMETERS_ARRAY[4]['id'] ) ? ' selected="selected"' : '') .'>'. string_operations( RISK_PARAMETERS_ARRAY[4]['title'], 'upper' ) .'</option>' . "\n";

            // control risk
            $cControlRiskStr = '<option value="'. RISK_PARAMETERS_ARRAY[4]['id'] .'"'. (( is_object($cAnsObj) && $cAnsObj -> control_risk == RISK_PARAMETERS_ARRAY[4]['id'] ) ? ' selected="selected"' : '') .'>'. string_operations( RISK_PARAMETERS_ARRAY[4]['title'], 'upper' ) .'</option>' . "\n";

            // 19.09.2024 // kunal update
            $dbRiskCategory = (is_array($data['data']['db_risk_category']) && array_key_exists(1, $data['data']['db_risk_category'])) ? $data['data']['db_risk_category'][ 4 ] : null;

            // Risk Type
            $cRiskTypeStr = '<option value="'. (is_object($dbRiskCategory) ? $dbRiskCategory -> id : '') .'"'. (( is_object($dbRiskCategory) && is_object($cAnsObj) && $cAnsObj -> risk_cat_id == $dbRiskCategory -> id ) ? ' selected="selected"' : '') .'>'. (is_object($dbRiskCategory) ? $dbRiskCategory -> risk_category : ERROR_VARS['notFound']) .'</option>' . "\n";
        }

        // business risk
        $returnData['blank_row'] .= '<td>
            <select class="form-control form-select br">';

            if ($cQuesDetails -> annexure_id_details -> risk_defination_id == 1)
                $returnData['blank_row'] .= '<option value="">-- Select Answer --</option>';
        
            $returnData['blank_row'] .= $cBusinessRiskStr .'
            </select>
        </td>' . "\n";

        // control risk
        $returnData['blank_row'] .= '<td>
            <select class="form-control form-select cr">';

            if ($cQuesDetails -> annexure_id_details -> risk_defination_id == 1)
                $returnData['blank_row'] .= '<option value="">-- Select Answer --</option>';

            $returnData['blank_row'] .= $cControlRiskStr .'
            </select>
        </td>' . "\n";

        // risk Type
        $returnData['blank_row'] .= '<td>
            <select class="form-control form-select rt">';

            if ($cQuesDetails -> annexure_id_details -> risk_defination_id == 1)
                $returnData['blank_row'] .= '<option value="">-- Select Answer --</option>';

            $returnData['blank_row'] .= $cRiskTypeStr .'
            </select>' . "\n";

            if( check_evidence_upload_strict() )
                $returnData['blank_row'] .= '<label class="compliance-evidence-checkbox font-sm font-medium d-block mt-2"><input class="compliance-evidence-chckbox" type="checkbox" value="2" '. ((is_object($cAnsObj) && $cAnsObj -> compliance_compulsary_ev_upload == 2) ? ' checked="checked"' : '') .' data-ansid="'. (is_object($cAnsObj) ? encrypt_ex_data($cAnsObj -> answer_id) : 0) .'" data-annexid="'. (is_object($cAnsObj) ? encrypt_ex_data($cAnsObj -> id) : 0) .'" data-ajxurl="'. (EVIDENCE_UPLOAD['control_url'] . 'annex-upload-status/') .'" /> '. EVIDENCE_UPLOAD['checkbox_text'] .'</label>' . "\n";

        $returnData['blank_row'] .= '</td>' . "\n";

        // close single row
        if( !is_object($cAnsObj) )
        {
            $returnData['blank_row'] .= '<td colspan="2"><button class="btn btn-light border annex-add-row w-100">Add</button></td>' . "\n";

            if( $activateEvidence )
                $returnData['blank_row'] .= '<td>-</td>' . "\n";
        }
        else
        {
            $removeAnnexBtn = '<td><button class="btn btn-danger annex-row-remove w-100" data-annexid="'. encrypt_ex_data($cAnsObj -> id) .'">Remove</button></td>' . "\n";

            if(check_re_assesment_status($data['data']['db_assesment_data']))
                $removeAnnexBtn = null;

            $returnData['blank_row'] .= '<td'. (empty($removeAnnexBtn) ? ' colspan="2"' : '') .'>
                <button class="btn btn-success annex-row-update w-100 mb-1" data-annexid="'. encrypt_ex_data($cAnsObj -> id) .'">Update</button>
            </td>' . "\n";

            // remove btn add
            $returnData['blank_row'] .= $removeAnnexBtn;
            unset($removeAnnexBtn);

            $activatEviBtn = true;

            if( $activateEvidence )
            {
                $annexEviMarkup = '';
                
                if(is_object($cAnsObj) && isset($cAnsObj -> audit_evidence))
                {
                    $activatEviBtn = false;

                    // function call
                    $annexEviMarkup .= display_evidence_markup($cAnsObj);
                }
                
                $returnData['blank_row'] .= '<td>
                    <button class="btn btn-secondary annex-evidence-upload-btn compliance-evi-btn" '. (!$activatEviBtn ? 'style="display:none"' : '') .''. view_tooltip('Upload File') .'>Evidence</button>
                    <div class="annex-evidence-upload-container" data-annexid="'. encrypt_ex_data($cAnsObj -> id) .'" data-ansid="'. encrypt_ex_data($cAnsObj -> answer_id) .'">'. $annexEviMarkup .'</div>
                </td>' . "\n";
            }
        }

        $returnData['blank_row'] .= '</tr>' . "\n";

        // default cols add
        $returnData['header'] .= '<th width="160">Business Risk</th>' . "\n";
        $returnData['header'] .= '<th width="160">Control Risk</th>' . "\n";
        $returnData['header'] .= '<th width="160">Risk Type</th>' . "\n";
        $returnData['header'] .= '<th colspan="2">Action</th>' . "\n";

        if( $activateEvidence )
            $returnData['header'] .= '<th>Evidence</th>' . "\n";    

        $returnData['header'] .= '</tr>' . "\n";

        // return ans
        if( is_object($cAnsObj) )
            return $returnData['blank_row'];

        if(check_re_assesment_status($data['data']['db_assesment_data']))
            $returnData['blank_row'] = null;
        
        return $returnData;
    }
}

if(!function_exists("generate_question_set_markup"))
{
    function generate_question_set_markup($data, $cSetDetails, $subset = false, $subsetContainer = '', $defAns = false)
    {
        $mrkup = '';

        $CHEADERQUESANS = generate_header_wise_question_array($data['data']['db_ans']);

        foreach($cSetDetails -> headers as $cHeaderId => $cHeaderDetails):

            $subsetStr = '';
            $HEADERANSCHECK = false;

            $mrkup .= '<div class="card apcard audit-ques-container'. ($subset ? (' d-none ' . $subsetContainer) : '') .'">' . "\n";

                $mrkup .= '<div class="card-header">' . "\n";

                    $mrkup .= /*(($subset) ? 'Subset: ' : '') .*/ string_operations($cHeaderDetails -> name, 'upper')/* . (($subset) ? ' ('. string_operations($cSetDetails -> name, 'upper') .') ' : '')*/;

                $mrkup .= '</div>' . "\n";

                $mrkup .= '<div class="card-body">' . "\n";
                
                if($data['data']['db_assesment_data'] -> audit_status_id == 1)
                    $mrkup .= '<button class="btn btn-sm btn-light audit-ques-default-btn">Default</button>' . "\n";

                $srNo = 1;

                $borderActive = array_keys($cHeaderDetails -> questions)[ sizeof($cHeaderDetails -> questions) - 1 ];

                foreach($cHeaderDetails -> questions as $cQuesId => $cQuesDetails):
                    
                    $checkQuetionAns = check_answer_exists_on_question_id($cQuesId, $data['data']['db_ans']);

                    // function call
                    $activateEvidence = (check_evidence_upload_strict() && ($cQuesDetails -> audit_ev_upload == 1 || (isset($checkQuetionAns -> audit_compulsary_ev_upload) && $checkQuetionAns -> audit_compulsary_ev_upload == 1 ? 1 : 0) ));

                    $DEFAULTANS = false;

                    if( !is_object($checkQuetionAns) && 
                        is_array($CHEADERQUESANS) && 
                        array_key_exists($cHeaderId, $CHEADERQUESANS) && 
                        !array_key_exists($cQuesId, $CHEADERQUESANS[ $cHeaderId ]) )
                        $DEFAULTANS = true; 
                        
                    if(!$DEFAULTANS && $defAns) $DEFAULTANS = true;                    
                    if(!$HEADERANSCHECK && (is_object($checkQuetionAns) || $DEFAULTANS)) $HEADERANSCHECK = true;    

                    $mrkup .= '<div class="row question-row'. (($borderActive != $cQuesId) ? ' border-bottom mb-3' : '') . ((is_object($checkQuetionAns) && $checkQuetionAns -> is_compliance) ? ' text-danger' : ((is_object($checkQuetionAns) || $DEFAULTANS) ? ' bg-ques-opac' : '')) .'" data-ques="'. $cQuesDetails -> id .'" data-headerid="'. $cQuesDetails -> header_id .'" data-ansid="' . (is_object($checkQuetionAns) ? encrypt_ex_data($checkQuetionAns -> id) : '0') .'">' . "\n";

                        $mrkup .= '<div class="col-md-6 position-relative'. ((is_object($checkQuetionAns) && $checkQuetionAns -> is_compliance) ? '' : '') .'">
                                        <div class="audit-question-container">
                                            <p class="text-secondary font-medium">'. $srNo++ .'</p>
                                            <p>'. $cQuesDetails -> question .'</p>
                                        </div>';

                        $activatEviBtn = true;

                        if( $activateEvidence )
                        {
                            $mrkup .= '<div class="evidence-upload-container">' . "\n";

                            // function call
                            $tempMarkup = display_evidence_markup($checkQuetionAns);

                            if(!empty($tempMarkup))
                                $activatEviBtn = false;

                            $mrkup .= $tempMarkup;

                            $mrkup .= '</div>' . "\n";
                        }

                        if( $activateEvidence )
                            $mrkup .= '<button class="evidence-upload-btn" '. (!$activatEviBtn ? 'style="display:none"' : '') .''. view_tooltip('Upload File') .'></button>';

                        $mrkup .= '</div>' . "\n";
                                
                        $mrkup .= '<div class="col-md-3 mb-3'. ((is_object($checkQuetionAns) && $checkQuetionAns -> is_compliance) ? '' : '') .'">' . "\n";

                            $selectDataArray = [];
                            $selectStr = '';
                                    
                            if( in_array($cQuesDetails -> option_id, [1, 2, 4]) )
                            {
                                //select options
                                $selectStr = generate_question_select_options($data, $cQuesDetails, ( is_object($checkQuetionAns) ? trim_str($checkQuetionAns -> answer_given) : ($DEFAULTANS ? 'default_' : null) ));
                            }
                            elseif( $cQuesDetails -> option_id == 5 && !$subset )
                            {
                                // for select
                                if(isset($cQuesDetails -> subset_index) && sizeof($cQuesDetails -> subset_index) > 0)
                                {
                                    $selectStr = '<select class="form-control form-select mb-2 subset-type-select" data-ques="'. $cQuesDetails -> id .'">';
                                        $selectStr .= '<option value="">-- Select Answer --</option>';

                                    $selectStr .= generate_question_select_options($data, $cQuesDetails, ( is_object($checkQuetionAns) ? trim_str($checkQuetionAns -> answer_given) : ($DEFAULTANS ? 'default_' : null) ), 0, 0);

                                    foreach ($cQuesDetails -> subset_index as $cOptionId => $cOptionTitle)  
                                        $selectStr .= '<option value="'. $cOptionId .'"'. ( (is_object($checkQuetionAns) && trim_str($checkQuetionAns -> answer_given) == $cOptionId ) ? ' selected="selected"' : '' ) .'>'. string_operations($cOptionTitle, 'upper') .'</option>';

                                    $selectStr .= '</select>';
                                }
                                else
                                    $mrkup .= $data['noti']::getCustomAlertNoti('noDataFound');

                                // for header generation // recursive call
                                if( !$subset && 
                                    isset($cQuesDetails -> subset_data) && 
                                    sizeof($cQuesDetails -> subset_data) > 0 )
                                {                     
                                    foreach($cQuesDetails -> subset_data as $cSubsetId => $cSubsetDetails)
                                        $subsetStr .= generate_question_set_markup($data, $cSubsetDetails, true, 'subset-container-'. $cQuesDetails -> id .' subset-' . $cSubsetId . 'Ques' . $cQuesDetails -> id, $defAns);
                                }
                                
                            }
                            
                            if( $cQuesDetails -> option_id != 3 && empty($selectStr) )
                                $data['noti']::getCustomAlertNoti('noDataFound');

                            if(!empty($selectStr) || $cQuesDetails -> option_id == 3): 
                                $mrkup .= $selectStr;
                            endif;

                            $mrkup .= '<span class="text-danger audit-question-err"></span>' . "\n";
                            $mrkup .= '<label class="compliance-checkbox font-medium"><input class="compliance-chckbox" type="checkbox" value="1" '. ((is_object($checkQuetionAns) && $checkQuetionAns -> is_compliance == 1) ? ' checked="checked"' : '') .' /> Compliance To Be Done</label>' . "\n";

                            if( check_evidence_upload_strict() )
                                $mrkup .= '<label class="compliance-evidence-checkbox font-sm font-medium"><input class="compliance-evidence-chckbox" type="checkbox" value="2" '. ((is_object($checkQuetionAns) && $checkQuetionAns -> compliance_compulsary_ev_upload == 2) ? ' checked="checked"' : '') .' data-ajxurl="'. (EVIDENCE_UPLOAD['control_url'] . 'ans-upload-status/') .'" /> '. EVIDENCE_UPLOAD['checkbox_text'] .'</label>' . "\n";

                        $mrkup .= '</div>' . "\n";

                        $mrkup .= '<div class="col-md-3 mb-3'. ((is_object($checkQuetionAns) && $checkQuetionAns -> is_compliance) ? ' ' : '') .'"><textarea class="form-control audit-comment" placeholder="Audit Comments">'. ( (is_object($checkQuetionAns) && !empty( $checkQuetionAns -> audit_comment )) ? trim_str($checkQuetionAns -> audit_comment) : '' ) .'</textarea></div>' . "\n";

                        if($cQuesDetails -> option_id == '4' && isset($cQuesDetails -> annexure_id_details)):
                                        
                            $mrkup .= '<div class="col-12 annex-container annex-'. $cQuesDetails -> annexure_id .'Ques'. $cQuesDetails -> id .'">' . "\n";
                                
                                if(!isset($cQuesDetails -> annexure_id_details -> annex_cols))
                                    $mrkup .= $data['noti']::getCustomAlertNoti('noDataFound');
                                else
                                {
                                    $mrkup .= '<span class="text-danger annex-container-err d-block text-center mb-2"></span>' . "\n";

                                    $mrkup .= '<div class="table-responsive">' . "\n";
                                    $mrkup .= '<table class="table table-bordered mb-0">' . "\n";
                                        
                                    $tempMarkup = generate_annex_ans_markup($data, $cQuesDetails);

                                    $mrkup .= $tempMarkup['header'];
                                    $mrkup .= $tempMarkup['blank_row'];
                                    unset($tempMarkup);

                                    if( is_object($checkQuetionAns) && 
                                        isset($checkQuetionAns -> annex_ans) && 
                                        is_array($checkQuetionAns -> annex_ans) && 
                                        sizeof($checkQuetionAns -> annex_ans) > 0 )
                                    {
                                        foreach($checkQuetionAns -> annex_ans as $cAnnexAnsId => $cAnnexAnsDetails ) {
                                            $mrkup .= generate_annex_ans_markup($data, $cQuesDetails, $cAnnexAnsDetails);
                                        }
                                    }

                                    $mrkup .= '</table>' . "\n";
                                    $mrkup .= '</div>' . "\n";

                                    if( $data['data']['db_assesment_data'] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'])
                                    {
                                        $mrkup .= '<p class="font-sm font-medium text-danger mt-3 mb-2">Note: Each annexure sample CSV is different. Please download the sample annexure CSV before uploading. <a class="font-sm d-block download-sample-annex" href="#" data-annexid="'. encrypt_ex_data($cQuesDetails -> annexure_id) .'" data-annexurl="'. $data['siteUrls']::getUrl('audit') .'/download-sample-annex">Download Sample Annexure CSV File &raquo;</a></p>' . "\n";

                                        $mrkup .= '<button class="annex-csv-upload-btn" '. view_tooltip('Upload File') .' data-quesid="'. encrypt_ex_data($cQuesDetails -> id) .'" data-dumpid="'. (!empty($data['data']['accId']) ? encrypt_ex_data($data['data']['accId']) : 0) .'">Upload Annexure CSV</button>';
                            
                                        $mrkup .= '<div class="annex-csv-upload-container mb-4"></div>';
                                    }
                                }
                                
                            $mrkup .= '</div>' . "\n";

                        endif;
                            
                        $mrkup .= '</div>' . "\n";

                endforeach;

                $mrkup .= '<div class="text-center mt-3">' . "\n";
                    $mrkup .= '<button class="btn btn-primary font-medium save-answers">'. ($HEADERANSCHECK ? 'Update' : 'Save') . ' Answers</button>' . "\n";
                    $mrkup .= '<span class="d-block save_response text-danger mt-3" style="display:none"></span>' . "\n";
                $mrkup .= '</div>' . "\n";

                $mrkup .= '</div>' . "\n";                

            $mrkup .= '</div>' . "\n";

        // append subset
        $mrkup .= $subsetStr;

        endforeach;

        return $mrkup;
    }
}

if(!function_exists("generate_executive_summary_markup"))
{
    function generate_executive_summary_markup($data, $mrkupBody = false)
    {
        $res = ['str' => '', 'count' => 0, 'pending_count' => 0];

        if( isset($data['data']['exe_summary_data']['rejected']) && $data['data']['exe_summary_data']['rejected'] > 0 )
            $res['count'] += $data['data']['exe_summary_data']['rejected'];
        else
            $res['count'] += $data['data']['exe_summary_data']['pending_reaudit'];

        $res['pending_count'] = $data['data']['exe_summary_data']['pending_reaudit'];

        if( $res['count'] > 0 ):

            if($mrkupBody)
            {
                $res['str'] .= '<div class="card apcard mb-4">' . "\n";
                    $res['str'] .= '<div class="card-header">' . "\n";
                        $res['str'] .= ( $res['count'] > 0 ) ? string_operations('Executive Summary', 'upper') : string_operations('No data found!', 'upper');
                    $res['str'] .= '</div>' . "\n";
                $res['str'] .= '<div class="card-body">' . "\n";
            }

            // for re audit // function call
            if( check_re_assesment_status($data['data']['db_assesment_data'] -> audit_status_id) )
                $msg = '<span class="font-medium">Executive Summary!</span> contains rejected points. Please complete the re audit points';
            else
                $msg = 'Please complete the remaining points in <span class="font-medium">Executive Summary!</span>';

            $res['str'] .= '<p class="text-danger mb-2">'. $msg .'</p>' . "\n";
            $res['str'] .= generate_link_button('link', ['value' => 'Executive Summary', 'href' => $data['siteUrls']::getUrl('executiveSummary') . '/audit' ]);

            if($mrkupBody)
                $res['str'] .= '</div></div>' . "\n";

        endif;

        return $res;
    }    
}

if(!function_exists("audit_end_asses_generate_rand_str")) {
    function audit_end_asses_generate_rand_str() { return substr( md5( microtime() ), rand(0, 26), 5); }
}

if(!function_exists("audit_end_asses_generate_set_markup")) {

    function audit_end_asses_generate_set_markup($colspan, $randStr, $catId, $setData, $data, $dumpDetails = null)
    {
        $returnData = [ 'markup' => '', 'pending' => null, 'compliance' => 0 ];
        $pendingReAssesmentMrk = '<p class="font-sm font-light text-primary mb-0">Error: Pending Re Assesment</p>';
        $pendingReAssesmentEviMrk = '<p class="font-sm font-light text-primary mb-0">Error: Pending Re Assesment Evidence Upload</p>';

        if( is_object( $setData ) )
        {
            $markup = '';
            $subsetMarkup = '';
            $pendingCnt = 0;
            $complianceCnt = 0;
            $gotoLink = $data['siteUrls']::getUrl('auditCategory') . encrypt_ex_data($catId);
            $dumpPendingQues = 0;

            foreach($setData -> headers as $cHeaderId => $cHeaderDetails)
            {
                if(isset($cHeaderDetails -> questions) && sizeof($cHeaderDetails -> questions) > 0)
                {
                    $markup .= '<tr class="header-tr">' . "\n";

                        $markup .= '<td class="bg-light-gray font-medium" colspan="'. $colspan .'"><u>HEADER: '.  string_operations($cHeaderDetails -> name, 'upper') .'</u></td>' . "\n";

                    $markup .= '</tr>' . "\n";

                    $markup .= '<tr class="header-thtr">' . "\n";

                        $markup .= '<th>#</th>' . "\n";
                        $markup .= '<th colspan="2">Question</th>' . "\n";
                        $markup .= '<th colspan="2">Answer Given</th>' . "\n";
                        $markup .= '<th width="120">Action</th>' . "\n";                                    

                    $markup .= '</tr>' . "\n";

                    $srNo = 1;
                    $dumpId = (is_object($dumpDetails) ? $dumpDetails -> id : 0);

                    foreach($cHeaderDetails -> questions as $cQuesId => $cQuesDetails)
                    {
                        $gotoLink = $data['siteUrls']::getUrl('auditCategory') . encrypt_ex_data($catId);

                        $C_QUES_ANS = null;

                        $cGenKey = $cHeaderId . '_' . $cQuesId . '_' . $dumpId;

                        if(
                            is_array($data['data']['db_ans']) && 
                            sizeof($data['data']['db_ans']) > 0 && 
                            array_key_exists($cGenKey, $data['data']['db_ans']) )
                        {
                            $C_QUES_ANS = $data['data']['db_ans'][ $cGenKey ];

                            // check for compliance
                            if( $C_QUES_ANS -> is_compliance == 1 )
                                $complianceCnt++;
        
                            // check for subset
                            if( $cQuesDetails -> option_id == 5 && 
                                isset($cQuesDetails -> subset_data) && 
                                is_array($cQuesDetails -> subset_data) && 
                                array_key_exists($C_QUES_ANS -> answer_given, $cQuesDetails -> subset_data) )
                            {
                                $C_QUES_ANS -> answer_given_str = string_operations($cQuesDetails -> subset_data[ $C_QUES_ANS -> answer_given ] -> name, 'upper');
    
                                // function call
                                $subsetMarkup = audit_end_asses_generate_set_markup($colspan, audit_end_asses_generate_rand_str(), $catId, $cQuesDetails -> subset_data[ $C_QUES_ANS -> answer_given ], $data, $dumpDetails);
                            }
                        }

                        if( !is_object($C_QUES_ANS) && 
                            !empty($cQuesDetails -> parameters) && 
                            in_array($cQuesDetails -> option_id, [1,2,4,5]) )
                        {
                            $parameters = json_decode($cQuesDetails -> parameters);
                            $tempOptionsArray = [];

                            if(is_array($parameters) && sizeof($parameters) > 0)
                            {
                                foreach ($parameters as $optionDetails)
                                    $tempOptionsArray[ $optionDetails -> br . '.' . $optionDetails -> cr ] = $optionDetails -> rt;
                            }

                            if(sizeof($tempOptionsArray) > 0)
                                krsort($tempOptionsArray);
                        }

                        // pending count
                        if(!is_object($dumpDetails) && $data['data']['db_assesment_data'] -> audit_status_id == 1)
                            $pendingCnt += !is_object($C_QUES_ANS) ? 1 : 0;

                        $isCompliance = is_object($C_QUES_ANS) && $C_QUES_ANS -> is_compliance ? ' text-danger font-medium' : '';

                        $markup .= '<tr class="question-ans-tr audit-question-container-row '. ( !is_object($C_QUES_ANS) ? 'text-danger' : 'ans-given-question-tr' . $isCompliance ) .'">' . "\n";

                            // sr no
                            $markup .= '<td>'. $srNo++ .'</td>';

                            // question
                            $markup .= '<td colspan="2">';
                                $markup .= /*$cQuesId .*/ trim_str($cQuesDetails -> question);

                                if( check_re_assesment_status($data['data']['db_assesment_data']) && 
                                    is_object($C_QUES_ANS) && $data['data']['db_assesment_data'] -> batch_key != $C_QUES_ANS -> batch_key)
                                {
                                    $pendingCnt += 1;
                                    $dumpPendingQues++;
                                    $markup .= $pendingReAssesmentMrk;
                                }

                                if( check_re_assesment_status($data['data']['db_assesment_data']) && 
                                    check_evidence_upload_strict() && 
                                    !empty($C_QUES_ANS -> audit_compulsary_ev_upload) &&
                                    $C_QUES_ANS -> audit_compulsary_ev_upload == 1 &&
                                    empty($C_QUES_ANS -> audit_evidance_upload))
                                {
                                    $pendingCnt += 1;
                                    $dumpPendingQues++;
                                    $markup .= $pendingReAssesmentEviMrk;
                                }

                            $markup .='</td>';

                            // ans given
                            if($cQuesDetails -> option_id == 5 && is_object($C_QUES_ANS) && isset( $C_QUES_ANS -> answer_given_str ))
                                $markup .= '<td colspan="2">'. trim_str($C_QUES_ANS -> answer_given_str) .'</td>';
                            else if($cQuesDetails -> option_id == 4 && 
                                    is_object($C_QUES_ANS) && 
                                    $C_QUES_ANS -> answer_given == $cQuesDetails -> annexure_id)
                                $markup .= '<td colspan="2">'. AS_PER_ANNEXURE .'</td>';
                            else
                                $markup .= '<td colspan="2">'. trim_str((is_object($C_QUES_ANS) && $C_QUES_ANS -> answer_given != '') ? $C_QUES_ANS -> answer_given : '') .'</td>';

                            // action
                            $markup .= '<td>';
                                $markup .=  generate_link_button('link', ['href' => $gotoLink, 'extra' => view_tooltip('Add / Edit Answer')]);
                            $markup .= '</td>';

                        $markup .= '</tr>' . "\n";

                        if( $cQuesDetails -> option_id == 4 && 
                            is_object($C_QUES_ANS) && 
                            $C_QUES_ANS -> answer_given == $cQuesDetails -> annexure_id && 
                            isset($C_QUES_ANS -> annex_ans) && 
                            sizeof($C_QUES_ANS -> annex_ans) > 0 )
                        {
                            $markup .= '<tr class="ans-given-question-tr">' . "\n";
                                $markup .= '<td colspan="'. $colspan .'">' . "\n";

                                if( !is_object($cQuesDetails -> annexure_id_details) || 
                                    !isset($cQuesDetails -> annexure_id_details -> annex_cols) ||
                                    (   isset($cQuesDetails -> annexure_id_details -> annex_cols) && 
                                        !sizeof($cQuesDetails -> annexure_id_details -> annex_cols) > 0) )
                                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound') . "\n";
                                else
                                {
                                    $markup .= '<table class="table table-sm v-table table-bordered mb-0">' . "\n";
                                        $markup .= '<tr>' . "\n";
                                            foreach($cQuesDetails -> annexure_id_details -> annex_cols as $cAnnexCols):
                                                $markup .= '<th>'. $cAnnexCols -> name .'</th>' . "\n";
                                            endforeach;

                                            if(check_evidence_upload_strict())
                                                $markup .= '<th>Evidence</th>' . "\n";
                                        $markup .= '</tr>' . "\n";

                                        foreach($C_QUES_ANS -> annex_ans as $cAnsAnnex):

                                            $firstRowAnnexError = false;

                                            // RE ASSESMENT CHECK
                                            if( check_re_assesment_status($data['data']['db_assesment_data']) && 
                                                $data['data']['db_assesment_data'] -> batch_key != $cAnsAnnex -> batch_key)
                                            {
                                                $firstRowAnnexError = true;
                                                $dumpPendingQues++;
                                                $pendingCnt += 1;
                                            }

                                            $markup .= '<tr>' . "\n";

                                            $jsonConvertBool = false;

                                            try 
                                            {
                                                $cJsonData = json_decode($cAnsAnnex -> answer_given);

                                                if(is_object($cJsonData))
                                                {
                                                    $jsonConvertBool = true;
                                                    $REAUDITBOOL = false;

                                                    // increment pending cnt
                                                    if( check_re_assesment_status($data['data']['db_assesment_data']) && 
                                                        $cAnsAnnex -> batch_key != $data['data']['db_assesment_data'] -> batch_key )
                                                        {
                                                            $REAUDITBOOL = true;
                                                        }

                                                    foreach($cJsonData as $cKey => $cJsonDetails)
                                                    {
                                                        if(!in_array($cKey, ['br', 'cr', 'rt']))
                                                        {
                                                            $markup .= '<td'. ( $REAUDITBOOL ? ' class="text-danger"' : '') .'>';
                                                                $markup .= trim_str($cJsonDetails);

                                                                if($firstRowAnnexError)
                                                                {
                                                                    $markup .= $pendingReAssesmentMrk;
                                                                    $firstRowAnnexError = false;
                                                                }

                                                            $markup .= '</td>';

                                                            
                                                        }
                                                    }
                                                }    
                                            } catch (Exception $e) { }

                                            if(!$jsonConvertBool)
                                                $markup .= '<td colspan="'. sizeof($cQuesDetails -> annexure_id_details -> annex_cols) .'" class="text-danger">'. $data['noti']::getNoti('noDataFound') .'</td>';

                                            if(check_evidence_upload_strict())
                                            {
                                                if( check_re_assesment_status($data['data']['db_assesment_data']) &&  
                                                    $cAnsAnnex -> audit_compulsary_ev_upload == 1 &&
                                                    empty($cAnsAnnex -> audit_evidance_upload))
                                                {
                                                    $pendingCnt += 1;
                                                    $dumpPendingQues++;
                                                    $markup .= '<td>'. $pendingReAssesmentEviMrk .'</td>' . "\n";
                                                }
                                                else
                                                    $markup .= '<td>-</td>' . "\n";
                                            }

                                            $markup .= '</tr>' . "\n";
                                        endforeach;

                                    $markup .= '</table>' . "\n";
                                }

                                $markup .= '</td>' . "\n";
                            $markup .= '</tr>' . "\n";
                        }
                    }

                    if($subsetMarkup != '')
                    {
                        $markup .= $subsetMarkup['markup'];
                        $pendingCnt += $subsetMarkup['pending'];
                        $complianceCnt += $subsetMarkup['compliance'];
                        $subsetMarkup = '';
                    }
                }
            }  
            
            // We are not append dump questions and ans data due to loading issue
            if( is_object($dumpDetails) )
            {
                $acAnsGiven = false;

                if( $dumpDetails -> sampling_filter == 1 && 
                    $dumpDetails -> assesment_period_id == $data['data']['db_assesment_data'] -> id)
                    $acAnsGiven = true;

                $gotoLink .= '?ac=' . encrypt_ex_data($dumpDetails -> id);

                // link
                $markup = '<td colspan="2">-</td><td colspan="2">'. generate_link_button("link", ["href" => $gotoLink, "extra" => view_tooltip("Add / Edit Answer") ]) .'</td>' . "\n";

                $markup .= '</tr>' . "\n";

                $markup .= '<tr'. ($acAnsGiven ? (' class="ans-given-question-tr" data-acc="acc-'. $dumpDetails -> id .'"') : '') .'>' . "\n";

                // display account notification
                if($data['data']['db_assesment_data'] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'] )
                {
                    if( $dumpDetails -> sampling_filter == 1 && 
                        $dumpDetails -> assesment_period_id == $data['data']['db_assesment_data'] -> id)
                    {
                        // assesment completed
                        $markup .= '<td colspan="'. $colspan .'" class="text-secondary text-center font-medium">&laquo; Assessment completed for the above account &raquo;</td>';
                    }
                    else
                    {
                        // assesment not completed for dump
                        $markup .= '<td colspan="'. $colspan .'" class="audit-question-container-row text-danger text-center font-medium">&laquo; Assessment not completed or pending questions found for the above account. &raquo;</td>';

                        $pendingCnt = 1;
                    }
                }
                else
                {
                    // RE ASSESMENT
                    if($dumpPendingQues > 0)
                    {
                        $markup .= '<td colspan="'. $colspan .'" class="audit-question-container-row text-danger text-center font-medium">&laquo; Pending questions found for the above account. &raquo;</td>';
                        $pendingCnt = 1;
                    }
                    else
                        $markup .= '<td colspan="'. $colspan .'" class="text-secondary text-center font-medium">&laquo; Assessment completed for the above account &raquo;</td>';
                }

                // link
                // $markup .= '<td>'. generate_link_button("link", ["href" => $gotoLink, "extra" => view_tooltip("Add / Edit Answer") ]) .'</td>' . "\n";

                $markup .= '</tr>' . "\n";
            }

            $returnData['markup'] = $markup;
            $returnData['pending'] = $pendingCnt;
            $returnData['compliance'] = $complianceCnt;
        }

        return $returnData;
    }
}

if(!function_exists('end_assesment_form_markup_generate'))
{
    function end_assesment_form_markup_generate($data, $extra = []) {

        if(!isset($extra['compliance_cnt']) || !isset($extra['pending_cnt']))
            return null;

        if( !($extra['compliance_cnt'] > 0) )
            echo $data['noti']::cError('Your current assessment has <b>0</b> compliance points. Are you sure you want to submit it?', 'warning');

        echo FormElements::generateFormStart(["name" => "audit-assesment", "action" =>  $data['siteUrls']::setUrl($data['me'] -> url) . "/end-assesment-submit" ]);

        echo FormElements::generateInput([
            "name" => "pending_points", "type" => "hidden", "value" => $extra['pending_cnt']
        ]);

        echo FormElements::generateSubmitButton('', [ 'name' => 'submit', 'value' => 'End Assesment of Current Audit', 'appendClass' => 'd-block w-100'] );

        echo FormElements::generateFormClose();
    }
}

// check cf answer data
if(!function_exists("audit_end_asses_generate_cf_markup")) {

    function audit_end_asses_generate_cf_markup($data, $assesmentData, $extra = [])
    {
        $cGenKey = '0_0_0_' . CARRY_FORWARD_ARRAY['id'];
        $res = ['markup' => '', 'cnt' => 0, 'pending' => 0];

        if(
            is_array($data['data']['db_ans']) && 
            sizeof($data['data']['db_ans']) > 0 && 
            array_key_exists($cGenKey, $data['data']['db_ans']) )
        {
            $cfAns = $data['data']['db_ans'][ $cGenKey ];

            if( isset($cfAns -> annex_ans) && 
                is_array($cfAns -> annex_ans) &&
                sizeof($cfAns -> annex_ans) > 0)
            {
                $res['markup'] .= '<table class="table table-sm v-table table-bordered mb-0">';
                    $res['markup'] .= '<tr>
                        <th>'. CARRY_FORWARD_ARRAY['title'] .'</th>
                        <th>Action</th>
                    </tr>' . "\n";

                    foreach($cfAns -> annex_ans as $cCFId => $cCFData)
                    {
                        $res['cnt']++;

                        // re assesment check
                        if($assesmentData -> batch_key != $cCFData -> batch_key)
                            $res['pending']++;

                        $res['markup'] .= '<tr>' . "\n";

                            $str = generate_cf_markup_row($cCFData, $assesmentData, $extra);

                            if( empty($str) )
                                $str = $data['noti']::getnoti('noDataFound');

                            $res['markup'] .= '<td>'. $str .'</td>' . "\n";

                        // action
                        $gotoLink = $data['siteUrls']::setUrl($data['me'] -> url) . '/' . encrypt_ex_data(CARRY_FORWARD_ARRAY['id']);

                        $res['markup'] .= '<td>'. generate_link_button('link', ['href' => $gotoLink, 'extra' => view_tooltip('Add / Edit Answer')]) .'</td>' . "\n";

                        $res['markup'] .= '</tr>' . "\n";
                    }

                $res['markup'] .= '</table>';
            }
        }

        if( empty($res['markup']) )
            $res['markup'] = $data['noti']::getnoti('noDataFound');
        
        return $res;
    }
}

if(!function_exists("generate_account_markup"))
{
    function generate_account_markup($data, $assesmentData, $dumpDetails, $needBtn = 1)
    {
        $returnMarkup = '';

        if(!is_object($assesmentData) || !is_object($dumpDetails))
            return $returnMarkup;

        $returnMarkup .= '<div class="table-responsive">' . "\n";
        $returnMarkup .= '<table class="table table-bordered table-sm mb-0">' . "\n";

            $returnMarkup .= '<tr class="bg-light">' . "\n";
                $returnMarkup .= '<td class="font-medium">Branch Details</td>' . "\n";

                if( is_object($assesmentData -> audit_unit_id_details) ):
                    $returnMarkup .= '<td>'. string_operations(($assesmentData -> audit_unit_id_details -> name . ' (' . $assesmentData -> audit_unit_id_details -> audit_unit_code . ')'), 'upper') .'</td>' . "\n";
                else:
                    $returnMarkup .= '<td>'. string_operations(ERROR_VARS['notFound'], 'upper') .'</td>' . "\n";
                endif;

                $returnMarkup .= '<td class="font-medium">Scheme Details</td>' . "\n";
                $returnMarkup .= '<td>'. string_operations($dumpDetails -> scheme_id_name, 'upper') . ' (' . $dumpDetails -> scheme_id_code . ')' .'</td>' . "\n";
            $returnMarkup .= '</tr>' . "\n";

            $returnMarkup .= '<tr>' . "\n";
                $returnMarkup .= '<td class="font-medium">A/C Number</td>' . "\n";
                $returnMarkup .= '<td>'. string_operations($dumpDetails -> account_no, 'upper') .'</td>' . "\n";

                $returnMarkup .= '<td class="font-medium">A/C Open Date</td>' . "\n";
                $returnMarkup .= '<td>'. string_operations($dumpDetails -> account_opening_date, 'upper') . ($data['data']['db_category'] -> menu_id == 8 && $data['data']['db_category'] -> is_cc_acc_category == 1 && isset($dumpDetails -> renewal_date) && !empty($dumpDetails -> renewal_date) ? (' Renewal Date: ' . $dumpDetails -> renewal_date) : '') .'</td>' . "\n";
            $returnMarkup .= '</tr>' . "\n";

            $returnMarkup .= '<tr class="bg-light">' . "\n";
                $returnMarkup .= '<td class="text-primary font-medium">A/C Holder Name</td>' . "\n";
                $returnMarkup .= '<td class="text-primary font-medium" colspan="3">'. string_operations($dumpDetails -> account_holder_name, 'upper') .'</td>' . "\n";
            $returnMarkup .= '</tr>' . "\n";

            $returnMarkup .= '<tr>' . "\n";
                $returnMarkup .= '<td class="font-medium">UCIC</td>' . "\n";
                $returnMarkup .= '<td>'. string_operations($dumpDetails -> ucic, 'upper') .'</td>' . "\n";

                if(isset($dumpDetails -> outstanding_balance)):
                    $returnMarkup .= '<td class="font-medium">Outstanding Balance</td>' . "\n";
                    $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> outstanding_balance, 2) .'</td>' . "\n";
                elseif(isset($dumpDetails -> balance)):
                    $returnMarkup .= '<td class="font-medium">Balance Amount</td>' . "\n";
                    $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> balance, 2) .'</td>' . "\n";
                else:
                    $returnMarkup .= '<td></td><td></td>' . "\n";
                endif;

                // $returnMarkup .= '<td class="font-medium">Customer Type</td>' . "\n";
                // $returnMarkup .= '<td>'. string_operations( ($dumpDetails -> customer_type ?? ERROR_VARS['notFound']), 'upper' ) .'</td>' . "\n";
            $returnMarkup .= '</tr>' . "\n";

            $returnMarkup .= '<tr>' . "\n";
                $returnMarkup .= '<td class="font-medium">Interest Rate</td>' . "\n";
                $returnMarkup .= '<td>'. get_decimal($dumpDetails -> intrest_rate, 2) .'</td>' . "\n";

                if(isset($dumpDetails -> principal_amount)):
                    $returnMarkup .= '<td class="font-medium">Principal Amount</td>' . "\n";
                    $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> principal_amount, 2) .'</td>' . "\n";
                elseif(isset($dumpDetails -> sanction_amount)):
                    $returnMarkup .= '<td class="font-medium">Sanction Amount</td>' . "\n";
                    $returnMarkup .= '<td>Rs. '. get_decimal($dumpDetails -> sanction_amount, 2) .'</td>' . "\n";
                endif;
            $returnMarkup .= '</tr>' . "\n";

            if(/*$this -> data['db_category'] -> linked_table_id == 2*/ $needBtn == 2 || $needBtn == 1 ):
                $returnMarkup .= '<tr>' . "\n";

                    if( empty($dumpDetails -> assesment_period_id) ):
                        $returnMarkup .= '<td colspan="2"><a id="accMarkAsComplete" class="btn btn-primary w-100" href="'. $data['siteUrls']::getUrl('audit') .'/category/'. encrypt_ex_data($data['data']['db_category'] -> id) . '?ac='. encrypt_ex_data($dumpDetails -> id) . '&mac=1">Mark As Completed</a></td>' . "\n";

                        $returnMarkup .= '<td colspan="2"><button id="accDefaultAnswers" class="btn btn-secondary w-100">Select Default Answers</button></td>' . "\n";
                    endif;

                    
                $returnMarkup .= '</tr>' . "\n";
            endif;

            if(/*$this -> data['db_category'] -> linked_table_id == 1 &&*/ $assesmentData -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'] &&  $needBtn == 1):
                $returnMarkup .= '<tr>' . "\n";
                    $returnMarkup .= '<td colspan="4"><a id="allAccAssesmentComplete" class="btn btn-danger w-100" href="'. $data['siteUrls']::getUrl('audit') .'/category/'. encrypt_ex_data($data['data']['db_category'] -> id) . '?ac='. encrypt_ex_data($dumpDetails -> id) . '&mac=all">Assesment Complete For All Remaining Accounts in Current Period</a></td>' . "\n";
                $returnMarkup .= '</tr>' . "\n";
            endif;

        $returnMarkup .= '</table>' . "\n";
        $returnMarkup .= '</div>' . "\n";

        return $returnMarkup;
    }
}

?>