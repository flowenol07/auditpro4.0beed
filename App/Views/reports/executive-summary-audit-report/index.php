<?php

echo '<div class="no-print mb-3">';
    require_once('form.php');
echo '</div>';

if(array_key_exists('assesmentData', $data['data']))
{

    echo '<div id="printContainer">' . "\n";

    // generate header function
    generate_report_header($data['data'], false, 0, true);

    if(!empty($data['data']['exeBasicData']))
    {
        require_once('formBasic.php');
    }

    if(!empty($data['data']['exeBranchData']))
    {
        require_once('formBranchPosition.php');
    }

    if(!empty($data['data']['exeFreshData']))
    {
        require_once('formFreshAccount.php');
    }

    echo '</div>' . "\n";
}
?>