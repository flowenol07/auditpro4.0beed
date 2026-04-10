<?php

echo '<div class="no-print mb-3">';
   require_once('form.php');
echo '</div>';

if(isset($data['data']['questions_data']))
{
    if(sizeof($data['data']['questions_data']) > 0):  
    
echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data']);
?>

    <p class="font-medium lead">Question Set : <?= (isset($data['data']['set_data'][$data['data']['questions_data'][0] -> set_id] -> name) ? $data['data']['set_data'][$data['data']['questions_data'][0] -> set_id] -> name : ERROR_VARS['notFound']) ?></p>

    <table class="table table-bordered v-table exportToExcelTable">
        <thead>
            <tr class="bg-light-gray">
                <th style="width:20%">Set Name</th>
                <th style="width:20%">Header Name</th>
                <th style="width:10%" class="text-center">Question ID</th>
                <th style="width:20%">Question Description</th>
                <th style="width:10%">Input Method</th>
                <th style="width:20%">Risk Parameters</th>
            </tr>
        </thead>

        <tbody>
            <?php
                foreach($data['data']['questions_data'] as $cKey => $cQuestionData)
                {   
                    echo
                    '<tr>
                        <td style="width:20%">'. (isset($data['data']['set_data'][$cQuestionData -> set_id]) ? $data['data']['set_data'][$cQuestionData -> set_id] -> name .' [ ' . $cQuestionData -> set_id . ' ]'  : ERROR_VARS['notFound'] ) .'</td>

                        <td style="width:20%">'. (isset($data['data']['header_data'][$cQuestionData -> header_id]) ? $data['data']['header_data'][$cQuestionData -> header_id] -> name .' [ ' . $cQuestionData -> header_id . ' ]' : ERROR_VARS['notFound'] ) .'</td>
                        
                        <td style="width:10%" class="text-center">'. $cQuestionData -> id .'</td>
                        <td style="width:20%">'. $cQuestionData -> question .'</td>

                        <td style="width:10%">'. (isset($GLOBALS['questionInputMethodArray'][$cQuestionData -> option_id]) ? $GLOBALS['questionInputMethodArray'][$cQuestionData -> option_id]['title'] : ERROR_VARS['notFound']) .'</td>

                        <td style="width:20%">';

                        $parameters = null;
                        
                        if( isset($cQuestionData -> parameters) && 
                            !empty($cQuestionData -> parameters))
                            $parameters = json_decode($cQuestionData -> parameters);

                        if( is_array($parameters) && 
                            sizeof($parameters) > 0 )
                        {
                            echo'<table class="table table-bordered v-table mb-0">
                                    <tr class="bg-light-gray">
                                        <th>Risk Type</th>
                                        <th>Business Risk</th>
                                        <th>Control Risk</th>
                                    </tr>';

                                foreach($parameters as $cKey => $cData)
                                {
                                    echo '<tr>
                                        <td>' . $cData -> rt . '</td>

                                        <td>' . (isset(RISK_PARAMETERS_ARRAY[$cData -> br]['title']) ? RISK_PARAMETERS_ARRAY[$cData -> br]['title'] : ERROR_VARS['notFound']) . '</td>

                                        <td>' . (isset(RISK_PARAMETERS_ARRAY[$cData -> cr]['title']) ? RISK_PARAMETERS_ARRAY[$cData -> cr]['title'] : ERROR_VARS['notFound']) . '</td>
                                    </tr>';
                                }
                                    
                                echo'</table>';
                        }
                        else
                            echo "";

                        echo'</td>
                    </tr>';
                }
            ?>
        </tbody>
    </table>
    
</div>

<?php 

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif; 

}

?>