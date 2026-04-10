<?php 

require_once 'single-set-details-markup.php';

if($data['data']['db_data_count'] > 0):
?>

<div class="table-responsive">
    <table id="questionHeaderDataTable" class="table table-hover v-table">

        <thead>
            <tr>
                <th scope="col" class="nosort">Sr.No.</th>
                <th scope="col">Header Name</th>
                <th scope="col">Status</th>
                <th scope="col" class="nosort">Action</th>
            </tr>
        </thead>

        <tbody></tbody>

    </table>
</div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'questionHeaderDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "name", "status", "action"], 0,1,['set' => $data['request'] -> input('set')]);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;
?>