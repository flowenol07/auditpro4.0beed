<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">
        <table id="riskControlMasterDataTable" class="table table-hover v-table dataTable">

            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Control Risk</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="nosort">Action</th>
                </tr>
            </thead>

            <tbody>
            </tbody>

        </table>
    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'riskControlMasterDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "name", "status", "action"], 1, 0);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>