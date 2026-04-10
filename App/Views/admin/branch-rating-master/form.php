<?php
use Core\FormElements;

echo $data['noti']::getSessionAlertNoti();

    if($data['request'] -> has('bulkData'))
        $formUrl = $data['me'] -> url . '&bulkData=1';
    else
        $formUrl = $data['me'] -> url;

echo FormElements::generateFormStart(["name" => "branch-rating", "action" => $formUrl]);

function generateFields ($data, $cRiskTypeId, $cRiskDetails)
{ 
    $name = array_key_exists($cRiskTypeId, RISK_PARAMETERS_ARRAY) ? RISK_PARAMETERS_ARRAY[$cRiskTypeId]['title'] : ERROR_VARS['notFound'];

    echo '<input type="hidden" id="risk_type_'. $cRiskTypeId . '" name="risk_type_' .$cRiskTypeId . '" value="'. $cRiskTypeId .'"/>';

    echo '<div class="col-md-6">';
            //range_from
            $markup = FormElements::generateLabel('range_from_' . $cRiskTypeId, 'Range From ( ' . $name . ' )');

            $markup .= FormElements::generateInput([
                "id" => "range_from_" . $cRiskTypeId, "name" => "range_from_" . $cRiskTypeId, 
                "type" => "text", "value" => ($data['request'] -> input('range_from_' . $cRiskTypeId, $cRiskDetails -> range_from)), 
                "placeholder" => "Range From"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'range_from_' . $cRiskTypeId);
    echo '</div>';

    echo '<div class="col-md-6">';
            //range_to
            $markup = FormElements::generateLabel('range_to_' . $cRiskTypeId, 'Range To ( ' . $name . ' )');

            $markup .= FormElements::generateInput([
                "id" => "range_to_" . $cRiskTypeId, "name" => "range_to_" . $cRiskTypeId, 
                "type" => "text", "value" => ($data['request'] -> input("range_to_" . $cRiskTypeId, $cRiskDetails -> range_to)), 
                "placeholder" => "Range To"
            ]);

            echo FormElements::generateFormGroup($markup, $data, 'range_to_' . $cRiskTypeId);
    echo '</div>';  
};

?>
    <div class="row">
        <?php
            if(!$data['request'] -> has('bulkData'))
            {
        ?>
            <div class="col-md-6">
                <?php

                    $firstRiskRec = array_keys($data['data']['db_data'])[0];

                    //audit_unit_id
                    $markup = FormElements::generateLabel('audit_unit_id', 'Audit Unit
                    ');

                    if(is_array($data['data']['db_audit_unit']) && sizeof($data['data']['db_audit_unit']) > 0)
                    {
                        $markup .= FormElements::generateSelect([
                            "id" => "audit_unit_id", "name" => "audit_unit_id", 
                            "default" => ["", "Please select audit unit"],
                            "options" => $data['data']['db_audit_unit'],
                            "appendClass" => "select2search",
                            "selected" => ($data['request'] -> input('audit_unit_id', $data['data']['db_data'][$firstRiskRec] -> audit_unit_id))
                        ]);
                    }
                    else
                        $markup .= $data['noti']::getCustomAlertNoti('noDataFound');

                    echo FormElements::generateFormGroup($markup, $data, 'audit_unit_id');
                ?>
            </div>
        <?php }?>

        <div class=<?= ($data['request'] -> has('bulkData')) ? "col-md-12" : "col-md-6"?>>
            <?php
                //audit_type_id
                $markup = FormElements::generateLabel('audit_type_id', 'Audit Type');

                $firstRiskRec = array_keys($data['data']['db_data'])[0];

                if(is_array(AUDIT_TYPE_ARRAY) && sizeof(AUDIT_TYPE_ARRAY) > 0 )
                {
                    $markup .= FormElements::generateSelect([
                        "id" => "audit_type_id", "name" => "audit_type_id", 
                        "default" => ["", "Please select audit type"],
                        "options" => AUDIT_TYPE_ARRAY,
                        "selected" => (($data['request'] -> input('audit_type_id', $data['data']['db_data'][$firstRiskRec] -> audit_type_id)) ?? ($data['request'] -> input('audit_type_id')))
                    ]);

                }
                else    
                    $markup .= $data['noti']::getCustomAlertNoti('noDataFound');


                echo FormElements::generateFormGroup($markup, $data, 'audit_type_id');
            ?>
        </div>
            
        <?php
            foreach($data['data']['db_data'] as $cRiskId => $cRiskDetails)
                generateFields($data, $cRiskDetails -> risk_type_id, $cRiskDetails); 
        ?>
        
    </div>

<?php 

    $btnArray = [ 'name' => 'submit', 'value' => 'Add Branch Rating'];     

    if($data['data']['btn_type'] == 'update')
    {
        $btnArray['value'] = 'Update Branch Rating';
        echo FormElements::generateSubmitButton('update', $btnArray );
    }
    else
        echo FormElements::generateSubmitButton('add', $btnArray );

    echo FormElements::generateFormClose(); 

?>