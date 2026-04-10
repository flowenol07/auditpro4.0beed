<?php 

if($data['data']['db_data_count'] > 0):

?>
    <div class="table-responsive">
        <table id="categoryDataTable" class="table table-hover v-table dataTable">

            <thead>
                <tr>
                    <th scope="col">Menu</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>

            <tbody>
            </tbody>

        </table>
    </div>

<?php
    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'categoryDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "menu_id", "name", "status", "action"], 0, 0);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>