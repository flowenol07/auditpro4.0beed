<?php

function gen_question($c_set, $c_set_details, $data = [],$option = null, $broaderArea = false)
{   
    $q_cnt = 0;

    foreach($c_set_details['header'] as $c_header => $c_header_details)
    {
        if( is_array($c_header_details['questions']) && sizeof($c_header_details['questions']) > 0):
                echo '<tr style="background-color:'. (($option == 'set') ? '#eff0db' : '#e3c7b6') .'; color: #5e3023">';
        
        
        echo '<th colspan="2">'. (($option == 'set') ? 'Subset' : 'Header Set') .' &raquo;</th>';

        if($broaderArea)
        {
            echo '<th colspan="7">'. (($option == 'set') ? ('Set Name: ' . $c_set_details['set_name'] . ' | ') : '') .  $c_header_details['header_name'] .'</th>';
        }
        else
        {
            echo '<th colspan="5">'. (($option == 'set') ? ('Set Name: ' . $c_set_details['set_name'] . ' | ') : '') .  (isset($c_header_details['header_name']) ? $c_header_details['header_name'] : '') .'</th>';
        }

        
        echo '</tr>';

        if($option == '')
        {
            if($broaderArea)
            {
                echo '<tr style="background-color:#f4f6f9">';
            }
            else
            {
                echo '<tr style="background-color: #f7f1e9; color: #5e3023">';
            }

                echo '<th rowspan="2">Set ID</th>';
                echo '<th rowspan="2" width="80">Question ID</th>';
                echo '<th rowspan="2">Question</th>';
                if($broaderArea)
                {
                    echo '<th rowspan="2">Broader Area</th>';
                    echo '<th rowspan="2">Risk Type</th>';  
                }
                echo '<th rowspan="2">Input Method</th>';
                echo '<th colspan="3" align="center">Risk Parameters</th>';
            echo '</tr>';
            if($broaderArea)
            {
                echo '<tr style="background-color:#f4f6f9">';
                echo '<th>Answer</th>';
            }
            else
            {
                echo '<tr style="background-color: #f7f1e9; color: #5e3023">';
                echo '<th>Risk Type</th>';
            }            
                echo '<th>Business Risk</th>';
                echo '<th>Control Risk</th>';
            echo '</tr>';
        }
        // print_r($c_header_details['questions']);

        foreach($c_header_details['questions'] as $c_que_id => $c_que_details)
        {
            $riskParameters = null;

            if( /*!in_array(string_operations($c_que_details['basis_for_mapping']), ['Subset', 'subset'])*/ true )
            {
                try
                {
                    //json decode
                    $riskParameters = json_decode($c_que_details -> parameters, 1);

                } 
                catch (Exception $e)
                {
                    //throw $th;
                    $riskParameters = null;
                }

                $qColspan = ($c_que_details -> option_id != 'na' && $c_que_details -> option_id != 4 && is_array($riskParameters) && sizeof($riskParameters) > 0) ? (' rowspan="' . sizeof($riskParameters) . '"') : '';
                $firstTr = 0;

                // print_r($c_que_details -> option_id);

                echo '<tr>';
                    echo '<td'. $qColspan .'>'. $c_set .'</td>';
                    echo '<td'. $qColspan .'>'. $c_que_id .'</td>';
                    echo '<td'. $qColspan .'>'. $c_que_details -> question .'</td>';
                if($broaderArea)
                {
                        //check broader exists or not
                    if(is_array($data['data']['broaderAreaArray']) && sizeof($data['data']['broaderAreaArray']) > 0 && $c_que_details -> area_of_audit_id != '' && array_key_exists($c_que_details -> area_of_audit_id, $data['data']['broaderAreaArray']))
                    {
                        echo '<td'. $qColspan .'>'. trim_str($data['data']['broaderAreaArray'][ $c_que_details -> area_of_audit_id] -> name) .'</td>';
                    }
                    else
                        echo '<td'. $qColspan .'>Error: No Borader Found!</td>';

                    if(is_array($data['data']['getAllRiskCategory']) && array_key_exists($c_que_details -> risk_category_id, $data['data']['getAllRiskCategory']))
                        echo '<td'. $qColspan .'>'. string_operations($data['data']['getAllRiskCategory'][ $c_que_details -> risk_category_id] -> risk_category, 'upper') .'</td>';
                    else
                        echo '<td'. $qColspan .'>Risk Category Not Found</td>';
                }
                    echo '<td'. $qColspan .'>'. $GLOBALS['questionInputMethodArray'][$c_que_details -> option_id]['title'] .'</td>';

                    //[{"riskType":"Yes","businessRisk":"4","controlRisk":"4"},{"riskType":"No","businessRisk":"1","controlRisk":"1"}]

                    if($c_que_details -> option_id != 'na' && $c_que_details -> option_id != 4 && (is_array($riskParameters) && sizeof($riskParameters) > 0))
                    {
                        foreach($riskParameters as $cRiskParameter)
                        {
                            if($firstTr)
                                echo '<tr>';
                            
                            echo '<td>'. string_operations($cRiskParameter['rt'], 'upper') .'</td>';
                            echo '<td>'. get_risk($cRiskParameter['br']) .'</td>';
                            echo '<td>'. get_risk($cRiskParameter['cr']) .'</td>';

                            $firstTr = 1;
                            echo '</tr>';
                        }
                    }
                    else { echo '<td>-</td><td>-</td><td>-</td>'; }

                echo '</tr>';

                $q_cnt++;
            }

            if( in_array($c_que_details -> option_id, [5]) )
            {   
                // echo $c_que_details -> header_name;
                // print_r($c_que_details -> subset_data);
                //has subset 
                if(is_array($c_que_details -> subset_data) && sizeof($c_que_details -> subset_data) > 0)
                {
                    //display set
                    foreach($c_que_details -> subset_data as $css_set => $css_set_details)
                    {
                        if($broaderArea)
                        {
                            //function call
                            gen_question($css_set, $css_set_details, $data, 'set');
                        }
                        else
                        {
                            //function call
                            gen_question($css_set, $css_set_details, 'set');
                        }

                        
                    }
                }
            }
        }
        endif;
    }

}

function get_risk($riskId)
{
    $returnVal = '-';

    switch($riskId)
    {
        case '1': {
            $returnVal = 'High Risk';
            break;
        }

        case '2': {
            $returnVal = 'Medium Risk';
            break;
        }

        case '3': {
            $returnVal = 'Low Risk';
            break;
        }

        case '4': {
            $returnVal = 'No Risk';
            break;
        }
    }

    return string_operations($returnVal, 'upper');
}

?>