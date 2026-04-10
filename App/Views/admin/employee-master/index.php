<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">

    <table id="empMasterDataTable" class="table table-hover v-table dataTable mt-2">

    <thead>
        <tr>
            <th scope="col">Emp. Code</th>
            <th scope="col" class="dtcol-240">Name</th>
            <th scope="col">Emp. Type</th>
            <th scope="col">Email</th>
            <th scope="col">Mobile</th>            
            <th scope="col">Status</th>
            <th scope="col" class="nosort dtcol-160">Action</th>
        </tr>
    </thead>

    <tbody></tbody>

    </table>

    </div>

<?php 

$data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'empMasterDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "emp_code", "name", "user_type_id", "email", "mobile", "status", "action" ]);

?>

<?php

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>