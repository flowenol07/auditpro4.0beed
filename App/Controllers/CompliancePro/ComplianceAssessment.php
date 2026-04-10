<?php

namespace Controllers\CompliancePro;

use Core\Controller;
use Core\Session;
use Core\Redirect;
use Core\Request;
use Core\SiteUrls;
use Core\Validation;
use Core\Except;
use Core\Notifications;

class ComplianceAssessment extends Controller  {

    public $me = null, $request, $data, $comId;
    public $comAssesModel;

    public function __construct($me) {
        
        $this -> me = $me;

        // request object created
        $this -> request = new Request();

        $this -> comAssesModel = $this -> model('ComplianceCircularAssesMasterModel');
    }

    public function assesment($getRequest) 
    {
        $this -> comId = decrypt_ex_data(isset($getRequest['val_1']) ? $getRequest['val_1'] : '');

        $this -> data['db_data'] = null;

        // get data //method call
        $this -> data['db_data'] = $this -> getDataOr404($this -> comId);

        //return if data not found
        if(!is_object($this -> data['db_data']))
            return $this -> data['db_data'];

        $empType = Session::get('emp_type');
        Session::set('compliance_id', encrypt_ex_data($this -> comId) );

        $redirectURL = null;

        switch($empType)
        {
            case '3' :
            {
                // compliance
                if( in_array( $this -> data['db_data'] -> com_status_id, [ 
                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][1]['status_id']
                    ] ))
                $redirectURL = SiteUrls::getUrl('compliancePro') . '/compliance';

                // re compliance
                else if ( in_array( $this -> data['db_data'] -> com_status_id, [ 
                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][3]['status_id']
                    ] ))
                $redirectURL = SiteUrls::getUrl('compliancePro') . '/re-compliance';

                break;
            }

            case '6' :
            {
                // review compliance
                if( in_array($this -> data['db_data'] -> com_status_id, [
                    COMPLIANCE_PRO_ARRAY['timeline_compliance_status'][2]['status_id']
                    ] ))
                $redirectURL = SiteUrls::getUrl('complianceAssessmentReviewer') . '/review-compliance';

                break;
            }
        }

        if(empty( $redirectURL ))
        {
            Except::exc_404( Notifications::getNoti('errorFinding') );
            exit;
        }

        // print_r($this -> data['db_data']);
        // echo $redirectURL;
        // exit;

        // redirect to location
        Redirect::to( $redirectURL );
    }

    public function getDataOr404($optional = null) {

        // CHECK COMPLIANCE AUTHORITY TO ACCESS CURRENT AUDIT
        
        // helper function call
        $this -> data['db_data'] = get_compliance_assesment_details($this, Session::get('emp_id'), $this -> comId);

        if( !is_object($this -> data['db_data']) )
        {
            Except::exc_404( Notifications::getNoti($this -> data['db_data']) );
            exit;
        }

        return $this -> data['db_data'];
    }
}

?>