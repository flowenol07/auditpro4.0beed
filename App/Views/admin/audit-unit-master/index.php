<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive">

    <table id="auditUnitMasterTable" class="table table-hover v-table dataTable mt-2">

    <thead>
        <tr>
            <th scope="col" class="dtcol-120">Unit Code</th>
            <th scope="col">Section</th>
            <th scope="col" class="dtcol-320">Audit Unit Details</th>
            <th scope="col">Last Assesment Date</th>
            <th scope="col">Status</th>
            <th scope="col" class="nosort dtcol-200">Action</th>
        </tr>
    </thead>

    <tbody></tbody>

    </table>

    </div>

<?php

$data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'auditUnitMasterTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/'. DATA_TABLE_AJX, [ "audit_unit_code", "section_type_id", "name", "last_audit_date", "status", "action" ]);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>