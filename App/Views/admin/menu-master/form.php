<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "menu-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php

                //section_type_id
                $markup = FormElements::generateLabel('section_type_id', 'Audit Section
                ');

                if(isset($data['data']['db_audit_section']) && sizeof($data['data']['db_audit_section']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "section_type_id", "name" => "section_type_id", 
                        "default" => ["", "Please select section"],
                        "options" => $data['data']['db_audit_section'],
                        "appendClass" => "select2search",
                        "selected" => $data['request'] -> input('section_type_id', $data['data']['db_data'] -> section_type_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'section_type_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //menu name
                $markup = FormElements::generateLabel('name', 'Menu');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Menu"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-12">
            <?php

                //linked_table_id
                $markup = FormElements::generateLabel('linked_table_id', 'Linked Table for Dump
                ');

                if(isset($GLOBALS['schemeTypesArray']) && sizeof($GLOBALS['schemeTypesArray']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "linked_table_id", "name" => "linked_table_id", 
                        "default" => ["", "Please select linked table"],
                        "options" => $GLOBALS['schemeTypesArray'],
                        "selected" => $data['request'] -> input('linked_table_id', $data['data']['db_data'] -> linked_table_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'linked_table_id');
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Menu'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Menu';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>