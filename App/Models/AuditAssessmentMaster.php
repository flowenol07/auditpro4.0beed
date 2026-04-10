<?php

namespace Models;

use Core\Model;

class AuditAssessmentMaster extends Model
{
    protected $table = 'audit_assesment_master';
    protected $primaryKey = 'id';
    
    /**
     * Get all audit assessments with conditions using Model methods
     * 
     * @param array $options Options array with keys:
     *   - where: WHERE clause string with placeholders
     *   - params: Array of parameters for the WHERE clause
     *   - order: ORDER BY clause
     *   - limit: LIMIT clause
     *   - fields: Fields to select (default: *)
     * @return array|object Array of assessment objects
     */
    public function getAllAuditAssesment($options = [])
    {
        // Default options
        $defaultOptions = [
            'where' => 'deleted_at IS NULL',
            'params' => [],
            'order' => 'id DESC',
            'limit' => null
        ];
        
        // Merge options with defaults
        $options = array_merge($defaultOptions, $options);
        
        // Use the parent Model's query method with proper parameter binding
        return $this->query(
            $this->getTableName(),
            $options['where'],
            $options['params'],
            $options['order'],
            $options['limit']
        );
    }
    
    /**
     * Get single audit assessment by ID
     * 
     * @param int $id Assessment ID
     * @return object|null Assessment object or null if not found
     */
    public function getAuditAssesmentById($id)
    {
        $result = $this->getAllAuditAssesment([
            'where' => 'id = :id AND deleted_at IS NULL',
            'params' => ['id' => $id],
            'limit' => 1
        ]);
        
        return !empty($result) ? reset($result) : null;
    }
    
    /**
     * Get assessments by audit unit ID
     * 
     * @param int $auditUnitId Audit unit ID
     * @param array $statusIds Optional array of status IDs to filter
     * @return array Array of assessment objects
     */
    public function getAssessmentsByAuditUnit($auditUnitId, $statusIds = [])
    {
        $params = ['audit_unit_id' => $auditUnitId];
        $where = 'audit_unit_id = :audit_unit_id AND deleted_at IS NULL';
        
        if (!empty($statusIds)) {
            $placeholders = [];
            foreach ($statusIds as $index => $statusId) {
                $placeholder = ":status_id_{$index}";
                $placeholders[] = $placeholder;
                $params[$placeholder] = $statusId;
            }
            $where .= " AND audit_status_id IN (" . implode(', ', $placeholders) . ")";
        }
        
        return $this->getAllAuditAssesment([
            'where' => $where,
            'params' => $params
        ]);
    }
    
    /**
     * Get active assessments (status 1-4)
     * 
     * @param int|null $auditUnitId Optional audit unit ID filter
     * @return array Array of assessment objects
     */
    public function getActiveAssessments($auditUnitId = null)
    {
        $params = [];
        $where = 'deleted_at IS NULL AND audit_status_id IN (1,2,3,4)';
        
        if (!empty($auditUnitId)) {
            $where .= ' AND audit_unit_id = :audit_unit_id';
            $params['audit_unit_id'] = $auditUnitId;
        }
        
        return $this->getAllAuditAssesment([
            'where' => $where,
            'params' => $params,
            'order' => 'id DESC'
        ]);
    }
    
    /**
     * Get assessments by status
     * 
     * @param int $statusId Status ID
     * @param int|null $auditUnitId Optional audit unit ID filter
     * @return array Array of assessment objects
     */
    public function getAssessmentsByStatus($statusId, $auditUnitId = null)
    {
        $params = ['status_id' => $statusId];
        $where = 'audit_status_id = :status_id AND deleted_at IS NULL';
        
        if (!empty($auditUnitId)) {
            $where .= ' AND audit_unit_id = :audit_unit_id';
            $params['audit_unit_id'] = $auditUnitId;
        }
        
        return $this->getAllAuditAssesment([
            'where' => $where,
            'params' => $params
        ]);
    }
    
    /**
     * Get overdue assessments (audit_due_date or compliance_due_date passed)
     * 
     * @return array Array of assessment objects
     */
    public function getOverdueAssessments()
    {
        $today = date('Y-m-d');
        
        return $this->getAllAuditAssesment([
            'where' => 'deleted_at IS NULL AND (audit_due_date < :today OR compliance_due_date < :today)',
            'params' => ['today' => $today]
        ]);
    }
    
    /**
     * Get upcoming deadlines (within next 7 days)
     * 
     * @return array Array of assessment objects
     */
    public function getUpcomingDeadlines()
    {
        $today = date('Y-m-d');
        $nextWeek = date('Y-m-d', strtotime('+7 days'));
        
        return $this->getAllAuditAssesment([
            'where' => 'deleted_at IS NULL AND ((audit_due_date BETWEEN :today AND :nextWeek) OR (compliance_due_date BETWEEN :today AND :nextWeek))',
            'params' => [
                'today' => $today,
                'nextWeek' => $nextWeek
            ]
        ]);
    }
}