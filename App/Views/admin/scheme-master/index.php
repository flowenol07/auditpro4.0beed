<?php 

if($data['data']['db_data_count'] > 0):

?>
    <div class="table-responsive">
        <table id="schemeDataTable" class="table v-table table-hover dataTable">

            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Scheme Type</th>
                    <th scope="col">Scheme Code</th>
                    <th scope="col">Scheme Name</th>
                    <th scope="col">Category Mapped</th>
                    <th scope="col" class="nosort">Action</th>
                </tr>
            </thead>

            <tbody>
            </tbody>

        </table>
    </div>

<?php
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'schemeDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "scheme_type_id", "scheme_code", "name", "category_id", "action"], 0, 1);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>