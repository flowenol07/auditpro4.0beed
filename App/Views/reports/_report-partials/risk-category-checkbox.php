<?php

use Core\FormElements;

    echo '<label for="reportAuditUnit" class="form-label font-medium d-block mb-1">Risk Category</label>' . "\n";

    if( isset($data['data']['risk_category_data']) && 
        is_array($data['data']['risk_category_data']) && 
        sizeof($data['data']['risk_category_data']) > 0):

        echo '<table class="table table-bordered mb-0">';

            $col = 0;
            $submittedRiskCategoryArr = (  $data['request'] -> has('risk_category_arr') && 
                                        is_array($data['request'] -> input('risk_category_arr')) ) ? $data['request'] -> input('risk_category_arr') : [];

            foreach($data['data']['risk_category_data'] as $cRiskId => $cRiskDetails) 
            {
                if($col % 2 == 0)
                    echo '<tr>' . "\n";

                echo '<td>' . "\n";
                    echo '<div class="custom-control custom-checkbox">
                        <input class="custom-control-input risk-category-checkboxes" type="checkbox" id="rc'. $cRiskId .'" name="risk_category_arr[]" value="'. $cRiskId .'"'. ( in_array($cRiskId, $submittedRiskCategoryArr) ? ' checked="checked"' : '' ) .'>
                        <label for="rc'. $cRiskId .'" class="custom-control-label">'. strtoupper($cRiskDetails -> risk_category) .'</label>
                    </div>';
                echo '</td>' . "\n";
                    
                $col++;

                if($col % 2 == 0) echo '</tr>' . "\n";
            }

            if($col % 2 == 1)
                echo '</tr>' . "\n";

        echo '</table>';

    else: 
        echo '<p>Error: No Risk Category Found</p>'; 
    endif;

?>