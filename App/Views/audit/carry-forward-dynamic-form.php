<?php

if( is_object($data['cf_data']) && 
    isset($data['cf_data'] -> annex_ans) && 
    is_array($data['cf_data'] -> annex_ans) && 
    sizeof($data['cf_data'] -> annex_ans) > 0 )
{

    echo '<div class="card apcard mb-4">' . "\n";
        echo '<div class="card-header">' . "\n";
            echo string_operations(CARRY_FORWARD_ARRAY['title'], 'upper');
        echo '</div>' . "\n";

        echo '<div class="card-body">' . "\n";

            $cCFIndex = 1;

            // display data
            foreach($data['cf_data'] -> annex_ans as $cCFAnnexId => $cCFAnnexData)
            {
                // function call
                $activateEvidence = (check_evidence_upload_strict() /*&& (isset($cCFAnnexData -> audit_compulsary_ev_upload) && $cCFAnnexData -> audit_compulsary_ev_upload == 1 ? 1 : 0)*/ );

                $borderBottom = (sizeof($data['cf_data'] -> annex_ans) != $cCFIndex ) ? ' border-bottom mb-3' : '';

                echo '<div class="row question-row'. $borderBottom .'" data-ansid="0" data-annexid="0">
                        <div class="col-md-8 position-relative">
                            <div class="audit-question-container">
                                <p class="text-secondary font-medium">'. $cCFIndex .'</p>
                                <p>';

                                    // function call
                                    $str = generate_cf_markup_row($cCFAnnexData, $data['db_assesment']);

                                    if( empty($str) )
                                        echo $data['noti']::getnoti('noDataFound');
                                    else 
                                        echo $str;

                          echo '</p>
                            </div>';

                            $activatEviBtn = true;
                            $mrkup = '';

                            if( $activateEvidence )
                            {
                                $annexEviMarkup = '';
                                
                                if(is_object($cCFAnnexData) && isset($cCFAnnexData -> audit_evidence))
                                {
                                    $activatEviBtn = false;

                                    // function call
                                    $annexEviMarkup .= display_evidence_markup($cCFAnnexData);
                                }
                                
                                $mrkup .= '
                                    <div class="td-class">
                                        <button class="btn btn-secondary annex-evidence-upload-btn compliance-evi-btn" '. (!$activatEviBtn ? 'style="display:none"' : '') .''. view_tooltip('Upload File') .'>Evidence</button>
                                        <div class="annex-evidence-upload-container" data-annexid="'. encrypt_ex_data($cCFAnnexData -> id) .'" data-ansid="'. encrypt_ex_data($cCFAnnexData -> answer_id) .'">'. $annexEviMarkup .'</div>
                                    </div>' . "\n";
                            }

                        // for evidence upload markup
                        echo $mrkup;
                        
                        echo '</div>

                        <div class="col-md-4 mb-3 audit-cf-container" data-url="'. ($data['siteUrls']::setUrl($data['me'] -> url) . '/cf-comment-save') .'">
                            <textarea class="form-control" placeholder="Audit Comments" data-ansid="'. encrypt_ex_data($cCFAnnexData -> id) .'">'. ( !empty($cCFAnnexData -> audit_comment) ? $cCFAnnexData -> audit_comment : '' ) .'</textarea>
                            <div class="reponse-status d-block font-sm mt-1 mb-2"></div>
                            <button class="btn btn-secondary btn-sm">Save Comment</button>
                        </div>
                </div>';

                $cCFIndex++;
            }

        echo '</div>' . "\n";
    echo '</div> ' . "\n";

    $evi_markup = check_evidence_upload_strict('file_upload');

    if(!empty($evi_markup) && $evi_markup != 1) { echo $evi_markup; }

}
else
{

?>
    <div class="card apcard mb-4">
        <div class="card-header">
            <?= string_operations('No data found!', 'upper'); ?>
        </div>
        <div class="card-body">
            <?= $data['noti']::getCustomAlertNoti('noDataFound'); ?>
        </div>
    </div>

<?php } ?>