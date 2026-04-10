<?php 

if($data['data']['db_data_count'] > 0):

?>
<div class="table-responsive">
    <table id="multiLevelDataTable" class="table table-hover v-table">
        <thead>
            <tr>
                <th scope="col">Sr. No.</th>
                <th scope="col">Period Wise Details</th>
                <th scope="col">User Type</th>
                <th scope="col" class="nosort">Action</th>
            </tr>
        </thead>

        <tbody></tbody>

    </table>
</div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'multiLevelDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "audit_unit_id", "user_type_id", "action"], 1, 0);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>