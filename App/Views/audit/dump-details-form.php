<div class="card apcard mb-4">
    <div class="card-header">
        Select Account to Audit
    </div>

    <div class="card-body">

        <?php $totalCount = $data['data']['db_dump_count']; ?>

        <p class="text-danger font-sm font-medium mb-2">Total accounts found with & without sampling: <?= $totalCount; ?></p>

        <?php if(!is_array($data['data']['db_dump_data']) || (is_array($data['data']['db_dump_data']) && !sizeof($data['data']['db_dump_data']) > 0)): ?>
            <?= $data['noti']::getCustomAlertNoti('noDumpSampled'); ?>
        <?php endif; ?>

        <?php if( $data['db_assesment'] -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[1]['status_id'] && $totalCount > 0): ?>
            <?= generate_link_button('link', ['value' => 'Resample Accounts', 'href' => $data['data']['sampling_link'], 'extra' => view_tooltip('Resample Accounts')]); ?>
            <div class="w-100 mb-3"></div>
        <?php endif; ?>

        <?php 
        
        if(is_array($data['data']['db_dump_data']) && sizeof($data['data']['db_dump_data']) > 0):  
            
            if(!isset($data['data']['db_display_percentage']))
                $data['data']['db_display_percentage'] = sizeof($data['data']['db_dump_data']);

            $enableActions = true;

            require_once APP_VIEWS . '/audit/dump-common-markup.php';

        endif;
        
        if(!$totalCount > 0):
            echo $data['noti']::getCustomAlertNoti('noDataFound'); 
        endif; ?>
        
    </div>
</div>