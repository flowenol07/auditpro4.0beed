<?php

namespace Controllers\Support;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Redirect;
use Core\SiteUrls;
use Core\Notifications;
use Core\Validation;

class DateChange extends Controller
{
    public $me = null;
    public $data = [];
    public $request;

    public function __construct($me)
    {
        $this->me = $me;
        $this->request = new Request();

        // Top button
        $this->data['topBtnArr'] = [
            'default' => ['href' => SiteUrls::getUrl('dateChange')],
        ];

        // UI helpers
        $this->data['need_select'] = true;
        $this->data['need_calender'] = true;

        // Load Audit Units
        $auditUnitModel = $this->model('AuditUnitModel');
        $auditUnits = $auditUnitModel->getAllAuditUnit();

        if (is_array($auditUnits)) {
            foreach ($auditUnits as $unit) {
                $unit->combined_name = $unit->audit_unit_code . ' - ' . $unit->name;
            }
            $this->data['audit_unit_data'] = generate_data_assoc_array($auditUnits, 'id');
        } else {
            $this->data['audit_unit_data'] = [];
        }
    }

    /**
     * Main page
     */
    public function index($requestData = [])
    {
        $this->data['js'][] = 'reports/report-audit-assesment.js';
        $this->data['js'][] = 'reports/report-search-type-filter.js';

        $this->data['data_container'] = true;
        $this->data['need_calender'] = true;

        // Inputs
        $assessmentId        = $this->request->input('reportAuditAssesment');
        $auditUnitId         = $this->request->input('reportAuditUnit');
        $startDate           = $this->request->input('audit_start_date');
        $endDate             = $this->request->input('audit_end_date');
        $reportSubmittedDate = $this->request->input('report_submitted_date');

        $complianceStartDate = $this->request->input('compliance_start_date');
        $complianceEndDate   = $this->request->input('compliance_end_date');
        /**
         * ==============================
         * HANDLE UPDATE
         * ==============================
         */

      
        if ($this->request->input('update_dates')) {

            if (empty($assessmentId) || empty($auditUnitId) || empty($startDate) || empty($endDate)) {
                Session::flash('error', Notifications::getNoti('somethingWrong'));
                Redirect::to(SiteUrls::getUrl('dateChange'));
            }

            $res_array = $this->updateDates(
                $assessmentId,
                $auditUnitId,
                $startDate,
                $endDate,
                $complianceStartDate,
                $complianceEndDate,
                $reportSubmittedDate
            );

            Session::flash($res_array['res'], $res_array['msg']);
            Validation::flashErrorMsg('Date Changed Successfully', 'success');

            Redirect::to(SiteUrls::getUrl('dateChange'));

        }

        /**
         * ==============================
         * FETCH EXISTING DATA
         * ==============================
         */
        if (!empty($assessmentId) && !empty($auditUnitId)) {

            $assesmentModel = $this->model('AuditAssesmentModel');

            $resData = $assesmentModel->getSingleAuditAssesment([
                'where' => 'id = :id AND audit_unit_id = :audit_unit_id AND deleted_at IS NULL',
                'params' => [
                    'id' => $assessmentId,
                    'audit_unit_id' => $auditUnitId
                ]
            ]);

            $this->data['assesmentData'] = $resData;

            $this->data['audit_start_date'] = !empty($resData->audit_start_date)
                ? date('Y-m-d', strtotime($resData->audit_start_date))
                : '';

            $this->data['audit_end_date'] = !empty($resData->audit_end_date)
                ? date('Y-m-d', strtotime($resData->audit_end_date))
                : '';

            $this->data['compliance_start_date'] = !empty($resData->compliance_start_date)
                ? date('Y-m-d', strtotime($resData->compliance_start_date))
                : '';

            $this->data['compliance_end_date'] = !empty($resData->compliance_end_date)
                ? date('Y-m-d', strtotime($resData->compliance_end_date))
                : '';

            // Fetch report_submitted_date
            $execModel = $this->model('ExeSummaryBasicModel');
            $execData = $execModel->getSingleBasicDetails([
                'where'  => 'assesment_id = :assessment_id AND deleted_at IS NULL',
                'params' => ['assessment_id' => $assessmentId]
            ]);

            $this->data['report_submitted_date'] = !empty($execData->report_submitted_date)
                ? date('Y-m-d', strtotime($execData->report_submitted_date))
                : '';
        }

        return return2View($this, $this->me->viewDir . 'index', [
            'data'    => $this->data,
            'request' => $this->request
        ]);
    }


    /**
     * Update Dates
     */
    private function updateDates(
        $assessmentId,
        $auditUnitId,
        $startDate,
        $endDate,
        $complianceStartDate = null,
        $complianceEndDate = null,
        $reportSubmittedDate = null
    ) {
        print_r($this->request);
        $res_array = [
            'msg' => Notifications::getNoti('somethingWrong'),
            'res' => 'error'
        ];

        try {
            /**
             * Update Audit Assessment
             */
            $assesmentModel = $this->model('AuditAssesmentModel');

            $updateArray = [
                'audit_start_date' => $startDate,
                'audit_end_date'   => $endDate,
                'compliance_start_date' => $complianceStartDate,
                'compliance_end_date'   => $complianceEndDate,
                'updated_at'       => date('Y-m-d H:i:s')
            ];

            $whereArray = [
                'where' => 'id = :id AND audit_unit_id = :audit_unit_id',
                'params' => [
                    'id' => $assessmentId,
                    'audit_unit_id' => $auditUnitId
                ]
            ];

            $result = $assesmentModel::update(
    'audit_assesment_master',
    $updateArray,
    $whereArray
);

            if (!$result) {
                return $res_array;
            }

            /**
             * Update Executive Summary Date
             */
            if (!empty($reportSubmittedDate)) {
                $execModel = $this->model('ExeSummaryBasicModel');

                $updateSummary = [
                    'report_submitted_date' => $reportSubmittedDate,
                    'updated_at'            => date('Y-m-d H:i:s')
                ];

                $whereSummary = [
                    'where' => 'assesment_id = :assessment_id',
                    'params' => ['assessment_id' => $assessmentId]
                ];

                $execModel::update(
                    $execModel->getTableName(),
                    $updateSummary,
                    $whereSummary
                );
            }

            $res_array = [
                'msg' => Notifications::getNoti('datesUpdatedSuccessfully'),
                'res' => 'success'
            ];

        } catch (\Exception $e) {
            $res_array['msg'] =
                Notifications::getNoti('errorSaving') . ' ' . $e->getMessage();
            $res_array['res'] = 'error';
        }

        return $res_array;
    }
}