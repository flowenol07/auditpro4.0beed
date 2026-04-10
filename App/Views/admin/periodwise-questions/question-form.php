<?php

use Core\FormElements;

require_once 'single-period-markup.php';
require_once 'question-markup-common-code.php';

if(is_array($data['data']['db_question_data_mix']) && sizeof($data['data']['db_question_data_mix']) > 0)
{
    $formMarkup = question_checkbox_markup($data, true);

    echo '<div class="row">' . "\n";
        echo '<div class="col-md-12">' . "\n";

            echo FormElements::generateFormStart(["name" => "update-questions", "appendClass" => "multi-checkbox-check-form" ]);

            echo FormElements::generateSubmitButton('', [ 'value' => 'Check All', 'type' => 'button', 'id' => 'checkAllCheckboxes'] );

            echo '<h4 class="font-medium lead mt-3 mb-0">Total Questions: '. $formMarkup['question_count'] .'</h4>' . "\n";

            if( $data['request'] -> has('multi_question_check_err'))
                echo '<span class="d-block text-danger font-sm mb-2">'. $data['request'] -> input('multi_question_check_err') .'</span>' . "\n";

        echo '</div>' . "\n";
    echo '</div>' . "\n";

    // close parent container
    echo '</div>' . "\n";
    echo '<div class="mb-4"></div>' . "\n";

    if($formMarkup['question_count'] > 0)
        echo $formMarkup['markup'];
    else
        echo $data['noti']::getCustomAlertNoti('noDataFound');

    echo FormElements::generateInput([
        "id" => "multi_type_check", "name" => "multi_type_check", 
        "type" => "hidden", "value" => '' ]);

    echo FormElements::generateSubmitButton('update', [ 'name' => 'submit', 'value' => 'Update Questions'] );

    echo FormElements::generateFormClose(); 
}
else
    echo $data['noti']::getCustomAlertNoti('noDataFound');

?>