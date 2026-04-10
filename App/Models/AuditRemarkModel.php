<?php

// namespace Model;

use Core\Model;

class AuditRemarkModel extends Model {

    protected static $table = 'audit_remarks';

    public function emptyInstance() {
        return $this -> _db::getTableColumns( self::$table );
    }

    public function getTableName() {
        return self::$table;
    }

    public function getAllAuditRemark($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(2, $this -> _db, self::$table, $filters, $query_type, $sql);
    }

    public function getSingleAuditRemark($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(1, $this -> _db, self::$table, $filters, $query_type, $sql);
    }
}

?>