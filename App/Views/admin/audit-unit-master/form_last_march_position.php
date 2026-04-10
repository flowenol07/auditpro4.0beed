<?php

use Core\FormElements;
use Core\SiteUrls;

echo $data['noti']::getSessionAlertNoti();

// Get the selected audit unit ID if any
$selectedAuditId = $data['request']->input('audit_id');

echo FormElements::generateFormStart(["name" => "audit-unit-master", "action" => $data['me']->url, "enctype" => "multipart/form-data"]);
?>
    <div class="row">
        <div class="col-md-6">
            <?php
                //audit_id - Audit Unit selection only
                $markup = FormElements::generateLabel('audit_id', 'Select Audit Unit');

                if(is_array($data['data']['db_audit_unit']) && sizeof($data['data']['db_audit_unit']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "audit_id", 
                        "name" => "audit_id", 
                        "default" => ["", "Please select audit unit"],
                        "appendClass" => "select2search",
                        "options" => $data['data']['db_audit_unit'],
                        "selected" => $selectedAuditId
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'audit_id');
            ?>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <?php
            $csvMarkup = FormElements::generateLabel('csv_file', 'Upload CSV File');
            $csvMarkup .= '<input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv">';
            
            echo FormElements::generateFormGroup($csvMarkup, $data, 'csv_file');
            ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <?php echo $data['noti']::getCustomAlertNoti('<span class="font-medium">Download: </span> Sample CSV file. <a class="text-danger" href="'. SiteUrls::getUrl('auditUnitMaster') .'/download-sample-csv">Click Here</a>', 'warning'); ?>
        </div>
    </div>
    
    <div class="row mt-2">
        <div class="col-md-12">
            <?php 
            $btnArray = ['name' => 'submit', 'value' => 'View Position', 'btn_type' => 'update', 'class' => 'btn-primary']; 
            $uploadArray = ['name' => 'upload_csv', 'value' => 'Upload & Update March Position', 'btn_type' => 'success', 'class' => 'btn-success ms-2'];
            
            echo FormElements::generateSubmitButton('update', $btnArray);
            echo FormElements::generateSubmitButton('upload_csv', $uploadArray);
            echo FormElements::generateFormClose(); 
            ?>
        </div>
    </div>

    <?php
// Only show the table if an audit unit is selected and data exists
if(!empty($selectedAuditId) && isset($data['data']['march_position_data']) && !empty($data['data']['march_position_data'])):
?>
    <div class='border bg-white p-4 mt-4'>
        <h5 class="mb-3">Last March Position Data for <?= htmlspecialchars($data['data']['selected_audit_unit_name'] ?? 'Selected Unit') ?></h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>GL Type ID</th>
                        <th>March Position</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($data['data']['march_position_data'] as $record): ?>
                    <tr>
                        <td><?= $record->gl_type_id ?? 'N/A' ?></td>
                        <td class="text-start"><?= number_format($record->march_position ?? 0, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <th><strong>Total</strong></th>
                        <th class="text-start">
                            <?php 
                            $totalMarchPosition = array_sum(array_column($data['data']['march_position_data'], 'march_position'));
                            echo number_format($totalMarchPosition, 2);
                            ?>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php elseif(!empty($selectedAuditId)): ?>
    <div class="alert alert-warning mt-4">
        No March position data found for the selected audit unit.
    </div>
<?php endif; ?>