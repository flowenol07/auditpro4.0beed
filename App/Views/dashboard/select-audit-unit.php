<?php

use Core\SiteUrls;
?>
    </div>
    <div class="row d-flex mt-4"> 
        
    <?php 
    $assesmentData = [];
    $auditPendingData = [];
    $auditCompletedData = [];
    $auditCompliancePending = [];
    $auditReviewPendingData = [];
    $lastAuditAssesData = [];
    $auditNotStartedData = [];
    
    foreach($data['data']['db_data'] as $cDataId => $cDataDetails): 

        $auditNotStartedData[$cDataId] = audit_assesment_not_started_common_code($cDataDetails, $this->model('AuditAssesmentModel'));

        if(isset($cDataDetails -> year_data))
        {
            foreach($cDataDetails -> year_data as $cYearId => $cYearDetails)
            {
                foreach($cYearDetails -> assesment_data  as $cAssesId => $cAssesDetails)
                {
                    $assesmentData[$cDataId][$cAssesId] = $cAssesDetails;

                    $lastAuditAssesData[$cDataId] = $cAssesDetails;

                    if($cAssesDetails -> audit_status_id == 1 || $cAssesDetails -> audit_status_id == 3 )
                        $auditPendingData[$cDataId][$cAssesId] = $cAssesDetails;
                    elseif($cAssesDetails -> audit_status_id == 7)
                        $auditCompletedData[$cDataId][$cAssesId] = $cAssesDetails;
                    elseif($cAssesDetails -> audit_status_id == 4 || $cAssesDetails -> audit_status_id == 6)
                        $auditCompliancePending[$cDataId][$cAssesId] = $cAssesDetails;
                    elseif($cAssesDetails -> audit_status_id == 2 || $cAssesDetails -> audit_status_id == 5)
                        $auditReviewPendingData[$cDataId][$cAssesId] = $cAssesDetails;
                }
            }
        }
    endforeach; 

    foreach($data['data']['db_data'] as $cDataId => $cDataDetails):

        if(array_key_exists($cDataId, $auditNotStartedData) && !empty($auditNotStartedData[$cDataId]))
            $auditNotStartedCount = sizeof($auditNotStartedData[$cDataId]);
        else
            $auditNotStartedCount = 0;

        echo '<div class="col-md-6 col-lg-4 mb-4">
                <div class="dash-cards">                      
                    <article class="dash-information dash-card  shadow-sm">
                        <a class="dash-links" href="' . SiteUrls::getUrl('dashboard') . '?auditUnit=' . encrypt_ex_data($cDataDetails -> id) . '"></a>
                        <h6 class="title font-bold">' . $cDataDetails -> name .' (' . $cDataDetails -> audit_unit_code . ')</h6>

                        <p><span class="info">Total Audit :</span> ' . (isset($cDataDetails -> year_data) ? sizeof($assesmentData[$cDataId]) : 0). '</p>

                        <p><span class="info">Audit Pending:</span> ' . ((isset($cDataDetails -> year_data) && array_key_exists($cDataId, $auditPendingData)) ? sizeof($auditPendingData[$cDataId]) : 0). '</p>

                        <p><span class="info">Compliance Pending :</span> ' . ((isset($cDataDetails -> year_data) && array_key_exists($cDataId, $auditCompliancePending)) ? sizeof($auditCompliancePending[$cDataId]) : 0). '</p>

                        <p><span class="info">Review Pending :</span> ' . ((isset($cDataDetails -> year_data) && array_key_exists($cDataId, $auditReviewPendingData)) ? sizeof($auditReviewPendingData[$cDataId]) : 0). '</p>

                        <p><span class="info">Audit Completed :</span> ' . ((isset($cDataDetails -> year_data) && array_key_exists($cDataId, $auditCompletedData)) ? sizeof($auditCompletedData[$cDataId]) : 0). '</p>

                        <dl class="dash-details">
                            <div>
                                <dd>Last Audit Status</dd>

                                <dt>' . (isset($lastAuditAssesData[$cDataId]) ? ASSESMENT_TIMELINE_ARRAY[$lastAuditAssesData[$cDataId] -> audit_status_id]['title'] : 'AUDIT NOT STARTED YET' ). '</dt>
                            </div>
                            <div>
                                <dd>Audit Not Started</dd>

                                <dt>' . $auditNotStartedCount . '</dt>
                            </div>
                        </dl>
                    </article>
                </div>
            </div>';
    endforeach; 
    ?>    
    </div>

    