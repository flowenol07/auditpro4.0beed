<?php

use Core\FormElements;

require_once 'single-audit-details-markup.php';

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "exe-summary", "action" => $data['me'] -> url ]);

?>
    <div class="row">
        <div class="col-md-12">
            <?php
                //gl_type_id
                $markup = FormElements::generateLabel('gl_type_id', 'Select GL Type');

                if(is_array(BRANCH_FINANCIAL_POSITION) && sizeof(BRANCH_FINANCIAL_POSITION) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "gl_type_id", "name" => "gl_type_id", 
                        "default" => ["", "Please select GL type"],
                        "options" => $data['data']['gl_type'],
                        "selected" => $data['request'] -> input('gl_type_id', $data['data']['db_data'] -> gl_type_id)
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'gl_type_id');
            ?>
        </div>

        <div class="col-12">    
            <?php

                //march_position
                $markup = FormElements::generateLabel('march_position', 'Last March Position (In Lakhs)');

                $markup .= FormElements::generateInput([
                    "id" => "march_position", "name" => "march_position", 
                    "type" => "text", "value" => $data['request'] -> input('march_position', $data['data']['db_data'] -> march_position), 
                    "placeholder" => "Enter Amount in Lakhs (xxxx.xx)"
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'march_position');

            ?>
        </div>
        
        <div id="months" class="row hide">

        <?php
        for($i = 4 ; $i < 16; $i++)
        {           
            echo '<div class="col-3">';
            if($i <= 12)
            {
                $m = $i;
                $year = $data['data']['db_single_year'] -> year;
            }
            else
            {
                $m = $i - 12;
                $year = ($data['data']['db_single_year'] -> year ) + 1;
            }

            $month = date("M", strtotime("0000-" . $m . "-01"));

            $markup = FormElements::generateLabel('m_' . $m, $month .' '. $year);
            $var = "m_" . $m;
            $markup .= FormElements::generateInput([
                "id" => $var, "name" => $var, 
                "type" => "text", "value" => $data['request'] -> input($var, $data['data']['db_data'] -> $var ), 
                "placeholder" => "Enter New Accounts"
            ]);

            echo FormElements::generateFormGroup($markup, $data, $var);  
            echo '</div>';
        
        }
        ?>
        </div>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add March Position'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update March Position';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 
?>

<?php
$data['data']['inline_js'] = "\n" . "
    <script>

    $(document).ready(function (){
        // for hide and show-------------
        function show_hide_container (val) {
            $('#months').show();
            
            let value = $('#gl_type_id option:selected').text();

            if(value.includes('(NPA)'))
                $('#months').hide();
        }

        $('#gl_type_id').change(function(){
            show_hide_container($(this).val());
        });

        show_hide_container($('#gl_type_id').val());
    });
    
    </script>";

?>