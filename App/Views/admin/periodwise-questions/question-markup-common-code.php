<?php

use Core\FormElements;

function question_checkbox_markup($data, $checkbox = false)
{
    $res = [ 'markup' => '', 'question_count' => 0 ];

    if(is_array($data['data']['db_menu_data']) && sizeof($data['data']['db_menu_data']) > 0)
    {
        foreach($data['data']['db_menu_data'] as $cMenuId => $cMenuDetails)
        {
            $res['markup'] .= '<div class="card apcard rounded-0 mb-4">' . "\n";
                $res['markup'] .= '<div class="card-header pb-1 font-medium">' . "\n";
                    $res['markup'] .= string_operations( (($cMenuId != 'subset' ? 'Menu: ' : '') . $cMenuDetails -> name), 'upper') . "\n";
                $res['markup'] .= '</div>' . "\n";

                $res['markup'] .= '<div class="card-body">' . "\n";

                    $question_data_arr = !empty($data['data']['db_data'] -> question_ids) ? explode (",", $data['data']['db_data'] -> question_ids) : [];

                    if($cMenuId == 1)
                    {
                        // executive summary
                        $res['markup'] .= '<h6 class="font-medium text-primary mb-1">'. string_operations( $cMenuDetails -> name, 'upper' ) .'</h6>';
                        $res['markup'] .= '<p class="text-secondary font-sm mb-0">Executive Summary Custom Data (Basic Details, March Position, New Accounts Open)</p>';
                    }
                    else
                    {
                        if( isset($cMenuDetails -> category_data) && 
                            is_array($cMenuDetails -> category_data) && 
                            sizeof($cMenuDetails -> category_data) > 0 )
                        {
                            $srNo = 1;

                            foreach($cMenuDetails -> category_data as $cCatId => $cCatDetails)
                            {
                                $res['markup'] .= '<h6 class="font-medium text-primary mb-2">'. $srNo .'. <span class="text-decoration-underline">' . string_operations( (($cCatId != 'subset' ? 'Category: ' : '') . $cCatDetails -> name), 'upper' ) .'</span></h6>';

                                if( isset($cCatDetails -> set_data) && 
                                    is_array($cCatDetails -> set_data) && 
                                    sizeof($cCatDetails -> set_data) > 0 )
                                {
                                    $markup = '';

                                    foreach($cCatDetails -> set_data as $setId => $cSetData)
                                    {
                                        foreach($cSetData as $cHeaderId => $cHeaderData)
                                        {
                                            // display header
                                            $markup .= '<tr class="bg-light-gray">' . "\n";
                                                $markup .= '<th class="text-center" width="60">'. string_operations('Header', 'upper') .'</th>' . "\n";
                                                $markup .= '<th>'. string_operations($cHeaderData -> name, 'upper') .'</th>' . "\n";
                                            $markup .= '</tr>' . "\n";

                                            if( isset($cHeaderData -> db_questions) && 
                                                is_array($cHeaderData -> db_questions) && 
                                                sizeof($cHeaderData -> db_questions) > 0 )
                                            {
                                                $qSrNo = 1;

                                                foreach($cHeaderData -> db_questions as $cQuesId => $cQuesData):

                                                    $checked = false;
                        
                                                    if(in_array($cQuesId, $question_data_arr))
                                                        $checked = true;
                        
                                                    $markup .= '<tr>' . "\n";
                        
                                                        if($checkbox)
                                                        {
                                                            $checkboxMrk = FormElements::generateCheckboxOrRadio([
                                                                "appendClass" => 'multi-checkbox-ids d-inlin-block ms-4',
                                                                "checked" => $checked, "value" => $cQuesId,
                                                            ]);
                            
                                                            $markup .= '<td>'. $checkboxMrk .'</td>' . "\n";
                                                        }

                                                        $markup .= '<td'. (!$checkbox ? ' colspan="2"' : '') .'>'. $qSrNo . '. ' . $cQuesData -> question .'</td>' . "\n";

                                                        $qSrNo++;
                                                        $res['question_count']++;

                                                    $markup .= '</tr>' . "\n";

                                                endforeach;
                                            }
                                            else
                                            {
                                                $markup .= '<tr>' . "\n";
                                                    $markup .= '<td colspan="2">'. $data['noti']::getCustomAlertNoti('noDataFound') . '</td>' . "\n";
                                                $markup .= '</tr>' . "\n";
                                            }
                                        }
                                    }

                                    if(!empty($markup))
                                    {
                                        $res['markup'] .= '<table class="table table-bordered">' . "\n";
                                            $res['markup'] .= $markup;
                                        $res['markup'] .= '</table>' . "\n";
                                    }
                                    else
                                        $res['markup'] .= $data['noti']::getCustomAlertNoti('noDataFound');

                                }
                                else
                                    $res['markup'] .= $data['noti']::getCustomAlertNoti('noDataFound');

                                $srNo++;
                            }
                        }
                        else
                            $res['markup'] .= $data['noti']::getCustomAlertNoti('noDataFound');
                    }
                    
                $res['markup'] .= '</div>' . "\n";
            $res['markup'] .= '</div>' . "\n";
        }
    }

    return $res;
}

?>