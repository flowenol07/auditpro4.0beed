<?php 

function generate_branch_and_fresh($data, $branchPosition, $freshAccount) 
{
    $alpha = 0;

    $glTypeArray = ($freshAccount == 1) ? BRANCH_FRESH_ACCOUNTS : BRANCH_FINANCIAL_POSITION;
    $sortedGLTypeArray = $data['data'][ ($freshAccount == 1) ? 'gl_type_bfa' : 'gl_type_bfp' ];

    foreach( $glTypeArray as $cGlType => $cGlTypeDetails)
    {
        $alphaIndex = chr(ord('A') + $alpha);
        $alpha++;
        $j = 1;
        $mrk = '';
        $total_march = 0;
        $total_current = 0;

        foreach($cGlTypeDetails as $cGlTypeId => $cGlTypeName)
        {
            $gl_name = str_ireplace("(" . ucfirst($cGlType) .")", "", (isset($sortedGLTypeArray[ $cGlTypeId ]) ? $sortedGLTypeArray[ $cGlTypeId ] : ERROR_VARS['notFound']));

            $gl_input_branch_name = 'branch_position_type_' . $cGlTypeId;

            $gl_input_class = string_operations(str_replace(" ","_", $cGlType)) . '_current_value';

            $gl_input_branch_comp_comment_name = 'branch_position_comment_type_' . $cGlTypeId;


            // Total Calculation for Branch Position
            if($branchPosition)
            {
                $total_march += isset($data['data']['db_march_position'][$cGlTypeId]) ? $data['data']['db_march_position'][$cGlTypeId] : '0.00';

                $total_current += (is_array($data['data']['exeBranchData']) && array_key_exists($cGlTypeId, $data['data']['exeBranchData'])) ? $data['data']['exeBranchData'][$cGlTypeId] -> amount : '0.00';

                $total_ytd = $total_current - $total_march;

                $march_value = isset($data['data']['db_march_position'][$cGlTypeId]) ? $data['data']['db_march_position'][$cGlTypeId] : '0.00';
            }
            
            // Common <td></td>  tag for all
            $mrk .= '
            <tr data-typeid= ' . $cGlTypeId . '>
                <td style="width:'. ($freshAccount ? '10' : '5') .'%" class="text-center">' . $j .'</td>
                <td style="width:'. ($freshAccount ? '20' : '15') .'%">' . $gl_name . '</td>';
    
            // Branch Position Start --------------------------------------------

            if($branchPosition)
            {
                $mrk .= '
                    <td style="width:20%" class="text-center">' . $march_value .'</td>

                    <td style="width:20%" class="text-center ' . $gl_input_class . '" id="' . $gl_input_branch_name . '">' . (isset($data['data']['exeBranchData'][$cGlTypeId] -> amount) ? $data['data']['exeBranchData'][$cGlTypeId] -> amount : 0.00)  . '</td>

                    <td style="width:20%"></td>';
                
                    // <td> only for compliance report = 2
                    if($data['data']['report_type'] == 2)
                    {
                        $mrk .= '<td style="width:20%">' . (isset($data['data']['exeBranchData'][$cGlTypeId] -> audit_commpliance) ? $data['data']['exeBranchData'][$cGlTypeId] -> audit_commpliance : "") . '</td>';
                    }
            }

            // Fresh Account Start --------------------------------------------

            if($freshAccount)
            {        
                $mrk .= '<td style="width:40%" class="text-center" id="' . $gl_input_branch_name . '">' . (isset($data['data']['exeFreshData'][$cGlTypeId] -> accounts) ? $data['data']['exeFreshData'][$cGlTypeId] -> accounts : 0) . '</td>';
                    
                    // <td> only for compliance report = 2
                    if($data['data']['report_type'] == 2)
                    {
                        $mrk .= '<td style="width:20%">' . (isset($data['data']['exeFreshData'][$cGlTypeId] -> audit_commpliance) ? $data['data']['exeFreshData'][$cGlTypeId] -> audit_commpliance : "") . '</td>';
                    }
                    
            }                
                $mrk .= '</tr>';
            $j++;
        }

        echo "<tr class='bg-light-gray'>
                <td style='width:". ($freshAccount ? '10' : '5') ."%' class='text-primary text-center'><strong>" . $alphaIndex . "</strong></td>
                <td style='width:". ($freshAccount ? '20' : '15') ."%' class='text-primary'><strong>" . string_operations($cGlType, 'upper') . "</strong></td>";

                if($branchPosition == 1)
                {
                    echo "<td style='width:20%' class='text-center text-primary'>
                            <strong id= '" . string_operations($cGlType) . "_total'>" . get_decimal($total_march, 2) ."</strong>
                        </td>

                        <td style='width:20%' class='text-center text-primary'>
                            <strong id='" . string_operations($cGlType) . "_total_current'>" . get_decimal($total_current, 2) . "</strong>
                        </td>

                        <td style='width:20%' class='text-center text-primary'>
                            <strong id='ytm_id_" . string_operations($cGlType) . "'>" . get_decimal($total_ytd, 2) ."</strong>
                        </td>";
                }
                
                elseif($freshAccount == 1) {
                    echo"<td style='width:40%'></td>";                    
                }

                if($data['data']['report_type'] == 2) {
                    echo"<td style='width:20%'></td>";
                }
        echo"</tr>"; 
            
        echo $mrk;
    }
}
?>