<?php use Core\FormElements; ?>

<div class="card apcard mb-4">
    <div class="card-header">
        Advance Sampling
    </div>

    <div class="card-body">

    <?php echo FormElements::generateFormStart(["name" => "target-master", "action" => $data['data']['sampling_link'], "method" => "get" ]); ?>

        <div class="row">
            <div class="col-md-12">
                <?php
                    // ft
                    $markup = FormElements::generateLabel('ft', 'Filter Type');

                    if(is_array($GLOBALS['columnTypeArray']) && sizeof($GLOBALS['columnTypeArray']) > 0 )
                    {
                        $markup .= FormElements::generateSelect([
                            "id" => "ft", "name" => "ft", 
                            "default" => ["", "Please select filter"],
                            "options" => $data['data']['sampling_filter'],
                            "selected" => $data['request'] -> input('ft')
                        ]);

                    }
                    else    
                        $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                    echo FormElements::generateFormGroup($markup, $data, 'ft');

                    echo FormElements::generateInput([
                        "name" => "smpl", "type" => "hidden", "value" => 1
                    ]);
                ?>
            </div>

            <div class="col-md-6 filter-container <?= empty($data['request'] -> input('ft')) ? 'd-none' : ''; ?>">
                <?php

                    $labelText = ($data['request'] -> input('ft') != 1) ? 'Percentage' : 'From Account';

                    // pr1
                    $markup = FormElements::generateLabel('pr1', $labelText);

                    $markup .= FormElements::generateInput([
                        "id" => "pr1", "name" => "pr1", 
                        "type" => "text", "value" => $data['request'] -> input('pr1')
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'pr1');
                ?>
            </div>

            <div class="col-md-6 filter-container <?= !in_array( $data['request'] -> input('ft'), [1] ) ? 'd-none' : ''; ?>">
                <?php
                
                    // pr2
                    $markup = FormElements::generateLabel('pr2', 'To Account');

                    $markup .= FormElements::generateInput([
                        "id" => "pr2", "name" => "pr2", 
                        "type" => "text", "value" => $data['request'] -> input('pr2')
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, 'pr2');
                ?>
            </div>

            <div class="clearfix w-100"></div>

            <div class="col-12">
                <?php echo FormElements::generateSubmitButton('search', ['id' => 'search_audit_units_btn', 'value' => 'Search'] ); ?>
            </div>
        </div>

    <?php echo FormElements::generateFormClose();  ?>

    </div>

</div>

<div class="card apcard mb-4">
    <div class="card-header">
        Accounts Dump Data
    </div>

    <div class="card-body">
        <?php if(is_array($data['data']['db_dump_data']) && sizeof($data['data']['db_dump_data']) > 0):

        if(!isset($data['data']['db_display_percentage']))
            $data['data']['db_display_percentage'] = sizeof($data['data']['db_dump_data']);

        echo FormElements::generateFormStart(["name" => "target-master", "action" => '' ]);

            echo '<p class="text-danger font-sm font-medium mb-2">Total accounts found with & without sampling: '. ceil($data['data']['db_display_percentage']) .'</p>' . "\n";

            $samplingCheck = true;

            require_once APP_VIEWS . '/audit/dump-common-markup.php';

            echo '<span id="samplingSubmitError" class="text-danger font-sm d-block mb-2 d-none">Please ensure that at least one account checkbox is selected.</span>' . "\n";

            echo FormElements::generateSubmitButton('', [ 'id' => 'samplingSubmit', 'name' => 'submit', 'value' => 'Apply / Fix Records'] );

        echo FormElements::generateFormClose();

        else: 
            echo $data['noti']::getCustomAlertNoti('noDataFound');
        endif; ?>
    </div>
</div>

<?php 

$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function(){

    if($("#ft").length > 0)
    {
        $("#ft").on("change", function() {

            $(".filter-container").addClass("d-none");

            if($(this).val() != \'\')
            {
                // first container
                $($(".filter-container")[0]).removeClass("d-none");

                if($(this).val() == 1)
                {
                    $($($(".filter-container")[0]).find("label")).text("From Account");
                    $($(".filter-container")[1]).removeClass("d-none");
                }
                else
                    $( $($(".filter-container")[0]).find("label") ).text("Percentage");
            }            
        });
    }

    if($("#checkAll").length > 0)
    {
        checked = 0;

        $("#checkAll").click(function() {
            checked = 1 - checked;
            $( ".acc-sampling-check" ).prop("checked", checked);
        });
    }

    if($("#samplingSubmit").length > 0 && $(".acc-sampling-check").length > 0)
    {
        $("#samplingSubmit").on("click", function() {

            if(!$(".acc-sampling-check:checked").length > 0)
            {
                $("#samplingSubmitError").removeClass("d-none");
                return false;
            }
            else
                $("#samplingSubmitError").addClass("d-none");
        });
    }

});

</script>';

?>