<?php 

use Core\FormElements; 

// display assesment details
echo generate_assesment_top_markup($data['data']['assesmentData']);

echo '<div class="card apcard rounded-0 mb-0">' . "\n";
    echo '<div class="card-header text-uppercase">Submit '. ($data['data']['assesmentData'] -> audit_status_id == 2 ? 'Audit' : ' Compliance') .' Review</div>' . "\n";

    echo '<div class="card-body">' . "\n";

        echo '<div class="table-repsonsive">' . "\n";
            echo '<table class="table table-bordered">' . "\n";
                echo '<tr>
                    <th class="text-center">Sr. No.</th>
                    <th>Observations</th>
                    <th class="text-center">Accepted</th>
                    <th class="text-center">Rejected</th>';
                    
                    if($data['data']['assesmentData'] -> audit_status_id == 5 || $data['data']['assesmentData'] -> audit_status_id == 15)
                    {
                        echo (isset(AUDIT_STATUS_ARRAY['compliance_review_action'][4]) ? '<th class="text-center">On Hold</th>' : '');
                        echo (isset(AUDIT_STATUS_ARRAY['compliance_review_action'][5]) ? '<th class="text-center">Carry Forward</th>' : '');
                    }
                    
                echo '</tr>' . "\n";

                $srNo = 1;

                foreach($data['data']['observation'] as $cObv => $cObvDetails)
                {
                    echo '<tr>' . "\n";

                        // sr no
                        echo '<td align="center">'. $srNo .'</td>' . "\n";

                        // title
                        echo '<td>'. $cObvDetails['title'] .'</td>' . "\n";

                        // accepted
                        echo '<td align="center">'. $cObvDetails['accepted'] .'</td>' . "\n";

                        // rejected
                        echo '<td align="center">'. $cObvDetails['rejected'] .'</td>' . "\n";

                        if($data['data']['assesmentData'] -> audit_status_id == 5 || $data['data']['assesmentData'] -> audit_status_id == 15)
                        {
                            echo (isset(AUDIT_STATUS_ARRAY['compliance_review_action'][4]) ? ('<td align="center">'. ($cObvDetails['onhold'] ?? 0) .'</td>') : '');
                            echo (isset(AUDIT_STATUS_ARRAY['compliance_review_action'][5]) ? ('<td align="center">'. ($cObvDetails['cf_point'] ?? 0) .'</td>') : '');
                        }

                    echo '</tr>' . "\n";

                    $srNo++;
                }

                // Add RO Observation row for RO Officer review (audit_status_id == 15)
                if($data['data']['assesmentData'] -> audit_status_id == 15 && isset($data['data']['ro_observation']['observation']))
                {
                    echo '<tr>' . "\n";
                        echo '<td align="center">'. $srNo .'</td>' . "\n";
                        echo '<td>'. ($data['data']['ro_observation']['observation']['title'] ?? 'RO Review Observations') .'</td>' . "\n";
                        echo '<td align="center">'. ($data['data']['ro_observation']['observation']['accepted'] ?? 0) .'</td>' . "\n";
                        echo '<td align="center">'. ($data['data']['ro_observation']['observation']['rejected'] ?? 0) .'</td>' . "\n";
                        
                        if($data['data']['assesmentData'] -> audit_status_id == 15)
                        {
                            echo (isset(AUDIT_STATUS_ARRAY['compliance_review_action'][4]) ? '<td align="center">0</td>' : '');
                            echo (isset(AUDIT_STATUS_ARRAY['compliance_review_action'][5]) ? '<td align="center">0</td>' : '');
                        }
                        
                    echo '</tr>' . "\n";
                }

            echo '</table>' . "\n";
        echo '</div>' . "\n";

        // 0 compliance points
        if( $data['data']['observation']['observation']['accepted'] == 0 &&
            $data['data']['observation']['observation']['rejected'] == 0 )
            echo $data['noti']::cError('Your current assessment has <b>0</b> compliance points. Are you sure you want to complete assessment?', 'warning');

        echo '<form class="text-center mb-0" action="" method="post">
            <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Review</button>
        </form>' . "\n";

    echo '</div>' . "\n";
echo '</div>' . "\n";

// send complete audit back
if(isset($data['data']['sendBack']) && $data['data']['assesmentData'] -> audit_status_id == 2)
{
    echo '<div class="card apcard rounded-0 mt-4 mb-0">' . "\n";
        echo '<div class="card-header text-uppercase">Send Complete Audit Back</div>' . "\n";

            echo '<div class="card-body">' . "\n";
                echo '<p class="lead text-danger mb-0">Send complete assesment back to audit department for audit</p>';
                echo '<p class="font-sm text-muted mb-2">Note: Only available once in every assesment period</p>';

                echo '<form class="mb-0" action="" method="post">
                    <button type="submit" name="send_back_audit" class="btn btn-primary">Send Back to Audit</button>
                </form>' . "\n";
            echo '</div>' . "\n";
        echo '</div>' . "\n";
    echo '</div>' . "\n";

}

?>