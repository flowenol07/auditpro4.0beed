<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "menu-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php

            // audit units	
            $markup = FormElements::generateLabel('auditUnit', 'Audit Unit');

            if(is_array($data['data']['audit_unit_data']) && sizeof($data['data']['audit_unit_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "auditUnit", "name" => "auditUnit", 
                    "default" => ["", "Please select audit units"],
                    "appendClass" => "select2search",
                    "options" => $data['data']['audit_unit_data'], "options_db" => ["type" => "obj", "val" => "combined_name"],
                    "selected" => $data['request'] -> input('auditUnit')
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'auditUnit');

            ?>
        </div>

        <div class="col-md-6">
            <?php

                $f_year = date('Y-m-01');
                $f_year = getFYOnDate($f_year);

                // startDate
                $markup = FormElements::generateLabel('start_date', 'Start Date');

                $markup .= FormElements::generateInput([
                    "id" => "start_date", "name" => "startDate", 
                    "type" => "text", "value" => $data['request'] -> input('startDate', date($f_year . '-04-01')), 
                    "placeholder" => "Start Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'startDate');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                // endDate
                $markup = FormElements::generateLabel('end_date', 'End Date');

                $markup .= FormElements::generateInput([
                    "id" => "end_date", "name" => "endDate", 
                    "type" => "text", "value" => $data['request'] -> input('endDate', date(($f_year + 1) . '-02-t')), 
                    "placeholder" => "End Date"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'endDate');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Search Data'];     

    echo FormElements::generateSubmitButton('search', $btnArray );

    echo FormElements::generateFormClose(); 

    if(isset($data['data']['db_data']))
    {
        if(!is_array($data['data']['db_data']) || (is_array($data['data']['db_data']) && !sizeof($data['data']['db_data']) > 0))
        {   
            // has no data
            echo '<div class="mb-3"></div>' . "\n";
            echo $data['noti']::getCustomAlertNoti('noDataFound');
        }
        else
        {
            echo '</div>' . "\n";

            // has data
            echo '<div class="border bg-white mt-4 p-4">' . "\n";

            echo '<h6 class="">Total Audit Assesment Found: '. sizeof($data['data']['db_data']) .'</h6>' . "\n";

            require_once('common-code.php');
            echo generate_table_markup_assesment($data, 1);

            // print_r($data['data']['db_data']);
            echo '</div>' . "\n";
        }
    }

?>