<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "scheme-master", "action" => $data['me'] -> url ]);

?>

<div class="row">
    <div class="col-md-6">

    <?php 

        //scheme_type_id
        $markup = FormElements::generateLabel('scheme_type', 'Scheme Type');

        if(is_array($GLOBALS['userTypesArray']) && sizeof($GLOBALS['userTypesArray']) > 0)
        {
            $markup .= FormElements::generateSelect([
                "id" => "scheme_type", "name" => "scheme_type", 
                "default" => ["", "Please select scheme type"],
                "options" => $GLOBALS['schemeTypesArray'],
                "selected" => $data['request'] -> input('scheme_type', $data['data']['db_data'] -> scheme_type_id)
            ]);

        }
        else
            $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

        echo FormElements::generateFormGroup($markup, $data, 'scheme_type');

    ?>

    </div>

    <div class="col-md-6">
    <div id="category_deposit_id">
        <?php 

            //category_id
            $markup = FormElements::generateLabel('category_id', 'Category');

            if(is_array($data['data']['db_deposit_category']) && sizeof($data['data']['db_deposit_category']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "category_deposit", "name" => "category_id", 
                    "default" => ["", "Please select category"],
                    "appendClass" => "select2search",
                    "options" => $data['data']['db_deposit_category'],
                    "selected" => $data['request'] -> input('category_id', $data['data']['db_data'] -> category_id)
                ]);

            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'category_id');
        ?>
    </div>
    <div id="category_advances_id">
        <?php
            
            //category_id
            $markup = FormElements::generateLabel('category_id', 'Category');

            if(is_array($data['data']['db_advances_category']) && sizeof($data['data']['db_advances_category']) > 0)
            {
                $markup .= FormElements::generateSelect([
                    "id" => "category_advances", "name" => "category_id", 
                    "default" => ["", "Please select category"],
                    "appendClass" => "select2search",
                    "options" => $data['data']['db_advances_category'],
                    "selected" => $data['request'] -> input('category_id', $data['data']['db_data'] -> category_id)
                ]);

            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'category_id');

        ?>
    </div>

    </div>

    <div class="clearfix"></div>

    <div class="col-md-6">

    <?php 

        //scheme_code
        $markup = FormElements::generateLabel('scheme_code', 'Scheme Code');

        $markup .= FormElements::generateInput([
            "id" => "scheme_code", "name" => "scheme_code", 
            "type" => "text", "value" => $data['request'] -> input('scheme_code', $data['data']['db_data'] -> scheme_code), 
            "placeholder" => "Scheme Code"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'scheme_code');

    ?>

    </div>

    <div class="col-md-6">

    <?php

        //name
        $markup = FormElements::generateLabel('name', 'Scheme Name');

        $markup .= FormElements::generateInput([
            "id" => "name", "name" => "name", 
            "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
            "placeholder" => "Scheme Name"
        ]);

        echo FormElements::generateFormGroup($markup, $data, 'name');
        
    ?>

    </div>
</div>

    <?php

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Scheme'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Scheme';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

echo FormElements::generateFormClose();

$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function () { 
    
    function showHideFunction () {
        const inputString = $("#scheme_type").val();

        $("#category_advances_id, #category_deposit_id").hide();
        $("#category_advances, #category_deposit").removeAttr("name");

        let inputEle = (inputString == 2) ? "advances" : ((inputString == 1) ? "deposit" : "");

        if( inputEle != "" )
        {
            $("#category_" + inputEle + "_id").show();
            $("#category_" + inputEle).attr("name", "category_id");
        }
       
    }

    // calling function on ready
    showHideFunction();
    
    // creating another function onchange and calling showHideFunction()
    $("#scheme_type").change(function () { 
        showHideFunction();      
    });
}); 

</script>';
?>