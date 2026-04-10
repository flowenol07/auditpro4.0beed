<?php 

if($data['data']['db_data_count'] > 0):

?>
<div class="table-responsive">
    <table id="questionSetDataTable" class="table table-hover v-table dataTable">

        <thead>
            <tr>
                <th scope="col" class="nosort">Sr. No.</th>
                <th scope="col">Set Type</th>
                <th scope="col">Set Name</th>
                <th scope="col">Status</th>
                <th scope="col" class="nosort dtcol-160">Action</th>            
            </tr>
        </thead>

        <tbody></tbody>

    </table>
</div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'questionSetDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "set_type_id", "name", "status", "action"], 0, 2);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>