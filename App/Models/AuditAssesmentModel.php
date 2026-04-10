<?php

// namespace Model;

use Core\Model;

class AuditAssesmentModel extends Model {

    protected static $table = 'audit_assesment_master';

    public function emptyInstance() {
        return $this -> _db::getTableColumns( self::$table );
    }

    public function getTableName() {
        return self::$table;
    }

    public function getAllAuditAssesment($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(2, $this -> _db, self::$table, $filters, $query_type, $sql);
    }

    public function getSingleAuditAssesment($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(1, $this -> _db, self::$table, $filters, $query_type, $sql);
    }
    public function getAssessmentById($id)
    {
        $result = $this->getSingleAuditAssesment([
            'where' => "id = '$id' AND deleted_at IS NULL"
        ]);
        
        if (!empty($result)) {
            return is_array($result) ? (object)$result[0] : (object)$result;
        }
        
        return null;
    }

}

?>