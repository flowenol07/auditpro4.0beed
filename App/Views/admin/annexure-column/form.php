<?php

use Core\FormElements;

require_once 'single-annexure-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "annexure-column", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-6">
            <?php
                //Column Name
                $markup = FormElements::generateLabel('name', 'Add Column Name');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Column Name"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-6">
            <?php
                //column_type_id
                $markup = FormElements::generateLabel('column_type_id', 'Select Column Type');

                if(is_array($GLOBALS['columnTypeArray']) && sizeof($GLOBALS['columnTypeArray']) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "column_type_id", "name" => "column_type_id", 
                        "default" => ["", "Please select column type"],
                        "options" => $GLOBALS['columnTypeArray'],
                        "selected" => $data['request'] -> input('column_type_id', $data['data']['db_data'] -> column_type_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'column_type_id');
            ?>
        </div>
    </div>

<?php 

function generate_select_section($select, $data) 
{
    // div for deposit / advances
    echo "<div class='show_hide_div' id='" . $select . "'>";
    
    echo "<div class='mb-4'></div>";
    
    //column_options
    $markup = FormElements::generateLabel('name', 'Column Options');

    $markup .= FormElements::generateInput([
        "id" => "column_options", "name" => "column_options", 
        "type" => "text", "value" => "", 
        "placeholder" => "Column Options"
    ]);

    echo FormElements::generateFormGroup($markup, $data, 'column_options');

    // button for add option
    echo '
        <button type="button" class="btn btn-primary addOption" id="addOption" value="Add Option">Add Option</button>';

    echo   
        '<div id="custom_option_list" class="col-md-12 mt-3">
            <table id="custom_option_listTable" class="table table-bordered table-striped">
                <thead>
                </thead>
                <tbody id="custom_option_list_table_body">';
                if($data['request'] -> input('column_options', $data['data']['db_data'] -> column_options))
                {   
                    $jsonData = json_decode($data['request'] -> input('column_options', $data['data']['db_data'] -> column_options));
                    for($i = 0; $i < count($jsonData) ;$i++)
                    {
                        echo'<tr class="optionsContainer"><th class="option" width="90%"><input type="text" class="form-control" name="option[]" id="option" value="' . htmlspecialchars($jsonData[$i]-> column_option) .'" /></th><th><button type ="button" class="remove_risk btn btn-danger">Remove</button></th></tr>
                        
                        ';
                    }
                }
                echo'</tbody>
                <tfoot>
                </tfoot>
                </table>
        </div>';

    echo '</div>';
    
}

// Generate deposit section 
// above function called
generate_select_section('dropdown',$data);
  

echo "<div class='mb-2'></div>";

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Column'];     

    if($data['data']['btn_type'] == 'update' && empty($data['data']['db_question']))
    {
        $btnArray['value'] = 'Update Column';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    elseif($data['data']['btn_type'] == 'add' && empty($data['data']['db_question']))
    {
        echo FormElements::generateSubmitButton('add', $btnArray );
    }
    else
    {
        echo "<p class='text-danger mb-1'> * This Annexure Columns are applied to question, So you cannot add, update or delete this column option. </p>";
    }
    echo FormElements::generateFormClose(); 


$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function(){

    // for hide and show-------------
    function show_hide_container (val) {
        $(".show_hide_div").hide();

        if($(".show_hide_div")[val - 3] !== undefined)
        {
            $($(".show_hide_div")[val - 3]).show();
        }
    }

    $("#column_type_id").change(function(){
        show_hide_container($(this).val());
    });

    show_hide_container($("#column_type_id").val());

    $("#addOption").click(function() {
    var optionValue = $("#column_options").val();
    
    var newRow = $("<tr class=\'optionsContainer\'><th class=\'option\' width=\'90%\'><input type=\'text\' class=\'form-control\' name=\'option[]\' id=\'option\' value=\'\'/></th><th><button class=\'remove_risk btn btn-danger\'>Remove</button></th></tr>");

    $("#custom_option_list_table_body").append(newRow);

    let currentOpt = $("#custom_option_list_table_body tr:last-child").find(".form-control");

    if(currentOpt.length > 0)
        $(currentOpt[0]).val(optionValue);

    $("#column_options").val("");

    $(".remove_risk").on("click", function(e) {
        e.preventDefault();
        $(this).closest(".optionsContainer").remove();
    });
});

$(".remove_risk").on("click", function(e) {
    e.preventDefault();
    $(this).closest(".optionsContainer").remove();
});

});

</script>';

?>