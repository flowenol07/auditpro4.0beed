<?php

use Core\FormElements;

$markup = FormElements::generateCheckboxOrRadio([
    'type' => 'checkbox', 'id' => 'rmv_pending_assesments', 'name' => 'rmv_pending_assesments', 
    'value' => '1', 'text' => 'Remove Pending Assesments',
    'checked' => ( ($data['request'] -> input('rmv_pending_assesments') == 1) ? true : false), 
    'customLabelClass' => 'font-medium text-danger'
]);

echo FormElements::generateFormGroup($markup);

?>