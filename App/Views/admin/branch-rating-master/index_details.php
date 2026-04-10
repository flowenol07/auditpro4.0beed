<?php 
use Core\FormElements;

if($data['data']['db_data_count'] > 0):

?>
<div class="table-responsive">
    <table id="branchRatingDataTable" class="table table-hover v-table dataTable">

        <thead>
            <tr>
                <th>Audit Unit</th>
                <th>Financial Year</th>
                <th>Audit Type</th>
                <th scope="col" class="nosort">Action</th>
            </tr>
        </thead>

        <tbody>
        </tbody>

    </table>
</div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'branchRatingDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "audit_unit_id", "year_id", "audit_type_id", "action"], 0, 0, ['yearId' => $data['request'] -> input('yearId')]);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
?>
    <div class="row">
        <div class="col-md-6">
            <a href="<?php echo $data['siteUrls']::getUrl($data['me'] -> id) . '/add?yearId=' . $data['request'] -> input('yearId'). '&bulkData=1' ?>" class="btn btn-success icn-grid icn-bf icn-add">Add Bulk Branch Rating Data</a>
        </div>        
    </div>

<?php
    echo FormElements::generateFormClose(); 
    
endif;
?>