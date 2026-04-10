<?php

require_once('common-code.php');

echo $data['noti']::getSessionAlertNoti();

echo generate_table_markup_assesment($data, false);

// echo '<div class="w-100 mt-2"></div>';

// check is blocked form
if(!($data['data']['db_data'] -> audit_status_id > 6) && $data['data']['db_data'] -> is_limit_blocked == 1):
    require_once 'remove-block-form.php';

// due expired audit
elseif(!($data['data']['db_data'] -> audit_status_id > 6) && in_array($data['data']['db_data'] -> audit_status_id, [ 
    ASSESMENT_TIMELINE_ARRAY[1]['status_id'], ASSESMENT_TIMELINE_ARRAY[2]['status_id'], ASSESMENT_TIMELINE_ARRAY[3]['status_id']
]) && strtotime($data['data']['db_data'] -> audit_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1])) ):
    require_once 'increase-due-date-form.php';


// due expired compliance
elseif(!($data['data']['db_data'] -> audit_status_id > 6) && in_array($data['data']['db_data'] -> audit_status_id, [ 
    ASSESMENT_TIMELINE_ARRAY[4]['status_id'], ASSESMENT_TIMELINE_ARRAY[5]['status_id'], ASSESMENT_TIMELINE_ARRAY[6]['status_id']
]) && strtotime($data['data']['db_data'] -> compliance_due_date) < strtotime(date($GLOBALS['dateSupportArray'][1]))):
    require_once 'increase-due-date-form.php';

endif;

?>