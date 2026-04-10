<?php 

use Core\FormElements; 

$extra = [];

if( isset($data['data']['circular_data_show']) && 
    isset($data['data']['circular_data']) && 
    is_object($data['data']['circular_data']))
{
    $extra['circular_data_show'] = 1;
    $extra['circular_data'] = $data['data']['circular_data'];
}

// display assesment details
echo generate_compliance_asses_top_markup($data['data']['comAssesData'], 1, $extra);

// has data
if( array_key_exists('data_array', $data['data']) && 
    is_array($data['data']['data_array']['ans_data']) && 
    sizeof($data['data']['data_array']['ans_data']) > 0 )
{
    echo '<div class="card apcard mb-4">' . "\n";
        echo '<div class="card-header">'. $data['me'] -> pageHeading .'</div>' . "\n";

        echo '<div class="card-body">' . "\n";
            echo generate_table_markup($data, $data['data']['data_array'], $data['data']['filter_type']);
        echo '</div>' . "\n";
        
    echo '</div>' . "\n";
}

echo '<form class="text-center mb-4" action="" method="post">
    <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Compliance</button>
</form>' . "\n";

// doc upload form // function call
echo generate_hidden_docs_upload_form();

?>