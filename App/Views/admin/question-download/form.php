<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "question-download", "action" => $data['me'] -> url ]);

?>
    <div class="row">

        <div class="col-md-12">
            <?php

                //section_id
                $markup = FormElements::generateLabel('section_id', 'Select Section
                ');

                if($data['data']['auditSectionData'] && sizeof($data['data']['auditSectionData']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "section_id", "name" => "section_id", 
                        "default" => ["", "Please select section type"],
                        "options" => $data['data']['auditSectionData'],
                        "selected" => $data['request'] -> input('section_id'),
                        "options_db" => ["type" => "obj", "val" => "name"],
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'section_id');
            ?>
        </div>
    </div>

    <input type='hidden' name='broaderAreaWise' value='1'/>

<?php 

    $btnArray = array('find', 'reset');

    if((isset($data['data']['questionData']) && sizeof($data['data']['questionData']) > 0) && isset($data['data']['queData']) && sizeof($data['data']['queData']) > 0 && is_array($data['data']['queData']))
        array_push($btnArray, 'excel');
    
    generate_report_buttons($btnArray);

    echo FormElements::generateFormClose(); 

?>