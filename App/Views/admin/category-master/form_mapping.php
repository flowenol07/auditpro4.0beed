<div class="card rounded-0">
  <div class="card-header pb-1 font-medium">
    Current Category Details
  </div>
  <div class="card-body">
    <h5 class="card-title font-medium text-primary mb-1"><?= $data['data']['db_data'] -> name ?></h5>
    <p class="mb-0 text-secondary font-sm">Status: <?= check_active_status($data['data']['db_data'] -> is_active); ?>, Created: <?= date($GLOBALS['dateSupportArray'][2], strtotime($data['data']['db_data'] -> created_at)) ?></p>
  </div>
</div>

<div class='border bg-white p-4 mt-4'>

<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "category-master", "action" => $data['me'] -> url , "appendClass" => "multi-checkbox-check-form" ]);

?>
    <div class="col-md-12 mb-1">
            <?= FormElements::generateLabel('question_set_ids', 'Question Set for Mapping') ?>
    </div>

    <?php
      $questionSetBool = (is_array($data['data']['db_mainset']) && sizeof($data['data']['db_mainset']) > 0);
    ?>

        <div class="col-md-12 <?php echo ($questionSetBool ? 'height-400' : '') ?>">
            <?php
            if($questionSetBool)
            {
                // checkboxes for employee
                
                $question_data_arr = $data['request'] -> input('question_set_ids', $data['data']['db_data'] -> question_set_ids);

                if(!is_array($question_data_arr))
                    $question_data_arr = !empty($question_data_arr) ? explode (",", $question_data_arr) : [];

                echo '<table class="table table-bordered">
                        <tr>';

                    // function call
                    echo generate_multiple_checkboxes($data['data']['db_mainset'], $question_data_arr, null, 'questionSet');

                echo '</tr>
                    </table>';
            }
            else
                echo $data['noti']::getCustomAlertNoti('noDataFound');
            ?>
        </div>

        <div class="col-md-12">
            <?php
                echo $data['noti']::getInputNoti($data['request'], 'question_set_ids_err');

                    echo FormElements::generateInput([
                        "id" => "multi_type_check", "name" => "question_set_ids", 
                        "type" => "hidden", "value" => '' ]);

                    echo "<div class='mb-2'></div>";
            ?>
        </div>

  <?php
    if($questionSetBool)
    {
      $btnArray = [ 'name' => 'submit', 'value' => 'Save Question Mapping', 'btn_type' => 'update'];     

          echo FormElements::generateSubmitButton('update', $btnArray );
          
          echo FormElements::generateFormClose();

          echo "</div>";

      echo "<div class='mb-2'></div>";
    }
?>

