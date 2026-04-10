<?php

use Core\FormElements;

require_once 'single-period-markup.php';

?>

<div class="row">

    <div class="col-md-12">

    <?php 

    if(is_array($data['data']['db_category_data']) && sizeof($data['data']['db_category_data']) > 0)
    {
        echo FormElements::generateFormStart(["name" => "update-categories", "appendClass" => "multi-checkbox-check-form" ]);

        echo FormElements::generateSubmitButton('', [ 'value' => 'Check All', 'type' => 'button', 'id' => 'checkAllCheckboxes'] );

        echo '<h4 class="font-medium lead mt-3 mb-2">Total Categories: '. sizeof($data['data']['db_category_data']) .'</h4>' . "\n";

        if( $data['request'] -> has('multi_category_check_err'))
            echo '<span class="d-block text-danger font-sm mb-2">'. $data['request'] -> input('multi_category_check_err') .'</span>' . "\n";

        echo '<table class="table table-bordered">';

            $category_data_arr = !empty($data['data']['db_data'] -> cat_ids) ? explode (",", $data['data']['db_data'] -> cat_ids) : [];

            // function call
            echo generate_multiple_checkboxes($data['data']['db_category_data'], $category_data_arr, 'multi_category_check', 'category');
            
        echo '</table>';

        echo FormElements::generateInput([
            "id" => "multi_type_check", "name" => "multi_type_check", 
            "type" => "hidden", "value" => '' ]);

        echo FormElements::generateSubmitButton('update', [ 'name' => 'submit', 'value' => 'Update Category'] );

        echo FormElements::generateFormClose(); 
    }
    else
        echo $data['noti']::getCustomAlertNoti('noDataFound');

    ?>

    </div>

</div>