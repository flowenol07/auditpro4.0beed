<?php

namespace Controllers\SuperAdmin;

use Core\Controller;
use Core\Request;
use Core\Session;
use Core\Redirect;
use Core\SiteUrls;
use Core\Notifications;
use Core\Validation;

class SuperAdminDashboard extends Controller
{
    public $me = null;
    public $data = [];
    public $request;
    private $auditAssessmentModel;
    private $auditUnitModel;
    private $employeeModel;
    private $allowedAuditUnitIds = [];
    private $loggedInUser = null;
    private $userData = null;
    private $exeBasicModel;  // <-- ADD THIS LINE


    public function __construct($me)
    {
        $this->me = $me;
        $this->request = new Request();
        
        // Initialize models
        $this->auditAssessmentModel = $this->model('AuditAssesmentModel');
        $this->auditUnitModel = $this->model('AuditUnitModel');
        $this->employeeModel = $this->model('EmployeeModel');
        $this->exeBasicModel = $this->model('ExeSummaryBasicModel');
        
        // Setup user data
        $this->getLoggedInUser();
        $this->checkUserAuthority();
        
        // Setup top buttons
        $this->data['topBtnArr'] = [
            'default' => ['href' => SiteUrls::getUrl('superAdminDashboard')],
            'calendar' => ['href' => SiteUrls::getUrl('superAdminDashboard') . '?view=calendar', 'label' => 'Calendar View'],
            'list' => ['href' => SiteUrls::getUrl('superAdminDashboard') . '?view=list', 'label' => 'List View']
        ];

        // Setup view requirements
        $this->data['need_select'] = true;
        $this->data['need_calender'] = true;
        $this->data['offline_calendar'] = true;
        
        // Load dynamic data
        $this->loadDynamicData();
    }
    
    private function getLoggedInUser()
    {
        $sessionKeys = ['emp_details', 'user', 'logged_in_user', 'auth_user', 'admin'];
        
        foreach ($sessionKeys as $key) {
            if (Session::has($key)) {
                $userData = Session::get($key);
                $this->loggedInUser = $this->extractUserFromSession($userData);
                if ($this->loggedInUser && !empty($this->loggedInUser->id)) {
                    break;
                }
            }
        }
        
        // Fallback to emp_id
        if ((!$this->loggedInUser || empty($this->loggedInUser->id)) && Session::has('emp_id')) {
            $this->loggedInUser = (object)[
                'id' => Session::get('emp_id'),
                'user_type_id' => Session::get('emp_type') ?? null
            ];
        }
        
        // Normalize user ID
        if ($this->loggedInUser && empty($this->loggedInUser->id)) {
            $idFields = ['user_id', 'employee_id'];
            foreach ($idFields as $field) {
                if (isset($this->loggedInUser->$field)) {
                    $this->loggedInUser->id = $this->loggedInUser->$field;
                    break;
                }
            }
        }
    }
    
    private function extractUserFromSession($userData)
    {
        if (is_array($userData)) {
            return (object)[
                'id' => $userData['emp_id'] ?? null,
                'user_type_id' => $userData['emp_type'] ?? null,
                'name' => $userData['emp_name'] ?? null,
                'gender' => $userData['emp_gender'] ?? null,
                'designation' => $userData['emp_design'] ?? null,
                'profile_pic' => $userData['emp_profile'] ?? null
            ];
        } elseif (is_object($userData)) {
            return $userData;
        }
        
        return null;
    }
    
    private function checkUserAuthority()
    {
        if (!$this->loggedInUser || empty($this->loggedInUser->id)) {
            $this->setAccessDenied('User not logged in properly. Please login again.');
            return;
        }
        
        $this->userData = $this->getEmployeeById($this->loggedInUser->id);
        
        if (!$this->userData) {
            $this->setAccessDenied('User record not found. Please contact administrator.');
            return;
        }
        
        $this->determineAllowedAuditUnits();
        
        if (empty($this->allowedAuditUnitIds)) {
            $this->setAccessDenied('You do not have access to any audit units. Please contact your administrator.');
        }
    }
    
    private function setAccessDenied($message)
    {
        $this->data['access_denied'] = true;
        $this->data['access_denied_message'] = $message;
    }
    
