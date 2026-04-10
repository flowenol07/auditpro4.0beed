<?php 

require_once 'single-risk-details-markup.php';

if($data['data']['db_data_count'] > 0):
?>
    <div class="table-responsive">
        <table id="riskCategoryWeightDataTable" class="table table-hover dataTable v-table">
            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Financial Year</th>
                    <th scope="col">Risk Weight</th>
                    <th scope="col">Risk Appetite</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="nosort dtcol-160">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>

        </table>
    </div>
    

<?php
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'riskCategoryWeightDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "year_id", "risk_weight", "risk_appetite_percent", "status", "action"], 1, 1, ['rc' => $data['request'] -> input('rc')] );
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;
?>