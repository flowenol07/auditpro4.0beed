<?php

use Core\FormElements;

if($data['data']['db_data_count'] > 0):

echo $data['noti']::getSessionAlertNoti();

// Get the selected audit unit ID if any
$selectedAuditId = $data['request'] -> input('audit_id');
$canChangeSelected = true;

if(!empty($selectedAuditId) && isset($data['data']['frequency_change_allowed'][$selectedAuditId])) {
    $canChangeSelected = $data['data']['frequency_change_allowed'][$selectedAuditId];
}

// Set disabled based on permission
$disabled = (!$canChangeSelected && !empty($selectedAuditId)) ? 'disabled' : '';

echo FormElements::generateFormStart(["name" => "audit-unit-master", "action" => $data['me'] -> url ]);
?>
    <div class="row">
        <div class="col-md-6">
            <?php

                //audit_id
                $markup = FormElements::generateLabel('audit_id', 'Audit Unit
                ');

                if(is_array($data['data']['db_audit_unit']) && sizeof($data['data']['db_audit_unit']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "audit_id", "name" => "audit_id", 
                        "default" => ["", "Please select unit"],
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

        <div class="col-md-6">
            <?php

                //frequency
                $markup = FormElements::generateLabel('frequency', 'Audit Frequency
                ');

                if(is_array($GLOBALS['auditFrequencyArray']) && sizeof($GLOBALS['auditFrequencyArray']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "frequency", "name" => "frequency", 
                        "default" => ["", "Please select audit frequency"],
                        "options" => $GLOBALS['auditFrequencyArray'],
                        "selected" => $data['request'] -> input('frequency'),
                        "disabled" => $disabled
                    ]);

                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'frequency');
            ?>
        </div>
    </div>

    <?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Save Unit', 'btn_type' => 'update']; 
    

    echo FormElements::generateSubmitButton('update', $btnArray );

    echo FormElements::generateFormClose(); 

    ?>

    </div>

    <?php
        if(sizeof($data['data']['db_data'])  > 0){
    ?>
            <div class='border bg-white p-4 mt-4'>
                <div class="table-responsive">
                    <table id="auditUnitFrequencyDataTable" class="table table-hover v-table dataTable">

                        <thead>
                            <tr>
                                <th scope="col">Unit Code</th>
                                <th scope="col">Branch</th>
                                <th scope="col">Frequency</th>
                                <th scope="col" class="nosort">Action</th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                    </table>
                </div>
            </div>
    <?php } ?>
    

<?php
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'auditUnitFrequencyDataTable', $data["siteUrls"]::getUrl( $data["me"] -> id ) .'/'. DATA_TABLE_AJX . '-frequency', [ "audit_unit_code", "name", "frequency", "action" ]);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>