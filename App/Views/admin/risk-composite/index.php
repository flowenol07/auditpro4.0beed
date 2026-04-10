<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">  
        <table id="riskCompositeDataTable" class="table table-hover v-table dataTable mt-2">
            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Business Risk</th>
                    <th scope="col">Control Risk</th>
                    <th scope="col">Composite Risk</th>
                    <th scope="col" class="nosort dtcol-160">Action</th>
                </tr>
            </thead>

            <tbody>
            </tbody>

        </table>
    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'riskCompositeDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "business_risk", "control_risk", "name", "action"], 0, 1);
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>