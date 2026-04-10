<?php 

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if( array_key_exists('data_array', $data['data']) && empty($data['data']['data_array']))
{
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti($data['data']['data_error']);
}

//has data
elseif( array_key_exists('data_array', $data['data']) && 
        is_array($data['data']['data_array']) && 
        sizeof($data['data']['data_array']) > 0 ) { 
        
    echo '<div id="printContainer">' . "\n";

        // generate header function
        generate_report_header($data['data']);
?>

    <div class="mb-4"></div>

    <div class="table-responsive">        
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>                
                <tr class="bg-light-gray">
                    <th style="width:5%" class="text-center">Sr. No.</th>
                    <th style="width:20%">Audit Unit</th>
                    <th style="width:15%">Assesment Period</th>
                    <th style="width:20%">Audit Status</th>
                    <th style="width:10%" class="text-center">Audited</th>
                    <th style="width:10%" class="text-center">Pending Observations</th>
                    <th style="width:10%" class="text-center">Completed</th>
                </tr>
            </thead>            
            <tbody>
            <?php 
            
            $cIndex = 1;
            
                foreach($data['data']['data_array'] as $cAuditDetails): ?>
                    
                <tr>
                    <td style="width:10%" class="text-center"><?= $cIndex++; ?></td>
                    
                    <td style="width:20%"><?= $cAuditDetails -> combined_name; ?></td>
                    
                    <td style="width:15%"><?= $cAuditDetails -> assesment_period_from . ' to ' . $cAuditDetails -> assesment_period_to ?><span class="d-block"> ( Frequency: <?= $cAuditDetails -> frequency ?> Months )</span></td>
                    
                    <td style="width:20%"><?= array_key_exists($cAuditDetails -> audit_status_id, ASSESMENT_TIMELINE_ARRAY) ? ASSESMENT_TIMELINE_ARRAY[$cAuditDetails -> audit_status_id]['title'] : ERROR_VARS['notFound'] ?></td>

                    <?php 
                    
                    $auditObserv = 0;
                    $completeObserv = 0;
                    $pendingObserv = 0;

                    if(isset($cAuditDetails -> audit_observ))
                    {
                        $auditObserv = $cAuditDetails -> audit_observ;
                        $pendingObserv = $cAuditDetails -> pending_observ;
                        $completeObserv = $auditObserv - $pendingObserv;

                        if($cAuditDetails -> audit_status_id == ASSESMENT_TIMELINE_ARRAY[7]['status_id'])
                        {
                            $pendingObserv = 0;
                            $completeObserv = $auditObserv;
                        }
                    }
                    
                    ?>

                    <td style="width:10%" class="text-center"><?= $auditObserv; ?></td>

                    <td style="width:10%" class="text-center"><?= $pendingObserv; ?></td>

                    <td style="width:10%" class="text-center"><?= $completeObserv; ?></td>
                </tr>

            <?php endforeach; ?>
            <tbody>

        </table>
    </div>

<?php } ?>