<?php 

echo generate_link_button('', [
    'value' => 'Bulk Upload Tasks',
    'href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/bulk-upload-circular-task'
]);

echo '<div class="w-100 mb-3"></div>';

if($data['data']['db_data_count'] > 0):

?>

<div class="table-responsive">

    <table id="circularSetDataTable" class="table table-hover v-table dataTable">

        <thead>
            <tr>
                <th scope="col" class="nosort dtcol-80">Sr. No.</th>
                <th scope="col" class="dtcol-80">Circular Name</th>
                <th scope="col">Task</th>
                <th scope="col">Risk Category</th>
                <th scope="col">Status</th>
                <th scope="col" class="nosort dtcol-100">Action</th>            
            </tr>
        </thead>

        <tbody></tbody>

    </table>

</div>

<?php
    $authQuery = $data['request'] -> has('auth') ? $data['request'] -> input('auth') : '';
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'circularSetDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/'. DATA_TABLE_AJX . ( !empty($authQuery) ? '?auth=' . $authQuery : '' ), [ "sr_no", "set_id", "task", "risk_category_id", "status", "action"], 0, 2);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>