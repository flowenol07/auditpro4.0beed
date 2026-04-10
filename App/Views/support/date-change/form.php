<?php

use Core\FormElements;

echo FormElements::generateFormStart(["name" => "audit-complete-report", "action" => "" ]);
?>

<div class="row">
    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-units.php'; ?>
    </div>

    <div class="col-md-6">
        <?php require_once REPORTS_VIEW . DS . '_report-partials/audit-assesments.php'; ?>
    </div>
</div>


<?php

    $btnArray = array('find');

    if(array_key_exists('assesmentData', $this -> data))
        array_push($btnArray, 'reset');
    
    generate_report_buttons($btnArray);
?>