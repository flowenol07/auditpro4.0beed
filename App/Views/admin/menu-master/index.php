<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">

    <table id="menuMasterDataTable" class="table table-hover v-table dataTable mt-2">

    <thead>
        <tr>
            <th scope="col">Section</th>
            <th scope="col">Menu Name</th>
            <th scope="col">Status</th>
            <th scope="col" class="nosort dtcol-160">Action</th>         
        </tr>
    </thead>

    <tbody></tbody>

    </table>

    </div>

<?php

$data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'menuMasterDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/'. DATA_TABLE_AJX, [ "section_type_id", "name", "status", "action" ] );

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>