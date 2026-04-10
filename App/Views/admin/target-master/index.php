<?php 
require_once 'single-audit-details-markup.php';

if($data['data']['db_data_count'] > 0):

?>

<div class="table-responsive">
    <table id="targetMasterDataTable" class="table table-hover dataTable v-table">

    <thead>
        <tr>
            <th scope="col" width="150">Financial Year</th>
            <th scope="col">Deposit Target (In Lakhs)</th>
            <th scope="col">Advance Target (In Lakhs)</th>
            <th scope="col">NPA Target (In Lakhs)</th>
            <th scope="col" class="nosort">Action</th>
        </tr>
    </thead>

    <tbody>
    </tbody>

    </table>
</div>

<?php
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'targetMasterDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "year_id", "deposit_target", "advances_target", "npa_target", "action"], 1, 1, ['audit' => $data['request'] -> input('audit')]);
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>