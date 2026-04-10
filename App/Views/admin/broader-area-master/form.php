<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "audit-area-master", "action" => $data['me'] -> url ]);


$form_element_arr = [
    'name' => [
        'label' => 'Broader Area of Non-Compliance',
        'placeholder' => 'Broader Area',
        'col' => 'col-md-12'
    ],

    'appetite_percent' => [
        'label' => 'Risk Appetite',
        'placeholder' => 'Risk Appetite',
        'col' => 'col-md-6'
    ],

    'occurance_percent' => [
        'label' => 'Probability Of Occurance',
        'placeholder' => 'Probability Of Occurance %',
        'col' => 'col-md-6'
    ],

    'magnitude' => [
        'label' => 'Magnitude',
        'placeholder' => 'Magnitude',
        'col' => 'col-md-6'
    ],

    'frequency' => [
        'label' => 'Frequency',
        'placeholder' => 'Frequency',
        'col' => 'col-md-6'
    ],

    'average_qualitative_count' => [
        'label' => 'Average Qualitative Count',
        'placeholder' => 'Average Qualitative Count',
        'col' => 'col-md-6'

    ],

    'average_quantitative_count' => [
        'label' => 'Average Quantitative Count',
        'placeholder' => 'Average Quantitative Count',
        'col' => 'col-md-6'

    ],
]

?>
    <div class="row">
    <?php

    foreach($form_element_arr as $fIndex => $fValue)
    {
        echo '<div class=' . $fValue['col'] . '>'; 
        
                //appetite_percent	
                    $markup = FormElements::generateLabel($fIndex, $fValue['label']);

                    $markup .= FormElements::generateInput([
                        "id" => $fIndex, "name" => $fIndex, 
                        "type" => 'text', "value" => $data['request'] -> input($fIndex, $data['data']['db_data'] -> $fIndex), "placeholder" => $fValue['placeholder']
                    ]);

                    echo FormElements::generateFormGroup($markup, $data, $fIndex);

        echo '</div>';
    }
    ?>
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Broader Area'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Broader Area';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>