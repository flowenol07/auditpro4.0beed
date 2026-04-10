<?php 
 
require_once 'audit-common-code.php';

$pendingCnt = 0;
$complianceCnt = 0;

// display assesment details
echo generate_assesment_top_markup($data['db_assesment']);

// function call
end_assesment_form_markup_generate($data, [
    'compliance_cnt' => $complianceCnt,
    'pending_cnt' => $pendingCnt,
]);

?>