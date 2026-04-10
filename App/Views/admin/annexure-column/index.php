<?php 

require_once 'single-annexure-details-markup.php';

if($data['data']['db_data_count'] > 0):
?>
    <div class="table-responsive">
        <table id="annexureColDataTable" class="table table-hover v-table dataTable">

            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Column Name</th>
                    <th scope="col">Column Type</th>
                    <th scope="col" class="nosort">Options</th>
                    <th scope="col" class="nosort">Action</th>
                </tr>
            </thead>

            <tbody></tbody>
        </table>
    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'annexureColDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "name", "column_type_id", "options", "action"], 0,1,['annex' => $data['request'] -> input('annex')]);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;
?>