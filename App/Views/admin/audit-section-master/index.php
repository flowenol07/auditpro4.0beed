<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">

    <table id="sectionDataTable" class="table table-hover v-table dataTable mt-2">

    <thead>
        <tr>
            <th scope="col" class="nosort dtcol-80">Sr. No.</th>
            <th scope="col">Section</th>
            <th scope="col">Status</th>
            <th scope="col" class="nosort dtcol-160">Action</th>
        </tr>
    </thead>

    <tbody>
    </tbody>

    </table>

    </div>

<?php

$data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'sectionDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "name", "status", "action" ], 0, 1 );

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>