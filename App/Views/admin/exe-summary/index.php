<?php 
require_once 'single-audit-details-markup.php';

if($data['data']['db_data_count'] > 0):

?>

<div class="table-responsive">
    <table id="exeSummaryDataTable" class="table table-hover v-table">

    <thead>
        <tr>
            <th scope="col" width='180'>GL Type</th>
            <th scope="col"> March Position (In Lakhs)</th>
            <th scope="col" class="nosort">New Accounts</th>
            <th scope="col" width='100' class="nosort">Action</th>
        </tr>
    </thead>

    <tbody>
    </tbody>

    </table>

<?php
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'exeSummaryDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "gl_type_id", "march_position", "months", "action"], 1, 1, ['audit' => $data['request'] -> input('audit'), 'year' => $data['request'] -> input('year')]);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>