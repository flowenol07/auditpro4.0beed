<?php 
    if($data['data']['db_data_count'] > 0):
?>

    <div class="table-responsive">  
        <table id="userColumns" class="table table-hover v-table dataTable mt-2">
            <thead>
                <tr>
                    <th scope="col" class="nosort">Sr. No.</th>
                    <th scope="col">Userid</th>
                    <th scope="col">Type of Salary</th>
                    <th scope="col">In Hand Salary</th>
                    <th scope="col">Tax Deductions</th>
                    <th scope="col">Provided Founds</th>
                    <th scope="col" class="nosort">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

<?php

    $data['data']['inline_js'] = "\n" . generate_datatable_javascript( 'userColumns', $data["siteUrls"]::setUrl( $data["me"] -> url ) .'/' . DATA_TABLE_AJX, [ "sr_no", "user_id", "type_of_salary", "in_hand_salary", "tax_dedications", "provide_funds", "action"], 1, 1);
else:
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif;
?>