    private function getEmployeeById($id)
    {
        try {
            $result = $this->employeeModel->getSingleEmploye([
                'where' => "id = '$id' AND deleted_at IS NULL"
            ]);
            
            return !empty($result) ? (is_array($result) ? (object)$result[0] : (object)$result) : null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function determineAllowedAuditUnits()
    {
        $auditUnitAuthority = $this->userData->audit_unit_authority ?? '';
        
        if (empty($auditUnitAuthority) || $auditUnitAuthority == '0') {
            $this->allowedAuditUnitIds = [];
            return;
        }
        
        $ids = preg_split('/\s*,\s*/', trim($auditUnitAuthority));
        $this->allowedAuditUnitIds = array_filter(array_map('intval', $ids), function($id) {
            return $id > 0;
        });
        sort($this->allowedAuditUnitIds);
    }
    
    private function hasAccessToAuditUnit($auditUnitId)
    {
        return !empty($this->allowedAuditUnitIds) && in_array($auditUnitId, $this->allowedAuditUnitIds);
    }

    private function loadDynamicData()
    {
        $this->data['audit_unit_data'] = $this->getAuditUnits();
        $this->prepareCalendarEventsFromDB();
        $this->prepareStatisticsFromDB();
    }

    private function getAuditUnits()
    {
        $auditUnits = $this->auditUnitModel->getAllAuditUnit(['where' => 'deleted_at IS NULL']);
        $unitData = [];
        
        foreach ($auditUnits as $unit) {
            if (!$this->hasAccessToAuditUnit($unit->id)) {
                continue;
            }
            
            $unitData[$unit->id] = (object)[
                'id' => $unit->id,
                'audit_unit_code' => $unit->audit_unit_code ?? '',
                'name' => $unit->name ?? '',
                'combined_name' => ($unit->audit_unit_code ?? '') . ' - ' . ($unit->name ?? ''),
                'frequency' => $unit->frequency ?? 1,
                'last_audit_date' => $unit->last_audit_date ?? null
            ];
        }
        
        return $unitData;
    }

    private function prepareCalendarEventsFromDB()
    {
        $events = [];
        $assessments = $this->getFilteredAssessments();
        $activeAssessments = $this->getActiveAssessments();
        
        // Process assessments
        foreach ($assessments as $assessment) {
            $events = array_merge($events, $this->createAssessmentEvents($assessment));
        }
        
        // Add upcoming audits
        $upcomingAudits = $this->calculateUpcomingAudits($activeAssessments);
        $events = array_merge($events, $upcomingAudits);
        
        // Sort events by date
        usort($events, function($a, $b) {
            return strcmp($a['start'], $b['start']);
        });
        
        $this->data['calendar_events'] = json_encode($events);
    }
    
    private function createAssessmentEvents($assessment)
    {
        $events = [];
        $hasAuditComplete = !empty($assessment->audit_end_date) && $assessment->audit_end_date != '0000-00-00';
        $hasComplianceComplete = !empty($assessment->compliance_end_date) && $assessment->compliance_end_date != '0000-00-00';
        
        // Audit start event
        if (!empty($assessment->audit_start_date) && $assessment->audit_start_date != '0000-00-00') {
            $events[] = $this->createEventFromAssessment($assessment, 'audit_start', $assessment->audit_start_date);
        }
        
        // Audit review event
        if (!empty($assessment->audit_review_date) && $assessment->audit_review_date != '0000-00-00') {
            $events[] = $this->createEventFromAssessment($assessment, 'audit_review', $assessment->audit_review_date);
        }
        
        // Audit end/due event
        if ($hasAuditComplete) {
            $events[] = $this->createEventFromAssessment($assessment, 'audit_end', $assessment->audit_end_date);
        } elseif (!empty($assessment->audit_due_date) && $assessment->audit_due_date != '0000-00-00') {
            $events[] = $this->createEventFromAssessment($assessment, 'audit_due', $assessment->audit_due_date);
        }
        
        // Compliance start event
        if (!empty($assessment->compliance_start_date) && $assessment->compliance_start_date != '0000-00-00') {
            $events[] = $this->createEventFromAssessment($assessment, 'compliance_start', $assessment->compliance_start_date);
        }
        
        // Compliance review event
        if (!empty($assessment->compliance_review_date) && $assessment->compliance_review_date != '0000-00-00') {
            $events[] = $this->createEventFromAssessment($assessment, 'compliance_review', $assessment->compliance_review_date);
        }
        
        // Compliance end/due event
        if ($hasComplianceComplete) {
            $events[] = $this->createEventFromAssessment($assessment, 'compliance_end', $assessment->compliance_end_date);
        } elseif (!empty($assessment->compliance_due_date) && $assessment->compliance_due_date != '0000-00-00') {
            $events[] = $this->createEventFromAssessment($assessment, 'compliance_due', $assessment->compliance_due_date);
        }
        
        return $events;
    }
    
    private function getFilteredAssessments()
{
    // First get all assessments using the existing model method
    $allAssessments = $this->auditAssessmentModel->getAllAuditAssesment([
        'where' => 'deleted_at IS NULL'
    ]);
    
    $filteredAssessments = [];
    
    foreach ($allAssessments as $assessment) {
        if ($this->hasAccessToAuditUnit($assessment->audit_unit_id)) {
            $assessment = is_array($assessment) ? (object)$assessment : $assessment;
            
            // Fetch report_submitted_date directly using the model without storing in property
            $exeBasicModel = $this->model('ExeSummaryBasicModel');
            $exeData = $exeBasicModel->getSingleBasicDetails([
                'where' => 'assesment_id = :assesment_id AND deleted_at IS NULL',
                'params' => ['assesment_id' => $assessment->id]
            ]);
            
            // Override audit_end_date if report_submitted_date exists
            if ($exeData && !empty($exeData->report_submitted_date) && $exeData->report_submitted_date != '0000-00-00') {
                $assessment->audit_end_date = $exeData->report_submitted_date;
            }
            
            $filteredAssessments[] = $assessment;
        }
    }
    
    return $filteredAssessments;
}
    
    private function getActiveAssessments()
    {
        $allAssessments = $this->getFilteredAssessments();
        $activeAssessments = [];
        
        foreach ($allAssessments as $assessment) {
            if ($assessment->audit_status_id >= 1 && $assessment->audit_status_id <= 6) {
                if ($this->hasAccessToAuditUnit($assessment->audit_unit_id)) {
                    $activeAssessments[] = $assessment;
                }
            }
        }
        
        return $activeAssessments;
    }
    
    private function calculateUpcomingAudits($activeAssessments)
    {
        $upcomingEvents = [];
        $today = date('Y-m-d');
        
        // Get audit units with active assessments
        $auditUnitsWithActiveAssessments = array_map(function($assessment) {
            return $assessment->audit_unit_id;
        }, $activeAssessments);
        
        foreach ($this->data['audit_unit_data'] as $unitId => $unit) {
            // Skip if there's an overlapping active assessment
            if ($this->hasOverlappingActiveAssessment($unitId, $activeAssessments, $today)) {
                continue;
            }
            
            if (empty($unit->last_audit_date)) {
                continue;
            }
            
            $frequencyMonths = (int)($unit->frequency ?? 1);
            if ($frequencyMonths <= 0) {
                continue;
            }
            
            $nextAuditStart = $this->calculateNextAuditStart($unit->last_audit_date, $frequencyMonths, $today);
            if ($nextAuditStart) {
                $upcomingEvents[] = $this->createUpcomingAuditEvent($unit, $nextAuditStart, $frequencyMonths);
            }
        }
        
        return $upcomingEvents;
    }
    
    private function hasOverlappingActiveAssessment($unitId, $activeAssessments, $today)
    {
        foreach ($activeAssessments as $assessment) {
            if ($assessment->audit_unit_id == $unitId) {
                $auditEndDate = !empty($assessment->audit_end_date) && $assessment->audit_end_date != '0000-00-00' 
                    ? $assessment->audit_end_date 
                    : $assessment->audit_due_date;
                
                if (!empty($auditEndDate) && $auditEndDate >= $today) {
                    return true;
                }
            }
        }
        return false;
    }
    
    private function calculateNextAuditStart($lastAuditDate, $frequencyMonths, $today)
    {
        $lastDate = new \DateTime($lastAuditDate);
        
        for ($i = 1; $i <= 12; $i++) {
            $auditStartDate = clone $lastDate;
            $auditStartDate->modify('+' . ($frequencyMonths * $i) . ' months');
            
            if ($auditStartDate->format('Y-m-d') >= $today) {
                return $auditStartDate;
            }
        }
        
        return null;
    }
    
    private function createUpcomingAuditEvent($unit, $auditStartDate, $frequencyMonths)
    {
        $auditEndDate = clone $auditStartDate;
        $auditEndDate->modify('+' . $frequencyMonths . ' months')->modify('-1 day');
        
        return [
            'id' => 'upcoming_audit_start_' . $unit->id,
            'title' => 'Upcoming Audit: ' . $unit->name,
            'start' => $auditStartDate->format('Y-m-d'),
            'end' => $auditStartDate->format('Y-m-d'),
            'type' => 'upcoming_audit',
            'category' => 'upcoming',
            'description' => 'Next scheduled audit for ' . $unit->name . ' will start on ' . $auditStartDate->format('Y-m-d'),
            'audit_unit_id' => $unit->id,
            'audit_unit_name' => $unit->combined_name,
            'status_id' => 0,
            'status_title' => 'UPCOMING AUDIT',
            'assesment_period_from' => $auditStartDate->format('Y-m-d'),
            'assesment_period_to' => $auditEndDate->format('Y-m-d'),
            'color' => '#0dcaf0',
            'is_upcoming' => true,
            'frequency' => $frequencyMonths,
            'last_audit_date' => $unit->last_audit_date
        ];
    }
    
    private function createEventFromAssessment($assessment, $eventType, $date)
    {
        $auditUnitName = $this->getAuditUnitName($assessment->audit_unit_id);
        $statusInfo = $this->getStatusInfo($assessment->audit_status_id);
        $eventConfig = $this->getEventConfig($eventType, $auditUnitName, $assessment->id);
        
        return [
            'id' => $assessment->id . '_' . $eventType,
            'title' => $eventConfig['title'],
            'start' => $date,
            'end' => $date,
            'type' => $eventConfig['type'],
            'category' => $eventConfig['category'],
            'description' => $eventConfig['description'],
            'audit_unit_id' => $assessment->audit_unit_id,
            'audit_unit_name' => $auditUnitName,
            'status_id' => $assessment->audit_status_id,
            'status_title' => $statusInfo['title'] ?? 'Unknown',
            'assessment_id' => $assessment->id,
            'assesment_period_from' => $assessment->assesment_period_from,
            'assesment_period_to' => $assessment->assesment_period_to,
            'color' => $eventConfig['color'],
            'is_upcoming' => false
        ];
    }
    
    private function getAuditUnitName($unitId)
    {
        return isset($this->data['audit_unit_data'][$unitId]) 
            ? $this->data['audit_unit_data'][$unitId]->combined_name 
            : '';
    }
    
    private function getEventConfig($eventType, $auditUnitName, $assessmentId)
    {
        $configs = [
            'audit_start' => [
                'title' => 'Audit Started: ' . $auditUnitName,
                'type' => 'audit',
                'category' => 'start',
                'color' => '#28a745',
                'description' => 'Audit period started'
            ],
            'audit_review' => [
                'title' => 'Audit Review: ' . $auditUnitName,
                'type' => 'audit',
                'category' => 'review',
                'color' => '#17a2b8',
                'description' => 'Audit review Pending'
            ],
            'audit_end' => [
                'title' => 'Audit Completed: ' . $auditUnitName,
                'type' => 'audit',
                'category' => 'end',
                'color' => '#6c757d',
                'description' => 'Audit completed'
            ],
            'audit_due' => [
                'title' => 'Audit Due Date: ' . $auditUnitName,
                'type' => 'audit',
                'category' => 'due',
                'color' => '#fd7e14',
                'description' => 'Audit due date'
            ],
            'compliance_start' => [
                'title' => 'Compliance Started: ' . $auditUnitName,
                'type' => 'compliance',
                'category' => 'start',
                'color' => '#ffc107',
                'description' => 'Compliance period started'
            ],
            'compliance_review' => [
                'title' => 'Compliance Review: ' . $auditUnitName,
                'type' => 'compliance',
                'category' => 'review',
                'color' => '#20c997',
                'description' => 'Compliance review completed'
            ],
            'compliance_end' => [
                'title' => 'Compliance Completed: ' . $auditUnitName,
                'type' => 'compliance',
                'category' => 'end',
                'color' => '#6c757d',
                'description' => 'Compliance completed'
            ],
            'compliance_due' => [
                'title' => 'Compliance Due Date: ' . $auditUnitName,
                'type' => 'compliance',
                'category' => 'due',
                'color' => '#dc3545',
                'description' => 'Compliance due date'
            ]
        ];
        
        return $configs[$eventType] ?? [
            'title' => 'Assessment: ' . $auditUnitName,
            'type' => 'assessment',
            'category' => 'general',
            'color' => '#6c757d',
            'description' => 'Assessment activity'
        ];
    }

    private function getStatusInfo($statusId)
    {
        $statusMap = [
            1 => 'AUDIT (PENDING / ACTIVE)',
            2 => 'REVIEW (PENDING / ACTIVE)',
            3 => 'RE AUDIT (PENDING / ACTIVE)',
            4 => 'COMPLIANCE (PENDING / ACTIVE)',
            5 => 'REVIEW (PENDING / ACTIVE)',
            6 => 'RE COMPLIANCE (PENDING / ACTIVE)',
            7 => 'ASSESMENT COMPLETED',
            8 => 'REVIEWER TO AUDIT (All OBSERVATIONS)',
            9 => 'REVIEWER TO COMPLIANCE (All OBSERVATIONS)',
            10 => 'ADMIN INCREASE ACCEPT / REJECT LIMIT IN AUDIT',
            11 => 'ADMIN INCREASE ACCEPT / REJECT LIMIT IN COMPLIANCE',
            12 => 'ADMIN INCREASE DUE DATE IN AUDIT',
            13 => 'ADMIN INCREASE DUE DATE IN COMPLIANCE',
            14 => 'REVIEWER TO AUDIT (ENTIRE ASSESMENT BACK TO AUDIT)',
        ];
        
        return [
            'status_id' => $statusId,
            'title' => $statusMap[$statusId] ?? 'Unknown Status'
        ];
    }

   private function prepareStatisticsFromDB()
{
    $assessments = $this->getFilteredAssessments();
    $today = date('Y-m-d');
    
    $stats = [
        'total_assessments' => 0,
        'active_assessments' => 0,
        'completed_assessments' => 0,
        'audit_completed_count' => 0,      // For assessments with report_submitted_date
        'compliance_completed_count' => 0, // For assessments with compliance_end_date
        'overdue_items' => 0,
        'upcoming_deadlines' => 0,
        'upcoming_audits_count' => 0,
        'audit_reviews' => 0,
        'compliance_reviews' => 0
    ];
    
    // Count assessments
    foreach ($assessments as $assessment) {
        $stats['total_assessments']++;
        
        // Fetch report_submitted_date for this assessment
        $exeData = $this->exeBasicModel->getSingleBasicDetails([
            'where' => 'assesment_id = :assesment_id AND deleted_at IS NULL',
            'params' => ['assesment_id' => $assessment->id]
        ]);
        
        // Check if report_submitted_date exists (AUDIT COMPLETED)
        $hasReportSubmitted = ($exeData && !empty($exeData->report_submitted_date) && 
                               $exeData->report_submitted_date != '0000-00-00');
        
        // Check if compliance is completed (has end date)
        $isComplianceCompleted = !empty($assessment->compliance_end_date) && 
                                $assessment->compliance_end_date != '0000-00-00';
        
        // COUNT AUDIT COMPLETED (where report_submitted_date exists)
        if ($hasReportSubmitted) {
            $stats['audit_completed_count']++;
        }
        
        // COUNT COMPLIANCE COMPLETED
        if ($isComplianceCompleted) {
            $stats['compliance_completed_count']++;
        }
        
        // Check if audit is completed using audit_end_date (which is overridden)
        $isAuditCompleted = !empty($assessment->audit_end_date) && 
                           $assessment->audit_end_date != '0000-00-00';
        
        // Check if assessment is fully completed
        $isFullyCompleted = ($assessment->audit_status_id == 7);
        
        if ($isFullyCompleted) {
            $stats['completed_assessments']++;
        } else {
            $stats['active_assessments']++;
            
            // Count audit due date
            if (!$isAuditCompleted) {
                if (!empty($assessment->audit_due_date) && $assessment->audit_due_date != '0000-00-00') {
                    if ($assessment->audit_due_date < $today) {
                        $stats['overdue_items']++;
                    } elseif ($assessment->audit_due_date >= $today) {
                        $stats['overdue_items']++;
                        $stats['upcoming_deadlines']++;
                    }
                }
            }
            
            // Count compliance due date
            if (!$isComplianceCompleted) {
                if (!empty($assessment->compliance_due_date) && $assessment->compliance_due_date != '0000-00-00') {
                    if ($assessment->compliance_due_date < $today) {
                        $stats['overdue_items']++;
                    } elseif ($assessment->compliance_due_date >= $today) {
                        $stats['overdue_items']++;
                        $stats['upcoming_deadlines']++;
                    }
                }
            }
        }
        
        // Count reviews
        if (!empty($assessment->audit_review_date) && $assessment->audit_review_date != '0000-00-00') {
            $stats['audit_reviews']++;
        }
        
        if (!empty($assessment->compliance_review_date) && $assessment->compliance_review_date != '0000-00-00') {
            $stats['compliance_reviews']++;
        }
    }
    
    // Count upcoming audits
    $stats['upcoming_audits_count'] = $this->countUpcomingAudits($today);
    
    // Prepare statistics for view
    $this->data['statistics'] = [
        'audit_events' => $stats['audit_completed_count'],        // AUDIT: assessments with report_submitted_date
        'compliance_events' => $stats['compliance_completed_count'], // COMPLIANCE: assessments with compliance_end_date
        'upcoming_audits' => $stats['upcoming_audits_count'],
        'overdue_items' => $stats['overdue_items'],
        'upcoming_deadlines' => $stats['upcoming_deadlines'],
        'audit_reviews' => $stats['audit_reviews'],
        'compliance_reviews' => $stats['compliance_reviews']
    ];
    
    $this->data['user_access'] = [
        'user_id' => $this->loggedInUser->id ?? 'unknown',
        'user_name' => $this->userData->name ?? $this->loggedInUser->name ?? 'Unknown',
        'user_type_id' => $this->userData->user_type_id ?? $this->loggedInUser->user_type_id ?? 0,
        'allowed_audit_units' => $this->allowedAuditUnitIds,
        'is_super_admin' => false,
        'has_access_denied' => $this->data['access_denied'] ?? false
    ];
}
    private function countDeadlines($assessment, $today, &$stats)
    {
        // Check audit due date
        if (!empty($assessment->audit_due_date) && $assessment->audit_due_date != '0000-00-00') {
            if ($assessment->audit_due_date < $today) {
                $stats['overdue_items']++;
            } elseif ($assessment->audit_due_date >= $today) {
                $stats['upcoming_deadlines']++;
            }
        }
        
        // Check compliance due date
        if (!empty($assessment->compliance_due_date) && $assessment->compliance_due_date != '0000-00-00') {
            if ($assessment->compliance_due_date < $today) {
                $stats['overdue_items']++;
            } elseif ($assessment->compliance_due_date >= $today) {
                $stats['upcoming_deadlines']++;
            }
        }
        
        // Check audit review date
        if (!empty($assessment->audit_review_date) && $assessment->audit_review_date != '0000-00-00') {
            if ($assessment->audit_review_date < $today) {
                $stats['overdue_items']++;
            } elseif ($assessment->audit_review_date >= $today) {
                $stats['upcoming_deadlines']++;
            }
        }
        
        // Check compliance review date
        if (!empty($assessment->compliance_review_date) && $assessment->compliance_review_date != '0000-00-00') {
            if ($assessment->compliance_review_date < $today) {
                $stats['overdue_items']++;
            } elseif ($assessment->compliance_review_date >= $today) {
                $stats['upcoming_deadlines']++;
            }
        }
    }
    
    private function countUpcomingAudits($today)
    {
        $count = 0;
        
        foreach ($this->data['audit_unit_data'] as $unit) {
            if (!empty($unit->last_audit_date)) {
                $frequencyMonths = (int)($unit->frequency ?? 1);
                if ($frequencyMonths > 0) {
                    $nextAuditStart = $this->calculateNextAuditStart($unit->last_audit_date, $frequencyMonths, $today);
                    if ($nextAuditStart) {
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }

    public function index()
    {
        if (!empty($this->data['access_denied'])) {
            return $this->renderAccessDenied();
        }
        
        $viewType = $this->request->input('view', 'calendar');
        $this->data['current_view'] = $viewType;
        
        // Apply filters if any
        $this->applyFilters();
        
        return return2View($this, $this->me->viewDir . 'index', [
            'data' => $this->data,
            'request' => $this->request
        ]);
    }
    
    private function renderAccessDenied()
    {
        return return2View($this, $this->me->viewDir . 'access_denied', [
            'message' => $this->data['access_denied_message'],
            'data' => $this->data
        ]);
    }
    
    private function applyFilters()
    {
        $auditUnitFilter = $this->request->input('audit_unit_id');
        $dateFilter = $this->request->input('filter_date');
        $typeFilter = $this->request->input('event_type');
        $statusFilter = $this->request->input('status_id');
        
        // Validate audit unit access
        if (!empty($auditUnitFilter) && !$this->hasAccessToAuditUnit($auditUnitFilter)) {
            $auditUnitFilter = null;
        }
        
        // Apply filters if any
        if (!empty($auditUnitFilter) || !empty($dateFilter) || !empty($typeFilter) || !empty($statusFilter)) {
            $this->applyDynamicFilters($auditUnitFilter, $dateFilter, $typeFilter, $statusFilter);
        }
    }

    private function applyDynamicFilters($auditUnitId, $date, $type, $statusId)
    {
        $events = json_decode($this->data['calendar_events'], true);
        $filteredEvents = [];
        
        foreach ($events as $event) {
            if ($this->shouldIncludeEvent($event, $auditUnitId, $date, $type, $statusId)) {
                $filteredEvents[] = $event;
            }
        }
        
        $this->data['calendar_events'] = json_encode($filteredEvents);
        $this->updateStatisticsFromFilteredEvents($filteredEvents);
    }
    
    private function shouldIncludeEvent($event, $auditUnitId, $date, $type, $statusId)
    {
        // Check audit unit
        if (!empty($auditUnitId) && ($event['audit_unit_id'] ?? '') != $auditUnitId) {
            return false;
        }
        
        // Check event type
        if (!empty($type) && $event['type'] != $type && $event['type'] != 'upcoming_audit') {
            return false;
        }
        
        // Check status
        if (!empty($statusId) && ($event['status_id'] ?? '') != $statusId && $event['status_id'] != 0) {
            return false;
        }
        
        // Check date
        if (!empty($date) && $event['start'] != $date) {
            return false;
        }
        
        return true;
    }
    
    private function updateStatisticsFromFilteredEvents($filteredEvents)
{
    $totalEvents = count($filteredEvents);
    $auditStarted = 0;
    $complianceStarted = 0;
    $upcomingAudits = 0;
    $today = date('Y-m-d');
    $upcomingDeadlines = 0;
    $overdueItems = 0;
    
    $uniqueAuditAssessments = [];
    $uniqueComplianceAssessments = [];
    
    // Track which assessments we've already processed for overdue
    $processedAssessments = [];
    
    foreach ($filteredEvents as $event) {
        // Count start events
        if ($event['type'] == 'audit' && $event['category'] == 'start') {
            $assessmentKey = $event['assessment_id'] ?? $event['id'];
            if (!in_array($assessmentKey, $uniqueAuditAssessments)) {
                $auditStarted++;
                $uniqueAuditAssessments[] = $assessmentKey;
            }
        } elseif ($event['type'] == 'compliance' && $event['category'] == 'start') {
            $assessmentKey = $event['assessment_id'] ?? $event['id'];
            if (!in_array($assessmentKey, $uniqueComplianceAssessments)) {
                $complianceStarted++;
                $uniqueComplianceAssessments[] = $assessmentKey;
            }
        } elseif ($event['type'] == 'upcoming_audit') {
            $upcomingAudits++;
        }
        
        // Track assessment for overdue calculation
        if (isset($event['assessment_id']) && !in_array($event['assessment_id'], $processedAssessments)) {
            $assessmentId = $event['assessment_id'];
            $processedAssessments[] = $assessmentId;
            
            // Get the assessment data using the new model method
            $assessment = $this->auditAssessmentModel->getAssessmentById($assessmentId);
            
            if ($assessment) {
                // Fetch report_submitted_date for this assessment
                $exeData = $this->exeBasicModel->getSingleBasicDetails([
                    'where' => 'assesment_id = :assesment_id AND deleted_at IS NULL',
                    'params' => ['assesment_id' => $assessmentId]
                ]);
                
                // Check if audit is completed (has report_submitted_date or audit_end_date)
                $isAuditCompleted = false;
                if ($exeData && !empty($exeData->report_submitted_date) && $exeData->report_submitted_date != '0000-00-00') {
                    $isAuditCompleted = true;
                } elseif (!empty($assessment->audit_end_date) && $assessment->audit_end_date != '0000-00-00') {
                    $isAuditCompleted = true;
                }
                
                // Check if compliance is completed
                $isComplianceCompleted = !empty($assessment->compliance_end_date) && 
                                        $assessment->compliance_end_date != '0000-00-00';
                
                // Check audit due date (only if audit not completed)
                if (!$isAuditCompleted && !empty($assessment->audit_due_date) && $assessment->audit_due_date != '0000-00-00') {
                    if ($assessment->audit_due_date < $today) {
                        $overdueItems++;
                    }
                }
                
                // Check compliance due date (only if compliance not completed)
                if (!$isComplianceCompleted && !empty($assessment->compliance_due_date) && $assessment->compliance_due_date != '0000-00-00') {
                    if ($assessment->compliance_due_date < $today) {
                        $overdueItems++;
                    }
                }
            }
        }
    }
    
    $this->data['statistics'] = [
        'total_events' => $totalEvents,
        'audit_events' => $auditStarted,
        'compliance_events' => $complianceStarted,
        'upcoming_audits' => $upcomingAudits,
        'ticket_events' => 0,
        'executive_events' => 0,
        'upcoming_deadlines' => $upcomingDeadlines,
        'overdue_items' => $overdueItems
    ];
}
    
    private function countEventByType($event, &$uniqueAudit, &$uniqueCompliance, &$auditStarted, 
        &$complianceStarted, &$upcomingAudits, $today, &$upcomingDeadlines, &$overdueItems)
    {
        // Count start events (excluding review events for the main count)
        if ($event['type'] == 'audit' && $event['category'] == 'start') {
            $assessmentKey = $event['assessment_id'] ?? $event['id'];
            if (!in_array($assessmentKey, $uniqueAudit)) {
                $auditStarted++;
                $uniqueAudit[] = $assessmentKey;
            }
        } elseif ($event['type'] == 'compliance' && $event['category'] == 'start') {
            $assessmentKey = $event['assessment_id'] ?? $event['id'];
            if (!in_array($assessmentKey, $uniqueCompliance)) {
                $complianceStarted++;
                $uniqueCompliance[] = $assessmentKey;
            }
        } elseif ($event['type'] == 'upcoming_audit') {
            $upcomingAudits++;
        }
        
        // Count deadlines and reviews
        if (isset($event['category']) && in_array($event['category'], ['due', 'review'])) {
            if ($event['start'] < $today) {
                $overdueItems++;
            } elseif ($event['start'] >= $today) {
                $upcomingDeadlines++;
            }
        }
    }
    
    public function exportData()
    {
        $exportData = [
            'export_date' => date('Y-m-d H:i:s'),
            'user_id' => $this->loggedInUser->id ?? 'unknown',
            'user_name' => $this->userData->name ?? $this->loggedInUser->name ?? 'Unknown',
            'events' => json_decode($this->data['calendar_events'], true),
            'statistics' => $this->data['statistics'],
            'audit_units' => $this->data['audit_unit_data'],
            'user_access' => $this->data['user_access'] ?? []
        ];

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="calendar_backup_' . ($this->loggedInUser->id ?? 'unknown') . '_' . date('Y-m-d') . '.json"');
        
        echo json_encode($exportData, JSON_PRETTY_PRINT);
        exit;
    }
}
?>