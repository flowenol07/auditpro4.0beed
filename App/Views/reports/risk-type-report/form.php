<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "last_march_report", "action" => ""]);

?>

<div class="row">
    <div class="col-md-6">
        <?php
            //financial_year	
            $markup = FormElements::generateLabel('financial_year', 'Select Financial Year');

            if(is_array($data['data']['db_year_data']) && sizeof($data['data']['db_year_data']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "financial_year", "name" => "financial_year", 
                    "default" => ["", "Please select financial year"],
                    "options" => $data['data']['db_year_data'],
                    "selected" => isset($data['data']['risk_category_weigth_data'][0] -> year_id) ? ($data['request'] -> input('financial_year', $data['data']['risk_category_weigth_data'][0] -> year_id)) : '',
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'financial_year');
        ?>
    </div>
</div>

<?php
    $btnArray = array('find');
    
    if(isset($data['data']['risk_category_weigth_data']) && !empty($data['data']['risk_category_weigth_data']))
            array_push($btnArray, 'print', 'reset','excel');
    
    generate_report_buttons($btnArray);
?>