<?php

// namespace Model;

use Core\Model;

class ComplianceCircularHeaderModel extends Model {

    protected static $table = 'com_circular_header_master';

    public function emptyInstance() {
        return $this -> _db::getTableColumns( self::$table );
    }

    public function getTableName() {
        return self::$table;
    }

    public function getAllCircularHeader($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(2, $this -> _db, self::$table, $filters, $query_type, $sql);
    }

    public function getSingleCircularHeader($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(1, $this -> _db, self::$table, $filters, $query_type, $sql);
    }
}

?>