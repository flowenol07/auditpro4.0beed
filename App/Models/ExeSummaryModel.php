<?php

// namespace Model;

use Core\Model;

class ExeSummaryModel extends Model {

    protected static $table = 'exe_summary';

    public function emptyInstance() {
        return $this -> _db::getTableColumns( self::$table );
    }

    public function getTableName() {
        return self::$table;
    }

    public function getAllMarchPosition($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(2, $this -> _db, self::$table, $filters, $query_type, $sql);
    }

    public function getSingleMarchPosition($filters = [], $query_type = null, $sql = '')
    {
        //database helper function call
        return get_all_data_query_builder(1, $this -> _db, self::$table, $filters, $query_type, $sql);
    }
    public function getAllExeSummary($filters = [], $query_type = null, $sql = '') {
        //database helper function call
        return get_all_data_query_builder(2, $this->_db, self::$table, $filters, $query_type, $sql);
    }

    public function getSingleExeSummary($filters = [], $query_type = null, $sql = '') {
        //database helper function call
        return get_all_data_query_builder(1, $this->_db, self::$table, $filters, $query_type, $sql);
    }
}

?>