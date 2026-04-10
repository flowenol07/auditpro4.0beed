<?php

if(!empty($data['data']['category_data'])):

    echo '<div class="no-print mb-3">';
        require_once('form.php');
    echo '</div>';

    echo '<div id="printContainer">' . "\n";
    generate_report_header($data['data']);

?>
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered v-table exportToExcelTable">
            <thead>
                <tr class="bg-light-gray">
                    <th style="width:10%" class="text-center">Category Id</th>
                    <th style="width:10%">Category Name</th>
                    <th style="width:10%" class="text-center">Menu Id</th>
                    <th style="width:10%">Menu Name</th>
                    <th style="width:10%">Data Linked</th>
                    <th style="width:20%">Scheme's Mapped</th>
                    <th style="width:20%">Name Of Question Sets Mapped</th>
                    <th style="width:10%" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($data['data']['category_data'] as $cKey => $cCategoryData)
                    {   echo
                        '<tr>
                            <td style="width:10%" class="text-center">' . $cCategoryData -> id . '</td>
                            <td style="width:10%">'. $cCategoryData -> name .'</td>

                            <td style="width:10%" class="text-center">'. $cCategoryData -> menu_id .'</td>

                            <td style="width:10%">' . (isset($data['data']['menu_data'][$cCategoryData -> menu_id]) ? ($data['data']['menu_data'][$cCategoryData -> menu_id]) : ERROR_VARS['notFound']) .'</td>
                            <td style="width:10%">' . (($cCategoryData -> linked_table_id == 0) ? 'NO' : 'YES') .'</td>

                            <td style="width:20%">';
                            
                            if(isset($cCategoryData -> scheme_data) && is_array($cCategoryData -> scheme_data))
                            {
                                $mapped_sche = '';
                                foreach($cCategoryData -> scheme_data as $dScheId => $dScheData)
                                {
                                    $mapped_sche .= '<span class="d-inline-block">{ ' . $cCategoryData -> scheme_data[$dScheId] -> name . ' ('. $cCategoryData -> scheme_data[$dScheId] -> scheme_code .') }</span><br><br>';
                                }
                                echo substr(trim_str($mapped_sche), 0, -1);
                            }
                            else    
                                echo '-';
                            
                            echo '</td>
                            <td style="width:20%">'; 

                                if(is_array(explode(',',$cCategoryData -> question_set_ids)) && sizeof(explode(',',$cCategoryData -> question_set_ids)) > 1)
                                {
                                    $ques_set = '';
                                    foreach(explode(',',$cCategoryData -> question_set_ids) as $bKey => $bQuesSet)
                                    {
                                        $ques_set .= '<span class="d-inline-block">{ ' . $data['data']['set_select_data'][$bQuesSet] . ' ('. $bQuesSet .') }</span><br><br>';
                                    }
                                    echo substr(trim_str($ques_set), 0, -1);
                                }
                                else
                                    echo ERROR_VARS['notFound'];
                            echo'</td>
                            <td style="width:10%" class="text-center">' . check_active_status($cCategoryData -> is_active) .'</td>
                        </tr>';
                    }
                ?>
            </tbody>
        </table>
    </div>

<?php

    echo '</div>' . "\n";

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');;
endif;
?>