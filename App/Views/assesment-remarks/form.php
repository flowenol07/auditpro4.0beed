<?php

use Core\FormElements;

echo FormElements::generateFormStart([ "id" => "assesment_remark_form", "action" => $data['siteUrls']::getUrl('assesmentRemarkMaster') . '/add' ]);

    echo '<div id="rmk_err_container"></div>' . "\n";

    echo '<div class="row">' . "\n";

        echo '<div class="col-md-12">' . "\n";

            // remark for
            $markup = null /*FormElements::generateLabel('rmk_noti_type', 'Remark Type')*/;

            if(is_array($data['remarkTypeArray']) && sizeof($data['remarkTypeArray']) > 0 )
            {
                $markup .= FormElements::generateSelect([
                    "id" => "rmk_noti_type", "name" => "rmk_noti_type", 
                    "default" => ["", "Please select remark type"],
                    "options" => $data['remarkTypeArray']['remark_array']
                ]);
            }
            else    
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            $markup .= '<span class="d-block font-sm text-danger rmk-span-err mt-1"></span>' . "\n";
            echo FormElements::generateFormGroup($markup, $data);            
        
        echo '</div>' . "\n";

        echo '<div class="col-md-12">' . "\n";

            // subject
            $markup = null /*FormElements::generateLabel('rmk_subject', 'Subject')*/;

            $markup .= FormElements::generateInput([
                "id" => "rmk_subject", "type" => "text", "name" => "rmk_subject", 
                "value" => null, "placeholder" => "Remark subject"
            ]);

            $markup .= '<span class="d-block font-sm text-danger rmk-span-err mt-1"></span>' . "\n";
            echo FormElements::generateFormGroup($markup, $data);

        echo '</div>' . "\n";

    echo '</div>' . "\n";

    // message
    $markup = null /*FormElements::generateLabel('rmk_message', 'Message')*/;

    $markup .= FormElements::generateTextArea([
        "id" => "rmk_message", "type" => "text", "name" => "rmk_message", 
        "value" => null, "placeholder" => "Write remark here..."
    ]);

    $markup .= '<span class="d-block font-sm text-danger rmk-span-err mt-1"></span>' . "\n";
    echo FormElements::generateFormGroup($markup, $data);

    // echo FormElements::generateInput([
    //     "id" => "rmk_id", "type" => "text", "name" => "rmk_id", 
    //     "value" => null
    // ]);

    $btnArray = [ 'id' => 'rmk_submit_btn', 'value' => 'Save Remark', 'appendClass' => 'w-100' ];   
    echo FormElements::generateSubmitButton( 'remark_asses', $btnArray );        

echo FormElements::generateFormClose();

?>