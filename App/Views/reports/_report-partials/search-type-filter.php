<?php 

use Core\FormElements; 

// search type
$markup = FormElements::generateLabel('selectSearchTypeFilter', 'Search Type');

$formElementArray = [
    "id" => "selectSearchTypeFilter", "name" => "selectSearchTypeFilter", 
    "default" => ["", "Please select search type"],
    "selected" => $data['request'] -> input('selectSearchTypeFilter'),
    "options" => $data['data']['search_type_array'],
    "options_db" => [ "type" => "arr", "val" => "title" ], 
    "optionDataAttributes" => ['showhide']
];        

$markup .= FormElements::generateSelect($formElementArray);
echo FormElements::generateFormGroup($markup, $data, 'selectSearchTypeFilter');

?>