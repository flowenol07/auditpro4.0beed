<?php 

use Core\FormElements; 

// display assesment details
echo generate_assesment_top_markup($data['data']['assesmentData']);

//has data
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

    $evi_markup = check_evidence_upload_strict('file_upload');

    if(!empty($evi_markup) && $evi_markup != 1)
        echo $evi_markup;
}

echo '<form class="text-center mb-4" action="" method="post">
    <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Compliance</button>
</form>' . "\n";

?>