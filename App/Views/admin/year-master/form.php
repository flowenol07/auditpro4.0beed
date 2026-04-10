<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "year-master", "action" => $data['me'] -> url ]);

    //year
    $markup = FormElements::generateLabel('year', 'Year');

    $markup .= FormElements::generateInput([
        "id" => "year", "name" => "year", 
        "type" => "text", "value" => $data['request'] -> input('year', $data['data']['db_data'] -> year), 
        "placeholder" => "Year"
    ]);

    echo FormElements::generateFormGroup($markup, $data, 'year');

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Year'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Year';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

echo FormElements::generateFormClose();

?>