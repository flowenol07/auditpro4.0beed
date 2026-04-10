<?php use Core\FormElements; 

if( is_array($data['data']['db_sets']) && sizeof($data['data']['db_sets']) > 0 ):

    require_once 'audit-common-code.php';

    $questionDataDisplay = true; 
    
    if( array_key_exists($data['data']['db_category'] -> linked_table_id, $GLOBALS['schemeTypesArray']) ): 

        $cSelectedAccDetails = null;

        if( !empty($data['data']['get_acc_id']) && 
            is_array( $data['data']['db_dump_data'] ) && array_key_exists($data['data']['get_acc_id'], $data['data']['db_dump_data']) )
            $cSelectedAccDetails = $data['data']['db_dump_data'][ $data['data']['get_acc_id'] ];

        if( !is_object($cSelectedAccDetails) ):
            $questionDataDisplay = false;
        
        else:

    ?>

        <div class="card apcard mb-4 fix-account-container">
            <div class="card-header">
                <?= string_operations(($data['data']['db_category'] -> name . ' - Accounts'), 'upper'); ?>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="col-md-4 col-lg-3 mb-3 mb-md-0">

                        <?php 
                        
                        echo FormElements::generateInput([
                            "id" => "search_acc_autocomplete", "type" => "text", 
                            "appendClass" => "mb-2", "placeholder" => "Search Account Number"
                        ]);
                        
                        ?>

                        <div class="account-tab-container" style="height:162px; overflow-x:auto;">

                        <?php  

                        foreach($data['data']['db_dump_data'] as $cAccId => $cAccDetails):

                            $cSelectionAcc = ( !empty($data['data']['get_acc_id']) && $cAccDetails -> id == $data['data']['get_acc_id'] ) ? 'btn-primary' : 'btn-light border';

                            if(!empty($cAccDetails -> assesment_period_id) && 
                             ( !empty($data['data']['get_acc_id']) && $cAccDetails -> id != $data['data']['get_acc_id'] ))
                                $cSelectionAcc = 'btn-success';
                            
                            echo '<a class="btn '. $cSelectionAcc .' search_acc_audit d-block mb-1" href="'. str_replace('?smpl=1', '', $data['data']['sampling_link']) . '?ac=' . encrypt_ex_data($cAccDetails -> id) .'" data-account_no="'. $cAccDetails -> account_no .'">'. string_operations(($cAccDetails -> account_no . ' (' . $cAccDetails -> scheme_id_code . ')'), 'upper') .'</a>' . "\n";
                        endforeach;

                        ?>
                        
                        </div>
                    </div>

                    <div class="col-md-8 col-lg-9">
                        <?= generate_account_markup($data, $data['data']['db_assesment_data'], $cSelectedAccDetails, /*$this -> data['db_category'] -> linked_table_id*/ 1) ; ?>
                    </div>
                </div>
                
            </div>

            <span id="fixAccountClick"></span>
        </div>

    <?php 
        endif;

    endif;

    if( $questionDataDisplay ):
    
        foreach( $data['data']['db_sets'] as $cSetId => $cSetDetails ):

            // helper function call
            echo generate_question_set_markup($data, $cSetDetails, false, '', ( ( isset($cSelectedAccDetails) && is_object($cSelectedAccDetails) && !empty($cSelectedAccDetails -> assesment_period_id)) ? true : false ));

        endforeach; 

        $evi_markup = check_evidence_upload_strict('file_upload');

        if(!empty($evi_markup) && $evi_markup != 1)
            echo $evi_markup;

        if(!check_re_assesment_status($data['data']['db_assesment_data']))
        {
            // annexure csv upload // not for re audit
            echo '<form id="annex_csv_upload_form" style="display:none;" enctype="multipart/form-data" data-action="'. $data['siteUrls']::getUrl('audit') .'/upload-question-annex-csv">' . "\n";
                echo '<input type="file" name="annex_csv_file" id="annex_csv_file">' . "\n";
                echo '<input type="text" name="annex_csv_quesid" id="annex_csv_quesid">' . "\n";
                echo '<input type="text" name="annex_csv_catid" id="annex_csv_catid" value="'. encrypt_ex_data($data['data']['db_category'] -> id) .'">' . "\n";
                echo '<input type="text" name="annex_csv_dumpid" id="annex_csv_dumpid">' . "\n";
            echo '</form>' . "\n";
        }

    endif;

else: 

?>
    <div class="card apcard mb-4">
        <div class="card-header">
            <?= string_operations('No data found!', 'upper'); ?>
        </div>
        <div class="card-body">
            <?= $data['noti']::getCustomAlertNoti('noDataFound'); ?>
        </div>
    </div>

<?php endif; ?>