<?php 

if($data['data']['db_data_count'] > 0):

?>
<div class="table-responsive">
    <table id="circularSetDataTable" class="table table-hover v-table dataTable">

        <thead>
            <tr>
                <th scope="col" class="nosort">Sr. No.</th>
                <th scope="col">Authority</th>
                <th scope="col">Circular Description</th>
                <th scope="col">Date</th>
                <th scope="col">Status</th>
                <th scope="col" class="nosort dtcol-200">Action</th>            
            </tr>
        </thead>

        <tbody></tbody>

    </table>
</div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'circularSetDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "authority_id", "name", "circular_date", "status", "action"], 0, 2);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>