<?php 

$srNo = 1;

echo '<div class="table-responsive">' . "\n";
    echo '<table class="table table-bordered">' . "\n";

    echo '<tr>' . "\n";

    if(isset($samplingCheck))
    {
        // check box
        echo '<th class="text-center"><label><input id="checkAll" type="checkbox" name="" value="" /><span class="d-block font-sm mt-1">Check All</span></label></th>' . "\n";
    }
    else
        echo '<th>#</th>' . "\n";

        // branch
        echo '<th>Branch</th>' . "\n";

        // scheme code
        echo '<th>Scheme</th>' . "\n";

        // account no
        echo '<th>Account No.</th>' . "\n";

        // account name // ucic
        echo '<th>Account Details</th>' . "\n";

        // account open date
        echo '<th>Account Open Date</th>' . "\n";

        // actions
        if(isset($enableActions))
            echo '<th width="120">Actions</th>' . "\n";

    echo '</td>' . "\n";

    foreach($data['data']['db_dump_data'] as $cAccId => $cAccDetails):

        if( isset($data['data']['db_display_percentage']) && $data['data']['db_display_percentage'] > 0 ):

        echo '<tr>' . "\n";

            if(isset($samplingCheck))
            {
                // check box
                echo '<td class="text-center"><input class="acc-sampling-check" type="checkbox" name="sampling[]" value="'. $cAccId .'" /></td>' . "\n";
            }
            else
                echo '<td>'. $srNo++ .'</td>' . "\n";

            // branch
            if(is_object($data['db_assesment'] -> audit_unit_id_details	))
                echo '<td>'. $data['db_assesment'] -> audit_unit_id_details -> name . ' (' . $data['db_assesment'] -> audit_unit_id_details -> audit_unit_code . ')' .'</td>' . "\n";
            else
                echo '<td>'. ERROR_VARS['notFound'] .'</td>' . "\n";

            // scheme code
            echo '<td>'. $cAccDetails -> scheme_id_name . ' (' . $cAccDetails -> scheme_id_code . ')' .'</td>' . "\n";

            // account no
            echo '<td>'. $cAccDetails -> account_no .'</td>' . "\n";

            // account name // ucic
            echo '<td>';
                echo $cAccDetails -> account_holder_name . ' (' . $cAccDetails -> ucic . ')';

                if( isset($cAccDetails -> sanction_amount) )
                    echo '<p class="font-sm font-medium mb-0 text-danger">Sanction Amount: '. $cAccDetails -> sanction_amount . '</p>' . "\n";

                if( isset($cAccDetails -> principal_amount) )
                    echo '<p class="font-sm font-medium mb-0 text-danger">Principal Amount: '. $cAccDetails -> principal_amount . '</p>' . "\n";

            echo '</td>' . "\n";

            // account open date
            echo '<td>';
                echo $cAccDetails -> account_opening_date;

                if(isset($cAccDetails -> renewal_date) && !empty($cAccDetails -> renewal_date))
                    echo '<span class="d-block font-sm text-danger font-medium">CC Renewal Date: '. $cAccDetails -> renewal_date .'</span>' . "\n";

            echo '</td>' . "\n";

            // actions
            if(isset($enableActions))
            {
                echo '<td>' . "\n";
                
                echo generate_link_button('link', ['href' => $data['start_audit_link'] . '?ac=' . encrypt_ex_data($cAccId), 'extra' => view_tooltip('Start Audit')]);

                if(empty($cAccDetails -> assesment_period_id)):
                    echo generate_link_button('delete', ['href' => $data['start_audit_link'] . '?rm=1&ac=' . encrypt_ex_data($cAccId), 'extra' => view_tooltip('Remove Sampling') . ' onclick="return confirm(\'Are you sure you want to remove sampling\');"' ]);
                endif;

                echo '</td>' . "\n";
            }

        echo '</tr>' . "\n";
        
        $data['data']['db_display_percentage']--;

        else:
            break;
        endif;

    endforeach;

    echo '</table>' . "\n";
echo '</div>' . "\n";

?>