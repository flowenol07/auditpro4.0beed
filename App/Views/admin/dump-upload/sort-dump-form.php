<?php

use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

echo FormElements::generateFormStart([ "name" => "sort-dump-upload", "method" => "get", "action" => $data['me'] -> url, "enctype" => "multipart/form-data" ]);

?>

<div class="row">

    <div class="col-md-12">
        <?php

            //audit-unit
            $markup = FormElements::generateLabel('filter', 'Filter');

            if( is_array($data['data']['filter_array']) && 
                sizeof($data['data']['filter_array']) > 0 )
            {
                $markup .= FormElements::generateSelect([
                    "id" => "filter", "name" => "filter", 
                    // "default" => ['all', 'ALL ACCOUNTS'],
                    "options" => $data['data']['filter_array'],
                    "selected" => $data['request'] -> input('filter')
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'filter');
        ?>
    </div>

    <div class="col-md-12 show_hide_div">

        <?php 

            //filter_text
            $markup = FormElements::generateLabel('filter_text', 'Filter Text');

            $markup .= FormElements::generateInput([
                "id" => "filter_text", "name" => "filter_text",
                "type" => "text", "value" => $data['request'] -> input('filter_text'),
                "placeholder" => "Search by Account No., Name, UCIC"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'filter_text');

        ?>

    </div>

    <div class="col-md-6">
        <?php

            //audit_unit
            $markup = FormElements::generateLabel('audit_unit', 'Select Filter');

            if( is_array($data['data']['db_audit_unit_data']) && 
                sizeof($data['data']['db_audit_unit_data']) > 0 )
            {
                $markup .= FormElements::generateSelect([
                    "id" => "audit_unit", "name" => "audit_unit", 
                    // "default" => ['all', 'ALL AUDIT UNITS'],
                    "options" => $data['data']['db_audit_unit_data'],
                    "appendClass" => "select2search",
                    "selected" => $data['request'] -> input('audit_unit')
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'audit_unit');
        ?>
    </div>

    <div class="col-md-6">
        <?php

            //scheme
            $markup = FormElements::generateLabel('scheme', 'Select Scheme');

            if( is_array($data['data']['db_scheme_data']) && 
                sizeof($data['data']['db_scheme_data']) > 0 )
            {
                $markup .= FormElements::generateSelect([
                    "id" => "scheme", "name" => "scheme", 
                    // "default" => ['all', 'ALL SCHEMES'],
                    "options" => $data['data']['db_scheme_data'],
                    "appendClass" => "select2search",
                    "selected" => $data['request'] -> input('scheme')
                ]);
            }
            else
                $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

            echo FormElements::generateFormGroup($markup, $data, 'scheme');
        ?>
    </div>
    
    <div class="col-md-6">

        <?php 

            //period_from
            $markup = FormElements::generateLabel('period_from', 'Period From');

            $markup .= FormElements::generateInput([
                "id" => "period_from", "name" => "period_from", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('period_from', $data['data']['db_data'] -> upload_period_from),
                "placeholder" => "Period From"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'period_from');

        ?>

    </div>

    <div class="col-md-6">

        <?php 

            //period_to
            $markup = FormElements::generateLabel('period_to', 'Period To');

            $markup .= FormElements::generateInput([
                "id" => "period_to", "name" => "period_to", "appendClass" => 'date_cls',
                "type" => "text", "value" => $data['request'] -> input('period_to', $data['data']['db_data'] -> upload_period_to),
                "placeholder" => "Period To"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'period_to');

        ?>

    </div>

</div>

    <?php

    $btnArray = [ 'value' => 'Search'];
    echo FormElements::generateSubmitButton('search', $btnArray );

echo FormElements::generateFormClose();

if(isset($data['data']['db_acc_data']) && sizeof($data['data']['db_acc_data']) > 0):

    $srr_no = 1;
?>

<h5 class="text-primary mt-4 mb-0">Total Accounts: <?= sizeof($data['data']['db_acc_data']); ?></h5>
<p class="text-danger font-sm"><span class="font-bold">Notice:</span> For security reasons related to the database, we limit the display of records to a maximum of 100.</p>

<div class="table-responsive height-400">
    <table class="table v-table kp-table">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Branch Code</th>
                <th width="150">Scheme Code</th>
                <th>Account Number</th>
                <th>Account Holder Name</th>
                <th width="120">Account Open Date</th>
                <th width="100">Actions</th>
            </tr>
        </thead>

        <?php 
            foreach($data['data']['db_acc_data'] as $cAccKey => $cAccDetails):

                echo '<tr>' . "\n";
                    echo '<td>'. $srr_no .'</td>' . "\n";
                    echo '<td>'. ( (is_array($data['data']['db_audit_unit_data_arr']) && array_key_exists($cAccDetails -> branch_id, $data['data']['db_audit_unit_data_arr'])) ? ($data['data']['db_audit_unit_data_arr'][ $cAccDetails -> branch_id ] -> combined_branch_name) : ERROR_VARS['notFound'] )  .'</td>' . "\n";
                    echo '<td>'. ( (is_array($data['data']['db_scheme_data_arr']) && array_key_exists($cAccDetails -> scheme_id, $data['data']['db_scheme_data_arr'])) ? ($data['data']['db_scheme_data_arr'][ $cAccDetails -> scheme_id ] -> combined_scheme) : ERROR_VARS['notFound'] ) .'</td>' . "\n";
                    echo '<td>'. $cAccDetails -> account_no .'</td>' . "\n";
                    echo '<td>'. $cAccDetails -> account_holder_name .'</td>' . "\n";
                    echo '<td>';
                        echo $cAccDetails -> account_opening_date;

                        if(!empty($cAccDetails -> renewal_date))    
                            echo '<span class="d-block font-sm text-danger font-medium">CC Renewal Date : ' . $cAccDetails -> renewal_date . '</span>';

                    echo '</td>' . "\n";

                    //action
                    if($data['data']['dumpType'][0] == 2)
                    {
                        echo '<td>';
                        if(($cAccDetails -> assesment_period_id) == 0)                        
                            echo generate_link_button('update', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/updateAdvances/' . encrypt_ex_data($cAccDetails -> id), 'extra' => view_tooltip('Update') ]);
                        else
                            echo $data['noti']::getCustomAlertNoti('accountInAssesment', 'x');

                        if(($cAccDetails -> sampling_filter) == 0)
                            echo generate_link_button('delete', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/deleteAdvances/' . encrypt_ex_data($cAccDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
                    echo '</td>' . "\n";
                    }
                    elseif($data['data']['dumpType'][0] == 1)
                    {
                        echo '<td>';
                        if(($cAccDetails -> assesment_period_id) == 0)
                            echo generate_link_button('update', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/updateDeposit/' . encrypt_ex_data($cAccDetails -> id), 'extra' => view_tooltip('Update') ]);
                        else
                            echo $data['noti']::getCustomAlertNoti('accountInAssesment', 'x');

                        if(($cAccDetails -> sampling_filter) == 0)
                            echo generate_link_button('delete', ['href' => $data['siteUrls']::setUrl( $data['me'] -> url ) . '/deleteDeposit/' . encrypt_ex_data($cAccDetails -> id), 'extra' => view_tooltip('Delete') . ' onclick="return confirm(\'Are you sure you want to delete\');"' ]);
                    echo '</td>' . "\n";
                    }
                echo '</tr>' . "\n";
                $srr_no++;

            endforeach; 
        ?>

    </table>
</div>

<?php 
// print_r($data['data']['dumpType']);
// echo $data['data']['dumpType'][0];

elseif(isset($data['data']['db_acc_data']) && !sizeof($data['data']['db_acc_data']) > 0):
    echo '<div class="mb-2"></div>';
    echo $data['noti']::getCustomAlertNoti('noDataFound');
endif; 

$data['data']['inline_js'] = "\n" . '
<script>
$(document).ready(function(){

    // for hide and show-------------
    function show_hide_container (val) {
        $(".show_hide_div").hide();

        if(val != "all")
            $(".show_hide_div").show();
    }

    $("#filter").change(function(){
        show_hide_container($(this).val());
    });

    show_hide_container($("#filter").val());

});

</script>';

?>