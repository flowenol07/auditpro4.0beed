<?php 

if(is_object($data['data']['db_data'])): 

    require_once 'single-set-details-markup.php';

    // check task exists or not
    if(is_array($data['data']['tasks_data']) && sizeof($data['data']['tasks_data']) > 0)
    {
        $headerSrNo = 1;

        foreach($data['data']['tasks_data'] as $cHeaderId => $cData): ?>

        <div class="card apcard mb-4 rounded-0">
            <div class="card-header pb-1 font-medium text-uppercase">
                <?= $headerSrNo; ?>. Header: <?= $cData['name']; ?>
            </div>
            <div class="card-body">
                <table class="table v-table">
                    <tr>
                        <th class="text-center" width="60">Sr. No.</th>
                        <th>Task Details</th>
                        <th width="100">Priority</th>
                    </tr>
                    
                    <?php 
                    
                    $srNo = 1;
                    foreach($cData['tasks'] as $cTaskId => $cTaskData): ?>
                    
                    <tr>
                        <td class="text-center font-medium"><?= $srNo ?></td>

                        <td>
                            <p class="font-sm mb-0"><span class="font-medium text-danger">Risk Category: </span><?= ($cTaskData -> risk_category != 'na' ? $cTaskData -> risk_category : ERROR_VARS['notFound']) ?></p>

                            <p class="text-primary mb-2"><?= $cTaskData -> task ?></p>
                            <?php /*<p class="font-sm text-secondary mb-0"><span class="font-medium">Area Of Audit: </span><?= ($cTaskData -> audit_area_name != 'na' ? $cTaskData -> audit_area_name : ERROR_VARS['notFound']); ?></p> */?>

                            <?php 

                            //question type
                            /*$cOptionData = ERROR_VARS['notFound'];

                            if( is_array($GLOBALS['questionInputMethodArray']) && 
                                array_key_exists($cTaskData -> option_id, $GLOBALS['questionInputMethodArray']))
                                $cOptionData = $GLOBALS['questionInputMethodArray'][ $cTaskData -> option_id ]['title'];

                            ?>

                            <p class="mb-2"><span class="font-medium">Answer Type: </span><?= $cOptionData; ?></p> */ ?>

                            <?php
 
                                if( isset($data['data']['cco_docs_true']) )
                                {
                                    $docsMrk = generate_circular_docs_markup($cTaskData, [ 'container' => 1, 'mt' => 1 ]);

                                    $extra = [ 
                                        'mb' => 1, 
                                        'circular_id' => $cTaskData -> set_id,
                                        'task_id' => $cTaskData -> id 
                                    ];

                                    if(empty($docsMrk))
                                        $extra['need_container'] = 1;

                                    echo generate_compliance_doc_btn($extra, 2);

                                    if(!empty($docsMrk))
                                        echo $docsMrk;
                                }
                            
                            ?>
                        </td>

                        <td class="font-medium text-danger"><?= isset(COMPLIANCE_PRO_ARRAY['compliance_priority'][ $cTaskData -> priority_id ]) ? COMPLIANCE_PRO_ARRAY['compliance_priority'][ $cTaskData -> priority_id ] : ERROR_VARS['notFound'] ?></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td>
                            <?php
                            
                            if($cTaskData -> is_active == 1):
                        
                                echo generate_link_button('update', ['href' => $data['siteUrls']::getUrl( 'complianceCircularTaskMaster' ) . '/update/' . encrypt_ex_data($cTaskData -> id) . '?backto=1', 'extra' => view_tooltip('Update') ]);
    
                                if($data['data']['disable_action']) {
    
                                    echo generate_link_button('delete', ['href' => $data['siteUrls']::getUrl( 'complianceCircularTaskMaster' ) . '/delete/' . encrypt_ex_data($cTaskData -> id) . '?backto=1', 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
                                    
                                }
    
                            endif;
                            
                            ?>
                        </td>

                        <td><?= check_active_status($cTaskData -> is_active, 1, 1, 1) ?></td>
                    </tr>

                    <?php 
                        $srNo++;
                        endforeach; 
                    ?>

                </table>
            </div>
        </div>

        <?php 
            $headerSrNo++;
            endforeach;
    }
    else
    {
        echo '<h4 class="font-medium lead">Circular Tasks &raquo;</h4>';
        echo $data['noti']::getCustomAlertNoti('noDataFound');
    }

    // doc upload form // function call
    echo generate_hidden_docs_upload_form();

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>