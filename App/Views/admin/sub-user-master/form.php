<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart(["name" => "sub-user-master", "action" => $data['me']->url]);
?>
<div class="row">

    <div class="col-md-6">
        <div id="username">
            
            <?php
                //name
                $markup = FormElements::generateLabel('type_of_salary', 'Type of Salary');
                $markup .= FormElements::generateSelect([
                    "id" => "type_of_salary",
                    "name" => "type_of_salary",
                    "default" => ["", "Please select user base"],
                    "options" => $data['data']['adminArray'],
                    "selected" => $data['request']->input('type_of_salary', $data['data']['db_data']->type_of_salary)
                ]);

                echo FormElements::generateFormGroup($markup, $data, 'type_of_salary');
            ?>

            <?php
                //name
                $markup = FormElements::generateLabel('in_hand_salary', 'In Hand Salary');

                $markup .= FormElements::generateInput([
                    "id" => "in_hand_salary",
                    "name" => "in_hand_salary",
                    "type" => "number",
                    "value" => $data['request']->input('in_hand_salary', $data['data']['db_data']->in_hand_salary),
                    "placeholder" => "Enter In Hand Salary"
                ]);
                echo FormElements::generateFormGroup($markup, $data, 'in_hand_salary');
            ?>
        </div>
    </div>

    <div class="col-md-6">
        <?php
            //name
            $markup = FormElements::generateLabel('tax_dedications', 'Salary Deduction');

            $markup .= FormElements::generateInput([
                "id" => "tax_dedications",
                "name" => "tax_dedications",
                "type" => "number",
                "value" => $data['request']->input('tax_dedications', $data['data']['db_data']->tax_dedications),
                "placeholder" => "Enter Salary Deduction"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'tax_dedications');
        ?>

        <?php
            //name
            $markup = FormElements::generateLabel('provide_funds', 'PF Deduction');

            $markup .= FormElements::generateInput([
                "id" => "provide_funds",
                "name" => "provide_funds",
                "type" => "number",
                "value" => $data['request']->input('provide_funds', $data['data']['db_data']->provide_funds),
                "placeholder" => "Enter PF Deduction"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'provide_funds');
        ?>

    </div>
</div>

<?php

$btnArray = ['name' => 'submit', 'value' => 'Add Record'];

if ($data['data']['btn_type'] == 'update') {
    $btnArray['value'] = 'Update Record';
    echo FormElements::generateSubmitButton('update', $btnArray);
} else
    echo FormElements::generateSubmitButton('add', $btnArray);

echo FormElements::generateFormClose();