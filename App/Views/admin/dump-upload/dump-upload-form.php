<?php

use Core\FormElements;
use Core\SiteUrls;
use Core\Session; // Added missing Session import

//function for check error
function check_error_acc_msg($key, $details_array)
{
    $return_str = $details_array[ $key ];

    if(array_key_exists('error', $details_array) && array_key_exists($key, $details_array['error']))
    {
        $return_str .= '<small class="text-danger d-block">'. $details_array['error'][ $key ] .'</small>';
    }

    return $return_str;
}

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart([ "name" => "dump-upload", "action" => $data['me'] -> url, "enctype" => "multipart/form-data" ]);

?>

<div class="row">

    <div class="col-md-6">
        <span class="font-medium btn btn-outline-secondary mr-1 mb-1">Last Uploaded Date: <?= (is_array($data['data']['last_upload_data']) && sizeof($data['data']['last_upload_data']) > 0) ? date($GLOBALS['dateSupportArray'][1], strtotime($data['data']['last_upload_data']['upload_date'])) : ERROR_VARS['notAvailable'] ?></span>

        <?php if(is_array($data['data']['list_upload_data']) && sizeof($data['data']['list_upload_data']) > 0): ?>
            <span class="btn btn-primary icn-grid icn-af" data-bs-toggle="modal" data-bs-target="#listUploadData">View All</span>
            
            <!-- Add Delete Last Uploaded Dump Button -->
            <?php if(is_array($data['data']['last_upload_data']) && sizeof($data['data']['last_upload_data']) > 0): 
                $dumpType = isset($this->dumpType) ? $this->dumpType : (isset($data['dumpType']) ? $data['dumpType'] : 1);
                
                if($dumpType == 2) {
                    $deleteUrl = SiteUrls::getUrl('manageAccountsAdvances') . '/deleteLastUploadAdvance';
                    $buttonText = 'Delete Last Uploaded Advance Dump';
                } else {
                    $deleteUrl = SiteUrls::getUrl('manageAccountsDeposits') . '/deleteLastUploadDeposit';
                    $buttonText = 'Delete Last Uploaded Deposit Dump';
                }
            ?>
                <a href="<?= $deleteUrl ?>" class="btn btn-danger" ><?= $buttonText ?></a>
            <?php endif; ?>

            <div class="modal fade" id="listUploadData" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="listUploadDataLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        
                        <div class="modal-header">
                            <h5 class="modal-title" id="listUploadDataLabel">Upload Dump List</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Upload Period</th>
                                        <th>Upload Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($data['data']['list_upload_data'] as $c_list_upload_date): ?>
                                    <tr>
                                        <td>
                                            <div class=""><?= $c_list_upload_date['upload_period_from'] ?> TO <?= $c_list_upload_date['upload_period_to'] ?></div>
                                        </td>
                                        <td>
                                            <div class=""><?= date($GLOBALS['dateSupportArray'][1], strtotime($c_list_upload_date['upload_date'])) ?></div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <div class="col-md-6">
        <p class="font-medium btn btn-outline-secondary">Last Uploaded Period: <?= (is_array($data['data']['last_upload_data']) && sizeof($data['data']['last_upload_data']) > 0) ? (date($GLOBALS['dateSupportArray'][1], strtotime($data['data']['last_upload_data']['upload_period_from'])) . ' TO ' . date($GLOBALS['dateSupportArray'][1], strtotime($data['data']['last_upload_data']['upload_period_to']))) : ERROR_VARS['notAvailable'] ?></p>
    </div>

    <div class="col-md-12">

    <?php 

        //upload_date
        $markup = FormElements::generateLabel('upload_date', 'Dump Upload Date');

        $markup .= FormElements::generateInput([
            "id" => "upload_date", "name" => "upload_date", "appendClass" => 'date_cls',
            "type" => "text", "value" => $data['request'] -> input('upload_date', $data['data']['db_data'] -> upload_date), 
            "placeholder" => "Dump Upload Date"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'upload_date');

    ?>

    </div>

    <div class="col-md-6">
        <?php 

            //upload_period_from
            $markup = FormElements::generateLabel('upload_period_from', 'Dump Upload - Period From');

            $markup .= FormElements::generateInput([
                "id" => "upload_period_from", "name" => "upload_period_from", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('upload_period_from', $data['data']['db_data'] -> upload_period_from), 
                "placeholder" => "Dump Upload - Period From"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'upload_period_from');

        ?>
    </div>

    <div class="col-md-6">
        <?php 

            //upload_period_to
            $markup = FormElements::generateLabel('upload_period_to', 'Dump Upload - Period To');

            $markup .= FormElements::generateInput([
                "id" => "upload_period_to", "name" => "upload_period_to", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('upload_period_to', $data['data']['db_data'] -> upload_period_to), 
                "placeholder" => "Dump Upload - Period To"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'upload_period_to');

        ?>
    </div>

    <div class="col-md-12">
        <?php echo $data['noti']::getCustomAlertNoti('<span class="font-medium">Download: </span> Sample CSV file. <a class="text-danger" href="'. $data['data']['sample_csv'] .'">Click Here</a>', 'warning'); ?>
    </div>

    <div class="col-md-12">
        <?php 

            //csv_file_upload
            $markup = FormElements::generateLabel('csv_file_upload', 'CSV File Upload <span class="text-danger font-sm">(CSV format with comma "," separator data</span>)');

            $markup .= FormElements::generateInput([
                "id" => "csv_file_upload", "name" => "csv_file_upload", "appendClass" => "form-control-file", "type" => "file", "value" => ""
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'csv_file_upload');

        ?>
    </div>

</div>

    <?php

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Dump'];     
    echo FormElements::generateSubmitButton('add', $btnArray );

echo FormElements::generateFormClose();

// Display upload summary from flash if available
$uploadSummary = Session::flash('upload_summary');
if(!empty($uploadSummary)):
?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <?= $uploadSummary ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(isset($data['data']['err_acc_data']) && sizeof($data['data']['err_acc_data']) > 0): ?>

    <?php $srr_no = 1; ?>

    <h5 class="text-primary mt-4 mb-3">Total Accounts: <?= $data['data']['csv_data']; ?> | Total Error Accounts: <?= sizeof($data['data']['err_acc_data']); ?></h5>

    <div class="table-responsive height-600">
        <table class="table table-bordered v-table">
            <tr>
                <th>Sr. No.</th>
                <th>Branch Code</th>
                <th>Scheme Code</th>
                <th>Account Number</th>
                <th>Account Holder Name</th>
                <th>Account Open Date</th>
            </tr>

            <?php 
                foreach($data['data']['err_acc_data'] as $cAccKey => $cAccDetails):
                
                    echo '<tr>' . "\n";
                        echo '<td>'. $srr_no .'</td>' . "\n";
                        echo '<td>'. check_error_acc_msg('branch_code', $cAccDetails) .'</td>' . "\n";
                        echo '<td>'. check_error_acc_msg('scheme_code', $cAccDetails) .'</td>' . "\n";
                        echo '<td>'. check_error_acc_msg('account_no', $cAccDetails) .'</td>' . "\n";
                        echo '<td>'. check_error_acc_msg('account_holder_name', $cAccDetails) .'</td>' . "\n";
                        echo '<td>'. check_error_acc_msg('account_opening_date', $cAccDetails) .'</td>' . "\n";
                    echo '</tr>' . "\n";
                    $srr_no++;

                endforeach; 
            ?>

        </table>
    </div>

<?php endif; ?>