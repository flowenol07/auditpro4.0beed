<?php

use Core\FormElements;

require_once 'single-period-markup.php';

?>

<div class="row">

    <div class="col-md-12">

    <?php 

    if(is_array($data['data']['db_scheme_data']) && sizeof($data['data']['db_scheme_data']) > 0)
    {
        echo FormElements::generateFormStart(["name" => "update-scheme", "appendClass" => "multi-checkbox-check-form" ]);

        echo FormElements::generateSubmitButton('', [ 'value' => 'Check All', 'type' => 'button', 'id' => 'checkAllCheckboxes'] );

        echo '<h4 class="font-medium lead mt-3 mb-2">Total Schemes: '. sizeof($data['data']['db_scheme_data']) .'</h4>' . "\n";

        if( $data['request'] -> has('multi_schemes_check_err'))
            echo '<span class="d-block text-danger font-sm mb-2">'. $data['request'] -> input('multi_schemes_check_err') .'</span>' . "\n";

        echo '<table class="table table-bordered">';

            if($data['data']['db_data'] -> scheme_type == 2)
                $scheme_data_arr = !empty($data['data']['db_data'] -> advances_scheme_ids) ? explode (",", $data['data']['db_data'] -> advances_scheme_ids) : [];
            else
                $scheme_data_arr = !empty($data['data']['db_data'] -> deposits_scheme_ids) ? explode (",", $data['data']['db_data'] -> deposits_scheme_ids) : [];

            // function call
            echo generate_multiple_checkboxes($data['data']['db_scheme_data'], $scheme_data_arr, 'multi_schemes_check', 'scheme');
            
        echo '</table>';

        echo FormElements::generateInput([
            "id" => "multi_type_check", "name" => "multi_type_check", 
            "type" => "hidden", "value" => '' ]);

        echo FormElements::generateSubmitButton('update', [ 'name' => 'submit', 'value' => 'Update Scheme'] );

        echo FormElements::generateFormClose(); 
    }
    else
        echo $data['noti']::getCustomAlertNoti('noDataFound');

    ?>

    </div>

</div>