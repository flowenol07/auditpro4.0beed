<?php 

use Core\FormElements;

if(is_object($data['data']['db_data'])) {

    $setMe = $data['siteUrls']::get('complianceCircularSetMaster');
    $data['data']['remove_container'] = false;

    require_once reverse_slash(APP_VIEWS . DS . $setMe -> viewDir . 'single-set-details-markup.php', '/');

    echo '<div class="card apcard my-4 rounded-0">' . "\n";
        echo '<div class="card-header font-medium">Compliance Status</div>' . "\n";
                
        echo '<div class="card-body">' . "\n";

            require_once 'form.php';

            if( is_array($data['data']['assesData']) && 
                sizeof($data['data']['assesData']) > 0) { 

                $checkAllStr = '';

                if( isset($data['data']['selectedTaskSet']) && $data['data']['assesCompletedCount'] > 0)
                {
                    $checkAllStr .= FormElements::generateSubmitButton('', [ 'value' => 'Check All', 'type' => 'button', 'id' => 'checkAllCheckboxes'] );
                    $checkAllStr .= '<div class="mb-3"></div>';
                }
                else // for all compliance
                    $data['data']['assesCompletedCount'] = 0;
                
                $res = generate_compliance_asses_table(['data' => [ 
                        'assesData' => $data['data']['assesData'], 
                        'year_data' => $data['data']['year_data'],
                        'check_all_str' => $checkAllStr,
                        'completed_count' => $data['data']['assesCompletedCount'],
                        'dbComSubmit' => $data['data']['dbComSubmit']
                    ], 
                    'selectedTaskSet' => isset($data['data']['selectedTaskSet']) ? $data['data']['selectedTaskSet'] : null,
                    'userDetails' => $data['userDetails'],
                    'siteUrls' => $data['siteUrls'],
                    'noti' => $data['noti'],
                    'request' => $data['request']
                ], 'review');

                if( isset($data['data']['selectedTaskSet']) && $data['data']['assesCompletedCount'] > 0)
                {
                    if( isset($data['data']['submitFormUrl']) )
                        echo FormElements::generateFormStart([
                            "name" => "compliance-submit-report-form", 
                            "appendClass" => "multi-checkbox-check-form", 
                            "action" => $data['data']['submitFormUrl'] 
                        ]);

                        // display asses table markup
                        echo $res['mrk'];

                        if( $data['request'] -> has('multi_type_check_err'))
                            echo '<span class="d-block text-danger font-sm mb-2">'. $data['noti']::getNoti( $data['request'] -> input('multi_type_check_err') ) .'</span>' . "\n";

                        // enable submit report tab
                        echo '<div class="">' . "\n";

                            // reporting_date
                            $markup = FormElements::generateLabel('reporting_date', 'Reporting Date');

                            $markup .= FormElements::generateInput([
                                "id" => "reporting_date", "name" => "reporting_date", "appendClass" => "date_cls",
                                "type" => "text", "value" => $data['request'] -> input('reporting_date', $data['data']['dbComSubmit'] -> reporting_date), 
                                "placeholder" => "Reporting Date"
                            ]);

                            echo FormElements::generateFormGroup($markup, $data, 'reporting_date');

                        echo '</div>' . "\n";

                        echo '<div class="">' . "\n";

                                // remark
                                $markup = FormElements::generateLabel('remark', 'Remark');

                                $markup .= FormElements::generateTextArea([
                                    "id" => "remark", "name" => "remark", 
                                    "type" => "text", "value" => $data['request'] -> input('remark', $data['data']['dbComSubmit'] -> remark),
                                    "placeholder" => "Remark"
                                ]);

                                echo FormElements::generateFormGroup($markup, $data, 'remark');

                        echo '</div>' . "\n";

                        echo FormElements::generateInput([
                            "id" => "multi_type_check", "name" => "multi_type_check", 
                            "type" => "hidden", "value" => '' ]);

                    if(isset($data['data']['submitFormUrl']) && $data['data']['assesCompletedCount'] > 0)
                    {
                        $btnArray = [ 'name' => 'submitReport', 'value' => 'Submit Report'];
                        echo FormElements::generateSubmitButton('', $btnArray );

                        echo FormElements::generateFormClose();
                    }
                }
                else
                {
                    // display asses table markup
                    echo $res['mrk'];
                }
            }

            if(isset($data['data']['postSubmitted']) && empty($data['data']['assesData']))
            {
                echo '<div class="mt-2"></div>' . "\n";
                echo $data['noti']::getCustomAlertNoti('noDataFound');
            }

        echo '</div>' . "\n";
    echo '</div>' . "\n";

    if( isset($data['data']['selectedTaskSet']) &&
        isset($data['data']['db_submitted_list']) && 
        is_array($data['data']['db_submitted_list']) && 
        sizeof($data['data']['db_submitted_list']) > 0)
    {
        foreach($data['data']['db_submitted_list'] as $cSubmitedData)
        {
            echo '<div class="card apcard my-4 rounded-0">' . "\n";
                echo '<div class="card-header font-medium">Submitted On '. $cSubmitedData -> reporting_date .'</div>' . "\n";
                
                echo '<div class="card-body">' . "\n";

                    // remark
                    echo '<p class="mb-0"><span class="font-medium">Remark: </span>'. $cSubmitedData -> remark .'</p>' . "\n";

                    echo '<span class="show-hide-content">Show More &raquo;</span>';

                    echo '<div class="mt-2 show-hide-content-container" data-height="80" style="height: 80px; overflow: hidden;">' . "\n";

                        echo '<h4 class="font-medium lead">Total Submitted Compliances: '. sizeof($cSubmitedData -> com_asses_ids_array) .'</h4>' . "\n";

                        if(sizeof($cSubmitedData -> com_asses_ids_array) > 0)
                        {
                            $srNo = 1;

                            echo '<div class="table-responsive-md">' . "\n";

                                echo '<table class="table table-bordered kp-table v-table border-top-0">
                                    <thead>    
                                        <tr class="top-border">
                                            <th width="80" class="text-center">Sr. No.</th>
                                            <th>Compliance Details</th>
                                            <th class="text-center">Submitted</th>
                                        </tr>
                                    </thead>' . "\n";

                                    echo '<tbody>' . "\n";

                                        foreach($cSubmitedData -> com_asses_ids_array as $cComAssesData)
                                        {
                                            echo '<tr>' . "\n";

                                                $cFreqStr = isset(COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $cComAssesData['frequency_id'] ]) ? COMPLIANCE_PRO_ARRAY['compliance_frequency'][ $cComAssesData['frequency_id'] ]['title'] : ERROR_VARS['notFound'];
                                                $cFreqStr = string_operations($cFreqStr, 'upper');

                                                // sr no
                                                echo '<td class="text-center">'. $srNo .'</td>' . "\n";

                                                // com asses data
                                                echo '<td>' . "\n";

                                                    // branch data
                                                    echo '<p class="font-medium text-primary font-sm mb-0">'. $cComAssesData['combined_name'] .'</p>' . "\n";
                                                    echo '<p class="mb-0"><span class="font-medium">Compliance Period:</span> '. $cComAssesData['com_period_from'] .' - '. $cComAssesData['com_period_to'] .' <span class="text-secondary font-sm">( Frequency - '. $cFreqStr .' )</span></p>' . "\n";

                                                echo '</td>' . "\n";

                                                echo '<td class="text-center">'. $cSubmitedData -> reporting_date .'</td>' . "\n";

                                                $srNo++;

                                            echo '</tr>' . "\n";
                                        }

                                    echo '</tbody>' . "\n";
                                echo '</table>' . "\n";

                            echo '</div>' . "\n";
                        }

                        echo generate_link_button('update', ['href' => $data['data']['submitFormUrl'] . '&comSubmitId=' . encrypt_ex_data($cSubmitedData -> id), 'extra' => view_tooltip('Update'), 'value' => 'Update Submitted Report' ]);

                        echo '<div class="w-100 mb-2"></div>' . "\n";

                        $docsMrk = generate_circular_docs_markup($cSubmitedData, [ 'container' => 1, 'mt' => 1 ]);

                        $extra = [ 'mt' => 1, 'circular_id' => $cSubmitedData -> circular_id, 'submit_auth_id' => $cSubmitedData -> id ];

                        if(empty($docsMrk))
                            $extra['need_container'] = 1;

                        if(isset($data['data']['cco_docs_true']))
                            echo generate_compliance_doc_btn($extra, 8);

                        if(!empty($docsMrk))
                            echo $docsMrk;

                    echo '</div>' . "\n";

                echo '</div>' . "\n";
            echo '</div>' . "\n";
        }

        // doc upload form // function call
        echo generate_hidden_docs_upload_form();
    }
}
else
    echo $data['noti']::getCustomAlertNoti('noDataFound');

?>