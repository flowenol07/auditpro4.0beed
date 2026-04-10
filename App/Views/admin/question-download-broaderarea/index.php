<?php 
// print_r($data['data']);

require_once(APP_VIEWS . '/admin/question-download/form.php');
require_once(APP_VIEWS . '/admin/question-download/function.php');

if((isset($data['data']['queData']) && sizeof($data['data']['queData']) > 0 && is_array($data['data']['queData'])))
{
    if((isset($data['data']['questionData']) && sizeof($data['data']['questionData']) > 0))
    {

    echo '<div class="table-responsive">';
        
        /*if($data['data']['section_type_id'] == 1)
        {
            echo 
            '<div class="card-header pb-1 font-medium text-uppercase text-center">
                BRANCH QUESTIONS
            </div>';
            $file_name = 'BRANCH QUESTIONS';
        }
        else
        {*/

            echo 
            '<div class="mt-3 pb-1 font-medium text-uppercase text-center">
                ' . $data['data']['section_details_obj'] -> name . ' ( Questionnaires )
            </div>';

            $file_name = $data['data']['fileName'];

        // }

        echo '<table id="exportToExcelTable" class="table table-bordered v-table" style="border-color: #d6aa90">';

                    $colspan = 9;
                    foreach($data['data']['queData'] as $c_menu_id => $c_menu_details)
                    {
                        echo '<tr>';
                            echo '<td colspan="'. $colspan .'" align="center" style="color:red"><b>Menu &raquo; '. $c_menu_details['menu_name'] .'</b></td>';
                        echo '</tr>';

                        if(isset($c_menu_details['category']))
                        {
                            //display category
                            foreach($c_menu_details['category'] as $c_category => $c_category_details)
                            {
                                echo '<tr style="background-color:#895737; color:#f3e9dc">';
                                echo '<td colspan="'. $colspan .'" align="center"><span class="font-medium">Category &raquo; '. $c_category_details['category_name'] .'</span></td>';
                        echo '</tr>';

                                //display set
                                foreach($c_category_details['set'] as $c_set => $c_set_details)
                                {
                                    //function call
                                    gen_question($c_set, $c_set_details, $data, null, true);
                                }
                
                                echo '<tr><td colspan="9" style="height:40px"></td></tr>';

                            }

                            //echo '<tr><td colspan="3">-</td></tr>';
                        }
                        else
                        {
                            echo '<tr>';
                                echo '<td colspan="9">ERROR: Category Not Found!!!</td>';
                            echo '</tr>';
                        }
                    }
                echo '</table>';
    echo '</div>';
    
    }
    else
    {
?>
    <div class='mt-4'>
        <?php echo $data['noti']::getCustomAlertNoti('noDataFound'); ?>
    </div>
<?php
    } 
}?>