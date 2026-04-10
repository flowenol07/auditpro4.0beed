<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">

        <table id="circularHeaderDataTable" class="table table-hover v-table dataTable">

            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Circular Name</th>
                    <th scope="col">Header Name</th>
                    <th scope="col" class="nosort dtcol-80">Action</th>         
                </tr>
            </thead>

            <tbody></tbody>

        </table>

    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'circularHeaderDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/'. DATA_TABLE_AJX, [ "sr_no", "circular_set_id", "name", "action"], 0, 2);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>