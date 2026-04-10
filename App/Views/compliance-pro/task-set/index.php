<?php 

if($data['data']['db_data_count'] > 0):

require_once(APP_VIEWS . '\compliance-pro\task-set\task-set-filter.php');
?>

<div class="table-responsive">

    <table id="circularSetDataTable" class="table table-hover v-table dataTable">

        <thead>
            <tr>
                <th scope="col" class="nosort dtcol-80">Sr. No.</th>
                <th scope="col" class="dtcol-120">Circular Name</th>
                <th scope="col">Task Set Name</th>
                <th scope="col">Frequency</th>
                <th scope="col">Status</th>
                <th scope="col" class="nosort dtcol-100">Action</th>            
            </tr>
        </thead>

        <tbody></tbody>

    </table>

</div>

<?php

$url = $data["me"] -> url .'/' . DATA_TABLE_AJX;

$GETData = $_GET;

if(is_array($GETData)) {
    
    unset($GETData['url']);

   
    $GETData = !empty($GETData) ? ('?' . http_build_query($GETData)) : '';
}
else
    $GETData = '';

// assign get data
$url .= $GETData;

    // $authQuery = $data['request'] -> has('auth') ? $data['request'] -> input('auth') : '';
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'circularSetDataTable', $url, [ "sr_no", "circular_id", "name", "frequency", "status", "action"], 1, 2);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>