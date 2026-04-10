<?php 

if($data['data']['db_data_count'] > 0):

?>
    <div class="table-responsive">
        <table id="annexureDataTable" class="table table-hover v-table dataTable">

            <thead>
                <tr>
                    <th scope="col" class='nosort'>Sr. No.</th>
                    <th scope="col">Annexure Name</th>
                    <th scope="col">Business Risk</th>
                    <th scope="col">Control Risk</th>
                    <th scope="col">Risk Defination</th>
                    <th scope="col">Status</th>
                    <th scope="col" class='nosort dtcol-200'>Action</th>
                </tr>
            </thead>

            <tbody></tbody>

        </table>
    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'annexureDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "name", "business_risk", "control_risk", "risk_defination_id", "status", "action"], 0, 1);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>