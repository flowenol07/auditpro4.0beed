<?php 

use Core\FormElements; 

// display assesment details
echo generate_compliance_asses_top_markup($data['data']['comAssesData']);

$mrk_str = '';
$timeline_status_array = null;
$compliancePointCount = true;
$gtMrk = '';

//has data
if( array_key_exists('data_array', $data['data']) && 
    is_array($data['data']['data_array']['ans_data']) && 
    sizeof($data['data']['data_array']['ans_data']) > 0 )
{
    $gtMrk = generate_table_markup($data, $data['data']['data_array'], $data['data']['filter_type']);
}
else
{
    $gtMrk = $data['noti']::cError('Your current assessment has <b>0</b> compliance points. Are you sure you want to complete assessment?', 'warning');
    $compliancePointCount = false;
}

if( array_key_exists('review_timeline_status', $data['data']) && 
    is_array($data['data']['review_timeline_status']) && 
    sizeof($data['data']['review_timeline_status']) > 0)
    $timeline_status_array = $data['data']['review_timeline_status'];

elseif( array_key_exists('review_timeline_status', $data['data']) && 
        is_array($data['data']['review_timeline_status']) && 
        sizeof($data['data']['review_timeline_status']) > 0)
        $timeline_status_array = $data['data']['review_timeline_status'];

if( is_array($timeline_status_array) && $compliancePointCount)
{
    /*$mrk_str .= '<div class="card apcard rounded-0 mb-4">' . "\n";
        $mrk_str .= '<div class="card-header text-uppercase">Observation Action</div>' . "\n";

        $mrk_str .= '<div class="card-body">' . "\n";
        
        $mrk_str .= FormElements::generateFormStart(["name" => "observation-action", "action" => '' ]);

            //user_type
            $markup = FormElements::generateLabel('observationAction', 'Observation Action');

            $markup .= FormElements::generateSelect([
                "id" => "observationAction", "name" => "observationAction", 
                "default" => ["", "Please select observation action"],
                "options" => $timeline_status_array,
                "selected" => $data['request'] -> input('observationAction')
            ]);        

            $mrk_str .= FormElements::generateFormGroup($markup, $data, 'observationAction');
            
            $mrk_str .= FormElements::generateSubmitButton('', [ 'name' => 'observationActionSubmit', 'value' => 'Save'] );

        $mrk_str .= FormElements::generateFormClose(); 

        $mrk_str .= '</div>' . "\n";

    $mrk_str .= '</div>' . "\n";*/
}

echo $mrk_str;

echo '<div class="card apcard rounded-0 mb-0">' . "\n";
    echo '<div class="card-header text-uppercase">Review Compliance</div>' . "\n";

    echo '<div class="card-body">' . "\n";

        echo $gtMrk;

        echo '<form class="mb-0" action="'. $data['siteUrls']::getUrl( 'complianceAssessmentReviewer' ) .'/submit-compliance-review" method="post">
            <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Compliance Review</button>
        </form>' . "\n";

    echo '</div>' . "\n";
echo '</div>' . "\n";

?>