<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "category-master", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php

                //menu_id
                $markup = FormElements::generateLabel('menu_id', 'Menu
                ');

                if(is_array($data['data']['db_menu']) && sizeof($data['data']['db_menu']) > 0)
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "menu_id", "name" => "menu_id", 
                        "default" => ["", "Please select menu"],
                        "options" => $data['data']['db_menu'],
                        "appendClass" => "select2search",
                        "selected" => $data['request'] -> input('menu_id', $data['data']['db_data'] -> menu_id)
                    ]);
                }
                else
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                echo FormElements::generateFormGroup($markup, $data, 'menu_id');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                //menu name
                $markup = FormElements::generateLabel('name', 'Category');

                $markup .= FormElements::generateInput([
                    "id" => "name", "name" => "name", 
                    "type" => "text", "value" => $data['request'] -> input('name', $data['data']['db_data'] -> name), 
                    "placeholder" => "Category"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'name');
            ?>
        </div>

        <div class="col-md-12 show_hide_div">
            <?php
                //is_cc_acc_category
                $markup = FormElements::generateCheckboxOrRadio([
                    'type' => 'checkbox', 'id' => 'is_cc_acc_category', 'name' => 'is_cc_acc_category', 
                    'value' => '1', 'text' => 'Is this CC Account Category',
                    'checked' => (($data['request'] -> input('is_cc_acc_category', $data['data']['db_data'] -> is_cc_acc_category) == 1 ) ? true : false), 
                    'customLabelClass' => 'font-medium text-primary'
                ]);
            
                echo FormElements::generateFormGroup($markup);
            ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Category'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Category';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

    $data['data']['inline_js'] = "\n" . '
<script>
    $(document).ready(function(){

        // for hide and show-------------
        function show_hide_container (val) {
            $(".show_hide_div").hide();
            if(val == ' . ENV_CONFIG['advances_id'] . ' )
            {
                $($(".show_hide_div")).show();
            }
        }

        $("#menu_id").change(function(){
            show_hide_container($(this).val());
        });
        
        show_hide_container($("#menu_id").val());
    });

</script>';

?>