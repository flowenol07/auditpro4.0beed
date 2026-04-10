<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "audit-complete-report", "action" => "" ]); 
$monthYearStr = '<span class="font-sm text-secondary">[YYYY-MM Eg. '. date('Y-m') .']</span>';

?>

<div class="row">
    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/search-type-filter.php'; ?>
    </div>

    <div class="col-md-6">
        <?php
            //trend
            $markup = FormElements::generateLabel('trend', 'Trend On');

            if(is_array($data['data']['trend_on']) && sizeof($data['data']['trend_on']) > 0 )
            {
                $markup .= FormElements::generateSelect([
                    "id" => "trend", "name" => "trend", 
                    "default" => ["", "Please select trend"],
                    "options" => $data['data']['trend_on'],
                    "selected" => $data['request'] -> input('trend')
                ]);

            }
            else    
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


            echo FormElements::generateFormGroup($markup, $data, 'trend');
        ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <span class="badge bg-primary rounded-0">Trend Period - 1</span>
        <div class="row mt-2">
            <div class="col-md-6">

                <?php

                // startMonth
                $markup = FormElements::generateLabel('start_month', 'Start Month ' . $monthYearStr);

                $markup .= FormElements::generateInput([
                    "id" => "start_month", "name" => "startMonth", 
                    "type" => "text", "value" => $data['request'] -> input('startMonth', date('Y-04')), 
                    "placeholder" => "Start Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'startMonth');

                ?>

            </div>

            <div class="col-md-6">

                <?php

                // endMonth
                $markup = FormElements::generateLabel('end_month', 'End Month ' . $monthYearStr);

                $markup .= FormElements::generateInput([
                    "id" => "end_month", "name" => "endMonth", 
                    "type" => "text", "value" => $data['request'] -> input('endMonth', date('Y-09')), 
                    "placeholder" => "End Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'endMonth');

                ?>

            </div>
        </div>
    </div>

    <div class="col-md-6">
        <span class="badge bg-primary rounded-0">Trend Period - 2</span>

        <div class="row mt-2">

            <div class="col-md-6">

                <?php

                // startMonth2
                $markup = FormElements::generateLabel('start_month_2', 'Start Month ' . $monthYearStr);

                $markup .= FormElements::generateInput([
                    "id" => "start_month_2", "name" => "startMonth2", 
                    "type" => "text", "value" => $data['request'] -> input('startMonth2', date('Y-10')), 
                    "placeholder" => "Start Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'startMonth2');

                ?>

            </div>

            <div class="col-md-6">

                <?php

                // endMonth2
                $markup = FormElements::generateLabel('end_month_2', 'End Month ' . $monthYearStr);

                $markup .= FormElements::generateInput([
                    "id" => "end_month_2", "name" => "endMonth2", 
                    "type" => "text", "value" => $data['request'] -> input('endMonth2', (date('Y') + 1) . '-03'), 
                    "placeholder" => "End Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'endMonth2');

                ?>

            </div>
        </div>

    </div>
</div>

<?php

    $btnArray = array('find', 'reset');

    if( array_key_exists('data_array', $data['data']) && 
        is_array($data['data']['data_array']) && 
        sizeof($data['data']['data_array']) > 0 )
        array_push($btnArray, 'print', 'excel');

    generate_report_buttons($btnArray);
?>