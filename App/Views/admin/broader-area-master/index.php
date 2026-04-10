<?php 

if($data['data']['db_data_count'] > 0):

?>

    <div class="table-responsive"> 
        <table id="broaderAreaDataTable" class="table table-hover v-table dataTable">

            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Broader Area Details</th>
                    <th scope="col" class="nosort">Action</th>
                </tr>
            </thead>

            <tbody>
            </tbody>

        </table>
    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'broaderAreaDataTable', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "name","action"], 0, 1);

else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;

?>