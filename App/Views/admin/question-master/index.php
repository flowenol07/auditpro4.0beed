<?php 

require_once APP_VIEWS . '/admin/question-header-master/single-set-details-markup.php';

if(sizeof($data['data']['db_data']) > 0):
    
    foreach($data['data']['db_data'] as $cHeaderId => $cData):
?>

    <div class="card rounded-0 mt-4">
        <div class="card-header pb-1 font-medium text-uppercase">
            Header: <?= $cData['name']; ?>
        </div>
        <div class="card-body">
            <table class="table v-table">
                <tr>
                    <th width="50">Q. Id.</th>
                    <th>Question Details</th>
                    <th width="80">Status</th>
                    <th width="140">Answer Type</th>
                    <th width="240">Actions</th>
                </tr>
                <?php foreach($cData['questions'] as $cQuesId => $cQuesData): ?>
                <tr>
                    <td><?= $cQuesId ?></td>

                    <td>
                        <p class="font-sm my-2"><span class="font-medium text-danger">Risk Category: </span><?= $cQuesData -> risk_category_name ?></p>

                        <p class="text-primary font-medium mb-0"><?= $cQuesData -> question ?></p>
                        <p class="font-sm text-secondary mb-2"><span class="font-medium">Area Of Audit: </span><?= $cQuesData -> area_of_audit_name ?></p>
                    </td>

                    <td><?= check_active_status($cQuesData -> is_active); ?></td>

                    <td><?= $cQuesData -> option_id_name ?></td>

                    <td>

                    <?php 
                    
                    if($cQuesData -> is_active == 1):
                    
                        echo generate_link_button('update', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/update/' . encrypt_ex_data($cQuesData -> id), 'extra' => view_tooltip('Update') ]);

                        if($data['data']['disable_action']) {

                            echo generate_link_button('delete', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/delete/' . encrypt_ex_data($cQuesData -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);

                            echo generate_link_button('inactive', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/status/' . encrypt_ex_data($cQuesData -> id), 'extra' => view_tooltip('Deactivate') . ' onclick="return confirm(\'Are you sure you want to Deactivate\');"' ]);
                            
                        }

                        if(in_array($cQuesData -> option_id, [1, 2, 4])):
                            echo generate_link_button('link', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/risk-mapping/' . encrypt_ex_data($cQuesData -> id), 'extra' => view_tooltip('Risk Mapping')]);
                        endif;
                        
                    else:

                        if($data['data']['disable_action']):
                            echo generate_link_button('active', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/status/' . encrypt_ex_data($cQuesData -> id), 'extra' => view_tooltip('Activate') ]);
                        endif;

                    endif;

                    ?>

                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>
    </div>

<?php

    endforeach;

else:

    echo $data['noti']::getCustomAlertNoti('noDataFound'); ?>

<?php endif; ?>