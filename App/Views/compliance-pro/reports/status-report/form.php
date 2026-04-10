<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "compliance-circular-master", "action" => $data['me'] -> url ]);

$dataBool = ( isset($data['data']['circular_data']) && is_array($data['data']['circular_data']) && sizeof($data['data']['circular_data']) > 0 );

?>
<div class="row">

    <?php require_once REPORTS_VIEW . DS . '_report-partials/date-filters.php'; ?>

    <?php if( $dataBool ): ?>

    <div class="col-md-6">
        <?php
            // circular set name	
            $markup = FormElements::generateLabel('circularSet', 'Select Circular');

            if(is_array($data['data']['circular_set_data']) && sizeof($data['data']['circular_set_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "circularSet",
                    "default" => ["", "Please select circular"],
                    "options" => $data['data']['circular_set_data'],
                    "appendClass" => "filter_dropdown select2search",
                    "extra" => "data-sort=2 onclick='return false;'"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'circularSet');
        ?>
    </div>

    <div class="col-md-6">
        <?php
            // authority	
            $markup = FormElements::generateLabel('authority', 'Select Authority');

            if(is_array($data['data']['db_authority_data']['data']) && sizeof($data['data']['db_authority_data']['data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "authority",
                    "default" => ["", "Please select authority"],
                    "options" => $data['data']['db_authority_data']['data'],
                    "options_db" => [ 'type' => 'obj', 'val' => 'name' ],
                    "appendClass" => "filter_dropdown",
                    "extra" => "data-sort=1 onclick='return false;'"
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'authority');
        ?>
    </div>

    <div class="col-md-6">
        <?php
            // Frequency	
            $markup = FormElements::generateLabel('frequency', 'Frequency');

            $markup .= FormElements::generateSelect([
                "id" => "frequency",
                "default" => ["", "Please select frequency"],
                "options" => $data['data']['frequency_data'],
                "appendClass" => "filter_dropdown",
                "extra" => "data-sort=6"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'frequency');
        ?>
    </div>

    <div class="col-md-6">
        <?php
            // Status	
            $markup = FormElements::generateLabel('status', 'Status');

            $markup .= FormElements::generateSelect([
                "id" => "status",
                "default" => ["", "Please select status"],
                "options" => $data['data']['status_data'],
                "appendClass" => "filter_dropdown",
                "extra" => "data-sort=11"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'status');
        ?>
    </div>

    <?php if(isset($data['data']['db_audit_unit_data']) && 
             is_array($data['data']['db_audit_unit_data']) &&
             sizeof($data['data']['db_audit_unit_data']) > 0): ?>
    
            <div class="col-md-12">
                <?php
                    // Audit Units	
                    $markup = FormElements::generateLabel('status', 'Compliance Units');

                    $markup .= FormElements::generateSelect([
                        "id" => "status",
                        "default" => ["", "Please select compliance unit"],
                        "options" => $data['data']['db_audit_unit_data']['select'],
                        "appendClass" => "filter_dropdown select2search",
                        "extra" => "data-sort=5"
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'status');
                ?>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php  

$btnArray = array('find');

if( $dataBool )
    array_push($btnArray, 'filter', 'print', 'excel', 'reset');

generate_report_buttons($btnArray);

?>