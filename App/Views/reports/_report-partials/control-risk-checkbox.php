<?php

use Core\FormElements;

    echo '<label class="form-label font-medium d-block mb-1">Control Category</label>' . "\n";

    if(1):

        echo '<table class="table table-bordered mb-0">';

            $col = 0;
            $submittedControlRiskArr = (  $data['request'] -> has('control_risk_arr') && 
                                          is_array($data['request'] -> input('control_risk_arr')) ) ? $data['request'] -> input('control_risk_arr') : [];     

            foreach(RISK_PARAMETERS_ARRAY as $cRiskId => $cRiskDetails)
            {
                if($col % 2 == 0)
                    echo '<tr>' . "\n";

                echo '<td>' . "\n";
                    echo '<div class="custom-control custom-checkbox">
                        <input class="custom-control-input risk-category-checkboxes" type="checkbox" id="cr'. $cRiskId .'" name="control_risk_arr[]" value="'. $cRiskId .'"'. ( in_array($cRiskId, $submittedControlRiskArr) ? ' checked="checked"' : '' ) .'>
                        <label for="cr'. $cRiskId .'" class="custom-control-label">'. strtoupper($cRiskDetails['title']) .'</label>
                    </div>';
                echo '</td>' . "\n";
                    
                $col++;

                if($col % 2 == 0) echo '</tr>' . "\n";
            }

            if($col % 2 == 1)
                echo '</tr>' . "\n";

        echo '</table>';

    else: 
        echo '<p>Error: No Risk Parameter Found</p>'; 
    endif;

?>